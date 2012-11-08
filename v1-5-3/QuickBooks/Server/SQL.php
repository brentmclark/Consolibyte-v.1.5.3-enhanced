<?php

/**
 * Mirror a QuickBooks database in a query-able SQL database 
 * 
 * I received an e-mail with questions about the Web Connector vs. the QODBC 
 * driver that can be found online. The QODBC driver is an ODBC driver for 
 * QuickBooks which allows you to connect to and query QuickBooks using SQL in 
 * real-time. This got me thinking, and what I've attempted to do here is come 
 * up with a similar solution for querying the QuickBooks database in real-time 
 * while utilizing the Web Connector. 
 * 
 * Essentially, this package tries to import your QuickBooks database into an 
 * SQL database of your choice, mapping the QuickBooks schema to SQL tables, 
 * and then allowing you to query (and possibly insert/update) the SQL database 
 * and keep this consistently syncronized with the original QuickBooks 
 * database.  
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @author Garrett Griffin <grgisme@gmail.com>
 * @license LICENSE.txt 
 * 
 * @package QuickBooks
 * @subpackage Server
 */

if (!defined('QUICKBOOKS_SERVER_SQL_ON_ERROR'))
{
	/**
	 *
	 */
	define('QUICKBOOKS_SERVER_SQL_ON_ERROR', 'continueOnError');
}

/**
 * Read from the QuickBooks database, and write to the SQL database
 */
define('QUICKBOOKS_SERVER_SQL_MODE_READONLY', 'r');

/**
 * Read from the SQL database, and write to the QuickBooks database
 */
define('QUICKBOOKS_SERVER_SQL_MODE_WRITEONLY', 'w');

/**
 * Read and write from both sources, keeping both sources in sync
 */
define('QUICKBOOKS_SERVER_SQL_MODE_READWRITE', '+');

define('QUICKBOOKS_SERVER_SQL_CONFLICT_LOG', 2);
define('QUICKBOOKS_SERVER_SQL_CONFLICT_NEWER', 4);
define('QUICKBOOKS_SERVER_SQL_CONFLICT_QUICKBOOKS', 8);
define('QUICKBOOKS_SERVER_SQL_CONFLICT_SQL', 16);
define('QUICKBOOKS_SERVER_SQL_CONFLICT_CALLBACK', 32);


/**
 * Delete Modes. Decides whether an item actually gets deleted, or just remains marked deleted.
 *
 */
define('QUICKBOOKS_SERVER_SQL_DELETE_REMOVE', 2);
define('QUICKBOOKS_SERVER_SQL_ON_DELETE_REMOVE', QUICKBOOKS_SERVER_SQL_DELETE_REMOVE);

define('QUICKBOOKS_SERVER_SQL_DELETE_FLAG', 4);
define('QUICKBOOKS_SERVER_SQL_ON_DELETE_FLAG', QUICKBOOKS_SERVER_SQL_DELETE_FLAG);




if (!defined('QUICKBOOKS_SERVER_SQL_ITERATOR_PRIORITY'))
{
	/**
	 * The priority value to use when re-queueing a request for the next part of an iterator
	 * 
	 * @var integer
	 */
	define('QUICKBOOKS_SERVER_SQL_ITERATOR_PRIORITY', 1000);
}


if (!defined('QUICKBOOKS_SERVER_SQL_CONFLICT_QUEUE_PRIORITY'))
{
	/**
	 * The priority value to use when issuing requests from an Error Handler for Add/Mods
	 * 
	 * @var integer
	 */
	define('QUICKBOOKS_SERVER_SQL_CONFLICT_QUEUE_PRIORITY', 9999);
}

if (!defined('QUICKBOOKS_SERVER_SQL_ITERATOR_MAXRETURNED'))
{
	/**
	 * How many records an iterator should grab in a single transaction
	 * 
	 * @var integer
	 */
	define('QUICKBOOKS_SERVER_SQL_ITERATOR_MAXRETURNED', 25);
}

/*function __temp_error_handler($requestID, $action, $ident, $extra, &$err, $xml, $errnum, $errmsg)
{
	return true;
}*/

/**
 * QuickBooks base constants
 */
require_once 'QuickBooks.php';

/**
 * QuickBooks driver classes
 */
require_once 'QuickBooks/Driver/Singleton.php';

/**
 * Server base class
 */
require_once 'QuickBooks/Server.php';

/**
 * SQL schema generation
 */
require_once 'QuickBooks/SQL/Schema.php';

/**
 * SQL objects (convert qbXML to objects to schema)
 */
require_once 'QuickBooks/SQL/Object.php';

/**
 * SQL callbacks (request and response handlers)
 */
require_once 'QuickBooks/Server/SQL/Callbacks.php';

/**
 * SQL error handlers
 */
require_once 'QuickBooks/Server/SQL/Errors.php';

/**
 * 
 * 
 */
