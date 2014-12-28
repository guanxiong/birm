<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$do = !empty($_GPC['do']) && in_array($_GPC['do'], array('profile')) ? $_GPC['do'] : 'profile';

if ($do == 'profile') {
	if (checksubmit('submit')) {
		$sql = "SELECT username, password, salt FROM " . tablename('members') . " WHERE `username` = '{$_GPC['name']}'";
		$user = pdo_fetch($sql);
		if (empty($user)) {
			message('抱歉，用户不存在或是已经被删除！', create_url('setting/profile'), 'error');
		}
		if (empty($_GPC['name']) || empty($_GPC['pw']) || empty($_GPC['pw2'])) {
			message('管理账号或者密码不能为空，请重新填写！', create_url('setting/profile'), 'error');
		}
		if ($_GPC['pw'] == $_GPC['pw2']) {
			message('新密码与原密码一致，请检查！', create_url('setting/admin'), 'right');
		}
		$password_old = member_hash($_GPC['pw'], $user['salt']);
		if ($user['password'] != $password_old) {
			message('原密码错误，请重新填写！', create_url('setting/profile'), 'error');
		}
		$result = '';
		$members = array(
			'username' => $_GPC['name'],
			'password' => member_hash($_GPC['pw2'], $user['salt']),
		);
		$result = pdo_update('members', $members, array('uid' => $_W['uid']));
		message('修改成功！', create_url('index'), 'success');
	}
	template('setting/profile');	
}