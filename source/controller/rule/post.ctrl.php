<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
include model('rule');

$modules = array();
foreach($_W['account']['modules'] as $k => $v) {
	if(is_array($_W['modules']) && $_W['modules'][$v['name']]['isrulefields']) {
		$modules[$k] = $_W['modules'][$v['name']];
	}
}

if (checksubmit('submit')) {
	$modulename = '';
	foreach ($modules as $module) {
		if ($module['name'] == $_GPC['module']) {
			$modulename = $_GPC['module'];
			break;
		}
	}
	if (empty($modulename)) {
		message('您未启用、安装该模块或是您没有权限使用！', '', 'error');
	}

	$rid = intval($_GPC['id']);
	if (empty($_GPC['name'])) {
		message('抱歉，规则名称为必填项，请选回修改！');
	}
	$rule = array(
		'weid' => $_W['weid'],
		'cid' => '',
		'name' => $_GPC['name'],
		'module' => $modulename,
		'status' => intval($_GPC['status']),
	);
	if (!empty($_GPC['istop'])) {
		$rule['displayorder'] = 255;
	} else {
		$rule['displayorder'] = intval($_GPC['displayorder']) > 254 ? 254 : intval($_GPC['displayorder']);
	}
	//调用模块处理
	$module = WeUtility::createModule($modulename);
	if (is_error($module)) {
		message('抱歉，模块不存在请重新其它模块！');
	}
	$msg = $module->fieldsFormValidate();
	if(is_string($msg) && trim($msg) != '') {
		message($msg);
	}
	if (!empty($rid)) {
		$isexists = pdo_fetch("SELECT id, module FROM ".tablename('rule')." WHERE id = :id", array(':id' => $rid));
		if (empty($isexists)) {
			message('抱歉，要修改的规则不存在或是已经被删除！');
		}
		$rule['module'] = $isexists['module'];
		$result = pdo_update('rule', $rule, array('id' => $rid));
	} else {
		$result = pdo_insert('rule', $rule);
		$rid = pdo_insertid();
	}
	if (!empty($rid)) {
		//更新，添加，删除关键字
		$sql = 'DELETE FROM '. tablename('rule_keyword') . ' WHERE `rid`=:rid AND `weid`=:weid';
		$pars = array();
		$pars[':rid'] = $rid;
		$pars[':weid'] = $_W['weid'];
		pdo_query($sql, $pars);

		$rows = array();
		$rowtpl = array(
			'rid' => $rid,
			'weid' => $_W['weid'],
			'module' => $rule['module'],
			'status' => $rule['status'],
			'displayorder' => $rule['displayorder'],
		);
		if (!empty($_GPC['keyword-name'])) {
			foreach ($_GPC['keyword-name'] as $id => $row) {
				if (empty($row) && strlen($row) == 0 && intval($_GPC['keyword-type'][$id]) != 4) {
					continue;
				}
				$rowtpl['content'] = $row;
				$rowtpl['type'] = intval($_GPC['keyword-type'][$id]);
				if($rowtpl['type'] == 4) {
					$rowtpl['content'] = '';
				}
				$rows[md5($rowtpl['type'] . $rowtpl['content'])] = $rowtpl;
			}
		}
		if (!empty($_GPC['keyword-name-new'])) {
			foreach ($_GPC['keyword-name-new'] as $id => $row) {
				if (empty($row) && strlen($row) == 0 && intval($_GPC['keyword-type-new'][$id]) != 4) {
					continue;
				}
				$rowtpl['content'] = $row;
				$rowtpl['type'] = intval($_GPC['keyword-type-new'][$id]);
				if($rowtpl['type'] == 4) {
					$rowtpl['content'] = '';
				}
				$rows[md5($rowtpl['type'] . $rowtpl['content'])] = $rowtpl;
			}
		}
		if(!empty($_GPC['keywords'])) {
			$kwds = explode(',', trim($_GPC['keywords']));
			foreach($kwds as $kwd) {
				$kwd = trim($kwd);
				if(empty($kwd)) {
					continue;
				}
				$rowtpl['content'] = $kwd;
				$rowtpl['type'] = 1;
				$rows[md5($rowtpl['type'] . $rowtpl['content'])] = $rowtpl;
			}
		}
		foreach($rows as $krow) {
			$result = pdo_insert('rule_keyword', $krow);
		}
		$module->fieldsFormSubmit($rid);
		message('规则操作成功！', 'rule.php?act=post&id='.$rid);
	} else {
		message('规则操作失败, 请联系网站管理员！');
	}
}
$types = array(
	2 => array(
		'name' => '包含关键字',
		'description' => '用户进行交谈时，对话中包含上述关键字就执行这条规则。',
	),
	3 => array(
		'name' => '正则表达式匹配',
		'description' => "用户进行交谈时，对话内容符合述关键字中定义的模式才会执行这条规则。<br/><strong>注意：如果你不明白正则表达式的工作方式，请不要使用正则匹配</strong> <br/><strong>注意：正则匹配使用MySQL的匹配引擎，请使用MySQL的正则语法</strong> <br /><br /><strong>示例: </strong><br/><b>^微信</b>匹配以“微信”开头的语句<br /><b>微信$</b>匹配以“微信”结尾的语句<br /><b>^微信$</b>匹配等同“微信”的语句<br /><b>微信</b>匹配包含“微信”的语句<br /><b>[0-9\.\-]</b>匹配所有的数字，句号和减号<br /><b>^[a-zA-Z_]$</b>所有的字母和下划线<br /><b>^[[:alpha:]]{3}$</b>所有的3个字母的单词<br /><b>^a{4}$</b>aaaa<br /><b>^a{2,4}$</b>aa，aaa或aaaa<br /><b>^a{2,}$</b>匹配多于两个a的字符串",
	),
	4 => array(
		'name' => '直接接管',
		'description' => "如果没有比这条回复优先级更高的回复被触发，那么直接使用这条回复。<br/><strong>注意：如果你不明白这个机制的工作方式，请不要使用直接接管</strong>",
	)
);
$defaultmodule = !empty($_GPC['module']) ? $_GPC['module'] : 'basic';
if(!array_key_exists($defaultmodule, $_W['modules'])) {
	$defaultmodule = 'basic';
}
if (!empty($defaultmodule)) {
	include_once model('extension');
	if (ext_module_checkupdate($defaultmodule)) {
		message('系统检测到该模块有更新，请点击“<a href="'.create_url('extension/module/upgrade', array('id' => $defaultmodule)).'">更新模块</a>”后继续使用！', '', 'error');
	}
}
$m = $_W['modules'][$defaultmodule];
if($m['isrulesingle']) {
	$sql = 'SELECT `id` FROM ' . tablename('rule') . ' WHERE `weid`=:weid AND `module`=:module';
	$pars = array();
	$pars[':weid'] = $_W['weid'];
	$pars[':module'] = $defaultmodule;
	$r = pdo_fetch($sql, $pars);
	if($r) {
		message('这个模块属于单规则模块, 规则已经存在！', create_url('rule/post', array('id' => $r['id'])), 'error');
	}
}

$typeslabel = "'".implode("','", $types)."'";
$rid = intval($_GPC['id']);
if (!empty($rid)) {
	$rule = rule_single($rid);
	if (empty($rule['rule'])) {
		message('抱歉，您操作的规则不在存或是已经被删除！', create_url('rule/display'), 'error');
	}
	$module = $rule['rule']['module'];
	$module = WeUtility::createModule($module);
	$rule['reply'] = $module;
	$rule['kwd-adv'] = false;
	$rule['keywords'] = array();
	foreach($rule['keyword'] as $kwd) {
		if($kwd['type'] != '1') {
			$rule['kwd-adv'] = true;
		} else {
			$rule['keywords'][] = $kwd['content'];
		}
	}
	$rule['keywords'] = implode(',', $rule['keywords']);
}
template('rule/post');
