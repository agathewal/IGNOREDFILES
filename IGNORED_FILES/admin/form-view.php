<?
require_once('../config/configuration.php');
require_once('../config/class.upload.php');
require_once("../config/class.phpmailer.php");
ob_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
	<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery-1.5.min.js"></script>
	<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.tools.min.js"></script>
	<script type="text/javascript" src="<? echo URL_DIR;?>/js/fct.js"></script>	
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/uidatepickerfr.js"></script>
	<link rel="stylesheet" href="<?echo URL_DIR;?>/css/base/jquery.ui.all.css" type="text/css" media="screen" charset="utf-8" />
	<link rel="stylesheet" href="<?echo URL_DIR;?>/css/tooltip-generic.css" type="text/css" media="screen" charset="utf-8" />
	<style type="text/css">
	body{
		background-image:none;
		background-color:#FFF;
	}
	</style>
</head>
<body>
<div id="container">
	<div id="content" class="wrapper">
		<div style="padding:0 0 20px;width:550px;">
<?
if(is_numeric($_GET['id'])){
	
	if(isset($_GET['action']))$action=$_GET['action'];
	else $action='';

	$req=$db->query('select * from cms_formulaire where ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		$form_info=mysql_fetch_array($req);
		if($form_info['ACTIF']==0){
			echo '<h1><b>Le formulaire est inactif  !</b>';
			echo"
					</div>
				</div>
			</div>
			</body>
			</html>";
			die();
		}
		$nb_record=$db->countOfall('cms_formulaire_data_'.$_GET['id']);
		if($form_info['LIMITATION']<=$nb_record && $form_info['LIMITATION']!=0){
			echo stripslashes($form_info['FULL_MSG']);
			echo"
					</div>
				</div>
			</div>
			</body>
			</html>";
			die();
		}
		
		$form_info=array_map("format",$form_info);
		if($form_info['LIMITATION']==0){
			$form_info['LIMITATION']='';
		}
	
		if($action=='valid'){
			//pr($_POST);
			
			//vérification des champs obligatoire ! 
			$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' AND REQUIRED = 1 ORDER BY `ORDRE`');
			$list_champ_obligatoire=array();
			if(mysql_num_rows($req_elements)!=0){
				while($don=mysql_fetch_array($req_elements)){
					
					$empty=0;
					switch($don['TYPE']){
						case 1://text
						if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
						break;
						case 2://textarea
						if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
						break;
						case 3://radio
						if(!isset($_POST['field_'.$don['ID']]) || !is_numeric($_POST['field_'.$don['ID']]))$empty=1;
						break;
						case 4://checkbox
						if(!isset($_POST['field_'.$don['ID']]) || count($_POST['field_'.$don['ID']])==0)$empty=1;
						break;
						case 5://select
						if(!isset($_POST['field_'.$don['ID']]) || !is_numeric($_POST['field_'.$don['ID']]))$empty=1;
						break;
						case 6://nom prénom
						if(!isset($_POST['field_fname_'.$don['ID']]) || $_POST['field_fname_'.$don['ID']]=='' || !isset($_POST['field_lname_'.$don['ID']]) || $_POST['field_lname_'.$don['ID']]=='')$empty=1;
						break;
						case 7://adresse
						if(!isset($_POST['field_adress_street_'.$don['ID']]) || $_POST['field_adress_street_'.$don['ID']]=='' || !isset($_POST['field_adress_zip_'.$don['ID']]) || $_POST['field_adress_zip_'.$don['ID']]=='' || !isset($_POST['field_adress_town_'.$don['ID']]) || $_POST['field_adress_town_'.$don['ID']]=='')$empty=1;
						break;
						case 8://téléphone
						if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
						break;
						case 9://date						
						if(!isset($_POST['field_date_'.$don['ID']]) || $_POST['field_date_'.$don['ID']]=="")$empty=1;
						else{
							list($jour, $mois, $annee) = explode('/', $_POST['field_date_'.$don['ID']]);
							if(!checkdate($mois,$jour,$annee))$empty=1; 
						}
						break;
						case 10://www
						if(!isset($_POST['field_'.$don['ID']]) || !verif_url($_POST['field_'.$don['ID']]))$empty=1;
						break;
						case 11://email
						if(!isset($_POST['field_'.$don['ID']]) || !VerifierAdresseMail($_POST['field_'.$don['ID']]))$empty=1;
						break;
						case 12://fichier
						$handle = new Upload($_FILES['field_'.$don['ID']]);
						if (!$handle->uploaded || $handle->file_src_name=='')$empty=1;
						break;
						
					}
					
					if($empty)$list_champ_obligatoire[]=$don['ID'];
				}
			}
			//pr($list_champ_obligatoire);
			if(count($list_champ_obligatoire)==0){
				$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' ORDER BY `ORDRE`');
				if(mysql_num_rows($req_elements)!=0){
					$req_values='';
					$req_insert='';
					$req_email='';
					$_POST=multidimensionalArrayMap('stripslashes',$_POST);
					$i=0;
					while($don=mysql_fetch_array($req_elements)){
						if($i!=0){
							$req_values.=',';
							$req_insert.=',';
						}
						$req_insert.='`FIELD_'.$don['ID'].'`';
						$req_email.='
						<tr>
							<td style="font-family: Arial; font-size:14px; line-height:19px; text-align:left; color:#666; font-weight:bold;">'.$don['NAME'].'</td>
							<td style="font-family: Arial; font-size:14px; line-height:19px; text-align:left; color:#666;">
						';
						
						if(!isset($_POST['field_'.$don['ID']]))$_POST['field_'.$don['ID']]='';
						
						switch($don['TYPE']){
							case 1://text
							$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
							$req_email.=$_POST['field_'.$don['ID']];
							break;
							
							case 2://textarea
							$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
							$req_email.=$_POST['field_'.$don['ID']];

							break;
							
							case 3://radio
							$req_value_opt='';
							if(is_numeric($_POST['field_'.$don['ID']])){
								$req_value_opt=$db->queryUniqueValue('SELECT NAME FROM cms_formulaire_champ_option WHERE ID_ELEMENT= '.$don['ID'].' AND ORDRE = '.$_POST['field_'.$don['ID']]);
							}
							$req_values.="'".addslashes($req_value_opt)."'";
							$req_email.=$req_value_opt;

							break;
							
							case 4://radio
							$req_value='';
							if(count($_POST['field_'.$don['ID']])>0){
								$j=0;
								foreach ($_POST['field_'.$don['ID']] as $value){
									//echo $value;
									$req_value_opt=$db->queryUniqueValue('SELECT NAME FROM cms_formulaire_champ_option WHERE ID_ELEMENT= '.$don['ID'].' AND ORDRE = '.$value);
									if($j!=0)$req_value.='<||>';
									$req_value.=$req_value_opt;
									$req_email.=$req_value_opt."<br>";
									$j++;
								}
							}
							$req_values.="'".addslashes($req_value)."'";
							break;
							
							
							case 5://multiple
							$req_value_opt='';
							if(is_numeric($_POST['field_'.$don['ID']])){
								$req_value_opt=$db->queryUniqueValue('SELECT NAME FROM cms_formulaire_champ_option WHERE ID_ELEMENT= '.$don['ID'].' AND ORDRE = '.$_POST['field_'.$don['ID']]);
							}
							$req_values.="'".addslashes($req_value_opt)."'";
							$req_email.=$req_value_opt;
							break;

							case 6://nom prénom
							$req_values.="'".addslashes($_POST['field_fname_'.$don['ID']].'<||>'.$_POST['field_lname_'.$don['ID']])."'";
							$req_email.=$_POST['field_fname_'.$don['ID']].' '.$_POST['field_lname_'.$don['ID']];
							break;

							case 7://adresse
							$req_values.="'".addslashes($_POST['field_adress_street_'.$don['ID']].'<||>'.$_POST['field_adress_street2_'.$don['ID']].'<||>'.$_POST['field_adress_zip_'.$don['ID']].'<||>'.$_POST['field_adress_town_'.$don['ID']])."'";
							$req_email.=$_POST['field_adress_street_'.$don['ID']].'<br/>'.$_POST['field_adress_street2_'.$don['ID']].'<br/>'.$_POST['field_adress_zip_'.$don['ID']].' '.$_POST['field_adress_town_'.$don['ID']];
							break;
							
							case 8://téléphone
							$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
							$req_email.=$_POST['field_'.$don['ID']];
							break;
							
							case 9://date
							if(isset($_POST['field_date_'.$don['ID']])){
								$temp=explode('/',$_POST['field_date_'.$don['ID']]);
								//pr($temp);
								$time_deb=mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
							}
							else $time_deb=0;
							$req_values.="'".$time_deb."'";
							$req_email.=$_POST['field_date_'.$don['ID']];
							break;
							
							case 10://www
							$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
							$req_email.=$_POST['field_'.$don['ID']];
							break;
							
							case 11://email
							$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
							$req_email.=$_POST['field_'.$don['ID']];
							break;
							
							case 12://fichier
							$req_value='';
							$handle = new Upload($_FILES['field_'.$don['ID']]);
							if ($handle->uploaded){		
								$handle->file_new_name_body=file_name_format($_FILES['field_'.$don['ID']]['name']);
								$handle->Process('../upload/');
								if($handle->processed){
									$req_value=$handle->file_dst_name;
								}
							}
							$req_values.="'".addslashes($req_value)."'";
							if($req_value!='')$req_email.='<a href="'.URL_DIR."/upload/".$req_value.'" style="color:#2f81ac; text-decoration:underline;"><strong>Télécharger le fichier</strong></a>';
							else $req_email.='';
							break;
							
						}
						$i++;
						
						$req_email.='
							</td>
						</tr>
						<tr>
							<td colspan="2"><img src="'.URL_DIR.'/images/hr-small.gif" width="614" height="11" alt="" /></td>
						</tr>
						';
					}
					$req_final='INSERT INTO cms_formulaire_data_'.$_GET['id'].' ('.$req_insert.',DT_CREATE,IP) VALUES ('.$req_values.','.time().',\''.addslashes(getIp()).'\')';
					$db->execute($req_final);
					ecrire_log('Formulaire',addslashes($req_final));
					
					
					//echo $email_html;					

					$req_emails=$db->query('select EMAIL FROM cms_formulaire_email WHERE ID_FORM = '.$_GET['id']);
					if(mysql_num_rows($req_emails)!=0){
											
						$smarty = new Smarty();					
						$smarty->template_dir= SMARTY_TPL_DIR;
						$smarty->compile_dir= SMARTY_COMPILE_DIR;
						$smarty->config_dir= SMARTY_CONFIG_DIR;
						$smarty->cache_dir= SMARTY_CACHE_DIR;					
						$smarty->caching = SMARTY_CACHE;						
						$smarty->assign('form_name',$form_info['NAME']);
						$smarty->assign('valeur',$req_email);
						$email_html=$smarty->fetch(ROOT_DIR.'tpl/email_formulaire.tpl');
					
						$mail= new PHPMailer();
						if(LOCAL_DEV){
							$mail->IsSMTP(); // telling the class to use SMTP
							$mail->Host      = "smtp.orange.fr";
						}
						$mail->CharSet       = "utf-8";
						$mail->SMTPAuth      = false;                  // enable SMTP authentication
						$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent
						$mail->SetFrom('noreply@nexinet.fr', 'Système de gestion de contenu');				
						while($don_email=mysql_fetch_array($req_emails)){
							//pr($don_email);
							$mail->AddAddress($don_email['EMAIL'], $don_email['EMAIL']);
						}
						$mail->Subject       = htmlspecialchars_decode(stripslashes($form_info['NAME']),ENT_QUOTES).' - nouvel enregistrement';
						$mail->MsgHTML($email_html);
						$mail->Send();
						
					}
					//mail('ccaron.smartlink@gmail.com', htmlspecialchars_decode(stripslashes($form_info['NAME']),ENT_QUOTES).' - nouvel enregistrement', $email_html, $headers);

					echo stripslashes($form_info['SUCCESS']);
					echo"
							</div>
						</div>
					</div>
					</body>
					</html>";
					die();
				}
			}
		}
		
		$jquery_date='';
		$html_tablo="
		<form method='post' action='?action=valid&id=".$_GET['id']."' enctype='multipart/form-data' id='form_view'>
		<h1><b>".$form_info['NAME']."</b></h1>
		<p>".mynl2br($form_info['DESC'])."</p>
		";
		
		if(count($list_champ_obligatoire)>0){
			$html_tablo.="
		<br/>
		<div style='background-color:#FFDFDF;width:100%;height:40px;padding:5px;'>
		<b>Attention <br/></b>
		Certains champs obligatoires ne sont remplis. <span style='background-color:#F9F496;'>Ils ont été mis en évidence.</span>
		</div>	
		";
		}
		
		
		
		$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' ORDER BY `ORDRE`');
		if(mysql_num_rows($req_elements)!=0){
			while($don=mysql_fetch_array($req_elements)){	

				
				if(isset($_POST['field_'.$don['ID']]) && $_POST['field_'.$don['ID']]!="")$don['DEFAULT']=$_POST['field_'.$don['ID']];
				if($don['DEFAULT']=='null')$don['DEFAULT']='';
				
				$don=multidimensionalArrayMap("format",$don);
					
				if($don['REQUIRED']==1)$req_stat='';
				else $req_stat=' nosee';
				
				$color='';
				if($action=='valid' && in_array($don['ID'],$list_champ_obligatoire)){
					$color='background-color:#F9F496;';
				}
				
				if($don['HELP']!=""){
					$help='class="help_tool" title="'.$don['HELP'].'"';
				}
				else $help='';
				
				$html_tablo.='				
				<br/>
				<div style="padding:3px;'.$color.'"  '.$help.'>
				<div class="label_field clear">'.$don['NAME'].'<span class="required_field'.$req_stat.'"> * </span></div><br/>				
				';
				$required_js='';
			
				switch($don['TYPE']){			
					
					case 1:	//text									
					switch($don['SIZE']){
						case 1:
						$width_text='width:25%;';
						break;
						case 2:
						$width_text='width:50%;';
						break;
						case 3:
						$width_text='width:100%;';
						break;
					}
					$html_tablo.='<input type="text"  style="width:'.$width_text.'" name="field_'.$don['ID'].'"  value="'.$don['DEFAULT'].'">
					<div class="clear"></div>';
					break;
					
					case 2:	//textarea					
					switch($don['SIZE']){
						case 1:
						$width_text='height:5.5em;';
						break;
						case 2:
						$width_text='height:10em;';
						break;
						case 3:
						$width_text='height:20em;';
						break;
					}					
					
					$html_tablo.='<textarea class="large_text" name="field_'.$don['ID'].'">'.$don['DEFAULT'].'</textarea>
					<div class="clear"></div>';
					break;
					
					case 3://radio					
					$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');		
					$j=0;
					
					while($don_opt=mysql_fetch_array($req_options)){
						if($don['DEFAULT']==$j && $don['DEFAULT']!='')$required='checked="checked"';
						else $required='';
						
						$html_tablo.='
						<div style="height:20px;">
							<div style="float:left;width:10%;"><input type="radio" value="'.$j.'" name="field_'.$don['ID'].'" '.$required.'></div>
							<div style="float:left;width:90%;"><label style="font-size:12px;">'.$don_opt['NAME'].'</label></div>
						</div>
						<div class="clear"></div>
						';	
						$j++;
					}					
					break;
					
					case 4:	//checkbox				
					$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
					$j=0;
					
					if($action=='valid'){
						$list_check=array();
						if(isset($_POST['field_'.$don['ID']])){
							foreach ($_POST['field_'.$don['ID']] as $value){
								$list_check[]=$value;
							}	
						}
					}
					
					while($don_opt=mysql_fetch_array($req_options)){					
						$sel='';
						
						if($action=='valid'){
							if(in_array($j,$list_check))$sel='checked="checked"';
						}
						else if($don_opt['DEFAULT']==1)$sel='checked="checked"';
										
						
						$html_tablo.='
						<div style="height:30px;">
						<div style="float:left;"><input type="checkbox" value="'.$j.'" name="field_'.$don['ID'].'[]" '.$sel.'></div>
						<div style="float:left;"><label style="font-size:12px;">'.$don_opt['NAME'].'</label></div>
						<div class="clear"></div>
						</div>
						';
						$j++;
					}				
					
					break;
					
					case 5:	//select	
					$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
					$j=0;
					$html_tablo.='<select name="field_'.$don['ID'].'">';
					while($don_opt=mysql_fetch_array($req_options)){
					
						if($don['DEFAULT']==$j)$required='selected="selected"';
						else $required='';
					
						$html_tablo.='
						<option value="'.$j.'" '.$required.'>'.$don_opt['NAME'].'</option>
						';					
						
						$j++;
					}
					$html_tablo.='</select>
					<div class="clear"></div>';
					
					break;
					
					case 6:	//nom prénom				
					$html_tablo.='
					<div style="height:40px;">
						<div style="float:left;width:48%;height:40px;">
							<input type="text" name="field_fname_'.$don['ID'].'" style="width:85%;" value="'.$_POST['field_fname_'.$don['ID']].'"><br/>
							<label style="font-size:10px;">Prénom</label>
						</div> 
						<div style="float:left;margin-left:10px;width:48%;height:40px;">
							<input type="text" name="field_lname_'.$don['ID'].'" style="width:85%;" value="'.$_POST['field_lname_'.$don['ID']].'"><br/>
							<label style="font-size:10px;">Nom</label>
						</div>
						<div class="clear"></div>
					</div>
					';
					break;
					
					case 7://adresse					
					$html_tablo.='
					
					<div style="height:100px;width:100%;">
						<div>
							<input type="text" name="field_adress_street_'.$don['ID'].'" value="'.$_POST['field_adress_street_'.$don['ID']].'" style="width:100%;"><br/>
							<label style="font-size:10px;">Rue <span class="required_field'.$req_stat.'"> * </span></label>
						</div>
						<div>
							<input type="text" name="field_adress_street2_'.$don['ID'].'" value="'.$_POST['field_adress_street2_'.$don['ID']].'" style="width:100%;"><br/>
							<label style="font-size:10px;">Rue seconde ligne</label>
						</div>
						<div style="display:inline;float:left;width:48%;">
							<input type="text" name="field_adress_zip_'.$don['ID'].'"  value="'.$_POST['field_adress_zip_'.$don['ID']].'" style="width:100%;"><br/>
							<label style="font-size:10px;">Code postal <span class="required_field'.$req_stat.'"> * </span></label>
						</div>
						<div style="display:inline;float:right;width:48%;">
							<input type="text" name="field_adress_town_'.$don['ID'].'"  value="'.$_POST['field_adress_town_'.$don['ID']].'" style="width:100%;"><br/>
							<label style="font-size:10px;">Ville <span class="required_field'.$req_stat.'"> * </span></label>
						</div>
					</div>
					<div class="clear"></div>
					';
					break;
					
					case 8://telephone					
					$html_tablo.='<div><input type="text" name="field_'.$don['ID'].'"  style="width:50%;" value="'.$don['DEFAULT'].'"></div>
					<div class="clear"></div>';
					break;
					
					case 9://date
					if($jquery_date!='')$jquery_date.=',';
					$jquery_date.='#field_date_'.$don['ID'];
					
					$html_tablo.='
					<div>
						<input type="text" name="field_date_'.$don['ID'].'" id="field_date_'.$don['ID'].'"  style="width:30%;" maxlength="10" value="'.$_POST['field_date_'.$don['ID']].'">						
					</div>
					<div class="clear"></div>';
					break;

					case 10://www					
					$html_tablo.='<div><input type="text" name="field_'.$don['ID'].'" style="width:100%;" value="'.$don['DEFAULT'].'"></div>
					<div class="clear"></div>';
					break;
					
					
					case 11://email					
					$html_tablo.='<div><input type="text" name="field_'.$don['ID'].'"  style="width:100%;" value="'.$don['DEFAULT'].'"></div>
					<div class="clear"></div>';
					break;
					
					case 12://fichier					
					$html_tablo.='<div><input type="file" name="field_'.$don['ID'].'" ></div>
					<div class="clear"></div>';
					break;
				
				}
				$html_tablo.='</div>';
			}
		}		
	}
}
echo $html_tablo;
?>
		
		<script type='text/javascript'>
		$(document).ready(function() {
			<?if ($jquery_date!=''){ ?>
			$( "<?echo $jquery_date;?>" ).datepicker({ 
			changeMonth: true,	
			changeYear: true,
			showOn: "button",
			buttonImage: "<?echo URL_DIR;?>/images/date.png"
			});
			<?}?>
			$(".help_tool").tooltip({
				// place tooltip on the right edge
				position: "center right",
				// a little tweaking of the position
				offset: [-2, 10],
				// use the built-in fadeIn/fadeOut effect
				effect: "fade",
				// custom opacity setting
				opacity: 0.7
			});
		});
		</script>

		<div class='submit'>
			<input type="submit" value="Valider">
		</div>
		</div>
	</div>
</div>
</body>
</html>
<?
ob_flush();
?>