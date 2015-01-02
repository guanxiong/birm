<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn: origins/source/bootstrap.inc.php : v 9a3b993ab950 : 2014/06/08 07:21:03 : RenChao $
 */
define('IN_IA', true);
define('IA_ROOT', str_replace("\\",'/', dirname(dirname(__FILE__))));
define('MAGIC_QUOTES_GPC', (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) || @ini_get('magic_quotes_sybase'));
define('TIMESTAMP', time());
ob_start();

$_W = $_GPC = array();
$pdo = $_W['pdo'] = null;
$configfile = IA_ROOT . "/data/config.php";

if(!file_exists($configfile)) {
	if (file_exists(IA_ROOT . '/install.php')) {
		header('Content-Type: text/html; charset=utf-8');
		require IA_ROOT . '/source/version.inc.php';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		echo "·如果你还没安装本程序，请运行<a href='install.php'> install.php 进入安装&gt;&gt; </a><br/><br/>";
		echo "&nbsp;&nbsp;<a href='http://bbs.we7.cc' style='font-size:12px' target='_blank'>Power by WDL " . IMS_VERSION . " &nbsp;微动力微信公众平台自助开源引擎</a>";
		exit();
	} else {
		header('Content-Type: text/html; charset=utf-8');
		exit('配置文件不存在或是不可读，请检查“data/config”文件或是重新安装！');
	}
}

require $configfile;
require IA_ROOT . '/source/version.inc.php';
require IA_ROOT . '/source/regular.inc.php';
require IA_ROOT . '/source/function/global.func.php';
require IA_ROOT . '/source/function/compat.func.php';
require IA_ROOT . '/source/function/file.func.php';
require IA_ROOT . '/source/function/template.func.php';
require IA_ROOT . '/source/function/tpl.func.php';
require IA_ROOT . '/source/function/pdo.func.php';
require IA_ROOT . '/source/function/communication.func.php';
require IA_ROOT . '/source/class/db.class.php';

define('CLIENT_IP', getip());
$_W['config'] = $config;
$_W['timestamp'] = TIMESTAMP;
$_W['template']['current'] = 'default';
$_W['template']['source'] = IA_ROOT . '/themes';
$_W['template']['compile'] = IA_ROOT . '/data/tpl';
$_W['charset'] = $_W['config']['setting']['charset'];
$_W['token'] = token();
$_W['clientip'] = CLIENT_IP;

define('DEVELOPMENT', $_W['config']['setting']['development'] == 1);
if(DEVELOPMENT) {
	ini_set('display_errors','1');
	error_reporting(E_ALL ^ E_NOTICE);
} else {
	error_reporting(0);
}

if(!in_array($_W['config']['setting']['cache'], array('mysql', 'file'))) {
	$_W['config']['setting']['cache'] = 'mysql';
}
require IA_ROOT . '/source/modules/engine.php';
require IA_ROOT . '/source/function/cache.func.php';
require IA_ROOT . '/source/model/cache.mod.php';
require IA_ROOT . '/source/model/account.mod.php';
require IA_ROOT . '/source/model/member.mod.php';
require IA_ROOT . '/source/model/fans.mod.php';

if(function_exists('date_default_timezone_set')){
	date_default_timezone_set($_W['config']['setting']['timezone']);
}
if(!empty($_W['config']['memory_limit']) && function_exists('ini_get') && function_exists('ini_set')) {
	if(@ini_get('memory_limit') != $_W['config']['memory_limit']) {
		@ini_set('memory_limit', $_W['config']['memory_limit']);
	}
}

$_W['script_name'] = basename($_SERVER['SCRIPT_FILENAME']);
if(basename($_SERVER['SCRIPT_NAME']) === $_W['script_name']) {
	$_W['script_name'] = $_SERVER['SCRIPT_NAME'];
} else if(basename($_SERVER['PHP_SELF']) === $_W['script_name']) {
	$_W['script_name'] = $_SERVER['PHP_SELF'];
} else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $_W['script_name']) {
	$_W['script_name'] = $_SERVER['ORIG_SCRIPT_NAME'];
} else if(($pos = strpos($_SERVER['PHP_SELF'],'/' . $scriptName)) !== false) {
	$_W['script_name'] = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $_W['script_name'];
} else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
	$_W['script_name'] = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
} else {
	$_W['script_name'] = 'unknown';
}
$_W['script_name'] = htmlspecialchars($_W['script_name']);

$sitepath = substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/'));
$_W['siteroot'] = htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].$sitepath);
if(substr($_W['siteroot'], -1) != '/') {
 	$_W['siteroot'] .= '/';
}

$_W['isajax'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$_W['ispost'] = $_SERVER['REQUEST_METHOD'] == 'POST';

if(MAGIC_QUOTES_GPC) {
	$_GET = istripslashes($_GET);
	$_POST = istripslashes($_POST);
	$_COOKIE = istripslashes($_COOKIE);
}
$cplen = strlen($_W['config']['cookie']['pre']);
foreach($_COOKIE as $key => $value) {
	if(substr($key, 0, $cplen) == $_W['config']['cookie']['pre']) {
		$_GPC[substr($key, $cplen)] = $value;
	}
}
$_GPC = array_merge($_GET, $_POST, $_GPC);
$_GPC = ihtmlspecialchars($_GPC);

unset($config);
unset($cplen);

$_W['attachurl'] = empty($_W['config']['upload']['attachurl']) ? $_W['siteroot'] . $_W['config']['upload']['attachdir'] : $_W['config']['upload']['attachurl'];

defined('IN_SYS') && require IA_ROOT . '/source/bootstarp.sys.inc.php';
defined('IN_MOBILE') && require IA_ROOT . '/source/bootstarp.mobile.inc.php';
defined('IN_API') && require IA_ROOT . '/source/bootstarp.api.inc.php';

register_shutdown_function('session_write_close');
header('Content-Type: text/html; charset='.$_W['charset']);
