<?php

header("content-type:text/html;charset=utf-8");

if (preg_match('/^音乐/', $this->message['content']))
{
	$content = preg_replace('/^音乐/', '', $this->message['content']);
	$content = trim($content);
	if ($content) {
		if ($content == '随机')
		{
			$urlarr = array(
				'http://music.soso.com/portal/hit/special/classic/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/movie/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/dj/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/couple/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/mood/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/world/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/network/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/school/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/hk/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/game/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/children/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/show/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/chineseshow/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/light/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/chinesefolk/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/rock/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/jazz/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/hip/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/country/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/rb/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/old/hit_list_1.html',
				'http://music.soso.com/portal/hit/special/pop/hit_list_1.html'
			);
			$index = array_rand($urlarr);
			$url = $urlarr[$index];
			$response = ihttp_request($url);
			$content = $response['content'];
			$content = str_replace("\n", "", $content);
			$content = iconv('gb2312', 'utf-8', $content);
			if (preg_match("/<span class=\"data\" style='display:none;'>(.*)<\/span>/", $content, $match)) {
				$content = explode("@@", $match[1]);
				$music = array(
					'Title'	=> $content[1],
					'Description' => $content[3],
					'MusicUrl' => $content[6],
					'HQMusicUrl' => $content[6]
				);
				return $this->respMusic($music);
			}
		}
		else
		{
			$content = rawurlencode(mb_convert_encoding($content, 'gb2312', 'utf-8'));//SOSO音乐使用GB2312格式的
			$url = "http://cgi.music.soso.com/fcgi-bin/m.q?w=".$content."&p=1&source=1&searchid=6792072470322251654&remoteplace=txt.soso.top&t=1";
			$response = ihttp_request($url);
			$content = $response['content'];
			$content = str_replace("\n", "", $content);
			$content = iconv('gb2312', 'utf-8', $content);
			if (preg_match("/<td class=\"data\">(.*)<\/td>/", $content, $match)) {
				$content = explode("@@", $match[1]);
				preg_match('/FIhttp:\/\/stream(\d+?)\.qqmusic\.qq\.com\/(\d+?)\.wma/', $content[8], $match);
				$stream = $match[1]+10;
				$musicid = $match[2]+18000000;
				$musicurl = "http://stream".$stream.".qqmusic.qq.com/".$musicid.".mp3";
				$music = array(
					'Title'	=> $content[1],
					'Description' => $content[3],
					'MusicUrl' => $musicurl,
					'HQMusicUrl' => $musicurl
				);
				return $this->respMusic($music);
			}
		}
		$text = "音乐获取失败，请更换关键词或重试！";
	} else {
		$text = "听音乐请回复“音乐加关键词”，如回复：\n音乐后来\n音乐刘若英后来\n随机请回复“音乐随机”";
	}
}
elseif (preg_match('/^搜搜/', $this->message['content']))
{
	$content = preg_replace('/^搜搜/', '', $this->message['content']);
	$content = trim($content);
	if ($content) {
		$content = iconv('utf-8', 'gbk', $content);
		$url = 'http://www.soso.com/q?w='.urlencode($content);
		$response = ihttp_request($url);
		$content = $response['content'];
		$content = str_replace("\n", "", $content);
		$content = iconv('gbk', 'utf-8', $content);
		
		if (preg_match_all('/<h3(.*?)><a(.*?)href="(.*?)"(.*?)>(.*?)<\/a><\/h3>/', $content, $match)) {
			$news = array();
			$news[] = array(
				'title' => '【搜搜结果如下】',
				'description' => '',
				'picurl' => 'http://bcs.duapp.com/jdxhdw/picture%2Fsoso.jpg',
				'url' => $url
			);
			$num = count($match[0])-1;
			if ($num>9) {
				$num = 9;
			}
			for ($i=0;$i<$num;$i++) {
				$news[] = array(
					'title' => strip_tags($match[5][$i]),
					'description' => '',
					'picurl' => '',
					'url' => $match[3][$i]
				);
			}
			return $this->respNews($news);
		} else {
			$text = "搜搜失败，可能企鹅睡着了，更换别的关键词试试吧！";
		}
	} else {
		$text = "请回复“搜搜加关键词”，如回复：\n搜搜微信\n搜搜经典笑话大王";
	}
}
elseif (preg_match('/^\d{5,11}状态/', $this->message['content']))
{
	$qq = str_replace('状态', '', $this->message['content']);
	$url = 'http://webservice.webxml.com.cn/webservices/qqOnlineWebService.asmx/qqCheckOnline?qqCode='.$qq;
	$response = ihttp_request($url);
	$content = $response['content'];
	$content = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
	if ($content == 'Y') {
		$text = "QQ号：$qq\n状态：在线";
	} elseif ($content == 'N') {
		$text = "QQ号：$qq\n状态：离线或隐身";
	} elseif ($content == 'E') {
		$text = ">>>QQ号码错误！";
	} else {
		$text = ">>>查询失败！QQ状态查询系统错误！";
	}
}

return $this->respText($text);

?>