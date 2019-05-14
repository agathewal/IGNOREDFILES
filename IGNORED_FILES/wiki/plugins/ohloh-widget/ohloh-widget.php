<?php
	$src = $_GET['src'];
	$content = file_get_contents("http://www.ohloh.net/p/".$src);
	$content=str_replace("\\n","\n",$content);
	$content=str_replace("\\","",$content);
	$content=substr($content,16,strlen($content)-18); // trim ends -- document.write(' ... \n') -- \n already stripped
	echo $content;
?>
