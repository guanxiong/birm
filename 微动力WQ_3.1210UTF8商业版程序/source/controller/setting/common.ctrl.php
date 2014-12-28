<?php 
/**
 * BAE相关设置选项
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
include model('setting');
if (checksubmit('bae_delete_update') || checksubmit('bae_delete_install')) {
	if (!empty($_GPC['bae_delete_update'])) {
		unlink(IA_ROOT . '/data/update.lock');
	} elseif (!empty($_GPC['bae_delete_install'])) {
		unlink(IA_ROOT . '/data/install.lock');
	}
	message('操作成功！', create_url('setting/common'), 'success');
} elseif (checksubmit('submit')) {
	$mail = array(
		'username' => $_GPC['username'],
		'password' => $_GPC['password'],
		'smtp' => $_GPC['smtp'],
		'sender' => $_GPC['sender'],
		'signature' => $_GPC['signature'],
	);
	setting_save($mail, 'mail');
	setting_save(intval($_GPC['authmode']), 'authmode');
	if (!empty($_GPC['testsend']) && !empty($_GPC['receiver'])) {
		$result = ihttp_email($_GPC['receiver'], $_W['setting']['copyright']['sitename'] . '验证邮件'.date('Y-m-d H:i:s'), '如果您收到这封邮件则表示您系统的发送邮件配置成功！');
		if (is_error($result)) {
			message($result['message']);
		}
	}
	message('更新设置成功！', create_url('setting/common'));
} else {
	template('setting/common');
}
