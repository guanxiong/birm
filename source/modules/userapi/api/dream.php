<?php 

header("Content-Type:text/html;charset=utf-8"); 

$content = trim(str_replace('梦见', '', $this->message['content']));

if ($content) {
	$content=rawurlencode(mb_convert_encoding($content, 'gb2312', 'utf-8'));
	$url = "http://www.gpsso.com/WebService/Dream/Dream.asmx/SearchDreamInfo?dream={$content}"; 
	$response = ihttp_request($url);
	$content = $response['content'];
	$content = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
	if($content->DREAM) {
		$text = str_replace('请回复“【】”中', '>>>请回复“梦见”加【】中的', $content->DREAM);
		$text = trim($text);
		$text = ">>>>周公解梦<<<<<\n\n".$text;
	} else {
		$text=">>>>周公解梦<<<<<\n\n>>>查询失败，可能是我太笨啦，找不到周公！";
	}
} else {
	$text = "解梦请回复“梦见……”，如回复：\n梦见下雨";
}

return $this->respText($text);

?>