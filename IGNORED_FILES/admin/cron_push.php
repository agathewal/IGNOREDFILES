<?
include('../config/configuration.php');

$db=New DB("push",DATABASE_SERVER,DATABASE_LOGIN,DATABASE_PASSWORD);

DEFINE('PUSH_URL','http://push.demo-cms.fr/push/');
DEFINE('PUSH_DIR','/push/');

function prepare_data_sql($content){
	return addslashes(str_replace("/push/",'http://localhost/push/',stripslashes($content)));
}

function mysql_structure($table) {
	global $db;
  
	$res = $db->query("SHOW CREATE TABLE $table");
    if ($res){
     
		$tableau = mysql_fetch_array($res);
		$tableau[1] .= ";";
		// $dumpsql[] = str_replace("\n", "", $tableau[1]);
		$dumpsql[] = $tableau[1];
		$req_table = $db->query("SELECT * FROM $table");
		$nbr_champs = mysql_num_fields($req_table);
		while ($ligne = mysql_fetch_array($req_table)){
			$insertions = "INSERT INTO $table VALUES(";
			for ($i=0; $i<=$nbr_champs-1; $i++){
				$insertions .= "'" . prepare_data_sql($ligne[$i]) . "', ";
			}
			$insertions = substr($insertions, 0, -2);
			//$insertions .= ");\n";
			$insertions .= ")";
			$dumpsql[] = $insertions;
        }     
    }    
	return $dumpsql;
}

function mysql_data_extract($table,$cond) {
	global $db;
      
	$dumpsql=array();
    $req_table = $db->query("SELECT * FROM $table$cond");
    $nbr_champs = mysql_num_fields($req_table);

    while ($ligne = mysql_fetch_array($req_table))
    {
        $insertions = "INSERT INTO $table VALUES(";
        for ($i=0; $i<=$nbr_champs-1; $i++)
          {
		  if($i==0){
			$insertions .= "'', ";
		  }
		  else if($i==7){
			$insertions .= "'ID_PARENT_HOME', ";
		  }
         else  $insertions .= "'" . addslashes(stripslashes($ligne[$i])) . "', ";
          }
        $insertions = substr($insertions, 0, -2);
        //$insertions .= ");\n";
        $insertions .= ")";
		$dumpsql[] = $insertions;
    }
   
      
    
  return $dumpsql;
  } 
 

function get_website_list($id_pack){
	global $db;
	$list_site_retenu=array();
	/*GROUPE*/
	$req=$db->query('select DISTINCT(push_groupe_site.ID_SITE) from push_pack_groupe,push_groupe_site WHERE push_pack_groupe.ID_GROUPE = push_groupe_site.ID_GROUPE AND push_pack_groupe.ID_PACK = '.$id_pack);
	if(mysql_num_rows($req)!=0){
		while($don=mysql_fetch_array($req)){
			$list_site_retenu[]=$don['ID_SITE'];
		}
	}
	/*UNITAIRE*/
	$req=$db->query('select ID_SITE FROM push_pack_site WHERE ID_PACK = '.$id_pack);
	if(mysql_num_rows($req)!=0){
		while($don=mysql_fetch_array($req)){
			if(!in_array($don['ID_SITE'],$list_site_retenu))$list_site_retenu[]=$don['ID_SITE'];
		}
	}
	$list_site=array();
	//pr($list_site_retenu);
	if(count($list_site_retenu)!=0){
		
		foreach($list_site_retenu as $value){
			$req_site=$db->query('select * from push_site where ID = '.$value);
			if(mysql_num_rows($req_site)!=0){
				$info_site=mysql_fetch_array($req_site);
				$list_site[]=$info_site;
			}
		}

	}
	return $list_site;
}

