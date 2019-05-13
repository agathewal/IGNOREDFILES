<?php
require_once '../config/configuration.php';

if(isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['key']) && strlen($_GET['key'])==8){
	
	if($info_user=get_user_data($_GET['id'])){
	
		$chain=$info_user['USERNAME'].EVENT_ICS_SECRET_KEY.$_GET['id'];
		$chain=substr(sha1($chain),0,8);
	
		if($_GET['key']==$chain){
			
			require_once 'iCalcreator.class.php';
			
			$v = new vcalendar( array( 'unique_id' => $_SERVER['SERVER_NAME'] ));// initiate new CALENDAR
			$v->setProperty( 'X-WR-CALNAME', 'Calendrier '.WEBSITE_NAME );
			$v->setProperty( 'X-WR-CALDESC', 'Calendrier '.WEBSITE_NAME );
			$v->setProperty( 'X-WR-TIMEZONE', 'Europe/Paris' );

			if(isset($_GET['idp']) && is_numeric($_GET['idp'])){
			
				$req=$db->query('SELECT NAME,NOTE,DEADLINE,ACHEVIED,PRIOR FROM comm_task INNER JOIN comm_task_user ON comm_task_user.ID_TASK  = comm_task.ID INNER JOIN comm_group_task ON comm_group_task.ID_TASK = comm_task.ID_TASK_LIST WHERE comm_task_user.`ID_USER` = '.$_GET['id'].' AND DEADLINE > \''.date('Y-m-d 00:00:00').'\' AND DEADLINE != \'0000-00-00 00:00:00\' AND comm_group_task.ID_GROUP = '.$_GET['idp'].' GROUP BY comm_task.ID');

				
			}else{
				$req=$db->query('SELECT NAME,NOTE,DEADLINE,ACHEVIED,PRIOR FROM comm_task INNER JOIN comm_task_user ON comm_task_user.ID_TASK  = comm_task.ID WHERE comm_task_user.`ID_USER` = '.$_GET['id'].' AND DEADLINE > \''.date('Y-m-d 00:00:00').'\' AND DEADLINE != \'0000-00-00 00:00:00\' GROUP BY comm_task.ID');
			}
			
			
			if(mysql_num_rows($req)!=0){
				
				while($don=mysql_fetch_array($req)){
					
					//pr($don);
					$don=array_map('stripslashes',$don);
					
					$tmp_deb=explode(' ',$don['DEADLINE']);
					$date_deb=explode('-',$tmp_deb[0]);
					$heure_deb=explode(':',$tmp_deb[1]);			
					
					$e = & $v->newComponent( "vevent" ); // initialiser un nouveau to don
					$e->setProperty( 'summary', $don['NAME'] );
					$e->setProperty( 'dtstart',  $date_deb[0], $date_deb[1], $date_deb[2], $heure_deb[0], $heure_deb[1], $heure_deb[2] ); 
					$e->setProperty( 'description', $don['NOTE'] );
					if($don['ACHEVIED']==1)$status="COMPLETED";
					else $status="NEEDS-ACTION";
					$e->setProperty( 'status', $status );
					
					/*if($don['PRIOR']==1)$prior=1;
					else $prior=0;
					$e->setProperty( "priority", $prior );*/
					
				}
				//pr($info_user);
			}

			$v->returnCalendar();  
		}
	}
}


?>