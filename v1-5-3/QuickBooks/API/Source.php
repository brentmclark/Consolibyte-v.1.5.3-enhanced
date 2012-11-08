<?php

/**
 * API source base class
 * 
 * The QuickBooks_API class instantiates QuickBooks_API_Source_* subclasses to 
 * perform the actual interaction with whatever QuickBooks interface. 
 * 
 * QuickBooks API source subclasses must implement the abstract methods 
 * provided herein to allow the API classes to interact with QuickBooks. 
 * Several methods are provided which allow the source to enable or disable 
 * features of the API by specifying what actions and translation methods this 
 * particular API source can understand and interact with. 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage API 
 */

/**
 * QuickBooks API-source base class (communication between QuickBooks and generic API methods)
 */
abstract class QuickBooks_API_Source
{
	/**
	 * Create a new API source
	 */
	abstract public function __construct(&$driver_obj, $user, $dsn, $options = array());
	
	/**
	 * Get the error number of the last error that occured
	 * 
	 * 
	 */
	public function errorNumber()
	{
		return $this->_errnum;
	}
	
	public function errorMessage()
	{
		return $this->_errmsg;
	}	
	
	public function lastResponse()
	{
		return $this->_last_response;
	}
	
	public function lastRequest()
	{
		return $this->_last_request;
	}
	
	/**
	 * 
	 * 
	 * @param integer $errnum
	 * @param string $errmsg
	 * @return void
	 */
	protected function _setError($errnum, $errmsg = '')
	{
		$this->_errnum = $errnum;
		$this->_errmsg = $errmsg;
	}	
	
	/**
	 * 
	 * 
	 * @param integer $errnum
	 * @param string $errmsg
	 * @return void
	 */
	protected function _setLastResponse($response)
	{
		$this->_last_response = $response;
	}	
	
	/**
	 * 
	 * 
	 * @param integer $errnum
	 * @param string $errmsg
	 * @return void
	 */
	protected function _setLastRequest($request)
	{
		$this->_last_request = $request;
	}	

	public function connectionTicket($cticket = null)
	{
		return $this->_connectionTicket($cticket);
	}
	
	abstract protected function _connectionTicket($cticket);
	
	public function sessionTicket($sticket = null)
	{
		return $this->_sessionTicket($sticket);
	}
	
	abstract protected function _sessionTicket($sticket);
	
	public function applicationID($appid = null)
	{
		return $this->_applicationID($appid);
	}
	
	abstract protected function _applicationID($appid);
	
	public function applicationLogin($login = null)
	{
		return $this->_applicationLogin($login);
	}
	
	abstract protected function _applicationLogin($login);
	
	/**
	 * Handle an SQL query
	 * 
	 * If your API source does not understand SQL queries, you can just return 
	 * NULL or FALSE from this function. You should also implement 
	 * {@link QuickBooks_API_Source::understandsSQL()} and return FALSE from 
	 * that method. 
	 * 
	 * @param string $method
	 * @param string $action
	 * @param string $type
	 * @param string $sql
	 * @param array $callbacks
	 * @param integer $webapp_ID
	 * @param integer $priority
	 * @param string $err
	 * @return boolean
	 */
	abstract public function handleSQL($method, $action, $type, $sql, $callbacks, $webapp_ID, $priority, &$err);
	
	/**
	 * 
	 */
	abstract public function handleObject($method, $action, $type, $object, $callbacks, $webapp_ID, $priority, &$err);
	
	/**
	 * Handle a qbXML request/query
	 * 
	 * @param string $method
	 * @param string $action
	 * @param string $type
	 * @param string $sql
	 * @param array $callbacks
	 * @param integer $webapp_ID
	 * @param integer $priority
	 * @param string $err
	 * @return boolean
	 */
	abstract public function handleQBXML($method, $action, $type, $qbxml, $callbacks, $webapp_ID, $priority, &$err);
	
	/**
	 * 
	 * 
	 */
	abstract public function handleArray($method, $action, $type, $array, $callbacks, $webapp_ID, $priority, &$err);
	
	abstract public function useTestEnvironment($yes_or_no);
	
	abstract public function useLiveEnvironment($yes_or_no);
	
	/**
	 * Turn debugging mode on or off
	 * 
	 * Turning debugging mode on will result in a large amount of output being 
	 * printed directly to stdout (the web browser or the console)
	 * 
	 * @param boolean $yes_or_no
	 * @return void
	 */
	abstract public function useDebugMode($yes_or_no);		
	
	/**
	 * Returns a list of actions the API supports
	 * 
	 * @return array
	 */
	abstract public function supported();
	
	/**
	 * Tell whether or not the API source supports reading from QuickBooks
	 * 
	 * @return boolean
	 */
	public function supportsReading()
	{
		return $this->supportsQuerying();
	}
	
	/**
	 * Tell whether or not the API source supports writing to QuickBooks
	 * 
	 * @return boolean
	 */
	public function supportsWriting()
	{
		return $this->supportsAdding() and 
			$this->supportsModifying() and 
			$this->supportsDeleting();
	}
	
	/**
	 * Tell whether or not the API source supports mapping of application IDs
	 * 
	 * @return boolean
	 */
	abstract public function supportsApplicationIDs();
	
	/**
	 * Tell whether or not the API source supports adding new stuff to QuickBooks
	 * 
	 * @return boolean
	 */
	abstract public function supportsAdding();
	
	/**
	 * Tell whether or not the API source supports deleting stuff from QuickBooks
	 * 
	 * @return boolean
	 */
	abstract public function supportsDeleting();
	
	/**
	 * Tell whether or not the API source supports modifying stuff in QuickBooks
	 * 
	 * @return boolean 
	 */
	abstract public function supportsModifying();
	
	/**
	 * Tell whether or not the API source supports querying for stuff in QuickBooks
	 * 
	 * @return boolean
	 */
	abstract public function supportsQuerying();
	
	/**
	 * Tell whether or not the API source supports real-time transactions with QuickBooks
	 * 
	 * @return boolean
	 */
	abstract public function supportsRealtime();
	
	/**
	 * Tell whether or not the API source understands QuickBooks_Object_* object instances
	 * 
	 * @return boolean
	 */
	abstract public function understandsObjects();
	
	/**
	 * Tell whether or not the API source understands qbXML requests
	 * 
	 * @return boolean
	 */
	abstract public function understandsQBXML();
	
	abstract public function understandsArrays();
	
	abstract public function understandsSQL();
}

?>