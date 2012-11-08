<?php

/**
 * QuickBooks integration package via the QuickBooks Web Connector SOAP service 
 * 
 * See also:
 * 	http://wiki.consolibyte.com/
 * 
 * Some notes:
 * 	- Go download the QuickBooks SDK (it has lots of helpful stuff in it) 
 * 	- Onscreen Reference (shows all of the XML commands)
 * 	- Tools > qbXML Validator (the QuickBooks Web Connector error log shows almost no debugging information, run your XML through the Validator and it will tell you *exactly* what the error in your XML stream is)
 * 	- Your version of QuickBooks might not support the latest version of the qbXML SDK, so you might have to set the qbXML message version with: <?qbxml version="x.y"?> (try 2.0 or another low number if you get error messages about versions)
 * 	- Check our the QuickBooks_Utilities class, it contains a few helpful static methods
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
 * 
 * @package QuickBooks
 */

/**
 * Path separator for file paths/include/require paths
 * @var string
 */
define('QUICKBOOKS_DIRECTORY_SEPARATOR', PATH_SEPARATOR);

// Include path modifications (relative paths within library)
ini_set('include_path', ini_get('include_path') . QUICKBOOKS_DIRECTORY_SEPARATOR . dirname(__FILE__));

if (function_exists('date_default_timezone_get'))
{
	@date_default_timezone_get();
	
	if (function_exists('error_get_last') and 
		$arrerr = error_get_last() and 
		substr($arrerr['message'], 0, strlen('date_default_timezone_get')) == 'date_default_timezone_get')
	{
		// Ooops, they never set their time-zone and PHP is warning them about 
		// 	this! Let's try to auto-set their timezone, and set a flag so that 
		// 	they can find out what's going wrong later.  
		
		/**
		 * 
		 */
		define('QUICKBOOKS_TIMEZONE_AUTOSET', true);
		
		date_default_timezone_set('America/New_York');
	}
}

if (!defined('QUICKBOOKS_TIMEZONE_AUTOSET'))
{
	/**
	 * 
	 */
	define('QUICKBOOKS_TIMEZONE_AUTOSET', false);
}

/**
 * The package author
 * @var string
 */
define('QUICKBOOKS_PACKAGE_AUTHOR', '"Keith Palmer" <keith@consolibyte.com>');

/**
 * The URL for the package
 * @var string
 */
define('QUICKBOOKS_PACKAGE_WEBSITE', 'http://www.consolibyte.com/');

/**
 * The name of the package
 * @var string
 */
define('QUICKBOOKS_PACKAGE_NAME', 'QuickBook PHP Connector');

/**
 * The version of this QuickBooks package 
 * @var string
 */
define('QUICKBOOKS_PACKAGE_VERSION', '1.5.3');

/**
 * Backwards compat.
 * @var string
 */
define('QUICKBOOKS_VERSION', QUICKBOOKS_PACKAGE_VERSION);

if (!defined('QUICKBOOKS_CRLF'))
{
	/**
	 * The carraige-return line-feed sequence to use
	 * @var string
	 */
	define('QUICKBOOKS_CRLF', "\r\n");
}

if (!defined('QUICKBOOKS_SALT'))
{
	/**
	 * Salt value for hashing passwords
	 * @var string
	 */
	define('QUICKBOOKS_SALT', 'andB@++3ry');
}

if (!defined('QUICKBOOKS_HASH'))
{
	/**
	 * The name of a function to use for hashing passwords
	 * @var string
	 */
	define('QUICKBOOKS_HASH', 'sha1');
}

if (!defined('QUICKBOOKS_TIMEOUT'))
{
	/**
	 * The number of seconds without any activity a session can stay open before automatically closed
	 * @var integer
	 */
	define('QUICKBOOKS_TIMEOUT', 1800);
}

if (!defined('QUICKBOOKS_WSDL'))
{
	/**
	 * Path to the QuickBooks WSDL file (the default WSDL is included with this package, you shouldn't need to override this generally) 
	 * @var string
	 */
	define('QUICKBOOKS_WSDL', dirname(__FILE__) . '/QuickBooks/QBWebConnectorSvc.wsdl');
}

if (!defined('QUICKBOOKS_DEBUG'))
{
	/**
	 * Whether or not to turn on debugging (unsupported for now...?)
	 * @var boolean
	 */
	define('QUICKBOOKS_DEBUG', true);
}

if (!defined('QUICKBOOKS_LOG'))
{
	/**
	 * Debug log (unsupported?)
	 * 
	 * @deprecated
	 * @var string
	 */
	define('QUICKBOOKS_LOG', '/tmp/qb-debug.log');
}

/**
 * Developer logging (this is designed just for developing, you probably don't want to use this debug level...)
 * @var integer
 */
define('QUICKBOOKS_LOG_DEVELOP', 4);

/**
 * Debug logging (too much data is logged... generally only useful for debugging)
 * @var integer
 */
define('QUICKBOOKS_LOG_DEBUG', 3);

/**
 * Verbose logging (lots of data is logged)
 * @var integer
 */
define('QUICKBOOKS_LOG_VERBOSE', 2);

/**
 * Normal logging (minimal data is logged)
 * @var integer
 */
define('QUICKBOOKS_LOG_NORMAL', 1);

/**
 * No logging at all (you probably should not use this...)
 * @var integer
 */
define('QUICKBOOKS_LOG_NONE', 0);

define('QUICKBOOKS_SERVICE_DESKTOP', 'Desktop');
define('QUICKBOOKS_SERVICE_DESKTOP_EDITION', QUICKBOOKS_SERVICE_DESKTOP);

define('QUICKBOOKS_SERVICE_QBOE', 'QBOE');
define('QUICKBOOKS_SERVICE_ONLINE_EDITION', QUICKBOOKS_SERVICE_QBOE);

define('QUICKBOOKS_SERVICE_QBMS', 'QBMS');
define('QUICKBOOKS_SERVICE_MERCHANT_SERVICES', QUICKBOOKS_SERVICE_QBMS);

/**
 * 
 */
define('QUICKBOOKS_TYPE_QBFS', 'QBFS');

/**
 * 
 */
define('QUICKBOOKS_TYPE_QBPOS', 'QBPOS');

