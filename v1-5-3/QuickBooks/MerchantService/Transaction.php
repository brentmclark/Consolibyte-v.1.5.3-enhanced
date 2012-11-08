<?php

/**
 * QuickBooks Merchant Service transaction class
 * 
 * This class represents a transaction returned by the QuickBooks Merchant 
 * Service web gateway. 
 * 
 * @package QuickBooks
 * @subpackage MerchantService
 */

/**
 * QuickBooks base classes
 */
require_once 'QuickBooks.php';

/**
 * QuickBooks MerchantService transaction processor
 */
require_once 'QuickBooks/MerchantService.php';

/**
 * XML parser class
 */
require_once 'QuickBooks/XML/Parser.php';

/**
 * 
 * 
 * 
 */
class QuickBooks_MerchantService_Transaction
{
	protected $_type;
	protected $_transID;
	protected $_clientTransID;
	protected $_authcode;
	protected $_merchant;
	protected $_batch;
	protected $_paymentgroup;
	protected $_paymentstatus;
	protected $_txnauthtime;
	protected $_txnauthstamp;
	protected $_avsstreet;
	protected $_avszip;
	protected $_cvvmatch;
	protected $_networkname;
	protected $_networknumber;
	
	/**
	 * 
	 * 
	 * 
	 */
	public function __construct($type, $transID, $clientTransID = null, $authcode = null, $merchant = null, $batch = null, $paymentgroup = null, $paymentstatus = null, $txnauthtime = null, $txnauthstamp = null, $avsstreet = null, $avszip = null, $cvvmatch = null, $networkname = null, $networknumber = null)
	{
		$this->_type = $type;
		$this->_transID = $transID;
		$this->_clientTransID = $clientTransID;
		$this->_authcode = $authcode;
		$this->_merchant = $merchant;
		$this->_batch = $batch;
		$this->_paymentgroup = $paymentgroup;
		$this->_paymentstatus = $paymentstatus;
		$this->_txnauthtime = $txnauthtime;
		$this->_txnauthstamp = $txnauthstamp;
		$this->_avsstreet = $avsstreet;
		$this->_avszip = $avszip;
		$this->_cvvmatch = $cvvmatch;
		$this->_networkname = $networkname;
		$this->_networknumber = $networknumber;
	}
	
	/**
	 * Set the transaction ID for this transaction
	 * 
	 * @param string $transID
	 * @return void
	 */
	public function setTransactionID($transID)
	{
		$this->_transID = $transID;
	}
	
	/**
	 * Set the client transaction ID for this transaction
	 * 
	 * @param string $clientTransID
	 * @return void
	 */
	public function setClientTransactionID($clientTransID)
	{
		$this->_clientTransID = $clientTransID;
	}
	
	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function getTransactionID()
	{
		return $this->_transID;
	}
	
	/**
	 * 
	 * 
	 * 
	 * 
	 */
	public function getClientTransactionID()
	{
		return $this->_clientTransID;
	}
	
	public function getAuthorizationCode()
	{
		return $this->_authcode;
	}
	
	public function getMerchantAccountNumber()
	{
		return $this->_merchant;
	}
	
	public function getAVSStreet()
	{
		return $this->_avsstreet;
	}
	
	public function getAVSZip()
	{
		return $this->_avszip;
	}
	
	public function getCardSecurityCodeMatch()
	{
		return $this->_cvvmatch;
	}
	
	public function getReconBatchID()
	{
		return $this->_batch;
	}
	
	public function getPaymentGroupingCode()
	{
		return $this->_paymentgroup;
	}
	
	public function getPaymentStatus()
	{
		return $this->_paymentstatus;
	}
	
	public function getTxnAuthorizationTime()
	{
		return $this->_txnauthtime;
	}
	
	public function getTxnAuthorizationStamp()
	{
		return $this->_txnauthstamp;
	}

