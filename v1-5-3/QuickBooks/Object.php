<?php

/**
 * Base class for QuickBooks objects
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Object
 */

/**
 * QuickBooks base class
 */
require_once 'QuickBooks.php';

/**
 * QuickBooks XML parser class
 */
require_once 'QuickBooks/XML/Parser.php';

/**
 * QuickBooks API class
 */
require_once 'QuickBooks/API.php';

/**
 * QuickBooks XML parser option - preserve empty XML elements
 */
define('QUICKBOOKS_OBJECT_XML_PRESERVE', QUICKBOOKS_XML_XML_PRESERVE);

/**
 * QuickBooks XML parser option - drop empty XML elements
 */
define('QUICKBOOKS_OBJECT_XML_DROP', QUICKBOOKS_XML_XML_DROP);

/**
 * QuickBooks XML parser option - compress /> empty XML elements
 */
define('QUICKBOOKS_OBJECT_XML_COMPRESS', QUICKBOOKS_XML_XML_COMPRESS);

/**
 * Base class for QuickBooks objects
 */
abstract class QuickBooks_Object
{
	/**
	 * Keys/values stored within the object
	 * 
	 * @var array
	 */
	protected $_object = array();
	
	/**
	 * Create a new instance of this QuickBooks class
	 * 
	 * @param array $arr
	 */
	public function __construct($arr)
	{
		$this->_object = $arr; 
	}
	
	/**
	 * Return a constant indicating the type of object
	 * 
	 * @return string
	 */
	abstract public function object();
	
	/**
	 * 
	 */
	public function encodeApplicationID($type, $tag, $ID)
	{
		return QuickBooks_API::encodeApplicationID($type, $tag, $ID);
	}
	
	/**
	 * 
	 */
	public function decodeApplicationID($encode, &$type, &$tag, &$ID)
	{
		return QuickBooks_API::decodeApplicationID($encode, $type, $tag, $ID);
	}
	
	/**
	 * 
	 */
	public function extractApplicationID($encode)
	{
		return QuickBooks_API::extractApplicationID($encode);
	}
	
	/**
	 * 
	 * 
	 * @param mixed $value
	 * @return void
	 */
	public function setApplicationID($value)
	{
		$tag = 'ListID';
		if (method_exists($this, 'setTxnID') or method_exists($this, 'getTxnID'))
		{
			$tag = 'TxnID';
		}
		
		return $this->set(QUICKBOOKS_API_APPLICATIONID, $this->encodeApplicationID($this->object(), $tag, $value));
	}
	
	/**
	 * 
	 * 
	 * @return mixed
	 */
	public function getApplicationID()
	{
		return $this->extractApplicationID($this->get(QUICKBOOKS_API_APPLICATIONID));
	}
	
	public function encodeApplicationEditSequence($type, $tag, $ID)
	{
		return QuickBooks_API::encodeApplicationEditSequence($type, $tag, $ID);
	}
	
	public function setApplicationEditSequence($value)
	{
		return $this->set(QUICKBOOKS_API_APPLICATIONEDITSEQUENCE, $this->encodeApplicationEditSequence($this->object(), 'EditSequence', $value));
	}
	
	public function getApplicationEditSequence()
	{
		return $this->extractApplicationEditSequence($this->get(QUICKBOOKS_API_APPLICATIONEDITSEQUENCE));
	}
	
	/**
	 * Get the date/time this object was created in QuickBooks
	 * 
	 * @param string $format		If you want the date/time in a particular format, specify the format here (use the notation from {@link http://www.php.net/date})
	 * @return string
	 */
	public function getTimeCreated($format = null)
	{
		if (!is_null($format))
		{
			return date($format, strtotime($this->get('TimeCreated')));
		}
		
		return $this->get('TimeCreated');
	}

	/** 
	 * Get the date/time when this object was last modified in QuickBooks
	 * 
	 * @param string $format		If you want the date/time in a particular format, specify the format here (use the notation from {@link http://www.php.net/date})
	 * @return string
	 */	
	public function getTimeModified($format = null)
	{
		if (!is_null($format))
		{
			return date($format, strtotime($this->get('TimeModified')));
		}
		
		return $this->get('TimeModified');
	}
	
	public function setEditSequence($value)
	{
		return $this->set('EditSequence', $value);
	}
	
	/**
	 * Get the QuickBooks EditSequence for this object
	 * 
	 * @return integer
	 */
	public function getEditSequence()
	{
		return $this->get('EditSequence');
	}
	
