<?php
require_once("../config.php");



if($page==null) $page = $_GET['id'];
if(!is_numeric($page)) exit; // extra precaution


$sql = "SELECT * FROM comm_wiki_page INNER JOIN comm_wiki_node ON comm_wiki_node.node_id=comm_wiki_page.node_id WHERE comm_wiki_node.node_id=$page";
$result = $db->query($sql) or die("Database Error ".mysql_error());

if(mysql_num_rows($result)==0){
	include('language.php');
	echo $language->menu->edit;
	exit;
}

$pid = mysql_result($result, 0, 'parent_id');

if($pid > 0)
	echo stripslashes(mysql_result($result, 0, 'page_text'));

?>