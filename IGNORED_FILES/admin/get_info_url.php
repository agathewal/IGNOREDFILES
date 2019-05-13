<?
require_once('../config/configuration.php');
$donnees=array();
$donnees['content']="";
if(isset($_POST['id_data']) && is_numeric($_POST['id_data'])){
	$content=file_get_contents('http://www.demo-cms.fr/cms/admin/get_info.php?id_data='.$_POST['id_data']);
	$content=str_replace('||',',',$content);
	$donnees['content']=$content;
}
echo json_encode($donnees);
?>