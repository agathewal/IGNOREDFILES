	<?if($nav_on==1){?>	
			
			</div>
		</div>
		<!--end content-->
	</div>
	<!--end main-->
	<div class="clear"></div>
		<?if ($no_menu!=1){?>
		<div class="bordure_menu" style="width:100px;margin-top:8px;float:right;">
			<div id="voir_votre_site2">
				<a href="<?echo URL_DIR;?>" target="_blank">Voir votre site</a>
			</div>
		</div>
		<?}?>
	<?}?>
</div>

<script type="text/javascript">
	Cufon.replace('#nav h3, #ariane span, #header_cms h1, #zone_login h1, #install_cl_left h2, #install_cl_rgt2 h2, .titre_section_home, .intro_home h2, .intro_module h1', { fontFamily: 'eurofurence', hover:true});
	Cufon.now();
</script>
<?
$nb_notifs=count($_SESSION['notification']);
if($nb_notifs!=0){
//pr($_SESSION);
?>
<script>
	$(document).ready(function(){
		<?for($i=0;$i<$nb_notifs;$i++){?>
		$.notifier.broadcast({
			ttl:"<? echo $_SESSION['notification'][$i][1];?>", 
			msg:"<? echo $_SESSION['notification'][$i][2];?>",
			skin:'base,rounded,<?
			switch($_SESSION['notification'][$i][0]){
				case 0: 
				echo'red';
				break;
				
				case 1: 
				echo'green';
				break;
			}
			?>',
			duration:5000
		});	
		
		<?}?>
	});
</script>
<?
$_SESSION['notification']=array();
}?>
</body>
</html>
<?
ob_flush();
?>