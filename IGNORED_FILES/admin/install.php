<?
$css_menu=1;
$nav_on=0;

if(isset($_GET['step']))$step=$_GET['step'];
else $step='1';

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

include('header.php');

if($step==2 && $action=='valide'){
	
	if($_POST['name_structure']!="" && $_POST['adress_structure']!="" && $_POST['cp_structure']!="" && $_POST['ville_structure']!="" && $_POST['pays_structure']!="" && $_POST['tel_structure']!="" && $_POST['email_structure']!="" && $_POST['nom_structure']!=""){
		
		set_c('WEBSITE_NAME',addslashes($_POST['name_structure']));
		set_c('SITE_ADRESS',addslashes($_POST['adress_structure']));
		set_c('ZIP_SITE',addslashes($_POST['cp_structure']));
		set_c('TOWN_SITE',addslashes($_POST['ville_structure']));
		set_c('COUNTRY_SITE',addslashes($_POST['pays_structure']));
		set_c('PHONE_SITE',addslashes($_POST['tel_structure']));
		set_c('EMAIL_SITE',addslashes($_POST['email_structure']));
		
		$_POST['nom_structure']=trim($_POST['nom_structure']);
		$pattern = '/^(.*) (.*)$/';
		preg_match($pattern,$_POST['nom_structure'],$matches);
		//pr($matches);
		if(count($matches)==3){
			$db->execute('UPDATE comm_admin_user SET `FNAME` = \''.addslashes($matches[1])."', `LNAME` = '".addslashes($matches[2]).'\' WHERE ID = '.$_SESSION['id']);
		}
		else{
			$db->execute('UPDATE comm_admin_user SET `FNAME` = \''.addslashes($matches[0])."' WHERE ID = ".$_SESSION['id']);
		}
		
		header('location:install.php?step=2');
		die();
		
	}else{
		$step=1;
	
	}
	
}elseif($step==3 && $action=="valide"){
	if(is_numeric($_POST['charte_form']) && is_numeric($_POST['accueil_form'])){
		$db->execute('UPDATE cms_homepage SET SELECTED = 0');
		$db->execute('UPDATE cms_homepage SET SELECTED = 1 WHERE ID_TEMPLATE = '.$_POST['accueil_form'].' AND ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' ORDER BY ID LIMIT 1');
		set_c('CHARTE',$_POST['charte_form']);
		header('location:install.php?step=3');
		die();
	}else $step=2;
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
		$list_charte[]='<img src="'.URL_DIR.'/admin/get-adaptative-thumb.php?url='.urlencode($name_small).'&width=90&height=70" width="90" height="70" alt="'.stripslashes($don['CHART_NAME']).'" onclick="javascript:change_charte(\\\''.$name_small.'\\\','.$don['ID_CHARTE'].','.$don['ID_DECLI'].');" style="cursor:pointer;">';	
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
	list_dim['.$don['ID_CHARTE'].']['.$num_decli.']["IMG"]=\''.$name_small.'\';
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
if($step==2 || $step==3){
	
	if(ID_CHARTE!=''){
		$info_charte=$db->query('select ID_TEMPLATE,THUMB from cms_charte_declinaison WHERE ID = '.ID_CHARTE);
		$don_charte=mysql_fetch_array($info_charte);
		$temp=pathinfo($don_charte['THUMB']);
		$name_small=$temp['dirname'].'/'.$temp['filename'].'_small.'.$temp['extension'];
		$id_decli_cours=ID_CHARTE;
		
		$req_homepage=$db->query("SELECT cms_homepage.ID_TEMPLATE,cms_template.FULL FROM `cms_homepage`,`cms_template` WHERE cms_homepage.ID_TEMPLATE=cms_template.ID AND cms_homepage.SELECTED = 1 AND cms_homepage.ID_LANG = ".$_SESSION['langue']." AND cms_homepage.ID_UNIVERS = ".$_SESSION['univers']);
		$don_home=mysql_fetch_array($req_homepage);
		$id_home_cours=$don_home['ID_TEMPLATE'];
	}else{
		$don_charte['ID_TEMPLATE']=0;
		$id_home_cours=0;
		$id_decli_cours=0;
	}
}

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
			$('#btn_dir_1').removeClass('btn_style2').addClass('btn_style3').unbind('mouseenter').unbind('mouseleave').css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'}) ;
			
			$('#text_aide_step2').html('Choisissez la charte graphique<br/>de votre site puis cliquez sur <b>OK</b>.');
			
			
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
			
			$('#btn_dir_2').removeClass('btn_style2').addClass('btn_style3').unbind('mouseenter').unbind('mouseleave').css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'});
			
			$('#text_aide_step2').html('Choisissez la déclinaison de couleurs de votre charte puis cliquez sur <b>OK</b>.');
			
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
				change_step(3);
			});
			step_en_cours=2;		
			
		}
		else if(step_num==3){
			
			$('#btn_dir_3').removeClass('btn_style2').addClass('btn_style3').unbind('mouseenter').unbind('mouseleave').css({'background-image':'url("<?echo URL_DIR;?>/images/bg_btn_step2.jpg")','color':'#FFFFFF'});
			
			$('#text_aide_step2').html('Choisissez le gabarit de page d\'accueil de votre site puis cliquez sur <b>OK</b>.');
			
			$('#mycarousel,.jcarousel-skin-tango').remove();		
			$('#zone_carousel').prepend('<ul id="mycarousel" class="jcarousel jcarousel-skin-tango"><?echo $list_home;?></ul>');
			
			if(nb_home>6){
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
			
			step_en_cours=3;
			
		}
	}
}

