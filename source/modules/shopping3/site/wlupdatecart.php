<?php
/**
 * 我的订单
 *
 * @author 微新星
 * @url
 */

$operation = $_GPC['op'];
 
	$result = array('status' => 0, 'message' => '');
	$goodsid = intval($_GPC['dishes_id']);
	$goods = pdo_fetch("SELECT id, type, total FROM ".tablename('shopping3_goods')." WHERE id = :id", array(':id' => $goodsid));
	if (empty($goods)) {
		$result['message'] = '抱歉，该商品不存在或是已经被删除！';
		message($result, '', 'ajax');
	}

	if ($goods['total'] == 0) {
		$result['message'] = '抱歉，该商品库存不足！';
		message($result, '', 'ajax');
	}

	 
	$row = pdo_fetch("SELECT id, total FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' AND goodsid = :goodsid", array(':from_user' => $from, ':goodsid' => $goodsid));
	if (empty($row['id'])) {
		if($_GPC['total']>0){
			$data = array(
				'weid' =>$weid,
				'goodsid' => $goodsid,
				'from_user' => $from,
				'total' => $_GPC['total'],
			);
			pdo_insert('shopping3_cart', $data);
		}
	} else {
		if ($goods['total'] != -1 && $row['total'] > $goods['total']) {
			$result['message'] = '抱歉，该商品库存不足！';
			message($result, '', 'ajax');
		}
		if ($_GPC['total']==0) {
			pdo_delete('shopping3_cart', array('from_user' => $from ,'weid' => $weid, 'goodsid' => $goodsid));
		} else {
			$data = array(
				'total' => $_GPC['total'],
			);
			pdo_update('shopping3_cart', $data, array('from_user' =>$from , 'weid' => $weid, 'goodsid' => $goodsid));
		}
	}
	$result['status'] = 1;
	$result['message'] = '商品数据更新成功！';
	$result['total'] = intval($data['total']);
	message($result, '', 'ajax');
