<?
$id_module=3;
require_once('../config/configuration.php');
$tree = new nestedTree($handle);
$walk = $tree->nstWalkPreorder($tree->nstRoot());
$select_list_cat='';
$select_list_art='';
while($curr = $tree->nstWalkNext($walk)){
	if($walk['row']['TYPE']==0){
		$select_list_cat.='<option value="'.$walk['row']['ID'].'">'.str_repeat('. ',$walk['level']).htmlspecialchars($walk['row']['TITLE'],ENT_QUOTES,"UTF-8").'</option>';
	}
}

$req_art=$db->query('SELECT * FROM cms_article ORDER BY TITLE');
while($don=mysql_fetch_array($req_art)){
	$select_list_art.='<option value="'.$don['ID'].'">'.$don['TITLE'].'</option>';
}
?>
<html>
<head>
<script type="text/javascript">
$(document).ready(function() {
	$(".btn_style").hover( function () {
		$(this).css({'background-color':'#E2E2E2'});
	},function(){
		$(this).css({'background-color':'#f8f8f8'});
	});
	$(".btn_style").corner("5px").parent().css('padding', '1px').corner("5px");
});
function valide_form(){
	document.forms["commentForm"].submit();
}
</script>
</head>
<body>
<div id="form_ajax">
	<form method="post" name="commentForm" action="arborescence.php?action=add_art_r">
	
	<div>Article choisi :</div>
	<div class="clear"></div>
	<div style='margin-top:5px;'>
		<select name="id_art">
		<?
		echo $select_list_art;
		?>
		</select>
	</div>		
	<div class="clear"></div>
	<div style='margin-top:10px;'>Je souhaite placer cet article dans :</div>
	<div class="clear"></div>
	<div style='margin-top:5px;'>
		<select name="id_categorie_parent">
		<?
		echo $select_list_cat;
		?>
		</select>				
	</div>
	<div class="clear"></div>
	<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="javascript:valide_form();">
		<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style"><p>Ajouter l'article</p></div>
	</div>	
	</form>
</div>
</body>
</html>