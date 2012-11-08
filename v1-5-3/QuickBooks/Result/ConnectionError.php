<?php

/**
 * Result container object for the SOAP ->connectionError() method call
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
 * 
 * @package QuickBooks
 * @subpackage Server
 */

/**
 * Result interface
 */
require_once 'QuickBooks/Result.php';

/**
 * Result container object for the SOAP ->connectionError() method call
 */
class QuickBooks_Result_ConnectionError extends QuickBooks_Result
{
	/**
	 * An error message
	 * 
	 * @var string
	 */
	public $connectionErrorResult;
	
	/**
	 * Create a new result object
	 * 
	 * @param string $err		An error message describing the problem
	 */
	public function __construct($err)
	{
		$this->connectionErrorResult = $err;
	}
}
