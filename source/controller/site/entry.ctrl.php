<?php 
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 * $sn: origins/source/controller/site/entry.ctrl.php : v 866195d935cc : 2014/05/16 09:42:08 : veryinf $
 */
defined('IN_IA') or exit('Access Denied');
$eid = intval($_GPC['eid']);
$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `eid`=:eid';
$entry = pdo_fetch($sql, array(':eid' => $eid));
if(empty($entry) || !empty($entry['call'])) {
	message('非法访问.');
}

if (!empty($entry['module'])) {
	include_once model('extension');
	if (ext_module_checkupdate($entry['module'])) {
		message('系统检测到该模块有更新，请点击“<a href="'.create_url('extension/module/upgrade', array('id' => $entry['module'])).'">更新模块</a>”后继续使用！', '', 'error');
	}
}
if(!$entry['direct']) {
	checklogin();
	checkaccount();
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
$_GPC['__entry'] = $entry['title'];
$_GPC['__state'] = $entry['state'];

if (!empty($_W['modules'][$entry['module']]['handles']) && (count($_W['modules'][$entry['module']]['handles']) > 1 || !in_array('text', $_W['modules'][$entry['module']]['handles']))) {
	$handlestips = true;
}
$module = WeUtility::createModule($entry['module']);
if(!is_error($module)) {
	$method = 'do' . ucfirst($entry['do']);
	if (method_exists($module, $method)) {
		exit($module->$method());
	}
}

$site = WeUtility::createModuleSite($entry['module']);
if(!is_error($site)) {
	$site->module = array_merge($_W['modules'][$entry['module']], $_W['account']['modules'][$_W['modules'][$entry['module']]['mid']]);
	$site->weid = $_W['weid'];
	$site->inMobile = false;
	$method = 'doWeb' . ucfirst($entry['do']);
	if (method_exists($site, $method)) {
		exit($site->$method());
	}
}

exit("访问的方法 {$method} 不存在.");
