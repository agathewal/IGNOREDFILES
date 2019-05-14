<?
$donnees=array();
$donnees['success']=0;

require_once '../config/configuration.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once 'EWS_Exception.php';
require_once 'ExchangeWebServices.php';
require_once 'NTLMSoapClient.php';
require_once 'NTLMSoapClient/Exchange.php';
spl_autoload_register('ews_autoloader');

$username="tao\tao-vision";
$password="jYcw23!ktqjtf";
$server="mobile.tape-a-loeil.com";
$type_server=$list_server_exchange[0]['FOR_CONNEX'];


$client = new ExchangeWebServices($server, $username, $password, $type_server);

$request = new EWSType_FindFolderType();
$request->Traversal = EWSType_FolderQueryTraversalType::SHALLOW; // use EWSType_FolderQueryTraversalType::DEEP for subfolders too
$request->FolderShape = new EWSType_FolderResponseShapeType();
$request->FolderShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
// configure the view
$request->IndexedPageFolderView = new EWSType_IndexedPageViewType();
$request->IndexedPageFolderView->BasePoint = 'Beginning';
$request->IndexedPageFolderView->Offset = 0;
$request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
// use a distinguished folder name to find folders inside it
$request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
$request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::MESSAGE_FOLDER_ROOT;

// request
try{
	$response = $client->FindFolder($request);
	//echo '<pre>'.print_r($response, true).'</pre>';
	if($response->ResponseMessages->FindFolderResponseMessage->ResponseCode=='NoError'){
		$donnees['success']=1;
		$donnees['total_items_view']=$response->ResponseMessages->FindFolderResponseMessage->RootFolder->TotalItemsInView;
	}else{
		$donnees['success']=3;
		$donnees['error']='Connexion établie au serveur mais aucune donnée trouvée';
	}
	
}
catch (Exception $e) {
	//echo '<pre>'.$e->getMessage();.'</pre>';
	$donnees['success']=2;
	$donnees['error']=$e->getMessage();
}


echo json_encode($donnees);
?>