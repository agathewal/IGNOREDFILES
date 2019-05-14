<?
$id_module=18;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';


if($action=='add'){
	$ariane_element2['URL']="homepage.php";
	$ariane_element2['LIBELLE']="Ajouter une page d'accueil";					
}elseif($action=='edit'){
	$ariane_element2['URL']="homepage.php";
	$ariane_element2['LIBELLE']="Modifier une page d'accueil";
}elseif($action=='gere'){
	$ariane_element2['URL']="homepage.php";
	$ariane_element2['LIBELLE']="Modifier le contenu de la page d'accueil";
}

include('header.php');



/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['home_name']) && $_POST['home_name']!=""){	
			
		$db->execute("INSERT INTO cms_homepage (ID_TEMPLATE,NAME,SELECTED,DT_CREATE,DT_MODIF,ID_LANG,ID_UNIVERS) VALUES (".$_POST['type_home'].",'".addslashes($_POST['home_name'])."',0,".time().",".time().",".$_SESSION['langue'].",".$_SESSION['univers'].")");
		$_SESSION['notification'][]=array(1,"Page d'accueil","La page d'accueil a été créée.");
		header('location:homepage.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Page d'accueil","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['home_name']) && $_POST['home_name']!=""){
		
		$db->execute("UPDATE cms_homepage SET `NAME` = '".addslashes($_POST['home_name'])."',`ID_TEMPLATE` = ".$_POST['type_home']." , `DT_MODIF` = ".time()." WHERE ID = ".$_POST['id']);		
		$_SESSION['notification'][]=array(1,"Page d'accueil","La page d'accueil a été modifiée.");
		header('location:homepage.php');	
		die();		
	}
	else{
		$_SESSION['notification'][]=array(0,"Page d'accueil","Veuillez remplir tous les champs");
		$action='add';
	}
}

/*Suppression*/
if($action=='delete' && isset($_GET['id']) && is_numeric($_GET['id'])){

	$req=$db->query('select * from cms_homepage where ID = '.$_GET["id"]);
	if(mysql_num_rows($req)!=0){
		$form=mysql_fetch_array($req);
		if($form['SELECTED']==0){
			$db->execute("DELETE FROM cms_homepage WHERE ID = ".$_GET['id']);		
			$db->execute("DELETE FROM cms_template_data_".$form['ID_TEMPLATE']." WHERE ID_PARENT = ".$_GET['id']);			
			$_SESSION['notification'][]=array(1,"Page d'accueil","La page d'accueil a été supprimée.");	
		}
		else{
			$_SESSION['notification'][]=array(0,"Page d'accueil","La page d'accueil est actuellement utilisée. Veuillez sélectionner une autre page");	
		}		
	}
	else{
		$_SESSION['notification'][]=array(1,"Page d'accueil","La page d'accueil a été supprimée.");
	}
	header('location:homepage.php');	
	die();		
}

