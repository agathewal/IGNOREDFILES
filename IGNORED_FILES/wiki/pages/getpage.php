<?php
require_once("../config.php");
require_once("../class/tags.class.php");
// TODO: use node class to get id to CamelCase links

$p = explode('-',$_GET['id']);
$page = $p[0];
$lang = $p[1];
if(!is_numeric($page) || strlen($lang) != 2) exit; 

$sql = "SELECT * FROM comm_wiki_page INNER JOIN comm_wiki_node ON comm_wiki_node.node_id=comm_wiki_page.node_id WHERE comm_wiki_node.node_id=$page";
$result = $db->query($sql) or die("Database Error ".mysql_error());

$pid = mysql_result($result, 0, 'parent_id');
$title = mysql_result($result, 0, 'page.label');
echo "<html>\n<head>\n<title>$title</title>\n<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />";

$tagger = new Tag($con,$page,$lang);
$tags = $tagger->getTags("csv");

if($tags != '')
	echo "\n<meta name='keywords' content='$tags' />";

echo "</head>\n<body>\n";

$wikimarkup = stripslashes(mysql_result($result, 0, 'page_text'));

// basic transformations only
$wikimarkup = preg_replace('/(={1,6})([^=]+)[=]+\n/', '<h1>$2</h1>',$wikimarkup); // don't care if 1 or n
$wikimarkup = preg_replace('/\n(.*)\n/','<p>$1</p>',$wikimarkup); // paragraph
$wikimarkup = preg_replace('/\[(http(s?)\:\/\/[^ ,]+)[, ]([^\]]*)\]/','<a href="$1$2">$3</a>',$wikimarkup); // external link
//$wikimarkup = preg_replace('/\[(\/[a-zA-z0-9_\-\/]+)\]/','$1',$wikimarkup); // internal link
//$wikimarkup = preg_replace('/\[#([^ ,]+)[, ]([^\]]*)\]/','<a href="../pages/getpage.php?id=$1">$2</a>',$wikimarkup); // internal link
$wikimarkup = preg_replace('/\[%(.*?)%\]/s', '<pre>$1</pre>', $wikimarkup); // pre
$wikimarkup = preg_replace('/%(.*?)%/s', '<code>$1</code>', $wikimarkup); // code
if($pid > 0)
	echo $wikimarkup."<br/><br/>";

include("links.php");
echo "<script>window.location = '../#$page-{$p[1]}';</script>";
echo "\n</body>\n</html>\n"
?>