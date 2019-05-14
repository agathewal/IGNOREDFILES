<?php
require_once("../config.php");
require_once("../class/node.class.php");

$NodeHandler = new Node($con,$_GET['lang']);

$NodeHandler->histid = 0;
$NodeHandler->setTarget($_GET['target']);
$NodeHandler->user = $_GET['user'];
$NodeHandler->setUid($_GET['uid']);
$NodeHandler->ip=$_SERVER['REMOTE_ADDR'];

$NodeHandler->position = $_POST['position'];
$NodeHandler->name = $_POST['name'];
$NodeHandler->comment = $_POST['commentf'];
$NodeHandler->registered_only = $CFG_REGISTERED_ONLY;
$NodeHandler->json = "{'response':'Not yet implemented!'}";
$action = $_POST['action'];
$clip = $_GET['clip']; // clipboard, used for paste only
if(!is_numeric($clip) && $clip!='') exit;

switch($action){
	case 'addpage':
		$NodeHandler->NewNode();
		if($NodeHandler->histid <0){
			echo $NodeHandler->json;
			exit;
		}
		break;

	case 'paste':
		if($clip==$NodeHandler->target){
			echo "{'response':'Can not paste into self','node':'".$NodeHandler->target."'}";
			exit;
		}
		$response = $NodeHandler->RemoveNode($clip);
		if(!$response){
			echo $NodeHandler->json;
			exit;
			
		}

		$response = $NodeHandler->NewNode($clip);
		if($NodeHandler->histid <0){
			echo $NodeHandler->json;
			exit;
		}
		
		break;

	case 'remove':
		$response = $NodeHandler->RemoveNode($NodeHandler->target);
		if(!$response){
			echo $NodeHandler->json;
			exit;
			
		}
		break;

	case 'rename':
		$NodeHandler->Rename();
		break;

	case 'cut':
		// do nothing, handled in the client
		$NodeHandler->json="{'response':'ok','node':'".$NodeHandler->target."'}";
		break;
}

// Update history
if($action != 'cut'){
	if(!$NodeHandler->UpdateHistory($action)){
		echo $NodeHandler->json;
		exit;
	}
}

// subscriptions
if($action != 'cut')
	$NodeHandler->Subscriptions($CFG_RETURN_ADDRESS, $action);

echo $NodeHandler->json;

?>