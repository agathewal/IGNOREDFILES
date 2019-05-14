<?php

$text = $_POST['text'];
$lang=$_POST['lang'];
if(strlen($lang) > 2) return;

require_once("../config.php");
include("htmldiff.php");

$id = intval($_POST['id']);
	
$sql = "SELECT page_text FROM comm_wiki_page WHERE node_id=$id";
$result = $db->query($sql) or die("Database Error - Unable to save page.");

$current = '';
if(mysql_num_rows($result) > 0)
	$current = stripslashes(mysql_result($result,0,'page_text'));
echo html_diff($current, $text, $id, 'page', true);

?>