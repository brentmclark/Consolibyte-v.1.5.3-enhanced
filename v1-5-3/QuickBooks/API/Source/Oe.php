<?php

/**
 * QuickBooks API support for QuickBooks Online Edition
 *
 * This class extends the QuickBooks_API support to QuickBooks Online Edition,
 * by providing an API source which talks directly to QuickBooks Online Edition
 * via HTTPS POST requests. 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 *
 * @package QuickBooks
 * @subpackage API
 */

/**
 * QuickBooks base constants
 */
require_once 'QuickBooks.php';

/**
 * Generic utility methods
 */
require_once 'QuickBooks/Utilities.php';

/**
 * API source base class
 */
require_once 'QuickBooks/API/Source.php';

/**
 * QuickBooks Online Edition API source
 * 
 * Communicates with QuickBooks Online Edition via the QBOE HTTPS POST API 
 * methods provided by the Intuit QBOE SDK gateway servers. 
 */
class QuickBooks_API_Source_OE extends QuickBooks_API_Source
{
	/**
	 * The QuickBooks back-end driver object
	 * @var QuickBooks_Driver
	 */
	protected $_driver;
	
	/**
	 * The username of the Web Connector user
	 * @var string
	 */
	protected $_user;
	
	/**
	 * Configuration variables
	 * @var array
	 */
	protected $_config;

	//protected $_connection_ticket;
	
	protected $_application_login;
	protected $_application_id;
	
	protected $_certificate;
	
	protected $_test;
	protected $_debug;
	
	protected $_live_gateway = 'https://webapps.quickbooks.com/j/AppGateway';
	protected $_test_gateway = '';
	
	protected $_ticket_session = '';
	protected $_ticket_connection = '';
	
	protected $_last_request;
	protected $_last_response;	
	
	/**
	 * Whether or not to enable masking of sensitive data in logging messages (credit card numbers, etc.)
	 * @var boolean
	 */
	protected $_masking;
	
	/**
	 * 
	 * 
	 */
	public function __construct(&$driver_obj, $user, $dsn, $options = array())
	{
		$this->_driver = $driver_obj;
		$this->_user = $user;
		
		$this->_config = $this->_defaults($options);
		
		$this->_masking = true;
		
		$this->_test = false;
		$this->_debug = false;
		
		// This particular 'source' uses the same database connection/DSN as 
		//	the driver, so there's no real reason to pull the user from 
		//	elsewhere...
		
		// @TODO Pull this information from the database
		
		if ($this->_config['connection_ticket'])
		{
			$this->_ticket_connection = $this->_config['connection_ticket'];
		}
		
		if ($this->_config['override_session_ticket'])
		{
			$this->_ticket_session = $this->_config['override_session_ticket'];
		} 
		
		if ($this->_config['override_connection_ticket'])
		{
			$this->_ticket_connection = $this->_config['override_connection_ticket'];
		}
		
		$this->_certificate = $this->_config['certificate'];
		$this->_application_login = $this->_config['application_login'];
		$this->_application_id = $this->_config['application_id'];
		
		$this->_log('Initialized the QBOE API source...', QUICKBOOKS_LOG_DEVELOP);
	}
	
	/**
	 * Merge configuration options with the default configuration options
	 * 
	 * @param array $options
	 * @return array
	 */
	protected function _defaults($options)
	{
		$defaults = array(
			'qbxml_version' => '6.0', 
			'qbxml_onerror' => 'stopOnError', 
			'always_use_iterator' => false, 
			'override_connection_ticket' => null, 
			'override_session_ticket' => null, 
			'application_login' => null, 
			'application_id' => null, 
			'certificate' => null, 
			);
		
		return array_merge($defaults, $options);
	}
	
	/**
	 * Get the HTTP/HTTPS gateway to use 
	 * 
	 * @return string
	 */	
	protected function _gateway()
	{
		if ($this->_test)
		{
			$this->_log('Using TEST gateway: ' . $this->_test_gateway, QUICKBOOKS_LOG_DEVELOP);
			return $this->_test_gateway;
		}
		
		$this->_log('Using LIVE gateway: ' . $this->_live_gateway, QUICKBOOKS_LOG_DEVELOP);
		return $this->_live_gateway;
	}	

