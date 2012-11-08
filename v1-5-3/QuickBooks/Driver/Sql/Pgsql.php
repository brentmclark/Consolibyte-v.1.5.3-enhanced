<?php

/**
 * PgSQL backend for the QuickBooks SOAP server
 * 
 * You need to use some sort of backend to facilitate communication between the 
 * SOAP server and your application. The SOAP server stores queue requests 
 * using the backend. 
 * 
 * This backend driver is for a PostgreSQL database. You can use the 
 * {@see QuickBooks_Utilities} class to initalize the five tables in the 
 * PostgreSQL database. 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 *  
 * @package QuickBooks
 * @subpackage Driver
 */

/**
 * Base QuickBooks constants
 */
require_once 'QuickBooks.php';

/**
 * QuickBooks driver base class
 */
require_once 'QuickBooks/Driver.php';

/**
 * QuickBooks driver SQL base class
 */
require_once 'QuickBooks/Driver/Sql.php';

/**
 * QuickBooks utilities class
 */
require_once 'QuickBooks/Utilities.php';

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_SALT'))
{
	/**
	 * Salt used when hashing to create ticket values
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_SALT', QUICKBOOKS_DRIVER_SQL_SALT);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX'))
{
	/**
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX', QUICKBOOKS_DRIVER_SQL_PREFIX);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_QUEUETABLE'))
{
	/**
	 * MySQL table name to store queued requests in
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_QUEUETABLE', QUICKBOOKS_DRIVER_SQL_QUEUETABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_USERTABLE'))
{
	/**
	 * MySQL table name to store usernames/passwords for the QuickBooks SOAP server
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_USERTABLE', QUICKBOOKS_DRIVER_SQL_USERTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_TICKETTABLE'))
{
	/**
	 * The table name to store session tickets in
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_TICKETTABLE', QUICKBOOKS_DRIVER_SQL_TICKETTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_LOGTABLE'))
{
	/**
	 * The table name to store log data in
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_LOGTABLE', QUICKBOOKS_DRIVER_SQL_LOGTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_RECURTABLE'))
{
	/**
	 * The table name to store recurring events in
	 * 
	 * @var string
	 */
	 define('QUICKBOOKS_DRIVER_SQL_PGSQL_RECURTABLE', QUICKBOOKS_DRIVER_SQL_RECURTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_IDENTTABLE'))
{
	/**
	 * The table name to store identifiers in
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_IDENTTABLE', QUICKBOOKS_DRIVER_SQL_IDENTTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_CONFIGTABLE'))
{
	/**
	 * The table name to store configuration options in
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_CONFIGTABLE', QUICKBOOKS_DRIVER_SQL_CONFIGTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_NOTIFYTABLE'))
{
	/**
	 * The table name to store notifications in
	 * 
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_NOTIFYTABLE', QUICKBOOKS_DRIVER_SQL_NOTIFYTABLE);
}

if (!defined('QUICKBOOKS_DRIVER_SQL_PGSQL_CONNECTIONTABLE'))
{
	/**
	 * The table name to store connection data in 
	 *
	 * @var string
	 */
	define('QUICKBOOKS_DRIVER_SQL_PGSQL_CONNECTIONTABLE', QUICKBOOKS_DRIVER_SQL_CONNECTIONTABLE);
}

/**
 * QuickBooks PostgreSQL back-end driver
 */
class QuickBooks_Driver_Sql_Pgsql extends QuickBooks_Driver_Sql
{
	/**
	 * PostgreSQL connection resource
	 * 
	 * @var resource
	 */
	protected $_conn;
	
	/**
	 * User-defined hook functions
	 * 
	 * @var array 
	 */
	protected $_hooks;
	
	/**
	 * 
	 */
	protected $_last_result;
	
	/**
	 * Create a new MySQL back-end driver
	 * 
	 * @param string $dsn		A DSN-style connection string (i.e.: "mysql://your-mysql-username:your-mysql-password@your-mysql-host:port/your-mysql-database")
	 * @param array $config		Configuration options for the driver (not currently supported)
	 */
	public function __construct($dsn_or_conn, $config)
	{
		$config = $this->_defaults($config);
		
		if (is_resource($dsn_or_conn))
		{
			$this->_conn = $dsn_or_conn;
		}
		else
		{
			$defaults = array(
				'scheme' => 'pgsql', 
				'host' => 'localhost', 
				'port' => 5432, 
				'user' => 'pgsql', 
				'pass' => '', 
				'path' => '/quickbooks',
				);
			
			$parse = QuickBooks_Utilities::parseDSN($dsn_or_conn, $defaults);
			
			$this->_connect($parse['host'], $parse['port'], $parse['user'], $parse['pass'], substr($parse['path'], 1), $config['new_link']);
		}

		// Call the parent constructor too
		parent::__construct($dsn_or_conn, $config);
	}
	
	/**
	 * Merge an array of configuration options with the defaults
	 * 
	 * @param array $config
	 * @return array 
	 */
	protected function _defaults($config)
	{
		$defaults = array(
			'new_link' => true, 
			);
		
		return array_merge($defaults, $config);
	}
	
	protected function _initialized()
	{
		$required = array(
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONFIGTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_NOTIFYTABLE) => false, 
			$this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONNECTIONTABLE) => false, 
			);
		
		$errnum = 0;
		$errmsg = '';
		$res = $this->_query("
			SELECT 
				table_name
			FROM
				information_schema.tables
			WHERE
				table_schema = 'public' AND table_type='BASE TABLE' ", $errnum, $errmsg);
		while ($arr = $this->_fetch($res))
		{
			$table = current($arr);
			
			if (isset($required[$table]))
			{
				$required[$table] = true;
			}
		}
		
		foreach ($required as $table => $exists)
		{
			if (!$exists)
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Connect to the database
	 * 
	 * @param string $host				The hostname the database is located at
	 * @param integer $port				The port the database is at
	 * @param string $user				Username for connecting
	 * @param string $pass				Password for connecting
	 * @param string $db				The database name
	 * @param boolean $new_link			TRUE for establishing a new link to the database, FALSE to re-use an existing one
	 * @param integer $client_flags		Database connection flags (see the PHP/MySQL documentation)
	 * @return boolean
	 */
	protected function _connect($host, $port, $user, $pass, $db, $new_link, $client_flags = null)
	{
		$tmp = array();
		
		if ($host)
		{
			$tmp[] = 'host=' . $host;
		}
		
		if ((int) $port)
		{
			$tmp[] = 'port=' . (int) $port;
		}
		
		if ($user)
		{
			$tmp[] = 'user=' . $user;
		}
		
		if ($pass)
		{
			$tmp[] = 'password=' . $pass;
		}
			
		if ($db)
		{
			$tmp[] = 'dbname=' . $db;
		}
		
		$str = implode(' ', $tmp);
		
		if ($new_link)
		{
			$this->_conn = pg_connect($str, PGSQL_CONNECT_FORCE_NEW);
		}
		else
		{
			$this->_conn = pg_connect($str);
		}
	}
	
	/**
	 * Fetch an array from a database result set
	 * 
	 * @param resource $res
	 * @return array
	 */
	protected function _fetch($res)
	{
		return pg_fetch_assoc($res);
	}
	
	/**
	 * Query the database
	 * 
	 * @param string $sql
	 * @return resource
	 */
	protected function _query($sql, &$errnum, &$errmsg, $offset = 0, $limit = null)
	{
		if ($limit)
		{
			if ($offset)
			{
				$sql .= " LIMIT " . (int) $offset . ", " . (int) $limit;
			}
			else
			{
				$sql .= " LIMIT " . (int) $limit;
			}
		}
		else if ($offset)
		{
			// @todo Should this be implemented...?
		}		
		
		$res = pg_query($this->_conn, $sql);
		
		$this->_last_result = $res;
		
		if (!$res)
		{
			$errnum = -1;
			$errmsg = pg_last_error($this->_conn);
			
			trigger_error('PostgreSQL Error: ' . $errmsg . ', SQL: ' . $sql, E_USER_ERROR);
			return false;
		}
		
		return $res;
	}
	
	/**
	 * Issue a query to the SQL server
	 * 
	 * @param string $sql
	 * @param integer $errnum
	 * @param string $errmsg
	 * @return resource
	 */
	public function query($sql, &$errnum, &$errmsg, $offset = 0, $limit = null)
	{
		return $this->_query($sql, $errnum, $errmsg, $offset, $limit);
	}
	
	/**
	 * Tell the number of rows the last run query affected
	 * 
	 * @return integer
	 */
	public function affected()
	{
		return pg_affected_rows($this->_last_result);
	}
	
	/**
	 * Tell the last inserted AUTO_INCREMENT value
	 * 
	 * @return integer
	 */
	public function last()
	{
		//return mysql_insert_id($this->_conn);
	}
	
	/**
	 * Tell the number of records in a result resource
	 * 
	 * @param resource $res
	 * @return integer
	 */
	public function count($res)
	{
		return $this->_count($res);
	}
	
	/**
	 * Escape a string
	 * 
	 * @param string $str
	 * @return string
	 */
	public function escape($str)
	{
		return $this->_escape($str);
	}
	
	/**
	 * Fetch a record from a result set
	 * 
	 * @param resource $res
	 * @return array
	 */
	public function fetch($res)
	{
		return $this->_fetch($res);
	}
	
	/**
	 * Escape a string for the database
	 * 
	 * @param string $str
	 * @return string
	 */
	protected function _escape($str)
	{
		return pg_escape_string($this->_conn, $str);
	}
	
	/**
	 * Count the number of rows returned from the database
	 * 
	 * @param resource $res
	 * @return integer
	 */
	protected function _count($res)
	{
		return pg_num_rows($res);
	}
	
	/**
	 * Map a default SQL table name to a PostgreSQL table name
	 * 
	 * @param string
	 * @return string
	 */
	/**
	 * Map a default SQL table name to a MySQL table name
	 * 
	 * @param string
	 * @return string
	 */
	protected function _mapTableName($table)
	{
		switch ($table)
		{
			case QUICKBOOKS_DRIVER_SQL_LOGTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_LOGTABLE;
			case QUICKBOOKS_DRIVER_SQL_QUEUETABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_QUEUETABLE;
			case QUICKBOOKS_DRIVER_SQL_RECURTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_vSQL_RECURTABLE;
			case QUICKBOOKS_DRIVER_SQL_TICKETTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_TICKETTABLE;
			case QUICKBOOKS_DRIVER_SQL_USERTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_USERTABLE;
			case QUICKBOOKS_DRIVER_SQL_CONFIGTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_CONFIGTABLE;
			case QUICKBOOKS_DRIVER_SQL_IDENTTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_IDENTTABLE;				
			case QUICKBOOKS_DRIVER_SQL_NOTIFYTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_NOTIFYTABLE;
			case QUICKBOOKS_DRIVER_SQL_CONNECTIONTABLE:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . QUICKBOOKS_DRIVER_SQL_PGSQL_CONNECTIONTABLE;
			default:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_PREFIX . $table;
		}
	}
	
	/**
	 * Map an encryption salt to a PostgreSQL-specific encryption salt
	 * 
	 * @param string $salt
	 * @return string
	 */
	protected function _mapSalt($salt)
	{
		switch ($salt)
		{
			case QUICKBOOKS_DRIVER_SQL_SALT:
				return QUICKBOOKS_DRIVER_SQL_PGSQL_SALT;
			default:
				return $salt;
		}
	}
	
	/**
	 * Override for the default SQL generation functions, PostgreSQL-specific field generation function
	 * 
	 * @param string $name
	 * @param array $def
	 * @return string
	 */
	protected function _generateFieldSchema($name, $def)
	{
		switch ($def[0])
		{
			case QUICKBOOKS_DRIVER_SQL_SERIAL:
				$sql = $name . ' SERIAL NOT NULL '; // AUTO_INCREMENT 
				
				return $sql;
			case QUICKBOOKS_DRIVER_SQL_DATETIME:
				
				$sql = $name . ' timestamp without time zone ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
					else
					{
						$sql .= ' DEFAULT ' . $def[2] . ' NOT NULL ';
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				return $sql;
			default:
				
				return parent::_generateFieldSchema($name, $def);
		}
	}
	
	/**
	 * Override for the default SQL generation functions, PostgreSQL-specific field generation function
	 * 
	 * @param string $name
	 * @param array $arr
	 * @param array $primary
	 * @param array $keys
	 * @return array
	 */
	protected function _generateCreateTable($name, $arr, $primary = array(), $keys = array())
	{
		$arr_sql = parent::_generateCreateTable($name, $arr, $primary, $keys);
		
		if (is_array($primary) and count($primary) == 1)
		{
			$primary = current($primary);
		}
		
		if (is_array($primary))
		{
			//ALTER TABLE  `quickbooks_ident` ADD PRIMARY KEY (  `qb_action` ,  `unique_id` )
			//$arr_sql[] = 'ALTER TABLE ' . $name . ' ADD PRIMARY KEY ( ' . implode(', ', $primary) . ' ) ';
		}
		else if ($primary)
		{
			$arr_sql[] = 'ALTER TABLE ONLY ' . $name . ' 
				ADD CONSTRAINT ' . $name . '_pkey PRIMARY KEY (' . $primary . ');';
		}
		
		foreach ($keys as $key)
		{
			if (is_array($key))		// compound key
			{
				$arr_sql[] = 'CREATE INDEX ' . implode('_', $key) . '_' . $name . '_index ON ' . $name . ' USING btree (' . implode(', ', $key) . ')';
			}
			else
			{
				$arr_sql[] = 'CREATE INDEX ' . $key . '_' . $name . '_index ON ' . $name . ' USING btree (' . $key . ')';
			}
		}
		
		return $arr_sql;
	}
}
