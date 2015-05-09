<?php
//error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
if (PHP_SAPI == 'cli') die('This example should only be run from a Web Browser');
require_once './source/modules/public/Classes/PHPExcel.php';

  $id = $_GPC['id'];

		if(!empty($id)){
			$where.=" AND  a.qid={$id} ";
		}
		if (!empty($_GPC['tel'])) {
			$where .= " AND a.tel LIKE '%{$_GPC['tel']}%'";
		}
		if (!empty($_GPC['status'])&&$_GPC['status']!=-1) {
			$status = intval($_GPC['status']);
			$where .= " AND a.status = '{$status}'";
		} 

		if(!empty($_GPC['start']) &&!empty($_GPC['end']) ){
			$starttime=strtotime($_GPC['start']);
			$endtime=strtotime($_GPC['end']);
 			$where.=" AND  a.createtime>{$starttime}  AND  a.createtime<{$endtime}";
 		}else{
			exit;
		}
		

 	//$list = pdo_fetchall("SELECT a.*,b.tel FROM ".tablename('award')." as a  left join ".tablename('bigwheel_fans')." as b on  a.rid=b.rid , a.from_user=b.from_user WHERE a.rid = :rid   ORDER BY `a.id` DESC ", array(':rid' => $rid,':weid'=>$_W['weid']));				
	$sql="SELECT a.*  FROM ".tablename('shopping3_order')." as a WHERE a.weid = '{$_W['weid']}' ".$where." ORDER BY a.createtime asc ";
 	$list = pdo_fetchall($sql);
 
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
            ->setCellValue('B1', '订单号')
            ->setCellValue('C1', '订单数量')
            ->setCellValue('D1', '快递费')
            ->setCellValue('E1', '订单总价')
            ->setCellValue('F1', '订单状态')			
            ->setCellValue('G1', '付款状态')
            ->setCellValue('H1', '付款方式')
			->setCellValue('I1', '顾客姓名')
			->setCellValue('J1', '顾客电话')
			->setCellValue('K1', '顾客地址')
			->setCellValue('L1', '订单时间');

$i=2;
foreach($list as $row){
if($row['paytype']==1){
	$row['paytypestr']='余额支付';
}elseif($row['paytype']==2){
	$row['paytypestr']='在线支付';
}elseif($row['paytype']==3){
	$row['paytypestr']='货到付款';
}
if($row['status']==1){
	$row['statusstr']='下单成功';
}elseif($row['status']==2){
	$row['statusstr']='订单确认';
}elseif($row['status']==3){
	$row['statusstr']='订单成功';
}elseif($row['status']==-1){
	$row['statusstr']='订单取消';	
}elseif($row['status']==-2){
	$row['statusstr']='退款成功';	
}elseif($row['status']==0){
	$row['statusstr']='已下单';
}else{
	$row['statusstr']='未知状态';	
}

$objPHPExcel->setActiveSheetIndex(0)			
            ->setCellValue('A'.$i, $row['id'])
            ->setCellValue('B'.$i, $row['ordersn'])
            ->setCellValue('C'.$i, $row['totalnum'])
            ->setCellValue('D'.$i, $row['expressprice'])
            ->setCellValue('E'.$i, $row['totalprice'])
			->setCellValue('F'.$i, $row['statusstr'])
            ->setCellValue('G'.$i, $row['ispay']==0?'未付款':'已付款')
			->setCellValue('H'.$i, $row['paytypestr'])
            ->setCellValue('I'.$i, $row['guest_name'])
            ->setCellValue('J'.$i, $row['tel'])
            ->setCellValue('K'.$i, $row['guest_address'])
			->setCellValue('L'.$i, $row['createtime']>0?date('Y/m/d H:i',$row['createtime']):'');
	$i++;		
}					
$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22); 
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12); 
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14); 
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(14); 
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(18); 
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(18); 

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle($_W['account'][$_W['weid']]['name'].'订单_'.date('y-m-d',$starttime).'_'.date('y-m-d',$endtime));


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="order'.$_W['weid'].'_'.date('ymd',$starttime).'_'.date('ymd',$endtime).'.xlsx"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

	