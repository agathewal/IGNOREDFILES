<?
$id_module=33;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

$count_categorie=$db->countOf('comm_infos_categorie','ID_LANG = '.$_SESSION['langue']);

/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['groupe_name']) && $_POST['groupe_name']!="" && isset($_POST['type_critere']) && is_numeric($_POST['type_critere'])){		
		
		if($_POST['nb_max']=='')$_POST['nb_max']=-1;
		
		$db->execute("INSERT INTO comm_infos (`NAME`,`TYPE`,`NB`,`ID_LANG`) VALUES ('".addslashes($_POST['groupe_name'])."',".$_POST['type_critere'].",".$_POST['nb_max'].",".$_SESSION['langue'].")");
		$id_info=$db->lastInsertedId();
		
		if(isset($_POST['cat_critere']) && is_numeric($_POST['cat_critere'])){
			$nb_item=$db->countOf('comm_infos_rel_categorie','ID_CATEGORIE = '.$_POST['cat_critere']);
			$db->execute("INSERT INTO comm_infos_rel_categorie (ID_CATEGORIE,ID_INFO,ORDRE) VALUES (".$_POST['cat_critere'].",".$id_info.",".($nb_item+1).")");
		}
		
		$_SESSION['notification'][]=array(1,"Informations","L'information a été ajoutée.");
		header('location:critere.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Informations","Veuillez remplir tous les champs");
		$action='add';
	}
}
elseif($action=='edit_r'){/*Modification*/
	if(isset($_POST['groupe_name']) && $_POST['groupe_name']!="" && isset($_POST['type_critere']) && is_numeric($_POST['type_critere'])){	
			
		if($_POST['nb_max']=='')$_POST['nb_max']=-1;
		
		$db->execute("UPDATE comm_infos SET NAME = '".addslashes($_POST['groupe_name'])."', NB = ".$_POST['nb_max'].", `TYPE` = ".$_POST['type_critere']." WHERE ID = ".$_POST['id']);
		
		if(isset($_POST['cat_critere']) && is_numeric($_POST['cat_critere'])){
			$verif=$db->countOf('comm_infos_rel_categorie','ID_INFO = '.$_POST['id'].' AND ID_CATEGORIE = '.$_POST['cat_critere']);
			if($verif==0){
				$db->execute('DELETE FROM comm_infos_rel_categorie WHERE ID_INFO = '.$_POST['id']);
				$nb_item=$db->countOf('comm_infos_rel_categorie','ID_CATEGORIE = '.$_POST['cat_critere']);
				$db->execute("INSERT INTO comm_infos_rel_categorie (ID_CATEGORIE,ID_INFO,ORDRE) VALUES (".$_POST['cat_critere'].",".$_POST['id'].",".($nb_item+1).")");
			}
		}
		
		
		$_SESSION['notification'][]=array(1,"Informations","L'information a été modifiée.");
		header('location:critere.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Informations","Veuillez remplir tous les champs");
		$action='add';
	}
}
elseif($action=='ordre_r'){	/*Ordre des infos*/
	$req=$db->query("select * from comm_infos_rel_categorie WHERE ID_CATEGORIE = ".$_POST['ID_CAT']." order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("comm_infos_rel_categorie","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['nav_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Informations","La position de l'information a été modifiée.");
	header('location:critere.php');
	die();	
}
elseif($action=='ordre_cat_r'){	/*Ordre des catégories d'infos*/
	$req=$db->query("select * from comm_infos_categorie WHERE ID_LANG = ".$_SESSION['langue']." order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("comm_infos_categorie","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['nav_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Informations","La position de la catégorie a été modifiée.");
	header('location:critere.php');
	die();	
}
elseif($action=='delete'){/*Suppression*/
	if(isset($_GET['id'])){
		$db->execute("DELETE FROM comm_infos WHERE ID = ".$_GET['id']);	
		$req_info=$db->query("SELECT * FROM comm_infos_rel_categorie WHERE ID_INFO = ".$_GET['id']);
		if(mysql_num_rows($req_info)!=0){
			$don_info=mysql_fetch_array($req_info);
			$req=$db->query('select * from comm_infos_rel_categorie WHERE ID_CATEGORIE = '.$don_info['ID_CATEGORIE'].' AND ORDRE > '.$don_info['ORDRE']);
			if(mysql_num_rows($req)!=0){
				while($don=mysql_fetch_array($req)){
					$db->execute('UPDATE comm_infos_rel_categorie SET ORDRE = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
				}
			}
		}
		
		$db->execute("DELETE FROM comm_infos_rel_categorie WHERE ID_INFO = ".$_GET['id']);	
	
		$_SESSION['notification'][]=array(1,"Informations","L'information a été supprimée.");
		header('location:critere.php');		
		die();
	}
}
elseif($action=='delete_cat'){/*Suppression*/
	if(isset($_GET['id'])){
		
		$req_info=$db->query("SELECT * FROM comm_infos_categorie WHERE ID = ".$_GET['id']);
		if(mysql_num_rows($req_info)!=0){
			$don_info=mysql_fetch_array($req_info);
			$req=$db->query('select * from comm_infos_categorie WHERE ID_LANG = '.$_SESSION['langue'].' AND ORDRE > '.$don_info['ORDRE']);
			if(mysql_num_rows($req)!=0){
				while($don=mysql_fetch_array($req)){
					$db->execute('UPDATE comm_infos_categorie SET ORDRE = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
				}
			}
		}
		$db->execute("DELETE FROM comm_infos_categorie WHERE ID = ".$_GET['id']);	
		$db->execute("DELETE FROM comm_infos_rel_categorie WHERE ID_CATEGORIE = ".$_GET['id']);	
	
		$_SESSION['notification'][]=array(1,"Informations","La catégorie a été supprimée.");
		header('location:critere.php');		
		die();
	}
}
elseif($action=="delete_list" && is_numeric($_GET['id_list'])){
	$db->execute('DELETE FROM comm_infos_list WHERE ID = '.$_GET['id_list']);
	$_SESSION['notification'][]=array(1,"Informations","Le choix a été supprimé.");
	header('location:critere.php?action=list&id='.$_GET['id']);		
	die();
}
elseif($action=='add_cat_r'){
	if(isset($_POST['groupe_name']) && $_POST['groupe_name']!=""){		
		
		$db->execute("INSERT INTO comm_infos_categorie (`NAME`,`ORDRE`,`ID_LANG`) VALUES ('".addslashes($_POST['groupe_name'])."',".($count_categorie+1).",".$_SESSION['langue'].")");		
		$_SESSION['notification'][]=array(1,"Informations","La catégorie a été ajoutée.");
		header('location:critere.php');	
		die();
	}
}
elseif($action=='add_list_r'){
	if(isset($_POST['groupe_name']) && $_POST['groupe_name']!=""){		
		
		$db->execute("INSERT INTO comm_infos_list (`NAME`,`ID_CRITERE`) VALUES ('".addslashes($_POST['groupe_name'])."',".$_GET['id'].")");		
		$_SESSION['notification'][]=array(1,"Informations","Le choix a été ajouté.");
		header('location:critere.php?action=list&id='.$_GET['id']);	
		die();
	}
}
elseif($action=='edit_cat_r'){
	if(isset($_POST['groupe_name']) && $_POST['groupe_name']!=""){		
		
		$db->execute("UPDATE comm_infos_categorie SET NAME = '".addslashes($_POST['groupe_name'])."' WHERE ID = ".$_POST['id']);		
		$_SESSION['notification'][]=array(1,"Informations","La catégorie a été modifiée.");
		header('location:critere.php');	
		die();
	}
}
elseif($action=='edit_list_r'){
	if(isset($_POST['groupe_name']) && $_POST['groupe_name']!=""){		
		
		$db->execute("UPDATE comm_infos_list SET `NAME` = '".addslashes($_POST['groupe_name'])."' WHERE ID = ".$_POST['id_list']);		
		$_SESSION['notification'][]=array(1,"Informations","Le choix a été modifié.");
		header('location:critere.php?action=list&id='.$_GET['id']);	
		die();
	}
}
elseif($action==''){
	if($count_categorie>0)$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/add.png','WIDTH'=>200,'LIBELLE'=>'Ajouter une information');
	$array_menu[]=array('URL'=>'?action=add_cat','IMG'=>URL_DIR.'/images/add.png','WIDTH'=>200,'LIBELLE'=>'Ajouter une catégorie');
}
elseif($action=="list"){
	
	$array_menu[]=array('URL'=>'critere.php','IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
	$array_menu[]=array('URL'=>'?action=add_list&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/add.png','WIDTH'=>200,'LIBELLE'=>'Ajouter un choix');
}	
echo genere_sous_menu_admin($array_menu);

if($action=='add_list' || $action=='edit_list'){
	$form=array();
	if($action=='edit_list'){
		$req=$db->query('select * from comm_infos_list where ID = '.$_GET["id_list"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}
?>
	<form method="post" id="form_user" name="form_feature" action="critere.php?action=<?echo $action;?>_r&id=<?echo $_GET['id'];?>">
	<script type='text/javascript'>
		function valide_form(){				
			var error=0;
			
			if($('#groupe_name').val()==''){
				$('#complete_all').show();
				$('#groupe_name').css({"border":"1px solid #CC0099"});
				error=1;
			}else{
				$('#complete_all').hide();
				$('#groupe_name').css({"border":"1px solid #999999"});
			}			
			
			if(error==0){
				document.forms["form_feature"].submit();	
			}		
		}
	</script>
	<input type='hidden' name='id_list' value='<?echo $form['ID'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name"><?if($action=='add_list'){echo "Ajouter";}else{echo "Modifier";}?> un choix</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >
		<div>Choix <span id="complete_all" style="display:none;"><b style='color:#CC0099;'>- Merci de saisir un choix</b></span></div>
		<div style="margin-top:10px;"><input name="groupe_name" type="text" value='<?echo $form['NAME'];?>' id="groupe_name" style="width:75%;"/></div>	
	</div>
	<div class="clear"></div>
	<div style="margin-top:40px;">						
		<div class="bordure_menu" style='width:195px;float:left;' onclick="javascript:valide_form();">
			<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
				<p><?
				if($action=='add_list'){echo 'Ajouter le choix';}
				else{ echo 'Modifier le choix';}
				?></p>
			</div>
		</div>				
		<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('critere.php?action=list&id=<?echo $_GET['id'];?>');">
			<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
		</div>			
	</div>
	</form>
<?	
	
}
else if($action=='add_cat' || $action=='edit_cat'){
	$form=array();
	if($action=='edit_cat'){
		$req=$db->query('select * from comm_infos_categorie where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	}
?>
	<form method="post" id="form_user" name="form_feature" action="critere.php?action=<?echo $action;?>_r">
	<script type='text/javascript'>
		function valide_form(){				
			var error=0;
			
			if($('#groupe_name').val()==''){
				$('#complete_all').show();
				$('#groupe_name').css({"border":"1px solid #CC0099"});
				error=1;
			}else{
				$('#complete_all').hide();
				$('#groupe_name').css({"border":"1px solid #999999"});
			}			
			
			if(error==0){
				document.forms["form_feature"].submit();	
			}		
		}
	</script>
	<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name"><?if($action=='add_cat'){echo "Ajouter";}else{echo "Modifier";}?> une catégorie</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >
		<div>Nom de la catégorie <span id="complete_all" style="display:none;"><b style='color:#CC0099;'>- Merci de saisir un nom</b></span></div>
		<div style="margin-top:10px;"><input name="groupe_name" type="text" value='<?echo $form['NAME'];?>' id="groupe_name" style="width:75%;"/></div>	
	</div>
	<div class="clear"></div>
	<div style="margin-top:40px;">						
		<div class="bordure_menu" style='width:195px;float:left;' onclick="javascript:valide_form();">
			<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
				<p><?
				if($action=='add_cat'){echo 'Ajouter la catégorie';}
				else{ echo 'Modifier la catégorie';}
				?></p>
			</div>
		</div>				
		<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('critere.php');">
			<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
		</div>			
	</div>
	</form>
<?	
	
}elseif($action=='list' && is_numeric($_GET['id'])){
	
	$req=$db->query('select * from comm_infos_list WHERE ID_CRITERE = '.$_GET['id']);
	
	echo "
	<div class='clear'></div>
	<div style='height:15px;width:100%;'>&nbsp;</div>
	";
	if(mysql_num_rows($req)!=0){
		
		
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th width='90'>Modifier</th>
				<th width='90'>Supprimer</th>
			</tr>
			</thead>
		";
		
		while($don=mysql_fetch_array($req)){
			echo "
			<tr>
				<td class='frst'>".stripslashes($don['NAME'])."</td>
				<td><a href='?action=edit_list&id=".$_GET['id']."&id_list=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete_list&id=".$_GET['id']."&id_list=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
		
	
	}else{
		echo "
		Aucun choix défini";
	}

	
}
else if($action=='add' || $action=='edit'){

	$form=array();
	$list_cat_present=array();
	if($action=='edit'){
		$req=$db->query('select * from comm_infos where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
		$req_cat=$db->query('select ID_CATEGORIE from comm_infos_rel_categorie where ID_INFO = '.$_GET['id']);
		if(mysql_num_rows($req_cat)!=0){
			while($don=mysql_fetch_array($req_cat))$list_cat_present[]=$don['ID_CATEGORIE'];
		}
	}

?>
	
	<form method="post" id="form_user" name="form_feature" action="critere.php?action=<?echo $action;?>_r">
	<script type='text/javascript'>
		function valide_form(){	
			
			var error=0;
			
			if($('#groupe_name').val()==''){
				$('#complete_all').show();
				$('#groupe_name').css({"border":"1px solid #CC0099"});
				error=1;
			}else{
				$('#complete_all').hide();
				$('#groupe_name').css({"border":"1px solid #999999"});
			}
			
			
			if(error==0){
				document.forms["form_feature"].submit();	
			}		
		}
		
	
	</script>
	<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
	<div id="form_admin">
		<div style='float:left;width:605px;'><h1 id="etape_name"><?if($action=='add'){echo "Ajouter";}else{echo "Modifier";}?> une information</h1></div>
		<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >
		<div>Information <span id="complete_all" style="display:none;"><b style='color:#CC0099;'>- Merci de saisir un nom</b></span></div>
		<div style="margin-top:10px;"><input name="groupe_name" type="text" value='<?echo $form['NAME'];?>' id="groupe_name" style="width:75%;"/></div>	
		<div style="margin-top:20px;" id="zone_critere_1" >
			Mon information sera remplie par l'internaute :			
		</div>
		<div style="margin-top:10px;" id="zone_critere_2" >
			<select name='type_critere'>
				<option value="2">avec une liste de choix que je définirais</option>
				<option value="1" <?if($form['TYPE']==1){ echo 'selected';}?>>librement</option>				
			</select>
		</div>
		<div style="margin-top:20px;" <?echo add_help('Informations',"Vous pouvez par exemple limiter une information nommée <b>sport</b> à 2 choix. Si vous laissez vide le champ, on considèrera qu'il n'y pas de limite");?>>
			Nombre maximum de choix pour cette information			
		</div>
		<div style="margin-top:10px;"><input name="nb_max" type="text" value='<?if( $form['NB']!=-1)echo $form['NB'];?>' id="nb_max" style="width:15%;"/></div>	
		<?
		$req_cat=$db->query('select * from comm_infos_categorie WHERE ID_LANG = '.$_SESSION['langue'].' ORDER BY NAME');
		if(mysql_num_rows($req_cat)!=0){
		?>
		<div style="margin-top:20px;">
			Mon informations sera affichée dans la catégorie : 		
		</div>
		<div style="margin-top:10px;">
			<select name="cat_critere">
			<?
			while($don=mysql_fetch_array($req_cat)){
				
				echo '<option value="'.$don['ID'].'" '.((in_array($don['ID'],$list_cat_present))?'selected="selected"':'').'>'.stripslashes($don['NAME']).'</option>';
			}
			?>
			</select>
		</div>
		<?
		}?>
	</div>
	<div class="clear"></div>
	<div style="margin-top:40px;">						
		<div class="bordure_menu" style='width:195px;float:left;' onclick="javascript:valide_form();">
			<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
				<p><?
				if($action=='add'){echo 'Ajouter l\'information';}
				else{ echo 'Modifier l\'information';}
				?></p>
			</div>
		</div>				
		<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('critere.php');">
			<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
		</div>			
	</div>
	
	
	</form>
	
	
<?	
}
else{

	$req=$db->query("select comm_infos.NAME as NAME_INFO, comm_infos.ID as ID_INFO, comm_infos.TYPE as TYPE_INFO, comm_infos_rel_categorie.ORDRE as ORDRE_INFO, comm_infos_categorie.NAME as NAME_CAT, comm_infos_categorie.ID as ID_CAT,comm_infos_categorie.ORDRE as ORDRE_CAT from comm_infos,comm_infos_categorie,comm_infos_rel_categorie WHERE comm_infos.ID=comm_infos_rel_categorie.ID_INFO AND  comm_infos_categorie.ID=comm_infos_rel_categorie.ID_CATEGORIE order by comm_infos_categorie.ORDRE,comm_infos_rel_categorie.ORDRE");

	echo "
	<div class='clear'></div>
	<div style='height:15px;width:100%;'>&nbsp;</div>";
	
	if(mysql_num_rows($req)!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th width='90'>Ordre</th>
				<th width='90'>Gérer la liste</th>
				<th width='90'>Modifier</th>
				<th width='90'>Supprimer</th>
			</tr>
			</thead>
		";
		$i=0;
		$id_cat=0;
		
		while($don=mysql_fetch_array($req)){			
				
			if($id_cat!=$don['ID_CAT']){
				
				$count_cat=$db->countOf('comm_infos_rel_categorie','ID_CATEGORIE = '.$don['ID_CAT']);
				$id_cat=$don['ID_CAT'];
				echo"
				<tr>
					<td class='frst'><b>".stripslashes($don['NAME_CAT'])."</b></td>
					<td style='padding-left:10px;'>".genere_form_ordre('nav_ordre','?action=ordre_cat_r',1,$count_categorie,$don['ORDRE_CAT'],0,array('ID'=>$don['ID_CAT']))."</td>
					<td></td>
					<td><a href='?action=edit_cat&id=".$don['ID_CAT']."'><img src='".URL_DIR."/images/edit.png'></a></td>
					<td><a href='?action=delete_cat&id=".$don['ID_CAT']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
				</tr>
				";
			}
				
			echo"
			<tr>
				<td class='frst2'>".stripslashes($don['NAME_INFO'])."</td>
				<td style='padding-left:25px;'>".genere_form_ordre('nav_ordre','?action=ordre_r',1,$count_cat,$don['ORDRE_INFO'],0,array('ID'=>$don['ID_INFO'],'ID_CAT'=>$don['ID_CAT']))."</td>
				<td>".(($don['TYPE_INFO']==2)?"<a href='?action=list&id=".$don['ID_INFO']."'><img src='".URL_DIR."/images/btn_crew.png'></a>":"")."</td>
				<td><a href='?action=edit&id=".$don['ID_INFO']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID_INFO']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
			
		}
		
		echo "</table>";
	}else{
		echo "Aucune information définie";
	}
}

include('footer.php');
?>