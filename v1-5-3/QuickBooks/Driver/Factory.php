<?php

/** 
 * 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt  
 * 
 * @package QuickBooks
 * @subpackage Driver
 */
 
/**
 * 
 *
 */
require_once 'QuickBooks.php';

/**
 * 
 */
require_once 'Quickbooks/Utilities.php';
 
/**
 * 
 * 
 *
 */
class QuickBooks_Driver_Factory
{
	/**
	 * Create an instance of a driver class from a DSN connection string *or* a connection resource
	 * 
	 * You can actually pass in *either* a DSN-style connection string OR an already connected database resource
	 * 	- mysql://user:pass@localhost:port/database
	 * 	- $var (Resource ID #XYZ, valid MySQL connection resource)
	 * 
	 * @param mixed $dsn_or_conn	A DSN-style connection string or a PHP resource
	 * @param array $config			An array of configuration options for the driver
	 * @param array $hooks			An array mapping hooks to user-defined hook functions to call
	 * @param integer $log_level	
	 * @return object				A class instance, a child class of QuickBooks_Driver
	 */
	static public function create($dsn_or_conn, $config = array(), $hooks = array(), $log_level = QUICKBOOKS_LOG_NORMAL)
	{
		static $instances = array();
			
		if (!is_array($hooks))
		{
			$hooks = array();
		}
			
		$key = (string) $dsn_or_conn . serialize($config) . serialize($hooks) . $log_level;
			
		if (!isset($instances[$key]))
		{
			if (is_resource($dsn_or_conn))
			{
				$scheme = current(explode(' ', get_resource_type($dsn_or_conn)));
			}
			else
			{
				$scheme = QuickBooks_Utilities::parseDSN($dsn_or_conn, array(), 'scheme');
			}
				
			if (false !== strpos($scheme, 'sql'))		// SQL drivers are subclassed... change class/scheme name
			{
				$scheme = 'SQL_' . $scheme;
			}
				
			$class = 'QuickBooks_Driver_' . ucfirst(strtolower($scheme));
			$file = 'QuickBooks/Driver/' . str_replace(' ', '/', ucwords(str_replace('_', ' ', strtolower($scheme)))) . '.php';
				
			require_once $file;
				
			if (class_exists($class))
			{
				$Driver = new $class($dsn_or_conn, $config);
				$Driver->registerHooks($hooks);
				$Driver->setLogLevel($log_level);
				
				// @todo Ugh this is really ugly... maybe have $log_level passed in as a parameter? Not really a driver option at all?
				//if (isset($config['log_level']))
				//{
				//	$driver->setLogLevel($config['log_level']);
				//}
				
				$instances[$key] = $Driver;
			}
			else
			{
				$instances[$key] = null;
			}
		}
			
		return $instances[$key];
	}	
}
