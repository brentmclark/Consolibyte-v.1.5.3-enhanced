<?php

/**
 * QuickBooks SQL-database driver base-class
 * 
 * All SQL back-end drivers should extend this class. This class provides some 
 * database abstraction and scheme generating functions for SQL back-ends. 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @author Garrett Griffin <grgisme@gmail.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Driver
 */

/**
 * Abstract base class
 */
require_once 'QuickBooks/Driver.php';

/**
 * SQL scheme generation
 */
require_once 'QuickBooks/SQL/Schema.php';

/**
 * SQL data type - CHAR
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_CHAR', 'char');

/**
 * SQL data type - VARCHAR
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_VARCHAR', 'varchar');

/**
 * SQL data type - BOOLEAN
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_BOOLEAN', 'boolean');

/**
 * SQL data type - TEXT
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TEXT', 'text');

/**
 * SQL data type - INTEGER
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_INTEGER', 'integer');

/**
 * SQL data type - FLOAT
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_DECIMAL', 'decimal');

/**
 * SQL data type - FLOAT
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FLOAT', 'float');

/**
 * SQL data type - SERIAL (AUTO_INCREMENT for you MySQL users)
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_SERIAL', 'serial');

/**
 * SQL data type - DATE
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_DATE', 'date');

/**
 * SQL data type - TIME
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TIME', 'time');

/**
 * SQL data type - DATETIME
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_DATETIME', 'datetime');

/**
 * SQL data type - TIMESTAMP (preferably auto-updating)
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TIMESTAMP', 'timestamp');

/**
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TIMESTAMP_ON_INSERT_OR_UPDATE', 'timestamp-on-update-or-insert');

/**
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TIMESTAMP_ON_UPDATE', 'timestamp-on-update');

/**
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TIMESTAMP_ON_INSERT', 'timestamp-on-insert');

/**
 * This is the prefix for the base SQL tables
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_PREFIX', 'quickbooks_');

/**
 * This is the prefix for the SQL mirror tables
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_PREFIX_SQL', 'qb_');

/**
 * This is the prefix used for any extra SQL tables needed by the integrators
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_PREFIX_INTEGRATOR', 'qb_');

/**
 * Default table name for SQL log table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_LOGTABLE', 'log');

/**
 * Default table name for SQL queue table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_QUEUETABLE', 'queue');

/**
 * Default table name for SQL recurring events table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_RECURTABLE', 'recur');

/**
 * Default table name for SQL ticket table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_TICKETTABLE', 'ticket');

/**
 * Default table name for SQL user table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_USERTABLE', 'user');

/**
 * Default table name for SQL ident table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_IDENTTABLE', 'ident');

/**
 * Default table name for SQL config table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_CONFIGTABLE', 'config');

/**
 * Default table name for SQL notification table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_NOTIFYTABLE', 'notify');

/**
 * Default table name for SQL connection table
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_CONNECTIONTABLE', 'connection');

/**
 * 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_ID', 'qbsql_id');

/**
 * Default SQL field to keep track of when records were first pushed into the database
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_DISCOVER', 'qbsql_discov_datetime');

/**
 * Default SQL field to keep track of when records were last synced from QuickBooks 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_RESYNC', 'qbsql_resync_datetime');

/**
 * Default SQL field to keep track of records that have been modified (update-on-modify if SQL driver supports it)
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_MODIFY', 'qbsql_modify_timestamp');

/**
 * Default SQL field to keep track of the last record hash for this record
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_HASH', 'qbsql_last_hash');

/**
 * Default SQL field to keep track of records that should be deleted
 * 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_TO_SYNC', 'qbsql_to_sync');

/**
 * Default SQL field to keep track of records that should be deleted
 * 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_TO_DELETE', 'qbsql_to_delete');

/**
 * Default SQL field to keep track of records that should be deleted
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_TO_SKIP', 'qbsql_to_skip');

/**
 * 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_FLAG_SKIPPED', 'qbsql_flag_skipped');

/** 
 * 
 * 
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_FLAG_DELETED', 'qbsql_flag_deleted');

/**
 * Default SQL field to keep track of add/mods that failed
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_NUMBER', 'qbsql_last_errnum');

/**
 * Default SQL field to keep track of why add/mods failed
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE', 'qbsql_last_errmsg');

/**
 * Default SQL field to keep track of records that should be updated
 * 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_ENQUEUE_TIME', 'qbsql_enqueue_datetime');

/**
 * Default SQL field to keep track of when record was last dequeued
 * 
 * 
 * @var string
 */
define('QUICKBOOKS_DRIVER_SQL_FIELD_DEQUEUE_TIME', 'qbsql_dequeue_datetime');

/*
if (!defined('QUICKBOOKS_DRIVER_SQL_PREFIX'))
{
	define('QUICKBOOKS_DRIVER_SQL_PREFIX', 'qb_');
}
*/

