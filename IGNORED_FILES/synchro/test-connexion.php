<?
$donnees=array();
$donnees['success']=0;
if(isset($_POST['login_exchange']) && $_POST['login_exchange']!='' && isset($_POST['pwd_exchange']) && $_POST['pwd_exchange']!='' && isset($_POST['adress_exchange']) && $_POST['adress_exchange']!='' && isset($_POST['type']) && is_numeric($_POST['type'])){

	require_once '../config/configuration.php';
	require_once 'EWS_Exception.php';
	require_once 'ExchangeWebServices.php';
	require_once 'NTLMSoapClient.php';
	require_once 'NTLMSoapClient/Exchange.php';
	spl_autoload_register('ews_autoloader');

	$username=$_POST['login_exchange'];
	$password=$_POST['pwd_exchange'];
	$server=$_POST['adress_exchange'];
	$type_server=$list_server_exchange[$_POST['type']]['FOR_CONNEX'];

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
		if($response->ResponseMessages->FindFolderResponseMessage->ResponseCode=='NoError'){
			$donnees['success']=1;
			$donnees['total_items_view']=$response->ResponseMessages->FindFolderResponseMessage->RootFolder->TotalItemsInView;
		}else{
			$donnees['success']=3;
			$donnees['error']='Connexion établie au serveur mais aucune donnée trouvée';
		}
		//echo '<pre>'.print_r($response, true).'</pre>';
	}
	catch (Exception $e) {
		$donnees['success']=2;
		$donnees['error']=$e->getMessage();
	}

	
}
echo json_encode($donnees);
?>