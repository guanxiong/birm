<?php
/**
 * 缓存统一接口
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

require IA_ROOT . "/source/function/cache.{$_W['config']['setting']['cache']}.func.php";

/**
 * 读取缓存，并将缓存加载至 $_W 全局变量中
 * @param string $key 缓存键名，如果键名以:结束，则做为分组名称读取所有缓存
 *
 */
function cache_load($key, $unserialize = false) {
	global $_W;
	if(substr($key, -1) == ':') {
		$data = cache_search($key);
		foreach($data as $k => $v) {
			$tmp = &cache_global($k);
			$tmp = $unserialize ? iunserializer($v) : $v;
		}
		return cache_global($key);
	} else {
		$data = cache_read($key);
		if ($key == 'setting') {
			$_W['setting'] = $data;
			return $_W['setting'];
		} elseif ($key == 'modules') {
			$_W['modules'] = $data;
			return $_W['modules'];
		} else {
			$tmp = &cache_global($key);
			$tmp = $unserialize ? iunserializer($data) : $data;
			return $unserialize ? iunserializer($data) : $data;
		}
	}
}

function &cache_global($key) {
	global $_W;
	$keys = explode(':', $key);
	$tmp = &$_W['cache'];
	foreach($keys as $v) {
		if(empty($v)) {
			continue;
		}
		$tmp = &$tmp[$v];
	}
	return $tmp;
}
