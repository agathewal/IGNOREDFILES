<?
header('Content-type: application/json');
include('../config/configuration.php');
ob_start();

$cache = 'list_ville.txt'; // un fichier cache pour chaque page de news
$expire = time() - 3600 ; // valable une heure
if(file_exists($cache) && filemtime($cache) > $expire){	
    readfile($cache);
}
else{

	$req=$db->query('select id_ville,nom from comm_maps_ville ORDER BY nom');
	if(mysql_num_rows($req)!=0){
		
		$list_json=array();
		while($don=mysql_fetch_array($req)){
			$data['key']=$don['id_ville'];
			$data['value']=stripslashes($don['nom']);
			$list_json[]=$data;
		}
		echo json_encode($list_json);
	}
	$tampon = ob_get_contents();
	file_put_contents('list_ville.txt', $tampon) ; //pour une meilleure organisation, on créera un répertoire cache pour y stocker les fichiers du cache
	ob_end_clean();
	echo $tampon;
}
?>