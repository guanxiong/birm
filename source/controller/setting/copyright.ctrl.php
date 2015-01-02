<?php
/**
 * 版权设置
 * [WeEngine System] Copyright (c) 2013 BIRM.CO
 */

defined('IN_IA') or exit('Access Denied');
include model('setting');
$settings = pdo_fetch('SELECT * FROM ' . tablename('settings') . " WHERE `key` = :key", array(':key' => 'copyright'));
$settings = iunserializer($settings['value']);

if (checksubmit('submit')) {
	$data = array(
		'sitename' => $_GPC['sitename'],
		'url' => strexists($_GPC['url'], 'http://') ? $_GPC['url'] : "http://{$_GPC['url']}",
		'statcode' => htmlspecialchars_decode($_GPC['statcode']),
		'footerleft' => htmlspecialchars_decode($_GPC['footerleft']),
		'footerright' => htmlspecialchars_decode($_GPC['footerright']),
		'flogo' => $_GPC['flogo_old'],
		'blogo' => $_GPC['blogo_old'],
		'lng' => $_GPC['lng'],
		'lat' => $_GPC['lat'],
		'address' => $_GPC['address'],
		'phone' => $_GPC['phone'],
		'qq' => $_GPC['qq'],
		'email' => $_GPC['email'],
		'keywords' => $_GPC['keywords'],
		'description' => $_GPC['description'],
	);
	if (!empty($_FILES['flogo']['tmp_name'])) {
		file_delete($_GPC['flogo_old']);
		$upload = file_upload($_FILES['flogo']);
		if (is_error($upload)) {
			message($upload['message'], '', 'error');
		}
		$data['flogo'] = $upload['path'];
	}

	if (!empty($_FILES['blogo']['tmp_name'])) {
		file_delete($_GPC['blogo_old']);
		$upload = file_upload($_FILES['blogo']);
		if (is_error($upload)) {
			message($upload['message'], '', 'error');
		}
		$data['blogo'] = $upload['path'];
	}
	setting_save($data, 'copyright');
	message('更新设置成功！', create_url('setting/copyright'));
}
if (checksubmit('fileupload-flogo-delete')) {
	file_delete($_GPC['fileupload-flogo-delete']);
	$settings['flogo'] = '';
	setting_save($settings, 'copyright');
	message('删除成功！', referer(), 'success');
}
if (checksubmit('fileupload-blogo-delete')) {
	file_delete($_GPC['fileupload-blogo-delete']);
	$settings['blogo'] = '';
	setting_save($settings, 'copyright');
	message('删除成功！', referer(), 'success');
}
template('setting/copyright');