function valide_step_1(valide){

	var erreur_form=0;

	if($('#name_structure').val()==''){		
		$('#name_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#name_structure').css({"border":"1px solid #CCC"});
	}
	
	if($('#adress_structure').val()==''){		
		$('#adress_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#adress_structure').css({"border":"1px solid #CCC"});
	}
	
	var zip_code=$('#cp_structure').val();	
	
	if(zip_code.length!=5||isNaN(zip_code)){		
		$('#cp_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#cp_structure').css({"border":"1px solid #CCC"});
	}
	
	if($('#ville_structure').val()==''){		
		$('#ville_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#ville_structure').css({"border":"1px solid #CCC"});
	}
	
	if($('#pays_structure').val()==''){		
		$('#pays_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#pays_structure').css({"border":"1px solid #CCC"});
	}
	
	
	if($('#tel_structure').val()==''){		
		$('#tel_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#tel_structure').css({"border":"1px solid #CCC"});
	}
	
	var email=$('#email_structure').val();
	if(!VerifMail(email)){		
		$('#email_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#email_structure').css({"border":"1px solid #CCC"});
	}
	
	if($('#nom_structure').val()==''){		
		$('#nom_structure').css({"border":"1px solid #FF9900"});
		erreur_form=1;
	}else{
		$('#nom_structure').css({"border":"1px solid #CCC"});
	}
	
	
	if(valide && !erreur_form){
		$('#complete_all').hide();
		document.forms["form_step1"].submit();
	}
	else $('#complete_all').show();
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
	$('#step2_main').css({"background-image":'url("'+url_visu+'")'});	
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

	$("#content_block_install,#content_block_install2,#content_block_install3,.btn_style2,.btn_style3,.btn_style4").corner("5px").parent().css('padding', '1px').corner("5px");
	
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
		
	<?if($step!=3){?>$('#voir_votre_site').parent().hide();<?}?>
	$('#btn_op_4').hide();
	
	$('#ok_btn').click(function(){
		change_step(2);
	});
	
	<?
	if($id_decli_cours!=0){
	?>
	change_charte('<?echo $name_small;?>',<?echo $don_charte['ID_TEMPLATE']?>,<?echo ID_CHARTE;?>);
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
	
	// add a "rel" attrib if Opera 7+
    if(window.opera) {
        if ($("div.jqbookmark").attr("rel") != ""){ // don't overwrite the rel attrib if already set
            $("div.jqbookmark").attr("rel","sidebar");
        }
    }
   
    $("div.jqbookmark").click(function(event){
        event.preventDefault(); // prevent the anchor tag from sending the user off to the link
        var url = "<?echo URL_DIR;?>/admin/";
        var title = "Administration de votre site";
       
        if (window.sidebar) { // Mozilla Firefox Bookmark
            window.sidebar.addPanel(title, url, "");
        } else if( window.external ) { // IE Favorite
            window.external.AddFavorite( url, title);
        } else if(window.opera) { // Opera 7+
            return false; // do nothing - the rel="sidebar" should do the trick
        } else { // for Safari, Konq etc - browsers who do not support bookmarking scripts (that i could find anyway)
            alert('Malheuresement votre navigateur ne permet pas la mise en favori. Utiliser la barre d\'action de votre navigateur pour mettre en favori ce site');
        }
    });
	
});
</script>
<?
if($step==1){
?>
<div class="bordure_menu" style="margin-top:10px;">
	<div id="content_block_install">
		<!--entete-->
		<div style='float:left;width:600px;'>
			<h1 id="etape_title">Étape 1/2 : Coordonnées de votre structure</h1>
		</div>
		<div style='float:right;width:100px;text-align:right;'>
			<img src='<?echo URL_DIR;?>/images/install_step_1.jpg'>
		</div>
		<br class="clear"/>
		<!--fin entete-->
		<div style='width:860px;'>
			<div id="install_cl_left">
				<h2>Bienvenue</h2>
				<p>
				Vous êtes propriétaire d'un site Nexinet, nous vous proposons de le paramétrer simplement en 2 étapes.  
				<br/><br><b style="color:#FF9900;">Une hésitation ?</b><br/><br/>Vous pourrez revenir ultérieurement sur vos choix via votre Administration.
				</p>
			</div>
			<div id="install_cl_rgt">
				<div id="form_install" <?echo add_help('Etape 1','Veuillez vérifier les coordonnées que nous avons enregistrées.<br/>- si elles sont exactes cliquez sur <b>valider cette étape.</b><br>- sinon, merci de les rectifier et de les valider.');?>>
					<div>Nom de votre structure<form method="post" name="form_step1" action="?step=2&action=valide"></div>
					<div style="margin-top:15px;"><input type="text" style='width:629px;' name="name_structure" id="name_structure" value="<?echo format(get_c('WEBSITE_NAME',$_SESSION['langue'],$_SESSION['univers']));?>"></div>
					<div style="margin-top:10px;">Adresse complète</div>
					<div style="margin-top:10px;"><input type="text" style='width:629px;' name="adress_structure" id="adress_structure" value="<?echo format(get_c('SITE_ADRESS'));?>"></div>
					<div style="margin-top:10px;">
						<div style="float:left;width:219px;">Code postal</div>
						<div style="float:left;width:220px;">Ville</div>
						<div style="float:left;width:190px;">Pays</div>
					</div>	
					<div class="clear"></div>			
					<div class="margin_10">
						<div style="float:left;width:219px;"><input type="text" style='width:189px;' name="cp_structure" id="cp_structure" value="<?echo format(get_c('ZIP_SITE'));?>"></div>
						<div style="float:left;width:218px;"><input type="text" style='width:190px;' name="ville_structure" id="ville_structure" value="<?echo format(get_c('TOWN_SITE'));?>"></div>
						<div style="float:right;width:190px;"><input type="text" style='width:190px;' name="pays_structure" id="pays_structure" value="<?echo format(get_c('COUNTRY_SITE'));?>"></div>
					</div>					
					<div class="clear"></div>
					<div style="margin-top:10px;">
						<div style="float:left;width:219px;">Numéro de téléphone...</div>
						<div style="float:left;width:410px;">et email de contact pour l’internaute</div>
					</div>
					<div class="clear"></div>
					<div class="margin_20">
						<div style="float:left;width:219px;"><input type="text" style='width:189px;' name="tel_structure" id="tel_structure" value="<?echo format(get_c('PHONE_SITE'));?>"></div>
						<div style="float:right;width:408px;"><input type="text" style='width:408px;' name="email_structure" id="email_structure" value="<?echo format(get_c('EMAIL_SITE'));?>"></div>
					</div>
					<div class="clear"></div>
					<div style="margin-top:10px;">Nom de l'administrateur</div>
					<div style="margin-top:10px;"><input type="text" style='width:630px;' name="nom_structure" id="nom_structure" value="<?
					$req_user=$db->query('select * from cms_user where ID = '.$_SESSION['id']);
					$don_user=mysql_fetch_array($req_user);
					echo format($don_user['FNAME'].' '.$don_user['LNAME']);?>"></div>
					<div class="margin_40">
						&nbsp;
					</div>
					<div style="float:left;width:200px;">
						<div class="bordure_menu" style='width:170px;' onclick="javascript:valide_step_1(1);">
							<div style='width:170px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
						</div>
					</div>
					<div style='float:left;width:400px;display:none;' id="complete_all">
						<b style='color:#FF9900;'>Merci de compléter tous les champs</b>
					</div>
					</form>
				</div>
			</div>
		</div>
	</div>	
</div>
<?
}
elseif($step==2){
?>
<div class="bordure_menu" style="margin-top:10px;">
	<div id="content_block_install2">
		<!--entete-->
		<div style='float:left;width:600px;'>
			<h1 id="etape_title">Étape 2/2 : Un peu de design</h1>
		</div>
		<div style='float:right;width:100px;text-align:right;'>
			<img src='<?echo URL_DIR;?>/images/install_step_2.jpg'>
		</div>
		<br class="clear"/>
		<!--fin entete-->
		<div style='width:860px;margin-top:-10px;'>
			<div id="step2_lft">
				<div class="bordure_menu" style='width:170px;' id="btn_op_1">
					<div style='width:170px;' class="btn_style3" id="btn_dir_1" onclick="javascript:change_step(1);">
						<div class="btn_style2_lft"><img src='<?echo URL_DIR;?>/images/step1_btn.png'></div>
						<div class="btn_style2_rgt">Charte graphique</div>					
					</div>
				</div>				
				<div style='margin-top:15px;'></div>
				<div class="bordure_menu" style='width:170px;' id="btn_op_2" onclick="javascript:change_step(2);">
					<div style='width:170px;' class="btn_style2" id="btn_dir_2">
						<div class="btn_style2_lft"><img src='<?echo URL_DIR;?>/images/step2_btn.png'></div>
						<div class="btn_style2_rgt">Couleurs</div>					
					</div>
				</div>				
				<div class="bordure_menu" style='width:170px;margin-top:15px;' id="btn_op_3" onclick="javascript:change_step(3);">
					<div style='width:170px;' class="btn_style2" id="btn_dir_3">
						<div class="btn_style2_lft"><img src='<?echo URL_DIR;?>/images/step3_btn.png'></div>
						<div class="btn_style2_rgt">Page d'accueil</div>					
					</div>
				</div>				
				<div class="bordure_menu" style='width:170px;margin-top:15px;' id="btn_op_4">
					<div style='width:170px;' class="btn_style2" id="btn_dir_4">
						<div class="btn_style2_lft"><img src='<?echo URL_DIR;?>/images/step4_btn.png'></div>
						<div class="btn_style2_rgt">Image de fond</div>					
					</div>
				</div>				
				<div class="bordure_menu" style='width:170px;margin-top:15px;' onclick="javascript:valide_step_2();">
					<div style='width:170px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Valider cette étape</p></div>
				</div>
				<div class="bordure_menu" style='width:170px;margin-top:175px;' onclick="javascript:redir('install.php?step=1');">
					<div style='width:170px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
				</div>
				<form method="post" name="form_step2" action="?step=3&action=valide"><input type="hidden" name="charte_form" id="charte_form"><input type="hidden" name="accueil_form" id="accueil_form">
				</form>
			</div>
			<div id="step2_main">
				<div id="home_preview">
					<img src='<?echo URL_DIR;?>/images/pix.gif' id="accueil">
				</div>
			</div>
			<div id="step2_rgt">
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
	</div>
</div>
<?
}
elseif($step==3){
?>
<div class="bordure_menu" style="margin-top:10px;">
	<div id="content_block_install3">
		<!--entete-->
		<div style='float:left;width:600px;'>
			<h1 id="etape_title"></h1>
		</div>
		<div style='float:right;width:100px;text-align:right;'>
			<img src='<?echo URL_DIR;?>/images/install_step_3.jpg'>
		</div>
		<br class="clear"/>
		<!--fin entete-->
		<div style='width:860px;'>
			<div id="install_cl_left">	

				<img src="<?echo URL_DIR?>/admin/get-adaptative-thumb.php?url=<? echo urlencode($name_small);?>&width=170&height=110" style='border:1px solid #ccc;'>
				<img src="<?echo URL_DIR?>/admin/get-adaptative-thumb.php?url=<? echo urlencode($don_home['FULL']);?>&width=170&height=110" style="margin-top:10px;border:1px solid #ccc;">
				<div class="bordure_menu" style='width:170px;margin-top:83px;' onclick="javascript:redir('install.php?step=2');">
					<div style='width:170px;background-image:url("<?echo URL_DIR;?>/images/btn_prev.png");' class="btn_style"><p>Etape précédente</p></div>
				</div>
			</div>
			<div id="install_cl_rgt2" >
				<h2>Félicitations <?
				$req_user=$db->query('select * from cms_user where ID = '.$_SESSION['id']);
				$don_user=mysql_fetch_array($req_user);
				echo $don_user['FNAME'];
				?></h2>
				<div>
				Votre site est en ligne.<br/>
				Vous pouvez le consulter à cette adresse <a href="<?echo URL_DIR;?>/" target='_blank'><?echo URL_DIR;?>/</a><br/>
				ou en cliquant, en haut à droite, sur le bouton :
				</div>
				<div class="bordure_menu" style='width:100px;margin-top:15px;' onclick="javascript:popupWindow('<?echo URL_DIR;?>', 'Voir', 1024, 860, 1);">
					<div style='width:100px;' class="btn_style4"><p>Voir votre site</p></div>
				</div>
				<div style="margin-top:15px">
				Vous pouvez également...
				</div>
				<div class="bordure_menu" style='width:345px;margin-top:15px;' onclick="javascript:redir('home.php');" <?echo add_help("Administration avancée","Votre site dispose d’un <b>Gestionnaire de Contenu</b>, encore appelé <b>Administration</b>.<br/>Ce programme vous permet de gérer votre site internet et de mettre en ligne ou modifier du contenu, des images, des audios et des vidéos.<br/>Cet outil ne nécessite pas de compétences en informatique, il est très simple d’utilisation.");?>>
					<div style='width:345px;background-image:url("<?echo URL_DIR;?>/images/btn_crew.png");' class="btn_style"><p>Découvrir l'administration avancée du site</p></div>
				</div>
				<div class="bordure_menu" style='width:345px;margin-top:10px;' onclick="javascript:popupWindow('faq_view.php', 'Aide', 640, 480, 1);" <?echo add_help("Guides","Pour chaque étape vous êtes guidés par des bulles d’aides et guides qui s’ouvrent lorsque votre souris passent au dessus de la fonction.");?>>
					<div style='width:345px;background-image:url("<?echo URL_DIR;?>/images/btn_guide.png");' class="btn_style"><p>Parcourir les guides de l'administration</p></div>
				</div>
				<div class="jqbookmark bordure_menu" style='width:345px;margin-top:10px;'   <?echo add_help("Mettre en favori","Si vous le désirez et pour éviter de rechercher l’adresse de votre administration, nous vous proposons de rentrer cette adresse sous &quot;Administration de votre site&quot; dans les favoris votre navigateur.<br/>Cliquez sur le bouton pour ajouter à vos favoris");?>>
					<div style='width:345px;background-image:url("<?echo URL_DIR;?>/images/btn_star.png");' class="btn_style"><p>Ajouter l’administration à vos favoris internet</p></div>
				</div>
			</div>
		</div>
	</div>	
</div>
<?
}
include('footer.php');
?>