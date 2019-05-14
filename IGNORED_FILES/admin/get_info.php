<?
require_once('../config/configuration.php');
$donnees=array();
if(isset($_GET['id_data']) && is_numeric($_GET['id_data'])){
	$req=$db->query('select * from cms_temp_data where ID = '.$_GET['id_data']);
	if(mysql_num_rows($req)!=0){		
		$don=mysql_fetch_array($req);
		echo stripslashes($don['DATA']);
	}
}
?>