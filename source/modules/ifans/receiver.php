<?php
/**
 * 粉丝管理模块订阅器
 *
 * @author WeNewstar Team
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class IfansModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_W, $_GPC;
		$type = $this->message['type'];
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看微新星文档来编写你的代码
		$set = $this->module['config'];
		if(!isset($set['guanzhupp'])) {
			$set['guanzhupp'] = '0';
		}
		if(!isset($set['huoyuepp'])) {
			$set['huoyuepp'] = '0';
		}
		if ($set['guanzhupp'] != '0' || $set['huoyuepp'] != '0') {
			$openid = $this->message['fromusername'];
			$atype = 'weixin';
			$account_token = "account_{$atype}_token";
			$account_code = "account_weixin_code";
			$token = $account_token($_W['account']);
			$url = sprintf("https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN", $token,$openid);
			$content = ihttp_get($url);
			$dat = $content['content'];
			$re = @json_decode($dat, true);
			$dataoi['openid'] = $openid;
			$content3 = ihttp_post(sprintf("https://api.weixin.qq.com/cgi-bin/groups/getid?access_token=%s", $token),json_encode($dataoi));
			$groupid = @json_decode($content3['content'],true);
		}
		
		//退订
		if($this->message['event'] == 'unsubscribe') {
			pdo_update('fans', array(
				'follow' => 0,
				'createtime' => TIMESTAMP,
			), array('from_user' => $this->message['fromusername'], 'weid' => $GLOBALS['_W']['weid']));
		} elseif(($this->message['event'] == 'subscribe')&&($set['guanzhupp'] == '0')) {
			fans_update($this->message['fromusername'], array(
				'weid' => $GLOBALS['_W']['weid'],
				'follow' => 1,
				'from_user' => $this->message['fromusername'],
				'createtime' => TIMESTAMP,
			));
		}elseif ($set['huoyuepp'] == '0') {
			fans_update($this->message['fromusername'], array(
				'weid' => $GLOBALS['_W']['weid'],
				'follow' => 1,
				'from_user' => $this->message['fromusername'],
				'createtime' => TIMESTAMP,
			));
		}else {
			fans_update($this->message['fromusername'], array(
				'weid' => $GLOBALS['_W']['weid'],
				'follow' => 1,
				'from_user' => $this->message['fromusername'],
				'nickname' => $re['nickname'],
				'gender' => $re['sex'],
				'groupid' => $groupid['groupid'],
				'residecity'=> $re['city'],
				'resideprovince' => $re['province'],
				'nationality' => $re['country'],
				'avatar' => $re['headimgurl'],
				'createtime' => TIMESTAMP,
			));
		}
	}
}
