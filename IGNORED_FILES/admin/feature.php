<?
$id_module=22;
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="form.php";
	$ariane_element2['LIBELLE']="Ajouter une mise en avant";					
}elseif($action=='edit'){
	$ariane_element2['URL']="form.php";
	$ariane_element2['LIBELLE']="Modifier une mise en avant";
}

include('header.php');


/*Ajout*/
if($action=='add_r'){
	//pr($_POST);
	if(isset($_POST['libelle']) && $_POST['libelle']!=""){
		
		if(isset($_POST['checker']) && $_POST['checker']=='all')$checker=1;		
		else $checker=0;		
				
		$count=$db->countOf('cms_feature',"ID_LANG =".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." AND LOCATION = ".$_POST['colonne']);		
				
		$db->execute("INSERT INTO cms_feature (`LIBELLE`,`TEXTE`,`NAME`,`ID_LANG`,`ID_UNIVERS`,`DT_CREATE`,`DT_MODIF`,`ALL`,`LOCATION`,`POSITION`,`ORDRE`) VALUES ('".addslashes($_POST['libelle'])."','".addslashes($_POST['resume'])."','".addslashes($_POST['name'])."',".$_SESSION['langue'].",".$_SESSION['univers'].",".time().",".time().",".$checker.",".$_POST['colonne'].",".$_POST['position'].",".($count+1).")");
		
		if(!$checker){
			$id_promo=$db->lastInsertedId();
			if(count($_POST['checke'])!=0){
				for($i=0;$i<count($_POST['checke']);$i++){
					$temp_type=explode('-',$_POST['checke'][$i]);
					$id_element=$temp_type[1];
					$type=0;
					switch($temp_type[0]){
						case "arbo" :
						$type=1;
						break;
						
						case "galerie" :
						$type=2;
						break;
						
						case "video" :
						$type=3;
						break;
					}
					$db->execute('INSERT INTO cms_feature_rel (ID_ELEMENT,ID_PUB,TYPE) VALUES ('.$id_element.','.$id_promo.','.$type.')');
				}
			}
		}		
		
		$_SESSION['notification'][]=array(1,"Mise en avant","La mise en avant a été ajoutée.");
		header('location:feature.php');		
		die();

	}	
	else{
		$_SESSION['notification'][]=array(0,"Mise en avant","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['libelle']) && $_POST['libelle']!=""){
		
		if(isset($_POST['checker']) && $_POST['checker']=='all')$checker=1;		
		else $checker=0;		
				
		$db->execute("UPDATE cms_feature SET `LIBELLE` = '".addslashes($_POST['libelle'])."',`NAME` = '".addslashes($_POST['name'])."',`TEXTE` = '".addslashes($_POST['resume'])."',`DT_MODIF` = ".time().",`ALL` = ".$checker.",`LOCATION` = ".$_POST['colonne'].",`POSITION` =".$_POST['position']." WHERE ID = ".$_POST['id']);
		$db->execute("DELETE FROM cms_feature_rel where ID_PUB = ".$_POST['id']);
		
		
		if(!$checker){
			$id_promo=$_POST['id'];
			if(count($_POST['checke'])!=0){
				for($i=0;$i<count($_POST['checke']);$i++){
					$temp_type=explode('-',$_POST['checke'][$i]);
					$id_element=$temp_type[1];
					$type=0;
					switch($temp_type[0]){
						case "arbo" :
						$type=1;
						break;
						
						case "galerie" :
						$type=2;
						break;
						
						case "video" :
						$type=3;
						break;
					}
					$db->execute('INSERT INTO cms_feature_rel (ID_ELEMENT,ID_PUB,TYPE) VALUES ('.$id_element.','.$id_promo.','.$type.')');
				}
			}
		}		
		
		$_SESSION['notification'][]=array(1,"Mise en avant","La mise en avant a été modifiée.");
		header('location:feature.php');		
		die();

	}	
	else{
		$_SESSION['notification'][]=array(0,"Mise en avant","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
if($action=='ordre_r'){	
	$req=$db->query("select * from cms_feature WHERE ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." AND LOCATION = ".$_POST['LOCATION']." order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_feature","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['feature_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Mise en avant","La position de la mise en avant a été modifiée.");
	header('location:feature.php');
	die();	
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		
		$req=$db->query("select * from cms_feature WHERE ID = ".$_GET['id']." order by ordre ASC");
		if(mysql_num_rows($req)!=0){
			$info_element=mysql_fetch_array($req);
			$req=$db->query('select * from cms_feature where `ORDRE` > '.$info_element['ORDRE'].' AND ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND LOCATION = '.$info_element['LOCATION']);
			if(mysql_num_rows($req)!=0){
				while($don=mysql_fetch_array($req)){
					$db->execute('update cms_feature set `ORDRE` = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
				}
			}
		}
		
		$db->execute("DELETE FROM cms_feature WHERE ID = ".$_GET['id']);
		$db->execute('DELETE FROM cms_feature_rel WHERE ID_PUB = '.$_GET['id']);
		$_SESSION['notification'][]=array(1,"Mise en avant","La mise en avant a été supprimée.");
		header('location:feature.php');		
		die();
	}
}

if($action==""){
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une mise en avant','WIDTH'=>'200');
	echo genere_sous_menu_admin($array_menu);
	echo '
	<script>
		$(document).ready(function(){
			'.auto_help('Mises en avant',"Les mises en avant permettent d’animer vos contenus et favoriser le rebond de votre internaute.<br/><a href='#' id='titre_guide'>Voir le guide &quot;Mises en avant&quot;</a>",'titre_guide','faq_view.php?id_aide=29&id_cat=8').'
		});
	</script>
	';
}
if($action=='add' || $action=='edit'){

	$form=array();
	$list_droit=array();
	if($action=='edit'){
		
		$req=$db->query('select * from cms_feature where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
		
		$list_galerie=array();
		$list_arbo=array();
		$list_video=array();
		
		$req=$db->query('select ID_ELEMENT from cms_feature_rel where TYPE = 1 AND ID_PUB = '.$_GET['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req))$list_arbo[]=$don['ID_ELEMENT'];
		}
		
		$req=$db->query('select ID_ELEMENT from cms_feature_rel where TYPE = 2 AND ID_PUB = '.$_GET['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req))$list_galerie[]=$don['ID_ELEMENT'];
		}
		
		$req=$db->query('select ID_ELEMENT from cms_feature_rel where TYPE = 3 AND ID_PUB = '.$_GET['id']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req))$list_video[]=$don['ID_ELEMENT'];
		}
		
	}
	else $form['POSITION']=1;

?>
	<script type="text/javascript" src="<?echo URL_DIR;?>/tiny_mce/tiny_mce.js"></script>
	<link rel="stylesheet" href="<?echo URL_DIR;?>/css/jquery.treeview.css" />
	<script src="<?echo URL_DIR;?>/js/jquery.cookie.js" type="text/javascript"></script>
	<script src="<?echo URL_DIR;?>/js/jquery.treeview.js" type="text/javascript"></script>
	<script type="text/javascript">
	<? echo genere_file_browser();?>
	$(document).ready(function() {
		$("#form_user").validate({meta: "validate"});
		$("#browser").treeview({animated: "fast",persist: "cookie"});
		$('#step2_admin,#step3_admin').hide();
	});	
	function toggle_check(zone){
		$("#"+zone).checkCheckboxes(); 
	}	
	
	num_step_cours=1;
	function launch_step(num_step){
		if(num_step==1){
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/3');
		}
		else if(num_step==2){
			if($('#libelle').val()==''){
				$('#complete_all').show();
				$('#libelle').css({"border":"1px solid #FF0000"});
			}else{
				$('#complete_all').hide();
				$('#libelle').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=2;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/3');
				
				
				<? echo genere_tiny_mce('resume');?>
			}
		}
		else if(num_step==3){
			var content =  tinyMCE.get('resume').getContent();
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
		if($('input:checkbox[name=checker]:checked').val() == undefined && $('input:checkbox[name=checke[]]:checked').val()==undefined){
			$('#complete_all3').show();
		}else{
			$('#complete_all3').hide();
			document.forms["form_feature"].submit();
		}
	}
	</script>
	<form method="post"  name="form_feature" action="feature.php?action=<?echo $action;?>_r">
	<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/3</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>	
	<div id="step1_admin" >
		<div>Libellé de la mise en avant <span id="complete_all" style="display:none;"><b style='color:#FF0000;'>- Merci de saisir un libellé</b></span></div>
		<div style="margin-top:10px;" <?echo add_help("Libellé de la mise en avant","Ce libellé ne sera pas affiché sur le site, il vous permet de retrouver facilement votre Mise en Avant dans la liste."); ?>>
			<input name="libelle" type="text" value='<?echo $form['LIBELLE'];?>' id="libelle" style="width:650px;" />
		</div>
		<div style='margin-top:20px;'>Titre de la mise en avant</div>
		<div style='margin-top:10px;' <? echo add_help("Titre de la mise en avant","Le titre sera affiché en entête sur les colonnes de votre site.");?>>
			<input name="name" type="text" value='<?echo $form['NAME'];?>' id="name" style="width:650px;" />
		</div>
		<div style='margin-top:20px;'>Colonne dans laquelle la mise en avant sera diffusée :</div>
		<?
		//préparation colonnes
		if(LEFT_COLUMN)$text_cl_lft='active';
		else $text_cl_lft='désactivée';
		
		if(RIGHT_COLUMN)$text_cl_rgt='active';
		else $text_cl_rgt='désactivée';	
		?>
		<div style='margin-top:10px;' <? echo add_help("Colonnes","Pour que votre mise en avant soit visible, vérifiez que la colonne destination soit en statut <b>actif</b>.<br/>Vérifiez son statut en cliquant <a href='colonne.php' target='_blank'>ici</a> ou allez dans <b style='color:#c09;'>Menu</b> puis <b style='color:#c09;'>Colonnes</b>.<br/><a href='#' id='titre_guide'>Consultez le guide &quot;Colonnes&quot;</a>",'titre_guide','faq_view.php?id_aide=28&id_cat=8');?>>
			<div style="width:200px;">
				<select name="colonne" class="select_class" style="width:200px;">
					<?
					$req=$db->query("select * from cms_column WHERE ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." ORDER BY `LEFT` DESC");
					while($don=mysql_fetch_array($req)){
						if($don['ID']==$form['LOCATION'])$sel='selected="selected"';
						else $sel='';
						
						echo '<option value="'.$don['ID'].'" '.$sel.'>'.stripslashes($don['LIBELLE']).'</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="clear"></div>
		<div style='margin-top:20px;' <? echo add_help("Colonnes","<a href='#' id='titre_guide2'>Consultez le guide &quot;Position d’une Mise en Avant&quot;</a>",'titre_guide2','faq_view.php?id_aide=48&id_cat=8');?>>Position de la mise en avant :</div>
		<div style='margin-top:10px;'>
			<div style="width:250px;">
				<select name="position" class="select_class" style="width:250px;">
					<option value="1">Après les menus de la colonne</option>
					<option value="0" <?if($form['POSITION']==0)echo 'selected="selected"';?>>Avant les menus de la colonne</option>				
				</select>
			</div>
		</div>
		<div class="clear"></div>
		<div style="margin-top:35px;">
			
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			<?if($action=='edit'){?>
				<div class="bordure_menu" style='width:195px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
					<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
						<p>Modifier la mise en avant</p>
					</div>
				</div>
			<?}?>
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('feature.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
			
		</div>
		<div class="clear"></div>
	</div>	
	<div  id="step2_admin">
		<div>Mise en avant <span id="complete_all2" style="display:none;"><b style='color:#FF0000;'>- Merci de remplir votre mise en avant</b></span></div>
		<div style="margin-top:12px;" <? echo add_help("Mise en avant","Votre Mise peut contenir :<br/>- un texte<br/>- un visuel<br/>- lecteur audio<br/>Utilisez l’éditeur de texte ci-dessous pour créer votre Mise en Avant et les boutons d’insertion d’image ou de lecteur audio.");?>><textarea name="resume" style="width: 655px;height:250px;" id="resume" class="tinymce"><?echo $form['TEXTE'];?></textarea></div>
		<div class="clear"></div>
		<div style="margin-top:40px;">	
			
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(1);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>
			
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="javascript:launch_step(3);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>			
			<?if($action=='add'){?>
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('feature.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
			<?}else{
			?>
			<div class="bordure_menu" style='width:195px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
					<p>Modifier la mise en avant</p>
				</div>
			</div>
			<?
			}?>
		</div>
		<div class="clear"></div>
		<?if($action=='edit'){?>
		<div style="background-color:#cccccc;width:100%;height:1px;margin-top:20px;"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>
		<div style="margin-top:20px;">	
			<div class="bordure_menu" style='width:155px;float:left;' onclick="redir('feature.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>	
		</div>
		<div class="clear"></div>
		<?}?>
	</div>
	<div id="step3_admin">
		<div>Page(s) où sera visible votre mise en avant :</div>
		<div style="margin-top:15px;">
			<input type="checkbox" value="all" name="checker" <?if($form['ALL']==1)echo 'checked="checked"';?>><b>Ma mise en avant sera visible sur l'ensemble du site</b>
		</div>
		<div style="margin-top:20px;"><b>Ou</b><br/>Sélectionnez les emplacements où votre mise en avant sera visible :</div>
		<div style="margin-top:15px;" <? echo add_help("Sélection des emplacements","Cliquez sur les croix pour déplier les éléments, cochez la case correspondante pour rendre visible la mise en avant sur la page sélectionnée.");?>>
			<ul id="browser" class="filetree">
				<li class="closed"><span class="folder"><b>Arborescence</b> <a href='javascript:toggle_check("arbor");'>Tout Cocher</a></span> 
					<ul id="arbor">
					<?
					$tree = new nestedTree($handle);
					$walk = $tree->nstWalkPreorder($tree->nstRoot());
					$prev_lvl=0;
					$i=0;
					while($curr = $tree->nstWalkNext($walk)) {
						//pr($walk);
						if($walk['row']['LFT']!=1){

												
							if($walk['level']>$prev_lvl){//si le niveau actuel est supérieur au précédent
								$view.='<ul>
								';
							}
							else if($walk['level']<$prev_lvl){//si plus bas que le précédent
								
								$view.=str_repeat('
								</li>
							</ul>',($prev_lvl-$walk['level']));
								
							}else if($i!=0){
								$view.='
								</li>';
							}
							
							$chck='';
							
							
							if($walk['row']['TYPE']!=0){
								if(count($list_arbo)!=0 && in_array($walk['row']['ID'],$list_arbo))$chck='checked="checked"';
								$check_plus='<input type="checkbox" value="arbo-'.$walk['row']['ID'].'" name="checke[]" '.$chck.'>';
							}
							else $check_plus='';
							
							$view.='
							<li>'.$check_plus.'<span>'.stripslashes($walk['row']['TITLE']).'</span>';
							$prev_lvl=$walk['level'];	
							$i++;
						}					
					}
					
					
					if($i==1)$view.='</li></ul>';
					$view.=str_repeat('</li></ul>',$prev_lvl);	
					echo $view;
					?>
					</ul>
				</li>
				<li class="closed" ><span class="folder"><b>Galerie photos</b> <a href='javascript:toggle_check("galer");'>Tout Cocher</a></span>
					<ul id="galer">
					<?
					$req=$db->query("select * from cms_galerie where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by TITLE ASC");
					while($don=mysql_fetch_array($req)){
						$chck='';
						if(count($list_galerie)!=0 && in_array($don['ID'],$list_galerie)){
							$chck='checked="checked"';
						}
						echo '<li><input type="checkbox" value="galerie-'.$don['ID'].'" name="checke[]" '.$chck.'> '.stripslashes($don['TITLE']).'</li>';
					}
					?>	
					</ul>
				</li>
				<li class="closed" ><span class="folder"><b>Vidéos</b> <a href='javascript:toggle_check("videor");'>Tout Cocher</a></span>
					<ul id="videor">
					<?
					$req=$db->query("select * from cms_video where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by TITLE ASC");
					while($don=mysql_fetch_array($req)){
						$chck='';
						if(count($list_video)!=0 && in_array($don['ID'],$list_video)){
							$chck='checked="checked"';
						}
						echo '<li><input type="checkbox" value="video-'.$don['ID'].'" name="checke[]" '.$chck.'>'.stripslashes($don['TITLE']).'</li>';
					}
					?>	
					</ul>
				</li>
			</ul>
			</div>
			<div class="clear"></div>
			
			<div style="margin-top:30px;">	
				<div id="complete_all3" style="display:none;margin-bottom:5px;"><b style='color:#CC0099;'>Merci de choisir où sera placé votre mise en avant</b></div>
				<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
				</div>				
				<div class="bordure_menu" style='width:195px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
					<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
						<p><?
						if($action=='add'){echo 'Ajouter la mise en avant';}
						else{ echo 'Modifier la mise en avant';}
						?></p>
					</div>
				</div>				
				<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('feature.php');">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
				</div>				
			</div>
			<div class="clear"></div>
		</div>
	</div>
	
	</form>
<?	
}
else{

	$req=$db->query("select * from cms_feature where ID_LANG =".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by LOCATION,ORDRE ASC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<b>Liste de vos mises en avant présentes sur le site</b>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
				<tr>
					<th class='frst'>Libellé</th>
					<th width='150'>Colonne concernée</th>
					<th width='95'>Ordre</th>
					<th width='95'>Modifier</th>
					<th width='95'>Supprimer</th>
				</tr>
			</thead>
		";
		
		$i=0;
		while($don=mysql_fetch_array($req)){
			
			$don=array_map('stripslashes',$don);
			
					
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			if($don['LOCATION']==1)$col=$column_left['LIBELLE'];
			if($don['LOCATION']==2)$col=$column_right['LIBELLE'];
			
			
			$count=$db->countOf('cms_feature',"ID_LANG =".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." AND LOCATION = ".$don['LOCATION']);
			
			
			echo"
			<tr ".$style_table.">
				<td class='frst'>".$don['LIBELLE']."</td>
				<td >".$col."</td>
				<td>".genere_form_ordre('feature_ordre','?action=ordre_r',1,$count,$don['ORDRE'],0,array('ID'=>$don['ID'],'LOCATION'=>$don['LOCATION']))."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "
		<div style='height:15px;width:100%;'>&nbsp;</div>
		Aucune mise en avant actuellement";
	}
}
?>

<?
include('footer.php');
?>