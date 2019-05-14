<?php
if(!isset($configpath)) $configpath="..";

$thisdir = $configpath=='.' ? 'pages/' : '' ;
require_once("$configpath/config.php");

$links = ''; $cnt = 0;

if($lang==''){
	// show default if none selected
	$json_string = file_get_contents ("$configpath/language/languages.json");
	$language = json_decode($json_string);
	$lang = $language->languages[0]->symbol;
}
parsenode(1, $node,$con,$lang);


// TODO: need better way to limit count
echo substr($links,3);


function parsenode($lid,$node,$con,$lang){

	global $db;
	$sql = "SELECT comm_wiki_node.node_id,comm_wiki_page.label FROM comm_wiki_page ".
		"INNER JOIN comm_wiki_node on comm_wiki_page.node_id=comm_wiki_node.node_id WHERE ".
		"comm_wiki_node.parent_id=$lid";
	$resultp = $db->query($sql) or die(mysql_error());
	$np = mysql_num_rows($resultp);
	for($p=0;$p<$np;$p++){
		$id = mysql_result($resultp, $p, 'comm_wiki_node.node_id');
		$label = mysql_result($resultp, $p, 'label');
		addlink($id."-$lang",$label);
		$cnt++;
		if($cnt > 100) return;
        parsenode($id, $node,$con,$lang);
	}
    
}


function addlink($id,$label){
	global $links,$thisdir;
	$links.= " | <a href='".$thisdir."getpage.php?id=$id&amp;title=".htmlspecialchars($label)."'>".htmlspecialchars($label)."</a> ";
}
?>