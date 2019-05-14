<?
$id_module=3;
require_once('../config/configuration.php');
$tree = new nestedTree($handle);
$walk = $tree->nstWalkPreorder($tree->nstRoot());
$select_list_cat='';
while($curr = $tree->nstWalkNext($walk)){
	if($walk['row']['TYPE']==0){
		$select_list_cat.='<option value="'.$walk['row']['ID'].'">'.str_repeat('. ',$walk['level']).htmlspecialchars($walk['row']['TITLE'],ENT_QUOTES,"UTF-8").'</option>';
	}
}
?>
<html>
<head>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.validate.js"></script>
<script type="text/javascript" src="<? echo URL_DIR;?>/js/jquery.metadata.js"></script>
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
	<form method="post" name="commentForm" id="commentForm" action="arborescence.php?action=add_r">
	
		<div>Nom de la catégorie :</div>
		<div style='margin-top:5px;'>
			<input name="titre" type="text" value='' class="{validate:{required:true, messages:{required:'Veuillez saisir un nom pour la catégorie'}}}" id="titre" style="width:80%;"/>
		</div>
		<div class="clear"></div>
		<div style='margin-top:10px;'>Je souhaite placer cette catégorie dans :</div>
		<div style='margin-top:5px;'>
		<select name="id_categorie_parent">
		<?
		echo $select_list_cat;
		?>
		</select>				
		</div>
		<div class="clear"></div>
		<div class="bordure_menu" style='width:175px;float:left;margin-top:10px;' onclick="javascript:valide_form();">
			<div style='width:175px;background-image:url("<?echo URL_DIR;?>/images/btn_add.png");' class="btn_style"><p>Ajouter une catégorie</p></div>
		</div>
	</form>
</div>
</body>
</html>