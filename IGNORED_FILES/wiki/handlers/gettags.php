<?php

require_once("../config.php");
require_once("../class/tags.class.php");

$id = $_POST['id'];
if(!is_numeric($id)) exit;
$lang = $_POST['lang'];
if(!preg_match('/[a-z]{2}/',$lang)) exit;

$tagger = new Tag($con,$id,$lang);
$text = $tagger->getTags("csv");

echo $text;

?>