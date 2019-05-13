<?php	
require_once('../config/configuration.php');	
if(isset($_POST['queryString']) && $_POST['queryString']!="") {
		
	$query = $db->query("SELECT ID,TITLE FROM cms_article WHERE TITLE LIKE '".addslashes($_POST['queryString'])."%' LIMIT 10");
	if($query) {
		while ($result = mysql_fetch_array($query)) {
			echo '<li onClick="javascript:window.location.href=\'article.php?action=edit&id='.$result['ID'].'\'">'.stripslashes($result['TITLE']).'</li>';
		}
	} else {
		echo 'ERROR: There was a problem with the query.';
	}
	
} else {
	echo 'There should be no direct access to this script!';
}
	
?>