<?php
require_once("../config.php");

$lang = $_GET['lang'];
if(!preg_match('/[a-z]{2}/',$lang)) exit;

$label = '';
$in = '';
header("Content-Type: text/xml");
$in = parseTree(1, $label, $in, $con, false);
$in = substr($in, 1);

$sql = "SELECT DISTINCT comm_wiki_tag.tag,comm_wiki_tag.tag_id FROM comm_wiki_tagxref ";
$sql .= "INNER JOIN comm_wiki_tag ON comm_wiki_tagxref.tag_id = comm_wiki_tag.tag_id ";
$sql .= "WHERE comm_wiki_tagxref.node_id IN ($in) AND comm_wiki_tag.tag !='' ORDER BY comm_wiki_tag.tag";
$result = $db->query($sql) or die("<index><root id='$in'></root></index>");
$xml = "<index>";

for($r=0;$r<mysql_num_rows($result);$r++){
	$tag = htmlspecialchars(mysql_result($result, $r, 'tag'), ENT_QUOTES, 'UTF-8' );
	$tag_id = mysql_result($result, $r, 'tag_id');

	$sql = "SELECT DISTINCT comm_wiki_tagxref.node_id,comm_wiki_page.label FROM comm_wiki_tagxref ";
	$sql .= "INNER JOIN comm_wiki_page ON comm_wiki_tagxref.node_id=comm_wiki_page.node_id ";
	$sql .= "WHERE comm_wiki_tag_id=$tag_id AND  comm_wiki_tagxref.node_id IN ($in) ORDER BY comm_wiki_page.label";
	$resultn = $db->query($sql) or die("<index><root id='-1'></root></index>");

	if(mysql_num_rows($resultn) > 0)
			$xml .= "<tag label='$tag'>"; // make sure it is not a tag from deleted page

	for($n=0;$n<mysql_num_rows($resultn);$n++){
		$node_id = mysql_result($resultn, $n, 'node_id');
		$label = mysql_result($resultn, $n, 'comm_wiki_page.label');
		$xml.="<node id='$node_id' label='" . htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' ) . "' />";
	}
	$xml.= "</tag>";
}

$xml .= "</index>";
echo $xml;

function parseTree($node, $label, $in, $con, $child=true){
	global $db;
	// root needed for selection but not display
	if($child){
		$in.=",$node";
	}  
	$sql = "SELECT * FROM comm_wiki_node WHERE parent_id=$node ORDER BY node_position";
	$result = $db->query($sql) or die("<tree><root id='-2'></root></tree>");
	$n = mysql_num_rows($result);
	
	for($f=0;$f<$n;$f++){
		$nodex = mysql_result($result, $f, 'node_id');
		$label = mysql_result($result, $f, 'label');
		$in = parseTree($nodex, $label, $in, $con);
	}
	
	return $in;
}

?>
