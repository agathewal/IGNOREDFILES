<?
header('Content-type: text/html; charset=utf-8');
date_default_timezone_set('Europe/Paris');//Configuration du temps serveur
require_once 'EWS_Exception.php';
require_once 'ExchangeWebServices.php';
require_once 'NTLMSoapClient.php';
require_once 'NTLMSoapClient/Exchange.php';

function ews_autoloader($className) {
	if($className != 'EWS_Exception') {
		$classPath = str_replace('_','/',$className);
	}
	if(file_exists($classPath.".php")) {
		include($classPath.".php");
	}
}

spl_autoload_register('ews_autoloader');
// A modifier évidemment

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

// Start building the request.
$request = new EWSType_CreateItemType();
$request->Items = new EWSType_NonEmptyArrayOfAllItemsType();
$request->Items->CalendarItem = new EWSType_CalendarItemType();

// Set the subject.
$request->Items->CalendarItem->Subject = 'Basic Calendar Item Insertion';

// Set the start and end times. For Exchange 2007, you need to include the timezone offset.
// For Exchange 2010, you should set the StartTimeZone and EndTimeZone properties. See below for
// an example.
$date = new DateTime('10:00 AM');
$request->Items->CalendarItem->Start = $date->format('c');
$date->modify('+1 hour');
$request->Items->CalendarItem->End = $date->format('c');
$request->Items->CalendarItem->Location = "Lieu test";
// Set no reminders
$request->Items->CalendarItem->ReminderIsSet = false;

// Or use this to specify when reminder is displayed (if this is not set, the default is 15 minutes)
//$request->Items->CalendarItem->ReminderMinutesBeforeStart = 15;
// $request->SavedItemFolderId->DistinguishedFolderId->Id = 'calendar';
	
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
$request->Items->CalendarItem->Categories->String = array('RSE Name');

// Set the importance of the event.
$request->Items->CalendarItem->Importance = new EWSType_ImportanceChoicesType();
$request->Items->CalendarItem->Importance->_ = EWSType_ImportanceChoicesType::NORMAL;

// Don't send meeting invitations.
$request->SendMeetingInvitations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE;

$response = $client->CreateItem($request);

echo '<pre>'.print_r($response, true).'</pre>';
?>