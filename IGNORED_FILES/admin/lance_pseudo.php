<?
require_once('../config/configuration.php');
$donnees=array();
if(isset($_POST['email'])){
	$nb=$db->countOf("comm_user","EMAIL = '".$_POST['email']."'");
	if($nb>=1)$donnees['libre']=0;
	else $donnees['libre']=1;
}
else $donnees['libre']=0;
echo json_encode($donnees);
?>