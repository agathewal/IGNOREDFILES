<?php
require_once("../config.php");
require_once("../class/class.simplediff.php"); 
include("./editcheck.php");

$page = $_SESSION['wiki_en_cours'];
$offset = $_POST['offset']; // page offset

if(is_numeric($_POST['mobile']))$mobile = $_POST['mobile']; // page mobile
else $mobile=0;
if(!is_numeric($page) || !is_numeric($offset)) exit; // extra precaution

$editable=false;
$sql = "SELECT comm_wiki_node.id_user,type_right FROM comm_wiki_node WHERE comm_wiki_node.node_id=$page";
$result = $db->query($sql); 

if(mysql_num_rows($result)!=0){

	$info_page=mysql_fetch_array($result);
	//pr($info_page);
	$info_page["page_text"]=nl2br($info_page["page_text"]);
	if($info_page['type_right']==2 || $info_page['type_right']==3){
		if($info_page['id_user']==$_SESSION['id_comm'])$editable=true;
		$is_member=$db->countOf('comm_wiki_share_page_user','node_id = '.$page.' AND id_user = '.$_SESSION['id_comm']);
		if($is_member>0)$editable=true;
		
	}else $editable=true;
	
}

if($offset == 0) include("htmldiff.php");

$sql = "SELECT label FROM comm_wiki_page WHERE node_id=$page";
$result = $db->query($sql) or die("Database Error - Unable to save page.");

if(mysql_num_rows($result) > 0){
	$label = mysql_result($result,0,'label');
}else{
	$sql = "SELECT label FROM comm_wiki_node WHERE node_id=$page";
	$result = $db->query($sql) or die("Database Error - Unable to save page.");
	$label = mysql_result($result,0,'label');
}

// paging calcs
$sql = "SELECT COUNT(*) AS cnt FROM comm_wiki_revision WHERE node_id=$page;";
$result = $db->query($sql) or die("$sql ".mysql_error());
$nrows = mysql_result($result,0,'cnt') -1 ;
$limit = 3;
$npages = ceil($nrows/$limit);
$off = $offset * $limit; // query offset

if($mobile==0)echo "<div class='titre_group' style='font-weight:bold;'>$label</div><div style='margin-top:10px;margin-bottom:10px;width:100%;' id='ligne_separation'></div>";
$next = $offset + 1;
$prev = $offset - 1;

