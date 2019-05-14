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

				/*$req=$db->query('SELECT comm_event.ID,NAME,DATE_START,DATE_END,DT_CREATE,LOCATION,`DESC` FROM comm_event INNER JOIN comm_group_event ON comm_event.ID = comm_group_event.ID_EVENT WHERE comm_group_event.`ID_GROUP` = '.$_GET['idp'].' AND comm_event.DATE_END > \''.date('Y-m-d 00:00:00').'\' GROUP BY ID_EVENT_PARENT ');*/
				$req=$db->query('SELECT comm_event.ID,NAME,DATE_START,DATE_END,DT_CREATE,LOCATION,`DESC` FROM comm_event INNER JOIN comm_group_event ON comm_event.ID = comm_group_event.ID_EVENT WHERE comm_group_event.`ID_GROUP` = '.$_GET['idp'].' AND comm_event.DATE_END > \''.date('Y-m-d 00:00:00').'\'');
				
			}else{
			
				$req=$db->query('(SELECT comm_event.NAME,DATE_START,DATE_END,LOCATION,`DESC` FROM comm_event INNER JOIN comm_event_participants ON comm_event.ID = comm_event_participants.ID_EVENT WHERE comm_event_participants.`ID_USER` = '.$info_user['ID'].' AND VALIDATE = 1 AND DATE_END > \''.date('Y-m-d 00:00:00').'\') UNION (SELECT comm_event.NAME,DATE_START,DATE_END,LOCATION,`DESC` FROM comm_event WHERE ID_CREATOR = '.$info_user['ID'].' AND DATE_END > \''.date('Y-m-d 00:00:00').'\')');
			}
			
			
			if(mysql_num_rows($req)!=0){
				
				while($don=mysql_fetch_array($req)){
					
					//pr($don);
					$don=array_map('stripslashes',$don);
					$tmp_deb=explode(' ',$don['DATE_START']);
					$date_deb=explode('-',$tmp_deb[0]);
					$heure_deb=explode(':',$tmp_deb[1]);			
					
					$e = & $v->newComponent( 'vevent' );           // initiate a new EVENT
					$e->setProperty( 'summary', $don['NAME'] );
					$e->setProperty( 'dtstart',  $date_deb[0], $date_deb[1], $date_deb[2], $heure_deb[0], $heure_deb[1], $heure_deb[2] ); 
					if($don['DATE_END']!='0000-00-00 00:00:00'){		
						$tmp_fin=explode(' ',$don['DATE_END']);
						$date_fin=explode('-',$tmp_fin[0]);
						$heure_fin=explode(':',$tmp_fin[1]);
						$e->setProperty( 'dtend',  $date_fin[0], $date_fin[1], $date_fin[2], $heure_fin[0], $heure_fin[1], $heure_fin[2]); 
					}
					$e->setProperty( 'description', $don['DESC'] );   
					$e->setProperty( 'location', $don['LOCATION'] );  
					
				}
				//pr($info_user);
			}

			$v->returnCalendar();  
		}
	}
}


?>