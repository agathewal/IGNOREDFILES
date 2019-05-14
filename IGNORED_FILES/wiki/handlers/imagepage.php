<?php
require_once("../config.php");
include('language.php');

$token = md5(uniqid(rand(), TRUE));
$_SESSION['token'] = $token;


$title = $_GET['title'];
$html =  "<div style='float:left;font-size:18px;'>".$language->images." : </div>";
$html .=  "<div style='float:right;'><div style='position:relative;'>"
			."<a href=\"javascript:previewcancel('$title')\">".$language->cancel."</a></div></div>";
			
$html.="<div style='float:right;margin-right:20px;'>
<form onsubmit='validateupload(\"$title\");' method='post' enctype='multipart/form-data' target='upload_target' action='handlers/uploadimage.php'>
<div id='uploadpanel'>
<input name='imagefile' id='imagefile' size='27' type='file' value='".$language->choosefile."'/>
<input type='hidden' id='token' name='token' value='$token' />
<input id='imagesubmit' type='submit' name='action' value='".$language->upload."' />
</div>
<iframe id='upload_target' name='upload_target' src='' style='width:0;height:0;border:0px solid #fff;' onload='uploadDone()'></iframe>
</form></div>"			;
			
$html .=  "<div style='float:right;'><div style='position:relative;margin-right:20px;'>"
			."<div id='up' style='position:absolute;left:-300px;z-index:100;display:none;'></div>"
			."</div></div>";
$html .=  "<span style='display:none' id='holder' ></span>";
$html .=  "<div style='height:100%;clear:both;'>";

$ip=$_SERVER['REMOTE_ADDR'];

$directory = scandir('../images');
natcasesort($directory);

foreach ($directory as $file){

    if ($file != '.' && $file != '..'){
        
        $relativefile = "../images/$file";
        $thumbfile = "../images/thumbs/$file";
        $displayfile = "images/$file";
        if(is_dir($relativefile)) continue;
        $width = "";
        
        if(file_exists(realpath($thumbfile))){
            $displayfile = "images/thumbs/$file";
        }else{
            $size = getimagesize($relativefile);
    		if($size == NULL) continue; // not an image
    		
    		$x = $size[0];
    		$y = $size[1];
    		$aspect = $x / $y;
    		
     		if($x > 100  && $aspect >= 1){
    			$x = 100;
    		}
    		else if($y > 100  && $aspect < 1){
    			$x = intval(100 * $aspect); 
    		}
            $width = "width:{$x}px;";
        }

        $html .= "<div style='float:left;text-align:center;'><div style='height:120px;margin:12px 2px 2px 2px;border:1px solid black'><a href=\"javascript:sysclipboard('../images/$file')\">"
		."<img src='$displayfile' style='{$width}margin:12px;' title='$file' /></a><div style='clear:both;'></div></div>"
        ."<div style='width:150px;overflow:hidden;'>$file</div></div>";
    	
    }
}

echo $html;
?>
