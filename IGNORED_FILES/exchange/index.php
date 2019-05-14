<?
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Paris');//Configuration du temps serveur
require_once 'EWS_Exception.php';
require_once 'ExchangeWebServices.php';
require_once 'NTLMSoapClient.php';
require_once 'NTLMSoapClient/Exchange.php';

function ews_autoloader($className) {
	//echo $className;
  if($className != 'EWS_Exception') {
    $classPath = str_replace('_','/',$className);
  }
  //echo "/".$classPath.".php";
  if(file_exists($classPath.".php")) {
  //  echo 'lala';
	include($classPath.".php");
  }
}

spl_autoload_register('ews_autoloader');

$nb_calendar_dispo=0;
$list_calendar=array();
$list_event=array();

$server = 'exchange2010-demo.com';
$username = 'chris.caron@exchange2010-demo.com';
$password = 'ulPI28uO6r';
$type_server = 'Exchange2010';

/*
$server = 'mobile.tape-a-loeil.com';
$username = 'tao\tao-vision';
$password = 'jYcw23!ktqjtf';
$type_server = 'Exchange2010';*/
 
$client = new ExchangeWebServices($server, $username, $password, $type_server);

/*
Récupération des dossiers ! 
*/


/*
STEP 1 -> On compte le nombre de calendriers
*/

$request = new EWSType_FindFolderType();
$request->Traversal = EWSType_FolderQueryTraversalType::DEEP; // use EWSType_FolderQueryTraversalType::DEEP for subfolders too
$request->FolderShape = new EWSType_FolderResponseShapeType();
$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
$request->IndexedPageFolderView->BasePoint = 'Beginning';
$request->IndexedPageFolderView->Offset = 0;
$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_FOLDER_ROOT;
$response = $client->FindFolder($request);
$nb_calendar_dispo=count($response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder);
/*var_dump($response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder);
echo $response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder->FolderId->Id;
echo '<pre>'.print_r($response, true).'</pre>';
print_r($response->ResponseMessages, true);
*/
if($nb_calendar_dispo==1){
	$list_calendar[]=$response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder;
}else if ($nb_calendar_dispo>1){
	foreach($response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder as $calendar){
		$list_calendar[]=$calendar;
	}
}else{
	echo "Pas de calendrier trouvé!";
	die();
}
echo '<pre>'.print_r($list_calendar, true).'</pre>';
/*foreach($list_calendar as $calendar){
	echo '<hr/> Calendrier nommé : '.$calendar->DisplayName;
	echo ' ('.$calendar->FolderId->Id.')';
	echo '-';
	echo ' ('.$calendar->FolderId->ChangeKey.')<br><br><br><br>';
}*/

//echo $list_calendar[2]->FolderId->Id;
/*
Récupération des évènements ! 
*/

foreach($list_calendar as $calendar){

	echo '<hr/> Calendrier nommé : '.$calendar->DisplayName;
	echo ' ('.$calendar->FolderId->Id.')';
	echo '-';
	echo ' ('.$calendar->FolderId->ChangeKey.')<br><br><br><br>';
	
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
	$request->CalendarView->StartDate = '2012-01-01T15:18:34+03:00'; // an ISO8601 date e.g. 2012-06-12T15:18:34+03:00
	$request->CalendarView->EndDate = '2013-12-10T15:18:34+03:00';// an ISO8601 date later than the above

	// Only look in the "calendars folder"
	/*$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
	$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
	$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::CALENDAR;*/

	$request->ParentFolderIds =new EWSType_TargetFolderIdType();
	$request->ParentFolderIds->FolderId = new EWSType_FolderIdType();
	$request->ParentFolderIds->FolderId->Id = $calendar->FolderId->Id;

	// Send request
	$response = $client->FindItem($request);
	/*echo '<pre>';
	print_r($response);
	echo '</pre>';*/
	
	// Loop through each item if event(s) were found in the timeframe specified
	$nb_event=$response->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView;
	
	echo "Nombre d'évènements : ".$nb_event.'<br><br><br><br>';
	
	if($nb_event==1){

		$events = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
		//var_dump($events);
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
			//var_dump($event);
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
	else {
		// No items returned
	}
	
}

echo '<pre>';
print_r($list_event);
echo '</pre>';

/*
Détails des dossiers ! 
*/
/*
 [FolderId] => stdClass Object
	(
		[Id] => AAMkADQ3ZWYxYzQ4LTM5ODctNDQyZC04N2I1LWJiNWIyOTliMDQ4OQAuAAAAAABaNB9n3smLSZdGM+SC6aHwAQD1KpO+V2m1TZVr1wm5Cm0oAAAAsUSlAAA=
		[ChangeKey] => BAAAABYAAAD1KpO+V2m1TZVr1wm5Cm0oAAAAsUTE
	)

[ParentFolderId] => stdClass Object
	(
		[Id] => AAMkADQ3ZWYxYzQ4LTM5ODctNDQyZC04N2I1LWJiNWIyOTliMDQ4OQAuAAAAAABaNB9n3smLSZdGM+SC6aHwAQD1KpO+V2m1TZVr1wm5Cm0oAAAAsUSUAAA=
		[ChangeKey] => AQAAAA==
	)
*/
/*
// Start building the request.
$request = new EWSType_CreateItemType();
$request->Items = new EWSType_NonEmptyArrayOfAllItemsType();
$request->Items->CalendarItem = new EWSType_CalendarItemType();

// Set the subject.
$request->Items->CalendarItem->Subject = 'Basic Calendar Item Insertion';

// Set the start and end times. For Exchange 2007, you need to include the timezone offset.
// For Exchange 2010, you should set the StartTimeZone and EndTimeZone properties. See below for
// an example.
$date = new DateTime('8:00 AM');
$request->Items->CalendarItem->Start = $date->format('c');
$date->modify('+1 hour');
$request->Items->CalendarItem->End = $date->format('c');

// Set no reminders
$request->Items->CalendarItem->ReminderIsSet = false;

// Or use this to specify when reminder is displayed (if this is not set, the default is 15 minutes)
//$request->Items->CalendarItem->ReminderMinutesBeforeStart = 15;

// Build the body.
$request->Items->CalendarItem->Body = new EWSType_BodyType();
$request->Items->CalendarItem->Body->BodyType = EWSType_BodyTypeType::HTML;
$request->Items->CalendarItem->Body->_ = 'This is <b>the</b> body';

// Set the item class type (not required).
$request->Items->CalendarItem->ItemClass = new EWSType_ItemClassType();
$request->Items->CalendarItem->ItemClass->_ = EWSType_ItemClassType::APPOINTMENT;

// Set the sensativity of the event (defaults to normal).
$request->Items->CalendarItem->Sensitivity = new EWSType_SensitivityChoicesType();
$request->Items->CalendarItem->Sensitivity->_ = EWSType_SensitivityChoicesType::NORMAL;

// Add some categories to the event.
$request->Items->CalendarItem->Categories = new EWSType_ArrayOfStringsType();
$request->Items->CalendarItem->Categories->String = array('Testing', 'php-ews');

// Set the importance of the event.
$request->Items->CalendarItem->Importance = new EWSType_ImportanceChoicesType();
$request->Items->CalendarItem->Importance->_ = EWSType_ImportanceChoicesType::NORMAL;

// Don't send meeting invitations.
$request->SendMeetingInvitations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

$response = $client->CreateItem($request);

echo '<pre>'.print_r($response, true).'</pre>';*/
?>