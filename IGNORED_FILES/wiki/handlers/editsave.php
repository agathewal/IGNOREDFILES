<?php
require_once("../config.php");
require_once("../class/revision.class.php");

$page = $_POST['id'];
if(!is_numeric($page)) exit; // something's wrong

$text = $_POST['text']; // prepare for database
$uid = $_SESSION['id_comm'];
$ip = '';
$comment =$_POST['comment'];
$path = $_POST['path'];
if($path != ''){
	
	// create a new page from the path.
	require_once("../class/node.class.php");
	$NodeHandler = new Node($con,$_POST['lang']);
	$NodeHandler->position = 'in';
	$NodeHandler->comment = '';
	$NodeHandler->setUid($uid);
	$NodeHandler->name = $NodeHandler->PageFromPath($path); 
	$NodeHandler->target = $NodeHandler->ParentFromPath($path); // parent id
	$NodeHandler->ip=$_SERVER['REMOTE_ADDR'];
	$NodeHandler->NewNode(); // add the new node
	$NodeHandler->UpdateHistory('add'); // update
	$page = $NodeHandler->NodeFromPath($path); //
	$NodeHandler->Subscriptions($CFG_RETURN_ADDRESS, $action); 

}

$lang="fr";
$sql = "SELECT page_text FROM comm_wiki_page WHERE node_id=$page";
$result = $db->query($sql);
$from = '';
if(mysql_num_rows($result) > 0){

	$from = addslashes(mysql_result($result,0,'page_text')); // they get removed when selected
	
	$sql = "UPDATE comm_wiki_page SET page_text='$text' WHERE node_id=$page";
	$result = $db->query($sql);
	
}else{

	// get label
	$sql = "SELECT label FROM comm_wiki_node WHERE node_id=$page";
	$result = $db->query($sql);
	$label = mysql_result($result, 0, 'label');
	//echo $label;
	// create the new page
	$sql = "INSERT INTO comm_wiki_page (node_id,label,page_text,locked) ";
	$sql .= "VALUES ($page,'$label','$text',0) ";
	$result = $db->query($sql);

}

$sql = "SELECT page_text FROM comm_wiki_page WHERE node_id=$page";    

$result = $db->query($sql);
$to = addslashes(mysql_result($result,0,'page_text'));
// TODO: can this be done without updated then comparing?

if($to==$from){ // no need for history
	include('getpage.php');
	exit;
}
// revision
$rev = new Revision($con,$page,$uid,$CFG_RETURN_ADDRESS,$lang);
$rev->save($from,'page',$comment,$to);

include('getpage.php');
?>