define('QUICKBOOKS_DATATYPE_STRING', 'STRTYPE');
define('QUICKBOOKS_DATATYPE_ID', 'IDTYPE');
define('QUICKBOOKS_DATATYPE_FLOAT', 'AMTTYPE');
define('QUICKBOOKS_DATATYPE_BOOLEAN', 'BOOLTYPE');
define('QUICKBOOKS_DATATYPE_INTEGER', 'INTTYPE');
define('QUICKBOOKS_DATATYPE_DATE', 'DATETYPE');
define('QUICKBOOKS_DATATYPE_ENUM', 'ENUMTYPE');
define('QUICKBOOKS_DATATYPE_DATETIME', 'DATETIMETYPE');

define('QUICKBOOKS_SUPPORTED_DEFAULT', '');
define('QUICKBOOKS_SUPPORTED_ALL', '0x0');
define('QUICKBOOKS_SUPPORTED_SIMPLESTART', '0x1'); 
define('QUICKBOOKS_SUPPORTED_PRO', '0x2'); 
define('QUICKBOOKS_SUPPORTED_PREMIER', '0x4'); 
define('QUICKBOOKS_SUPPORTED_ENTERPRISE', '0x8');

define('QUICKBOOKS_PERSONALDATA_DEFAULT', '');
define('QUICKBOOKS_PERSONALDATA_NOTNEEDED', 'pdpNotNeeded');
define('QUICKBOOKS_PERSONALDATA_OPTIONAL', 'pdpOptional');
define('QUICKBOOKS_PERSONALDATA_REQUIRED', 'pdpRequired');

define('QUICKBOOKS_UNATTENDEDMODE_DEFAULT', '');
define('QUICKBOOKS_UNATTENDEDMODE_REQUIRED', 'umpRequired');
define('QUICKBOOKS_UNATTENDEDMODE_OPTIONAL', 'umpOptional');

define('QUICKBOOKS_LOCALE_UNITED_STATES', 'US');
define('QUICKBOOKS_LOCALE_US', QUICKBOOKS_LOCALE_UNITED_STATES);

define('QUICKBOOKS_LOCALE_CANADA', 'CA');
define('QUICKBOOKS_LOCALE_CA', QUICKBOOKS_LOCALE_CANADA);

define('QUICKBOOKS_LOCALE_UNITED_KINGDOM', 'UK');
define('QUICKBOOKS_LOCALE_UK', QUICKBOOKS_LOCALE_UNITED_KINGDOM);

/**
 * Use the PHP SoapServer ext/soap PHP extension
 */
define('QUICKBOOKS_SOAPSERVER_PHP', 'php');

/**
 * Use the PEAR {@link http://pear.php.net/} SOAP package
 */
define('QUICKBOOKS_SOAPSERVER_PEAR', 'pear');

/**
 * Use the nuSOAP package
 */
define('QUICKBOOKS_SOAPSERVER_NUSOAP', 'nusoap');

/**
 * Use the built-in pure PHP SOAP server
 */
define('QUICKBOOKS_SOAPSERVER_BUILTIN', 'builtin');

/**
 * Use the PHP ext/soap SOAP client
 */
define('QUICKBOOKS_SOAPCLIENT_PHP', QUICKBOOKS_SOAPSERVER_PHP);

/**
 * Use the PEAR SOAP client
 */
define('QUICKBOOKS_SOAPCLIENT_PEAR', QUICKBOOKS_SOAPSERVER_PEAR);

/**
 * Use the nuSOAP SOAP client
 */
define('QUICKBOOKS_SOAPCLIENT_NUSOAP', QUICKBOOKS_SOAPSERVER_NUSOAP);

/**
 * Use the built-in SOAP client
 */
define('QUICKBOOKS_SOAPCLIENT_BUILTIN', QUICKBOOKS_SOAPSERVER_BUILTIN);

/**
 * QuickBooks flag to request to enter "Interactive Mode"
 * 
 * *** DO NOT CHANGE THIS *** This is a required QuickBooks-defined constant that is neccessary for interactive mode requests
 * 
 * @var string
 */
define('QUICKBOOKS_INTERACTIVE_MODE', 'Interactive mode');

/**
 * 
 */
define('QUICKBOOKS_OBJECT_NULL', 'Null');

/**
 * 
 */
define('QUICKBOOKS_NOOP', 'NoOp');

// This is temporary, eventually we should implement an actual in-handler skip method
define('QUICKBOOKS_SKIP', 'NoOp');

define('QUICKBOOKS_ADD', 'Add');
define('QUICKBOOKS_MOD', 'Mod');
define('QUICKBOOKS_QUERY', 'Query');
define('QUICKBOOKS_DELETE', 'Delete');

define('QUICKBOOKS_OBJECT_ACCOUNT', 'Account');
define('QUICKBOOKS_ADD_ACCOUNT', 'AccountAdd');
define('QUICKBOOKS_MOD_ACCOUNT', 'AccountMod');
define('QUICKBOOKS_QUERY_ACCOUNT', 'AccountQuery');
define('QUICKBOOKS_IMPORT_ACCOUNT', 'AccountImport');

define('QUICKBOOKS_OBJECT_BILL', 'Bill');
define('QUICKBOOKS_ADD_BILL', 'BillAdd');
define('QUICKBOOKS_MOD_BILL', 'BillMod');
define('QUICKBOOKS_QUERY_BILL', 'BillQuery');
define('QUICKBOOKS_IMPORT_BILL', 'BillImport');

define('QUICKBOOKS_OBJECT_BILLINGRATE', 'BillingRate');
define('QUICKBOOKS_ADD_BILLINGRATE', 'BillingRateAdd');
define('QUICKBOOKS_QUERY_BILLINGRATE', 'BillingRateQuery');
define('QUICKBOOKS_IMPORT_BILLINGRATE', 'BillingRateImport');

define('QUICKBOOKS_OBJECT_BILLTOPAY', 'BillToPay');
define('QUICKBOOKS_QUERY_BILLTOPAY', 'BillToPayQuery');
define('QUICKBOOKS_IMPORT_BILLTOPAY', 'BillToPayImport');

define('QUICKBOOKS_OBJECT_BILLPAYMENTCHECK', 'BillPaymentCheck');
define('QUICKBOOKS_ADD_BILLPAYMENTCHECK', 'BillPaymentCheckAdd');
define('QUICKBOOKS_MOD_BILLPAYMENTCHECK', 'BillPaymentCheckMod');
define('QUICKBOOKS_QUERY_BILLPAYMENTCHECK', 'BillPaymentCheckQuery');
define('QUICKBOOKS_IMPORT_BILLPAYMENTCHECK', 'BillPaymentCheckImport');

