<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');
	
require_once './source/library/phpexcel/PHPExcel.php';

	global $_GPC,$_W;
	$rid= intval($_GPC['rid']);
	$uid= intval($_GPC['uid']);
	if(empty($rid)){
		message('抱歉，传递的参数错误！','', 'error');              
	}
	$Where = "";
	if (!empty($uid)){
			$Where = " AND `uid` = $uid";		
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename($this->table_data)." WHERE rid =:rid  and weid= :weid ".$Where." order by `zonyutime` desc" , array(':rid' => $rid,':weid'=>$_W['weid']));				
 
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");


// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', '分享人姓名')
            ->setCellValue('C1', '分享人电话')
            ->setCellValue('D1', '助抢人姓名')
            ->setCellValue('E1', 'IP地址')
            ->setCellValue('F1', '助抢次数')			
            ->setCellValue('G1', '助抢时间');

$i=2;
$ii = 1;
foreach($list as $row){
$reply = pdo_fetch("SELECT from_user FROM ".tablename($this->table_list)." WHERE weid = :weid and rid = :rid and id = :id", array(':weid' => $_W['weid'], ':rid' => $rid, ':id' => $row['uid']));
$profile  = fans_search($reply['from_user'], array('realname','mobile'));
$objPHPExcel->setActiveSheetIndex(0)			
            ->setCellValue('A'.$i, $row['id'])
            ->setCellValue('B'.$i, $profile['realname'])
            ->setCellValue('C'.$i, $profile['mobile'])
            ->setCellValue('D'.$i, $row['realname'])
            ->setCellValue('E'.$i, $row['zonyuip'])
            ->setCellValue('F'.$i, $row['viewnum'])			
            ->setCellValue('G'.$i, date('Y/m/d H:i:s',$row['zonyutime']));		
			
$i++;		
$ii++;
}					
$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(22); 
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('抢礼品活动助抢数据_'.$rid);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
if (empty($uid)){
header('Content-Disposition: attachment;filename="zonyudata_'.$rid.'.xlsx"');
}else{
header('Content-Disposition: attachment;filename="zonyudata_'.$rid.'_'.$uid.'.xlsx"');
}
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

	