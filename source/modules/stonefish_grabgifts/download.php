<?php
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');
	
require_once './source/modules/public/Classes/PHPExcel.php';

	global $_GPC,$_W;
	$rid= intval($_GPC['rid']);
	$zj= intval($_GPC['zj']);
	//if(empty($rid)){
	//	message('抱歉，传递的参数错误！','', 'error');              
	//}
	if(!empty($zj)){
	    $where = 'And a.zhongjiang>=1';
	}
	if ($rid>0){
	    $list = pdo_fetchall('SELECT a.*,b.lihetitle FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id WHERE a.rid =:rid  and a.weid= :weid '.$where.' order by `datatime` desc', array(':rid' => $rid,':weid'=>$_W['weid']));	
	}else{
	    $list = pdo_fetchall('SELECT a.*,b.lihetitle FROM '.tablename($this->table_list).' as a left join '.tablename($this->table_gift).' as b on a.liheid=b.id WHERE a.weid= :weid '.$where.' order by `datatime` desc', array(':weid'=>$_W['weid']));	
	}
 
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
            ->setCellValue('A1', '注册时间')
            ->SetCellValue('B1', '姓名')
            ->SetCellValue('C1', '手机')
			->SetCellValue('D1', '微信号')
			->SetCellValue('E1', 'QQ号')
			->SetCellValue('F1', '邮箱')
			->SetCellValue('G1', '地址')
            ->setCellValue('H1', '分享量')
            ->setCellValue('I1', '礼盒名称')
            ->setCellValue('G1', '是否中奖')			
            ->setCellValue('K1', '分享时间')					
			->setCellValue('L1', '是否屏蔽');	

$i=2;
$ii = 1;
foreach($list as $row){
//是否中奖
if($row['zhongjiang']==0){
	$row['zhongjiang']='未中奖';
}elseif($row['zhongjiang']==1){
	$row['zhongjiang']='已中奖';
}else{
	$row['zhongjiang']='已发放';
}
//是否屏蔽
if($row['status']==0){
	$row['status']='已屏蔽';
}elseif($row['status']==1){
	$row['status']='未屏蔽';
}

$objPHPExcel->setActiveSheetIndex(0)			
            ->setCellValue('A'.$i, date('Y/m/d H:i:s',$row['datatime']))
            ->SetCellValue('B'.$i, $row['realname'])
            ->SetCellValue('C'.$i, $row['mobile'])
			->SetCellValue('D'.$i, $row['weixin'])
			->SetCellValue('E'.$i, $row['qqhao'])
			->SetCellValue('F'.$i, $row['email'])
			->SetCellValue('G'.$i, $row['address'])
            ->SetCellValue('H'.$i, $row['sharenum'])
            ->SetCellValue('I'.$i, $row['title'])
            ->SetCellValue('J'.$i, $row['zhongjiang'])			
            ->SetCellValue('K'.$i, date('Y/m/d H:i:s',$row['sharetime']))				
			->SetCellValue('L'.$i, $row['status']);
			
$i++;		
$ii++;
}					
$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(12);
// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('幸运拆礼盒_'.$rid);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="幸运拆礼盒_'.$rid.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

	