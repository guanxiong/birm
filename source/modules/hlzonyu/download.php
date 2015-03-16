<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');
	
require_once './source/library/phpexcel/PHPExcel.php';

	global $_GPC,$_W;
	$rid= intval($_GPC['rid']);
	if(empty($rid)){
		message('抱歉，传递的参数错误！','', 'error');              
	}
	$sql="SELECT a.*,b.realname,b.mobile,r.title FROM ".tablename($this->table_list)." as a left join ".tablename('fans')." as b on a.from_user=b.from_user LEFT JOIN ims_hlzonyu_reply as r on a.rid=r.rid  WHERE  a.weid= :weid order by `zonyunum` desc,`zonyutime` asc";
	
	$list = pdo_fetchall($sql , array(':weid'=>$_W['weid']));				
 
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
            ->setCellValue('A1', '排名')
            ->setCellValue('B1', '姓名')
            ->setCellValue('C1', '手机')
            ->setCellValue('D1', '砍价')
            ->setCellValue('E1', '内定排名')
            ->setCellValue('F1', '是否中奖')			
            ->setCellValue('G1', '最后集抢时间')					
			->setCellValue('H1', '商品')
			->setCellValue('I1', '分享时间')
			->setCellValue('J1', '人数');

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

	$row['ndrank']='';
	
	
	$gnumes=pdo_fetchcolumn("select count(*) from  ".tablename($this->table_data)." where uid={$row['id']}");
	$objPHPExcel->setActiveSheetIndex(0)			
				->setCellValue('A'.$i, $ii)
				->setCellValue('B'.$i, $row['realname'])
				->setCellValue('C'.$i, $row['mobile'])
				->setCellValue('D'.$i, $row['zonyunum'])
				->setCellValue('E'.$i, $row['ndrank'])
				->setCellValue('F'.$i, $row['zhongjiang'])			
				->setCellValue('G'.$i, date('Y/m/d H:i:s',$row['zonyutime']))				
				->setCellValue('H'.$i, $row['title'])
				->setCellValue('I'.$i, date('Y/m/d H:i:s',$row['createtime']))
				->setCellValue('J'.$i, $gnumes);
				
				
	$i++;		
	$ii++;
}					
$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20); 
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30); 
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30); 
// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('抢礼品活动_'.$rid);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="zonyu_'.$rid.'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

	