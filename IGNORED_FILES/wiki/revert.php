<?php

$rev = urlencode($_GET['rev']);
if(!is_numeric($rev)) exit;

?>

<div class="header">Revert to revision <?php echo $rev; ?></div>

<input type="hidden" id="rev" name="rev" value="<?php echo $rev; ?>" />

  <div class="clear"></div>
<div class="labelvar">Revision Comment:</div>
<div class="field"><textarea id="revcomment" name="revcomment"></textarea></div>
<div style='clear:both;height:18px;'></div>
<div style="text-align:center;">
	<input type="submit" value="Update"/>
</div>

