<?php

/**
 * 
 * 
 * @package QuickBooks
 * @subpackage Hook 
 */

require_once 'QuickBooks.php';

require_once 'QuickBooks/Hook.php';

class QuickBooks_Hook_Factory
{
	static public function create($hook, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null, $arg5 = null, $arg6 = null, $arg7 = null, $arg8 = null, $arg9 = null)
	{
		$split = explode('_', $hook);
		foreach ($split as $key => $value)
		{
			$split[$key] = ucfirst(strtolower($value));
		}
		
		$file = 'QuickBooks/Hook/' . implode('/', $split) . '.php';
		$class = 'QuickBooks_Hook_' . $hook;
		
		require_once $file;
		
		return new $class($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9);
	}
}