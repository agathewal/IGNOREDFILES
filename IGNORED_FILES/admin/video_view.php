<?
$id_module=14;
require_once('../config/configuration.php');
?>
<html>
<head></head>
<body>
<div align="center">
<?
if(isset($_GET['id'])){
	$req=$db->query('select * from cms_video where ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		$don_video=mysql_fetch_array($req);
		echo resize_embed(stripslashes($don_video['EMBED']),480,293);
	}else 'Vidéo non trouvée !';
}else 'Vidéo non trouvée !';
?>
</div>
</body>
</html>