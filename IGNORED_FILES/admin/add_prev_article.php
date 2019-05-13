<?
require_once('../config/configuration.php');
$donnees=array();
if(isset($_POST['id_article']) && is_numeric($_POST['id_article']) && isset($_POST['titre_article']) && $_POST['titre_article']!="" && isset($_POST['text_article']) && $_POST['text_article']!="" ){
	$db->execute('DELETE FROM cms_prev_article WHERE ID = '.$_POST['id_article']);
	$db->execute('INSERT INTO cms_prev_article (`ID`,`TITRE`,`TEXT`) VALUES ('.$_POST['id_article'].',\''.addslashes($_POST['titre_article']).'\',\''.addslashes($_POST['text_article']).'\') ');
	$donnees['titre_article']=$_POST['titre_article'];
	$donnees['success']=1;
}
else $donnees['success']=0;

echo json_encode($donnees);
?>