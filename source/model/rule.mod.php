<?php 
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
 */
defined('IN_IA') or exit('Access Denied');
function rule_keywords_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	if(!empty($condition)) {
		$where = " WHERE {$condition} ";
	}
	$sql = 'SELECT * FROM ' . tablename('rule_keyword') . $where . ' ORDER BY displayorder DESC, `type` ASC, id DESC';
	if($pindex > 0) {
		// 需要分页
		$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule_keyword') . $where, $params);
	}
	return pdo_fetchall($sql, $params);
}

function rule_search($condition = '', $params = array(), $pindex = 0, $psize = 10, &$total = 0) {
	if(!empty($condition)) {
		$where = "WHERE {$condition}";
	}
	$sql = 'SELECT * FROM ' . tablename('rule') . $where . " ORDER BY status DESC, displayorder DESC, id ASC";
	if($pindex > 0) {
		// 需要分页
		$start = ($pindex - 1) * $psize;
		$sql .= " LIMIT {$start},{$psize}";
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('rule') . $where, $params);
	}
	return pdo_fetchall($sql, $params);
}

function rule_single($id) {
	$result = array();
	$id = intval($id);
	$result['rule'] = pdo_fetch("SELECT * FROM ".tablename('rule')." WHERE id = :id", array(':id' => $id));
	if (empty($result['rule'])) {
		return $result;
	}
	$result['keyword'] = pdo_fetchall("SELECT * FROM ".tablename('rule_keyword')." WHERE rid = :rid", array(':rid' => $id));
	return $result;
}
