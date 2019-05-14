<?php

class Tag{
	
    var $con;       // the sql connection
    var $node_id;   // the id of the node
	var $language;

    /**
     * Consructor
     * 
     * @param $con  The connection to the mysql database
     * @param $id The id of the node associated with the tag
     */
    function Tag($con,$id,$lang){
        $this->con = $con;
        $this->node_id = $id;
		$this->language = $lang;
    }
    
    /**
     * Returns the tags for a node from the database
     * 
     * @param $fmt "array" or "csv"
     * @return the tags for a node as array or csv
     */
    function getTags($fmt="array"){
	global $db;
		$sql = "SELECT comm_wiki_tag.tag FROM comm_wiki_tagxref INNER JOIN comm_wiki_tag ON comm_wiki_tagxref.tag_id = comm_wiki_tag.tag_id WHERE comm_wiki_tagxref.node_id=".$this->node_id." ORDER BY comm_wiki_tag.tag";
		$result = $db->query($sql) or die("Database Error - ".mysql_error());
		
		if(mysql_num_rows($result) == 0) return $fmt=="array" ? array() : "";
		
		$dbtags = "";
		for($r=0;$r<mysql_num_rows($result);$r++){
		    $dbtags.=  ",".mysql_result($result, $r, 'tag');
		}
		
		$dbtags = substr($dbtags, 1);
		
		return $fmt=="array" ? explode(",",$dbtags) : $dbtags;      
    }
    
    /**
     * Saves tags to the database from a csv string
     * 
     * @param $csv non escaped string of tags to save
     */
    function saveTags($csv){
		global $db;
		$tags = explode(",",$csv);
		$oldtags = $this->getTags();
		
		foreach($tags as $tag){
		    $tag = trim($tag);
		    if($this->in_arrayi($tag,$oldtags)){
		        $key = array_search($tag,$oldtags);
		        unset($oldtags[$key]); // remove from temporary array
		    }else{
		        // check to see if tag exists
			$tag = mysql_real_escape_string(str_replace("\\","\\\\",$tag));
		        $sql = "SELECT tag_id FROM comm_wiki_tag WHERE tag='$tag'";
		        $result = $db->query($sql) or die("{'response':'Database Error 1 - '}");
		        if(mysql_num_rows($result) > 0){
		            $tag_id = mysql_result($result,0, 'tag_id');
		            $sql = "INSERT INTO comm_wiki_tagxref (tagxref_id,tag_id,node_id) VALUES('',$tag_id,$this->node_id)";
		            $result = $db->query($sql) or die("{'response':'Database 2 Error - Unable to insert tag.'}");
		        }else{
		            $sql = "INSERT INTO comm_wiki_tag VALUES('','$tag')";
		            $result = $db->query($sql) or die("{'response':'Database Error 3 - Unable to insert tag.'}");
		            $tag_id = mysql_insert_id();
		
		            $sql = "INSERT INTO comm_wiki_tagxref (tagxref_id,tag_id,node_id) VALUES('',$tag_id,$this->node_id)";
		            $result = $db->query($sql) or die("{'response':'Database Error 4 - Unable to insert tag. $sql'}");
		            
		        }
		    }
		
		}
		
		// any old tags to be removed?
		
		foreach($oldtags as $tag){
			$tag = trim($tag);
			$tag = mysql_real_escape_string(str_replace("\\","\\\\",$tag));
			$sql = "SELECT tag_id FROM comm_wiki_tag WHERE tag='$tag'";
			$result = $db->query($sql) or die("{'response':'Database Error 5 - Unable to retrive tags.'}");
		
			if(mysql_num_rows($result) > 0){
				$tag_id = mysql_result($result,0, 'tag_id');
				$sql = "DELETE FROM comm_wiki_tagxref WHERE node_id = $this->node_id AND tag_id = $tag_id";
				$result = $db->query($sql) or die("{'response':'Database 6 Error - Unable to insert tag.'}");
			}   
		}		
	}
	
	function in_arrayi( $needle, $haystack ) { 
	    $found = false; 
	    foreach( $haystack as $value ) { 
	        if( strtolower( $value ) == strtolower( $needle ) ) { 
	            $found = true; 
	        } 
	    }    
	    return $found; 
	} 
    

	
}
?>
