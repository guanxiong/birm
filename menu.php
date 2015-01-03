<?php
/**
 * 用户管理
 * 
 * [WNS] Copyright (c) 2013 BIRM.CO
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
