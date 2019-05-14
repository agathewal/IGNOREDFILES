<?
$id_module=28;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['email']) && $_POST['email']!="" && isset($_POST['name']) && $_POST['name']!=""){
	
		$db->execute("INSERT INTO cms_agenda (NAME,EMAIL,ID_UNIVERS,ID_LANG) VALUES ('".addslashes($_POST['name'])."','".addslashes($_POST['email'])."',".$_SESSION['univers'].",".$_SESSION['langue'].")");
		$_SESSION['notification'][]=array(1,"Agenda","L'agenda a été ajouté.");
		header('location:agenda.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Agenda","Veuillez remplir tous les champs");
		$action='add';
	}
}

/*Modif*/

if($action=='edit_r'){
	if(isset($_POST['email']) && $_POST['email']!="" && isset($_POST['name']) && $_POST['name']!=""){
	
		$db->execute("UPDATE cms_agenda SET NAME = '".addslashes($_POST['name'])."', EMAIL = '".addslashes($_POST['email'])."' WHERE ID = ".$_POST['id']);
		$_SESSION['notification'][]=array(1,"Agenda","L'agenda a été modifié.");
		header('location:agenda.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Agenda","Veuillez remplir tous les champs");
		$action='edit';
		$_GET['id']=$_POST['id'];
	}
}


/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		$db->execute("DELETE FROM cms_agenda WHERE ID = ".$_GET['id']);
		$_SESSION['notification'][]=array(1,"Agenda","L'agenda a été supprimé.");
		header('location:agenda.php');		
		die();
	}
}

if($action=='') $array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter un agenda','WIDTH'=>'180');
echo genere_sous_menu_admin($array_menu);?>
<?
if($action=='add' || $action=='edit'){

	$form=array();
	if($action=='edit'){
		
		$req=$db->query('select * from cms_agenda where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
		
	}
?>
	<script>
	function valide_form(){
		
		var error_form=0;
		if($('#name').val()==''){
			$('#name').css({"border":"1px solid #333399"});
			$('#complete_all').show();
			error_form=1;
		}else{
			$('#name').css({"border":"1px solid #999999"});
			$('#complete_all').hide();
		}
		
		
		var email_test=VerifMail($('#email').val());
		if(!email_test){
			$('#email').css({"border":"1px solid #333399"});
			$('#complete_all2').show();
			error_form=1;
		}else{
			$('#email').css({"border":"1px solid #999999"});
			$('#complete_all2').hide();
		}
		
		if(!error_form){
			document.forms['form_user'].submit();	
		}	
	}
	</script>
	<div id="form">
	<h1><?if($action=='add'){echo 'Ajouter';}else{ echo 'Modifier';}?> un agenda</h1>
	</div>
	<form method="post"  name='form_user' id="form_user" action="agenda.php?action=<?echo $action;?>_r">
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div style="margin-top:10px;">Nom de l'agenda : <span id="complete_all" style="display:none;"><b style='color:#333399;'>- Merci de saisir un nom</b></span></div>
		<div style='margin-top:5px;'>
			<input name="name" type="text" value='<?echo $form['NAME'];?>' id="name" style="width:350px;" />
		</div>	
		<div style="margin-top:10px;">Email utilisé pour l'agenda : <span id="complete_all2" style="display:none;"><b style='color:#333399;'>- Merci de saisir un email</b></span></div>
		<div style='margin-top:5px;'>
			<input name="email" type="text" value='<?echo $form['EMAIL'];?>' id="email" style="width:350px;" />
		</div>		
		<div class="clear"></div>
		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:175px;float:left;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_<?if($action=='add'){echo 'add';}else{ echo 'edit';}?>.png");' class="btn_style">
					<p><?if($action=='add'){echo 'Ajouter l\'agenda';}else{ echo 'Modifier l\'agenda';}?></p>
				</div>
			</div>	
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('agenda.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
		</div>
	</form>
<?	
}
else{
?>
<script type='text/javascript'>
$(document).ready(function(){
	$(".see_google").fancybox({
		'width':850,
		'height':620,
		'autoDimensions':false,
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'ajax',
		'modal' : false
	});
});
</script>
<?
	$req=$db->query("select * from cms_agenda ORDER BY NAME ASC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>
		Liste des agendas Google
		<div style='height:15px;width:100%;'></div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Nom</th>
				<th  width='100'>Voir</th>
				<th  width='100'>Modifier</th>
				<th  width='100'>Supprimer</th>
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
				<td class='frst'>".stripslashes($don['NAME'])."</td>
				<td><a href='see_agenda.php?email=".urlencode($don['EMAIL'])."' class='see_google'><img src='".URL_DIR."/images/display.png'></a></td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'></div>Aucun agenda actuellement";
	}
}
?>

<?
include('footer.php');
?>