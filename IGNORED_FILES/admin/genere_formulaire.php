<?
if($_GET['id']!=''){
	$chaine='<p>
	<br />
	<br />
	<iframe scrolling="auto" height="1201" frameborder="0" title="Tournoi" src="http://formulaire.tvuonline.net/form_admin/embed.php?id='.$_GET['id'].'" style="width: 100%; border: medium none;" allowtransparency="true"><a href="http://formulaire.tvuonline.net/form_admin/view.php?id='.$_GET['id'].'" title="Tournoi">Tournoi</a></iframe>
	</p>';
}
?>
<form method="get">
	<input type='text' name='id'> <input type='submit' value='ok'>
	<textarea style='width:80%;height:300px;'><?echo htmlentities($chaine,ENT_QUOTES,'UTF-8');?></textarea>
</form>