<?php

$text = $_POST['tags'];// do this before config or it won't trim properly

require_once("../config.php");
require_once("../class/tags.class.php");
require_once("../class/revision.class.php");

$id = $_GET['id'];
$uid = $_GET['uid'];
if(!is_numeric($id) || !is_numeric($uid)) exit;
$lang=$_GET['lang'];
if(strlen($lang) > 2) return;


$tagger = new Tag($con,$id,$lang);
$from = $tagger->getTags("csv"); // save for history update
$tagger->saveTags($text);

// if change was made, update revision history
if($from != $text){
	$rev = new Revision($con,$id,$uid,$CFG_RETURN_ADDRESS,$lang);	
	$rev->save(mysql_real_escape_string($from),'tag','',mysql_real_escape_string($text));
}

$json = "{'response':'ok'}";
echo $json;

?>


