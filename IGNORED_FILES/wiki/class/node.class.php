<?php
define("NEW_PAGE",-1);
define("NO_PATH",-2);

class Node{
    
    var $con;       // the sql connection
    var $histid;    // the id of the node for history purpose
    var $target;    // the node from where the action was initiated
    var $right;    // the node from where the action was initiated
    var $tags;    // the node from where the action was initiated
    var $user;      //
    var $uid;       // the id of the user making the change
    var $lang;      // the language of the tree
    var $ip;        // the ip address of the change being made
    var $action;    // add, remove, rename
    var $comment;   // the comment on the change recorded in the history
    var $position;  // this is used for ordering the nodes
    var $name;      // the label of the node
    var $json;      // a response to send to the client
    var $root;      // language root
    var $registered_only;      // only registered users can edit
    
    /**
     * Consructor
     * 
     * @param $con  The connection to the mysql database
     * @param $lang The language
     */
    function Node($con,$lang){
        $this->con = $con;
        $this->lang = $lang;

        $this->root = 1;
        
    }

    /**
     * Sets the id
     * 
     * @param $id  The string to be converted (WikiWord)
     * @return      void
     */
    function setHistId($id) {$this->histid = is_numeric($id) ? $id : 0;}
    
    /**
     * Sets the id
     * 
     * @param $id  The string to be converted (WikiWord)
     * @return      void
     */
    function setUid($id) {$this->uid = is_numeric($id) ? $id : 0;}
    
    /**
     * Sets the id
     * 
     * @param $id  The string to be converted (WikiWord)
     * @return      void
     */
    function setTarget($id) {$this->target = is_numeric($id) ? $id : 0;}
	
	/**
     * Sets the right
     * 
     * @param $right  The string to be converted (WikiWord)
     * @return      void
     */
    function setRight($right) {$this->right = is_numeric($right) ? $right : 1;}
	
	/**
     * Sets the tags
     * 
     * @param $right  The string to be converted (WikiWord)
     * @return      void
     */
    function setTags($tags) {$this->tags = addslashes($tags);}
    
    /**
     * Converts a String to Wiki Word Format
     * 
     * @param $str  The string to be converted (WikiWord)
     * @return      The converted format (Wiki Word)
     */
    function WikiWord($str){
        $segs = explode("/",$str);
        $w = $segs[count($segs)-1];
        return preg_replace("/([a-z])([A-Z])/", "$1 $2", $w);
    }
    
    /**
     * Find the id of a node based on it's name and parent
     * 
     * @param $parent   The parent id
     * @param $label    The label of the node
     * @return          The node id or -1 if not found
     */
    function parseNode($parent, $label){
	
		global $db;
		
        $label = $this->WikiWord($label);
        $sql = "SELECT * FROM comm_wiki_node INNER JOIN comm_wiki_page ON comm_wiki_node.node_id=comm_wiki_page.node_id ".
				"WHERE comm_wiki_node.parent_id=$parent AND comm_wiki_page.label='$label'";
        $result = $db->query($sql);
        
        if(mysql_num_rows($result) > 0){
            $id = mysql_result($result, 0, 'comm_wiki_node.node_id');
        }else{
            $id = -1;
        }       
        return $id;
    }
    
    /**
     * Find the page name based on the path
     * 
     * @param $path     The path of the node
     * @return          The page name as a string
     */
    function PageFromPath($path){
        $chunks = explode("/", substr($path,1)); 
        return $this->WikiWord($chunks[count($chunks)-1]);
    }
    
    /**
     * Find the id of the parent node based on it's name and parent
     * 
     * @param $path     The path of the node
     * @return          The parent id
     */
    function ParentFromPath($path){
        $chunks = explode("/", substr($path,1)); 

        if(count($chunks) == 1){
            return $this->root;
        }else{
            $parentpath = '';
            for($i=0;$i<count($chunks)-1;$i++){
                $parentpath.= "/$chunks[$i]";
            }
            return $this->NodeFromPath($parentpath);
        }
    }
    
