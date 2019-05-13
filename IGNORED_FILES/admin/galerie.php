<?
$id_module=12;
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="galerie.php";
	$ariane_element2['LIBELLE']="Ajouter une galerie";					
}elseif($action=='edit'){
	$ariane_element2['URL']="galerie.php";
	$ariane_element2['LIBELLE']="Modifier une galerie";
}
elseif(isset($_GET['id']) && is_numeric($_GET['id'])){
	$ariane_element2['URL']="galerie.php?action=view&id=".$_GET['id'];
	$ariane_element2['LIBELLE']="Modifier la galerie";
	if($action=='addfile'){
		$ariane_element3['URL']="galerie.php";
		$ariane_element3['LIBELLE']="Ajouter des photos";
	}
	if($action=='reorder'){
		$ariane_element3['URL']="galerie.php";
		$ariane_element3['LIBELLE']="Modifier l'ordre des photos";
	}
}

include('header.php');
require_once ROOT_DIR.'config/ThumbLib.inc.php';


/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['galerie']) && $_POST['galerie']!=""){
	
		$ordre=$db->countOf('cms_galerie','ID_LANG='.$_SESSION['langue'].' AND ORDRE > 0 AND ID_UNIVERS = '.$_SESSION['univers']);
		$db->execute("INSERT INTO cms_galerie (TITLE,TEXT,ORDRE,DT_CREATE,DT_MODIF,ID_USER,ID_UNIVERS,ID_LANG) VALUES ('".addslashes($_POST['galerie'])."','".addslashes($_POST['text_galerie'])."','".($ordre+1)."',".time().",".time().",".$_SESSION['id'].",".$_SESSION['univers'].",".$_SESSION['langue'].")");
		
		$id_galerie=$db->lastInsertedId();
		
		$old = umask(0); 
		mkdir("../images/galerie/".$_SESSION['dir_upload']."/".$id_galerie."/thumb", 0777, true);
		chmod("../images/galerie/".$_SESSION['dir_upload']."/".$id_galerie, 0777);
		chmod("../images/galerie/".$_SESSION['dir_upload']."/".$id_galerie."/thumb", 0777);
		umask($old); 
		
		
		$req=$db->query('SELECT * FROM cms_photo WHERE ID_GALERIE = '.$_POST['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req)){
				$db->execute("UPDATE cms_photo SET ID_GALERIE = ".$id_galerie." WHERE ID_GALERIE = ".$_POST['id']);
				copy('../tmp/'.$don['FILE'], '../images/galerie/'.$_SESSION['dir_upload'].'/'.$id_galerie.'/'.$don['FILE']);
				unlink('../tmp/'.$don['FILE']);

			}
		}
			
		$_SESSION['notification'][]=array(1,"Galerie photos","La galerie photos a été créée.");
		header('location:galerie.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Galerie photos","Veuillez remplir tous les champs");
		$action='add';
	}
}
elseif($action=='addfile_r'){/*Ajouter des photos à une galerie*/		
		
	$_SESSION['notification'][]=array(1,"Galerie photos","Les photos ont été ajoutées à la galerie.");
	header('location:galerie.php?action=view&id='.$_POST['id']);	
	die();	
}
elseif($action=='ordre'){	/*Réordonner les galeries*/
	$req=$db->query("select * from cms_galerie WHERE ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_galerie","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['galerie_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Galerie photos","La position de la galerie a été modifiée.");
	header('location:galerie.php');
	die();	
}
else if($action=="reorder_r" && is_numeric($_POST['id']) && $_POST['listOrder']!=""){/*Reordonner les photos*/
	$list_element=explode('|',$_POST['listOrder']);
	$nb_element=count($list_element);
	if($nb_element>1){
		for($i=0;$i<$nb_element;$i++){
			$db->execute('UPDATE cms_photo SET ORDRE = '.($i+1).' WHERE ID_GALERIE='.$_POST['id'].' AND ID = '.$list_element[$i]);
		}
	}
	$_SESSION['notification'][]=array(1,"Galerie photos","La galerie photos a été réordonnée.");
	header('location:galerie.php?action=view&id='.$_POST['id']);	
	die();
}
elseif($action=='edit_r'){/*Modification*/
	if(isset($_POST['galerie']) && $_POST['galerie']!=""){

		$db->execute("UPDATE cms_galerie SET TITLE = '".addslashes($_POST['galerie'])."', TEXT = '".addslashes($_POST['text_galerie'])."', DT_MODIF = '".time()."' WHERE ID = ".$_POST['id']);
		
		$req=$db->query('SELECT * FROM cms_photo WHERE ID_GALERIE = '.$_POST['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req)){
				$info_file=pathinfo($don['FILE']);
						
				if(!file_exists('../images/galerie/'.$_SESSION['dir_upload'].'/'.$_POST['id'].'/thumb/'.$info_file['filename'].'80x80.'.$info_file['extension'])){
					$thumb = PhpThumbFactory::create('../images/galerie/'.$_SESSION['dir_upload'].'/'.$_POST['id'].'/'.$don['FILE']);
					$thumb->resize(80, 80);
					$thumb->save('../images/galerie/'.$_SESSION['dir_upload'].'/'.$_POST['id'].'/thumb/'.$info_file['filename'].'80x80.'.$info_file['extension']);
				}
				
			}
		}		
		
		$_SESSION['notification'][]=array(1,"Galerie photos","La galerie photos a été modifiée.");
		header('location:galerie.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Galerie photos","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
elseif($action=='delete'){/*Suppression*/
	if(isset($_GET['id'])){
		
		$req=$db->query('select * from cms_galerie where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
				
		$req2=$db->query('SELECT * FROM cms_galerie WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ORDRE > '.$form['ORDRE']);
		while($don2=mysql_fetch_array($req2)){
			$db->execute('UPDATE cms_galerie SET ORDRE = '.($don2['ORDRE']-1).' WHERE ID = '.$don2['ID']);
		}
		
		$db->execute("DELETE FROM cms_galerie WHERE ID = ".$_GET['id']);
		$db->execute('DELETE FROM cms_photo WHERE ID_GALERIE = '.$_GET['id']);
		rrmdir('../images/galerie/'.$_SESSION['dir_upload'].'/'.$_GET['id'].'/');
		$_SESSION['notification'][]=array(1,"Galerie photos","La galerie photos a été supprimée.");
		header('location:galerie.php');		
		die();
	}
}

if($action=='view'){
	$array_menu[]=array('URL'=>'galerie.php','IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
	$array_menu[]=array('URL'=>'galerie.php?action=addfile&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter des photos');
	$array_menu[]=array('URL'=>'galerie.php?action=reorder&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/btn_settings.png','LIBELLE'=>'Modifier l\'ordre des photos','WIDTH'=>'200');	
}
elseif($action=='reorder' || $action=='addfile'){
	$array_menu[]=array('URL'=>'galerie.php?action=view&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
}
else if($action==''){
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une galerie','WIDTH'=>'180');
}
echo genere_sous_menu_admin($array_menu);?>
<?
/*
	AJOUT ET MODIFICATION GALERIE

*/
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){		
		$req=$db->query('select * from cms_galerie where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	
	}else{		
		$id_temp=mt_rand(0, 100000)*(-1);
		$form['ID']=$id_temp;	
	}
?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swf/swfupload.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swf/swfupload.queue.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swf/fileprogress.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swf/handlers.js"></script>
<script type="text/javascript">
	var swfu;
	window.onload = function() {
		var settings = {
			flash_url : "../js/swf/swfupload.swf",
			upload_url: "upload_galerie.php",
			post_params: {"USER_ID": <?echo $_SESSION['id'];?>,"ID" : "<?echo $form['ID'];?>"<?if($action=='edit'){ echo ',"ACTION":"DIRECT"';}?>},
			file_size_limit : "100 MB",
			file_types : "*.jpg;*.gif;*.png;*.jpeg",
			file_types_description : "Images",
			file_upload_limit : 100,
			file_queue_limit : 0,
			custom_settings : {
				progressTarget : "fsUploadProgress"
			},
			debug: false,
			// Button settings
			button_image_url: "../js/swf/parcourir.png",
			button_width: 280,
			button_height: 26,
			button_placeholder_id: "spanButtonPlaceHolder",
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,				
			// The event handler functions are defined in handlers.js
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			debug:false,
			queue_complete_handler : queueComplete	// Queue plugin event
		};

		swfu = new SWFUpload(settings);
	 };
	 
	 function lancedl(){
		if(swfu.getStats().files_queued==0)document.forms["form_galerie"].submit();
		try {
			swfu.startUpload();
		} catch (ex) {

		}
		return false;
	 }
	 
	 num_step_cours=1;
	function launch_step(num_step){
		if(num_step==1){
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/2');
		}
		else if(num_step==2){
			if($('#galerie').val()==''){
				$('#complete_all').show();
				$('#galerie').css({"border":"1px solid #FF0000"});
			}else{
				$('#complete_all').hide();
				$('#galerie').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=2;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/2');
			}
		}
	}
	function valide_form(){
		lancedl();		
	}
	function valide_form1(){
		document.forms["form_galerie"].submit();	
	}
	$(document).ready(function(){
		$('#step2_admin').hide();
	});	
</script>
<form method="post" name="form_galerie" id="form_galerie" action="galerie.php?action=<?echo $action;?>_r" enctype="multipart/form-data">
<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/2</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
</div>
<div class="clear"></div>
<div id="step1_admin" >
	<div style="margin-top:10px;">Nom de la galerie : <span id="complete_all" style="display:none;"><b style='color:#FF0000;'>- Merci de saisir un nom</b></span></div>
	<div style='margin-top:5px;'><input name="galerie" type="text" value='<?echo $form['TITLE'];?>'  id="galerie" style="width:80%;"/></div>
	<div class="clear"></div>
	<div style="margin-top:10px;">Description de votre galerie :</div>
	<div style='margin-top:5px;'><textarea name="text_galerie" rows="5" cols="80" style="width: 80%; border: 1px solid #999999;padding-left:10px; color: #8D8D8D;  font-size: 11px;    font-weight: bold;"><?echo $form['TEXT'];?></textarea></div>
	<div class="clear"></div>

	<div style="margin-top:35px;">	
		<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
			<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
		</div>
		<?if($action=='edit'){?>
			<div class="bordure_menu" style='width:195px;float:left;margin-left:30px;' onclick="javascript:valide_form1();">
				<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
					<p>Modifier la galerie</p>
				</div>
			</div>
		<?}?>
		<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('galerie.php');">
			<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
		</div>			
		
	</div>
</div>
<div class="clear"></div>
<div id="step2_admin" <?echo add_help('Ajouter des photos',"Pour sélectionnez les images de votre galerie photos de votre ordinateur cliquez sur <img src='".URL_DIR."/images/sel_photo.jpg'><br/>Pour lancer le téléchargement de vos images, cliquez sur <b>Ajouter les photos</b>.");?>>
	<div style="margin-top:10px;">Sélectionner les images de votre galerie photos puis cliquer sur <b><?if($action=='add'){echo 'Ajouter la galerie';}else{ echo 'Modifier la galerie';}?></b> pour débuter le téléchargement des images</div>
	<div style="float:left;width:400px;">
		<div style="margin-top:10px;">
			<span id="spanButtonPlaceHolder"></span>
		</div>
		<div class="clear"></div>
		<div class="fieldset flash" id="fsUploadProgress" style="margin-top:20px;">
			<span class="legend">File de téléchargement</span>
			<div id="divStatus"></div>
		</div>
	</div>
	<div style="float:right;width:250px;">
		<div class="bordure_menu" style='width:175px;float:left;' onclick="javascript:launch_step(1);">
			<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
		</div>					
		<div class="clear"></div>		
		<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="javascript:valide_form();">
			<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
				<p><?
				if($action=='add'){echo 'Ajouter la galerie';}
				else{ echo 'Modifier la galerie';}
				?></p>
			</div>
		</div>	
		<div class="clear"></div>
		<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="redir('galerie.php');">
			<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
		</div>		
	</div>
</div>
</form>
<?	
}
elseif($action=='addfile'){
?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swfupload.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swfupload.queue.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/fileprogress.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/swf/handlers.js"></script>
<script type="text/javascript">
	var swfu;
	window.onload = function() {
		var settings = {
			flash_url : "../js/swfupload.swf",
			flash9_url : "../js/swfupload_fp9.swf",
			upload_url: "upload_galerie.php",
			post_params: {"USER_ID": <?echo $_SESSION['id'];?>,"ID" : "<?echo $_GET['id'];?>","ACTION":"DIRECT"},
			file_size_limit : "100 MB",
			file_types : "*.jpg;*.gif;*.png;*.jpeg",
			file_types_description : "Images",
			file_upload_limit : 100,
			file_queue_limit : 0,
			custom_settings : {
				progressTarget : "fsUploadProgress"
			},
			debug: false,
			// Button settings
			button_image_url: "../js/swf/parcourir.png",
			button_width: 280,
			button_height: 26,
			button_placeholder_id: "spanButtonPlaceHolder",
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_cursor: SWFUpload.CURSOR.HAND,			
			// The event handler functions are defined in handlers.js
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete	// Queue plugin event
		};

		swfu = new SWFUpload(settings);
	 };
	 
	 function lancedl(){
		if(swfu.getStats().files_queued==0)document.forms["form_galerie"].submit();
		try {
			swfu.startUpload();
		} catch (ex) {

		}
		return false;
	 }
	
</script>
<div class="clear"></div>
<div id="form_admin" style="margin-top:10px;">
	<div style='float:left;width:605px;'><h1 id="etape_name">Ajouter des photos</h1></div>
</div>
<div class="clear"></div>
<form method="post" name="form_galerie" id="form_galerie" action="galerie.php?action=<?echo $action;?>_r" enctype="multipart/form-data">
	<input type='hidden' name='id' value='<?echo $_GET['id'];?>'>
	<div style="margin-top:10px;">Sélectionner les images de votre galerie photos puis cliquer sur <b>Ajouter les photos</b> pour débuter le téléchargement des images</div>
	<div style="float:left;width:400px;">
		<div style="margin-top:10px;">
			<span id="spanButtonPlaceHolder"></span>
		</div>
		<div class="clear"></div>
		<div class="fieldset flash" id="fsUploadProgress" style="margin-top:20px;">
			<span class="legend">File de téléchargement</span>
			<div id="divStatus"></div>
		</div>
	</div>
	<div style="float:right;width:250px;">	
		<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="javascript:lancedl();">
			<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
				<p>Ajouter les photos</p>
			</div>
		</div>	
		<div class="clear"></div>
		<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="redir('galerie.php?action=view&id=<?echo $_GET['id'];?>');">
			<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
		</div>		
	</div>
	</div>
</form>
<?
}
elseif($action=='view'){

if(!is_numeric($_GET['id'])){
	header('location:galerie.php');
	die();
}
?>
<script type='text/javascript'>
	var text_modif='';
	var nouvo_text='';
	var deja_modif=0;

	function modif_photo(num_photo){
		if(!deja_modif){
			deja_modif=1;
			text_modif=$('#photo-'+num_photo).html();
			//text_modif=text_modif.replace('<br>/gi', "");
			text_modif=Remplace(text_modif,'<br>', "");
			
			$('#photo-'+num_photo).html(
			'<textarea name="text-'+num_photo+'" id="text-'+num_photo+'" style="width:300px;height:80px;border: 1px solid #999999;padding-left:10px; color: #8D8D8D;  font-size: 11px;    font-weight: bold;">'+
			text_modif+
			'</textarea><div style="float:left;"><a href="javascript:modif_text_photo(\''+num_photo+'\');void(0);"><img src="<?echo URL_DIR;?>/images/pencil.png" align="absmiddle" style="margin-top:0px;"> OK</a>'+
			'&nbsp;<a href="javascript:get_text_photo(\''+num_photo+'\');void(0);"><img src="<?echo URL_DIR;?>/images/delete.png" align="absmiddle" style="margin-top:0px;"> Annuler</a></div>'
			);
			
			if($.browser.msie && $.browser.version=="6.0"){
				DD_belatedPNG.fix('#photo-'+num_photo);
			}
	
	
		}
	}

	function modif_text_photo(num_photo){
		nouvo_text=$('#text-'+num_photo).val();
		$.post("action_photo.php",{text_modif: nouvo_text,id_photo:num_photo,action:"modif"},
			function success(data){				
				get_text_photo(num_photo);				
			}
		, "json");
	}
	
	function get_text_photo(num_photo){
		deja_modif=0;
		$('#photo-'+num_photo).html('');
		$.post("action_photo.php",{id_photo:num_photo,action:"get"},
			function success(data){
				$('#photo-'+num_photo).html(data.text_modif);
			}
		, "json");
	}
	
	function suppr_photo(num_photo){		
		$('#ligne-'+num_photo).fadeOut('slow');
		$.post("action_photo.php",{id_photo:num_photo,action:"delete"},
			function success(data){

			}
		, "json");
	}
</script>
<?
/*
	VUE DE LA GALERIE

*/
	$req_gal=$db->query('SELECT * FROM cms_galerie WHERE ID = '.$_GET['id'].' LIMIT 0,1');
	if(mysql_num_rows($req_gal)!=0){
		$info_gal=mysql_fetch_array($req_gal);		
	}else{
		header('location:galerie.php');
		die();	
	}
	
	
	
	$req=$db->query('SELECT * FROM cms_photo WHERE ID_GALERIE = '.$_GET['id'].' ORDER BY ORDRE');
	if(mysql_num_rows($req)!=0){
	
		echo "
		<div id=\"form_admin\" style=\"margin-top:10px;\">
			<div style='float:left;width:605px;'><h1 id=\"etape_name\">Galerie photos - ".$info_gal['TITLE']."</h1></div>
		</div>
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Photo</th>
				<th >Description</th>
				<th width='80'>Modifier</th>
				<th width='80'>Supprimer</th>
			</tr>
			</thead>
		";
	
			
		$i=0;
		while($don=mysql_fetch_array($req)){
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;

			$file=ADD_DIR.'/images/galerie/'.$_SESSION['dir_upload'].'/'.$_GET['id'].'/'.$don['FILE'];
			
			
			echo'
			<tr id="ligne-'.$don['ID'].'" '.$style_table.'>
				<td style="height:100px;width:90px;" valign="top" class="frst"><img src="'.URL_DIR.'/admin/get-adaptative-thumb.php?url='.urlencode($file).'&width=80&height=80" align="absmiddle"> 
				</td>
				<td  style="text-align:left;"><span id="photo-'.$don['ID'].'">'.strCut($don['TITLE'],250).'</span></td>
				<td valign="top" style="padding-top:40px;" '.add_help("Description","Modifier votre description puis valider en cliquant sur <img src='".URL_DIR."/images/pencil.png> <b>OK</b>").'><a href="javascript:modif_photo(\''.$don['ID'].'\');void(0);"><img src="'.URL_DIR.'/images/photo_edit.png"></a></td>
				<td valign="top" style="padding-top:40px;"><a href="javascript:suppr_photo(\''.$don['ID'].'\');void(0);" onclick="return confirm(\'Etes-vous sûr ?\');"><img src="'.URL_DIR.'/images/btn_delete.png"></a></td>
			</tr>
			'; 
		}
		
		echo "</table>";
		
	}else{
		header('location:galerie.php?id='.$_GET['id'].'&action=addfile');
		die();
	}
}
elseif($action=='reorder'){
/*
	REORDONNER LA GALERIE

*/
	

	$req=$db->query('select * from cms_photo where ID_GALERIE = '.$_GET['id'].' ORDER BY ORDRE');
	while($don=mysql_fetch_array($req)){
		$file=ADD_DIR.'/images/galerie/'.$_SESSION['dir_upload'].'/'.$_GET['id'].'/'.$don['FILE'];
		$list_photos.='<li id="'.$don['ID'].'"><div class="wraptocenter"><span></span><img src="'.URL_DIR.'/admin/get-adaptative-thumb.php?url='.urlencode($file).'&width=80&height=80" ><br/>'.strCut($don['TITLE'],15).'</div></li>';
	}
?>
<form method="post" action="?action=reorder_r" name="form_ordre">
<div class="bordure_menu" style='width:657px;margin-top:10px;'>
	<div class="aide-legende">
	Pour ordonner différemment vos images, cliquer sur l'image de votre choix et faites la glisser à son nouvel emplacement.
	Une fois vos modifications terminées, cliquez sur <b>Modifier l'ordre.</b><br/>
	</div>
</div>

<input name="listOrder" type="hidden" value=""/>
<input name="id" type="hidden" value="<?echo $_GET['id'];?>" />
<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="javascript:valide_form();">
	<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
		<p>Modifier l'ordre</p>
	</div>
</div>	
<div class='clear'></div>
</form>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery.dragsort-0.3.10.js"></script>	
<script type="text/javascript">
	
	$(document).ready(function(){
		$(".aide-legende").corner("5px").parent().css('padding', '1px').corner("5px");
		if(!$.browser.msie || $.browser.version!="6.0"){
			$("#list1 div").corner();
		}
		$("#list1").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });
	});
	
	function valide_form(){
		document.forms['form_ordre'].submit();
	}
	
	function saveOrder() {
		var serialStr = "";
		$("#list1 li").each(function(i, elm) { 
			serialStr += (i > 0 ? "|" : "") + $(elm).attr('id'); 
		});
		$("input[name=listOrder]").val(serialStr);
	};
</script>
<!--[if lt IE 8]>
<style>
.wraptocenter span {
    display: inline-block;
    height: 100%;
}
</style><![endif]-->

<ul id="list1">
<? echo $list_photos;?>
</ul>
<?	
}
else{


/*
	VUE NORMALE

*/
	$req=$db->query("select * from cms_galerie WHERE ID_LANG = ".$_SESSION['langue']." AND ID_PUSH = 0 AND ID_UNIVERS = ".$_SESSION['univers']." order by ORDRE ASC");
	$nb_galerie=mysql_num_rows($req);
	if($nb_galerie!=0){
		?>
<script type="text/javascript">
$(document).ready(function() {
	$(".aide-legende").corner("5px").parent().css('padding', '1px').corner("5px");
	<? echo auto_help("Galerie Photos","<a href='#' id='titre_guide'>Voir le guide &quot;Galerie Photos&quot;</a>",'titre_guide','faq_view.php?id_aide=46&id_cat=8');?>

});
</script>
<div class="bordure_menu" style='width:657px;margin-top:10px;'>
	<div class="aide-legende">
		Pour gérer une galerie photos, <b>cliquez sur l'icône <img src='<?echo URL_DIR;?>/images/photo_edit.png'  align='absmiddle' style='margin-top:0px;'></b>.<br/>
		Pour régler l'ordre dans lequel apparaîtront vos galeries photos dans la section "Galerie photos" de votre site cliquez sur le  menu déroulant déroulant concerné <img src='<?echo URL_DIR;?>/images/bouton_ordre.jpg'  align='absmiddle' style='margin-top:0px;'>.<br/>
		Pour modifier le contenu  (titre et description) d’une galerie, cliquez sur <img src='<?echo URL_DIR;?>/images/edit.png'  align='absmiddle' style='margin-top:0px;'>.		
	</div>	
</div>	
<div class='clear'></div>
<div style='height:15px;width:100%;'></div>	
		<?
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='660' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Titre de la galerie</th>
				<th width='130'>Gérer la galerie</th>
				<th width='80'>Ordre</th>
				<th width='80'>Modifier</th>
				<th width='80'>Supprimer</th>
			</tr>
			</thead>
		";
		
		$i=0;
		while($don=mysql_fetch_array($req)){
			
			$nb_photos=$db->countOf('cms_photo','ID_GALERIE='.$don['ID']);
			
			switch($nb_photos){
				case 0:
				$lib_photo="<a href='?action=addfile&id=".$don['ID']."'><img src='".URL_DIR."/images/photo_edit.png'  align='absmiddle' style='margin-top:0px;'> Aucune image</a>";
				break;
				case 1:
				$lib_photo="<a href='?action=view&id=".$don['ID']."'><img src='".URL_DIR."/images/photo_edit.png'  align='absmiddle' style='margin-top:0px;'> 1 image</a>";
				break;
				default:
				$lib_photo="<a href='?action=view&id=".$don['ID']."'><img src='".URL_DIR."/images/photo_edit.png' align='absmiddle' style='margin-top:0px;'> ".$nb_photos." images</a>";
				break;
			}
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			echo "
			<tr ".$style_table.">
				<td class='frst'>".$don['TITLE']."</td>
				<td>".$lib_photo."</td>								
				<td>".genere_form_ordre('galerie_ordre','?action=ordre',1,$nb_galerie,$don['ORDRE'],1,array('ID'=>$don['ID']))."</td>								
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucune galerie actuellement";
	}
}

include('footer.php');
?>