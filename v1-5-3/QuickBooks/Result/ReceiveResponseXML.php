<?php

/**
 * Response result for the SOAP ->receiveRequestXML() method call
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
 *  
 * @package QuickBooks
 * @subpackage Server
 */

/**
 * Result base class
 */
require_once 'QuickBooks/Result.php';

/**
 * Response result for the SOAP ->receiveRequestXML() method call
 */
class QuickBooks_Result_ReceiveResponseXML extends QuickBooks_Result
{
	/**
	 * Integer indicating update progress
	 * 
	 * @var integer
	 */
	public $receiveResponseXMLResult;
	
	/**
	 * Create a new ->receiveResponseXML result object
	 * 
	 * @param integer $complete		An integer between 0 and 100 indicating the percentage complete this update is *OR* a negative integer indicating an error has occured
	 */
	public function __construct($complete)
	{
		$this->receiveResponseXMLResult = $complete;
	}
}
