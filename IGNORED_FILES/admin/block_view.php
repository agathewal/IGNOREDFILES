<?
$id_module=18;
include('../config/configuration.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';
if(isset($_GET['action2']))$action2=$_GET['action2'];
else $action2='';

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

if(!isset($_GET['idp']) || !is_numeric($_GET['idp'])){
	$_GET['idp']=0;
}
if(!isset($_GET['idr']) || !is_numeric($_GET['idr'])){
	$_GET['idr']=0;
}

if($action=='ordre_r'){	/*Réordonner les vidéos*/
	$req=$db->query('SELECT * FROM cms_template_data_'.$info_req['ID_TEMPLATE'].' WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_BLOC = '.$_GET['id'].' AND ID_PARENT = '.$_GET['idp']);
	while($don=mysql_fetch_array($req)){
		change_ordre("cms_template_data_".$info_req['ID_TEMPLATE'],"ID","ORDRE",$don['ORDRE'],$_POST['oldorder'],$_POST['record_ordre'],$don['ID'],$_POST['ID_RECORD']);
	}
	
	/*$_SESSION['notification'][]=array(1,"Modèle","La position de l'enregistrement a été modifiée.");*/
	header('location:block_view.php?id='.$_GET['id'].'&idp='.$_GET['idp']);
	die();	
}

if($action=='suppr' && isset($_GET['idr']) && is_numeric($_GET['idr'])){
	$req=$db->query('select `ORDRE` from cms_template_data_'.$info_req['ID_TEMPLATE'].' WHERE ID = '.$_GET['idr']);
	if(mysql_num_rows($req)!=0){
		$info=mysql_fetch_array($req);
		$req=$db->query('SELECT * FROM cms_template_data_'.$info_req['ID_TEMPLATE'].' WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND `ORDRE` > '.$info['ORDRE'].' AND ID_BLOC = '.$_GET['id'].' AND ID_PARENT = '.$_GET['idp']);
		while($don=mysql_fetch_array($req)){
			$db->execute('UPDATE cms_template_data_'.$info_req['ID_TEMPLATE'].' SET `ORDRE` = '.($don['ORDRE']-1).' WHERE ID = '.$don['ID']);
		}
		$db->execute('DELETE FROM cms_template_data_'.$info_req['ID_TEMPLATE'].' WHERE ID = '.$_GET['idr']);
		/*$_SESSION['notification'][]=array(1,"Modèle","L'enregistrement a été supprimé.");*/
		header('location:block_view.php?id='.$_GET['id'].'&idp='.$_GET['idp']);
		die();	
	}
}

$max=$db->countOf('cms_template_data_'.$info_req['ID_TEMPLATE'],'ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_BLOC = '.$_GET['id'].' AND ID_PARENT = '.$_GET['idp']);
if($max==1 && $info_req['NB_MAX_ELEMENT']==1 && $action==""){
	$rr=$db->query('select ID FROM cms_template_data_'.$info_req['ID_TEMPLATE'].' WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_BLOC = '.$_GET['id'].' AND ID_PARENT = '.$_GET['idp']);
	$don=mysql_fetch_array($rr);
	//echo 'block_view.php?action=modif&id='.$_GET['id'].'&idr='.$don['ID'].'&idp='.$_GET['idp'];
	header('location:block_view.php?action=modif&id='.$_GET['id'].'&idr='.$don['ID'].'&idp='.$_GET['idp']);
	die();
}
require_once('../config/class.upload.php');
ob_start();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/jquery.fancybox-1.3.1.css" type="text/css"  media="screen" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.tools.complete.min.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.corner.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/notifier.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.validate.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.metadata.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.checkboxes.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.mousewheel-3.0.2.pack.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.fancybox-1.3.1.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/customSelect.jquery.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/fct.js"></script>
<link rel="stylesheet" type="text/css" href="<? echo URL_DIR;?>/images/resources/css/ext-all.css" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/ext-all.js"></script>
<title><? if(!empty($title)) echo $title;?></title>
<script type="text/javascript">
$(function(){
	$('select.select_style').customStyle();
	$(".btn_style").hover( function () {
		$(this).css({'background-color':'#E2E2E2'});
	},function(){
		$(this).css({'background-color':'#f8f8f8'});
	});
	$(".btn_style").corner("5px").parent().css('padding', '1px').corner("5px");
});
</script>
<!--[if lte IE 7]>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/DD_belatedPNG_0.0.8a-min.js"></script>
<script>
$(function(){
	DD_belatedPNG.fix('img, .btn_style');
});
</script>
<![endif]-->
<style type="text/css">
body{
	background-image:none;
	background-color:#FFF;
}
input,textarea {
	background-color:#FFF;
	color:#8d8d8d;
	font-weight:bold;
	border:1px solid #999999;
	height:20px;
	padding-left:10px;
	font-size:11px;
}
</style>
</head>
<body>
<div id="container">
<div id="content" class="wrapper" style="margin:0px;width:658px;padding:0px;">
<?
if($action==''){
	if($max<$info_req['NB_MAX_ELEMENT'] || $info_req['NB_MAX_ELEMENT']==0)$array_menu[]=array('URL'=>'?action=add&id='.$_GET['id'].'&idp='.$_GET['idp'],'IMG'=>URL_DIR.'/images/add.png','LIBELLE'=>'Ajouter un enregistrement','WIDTH'=>'200');
}
if(count($array_menu)!=0)echo genere_sous_menu_admin($array_menu);
?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/uidatepickerfr.js"></script>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jnotes/jquery-notes_js/jquery-notes_1.0.8.js"></script>
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/base/jquery.ui.all.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/tooltip-generic.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" title="Standard" href="<?echo URL_DIR;?>/js/jnotes/jquery-notes_css/style.css" type="text/css" media="all" />
<?
$req_elements=$db->query('select * from cms_template_element where ID_BLOC = '.$_GET['id'].' order by `ORDRE`');
if(mysql_num_rows($req_elements)!=0){

	/* Validation des données avant ajout/modif */	
	if($action2=='valid'){
		//pr($_POST);
		
		//vérification des champs obligatoire ! 
		$req_elements=$db->query('select * from cms_template_element where ID_BLOC = '.$_GET['id'].' AND REQUIRED = 1 ORDER BY `ORDRE`');
		$list_champ_obligatoire=array();
		if(mysql_num_rows($req_elements)!=0){
			while($don=mysql_fetch_array($req_elements)){
				
				$empty=0;
				switch($don['TYPE']){
					case 1://text
					if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
					break;
					case 2://textarea
					if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
					break;
					case 3://image
					if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='' || !is_file('../..'.$_POST['field_'.$don['ID']]))$empty=1;
					break;
					case 4://checkbox
					if(!isset($_POST['field_'.$don['ID']]) || count($_POST['field_'.$don['ID']])==0)$empty=1;
					break;
					case 5://select
					if(!isset($_POST['field_'.$don['ID']]) || !is_numeric($_POST['field_'.$don['ID']]))$empty=1;
					break;
					case 6://bdd
					if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
					break;
					case 7://tiny
					if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
					break;
					case 8://géo
					if(!isset($_POST['field_'.$don['ID']]) || $_POST['field_'.$don['ID']]=='')$empty=1;
					break;
					case 9://date						
					if(!isset($_POST['field_date_'.$don['ID']]) || $_POST['field_date_'.$don['ID']]=="")$empty=1;
					else{
						list($jour, $mois, $annee) = explode('/', $_POST['field_date_'.$don['ID']]);
						if(!checkdate($mois,$jour,$annee))$empty=1; 
					}
					break;
					case 10://www
					if(!isset($_POST['field_'.$don['ID']]) || !verif_url($_POST['field_'.$don['ID']]))$empty=1;
					break;
					case 11://email
					if(!isset($_POST['field_'.$don['ID']]) || !VerifierAdresseMail($_POST['field_'.$don['ID']]))$empty=1;
					break;
					case 12://fichier
					$handle = new Upload($_FILES['field_'.$don['ID']]);
					if ((!$handle->uploaded || $handle->file_src_name=='') && !isset($_POST['fichier_dl_'.$don['ID']]))$empty=1;
					break;		
					
				}
				
				if($empty){
					$list_champ_obligatoire[]=$don['ID'];
				}
			}
		}
		//pr($list_champ_obligatoire);
		if(count($list_champ_obligatoire)==0){
			$req_elements=$db->query('select * from cms_template_element where ID_BLOC = '.$_GET['id'].' ORDER BY `ORDRE`');
			if(mysql_num_rows($req_elements)!=0){
				$req_values='';
				$req_insert='';
				$req_email='';
				$_POST=multidimensionalArrayMap('stripslashes',$_POST);
				$i=0;
				while($don=mysql_fetch_array($req_elements)){
					
					if($action=="add"){
						if($i!=0){
							$req_values.=',';
							$req_insert.=',';
						}
						$req_insert.='`FIELD_'.$don['ID'].'`';
					}
					else{
						$req_values.=',';						
						$req_values.='`FIELD_'.$don['ID'].'` = ';
					}
					
					
					if(!isset($_POST['field_'.$don['ID']]))$_POST['field_'.$don['ID']]='';
					
					switch($don['TYPE']){
						case 1://text
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
						break;
						
						case 2://textarea
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
						break;
						
						case 3://image
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
						break;
						
						case 4://checkbox
						$req_value='';
						if(count($_POST['field_'.$don['ID']])>0){
							$j=0;
							foreach ($_POST['field_'.$don['ID']] as $value){
								//echo $value;
								$req_value_opt=$db->queryUniqueValue('SELECT NAME FROM cms_template_element_options WHERE ID_ELEMENT= '.$don['ID'].' AND ORDRE = '.$value);
								if($j!=0)$req_value.='<||>';
								$req_value.=$req_value_opt;
								$j++;
							}
						}
						$req_values.="'".addslashes($req_value)."'";
						break;
						
						
						case 5://multiple
						$req_value_opt='';
						if(is_numeric($_POST['field_'.$don['ID']])){
							$req_value_opt=$db->queryUniqueValue('SELECT NAME FROM cms_template_element_options WHERE ID_ELEMENT= '.$don['ID'].' AND ORDRE = '.$_POST['field_'.$don['ID']]);
						}
						$req_values.="'".addslashes($req_value_opt)."'";
						break;

						case 6://bdd
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";						
						break;

						case 7://tiny
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";						
						break;
						
						case 8://géo
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";						
						break;
						
						case 9://date
						if(isset($_POST['field_date_'.$don['ID']])){
							$temp=explode('/',$_POST['field_date_'.$don['ID']]);
							//pr($temp);
							$time_deb=mktime(0, 0, 0, $temp[1], $temp[0], $temp[2]);
						}
						else $time_deb=0;
						$req_values.="'".$time_deb."'";
						
						break;
						
						case 10://www
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
					
						break;
						
						case 11://email
						$req_values.="'".addslashes($_POST['field_'.$don['ID']])."'";
						
						break;
						
						case 12://fichier
						$req_value='';
						$handle = new Upload($_FILES['field_'.$don['ID']]);
						if ($handle->uploaded){		
							$handle->file_new_name_body=file_name_format($_FILES['field_'.$don['ID']]['name']);
							$handle->Process('../upload/');
							if($handle->processed){
								$req_value=$handle->file_dst_name;
							}
						}else{
							$req_value=$_POST['fichier_dl_'.$don['ID']];
						}
						$req_values.="'".addslashes($req_value)."'";
						break;
						
					}
					$i++;	
				}
								
				if($action=="add")$req_final='INSERT INTO cms_template_data_'.$info_req['ID_TEMPLATE'].' ('.$req_insert.',`DT_CREATE`,`DT_MODIF`,`ID_LANG`,`ID_UNIVERS`,`ID_BLOC`,`ID_PARENT`,`ORDRE`) VALUES ('.$req_values.','.time().','.time().','.$_SESSION['langue'].','.$_SESSION['univers'].','.$_GET['id'].','.$_GET['idp'].','.($max+1).')';
				else $req_final='UPDATE cms_template_data_'.$info_req['ID_TEMPLATE'].' SET `DT_MODIF` = '.time().$req_values.' WHERE ID = '.$_GET['idr'];
				//echo $req_final;
				ecrire_log('Formulaire',addslashes($req_final));	
				$db->execute($req_final);				
				header('location:block_view.php?id='.$_GET['id'].'&idp='.$_GET['idp']);				
				die();
			}
		}
	}
	if($action=="add" || $action=="modif"){/* Affichage ajout/modif */	
	
		$jquery_date='';
		$html_tablo="
		<script type=\"text/javascript\" src=\"".URL_DIR."/tiny_mce/tiny_mce.js\"></script>
		<script type='text/javascript'>		
		".genere_file_browser()."
		</script>
		<form method='post' name='form' action='?action2=valid&id=".$_GET['id']."&idp=".$_GET['idp']."&idr=".$_GET['idr']."&action=".$action."' enctype='multipart/form-data' id='form_view'>
		";
		
		//pr($_POST);
		
		
		if($info_req['VIGNETTE']!=""){
		
			include '../js/jnotes/jquery-notes_php/notes.class.php';
			$oNote = new note('../js/jnotes/jquery-notes_notes/', '',$info_req['VIGNETTE'], '.note');
			$notes=$oNote->getNotes();

			//pr($notes);
			
			$nb_field=$db->countOf('cms_template_element','ID_BLOC = '.$_GET['id']);
		
			$list_field=array();
			for($i=1;$i<=$nb_field;$i++){
				$list_field[$i]=0;
				foreach ($notes as $value) {		
					if($value['NOTE']==$i){
						$list_field[$i]=$value['ID'];
					}
				}
			}
			
			//pr($list_field);
			
		}
		
		if(count($list_champ_obligatoire)>0){
			$html_tablo.="
			<br/>
			<div style='background-color:#FFDFDF;'>
			<b>Attention <br/></b>
			Certains champs obligatoires ne sont remplis. <span style='background-color:#F9F496;'>Ils ont été mis en évidence.</span>
			</div>	
			";
		}
		
		if($action=="modif"){
			$req_id=$db->query('select * from cms_template_data_'.$info_req['ID_TEMPLATE'].' where ID = '.$_GET['idr'].' AND ID_BLOC = '.$_GET['id'].' AND ID_PARENT = '.$_GET['idp']);
			if(mysql_num_rows($req_id)!=0){
				$don_modif=mysql_fetch_array($req_id);				
			}
		}
		
		$req_elements=$db->query('select * from cms_template_element where ID_BLOC = '.$_GET['id'].' order by `ORDRE`');
		$num_element=1;
		
		while($don=mysql_fetch_array($req_elements)){	

			if($don['OPTIONS']!='')$options_element=explode('<||>',htmlspecialchars_decode($don['OPTIONS'],ENT_NOQUOTES));
			else $options_element[0]='';			
			
			
			if($action2=="valid" && isset($_POST['field_'.$don['ID']]))$don['DEFAULT']=$_POST['field_'.$don['ID']];//si en cours de validation
			else if($action=='modif'){
				$don['DEFAULT']=$don_modif['FIELD_'.$don['ID']];
				switch($don['TYPE']){//cas spéciaux
					case 4:
					if($don['DEFAULT']!=""){
						$temp=explode('<||>',htmlspecialchars_decode($don['DEFAULT']));					
						for($i=0;$i<count($temp);$i++){
							$sel=$db->query('select ORDRE from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' AND NAME = \''.$temp[$i].'\'');
							//echo 'select ID from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' AND NAME = \''.$temp[$i].'\'';
							if(mysql_num_rows($sel)!=0){
								$don3=mysql_fetch_array($sel);
								$list_check[]=$don3['ORDRE'];
							}														
						}
					}
					break;
					
					case 5:
					$sel=$db->query('select ORDRE from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' AND NAME = \''.$don['DEFAULT'].'\'');
					//echo 'select ID from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' AND NAME = \''.$temp[$i].'\'';
					if(mysql_num_rows($sel)!=0){
						$don3=mysql_fetch_array($sel);
						$don['DEFAULT']=$don3['ORDRE'];
					}
					break;
					
					
					case 9:
					$_POST['field_date_'.$don['ID']]=date('d/m/Y',$don['DEFAULT']);
					break;
				}
			}
			else if($don['TYPE']!=3)$don['DEFAULT']=$options_element[0];//si ajout
			
			if($don['DEFAULT']=='null')$don['DEFAULT']='';
			
			$don=multidimensionalArrayMap("format",$don);
				
			if($don['REQUIRED']==1)$req_stat='';
			else $req_stat=' nosee';
			
			$color='';
			if($action2=='valid' && in_array($don['ID'],$list_champ_obligatoire)){
				$color='background-color:#F9F496;';
			}

			if($don['HELP']!="" && $don['TYPE']!=3){
				$help='class="help_tool" title="'.$don['HELP'].'"';
			}
			else $help='';
			
			if($info_req['VIGNETTE']!=""){
				$help.=' onmouseover="javascript:note_focus(\''.$list_field[$num_element].'\');void(0);"';
			}
			
			if($num_element!=1){
				$style_plus='margin-top:10px;';
			}
			else $style_plus='';
			
			$html_tablo.='				
			
			<div style="'.$style_plus.$color.'width:450px;" '.$help.'>
			<div class="label_field clear">'.$don['NAME'].'<span class="required_field'.$req_stat.'"> * </span></div><br/>				
			';
			$required_js='';
		
			switch($don['TYPE']){			
				
				case 1:	//text									
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
				$html_tablo.='<input type="text"  style="'.$width_text.'" name="field_'.$don['ID'].'"  value="'.$don['DEFAULT'].'">
				<div class="clear"></div>';
				break;
				
				case 2:	//textarea					
				switch($options_element[1]){
					case 1:
					$width_text='height:5.5em;';
					break;
					case 2:
					$width_text='height:10em;';
					break;
					case 3:
					$width_text='height:20em;';
					break;
				}					
				
				$html_tablo.='<textarea class="large_text" name="field_'.$don['ID'].'">'.$don['DEFAULT'].'</textarea>
				<div class="clear"></div>';
				break;
				
				case 3://image
				
				if($don['HELP']!=''){
					$help_plus="
					<div style=\"float:right;\">
						<div class=\"bordure_menu\" style=\"width:268px;\">
							<div class=\"info_img\"><p>".$don['HELP']."</p></div>
						</div>
					</div>";
					
				}else $help_plus='';
				
				$html_tablo.='			
				<div style="float:left;width:170px;">'.form_picture($options_element[2],$options_element[4],'field_'.$don['ID'],$don['DEFAULT'],ADD_DIR.$options_element[0],'',array(),0,0).'</div>
				'.$help_plus.'
				<div style="clear:both;"></div>
				';		
				break;
				
				case 4:	//checkbox				
				$req_options=$db->query('select * from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
				$j=0;
				
				if($action2=='valid'){
					$list_check=array();
					if(isset($_POST['field_'.$don['ID']])){
						foreach ($_POST['field_'.$don['ID']] as $value){
							$list_check[]=$value;
						}	
					}
				}
				
				while($don_opt=mysql_fetch_array($req_options)){					
					$sel='';
					
					if(($action2=='valid' || $action=='modif') && count($list_check)!=0){
						if(in_array($j,$list_check))$sel='checked="checked"';
					}
					else if($don_opt['DEFAULT']==1)$sel='checked="checked"';
									
					
					$html_tablo.='
					<div style="height:30px;">
						<div style="float:left;"><input type="checkbox" value="'.$j.'" name="field_'.$don['ID'].'[]" '.$sel.'></div>
						<div style="float:left;"><label style="font-size:12px;">'.$don_opt['NAME'].'</label></div>
						<div class="clear"></div>
					</div>
					';
					$j++;
				}				
				
				break;
				
				case 5:	//select	
				$req_options=$db->query('select * from cms_template_element_options where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
				$j=0;
				$html_tablo.='<select name="field_'.$don['ID'].'">';
				while($don_opt=mysql_fetch_array($req_options)){
				
					if($don['DEFAULT']==$j)$required='selected="selected"';
					else $required='';
				
					$html_tablo.='
					<option value="'.$j.'" '.$required.'>'.$don_opt['NAME'].'</option>
					';					
					
					$j++;
				}
				$html_tablo.='</select>
				<div class="clear"></div>';
				
				break;
				
				case 6:	//bdd
				$requete=sprintf3($options_element[0],array("lang"=>$_SESSION['langue'],"univers"=>$_SESSION['univers']));
				//echo $requete;
				$req2=$db->query($requete);
				if(mysql_num_rows($req2)!=0){
					$html_tablo.='<select name="field_'.$don['ID'].'">';
					while($don2=mysql_fetch_array($req2)){
						$don2=multidimensionalArrayMap("format",$don2);
						if($don2[$options_element[1]]==$don["DEFAULT"])$sel='selected="selected"';
						else $sel='';				
						$html_tablo.='<option value="'.$don2[$options_element[1]].'" '.$sel.'>'.$don2[$options_element[2]].'</option>';
					}
					$html_tablo.='</select>';
				}
				else {
					$html_tablo.='<input type="hidden" name="field_'.$don['ID'].'" value=""> Aucun choix !';
				}
				break;
				
				case 7://tiny					
				$html_tablo.='
				<script type="text/javascript">
				'.genere_tiny_mce('field_'.$don['ID'],3).'
				</script>
				<textarea name="field_'.$don['ID'].'" type="text" rows="15" cols="80" style="width: 80%" id="field_'.$don['ID'].'"  class="tinymce"/>'.$don['DEFAULT'].'</textarea>
				';
				break;
				
				case 8://géo

				
				if($don['DEFAULT']!=''){
					$temp=explode(',',$don['DEFAULT']);
					$latitude=$temp[0];
					$longitude=$temp[1];
				}
				else{
					$latitude=get_c('LATITUDE_DEF');
					$longitude=get_c('LONGITUDE_DEF');
				}
				$alea=mt_rand();
				
				$html_tablo.='
			
				<b>Coordonnées :</b> <input type="text" value="'.$don['DEFAULT'].'" id="field_'.$don['ID'].'" name="field_'.$don['ID'].'" style="width:200px;">&nbsp;<a href="javascript:call_mother_frame(\'field_'.$don['ID'].'\','.$alea.');void(0);">Sélectionner ces coordonnées</a> <img src="'.URL_DIR.'/images/loader.gif" style="display:none;" id="img_field_'.$don['ID'].'"><span id="span_field_'.$don['ID'].'" style="display:none;">Mise à jour</span>
				<div class="clear"></div><br/><hr/>
				<script type="text/javascript">
				function iframe_time_'.$don['ID'].'(){
						$("#zone_iframe_'.$don['ID'].'").attr({"src":"http://www.demo-cms.fr/cms/admin/gmap.php?field=field_'.$don['ID'].'&lat='.$latitude.'&lon='.$longitude.'&numalea='.$alea.'"});	
					}
					
				$(function(){		  
					var src = \'http://www.demo-cms.fr/cms/admin/gmap.php?field=field_'.$don['ID'].'&lat='.$latitude.'&lon='.$longitude.'&numalea='.$alea.'\';
					//alert(src);
					var iframe_'.$don['ID'].' = $( \'<iframe id="zone_iframe_'.$don['ID'].'" src="\' + src + \'" width="450" height="500" scrolling="no" frameborder="0"><\/iframe>\' ) .appendTo( \'#iframe_'.$don['ID'].'\' );		
					setTimeout(\'iframe_time_'.$don['ID'].'()\',1000);		
				  
				});
				</script>
				<div id="iframe_'.$don['ID'].'"></div>';
				break;
				
				case 9://date
				if($jquery_date!='')$jquery_date.=',';
				$jquery_date.='#field_date_'.$don['ID'];
				
				$html_tablo.='
				<div>
					<input type="text" name="field_date_'.$don['ID'].'" id="field_date_'.$don['ID'].'"  style="width:30%;" maxlength="10" value="'.$_POST['field_date_'.$don['ID']].'">						
				</div>
				<div class="clear"></div>';
				break;

				case 10://www					
				$html_tablo.='<div><input type="text" name="field_'.$don['ID'].'" style="width:100%;" value="'.$don['DEFAULT'].'"></div>
				<div class="clear"></div>';
				break;
				
				
				case 11://email					
				$html_tablo.='<div><input type="text" name="field_'.$don['ID'].'"  style="width:100%;" value="'.$don['DEFAULT'].'"></div>
				<div class="clear"></div>';
				break;
				
				case 12://fichier	
				if($don["DEFAULT"]!=""){
					$html_tablo.='<div><a href="'.URL_DIR.'/upload/'.$don["DEFAULT"].'" target="_blank">Voir le fichier téléchargé</a><input type="hidden" name="fichier_dl_'.$don['ID'].'" value="'.$don["DEFAULT"].'"></div>';
				}
				$html_tablo.='<div><input type="file" name="field_'.$don['ID'].'" ></div>
				<div class="clear"></div>';
				break;
			
			}
			$html_tablo.='</div>';
			$num_element++;
		}
		?>
		<div style="width:450px;float:left;">
			<?
			echo $html_tablo;
			?>
		</div>
		<div style="width:180px;margin-left:470px;position: fixed;">
			<?
			if($info_req['VIGNETTE']!=""){
				echo '<img src="'.$info_req['VIGNETTE'].'" alt="" class="jquery-note_1-1" style="border:1px solid #CCCCCC;"/>';
			?>
			<script type="text/javascript">
			$(document).ready(function(){
				//alert('la');
				$('.jquery-note_1-1').jQueryNotes({
					operator: '<?echo URL_DIR;?>/js/jnotes/jquery-notes_php/notes.php',
					allowAuthor:false,
					allowLink:false,
					allowHide: true,
					allowReload: false,
					allowHide: false,
					allowAdd: false,
					hideNotes: true
				});

			});

			function note_focus(foc_zone){
				$('.notes .note').css({
					visibility: 'hidden'
				});
				$('#n_1-'+foc_zone).css({'visibility':'visible'});
			}
			</script>
			<?
			}
			?>
		</div>
		<div class="clear"></div>
		<script type='text/javascript'>
		$(document).ready(function() {
			<?
			if ($jquery_date!=''){
			?>
			$( "<?echo $jquery_date;?>").datepicker({ 
			changeMonth: true,	
			changeYear: true, showOn: "button",
			buttonImage: "<?echo URL_DIR;?>/images/date.png"
			});
			<?
			}
			?>
			// select all desired input fields and attach tooltips to them
			$(".help_tool").tooltip({
				// place tooltip on the right edge
				position: "top center",
				// a little tweaking of the position
				offset: [-2, 10],
				// use the built-in fadeIn/fadeOut effect
				effect: "fade",
				// custom opacity setting
				opacity: 0.7
			});
			$(".btn_style").hover( function () {
				$(this).css({'background-color':'#E2E2E2'});
			},function(){
				$(this).css({'background-color':'#f8f8f8'});
			});
			$(".btn_style,.info_img").corner("5px").parent().css('padding', '1px').corner("5px");

		});
		function call_mother_frame(field_name,alea){
			//alert(alea);
			$('#img_'+field_name).show('normal');
			$('#span_'+field_name).show('normal');
			$.post("get_info_url.php",{id_data:alea},
				function success(data){
					$('#'+field_name).val(data.content);
					$('#img_'+field_name).hide('normal');
					$('#span_'+field_name).hide('normal');
				}
			, "json");
		}
		function valide_form(){
			document.forms['form'].submit();
		}
		</script>
		
		<div style="margin-top:15px;clear:both;">
			<div class="bordure_menu" style='width:155px;float:left;' onclick="javascript:valide_form();">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider</p></div>
			</div>
			<?if($max!=0 && $info_req['NB_MAX_ELEMENT']!=1){?>
			<div class="bordure_menu" style='width:155px;margin-left:30px;float:left;' onclick="javascript:redir('<?echo "?id=".$_GET['id']."&idp=".$_GET['idp'];?>');">
				<div style='width:155px;background-image:url("<?echo URL_DIR;?>/images/btn_cross.png");' class="btn_style"><p>Abandonner</p></div>
			</div>
		</div>		
			<?		
		}
	}else{
	//affichage de la zone de gestion du bloc
	
		if($max==0){//si zéro on redirige directement vers le formulaire d'ajout
			header('location:block_view.php?action=add&id='.$_GET['id'].'&idp='.$_GET['idp']);
			die();
		}else{
			
			echo "
			<div style='height:15px;width:100%;'>&nbsp;</div>
			<div class='clear'></div>
			<table cellpadding='0' cellspacing='0' border='0' id='table_view'>
				<thead>
				<tr>";
			//on va d'abord rechercher si il y a des champs de type text ou  image
			//recherche texte 
			$req_search_text=$db->query('SELECT ID,NAME FROM cms_template_element WHERE ID_BLOC = '.$_GET['id'].' AND (`TYPE` = 1 OR `TYPE` = 2 OR `TYPE` = 5 OR `TYPE` = 4 OR `TYPE` = 7 OR `TYPE` = 8 OR `TYPE` = 9 OR `TYPE` = 10 OR `TYPE` = 11 OR `TYPE` = 12) ORDER BY ORDRE LIMIT 0,1');
			$text_ready=0;
			if(mysql_num_rows($req_search_text)!=0){
				$text_ready=1;
				$req_result=mysql_fetch_array($req_search_text);
				$id_text_field=$req_result['ID'];
				$name_text_field=stripslashes($req_result['NAME']);
				echo "
					<th>".strCut($name_text_field,40)."</th>";
			}
			//recherche image 
			$req_search_img=$db->query('SELECT ID,NAME FROM cms_template_element WHERE ID_BLOC = '.$_GET['id'].' AND `TYPE` = 3 ORDER BY ORDRE LIMIT 0,1');
			$img_ready=0;
			if(mysql_num_rows($req_search_img)!=0){
				$img_ready=1;
				$req_result=mysql_fetch_array($req_search_img);
				$id_img_field=$req_result['ID'];
				$name_img_field=stripslashes($req_result['NAME']);
				echo "<th>".strCut($name_img_field,40)."</th>";
			}
			
			echo "	<th>Ordre</th>";
			echo "	<th>Modifier</th>";
			
			if($max>$info_req['NB_MIN_ELEMENT'] || $info_req['NB_MIN_ELEMENT']==0)echo "	<th>Supprimer</th>";
			
			echo "</tr></thead>";
		
			$req_record=$db->query('select * from cms_template_data_'.$info_req['ID_TEMPLATE'].' WHERE ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' AND ID_BLOC = '.$_GET['id'].' AND ID_PARENT = '.$_GET['idp'].' ORDER BY ORDRE');
			while($don=mysql_fetch_array($req_record)){
				echo '
				<tr>';
				if($text_ready){
					echo'<td>'.strCut(strip_tags(stripslashes($don['FIELD_'.$id_text_field])),40).'</td>';
				}
				if($img_ready){
					echo'<td><img src="'.URL_DIR.'/admin/get-thumb.php?url='.urlencode(stripslashes($don['FIELD_'.$id_img_field])).'&width=100&height=100"></td>';
				}
				echo '	<td>
				'.genere_form_ordre('record_ordre','?action=ordre_r&id='.$_GET['id'].'&idp='.$_GET['idp'],1,$max,$don['ORDRE'],0,array('ID_RECORD'=>$don['ID'])).'
				</td>';
				echo '	<td><a href="?action=modif&idr='.$don['ID'].'&id='.$_GET['id'].'&idp='.$_GET['idp'].'"><img src="'.URL_DIR.'/images/edit.png"></a></td>';
				if($max>$info_req['NB_MIN_ELEMENT'] || $info_req['NB_MIN_ELEMENT']==0)echo '	<td><a href="?action=suppr&idr='.$don['ID'].'&id='.$_GET['id'].'&idp='.$_GET['idp'].'" onclick="return confirm(\'Etes-vous sûr ?\');"><img src="'.URL_DIR.'/images/delete.png"></a></td>';
				echo '
				</tr>';
			}
			
			echo '
			</table>';
		}
		
	}	
}
else{
	header('location:modele_bloc.php?id='.$info_req['ID_TEMPLATE']);
	die();
}?>

	</div>
</div>
</body>
</html>
<?
ob_flush();
?>