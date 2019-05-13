<?
$id_module=27;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='modif'){
	if(isset($_POST['filepath']) && $_POST['filepath']!=""){
		set_c('LOGO',$_POST['filepath']);
		$_SESSION['notification'][]=array(1,"Logo","Le logo a été modifié.");
		header('location:logo.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Logo","Veuillez sélectionner le logo");
		$action='add';
	}
}
?>
<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Votre logo</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
</div>
<div class="clear"></div>
<div id="step1_admin" >
<form method="post" action="?action=modif" name="form_logo">
<div>La taille maximale conseillée pour votre logo est 400 x 200 Pixels. Si votre logo dépasse ces dimensions, il est automatiquement redimensionné sur le site internet.</div>
<div style='width:203px;margin-top:10px;'>
<?
$dir_upload2=ADD_DIR.'/medias/logo/';
echo form_picture(400,200,'filepath',get_c('LOGO'),$dir_upload2,"Sélectionner<br/>mon logo",array(
		"THUMB_WIDTH"=>100,
		"THUMB_HEIGHT"=>100,
		"TEXT_WIDTH"=>80,
		"TOTAL_WIDTH"=>203,
		"TOTAL_HEIGHT"=>68),0);
?>			
<div class="bordure_menu" style='width:155px;float:left;margin-top:20px;' onclick="javascript:document.forms['form_logo'].submit();">
	<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
		<p>Modifier le logo</p>
	</div>
</div>
</div>
<?
include('footer.php');
?>