<?php
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 * 微动力一键更新程序
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';

$actions = array('update');
$action = $_GET['act'];
$action = in_array($action, $actions) ? $action : 'update';

$controller = 'update';
require router($controller, $action);
