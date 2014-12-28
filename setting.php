<?php 
/**
 * [WeEngine System] Copyright (c) 2013 B2CTUI.COM
 * $sn: htdocs/setting.php : v 1ced5ce4bed8 : 2014/03/19 08:35:31 : veryinf $
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';
checklogin();

if (empty($_W['isfounder'])) {
	$actions = array('profile', 'updatecache');
} else {
	$actions = array('profile', 'updatecache', 'common', 'template', 'style', 'register', 'database', 'tools', 'copyright');
}
$action = in_array($_GPC['act'], $actions) ? $_GPC['act'] : message('您无权限进行该操作！');

$controller = 'setting';
require router($controller, $action);

