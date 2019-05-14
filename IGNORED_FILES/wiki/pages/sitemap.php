<?php
$sitemap='<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>';
$sitemap.='<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n"
;

require_once("../config.php");
//require_once("../wwh/class/node.class.php");


$sql = "SELECT DISTINCT language FROM  `comm_wiki_page` WHERE 1 ";
$result = $db->query($sql) or die(mysql_error());
$n = mysql_num_rows($result);
for($r=0;$r<$n;$r++){
	$lang = mysql_result($result, $r, 'language');    
	parsenode(1, $node,$con,$lang);

}
$sitemap.= "</urlset>\n";

header ("Content-Type:text/xml");
echo $sitemap;

function parsenode($lid,$node,$con,$lang){
global $db;
	$sql = "SELECT comm_wiki_node.node_id,comm_wiki_page.label FROM comm_wiki_page ".
		"INNER JOIN comm_wiki_node on comm_wiki_page.node_id=comm_wiki_node.node_id WHERE ".
		"comm_wiki_node.parent_id=$lid AND language='$lang'";
	$resultp = $db->query($sql) or die(mysql_error());
	$np = mysql_num_rows($resultp);
	for($p=0;$p<$np;$p++){
		$id = mysql_result($resultp, $p, 'comm_wiki_node.node_id');
		$label = mysql_result($resultp, $p, 'label');
		addmap($id,$lang,$label,$con);
        parsenode($id, $node,$con,$lang);
	}
    
}

function addmap($id,$lang,$label,$con){
    global $sitemap,$db;
    $root = $_SERVER['SERVER_NAME'];
    $scriptpath = $_SERVER['REQUEST_URI'];
    $break = Explode('/', $scriptpath);
    $pfile = $break[count($break) - 1]; 

    $scriptpath = substr($scriptpath,0,strlen($scriptpath)-strlen($pfile)-strlen($relpath)-1);
    $path = "http://$root$scriptpath".substr($path,5)."/getpage.php?id=$id-$lang&amp;title=".htmlspecialchars($label);

    $sql = "SELECT UNIX_TIMESTAMP(revision_time) AS rev_time FROM comm_wiki_node_revision WHERE node_id=$id AND language='$lang' ORDER BY revision_time DESC LIMIT 0,1";
    $result = $db->query($sql) or die(mysql_error());
    $n = mysql_num_rows($result);
    $rev_time = $n > 0 ? mysql_result($result, 0, 'rev_time'): 0;
    
    $mod = $rev_time == 0 ? '2000-01-01' : date("Y-m-d", $rev_time);
    $sitemap.="  <url>\n".
    "   <loc>$path</loc>\n".
    "   <lastmod>$mod</lastmod>\n".
    "   <priority>0.5</priority>\n".
    "   <changefreq>weekly</changefreq>\n".
    "  </url>\n"
    ;
}
?>