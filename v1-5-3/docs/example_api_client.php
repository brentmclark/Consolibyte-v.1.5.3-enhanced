<?php

/**
 * 
 * 
 * @package QuickBooks
 * @subpackage Documentation
 */

// 
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

// 
if (function_exists('date_default_timezone_set'))
{
	date_default_timezone_set('America/New_York');
}

// 
ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '/Users/keithpalmerjr/Projects/QuickBooks');

/**
 * 
 */
require_once 'QuickBooks.php';

$user = 'api';
$source_type = QUICKBOOKS_API_SOURCE_WEB;
$api_driver_dsn = 'mysql://root:root@localhost/quickbooks_api';
//$api_driver_dsn = 'pgsql://pgsql@localhost/quickbooks';
$source_dsn = 'http://quickbooks:test@localhost/path/to/server.php';
$api_options = array();
$source_options = array();
$driver_options = array();

if (!QuickBooks_Utilities::initialized($api_driver_dsn))
{
	// 
	QuickBooks_Utilities::initialize($api_driver_dsn);
	
	// 
	QuickBooks_Utilities::createUser($api_driver_dsn, 'api', 'password');
}

$API = new QuickBooks_API($api_driver_dsn, $user, $source_type, $source_dsn, $api_options, $source_options, $driver_options);


// CUSTOMERS
$fname = 'Shannon ' . mt_rand();
$lname = 'Daniels';

$Customer = new QuickBooks_Object_Customer();

$Customer->setFirstName($fname);
$Customer->setLastName($lname);

$Customer->setShipAddress('56 Cowles Road', '', '', '', '', 'Willington', 'CT');
$Customer->setMiddleName('R');
$Customer->setSalutation('Mr.');

$Customer->setPhone('1.860.634.1602');

$API->addCustomer($Customer, '_quickbooks_customer_add_callback', 15);


// INVOICES
$Invoice = new QuickBooks_Object_Invoice();
//$Invoice->setOther('test of other');		// for some reason this field doesn't work...
$Invoice->setMemo('test of a memo');
$Invoice->setCustomerApplicationID(15);
$Invoice->setRefNumber(125);

$InvoiceLine1 = new QuickBooks_Object_Invoice_InvoiceLine();
$InvoiceLine1->setItemApplicationID(12);
$InvoiceLine1->setAmount(300.00);
$InvoiceLine1->setQuantity(3);

$InvoiceLine2 = new QuickBooks_Object_Invoice_InvoiceLine();
$InvoiceLine2->setItemApplicationID(11);
$InvoiceLine2->setAmount(225.00);
$InvoiceLine2->setQuantity(5);

$Invoice->addInvoiceLine($InvoiceLine1);
$Invoice->addInvoiceLine($InvoiceLine2);

$API->addInvoice($Invoice, '_quickbooks_invoice_add_callback', 20);


// VENDORS
$Vendor = new QuickBooks_Object_Vendor();
$Vendor->setName('Test Vendor ' . mt_rand());
$Vendor->setPhone('1.860.634.1602');
$Vendor->setFirstName('Test');
$Vendor->setFax('1.860.429.5183');

$API->addVendor($Vendor, '_quickbooks_vendor_add_callback', 19);


// SERVICE ITEMS
$ServiceItem = new QuickBooks_Object_ServiceItem();
$ServiceItem->isSalesOrPurchase(true);

$ServiceItem->setName('My Service Item ' . mt_rand());
$ServiceItem->setPrice(250);
$ServiceItem->setDescription('My Test Item Description');
$ServiceItem->setAccountName('Sales');


$API->addServiceItem($ServiceItem, '_quickbooks_serviceitem_add_callback', 12);


// INVENTORY ITEMS
$InventoryItem = new QuickBooks_Object_InventoryItem();
$InventoryItem->setName('Test Inventory Item ' . mt_rand());
$InventoryItem->setSalesPrice(25.25);

$InventoryItem->setIncomeAccountName('Sales');
$InventoryItem->setCOGSAccountName('Cost of Goods Sold');
$InventoryItem->setAssetAccountName('Inventory Asset');
$InventoryItem->setPreferredVendorApplicationID(19);

$API->addInventoryItem($InventoryItem, '_quickbooks_inventoryitem_add_callback', 11);

/*
// QUERYING FOR ACCOUNTS
$datetime = '2009-01-02 01:02:03';
$API->listAccountsModifiedAfter($datetime, '_quickbooks_account_query_callback');
*/
