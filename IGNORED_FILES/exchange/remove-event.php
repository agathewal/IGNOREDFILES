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
$id_event='AAMkAGEzOTA5NmQ5LTQ2OWItNDFkOS1hYWJmLTc2YWY0ZGQ5YWM2MABGAAAAAACnA6uuaiuEQoCoRLlKqeknBwAeHS4pfQx4TKz48xO2uDx5AAAAWT3pAAAeHS4pfQx4TKz48xO2uDx5AAAAWWlkAAA=';
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
$request->ItemIds->ItemId->Id = $id_event; //e.g. 

$response = $client->GetItem($request);
if($response->ResponseMessages->GetItemResponseMessage->ResponseCode=='NoError'){

	$ChangeKey=$response->ResponseMessages->GetItemResponseMessage->Items->CalendarItem->ItemId->ChangeKey;
	
	$request = new EWSType_DeleteItemType();
	$request->ItemIds->ItemId[0]->Id = $id_event;	    
	$request->DeleteType = EWSType_DisposalType::MOVE_TO_DELETED_ITEMS;
	$request->SendMeetingCancellations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_ONLY_TO_ALL;
	$response = $client->DeleteItem($request);
	
	echo '<pre>';
	print_r($response);
	echo '</pre>';

}

?>