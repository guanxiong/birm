<?php 
/**
 * 云服务相关操作
 * [WNS] Copyright (c) 2013 BIRM.CO
 */

if(empty($_W['isfounder'])) {
	message('访问非法.');
}
$do = in_array($do, array('profile', 'callback', 'promotion')) ? $do : 'profile';
$authurl = 'http://addons.we7.cc/site.php?act=passport';

$auth = array();
$auth['key'] = '';
$auth['password'] = '';
$auth['url'] = rtrim($_W['siteroot'], '/');
$auth['referrer'] = intval($_W['config']['setting']['referrer']);
if(!empty($_W['setting']['site']['key']) && !empty($_W['setting']['site']['token'])) {
	$auth['key'] = $_W['setting']['site']['key'];
	$auth['password'] = md5($_W['setting']['site']['key'] . $_W['setting']['site']['token']);
}	

if($do == 'profile') {
	$auth['forward'] = 'profile';
	header('location: ' . __to($auth));
}

if($do == 'promotion') {
	if(empty($_W['setting']['site']['key']) || empty($_W['setting']['site']['token'])) {
		message("你的程序需要在微新星云服务平台注册你的站点资料, 来接入云平台服务后才能使用推广功能.", create_url('cloud/profile'), 'error');
	}
	$auth['forward'] = 'promotion';
	header('location: ' . __to($auth));
}


if($do == 'callback') {
	$resp = @json_decode(base64_decode($_GPC['__auth']), true);
	$auth['key'] = $resp['key'];
	$auth['password'] = md5($resp['key'] . $resp['token']);
	if(is_array($resp)) {
		$site = array();
		$site['key'] = $resp['key'];
		$site['token'] = $resp['token'];
		include model('setting');
		setting_save($site, 'site');
		$auth['forward'] = 'register';
		header('location: ' . __to($auth));
	} else {
		message('访问非法.');
	}
}

function __to($auth) {
	global $authurl;
	$query = base64_encode(json_encode($auth));
	return $authurl . '&__auth=' . $query;
}
