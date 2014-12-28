<?php
/**
 * [WeEngine System] Copyright (c) 2013 B2CTUI.COM
 * $sn: htdocs/source/controller/extension/theme.ctrl.php : v a9e8f6aaba62 : 2014/03/22 19:25:26 : yuan $
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('installed', 'prepared', 'install', 'refresh', 'uninstall', 'web');
$do = in_array($do, $dos) ? $do : 'installed';
include model('extension');

if($do == 'installed') {
	$templateids = array();
	$templates = pdo_fetchall("SELECT * FROM ".tablename('site_templates'));
	foreach($templates as $tpl) {
		$templateids[] = $tpl['name'];
	}
	template('extension/theme');
}
if($do == 'prepared') {
	$templateids = array();
	$templates = pdo_fetchall("SELECT * FROM ".tablename('site_templates'));
	foreach($templates as $tpl) {
		$templateids[] = $tpl['name'];
	}
	$uninstallTemplates = array();
	$path = IA_ROOT . '/themes/mobile/';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($modulepath = readdir($handle))) {
				$manifest = ext_template_manifest($modulepath);
				if(!empty($manifest) && !in_array($manifest['name'], $templateids)) {
					$uninstallTemplates[] = $manifest;
					$templateids[] = $manifest['name'];
				}
			}
		}
	}
	template('extension/theme');
}
if($do == 'install') {
	$id = $_GPC['templateid'];
	$m = ext_template_manifest($id);
	if (empty($m)) {
		message('模板安装配置文件不存在或是格式不正确！', '', 'error');
	}
	if (pdo_fetchcolumn("SELECT id FROM ".tablename('site_templates')." WHERE name = '{$m['name']}'")) {
		message('模板已经安装或是唯一标识已存在！', '', 'error');
	}
	$settings = $m['settings'];
	unset($m['settings']);

	if (pdo_insert('site_templates', $m)) {
		$templateid = pdo_insertid();
		//写入预设变量
		if (!empty($settings)) {
			foreach ($settings as $variable => $content) {
				pdo_insert('site_styles', array('variable' => $variable, 'content' => $content, 'templateid' => $templateid, 'weid' => $_W['weid']));
			}
		}
		message('模板安装成功！', create_url('extension/theme'), 'success');
	} else {
		message('模板安装失败, 请联系模板开发者！');
	}
}
if($do == 'uninstall') {
	$id = $_GPC['templateid'];
	$m = array();
	$m['name'] = $id;
	if (pdo_delete('site_templates', $m)) {
		message('模板移除成功, 你可以重新安装, 或者直接移除文件来安全删除！', referer(), 'success');
	} else {
		message('模板移除失败, 请联系模板开发者！');
	}
}
if($do == 'refresh') {

}
if($do == 'web') {
	if(checksubmit('submit')) {
		include model('setting');
		$data = array(
			'template' => $_GPC['template'],
		);

		setting_save($data, 'basic');
		message('更新设置成功！', 'refresh');
	}
	$path = IA_ROOT . '/themes/web/';
	if(is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($templatepath = readdir($handle))) {
				if ($templatepath != '.' && $templatepath != '..') {
					$template[] = $templatepath;
				}
			}
		}
	}
	template('extension/web');
}