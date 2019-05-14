<?
$id_module=31;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='modif'){

	set_c('SMT_BANNER',$_POST['filepath']);
	set_c('SMT_TITLE',addslashes($_POST['title']));

	$_SESSION['notification'][]=array(1,"Smartphone - Configuration","Les changements ont été effectué.");
	header('location:smart-config.php');	
	die();
}
?>
<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Smartphone - Configuration</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
</div>
<div class="clear"></div>
<div id="step1_admin" >
<form method="post" action="?action=modif" name="form_logo">
<div style="margin-top:10px;">
	Titre de la page d'accueil Smartphone
</div>
<div style="margin-top:5px;">
	<input type="text" name="title" value="<? echo format(get_c('SMT_TITLE')); ?>" style="width:50%;">
</div>
<div class="clear"></div>
<div style='margin-top:10px;'>La taille maximale conseillée pour votre bannière Smartphone est 320 x 83 Pixels. Si votre bannière dépasse ces dimensions, il est automatiquement redimensionné sur le site internet.</div>
<div style='width:203px;margin-top:10px;'>
<?
$dir_upload2=ADD_DIR.'/medias/banner/';
echo form_picture(320,83,'filepath',get_c('SMT_BANNER'),$dir_upload2,"Sélectionner<br/>la bannière",array(
		"THUMB_WIDTH"=>100,
		"THUMB_HEIGHT"=>100,
		"TEXT_WIDTH"=>80,
		"TOTAL_WIDTH"=>203,
		"TOTAL_HEIGHT"=>68),0);
?>	
<div class="clear"></div>
</form>
<div class="bordure_menu" style='width:220px;float:left;margin-top:20px;' onclick="javascript:document.forms['form_logo'].submit();">
	<div style='width:220px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
		<p>Sauvegarder les changements</p>
	</div>
</div>
</div>
<?
include('footer.php');
?>