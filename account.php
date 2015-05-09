<?php
/**
 * 公众号管理
 * 
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';
checklogin();

$actions = array('display', 'post', 'payment', 'switch', 'delete', 'sync');
$action = in_array($_GPC['act'], $actions) ? $_GPC['act'] : 'display';

$controller = 'account';
require router($controller, $action);
