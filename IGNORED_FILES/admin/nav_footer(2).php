<?
$id_module=21;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="nav_footer.php";
	$ariane_element2['LIBELLE']="Ajouter un élément au menu";					
}elseif($action=='edit'){
	$ariane_element2['URL']="nav_footer.php";
	$ariane_element2['LIBELLE']="Modifier un élément du menu";
}

include('header.php');

$tree = new nestedTree($handle);


/*Ajout*/
if($action=='add_r'){

	if(isset($_POST['type_titre']) && is_numeric($_POST['type_titre']) && isset($_POST['type_select']) && $_POST['type_select']!=""){
	
		if($_POST['type_titre']==1 && $_POST['titre']==""){
			$_SESSION['notification'][]=array(0,"Pied de page","Veuillez remplir tous les champs");
			$action='add';
		}
		elseif($_POST['type_select']=='externe-1' && $_POST['external_url']==''){
			$_SESSION['notification'][]=array(0,"Pied de page","Veuillez remplir tous les champs");
			$action='add';
		}else{	
		
			$temp_type=explode('-',$_POST['type_select']);
			$id_element=$temp_type[1];
			$type=0;
			switch($temp_type[0]){
				case "arbo" :
				$type=1;
				break;
				
				case "article" :
				$type=2;
				break;
				
				case "formulaire" :
				$type=3;
				break;
				
				case "galerie" :
				$type=4;
				break;
				
				case "video" :
				$type=5;
				break;
				
				case "fonction" :
				$type=6;
				break;
				
				case "externe" :
				$type=7;
				$id_element=$_POST['external_url'];
				break;
			}
		
			$count=$db->countOf('cms_footer','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PARENT = '.$_POST['parent']);
			
			$db->execute("INSERT INTO cms_footer (ID_PARENT,ID_ELEMENT,ID_LANG,ID_UNIVERS,TYPE,ORDRE,TEXT) VALUES (".$_POST['parent'].",'".addslashes($id_element)."',".$_SESSION['langue'].",".$_SESSION['univers'].",".$type.",".($count+1).",'".addslashes($_POST['titre'])."')");
	
		
			$_SESSION['notification'][]=array(1,"Pied de page","L'élément du menu a été créé.");
			header('location:nav_footer.php');	
			die();
		}
	}else{
		$_SESSION['notification'][]=array(0,"Pied de page","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Réordonner les éléments*/
elseif($action=='ordre_r'){	
	$req=$db->query("select * from cms_footer WHERE ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." AND ID_PARENT = ".$_POST['ID_PARENT']." order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_footer","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['nav_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Pied de page","La position de l'élément du menu a été modifiée.");
	header('location:nav_footer.php');
	die();	
}
/*Modification*/
if($action=='edit_r'){
	//pr($_POST);
	if(isset($_POST['type_titre']) && is_numeric($_POST['type_titre']) && isset($_POST['type_select']) && $_POST['type_select']!=""){
		
		if($_POST['type_titre']==1 && $_POST['titre']==""){
			$_SESSION['notification'][]=array(0,"Pied de page","Veuillez remplir le titre");
			$action='edit';
			$_GET["id"]=$_POST['id'];
		}
		elseif($_POST['type_select']=='externe-1' && $_POST['external_url']==''){
			$_SESSION['notification'][]=array(0,"Pied de page","Veuillez remplir l'adresse internet");
			$action='edit';
			$_GET["id"]=$_POST['id'];
		}else{	
			
			if($_POST['type_titre']==2)$_POST['titre']='';
			$temp_type=explode('-',$_POST['type_select']);
			$id_element=$temp_type[1];
			$type=0;
			switch($temp_type[0]){
				case "arbo" :
				$type=1;
				break;
				
				case "article" :
				$type=2;
				break;
				
				case "formulaire" :
				$type=3;
				break;
				
				case "galerie" :
				$type=4;
				break;
				
				case "video" :
				$type=5;
				break;
				
				case "fonction" :
				$type=6;
				break;
				
				case "externe" :
				$type=7;
				$id_element=$_POST['external_url'];
				break;
			}

			$req=$db->query('select * from cms_footer where ID = '.$_POST['id']);
			$info_element=mysql_fetch_array($req);
			if($info_element['ID_PARENT']!=$_POST['parent']){
				
				$count=$db->countOf('cms_footer','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PARENT = '.$_POST['parent']);
				
				$db->execute("UPDATE cms_footer SET ID_PARENT = '".$_POST['parent']."', ORDRE = ".($count+1)." WHERE ID = ".$_POST['id']);
				
				$req=$db->query('select * from cms_footer where `ORDRE` > '.$info_element['ORDRE'].' AND ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PARENT = '.$info_element['ID_PARENT']);
				if(mysql_num_rows($req)!=0){
					while($don=mysql_fetch_array($req)){
						$db->execute('update cms_footer set `ORDRE` = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
					}
				}
				
			}	
				
			$db->execute("UPDATE cms_footer SET ID_ELEMENT = '".addslashes($id_element)."', TYPE = ".$type.", TEXT = '".addslashes($_POST['titre'])."' WHERE ID = ".$_POST['id']);
			
			
			$_SESSION['notification'][]=array(1,"Pied de page","L'élément du menu a été modifié.");
			header('location:nav_footer.php');		
			die();
		}
	}
	else{
		$_SESSION['notification'][]=array(0,"Pied de page","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		
		$req=$db->query('select * from cms_footer where ID = '.$_GET['id']);
		$info_element=mysql_fetch_array($req);					
			
		$req=$db->query('select * from cms_footer where `ORDRE` > '.$info_element['ORDRE'].' AND ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PARENT = '.$info_element['ID_PARENT']);
		if(mysql_num_rows($req)!=0){
			while($don=mysql_fetch_array($req)){
				$db->execute('update cms_footer set `ORDRE` = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
			}
		}		
		
		$db->execute("DELETE FROM cms_footer WHERE ID = ".$_GET['id']);
	
		$_SESSION['notification'][]=array(1,"Pied de page","L'élément du menu a été supprimé.");
		header('location:nav_footer.php');		
		die();
	}
}

if($action==''){
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter un élément','WIDTH'=>'210'); 
	echo '
	<script>
		$(document).ready(function(){
			'.auto_help("Pied de Page","Le Pied de Page se gère comme le Menu Horizontal.<br/><a href='#' id='titre_guide'>Voir le guide &quot;Comment gérer le Menu Horizontal&quot;</a>",'titre_guide','faq_view.php?id_aide=57&id_cat=8').'
		});
	</script>
	';
}
echo genere_sous_menu_admin($array_menu);

if($action=='add' || $action=='edit'){

	$form=array();
	$element_id='';
	if($action=='edit'){
		
		$req=$db->query('select * from cms_footer where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
		
		switch($form['TYPE']){
			case 1:
			$element_id='arbo-'.$form['ID_ELEMENT'];
			break;
			
			case 2:
			$element_id='article-'.$form['ID_ELEMENT'];
			break;
			
			case 3:
			$element_id='formulaire-'.$form['ID_ELEMENT'];
			break;
			
			case 4:
			$element_id='galerie-'.$form['ID_ELEMENT'];
			break;
			
			case 5:
			$element_id='video-'.$form['ID_ELEMENT'];
			break;
			
			case 6:
			$element_id='fonction-'.$form['ID_ELEMENT'];
			break;
			
			case 7:
			$element_id='externe-1';
			break;
		}
	}

?>

	<link rel="stylesheet" href="<?echo URL_DIR;?>/css/jquery.treeview.css" />
	<script src="<?echo URL_DIR;?>/js/jquery.cookie.js" type="text/javascript"></script>
	<script src="<?echo URL_DIR;?>/js/jquery.treeview.js" type="text/javascript"></script>
	<script type="text/javascript">
	var element_select='<?echo $element_id;?>';
	$(document).ready(function() {
		if(element_select!=""){
			$('#'+element_select).css({'color':'#FF0000'});
		}
		$("#form_user").validate({meta: "validate"});
		$("#browser").treeview({animated: "fast",persist: "cookie"});
		$(".clikable").click(function(){
			if(element_select!="")$('#'+element_select).css({'color':'#444444'});
			element_select=this.id;
			$('#type_select').val(this.id);
			//$('#infoObject').html(this.id);				
		}).css(
		{'cursor':'pointer'}
		).mouseover(function(){
			$(this).css({'color':'#FF0000'});
		}).mouseout(function(){
			if(this.id != element_select)$(this).css({'color':'#444444'});			
		});
		$('#titre_menu').click(function(){
			$('#type_titre1').attr({'checked':"checked"});
		});
		$('#type_titre3').click(function(){
			$('#type_titre2').attr({'checked':"checked"});
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
			if($('input:radio[name=type_titre]:checked').val()==1 && $('#titre_menu').val()==''){
				$('#complete_all').show();
				$('#titre_menu').css({"border":"1px solid #FF0000"});
			}else{
				$('#complete_all').hide();
				$('#titre_menu').css({"border":"1px solid #999999"});
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=2;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/2');
			}
		}
	}
	function valide_form(){
		if(element_select=='' || (element_select=='externe-1' && $('#external_url').val()=='')){
			$('#complete_all2').show();
		}else{
			$('#complete_all2').hide();
			document.forms["form_nav"].submit();
		}
	}
	</script>
	
	<form method="post"  name="form_nav" action="nav_footer.php?action=<?echo $action;?>_r">
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<input type="hidden" name="type_select" id="type_select" value="<?echo $element_id;?>">
		<div id="form_admin">
			<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/2</h1></div>
			<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_2.jpg'></div>
		</div>
		<div class="clear"></div>
		<div id="step1_admin">
			<div>Titre de l'élément du menu <span id="complete_all" style="display:none;"><b style='color:#FF0000;'>- Merci de saisir un titre</b></span></div>
			<div style="margin-top:15px;">
				<input type="radio" name="type_titre" id="type_titre1" value="1" <?if($form['TEXT']!=""){ echo "checked='checked'";}?>> Je saisis un titre : <input name="titre" type="text" value='<?echo $form['TEXT'];?>'  style="width:350px;" id="titre_menu"/>
			</div>
			<div style="margin-top:15px;">
				<input type="radio" name="type_titre" id="type_titre2" value="2" <?if($form['TEXT']==""){ echo "checked='checked'";}?>><span id="type_titre3">Ou le titre de l'élément associé sera repris automatiquement (par exemple en sélectionnant une galerie photos, on reprendra le titre de la galerie photos).</span>
			</div>
			<div style="margin-top:20px;">Je souhaite placer mon élément</div>
			<div style="margin-top:10px;">
				<div style="width:400px;">
				<select name="parent" class="select_class" style="width:400px;">
					<option value='0' <?if($form['ID_PARENT']==0){ echo 'selected="selected"';}?>>en élément principal</option>
					<?
					$req=$db->query("select * from cms_footer where ID_PARENT = 0 AND ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by ORDRE ASC");
					if(mysql_num_rows($req)!=0){
						while($don=mysql_fetch_array($req)){
						
							if($form['ID_PARENT']==$don['ID'])$sel='selected="selected"';
							else $sel='';
							
							if($don['TEXT']==""){
								$title=get_title_nav($don['TYPE'],$don['ID_ELEMENT']);
							}else{
								$title=stripslashes($don['TEXT']);
							}
				
							echo '<option value="'.$don['ID'].'" '.$sel.'>. '.stripslashes($title).'</option>';
						}
					}
					?>
				</select>
				</div>
			</div>
			<div class="clear"></div>
			<div style="margin-top:35px;">				
				<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
				</div>
				<?if($action=='edit'){?>
					<div class="bordure_menu" style='width:165px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
						<div style='width:165px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
							<p>Modifier l'élément</p>
						</div>
					</div>
				<?}?>
				<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('nav_footer.php');">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
				</div>			
				
			</div>
			<div class="clear"></div>
		</div>
		<div id="step2_admin">
			<div>Je souhaite que mon élément du menu amène l'internaute vers : </div>
			<div style="margin-top:15px;">
				<ul id="browser" class="filetree">
					<li class="closed"><span class="folder">Une rubrique</span>
						<ul>
						<?
						$walk = $tree->nstWalkPreorder($tree->nstRoot());
						$prev_lvl=0;
						$i=0;
						while($curr = $tree->nstWalkNext($walk)) {
							//pr($walk);
							if($walk['row']['TYPE']==0 && $walk['row']['LFT']!=1){

													
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
								
								$view.='
								<li><span id="arbo-'.$walk['row']['ID'].'" class="clikable">'.stripslashes($walk['row']['TITLE']).'</span>';
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
					<li  class="closed"><span class="folder">Un formulaire</span>
						<ul>
						<?
						$req=$db->query("select * from cms_formulaire where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by NAME ASC");
						while($don=mysql_fetch_array($req)){
							echo '<li id="formulaire-'.$don['ID'].'" class="clikable">'.stripslashes($don['NAME']).'</li>';
						}
						?>	
						</ul>
					</li>
					<li class="closed" ><span class="folder">Une galerie photos</span>
						<ul>
						<?
						$req=$db->query("select * from cms_galerie where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by TITLE ASC");
						while($don=mysql_fetch_array($req)){
							echo '<li id="galerie-'.$don['ID'].'" class="clikable">'.stripslashes($don['TITLE']).'</li>';
						}
						?>	
						</ul>
					</li>
					<li class="closed" ><span class="folder">Une vidéo</span>
						<ul>
						<?
						$req=$db->query("select * from cms_video where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by TITLE ASC");
						while($don=mysql_fetch_array($req)){
							echo '<li id="video-'.$don['ID'].'" class="clikable">'.stripslashes($don['TITLE']).'</li>';
						}
						?>	
						</ul>
					</li>
					<li class="closed"><span class="folder">Une fonctionnalité</span>
						<ul>
							<li id="fonction-1" class="clikable">Page d'accueil du site internet</li>
							<li id="fonction-2" class="clikable">Accès aux galeries photos</li>
							<li id="fonction-3" class="clikable">Accès à la Web TV</li>
						</ul>
					</li>
					<li id="externe-1" class="clikable closed"><span class="folder">Une adresse Internet externe (http://) : <input type="text" name="external_url" id="external_url" value="<? if($form['TYPE']==7){ echo $form['ID_ELEMENT']; }?>"></span>					
					</li>
				</ul>
			</div>
			<div class="clear"></div>
			<div style="margin-top:40px;">	
				<div id="complete_all2" style="display:none;margin-bottom:5px;"><b style='color:#FF0000;'>Merci de choisir une destination pour l'élément du menu</b></div>
				<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(1);">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
				</div>
				<div class="bordure_menu" style='width:245px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
					<div style='width:245px;background-image:url("<?echo URL_DIR;?>/images/btn_<?if($action=='add'){echo 'add';}else{ echo 'edit';}?>.png");' class="btn_style">
						<p><?if($action=='add'){echo 'Ajouter l\'élément au pied de page';}else{ echo 'Modifier l\'élément du pied de page';}?></p>
					</div>
				</div>								
				<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('nav_footer.php');">
					<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
				</div>			
				
			</div>
			<div class="clear"></div>
			
		</div>
	
	</form>
<?	
}
else{
?>
<script>
$(document).ready(function(){
	$(".iframe").fancybox({
		'width'				: 300,
		'height'			: 300,
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe'
	});
});
</script>
<?
	$req=$db->query("select * from cms_footer where ID_PARENT=0 AND ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by ORDRE ASC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<b>Liste de vos éléments en Pied de Page</b>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view' >
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th width='80'>Ordre</th>
				<th width='100'>Modifier</th>
				<th width='120'>Supprimer</th>
			</tr>
			</thead>
		";		
	
		while($don=mysql_fetch_array($req)){			
			
			$count=$db->countOf('cms_footer','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PARENT = '.$don['ID_PARENT']);
			
			if($don['TEXT']==""){
				$title=get_title_nav($don['TYPE'],$don['ID_ELEMENT']);
			}else{
				$title=stripslashes($don['TEXT']);
			}		
			
			echo"
			<tr>
				<td class='frst'><b>".$title."</b></td>
				<td>".genere_form_ordre('nav_ordre','?action=ordre_r',1,$count,$don['ORDRE'],0,array('ID'=>$don['ID'],'ID_PARENT'=>$don['ID_PARENT']))."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
			</tr>
			";
			
			$req2=$db->query("select * from cms_footer where ID_PARENT=".$don['ID']." AND ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by ORDRE ASC");
			if(mysql_num_rows($req2)!=0){
				
				while($don2=mysql_fetch_array($req2)){
				
					$count=$db->countOf('cms_footer','ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PARENT = '.$don2['ID_PARENT']);
			
					if($don2['TEXT']==""){
						$title=get_title_nav($don2['TYPE'],$don2['ID_ELEMENT']);
					}else{
						$title=stripslashes($don2['TEXT']);
					}		
					
					echo"
					<tr class='odd'>
						<td class='frst' style='padding-left:40px;'>".$title."</td>
						<td>".genere_form_ordre('nav_ordre','?action=ordre_r',1,$count,$don2['ORDRE'],0,array('ID'=>$don2['ID'],'ID_PARENT'=>$don2['ID_PARENT']))."</td>
						<td><a href='?action=edit&id=".$don2['ID']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
						<td><a href='?action=delete&id=".$don2['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
					</tr>
					";
				}
			}
		}
		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucun élément actuellement";
	}
}
?>

<?
include('footer.php');
?>