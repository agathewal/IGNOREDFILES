<?
$id_module=24;
include('header.php');
?>

<form method="post" name="stats" action="<?echo STATS_DIR;?>/stats/">
	<input type='hidden' name="form_login" value="<? echo get_c('LOGIN_STAT');?>">
	<input type='hidden' name="form_password" value="<? echo get_c('MDP_STAT');?>">
	<input type='hidden' name="direct_log" value="1">
</form>
<script>
document.forms['stats'].submit();
</script>
<?
include('footer.php');
?>