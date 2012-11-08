<?php

/**
 * QuickBooks encryption library factory method
 * 
 * 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
 *  
 * @package QuickBooks
 * @subpackage Encryption
 */

/**
 * 
 */
require_once 'QuickBooks.php';

/**
 * 
 */
require_once 'QuickBooks/Encryption.php';

/**
 * 
 * 
 * 
 */
class QuickBooks_Encryption_Factory
{
	static public function create($encrypt, $iv = null, $mode = null)
	{
		$class = 'QuickBooks_Encryption_' . ucfirst(strtolower($encrypt));
		$file = 'QuickBooks/Encryption/' . ucfirst(strtolower($encrypt)) . '.php';
		
		require_once $file;
		
		return new $class();
	}
}
