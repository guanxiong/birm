<?php
/**
 * PHPEXCEL����excel�ļ�
 * @author:firmy
 * @desc ֧������������������excel�ļ�����δ��ӵ�Ԫ����ʽ�Ͷ���
 */
ini_set('memory_limit', '120M'); 
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once 'Classes/PHPExcel/Writer/Excel5.php';
include_once 'Classes/PHPExcel/IOFactory.php';

$fileName = "test_excel";
$headArr = array("aaa","bbb","ccc");
$data = array(array(iconv('gbk', 'utf-8', '����Hello'),2,5),array(1,3,6),array(5,7,8));
getExcel($fileName,$headArr,$data);


function getExcel($fileName,$headArr,$data){
    if(empty($data) || !is_array($data)){
        die("data must be a array");
    }
    if(empty($fileName)){
        exit;
    }
    $date = date("Y_m_d",time());
    $fileName .= "_{$date}.xlsx";

    //�����µ�PHPExcel����
    $objPHPExcel = new PHPExcel();
    $objProps = $objPHPExcel->getProperties();
 
    //���ñ�ͷ
    $key = ord("A");
    foreach($headArr as $v){
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
        $key += 1;
    }

    
    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data as $key => $rows){ //��д��
        $span = ord("A");
  $i= '0';    //ʮ��λ
        foreach($rows as $keyName=>$value){// ��д��
   if($span>90 && $i=='0'){
    $span=ord("A");
    $i=ord('A');
   }else if($span>90){
    $i++;
    $span=ord("A");
   }
            $j = chr($span);
   if($i=='0'){
             $objActSheet->setCellValue($j.$column, $value);
   }else{
      $k=chr($i);
      $objActSheet->setCellValue($k.$j.$column, $value);
   }
   $span++;
            
        }
        $column++;
    }
    $fileName = iconv("utf-8", "gb2312", $fileName);
    //��������
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    //���û��ָ������һ����,����Excel�����ǵ�һ����
    $objPHPExcel->setActiveSheetIndex(0);
    //������ض���һ���ͻ���web�����(Excel2007)
          /*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header("Content-Disposition: attachment; filename=\"$fileName\"");
          header('Cache-Control: max-age=0');*/
          $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
     $filename = "outexcel.xlsx";
        
        header("Content-Type: application/force-download"); 
        header("Content-Type: application/octet-stream"); 
        header("Content-Type: application/download"); 
        header('Content-Disposition:inline;filename="'.$filename.'"'); 
        header("Content-Transfer-Encoding: binary"); 
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
        header("Pragma: no-cache"); 
   $objWriter->save('php://output'); //�ļ�ͨ�����������
   exit();  //end
          if(!empty($_GET['excel'])){
            $objWriter->save('php://output'); //�ļ�ͨ�����������
        }else{
          $objWriter->save($fileName); //�ű���ʽ���У������ڵ�ǰĿ¼
        }
  exit;

}