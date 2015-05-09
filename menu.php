<?php
/**
 * 用户管理
 * 
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';
checklogin();
checkaccount();

$actions = array('designer', 'action', 'search');
$action = $_GET['act'];
$action = in_array($action, $actions) ? $action : 'designer';

$controller = 'menu';
require router($controller, $action);
