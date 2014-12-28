<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$module = $_GPC['module'];
$exist = false;
foreach($_W['account']['modules'] as $m) {
	if(strtolower($m['name']) == $module) {
		$exist = true;
		break;
	}
}
if(!$exist) {
	message('抱歉，你操作的模块不能被访问！');
}
$m = $_W['modules'][$module];
if($m['isrulesingle']) {
	$sql = 'SELECT `id` FROM ' . tablename('rule') . ' WHERE `weid`=:weid AND `module`=:module';
	$pars = array();
	$pars[':weid'] = $_W['weid'];
	$pars[':module'] = $module;
	$r = pdo_fetch($sql, $pars);
	if($r) {
		header('location:' . create_url('rule/post', array('id' => $r['id'])));
	} else {
		header('location:' . create_url('rule/post', array('module' => $module, 'name' => $m['title'].'访问入口设置')));
	}
} else {
	header('location:' . create_url('rule/display', array('module' => $module)));
}
