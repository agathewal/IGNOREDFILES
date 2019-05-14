<?php
require_once("../config.php");

$lang = $_GET['lang'];
if(!preg_match('/[a-z]{2}/',$lang)) exit;
$id_grp = $_GET['id_grp'];
if(!is_numeric($id_grp)) exit;

function parseTree($node, $label, $xml, $con, $lang, $child=true, $id_grp){
		
		global $db,$id_base;
		$sql = "SELECT * FROM comm_wiki_node WHERE parent_id=$node AND group_id = $id_grp ORDER BY node_position";
		$result = $db->query($sql) or die("<tree><root id='0'></root></tree>");
		$n = mysql_num_rows($result);
		
		$can_see=false;
		$can_edit=false;
		$actual_node_req=$db->query('SELECT * FROM comm_wiki_node WHERE node_id = '.$node);
		if(mysql_num_rows($actual_node_req)!=0){
			$info_page=mysql_fetch_array($actual_node_req);
			if($info_page['id_user']==$_SESSION['id_comm']){
				$can_edit=true;
			}
			if($info_page['type_right']==3){
				if($info_page['id_user']==$_SESSION['id_comm'])$can_see=true;
				$is_member=$db->countOf('comm_wiki_share_page_user','node_id = '.$node.' AND id_user = '.$_SESSION['id_comm']);
				if($is_member>0)$can_see=true;
			}else $can_see=true;
			
		}
		
		if($can_see){
		
			if($n==0){
				if($node==$id_base){
					return "<tree><root id='0'></root>";
				}
				$xml .= "<leaf label='" . htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' ) . "' ref='$node' editable='$can_edit'/>";
			}else{
				// root needed for selection but not display
				if($child) $xml .= "<folder label='" . htmlspecialchars( $label, ENT_QUOTES, 'UTF-8') . "' ref='$node' editable='$can_edit'>";
			
				for($f=0;$f<$n;$f++){
					$nodex = mysql_result($result, $f, 'node_id');
					$sql = "SELECT label FROM comm_wiki_page WHERE node_id=$nodex";
					$reslutl = $db->query($sql) or die("<tree><root id='0'></root></tree>");
					
					$label = mysql_num_rows($reslutl) > 0 ? mysql_result($reslutl, 0, 'label') : mysql_result($result, $f, 'label');
					$xml = parseTree($nodex, $label, $xml, $con, $lang, true,$id_grp);
				}
				if($child) $xml .= "</folder>"; // not root
			}
			
		}
		
		return $xml;
		
	}
$label = '';

$xml = "<tree>";
header("Content-Type: text/xml");

$id_base=$db->queryUniqueValue('SELECT node_id FROM comm_wiki_node WHERE parent_id = 0 AND group_id = '.$id_grp);

if($id_base!=''){
	echo parseTree($id_base, $label, $xml, $con, $lang, false, $id_grp). "</tree>";


	
}else{

	echo "<tree><root id='0'></root></tree>";
}

?>
