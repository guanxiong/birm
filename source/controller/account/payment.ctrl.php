<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$id = intval($_GPC['id']);
if(empty($id)) {
	$id = $_W['weid'];
}
if (!checkpermission('wechats', $id)) {
	message('公众号不存在或是您没有权限操作！');
}
if($_W['ispost']) {
	$credit = array_elements(array('switch'), $_GPC['credit']);
	$credit['switch'] = $credit['switch'] == 'true';
	$offline = array_elements(array('switch', 'account'), $_GPC['offline']);
	$offline['switch'] = $offline['switch'] == 'true';
	$alipay = array_elements(array('switch', 'account', 'partner', 'secret'), $_GPC['alipay']);
	$alipay['switch'] = $alipay['switch'] == 'true';
	$alipay['account'] = trim($alipay['account']);
	$alipay['partner'] = trim($alipay['partner']);
	$alipay['secret'] = trim($alipay['secret']);
	$delivery = array_elements(array('switch'), $_GPC['delivery']);
	$delivery['switch'] = $delivery['switch'] == 'true';
	if($alipay['switch'] && (empty($alipay['account']) || empty($alipay['partner']) || empty($alipay['secret']))) {
		message('请输入完整的支付宝接口信息.');
	}
	if($_GPC['alipay']['t'] == 'true') {
		$params = array();
		$params['tid'] = md5(uniqid());
		$params['user'] = '测试用户';
		$params['fee'] = '0.01';
		$params['title'] = '测试支付接口';

		require model('payment');
		$ret = alipay_build($params, $alipay);
		if($ret['url']) {
			header("location: {$ret['url']}");
		}
		exit();
	}
	$wechat = array_elements(array('switch', 'appid', 'secret', 'signkey', 'partner', 'key', 'version', 'mchid'), $_GPC['wechat']);
	$wechat['switch'] = $wechat['switch'] == 'true';
	$wechat['signkey'] = trim($wechat['signkey']);
	$wechat['partner'] = trim($wechat['partner']);
	$wechat['key'] = trim($wechat['key']);
	if($wechat['switch'] && (empty($wechat['appid']) || empty($wechat['secret']) || empty($wechat['partner']) || empty($wechat['key']))) {
		message('请输入完整的微信支付接口信息.');
	}

	$payment = $_W['account']['payment'];
	if(!is_array($payment)) {
		$payment = array();
	}
	$payment['credit'] = $credit;
	$payment['alipay'] = $alipay;
	$payment['wechat'] = $wechat;
	$payment['offline'] = $offline;
	$payment['delivery'] = $delivery;
	$dat = iserializer($payment);
	if(pdo_update('wechats', array('payment' => $dat), array('weid' => $id)) !== false) {
		message('保存支付信息成功. ', 'refresh');
	}
	exit();
}
if(!is_array($_W['account']['payment'])) {
	$_W['account']['payment'] = array();
}
$credit = $_W['account']['payment']['credit'];
$alipay = $_W['account']['payment']['alipay'];
$wechat = $_W['account']['payment']['wechat'];
$offline = $_W['account']['payment']['offline'];
$delivery = $_W['account']['payment']['delivery'];
template('account/payment');
