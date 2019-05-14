<?php include('language.php') ; ?>

      <div class="header"><?php  echo $language->menu->login; ?></div>
      <div class="label"><?php  echo $language->username; ?>:</div>
      <div class="field"><input name="user" /></div>
	  <div class="clear"></div>
      <div class="label"><?php  echo $language->password; ?>:</div>
      <div class="field"><input type="password" name="pass" /></div>
      <div style="height:20px;clear:both;"></div>
      <div style="text-align:center;">
       <input type="submit" value="<?php  echo $language->menu->login; ?>"/>
      </div>
	  
