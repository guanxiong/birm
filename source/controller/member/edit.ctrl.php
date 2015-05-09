<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
include_once model('setting');

$uid = intval($_GPC['uid']);
$m = array();
$m['uid'] = $uid;
$member = member_single($m);
$founders = explode(',', $_W['config']['setting']['founder']);
if(empty($member) || in_array($m['uid'], $founders) || !in_array($_W['uid'], $founders)) {
	message('访问错误.');
}


$do = $_GPC['do'];
$dos = array('delete', 'edit');
$do = in_array($do, $dos) ? $do: 'edit';

if ($do == 'edit') {
	$extendfields = pdo_fetchall("SELECT field, title, description, required FROM ".tablename('profile_fields')." WHERE available = '1' AND showinregister = '1'");
	if(checksubmit('profile_submit')) {
		require_once IA_ROOT . '/source/model/member.mod.php';
		$nMember = array();
		$nMember['uid'] = $uid;
		$nMember['password'] = $_GPC['password'];
		$nMember['salt'] = $member['salt'];
		$nMember['groupid'] = intval($_GPC['groupid']);
		if(!empty($nMember['password']) && istrlen($nMember['password']) < 8) {
			message('必须输入密码，且密码长度不得低于8位。');
		}
		$nMember['lastip'] = $_GPC['lastip'];
		$nMember['lastvisit'] = strtotime($_GPC['lastvisit']);
		$nMember['remark'] = $_GPC['remark'];
		member_update($nMember);
		if (!empty($extendfields)) {
			foreach ($extendfields as $row) {
				if($row['field'] != 'profile') $profile[$row['field']] = $_GPC[$row['field']];
			}
			if (!empty($profile)) {
				$exists = pdo_fetchcolumn("SELECT uid FROM ".tablename('members_profile')." WHERE uid = :uid", array(':uid' => $uid));
				if (!empty($exists)) {
					pdo_update('members_profile', $profile, array('uid' => $uid));
				} else {
					$profile['uid'] = $uid;
					pdo_insert('members_profile', $profile);
				}

			}
		}
		message('保存用户资料成功！', 'refresh');
	}
	if (!empty($extendfields)) {
		foreach ($extendfields as $row) {
			$fields[] = $row['field'];
		}
		$member['profile'] = pdo_fetch("SELECT `".implode("`,`", $fields)."` FROM ".tablename('members_profile')." WHERE uid = :uid", array(':uid' => $uid));
	}
	$groups = pdo_fetchall("SELECT id, name FROM ".tablename('members_group')." ORDER BY id ASC");
	template('member/edit');
} elseif($do == 'delete') {
	if($_W['ispost'] && $_W['isajax']) {
		$founders = explode(',', $_W['config']['setting']['founder']);
		if(in_array($uid, $founders)) {
			exit('管理员用户不能删除.');
		}
		$member = array();
		$member['uid'] = $uid;
		if(pdo_delete('members', $member) === 1) {
			//把该用户所属的公众号返给创始人
			pdo_update('wechats', array('uid' => $founders[0]), array('uid' => $uid));
			exit('success');
		}
	}
}
