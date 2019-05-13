<?
include('../config/configuration.php');
if(isset($_GET['lat']) && is_numeric($_GET['lat']) && isset($_GET['lon']) && is_numeric($_GET['lon']) &&  isset($_GET['numalea']) && is_numeric($_GET['numalea']) && isset($_GET['field']) && $_GET['field']!=""){
$html_tablo=get_google_maps_api();
$html_tablo.=get_admin_location($_GET['lat'],$_GET['lon'],$_GET['field']);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/fct.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.corner.js"></script>
<title>Géolocalisation</title>
</head>
<body style="background-image:none;">
<? echo $html_tablo; ?>
</body>
</html>
<?}else{
echo "Accès Interdit !";
}
?>