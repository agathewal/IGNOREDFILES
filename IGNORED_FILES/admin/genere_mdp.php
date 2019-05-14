<?
require_once('../config/configuration.php');
$donnees=array();
$donnees['mdp']=GenerateRandomString(10);

echo json_encode($donnees);
?>