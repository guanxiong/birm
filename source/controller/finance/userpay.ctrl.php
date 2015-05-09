<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$current['register'] = ' class="current"';
if(checksubmit()) {
	require_once IA_ROOT . '/source/model/member.mod.php';

	$member = array();
	$member['username'] = trim($_GPC['username']);

$data=member_single(array('username' => $member['username']));

	if($data['uid'] > 0) {
		unset($member['username']);
	$member['money'] = $_GPC['money'];
	
			$fee = intval($member['money']);
			$sql = 'UPDATE ' . tablename('members') . " SET `money`=`money`+{$fee} WHERE  `uid`=:uid";
			$pars = array();
			$pars[':uid'] = $data['uid'];
			pdo_query($sql, $pars);
			
								pdo_insert('members_paylog', array(
							'uid' => $data['uid'],
							'money' => $member['money'],
							'type' => 1,
							'msg' => "由管理员充值：".$member['money'],
							'paytime' => TIMESTAMP,
						));

		message('用户充值成功！', create_url('finance/userlist', array('uid' => $uid)));
	}
	message('充值用户失败，请稍候重试或联系网站管理员解决！');
}
	$id= intval($_GPC['id']);
	if($id){
$member=member_single(array('uid' => $id));
}
template('finance/userpay');
