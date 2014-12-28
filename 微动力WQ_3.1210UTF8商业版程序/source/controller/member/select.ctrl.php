<?php 
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

defined('IN_IA') or exit('Access Denied');
$owner = intval($_GPC['owner']);
$member = pdo_fetch("SELECT * FROM ".tablename('members')." WHERE uid = :uid", array(':uid' => $owner));

$do = $_GPC['do'];
$dos = array('account', 'module', 'template');
$do = in_array($do, $dos) ? $do: 'account';

if($do == 'account') {
	require model('wechat');
	$condition = '';
	$params = array();
	if(!empty($_GPC['keyword'])) {
		$condition = '`name` LIKE :name';
		$params[':name'] = "%{$_GPC['keyword']}%";
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 5;
	$total = 0;
	$wechats = wechat_search($condition, $params, $pindex, $psize, $total);
	$owner = $_GPC['owner'];
	foreach($wechats as &$wechat) {
		$member = member_single(array('uid' => $wechat['uid']));
		$wechat['member'] = $member;
		if($wechat['uid'] == $owner) {
			$wechat['owner'] = true;
		}
	}
	$pager = pagination($total, $pindex, $psize, '', array('ajaxcallback'=>'null'));
	template('member/select');
}

if($do == 'module') {
	$sql = "SELECT resourceid AS `mid` FROM " . tablename('members_permission') . " WHERE `uid`=:uid AND type = '1'";
	$mids = pdo_fetchall($sql, array(':uid' => $owner));
	$qMids = array();
	foreach($mids as $row) {
		array_push($qMids, $row['mid']);
	}
	//获取用户组权限
	$groupsmodules = pdo_fetch("SELECT modules FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $member['groupid']));
	if (!empty($groupsmodules['modules'])) {
		$groupsmodules['modules'] = iunserializer($groupsmodules['modules']);
		foreach ($groupsmodules['modules'] as $row) {
			array_push($qMids, $row);
		}
	}
	$sql = 'SELECT * FROM ' . tablename('modules') . ' ORDER BY issystem DESC, mid ASC';
	$modules = pdo_fetchall($sql);
	foreach($modules as &$m) {
		$m['owner'] = in_array($m['mid'], $qMids);
	}
	template('member/select');
}

if ($do == 'template') {
	$sql = "SELECT resourceid FROM " . tablename('members_permission') . " WHERE `uid`=:uid AND type = '2'";
	$resourceids = pdo_fetchall($sql, array(':uid' => $owner));
	
	$qMids = array();
	if (!empty($resourceids)) {
		foreach($resourceids as $row) {
			array_push($qMids, $row['resourceid']);
		}
	}
	
	//获取用户组权限
	$groupsmodules = pdo_fetch("SELECT templates FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $member['groupid']));
	if (!empty($groupsmodules['templates'])) {
		$groupsmodules['templates'] = (array)iunserializer($groupsmodules['templates']);
		foreach ($groupsmodules['templates'] as $row) {
			array_push($qMids, $row);
		}
	}
	$sql = 'SELECT * FROM ' . tablename('site_templates') . ' ORDER BY id ASC';
	$templates = pdo_fetchall($sql);
	foreach($templates as &$m) {
		$m['owner'] = in_array($m['id'], $qMids);
	}
	template('member/select');
}
