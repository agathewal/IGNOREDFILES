<?
require_once('../config/configuration.php');
$donnees=array();
if(isset($_POST['help_stat']) && is_numeric($_POST['help_stat'])){
	$new_stat=1;
	if($_POST['help_stat']==1)$new_stat=0;
	
	$db->query('update comm_admin_user set HELP_STATUT = '.$new_stat.' where ID = '.$_SESSION['id']);	
	$_SESSION['help']=$new_stat;
	$donnees['success']=1;
}
else $donnees['success']=0;

echo json_encode($donnees);
?>