class QuickBooks_Server_SQL extends QuickBooks_Server
{
	/**
	 * 
	 * 
	 * You can run this server in one of three modes:
	 * 	- QUICKBOOKS_SERVER_SQL_MODE_READONLY: Data will only be read from 
	 * 		QuickBooks; changes to data in the SQL database will never be 
	 * 		pushed back to QuickBooks. 
	 * 	- QUICKBOOKS_SERVER_SQL_MODE_WRITEONLY: Data will only be pushed to 
	 * 		QuickBooks, and nothing that already exists in QuickBooks will be 
	 * 		imported into the SQL database.
	 * 	- QUICKBOOKS_SERVER_SQL_MODE_READWRITE: The server will do it's best to 
	 * 		try to import all QuickBooks data into the SQL database, and then 
	 * 		push changes that occur in either location to the other location. 
	 * 		The server will try to syncronise the two locations as much as is 
	 * 		possible. 
	 * 
	 * @param string $dsn_or_conn		DSN-style connection string or an already opened connection to the driver
	 * @param string $how_often			The maximum time we wait between updates/syncs (you can use any valid interval: "1 hour", "15 minutes", 60, etc.)
	 * @param char $mode				The mode the server should run in (see constants above)
	 * @param char $conflicts			The steps towards update conflict resolution the server should take (see constants above)
	 * @param mixed $users				The user (or an array of users) who will be using the SQL server
	 * @param array $map				
	 * @param array $onerror			
	 * @param string $wsdl				
	 * @param array $soap_options		
	 * @param array $handler_options	
	 * @param array $driver_options		
	 */
	public function __construct(
		$dsn_or_conn, 
		$how_often, 
		$mode, 
		$delete,
		$conflicts, 
		$users = null, 
		$map = array(), 
		$onerror = array(), 
		$hooks = array(), 
		$log_level = QUICKBOOKS_LOG_NORMAL, 
		$soap = QUICKBOOKS_SOAPSERVER_BUILTIN, 
		$wsdl = QUICKBOOKS_WSDL, 
		$soap_options = array(), 
		$handler_options = array(), 
		$driver_options = array(),
		$sql_options = array(), 
		$callback_options = array())
	{
		// $dsn_or_conn, $map, $onerror = array(), $hooks = array(), $log_level = QUICKBOOKS_LOG_NORMAL, $soap = QUICKBOOKS_SOAPSERVER_BUILTIN, $wsdl = QUICKBOOKS_WSDL, $soap_options = array(), $handler_options = array(), $driver_options = array()
		
		if (!is_array($users))
		{
			$users = array( $users );
		}
		
		// Map of callback handlers 		
		$sql_map = array();
		
		foreach (get_class_methods('QuickBooks_Server_SQL_Callbacks') as $method)
		{
			if (strtolower(substr($method, -7)) == 'request')
			{
				$action = substr($method, 0, -7);
				
				$sql_map[$action] = array( 
					'QuickBooks_Server_SQL_Callbacks::' . $action . 'Request', 
					'QuickBooks_Server_SQL_Callbacks::' . $action . 'Response' );
			}
		}
		
		// Default error handlers
		$sql_onerror = array(
			'*' => 'QuickBooks_Server_SQL_Errors::catchall', 
			);
		
		$sql_onerror = $this->_merge($sql_onerror, $onerror, false);
		
		// Default hooks
		$sql_hooks = array(
			// This hook is neccessary for queueing up the appropriate actions to perform the sync 	(use login success so we know user to sync for)
			QUICKBOOKS_HANDLERS_HOOK_LOGINSUCCESS => array( 'QuickBooks_Server_SQL_Callbacks::onAuthenticate' ), 
			);
		
		// Merge with user-defined hooks
		$sql_hooks = $this->_merge($hooks, $sql_hooks, true);
		
		// @TODO Prefix these with _ so that people don't accidentally overwrite them
		$sql_callback_options = array(
			'hooks' => $sql_hooks, 
			'conflicts' => $conflicts,
			'mode' => $mode,
			'delete' => $delete,
			'recur' => QuickBooks_Utilities::intervalToSeconds($how_often),
			'map' => $sql_map,
			);
		
		//print_r($sql_options);
		//exit;
		
		$defaults = $this->_sqlDefaults($sql_options);
				
		$sql_callback_options['_only_query'] = $defaults['only_query'];
		$sql_callback_options['_dont_query'] = $defaults['dont_query'];
		$sql_callback_options['_only_add'] = $defaults['only_add'];
		$sql_callback_options['_dont_add'] = $defaults['dont_add'];
		$sql_callback_options['_only_modify'] = $defaults['only_modify'];
		$sql_callback_options['_dont_modify'] = $defaults['dont_modify'];
		
		// Merge default values with passed in values
		//	(in this case, we are *required* to have these values present, so 
		//	we make sure that the SQL options override any user-defined options
		$sql_callback_options = $this->_merge($callback_options, $sql_callback_options, false);
		
		// Initialize the Driver singleton
		$Driver = QuickBooks_Driver_Singleton::getInstance($dsn_or_conn, $driver_options, $sql_hooks, $log_level);		
		
		// $dsn_or_conn, $map, $onerror = array(), $hooks = array(), $log_level = QUICKBOOKS_LOG_NORMAL, $soap = QUICKBOOKS_SOAPSERVER_BUILTIN, $wsdl = QUICKBOOKS_WSDL, $soap_options = array(), $handler_options = array(), $driver_options = array()
		parent::__construct($dsn_or_conn, $sql_map, $sql_onerror, $sql_hooks, $log_level, $soap, $wsdl, $soap_options, $handler_options, $driver_options, $sql_callback_options);
	}
	
	/**
	 * Apply default options to an array of configuration options
	 * 
	 * @param array $config
	 * @return array
	 */
	protected function _sqlDefaults($config)
	{
		$tmp = array(
			'only_query',
			'dont_query',
			'only_add',
			'dont_add',
			'only_modify',
			'dont_modify', 
			);
		
		foreach ($tmp as $filter)
		{
			if (empty($config[$filter]) or
				(!empty($config[$filter]) and !is_array($config[$filter])))
			{
				$config[$filter] = array();
			}
		}
		
		// Any other configuration defaults go here 
		$defaults = array(
						  
			);
		
		return array_merge($defaults, $config);
	}
}

