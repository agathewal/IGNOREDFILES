<?
header('Content-type: text/html; charset=utf-8');
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

/*
require_once 'EWSType/FindItemType.php';
require_once 'EWSType/ItemQueryTraversalType.php';
require_once 'EWSType/ItemResponseShapeType.php';
require_once 'EWSType/DefaultShapeNamesType.php';
require_once 'EWSType/CalendarViewType.php';
 */
 
function sendMail($client, $params) {
 
    $createItem->MessageDisposition = 'SendAndSaveCopy';
    $createItem->SavedItemFolderId->DistinguishedFolderId->Id = 'sentitems';
 
    $message->ItemClass = 'IPM.Note';
 
    if (empty($params['Subject'])) {
        $message->SubJect = 'No subject';
    } else {
        $message->Subject = $params['Subject'];
    }
 
    if (empty($params['BodyType'])) {
        $message->Body->BodyType = 'Text';
    } else {
        $message->Body->BodyType = $params['BodyType'];
    }
 
    if (empty($params['Body'])) {
        return false;
    }
    $message->Body->_ = $params['Body'];
 
    foreach ($params['Recipients'] as $mail) {
        $message->ToRecipients->Mailbox[]->EmailAddress = $mail;
    }
 
    $createItem->Items->Message[] = $message;
 
    try {
        // Send the request to create and send the e-mail item, and get the response.
        $createItemResponse = $client->CreateItem($createItem);
 
        // Determine whether the request was a success.
        if ($createItemResponse->ResponseMessages->CreateItemResponseMessage->ResponseClass == 'Error') {
            throw new Exception($createItemResponse->ResponseMessages->CreateItemResponseMessage->MessageText);
 
        } else {
            echo "Item was created";
        }
 
    } catch(Exception $e) {
        echo $e->getMessage();
    }
}

// A modifier �videmment

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
 
var_dump($client);
/*
Envoi d'un email 
*/
/*
sendMail($client, array(
    'Subject' => 'Test Chris',
    'BodyType' => 'HTML',
    'Body' => 'Salut tu reçois un email depuis mon compte exchange via une connexion spéciale avec le RSE ! '."\n",
    'Recipients' => array(
        'chris.caron@exchange2010-demo.com',
		'christophe.caron@nexinet.fr',
		'antoine.vermelle@orange.fr'
    )
));*/



/*
Récupération des emails
*/

/*
$request = new EWSType_FindItemType();

$request->ItemShape = new EWSType_ItemResponseShapeType();
$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::DEFAULT_PROPERTIES;

$request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;

$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::INBOX;

// sort order
$request->SortOrder = new EWSType_NonEmptyArrayOfFieldOrdersType();
$request->SortOrder->FieldOrder = array();
$order = new EWSType_FieldOrderType();
// sorts mails so that oldest appear first
// more field uri definitions can be found from types.xsd (look for UnindexedFieldURIType)
$order->FieldURI->FieldURI = 'item:DateTimeReceived'; 
$order->Order = 'Ascending'; 
$request->SortOrder->FieldOrder[] = $order;

$response = $client->FindItem($request);
echo '<pre>'.print_r($response, true).'</pre>';
*/

/*
Récupération des dossiers ! 
*/


/*
STEP 1
*/







$request = new EWSType_FindFolderType();
$request->Traversal = EWSType_FolderQueryTraversalType::DEEP; // use EWSType_FolderQueryTraversalType::DEEP for subfolders too
$request->FolderShape = new EWSType_FolderResponseShapeType();
$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

// configure the view
$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
$request->IndexedPageFolderView->BasePoint = 'Beginning';
$request->IndexedPageFolderView->Offset = 0;

$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();

// use a distinguished folder name to find folders inside it
/*$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_FOLDER_ROOT;
*/
$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_FOLDER_ROOT;
$response = $client->FindFolder($request);
echo '<pre>'.print_r($response, true).'</pre>';

$list_calendar=array();
$request = new EWSType_FindFolderType();
$request->Traversal = EWSType_FolderQueryTraversalType::DEEP; // use EWSType_FolderQueryTraversalType::DEEP for subfolders too
$request->FolderShape = new EWSType_FolderResponseShapeType();
$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;

// configure the view
$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
$request->IndexedPageFolderView->BasePoint = 'Beginning';
$request->IndexedPageFolderView->Offset = 0;

$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();

// use a distinguished folder name to find folders inside it
/*$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_FOLDER_ROOT;
*/
$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::CALENDAR;
// if you know exact folder id, then use this piece of code instead. For example
// $folder_id = 'AAKkADE4N2NkZDRjLWZjY2EtNDNlFy04MjFlLTkzODAyXTMyMGVmOABGAAAAAACO4PBzuy...';
// $request->ParentFolderIds->FolderId = new EWSType_FolderIdType();
// $request->ParentFolderIds->FolderId->Id = $folder_id;

// request
$response = $client->FindFolder($request);
echo '<pre>'.print_r($response, true).'</pre>';
/*echo '<pre>'.print_r($response, true).'</pre>';
echo '<pre>'.print_r($response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder, true).'</pre>';*/
if($response->ResponseMessages->FindFolderResponseMessage->RootFolder->TotalItemsInView>0){
	foreach($response->ResponseMessages->FindFolderResponseMessage->RootFolder->Folders->CalendarFolder as $calendar){
		
		$list_calendar[]=$calendar;
	}
}
/*echo '<pre>'.print_r($list_calendar, true).'</pre>';
foreach($list_calendar as $calendar){
	echo 'Lala / Lala <br>';
	echo $calendar->FolderId->Id;
	echo '<br>';
	echo $calendar->FolderId->ChangeKey;
}*/

//echo $list_calendar[2]->FolderId->Id;
/*
Récupération des évènements ! 
*/

foreach($list_calendar as $calendar){

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
	$request->CalendarView->StartDate = '2013-01-01T15:18:34+03:00'; // an ISO8601 date e.g. 2012-06-12T15:18:34+03:00
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
	echo '<pre>';
	print_r($response);
	echo '</pre>';
	$list_event=array();
	// Loop through each item if event(s) were found in the timeframe specified
	$nb_event=$response->ResponseMessages->FindItemResponseMessage->RootFolder->TotalItemsInView;
	if($nb_event==1){

		$events = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
		//var_dump($events);
		$event_to_add=array();
		//var_dump($event);
		$event_to_add['id'] = $events->ItemId->Id;
		$event_to_add['change_key'] = $events->ItemId->ChangeKey;
		$event_to_add['start'] = $events->Start;
		$event_to_add['end'] = $events->End;
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
			$event_to_add['start'] = $event->Start;
			$event_to_add['end'] = $event->End;
			$event_to_add['subject'] = $event->Subject;
			$event_to_add['location'] = $event->Location;
			$list_event[]=$event;
		}
		
	}
	else {
		// No items returned
	}
	
}

/*
echo '<pre>';
print_r($list_event);
echo '</pre>';
*/

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