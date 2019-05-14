<?
$id_module=14;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add' || $action=='choix'){
	$ariane_element2['URL']="video.php";
	$ariane_element2['LIBELLE']="Ajouter une vidéo";					
}elseif($action=='edit'){
	$ariane_element2['URL']="video.php";
	$ariane_element2['LIBELLE']="Modifier une vidéo";
}elseif($action=='ordre'){
	$ariane_element2['URL']="video.php";
	$ariane_element2['LIBELLE']="Gérer l'ordre des vidéos";
}


include('header.php');


$dir_upload='../'.$_SESSION['dir_upload'].'/medias/video/';
$dir_upload2=ADD_DIR.'/'.$_SESSION['dir_upload'].'/medias/video/';

if($action=='add_r'){/*Ajout*/
	if(isset($_POST['name']) && $_POST['name']!=""){	
	
		create_thumb($_SERVER['DOCUMENT_ROOT'].$_POST['filepath'],150,150,$_SERVER['DOCUMENT_ROOT'].$_POST['filepath']);				
				
		$db->execute("INSERT INTO cms_video (TITLE,TEXT,ID_USER,ID_LANG,ID_UNIVERS,DT_CREATE,DT_MODIF,META_KEY,VIGNETTE,EMBED) VALUES ('".addslashes($_POST['name'])."','".addslashes($_POST['desc'])."',".$_SESSION['id'].",".$_SESSION['langue'].",".$_SESSION['univers'].",".time().",".time().",'".addslashes($_POST['key'])."','".addslashes($_POST['filepath'])."','".addslashes(clean_code_embed($_POST['code']))."')");
		$id_video=$db->lastInsertedId();

		if(isset($_POST['chaine']) && $_POST['chaine']!=""){
			foreach ($_POST['chaine'] as $value){
				$ordre=$db->countOf('cms_video_categorie_link','ID_CATEGORIE = '.$value.' AND ORDRE > 0');
				$db->execute('INSERT INTO cms_video_categorie_link (ID_CATEGORIE,ID_VIDEO,ORDRE) VALUES ('.$value.','.$id_video.','.($ordre+1).')');
			}
		}
		
		$_SESSION['notification'][]=array(1,"Vidéos","La vidéo a été créée.");
		header('location:video.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Vidéos","Veuillez remplir tous les champs");
		$action='add';
	}
}
elseif($action=='edit_r'){/*Modification*/
	if(isset($_POST['name']) && $_POST['name']!="" && isset($_POST['code']) && $_POST['code']!=""){
		
		create_thumb($_SERVER['DOCUMENT_ROOT'].$_POST['filepath'],150,150,$_SERVER['DOCUMENT_ROOT'].$_POST['filepath']);				
	
		$db->execute("UPDATE cms_video SET TITLE = '".addslashes($_POST['name'])."',TEXT='".addslashes($_POST['desc'])."',DT_MODIF=".time().",META_KEY='".addslashes($_POST['key'])."',VIGNETTE='".addslashes($_POST['filepath'])."',EMBED='".addslashes(clean_code_embed($_POST['code']))."' WHERE ID = ".$_POST['id']);		
		
		$list_chaine_actuel=array();
		$list_chaine_ordre=array();
		$req_chaine=$db->query('select ID_CATEGORIE,ORDRE FROM cms_video_categorie_link WHERE ID_VIDEO = '.$_POST['id']);
		if(mysql_num_rows($req_chaine)!=0){
			while($don=mysql_fetch_array($req_chaine)){
				$list_chaine_actuel[]=$don['ID_CATEGORIE'];
				$list_chaine_ordre[$don['ID_CATEGORIE']]=$don['ORDRE'];
			}
		}
		
		if(isset($_POST['chaine']) && $_POST['chaine']!=""){
			foreach ($_POST['chaine'] as $value){			
				if(!in_array($value,$list_chaine_actuel)){//pr les nouvelles chaines on insère
					ecrire_log('Vidéos','INSERT INTO cms_video_categorie_link (ID_CATEGORIE,ID_VIDEO) VALUES ('.$value.','.$_POST['id'].')');			
					$ordre=$db->countOf('cms_video_categorie_link','ID_CATEGORIE = '.$value.' AND ORDRE > 0');
					$db->execute('INSERT INTO cms_video_categorie_link (ID_CATEGORIE,ID_VIDEO,ORDRE) VALUES ('.$value.','.$_POST['id'].','.($ordre+1).')');	
				}
				removeFromArray($list_chaine_actuel,$value);
			}	
		}
		
		//suppression des chaînes non conservées
		$nb_chaine_suppr=count($list_chaine_actuel);
		if($nb_chaine_suppr>0){
			for($i=0;$i<$nb_chaine_suppr;$i++){
				ecrire_log('Vidéos','SELECT * FROM cms_video_categorie_link WHERE ORDRE > '.$list_chaine_ordre[$list_chaine_actuel[$i]].' AND ID_CATEGORIE = '.$list_chaine_actuel[$i]);
				$reqinf=$db->query('SELECT * FROM cms_video_categorie_link WHERE ORDRE > '.$list_chaine_ordre[$list_chaine_actuel[$i]].' AND ID_CATEGORIE = '.$list_chaine_actuel[$i]);
				if(mysql_num_rows($reqinf)!=0){
					while($don=mysql_fetch_array($reqinf)){
						ecrire_log('Vidéos','UPDATE cms_video_categorie_link SET ORDRE = '.($don['ORDRE']-1).' WHERE ID ='.$don['ID']);
						$db->execute('UPDATE cms_video_categorie_link SET ORDRE = '.($don['ORDRE']-1).' WHERE ID ='.$don['ID']);
					}					
				}
				ecrire_log('Vidéos','DELETE FROM cms_video_categorie_link WHERE ID_CATEGORIE = '.$list_chaine_actuel[$i].' AND ID_VIDEO = '.$_POST['id']);
				$db->execute('DELETE FROM cms_video_categorie_link WHERE ID_CATEGORIE = '.$list_chaine_actuel[$i].' AND ID_VIDEO = '.$_POST['id']);
			}
		}
		
		$_SESSION['notification'][]=array(1,"Vidéos","La vidéo a été modifiée.");
		header('location:video.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Vidéos","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
elseif($action=='ordre_r'){	/*Réordonner les vidéos*/
	$req=$db->query("select * from cms_video_categorie_link WHERE ID_CATEGORIE= ".$_POST['ID_CATEGORIE']." ORDER BY ORDRE ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_video_categorie_link","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['video_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Vidéos","La position de la vidéo a été modifiée.");
	header('location:video.php?action=ordre');
	die();	
}
elseif($action=='delete'&& isset($_GET['id'])){	/*Suppression*/
	
	$req=$db->query('select * from cms_video_categorie_link where ID_VIDEO = '.$_GET['id']);
	while($don=mysql_fetch_array($req)){
		$reqinf=$db->query('SELECT * FROM cms_video_categorie_link WHERE ORDRE > '.$don['ORDRE'].' AND ID_CATEGORIE = '.$don['ID_CATEGORIE']);
		if(mysql_num_rows($reqinf)!=0){
			while($don2=mysql_fetch_array($reqinf)){
				ecrire_log('Vidéos','UPDATE cms_video_categorie_link SET ORDRE = '.($don2['ORDRE']-1).' WHERE ID ='.$don2['ID']);
				$db->execute('UPDATE cms_video_categorie_link SET ORDRE = '.($don2['ORDRE']-1).' WHERE ID ='.$don2['ID']);
			}					
		}
		$db->execute('DELETE FROM cms_video_categorie_link WHERE ID_CATEGORIE = '.$don['ID_CATEGORIE'].' AND ID_VIDEO = '.$_GET['id']);
	}			
	$db->execute("DELETE FROM cms_video WHERE ID = ".$_GET['id']);
	$_SESSION['notification'][]=array(1,"Vidéos","La vidéo a été supprimée.");		
	header('location:video.php');		
	die();
	
}

if($action=='ordre')$array_menu[]=array('URL'=>'video.php','IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
else if($action==''){
	
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une vidéo','WIDTH'=>'170');
	$array_menu[]=array('URL'=>'video_categorie.php','IMG'=>URL_DIR.'/images/video-channel.png','LIBELLE'=>'Gérer les chaînes vidéos','WIDTH'=>'200');
	$nb_videos=$db->countOf('cms_video','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PUSH = 0 ');
	$nb_videos_categorie=$db->countOf('cms_video_categorie','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PUSH = 0');
			
	if($nb_videos>0 && $nb_videos_categorie>0){
		$array_menu[]=array('URL'=>'?action=ordre','IMG'=>URL_DIR.'/images/order.png','LIBELLE'=>'Gérer l\'ordre des vidéos','WIDTH'=>'200');
	}
}
echo genere_sous_menu_admin($array_menu);
if($action=='add' || $action=='edit'){

	$form=array();
	
	$nb_chaine=$db->countOf('cms_video_categorie','ID_LANG = '.$_SESSION['langue'].' AND ID_PUSH = 0 AND ID_UNIVERS = '.$_SESSION['univers']);

	
	if($_POST['url_video']!=''){
		$old=array('&amp;#39');
		$change=array('&#39');
		
		$info=getVideoInfo($_POST['url_video']);
		$form['TITLE']=html_entity_decode(str_replace($old,$change,$info['titre']),ENT_QUOTES,'UTF-8');
		$form['TEXT']=html_entity_decode(str_replace($old,$change,$info['description']),ENT_QUOTES,'UTF-8');
		$form['META_KEY']=html_entity_decode(str_replace($old,$change,$info['keywords']),ENT_QUOTES,'UTF-8');
		$form['EMBED']=$info['code'];	
		$form['VIGNETTE']=$info['img'];	
		$form=array_map("format",$form);	
		
		if($form['VIGNETTE']!=""){
			//echo $form['VIGNETTE'];
			$imginfo=pathinfo($form['VIGNETTE']);
			if($imginfo['extension']==''){
				$imginfo['extension']='jpg';
			}
			if($imginfo['filename']==''){
				$imginfo['filename']=GenerateRandomString();
			}
			$imginfo['basename']=$imginfo['filename'].'.'.$imginfo['extension'];
			if(file_exists($dir_upload.$imginfo['basename'])){
				$imginfo['basename']=$imginfo['filename'].mt_rand(0, 100000).'.'.$imginfo['extension'];
			}
			if(copy($form['VIGNETTE'],$dir_upload.$imginfo['basename'])){
				$form['VIGNETTE']=ADD_DIR.'/'.$_SESSION['dir_upload'].'/medias/video/'.$imginfo['basename'];
			}
			
			//pr($imginfo);
		}
		
		//pr($form);
	}
	
	if($action=='edit'){
		
		$req=$db->query('select * from cms_video where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	
	}

?>
	<script type='text/javascript'>
	$(document).ready(function(){
		$("#add_video").fancybox({
			'width':600,
			'height':100,
			'autoDimensions':false,
			'autoScale'			: true,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'ajax',
			'modal' : false

		});
		$('#step2_admin').hide();
	});
	num_step_cours=1;
	function launch_step(num_step){
		if(num_step==1){
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/2');
		}
		else if(num_step==2){
			if($('#name').val()==''){
				$('#complete_all').show();
				$('#name').css({"border":"1px solid #333399"});
			}else{
				$('#complete_all').hide();
				$('#name').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=2;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/2');
			}
		}
	}
	function valide_form(){
		document.forms['form_video'].submit();	
	}
	</script>		
	<script type="text/javascript" src="<?echo URL_DIR;?>/tiny_mce/tiny_mce.js"></script>
	<script type='text/javascript'>
	<? echo genere_file_browser();?>
	<? echo genere_tiny_mce('desc');?>
	
	</script>
	<div class="clear"></div>
	<div id="form_admin" style="margin-top:10px;">
		<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/2</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>
	<div class="clear"></div>
	<div id="step1_admin" >
	<a id="add_video" href='add_video.php'> <img src="<?echo URL_DIR;?>/images/btn_zone_modif_50.png" align="absmiddle"> Récupérez automatiquement <span style="font-weight:normal;">une vidéo de Youtube, Dailymotion, Google Video, Wat.tv.</span></a>

	<form method="post"  id="form_video"  name="form_video" action="video.php?action=<?echo $action;?>_r"  enctype="multipart/form-data">
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div style="margin-top:10px;">Titre : <span id="complete_all" style="display:none;"><b style='color:#333399;'>- Merci de saisir un titre</b></span></div>
		<div style="margin-top:5px;">
			<input name="name" type="text" value='<?echo $form['TITLE'];?>' id="name" style="width:80%;"/>
		</div>	
		<div class="clear"></div>
		<div style="margin-top:10px;">Description :</div>
		<div style="margin-top:5px;">
			<textarea name="desc" type="text" cols="80" style="width: 80%;height:100px;" id="desc"  class="tinymce"/><?echo $form['TEXT'];?></textarea>
		</div>	
		<div class="clear"></div>
		<div style="margin-top:10px;">Mots clés (ils seront utilisés pour rechercher votre vidéo):</div>
		<div style="margin-top:5px;">	
			<input name="key" type="text" value='<?echo $form['META_KEY'];?>' id="key" style="width:80%;"/>
		</div>	
		<div class="clear"></div>
		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			<?if($action=='edit'){?>
				<div class="bordure_menu" style='width:195px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
					<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
						<p>Modifier la vidéo</p>
					</div>
				</div>
			<?}?>
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('video.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
		</div>
		<div class="clear"></div>
	</div>
	<div id="step2_admin" >
		<div style="margin-top:10px;">
			<div style="width:460px;float:left;">Code Embed :</div>
			<div style='float:right;width:168px;'>Illustration :</div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:5px;">
			<div style="width:460px;float:left;"><textarea name="code" style="width:458px;height:83px;border: 1px solid #999999;padding-left:10px; color: #8D8D8D;  font-size: 11px;    font-weight: bold;"><?echo $form['EMBED'];?></textarea></div>	
			<div style='float:right;width:168px;'>
			<?
			echo form_picture(150,150,'filepath',$form['VIGNETTE'],$dir_upload2,'Ajouter une image pour la web tv',array(
		"THUMB_WIDTH"=>55,
		"THUMB_HEIGHT"=>55,
		"TEXT_WIDTH"=>88,
		"TOTAL_WIDTH"=>168,
		"TOTAL_HEIGHT"=>83));
			?>	
			</div>
		<div class="clear"></div>
		<?
		$req_chaine=$db->query('select * from cms_video_categorie where ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PUSH = 0 ORDER BY ORDRE ');
		$nb_chaine=mysql_num_rows($req_chaine);
		if($nb_chaine!=0){
		?>
			<div style="margin-top:10px;">Chaîne(s) où sera présente la vidéo  :</div>
			<div style="margin-top:5px;">	
				<?
						
				while($don=mysql_fetch_array($req_chaine)){
					
					if($nb_chaine==1)$check='checked="checked"';
					else $check='';
					
					if(is_numeric($_GET['id'])){
						$exists=$db->countOf('cms_video_categorie_link','ID_VIDEO = '.$_GET['id'].' AND ID_CATEGORIE = '.$don['ID']);
						if($exists)$check='checked="checked"';
					}
					
					echo '<div style="margin-top:5px;"><input type="checkbox" name="chaine[]" value="'.$don['ID'].'" '.$check.'>'.$don['TITLE'].'</div>';
				}
				?>
			</div>		
			<div class="clear"></div>
		<?}?>
		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:175px;float:left;' onclick="javascript:launch_step(1);">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>	
			<div class="bordure_menu" style='width:175px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
					<p><?
					if($action=='add'){echo 'Ajouter la vidéo';}
					else{ echo 'Modifier la vidéo';}
					?></p>
				</div>
			</div>	
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('video.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
		</div>
		<div class="clear"></div>
	</form>
	</div>
<?	
}
elseif($action=='ordre'){
	
	//$info=getVideoInfo('http://www.dailymotion.com/video/xfa5z9_l-amour-fou-cali-remi-gaillard_fun#hp-sc-p-1');
	//pr($info);	

	
		$req=$db->query("select cms_video_categorie.TITLE as TITLE_CHANNEL, cms_video.TITLE as TITLE , cms_video_categorie.ID as ID_CATEGORIE , cms_video_categorie_link.ORDRE as VIDEO_ORDRE , cms_video_categorie_link.ID AS ID_RELATION, cms_video_categorie_link.ID_VIDEO as ID_VIDEO from cms_video_categorie,cms_video,cms_video_categorie_link WHERE cms_video.ID=cms_video_categorie_link.ID_VIDEO AND cms_video_categorie.ID=cms_video_categorie_link.ID_CATEGORIE AND cms_video_categorie.ID_LANG = ".$_SESSION['langue']." AND cms_video_categorie.ID_UNIVERS = ".$_SESSION['univers']." AND cms_video_categorie.ID_PUSH = 0 ORDER BY cms_video_categorie.ORDRE,cms_video_categorie_link.ORDRE ASC");
	
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>";
		
		if(mysql_num_rows($req)!=0){
		
			echo "		
			<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
				<thead>
					<tr>
						<th class='frst'>Nom</th>
						<th width='80'>Ordre</th>
					</tr>
				</thead>
			";			
			$chaine=0;

			while($don=mysql_fetch_array($req)){

				
				if($chaine!=$don['ID_CATEGORIE']){
					echo '
				<tr>
					<td colspan="2" class="frst"><b>'.$don['TITLE_CHANNEL'].'</b></td>
				</tr>	
					';
					$chaine=$don['ID_CATEGORIE'];
					$nb_video_chaine=$db->countOf('cms_video_categorie_link','ID_CATEGORIE = '.$don['ID_CATEGORIE']);
				}		
				
				
				echo"
				<tr  class='odd'>
					<td style='text-align: left;padding-left:25px;'>".$don['TITLE']."</td>				
					<td>".genere_form_ordre('video_ordre','?action=ordre_r',1,$nb_video_chaine,$don['VIDEO_ORDRE'],1,array('ID'=>$don['ID_RELATION'],'ID_CATEGORIE'=>$don['ID_CATEGORIE']))."</td>
		
				</tr>
				";
			}			
			echo "</table>";
		}else{
		
			echo "<div>Pour gérer l'ordre des vidéos, veuillez placer les vidéos au sein des chaînes vidéos , une vidéo peut appartenir à une ou plusieurs chaînes.</div>";
		}
	
}
else{	
			
	$nb_chaine=$db->countOf('cms_video_categorie','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers']);
	
	$req=$db->query('SELECT * FROM cms_video WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PUSH = 0');
		
?>
	<script type='text/javascript'>
	$(document).ready(function(){
		$(".videow").fancybox({
			'width':600,
			'height':450,
			'autoDimensions':false,
			'autoScale'			: true,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'type'				: 'ajax',
			'modal' : false

		});
		<? echo auto_help("Vidéos","<a href='#' id='titre_guide'>Voir le guide &quot;Vidéos&quot;</a>",'titre_guide','faq_view.php?id_aide=30&id_cat=8');?>
		
	});
	</script>
<?
		
	if(mysql_num_rows($req)!=0){
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom de la vidéo</th>
				<th width='95'>Voir la vidéo</th>
				<th width='95'>Modifier</th>
				<th width='95'>Supprimer</th>
			</tr>
			</thead>
		";		
		$i=0;
		while($don=mysql_fetch_array($req)){	
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			
			
			echo "
			<tr ".$style_table.">
				<td class='frst'>".$don['TITLE']."</td>				
				<td><a class='videow' href='video_view.php?id=".$don['ID']."'><img src='".URL_DIR."/images/btn_movie_play.png'></a></td>				
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucune vidéo actuellement présente";
	}
	
}

include('footer.php');
?>