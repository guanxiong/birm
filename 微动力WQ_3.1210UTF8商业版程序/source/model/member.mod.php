<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
/**
 * 用户注册
 * PS:密码字段不要加密
 * @param array $member 用户注册信息，需要的字段必须包括 username, password, remark
 * @return int 成功返回新增的用户编号，失败返回 0
 */
function member_register($member) {
	$member['salt'] = random(8);
	$member['joindate'] = TIMESTAMP;
	$member['password'] = member_hash($member['password'], $member['salt']);
	empty($member['status']) && $member['status'] = 0;
	$member['joinip'] = CLIENT_IP;
	$member['lastvisit'] = TIMESTAMP;
	$member['lastip'] = CLIENT_IP;
	$result = pdo_insert('members', $member);
	if($result) {
		if(empty($member['uid'])) {
			$member['uid'] = pdo_insertid();
		}
	}
	return $member['uid'];
}

/**
 * 检查用户是否存在，多个如果检查的参数包括多个字段，则必须满足所有参数条件符合才返回true
 * PS:密码字段不要加密，不能单独依靠密码查询
 * @param array $member 用户信息，需要的字段可以包括 uid, username, password, status
 * @return bool
 */
function member_check($member) {
	$sql = 'SELECT `password`,`salt` FROM ' . tablename('members') . " WHERE 1";
	$params = array();
	if(!empty($member['uid'])) {
		$sql .= ' AND `uid`=:uid';
		$params[':uid'] = intval($member['uid']);
	}
	if(!empty($member['username'])) {
		$sql .= ' AND `username`=:username';
		$params[':username'] = $member['username'];
	}
	if(!empty($member['status'])) {
		$sql .= " AND `status`=:status";
		$params[':status'] = intval($member['status']);
	}
	$sql .= " LIMIT 1";
	$record = pdo_fetch($sql, $params);
	if(!$record || empty($record['password']) || empty($record['salt'])) {
		return false;
	}
	if(!empty($member['password'])) {
		$password = member_hash($member['password'], $record['salt']);
		return $password == $record['password'];
	}
	return true;
}

/**
 * 获取单条用户信息，如果查询参数多于一个字段，则查询满足所有字段的用户
 * PS:密码字段不要加密
 * @param array $member 要查询的用户字段，可以包括  uid, username, password, status
 * @param bool 是否要同时获取状态信息
 * @return array 完整的用户信息
 */
function member_single($member) {
	$sql = 'SELECT * FROM ' . tablename('members') . " WHERE 1";
	$params = array();
	if(!empty($member['uid'])) {
		$sql .= ' AND `uid`=:uid';
		$params[':uid'] = intval($member['uid']);
	}
	if(!empty($member['username'])) {
		$sql .= ' AND `username`=:username';
		$params[':username'] = $member['username'];
	}
	if(!empty($member['email'])) {
		$sql .= ' AND `email`=:email';
		$params[':email'] = $member['email'];
	}
	if(!empty($member['status'])) {
		$sql .= " AND `status`=:status";
		$params[':status'] = intval($member['status']);
	}
	$sql .= " LIMIT 1";
	$record = pdo_fetch($sql, $params);
	if(!$record) {
		return false;
	}
	if(!empty($member['password'])) {
		$password = member_hash($member['password'], $record['salt']);
		if($password != $record['password']) {
			return false;
		}
	}
	return $record;
}

/**
 * 更新用户资料
 * PS:密码字段需要加密
 * @param array $member 用户的资料数据, 需要的字段可以包括password, status, lastvisit, lastip, remark 必须包括 uid
 * @return bool
 */
function member_update($member) {
	if(empty($member['uid'])) {
		return false;
	}
	$params = array();
	if($member['password']) {
		$params['password'] = member_hash($member['password'], $member['salt']);
	}
	if($member['lastvisit']) {
		$params['lastvisit'] = (strlen($member['lastvisit']) == 10) ? $member['lastvisit'] : strtotime($member['lastvisit']);
	}
	if($member['lastip']) {
		$params['lastip'] = $member['lastip'];
	}
	if(isset($member['joinip'])) {
		$params['joinip'] = $member['joinip'];
	}
	if(isset($member['remark'])) {
		$params['remark'] = $member['remark'];
	}
	if(isset($member['status'])) {
		$params['status'] = $member['status'];
	}
	if (isset($member['groupid'])) {
		$params['groupid'] = $member['groupid'];
	}
	if(empty($params)) {
		return false;
	}

	return pdo_update('members', $params, array('uid' => intval($member['uid'])));
}

/**
 * 计算用户密码hash
 * @param string $input 输入字符串
 * @param string $salt 附加字符串
 * @return string
 */
function member_hash($input, $salt) {
	global $_W;
	$input = "{$input}-{$salt}-{$_W['config']['setting']['authkey']}";
	return sha1($input);
}

function member_level() {
	static $level = array(
		'-3' => '锁定用户',
		'-2' => '禁止访问',
		'-1' => '禁止发言',
		'0' => '普通会员',
		'1' => '管理员',
	);
	return $level;
}
