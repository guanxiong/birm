<?php
/**
* 历史上的今天 文字版
*/



$url1 ="http://tool.chasfz.com/today/";


$ch=curl_init($url1);

curl_setopt($ch, CURLOPT_REFERER, "http://tool.chasfz.com/");

curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

$data  =  curl_exec($ch);

curl_close($ch);

$result = date('n月j日') ."历史上的大事记:\n\n";
preg_match_all('/<h3>([\s\S]*?)<\/h3>/',$data,$rs);


$arr=$rs[1];//全部读取
$num=count($arr)-2;
//$key = array_rand($arr,3);//随机取3个
//$num=count($arr)>3?3:count($arr);//随机取3个
for($i=0;$i<$num;$i++){
$result .=sprintf("%s\n\n",trim(strip_tags(iconv('GB2312', 'UTF-8',$arr[$i]))));
//$val =trim(strip_tags(iconv('GB2312', 'UTF-8',$arr[$key])));//返回的值
}

return $this->respText($result);


