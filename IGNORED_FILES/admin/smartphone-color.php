<?
$id_module=30;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='modif'){
	set_c('SMT_1_COLOR',$_POST['SMT_1_COLOR']);
	set_c('SMT_2_COLOR',$_POST['SMT_2_COLOR']);
	set_c('SMT_COLOR',$_POST['SMT_COLOR']);
	set_c('SMT_SHAD_COLOR',$_POST['SMT_SHAD_COLOR']);
	set_c('SMT_BANNER_1_COLOR',$_POST['SMT_BANNER_1_COLOR']);
	set_c('SMT_BANNER_2_COLOR',$_POST['SMT_BANNER_2_COLOR']);
	set_c('SMT_BANNER_COLOR',$_POST['SMT_BANNER_COLOR']);
	set_c('SMT_BANNER_SHAD_COLOR',$_POST['SMT_BANNER_SHAD_COLOR']);
	set_c('SMT_BANNER',$_POST['filepath']);
	set_c('SMT_TITLE',addslashes($_POST['title']));
	$_SESSION['notification'][]=array(1,"Smartphone","Les changements ont été effectué.");
	header('location:smartphone-color.php');	
	die();
}
?>
<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Smartphone</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
</div>
<div class="clear"></div>
<div id="step1_admin" >
<form method="post" action="?action=modif" name="form_logo">
<input type='hidden' name='SMT_1_COLOR' id='SMT_1_COLOR' value='<?echo get_c('SMT_1_COLOR');?>'>
<input type='hidden' name='SMT_2_COLOR' id='SMT_2_COLOR' value='<?echo get_c('SMT_2_COLOR');?>'>
<input type='hidden' name='SMT_COLOR' id='SMT_COLOR' value='<?echo get_c('SMT_COLOR');?>'>
<input type='hidden' name='SMT_SHAD_COLOR' id='SMT_SHAD_COLOR' value='<?echo get_c('SMT_SHAD_COLOR');?>'>
<input type='hidden' name='SMT_BANNER_1_COLOR' id='SMT_BANNER_1_COLOR' value='<?echo get_c('SMT_BANNER_1_COLOR');?>'>
<input type='hidden' name='SMT_BANNER_2_COLOR' id='SMT_BANNER_2_COLOR' value='<?echo get_c('SMT_BANNER_2_COLOR');?>'>
<input type='hidden' name='SMT_BANNER_COLOR' id='SMT_BANNER_COLOR' value='<?echo get_c('SMT_BANNER_COLOR');?>'>
<input type='hidden' name='SMT_BANNER_SHAD_COLOR' id='SMT_BANNER_SHAD_COLOR' value='<?echo get_c('SMT_BANNER_SHAD_COLOR');?>'>

<link rel="stylesheet" href="<?echo URL_DIR;?>/css/colorpicker.css" type="text/css" />
<script type="text/javascript" src="<?echo URL_DIR;?>/js/eye.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/utils.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/colorpicker.js"></script>
<script type="text/javascript">
jQuery(document).ready(function(){
	
	var SMT_1_COLOR='<?echo get_c('SMT_1_COLOR');?>';
	var SMT_2_COLOR='<?echo get_c('SMT_2_COLOR');?>';
	var SMT_COLOR='<?echo get_c('SMT_COLOR');?>';
	var SMT_SHAD_COLOR='<?echo get_c('SMT_SHAD_COLOR');?>';
	
	var SMT_BANNER_1_COLOR='<?echo get_c('SMT_BANNER_1_COLOR');?>';
	var SMT_BANNER_2_COLOR='<?echo get_c('SMT_BANNER_2_COLOR');?>';
	var SMT_BANNER_COLOR='<?echo get_c('SMT_BANNER_COLOR');?>';
	var SMT_BANNER_SHAD_COLOR='<?echo get_c('SMT_BANNER_SHAD_COLOR');?>';
	
	
	/*Fond*/
	jQuery('#picker').ColorPicker({color:'#'+SMT_1_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_1_COLOR=hex;
		jQuery('#SMT_1_COLOR').val(hex);
		jQuery('#couleur_fond').css({'background':'-moz-linear-gradient(center top , #'+hex+', #'+SMT_2_COLOR+') repeat scroll 0 0 #'+hex+''});
	}});
	
	jQuery('#picker2').ColorPicker({color:'#'+SMT_2_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_2_COLOR=hex;
		jQuery('#SMT_2_COLOR').val(hex);
		jQuery('#couleur_fond').css({'background':'-moz-linear-gradient(center top , #'+SMT_1_COLOR+', #'+hex+') repeat scroll 0 0 #'+SMT_1_COLOR+''});
	}});
	
	jQuery('#picker3').ColorPicker({color:'#'+SMT_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_COLOR=hex;
		jQuery('#SMT_COLOR').val(hex);
		jQuery('#couleur_fond').css({'color':'#'+hex});
	}});
	
	jQuery('#picker4').ColorPicker({color:'#'+SMT_SHAD_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_SHAD_COLOR=hex;
		jQuery('#SMT_SHAD_COLOR').val(hex);
		jQuery('#couleur_fond').css({'text-shadow':'0 1px 0 #'+hex});
	}});
	
	
	/*bannière*/
	jQuery('#picker5').ColorPicker({color:'#'+SMT_BANNER_1_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_BANNER_1_COLOR=hex;
		jQuery('#SMT_BANNER_1_COLOR').val(hex);
		jQuery('#couleur_banniere').css({'background':'-moz-linear-gradient(center top , #'+hex+', #'+SMT_BANNER_2_COLOR+') repeat scroll 0 0 #'+hex+''});
	}});
	
	jQuery('#picker6').ColorPicker({color:'#'+SMT_BANNER_2_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_BANNER_2_COLOR=hex;
		jQuery('#SMT_BANNER_2_COLOR').val(hex);
		jQuery('#couleur_banniere').css({'background':'-moz-linear-gradient(center top , #'+SMT_BANNER_1_COLOR+', #'+hex+') repeat scroll 0 0 #'+SMT_BANNER_1_COLOR+''});
	}});
	
	jQuery('#picker7').ColorPicker({color:'#'+SMT_BANNER_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_BANNER_COLOR=hex;
		jQuery('#SMT_BANNER_COLOR').val(hex);
		jQuery('#couleur_banniere').css({'color':'#'+hex});
	}});
	
	jQuery('#picker8').ColorPicker({color:'#'+SMT_BANNER_SHAD_COLOR,onChange: function (hsb, hex, rgb) {
		SMT_BANNER_SHAD_COLOR=hex;
		jQuery('#SMT_BANNER_SHAD_COLOR').val(hex);
		jQuery('#couleur_banniere').css({'text-shadow':'0 1px 0 #'+hex});
	}});
	
	
});
</script>
<div class="bordure_menu" style='width:220px;float:right;margin-top:20px;' onclick="javascript:popupWindow('<?echo URL_DIR;?>/iphone/','Aide',320,480,1);">
	<div style='width:220px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
		<p>Visualiser le site iphone</p>
	</div>
