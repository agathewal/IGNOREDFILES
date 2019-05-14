<?
$id_module=14;
require_once('../config/configuration.php');
require_once ROOT_DIR.'config/ThumbLib.inc.php';

$msg='';
$ok=0;
if(isset($_POST['urlfile']) && isset($_POST['dir']) && isset($_POST['x']) && isset($_POST['x2']) && isset($_POST['y']) && isset($_POST['y2']) && isset($_POST['choix_crea'])){

	$width=$_POST['x2']-$_POST['x'];
	$height=$_POST['y2']-$_POST['y'];
	$info_file=pathinfo($_SERVER['DOCUMENT_ROOT'].$_POST['urlfile']);	
	$thumb= PhpThumbFactory::create('../tmp/'.$_SESSION['img_aleatoire'].'.'.$info_file['extension']);
	$thumb->crop($_POST['x'],$_POST['y'],$width, $height);	
	
	if($_POST['choix_crea']=='1'){
		$file=$_POST['urlfile'];
		$thumb->save($_SERVER['DOCUMENT_ROOT'].$_POST['urlfile']);
	}else{
		$file=$_POST['dir'].$info_file['filename'].'_'.mt_rand(0,100000).'.'.$info_file['extension'];
		$thumb->save($_SERVER['DOCUMENT_ROOT'].$file);
	}
	
	unlink('../tmp/'.$_SESSION['img_aleatoire'].'.'.$info_file['extension']);
		
	$msg.='<b>Le recadrage a été effectué !</b><br/><a href="javascript:ferme_fenetre(\''.$_POST['field'].'\',\''.$file.'\');">Cliquez ici pour fermer la fenêtre et actualiser la miniature.</a><br/><img src="'.$file.'">';
	$ok=1;
	
}
else if(isset($_GET['urlfile']) && $_GET['urlfile']!="" && isset($_GET['dir']) && isset($_GET['width']) && is_numeric($_GET['width']) && isset($_GET['height']) && is_numeric($_GET['height'])){

	if($_SESSION['dir_upload']!='' && !eregi('/'.$_SESSION['dir_upload'].'/medias/',$_GET['dir'])){
		$_GET['dir']=str_replace('/medias/','/'.$_SESSION['dir_upload'].'/medias/',$_GET['dir']);
	}
	
	$msg.='<b style="color:#EE3322;">Pour recadrer votre image, déplacer la sélection en maintenant le clic gauche enfoncé puis sauvegarder.</b><br/><br/>';

	$width_need=$_GET['width'];
	$height_need=$_GET['height'];
	$_GET['urlfile']=urldecode($_GET['urlfile']);
	$_GET['dir']=urldecode($_GET['dir']);
	
	/*Etape 1 capture des dimensions*/
	list($width, $height, $type, $attr) = getimagesize($_SERVER['DOCUMENT_ROOT'].$_GET['urlfile']);
	$width=floor($width);
	$height=floor($height);
		
	//echo $width.$height;
	
	$ratio_x=$width/$width_need;
	$ratio_y=$height/$height_need;
	
	if($ratio_x>$ratio_y){//si le ratio supérieur est le x alors on se calle par rapport au y
		$height_final=$height_need;
		$width_final=ceil($width/$ratio_y);
	}
	else{
		$width_final=$width_need;
		$height_final=ceil($height/$ratio_x);
	}
	
	//zone de sélection paramètre gauche x et y
	
	$x_crop=floor(($width_final-$width_need)/2);
	$y_crop=floor(($height_final-$height_need)/2);
	
	//echo $width_final.'|'.$height_final;
	if(!isset($_SESSION['img_aleatoire']))$_SESSION['img_aleatoire']=mt_rand(0,100000);
	
	$info_file=pathinfo($_GET['urlfile']);	
	$thumb= PhpThumbFactory::create($_SERVER['DOCUMENT_ROOT'].$_GET['urlfile']);
	$thumb->resize($width_final, $height_final);
	$thumb->save('../tmp/'.$_SESSION['img_aleatoire'].'.'.$info_file['extension']);	
	
	$msg.='<img src="../tmp/'.$_SESSION['img_aleatoire'].'.'.$info_file['extension'].'" id="cropbox"/><br/>';
}
else{
	echo "Image non trouvée !";
	die();
}
?>
<html>
<head>
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/jquery.Jcrop.css" type="text/css"  media="screen" />
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/crop.css" type="text/css"  media="screen" />
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery.Jcrop.js"></script>
<script type='text/javascript'>
function ferme_fenetre(field,url_file){
	window.opener.document.getElementById(field).value=url_file;
	window.opener.refresh_visu_<?
	if(isset($_POST['field'])) echo $_POST['field'];
	else if(isset($_GET['field'])) echo $_GET['field'];
	?>(url_file,field);
	window.close();	
	window.close();	
}

jQuery(function() {

	$('#cropbox').Jcrop({
		setSelect: [ <?echo $x_crop;?>,<?echo $y_crop;?>, <?echo ($width_need+$x_crop);?>, <?echo ($height_need+$y_crop);?> ],
		onChange: showCoords,
		onSelect: showCoords,
		allowResize:false
	});
	
	function showCoords(c){
		jQuery('#x').val(c.x);
		jQuery('#y').val(c.y);
		jQuery('#x2').val(c.x2);
		jQuery('#y2').val(c.y2);

	}
});

</script>
</head>
<body><br/>
<div align="center">

<?echo $msg;

if(!$ok){?>
<form method="post" action='?action=crop_r'>
	<input type="hidden" size="4" id="x" name="x" />
	<input type="hidden" size="4" id="y" name="y" />
	<input type="hidden" size="4" id="x2" name="x2" />
	<input type="hidden" size="4" id="y2" name="y2" />
	<input type='hidden' name='urlfile' value='<?echo format($_GET['urlfile']);?>'>
	<input type='hidden' name='dir' value='<?echo format($_GET['dir']);?>'>
	<input type='hidden' name='field' value='<?echo format($_GET['field']);?>'>
	<br/>
	<b><input type='radio' value='0' name='choix_crea' checked='checked'> Créer une nouvelle image <input type='radio' name='choix_crea' value='1'> &Eacute;craser l'image existante</b><br/>
	<div class="submit"><input type='submit' value="Sauvegarder l'image"></div>
</form>
<?}?>
</div>
</body>
</html>