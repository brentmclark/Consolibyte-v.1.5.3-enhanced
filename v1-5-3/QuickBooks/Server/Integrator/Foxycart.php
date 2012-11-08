<?php

/**
 * 
 * 
 * 
 * @package QuickBooks
 * @subpackage Server
 */

define('QUICKBOOKS_SERVER_INTEGRATOR_MODULE_FOXYCART', 'foxycart');

define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_USER', 'qb_foxycart_user');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_LOG', 'qb_foxycart_log');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_DATAFEED', 'qb_foxycart_datafeed');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER', 'qb_foxycart_customer');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_PRODUCT', 'qb_foxycart_product');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION', 'qb_foxycart_transaction');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAIL', 'qb_foxycart_transaction_detail');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDISCOUNT', 'qb_foxycart_transaction_discount');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONCUSTOMFIELD', 'qb_foxycart_transaction_customfield');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAILOPTION', 'qb_foxycart_transaction_detail_option');

define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTCUSTOMER', 'QuickBooks_Server_Integrator_Foxycart::insertCustomer');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_UPDATECUSTOMER', 'QuickBooks_Server_Integrator_Foxycart::updateCustomer');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTORDER', 'QuickBooks_Server_Integrator_Foxycart::insertOrder');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_UPDATEORDER', 'QuickBooks_Server_Integrator_Foxycart::updateOrder');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTORDERLINE', 'QuickBooks_Server_Integrator_Foxycart::insertOrderLine');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_UPDATEORDERLINE', 'QuickBooks_Server_Integrator_Foxycart::updateOrderLine');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTPRODUCT', 'QuickBooks_Server_Integrator_Foxycart::insertProduct');
define('QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_UPDATEPRODUCT', 'QuickBooks_Server_Integrator_Foxycart::updateProduct');

/**
 * 
 */
require_once 'QuickBooks.php';

/**
 * 
 *
 */
require_once 'QuickBooks/Server/Integrator.php';

/**
 * 
 */
require_once 'QuickBooks/Encryption/Factory.php';

/**
 * 
 * 
 */
class QuickBooks_Server_Integrator_FoxyCart extends QuickBooks_Server_Integrator
{
	/**
	 * 
	 */
	protected $_api;
	
	/**
	 * 
	 */
	protected $_integrator;
	
	/** 
	 * 
	 */
	protected $_foxycart_options;
	
	/**
	 * Create and return an instance of the iterator
	 * 
	 * FoxyCart uses a database instance class to cache data received from the 
	 * FoxyCart data feeds, so we also create a database instance class and 
	 * send that to the iterator. 
	 * 
	 * @param string $integrator_dsn_or_conn
	 * @param array $integrator_options
	 * @return QuickBooks_Integrator_*
	 */
	protected function _integratorFactory($integrator_dsn_or_conn, $integrator_options, $API)
	{
		$Driver = QuickBooks_Driver_Factory::create($integrator_dsn_or_conn, $integrator_options);
		return new QuickBooks_Integrator_Foxycart($Driver, $integrator_options, $API);
	}
	
	/**
	 * Handle a SOAP request *or* a FoxyCart Datafeed message
	 * 
	 * If this method detects a SOAP request, it will act as a FoxyCart web 
	 * service integration using the Web Connector. If it detects a FoxyCart 
	 * data feed, it will process the data feed and store it in database tables 
	 * for sending to QuickBooks later. 
	 * 
	 * @param boolean $return
	 * @param boolean $debug
	 * @return mixed
	 */
	public function handle($return = false, $debug = false)
	{
		if (isset($_POST['FoxyData']))
		{
			return $this->_foxycart($this->_api, $this->_integrator, $this->_integrator_config, $_POST['FoxyData']);
		}
		else
		{
			return parent::handle($return, $debug);
		}
	}
	
