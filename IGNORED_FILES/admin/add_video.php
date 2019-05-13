<?
$id_module=14;
require_once('../config/configuration.php');
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
<div >
	<form method="post" action="?action=add" id="commentForm">
	<div style="margin-top:10px;">Adresse de la vidéo :</div>
	<div style="margin-top:5px;">
		<input name="url_video" id="url_video" type="text" value='' style="width:80%;"/>		
	</div>	
	<div class="clear"></div>
	<div style="margin-top:10px;">
		<div class="bordure_menu" style='width:100px;' onclick="javascript:valide_form();">
			<div style='width:100px;background-image:url("<?echo URL_DIR;?>/images/btn_check.png");' class="btn_style"><p>OK</p></div>
		</div>
	</div>
	</form>
</div>
</body>
</html>