<?php

/**
 * QuickBooks Integrator base class - integrate common applications with QuickBooks
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Server
 */

/*
if (!defined('QUICKBOOKS_SERVER_INTEGRATOR_OFFSET'))
{
	define('QUICKBOOKS_SERVER_INTEGRATOR_OFFSET', 60 * 60 * 24);
}
*/

// 
if (!defined('QUICKBOOKS_SERVER_INTEGRATOR_RECUR'))
{
	/**
	 * How often items should re-occur in the queue
	 * 
	 * @var integer
	 */
	define('QUICKBOOKS_SERVER_INTEGRATOR_RECUR', 120);
}

/**
 * 
 */
define('QUICKBOOKS_SERVER_INTEGRATOR_HOOK_INTEGRATEORDER', 'QuickBooks_Server_Integrator::integrateOrder');

/**
 * 
 */
define('QUICKBOOKS_SERVER_INTEGRATOR_HOOK_INTEGRATECUSTOMER', 'QuickBooks_Server_Integrator::integrateCustomer');

/**
 * 
 */
define('QUICKBOOKS_SERVER_INTEGRATOR_HOOK_INTEGRATEPRODUCT', 'QuickBooks_Server_Integrator::integrateProduct');

/** 
 * QuickBooks base framework
 */
require_once 'QuickBooks.php';

/** 
 * QuickBooks server base class
 */
require_once 'QuickBooks/Server.php';

/**
 * API Server (OOP interface to qbXML)
 */
require_once 'QuickBooks/Server/API.php';

/**
 * QuickBooks API class (OOP interface to qbXML)
 */
require_once 'QuickBooks/API.php';

/**
 * QuickBooks integrator base class
 */
require_once 'QuickBooks/Integrator.php';

/**
 * Integrator singleton
 */
require_once 'QuickBooks/Integrator/Singleton.php';

/**
 * Integrator server callbacks
 */
require_once 'QuickBooks/Server/Integrator/Callbacks.php';

/**
 * QuickBooks integrator base class
 */
