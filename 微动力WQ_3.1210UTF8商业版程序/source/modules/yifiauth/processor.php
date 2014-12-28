<?php
/**
 * YIFI上网认证模块处理程序
 *
 * @author 极脉信息科技
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class YifiAuthModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$message = $this->message;

		$textTpl="<xml>
				<ToUserName><![CDATA[%s]]></ToUserName>
				<FromUserName><![CDATA[%s]]></FromUserName>
				<CreateTime>%s</CreateTime>
				<MsgType><![CDATA[%s]]></MsgType>
				<Content><![CDATA[%s]]></Content>
				<FuncFlag>0</FuncFlag>
				</xml>";
		$postStr=sprintf($textTpl,$this->message['tousername'],$this->message['fromusername'],$this->message['createtime'],"text","上网");

		if($this->message['msgtype']=="text"){

						$header[]='Content-type:text/xml';

						$ch=curl_init();

						curl_setopt($ch,CURLOPT_URL,"http://www.jimair.net/Api/yifiauth.php");

						curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

						curl_setopt($ch,CURLOPT_HTTPHEADER,$header);

						curl_setopt($ch,CURLOPT_POST,1);

						curl_setopt($ch,CURLOPT_POSTFIELDS,$postStr);

						curl_setopt($ch,CURLOPT_HEADER,0);

						$result=curl_exec($ch);

						$resultObj = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
						
						$response=trim($resultObj->Content);

						curl_close($ch);

						return $this->respText($response);
		}else{
			return $this->respText("获取验证码失败");
		}
	}
}