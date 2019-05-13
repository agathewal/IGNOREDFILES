<?
require_once('../config/configuration.php');
ob_start();

if(!empty($id_module)){
	$menu=$db->queryUniqueValue('select ID_MENU from comm_module_menu WHERE ID_MODULE = '.$id_module.' ORDER BY ORDRE');
}
$type_css_ariane=1;
if($no_menu!=1){
	if(!is_numeric($_SESSION['id'])){
		header('location:login.php');
		die();
	}
	
	$req_nav=$db->query('select * from comm_menu ORDER BY ORDRE');
	$menu_affiche=array();
	$i=0;
	$premier_actif=0;
	while($don=mysql_fetch_array($req_nav)){
		$req_nav2=$db->query('select * from comm_module_menu,comm_module WHERE comm_module.ID=comm_module_menu.ID_MODULE AND ID_MENU = '.$don['ID'].' ORDER BY ORDRE');
		$menu_affiche[$i]['actif']=0;
		while($don2=mysql_fetch_array($req_nav2)){		
			$droit_access=$db->countOf('comm_module_user','ID_USER = '.$_SESSION['id'].' AND ID_MODULE = '.$don2["ID"]);
			
			if($droit_access){
				if($menu_affiche[$i]['actif']==0){
					$menu_affiche[$i]['actif']=1;
					if(!$premier_actif){
						$premier_actif=1;
						//$menu_id=$don['ID'];
					}
					$menu_affiche[$i]['id']=$don['ID'];
					$menu_affiche[$i]['name']=stripslashes($don['NAME']);
					$menu_affiche[$i]['css']=$don['CSS'];
				}
				$menu_affiche[$i][]=$don2;
			}
			//echo $don2["ID"].'<br>';
		}
		$i++;
	}
	if($menu==''){
		if(isset($_GET['id_menu']))$menu=$_GET['id_menu'];
		//else $menu=$menu_id;
	}
	if($menu!="")$css_menu=$db->queryUniqueValue('select CSS from comm_menu WHERE ID = '.$menu);
}	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title>Administration <? if(!empty($title)) echo $title;?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/x-icon" href="<?echo URL_DIR;?>/images/favicon.ico" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/admin.css" type="text/css" media="screen" charset="utf-8" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/css/jquery.fancybox-1.3.1.css" type="text/css"  media="screen" />
<link rel="stylesheet" href="<? echo URL_DIR;?>/images/resources/css/ext-all.css" type="text/css" media="screen"  />
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/dd.css" type="text/css" media="screen" charset="utf-8" />
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.tools.complete.min.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.corner.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/notifier.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/cufon-yui.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/eurofurence_500.font.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.validate.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.metadata.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.wait.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.checkboxes.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.mousewheel-3.0.2.pack.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.fancybox-1.3.1.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/customSelect.jquery.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/fct.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/ext-all.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.dd.js"></script>
<script type="text/javascript">
<?if(isset($_SESSION['help'])){?>
var help_statut=<? echo $_SESSION['help'];?>;
<?
}
else{
?>
var help_statut=1;
<?
}
?>

