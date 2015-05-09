<?php 
/**
 * 1、接收POST进来的xml数据处理
 * 2、查询接口得到数据
 * 3、返回给微动力结果
 */
//如果是引用本地文件，可直接使用微动力中的消息变量 $this->message
//如果是引用其它远程文件，此处只能得到POST过来的值自行解析数据

//$message = userApiUtility::parse($GLOBALS["HTTP_RAW_POST_DATA"]);
$message = $this->message;

/**
 * 处理用户发送来的内容信息，这里的需求是需要取出包含的城市信息
 * 还有很多种处理方式，根据自己设定的关键字来处理。
 */
$message['content'] = str_replace("+", "", $message['content']);
$message['content'] = str_replace(" ", "", $message['content']);
preg_match('/天气(.*)/', $message['content'], $match);
$content = $match[1];
if(!$content){
	$content = '深圳';
}

include("t_api.php");
$city=$t_api[$content];
if($city){//判断地名是否存在
	$url = "http://m.weather.com.cn/data/".$city.".html";
	$output = file_get_contents($url);
	$json_decode = json_decode($output,true);
	@$json = $json_decode[weatherinfo];
	$count = '5';
	$description1 = '';
	$title1 = "您好，".$content."未来两天天气如下：";
	$pic1 = "http://chilechuan.imau.edu.cn/api/imauhelper/tq.jpg";
	$title2 = "【今日天气】：".$json['weather1']."  ".$json['temp1']." ".$json['wind1'];
	$pic2 = "http://m.weather.com.cn/img/b".$json['img1'].".gif ";
	$title3 = "【明天天气】：".$json['weather2']."  ".$json['temp2']." ".$json['wind2'];
	$pic3 = "http://m.weather.com.cn/img/b".$json['img2'].".gif ";
	$title4 = "【后天天气】：".$json['weather3']."  ".$json['temp3']." ".$json['wind3'];
	$pic4 = "http://m.weather.com.cn/img/b".$json['img3'].".gif ";
	$title5 = "默认为您提供深圳的天气预报情况，查询其他城市天气预报请回复 天气+城市名 ，例如 天气北京";
}else{
	$count = '1';
	$title1 = "sorry~~~没有找到".$content."的天气预报信息";
	$description1 = "查询其他城市天气预报请回复 天气+城市名 ，例如 天气北京 使用本平台其他功能，请回复#查看功能菜单，谢谢您的使用";
}

//返回的消息记录，如果是本地文件必须构造一个response数组变量，并填充相关信息。
//如果是远程URL，返回的数据可以是response数组的json串，也可以是微信公众平台的标准XML数据接口。

$response = array();
if ($status['http_code'] = 200) {
	$obj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
	$response['FromUserName'] = $message['to'];
	$response['ToUserName'] = $message['from'];
	$response['MsgType'] = 'news';
	$response['ArticleCount'] = $count;
			$response['Articles'][1] = array(
              'Title' => $title1,
        		'Description' => $description1,
        		'PicUrl' => $pic1,
        		'Url' => '',
        		'TagName' => 'item',
        	);
        	$response['Articles'][2] = array(
              'Title' => $title2,
        		'Description' => '',
        		'PicUrl' => $pic2,
        		'Url' => '',
        		'TagName' => 'item',
        	);
        	$response['Articles'][3] = array(
              'Title' => $title3,
        		'Description' => '',
        		'PicUrl' => $pic3,
        		'Url' => '',
        		'TagName' => 'item',
        	);
        	$response['Articles'][4] = array(
              'Title' => $title4,
        		'Description' => '',
        		'PicUrl' => $pic4,
        		'Url' => '',
        		'TagName' => 'item',
        	);
        	$response['Articles'][5] = array(
              'Title' => $title5,
        		'Description' => '',
        		'PicUrl' => $pic5,
        		'Url' => '',
        		'TagName' => 'item',
        	);
}
return $response;


class userApiUtility{
	/**
	 * 签名验证
	 * @param string $sign
	 * @param string $token 在微动力“用户自定义接口”模块设置中设置的token值
	 * @return boolean
	 */
	static public function checkSign($sign = '', $token = '') {
		return $_GET['sign'] == sha1($_GET['time'].$token);
	}
	
	/**
	 * 格式化接收到的xml数据
	 * @param string $message
	 * @return multitype:string Ambigous <string>
	 */
	static public function parse($message) {
		$packet = array();
		if (!empty($message)){
			$obj = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA);
			if($obj instanceof SimpleXMLElement) {
				$packet['from'] = strval($obj->FromUserName);
				$packet['to'] = strval($obj->ToUserName);
				$packet['time'] = strval($obj->CreateTime);
				$packet['type'] = strval($obj->MsgType);
				$packet['event'] = strval($obj->Event);
	
				foreach ($obj as $variable => $property) {
					$packet[strtolower($variable)] = (string)$property;
				}
				if($packet['type'] == 'event') {
					$packet['type'] = $packet['event'];
					unset($packet['content']);
				}
			}
		}
		return $packet;
	}
}
