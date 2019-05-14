<?php
	$group_id = $_GET['group_id'];
	$atid = $_GET['atid'];
	$feed = "http://sourceforge.net/tracker/?func=rssfeed&group_id=$group_id&atid=$atid";
	
	$content = file_get_contents($feed);  
	$x = new SimpleXmlElement($content);  
  
	echo "<table class='sf-tracker-table'>";  
	echo "<th class='sf-tracker-th'>Ticket Number</th><th class='sf-tracker-th'>Description</th><th class='sf-tracker-th'>Status</th>";  
	echo "<th class='sf-tracker-th'>Date Submitted</th><th class='sf-tracker-th'>Resolution</th><th class='sf-tracker-th'>Priority</th>";  
  
	foreach($x->channel->item as $entry) { 
		echo "<tr>"; 
		$cont = $entry->children('http://purl.org/rss/1.0/modules/content/');
		$encoded = $cont->encoded;
		$title = explode("-",$entry->title);
		$ticket = trim($title[0]);
		$description = trim($title[1]);
		
		$status = subcontent($encoded, "Status");
		$class_status = $status == "Open" ? "sf-tracker-td-open" : "sf-tracker-td-closed";
		$submitted = subcontent($encoded, "Submitted Date");
		$resolution = subcontent($encoded, "Resolution");
		$priority = subcontent($encoded, "Priority");
		

		echo "<td class='sf-tracker-td'><a class='extlink' href='$entry->link' title='$entry->title'>" . $ticket . "</a></td>";  
        	echo "<td class='sf-tracker-td'>$description</td>";  
        	echo "<td class='$class_status'>$status</td>";  
        	echo "<td class='sf-tracker-td'>$submitted</td>";  
        	echo "<td class='sf-tracker-td'>$resolution</td>";  
        	echo "<td class='sf-tracker-td'>$priority</td>";  
		
		echo "</tr>";
    }  
    echo "</table>";  
	
	function subcontent($en,$item){
		$pattern = '/'.$item.': ([^<]+)</';
		preg_match($pattern, $en, $matches);
		return $matches[1];
	}
	
?>
