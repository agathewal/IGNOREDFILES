<?php
require_once("../config.php");

$donnees=array();
$donnees['success']=0;
$donnees['can_edit']=0;

$page = $_POST['id'];
if(is_numeric($page)){

	$sql = "SELECT comm_wiki_page.page_text,comm_wiki_page.label,comm_wiki_node.id_user,type_right FROM comm_wiki_page INNER JOIN comm_wiki_node ON comm_wiki_node.node_id=comm_wiki_page.node_id WHERE comm_wiki_node.node_id=$page";
	$result = $db->query($sql); 
	
	if(mysql_num_rows($result)!=0){
		$info_page=mysql_fetch_array($result);
		$info_page=array_map('stripslashes',$info_page);
		
		if($info_page['type_right']==2 || $info_page['type_right']==3){
			if($info_page['id_user']==$_SESSION['id_comm'])$donnees['can_edit']=1;
			$is_member=$db->countOf('comm_wiki_share_page_user','node_id = '.$page.' AND id_user = '.$_SESSION['id_comm']);
			if($is_member>0)$donnees['can_edit']=1;
			
		}else $donnees['can_edit']=1;
	
		$donnees['success']=1;
		$donnees['page']=$info_page;
	}
	
}

echo json_encode($donnees);
?>