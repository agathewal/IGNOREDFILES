<?
require('../config/configuration.php');
require_once 'EWS_Exception.php';
require_once 'ExchangeWebServices.php';
require_once 'NTLMSoapClient.php';
require_once 'NTLMSoapClient/Exchange.php';
spl_autoload_register('ews_autoloader');

$inF = fopen('synchro-log.txt',"a+");
fwrite($inF,'Launch Synchro Exchange Ã  '.date('Y-m-d H:i:s')."\n");
fclose($inF);

$req_id_user=' AND CONNEXION_ACTIVE = 1 AND SYNCHRO_TO_EXCHANGE = 1';
if(isset($_GET['id_user']) && is_numeric($_GET['id_user'])){
	$req_id_user=' AND ID_USER = '.$_GET['id_user'];
}
ini_set('max_execution_time', 600);
$req_exchange=$db->query('SELECT * FROM comm_exchange_user_config WHERE ID_USER IN (SELECT ID_USER FROM comm_event_participants WHERE TO_INSERT = 1 OR TO_UPDATE = 1 OR TO_DELETE = 1)'.$req_id_user);
if(mysql_num_rows($req_exchange)!=0){
	while($info_exchange=mysql_fetch_array($req_exchange)){
		$req_event_interaction=$db->query('SELECT * FROM comm_event_participants WHERE ID_USER = '.$info_exchange['ID_USER'].' AND (TO_INSERT = 1 OR TO_UPDATE = 1 OR TO_DELETE = 1) ORDER BY TO_INSERT DESC, TO_UPDATE DESC, TO_DELETE DESC');
		if(mysql_num_rows($req_event_interaction)!=0){
			$client = new ExchangeWebServices($info_exchange['SERVER_EX'], $info_exchange['USER_EX'], $info_exchange['PASSWORD_EX'], $info_exchange['TYPE_SERVER_EX']);
			while($don_interaction=mysql_fetch_array($req_event_interaction)){
				if($don_interaction['TO_INSERT']==1 OR $don_interaction['TO_UPDATE']==1){
					$event_info=get_event_data($don_interaction['ID_EVENT']);
				}
				if($don_interaction['TO_INSERT']==1){//qd on ajoute des évènements à exchange				
					//pr($don_interaction);
					$db->execute('UPDATE comm_event_participants SET TO_INSERT = 0 WHERE ID_EVENT = '.$don_interaction['ID_EVENT'].' AND ID_USER = '.$info_exchange['ID_USER']);
					$request = new EWSType_CreateItemType();
					$request->Items = new EWSType_NonEmptyArrayOfAllItemsType();
					$request->Items->CalendarItem = new EWSType_CalendarItemType();
					$request->Items->CalendarItem->Subject = $event_info['NAME'];
					$date_start = new DateTime($event_info['DATE_START']);
					$request->Items->CalendarItem->Start = $date_start->format('c');
					$date_end = new DateTime($event_info['DATE_END']);
					$request->Items->CalendarItem->End = $date_end->format('c');
					$request->Items->CalendarItem->Location = $event_info['LOCATION'];
					$request->Items->CalendarItem->ReminderIsSet = false;
					if($info_exchange['CALENDAR_FOR_UPDATE']!=''){
						$request->SavedItemFolderId->FolderId->Id = $info_exchange['CALENDAR_FOR_UPDATE'];
					}else{
						$request->SavedItemFolderId->DistinguishedFolderId->Id = 'calendar';
					}					
					$request->Items->CalendarItem->Body = new EWSType_BodyType();
					$request->Items->CalendarItem->Body->BodyType = EWSType_BodyTypeType::TEXT;
					$request->Items->CalendarItem->Body->_ = $event_info['DESC'];
					$request->Items->CalendarItem->ItemClass = new EWSType_ItemClassType();
					$request->Items->CalendarItem->ItemClass->_ = EWSType_ItemClassType::APPOINTMENT;
					$request->Items->CalendarItem->Sensitivity = new EWSType_SensitivityChoicesType();
					$request->Items->CalendarItem->Sensitivity->_ = EWSType_SensitivityChoicesType::NORMAL;
					$request->Items->CalendarItem->Categories = new EWSType_ArrayOfStringsType();
					$request->Items->CalendarItem->Categories->String = array(WEBSITE_NAME);
					$request->Items->CalendarItem->Importance = new EWSType_ImportanceChoicesType();
					$request->Items->CalendarItem->Importance->_ = EWSType_ImportanceChoicesType::NORMAL;
					$request->SendMeetingInvitations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;
					try{
						$response = $client->CreateItem($request);						
						if($response->ResponseMessages->CreateItemResponseMessage->ResponseCode=='NoError'){
							$db->execute("UPDATE comm_event_participants SET EVENT_EXCHANGE = '".addslashes($response->ResponseMessages->CreateItemResponseMessage->Items->CalendarItem->ItemId->Id)."' WHERE ID_EVENT = ".$don_interaction['ID_EVENT']." AND ID_USER = ".$info_exchange['ID_USER']);
						}else{							
							$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",1,'','1:1:".addslashes($response->ResponseMessages->CreateItemResponseMessage->ResponseCode)."',".time().")");
						}				
					}
					catch (Exception $e) {
						$error_exchange=$e->getMessage();
						$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",1,'','1:2:".addslashes($error_exchange)."',".time().")");
					}
					
				}else if($don_interaction['TO_UPDATE']==1){//qd on mets à jour des évènements à exchange	
				
					$db->execute('UPDATE comm_event_participants SET TO_UPDATE = 0 WHERE ID_EVENT = '.$don_interaction['ID_EVENT'].' AND ID_USER = '.$info_exchange['ID_USER']);
					if($don_interaction['EVENT_EXCHANGE']!=''){
						$request = new EWSType_FindItemType();
						$request->ItemShape = new EWSType_ItemResponseShapeType();
						$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
						$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
						$request->ItemIds->ItemId = new EWSType_ItemIdType();
						$request->ItemIds->ItemId->Id = $don_interaction['EVENT_EXCHANGE'];
						try{
							$response = $client->GetItem($request);
							if($response->ResponseMessages->GetItemResponseMessage->ResponseCode=='NoError'){
								
								$ChangeKey=$response->ResponseMessages->GetItemResponseMessage->Items->CalendarItem->ItemId->ChangeKey;
								$request = new EWSType_UpdateItemType();
								$request->ConflictResolution = 'AlwaysOverwrite';
								$request->SendMeetingInvitationsOrCancellations = 'SendToNone';
								$request->ItemChanges = array();

								$change = new EWSType_ItemChangeType();
								$change->ItemId = new EWSType_ItemIdType();
								$change->ItemId->Id = $don_interaction['EVENT_EXCHANGE'];
								$change->ItemId->ChangeKey = $ChangeKey;
								
								$field = new EWSType_SetItemFieldType();
								$field->FieldURI = new EWSType_PathToUnindexedFieldType();
								$field->FieldURI->FieldURI = 'item:Subject';
								$field->CalendarItem = new EWSType_CalendarItemType();
								$field->CalendarItem->Subject = $event_info['NAME'];
								$change->Updates->SetItemField[] = $field;
								unset($field);
				
								$field = new EWSType_SetItemFieldType();
								$field->FieldURI = new EWSType_PathToUnindexedFieldType();
								$field->FieldURI->FieldURI = 'calendar:Location';
								$field->CalendarItem = new EWSType_CalendarItemType();
								$field->CalendarItem->Location = $event_info['LOCATION'];
								$change->Updates->SetItemField[] = $field;

								unset($field);
								$field = new EWSType_SetItemFieldType();
								$field->FieldURI = new EWSType_PathToUnindexedFieldType();
								$field->FieldURI->FieldURI = 'item:Body';
								$field->CalendarItem = new EWSType_CalendarItemType();
								$field->CalendarItem->Body->BodyType = EWSType_BodyTypeType::TEXT;
								$field->CalendarItem->Body->_ = $event_info['DESC'];
								$change->Updates->SetItemField[] = $field;
								
								unset($field);
								$date_start = new DateTime($event_info['DATE_START']);
								$field = new EWSType_SetItemFieldType();
								$field->FieldURI = new EWSType_PathToUnindexedFieldType();
								$field->FieldURI->FieldURI = 'calendar:Start';
								$field->CalendarItem = new EWSType_CalendarItemType();
								$field->CalendarItem->Start = $date_start->format('c');
								$change->Updates->SetItemField[] = $field;

								unset($field);
								$date_end = new DateTime($event_info['DATE_END']);
								$field = new EWSType_SetItemFieldType();
								$field->FieldURI = new EWSType_PathToUnindexedFieldType();
								$field->FieldURI->FieldURI = 'calendar:End';
								$field->CalendarItem = new EWSType_CalendarItemType();
								$field->CalendarItem->End = $date_end->format('c');
								$change->Updates->SetItemField[] = $field;
								//pr($change);
								$request->ItemChanges[] = $change;								
								try{
									$response = $client->UpdateItem($request);
									if($response->ResponseMessages->UpdateItemResponseMessage->ResponseCode!='NoError'){						
										$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','2:1:".addslashes($response->ResponseMessages->UpdateItemResponseMessage->ResponseCode)."',".time().")");
									}	
								}
								catch (Exception $e) {
									$error_exchange=$e->getMessage();
									$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','2:2:".addslashes($error_exchange)."',".time().")");
								}
								
							}else{
								$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','2:3:".addslashes($response->ResponseMessages->UpdateItemResponseMessage->ResponseCode)."',".time().")");
							}
						}
						catch (Exception $e) {
							$error_exchange=$e->getMessage();
							$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','2:4:".addslashes($error_exchange)."',".time().")");
						}
					}else{
						//pr($don_interaction);
						$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'','2:5:Identifiant Exchange inconnu',".time().")");
					}
					
				}else if($don_interaction['TO_DELETE']==1){//qd on supprime des évènements sur le communautaire	
					$db->execute('UPDATE comm_event_participants SET TO_DELETE = 0 WHERE ID_EVENT = '.$don_interaction['ID_EVENT'].' AND ID_USER = '.$info_exchange['ID_USER']);
					if($don_interaction['EVENT_EXCHANGE']!=''){
					
						$request = new EWSType_FindItemType();
						$request->ItemShape = new EWSType_ItemResponseShapeType();
						$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
						$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
						$request->ItemIds->ItemId = new EWSType_ItemIdType();
						$request->ItemIds->ItemId->Id = $don_interaction['EVENT_EXCHANGE'];
						try{
							$response = $client->GetItem($request);
							if($response->ResponseMessages->GetItemResponseMessage->ResponseCode=='NoError'){
								
								$request = new EWSType_DeleteItemType();
								$request->ItemIds->ItemId[0]->Id = $don_interaction['EVENT_EXCHANGE'];	    
								$request->DeleteType = EWSType_DisposalType::MOVE_TO_DELETED_ITEMS;
								$request->SendMeetingCancellations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_ONLY_TO_ALL;								
								try{
									$response = $client->DeleteItem($request);
									if($response->ResponseMessages->DeleteItemResponseMessage->ResponseCode!='NoError'){						
										$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",3,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','3:1:".addslashes($response->ResponseMessages->DeleteItemResponseMessage->ResponseCode)."',".time().")");
									}else{
										$db->execute('UPDATE comm_event_participants SET EVENT_EXCHANGE = \'\' WHERE ID_EVENT = '.$don_interaction['ID_EVENT'].' AND ID_USER = '.$info_exchange['ID_USER']);
									}	
								}
								catch (Exception $e) {
									$error_exchange=$e->getMessage();
									$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",3,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','3:2:".addslashes($error_exchange)."',".time().")");
								}
								
							}else{
								$db->execute('UPDATE comm_event_participants SET EVENT_EXCHANGE = \'\' WHERE ID_EVENT = '.$don_interaction['ID_EVENT'].' AND ID_USER = '.$info_exchange['ID_USER']);
								$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",3,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','3:3:".addslashes($response->ResponseMessages->GetItemResponseMessage->ResponseCode)."',".time().")");
							}
						}
						catch (Exception $e) {
							$error_exchange=$e->getMessage();
							$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'".addslashes($don_interaction['EVENT_EXCHANGE'])."','3:4:".addslashes($error_exchange)."',".time().")");
						}
					}else{
						//pr($don_interaction);
						$db->execute("INSERT INTO comm_exchange_error (ID_USER,ID_EVENT,TYPE_OPE,ID_EVENT_EXCHANGE,`ERROR`,TIME_ERROR) VALUES (".$info_exchange['ID_USER'].",".$don_interaction['ID_EVENT'].",2,'','3:5:Identifiant Exchange inconnu',".time().")");
					}	
				}
			}
		}
	}
}
echo 'OK';
?>