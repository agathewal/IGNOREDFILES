<?
$id_module=11;
$ariane_element2['URL']="module.php";
$ariane_element2['LIBELLE']="Modules";		
	
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element3['URL']="module.php";
	$ariane_element3['LIBELLE']="Ajouter un module";					
}elseif($action=='edit'){
	$ariane_element3['URL']="module.php";
	$ariane_element3['LIBELLE']="Modifier un module";
}


include('header.php');


/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['module_name']) && $_POST['module_name']!="" && isset($_POST['module_adresse']) && $_POST['module_adresse']!=""){
		$db->execute("INSERT INTO comm_module (LIBELLE,URL,INTRO) VALUES ('".addslashes($_POST['module_name'])."','".addslashes($_POST['module_adresse'])."','".addslashes($_POST['intro'])."')");
		$_SESSION['notification'][]=array(1,"Module du CMS","Le module a été ajouté.");
		header('location:module.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Module du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['module_name']) && $_POST['module_name']!="" && isset($_POST['module_adresse']) && $_POST['module_adresse']!=""){
		$db->execute("UPDATE comm_module SET LIBELLE = '".addslashes($_POST['module_name'])."' ,URL = '".addslashes($_POST['module_adresse'])."',INTRO = '".addslashes($_POST['intro'])."' WHERE ID = ".$_POST['id']);
		$_SESSION['notification'][]=array(1,"Module du CMS","Le module a été modifié.");
		header('location:module.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Module du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		$db->execute("DELETE FROM comm_module WHERE ID = ".$_GET['id']);
		
		$req_menu=$db->query('SELECT * FROM comm_module_menu WHERE ID_MODULE = '.$_GET['id']);
		while($don_menu=mysql_fetch_array($req_menu)){
			$req=$db->query("select * from comm_module_menu WHERE comm_module_menu.ID_MENU = ".$don_menu['ID_MENU']." AND ORDRE > ".$don_menu['ORDRE']);
			while($don=mysql_fetch_array($req)){
				$db->execute("update comm_module_menu set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
			}
		}
		
		$db->execute("DELETE FROM comm_module_menu WHERE ID_MODULE = ".$_GET['id']);
		$_SESSION['notification'][]=array(1,"Module du CMS","Le module a été supprimé.");
		header('location:module.php');		
		die();
	}
}

if($action=='add' || $action=='edit')$array_menu[]=array('URL'=>'module.php','IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
else $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter un module');
$array_menu[]=array('URL'=>'menu.php','IMG'=>URL_DIR.'/images/categorie-menu.png','LIBELLE'=>'Gérer les menus');
echo genere_sous_menu_admin($array_menu);
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from comm_module where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}

?>
	<div id="form">
	<h1>
	<?
		if($action=='add'){echo 'Ajouter un module';}
		else{ echo 'Modifier un module';}
		?>
	</h1>
	</div>
	<form method="post" id="form_user" action="module.php?action=<?echo $action;?>_r">
		<script type='text/javascript'>
		$(document).ready(function() {
			$("#form_user").validate({meta: "validate"});
		});
		</script>
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div style="margin-top:10px;">Nom du module</div>
		<div style='margin-top:5px;'>
			<input name="module_name" type="text" value='<?echo $form['LIBELLE'];?>' maxlength="50" id="module_name"  class="{validate:{required:true, messages:{required:'Veuillez saisir un nom pour le module'}}}"/>
		</div>
		<div style="margin-top:10px;">URL du module (par rapport au dossier admin)</div>
		<div style='margin-top:5px;'>
			<input name="module_adresse" type="text"  value='<?echo $form['URL'];?>' id="module_adresse" style="width:350px;"  class="{validate:{required:true, messages:{required:'Veuillez saisir une adresse pour le module'}}}"/>
		</div>
		<div style="margin-top:10px;">Texte d'introduction du module</div>
		<div style='margin-top:5px;'><textarea style="width:210px;height:40px;" name='intro'><?echo $form['INTRO'];?></textarea></div>
		<div class="submit"><input type="submit" value="
		<?
		if($action=='add'){echo 'Ajouter le module';}
		else{ echo 'Modifier le module';}
		?>" /></div>
	</form>
<?	
}
else{

	$req=$db->query("select * from comm_module order by LIBELLE ASC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th  class='frst'>Nom</th>
				<th>URL</th>
				<th>Modifier</th>
				<th>Supprimer</th>
			</tr>
			</thead>
		";
		$i=0;
		while($don=mysql_fetch_array($req)){
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			
			echo"
			<tr ".$style_table.">
				<td  class='frst'>".$don['LIBELLE']."</td>
				<td>".$don['URL']."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		
	}
}
?>

<?
include('footer.php');
?>