// nav buttons
$imgp = $imgn = "";
if($nrows > 0 && ($prev > -1 || $next < $npages)){

	echo "<div style='text-align:center;'>";
	
	//$imgp = "<span><img src='images/system/no-previous-view.png' alt='previous'/></span>&nbsp;";
	//$imgp ='<div class="pagination_arrow bordure_img"><img src="'.URL_DIR.'/images/left-1-opac.png"></a></div>';
	if($mobile==0){
	/*	$imgp ='
		<div style="width:98px;float:left;border:1px solid #CCC;height:20px;">
			<div class="text_button_comm">'._("Précédent").'</div>
			<div class="icon_button_comm"><img src="'.URL_DIR.'/images/arrow_left.png"></div>
		</div>	';
		*/
	}else{
	
		$imgp ="
		<div style='float:left;' class='pagination_mobile'><img src=\"".URL_DIR."/images/arrow_left.png\" style='margin-top: 2px;'></div>";
		
	}
	
	if($prev > -1)
		//$imgp = "<a href='javascript:gethistory($prev)'><img src='images/system/go-previous-view.png'  style='border:none;' alt='previous' /></a>&nbsp;";
		
		//$imgp ='<div class="pagination_arrow bordure_img" style="background: #EEE;"><a href="javascript:gethistory('.$prev.')"><img src="'.URL_DIR.'/images/left-1.png"></a></div>';
		if($mobile==0){
			/*$imgp ='
			<div class="button_comm" style="width:98px;float:left;" onclick="javascript:gethistory('.$prev.');">
				<div class="text_button_comm">'._("Précédent").'</div>
				<div class="icon_button_comm"><img src="'.URL_DIR.'/images/arrow_left.png"></div>
			</div>';*/
		}else{
			$imgp ="
			<div style='float:left;' class='pagination_mobile background_pagination_mobile_active' onclick='javascript:gethistoryMobile(".$prev.",".$page.");'><img src=\"".URL_DIR."/images/arrow_left.png\" style='margin-top: 2px;'></div>";

		}
	
	//$imgn = "&nbsp;<span><img src='images/system/no-next-view.png' alt='next' /></span>";
	//$imgn ='<div class="pagination_arrow bordure_img" style="float:right;"><img src="'.URL_DIR.'/images/right-1-opac.png"></a></div>';
	if($mobile==0){
		/*$imgn ='<div style="width:98px;float:right;border:1px solid #CCC;height:20px;">
		<div class="text_button_comm">'._("Suivant").'</div>
		<div class="icon_button_comm"><img src="'.URL_DIR.'/images/arrow_right.png"></div>
		</div>';*/
	}else{
		$imgn ="
		<div style='float:right;' class='pagination_mobile' ><img src=\"".URL_DIR."/images/arrow_right.png\" style='margin-top: 2px;'></div>";
	}
	if($next < $npages)
		if($mobile==0){
			/*$imgn ='<div class="button_comm" style="width:98px;float:right;" onclick="javascript:gethistory('.$next.');">
				<div class="text_button_comm">'._("Suivant").'</div>
				<div class="icon_button_comm"><img src="'.URL_DIR.'/images/arrow_right.png"></div>
			</div>';*/
		}else{		
			$imgn ="
			<div style='float:right;' class='pagination_mobile background_pagination_mobile_active' onclick='javascript:gethistoryMobile(".$next.",".$page.");'><img src=\"".URL_DIR."/images/arrow_right.png\" style='margin-top: 2px;'></div>";
		}
		//$imgn ='<div class="pagination_arrow bordure_img" style="float:right;background: #EEE;"><a href="javascript:gethistory('.$next.')"><img src="'.URL_DIR.'/images/right-1.png"></a></div>';
		/*$imgn = "&nbsp;<a href='javascript:gethistory($next)'><img src='images/system/go-next-view.png' style='border:none;'  alt='next' /></a>";*/
	
	echo $imgp.$imgn.'<div class="clear"></div>';
	
	echo "</div>";
}

$sql = "SELECT * FROM comm_wiki_revision  ";
$sql.= "WHERE node_id=$page ORDER BY revision_time DESC";
$result = $db->query($sql) or die(mysql_error());
$cnt = mysql_num_rows($result);

$previous = $displayrev = _('Actuel');
$revision = _('Révision');

// first page shows current revision, all others show the next revision to restore so we need to get our starting point
$lastpage = 0; 
if($offset > 0 && $cnt > 0){
    $firstrev = mysql_result($result, 0, 'revision_id');
    $sql = "SELECT * FROM comm_wiki_revision WHERE node_id=$page AND `type`='page' AND revision_id > $firstrev ORDER BY revision_time LIMIT 0,1";
   // echo $sql;
    $r2 = $db->query($sql) or die(mysql_error());
	if(mysql_num_rows($r2) > 0){
	    $lastpage = mysql_result($r2, 0, 'revision_id');
	}
}