    /**
     * Find the id of a node from it's path
     * 
     * @param $path     The path of the node
     * @return          The node id or NEW_PAGE if found at end of path.  NO_PATH if not found.
     */
    function NodeFromPath($path){
        $chunks = explode("/", substr($path,1)); // remove first slash and split
        
        $id = $this->root;
        
        $c = 1; // count chunks, only create page at end of path
        foreach($chunks as $chunk){
            $id = $this->parseNode($id, $chunk,$con);
            if($id==-1){
                $redirect = $this->CheckRedirect($path);
                if($redirect > 0) return $redirect;
                if($c == count($chunks))
                    $id = NEW_PAGE;
                else
                    $id = NO_PATH;  
                break;
            }
            $c++;
        }
        return $id;
    }
    
    /**
     * email changes to subscribers
     * 
     * @param $return_address   The From: part of the email
     * @param $action   (add,remove,rename)
     */
    function Subscriptions($return_address, $action){
	
	
		global $db;
		
        if($return_address=='') return; // don't use subscription mail feature
        $sql = "SELECT subscribe,email FROM comm_wiki_user WHERE subscribe=1";
        try{
            $result = $db->query($sql);
            
        }catch(Exception $e){
            $this->json="{'response':'Database Error - Unable to check subscription'}";
            return FALSE;
        }

	
        
        for($s=0;$s<mysql_num_rows($result);$s++){
            
            $nowe = date('Y-m-d H:i:s');
            $email = mysql_result($result,$s,'email');  
            $to      = $email;
            $subject = 'Wiki Web Help Update';
            $headers = "From: $return_address\r\n";
                
            $send = "Node ".$this->name." has been updated $nowe with action of $action.";
            
            try{
                $this->_mail_utf8($to, $subject, $send, $headers);
            
            }catch(Exception $e){
                $this->json="{'response':'Database Error - Unable to send subscription'}";
            }
        }
    }
    
