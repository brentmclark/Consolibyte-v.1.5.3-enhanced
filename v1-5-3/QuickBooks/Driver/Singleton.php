<?php

/**
 * QuickBooks driver singleton
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Driver
 */

/**
 * 
 */
require_once 'QuickBooks.php';

/**
 * 
 */
require_once 'QuickBooks/Driver.php';

/**
 * 
 */
require_once 'QuickBooks/Utilities.php';

/**
 * 
 */
class QuickBooks_Driver_Singleton
{
	/**
	 * 
	 * 
	 * @param string $dsn_or_conn
	 * @param array $options
	 * @return QuickBooks_Driver
	 */
	public static function getInstance($dsn_or_conn = null, $options = array(), $hooks = array(), $log_level = null)
	{
		static $instance = null;
		if (is_null($instance))
		{
			$instance = QuickBooks_Utilities::driverFactory($dsn_or_conn, $options, $hooks, $log_level);
		}
		
		return $instance;
	}
	
	/**
	 * 
	 * 
	 */
	public static function initialize($dsn_or_conn, $options = array(), $hooks = array(), $log_level = null)
	{
		if ($obj = QuickBooks_Driver_Singleton::getInstance($dsn_or_conn, $options, $hooks, $log_level))
		{
			return true;
		}
		
		return false;
	}
}
