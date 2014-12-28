<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if (empty($_GPC['name'])) {
	message('抱歉，模块不存在或是已经被禁用！');
}

$modulename = !empty($_GPC['name']) ? $_GPC['name'] : 'basic';
$exist = false;
foreach($_W['account']['modules'] as $m) {
	if(strtolower($m['name']) == $modulename) {
		$exist = true;
		break;
	}
}
if(!$exist) {
	message('抱歉，你操作的模块不能被访问！');
}

$_W['styles'] = mobile_styles();
$_W['account']['quickmenu'] = iunserializer($_W['account']['quickmenu']);
if (!empty($_W['account']['quickmenu']['enablemodule']) && in_array($modulename, $_W['account']['quickmenu']['enablemodule'])) {
	$_W['quickmenu']['template'] = !empty($_W['account']['quickmenu']['template']) ? '../quick/' . $_W['account']['quickmenu']['template'] : '../quick/default';
	$_W['quickmenu']['menus'] = mobile_nav(3);
}

$site = WeUtility::createModuleSite($modulename);
if (is_error($site)) {
	exit($site['message']);
}
$site->module = $_W['account']['modules'][$modulename];
$site->weid = $_W['weid'];
$site->inMobile = true;
$site->do = strtolower($_GPC['do']);

if(isset($_GPC['do'])) {
	$method = 'doMobile'.$_GPC['do'];
}
if (method_exists($site, $method)) {
	exit($site->$method());
} else {
	exit("访问的方法 {$method} 不存在.");
}