$lasttag=0;
if ($nrows!=0){
for($i=1;$i<$cnt;$i++){

    $rev = mysql_result($result, $i, 'revision_id');
    $rt = mysql_result($result, $i, 'revision_time');
    $uid = mysql_result($result, $i, 'user_id');
    $ip = mysql_result($result, $i, 'user_ip');
    $type = mysql_result($result, $i, 'type');
	
	$rta = explode(" ",$rt);
	
	$date_deb=explode('-',$rta[0]);
	$heure_deb=explode(':',$rta[1]);	
	$time_deb=mktime($heure_deb[0], $heure_deb[1], $heure_deb[2], $date_deb[1], $date_deb[2], $date_deb[0]);
    
    $comment = mysql_result($result, $i, 'comment');
	$user_data = get_user_data($uid);
    $revert = $display = "";   
    if($i > 0 || $offset > 0) $displayrev = $type=="page" ? $lastpage : "";
    if($type=="page"){
        if($lastpage == 0){
            $revno = "$revision : "._('Actuel');
        }else{
 
            $revno= $lastpage != 0 ? "$revision : $displayrev" : "$revision : "._('Actuel');
            
            if($editable){
				if($mobile==0){
					$revert = $i==0 && $offset == 0 ? "" : "<span class='histrevert'><a href='javascript:revert($lastpage);' title='"._("Retourner à cette version permets de rétablir cette version du document, la version actuelle restera disponible dans l\'historique")."'>"._('Retourner à cette version')."</a></span>";
				}else{
					$revert = $i==0 && $offset == 0 ? "" : "<span class='histrevert'><a href='javascript:revertMobile($lastpage);' >"._('Retourner à cette version')."</a></span>";
				}
			}
            
			
            if($mobile==0){
				$display = $i==0 && $offset == 0 ? "" : "<span class='histdisplay'><a href='javascript:getrev($lastpage,\"$rt\",\"$offset\");'>"._('Aperçu')."</a></span>";
			}
			else {
				$display = $i==0 && $offset == 0 ? "" : "<span class='histdisplay'><a href='javascript:getrevMobile($lastpage,\"$rt\",\"$offset\",$page);'>"._('Aperçu')."</a></span>";
			}
			if($revert!='')$display='<span class="separation_point_cmd">.</span>  '.$display;
        }
    }else{
       
	$revno = "$revision : <span style='color:#f00;'>"._('Mots clés')."</span>";
    }
    
	
	$compare = $type=="page" ? $lastpage : $lasttag;
    $cls = $offset==0 && $i==0 ? 'histexpand' : 'histcollapse';
  
	if($mobile==1){
		echo "<div class='histrow'>
			<div class='histrev'><a class='$cls title_notif' id='histlink_$rev' href='javascript:togglediff($compare,$rev,$page,\"$type\",\"histlink_$rev\")'>$revno</a> </div>
			<div class='histdate'>".format_date_diff($time_deb)."</div>
			<div class='histuser'> "._('par')." <a href='".URL_DIR."/".$user_data['USERNAME']."' class='title_notif'>".$user_data['DISPLAY_NAME']."</a></div>
			<div class=\"btn_icon_right\" onclick='javascript:togglediff($compare,$rev,$page,\"$type\",\"histlink_$rev\");' style='float:right;'><img src=\"".URL_DIR."/images/btn_glass_search.png\"></div>
			<div class='clear'></div> ".
			"<div class='commande-comm' style='margin-top: 5px;'> $revert  $display</div>";
	}
	else  {
		//if (($lastpage>0)){
		echo "<div class='histrow'>
			<div class='histrev'><a class='$cls title_fst_color' id='histlink_$rev' href='javascript:togglediff($compare,$rev,$page,\"$type\",\"histlink_$rev\")'>$revno</a> </div>
			<div class='histdate'>".format_date_diff($time_deb)."</div>
			<div class='histuser'> par <a href='".URL_DIR."/".$user_data['USERNAME']."' class='title_fst_color'>".$user_data['DISPLAY_NAME']."</a></div>
			<div class='button_comm' style='width:148px;float:right;' onclick='javascript:togglediff($compare,$rev,$page,\"$type\",\"histlink_$rev\");'>
				<div class='text_button_comm'>".gettext('Voir les différences')."</div>
				<div class='icon_button_comm'><img src='".URL_DIR."/images/btn_glass_search.png'></div>
			</div>
			<div class='clear'></div> ".
			"<div class='commande_message' style='margin-top: 5px;'> $revert  $display</div>";
	//	}
	}
	
    if($comment !='') echo "<div class='histcomment'>Commentaire : <span style='color:#333;'>$comment</span></div>";
    echo "<div style='clear:both;'></div></div>";
    
	if($offset==0 && $i==0){
		echo "<div id='revdiff_$rev'>";
		echo html_diff($rev, $compare, $page, $type);
	}else{	
		echo "<div id='revdiff_$rev' style='display:none'>";
	}
		
	echo "</div>";
    if($type=="page") {
        $lastpage = $rev;
    } else {
        $lasttag = $rev;
    }
    $previous = "$rev";
}
}
echo "<div style='margin-top:20px;clear:both;'>";

if($nrows==0){
	echo "<div class='normal_text'>"._('Aucun historique')."</div>";

}
echo $imgp.$imgn.'<div class="clear"></div>';

echo "</div>";


?>
