<?
$id_module=1;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

/*Ajout*/
if($action=='add_r'){
	if(isset($_POST['user_lname']) && $_POST['user_lname']!="" && isset($_POST['user_fname']) && $_POST['user_fname']!="" && isset($_POST['email']) && $_POST['email']!="" && isset($_POST['login']) && $_POST['login']!="" && isset($_POST['mdp']) && $_POST['mdp']!=""){
	
		$db->execute("INSERT INTO comm_admin_user (FNAME,LNAME,EMAIL,LOGIN,MDP,MD5,HASH) VALUES ('".addslashes($_POST['user_fname'])."','".addslashes($_POST['user_lname'])."','".addslashes($_POST['email'])."','".addslashes($_POST['login'])."','".addslashes($_POST['mdp'])."','".md5(addslashes($_POST['mdp']))."','')");
		
		$id_user=$db->lastInsertedId();
		while (list($key, $value) = each($_POST['droit'])) {
			$db->execute("INSERT INTO comm_module_user (ID_USER,ID_MODULE) VALUES (".$id_user.",".$value.") ");
		}		
		
		$_SESSION['notification'][]=array(1,"Utilisateurs","L'utilisateur a été créé.");
		header('location:user.php');	
		die();
	}else{
		$_SESSION['notification'][]=array(0,"Utilisateurs","Veuillez remplir tous les champs");
		$action='add';
	}
}
/*Modification*/
if($action=='edit_r'){
	if(isset($_POST['user_lname']) && $_POST['user_lname']!="" && isset($_POST['user_fname']) && $_POST['user_fname']!="" && isset($_POST['email']) && $_POST['email']!="" && isset($_POST['login']) && $_POST['login']!="" && isset($_POST['mdp']) && $_POST['mdp']!=""){
		$db->execute("UPDATE comm_admin_user SET FNAME = '".addslashes($_POST['user_fname'])."', LNAME = '".addslashes($_POST['user_lname'])."', EMAIL = '".addslashes($_POST['email'])."', LOGIN = '".addslashes($_POST['login'])."', MDP = '".addslashes($_POST['mdp'])."', MD5 = '".md5($_POST['mdp'])."' WHERE ID = ".$_POST['id']);
		$db->execute('DELETE FROM comm_module_user WHERE ID_USER = '.$_POST['id']);
		while (list($key, $value) = each($_POST['droit'])) {
			$db->execute("INSERT INTO comm_module_user (ID_USER,ID_MODULE) VALUES (".$_POST['id'].",".$value.") ");
		}	
		$_SESSION['notification'][]=array(1,"Utilisateurs","L'utilisateur a été modifié.");
		header('location:user.php');		
		die();
	}
	else{
		$_SESSION['notification'][]=array(0,"Utilisateurs","Veuillez remplir tous les champs");
		$action='edit';
		$_GET["id"]=$_POST['id'];
	}
}
/*Suppression*/
if($action=='delete'){
	if(isset($_GET['id'])){
		$db->execute("DELETE FROM comm_admin_user WHERE ID = ".$_GET['id']);
		$db->execute('DELETE FROM comm_module_user WHERE ID_USER = '.$_GET['id']);
		$_SESSION['notification'][]=array(1,"Utilisateurs","L'utilisateur a été supprimé.");
		header('location:user.php');		
		die();
	}
}

if($action=='')$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter un utilisateur','WIDTH'=>'200');
echo genere_sous_menu_admin($array_menu);