if($action=='dupli' && isset($_GET['id']) && is_numeric($_GET['id'])){
	$req=$db->query('select * from cms_homepage where ID = '.$_GET["id"]);
	if(mysql_num_rows($req)!=0){
		$form=mysql_fetch_array($req);		
		$form=array_map('stripslashes',$form);
		$form=array_map('addslashes',$form);
		$db->execute("INSERT INTO `cms_homepage` (`ID`, `ID_LANG`, `ID_UNIVERS`, `ID_PUSH`, `NAME`, `ID_TEMPLATE`, `SELECTED`, `DT_CREATE`, `DT_MODIF`) VALUES
('', ".$form['ID_LANG'].", ".$form['ID_UNIVERS'].", ".$form['ID_PUSH'].", '".$form['NAME']." (copie)', ".$form['ID_TEMPLATE'].", 0, ".$form['DT_CREATE'].", ".$form['DT_MODIF'].")");
		$id_homepage=$db->lastInsertedId();
		//echo $id_homepage.'<br>';
		$donnees_dupli=mysql_data_template('cms_template_data_'.$form['ID_TEMPLATE'],' WHERE ID_PARENT = '.$_GET["id"]);
		$donnees_dupli=str_replace('ID_PARENT_HOME',$id_homepage,$donnees_dupli);
		for($i=0;$i<count($donnees_dupli);$i++){
			//echo $donnees_dupli[$i].'<br>';
			$db->execute($donnees_dupli[$i]);
		}
		//pr($donnees_dupli);
		$_SESSION['notification'][]=array(1,"Page d'accueil","La page d'accueil a été dupliquée.");
		header('location:homepage.php');	
		die();	
	}
}

/*Sélection*/
if($action=='select' && isset($_GET['id']) && is_numeric($_GET['id'])){

	$db->execute("UPDATE cms_homepage SET SELECTED = 0 WHERE `ID_LANG` = '".$_SESSION['langue']."' AND `ID_UNIVERS` = ".$_SESSION['univers']);	
	$db->execute("UPDATE cms_homepage SET SELECTED = 1 WHERE `ID` = ".$_GET['id']);
	$_SESSION['notification'][]=array(1,"Page d'accueil","La page d'accueil sélectionnée a été modifiée.");	
	header('location:homepage.php');	
	die();		
}
?>
<!--[if lte IE 7]>
<script type="text/javascript">
$(document).ready(function(){ 
	$('#content_block').css({'padding-bottom':'27px'});
});
</script>
<![endif]-->
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript">
$(function(){
	$("#entete_admin_home,.info_iframe").corner("5px").parent().css('padding', '1px').corner("5px");	
	$("#iframe_zone").hide();
});
function retour_zone_selec(){
	$("#iframe_zone").hide();
	$("#zone_choix").show();	
}
</script>
<? 
if($action=='gere')$array_menu[]=array('URL'=>'homepage.php?id='.$_GET['id'],'IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour à la gestion des pages d\'accueil','WIDTH'=>300);
else if($action==''){
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une page d\'accueil','WIDTH'=>'200');
}
echo genere_sous_menu_admin($array_menu);?>
<?
if($action=='gere' && isset($_GET['id']) && is_numeric($_GET['id'])){
?>
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/tooltip-generic.css" type="text/css" media="screen" charset="utf-8" />
	<div style="font-size:14px;margin-top:20px;">Administration de la page d'accueil</div>
	<div class="clear"></div>
	<div id="zone_choix">
		<div class="bordure_menu" style="margin-top:20px;margin-bottom:20px;width:658px;">
			<div id="entete_admin_home">
				<p>Cliquer ci-dessous sur la zone à modifier.</p>
			</div>
		</div>
<?	

	echo'
	<div align="center" id="zone_gere_accueil">';
	
	$req=$db->query('select * from cms_homepage where ID = '.$_GET["id"]);
	$form=mysql_fetch_array($req);
	$req=$db->query('select * from cms_template_block where ID_TEMPLATE = '.$form['ID_TEMPLATE'].' order by `ORDRE`');
	$url_list=array();
	while($don=mysql_fetch_array($req)){
		$url_list[]='block_view.php?id='.$don['ID'].'&idp='.$_GET['id'];
		$titre_list[]=$don['NAME'];
	}
	//pr($url_list);
	
	$smarty = new Smarty();					
	$smarty->template_dir= SMARTY_TPL_DIR;
	$smarty->compile_dir= SMARTY_COMPILE_DIR;
	$smarty->config_dir= SMARTY_CONFIG_DIR;
	$smarty->cache_dir= SMARTY_CACHE_DIR;					
	$smarty->caching = SMARTY_CACHE;					
	$smarty->assign('URL_IMAGE',URL_DIR.'/tpl/admin_template/images/');
	$smarty->assign('url_list',$url_list);
	$smarty->assign('titre_list',$titre_list);
	$smarty->display(ROOT_DIR.'tpl/admin_template/'.$form['ID_TEMPLATE'].'.tpl');
	echo '</div></div>
	<div id="iframe_zone">
	<div class="bordure_menu" style="margin-top:20px;margin-bottom:20px;width:300px;" onclick="javascript:retour_zone_selec();">
		<div class="btn_style" style="background-image:url(\''.URL_DIR.'/images/btn_prev.png\');">
			<p>Retour à la sélection de la zone à modifier</p>
		</div>
	</div>
	</div>
	';
	
}
elseif($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from cms_homepage where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}
	
