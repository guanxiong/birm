<?php
/**
 * 用户管理
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';

$actions = array();
$action = $_GET['act'];
if($_W['uid']) {
	$actions = array('logout', 'setting', 'wechat', 'module', 'barcode');
	if($_W['isfounder']) {
		$actions = array_merge($actions, array('create', 'edit', 'display', 'select', 'group', 'fields', 'permission'));
	}
	if (!in_array($action, $actions)) {
		header('Location: ./index.php');
	}
} else {
	$actions = array('login', 'register', 'code');
	$action = in_array($action, $actions) ? $action : 'login';
}

$controller = 'member';
require router($controller, $action);

function _login($forward = '') {
	global $_GPC;
	require_once IA_ROOT . '/source/model/member.mod.php';
	hooks('member:login:before');
	$member = array();
	$username = trim($_GPC['username']);
	if(empty($username)) {
		message('请输入要登录的用户名');
	}
	$member['username'] = $username;
	$member['password'] = $_GPC['password'];
	if(empty($member['password'])) {
		message('请输入密码');
	}
	$record = member_single($member);
	if(!empty($record)) {
		if($record['status'] == -1) {
			message('您的账号正在核合或是已经被系统禁止，请联系网站管理员解决！');
		}
		$cookie = array();
		$cookie['uid'] = $record['uid'];
		$cookie['lastvisit'] = $record['lastvisit'];
		$cookie['lastip'] = $record['lastip'];
		$cookie['hash'] = md5($record['password'] . $record['salt']);
		$session = base64_encode(json_encode($cookie));
		isetcookie('__session', $session, !empty($_GPC['rember']) ? 7 * 86400 : 0);
		$status = array();
		$status['uid'] = $record['uid'];
		$status['lastvisit'] = TIMESTAMP;
		$status['lastip'] = CLIENT_IP;
		member_update($status);
		hooks('member:login:success');
		if(empty($forward)) {
			$forward = $_GPC['forward'];
		}
		if(empty($forward)) {
			$forward = './index.php?refersh';
		}
		message("欢迎回来，{$record['username']}。", $forward);
	} else {
		message('登录失败，请检查您输入的用户名和密码！');
	}
}
