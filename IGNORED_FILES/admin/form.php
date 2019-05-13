<?
$id_module=16;
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='add'){
	$ariane_element2['URL']="form.php";
	$ariane_element2['LIBELLE']="Ajouter un formulaire";					
}elseif($action=='edit'){
	$ariane_element2['URL']="form.php";
	$ariane_element2['LIBELLE']="Modifier un formulaire";
}elseif($action=='data'){
	$ariane_element2['URL']="form.php";
	$ariane_element2['LIBELLE']="Consulter les enregistrements";
}

include('header.php');


/*Ajout*/
if($action=='add_r'){

	//d'abord on capte les informations
	$elements_form=array();
	parse_str($_POST['ordre'], $output);
	
	
	if(!isset($_POST['form_nb']) || !is_numeric($_POST['form_nb']))$_POST['form_nb']=0;
	
	$req=$db->execute("INSERT INTO cms_formulaire (`NAME`,`DESC`,`SUCCESS`,`ACTIF`,`ID_LANG`,`ID_UNIVERS`,`LIMITATION`,`ID_USER`,`DT_CREATE`,`DT_MODIF`,`FULL_MSG`) VALUES ('".addslashes($_POST['form_name'])."','".addslashes($_POST['form_desc'])."','".addslashes($_POST['form_success'])."',".$_POST['form_active'].",".$_SESSION['langue'].",".$_SESSION['univers'].",".$_POST['form_nb'].",".$_SESSION['id'].",".time().",".time().",'".addslashes($_POST['form_complet'])."')");
	$id_form=$db->lastInsertedId();
	
	$table_donnees="
	CREATE TABLE `cms_formulaire_data_".$id_form."` (
	`ID` int(10) NOT NULL AUTO_INCREMENT,
	";
	
	$nb_field=0;
	while (list ($key, $val) = each ($_POST['name_element']) ) {
 
		if($_POST['default_'.$key]=='undefined')$_POST['default_'.$key]='';
		
		$elements_form[$key]['NAME']=$val;
		$elements_form[$key]['ID_FIELD']=$_POST['id_field_'.$key];
		$elements_form[$key]['ORDRE']=array_search($_POST['id_field_'.$key],$output['listItem'])+1;
		$elements_form[$key]['TYPE']=$_POST['type_element_'.$key];
		$elements_form[$key]['DEFAULT']=$_POST['default_'.$key];
		$elements_form[$key]['HELP']=$_POST['help_'.$key];
		$elements_form[$key]['REQUIRED']=$_POST['required_'.$key];
		$elements_form[$key]['SIZE']=$_POST['size_'.$key];	
		
		if($elements_form[$key]['REQUIRED']=="")$elements_form[$key]['REQUIRED']=0;
		if($elements_form[$key]['SIZE']=="")$elements_form[$key]['SIZE']=0;
		

		$req_element=$db->execute("INSERT INTO cms_formulaire_champ (`NAME`,`TYPE`,`ORDRE`,`HELP`,`REQUIRED`,`ID_FORM`,`SIZE`,`DEFAULT`) VALUES ('".addslashes($elements_form[$key]['NAME'])."',".$elements_form[$key]['TYPE'].",".$elements_form[$key]['ORDRE'].",'".addslashes($elements_form[$key]['HELP'])."',".$elements_form[$key]['REQUIRED'].",".$id_form.",".$elements_form[$key]['SIZE'].",'".addslashes($elements_form[$key]['DEFAULT'])."')");
		
		$id_element=$db->lastInsertedId();
		$table_donnees.="
		`FIELD_".$id_element."` text NOT NULL,";
		
		
		if($elements_form[$key]['TYPE']==3 || $elements_form[$key]['TYPE']==5){
			$i=0;
			while(isset($_POST['name_option_'.$key.'_'.$i])){
				$elements_form[$key]['ELEMENTS'][]=$_POST['name_option_'.$key.'_'.$i];
				if($elements_form[$key]['DEFAULT']==$i)$sel=1;
				else $sel=0;
				
				$req_element_options="INSERT INTO cms_formulaire_champ_option (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".addslashes($_POST['name_option_'.$key.'_'.$i])."',".$i.",".$sel.")";
				$db->execute($req_element_options);
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

				
				$req_element_options="INSERT INTO cms_formulaire_champ_option (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".addslashes($info_opt['LIBELLE'])."',".$i.",".$info_opt['DEFAULT'].")";
				$db->execute($req_element_options);				
				
				$i++;
			}			
		}
		$nb_field++;
	}
	
	$table_donnees.="
	`DT_CREATE` int(10) NOT NULL,
	`IP` text NOT NULL,
	PRIMARY KEY (`ID`)
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	
	//echo $table_donnees;
	
	$db->execute($table_donnees);
	/*
	pr($elements_form);
	pr($_POST);
	pr($output['listItem']);
	*/
	$_SESSION['notification'][]=array(1,"Formulaire","Le formulaire a été ajouté.");
	header('location:form.php');	
	die();
}
/*Modification*/

if($action=='edit_r' && is_numeric($_POST['id'])){

	$id_form=$_POST['id'];
	
	if(!isset($_POST['form_nb']) || !is_numeric($_POST['form_nb']))$_POST['form_nb']=0;
	
	
	$req_element=$db->execute("UPDATE cms_formulaire SET `NAME` = '".addslashes($_POST['form_name'])."',`ACTIF` = ".$_POST['form_active'].",`FULL_MSG` = '".addslashes($_POST['form_complet'])."',`LIMITATION` = ".$_POST['form_nb'].",`DESC` = '".addslashes($_POST['form_desc'])."', `SUCCESS` = '".addslashes($_POST['form_success'])."', `DT_MODIF` = ".time()." WHERE ID = ".$id_form);
		
	$form_table="`cms_formulaire_data_".$id_form."`";
	$req_form_elements=$db->query('SELECT * FROM cms_formulaire_champ WHERE ID_FORM = '.$id_form);
	$list_elements=array();
	while($don=mysql_fetch_array($req_form_elements)){
		$list_elements[]=$don['ID'];
	}
	//pr($list_elements);	

	//d'abord on capte les informations
	$elements_form=array();
	parse_str($_POST['ordre'], $output);

	$nb_field=0;
	while (list ($key, $val) = each ($_POST['name_element']) ) {
 
		if($_POST['default_'.$key]=='undefined')$_POST['default_'.$key]='';
		if($_POST['actual_id_'.$key]=='undefined')$_POST['actual_id_'.$key]='';
		
		$elements_form[$key]['NAME']=$val;
		$elements_form[$key]['ID_FIELD']=$_POST['id_field_'.$key];
		$elements_form[$key]['ORDRE']=array_search($_POST['id_field_'.$key],$output['listItem'])+1;
		$elements_form[$key]['TYPE']=$_POST['type_element_'.$key];
		$elements_form[$key]['DEFAULT']=$_POST['default_'.$key];
		$elements_form[$key]['HELP']=$_POST['help_'.$key];
		$elements_form[$key]['REQUIRED']=$_POST['required_'.$key];
		$elements_form[$key]['SIZE']=$_POST['size_'.$key];	
		$elements_form[$key]['ACTUAL_ID']=$_POST['actual_id_'.$key];	
		
		if($elements_form[$key]['REQUIRED']=="")$elements_form[$key]['REQUIRED']=0;
		if($elements_form[$key]['SIZE']=="")$elements_form[$key]['SIZE']=0;
		
		if(is_numeric($elements_form[$key]['ACTUAL_ID']) && in_array($elements_form[$key]['ACTUAL_ID'],$list_elements)){/*Si déjà présent*/
		
			removeFromArray($list_elements,$elements_form[$key]['ACTUAL_ID']);
			//echo $elements_form[$key]['NAME'];		
			$req_element="UPDATE cms_formulaire_champ SET `NAME` = '".addslashes($elements_form[$key]['NAME'])."',`TYPE` = ".$elements_form[$key]['TYPE'].",`ORDRE` = ".$elements_form[$key]['ORDRE'].", `HELP` = '".addslashes($elements_form[$key]['HELP'])."', `REQUIRED` = ".$elements_form[$key]['REQUIRED'].", `SIZE` = ".$elements_form[$key]['SIZE'].", `DEFAULT` = '".addslashes($elements_form[$key]['DEFAULT'])."' WHERE `ID` = ".$elements_form[$key]['ACTUAL_ID'];
			//echo $req_element.'<br/>';
			$db->execute($req_element);
			$id_element=$elements_form[$key]['ACTUAL_ID'];
			$req_suppr_opt='DELETE FROM cms_formulaire_champ_option WHERE ID_ELEMENT = '.$id_element;
			//echo $req_suppr_opt;
			$db->execute($req_suppr_opt);
			
		}else{//si nouvel élément
		
			$req_element=$db->execute("INSERT INTO cms_formulaire_champ (`NAME`,`TYPE`,`ORDRE`,`HELP`,`REQUIRED`,`ID_FORM`,`SIZE`,`DEFAULT`) VALUES ('".addslashes($elements_form[$key]['NAME'])."',".$elements_form[$key]['TYPE'].",".$elements_form[$key]['ORDRE'].",'".addslashes($elements_form[$key]['HELP'])."',".$elements_form[$key]['REQUIRED'].",".$id_form.",".$elements_form[$key]['SIZE'].",'".addslashes($elements_form[$key]['DEFAULT'])."')");
		
			$id_element=$db->lastInsertedId();			
			$req_add_element='ALTER TABLE '.$form_table.' ADD `FIELD_'.$id_element.'` TEXT NOT NULL';
			
			
			$db->execute($req_add_element);
		}	
			
		
		if($elements_form[$key]['TYPE']==3 || $elements_form[$key]['TYPE']==5){
			$i=0;
			while(isset($_POST['name_option_'.$key.'_'.$i])){
				$elements_form[$key]['ELEMENTS'][]=$_POST['name_option_'.$key.'_'.$i];
				if($elements_form[$key]['DEFAULT']==$i)$sel=1;
				else $sel=0;							
			
				$req_element_options="INSERT INTO cms_formulaire_champ_option (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".addslashes($_POST['name_option_'.$key.'_'.$i])."',".$i.",".$sel.")";
				//echo $req_element_options;
				$db->execute($req_element_options);
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
				
				$req_element_options="INSERT INTO cms_formulaire_champ_option (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".addslashes($info_opt['LIBELLE'])."',".$i.",".$info_opt['DEFAULT'].")";
				//echo $req_element_options;
				$db->execute($req_element_options);			
				
				$i++;
			}			
		}
		$nb_field++;
		
	}
	
	$nb_elements=count($list_elements);
	if($nb_elements>0){
		for($i=0;$i<$nb_elements;$i++){
			$db->execute('ALTER TABLE '.$form_table.' DROP COLUMN `FIELD_'.$list_elements[$i].'`');
			$db->execute('DELETE FROM cms_formulaire_champ WHERE ID = '.$list_elements[$i]);
			$db->execute('DELETE FROM cms_formulaire_champ_option WHERE ID_ELEMENT = '.$list_elements[$i]);
		}
	}
	/*
	pr($list_elements);
	pr($elements_form);
	*/
	
	$_SESSION['notification'][]=array(1,"Formulaire","Le formulaire a été modifié.");
	header('location:form.php');	
	die();
}
/*Suppression*/
if($action=='delete' && isset($_GET['id']) && is_numeric($_GET['id'])){

	$db->execute("DELETE FROM cms_formulaire WHERE ID = ".$_GET['id']);
	$req_field=$db->query("SELECT * FROM cms_formulaire_champ WHERE ID_FORM = ".$_GET['id']);
	while($don=mysql_fetch_array($req_field)){
		$db->execute("DELETE FROM cms_formulaire_champ_option WHERE ID_ELEMENT = ".$don['ID']);
	}
	$db->query("DELETE FROM cms_formulaire_champ WHERE ID_FORM = ".$_GET['id']);
	$db->query("DROP TABLE `cms_formulaire_data_".$_GET['id']."`");

	$_SESSION['notification'][]=array(1,"Formulaire","Le formulaire a été supprimé.");
	header('location:form.php');		
	die();
	
}
if($action=='supp_record' && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['id_record']) && is_numeric($_GET['id_record'])){
	$db->execute('DELETE FROM cms_formulaire_data_'.$_GET['id'].' WHERE ID = '.$_GET['id_record']);
	$_SESSION['notification'][]=array(1,"Formulaire","L'enregistrement a été supprimé.");
	header('location:form.php?action=data&id='.$_GET['id']);		
	die();
}
if($action=="duplicate" && isset($_GET['id']) && is_numeric($_GET['id'])){
	$req_form=$db->query('select * from cms_formulaire where ID  = '.$_GET['id']);
	if(mysql_num_rows($req_form)!=0){
	
		$don_info=mysql_fetch_array($req_form);
		$don_info=array_map('stripslashes',$don_info);
		$don_info=array_map('addslashes',$don_info);
		$req=$db->execute("INSERT INTO cms_formulaire (`NAME`,`DESC`,`SUCCESS`,`ACTIF`,`ID_LANG`,`ID_UNIVERS`,`LIMITATION`,`ID_USER`,`DT_CREATE`,`DT_MODIF`,`FULL_MSG`) VALUES ('".$don_info['NAME']." (copie)','".$don_info['DESC']."','".$don_info['SUCCESS']."',".$don_info['ACTIF'].",".$_SESSION['langue'].",".$_SESSION['univers'].",".$don_info['LIMITATION'].",".$_SESSION['id'].",".time().",".time().",'".$don_info['FULL_MSG']."')");
		$id_form=$db->lastInsertedId();
		$table_donnees="
		CREATE TABLE `cms_formulaire_data_".$id_form."` (
		`ID` int(10) NOT NULL AUTO_INCREMENT,
		";
		
		$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id']);
		if(mysql_num_rows($req_elements)!=0){
			while($don_elements=mysql_fetch_array($req_elements)){
				$don_elements=array_map('stripslashes',$don_elements);
				$don_elements=array_map('addslashes',$don_elements);
				$req_element=$db->execute("INSERT INTO cms_formulaire_champ (`NAME`,`TYPE`,`ORDRE`,`HELP`,`REQUIRED`,`ID_FORM`,`SIZE`,`DEFAULT`) VALUES ('".$don_elements['NAME']."',".$don_elements['TYPE'].",".$don_elements['ORDRE'].",'".$don_elements['HELP']."',".$don_elements['REQUIRED'].",".$id_form.",".$don_elements['SIZE'].",'".$don_elements['DEFAULT']."')");
				$id_element=$db->lastInsertedId();
				$table_donnees.="
				`FIELD_".$id_element."` text NOT NULL,";
		
				$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don_elements['ID']);
				if(mysql_num_rows($req_options)!=0){
					while($don_options=mysql_fetch_array($req_options)){
						$don_options=array_map('stripslashes',$don_options);
						$don_options=array_map('addslashes',$don_options);
						$db->execute("INSERT INTO cms_formulaire_champ_option (`ID_ELEMENT`,`NAME`,`ORDRE`,`DEFAULT`) VALUES (".$id_element.",'".$don_options['NAME']."',".$don_options['ORDRE'].",".$don_options['DEFAULT'].")");
					}
				}				
			}
		}
		
		$table_donnees.="
		`DT_CREATE` int(10) NOT NULL,
		`IP` text NOT NULL,
		PRIMARY KEY (`ID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
	
		$db->execute($table_donnees);
	}
}
//ajouter un email
if($action=='add_email'&& isset($_GET['id']) && is_numeric($_GET['id'])){
	$db->execute('INSERT INTO cms_formulaire_email (`ID_FORM`,`EMAIL`) VALUES ('.$_GET['id'].',\''.addslashes($_POST['email']).'\')');
	$_SESSION['notification'][]=array(1,"Formulaire","L'email a été ajouté.");
	header('location:form.php?action=email&id='.$_GET['id']);		
	die();
}
//modifier un email
if($action=='edit_email_r' && isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['id_email']) && is_numeric($_POST['id_email'])){
	$db->execute('UPDATE cms_formulaire_email SET `EMAIL` = \''.addslashes($_POST['email']).'\' WHERE ID = '.$_POST['id_email']);
	$_SESSION['notification'][]=array(1,"Formulaire","L'email a été modifié.");
	header('location:form.php?action=email&id='.$_POST['id']);		
	die();
}
//supprimer un email
if($action=='suppr_email' && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['id_email']) && is_numeric($_GET['id_email'])){
	$db->execute('DELETE FROM cms_formulaire_email WHERE ID = '.$_GET['id_email']);
	$_SESSION['notification'][]=array(1,"Formulaire","L'email a été supprimé.");
	header('location:form.php?action=email&id='.$_GET['id']);		
	die();
}


if($action=='email' || $action=='data')$array_menu[]=array('URL'=>'form.php','IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
else if($action=='edit_email')$array_menu[]=array('URL'=>'form.php?action=email&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
else if($action=='data_view')$array_menu[]=array('URL'=>'form.php?action=data&id='.$_GET['id'],'IMG'=>URL_DIR.'/images/btn_prev.png','LIBELLE'=>'Retour');
else if($action=='')$array_menu[]=array('URL'=>'?action=add','IMG'=>URL_DIR.'/images/btn_add.png','LIBELLE'=>'Ajouter un formulaire','WIDTH'=>'175px;');
echo genere_sous_menu_admin($array_menu);
if($action=='add' || $action=='edit'){

$nb_form=1;
$nb_field_form=0;
$form['TITLE']='Titre du formulaire';
$form['DESC']='Description du formulaire';
$form['SUCCESS']="Merci d\'avoir rempli ce formulaire !";
$form['COMPLET']="Désolé ce formulaire est complet.";
$form['NB']="";
$form['ACTIVE']=1;

if($action=="edit" && is_numeric($_GET['id'])){
	
	$req=$db->query('select * from cms_formulaire where ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
		$form_info=mysql_fetch_array($req);
		$form_info=array_map("format",$form_info);
		$form['TITLE']=mynl2br($form_info['NAME']);
		$form['DESC']=mynl2br($form_info['DESC']);
		$form['SUCCESS']=mynl2br($form_info['SUCCESS']);
		$form['ACTIVE']=$form_info['ACTIF'];
		$form['COMPLET']=mynl2br($form_info['FULL_MSG']);
		$form['NB']=$form_info['LIMITATION'];
		if($form['NB']==0){
			$form['NB']='';
		}
		
		$nb_field_form=$db->countOf('cms_formulaire_champ','ID_FORM = '.$_GET['id']);
		$nb_form=$nb_field_form+1;
		
		$js_tablo="";
		$html_tablo="";
		
		$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' ORDER BY `ORDRE`');
		if(mysql_num_rows($req_elements)!=0){
			$i=1;
			while($don=mysql_fetch_array($req_elements)){		

				$don=array_map("format",$don);
					
				if($don['REQUIRED']==1)$req_stat='';
				else $req_stat=' nosee';
				
				$html_tablo.='
				<li id="listItem_'.$i.'" onclick="javascript:lance_modif('.$i.');void(0);"> 				
				<div style="float:left;min-height:25px;"><span id="label_'.$i.'" class="label_field">'.mynl2br($don['NAME']).'</span><span class="required_field'.$req_stat.'" id="required_'.$i.'"> * </span></div>
				<div class="zone_bouton"><img src="'.URL_DIR.'/images/btn_hand.gif" alt="déplacer"  class="handle" style="cursor:move;" /> <a><img src="'.URL_DIR.'/images/pencil.png" alt="modifier"  /></a> <a href="javascript:suppr_element('.$i.');void(0);"><img src="'.URL_DIR.'/images/delete.png" alt="supprimer"/></a></div>
				<div class="clear"></div>			
				<div id="zone_form_'.$i.'" >
				';
			
				switch($don['TYPE']){			
					
					case 1:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=1;
					element['ACTUAL_ID']=".$don['ID'].";
					element['SIZE']=".$don['SIZE'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					
					switch($don['SIZE']){
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
					$html_tablo.='<div class="width_form_element"><input type="text" readonly="readonly" id="text_'.$i.'" style="width:'.$width_text.'"></div>';
					break;
					
					case 2:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=2;
					element['ACTUAL_ID']=".$don['ID'].";
					element['SIZE']=".$don['SIZE'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					
					switch($don['SIZE']){
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
					
					$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
					$list_opt='';
					$list_opt_js='';
					$j=0;
			
					
					while($don_opt=mysql_fetch_array($req_options)){
						$html_tablo.='
						<div style="float:left;"><input type="radio"></div>
						<div style="float:left;margin-left:5px;"><label id="choice_'.$i.'_'.$j.'">'.$don_opt['NAME'].'</label></div>
						<div class="clear"></div>
						';
						
						$list_opt_js.="
						element['ELEMENTS'][".$j."]='".mynl2br($don_opt['NAME'])."';
						";
						$j++;
					}
					
					if($don['DEFAULT']=="")$don['DEFAULT']='null';
					
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=3;
					element['ACTUAL_ID']=".$don['ID'].";
					element['SIZE']=".$don['SIZE'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['DEFAULT']=".$don['DEFAULT'].";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					element['ELEMENTS']=new Array();
					".$list_opt_js."
					tab_form.push(element);					
					";
					
					break;
					
					case 4:
					
					$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
					$list_opt='';
					$list_opt_js='';
					$j=0;
					while($don_opt=mysql_fetch_array($req_options)){
					
						if($don_opt['DEFAULT']==1)$sel='checked="checked"';
						else $sel='';
						
						
						$html_tablo.='
						<div style="float:left;"><input type="checkbox" id="check_'.$i.'_'.$j.'" '.$sel.'></div>
						<div style="float:left;margin-left:5px;"><label id="choice_'.$i.'_'.$j.'">'.$don_opt['NAME'].'</label></div>
						<div class="clear"></div>
						';
						
						$list_opt_js.="						
						element['ELEMENTS'][".$j."]= new Array();
						element['ELEMENTS'][".$j."]['LIBELLE']='".mynl2br($don_opt['NAME'])."';
						element['ELEMENTS'][".$j."]['DEFAULT']=".$don_opt['DEFAULT'].";
						";
						$j++;
					}
					
					
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=4;
					element['ACTUAL_ID']=".$don['ID'].";
					element['SIZE']=".$don['SIZE'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					element['ELEMENTS']=new Array();
					".$list_opt_js."
					tab_form.push(element);					
					";
					
					break;
					
					case 5:
					
					$req_options=$db->query('select * from cms_formulaire_champ_option where ID_ELEMENT = '.$don['ID'].' ORDER BY `ORDRE`');
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
					
					if($don['DEFAULT']=="")$don['DEFAULT']='null';
					
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=5;
					element['ACTUAL_ID']=".$don['ID'].";
					element['SIZE']=".$don['SIZE'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['DEFAULT']=".mynl2br($don['DEFAULT']).";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
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
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					$html_tablo.='<span style="float:left;"><input type="text" readonly="readonly"><br/><label>Nom</label></span> <span style="float:left;margin-left:10px;"><input type="text" readonly="readonly"><br/><label>Prénom</label></span><div class="clear"></div>';
					break;
					
					case 7:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=7;
					element['ACTUAL_ID']=".$don['ID'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					$html_tablo.='<div class="width_form_element"><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Rue</label></div>
					<div class="width_form_element"><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Rue seconde ligne</label></div>
					<div style="display:inline;float:left;width:48%;"><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Code postal</label></div>
					<div style="display:inline;float:right;width:48%;"><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Ville</label></div><div class="clear"></div>';
					break;
					
					case 8:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=8;
					element['ACTUAL_ID']=".$don['ID'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					$html_tablo.='<div><input type="text" readonly="readonly" style="width:50%;"></div>';
					break;
					
					case 9:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=9;
					element['ACTUAL_ID']=".$don['ID'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
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
					element['NAME']='".mynl2br($don['NAME'])."';
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					$html_tablo.='<div class="width_form_element"><input type="text" readonly="readonly" value="http://" style="width:100%;"></div>';
					break;
					
					
					case 11:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=11;
					element['ACTUAL_ID']=".$don['ID'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					$html_tablo.='<div  class="width_form_element"><input type="text" readonly="readonly" value="@" style="width:100%;"></div>';
					break;
					
					case 12:
					$js_tablo.="
					element= new Array();
					element['ID']=".$i.";
					element['TYPE']=12;
					element['ACTUAL_ID']=".$don['ID'].";
					element['REQUIRED']=".$don['REQUIRED'].";
					element['NAME']='".mynl2br($don['NAME'])."';
					element['DEFAULT']='".mynl2br($don['DEFAULT'])."';
					element['HELP']='".mynl2br($don['HELP'])."';
					tab_form.push(element);					
					";
					$html_tablo.='<div><input type="file" readonly="readonly"></div>';
					break;
				
				}		
				

				$html_tablo.='
				</div></li>';				
				$i++;
			}
		}		
	}
}
?>
<script language="JavaScript" type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.7.1.custom.min.js"></script>
<link rel='stylesheet' href='<? echo URL_DIR;?>/css/dragdrop.css' type='text/css' media='all' />
<script type="text/javascript">
nb_form=<?echo $nb_form;?>;
nb_actuel=0;
last_timestamp=0;
if($.browser.msie && $.browser.version=="6.0")size_padding=10;
else size_padding=9;
var order_general='';
var form_info = new Array();
var nb_field_form=<?echo $nb_field_form;?>;
form_info['TITLE']='<?echo $form['TITLE'];?>';
form_info['DESC']='<?echo $form['DESC'];?>';
form_info['SUCCESS']='<?echo $form['SUCCESS'];?>';
form_info['COMPLET']='<?echo $form['COMPLET'];?>';
form_info['NB']='<?echo $form['NB'];?>';
form_info['ACTIVE']=<?echo $form['ACTIVE'];?>;
form_info['ACTIVE_ZONE']=0;
var tab_form = new Array();
<?echo $js_tablo;?>

function display_save(){
	//alert(nb_field_form);
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
		else if(type_element=='choice'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=3;
			element['REQUIRED']=0;
			element['DEFAULT']='';
			element['NAME']='Choix multiple';
			element['HELP']='';
			element['ELEMENTS']=new Array();
			element['ELEMENTS'][0]="1ère option";
			element['ELEMENTS'][1]="2ème option";
			element['ELEMENTS'][2]="3ème option";
			tab_form.push(element);	
			
			code_html='<div style="float:left;"><input type="radio"></div><div style="float:left;margin-left:5px;"><label id="choice_'+nb_form+'_0">1ère option</label></div><div class="clear"></div>'+
			'<div style="float:left;"><input type="radio"></div><div style="float:left;margin-left:5px;"><label id="choice_'+nb_form+'_1">2ème option</label></div><div class="clear"></div>'+
			'<div style="float:left;"><input type="radio"></div><div style="float:left;margin-left:5px;"><label id="choice_'+nb_form+'_2">3ème option</label></div><div class="clear"></div>';
		}
		else if(type_element=='checkbox'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=4;
			element['REQUIRED']=0;
			element['DEFAULT']="";
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
			
			code_html='<div style="float:left;"><input type="checkbox" id="check_'+nb_form+'_0"></div><div style="float:left;margin-left:5px;"><label id="choice_'+nb_form+'_0">1ère option</label></div><div class="clear"></div>'+
			'<div style="float:left;"><input type="checkbox" id="check_'+nb_form+'_1"></div><div style="float:left;margin-left:5px;"><label id="choice_'+nb_form+'_1">2ème option</label></div><div class="clear"></div>'+
			'<div style="float:left;"><input type="checkbox" id="check_'+nb_form+'_2"></div><div style="float:left;margin-left:5px;"><label id="choice_'+nb_form+'_2">3ème option</label></div><div class="clear"></div>';	
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
		else if(type_element=='name'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=6;
			element['REQUIRED']=0;
			element['DEFAULT']='';
			element['NAME']='Nom';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<span style="float:left;"><input type="text" readonly="readonly"><br/><label>Nom</label></span> <span style="float:left;margin-left:10px;"><input type="text" readonly="readonly"><br/><label>Prénom</label></span><div class="clear"></div>';
		}
		else if(type_element=='adresse'){
		
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=7;
			element['REQUIRED']=0;
			element['NAME']='Adresse';
			element['HELP']='';
			tab_form.push(element);
			
			
			code_html='<div><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Rue</label></div>'+
			'<div><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Rue seconde ligne</label></div>'+
			'<div style="display:inline;float:left;width:48%;"><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Code postal</label></div>'+
			'<div style="display:inline;float:right;width:48%;"><input type="text" readonly="readonly" style="width:100%;"><br/><label style="font-size:10px;">Ville</label></div><div class="clear"></div>';
		}
		else if(type_element=='tel'){
			element= new Array();
			element['ID']=nb_form;
			element['TYPE']=8;
			element['REQUIRED']=0;
			element['NAME']='Téléphone';
			element['DEFAULT']='';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><input type="text" readonly="readonly" style="width:50%;"></div>';
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
			element['DEFAULT']='';
			element['HELP']='';
			tab_form.push(element);
			
			code_html='<div><input type="file" readonly="readonly"></div>';
		}
		
		/*Affichage principal !*/
		
		last_timestamp=h_local;
		
		$("#form_elements").append('<li id="listItem_'+nb_form+'" onclick="javascript:lance_modif('+nb_form+');void(0);">'+
		'<div style="float:left;min-height:25px;"><span id="label_'+nb_form+'" class="label_field">'+element['NAME']+'</span>'+
		'<span class="required_field nosee" id="required_'+nb_form+'"> * </span></div><div class="zone_bouton" id="zone_bouton_'+nb_form+'"><img src="<?echo URL_DIR;?>/images/btn_hand.gif" alt="déplacer" class="handle" style="cursor:move;"/> <a><img src="<?echo URL_DIR;?>/images/pencil.png" alt="modifier"  /></a> <a href="javascript:suppr_element('+nb_form+');void(0);"><img src="<?echo URL_DIR;?>/images/delete.png" alt="supprimer"/></a></div><div class="clear"></div>'+
		'<div id="zone_form_'+nb_form+'">'+code_html+'</div>'+'</li>');
		
		if($.browser.msie && $.browser.version=="6.0"){
			$('#zone_bouton_'+nb_form).show(); 
			DD_belatedPNG.fix('#zone_bouton_'+nb_form);
		}
		else{
			
			$('#form_elements li:last-child').mouseover(function(){
				$(this).children('.zone_bouton').show();
			}).mouseout(function(){
				$(this).children('.zone_bouton').hide();
			});
			$('#zone_bouton_'+nb_form).hide();
		}
		
		nb_form++;
		nb_field_form++;
		display_save();
	}
}


function lance_modif_info(){
	
	$('#listItem_'+nb_actuel).css({'background-color': '#FFFFFF'});
	$('#entete').css({'background-color': '#f8f8f8'});	
	
	var sel_actif='';
	var sel_inactif='';
	
	if(form_info['ACTIVE']==1)sel_actif='selected="selected"';
	if(form_info['ACTIVE']==0)sel_inactif='selected="selected"';
	
	var form_edit='<div>Titre du formulaire :</div>'
	+'<div style="margin-top:5px;"><input type="text" style="width:100%;" value="'+Remplace(form_info['TITLE'],'<br />', "\n")+'" onkeyup="maj_entete(\'titre_entete\',this.value);maj_entete_value(\'TITLE\',this.value);"></div><div class="clear"></div>'
	+'<div style="margin-top:10px;">Description du formulaire :</div>'
	+'<div style="margin-top:5px;"><textarea style="height:5em;width:100%;"  onkeyup="maj_entete(\'desc_entete\',this.value);maj_entete_value(\'DESC\',this.value);">'+Remplace(form_info['DESC'],'<br />', "\n")+'</textarea></div><div class="clear"></div>'
	+'<div style="margin-top:10px;">Statut du formulaire</div>'
	+'<div style="margin-top:5px;"><select id="statut_form" onchange="maj_entete_value(\'ACTIVE\',$(\'#statut_form\').val());"><option value="1" '+sel_actif+'>Actif</option><option value="0" '+sel_inactif+'>Inactif</option></select></div><div class="clear"></div>'
	+'<div style="margin-top:10px;">Message affiché après la soumission du formulaire :</div>'
	+'<div style="margin-top:5px;"><textarea style="height:5em;width:90%;"  onkeyup="maj_entete_value(\'SUCCESS\',this.value);">'+Remplace(form_info['SUCCESS'],'<br />', "\n")+'</textarea></div><div class="clear"></div>'
	+'<div style="margin-top:10px;">Nombre maximum d\'enregistrements (si vide il n\'y aura pas de limite):</div>'
	+'<div style="margin-top:5px;"><input type="text" style="width:20%;" value="'+form_info['NB']+'" onkeyup="maj_entete_value(\'NB\',this.value);"></div><div class="clear"></div>'
	+'<div style="margin-top:10px;">Message affiché une fois le maximum d\'enregistrements atteint:</div>'
	+'<div style="margin-top:5px;"><textarea style="height:5em;width:90%;"  onkeyup="maj_entete_value(\'COMPLET\',this.value);">'+Remplace(form_info['COMPLET'],'<br />', "\n")+'</textarea></div>';	
	
	form_info['ACTIVE_ZONE']=1;
	
	affiche_zone_form('info');

	
	$('#zone_form_info').html(form_edit);
	
	
	
	
}
function lance_modif(id_field){
	//$('#log').html(id_field);
	//alert(id_field);	
	
	if(form_info['ACTIVE_ZONE']==1){
		form_info['ACTIVE_ZONE']=0;
		affiche_zone_form('edit');
		$('#entete').css({'background-color': '#FFFFFF'});	
	}
	$('#listItem_'+nb_actuel).css({'background-color': '#FFFFFF'});
	$('#listItem_'+id_field).css({'background-color': '#f8f8f8'});	
	$('#zone_form_add,#suppr_'+nb_actuel).hide();
	$('#zone_form_edit,#suppr_'+id_field).show();
	/*
	size_to_show=get_height_before(id_field);
	$('#zone_form_edit').css({'margin-top':size_to_show+"px"});*/
	nb_actuel=id_field;
	//alert(id_field);
	var modif_valeur=get_table_index(id_field);
	//alert(modif_valeur);
	var require_check='';

	//alert(modif_valeur);
	//alert(tab_form[modif_valeur]['TYPE']);
	var form_edit='<div>Libellé du champ : </div><div class="clear"></div>'
	+'<textarea style="height:3.7em;width:100%;margin-top:5px;" onkeyup="javascript:maj_champ(this.value,'+id_field+');maj_valeur(this.value,\'NAME\','+id_field+');">'+Remplace(tab_form[modif_valeur]['NAME'],'<br />', "\n")+'</textarea>';
	
	if(tab_form[modif_valeur]['TYPE']==1){
		
		var require_sel1='';
		if(tab_form[modif_valeur]['SIZE']==1)require_sel1="selected='selected'";
		var require_sel2='';
		if(tab_form[modif_valeur]['SIZE']==2)require_sel2="selected='selected'";
		var require_sel3='';
		if(tab_form[modif_valeur]['SIZE']==3)require_sel3="selected='selected'";		
		
		
		form_edit+='<div style="margin-top:10px;">Taille du champ :</div><div class="clear"></div>'
		+'<div style="margin-top:5px;"><select name="taille_champ" id="taille_champ" onchange="maj_valeur($(\'#taille_champ\').val(),\'SIZE\','+id_field+');maj_taille($(\'#taille_champ\').val(),'+id_field+');"><option value="1" '+require_sel1+'>Petit</option><option value="2" '+require_sel2+'>Moyen</option><option value="3"  '+require_sel3+'>Grand</option></select>'
		+'<div class="clear"></div><div style="margin-top:10px;">Valeur par défaut :</div><div class="clear"></div>'
		+'<div style="margin-top:5px;"><input type="text" style="width:100%;" value="'+Remplace(tab_form[modif_valeur]['DEFAULT'],'<br />', "\n")+'" onkeyup="javascript:maj_valeur(this.value,\'DEFAULT\','+id_field+');"></div>';	
		
	}	
	else if(tab_form[modif_valeur]['TYPE']==2){
		
		var require_sel1='';
		if(tab_form[modif_valeur]['SIZE']==1)require_sel1="selected='selected'";
		var require_sel2='';
		if(tab_form[modif_valeur]['SIZE']==2)require_sel2="selected='selected'";
		var require_sel3='';
		if(tab_form[modif_valeur]['SIZE']==3)require_sel3="selected='selected'";		
		
		form_edit+='<div style="margin-top:10px;">Taille du champ :</div><div class="clear"></div>'
		+'<div><select name="taille_champ" id="taille_champ" style="margin-top:5px;" onchange="maj_valeur($(\'#taille_champ\').val(),\'SIZE\','+id_field+');maj_taille_area($(\'#taille_champ\').val(),'+id_field+');"><option value="1" '+require_sel1+'>Petit</option><option value="2" '+require_sel2+'>Moyen</option><option value="3"  '+require_sel3+'>Grand</option></select></div>'
		+'<div class="clear"></div><div style="margin-top:10px;">Valeur par défaut :</div><div class="clear"></div>'
		+'<input type="text" style="width:100%;margin-top:5px;" value="'+Remplace(tab_form[modif_valeur]['DEFAULT'],'<br />', "\n")+'" onkeyup="javascript:maj_valeur(this.value,\'DEFAULT\','+id_field+');">';	
		
	}
	else if(tab_form[modif_valeur]['TYPE']==3 || tab_form[modif_valeur]['TYPE']==5){/*Combobox et radio*/
		
		form_edit+='<div style="margin-top:10px;">Options : </div><div class="clear"></div><div style="margin-top:5px;"><ul id="list_opt_'+id_field+'">';

		j=tab_form[modif_valeur]['ELEMENTS'].length;
		for(var i in tab_form[modif_valeur]['ELEMENTS']){		
			
			if(i<j){
				
				if(tab_form[modif_valeur]['DEFAULT']==i && is_numeric(tab_form[modif_valeur]['DEFAULT']))img_default='star';
				else img_default='case';
			
				form_edit+='<li id="option_'+id_field+'_'+i+'" style="margin-bottom:5px;"><div style="float:left;width:170px;"><input type="text" style="width:150px;" value="'+tab_form[modif_valeur]['ELEMENTS'][i]+'" onkeyup="javascript:maj_option_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"></div><div style="float:right;width:75px;"><a href="javascript:add_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/add.png" align="absmiddle"></a><a href="javascript:delete_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/delete.png" align="absmiddle"  style="margin-left:10px;"></a><a href="javascript:select_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></div><div class="clear"></div></li>';
			}
			
		}
		form_edit+='</ul></div>';
	}
	else if(tab_form[modif_valeur]['TYPE']==4){/*Cases à cocher*/
		
		form_edit+='<div style="margin-top:10px;">Options : </div><div class="clear"></div><div style="margin-top:5px;"><ul id="list_opt_'+id_field+'">';

		j=tab_form[modif_valeur]['ELEMENTS'].length;
		for(var i in tab_form[modif_valeur]['ELEMENTS']){		
			
			if(i<j){
				
				if(tab_form[modif_valeur]['ELEMENTS'][i]['DEFAULT']==1)img_default='star';
				else img_default='case';
			
				form_edit+='<li id="option_'+id_field+'_'+i+'" style="margin-bottom:5px;"><div style="float:left;width:170px;"><input type="text" style="width:150px;" value="'+tab_form[modif_valeur]['ELEMENTS'][i]['LIBELLE']+'" onkeyup="javascript:maj_option_check_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"></div><div style="float:right;width:75px;"><a href="javascript:add_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/add.png" align="absmiddle"></a><a href="javascript:delete_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/delete.png" align="absmiddle" style="margin-left:10px;"></a><a href="javascript:select_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></div><div class="clear"></div></li>';
			}
			
		}
		form_edit+='</ul></div>';
	}
	else if(tab_form[modif_valeur]['TYPE']==11 || tab_form[modif_valeur]['TYPE']==10 || tab_form[modif_valeur]['TYPE']==8){
		form_edit+='<div class="clear"></div><div style="margin-top:10px;">Valeur par défaut :</div><div class="clear"></div>'
		+'<div style="margin-top:5px;"><input type="text" style="width:80%;" value="'+Remplace(tab_form[modif_valeur]['DEFAULT'],'<br />', "\n")+'" onkeyup="javascript:maj_valeur(this.value,\'DEFAULT\','+id_field+');"></div>';	
	}
	
	if(tab_form[modif_valeur]['REQUIRED']==1)require_check="checked='checked'";
	
	form_edit+='<div class="clear"></div><div style="margin-top:10px;">Conditions :</div><div class="clear"></div>'
	+'<div style="margin-top:5px;"><input type="checkbox" value="0" '+require_check+' onclick="(this.checked) ? checkVal = \'1\' : checkVal = \'0\';maj_valeur(checkVal,\'REQUIRED\','+id_field+');maj_required(checkVal,'+id_field+');"> Requis</div>'	
	+'<div class="clear"></div><div style="margin-top:10px;">Aide à la saisie :</div><div class="clear"></div>'	
	+'<div><textarea style="height:5.7em;width:100%;margin-top:5px;" onkeyup="javascript:maj_valeur(this.value,\'HELP\','+id_field+');">'+Remplace(tab_form[modif_valeur]['HELP'],'<br />', "\n")+'</textarea></div>';
	
	$('#zone_form_edit').html(form_edit);
	
	
	if($.browser.msie && $.browser.version=="6.0" && (tab_form[modif_valeur]['TYPE']>=3 && tab_form[modif_valeur]['TYPE']<=5)){
		$('#list_opt_'+id_field).show();
		DD_belatedPNG.fix('#list_opt_'+id_field);
	}
	
	
	
}
function maj_entete(id_field,value){
	//alert(id_field+':'+id_option+':'+value);
	$('#'+id_field).html(Remplace(value,"\n", "<br />"));	
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
			
			if(tab_form[modif_valeur]['DEFAULT']==i && is_numeric(tab_form[modif_valeur]['DEFAULT']))img_default='star';
			else img_default='case';
			
			form_edit+='<li id="option_'+id_field+'_'+i+'" style="margin-bottom:5px;"><div style="float:left;width:170px;"><input type="text" style="width:150px;" value="'+tab_form[modif_valeur]['ELEMENTS'][i]+'" onkeyup="javascript:maj_option_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"></div><div style="float:right;width:75px;"><a href="javascript:add_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/add.png" align="absmiddle"></a><a href="javascript:delete_option('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/delete.png" align="absmiddle" style="margin-left:10px;"></a><a href="javascript:select_option('+id_field+','+i+');void(0);" ><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></div><div class="clear"></div></li>';
			
			if(tab_form[modif_valeur]['TYPE']==5){
				left_display+='<option id="choice_'+id_field+'_'+i+'">'+tab_form[modif_valeur]['ELEMENTS'][i]+'</option>';
			}else if(tab_form[modif_valeur]['TYPE']==3){
				left_display+='<div style="float:left;"><input type="radio"></div><div style="float:left;margin-left:5px;"><div id="choice_'+id_field+'_'+i+'">'+tab_form[modif_valeur]['ELEMENTS'][i]+'</div></div><div class="clear"></div>';		
			}
		}			
	}
	if(tab_form[modif_valeur]['TYPE']==5)left_display+='</select>';
	//alert(form_edit);	
		
	$('#zone_form_'+id_field).html(left_display);
	$('#list_opt_'+id_field).html(form_edit);
	
	
	if($.browser.msie && $.browser.version=="6.0"){
		$('#list_opt_'+id_field).show();
		DD_belatedPNG.fix('#list_opt_'+id_field);
	}
	
	
}
function select_option(id_field,id_indice){
	var modif_valeur=get_table_index(id_field);	
	//alert(tab_form[modif_valeur]["DEFAULT"]+' '+id_indice);
	if(tab_form[modif_valeur]["DEFAULT"]!=null){
		//alert('ancienne valeur '+tab_form[modif_valeur]["DEFAULT"]);
		if(tab_form[modif_valeur]["DEFAULT"]==id_indice){
			//alert('lali');
			tab_form[modif_valeur]["DEFAULT"]='';
			$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/case.png'});

		}else{
			//alert('lala');
			$('#img_select_'+id_field+'_'+tab_form[modif_valeur]["DEFAULT"]).attr({'src':'<?echo URL_DIR;?>/images/case.png'});
			tab_form[modif_valeur]["DEFAULT"]=id_indice;
			$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/star.png'});
		}
	}
	else{
		tab_form[modif_valeur]["DEFAULT"]=id_indice;
		
		$('#img_select_'+id_field+'_'+id_indice).attr({'src':'<?echo URL_DIR;?>/images/star.png'});
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
	form_don+='<input type="hidden" name="form_name" value="'+Remplace(form_info['TITLE'],'<br />', "\n")+'">';
	form_don+='<input type="hidden" name="form_desc" value="'+Remplace(form_info['DESC'],'<br />', "\n")+'">';
	form_don+='<input type="hidden" name="form_success" value="'+Remplace(form_info['SUCCESS'],'<br />', "\n")+'">';
	form_don+='<input type="hidden" name="form_complet" value="'+Remplace(form_info['COMPLET'],'<br />', "\n")+'">';
	form_don+='<input type="hidden" name="form_active" value="'+form_info['ACTIVE']+'">';
	form_don+='<input type="hidden" name="form_nb" value="'+form_info['NB']+'">';
	form_don+='<input type="hidden" name="id" value="<?echo $_GET['id'];?>">';

	j=tab_form.length;
	//alert(j;
	for(var i in tab_form){
		if(i<j){
			form_don+='<input type="hidden" name="name_element[]" value="'+Remplace(tab_form[i]['NAME'],'<br />', "\n")+'">';
			form_don+='<input type="hidden" name="type_element_'+i+'" value="'+tab_form[i]['TYPE']+'">';
			form_don+='<input type="hidden" name="actual_id_'+i+'" value="'+tab_form[i]['ACTUAL_ID']+'">';
			form_don+='<input type="hidden" name="id_field_'+i+'" value="'+tab_form[i]['ID']+'">';
			form_don+='<input type="hidden" name="default_'+i+'" value="'+Remplace(tab_form[i]['DEFAULT'],'<br />', "\n")+'">';
			form_don+='<input type="hidden" name="help_'+i+'" value="'+Remplace(tab_form[i]['HELP'],'<br />', "\n")+'">';
			form_don+='<input type="hidden" name="required_'+i+'" value="'+tab_form[i]['REQUIRED']+'">';
			
			if(tab_form[i]['TYPE']==1 || tab_form[i]['TYPE']==2){
				form_don+='<input type="hidden" name="size_'+i+'" value="'+tab_form[i]['SIZE']+'">';
			}
			
			if(tab_form[i]['TYPE']==3 || tab_form[i]['TYPE']==5){
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
	
	$('#zone_form_add').hide();
	
	if(!$.browser.msie || $.browser.version!="6.0"){
		$('.zone_bouton').hide();
		$('#form_elements li').mouseover(function(){
			$(this).children('.zone_bouton').show();
		}).mouseout(function(){
			$(this).children('.zone_bouton').hide();
		});
	}
	
	
	$('#form_elements_entete li').mouseover(function(){
		$("#settings_zone").show();
	}).mouseout(function(){
		$("#settings_zone").hide();
	});
	
	$(window).scroll(function() {
		height=$(this).scrollTop();
		if(height>200){
			height=height-200;
			$('#form_right').css('margin-top', height + "px");
		}else{
			$('#form_right').css('margin-top',"0px");
		}
	});

	
    $("#form_elements").sortable({
      handle : '.handle',
      update : function () {
		 order_general = $('#form_elements').sortable('serialize');
		 //alert(order);
      }
    });
});

function affiche_zone_form(form_zone){
	
	if(form_zone=='info'){			
		$(window).scroll(function() {
			$('#form_right').css('margin-top',"0px");		
		});
	}else{	
		$(window).scroll(function() {
			height=$(this).scrollTop();
			if(height>200){
				height=height-200;
				$('#form_right').css('margin-top', height + "px");
			}else{
				$('#form_right').css('margin-top',"0px");
			}
		});
	}
	
	if(form_zone=='add'){
		$('#zone_form_edit').hide();
		$('#zone_form_edit').html('');
		$('#zone_form_info').hide();
		
	}
	else if(form_zone=='edit'){
		$('#zone_form_add').hide();
		$('#zone_form_info').hide();
	}
	else if(form_zone=='info'){
		$('#zone_form_add').hide();
		$('#zone_form_edit').hide();
		$('#zone_form_edit').html('');
		
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
			
			if(tab_form[modif_valeur]['ELEMENTS'][i]['DEFAULT']==1)img_default='star';
			else img_default='case';			
			
			form_edit+='<li id="option_'+id_field+'_'+i+'" style="margin-bottom:5px;"><div style="float:left;width:170px;"><input type="text" style="width:150px;" value="'+tab_form[modif_valeur]['ELEMENTS'][i]['LIBELLE']+'" onkeyup="javascript:maj_option_check_value('+id_field+','+i+',this.value);maj_label('+id_field+','+i+',this.value);"></div><div style="float:right;width:75px;"><a href="javascript:add_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/add.png" align="absmiddle"></a><a href="javascript:delete_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/delete.png" align="absmiddle" style="margin-left:10px;"></a><a href="javascript:select_option_check('+id_field+','+i+');void(0);"><img src="<?echo URL_DIR;?>/images/'+img_default+'.png" align="absmiddle" id="img_select_'+id_field+'_'+i+'" style="margin-left:10px;"></a></div><div class="clear"></div></li>';
			
			left_display+='<div style="float:left;"><input type="checkbox" id="check_'+id_field+'_'+i+'"></div><div style="float:left;margin-left:5px;"><label id="choice_'+id_field+'_'+i+'">'+tab_form[modif_valeur]['ELEMENTS'][i]['LIBELLE']+'</label></div><div class="clear"></div>';		
			
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



<div id="form_admin" <?echo add_help('Formulaires',"Pour connaître les fonctionnalités <a href='#' id='titre_guide'>voir le Guide &quot;Formulaire&quot;</a>",'titre_guide','faq_view.php?id_cat=10');?>>
	<div style='float:left;width:605px;'><h1 id="etape_name"><?if($action=='add'){?>Création<?}else {?>Modification<?}?> d'un formulaire</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/btn_article_add.jpg'></div>
</div>
<div class="clear"></div>
<div style="float:left;width:380px;">
<div id="log">
	<form id="form_final" action="?action=<?echo $action;?>_r" name="form_final" method="post"></form>
</div>
<ul id="form_elements_entete">
	<li id="entete" onclick="javascript:lance_modif_info();void(0);">
		<div id="titre_entete" style='float:left;width:330px;height:25px;'><? echo $form['TITLE'];?></div>
		<div id="settings_zone"><img src='<?echo URL_DIR;?>/images/settings.gif'></div>
		<div class="clear"></div>
		<p id="desc_entete"><? echo $form['DESC'];?></p>
	</li>
</ul>
<ul id="form_elements">
	<?echo $html_tablo;?>	
</ul>
</div>	
<div style="width:250px;float:left;margin-left:25px;" id="form_right">	
	<div class="bordure_menu" style='width:250px;float:left;' onclick="javascript:affiche_zone_form('add');" <?echo add_help('Ajouter un champ',"<a href='#' id='titre_guide2'>Voir le Guide &quot;Comment créer/modifier les champs d'un formulaire ?&quot;</a>",'titre_guide2','faq_view.php?id_aide=49&id_cat=10');?>>
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style"><p>Ajouter un champ</p></div>
	</div>
	<div class='clear'></div>		
	<div id="zone_form_add" class="menu_form">		
		<div>
			<ul style='float:left;list-style:none outside none;width:135px;padding:0px;margin:0px;'>
				<li><a href="javascript:insert_element('texte');void(0);"><img src='<?echo URL_DIR;?>/images/star.png' align='absmiddle'> Ligne de texte</a></li>
				<li><a href="javascript:insert_element('paragraph');void(0);"><img src='<?echo URL_DIR;?>/images/star.png' align='absmiddle'> Paragraphe</a></li>
				<li><a href="javascript:insert_element('choice');void(0);"><img src='<?echo URL_DIR;?>/images/star.png' align='absmiddle'> Choix multiple</a></a></li>
				<li><a href="javascript:insert_element('checkbox');void(0);"><img src='<?echo URL_DIR;?>/images/star.png' align='absmiddle'> Cases à cocher</a></a></li>
				<li><a href="javascript:insert_element('select');void(0);"><img src='<?echo URL_DIR;?>/images/star.png' align='absmiddle'> Menu déroulant</a></a></li>
				<li><a href="javascript:insert_element('date');void(0);"><img src='<?echo URL_DIR;?>/images/calendar.png' align='absmiddle'> Date</a></a></li>
				<li><a href="javascript:insert_element('upload');void(0);"><img src='<?echo URL_DIR;?>/images/attached.png' align='absmiddle'> Joindre un fichier</a></a></li>
			</ul>
			<ul style='float:right;list-style:none outside none;width:100px;padding:0px;margin-right:5px;'>
				<li><a href="javascript:insert_element('name');void(0);"><img src='<?echo URL_DIR;?>/images/name.png' align='absmiddle'> Nom</a></a></li>
				<li><a href="javascript:insert_element('adresse');void(0);"><img src='<?echo URL_DIR;?>/images/address.png' align='absmiddle'> Adresse</a></a></li>
				<li><a href="javascript:insert_element('email');void(0);"><img src='<?echo URL_DIR;?>/images/email.png' align='absmiddle'> Email</a></a></li>
				<li><a href="javascript:insert_element('tel');void(0);"><img src='<?echo URL_DIR;?>/images/phone.png' align='absmiddle'> Téléphone</a></li>
				<li><a href="javascript:insert_element('website');void(0);"><img src='<?echo URL_DIR;?>/images/world.png' align='absmiddle'> Site internet</a></a></li>
			</ul>
		</div>
		<div class='clear'></div>	
	</div>
	<div class='clear'></div>
	<div class="bordure_menu" style='width:250px;float:left;margin-top:10px;' onclick="javascript:affiche_zone_form('edit');"  <?echo add_help('Modifier un champ',"<a href='#' id='titre_guide3'>Voir le Guide &quot;Comment créer/modifier les champs d'un formulaire ?&quot;</a>",'titre_guide3','faq_view.php?id_aide=49&id_cat=10');?>>
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_pencil.png");' class="btn_style"><p>Modifier un champ</p></div>
	</div>
	<div class='clear'></div>
	<div id="zone_form_edit"  style="margin-left:5px;display:none;" class="menu_form">
	</div>
	<div class='clear'></div>
	<div class="bordure_menu" style='width:250px;float:left;margin-top:10px;' onclick="javascript:lance_modif_info();"  <?echo add_help('Propriétés du formulaire',"<a href='#' id='titre_guide4'>Voir le Guide &quot;Propriétés d'un formulaire&quot;</a>",'titre_guide4','faq_view.php?id_aide=50&id_cat=10');?>>
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_settings.png");' class="btn_style"><p>Propriétés du formulaire</p></div>
	</div>
	<div class="clear"></div>
	<div id="zone_form_info"  style="margin-left:5px;" class="menu_form">
	</div>
	<div class="clear"></div>
	<div style="background-color:#cccccc;width:100%;height:1px;margin-top:20px;"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>	
	<div class="bordure_menu" style='width:250px;float:left;margin-top:10px;<?if($action=='add'){?>display:none;<?}?>' onclick="javascript:return_all_value();" id="img_save">
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_valid.png");' class="btn_style" ><p>Sauvegarder</p></div>
	</div>	
	<div class="bordure_menu" style='width:250px;float:left;margin-top:10px;' onclick="javascript:redir('form.php');">
		<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_delete.png");' class="btn_style"><p>Abandonner</p></div>
	</div>	
	
	<div class='clear'></div>
</div>

<?	
}
elseif($action=='edit_email' && isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['id_email']) && is_numeric($_GET['id_email'])){

$req_email=$db->query('select * from cms_formulaire_email where ID = '.$_GET['id_email']);
if(mysql_num_rows($req_email)!=0){
	$form=mysql_fetch_array($req_email);
}
else $form=array();
?>

<script type="text/javascript">
$(document).ready(function() {
	$("#commentForm").validate({meta: "validate"});
});
</script>

<div id="form">
	<h1>Modifier l'email</h1>
</div>	
<form method="post" id="commentForm" action="?action=<?echo $action;?>_r" enctype="multipart/form-data" class="cmxform">
	<input type='hidden' name='id' value='<?echo $form['ID_FORM'];?>'>
	<input type='hidden' name='id_email' value='<?echo $form['ID'];?>'>
	<div class="input text required">
		<label for="email">Email :</label>
		<input name="email" type="text" value='<?echo $form['EMAIL'];?>' class="{validate:{required:true, email:true, messages:{required:'Veuillez saisir un email', email:'Veuillez saisir un email valide'}}}" style="width:350px;"/>
	</div>
	<div class="submit"><input type="submit" id="btnSubmit" value="Modifier l'email" /></div>
</form>
<?
}
elseif($action=="data_view" &&  isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['id_record']) && is_numeric($_GET['id_record'])){
	
	$req=$db->query('select * from cms_formulaire where ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
	
		$form_info=mysql_fetch_array($req);	
	
		$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' ORDER BY `ORDRE`');
		$list_elements=array();
		
		if(mysql_num_rows($req_elements)>0){
			
			while($don=mysql_fetch_array($req_elements)){
				$list_elements[]=$don;			
			}
			
			echo "
			<div style='float:right;'><a href='?action=supp_record&id_record=".$don['ID']."&id=".$_GET['id']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png' align='absmiddle'> Supprimer cet enregistrement</a></div>
			<div class='clear'></div>
			<div style='height:15px;width:100%;'>&nbsp;</div>
			<table cellpadding='0' cellspacing='0' border='0' width='100%' id='table_view'>
			";
		
			$req=$db->query('SELECT * FROM cms_formulaire_data_'.$_GET['id'].' WHERE ID = '.$_GET['id_record'].' ORDER BY ID DESC');
			while($don=mysql_fetch_array($req)){
				$don=array_map('stripslashes',$don);
				
				for($i=0;$i<count($list_elements);$i++){
					
					echo '
				<tr>	
					<td style="width:40%;text-align:left;" class="column1"><b>'.stripslashes($list_elements[$i]['NAME']).'</b></td>
					<td style="width:60%;text-align:left;">';
					switch($list_elements[$i]['TYPE']){
					
						case 1:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 2:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 3:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 4:
						$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);
						$recp='';
						for($j=0;$j<count($temp);$j++){
							$recp.=$temp[$j].'<br/>';
						}
						echo $recp;
						break;
						
						case 5:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 6:
						$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);		
						echo $temp[0].' '.$temp[1];
						break;
						
						case 7:
						$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);					
						echo $temp[0].'<br/>'.$temp[1].'<br/>'.$temp[2].' '.$temp[3];					
						break;

						case 8:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 9:
						if(is_numeric($don['FIELD_'.$list_elements[$i]['ID']])){
							echo date('d/m/Y',$don['FIELD_'.$list_elements[$i]['ID']]);
						}
						break;
						
						case 10:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 11:
						echo $don['FIELD_'.$list_elements[$i]['ID']];
						break;
						
						case 12:
						if($don['FIELD_'.$list_elements[$i]['ID']]!=""){
							echo '<a href="'.URL_DIR."/upload/".$don['FIELD_'.$list_elements[$i]['ID']].'">Télécharger le fichier</a>';
						}
						break;

					}
					echo '
					</td>
				</tr>';
				}
			}
			echo '
			</table>
		';
		}else{
			echo "Aucun enregistrement";
		}
	}
	else{
		header('location:form.php');
		die();
	}
}
elseif($action=="data" &&  isset($_GET['id']) && is_numeric($_GET['id'])){
	
	$req=$db->query('select * from cms_formulaire where ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
	
		$form_info=mysql_fetch_array($req);	
		
		$nb_record=$db->countOfAll('cms_formulaire_data_'.$_GET['id']);
		$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' ORDER BY `ORDRE`');
		$list_elements=array();
		
		if($nb_record>0){
		
		
			echo "<div style='margin-top:10px;float:left;width:420px;'><b>Enregistrements du formulaire '".stripslashes($form_info['NAME'])."'</b></div>
			<div style='float:right;width:200px;'>			
				<div class=\"bordure_menu\" style='width:200px;float:left;margin-top:10px;' onclick=\"javascript:redir('export_excel.php?action=export_xls&id=".$_GET['id']."');\">
					<div style='width:200px;background-image:url(\"".URL_DIR."/images/btn_display.png\");' class=\"btn_style\" ><p>Export sur Excel</p></div>
				</div>
			</div>
			<div class='clear'></div>
			<div style='height:15px;width:100%;'>&nbsp;</div>
			<div style='overflow-x:auto;width:660px;'>
			<table cellpadding='0' cellspacing='0' border='0' id='table_view'>
				<thead>
				<tr>
					<th style='white-space:nowrap;'>&nbsp;Voir en détail&nbsp;</th>
					<th>&nbsp;Supprimer&nbsp;</th>
				";
			while($don=mysql_fetch_array($req_elements)){
				$list_elements[]=$don;	
				echo '<th style="width:90px;overflow:hidden;white-space:nowrap;height:1.1em;" >&nbsp;'.strCut(stripslashes($don['NAME']),15).'&nbsp;</th>';
			}
			echo '</tr></thead>';
			$req=$db->query('SELECT * FROM cms_formulaire_data_'.$_GET['id'].' ORDER BY ID DESC');
			while($don=mysql_fetch_array($req)){
				$don=array_map('stripslashes',$don);
				echo "
				<tr>
					<td style='text-align:center;'><a href='?action=data_view&id_record=".$don['ID']."&id=".$_GET['id']."'><img src='".URL_DIR."/images/magnify.png'></a></td>
					<td style='text-align:center;'><a href='?action=supp_record&id_record=".$don['ID']."&id=".$_GET['id']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/delete.png'></a></td>
				";
				for($i=0;$i<count($list_elements);$i++){
					
					echo '<td style="white-space:nowrap;">&nbsp;';
					switch($list_elements[$i]['TYPE']){
					
						case 1:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 2:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 3:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 4:
						$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);
						$recp='';
						for($j=0;$j<count($temp);$j++){
							$recp.=$temp[$j].'<br/>';
						}
						echo strCut($recp,15);
						break;
						
						case 5:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 6:
						$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);		
						echo strCut($temp[0].' '.$temp[1],15);
						break;
						
						case 7:
						$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);					
						echo strCut($temp[0].'<br/>'.$temp[1].'<br/>'.$temp[2].' '.$temp[3],15);					
						break;

						case 8:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 9:
						if(is_numeric($don['FIELD_'.$list_elements[$i]['ID']])){
							echo date('d/m/Y',$don['FIELD_'.$list_elements[$i]['ID']]);
						}
						break;
						
						case 10:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 11:
						echo strCut($don['FIELD_'.$list_elements[$i]['ID']],15);
						break;
						
						case 12:
						if($don['FIELD_'.$list_elements[$i]['ID']]!=""){
							echo '<a href="'.URL_DIR."/upload/".$don['FIELD_'.$list_elements[$i]['ID']].'">Télécharger le fichier</a>';
						}
						break;

					}
					echo '&nbsp;</td>';
				}
				echo '</tr>';
			}
			echo '
			</table>
		</div>';
		}else{
			echo "Aucun enregistrement";
		}
	}
	else{
		header('location:form.php');
		die();
	}
}
elseif($action=='email' && isset($_GET['id']) && is_numeric($_GET['id'])){
?>
<script type="text/javascript">
$(document).ready(function() {
	$("#commentForm").validate({meta: "validate"});
});
</script>
<?
	$req=$db->query('select * from cms_formulaire_email where ID_FORM = '.$_GET['id']);
	
	echo"
	<form method='post' id='commentForm' name='commentForm' action='?action=add_email&id=".$_GET['id']."'>
		<div style='margin-top:10px;'><b>Email :</b></div>
		<div style='margin-top:5px;'><input type='text' style='width:50%;' name='email'  class=\"{validate:{required:true, email:true, messages:{required:'', email:''}}}\"></div>
		<div class=\"bordure_menu\" style='width:155px;float:left;margin-top:10px;' onclick=\"javascript:document.forms['commentForm'].submit();\">
			<div style='width:155px;background-image:url(\"".URL_DIR."/images/btn_add.png\");' class=\"btn_style\" ><p>Ajouter l'email</p></div>
		</div>
	</form>
	<div class='clear'></div>
	<div style='height:15px;width:100%;'>&nbsp;</div>
	";
	if(mysql_num_rows($req)!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%'  id='table_view'>
			<thead>
			<tr>
				<th class='frst'>Emails</th>
				<th width='80'>Modifier</th>
				<th  width='80'>Supprimer</th>
			</tr>
			</thead>
		";
		$i=0;
		while($don=mysql_fetch_array($req)){
			if($i%2==1)$style_table="class='odd'";
			else $style_table='';
			$i++;
			
			echo "
			<tr ".$style_table.">
				<td  class='frst'>".$don['EMAIL']."</td>
				<td><a href='?action=edit_email&id_email=".$don['ID']."&id=".$_GET['id']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=suppr_email&id_email=".$don['ID']."&id=".$_GET['id']."'><img src='".URL_DIR."/images/btn_delete.png'></a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "Aucun email relié à ce formulaire";
	}
}
else{

	$req=$db->query("select * from cms_formulaire WHERE ID_LANG = ".$_SESSION['langue']." AND ID_UNIVERS = ".$_SESSION['univers']." order by ID ASC");

	if(mysql_num_rows($req)!=0){
?>
<script type="text/javascript">
$(document).ready(function() {
	$(".aide-legende").corner("5px").parent().css('padding', '1px').corner("5px");
	<? echo auto_help("Formulaires","<a href='#' id='titre_guide'>Voir le guide &quot;Formulaires&quot;</a>",'titre_guide','faq_view.php?id_cat=10');?>
});
</script>
<div class="bordure_menu" style='width:657px;margin-top:10px;'>
	<div class="aide-legende">
		Une fois le formulaire créé vous pouvez consulter les <b>enregistrements du formulaire <img src='<? echo URL_DIR; ?>/images/database.png' align='absmiddle' style='margin-top:0px;'></b> ainsi que configurer les différents <b>emails reliés au formulaire <img src='<? echo URL_DIR; ?>/images/emails.png' align='absmiddle' style="margin-top:0px;"></b> qui recevront les enregistrements.
	</div>
</div>
<div class='clear'></div>
<div style='height:15px;width:100%;'>&nbsp;</div>
<b>Liste de vos Formulaires</b>
<div style='height:15px;width:100%;'>&nbsp;</div>
<table cellpadding='0' cellspacing='0' border='0' width='660'  id='table_view' <?echo add_help('Liste de vos Formulaires','Si vous souhaitez visualisez/modifier/supprimer un formulaire, cliquez sur le bouton d’action souhaité associé au titre.');?>>
	<thead>
	<tr>
		<th class='frst'>Intitulé</th>
		<th width='80'>Voir</th>
		<th width='80'>Emails</th>
		<th width='80'>Modifier</th>
		<th width='80'>Dupliquer</th>
		<th width='80'>Supprimer</th>
	</tr>
	</thead>
<?		
		

		while($don=mysql_fetch_array($req)){
			
			$don=array_map('stripslashes',$don);
			$nb_enregistrement=$db->countOfAll('cms_formulaire_data_'.$don['ID']);
			
			echo"
			<tr>
				<td class='frst'>".$don['NAME']."</td>
				<td><a href='form-view.php?id=".$don['ID']."' target='_blank'><img src='".URL_DIR."/images/btn_see.png'></a></td>
				<td><a href='?action=email&id=".$don['ID']."'><img src='".URL_DIR."/images/emails.png'></a></td>
				<td><a href='?action=edit&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_edit.png'></a></td>
				<td><a href='?action=duplicate&id=".$don['ID']."'><img src='".URL_DIR."/images/btn_duplicate.png'></a></td>
				<td><a href='?action=delete&id=".$don['ID']."' onclick=\"return confirm('Etes-vous sûr ?');\"><img src='".URL_DIR."/images/btn_drop.png'></a></td>
			</tr>
			<tr class='odd'>
				<td colspan='6'  style='text-align:left;'><a href='?action=data&id=".$don['ID']."'><img src='".URL_DIR."/images/database.png' align='absmiddle' style='margin-top:0px;'> ".gere_cas($nb_enregistrement,array('Aucun enregistrement','Un enregistrement',' enregistrements'))."</a></td>
			</tr>
			";
		}
		
		echo "</table>";
	}else{
		echo "<div style='height:15px;width:100%;'>&nbsp;</div>Aucun formulaire présent";
	}
}
?>

<?
include('footer.php');
?>