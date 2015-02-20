<?php

header("Content-type: text/html; charset=utf-8");

$content = str_replace("算命", "", $this->message['content']);

if ($content) {
	$year = substr($content,0,4);
	$month = substr($content,4,2);
	$day = substr($content,6,2);
	$hour = 1+(intval(substr($content,8,2))+1)/2;//转换成时辰
	$url = "http://www.7mingwang.com/chengu/cg.php?y=".$year."&m=".$month."&d=".$day."&h=".$hour;
	$response = ihttp_request($url);
	$content = $response['content'];
	$text = strip_tags($content);
	$text = cut($text, "initChengu();", "my_cg_code();"); //输出内容
	$text = preg_replace("/(\r\n|\n|\r|\t)/i", '', $text);
	$text = str_replace("称骨算命歌诀", "\n\n【歌诀】\n", $text);
	$text = str_replace("注解：", "\n\n【注解】\n", $text);
	$text = str_replace("你出生于", "【算命】\n你出生于", $text);
} else {
	$text = "算命请回复”算命加农历的生日”，年四位，月日时都是两位，时间采用24小时制，如输入：\n算命1997100108";
}

return $this->respText($text);

function cut($file, $from, $end) { 
	$posstart = stripos($file, $from);
	if ($posstart === FALSE) {
		return ">>>查询错误，请输入”算命加农历的生日和时间”，年四位，月日都是两位，时间两位，采用24小时制，如输入：\n算命1990023017";
	} else {
		$posstart = $posstart+strlen($from);
	}
	$posend = strrpos($file,$end)-$posstart;
	if ($posstart === FALSE) {
		return ">>>查询错误，请输入”算命加农历的生日和时间”，年四位，月日都是两位，时间两位，采用24小时制，如输入：\n算命1990023017";
	} 
	return substr($file, $posstart, $posend);
}

?>