	/**
	 * Set a value within the object
	 * 
	 * @param string $key
	 * @param string $value
	 * @return boolean
	 */
	public function set($key, $value)
	{
		if (is_array($value))
		{
			$this->_object[$key] = $value;
		}
		else
		{
			$this->_object[$key] = $value;
		}
		
		return true;
	}
	
	/**
	 * Get a value from the object
	 * 
	 * @param string $key		The key to fetch the value for
	 * @param mixed $default	If there is no value set for the given key, this will be returned
	 * @return mixed			The value fetched
	 */
	public function get($key, $default = null)
	{
		if (isset($this->_object[$key]))
		{
			return $this->_object[$key];
		}
		
		return $default;
	}
	
	/**
	 * Set a boolean value
	 * 
	 * @param string $key		
	 * @param mixed $value		
	 * @return boolean			
	 */
	public function setBooleanType($key, $value)
	{
		if ($value == 'true' or $value === 1 or $value === true)
		{
			return $this->set($key, 'true');
		}
		
		return $this->set($key, 'false');
	}
	
	/**
	 * 
	 * 
	 * @param string $key
	 * @param boolean $default
	 * @return boolean
	 */
	public function getBooleanType($key, $default = null)
	{
		if ($this->exists($key))
		{ 
			$boolean = $this->get($key);
			if (is_bool($boolean))
			{
				return $boolean;
			}
			else if ($boolean == 'false')
			{
				return false;
			}
			else if ($boolean == 'true')
			{
				return true;
			}
		}
		
		return $default == 'true' or $default === 1 or $default === true;
	}
	
	/**
	 * Set a date 
	 * 
	 * @param string $key		The key for where to store the date
	 * @param mixed $date		The date value (accepts anything www.php.net/strtotime can convert or unix timestamps)
	 * @return boolean
	 */
	public function setDateType($key, $date, $dont_allow_19691231 = true)
	{
		if ($date == '1969-12-31' and $dont_allow_19691231)
		{
			return false;
		}
		
		if (!strlen($date) or 
			$date == '0')
		{
			return false;
		}
		
		// 1228241458		vs.		19830102
		if (preg_match('/^[[:digit:]]+$/', $date) and strlen($date) > 8)
		{
			// It's a unix timestamp (seconds since unix epoch, conver to string)
			$date = date('Y-m-d', $date);
		}
		
		return $this->set($key, date('Y-m-d', strtotime($date)));
	}
	
	/**
	 * Get a date value
	 * 
	 * @param string $key		Get a date value
	 * @param string $format	The format (any format from www.php.net/date)
	 * @return string
	 */
	public function getDateType($key, $format = 'Y-m-d')
	{
		if (!strlen($format))
		{
			$format = 'Y-m-d';
		}
		
		if ($this->exists($key) and $this->get($key))
		{
			return date($format, strtotime($this->get($key)));
		}
		
		return null;
	}
	
	public function setAmountType($key, $amount)
	{
		$this->set($key, sprintf('%01.2f', (float) $amount));
	}
	
	public function getAmountType($key)
	{
		return (float) $this->get($key);
	}
	
	/**
	 * Tell if a data field exists within the object
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function exists($key)
	{
		return isset($this->_object[$key]);
	}
	
	/**
	 * Removes a key from this object
	 * 
	 * @param string $key
	 * @return boolean
	 */
	public function remove($key)
	{
		if (isset($this->_object[$key]))
		{
			unset($this->_object[$key]);
			return true;
		}
		
		return false;
	}
	
	public function getListItem($key, $index)
	{
		$list = $this->getList($key);
		
		if (isset($list[$index]))
		{
			return $list[$index];
		}
		
		return null;
	}
	
	/**
	 * 
	 * 
	 */
	public function addListItem($key, $obj)
	{
		$list = $this->getList($key);
		
		$list[] = $obj;
		
		return $this->set($key, $list);
	}
	
	/**
	 * 
	 */
	public function getList($key)
	{
		$list = $this->get($key, array());
		
		if (!is_array($list))
		{
			$list = array();
		}
		
		return $list;
	}
	
