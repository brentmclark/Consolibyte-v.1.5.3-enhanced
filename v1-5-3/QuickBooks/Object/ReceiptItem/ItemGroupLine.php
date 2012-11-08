<?php

/**
 * QuickBooks ItemGroupLine object container
 *
 * @todo Documentation
 *
 * @author Harley Laue <harley.laue@gmail.com>
 * @license LICENSE.txt
 *
 * @package QuickBooks
 * @subpackage Object
 */

/**
 * QuickBooks base
 */
require_once 'QuickBooks.php';

/**
 * QuickBooks object base class
 */
require_once 'QuickBooks/Object.php';

/**
 * Quickbooks ItemGroupLine definition
 */
class QuickBooks_Object_ReceiptItem_ItemGroupLine extends QuickBooks_Object
{
	/**
	 * Create a new QuickBooks ReceiptItem ItemGroupLine object
	 *
	 * @param array $arr
	 */
	public function __construct($arr = array())
	{
		parent::__construct($arr);
	}

	public function getItemGroupListID()
	{
		return $this->get('ItemGroupRef ListID');
	}
	
	public function setItemGroupListID($ListID)
	{
		return $this->set('ItemGroupRef ListID', $ListID);
	}

	public function getItemGroupName()
	{
		return $this->get('ItemGroupRef FullName');
	}
	
	public function setItemGroupName($Name)
	{
		return $this->set('ItemGroupRef FullName', $Name);
	}
  
	public function getQuantity()
	{
		return $this->get('Quantity');
	}
	
	public function setQuantity($Quantity)
	{
		return $this->set('Quantity', $Quantity);
	}
  
	public function getUnitOfMeasure()
	{
		return $this->get('UnitOfMeasure');
	}
	
	public function setUnitOfMeasure($UnitOfMeasure)
	{
		return $this->set('UnitOfMeasure', $UnitOfMeasure);
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
			case QUICKBOOKS_ADD_RECEIPTITEM:
				$root = 'ItemGroupLineAdd';
				break;
// Currently unimplemented
/*
			case QUICKBOOKS_QUERY_INVENTORYADJUSTMENT:
				$root = 'ExpenseLineQuery';
				break;
*/
		}

		return parent::asXML($root);
	}

	/**
	 * Convert this object to a valid qbXML request
	 *
	 * @param string $request The type of request to convert this to (examples: CustomerAddRq, CustomerModRq, CustomerQueryRq)
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
	 * Tell what type of object this is
	 *
	 * @return string
	 */
	public function object()
	{
		return "ItemGroupLine";
	}
}

