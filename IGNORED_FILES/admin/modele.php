<?
$id_module=17;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="modele.php";
	$ariane_element2['LIBELLE']="Ajouter un modèle";					
}elseif($action=='edit'){
	$ariane_element2['URL']="modele.php";
	$ariane_element2['LIBELLE']="Modifier un modèle";
}


include('header.php');


/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['modele_name']) && $_POST['modele_name']!="" && is_numeric($_POST['type_modele'])){
		$nb_menu=$db->countOf("cms_template",'TYPE = '.$_POST['type_modele']);				
		$db->execute("INSERT INTO cms_template (TYPE,NAME,ORDRE,DT_CREATE,DT_MODIF,DESCRIPTION,THUMB,`FULL`) VALUES (".$_POST['type_modele'].",'".addslashes($_POST['modele_name'])."',".($nb_menu+1).",".time().",".time().",'".addslashes($_POST['modele_desc'])."','".addslashes($_POST['modele_img'])."','".addslashes($_POST['full_img'])."')");
		$_SESSION['notification'][]=array(1,"Modèles du CMS","Le modèle a été ajouté au cms.");
		header('location:modele.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Modèles du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['modele_name']) && $_POST['modele_name']!="" && is_numeric($_POST['type_modele'])){
	
		$req=$db->query('select * from cms_template where ID = '.$_POST["id"]);
		$form=mysql_fetch_array($req);
		$db->execute("UPDATE cms_template SET `NAME` = '".addslashes($_POST['modele_name'])."',`TYPE` = ".$_POST['type_modele']." ,`THUMB` = '".addslashes($_POST['modele_img'])."',`FULL` = '".addslashes($_POST['full_img'])."',`DESCRIPTION` = '".addslashes($_POST['modele_desc'])."', `DT_MODIF` = ".time()." WHERE ID = ".$_POST['id']);
		if($form['TYPE']!=$_POST['type_modele']){
			$nb_menu=$db->countOf("cms_template",'TYPE = '.$_POST['type_modele']);	
			$db->execute("UPDATE cms_template SET `ORDRE` = ".($nb_menu+1)." WHERE ID = ".$_POST['id']);
			$req=$db->query('select * from cms_template where TYPE = '.$_POST['type_modele'].' AND ORDRE > '.$form['ORDRE']);
			if(mysql_num_rows($req)!=0){
				while($don=mysql_fetch_array($req)){
					$db->execute('UPDATE cms_template SET ORDRE = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
				}
			}
		}
		
		$_SESSION['notification'][]=array(1,"Modèles du CMS","Le modèle a été modifié.");
		header('location:modele.php');	
		die();		
	}
	else{
		$_SESSION['notification'][]=array(0,"Modèles du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Ordre*/
if($action=='ordre'){	
	$req=$db->query('select * from cms_template where ID = '.$_POST["ID"]);
	if(mysql_num_rows($req)!=0){
		$form=mysql_fetch_array($req);
		$req=$db->query("select * from cms_template WHERE TYPE = ".$form['TYPE']." order by ordre ASC");
		while($don=mysql_fetch_array($req)){
			change_ordre("cms_template","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['menu_ordre'],$don['ID'],$_POST['ID']);
		}
		$_SESSION['notification'][]=array(1,"Modèles du CMS","La position du modèle a été modifiée.");
		header('location:modele.php');
		die();	
	}else{
		$_SESSION['notification'][]=array(0,"Modèles du CMS","Modèle non disponible");
		header('location:modele.php');
		die();
	}
}
/*Suppression*/
if($action=='delete' && isset($_GET['id']) && is_numeric($_GET['id'])){
	
	$req=$db->query('select * from cms_template where ID = '.$_GET["id"]);
	$info_req=mysql_fetch_array($req);
	
	$req=$db->query("select * from cms_template where ORDRE > ".$info_req['ORDRE']." AND TYPE = ".$info_req['TYPE']);
	if(mysql_num_rows($req)!=0){
		while($don=mysql_fetch_array($req)){
			$db->execute("update cms_template set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
		}
	}
	$req=$db->query('SELECT * FROM cms_template_block WHERE ID_TEMPLATE = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		while($don=mysql_fetch_array($req)){
			$db->execute("DELETE FROM cms_template_element WHERE ID_BLOC = ".$_GET['id']);
		}
	}
	$db->execute("DELETE FROM cms_template_block WHERE ID_TEMPLATE = ".$_GET['id']);
	$db->execute("DELETE FROM cms_template WHERE ID = ".$_GET['id']);
	$db->execute("DROP TABLE IF EXISTS cms_template_".$_GET['id']);
	
	$_SESSION['notification'][]=array(1,"Modèles du CMS","Le modèle a été supprimé.");
	header('location:modele.php');
	die();		
	
}

if($action=='add' || $action=='edit')$array_menu[]=array('URL'=>'modele.php','IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
else $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/add.png','LIBELLE'=>'Ajouter un modèle');
echo genere_sous_menu_admin($array_menu);?>
<br/><br/>
<?
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from cms_template where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);		
	}
	
?>
	<div id="form">
		<h1>
		<?
			if($action=='add'){echo 'Ajouter un modèle';}
			else{ echo 'Modifier un modèle';}
			?>
		</h1>
	</div>
	<form method="post" id="form_user" action="modele.php?action=<?echo $action;?>_r">
		<script type='text/javascript'>
		$(document).ready(function() {
			$("#form_user").validate({meta: "validate"});
		});
		</script>
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div class="input text required">
			<label for="modele_name">Nom du modèle</label>
			<input name="modele_name" type="text" value='<?echo $form['NAME'];?>'  id="modele_name"  class="{validate:{required:true, messages:{required:'Veuillez saisir un nom pour le modèle'}}}" />
		</div>
		<div class="input text required">
			<label for="modele_desc">Description du modèle</label>
			<textarea name="modele_desc" style="width:80%;height:100px;"><?echo $form['DESCRIPTION'];?></textarea>
		</div>
		<div class="input text required">
			<label for="modele_img">Miniature du modèle</label>
			<div style='width:100%;'>
			<?
			$dir_upload2=ADD_DIR.'/images/thumb/';
			echo form_picture(130,130,'modele_img',$form['THUMB'],$dir_upload2);
			?>			
			</div>
		</div>
		<div class="input text required">
			<label for="full_img">Capture d'écran du modèle</label>
			<div style='width:100%;'>
			<?
			$dir_upload2=ADD_DIR.'/images/thumb/';
			echo form_picture(950,700,'full_img',$form['FULL'],$dir_upload2);
			?>			
			</div>
		</div>
		<div class="input text required">
			<label for="type_modele">Type de modèle</label>
			<select name="type_modele">
				<?
				$req=$db->query('select * from cms_template_type order by NAME ASC');
				while($don=mysql_fetch_array($req)){	
					$sel='';
					if($form['TYPE']==$don['ID'])$sel="selected='selected'";
					echo '<option value="'.$don['ID'].'" '.$sel.'>'.$don['NAME'].'</option>';
				}
				?>
			</select>
		</div>
		<div class="submit"><input type="submit" value="
		<?
		if($action=='add'){echo 'Ajouter le module';}
		else{ echo 'Modifier le module';}
		?>" />
		</div>
	</form>
<?	
}
else{

	$req=$db->query("select cms_template.NAME as NAME, cms_template_type.NAME as CAT_NAME, cms_template.ID AS ID, cms_template.TYPE, cms_template.ORDRE from cms_template, cms_template_type where cms_template.TYPE = cms_template_type.ID  order by cms_template_type.NAME,cms_template.ordre ASC");
	

	$nb_menu=mysql_num_rows($req);
	if($nb_menu!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th>Nom</th>
				<th>Modifier</th>
				<th>Ordre</th>
				<th>Gérer le modèle<br/><i style='font-size:11px;'>Cliquez sur la ligne correspondante pour gérer les blocs</i></th>
				<th>Supprimer</th>
			</tr>
			</thead>
		";
		
		
		$cat_en_cours=0;
		while($don=mysql_fetch_array($req)){
		
			$nb_module=$db->countOf("cms_template_block","ID_TEMPLATE = ".$don['ID']);
			$nb_menu=$db->countOf("cms_template","TYPE = ".$don['TYPE']);

			if($cat_en_cours!=$don['TYPE']){
				$cat_en_cours=$don['TYPE'];
				echo "
			<tr>
				<td colspan='5'><b>".$don['CAT_NAME']."</b></td>
			</tr>";
			}
			echo"
			<tr class='odd'>
				<td >".$don['NAME']."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td>".genere_form_ordre('menu_ordre','?action=ordre',1,$nb_menu,$don['ORDRE'],0,array('ID'=>$don['ID']))."</td>
				<td><a href='modele_bloc.php?id=".$don['ID']."'><img src='".URL_DIR."/images/menu-edit.png'> (".gere_cas($nb_module,array('0'=>'Aucun bloc','1'=>'Un bloc','2'=>' blocs')).")</a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
			
		}
		
		echo "</table>";
	}else{
		echo "Aucun modèle";
	}
}
?>

<?
include('footer.php');
?>