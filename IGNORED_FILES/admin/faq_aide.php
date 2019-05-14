<?
$id_module=26;
$ariane_element2['URL']="faq_aide.php";
$ariane_element2['LIBELLE']="Aides";		
	
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element3['URL']="faq_aide.php";
	$ariane_element3['LIBELLE']="Ajouter une aide";					
}elseif($action=='edit'){
	$ariane_element3['URL']="faq_aide.php";
	$ariane_element3['LIBELLE']="Modifier une aide";
}

include('header.php');

/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['help_name']) && $_POST['help_name']!="" && isset($_POST['text_article']) && $_POST['text_article']!=""){
	
		if(isset($_POST['only_admin'])  && $_POST['only_admin']==1)$_POST['only_admin']=1;
		else $_POST['only_admin']=0;
		
		if(isset($_POST['only_supra_admin'])  && $_POST['only_supra_admin']==1)$_POST['only_supra_admin']=1;
		else $_POST['only_supra_admin']=0;
		
		$db->execute("INSERT INTO comm_faq_article (TITLE,`TEXT`,ONLY_ADMIN,ONLY_SUPRA_ADMIN) VALUES ('".addslashes($_POST['help_name'])."','".addslashes($_POST['text_article'])."',".$_POST['only_admin'].",".$_POST['only_supra_admin'].")");
		$_SESSION['notification'][]=array(1,"Aides de la FAQ","L'aide a été ajoutée.");
		header('location:faq_aide.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Aides de la FAQ","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['help_name']) && $_POST['help_name']!="" && isset($_POST['text_article']) && $_POST['text_article']!=""){
		
		if(isset($_POST['only_admin'])  && $_POST['only_admin']==1)$_POST['only_admin']=1;
		else $_POST['only_admin']=0;
		
		if(isset($_POST['only_supra_admin'])  && $_POST['only_supra_admin']==1)$_POST['only_supra_admin']=1;
		else $_POST['only_supra_admin']=0;
		
		$db->execute("UPDATE comm_faq_article SET TITLE = '".addslashes($_POST['help_name'])."' ,`TEXT` = '".addslashes($_POST['text_article'])."', ONLY_ADMIN = ".$_POST['only_admin']." , ONLY_SUPRA_ADMIN = ".$_POST['only_supra_admin']." WHERE ID = ".$_POST['id']);
		$_SESSION['notification'][]=array(1,"Aides de la FAQ","L'aide a été modifiée.");
		header('location:faq_aide.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Aides de la FAQ","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		$db->execute("DELETE FROM comm_faq_article WHERE ID = ".$_GET['id']);
		
		$req_menu=$db->query('SELECT * FROM comm_faq_rel WHERE ID_ARTICLE = '.$_GET['id']);
		while($don_menu=mysql_fetch_array($req_menu)){
			$req=$db->query("select * from comm_faq_rel WHERE comm_faq_rel.ID_ARTICLE = ".$don_menu['ID_ARTICLE']." AND ORDRE > ".$don_menu['ORDRE']);
			while($don=mysql_fetch_array($req)){
				$db->execute("update comm_faq_rel set ORDRE = ".($don['ORDRE']-1)." WHERE ID = ".$don['ID']);
			}
		}
		
		$db->execute("DELETE FROM comm_faq_rel WHERE ID_ARTICLE = ".$_GET['id']);
		$_SESSION['notification'][]=array(1,"Aides de la FAQ","L'aide a été supprimée.");
		header('location:faq_aide.php');		
		die();
	}
}

if($action=='add' || $action=='edit')$array_menu[]=array('URL'=>'faq_aide.php','IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
else $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/add.png','LIBELLE'=>'Ajouter une aide');
$array_menu[]=array('URL'=>'faq.php','IMG'=>URL_DIR.'/images/categorie-menu.png','LIBELLE'=>'Gérer les catégories','WIDTH'=>'175');
echo genere_sous_menu_admin($array_menu);

if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		$req=$db->query('select * from comm_faq_article where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}

?>
	<div id="form">
	<h1 style="margin-top:10px;">
	<?
		if($action=='add'){echo 'Ajouter une aide';}
		else{ echo 'Modifier une aide';}
	?>
	</h1>
	</div>
	<form method="post" id="form_user" action="faq_aide.php?action=<?echo $action;?>_r">
		<script type="text/javascript" src="<?echo URL_DIR;?>/tiny_mce/tiny_mce.js"></script>
		<script type='text/javascript'>
		$(document).ready(function() {
			$("#form_user").validate({meta: "validate"});
		});
		<? echo genere_tiny_mce('text_article');?>
		<? echo genere_file_browser();?>
		</script>
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div class="input text required" style="margin-top:10px;">
			<label for="help_name">Titre de l'aide</label>
			<input name="help_name" type="text" value='<?echo $form['TITLE'];?>' style="width:80%;" id="help_name"  class="{validate:{required:true, messages:{required:'Veuillez saisir un titre pour l\'aide'}}}"/>
		</div>
		<div class="input text required" style="margin-top:10px;">
			<label for="text_article">Texte</label>
			<textarea name="text_article" rows="15" cols="80" style="width: 80%" id="text_article" class="tinymce"><?echo $form['TEXT'];?></textarea>
		</div>
		<div class="input text required" style="margin-top:10px;">
			<input type="checkbox" name="only_admin" value="1" <?if ($form['ONLY_ADMIN']==1){ echo 'checked="checked"';} ?>> Aide visible uniquement par des administrateurs de tribu ou supra admin
		</div>
		<div class="input text required" style="margin-top:10px;">
			<input type="checkbox" name="only_supra_admin" value="1" <?if ($form['ONLY_SUPRA_ADMIN']==1){ echo 'checked="checked"';} ?>> Aide visible uniquement par des supra-admins
		</div>
		<div class="submit" style="margin-top:10px;"><input type="submit" value="<?
		if($action=='add'){echo 'Ajouter l\'aide';}
		else{ echo 'Modifier l\'aide';}
		?>" /></div>
	</form>
<?	
}
else{

	$req=$db->query("select * from comm_faq_article order by TITLE ASC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view' style='margin-top:15px;'>
			<thead>
			<tr>
				<th style='text-align:left;'>ID</th>
				<th style='text-align:left;'>Nom</th>
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
				<td style='text-align:left;'>".$don['ID']."</td>
				<td style='text-align:left;'>".stripslashes($don['TITLE'])."</td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		
	}
}

include('footer.php');
?>