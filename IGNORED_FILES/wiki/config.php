<?php
include(dirname(__FILE__).'/../config/configuration.php');
//This stops SQL Injection in POST vars 
foreach ($_POST as $key => $value) { 
	$_POST[$key] = mysql_real_escape_string(str_replace("\\","\\\\",$value)); 
} 

//This stops SQL Injection in GET vars 
foreach ($_GET as $key => $value) { 
	$_GET[$key] = mysql_real_escape_string(htmlspecialchars ($value,ENT_QUOTES,'UTF-8')); 
}

$con='';
?>