	function _mail_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
		$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
		return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $message, $header_ . $header);
	}

    /**
     * Renames a node and adds a redirect from the old name
     * 
     * @return  TRUE or FALSE
     */
    function Rename(){
		global $db;
		
    	if(!$this->isEditable($this->target)){
    		$this->json="{'response':'Page locked, unable to rename'}";
		    return FALSE;
    	}
			
        try{
			$sql = "SELECT node_id FROM comm_wiki_page WHERE node_id=$this->target";
            $result = $db->query($sql);
			if(mysql_num_rows($result) == 0){
				$sql = "INSERT INTO comm_wiki_page VALUES($this->target,'$this->lang','$this->name','=$this->name=',0)";
				$result = $db->query($sql);
			}else{
		
				// here we want to save the old name as a redirect.
				$this->AddRedirect($this->PathFromId($this->target),$this->target);
				$sql = "UPDATE comm_wiki_page SET label='$this->name' WHERE node_id=$this->target";
				$result = $db->query($sql);
			}			
            
        }catch(Exception $e){
            $this->json="{'response':'Unable to rename'}";
            return FALSE;
        }
        $this->json="{'response':'ok','node':'".$this->target."'}";     
        $this->histid = $this->target;
        
        return TRUE;
        
    }
    
    /**
     * Records the changes
     * 
     * @param $action   (add, remove, rename)
     * @return  TRUE or FALSE
     */
    function UpdateHistory($action){
		global $db;
        $now = date('YmdHis');
        if($action=="addpage" || $action == "addfolder") $action = "add";
        
        $sql = "INSERT INTO `comm_wiki_node_revision` (revision_id,node_id,user_id,action, comment,revision_time,label) ";
        $sql .= "VALUES ('',".$this->histid.",".$this->uid.",'$action', '".$this->comment."','$now','".$this->name."') ";

        try{
            $result = $db->query($sql);
            
        }catch(Exception $e){
            $this->json="{'response':'Error updating history'}";
            return FALSE;
        }
        return TRUE;
    }
	
    /**
     * Is a user blocked?
     * 
     * @return  TRUE or FALSE
     */
    function blocked(){
        
        return FALSE;
    	
    }

    /**
     * Checks to see if a node is editable based on its locked status and blocked ips
     * 
     * @param $node_id  The id of the node to be checked
     * @return  TRUE or FALSE
     */
	function isEditable($node_id){
       global $db;
	   $sql = "SELECT locked FROM comm_wiki_page WHERE node_id=".$node_id;
        $block = 1; $locked = 0;
        
        try{
            $result = $db->query($sql);
            if(mysql_num_rows($result) > 0)
				$locked = mysql_result($result, 0, 'locked');
            
        }catch(Exception $e){
            $err = $e->getMessage();
            $this->json="{'response':'Database Error, $err'}";
            return FALSE;
        }
		if($locked == 1 || $this->blocked() || ($this->registered_only && ($this->uid < 1)))
		    return FALSE;

		return TRUE;
	}
    
    /**
     * Removes a node
     * 
     * @param $node_id  The id of the node to be removed
     * @return  TRUE or FALSE
     */
    function RemoveNode($node_id){
       global $db;
	   $this->histid = $node_id;
        $sql = "SELECT parent_id,node_position,locked,label FROM comm_wiki_node WHERE node_id=".$node_id;
		
        try{
            $result = $db->query($sql);
            
        }catch(Exception $e){
        	$err = $e->getMessage();
            $this->json="{'response':'Database Error, $err'}";
            return FALSE;
        }
		
        if(!$this->isEditable($node_id)){
            $this->json="{'response':'Page locked.  Unlock page before deleting.$edit'}";
            return FALSE;
        }

        $parent_id = mysql_result($result, 0, 'parent_id');
        $node_position = mysql_result($result, 0, 'node_position');
        $this->name = mysql_result($result, 0, 'label');
        
        // remove node by setting parent to 0 to preserve it
        $sql = "UPDATE comm_wiki_node SET parent_id=0 WHERE node_id=".$node_id;
        try{
            $result = $db->query($sql);
            
        }catch(Exception $e){
            $this->json="{'response':'Database Error, Unable to update node positions'}";
            return FALSE;
        }
		
		delete_activity(12,$node_id);
    
        // shift nodes down if they are above the current node
        $sql = "UPDATE comm_wiki_node SET node_position=node_position-1 WHERE node_position>$node_position AND parent_id=$parent_id";
        try{
            $result = $db->query($sql);
            
        }catch(Exception $e){
            $this->json="{'response':'Database Error, Unable to update node positions'}";
            return FALSE;
        }
        $this->json="{'response':'ok','node':'-1'}";
        return TRUE; 
    }
    
    /**
     * Creates a new node based on the previously set member variables
     * 
     * @param $clip This is used when pasting a node from the clipboard
     */
    function NewNode($clip = false){
    	global $db;
		if($this->blocked()){
    		$this->json="{'response':'You do not have the authority to add pages'}";
			return;
    	}
        $sql = "SELECT parent_id, node_position FROM comm_wiki_node WHERE node_id=".$this->target;
        try{
            $result = $db->query($sql);
            
        }catch(Exception $e){
            $this->histid=-1; 
            $this->json="{'response':'Database Error, Invalid Target'}";
            return;
        }

        $parent_id = mysql_result($result, 0, 'parent_id');
        $node_position = mysql_result($result, 0, 'node_position');
        $newpos = 0;
    
        if($this->position == 'before'){
            $newpos = $node_position;
            $targetpos = $node_position + 1;
        }
        
        if($this->position == 'after'){
            $newpos = $node_position + 1;
            $targetpos = $node_position;        
        }
    
        if($this->position == 'in'){
            // insert in folder at the end
            $sql = "SELECT max(node_position) AS lastnode FROM comm_wiki_node WHERE parent_id=".$this->target;
            try{
                $result = $db->query($sql);
                
            }catch(Exception $e){
                $this->histid=-1; 
                $this->json="{'response':'Database Error, Unable to retrieve position'}";
                return;
            }

            if(mysql_num_rows($result) > 0){
                $last = mysql_result($result, 0, 'lastnode')+1;     
                $newpos = $last;
            }
            
            $targetpos = $node_position;    
            $parent_id = $this->target; 
        }else{
            // shift nodes up before inserting and updating target
            $sql = "UPDATE comm_wiki_node SET node_position=node_position+1 WHERE node_position>$node_position AND parent_id=$parent_id";
            try{
                $result = $db->query($sql);
                
            }catch(Exception $e){
                $this->histid=-1; 
                $this->json="{'response':'Database Error, Unable to update node position'}";
                return;
            }
        }
        
        if($targetpos != $node_position){
            $sql = "UPDATE comm_wiki_node SET node_position=$targetpos WHERE node_id=".$this->target;
            try{
                $result = $db->query($sql);
                
            }catch(Exception $e){
                $this->histid=-1; 
                $this->json="{'response':'Database Error, Unable to update node position'}";
                return;
            }
        }
        
        if($clip){
            $sql = "UPDATE comm_wiki_node SET node_position=$newpos, parent_id=$parent_id WHERE node_id=$clip";
            try{
                $result = $db->query($sql);
                
            }catch(Exception $e){
                $this->histid=-1; 
                $this->json="{'response':'Database Error, Unable to paste node'}";
                return;
            }
            $new_id = $clip;
        }else{
            $sql = "INSERT INTO comm_wiki_node VALUES(NULL,$parent_id, ".$this->grp_id.",'".$this->name."' ,'".$this->tags."',".$_SESSION['id_comm'].", $newpos, 0, ".$this->right.")";
            try{
                $result = $db->query($sql);
                
            }catch(Exception $e){
                $this->histid=-1; 
                $this->json="{'response':'Database Error, Unable to insert node'}";
                return ;
            }
            $new_id = mysql_insert_id();
            
            $sql = "INSERT INTO comm_wiki_page VALUES($new_id, '','$this->name','*$this->name*',0)";
            try{
                $result = $db->query($sql);
                
            }catch(Exception $e){
                $this->histid=-1; 
                $this->json="{'response':'Database Error, Unable to create page'}";
                return;
            }
        }
        
        $this->histid = $new_id;
        $this->json = "{'response':'ok','node':'$new_id'}"; 
    }
    
    /**
     * Finds the parent id and the label of a node
     * 
     * @param $id   The id of the node
     * @return  array with the parent id and the label of the node
     */
    function ParentFromId($id){
       global $db;
	   $sql = "SELECT parent_id,page.label FROM comm_wiki_node INNER JOIN comm_wiki_page ON comm_wiki_node.node_id=comm_wiki_page.node_id WHERE comm_wiki_page.node_id=$id";
        try{
            $result = $db->query($sql);
			if(mysql_num_rows($result) > 0){
				$pid = mysql_result($result, 0, 'parent_id');               
				$lab = mysql_result($result, 0, 'comm_wiki_page.label');   
			}
        }catch(Exception $e){
            $this->json = "{'response':'".mysql_error()."'}";
            return array("pid"=>"", "label"=>"");
        }

        return array("pid"=>$pid, "label"=>$lab);
    }
    
    /**
     * Gets the full path of a node
     * 
     * @param $id   The id of the node
     * @return  The full path
     */
    function PathFromId($id){
        $chunks = array();
        while($id != $this->root){
            $info = $this->ParentFromId($id);
            $id = $info['pid'];
            if($id == 0)  // deleted page
                break;
            $chunks[] = $info['label'];
            $cnt++;
        }
        $chunks = array_reverse($chunks);
        
        foreach($chunks as $chunk){
            $path.="/$chunk";
        }

        return $path;
    }
    
    /**
     * Adds a redirect to the database that will be used to send to an id from the path
     * 
     * @param $path The path to be inserted
     * @param $id   The id of the node to be inserted
     */
    function AddRedirect($path,$id){
        global $db;
		if($path == '') return;
        $sql = "INSERT INTO comm_wiki_redirect VALUES('$path', '$this->lang', $id)";
        try{
            $db->query($sql);
        }catch(Exception $e){
            $this->json = "{'response':'".mysql_error()."'}";
        }
    }
    
    /**
     * Checks to see if the path is in the redirect table.  
     * This is only checked if path does not exist
     * 
     * @param $path The path to be redirected
     * @return  The id to redirect to if found, if not -1
     */
    function CheckRedirect($path){
       global $db;
	   $path = preg_replace("/([a-z])([A-Z])/", "$1 $2", $path); // wiki wordified path
        $sql = "SELECT * FROM `comm_wiki_redirect` "
                ."INNER JOIN `comm_wiki_node` ON comm_wiki_node.node_id=comm_wiki_redirect.redirect_id "
                ."WHERE redirect_path='$path'";
        try{
            $result = $db->query($sql); 
            if(mysql_num_rows($result)>0) {
                $id = mysql_result($result, 0, 'redirect_id');
                $pid = mysql_result($result, 0, 'parent_id');
                if($pid > 0) return $id;
            }
        }catch(Exception $e){
            $this->json = "{'response':'".mysql_error()."'}";
            return -1;
        }
        
        return -1;
    }
}
?>