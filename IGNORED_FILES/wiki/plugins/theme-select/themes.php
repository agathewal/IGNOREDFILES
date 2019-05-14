<?php
    $cur = $_GET['cur'];
    echo "<select id='seltheme' onchange='theme_select.replacestyles()'>\n";
	$handle=opendir("../../theme");
	while ($file = readdir($handle)) {
		if($file!="." && $file!=".."){
			$selected = $cur==$file ? ' selected' : '' ;
        	echo " <option value='$file'$selected>$file</option>\n";
		}
	}
	closedir($handle);
 ?>