	/**
	 * 
	 * 
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'Type' => $this->_type, 
			'CreditCardTransID' => $this->_transID, 
			'AuthorizationCode' => $this->_authcode, 
			'AVSStreet' => $this->_avsstreet,
			'AVSZip' => $this->_avszip, 
			'CardSecurityCodeMatch' => $this->_cvvmatch, 
			'ClientTransID' => $this->_clientTransID,  
			'MerchantAccountNumber' => $this->_merchant, 
			'ReconBatchID' => $this->_batch, 
			'PaymentGroupingCode' => $this->_paymentgroup,
			'PaymentStatus' => $this->_paymentstatus, 
			'TxnAuthorizationTime' => $this->_txnauthtime, 
			'TxnAuthorizationStamp' => $this->_txnauthstamp, 
			'NetworkName' => $this->_networkname, 
			'NetworkNumber' => $this->_networknumber, 
			//'DebitCardTransID' => $this->_transID, 
			);
	}
	
	static public function fromArray($arr)
	{
		static $defaults = array(
			'Type' => null, 
			'CreditCardTransID' => null, 
			'AuthorizationCode' => null, 
			'AVSStreet' => null,
			'AVSZip' => null, 
			'CardSecurityCodeMatch' => null, 
			'ClientTransID' => null,  
			'MerchantAccountNumber' => null, 
			'ReconBatchID' => null, 
			'PaymentGroupingCode' => null,
			'PaymentStatus' => null, 
			'TxnAuthorizationTime' => null, 
			'TxnAuthorizationStamp' => null, 
			'NetworkName' => null, 
			'NetworkNumber' => null, 
			'DebitCardTransID' => null, 
			);		
		
		$trans = array_merge($defaults, $arr);
		
		return new QuickBooks_MerchantService_Transaction(
			$trans['Type'], 
			$trans['CreditCardTransID'], 
			$trans['ClientTransID'], 
			$trans['AuthorizationCode'], 
			$trans['MerchantAccountNumber'], 
			$trans['ReconBatchID'], 
			$trans['PaymentGroupingCode'], 
			$trans['PaymentStatus'], 
			$trans['TxnAuthorizationTime'], 
			$trans['TxnAuthorizationStamp'], 
			$trans['AVSStreet'], 
			$trans['AVSZip'], 
			$trans['CardSecurityCodeMatch'], 
			$trans['NetworkName'], 
			$trans['NetworkNumber']);		
	}
	
	public function toXML()
	{
		$xml = '';
		$xml .= '<?xml version="1.0" encoding="UTF-8" ?>' . QUICKBOOKS_CRLF;
		$xml .= '<QBMSTransaction>' . QUICKBOOKS_CRLF;
		foreach ($this->toArray() as $key => $value)
		{
			$xml .= '<' . $key . '>' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</' . $key . '>' . QUICKBOOKS_CRLF;
		}
		$xml .= '</QBMSTransaction>';
		
		return $xml;
	}
	
	/**
	 * 
	 * 
	 * @param string $xml
	 * @return QuickBooks_MerchantService_Transaction
	 */
	static public function fromXML($xml)
	{		
		$errnum = 0;
		$errmsg = '';
		
		$arr = array();
		
		$Parser = new QuickBooks_XML_Parser($xml);
		if ($Doc = $Parser->parse($errnum, $errmsg))
		{
			$Root = $Doc->getRoot();
			
			foreach ($Root->asArray(QUICKBOOKS_XML_ARRAY_PATHS) as $path => $value)
			{
				$tmp = explode(' ', $path);
				$key = trim(end($tmp));
				
				$arr[$key] = $value;
			}
		}
		
		return QuickBooks_MerchantService_Transaction::fromArray($arr);
	}
	
	public function serialize()
	{
		return serialize($this->toArray());
	}
	
	static public function unserialize($str)
	{
		return QuickBooks_MerchantService_Transaction::fromArray(unserialize($str));
	}
}
