<?php

ini_set('include_path', ini_get('include_path') . ':' . realpath(dirname(__FILE__) . '/..'));

require_once 'QuickBooks.php';
require_once 'QuickBooks/XML.php';

$xml = file_get_contents('/home/library_php/QuickBooks/data/InvoiceQuery.xml');

$Parser = new QuickBooks_XML($xml);

$errnum = 0;
$errmsg = '';
$Parser->parse($errnum, $errmsg);

//print_R($Parser);

$tmp = $Parser->children();
$base = current($tmp);

$tmp = $base->children();
$rs = next($tmp);

$tmp = $rs->children();
$qbxml = current($tmp);

Transform($qbxml);

function Transform($node)
{
	$name = $node->name();		// table name
	$fields = array();
	$subtables = array();
	
	foreach ($node->children() as $child)
	{
		if (substr($child->name(), -3) == 'Ref')
		{
			// We need to look through the reference, and see if we can find it's unique identifier (is it a ListID? a FullName? a TxnID?)
			
			$fields[substr($child->name(), 0, -3) . '_ListID'] = array(
				'link!', 
				);
		}
		else if ($child->childCount())		// subtable
		{
			$child->setName($node->name() . '_' . $child->name());
			
			$fields[$child->name() . '_qbID'] = array(
				QUICKBOOKS_DRIVER_SQL_INTEGER, 
				);
			
			$child->addChild( new QuickBooks_XML_Node($child->name() . '_qbID') , true);
			
			$subtables[] = $child;
		}
		else
		{
			$fields[$child->name()] = array(
				$child->data()
				);
		}
	}
	
	print('name: ' . $name . "\n");
	print_r($fields);
	print("\n");
	
	foreach ($subtables as $node)
	{
		Transform($node);
	}
}



//_helper($add);

function _helper($node, $lvl = 0)
{
	if ($lvl == 0)		// base table
	{
		print('CREATE TABLE ');
	}
	else if ($lvl == 1)
	{
		$name = $node->name();
		
		if ($node->childCount())
		{
			if (substr($name, -3) == 'Ref')
			{
				print('REFERENCE ');
			}
			else
			{
				print('MAKE TABLE, REFERENCE');
			}
		}
		else
		{
			print('FIELD ');
		}
		
	}
	
	
	print(str_repeat("\t", $lvl) . $node->name() . "\n");
	
	if ($node->childCount())
	{
		foreach ($node->children() as $child)
		{
			_helper($child, $lvl + 1);
		}
	}
	
}

?>