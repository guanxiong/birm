<?php 
if (empty($_GPC['name'])) {
	message('抱歉，模块不存在或是已经被禁用！');
}

$modulename = !empty($_GPC['name']) ? $_GPC['name'] : 'basic';
$exist = false;

if (!empty($_W['modules'])) {
	foreach($_W['modules'] as $m) {
		if(strtolower($m['name']) == $modulename) {
			$exist = true;
			break;
		}
	}
}
if(!$exist) {
	message('抱歉，你操作的模块不能被访问！');
}
if(!$_W['isajax'] && !$_W['ispost'] && !isset($_GPC['force'])) {
	message("模块操作入口推荐使用 site.php, 当前还可以继续访问, 但是推荐更新你的代码, 微新星系统将在下一个版本不支持这种操作调用方式. <hr />请修改你的模块以便使用新的操作入口机制(将你定义在 module.php 里的操作 do{$_GPC['do']} 移动至 site.php 并命名为 doWeb{$_GPC['do']}. 并使用 create_url('site/module/{$_GPC['do']}', array('name' => '{$_GPC['name']}', 'state' => '{$_GPC['state']}')) 来获得新的访问入口地址) . 如果你不能理解这里的内容, 请将这个提示信息提供给你的模块开发者.", '?' . $_SERVER['QUERY_STRING'] . '&force=1');
}

$module = WeUtility::createModule($modulename);
if (is_error($module)) {
	exit($module['message']);
}

$method = 'do'.$_GPC['do'];
if (method_exists($module, $method)) {
	$rid = intval($_GPC['id']);
	$state = trim($_GPC['state']);
	exit(@$module->$method($rid, $state));
} else {
	exit("访问的方法 {$method} 不存在.");
}
