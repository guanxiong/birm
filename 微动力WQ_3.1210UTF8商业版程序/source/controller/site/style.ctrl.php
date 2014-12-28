<?php 
/**
 * 微站风格管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$templateid = intval($_GPC['templateid']);

if ($do == 'default') {
	$template = array();
	if ($_W['isfounder']) {
		$template = pdo_fetch("SELECT * FROM ".tablename('site_templates')." WHERE id = '{$templateid}'");
	} else {
		$templatename = pdo_fetchcolumn("SELECT name FROM ".tablename('site_templates')." WHERE id = '{$templateid}'");
		if ($templatename == 'default') {
			$template = true;
		} else {
			$grouptemplates = pdo_fetch("SELECT templates FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $_W['member']['groupid']));
			if (!empty($grouptemplates['templates'])) {
				$grouptemplates['templates'] = iunserializer($grouptemplates['templates']);
			}
			if (!empty($grouptemplates['templates']) && in_array($templateid, $grouptemplates['templates'])) {
				$template = true;
			} else {
				$temp = pdo_fetch("SELECT id FROM ".tablename('members_permission')." WHERE uid = '{$_W['uid']}' AND resourceid = :resourceid AND type = '2'", array(':resourceid' => $templateid));
				if (!empty($temp['id'])) {
					$template = true;
				}
			}
		}
	}
	if (empty($template)) {
		message('抱歉，模板不存在或是您无权限使用！', '', 'error');
	}
	pdo_update('wechats', array('styleid' => $templateid), array('weid' => $_W['weid']));
	message('默认模板更新成功！', create_url('site/style'), 'success');
} elseif ($do == 'designer') {
	$template = pdo_fetch("SELECT * FROM ".tablename('site_templates')." WHERE id = '{$templateid}'");
	if (empty($template)) {
		message('抱歉，模板不存在或是已经被删除！', '', 'error');
	}
	$styles = pdo_fetchall("SELECT variable, content FROM ".tablename('site_styles')." WHERE templateid = :templateid  AND weid = '{$_W['weid']}'", array(':templateid' => $templateid), 'variable');
	if (checksubmit('submit')) {
		if (!empty($_GPC['style'])) {
			foreach ($_GPC['style'] as $variable => $value) {
				if (!empty($value)) {
					if (!empty($styles[$variable])) {
						if ($styles[$variable] != $value) {
							pdo_update('site_styles', array('content' => $value), array('templateid' => $templateid, 'variable' => $variable, 'weid' => $_W['weid']));
						}
						unset($styles[$variable]);
					} else {
						pdo_insert('site_styles', array('content' => $value, 'templateid' => $templateid, 'variable' => $variable, 'weid' => $_W['weid']));
					}
				}
			}
		}
		if (!empty($_GPC['custom']['name'])) {
			foreach ($_GPC['custom']['name'] as $i => $variable) {
				$value = $_GPC['custom']['value'][$i];
				if (!empty($value)) {
					if (!empty($styles[$variable])) {
						if ($styles[$variable] != $value) {
							pdo_update('site_styles', array('content' => $value), array('templateid' => $templateid, 'variable' => $variable, 'weid' => $_W['weid']));
						}
						unset($styles[$variable]);
					} else {
						pdo_insert('site_styles', array('content' => $value, 'templateid' => $templateid, 'variable' => $variable, 'weid' => $_W['weid']));
					}
				}
			}
		}
		if (!empty($styles)) {
			pdo_query("DELETE FROM ".tablename('site_styles')." WHERE variable IN ('".implode("','", array_keys($styles))."') AND weid = '{$_W['weid']}'");
		}
		message('更新风格成功！', create_url('site/style/designer', array('templateid' => $templateid)), 'success');
	}
	$systemtags = array('imgdir', 'indexbgcolor', 'indexbgimg', 'indexbgextra', 'fontfamily',
						'fontsize', 'fontcolor', 'fontnavcolor', 'linkcolor', 'css');
	template('site/style');
} elseif ($do == 'module') {
	if (empty($_W['isfounder'])) {
		message('您无权进行该操作！');
	}
	$path = IA_ROOT . '/source/modules';
	if (is_dir($path)) {
		if ($handle = opendir($path)) {
			while (false !== ($modulepath = readdir($handle))) {
				if ($modulepath != '.' && $modulepath != '..') {
					if (is_dir($path . '/' .$modulepath . '/template/mobile')) {
						if ($handle1 = opendir($path . '/' .$modulepath . '/template/mobile')) {
							while (false !== ($mobilepath = readdir($handle1))) {
								if ($mobilepath != '.' && $mobilepath != '..' && strexists($mobilepath, '.html')) {
									$templates[$modulepath][] = $mobilepath;
								}
							}
						}
					}
				}
			}
		}
	}
	template('site/style');
} elseif ($do == 'createtemplate') {
	$module = $_GPC['name'];
	$file = $_GPC['file'];
	$targetfile = IA_ROOT . '/themes/mobile/'.$_W['account']['template']. '/' .$module.'/'.$file;
	if (!file_exists($targetfile)) {
		mkdirs(dirname($targetfile));
		file_put_contents($targetfile, '<!-- 原始文件：source/modules/'.$module.'/template/mobile/'.$file.' -->');
		@chmod($targetfile, $_W['config']['setting']['filemode']);
	}
	message('操作成功！', '', 'success');
	
} else {
	if ($_W['isfounder']) {
		$templates = pdo_fetchall("SELECT * FROM ".tablename('site_templates'));
	} else {
		$grouptemplates = pdo_fetch("SELECT templates FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $_W['member']['groupid']));
		if (!empty($grouptemplates['templates'])) {
			$grouptemplates['templates'] = iunserializer($grouptemplates['templates']);
		}
		$templates = pdo_fetchall("SELECT a.* FROM ".tablename('site_templates')." AS a LEFT JOIN ".tablename('members_permission')." AS b
									 ON a.id = b.resourceid WHERE b.type = '2' AND b.uid = '{$_W['uid']}' OR a.name = 'default'" .
									(!empty($grouptemplates['templates']) ? " OR a.id IN (".implode(',', $grouptemplates['templates']).")" : ''), array(), 'id');
	}
	template('site/style');
}
