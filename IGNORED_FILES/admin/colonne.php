<?
$id_module=20;

if(isset($_GET['action']))$action=$_GET['action'];
else $action='';

if($action=='edit'){
	$ariane_element2['URL']="nav_footer.php";
	$ariane_element2['LIBELLE']="Modifier une colonne";					
}

include('header.php');



/*Modification*/
if($action=='edit_r'){
	
	/*Colonne de gauche*/
	$db->execute('DELETE FROM cms_column_menu where ID_MENU = '.$column_left['ID']);
	for($i=1;$i<=$_POST['compteur_lft'];$i++){
		$id_module=str_replace("table_lft_","",$_POST['ordre_menu_lft_'.$i]);
		$titre='';
		for($j=1;$j<=$_POST['compteur_lft'];$j++){
			if($_POST['menu_lft_'.$j]==$id_module){
				$titre=$_POST['libelle_lft_'.$j];
				break;
			}
		}	
		$db->execute('INSERT INTO cms_column_menu (ID_MENU,ID_MODULE,TITLE,ORDRE) VALUES ('.$column_left['ID'].','.$id_module.',\''.addslashes($titre).'\','.$i.') ');		
	}
	$db->execute('UPDATE cms_column SET ACTIVE = '.$_POST['stat_clf_lft'].' WHERE ID = '.$column_left['ID']);
	
	
	/*Colonne de droite*/
	$db->execute('DELETE FROM cms_column_menu where ID_MENU = '.$column_right['ID']);
	for($i=1;$i<=$_POST['compteur_rgt'];$i++){
		$id_module=str_replace("table_rgt_","",$_POST['ordre_menu_rgt_'.$i]);
		$titre='';
		for($j=1;$j<=$_POST['compteur_rgt'];$j++){
			if($_POST['menu_rgt_'.$j]==$id_module){
				$titre=$_POST['libelle_rgt_'.$j];
				break;
			}
		}	
		$db->execute('INSERT INTO cms_column_menu (ID_MENU,ID_MODULE,TITLE,ORDRE) VALUES ('.$column_right['ID'].','.$id_module.',\''.addslashes($titre).'\','.$i.') ');		
	}
	$db->execute('UPDATE cms_column SET ACTIVE = '.$_POST['stat_clf_rgt'].' WHERE ID = '.$column_right['ID']);
	
	
	
	$_SESSION['notification'][]=array(1,"Colonnes","Les colonnes ont été modifiées.");
	header('location:colonne.php');		
	die();
}


$nb_menu_actuel_lft=0;
$req=$db->query('select * from cms_column_menu where ID_MENU = '.$column_left['ID']." ORDER BY `ORDRE`");
$nb_menu_actuel_lft=mysql_num_rows($req);
$js_plus.='var total_menu_lft='.$nb_menu_actuel_lft.';';
$js_plus.='var stat_clf_lft='.LEFT_COLUMN.';';
$sortable_plus_lft='';

