<?php 
/**
 * 微站风格管理
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
include model('rule');

$entry = array(
	'title' => '微站入口设置',
);

$reply = pdo_fetch("SELECT * FROM " . tablename('cover_reply') . ' WHERE `module` = :module AND `do` = :do AND weid = :weid', array(':module' => 'wesite', ':do' => 'channel', ':weid' => $_W['weid']));
$rule = rule_single($reply['rid']);
$rule['keywords'] = array();
if(is_array($rule['keyword'])) {
	foreach($rule['keyword'] as $kwd) {
		$rule['keywords'][] = $kwd['content'];
	}
}
$rule['keywords'] = implode(',', $rule['keywords']);
if(empty($reply)) {
	$reply = array();
}
if(empty($reply['title'])) {
	$reply['title'] = $entry['title'];
}
$entry['url'] = $reply['url'];
if (checksubmit('submit')) {
	if(trim($_GPC['keywords']) == '') {
		message('必须输入触发关键字.');
	}
	if(trim($_GPC['title']) == '') {
		message('必须输入封面标题和链接.');
	}
	$record = array(
		'weid' => $_W['weid'],
		'cid' => '',
		'name' => $reply['title'],
		'module' => 'cover',
		'status' => intval($_GPC['status']),
	);
	if (!empty($_GPC['istop'])) {
		$record['displayorder'] = 255;
	} else {
		$record['displayorder'] = intval($_GPC['displayorder']) > 254 ? 254 : intval($_GPC['displayorder']);
	}
	
	$rid = $reply['rid'];
	if (!empty($rule['rule'])) {
		pdo_update('rule', $record, array('id' => $rid));
	} else {
		pdo_insert('rule', $record);
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
			'module' => $record['module'],
			'status' => $record['status'],
			'displayorder' => $record['displayorder'],
		);
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
		$data = array(
			'weid' => $_W['weid'],
			'rid' => $rid,
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'thumb' => $_GPC['thumb'],
			'url' => create_url('mobile/channel', array('name' => 'index', 'weid' => $_W['weid'])),
			'do' => 'channel',
			'module' => 'wesite',
		);
		if (empty($reply['id'])) {
			pdo_insert('cover_reply', $data);
		} else {
			pdo_update('cover_reply', $data, array('id' => $reply['id']));
		}
		message('封面保存成功！', 'refresh', 'success');
	} else {
		message('封面保存失败, 请联系网站管理员！');
	}
}
template('rule/cover');