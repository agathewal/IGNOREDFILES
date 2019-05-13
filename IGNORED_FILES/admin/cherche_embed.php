<?
require_once('../config/configuration.php');
$donnees=array();
$donnees['succes']=0;
if(isset($_POST['id'])){
	$req=$db->query('select * from cms_video where ID = '.$_POST['id']);
	if(mysql_num_rows($req)!=0){
		$don=mysql_fetch_array($req);
		$donnees['succes']=1;
		$donnees['code_embed']=stripslashes($don['EMBED']);
	}
}
echo json_encode($donnees);
?>