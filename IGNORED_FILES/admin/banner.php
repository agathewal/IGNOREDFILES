<?
$id_module=25;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['filepath']) && $_POST['filepath']!=""){
	
		$db->execute("INSERT INTO cms_banner (URL) VALUES ('".addslashes($_POST['filepath'])."')");
		$_SESSION['notification'][]=array(1,"Bannières","La bannière a été ajoutée.");
		header('location:banner.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Bannières","Veuillez remplir tous les champs");
		$action='add';
	}
}

/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		$db->execute("DELETE FROM cms_banner WHERE ID = ".$_GET['id']);
		$_SESSION['notification'][]=array(1,"Bannières","La bannière a été supprimée.");
		header('location:banner.php');		
		die();
	}
}

/*Sélection*/
if($action=='select' && isset($_GET['id']) && is_numeric($_GET['id'])){

	$db->execute("UPDATE cms_banner SET SELECTED = 0");	
	$db->execute("UPDATE cms_banner SET SELECTED = 1 WHERE `ID` = ".$_GET['id']);
	$_SESSION['notification'][]=array(1,"Bannières","La bannière sélectionnée pour la page d'accueil a été modifiée.");	
	header('location:banner.php');	
	die();		
}


if($action=='') $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter une bannière','WIDTH'=>'180');
echo genere_sous_menu_admin($array_menu);?>
<?
if($action=='add'){


?>
	<script>
	function valide_form(){
		document.forms['form_user'].submit();	
			
	}
	</script>
	<div id="form">
	<h1>Ajouter une bannière</h1>
	</div>
	<form method="post"  name='form_user' id="form_user" action="banner.php?action=<?echo $action;?>_r">
		<div style="margin-top:10px;">Bannière :</div>
		<div style='margin-top:5px;width:275px;'>
			<?
			$dir_upload2=ADD_DIR.'/medias/banner/';
			echo form_picture(960,250,'filepath',$form['VIGNETTE'],$dir_upload2,"Ajouter<br/> une bannière",array(
		"THUMB_WIDTH"=>150,
		"THUMB_HEIGHT"=>35,
		"TEXT_WIDTH"=>100,
		"TOTAL_WIDTH"=>273,
		"TOTAL_HEIGHT"=>68));
			?>			
		<div class="clear"></div>
		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:175px;float:left;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
					<p><?
					echo 'Ajouter la bannière';
					?></p>
				</div>
			</div>	
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('banner.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
		</div>
	</form>
<?	
}
else{

	$req=$db->query("select * from cms_banner ORDER BY URL ASC");
	echo "<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>";
	if(mysql_num_rows($req)!=0){
		
		echo "
		
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Bannière</th>
				<th >Bannière de la page d'accueil</th>
				<th >Supprimer</th>
			</tr>
			</thead>
		";
		
		$i=0;
		while($don=mysql_fetch_array($req)){
			
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			
			if($don['SELECTED']==1)$selec="<img src='".URL_DIR."/images/star_on.png'>";
			else $selec="<a href='?action=select&id=".$don['ID']."'><img src='".URL_DIR."/images/star_off.png'></a>";
			
			
			$i++;
			
			echo"
			<tr ".$style_table.">
				<td  class='frst'><img src='get-adaptative-thumb.php?url=".urlencode($don['URL'])."&width=300&height=80'></td>
				<td>".$selec."</td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "Aucune bannière actuellement";
	}
}
?>

<?
include('footer.php');
?>