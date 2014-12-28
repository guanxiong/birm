<?php
/**
 * 系统回复
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
$do = !empty($_GPC['do']) && in_array($_GPC['do'], array('display', 'set', 'cancel', 'message')) ? $_GPC['do'] : 'display';
if ($do == 'display') {
	if (checksubmit('submit')) {
		$settings = array(
			'default_period' => intval($_GPC['default-period']),
		);
		isset($_GPC['default']) && $settings['default'] = $_GPC['default'];
		isset($_GPC['welcome']) && $settings['welcome'] = $_GPC['welcome'];
		if (!empty($_GPC['welcomekeyword'])) {
			$keyword = pdo_fetch("SELECT rid, module FROM ".tablename('rule_keyword')." WHERE content = :content AND weid = :weid", array(':weid' => $_W['weid'], ':content' => $_GPC['welcomekeyword']));
			$settings['welcome'] = serialize(array(
				'module' => $keyword['module'],
				'id' => $keyword['rid'],
			));
		}
		if (!empty($_GPC['defaultkeyword'])) {
			$keyword = pdo_fetch("SELECT rid, module FROM ".tablename('rule_keyword')." WHERE content = :content AND weid = :weid", array(':weid' => $_W['weid'], ':content' => $_GPC['defaultkeyword']));
			$settings['default'] = serialize(array(
				'module' => $keyword['module'],
				'id' => $keyword['rid'],
			));
		}
		pdo_update('wechats', $settings, array('weid' => $_W['weid']));
		message('系统回复更新成功！', create_url('rule/system'));
	} else {
		include model('rule');
		if (is_array($_W['account']['default'])) {
			$wechat['default'] = rule_single($_W['account']['default']['id']);
			$wechat['defaultrid'] = $_W['account']['default']['id'];
		}
		if (is_array($_W['account']['welcome'])) {
			$wechat['welcome'] = rule_single($_W['account']['welcome']['id']);
			$wechat['welcomerid'] = $_W['account']['welcome']['id'];
		}
		template('rule/system');
	}
} elseif ($do == 'message') {
	$mtypes = array();
	$mtypes['image'] = '图片消息';
	$mtypes['voice'] = '语音消息';
	$mtypes['video'] = '视频消息';
	$mtypes['location'] = '位置消息';
	$mtypes['link'] = '链接消息';
	$mtypes['subscribe'] = '粉丝开始关注';
	if(checksubmit()) {
		$s = array_elements(array_keys($mtypes), $_GPC);
		$ms = array();
		foreach($_W['account']['modules'] as $m) {
			$ms[] = $m['name'];
		}
		foreach($s as $k => $v) {
			if($v != '' && !in_array($v, $ms)) {
				message($mtypes[$k] . "选择的处理模块无效. ");
			}
		}
		$row = array();
		$row['default_message'] = iserializer($s);
		if(pdo_update('wechats', $row, array('weid' => $_W['weid']))) {
			message('保存特殊类型消息处理成功.', 'refresh');
		} else {
			message('保存失败, 请稍后重试. ');
		}
	}

	$ds = array();
	foreach($mtypes as $k => $v) {
		$row = array();
		$row['type'] = $k;
		$row['title'] = $v;
		$row['handles'] = array();
		foreach($_W['account']['modules'] as $m) {
			if(is_array($_W['modules'][$m['name']]['handles']) && in_array($k, $_W['modules'][$m['name']]['handles'])) {
				$row['handles'][] = array('name' => $m['name'], 'title' => $_W['modules'][$m['name']]['title']);
			}
		}
		$row['empty'] = empty($row['handles']);
		$row['current'] = is_array($_W['account']['default_message']) ? $_W['account']['default_message'][$k] : '';
		$ds[] = $row;
	}
	template('rule/message');
} elseif ($do == 'set') {
	$rid = intval($_GPC['id']);
	$rule = pdo_fetch("SELECT id, module FROM ".tablename('rule')." WHERE id = :id", array(':id' => $rid));
	if (empty($rule)) {
		message('抱歉，要设置的规则不存在或是已经被删除！', '', 'error');
	}
	$value = iserializer(array(
		'module' => $rule['module'],
		'id' => $rid, 	
	));
	if ($_GPC['type'] == 'default') {
		$data = array(
			'default' => $value,
		);
	} elseif ($_GPC['type'] == 'welcome') {
		$data = array(
			'welcome' => $value,
		);
	}
	pdo_update('wechats', $data, array('weid' => $_W['weid']));
	message('设置系统回复更新成功！', referer(), 'success');
} elseif ($do == 'cancel') {
	if ($_GPC['type'] == 'default') {
		$data = array(
			'default' => '',
		);
	} elseif ($_GPC['type'] == 'welcome') {
		$data = array(
			'welcome' => '',
		);
	}
	pdo_update('wechats', $data, array('weid' => $_W['weid']));
	message('取消系统回复成功！', referer(), 'success');
}
