<?php
require_once("../config.php");
include('language.php'); 

$page = $_POST['id'];
if(!is_numeric($page)) exit;

$lang = $_GET['lang'];
if(!preg_match('/[a-z]{2}/',$lang)) exit;

echo "<div style='float:left;font-size:18px;'>$language->treehistory</div>";
echo "<div style='float:left;margin-left:20px;margin-top:5px;'><a href='javascript:tree.click($page)'>{$language->history->goback}</a></div>";
echo "<div style='clear:both;height:12px;'></div>";
echo "<div id='revform' style='display:none;position:absolute;top:50px;'></div>";
$sql = "SELECT * FROM comm_wiki_node_revision  ";
$sql.= "ORDER BY revision_time DESC";
$result = $db->query($sql) or die("Database Error - Unable to get revisions.  $sql");

$cnt = mysql_num_rows($result);

$previous = 'current';
for($i=0;$i<$cnt;$i++){
	$rev = mysql_result($result, $i, 'revision_id');
	$rt = mysql_result($result, $i, 'revision_time');
	$uid = mysql_result($result, $i, 'user_id');
	$nid = mysql_result($result, $i, 'node_id');
	$ip = mysql_result($result, $i, 'user_ip');
	$comment = mysql_result($result, $i, 'comment');
	$action = mysql_result($result, $i, 'action');
	
	if($nid==0) continue; // TODO: where do these come from???
	
	$user = "anonymous";
	$sql = "SELECT user_name FROM comm_wiki_user WHERE user_id=$uid";
	$resultu = $db->query($sql) or die("Database Error - Unable to get user.");
	if(mysql_num_rows($resultu)>0)
		$user = mysql_result($resultu, 0,'user_name');
		
	$sql = "SELECT label,parent_id FROM comm_wiki_node WHERE node_id=$nid";
	$resultn = $db->query($sql) or die("Database Error - Unable to get node.");
	if(mysql_num_rows($resultn)==0) continue;
	$label = mysql_result($resultn, 0, 'label');
	$pid = mysql_result($resultn, 0, 'parent_id');


	$revert = $action == 'remove' && $pid==0 ? "<span style='font-size:12px;'><a href='javascript:setclipboard($nid);'>Node to clipboard</a></span>": "" ;
	echo "<div style='font-size:14px;font-weight:bold;'>$rt <span style='color:#009'>$user</span> : $ip : <span style='color:#009'>$label</span> $revert </div>";
	echo "<div style='float:left;width:120px;'><span style='color:#009'>{$language->history->action}:</span> $action</div> <div style='float:left'><span style='color:#009'>{$language->comment}:</span> $comment</div><br/><br/>";
	
	$previous = "$rev";
}



?>
