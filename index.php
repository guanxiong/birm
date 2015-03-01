<?php
/**
 * [WDL] Copyright 微动力 (c) 2013 B2CTUI.COM
 */
define('IN_SYS', true);

require 'source/bootstrap.inc.php';
define('CONTROLLER', 'home');
$actions = array('attachment', 'help', 'announcement', 'module', 'welcome', 'sysinfo', 'index');
$action = in_array($action, $actions) ? $action : (!empty($_W['uid']) ? 'frame' : 'index');
require router(CONTROLLER, $action);