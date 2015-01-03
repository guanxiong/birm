<?php 
/**
 * 1、接收POST进来的xml数据处理
 * 2、查询接口得到数据
 * 3、返回给微新星结果
 */
//如果是引用本地文件，可直接使用微新星中的消息变量 $this->message
//如果是引用其它远程文件，此处只能得到POST过来的值自行解析数据

//$message = userApiUtility::parse($GLOBALS["HTTP_RAW_POST_DATA"]);
$message = $this->message;

/**
 * 处理用户发送来的内容信息，这里的需求是需要取出包含的城市信息
 * 还有很多种处理方式，根据自己设定的关键字来处理。
 */
preg_match('/公交线路(.*)/', $message['content'], $match);
$data=$match[1];

	$response['FromUserName'] = $message['to'];
	$response['ToUserName'] = $message['from'];
	$response['MsgType'] = 'text';
	$response['Content'] ='';
if(empty($data)){
  	$response['Content'] ='公交线路查询，公交线路城市+线路，或者公交线路城市,线路';
  	return $response;
}
$data=str_replace("+",",",$data);
$data=str_replace("，",",",$data);
$data=explode(',',$data);
if(count($data)!=2){
   	$response['Content'] ='公交线路查询，公交线路城市+线路，或者公交线路城市,线路';
  	return $response;
}

	$vipurl='http://www.twototwo.cn/bus/Service.aspx?format=json&action=QueryBusByLine&key=5da453b2-b154-4ef1-8f36-806ee58580f6&zone='.$data[0].'&line='.$data[1];

//开始获取远程数据

$json = file_get_contents($vipurl);
		$data=json_decode($json);
		//线路名
		$xianlu=$data->Response->Head->XianLu;
		//验证查询是否正确
		$xdata=get_object_vars($xianlu->ShouMoBanShiJian);
		$xdata=$xdata['#cdata-section'];
		$piaojia=get_object_vars($xianlu->PiaoJia);
		$xdata=$xdata.' -- '.$piaojia['#cdata-section'];		
		$main=$data->Response->Main->Item->FangXiang;
		//线路-路经
		$xianlu=$main[0]->ZhanDian;
		$str=$xdata."\n";
		$str.="【本交公途经】\n";
		for($i=0;$i<count($xianlu);$i++){
			$str.="\n".trim($xianlu[$i]->ZhanDianMingCheng);
		}

$response['Content'] =$str;
return $response;







//返回的消息记录，如果是本地文件必须构造一个response数组变量，并填充相关信息。
//如果是远程URL，返回的数据可以是response数组的json串，也可以是微信公众平台的标准XML数据接口。



class userApiUtility{
	/**
	 * 签名验证
	 * @param string $sign
	 * @param string $token 在微新星“用户自定义接口”模块设置中设置的token值
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
