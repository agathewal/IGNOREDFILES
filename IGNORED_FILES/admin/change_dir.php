<?
require_once('../config/configuration.php');

$old_dir='/newcom/newcom/faqcomm/';
$new_dir='/newcom/faqcomm/';

/*FAQ*/
$req=$db->query('select * from comm_faq_article');
while($don=mysql_fetch_array($req)){
	$db->execute("UPDATE comm_faq_article SET TEXT = '".str_replace(array($old_dir,'<hr>','<hr />'),array($new_dir,'',''),addslashes(stripslashes($don['TEXT'])))."' WHERE ID = ".$don['ID']);
}
?>