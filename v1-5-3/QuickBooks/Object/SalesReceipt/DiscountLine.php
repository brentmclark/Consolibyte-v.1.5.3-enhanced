<?php

/**
 * 
 * 
 * @package QuickBooks
 * @subpackage Object
 */

/**
 * 
 */
require_once 'QuickBooks.php';

/**
 * 
 */
require_once 'QuickBooks/Object.php';

/**
 * 
 */
require_once 'QuickBooks/Object/SalesReceipt.php';

/**
 * 
 * 
 */
class QuickBooks_Object_SalesReceipt_DiscountLine extends QuickBooks_Object
{
	/**
	 * Create a new QuickBooks SalesReceipt SalesReceiptLine object
	 * 
	 * @param array $arr
	 */
	public function __construct($arr = array())
	{
		parent::__construct($arr);
	}
	
	public function setAmount($amount)
	{
		$amount = (float) $amount;
		
		// Discount amounts are always negative in QuickBooks 
		if ($amount > 0)
		{
			$amount = $amount * -1.0;
		}
		
		return $this->setAmountType('Amount', $amount);
	}
	
	public function setAccountListID($ListID)
	{
		return $this->set('AccountRef ListID', $ListID);
	}
	
	public function setAccountName($name)
	{
		return $this->set('AccountRef FullName', $name);
	}		
		
	/**
	 * 
	 * 
	 * @return boolean
	 */
	protected function _cleanup()
	{
		
		return true;
	}
	
	/**
	 * 
	 */
	public function asArray($request, $nest = true)
	{
		$this->_cleanup();
		
		return parent::asArray($request, $nest);
	}
	
	public function asXML($root = null, $parent = null)
	{
		switch ($parent)
		{
			case QUICKBOOKS_ADD_SALESRECEIPT:
				$root = 'DiscountLineAdd';
				break;
			case QUICKBOOKS_MOD_SALESRECEIPT:
				$root = 'DiscountLineMod';
				break;
		}
		
		return parent::asXML($root);
	}
	
	/**
	 * 
	 * 
	 * @param boolean $todo_for_empty_elements	A constant, one of: QUICKBOOKS_XML_XML_COMPRESS, QUICKBOOKS_XML_XML_DROP, QUICKBOOKS_XML_XML_PRESERVE
	 * @param string $indent
	 * @param string $root
	 * @return string
	 */
	public function asQBXML($request, $todo_for_empty_elements = QUICKBOOKS_OBJECT_XML_DROP, $indent = "\t", $root = null)
	{
		$this->_cleanup();
		
		
		
		return parent::asQBXML($request, $todo_for_empty_elements, $indent, $root);
	}
	
	/**
	 * Tell the type of object this is
	 * 
	 * @return string
	 */
	public function object()
	{
		return 'DiscountLine';
	}
}

?>