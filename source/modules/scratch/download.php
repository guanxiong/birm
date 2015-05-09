<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');
	
require_once './source/modules/public/Classes/PHPExcel.php';

	global $_GPC,$_W;
	$rid= intval($_GPC['rid']);
	if(empty($rid)){
		message('抱歉，传递的参数错误！','', 'error');              
	}
 	//$list = pdo_fetchall("SELECT a.*,b.tel FROM ".tablename('award')." as a  left join ".tablename('scratch_fans')." as b on  a.rid=b.rid , a.from_user=b.from_user WHERE a.rid = :rid   ORDER BY `a.id` DESC ", array(':rid' => $rid,':weid'=>$_W['weid']));				
	$list = pdo_fetchall("SELECT a.*,b.tel FROM ".tablename('award')." as a  left join ".tablename('scratch_fans')." as b on a.rid=b.rid and  a.from_user=b.from_user  WHERE a.rid = :rid and a.weid=:weid   ORDER BY a.id DESC " , array(':rid' => $rid,':weid'=>$_W['weid']));				
 
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
            ->setCellValue('B1', 'sn码')
            ->setCellValue('C1', '奖项')
            ->setCellValue('D1', '奖品名称')
            ->setCellValue('E1', '状态')
            ->setCellValue('F1', '领取者手机号')			
            ->setCellValue('G1', '中奖者微信码')					
			->setCellValue('H1', '中奖时间')
			->setCellValue('I1', '使用时间');	

$i=2;
foreach($list as $row){
if($row['status']==1){
	$row['status']='已发放';
}elseif($row['status']==2){
	$row['status']='已兑奖';
}else{
	$row['status']='未发放';
}
$objPHPExcel->setActiveSheetIndex(0)			
            ->setCellValue('A'.$i, $row['id'])
            ->setCellValue('B'.$i, $row['award_sn'])
            ->setCellValue('C'.$i, $row['prizetype'])
            ->setCellValue('D'.$i, $row['description'])
            ->setCellValue('E'.$i, $row['status'])
            ->setCellValue('F'.$i, $row['tel'])			
            ->setCellValue('G'.$i, $row['from_user'])				
			->setCellValue('H'.$i, date('Y/m/d H:i',$row['createtime']))
            ->setCellValue('I'.$i, date('Y/m/d H:i',$row['consumetime']));
			
$i++;		
}					
$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22); 
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14); 
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14); 
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18); 

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('活动奖品发放_'.$rid);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SNcode_'.$rid.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

	