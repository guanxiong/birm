<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
checkaccount();
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'display') {

	$list = pdo_fetchall("SELECT * FROM ".tablename('members_group'));

} elseif ($do == 'post') {
	$id = intval($_GPC['id']);
	$modules = $_W['modules'];
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM ".tablename('members_group') . " WHERE id = :id", array(':id' => $id));

	}
		if (checksubmit('submit')) {

		$data = array(
			'price' => $_GPC['price'],
			'dprice' => $_GPC['dprice'],
			'did' => $_GPC['did'],
			'status' => $_GPC['status'],
		);
		
					if (!empty($_FILES['icon']['tmp_name'])) {
			if (!empty($elan['icon'])) {
				file_delete($elan['icon']);
			}
			$upload = file_upload($_FILES['icon']);
			if (is_error($upload)) {
				message($upload['message']);
			}
			$data['icon'] = $upload['path'];
		}

			pdo_update('members_group', $data, array('id' => $id));
		message('用户组更新成功！', create_url('finance/group/display'), 'success');
	}
	

}
template('finance/group');