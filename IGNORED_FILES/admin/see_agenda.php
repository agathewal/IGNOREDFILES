<html><body>
<?
if(isset($_GET['email']) && $_GET['email']!=''){
	echo '<iframe src="https://www.google.com/calendar/embed?showTitle=0&amp;showCalendars=0&amp;height=600&amp;wkst=1&amp;bgcolor=%23FFFFFF&amp;src='.urlencode(urldecode($_GET['email'])).'&amp;color=%23182C57&amp;ctz=Europe%2FParis" style=" border-width:0 " width="800" height="600" frameborder="0" scrolling="no"></iframe>';
}
?>
</body></html>