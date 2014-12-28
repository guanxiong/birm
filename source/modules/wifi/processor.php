<?php
/**
 * wifi营销功能模块处理程序
 *
 * @author 珊瑚海
 * @url http://www.vfanm.com/
 */
defined('IN_IA') or exit('Access Denied');

class WifiModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		$openid = $this->message['from'];
		$appId = $this->module['config']['appId'];
		$appKey = $this->module['config']['appKey'];
		$nodeId = $this->module['config']['nodeId'];
		$url = "http://service.rippletek.com/Portal/Wx/wxFunLogin?appId=%s&appKey=%s&nodeId=%s&openId=%s";
		$result = ihttp_get(sprintf($url,$appId,$appKey,$nodeId,$openid));
		$info = json_decode($result['content'],true);
		if($info['result'] == "ok"){
			return $this->respText(" 本机上网请 <a href=\"".$info['url']."\">直接点击</a>, 如其他设备上网，请在设备登陆界面输入验证码 : ".$info["token"]."(验证码有效期 10 分钟。)");
		}else{
			return $this->respText("登录失败，请重试。");
		}
	}
}