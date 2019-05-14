<?php
require_once("../config.php");

$user = $_POST['user'];
$pass = md5($_POST['pass']);
$ip=$_SERVER['REMOTE_ADDR'];

$sql = "SELECT * FROM comm_wiki_user WHERE user_name = '$user' AND password = '$pass'";
$result = $db->query($sql)  or die("{'response':'Database Error'}");
if(mysql_num_rows($result)==0){
	echo "{'response':'Invalid Login, Please try again.'}";
	exit;
}

$uid = mysql_result($result, 0,'user_id');
$level = mysql_result($result, 0,'level');
if(mysql_num_rows($result) == 1){
	$_SESSION['uid'] = $uid;
	$_SESSION['level'] = $level;
	$json = "{'response':'ok','level':'$level','user':'$user','ip':'$ip', 'uid':'$uid'}";
	if(mysql_result($result, 0 , 'level') == 'admin')
		$_SESSION['admin'] = true;
}else{
	$json = "{'response':'Invalid Login, Please try again.'}";
}
echo $json;
?>
