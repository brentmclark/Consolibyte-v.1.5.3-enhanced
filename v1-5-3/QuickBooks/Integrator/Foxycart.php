<?php

/**
 * QuickBooks FoxyCart Integrator
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Integrator
 */

/** 
 * QuickBooks constants
 */
require_once 'QuickBooks.php';

/** 
 * QuickBooks Integrator base class
 */
require_once 'QuickBooks/Integrator.php';

/**
 *
 * 
 */
class QuickBooks_Integrator_FoxyCart extends QuickBooks_Integrator
{		
	protected function _wrapAndEscape($str)
	{
		return $str;
	}
	
	/** 
	 * Configuration defaults for Interspire
	 * 
	 * @param array $config
	 * @return array
	 */
	protected function _defaults($config)
	{
		$defaults = parent::_defaults($config);
		
		$config = array_merge($defaults, $config);
		
		foreach ($defaults as $key => $value)
		{
			if (is_array($value) and !is_array($config[$key]))
			{
				$config[$key] = array( $config[$key] ); 
			}
		}
		
		return $config;
	}
		
	/**
	 * 
	 * 
	 * @return string
	 */
	public function _defaultGetCustomerQuery()
	{
		return '';
	}

	/**
	 * Get a list of orders that are new since a specific date 
	 * 
	 * @return string
	 */
	protected function _defaultListNewOrdersSinceQuery()
	{
		return '';
	}
		
	/**
	 * 
	 * 
	 * 
	 * 
	 */
	protected function _getCustomerExtras($CustomerID, $from = null, $line = null)
	{
		return array();
	}
	
	protected function _setCustomer($CustomerID, $Customer)
	{
		return false;
	}
	
	protected function _setEstimate($EstimateID, $Estimate, $from = null, $line = null)
	{
		return false;
	}
	
	public function query($sql, $errnum, $errmsg)
	{
		return $this->_driver->query($sql, $errnum, $errmsg);
	}
	
	public function escape($str)
	{
		return $this->_driver->escape($str);
	}
	
	public function fetch($res)
	{
		return $this->_driver->fetch($res);
	}
	
	public function insert($table, $record, $resync = true)
	{
		return $this->_driver->insert($table, $record, $resync);
	}
	
	public function update($table, $record, $where, $resync = true)
	{
		return $this->_driver->update($table, $record, $where, $resync);
	}
	
	public function get($table, $where)
	{
		return $this->_driver->get($table, $where);
	}
	
