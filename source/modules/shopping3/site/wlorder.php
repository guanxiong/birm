<?php
/**
 * 我的订单
 *
 * @author 微新星
 * @url
 */
if($_GPC['d']=='checkout'){
		
	$params=$_POST['params'];
	$paramsArr=json_decode($params,true);
	
	if(empty($paramsArr)||$_GPC['totalnum']==0){
		$return=array(
			'status'=>0,
			'msg'=>'抱歉，您的购物车里没有任何商品，请先购买！',
			'url'=>$this->createMobileUrl('wlindex'),
		);
		die(json_encode($return));
	}
	$ordernum = pdo_fetchcolumn("SELECT count(id) FROM ".tablename('shopping3_order')." WHERE weid = :weid AND from_user='".$from."' AND ( status=0 OR  status=1 )", array(':weid' => $weid));
	if($ordernum>=$set['order_limit'] && $set['order_limit']!=0){
		$return=array(
			'status'=>0,
			'msg'=>'您目前无法下单，因为您有太多未处理订单，请联系商家处理.',
 		);
		die(json_encode($return));
	}
	//修改库存
	foreach ($paramsArr as $v){
		//这里可以优化，不需要多次查询 by微新星 20140310
		$_goods=pdo_fetch("SELECT title,total FROM ".tablename('shopping3_goods')." WHERE id = {$v['dishes_id']}");
		if($v['total']>$_goods['total']){
			//更改订单
			if($_goods['total']>0){
				pdo_update('shopping3_cart',array('total'=>$_goods['total']),array('id'=>$v['dishes_id']));
			}else{
				pdo_delete('shopping3_cart',array('id'=>$v['dishes_id']));
			}
			$return=array(
				'status'=>0,
				'msg'=>$_goods['title'].'的库存不足，目前仅有'.$_goods['total'].'件,返回购物车查看。',
			);
			die(json_encode($return));
		}
	}
	//加上快递费
	//$totalprice=$totalprice+floatval($_GPC['expressprice']);

	
		//保存新订单
	$data = array(
			'weid' => $weid,
			'from_user' => $_W['fans']['from_user'],
			'ordersn' => date('md') .sprintf("%04d", $_W['fans']['id']) .random(4, 1),

			'status' => 0,
			'sendtype' => 0,
			'paytype' => 0,
			
			'tel'=>$_GPC['tel'],
			'guest_name'=>$_GPC['guest_name'],
			'sex'=>$_GPC['sex'],
			'guest_address'=>$_GPC['address'],

			'time_day'=>$_GPC['time_day'],
			'time_hour'=>$_GPC['time_hour'],
			'time_second'=>$_GPC['time_second'],
			'seat_type'=>$_GPC['seat_type'],
			'totalnum' => $_GPC['totalnum'],
			'totalprice' => $_GPC['totalprice'],			
			'remark'=>$_GPC['Remark'],
			'createtime' => TIMESTAMP,
			'secretid'=>random(4,1),
			//打印状态
			'print_sta'=>0,
			'desk'=>$_GPC['desk'],
			'order_type'=>$_GPC['order_type'],
			//'print_usr'=>0,
		);
	pdo_insert('shopping3_order', $data);
	$orderid = pdo_insertid();	
	
	//保存新订单商品
	foreach ($paramsArr as $row) {
		if (empty($row)) {
			continue;
		}
		pdo_insert('shopping3_order_goods', array(
			'weid' => $weid,
			'goodsid' => $row['dishes_id'],
			'orderid' => $orderid,
			'total' => $row['nums'],
			'description'=>$row['description'],
			'createtime' => TIMESTAMP,
		));
	}
	//清空购物车
	pdo_delete('shopping3_cart', array('weid' => $weid, 'from_user' => $_W['fans']['from_user']));
	$return=array(
		'status'=>1,
		'OrderId'=>$orderid,
		'msg'=>'订单成功',
		'url'=>$this->createMobileUrl('wlorder',array('OrderId'=>$orderid,'d'=>'payment')),
	);
	fans_update($from,array('nickname'=>$_GPC['guest_name'],'mobile'=>$_GPC['tel'],'gender'=>$_GPC['sex'],'address'=>$_GPC['address']));
	die(json_encode($return));
	//message('提交订单成功，现在跳转至付款页面...', $this->createMobileUrl('pay', array('orderid' => $orderid)), 'success');
 
}elseif($_GPC['d']=='payment'){
	//考虑库存 by 微新星 2014-2-14
	$orderid = intval($_GPC['OrderId']);
	$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $orderid));
	//获取地址
	include $this->template('wl_payment');
}