abstract class QuickBooks_Server_Integrator extends QuickBooks_Server_API
{
	/**
	 * 
	 * 
	 * 
	 */
	public function __construct(
		$dsn_or_conn, 
		$integrator_dsn_or_conn, 
		$email, 
		$user, 
		$map = array(), 
		$onerror = array(), 
		$hooks = array(), 
		$log_level = QUICKBOOKS_LOG_NORMAL, 
		$soap = QUICKBOOKS_SOAPSERVER_BUILTIN, 
		$wsdl = QUICKBOOKS_WSDL, 
		$soap_options = array(), 
		$handler_options = array(), 
		$driver_options = array(), 
		$api_options = array(), 
		$source_options = array(), 
		$integrator_options = array(), 
		$callback_options = array())
	{
		$integrator_onerror = array(
			'3100' => 'QuickBooks_Server_Integrator_Errors::e3100_alreadyexists',
			'3170' => 'QuickBooks_Server_Integrator_Errors::e3170_errorsaving', 
			'3180' => 'QuickBooks_Server_Integrator_Errors::e3180_errorsaving',
			'3200' => 'QuickBooks_Server_Integrator_Errors::e3200_editsequence', 
			'*' => 'QuickBooks_Server_Integrator_Errors::e_catchall', 
			);
		
		// Merge integration options over default options
		//	(allow overrides, they'll need to handle errors manually...)
		$integrator_onerror = $this->_merge($integrator_onerror, $onerror, false);
		
		// Merge hooks in
		$integrator_hooks = array(
			);
		
		$integrator_hooks = $this->_merge($integrator_hooks, $hooks, false);
		
		// Callback options
		$integrator_callback_options = array(
			'_error_email' => $email, 
			'_error_subject' => 'QuickBooks Error on ' . $_SERVER['HTTP_HOST'], 
			'_error_from' => 'quickbooks@' . implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2)), 
			);
		
		//print_r($integrator_callback_options);
		//exit;
		
		// Merge callback options 
		$integrator_callback_options = $this->_merge($integrator_callback_options, $callback_options, false);
		
		// QuickBooks_Server_API::__construct( ... )
		parent::__construct(
			$dsn_or_conn, 
			$user, 
			$map, 
			$integrator_onerror,
			$integrator_hooks, 
			$log_level, 
			$soap, 
			$wsdl, 
			$soap_options, 
			$handler_options, 
			$driver_options,
			$api_options, 
			$source_options, 
			$callback_options);
		
		//header('Content-Type: text/plain');
		//print_r($this);
		//exit;
		
		$source_type = QUICKBOOKS_API_SOURCE_WEB;
		$source_dsn = null;
		
		// Set up the API 
		// $api_driver_dsn, $user, $source_type, $source_dsn, $api_options = array(), $source_options = array(), $driver_options = array(), $callback_options = array()
		$API = QuickBooks_API_Singleton::getInstance($dsn_or_conn, $user, $source_type, $source_dsn, $api_options, $source_options, $driver_options, $callback_options);
		$this->_api = $API; // new QuickBooks_API($dsn_or_conn, $user, $source_type, $source_dsn, $api_options, $source_options, $driver_options, $callback_options);
		
		// Set up the integrator
		$this->_integrator = $this->_integratorFactory($integrator_dsn_or_conn, $integrator_options, $API);
		
		// Initialize the Integrator singleton
		$tmp = QuickBooks_Integrator_Singleton::getInstance($this->_integrator);
		
		// Integrator options (shared between the server component and actual integrator)
		$this->_integrator_config = $this->_integratorDefaults($integrator_options);
	}
	
	/**
	 * Merge default configuration options
	 * 
	 * @param array $config
	 * @return array
	 */
	protected function _integratorDefaults($config)
	{
		$defaults = array(
			'debug_datetime' => null, 
		
			'push_orders' => true,
			'push_payments' => true, 
			'push_customers' => true, 
			'push_products' => true, 
			'push_accounts' => true, 
			'push_shipmethods' => true, 
			'push_paymentmethods' => true, 
			
			'push_shipping' => true, 
			'push_handling' => true, 
			'push_discounts' => true, 
			'push_coupons' => true,  
			
			'pull_estimates' => false, 
			'pull_orders' => false, 
			'pull_payments' => false, 
			'pull_customers' => false, 
			'pull_products' => false,  
			'pull_shipmethods' => false, 
			'pull_paymentmethods' => false, 
			
			'lookup_orders' => false, 
			'lookup_customers' => true, 
			'lookup_products' => true, 
			'lookup_accounts' => true, 
			'lookup_shipmethods' => true, 
			'lookup_paymentmethods' => true, 
			
			/*
			'use_generic_coupons' => true, 
			'use_generic_discounts' => true, 
			'use_generic_shipping' => true, 
			*/

			'use_generic_coupons' => false, 
			'use_generic_discounts' => false, 
			'use_generic_shipping' => false, 
			
			//'order_format' => '$RefNumber ($ApplicationID)', 
			//'customer_format' => '$Name ($ApplicationID)', 
			//'item_format' => '$Name ($ApplicationID)', 
			);
			
		return array_merge($defaults, $config);
	}
	
	/**
	 * 
	 * 
	 * @param
	 * @param 
	 * @return boolean
	 */
	public function handle($return = false, $debug = false)
	{
		$this->_headers();
		
		// This is the *very first time the integration was run!* 
		//	Orders *will not* be fetched from before this time! 
		$type = null;
		$opts = null;
		$first_datetime = $this->_api->configRead(get_class($this), 'initial', $type, $opts);
		
		$first = false;
		if (!$first_datetime)
		{
			$first_datetime = date('Y-m-d H:i:s');
			$this->_api->configWrite(get_class($this), 'initial', $first_datetime);
			
			$first = true;
		}
		
		// The is the last time the integration was run!
		$type = null;
		$opts = null;
		$last_datetime = $this->_api->configRead(get_class($this), 'datetime', $type, $opts);
		
		if (!$last_datetime)
		{
			$last_datetime = date('Y-m-d H:i:s');
		}
		
		if (!empty($this->_integrator_config['debug_datetime']))
		{
			$last_datetime = date('Y-m-d H:i:s', strtotime($this->_integrator_config['debug_datetime']));
			$first_datetime = date('Y-m-d H:i:s', strtotime($this->_integrator_config['debug_datetime']));
		}
		
		$force = false;
		/*
		$force = false;
		if (isset($_GET['OrderID']))
		{
			$force = true;
		}
		*/
		
		$this_datetime = date('Y-m-d H:i:s');
		if (strtotime($this_datetime) - strtotime($last_datetime) > QUICKBOOKS_SERVER_INTEGRATOR_RECUR or 
			$first or 
			$force)
		{
			$this->_api->log('Last integration timestamp: ' . $last_datetime . ', current timestamp: ' . $this_datetime, QUICKBOOKS_LOG_VERBOSE);
			
			// Do some integration routines
			$this->_integrate($last_datetime, $first_datetime, $first);
			
			$this->_api->configWrite(get_class($this), 'datetime', $this_datetime);
		}
		else
		{
			$this->_api->log('Integration was not due yet (only ' . (strtotime($this_datetime) - strtotime($last_datetime)) . ' seconds since last run)', QUICKBOOKS_LOG_DEVELOP);
		}
		
		// Call the parent handler
		return parent::handle($return, $debug);
	}
	
	/**
	 * 
	 * 
	 * @param string $integrator_dsn_or_conn
	 * @param array $integrator_options
	 */
	abstract protected function _integratorFactory($integrator_dsn_or_conn, $integrator_options, $API);
	
	/**
	 * 
	 * 
	 * @param string $last_datetime
	 * @param boolean $first_time_running
	 * @return boolean
	 */
	protected function _integrate($last_datetime, $first_datetime, $first_time_running)
	{
		// Generics which *must* be present
		if ($Shipping = $this->_integrator->getGenericShipping())
		{ 
			$this->_integrateShipping($Shipping);
		}
		
		/*
		if ($Handling = $this->_integrator->getGenericHandling())
		{
			$this->_integrateHandling($Handling);
		}
		*/
		
		if ($Discount = $this->_integrator->getGenericDiscount())
		{
			$this->_integrateDiscounts($Discount);
		}
		
		/*
		if ($Coupon = $this->_integrator->getGenericCoupon())
		{
			$this->_integrateCoupons($Coupon);
		}
		*/
		
		$customers = $this->_integrator->listNewCustomersSince($last_datetime, $first_datetime, $first_time_running);
		$this->_integrateNewCustomers($customers);
		
		// 
		$orders = $this->_integrator->listNewOrdersSince($last_datetime, $first_datetime, $first_time_running);
		$this->_integrateNewOrders($orders);
		
		// 
		$estimates = $this->_integrator->listNewEstimatesSince($last_datetime, $first_datetime, $first_time_running);
		$this->_integrateNewEstimates($estimates);
		
		// 
		$this->_pullNewAccounts($last_datetime, $first_datetime, $first_time_running);
		
		// 
		$this->_pullNewPaymentMethods($last_datetime, $first_datetime, $first_time_running);
		
		// 
		$this->_pullNewCustomerTypes($last_datetime, $first_datetime, $first_time_running);
		
		// 
		$this->_pullNewShipMethods($last_datetime, $first_datetime, $first_time_running);
		
		// 
		$this->_pullNewOrders($last_datetime, $first_datetime, $first_time_running);
		
		// 
		$this->_pullNewEstimates($last_datetime, $first_datetime, $first_time_running);
	}
	
	/**
	 * 
	 * 
	 */
	protected function _pullNewAccounts($datetime, $first_datetime, $first_time_running)
	{
		if ($first_time_running)
		{
			// A realllly long time ago
			$datetime = '1983-01-02 00:00:01';
		}
			
		$this->_api->log('Pulling in accounts modified since ' . $datetime, QUICKBOOKS_LOG_VERBOSE);
		
		return $this->_api->listAccountsModifiedAfter($datetime, 'QuickBooks_Server_Integrator_Callbacks::listAccountsModifiedAfter');
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _pullNewPaymentMethods($datetime, $first_datetime, $first_time_running)
	{
		if ($first_time_running)
		{
			// A realllly long time ago
			$datetime = '1983-01-02 00:00:01';
		}
			
		$this->_api->log('Pulling in payment methods modified since ' . $datetime, QUICKBOOKS_LOG_VERBOSE);
		
		return $this->_api->listPaymentMethodsModifiedAfter($datetime, 'QuickBooks_Server_Integrator_Callbacks::listPaymentMethodsModifiedAfter');
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _pullNewShipMethods($datetime, $first_datetime, $first_time_running)
	{
		if ($first_time_running)
		{
			// A realllly long time ago
			$datetime = '1983-01-02 00:00:01';
		}
			
		$this->_api->log('Pulling in ship methods modified since ' . $datetime, QUICKBOOKS_LOG_VERBOSE);
		
		return $this->_api->listShipMethodsModifiedAfter($datetime, 'QuickBooks_Server_Integrator_Callbacks::listShipMethodsModifiedAfter');
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _pullNewCustomerTypes($datetime, $first_datetime, $first_time_running)
	{
		if ($first_time_running)
		{
			// A realllly long time ago
			$datetime = '1983-01-02 00:00:01';
		}
			
		$this->_api->log('Pulling in customer types modified since ' . $datetime, QUICKBOOKS_LOG_VERBOSE);
		
		return $this->_api->listCustomerTypesModifiedAfter($datetime, 'QuickBooks_Server_Integrator_Callbacks::listCustomerTypesModifiedAfter');
	}

	
	/**
	 * 
	 * 
	 * @param string $datetime
	 * @return boolean
	 */
	protected function _pullNewEstimates($datetime, $first_datetime, $first_time_running)
	{
		if ($this->_integrator_config['pull_estimates'])
		{
			// Use the lookback value... 
			$max = max(strtotime($datetime) - QUICKBOOKS_INTEGRATOR_LOOKBACK, strtotime($first_datetime));
			$datetime = date('Y-m-d H:i:s', $max);
			
			$this->_api->log('Pulling in estimates modified since ' . $datetime, QUICKBOOKS_LOG_VERBOSE);
			
			return $this->_api->listEstimatesModifiedAfter($datetime, 'QuickBooks_Server_Integrator_Callbacks::listEstimatesModifiedAfter');
		}
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * @param string $datetime
	 * @param string $first_datetime
	 * @param boolean $first_time_running
	 */
	protected function _pullNewOrders($datetime, $first_datetime, $first_time_running)
	{
		if ($this->_integrator_config['pull_orders'])
		{
			// Use the lookback value... 
			$max = max(strtotime($datetime) - QUICKBOOKS_INTEGRATOR_LOOKBACK, strtotime($first_datetime));
			$datetime = date('Y-m-d H:i:s', $max);
			
			$this->_api->log('Pulling in invoices modified since ' . $datetime, QUICKBOOKS_LOG_VERBOSE);
			
			return $this->_api->listInvoicesModifiedAfter($datetime, 'QuickBooks_Server_Integrator_Callbacks::listInvoicesModifiedAfter');			
		}
		
		return true;
	}
	
	protected function _integrateNewCustomers($customers)
	{
		foreach ($customers as $CustomerID)
		{
			//$CustomerID = $Estimate->getCustomerApplicationID();
			/*if ($ListID = $Estimate->getCustomerListID())
			{
				// xxx Do nothing, already in QuickBooks
				// Add it again, just in case!
				
				QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
				
				//$this->_api->getCustomerByName($this->_integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID);
			}
			else*/ if ($this->_integrator_config['lookup_customers'])
			{
				// Try to fetch the customer by name
				$this->_api->getCustomerByName($this->_integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID);
				
				// TEMP TEMP TEMP
				//QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//QuickBooks_Server_Integrator_Callbacks::integrateModCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
			}
			else
			{
				// Add the customer to QuickBooks
				
				QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
			}
		}
	}
	
	/**
	 * 
	 * 
	 * @param array $estimates
	 * @return boolean
	 */
	protected function _integrateNewEstimates($estimates)
	{
		// Let's start with new estimates
		foreach ($estimates as $EstimateID)
		{
			$this->_api->log('Analyzing estimate #' . $EstimateID, QUICKBOOKS_LOG_VERBOSE);
			
			$Estimate = $this->_integrator->getEstimate($EstimateID);
			$this->_integrateEstimate($Estimate, $EstimateID);
			
			// Customer
			$CustomerID = $Estimate->getCustomerApplicationID();
			if ($ListID = $Estimate->getCustomerListID())
			{
				// xxx Do nothing, already in QuickBooks
				// Add it again, just in case!
				
				QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
				
				//$this->_api->getCustomerByName($this->_integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID);
			}
			else if ($this->_integrator_config['lookup_customers'])
			{
				// Try to fetch the customer by name
				$this->_api->getCustomerByName($this->_integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID);
				
				// TEMP TEMP TEMP
				//QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//QuickBooks_Server_Integrator_Callbacks::integrateModCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
			}
			else
			{
				// Add the customer to QuickBooks
				
				QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
			}
			
			$list = $this->_integrator->getEstimateItemsForEstimate($EstimateID);
			foreach ($list as $EstimateItem)
			{
				//print_r($OrderItem);
				//print("\n");
				//exit;
				
				$ProductID = $EstimateItem->getItemApplicationID();
				
				if (!$EstimateID)
				{
					continue;
				}
				
				//print('product id is: ' . $ProductID . "\n");
				///
				
				//header('Content-Type: text/plain');
				//$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
				//$this->_integrateProduct($Product, $ProductID);
				
				//print_r($Product);
				//exit;
				/// 
				
				if ($ListID = $EstimateItem->getItemListID())
				{
					//print('push');
					// XXX Do nothing, already in QuickBooks
					// Add it again anyway, just in case
					$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
					$this->_integrateProduct($Product, $ProductID);
				}
				else if ($this->_integrator_config['lookup_products'])
				{
					//print('getbyname');
					// Queue a request *for each type* of item
					$this->_api->getItemByName($this->_integrator->getProductNameForQuery($ProductID), 'QuickBooks_Server_Integrator_Callbacks::getProductByName', $ProductID);
				}
				else
				{
					//print('else');
					$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
					$this->_integrateProduct($Product, $ProductID);
				}
				
				//print('here');
			}
		}		
	}
	
	protected function _integrateNewOrders($orders)
	{
		// Let's start with new orders
		foreach ($orders as $OrderID)
		{
			$this->_api->log('Analyzing order #' . $OrderID, QUICKBOOKS_LOG_VERBOSE);
			
			$Order = $this->_integrator->getOrder($OrderID);
			$this->_integrateOrder($Order, $OrderID);
			
			$Payment = $this->_integrator->getPayment($OrderID);
			if ($Payment and $Payment->getTotalAmount() > 0)
			{
				$this->_integratePayment($Payment, $OrderID);
			}
			
			// Customer
			$CustomerID = $Order->getCustomerApplicationID();
			if ($ListID = $Order->getCustomerListID())
			{
				// xxx Do nothing, already in QuickBooks
				// Add it again, just in case!
				
				QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
				
				//$this->_api->getCustomerByName($this->_integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID);
			}
			else if ($this->_integrator_config['lookup_customers'])
			{
				// Try to fetch the customer by name
				$this->_api->getCustomerByName($this->_integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID);
				
				// TEMP TEMP TEMP
				//QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//QuickBooks_Server_Integrator_Callbacks::integrateModCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
			}
			else
			{
				// Add the customer to QuickBooks
				
				QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($CustomerID);
				//$extras = $this->_integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
				//$Customer = $this->_integrator->getCustomer($CustomerID, __FILE__, __LINE__);
				//$this->_integrateCustomer($Customer, $CustomerID, $extras);
			}
			
			// ShipMethod
			$ShipMethodID = $Order->getShipMethodApplicationID();
			if (
				(is_numeric($ShipMethodID) and (int) $ShipMethodID) or
				(!is_numeric($ShipMethodID) and strlen($ShipMethodID)))
			{
				if ($ListID = $Order->getShipMethodListID())
				{
					// xxx Do nothing, already in QuickBooks
					// Add it again anyway, just in case!
					$ShipMethod = $this->_integrator->getShipMethod($ShipMethodID);
					$this->_integrateShipMethod($ShipMethod, $ShipMethodID);
				}
				else if ($this->_integrator_config['lookup_shipmethods'] and $ShipMethodID)
				{
					// Try to fetch the shipping method by name
					$this->_api->getShipMethodByName($this->_integrator->getShipMethodNameForQuery($ShipMethodID), 'QuickBooks_Server_Integrator_Callbacks::getShipMethodByName', $ShipMethodID);
				}
				else if ($ShipMethodID)
				{
					$ShipMethod = $this->_integrator->getShipMethod($ShipMethodID);
					$this->_integrateShipMethod($ShipMethod, $ShipMethodID);
				}
			}
			
			// Account
			switch ($Order->object())
			{
				case QUICKBOOKS_OBJECT_INVOICE:
					
					$AccountID = $Order->getARAccountApplicationID();
					if ($AccountID)
					{
						if ($ListID = $Order->getARAccountListID())
						{
							// XXX Do nothing, already in QuickBooks
							// Add it again anyway, just in case
							$Account = $this->_integrator->getAccount($AccountID);
							$this->_integrateAccount($Account, $AccountID);
						}
						else if ($this->_integrator_config['lookup_accounts'] and $AccountID)
						{
							
						}
						else if ($AccountID)
						{
							$Account = $this->_integrator->getAccount($AccountID);
							$this->_integrateAccount($Account, $AccountID);
						}
					}
					
					break;
				case QUICKBOOKS_OBJECT_SALESRECEIPT:
					
					break;
			}
			
			
			$list = $this->_integrator->getOrderItems($OrderID);
			foreach ($list as $OrderItem)
			{
				//print_r($OrderItem);
				//print("\n");
				//exit;
				
				$ProductID = $OrderItem->getItemApplicationID();
				
				if (!$ProductID)
				{
					continue;
				}
				
				//print('product id is: ' . $ProductID . "\n");
				///
				
				//header('Content-Type: text/plain');
				//$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
				//$this->_integrateProduct($Product, $ProductID);
				
				//print_r($Product);
				//exit;
				/// 
				
				if ($ListID = $OrderItem->getItemListID())
				{
					//print('push');
					// XXX Do nothing, already in QuickBooks
					// Add it again anyway, just in case
					$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
					$this->_integrateProduct($Product, $ProductID);
				}
				else if ($this->_integrator_config['lookup_products'])
				{
					// Queue a request *for each type* of item
					
					// These next two lines are just for testing
					//$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
					//$this->_integrateProduct($Product, $ProductID);
					
					// Try to fetch the product from QuickBooks
					$this->_api->getItemByName($this->_integrator->getProductNameForQuery($ProductID), 'QuickBooks_Server_Integrator_Callbacks::getProductByName', $ProductID);
				}
				else
				{
					//print('else');
					$Product = $this->_integrator->getProduct($ProductID, __FILE__, __LINE__);
					$this->_integrateProduct($Product, $ProductID);
				}
				
				//print('here');
			}
		}		
	}
	
	/** 
	 * 
	 * 
	 * @param QuickBooks_Object_Customer
	 * @return boolean
	 */
	/*
	protected function _integrateCustomer($Customer, $CustomerID, $extras = array())
	{
		// Handle the customer for the order
		if ($this->_api->addCustomer($Customer, 'QuickBooks_Server_Integrator_Callbacks::addCustomer', $CustomerID))
		{
			foreach ($extras as $Extra)
			{
				$this->_api->addDataExt($Extra, 'QuickBooks_Server_Integrator_Callbacks::addExtra');
			}
		}
		
		return false;
	}
	*/
	
	/**
	 * @deprecated 
	 */
	protected function _integrateHandling($Handling)
	{
		if (!$this->_integrator_config['push_handling'])
		{
			return true;
		}
		
		return $this->_integrateProduct($Handling, QUICKBOOKS_INTEGRATOR_HANDLING_ID);
	}
	
	/**
	 * 
	 */
	protected function _integrateShipping($Shipping)
	{
		if (!$this->_integrator_config['push_shipping'])
		{
			return true;
		}
		
		//if (!$this->_api->fetchQuickBooksID($Shipping->object(), QUICKBOOKS_INTEGRATOR_SHIPPING_ID))
		//{
			return $this->_integrateProduct($Shipping, QUICKBOOKS_INTEGRATOR_SHIPPING_ID);
		//}
		
		return true;
	}
	
	/**
	 * 
	 */
	protected function _integrateCoupons($Coupon)
	{
		if (!$this->_integrator_config['push_coupons'])
		{
			return true;
		}
		
		//if (!$this->_api->fetchQuickBooksID($Coupon->object(), QUICKBOOKS_INTEGRATOR_COUPON_ID))
		//{
			return $this->_integrateProduct($Coupon, QUICKBOOKS_INTEGRATOR_COUPON_ID);
		//}
		
		return true;
	}
	
	/**
	 * 
	 */
	protected function _integrateDiscounts($Discount)
	{
		if (!$this->_integrator_config['push_discounts'])
		{
			return true;
		}
		
		//if (!$this->_api->fetchQuickBooksID($Discount->object(), QUICKBOOKS_INTEGRATOR_DISCOUNT_ID))
		//{
			return $this->_integrateProduct($Discount, QUICKBOOKS_INTEGRATOR_DISCOUNT_ID);
		//}
		
		return true;
	}
	
	/** 
	 * 
	 * 
	 * 
	 */
	protected function _integrateOrder($Order, $OrderID)
	{
		$this->_api->log('Integrating order #' . $OrderID . ' as a ' . $Order->object(), QUICKBOOKS_LOG_DEVELOP);
		$user = $this->_api->user();
		
		// Call a hook to indicate the order is being pushed to QuickBooks
		$hook_data = array(
			'OrderID' => $OrderID, 
			'Order' => $Order, 
			);
		$this->_callHooks(
			QUICKBOOKS_SERVER_INTEGRATOR_HOOK_INTEGRATEORDER, 
			null, 
			$user, 
			null, 
			$err, 
			$hook_data);
		
		// Send the object to QuickBooks
		switch ($Order->object())
		{
			case QUICKBOOKS_OBJECT_SALESRECEIPT:
				
				return $this->_api->addSalesReceipt($Order, 'QuickBooks_Server_Integrator_Callbacks::addSalesReceipt', $OrderID);
			case QUICKBOOKS_OBJECT_SALESORDER:
				
				return $this->_api->addSalesOrder($Order, 'QuickBooks_Server_Integrator_Callbacks::addSalesOrder', $OrderID);
			case QUICKBOOKS_OBJECT_INVOICE:
				
				return $this->_api->addInvoice($Order, 'QuickBooks_Server_Integrator_Callbacks::addInvoice', $OrderID);
			default:
				return false;
		}
	}

	protected function _integrateEstimate($Estimate, $EstimateID)
	{
		$this->_api->log('Integrating estimate #' . $EstimateID . ' as a ' . $Estimate->object(), QUICKBOOKS_LOG_DEVELOP);
		
		return $this->_api->addEstimate($Estimate, 'QuickBooks_Server_Integrator_Callbacks::addEstimate', $EstimateID);
	}
	
	/**
	 * 
	 * 
	 * @param QuickBooks_Object_ReceivePayment $Payment
	 * @param mixed $OrderID
	 * @return boolean
	 */
	protected function _integratePayment($Payment, $OrderID)
	{
		if ($this->_integrator_config['push_payments'])
		{
			return $this->_api->addReceivePayment($Payment, 'QuickBooks_Server_Integrator_Callbacks::addReceivePayment', $OrderID);
		}
		
		return true;
	}
	
	/**
	 * 
	 * 
	 * @param QuickBooks_Object $Product
	 * @param mixed $ProductID
	 * @return boolean
	 */
	protected function _integrateProduct($Product, $ProductID)
	{
		// Call a hook to indicate the order is being pushed to QuickBooks
		$hook_data = array(
			'ProductID' => $ProductID, 
			'Product' => $Product, 
			);
		$this->_callHooks(
			QUICKBOOKS_SERVER_INTEGRATOR_HOOK_INTEGRATEPRODUCT, 
			null, 
			$user, 
			null, 
			$err, 
			$hook_data);
		
		switch ($Product->object())
		{
			case QUICKBOOKS_OBJECT_INVENTORYITEM:
				
				return $this->_api->addInventoryItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addInventoryItem', $ProductID);
			case QUICKBOOKS_OBJECT_NONINVENTORYITEM:
				
				return $this->_api->addNonInventoryItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addNonInventoryItem', $ProductID);
			case QUICKBOOKS_OBJECT_SERVICEITEM:
				
				return $this->_api->addServiceItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addServiceItem', $ProductID);
			case QUICKBOOKS_OBJECT_DISCOUNTITEM:
				
				return $this->_api->addDiscountItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addDiscountItem', $ProductID);
			case QUICKBOOKS_OBJECT_OTHERCHARGEITEM:
					
				return $this->_api->addOtherChargeItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addOtherChargeItem', $ProductID);
			default:
				return false;
		}
	}
	
	protected function _integrateSalesReceipt()
	{
		
	}
	
	protected function _integrateSalesOrder()
	{
		
	}
	
	protected function _integrateInventoryItem()
	{
		
	}
	
	protected function _integrateNonInventoryItem()
	{
		
	}
	
	protected function _integrateServiceItem()
	{
		
	}
	
	protected function _integrateDiscountItem()
	{
		
	}
	
	protected function _integrateSalesTaxItem()
	{
		
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _format($format, $config)
	{
		
	}
}
