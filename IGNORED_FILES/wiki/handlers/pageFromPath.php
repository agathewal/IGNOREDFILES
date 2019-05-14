<?php
require_once("../config.php");
require_once("../class/node.class.php");

$path = $_POST['path'];

$lang=$_POST['lang'];
if(strlen($lang) > 2) return;

$NodeHandler = new Node($con,$lang);
echo $NodeHandler->NodeFromPath($path);

?>