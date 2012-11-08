<?php

/**
 * 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt
 * 
 * @package QuickBooks
 * @subpackage Server
 */

/**
 * 
 */
require_once 'QuickBooks.php';

/**
 * 
 */
require_once 'QuickBooks/Server/Integrator.php';

/**
 * 
 */
require_once 'QuickBooks/Integrator.php';

/**
 * 
 */
require_once 'QuickBooks/Integrator/Interspire.php';

/**
 * 
 */
class QuickBooks_Server_Integrator_Interspire extends QuickBooks_Server_Integrator
{
	/**
	 * Get the Interspire integrator object for the Interspire integrator server
	 * 
	 * @param string $integrator_dsn_or_conn
	 * @param array $integrator_options
	 * @return QuickBooks_Integrator
	 */
	protected function _integratorFactory($integrator_dsn_or_conn, $integrator_options, $API)
	{
		$driver = QuickBooks_Utilities::driverFactory($integrator_dsn_or_conn);
		return new QuickBooks_Integrator_Interspire($driver, $integrator_options, $API);
	}
}
