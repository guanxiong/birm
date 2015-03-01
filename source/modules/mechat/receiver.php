<?php
/**
 * 美洽客服接入模块订阅器
 *
 * @author Yokit QQ:182860914
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class MechatModuleReceiver extends WeModuleReceiver {
	public $tablename = 'mechat';
	public $gateway = array();
	public $atype;
	
	public function __construct(){
		global $_W;
		$this->atype='';
		if($_W['account']['type'] == '1') {
			$this->atype = 'weixin';
			$this->gateway['fans_info'] = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s";
		}
		if($_W['account']['type'] == '2') {
			$this->atype = 'yixin';
			$this->gateway['fans_info'] = "";
		}
		$this->gateway['mechat_receive'] = "http://wx.mobilechat.im/cgi-bin/weixin/receive?access_token=%s";
		
	}
	
	public function receive() {
		global $_W;
		$type = $this->message['type'];
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看微动力文档来编写你的代码
		if($type!="text" && $type!="image" && $type!="voice") {
			return;
		}
		
		$file = IA_ROOT . '/source/modules/mechat/function.php';
		if (!file_exists($file)) {
			return array();
		}
		
		include_once($file);
		
		$sql = "SELECT * FROM " . tablename($this->tablename) . " WHERE `weid`=:weid";
		$row = pdo_fetch( $sql, array(':weid'=>$_W['weid']) );
		
		if($row){
			$pdata = array(
							"ToUserName" => $this->message['tousername'],
							"FromUserName" => $this->message['fromusername'],
							"CreateTime" => $this->message['createtime'],
							"MsgType" => $this->message['msgtype'],
							"Content" => $this->message['content'],
							"MsgId" => $this->message['msgid']
							);
			if($type=="voice"){
				$pdata["MsgType"]="text";
				$pdata["Content"]="系统：微信公众号粉丝发送语音消息，系统暂不能接收请客服处理。";
			}
			
			$dat=array('unit' => $row["name"], 'msg' => json_encode($pdata));
			$dat2=iunserializer($row["cdata"]);
			$actoken = account_mechat_token( array("weid" => $_W['weid'], "access_token" => $dat2["access_token"], "appid" => $dat2["appid"], "appsecret" => $dat2["appsecret"]) );
			
			$url = sprintf($this->gateway['mechat_receive'], $actoken);
			
			$content = ihttp_post($url, $dat);
		}
		
	}
}