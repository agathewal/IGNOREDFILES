<?
$id_module=23;
include('header.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';


if(is_numeric($_POST['charte_form']) && is_numeric($_POST['accueil_form'])){
	$db->execute('UPDATE cms_homepage SET SELECTED = 0');
	$db->execute('UPDATE cms_homepage SET SELECTED = 1 WHERE ID_TEMPLATE = '.$_POST['accueil_form'].' AND ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' ORDER BY ID');
	set_c('CHARTE',$_POST['charte_form']);
	$_SESSION['notification'][]=array(1,"Aspect graphique","Les changements ont été appliqués.");
	header('location:graphisme.php');
	die();
}



/*Affichage de la charte*/
$req=$db->query('select cms_charte.ID as ID_CHARTE,cms_charte_declinaison.ID AS ID_DECLI, cms_charte.NAME as CHART_NAME, cms_charte_declinaison.NAME, COLORI, THUMB, HOME_WIDTH ,  HOME_HEIGHT, TEXT_LEFT, TEXT_TOP from cms_charte,cms_charte_declinaison WHERE cms_charte.ID = cms_charte_declinaison.ID_TEMPLATE order by cms_charte.ID,cms_charte_declinaison.ORDRE');
$id_charte=0;
$list_image='';
$list_decli='
var list_taille= new Array();
var list_dim= new Array();
';
$first='';
$list_charte=array();
$list_couleur='';
$num_decli=0;
while($don=mysql_fetch_array($req)){

	$temp=pathinfo($don['THUMB']);
	$name_small=$temp['dirname'].'/'.$temp['filename'].'_small.'.$temp['extension'];
		
	if($don['ID_CHARTE']!=$id_charte){;
		$id_charte=$don['ID_CHARTE'];	
		$list_couleur.='
		list_dim['.$don['ID_CHARTE'].']=new Array();
		';
		$num_decli=0;
		$list_charte[]='<img src="'.URL_DIR.'/admin/get-adaptative-thumb.php?url='.urlencode($name_small).'&width=90&height=70" width="90" height="70" alt="'.stripslashes($don['CHART_NAME']).'" onclick="javascript:change_charte(\\\''.$don['THUMB'].'\\\','.$don['ID_CHARTE'].','.$don['ID_DECLI'].');" style="cursor:pointer;">';	
	}
	
	$list_decli.='
	list_taille['.$don['ID_DECLI'].']=new Array();
	list_taille['.$don['ID_DECLI'].']["width"]='.$don['HOME_WIDTH'].';
	list_taille['.$don['ID_DECLI'].']["height"]='.$don['HOME_HEIGHT'].';
	';
		
	/*Colori*/
	$list_couleur.='
	list_dim['.$don['ID_CHARTE'].']['.$num_decli.']=new Array();
	list_dim['.$don['ID_CHARTE'].']['.$num_decli.']["ID"]=\''.$don['ID_DECLI'].'\';
	list_dim['.$don['ID_CHARTE'].']['.$num_decli.']["IMG"]=\''.$don['THUMB'].'\';
	';
	$num_decli++;
}

$list_charte_html='';
for($i=0;$i<count($list_charte);$i++){
	if($i%2==0)$list_charte_html.='<li>';
	$list_charte_html.=$list_charte[$i];
	if($i%2==0)$list_charte_html.='<img src="'.URL_DIR.'/images/pix.gif" width="10" height="50" style="border:0px;">';
	else $list_charte_html.='</li>';							
}	
if($i%2==1) $list_charte_html.='</li>';	
$nb_charte=count($list_charte);

$list_home='';

$req=$db->query('select * from cms_template where TYPE = 1 order by ORDRE');
$i=0;
$nb_home=mysql_num_rows($req);
while($don=mysql_fetch_array($req)){		
	if($i%2==0)$list_home.='<li>';
	$list_home.='<img src="'.URL_DIR.'/admin/get-adaptative-thumb.php?url='.urlencode($don['FULL']).'&width=90&height=70" width="90" height="70" alt="'.stripslashes($don['NAME']).'" onclick="javascript:change_accueil(\\\''.$don['FULL'].'\\\','.$don['ID'].');" style="cursor:pointer;">';
	if($i%2==0)$list_home.='<img src="'.URL_DIR.'/images/pix.gif" width="10" height="50" style="border:0px;">';
	else $list_home.='</li>';	
	$i++;
}
if($i%2==1) $list_home.='</li>';	

$id_decli_cours=0;
$id_home_cours=0;

$info_charte=$db->query('select ID_TEMPLATE,THUMB from cms_charte_declinaison WHERE ID = '.ID_CHARTE);
$don_charte=mysql_fetch_array($info_charte);
$temp=pathinfo($don_charte['THUMB']);
$name_small=$temp['dirname'].'/'.$temp['filename'].'_small.'.$temp['extension'];
$id_decli_cours=ID_CHARTE;

$req_homepage=$db->query("SELECT cms_homepage.ID_TEMPLATE,cms_template.FULL FROM `cms_homepage`,`cms_template` WHERE cms_homepage.ID_TEMPLATE=cms_template.ID AND cms_homepage.SELECTED = 1 AND cms_homepage.ID_LANG = ".$_SESSION['langue']." AND cms_homepage.ID_UNIVERS = ".$_SESSION['univers']);
$don_home=mysql_fetch_array($req_homepage);
$id_home_cours=$don_home['ID_TEMPLATE'];

?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery.jcarousel.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?echo URL_DIR;?>/images/carousel/skins/tango/skin.css" />
<script>
var id_charte_cours=<?if ($don_charte['ID_TEMPLATE']!=''){echo $don_charte['ID_TEMPLATE'];}else{ echo '0';}?>;
var id_decli_cours=<?echo $id_decli_cours;?>;
var id_home_cours=<?echo $id_home_cours;?>;
var url_visu_cours='';
var step_en_cours=1;
var nb_home=<? echo $nb_home;?>;
var nb_charte=<? echo $nb_charte;?>;
/*Carousel de l'étape 2*/
function mycarousel_initCallback(carousel) {

	jQuery('#car_down').bind('click', function() {
		carousel.next();
		return false;
	});

	jQuery('#car_up').bind('click', function() {
		carousel.prev();
		return false;
	});
};
function change_step(step_num){
	
	//alert(step_en_cours);
	if((step_num==2 || step_num==3) && id_charte_cours==0)alert('Veuillez sélectionner une charte graphique');
	else{
		$('#btn_dir_'+step_en_cours).removeClass('btn_style3').addClass('btn_style2').css({'background-image':'none','color':'#686868'}).hover(function(){$(this).css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'});}, function (){$(this).css({'background-image':'none','color':'#686868'});});
		
		if(step_num==1){
		
			$('#text_aide_step2').html('Choisissez la charte graphique<br/>de votre site puis cliquez sur <b>OK</b>.');
			
			$('#btn_dir_1').removeClass('btn_style2').addClass('btn_style3').unbind('mouseenter').unbind('mouseleave').css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'}) ;
			
			$('#mycarousel,.jcarousel-skin-tango').remove();
			
			$('#zone_carousel').prepend('<ul id="mycarousel" class="jcarousel jcarousel-skin-tango"><?echo $list_charte_html;?></ul>');
			
			if(nb_charte>6){
				$('#fleche').show();
			}else{
				$('#fleche').hide();
			}
			
			$("#mycarousel").jcarousel({
				vertical: true,
				scroll: 2,
				initCallback: mycarousel_initCallback,
				// This tells jCarousel NOT to autobuild prev/next buttons
				buttonNextHTML: null,
				buttonPrevHTML: null
			});
			
			
			$('#ok_btn').show().unbind('click').click(function(){
				change_step(2);
			});
			step_en_cours=1;
			
		}
		else if(step_num==2){
			//alert(id_charte_cours);
			
			$('#text_aide_step2').html('Choisissez la déclinaison de couleurs de votre charte puis cliquez sur <b>OK</b>.');
			
			$('#btn_dir_2').removeClass('btn_style2').addClass('btn_style3').unbind('mouseenter').unbind('mouseleave').css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'});
			
			nb_decli=list_dim[id_charte_cours].length;
			
			var liste_html_decli='';
			for(i=0;i<nb_decli;i++){

				if(i%2==0)liste_html_decli+='<li>'; 
				
				liste_html_decli+='<img src="<? echo URL_DIR; ?>/admin/get-adaptative-thumb.php?url='+list_dim[id_charte_cours][i]['IMG']+'&width=90&height=70" width="90" height="70" onclick="javascript:change_charte(\''+list_dim[id_charte_cours][i]['IMG']+'\','+id_charte_cours+','+list_dim[id_charte_cours][i]['ID']+');" style="cursor:pointer;">';
				
				if(i%2==0)liste_html_decli+='<img src="<? echo URL_DIR; ?>/images/pix.gif" width="10" height="50" style="border:0px;">';
				else liste_html_decli+='</li>';
				
			}
			if(i%2==1)liste_html_decli+='</li>';
			

			
			$('#mycarousel,.jcarousel-skin-tango').remove();		
			$('#zone_carousel').prepend('<ul id="mycarousel" class="jcarousel jcarousel-skin-tango">'+liste_html_decli+'</ul>');
			
			if(nb_decli>6){
				$('#fleche').show();
			}else{
				$('#fleche').hide();
			}
			
			$("#mycarousel").jcarousel({
				vertical: true,
				scroll: 2,
				initCallback: mycarousel_initCallback,
				// This tells jCarousel NOT to autobuild prev/next buttons
				buttonNextHTML: null,
				buttonPrevHTML: null
			});
			$('#ok_btn').show().unbind('click').click(function(){
				valide_step_2();
			});
			step_en_cours=2;		
			
		}
		
	}
}



function valide_step_2(){
	if(id_decli_cours!=0 && id_home_cours!=0){
		$('#charte_form').val(id_decli_cours);
		$('#accueil_form').val(id_home_cours);
		document.forms["form_step2"].submit();
	}
	else alert('Veuillez sélectionner une charte graphique et le type de page d\'accueil de votre site');
}



function change_charte(url_visu,id_charte,id_decli){
	id_decli_cours=id_decli;
	id_charte_cours=id_charte;
	$('#step2_main').css({"background-image":'url("<? echo URL_DIR; ?>/admin/get-adaptative-thumb.php?url='+url_visu+'&width=450&height=352")'});	
	change_accueil(url_visu_cours,id_home_cours);
	
}
function change_accueil(url_visu,id_home){
	if(url_visu!=""){
		url_visu_cours=url_visu;
		id_home_cours=id_home;
		$('#accueil').attr({"src":'<?echo URL_DIR?>/admin/get-thumb.php?url='+url_visu+'&width=290&height=240'});
		var ajout=139;
		if(list_taille[id_decli_cours]['height']<185){
			ajout=ajout+(185-list_taille[id_decli_cours]['height']);
		}
		$('#home_preview').css({'margin-left':list_taille[id_decli_cours]['width']+'px','margin-top':list_taille[id_decli_cours]['height']+'px','height':ajout+'px'});
	}
}
 

<?
echo $list_decli;
echo $list_couleur;
?>
$(function(){

	$(".btn_style2,.btn_style3,.btn_style4").corner("5px").parent().css('padding', '1px').corner("5px");
	
	/*Etape 1*/
	$(".btn_style4").hover( function () {
		$(this).css({'background-color':'#E2E2E2'});
	},function(){
		$(this).css({'background-color':'#f8f8f8'});
	});
	
	$(".btn_style2").hover(function(){
		$(this).css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'});
	}, function (){
		$(this).css({'background-image':'none','color':'#686868'});
	});
		
	
	$('#ok_btn').click(function(){
		change_step(2);
	});
	
	<?
	if($id_decli_cours!=0){
	?>
	change_charte('<?echo $don_charte['THUMB'];?>',<?echo $don_charte['ID_TEMPLATE']?>,<?echo ID_CHARTE;?>);
	<?
	}
	if($id_home_cours!=0){
	?>
	change_accueil('<?echo $don_home['FULL'];?>',<?echo $don_home['ID_TEMPLATE'];?>);
	<?
	}
	?>
	
	jQuery("#mycarousel").jcarousel({
		vertical: true,
		scroll: 2,
		initCallback: mycarousel_initCallback,
		// This tells jCarousel NOT to autobuild prev/next buttons
		buttonNextHTML: null,
		buttonPrevHTML: null
	});
	
	<? echo auto_help("Aspect Graphique","<a href='#' id='titre_guide'>Voir le guide &quot;Aspect Graphique&quot;</a>",'titre_guide','faq_view.php?id_aide=33&id_cat=8');?>
	
	
});
</script>

<!--entete-->
<div style='float:left;width:600px;'>
	<h1 id="etape_title">Aspect Graphique</h1>
</div>
<div style='float:right;width:100px;text-align:right;'>
	<img src='<?echo URL_DIR;?>/images/install_step_2.jpg'>
</div>
<br class="clear"/>
<!--fin entete-->
<div>	
	<div id="step2_main">
		<div id="home_preview">
			<img src='<?echo URL_DIR;?>/images/pix.gif' id="accueil">
		</div>
	</div>
	<div id="step2_rgt" style="margin-left:15px;">
		<div style='width:100%;margin-top:5px;' id="text_aide_step2">
			Choisissez la charte graphique<br/>
			de votre site puis cliquez sur <b>OK</b>.
		</div>
		<!--ici le caroussel-->
		<div id="zone_carousel">
			<ul id="mycarousel" class="jcarousel jcarousel-skin-tango">
				<?
				echo stripslashes($list_charte_html);
				?>						
			</ul>
			<div style="float:left;width:100px;<?if($nb_charte<7){echo 'display:none;';}?>" id="fleche" ><a href="#" id="car_down"><img src="<?echo URL_DIR; ?>/images/btn_carousel_down.jpg"></a> <a href="#" id="car_up"><img src="<?echo URL_DIR; ?>/images/btn_carousel_up.jpg"></a></div>
			<div style="float:right;width:68px;">
				<div class="bordure_menu" style='width:66px;' id="ok_btn">
					<div style='width:66px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>OK</p></div>
				</div>
			</div>
		</div>
		<!--fin caroussel-->
	</div>
</div>
<div class="clear"></div>
<div style="margin-top:35px;">
	<div class="bordure_menu" style='width:170px;float:left;' id="btn_op_1">
		<div style='width:170px;' class="btn_style3" id="btn_dir_1" onclick="javascript:change_step(1);">
			<div class="btn_style2_lft"><img src='<?echo URL_DIR;?>/images/step1_btn.png'></div>
			<div class="btn_style2_rgt">Charte graphique</div>					
		</div>
	</div>	
	<div class="bordure_menu" style='width:170px;margin-left:30px;float:left;height:24px;' id="btn_op_2" onclick="javascript:change_step(2);">
		<div style='width:170px;' class="btn_style2" id="btn_dir_2">
			<div class="btn_style2_lft"><img src='<?echo URL_DIR;?>/images/step2_btn.png'></div>
			<div class="btn_style2_rgt">Couleurs</div>					
		</div>
	</div>		
	<div class="bordure_menu" style='width:200px;margin-left:30px;float:left;height:24px;' onclick="javascript:valide_step_2();">
		<div style='width:200px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Appliquer les changements</p></div>
	</div>
	<form method="post" name="form_step2" action="graphisme.php">
		<input type="hidden" name="charte_form" id="charte_form"><input type="hidden" name="accueil_form" id="accueil_form">
	</form>
</div>			

<?
include('footer.php');
?>