<?
$id_module=12;
require_once('../config/configuration.php');
$array_answer=array();
if(is_numeric($_POST['id_photo']) && $_POST['action']!=''){
	$success=0;
	
	if($_POST['action']=='get'){
		$req=$db->query("SELECT * FROM cms_photo WHERE ID = ".$_POST['id_photo']);
		$don=mysql_fetch_array($req);
		$_POST['text_modif']=strCut($don['TITLE'],250);
		$success=1;
		
	}
	if($_POST['action']=='modif' && $_POST['text_modif']!=""){
		$db->execute("UPDATE cms_photo SET TITLE = '".addslashes(nl2br(stripslashes($_POST['text_modif'])))."' WHERE ID = ".$_POST['id_photo']);
		$success=1;
	}
	if($_POST['action']=='delete'){
		$req=$db->query("SELECT * FROM cms_photo WHERE ID = ".$_POST['id_photo']);
		$don=mysql_fetch_array($req);
		
		$req=$db->query("SELECT * FROM cms_photo WHERE ID_GALERIE = ".$don['ID_GALERIE']." AND ORDRE > ".$don['ORDRE']);
		while($don2=mysql_fetch_array($req)){
			$db->execute('UPDATE cms_photo SET ORDRE = '.($don2['ORDRE']-1).' WHERE ID = '.$don2['ID']);
		}
		$db->execute("DELETE FROM cms_photo WHERE ID = ".$_POST['id_photo']);
		$success=1;
	}
	
	
	$array_answer['succes']=$success;
	$array_answer['text_modif']=$_POST['text_modif'];
	$array_answer['id_photo']=$_POST['id_photo'];
	$array_answer['action']=$_POST['action'];
}
echo json_encode($array_answer);
?>