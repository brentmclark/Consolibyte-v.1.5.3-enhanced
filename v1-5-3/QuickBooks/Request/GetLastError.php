<?php

/**
 * 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
 * 
 * @package QuickBooks
 * @subpackage Client
 */

/**
 * 
 */
require_once 'QuickBooks/Request.php';

/**
 * 
 * 
 * 
 */
class QuickBooks_Request_GetLastError extends QuickBooks_Request
{
	public $ticket;
	
	public function __construct($ticket = null)
	{
		$this->ticket = $ticket;
	}
}