$msg_push='';
if(isset($_GET['id_pack']) && is_numeric($_GET['id_pack'])){
	$id_pack=$_GET['id_pack'];
	$req=$db->query('select * from push_pack where ID ='.$id_pack);
	
	if(mysql_num_rows($req)!=0){
		$info_pack=mysql_fetch_array($req);		
		
		/*déploiement du pack*/
		$list_site=get_website_list($id_pack);
				
		if(count($list_site)!=0){
			
			$list_arbo=array();
			$list_rub=array();
			$list_form=array();
			$list_galerie=array();
			$list_photos=array();
			$list_video_cat=array();
			$list_video=array();
			$list_homepage=array();
			$list_feature=array();
			$nb_chaine_video=0;
			$nb_formulaire=0;
			$nb_homepage=0;
			
			
			$req_element_pack=$db->query('select * from push_pack_rel where ID_PACK = '.$id_pack.' ORDER BY TYPE ');
			//echo 'select * from push_pack_rel where ID_PACK = '.$id_pack.' ORDER BY TYPE ';
			if(mysql_num_rows($req_element_pack)!=0){
				
				/*si on a bien des éléments à pusher*/					
				while($don=mysql_fetch_array($req_element_pack)){
					$don=array_map('prepare_data_sql',$don);
					switch($don['TYPE']){
						case 1 ://arborescence						
						$req_arbo=$db->query("SELECT * FROM `cms_nst` where ID_UNIVERS = ".$don['ID_ELEMENT']);								
						$req_rub=$db->query("SELECT * FROM `push_rubrique` where ID = ".$don['ID_ELEMENT']);								
						if(mysql_num_rows($req_rub)!=0){
							$info_rub=mysql_fetch_array($req_rub);
							$info_rub=array_map('prepare_data_sql',$info_rub);
							$list_arbo[]=$info_rub;
							if(mysql_num_rows($req)!=0){
								while($don_arbo=mysql_fetch_array($req_arbo)){
									$don_arbo=array_map('prepare_data_sql',$don_arbo);
									$list_rub[]=$don_arbo;
								}
							}
						}
						break;
						
						case 2 ://mise en avant
						$req_feature=$db->query('select * from cms_feature where ID = '.$don['ID_ELEMENT']);
						if(mysql_num_rows($req_feature)!=0){
							$info_feature=mysql_fetch_array($req_feature);
							$info_feature=array_map('prepare_data_sql',$info_feature);
							$list_feature[]=$info_feature;
						}
						break;	
						
						case 3 ://galerie
						$req_gal=$db->query('select * from cms_galerie where ID = '.$don['ID_ELEMENT']);
						if(mysql_num_rows($req_gal)!=0){
							$info_gal=mysql_fetch_array($req_gal);
							$info_gal=array_map('prepare_data_sql',$info_gal);
							$list_galerie[]=$info_gal;
							$req_photos=$db->query('select * from cms_photo where ID_GALERIE = '.$don['ID_ELEMENT'].' ORDER BY `ORDRE`');
							if(mysql_num_rows($req_photos)!=0){
								$list_photos[$don['ID_ELEMENT']]=array();
								while($photo=mysql_fetch_array($req_photos)){
									$photo=array_map('prepare_data_sql',$photo);
									$list_photos[$don['ID_ELEMENT']][]=$photo;
								}
							}
						}
						break;
						
						case 4 ://video
						$req_video=$db->query('select * from cms_video where ID = '.$don['ID_ELEMENT']);
						if(mysql_num_rows($req_video)!=0){
							$info_video=mysql_fetch_array($req_video);
							$info_video=array_map('prepare_data_sql',$info_video);
							$list_video[]=$info_video;
						}
						break;
						
						case 5 ://categorie video
						$req_video_cat=$db->query('select * from cms_video_categorie where ID = '.$don['ID_ELEMENT']);
						if(mysql_num_rows($req_video_cat)!=0){
						
							$video_cat=mysql_fetch_array($req_video_cat);
							$video_cat=array_map('prepare_data_sql',$video_cat);
							$list_video_cat[]=$video_cat;
							
							$req_link_video=$db->query('select * from cms_video_categorie_link where ID_CATEGORIE = '.$don['ID_ELEMENT'].' ORDER BY `ORDRE`');
							if(mysql_num_rows($req_link_video)!=0){ 
								while($list_video_link=mysql_fetch_array($req_link_video)){
									$list_video_link=array_map('prepare_data_sql',$list_video_link);
									$list_video_cat[$nb_chaine_video]['VIDEOS'][]=$list_video_link["ID_VIDEO"];
									if(!MultiInArray($list_video,$list_video_link['ID_VIDEO'],"ID")){	
										$req_video=$db->query('select * from cms_video where ID = '.$list_video_link["ID_VIDEO"]);
										if(mysql_num_rows($req_video)!=0){
											$info_video=mysql_fetch_array($req_video);
											$info_video=array_map('prepare_data_sql',$info_video);
											$list_video[]=$info_video;
										}
									}
								}
							}
							$nb_chaine_video++;
						}
						
						break;
						
						case 6 ://formulaire
						$req_form=$db->query('select * from cms_formulaire where ID = '.$don['ID_ELEMENT']);
						if(mysql_num_rows($req_form)!=0){								
								
							$info_form=mysql_fetch_array($req_form);
							$info_form=array_map('prepare_data_sql',$info_form);
							
							$info_form['STRUCTURE']=mysql_structure("cms_formulaire_data_".$don['ID_ELEMENT']);
							/*
							$res = $db->query("SHOW CREATE TABLE cms_formulaire_data_".$don['ID_ELEMENT']); 
							$structure=mysql_fetch_array($res); 
							
							$info_form['STRUCTURE']=$structure[1];
							*/
							 
							$list_form[]=$info_form;
							$req_form_element=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$don['ID_ELEMENT'].' ORDER BY `ORDRE`');
							if(mysql_num_rows($req_form_element)!=0){	
								$form_options=0;
								while($don_form_element=mysql_fetch_array($req_form_element)){
									$don_form_element=array_map('prepare_data_sql',$don_form_element);
									$list_form[$nb_formulaire]['ELEMENT'][]=$don_form_element;
									$req_form_options=$db->query('select * from cms_formulaire_champ_option WHERE ID_ELEMENT = '.$don_form_element['ID'].' ORDER BY `ORDRE`');
									if(mysql_num_rows($req_form_options)!=0){
										while($don_options=mysql_fetch_array($req_form_options)){
											$don_options=array_map('prepare_data_sql',$don_options);
											$list_form[$nb_formulaire]['ELEMENT'][$form_options]['OPTIONS'][]=$don_options;										
										}
									}
									$form_options++;
								}
							}
							
							$list_form[$nb_formulaire]['EMAIL']=array();
							
							$req_form_email=$db->query('select * from cms_formulaire_email where ID_FORM = '.$don['ID_ELEMENT']);
							if(mysql_num_rows($req_form_email)!=0){
								while($don_email=mysql_fetch_array($req_form_email)){
									$don_email=array_map('prepare_data_sql',$don_email);
									$list_form[$nb_formulaire]['EMAIL'][]=$don_email;
								}
							}
							
							$list_form[$nb_formulaire]['DATA']=array();
							
							$req_data_form=$db->query('select * from cms_formulaire_data_'.$don['ID_ELEMENT']);
							if(mysql_num_rows($req_data_form)!=0){
								while($data_home=mysql_fetch_array($req_data_form)){
									$data_home=array_map('prepare_data_sql',$data_home);
									$list_form[$nb_formulaire]['DATA'][]=$data_home;
								}
							}
							
							$nb_formulaire++;
						}
						break;
						
						case 7 ://accueil
						$req_homepage=$db->query('SELECT * FROM `cms_homepage` WHERE ID = '.$don['ID_ELEMENT']);
						if(mysql_num_rows($req_homepage)!=0){
							$info_homepage=mysql_fetch_array($req_homepage);
							$info_homepage=array_map('prepare_data_sql',$info_homepage);
							$list_homepage[]=$info_homepage;
							
							$list_homepage[$nb_homepage]['DATA']=mysql_data_extract('cms_template_data_'.$info_homepage['ID_TEMPLATE'],' WHERE ID_PARENT = '.$don['ID_ELEMENT']);
							
							/*
							$req_data_home=$db->query('select * from cms_template_data_'.$info_homepage['ID_TEMPLATE'].' WHERE ID_PARENT = '.$don['ID_ELEMENT']);
							if(mysql_num_rows($req_data_home)!=0){
								while($data_home=mysql_fetch_array($req_data_home)){
									$data_home=array_map('prepare_data_sql',$data_home);
									$list_homepage[$nb_homepage]['DATA'][]=$data_home;
								}
							}*/
							
							$list_homepage[$nb_homepage]['BLOCK']=array();
							
							$req_block_home=$db->query('select * from push_accueil where ID_ACCUEIL = '.$don['ID_ELEMENT']);
							if(mysql_num_rows($req_block_home)!=0){
								while($block_home=mysql_fetch_array($req_block_home)){
									$block_home=array_map('prepare_data_sql',$block_home);
									$list_homepage[$nb_homepage]['BLOCK'][]=$block_home;
								}
							}
							$nb_homepage++;
						}
						break;	

												
					}					
				}
				
			
				//pr($list_arbo);				
				//pr($list_rub);			
				//pr($list_form);
				//pr($list_galerie);
				//pr($list_photos);				
				//pr($list_video_cat);
				//pr($list_video);
				//pr($list_homepage);

				
				/*Insertion du push !*/
				
				foreach($list_site as $value){
					
					$db=New DB($value['DB_NAME'],$value['DB_HOST'],$value['DB_LOGIN'],$value['DB_PASSWORD']);
					
					
					/*rubrique rajouté !
					if(count($list_arbo)!=0){
						for($i=0;$i<count($list_arbo);$i++){
							
							
							$db->query('DELETE FROM push_rubrique WHERE ID = '.$list_arbo[$i]['ID']);
							$db->query('DELETE FROM cms_nst WHERE ID_PUSH = '.$id_pack);	
							
							$req="INSERT INTO push_rubrique (`ID`,`NAME`,`POSITION`) VALUES (".$list_arbo[$i]['ID'].",'".$list_arbo[$i]['NAME']."',".$list_arbo[$i]['POSITION'].")";
							$db->execute($req);
							//echo $req.'<br/>';
						}
					}*/
					
					/*Contenu des rubriques
					if(count($list_rub)!=0){
						
						for($i=0;$i<count($list_rub);$i++){								
							
							
							$req="INSERT INTO `cms_nst` (`LFT`, `RGT`, `ID_USER`, `ID_ELEMENT`, `ID_LANG`, `ID_UNIVERS`, `ID_PUSH`, `DT_CREATE`, `DT_MODIF`, `DT_DEB_PUBLI`, `DT_FIN_PUBLI`, `TYPE`, `TITLE`, `TEXT`, `META_DESC`, `META_KEY`, `RESUME`, `IMG_RESUME`, `ID_TEMPLATE`, `ACTIVE`) VALUES
							(".$list_rub[$i]['LFT'].", ".$list_rub[$i]['RGT'].", ".$list_rub[$i]['ID_USER'].", ".$list_rub[$i]['ID_ELEMENT'].", ".$list_rub[$i]['ID_LANG'].", ".$list_rub[$i]['ID_UNIVERS'].", ".$id_pack.", ".$list_rub[$i]['DT_CREATE'].", ".$list_rub[$i]['DT_MODIF'].", ".$list_rub[$i]['DT_DEB_PUBLI'].", ".$list_rub[$i]['DT_FIN_PUBLI'].", ".$list_rub[$i]['TYPE'].", '".$list_rub[$i]['TITLE']."', '".$list_rub[$i]['TEXT']."', '".$list_rub[$i]['META_DESC']."', '".$list_rub[$i]['META_KEY']."', '".$list_rub[$i]['RESUME']."', '".$list_rub[$i]['IMG_RESUME']."', ".$list_rub[$i]['ID_TEMPLATE'].", ".$list_rub[$i]['ACTIVE'].")";
							$db->execute($req);
							//echo $req.'<br/>';
							
						}
					}*/
					
					/*Description des galeries photos
					if(count($list_galerie)!=0){
						$db->execute('delete from cms_galerie where ID_PUSH = '.$id_pack);
					
						for($i=0;$i<count($list_galerie);$i++){	
							$req="INSERT INTO `cms_galerie` (`TITLE`, `TEXT`, `ORDRE`, `DT_CREATE`, `DT_MODIF`, `ID_USER`, `ID_UNIVERS`, `ID_LANG`, `ID_PUSH`) VALUES
							('".$list_galerie[$i]['TITLE']."', '".$list_galerie[$i]['TEXT']."', ".$list_galerie[$i]['ORDRE'].", ".$list_galerie[$i]['DT_CREATE'].", ".$list_galerie[$i]['DT_MODIF'].", ".$list_galerie[$i]['ID_USER'].", ".$list_galerie[$i]['ID_UNIVERS'].", ".$list_galerie[$i]['ID_LANG'].", ".$id_pack.")";
							//echo $req.'<br/>';
							$db->execute($req);
							$id_galerie=$db->lastInsertedId();
							
							
							//pr($list_photos[$list_galerie[$i]['ID']]);
							
							if(count($list_photos[$list_galerie[$i]['ID']])!=0){
							
								for($j=0;$j<count($list_photos[$list_galerie[$i]['ID']]);$j++){
								
									
									$req="INSERT INTO `cms_photo` (`TITLE`, `ORDRE`, `ID_GALERIE`, `ID_USER`, `DT_CREATE`, `DT_MODIF`, `FILE`) VALUES
										('".$list_photos[$list_galerie[$i]['ID']][$j]['TITLE']."', ".$list_photos[$list_galerie[$i]['ID']][$j]['ORDRE'].", ".$id_galerie.", ".$list_photos[$list_galerie[$i]['ID']][$j]['ID_USER'].", ".$list_photos[$list_galerie[$i]['ID']][$j]['DT_CREATE'].", ".$list_photos[$list_galerie[$i]['ID']][$j]['DT_MODIF'].", '".PUSH_URL."images/galerie/".$list_galerie[$i]['ID']."/".$list_photos[$list_galerie[$i]['ID']][$j]['FILE']."')";
									//echo $req.'<br/>';
									$db->execute($req);
								}
							}
						}
					}*/
					
					/*Catégorie vidéos supplémentaire !*/
					/*
					if(count($list_video)!=0){
						$list_new_id_video=array();
						for($i=0;$i<count($list_video);$i++){	
							$req="INSERT INTO `cms_video` (`TITLE`, `TEXT`, `ID_USER`, `ID_LANG`, `ID_PUSH`, `ID_UNIVERS`, `DT_CREATE`, `DT_MODIF`, `META_KEY`, `VIGNETTE`, `EMBED`) VALUES
							('".$list_video[$i]['TITLE']."', '".$list_video[$i]['TEXT']."', ".$list_video[$i]['ID_USER'].", ".$list_video[$i]['ID_LANG'].",".$id_pack.", ".$list_video[$i]['ID_UNIVERS'].", ".$list_video[$i]['DT_CREATE'].", ".$list_video[$i]['DT_MODIF'].", '".$list_video[$i]['META_KEY']."', '".$list_video[$i]['VIGNETTE']."','".$list_video[$i]['EMBED']."')";
							$db->execute($req);
							$id_video=$db->lastInsertedId();
							//$id_video=mt_rand();
							$list_new_id_video[$list_video[$i]['ID']]=$id_video;
						
						}
						//pr($list_new_id_video);
					}
					
					if(count($list_video_cat)!=0){
						//pr($list_video_cat);
						for($i=0;$i<count($list_video_cat);$i++){
							$req="INSERT INTO `cms_video_categorie` (`TITLE`, `ID_LANG`, `ID_UNIVERS`,`ID_PUSH`, `ORDRE`) VALUES ('".$list_video_cat[$i]['TITLE']."', ".$list_video_cat[$i]['ID_LANG'].", ".$list_video_cat[$i]['ID_UNIVERS'].",".$id_pack.", ".$list_video_cat[$i]['ORDRE'].")";
							//echo $req.'<br>';
							$db->execute($req);
							//$id_categorie=1;
							$id_categorie=$db->lastInsertedId();
							if(count($list_video_cat[$i]['VIDEOS'])!=0){
								for($j=0;$j<count($list_video_cat[$i]['VIDEOS']);$j++){
									//echo $list_new_id_video[$list_video_cat[$i]['VIDEOS'][$j]];
									$req="INSERT INTO cms_video_categorie_link (ID_VIDEO,ID_CATEGORIE,ORDRE) VALUES (".$list_new_id_video[$list_video_cat[$i]['VIDEOS'][$j]].",".$id_categorie.",".($j+1).") ";
									$db->execute($req);
									//echo $req.'<br>';
								}
							}
						}
					}
					*/
					//pr($list_form);
					/*Formulaires !
					if(count($list_form)!=0){
						for($i=0;$i<count($list_form);$i++){	
							$req="INSERT INTO `cms_formulaire` ( `NAME`, `DESC`, `SUCCESS`, `ACTIF`, `ID_LANG`, `ID_UNIVERS`, `ID_PUSH`, `LIMITATION`, `ID_USER`, `DT_CREATE`, `DT_MODIF`, `FULL_MSG`) VALUES
('".$list_form[$i]['NAME']."', '".$list_form[$i]['DESC']."', '".$list_form[$i]['SUCCESS']."', '".$list_form[$i]['ACTIF']."', ".$list_form[$i]['ID_LANG'].", ".$list_form[$i]['ID_UNIVERS'].", ".$id_pack.", ".$list_form[$i]['LIMITATION'].", ".$list_form[$i]['ID_USER'].", ".$list_form[$i]['DT_CREATE'].", ".$list_form[$i]['DT_MODIF'].", '".$list_form[$i]['FULL_MSG']."')";
							//echo $req.'<br>';
							$db->execute($req);
							$id_form=$db->lastInsertedId();
							
							//Enregistrements
							$list_form[$i]['STRUCTURE']=str_replace('cms_formulaire_data_'.$list_form[$i]['ID'],'cms_formulaire_data_'.$id_form,$list_form[$i]['STRUCTURE']);
							
							//Elements !
							if(count($list_form[$i]['ELEMENT'])!=0){
							
								for($j=0;$j<count($list_form[$i]['ELEMENT']);$j++){
									$req="INSERT INTO `cms_formulaire_champ` (`NAME`, `TYPE`, `ORDRE`, `HELP`, `REQUIRED`, `ID_FORM`, `SIZE`, `DEFAULT`) VALUES
	('".$list_form[$i]['ELEMENT'][$j]['NAME']."', ".$list_form[$i]['ELEMENT'][$j]['TYPE'].", ".$list_form[$i]['ELEMENT'][$j]['ORDRE'].", '".$list_form[$i]['ELEMENT'][$j]['HELP']."', ".$list_form[$i]['ELEMENT'][$j]['REQUIRED'].", ".$id_form.", ".$list_form[$i]['ELEMENT'][$j]['SIZE'].", '".$list_form[$i]['ELEMENT'][$j]['DEFAUT']."')";
									//echo $req.'<br>';
									$db->execute($req);
									$id_element=$db->lastInsertedId();
									//$id_element=mt_rand();
									$list_form[$i]['STRUCTURE']=str_replace('FIELD_'.$list_form[$i]['ELEMENT'][$j]['ID'],'FIELD_'.$id_element,$list_form[$i]['STRUCTURE']);
									
									
									if(count($list_form[$i]['ELEMENT'][$j]['OPTIONS'])!=0){
										for($k=0;$k<count($list_form[$i]['ELEMENT'][$j]['OPTIONS']);$k++){
											$req="INSERT INTO `cms_formulaire_champ_option` (`ID_ELEMENT`, `NAME`, `ORDRE`, `DEFAULT`) VALUES
											(".$id_element.", '".$list_form[$i]['ELEMENT'][$j]['OPTIONS'][$k]['NAME']."', ".$list_form[$i]['ELEMENT'][$j]['OPTIONS'][$k]['ORDRE'].", ".$list_form[$i]['ELEMENT'][$j]['OPTIONS'][$k]['DEFAULT'].")";
											//echo $req.'<br>';
											$db->execute($req);
										}
									}									
								}								
							}
							//Emails
							if(count($list_form[$i]['EMAIL'])!=0){
								for($j=0;$j<count($list_form[$i]['EMAIL']);$j++){
									$req="INSERT INTO cms_formulaire_email (`ID_FORM`,`EMAIL`) VALUES (".$id_form.",'".$list_form[$i]['EMAIL'][$j]['EMAIL']."')";
									//echo $req.'<br>';
									$db->execute($req);
								}
							}							
							
							//Creation de la table et ses datas
							
							$req=$list_form[$i]['STRUCTURE'];
							//pr($list_form[$i]['STRUCTURE']);
							for($j=0;$j<count($list_form[$i]['STRUCTURE']);$j++){
								$db->execute($list_form[$i]['STRUCTURE'][$j]);
							}							
							
						}
					}	
					*/
					//pr($list_form);
					//pr($list_homepage);
					/*
					if(count($list_homepage)!=0){
						for($i=0;$i<count($list_homepage);$i++){	
							
							$req="INSERT INTO `cms_homepage` (`ID_LANG`, `ID_UNIVERS`, `ID_PUSH`, `NAME`, `ID_TEMPLATE`, `SELECTED`, `DT_CREATE`, `DT_MODIF`) VALUES
							(".$list_homepage[$i]['ID_LANG'].", ".$list_homepage[$i]['ID_UNIVERS'].", ".$id_pack.", '".$list_homepage[$i]['NAME']."', ".$list_homepage[$i]['ID_TEMPLATE'].", 0, ".$list_homepage[$i]['DT_CREATE'].", ".$list_homepage[$i]['DT_MODIF'].")";
							
							echo $req."<br>";							
							//$id_accueil=mt_rand();
							$db->execute($req);
							$id_accueil=$db->lastInsertedId();
							//Les datas
							
							if(count($list_homepage[$i]['DATA'])!=0){
								$list_homepage[$i]['DATA']=str_replace('ID_PARENT_HOME',$id_accueil,$list_homepage[$i]['DATA']);
								for($j=0;$j<count($list_homepage[$i]['DATA']);$j++){
									$req=$list_homepage[$i]['DATA'][$j];
									echo $req."<br>";
									$db->execute($req);
								}
							}
							if(count($list_homepage[$i]['BLOCK'])!=0){
								for($j=0;$j<count($list_homepage[$i]['BLOCK']);$j++){
									$req='INSERT INTO push_accueil (ID_ACCUEIL,ID_BLOCK) VALUES ('.$id_accueil.','.$list_homepage[$i]['BLOCK'][$j]['ID_BLOCK'].')';
									echo $req."<br>";
									$db->execute($req);
								}
							}	
							
						}
					}	
					*/
					//pr($list_feature);
					if(count($list_feature)!=0){
						for($i=0;$i<count($list_feature);$i++){
							$req="INSERT INTO `cms_feature` (`DT_CREATE`, `DT_MODIF`, `LIBELLE`, `NAME`, `TEXTE`, `ID_LANG`, `ID_UNIVERS`, `ID_PUSH`, `ALL`, `LOCATION`, `POSITION`, `ORDRE`) VALUES
							(".$list_feature[$i]['DT_CREATE'].", ".$list_feature[$i]['DT_MODIF'].", '".$list_feature[$i]['LIBELLE']."', '".$list_feature[$i]['NAME']."', '".$list_feature[$i]['TEXTE']."', ".$list_feature[$i]['ID_LANG'].", ".$list_feature[$i]['ID_UNIVERS'].", ".$id_pack.", ".$list_feature[$i]['ALL'].", ".$list_feature[$i]['LOCATION'].", ".$list_feature[$i]['POSITION'].", ".$list_feature[$i]['ORDRE'].")";
							//echo $req."<br>";
							$db->execute($req);
						}
					}
				}
			}
			else $error=2;
		}
	}
}else $error=1;

switch($error){
	case 0:
	echo "Pack mis en place avec succès , journalisation : <br>".$msg_push;
	break;
	
	case 1 :
	echo "Le pack est inconnu !";
	break;
	
	case 2 :
	echo "Le pack est vide !";
	break;
	
}
?>