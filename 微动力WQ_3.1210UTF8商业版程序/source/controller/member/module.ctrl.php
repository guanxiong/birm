<?php 
/**
 * 用户模块管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */

defined('IN_IA') or exit('Access Denied');
checkaccount();
$do = !empty($_GPC['do']) ? $_GPC['do'] : 'display';

$modulelist = account_module(false);
if (!empty($modulelist)) {
	foreach ($modulelist as $mid => &$module) {
		$module = array_merge($module, $_W['modules'][$module['name']]);
	}
	unset($module);
}

if($do == 'display') {
	$shortcuts = @iunserializer($_W['account']['shortcuts']);
	if (!empty($modulelist)) {
		foreach ($modulelist as $mid => &$module) {
			$module['shortcut'] = !empty($shortcuts[$module['name']]);
		}
		unset($module);
	}
	template('member/module');
} elseif ($do == 'setting') {
	$mid = intval($_GPC['mid']);
	if (!array_key_exists($mid, $modulelist)) {
		message('抱歉，你操作的模块不能被访问！');
	}
	$module = $modulelist[$mid];
	$config = $module['config'];
	if (!empty($module['handles']) && !in_array('text', $module['handles'])) {
		$handlestips = true;
	}
	$obj = WeUtility::createModule($module['name']);
	$obj->_saveing_params = array();
	$obj->_saveing_params['weid'] = $_W['weid'];
	$obj->_saveing_params['mid'] = $mid;
	$obj->settingsDisplay($config);
	exit();
} elseif ($do == 'shortcut') {
	$mid = intval($_GPC['mid']);
	if (!array_key_exists($mid, $modulelist)) {
		message('抱歉，你操作的模块不能被访问！');
	}
	$module = $modulelist[$mid];
	$shortcuts = @iunserializer($_W['account']['shortcuts']);
	if(!is_array($shortcuts)) {
		$shortcuts = array();
	}
	if($_GPC['shortcut'] == '1') {
		$shortcut = array();
		$shortcut['mid'] = $module['mid'];
		$shortcut['link'] = '';

		if($module['rule'] || $module['isrulefields']) {
			$shortcut['link'] = create_url('rule/display', array('module' => $module['name']));
		}
		$bindings = pdo_fetchall('SELECT * FROM ' . tablename('modules_bindings')." WHERE `module`=:module", array(':module' => $module['name']));
		$entries = array();
		foreach($bindings as $bind) {
			$entries[$bind['entry']][] = $bind;
		}
	   	if(empty($shortcut['link']) && $entries['cover']) {
			foreach($entries['cover'] as $opt) {
				if(empty($opt['call'])) {
					$shortcut['link'] = create_url("rule/cover", array('eid' => $opt['eid']));
					break;
				}
			}
		}
	   	if(empty($shortcut['link']) && $entries['menu']) {
			foreach($entries['menu'] as $opt) {
				if(empty($opt['call'])) {
					$shortcut['link'] = create_url("rule/cover", array('eid' => $opt['eid']));
					break;
				}
			}
		}
	   	if(empty($shortcut['link']) && (!empty($m['home']) || !empty($m['profile']) || !empty($m['shortcut']))) {
			$shortcut['link'] = create_url('site/nav', array('name' => $row['name']));
		}
	   	if(empty($shortcut['link']) && $module['settings']) {
			$shortcut['link'] = create_url('member/module/setting', array('mid' => $mg['mid']));
		}
		$shortcuts[$module['name']] = $shortcut;
		if(count($shortcuts) > 10) {
			message('不能设置超过 10 个以上的快捷操作.');
		}
	} else {
		unset($shortcuts[$module['name']]);
	}
	$record = array();
	$record['shortcuts'] = iserializer($shortcuts);
	if(pdo_update('wechats', $record, array('weid' => $_W['weid'])) !== false) {
		message('模块操作成功！', referer(), 'success');
	}
	exit();
} elseif ($do == 'enable') {
	$mid = intval($_GPC['mid']);
	if (!array_key_exists($mid, $modulelist)) {
		message('抱歉，你操作的模块不能被访问！');
	}
	$module = $modulelist[$mid];
	$exist = pdo_fetchcolumn("SELECT id FROM ".tablename('wechats_modules')." WHERE mid = :mid AND weid = :weid", array(':mid' => $mid, ':weid' => $_W['weid']));
	if (empty($exist)) {
		pdo_insert('wechats_modules', array(
			'mid' => $mid,
			'weid' => $_W['weid'],
			'enabled' => empty($_GPC['enabled']) ? 0 : 1,
		));
	} else {
		pdo_update('wechats_modules', array(
			'mid' => $mid,
			'weid' => $_W['weid'],
			'enabled' => empty($_GPC['enabled']) ? 0 : 1,
		), array('id' => $exist));
	}
	message('模块操作成功！', referer(), 'success');
} elseif ($do == 'form') {
	include model('rule');
	if (empty($_GPC['name'])) {
		message('抱歉，模块不存在或是已经被删除！');
	}
	$modulename = !empty($_GPC['name']) ? $_GPC['name'] : 'basic';
	$exist = false;
	foreach($modulelist as $m) {
		if(strtolower($m['name']) == $modulename && $m['enabled']) {
			$exist = true;
			break;
		}
	}
	if(!$exist) {
		message('抱歉，你操作的模块不能被访问！');
	}
	$m = $_W['modules'][$modulename];
	if($m['isrulesingle']) {
		$sql = 'SELECT `id` FROM ' . tablename('rule') . ' WHERE `weid`=:weid AND `module`=:module';
		$pars = array();
		$pars[':weid'] = $_W['weid'];
		$pars[':module'] = $modulename;
		$r = pdo_fetch($sql, $pars);
		if($r) {
			exit('already:' . $r['id']);
		}
	}
	$module = WeUtility::createModule($modulename);
	if (is_error($module)) {
		exit($module['message']);
	}
	$rid = intval($_GPC['id']);
	exit($module->fieldsFormDisplay($rid));
}
