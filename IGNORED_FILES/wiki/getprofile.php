<?php

$token = md5(uniqid(rand(), TRUE));
$_SESSION['token'] = $token;

require_once("config.php");
include('language.php'); 

$id = $_SESSION['id_comm'];

$sql = "SELECT * FROM comm_wiki_user WHERE user_id=$id";
$result = $db->query($sql) or die('Database Error, Unable to retrieve user information');
$username = mysql_result($result,0,'user_name');
$email = mysql_result($result,0,'email');
$subscribe = mysql_result($result,0,'subscribe');
$subcheck = $subscribe == 1 ? ' checked' : '';

?>

      <div class="header"><?php  echo $language->userprofile; ?></div>
	  
      <div class="label"><?php  echo $language->username; ?>:</div>
      <div class="readfield"><?php echo $username;?></div>
	  <div class="clear"></div>
	  
      <div class="label"><?php  echo $language->password; ?>:</div>
      <div class="field"><input type="password" id="ppass" name="pass" /></div>
	  <div class="clear"></div>
	  
      <div class="label"><?php  echo $language->confirm; ?>:</div>
      <div class="field"><input type="password" id="pconfirm" name="confirm" /></div>
	  <div class="clear"></div>
	  
      <div class="label"><?php  echo $language->email; ?>:</div>
      <div class="field"><input id="pemail" name="email" value="<?php echo $email; ?>" /></div>
	  <div class="clear"></div>
	  
      <div class="label">&nbsp;</div>
      <div class="field"><input type='checkbox' id="sub" name="sub"<?php echo $subcheck; ?> />&nbsp;<?php  echo $language->subscribe; ?></div>
      <div style="height:20px;clear:both;"></div>
	  
      <div style="text-align:center;">
	   <input type="hidden" id="token" name="token" value="<?php echo $token; ?>" />
	   <input type="hidden" id="pid" name="id" value="<?php echo $id; ?>" />
	   <input type="hidden" id="subscribe" name="subscribe" value="<?php echo $id; ?>" />
       <input type="submit" value="<?php  echo $language->update; ?>"/>
      </div>
