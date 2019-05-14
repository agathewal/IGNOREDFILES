<?
include('../config/configuration.php');

if(isset($_GET['id_cat']) && is_numeric($_GET['id_cat']))$id_cat=$_GET['id_cat'];
else $id_cat='';

if(isset($_GET['id_aide']) && is_numeric($_GET['id_aide']))$id_aide=$_GET['id_aide'];
else $id_aide='';

if(isset($_GET['search']))$search=$_GET['search'];
else $search='';

$ariane_aide=array();



if($search!='' && is_numeric($id_aide)){

	$ariane_aide[]=array("LIBELLE"=>"FAQ","URL"=>'faq_view.php');
	$ariane_aide[]=array("LIBELLE"=>"Recherche sur \"".$search."\"","URL"=>'faq_view.php?search='.$search);
		
	$req2=$db->query('select * from comm_faq_article where ID = '.$id_aide);
	if(mysql_num_rows($req2)!=0){
		$art_info=mysql_fetch_array($req2);
		$ariane_aide[]=array("LIBELLE"=>stripslashes($art_info['TITLE']),"URL"=>'faq_view.php?id_aide='.$id_aide.'&search='.urlencode($search));
		$search_article=1;
		$req=$db->query("select * from comm_faq_article where (`TEXT` REGEXP '".addslashes($search)."' OR TITLE REGEXP '".addslashes($search)."') AND ID != ".$id_aide);
		$list_result=mysql_num_rows($req);
		if($list_result!=0){
			$list_cat='
			<div id="questions">
				<ul>';
			while($don=mysql_fetch_array($req)){			
				$list_cat.='<li style="line-height:18px;"><a href="?id_aide='.$don['ID'].'&search='.urlencode($search).'">'.stripslashes($don['TITLE']).'</a><br>'.supertruncate(strip_tags(html_entity_decode(stripslashes($don['TEXT']),ENT_QUOTES,'UTF-8')),stripslashes($search),100,100).'</li>';			
			}
			$list_cat.='
				</ul>
			</div>';
		}
	}
	else {
		header('location:faq_view.php');
		die();
	}
}
elseif($search!=''){

	$ariane_aide[]=array("LIBELLE"=>"FAQ","URL"=>'faq_view.php');
	$ariane_aide[]=array("LIBELLE"=>"Recherche sur \"".$search."\"","URL"=>'faq_view.php?search='.$search);
		
	$search_plus=1;
	$req=$db->query("select * from comm_faq_article where `TEXT` REGEXP '".addslashes($search)."' OR TITLE REGEXP '".addslashes($search)."'");
	$list_result=mysql_num_rows($req);
	if($list_result!=0){
		$list_cat='
		<div id="questions">
			<ul>';
		while($don=mysql_fetch_array($req)){			
			$list_cat.='<li style="line-height:18px;"><a href="?id_aide='.$don['ID'].'&search='.urlencode($search).'">'.stripslashes($don['TITLE']).'</a><br>'.supertruncate(strip_tags(html_entity_decode(stripslashes($don['TEXT']),ENT_QUOTES,'UTF-8')),stripslashes($search),100,100).'</li>';			
		}
		$list_cat.='
			</ul>
		</div>';
	}
}
else if(is_numeric($id_cat) && is_numeric($id_aide)){

	$req=$db->query('select * from comm_faq_categorie where ID = '.$id_cat);
	$req2=$db->query('select * from comm_faq_article where ID = '.$id_aide);
	
	if(mysql_num_rows($req)!=0 && mysql_num_rows($req2)!=0){
	
		$cat_info=mysql_fetch_array($req);
		$art_info=mysql_fetch_array($req2);
		
		$ariane_aide[]=array("LIBELLE"=>"FAQ","URL"=>'faq_view.php');
		$ariane_aide[]=array("LIBELLE"=>stripslashes($cat_info['NAME']),"URL"=>'faq_view.php?id_cat='.$id_cat);
		$ariane_aide[]=array("LIBELLE"=>stripslashes($art_info['TITLE']),"URL"=>'faq_view.php?id_aide='.$id_aide.'&id_cat='.$id_cat);
		
		$article=1;
		$req=$db->query('select * from comm_faq_article,comm_faq_rel where comm_faq_article.ID = comm_faq_rel.ID_ARTICLE AND comm_faq_rel.ID_RUBRIQUE = '.$id_cat.' AND comm_faq_rel.ID_ARTICLE != '.$id_aide.' ORDER BY comm_faq_rel.`ORDRE`');
		$nb_categorie=mysql_num_rows($req);
		$list_cat='
		<div id="questions">
			<ul>';
		if($nb_categorie!=0){
			while($don=mysql_fetch_array($req)){
				$list_cat.='<li style="line-height:18px;"><a href="?id_aide='.$don['ID_ARTICLE'].'&id_cat='.$id_cat.'">'.stripslashes($don['TITLE']).'</a></li>';
			}
		}
		$list_cat.='
			</ul>
		</div>';
	}
	else {
		header('location:faq_view.php');
		die();
	}
}
elseif(is_numeric($id_cat)){
	
	$req=$db->query('select * from comm_faq_categorie where ID = '.$id_cat);
	if(mysql_num_rows($req)!=0){
		$cat_info=mysql_fetch_array($req);
		$ariane_aide[]=array("LIBELLE"=>"FAQ","URL"=>'faq_view.php');
		$ariane_aide[]=array("LIBELLE"=>stripslashes($cat_info['NAME']),"URL"=>'faq_view.php?id_cat='.$id_cat);
		
		$categorie=1;
		$req=$db->query('select * from comm_faq_article,comm_faq_rel where comm_faq_article.ID = comm_faq_rel.ID_ARTICLE AND comm_faq_rel.ID_RUBRIQUE = '.$id_cat.' ORDER BY comm_faq_rel.`ORDRE`');
		$nb_categorie=mysql_num_rows($req);
		$list_cat='
		<div id="questions">
			<ul>';
		if($nb_categorie!=0){
			while($don=mysql_fetch_array($req)){
				$list_cat.='<li style="line-height:18px;"><a href="?id_aide='.$don['ID_ARTICLE'].'&id_cat='.$id_cat.'">'.stripslashes($don['TITLE']).'</a></li>';
			}
		}
		$list_cat.='
			</ul>
		</div>';
	}
	else {
		header('location:faq_view.php');
		die();
	}
}
elseif($id_cat=='' && $id_aide=='' && $search==''){
	$accueil=1;
	$req=$db->query('select * from comm_faq_categorie ORDER BY `ORDRE`');
	$nb_categorie=mysql_num_rows($req);
	$list_cat='
	<div id="questions">
		<ul>';
	if($nb_categorie!=0){
		while($don=mysql_fetch_array($req)){
			$list_cat.='<li style="line-height:18px;"><a href="?id_cat='.$don['ID'].'">'.stripslashes($don['NAME']).'</a></li>';
		}
	}
	$list_cat.='
		</ul>
	</div>';
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery-1.5.min.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.tools.min.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.corner.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/fct.js"></script>
<link rel="stylesheet" type="text/css" href="<? echo URL_DIR;?>/images/resources/css/ext-all.css" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/ext-all.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/cufon-yui.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/eurofurence_500.font.js"></script>
<title>Guides du CMS</title>
<script>
$(function(){
	$("#content_block_help,#ariane_help,#voir_votre_site").corner("5px").parent().css('padding', '1px').corner("5px");
});
</script>
</head>
<body>
<div id="header_cms">
	<div align="center"><h1 style="margin-left:0px;">Guides de votre outil d'administration</h1></div>
	<div class="clear"></div>
</div>
<form method="get" action="faq_view.php" name="form_search">
<div style="width:578px;margin-left:25px;">
	<div style='width:240px;float:right;padding: 0px;height:25px;'>
		<div style="float:left;width:200px;">
			Rechercher : 
			<input type="text" style='width:120px;' name="search" value="" /> 
		</div>
		<div class="bordure_menu" style="width:26px;cursor:pointer;height:20px;float:right;" onclick='javascript:document.forms["form_search"].submit();'>
			<div id="voir_votre_site" style='width:26px;height:20px;'>
				OK
			</div>
		</div>
	</div>
</div>
</form>
<div class='clear'></div>
<div class="bordure_menu" style="margin-top:10px;width:578px;margin-left:25px;" id="zone_aide">
	
	<div id="ariane_help">
		<p class="axe-y" id='ariane_5'>
		<?
		$nb_element=count($ariane_aide);
		if($nb_element==0){
			echo "<span>FAQ</span>";
		}
		else{
			for($i=0;$i<$nb_element;$i++){
				if($i!=0)echo ' <img src="'.URL_DIR.'/images/fleche_ariane.jpg" class="separation" > ';
				if($nb_element==($i+1))echo "<span>".$ariane_aide[$i]['LIBELLE']."</span>";
				else echo '<a href="'.$ariane_aide[$i]['URL'].'">'.$ariane_aide[$i]['LIBELLE'].'</a>';
			}	
		}
		?>
		</p>
		
	</div>
	
</div>
<div class="bordure_menu" style="margin-top:10px;width:578px;margin-left:25px;">
	<div id="content_block_help">
	<?
	if($accueil){
		echo $list_cat;
	}elseif($categorie){
		echo $list_cat;
	}elseif($search_plus){
	?>
	<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery.highlight-3.js"></script>
	<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#questions").highlight('<?echo $search;?>');
	});
	</script>	
	<?
		if($list_result!=0)echo $list_cat;
		else echo 'Aucun résultat trouvé <br><br> <a href="faq_view.php">Retour à l\'accueil de la FAQ</a>';
	}
	elseif($article || $search_article){
		echo "<h1>".stripslashes($art_info['TITLE'])."</h1><br>".stripslashes($art_info['TEXT']);
		if($article && $nb_categorie!=0){
			echo "<br><p><b>Autres aide de la rubrique :</b></p><br/> ".$list_cat;
		}
		if($search_article && $list_result!=0){
			echo "<br><p><b>Autres résultat de la recherche :</b></p><br/> ".$list_cat;
		}
	}
	?>
	</div>
</div>
<script type="text/javascript">
	Cufon.replace('#header_cms h1', { fontFamily: 'eurofurence', hover:true});
	Cufon.now();
</script>
</body>
</html>