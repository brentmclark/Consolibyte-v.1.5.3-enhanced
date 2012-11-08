<?php

/**
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
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
require_once 'QuickBooks/Object/ReceivePayment.php';

/**
 * 
 * 
 */
class QuickBooks_Object_ReceivePayment_AppliedToTxn extends QuickBooks_Object
{
	/**
	 * Create a new QuickBooks ReceivePayment AppliedToTxn object
	 * 
	 * @param array $arr
	 */
	public function __construct($arr = array())
	{
		parent::__construct($arr);
	}
	
	public function setTxnID($TxnID)
	{
		return $this->set('TxnID', $TxnID);
	}
	
	public function setTransactionID($TxnID)
	{
		return $this->setTxnID($TxnID);
	}
	
	public function getTxnID()
	{
		return $this->get('TxnID');
	}
	
	public function getTransactionID()
	{
		return $this->getTxnID();
	}
	
	public function setTxnApplicationID($value)
	{
		return $this->set(QUICKBOOKS_API_APPLICATIONID, $this->encodeApplicationID(QUICKBOOKS_OBJECT_INVOICE, QUICKBOOKS_TXNID, $value));
		//return $this->set('NullRef ' . QUICKBOOKS_API_APPLICATIONID, $this->encodeApplicationID(QUICKBOOKS_OBJECT_INVOICE, QUICKBOOKS_TXNID, $value));
	}
	
	public function getTxnApplicationID()
	{
		
	}
	
	public function getPaymentAmount($amount)
	{
		return $this->get('PaymentAmount');
	}
	
	public function setPaymentAmount($amount)
	{
		return $this->set('PaymentAmount', sprintf('%01.2f', (float) $amount));
	}
	
	/**
	 * 
	 * 
	 * @return boolean
	 */
	protected function _cleanup()
	{
		if ($this->exists('PaymentAmount'))
		{
			$this->set('PaymentAmount', sprintf('%01.2f', $this->get('PaymentAmount')));
		}
		
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
		$this->_cleanup();
		
		switch ($parent)
		{
			case QUICKBOOKS_ADD_RECEIVEPAYMENT:
				$root = 'AppliedToTxnAdd';
				break;
			case QUICKBOOKS_MOD_RECEIVEPAYMENT:
				$root = 'AppliedToTxnMod';
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
		return 'AppliedToTxn';
	}
}

?>