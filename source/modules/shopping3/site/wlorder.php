<?php
/**
 * 我的订单
 *
 * @author 微动力
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
		//这里可以优化，不需要多次查询 by微动力 20140310
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
	/*strai
	 使用优惠券
	*/
	//获取使用后的金额
	if($_GPC['coupon_id']){
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			$res = coupon_fans_use($_GPC['coupon_id'],$_GPC['totalprice']);//使用优惠券,返回
			if($res['status']){
				$_GPC['totalprice'] = $res['total_price'];
			}else{
				$return = $res;
				$return['url']=$this->createMobileUrl('wlindex');
				die(json_encode($return));
			}
		}
	}
	//end
	
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
			'guest_address'=>$_GPC['pre_address'].$_GPC['address'],
			'time_day'=>$_GPC['time_day'],
			'time_hour'=>$_GPC['time_hour'],
			'time_second'=>$_GPC['time_second'],
			'seat_type'=>$_GPC['seat_type'],
			'totalnum' => $_GPC['totalnum'],
			'totalprice' => $_GPC['totalprice'],			
			'remark'=>$_GPC['remark'],
			'createtime' => TIMESTAMP,
			'secretid'=>random(4,1),
			//打印状态
			'print_sta'=>0,
			//店内点餐
			'desk'=>($_GPC['seat_type']==2?$_GPC['room_t']:$_GPC['desk_t']),
			'nums'=>$_GPC['nums'],
			'order_type'=>$_GPC['order_type'],
			//'print_usr'=>0,
		);
	pdo_insert('shopping3_order', $data);
	$orderid = pdo_insertid();	
	//创建优惠券使用记录
	if($_GPC['coupon_id']){
		if(is_file($file)) {
			$fans_coupon = coupon_fans_fetch($_GPC['coupon_id']);
			if($fans_coupon){
				$res = coupon_fans_log_add($fans_coupon['id'],$orderid,$data['ordersn']);
				if(!$res['status']){
					$return = $res;
					die_dump($res);
					$return['url']=$this->createMobileUrl('wlindex');
					die(json_encode($return));			
				}
			}else{
				$return['status'] = 0;
				$return['msg'] = '优惠券不存在';
				$return['url']=$this->createMobileUrl('wlindex');
				die(json_encode($return));			
			}
		}
	}
	//保存新订单商品
	foreach ($paramsArr as $row) {
		if (empty($row)||$row['nums']<1) {
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
		//考虑库存 by 微动力 2014-2-14
		//$orderid = intval($_GPC['OrderId']);
		//$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $orderid));
		//获取地址
		//include $this->template('wl_payment');
		$orderid = intval($_GPC['OrderId']);
		//考虑看库存
		$temp=$this->_checkstock($orderid);
		if($temp==false){
			message('订单中某些产品库存不足，订单已取消，请联系客服。', $this->createMobileUrl('wlmember',array('weid'=>$_GET['weid'])), 'error');
		}
 		$order = pdo_fetch("SELECT * FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $orderid));
		if($order==false){
			message('订单不存在！');
		}
		if($order['status']==2){
			message('订单状态已经确认！',$this->createMobilurl('wlmember'),'success');
		}
		if (checksubmit('codsubmit')) {
			pdo_update('shopping3_order', array('paytype' =>3,'status'=>1), array('id' => $orderid));
			//计算库存
			$this->_inventory($orderid);
			$this->_assist(1,$orderid);
			//选择现金支付，跳转到会员页面
			message('您选择货到付款，您的订单我们正在处理中！', $this->createMobileUrl('wlmember'));
		}
		$params['tid'] = $orderid;
		$params['user'] = $_W['fans']['from_user'];
		$params['fee'] = $order['totalprice'];
		$params['title'] = $_W['account']['name'];
		$params['ordersn'] = $order['ordersn'];
		$params['virtual'] = $order['goodstype'] == 2 ? true : false;
		$bootstrap_type = 3;
		include $this->template('wl_payment');
}