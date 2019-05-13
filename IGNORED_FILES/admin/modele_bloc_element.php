<?
$id_module=17;

$ariane_element2['URL']="modele_bloc.php?id=".$_GET['id'];
$ariane_element2['LIBELLE']="Blocs du module";		

$ariane_element3['URL']="modele_bloc.php?id=".$_GET['id'];
$ariane_element3['LIBELLE']="Elements du bloc";					

include('header.php');

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if(isset($_GET['id']) && is_numeric($_GET['id'])){
	$req=$db->query('select * from cms_template_block WHERE ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		$info_req=mysql_fetch_array($req);
	}else{
		header('location:modele.php');
		die();
	}
}else{
	header('location:modele.php');
	die();
}


if($action=='submit'){


	//pr($_POST);	
	//d'abord on capte les informations
	$elements_form=array();
	parse_str($_POST['ordre'], $output);
	
	$req_form_elements=$db->query('SELECT * FROM cms_template_element WHERE ID_BLOC = '.$_GET['id']);
	$list_elements=array();
	if(mysql_num_rows($req_form_elements)!=0){
		while($don=mysql_fetch_array($req_form_elements)){
			$list_elements[]=$don['ID'];
		}
	}
	//pr($list_elements);
	
	while (list ($key, $val) = each ($_POST['name_element']) ) {
 
		if($_POST['default_'.$key]=='undefined')$_POST['default_'.$key]='';
		
		$elements_form[$key]['NAME']=$val;
		$elements_form[$key]['ID_FIELD']=$_POST['id_field_'.$key];
		$elements_form[$key]['ACTUAL_ID']=$_POST['actual_id_'.$key];
		$elements_form[$key]['ORDRE']=array_search($_POST['id_field_'.$key],$output['listItem'])+1;
		$elements_form[$key]['TYPE']=$_POST['type_element_'.$key];
		$elements_form[$key]['DEFAULT']=$_POST['default_'.$key];
		$elements_form[$key]['HELP']=$_POST['help_'.$key];
		$elements_form[$key]['REQUIRED']=$_POST['required_'.$key];	
		if($elements_form[$key]['REQUIRED']=="")$elements_form[$key]['REQUIRED']=0;
		
		
		$array_options=array();
		switch($elements_form[$key]['TYPE']){
			case 1://texte
			$elements_form[$key]['SIZE']=$_POST['size_'.$key];	
			if($elements_form[$key]['SIZE']=="")$elements_form[$key]['SIZE']=0;
			$array_options[]=$elements_form[$key]['DEFAULT'];
			$array_options[]=$elements_form[$key]['SIZE'];
			break;
			
			case 2://paragraphe
			$elements_form[$key]['SIZE']=$_POST['size_'.$key];	
			if($elements_form[$key]['SIZE']=="")$elements_form[$key]['SIZE']=0;
			$array_options[]=$elements_form[$key]['DEFAULT'];
			$array_options[]=$elements_form[$key]['SIZE'];
			break;
			
			case 3://image
			if($_POST['repertory_'.$key]=="")$_POST['repertory_'.$key]='../medias/';
			if(!is_numeric($_POST['width_min_'.$key]))$_POST['width_min_'.$key]=0;
			if(!is_numeric($_POST['width_max_'.$key]))$_POST['width_max_'.$key]=0;
			if(!is_numeric($_POST['height_min_'.$key]))$_POST['height_min_'.$key]=0;
			if(!is_numeric($_POST['height_max_'.$key]))$_POST['height_max_'.$key]=0;	
			$array_options[]=$_POST['repertory_'.$key];
			$array_options[]=$_POST['width_min_'.$key];
			$array_options[]=$_POST['width_max_'.$key];
			$array_options[]=$_POST['height_min_'.$key];
			$array_options[]=$_POST['height_max_'.$key];
			break;
			
			case 5://option
			$array_options[]=$elements_form[$key]['DEFAULT'];
			break;
			
			case 6://requete
			if($_POST['request_'.$key]=="")$_POST['request_'.$key]='';
			if($_POST['req_id_'.$key]=="")$_POST['req_id_'.$key]='';
			if($_POST['req_label_'.$key]=="")$_POST['req_label_'.$key]='';
			$array_options[]=$_POST['request_'.$key];
			$array_options[]=$_POST['req_id_'.$key];
			$array_options[]=$_POST['req_label_'.$key];
			break;
			
			case 10://website
			$array_options[]=$elements_form[$key]['DEFAULT'];
			break;
			
			case 11://email
			$array_options[]=$elements_form[$key]['DEFAULT'];
			break;
			
		}	
		
		if(count($array_options)>0)$options_element=implode("<||>",$array_options);
		else $options_element='';

		if(is_numeric($elements_form[$key]['ACTUAL_ID']) && in_array($elements_form[$key]['ACTUAL_ID'],$list_elements)){/*Si déjà présent*/
		
			removeFromArray($list_elements,$elements_form[$key]['ACTUAL_ID']);
			//echo $elements_form[$key]['NAME'];		
			$req_element="UPDATE cms_template_element SET `NAME` = '".addslashes($elements_form[$key]['NAME'])."',`TYPE` = ".$elements_form[$key]['TYPE'].",`ORDRE` = ".$elements_form[$key]['ORDRE'].", `HELP` = '".addslashes($elements_form[$key]['HELP'])."', `REQUIRED` = ".$elements_form[$key]['REQUIRED'].", `OPTIONS` = '".addslashes($options_element)."' WHERE `ID` = ".$elements_form[$key]['ACTUAL_ID'];
			//echo $req_element.'<br/>';
			$db->execute($req_element);
			$id_element=$elements_form[$key]['ACTUAL_ID'];
			$req_suppr_opt='DELETE FROM cms_template_element_options WHERE ID_ELEMENT = '.$id_element;
			//echo $req_suppr_opt.'<br/>';
			$db->execute($req_suppr_opt);
			
		}else{
		
			$req_element="INSERT INTO cms_template_element (`ID_BLOC`,`NAME`,`TYPE`,`OPTIONS`,`ORDRE`,`REQUIRED`,`HELP`) VALUES (".$_GET['id'].",'".addslashes($elements_form[$key]['NAME'])."',".$elements_form[$key]['TYPE'].",'".addslashes($options_element)."',".$elements_form[$key]['ORDRE'].",".$elements_form[$key]['REQUIRED'].",'".addslashes($elements_form[$key]['HELP'])."')";
			$db->execute($req_element);
			//echo $req_element.'<br><br>';
			$id_element=$db->lastInsertedId();
		}
	
		if($elements_form[$key]['TYPE']==5){
			$i=0;
			while(isset($_POST['name_option_'.$key.'_'.$i])){
				$elements_form[$key]['ELEMENTS'][]=$_POST['name_option_'.$key.'_'.$i];
				if($elements_form[$key]['DEFAULT']==$i)$sel=1;
				else $sel=0;
				
				$req_element_options="INSERT INTO cms_template_element_options (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".addslashes($_POST['name_option_'.$key.'_'.$i])."',".$i.",".$sel.")";				
				$db->execute($req_element_options);
				//echo $req_element_options.'<br><br>';
				$i++;
			}			
		}
		if($elements_form[$key]['TYPE']==4){
			$i=0;
			
			while(isset($_POST['name_option_lib_'.$key.'_'.$i])){
				$info_opt=array();
				$info_opt['LIBELLE']=$_POST['name_option_lib_'.$key.'_'.$i];
				$info_opt['DEFAULT']=$_POST['name_option_def_'.$key.'_'.$i];
				$elements_form[$key]['ELEMENTS'][]=$info_opt;

				
				$req_element_options="INSERT INTO cms_template_element_options (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".addslashes($info_opt['LIBELLE'])."',".$i.",".$info_opt['DEFAULT'].")";
				$db->execute($req_element_options);				
				//echo $req_element_options.'<br><br>';
				$i++;
			}			
		}

	}
	
	$nb_elements=count($list_elements);
	if($nb_elements>0){
		for($i=0;$i<$nb_elements;$i++){
			$db->execute('DELETE FROM cms_template_element WHERE ID = '.$list_elements[$i]);
			$db->execute('DELETE FROM cms_template_element_options WHERE ID_ELEMENT = '.$list_elements[$i]);
		}
	}

	//pr($list_elements);
	/*
	pr($elements_form);
	pr($_POST);
	pr($output['listItem']);
	*/
	$_SESSION['notification'][]=array(1,"Modèles du CMS","Les éléments du bloc ont été modifiés.");
	header('location:modele_bloc.php?id='.$info_req['ID_TEMPLATE']);	
	die();
	
	
}

