<?
$id_module=11;

$ariane_element2['URL']="menu.php";
$ariane_element2['LIBELLE']="Ajouter un module au menu";	

include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if(!is_numeric($_GET['id'])){
	header('location:menu.php');
	die();
}
else{
	$id=$_GET['id'];
	$req=$db->query('select * from comm_menu where ID = '.$_GET["id"]);
	if(mysql_num_rows($req)!=0){
		$info_menu=mysql_fetch_array($req);
	}
	else {
		header('location:menu.php');
		die();
	}
	
}


/*Ajout*/
if($action=='add'){
	if(isset($_POST['module_add']) && $_POST['module_add']!=""){
		$nb_menu=$db->countOf("comm_module_menu","comm_module_menu.ID_MENU = ".$id);				
		$db->execute("INSERT INTO comm_module_menu (ID_MODULE,ID_MENU,ORDRE) VALUES ('".$_POST['module_add']."',".$id.",".($nb_menu+1).")");
		$_SESSION['notification'][]=array(1,"Menu du CMS","Le module a été ajouté au menu.");
		header('location:module_menu.php?id='.$id);
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Menu du CMS","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Ordre*/
if($action=='ordre'){	
	//pr($_POST);
	$req=$db->query("select * from comm_module_menu where comm_module_menu.ID_MENU = ".$id);
	while($don=mysql_fetch_array($req)){	
		change_ordre("comm_module_menu","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['menu_module_ordre'],$don['ID'],$_POST['ID_REL']);
	}
	$_SESSION['notification'][]=array(1,"Menu du CMS","La position des modules a été modifié.");
	header('location:module_menu.php?id='.$id);	
	die();
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
	
		$req=$db->query('select * from comm_module_menu WHERE ID = '.$_GET["id_rel"]);
		$info_req=mysql_fetch_array($req);
		
		$req=$db->query("select * from comm_module_menu WHERE comm_module_menu.ID_MENU = ".$id." AND ORDRE > ".$info_req['ORDRE']);
		while($don=mysql_fetch_array($req)){
			$db->execute("update comm_module_menu set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
		}
	
		$db->execute("DELETE FROM comm_module_menu WHERE ID = ".$_GET['id_rel']);
		$_SESSION['notification'][]=array(1,"Menu du CMS","Le module a été retiré du menu.");
		header('location:module_menu.php?id='.$id);	
		die();		
	}
}
 
$array_menu[]=array('URL'=>'module.php','IMG'=>URL_DIR.'/images/menu.png','LIBELLE'=>'Gérer les modules');
$array_menu[]=array('URL'=>'menu.php','IMG'=>URL_DIR.'/images/categorie-menu.png','LIBELLE'=>'Gérer les menus');
echo genere_sous_menu_admin($array_menu);

	$req=$db->query("select comm_module_menu.ID,comm_module.LIBELLE,comm_module_menu.ORDRE from comm_module_menu,comm_module where comm_module_menu.ID_MODULE=comm_module.ID AND comm_module_menu.ID_MENU = ".$id." order by ordre ASC");
	
	$req_module=$db->query("select * from comm_module order by LIBELLE ASC ");
	$select_module='';
	while($don_module=mysql_fetch_array($req_module)){
		$select_module.='<option value="'.$don_module['ID'].'">'.$don_module['LIBELLE'].'</option>';
	}
	
	
	echo '
	<script>
	function valide_form(){
		document.forms["form_mod"].submit();
	}
	</script>
	<div class=\'clear\'></div>
	<div style=\'height:15px;width:100%;\'></div>
	<div>
		<h1>Ajouter un module au menu</h1>
		<form method="post" name="form_mod" action="?action=add&id='.$id.'">
			<div style="margin-top:10px;">Sélectionnez le module à ajouter :</div>
			<div style="margin-top:5px;">
				<div style="float:left;width:150px;">
					<select name="module_add" id="module_add"/>
					'.$select_module.'
					</select>
				</div>
				<div class="bordure_menu" style=\'width:175px;float:left;\' onclick="javascript:valide_form();">
					<div style=\'width:175px;background-image:url("'.URL_DIR.'/images/btn_add.png");\' class="btn_style">
						<p>Ajouter le module</p>
					</div>
				</div>	
			</div>	
		</form>
	</div>';
	$nb_menu=mysql_num_rows($req);
	
	if($nb_menu!=0){
		
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Module</th>
				<th width='100'>Ordre</th>
				<th width='150'>Retirer le module</th>
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
				<td class='frst'>".$don['LIBELLE']."</td>
				<td>".genere_form_ordre('menu_module_ordre','?action=ordre&id='.$id,1,$nb_menu,$don['ORDRE'],0,array('ID_REL'=>$don['ID']))."</td>
				<td><a href='?action=delete&id=".$id."&id_rel=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/cancel.png'></a></td>
			</tr>
			";
			
		}
		
		echo "</table>";
	}else{
	
		echo "Aucun module relié";
		
	}

include('footer.php');
?>