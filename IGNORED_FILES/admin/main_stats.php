<?
$homepage = file_get_contents('http://www.nexinet.fr/cgi-bin/awstats.pl?config=www.nexinet.fr&lang=fr');
echo str_replace('awstats.pl','stats2.php',$homepage);
?>