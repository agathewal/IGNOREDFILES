<?php 
    require_once("./config.php");
    include('language.php') ; 
    include("./handlers/editcheck.php") ;

    $diablepaste = $_GET['clip'] == '' ? "disabled " : '';
    $stylepaste = $_GET['clip'] == '' ? " style='color:#888;'" : '';
    
    $page = $_GET['target'];
    if(!is_numeric($page)) exit;

    if(!isEditable($page, $lang, $con, $CFG_REGISTERED_ONLY)){
        echo "<div class='header'>".$language->node->title."</div>";

        echo "<div>{$language->message->unauthorized}  $tell<div>";
        exit;
    }

?>

      <div class="header"><?php  echo $language->node->title; ?></div>
	  
      <div class="labelvar"><?php  echo $language->node->action; ?></div>
	  <div class="clear"></div>
	  
	  <div style="min-height:100px;border: 1px solid black;width:100%;">
		  <div style="float:left;width:100px;">
		      <div class="field"><input id="faddpage" type="radio" name="act" checked onclick="updatefldrfrm(this);"/><?php  echo $language->node->add; ?></div>
			  <div class="clear"></div>
		      <div class="field"><input id="fremovefolder" type="radio" name="act" onclick="updatefldrfrm(this);"/><?php  echo $language->node->remove; ?></div>
			  <div class="clear"></div>
		  </div>
		  <div style="float:left;">
		      <div class="field"><input id="fcut" type="radio" name="act" onclick="updatefldrfrm(this);" ><?php  echo $language->node->cut; ?></div>
			  <div class="clear"></div>
		      <div class="field" <?php echo $stylepaste;?>><input id="fpaste" type="radio" name="act" onclick="updatefldrfrm(this);" <?php  echo $diablepaste; ?>/><?php  echo $language->node->paste; ?></div>
			  <div class="clear"></div>
		      <div class="field"><input id="frename" type="radio" name="act" onclick="updatefldrfrm(this);" /><?php  echo $language->node->rename; ?></div>
			  <div class="clear"></div>
		  </div>
		  <div class="clear"></div>
	  </div>
	  
	  <input type="hidden" id="action" name="action" />
	  <input type="hidden" id="ntype" name="ntype" value="<?php echo $t; ?>" />
	  
      <div id="fldrfrmname">
          <div class="labelvar"><?php  echo $language->node->newpage; ?>:</div>
          <div class="field"><input name="name" id="fname" /></div>
          <div class="clear"></div>
          <div class="clear"></div>
      </div>
    
      <div id="fldrfrmaddpaste">
	      <div class="labelvar"><?php  echo $language->node->addpaste; ?></div>
		  <div class="clear"></div>
		  
		  <div>	  
			  <div style="float:left;">
			      <div class="field"><input id="before" type="radio" name="where" checked /><?php  echo $language->node->before; ?></div>
				  <div class="clear"></div>
			  </div>
			  <div style="float:left;">
			      <div class="field"><input id="after" type="radio" name="where" /><?php  echo $language->node->after; ?></div>
				  <div class="clear"></div>
			  </div>
			  <div style="float:left;">
			      <div class="field"><input id="in" type="radio" name="where" /><?php  echo $language->node->in; ?></div>
				  <div class="clear"></div>
			  </div>		  
			  <div class="clear"></div>
		  </div>
	  </div>
	  
	  <input type="hidden" id="position" name="position" />
	  
      <div id="fldrfrmcmt">
	 	  <div class="clear"></div>
	      <div class="labelvar"><?php  echo $language->node->comment; ?>:</div>
		  <div class="field"><textarea id="commentf" name="commentf"></textarea></div>
	  </div>
	  
	  
      <div style="height:20px;clear:both;"></div>
      <div style="text-align:center;">
       <input type="submit" value="<?php  echo $language->update; ?>"/>
      </div>
