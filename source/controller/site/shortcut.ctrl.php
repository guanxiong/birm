<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$do = empty($do) ? 'default' : $do;

$settingname = "quickmenu:{$_W['weid']}";
if ($do == 'default') {
	include model('setting');
	if (checksubmit('submit')) {
		$module = array();
		if (!empty($_GPC['module'])) {
			foreach ($_GPC['module'] as $row) {
				if (isset($_W['modules'][$row])) {
					$module[] = $row;
				}
			}
		}
		$quickmenu = array(
			'template' => $_GPC['template'],
			'enablemodule' => $module,
		);
		pdo_update('wechats', array('quickmenu' => iserializer($quickmenu)), array('weid' => $_W['weid']));
		message('快捷菜单模板设置成功！', create_url('site/shortcut'), 'success');
	}
	$_W['account']['quickmenu'] = iunserializer($_W['account']['quickmenu']);
	$path = IA_ROOT . '/themes/mobile/quick';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($templatepath = readdir($handle))) {
				$ext = pathinfo($templatepath);
				$ext = $ext['extension'];
				if ($templatepath != '.' && $templatepath != '..' && !empty($ext)) {
					$pathinfo = pathinfo($templatepath);
					$template[] = $pathinfo['filename'];
				}
			}
		}
	}
	//开启显示模块
	$ignore = array('basic', 'music', 'userapi', 'stat');
	template('site/shortcut');
} elseif ($do == 'preview') {
	include model('mobile');
	$_W['quickmenu']['menus'] = mobile_nav(3);
	$current = $_W['account']['template'];
	$template = !empty($_GPC['file']) ? $_GPC['file'] : 'default';
	define('IN_MOBILE', true);
	template('header');
	$_W['account']['template'] = 'quick';
	template($template);
	$_W['account']['template'] = $current;
	$footer_off = 1;
}