define('QUICKBOOKS_OBJECT_BILLPAYMENTCREDITCARD', 'BillPaymentCreditCard');
define('QUICKBOOKS_ADD_BILLPAYMENTCREDITCARD', 'BillPaymentCreditCardAdd');
//define('QUICKBOOKS_MOD_BILLPAYMENTCREDITCARD', 'BillPaymentCreditCardMod');	// Not supported by QuickBooks SDK
define('QUICKBOOKS_QUERY_BILLPAYMENTCREDITCARD', 'BillPaymentCreditCardQuery');
define('QUICKBOOKS_IMPORT_BILLPAYMENTCREDITCARD', 'BillPaymentCreditCardImport');

define('QUICKBOOKS_OBJECT_CHARGE', 'Charge');
define('QUICKBOOKS_ADD_CHARGE', 'ChargeAdd');
define('QUICKBOOKS_MOD_CHARGE', 'ChargeMod');
define('QUICKBOOKS_QUERY_CHARGE', 'ChargeQuery');
define('QUICKBOOKS_IMPORT_CHARGE', 'ChargeImport');

define('QUICKBOOKS_OBJECT_CHECK', 'Check');
define('QUICKBOOKS_ADD_CHECK', 'CheckAdd');
define('QUICKBOOKS_MOD_CHECK', 'CheckMod');
define('QUICKBOOKS_QUERY_CHECK', 'CheckQuery');
define('QUICKBOOKS_IMPORT_CHECK', 'CheckImport');

define('QUICKBOOKS_OBJECT_CLASS', 'Class');
define('QUICKBOOKS_ADD_CLASS', 'ClassAdd');
define('QUICKBOOKS_QUERY_CLASS', 'ClassQuery');
define('QUICKBOOKS_IMPORT_CLASS', 'ClassImport');

define('QUICKBOOKS_OBJECT_COMPANY', 'Company');


/**
 * QuickBooks company object (company file meta-data)
 * @var string
 */
define('QUICKBOOKS_OBJECT_CUSTOMER', 'Customer');

/**
 * QuickBooks request to query a company for meta-data
 * @var string
 */
define('QUICKBOOKS_QUERY_COMPANY', 'CompanyQuery');

define('QUICKBOOKS_OBJECT_CREDITCARDCREDIT', 'CreditCardCredit');
define('QUICKBOOKS_ADD_CREDITCARDCREDIT', 'CreditCardCreditAdd');
define('QUICKBOOKS_MOD_CREDITCARDCREDIT', 'CreditCardCreditMod');
define('QUICKBOOKS_QUERY_CREDITCARDCREDIT', 'CreditCardCreditQuery');
define('QUICKBOOKS_IMPORT_CREDITCARDCREDIT', 'CreditCardCreditImport');

define('QUICKBOOKS_OBJECT_CREDITCARDCHARGE', 'CreditCardCharge');
define('QUICKBOOKS_ADD_CREDITCARDCHARGE', 'CreditCardChargeAdd');
define('QUICKBOOKS_MOD_CREDITCARDCHARGE', 'CreditCardChargeMod');
define('QUICKBOOKS_QUERY_CREDITCARDCHARGE', 'CreditCardChargeQuery');
define('QUICKBOOKS_IMPORT_CREDITCARDCHARGE', 'CreditCardChargeImport');

define('QUICKBOOKS_OBJECT_CREDITCARDMEMO', 'CreditCardMemo');
define('QUICKBOOKS_ADD_CREDITCARDMEMO', 'CreditCardMemoAdd');
define('QUICKBOOKS_MOD_CREDITCARDMEMO', 'CreditCardMemoMod');
define('QUICKBOOKS_QUERY_CREDITCARDMEMO', 'CreditCardMemoQuery');

define('QUICKBOOKS_OBJECT_CREDITMEMO', 'CreditMemo');
define('QUICKBOOKS_ADD_CREDITMEMO', 'CreditMemoAdd');
define('QUICKBOOKS_MOD_CREDITMEMO', 'CreditMemoMod');
define('QUICKBOOKS_QUERY_CREDITMEMO', 'CreditMemoQuery');
define('QUICKBOOKS_IMPORT_CREDITMEMO', 'CreditMemoImport');

/**
 * QuickBooks request to add a customer record
 * @var string
 */
define('QUICKBOOKS_ADD_CUSTOMER', 'CustomerAdd');

/**
 * QuickBooks request to modify a customer record
 * @var string
 */
define('QUICKBOOKS_MOD_CUSTOMER', 'CustomerMod');

/**
 * QuickBooks request to search for/query for customer records
 * @var string
 */
define('QUICKBOOKS_QUERY_CUSTOMER', 'CustomerQuery');
define('QUICKBOOKS_IMPORT_CUSTOMER', 'CustomerImport');

define('QUICKBOOKS_OBJECT_CUSTOMERMSG', 'CustomerMsg');
define('QUICKBOOKS_ADD_CUSTOMERMSG', 'CustomerMsgAdd');
define('QUICKBOOKS_QUERY_CUSTOMERMSG', 'CustomerMsgQuery');
define('QUICKBOOKS_IMPORT_CUSTOMERMSG', 'CustomerMsgImport');

define('QUICKBOOKS_OBJECT_CUSTOMERTYPE', 'CustomerType');
define('QUICKBOOKS_ADD_CUSTOMERTYPE', 'CustomerTypeAdd');
define('QUICKBOOKS_QUERY_CUSTOMERTYPE', 'CustomerTypeQuery');

define('QUICKBOOKS_OBJECT_DATAEXT', 'DataExt');
define('QUICKBOOKS_ADD_DATAEXT', 'DataExtAdd');
define('QUICKBOOKS_MOD_DATAEXT', 'DataExtMod');
define('QUICKBOOKS_DEL_DATAEXT', 'DataExtDel');
define('QUICKBOOKS_DELETE_DATAEXT', QUICKBOOKS_DEL_DATAEXT);

define('QUICKBOOKS_OBJECT_DATAEXTDEF', 'DataExtDef');
define('QUICKBOOKS_ADD_DATAEXTDEF', 'DataExtDefAdd');
define('QUICKBOOKS_MOD_DATAEXTDEF', 'DataExtDefMod');
define('QUICKBOOKS_DEL_DATAEXTDEF', 'DataExtDefDel');
define('QUICKBOOKS_DELETE_DATAEXTDEF', QUICKBOOKS_DEL_DATAEXTDEF);
define('QUICKBOOKS_QUERY_DATAEXTDEF', 'DataExtDefQuery');

