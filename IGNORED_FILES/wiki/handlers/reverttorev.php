<?php
require_once("../config.php");

$rev = $_POST['rev'];
$donnees=array();
$donnees['success']=0;

if(is_numeric($rev)){

	$comment =$_POST['comment'];
	$sql = "SELECT page_text,node_id FROM comm_wiki_revision WHERE revision_id=$rev";
	$result = $db->query($sql);

	if(mysql_num_rows($result)!=0){
	
		$page_text = mysql_result($result,0,'page_text');
		$node_id = mysql_result($result, 0, 'node_id');
		$donnees['node_id']=$node_id;
		
		$sql = "SELECT page_text FROM comm_wiki_page WHERE node_id=$node_id";
		$result = $db->query($sql);
		
		if(mysql_num_rows($result)!=0){
		
			$bad_text = mysql_result($result,0,'page_text');

			//if($bad_text == $page_text){
			//	echo "<script>alert()</script>";
			//	$donnees['success']=2;
			//}else{

				$donnees['success']=1;
				$page_text = addslashes($page_text);
				$sql = "UPDATE comm_wiki_page SET page_text='$page_text' WHERE node_id=$node_id";
				$result = $db->query($sql);

				$now = date('YmdHis');
				$bad_text = addslashes($bad_text);
				$sql = "INSERT INTO `comm_wiki_revision` (revision_id,node_id,user_id, `type`, page_text, comment,revision_time) ";
				$sql .= "VALUES ('',$node_id,".$_SESSION['id_comm'].", 'page', '$bad_text', 'Renversement vers la révision $rev : $comment','$now') ";
			
				$result = $db->query($sql);
				$text = stripslashes($page_text);
				
			//}
			
		}
	}
}

echo json_encode($donnees);
?>