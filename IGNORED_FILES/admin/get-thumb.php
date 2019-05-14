<?php  
require_once '../config/configuration.php';  
require_once '../config/ThumbLib.inc.php';  
$cache_dir="../cache/";

$_GET['url']=urldecode($_GET['url']);
if(isset($_GET['width']) && isset($_GET['height']) && isset($_GET['url']) && $_GET['url']!=''){

	if(!is_file($_GET['url']))$_GET['url']=str_replace(ADD_DIR.'/','../',$_GET['url']);

	$time_actual=filemtime($_GET['url']);
	$temp=pathinfo($_GET['url']);
	$name_final=md5($_GET['url'].$_GET['width'].$_GET['height']).".".$temp['extension'];

	if(isset($temp['extension']) && $temp['extension']!=""){
	
		if(is_file($cache_dir.$name_final)){
		
			
			//echo "la";
			/*
			$thumb = PhpThumbFactory::create($cache_dir.$name_final,array('preserveAlpha'=>true));
			$thumb->show();  */
			$time_cache=filemtime($cache_dir.$name_final);
			if($time_cache<$time_actual){
				if(is_file($_GET['url'])){
					$thumb = PhpThumbFactory::create($_GET['url']);  
					$thumb->adaptiveResize($_GET['width'], $_GET['height']);  
					$thumb->save($cache_dir.$name_final);
					$thumb->show();
				}
			}else{			
				header('location:'.$cache_dir.$name_final);
				die();
			}
			
			/*
			switch($temp['extension']){
				case 'jpg':
				case 'jpeg':
				$im = imagecreatefromjpeg($cache_dir.$name_final);
				header('Content-type: image/jpeg'); 
				imagejpeg($im);
				break;
				
				case 'gif':
				$im = imagecreatefromgif($cache_dir.$name_final);
				header('Content-type: image/gif'); 
				imagegif($im);
				break;
				
				case 'png':
				$im = imagecreatefrompng($cache_dir.$name_final);
				imagealphablending($im, false);
				imagesavealpha($im, true);
				header('Content-type: image/png'); 
				imagepng($im);
				break;
			
			}
			*/
				
		}
		else{
			if(is_file($_GET['url'])){			
				$thumb = PhpThumbFactory::create($_GET['url']);  
				$thumb->resize($_GET['width'], $_GET['height']);  
				$thumb->save($cache_dir.$name_final);
				$thumb->show();
			}else{
				header('location : '.URL_DIR.'/images/pix.gif');
				die();
			}			
		}
	}
	else{
		header('location : '.URL_DIR.'/images/pix.gif');
		die();	
	}
}	
else{
	header('location : '.URL_DIR.'/images/pix.gif');
	die(); 
}
?>  