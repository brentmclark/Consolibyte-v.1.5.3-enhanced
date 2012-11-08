<?php

/**
 * Callback methods for the server integrator components
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Server
 */

/**
 * QuickBooks base classes
 */
require_once 'QuickBooks.php';

/**
 * QuickBooks API object-oriented classes
 */
require_once 'QuickBooks/API.php';

/**
 * API singleton classes
 */
require_once 'QuickBooks/API/Singleton.php';

/**
 * Server integrator class
 */
require_once 'QuickBooks/Server/Integrator.php';

/**
 * Callback methods for the server integrator 
 */
class QuickBooks_Server_Integrator_Callbacks
{
	static public function integrateQueryCustomer($CustomerID)
	{
		return QuickBooks_Server_Integrator_Callbacks::integrateCustomer($CustomerID, false, true);
	}
	
	static public function integrateAddCustomer($CustomerID)
	{
		//print('adding!');
		return QuickBooks_Server_Integrator_Callbacks::integrateCustomer($CustomerID, false);
	}
	
	static public function integrateModCustomer($CustomerID)
	{
		//print('modifying!');
		return QuickBooks_Server_Integrator_Callbacks::integrateCustomer($CustomerID, true);
	}
	
	static public function integrateCustomer($CustomerID, $modify = false, $query = false)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		$API->log('Analyzing customer #' . $CustomerID, QUICKBOOKS_LOG_DEVELOP);
		
		$extras = $Integrator->getCustomerExtras($CustomerID, __FILE__, __LINE__);
		
		if ($Customer = $Integrator->getCustomer($CustomerID, __FILE__, __LINE__))
		{
			$action = 'add';
			if ($modify)
			{
				$action = 'mod';
			}
			else if ($query)
			{
				$action = 'query';
			}
			
			$API->log('Integrating customer #' . $CustomerID . ' (' . $action . ')', QUICKBOOKS_LOG_DEVELOP);
			
			$continue = false;
			if ($modify and 
				$API->modifyCustomer($Customer, 'QuickBooks_Server_Integrator_Callbacks::modCustomer', $CustomerID))
			{
				$continue = true;
			}
			else if ($query and
				$API->getCustomerByName($Integrator->getCustomerNameForQuery($CustomerID), 'QuickBooks_Server_Integrator_Callbacks::getCustomerByName', $CustomerID))
			{
				return true;
			}
			else if (!$modify and !$query and 
				$API->addCustomer($Customer, 'QuickBooks_Server_Integrator_Callbacks::addCustomer', $CustomerID))
			{
				// Call a hook to indicate the customer is being pushed to QuickBooks
				/*
				$user = $API->user();
				$hook_data = array(
					'CustomerID' => $CustomerID, 
					'Customer' => $Customer, 
					);
				$this->_callHooks(
					QUICKBOOKS_SERVER_INTEGRATOR_HOOK_INTEGRATECUSTOMER, 
					null, 
					$user, 
					null, 
					$err, 
					$hook_data);
				*/
				
				$continue = true;
			}
			
			if ($continue)
			{
				if (is_array($extras))
				{
					foreach ($extras as $key => $Extra)
					{
						$API->addDataExt($Extra, 'QuickBooks_Server_Integrator_Callbacks::addExtra', $CustomerID . '-' . $key, null, QUICKBOOKS_ADD_CUSTOMER);
						
						if ($modify)
						{
							$API->modifyDataExt($Extra, 'QuickBooks_Server_Integrator_Callbacks::modExtra', $CustomerID . '-' . $key, null, QUICKBOOKS_MOD_CUSTOMER);
						}
					}
				}
				
				return true;				
			}
		}
		
