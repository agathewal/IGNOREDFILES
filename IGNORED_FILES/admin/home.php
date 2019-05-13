<?
if(!isset($_GET['id_menu']))$no_center_style=1;//pour la page d'accueil de l'admin
include('header.php');

$test=New Login();
$test->confirm_Member();	
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';
?>

<?
/*
Accueil de l'admin
*/
if ($menu==''){	
?>
<script>
$(document).ready(function(){
	$("#content_home,#content_content").corner("5px").parent().css('padding', '1px').corner("5px");
});
</script>
<div style='margin-top:10px;'>
	<div class="bordure_menu">
		<div style='background-color:#FFFFFF;width:658px;height:428px;padding:20px;' id="content_content">
		
		<div class="bordure_menu" style="width:656px;float:left;">
			<div id="content_home">
				<div class="intro_home">
					<h2 style='color:#ff9900;'>Bienvenue dans l'administration de votre communautaire !</h2>
					<p>
					Elle vous permet de gérer votre communautaire.<br/>
					Chaque univers est accessible par la <b>colonne de gauche</b>, il vous suffit de cliquer et de vous laisser guider !
					Une <b>aide en ligne</b> vous accompagne constamment dans vos actions, n’ayez crainte !<br/>
					Vous pouvez si vous le souhaitez, faire réapparaître l'interface d'installation initiale en cliquant sur l'icône <b>Paramètre de base</b>.
					</p>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<div style='height:15px;width:100%;'>&nbsp;</div>
		<div style="background-color:#cccccc;width:100%;height:1px;"><img src="<?echo URL_DIR;?>/images/pix.gif" ></div>
		<div class="clear"></div>
		<!--Slot de présentation des modules-->
		<div style="margin-top:20px;">
			<div style='float:left;background-image:url("<?echo URL_DIR;?>/images/plot_contenu.jpg");' class="home_item" onclick="javascript:redir('home.php?id_menu=14');">
				<div style="margin-top:80px;" class="titre_section_home">Modération</div>
				<div class="paragraph_section_home">Gérez les inscriptions et contenus</div>
			</div>
			
			<div style='float:left;background-image:url("<?echo URL_DIR;?>/images/plot_anim.jpg");margin-left:19px;' class="home_item" onclick="javascript:redir('home.php?id_menu=12');">
				<div style="margin-top:80px;" class="titre_section_home">Configuration</div>
				<div class="paragraph_section_home">Modifier la configuration du site communautaire</div>
			</div>
			
			<div style='float:left;background-image:url("<?echo URL_DIR;?>/images/plot_stats.jpg");margin-left:19px;' class="home_item" onclick="javascript:redir('home.php?id_menu=4');">
				<div style="margin-top:80px;" class="titre_section_home">Statistiques</div>
				<div class="paragraph_section_home">Analysez le traffic de votre site internet</div>
			</div>
			
			<div style='float:left;background-image:url("<?echo URL_DIR;?>/images/plot_guides.jpg");margin-left:19px;' class="home_item" onclick="javascript:popupWindow('faq_view.php','Aide',640,700,1);">
				<div style="margin-top:80px;" class="titre_section_home">Guides</div>
				<div class="paragraph_section_home">Consultez les aides en ligne</div>
			</div>
			<div class="clear"></div>	
		</div>
	</div>
<?
}
else{
/*
Autres section
*/
?>
<!--Home-->	
	<div class="intro_home">
		<? 
		if($menu==2){		
		?>
		<h2 class='menu_<?echo $css_menu;?>'><?echo $libelle_menu;?></h2>
		<p></p>
		<?}
		else if($menu==14){
		?>
		<h2 class='menu_<?echo $css_menu;?>'><?echo $libelle_menu;?></h2>
		<p>
		</p>
		<?}
		else if($menu==12){
		?>
		<h2 class='menu_<?echo $css_menu;?>'><?echo $libelle_menu;?></h2>
		<p>
		</p>
		<?}
		else if($menu==1){
		?>
		<h2 class='menu_<?echo $css_menu;?>'><?echo $libelle_menu;?></h2>
		<p>
		</p>
		<?}
		else if($menu==4){
		?>
		<h2 class='menu_<?echo $css_menu;?>'><?echo $libelle_menu;?></h2>
		<p>
		</p>
		<?
		}?>
		</div>	
		
		
		<?
		$req_nav=$db->query('select * from comm_module_menu,comm_module WHERE comm_module.ID=comm_module_menu.ID_MODULE AND ID_MENU = '.$menu.' ORDER BY ORDRE');
		$y=0;
		if(mysql_num_rows($req_nav)!=0){	

			switch($menu){
				case 14:
				$color_hover='CC0099';
				break;
				
				case 12:
				$color_hover='FF0000';
				break;
				
				case 1:
				$color_hover='ADAC48';
				break;
				
				case 4:
				$color_hover='75551C';
				break;
			}
		?>
		<script>
		$(document).ready(function(){
			$(".home_item_2").corner("5px").parent().css('padding', '1px').corner("5px");			
			$('.zone_module_intro').mouseover(function(){
				$(this).css({'background-color':'#<?echo $color_hover;?>'});
				$(this).find('.img_mod').hide();
				$(this).find('.img_mod2').show();
				$(this).find('h1').css({'color':'#<?echo $color_hover;?>'});
				Cufon.replace('.intro_module h1');
			}).mouseout(function(){
				$(this).css({'background-color':'#CCCCCC'});
				$(this).find('.img_mod2').hide();
				$(this).find('.img_mod').show();
				$(this).find('h1').css({'color':'#333333'});
				Cufon.replace('.intro_module h1');
			});
		});
		</script>
		<?
			while($don_nav=mysql_fetch_array($req_nav)){		
				$droit_access=$db->countOf('comm_module_user','ID_USER = '.$_SESSION['id'].' AND ID_MODULE = '.$don_nav["ID"]);
				if($droit_access){
					$don_nav=array_map('stripslashes',$don_nav);
					
					if($y%2==0){
						echo '
			<div class="clear"></div>
			<div style="margin-top:20px;">';		
					}
		?>
			<div class="bordure_menu zone_module_intro" <?if($y%2==1){echo "style='margin-left:18px;'";}?> onclick="javascript:<?
			if($don_nav['ID']!=24){echo "redir('".$don_nav['URL']."');";}else{ echo "popupWindow('stats_site.php', 'Statistiques', 1024, 700, 1);";}?>">
				<div style='background-image:url("<?echo URL_DIR;?>/images/module_hover.jpg");' class='home_item_2'>
					<div class="intro_module">
						<h1><?echo $don_nav['LIBELLE'];?></h1>
						<p><?echo $don_nav['INTRO'];?></p>
					</div>
					<div style='float:left;width:88px;margin-top:5px;'><img src='<?echo URL_DIR;?>/images/<?if($don_nav['THUMB']!=''){echo $don_nav['THUMB'];}else{echo "joker_module.jpg";}?>' class="img_mod"><img src='<?echo URL_DIR;?>/images/modif_module.jpg' class="img_mod2"></div>
				</div>
			</div>
		<?		
					if($y%2==1){
						echo '
			</div>';		
					}
					$y++;
				}
			}
			if($y%2==1){
				echo '
			</div>';		
			}
		}
		?>
<!--Fin Home-->	
<?
}
include('footer.php');
?>