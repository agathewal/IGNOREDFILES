<?
$id_module=11;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="menu.php";
	$ariane_element2['LIBELLE']="Ajouter un menu";					
}elseif($action=='edit'){
	$ariane_element2['URL']="menu.php";
	$ariane_element2['LIBELLE']="Modifier un menu";
}

include('header.php');
/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['menu_name']) && $_POST['menu_name']!=""){
		$nb_menu=$db->countOfAll("comm_menu");				
		$db->execute("INSERT INTO comm_menu (NAME,ORDRE) VALUES ('".addslashes($_POST['menu_name'])."',".($nb_menu+1).")");
		$_SESSION['notification'][]=array(1,"Menu du CMS","Le menu a été ajouté au cms.");
		header('location:menu.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Menu du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['menu_name']) && $_POST['menu_name']!=""){
		$db->execute("UPDATE comm_menu SET NAME = '".addslashes($_POST['menu_name'])."' WHERE ID = ".$_POST['id']);
		$_SESSION['notification'][]=array(1,"Menu du CMS","Le menu a été modifié.");
		header('location:menu.php');	
		die();		
	}
	else{
		$_SESSION['notification'][]=array(0,"Menu du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Ordre*/
if($action=='ordre'){	
	$req=$db->query("select * from comm_menu order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("comm_menu","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['menu_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Menu du CMS","La position du menu a été modifié.");
	header('location:menu.php');
	die();	
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
	
		$req=$db->query('select * from comm_menu where ID = '.$_GET["id"]);
		$info_req=mysql_fetch_array($req);
		
		$req=$db->query("select * from comm_menu where ORDRE > ".$info_req['ORDRE']);
		while($don=mysql_fetch_array($req)){
			$db->execute("update comm_menu set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
		}
		
		$db->execute("DELETE FROM comm_menu WHERE ID = ".$_GET['id']);
		$db->execute("DELETE FROM comm_module_menu WHERE ID_MENU = ".$_GET['id']);
		$_SESSION['notification'][]=array(1,"Menu du CMS","Le menu a été supprimé.");
		header('location:menu.php');
		die();		
	}
}

if($action=='add' || $action=='edit')$array_menu[]=array('URL'=>'menu.php','IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
else $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/add.png','LIBELLE'=>'Ajouter un menu');
$array_menu[]=array('URL'=>'module.php','IMG'=>URL_DIR.'/images/menu.png','LIBELLE'=>'Gérer les modules');
echo genere_sous_menu_admin($array_menu);?>
<br/><br/>
<?
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from comm_menu where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}
	
?>
	<div id="form">
		<h1>
		<?
			if($action=='add'){echo 'Ajouter un menu';}
			else{ echo 'Modifier un menu';}
			?>
		</h1>
	</div>
	<form method="post" id="form_user" action="menu.php?action=<?echo $action;?>_r">
		<script type='text/javascript'>
		$(document).ready(function() {
			$("#form_user").validate({meta: "validate"});
		});
		</script>
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div class="input text required">
			<label for="menu_name">Nom du menu</label>
			<input name="menu_name" type="text" value='<?echo $form['NAME'];?>' maxlength="50" id="menu_name"  class="{validate:{required:true, messages:{required:'Veuillez saisir un nom pour le menu'}}}" />
		</div>
		<div class="submit"><input type="submit" value="
		<?
		if($action=='add'){echo 'Ajouter le menu';}
		else{ echo 'Modifier le menu';}
		?>" />
		</div>
	</form>
<?	
}
else{

	$req=$db->query("select * from comm_menu  order by ordre ASC");
	

	$nb_menu=mysql_num_rows($req);
	if($nb_menu!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th>Modifier</th>
				<th>Ordre</th>
				<th>Modules contenus dans le menu</th>
				<th>Supprimer</th>
			</tr>
			</thead>
		";
		
		$i=0;
		while($don=mysql_fetch_array($req)){
		
			$nb_module=$db->countOf("comm_module_menu","ID_MENU = ".$don['ID']);
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			
			echo"
			<tr ".$style_table.">
				<td class='frst'>".$don['NAME']."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td>".genere_form_ordre('menu_ordre','?action=ordre',1,$nb_menu,$don['ORDRE'],0,array('ID'=>$don['ID']))."</td>
				<td><a href='module_menu.php?id=".$don['ID']."'><img src='".URL_DIR."/images/menu-edit.png'> (".gere_cas($nb_module,array('0'=>'Aucun modèle relié','1'=>'Un modèle relié','2'=>' modèles reliés')).")</a></td>
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