<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微擎
 * @author 微动力
 * @url
 */
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	$condition = '';
	if (!empty($_GPC['keyword'])) {
		$condition .= " AND a.title LIKE '%{$_GPC['keyword']}%'";
	}
	if (!empty($_GPC['cate_2'])) {
		$cid = intval($_GPC['cate_2']);
		$condition .= " AND a.ccate = '{$cid}'";
	} elseif (!empty($_GPC['cate_1'])) {
		$cid = intval($_GPC['cate_1']);
		$condition .= " AND a.pcate = '{$cid}'";
	}

	if (isset($_GPC['status'])) {
		$condition .= " AND a.status = '".intval($_GPC['status'])."'";
	}
	$list = pdo_fetchall("SELECT a.* FROM ".tablename('shopping3_order')." as a  WHERE a.weid = '{$_W['weid']}' $condition ORDER BY a.status ASC, a.createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " as a WHERE a.weid = '{$_W['weid']}' $condition");
	$pager = pagination($total, $pindex, $psize);

 	//$express = pdo_fetchall("SELECT id,express_name FROM ".tablename('shopping3_express')." WHERE weid = '{$_W['weid']}'",array(),'id');
	//$express['0']=array('express_name'=>'未选择');

} elseif ($operation == 'detail') {
	//流程 第一步确认付款 第二步确认订单 第三步，完成订单
	$id = intval($_GPC['id']);

	if (checksubmit('finish')) {
		//订单确认，代表已经支付过
		pdo_update('shopping3_order', array('status' => 3,'ispay'=>1, 'remark' => $_GPC['remark']), array('id' => $id));
		message('订单操作成功！', referer(), 'success');
	}

	if (checksubmit('cancel')) {
		pdo_update('shopping3_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
		message('取消完成订单操作成功！', referer(), 'success');
	}
	
	if (checksubmit('confrim')) {
		pdo_update('shopping3_order', array('status' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
		message('确认订单操作成功！', referer(), 'success');
	}
	if (checksubmit('cancelpay')) {
		pdo_update('shopping3_order', array('status' => 1,'ispay'=>0, 'remark' => $_GPC['remark']), array('id' => $id));
		message('取消订单付款操作成功！', referer(), 'success');
	}

	if (checksubmit('confrimpay')) {
		pdo_update('shopping3_order', array('status' => 2,'ispay'=>1,'remark' => $_GPC['remark']), array('id' => $id));
		message('确认订单付款操作成功！', referer(), 'success');
	}
	if (checksubmit('cancelprint')) {
		pdo_update('shopping3_order', array('print_sta' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
		message('取消打印状态成功！', referer(), 'success');
	}

	if (checksubmit('confrimprint')) {
		pdo_update('shopping3_order', array('print_sta' => -1,'remark' => $_GPC['remark']), array('id' => $id));
		message('开启打印状态成功！', referer(), 'success');
	}
	if (checksubmit('close')) {
		pdo_update('shopping3_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
		message('订单关闭操作成功！', referer(), 'success');
	}

	if (checksubmit('open')) {
		pdo_update('shopping3_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
		message('开启订单操作成功！', referer(), 'success');
	}
	if (checksubmit('send')) {
		pdo_update('shopping3_order', array('sendstatus' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
		message('此订单已确认配送!', referer(), 'success');
	}
	if (checksubmit('cancelsend')) {
		pdo_update('shopping3_order', array('sendstatus' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
		message('此订单已确认配送!', referer(), 'success');
	}	

	$item = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $id));
	$address=pdo_fetch("SELECT * FROM ".tablename('shopping3_address')." WHERE id = :id", array(':id' => $item['aid']));
	$item['user'] = fans_search($item['from_user'], array('realname', 'resideprovince', 'residecity', 'residedist', 'address', 'mobile', 'qq'));
	$goodsid = pdo_fetchall("SELECT goodsid, total,description FROM ".tablename('shopping3_order_goods')." WHERE orderid = '{$item['id']}'", array(), 'goodsid');
	$goods = pdo_fetchall("SELECT * FROM ".tablename('shopping3_goods')."  WHERE id IN ('".implode("','", array_keys($goodsid))."')");
	
	//$express = pdo_fetchall("SELECT id,express_name FROM ".tablename('shopping3_express')." WHERE weid = '{$_W['weid']}'",array(),'id');
	//$express['0']=array('express_name'=>'未选择');

	$item['goods'] = $goods;
} elseif ($operation == 'delete') {
	$id = intval($_GPC['id']);
	$order = pdo_fetch("SELECT id  FROM ".tablename('shopping3_order')." WHERE id = '$id' AND weid=".$_W['weid']."");
	if (empty($order)) {
		message('抱歉，订单不存在，或者已经删除！', $this->createWebUrl('order', array('op' => 'display')), 'error');
	}
	//删除订单里面的东西
	pdo_delete('shopping3_order_goods', array('orderid' => $id));
	pdo_delete('shopping3_order', array('id' => $id));
	message('订单删除成功！', $this->createWebUrl('order', array('order' => 'display')), 'success');
}elseif ($operation == 'cprint') {
	pdo_update('shopping3_order', array('print_sta' => 0), array('weid' => $weid));
	message('取消所有未打印订单！', $this->createWebUrl('order', array('order' => 'display')), 'success');
}
include $this->template('web/order');