define('QUICKBOOKS_OBJECT_DATEDRIVENTERMS', 'DateDrivenTerms');
define('QUICKBOOKS_ADD_DATEDRIVENTERMS', 'DateDrivenTermsAdd');
define('QUICKBOOKS_QUERY_DATEDRIVENTERMS', 'DateDrivenTermsQuery');
define('QUICKBOOKS_IMPORT_DATEDRIVENTERMS', 'DateDrivenTermsImport');

/**
 * Query QuickBooks for lists of deleted list items (customers, items, etc.)
 */
define('QUICKBOOKS_QUERY_DELETEDLISTS', 'ListDeletedQuery');

/**
 * @deprecated
 */
define('QUICKBOOKS_QUERY_DELETEDITEMS', QUICKBOOKS_QUERY_DELETEDLISTS);

/**
 * Query QuickBooks for lists of deleted transactions (payments, invoices, estimates, etc.)
 */
define('QUICKBOOKS_QUERY_DELETEDTXNS', 'TxnDeletedQuery');

/**
 * Alias of QUICKBOOKS_QUERY_DELETEDTXNS
 */
define('QUICKBOOKS_QUERY_DELETEDTRANSACTIONS', QUICKBOOKS_QUERY_DELETEDTXNS);

define('QUICKBOOKS_OBJECT_DEPOSIT', 'Deposit');
define('QUICKBOOKS_ADD_DEPOSIT', 'DepositAdd');
define('QUICKBOOKS_MOD_DEPOSIT', 'DepositMod');
define('QUICKBOOKS_QUERY_DEPOSIT', 'DepositQuery');
define('QUICKBOOKS_IMPORT_DEPOSIT', 'DepositImport');

define('QUICKBOOKS_OBJECT_EMPLOYEE', 'Employee');
define('QUICKBOOKS_ADD_EMPLOYEE', 'EmployeeAdd');
define('QUICKBOOKS_MOD_EMPLOYEE', 'EmployeeMod');
define('QUICKBOOKS_QUERY_EMPLOYEE', 'EmployeeQuery');
define('QUICKBOOKS_IMPORT_EMPLOYEE', 'EmployeeImport');

define('QUICKBOOKS_OBJECT_ESTIMATE', 'Estimate');
define('QUICKBOOKS_ADD_ESTIMATE', 'EstimateAdd');
define('QUICKBOOKS_MOD_ESTIMATE', 'EstimateMod');
define('QUICKBOOKS_QUERY_ESTIMATE', 'EstimateQuery');
define('QUICKBOOKS_IMPORT_ESTIMATE', 'EstimateImport');

define('QUICKBOOKS_OBJECT_INVENTORYADJUSTMENT', 'InventoryAdjustment');
define('QUICKBOOKS_ADD_INVENTORYADJUSTMENT', 'InventoryAdjustmentAdd');
define('QUICKBOOKS_QUERY_INVENTORYADJUSTMENT', 'InventoryAdjustmentQuery');
define('QUICKBOOKS_IMPORT_INVENTORYADJUSTMENT', 'InventoryAdjustmentImport');

/**
 * Job constant in QuickBooks
 * 
 * In actuality, there are no such thing as "Jobs" in QuickBooks. Jobs in 
 * QuickBooks are handled as customers with parent customers. 
 * 
 * @var string
 */
define('QUICKBOOKS_OBJECT_JOB', 'Job');
define('QUICKBOOKS_ADD_JOB', 'JobAdd');
define('QUICKBOOKS_MOD_JOB', 'JobMod');
define('QUICKBOOKS_QUERY_JOB', 'JobQuery');

define('QUICKBOOKS_OBJECT_ITEM', 'Item');
define('QUICKBOOKS_QUERY_ITEM', 'ItemQuery');
define('QUICKBOOKS_IMPORT_ITEM', 'ItemImport');

define('QUICKBOOKS_OBJECT_INVENTORYITEM', 'ItemInventory');
define('QUICKBOOKS_ADD_INVENTORYITEM', 'ItemInventoryAdd');
define('QUICKBOOKS_MOD_INVENTORYITEM', 'ItemInventoryMod');
define('QUICKBOOKS_QUERY_INVENTORYITEM', 'ItemInventoryQuery');
define('QUICKBOOKS_IMPORT_INVENTORYITEM', 'ItemInventoryImport');

define('QUICKBOOKS_OBJECT_INVENTORYASSEMBLYITEM', 'ItemInventoryAssembly');
define('QUICKBOOKS_ADD_INVENTORYASSEMBLYITEM', 'ItemInventoryAssemblyAdd');
define('QUICKBOOKS_MOD_INVENTORYASSEMBLYITEM', 'ItemInventoryAssemblyMod');
define('QUICKBOOKS_QUERY_INVENTORYASSEMBLYITEM', 'ItemInventoryAssemblyQuery');
define('QUICKBOOKS_IMPORT_INVENTORYASSEMBLYITEM', 'ItemInventoryAssemblyImport');

define('QUICKBOOKS_OBJECT_GROUPITEM', 'ItemGroup');
define('QUICKBOOKS_ADD_GROUPITEM', 'ItemGroupAdd');
define('QUICKBOOKS_MOD_GROUPITEM', 'ItemGroupMod');
define('QUICKBOOKS_QUERY_GROUPITEM', 'ItemGroupQuery');
define('QUICKBOOKS_IMPORT_GROUPITEM', 'ItemGroupImport');

define('QUICKBOOKS_OBJECT_NONINVENTORYITEM', 'ItemNonInventory');
define('QUICKBOOKS_ADD_NONINVENTORYITEM', 'ItemNonInventoryAdd');
define('QUICKBOOKS_MOD_NONINVENTORYITEM', 'ItemNonInventoryMod');
define('QUICKBOOKS_QUERY_NONINVENTORYITEM', 'ItemNonInventoryQuery');
define('QUICKBOOKS_IMPORT_NONINVENTORYITEM', 'ItemNonInventoryImport');

define('QUICKBOOKS_OBJECT_DISCOUNTITEM', 'ItemDiscount');
define('QUICKBOOKS_ADD_DISCOUNTITEM', 'ItemDiscountAdd');
define('QUICKBOOKS_MOD_DISCOUNTITEM', 'ItemDiscountMod');
define('QUICKBOOKS_QUERY_DISCOUNTITEM', 'ItemDiscountQuery');
define('QUICKBOOKS_IMPORT_DISCOUNTITEM', 'ItemDiscountImport');

