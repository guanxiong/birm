<?php

header("content-type:text/html;charset=utf-8");

$page = rand(360,1);
$url = 'http://www.jokeji.cn/list_'.$page.'.htm';
$response = ihttp_request($url);
$content = $response['content'];
$pattern = '#<a href="(/jokehtml/.*?/[0-9]+\.htm)".*?>.*?</a>#';
preg_match_all($pattern, $content, $m);
$index = array_rand($m[1]);
$url = 'http://www.jokeji.cn'.$m[1][$index];
$response = ihttp_request($url);
$content = $response['content'];
$content = str_replace("\n", "", $content);
$pattern = '#<span id="text110">(.*?)</span>#';
preg_match($pattern, $content, $m);
$content = $m[1];
$content = preg_replace('#<A href=(.*?)>(.*?)</A>#', '', $content);
$pattern = '#<P>(.*?)</P>#';
preg_match_all($pattern, $content, $m);
$index = array_rand($m[1]);
$content = $m[1][$index];
$content = str_replace("<BR>", "\n", $content);
$content = strip_tags($content);
$content = preg_replace('/&(.*);/', '', $content);
$content = trim($content);
$text = iconv('gbk', 'utf-8', $content);
	
if (strlen($content) < 30) {
	$fdarr = array(
		'你说啥？我没听见！',
		'刚才卡了，你再发一遍！',
		'不讲了，我累了！/:,@o',
		'讲累了，不讲了！/:,@o',
		'就要笑话，来点其它的不行吗？',
		'讲那么多笑话也不见你笑啊！/:,@o',
		'你不就是一个笑话吗？/:,@P',
		'吼吼，皮笑肉不笑！/:,@o',
		'你再发一次试试！/:,@P',
		'今天的笑话卖完了，明天再来吧！/:,@P',
		'你让我讲笑话我就讲，那我岂不是太没面子啦？/:,@o'
	);
	$index = array_rand($fdarr);
	$text = $fdarr[$index];
}

return $this->respText($text);

?>