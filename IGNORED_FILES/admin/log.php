<?
$id_module=1;
include('header.php');
$test=New Login();
$test->confirm_Member();	
?>
<h2>Journalisation</h2>
<br/><br/><?

?>
<?
	$req=$db->query("select * from cms_log order by ID DESC");

	if(mysql_num_rows($req)!=0){
		
		echo "
		<table cellpadding='0' cellspacing='0' border='0' width='100%'>
			<tr>
				<th>Module</th>
				
				<th>Date d'éxécution</th>
				<th>Texte</th>
			</tr>
		";
		while($don=mysql_fetch_array($req)){
			
			echo"
			<tr>
				<td>".$don['MODULE']."</td>
				<td>".date('Y-m-d H:i:s',$don['DT_EVENT'])."</td>
				<td>".$don['TEXT']."</td>
				
			</tr>
			";
		}
		
		echo "</table>";
	}

?>

<?
include('footer.php');
?>