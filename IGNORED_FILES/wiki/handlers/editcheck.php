<?php
require_once(dirname(__FILE__).'/../config.php');

function isEditable($page, $lang, $con, $CFG_REGISTERED_ONLY){
	global $db;
    $admin = false;
    $loggedon = isset($_SESSION['uid']);
    if($loggedon){
            $sql = "SELECT level FROM comm_wiki_user WHERE user_id={$_SESSION['uid']}";
            $result = $db->query($sql) or die("Database Error - Unable to retrive user info. ".mysql_error());
            if(mysql_num_rows($result) > 0)
                    $admin = mysql_result($result, 0, 'level')=='admin';

    }


    $locked = 0;
    $sql = "SELECT locked FROM comm_wiki_page WHERE node_id=$page";
    $result = $db->query($sql) or die("Database Error - Unable to retrive locked status. ".mysql_error());
    if(mysql_num_rows($result) > 0)
            $locked = mysql_result($result, 0, 'locked');

    $ip=$_SERVER['REMOTE_ADDR'];

    

    $registered = ($CFG_REGISTERED_ONLY && $loggedon) || !$CFG_REGISTERED_ONLY;

    $edit = ($locked == 0 && $registered) || $admin;

    if($admin) $edit=2;

    return $edit;
}
?>
