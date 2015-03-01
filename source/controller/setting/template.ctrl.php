<?php
/**
 * [WDL]Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

if ($do == 'install') {
	$id = $_GPC['templateid'];
	$m = template_manifest($id);
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
		message('模板安装成功！', create_url('setting/template'), 'success');
	} else {
		message('模板安装失败, 请联系模板开发者！');
	}
} elseif ($do == 'uninstall') {
	$id = $_GPC['templateid'];
	$m = array();
	$m['name'] = $id;
	if (pdo_delete('site_templates', $m)) {
		message('模板移除成功, 你可以重新安装, 或者直接移除文件来安全删除！', create_url('setting/template'), 'success');
	} else {
		message('模板移除失败, 请联系模板开发者！');
	}
} else {
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
				$manifest = template_manifest($modulepath);
				if(!empty($manifest) && !in_array($manifest['name'], $templateids)) {
					$uninstallTemplates[] = $manifest;
					$templateids[] = $manifest['name'];
				}
			}
		}
	}

	template('setting/template');
}

function template_manifest($path) {
	$manifest = array();
	$filename = IA_ROOT . '/themes/mobile/' . $path . '/manifest.xml';
	if (!file_exists($filename)) {
		return array();
	}
	$xml = str_replace(array('&'), array('&amp;'), file_get_contents($filename));
	$xml = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if (empty($xml)) {
		return array();
	}
	$manifest['name'] = strval($xml->identifie);
	if(empty($manifest['name']) || $manifest['name'] != $path) {
		return array();
	}
	$manifest['title'] = strval($xml->title);
	if(empty($manifest['title'])) {
		return array();
	}
	$manifest['description'] = strval($xml->description);
	$manifest['author'] = strval($xml->author);
	$manifest['url'] = strval($xml->url);
	
	if($xml->settings->item) {
		foreach($xml->settings->item as $msg) {
			$attrs = $msg->attributes();
			$manifest['settings'][trim(strval($attrs['variable']))] = trim(strval($attrs['content']));
		}
	}
	return $manifest;
}