$array_menu[]=array('URL'=>'modele_bloc.php?id='.$info_req['ID_TEMPLATE'],'IMG'=>URL_DIR.'/images/back.png','LIBELLE'=>'Retour');
echo genere_sous_menu_admin($array_menu);?>
<br/>
<?
$req=$db->query('select * from cms_template_element where ID_BLOC = '.$_GET['id'].' order by `ORDRE`');
$nb_field_form=mysql_num_rows($req);
$nb_form=$nb_field_form+1;
if($nb_field_form!=0){/*Si déjà des données présentes */	
		
	$js_tablo="";
	$html_tablo="";
	$i=1;
	while($don=mysql_fetch_array($req)){		

		$don=array_map("format",$don);
			
		if($don['REQUIRED']==1)$req_stat='';
		else $req_stat=' nosee';
		
		$html_tablo.='
		<li id="listItem_'.$i.'" onclick="javascript:lance_modif('.$i.');void(0);"><img src="'.URL_DIR.'/images/arrow.png" alt="move" width="16" height="16" class="handle" />
		<span id="label_'.$i.'" class="label_field">'.$don['NAME'].'</span><span class="required_field'.$req_stat.'" id="required_'.$i.'"> * </span>
		<div id="zone_form_'.$i.'">
		';
	
		if($don['OPTIONS']!='')$options_element=explode('<||>',htmlspecialchars_decode($don['OPTIONS'],ENT_NOQUOTES));
		
		//pr($options_element);
		
		switch($don['TYPE']){			
			
			case 1:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=1;
			element['ACTUAL_ID']=".$don['ID'].";
			element['SIZE']=".$options_element[1].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['DEFAULT']='".$options_element[0]."';
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			
			switch($options_element[1]){
				case 1:
				$width_text='width:25%;';
				break;
				case 2:
				$width_text='width:50%;';
				break;
				case 3:
				$width_text='width:100%;';
				break;
			}
			$html_tablo.='<input type="text" readonly="readonly" id="text_'.$i.'" style="'.$width_text.'">';
			break;
			
			case 2:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=2;
			element['ACTUAL_ID']=".$don['ID'].";
			element['SIZE']=".$options_element[1].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['DEFAULT']='".$options_element[0]."';
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			
			switch($options_element[1]){
				case 1:
				$width_text='style="height:5.5em;"';
				break;
				case 2:
				$width_text='style="height:10em;"';
				break;
				case 3:
				$width_text='style="height:20em;"';
				break;
			}
			$html_tablo.='<textarea class="large_text" readonly="readonly" id="textarea_'.$i.'" '.$width_text.'></textarea>';
			
		
			break;
			
			case 3:
			
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=3;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REPERTORY']='".$options_element[0]."';
			element['WIDTH_MIN']='".$options_element[1]."';
			element['WIDTH_MAX']='".$options_element[2]."';
			element['HEIGHT_MIN']='".$options_element[3]."';
			element['HEIGHT_MAX']='".$options_element[4]."';
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";			
			
			$html_tablo.='<div><img src="'.URL_DIR.'/images/image_select.jpg"></div><div class="clear"></div>';
			
			break;
			
			case 4:
			
			$req_options=$db->query('select * from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
			$list_opt='';
			$list_opt_js='';
			$j=0;
			while($don_opt=mysql_fetch_array($req_options)){
			
				if($don_opt['DEFAULT']==1)$sel='checked="checked"';
				else $sel='';
				
				
				$html_tablo.='
				<div style="float:left;"><input type="checkbox" id="check_'.$i.'_'.$j.'" '.$sel.'></div>
				<div style="float:left;"><label id="choice_'.$i.'_'.$j.'">'.$don_opt['NAME'].'</label></div>
				<div class="clear"></div>
				';
				
				$list_opt_js.="						
				element['ELEMENTS'][".$j."]= new Array();
				element['ELEMENTS'][".$j."]['LIBELLE']='".$don_opt['NAME']."';
				element['ELEMENTS'][".$j."]['DEFAULT']=".$don_opt['DEFAULT'].";
				";
				$j++;
			}
			
			
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=4;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['DEFAULT']='".$don['DEFAULT']."';
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			element['ELEMENTS']=new Array();
			".$list_opt_js."
			tab_form.push(element);					
			";
			
			break;
			
			case 5:
			
			$req_options=$db->query('select * from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
			$list_opt='';
			$list_opt_js='';
			$j=0;
			$html_tablo.='<select>';
			while($don_opt=mysql_fetch_array($req_options)){
			
			
				$html_tablo.='
				<option id="choice_'.$i.'_'.$j.'">'.$don_opt['NAME'].'</option>
				';
				
				$list_opt_js.="
				element['ELEMENTS'][".$j."]='".$don_opt['NAME']."';
				";
				$j++;
			}
			$html_tablo.='</select>';
			
			//pr($options_element);
			if($options_element[0]=="")$options_element[0]='null';
			
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=5;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['DEFAULT']=".$options_element[0].";
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			element['ELEMENTS']=new Array();
			".$list_opt_js."
			tab_form.push(element);					
			";
			
			break;
			
			case 6:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=6;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['REQUEST']='".$options_element[0]."';
			element['REQ_ID']='".$options_element[1]."';
			element['REQ_LABEL']='".$options_element[2]."';
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><img src="'.URL_DIR.'/images/db_select.jpg"></div><div class="clear"></div>';
			break;
			
			case 7:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=7;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><img src="'.URL_DIR.'/images/tiny_select.jpg"></div><div class="clear"></div>';
			break;
			
			case 8:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=8;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><img src="'.URL_DIR.'/images/geo_select.jpg"></div><div class="clear"></div>';
			break;
			
			case 9:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=9;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><span style="float:left;"><input type="text" readonly="readonly" size="2"> /&nbsp;<br><label style="font-size:10px;">Jour</label></span><span style="float:left;"><input type="text" readonly="readonly" size="2"> /&nbsp;<br><label style="font-size:10px;">Mois</label></span><span style="float:left;"><input type="text" readonly="readonly" size="2"> <br><label style="font-size:10px;">Année</label></span></div><div class="clear"></div>';
			break;

			case 10:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=10;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['DEFAULT']='".$options_element[0]."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><input type="text" readonly="readonly" value="http://" style="width:100%;"></div>';
			break;
			
			
			case 11:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=11;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['DEFAULT']='".$options_element[0]."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><input type="text" readonly="readonly" value="@" style="width:100%;"></div>';
			break;
			
			case 12:
			$js_tablo.="
			element= new Array();
			element['ID']=".$i.";
			element['TYPE']=12;
			element['ACTUAL_ID']=".$don['ID'].";
			element['REQUIRED']=".$don['REQUIRED'].";
			element['NAME']='".$don['NAME']."';
			element['HELP']='".$don['HELP']."';
			tab_form.push(element);					
			";
			$html_tablo.='<div><input type="file" readonly="readonly"></div>';
			break;
		
		}		
		

		$html_tablo.='
		<div style="line-height:10px;margin:0 10px 0 0;padding:0;display:none;" id="suppr_'.$i.'">
		<a href="javascript:suppr_element('.$i.');void(0);"><img src="'.URL_DIR.'/images/cancel.png"></a></div></div></li>';
		
		$i++;
	}
		
		
	
}
?>
<script language="JavaScript" type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.7.1.custom.min.js"></script>
<link rel='stylesheet' href='<? echo URL_DIR;?>/css/dragdrop.css' type='text/css' media='all' />
<script>
nb_form=<?echo $nb_form;?>;
nb_actuel=0;
last_timestamp=0;
if($.browser.msie && $.browser.version=="6.0")size_padding=10;
else size_padding=9;
var order_general='';
var form_info = new Array();
var nb_field_form=<?echo $nb_field_form;?>;
var tab_form = new Array();
<?echo $js_tablo;?>

function display_save(){
	if(nb_field_form>0)$('#img_save').show();
	else $('#img_save').hide();
}
function insert_element(type_element){

	var d = new Date();
	h_local = d.getTime();
	h_local = Math.floor(h_local / 500) ;
	
	if(h_local!=last_timestamp){//si le dernier date d'il y a plus d'une seconde on accepte
	
		
		if(type_element=='texte'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=1;
			element['SIZE']=2;
			element['REQUIRED']=0;
			element['DEFAULT']='';
			element['NAME']='Texte';
			element['HELP']='';
			tab_form.push(element);
			code_html='<input type="text" readonly="readonly" id="text_'+nb_form+'" style="width:50%">';
		}
		else if(type_element=='paragraph'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=2;
			element['SIZE']=2;
			element['REQUIRED']=0;
			element['DEFAULT']='';
			element['NAME']='Paragraphe';
			element['HELP']='';
			tab_form.push(element);
			code_html='<textarea class="large_text" readonly="readonly" id="textarea_'+nb_form+'"></textarea>';
		}
		else if(type_element=='image'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=3;
			element['REQUIRED']=0;
			element['REPERTORY']='';
			element['WIDTH_MIN']='0';
			element['WIDTH_MAX']='';
			element['HEIGHT_MIN']='0';
			element['HEIGHT_MAX']='';
			element['NAME']='Image';
			element['HELP']='';
			tab_form.push(element);	
			
			code_html='<div><img src="<?echo URL_DIR;?>/images/image_select.jpg"></div><div class="clear"></div>';
		}
		else if(type_element=='checkbox'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=4;
			element['REQUIRED']=0;
			element['NAME']='Cases à cocher';
			element['ELEMENTS']=new Array();
			element['ELEMENTS'][0]= new Array();
			element['ELEMENTS'][0]['LIBELLE']="1ère option";
			element['ELEMENTS'][0]['DEFAULT']=0;
			element['ELEMENTS'][1]= new Array();
			element['ELEMENTS'][1]['LIBELLE']="2ème option";
			element['ELEMENTS'][1]['DEFAULT']=0;
			element['ELEMENTS'][2]= new Array();
			element['ELEMENTS'][2]['LIBELLE']="3ème option";
			element['ELEMENTS'][2]['DEFAULT']=0;
			element['HELP']='';
			tab_form.push(element);	
			
			code_html='<div style="float:left;"><input type="checkbox" id="check_'+nb_form+'_0"></div><div style="float:left;"><label id="choice_'+nb_form+'_0">1ère option</label></div><div class="clear"></div>'+
			'<div style="float:left;"><input type="checkbox" id="check_'+nb_form+'_1"></div><div style="float:left;"><label id="choice_'+nb_form+'_1">2ème option</label></div><div class="clear"></div>'+
			'<div style="float:left;"><input type="checkbox" id="check_'+nb_form+'_2"></div><div style="float:left;"><label id="choice_'+nb_form+'_2">3ème option</label></div><div class="clear"></div>';	
		}
		else if(type_element=='select'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=5;
			element['REQUIRED']=0;
			element['DEFAULT']='';
			element['NAME']='Menu déroulant';
			element['HELP']='';
			element['ELEMENTS']=new Array();
			element['ELEMENTS'][0]="1ère option";
			element['ELEMENTS'][1]="2ème option";
			element['ELEMENTS'][2]="3ème option";
			tab_form.push(element);	
			
			code_html='<select>'+
			'<option id="choice_'+nb_form+'_0">1ère option</option>'+
			'<option id="choice_'+nb_form+'_1">2ème option</option>'+
			'<option id="choice_'+nb_form+'_2">3ème option</option></select>';
		}
		else if(type_element=='database'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=6;
			element['REQUIRED']=0;
			element['REQUEST']='';
			element['REQ_ID']='';
			element['REQ_LABEL']='';
			element['NAME']='Sélectionnez la valeur correspondante';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><img src="<?echo URL_DIR;?>/images/db_select.jpg"></div><div class="clear"></div>';
		}
		else if(type_element=='tiny'){
		
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=7;
			element['REQUIRED']=0;
			element['NAME']='Votre contenu';
			element['HELP']='';
			tab_form.push(element);
			
			
			code_html='<div><img src="<?echo URL_DIR;?>/images/tiny_select.jpg"></div><div class="clear"></div>';
		}
		else if(type_element=='geo'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=8;
			element['REQUIRED']=0;
			element['NAME']='Géolocalisation';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><img src="<?echo URL_DIR;?>/images/geo_select.jpg"></div><div class="clear"></div>';
		}
		else if(type_element=='date'){
		
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=9;
			element['REQUIRED']=0;
			element['NAME']='Date';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><span style="float:left;"><input type="text" readonly="readonly" size="2"> /&nbsp;<br><label style="font-size:10px;">Jour</label></span><span style="float:left;"><input type="text" readonly="readonly" size="2"> /&nbsp;<br><label style="font-size:10px;">Mois</label></span><span style="float:left;"><input type="text" readonly="readonly" size="2"> <br><label style="font-size:10px;">Année</label></span></div><div class="clear"></div>';
		}
		else if(type_element=='website'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=10;
			element['REQUIRED']=0;
			element['NAME']='Site internet';
			element['DEFAULT']='';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><input type="text" readonly="readonly" value="http://" style="width:100%;"></div>';
		}
		else if(type_element=='email'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=11;
			element['REQUIRED']=0;
			element['NAME']='Email';
			element['DEFAULT']='';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><input type="text" readonly="readonly" value="@" style="width:100%;"></div>';
		}
		else if(type_element=='upload'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=12;
			element['REQUIRED']=0;
			element['NAME']='Télécharger un fichier';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><input type="file" readonly="readonly"></div>';
		}
		
		/*Affichage principal !*/
		
		last_timestamp=h_local;
		
		
		
		$("#form_elements").append('<li id="listItem_'+nb_form+'" onclick="javascript:lance_modif('+nb_form+');void(0);">'+'<img src="<? echo URL_DIR;?>/images/arrow.png" alt="move" width="16" height="16" class="handle" />'+
		'<span id="label_'+nb_form+'" class="label_field">'+element['NAME']+'</span>'+
		'<span class="required_field nosee" id="required_'+nb_form+'"> * </span>'+
		'<div id="zone_form_'+nb_form+'">'+code_html+
		'<div style="line-height:10px;margin:0 10px 0 0;padding:0;display:none;" id="suppr_'+nb_form+'">'+
		'<a href="javascript:suppr_element('+nb_form+');void(0);"><img src="<?echo URL_DIR;?>/images/cancel.png"></a>'+
		'</div>'+
		'</div>'+
		'</li>');

		nb_form++;
		nb_field_form++;
		display_save();
	}
}

function get_height_before(id_field){
	
	var size_header=225;
	var position = $('#listItem_'+id_field).position();	
	//alert( "left: " + position.left + ", top: " + position.top);	
	var size=parseInt(position.top)-size_header;
	return size;
	/*
	size=30;
	for(var i in tab_form){		
		if(tab_form[i]["ID"]){		
			size+=size_padding;
			if(id_field==tab_form[i]["ID"])break;
			size+=tab_form[i]["WIDTH"];
			
		}
	}
	return size;
	//alert(size);*/
}

function lance_modif(id_field){
	//$('#log').html(id_field);
	//alert(id_field);	
	$('#listItem_'+nb_actuel).css({'background-color': '#FFFFFF'});
	$('#listItem_'+id_field).css({'background-color': '#E2F6FF'});	
	$('#zone_form_add,#suppr_'+nb_actuel).hide();
	$('#zone_form_edit,#suppr_'+id_field).show();
	size_to_show=get_height_before(id_field);
	$('#zone_form_edit').css({'margin-top':size_to_show+"px"});
	nb_actuel=id_field;
	//alert(id_field);
	var modif_valeur=get_table_index(id_field);
	//alert(modif_valeur);
	var require_check='';

	//alert(modif_valeur);
	//alert(tab_form[modif_valeur]['TYPE']);
	var form_edit='<label>Libellé du champ : </label><br/>'
	+'<textarea style="height:3.7em;width:80%;" onkeyup="javascript:maj_champ(this.value,'+id_field+');maj_valeur(this.value,\'NAME\','+id_field+');">'+tab_form[modif_valeur]['NAME']+'</textarea>';
	
	if(tab_form[modif_valeur]['TYPE']==1){
		
		var require_sel1='';
		if(tab_form[modif_valeur]['SIZE']==1)require_sel1="selected='selected'";
		var require_sel2='';
		if(tab_form[modif_valeur]['SIZE']==2)require_sel2="selected='selected'";
		var require_sel3='';
		if(tab_form[modif_valeur]['SIZE']==3)require_sel3="selected='selected'";		
		
		
		form_edit+='<label>Taille du champ :</label><br/>'
		+'<select name="taille_champ" id="taille_champ" onchange="maj_valeur($(\'#taille_champ\').val(),\'SIZE\','+id_field+');maj_taille($(\'#taille_champ\').val(),'+id_field+');"><option value="1" '+require_sel1+'>Petit</option><option value="2" '+require_sel2+'>Moyen</option><option value="3"  '+require_sel3+'>Grand</option></select>'
		+'<br/><label>Valeur par défaut :</label><br/>'
		+'<input type="text" style="width:80%;" value="'+tab_form[modif_valeur]['DEFAULT']+'" onkeyup="javascript:maj_valeur(this.value,\'DEFAULT\','+id_field+');">';	
		
	}	
	else if(tab_form[modif_valeur]['TYPE']==2){
		
		var require_sel1='';
		if(tab_form[modif_valeur]['SIZE']==1)require_sel1="selected='selected'";
		var require_sel2='';
		if(tab_form[modif_valeur]['SIZE']==2)require_sel2="selected='selected'";
		var require_sel3='';
		if(tab_form[modif_valeur]['SIZE']==3)require_sel3="selected='selected'";		
		
		form_edit+='<label>Taille du champ :</label><br/>'
		+'<select name="taille_champ" id="taille_champ" onchange="maj_valeur($(\'#taille_champ\').val(),\'SIZE\','+id_field+');maj_taille_area($(\'#taille_champ\').val(),'+id_field+');"><option value="1" '+require_sel1+'>Petit</option><option value="2" '+require_sel2+'>Moyen</option><option value="3"  '+require_sel3+'>Grand</option></select>'
		+'<br/><label>Valeur par défaut :</label><br/>'
		+'<input type="text" style="width:80%;" value="'+tab_form[modif_valeur]['DEFAULT']+'" onkeyup="javascript:maj_valeur(this.value,\'DEFAULT\','+id_field+');">';	
		
	}
	if(tab_form[modif_valeur]['TYPE']==3){
		
		form_edit+='<label>Répertoire où sera recherché/téléchargé l\'image :</label><br/>'
		+'<input type="text" style="width:80%;" value="'+tab_form[modif_valeur]['REPERTORY']+'" onkeyup="javascript:maj_valeur(this.value,\'REPERTORY\','+id_field+');">'
		+'<br/><label>Largeur maximale :</label><br/>'
		+'<!--<input type="text" style="width:30%;" value="'+tab_form[modif_valeur]['WIDTH_MIN']+'" onkeyup="javascript:maj_valeur(this.value,\'WIDTH_MIN\','+id_field+');">&nbsp;&nbsp;&nbsp;-->'
		+'<input type="text" style="width:30%;" value="'+tab_form[modif_valeur]['WIDTH_MAX']+'" onkeyup="javascript:maj_valeur(this.value,\'WIDTH_MAX\','+id_field+');">'
		+'<br/><label>Hauteur et maximale :</label><br/>'
		+'<!--<input type="text" style="width:30%;" value="'+tab_form[modif_valeur]['HEIGHT_MIN']+'" onkeyup="javascript:maj_valeur(this.value,\'HEIGHT_MIN\','+id_field+');">&nbsp;&nbsp;&nbsp;-->'
		+'<input type="text" style="width:30%;" value="'+tab_form[modif_valeur]['HEIGHT_MAX']+'" onkeyup="javascript:maj_valeur(this.value,\'HEIGHT_MAX\','+id_field+');">'
		;	
		
	}	
	if(tab_form[modif_valeur]['TYPE']==6){
		
		form_edit+='<label>Requête :</label><br/>'
		+'<input type="text" style="width:80%;" value="'+tab_form[modif_valeur]['REQUEST']+'" onkeyup="javascript:maj_valeur(this.value,\'REQUEST\','+id_field+');">'
		+'<br/><label>Champ à enregistrer</label><br/>'
		+'<input type="text" style="width:50%;" value="'+tab_form[modif_valeur]['REQ_ID']+'" onkeyup="javascript:maj_valeur(this.value,\'REQ_ID\','+id_field+');">'
		+'<br/><label>Champ à afficher :</label><br/>'
		+'<input type="text" style="width:50%;" value="'+tab_form[modif_valeur]['REQ_LABEL']+'" onkeyup="javascript:maj_valeur(this.value,\'REQ_LABEL\','+id_field+');">';	
		
	}	
	else if(tab_form[modif_valeur]['TYPE']==5){/*Combobox et radio*/
		
		form_edit+='<label>Options : </label><br/><ul id="list_opt_'+id_field+'">';

		j=tab_form[modif_valeur]['ELEMENTS'].length;
		for(var i in tab_form[modif_valeur]['ELEMENTS']){		
			
			if(i<j){
				
				if(tab_form[modif_valeur]['DEFAULT']==i && is_numeric(tab_form[modif_valeur]['DEFAULT']))img_default='select';
				else img_default='unselect';
			
				form_edit+='<li id="option_'+id_field+'_'+i+'"><input type="text" value="'+tab_form[modif_valeur]['ELEMENTS'][i]+'" onkeyup="javascript:maj_option_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"> <a href="javascript:add_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/plus.png" align="absmiddle"></a>  <a href="javascript:delete_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/minus.png" align="absmiddle"></a><a href="javascript:select_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></li>';
			}
			
		}
		form_edit+='</ul>';
	}
	else if(tab_form[modif_valeur]['TYPE']==4){/*Cases à cocher*/
		
		form_edit+='<label>Options : </label><br/><ul id="list_opt_'+id_field+'">';

		j=tab_form[modif_valeur]['ELEMENTS'].length;
		for(var i in tab_form[modif_valeur]['ELEMENTS']){		
			
			if(i<j){
				
				if(tab_form[modif_valeur]['ELEMENTS'][i]['DEFAULT']==1)img_default='select';
				else img_default='unselect';
			
				form_edit+='<li id="option_'+id_field+'_'+i+'"><input type="text" value="'+tab_form[modif_valeur]['ELEMENTS'][i]['LIBELLE']+'" onkeyup="javascript:maj_option_check_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"> <a href="javascript:add_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/plus.png" align="absmiddle"></a>  <a href="javascript:delete_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/minus.png" align="absmiddle"></a><a href="javascript:select_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></li>';
			}
			
		}
		form_edit+='</ul>';
	}
	else if(tab_form[modif_valeur]['TYPE']==11 || tab_form[modif_valeur]['TYPE']==10){
		form_edit+='<br/><label>Valeur par défaut :</label><br/>'
		+'<input type="text" style="width:80%;" value="'+tab_form[modif_valeur]['DEFAULT']+'" onkeyup="javascript:maj_valeur(this.value,\'DEFAULT\','+id_field+');">';	
	}
	
	if(tab_form[modif_valeur]['REQUIRED']==1)require_check="checked='checked'";
	
	form_edit+='<br /><label>Conditions :</label><br/>'
	+'<input type="checkbox" value="0" '+require_check+' onclick="(this.checked) ? checkVal = \'1\' : checkVal = \'0\';maj_valeur(checkVal,\'REQUIRED\','+id_field+');maj_required(checkVal,'+id_field+');"> Requis'	
	+'<br/><label class="clear">Aide à la saisie :</label><br/>'	
	+'<textarea style="height:5.7em;width:80%;" onkeyup="javascript:maj_valeur(this.value,\'HELP\','+id_field+');">'+tab_form[modif_valeur]['HELP']+'</textarea>';
	
	$('#zone_form_edit').html(form_edit);
}
function maj_entete(id_field,value){
	//alert(id_field+':'+id_option+':'+value);
	$('#'+id_field).html(value);	
}
function maj_entete_value(id_field,value){
	//alert(id_field+':'+id_option+':'+value);
	form_info[id_field]=value;
}
function maj_label(id_field,id_option,value){
	//alert(id_field+':'+id_option+':'+value);
	$('#choice_'+id_field+'_'+id_option).html(value);
}
function maj_option_value(id_field,id_option,value){
	//alert(value);
	var modif_valeur=get_table_index(id_field);
	tab_form[modif_valeur]['ELEMENTS'][id_option]=value;
	
}
function affiche_liste(id_field){
	var modif_valeur=get_table_index(id_field);
	//alert(id_field+' : '+id_field);
	form_edit='';
	left_display='';
	img_default='';
	if(tab_form[modif_valeur]['TYPE']==5)left_display='<select>';
	j=tab_form[modif_valeur]['ELEMENTS'].length;
	for(var i in tab_form[modif_valeur]['ELEMENTS']){	
	
		if(i<j){
			
			if(tab_form[modif_valeur]['DEFAULT']==i && is_numeric(tab_form[modif_valeur]['DEFAULT']))img_default='select';
			else img_default='unselect';
			
			form_edit+='<li id="option_'+id_field+'_'+i+'"><input type="text" value="'+tab_form[modif_valeur]['ELEMENTS'][i]+'" onkeyup="javascript:maj_option_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"> <a href="javascript:add_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/plus.png" align="absmiddle"></a>  <a href="javascript:delete_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/minus.png" align="absmiddle"></a> <a href="javascript:select_option('+id_field+','+i+');void(0);" style="margin-left:10px;"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'"></a></li>';
			
			if(tab_form[modif_valeur]['TYPE']==5){
				left_display+='<option id="choice_'+id_field+'_'+i+'">'+tab_form[modif_valeur]['ELEMENTS'][i]+'</option>';
			}else if(tab_form[modif_valeur]['TYPE']==3){
				left_display+='<div style="float:left;"><input type="radio"></div><div style="float:left;"><label id="choice_'+id_field+'_'+i+'">'+tab_form[modif_valeur]['ELEMENTS'][i]+'</label></div><div class="clear"></div>';		
			}
		}			
	}
	if(tab_form[modif_valeur]['TYPE']==5)left_display+='</select>';
	//alert(form_edit);
	left_display+='<div style="line-height:10px;margin:0 10px 0 0;padding:0;" id="suppr_'+id_field+'">'+
		'<a href="javascript:suppr_element('+id_field+');void(0);"><img src="<?echo URL_DIR;?>/images/cancel.png"></a>'+
		'</div>';		
		
	$('#zone_form_'+id_field).html(left_display);
	$('#list_opt_'+id_field).html(form_edit);
}
function select_option(id_field,id_indice){
	var modif_valeur=get_table_index(id_field);
	
	//alert(tab_form[modif_valeur]["DEFAULT"]+' '+id_indice);
	if(is_numeric(tab_form[modif_valeur]["DEFAULT"])){
		//alert('ancienne valeur '+tab_form[modif_valeur]["DEFAULT"]);
		if(tab_form[modif_valeur]["DEFAULT"]==id_indice){
			//alert('lali');
			tab_form[modif_valeur]["DEFAULT"]='';
			$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/unselect.png'});

		}else{
			//alert('lala');
			$('#img_select_'+id_field+'_'+tab_form[modif_valeur]["DEFAULT"]).attr({'src':'<?echo URL_DIR;?>/images/unselect.png'});
			tab_form[modif_valeur]["DEFAULT"]=id_indice;
			$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/select.png'});
		}
	}
	else{
		tab_form[modif_valeur]["DEFAULT"]=id_indice;
		$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/select.png'});
	}
}
function add_option(id_field,before){
	
	var d = new Date();
	h_local = d.getTime();
	h_local = Math.floor(h_local / 500) ;
	if(h_local!=last_timestamp){//si le dernier date d'il y a plus d'une seconde on accepte
		var modif_valeur=get_table_index(id_field);
		var emplacement=parseInt(before)+1;
		tab_form[modif_valeur]["ELEMENTS"].splice(emplacement, 0, '');
		affiche_liste(id_field);
		last_timestamp=h_local;
	}
}
function delete_option(id_field,id_deleted){
	var d = new Date();
	h_local = d.getTime();
	h_local = Math.floor(h_local / 500) ;
	if(h_local!=last_timestamp){//si le dernier date d'il y a plus d'une seconde on accepte
	
		var modif_valeur=get_table_index(id_field);		
		var nb_opt=tab_form[modif_valeur]["ELEMENTS"].length;
		
		if(nb_opt>1){
			tab_form[modif_valeur]["ELEMENTS"].splice(id_deleted, 1);
			affiche_liste(id_field);
		}
		else alert('Veuillez conserver au moins une option');
		last_timestamp=h_local;
	}
}
function maj_champ(valeur,id_field){
	$('#label_'+id_field).html(valeur);
}
function maj_taille(valeur,id_field){
	//alert(valeur+':'+id_field);
	if(valeur=='1')$('#text_'+id_field).css({'width':'25%'});
	else if(valeur=='2')$('#text_'+id_field).css({'width':'50%'});
	else if(valeur=='3')$('#text_'+id_field).css({'width':'100%'});
}
function maj_required(valeur,id_field){
	//alert(valeur+' '+id_field);
	if(valeur=='0')$('#required_'+id_field).hide();
	else if(valeur=='1')$('#required_'+id_field).show();
}
function maj_taille_area(valeur,id_field){
	//alert(valeur+':'+id_field);
	if(valeur=='1')$('#textarea_'+id_field).css({'height':'5.5em'});
	else if(valeur=='2')$('#textarea_'+id_field).css({'height':'10em'});
	else if(valeur=='3')$('#textarea_'+id_field).css({'height':'20em'});
}
function maj_valeur(valeur,fieldname,id_field){
	//alert(valeur+':'+fieldname+':'+id_array);
	var index_modif=get_table_index(id_field);
	tab_form[index_modif][fieldname]=valeur;
}

function print_r(obj) {
	win_print_r = window.open('about:blank', 'win_print_r');
	win_print_r.document.write('<html><body>');
	r_print_r(obj, win_print_r);
	win_print_r.document.write('</body></html>');
}

function return_all_value(){
	
	var form_don='';	
	form_don='<input type="hidden" name="ordre" value="'+$('#form_elements').sortable('serialize')+'">';
	form_don+='<input type="hidden" name="id" value="<?echo $_GET['id'];?>">';

	j=tab_form.length;
	//alert(j;
	for(var i in tab_form){
		if(i<j){
			form_don+='<input type="hidden" name="name_element[]" value="'+tab_form[i]['NAME']+'">';
			form_don+='<input type="hidden" name="type_element_'+i+'" value="'+tab_form[i]['TYPE']+'">';
			form_don+='<input type="hidden" name="actual_id_'+i+'" value="'+tab_form[i]['ACTUAL_ID']+'">';
			form_don+='<input type="hidden" name="id_field_'+i+'" value="'+tab_form[i]['ID']+'">';
			form_don+='<input type="hidden" name="default_'+i+'" value="'+tab_form[i]['DEFAULT']+'">';
			form_don+='<input type="hidden" name="help_'+i+'" value="'+tab_form[i]['HELP']+'">';
			form_don+='<input type="hidden" name="required_'+i+'" value="'+tab_form[i]['REQUIRED']+'">';
			
			if(tab_form[i]['TYPE']==1 || tab_form[i]['TYPE']==2){
				form_don+='<input type="hidden" name="size_'+i+'" value="'+tab_form[i]['SIZE']+'">';
			}
			if(tab_form[i]['TYPE']==3){
				form_don+='<input type="hidden" name="repertory_'+i+'" value="'+tab_form[i]['REPERTORY']+'">';
				form_don+='<input type="hidden" name="width_min_'+i+'" value="'+tab_form[i]['WIDTH_MIN']+'">';
				form_don+='<input type="hidden" name="width_max_'+i+'" value="'+tab_form[i]['WIDTH_MAX']+'">';
				form_don+='<input type="hidden" name="height_min_'+i+'" value="'+tab_form[i]['HEIGHT_MIN']+'">';
				form_don+='<input type="hidden" name="height_max_'+i+'" value="'+tab_form[i]['HEIGHT_MAX']+'">';
			}
			if(tab_form[i]['TYPE']==6){
				form_don+='<input type="hidden" name="request_'+i+'" value="'+tab_form[i]['REQUEST']+'">';
				form_don+='<input type="hidden" name="req_id_'+i+'" value="'+tab_form[i]['REQ_ID']+'">';
				form_don+='<input type="hidden" name="req_label_'+i+'" value="'+tab_form[i]['REQ_LABEL']+'">';
			}
			if(tab_form[i]['TYPE']==5){
				j2=tab_form[i]['ELEMENTS'].length;
				for(i2 in tab_form[i]['ELEMENTS']){
					if(i2<j2)form_don+='<input type="hidden" name="name_option_'+i+'_'+i2+'" value="'+tab_form[i]['ELEMENTS'][i2]+'">';
				}
			}
			
			if(tab_form[i]['TYPE']==4){
				j2=tab_form[i]['ELEMENTS'].length;
				for(i2 in tab_form[i]['ELEMENTS']){
					if(i2<j2){
						form_don+='<input type="hidden" name="name_option_lib_'+i+'_'+i2+'" value="'+tab_form[i]['ELEMENTS'][i2]['LIBELLE']+'">';
						form_don+='<input type="hidden" name="name_option_def_'+i+'_'+i2+'" value="'+tab_form[i]['ELEMENTS'][i2]['DEFAULT']+'">';
					}
				}
			}
		}
	}
	
	$('#form_final').html(form_don);
	setTimeout("finish_submit()",500);
	
}

function finish_submit(){
	document.form_final.submit();
}

function r_print_r(theObj, win_print_r) {
	if(theObj.constructor == Array ||	theObj.constructor == Object){
		if (win_print_r == null)win_print_r = window.open('about:blank', 'win_print_r');
	}
	for(var p in theObj){
		if(theObj[p].constructor == Array|| theObj[p].constructor == Object){
			win_print_r.document.write("<li>["+p+"] =>"+typeof(theObj)+"</li>");
			win_print_r.document.write("<ul>")
			r_print_r(theObj[p], win_print_r);
			win_print_r.document.write("</ul>")
		} else {
			win_print_r.document.write("<li>["+p+"] =>"+theObj[p]+"</li>");
		}
	}
	win_print_r.document.write("</ul>")
}
function get_table_index(id_field){
	var index_final='';
	for(var i in tab_form){		
		if(tab_form[i]["ID"]==id_field){		
			index_final=i;			
		}
	}	
	return index_final;
}
function suppr_element(id_field){
	//ici on a récupéré l'identifiant du menu , on va maintenant devoir trouver son identifiant au sein du tableau
	var index_suppr=get_table_index(id_field);
	//alert(index_suppr+' : '+tab_form[index_suppr]["NAME"]);
	tab_form.splice(index_suppr,1);
	$('#listItem_'+id_field).remove();
	affiche_zone_form('add');
	nb_field_form--;
	display_save();
}
 // When the document is ready set up our sortable with it's inherant function(s)
$(document).ready(function() {
    $("#form_elements").sortable({
      handle : '.handle',
      update : function () {
		 order_general = $('#form_elements').sortable('serialize');
		 //alert(order);
      }
    });
});

function affiche_zone_form(form_zone){
	if(form_zone=='add'){
		$('#zone_form_edit').hide();
		$('#zone_form_edit').html('');
	}
	if(form_zone=='edit'){
		$('#zone_form_add').hide();
	}
	$('#zone_form_'+form_zone).show();
}
function maj_option_check_value(id_field,id_option,value){
	//alert(value);
	var modif_valeur=get_table_index(id_field);
	tab_form[modif_valeur]['ELEMENTS'][id_option]['LIBELLE']=value;
	
}
function affiche_liste_check(id_field){
	var modif_valeur=get_table_index(id_field);
	//alert(id_field+' : '+id_field);
	form_edit='';
	left_display='';
	img_default='';
	
	j=tab_form[modif_valeur]['ELEMENTS'].length;
	for(var i in tab_form[modif_valeur]['ELEMENTS']){	
	
		if(i<j){
			
			if(tab_form[modif_valeur]['ELEMENTS'][i]['DEFAULT']==1)img_default='select';
			else img_default='unselect';			
			
			form_edit+='<li id="option_'+id_field+'_'+i+'"><input type="text" value="'+tab_form[modif_valeur]['ELEMENTS'][i]['LIBELLE']+'" onkeyup="javascript:maj_option_check_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"> <a href="javascript:add_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/plus.png" align="absmiddle"></a>  <a href="javascript:delete_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/minus.png" align="absmiddle"></a><a href="javascript:select_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></li>';
			
			left_display+='<div style="float:left;"><input type="checkbox" id="check_'+id_field+'_'+i+'"></div><div style="float:left;"><label id="choice_'+id_field+'_'+i+'">'+tab_form[modif_valeur]['ELEMENTS'][i]['LIBELLE']+'</label></div><div class="clear"></div>';		
			
		}			
	}

	//alert(form_edit);
	left_display+='<div style="line-height:10px;margin:0 10px 0 0;padding:0;" id="suppr_'+id_field+'">'+
		'<a href="javascript:suppr_element('+id_field+');void(0);"><img src="<?echo URL_DIR;?>/images/cancel.png"></a>'+
		'</div>';
		
		
	$('#zone_form_'+id_field).html(left_display);
	$('#list_opt_'+id_field).html(form_edit);
}
function select_option_check(id_field,id_indice){
	var modif_valeur=get_table_index(id_field);
	
	//alert(tab_form[modif_valeur]["DEFAULT"]+' '+id_indice);	
	//alert('ancienne valeur '+tab_form[modif_valeur]["DEFAULT"]);
	if(tab_form[modif_valeur]["ELEMENTS"][id_indice]["DEFAULT"]==1){
		//alert('desact');		
		tab_form[modif_valeur]["ELEMENTS"][id_indice]["DEFAULT"]=0;
		$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/unselect.png'});
		$('#check_'+id_field+'_'+id_indice).attr({'checked':false});

	}else{
		//alert('act');
		tab_form[modif_valeur]["ELEMENTS"][id_indice]["DEFAULT"]=1;
		$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/select.png'});
		$('#check_'+id_field+'_'+id_indice).attr({'checked':true});
	}
	
}
function add_option_check(id_field,before){
	
	var d = new Date();
	h_local = d.getTime();
	h_local = Math.floor(h_local / 500) ;
	if(h_local!=last_timestamp){//si le dernier date d'il y a plus d'une seconde on accepte
		var modif_valeur=get_table_index(id_field);
		var emplacement=parseInt(before)+1;
		var element_new=new Array();
		element_new['LIBELLE']='';
		element_new['DEFAULT']=0;		
		tab_form[modif_valeur]["ELEMENTS"].splice(emplacement, 0, element_new);
		affiche_liste_check(id_field);
		last_timestamp=h_local;
	}
}
function delete_option_check(id_field,id_deleted){
	var d = new Date();
	h_local = d.getTime();
	h_local = Math.floor(h_local / 500) ;
	if(h_local!=last_timestamp){//si le dernier date d'il y a plus d'une seconde on accepte
	
		var modif_valeur=get_table_index(id_field);		
		tab_form[modif_valeur]["ELEMENTS"].splice(id_deleted, 1);
		affiche_liste_check(id_field);
		last_timestamp=h_local;
	}
}
</script>
<div style="float:left;padding:0 0 20px;width:470px;">
<div id="log">
	<form id="form_final" action="?action=submit&id=<? echo $_GET['id'];?>" name="form_final" method="post"></form>
</div>
<ul id="form_elements">
<?echo $html_tablo;
?>	
</ul>
<a href="javascript:return_all_value();void(0);"><img src="<?echo URL_DIR;?>/images/save_form.jpg" id="img_save" <?if($nb_field_form==0){?>style="display:none;"<?}?>></a>
</div>	
<div style="width:400px;float:left;margin-left:15px;">
	<div style="float:left;"><a href="javascript:affiche_zone_form('add');void(0);"><img src="<?echo URL_DIR;?>/images/add_field.jpg" valign="absmiddle"></a></div>
	<div style="float:left;margin-left:50px;"><a href="javascript:affiche_zone_form('edit');void(0);"><img src="<?echo URL_DIR;?>/images/edit_field.jpg" valign="absmiddle"></div>
	<div id="zone_form_add" class="menu_form">
		<div id="form_fixed">
			<ul style='float:left;list-style:none outside none;width:180px;padding:0px;margin:0px;'>
				<li><a href="javascript:insert_element('texte');void(0);"><img src="<?echo URL_DIR;?>/images/bouton1.jpg"></a></li>
				<li><a href="javascript:insert_element('image');void(0);"><img src="<?echo URL_DIR;?>/images/bouton13.jpg"></a></li>
				<li><a href="javascript:insert_element('database');void(0);"><img src="<?echo URL_DIR;?>/images/bouton14.jpg"></a></li>
				<li><a href="javascript:insert_element('tiny');void(0);"><img src="<?echo URL_DIR;?>/images/bouton15.jpg"></a></li>
				<li><a href="javascript:insert_element('email');void(0);"><img src="<?echo URL_DIR;?>/images/bouton11.jpg"></a></li>
				<li><a href="javascript:insert_element('geo');void(0);"><img src="<?echo URL_DIR;?>/images/bouton16.jpg"></a></li>
			</ul>
			<ul style='float:right;list-style:none outside none;width:180px;padding:0px;margin:0px;'>
				<li><a href="javascript:insert_element('paragraph');void(0);"><img src="<?echo URL_DIR;?>/images/bouton2.jpg"></a></li>
				<li><a href="javascript:insert_element('checkbox');void(0);"><img src="<?echo URL_DIR;?>/images/bouton4.jpg"></a></li>
				<li><a href="javascript:insert_element('select');void(0);"><img src="<?echo URL_DIR;?>/images/bouton5.jpg"></a></li>
				<li><a href="javascript:insert_element('date');void(0);"><img src="<?echo URL_DIR;?>/images/bouton9.jpg"></a></li>
				<li><a href="javascript:insert_element('website');void(0);"><img src="<?echo URL_DIR;?>/images/bouton10.jpg"></a></li>
				<li><a href="javascript:insert_element('upload');void(0);"><img src="<?echo URL_DIR;?>/images/bouton12.jpg"></a></li>
			</ul>
		</div>
	</div>
	<div id="zone_form_edit"  style="background-color:#E2F6FF;float:left;height:auto;margin-left:15px;padding-left:5px;position:relative;width:400px;min-height:500px;display:none;"><br/>
	</div>
	<div id="zone_form_info"  style="background-color:#E2F6FF;float:left;height:auto;margin-left:15px;padding-left:5px;position:relative;width:400px;min-height:200px;display:none;"><br/>	
	</div>
</div>
<?
include('footer.php');
?>