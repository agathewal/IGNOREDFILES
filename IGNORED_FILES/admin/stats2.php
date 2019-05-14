<?
$variable_get="";
$i=0;
foreach($_GET as $key => $value){
	if($i!=0)$variable_get.="&";
	$variable_get.=$key."=".$value;
	$i++;
}
$lines = file ('http://www.nexinet.fr/cgi-bin/awstats.pl?'.$variable_get);
// Affiche toutes les lignes du tableau comme code HTML, avec les numéros de ligne
foreach ($lines as $line_num => $line){			
	echo str_replace('awstats.pl','stats2.php',$line);		
}
?>