<?php

header("Content-type: text/html; charset=utf-8");

$content = str_replace('归属', '', $this->message['content']);

if ($content) {
	if (preg_match('/^1[3458]\d{9}$/', $content)) {
		$content = rawurlencode(mb_convert_encoding($content, 'gb2312', 'utf-8'));
		$url = 'http://webservice.webxml.com.cn/WebServices/MobileCodeWS.asmx/getMobileCodeInfo?mobileCode='.$content.'&userId=';
		$response = ihttp_request($url);
		$content = $response['content'];
		$text = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
		if(strpos($text,'http://')) {
			$text = "手机号码错误！\n请回复“归属地…”，如：\n归属地13800138000";
		}
	} else {
		$url = 'http://www.gpsso.com/webservice/idcard/idcard.asmx/SearchIdCard?IdCard='.$content; 
		$response = ihttp_request($url);
		$content = $response['content'];
		$content = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
		if ($content->IDCARD) {
			$text = "【身份证号】\n".$content->IDCARD."\n\n【出生日期】".$content->BIRTHDAY."\n\n【农历】".$content->NONGLI."\n\n【地址】".$content->ADDRESS."\n\n【性别】".$content->SIX;
		} else {
			$text = "您输入的身份证号有误！请回复“身份证…”，如：\n身份证360782199205130210";
		}
	}
} else {
	$text = "1、查询手机号码归属地请回复“手机号加归属”，如：\n13800138000归属\n\n2、查询身份证号码归属地请回复“身份证号码加归属”，如回复：\n360782199205130210归属";
}

return $this->respText($text);

?>