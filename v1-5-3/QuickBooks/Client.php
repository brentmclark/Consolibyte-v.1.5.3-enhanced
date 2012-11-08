<?php

/**
 * QuickBooks SOAP client for testing purposes
 * 
 * Unused for now, might be here for testing in later versions
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 */

// Include path modifications (relative paths within library)
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . dirname(__FILE__) . '/../');

/**
 * 
 */
require_once 'QuickBooks.php';

require_once 'QuickBooks/Request.php';

require_once 'QuickBooks/Request/Authenticate.php';

require_once 'QuickBooks/Request/SendRequestXML.php';

require_once 'QuickBooks/Request/ReceiveResponseXML.php';

/**
 * 
 * 
 */
class QuickBooks_Client
{
	/**
	 * 
	 */
	protected $_client;
	
	/**
	 * 
	 */
	public function __construct($endpoint, $wsdl = QUICKBOOKS_WSDL, $soap = QUICKBOOKS_SOAPCLIENT_PHP, $trace = true)
	{
		$this->_client = $this->_adapterFactory($soap, $endpoint, $wsdl, $trace);
	}
	
	protected function _adapterFactory($adapter, $endpoint, $wsdl, $trace)
	{
		$adapter = ucfirst(strtolower($adapter));
		
		$file = 'QuickBooks/Adapter/Client/' . $adapter . '.php';
		$class = 'QuickBooks_Adapter_Client_' . $adapter;
		
		require_once $file;
		
		if (class_exists($class))
		{
			return new $class($endpoint, $wsdl, $trace);
		}
		
		return null;
	}
	
	/**
	 * Authenticate against a QuickBooks SOAP server
	 * 
	 * @param string $user
	 * @param string $pass
	 * @return array
	 */
	public function authenticate($user, $pass)
	{
		return $this->_client->authenticate($user, $pass);
	}
	
	public function sendRequestXML($ticket, $hcpresponse, $companyfile, $country, $majorversion, $minorversion)
	{
		return $this->_client->sendRequestXML($ticket, $hcpresponse, $companyfile, $country, $majorversion, $minorversion);
	}
	
	public function receiveResponseXML($ticket, $response, $hresult, $message)
	{
		return $this->_client->receiveResponseXML($ticket, $response, $hresult, $message);
	}
	
	public function getLastRequest()
	{
		return $this->_client->getLastRequest();
	}
	
	public function getLastResponse()
	{
		return $this->_client->getLastResponse();
	}
}

