<?php 
require IA_ROOT . '/source/function/api.func.php';
$url="http://news.sina.cn/?sa=d1t110v414&pos=3&vt=4";
$output=file($url);
$data=implode("",$output);


$info=explode('<div class="p_column_list">',$data);  
$info_b=explode('<span class="l_select num_pager">',$info[1]);  

$news=explode('</dl>',$info_b[0]);  

$result="不好意思，暂时没有获取到新闻数据，要不重新发送“2”试试看？";
if(count($news)>1){
	$result="新闻快讯：\n\n";
	for($j=0;$j<11;$j++){
		//$n++;
		$new=str_replace("<li>",'',$news[$j]);
		$new=str_replace('<dl class="p_column_dl"><dt>','',$new);
		$new=str_replace("</dt><dd>",'',$new);
		$new=str_replace("</dd>",'',$new);
		$new=str_replace("'",'"',$new);
		$result .="●".trim($new)."\n";
	}
	
	
}
return $this->respText($result."\n\n--------\n@xiaowaibst <a href=\"http://www.bstcrm.com/site.php?do=index&sid=1#mp.weixin.qq.com\">小歪微站</a>");

