<?php
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
 */
defined('IN_IA') or exit('Access Denied');

$weid = $_W['weid'];
$_W['setting']['authmode'] = empty($_W['setting']['authmode']) ? 1 : $_W['setting']['authmode'];
if($_GPC['__auth']) {
	$pass = @base64_decode($_GPC['__auth']);
	$pass = @json_decode($pass, true);
	if(is_array($pass) && !empty($pass['fans']) && !empty($pass['time']) && !empty($pass['hash'])) {
		if(($_W['setting']['authmode'] == 2 && abs($pass['time'] - TIMESTAMP) < 180) || $_W['setting']['authmode'] == 1) {
			$row = fans_search($pass['fans'], array('salt'));
			if(!is_array($row) || empty($row['salt'])) {
				$row = array('from_user' => $pass['fans'], 'salt' => '');
			}
			$hash = md5("{$pass['fans']}{$pass['time']}{$row['salt']}{$_W['config']['setting']['authkey']}");
			if($pass['hash'] == $hash) {
				if ($_W['setting']['authmode'] == 2) {
					$row = array();
					$row['salt'] = random(8);
					fans_update($pass['fans'], $row);
				}
				$cookie = array();
				$cookie['openid'] = $pass['fans'];
				$cookie['hash'] = substr(md5("{$pass['fans']}{$row['salt']}{$_W['config']['setting']['authkey']}"), 5, 5);
				$session = base64_encode(json_encode($cookie));
				isetcookie('__msess', $session, 30 * 86400);
			}
		}
	}
}

$forward = @base64_decode($_GPC['forward']);
if(empty($forward)) {
	$forward = create_url('mobile/channel', array('name'=>'index', 'weid'=>$weid));
} else {
	$forward = strexists($forward, 'http://') ? $forward : $_W['siteroot'] . $forward;
}
if(strexists($forward, '#')) {
	$pieces = explode('#', $forward, 2);
	$forward = $pieces[0];
}
$forward .= '&wxref=mp.weixin.qq.com#wechat_redirect';
header('location:' . $forward);
