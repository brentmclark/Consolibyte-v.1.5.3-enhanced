<?php

//$url = 'http://localhost/~kpalmer/QuickBooks%20SQL%20Mirror/example_sql_server.php';
//$url = 'http://localhost:8080/quickbooks/server';
//$url = 'http://localhost/~kpalmer/QuickBooks%20-%20embwebstores/embwebstores_server.php';
//$username = 'quckbooks';
//$password = 'password';

//$url = 'http://localhost/~kpalmer/QuickBooks/example_server_oop.php';
//$username = 'quickbooks';
//$password = 'password';

$url = 'http://localhost:8888/Cornerstone/html/index.php?__module=accounting_quickbooks&__action=server';
$username = 'quickbooks';
$password = 'password';

header('Content-type: text/plain');

print('URL: ' . $url . "\n");
print('User: ' . $username . "\n");
print('Pass: ' . $password . "\n");

$return = tester($url, $username, $password, 'authenticate');

print($return);

$pos = strpos($return, '<ns1:string>');
$ticket = substr($return, $pos + 12, 32);

print("\n\n" . 'TICKET IS: ' . $ticket . "\n\n");

exit;

$max = 1;
for ($i = 0; $i < $max; $i++)
{
	print(tester($url, $ticket, null, 'sendRequestXML'));
}

exit;

function tester($url, $username_or_ticket, $password, $method, $data = null)
{
	print('Sending request method: ' . $method . "\n");
	
	switch ($method)
	{
		case 'authenticate':
			$soap = '<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/"
 SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
 xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
	<SOAP-ENV:Body>
		<authenticate xmlns="http://developer.intuit.com/">
			<strUserName xsi:type="xsd:string">' . $username_or_ticket . '</strUserName>
			<strPassword xsi:type="xsd:string">' . $password . '</strPassword>
		</authenticate>
	</SOAP-ENV:Body>
</SOAP-ENV:Envelope>';
			break;
		case 'sendRequestXML':
			$soap = '<?xml version="1.0" encoding="UTF-8"?>
	<SOAP-ENV:Envelope 
	 xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"
	 xmlns:ns1="http://developer.intuit.com/">
		<SOAP-ENV:Body>
			<ns1:sendRequestXML>
				<ns1:ticket>' . $username_or_ticket . '</ns1:ticket>
				<ns1:strHCPResponse></ns1:strHCPResponse>
				<ns1:strCompanyFileName></ns1:strCompanyFileName>
				<ns1:qbXMLCountry></ns1:qbXMLCountry>
				<ns1:qbXMLMajorVers>4</ns1:qbXMLMajorVers>
				<ns1:qbXMLMinorVers>0</ns1:qbXMLMinorVers>
			</ns1:sendRequestXML>
		</SOAP-ENV:Body>
	</SOAP-ENV:Envelope>';
			break;
	}
	
	$headers = array(
		'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; MS Web Services Client Protocol 2.0.50727.1433)', 
		'Content-Type: text/xml; charset=utf-8',
		'Soapaction: "http://developer.intuit.com/' . $method . '"',
		);

	if (function_exists('curl_init'))
	{
		$curl = curl_init($url); 
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		
		
		curl_setopt($curl, CURLOPT_POSTFIELDS, $soap);
		
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_MAXCONNECTS, 1);
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		
		$return = curl_exec($curl);
	}
	else
	{
		$parse = parse_url($url);
		if (empty($parse['port']))
		{
			$parse['port'] = 80;
		}
		
		if ($parse['scheme'] == 'https')
		{
			die('sorry, you need curl to test https (for now at least)');
		}
		
		if ($fp = fsockopen($parse['host'], $parse['port']))
		{
			$request = '';
			$request .= 'POST ' . $parse['path'] . '?' . $parse['query'] . ' HTTP/1.0' . "\r\n";
			$request .= 'Host: ' . $parse['host'] . "\r\n";
			
			foreach ($headers as $key => $value)
			{
				//$request .= $key . ': ' . $value . "\r\n";
				$request .= $value . "\r\n";
			}
			
			$request .= 'Content-Length: ' . strlen($soap) ."\r\n"; 
			$request .= 'Connection: close' . "\r\n";
			$request .= "\r\n"; 
			$request .= $soap; 
			
			print(str_repeat('-', 20) . ' REQUEST ' . str_repeat('-', 20) . "\n");
			print($request . "\n");
			print(str_repeat('-', 48) . "\n");
			
			fputs($fp, $request);
				
			$bytes = 0;
			$resp = '';
			while (!feof($fp) and $bytes < 10000) 
			{ 
				$tmp = fgets($fp, 128);
				$bytes += strlen($tmp);
				
				$resp .= $tmp; 
			}
			
			print(str_repeat('-', 19) . ' RESPONSE ' . str_repeat('-', 19) . "\n");	
			print($resp . "\n");
			print(str_repeat('-', 48) . "\n");
			print("\n\n");
				
			fclose($fp);	
		}
		else
		{
			die('Connection failed!');
		}
			
		$return = $resp;
	}
	
	return $return;
}

