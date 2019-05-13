<?
$id_module=26;

$ariane_element2['URL']="faq.php";
$ariane_element2['LIBELLE']="Ajouter une aide à la catégorie";	

include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if(!is_numeric($_GET['id'])){
	header('location:faq.php');
	die();
}
else{
	$id=$_GET['id'];
	$req=$db->query('select * from comm_faq_categorie where ID = '.$_GET["id"]);
	if(mysql_num_rows($req)!=0){
		$info_menu=mysql_fetch_array($req);
	}
	else {
		header('location:faq.php');
		die();
	}
	
}


/*Ajout*/
if($action=='add'){
	if(isset($_POST['module_add']) && $_POST['module_add']!=""){
		$nb_menu=$db->countOf("comm_faq_rel","ID_RUBRIQUE = ".$id);				
		$db->execute("INSERT INTO comm_faq_rel (ID_ARTICLE,ID_RUBRIQUE,ORDRE) VALUES ('".$_POST['module_add']."',".$id.",".($nb_menu+1).")");
		$_SESSION['notification'][]=array(1,"Aide de la catégorie","L'aide a été ajoutée à la catégorie.");
		header('location:faq_rel.php?id='.$id);
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Aide de la catégorie","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Ordre*/
if($action=='ordre'){	
	$req=$db->query("select * from comm_faq_rel WHERE ID_RUBRIQUE = ".$id);
	while($don=mysql_fetch_array($req)){	
		change_ordre("comm_faq_rel","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['faq_rel_ordre'],$don['ID'],$_POST['ID_REL']);
	}
	$_SESSION['notification'][]=array(1,"Aide de la catégorie","La position des aides a été modifiée.");
	header('location:faq_rel.php?id='.$id);	
	die();
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
	
		$req=$db->query('select * from comm_faq_rel WHERE ID = '.$_GET["id_rel"]);
		$info_req=mysql_fetch_array($req);
		
		$req=$db->query("select * from comm_faq_rel WHERE ID_RUBRIQUE = ".$id." AND ORDRE > ".$info_req['ORDRE']);
		while($don=mysql_fetch_array($req)){
			$db->execute("update comm_faq_rel set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
		}
	
		$db->execute("DELETE FROM comm_faq_rel WHERE ID = ".$_GET['id_rel']);
		$_SESSION['notification'][]=array(1,"Aide de la catégorie","L'aide a été retirée de la catégorie.");
		header('location:faq_rel.php?id='.$id);	
		die();		
	}
}
 
$array_menu[]=array('URL'=>'faq_aide.php','IMG'=>URL_DIR.'/images/menu.png','LIBELLE'=>'Gérer les aides');
$array_menu[]=array('URL'=>'faq.php','IMG'=>URL_DIR.'/images/categorie-menu.png','LIBELLE'=>'Gérer les catégories','WIDTH'=>200);
echo genere_sous_menu_admin($array_menu);?>
<br/>
<?

	$req=$db->query("select comm_faq_rel.ID,comm_faq_article.TITLE,comm_faq_rel.ORDRE from comm_faq_rel,comm_faq_article where comm_faq_rel.ID_ARTICLE=comm_faq_article.ID AND comm_faq_rel.ID_RUBRIQUE = ".$id." order by ordre ASC");
	
	$req_module=$db->query("select * from comm_faq_article order by TITLE ASC ");
	$select_module='';
	while($don_module=mysql_fetch_array($req_module)){
		$select_module.='<option value="'.$don_module['ID'].'">'.stripslashes($don_module['TITLE']).'</option>';
	}
	
	
	echo '
	<div id="form" style="background-color:#F5F5F5;">
		<h1>Ajouter une aide à la catégorie '.$info_menu['NAME'].'</h1>
		<form method="post" action="?action=add&id='.$id.'">
			<div class="input text"  style="float:left;">
				<label for="module_add"><b>Sélectionnez l\'aide à ajouter :</b></label>
				<select name="module_add" id="module_add"/>
				'.$select_module.'
				</select>
			</div>
			<div style="float:left;margin-left:10px;">
			<input type="submit" value="Ajouter l\'aide" />
			</div>
			<div class="clear"></div>
		</form>
	</div>
	<br/>';
	$nb_menu=mysql_num_rows($req);
	
	if($nb_menu!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th>Aide</th>
				<th>Ordre</th>
				<th>Retirer l'aide</th>
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
				<td>".$don['TITLE']."</td>
				<td>".genere_form_ordre('faq_rel_ordre','?action=ordre&id='.$id,1,$nb_menu,$don['ORDRE'],0,array('ID_REL'=>$don['ID']))."</td>
				<td><a href='?action=delete&id=".$id."&id_rel=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/cancel.png'></a></td>
			</tr>
			";
			
		}
		
		echo "</table>";
	}else{
	
		echo "Aucune aide reliée";
		
	}

include('footer.php');
?>