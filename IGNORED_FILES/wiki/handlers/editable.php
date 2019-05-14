<?php
require_once("../config.php");
include("./editcheck.php");

$page = $_POST['id'];
if(!is_numeric($page)) exit;

$can_edit=0;
$actual_node_req=$db->query('SELECT * FROM comm_wiki_node WHERE node_id = '.$page);
if(mysql_num_rows($actual_node_req)!=0){
	$info_page=mysql_fetch_array($actual_node_req);
	if($info_page['id_user']==$_SESSION['id_comm']){
		$can_edit=1;
	}
}

echo $can_edit;
?>
