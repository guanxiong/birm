<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

if(checksubmit()) {
	require_once IA_ROOT . '/source/model/member.mod.php';
	hooks('member:register:before');
	$member = array();
	$uid = intval($_GPC['uid']);

$data=member_single(array('uid' => $uid));

	if($data['uid'] > 0) {

	$stattime = strtotime($_GPC['stattime'].' 00:00:00');
	$endtime = strtotime($_GPC['endtime'].' 23:59:59');
	$groupid = intval($_GPC['groupid']);
	

			$sql = 'UPDATE ' . tablename('members') . " SET `groupid`='{$groupid}' WHERE  `uid`=:uid";
			$pars = array();
			$pars[':uid'] = $data['uid'];
			pdo_query($sql, $pars);
			$wedata=pdo_fetch("SELECT * FROM ".tablename('members_status')." WHERE uid = '{$data['uid']}' LIMIT 1");
			if ($wedata) {
			
			    				$datastatus = array();
			$datastatus['endtime'] = $endtime;
			$datastatus['stattime'] = $stattime;
			$datastatus['uid'] = $data['uid'];
			$datastatus['gid'] = $groupid;
			$datastatus['status'] = 0;
    				pdo_update('members_status', $datastatus, array('id' => $wedata['id']));
    				//pdo_delete('members_modules', array('uid' => $data['uid']));
			
			
			}else{
			 				$datastatus = array();
			$datastatus['endtime'] = $endtime;
			$datastatus['stattime'] = $stattime;
			$datastatus['uid'] = $data['uid'];
			$datastatus['gid'] = $groupid;
			$datastatus['status'] = 0;
			pdo_insert('members_status', $datastatus);
			
			

}

								pdo_insert('members_paylog', array(
							'uid' => $data['uid'],
							'money' => 0,
							'type' => 3,
							'msg' => "由管理员开通服务 ,等级：".$groupid." 开始日期：".date('Y-m-d  h:i:s', $stattime) ."，到期时间".date('Y-m-d  h:i:s', $endtime),
							'paytime' => TIMESTAMP,
						));
		message('用户编辑成功！', create_url('finance/userlist'));
	}
	message('用户编辑失败，请稍候重试或联系网站管理员解决！');
}
	$id= intval($_GPC['id']);
	if($id){
$member=member_single(array('uid' => $id));
$wedata=pdo_fetch("SELECT * FROM ".tablename('members_status')." WHERE uid = '{$id}' LIMIT 1");
		$starttime = empty($wedata['stattime']) ? TIMESTAMP : $wedata['stattime'];
		$endtime = empty($wedata['endtime']) ? TIMESTAMP+ 86399 : $wedata['endtime'];

	$groups = pdo_fetchall("SELECT id, name FROM ".tablename('members_group')." ORDER BY id ASC");
}
template('finance/edit');
