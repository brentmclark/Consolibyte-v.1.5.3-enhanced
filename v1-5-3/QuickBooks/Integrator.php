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
if (!defined('QUICKBOOKS_INTEGRATOR_LOOKBACK'))
{
	/** 
	 * How far back in the past to look for new orders 
	 * 
	 * Many shopping carts will place temporary orders in their order tables, 
	 * or orders will be marked to not be pushed to QuickBooks until a certain 
	 * status is reached, or something similar. We want to still catch these 
	 * orders and send them to QuickBooks, so we can't *really* sync from the 
	 * last time things ran, we have to sync back in the past to make sure we 
	 * catch these orders. 
	 * 
	 * @var integer
	 */
	define('QUICKBOOKS_INTEGRATOR_LOOKBACK', 60 * 60 * 24 * 30);
}

//
if (!defined('QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME', 'Discount (QBi)');
}

//
if (!defined('QUICKBOOKS_INTEGRATOR_SHIPPING_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_SHIPPING_NAME', 'Shipping Charge (QBi)');
}

//
if (!defined('QUICKBOOKS_INTEGRATOR_COUPON_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_COUPON_NAME', 'Coupon (QBi)');
}

// 
if (!defined('QUICKBOOKS_INTEGRATOR_HANDLING_NAME'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_INTEGRATOR_HANDLING_NAME', 'Handling Charge (QBi)');
}

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_SHIPPING_ID', 'shipping');

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_HANDLING_ID', 'handling');

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_COUPON_ID', 'coupon');

/**
 * 
 */
define('QUICKBOOKS_INTEGRATOR_DISCOUNT_ID', 'discount');

/** 
 *
 */
define('QUICKBOOKS_INTEGRATOR_NULL', 'quickbooks-integrator-null');

// @Todo Change these to use constants
define('QUICKBOOKS_INTEGRATOR_TABLE_ACCOUNT', 'qb_integrator_account');
define('QUICKBOOKS_INTEGRATOR_TABLE_CUSTOMERTYPE', 'qb_integrator_customertype');
define('QUICKBOOKS_INTEGRATOR_TABLE_PAYMENTMETHOD', 'qb_integrator_paymentmethod');
define('QUICKBOOKS_INTEGRATOR_TABLE_SHIPMETHOD', 'qb_integrator_shipmethod');
define('QUICKBOOKS_INTEGRATOR_TABLE_ITEM', 'qb_integrator_item');

define('QUICKBOOKS_INTEGRATOR_HOOK_SAVEOBJECT', 'QuickBooks_Integrator save-object');

/**
 * 
 */
//define('QUICKBOOKS_QUERY_NULL', 'quickbooks-query-null');

/**
 * QuickBooks Integrator base class
 */
abstract class QuickBooks_Integrator
{
	/**
	 * Integrator driver class
	 * @deprecated
	 * @var object
	 */
	protected $_integrator;
	
	/**
	 * Integrator driver class
	 * @var object
	 */
	protected $_driver;
	
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
	final public function __construct($integrator_driver, $config, $API, $init = array())
	{
		// @deprecated
		$this->_integrator = $integrator_driver;
		
		// This is the correct version of above... 
		$this->_driver = $integrator_driver;
		
		$this->_config = $this->_defaults($config);
		$this->_api = $API;
		
		$this->_init($init);
	}

	protected function _init($options)
	{
		foreach ($options as $key => $value)
		{
			$this->$key = $value;
		}
	}
	
	/**
	 * Send a message to the driver to be logged
	 *
	 * @param string $msg		The message to be sent
	 * @return boolean		Wether or not the message got logged
	 */
	protected function _log($msg, $lvl = null)
	{
		return $this->_api->log($msg, $lvl);
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
			'debug_datetime' => null, 
			
			'push_orders_as' => QUICKBOOKS_OBJECT_INVOICE, 
			'push_products_as' => QUICKBOOKS_OBJECT_SERVICEITEM,
			'push_discounts_as' => QUICKBOOKS_OBJECT_DISCOUNTITEM, 
			'push_coupons_as' => QUICKBOOKS_OBJECT_DISCOUNTITEM, 
			'push_shipping_as' => QUICKBOOKS_OBJECT_SERVICEITEM,  
			'push_handling_as' => QUICKBOOKS_OBJECT_SERVICEITEM, 
			'push_giftcertificates_as' => QUICKBOOKS_OBJECT_OTHERCHARGEITEM, 
			
			'sales_account_name' => 'Sales', 
			'cogs_account_name' => 'COGS', 
			'income_account_name' => 'Sales', 
			'asset_account_name' => 'Assets', 
			'shipping_account_name' => 'Services', 
			'handling_account_name' => 'Services', 
			'discount_account_name' => 'Discounts', 
			'coupon_account_name' => 'Coupons', 
			'salestax_account_name' => 'Sales Tax', 
			
			'customer_defaults' => array(
				'CustomerTypeName' => null, 
				// ... other additional fields
				), 
			
			'customer_extra_queries' => array(), 
			
			'order_defaults' => array(
				
				),
			
			'orderitem_additional_queries' => array(), 
			
			'product_defaults' => array(
				'SalesOrPurchase_AccountName' => 'Sales', 
				'SalesAndPurchase_IncomeAccountName' => 'Sales', 
				'SalesAndPurchase_ExpenseAccountName' => 'COGS', 
				), 
			
			//'customer_default_type_name' => null, 
			//'customer_default_terms_name' => null, 
			//'customer_default_salesrep_name' => null, 
			//'customer_default_salestaxcode_name' => null, 
			//'customer_default_pricelevel_name' => null, 
			//'customer_default_salestaxitem_name' => null, 
			
			
			'tax_code_taxable' => 'TAX', 
			'tax_code_nontaxable' => 'NON', 
			'send_orders_as_pending' => false, 
			'send_orders_as_to_be_emailed' => false, 
			'send_orders_as_to_be_printed' => true, 
			'customer_name_for_add_format' => '', 
			'customer_name_for_modify_format' => '', 
			'customer_name_for_query_format' => '$Name', 
			'shipmethod_name_for_query_format' => '$Name', 
			'paymentmethod_name_for_query_format' => '$Name', 
			'order_refnumber_for_query_format' => '$RefNumber', 
			'product_name_for_query_format' => '$Name', 
			'class_name_for_query_format' => '$Name', 
			'account_name_for_query_format' => '$Name', 
			'default_listneworderssince_query' => null, 
			'default_listmodifiedorderssince_query' => null, 
			'default_listnewcustomerssince_query' => null, 
			'default_listmodifiedcustomerssince_query' => null, 
			'default_getorderitemsfororder_query' => null, 
			'default_getorder_query' => null, 
			'default_getpaymentfororder_query' => null, 
			'default_getproduct_query' => null, 
			'default_getcustomer_query' => null, 
			'default_getgiftcertificate_query' => null, 
			'default_getshipmethod_query' => null, 
			'default_getpaymentmethod_query' => null, 
			'default_discount_query' => null, 
			'default_getshipping_query' => null, 
			'default_gethandling_query' => null, 
			'default_coupon_query' => null, 
			'default_salestax_query' => null, 
			'default_orderitem_query' => null, 
			'default_payment_query' => null, 
			);
			
		return array_merge($defaults, $config);
	}
	
	/**
	 * Clean an HTML string to remove HTML (send as plain-text to QuickBooks)
	 * 
	 * @param string $html		The HTML laden string
	 * @return string			The plain-text string
	 */
	protected function _cleanHTML($html)
	{
		$replace = array(
			'&nbsp;' => ' ',
			'<br />' => "\n",
			'<br>' => "\n",
			'<li>' => ' * ',
			'&amp;' => '&',
			'&gt;' => '>',
			'&lt;' => '<',
			'&quot;' => '"',
			'&apos;' => '\'',
			);
		
		$html = str_replace(array_keys($replace), array_values($replace), $html);
		$html = str_replace(array_keys($replace), array_values($replace), $html);		// Sometimes &amp;nbsp; will get half converted first, finish converting it with this run
		$html = strip_tags($html);
		$html = str_replace(array_keys($replace), array_values($replace), $html);		// Sometimes &amp;nbsp; will get half converted first, finish converting it with this run
		
		while (false !== strpos($html, '  '))
		{
			$html = str_replace('  ', ' ', $html);
		}
		
		
		
		return $html;
	}
	
	abstract protected function _defaultGetOrderQuery();
	
	protected function _getOrderQuery($OrderID)
	{
		$sql = $this->_config['default_getorder_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetOrderQuery();
		}
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $OrderID, 
				'OrderID' => $OrderID
				));
	}
	
	protected function _getCustomerExtrasQueries($CustomerID)
	{
		$queries = $this->_config['customer_extras_queries'];
		
		foreach ($queries as $key => $sql)
		{
			$queries[$key] = $this->_applyFormat($sql, 
				array( 
					'ID' => $CustomerID,
					'CustomerID' => $CustomerID, 
					), true);
		}
		
		return $queries;
	}
	
	protected function _getOrderItemsForOrderAdditionalQueries($OrderID)
	{
		$queries = $this->_config['orderitem_additional_queries'];
		
		foreach ($queries as $key => $sql)
		{
			$queries[$key] = $this->_applyFormat($sql, 
				array( 
					'ID' => $OrderID,
					'OrderID' => $OrderID, 
					), true);
		}
		
		return $queries;
	}	
	
	abstract protected function _defaultGetCustomerQuery();
	
	protected function _getCustomerQuery($CustomerID)
	{
		$sql = $this->_config['default_getcustomer_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetCustomerQuery();
		}
		
		//$CustomerID = $this->_integrator->escape($CustomerID);
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $CustomerID,
				'CustomerID' => $CustomerID, 
				), true);
	}
	
	abstract protected function _defaultGetProductQuery();
	
	protected function _getProductQuery($ProductID)
	{
		$sql = $this->_config['default_getproduct_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetProductQuery();
		}
		
		$ProductID = $this->_integrator->escape($ProductID);
		
		return $this->_applyFormat($sql, 
			array( 
				'ProductID' => $ProductID, 
				'ID' => $ProductID, 
				'IncomeAccountName' => $this->_config['income_account_name'], 
				'COGSAccountName' => $this->_config['cogs_account_name'], 
				'AssetAccountName' => $this->_config['asset_account_name'], 
				), true);
	}

	abstract protected function _defaultGetGiftCertificateQuery();

	protected function _getGiftCertificateQuery($GiftCertificateID)
	{
		$sql = $this->_config['default_getgiftcertificate_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetGiftCertificateQuery();
		}
		
		$GiftCertificateID = $this->_integrator->escape($GiftCertificateID);
		
		return $this->_applyFormat($sql, 
			array( 
				'GiftCertificateID' => $GiftCertificateID, 
				'ID' => $GiftCertificateID, 
				'IncomeAccountName' => $this->_config['income_account_name'], 
				'COGSAccountName' => $this->_config['cogs_account_name'], 
				'AssetAccountName' => $this->_config['asset_account_name'], 
				));
	}
	
	abstract protected function _defaultGetShipMethodQuery();
	
	protected function _getShipMethodQuery($ShipMethodID)
	{
		$sql = $this->_config['default_getshipmethod_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetShipMethodQuery();
		}
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $ShipMethodID,
				'ShipMethodID' => $ShipMethodID, 
				));
	}
	
	abstract protected function _defaultGetHandlingForOrderQuery();
	
	protected function _getHandlingForOrderQuery($OrderID)
	{
		$sql = $this->_config['default_gethandling_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetHandlingForOrderQuery();
		}
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $OrderID, 
				'OrderID' => $OrderID,
				));
	}	
	
	abstract protected function _defaultGetShippingForOrderQuery();
	
	protected function _getShippingForOrderQuery($OrderID)
	{
		$sql = $this->_config['default_getshipping_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetShippingForOrderQuery();
		}
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $OrderID, 
				'OrderID' => $OrderID,
				));
	}
	
	//abstract protected function _defaultPaymentMethodQuery();
	
	protected function _getPaymentMethodQuery($OrderID)
	{
		$sql = $this->_config['default_getpaymentmethod_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetPaymentMethodQuery();
		}
		
		$OrderID = $this->_integrator->escape($OrderID);
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $OrderID,
				'OrderID' => $OrderID, 
				 ));
	}
	
	abstract protected function _defaultListNewOrdersSinceQuery();
	
	protected function _listNewOrdersSinceQuery($datetime)
	{
		if (!empty($this->_config['debug_datetime']))
		{
			$datetime = date('Y-m-d H:i:s', strtotime($this->_config['debug_datetime']));
		}
		
		$sql = $this->_config['default_listneworderssince_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultListNewOrdersSinceQuery();
		}
		
		return $this->_applyFormat($sql, array( 'datetime' => $datetime ));
	}
	
	public function getOrderItemsForOrder($OrderID)
	{
		return $this->_getOrderItemsForOrder($OrderID);
	}
	
	abstract protected function _getOrderItemsForOrder($OrderID);
	
	public function getEstimateItemsForEstimate($EstimateID)
	{
		return $this->_getEstimateItemsForEstimate($EstimateID);
	}
	
	abstract protected function _getEstimateItemsForEstimate($EstimateID);
	
	protected function _getOrderItemsForOrderQuery($OrderID)
	{
		$sql = $this->_config['default_getorderitemsfororder_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetOrderItemsForOrderQuery();
		}
		
		$OrderID = $this->_integrator->escape($OrderID);
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $OrderID, 
				'OrderID' => $OrderID,
				));
	}
	
	abstract protected function _defaultGetOrderItemsForOrderQuery();	
	
	public function _getPaymentForOrderQuery($OrderID)
	{
		$sql = $this->_config['default_getpaymentfororder_query'];
		if (empty($sql))
		{
			$sql = $this->_defaultGetPaymentForOrderQuery();
		}
		
		$OrderID = $this->_integrator->escape($OrderID);
		
		return $this->_applyFormat($sql, 
			array( 
				'ID' => $OrderID, 
				'OrderID' => $OrderID,
				));		
	}

	abstract protected function _defaultGetPaymentForOrderQuery();
	
	
	/*
	abstract protected function _defaultDiscountQuery();
	
	abstract protected function _defaultShippingQuery();
	
	abstract protected function _defaultCouponQuery();
	
	abstract protected function _defaultSalesTaxQuery();
	
	abstract protected function _defaultOrderItemQuery();
	
	abstract protected function _defaultPaymentQuery();
	*/
	
	abstract protected function _getCustomerExtras($CustomerID);
	
	public function getCustomerExtras($CustomerID, $action = QUICKBOOKS_ADD_CUSTOMER)
	{
		if ($list = $this->_getCustomerExtras($CustomerID))
		{
			return $list;
		}
		
		return array();
	}
	
	/**
	 * Get a customer object by ID value
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_Customer
	 */
	abstract protected function _getCustomer($CustomerID);
	
	public function getCustomer($CustomerID, $action = QUICKBOOKS_ADD_CUSTOMER)
	{
		if ($Customer = $this->_getCustomer($CustomerID))
		{
			/*
			if ($this->_config['customer_name_for_add_format'] or 
				$this->_config['customer_name_for_modify_format'])
			{
				switch ($action)
				{
					case QUICKBOOKS_MOD_CUSTOMER:
						$format = $this->_config['customer_name_for_modify_format'];
						break;
					case QUICKBOOKS_ADD_CUSTOMER:
					default:
						$format = $this->_config['customer_name_for_add_format'];
						break;
				}
				
				$list = $Customer->asList($action);
				$name = $this->_applyFormat($format, $list);
				
				$Customer->setName($name);
			}
			
			//header('Content-Type: text/plain');
			//print_r($Customer);
			//exit;
			*/
			
			return $Customer;
		}
		
		return null;
	}
	
	/**
	 * Get a list ID #s for new customers since $datetime
	 * 
	 * @param string $datetime
	 * @return array
	 */
	abstract protected function _listNewCustomersSince($datetime);
	
	/**
	 * @see QuickBooks_Integrator::_lastNewCustomersSince()
	 */
	public function listNewCustomersSince($datetime)
	{
		return $this->_listNewCustomersSince($datetime);
	}
	
	/**
	 * Get a list of ID #s for modified customers since $datetime
	 * 
	 * @param string $datetime
	 * @return array
	 */
	abstract protected function _listModifiedCustomersSince($datetime);
	
	/**
	 * @see QuickBooks_Integrator::_listModifiedCustomersSince()
	 */
	public function listModifiedCustomersSince($datetime)
	{
		return $this->_listModifiedCustomersSince($datetime);
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	abstract protected function _listNewOrdersSince($datetime);
	
	/**
	 * 
	 * 
	 * @param string $datetime
	 * @param boolean $first_time_running
	 * @return array
	 */
	public function listNewOrdersSince($datetime, $first_datetime, $first_time_running = false)
	{
		if ($first_time_running)
		{
			//print("FIRST TIME RUNNING!");
			$this->_api->log('First time running integration: ' . $first_datetime, QUICKBOOKS_LOG_NORMAL);
		}

		// Use the lookback value... 
		$max = max(strtotime($datetime) - QUICKBOOKS_INTEGRATOR_LOOKBACK, strtotime($first_datetime));
		
		$datetime = date('Y-m-d H:i:s', $max);
		$this->_api->log('New orders from (with lookback time) ' . $datetime . ' to ' . date('Y-m-d H:i:s'), QUICKBOOKS_LOG_DEVELOP);
		
		$list = $this->_listNewOrdersSince($datetime);
		
		//print_r($this);
		//print('datetime: ' . $datetime . "\n");
		//print_r($list);
		
		foreach ($list as $key => $value)
		{
			if ($this->_api->hasQuickBooksID($this->_config['push_orders_as'], $value))
			{
				$this->_api->log('Order #' . $value . ' has already been pushed to QuickBooks! Skipping!', QUICKBOOKS_LOG_DEBUG);
				
				unset($list[$key]);
			}
		}
		
		return $list;
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	abstract protected function _listModifiedOrdersSince($datetime);
	
	public function listModifiedOrdersSince($datetime)
	{
		return $this->_listModifiedOrdersSince($datetime);
	}

	/**
	 * 
	 * 
	 * 
	 */
	abstract protected function _listNewEstimatesSince($datetime);
	
	/**
	 * 
	 * 
	 * @param string $datetime
	 * @param boolean $first_time_running
	 * @return array
	 */
	public function listNewEstimatesSince($datetime, $first_datetime, $first_time_running = false)
	{
		if ($first_time_running)
		{
			//print("FIRST TIME RUNNING!");
			$this->_api->log('First time running integration: ' . $first_datetime, QUICKBOOKS_LOG_NORMAL);
		}
		
		//print('first: ' . $first_datetime . "\n");
		//print('date: ' . $datetime . "\n");
		
		// Use the lookback value... 
		$max = max(strtotime($datetime) - QUICKBOOKS_INTEGRATOR_LOOKBACK, strtotime($first_datetime));
		
		$datetime = date('Y-m-d H:i:s', $max);
		$this->_api->log('New estimates from (with lookback time) ' . $datetime . ' to ' . date('Y-m-d H:i:s'), QUICKBOOKS_LOG_DEVELOP);
		
		$list = $this->_listNewEstimatesSince($datetime);
		
		//print_r($this);
		//print('datetime: ' . $datetime . "\n");
		//print_r($list);
		
		foreach ($list as $key => $value)
		{
			if ($this->_api->hasQuickBooksID(QUICKBOOKS_OBJECT_ESTIMATE, $value))
			{
				$this->_api->log('Estimate #' . $value . ' has already pushed to QuickBooks! Skipping!', QUICKBOOKS_LOG_DEBUG);
				unset($list[$key]);
			}
		}
		
		return $list;
	}
	
	/*
	protected function _customerIDToQuickBooksID($CustomerID)
	{
		if ($this->_api->hasQuickBooksID(QUICKBOOKS_OBJECT_CUSTOMER, $CustomerID))
		{
			return $this->_api->fetchQuickBooks(QUICKBOOKS_OBJECT_CUSTOMER, $CustomerID);
		}
		
		return null;
	}

	protected function _estimateIDToQuickBooksID($EstimateID)
	{
		if ($this->_api->hasQuickBooksID(QUICKBOOKS_OBJECT_ESTIMATE, $EstimateID))
		{
			return $this->_api->fetchQuickBooks(QUICKBOOKS_OBJECT_ESTIMATE, $EstimateID);
		}
		
		return null;
	}
	*/
	
	protected function _quickBooksIDToCustomerID($ListID)
	{
		if ($this->_api->hasApplicationID(QUICKBOOKS_OBJECT_CUSTOMER, $ListID))
		{
			return $this->_api->fetchApplicationID(QUICKBOOKS_OBJECT_CUSTOMER, $ListID);
		}
		
		return null;
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	abstract protected function _listModifiedEstimatesSince($datetime);
	
	public function listModifiedEstimatesSince($datetime)
	{
		return $this->_listModifiedEstimatesSince($datetime);
	}
	
	abstract protected function _setCustomer($CustomerID, $Customer);
	
	public function setCustomer($CustomerID, $Customer)
	{
		return $this->_setCustomer($CustomerID, $Customer);
	}
	
	abstract protected function _setEstimate($EstimateID, $Estimate);
	
	public function setEstimate($EstimateID, $Estimate)
	{
		return $this->_setEstimate($EstimateID, $Estimate);
	}
	
	abstract protected function _getEstimate($ID);
	
	public function getEstimate($ID)
	{
		return $this->_getEstimate($ID);
	}	
	
	/**
	 * 
	 */
	abstract protected function _getOrder($ID);
	
	/**
	 * Get an order by order number (ID value / primary key / etc.)
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_*
	 */
	public function getOrder($ID)
	{
		return $this->_getOrder($ID);
	}
	
	abstract protected function _getOrderItems($ID);
	
	public function getOrderItems($ID)
	{
		// @todo Can we cheat here and just pull the order items directly from the order...?
		return $this->_getOrderItems($ID);
	}
	
	abstract protected function _getPayment($ID);	
	
	public function getPayment($ID)
	{
		return $this->_getPayment($ID);
	}
	
	abstract protected function _getProduct($ID);
	
	public function getProduct($ID)
	{
		return $this->_getProduct($ID);
	}
		
	abstract protected function _getShipMethod($ID);
	
	public function getShipMethod($ID)
	{
		return $this->_getShipMethod($ID);
	}
	
	abstract protected function _getDiscountForOrder($OrderID);
	
	public function getDiscountForOrder($OrderID)
	{
		return $this->_getDiscountForOrder($OrderID);
	}
	
	abstract protected function _getSalesTax($ID);
	
	public function getSalesTax($ID)
	{
		return $this->_getSalesTax($ID);
	}
	
	final public function saveAccount($Account)
	{
		return $this->_saveObject($Account);
	}
	
	final public function savePaymentMethod($PaymentMethod)
	{
		return $this->_saveObject($PaymentMethod);
	}
	
	final public function saveCustomerType($CustomerType)
	{
		return $this->_saveObject($CustomerType);		
	}
	
	final public function saveShipMethod($ShipMethod)
	{
		return $this->_saveObject($ShipMethod);
	}
	
	protected function _callHooks(&$hooks, $hook, $requestID, $user, &$err, $hook_data, $callback_config = array())
	{
		// @TODO Will this work with non-SQL drivers?
		$Driver = $this->_driver;
		
		return QuickBooks_Callbacks::callHook($Driver, $hooks, $hook, $requestID, $user, null, $err, $hook_data, $callback_config, __FILE__, __LINE__);	
	}
	
	final protected function _saveObject($Object)
	{
		$API = $this->_api;
		$Driver = $this->_driver;
		
		// @TODO Will this still work if the integrator is not extended from the database....?
		
		switch ($Object->object())
		{
			case QUICKBOOKS_OBJECT_ACCOUNT:
				$table = QUICKBOOKS_INTEGRATOR_TABLE_ACCOUNT;
				
				$fetch = array(
					'ListID' => 'getListID', 
					'TimeCreated' => 'getTimeCreated', 
					'TimeModified' => 'getTimeModified', 
					'EditSequence' => 'getEditSequence', 
					'Name' => 'getName', 
					'FullName' => 'getFullName',
					'IsActive' => 'getIsActive', 
					'Parent_ListID' => 'getParentListID', 
					'Parent_FullName' => 'getParentFullName', 
					'AccountType' => 'getAccountType', 
					'SpecialAccountType' => 'getSpecialAccountType', 
					'AccountNumber' => 'getAccountNumber', 
					'CashFlowClassification' => 'getCashFlowClassification', 
					);
				
				$where = array( 
					'qb_username' => $API->user(), 
					'FullName' => $Object->getFullName(),
					);
				
				break;
			case QUICKBOOKS_OBJECT_PAYMENTMETHOD:
				$table = QUICKBOOKS_INTEGRATOR_TABLE_PAYMENTMETHOD;
				
				$fetch = array(
					'ListID' => 'getListID', 
					'TimeCreated' => 'getTimeCreated', 
					'TimeModified' => 'getTimeModified', 
					'EditSequence' => 'getEditSequence', 
					'Name' => 'getName', 
					'IsActive' => 'getIsActive', 
					'PaymentMethodType' => 'getPaymentMethodType', 
					);
				
				$where = array( 
					'qb_username' => $API->user(), 
					'Name' => $Object->getName(),
					);				
				
				break;
			case QUICKBOOKS_OBJECT_CUSTOMERTYPE:
				$table = QUICKBOOKS_INTEGRATOR_TABLE_CUSTOMERTYPE;
				
				$fetch = array(
					'ListID' => 'getListID', 
					'TimeCreated' => 'getTimeCreated', 
					'TimeModified' => 'getTimeModified', 
					'EditSequence' => 'getEditSequence', 
					'Name' => 'getName', 
					'FullName' => 'getFullName', 
					'IsActive' => 'getIsActive', 
					'Parent_ListID' => 'getParentListID', 
					'Parent_FullName' => 'getParentFullName', 
					);
				
				$where = array( 
					'qb_username' => $API->user(), 
					'FullName' => $Object->getFullName(),
					);
					
				break;
			case QUICKBOOKS_OBJECT_SHIPMETHOD:
				$table = QUICKBOOKS_INTEGRATOR_TABLE_SHIPMETHOD;

				$fetch = array(
					'ListID' => 'getListID', 
					'TimeCreated' => 'getTimeCreated', 
					'TimeModified' => 'getTimeModified', 
					'EditSequence' => 'getEditSequence', 
					'Name' => 'getName', 
					'IsActive' => 'getIsActive', 
					);
				
				$where = array( 
					'qb_username' => $API->user(), 
					'Name' => $Object->getName(),
					);				
								
				break;
			default:
				return false;
		}
		
		$data = array(
			'qb_username' => $API->user(), 
			);
		foreach ($fetch as $field => $method)
		{
			$data[$field] = $Object->$method();
		}
		
		// Call any integrator hooks
		
		// @todo Shouldn't this get assigned to something?
		$callback_config = array();
		
		$hook_data = array(
			'user' => $API->user(), 
			'table' => $table, 
			'data' => $data, 
			'where' => $where, 
			);
		
		$requestID = null;
		$user = $API->user();
		$hookerr = null;
		$this->_callHooks(
			$this->_hooks, 
			QUICKBOOKS_INTEGRATOR_HOOK_SAVEOBJECT, 
			$requestID, 
			$user, 
			$hookerr, 
			$hook_data, 
			$callback_config);
		
		// First, check if the record exists or not
		if ($tmp = $Driver->get($table, $where))
		{
			// Update it
			$Driver->update($table, $data, array( $where ), false, false);
			
			return true;
		}
		else
		{
			// Insert it
			$Driver->insert($table, $data, false);
			
			return true;
		}
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	public function getGenericShipping()
	{
		return $this->_getGenericShipping();
	}
	
	/**
	 * 
	 * 
	 * @note This method can be overridden by child classes to provide customization or custom functionality of the generic shipping item
	 */
	protected function _getGenericShipping()
	{
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_SHIPPING_ID,
			'Name' => QUICKBOOKS_INTEGRATOR_SHIPPING_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_SHIPPING_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			//'SalesOrPurchase_AccountName' => null, 
			);
		
		$Item = $this->_productFromArray($arr, $this->_config['push_shipping_as']);
		
		return $Item;		
	}

	public function getGenericHandling()
	{
		$current = $this->_config['push_products_as'];
		$this->_config['push_products_as'] = $this->_config['push_shipping_as'];
		
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_HANDLING_ID,
			'Name' => QUICKBOOKS_INTEGRATOR_HANDLING_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_HANDLING_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			//'SalesOrPurchase_AccountName' => null, 
			);
		
		$Item = $this->_productFromArray($arr);	
		
		$this->_config['push_products_as'] = $current;
		
		return $Item;
	}
	
	public function getGenericDiscount()
	{
		return $this->_getGenericDiscount();
	}
	
	/**
	 * 
	 * 
	 * @note This method can be overridden by child classes to provide customization or custom functionality of the generic shipping item
	 */
	protected function _getGenericDiscount()
	{
		$current = $this->_config['push_products_as'];
		$this->_config['push_products_as'] = $this->_config['push_discounts_as'];
		
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_DISCOUNT_ID, 
			'Name' => QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			//'SalesOrPurchase_AccountName' => null, 
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
			//'SalesOrPurchase_AccountName' => null, 
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
		$Customer = $this->getCustomer($ID, __FILE__, __LINE__);
		
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
		//print('getting product by ID: {' . $ID . '}');
		
		$Product = $this->getProduct($ID);
		
		//print('prod: ');
		//print_r($Product);
		//print('}}');
		
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
	
	
	
	/*
	public function getCustomerIDFromCustomer($Customer)
	{
		return $this->_getCustomerIDFromCustomer($Customer);
	}
	
	abstract protected function _getCustomerIDFromCustomer($Customer);
	
	public function getInvoiceIDFromInvoice($Invoice)
	{
		return $this->_getInvoiceIDFromInvoice();
	}
	
	abstract protected function _getInvoiceIDFromInvoice($Invoice);
	
	public function getEstimateIDFromEstimate($Estimate)
	{
		return $this->_getEstimateIDFromEstimate($Estimate);
	}
	
	abstract protected function _getEstimateIDFromEstimate($Estimate);
	*/
	
	protected function _createMapping($object_type, $webapp_ID, $ListID_or_TxnID, $editsequence = null)
	{
		return $this->_api->createMapping($object_type, $webapp_ID, $ListID_or_TxnID, $editsequence);
	}
	
	/**
	 * Apply a format string to an array, to generate a string
	 * 
	 * @param string $format
	 * @param array $arr
	 * @return string 
	 */
	protected function _applyFormat($format, $arr, $wrap_and_escape = false)
	{
		$func = create_function('$a, $b', '
			if (is_string($a) and is_string($b)) 
			{ 
				if (strlen($a) > strlen($b)) 
				{ 
					return -1; 
				} 
				else 
				{ 
					return 1; 
				} 
			}
			return 0;');
		
		uasort($arr, $func);
		
		foreach ($arr as $key => $value)
		{
			if (is_string($value))
			{
				if ($wrap_and_escape)
				{
					$value = $this->_wrapAndEscape($value);
				}
				
				$format = str_replace('$' . $key, $value, $format);
			}
		}
		
		return $format;
	}
	
	/**
	 * 
	 * 
	 * @param mixed $value
	 * @return mixed 
	 */
	abstract protected function _wrapAndEscape($value);
	
	/**
	 * Force fields to pre-defined booleans if those fields are empty
	 * 
	 * @param array $force_boolean_if_null
	 * @param object $obj
	 * @return void
	 */
	protected function _forceNullBooleans($arr, $force_boolean_if_null, &$obj)
	{
		foreach ($force_boolean_if_null as $key => $tmp)
		{
			if (!isset($arr[$key]))
			{
				$force_it_to = $tmp[0];
				$func = $tmp[1];
				
				if ($force_it_to)
				{
					$obj->$func($force_it_to);
				}
				else
				{
					$obj->$func($force_it_to);
				}
			}
		}
		
		return;
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _applyDefaults($arr, $defaults)
	{
		return array_merge($defaults, $arr);
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
	protected function _applyBaseMap($arr, $map, &$obj, $type, $path = '')
	{
		if ($path)
		{
			$path = trim($path) . ' ';
		}
		
		foreach ($map as $field => $tmp)
		{
			//print($field . ' => ' . $arr[$field] . "\n");
			
			if (isset($arr[$field]) and strlen($arr[$field]))
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
				else if ($qbfield and strlen($resolve) and
					(
						(is_numeric($value) and (int) $value) or
						(!is_numeric($value) and strlen($value))
					))
				{
					// Try to resolve a value to a ListID or TxnID
					$obj->$method($value);
					$encode = $obj->get($qbfield);
					//$obj->remove($qbfield);
					
					$reftype = null;
					$reftag = null;
					$refid = null;
					
					$obj->decodeApplicationID($encode, $reftype, $reftag, $refid);
					
					//$API = QuickBooks_API_Singleton::getInstance();
					
					// Early auto-resolving of TxnIDs and ListIDs is *not* a good idea!
					// 	It introduces cases where someone may have deleted the record already from QuickBooks,
					//	and it'll get caught because the integrator server double checks for these things, but
					//	the early auto-resolving will auto-resolve it *before* it can get caught by the
					//	server double-checks! 
					/*
					if ($ListID_or_TxnID = $API->fetchQuickBooksID($reftype, $value))
					{
						$obj->$resolve($ListID_or_TxnID);
						$set = false;
					}
					*/
				}
				else
				{
					$set = false;
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
	 * @param object $obj
	 * @param string $type
	 * @return null
	 */
	protected function _applyCreditCardInfoMap($arr, $map, &$obj, $type)
	{
		if (!count($arr))
		{
			return null;
		}
		
		foreach ($map as $cardinfotype => $method)
		{
			// Default values
			$defaults = array(
				'CreditCardNumber' => '', 
				'ExpirationMonth' => 0, 
				'ExpirationYear' => 0, 
				'NameOnCard' => '', 
				'CreditCardAddress' => '', 
				'CreditCardPostalCode' => '', 
			);

			foreach ($defaults as $key => $default)
			{
				// 				"CreditCardInfo_CreditCardNumber"
				if (!empty($arr[$cardinfotype . '_' . $key]))
				{
					//								QUICKBOOKS_OBJECT_INVOICE, "ShipAddress Addr1", "56 Cowles Road"
					$casted = QuickBooks_Cast::cast($type, $cardinfotype . ' ' . $key, $arr[$cardinfotype . '_' . $key]);
					$defaults[$key] = $casted;
				}
			}
			
			$obj->$method(
				$defaults['CreditCardNumber'], 
				$defaults['ExpirationMonth'], 
				$defaults['ExpirationYear'], 
				$defaults['NameOnCard'], 
				$defaults['CreditCardAddress'], 
				$defaults['CreditCardPostalCode']);
			
		}
		
		return null;		
	}
	
	/**
	 * 
	 * 
	 * * WARNING * 
	 * This function should *NOT* return an object! That behavior is *deprecated*!
	 * 
	 * @param array $arr
	 * @param array $map
	 * @param QuickBooks_Object
	 * @param string $type
	 * @return null
	 */
	protected function _applyAddressMap($arr, $map, &$obj, $type)
	{
		if (!count($arr))
		{
			return null;
		}		
		
		// 				"ShipAddress" => "setShipAddress"
		foreach ($map as $addrtype => $method)
		{
			
			// Some integrators pass things like "ShipAddress_Address1" instead of "ShipAddress_Addr1" so we fix this here
			for ($i = 1; $i <= 5; $i++)
			{
				if (!empty($arr[$addrtype . '_Address' . $i]) and 
					empty($arr[$addrtype . '_Addr' . $i]))
				{
					$arr[$addrtype . '_Addr' . $i] = $arr[$addrtype . '_Address' . $i];
				}
			}
			
			// Compress empty address lines
			for ($i = 2; $i <= 5; $i++)
			{
				if (empty($arr[$addrtype . '_Addr' . ($i - 1)]) and 
					!empty($arr[$addrtype . '_Addr' . $i]))
				{
					$arr[$addrtype . '_Addr' . ($i - 1)] = $arr[$addrtype . '_Addr' . $i];
					$arr[$addrtype . '_Addr' . $i] = '';
				}
			}
			
			// Default values
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
				'Country' => '', 
				'Notes' => '', 
				);

			foreach ($defaults as $key => $default)
			{
				// 				"ShipAddress_Addr1"
				if (!empty($arr[$addrtype . '_' . $key]))
				{
					//								QUICKBOOKS_OBJECT_INVOICE, "ShipAddress Addr1", "56 Cowles Road"
					$casted = QuickBooks_Cast::cast($type, $addrtype . ' ' . $key, $arr[$addrtype . '_' . $key]);
					$defaults[$key] = $casted;
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
	protected function _orderFromArray($arr, $items, $shipping = null, $handling = null, $discount = null)
	{
		// Apply default values 
		$arr = $this->_applyDefaults($arr, $this->_config['order_defaults']);
		
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				$SalesReceipt = new QuickBooks_Object_SalesReceipt();
				
				$map = array(
					'CustomerID' => 	array( 'setCustomerApplicationID', 'CustomerRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerListID' ), 
					'CustomerName' => 	array( 'setCustomerName', 'CustomerRef Name' ), 
					'ClassID' => 		array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					'ClassName' => 		array( 'setClassName', 'ClassRef FullName' ), 
					'DepositToAccountID' => array( 'setDepositToAccountApplicationID', 'DepositToAccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setDepositToAccountListID' ), 
					'DepositToAccountName' => array( 'setDepositToAccountName', 'DepositToAccountRef FullName' ), 
					'TemplateID' => 	array( 'setTemplateApplicationID', 'TemplateRef ' . QUICKBOOKS_API_APPLICATIONID, 'setTemplateListID' ), 
					'TemplateName' => 	array( 'setTemplateName', 'TemplateRef FullName' ), 
					'TxnDate' => 		array( 'setTxnDate', 'TxnDate' ), 
					'RefNumber' => 		array( 'setRefNumber', 'RefNumber' ), 
					'PONumber' => 		array( 'setPONumber', 'PONumber' ), 
					'TermsID' => 		array( 'setTermsApplicationID', 'TermsRef ' . QUICKBOOKS_API_APPLICATIONID, 'setTermsListID' ), 
					'TermsName' => 		array( 'setTermsName', 'TermsRef FullName' ), 
					'SalesRepID' => 	array( 'setSalesRepApplicationID', 'SalesRepRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesRepListID' ), 
					'SalesRepName' => 	array( 'setSalesRepName', 'SalesRepRef FullName' ), 
					'FOB' => 			array( 'setFOB', 'FOB' ), 
					'ShipDate' => 		array( 'setShipDate', 'ShipDate' ), 
					'ShipMethodID' => 	array( 'setShipMethodApplicationID', 'ShipMethodRef ' . QUICKBOOKS_API_APPLICATIONID, 'setShipMethodListID' ), 
					'ShipMethodName' => array( 'setShipMethodName', 'ShipMethodRef FullName' ), 
					'ItemSalesTaxID' => array( 'setSalesTaxItemApplicationID', 'SalesTaxItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxItemListID' ), 
					'ItemSalesTaxName' => array( 'setSalesTaxItemName', 'ItemSalesTaxRef FullName' ), 
					'CustomerMsgID' => 	array( 'setCustomerMsgApplicationID', 'CustomerMsgRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerMsgListID' ), 
					'Memo' => 			array( 'setMemo', 'Memo' ), 
					'IsPending' => 		array( 'setIsPending', 'IsPending' ), 
					'IsToBePrinted' => 	array( 'setIsToBePrinted', 'IsToBePrinted' ), 
					'IsToBeEmailed' => 	array( 'setIsToBeEmailed', 'IsToBeEmailed' ), 
					'CustomerSalesTaxCodeID' => array( 'setCustomerSalesTaxCodeApplicationID', 'CustomerSalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerSalesTaxCodeListID' ), 
					'CustomerSalesTaxCodeName' => array( 'setCustomerSalesTaxCodeName', 'CustomerSalesTaxCodeRef FullName' ), 
					'Other' => 			array( 'setOther', 'Other' ), 
					);
				
				$this->_applyBaseMap($arr, $map, $SalesReceipt, QUICKBOOKS_OBJECT_SALESRECEIPT);
				
				if (!empty($arr['TxnDate']))
				{
					$SalesReceipt->setTxnDate($arr['TxnDate']);
				}
				else
				{
					$SalesReceipt->setTxnDate(date('Y-m-d'));
				}
								
				// These things should be forced to a specific value if they havn't been explicitly set 
				$force_boolean_if_empty = array(
					// $key => 			array( $force_it_to, $function ), 
					'IsPending' => 		array( $this->_config['send_orders_as_pending'], 'setIsPending' ), 
					'IsToBeEmailed' => 	array( $this->_config['send_orders_as_to_be_emailed'], 'setIsToBeEmailed' ), 
					'IsToBePrinted' => 	array( $this->_config['send_orders_as_to_be_printed'], 'setIsToBePrinted' ), 
					);
				
				//$this->_forceNullBooleans($arr, $force_boolean_if_empty, $SalesReceipt);
				
				
				// Shipping and billing information
				$map2 = array( 
					'ShipAddress' => 'setShipAddress', 
					'BillAddress' => 'setBillAddress', 
					);
				
				$this->_applyAddressMap($arr, $map2, $SalesReceipt, QUICKBOOKS_OBJECT_SALESRECEIPT);
				
				foreach ($items as $item)
				{
					if (is_object($item))
					{
						$SalesReceipt->addSalesReceiptLine($item);
					}
				}
				
				if (is_object($shipping))
				{
					$SalesReceipt->addSalesReceiptLine($shipping);
				}
				
				if (is_object($handling))
				{
					$SalesReceipt->addSalesReceiptLine($handling);
				}
				
				if (is_object($discount))
				{
					$SalesReceipt->addSalesReceiptLine($discount);
				}
				
				/*
				header('Content-Type: text/plain');
				print_r($arr);
				print_r($SalesReceipt);
				exit;
				*/
				
				return $SalesReceipt;
				
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
				
				
				// These things should be forced to a specific value if they havn't been explicitly set 
				$force_boolean_if_empty = array(
					// $key => 			array( $force_it_to, $function ), 
					'IsPending' => 		array( $this->_config['send_orders_as_pending'], 'setIsPending' ), 
					'IsToBeEmailed' => 	array( $this->_config['send_orders_as_to_be_emailed'], 'setIsToBeEmailed' ), 
					'IsToBePrinted' => 	array( $this->_config['send_orders_as_to_be_printed'], 'setIsToBePrinted' ), 
					);
				
				$this->_forceNullBooleans($arr, $force_boolean_if_empty, $Invoice);
				
				
				// Shipping and billing information
				$map2 = array( 
					'ShipAddress' => 'setShipAddress', 
					'BillAddress' => 'setBillAddress', 
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
				
				if (is_object($handling))
				{
					$Invoice->addInvoiceLine($handling);
				}
				
				if (is_object($discount))
				{
					$Invoice->addInvoiceLine($discount);
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
				
				$SalesReceiptLine = new QuickBooks_Object_SalesReceipt_SalesReceiptLine();
				
				$map = array(
					'ProductID' => 			array( 'setItemApplicationID', 'ItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setItemListID' ),
					'ItemID' => 			array( 'setItemApplicationID', 'ItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setItemListID' ), 
					'ProductName' => 		array( 'setItemName', 'ItemRef FullName' ),  
					'Desc' => 				array( 'setDescription', 'Desc' ),
					'Descrip' => 			array( 'setDescription', 'Desc' ), 
					'Description' => 		array( 'setDescription', 'Desc' ),  
					'Quantity' => 			array( 'setQuantity', 'Quantity' ),  
					'Rate' => 				array( 'setRate', 'Rate' ),
					'Amount' => 			array( 'setAmount', 'Amount' ), 
					
					'ClassID' => 			array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ),
					'ClassName' => 			array( 'setClassName', 'ClassRef FullName' ), 
					'ClassListID' => 		array( 'setClassListID', 'ClassRef ListID' ), 
					
					'SalesTaxCodeID' => 	array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					'SalesTaxCodeName' => 	array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ), 
					'SalesTaxCodeListID' => array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ), 
					);
				
				$SalesReceiptLine = $this->_applyBaseMap($arr, $map, $SalesReceiptLine, 'SalesReceipt SalesReceiptLine');
				
				return $SalesReceiptLine;
			case QUICKBOOKS_OBJECT_SALESORDER:
				
				break;
			case QUICKBOOKS_OBJECT_INVOICE:
			default:
				
				$InvoiceLine = new QuickBooks_Object_Invoice_InvoiceLine();
				
				$map = array(
					'ProductID' => 			array( 'setItemApplicationID', 'ItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setItemListID' ),
					'ItemID' => 			array( 'setItemApplicationID', 'ItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setItemListID' ),
					'ProductName' => 		array( 'setItemName', 'ItemRef FullName' ),  
					'Desc' => 				array( 'setDescription', 'Desc' ),
					'Descrip' => 			array( 'setDescription', 'Desc' ), 
					'Description' => 		array( 'setDescription', 'Desc' ),  
					'Quantity' => 			array( 'setQuantity', 'Quantity' ),  
					'Rate' => 				array( 'setRate', 'Rate' ),
					'Amount' => 			array( 'setAmount', 'Amount' ), 
					
					'ClassID' => 			array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					'ClassName' => 			array( 'setClassName', 'ClassRef FullName' ), 
					'ClassListID' => 		array( 'setClassListID', 'ClassRef ListID' ), 
					
					'SalesTaxCodeID' => 	array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					'SalesTaxCodeName' => 	array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ), 
					'SalesTaxCodeListID' => array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ), 					
					);
				
				$InvoiceLine = $this->_applyBaseMap($arr, $map, $InvoiceLine, 'Invoice InvoiceLine');	
				
				return $InvoiceLine;
		}
	}

	protected function _estimateFromArray($arr, $items, $shipping = null, $handling = null, $discount = null)
	{
		// Apply default values 
		$arr = $this->_applyDefaults($arr, $this->_config['estimate_defaults']);

		$Estimate = new QuickBooks_Object_Estimate();
		
		$map = array(
			'CustomerID' => 	array( 'setCustomerApplicationID', 'CustomerRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerListID' ), 
			'CustomerName' => 	array( 'setCustomerName', 'CustomerRef Name' ), 
			'ClassID' => 		array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
			'ClassName' => 		array( 'setClassName', 'ClassRef FullName' ), 
			'DepositToAccountID' => array( 'setDepositToAccountApplicationID', 'DepositToAccountRef ' . QUICKBOOKS_API_APPLICATIONID, 'setDepositToAccountListID' ), 
			'DepositToAccountName' => array( 'setDepositToAccountName', 'DepositToAccountRef FullName' ), 
			'TemplateID' => 	array( 'setTemplateApplicationID', 'TemplateRef ' . QUICKBOOKS_API_APPLICATIONID, 'setTemplateListID' ), 
			'TemplateName' => 	array( 'setTemplateName', 'TemplateRef FullName' ), 
			'TxnDate' => 		array( 'setTxnDate', 'TxnDate' ), 
			'RefNumber' => 		array( 'setRefNumber', 'RefNumber' ), 
			'PONumber' => 		array( 'setPONumber', 'PONumber' ), 
			'TermsID' => 		array( 'setTermsApplicationID', 'TermsRef ' . QUICKBOOKS_API_APPLICATIONID, 'setTermsListID' ), 
			'TermsName' => 		array( 'setTermsName', 'TermsRef FullName' ), 
			'SalesRepID' => 	array( 'setSalesRepApplicationID', 'SalesRepRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesRepListID' ), 
			'SalesRepName' => 	array( 'setSalesRepName', 'SalesRepRef FullName' ), 
			'FOB' => 			array( 'setFOB', 'FOB' ), 
			'ShipDate' => 		array( 'setShipDate', 'ShipDate' ), 
			'ShipMethodID' => 	array( 'setShipMethodApplicationID', 'ShipMethodRef ' . QUICKBOOKS_API_APPLICATIONID, 'setShipMethodListID' ), 
			'ShipMethodName' => array( 'setShipMethodName', 'ShipMethodRef FullName' ), 
			'ItemSalesTaxID' => array( 'setSalesTaxItemApplicationID', 'SalesTaxItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxItemListID' ), 
			'ItemSalesTaxName' => array( 'setSalesTaxItemName', 'ItemSalesTaxRef FullName' ), 
			'CustomerMsgID' => 	array( 'setCustomerMsgApplicationID', 'CustomerMsgRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerMsgListID' ), 
			'Memo' => 			array( 'setMemo', 'Memo' ), 
			'CustomerSalesTaxCodeID' => array( 'setCustomerSalesTaxCodeApplicationID', 'CustomerSalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setCustomerSalesTaxCodeListID' ), 
			'CustomerSalesTaxCodeName' => array( 'setCustomerSalesTaxCodeName', 'CustomerSalesTaxCodeRef FullName' ), 
			'Other' => 			array( 'setOther', 'Other' ), 
			);
		
		$this->_applyBaseMap($arr, $map, $Estimate, QUICKBOOKS_OBJECT_ESTIMATE);
		
		if (!empty($arr['TxnDate']))
		{
			$Estimate->setTxnDate($arr['TxnDate']);
		}
		else
		{
			$Estimate->setTxnDate(date('Y-m-d'));
		}
						
		// These things should be forced to a specific value if they havn't been explicitly set 
		$force_boolean_if_empty = array(
			// $key => 			array( $force_it_to, $function ), 
			);
		
		$this->_forceNullBooleans($arr, $force_boolean_if_empty, $Estimate);
		
		
		// Shipping and billing information
		$map2 = array( 
			'ShipAddress' => 'setShipAddress', 
			'BillAddress' => 'setBillAddress', 
			);
		
		$this->_applyAddressMap($arr, $map2, $Estimate, QUICKBOOKS_OBJECT_ESTIMATE);
		
		foreach ($items as $item)
		{
			if (is_object($item))
			{
				$Estimate->addEstimateLine($item);
			}
		}
		
		if (is_object($shipping))
		{
			$Estimate->addEstimateLine($shipping);
		}
		
		if (is_object($handling))
		{
			$Estimate->addEstimateLine($handling);
		}
		
		if (is_object($discount))
		{
			$Estimate->addEstimateLine($discount);
		}
		
		return $Estimate;
	}
	
	/**
	 * Create an order item (invoice line item, etc.) from an array
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object_*
	 */
	protected function _estimateItemFromArray($arr)
	{
		$EstimateLine = new QuickBooks_Object_Estimate_EstimateLine();
		
		$map = array(
			'ProductID' => 			array( 'setItemApplicationID', 'ItemRef ' . QUICKBOOKS_API_APPLICATIONID, 'setItemListID' ),
			'ProductName' => 		array( 'setItemName', 'ItemRef FullName' ),  
			'Desc' => 				array( 'setDescription', 'Desc' ),
			'Descrip' => 			array( 'setDescription', 'Desc' ), 
			'Description' => 		array( 'setDescription', 'Desc' ),  
			'Quantity' => 			array( 'setQuantity', 'Quantity' ),  
			'Rate' => 				array( 'setRate', 'Rate' ),
			
			'ClassID' => 			array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ),
			'ClassName' => 			array( 'setClassName', 'ClassRef FullName' ), 
			'ClassListID' => 		array( 'setClassListID', 'ClassRef ListID' ), 
			
			'SalesTaxCodeID' => 	array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
			'SalesTaxCodeName' => 	array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ), 
			'SalesTaxCodeListID' => array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ), 
			);
		
		$EstimateLine = $this->_applyBaseMap($arr, $map, $EstimateLine, 'Estimate EstimateLine');
		
		return $EstimateLine;
	}
	
	protected function _merge($arr, $arr2)
	{
		foreach ($arr2 as $key => $value)
		{
			if ($value == QUICKBOOKS_INTEGRATOR_NULL)
			{
				continue;
			}
			
			$arr[$key] = $value;
		}
		
		return $arr;
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
	protected function _productFromArray($arr, $as = null, $apply_defaults = true)
	{
		if (!$as)
		{
			$as = $this->_config['push_products_as'];
		}
		
		if ($apply_defaults)
		{
			$arr = $this->_merge($this->_config['product_defaults'], $arr);
		}
		
		switch ($as)
		{
			case QUICKBOOKS_OBJECT_OTHERCHARGEITEM:
				
				$OtherChargeItem = new QuickBooks_Object_OtherChargeItem();
				
				
				
				return $OtherChargeItem;
				
				break;
			case QUICKBOOKS_OBJECT_INVENTORYITEM:
				
				$InventoryItem = new QuickBooks_Object_InventoryItem();
				
				$map = array(
					'Name' => 					array( 'setName', 'Name' ),
					'IsActive' => 				array( 'setIsActive', 'IsActive' ), 
					'ParentID' => 				array( 'setParentApplicationID', 'ParentRef ' . QUICKBOOKS_API_APPLICATIONID, 'setParentListID' ),
					'ParentListID' => 			array( 'setParentListID', 'ParentRef ListID' ),
					'ParentName' => 			array( 'setParentName', 'ParentRef FullName' ),
					'SalesTaxCodeID' => 		array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					'SalesTaxCodeListID' => 	array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ),
					'SalesTaxCodeName' =>		array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ),
					'IncomeAccountName' => 		array( 'setIncomeAccountName', 'IncomeAccountRef FullName' ),
					'COGSAccountName' => 		array( 'setCOGSAccountName', 'COGSAccountRef FullName' ),
					'AssetAccountName' => 		array( 'setAssetAccountName', 'AssetAccountRef FullName' ), 
					'SalesDesc' => 				array( 'setSalesDescription', 'SalesDesc' ),
					'SalesDescrip' => 			array( 'setSalesDescription', 'SalesDesc' ),
					'SalesDescription' => 		array( 'setSalesDescription', 'SalesDesc' ),
					'SalesPrice' => 			array( 'setSalesPrice', 'SalesPrice' ),
					'PurchaseDesc' => 			array( 'setPurchaseDescription', 'PurchaseDesc' ),
					'PurchaseDescrip' => 		array( 'setPurchaseDescription', 'PurchaseDesc' ),
					'PurchaseDescription' => 	array( 'setPurchaseDescription', 'PurchaseDesc' ),
					'PurchaseCost' => 			array( 'setPurchaseCost', 'PurchaseCost' ), 
					);  
				
				$this->_applyBaseMap($arr, $map, $InventoryItem, QUICKBOOKS_OBJECT_INVENTORYITEM);				
				
				//print_r($InventoryItem);
				
				return $InventoryItem;
				
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
	
	protected function _extraFromArray($arr)
	{
		if (!isset($arr['OwnerID']))
		{
			return null;
		}
		
		$DataExt = new QuickBooks_Object_DataExt();
				
		$map = array(
			'OwnerID' => 			array( 'setOwnerID', 'OwnerID' ), 
			'DataExtName' => 		array( 'setDataExtName', 'DataExtName' ), 
			'ListDataExtType' => 	array( 'setListDataExtType', 'ListDataExtType' ), 
			//'ListObjID' => 			array( 'setListObjApplicationID', 'ListObjRef ' . QUICKBOOKS_API_APPLICATIONID, 'setListObjListID' ), 
			'ListObjListID' => 		array( 'setListObjListID', 'ListObjRef ListID' ), 
			'ListObjName' => 		array( 'setListObjName', 'ListObjRef FullName' ), 
			'TxnDataExtType' => 	array( 'setTxnDataExtType', 'TxnDataExtType' ), 
			'TxnID' => 				array( 'setTxnID', 'TxnID' ), 
			'TxnLineID' => 			array( 'setTxnLineID', 'TxnLineID' ), 
			'OtherDataExtType' => 	array( 'setOtherDataExtType', 'OtherDataExtType' ), 
			'DataExtValue' => 		array( 'setDataExtValue', 'DataExtValue' ), 
			);
			
		$this->_applyBaseMap($arr, $map, $DataExt, QUICKBOOKS_OBJECT_DATAEXT);
		
		if (!empty($arr['ListObjID']) and 
			!empty($arr['ListDataExtType']))
		{
			$DataExt->setListObjApplicationID($arr['ListObjID'], $arr['ListDataExtType']);
		}
		
		if (strlen(trim($DataExt->getDataExtValue())) == 0)
		{
			return null;
		}
		
		//print_r($arr);
		//print_r($DataExt);
		
		return $DataExt;
	}
	
	protected function _handlingFromArray($arr)
	{
		if (count($arr) == 1 and 
			strlen(current($arr)) == 0)
		{
			return null;
		}
		
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:

				$SalesReceiptLine = new QuickBooks_Object_SalesReceipt_SalesReceiptLine();
				
				if (!empty($arr['Rate']))
				{
					$SalesReceiptLine->setRate($arr['Rate']);
					$SalesReceiptLine->setQuantity(1);
				}
				else if (!empty($arr['Amount']))
				{
					$SalesReceiptLine->setAmount((float) $arr['Amount']);
				}
				else
				{
					$SalesReceiptLine->setAmount(0);
				}
				
				$map = array(
					'Desc' => 				array( 'setDescription', 'Desc' ),
					'Descrip' => 			array( 'setDescription', 'Desc' ),
					'Description' => 		array( 'setDescription', 'Desc' ),
					'UnitOfMeasure' => 		array( 'setUnitOfMeasure', 'UnitOfMeasure' ), 
					
					'PriceLevelName' => 	array( 'setPriceLevelName', 'PriceLevelRef FullName' ), 
					'PriceLevelListID' => 	array( 'setPriceLevelListID', 'PriceLevelRef ListID' ), 
					'PriceLevelID' => 		array( 'setPriceLevelApplicationID', 'PriceLevelRef ' . QUICKBOOKS_API_APPLICATIONID, 'setPriceLevelID' ), 
					
					'ClassName' => 			array( 'setClassName', 'ClassRef FullName' ), 
					'ClassListID' => 		array( 'setClassListID', 'ClassRef ListID' ), 
					'ClassID' => 			array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					
					'ServiceDate' => 		array( 'setServiceDate', 'ServiceDate' ), 
					'Other1' => 			array( 'setOther1', 'Other1' ), 
					'Other2' => 			array( 'setOther2', 'Other2' ), 
					
					'SalesTaxCodeName' => 	array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName'), 
					'SalesTaxCodeListID' => array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID'), 
					'SalesTaxCodeID' => 	array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					);
				
				$SalesReceiptLine = $this->_applyBaseMap($arr, $map, $SalesReceiptLine, 'SalesReceipt SalesReceiptLine');	
				
				$SalesReceiptLine->setItemApplicationID(QUICKBOOKS_INTEGRATOR_HANDLING_ID);
				
				return $SalesReceiptLine;
				
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
	 * Create a QuickBooks discount (actually an item) object, from an array
	 * 
	 * This method can return items of the following type:
	 * 	- QuickBooks_Object_SalesReceipt_SalesReceiptLine
	 * 	- QuickBooks_Object_Invoice_InvoiceLine
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object
	 */
	protected function _discountFromArray($arr)
	{
		if (count($arr) == 1 and 
			strlen(current($arr)) == 0)
		{
			return null;
		}
		
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				$SalesReceiptLine = new QuickBooks_Object_SalesReceipt_SalesReceiptLine();
				
				if (!empty($arr['Rate']))
				{
					$SalesReceiptLine->setRate($arr['Rate']);
					$SalesReceiptLine->setQuantity(1);
				}
				else if (!empty($arr['Amount']))
				{
					$SalesReceiptLine->setAmount((float) $arr['Amount']);
				}
				else
				{
					$SalesReceiptLine->setAmount(0);
				}
				
				$map = array(
					'Desc' => 				array( 'setDescription', 'Desc' ),
					'Descrip' => 			array( 'setDescription', 'Desc' ),
					'Description' => 		array( 'setDescription', 'Desc' ),
					'UnitOfMeasure' => 		array( 'setUnitOfMeasure', 'UnitOfMeasure' ), 
					
					'PriceLevelName' => 	array( 'setPriceLevelName', 'PriceLevelRef FullName' ), 
					'PriceLevelListID' => 	array( 'setPriceLevelListID', 'PriceLevelRef ListID' ), 
					'PriceLevelID' => 		array( 'setPriceLevelApplicationID', 'PriceLevelRef ' . QUICKBOOKS_API_APPLICATIONID, 'setPriceLevelID' ), 
					
					'ClassName' => 			array( 'setClassName', 'ClassRef FullName' ), 
					'ClassListID' => 		array( 'setClassListID', 'ClassRef ListID' ), 
					'ClassID' => 			array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					
					'ServiceDate' => 		array( 'setServiceDate', 'ServiceDate' ), 
					'Other1' => 			array( 'setOther1', 'Other1' ), 
					'Other2' => 			array( 'setOther2', 'Other2' ), 
					
					'SalesTaxCodeName' => 	array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName'), 
					'SalesTaxCodeListID' => array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID'), 
					'SalesTaxCodeID' => 	array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					);
				
				$SalesReceiptLine = $this->_applyBaseMap($arr, $map, $SalesReceiptLine, 'SalesReceipt SalesReceiptLine');	
				
				$SalesReceiptLine->setItemApplicationID(QUICKBOOKS_INTEGRATOR_DISCOUNT_ID);
				
				return $SalesReceiptLine;
								
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
				
				$InvoiceLine->setItemApplicationID(QUICKBOOKS_INTEGRATOR_DISCOUNT_ID);
				
				return $InvoiceLine;
		}
	}

	/**
	 * Create a QuickBooks shipping (actually an item) object, from an array
	 * 
	 * This method can return items of the following type:
	 * 	- QuickBooks_Object_SalesReceipt_SalesReceiptLine
	 * 	- QuickBooks_Object_Invoice_InvoiceLine
	 * 
	 * @param array $arr
	 * @return QuickBooks_Object
	 */
	protected function _shippingFromArray($arr)
	{
		if (count($arr) == 1 and 
			strlen(current($arr)) == 0)
		{
			return null;
		}
		
		switch ($this->_config['push_orders_as'])
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				$SalesReceiptLine = new QuickBooks_Object_SalesReceipt_SalesReceiptLine();
				
				if (!empty($arr['Rate']))
				{
					$SalesReceiptLine->setRate($arr['Rate']);
					$SalesReceiptLine->setQuantity(1);
				}
				else if (!empty($arr['Amount']))
				{
					$SalesReceiptLine->setAmount((float) $arr['Amount']);
				}
				else
				{
					$SalesReceiptLine->setAmount(0);
				}
				
				$map = array(
					'Desc' => 				array( 'setDescription', 'Desc' ),
					'Descrip' => 			array( 'setDescription', 'Desc' ),
					'Description' => 		array( 'setDescription', 'Desc' ),
					'UnitOfMeasure' => 		array( 'setUnitOfMeasure', 'UnitOfMeasure' ), 
					
					'PriceLevelName' => 	array( 'setPriceLevelName', 'PriceLevelRef FullName' ), 
					'PriceLevelListID' => 	array( 'setPriceLevelListID', 'PriceLevelRef ListID' ), 
					'PriceLevelID' => 		array( 'setPriceLevelApplicationID', 'PriceLevelRef ' . QUICKBOOKS_API_APPLICATIONID, 'setPriceLevelID' ), 
					
					'ClassName' => 			array( 'setClassName', 'ClassRef FullName' ), 
					'ClassListID' => 		array( 'setClassListID', 'ClassRef ListID' ), 
					'ClassID' => 			array( 'setClassApplicationID', 'ClassRef ' . QUICKBOOKS_API_APPLICATIONID, 'setClassListID' ), 
					
					'ServiceDate' => 		array( 'setServiceDate', 'ServiceDate' ), 
					'Other1' => 			array( 'setOther1', 'Other1' ), 
					'Other2' => 			array( 'setOther2', 'Other2' ), 
					
					'SalesTaxCodeName' => 	array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName'), 
					'SalesTaxCodeListID' => array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID'), 
					'SalesTaxCodeID' => 	array( 'setSalesTaxCodeApplicationID', 'SalesTaxCodeRef ' . QUICKBOOKS_API_APPLICATIONID, 'setSalesTaxCodeListID' ), 
					);
				
				$SalesReceiptLine = $this->_applyBaseMap($arr, $map, $SalesReceiptLine, 'SalesReceipt SalesReceiptLine');	
				
				$SalesReceiptLine->setItemApplicationID(QUICKBOOKS_INTEGRATOR_SHIPPING_ID);
				
				return $SalesReceiptLine;
								
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
		
		// Apply default values 
		$arr = $this->_applyDefaults($arr, $this->_config['customer_defaults']);
		
		// 		Array Key => array( Method, QuickBooks Field for Cast ), 
		$map = array(
			'ID' => 				array( 'setApplicationID', QUICKBOOKS_API_APPLICATIONID ),
			'CustomerID' => 		array( 'setApplicationID', QUICKBOOKS_API_APPLICATIONID ), 
			'Name' => 				array( 'setName', 'Name' ), 
			'FirstName' => 			array( 'setFirstName', 'FirstName' ),
			'MiddleName' => 		array( 'setMiddleName', 'MiddleName' ),
			'LastName' => 			array( 'setLastName', 'LastName' ), 
			'CompanyName' => 		array( 'setCompanyName', 'CompanyName' ), 
			'Phone' => 				array( 'setPhone', 'Phone' ), 
			'AltPhone' => 			array( 'setAltPhone', 'AltPhone' ), 
			'Email' => 				array( 'setEmail', 'Email' ), 
			'Contact' => 			array( 'setContact', 'Contact' ), 
			
			'CustomerTypeName' => 		array( 'setCustomerTypeName', 'CustomerTypeRef FullName' ), 
			'CustomerTypeListID' => 	array( 'setCustomerTypeListID', 'CustomerTypeRef ListID' ), 
			'TermsName' => 				array( 'setTermsName', 'TermsRef FullName' ), 
			'TermsListID' => 			array( 'setTermsListID', 'TermsRef ListID' ), 
			'SalesRepName' => 			array( 'setSalesRepName', 'SalesRepRef FullName' ), 
			'SalesRepListID' => 		array( 'setSalesRepListID', 'SalesRepRef ListID' ), 
			'PriceLevelName' => 		array( 'setPriceLevelName', 'PriceLevelRef FullName' ), 
			'PriceLevelListID' => 		array( 'setPriceLevelListID', 'PriceLevelRef ListID' ), 
			'SalesTaxCodeName' => 		array( 'setSalesTaxCodeName', 'SalesTaxCodeRef FullName' ), 
			'SalesTaxCodeListID' => 	array( 'setSalesTaxCodeListID', 'SalesTaxCodeRef ListID' ), 
			);
		
		//print_r($this->_config['customer_defaults']);
		//print_r($arr);
		//exit;
		
		$this->_applyBaseMap($arr, $map, $Customer, QUICKBOOKS_OBJECT_CUSTOMER);
		
		$map2 = array( 
			'ShipAddress' => 'setShipAddress', 
			'BillAddress' => 'setBillAddress', 
			);
		
		$this->_applyAddressMap($arr, $map2, $Customer, QUICKBOOKS_OBJECT_CUSTOMER);
		
		if (isset($arr['CreditCardInfo_CreditCardNumber']))
		{
			$map3 = array(
				'CreditCardInfo' => 'setCreditCardInfo', 
				);
				
			$this->_applyCreditCardInfoMap($arr, $map3, $Customer, QUICKBOOKS_OBJECT_CUSTOMER);
		}
		
		/*
		print('<pre>');
		print_r($Customer);
		print('</pre>');	
		*/
			
		return $Customer;
	}

}
