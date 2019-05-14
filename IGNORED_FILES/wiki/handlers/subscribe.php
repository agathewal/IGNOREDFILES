<?php
require_once("../config.php");

$page = $_POST['pid'];
$uid = $_POST['uid'];
if(!is_numeric($page) || !is_numeric($uid)) exit; 
$lang=$_POST['lang'];
if(strlen($lang) > 2) return;

$add = $_POST['val'] == "true" ? TRUE : FALSE;

if($add){
	$sql = "INSERT INTO `comm_wiki_subscription` VALUES('', $page, '$lang', $uid)";
	$result = $db->query($sql);
}else{
	$sql = "DELETE FROM `comm_wiki_subscription` WHERE page_id=$page AND user_id=$uid";
	$result = $db->query($sql);
}
?>