//$response = ' THE QBXML RESPONSE GOES HERE ';
$response = '<?xml version="1.0" ?>
<QBXML>
<QBXMLMsgsRs>
<EstimateQueryRs requestID="RXN0aW1hdGVRdWVyeXw4Zjc2NTI2YzQ5OTI5YWZkNmZlMTVjMzJiNzM3ZDdiYw==" statusCode="0" statusSeverity="Info" statusMessage="Status OK">
<EstimateRet>
<TxnID>19-1236008423</TxnID>
<TimeCreated>2009-03-02T10:40:23-05:00</TimeCreated>
<TimeModified>2009-03-02T10:40:23-05:00</TimeModified>
<EditSequence>1236008423</EditSequence>
<TxnNumber>6</TxnNumber>
<CustomerRef>
<ListID>80000003-1235098924</ListID>
<FullName>176007582 - Eastern XYZ University</FullName>
</CustomerRef>
<TemplateRef>
<ListID>8000000C-1232556584</ListID>
<FullName>Custom Estimate</FullName>
</TemplateRef>
<TxnDate>2009-03-02</TxnDate>
<RefNumber>6</RefNumber>
<BillAddress>
<Addr1>Eastern XYZ University</Addr1>
<Addr2>College of Engineering</Addr2>
<Addr3>123 XYZ Road</Addr3>
<City>Storrs-Mansfield</City>
<State>CT</State>
<PostalCode>06268</PostalCode>
<Country>USA</Country>
</BillAddress>
<BillAddressBlock>
<Addr1>Eastern XYZ University</Addr1>
<Addr2>College of Engineering</Addr2>
<Addr3>123 XYZ Road</Addr3>
<Addr4>Storrs-Mansfield, CT 06268</Addr4>
<Addr5>United States</Addr5>
</BillAddressBlock>
<IsActive>true</IsActive>
<DueDate>2009-03-02</DueDate>
<Subtotal>6.00</Subtotal>
<ItemSalesTaxRef>
<ListID>80000001-1224606947</ListID>
<FullName>Out of State</FullName>
</ItemSalesTaxRef>
<SalesTaxPercentage>0.00</SalesTaxPercentage>
<SalesTaxTotal>0.00</SalesTaxTotal>
<TotalAmount>6.00</TotalAmount>
<IsToBeEmailed>false</IsToBeEmailed>
<CustomerSalesTaxCodeRef>
<ListID>80000001-1224606843</ListID>
<FullName>Tax</FullName>
</CustomerSalesTaxCodeRef>
<EstimateLineRet>
<TxnLineID>1B-1236008423</TxnLineID>
<ItemRef>
<ListID>80000006-1235049533</ListID>
<FullName>Bio-Hazard Clean-up</FullName>
</ItemRef>
<Desc>Test Estimate Line Item</Desc>
<Quantity>2</Quantity>
<Rate>1.00</Rate>
<Amount>2.00</Amount>
<SalesTaxCodeRef>
<ListID>80000001-1224606843</ListID>
<FullName>Tax</FullName>
</SalesTaxCodeRef>
</EstimateLineRet>
<EstimateLineRet>
<TxnLineID>1C-1236008423</TxnLineID>
<ItemRef>
<ListID>80000008-1235049539</ListID>
<FullName>Fire Restoration</FullName>
</ItemRef>
<Desc>Test Estimate 2</Desc>
<Quantity>4</Quantity>
<Rate>1.00</Rate>
<Amount>4.00</Amount>
<SalesTaxCodeRef>
<ListID>80000001-1224606843</ListID>
<FullName>Tax</FullName>
</SalesTaxCodeRef>
</EstimateLineRet>
<EstimateLineRet>
<TxnLineID>1D-1236008423</TxnLineID>
<Desc>Test Esitmate 3</Desc>
</EstimateLineRet>
</EstimateRet>
</EstimateQueryRs>
</QBXMLMsgsRs>
</QBXML>';

/*
$response = '<?xml version="1.0" ?>
<QBXML>
	<QBXMLMsgsRs>
		<CustomerQueryRs requestID="QnJpZGdlfDEw" statusCode="500" statusSeverity="Warn" statusMessage="The query request has not been fully completed. There was a required element (&quot;Keith Palmer&quot;) that could not be found in QuickBooks." />
	</QBXMLMsgsRs>
</QBXML>';
*/

$response = htmlspecialchars($response, ENT_QUOTES);

// receive response
$soap = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><receiveResponseXML xmlns="http://developer.intuit.com/"><ticket>' . $ticket . '</ticket><response>' . $response . '</response><hresult /><message /></receiveResponseXML></soap:Body></soap:Envelope>';



curl_setopt($curl, CURLOPT_POSTFIELDS, $soap);
	
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
curl_setopt($curl, CURLOPT_MAXCONNECTS, 1);
//curl_setopt($curl, CURLOPT_POSTFIELDS, $soap);	
$return = curl_exec($curl);
print($return);

print('sent ' . $response . "\n\n\n");
print('got back: [[' . $return . ']]');
exit;

// get last error
$soap = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><getLastError xmlns="http://developer.intuit.com/"><ticket>ea0bf596246b623664adb465cc8662a2</ticket></getLastError></soap:Body></soap:Envelope>';

curl_setopt($curl, CURLOPT_POSTFIELDS, $soap);	
$return = curl_exec($curl);
print($return);

// exit;

// close connection
$soap = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Body><closeConnection xmlns="http://developer.intuit.com/"><ticket>ea0bf596246b623664adb465cc8662a2</ticket></closeConnection></soap:Body></soap:Envelope>';

curl_setopt($curl, CURLOPT_POSTFIELDS, $soap);	
$return = curl_exec($curl);
print($return);

?>
