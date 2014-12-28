<?php
/**
 * 资料字段管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'post') {
	$id = intval($_GPC['id']);
	
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('抱歉，请填写资料名称！');
		}
		$data = array(
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'displayorder' => intval($_GPC['displayorder']),
			'available' => intval($_GPC['available']),
			'unchangeable' => intval($_GPC['unchangeable']),
			'showinregister' => intval($_GPC['showinregister']),
			'required' => intval($_GPC['required']),
		);
		pdo_update('profile_fields', $data, array('id' => $id));
		message('更新粉丝字段成功！', create_url('member/fields'));
	}
	$item = pdo_fetch("SELECT * FROM ".tablename('profile_fields')." WHERE id = :id", array(':id' => $id));
	template('member/fields');
} elseif ($do == 'delete') {
	if (!empty($_GPC['name'])) {
		pdo_query("ALTER TABLE ".tablename('fans')." DROP `".$_GPC['name']."`");
	}
	message('更新粉丝字段成功！', create_url('setting/fields'));
} else {
	if (checksubmit('submit')) {
		if (!empty($_GPC['displayorder'])) {
			foreach ($_GPC['displayorder'] as $id => $displayorder) {
				pdo_update('profile_fields', array(
					'displayorder' => intval($displayorder),
					'available' => intval($_GPC['available'][$id]),
					'showinregister' => intval($_GPC['showinregister'][$id]),
					'required' => intval($_GPC['required'][$id]),
				), array('id' => $id));
			}
		}
		message('资料设置更新成功！', referer(), 'success');
	}
	$fields = pdo_fetchall("SELECT * FROM ".tablename('profile_fields')." ORDER BY displayorder DESC");
	template('member/fields');
}