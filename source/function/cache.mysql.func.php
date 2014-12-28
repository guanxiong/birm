<?php
/**
 * 数据库缓存
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 取出缓存的单条数据
 * @param 缓存键名，多个层级或分组请使用:隔开
 * @return mixed
 */
function cache_read($key) {
	$sql = 'SELECT `value` FROM ' . tablename('cache') . ' WHERE `key`=:key';
	$params = array();
	$params[':key'] = $key;
	$val = pdo_fetchcolumn($sql, $params);
	return iunserializer($val);
}

/**
 * 检索缓存中指定层级或分组的所有缓存
 * @param 缓存分组
 * @return array
 */
function cache_search($prefix) {
	$sql = 'SELECT * FROM ' . tablename('cache') . ' WHERE `key` LIKE :key';
	$params = array();
	$params[':key'] = "{$prefix}%";
	$rs = pdo_fetchall($sql, $params);
	$result = array();
	foreach((array)$rs as $v) {
		$result[$v['key']] = iunserializer($v['value']);
	}
	return $result;
}

function cache_write($key, $data) {
	if (empty($key) || !isset($data)) {
		return false;
	}
	$record = array();
	$record['key'] = $key;
	$record['value'] = iserializer($data);
	$tmp = &cache_global($key);
	$tmp = $data;
	return pdo_insert('cache', $record, true);
}

function cache_delete($key) {
	$sql = 'DELETE FROM ' . tablename('cache') . ' WHERE `key`=:key';
	$params = array();
	$params[':key'] = $key;
	$result = pdo_query($sql, $params);
	if($result) {
		$tmp = &cache_global($key);
		$tmp = null;
	}
	return $result;
}

function cache_clean($prefix = '') {
	global $_W;
	if(empty($prefix)) {
		$sql = 'DELETE FROM ' . tablename('cache');
		$result = pdo_query($sql);
		if($result) {
			unset($_W['cache']);
		}
	} else {
		$sql = 'DELETE FROM ' . tablename('cache') . ' WHERE `key` LIKE :key';
		$params = array();
		$params[':key'] = "{$prefix}:%";
		$result = pdo_query($sql, $params);
		if($result) {
			$tmp = &cache_global($key);
			$tmp = null;
		}
	}
	return $result;
}
