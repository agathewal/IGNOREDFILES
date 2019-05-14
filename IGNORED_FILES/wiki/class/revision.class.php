<?php

require_once("../class/class.simplediff.php");

class Revision{
	
    var $con;       // the sql connection
    var $node_id;   // the id of the node
    var $user_id;   // the id of the user
    var $node_name;   // the name of the page
    var $return_address;   // the name of the page
	var $language;

    /**
     * Consructor
     * 
     * @param $con  The connection to the mysql database
     * @param $id The id of the node associated with the tag
     * @param $uid The id of the user associated with the tag
     */
    function Revision($con,$id,$uid,$return_address,$language){
	global $db;
        $this->con = $con;
        $this->node_id = is_numeric($id) ? $id : 0;
        $this->user_id = is_numeric($uid) ? $uid : 0;
        $this->return_address = $return_address;
		$this->language = $language;
		$sql = "SELECT label FROM comm_wiki_page WHERE node_id=$id";
		$result = $db->query($sql) or die("Database Error - ".mysql_error());
		$this->node_name = mysql_result($result,0,'label');
    }
    
    /**
     * Updates the history and notifies subscribers
     * 
     * @param $text  The text of the previous revision
     * @param $type "page" or "tag"
     */
    function save($text,$type,$comment="",$new_text=""){
		
		global $db;
		$now = date('YmdHis');
		$nowe = date('Y-m-d H:i:s');
		$ip=$_SERVER['REMOTE_ADDR'];
		
		$sql = "INSERT INTO `comm_wiki_revision` (revision_id,node_id,user_id, `type`, page_text, comment,revision_time) ";
		$sql .= "VALUES ('',".$this->node_id.",".$this->user_id.", '$type', '$text', '$comment','$now') ";
		
		$result = $db->query($sql) or die("{'response':'Database Error - Unable to save revision.\n$sql'}");

                if($this->return_address == '')
                    return; // no mail option

		// global subscriptions
		$sql = "SELECT subscribe,email FROM comm_wiki_user WHERE subscribe=1";
		$result = $db->query($sql) or die("{'response':'Database Error - Unable to check subscription'}");
		
		$diffsend = "";
		if($new_text != ""){
			$diffsend = simpleDiff::diff($text, $new_text);
		}
		$link = $this->_curpage()."-".$this->language;

		$send = $this->node_name." $type has been updated $nowe.\n\n$diffsend\n\n$link";
		for($s=0;$s<mysql_num_rows($result);$s++){
		
		    $email = mysql_result($result,$s,'email');  
		    $to      = $email;
		    $subject = 'Wiki Web Help Update';
		    $headers = "From: ".$this->return_address."\r\n";
		        
		    
		    if(!$this->_mail_utf8($to, $subject, $send, $headers)){
		        echo "{'response':'There was an error sending subscription.  Please try again!'}";
			exit;
		    }
		        
		}
    	
		// single page subscriptions
		$sql = "SELECT email FROM comm_wiki_user INNER JOIN comm_wiki_subscription ON comm_wiki_user.user_id=comm_wiki_subscription.user_id WHERE page_id="
				.$this->node_id;

		$result = $db->query($sql) or die("{'response':'Database Error - Unable to check page subscription'}");
		
		for($s=0;$s<mysql_num_rows($result);$s++){
		
		    $email = mysql_result($result,$s,'email');  
		    $to      = $email;
		    $subject = 'Wiki Web Help Update';
		    $headers = "From: ".$this->return_address."\r\n";
		        
		    if(!$this->_mail_utf8($to, $subject, $send, $headers)){
		        echo "{'response':'There was an error sending page subscription.  Please try again!'}";
			exit;
		    }
		        
		}
    	
    }

	function _mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
		$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
		return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
	}

	function _curpage() {
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		$pageURL = substr($pageURL,0,strrpos($pageURL,"/")); // don't need file name
		$pageURL = substr($pageURL,0,strrpos($pageURL,"/")); // don't need handler directory
		return $pageURL."/#".$this->node_id;
	}    
}
?>