?>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name">Ajouter une page d'accueil</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>
	<form method="post" name="form_modele" action="homepage.php?action=<?echo $action;?>_r">
	<script>
	function select_home(id_homepage){
		$('#type_home').val(id_homepage);
		$('#zone_choix div').css({'background-color':'#FFFFFF'});
		$('#zone_home_'+id_homepage).css({'background-color':'#e1dede'});
	}
	function valide_form(){
		
		if($('#home_name').val()==''){
			$('#complete_all').show();
			$('#libelle').css({"border":"1px solid #CC0099"});
		}else{
			$('#complete_all').hide();
			$('#libelle').css({"border":"1px solid #999999"});
			document.forms['form_modele'].submit();
		}
		
	}
	</script>
	<div id="step1_admin" >
		<div style="margin-top:10px;">Nom de la page d'accueil : <span id="complete_all" style="display:none;"><b style='color:#CC0099;'>- Merci de saisir un nom</b></span></div>
		<div style="margin-top:5px;">
			<input name="home_name" type="text" value='<?echo $form['NAME'];?>' style="width:320px;" id="home_name"  />
		</div>
		<div style="margin-top:10px;">
		Type de page d'accueil :
		</div>
		<div style="margin-top:5px;" id="zone_choix">
		<input type="hidden" name="type_home" id="type_home" value=''>
		<?
		$req=$db->query('select * from cms_template where TYPE = 1 ORDER BY `ORDRE` ASC');
		$i=0;
		while($don=mysql_fetch_array($req)){
			
			if($i!=0)$plus='margin-left:25px;';
			else $plus='';
			
			echo'
			
				<div id="zone_home_'.$don['ID'].'" style="padding:25px;float:left;cursor:pointer;width:100px;height:100px;border:1px solid #CCCCCC;'.$plus.'" onclick="javascript:select_home(\''.$don['ID'].'\');"><img src="'.URL_DIR.'/admin/get-thumb.php?url='.urlencode($don['THUMB']).'&width=100&height=100" /></div>

			';
			$i++;
		}
		
		?>		
		</div>
		<div class="clear"></div>

		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:155px;margin-top:15px;float:left;' onclick="javascript:valide_form();">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider</p></div>
			</div>
			<div class="bordure_menu" style='width:155px;margin-left:30px;margin-top:15px;float:left;' onclick="javascript:redir('homepage.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>
		
		</div>
		
	</form>
<?	
}
else{

	$req=$db->query("select * from cms_homepage  where ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." AND ID_PUSH = 0 order by NAME ASC");
	

	$nb_menu=mysql_num_rows($req);
	if($nb_menu!=0){
		
?>
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/tooltip-homepage.css" type="text/css" media="screen" charset="utf-8" />

<script type="text/javascript">
$(document).ready(function() {
	$(".aide-legende").corner("5px").parent().css('padding', '1px').corner("5px");
	$(".tooltip-info").tooltip({offset: [-50, -80]});
	<? echo auto_help("Page d'accueil","<a href='#' id='titre_guide'>Voir le guide &quot;Page d'accueil&quot;</a>",'titre_guide','faq_view.php?id_aide=60&id_cat=8');?>
});
</script>
<div class='clear'></div>
<div style='height:15px;width:100%;'>&nbsp;</div>
<div class="bordure_menu" style='width:657px;'>
	<div class="aide-legende">
		Pour gérer une page d'accueil, <b>cliquez sur la miniature</b> correspondante au <b>gabarit</b> choisi.<br/> La <b>page d'accueil actuellement utilisée</b> est signalée par cette étoile <img src='<? echo URL_DIR;?>/images/star_on.png' align="absmiddle">. Pour sélectionner une autre page d'accueil, cliquez sur <b>l'étoile correspondante <img src='<? echo URL_DIR;?>/images/star_off.png' align="absmiddle"></b>.
	</div>
</div>
<div class='clear'></div>
<div style='height:15px;width:100%;'>&nbsp;</div>

<?		
		
	
	
		echo "
		
		<table cellpadding='0' cellspacing='0' border='0' width='660'  id='table_view'>
			<thead>
				<tr>
					<th class='frst'>Nom</th>
					<th width='85'>Page active</th>				
					<th width='85'>Voir</th>
					<th width='85'>Modifier</th>				
					<th width='85'>Dupliquer</th>			
					<th width='85'>Supprimer</th>
				</tr>
			</thead>
		";
		$i=0;
		while($don=mysql_fetch_array($req)){			
			
			$don=array_map('stripslashes',$don);
			
			if($don['SELECTED']==1)$selec="<img src='".URL_DIR."/images/star_on.png'>";
			else $selec="<a href='?action=select&id=".$don['ID']."'><img src='".URL_DIR."/images/star_off.png'></a>";
			
			$info_tpl=$db->query('select * from cms_template where ID = '.$don['ID_TEMPLATE']);
			$don_tpl=mysql_fetch_array($info_tpl);
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			echo "
			<tr ".$style_table.">
				<td class='frst'>".$don['NAME']."</td>
					
				<td>".$selec."</td>
				<td><a href='".URL_DIR."/?id_preview=".$don['ID']."' target='_blank'><img src='".URL_DIR."/images/btn_see.png'></a></td>
				<td><a href='?action=gere&id=".$don['ID']."' title=\"<img src='".URL_DIR."/admin/get-thumb.php?url=".urlencode($don_tpl['THUMB'])."&width=100&height=100' />\" class='tooltip-info'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=dupli&id=".$don['ID']."'><img src='".URL_DIR."/images/duplicate.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
			</tr>
			";
			
		}
		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucune page d'accueil";
	}
}

include('footer.php');
?>