	/**
	 * 
	 */
	public function getArray($pattern, $defaults = array(), $defaults_if_empty = true)
	{
		$list = array();
		foreach ($this->_object as $key => $value)
		{
			if ($this->_fnmatch($pattern, $key))
			{
				$list[$key] = $value;
				
				if ($defaults_if_empty and 
					empty($value) and 
					isset($defaults[$key]))
				{
					$list[$key] = $defaults[$key];
				}
			}
		}
		
		return array_merge($defaults, $list);
	}
	
	protected function _fnmatch($pattern, $str)
	{
		$arr = array(
			'\*' => '.*', 
			'\?' => '.'
			);
		return preg_match('#^' . strtr(preg_quote($pattern, '#'), $arr) . '$#i', $str);
	}
	
	/**
	 * Get a qbXML schema object for a particular type of request
	 * 
	 * Schema objects are used to build and validate qbXML requests and the 
	 * fields and data types of qbXML elements. 
	 * 
	 * @param string $request		A valid QuickBooks API request (for example: CustomerAddRq, InvoiceQueryRq, CustomerModRq, etc.)
	 * @return QuickBooks_QBXML_Schema_Object
	 */
	protected function _schema($request)
	{
		if (strtolower(substr($request, -2, 2)) != 'rq')
		{
			$request = $request . 'Rq';
		}
		
		$class = 'QuickBooks_QBXML_Schema_Object_' . $request;
		$file = 'QuickBooks/QBXML/Schema/Object/' . $request . '.php';
		
		include_once $file;
		
		if (class_exists($class))
		{
			return new $class();
		}
		
		return false;
	}
	
	/**
	 * Convert this QuickBooks object to an XML node object representation
	 * 
	 * @param string $root			The node to use as the root node of the XML node structure
	 * @param string $parent		
	 * @return QuickBooks_XML_Node
	 */
	public function asXML($root = null, $parent = null)
	{
		if (is_null($root))
		{
			$root = $this->object();
		}
		
		$Node = new QuickBooks_XML_Node($root);
		
		foreach ($this->_object as $key => $value)
		{
			if (is_array($value))
			{
				$Node->setChildDataAt($root . ' ' . $key, '', true);
				
				foreach ($value as $sub)
				{
					//print('printing sub' . "\n");
					//print_r($sub);
					//print($sub->asXML());
					$Node->addChildAt($root, $sub->asXML(null, $root));
				}
			}
			else 
			{
				$Node->setChildDataAt($root . ' ' . $key, $value, true);
			}
		}
		
		//print_r($Node);
		
		return $Node;
	}
	
	public function asArray($request, $nest = false)
	{
		
	}
	
	/**
	 * Convert this object to a valid qbXML request/response
	 * 
	 * @todo What should this function return if a schema can't be found...? 
	 * 
	 * @param boolean $compress_empty_elements
	 * @param string $indent
	 * @param string $root
	 * @return string
	 */
	public function asQBXML($request, $todo_for_empty_elements = QUICKBOOKS_XML_XML_DROP, $indent = "\t", $root = null)
	{
		if (strtolower(substr($request, -2, 2)) != 'rq')
		{
			$request .= 'Rq';
		}
		
		$Request = new QuickBooks_XML_Node($request);
		
		if ($schema = $this->_schema($request))
		{
			$tmp = array();
			
			//print_r(array_keys($this->asList($request)));
			
			foreach ($schema->reorderPaths(array_keys($this->asList($request))) as $key => $path)
			{
				$value = $this->_object[$path];
				
				if (is_array($value))
				{
					$tmp[$path] = array();
					
					foreach ($value as $arr)
					{
						$tmp2 = array();
						
						foreach ($arr->asList('') as $inkey => $invalue)
						{
							$arr->set($path . ' ' . $inkey, $invalue);
						}
						
						foreach ($schema->reorderPaths(array_keys($arr->asList(''))) as $subkey => $subpath)
						{
							$subpath = substr($subpath, strlen($path) + 1);
							
							$tmp2[$subpath] = $arr->get($subpath);
						}
						
						$tmp2 = new QuickBooks_Object_Generic($tmp2, $arr->object());
						
						$tmp[$path][] = $tmp2;
					}
				}
				else
				{
					$tmp[$path] = $this->_object[$path];
				}
			}
			
			$this->_object = $tmp;
			
			if ($wrapper = $schema->qbxmlWrapper())
			{
				
				$Node = $this->asXML($wrapper);
				$Request->addChild($Node);
				
				return $Request->asXML($todo_for_empty_elements, $indent);
			}
			else if (count($this->_object) == 0)
			{
				// This catches the cases where we just want to get *all* objects 
				//	back (no filters) and thus the root level qbXML element is *empty* 
				//	and we need to *preserve* this empty element rather than just 
				//	drop it (which results in an empty string, and thus invalid query)
				
				$Node = $this->asXML($request);
				
				return $Node->asXML(QUICKBOOKS_XML_XML_PRESERVE, $indent);
			}
			else
			{
				$Node = $this->asXML($request);
				
				return $Node->asXML($todo_for_empty_elements, $indent);
			}
		}
		
		return '';
	}
	
