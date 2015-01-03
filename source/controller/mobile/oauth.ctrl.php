<?php
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
 * $sn: origins/source/controller/mobile/oauth.ctrl.php : v 8c3eea27b11e : 2014/05/16 09:42:29 : veryinf $
 */
defined('IN_IA') or exit('Access Denied');

$account = $_W['account'];
$code = $_GPC['code'];
if(!empty($code)) {
	$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$account['key']}&secret={$account['secret']}&code={$code}&grant_type=authorization_code";
	$ret = ihttp_get($url);
	if(!is_error($ret)) {
		$auth = @json_decode($ret['content'], true);
		if(is_array($auth) && !empty($auth['openid'])) {
			$row = array(
				'weid' => $_W['weid'],
				'follow' => 0,
				'salt' => random(8),
				'from_user' => $auth['openid'],
				'createtime' => TIMESTAMP,
			);
			fans_update($auth['openid'], $row);
			$cookie = array();
			$cookie['openid'] = $auth['openid'];
			$cookie['hash'] = substr(md5("{$auth['openid']}{$row['salt']}{$_W['config']['setting']['authkey']}"), 5, 5);
			$session = base64_encode(json_encode($cookie));
			isetcookie('__msess', $session, 30 * 86400);
			$forward = base64_decode($_GPC['state']);
			header('location: ' . $_W['siteroot'] . 'mobile.php?' . $forward . '&wxref=mp.weixin.qq.com#wechat_redirect');
			exit;
		}
	}
}
message('微信授权失败, 错误信息为: ' . $ret['message']);