	public function last()
	{
		return $this->_driver->last();
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _foxycartConfigRead($key)
	{
		$Driver = $this->_driver;
		
		$errnum = 0;
		$errmsg = '';
		$res = $Driver->query("
			SELECT 
				*
			FROM 
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_USER . " 
			WHERE 
				foxycart_user_name = '" . $Driver->escape($this->_api->user()) . "' ", $errnum, $errmsg);
		if ($arr = $Driver->fetch($res) and 
			isset($arr[$key]))
		{
			return $arr[$key];
		}
		
		return null;
	}
	
	/**
	 * Get a customer by ID value
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_Customer
	 */
	protected function _getCustomer($CustomerID, $from = null, $line = null)
	{
		$Driver = $this->_driver;
		
		$errnum = 0;
		$errmsg = null;
		$res = $Driver->query("
			SELECT
				customer_id AS ID, 
				customer_first_name AS FirstName, 
				customer_last_name AS LastName, 
				CONCAT(customer_first_name, ' ', customer_last_name) AS BillAddress_Addr1, 
				customer_address1 AS BillAddress_Addr2, 
				customer_address2 AS BillAddress_Addr3, 
				customer_city AS BillAddress_City, 
				customer_state AS BillAddress_State, 
				customer_postal_code AS BillAddress_PostalCode, 
				customer_country AS BillAddress_Country, 
				CONCAT(shipping_first_name, ' ', shipping_last_name) AS ShipAddress_Addr1, 
				shipping_address1 AS ShipAddress_Addr2, 
				shipping_address2 AS ShipAddress_Addr3,  
				shipping_city AS ShipAddress_City, 
				shipping_state AS ShipAddress_State, 
				shipping_postal_code AS ShipAddress_PostalCode, 
				shipping_country AS ShipAddress_Country, 
				customer_phone AS Phone, 
				customer_email AS Email, 
				customer_company AS CompanyName, 
				CONCAT(customer_first_name, ' ', customer_last_name) AS Contact
			FROM 
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER . " 
			WHERE
				customer_id = " . (int) $CustomerID . " AND 
				foxycart_customer_user = '" . $Driver->escape($this->_api->user()) . "' ", $errnum, $errmsg);
		
		$format = $this->_foxycartConfigRead('foxycart_user_customer_format');
		if (!$format)
		{
			$format = '$FirstName $LastName';
		}
		
		if ($arr = $Driver->fetch($res))
		{
			$arr['Name'] = $this->_applyFormat($format, $arr);
			
			// Sometimes FoxyCart sends us blank shipping information, in which 
			//	case we want to default it to using the billing information as 
			//	the ship to address
			if (!$arr['ShipAddress_Addr1'] and 
				!$arr['ShipAddress_City'])
			{
				// Fill the shipping address info from the billing address info
				$arr['ShipAddress_Addr1'] = $arr['BillAddress_Addr1'];
				$arr['ShipAddress_Addr2'] = $arr['BillAddress_Addr2'];
				$arr['ShipAddress_Addr3'] = $arr['BillAddress_Addr3'];
				$arr['ShipAddress_City'] = $arr['BillAddress_City'];
				$arr['ShipAddress_State'] = $arr['BillAddress_State'];
				$arr['ShipAddress_PostalCode'] = $arr['BillAddress_PostalCode'];
				$arr['ShipAddress_Country'] = $arr['BillAddress_Country'];
			}
			
			return $this->_customerFromArray($arr);
		}
		
		return null;
	}
	
	/**
	 * Run a bunch of additional queries, and merge them with the existing result set
	 * 
	 * @param array $queries		An array of queries to run and merge together
	 * @param array $arr			The already existing result set
	 * @param array $vars			An array of variables for use in the queries
	 * @return array
	 */
	protected function _additionalQueries($queries, $arr, $vars)
	{
		return array();
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _listNewCustomersSince($datetime)
	{
		$list = array();
		
		$Driver = $this->_driver;
		
		$errnum = 0;
		$errmsg = '';
		$res = $Driver->query("
			SELECT
				customer_id
			FROM
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER . " 
			WHERE
				foxycart_customer_discovered_datetime > '" . $datetime . "' AND 
				foxycart_customer_user = '" . $Driver->escape($this->_api->user()) . "' ", $errnum, $errmsg);
		
		while ($arr = $Driver->fetch($res))
		{
			$list[] = $arr['customer_id'];
		}
		
		return $list;
	}
	
	/**
	 * 
	 * 
	 * 
	 */
	protected function _listModifiedCustomersSince($datetime)
	{
		return array();
	}
	
	/** 
	 * Get a list of order IDs which have been created since a given date/time
	 * 
	 * @param string $datetime
	 * @return array
	 */
	protected function _listNewOrdersSince($datetime)
	{
		$list = array();
		
		$Driver = $this->_driver;
		
		$errnum = 0;
		$errmsg = '';
		$res = $Driver->query("
			SELECT
				id
			FROM
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION . " 
			WHERE
				foxycart_transaction_discovered_datetime > '" . $datetime . "' AND 
				foxycart_transaction_user = '" . $Driver->escape($this->_api->user()) . "' ", $errnum, $errmsg);
		
		while ($arr = $Driver->fetch($res))
		{
			$list[] = $arr['id'];
		}
		
		return $list;
	}
	
	/**
	 * 
	 * 
	 * @param string $datetime
	 * @return array 
	 */
	protected function _listModifiedOrdersSince($datetime)
	{
		return array();
	}
	
	/**
	 * Get an order by ID value (returns a QuickBooks_Object_Invoice, QuickBooks_Object_SalesOrder, or QuickBooks_Object_SalesReceipt)
	 * 
	 * This function can return one of these types of objects:
	 * 	- QuickBooks_Object_Invoice
	 * 	- QuickBooks_Object_SalesOrder
	 * 	- QuickBooks_Object_SalesReceipt
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_*
	 */
	protected function _getOrder($ID)
	{
		$Driver = $this->_driver;
		
		$errnum = 0;
		$errmsg = '';
		$res = $Driver->query("
			SELECT
				id AS RefNumber, 
				customer_id AS CustomerID, 
				CONCAT(customer_first_name, ' ', customer_last_name) AS BillAddress_Addr1,
				CASE 
					WHEN LENGTH(customer_company) > 0 AND LENGTH(customer_address2) = 0 THEN customer_company
					ELSE customer_address1
				END AS BillAddress_Addr2, 
				CASE 
					WHEN LENGTH(customer_company) > 0 AND LENGTH(customer_address2) = 0 THEN customer_address1
					ELSE customer_address2 
				END AS BillAddress_Addr3, 
				customer_city AS BillAddress_City, 
				customer_state AS BillAddress_State, 
				customer_postal_code AS BillAddress_PostalCode, 
				customer_country AS BillAddress_Country, 
				CONCAT(shipping_first_name, ' ', shipping_last_name) AS ShipAddress_Addr1,
				CASE 
					WHEN LENGTH(shipping_company) > 0 AND LENGTH(shipping_address2) = 0 THEN shipping_company
					ELSE shipping_address1
				END AS ShipAddress_Addr2, 
				CASE 
					WHEN LENGTH(shipping_company) > 0 AND LENGTH(shipping_address2) = 0 THEN shipping_address1
					ELSE shipping_address2 
				END AS ShipAddress_Addr3, 
				shipping_city AS ShipAddress_City, 
				shipping_state AS ShipAddress_State, 
				shipping_postal_code AS ShipAddress_PostalCode, 
				shipping_country AS ShipAddress_Country, 
				shipping_total, 
				tax_total, 
				order_total, 
				processor_response, 
				payment_gateway_type
			FROM
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION . " 
			WHERE
				id = '" . (int) $ID . "' ", $errnum, $errmsg);
		
		if ($arr = $Driver->fetch($res))
		{
			// Sometimes FoxyCart sends us blank shipping information, in which 
			//	case we want to default it to using the billing information as 
			//	the ship to address
			if (!$arr['ShipAddress_Addr1'] and 
				!$arr['ShipAddress_City'])
			{
				// Fill the shipping address info from the billing address info
				$arr['ShipAddress_Addr1'] = $arr['BillAddress_Addr1'];
				$arr['ShipAddress_Addr2'] = $arr['BillAddress_Addr2'];
				$arr['ShipAddress_Addr3'] = $arr['BillAddress_Addr3'];
				$arr['ShipAddress_City'] = $arr['BillAddress_City'];
				$arr['ShipAddress_State'] = $arr['BillAddress_State'];
				$arr['ShipAddress_PostalCode'] = $arr['BillAddress_PostalCode'];
				$arr['ShipAddress_Country'] = $arr['BillAddress_Country'];
			}
			
			// Fetch the order items
			$items = $this->_getOrderItems($ID);
			
			// Create the shipping line item
			$arr_shipping = array(
				'Desc' => 'Shipping Charge', 
				'Amount' => $arr['shipping_total'], 
				);
			
			$shipping = $this->_shippingFromArray($arr_shipping);
			
			$handling = null;
			
			// Get a list of discounts for this order
			$discount = null;
			$tmp1 = '';
			$tmp2 = 0.0;
			
			$errnum = 0;
			$errmsg = '';
			$res_discount = $Driver->query("
				SELECT 
					* 
				FROM 
					" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDISCOUNT . " 
				WHERE 
					transaction_id = " . (int) $ID, $errnum, $errmsg);
			while ($arr_discount = $Driver->fetch($res_discount))
			{
				switch ($arr_discount['discount_coupon_discount_type'])
				{
					case 'price_amount':
						$tmp1 .= $arr_discount['discount_name'] . ' (' . $arr_discount['discount_code'] . '): ' . $arr_discount['discount_display'];
						$tmp2 += $arr_discount['discount_amount'];
						break;
					default:
						break;
				}
			}
			
			if ($tmp1 and $tmp2)
			{
				$arr_discount = array(
					'Desc' => $tmp1, 
					'Amount' => $tmp2, 
					);
				
				$discount = $this->_discountFromArray($arr_discount);
			}
			
			// Create the order
			return $this->_orderFromArray($arr, $items, $shipping, $handling, $discount);	
		}
		
		return null;
	}
	
	protected function _getPayment($ID)
	{
		return null;
	}
	
	protected function _getOrderItems($OrderID)
	{
		$Driver = $this->_driver;
	
		// Now, fetch a list of items 
		$errnum = null;
		$errmsg = null;
		$res2 = $Driver->query("
			SELECT
				product__id AS ItemID, 
				product_name AS Descrip, 
				product_price AS Rate, 
				product_quantity AS Quantity
			FROM
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAIL . " 
			WHERE
				transaction_id = " . (int) $OrderID . " ", $errnum, $errmsg);
			
		// ... and turn those details into order line items
		$items = array();
		while ($arr2 = $Driver->fetch($res2))
		{
			$item = $this->_orderItemFromArray($arr2);
			
			if ($item)
			{
				$items[] = $item;
			}
		}
		
		return $items;
	}
	
	/** 
	 * 
	 * 
	 */
	protected function _defaultGetOrderQuery()
	{
		return null;		
	}
	
	protected function _defaultGetOrderItemsForOrderQuery()
	{
		return '';
	}
	
	/**
	 * Get a list of items for an order (Invoice, SalesReceipt, SalesOrder)
	 * 
	 * @param integer $OrderID
	 * @return array 
	 */
	protected function _getOrderItemsForOrder($OrderID)
	{
		return array();
	}

	protected function _getEstimateItemsForEstimate($EstimateID)
	{
		return false;
	}

	/** 
	 * Get a list of order IDs which have been created since a given date/time
	 * 
	 * @param string $datetime
	 * @return array
	 */
	protected function _listNewEstimatesSince($datetime)
	{
		return array();
	}
		
	/**
	 * 
	 * 
	 * @param string $datetime
	 * @return array 
	 */
	protected function _listModifiedEstimatesSince($datetime)
	{
		return $this->_listNewEstimatesSince($datetime);
	}
	
	/**
	 *
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_Estimate
	 */
	protected function _getEstimate($EstimateID)
	{
		return false;
	}
	
	/** 
	 * 
	 * 
	 */
	protected function _defaultGetEstimateQuery()
	{
		return null;		
	}
	
	protected function _defaultGetEstimateItemsForEstimateQuery()
	{
		return '';
	}
		
	/**
	 * Get a ship method object by ID
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_ShipMethod
	 */
	protected function _getShipMethod($ID)
	{
		return null;
	}
	
	protected function _defaultGetShipMethodQuery()
	{
		return null;
	}
	
	/**
	 * Get a payment method by ID value
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_PaymentMethod
	 */
	protected function _getPaymentMethod($ID)
	{
		return null;
	}
	
	/**
	 * Get a payment by Order ID
	 * 
	 * @param integer $OrderID
	 * @return QuickBooks_Object_ReceivePayment
	 */
	protected function _getPaymentForOrder($OrderID)
	{
		return null;
	}
	
	protected function _defaultGetPaymentForOrderQuery()
	{
		return null;
	}
	
	/**
	 * Get the order discount (if any)
	 * 
	 * @param integer $OrderID
	 * @return QuickBooks_Object_DiscountItem
	 */
	protected function _getDiscountForOrder($OrderID)
	{
		return null;
	}
	
	/**
	 * Get the coupon for this order (if any)
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_DiscountItem
	 */
	protected function _getCoupon($ID)
	{
		return null;
	}
	
	/** 
	 * Get a sales tax item by ID
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_SalesTaxItem
	 */
	protected function _getSalesTax($ID)
	{
		return null;
	}
	
	/**
	 * Get a shipping item for an order
	 * 
	 * @param integer $OrderID
	 * @return QuickBooks_Object_OtherChargeItem
	 */
	protected function _getShippingForOrder($OrderID)
	{
		return null;
	}
	
	/**
	 * 
	 * 
	 */
	protected function _getHandlingForOrder($OrderID)
	{
		return null;
	}
	
	protected function _defaultGetShippingForOrderQuery()
	{
		return null;
	}

	protected function _defaultGetHandlingForOrderQuery()
	{
		return null;
	}
	
	/**
	 * Get a product by ID number
	 * 
	 * This method can return any of the following types of objects:
	 * 	- QuickBooks_Object_ServiceItem
	 * 	- QuickBooks_Object_InventoryItem
	 * 	- QuickBooks_Object_NonInventoryItem
	 * 	- QuickBooks_Object_OtherChargeItem
	 * 
	 * @param integer $ID
	 * @return QuickBooks_Object_*
	 */
	protected function _getProduct($ProductID, $from = null, $line = null)
	{
		$Driver = $this->_driver;
		
		$errnum = 0;
		$errmsg = '';
		$res = $Driver->query("
			SELECT
				_id AS ProductID, 
				_name AS Name, 
				NULL AS IncomeAccountName,
				NULL AS COGSAccountName, 
				NULL AS AssetAccountName
			FROM
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_PRODUCT . " 
			WHERE
				_id = " . (int) $ProductID . " ", $errnum, $errmsg);
		
		if ($arr = $Driver->fetch($res))
		{
			
			$income_account = $this->_foxycartConfigRead('foxycart_user_item_account_income');
			if ($income_account)
			{
				// .... which one is it? 
				$arr['IncomeAccountName'] = $income_account;
				$arr['SalesOrPurchase_AccountName'] = $income_account;
				$arr['SalesAndPurchase_IncomeAccountName'] = $income_account;
			}
			
			return $this->_productFromArray($arr);
		}
		
		return null;
	}
	
	/**
	 *
	 * 
	 * @note This overrides the base class implementation of _getGenericShipping()
	 */
	protected function _getGenericShipping()
	{
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_SHIPPING_ID,
			'Name' => QUICKBOOKS_INTEGRATOR_SHIPPING_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_SHIPPING_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			'SalesOrPurchase_AccountName' => QUICKBOOKS_INTEGRATOR_NULL, 		// Pass special flag, will not overwrite defaults 
			);
		
		$income_account = $this->_foxycartConfigRead('foxycart_user_item_account_income');
		if ($income_account)
		{
			$arr['SalesOrPurchase_AccountName'] = $income_account;
		}
		
		$Item = $this->_productFromArray($arr, $this->_config['push_shipping_as']);
		
		return $Item;		
	}
	
	protected function _getGenericDiscount()
	{
		$arr = array(
			'ProductID' => QUICKBOOKS_INTEGRATOR_DISCOUNT_ID,
			'Name' => QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME,
			'SalesTaxCodeName' => QUICKBOOKS_NONTAXABLE,   
			'SalesOrPurchase_Desc' => QUICKBOOKS_INTEGRATOR_DISCOUNT_NAME, 
			'SalesOrPurchase_Price' => 0.0, 
			'SalesOrPurchase_AccountName' => QUICKBOOKS_INTEGRATOR_NULL, 		// Pass special flag, will not overwrite defaults 
			);
		
		$discount_account = $this->_foxycartConfigRead('foxycart_user_item_account_discount');
		if ($discount_account)
		{
			$arr['SalesOrPurchase_AccountName'] = $discount_account;
		}
		
		$Item = $this->_productFromArray($arr, $this->_config['push_shipping_as']);
		
		return $Item;
	}
	
	protected function _defaultGetGiftCertificateQuery()
	{
		return null;		
	}
	
	protected function _defaultGetProductQuery()
	{
		return null;
	}
}
