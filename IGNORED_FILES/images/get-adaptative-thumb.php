<?php  
header("Access-Control-Allow-Origin: *"); 
require_once '../config/ThumbLib.inc.php';  
require_once '../config/configuration.php';  
$_GET['url']=urldecode($_GET['url']);
$cache_dir="../cache/";

if(isset($_GET['width']) && isset($_GET['height']) && isset($_GET['url'])){	

	
	if(!is_file($_GET['url']) && ADD_DIR!='')$_GET['url']=str_replace(ADD_DIR.'/','../',$_GET['url']);
	
	$_GET['url']='..'.$_GET['url'];
	
	$time_actual=filemtime($_GET['url']);
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
			
				$types = array (
					 'jpg'  => 'image/jpeg',
					 'jpeg' => 'image/jpeg',
					 'png'  => 'image/png',
					 'gif'  => 'image/gif'
				);
				if (strlen ($temp['extension']) && strlen ($types[$temp['extension']])) {
					$mime_type = $types[$temp['extension']];
				}
				$fileSize = filesize ($cache_dir.$name_final);
				
				$gmdate_mod = gmdate ("D, d M Y H:i:s", filemtime($cache_dir.$name_final));
				if (! strstr ($gmdate_mod, "GMT")) {
					$gmdate_mod .= " GMT";
				}
				
				header ('Content-Type: ' . $mime_type);
				header ('Accept-Ranges: bytes');
				header ('Last-Modified: ' . $gmdate_mod);
				header ('Content-Length: ' . $fileSize);
				header ('Cache-Control: max-age=9999, must-revalidate');
				header ('Expires: ' . $gmdate_mod);
				readfile($cache_dir.$name_final);
				die();
			}
		
							
		}else{						
			if(is_file($_GET['url'])){
				$thumb = PhpThumbFactory::create($_GET['url']);  
				$thumb->adaptiveResize($_GET['width'], $_GET['height']);  
				$thumb->save($cache_dir.$name_final);
				$thumb->show();  
			}else{
				readfile('pix.gif');
				die();
			}			
		}
	}
	else{
		readfile('pix.gif');
		die();
	}
}else if(isset($_GET['width']) && isset($_GET['url'])){
	
	if(!is_file($_GET['url']) && ADD_DIR!='')$_GET['url']=str_replace(ADD_DIR.'/','../',$_GET['url']);
	$_GET['url']='..'.$_GET['url'];
	
	$time_actual=filemtime($_GET['url']);	
	$temp=pathinfo($_GET['url']);
	$name_final=md5($_GET['url'].$_GET['width'])."-resize.".$temp['extension'];

	if(isset($temp['extension']) && $temp['extension']!=""){	
		if(is_file($cache_dir.$name_final)){		
			$time_cache=filemtime($cache_dir.$name_final);
			if($time_cache<$time_actual){
				if(is_file($_GET['url'])){
					$thumb = PhpThumbFactory::create($_GET['url']);  
					$thumb->resize($_GET['width']);  
					$thumb->save($cache_dir.$name_final);
					$thumb->show();
				}
			}else{	
			
				$types = array (
					 'jpg'  => 'image/jpeg',
					 'jpeg' => 'image/jpeg',
					 'png'  => 'image/png',
					 'gif'  => 'image/gif'
				);

				if (strlen ($temp['extension']) && strlen ($types[$temp['extension']])) {
					$mime_type = $types[$temp['extension']];
				}
				$fileSize = filesize ($cache_dir.$name_final);
				
				$gmdate_mod = gmdate ("D, d M Y H:i:s", filemtime($cache_dir.$name_final));
				if (! strstr ($gmdate_mod, "GMT")) {
					$gmdate_mod .= " GMT";
				}
				
				header ('Content-Type: ' . $mime_type);
				header ('Accept-Ranges: bytes');
				header ('Last-Modified: ' . $gmdate_mod);
				header ('Content-Length: ' . $fileSize);
				header ('Cache-Control: max-age=9999, must-revalidate');
				header ('Expires: ' . $gmdate_mod);
				readfile($cache_dir.$name_final);
				die();
			}
		
							
		}else{						
			if(is_file($_GET['url'])){
				$thumb = PhpThumbFactory::create($_GET['url']);  
				$thumb->resize($_GET['width']);  
				$thumb->save($cache_dir.$name_final);
				$thumb->show();  
			}else{
				readfile('pix.gif');
				die();
			}			
		}
	}
	else{
		readfile('pix.gif');
		die();
	}
	
}	
else{
	readfile('pix.gif');
	die();
}
?>  