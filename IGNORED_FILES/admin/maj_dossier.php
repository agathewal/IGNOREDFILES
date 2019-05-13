<?
require_once('../config/configuration.php');

$req=$db->query('select * from cms_nst');
while($don=mysql_fetch_array($req)){
	
	$diff=$don['RGT']-$don['LFT'];
	if($diff==1){
		echo $don['ID'];
		$db->execute('UPDATE cms_nst SET TYPE = 1 WHERE ID = '.$don['ID']);
	}
}
?>