define('QUICKBOOKS_OBJECT_FIXEDASSETITEM', 'ItemFixedAsset');
define('QUICKBOOKS_ADD_FIXEDASSETITEM', 'ItemFixedAssetAdd');
define('QUICKBOOKS_MOD_FIXEDASSETITEM', 'ItemFixedAssetMod');
define('QUICKBOOKS_QUERY_FIXEDASSETITEM', 'ItemFixedAssetQuery');

define('QUICKBOOKS_OBJECT_PAYMENTITEM', 'ItemPayment');
define('QUICKBOOKS_ADD_PAYMENTITEM', 'ItemPaymentAdd');
define('QUICKBOOKS_MOD_PAYMENTITEM', 'ItemPaymentMod');
define('QUICKBOOKS_QUERY_PAYMENTITEM', 'ItemPaymentQuery');

define('QUICKBOOKS_OBJECT_PAYROLLITEMWAGE', 'ItemPayrollWage');
define('QUICKBOOKS_ADD_PAYROLLITEMWAGE', 'ItemPayrollWageAdd');
define('QUICKBOOKS_MOD_PAYROLLITEMWAGE', 'ItemPayrollWageMod');
define('QUICKBOOKS_QUERY_PAYROLLITEMWAGE', 'ItemPayrollWageQuery');

define('QUICKBOOKS_OBJECT_PAYROLLITEMNONWAGE', 'ItemPayrollNonWage');
define('QUICKBOOKS_ADD_PAYROLLITEMNONWAGE', 'ItemPayrollNonWageAdd');
define('QUICKBOOKS_MOD_PAYROLLITEMNONWAGE', 'ItemPayrollNonWageMod');
define('QUICKBOOKS_QUERY_PAYROLLITEMNONWAGE', 'ItemPayrollNonWageQuery');

define('QUICKBOOKS_OBJECT_SERVICEITEM', 'ItemService');
define('QUICKBOOKS_ADD_SERVICEITEM', 'ItemServiceAdd');
define('QUICKBOOKS_MOD_SERVICEITEM', 'ItemServiceMod');
define('QUICKBOOKS_QUERY_SERVICEITEM', 'ItemServiceQuery');

define('QUICKBOOKS_OBJECT_SALESTAXITEM', 'ItemSalesTax');
define('QUICKBOOKS_ADD_SALESTAXITEM', 'ItemSalesTaxAdd');
define('QUICKBOOKS_MOD_SALESTAXITEM', 'ItemSalesTaxMod');
define('QUICKBOOKS_QUERY_SALESTAXITEM', 'ItemSalesTaxQuery');

define('QUICKBOOKS_OBJECT_OTHERCHARGEITEM', 'ItemOtherCharge');
define('QUICKBOOKS_ADD_OTHERCHARGEITEM', 'ItemOtherChargeAdd');
define('QUICKBOOKS_MOD_OTHERCHARGEITEM', 'ItemOtherChargeMod');
define('QUICKBOOKS_QUERY_OTHERCHARGEITEM', 'ItemOtherChargeQuery');

define('QUICKBOOKS_OBJECT_RECEIPTITEM', 'ItemReceipt');
define('QUICKBOOKS_ADD_RECEIPTITEM', 'ItemReceiptAdd');
define('QUICKBOOKS_MOD_RECEIPTITEM', 'ItemReceiptMod');
define('QUICKBOOKS_QUERY_RECEIPTITEM', 'ItemReceiptQuery');

define('QUICKBOOKS_OBJECT_SUBTOTALITEM', 'ItemSubtotal');
define('QUICKBOOKS_ADD_SUBTOTALITEM', 'ItemSubtotalAdd');
define('QUICKBOOKS_MOD_SUBTOTALITEM', 'ItemSubtotalMod');
define('QUICKBOOKS_QUERY_SUBTOTALITEM', 'ItemSubtotalQuery');

define('QUICKBOOKS_OBJECT_JOBTYPE', 'JobType');
define('QUICKBOOKS_ADD_JOBTYPE', 'JobTypeAdd');
define('QUICKBOOKS_QUERY_JOBTYPE', 'JobTypeQuery');

define('QUICKBOOKS_OBJECT_JOURNALENTRY', 'JournalEntry');
define('QUICKBOOKS_ADD_JOURNALENTRY', 'JournalEntryAdd');
define('QUICKBOOKS_MOD_JOURNALENTRY', 'JournalEntryMod');
define('QUICKBOOKS_QUERY_JOURNALENTRY', 'JournalEntryQuery');

define('QUICKBOOKS_OBJECT_INVOICE', 'Invoice');

/**
 * QuickBooks request to create an invoice
 * @var string
 */
define('QUICKBOOKS_ADD_INVOICE', 'InvoiceAdd');

/**
 * QuickBooks request to modify an invoice
 * @var string
 */
define('QUICKBOOKS_MOD_INVOICE', 'InvoiceMod');

/**
 * QuickBooks request to run a query for invoices
 * @var string
 */
define('QUICKBOOKS_QUERY_INVOICE', 'InvoiceQuery');
define('QUICKBOOKS_IMPORT_INVOICE', 'InvoiceImport');

define('QUICKBOOKS_OBJECT_RECEIVEPAYMENT', 'ReceivePayment');

/**
 * QuickBooks request to register a payment as received
 * @var string
 */
define('QUICKBOOKS_ADD_RECEIVEPAYMENT', 'ReceivePaymentAdd');
define('QUICKBOOKS_MOD_RECEIVEPAYMENT', 'ReceivePaymentMod');
define('QUICKBOOKS_QUERY_RECEIVEPAYMENT', 'ReceivePaymentQuery');
define('QUICKBOOKS_IMPORT_RECEIVEPAYMENT', 'ReceivePaymentImport');

define('QUICKBOOKS_ADD_RECEIVE_PAYMENT', QUICKBOOKS_ADD_RECEIVEPAYMENT);
define('QUICKBOOKS_MOD_RECEIVE_PAYMENT', QUICKBOOKS_MOD_RECEIVEPAYMENT);
define('QUICKBOOKS_QUERY_RECEIVE_PAYMENT', QUICKBOOKS_QUERY_RECEIVEPAYMENT);
define('QUICKBOOKS_IMPORT_RECEIVE_PAYMENT', QUICKBOOKS_IMPORT_RECEIVEPAYMENT);

