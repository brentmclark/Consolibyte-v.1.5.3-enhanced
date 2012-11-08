<?php

/**
 * 
 * 
 * @author Keith Palmer <keith@consolibyte.com>
 * @license LICENSE.txt 
 * 
 * @package QuickBooks
 * @subpackage SQL
 */

/**
 * 
 */
class QuickBooks_Server_SQL_Errors
{
	/**
	 * @TODO Change this to return false by default, and only catch the specific errors we're concerned with.
	 * 
	 */
	static public function catchall($requestID, $user, $action, $ident, $extra, &$err, $xml, $errnum, $errmsg, $config)
	{
		$Driver = QuickBooks_Driver_Singleton::getInstance();
		
		/*
		$Parser = new QuickBooks_XML($xml);
		$errnumTemp = 0;
		$errmsgTemp = '';
		$Doc = $Parser->parse($errnumTemp, $errmsgTemp);
		$Root = $Doc->getRoot();		
		$emailStr = var_export($Root->children(), true);
			
		$List = $Root->getChildAt('QBXML QBXMLMsgsRs '.QuickBooks_Utilities::actionToResponse($action));
		$Node = current($List->children());
		*/
		
		$map = array();
		$others = array();
		QuickBooks_SQL_Schema::mapToSchema(trim(QuickBooks_Utilities::actionToXMLElement($action)), QUICKBOOKS_SQL_SCHEMA_MAP_TO_SQL, $map, $others);
		$sqlObject = new QuickBooks_SQL_Object($map[0], trim(QuickBooks_Utilities::actionToXMLElement($action)));
		$table = $sqlObject->table();
			
		switch ($errnum)
		{
			case 1:		// These errors occur when we search for something and it doesn't exist
			case 500:	// 	i.e. we query for invoices modified since xyz, but there are none that have been modified since then
				
				// This isn't really an error, just ignore it
				
				return true;
			case 1000: // An internal error occured
				
				// @todo Hopefully at some point we'll have a better idea of how to handle this error...
				
				return true;
			case 3200:
				// Ignore EditSequence errors (the record will be picked up and a conflict reported next time it runs... maybe?)
				// @todo Think about this one more
				return true;
			case 3250: // This feature is not enabled or not available in this version of QuickBooks. 
				
				// Do nothing (this can be safely ignored)
				
				return true;
			case 3100: // Name of List Element is already in use.
				
				$multipart = array( QUICKBOOKS_DRIVER_SQL_FIELD_ID => $ident );
				$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_NUMBER, $errnum);
				$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE, $errmsg);
				
