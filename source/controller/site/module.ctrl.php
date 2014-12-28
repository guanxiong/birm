<?php 
if (empty($_GPC['name'])) {
	message('抱歉，模块不存在或是已经被禁用！');
}

$modulename = !empty($_GPC['name']) ? $_GPC['name'] : 'basic';

$sql = 'SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `module`=:module AND `do`=:do';
$entry = pdo_fetch($sql, array(':module' => $modulename, ':do' => $_GPC['do']));


if(empty($entry) || !$entry['direct']) {
	checklogin();
	checkaccount();
	$exist = false;
	foreach($_W['account']['modules'] as $m) {
		if(strtolower($m['name']) == strtolower($modulename)) {
			$exist = true;
			break;
		}
	}
	if(!$exist) {
		message('抱歉，你操作的模块不能被访问！');
	}
}
$_GPC['__title'] = $entry['title'];
$_GPC['__state'] = $entry['state'];

$module = WeUtility::createModule($modulename);
if(!is_error($module)) {
	if(isset($_GPC['do'])) {
		$method = 'do'.$_GPC['do'];
	}
	if (method_exists($module, $method)) {
		exit($module->$method());
	}
}

$site = WeUtility::createModuleSite($modulename);
if(!is_error($site)) {
	if(isset($_GPC['do'])) {
		$method = 'doWeb'.$_GPC['do'];
	}
	$site->module = array_merge($_W['modules'][$modulename], $_W['account']['modules'][$_W['modules'][$modulename]['mid']]);
	$site->weid = $_W['weid'];
	$site->inMobile = false;
	if (method_exists($site, $method)) {
		exit($site->$method());
	}
}

exit("访问的方法 {$method} 不存在.");
