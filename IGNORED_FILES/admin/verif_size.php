<?
require_once('../config/configuration.php');
$retour=array();
$retour['success']=0;
$retour['url_photo']=$_POST['url_photo'];
$retour['width']=$_POST['width'];
$retour['height']=$_POST['height'];

if(isset($_POST['url_photo']) && $_POST['url_photo']!="" && is_numeric($_POST['width']) && is_numeric($_POST['height'])){
	if(file_exists($_SERVER['DOCUMENT_ROOT'].$_POST['url_photo'])){
	
		$retour['success']=1;
		
		list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].$_POST['url_photo']);
		$too=0;
		$width=floor($width);
		$height=floor($height);
		if($width>$_POST['width'])$too=1;
		else if($height>$_POST['height'])$too=1;
		
		$retour['too']=$too;
		$retour['width']=$width;
		$retour['height']=$height;
		
	}
}
echo json_encode($retour);
?>