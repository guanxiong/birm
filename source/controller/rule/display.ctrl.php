<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 * $sn: origins/source/controller/rule/display.ctrl.php : v 866195d935cc : 2014/05/16 09:42:08 : veryinf $
 */
defined('IN_IA') or exit('Access Denied');

include model('rule');
$pindex = max(1, intval($_GPC['page']));
$psize = 20;
$module = empty($_GPC['module']) ? 'all' : $_GPC['module'];
$cids = $parentcates = $list =  array();
$types = array('', '等价', '包含', '正则表达式匹配', '直接接管');

if (!empty($module)) {
	include_once model('extension');
	if (ext_module_checkupdate($module)) {
		message('系统检测到该模块有更新，请点击“<a href="'.create_url('extension/module/upgrade', array('id' => $module)).'">更新模块</a>”后继续使用！', '', 'error');
	}
}
$condition = 'weid = :weid';
$params = array();
$params[':weid'] = $_W['weid'];

if (isset($_GPC['status'])) {
	$condition .= " AND status = :status";
	$params[':status'] = intval($_GPC['status']);
}

$modules = array();
foreach($_W['account']['modules'] as $k => $v) {
	if($_W['modules'][$v['name']]['isrulefields']) {
		$modules[$v['name']] = $_W['modules'][$v['name']];
	}
}
if(isset($_GPC['keyword'])) {
	$condition .= ' AND `name` LIKE :keyword';
	$params[':keyword'] = "%{$_GPC['keyword']}%";
}
if($module != 'all') {
	$condition .= ' AND `module` = :module';
	$params[':module'] = $module;
}
$list = rule_search($condition, $params, $pindex, $psize, $total);
$pager = pagination($total, $pindex, $psize);
$bindings = array();
if($module != 'all') {
	$bindings = pdo_fetchall('SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `entry`=\'rule\' AND `module`=:module', array(':module' => $module));
} else {
	$bindings = pdo_fetchall('SELECT * FROM ' . tablename('modules_bindings') . ' WHERE `entry`=\'rule\'');
}

if (!empty($list)) {
	foreach($list as &$item) {
		$condition = '`rid`=:rid';
		$params = array();
		$params[':rid'] = $item['id'];
		$item['keywords'] = rule_keywords_search($condition, $params);
		$item['options'] = array();
		foreach($bindings as $opt) {
			if($opt['module'] == $item['module']) {
				if(!empty($opt['call'])) {
					$site = WeUtility::createModuleSite($item['module']);
					if(method_exists($site, $opt['call'])) {
						$ret = $site->$opt['call']();
						if(is_array($ret)) {
							foreach($ret as $et) {
								$et['url'] .= strexists($et['url'], '?') ? "&id={$item['id']}" : "?id={$item['id']}";
								$item['options'][] = array('title' => $et['title'], 'link' => $et['url']);
							}
						}
					}
				} else {
					$vars = array();
					$vars['eid'] = $opt['eid'];
					$vars['id'] = $item['id'];
					$link = create_url('site/entry', $vars);
					$item['options'][] = array('title' => $opt['title'], 'link' => $link);
				}
			}
		}
	}
}

$temp = iunserializer($_W['account']['default']);
if (is_array($temp)) {
	$_W['account']['default'] = $temp;
	$_W['account']['defaultrid'] = $temp['id'];
}
$temp = iunserializer($_W['account']['welcome']);
if (is_array($temp)) {
	$_W['account']['welcome'] = $temp;
	$_W['account']['welcomerid'] = $temp['id'];
}

template('rule/display');