	public function asList($request)
	{
		
		
		return $this->_object;
	}

	/**
	 * 
	 * 
	 */
	static protected function _fromXMLHelper($class, $XML)
	{
		$paths = $XML->asArray(QUICKBOOKS_XML_ARRAY_PATHS);
		foreach ($paths as $path => $value)
		{
			$newpath = implode(' ', array_slice(explode(' ', $path), 1));
			$paths[$newpath] = $value;
			unset($paths[$path]);
		}
		
		return new $class($paths);		
	}
	
	/**
	 * Convert a QuickBooks_XML_Node object to a QuickBooks_Object_* object instance 
	 * 
	 * @param QuickBooks_XML_Node $XML
	 * @param string $action_or_object
	 * @return QuickBooks_Object
	 */
	static public function fromXML($XML, $action_or_object = null)
	{		
		if (!$action_or_object)
		{
			$action_or_object = $XML->name();
		}
		
		$type = QuickBooks_Utilities::actionToObject($action_or_object);
		$class = 'QuickBooks_Object_' . ucfirst(strtolower($type));
		
		if (class_exists($class))
		{
			$Object = QuickBooks_Object::_fromXMLHelper($class, $XML);
			
			$children = array();
			switch ($Object->object())
			{
				case QUICKBOOKS_OBJECT_PURCHASEORDER:
					
					$children = array(
						'PurchaseOrderLineRet' => array( 'QuickBooks_Object_PurchaseOrder_PurchaseOrderLine', 'addPurchaseOrderLine' ), 
						);
					
					break;
				case QUICKBOOKS_OBJECT_INVOICE:
					
					$children = array( 
						'InvoiceLineRet' => array( 'QuickBooks_Object_Invoice_InvoiceLine', 'addInvoiceLine' ), 
						);
					
					break;
				case QUICKBOOKS_OBJECT_ESTIMATE:
					
					$children = array( 
						'EstimateLineRet' => array( 'QuickBooks_Object_Estimate_EstimateLine', 'addEstimateLine' ), 
						);					
					
					break;
				case QUICKBOOKS_OBJECT_SALESRECEIPT:
					
					$children = array( 
						'SalesReceiptLineRet' => array( 'QuickBooks_Object_SalesReceipt_SalesReceiptLine', 'addSalesReceiptLine' ), 
						);					
					
					break;
				case QUICKBOOKS_OBJECT_JOURNALENTRY:
					
					$children = array(
						'JournalCreditLine' => array( 'QuickBooks_Object_JournalEntry_JournalCreditLine', 'addCreditLine' ), 
						'JournalDebitLine' => array( 'QuickBooks_Object_JournalEntry_JournalDebitLine', 'addDebitLine' ), 
						);
					
					break;
			}
			
			foreach ($children as $node => $tmp)
			{
				$childclass = $tmp[0];
				$childmethod = $tmp[1];
				
				if (class_exists($childclass))
				{
					foreach ($XML->children() as $ChildXML)
					{
						if ($ChildXML->name() == $node)
						{
							$ChildObject = QuickBooks_Object::_fromXMLHelper($childclass, $ChildXML);
							$Object->$childmethod($ChildObject);			
						}	
					}
				}
			}
			
			return $Object;
		}
		
		return false;		
	}
	
	/**
	 * Convert a qbXML string to a QuickBooks_Object_* object instance
	 * 
	 * @param string $qbxml
	 * @param string $action_or_object
	 * @return QuickBooks_Object
	 */
	static public function fromQBXML($qbxml, $action_or_object = null)
	{
		$errnum = null;
		$errmsg = null;
		
		$Parser = new QuickBooks_XML_Parser($qbxml);
		if ($Doc = $Parser->parse($errnum, $errmsg))
		{
			$XML = $Doc->getRoot();
			
			return QuickBooks_Object::fromXML($XML, $action_or_object);
		}
		
		return false;
	}
}
