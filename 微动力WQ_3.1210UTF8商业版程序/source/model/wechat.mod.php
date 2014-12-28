<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

function wechat_all($availableOnly = true) {
	global $_W;
	if($availableOnly) {
		$condition = $_W['isfounder'] ? '' : "uid = '{$_W['uid']}'";
	}
	return wechat_search($condition);
}

function wechat_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	$where = '';
	if(!empty($condition)) {
		$where .= " WHERE {$condition}";
	}
	$sql = "SELECT * FROM " . tablename('wechats') . " $where ORDER BY `weid` DESC";
	if($pindex > 0) {
		// 需要分页
		$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('wechats') . $where, $params);
	}
	return pdo_fetchall($sql, $params, 'weid');
}
