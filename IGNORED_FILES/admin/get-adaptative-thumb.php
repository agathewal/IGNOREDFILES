<?php  
require_once '../config/ThumbLib.inc.php';  
require_once '../config/configuration.php';  
$_GET['url']=urldecode($_GET['url']);
$cache_dir="../cache/";

if(isset($_GET['width']) && isset($_GET['height']) && isset($_GET['url'])){	

	if(!is_file($_GET['url'])){
		$_GET['url']=str_replace(ADD_DIR.'/','../',$_GET['url']);		
	}

	$_GET['url']=str_replace(PUSH_DIR,'../../push2/',$_GET['url']);	
	
	$time_actual=filemtime($_GET['url']);
	//echo $time_actual.'<br>';	
	//$size = getimagesize($_GET['url']);
	//pr($size);	
	$temp=pathinfo($_GET['url']);
	$name_final=md5($_GET['url'].$_GET['width'].$_GET['height'])."-resize.".$temp['extension'];
		
	if(isset($temp['extension']) && $temp['extension']!=""){	
		if(is_file($cache_dir.$name_final)){		
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
			$thumb = PhpThumbFactory::create($cache_dir.$name_final,array('preserveAlpha'=>true));
			$thumb->show();  
			
			*/
			/*
			header('location:'.$cache_dir.$name_final);
			die();*/
	
		/*
			switch($temp['extension']){
				case 'jpg':
				case 'jpeg':
				$im = imagecreatefromjpeg($cache_dir.$name_final); 
				imagejpeg($im,NULL,100);
				header('Content-type: image/jpeg');
				readfile($cache_dir.$name_final);
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
			
			}*/		
				
		}else{						
			if(is_file($_GET['url'])){
				$thumb = PhpThumbFactory::create($_GET['url']);  
				$thumb->adaptiveResize($_GET['width'], $_GET['height']);  
				$thumb->save($cache_dir.$name_final);
				$thumb->show();  
			}else{
				header('location : ../images/pix.gif');
				die();
			}			
		}
	}
	else{
		header('location : ../images/pix.gif');
		die();
	}
}	
else{
	header('location : ../images/pix.gif');
	die();
}
?>  