				$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $sqlObject, array( $multipart ));
				
				break;
			case 3260: // Insufficient permission level to perform this action. 
				
				// There's nothing we can do about this, if they don't grant the user permission, just skip it
				
				return true;
			case 3200: // The provided edit sequence is out-of-date.
				
				if (!$tmp = $Driver->get(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, array(QUICKBOOKS_DRIVER_SQL_FIELD_ID => $ident) ) )
				{
					return true;
				}
					
				switch ($config['conflicts'])
				{
					case QUICKBOOKS_SERVER_SQL_CONFLICT_LOG:
					
						$multipart = array(QUICKBOOKS_DRIVER_SQL_FIELD_ID => $ident);
						$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_NUMBER, $errnum);
						$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE, $errmsg);
						$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $sqlObject, array( $multipart ));
						
						break;						
					case QUICKBOOKS_SERVER_SQL_CONFLICT_NEWER:
						
						$Parser = new QuickBooks_XML_Parser($xml);
						$errnumTemp = 0;
						$errmsgTemp = '';
						$Doc = $Parser->parse($errnumTemp, $errmsgTemp);
						$Root = $Doc->getRoot();		
							
						$List = $Root->getChildAt('QBXML QBXMLMsgsRs '.QuickBooks_Utilities::actionToResponse($action));
						$TimeModified = $Root->getChildDataAt('QBXML QBXMLMsgsRs ' . QuickBooks_Utilities::actionToResponse($action) . ' ' . QuickBooks_Utilities::actionToXMLElement($action) . ' TimeModified');
						$EditSequence = $Root->getChildDataAt('QBXML QBXMLMsgsRs ' . QuickBooks_Utilities::actionToResponse($action) . ' ' . QuickBooks_Utilities::actionToXMLElement($action) . ' EditSequence');
							
						$multipart = array(QUICKBOOKS_DRIVER_SQL_FIELD_ID => $ident);
							
						if (QuickBooks_Utilities::compareQBTimeToSQLTime($TimeModified, $tmp->get(QUICKBOOKS_DRIVER_SQL_FIELD_MODIFY)) >= 0 && $config['mode'] != QUICKBOOKS_SERVER_SQL_MODE_WRITEONLY)
						{
							//@TODO: Make this get only a single item, not the whole table
							$Driver->queueEnqueue($user, QuickBooks_Utilities::convertActionToQuery($action), __FILE__, true, QUICKBOOKS_SERVER_SQL_CONFLICT_QUEUE_PRIORITY, $extra);							
						}
						else if (QuickBooks_Utilities::compareQBTimeToSQLTime($TimeModified, $tmp->get(QUICKBOOKS_DRIVER_SQL_FIELD_MODIFY)) < 0)
						{
							//Updates the EditSequence without marking the row as resynced.
							$tmpSQLObject = new QuickBooks_SQL_Object($table, null);
							$tmpSQLObject->set("EditSequence", $EditSequence);
							$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $tmpSQLObject, array( $multipart ));
							$Driver->queueEnqueue($user, QuickBooks_Utilities::convertActionToMod($action), $tmp->get(QUICKBOOKS_DRIVER_SQL_FIELD_ID), true, QUICKBOOKS_SERVER_SQL_CONFLICT_QUEUE_PRIORITY, $extra);
						}
						else
						{
							//Trash it, set synced.
							$tmpSQLObject = new QuickBooks_SQL_Object($table, null);
							$tmpSQLObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE, "Read/Write Mode is WRITEONLY, and Conflict Mode is NEWER, and Quickbooks has Newer data, so no Update Occured.");
							$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $tmpSQLObject, array( $multipart ));
						}
						
						break;
					case QUICKBOOKS_SERVER_SQL_CONFLICT_QUICKBOOKS:
						
						if ($config['mode'] == QUICKBOOKS_SERVER_SQL_MODE_READWRITE)
						{
							//@TODO: Make this get only a single item, not the whole table
							$Driver->queueEnqueue($user, QuickBooks_Utilities::convertActionToQuery($action), null, true, QUICKBOOKS_SERVER_SQL_CONFLICT_QUEUE_PRIORITY, $extra);
							$multipart = array(QUICKBOOKS_DRIVER_SQL_FIELD_ID => $ident);
							$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_NUMBER, $errnum);
							$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE, $errmsg);
							$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $sqlObject, array( $multipart ));
							//Use what's on quickbooks, and trash whatever is here.
						}
						else
						{
							$multipart = array(QUICKBOOKS_DRIVER_SQL_FIELD_ID => $ident);
							$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_NUMBER, $errnum);
							$sqlObject->set(QUICKBOOKS_DRIVER_SQL_FIELD_ERROR_MESSAGE, $errmsg);
							$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $sqlObject, array( $multipart ));
							// @TODO: Raise Notification that the conflicts level requires writing to SQL table, but Mode disallows this
						}
						
						break;						
					case QUICKBOOKS_SERVER_SQL_CONFLICT_SQL:
							// Updates the EditSequence without marking the row as resynced.
							$tmpSQLObject = new QuickBooks_SQL_Object($table, null);
							$tmpSQLObject->set("EditSequence", $EditSequence);
							$Driver->update(QUICKBOOKS_DRIVER_SQL_PREFIX_SQL . $table, $tmpSQLObject, array( $multipart ));
							$Driver->queueEnqueue($user, QuickBooks_Utilities::convertActionToMod($action), $tmp->get(QUICKBOOKS_DRIVER_SQL_FIELD_ID), true, QUICKBOOKS_SERVER_SQL_CONFLICT_QUEUE_PRIORITY, $extra);
						break;
						
					case QUICKBOOKS_SERVER_SQL_CONFLICT_CALLBACK:
						break;
						
					default:
						break;
				}
				
				break;
			default:
				
				if (strstr($xml, 'statusSeverity="Info"') === false) // If it's NOT just an Info message.
				{	
					// 
				}
				
				break;
		}
		
		// Please don't change this, it stops us from knowing what's actually 
		//	going wrong. If an error occurs, we should either catch it if it's 
		//	recoverable, or treated as a fatal error so we know about it and 
		//	can address it later.  
		//return false;
		return true;
	}
}