define('QUICKBOOKS_OBJECT_PAYMENTMETHOD', 'PaymentMethod');
define('QUICKBOOKS_ADD_PAYMENTMETHOD', 'PaymentMethodAdd');
define('QUICKBOOKS_QUERY_PAYMENTMETHOD', 'PaymentMethodQuery');
define('QUICKBOOKS_IMPORT_PAYMENTMETHOD', 'PaymentMethodImport');

define('QUICKBOOKS_OBJECT_PRICELEVEL', 'PriceLevel');
define('QUICKBOOKS_ADD_PRICELEVEL', 'PriceLevelAdd');
define('QUICKBOOKS_MOD_PRICELEVEL', 'PriceLevelMod');
define('QUICKBOOKS_QUERY_PRICELEVEL', 'PriceLevelQuery');

define('QUICKBOOKS_OBJECT_PURCHASEORDER', 'PurchaseOrder');
define('QUICKBOOKS_ADD_PURCHASEORDER', 'PurchaseOrderAdd');
define('QUICKBOOKS_MOD_PURCHASEORDER', 'PurchaseOrderMod');
define('QUICKBOOKS_QUERY_PURCHASEORDER', 'PurchaseOrderQuery');
define('QUICKBOOKS_IMPORT_PURCHASEORDER', 'PurchaseOrderImport');

define('QUICKBOOKS_ADD_PURCHASE_ORDER', QUICKBOOKS_ADD_PURCHASEORDER);
define('QUICKBOOKS_MOD_PURCHASE_ORDER', QUICKBOOKS_MOD_PURCHASEORDER);
define('QUICKBOOKS_QUERY_PURCHASE_ORDER', QUICKBOOKS_QUERY_PURCHASEORDER);
define('QUICKBOOKS_IMPORT_PURCHASE_ORDER', QUICKBOOKS_IMPORT_PURCHASEORDER);

define('QUICKBOOKS_OBJECT_SALESORDER', 'SalesOrder');
define('QUICKBOOKS_ADD_SALESORDER', 'SalesOrderAdd');
define('QUICKBOOKS_MOD_SALESORDER', 'SalesOrderMod');
define('QUICKBOOKS_QUERY_SALESORDER', 'SalesOrderQuery');
define('QUICKBOOKS_IMPORT_SALESORDER', 'SalesOrderImport');

define('QUICKBOOKS_OBJECT_SALESRECEIPT', 'SalesReceipt');
define('QUICKBOOKS_ADD_SALESRECEIPT', 'SalesReceiptAdd');
define('QUICKBOOKS_MOD_SALESRECEIPT', 'SalesReceiptMod');
define('QUICKBOOKS_QUERY_SALESRECEIPT', 'SalesReceiptQuery');
define('QUICKBOOKS_IMPORT_SALESRECEIPT', 'SalesReceiptImport');

define('QUICKBOOKS_OBJECT_SALESREP', 'SalesRep');
define('QUICKBOOKS_ADD_SALESREP', 'SalesRepAdd');
define('QUICKBOOKS_MOD_SALESREP', 'SalesRepMod');
define('QUICKBOOKS_QUERY_SALESREP', 'SalesRepQuery');

define('QUICKBOOKS_OBJECT_SALESTAXCODE', 'SalesTaxCode');
define('QUICKBOOKS_ADD_SALESTAXCODE', 'SalesTaxCodeAdd');
define('QUICKBOOKS_QUERY_SALESTAXCODE', 'SalesTaxCodeQuery');

define('QUICKBOOKS_OBJECT_SHIPMETHOD', 'ShipMethod');
define('QUICKBOOKS_ADD_SHIPMETHOD', 'ShipMethodAdd');
define('QUICKBOOKS_QUERY_SHIPMETHOD', 'ShipMethodQuery');

define('QUICKBOOKS_OBJECT_SPECIALACCOUNT', 'SpecialAccount');
define('QUICKBOOKS_ADD_SPECIALACCOUNT', 'SpecialAccountAdd');

define('QUICKBOOKS_OBJECT_SPECIALITEM', 'SpecialItem');
define('QUICKBOOKS_ADD_SPECIALITEM', 'SpecialItemAdd');

define('QUICKBOOKS_OBJECT_STANDARDTERMS', 'StandardTerms');
define('QUICKBOOKS_ADD_STANDARDTERMS', 'StandardTermsAdd');
define('QUICKBOOKS_QUERY_STANDARDTERMS', 'StandardTermsQuery');

define('QUICKBOOKS_OBJECT_TEMPLATE', 'Template');
define('QUICKBOOKS_QUERY_TEMPLATE', 'TemplateQuery');

define('QUICKBOOKS_OBJECT_TERMS', 'Terms');
define('QUICKBOOKS_QUERY_TERMS', 'TermsQuery');

define('QUICKBOOKS_DELETE_OBJECT', 'ListDel');
define('QUICKBOOKS_DEL_OBJECT', QUICKBOOKS_DELETE_OBJECT);
define('QUICKBOOKS_DEL_LIST', QUICKBOOKS_DELETE_OBJECT);
define('QUICKBOOKS_DELETE_LIST', QUICKBOOKS_DELETE_OBJECT);

define('QUICKBOOKS_OBJECT_OBJECT', 'Object');

/**
 * 
 */
define('QUICKBOOKS_OBJECT_TIMETRACKING', 'TimeTracking');
define('QUICKBOOKS_ADD_TIMETRACKING','TimeTrackingAdd');
define('QUICKBOOKS_MOD_TIMETRACKING','TimeTrackingMod');
define('QUICKBOOKS_QUERY_TIMETRACKING','TimeTrackingQuery');

define('QUICKBOOKS_OBJECT_TRANSACTION', 'Transaction');

/**
 * QuickBooks request to delete a transaction
 * @var string
 */
define('QUICKBOOKS_DELETE_TRANSACTION', 'TxnDel');
define('QUICKBOOKS_DEL_TRANSACTION', QUICKBOOKS_DELETE_TRANSACTION);
define('QUICKBOOKS_DEL_TXN', QUICKBOOKS_DELETE_TRANSACTION);
define('QUICKBOOKS_DELETE_TXN', QUICKBOOKS_DELETE_TRANSACTION);

