<?php 
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
 * $sn: origins/extension.php : v 866195d935cc : 2014/05/16 09:42:08 : veryinf $
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';
checklogin();

if(empty($_W['isfounder'])) {
	$actions = array('service');
} else {
	$actions = array('module', 'theme', 'service');
}
$action = in_array($action, $actions) ? $action : 'module';

$controller = 'extension';
require router($controller, $action);

