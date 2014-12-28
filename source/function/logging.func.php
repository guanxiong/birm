<?php
/**
 * 日志
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 记录远行日志
 * @param <array | string> $log 要记录的内容
 * @param string $filename
 * @param boolean $includeRequest 是否记录POST信息
 * @return <error | boolean>:
 */
function logging_run($log, $type = 'normal', $filename = 'run', $includePost = true) {
	global $_W;
	$filename .= '.log';
	
	$path = IA_ROOT . '/data/logs/';
	if (empty($path)) {
		return error(1, '目录创建失败');
	}	
	
	$logFormat = "%date %type %user %url %context";
	
	if ($includePost) {
		if(!empty($GLOBALS['_POST'])) {
			$context[] = logging_implode($GLOBALS['_POST']);
		}
	}
	
	if (is_array($log)) {
		$context[] = logging_implode($log);
	} else {
		$context[] = preg_replace('/[ \t\r\n]+/', ' ', $log);
	}
	
	$log = str_replace(explode(' ', $logFormat), array(
			'['.date('Y-m-d H:i:s', $_W['timestamp']).']',
			$type,
			$_W['username'],
			$_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"],
			implode("\n", $context),
			), $logFormat);
	
	file_put_contents($path.$filename, $log."\r\n", FILE_APPEND);
	return true;
}

/**
 * 错误日志
 */
function logging_error() {
	
}
/**
 * 生成日志存放目录
 * 
 * 按yyyymmdd格式
 */
function logging_mkdir() {
	$logRoot = IA_ROOT . '/data/logs/';
	$logDir = $logRoot . date('Ymd');
	if (mkdirs($logDir)) {
		return $logDir . '/';
	} else {
		return false;
	}
}

function logging_implode($array, $skip = array()) {
	$return = '';
	if(is_array($array) && !empty($array)) {
		foreach ($array as $key => $value) {
			if(empty($skip) || !in_array($key, $skip, true)) {
				if(is_array($value)) {
					$return .= "$key={".logging_implode($value, $skip)."}; ";
				} else {
					$return .= "$key=$value; ";
				}
			}
		}
	}
	return $return;
}
