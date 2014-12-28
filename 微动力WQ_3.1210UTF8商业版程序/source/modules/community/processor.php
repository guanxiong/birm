<?php
defined('IN_IA') or exit('Access Denied');

class CommunityModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$member = pdo_fetch("SELECT * FROM ".tablename('community_member')." WHERE `weid` = :weid AND `openid` = :openid", array(':weid' => $_W['weid'],':openid' => $this->message['from']));
		if (empty($member)) {
			$content = "尊敬的业主您好，此物业服务针对对象为物业业主，您的身份还未进行认证与审核，请您点击<a href=\"mobile.php?act=module&name=community&do=register&weid={$_W['weid']}&openid={$this->message['from']}\">【自助认证业主】</a>按钮进行您的业主身份认证。更多物业服务请点击底部菜单。".PHP_EOL;
			//return $this->respText($content);
		}
		
	}
}
