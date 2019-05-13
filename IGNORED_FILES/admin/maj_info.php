<?
require_once('../config/configuration.php');
$donnees=array();
$donnees['succes']=0;
$donnees['req']='';
if(isset($_POST['id_data']) && isset($_POST['latitude']) && isset($_POST['longitude'])){
	$req=$db->query('select * from cms_temp_data where ID = '.$_POST['id_data']);
	if(mysql_num_rows($req)!=0){		
		$db->execute('UPDATE cms_temp_data SET DATA = \''.addslashes($_POST['latitude'].'||'.$_POST['longitude']).'\' WHERE ID = '.$_POST['id_data']);		
		//$donnees['req']='UPDATE cms_temp_data SET DATA = \''.addslashes($_POST['latitude'].'||'.$_POST['longitude']).'\' WHERE ID = '.$_POST['id_data'];
		$donnees['succes']=1;
	}else{
		$db->execute('INSERT INTO cms_temp_data (ID,DATA) VALUES ('.$_POST['id_data'].',\''.addslashes($_POST['latitude'].'||'.$_POST['longitude']).'\')');
		//$donnees['req']='INSERT INTO cms_temp_data (ID,DATA) VALUES ('.$_POST['id_data'].',\''.addslashes($_POST['latitude'].'||'.$_POST['longitude']).'\')';
		$donnees['succes']=1;
	}
}
echo json_encode($donnees);
?>