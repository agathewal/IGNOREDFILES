<?php

if($_SESSION['token'] != $_POST['token'] || !isset($_SESSION['token'])) {
   exit; // no automated script attacks
}

$image= $_FILES["imagefile"]['name'];

$isimage = FALSE;
$whitelist = array(".png", ".gif", ".jpg", ".jpeg");
foreach ($whitelist as $item) {
   if(preg_match("/$item\$/i", $image)) {
   	   $isimage = TRUE;
       break;
   }
}

if($isimage == FALSE) exit;

if($image<>""){

    $image_path = "../images/";

    $target_path = $image_path . basename( $_FILES['imagefile']['name']);
    $thumb_path = "{$image_path}thumbs/" . basename( $_FILES['imagefile']['name']); 

    if(move_uploaded_file($_FILES['imagefile']['tmp_name'], $target_path)) {

        // create thumbnail
        $size = getimagesize($target_path);
        
        // only create if image is larger than thumbnail size
        if ($size[0] > 100 || $size[1] > 100) {
            createthumb($target_path,$thumb_path,100,100);
        }
        // else the original will be displayed in the image page

    } else{
    	echo "{'response':'Error uploading image, please try again'}";
    	exit;
    }

}else{
	echo "{'response':'No image file'}";
	exit;
}

echo "{'response':'ok'}";

// function modified from http://icant.co.uk/articles/phpthumbnails/
function createthumb($name,$filename,$new_w,$new_h)
{
    ini_set("memory_limit","68M");
	$system=explode(".",$name);
	if (preg_match("/jpg|jpeg/i",$system[count($system)-1])){$src_img=imagecreatefromjpeg($name);}
	if (preg_match("/png/i",$system[count($system)-1])){$src_img=imagecreatefrompng($name);}

	$old_x=imagesx($src_img);
 	$old_y=imagesy($src_img);
	if ($old_x > $old_y) 
	{
		$thumb_w=$new_w;
		$thumb_h=$old_y*($new_h/$old_x);
	}
	if ($old_x < $old_y) 
	{
		$thumb_w=$old_x*($new_w/$old_y);
		$thumb_h=$new_h;
	}
	if ($old_x == $old_y) 
	{
		$thumb_w=$new_w;
		$thumb_h=$new_h;
	}

	$dst_img=ImageCreateTrueColor($thumb_w,$thumb_h);
	imagecopyresampled ($dst_img,$src_img,0,0,0,0,$thumb_w,$thumb_h,$old_x,$old_y); 
	if (preg_match("/png/i",$system[count($system)-1]))
	{
		imagepng($dst_img,$filename); 
	} else {
		imagejpeg($dst_img,$filename); 
	}
	imagedestroy($dst_img); 
	imagedestroy($src_img); 
}

?>






	