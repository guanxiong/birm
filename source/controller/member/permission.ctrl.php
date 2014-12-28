<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
include_once model('setting');

$uid = intval($_GPC['uid']);
$m = array();
$m['uid'] = $uid;
$member = member_single($m);
$founders = explode(',', $_W['config']['setting']['founder']);
if(empty($member) || in_array($m['uid'], $founders)) {
	message('访问错误.');
}

$do = $_GPC['do'];
$dos = array('deny', 'delete', 'auth', 'revo', 'revos');
$do = in_array($do, $dos) ? $do: 'edit';

if($do == 'edit') {
	require model('wechat');
	$wechats = wechat_search("`uid`='{$uid}'");

	$sql = "SELECT resourceid AS mid FROM " . tablename('members_permission') . " WHERE `uid`=:uid AND type = '1'";
	$mids = pdo_fetchall($sql, array(':uid' => $uid));
	
	//获取用户组权限
	$groupsmodules = pdo_fetch("SELECT * FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $member['groupid']));
	
	$qMids = array();
	if(!empty($mids)) {
		foreach($mids as $row) {
			array_push($qMids, $row['mid']);
		}
	}
	if (!empty($groupsmodules['modules'])) {
		$groupsmodules['modules'] = iunserializer($groupsmodules['modules']);
		foreach ($groupsmodules['modules'] as $row) {
			array_push($qMids, $row);
		}
	}
	$sql = 'SELECT * FROM ' . tablename('modules') . " WHERE `issystem`=1";
	if(!empty($qMids)) {
		$mids = implode(',', $qMids);
		$sql .= " OR `mid` IN ({$mids})";
	}
	$modules = pdo_fetchall($sql);
	$groups = pdo_fetchall("SELECT id, name FROM ".tablename('members_group')." ORDER BY id ASC");
	
	$sql = "SELECT resourceid FROM " . tablename('members_permission') . " WHERE `uid`=:uid AND type = '2'";
	$tids = pdo_fetchall($sql, array(':uid' => $uid));
	
	$qMids = array();
	if(!empty($mids)) {
		foreach($tids as $row) {
			array_push($qMids, $row['resourceid']);
		}
	}
	
	if (!empty($groupsmodules['templates'])) {
		$groupsmodules['templates'] = (array)iunserializer($groupsmodules['templates']);
		foreach ($groupsmodules['templates'] as $row) {
			array_push($qMids, $row);
		}
	}
	$sql = 'SELECT * FROM ' . tablename('site_templates') . " WHERE name = 'default'";
	if(!empty($qMids)) {
		$tids = implode(',', $qMids);
		$sql .= " OR `id` IN ({$tids})";
	}
	$templates = pdo_fetchall($sql);
	template('member/permission');
}

if($do == 'deny') {
	if($_W['ispost'] && $_W['isajax']) {
		$founders = explode(',', $_W['config']['setting']['founder']);
		if(in_array($uid, $founders)) {
			exit('管理员用户不能禁用.');
		}
		$member = array();
		$member['uid'] = $uid;
		$status = $_GPC['status'];
		$member['status'] = $status == '-1' ? '-1' : '0';
		if(member_update($member)) {
			exit('success');
		}
	}
}
if($do == 'auth') {
	$mod = $_GPC['mod'];
	if($mod == 'account') {
		$weid = intval($_GPC['wechat']);
		if(empty($weid)) {
			exit('error');
		}

		if($member['status'] == '-1') {
			exit('此用户已经被禁用. ');
		}
		$wechat = array();
		$wechat['uid'] = $uid;

		if(pdo_update('wechats', $wechat, array('weid' => $weid))) {
			pdo_delete('wechats_modules', array('weid' => $weid));
			exit('success');
		} else {
			exit('error');
		}
	}
	if($mod == 'module') {
		$mid = intval($_GPC['mid']);
		$sql = 'SELECT * FROM ' . tablename('modules') . " WHERE `mid`='{$mid}'";
		$module = pdo_fetch($sql);
		if(empty($module) || $module['issystem']) {
			exit('不存在的模块, 或者此模块是系统模块, 不能操作.');
		}

		$sql = 'SELECT id FROM ' . tablename('members_permission') . " WHERE `uid`='{$uid}' AND `resourceid`='{$mid}' AND type = '1'";
		$mapping = pdo_fetch($sql);
		if(empty($mapping)) {
			$record = array();
			$record['uid'] = $uid;
			$record['resourceid'] = $mid;
			$record['type'] = 1;
			if(pdo_insert('members_permission', $record)) {
				pdo_query("DELETE FROM ".tablename('wechats_modules')." WHERE mid = '$mid' AND weid IN (SELECT weid FROM ".tablename('wechats')." WHERE uid = '$uid')");
				exit('success');
			}
		}
		exit('error');
	}
	if($mod == 'template') {
		$id = intval($_GPC['mid']);
		$sql = 'SELECT * FROM ' . tablename('site_templates') . " WHERE `id`='{$id}'";
		$template = pdo_fetch($sql);
		if(empty($template) || $template['name'] == 'default') {
			exit('不存在的模板, 或者此模板是系统模板, 不能操作.');
		}
	
		$sql = 'SELECT id FROM ' . tablename('members_permission') . " WHERE `uid`='{$uid}' AND `resourceid`='{$id}' AND type = '2'";
		$mapping = pdo_fetch($sql);
		if(empty($mapping)) {
			$record = array();
			$record['uid'] = $uid;
			$record['resourceid'] = $id;
			$record['type'] = 2;
			if(pdo_insert('members_permission', $record)) {
				exit('success');
			}
		}
		exit('error');
	}
}

if($do == 'revo') {
	$mod = $_GPC['mod'];
	if($mod == 'account') {
		$weid = intval($_GPC['wechat']);
		if(empty($weid)) {
			exit('error');
		}

		$wechat = array();
		$wechat['uid'] = $_W['uid'];

		if(pdo_update('wechats', $wechat, array('weid' => $weid))) {
			exit('success');
		} else {
			exit('error');
		}
	}
	if($mod == 'module') {
		$mid = intval($_GPC['mid']);
		$sql = 'SELECT * FROM ' . tablename('modules') . " WHERE `mid`='{$mid}'";
		$module = pdo_fetch($sql);
		if(empty($module) || $module['issystem']) {
			exit('不存在的模块, 或者此模块是系统模块, 不能操作.');
		}
		$record = array();
		$record['uid'] = $uid;
		$record['resourceid'] = $mid;
		$record['type'] = 1;
		if(pdo_delete('members_permission', $record)) {
			pdo_query("DELETE FROM ".tablename('wechats_modules')." WHERE mid = '$mid' AND weid IN (SELECT weid FROM ".tablename('wechats')." WHERE uid = '$uid')");
			//收回授权后，删除该模块的导航菜单
			$wechats = pdo_fetchall("SELECT weid FROM ".tablename('wechats')." WHERE uid = '$uid'", array(), 'weid');
			if (!empty($wechats)) {
				pdo_query("DELETE FROM ".tablename('site_nav')." WHERE module = '{$module['name']}' AND weid IN (".implode(',', array_keys($wechats)).")");
			}
			exit('success');
		}
		exit('error');
	}
	if($mod == 'template') {
		$id = intval($_GPC['mid']);
		$sql = 'SELECT * FROM ' . tablename('site_templates') . " WHERE `id`='{$id}'";
		$template = pdo_fetch($sql);
		if(empty($template) || $template['name'] == 'default') {
			exit('不存在的模板, 或者此模板是系统模板, 不能操作.');
		}
	
		$record = array();
		$record['uid'] = $uid;
		$record['resourceid'] = $id;
		$record['type'] = 2;
		if(pdo_delete('members_permission', $record)) {
			exit('success');
		}
		exit('error');
	}
}

if($do == 'revos') {
	$mod = $_GPC['mod'];
	if($mod == 'account') {
		$uid = $_W['uid'];
		$wechats = explode(',', $_GPC['wechats']);
		$weids = array();
		foreach($wechats as $w) {
			$weid = intval($w);
			if($weid) {
				array_push($weids, $weid);
			}
		}
		$weids = implode(',', $weids);
		$sql = 'UPDATE ' . tablename('wechats') . " SET `uid`=:uid WHERE `weid` IN ({$weids})";
		$params = array();
		$params[':uid'] = $uid;
		if(pdo_query($sql, $params)) {
			exit('success');
		} else {
			exit('error');
		}
	}
	if($mod == 'module') {
		$mids = explode(',', $_GPC['mids']);
		$ms = array();
		foreach($mids as $w) {
			$mid = intval($w);
			if($mid) {
				array_push($ms, $mid);
			}
		}
		$sql = 'DELETE FROM ' . tablename('members_permission') . " WHERE `uid`='{$uid}' AND type = '1' AND `resourceid` IN (".implode(',', $ms).")";
		if(pdo_query($sql)) {
			//收回授权后，删除该模块的导航菜单
			$modules = pdo_fetchall("SELECT mid, name FROM ".tablename('modules')." WHERE mid IN (".implode(',', $ms).")", array(), 'name');
			if (!empty($modules)) {
				$wechats = pdo_fetchall("SELECT weid FROM ".tablename('wechats')." WHERE uid = '$uid'", array(), 'weid');
				pdo_query("DELETE FROM ".tablename('site_nav')." WHERE module IN ('".implode("','", array_keys($modules))."') AND weid IN (".implode(',', array_keys($wechats)).")");
			}
			exit('success');
		}
		exit('error');
	}
	if($mod == 'template') {
		$ids = explode(',', $_GPC['mids']);
		$templateids = array();
		if (!empty($ids)) {
			foreach ($ids as $id) {
				$id = intval($id);
				if($id) {
					array_push($templateids, $id);
				}
			}
		}
		if (!empty($templateids)) {
			$sql = 'DELETE FROM ' . tablename('members_permission') . " WHERE `uid`='{$uid}' AND type = '2' AND `resourceid` IN (".implode(',', $templateids).")";
			pdo_query($sql);
			exit('success');
		}
		exit('error');
	}
}

