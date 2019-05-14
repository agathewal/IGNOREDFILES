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
$id_event='AAMkAGEzOTA5NmQ5LTQ2OWItNDFkOS1hYWJmLTc2YWY0ZGQ5YWM2MABGAAAAAACnA6uuaiuEQoCoRLlKqeknBwAeHS4pfQx4TKz48xO2uDx5AAAAWT3pAAAeHS4pfQx4TKz48xO2uDx5AAAAWWljAAA=';
$ChangeKey='';
/*
$server = 'mobile.tape-a-loeil.com';
$username = 'tao\tao-vision';
$password = 'jYcw23!ktqjtf';
$type_server = 'Exchange2010';*/
 
$client = new ExchangeWebServices($server, $username, $password, $type_server);

// Define the event to be updated.

$request = new EWSType_FindItemType();
$request->ItemShape = new EWSType_ItemResponseShapeType();
$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
$request->ItemIds->ItemId = new EWSType_ItemIdType();
$request->ItemIds->ItemId->Id = $id_event; //e.g. AAAeABFuZHJleS5rdXByaDFub3ZAZnAtbWFyaW5lLmNvbQBGAAAAAACO4PBzuyEJTYBp5uGaRLNjBwA14Y7YwAwNSKAQKDMKwnWjAAIuTYgHAACUyTGM6SD5So28BizklbQLAAK4LwQBAAA=

$response = $client->GetItem($request);
if($response->ResponseMessages->GetItemResponseMessage->ResponseCode=='NoError'){

	$ChangeKey=$response->ResponseMessages->GetItemResponseMessage->Items->CalendarItem->ItemId->ChangeKey;
	
	$request = new EWSType_UpdateItemType();
	$request->ConflictResolution = 'AlwaysOverwrite';
	$request->SendMeetingInvitationsOrCancellations = 'SendToNone';
	$request->ItemChanges = array();

	$change = new EWSType_ItemChangeType();
	$change->ItemId = new EWSType_ItemIdType();
	$change->ItemId->Id = $id_event;
	$change->ItemId->ChangeKey = $ChangeKey;

	$field = new EWSType_SetItemFieldType();
	$field->FieldURI = new EWSType_PathToUnindexedFieldType();
	$field->FieldURI->FieldURI = 'item:Subject';
	$field->CalendarItem = new EWSType_CalendarItemType();
	$field->CalendarItem->Subject = 'Test '.rand().' !';
	$change->Updates->SetItemField[] = $field;

	unset($field);
	$field = new EWSType_SetItemFieldType();
	$field->FieldURI = new EWSType_PathToUnindexedFieldType();
	$field->FieldURI->FieldURI = 'calendar:Location';
	$field->CalendarItem = new EWSType_CalendarItemType();
	$field->CalendarItem->Location = 'Changement de lieu : '.rand();
	$change->Updates->SetItemField[] = $field;

	unset($field);
	$field = new EWSType_SetItemFieldType();
	$field->FieldURI = new EWSType_PathToUnindexedFieldType();
	$field->FieldURI->FieldURI = 'item:Body';
	$field->CalendarItem = new EWSType_CalendarItemType();
	$field->CalendarItem->Body->BodyType = EWSType_BodyTypeType::HTML;
	$field->CalendarItem->Body->_ = 'J\'aime les chats'.rand();
	$change->Updates->SetItemField[] = $field;

	$request->ItemChanges[] = $change;
	$response = $client->UpdateItem($request);
	
	echo '<pre>';
	print_r($response);
	echo '</pre>';

}

?>