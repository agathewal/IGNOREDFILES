<?php
require_once("../config.php");
include("htmldiff.php");

$from = intval($_POST['from']);
$to = intval($_POST['to']);
$page = intval($_POST['page']);
$type = $_POST['type'];
$lang=$_POST['lang'];

if(strlen($lang) > 2) return;

echo html_diff($from, $to, $page, $type);

?>