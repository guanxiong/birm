<?php 
/**
 * 设置中心
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
define('IN_SYS', true);
require './source/bootstrap.inc.php';
checklogin();
//checkaccount();
if($_W['isfounder']) {

$actions = array('userpay','log','userlist', 'status','edit','useredit','group');
$action = in_array($_GPC['act'], $actions) ? $_GPC['act'] : 'userlist';

$controller = 'finance';
require router($controller, $action);
}
