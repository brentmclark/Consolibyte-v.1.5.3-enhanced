<?php

/**
 * Integrator base class
 * 
 * The QuickBooks_Integrator base class provides general utility methods for 
 * integrating pre-packaged shopping carts/applications with QuickBooks. 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Integrator
 */

//
if (!defined('QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME', 'Discount Item - QuickBooks Integrator');
}

//
if (!defined('QUICKBOOKS_INTEGRATOR_SHIPPING_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_SHIPPING_NAME', 'Shipping Item - QuickBooks Integrator');
}

//
if (!defined('QUICKBOOKS_INTEGRATOR_COUPON_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_COUPON_NAME', 'Coupon Item - QuickBooks Integrator');
}

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_SHIPPING_ID', 'shipping');

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_COUPON_ID', 'coupon');

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_DISCOUNT_ID', 'discount');

/**
 * QuickBooks Integrator base class
 */
abstract class QuickBooks_Integrator
{
	/**
	 * Integrator driver class
	 * @var object
	 */
	protected $_integrator;
	
	/**
	 * Integrator config
	 * @var array
	 */
	protected $_config;
	
	/**
	 * Integrator consturctor
	 * 
	 * @param object $integrator_driver
	 * @param array $config
	 */
	final public function __construct($integrator_driver, $config)
	{
		$this->_integrator = $integrator_driver;
		$this->_config = $this->_defaults($config);
	}
	
	/**
	 * Integrator configuration
	 * 
	 * @param array $config
	 * @return array
	 */
	protected function _defaults($config)
	{
		$defaults = array(
			'push_orders_as' => QUICKBOOKS_OBJECT_INVOICE, 
			'push_products_as' => QUICKBOOKS_OBJECT_SERVICEITEM,
			'push_discounts_as' => QUICKBOOKS_OBJECT_DISCOUNTITEM, 
			'push_coupons_as' => QUICKBOOKS_OBJECT_DISCOUNTITEM, 
			'push_shipping_as' => QUICKBOOKS_OBJECT_SERVICEITEM,  
			'sales_account_name' => 'Sales', 
			'cogs_account_name' => 'COGS', 
			'shipping_account_name' => 'Services', 
			'discount_account_name' => 'Discounts', 
			'coupon_account_name' => 'Coupons', 
			'salestax_account_name' => 'Sales Tax', 
			'tax_code_taxable' => 'TAX', 
			'tax_code_nontaxable' => 'NON', 
			'customer_name_for_query_format' => '$FirstName $LastName', 
			'shipmethod_name_for_query_format' => '$Name', 
			'paymentmethod_name_for_query_format' => '$Name', 
			'order_refnumber_for_query_format' => '$RefNumber', 
			'product_name_for_query_format' => '$Name', 
			'class_name_for_query_format' => '$Name', 
			'account_name_for_query_format' => '$Name', 
			'default_getorder_query' => null, 
			'default_getproduct_query' => null, 
			'default_getcustomer_query' => null, 
			'default_getshipmethod_query' => null, 
			'default_getpaymentmethod_query' => null, 
			'default_discount_query' => null, 
			'default_getshipping_query' => null, 
			'default_coupon_query' => null, 
			'default_salestax_query' => null, 
			'default_orderitem_query' => null, 
			'default_payment_query' => null, 
			);
			
		return array_merge($defaults, $config);
	}
	
	abstract protected function _defaultGetOrderQuery();
	
	protected function _getOrderQuery($ID)
	{
		$sql = $this->_config['default_getorder_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetOrderQuery();
		}
		
		return $this->_applyFormat($sql, array( 'ID' => $ID ));
	}
	
	abstract protected function _defaultGetCustomerQuery();
	
	protected function _getCustomerQuery($ID)
	{
		$sql = $this->_config['default_getcustomer_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetCustomerQuery();
		}
		
		return $this->_applyFormat($sql, array( 'ID' => $ID ));
	}
	
	//abstract protected function _defaultGetProductQuery();
	
	protected function _getProductQuery($ID)
	{
		$sql = $this->_config['default_getproduct_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetProductQuery();
		}
		
		return $this->_applyFormat($sql, array( 'ID' => $ID ));
	}
	
