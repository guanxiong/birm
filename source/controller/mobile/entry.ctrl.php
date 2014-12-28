<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$eid = intval($_GPC['eid']);
$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `eid`=:eid';
$entry = pdo_fetch($sql, array(':eid' => $eid));
if(empty($entry) || !empty($entry['call'])) {
	message('非法访问.');
}
if(!$entry['direct']) {
	$isexists = false;
	foreach($_W['account']['modules'] as $m) {
		if(strtolower($m['name']) == strtolower($entry['module'])) {
			$isexists = true;
		}
	}
	if(!$isexists) {
		message("访问非法, 没有操作权限. (module: {$entry['module']})");
	}
}
$_W['account']['quickmenu'] = iunserializer($_W['account']['quickmenu']);
if (!empty($_W['account']['quickmenu']['enablemodule']) && in_array($entry['module'], (array)$_W['account']['quickmenu']['enablemodule'])) {
	$_W['quickmenu']['template'] = !empty($_W['account']['quickmenu']['template']) ? '../quick/' . $_W['account']['quickmenu']['template'] : '../quick/default';
	$_W['quickmenu']['menus'] = mobile_nav(3);
}
if (!empty($_W['modules'][$entry['module']]['handles'])) {
	$handlestips = true;
}
$site = WeUtility::createModuleSite($entry['module']);
if(!is_error($site)) {
	$site->module = $_W['account']['modules'][$entry['module']];
	$site->weid = $_W['weid'];
	$site->inMobile = true;
	$site->do = strtolower($entry['do']);
	$method = 'doMobile' . ucfirst($entry['do']);
	$_GPC['__title'] = $entry['title'];
	$_GPC['__state'] = $entry['state'];
	if (method_exists($site, $method)) {
		exit($site->$method());
	}
}

exit("访问的方法 {$method} 不存在.");