	/**
	 * 
	 * 
	 * 
	 * @param string $message
	 * @param integer $level
	 * @return boolean
	 */
	protected function _log($message, $level = QUICKBOOKS_LOG_NORMAL)
	{
		if ($this->_masking)
		{
			// Mask credit card numbers, session tickets, and connection tickets
			$message = QuickBooks_Utilities::mask($message);
		}
		
		if ($this->_debug)
		{
			print($message . QUICKBOOKS_CRLF);
		}
		
		if ($this->_driver)
		{
			$this->_driver->log($message, $this->_ticket_session, $level);
		}
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * @param string $xml
	 * @param string $version
	 * @param string $onerror
	 * @return string
	 */
	protected function _makeValidQBXML($xml, $version = '{$version}', $onerror = '{$onerror}')
	{
		$pre = '';
		$pre .= '<?xml version="1.0" ?>' . QUICKBOOKS_CRLF;
		$pre .= '<?qbxml version="6.0"?>' . QUICKBOOKS_CRLF;
		$pre .= '<QBXML>' . QUICKBOOKS_CRLF; 
		$pre .= '	<SignonMsgsRq>' . QUICKBOOKS_CRLF;
		$pre .= '		<SignonTicketRq>' . QUICKBOOKS_CRLF;
		$pre .= '			<ClientDateTime>' . date('Y-m-d') . 'T' . date('h:i:s') . '</ClientDateTime>' . QUICKBOOKS_CRLF;
		$pre .= '			<SessionTicket>' . $this->_ticket_session . '</SessionTicket>' . QUICKBOOKS_CRLF;
		$pre .= '			<Language>English</Language>' . QUICKBOOKS_CRLF;
		$pre .= '			<AppID>' . $this->_application_id . '</AppID>' . QUICKBOOKS_CRLF;
		$pre .= '			<AppVer>1</AppVer>' . QUICKBOOKS_CRLF;
		$pre .= '		</SignonTicketRq>' . QUICKBOOKS_CRLF;
		$pre .= '	</SignonMsgsRq>' . QUICKBOOKS_CRLF;
		$pre .= '	<QBXMLMsgsRq onError="' . $onerror . '">';
		
		$post = '	</QBXMLMsgsRq>' . QUICKBOOKS_CRLF;
		$post .= '</QBXML>';
		
		// If the request they passed is a full request, then we don't need to prepend/append the extra XML
		if (false === stripos($xml, '<QBXML>'))
		{
			return $pre . $xml . $post;
		}
		
		return $xml;
	}

	/**
	 * Get (or set) the connection ticket
	 * 
	 * @param string $cticket		The new connection ticket to set (or null if you only want to get it)
	 * @return string				The connection ticket currently in use
	 */
	protected function _connectionTicket($cticket)
	{
		$current = $this->_ticket_connection;
		
		if ($cticket)
		{
			$this->_ticket_connection = $cticket;
		}
		
		return $current;
	}
	
	/**
	 * Get (or set) the session ticket 
	 * 
	 * @param string $sticket		The new session ticket to set (or null if you only want to get it)
	 * @return string				The session ticket currently in use
	 */
	protected function _sessionTicket($sticket)
	{
		$this->_setError(QUICKBOOKS_API_ERROR_OK);
		
		$current = $this->_ticket_session;
		
		if ($sticket)
		{
			$this->_ticket_session = $sticket;
		} 
		else
		{	
			// Make sure we have a session ticket so we can actually return it... 
			if (!$this->_isSignedOn())
			{
				$this->_signOn();
				
				if ($this->errorNumber())
				{
					return false;
				}
			}
		}
		
		return $current;
	}
	
	protected function _applicationID($appid)
	{	
		$current = $this->_application_id;
		
		if ($appid)
		{
			$this->_application_id = $appid;
		}
		
		return $current;
	}
	
	protected function _applicationLogin($login)
	{
		$current = $this->_application_login;
		
		if ($login)
		{
			$this->_application_login = $login;
		}
		
		return $current;
	}
	
	/**
	 * 
	 *
	 * @return boolean
	 */
	protected function _signOn()
	{
		$this->_setError(QUICKBOOKS_API_ERROR_OK);
		
		$xml = '';
		$xml .= '<?xml version="1.0" ?>' . QUICKBOOKS_CRLF;
		$xml .= '<?qbxml version="6.0"?> ' . QUICKBOOKS_CRLF;
		$xml .= '<QBXML>' . QUICKBOOKS_CRLF;
		$xml .= '	<SignonMsgsRq>' . QUICKBOOKS_CRLF;
		
		if ($this->_certificate)
		{
			$this->_log('Signing on as a HOSTED QBOE application.', QUICKBOOKS_LOG_DEBUG);
			
			$xml .= '		<SignonAppCertRq>' . QUICKBOOKS_CRLF;
			$xml .= '			<ClientDateTime>' . date('Y-m-d') . 'T' . date('h:i:s') . '</ClientDateTime> ' . QUICKBOOKS_CRLF;
			$xml .= '			<ApplicationLogin>' . $this->_application_login . '</ApplicationLogin> ' . QUICKBOOKS_CRLF;
			$xml .= '			<ConnectionTicket>' . $this->_ticket_connection . '</ConnectionTicket> ' . QUICKBOOKS_CRLF;
			$xml .= '			<Language>English</Language> ' . QUICKBOOKS_CRLF;
			$xml .= '			<AppID>' . $this->_application_id . '</AppID> ' . QUICKBOOKS_CRLF;
			$xml .= '			<AppVer>1</AppVer> ' . QUICKBOOKS_CRLF;
			$xml .= '		</SignonAppCertRq> ' . QUICKBOOKS_CRLF;
		}
		else
		{
			$this->_log('Signing on as a DESKTOP QBOE application.', QUICKBOOKS_LOG_DEBUG);
			
			$xml .= '		<SignonDesktopRq>' . QUICKBOOKS_CRLF;
			$xml .= '			<ClientDateTime>' . date('Y-m-d') . 'T' . date('h:i:s') . '</ClientDateTime> ' . QUICKBOOKS_CRLF;
			$xml .= '			<ApplicationLogin>' . $this->_application_login . '</ApplicationLogin> ' . QUICKBOOKS_CRLF;
			$xml .= '			<ConnectionTicket>' . $this->_ticket_connection . '</ConnectionTicket> ' . QUICKBOOKS_CRLF;
			$xml .= '			<Language>English</Language> ' . QUICKBOOKS_CRLF;
			$xml .= '			<AppID>' . $this->_application_id . '</AppID> ' . QUICKBOOKS_CRLF;
			$xml .= '			<AppVer>1</AppVer> ' . QUICKBOOKS_CRLF;
			$xml .= '		</SignonDesktopRq> ' . QUICKBOOKS_CRLF;			
		}
		
		$xml .= '	</SignonMsgsRq> ' . QUICKBOOKS_CRLF;
		$xml .= '</QBXML>';
		
		$errnum = QUICKBOOKS_API_ERROR_OK;
		$errmsg = '';
		
		$response = $this->_request($xml, $errnum, $errmsg);
		
		if ($errnum)
		{
			$this->_setError(QUICKBOOKS_API_ERROR_SOCKET, $errnum . ': ' . $errmsg);
			return false;
		}
		
		$code = $this->_extractAttribute('statusCode', $response);
		$message = $this->_extractAttribute('statusMessage', $response);
		
		if ($code != QUICKBOOKS_API_ERROR_OK)
		{
			$this->_setError($code, $message);
			return false;
		}
		
		if ($ticket = $this->_extractTagContents('SessionTicket', $response))
		{
			$this->_ticket_session = $ticket;
			
			return true;
		}
		
		$this->_setError(QUICKBOOKS_API_ERROR_INTERNAL, 'Could not locate SessionTicket in response.');
		
		return false;
	}

	protected function _extractTagContents($tag, $data)
	{
		// SessionTicket
		if (false !== strpos($data, '<' . $tag . '>') and 
			false !== strpos($data, '</' . $tag . '>'))
		{
			$data = strstr($data, '<' . $tag . '>');
			$end = strpos($data, '</' . $tag . '>');
			
			return substr($data, strlen($tag) + 2, $end - (strlen($tag) + 2));
		}
		
		return null;
	}
	
	protected function _extractAttribute($attr, $data, $which = 0)
	{
		if ($which == 1)
		{
			$spos = strpos($data, $attr . '="');
			$data = substr($data, $spos + strlen($attr));
		}
		
		if (false !== ($spos = strpos($data, $attr . '="')) and 
			false !== ($epos = strpos($data, '"', $spos + strlen($attr) + 2)))
		{
			//print('start: ' . $spos . "\n");
			//print('end: ' . $epos . "\n");
			
			return substr($data, $spos + strlen($attr) + 2, $epos - $spos - strlen($attr) - 2);
		}
		
		return '';
	}
	
	/**
	 * 
	 * 
	 * 
	 * 
	 */
	protected function _isSignedOn()
	{
		return strlen($this->_ticket_session) > 0;
	}
	
	/**
	 *  
	 * 
	 */
	public function useTestEnvironment($yes_or_no)
	{
		$this->_test = (boolean) $yes_or_no;
	}
	
	/**
	 * 
	 * 
	 */
	public function useLiveEnvironment($yes_or_no)
	{
		$this->_test = ! (boolean) $yes_or_no;
	}
	
	/**
	 * Turn debugging mode on or off
	 * 
	 * Turning debugging mode on will result in a large amount of output being 
	 * printed directly to stdout (the web browser or the console)
	 * 
	 * @param boolean $yes_or_no
	 * @return void
	 */
	public function useDebugMode($yes_or_no)
	{
		$this->_debug = (boolean) $yes_or_no;
	}	
	
	/**
	 * 
	 * 
	 * @param string $method
	 * @param string $action
	 * @param string $type
	 * @param QuickBooks_Object $object
	 * @param array $callbacks
	 * @param integer $webapp_ID
	 * @param integer $priority
	 * @param string $err
	 * @param integer $recur
	 * @return boolean
	 */
	public function handleObject($method, $action, $type, $object, $callbacks, $webapp_ID, $priority, &$err, $recur = null)
	{
		return false;
	}
	
	/**
	 * 
	 * 
	 */
	public function handleArray($method, $action, $type, $array, $callbacks, $webapp_ID, $priority, &$err, $recur = null)
	{
		return false;
	}
	
	/**
	 * 
	 * 
	 * @param string $method
	 * @param string $action
	 * @param string $type
	 * @param string $qbxml
	 * @param array $callbacks
	 * @param mixed $uniqueid
	 * @param integer $priority
	 * @param string $err
	 * @param integer $recur
	 * @return boolean
	 */
	public function handleQBXML($method, $action, $type, $qbxml, $callbacks, $uniqueid, $priority, &$err, $recur = null)
	{
		$this->_setError(QUICKBOOKS_API_ERROR_OK);
		
		// Make sure we have a session ticket 
		if (!$this->_isSignedOn())
		{
			$this->_signOn();
			
			if ($this->errorNumber())
			{
				return false;
			}
		}
		
		// @TODO Determine $action if it's not set
		
		// If a unique ID wasn't provided, we'll make one up
		if (strlen($uniqueid) == 0)
		{
			$uniqueid = md5(time() . $this->_user . mt_rand());
		}
		
		// The qbXML requests that get passed to this function are without the 
		//	typical qbXML wrapper info, so we need to modify them to make them 
		// 	into complete, valid requests. 
		$qbxml = $this->_makeValidQBXML($qbxml, $this->_config['qbxml_version'], $this->_config['qbxml_onerror']);
		
		//$requestID = null;
		$extra = array(
			'callbacks' => $callbacks, 
			);
		$last_action_time = null;
		$last_actionident_time = null;
		$qb_identifiers = array();
		
		// Send the request to QuickBooks Online Edition
		$response = $this->_request($qbxml);
		
		// Try to map the response to QuickBooks objects
		$map = array(
			QUICKBOOKS_ADD_CUSTOMER => array( '', 'QuickBooks_Callbacks_API_Callbacks::CustomerAddResponse' ), 
			QUICKBOOKS_MOD_CUSTOMER => array( '', 'QuickBooks_Callbacks_API_Callbacks::CustomerModResponse' ), 
			QUICKBOOKS_QUERY_CUSTOMER => array( '', 'QuickBooks_Callbacks_API_Callbacks::CustomerQueryResponse' ), 
			'*' => array( '', 'QuickBooks_Callbacks_API_Callbacks::RawQBXMLResponse' ), 
			);
		
		//print($qbxml);
		//print('CALL THIS: QuickBooks_Server_API_Callbacks::' . $action . 'Response');
		//print($response);
		
		return QuickBooks_Callbacks::callResponseHandler($this->_driver, $map, $action, $this->_user, $action, $uniqueid, $extra, $err, $last_action_time, $last_actionident_time, $response, $qb_identifiers);
		
		//exit;
		/*
		if ($recur)
		{
			return false;
		}
		else
		{
			return $this->_driver->queueEnqueue($this->_user, $action, $uniqueid, true, $priority, $extra, $qbxml);
		}
		*/
	}
	
	/**
	 * Send an XML request to QuickBooks Online Edition
	 * 
	 * This function will auto-detect if CURL is enabled, and if so, use CURL. 
	 * Otherwise, it will fall back to using fsockopen(). 
	 * 
	 * @param string $qbxml
	 * @return string
	 */
	protected function _request($qbxml)
	{
		if (function_exists('curl_init'))
		{
			return $this->_requestCurl($qbxml);
		}
		else
		{
			return $this->_requestFsockopen($qbxml);
		}
	}
	
	/**
	 * Send a request to QuickBooks Online Edition using the CURL PHP module
	 * 
	 * @param string $xml
	 * @return string
	 */
	protected function _requestCurl($xml)
	{
		$ch = curl_init(); 
	
		$header[] = 'Content-Type: application/x-qbxml'; 
		$header[] = 'Content-Length: ' . strlen($xml); 
		
		//$this->_certificate = '/Users/kpalmer/Projects/QuickBooks/QuickBooks/dev/test_qboe.pem';
		
		$params = array();
		$params[CURLOPT_HTTPHEADER] = $header; 
		$params[CURLOPT_POST] = true; 
		$params[CURLOPT_RETURNTRANSFER] = true; 
		$params[CURLOPT_URL] = $this->_gateway(); 
		$params[CURLOPT_TIMEOUT] = 30; 
		$params[CURLOPT_POSTFIELDS] = $xml; 
		$params[CURLOPT_VERBOSE] = $this->_debug; 
		$params[CURLOPT_HEADER] = true;
		
		if (file_exists($this->_certificate))
		{
			$params[CURLOPT_SSL_VERIFYPEER] = false; 
			$params[CURLOPT_SSLCERT] = $this->_certificate; 
		}
		
		// Some Windows servers will fail with SSL errors unless we turn this off
		$params[CURLOPT_SSL_VERIFYPEER] = false;
		$params[CURLOPT_SSL_VERIFYHOST] = 0;		
		
		// Diagnostic information: https://merchantaccount.quickbooks.com/j/diag/http
		// curl_setopt($ch, CURLOPT_INTERFACE, '<myipaddress>');
		
		$ch = curl_init();
		curl_setopt_array($ch, $params);
		$response = curl_exec($ch);
		
		$this->_log('CURL options: ' . print_r($params, true), QUICKBOOKS_LOG_DEBUG);
		
		// @todo Strip credit card numbers from logged XML... (or should this be within the _log() method?)
		
		$this->_setLastRequest($xml);
		$this->_log('Outgoing QBOE request: ' . $xml, QUICKBOOKS_LOG_DEBUG);	// Set as DEBUG so that no one accidentally logs all the credit card numbers...
		
		$this->_setLastResponse($response);
		$this->_log('Incoming QBOE response: ' . $response, QUICKBOOKS_LOG_VERBOSE);
		
		if (curl_errno($ch)) 
		{
			$errnum = curl_errno($ch);
			$errmsg = curl_error($ch);
			
			$this->_log('CURL error: ' . $errnum . ': ' . $errmsg, QUICKBOOKS_LOG_NORMAL);
			
			return false;
		} 
		
		// Close the connection 
		@curl_close($ch);
		
		// Remove the HTTP headers from the response
		$pos = strpos($response, "\r\n\r\n");
		$response = ltrim(substr($response, $pos));
		
		return $response;		
	}
	
	/**
	 * 
	 * 
	 */
	protected function _requestFsockopen()
	{
		$this->_log('FSOCKOPEN support is not yet implemented (install the PHP CURL extension).', QUICKBOOKS_LOG_NORMAL);
	}
	
	public function  handleSQL($method, $action, $type, $sql, $callbacks, $webapp_ID, $priority, &$err, $recur = null)
	{
		return false;
	}
	
	/**
	 * 
	 * 
	 * @return array
	 */
	public function supported()
	{
		return array(
			QUICKBOOKS_ADD_CLASS, 
			QUICKBOOKS_QUERY_CLASS, 
			
			QUICKBOOKS_ADD_ACCOUNT, 
			QUICKBOOKS_MOD_ACCOUNT, 
			QUICKBOOKS_QUERY_ACCOUNT, 
			
			QUICKBOOKS_ADD_CUSTOMER,
			QUICKBOOKS_MOD_CUSTOMER,  
			QUICKBOOKS_QUERY_CUSTOMER,
			
			QUICKBOOKS_ADD_CUSTOMERTYPE, 
			QUICKBOOKS_QUERY_CUSTOMERTYPE, 
			
			QUICKBOOKS_ADD_DEPOSIT, 
			QUICKBOOKS_MOD_DEPOSIT, 
			QUICKBOOKS_QUERY_DEPOSIT, 
			
			QUICKBOOKS_ADD_DATAEXT, 
			QUICKBOOKS_MOD_DATAEXT, 
			QUICKBOOKS_DEL_DATAEXT, 
			
			QUICKBOOKS_ADD_INVOICE, 
			QUICKBOOKS_MOD_INVOICE, 
			QUICKBOOKS_QUERY_INVOICE, 
						
			QUICKBOOKS_ADD_EMPLOYEE, 
			QUICKBOOKS_MOD_EMPLOYEE, 
			QUICKBOOKS_QUERY_EMPLOYEE, 
			
			QUICKBOOKS_ADD_ESTIMATE, 
			QUICKBOOKS_MOD_ESTIMATE, 
			QUICKBOOKS_QUERY_ESTIMATE, 
			
			QUICKBOOKS_ADD_PAYMENTMETHOD, 
			QUICKBOOKS_QUERY_PAYMENTMETHOD, 
			
			QUICKBOOKS_ADD_RECEIVEPAYMENT, 
			QUICKBOOKS_MOD_RECEIVEPAYMENT,
			QUICKBOOKS_QUERY_RECEIVEPAYMENT,  
			
			QUICKBOOKS_QUERY_ITEM,
			
			QUICKBOOKS_ADD_DISCOUNTITEM, 
			QUICKBOOKS_MOD_DISCOUNTITEM, 
			QUICKBOOKS_QUERY_DISCOUNTITEM, 
			
			QUICKBOOKS_ADD_FIXEDASSETITEM, 
			QUICKBOOKS_MOD_FIXEDASSETITEM, 
			QUICKBOOKS_QUERY_FIXEDASSETITEM, 
			
			QUICKBOOKS_ADD_SERVICEITEM,
			QUICKBOOKS_MOD_SERVICEITEM, 
			QUICKBOOKS_QUERY_SERVICEITEM,  
			
			QUICKBOOKS_ADD_INVENTORYITEM, 
			QUICKBOOKS_MOD_INVENTORYITEM, 
			QUICKBOOKS_QUERY_INVENTORYITEM,
			 
			QUICKBOOKS_ADD_NONINVENTORYITEM,
			QUICKBOOKS_MOD_NONINVENTORYITEM, 
			QUICKBOOKS_QUERY_NONINVENTORYITEM,
			  
			QUICKBOOKS_ADD_OTHERCHARGEITEM, 
			QUICKBOOKS_MOD_OTHERCHARGEITEM, 
			QUICKBOOKS_QUERY_OTHERCHARGEITEM, 
			  
			QUICKBOOKS_ADD_SALESTAXITEM, 
			QUICKBOOKS_MOD_SALESTAXITEM, 
			QUICKBOOKS_QUERY_SALESTAXITEM,
			
			QUICKBOOKS_ADD_SALESRECEIPT, 
			QUICKBOOKS_MOD_SALESRECEIPT, 
			QUICKBOOKS_QUERY_SALESRECEIPT, 
			
			QUICKBOOKS_ADD_SHIPMETHOD, 
			QUICKBOOKS_QUERY_SHIPMETHOD, 						
			
			QUICKBOOKS_ADD_VENDOR, 
			QUICKBOOKS_MOD_VENDOR, 
			QUICKBOOKS_QUERY_VENDOR,  
			);
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	public function supportsApplicationIDs()
	{
		return true;
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	public function supportsAdding()
	{
		return true;
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	public function supportsDeleting()
	{
		return true;
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	public function supportsModifying()
	{
		return true;
	}
	
	public function supportsQuerying()
	{
		return true;
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	public function supportsRealtime()
	{
		return true;
	}
	
	public function supportsRecurring()
	{
		return false;
	}
	
	public function understandsSQL()
	{
		return false;
	}
	
	public function understandsQBXML()
	{
		return true;
	}
	
	public function understandsArrays()
	{
		return false;
	}
	
	public function understandsObjects()
	{
		return false;
	}
}

?>