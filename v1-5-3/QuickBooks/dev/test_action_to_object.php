<?php

require_once '../../QuickBooks.php';

$out = QuickBooks_Utilities::actionToObject('VendorCreditAdd');

print("\n\n" . $out . "\n\n");

//$out = QuickBooks_Utilities::actionToObject('VendorAdd');

//print("\n\n" . $out . "\n\n");

//print_r(QuickBooks_Utilities::listObjects());

?>