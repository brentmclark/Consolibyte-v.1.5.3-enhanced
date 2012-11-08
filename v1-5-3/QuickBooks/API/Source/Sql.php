<?php

/**
 * 
 * 
 * This is really just a wrapper around the QuickBooks_SQL interface which has 
 * already been specifically designed 
 * 
 * 
 * @package QuickBooks
 * @subpackage API
 */

/**
 * 
 */
require_once 'QuickBooks/API/Source.php';

/**
 * 
 */
require_once 'QuickBooks/SQL.php';

/**
 * 
 */
class QuickBooks_API_Source_SQL extends QuickBooks_API_Source
{
	public function __construct()
	{
		
		
		$this->_sql = new QuickBooks_SQL();
	}
	
	public function sql($sql)
	{
		return $this->_sql->query($sql);
	}
	
	public function fetch($res, $as_object = true, $index = null)
	{
		return $this->_sql->fetch($res, $as_object, $index);
	}
	
public function handleObject($method, $action, $type, $object, $callbacks, $webapp_ID, $priority, &$err)
	{
		return false;
	}
	
	public function handleArray($method, $action, $type, $array, $callbacks, $webapp_ID, $priority, &$err)
	{
		return false;
	}
	
	public function handleQBXML($method, $action, $type, $qbxml, $callbacks, $uniqueid, $priority, &$err)
	{
		return false;
	}
	
	public function  handleSQL($method, $action, $type, $sql, $callbacks, $webapp_ID, $priority, &$err)
	{
		return false;
	}
	
	public function supported()
	{
		return array();
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
	
	public function supportsAdding()
	{
		return false;
	}
	
	public function supportsDeleting()
	{
		return false;
	}
	
	public function supportsModifying()
	{
		return false;
	}
	
	public function supportsQuerying()
	{
		return true;
	}
	
	public function supportsSQL()
	{
		return true;
	}
	
	public function supportsRealtime()
	{
		return true;
	}
	
	public function supportsQBXML()
	{
		return false;
	}
	
	
	public function understandsSQL()
	{
		return false;
	}
	
	public function understandsQBXML()
	{
		return false;
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