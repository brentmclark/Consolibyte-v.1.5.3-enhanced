<?php

/**
 * XML constants (and backward compat. class)
 * 
 * @package QuickBooks
 * @subpackage XML
 */

/**
 * Indicates an error *did not* occur
 * @var integer
 */
define('QUICKBOOKS_XML_ERROR_OK', 0);

/**
 * Alias of QUICKBOOKS_XML_ERROR_OK
 */
define('QUICKBOOKS_XML_OK', QUICKBOOKS_XML_ERROR_OK);

/**
 * Indicates a tag mismatch/bad tag order
 * @var integer
 */
define('QUICKBOOKS_XML_ERROR_MISMATCH', 1);

/**
 * Indicates garbage somewhere in the XML stream 
 * @var integer
 */
define('QUICKBOOKS_XML_ERROR_GARBAGE', 2);

/**
 * Indicates a bad XML entity
 * @var integer
 */
define('QUICKBOOKS_XML_ERROR_ENTITY', 3);

/**
 * Indicates a dangling XML attribute after parsing
 * @var integer
 */
define('QUICKBOOKS_XML_ERROR_DANGLING', 4);

/**
 * <code>
 * $xml = '
 * 	<Person>
 * 		<Name type="firstname">Keith</Name>
 * 	</Person>
 * ';
 * 
 * $arr = array(
 * 	'Person' => array(
 * 		'Name' => 'Keith', 
 * 		), 
 * 	);
 * </code>
 */
define('QUICKBOOKS_XML_ARRAY_NOATTRIBUTES', 'no-attrs');

/**
 * 
 * <code>
 * $arr = array(
 * 	'Person' => array(
 * 		'Name' => 'Keith',
 * 		'Name_type' => 'firstname', 
 * 		), 
 * 	);
 * </code>
 * 
 */
define('QUICKBOOKS_XML_ARRAY_EXPANDATTRIBUTES', 'child-attrs');

/**
 * <code>
 * $arr = array(
 * 	0 => array(
 * 		'name' => 'Person', 
 * 		'attributes' => array( ),
 * 		'children' => array(
 * 			0 => array(
 * 				'name' => 'Name', 
 * 				'attributes' => array( 
 * 					'type' => 'firstname', 
 * 				), 
 * 				'children' => array(  ), 
 * 				'data' => 'Keith', 
 * 			),  
 * 		), 
 * 		'data' => null, 
 * 	), 	
 * );
 * </code>
 */
define('QUICKBOOKS_XML_ARRAY_BRANCHED', 'branched');

/**
 * 
 * <code>
 * $arr = array(
 * 	'Person Name' => 'Keith', 
 * 	);
 * </code>
 * 
 */
define('QUICKBOOKS_XML_ARRAY_PATHS', 'paths');

/**
 * Flag to compress empty XML elements
 * 
 * <Customer>
 * 	<FirstName>Keith</FirstName>
 * 	<LastName />
 * </Customer>
 * 
 * @note Defined as an integer for backwards compat.
 * @var integer 
 */
define('QUICKBOOKS_XML_XML_COMPRESS', 1);

/**
 * Flag to drop empty XML elements
 * 
 * <Customer>
 * 	<FirstName>Keith</FirstName>
 * </Customer>
 * 
 * @note Defined as an integer for backwards compat.
 * @var integer
 */
define('QUICKBOOKS_XML_XML_DROP', -1);

/**
 * Flag to preserve empty elements
 * 
 * <Customer>
 * 	<FirstName>Keith</FirstName>
 * 	<LastName></LastName>
 * </Customer>
 * 
 * @note Defined as an integer for backwards compat.
 * @var integer
 */
define('QUICKBOOKS_XML_XML_PRESERVE', 0);

/**
 * Node class
 */
require_once 'QuickBooks/XML/Node.php';

/**
 * Document class
 */
require_once 'QuickBooks/XML/Document.php';

/**
 * XML parser
 */
require_once 'QuickBooks/XML/Parser.php';

/**
 * This just extends the XML parser for compatability, use QuickBooks_XML_Parser instead!
 */
class QuickBooks_XML extends QuickBooks_XML_Parser
{
	/**
	 * @deprecated		See QuickBooksXML::encode() instead
	 */
	static public function htmlspecialchars($string, $quote_style = ENT_COMPAT, $charset = 'ISO-8859-1', $double_encode = true)
	{
		/*
		if (!$charset)
		{
			$charset = 'ISO-8859-1';
		}
		
		if (version_compare(PHP_VERSION, '5.2.3', '>='))
		{
			return htmlspecialchars($string, $quote_style, $charset, $double_encode);
		}
		else
		{
			$string = htmlspecialchars($string, $quote_style, $charset);
			
			$fix = array(
				'&amp;amp;' => '&amp;', 
				'&amp;quot;' => '&quot;', 
				);
			
			return str_replace(array_keys($fix), array_values($fix), $string);
		}
		*/
		
		return QuickBooks_XML::encode($str, true, $double_encode);
	}
	
	/**
	 * Encode a string for use within an XML document
	 *
	 * @todo Investigate QuickBooks qbXML encoding and implement solution
	 * 
	 * @param string $str				The string to encode
	 * @param boolean $for_qbxml		
	 * @return string
	 */
	static public function encode($str, $for_qbxml = true, $double_encode = true)
	{
		$transform = array(
			'&' => '&amp;', 
			'<' => '&lt;', 
			'>' => '&gt;', 
			'\'' => '&apos;', 
			'"' => '&quot;', 
			);
			
		$str = str_replace(array_keys($transform), array_values($transform), $str);
		
		if (!$double_encode)
		{
			$fix = array();
			foreach ($transform as $raw => $encoded)
			{
				$fix[str_replace('&', '&amp;', $encoded)] = $encoded;
			}
			
			$str = str_replace(array_keys($fix), array_values($fix), $str);
		}
		
		return $str;
	}
	
	/**
	 * Decode a string for use within an XML document
	 *
	 * @todo Investigate QuickBooks qbXML encoding and implement solution
	 * 
	 * @param string $str				The string to encode
	 * @param boolean $for_qbxml		
	 * @return string
	 */
	static public function decode($str, $for_qbxml = true)
	{
		$transform = array(
			'&lt;' => '<', 
			'&gt;' => '>', 
			'&apos;' => '\'', 
			'&quot;' => '"', 
			'&amp;' => '&', 		// Make sure that this is *the last* transformation to run, otherwise we end up double-un-encoding things
			);
			
		return str_replace(array_keys($transform), array_values($transform), $str);		
	}
}