$(function(){

	if(!$.browser.msie){
		$(".select_class").msDropDown({mainCSS:'dd2'});
	}
	
	$("#header_nav_1,#header_nav_2,#header_nav_3,#header_nav_4,#header_nav_5,#header_nav_6,#header_nav_7,#voir_votre_site,#voir_votre_site2,#ariane<?echo $extend_class;?>,#content_block<?echo $extend_class;?>,.btn_style").corner("5px").parent().css('padding', '1px').corner("5px");	
	
	$("#header_nav_on_1,#header_nav_on_2,#header_nav_on_3,#header_nav_on_4,#header_nav_on_5,#header_nav_on_6").corner("5px tl tr").parent().css('padding', '1px').corner("5px");
	$("#menu_nav_2,#menu_nav_3,#menu_nav_4,#menu_nav_5,#menu_nav_6").corner("5px bl br");	
	$(".btn_style").hover( function () {
		$(this).css({'background-color':'#E2E2E2'});
	},function(){
		$(this).css({'background-color':'#f8f8f8'});
	});
	
	
});
</script>
<!--[if IE 8]>
<style type='text/css'>
*html #help_zone { 
    position: absolute;
    top: expression((document.documentElement.scrollTop || document.body.scrollTop) +
    this.offsetHeight - 120);
    left: 0px;
}
</style>
<![endif]-->
<!--[if lte IE 7]>
<style type='text/css'>
#help_zone{
	position:absolute;
	top: expression((document.documentElement.scrollTop || document.body.scrollTop) +
    this.offsetHeight - 90);
    left: 0px;
}
</style>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/DD_belatedPNG_0.0.8a-min.js"></script>
<script>
$(function(){
	DD_belatedPNG.fix('img, .btn_style, .aide-legende, #header_help, #bottom_help');
});
</script>
<![endif]-->
</head>
<body<?if(is_numeric($css_menu)){ echo ' style=\'background-image: url("../images/fond_header_'.$css_menu.'.jpg");\''; } 
if($no_bg_body)echo ' style="background-image:none;"';?>>
<div id="cms_content<?echo $extend_class;?>">
	<?if($no_menu!=1){?>
	<div id="header_cms">
		
		<div style="float:left;width:400px;"><div style='float:left;width:80px;margin-top:31px;'><img src="<?echo URL_DIR;?>/images/logo_nexi.jpg"></div><h1> - <?echo stripslashes(WEBSITE_NAME);?></h1></div>
		<div id="header_right_zone">
			<?if(is_numeric($_SESSION['id'])){
				$req_user=$db->query('select * from comm_admin_user where ID = '.$_SESSION['id']);
				$don_user=mysql_fetch_array($req_user);
			}?>
			<div id="header_info">
				Bonjour <?echo $don_user['FNAME'];?> 
				<img src="<? echo URL_DIR;?>/images/separation_info_header.jpg" align="absmiddle" class="separation"> 
				<a href="login.php?logout=1">Se déconnecter</a> 
				<img src="<? echo URL_DIR;?>/images/separation_info_header.jpg" align="absmiddle" class="separation"> 
				<a href='javascript:toggle_help();' id="desactive_help"><?if($_SESSION['help']==0){echo "Activer l'aide";}else{ echo "Désactiver l'aide"; }?></a>
			</div>
			<div class="clear"></div>
			<div style="background-color:#E5E5E5;">
				<div class="bordure_menu" style="width:100px;margin-top:8px;float:right;">
					<div id="voir_votre_site">
						<a href="<?echo URL_DIR;?>" target="_blank">Voir votre site</a>
					</div>
				</div>
			</div>
			
		</div>
		<div id='help_zone'>
			<div id='header_help'><h3 class="titre_help<?echo $css_menu;?>"></h3></div>
			<div id='center_help'>
				<div id="content_help"></div>
				<div class="clear"></div>				
			</div>
			<div id='bottom_help'></div>						
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	
	<?if($nav_on==1){?>		
	<div>
		<!--start nav-->
		<div id="nav">
			<div id="nav_1" onclick="javascript:redir('home.php');">
				<div class="bordure_menu">
					<div id="header_nav_1"><h3 <?if($menu=='')echo 'class="on"';?>><a href="home.php">Accueil</a></h3></div>
				</div>
			</div>
			<?
			for($i=0;$i<count($menu_affiche);$i++){
				if($menu_affiche[$i]['actif']){
			?>
			<div id="nav_<? echo $menu_affiche[$i]['css']; ?>" onclick="javascript:redir('home.php?id_menu=<? echo $menu_affiche[$i]['id'];?>');">
				<div class="bordure_menu">
					<div id="header_nav_<?if($menu==$menu_affiche[$i]['id'])echo 'on_';?><? echo $menu_affiche[$i]['css']; ?>"><h3 <?if($menu==$menu_affiche[$i]['id'])echo 'class="on"';?>><a href="<? echo 'home.php?id_menu='.$menu_affiche[$i]['id'];?>"><? echo $menu_affiche[$i]['name']; ?></a></h3></div>
					<?
					if($menu==$menu_affiche[$i]['id']){
						$type_css_ariane=$menu_affiche[$i]['css'];
						
						$ariane_element['URL']=ADD_DIR."/admin/home.php?id_menu=".$menu;
						$ariane_element['LIBELLE']=$menu_affiche[$i]['name'];					
						$libelle_menu=$menu_affiche[$i]['name'];					
						$ariane_admin[]=$ariane_element;
						
						$req_nav=$db->query('select * from comm_module_menu,comm_module WHERE comm_module.ID=comm_module_menu.ID_MODULE AND ID_MENU = '.$menu.' ORDER BY ORDRE');
						
					?>
					<div id="menu_nav_<? echo $menu_affiche[$i]['css']; ?>">
						<?
						$y=0;
						while($don_nav=mysql_fetch_array($req_nav)){		
							$droit_access=$db->countOf('comm_module_user','ID_USER = '.$_SESSION['id'].' AND ID_MODULE = '.$don_nav["ID"]);
							if($droit_access){
								$don_nav=array_map('stripslashes',$don_nav);
								if($y==0)echo'<div style="height:8px;"></div>';	
								echo '<div class="nav_element';
								if($don_nav['ID_MODULE']==$id_module){
									echo'_on first_element';
									$ariane_element['URL']=$don_nav['URL'];
									$ariane_element['LIBELLE']=$don_nav['LIBELLE'];					
									$ariane_admin[]=$ariane_element;
								}															
								echo '"><a href="'.$don_nav['URL'].'" title="'.$don_nav['LIBELLE'].'">'.$don_nav['LIBELLE'].'</a></div>';
								$y++;
							}
						}						
						?>						
						<div id="bottom_nav"><img src='<? echo URL_DIR;?>/images/pix.gif'></div>
					</div>
					<?}?>
				</div>
			</div>
			<?
				}
			}
			?>		
			<div id="nav_7" onclick="javascript:popupWindow('faq_view.php', 'Aide', 640, 700, 1);">			
				<div class="bordure_menu">
					<div id="header_nav_7"><h3 ><a href="javascript:popupWindow('faq_view.php', 'Aide', 640, 700, 1);">Guides</a></h3></div>
				</div>
			</div>
		</div>
	</div>
	<!--end nav-->
	<!--start main-->
	<div id="main_block<?echo $extend_class;?>">
		<div class="bordure_menu">
			<div id="ariane<?echo $extend_class;?>">
				<p class="axe-y" id="<?if($type_css_ariane==""){echo 'ariane_1';}else{ echo 'ariane_'.$type_css_ariane;}?>">
				<?
				if(count($ariane_element2)!=0)$ariane_admin[]=$ariane_element2;
				if(count($ariane_element3)!=0)$ariane_admin[]=$ariane_element3;
				$nb_element=count($ariane_admin);
				if($nb_element==0){
					echo "<span class='action_on'>Accueil</span>";
				}
				else{					
					for($i=0;$i<$nb_element;$i++){
						if($i!=0)echo ' <img src="'.URL_DIR.'/images/fleche_ariane.jpg" class="separation" > ';
						if($nb_element==($i+1))echo "<span class='action_on'>".$ariane_admin[$i]['LIBELLE']."</span>";
						else echo '<a href="'.$ariane_admin[$i]['URL'].'">'.$ariane_admin[$i]['LIBELLE'].'</a>';
					}
				}
				?>			
				</p>
			</div>
		</div>
		<div class="clear"></div>
		<!--start content-->
		<?if ($no_center_style!=1){?>
		<div class="bordure_menu" style="margin-top:10px;">
			<div id="content_block<?echo $extend_class;?>">
		<?}?>
	<?}?>
	<?}?>