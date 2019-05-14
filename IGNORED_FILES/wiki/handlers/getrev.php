<?php
require_once("../config.php");

$donnees=array();
$donnees['success']=0;
$rev = $_POST['id'];

if(is_numeric($rev)){

	$sql = "SELECT page_text FROM comm_wiki_revision WHERE revision_id=$rev";
	$result = $db->query($sql);
	if(mysql_num_rows($result)!=0){
		
		$info_page=mysql_fetch_array($result);
		$info_page=array_map('stripslashes',$info_page);
		$info_page["page_text"]=$info_page["page_text"];
		$donnees['success']=1;
		$donnees['page']=$info_page["page_text"];
		
		
		//echo "<div class='titre_group'>Revision $rev : $rt</div>\n<div style='margin-top:10px;margin-bottom:10px;width:100%;' id='ligne_separation'></div>\n".nl2br(stripslashes(mysql_result($result, 0, 'page_text')));
	
	}
	
}

echo json_encode($donnees);
?>