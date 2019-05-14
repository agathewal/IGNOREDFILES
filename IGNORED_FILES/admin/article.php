<?
$id_module=2;
header('location:home.php');
die();
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if(isset($_GET['filter']))$filter=$_GET['filter'];
else $filter='';

if(isset($_GET['page']) && is_numeric($_GET['page']))$page=$_GET['page'];
else $page=0;


if($action=='add'){
	$ariane_element2['URL']="article.php";
	$ariane_element2['LIBELLE']="Ajouter un article";					
}elseif($action=='edit'){
	$ariane_element2['URL']="article.php";
	$ariane_element2['LIBELLE']="Modifier un article";
}

$perpage=10;

include('header.php');

/*Ajout d'articles*/

if($action=='add_r'){/*Ajout de catégorie*/
	if(isset($_POST['titre']) && $_POST['titre']!="" && isset($_POST['text_article']) && $_POST['text_article']!=""){
	
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
		
	
		$db->execute("INSERT INTO cms_article (TITLE,TEXT,RESUME,IMG_RESUME,META_KEY,META_DESC,DT_CREATE,DT_MODIF,ID_LANG,ID_UNIVERS,ID_USER,DT_DEB_PUBLI,DT_FIN_PUBLI) VALUES ('".addslashes($_POST['titre'])."','".addslashes($_POST['text_article'])."','".addslashes($_POST['resume'])."','".addslashes($_POST['img_resume'])."','".addslashes($_POST['meta_key'])."','".addslashes($_POST['meta_desc'])."','".time()."','".time()."',".$_SESSION['langue'].",".$_SESSION['univers'].",".$_SESSION['id'].",".$time_deb.",".$time_fin.")");
		
		$id_article=$db->lastInsertedId();
		ecrire_log('Article',"Création de l\'article :  ID : ".$id_article);	
		/*
		Traitement des cases cochées , soit on met à jour si l'emplacement été déjà présent, soit on ajoute dans le cas d'un nouvel emplacement
		*/
		
		if($_POST['check']!=""){
			$tree = new nestedTree($handle);
			$value=$_POST['check'];
			$parent=$tree->nstGetNodeWhere('ID='.$value);				
			if(is_numeric($parent['l']) && is_numeric($parent['r'])){
				//echo "insert !";
				$result=$tree->nstNewLastChild($parent,
				array(
				'ID_USER'=>$_SESSION['id'],
				'ID_ELEMENT'=>$id_article,
				'ID_LANG'=>$_SESSION['langue'],
				'ID_UNIVERS'=>$_SESSION['univers'],
				'DT_CREATE'=>time(),
				'DT_MODIF'=>time(),
				'DT_DEB_PUBLI'=>$time_deb,
				'DT_FIN_PUBLI'=>$time_fin,
				'TYPE'=>1,
				'TITLE'=>"'".addslashes($_POST['titre'])."'",
				'RESUME'=>"'".addslashes($_POST['resume'])."'",
				'IMG_RESUME'=>"'".addslashes($_POST['img_resume'])."'",
				'TEXT'=>"'".addslashes($_POST['text_article'])."'",
				'META_DESC'=>"'".addslashes($_POST['meta_desc'])."'",
				'META_KEY'=>"'".addslashes($_POST['meta_key'])."'")
				);	

				$id_element=$db->queryUniqueValue("SELECT ID FROM cms_nst WHERE `LFT` = ".$result['l']." AND `RGT` = ".$result['r']." AND ID_LANG =".$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers']);
				
				add_search($_POST['titre'].'||'.$_POST['text_article'].'||'.$_POST['resume'].'||'.$_POST['meta_desc'].'||'.$_POST['meta_key'],$id_element,0,$_SESSION['langue'],$_SESSION['univers']);
		
				ecrire_log('Article',"Insertion de l\'article :  LFT : ".$result['l'].' RGT : '.$result['r']);	
			}			
		}
		
		$_SESSION['notification'][]=array(1,"Articles","L'article a été créé.");
		header('location:article.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Articles","Veuillez remplir tous les champs");
		$action='add';
	}
}
elseif($action=='duplicate' && is_numeric($_GET['id'])){
	$req=$db->query('SELECT * FROM cms_article WHERE ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		$don=mysql_fetch_array($req);
		$don=array_map("stripslashes",$don);
		$db->execute("INSERT INTO cms_article (TITLE,TEXT,RESUME,IMG_RESUME,META_KEY,META_DESC,DT_CREATE,DT_MODIF,ID_LANG,ID_UNIVERS,ID_USER,DT_DEB_PUBLI,DT_FIN_PUBLI) VALUES ('".addslashes($don['TITLE'])."','".addslashes($don['TEXT'])."','".addslashes($don['RESUME'])."','".addslashes($don['IMG_RESUME'])."','".addslashes($don['META_KEY'])."','".addslashes($don['META_DESC'])."','".time()."','".time()."',".$_SESSION['langue'].",".$_SESSION['univers'].",".$_SESSION['id'].",".$don['DT_DEB_PUBLI'].",".$don['DT_FIN_PUBLI'].")");
		
		
		$_SESSION['notification'][]=array(1,"Articles","L'article a été dupliqué.");
		header('location:article.php');	
		die();
	}
}
elseif($action=='edit_r'){/*Modification*/
	if(isset($_POST['titre']) && $_POST['titre']!="" && isset($_POST['text_article']) && $_POST['text_article']!=""){
		
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
		
		
		//pr($_POST);
		
		
		$db->execute("UPDATE cms_article SET TITLE = '".addslashes($_POST['titre'])."', TEXT = '".addslashes($_POST['text_article'])."',RESUME = '".addslashes($_POST['resume'])."', IMG_RESUME = '".addslashes($_POST['img_resume'])."', META_KEY = '".addslashes($_POST['meta_key'])."', META_DESC = '".addslashes($_POST['meta_desc'])."', DT_MODIF = ".time().", DT_DEB_PUBLI = ".$time_deb." , DT_FIN_PUBLI = ".$time_fin." WHERE ID = ".$_POST['id']);
		
		/*
		Récupération de la liste des emplacements de l'article actuel
		*/
		$tree = new nestedTree($handle);		
		$list_parent=array();
		$req=$db->query("SELECT * FROM cms_nst WHERE TYPE = 1 AND ID_ELEMENT = ".$_POST['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req)){
				$don['l']=$don['LFT'];
				$don['r']=$don['RGT'];
				//pr($don);
				$parent=$tree->nstAncestor($don);
				//pr($parent);
				$temp=$tree->nstNodeAttribute($parent,"ID");
				//pr($temp);
				$parent['id']=$temp;
				$parent['id_item']=$don['ID'];
				$parent['still']=0;
				$list_parent[]=$parent;			
			}
			//pr($list_parent);			
		}
	
		/*
		Traitement des cases cochées , soit on met à jour si l'emplacement été déjà présent, soit on ajoute dans le cas d'un nouvel emplacement
		*/
		if($_POST['check']!=""){
			$value=$_POST['check'];
			$parent=$tree->nstGetNodeWhere('ID='.$value);
			$deja_mis=0;
			//pr($parent);
			if(count($list_parent)!=0){
				for($i=0;$i<count($list_parent);$i++){					
					if($value==$list_parent[$i]['id']){
						$deja_mis=1;
						$list_parent[$i]['still']=1;
						//echo "update !";
						$db->execute("UPDATE cms_nst SET TITLE = '".addslashes($_POST['titre'])."', TEXT = '".addslashes($_POST['text_article'])."', META_KEY = '".addslashes($_POST['meta_key'])."', META_DESC = '".addslashes($_POST['meta_desc'])."', DT_MODIF = ".time().", DT_DEB_PUBLI = ".$time_deb." ,RESUME = '".addslashes($_POST['resume'])."', IMG_RESUME = '".addslashes($_POST['img_resume'])."', DT_FIN_PUBLI = ".$time_fin." WHERE ID = ".$list_parent[$i]['id_item']);
						ecrire_log('Article',"Mise à jour de l\'article :  ID : ".$list_parent[$i]['id_item']);
						
						modif_search($_POST['titre'].'||'.$_POST['text_article'].'||'.$_POST['resume'].'||'.$_POST['meta_desc'].'||'.$_POST['meta_key'],$list_parent[$i]['id_item'],0,$_SESSION['langue'],$_SESSION['univers']);
						
						
					}
				}
			}
			if(!$deja_mis){
				//echo "insert !";
				$result=$tree->nstNewLastChild($parent,
				array(
				'ID_USER'=>$_SESSION['id'],
				'ID_ELEMENT'=>$_POST['id'],
				'ID_LANG'=>$_SESSION['langue'],
				'ID_UNIVERS'=>$_SESSION['univers'],
				'DT_CREATE'=>time(),
				'DT_MODIF'=>time(),
				'DT_DEB_PUBLI'=>$time_deb,
				'DT_FIN_PUBLI'=>$time_fin,
				'TYPE'=>1,
				'TITLE'=>"'".addslashes($_POST['titre'])."'",
				'RESUME'=>"'".addslashes($_POST['resume'])."'",
				'IMG_RESUME'=>"'".addslashes($_POST['img_resume'])."'",
				'TEXT'=>"'".addslashes($_POST['text_article'])."'",
				'META_DESC'=>"'".addslashes($_POST['meta_desc'])."'",
				'META_KEY'=>"'".addslashes($_POST['meta_key'])."'")
				);	

				$id_element=$db->queryUniqueValue("SELECT ID FROM cms_nst WHERE `LFT` = ".$result['l']." AND `RGT` = ".$result['r']." AND ID_LANG =".$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers']);
		
				add_search($_POST['titre'].'||'.$_POST['text_article'].'||'.$_POST['resume'].'||'.$_POST['meta_desc'].'||'.$_POST['meta_key'],$id_element,0,$_SESSION['langue'],$_SESSION['univers']);
				
				ecrire_log('Article',"Insertion de l\'article :  LFT : ".$result['l'].' RGT : '.$result['r']);	
			}			
		}
		
		/*
		Traitement des suppressions des différents emplacements retirés
		*/
		if(count($list_parent)!=0){
			for($i=0;$i<count($list_parent);$i++){				
				if($list_parent[$i]['still']==0){
					//echo "delete !";
					//pr($list_parent[$i]['node_item']);	
					$list_parent[$i]['node_item']=$tree->nstGetNodeWhere("ID=".$list_parent[$i]['id_item']);
					if(is_numeric($list_parent[$i]['node_item']['l']) && is_numeric($list_parent[$i]['node_item']['r'])){	
						
						$tree->nstDelete($list_parent[$i]['node_item']);
						$db->execute('DELETE FROM cms_search WHERE ID_ELEMENT = '.$list_parent[$i]['id_item'].' AND TYPE = 0');
						
						ecrire_log('Article',"Suppression de l\'article inséré :  ".$list_parent[$i]['id_item'].' LFT : '.$list_parent[$i]['node_item']['l'].' RGT : '.$list_parent[$i]['node_item']['r']);
					}
				}			
			}
		}
		$_SESSION['notification'][]=array(1,"Articles","L'article a été modifié.");
		header('location:article.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Articles","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
elseif($action=='delete'){/*Suppression*/
	if(isset($_GET['id'])){
		if(preg_match("/arborescence/i",$_SERVER["HTTP_REFERER"]))$url_back='arborescence.php';
		else $url_back='article.php';
	
		$tree = new nestedTree($handle);
		$req=$db->query("SELECT * FROM cms_nst WHERE TYPE = 1 AND ID_ELEMENT = ".$_GET['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req)){
				//pr($don);
				$id_nst=$don['ID'];
				$don=$tree->nstGetNodeWhere("ID=".$don['ID']);				
				if(is_numeric($don['l']) && is_numeric($don['r'])){		
					$tree->nstDelete($don);
					
					
					ecrire_log('Article',"Suppression de l\'article inséré : ".$don['ID'].' LFT : '.$don['l'].' RGT : '.$don['r']);
				}
			}
		}
		$db->execute("DELETE FROM cms_article WHERE ID = ".$_GET['id']);
		$db->execute('DELETE FROM cms_search WHERE ID_ELEMENT = '.$_GET['id'].' AND TYPE = 0');
		$db->execute('DELETE FROM cms_vertical_navigation WHERE ID_ELEMENT = '.$_GET['id'].' AND TYPE = 2');
		$db->execute('DELETE FROM cms_footer WHERE ID_ELEMENT = '.$_GET['id'].' AND TYPE = 2');
		$_SESSION['notification'][]=array(1,"Articles","L'article a été supprimé.");
		header('location:'.$url_back);		
		die();
	}
}

if($action==''){
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter un article');
	echo genere_sous_menu_admin($array_menu);
	echo '
	<script>
		$(document).ready(function(){
			'.auto_help("Liste des articles","Si vous souhaitez visualisez/modifier/supprimer un article, cliquez sur le bouton d’action souhaité associé au titre.").'
		});
	</script>
	';

}

if($action=='add' || $action=='edit'){

	$tree = new nestedTree($handle);
	
	$form=array();
	if($action=='edit'){
		
		$req=$db->query('SELECT * FROM cms_article WHERE ID = '.$_GET["id"]);
		if(mysql_num_rows($req)!=0){
			$form=mysql_fetch_array($req);
			$form=array_map("format",$form);

			$form['DT_DEB_PUBLI']=date('d/m/Y',$form['DT_DEB_PUBLI']);
			$form['DT_FIN_PUBLI']=date('d/m/Y',$form['DT_FIN_PUBLI']);
		}
		
		$list_parent=array();
		$req=$db->query("SELECT * FROM cms_nst WHERE TYPE = 1 AND ID_ELEMENT = ".$_GET['id']);
		//echo "SELECT * FROM cms_nst WHERE TYPE = 1 AND ID_ELEMENT = ".$_GET['id'];
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req)){
				$don['l']=$don['LFT'];
				$don['r']=$don['RGT'];
				$parent=$tree->nstAncestor($don);
				$list_parent[]=$parent;			
			}	
		}
	}
	
	if($form['DT_DEB_PUBLI']==''){
		$form['DT_DEB_PUBLI']=date('d/m/Y',time());
		$date_article=date('Y');
		$date_article+=10;
		$form['DT_FIN_PUBLI']=date('d/m/',time()).$date_article;
	}
	$rand_prev=mt_rand(1,250000);
?>
	<script type="text/javascript" src="<?echo URL_DIR;?>/tiny_mce/tiny_mce.js"></script>
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/uidatepickerfr.js"></script>
	<link rel="stylesheet" href="<?echo URL_DIR;?>/css/base/jquery.ui.all.css" type="text/css" media="screen" charset="utf-8" />
	
	<script type='text/javascript'>
	$(document).ready(function() {
		$("#date_deb,#date_fin" ).datepicker({ minDate: 0, changeMonth: true,	changeYear: true});
		$('#step2_admin,#step3_admin').hide();
	});
	num_step_cours=1;
	<? echo genere_tiny_mce('text_article');?>
	<? echo genere_file_browser();?>
	function preview_article(){
	
	
		var rand=<? echo $rand_prev;?>;
		var url_preview='<? echo URL_DIR;?>/article.php?id_preview2=<?echo $rand_prev;?>';
		var content =  tinyMCE.get('text_article').getContent();
		var titre = $('#titre').val();
		
		if(titre=='')titre='Pas de titre';
		if(content=='')content='Pas de contenu';
		
		$.post("add_prev_article.php",{id_article:rand,titre_article:titre,text_article:content},
		   function success(data){
				popupWindow(url_preview, 'Prévisualisation', 1000, 700, 1)
		   }, "json");
		
	}
	function launch_step(num_step){
		if(num_step==1){
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/3');
		}
		else if(num_step==2){
			if($('#titre').val()==''){
				$('#complete_all').show();
				$('#titre').css({"border":"1px solid #F00"});
			}else{
				$('#complete_all').hide();
				$('#titre').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=2;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/3');
			}
		}
		else if(num_step==3){
			var content =  tinyMCE.get('text_article').getContent();
			if(content==''){
				$('#complete_all2').show();
			}else{
				$('#complete_all2').hide();
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=3;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 3/3');
			}
		}
	}
	function valide_form(){
		document.forms["form_art"].submit();
	}
	</script>
	<form method="post"  name="form_art" action="article.php?action=<?echo $action;?>_r">
	<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/3</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/btn_article_add.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >
		<div>Titre de l'article <span id="complete_all" style="display:none;"><b style='color:#F00;'>- Merci de saisir un titre</b></span></div>
		<div style="margin-top:15px;" <? echo add_help("Titre de l'article","Le titre de votre article est repris dans l’URL de sa page.<br/>Soyez concis et précis dans sa rédaction<br/><a href='#' id='titre_guide'>Voir Guide Nom de domaine et URL</a>","titre_guide","faq_view.php?id_aide=43&id_cat=9"); ?>>
			<input name="titre" type="text" value='<?echo $form['TITLE'];?>' id="titre" style="width:650px;" />
		</div>
		<div style='margin-top:20px;' <? echo add_help("Emplacement de l'article","Sélectionnez l’emplacement dans votre Arborescence (<a href='#' id='titre_guide2'>Voir Guide Arborescence</a>) où vous souhaitez voir apparaitre votre article<br/>La position &quot;Non classifié&quot; signifie que votre article est bien enregistré mais il n’est pas visible par l’internaute faute d’emplacement","titre_guide2","faq_view.php?id_aide=23&id_cat=8"); ?>>Emplacement de l'article</div>
		<div style='margin-top:15px;width:225px;'>
			<select name="check" class="select_class" style="width:225px;" tabindex="1">
			<option value="">Non classifié</option>
			<?
			$walk = $tree->nstWalkPreorder($tree->nstRoot());
			while($curr = $tree->nstWalkNext($walk)) {
				//pr($walk);
				$checked='';
				$node_actual['l']=$walk['prevl'];
				$node_actual['r']=$walk['prevr'];
				if($walk['row']['TYPE']==0 && $walk['row']['LFT']!=1){
					for($i=0;$i<count($list_parent);$i++){	
						if($tree->nstEqual($node_actual,$list_parent[$i])){
							$checked="selected='selected'";
						}
					}
					
					echo '<option value="'.$walk['row']['ID'].'" '.$checked.'> '.str_repeat('&nbsp;&nbsp;',$walk['level']).' '.htmlspecialchars($walk['row']['TITLE'],ENT_QUOTES,"UTF-8").'</option>';
				}
			}
			?>
			</select>
		</div>
		<div class="clear"></div>
		<div style="margin-top:35px;">
			
			<div class="bordure_menu" style='width:155px;float:left;display:none;'>
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_see.png");' class="btn_style"><p>Aperçu de l'article</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			
			<?if($action=='edit'){?>
				<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_edit.png");' class="btn_style">
						<p>Modifier l'article</p>
					</div>
				</div>
			<?}?>
			
			<div class="bordure_menu" style='width:155px;float:left;display:none;'>
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('article.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
			
		</div>
		<div class="clear"></div>	
	</div>
	<div  id="step2_admin">
		<div>Rédaction de votre article <span id="complete_all2" style="display:none;"><b style='color:#F00;'>- Merci de rédiger l'article</b></span></div>
		<div style="margin-top:12px;" <? echo add_help("Contenu de l'article","La rédaction de votre article passe par un éditeur de texte type Word.<br/>N’hésitez pas à insérer des photos, vidéos, audios et des liens vers des sites extérieurs pour enrichir votre contenu.<br/><a href='#' id='titre_guide3'>Voir les guides <br/>&quot;Comment créer un contenu ?&quot;</a>","titre_guide3","faq_view.php?id_cat=5"); ?>><textarea name="text_article" style="width: 660px;height:250px;" id="text_article" class="tinymce"><?echo $form['TEXT'];?></textarea></div>
		<div class="clear"></div>
		<div style="margin-top:35px;">
			
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:preview_article();">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_see.png");' class="btn_style"><p>Aperçu de l'article</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="javascript:launch_step(3);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_<?if($action=='add'){echo 'add';}else{ echo 'edit';}?>.png");' class="btn_style"><p><?if($action=='add'){echo 'Ajouter l\'article';}else{ echo 'Modifier l\'article';}?></p></div>
			</div>
		</div>
		<div class="clear"></div>
		<div style="background-color:#cccccc;width:100%;height:1px;margin-top:20px;"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>
		<div style="margin-top:20px;">	
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(1);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('article.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
			
		</div>
		<div class="clear"></div>
	</div>
	<div id="step3_admin">
		<div>Résumé de l'article</div>
		<div style="margin-top:10px;">
			<div style="width:460px;float:left;" <? echo add_help("Résumé de l'article","Si votre article est placé au sein d'une rubrique, le résumé présentera brièvement votre article à l'aide d'un paragraphe et d'une illustration. Si vous ne remplissez pas le résumé, le début de votre article sera repris automatiquement"); ?>><textarea name="resume" style="width:458px;height:65px;" id="resume_zone"><?echo $form['RESUME'];?></textarea></div>	
			<div style="float:right;width:168px;" <? echo add_help("Image du résumé","Chaque article, comme les Rubriques ou sous Rubriques peut apparaître avec une image d’illustration pour animer votre site. Une taille de 150 * 100 pixels est conseillée.<br/><a href='#' id='titre_guide4'>Voir le guide Sous-rubriques</a>",'titre_guide4',"faq_view.php?id_aide=25&id_cat=8"); ?>>
			<?
			$dir_upload2=ADD_DIR.'/medias/';
			echo form_picture(150,100,'img_resume',$form['IMG_RESUME'],$dir_upload2);
			?>				
			
		</div>
		<div class="clear"></div>		
		<div style='margin-top:15px;'>Description</div>
		<div style="margin-top:5px;" <? echo add_help('Meta description',"La Description de la page doit respecter certaines règles pour optimiser le référencement de votre page.<br/><a href='#' id='titre_guide5'>Voir le guide Meta-description</a>",'titre_guide5',"faq_view.php?id_aide=39&id_cat=9 ");?>><input name="meta_desc" type="text" value='<?echo $form['META_DESC'];?>'id="meta_desc" style="width:650px;"/></div>
		<div style='margin-top:15px;'>Mots clés</div>
		<div style="margin-top:5px;" <? echo add_help('Mots clés',"C'est le terme (ou combinaison de mots) que l'internaute va utiliser pour effectuer une recherche.<br/><a href='#' id='titre_guide6'>Voir le guide Mots clés</a> pour plus d’informations.",'titre_guide6','faq_view.php?id_aide=40&id_cat=8');?>><input name="meta_key" type="text" value='<?echo $form['META_KEY'];?>' id="meta_key" style="width:650px;"/></div>
		<div style='margin-top:15px;'>
			<div style="float:left;width:160px;">Publication</div>
			<div style="float:left;width:160px;">Retrait de publication</div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:5px;" <? echo add_help("Début et fin de publication","Vous avez la possibilité de gérer les dates de publication de votre article, sélectionnez les dates de début et de fin. Sans intervention de votre part, votre article est publié de manière permanente.");?>>
			<div style="float:left;width:160px;"><input name="date_deb" type="text" value='<?echo $form['DT_DEB_PUBLI'];?>' id="date_deb" style="width:120px;"/></div>
			<div style="float:left;width:160px;"><input name="date_fin" type="text" value='<?echo $form['DT_FIN_PUBLI'];?>' id="date_fin" style="width:120px;"/></div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:35px;">			
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:preview_article();">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_glass.png");' class="btn_style"><p>Aperçu de l'article</p></div>
			</div>					
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_<?if($action=='add'){echo 'add';}else{ echo 'edit';}?>.png");' class="btn_style"><p><?
						if($action=='add'){echo 'Ajouter l\'article';}
						else{ echo 'Modifier l\'article';}
						?></p></div>
			</div>
		</div>
		<div class="clear"></div>
		<div class="separation_btn"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>
		<div style="margin-top:20px;">	
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('article.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
			
		</div>
	</div>
	</form>
<?	
}
else{
?>
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/pagination.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript">
	function lookup(inputString) {
		if(inputString.length == 0) {
			$('#suggestions').hide();
		} else {
			$.post("search.php", {queryString: ""+inputString+""}, function(data){
				if(data.length >0) {
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
				}
			});
		}
	} 	
	function fill(thisValue) {
		$('#inputString').val(thisValue);
		setTimeout("$('#suggestions').hide();", 200);
	}
</script>
<style type="text/css">
	.suggestionsBox {
		position: absolute;
		margin: 10px 0px 0px 110px;
		width: 200px;
		background-color: #E2E2E2;
		-moz-border-radius: 7px;
		-webkit-border-radius: 7px;
		border: 2px solid #ccc;	
		color: #666666;
	}	
	.suggestionList {
		margin: 0px;
		padding: 0px;
	}	
	.suggestionList li {		
		margin: 0px 0px 3px 0px;
		padding: 3px;
		cursor: pointer;
		list-style-type: none; 
	}	
	.suggestionList li:hover {
		background-color: #f8f8f8;
	}
</style>

<?

	if($filter!=''){
		switch($filter){
		
			case "dt_crea" :
			$order_by='DT_CREATE DESC';
			break;
			
			case "dt_modif" :
			$order_by='DT_MODIF DESC';
			break;
			
			default:
			$order_by='TITLE ASC';
			break;
			
		}
	}
	else $order_by='TITLE ASC';

	
	$nb_article=$db->countOf('cms_article',"ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']);	
	$req=$db->query("select * from cms_article where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by ".$order_by." LIMIT ".($page*$perpage).','.$perpage);

	
	//pr($user_list);
		
	if(mysql_num_rows($req)!=0){
	
		
		$user_list=User::get_users();
		
		if($filter=='dt_crea')$sel_crea='selected="selected"';
		else $sel_crea='';
		if($filter=='dt_modif')$sel_modif='selected="selected"';
		else $sel_modif='';
		
		echo "
		<br/>
		<div style='float:left;' ".add_help("Rechercher un article","Saisissez les premières lettres du titre de l'article que vous recherchez, puis lorsque les résultats s'affichent, cliquez sur l'article souhaité").">
			<div id='zone_items'>Rechercher un article : 
				<input type=\"text\" size=\"30\" value=\"\" id=\"inputString\" onkeyup=\"lookup(this.value);\" onblur=\"fill();\" />
			</div>
			<div class=\"suggestionsBox\" id=\"suggestions\" style=\"display: none;\">
				<img src=\"".URL_DIR."/images/upArrow.png\" style=\"position: relative; top: -12px; left: 30px;\" alt=\"upArrow\" />
				<div class=\"suggestionList\" id=\"autoSuggestionsList\">
					&nbsp;
				</div>
			</div>
		</div>
		<div style='float:right;' ".add_help("Trier les articles","Sélectionnez dans le menu déroulant le type de tri souhaité").">
			<form method='get' action='article.php'>		
			<div style='float:left;margin-right:10px;margin-top:3px;'>Trier par : </div>
			<div style='float:left;width:200px;'>
				<select name='filter' onchange='this.form.submit();'  style='width:200px;' class='select_class'>
					<option value=''>Nom</option>
					<option value='dt_crea' ".$sel_crea.">Date de création</option>
					<option value='dt_modif' ".$sel_modif.">Date de modification</option>			
				</select>	
			</div>
			</form>		
		</div>
		
		<div class='clear'></div>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<b>Liste de vos articles présents sur le site</b>
		<div style='height:10px;width:100%;'>&nbsp;</div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view' ".add_help("Liste des articles","Si vous souhaitez visualisez/modifier/supprimer un article, cliquez sur le bouton d’action souhaité associé au titre.").">
			<thead>
				<tr>
					<th class='frst'>Titre de l'article</th>			
					<th width='80'>Voir</th>
					<th width='80'>Modifier</th>
					<th width='80'>Dupliquer</th>
					<th width='80'>Supprimer</th>
				</tr>
			</thead>
			<tbody>
		";
		$i=0;
		while($don=mysql_fetch_array($req)){
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			echo "
			<tr ".$style_table.">
				<td class='frst'>".$don['TITLE']."</td>
				<td><a href='".URL_DIR."/article.php?id_preview=".$don['ID']."' target='_blank'><img src='".URL_DIR."/images/btn_see.png'></a></td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=duplicate&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_duplicate.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
			</tr>
			";
		}
		
		echo "
			</tbody>
		</table>
		<div style='width:100%;margin-top:10px;' class='pagination'>
		";
		
		echo genere_pagination($page,$nb_article,$perpage,'&filter='.$filter);
		
		echo '</div>';
		
		
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucun article actuellement";
	}
}
?>

<?
include('footer.php');
?>