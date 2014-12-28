<?php
/**
 * 微动力管理后台初始化文件
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

$session = json_decode(base64_decode($_GPC['__session']), true);
if(is_array($session)) {
	$member = member_single(array('uid'=>$session['uid']));
	if(is_array($member) && $session['hash'] == md5($member['password'] . $member['salt'])) {
		$_W['uid'] = $member['uid'];
		$_W['username'] = $member['username'];
		$member['currentvisit'] = $member['lastvisit'];
		$member['currentip'] = $member['lastip'];
		$member['lastvisit'] = $session['lastvisit'];
		$member['lastip'] = $session['lastip'];
		$_W['member'] = $member;
		$founder = explode(',', $_W['config']['setting']['founder']);
		$_W['isfounder'] = in_array($_W['uid'], $founder) ? true : false;
	} else {
		isetcookie('__session', false, -100);
	}
	unset($member);
}
unset($session);

if (!empty($_GPC['__weid'])) {
	$_W['weid'] = intval($_GPC['__weid']);
}

if (!empty($_W['weid'])) {
	$_W['account'] = pdo_fetch("SELECT * FROM " . tablename('wechats') . " WHERE weid = :weid", array(':weid' => $_W['weid']));
	$_W['account']['default_message'] = iunserializer($_W['account']['default_message']);
	$_W['account']['access_token'] = iunserializer($_W['account']['access_token']);
	$_W['account']['payment'] = iunserializer($_W['account']['payment']);

	$_W['account']['template'] = pdo_fetchcolumn("SELECT name FROM ".tablename('site_templates')." WHERE id = '{$_W['account']['styleid']}'");
	$default = iunserializer($_W['account']['default']);
	$welcome = iunserializer($_W['account']['welcome']);
	$_W['account']['default'] = empty($default) ? $_W['account']['default'] : $default;
	$_W['account']['welcome'] = empty($welcome) ? $_W['account']['welcome'] : $welcome;
	$_W['account']['modules'] = account_module();
}

cache_load('modules');
$_W['setting'] = (array)cache_load("setting");

if (!empty($_W['setting']['basic']['template'])) {
	$_W['template']['current'] = $_W['setting']['basic']['template'];
}

$action = $_GPC['act'];
$do = $_GPC['do'];
