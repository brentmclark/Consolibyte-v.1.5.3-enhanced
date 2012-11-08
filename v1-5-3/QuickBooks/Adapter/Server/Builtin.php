<?php

/**
 * Adapter class for the built-in QuickBooks SOAP server
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Adapter
 */

/**
 * Server adapter base-class
 */
require_once 'QuickBooks/Adapter/Server.php';

/**
 * SOAP server base class
 */
require_once 'QuickBooks/SOAP/Server.php';

/**
 * 
 */
class QuickBooks_Adapter_Server_Builtin implements QuickBooks_Adapter_Server
{
	/**
	 * QuickBooks_SOAP_Server built-in SOAP server instance
	 */
	protected $_server;
	
	/**
	 * Create a new adapter for the built-in SOAP server
	 * 
	 * @param string $wsdl				The path to the WSDL file
	 * @param array $soap_options		An array of SOAP server options
	 */
	public function __construct($wsdl, $soap_options)
	{
		$this->_server = new QuickBooks_SOAP_Server($wsdl, $soap_options);
	}
	
	/**
	 * Handle an incoming SOAP request
	 * 
	 * @param string $raw_http_input	A string SOAP request
	 * @return string					A string containing XML SOAP output
	 */
	public function handle($raw_http_input)
	{
		return $this->_server->handle($raw_http_input);
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	public function setClass($class, $dsn_or_conn, $map, $onerror, $hooks, $log_level, $raw_http_input, $handler_options, $driver_options, $callback_options)
	{
		return $this->_server->setClass($class, $dsn_or_conn, $map, $onerror, $hooks, $log_level, $raw_http_input, $handler_options, $driver_options, $callback_options);
	}
	
	/** 
	 * 
	 * 
	 * 
	 */
	public function getFunctions()
	{
		return $this->_server->getFunctions();
	}
}

?>