		return false;
	}
		
	/**
	 * 
	 * 
	 * @param string $method
	 * @param string $action
	 * @param mixed $ID
	 * @param string $err
	 * @param string $qbxml
	 * @param QuickBooks_Iterator $Iterator
	 * @param resource $qbres
	 * @return boolean
	 */
	static public function getCustomerByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		if ($Iterator->count() == 1)
		{
			// If we found the customer in QuickBooks, create a mapping with the ListID value
			
			$Customer = $Iterator->next();
			if ($API->createMapping(QUICKBOOKS_OBJECT_CUSTOMER, $ID, $Customer->getListID(), $Customer->getEditSequence()))
			{
				// Let's make sure that this customer is up-to-date	
				
				return QuickBooks_Server_Integrator_Callbacks::integrateModCustomer($ID);
			}
		}
		else if ($Iterator->count() == 0)
		{
			// Otherwise, we need to queue up an add request to add this cart item to QuickBooks
			
			return QuickBooks_Server_Integrator_Callbacks::integrateAddCustomer($ID);
		}
		
		return false;
	}
	
	static public function getInvoiceByRefNumber($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getProductByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		//print('got called!');
		//exit;
		
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		//print_r($Iterator);
		//exit;
		
		
		
		if ($Iterator->count() == 1)
		{
			// If we found the item in QuickBooks, create a mapping with the ListID value
			
			$Item = $Iterator->next();
			return $API->createMapping($Item->object(), $ID, $Item->getListID(), $Item->getEditSequence());
		}
		else if ($Iterator->count() == 0)
		{
			// Otherwise, we need to queue up an add request to add this cart item to QuickBooks
			
			$Product = $Integrator->getProduct($ID, __FILE__, __LINE__);
			
			switch ($Product->object())
			{
				case QUICKBOOKS_OBJECT_SERVICEITEM:
					return $API->addServiceItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addServiceItem', $ID);
					break;
				case QUICKBOOKS_OBJECT_INVENTORYITEM:
					return $API->addInventoryItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addInventoryItem', $ID);
					break;
				case QUICKBOOKS_OBJECT_NONINVENTORYITEM:
					return $API->addNonInventoryItem($Product, 'QuickBooks_Server_Integrator_Callbacks::addNonInventoryItem', $ID);
					break;
			}
		}
		
		return false;
	}
	
	static public function getServiceItemByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getInventoryItemByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getNonInventoryItemByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getDiscountItemByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getClassByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getAccountByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		
	}
	
	static public function getShipMethodByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		if ($Iterator->count() == 1)
		{
			// If we found the object in QuickBooks, create a mapping with the ListID value
			
			$ShipMethod = $Iterator->next();
			return $API->createMapping(QUICKBOOKS_OBJECT_SHIPMETHOD, $ID, $ShipMethod->getListID(), $ShipMethod->getEditSequence());
		}
		else if ($Iterator->count() == 0)
		{
			// Otherwise, we need to queue up an add request to add this cart item to QuickBooks
			
			$ShipMethod = $Integrator->getShipMethod($ID);
			return $API->addShipMethod($ShipMethod, 'QuickBooks_Server_Integrator_Callbacks::addShipMethod', $ID);
		}
		
		return false;
	}
	
	/** 
	 * 
	 * 
	 * @param string $method
	 * @param string $action
	 * @param mixed $ID
	 * @param string $err
	 * @param string $qbxml
	 * @param QuickBooks_Iterator $Iterator
	 * @param resource $qbres
	 * @return boolean
	 */ 
	static public function getPaymentMethodByName($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		if ($Iterator->count() == 1)
		{
			// If we found the object in QuickBooks, create a mapping with the ListID value
			
			$PaymentMethod = $Iterator->next();
			return $API->createMapping(QUICKBOOKS_OBJECT_PAYMENTMETHOD, $ID, $PaymentMethod->getListID(), $PaymentMethod->getEditSequence());
		}
		else if ($Iterator->count() == 0)
		{
			// Otherwise, we need to queue up an add request to add this cart item to QuickBooks
			
			$PaymentMethod = $Integrator->getPaymentMethod($ID);
			return $API->addPaymentMethod($PaymentMethod, 'QuickBooks_Server_Integrator_Callbacks::addPaymentMethod', $ID);
		}
		
		return false;
	}
	
	static public function listInvoicesModifiedAfter($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		while ($Invoice = $Iterator->next())
		{
			return false;
		}
		
		return true;		
	}
	
	static public function listAccountsModifiedAfter($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		while ($Account = $Iterator->next())
		{
			// Store this in the database
			$Integrator->saveAccount($Account);
		}
		
		return true;
	}
	
	static public function listPaymentMethodsModifiedAfter($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		while ($PaymentMethod = $Iterator->next())
		{
			// Store this in the database
			$Integrator->savePaymentMethod($PaymentMethod);
		}
		
		return true;
	}	
	
	static public function listCustomerTypesModifiedAfter($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		while ($CustomerType = $Iterator->next())
		{
			// Store this in the database
			$Integrator->saveCustomerType($CustomerType);
		}
		
		return true;
	}		
	
	static public function listShipMethodsModifiedAfter($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		while ($ShipMethod = $Iterator->next())
		{
			// Store this in the database
			$Integrator->saveShipMethod($ShipMethod);
		}
		
		return true;
	}
	
	static public function listEstimatesModifiedAfter($method, $action, $ID, $err, $qbxml, $Iterator, $qbres)
	{
		$API = QuickBooks_API_Singleton::getInstance();
		$Integrator = QuickBooks_Integrator_Singleton::getInstance();
		
		while ($Estimate = $Iterator->next())
		{
			// Let's check if this estimate already exists in the system
			$EstimateID = null;
			if ($API->hasApplicationID(QUICKBOOKS_OBJECT_ESTIMATE, $Estimate->getTxnID()))
			{
				$EstimateID = $API->fetchApplicationID(QUICKBOOKS_OBJECT_ESTIMATE, $Estimate->getTxnID());
			}
			
			// Now, there's a customer assigned to this estimate, let's make sure the customer exists 
			if ($API->hasApplicationID(QUICKBOOKS_OBJECT_CUSTOMER, $Estimate->getCustomerListID()))
			{
				// Great, it exists!
			}
			else
			{
				// Uh oh... create it!
				$Customer = new QuickBooks_Object_Customer();
				$Customer->setListID($Estimate->getCustomerListID());
				$Customer->setName($Estimate->getCustomerName());
				
				$Integrator->setCustomer(null, $Customer);
			}
			
			// There are line items assigned to this estimate too, and each line item has a product...
			//foreach ($Estimate->listLineItems
			
			$Integrator->setEstimate($EstimateID, $Estimate);
		}
		
		return true;
	}
	
	static public function addPaymentMethod()
	{
		
	}
	
	static public function addShipMethod()
	{
		
	}
	
	static public function addServiceItem()
	{
		
	}
	
	static public function addInventoryItem()
	{
		
	}
	
	static public function addNonInventoryItem()
	{
		
	}
	
	static public function addInvoice()
	{
		
	}
	
	static public function addEstimate()
	{
		
	}
	
	static public function addSalesReceipt()
	{
		
	}
	
	static public function addAccount()
	{
		
	}
	
	static public function addClass()
	{
		
	}
	
	static public function addExtra()
	{
		
	}
	
	static public function modExtra()
	{
		
	}
	
	static public function addCustomer()
	{
		
	}
	
	static public function modCustomer()
	{
		
	}
	
	static public function addReceivePayment()
	{
		
	}
}
