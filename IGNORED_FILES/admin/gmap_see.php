<?
include('../config/configuration.php');
if(isset($_GET['coord']) && $_GET['coord']!=''){
$html_tablo=get_google_maps_api();
$data=explode(',',$_GET['coord']);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery-1.4.2.min.js"></script>
<title>Géolocalisation</title>
</head>
<body style="background-image:none;">
<? 

echo $html_tablo; ?>
<script type="text/javascript">
var latitudee=<?echo $data[0];?>;
var longitudee=<?echo $data[1];?>;

function load_coord() {
	if (GBrowserIsCompatible()) {
	
		var map = new GMap2(document.getElementById("map_coord"));
		map.addControl(new GSmallMapControl());
		map.addControl(new GMapTypeControl());
		var center = new GLatLng(latitudee,longitudee);
		map.setCenter(center, 15);
		geocoder = new GClientGeocoder();
		var marker = new GMarker(center,{});  
		map.addOverlay(marker);
		
	}
}

</script>


<div align="center" id="map_coord" style="width: 100%; height: 457px; margin-top:5px;"><br/></div>
<script type='text/javascript'>
	$(document).ready(function() {
		load_coord();
	});
</script>

</body>
</html>
<?}
else{
	echo "Accès Interdit !";
}
?>