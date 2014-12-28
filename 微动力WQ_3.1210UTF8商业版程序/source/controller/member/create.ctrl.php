<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
if(checksubmit()) {
	require_once IA_ROOT . '/source/model/member.mod.php';
	hooks('member:register:before');
	$member = array();
	$member['username'] = trim($_GPC['username']);
	if(!preg_match(REGULAR_USERNAME, $member['username'])) {
		message('必须输入用户名，格式为 3-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。');
	}
	if(member_check(array('username' => $member['username']))) {
		message('非常抱歉，此用户名已经被注册，你需要更换注册名称！');
	}
	$member['password'] = $_GPC['password'];
	if(istrlen($member['password']) < 8) {
		message('必须输入密码，且密码长度不得低于8位。');
	}
	$member['remark'] = $_GPC['remark'];
	$member['groupid'] = intval($_GPC['groupid']);
	$uid = member_register($member);
	if($uid > 0) {
		unset($member['password']);
		$member['uid'] = $uid;
		//有用户组则添加相关权限
		if (!empty($member['groupid'])) {
			$group = pdo_fetch("SELECT modules FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $member['groupid']));
			if (!empty($group['modules'])) {
				$group['modules'] = iunserializer($group['modules']);
				if (is_array($group['modules'])) {
					$modules = pdo_fetchall("SELECT mid FROM ".tablename('modules')." WHERE mid IN ('".implode("','", $group['modules'])."')");
					if (!empty($modules)) {
						foreach ($modules as $row) {
							pdo_insert('members_permission', array('uid' => $uid, 'resourceid' => $row['mid'], 'type' => 1));
						}
					}
				}
			}
		}
		hooks('member:register:success', $member);
		message('用户增加成功！', create_url('member/edit', array('uid' => $uid)));
	}
	message('增加用户失败，请稍候重试或联系网站管理员解决！');
}
$groups = pdo_fetchall("SELECT id, name FROM ".tablename('members_group')." ORDER BY id ASC");
template('member/create');
