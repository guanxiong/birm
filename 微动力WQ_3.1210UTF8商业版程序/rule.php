<?php
/**
 * 规则管理
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
define('IN_SYS', true);
require 'source/bootstrap.inc.php';
define('CONTROLLER', 'rule');

checklogin();
checkaccount();

$actions = array('display', 'post', 'system', 'delete', 'entry', 'cover');
$action = in_array($action, $actions) ? $action : 'display';

if (empty($_W['account']['modules'])) {
	message('抱歉，未发现您系统中的可用模块，请更新缓存或是联系官方！', '', 'error');
}

require router(CONTROLLER, $action);