define('QUICKBOOKS_QUERY_TRANSACTION', 'TransactionQuery');
define('QUICKBOOKS_VOID_TRANSACTION', 'TxnVoid');

define('QUICKBOOKS_IMPORT_TRANSACTION', 'TransactionImport');

define('QUICKBOOKS_OBJECT_VEHICLE', 'Vehicle');
define('QUICKBOOKS_ADD_VEHICLE','VehicleAdd');
define('QUICKBOOKS_MOD_VEHICLE','VehicleMod');
define('QUICKBOOKS_QUERY_VEHICLE','VehicleQuery');

define('QUICKBOOKS_OBJECT_VEHICLEMILEAGE', 'VehicleMileage');
define('QUICKBOOKS_ADD_VEHICLEMILEAGE','VehicleMileageAdd');
define('QUICKBOOKS_MOD_VEHICLEMILEAGE','VehicleMileageMod');
define('QUICKBOOKS_QUERY_VEHICLEMILEAGE','VehicleMileageQuery');

define('QUICKBOOKS_OBJECT_VENDOR', 'Vendor');
define('QUICKBOOKS_ADD_VENDOR', 'VendorAdd');
define('QUICKBOOKS_MOD_VENDOR', 'VendorMod');
define('QUICKBOOKS_QUERY_VENDOR', 'VendorQuery');

define('QUICKBOOKS_OBJECT_VENDORCREDIT', 'VendorCredit');
define('QUICKBOOKS_ADD_VENDORCREDIT', 'VendorCreditAdd');
define('QUICKBOOKS_QUERY_VENDORCREDIT', 'VendorCreditQuery');

define('QUICKBOOKS_OBJECT_VENDORTYPE', 'VendorType');
define('QUICKBOOKS_ADD_VENDORTYPE', 'VendorTypeAdd');
define('QUICKBOOKS_QUERY_VENDORTYPE', 'VendorTypeQuery');

define('QUICKBOOKS_OBJECT_WORKERSCOMPCODE', 'WorkersCompCode');
define('QUICKBOOKS_ADD_WORKERSCOMPCODE', 'WorkersCompCodeAdd');
define('QUICKBOOKS_MOD_WORKERSCOMPCODE', 'WorkersCompCodeMod');
define('QUICKBOOKS_QUERY_WORKERSCOMPCODE', 'WorkersCompCodeQuery');

define('QUICKBOOKS_OBJECT_UNITOFMEASURESET', 'UnitOfMeasureSet');
define('QUICKBOOKS_ADD_UNITOFMEASURESET', 'UnitOfMeasureSetAdd');
//define('QUICKBOOKS_MOD_UNITOFMEASURESET', 'UnitOfMeasureSetMod');
define('QUICKBOOKS_QUERY_UNITOFMEASURESET', 'UnitOfMeasureSetQuery');

/**
 * An always-present QuickBooks constant for "TAXABLE" items to embed in "SalesTaxCodeRef FullName" qbXML values 
 * 
 * @var string
 */
define('QUICKBOOKS_TAXABLE', 'TAX');

/**
 * An always-present QuickBooks constant for "NON-TAXABLE" items to embed in "SalesTaxCodeRef FullName" qbXML values
 * 
 * @var string
 */
define('QUICKBOOKS_NONTAXABLE', 'NON');

define('QUICKBOOKS_LISTID', 'ListID');
define('QUICKBOOKS_TXNID', 'TxnID');

define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_ACCOUNTSPAYABLE', 'AccountsPayable');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_ACCOUNTSRECEIVABLE', 'AccountsReceivable');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_BANK', 'Bank');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_COSTOFGOODSSOLD', 'CostOfGoodsSold');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_CREDITCARD', 'CreditCard');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_EQUITY', 'Equity');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_EXPENSE', 'Expense');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_FIXEDASSET', 'FixedAsset');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_INCOME', 'Income');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_LONGTERMLIABILITY', 'LongTermLiability');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_NONPOSTING', 'NonPosting');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_OTHERASSET', 'OtherAsset');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_CURRENTASSET', 'OtherCurrentAsset');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_CURRENTLIABILITY', 'OtherCurrentLiability');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_OTHEREXPENSE', 'OtherExpense');
define('QUICKBOOKS_ACCOUNT_ACCOUNTTYPE_OTHERINCOME', 'OtherIncome');

define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_ACCOUNTSPAYABLE', 'AccountsPayable');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_ACCOUNTSRECEIVABLE', 'AccountsReceivable');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_CONDENSEITEMADJUSTMENTEXPENSES', 'CondenseItemAdjustmentExpenses');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_COSTOFGOODSSOLD', 'CostOfGoodsSold');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_DIRECTDEPOSITLIABILITIES', 'DirectDepositLiabilities');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_ESTIMATES', 'Estimates');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_INVENTORYASSETS', 'InventoryAssets');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_OPENINGBALANCEEQUITY', 'OpeningBalanceEquity');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_PAYROLLEXPENSES', 'PayrollExpenses');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_PAYROLLLIABILITIES', 'PayrollLiabilities');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_PETTYCASH', 'PettyCash');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_PURCHASEORDERS', 'PurchaseOrders');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_RECONCILIATIONDIFFERENCES', 'ReconciliationDifferences');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_RETAINEDEARNINGS', 'RetainedEarnings'); 
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_SALESORDERS', 'SalesOrders');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_SALESTAXPAYABLE', 'SalesTaxPayable');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_UNCATEGORIZEDEXPENSES', 'UncategorizedExpenses'); 
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_UNCATEGORIZEDINCOME', 'UncategorizedIncome');
define('QUICKBOOKS_ACCOUNT_SPECIALACCOUNTTYPE_UNDEPOSITEDFUNDS', 'UndepositedFunds');

define('QUICKBOOKS_CUSTOMER_DELIVERYMETHOD_PRINT', 'Print');
define('QUICKBOOKS_CUSTOMER_DELIVERYMETHOD_FAX', 'Fax');
define('QUICKBOOKS_CUSTOMER_DELIVERYMETHOD_EMAIL', 'Email');

/**
 * Queuing status for queued QuickBooks transactions - QUEUED
 * @var char
 */
define('QUICKBOOKS_STATUS_QUEUED', 'q');

/**
 * QuickBooks status for queued QuickBooks transactions - was queued, then SUCCESSFULLY PROCESSED
 * @var char
 */
define('QUICKBOOKS_STATUS_SUCCESS', 's');