if (!defined('QUICKBOOKS_DRIVER_SQL_SALT'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_DRIVER_SQL_SALT', '@ndP3pp@');
}

/**
 * SQL driver back-end for QuickBooks queues
 */
abstract class QuickBooks_Driver_Sql extends QuickBooks_Driver
{
	/**
	 * The maximum number of entries we should keep in the log table
	 * @var integer
	 */
	protected $_max_log_history;
	
	/**
	 * The maximum number of (successfully processed) entries we should keep in the queue table
	 * @var integer
	 */
	protected $_max_queue_history;
	
	/**
	 * The maximum number of entries we should keep in the ticket table
	 * @var integer
	 */
	protected $_max_ticket_history;	
		
	/**
	 * The logging level to log messages at
	 * @var integer
	 */
	protected $_log_level;

	/**
	 * 
	 * 
	 * @param string $dsn
	 * @param array $config
	 */
	public function __construct($dsn, $config)
	{
		$config = $this->__defaults($config);
		
		$this->_max_log_history = (int) $config['max_log_history'];
		$this->_max_queue_history = (int) $config['max_queue_history'];
		$this->_max_ticket_history = (int) $config['max_ticket_history'];
		
		$this->_log_level = $config['log_level'];
	}
	
	/**
	 * Merge an array of configuration options with the defaults
	 * 
	 * @param array $config
	 * @return array 
	 */
	private function __defaults($config)
	{
		$defaults = array(
			'max_log_history' => -1, 		// -1 means no limit
			'max_queue_history' => -1, 
			'max_ticket_history' => -1, 
			'log_level' => QUICKBOOKS_LOG_NORMAL, 
			);
		
		return array_merge($defaults, $config);
	}
	
	/**
	 * Resolve a ticket string back to a ticket ID number
	 * 
	 * @param string $ticket
	 * @return integer
	 */
	protected function _ticketResolve($ticket)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($arr = $this->_fetch($this->_query("
			SELECT 
				* 
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " 
			WHERE 
				ticket = '" . $this->_escape($ticket) . "' ", $errnum, $errmsg)))
		{
			return $arr['quickbooks_ticket_id'];
		}
		
		return 0;
	}
	
	/**
	 * Write a configuration variable to the database
	 * 
	 * @param string $user		The QuickBooks user this is stored for
	 * @param string $module	The module name this is stored for (free-form text, you make it up, but make it unique! a good habit is to use the __CLASS__ constant)
	 * @param string $key		A key to fetch and store this value by
	 * @param mixed $value		The value
	 * @param string $type		
	 * @param array $opts		
	 * @return boolean			Success or failure
	 */
	protected function _configWrite($user, $module, $key, $value, $type, $opts)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($arr = $this->_fetch($this->_query("
			SELECT 
				quickbooks_config_id 
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONFIGTABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' AND 
				module = '" . $this->_escape($module) . "' AND 
				cfgkey = '" . $this->_escape($key) . "' ", $errnum, $errmsg)))
		{
			$this->_query("
				UPDATE
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONFIGTABLE) . "
				SET
					cfgval = '" . $this->_escape($value) . "', 
					mod_datetime = NOW()
				WHERE
					quickbooks_config_id = " . $arr['quickbooks_config_id'], $errnum, $errmsg);
		}
		else
		{
			return $this->_query("
				INSERT INTO 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONFIGTABLE) . "
				(
					qb_username, 
					module, 
					cfgkey, 
					cfgval, 
					cfgtype, 
					cfgopts, 
					write_datetime, 
					mod_datetime
				) VALUES ( 
					'" . $this->_escape($user) . "', 
					'" . $this->_escape($module) . "', 
					'" . $this->_escape($key) . "', 
					'" . $this->_escape($value) . "', 
					'" . $this->_escape($type) . "', 
					'" . $this->_escape(serialize($opts)) . "', 
					NOW(), 
					NOW()
				) ", $errnum, $errmsg);
		}
	}
	
	/**
	 * Read configuration information 
	 * 
	 * @param string $user		The username to store this for
	 * @param string $module	The module to store this for
	 * @param string $key		The key to store this by
	 * @param string $type		
	 * @param array $opts		
	 * @return mixed			The value read from the SQL database 
	 */
	protected function _configRead($user, $module, $key, &$type, &$opts)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($arr = $this->_fetch($this->_query("
			SELECT 
				cfgval, 
				cfgtype, 
				cfgopts
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONFIGTABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' AND 
				module = '" . $this->_escape($module) . "' AND 
				cfgkey = '" . $this->_escape($key) . "' ", $errnum, $errmsg)))
		{
			$type = $arr['cfgtype'];
			$opts = $arr['cfgopts'];
			
			return $arr['cfgval'];
		}
		
		$type = null;
		$opts = null;
		
		return null;
	}
	
	/**
	 * Convert a ticket into a username
	 * 
	 * @param string $ticket
	 * @return string			The username of the user who belongs to this ticket
	 */
	protected function _authResolve($ticket)
	{
		if ($ticket_id = $this->_ticketResolve($ticket))
		{
			$errnum = 0;
			$errmsg = '';
			$arr = $this->_fetch($this->_query("
				SELECT 
					qb_username 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " 
				WHERE 
					quickbooks_ticket_id = " . $ticket_id, $errnum, $errmsg));
			
			return $arr['qb_username'];
		}
		
		return '';
	} 
	

	/**
	 * Create a new user for the SOAP server
	 * 
	 * @param string $username
	 * @param string $password
	 * @return boolean 
	 */
	protected function _authCreate($username, $password, $company_file = null, $wait_before_next_update = null, $min_run_every_n_seconds = null)
	{
		$errnum = 0;
		$errmsg = '';
		
		if (!$this->_count($this->_query("SELECT qb_username FROM " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " WHERE qb_username = '" . $this->_escape($username) . "' ", $errnum, $errmsg, 0, 1)))
		{
			return $this->_query("
				INSERT INTO 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
				( 
					qb_username, 
					qb_password, 
					qb_company_file, 
					qbwc_wait_before_next_update, 
					qbwc_min_run_every_n_seconds, 
					status, 
					write_datetime, 
					touch_datetime
				) VALUES (
					'" . $this->_escape($username) . "', 
					'" . $this->_escape($this->_hash($password)) . "', 
					'" . $this->_escape($company_file) . "', 
					" . (int) $wait_before_next_update . ", 
					" . (int) $min_run_every_n_seconds . ", 
					'" . QUICKBOOKS_USER_ENABLED . "', 
					'" . date('Y-m-d H:i:s') . "', 
					'" . date('Y-m-d H:i:s') . "'
				) ", $errnum, $errmsg);
		}
		
		return false;
	}
	
	/**
	 * Enable a username
	 * 
	 * @param string $username
	 * @return boolean
	 */
	protected function _authEnable($username)
	{
		$errnum = 0;
		$errmsg = '';
		
		return $this->_query("
			UPDATE 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
			SET
				status = '" . QUICKBOOKS_USER_ENABLED . "', 
				touch_datetime = '" . date('Y-m-d H:i:s') . "'
			WHERE
				qb_username = '" . $this->_escape($username) . "' ");
	}
	
	/**
	 * Disable a username
	 * 
	 * @param string $username
	 * @return boolean
	 */
	protected function _authDisable($username)
	{
		$errnum = 0;
		$errmsg = '';
		
		return $this->_query("
			UPDATE 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
			SET
				status = '" . QUICKBOOKS_USER_DISABLED . "', 
				touch_datetime = '" . date('Y-m-d H:i:s') . "'
			WHERE
				qb_username = '" . $this->_escape($username) . "' ");
	}
	
	/**
	 * Get the default user
	 * 
	 * @return string
	 */
	protected function _authDefault()
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($arr = $this->_fetch($this->_query("
			SELECT 
				qb_username 
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
			WHERE
				status = '" . QUICKBOOKS_USER_ENABLED . "' ", $errnum, $errmsg, 0, 1)))
		{
			return $arr['qb_username'];
		}
		
		return '';
	}
	
	/**
	 * Search for QuickBooks users
	 * 
	 * @param integer $offset
	 * @param integer $limit
	 * @param string $match
	 * @return QuickBooks_Iterator
	 */
	protected function _authView($offset, $limit, $match = '')
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		$list = array();
		
		if (strlen($match))
		{
			
		}
		else
		{
			$sql = "
				SELECT 
					* 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
				ORDER BY 
					qb_username ASC ";
		}
		
		$res = $this->_query($sql, $errnum, $errmsg, $offset, $limit);
		while ($arr = $this->_fetch($res))
		{
			$list[] = $arr;
		}
		
		return new QuickBooks_Iterator($list);
	}
	
	/**
	 * Get a count of how many results a search would return
	 * 
	 * @param string $match
	 * @return integer
	 */
	protected function _authSize($match = '')
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		if (strlen($match))
		{
			
		}
		else
		{
			$sql = "SELECT COUNT(*) AS total FROM " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE);
		}
		
		$arr = $this->_fetch($this->_query($sql, $errnum, $errmsg));
		return $arr['total'];
	}
	
	/**
	 * Log a user in
	 * 
	 * @param string $username		
	 * @param string $password		
	 * @param boolean $override		If this is set to TRUE, a correct password *is not* required
	 * @return string				A session ticket, or an empty string if the login failed
	 */
	protected function _authLogin($username, $password, &$company_file, &$wait_before_next_update, &$min_run_every_n_seconds, $override = false)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($override) // We still need to make sure that the user exists, even if using external authentication
		{
			$this->authCreate($username, $password);
		}
		else if (strlen(trim($password)) == 0)
		{
			// Blank passwords *always fail* 
			return null;
		}
		else if (strlen(trim($password)) == 32 or strlen(trim($password)) == 40)
		{
			// Possible *hack* attempt (they're sending us a random hash hoping it will match one of the hashed passwords)
			return null;
		}
		
		// Support for plain-text, MD5 (without salt), and SHA1 (without salt) passwords
		$plain_text = $password;
		$plain_md5 = md5($password);
		$plain_sha1 = sha1($password);
		
		if ($override or 
			$arr = $this->_fetch($this->_query("
				SELECT 
					* 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
				WHERE 
					qb_username = '" . $this->_escape($username) . "' AND 
					(
						qb_password = '" . $this->_escape($this->_hash($password)) . "' OR
						qb_password = '" . $this->_escape($plain_text) . "' OR 
						qb_password = '" . $this->_escape($plain_md5) . "' OR 
						qb_password = '" . $this->_escape($plain_sha1) . "' 
					) AND 
					status = '" . QUICKBOOKS_USER_ENABLED . "' ", $errnum, $errmsg, 0, 1)))
		{
			$ticket = md5((string) microtime() . $username . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_SALT));
			
			$this->_query("
				INSERT INTO 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . "
				( 
					qb_username, 
					ticket, 
					ipaddr, 
					write_datetime, 
					touch_datetime
				) VALUES ( 
					'" . $this->_escape($username) . "', 
					'" . $this->_escape($ticket) . "',
					'" . $_SERVER['REMOTE_ADDR'] . "',  
					'" . date('Y-m-d H:i:s') . "', 
					'" . date('Y-m-d H:i:s') . "'
				) ", $errnum, $errmsg);
			
			$this->_query("
				UPDATE 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE) . " 
				SET 
					touch_datetime = '" . date('Y-m-d H:i:s') . "'
				WHERE 
					qb_username = '" . $this->_escape($username) . "' ", $errnum, $errmsg);
			
			if (isset($arr) and 
				is_array($arr))		// Might not have this if it's an authenticate override
			{
				$company_file = $arr['qb_company_file'];
				$wait_before_next_update = $arr['qbwc_wait_before_next_update'];
				$min_run_every_n_seconds = $arr['qbwc_min_run_every_n_seconds'];
			}
			
			return $ticket;
		}
		
		return null;
	}
	
	/**
	 * Check to see if a log in session is valid
	 * 
	 * @param string $ticket
	 * @return boolean
	 */
	protected function _authCheck($ticket)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($arr = $this->_fetch($this->_query("
			SELECT 
				quickbooks_ticket_id 
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " 
			WHERE 
				ticket = '" . $this->_escape($ticket) . "' AND 
				touch_datetime > '" . date('Y-m-d H:i:s', time() - QUICKBOOKS_TIMEOUT) . "' ", $errnum, $errmsg)))
		{
			$this->_query("
				UPDATE 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " 
				SET 
					touch_datetime = '" . date('Y-m-d H:i:s') . "' 
				WHERE 
					quickbooks_ticket_id = " . $arr['quickbooks_ticket_id'], $errnum, $errmsg);
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Log a user out
	 * 
	 * @param string $ticket
	 * @return boolean
	 */
	protected function _authLogout($ticket)
	{
		return true;
	}
	
	/**
	 * Store the last error which occured 
	 * 
	 * @param string $ticket		The session ticket for the session this error occured within
	 * @param string $errnum		The error number
	 * @param string $errmsg		The error message
	 * @return boolean
	 */
	protected function _errorLog($ticket, $errnum, $errmsg)
	{
		if ($ticket_id = $this->_ticketResolve($ticket))
		{
			$db_errnum = 0;
			$db_errmsg = '';
			
			return $this->_query("
				UPDATE 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . "
				 SET 
					lasterror_num = '" . $this->_escape($errnum) . "', 
					lasterror_msg = '" . $this->_escape($errmsg) . "' 
				WHERE 
					quickbooks_ticket_id = " . $ticket_id, $db_errnum, $db_errmsg);
		}
		
		return false;
	}
	
	/**
	 * Retreive the last error message which occured for a given ticket (session)
	 * 
	 * @param string $ticket
	 * @return string
	 */
	protected function _errorLast($ticket)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($ticket_id = $this->_ticketResolve($ticket))
		{
			if ($arr = $this->_fetch($this->_query("SELECT lasterror_num, lasterror_msg FROM " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " WHERE quickbooks_ticket_id = '" . $ticket_id . "' ", $errnum, $errmsg, 0, 1)))
			{
				return $arr['lasterror_num'] . ': ' . $arr['lasterror_msg'];
			}
		}
		
		return 'Error fetching last error.';
	}
	
	/**
	 * Register a recurring event for a particular user
	 * 
	 * @param string $user
	 * @param integer $run_every
	 * @param string $action
	 * @param mixed $ident
	 * @param boolean $replace
	 * @param integer $priority
	 * @param mixed $extra
	 * @return boolean
	 */
	protected function _recurEnqueue($user, $run_every, $action, $ident, $replace = true, $priority = 0, $extra = null, $qbxml = null)
	{
		$errnum = 0;
		$errmsg = '';
		
		// By default, it has *never* occured
		$recur_lasttime = (time() - $run_every - 60);
		
		if ($replace)
		{
			if ($existing = $this->_fetch($this->_query("
					SELECT 
						recur_lasttime 
					FROM 
						" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE) . " 
					WHERE 
						qb_username = '" . $this->_escape($user) . "' AND 
						qb_action = '" . $this->_escape($action) . "' AND 
						ident = '" . $this->_escape($ident) . "' ", $errnum, $errmsg)))
			{
				$this->_query("
					DELETE FROM
						" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE) . " 
					WHERE
						qb_username = '" . $this->_escape($user) . "' AND 
						qb_action = '" . $this->_escape($action) . "' AND 
						ident = '" . $this->_escape($ident) . "' ", $errnum, $errmsg);

				$recur_lasttime = $existing['recur_lasttime'];
			}
		}
		
		if ($extra)
		{
			$extra = serialize($extra);
		}
		
		return $this->_query("
			INSERT INTO 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE) . "
			(
				qb_username, 
				qb_action, 
				ident, 
				extra,	
				qbxml, 
				priority,
				run_every, 
				recur_lasttime, 
				enqueue_datetime
			) VALUES (
				'" . $this->_escape($user) . "', 
				'" . $this->_escape($action) . "', 
				'" . $this->_escape($ident) . "',
				'" . $this->_escape($extra) . "',
				'" . $this->_escape($qbxml) . "', 
				" . (int) $priority . ",  
				" . (int) $run_every . ", 
				" . $recur_lasttime . ", 
				'" . date('Y-m-d H:i:s') . "' 
			) ", $errnum, $errmsg);
	}
	
	/**
	 * Dequeue a recurring even that is schedule to be run
	 * 
	 * @param string $user
	 * @param boolean $by_priority
	 * @return array
	 */
	protected function _recurDequeue($user, $by_priority = false)
	{
		$errnum = 0;
		$errmsg = '';
		
		$sql = "
			SELECT 
				* 
			FROM
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE) . " 
			WHERE
				qb_username = '" . $this->_escape($user) . "' AND 	
				recur_lasttime + run_every <= " . time();
		
		if ($by_priority)
		{
			$sql .= ' ORDER BY priority DESC ';
		}
		
		if ($arr = $this->_fetch($this->_query($sql . ' ', $errnum, $errmsg, 0, 1)))
		{
			// Update it, so it doesn't get fetched again until it's supposed to
			$errnum = 0;
			$errmsg = '';
			$this->_query("UPDATE " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE) . " SET recur_lasttime = " . time() . " WHERE quickbooks_recur_id = " . $arr['quickbooks_recur_id'], $errnum, $errmsg);
			
			return $arr;
		}
		
		return false;
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _recurView($offset, $limit, $match)
	{
		
		return new QuickBooks_Iterator();
	}
	
	/**
	 * Add an item to the queue
	 * 
	 * @param string $action
	 * @param mixed $ident
	 * @return boolean 
	 */
	protected function _queueEnqueue($user, $action, $ident, $replace = true, $priority = 0, $extra = null, $qbxml = null)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($replace)
		{
			$this->_query("
				DELETE FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
				WHERE 
					qb_username = '" . $this->_escape($user) . "' AND 
					qb_action = '" . $this->_escape($action) . "' AND 
					ident = '" . $this->_escape($ident) . "' AND
					qb_status = '" . QUICKBOOKS_STATUS_QUEUED . "' ", $errnum, $errmsg);
		}
		
		if ($extra)
		{
			$extra = serialize($extra);
		}
		
		return $this->_query("
			INSERT INTO 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . "
			(
				qb_username, 
				qb_action, 
				ident, 
				extra,	
				qbxml, 			
				priority, 
				qb_status, 
				enqueue_datetime
			) VALUES (
				'" . $this->_escape($user) . "', 
				'" . $this->_escape($action) . "', 
				'" . $this->_escape($ident) . "',
				'" . $this->_escape($extra) . "',
				'" . $this->_escape($qbxml) . "', 
				" . (int) $priority . ",  
				'" . QUICKBOOKS_STATUS_QUEUED . "', 
				'" . date('Y-m-d H:i:s') . "' 
			) ", $errnum, $errmsg);
	}
	
	/**
	 * Fetch a particular item from the queue
	 * 
	 * @param integer $queue_id
	 * @return array 
	 */
	protected function _queueFetch($user, $action, $ident, $status = QUICKBOOKS_STATUS_QUEUED)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($status)
		{
			$sql = "
				SELECT 
					* 
				FROM
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . "
				WHERE
					qb_username = '" . $this->_escape($user) . "' AND 
					qb_action = '" . $this->_escape($action) . "' AND 
					ident = '" . $this->_escape($ident) . "' AND 
					qb_status = '" . $this->_escape($status) . "'  ";
		}
		else
		{
			$sql = "
				SELECT 
					* 
				FROM
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . "
				WHERE
					qb_username = '" . $this->_escape($user) . "' AND 
					qb_action = '" . $this->_escape($action) . "' AND 
					ident = '" . $this->_escape($ident) . "' ";			
		}
		
		return $this->_fetch($this->_query($sql, $errnum, $errmsg, 0, 1));
	}
	
	/**
	 * Get the last time an action of this type was dequeued successfully for this user
	 * 
	 * @param string $user
	 * @param string $action
	 * @return integer
	 */
	protected function _queueActionLast($user, $action)
	{
		$errnum = 0;
		$errmsg = '';
		
		$sql = "
			SELECT
				dequeue_datetime
			FROM
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' AND 
				qb_status = '" . QUICKBOOKS_STATUS_SUCCESS . "' AND 
				qb_action = '" . $this->_escape($action) . "' 
			ORDER BY 
				dequeue_datetime DESC ";
		
		if ($arr = $this->_fetch($this->_query($sql, $errnum, $errmsg, 0, 1)))
		{
			return strtotime($arr['dequeue_datetime']);
		}
		
		return null;
	}
	
	/**
	 * Get the last time an action of this type *and* with this ident was dequeued successfully for this user
	 * 
	 * @param string $user
	 * @param string $action
	 * @param string $ident
	 * @return integer 
	 */
	protected function _queueActionIdentLast($user, $action, $ident)
	{
		$errnum = 0;
		$errmsg = '';
		
		$sql = "
			SELECT
				dequeue_datetime
			FROM
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' AND 
				qb_status = '" . QUICKBOOKS_STATUS_SUCCESS . "' AND 
				qb_action = '" . $this->_escape($action) . "' AND 
				ident = '" . $this->_escape($ident) . "' 
			ORDER BY
				dequeue_datetime DESC ";
		
		$errnum = 0;
		$errmsg = '';
		if ($arr = $this->_fetch($this->_query($sql, $errnum, $errmsg, 0, 1)))
		{
			return strtotime($arr['dequeue_datetime']);
		}
		
		return null;
	}
	
	/**
	 * Remove an item from the queue
	 * 
	 * @param string $user
	 * @param boolean $by_priority
	 * @return array 
	 */
	protected function _queueDequeue($user, $by_priority = false)
	{
		$errnum = 0;
		$errmsg = '';
		
		$sql = "
			SELECT 
				* 
			FROM
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
			WHERE
				qb_username = '" . $this->_escape($user) . "' AND 
				qb_status = '" . QUICKBOOKS_STATUS_QUEUED . "' ";
		
		if ($by_priority)
		{
			$sql .= ' ORDER BY priority DESC ';
		}
		
		return $this->_fetch($this->_query($sql, $errnum, $errmsg, 0, 1));
	}
	
	/**
	 * Tell how many items are in the queue
	 * 
	 * @return integer
	 */
	protected function _queueLeft($user, $queued = true)
	{
		$errnum = 0;
		$errmsg = '';
		
		// SELECT * FROM quickbooks_queue WHERE qb_status = 'q'
		$sql = "
			SELECT 
				COUNT(*) AS num_left 
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' ";
				
		if ($queued)
		{
			$sql .= " AND qb_status = '" . QUICKBOOKS_STATUS_QUEUED . "' ";
		}
		
		$arr = $this->_fetch($this->_query($sql, $errnum, $errmsg));
		
		return $arr['num_left'];
	}
	
	/**
	 * Tell how many items are in the queue table (queued or dequeued)
	 * 
	 * @param string $match
	 * @return integer
	 */
	protected function _queueSize($match = '')
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		if (strlen($match))
		{
			
		}
		else
		{
			$sql = "SELECT COUNT(*) AS total FROM " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE);
		}
		
		$arr = $this->_fetch($this->_query($sql, $errnum, $errmsg));
		return $arr['total'];
	}
	
	/**
	 * Search for items in the queue
	 * 
	 * @param integer $offset
	 * @param integer $limit
	 * @param string $match
	 * @return QuickBooks_Iterator
	 */
	protected function _queueView($offset, $limit, $match)
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		$list = array();
		
		if (strlen($match))
		{
			
		}
		else
		{
			$sql = "
				SELECT 
					* 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
				ORDER BY 
					enqueue_datetime DESC ";
			$res = $this->_query($sql, $errnum, $errmsg, $offset, $limit);
			
			while ($arr = $this->_fetch($res))
			{
				$list[] = $arr;
			}
		}
		
		return new QuickBooks_Iterator($list);
	}
	
	/**
	 * Update the status of an item in the queue
	 * 
	 * @param string $ticket
	 * @param string $action
	 * @param mixed $ident
	 * @param char $status
	 * @param string $msg
	 * @return boolean 
	 */
	protected function _queueStatus($ticket, $action, $ident, $new_status, $msg = null)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($ticket_id = $this->_ticketResolve($ticket))
		{
			$user = $this->authResolve($ticket);
			
			//print('action: ' . $action . ', ident: ' . $ident . ', new status: ' . $new_status . ', ticket_id: ' . $ticket_id . ', user: ' . $user . ', msg: ' . $msg);
			
			if ($new_status == QUICKBOOKS_STATUS_SUCCESS)
			{
				$this->_query("
					UPDATE 
						" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " 
					SET 
						processed = processed + 1 
					WHERE 					
						quickbooks_ticket_id = " . (int) $ticket_id . " ", $errnum, $errmsg);
			}
			
			if ($new_status == QUICKBOOKS_STATUS_PROCESSING)
			{
				$this->_query("
					UPDATE 
						" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . "
					SET
						qb_status = '" . $this->_escape($new_status) . "', 
						msg = '" . $this->_escape($msg) . "',
						quickbooks_ticket_id = " . (int) $ticket_id . ", 
						dequeue_datetime = '" . date('Y-m-d H:i:s') . "' 
					WHERE 
						qb_username = '" . $this->_escape($user) . "' AND 
						qb_action = '" . $this->_escape($action) . "' AND 
						ident = '" . $this->_escape($ident) . "' AND 
						qb_status = '" . QUICKBOOKS_STATUS_QUEUED . "' ", $errnum, $errmsg, 0, 1);
			}
			else if ($new_status == QUICKBOOKS_STATUS_SUCCESS)
			{
				// You can only update to a SUCCESS status if you're currently 
				//	in a PROCESSING status
				
				$sql = "
					UPDATE
						" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
					SET 
						qb_status = '" . $this->_escape($new_status) . "', 
						msg = '" . $this->_escape($msg) . "' 
					WHERE
						quickbooks_ticket_id = " . (int) $ticket_id . " AND 
						qb_username = '" . $this->_escape($user) . "' AND 
						qb_action = '" . $this->_escape($action) . "' AND 
						ident = '" . $this->_escape($ident) . "' AND 
						qb_status = '" . QUICKBOOKS_STATUS_PROCESSING . "' ";
				
				$this->_query($sql, $errnum, $errmsg, 0, 1);
			}
			else
			{
				// There are some statuses which *can not be updated* because 
				//	they're already removed from the queue. These are listed in 
				//	the NOT IN section
				
				$sql = "
					UPDATE
						" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
					SET 
						qb_status = '" . $this->_escape($new_status) . "', 
						msg = '" . $this->_escape($msg) . "' 
					WHERE
						quickbooks_ticket_id = " . (int) $ticket_id . " AND 					
						qb_username = '" . $this->_escape($user) . "' AND 
						qb_action = '" . $this->_escape($action) . "' AND 
						ident = '" . $this->_escape($ident) . "' AND
						qb_status NOT IN ( 
							'" . QUICKBOOKS_STATUS_SUCCESS . "', 
							'" . QUICKBOOKS_STATUS_HANDLED . "', 
							'" . QUICKBOOKS_STATUS_CANCELLED . "' ) ";
				
				$this->_query($sql, $errnum, $errmsg, 0, 1);
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Tell how many items have been processed during this session
	 * 
	 * @param string $ticket
	 * @return integer
	 */
	protected function _queueProcessed($ticket)
	{
		$errnum = 0;
		$errmsg = '';
		
		if ($arr = $this->_fetch($this->_query("
			SELECT 
				processed 
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE) . " 
			WHERE 
				ticket = '" . $this->_escape($ticket) . "' ", $errnum, $errmsg)))
		{
			return $arr['processed'];
		}
		
		return 0;
	}
	
	/**
	 * Tell whether or not an item exists in the queue
	 * 
	 * @param string $action
	 * @param mixed $ident
	 * @return boolean
	 */
	protected function _queueExists($user, $action, $ident)
	{
		$errnum = 0;
		$errmsg = '';
		
		return $this->_count($this->_query("
			SELECT 
				quickbooks_queue_id
			FROM
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE) . " 
			WHERE
				qb_username = '" . $this->_escape($user) . "' AND 
				qb_action = '" . $this->escape($action) . "' AND 
				ident = '" . $this->escape($ident) . "' AND 
				qb_status = '" . QUICKBOOKS_STATUS_QUEUED . "' ", $errnum, $errmsg)) > 0;
	}
	
	/**
	 * Resolve a mapping of a unique application ID to a QuickBooks ListID or TxnID
	 * 
	 * @param string $user				The username to look up mappings for
	 * @param string $objecttype		The type of object (see: QUICKBOOKS_OBJECT_*)
	 * @param mixed $uniqueid			The unique application ID
	 * @param string $editsequence		The edit sequence (if known/stored) will be returned here
	 * @param mixed $extra				Any extra data you stored with the mapping will be placed here 
	 * @return string					The QuickBooks ListID or TxnID 
	 */
	protected function _identToQuickBooks($user, $objecttype, $uniqueid, &$editsequence, &$extra)
	{
		$errnum = 0;
		$errmsg = '';
		
		$res = $this->_query("
			SELECT 
				qb_ident, 
				editsequence, 
				extra
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' AND 
				qb_object = '" . $this->_escape($objecttype) . "' AND 
				unique_id = '" . $this->_escape($uniqueid) . "' ", $errnum, $errmsg);
		if ($this->_count($res))
		{
			$arr = $this->_fetch($res);
			
			$editsequence = $arr['editsequence'];
			
			if (strlen($arr['extra']))
			{
				$extra = unserialize($arr['extra']);
			}
			
			return $arr['qb_ident'];
		}
		
		return null;
	}
	
	/**
	 * Resolve a mapping of a QuickBooks ListID or TxnID to a unique application ID
	 * 
	 * @param string $user			The username to resolve the mapping for
	 * @param string $objecttype	The type of object (see: QUICKBOOKS_OBJECT_*)
	 * @param string $qbid			The QuickBooks ListID or TxnID
	 * @param mixed $extra			Any extra data you stored with this mapping
	 * @return mixed				The application unique ID
	 */
	protected function _identToApplication($user, $objecttype, $qbid, &$extra)
	{
		$errnum = 0;
		$errmsg = '';
		
		$res = $this->_query("
			SELECT 
				unique_id, 
				extra
			FROM 
				" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) . " 
			WHERE 
				qb_username = '" . $this->_escape($user) . "' AND 
				qb_object = '" . $this->_escape($objecttype) . "' AND 
				qb_ident = '" . $this->_escape($qbid) . "' ", $errnum, $errmsg);
		if ($this->_count($res))
		{
			$arr = $this->_fetch($res);
			
			if (strlen($arr['extra']))
			{
				$extra = unserialize($arr['extra']);
			}
			
			return $arr['unique_id'];
		}
		
		return null;
	}
	
	/**
	 * Map a QuickBooks identifier (ListID, TxnID, etc.) to a QuickBooks object type and web application identifier
	 * 
	 * @param string $user
	 * @param string $object
	 * @param mixed $uniqueid
	 * @param string $qb_ident
	 * @param string $editsequence
	 * @param mixed $extra				Any extra data you want to store with the mapping
	 * @return boolean 
	 */
	protected function _identMap($user, $objecttype, $uniqueid, $qb_ident, $editsequence = '', $extra = null)
	{
		if ($user and $objecttype and $uniqueid and $qb_ident)
		{
			$errnum = 0;
			$errmsg = '';
			
			// Remove any conflicting records... 
			$this->_query("
				DELETE FROM
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) . " 
				WHERE 
					qb_username = '" . $this->_escape($user) . "' AND 
					qb_object = '" . $this->_escape($objecttype) . "' AND 
					unique_id = '" . $this->_escape($uniqueid) . "' ", $errnum, $errmsg);
			
			// Insert the new mapping
			return $this->_query("
				INSERT INTO
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) . " 
				( 
					qb_username, 
					qb_object, 
					unique_id, 
					qb_ident, 
					editsequence, 
					extra, 
					map_datetime
				)
				VALUES
				(
					'" . $this->_escape($user) . "', 
					'" . $this->_escape($objecttype) . "', 
					'" . $this->_escape($uniqueid) . "', 
					'" . $this->_escape($qb_ident) . "', 
					'" . $this->_escape($editsequence) . "', 
					'" . $this->_escape(serialize($extra)) . "', 
					'" . date('Y-m-d H:i:s') . "'
				) ", $errnum, $errmsg);
		}
		
		return false;
	}
	
	/**
	 * 
	 * 
	 * @param integer $offset
	 * @param integer $limit
	 * @param string $match
	 * @return QuickBooks_Iterator
	 */
	protected function _identView($offset, $limit, $match)
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		$list = array();
		
		if (strlen($match))
		{
			
		}
		else
		{
			$sql = "
				SELECT 
					* 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) . " 
				ORDER BY 
					qb_username, qb_object, unique_id ";
			$res = $this->_query($sql, $errnum, $errmsg, $offset, $limit);
			
			while ($arr = $this->_fetch($res))
			{
				$list[] = $arr;
			}
		}
		
		return new QuickBooks_Iterator($list);
	}
	
	/**
	 * 
	 * 
	 * @return integer
	 */
	protected function _identSize($match)
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		$list = array();
		
		if (strlen($match))
		{
			return 0;
		}
		else
		{
			$arr = $this->_fetch($this->_query("SELECT COUNT(*) AS total FROM " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE) . " ", $errnum, $errmsg));
			return $arr['total'];
		}
	}
	
	/**
	 * Truncate (if neccessary) the log and queue tables if they grow too large
	 * 
	 * @param string $table
	 * @param integer $max_history
	 * @return void
	 */
	protected function _truncate($table, $max_history)
	{
		switch ($table)
		{
			case QUICKBOOKS_DRIVER_SQL_LOGTABLE:
				$sql = "SELECT quickbooks_log_id FROM " . $this->_mapTableName($table) . " ORDER BY quickbooks_log_id ASC LIMIT ";
				$field = 'quickbooks_log_id';
				break;
			case QUICKBOOKS_DRIVER_SQL_QUEUETABLE:
				$sql = "
					SELECT
						quickbooks_queue_id 
					FROM
						" . $this->_mapTableName($table) . " 
					WHERE
						qb_status IN ( 
							'" . QUICKBOOKS_STATUS_SUCCESS . "', 
							'" . QUICKBOOKS_STATUS_HANDLED . "', 
							'" . QUICKBOOKS_STATUS_CANCELLED . "', 
							'" . QUICKBOOKS_STATUS_NOOP . "' ) 
					ORDER BY
						quickbooks_queue_id ASC LIMIT ";
				$field = 'quickbooks_queue_id';
				break;
			case QUICKBOOKS_DRIVER_SQL_TICKETTABLE:
				$sql = "SELECT quickbooks_ticket_id FROM " . $this->_mapTableName($table) . " ORDER BY quickbooks_ticket_id ASC LIMIT ";
				$field = 'quickbooks_ticket_id';
				break;
		}
		
		// How big is the log file? Should we auto-truncate it? 
		$errnum = 0;
		$errmsg = '';
		$arr = $this->_fetch($this->_query("SELECT COUNT(*) AS counter FROM " . $this->_mapTableName($table), $errnum, $errmsg));
		if ($arr['counter'] > $max_history and 
			$max_history > 0)
		{
			// Truncate the log to the size specified
			
			$start = time();
			$cutoff = 5; 		// 5 seconds max cutoff time to avoid timeouts
			
			$limit = 100;
			$list = array();
			
			$errnum = 0;
			$errmsg = '';
			$res = $this->_query($sql . floor($max_history / 2), $errnum, $errmsg);
			while ($arr = $this->_fetch($res) and time() - $start < $cutoff)
			{
				// Delete it batches of $limit, keep under $cutoff seconds
				$list[] = current($arr);
				
				if (count($list) > $limit)
				{
					$errnum = 0;
					$errmsg = '';
					
					$this->_query("DELETE FROM " . $this->_mapTableName($table) . " WHERE " . $field . " IN ( " . implode(', ', $list) . " )", $errnum, $errmsg);
					$list = array();
				}
			}
		}
		
		return;
	}
	
	/**
	 * Write a message to the log file
	 * 
	 * @param string $msg
	 * @param string $ticket
	 * @param integer $log_level
	 * @return boolean
	 */
	protected function _log($msg, $ticket = null, $log_level = QUICKBOOKS_LOG_NORMAL)
	{
		static $batch = 0;
		if ($batch == 0)
		{
			// We store a batch ID so that we can tell which logged messages go with which actual separate HTTP request
			
			$errnum = 0;
			$errmsg = '';
			
			if ($arr = $this->_fetch($this->_query("SELECT MAX(batch) AS maxbatch FROM " . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE) . " ", $errnum, $errmsg)))
			{
				$batch = (int) $arr['maxbatch'];
			}
			
			$batch++;
		}
		
		// Truncate log and queue tables
		$this->_truncate(QUICKBOOKS_DRIVER_SQL_LOGTABLE, $this->_max_log_history);
		$this->_truncate(QUICKBOOKS_DRIVER_SQL_QUEUETABLE, $this->_max_queue_history);
		$this->_truncate(QUICKBOOKS_DRIVER_SQL_TICKETTABLE, $this->_max_ticket_history);
		
		// Actually insert the log message...
		$errnum = 0;
		$errmsg = '';
		
		// Make sure the message isn't too long
		$msg = substr($msg, 0, 65534);
		
		// Log level handling is handled by the QuickBooks_Driver base class (see public method {@link QuickBooks_Driver::log()})
		if ($ticket_id = $this->_ticketResolve($ticket))
		{
			return $this->_query("
				INSERT INTO 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE) . " 
				( 
					quickbooks_ticket_id,
					batch, 
					msg, 
					log_datetime 
				) VALUES ( 
					" . $ticket_id . ",
					" . $batch . ", 
					'" . $this->_escape($msg) . "', 
					'" . date('Y-m-d H:i:s') . "' ) ", $errnum, $errmsg);
		}
		else
		{
			return $this->_query("
				INSERT INTO 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE) . " 
				(
					batch,
					msg, 
					log_datetime
				) VALUES (
					" . $batch . ", 
					'" . $this->_escape($msg) . "',
					'" . date('Y-m-d H:i:s') . "' ) ", $errnum, $errmsg);
		}
	}

	/**
	 * 
	 * 
	 * @param integer $offset
	 * @param integer $limit
	 * @param string $match
	 * @return QuickBooks_Iterator
	 */
	protected function _logView($offset, $limit, $match)
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		$list = array();
		
		if (strlen($match))
		{
			
		}
		else
		{
			$sql = "
				SELECT 
					* 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE) . " 
				ORDER BY 
					quickbooks_log_id DESC ";
			$res = $this->_query($sql, $errnum, $errmsg, $offset, $limit);
			
			while ($arr = $this->_fetch($res))
			{
				$list[] = $arr;
			}
		}
		
		return new QuickBooks_Iterator($list);
	}
	
	protected function _logSize($match)
	{
		$errnum = 0;
		$errmsg = '';
		
		$match = trim($match);
		
		if (strlen($match))
		{
			return 0;
		}
		else
		{
			$arr = $this->_fetch($this->_query("
				SELECT 
					COUNT(*) AS total 
				FROM 
					" . $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE) . " ", $errnum, $errmsg));
			return $arr['total'];
		}
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	protected function _noop()
	{
		$errnum = 0;
		$errmsg = '';
		$tmp = $this->_fetch($this->_query("SELECT 42 + 42 AS thesum ", $errnum, $errmsg));
		return $tmp['thesum'] == 84;
	}
	
	/**
	 * Execute an SQL query against the database
	 * 
	 * @param string $sql
	 * @param integer $errnum
	 * @param string $errmsg
	 * @return resource
	 */
	public abstract function query($sql, &$errnum, &$errmsg, $offset = 0, $limit = null);
	
	/**
	 * Escape a string
	 * 
	 * @param string $str
	 * @return string
	 */
	public abstract function escape($str);
	
	/**
	 * Fetch a row from a result set
	 * 
	 * @param resource $res
	 * @return array
	 */
	public abstract function fetch($res);
	
	/**
	 * Get the number of rows the last query affected
	 * 
	 * @return integer
	 */
	public abstract function affected();
	
	/**
	 * Get the last sequence value from the last SQL insert
	 * 
	 * @return integer
	 */
	public abstract function last();
	
	/**
	 * Get a count of the number of results in an SQL result set
	 * 
	 * @param resource $res
	 * @return integer
	 */
	public abstract function count($res);
	
	/**
	 * Map a default table name to a database-specific table name
	 * 
	 * @param string $table
	 * @return string
	 */
	protected function _mapTableName($table)
	{
		return QUICKBOOKS_DRIVER_SQL_PREFIX . $table;
	}
	
	/**
	 * Map a default encryption salt to a database-specific salt
	 * 
	 * @param string $salt
	 * @return string
	 */
	protected function _mapSalt($salt)
	{
		return $salt;
	}
	
	/**
	 * 
	 * 
	 */
	protected function _generateCreatePrimaryKey($table, $key, $serial = true)
	{
		
	}
	
	/**
	 * 
	 * 
	 */
	protected function _generateCreateForeignKey($table, $this_field, $references_that_field)
	{
		
	}
	
	/**
	 * 
	 */
	protected function _generateFieldSchema($name, $def)
	{
		$sql = '';
		
		switch ($def[0])
		{
			case QUICKBOOKS_DRIVER_SQL_INTEGER:
				$sql .= $name . ' INTEGER ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
					else
					{
						$sql .= ' DEFAULT ' . (int) $def[2];
					}
				}
				
				break;
			case QUICKBOOKS_DRIVER_SQL_DECIMAL:
				$sql .= $name . ' DECIMAL ';
		
				if (!empty($def[1]))
				{
					$tmp = explode(",", $def[1]);
					if(count($tmp) == 2)
						$sql .= '(' . (int) $tmp[0] . ',' . (int) $tmp[1] . ') ';
				}
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' NULL ';
					}
					else
					{
						if(isset($tmp) && count($tmp) == 2)
							$sql .= ' DEFAULT ' . sprintf('%01.'.(int)$tmp[1].'f', (float) $def[2]);
						else
							$sql .= ' DEFAULT ' . sprintf('%01.2f', (float) $def[2]);
					}
				}
				if(isset($tmp))
					unset($tmp);
				
				break;
			case QUICKBOOKS_DRIVER_SQL_FLOAT:
				$sql .= $name . ' FLOAT ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' NULL ';
					}
					else
					{
						$sql .= ' DEFAULT ' . sprintf('%01.2f', (float) $def[2]);
					}
				}
				
				break;
			case QUICKBOOKS_DRIVER_SQL_BOOLEAN:
				$sql .= $name . ' BOOLEAN ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
					else if ($def[2])
					{
						$sql .= ' DEFAULT TRUE ';
					}
					else
					{
						$sql .= ' DEFAULT FALSE ';
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				break;
			case QUICKBOOKS_DRIVER_SQL_DATE:
				$sql .= $name . ' DATE ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				break;
			case QUICKBOOKS_DRIVER_SQL_TIME:
				
				
				
				break;
			case QUICKBOOKS_DRIVER_SQL_DATETIME:
				$sql .= $name . ' DATETIME ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				break;
			case QUICKBOOKS_DRIVER_SQL_VARCHAR:
				$sql .= $name . ' VARCHAR';
				
				/*if ($name == 'ListID')
				{
					print('LIST ID:');
					print_r($def);
				}*/
				
				if (!empty($def[1]))
				{
					$sql .= '(' . (int) $def[1] . ') ';
				}
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
					else if ($def[2] === false)
					{
						$sql .= ' NOT NULL ';
					}
					else
					{
						$sql .= " NOT NULL DEFAULT '" . $def[2] . "' ";
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				break;
			case QUICKBOOKS_DRIVER_SQL_CHAR:
				$sql .= $name . ' CHAR';
				
				if (!empty($def[1]))
				{
					$sql .= '(' . (int) $def[1] . ') ';
				}
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
					else
					{
						$sql .= " NOT NULL DEFAULT '" . $def[2] . "' ";
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				break;
			default:
			case QUICKBOOKS_DRIVER_SQL_TEXT:
				$sql .= $name . ' TEXT ';
				
				if (isset($def[2]))
				{
					if (strtolower($def[2]) == 'null')
					{
						$sql .= ' DEFAULT NULL ';
					}
					else
					{
						$sql .= " NOT NULL DEFAULT '" . $def[2] . "' ";
					}
				}
				else
				{
					$sql .= ' NOT NULL ';
				}
				
				break;
		}
		
		return $sql;
	}
	
	protected function _generateCreateTable($name, $arr, $primary = array(), $keys = array())
	{
		$sql = '';
		
		foreach ($arr as $field => $def)
		{
			$sql .= "\t" . $this->_generateFieldSchema($field, $def) . ', ' . "\n";
		}
		
		return array(
			'CREATE TABLE ' . $name . ' ( ' . "\n" . substr($sql, 0, -3) . ' ); ',
			);
	}
	
	protected function _initialize($init_options = array())
	{
		$defaults = array(
			//'quickbooks_api_enabled' => false, 
			//'quickbooks_api_debug' => false, 
			//'quickbooks_api_droptable' => false, 
			//'quickbooks_api_print' => false, 
			
			'quickbooks_sql_enabled' => false, 
			'quickbooks_sql_schema' => realpath(dirname(__FILE__) . '/../data/schema'),
			'quickbooks_sql_debug' => false, 
			'quickbooks_sql_droptable' => false, 
			'quickbooks_sql_prefix' => QUICKBOOKS_DRIVER_SQL_PREFIX_SQL,
			'quickbooks_sql_print' => false, 
			
			'quickbooks_integrator_enabled' => false, 
			'quickbooks_integrator_prefix' => QUICKBOOKS_DRIVER_SQL_PREFIX_INTEGRATOR, 
			);
		
		$config = array_merge($defaults, $init_options);
		
		// list of SQL statements to run
		$arr_sql = array();
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_LOGTABLE);
		$def = array(
			'quickbooks_log_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ), 
			'quickbooks_ticket_id' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, null, 'null' ),
			'batch' => array( QUICKBOOKS_DRIVER_SQL_INTEGER ), 
			'msg' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
			'log_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_log_id';
		$keys = array( 'quickbooks_ticket_id', 'batch' );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_QUEUETABLE);
		$def = array(
			'quickbooks_queue_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ), 
			'quickbooks_ticket_id' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, null, 'null' ), 
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_action' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 32 ), 
			'ident' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'extra' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null, 'null' ), 
			'qbxml' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null, 'null' ), 
			'priority' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, 3, 0 ), 
			'qb_status' => array( QUICKBOOKS_DRIVER_SQL_CHAR, 1 ), 
			'msg' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null, 'null' ), 
			'enqueue_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'dequeue_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME, null, 'null' ), 
			);
		$primary = 'quickbooks_queue_id';
		$keys = array( 'quickbooks_ticket_id', 'priority', array( 'qb_username', 'qb_action', 'ident', 'qb_status' ), 'qb_status' );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_RECURTABLE);
		$def = array(
			'quickbooks_recur_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ), 
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_action' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 32 ), 
			'ident' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'extra' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null, 'null' ), 
			'qbxml' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null, 'null' ), 
			'priority' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, 3, 0 ), 
			'run_every' => array( QUICKBOOKS_DRIVER_SQL_INTEGER ), 
			'recur_lasttime' => array( QUICKBOOKS_DRIVER_SQL_INTEGER ), 
			'enqueue_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_recur_id';
		$keys = array( array( 'qb_username', 'qb_action', 'ident' ), 'priority' );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_TICKETTABLE);
		$def = array(
			'quickbooks_ticket_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ), 
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'ticket' => array( QUICKBOOKS_DRIVER_SQL_CHAR, 32 ), 
			'processed' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, null, 0 ), 
			'lasterror_num' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 32, 'null' ), 
			'lasterror_msg' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255, 'null' ), 
			'ipaddr' => array( QUICKBOOKS_DRIVER_SQL_CHAR, 15 ), 
			'write_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'touch_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_ticket_id';
		$keys = array( 'ticket' );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_USERTABLE);
		$def = array(
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_password' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255 ), 
			'qb_company_file' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255, 'null' ), 
			'qbwc_wait_before_next_update' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, null, 0 ), 
			'qbwc_min_run_every_n_seconds' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, null, 0 ), 
			'status' => array( QUICKBOOKS_DRIVER_SQL_CHAR, 1 ), 
			'write_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'touch_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'qb_username';
		$keys = array(  );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_IDENTTABLE);
		$def = array(
			'quickbooks_ident_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_object' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'unique_id' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_ident' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
			'editsequence' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),  
			'extra' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null, 'null' ), 
			'map_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_ident_id';
		$keys = array(  );
		$uniques = array( array( 'qb_username', 'qb_object', 'unique_id' ) );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONFIGTABLE);
		$def = array(
			'quickbooks_config_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'module' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'cfgkey' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'cfgval' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'cfgtype' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'cfgopts' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null ), 
			'write_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'mod_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_config_id';
		$keys = array(  );
		$uniques = array( array( 'qb_username', 'module', 'cfgkey' ) );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_NOTIFYTABLE);
		$def = array(
			'quickbooks_notify_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_object' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'unique_id' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_ident' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'errnum' => array( QUICKBOOKS_DRIVER_SQL_INTEGER, null, 'null' ), 
			'errmsg' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null ), 
			'note' => array( QUICKBOOKS_DRIVER_SQL_TEXT, null ), 
			'priority' => array( QUICKBOOKS_DRIVER_SQL_INTEGER ), 
			'write_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'mod_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_notify_id';
		$keys = array(  );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		
		$table = $this->_mapTableName(QUICKBOOKS_DRIVER_SQL_CONNECTIONTABLE);
		$def = array(
			'quickbooks_connection_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ), 
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
			'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 12 ), 
			'certificate' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255, 'null' ), 
			'connection' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
			'application_id' => array( QUICKBOOKS_DRIVER_SQL_INTEGER ), 
			'application_login' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
			'lasterror_num' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 32, 'null' ), 
			'lasterror_msg' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255, 'null' ), 
			'connection_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'write_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			'touch_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
			);
		$primary = 'quickbooks_connection_id';
		$keys = array(  );
		$uniques = array(  );
		
		$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));

		//header('Content-Type: text/plain');
		//print_r($arr_sql);
		//exit;
		
		// Support for specialized integrator stuff
		if ($config['quickbooks_integrator_enabled'])
		{
			$table = 'qb_integrator_item';
			$def = array(
				'qbintegrator_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
				'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'qb_itemtype' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
				'ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'TimeCreated' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'TimeModified' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'EditSequence' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
				'Name' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
				'IsActive' => array( QUICKBOOKS_DRIVER_SQL_BOOLEAN ), 
				'Parent_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'Parent_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
				'ManufacturerPartNumber' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 31, 'null' ), 
				'Account_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
				'Account_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT, 'null' ), 
				'IncomeAccount_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
				'IncomeAccount_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT, 'null' ), 
				'COGSAccount_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
				'COGSAccount_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT, 'null' ), 
				'AssetAccount_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
				'AssetAccount_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT, 'null' ), 
				'ExpenseAccount_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
				'ExpenseAccount_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT, 'null' ), 
				'PrefVendor_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' ), 
				'PrefVendor_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT, 'null' ), 
				);
			$primary = 'qbintegrator_id';
			$keys = array( 'qb_username', 'Name' );
			$uniques = array( );
			
			$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
			
			// @Todo Change these to constants in QuickBooks/Integrator.php
			$table = 'qb_integrator_account';
			$def = array(
				'qbintegrator_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
				'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'TimeCreated' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'TimeModified' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'EditSequence' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
				'Name' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
				'IsActive' => array( QUICKBOOKS_DRIVER_SQL_BOOLEAN ), 
				'Parent_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'Parent_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
				'AccountType' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'SpecialAccountType' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'AccountNumber' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'CashFlowClassification' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 				
				);
			$primary = 'qbintegrator_id';
			$keys = array( 'qb_username', 'Name', 'AccountType' );
			$uniques = array( );
			
			$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
			
			$table = 'qb_integrator_paymentmethod';
			$def = array(
				'qbintegrator_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
				'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'TimeCreated' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'TimeModified' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'EditSequence' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
				'Name' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'IsActive' => array( QUICKBOOKS_DRIVER_SQL_BOOLEAN ), 
				'PaymentMethodType' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				);
			$primary = 'qbintegrator_id';
			$keys = array( 'qb_username', 'Name' );
			$uniques = array( );
			
			$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));	
			
			$table = 'qb_integrator_shipmethod';
			$def = array(
				'qbintegrator_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
				'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'TimeCreated' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'TimeModified' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'EditSequence' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
				'Name' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'IsActive' => array( QUICKBOOKS_DRIVER_SQL_BOOLEAN ), 
				);
			$primary = 'qbintegrator_id';
			$keys = array( 'qb_username', 'Name' );
			$uniques = array( );
			
			$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));				
			
			// CustomerTypes
			$table = 'qb_integrator_customertype';
			$def = array(
				'qbintegrator_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
				'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'TimeCreated' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'TimeModified' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'EditSequence' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ), 
				'Name' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 100 ), 
				'FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
				'IsActive' => array( QUICKBOOKS_DRIVER_SQL_BOOLEAN ), 
				'Parent_ListID' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'Parent_FullName' => array( QUICKBOOKS_DRIVER_SQL_TEXT ), 
				);
			$primary = 'qbintegrator_id';
			$keys = array( 'qb_username', 'Name' );
			$uniques = array( );
			
			$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
			
			// mapping table
			$table = 'qb_integrator_mapping';
			$def = array(
				'qbintegrator_id' => array( QUICKBOOKS_DRIVER_SQL_SERIAL ),
				'qb_username' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40 ),
				'map_datetime' => array( QUICKBOOKS_DRIVER_SQL_DATETIME ), 
				'QuickBooks_Name_or_RefNumber' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255 ), 
				'Integrator_Name_or_RefNumber' => array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 255 ),
				);
			$primary = 'qbintegrator_id';
			$keys = array( 'qb_username', 'Integrator_Name_or_RefNumber' );
			$uniques = array( array( 'qb_username', 'QuickBooks_Name_or_RefNumber', 'Integrator_Name_or_RefNumber' ) );
			
			$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($table, $def, $primary, $keys, $uniques));
		}
		
		// Support for mirroring the QuickBooks database in an SQL database
		if ($config['quickbooks_sql_enabled'])
		{
			$tables = array();
			
			// Use the QuickBooks_SQL_Schema class 
			$dh = opendir($defaults['quickbooks_sql_schema']);
			while (false !== ($file = readdir($dh)))
			{
				if ($file{0} == '.')
				{
					continue;
				}
				
				$xml = file_get_contents($defaults['quickbooks_sql_schema'] . '/' . $file);
				
				QuickBooks_SQL_Schema::mapSchemaToSQLDefinition($xml, $tables);
				
				// This times out on some SQL connections because it takes so darn long to generate the
				//	schema. Thus, we're going to issue a few useless queries here, just so we don't lose
				//	the connection to the database.
				$this->noop();
			}
			
			// A table has to be created for each query type, and each table has to have some extra fields added to it
			foreach ($tables as $table)
			{
				// @TODO Support other transformations (table names to uppercase, field names to lowercase, etc.)
				$name = strtolower($config['quickbooks_sql_prefix'] . $table[0]);
				
				$idfield = array( QUICKBOOKS_DRIVER_SQL_SERIAL, null, 0 );
				
				$ifield = array( QUICKBOOKS_DRIVER_SQL_DATETIME, null, 'null' );		// Date/time when first inserted
				$ufield = array( QUICKBOOKS_DRIVER_SQL_DATETIME, null, 'null' );		// Date/time when updated (re-sync from QuickBooks)
				$mfield = array( QUICKBOOKS_DRIVER_SQL_TIMESTAMP, null, 'null' );		// Date/time when modified by a user (needs to be pushed to QB)
				$hfield = array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 40, 'null' );
				//$dfield = array( QUICKBOOKS_DRIVER_SQL_DATETIME, null, 'null' );		// Date/time when deleted by a user (needs to be deleted from QB)
				//$cfield = array( QUICKBOOKS_DRIVER_SQL_TIMESTAMP_ON_INSERT, null, 'NOW()' );
				//$mfield = array( QUICKBOOKS_DRIVER_SQL_TIMESTAMP_ON_INSERT_OR_UPDATE, null, 'NOW()' );
				
				// This should be an VARCHAR, QuickBooks errors are sometimes in the format "0x12341234" 
				$enfield = array( QUICKBOOKS_DRIVER_SQL_VARCHAR, 32, 'null' );			// Add/Mod error number
				$emfield = array( QUICKBOOKS_DRIVER_SQL_TEXT, null, null );				// Add/Mod error message
				$enqfield = array( QUICKBOOKS_DRIVER_SQL_DATETIME, null, 'null' );		// Add/Mod enqueue date/time
				$deqfield = array( QUICKBOOKS_DRIVER_SQL_DATETIME, null, 'null' );		// Add/Mod dequeue date/time
				
				$to_delete_field = array( QUICKBOOKS_DRIVER_SQL_BOOLEAN, null, 0 );		// Flag it for deletion
				$to_skip_field = array( QUICKBOOKS_DRIVER_SQL_BOOLEAN, null, 0 );		// Flag it for skipping
				$to_sync_field = array( QUICKBOOKS_DRIVER_SQL_BOOLEAN, null, 0 );
				
				$flag_deleted_field = array( QUICKBOOKS_DRIVER_SQL_BOOLEAN, null, 0 );	// This has been deleted within QuickBooks
				$flag_skipped_field = array( QUICKBOOKS_DRIVER_SQL_BOOLEAN, null, 0 );	// This has been skipped within the sync to QuickBooks
				
				$fields = $table[1];
				
				$fields = array_merge( array( QUICKBOOKS_DRIVER_SQL_FIELD_ID => $idfield ), $fields );
				
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_DISCOVER] = $ifield;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_RESYNC] = $ufield;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_MODIFY] = $mfield;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_HASH] = $hfield;
				//$fields[QUICKBOOKS_DRIVER_SQL_FIELD_DELETE] = $dfield;
				
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_NUMBER] = $enfield;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE] = $emfield;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_ENQUEUE_TIME] = $enqfield;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_DEQUEUE_TIME] = $deqfield;
				
				//$fields[QUICKBOOKS_DRIVER_SQL_FIELD_DELETED_FLAG] = $delflagfield;
				
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_TO_SYNC] = $to_sync_field;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_TO_DELETE] = $to_delete_field;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_TO_SKIP] = $to_skip_field;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_FLAG_DELETED] = $flag_deleted_field;
				$fields[QUICKBOOKS_DRIVER_SQL_FIELD_FLAG_SKIPPED] = $flag_skipped_field;
                
				$primary = QUICKBOOKS_DRIVER_SQL_FIELD_ID;
				//$keys = array();
				//$uniques = array( $table[2] );
				//$uniques = array();
				
				
				$keys = $table[3];
				$uniques = $table[4];
				
				// @TODO Fix this to support unique keys
				$keys = array_merge($keys, $uniques);
				
				/*
				print('keys: ');
				print_r($keys);
				print("\n\n");
				print('uniques: ');
				print_r($uniques);
				exit;
				*/
				
				$arr_sql = array_merge($arr_sql, $this->_generateCreateTable($name, $fields, $primary, $keys));
			}
		}
		
		// Run each CREATE TABLE statement... 
		foreach ($arr_sql as $sql)
		{
			if ($config['quickbooks_sql_debug'] or $config['quickbooks_sql_print'])
			{
				print($sql . "\n\n");
			}
			else
			{
				$errnum = 0;
				$errmsg = '';
				
				//print($sql);
				
				$this->_query($sql, $errnum, $errmsg);
			}
		}
		
		//exit;
	}
	
	/**
	 * 
	 * 
	 * @param string $table
	 * @param array $restrict
	 * @return object
	 */
	public function get($table, $restrict)
	{
		$sql = '';
		$where = array();
		
		if (count($restrict))
		{
			foreach ($restrict as $field => $value)
			{
				$where[] = $field . " = '" . $this->_escape($value) . "' ";
			}
			
			$errnum = 0;
			$errmsg = '';
			if ($res = $this->_query("SELECT * FROM " . $this->_escape($table) . " WHERE " . implode(' AND ', $where) . " ", $errnum, $errmsg, 0, 1) and 
				$arr = $this->_fetch($res))
			{
				// @TODO This should be returning an array, not dependent on something outside of it's class tree... 
				return new QuickBooks_SQL_Object($table, null, $arr);
			}
		}
		
		return false;
	}
	
	/**
	 * Update a record in the SQL table
	 * 
	 * @todo We should make this support only passing a single $where component, instead of an array of array where components
	 * 
	 * @param string $table			The table to update
	 * @param object $object		An object containing a record to update
	 * @param array $where			An array to use to build the WHERE clause
	 * @return boolean
	 */
	public function update($table, $object, $where = array(), $resync = true, $discov = null)
	{
		$sql = '';
		$set = array();
		
		if (is_object($object))
		{
			$object = $object->asArray();
		}
		
		// 
		foreach ($object as $field => $value)
		{
			// Commented out because doing this to very large integers (i.e. ItemRef/FullName is a large integer SKU) causes integer overflow
			/*if (strlen((int) $value) == strlen($value))
			{
				$set[] = $field . ' = ' . (int) $value;
			}
			else
			{*/
				$set[] = $field . " = '" . $this->_escape($value) . "' ";
			//}
		}
		
		$wheres = array();
		foreach ($where as $part)
		{
			foreach ($part as $field => $value)
			{
				$wheres[] = $field . " = '" . $this->_escape($value) . "' ";
			}
		}
		
		$sql = "UPDATE " . $this->_escape($table) . " SET " . implode(', ', $set);
		
		if ($resync)
		{
			$sql .= ", " . QUICKBOOKS_DRIVER_SQL_FIELD_RESYNC . " = NOW() ";
		}
		
		$sql .= " WHERE " . implode(' AND ', $wheres);
		
		$errnum = 0;
		$errmsg = '';
		$return = $this->_query($sql, $errnum, $errmsg);
		
		if (is_null($discov))
		{
			$discov = $resync;
		}
		
		if ($discov)
		{
			// Update the discover datetime *IF THE DISCOVER DATETIME IS NULL*
			//	This happens when an AddResponse is received, and we need to 
			//	update a record that has just been added to QuickBooks. If we 
			//	don't mark it as discovered, then updates to the record will 
			//	never be picked up and sent to QuickBooks
			
			$errnum = 0;
			$errmsg = '';
			
			$wheres[] = QUICKBOOKS_DRIVER_SQL_FIELD_DISCOVER . " IS NULL ";
			
			$this->_query("
				UPDATE 
					" . $this->_escape($table) . "
				SET 
					" . QUICKBOOKS_DRIVER_SQL_FIELD_DISCOVER . " = " . QUICKBOOKS_DRIVER_SQL_FIELD_RESYNC . "
				WHERE
					" . implode(' AND ', $wheres), $errnum, $errmsg);
		}
		
		return $return;
	}
	
	/**
	 * Insert a new record into an SQL table
	 * 
	 * @param string $table
	 * @param object $object
	 * @return boolean
	 */
	public function insert($table, $object, $discov_and_resync = true)
	{
		$sql = '';
		$fields = array();
		$values = array();
		
		if (is_object($object))
		{
			$object = $object->asArray();
		}
		
		foreach ($object as $field => $value)
		{
			$fields[] = $field;
	
			/*
			// * POSSIBLE FIX FOR BELOW CODE *
			
if ( strlen((int) $value) == strlen($value) and
((int)$value) == $value and
ctype_digit($value) )		
			*/
			
			// Commented out because doing this to very large integers (i.e. ItemRef/FullName is a large integer SKU) causes integer overflow
			/*if (strlen((int) $value) == strlen($value))
			{
				$values[] = (int) $value;
			}
			else
			{*/
				$values[] = " '" . $this->_escape($value) . "' ";
			//}
		}
		
		$sql = "INSERT INTO " . $this->_escape($table) . " ( " . implode(', ', $fields) . " ";
		
		if ($discov_and_resync)
		{
			$sql .= ", " . QUICKBOOKS_DRIVER_SQL_FIELD_DISCOVER . ", " . QUICKBOOKS_DRIVER_SQL_FIELD_RESYNC . " ";
		}
		
		$sql .= " ) VALUES ( " . implode(', ', $values) . " ";
		
		if ($discov_and_resync)
		{
			$sql .= ", NOW(), NOW() ";
		}
		
		$sql .= " ); ";
		
		$errnum = 0;
		$errmsg = '';
		return $this->_query($sql, $errnum, $errmsg);
	}
	
	/**
	 * 
	 * 
	 * @param string $table
	 * @param array $where
	 * @return boolean
	 */
	public function delete($table, $where)
	{
		$sql = '';
		
		$wheres = array();
		foreach ($where as $part)
		{
			foreach ($part as $field => $value)
			{
				$wheres[] = $field . " = '" . $this->_escape($value) . "' ";
			}
		}
		
		$sql = "DELETE FROM " . $this->_escape($table);
		
		$sql .= " WHERE " . implode(' AND ', $wheres);
		
		$errnum = 0;
		$errmsg = '';
		return $this->_query($sql, $errnum, $errmsg);
	}
}

