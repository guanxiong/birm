<?php
/**
 * 微动力微拍模块微站定义
 *
 * @author 微动力团队
 * @url 
 */
define('IN_SYS', true);
require '../../bootstrap.inc.php';

$actions = array('getsetting', 'main', 'getdata', 'finished', 'failed', 'batchfinish');
if (in_array($_GPC['act'], $actions)) {
	$action = $_GPC['act'];
} else {
	exit('Access Denied');
}
$_W['attachurl'] = str_replace('source/modules/we7_photomaker/', '', $_W['attachurl']);
$site = WeUtility::createModuleSite('we7_photomaker');
$site->inMobile = false;
$site->module['name'] = 'we7_photomaker';
if (method_exists($site, 'doWeb'.$action)) {
	call_user_func(array($site, 'doWeb'.$action));
	exit;
} else {
	exit('Access Denied');
}