</div>
<div class="clear"></div>
<div style="margin-top:10px;">
	Titre de la page d'accueil Smartphone
</div>
<div style="margin-top:5px;">
	<input type="text" name="title" value="<? echo format(get_c('SMT_TITLE')); ?>" style="width:50%;">
</div>
<div class="clear"></div>
<div style='margin-top:10px;'>
	Bannière
</div>
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
<div style="margin-top:10px;">
	<div style='border-top:1px solid #000;margin-top:10px;margin-bottom:10px;height:10px;'></div>
	<div style="float:left;width:50%;">
		<div>
		Première couleur du fond : 
		<a id="picker"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
		<div style="margin-top:10px;">
		Second couleur du fond : 
		<a id="picker2"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
		<div style="margin-top:10px;">
		Couleur de la police : 
		<a id="picker3"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
		<div style="margin-top:10px;">
		Couleur de l'ombre de la police : 
		<a id="picker4"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
	</div>
	<div style="float:right;width:50%;">
		<div id="couleur_fond" style="height:40px; color: #<?echo get_c('SMT_COLOR');?>;padding:15px;padding-left:50px;background: -moz-linear-gradient(center top , #<?echo get_c('SMT_1_COLOR');?>, #<?echo get_c('SMT_2_COLOR');?>) repeat scroll 0 0 #<?echo get_c('SMT_1_COLOR');?>;text-shadow: 0 1px 0 #<?echo get_c('SMT_SHAD_COLOR');?>;">Fond du site</div>
	</div>
	<div class="clear"></div>
	<div style='border-top:1px solid #000;margin-top:10px;margin-bottom:10px;height:10px;'></div>
	<div style="float:left;width:50%;">
		<div>
		Première couleur de la bannière : 
		<a id="picker5"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
		<div style="margin-top:10px;">
		Second couleur de la bannière : 
		<a id="picker6"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
		<div style="margin-top:10px;">
		Couleur de la bannière : 
		<a id="picker7"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
		<div style="margin-top:10px;">
		Couleur de l'ombre de la bannière : 
		<a id="picker8"><img src="<?echo URL_DIR;?>/images/pic.png"  alt="" align="absmiddle"/></a>
		</div>
	</div>
	<div style="float:right;width:50%;">
		<div id="couleur_banniere" style="height:40px; color: #<?echo get_c('SMT_BANNER_COLOR');?>;padding:15px;padding-left:50px;background: -moz-linear-gradient(center top , #<?echo get_c('SMT_BANNER_1_COLOR');?>, #<?echo get_c('SMT_BANNER_2_COLOR');?>) repeat scroll 0 0 #<?echo get_c('SMT_BANNER_1_COLOR');?>;text-shadow: 0 1px 0 #<?echo get_c('SMT_BANNER_SHAD_COLOR');?>;">Bannière</div>
	</div>



	<div class="clear"></div>
</div>
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