	abstract protected function _defaultGetShipMethodQuery();
	
	protected function _getShipMethodQuery($ID)
	{
		$sql = $this->_config['default_getshipmethod_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetShipMethodQuery();
		}
		
		return $this->_applyFormat($sql, array( 'ID' => $ID ));
	}
	
	abstract protected function _defaultGetShippingForOrderQuery();
	
	protected function _getShippingForOrderQuery($OrderID)
	{
		$sql = $this->_config['default_getshipping_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetShippingForOrderQuery();
		}
		
		return $this->_applyFormat($sql, array( 'OrderID' => $OrderID ));
	}
	
	//abstract protected function _defaultPaymentMethodQuery();
	
	protected function _getPaymentMethodQuery($ID)
	{
		$sql = $this->_config['default_getpaymentmethod_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetPaymentMethodQuery();
		}
		
		return $this->_applyFormat($sql, array( 'ID' => $ID ));
	}
	
	/*
	abstract protected function _defaultDiscountQuery();
	
	abstract protected function _defaultShippingQuery();
	
	abstract protected function _defaultCouponQuery();
	
	abstract protected function _defaultSalesTaxQuery();
	
	abstract protected function _defaultOrderItemQuery();
	
	abstract protected function _defaultPaymentQuery();
	*/
	
	/**
	 * Get a customer object by ID value
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_Customer
	 */
	abstract public function getCustomer($ID);
	
	/**
	 * Get a list ID #s for new customers since $datetime
	 * 
	 * @param string $datetime
	 * @return array
	 */
	abstract public function listNewCustomersSince($datetime);
	
	/**
	 * Get a list of ID #s for modified customers since $datetime
	 * 
	 * @param string $datetime
	 * @return array
	 */
	abstract public function listModifiedCustomersSince($datetime);
	
	/**
	 * 
	 * 
	 * 
	 */
	abstract public function listNewOrdersSince($datetime);
	
	/**
	 * 
	 * 
	 * 
	 */
	abstract public function listModifiedOrdersSince($datetime);
	
	abstract public function getOrder($ID);
	
	abstract public function getProduct($ID);
	
	abstract public function getOrderItems($ID);
	
	abstract public function getShipMethod($ID);
	
	abstract public function getDiscountForOrder($OrderID);
	
	abstract public function getSalesTax($ID);
	
	abstract public function getPayment($ID);
	
	public function getGenericShipping()
	{
		$current = $this->_config['push_products_as'];
		$this->_config['push_products_as'] = $this->_config['push_shipping_as'];
		
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_SHIPPING_ID,
			'Name' => QUICKBOOKS_INTEGRATOR_SHIPPING_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_SHIPPING_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			'SalesOrPurchase_AccountName' => $this->_config['shipping_account_name'], 
			);
		
		$Item = $this->_productFromArray($arr);	
		  
		$this->_config['push_products_as'] = $current;
		
