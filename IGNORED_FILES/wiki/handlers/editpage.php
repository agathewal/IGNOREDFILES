<?php
require_once("../config.php");
include('language.php'); 
include('buttons.php'); 

$page = $_GET['id'];
if(!is_numeric($page)) exit;

$lang = $_GET['lang'];
if(strlen($lang) > 2) return;

$clip = $_GET['clip'];
$admin = false;
if(isset($_SESSION['uid'])){
	$sql = "SELECT level FROM comm_wiki_user WHERE user_id={$_SESSION['uid']}";
	$result = $db->query($sql) or die("Database Error - Unable to retrive user info. ".mysql_error());
	if(mysql_num_rows($result) > 0)
		$admin = mysql_result($result, 0, 'level')=='admin';

}
$loggedon = isset($_SESSION['uid']) ? $_SESSION['uid'] : '';

header("Content-Type: text/html");
$path = $_GET['path'];
$pathinput = '';
if($path!=''){

	$parts = explode("/", $path);
	$title = preg_replace("/([a-z])([A-Z])/", "$1 $2", $parts[count($parts)-1]); // wiki word
	$text = "=$title=";
	$locked = 0;
	$pathinput = "<input type='hidden' id='path' name='path' value='$path'></input>";
	
}else{

	$sql = "SELECT page_text,label,locked FROM comm_wiki_page WHERE node_id=$page";
	$result = $db->query($sql) or die("Database Error - ".mysql_error());
	$locked = 0;
	if(mysql_num_rows($result) > 0){
		$text =  mysql_result($result, 0, 'page_text');
		$title = mysql_result($result, 0, 'label');
		$locked = mysql_result($result, 0, 'locked');
	}else{
		$sql = "SELECT label FROM comm_wiki_node WHERE node_id=$page";
		$result = $db->query($sql) or die("Database Error - ".mysql_error());
		$title = mysql_result($result, 0, 'label');
		$text = "=$title=";
	}
}

$ip=$_SERVER['REMOTE_ADDR'];

$registered = ($CFG_REGISTERED_ONLY && $loggedon) || !$CFG_REGISTERED_ONLY;

$edit = ($locked == 0 && $registered) || $admin;

$html =  "
<div id='zone_wiki'>
	<div style='width:900px;height: 465px;'>";
if($edit){
	$html .= buttons();
}

//$html .= "<div style='float:right;'><input type='image' src='".URL_DIR."/wiki/images/system/toggleedit.png' class='image' title='Changer de vue' onclick='toggleedit();' /></div>";
$html .= "<div style='float:right;margin-right: 200px;font-weight: bold;'>"._('Aperçu de votre document')."</div>";

$html .=  "<div class='clear'></div></div><div style='clear:both;margin-top:10px;'>";

$ro = "";
if(!$edit) $ro = " readonly";

$html .=  " <textarea id='edittext'  style='float:left;width:48%;border:1px solid #CCC;padding-left:5px;height:300px;' onkeyup='inlinepreview();' onfocus='inlinepreview();' onmouseup='previewscroll();' onscroll='previewscroll();'$ro>$text</textarea>";
$html .=  " <div id='previewbox' style='float:right;height:300px;width:48%;overflow:auto;border:1px solid #CCC;margin-top:2px;padding-left:5px;'></div>";
$html .=  " <div class='clear'></div>";

/*$html .=  " <div id='editdiff' style='margin-top:2px;'></div>";

$html .=  " <div style='clear:both;'></div>";
$html .=  "</div>";*/

$html .=  "<div style='clear:both;margin-top:10px;'>";
if($edit){
	$html .=  _("Commentaire sur votre modification :")." <br><textarea id='commente' style='width:99%;' class='input_design'></textarea>";
}
	//$html .=  " <div style='display:inline;margin-left:30px;'><input type='button' value='Annuler' onclick='tree.click(\"$page\");'></div>";

if($edit){

	$html.= $pathinput;
	$html.='
	<div style="margin-top:10px;">
	<div class="button_comm" style="float:left;width:120px;" onclick="destroy_edit(\''.$page.'\');">
		<div class="text_button_comm">'._('Annuler').'</div>
		<div class="icon_button_comm"><img src="'.URL_DIR.'/images/btn_cross.png"></div>
	</div>';
	$html.='
	<div class="button_comm" style="float:right;width:120px;" onclick="editsave();">
		<div class="text_button_comm">'._('Sauvegarder').'</div>
		<div class="icon_button_comm"><img src="'.URL_DIR.'/images/btn_check.png"></div>
	</div>
	</div>
	';

	/*$html .=  " <div style='display:inline;padding-left:30px;display:inline'>";
	$html .=  "  <input type='button' value='".$language->save."' onclick='editsave();'></input></div>";*/
	$html .=  " <div style='padding-left:30px;display:none;'>";
	$html .=  "  <input type='button' value='"._('Différence')."' onclick='editdiff();'></input></div>";
	$html .=  " <div style='display:none;padding-left:30px;'>";
	$html .=  "  $language->preview.<input id='showpreview' type='checkbox' checked='checked'></input></div>";
	$html .=  " <div style='display:none;padding-left:10px;'>";
	$html .=  "  $language->scroll.<input id='autoscroll' type='checkbox' checked='checked'></input></div>";
	$html .=  "<div class='clear'></div></div><div class='clear'></div></div>";
	
}else{

	$html .=  " <div style='display:inline;padding-left:30px;display:inline'>";
	$html .=  "  $language->preview.<input id='showpreview' type='checkbox' checked='checked'></input></div>";
	$html .=  " <div style='display:inline;padding-left:10px;display:inline'>";
	$html .=  "  Scroll.<input id='autoscroll' type='checkbox' checked='checked'></input></div>";
	
}

echo stripslashes($html);
?>