if($nb_menu_actuel_lft!=0){		
	while($don=mysql_fetch_array($req)){
		$don=array_map("format",$don);
		$js_plus.='
		purchased.push({id:'.$don['ID_MODULE'].',cnt:1,txt:\''.$don['TITLE'].'\',actif:0});';
		
		if($don['TITLE']==''){
			switch($don['ID_MODULE']){
				case 1:
				$don['TITLE']= "Arborescence (Pas de titre)";
				break;
				
				case 2:
				$don['TITLE'] = "Galeries photos (Pas de titre)";
				break;
				
				case 3:
				$don['TITLE'] = "Web TV (Pas de titre)";
				break;
			}
		}
			
			
		$sortable_plus_lft.='<li id="table_lft_'.$don['ID_MODULE'].'"><div class="bordure_menu" id="item_lft_'.$don['ID_MODULE'].'" style=\'width:350px;float:left;margin-top:20px;background-image:url("'.URL_DIR.'/images/fond_menu_selected.jpg");\'><div class="menu_style"><div class="menu_column_title" id="libelle_lft_'.$don['ID_MODULE'].'">'.$don['TITLE'].'</div><div class="zone_bouton_column"><a href="#liste_'.$don['ID_MODULE'].'" onclick="display_lft_input('.$don['ID_MODULE'].')"><img src=\''.URL_DIR.'/images/pencil.png\'></a><a href=\'#\' onclick="remove_lft('.$don['ID_MODULE'].');return false;"><img src=\''.URL_DIR.'/images/delete.png\' style=\'margin-left:5px;\'></a></div></div></div><div class="clear"></div></li>';
	
	}
}


$nb_menu_actuel_rgt=0;
$req=$db->query('select * from cms_column_menu where ID_MENU = '.$column_right['ID']." ORDER BY `ORDRE`");
$nb_menu_actuel_rgt=mysql_num_rows($req);
$js_plus.='var total_menu_rgt='.$nb_menu_actuel_rgt.';';
$js_plus.='var stat_clf_rgt='.RIGHT_COLUMN.';';
$sortable_plus_rgt='';

if($nb_menu_actuel_rgt!=0){		
	while($don=mysql_fetch_array($req)){
		$don=array_map("format",$don);
		$js_plus.='
		purchased_r.push({id:'.$don['ID_MODULE'].',cnt:1,txt:\''.$don['TITLE'].'\',actif:0});';
		
		if($don['TITLE']==''){
			switch($don['ID_MODULE']){
				case 1:
				$don['TITLE']= "Arborescence (Pas de titre)";
				break;
				
				case 2:
				$don['TITLE'] = "Galeries photos (Pas de titre)";
				break;
				
				case 3:
				$don['TITLE'] = "Web TV (Pas de titre)";
				break;
			}
		}	
		
		$sortable_plus_rgt.='<li id="table_rgt_'.$don['ID_MODULE'].'"><div class="bordure_menu" id="item_rgt_'.$don['ID_MODULE'].'" style=\'width:350px;float:left;margin-top:20px;background-image:url("'.URL_DIR.'/images/fond_menu_selected.jpg");\'><div class="menu_style"><div class="menu_column_title" id="libelle_rgt_'.$don['ID_MODULE'].'">'.$don['TITLE'].'</div><div class="zone_bouton_column"><a href="#liste_'.$don['ID_MODULE'].'" onclick="display_rgt_input('.$don['ID_MODULE'].')"><img src=\''.URL_DIR.'/images/pencil.png\'></a><a href=\'#\' onclick="remove_rgt('.$don['ID_MODULE'].');return false;"><img src=\''.URL_DIR.'/images/delete.png\' style=\'margin-left:5px;\'></a></div></div></div><div class="clear"></div></li>';
	
	}
}

?>
<script type="text/javascript" src="<?echo URL_DIR;?>/js/jquery-ui-1.8.5.custom.min.js"></script>
<link rel="stylesheet" href="<?echo URL_DIR;?>/css/column.css" type="text/css" media="screen" charset="utf-8" />
<script>
var purchased=new Array();
var purchased_r=new Array();
<?
echo $js_plus;
?>

function get_data(){

	var field_result='';
	var result_order=$('#menu_gauche').sortable('toArray');

	for(var i=1; i<result_order.length; i++){
		field_result+='<input type="hidden" name="ordre_menu_lft_'+i+'" value="'+result_order[i]+'">';
	}	
		
	var compteur=0;	
	for(var i=0; i<purchased.length;i++)
	{
		if(purchased[i].cnt>0)
		{
			compteur++;
			field_result+='<input type="hidden" name="menu_lft_'+compteur+'" value="'+purchased[i].id+'">';
			field_result+='<input type="hidden" name="libelle_lft_'+compteur+'" value="'+purchased[i].txt+'">';
			
		}
	}
	field_result+='<input type="hidden" name="compteur_lft" value="'+compteur+'">';
	field_result+='<input type="hidden" name="stat_clf_lft" value="'+stat_clf_lft+'">';
	
	var result_order=$('#menu_droite').sortable('toArray');

	for(var i=1; i<result_order.length; i++){
		field_result+='<input type="hidden" name="ordre_menu_rgt_'+i+'" value="'+result_order[i]+'">';
	}	
		
	var compteur=0;	
	for(var i=0; i<purchased_r.length;i++)
	{
		if(purchased_r[i].cnt>0)
		{
			compteur++;
			field_result+='<input type="hidden" name="menu_rgt_'+compteur+'" value="'+purchased_r[i].id+'">';
			field_result+='<input type="hidden" name="libelle_rgt_'+compteur+'" value="'+purchased_r[i].txt+'">';
			
		}
	}
	field_result+='<input type="hidden" name="compteur_rgt" value="'+compteur+'">';
	field_result+='<input type="hidden" name="stat_clf_rgt" value="'+stat_clf_rgt+'">';
	
	
	$('#form_user').append(field_result);
	//alert(field_result);
	setTimeout("submit_form()",1000);
}

function submit_form(){
	document.forms['form_user'].submit();
}

function toggle_stat_cl(cl){
	if(cl=='lft'){
		if(stat_clf_lft){
			stat_clf_lft=0;
			$('#btn_stat_lft').html('<p>Activer</p>').css({'background-image':'url("<?echo URL_DIR;?>/images/btn_check.png")'});
		}else{
			stat_clf_lft=1;
			$('#btn_stat_lft').html('<p>Désactiver</p>').css({'background-image':'url("<?echo URL_DIR;?>/images/btn_delete.png")'});
		}		
	}else{
		if(stat_clf_rgt){
			stat_clf_rgt=0;
			$('#btn_stat_rgt').html('<p>Activer</p>').css({'background-image':'url("<?echo URL_DIR;?>/images/btn_check.png")'});
		}else{
			stat_clf_rgt=1;
			$('#btn_stat_rgt').html('<p>Désactiver</p>').css({'background-image':'url("<?echo URL_DIR;?>/images/btn_delete.png")'});
		}
	}
}

function addlist_left(titre,id_menu)
{
	
	var msg ={id:id_menu,txt:titre,actif:0};
	var check=false;
	var cnt = false;
	
	for(var i=0; i<purchased.length;i++)
	{
		if(purchased[i].id==msg.id)
		{
			check=true;
			cnt=purchased[i].cnt;			
			break;
		}
	}	
	
	if(!cnt){
		
		$('#help_lft').hide();
		
		$('#menu_gauche').append('<li id="table_lft_'+msg.id+'"><div class="bordure_menu" id="item_lft_'+msg.id+'" style=\'width:350px;float:left;margin-top:20px;background-image:url("<?echo URL_DIR;?>/images/fond_menu_selected.jpg");\'><div class="menu_style"><div class="menu_column_title" id="libelle_lft_'+msg.id+'">'+msg.txt+'</div><div class="zone_bouton_column"><a href="#liste_'+msg.id+'" onclick="display_lft_input('+msg.id+')"><img src=\'<?echo URL_DIR;?>/images/pencil.png\'></a><a href=\'#\' onclick="remove_lft('+msg.id+');return false;"><img src=\'<?echo URL_DIR;?>/images/delete.png\' style=\'margin-left:5px;\'></a></div></div></div><div class="clear"></div></li>');
		
		$('#table_lft_'+msg.id+' .menu_style').corner("5px").parent().css('padding', '5px').corner("5px");
				
		if(!$.browser.msie || $.browser.version!="6.0"){
			$('#table_lft_'+msg.id+' .zone_bouton_column').hide();
			$('#table_lft_'+msg.id).mouseover(function(){
				$(this).find('.zone_bouton_column').show();
			}).mouseout(function(){
				$(this).find('.zone_bouton_column').hide();
			});
		}
	
	}
		
		
	if(!check)
	{
		purchased.push({id:msg.id,cnt:1,txt:msg.txt,actif:0});
		total_menu_lft++;
	}
	else
	{
		if(!cnt){
			var i=findpos_lft(id_menu);
			total_menu_lft++;
			purchased[i].cnt=1;
		}
		return false;
	}	
}

function display_lft_input(id_display){
	var i=findpos_lft(id_display);
	if(!purchased[i].actif){
		purchased[i].actif=1;
		var text=purchased[i].txt;
		$('#libelle_lft_'+id_display).html('<input type="text" value="'+text+'" name="text_value_lft_'+id_display+'" id="text_value_lft_'+id_display+'" style="width:280px;">');
	}else{
		purchased[i].actif=0;
		var nouvo_text=$('#text_value_lft_'+id_display).val();
		purchased[i].txt=nouvo_text;
		if(nouvo_text==''){
			if(purchased[i].id==1){
				nouvo_text="Arborescence (Pas de titre)";
			}
			else if(purchased[i].id==2){
				nouvo_text="Galeries photos (Pas de titre)";
			}
			else if(purchased[i].id==3){
				nouvo_text="Web TV (Pas de titre)";
			}
		}
		
		$('#libelle_lft_'+id_display).html(nouvo_text);
		
	}
}

function display_rgt_input(id_display){
	var i=findpos_rgt(id_display);
	if(!purchased_r[i].actif){
		purchased_r[i].actif=1;
		var text=purchased_r[i].txt;
		$('#libelle_rgt_'+id_display).html('<input type="text" value="'+text+'" name="text_value_rgt_'+id_display+'" id="text_value_rgt_'+id_display+'" style="width:280px;">');
	}else{
		purchased_r[i].actif=0;
		var nouvo_text=$('#text_value_rgt_'+id_display).val();
		purchased_r[i].txt=nouvo_text;
		if(nouvo_text==''){
			if(purchased_r[i].id==1){
				nouvo_text="Arborescence (Pas de titre)";
			}
			else if(purchased_r[i].id==2){
				nouvo_text="Galeries photos (Pas de titre)";
			}
			else if(purchased_r[i].id==3){
				nouvo_text="Web TV (Pas de titre)";
			}
		}		
		$('#libelle_rgt_'+id_display).html(nouvo_text);
		
	}
}

function addlist_right(titre,id_menu)
{
	
	var msg ={id:id_menu,txt:titre,actif:0};
	var check=false;
	var cnt = false;
	
	for(var i=0; i<purchased_r.length;i++)
	{
		if(purchased_r[i].id==msg.id)
		{
			check=true;
			cnt=purchased_r[i].cnt;			
			break;
		}
	}	
	
	if(!cnt){
		
		$('#help_rgt').hide();
		
		$('#menu_droite').append('<li id="table_rgt_'+msg.id+'"><div class="bordure_menu" id="item_rgt_'+msg.id+'" style=\'width:350px;float:left;margin-top:20px;background-image:url("<?echo URL_DIR;?>/images/fond_menu_selected.jpg");\'><div class="menu_style"><div class="menu_column_title" id="libelle_rgt_'+msg.id+'">'+msg.txt+'</div><div class="zone_bouton_column"><a href="#liste_'+msg.id+'" onclick="display_rgt_input('+msg.id+')"><img src=\'<?echo URL_DIR;?>/images/pencil.png\'></a><a href=\'#\' onclick="remove_rgt('+msg.id+');return false;"><img src=\'<?echo URL_DIR;?>/images/delete.png\' style=\'margin-left:5px;\'></a></div></div></div><div class="clear"></div></li>');
		
		$('#table_rgt_'+msg.id+' .menu_style').corner("5px").parent().css('padding', '5px').corner("5px");
		if(!$.browser.msie || $.browser.version!="6.0"){
			$('#table_rgt_'+msg.id+' .zone_bouton_column').hide();
			$('#table_rgt_'+msg.id).mouseover(function(){
				$(this).find('.zone_bouton_column').show();
			}).mouseout(function(){
				$(this).find('.zone_bouton_column').hide();
			});
		}
	}
		
		
	if(!check)
	{
		purchased_r.push({id:msg.id,cnt:1,txt:msg.txt,actif:0});
		total_menu_rgt++;
	}
	else
	{
		if(!cnt){
			var i=findpos_rgt(id_menu);
			total_menu_rgt++;
			purchased_r[i].cnt=1;
		}
		return false;
	}	
}


function findpos_lft(id)
{
	for(var i=0; i<purchased.length;i++)
	{
		if(purchased[i].id==id)
			return i;
	}
	
	return false;
}

function findpos_rgt(id)
{
	for(var i=0; i<purchased_r.length;i++)
	{
		if(purchased_r[i].id==id)
			return i;
	}
	
	return false;
}

function remove_lft(id)
{
	var i=findpos_lft(id);
	purchased[i].cnt = 0;
	purchased[i].actif = 0;
	total_menu_lft--;	
	if(total_menu_lft==0)$('#help_lft').show();
	$('#table_lft_'+id).remove();

}

function remove_rgt(id)
{
	var i=findpos_rgt(id);
	purchased_r[i].cnt = 0;
	purchased_r[i].actif = 0;
	total_menu_rgt--;
	if(total_menu_rgt==0)$('#help_rgt').show();
	$('#table_rgt_'+id).remove();

}

jQuery(document).ready(function(){
	<?
	echo auto_help("Contenu de votre colonne","Choisissez l'un des Menus Fonctionnels suivants<br><a href='#' id='titre_guide'>Voir guide &quot;Menus des colonnes&quot;</a><br>et <b>glissez</b> le dans la colonne souhaitée puis <b>Sauvegardez</b>",'titre_guide','faq_view.php?id_aide=28&id_cat=8');
	?>
	
	
	$('.menu_style').corner("5px").parent().css('padding', '5px').corner("5px");
	
	if(!$.browser.msie || $.browser.version!="6.0"){
		$('.zone_bouton_column').hide();
		$('#menu_gauche li,#menu_droite li').mouseover(function(){
			$(this).find('.zone_bouton_column').show();
		}).mouseout(function(){
			$(this).find('.zone_bouton_column').hide();
		});
	}
	
	$( "#menu_gauche,#menu_droite" ).sortable();	
	$(".sel_prod").draggable({	
		containment: 'document',
		opacity: 0.6,
		revert: 'invalid',
		helper: 'clone',
		zIndex: 100
	
	});

	$("#div_menu_gauche").droppable({	
		drop:
		function(e, ui)
		{					
			//alert('lalala');
			var id_menu = $(ui.draggable).attr("id");
			if(is_numeric(id_menu)){				
				var param = $('#text_'+id_menu).html();
				addlist_left(param,id_menu);						
			}
		}	
	});
	
	$("#div_menu_droite").droppable({	
		drop:
		function(e, ui)
		{					
			//alert('lolo');
			var id_menu = $(ui.draggable).attr("id");

			if(is_numeric(id_menu)){
				
				var param = $('#text_'+id_menu).html();
				addlist_right(param,id_menu);						
			}
		}	
	});

});
</script>
<form method="post"  id="form_user" action="colonne.php?action=edit_r">
</form>
<div id="form_admin">
	<div style='float:left;width:605px;'><h1 id="etape_name">Gestion des colonnes</h1></div>
	<div style='float:right;width:50px;'><img src='<?echo URL_DIR;?>/images/btn_article_add.jpg'></div>
</div>
<div class="clear"></div>
<div style="float:left;width:385px;">
	<div style="border:1px solid #CCCCCC;width:380px;" id="div_menu_gauche">
		<div style="margin:10px;">
			<div style="float:left;">
				<img src="<? echo URL_DIR;?>/images/lft_column.jpg">
			</div>
			<div style="float:left;margin-left:10px;font-size:16px;">
				Colonne de gauche
			</div>
			<div class="bordure_menu" style='width:110px;float:right;' onclick="javascript:toggle_stat_cl('lft');">
				<?if (LEFT_COLUMN){?><div style='width:110px;background-image:url("<?echo URL_DIR;?>/images/btn_delete.png");' class="btn_style" id="btn_stat_lft"><p>Désactiver</p></div>
				<?} else{?><div style='width:110px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style" id="btn_stat_lft"><p>Activer</p></div>
				<?}?>
			</div>
			<div class="clear"></div>			
			<ul id="menu_gauche">
				<li id="help_lft" <?if($nb_menu_actuel_lft!=0){echo 'style="display:none;"';}?>>
					<div class="bordure_menu" style='width:350px;float:left;margin-top:20px;background-image:url("<?echo URL_DIR;?>/images/fond_menu_selected.jpg");'>
						<div class="menu_style">
							<div  class="menu_column_title">
								Glisser ici un des éléments
							</div>							
						</div>
					</div>
					<div class="clear"></div>
				</li>	
				<?echo $sortable_plus_lft;?>
			</ul>
		</div>		
	</div>
	<div class='clear'></div>
	<div style="border:1px solid #CCCCCC;width:380px;margin-top:20px;" id="div_menu_droite">
		<div style="margin:10px;">
			<div style="float:left;">
				<img src="<? echo URL_DIR;?>/images/rgt_column.jpg">
			</div>
			<div style="float:left;margin-left:10px;font-size:16px;">
				Colonne de droite
			</div>
			<div class="bordure_menu" style='width:110px;float:right;' onclick="javascript:toggle_stat_cl('rgt');">
				<?if (RIGHT_COLUMN){?><div style='width:110px;background-image:url("<?echo URL_DIR;?>/images/btn_delete.png");' class="btn_style" id="btn_stat_rgt"><p>Désactiver</p></div><?} else{?><div style='width:110px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style" id="btn_stat_rgt"><p>Activer</p></div>
				<?}?>
			</div>
			<div class="clear"></div>			
			<ul id="menu_droite">
				<li id="help_rgt" <?if($nb_menu_actuel_rgt!=0){echo 'style="display:none;"';}?>>
					<div class="bordure_menu" style='width:350px;float:left;margin-top:20px;background-image:url("<?echo URL_DIR;?>/images/fond_menu_selected.jpg");'>
						<div class="menu_style">
							<div  class="menu_column_title">
								Glisser ici un des éléments
							</div>							
						</div>
					</div>
					<div class="clear"></div>
				</li>
				<?echo $sortable_plus_rgt;?>
			</ul>
		</div>		
	</div>
</div>
<div style="float:left;width:252px;margin-left:20px;">
	<div class="bordure_menu" style='width:250px;float:left;height:40px;display:inline;'>
		<div style='width:250px;height:40px;background-image:url("<?echo URL_DIR;?>/images/btn_hand.png");' class="btn_style"><p>Faites glisser les éléments dans<br>vos colonnes</p></div>
	</div>	
	<div class="clear"></div>
	<?
	$req=$db->query('select * from cms_column_module where ID_LANG = '.$_SESSION['langue'].' AND ID_UNIVERS = '.$_SESSION['univers'].' ORDER BY `ORDRE`');
	while($don=mysql_fetch_array($req)){
	?>
	<div class="sel_prod bordure_menu" style='width:230px;float:left;margin-top:20px;margin-left:5px;background-color:#d7d7d7;display:inline;' id="<?echo $don['ID'];?>">
		<div class="menu_style" style="height:auto;">			
			<p class="menu_add_title" id="text_<?echo $don['ID'];?>"><?echo stripslashes($don['TITLE']);?></p>
			<p class="menu_add_desc"><?			
			echo stripslashes($don['DESCRIPTION']);
			?></p>	
		</div>
	</div>
	<div class="clear"></div>
	<?
	}
	?>   
	<div style="background-color:#cccccc;width:100%;height:1px;margin-top:20px;"><img src="<?echo URL_DIR;?>/images/pix.gif"></div>	
	<div class="clear"></div>
	<div style="margin-top:20px;">
		<div class="bordure_menu" style='width:250px;float:left;' onclick="javascript:get_data();">
			<div style='width:250px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>Sauvegarder</p></div>
		</div>
	</div>
</div>
<?
include('footer.php');
?>