		return $Item;
	}
	
	public function getGenericDiscount()
	{
		$current = $this->_config['push_products_as'];
		$this->_config['push_products_as'] = $this->_config['push_discounts_as'];
		
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_DISCOUNT_ID, 
			'Name' => QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			'SalesOrPurchase_AccountName' => $this->_config['discount_account_name'], 
			);
		
		$Item = $this->_productFromArray($arr);	
		  
		$this->_config['push_products_as'] = $current;
		
		return $Item;
	}
	
	public function getGenericCoupon()
	{
		$current = $this->_config['push_products_as'];
		$this->_config['push_products_as'] = $this->_config['push_coupons_as'];
		
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_COUPON_ID, 
			'Name' => QUICKBOOKS_INTEGRATOR_COUPON_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_COUPON_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			'SalesOrPurchase_AccountName' => $this->_config['coupon_account_name'], 
			);
		
		$Item = $this->_productFromArray($arr);	
		  
		$this->_config['push_products_as'] = $current;
		
		return $Item;
	}
	
	public function getPaymentMethodNameForQuery($ID)
	{
		$PaymentMethod = $this->getPaymentMethod($ID);
		
		$list = $PaymentMethod->asList(QUICKBOOKS_ADD_PAYMENTMETHOD);
		$name = $this->_applyFormat($this->_config['paymentmethod_name_for_query_format'], $list);
		
		return $name;
	}
	
	public function getShipMethodNameForQuery($ID)
	{
		$ShipMethod = $this->getShipMethod($ID);
		
		$list = $ShipMethod->asList(QUICKBOOKS_ADD_SHIPMETHOD);
		$name = $this->_applyFormat($this->_config['shipmethod_name_for_query_format'], $list);
		
		return $name;
	}
	
	public function getCustomerNameForQuery($ID)
	{
		$Customer = $this->getCustomer($ID);
		
		$list = $Customer->asList(QUICKBOOKS_ADD_CUSTOMER);
		$name = $this->_applyFormat($this->_config['customer_name_for_query_format'], $list);
		
		return $name;
	}
	
	/**
	 * 
	 * 
	 * @todo If we ever want to support adding multiple types of items (inventory, service, noninventory, etc.) we'll need to allow diff. formats for each type of itme
	 */
	public function getProductNameForQuery($ID)
	{
		$Product = $this->getProduct($ID);
		
		switch ($Product->object())
		{
			case QUICKBOOKS_OBJECT_SERVICEITEM:
				$list = $Product->asList(QUICKBOOKS_ADD_SERVICEITEM);
				break;
			case QUICKBOOKS_OBJECT_DISCOUNTITEM:
				$list = $Product->asList(QUICKBOOKS_ADD_DISCOUNTITEM);
				break;
			case QUICKBOOKS_OBJECT_INVENTORYITEM:
				$list = $Product->asList(QUICKBOOKS_ADD_INVENTORYITEM);
				break;
			case QUICKBOOKS_OBJECT_NONINVENTORYITEM:
				$list = $Product->asList(QUICKBOOKS_ADD_NONINVENTORYITEM);
				break;
			default:
				return '-';
		}
		
		$name = $this->_applyFormat($this->_config['product_name_for_query_format'], $list);
		
		return $name;
	}
	
	public function getClassNameForQuery($ID)
	{
		$Class = $this->getClass($ID);
		
		$list = $Class->asList(QUICKBOOKS_ADD_CLASS);
		$name = $this->_applyFormat($this->_config['class_name_for_query_format'], $list);
		
		return $name;
	}
	
	public function getAccountNameForQuery($ID)
	{
		$Account = $this->getAccount($ID);
		
		$list = $Account->asList(QUICKBOOKS_ADD_ACCOUNT);
		$name = $this->_applyFormat($this->_config['account_name_for_query_format'], $list);
		
		return $name;
	}
	
	/**
	 * Apply a format string to an array, to generate a string
	 * 
	 * @param string $format
	 * @param array $arr
	 * @return string 
	 */
	protected function _applyFormat($format, $arr)
	{
		$func = create_function('$a, $b', ' if (strlen($a) > strlen($b)) { return -1; } return 1; ');
		uasort($arr, $func);
		
		foreach ($arr as $key => $value)
		{
			$format = str_replace('$' . $key, $value, $format);
		}
		
		return $format;
	}
	
	/** 
	 * 
	 * 
	 * @param array $arr
	 * @param array $map
	 * @param QuickBooks_Object
	 * @param string $type
	 * @param string $type
	 * @return QuickBooks_Object
	 */
	protected function _applyBaseMap($arr, $map, $obj, $type, $path = '')
	{
		if ($path)
		{
			$path = trim($path) . ' ';
		}
		
		foreach ($map as $field => $tmp)
		{
			if (!empty($arr[$field]))
			{
				$set = true;
				
				$method = $tmp[0];
				$qbfield = $tmp[1];
				
				$resolve = false;
				if (!empty($tmp[2]))
				{
					$resolve = $tmp[2];
				}
						
				$value = $arr[$field];
					
				if ($qbfield and !strlen($resolve))
				{
					// Cast $value
					$value = QuickBooks_Cast::cast($type, $path . $qbfield, $value);
				}
				else if ($qbfield and strlen($resolve))
				{
					// Try to resolve a value to a ListID or TxnID
					$obj->$method($value);
					$encode = $obj->get($qbfield);
					$obj->remove($qbfield);
					
					$reftype = null;
					$reftag = null;
					$refid = null;
					
					$obj->decodeApplicationID($encode, $reftype, $reftag, $refid);
					
					$API = QuickBooks_API_Singleton::getInstance();
					
					if ($ListID_or_TxnID = $API->fetchQuickBooksID($reftype, $value))
					{
						$obj->$resolve($ListID_or_TxnID);
						$set = false;
					}
				}
				
				if ($set)
				{
					$obj->$method($value);
				}
			}
		}
		
		return $obj;
	}
	
	/**
	 * 
	 * 
	 * @param array $arr
	 * @param array $map
	 * @param QuickBooks_Object
	 * @param string $type
	 * @return QuickBooks_Object
	 */
	protected function _applyAddressMap($arr, $map, $obj, $type)
	{
		foreach ($map as $type => $method)
		{
			$defaults = array(
				'Addr1' => '', 
				'Addr2' => '', 
				'Addr3' => '', 
				'Addr4' => '', 
				'Addr5' => '', 
				'City' => '', 
				'State' => '', 
				'Province' => '', 
				'PostalCode' => '', 
				'Country' => 'USA', 
				'Notes' => '', 
				);

			foreach ($defaults as $key => $default)
			{
				if (!empty($arr[$type . $key]))
				{
					$defaults[$key] = $arr[$type . $key];
				}
			}
			
			$obj->$method(
				$defaults['Addr1'], 
				$defaults['Addr2'], 
				$defaults['Addr3'], 
				$defaults['Addr4'], 
				$defaults['Addr5'], 
				$defaults['City'], 
				$defaults['State'], 
				$defaults['Province'], 
				$defaults['PostalCode'], 
				$defaults['Country'], 
				$defaults['Notes']);
		}
		
		return $obj;
	}
	
	/**
	 * Create a discount item for a coupon, from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_DiscountItem
	 */
	protected function _couponFromArray($arr)
	{
		$DiscountItem = new QuickBooks_Object_DiscountItem();
		
		$map = array(
			'Name' => 					array( 'setName', 'Name' ), 
			'ItemDesc' => 				array( 'setItemDesc', 'ItemDesc' ), 
			'SalesTaxCodeID' => 		array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
			'SalesTaxCodeName' => 		array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ),  
			'SalesTaxCodeListID' => 	array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ), 
			'DiscountRate' => 			array( 'setDiscountRate', 'DiscountRate' ), 
			'DiscountRatePercent' => 	array( 'setDiscountRatePercent', 'DiscountRatePercent' ), 
			'AccountID' => 				array( 'setAccountApplicationID', 'AccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setAccountListID' ), 
			'AccountListID' => 			array( 'setAccountListID', 'AccountRef ListID' ), 
			'AccountName' => 			array( 'setAccountName', 'AccountRef FullName' ), 
			);
		
		$DiscountItem = $this->_applyBaseMap($arr, $map, $DiscountItem, QUICKBOOKS_OBJECT_DISCOUNTITEM);
		
		return $DiscountItem;
	}
	
	/**
	 * Create a payment item, from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_ReceivePayment
	 */
	protected function _paymentFromArray($arr)
	{
		$ReceivePayment = new QuickBooks_Object_ReceivePayment();
		
		$map = array(
			'CustomerID' => 			array( 'setCustomerApplicationID', 'CustomerRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerListID' ), 
			'ARAccountID' => 			array( 'setARAccountApplicationID', 'ARAccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setARAccountListID' ), 
			'ARAccountListID' => 		array( 'setARAccountListID', 'ARAccountRef ListID' ), 
			'ARAccountName' => 			array( 'setARAccountName', 'ARAccountRef FullName' ), 
			'DepositToAccountID' => 	array( 'setDepositToAccountApplicationID', 'DepositToAccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setDepositToAccountListID' ), 
			'DepositToAccountListID' => array( 'setDepositToAccountListID', 'DepositToAccountRef ListID', ), 
			'DepositToAccountName' => 	array( 'setDepositToAccountName', 'DepositToAccountRef FullName' ), 
			'PaymentMethodID' => 		array( 'setPaymentMethodApplicationID', 'PaymentMethodRef ' . QUICKBOOKS_API_APPLICATIONID, 'setPaymentMethodListID' ), 
			'PaymentMethodListID' => 	array( 'setPaymentMethodListID', 'PaymentMethodRef ListID' ), 
			'PaymentMethodName' => 		array( 'setPaymentMethodName', 'PaymentMethodRef FullName' ), 
			'Memo' => 					array( 'setMemo', 'Memo' ), 
			'IsAutoApply' => 			array( 'setIsAutoApply', 'IsAutoApply' ), 
			'TxnDate' => 				array( 'setTransactionDate', 'TxnDate' ), 
			'RefNumber' => 				array( 'setRefNumber', 'RefNumber' ), 
			'TotalAmount' => 			array( 'setTotalAmount', 'TotalAmount' ), 
			);
		
		$ReceivePayment = $this->_applyBaseMap($arr, $map, $ReceivePayment, QUICKBOOKS_OBJECT_RECEIVEPAYMENT);
		
		return $ReceivePayment;
	}
	
	/**
	 * Create a ship method object, from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_ShipMethod
	 */
	protected function _shipMethodFromArray($arr)
	{
		$ShipMethod = new QuickBooks_Object_ShipMethod();
		
		$map = array(
			'Name' => array( 'setName', 'Name' ), 
			);
		
		$ShipMethod = $this->_applyBaseMap($arr, $map, $ShipMethod, QUICKBOOKS_OBJECT_SHIPMETHOD);
		
		return $ShipMethod;
	}
	
	/**
	 * Create a payment method object, from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_PaymentMethod
	 */
	protected function _paymentMethodFromArray($arr)
	{
		$PaymentMethod = new QuickBooks_Object_PaymentMethod();
		
		$map = array(
			'Name' => array( 'setName', 'Name' ), 
			);
		
		$PaymentMethod = $this->_applyBaseMap($arr, $map, $PaymentMethod, QUICKBOOKS_OBJECT_PAYMENTMETHOD);
		
		return $PaymentMethod;
	}
	
	/**
	 * Create an order object instance, from an array
	 * 
	 * This method can create objects of the following types: 
	 * 	- QuickBooks_Object_Invoice
	 *	- QuickBooks_Object_SalesReceipt
	 * 	- QuickBooks_Object_SalesOrder
	 * 
	 * @param array $arr
	 * @param array $items
	 * @return QuickBooks_Object_*
	 */
	protected function _orderFromArray($arr, $items, $shipping = null, $discount = null)
	{
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				
				
				break;
			case QUICKBOOKS_OBJECT_SALESORDER:
				
				
				
				break;
			case QUICKBOOKS_OBJECT_INVOICE:
			default:
				
				$Invoice = new QuickBooks_Object_Invoice();
				
				$map = array(
					//'OrderID' => 		array( 'setReferenceNumber', 'RefNumber' ), 
					'CustomerID' => 	array( 'setCustomerApplicationID', 'CustomerRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerListID' ), 
					'ClassID' => 		array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					'ARAccountID' => 	array( 'setARAccountApplicationID', 'ARAccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setARAccountListID' ), 
					'TemplateID' => 	array( 'setTemplateApplicationID', 'TemplateRef ' . QUICKBOOKS_API_APPLICATIONID, 'setTemplateListID' ), 
					'TxnDate' => 		array( 'setTxnDate', 'TxnDate' ), 
					'RefNumber' => 		array( 'setRefNumber', 'RefNumber' ), 
					'IsPending' => 		array( 'setIsPending', 'IsPending' ), 
					'PONumber' => 		array( 'setPONumber', 'PONumber' ), 
					'TermsID' => 		array( 'setTermsApplicationID', 'TermsRef ' . QUICKBOOKS_API_APPLICATIONID, 'setTermsListID' ), 
					'SalesRepID' => 	array( 'setSalesRepApplicationID', 'SalesRepRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesRepListID' ), 
					'FOB' => 			array( 'setFOB', 'FOB' ), 
					'ShipDate' => 		array( 'setShipDate', 'ShipDate' ), 
					'ShipMethodID' => 	array( 'setShipMethodApplicationID', 'ShipMethodRef ' . QUICKBOOKS_API_APPLICATIONID, 'setShipMethodListID' ), 
					'ItemSalesTaxID' => array( 'setSalesTaxItemApplicationID', 'SalesTaxItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxItemListID' ), 
					'CustomerMsgID' => 	array( 'setCustomerMsgApplicationID', 'CustomerMsgRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerMsgListID' ), 
					'Memo' => 			array( 'setMemo', 'Memo' ), 
					'IsToBePrinted' => 	array( 'setIsToBePrinted', 'IsToBePrinted' ), 
					'IsToBeEmailed' => 	array( 'setIsToBeEmailed', 'IsToBeEmailed' ), 
					'CustomerSalesTaxCodeID' => array( 'setCustomerSalesTaxCodeApplicationID', 'CustomerSalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerSalesTaxCodeListID' ), 
					);
				
				$this->_applyBaseMap($arr, $map, $Invoice, QUICKBOOKS_OBJECT_INVOICE);
				
				if (!empty($arr['TxnDate']))
				{
					$Invoice->setTxnDate($arr['TxnDate']);
				}
				else
				{
					$Invoice->setTxnDate(date('Y-m-d'));
				}
				
				$map2 = array( 
					'ShipAddress_' => 'setShipAddress', 
					'BillAddress_' => 'setBillAddress', 
					);
				
				$this->_applyAddressMap($arr, $map2, $Invoice, QUICKBOOKS_OBJECT_INVOICE);
				
				foreach ($items as $item)
				{
					if (is_object($item))
					{
						$Invoice->addInvoiceLine($item);
					}
				}
				
				if (is_object($shipping))
				{
					$Invoice->addInvoiceLine($shipping);
				}
				
				return $Invoice;
		}
	}
	
	/**
	 * Create an order item (invoice line item, etc.) from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_*
	 */
	protected function _orderItemFromArray($arr)
	{
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				break;
			case QUICKBOOKS_OBJECT_SALESORDER:
				
				break;
			case QUICKBOOKS_OBJECT_INVOICE:
			default:
				
				$InvoiceLine = new QuickBooks_Object_Invoice_InvoiceLine();
				
				$map = array(
					'ProductID' => 	array( 'setItemApplicationID', 'ItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setItemListID' ), 
					'Desc' => 		array( 'setDescription', 'Invoice InvoiceLine Desc' ), 
					'Quantity' => 	array( 'setQuantity', 'Invoice InvoiceLine Quantity' ),  
					'Rate' => 		array( 'setRate', 'Invoice InvoiceLine Rate' ),
					'ClassID' => 	array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					);
				
				$InvoiceLine = $this->_applyBaseMap($arr, $map, $InvoiceLine, 'Invoice InvoiceLine');	
				
				return $InvoiceLine;
		}
	}
	
	/**
	 * Create a QuickBooks product item object, from an array
	 * 
	 * This method can return items of the following type:
	 * 	- QuickBooks_Object_InventoryItem
	 * 	- QuickBooks_Object_NonInventoryItem
	 * 	- QuickBooks_Object_ServiceItem
	 * 	- QuickBooks_Object_OtherChargeItem
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_ServiceItem
	 */
	protected function _productFromArray($arr)
	{
		switch ($this->_config['push_products_as'])
		{
			case QUICKBOOKS_OBJECT_OTHERCHARGEITEM:
				
				$OtherChargeItem = new QuickBooks_Object_OtherChargeItem();
				
				
				
				return $OtherChargeItem;
				
				break;
			case QUICKBOOKS_OBJECT_INVENTORYITEM:
				
				break;
			case QUICKBOOKS_OBJECT_NONINVENTORYITEM:
				
				
				break;
			case QUICKBOOKS_OBJECT_SERVICEITEM:
			default:
				
				$ServiceItem = new QuickBooks_Object_ServiceItem();

				$map = array(
					'Name' => 					array( 'setName', 'Name' ),
					'IsActive' => 				array( 'setIsActive', 'IsActive' ), 
					'ParentID' => 				array( 'setParentApplicationID', 'ParentRef ' . QUICKBOOKS_API_APPLICATIONID, 'setParentListID' ),
					'ParentListID' => 			array( 'setParentListID', 'ParentRef ListID' ),
					'ParentName' => 			array( 'setParentName', 'ParentRef FullName' ),
					'SalesTaxCodeID' => 		array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					'SalesTaxCodeListID' => 	array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ),
					'SalesTaxCodeName' =>		array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ),  
					);  

				
				if (!empty($arr['SalesAndPurchase_PurchaseCost']) and !empty($arr['SalesAndPurchase_SalesPrice']))
				{
					$map = array_merge($map, array(
						'SalesAndPurchase_SalesDesc' => array( 'setSalesDescription', 'SalesAndPurchase SalesDesc'), 
						));
				}
				else
				{
					$map = array_merge($map, array(
						'SalesOrPurchase_Desc' => 			array( 'setDescription', 'SalesOrPurchase Desc' ), 
						'SalesOrPurchase_Price' => 			array( 'setPrice', 'SalesOrPurchase Price' ),
						'SalesOrPurchase_AccountID' => 		array( 'setAccountApplicationID', 'AccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setAccountListID' ),
						'SalesOrPurchase_AccountListID' => 	array( 'setAccountListID', 'AccountRef ListID' ), 
						'SalesOrPurchase_AccountName' => 	array( 'setAccountName', 'AccountRef FullName' ),  
						));
				}
				
				$this->_applyBaseMap($arr, $map, $ServiceItem, QUICKBOOKS_OBJECT_SERVICEITEM);
				
				return $ServiceItem;
		}
	}

	/**
	 * Create a QuickBooks shipping (actually an item) object, from an array
	 * 
	 * This method can return items of the following type:
	 * 	- QuickBooks_Object_ServiceItem
	 * 	- QuickBooks_Object_OtherChargeItem
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object
	 */
	protected function _shippingFromArray($arr)
	{
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				break;
			case QUICKBOOKS_OBJECT_SALESORDER:
				
				break;
			case QUICKBOOKS_OBJECT_INVOICE:
			default:
				
				$InvoiceLine = new QuickBooks_Object_Invoice_InvoiceLine();
				
				if (!empty($arr['Rate']))
				{
					$InvoiceLine->setRate($arr['Rate']);
					$InvoiceLine->setQuantity(1);
				}
				else if (!empty($arr['Amount']))
				{
					$InvoiceLine->setAmount((float) $arr['Amount']);
				}
				else
				{
					$InvoiceLine->setAmount(0);
				}
				
				$map = array(
					'Desc' => 		array( 'setDescription', 'Invoice InvoiceLine Desc' ),
					'ClassID' => 	array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					);
				
				$InvoiceLine = $this->_applyBaseMap($arr, $map, $InvoiceLine, 'Invoice InvoiceLine');	
				
				$InvoiceLine->setItemApplicationID(QUICKBOOKS_INTEGRATOR_SHIPPING_ID);
				
				return $InvoiceLine;
		}
	}
	
	/**
	 * Create a customer object instance, from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_Customer
	 */
	protected function _customerFromArray($arr)
	{
		$Customer = new QuickBooks_Object_Customer();
		
			// Array Key => array( Method, QuickBooks Field for Cast ), 
		$map = array(
			'Name' => 			array( 'setName', 'Name' ), 
			'FirstName' => 		array( 'setFirstName', 'FirstName' ),
			'MiddleName' => 	array( 'setMiddleName', 'MiddleName' ),
			'LastName' => 		array( 'setLastName', 'LastName' ), 
			'CompanyName' => 	array( 'setCompanyName', 'CompanyName' ), 
			'Phone' => 			array( 'setPhone', 'Phone' ), 
			'AltPhone' => 		array( 'setAltPhone', 'AltPhone' ), 
			'Email' => 			array( 'setEmail', 'Email' ), 
			'Contact' => 		array( 'setContact', 'Contact' ), 
			);
		
		$this->_applyBaseMap($arr, $map, $Customer, QUICKBOOKS_OBJECT_CUSTOMER);

		$map2 = array( 
			'ShipAddress_' => 'setShipAddress', 
			'BillAddress_' => 'setBillAddress', 
			);
		
		$this->_applyAddressMap($arr, $map2, $Customer, QUICKBOOKS_OBJECT_CUSTOMER);
		
		return $Customer;
	}
}

?>