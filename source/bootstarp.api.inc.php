<?php 
/**
 * 微动力接口初始化文件
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
include_once model('rule');

$sql = "SELECT * FROM " . tablename('wechats') . " WHERE `hash`=:hash LIMIT 1";
$_W['account'] = pdo_fetch($sql, array(':hash' => $_GPC['hash']));
$_W['account']['default_message'] = iunserializer($_W['account']['default_message']);
$_W['account']['access_token'] = iunserializer($_W['account']['access_token']);
$_W['account']['payment'] = iunserializer($_W['account']['payment']);

if(empty($_W['account'])) {
	exit('initial error hash');
}

if(empty($_W['account']['token'])) {
	exit('initial missing token');
}

$_W['weid'] = $_W['account']['weid'];
$_W['uid'] = $_W['account']['uid'];
$_W['account']['modules'] = array();
$_W['isfounder'] = in_array($_W['uid'], (array)explode(',', $_W['config']['setting']['founder'])) ? true : false;

cache_load('modules');
$_W['setting'] = (array)cache_load("setting");

$rs = pdo_fetchall("SELECT mid,settings,enabled FROM ".tablename('wechats_modules')." WHERE weid = '{$_W['weid']}'", array(), 'mid');
$accountmodules = array();
$disabledmodules = array();
foreach($rs as $k => &$m) {
	if(!$m['enabled']) {
		$disabledmodules[$m['mid']] = $m['mid'];
		continue;
	} else {
		$accountmodules[$m['mid']] = array(
			'mid' => $m['mid'],
			'config' => iunserializer($m['settings']),
		);
	}
}
if ($_W['isfounder']) {
	$membermodules = pdo_fetchall("SELECT mid, name, issystem FROM ".tablename('modules') . (!empty($disabledmodules) ? " WHERE mid NOT IN (".implode(',', array_keys($disabledmodules)).")" : '') . " ORDER BY issystem DESC, mid ASC", array(), 'mid');
} else {
	$membermodules = pdo_fetchall("SELECT resourceid FROM ".tablename('members_permission')." WHERE uid = :uid AND type = '1'".(!empty($disabledmodules) ? " AND resourceid NOT IN (".implode(',', array_keys($disabledmodules)).")" : '')." ORDER BY resourceid ASC", array(':uid' => $_W['uid']), 'resourceid');
}

if (!empty($_W['modules'])) {
	$groupid = pdo_fetchcolumn("SELECT groupid FROM ".tablename('members')." WHERE uid = :uid", array(':uid' => $_W['uid']));
	$groupsmodules = pdo_fetch("SELECT modules FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $groupid));
	if (!empty($groupsmodules['modules'])) {
		$groupsmodules['modules'] = iunserializer($groupsmodules['modules']);
	}
	foreach ($_W['modules'] as $name => $module) {
		if (isset($membermodules[$module['mid']]) || !empty($module['issystem']) || in_array($module['mid'], (array)$groupsmodules['modules'])) {
			$modulesimple = array(
				'mid' => $module['mid'],
				'name' => $module['name'],
				'title' => $module['title'],
			);
			$_W['account']['modules'][$module['name']] = $module;
			if($accountmodules[$module['mid']]['config']) {
				$_W['account']['modules'][$module['name']]['config'] = $accountmodules[$module['mid']]['config'];
			}
		}
	}
}
unset($membermodules);
unset($_W['modules']);
