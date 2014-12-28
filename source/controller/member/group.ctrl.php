<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

if ($do == 'display') {
	if (checksubmit('submit')) {
		if (!empty($_GPC['delete'])) {
			pdo_query("DELETE FROM ".tablename('members_group')." WHERE id IN ('".implode("','", $_GPC['delete'])."')");
		}
		message('用户组更新成功！', referer(), 'success');
	}
	$list = pdo_fetchall("SELECT * FROM ".tablename('members_group'));
	if (!empty($list)) {
		foreach ($list as &$row) {
			if (!empty($row['modules'])) {
				$modules = iunserializer($row['modules']);
				if (is_array($modules)) {
					$row['modules'] = pdo_fetchall("SELECT name, title FROM ".tablename('modules')." WHERE mid IN ('".implode("','", $modules)."')");
				}
			}
			if (!empty($row['templates'])) {
				$templates = iunserializer($row['templates']);
				if (is_array($templates)) {
					$row['templates'] = pdo_fetchall("SELECT name, title FROM ".tablename('site_templates')." WHERE id IN ('".implode("','", $templates)."')");
				}
			}
		}
	}
} elseif ($do == 'post') {
	$id = intval($_GPC['id']);
	$modules = $_W['modules'];
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM ".tablename('members_group') . " WHERE id = :id", array(':id' => $id));
		$item['modules'] = iunserializer($item['modules']);
		$item['templates'] = iunserializer($item['templates']);
	}
	$templates  = pdo_fetchall("SELECT * FROM ".tablename('site_templates'));
	if (checksubmit('submit')) {
		if (empty($_GPC['name'])) {
			message('请输入用户组名称！');
		}
		$data = array(
			'name' => $_GPC['name'],
			'modules' => iserializer($_GPC['modules']),
			'templates' => iserializer($_GPC['templates']),
			'maxaccount' => intval($_GPC['maxaccount']),
			'maxsubaccount' => intval($_GPC['maxsubaccount']),
		);
		if (empty($id)) {
			pdo_insert('members_group', $data);
		} else {
			pdo_update('members_group', $data, array('id' => $id));
		}
		message('用户组更新成功！', create_url('member/group/display'), 'success');
	}
}
template('member/group');