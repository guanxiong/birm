<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
include model('setting');
if (checksubmit('submit')) {
	setting_save(array('open' => intval($_GPC['open']), 'verify' => intval($_GPC['verify']), 'code' => intval($_GPC['code']), 'groupid' => intval($_GPC['groupid'])), 'register');
	message('更新设置成功！', create_url('setting/register'));
}
$settings = pdo_fetch('SELECT * FROM ' . tablename('settings') . " WHERE `key` = 'register'", array(), 'key');
$settings = unserialize($settings['value']);
$groups = pdo_fetchall("SELECT id, name FROM ".tablename('members_group')." ORDER BY id ASC");
template('setting/access');