	/**
	 * Log a message to the FoxyCart log table
	 * 
	 * 
	 */
	protected function _foxycartLog($message, $user, $feed = null)
	{
		if ($feed)
		{
			$feed = " '" . $this->_integrator->escape($feed) . "' ";
		}
		else
		{
			$feed = " NULL ";
		}
		
		$errnum = 0;
		$errmsg = null;
		return $this->_integrator->query("
			INSERT INTO
				" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_LOG . "
			(
				foxycart_log_msg, 
				foxycart_log_user, 
				foxycart_log_datafeed, 
				foxycart_log_datetime
			) VALUES (
				'" . $this->_integrator->escape($message) . "', 
				'" . $this->_integrator->escape($user) . "', 
				" . $feed . ", 
				'" . date('Y-m-d H:i:s') . "' 
			)", $errnum, $errmsg);
	}
	
	protected function _foxycart($API, $Integrator, $foxycart_config, $foxydata)
	{
		$FOXYCART_FEED = date('Y-m-d H:i:s');
		$FOXYCART_NOW = date('Y-m-d H:i:s');
		$FOXYCART_USER = $API->user();
		$FOXYCART_KEY = null;
		
		// Check the username
		$errnum = null;
		$errmsg = null;
		$check = $Integrator->fetch($Integrator->query("SELECT * FROM " . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_USER . " WHERE foxycart_user_name = '" . $Integrator->escape($FOXYCART_USER) . "' ", $errnum, $errmsg));
		if (!$check)
		{
			$msg = 'Could not locate a user account for: [' . $FOXYCART_USER . ']';
			$this->_log($msg, $FOXYCART_USER, $FOXYCART_FEED);
			die($msg);
		}
		
		if (empty($foxycart_config['foxycart_secret_key']))
		{
			// Look it up from the QuickBooks config table
			// @todo Ecccchhhh should I be doing this? 
			$tmp1 = null;
			$tmp2 = null;
			$FOXYCART_KEY = $API->configRead(QUICKBOOKS_SERVER_INTEGRATOR_MODULE_FOXYCART, 'foxycart_secret_key', $tmp1, $tmp2);
			
			// If not found, look it up in the foxycart_user table
			if (!$FOXYCART_KEY)
			{
				$errnum = 0;
				$errmsg = null;
				$res = $Integrator->query("SELECT * FROM " . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_USER . " WHERE foxycart_user_name = '" . $Integrator->escape($FOXYCART_USER) . "' ", $errnum, $errmsg);
				$arr = $Integrator->fetch($res);
				
				$FOXYCART_KEY = $arr['foxycart_user_key'];
			}
		}
		else
		{
			$FOXYCART_KEY = $foxycart_config['foxycart_secret_key'];
		}
		
		if ($FOXYCART_KEY)
		{
			//$xml = rc4crypt::decrypt(FOXY_SECRET_KEY, urldecode($_POST['FoxyData']));
			$crypt = QuickBooks_Encryption_Factory::create('RC4');
			$xml = $crypt->decrypt($FOXYCART_KEY, urldecode($foxydata));
		}
		else
		{
			$xml = $foxydata;
		}
		
		if ($xml[0] != '<')
		{
			$msg = 'Could not process data with key: [' . $FOXYCART_KEY . '], data: ' . $xml;
			$this->_log($msg, $FOXYCART_USER, $FOXYCART_FEED);
			die($msg);
		}
		
		$map = array(
			'customer_id' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ),
			'customer_first_name' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'customer_last_name' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ),  
			'customer_company' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'customer_address1' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'customer_address2' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'customer_city' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'customer_state' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'customer_postal_code' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ),  
			'customer_country' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			
			'customer_phone' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER ), 
			'customer_email' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER ), 
			'customer_ip' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, ), 
			'shipping_first_name' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_last_name' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ),
			'shipping_company' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ),  
			'shipping_address1' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_address2' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_city' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_state' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_postal_code' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_country' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER, QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_phone' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER ), 
			
			'id' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'transaction_date' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'purchase_order' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'product_total' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'tax_total' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'shipping_total' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'order_total' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'processor_response' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ), 
			'payment_gateway_type' => array( QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION ),
			);
		
		$primaries = array(
			QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER => 'customer_id', 
			QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION => 'id', 
			);
		
		$foxyusers = array(
			QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER => 'foxycart_customer_user', 
			QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION => 'foxycart_transaction_user', 			
			);
		
		// Parse the XML
		$errnum = null;
		$errmsg = null;
		$Parser = new QuickBooks_XML_Parser($xml);
		if ($Doc = $Parser->parse($errnum, $errmsg))
		{
			$Root = $Doc->getRoot();

			// Log the datafeed
			$record = array(
				'foxydata' => $xml, 
				'datafeed_version' => $Root->getChildDataAt('foxydata datafeed_version'), 
				'foxycart_datafeed_datetime' => $FOXYCART_FEED, 
				'foxycart_datafeed_user' => $FOXYCART_USER
				);
			$Integrator->insert(QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_DATAFEED, $record, false);
			
			// Loop through all of the transactions
			$Transactions = $Root->getChildAt('foxydata transactions');
			
			foreach ($Transactions->children() as $Transaction)
			{
				$tables = array(
					QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER => array(), 
					QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION => array(), 
					);
				
				foreach ($Transaction->children() as $Data)
				{
					$name = $Data->name();
					if (isset($map[$name]))
					{
						foreach ($map[$name] as $table)
						{
							$tables[$table][$name] = $Data->data();
						}
						
						
					}
				}
				
				//print_r($tables);
				
				foreach ($tables as $table => $data)
				{
					$key = $primaries[$table];
					$foxyuser = $foxyusers[$table];
					
					if ($record = $Integrator->get($table, array( $key => $tables[$table][$key], $foxyuser => $FOXYCART_USER )))
					{
						// Update
						//print('update');
						//exit;
						
						switch ($table)
						{
							case QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION:
								
								break;
						}
						
					}
					else
					{
						// Insert
						//print_r($data);
						//exit;
						
						switch ($table)
						{
							case QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_CUSTOMER:
								
								$data['foxycart_customer_discovered_datetime'] = $FOXYCART_NOW;
								$data['foxycart_customer_discovered_datafeed'] = $FOXYCART_FEED;
								$data['foxycart_customer_user'] = $FOXYCART_USER;
								
								// Call a hook to indicate a new customer has been found
								$hook_data = array(
									'customer' => $data, 
									);
								
								$err = null;
								$this->_callHooks(
									QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTCUSTOMER, 
									null, 
									$FOXYCART_USER, 
									null, 
									$err, 
									$hook_data);
								
								break;
							case QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTION:
								
								$data['foxycart_transaction_discovered_datetime'] = $FOXYCART_NOW;
								$data['foxycart_transaction_discovered_datafeed'] = $FOXYCART_FEED;
								$data['foxycart_transaction_user'] = $FOXYCART_USER;
								
								// We need to set this flag so that we can call the order hook later
								$hook_data = array(
									'order' => $data, 
									);
								
								$err = null;
								$this->_callHooks(
									QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTORDER, 
									null, 
									$FOXYCART_USER, 
									null, 
									$err, 
									$hook_data);
									
								break;
						}
						
						$Integrator->insert($table, $data, false);
					}
				}
				
				// Delete any current line items (and line item options)
				$errnum = 0;
				$errmsg = '';
				$res = $Integrator->query("
					SELECT 
						_id
					FROM 
						" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAIL . " 
					WHERE
						transaction_id = " . (int) $Transaction->getChildDataAt('transaction id') . " AND
						foxycart_transaction_detail_user = '" . $Integrator->escape($FOXYCART_USER) . "' ", $errnum, $errmsg);
				
				while ($arr = $Integrator->fetch($res))
				{
					// Delete the options
					$Integrator->query("DELETE FROM " . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAILOPTION . " WHERE transaction_detail__id = " . $arr['_id'], $errnum, $errmsg);
					
					// Delete the detail item
					$Integrator->query("DELETE FROM " . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAIL . " WHERE _id = " . $arr['_id'], $errnum, $errmsg);
				}
				
				// Also delete any custom fields...
				$errnum = 0;
				$errmsg = '';
				$Integrator->query("
					DELETE FROM
						" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONCUSTOMFIELD . "
					WHERE
						transaction_id = " . (int) $Transaction->getChildDataAt('transaction id') . " AND 
						foxycart_transaction_customfield_user = '" . $Integrator->escape($FOXYCART_USER) . "' ", $errnum, $errmsg);
				
				// And delete any discounts
				$errnum = 0;
				$errmsg = '';
				$Integrator->query("
					DELETE FROM
						" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDISCOUNT . "
					WHERE
						transaction_id = " . (int) $Transaction->getChildDataAt('transaction id') . " AND 
						foxycart_transaction_discount_user = '" . $Integrator->escape($FOXYCART_USER) . "' ", $errnum, $errmsg);				
				
				// Now, process the transaction line details
				foreach ($Transaction->children() as $Node)
				{
					if ($Node->name() == 'discounts')
					{
						foreach ($Node->children() as $Discount)
						{
							/*
							<discount>
								<code>MM</code>
								<name>McMahon Medical</name>
								<amount>-149.99</amount>
								<display>-149.99</display>
								<coupon_discount_type>price_amount</coupon_discount_type>
								<coupon_discount_details>149.99-149.99, 99-0</coupon_discount_details>
							</discount>
							*/
							
							$discount = array(
								'transaction_id' => $Transaction->getChildDataAt('transaction id'), 
								'discount_code' => $Discount->getChildDataAt('discount code'), 
								'discount_name' => $Discount->getChildDataAt('discount name'), 
								'discount_amount' => (float) $Discount->getChildDataAt('discount amount'), 
								'discount_display' => $Discount->getChildDataAt('discount display'), 
								'discount_coupon_discount_type' => $Discount->getChildDataAt('discount coupon_discount_type'), 
								'discount_coupon_discount_details' => $Discount->getChildDataAt('discount coupon_discount_details'), 
								'foxycart_transaction_discount_user' => $FOXYCART_USER, 
								);
							
							//print_r($details);
							$Integrator->insert(QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDISCOUNT, $discount, false);
							$_id = $Integrator->last();
						}
					}
					else if ($Node->name() == 'custom_fields')
					{
						foreach ($Node->children() as $CustomField)
						{
							/*
							<custom_field>
								<custom_field_name>Comments</custom_field_name>
								<custom_field_value>JO145</custom_field_value>
							</custom_field>
							*/
							
							$customfield = array(
								'transaction_id' => $Transaction->getChildDataAt('transaction id'), 
								'customfield_custom_field_name' => $CustomField->getChildDataAt('custom_field custom_field_name'), 
								'customfield_custom_field_value' => $CustomField->getChildDataAt('custom_field custom_field_value'), 
								'foxycart_transaction_customfield_user' => $FOXYCART_USER, 
								);
							
							//print_r($details);
							$Integrator->insert(QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONCUSTOMFIELD, $customfield, false);
							$_id = $Integrator->last();
						}						
					}
					else if ($Node->name() == 'transaction_details')
					{
						foreach ($Node->children() as $TransactionDetail)
						{
							// Let's see if we've already seen this type of product before... 
							$errnum = 0;
							$errmsg = '';
							$res_product = $Integrator->query("
								SELECT 
									_id 
								FROM 
									" . QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_PRODUCT . " 
								WHERE 
									_name = '" . $Integrator->escape($TransactionDetail->getChildDataAt('transaction_detail product_name')) . "' AND 
									foxycart_product_user = '" . $Integrator->escape($FOXYCART_USER) . "' ", $errnum, $errmsg);
							  
							if ($arr_product = $Integrator->fetch($res_product))
							{
								$product_id = $arr_product['_id'];
							}
							else
							{
								// Product doesn't exist yet, create it
								$tmp = array(
									'_name' => $TransactionDetail->getChildDataAt('transaction_detail product_name'), 
									'foxycart_product_discovered_datetime' => $FOXYCART_NOW,
									'foxycart_product_discovered_datafeed' => $FOXYCART_FEED,
									'foxycart_product_user' => $FOXYCART_USER, 
									);
								
								$Integrator->insert(QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_PRODUCT, $tmp, false);
								$product_id = $Integrator->last();

								// Call the hook to indicate we're adding a new line item
								$hook_data = array(
									'product' => $tmp, 
									);
								
								$err = null;
								$this->_callHooks(
									QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTPRODUCT, 
									null, 
									$FOXYCART_USER, 
									null, 
									$err, 
									$hook_data);
							}
							
							$details = array(
								'transaction_id' => $Transaction->getChildDataAt('transaction id'), 
								'product__id' => $product_id, 
								'product_name' => $TransactionDetail->getChildDataAt('transaction_detail product_name'), 
								'product_price' => $TransactionDetail->getChildDataAt('transaction_detail product_price'), 
								'product_quantity' => $TransactionDetail->getChildDataAt('transaction_detail product_quantity'), 
								'product_weight' => $TransactionDetail->getChildDataAt('transaction_detail product_weight'), 
								'product_code' => $TransactionDetail->getChildDataAt('transaction_detail product_code'), 
								'foxycart_transaction_detail_user' => $FOXYCART_USER, 
								);

							// Call the hook to indicate we're adding a new line item
							$hook_data = array(
								'orderline' => $details, 
								);
							
							$err = null;
							$this->_callHooks(
								QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_HOOK_INSERTORDERLINE, 
								null, 
								$FOXYCART_USER, 
								null, 
								$err, 
								$hook_data);
								
							//print_r($details);
							$Integrator->insert(QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAIL, $details, false);
							$_id = $Integrator->last();
							
							// Now, handle the options for each line item
							foreach ($TransactionDetail->children() as $Node2)
							{
								if ($Node2->name() == 'transaction_detail_options')
								{
									foreach ($Node2->children() as $TransactionDetailOption)
									{
										$details = array(
											'transaction_detail__id' => $_id, 
											'product_option_name' => $TransactionDetailOption->getChildDataAt('transaction_detail_option product_option_name'), 
											'product_option_value' => $TransactionDetailOption->getChildDataAt('transaction_detail_option product_option_value'),  
											'price_mod' => $TransactionDetailOption->getChildDataAt('transaction_detail_option price_mod'),  
											'weight_mod' => $TransactionDetailOption->getChildDataAt('transaction_detail_option weight_mod'),  
											);
										
										$Integrator->insert(QUICKBOOKS_SERVER_INTEGRATOR_FOXYCART_TABLE_TRANSACTIONDETAILOPTION, $details, false);							
									}
								}
							}
						}
					}
				}
			}
		}
		
		die('foxy');
	}
}
