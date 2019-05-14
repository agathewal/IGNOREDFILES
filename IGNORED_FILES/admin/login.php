<?php
$no_menu=1;
$no_bg_body=1;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if(isset($_GET['logout']) && $_GET['logout']==1){
	Login::log_User_Out();
}

$success_forgot=0;
if(isset($_POST['forgot_username'])){
	$don_user=$db->query("select * from comm_admin_user where (LOGIN = '".addslashes($_POST['forgot_username'])."' OR EMAIL = '".addslashes($_POST['forgot_username'])."' ) LIMIT 0,1");
	if(mysql_num_rows($don_user)!=0){
		$info=mysql_fetch_array($don_user);
		require_once("../config/class.phpmailer.php");
		$forgot_password=1;
		$smarty = new Smarty();					
		$smarty->template_dir= SMARTY_TPL_DIR;
		$smarty->compile_dir= SMARTY_COMPILE_DIR;
		$smarty->config_dir= SMARTY_CONFIG_DIR;
		$smarty->cache_dir= SMARTY_CACHE_DIR;					
		$smarty->caching = SMARTY_CACHE;						
		$smarty->assign('info',$info);
		$email_html=$smarty->fetch(ROOT_DIR.'tpl/email_forgot.tpl');	
		$mail= new PHPMailer();
		if(LOCAL_DEV){
			$mail->IsSMTP(); // telling the class to use SMTP
			$mail->Host      = "smtp.orange.fr";
		}
		$mail->CharSet       = "utf-8";
		$mail->SMTPAuth      = false;                  // enable SMTP authentication
		$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
		$mail->SetFrom('noreply@nexinet.fr', 'Système de gestion de contenu');		
		$mail->AddAddress(stripslashes($info['EMAIL']), stripslashes($info['FNAME'].' '.$info['LNAME']));		
		$mail->Subject       ='Confidentiel - Rappel de votre mot de passe';
		$mail->MsgHTML($email_html);
		$mail->Send();
	}else{
		$retour='"Veuillez saisir un login/email correct";';
	}
}

if($_SESSION['logged']==1 && is_numeric($_SESSION['id'])){
	header('location:home.php');
	die();
}



?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
<script>
$(function(){
	$("#zone_login,.btn_style2").corner("5px").parent().css('padding', '1px').corner("5px");
	<?
	if($retour!=''){
	?>
	$("#zone_login").parent().effect("shake", { times:3 }, 200);
	<?}?>
	
	$('#form_login').keypress(function(e){
		if(e.which == 13){
			document.forms["login_form"].submit();
		}
    });

	  
});

</script>
<form method="post" action="" name="login_form" id="form_login">    	
<div class="bordure_menu" style="width:400px;margin-top:70px;margin-left:250px;">
	<div style='width:400px;background-color:#ffffff;background-image:url("../images/fond_login.jpg");background-repeat:repeat-x;padding-bottom:10px;' id="zone_login">
		<div style='height:80px;width:100%;'>
			<div style='float:left;width:80px;margin-top:25px;margin-left:25px;display:inline;'><img src="<?echo URL_DIR;?>/images/logo_nexi.jpg"></div>
			<div style='float:left;width:250px;margin-top:35px;margin-left:15px;'><h1><?echo WEBSITE_NAME;?></h1></div>
		</div>
		<div class="clear"></div>
		<?if($action==''){?>
		<div style="float:left;width:290px;margin-top:30px;margin-left:25px;display:inline;">
			<div>Votre adresse email ou votre identifiant</div>
			<div style="margin-top:10px;">
				<input type="text" style='width:290px;' name="admin_username" id="admin_username">
			</div>
			<div style="margin-top:15px;">Votre mot de passe</div>
			<div style="margin-top:10px;">
				<input type="password" style='width:290px;' name="admin_pwd" id="admin_pwd">
			</div>
			<div style="margin-top:15px;">Langue administrée</div>
			<div style="margin-top:10px;">
				<select name="admin_langue">
				<?
				$req=$db->query('SELECT * FROM comm_lang ORDER BY comm_lang.ORDRE ASC');
				if(mysql_num_rows($req)!=0){
					while($don=mysql_fetch_array($req)){
						
						echo '<option value="'.$don['ID'].'">'.stripslashes($don['LANG']).'</option>';
					}					
				}
				?>
				</select>
			</div>
		</div>	
		<div style='float:left;width:50px;margin-top:20px;margin-left:25px;display:inline;'>
			<img src='<?echo URL_DIR;?>/images/login_btn.jpg'>
		</div>
		<div class="clear"></div>
		<div style='width:100%;margin-top:25px;'>
			<div style="margin-left:25px;float:left;display:inline;" onclick='javascript:document.forms["login_form"].submit();'>
				<div class="bordure_menu" style='width:125px;' >
					<div style='width:125px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Se connecter</p></div>
				</div>
			</div>
			<?
			if($retour!=''){
			?>
			<div class="bordure_menu" style='width:220px;float:left;margin-left:10px;'>
				<div style='width:220px;background-image:url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg");color:#FFFFFF;font-size:11px;font-weight:bold;' class="btn_style2">
					<p style='margin-left:10px;padding-top:5px;'>Mot de passe incorrect !</p>					
				</div>
			</div>
			<?}?>
		</div>
		<div class="clear"></div>
		<div style="margin-top:10px;margin-left:25px;">
			<a href="?action=forgot_password">Mot de passe oublié ?</a>
		</div>
		<?}else{
		if($forgot_password==1){
		?>
		<div style="float:left;width:290px;margin-top:30px;margin-left:25px;display:inline;">
			<div>Un email vous a été envoyé avec votre mot de passe.</div>
		</div>	
		<div style='float:left;width:50px;margin-top:20px;margin-left:25px;display:inline;'>
			<img src='<?echo URL_DIR;?>/images/login_btn.jpg'>
		</div>
		<div class="clear"></div>
		<div style='width:100%;margin-top:25px;'>
			<div style="margin-left:25px;float:left;display:inline;" onclick="javascript:redir('login.php');">
				<div class="bordure_menu" style='width:200px;' >
					<div style='width:200px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>S'identifier à nouveau</p></div>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<?
		}else{		
		?>
		<div style="float:left;width:290px;margin-top:30px;margin-left:25px;display:inline;">
			<div>Saisissez votre adresse email ou votre identifiant</div>
			<div style="margin-top:10px;">
				<input type="text" style='width:290px;' name="forgot_username" id="forgot_username">
			</div>
		</div>	
		<div style='float:left;width:50px;margin-top:20px;margin-left:25px;display:inline;'>
			<img src='<?echo URL_DIR;?>/images/login_btn.jpg'>
		</div>
		<div class="clear"></div>
		<div style='width:100%;margin-top:25px;'>
			<div style="margin-left:25px;float:left;display:inline;" onclick='javascript:document.forms["login_form"].submit();'>
				<div class="bordure_menu" style='width:200px;' >
					<div style='width:200px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>M'envoyer le mot de passe</p></div>
				</div>
			</div>
			<?
			if($retour!=''){
			?>
			<div class="clear"></div>
			<div class="bordure_menu" style='width:220px;margin-top:10px;margin-left:25px;'>
				<div style='width:220px;background-image:url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg");color:#FFFFFF;font-size:11px;font-weight:bold;' class="btn_style2">
					<p style='margin-left:10px;padding-top:5px;'>Identifiant/Email introuvable !</p>					
				</div>
			</div>
			<?}?>
		</div>
		<div class="clear"></div>
		<?}
		}?>		
	</div>
</div>
</form>
<?
include('footer.php');
?>