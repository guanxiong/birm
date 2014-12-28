<?php
/**
 * 版权设置
 * [WeEngine System] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
include model('setting');

if (checksubmit('submit')) {
	$data = array(
		'template' => $_GPC['template'],
	);
	
	setting_save($data, 'basic');
	message('更新设置成功！', create_url('setting/style'));
}
$path = IA_ROOT . '/themes/web/';
if (is_dir($path)) {
	if ($handle = opendir($path)) {
		while (false !== ($templatepath = readdir($handle))) {
			if ($templatepath != '.' && $templatepath != '..') {
				$template[] = $templatepath;
			}
		}
	}
}
template('setting/style');