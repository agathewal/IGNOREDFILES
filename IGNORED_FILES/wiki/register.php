<?php include('language.php') ; ?>

      <div class="header"><?php  echo $language->menu->register; ?></div>
	  
      <div class="label"><?php  echo $language->username; ?>:</div>
      <div class="field"><input id="user" name="user" /></div>
	  <div class="clear"></div>
	  
      <div class="label"><?php  echo $language->password; ?>:</div>
      <div class="field"><input type="password" id="pass" name="pass" /></div>
	  <div class="clear"></div>
	  
      <div class="label"><?php  echo $language->confirm; ?>:</div>
      <div class="field"><input type="password" id="confirm" name="confirm" /></div>
	  <div class="clear"></div>
	  
      <div class="label"><?php  echo $language->email; ?>:</div>
      <div class="field"><input id="email" name="email" /></div>
      <div style="height:20px;clear:both;"></div>
	  
      <div style="text-align:center;">
       <input type="submit" value="<?php  echo $language->menu->register; ?>"/>
      </div>
