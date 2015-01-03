<?php
/**
 * 微站管理
 * [WNS] Copyright (c) 2013 BIRM.CO
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';

$actions = array('nav', 'template', 'style', 'icon', 'module', 'preview', 'cover', 'entry', 'shortcut', 'slide', 'siteinfo', 'emoji');

if (in_array($_GPC['act'], $actions)) {
	$action = $_GPC['act'];
} else {
	$action = 'style';
}

if($action != 'module' && $action != 'entry') {
	checklogin();
	checkaccount();
}

$controller = 'site';
require router($controller, $action);
