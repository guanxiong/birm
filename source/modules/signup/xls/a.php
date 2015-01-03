<?php
/**
 * PHPEXCEL生成excel文件
 * @author:firmy
 * @desc 支持任意行列数据生成excel文件，暂未添加单元格样式和对齐
 */
ini_set('memory_limit', '120M'); 
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/Writer/Excel2007.php';
require_once 'Classes/PHPExcel/Writer/Excel5.php';
include_once 'Classes/PHPExcel/IOFactory.php';

$fileName = "test_excel";
$headArr = array("aaa","bbb","ccc");
$data = array(array(iconv('gbk', 'utf-8', '中文Hello'),2,5),array(1,3,6),array(5,7,8));
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

    //创建新的PHPExcel对象
    $objPHPExcel = new PHPExcel();
    $objProps = $objPHPExcel->getProperties();
 
    //设置表头
    $key = ord("A");
    foreach($headArr as $v){
        $colum = chr($key);
        $objPHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
        $key += 1;
    }

    
    $column = 2;
    $objActSheet = $objPHPExcel->getActiveSheet();
    foreach($data as $key => $rows){ //行写入
        $span = ord("A");
  $i= '0';    //十进位
        foreach($rows as $keyName=>$value){// 列写入
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
    //重命名表
    $objPHPExcel->getActiveSheet()->setTitle('Simple');
    //设置活动单指数到第一个表,所以Excel打开这是第一个表
    $objPHPExcel->setActiveSheetIndex(0);
    //将输出重定向到一个客户端web浏览器(Excel2007)
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
   $objWriter->save('php://output'); //文件通过浏览器下载
   exit();  //end
          if(!empty($_GET['excel'])){
            $objWriter->save('php://output'); //文件通过浏览器下载
        }else{
          $objWriter->save($fileName); //脚本方式运行，保存在当前目录
        }
  exit;

}