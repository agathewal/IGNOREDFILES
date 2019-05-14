<?php
require_once(dirname(__FILE__).'/../class/class.simplediff.php');
require_once(dirname(__FILE__).'/../class/tags.class.php');
require_once(dirname(__FILE__).'/../config.php');


function html_diff($from, $to, $page, $type, $istext=false)
{
    
	global $con,$lang,$db;
	
	if(!$istext){
		if($to > 0){
			$sql = "SELECT page_text FROM `comm_wiki_revision` WHERE revision_id=$to";
			$result = $db->query($sql) or die("Database Error - Unable to save page.");
			$new = stripslashes(mysql_result($result,0,'page_text'));
		}else{
			$tagger = new Tag($con,$page,$lang);
			if($type=='tag'){	
				$new = $tagger->getTags("csv");
			}else{
				$sql = "SELECT page_text FROM `comm_wiki_page` WHERE node_id=$page";
				$result = $db->query($sql) or die("Database Error - Unable to save page.");
				$new = stripslashes(mysql_result($result,0,'page_text'));	
			}
		}


		$old='';
		if($from>0){
			$sql = "SELECT page_text FROM `comm_wiki_revision` WHERE revision_id=$from";
			$result = $db->query($sql) or die("Database Error - Unable to save page.");
			$old = stripslashes(mysql_result($result,0,'page_text'));
		}
	}else{
		$old = $from;
		$new = $to;
	}
	//------------------

    $diff = simpleDiff::diff_to_array(false, $old , $new, 2);

    $out = '<table class="diff">';
    $prev = key($diff);
    $il = $ir = $prev;

    foreach ($diff as $i=>$line)
    {
        if ($i > $prev + 1)
        {
            $out .= '<tr><td colspan="6" class="separator"><hr class="break" /></td></tr>';

            // determine where to start renumbering
            $it = ($ir - $il);              // difference between left and right number
            $il = $it <=0 ? $i : $i - $it ; // if left side is bigger, use $i for left, else adjust by the difference
            $ir = $il + $it;                // add the differenct to the right side

        }

        list($type, $old, $new) = $line;

        $class1 = $class2 = '';
        $t1 = $t2 = '';

        if ($type == simpleDiff::INS)
        {
            $class2 = 'ins';
            $t2 = '+';
        }
        elseif ($type == simpleDiff::DEL)
        {
            $class1 = 'del';
            $t1 = '-';
        }
        elseif ($type == simpleDiff::CHANGED)
        {
            $class1 = 'del';
            $class2 = 'ins';
            $t1 = '-';
            $t2 = '+';

            $lineDiff = simpleDiff::wdiff($old, $new);

            // Don't show new things in deleted line
            $old = preg_replace('!\{\+(?:.*)\+\}!U', '', $lineDiff);
            $old = str_replace('  ', ' ', $old);
            $old = str_replace('-] [-', ' ', $old);
            $old = preg_replace('!\[-(.*)-\]!U', '<del class="hist">\\1</del>', $old);

            // Don't show old things in added line
            $new = preg_replace('!\[-(?:.*)-\]!U', '', $lineDiff);
            $new = str_replace('  ', ' ', $new);
            $new = str_replace('+} {+', ' ', $new);
            $new = preg_replace('!\{\+(.*)\+\}!U', '<ins class="hist">\\1</ins>', $new);
        }

	$ild = ''; $ird = ''; // line numbers to display

	// left side increments
	if($t1=='-') {
		$il++; 
		$ild = $il; // set value for display
	}

	// both sides increment
	if($t1=='' && $t2=='') {
		$il++; 
		$ild = $il;
		$ir++; 
		$ird = $ir;
	}

	// right side increments
	if($t2=='+') {
		$ir++; 
		$ird = $ir;
	}

        $out .= '<tr>';
        $out .= '<td class="line">'.$ild.'</td>';
        $out .= '<td class="leftChange">'.$t1.'</td>';
        $out .= '<td class="leftText '.$class1.'">'.$old.'</td>';
        $out .= '<td class="line">'.$ird.'</td>';
        $out .= '<td class="rightChange">'.$t2.'</td>';
        $out .= '<td class="rightText '.$class2.'">'.$new.'</td>';
        $out .= '</tr>';

        $prev = $i;
    }

    $out .= '</table>';
	

    return $out;
}
	

?>