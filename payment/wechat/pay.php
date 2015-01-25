<?php
/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * $sn$
 */
define('IN_MOBILE', true);
require '../../source/bootstrap.inc.php';

$sl = $_GPC['ps'];
$params = @json_decode(base64_decode($sl), true);
if($_GPC['done'] == '1') {
	pdo_update('paylog',array('status'=>'1'),array('plid'=>$params['tid']));
	$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `plid`=:plid';
	$pars = array();
	$pars[':plid'] = $params['tid'];
	$log = pdo_fetch($sql, $pars);
	if(!empty($log)) {
		if (!empty($log['tag'])) {
			$tag = iunserializer($log['tag']);
		}
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
				$ret['tag'] = $tag;
				exit($site->$method($ret));
			}
		}
	}
}
$sql = 'SELECT * FROM ' . tablename('paylog') . ' WHERE `plid`=:plid';
$log = pdo_fetch($sql, array(':plid' => $params['tid']));
if(!empty($log) && $log['status'] != '0') {
	exit('这个订单已经支付成功, 不需要重复支付.');
}
$auth = sha1($sl . $log['weid'] . $_W['config']['setting']['authkey']);
if($auth != $_GPC['auth']) {
	exit('参数传输错误.');
}

require_once model('payment');
$wOpt = wechat_build($params, $_W['account']['payment']['wechat']);
if (is_error($wOpt)) {
	if ($wOpt['message'] == 'invalid out_trade_no') {
		$id = date('YmdH');
		pdo_update('paylog', array('plid' => $id), array('plid' => $log['plid']));
		pdo_query("ALTER TABLE ".tablename('paylog')." auto_increment = ".($id+1).";");
		message("抱歉，发起支付失败，系统已经修复此问题，请重新尝试支付。");
	}
	message("抱歉，发起支付失败，具体原因为：“{$wOpt['errno']}:{$wOpt['message']}”。请及时联系站点管理员。");
	exit;
}
?>
<script type="text/javascript">
document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
	WeixinJSBridge.invoke('getBrandWCPayRequest', {
		'appId' : '<?php echo $wOpt['appId'];?>',
		'timeStamp': '<?php echo $wOpt['timeStamp'];?>',
		'nonceStr' : '<?php echo $wOpt['nonceStr'];?>',
		'package' : '<?php echo $wOpt['package'];?>',
		'signType' : '<?php echo $wOpt['signType'];?>',
		'paySign' : '<?php echo $wOpt['paySign'];?>'
	}, function(res) {
		if(res.err_msg == 'get_brand_wcpay_request:ok') {
			location.search += '&done=1';
		} else {
			//alert('启动微信支付失败, 请检查你的支付参数. 详细错误为: ' + res.err_msg);
			history.go(-1);
		}
	});
}, false);
</script>