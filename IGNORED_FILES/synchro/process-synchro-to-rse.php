<?
require('../config/configuration.php');
require_once 'EWS_Exception.php';
require_once 'ExchangeWebServices.php';
require_once 'NTLMSoapClient.php';
require_once 'NTLMSoapClient/Exchange.php';
spl_autoload_register('ews_autoloader');

$req_id_user=' CONNEXION_ACTIVE = 1 AND SYNCHRO_TO_NEXINET = 1';
if(isset($_GET['id_user']) && is_numeric($_GET['id_user'])){
	$req_id_user=' ID_USER = '.$_GET['id_user'];
}

ini_set('max_execution_time', 600);
$req_exchange=$db->query('SELECT * FROM comm_exchange_user_config WHERE'.$req_id_user.' AND ID_USER IN (SELECT ID_USER FROM `comm_exchange_user_calendar`)');
if(mysql_num_rows($req_exchange)!=0){
	while($info_exchange=mysql_fetch_array($req_exchange)){	

		$list_event_from_exchange_present=array();
		$req_in_base=$db->query('SELECT ID FROM comm_event WHERE ID_CREATOR = '.$info_exchange['ID_USER'].' AND IS_EXCHANGE_ITEM = 1');
		if(mysql_num_rows($req_in_base)!=0){
			while($don=mysql_fetch_array($req_in_base)){
				$list_event_from_exchange_present[]=$don['ID'];
			}
		}
		
		pr($list_event_from_exchange_present);
		
		$req_calendar=$db->query('SELECT * FROM `comm_exchange_user_calendar` WHERE ID_USER = '.$info_exchange['ID_USER']);
		if(mysql_num_rows($req_calendar)!=0){
			$client = new ExchangeWebServices($info_exchange['SERVER_EX'], $info_exchange['USER_EX'], $info_exchange['PASSWORD_EX'], $info_exchange['TYPE_SERVER_EX']);
			while($calendar=mysql_fetch_array($req_calendar)){		
				
				$list_event=array();
				// Set init class
				$request = new EWSType_FindItemType();
				// Use this to search only the items in the parent directory in question or use ::SOFT_DELETED
				// to identify "soft deleted" items, i.e. not visible and not in the trash can.
				$request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;
				// This identifies the set of properties to return in an item or folder response
				$request->ItemShape = new EWSType_ItemResponseShapeType();
				$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

				// Define the timeframe to load calendar items
				$request->CalendarView = new EWSType_CalendarViewType();
				$date_start = new DateTime('NOW');
				$date_end = new DateTime('NOW');
				$date_start->modify('-1 month');
				$date_end->modify('+6 months');
				$request->CalendarView->StartDate = $date_start->format('c'); // an ISO8601 date e.g. 2012-06-12T15:18:34+03:00
				$request->CalendarView->EndDate = $date_end->format('c');// an ISO8601 date later than the above

				$request->ParentFolderIds =new EWSType_TargetFolderIdType();
				$request->ParentFolderIds->FolderId = new EWSType_FolderIdType();
				$request->ParentFolderIds->FolderId->Id = $calendar['ID_CALENDAR_IN_EXCHANGE'];
				
				// Send request
				$response = $client->FindItem($request);
				/*echo '<pre>';
				print_r($response);
				echo '</pre>';*/
				
				// Loop through each item if event(s) were found in the timeframe specified
				$nb_event=$response->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView;
				//echo "Nombre d'évènements : ".$nb_event.'<br><br><br><br>';
				
				if($nb_event==1){

					$events = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
					//var_dump($events);
					/*echo '<pre>';
					print_r($events);
					echo '</pre>';*/
					$event_to_add=array();					
					$event_to_add['id'] = $events->ItemId->Id;
					$event_to_add['change_key'] = $events->ItemId->ChangeKey;
					$event_to_add['start'] = strtotime($events->Start);
					$event_to_add['start_timestamp']=date('Y-m-d H:i:s',$event_to_add['start']);
					$event_to_add['end'] = strtotime($events->End);
					$event_to_add['end_timestamp']=date('Y-m-d H:i:s',$event_to_add['end']);
					$event_to_add['subject'] = $events->Subject;
					$event_to_add['location'] = $events->Location;
					$list_event[]=$event_to_add;
					
				}else if ($nb_event> 1){
				   
					$events = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
					// var_dump($events);
					foreach ($events as $event){
						$event_to_add=array();
						/*echo '<pre>';
						print_r($event);
						echo '</pre>';*/
						$event_to_add['id'] = $event->ItemId->Id;
						$event_to_add['change_key'] = $event->ItemId->ChangeKey;
						$event_to_add['start'] = strtotime($event->Start);
						$event_to_add['start_timestamp']=date('Y-m-d H:i:s',$event_to_add['start']);
						$event_to_add['end'] = strtotime($event->End);
						$event_to_add['end_timestamp']=date('Y-m-d H:i:s',$event_to_add['end']);
						$event_to_add['subject'] = $event->Subject;
						$event_to_add['location'] = $event->Location;
						$list_event[]=$event_to_add;
					}
				}
				
				if(count($list_event)!=0){
					foreach($list_event as $event){
						//echo $event['id'].'<br>';
						
						$is_already_in_base=$db->countOf('comm_event_participants',"ID_USER= ".$info_exchange['ID_USER']." AND EVENT_EXCHANGE = '".$event['id']."'");
						if($is_already_in_base==0){//si pas provenant du RSE								
							$req_event_echange=$db->query('SELECT * FROM comm_event WHERE ID_CREATOR = '.$info_exchange['ID_USER']." AND ID_EXCHANGE_EVENT = '".$event['id']."'");
							if(mysql_num_rows($req_event_echange)!=0){
							
								$info_event=mysql_fetch_array($req_event_echange);								
								$value_to_del=array_search($info_event['ID'],$list_event_from_exchange_present);
								unset($list_event_from_exchange_present[$value_to_del]);
								
								if($info_event['CHANGE_KEY']!=$event['change_key']){
									//nouvelle clé !
									//echo "nouvelle clé";
									$db->execute("UPDATE comm_event SET `NAME` = '".addslashes($event['subject'])."' ,`DATE_START` = '".$event['start_timestamp']."',`DATE_END` = '".$event['end_timestamp']."',`DT_MODIF` = ".time().",`LOCATION` = '".addslashes($event['location'])."', CHANGE_KEY = '".$event['change_key']."', ID_CALENDAR = '".$calendar['ID_CALENDAR_IN_EXCHANGE']."' WHERE ID = ".$info_event['ID']);
								}	
								
							}else{
								//echo "on insert !";
								$db->execute("INSERT INTO comm_event (`NAME`,`DATE_START`,`DATE_END`,`ID_CREATOR`,`DT_MODIF`,`DT_CREATE`,`LOCATION`,`DESC`,`TAGS`,`TYPE_INSCRIPTION`,`DISPLAY_LIST`,`PHOTO`,IS_EXCHANGE_ITEM, ID_EXCHANGE_EVENT, CHANGE_KEY, ID_CALENDAR) VALUES ('".addslashes($event['subject'])."','".$event['start_timestamp']."','".$event['end_timestamp']."',".$info_exchange['ID_USER'].",".time().",".time().",'".addslashes($event['location'])."','','',2,2,'',1,'".$event['id']."','".$event['change_key']."','".$calendar['ID_CALENDAR_IN_EXCHANGE']."')");
								$id_event=$db->lastInsertedId();	
								$db->execute('UPDATE comm_event SET ID_EVENT_PARENT = '.$id_event.' WHERE ID = '.$id_event);
								$db->execute('INSERT INTO comm_event_participants (ID_EVENT,ID_USER,VALIDATE,SENDER) VALUES ('.$id_event.','.$info_exchange['ID_USER'].',1,0)');
							}						
						}	
						//echo '<br><br>';
					}				
				}
			}
		}
		if(count($list_event_from_exchange_present)!=0){
			foreach($list_event_from_exchange_present as $event_exchange){
				echo 'boom '.$event_exchange;
				delete_event($event_exchange);
			}
		}
	}
}
echo 'OK';
?>