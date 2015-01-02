	<?php
/**
 * 详情
 *
 * @author 微新星
 * @url
 */

 	$orderid = intval($_GPC['orderid']);
	$order = pdo_fetch("SELECT * FROM ".tablename('groupon_order')." WHERE id = :id", array(':id' => $orderid));
	
/* 	if ($order['paytype'] != 1) {
		message('抱歉，您的支付方式不正确，请重新提交订单！', $this->createMobileUrl('wlorder'), 'error');
	}
	if ($_W['fans']['credit2'] < $order['totalprice']) {
		message('抱歉，您帐户的余额不够支付该订单，请充值！', create_url('mobile/module/charge', array('name' => 'member', 'weid' => $_W['weid'])), 'error');
	}
	if (pdo_update('card_members', array('credit2' => $profile['credit2'] - $order['totalprice']), array('from_user' => $_W['fans']['from_user']))) {
		pdo_update('shopping1_order', array('status' => 2), array('id' => $orderid));
		message('余额付款成功！', $this->createMobileUrl('wlorder'), 'success');
	} else {
		message('余额付款失败，请重试！', $this->createMobileUrl('wlorder'), 'error');
	} */
	
 			$params['tid'] = $orderid;
			$params['user'] = $_W['fans']['from_user'];
			$params['fee'] = $order['totalprice'];
			$params['ordersn'] = $order['ordersn'];
			$params['title'] = $_W['account']['name'];
	$this->pay($params);