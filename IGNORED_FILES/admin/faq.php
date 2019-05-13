<?
$id_module=26;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="faq.php";
	$ariane_element2['LIBELLE']="Ajouter une catégorie";					
}elseif($action=='edit'){
	$ariane_element2['URL']="faq.php";
	$ariane_element2['LIBELLE']="Modifier une catégorie";
}

include('header.php');
/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['lib_categorie']) && $_POST['lib_categorie']!=""){
		$nb_menu=$db->countOfAll("comm_faq_categorie");				
		$db->execute("INSERT INTO comm_faq_categorie (NAME,ORDRE) VALUES ('".addslashes($_POST['lib_categorie'])."',".($nb_menu+1).")");
		$_SESSION['notification'][]=array(1,"Catégorie de la FAQ","La catégorie a été ajoutée.");
		header('location:faq.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Catégorie de la FAQ","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['lib_categorie']) && $_POST['lib_categorie']!=""){
		$db->execute("UPDATE comm_faq_categorie SET NAME = '".addslashes($_POST['lib_categorie'])."' WHERE ID = ".$_POST['id']);
		$_SESSION['notification'][]=array(1,"Catégorie de la FAQ","La catégorie a été modifiée.");
		header('location:faq.php');	
		die();		
	}
	else{
		$_SESSION['notification'][]=array(0,"Catégorie de la FAQ","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Ordre*/
if($action=='ordre'){	
	$req=$db->query("select * from comm_faq_categorie order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("comm_faq_categorie","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['cat_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Catégorie de la FAQ","La position de la catégorie a été modifiée.");
	header('location:faq.php');
	die();	
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
	
		$req=$db->query('select * from comm_faq_categorie where ID = '.$_GET["id"]);
		$info_req=mysql_fetch_array($req);
		
		$req=$db->query("select * from comm_faq_categorie where ORDRE > ".$info_req['ORDRE']);
		while($don=mysql_fetch_array($req)){
			$db->execute("update comm_faq_categorie set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
		}
		
		$db->execute("DELETE FROM comm_faq_categorie WHERE ID = ".$_GET['id']);
		$db->execute("DELETE FROM comm_faq_rel WHERE ID_RUBRIQUE = ".$_GET['id']);
		$_SESSION['notification'][]=array(1,"Catégorie de la FAQ","La catégorie a été supprimée.");
		header('location:faq.php');
		die();		
	}
}

if($action=='add' || $action=='edit')$array_menu[]=array('URL'=>'faq.php','IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
else $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une catégorie','WIDTH'=>'200');
$array_menu[]=array('URL'=>'faq_aide.php','IMG'=>URL_DIR.'/images/menu.png','LIBELLE'=>'Gérer les aides');
echo genere_sous_menu_admin($array_menu);?>
<br/><br/>
<?
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from comm_faq_categorie where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}
	
?>
	<div id="form">
		<h1>
		<?
			if($action=='add'){echo 'Ajouter une catégorie';}
			else{ echo 'Modifier une catégorie';}
			?>
		</h1>
	</div>
	<form method="post" id="form_user" action="faq.php?action=<?echo $action;?>_r">
		<script type='text/javascript'>
		$(document).ready(function() {
			$("#form_user").validate({meta: "validate"});
		});
		</script>
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div class="input text required">
			<label for="lib_categorie">Libellé de la catégorie</label>
			<input name="lib_categorie" type="text" value='<?echo $form['NAME'];?>' maxlength="50" id="lib_categorie"  class="{validate:{required:true, messages:{required:'Veuillez saisir un libellé pour la catégorie'}}}" />
		</div>
		<div class="submit"><input type="submit" value="
		<?
		if($action=='add'){echo 'Ajouter la catégorie';}
		else{ echo 'Modifier la catégorie';}
		?>" />
		</div>
	</form>
<?	
}
else{

	$req=$db->query("select * from comm_faq_categorie  order by ordre ASC");
	

	$nb_menu=mysql_num_rows($req);
	if($nb_menu!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th>Modifier</th>
				<th>Ordre</th>
				<th>Aides contenues dans cette rubrique</th>
				<th>Supprimer</th>
			</tr>
			</thead>
		";
		
		$i=0;
		while($don=mysql_fetch_array($req)){
		
			$nb_module=$db->countOf("comm_faq_rel","ID_RUBRIQUE = ".$don['ID']);
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			
			echo"
			<tr ".$style_table.">
				<td class='frst'>".$don['NAME']."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td>".genere_form_ordre('cat_ordre','?action=ordre',1,$nb_menu,$don['ORDRE'],0,array('ID'=>$don['ID']))."</td>
				<td><a href='faq_rel.php?id=".$don['ID']."'><img src='".URL_DIR."/images/menu-edit.png'> (".gere_cas($nb_module,array('0'=>'Aucune aide reliée','1'=>'Une aide reliée','2'=>' aides reliées')).")</a></td>
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