if($action=='add' || $action=='edit'){

	$form=array();
	$list_droit=array();
	if($action=='edit'){
		
		$req=$db->query('select * from comm_admin_user where ID = '.$_GET["id"]);
		$form=mysql_fetch_array($req);
		$form=array_map("format",$form);	
		
		$req_droit=$db->query('select * from comm_module_user where ID_USER = '.$_GET["id"]);
		
		while($don=mysql_fetch_array($req_droit)){
			$list_droit[]=$don['ID_MODULE'];
		}
	}

?>
	<script type="text/javascript">
	function verif_email(email){
		<? if ($form['EMAIL']!=""){
			echo "var old_email='".$form['EMAIL']."';
			if(email==old_email){
			}
			else{
			";
		}?>		
		 $.post("trouve_email.php",{email: email},
		   function success(data){
				if(data.libre==0)alert( "Email déjà pris " );
		   }, "json");
		<? if ($form['EMAIL']!=""){
		echo '}';
		}
		?>
	}
	function lance_pseudo(){
		$.post("genere_mdp.php",
		   function success(data){
				$('#mdp').val(data.mdp);
		   }, "json");
	}
	function toggle_check(){
		$("#zone_checked").checkCheckboxes(); 
	}		
	$(document).ready(function() {
		$("#step2_admin").hide();
		
	});
	
	function verif_user(){
		var error=0;
		if($('#user_lname').val()==''){
			$('#complete_all').show();
			$('#user_lname').css({"border":"1px solid #ADAC48"});
			error=1;
		}else{
			$('#complete_all').hide();
			$('#user_lname').css({"border":"1px solid #999999"});
		}
		
		if($('#user_fname').val()==''){
			$('#complete_all2').show();
			$('#user_fname').css({"border":"1px solid #ADAC48"});
			error=1;
		}else{
			$('#complete_all2').hide();
			$('#user_fname').css({"border":"1px solid #999999"});
		}
		
		var email_test=VerifMail($('#email').val());
		if(!email_test){
			$('#complete_all3').show();
			$('#email').css({"border":"1px solid #ADAC48"});
			error=1;
		}else{
			$('#complete_all3').hide();
			$('#email').css({"border":"1px solid #999999"});
		}
		
		if($('#login').val()==''){
			$('#complete_all4').show();
			$('#login').css({"border":"1px solid #ADAC48"});
			error=1;
		}else{
			$('#complete_all4').hide();
			$('#login').css({"border":"1px solid #999999"});
		}
		
		if($('#mdp').val()==''){
			$('#complete_all5').show();
			$('#mdp').css({"border":"1px solid #ADAC48"});
			error=1;
		}else{
			$('#complete_all5').hide();
			$('#mdp').css({"border":"1px solid #999999"});
		}
		
		return error;
	}
	num_step_cours=1;
	function launch_step(num_step){
		if(num_step==1){
			$('#step'+num_step_cours+'_admin').hide();
			num_step_cours=1;
			$('#step'+num_step_cours+'_admin').show();
			$('#etape_name').html('Etape 1/2');
		}
		else if(num_step==2){
			var res_test=verif_user();
			if(!res_test){
				$('#step'+num_step_cours+'_admin').hide();
				num_step_cours=2;
				$('#step'+num_step_cours+'_admin').show();
				$('#etape_name').html('Etape 2/2');
			}
		}
	}
	function valide_form(){
		document.forms["form_user"].submit();	
	}
	</script>
	<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Etape 1/2</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/install_step_3.jpg'></div>
	</div>
	<div class="clear"></div>
	<div id="step1_admin" >
	<form method="post"  name="form_user" id="form_user" action="user.php?action=<?echo $action;?>_r">
		<input type='hidden' name='id' value='<?echo $form['ID'];?>'>
		<div style='margin-top:10px;'>Nom de l'utilisateur <span id="complete_all" style="display:none;"><b style='color:#ADAC48;'>- Merci de saisir un nom</b></span></div>
		<div style='margin-top:5px;'>
			<input name="user_lname" type="text" value='<?echo $form['LNAME'];?>'   id="user_lname" style="width:350px;"/>
		</div>
		<div style='margin-top:10px;'>Prénom de l'utilisateur <span id="complete_all2" style="display:none;"><b style='color:#ADAC48;'>- Merci de saisir un prénom</b></span></div>
		<div style='margin-top:5px;'>
			<input name="user_fname" type="text"  value='<?echo $form['FNAME'];?>'  id="user_fname" style="width:350px;"/>
		</div>
		<div style='margin-top:10px;'>Email <span id="complete_all3" style="display:none;"><b style='color:#ADAC48;'>- Merci de saisir un email valide</b></span></div>
		<div style='margin-top:5px;'>
			<input name="email" type="text"  value='<?echo $form['EMAIL'];?>' onchange="verif_email(this.value);" id="email" style="width:350px;"/>
		</div>
		<div style='margin-top:10px;'>Login <span id="complete_all4" style="display:none;"><b style='color:#ADAC48;'>- Merci de saisir un login</b></span></div>
		<div style='margin-top:5px;'>
			<input name="login" type="text"  value='<?echo $form['LOGIN'];?>' id="login" />
		</div>
		<div style='margin-top:10px;'>Mot de passe <span id="complete_all5" style="display:none;"><b style='color:#ADAC48;'>- Merci de saisir un mot de passe</b></span></div>
		<div style='margin-top:5px;'>
			<input name="mdp" type="text" maxlength="50" value='<?echo $form['MDP'];?>'   id="mdp" />  <a href="javascript:lance_pseudo();">Générer un mot de passe</a>
		</div>
		<div class="clear"></div>

		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:launch_step(2);">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
			</div>
			<?if($action=='edit'){?>
				<div class="bordure_menu" style='width:195px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
					<div style='width:195px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
						<p>Modifier l'utilisateur</p>
					</div>
				</div>
			<?}?>
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('user.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>			
			
		</div>
	</div>
	<div class="clear"></div>
	<div id="step2_admin" >
		<div style='margin-top:10px;'>Ses droits</div>
		<div style='margin-top:5px;' id="zone_checked">
			<a href="javascript:toggle_check();">Cocher tous les droits</a><br/><br/>
			<?
			$req=$db->query("select * from comm_module order by LIBELLE ASC");
			while($don=mysql_fetch_array($req)){
				if($don['NO_ACTIVE']==1){
					if(in_array($don['ID'],$list_droit))$sel='checked="checked"';
					else $sel='';
					echo '<div style="padding:0px;margin-top:5px; line-height: 20px;"><input type="checkbox" name="droit[]" value="'.$don['ID'].'" '.$sel.'> '.$don['LIBELLE'].'</div>';
				}
			}
			?>
			
		</div>
		<div class="clear"></div>

		<div style="margin-top:35px;">	
			<div class="bordure_menu" style='width:175px;float:left;' onclick="javascript:launch_step(1);">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
			</div>	
			<div class="bordure_menu" style='width:175px;float:left;margin-left:30px;' onclick="javascript:valide_form();">
				<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style">
					<p><?
					if($action=='add'){echo 'Ajouter l\'utilisateur';}
					else{ echo 'Modifier l\'utilisateur';}
					?></p>
				</div>
			</div>	
			<div class="bordure_menu" style='width:155px;float:left;margin-left:30px;' onclick="redir('user.php');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>				
			
		</div>
	</div>
	</form>
<?	
}
else{
?>
<script>
$(document).ready(function(){
	<? echo auto_help("Utilisateurs","En tant qu’Administrateur de votre site, vous pouvez, si vous le désirez, donner la possibilité à l’un ou plusieurs de vos collaborateurs de gérer toute ou partie de votre site.<br/>Vous pouvez sélectionner les fonctions auxquelles il a un droit d’utilisation.<br/>Vous pouvez à tout moment revenir sur ses droits.");?>
	$(".iframe").fancybox({
		'width'				: 300,
		'height'			: 350,
		'autoScale'			: false,
		'transitionIn'		: 'none',
		'transitionOut'		: 'none',
		'type'				: 'iframe'
	});
});
</script>
<?
	$req=$db->query("select * from comm_admin_user order by LNAME,FNAME ASC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<div class='clear'></div>
		<div style='height:15px;width:100%;'></div>
		<b>Liste des utilisateurs</b>
		<div style='height:15px;width:100%;'></div>
		<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Prénom Nom</th>
				<th width='85'>Login</th>
				<th width='85'>Ses droits</th>
				<th width='85'>Modifier</th>
				<th width='85'>Supprimer</th>
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
				<td class='frst'>".$don['FNAME']." ".$don['LNAME']."</td>
				<td>".$don['LOGIN']."</td>
				<td><a class='iframe' href='list_droit.php?id=".$don['ID']."' style='text-decoration:none;'><img src='".URL_DIR."/images/display.png' align='absmiddle'></a></td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/edit.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "Aucun utilisateur actuellement";
	}
}
?>

<?
include('footer.php');
?>