<?
$id_module=17;

$ariane_element2['URL']="modele_bloc.php?id=".$_GET['id'];
$ariane_element2['LIBELLE']="Blocs du module";		
	
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element3['URL']="modele_bloc.php?id=".$_GET['id'];
	$ariane_element3['LIBELLE']="Ajouter un bloc";					
}elseif($action=='edit'){
	$ariane_element3['URL']="modele_bloc.php?id=".$_GET['id'];
	$ariane_element3['LIBELLE']="Modifier un bloc";
}



include('header.php');


if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$req=$db->query('select * from cms_template WHERE ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		$info_req=mysql_fetch_array($req);
	}else{
		header('location:modele.php');
		die();
	}
}else{
	header('location:modele.php');
	die();
}


/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['block_name']) && $_POST['block_name']!=""){
	
		if(!isset($_POST['min_element']) || !is_numeric($_POST['min_element']))$_POST['min_element']=0;
		if(!isset($_POST['max_element']) || !is_numeric($_POST['max_element']))$_POST['max_element']=0;
		
		$nb_menu=$db->countOf("cms_template_block",'ID_TEMPLATE = '.$_GET['id']);				
		$db->execute("INSERT INTO cms_template_block (ID_TEMPLATE,NAME,ORDRE,NB_MIN_ELEMENT,NB_MAX_ELEMENT,VIGNETTE) VALUES (".$_GET['id'].",'".addslashes($_POST['block_name'])."',".($nb_menu+1).",".$_POST['min_element'].",".$_POST['max_element'].",'".addslashes($_POST['illus'])."')");
		$_SESSION['notification'][]=array(1,"Modèles du CMS","Le bloc a été ajouté au modèle.");
		header('location:modele_bloc.php?id='.$_GET['id']);		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Modèles du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['block_name']) && $_POST['block_name']!=""){
	
		if(!isset($_POST['min_element']))$_POST['min_element']=0;
		if(!isset($_POST['max_element']))$_POST['max_element']=0;
		
		$db->execute("UPDATE cms_template_block SET `NAME` = '".addslashes($_POST['block_name'])."',`NB_MIN_ELEMENT` = ".$_POST['min_element']." , `NB_MAX_ELEMENT` = ".$_POST['max_element'].", VIGNETTE = '".addslashes($_POST['illus'])."' WHERE ID = ".$_POST['id']);		
		$_SESSION['notification'][]=array(1,"Modèles du CMS","Le bloc a été modifié.");
		header('location:modele_bloc.php?id='.$_GET['id']);	
		die();		
	}
	else{
		$_SESSION['notification'][]=array(0,"Modèles du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Ordre*/
if($action=='ordre'){	
	//pr($_POST);

	$req=$db->query("select * from cms_template_block where ID_TEMPLATE = ".$_GET['id']." order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_template_block","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['menu_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Modèles du CMS","La position du bloc a été modifiée.");
	header('location:modele_bloc.php?id='.$_GET['id']);	
	die();	
}
/*Suppression*/
if($action=='delete' && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['id_block']) && is_numeric($_GET['id_block'])){
	
	$req=$db->query('select * from cms_template_block where ID = '.$_GET["id_block"]);
	$info_req=mysql_fetch_array($req);
	
	$req=$db->query('select * from cms_template_block where ID_TEMPLATE = '.$_GET['id'].' AND ORDRE > '.$info_req['ORDRE']);
	while($don=mysql_fetch_array($req)){
		$db->execute("UPDATE cms_template_block SET ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
	}
	
	$db->execute("DELETE FROM cms_template_element WHERE ID_BLOC = ".$_GET['id_block']);		
	$db->execute("DELETE FROM cms_template_block WHERE ID = ".$_GET['id_block']);	
	
	$_SESSION['notification'][]=array(1,"Modèles du CMS","Le modèle a été supprimé.");
	header('location:modele_bloc.php?id='.$_GET['id']);	
	die();		
	
}

if($action=="genere" && isset($_GET['id']) && is_numeric($_GET['id'])){


	$form_table="cms_template_data_".$_GET['id'];
	
	/*On vérifie que la table existe*/
	$req=$db->query('SHOW TABLES FROM '.DATABASE_NAME.' LIKE "cms_template_data_'.$_GET['id'].'"');
	if(mysql_num_rows($req)==0){//si n'existe pas on la crée bien sur
		$table_donnees="
		CREATE TABLE `".$form_table."` (
		`ID` int(10) NOT NULL AUTO_INCREMENT,
		`DT_CREATE` int(15) NOT NULL,
		`DT_MODIF` int(15) NOT NULL,
		`ID_LANG` int(5) NOT NULL,
		`ID_UNIVERS` int(5) NOT NULL ,
		`ID_BLOC` int(5) NOT NULL,
		`ORDRE` int(10) NOT NULL,
		`ID_PARENT` int(15) NOT NULL,		
		PRIMARY KEY (`ID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
		";
		$db->execute($table_donnees);
	}
	
	$list_field=array();
	
	$result = $db->query("SHOW COLUMNS FROM ".$form_table);		
	while ($don = mysql_fetch_array($result)) {
		//$list_field[]=$don['Field'];
		if(preg_match("(FIELD_)",$don['Field'])){
			$list=str_replace('FIELD_','',$don['Field']);
			$list_field[]=$list;
		} 
	}
	
	//pr($list_field);
	
	$req_bloc=$db->query('select * from cms_template_block where ID_TEMPLATE = '.$_GET['id']);
	if(mysql_num_rows($req_bloc)!=0){
		while($don=mysql_fetch_array($req_bloc)){
			$req=$db->query('select * from cms_template_element where ID_BLOC = '.$don['ID'].' order by `ORDRE`');
			if(mysql_num_rows($req)!=0){
				while($don2=mysql_fetch_array($req)){
					if(in_array($don2['ID'],$list_field)){/*Si déjà présent*/		
						removeFromArray($list_field,$don2['ID']);
						//echo $don2['ID'];
					}else{
						$table_donnees='ALTER TABLE '.$form_table.' ADD `FIELD_'.$don2['ID'].'` TEXT NOT NULL';
						//echo $table_donnees."<br>";
						$db->execute($table_donnees);
					}
				}
			}	
		}
	}
	
	//pr($list_field);
	$nb_elements=count($list_field);
	if($nb_elements>0){
		for($i=0;$i<$nb_elements;$i++){
			$table_donnees='ALTER TABLE '.$form_table.' DROP COLUMN `FIELD_'.$list_field[$i].'`';
			//echo $table_donnees."<br>";
			$db->execute($table_donnees);			
		}
	}
	
	
	$_SESSION['notification'][]=array(1,"Modèles du CMS","Le modèle a été généré.");
	header('location:modele_bloc.php?id='.$_GET['id']);	
	die();
	
}

if($action=='add' || $action=='edit' || $action=="crop")$array_menu[]=array('URL'=>'modele_bloc.php?id='.$_GET['id'],'IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
else {
	$array_menu[]=array('URL'=>'?action=add&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/add.png','LIBELLE'=>'Ajouter un bloc');
	$array_menu[]=array('URL'=>'?action=genere&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/genere-template.png','LIBELLE'=>'Générer le modèle');
}
echo genere_sous_menu_admin($array_menu);?>
<br/><br/>
<?
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from cms_template_block where ID = '.$_GET["id_block"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}
	
?>
	<div id="form">
		<h1>
		<?
			if($action=='add'){echo 'Ajouter un bloc';}
			else{ echo 'Modifier un bloc';}
			?>
		</h1>
	</div>
	<form method="post" id="form_user" action="modele_bloc.php?action=<?echo $action;?>_r&id=<?echo $_GET['id'];?>">
		<script type='text/javascript'>
		$(document).ready(function() {
			$("#form_user").validate({meta: "validate"});
		});
		</script>
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div class="input text required">
			<label for="block_name">Nom du bloc</label>
			<input name="block_name" type="text" value='<?echo $form['NAME'];?>' style="width:50%;" id="block_name"  class="{validate:{required:true, messages:{required:'Veuillez saisir un nom pour le bloc'}}}" />
		</div>
		<div class="input text required">
			<label for="min_element">Minimum d'éléments du bloc (si vide pas de minimum)</label>
			<input name="min_element" type="text" value='<?echo $form['NB_MIN_ELEMENT'];?>' style="width:80px;" id="min_element" />
		</div>
		<div class="input text required">
			<label for="max_element">Maximum d'éléments du bloc (si vide pas de maximum)</label>
			<input name="max_element" type="text" value='<?echo $form['NB_MAX_ELEMENT'];?>' style="width:80px;"  id="max_element" />
		</div>
		<div class="input text" style='width:660px;'>
			<label for="illus"><b>Illustration d'exemple :</b></label>
			<div style='width:100%;'>
			<?
			$dir_upload2=ADD_DIR.'/tpl/admin_template/images/';
			echo form_picture(280,500,'illus',$form['VIGNETTE'],$dir_upload2);
			?>			
			</div>
		</div>
		<div class="submit"><input type="submit" value="
		<?
		if($action=='add'){echo 'Ajouter le bloc';}
		else{ echo 'Modifier le bloc';}
		?>" />
		</div>
	</form>
<?	
}
elseif($action=="crop" && isset($_GET['id']) && is_numeric($_GET['id'])){

$req=$db->query('select * from cms_template_block where ID = '.$_GET["id_block"]);
$form=mysql_fetch_array($req);
$html_element='<ol>';
$element=$db->query('select * from cms_template_element where ID_BLOC = '.$_GET["id_block"].' ORDER BY `ORDRE`');
if(mysql_num_rows($element)!=0){
	$i=1;
	while($list_element=mysql_fetch_array($element)){
		$html_element.='<li>'.$i.' : '.stripslashes($list_element['NAME']).'</li>';
		$i++;
	}
}
$html_element.='</ol>';
?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jnotes/jquery-notes_js/jquery-notes_1.0.8.js"></script>
<link rel="stylesheet" title="Standard" href="<?echo URL_DIR;?>/js/jnotes/jquery-notes_css/style.css" type="text/css" media="all" />
<script type="text/javascript">
$(document).ready(function(){
	//alert('la');
	$('.jquery-note_1-1').jQueryNotes({
		operator: '<?echo URL_DIR;?>/js/jnotes/jquery-notes_php/notes.php',
		maxNotes: null,
		minWidth:15,
		minHeight:15,
		allowAuthor:false,
		allowLink:false,
		allowHide: true,
		allowReload: true,
		allowHide: true,
		allowAdd: true,
		hideNotes: false,
    });

});
function note_focus(foc_zone){
	$('.notes .note').css({
		visibility: 'hidden'
	});
	$('#n_1-'+foc_zone).css({'visibility':'visible'});
}
</script>
<div style="width:450px;float:left;">
	<img src="<?echo $form['VIGNETTE'];?>" alt="" class="jquery-note_1-1" />
</div>
<div style="width:250px;float:left;text-align:left;">
<? echo $html_element;
?>
</div>
<?
}
else{

	$req=$db->query("select * from cms_template_block  where id_template = ".$_GET['id']." order by ordre ASC");
	

	$nb_menu=mysql_num_rows($req);
	if($nb_menu!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
				<tr>
					<th>Nom</th>
					<th>Min.</th>
					<th>Max.</th>
					<th>Gérer les éléments<br/><i style='font-size:11px;'>Cliquez sur la ligne correspondante pour gérer les blocs</i></th>				
					<th>Ordre</th>
					<th>Découper</th>
					<th>Voir</th>
					<th>Modifier</th>				
					<th>Supprimer</th>
				</tr>
			</thead>
		";
		$i=0;
		while($don=mysql_fetch_array($req)){
		
			$nb_module=$db->countOf("cms_template_element","ID_BLOC = ".$don['ID']);

			if($don['NB_MIN_ELEMENT']==0)$don['NB_MIN_ELEMENT']='/';
			if($don['NB_MAX_ELEMENT']==0)$don['NB_MAX_ELEMENT']='/';
			
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			echo"
			<tr ".$style_table.">
				<td style='text-align:left;'>".$don['NAME']."</td>
				<td>".$don['NB_MIN_ELEMENT']."</td>
				<td>".$don['NB_MAX_ELEMENT']."</td>
				<td><a href='modele_bloc_element.php?id=".$don['ID']."'><img src='".URL_DIR."/images/menu-edit.png'> (".gere_cas($nb_module,array('0'=>'Aucun élément','1'=>'Un élément','2'=>' éléments')).")</a></td>				
				<td>".genere_form_ordre('menu_ordre','?action=ordre&id='.$_GET['id'],1,$nb_menu,$don['ORDRE'],0,array('ID'=>$don['ID']))."</td>
				<td><a href='?action=crop&id=".$_GET['id']."&id_block=".$don['ID']."'><img src='".URL_DIR."/images/crop.png'></a></td>
				<td><a href='block_view.php?id=".$don['ID']."' ><img src='".URL_DIR."/images/magnify.png'></a></td>
				<td><a href='?action=edit&id=".$_GET['id']."&id_block=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$_GET['id']."&id_block=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
			
		}
		
		echo "</table>";
	}else{
		echo "Aucun bloc";
	}
}

include('footer.php');
?>