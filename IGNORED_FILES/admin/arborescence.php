<?
$id_module=3;
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="arborescence.php";
	if($_GET['type']==0)$ariane_element2['LIBELLE']="Ajouter une rubrique";
	else $ariane_element2['LIBELLE']="Ajouter une page";
}
if($action=='edit'){
	$ariane_element2['URL']="arborescence.php";
	if($_GET['type']==0)$ariane_element2['LIBELLE']="Modifier un dossier";
	else $ariane_element2['LIBELLE']="Modifier une page";
}

include('header.php');
$tree = new nestedTree($handle);


/*Redirection vers les modules correspondant (lors d'un clic sur modifier ou supprimer d'un élément externe*/

if($action=='redir'){
	if(is_numeric($_GET['id']) && $_GET['action_clic']!=''){
		$req=$db->query('SELECT TYPE,ID_ELEMENT,LFT as l,RGT as r FROM cms_nst WHERE ID = '.$_GET['id']);
		if(mysql_num_rows($req)!=0){
			$don=mysql_fetch_array($req);			
			if($_GET['action_clic']=='retire'){
				$tree->nstDelete($don);
				$_SESSION['notification'][]=array(1,"Arborescence","L'élément a été retiré.");
				header('location:arborescence.php');		
				die();
			}else{
				switch($don['TYPE']){
					case 1:
					if($_GET['action_clic']=='modif'){
						header('location:article.php?id='.$don['ID_ELEMENT'].'&action=edit');
						die();
					}
					elseif($_GET['action_clic']=='suppr'){
						header('location:article.php?id='.$don['ID_ELEMENT'].'&action=delete');
						die();
					}
					break;
				}
			}
		}
		else{
			header('location:arborescence.php');
			die();
		}
	}
	else{
		header('location:arborescence.php');
		die();
	}
}
elseif($action=='active' && is_numeric($_GET['id'])){
	
	$info_article=$db->query('SELECT * FROM cms_nst WHERE ID = '.$_GET['id']);	
	if(mysql_num_rows($info_article)!=0){
		$infart=mysql_fetch_array($info_article);
		
		$db->execute('UPDATE cms_nst SET ACTIVE = 1 WHERE ID = '.$_GET['id']);
		
		add_search($infart['TITLE'].'||'.$infart['TEXT'].'||'.$infart['RESUME'].'||'.$infart['META_DESC'].'||'.$infart['META_KEY'],$_GET['id'],0,$_SESSION['langue'],$_SESSION['univers']);
		
		$_SESSION['notification'][]=array(1,"Arborescence","L'élément a été activé.");
		header('location:arborescence.php');		
		die();
	}
}
elseif($action=='desactive' && is_numeric($_GET['id'])){
	$db->execute('UPDATE cms_nst SET ACTIVE = 0 WHERE ID = '.$_GET['id']);
	$db->execute('DELETE FROM cms_search WHERE ID_ELEMENT = '.$_GET['id'].' AND TYPE = 0');
	$_SESSION['notification'][]=array(1,"Arborescence","L'élément a été désactivé.");
	header('location:arborescence.php');		
	die();
}
elseif($action=='add_r'){/*Ajout*/
	if(isset($_POST['titre']) && $_POST['titre']!=""){
	
		if($_POST['date_deb']){
			$temp=explode('/',$_POST['date_deb']);
			$time_deb=mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
		}
		if($_POST['date_fin']){
			$temp=explode('/',$_POST['date_fin']);
			$time_fin=mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
		}
		
		if(!is_numeric($time_deb))$time_deb=time();
		if(!is_numeric($time_fin))$time_fin=time()+315360000 ;
		
		
		$node_father=$tree->nstGetNodeWhere('ID='.$_POST['id_categorie_parent']);
		//echo $_POST['id_categorie_parent'];
		
		if(is_numeric($node_father['l']) && is_numeric($node_father['r'])){
			
			//pr($node_father);
			
			$new_node=$tree->nstNewLastChild($node_father,
			array(
			'ID_USER'=>$_SESSION['id'],
			'ID_ELEMENT'=>0,
			'ID_LANG'=>$_SESSION['langue'],
			'ID_UNIVERS'=>$_SESSION['univers'],
			'DT_CREATE'=>time(),
			'DT_MODIF'=>time(),
			'DT_DEB_PUBLI'=>$time_deb,
			'DT_FIN_PUBLI'=>$time_fin,
			'TYPE'=>$_POST['type'],
			'TYPE_PAGE'=>$_POST['type_page'],
			'NEWS'=>$_POST['news'],
			'BANNER'=>"'".addslashes($_POST['filepath'])."'",
			'TITLE'=>"'".addslashes($_POST['titre'])."'",
			'TEXT'=>"'".addslashes($_POST['text_categorie'])."'",
			'META_DESC'=>"'".addslashes($_POST['meta_desc'])."'",
			'META_KEY'=>"'".addslashes($_POST['meta_key'])."'",
			'RESUME'=>"'".addslashes($_POST['resume'])."'",
			'IMG_RESUME'=>"'".addslashes($_POST['img_resume'])."'"));	
			
			
			$id_element=$db->queryUniqueValue("SELECT ID FROM cms_nst WHERE `LFT` = ".$new_node['l']." AND `RGT` = ".$new_node['r']." AND ID_LANG =".$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers']);
			
			
			add_search($_POST['titre'].'||'.$_POST['text_categorie'].'||'.$_POST['resume'].'||'.$_POST['meta_desc'].'||'.$_POST['meta_key'],$id_element,0,$_SESSION['langue'],$_SESSION['univers']);
			
			$_SESSION['notification'][]=array(1,"Arborescence","L'élément a été créé.");
			header('location:arborescence.php');	
			die();
		}
		
	}else{
		$_SESSION['notification'][]=array(0,"Arborescence","Veuillez remplir tous les champs");
		$action='add';
	}
}
elseif($action=='edit_r'){/*Modification*/
	
	if(isset($_POST['titre']) && $_POST['titre']!=""){
		
		if($_POST['date_deb']){
			$temp=explode('/',$_POST['date_deb']);
			$time_deb=mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
		}
		if($_POST['date_fin']){
			$temp=explode('/',$_POST['date_fin']);
			$time_fin=mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
		}
		
		if(!is_numeric($time_deb))$time_deb=time();
		if(!is_numeric($time_fin))$time_fin=time()+315360000 ;
		
		$node_father=$tree->nstGetNodeWhere('ID='.$_POST['id_categorie_parent']);
		$node_actuel=$tree->nstAncestor($tree->nstGetNodeWhere('ID='.$_POST['id']));

		
		if(!$tree->nstEqual($node_father,$node_actuel)){						
			$tree->nstMoveToLastChild($tree->nstGetNodeWhere('ID='.$_POST['id']),$node_father);
		}
		
		$db->execute("UPDATE cms_nst SET TITLE = '".addslashes($_POST['titre'])."', TEXT = '".addslashes($_POST['text_categorie'])."', META_KEY = '".addslashes($_POST['meta_key'])."', META_DESC = '".addslashes($_POST['meta_desc'])."',RESUME = '".addslashes($_POST['resume'])."',TYPE_PAGE = ".addslashes($_POST['type_page'])." ,IMG_RESUME = '".addslashes($_POST['img_resume'])."', DT_MODIF = ".time().", DT_DEB_PUBLI = ".$time_deb." , DT_FIN_PUBLI = ".$time_fin.", BANNER = '".addslashes($_POST['filepath'])."' WHERE ID = ".$_POST['id']);
		
		modif_search($_POST['titre'].'||'.$_POST['text_categorie'].'||'.$_POST['resume'].'||'.$_POST['meta_desc'].'||'.$_POST['meta_key'],$_POST['id'],0,$_SESSION['langue'],$_SESSION['univers']);
		
		$_SESSION['notification'][]=array(1,"Arborescence","La catégorie a été modifiée.");
		header('location:arborescence.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Arborescence","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
elseif($action=='delete'){/*Suppression*/
	if(isset($_GET['id']) && is_numeric($_GET['id'])){	
		$node_father=$tree->nstGetNodeWhere('ID='.$_GET['id']);		
		if(is_numeric($node_father['l']) && is_numeric($node_father['r'])){		
			$tree->nstDelete($node_father);
			$db->execute('DELETE FROM cms_search WHERE ID_ELEMENT = '.$_GET['id'].' AND TYPE = 0');
			$_SESSION['notification'][]=array(1,"Arborescence","La catégorie a été supprimée.");
			header('location:arborescence.php');		
			die();
		}else{
			$_SESSION['notification'][]=array(0,"Arborescence","Catégorie introuvable");
			header('location:arborescence.php');		
			die();
		}
	}
	else{
		$_SESSION['notification'][]=array(0,"Arborescence","Catégorie introuvable");
		header('location:arborescence.php');		
		die();
	}
}

?>
<!--[if lte IE 7]>
<script type="text/javascript">
$(document).ready(function(){ 
	$('#content_block').css({'padding-bottom':'27px'});
});
</script>
<![endif]-->
<?
if($action=='edit' || $action=='add'){

	$form=array();
	
	if($action=='edit'){
		
		$req=$db->query('select * from cms_nst where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);
		
		$node['l']=$form['LFT'];
		$node['r']=$form['RGT'];
		
		$node_pere=$tree->nstAncestor($node);
		$form['DT_DEB_PUBLI']=date('d/m/Y',$form['DT_DEB_PUBLI']);
		$form['DT_FIN_PUBLI']=date('d/m/Y',$form['DT_FIN_PUBLI']);
		
		if($form['NEWS']==1)$_GET['news']=1;
			
	}	
	else{
		$form['TYPE']=$_GET['type'];
		$form['NEWS']=0;
		if(isset($_GET['news']))$form['NEWS']='1';
		
		
	}
	if($form['DT_DEB_PUBLI']==''){
		$form['DT_DEB_PUBLI']=date('d/m/Y',time());
		$date_article=date('Y');
		$date_article+=10;
		$form['DT_FIN_PUBLI']=date('d/m/',time()).$date_article;
	}
	
	$walk = $tree->nstWalkPreorder($tree->nstRoot());
	$select_list_cat='';
	while($curr = $tree->nstWalkNext($walk)){
		if($walk['row']['TYPE']==0){
		
		
			
			if($walk['row']['ID']!=$form['ID']){
				if($walk['row']['LFT']==1)$walk['row']['TITLE']='Le dossier principal';
				
				$node_actual['l']=$walk['row']['LFT'];
				$node_actual['r']=$walk['row']['RGT'];
				
				$lvl_node_actual=$tree->nstLevel($node_actual);
				
				if($tree->nstEqual($node_pere,$node_actual))$sel='selected="selected"';
				else $sel='';
			
				if(($_GET['type']==0 && $lvl_node_actual<2) || ($_GET['type']==1 && $lvl_node_actual<3)){
					
					$select_list_cat.='<option value="'.$walk['row']['ID'].'" '.$sel.'>'.str_repeat('. ',$walk['level']).htmlspecialchars($walk['row']['TITLE'],ENT_QUOTES,"UTF-8").'</option>';
				}
			}
		}
	}
	
	
?>
	
	
	<script type="text/javascript" src="<?echo URL_DIR;?>/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/uidatepickerfr.js"></script>
	<link rel="stylesheet" href="<?echo URL_DIR;?>/css/base/jquery.ui.all.css" type="text/css" media="screen" charset="utf-8" />
	
	<?
	if($_GET['type']==1){
	/*admin d'une page*/
	?>
	<!--[if lte IE 7]>
	<script type="text/javascript">
	$(document).ready(function(){ 
		$('#content_block').css({'padding-bottom':'26px'});
	});
	</script>
	<![endif]-->
	<script type='text/javascript'>
	$(document).ready(function() {
		$("#date_deb,#date_fin").datepicker({ minDate: 0, changeMonth: true,	changeYear: true});
		$('#step3_admin').hide();
	});
	<? echo genere_tiny_mce('text_categorie');?>
	<? echo genere_file_browser();?>
	num_step_cours=1;
	function launch_step(num_step){
		if(num_step==1){			
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/2');
		}
		else if(num_step==3){
			if($('#titre').val()==''){
				$('#complete_all').show();
				$('#titre').css({"border":"1px solid #CC0099"});
			}else{
				$('#complete_all').hide();
				$('#titre').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=3;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/2');
			}
		}
	}
	function valide_form(){
		document.forms["form_rub"].submit();
	}
	</script>
	<form method="post" name="form_rub" id="form_user" action="arborescence.php?action=<?echo $action;?>_r">
	<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
	<input type='hidden' name='type' value='<?echo $form['TYPE'];?>'>
	<input type='hidden' name='news' value='<?echo $form['NEWS'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/2</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/btn_article_add.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >
		<div>Nom de la page <span id="complete_all" style="display:none;"><b style='color:#CC0099;'>- Merci de saisir un nom</b></span></div>
		<div style="margin-top:15px;">
			<input name="titre" type="text" value='<?echo $form['TITLE'];?>' id="titre" style="width:650px;" />
		</div>
		<div class="clear"></div>
		<div style='margin-top:10px;'>Je souhaite placer cette page dans :</div>
		<div style='margin-top:5px;width:225px;'>
			<select name="id_categorie_parent" class="select_class" style="width:225px;">
			<?
			echo $select_list_cat;
			?>
			</select>				
		</div>
		<div class="clear"></div>
		<div style='margin-top:5px;'>Rédaction de votre page</div>
		<div class="clear"></div>
		<div style="margin-top:12px;"  <? echo add_help("Contenu","La rédaction du contenu passe par un éditeur de texte type Word.<br/>N’hésitez pas à insérer des photos, vidéos, audios et des liens vers des sites extérieurs pour enrichir votre contenu.<br/><a href='#' id='titre_guide3'>Voir les guides <br/>&quot;Comment créer un contenu ?&quot;</a>","titre_guide3","faq_view.php?id_cat=5"); ?>><textarea name="text_categorie" style="width: 660px;height:250px;" id="text_categorie" class="tinymce"><?echo $form['TEXT'];?></textarea></div>		
		<div class="clear"></div>
	
		<div style="margin-top:35px;">				
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(3);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			
			<div class="bordure_menu" style='width:175px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
					<p><?if($action=='add'){echo 'Ajouter';}else{echo 'Modifier';}?> la page</p>
				</div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('arborescence.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>		
			
		</div>
		<div class="clear"></div>	
	</div>	
	<div id="step3_admin">
		<div>Résumé pour le smartphone</div>
		<div style="margin-top:10px;" >
			<div style="width:460px;float:left;" <? echo add_help("Résumé","Le résumé sera affiché lors de la navigation sur smartphone."); ?>><textarea name="resume" style="width:458px;height:85px;" id="resume_zone"><?echo $form['RESUME'];?></textarea></div>	
			<div style="float:right;width:168px;" <? echo add_help("Image du résumé","L'image de résumé sera affiché lors de la navigation par Smartphone, la taille conseillée pour votre image est 115*115 pixels<br/><a href='#' id='titre_guide4'>Voir le guide</a>",'titre_guide4',"faq_view.php?id_aide=25&id_cat=8"); ?>>
			<?
			$dir_upload2=ADD_DIR.'/medias/';
			echo form_picture(150,100,'img_resume',$form['IMG_RESUME'],$dir_upload2,'Insérer une image pour le Smartphone',$array_dim=array(
			"THUMB_WIDTH"=>50,
			"THUMB_HEIGHT"=>50,
			"TEXT_WIDTH"=>88,
			"TOTAL_WIDTH"=>168,
			"TOTAL_HEIGHT"=>88));
			?>	
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
		<div style='margin-top:10px;'>Ma page sera visible sur :</div>
		<div style="margin-top:5px;">
			<select name="type_page" class="select_class" style="width:320px;">
				<option value="1">Le site internet et sa version smartphone</option>
				<option value="0" <?if( $action == 'edit' && $form['TYPE_PAGE'] == 0) echo 'selected="selected"';?>>Le site internet</option>
				<option value="2" <?if( $action == 'edit' && $form['TYPE_PAGE'] == 2) echo 'selected="selected"';?>>La version smartphone</option>
			</select>
		</div>		
		<div class="clear"></div>		
		<div style='margin-top:15px;'>Description</div>
		<div style="margin-top:5px;" <? echo add_help('Meta description',"La Description de la page doit respecter certaines règles pour optimiser le référencement de votre page.<br/><a href='#' id='titre_guide5'>Voir le guide Meta-description</a>",'titre_guide5',"faq_view.php?id_aide=39&id_cat=9 ");?>><input name="meta_desc" type="text" value='<?echo $form['META_DESC'];?>'id="meta_desc" style="width:650px;"/></div>
		<div style='margin-top:15px;'>Mots clés</div>
		<div style="margin-top:5px;" <? echo add_help('Mots clés',"C'est le terme (ou combinaison de mots) que l'internaute va utiliser pour effectuer une recherche.<br/><a href='#' id='titre_guide6'>Voir le guide Mots clés</a> pour plus d’informations.",'titre_guide6','faq_view.php?id_aide=40&id_cat=8');?>><input name="meta_key" type="text" value='<?echo $form['META_KEY'];?>' id="meta_key" style="width:650px;"/></div>
		<div style='margin-top:15px;'>			
			<div style="float:left;width:130px;">Publication</div>
			<div style="float:left;width:130px;margin-left:48px;">Retrait de publication</div>
			<div style="float:right;width:322px;">Placer une bannière spécifique dans cette page :	</div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:5px;">
		
		<div style="float:left;width:320px;margin-top:5px;" 
			<? echo add_help("Début et fin de publication","Vous avez la possibilité de gérer les dates de publication de votre article, sélectionnez les dates de début et de fin. Sans intervention de votre part, votre article est publié de manière permanente.");?>>
			<div style="float:left;width:130px;"><input name="date_deb" type="text" value='<?echo $form['DT_DEB_PUBLI'];?>' id="date_deb" style="width:120px;"/></div>
			<div style="float:left;width:123px;margin-left:48px;display:inline;"><input name="date_fin" type="text" value='<?echo $form['DT_FIN_PUBLI'];?>' id="date_fin" style="width:120px;"/></div>
			<div class="clear"></div>
		</div>		
		<div style="float:right;width:320px;">	
			<div style='margin-top:5px;width:320px;'>
			<?
			$dir_upload2=ADD_DIR.'/medias/banner/';
			echo form_picture(960,250,'filepath',$form['BANNER'],$dir_upload2,"Ajouter une bannière",array(
			"THUMB_WIDTH"=>150,
			"THUMB_HEIGHT"=>35,
			"TEXT_WIDTH"=>150,
			"TOTAL_WIDTH"=>320,
			"TOTAL_HEIGHT"=>68));
			?>	
			<div class="clear"></div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:35px;">				
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(1);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>
			<div class="bordure_menu" style='width:175px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
					<p><?if($action=='add'){echo 'Ajouter';}else{echo 'Modifier';}?> la page</p>
				</div>
			</div>
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('arborescence.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>		
			
		</div>
		<div class="clear"></div>	
	</div>
	</form>
	<?}else{
	/*Admin d'une rubrique*/
	?>
	<!--[if lte IE 7]>
	<script type="text/javascript">
	$(document).ready(function(){ 
		$('#content_block').css({'padding-bottom':'25px'});
	});
	</script>
	<![endif]-->
	<script type='text/javascript'>
	<? echo genere_tiny_mce('text_categorie',2);?>
	<? echo genere_file_browser();?>
	$(document).ready(function() {
		$('#step3_admin').hide();
	});
	num_step_cours=1;
	
	function launch_step(num_step){
		if(num_step==1){			
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/2');
		}
		else if(num_step==3){

			if($('#titre').val()==''){
				$('#complete_all').show();
				$('#titre').css({"border":"1px solid #CC0099"});
			}else{
				$('#complete_all').hide();
				$('#titre').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=3;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/2');
			}	
		
		}
	}
	function valide_form(){
		document.forms["form_rub"].submit();
	}

	</script>
	<form method="post" name="form_rub" id="form_user" action="arborescence.php?action=<?echo $action;?>_r">
	<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
	<input type='hidden' name='type' value='<?echo $form['TYPE'];?>'>
	<input type='hidden' name='news' value='<?echo $form['NEWS'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name"><?if($action=='add'){echo "Ajouter un dossier";}else{echo "Modifier un dossier";}?><?if(isset($_GET['news']))echo " d'actualités";?></h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/btn_article_add.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >	
		<div>Nom du dossier <?if(isset($_GET['news']))echo " d'actualités";?><span id="complete_all" style="display:none;"><b style='color:#CC0099;'>- Merci de saisir un nom</b></span></div>
		<div style="margin-top:15px;">
			<input name="titre" type="text" value='<?echo $form['TITLE'];?>' id="titre" style="width:650px;" />
		</div>
		<div class="clear"></div>
		<div style='margin-top:10px;'>Je souhaite placer ce dossier dans :</div>
		<div style='margin-top:5px;width:225px;'>
			<select name="id_categorie_parent" class="select_class" style="width:225px;">
			<?
			echo $select_list_cat;
			?>
			</select>				
		</div>
		<div class="clear"></div>
		<div style='margin-top:5px;'>Description de votre dossier</div>
		<div class="clear"></div>
		<div style="margin-top:12px;"  <? echo add_help("Description de votre dossier","La description de votre dossier apparaîtra automatiquement au dessus de l’arborescence du site . <br/><a href='#' id='titre_guide3'>Voir les guides <br/>&quot;Comment créer un contenu ?&quot;</a>","titre_guide3","faq_view.php?id_cat=5"); ?>><textarea name="text_categorie" style="width: 660px;height:250px;" id="text_categorie" class="tinymce"><?echo $form['TEXT'];?></textarea></div>		
		<div class="clear"></div>
		<div style="margin-top:35px;">				
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(3);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			
			<div class="bordure_menu" style='width:175px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
					<p><?if($action=='add'){echo "Ajouter";}else{echo "Modifier";}?> le dossier</p>
				</div>
			</div>
		
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('arborescence.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>		
			
		</div>
		<div class="clear"></div>
	</div>
	<div id="step3_admin">
		<div style="margin-top:10px;">Résumé pour le smartphone</div>
		<div style="margin-top:10px;" >
			<div style="width:460px;float:left;" <? echo add_help("Résumé pour le Smartphone","Le résumé sera affiché lors de la navigation sur smartphone."); ?>><textarea name="resume" style="width:458px;height:85px;background-color: #FFFFFF; border: 1px solid #999999;  color: #666666;  font-size: 11px;   font-weight: normal;  padding: 2px;" id="resume_zone"><?echo $form['RESUME'];?></textarea></div>	
			<div style="float:right;width:168px;" <? echo add_help("Image du résumé","L'image de résumé sera affiché lors de la navigation par Smartphone, la taille conseillée pour votre image est 115*115 pixels<br/><a href='#' id='titre_guide4'>Voir le guide</a>",'titre_guide4',"faq_view.php?id_aide=25&id_cat=8"); ?>>
			<?
			$dir_upload2=ADD_DIR.'/medias/';
			echo form_picture(115,115,'img_resume',$form['IMG_RESUME'],$dir_upload2,'Insérer une image pour le Smartphone',$array_dim=array(
			"THUMB_WIDTH"=>55,
			"THUMB_HEIGHT"=>55,
			"TEXT_WIDTH"=>88,
			"TOTAL_WIDTH"=>168,
			"TOTAL_HEIGHT"=>88));
			?>	
			<div class="clear"></div>
		</div>	
		<div class="clear"></div>
		<div style='margin-top:10px;'>Ma page sera visible sur :</div>
		<div style="margin-top:5px;">
			<select name="type_page" class="select_class" style="width:320px;">
				<option value="1">Le site internet et sa version smartphone</option>
				<option value="0" <?if( $action == 'edit' && $form['TYPE_PAGE'] == 0) echo 'selected="selected"';?>>Le site internet</option>
				<option value="2" <?if( $action == 'edit' && $form['TYPE_PAGE'] == 2) echo 'selected="selected"';?>>La version smartphone</option>
			</select>
		</div>			
		<div class="clear"></div>
		<div style="margin-top:10px;">
			Placer une bannière spécifique dans ce dossier :			
		</div>
		<div style="margin-top:5px;">
			<div style='margin-top:5px;width:320px;'>
			<?
			$dir_upload2=ADD_DIR.'/medias/banner/';
			echo form_picture(960,250,'filepath',$form['BANNER'],$dir_upload2,"Ajouter une bannière",array(
			"THUMB_WIDTH"=>150,
			"THUMB_HEIGHT"=>35,
			"TEXT_WIDTH"=>150,
			"TOTAL_WIDTH"=>320,
			"TOTAL_HEIGHT"=>68));
			?>	
			<div class="clear"></div>
		</div>
		<div class="clear"></div>		
		<div style="margin-top:35px;">
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(1);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>
			<div class="bordure_menu" style='width:175px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
					<p><?if($action=='add'){echo "Ajouter";}else{echo "Modifier";}?> le dossier</p>
				</div>
			</div>
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('arborescence.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
		</div>
		<div class="clear"></div>	
	</div>	
	</form>
	<?
	}?>
<?
}
else{

	$actif_tablo='
	var tablo_actif_noeud=new Array();
	';
	$walk = $tree->nstWalkPreorder($tree->nstRoot());
	$prev_lvl=0;
	$select_list_cat='';
	$i=0;
	while($curr = $tree->nstWalkNext($walk)) {
		//pr($walk);
		
		/*
		Traitemant du menu de sélection
		*/
		$select_list_cat.='<option value="'.$walk['row']['ID'].'">'.str_repeat('. ',$walk['level']).htmlspecialchars($walk['row']['TITLE'],ENT_QUOTES,"UTF-8").'</option>';
		
		/*
		Création du tableau Javascript appelé par Ext
		*/
		
		$actif_tablo.='
		tablo_actif_noeud['.$walk['row']['ID'].'] = '.$walk['row']['ACTIVE'].';
		';
		
		
		if($walk['level']==0){
			$id_homepage=$walk['row']['ID'];
			$view.= "root:{
		text:\"Arborescence\", 
		id:'".$walk['row']['ID']."',
		iconCls : \"folder\", 
		expanded:true, 	
		children:[
			";
		}
		else{
			if($walk['level']>$prev_lvl && $prev_lvl!=0){
				$view.= ',children:[
				';
			}
			else if($walk['level']<$prev_lvl){
				/*$view.= '}]
					},
				';*/
				$view.=str_repeat('}]',($prev_lvl-$walk['level'])).'},';
				
			}
			else if($prev_lvl!=0) $view.= '},
			';
			
			if($tree->nstIsLeaf(array('l'=>$walk['row']['LFT'],'r'=>$walk['row']['RGT']))){
			
				if($walk['row']['TYPE']==0)$leaf='
				,expanded:true,
				children:[]';
				else $leaf='
				,leaf:true';
			}
			else $leaf='';
			
			if($walk['row']['TYPE']==0)$class_nst='	iconCls : "folder"';
			else $class_nst='	iconCls : "files"';
			

			if($walk['row']['ACTIVE']==0)$class_node_active="cls:'inactive_element',";
			else $class_node_active='';
			
			$view.= "{
				text:\"".htmlspecialchars($walk['row']['TITLE'],ENT_QUOTES,"UTF-8")."\",
				qtip:".$walk['row']['ID'].",
				id:'".$walk['row']['ID']."',
				".$class_node_active."
				".$class_nst.$leaf;	
			
		}
		$prev_lvl=$walk['level'];
		$i++;
	}
	if($i==1)$view.=']';
	$view.=str_repeat('}]',$prev_lvl).'}';
	
	//echo $view;
?>
<div id='infoObject'></div>
<script type="text/javascript">
Ext.ns('Example');
Ext.BLANK_IMAGE_URL = '<? echo URL_DIR;?>/images/resources/images/default/s.gif';
id_selected='';
leaf_statut=0;
id_homepage=<? echo $id_homepage;?>;
<?echo $actif_tablo;?>
// application main entry point
Ext.onReady(function() {
	// initialize QuickTips
	//Ext.QuickTips.init();
	var dropped='';
	var targeted='';
	var operation_done='';	
	
	$('#zone_desactive,#zone_active,#zone_modif').hide();
	
	// create DD enabled tree in the east
	// Note: It can be also an extension as the grid is
	var tree = new Ext.tree.TreePanel({
		// root with some static demo nodes
		<?echo $view;?>
		// preloads 1st level children
		,loader:new Ext.tree.TreeLoader({preloadChildren:true})
		// enable DD
		,enableDD:true
		,id:'tree'
		,split:true
		,useArrows: true
		,animate: true
		,collapsible:false
		,autoScroll:false
		,listeners:{
			// create nodes based on data from grid
			beforenodedrop:{
				fn:function(e) {
					//retourObj(dd,3);
					if(e.target.getDepth()>2 && e.point=='append'){
						alert('Votre arborescence ne peut pas dépasser 3 niveaux de dossier.');
						e.cancel=true;
					}
					dropped = e.dropNode;
					targeted = e.target;
					operation_done = e.point;
					
					$.post("order_tree.php",{moved_node: ""+dropped, target_node : ""+targeted, operation: ""+operation_done},
						function success(data){
							//alert('la');
						}
					, "json");
					
					
					//$('#infoObject').html('celui ciblé : '+e.target+', celui bougé : '+e.dropNode);
				}
			},
			click:{
				fn:function(e) {
					
					if(e.id==id_homepage){
						$('#zone_modif,#btn_modif_element').hide();
						$('#zone_modif_home').show();
					}else{
						//retourObj(e,5);
						$('#titre_sel').html(e.text);
						$('#zone_modif_home').hide();
						$('#zone_modif,#btn_modif_element').show();
						
						if(tablo_actif_noeud[e.id]){
							$('#zone_active').hide();
							$('#zone_desactive').show();
						}else{
							$('#zone_desactive').hide();
							$('#zone_active').show();
						}
						
						if(e.leaf!= true){//si c'est un dossier
							$('#see_online').hide();
						}else{
							$('#see_online').show();
						}
						
						id_selected=e.id;
						leaf_statut=e.leaf;
						//alert(id_selected);
					}
				}
			}
		}
	});
	// }}}
 
	tree.render('tree-div');
    tree.expandAll(); 
}); // eo function onReady
 
// eof
$(document).ready(function(){
	<? echo auto_help("Arborescence","<a href='#' id='titre_guide'>Voir le guide &quot;Comment gérer l'arborescence de votre site&quot;</a>",'titre_guide','faq_view.php?id_aide=23&id_cat=8');?>
	$(window).scroll(function() {
		height=$(this).scrollTop();
		if(height>200){
			height=height-85;
			$('#arbo_right').css('margin-top', height + "px");
		}else{
			$('#arbo_right').css('margin-top',"0px");
		}
	});
});

function launch_action(type_action){
	if(id_selected==''){
		alert('Pour utiliser l\'une des actions, Cliquez d\'abord sur l\'un des éléments');
	}else{
		
		if(type_action=='active' || type_action=='desactive'){
			window.location.href='arborescence.php?id='+id_selected+'&action='+type_action;
		}
		else{
			if(type_action=='modif'){
				if(leaf_statut)window.location.href='arborescence.php?id='+id_selected+'&action=edit&type=1';
				else window.location.href='arborescence.php?id='+id_selected+'&action=edit&type=0';
			}
			else if(type_action=='see'){
				window.open('<?echo URL_DIR;?>/rubrique.php?id_preview='+id_selected,'child','height=' + screen.height + ',width=' + screen.width+',toolbar=yes,location=yes,scrollbars=yes,status=yes,resizable=yes');
			}
			else if(type_action=='suppr'){
				window.location.href='arborescence.php?id='+id_selected+'&action=delete';
			}
		}
		/*else{
			if(type_action=='see'){
				window.open('<?echo URL_DIR;?>/rubrique.php?id_preview='+id_selected,'child','height=' + screen.height + ',width=' + screen.width+',toolbar=yes,location=yes,scrollbars=yes,status=yes,resizable=yes');
			}
			else{
				window.location.href='arborescence.php?id='+id_selected+'&action=redir&action_clic='+type_action;
			}
		}*/
	}
}
</script>
<div id="infoObject"></div>
<div class="clear"></div>
<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Arborescence de votre site</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/btn_article_add.jpg'></div>
</div>
<div class="clear"></div>
<div id="tree-div" style="height:auto;width:380px;border:1px solid #cccccc;float:left;"></div>
<div id='arbo_right' >
	<div class="bordure_menu" style='width:250px;float:left;' onclick="javascript:redir('arborescence.php?action=add&type=0');">
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_folder.png");' class="btn_style"><p>Créer un nouveau dossier</p></div>
	</div>
	<div class="clear"></div>
	<?
	$compteur_actu=$db->countof('cms_nst','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND NEWS = 1');
	if($compteur_actu==0){
	?>
	<div class="bordure_menu" style='width:250px;margin-top:10px;float:left;' onclick="javascript:redir('arborescence.php?action=add&type=0&news=1');">
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_folder.png");' class="btn_style"><p>Créer un dossier d'actualités</p></div>
	</div>	
	<div class="clear"></div>
	<?
	}
	?>
	<div class="bordure_menu" style='width:250px;margin-top:10px;float:left;' onclick="javascript:redir('arborescence.php?action=add&type=1');">
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/leaf.png");' class="btn_style"><p>Créer une nouvelle page</p></div>
	</div>
	<div class="clear"></div>	
	<div style="background-color:#cccccc;width:100%;height:1px;margin-top:20px;"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>	
	<div class="bordure_menu" style='width:250px;float:left;margin-top:20px;' onclick="javascript:launch_action('modif');" id="btn_modif_element">
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_pencil.png");' class="btn_style"><p>Modifier un élément</p></div>
	</div>
	<div class="clear"></div>
	<div id="zone_modif">
		<div style="margin-top:20px;">Titre</div>
		<div class="element_sel" id="titre_sel">Element sélectionnée</div>
		<div class="clear"></div>
		<div style="margin-top:15px;" id="zone_desactive"><a href="javascript:launch_action('desactive');void(0);" style='text-decoration:none;'><img src="<?echo URL_DIR;?>/images/star.png" align="absmiddle"> Mettre hors ligne</a></div>
		<div class="clear"></div>
		<div style="margin-top:15px;" id="zone_active"><a href="javascript:launch_action('active');void(0);" style='text-decoration:none;'><img src="<?echo URL_DIR;?>/images/star.png" align="absmiddle"> Mettre en ligne</a></div>
		<div class="clear"></div>
		<div style="margin-top:5px;"><a href="javascript:launch_action('suppr');void(0);" onclick="return confirm('Etes-vous sûr ? ');"><img src="<?echo URL_DIR;?>/images/delete.png" align="absmiddle"> Supprimer l'élément</a></div>
		<div class="clear"></div>
		<div style="background-color:#cccccc;width:100%;height:1px;margin-top:20px;"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>	
		<div class="clear"></div>
		<div style="margin-top:20px;" id="see_online">
			<div class="bordure_menu" style='width:250px;float:left;' onclick="javascript:launch_action('see');">
				<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_display.png");' class="btn_style"><p>Voir en ligne</p></div>
			</div>
		</div>
	</div>
	<div id="zone_modif_home" style='display:none;'>
		
	</div>
</div>

<div class="x-clear"></div>
<div id="infoObject"></div>
<?
}
include('footer.php');
?>