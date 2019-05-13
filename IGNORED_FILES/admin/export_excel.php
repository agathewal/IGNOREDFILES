<?
$id_module=16;
include('../config/configuration.php');
if(isset($_GET['action']))$action=$_GET['action'];
else $action='';


if($action=="export_xls" && isset($_GET['id']) && is_numeric($_GET['id'])){

	$req=$db->query('select * from cms_formulaire where ID = '.$_GET['id']);
	if(mysql_num_rows($req)!=0){
	
		$form_info=mysql_fetch_array($req);	
	
		$req_elements=$db->query('select * from cms_formulaire_champ where ID_FORM = '.$_GET['id'].' ORDER BY `ORDRE`');
		$list_elements=array();
		
		if(mysql_num_rows($req_elements)>0){
			
			include(ROOT_DIR."config/PHPExcel.php");	
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setTitle(stripslashes($form_info['NAME']));			
			$objPHPExcel->setActiveSheetIndex(0);
			
			$objPHPExcel->getActiveSheet()->setCellValue('A2', "first line\nsecond line");
			$objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		
			$element_i=0;
			while($don=mysql_fetch_array($req_elements)){
				$list_elements[]=$don;	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($element_i+65).'1', stripslashes($don['NAME']));
				$element_i++;
			}		
			
			$req=$db->query('SELECT * FROM cms_formulaire_data_'.$_GET['id'].' ORDER BY ID ASC');
			if(mysql_num_rows($req)!=0){
				$lettre_i=2;
				while($don=mysql_fetch_array($req)){
					$don=array_map('stripslashes',$don);
					
					//$lettre_en_cours=chr(($lettre_i+65));
					
					for($i=0;$i<count($list_elements);$i++){
					
						$lettre_en_cours=chr($i+65);
						
						
						
						switch($list_elements[$i]['TYPE']){
						
							case 1:
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']], PHPExcel_Cell_DataType::TYPE_STRING);							
							break;
							
							case 2:
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']], PHPExcel_Cell_DataType::TYPE_STRING);			
							$objPHPExcel->setActiveSheetIndex(0)->getStyle($lettre_en_cours.$lettre_i)->getAlignment()->setWrapText(true);		
							break;
							
							case 3:
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']]);
							break;
							
							case 4:
							$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);
							$recp='';
							for($j=0;$j<count($temp);$j++){
								if($j!=0){
									$recp.="\n";
								}
								$recp.=$temp[$j];
							}			
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $recp);	
							$objPHPExcel->setActiveSheetIndex(0)->getStyle($lettre_en_cours.$lettre_i)->getAlignment()->setWrapText(true);		
							break;
							
							case 5:
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']]);
							break;
							
							case 6:
							$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);		
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $temp[0].' '.$temp[1]);
							
							break;
							
							case 7:
							$temp=explode('<||>',$don['FIELD_'.$list_elements[$i]['ID']]);	
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $temp[0]."\n".$temp[1]."\n".$temp[2]." ".$temp[3]);
							$objPHPExcel->setActiveSheetIndex(0)->getStyle($lettre_en_cours.$lettre_i)->getAlignment()->setWrapText(true);			
							break;

							case 8:
							//$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']]);
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']], PHPExcel_Cell_DataType::TYPE_STRING);
							break;
							
							case 9:
							if(is_numeric($don['FIELD_'.$list_elements[$i]['ID']])){
								$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, date('d/m/Y',$don['FIELD_'.$list_elements[$i]['ID']]));
							}
							break;
							
							case 10:
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']]);
							break;
							
							case 11:							
							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, $don['FIELD_'.$list_elements[$i]['ID']]);
							break;
							
							case 12:
							if($don['FIELD_'.$list_elements[$i]['ID']]!=""){	
								$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, URL_DIR."/upload/".$don['FIELD_'.$list_elements[$i]['ID']]);	
								$objPHPExcel->getActiveSheet()->getCell($lettre_en_cours.$lettre_i)->getHyperlink()->setUrl(URL_DIR."/upload/".$don['FIELD_'.$list_elements[$i]['ID']]);
							}
							else{
								$objPHPExcel->setActiveSheetIndex(0)->setCellValue($lettre_en_cours.$lettre_i, 'Aucun fichier téléchargé');	
							}
							break;

						}
						
					}
					$lettre_i++;
				}
			}
			
			for($i=0;$i<count($list_elements);$i++){
				$objPHPExcel->getActiveSheet()->getColumnDimension(chr($i+65))->setAutoSize(true);
			}
			
			$objPHPExcel->setActiveSheetIndex(0);

			// Redirect output to a client’s web browser (Excel5)
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="formulaire-'.date('Y-m-d').'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			exit;
		}else{
			echo "Aucun enregistrement";
		}
	}	
}
?>