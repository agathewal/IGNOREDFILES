<?
$id_module=1;
require_once('../config/configuration.php');
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
</head>
<body style='background-image:none;'>
<?

if(is_numeric($_GET['id'])){

	$req_droit=$db->query('select * from comm_module_user,comm_module where comm_module.ID=comm_module_user.ID_MODULE AND ID_USER = '.$_GET["id"]);
	while($don=mysql_fetch_array($req_droit)){
		echo "<span style='font-weight:bold;color:#000000;line-height:25px;'>".$don['LIBELLE'].'</span><br/>';
	}
}
?>
</body>
</html>