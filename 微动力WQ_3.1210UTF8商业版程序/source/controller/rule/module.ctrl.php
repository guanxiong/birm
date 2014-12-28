<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
include model('rule');

if (empty($_GPC['name'])) {
	message('抱歉，模块不存在或是已经被！');	
}
$modulename = !empty($_GPC['name']) ? $_GPC['name'] : 'basic';
$module = WeUtility::createModule($modulename);

if (is_error($module)) {
	exit($module['message']);
}

if ($_GPC['do'] == 'display') {
	$rid = intval($_GPC['id']);
	exit($module->fieldsFormDisplay($rid));
} else {
	$method = 'do'.$_GPC['do'];
	if (method_exists($module, $method)) {
		exit($module->$method());
	}
}