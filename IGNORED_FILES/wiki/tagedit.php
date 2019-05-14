<?php

require_once("config.php");
include('language.php') ;
include('class/tags.class.php');

$id = $_GET['id'];
if(!is_numeric($id)) exit;
$lang = $_GET['lang'];
if(!preg_match('/[a-z]{2}/',$lang)) exit;

$tagger = new Tag($con,$id,$lang);
$text = $tagger->getTags("csv");

header("Content-Type: text/html");
?>
      <div class="header"><?php echo $language->menu->tags ; ?></div>
	  <div class="clear"></div>
	  
	  <div class="field" style="width:350px;"><textarea name="tags" rows=5 cols=40><?php echo $text; ?></textarea></div>
	    
      <div style="height:20px;clear:both;"></div>
      <div style="text-align:center;">
       <input type="submit" value="<?php echo $language->update ; ?>"/>
      </div>
