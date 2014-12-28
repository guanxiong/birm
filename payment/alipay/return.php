<?php
error_reporting(0);
define('IN_MOBILE', true);
if(empty($_GET['out_trade_no'])) {
	exit('request failed.');
}
$pieces = explode(':', $_GET['out_trade_no']);
if(!is_array($pieces) || count($pieces) != 2) {
	exit('request failed.');
}
$_POST['weid'] = $pieces[0];
require '../../source/bootstrap.inc.php';
if(empty($_W['account']['payment'])) {
	exit('request failed.');
}
$alipay = $_W['account']['payment']['alipay'];
if(empty($alipay)) {
	exit('request failed.');
}
$prepares = array();
foreach($_GET as $key => $value) {
	if($key != 'sign' && $key != 'sign_type') {
		$prepares[] = "{$key}={$value}";
	}
}
sort($prepares);
$string = implode($prepares, '&');
$string .= $alipay['secret'];
$sign = md5($string);
if($sign == $_GET['sign'] && $_GET['result'] == 'success') {
	$plid = $pieces[1];
	$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `plid`=:plid';
	$params = array();
	$params[':plid'] = $plid;
	$log = pdo_fetch($sql, $params);
	if(!empty($log)) {

		$site = WeUtility::createModuleSite($log['module']);
		if(!is_error($site)) {
			$site->module = $_W['account']['modules'][$log['module']];
			$site->weid = $_W['weid'];
			$site->inMobile = true;
			$method = 'payResult';
			if (method_exists($site, $method)) {
				$ret = array();
				$ret['weid'] = $log['weid'];
				$ret['result'] = $log['status'] == '1' ? 'success' : 'failed';
				$ret['type'] = $log['type'];
				$ret['from'] = 'return';
				$ret['tid'] = $log['tid'];
				$ret['user'] = $log['openid'];
				$ret['fee'] = $log['fee'];
				exit($site->$method($ret));
			}
		}
	}
}
