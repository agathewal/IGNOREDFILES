<?
$id_module=14;

$ariane_element2['URL']="video_categorie.php";
$ariane_element2['LIBELLE']="Chaînes vidéos";		
	
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element3['URL']="video_categorie.php";
	$ariane_element3['LIBELLE']="Ajouter une chaîne";					
}elseif($action=='edit'){
	$ariane_element3['URL']="video_categorie.php";
	$ariane_element3['LIBELLE']="Modifier une chaîne";
}

include('header.php');
/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['name']) && $_POST['name']!=""){	
		$ordre=$db->countOf('cms_video_categorie','ID_LANG='.$_SESSION['langue'].' AND ID_PUSH = 0 AND ORDRE > 0 AND ID_UNIVERS = '.$_SESSION['univers']);
		$db->execute("INSERT INTO cms_video_categorie (TITLE,ID_LANG,ID_UNIVERS,ORDRE) VALUES ('".addslashes($_POST['name'])."',".$_SESSION['langue'].",".$_SESSION['univers'].",".($ordre+1).")");
		$_SESSION['notification'][]=array(1,"Vidéos","La chaîne a été créée.");
		header('location:video_categorie.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Vidéos","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['name']) && $_POST['name']!=""){
		$db->execute("UPDATE cms_video_categorie SET TITLE = '".addslashes($_POST['name'])."' WHERE ID = ".$_POST['id']);
		$_SESSION['notification'][]=array(1,"Vidéos","La chaîne a été modifiée.");
		header('location:video_categorie.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Vidéos","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
if($action=='ordre'){	/*Réordonner les galeries*/
	$req=$db->query("select * from cms_video_categorie WHERE ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." AND ID_PUSH = 0 order by ordre ASC");
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_video_categorie","ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['video_ordre'],$don['ID'],$_POST['ID']);
	}
	$_SESSION['notification'][]=array(1,"Vidéos","La position de la chaîne a été modifiée.");
	header('location:video_categorie.php');
	die();	
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		$num_concern=$db->countOf('cms_video_categorie_link','ID_CATEGORIE = '.$_GET['id']);
		if($num_concern==0){
			
			$req=$db->query('select * from cms_video_categorie where ID = '.$_GET["id"]);
			$form=mysql_fetch_array($req);
					
			$req2=$db->query('SELECT * FROM cms_video_categorie WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PUSH = 0 AND ORDRE > '.$form['ORDRE']);
			while($don2=mysql_fetch_array($req2)){
				$db->execute('UPDATE cms_video_categorie SET ORDRE = '.($don2['ORDRE']-1).' WHERE ID = '.$don2['ID']);
			}

		
		
			$db->execute("DELETE FROM cms_video_categorie WHERE ID = ".$_GET['id']);
			$_SESSION['notification'][]=array(1,"Vidéos","La chaîne a été supprimée.");
		}else{
			$_SESSION['notification'][]=array(0,"Vidéos","La chaîne est actuellement utilisée, merci de supprimer ou modifier les vidéos l'utilisant.");
		}
		header('location:video_categorie.php');		
		die();
	}
}

if($action=='') {
	$array_menu[]=array('URL'=>'video.php','IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Gérer les vidéos','WIDTH'=>'200');
	$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une chaîne','WIDTH'=>'200');
}
echo genere_sous_menu_admin($array_menu);

if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		
		$req=$db->query('select * from cms_video_categorie where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
	
	}

?>
<script>
function valide_form(){
	if($('#name').val()==''){
		$('#complete_all').show();
		$('#name').css({"border":"1px solid #333399"});
	}else{
		$('#complete_all').hide();
		$('#name').css({"border":"1px solid #999999"});
		document.forms['form_user'].submit();	
	}		
}
</script>
<div id="step1_admin" >
	<form method="post"  id="form_user" name="form_user" action="video_categorie.php?action=<?echo $action;?>_r">
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div style="margin-top:10px;">Nom de la chaîne : <span id="complete_all" style="display:none;"><b style='color:#333399;'>- Merci de saisir un nom</b></span></div>
		<div style="margin-top:5px;">
			<input name="name" type="text" value='<?echo $form['TITLE'];?>'  id="name" style="width:350px;"/>
		</div>	
		<div class="clear"></div>
		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:175px;float:left;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
					<p><?
					if($action=='add'){echo 'Ajouter la chaîne';}
					else{ echo 'Modifier la chaîne';}
					?></p>
				</div>
			</div>	
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('video_categorie.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
		</div>
	</form>
</div>

<?	
}
else{
	
	
	$req_chaine=$db->query('select * from cms_video_categorie where ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_PUSH = 0 ORDER BY ORDRE ');
	$nb_chaine=mysql_num_rows($req_chaine);
	
	if($nb_chaine>0){				
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th width='80'>Ordre</th>
				<th width='80'>Modifier</th>
				<th width='80'>Supprimer</th>
			</tr>
			</thead>
		";
		$i=0;
		while($don=mysql_fetch_array($req_chaine)){
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
				
				echo "
			<tr  class='frst'>
				<td style='text-align:left;'>".$don['TITLE']."</td>
				<td>".genere_form_ordre('video_ordre','?action=ordre',1,$nb_chaine,$don['ORDRE'],1,array('ID'=>$don['ID']))."</td>												
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucune chaîne actuellement";
	}
	
}

include('footer.php');
?>