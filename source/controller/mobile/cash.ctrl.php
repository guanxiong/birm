<?php 
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 * $sn: origins/source/controller/mobile/cash.ctrl.php : v 866195d935cc : 2014/05/16 09:42:08 : veryinf $
 */
defined('IN_IA') or exit('Access Denied');
require model('cloud');

$params = @json_decode(base64_decode($_GPC['params']), true);
if(empty($params) || !array_key_exists($params['module'], $_W['account']['modules'])) {
	message('访问错误.');
}

$dos = array();
if(!empty($_W['account']['payment']['credit']['switch'])) {
	$dos[] = 'credit2';
}
if(!empty($_W['account']['payment']['alipay']['switch'])) {
	$dos[] = 'alipay';
}
if(!empty($_W['account']['payment']['wechat']['switch'])) {
	$dos[] = 'wechat';
}
$do = $_GET['do'];
$type = in_array($do, $dos) ? $do : '';

if(!empty($type)) {
	$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `weid`=:weid AND `module`=:module AND `tid`=:tid';
	$pars  = array();
	$pars[':weid'] = $_W['weid'];
	$pars[':module'] = $params['module'];
	$pars[':tid'] = $params['tid'];
	$log = pdo_fetch($sql, $pars);
	if(!empty($log) && $log['status'] != '0') {
		message('这个订单已经支付成功, 不需要重复支付.');
	}
	if($log['fee'] != $params['fee']) {
		pdo_delete('paylog', array('plid' => $log['plid']));
		$log = null;
	}
	if(empty($log)) {
		$fee = $params['fee'];
		$record = array();
		$record['weid'] = $_W['weid'];
		$record['openid'] = $_W['fans']['from_user'];
		$record['module'] = $params['module'];
		$record['type'] = $type;
		$record['tid'] = $params['tid'];
		$record['fee'] = $fee;
		$record['status'] = '0';
		if(pdo_insert('paylog', $record)) {
			$plid = pdo_insertid();
			$record['plid'] = $plid;
			$log = $record;
		} else {
			message('系统错误, 请稍后重试.');
		}
	} else {
		if($log['type'] != $type) {
			$record = array();
			$record['type'] = $type;
			pdo_update('paylog', $record, array('plid' => $log['plid']));
		}
	}
	$ps = array();
	$ps['tid'] = $log['plid'];
	$ps['user'] = $_W['fans']['from_user'];
	$ps['fee'] = $log['fee'];
	$ps['title'] = $params['title'];

	if($type == 'alipay') {
		require_once model('payment');
		$ret = alipay_build($ps, $_W['account']['payment']['alipay']);
		if($ret['url']) {
			header("location: {$ret['url']}");
			exit();
		}
	}
	if($type == 'wechat') {
		require_once model('payment');
		$sl = base64_encode(json_encode($ps));
		$auth = sha1($sl . $_W['weid'] . $_W['config']['setting']['authkey']);
		header("location: ./payment/wechat/pay.php?weid={$_W['weid']}&auth={$auth}&ps={$sl}");
		exit();
	}
	if($type == 'credit2') {
		$pars = array(':from_user' => $_W['fans']['from_user'], ':weid' => $_W['weid']);
		$row = pdo_fetch("SELECT * FROM " . tablename('card_members') . " WHERE from_user = :from_user AND weid = :weid", $pars);
		if(empty($row)) {
			message("请您先领取会员卡，充值后使用余额支付！");
		}
		if($row['credit2'] < $ps['fee']) {
			message("余额不足以支付, 需要 {$ps['fee']}, 当前 {$row['credit2']}");
		}
		$fee = floatval($ps['fee']);
		$sql = 'UPDATE ' . tablename('card_members') . " SET `credit2`=`credit2`-{$fee} WHERE from_user = :from_user AND weid = :weid";
		if(pdo_query($sql, $pars) == 1) {
			$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `plid`=:plid';
			$pars = array();
			$pars[':plid'] = $ps['tid'];
			$log = pdo_fetch($sql, $pars);
			if(!empty($log) && $log['status'] == '0') {
				$record = array();
				$record['status'] = '1';
				pdo_update('paylog', $record, array('plid' => $log['plid']));

				$site = WeUtility::createModuleSite($log['module']);
				if(!is_error($site)) {
					$site->module = $_W['account']['modules'][$log['module']];
					$site->weid = $_W['weid'];
					$site->inMobile = true;
					$method = 'payResult';
					if (method_exists($site, $method)) {
						$ret = array();
						$ret['result'] = 'success';
						$ret['type'] = $log['type'];
						$ret['from'] = 'return';
						$ret['tid'] = $log['tid'];
						$ret['user'] = $log['openid'];
						$ret['fee'] = $log['fee'];
						$ret['weid'] = $log['weid'];
						exit($site->$method($ret));
					}
				}
			}
		}
	}
}
