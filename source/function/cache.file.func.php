<?php 
/**
 * 文件缓存
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
/**
 * 缓存函数四个，分别用于写入，读取，删除，清空缓存。暂时采用文件缓冲，期待使用memcache或mysql内存缓冲
 */
function cache_get($file,$dir='',$include=true) 
{
	$file=$dir?"{$GLOBALS['config']['cache']['dir']}/{$dir}/{$file}":"{$GLOBALS['config']['cache']['dir']}/{$file}";
	if(!is_file($file)) return array();
	return $include?include $file:file_get_contents($file);
}

function cache_set($file,$data,$dir='')
{
	if(!is_string($data))
		$data="<?php \r\ndefined('CURRENT_VERSION') or exit('Access Denied');\r\nreturn ".var_export($data, true).';';
	$file=$dir?"{$GLOBALS['config']['cache']['dir']}/{$dir}/{$file}":"{$GLOBALS['config']['cache']['dir']}/{$file}";
	return file_write($file,$data);
}

function cache_delete($file,$dir='')
{
	$file=$dir?"{$GLOBALS['config']['cache']['dir']}/{$dir}/{$file}":"{$GLOBALS['config']['cache']['dir']}/{$file}";
	@unlink($file);
}

function cache_clean($dir='')
{
	$dir=$dir?"{$GLOBALS['config']['cache']['dir']}/{$dir}":"{$GLOBALS['config']['cache']['dir']}";
	rmdirs($dir,true);
}