/**
 * QuickBooks status for queued QuickBooks transactions - was queued, an ERROR OCCURED when processing it
 * @var char
 */
define('QUICKBOOKS_STATUS_ERROR', 'e');

/**
 * QuickBooks status for items that have been dequeued and are being processed by QuickBooks (we assume) but we havn't received a response back about them yet
 * @var char
 */
define('QUICKBOOKS_STATUS_PROCESSING', 'i');

/**
 * QuickBooks status for items that were dequeued, had an error occured, and then the error was handled by an error handler
 * @var char
 */
define('QUICKBOOKS_STATUS_HANDLED', 'h');

/**
 * QuickBooks status for items that were cancelled
 * @var char
 */
define('QUICKBOOKS_STATUS_CANCELLED', 'c');

/**
 * QuickBooks status for items that were NoOp
 * @var char
 */
define('QUICKBOOKS_STATUS_NOOP', 'n');

/**
 * Error code for errors that are not really errors... 
 * @var integer
 */
define('QUICKBOOKS_ERROR_OK', 0);

/**
 * Error code for SOAP server errors that occur internally (misc. errors)
 * @var integer
 */
define('QUICKBOOKS_ERROR_INTERNAL', -1);

/**
 * Error code for errors that occur within function handlers
 * @var integer
 */
define('QUICKBOOKS_ERROR_HANDLER', -2);

/**
 * Error code for errors that occur within driver classes
 * @var integer
 */
define('QUICKBOOKS_ERROR_DRIVER', -3);

/**
 * Error code for errors that are reported by hook classes
 * @var integer
 */
define('QUICKBOOKS_ERROR_HOOK', -4);

/**
 * Status for an enabled user
 * @var char
 */
define('QUICKBOOKS_USER_ENABLED', 'e');

/**
 * Status for a disabled user
 * @var char
 */
define('QUICKBOOKS_USER_DISABLED', 'd');

/**
 * Frameworks declarations
 */
require_once 'QuickBooks/Frameworks.php';

// If this constant isn't defined, then include *everything*
if (!defined('QUICKBOOKS_FRAMEWORKS'))
{
	$all = 0;
	
	$constants = get_defined_constants(true);
	foreach ($constants['user'] as $constant => $value)
	{
		if (substr($constant, 0, 21) == 'QUICKBOOKS_FRAMEWORK_')
		{
			$all = $all | $value;
		}
	}
	
	/**
	 * Determines which frameworks are included (if not defined, this defines it to include *everything*)
	 * @var integer
	 */
	define('QUICKBOOKS_FRAMEWORKS', $all);
}

if (QUICKBOOKS_FRAMEWORKS & QUICKBOOKS_FRAMEWORK_QUEUE or 
	QUICKBOOKS_FRAMEWORKS & QUICKBOOKS_FRAMEWORK_WEBCONNECTOR)
{
	/**
	 * Queue class for QuickBooks queueing 
	 */
	require_once 'QuickBooks/Queue.php';
}

if (QUICKBOOKS_FRAMEWORKS & QUICKBOOKS_FRAMEWORK_WEBCONNECTOR)
{
	/**
	 * SOAP server for QuickBooks web services
	 */
	require_once 'QuickBooks/Server.php';
	
	/**
	 * Web Connector generation
	 */
	require_once 'QuickBooks/QWC.php';
	
	/**
	 * Various QuickBooks utility classes
	 */
	require_once 'QuickBooks/Utilities.php';
}

if (QUICKBOOKS_FRAMEWORK_MISCELLANEOUS & QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * SOAP client for QuickBooks testing
	 */
	require_once 'QuickBooks/Client.php';
}

if (QUICKBOOKS_FRAMEWORK_MISCELLANEOUS & QUICKBOOKS_FRAMEWORKS or 
	QUICKBOOKS_FRAMEWORK_ONLINEEDITION & QUICKBOOKS_FRAMEWORKS or 
	QUICKBOOKS_FRAMEWORK_MERCHANTSERVICE & QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * Encryption/decryption classes
	 */
	require_once 'QuickBooks/Encryption/Factory.php';
}

if (QUICKBOOKS_FRAMEWORK_CONSTANTS != QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * Functions for calling callback functions 
	 */
	require_once 'QuickBooks/Callbacks.php';
}

if (QUICKBOOKS_FRAMEWORK_MISCELLANEOUS & QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * Utilities for ensuring values fit into qbXML fields 
	 */
	require_once 'QuickBooks/Cast.php';
	
	/**
	 * Pre-defined hooks to use with QuickBooks components
	 */
	require_once 'QuickBooks/Hook/Factory.php';
}

if (QUICKBOOKS_FRAMEWORK_CONSTANTS != QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * QuickBooks PHP error handler
	 */
	require_once 'QuickBooks/ErrorHandler.php';
}

if (QUICKBOOKS_FRAMEWORK_ONLINEEDITION & QUICKBOOKS_FRAMEWORKS or 
	QUICKBOOKS_FRAMEWORK_WEBCONNECTOR & QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * API class
	 */
	require_once 'QuickBooks/API.php';
}

if (QUICKBOOKS_FRAMEWORK_MERCHANTSERVICE & QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * QuickBooks Merchant Service support
	 */
	require_once 'QuickBooks/MerchantService.php';
}

if (QUICKBOOKS_FRAMEWORK_FRONTEND & QUICKBOOKS_FRAMEWORKS)
{
	/**
	 * Front-end GUI class
	 */
	require_once 'QuickBooks/Frontend.php';
}

if (QUICKBOOKS_FRAMEWORK_WEBCONNECTOR & QUICKBOOKS_FRAMEWORKS)
{
	// Other servers
	QuickBooks_Utilities::import(dirname(__FILE__) . '/QuickBooks/Server');
}

if (QUICKBOOKS_FRAMEWORK_OBJECTS & QUICKBOOKS_FRAMEWORKS)
{
	// Objects for the API
	QuickBooks_Utilities::import(dirname(__FILE__) . '/QuickBooks/Object');
}

if (QUICKBOOKS_FRAMEWORK_INTEGRATORS & QUICKBOOKS_FRAMEWORKS)
{
	// Integrator server classes
	QuickBooks_Utilities::import(dirname(__FILE__) . '/QuickBooks/Server/Integrator');
	
	// Integrator drivers
	QuickBooks_Utilities::import(dirname(__FILE__) . '/QuickBooks/Integrator');
}

