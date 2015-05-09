<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微动力
 * @author 微动力
 * @url
 */
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
	$condition = '';
	if (checksubmit('submit1') && !empty($_GPC['select'])) {
		pdo_update('shopping3_order', array('status' => 2), " id  IN  ('".implode("','", $_GPC['select'])."')");
		//strai
		//根据订单号和对所有订单进行处理,直接在这写
		//遍历 判断下状态
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			//有时间判断下
			foreach($_GPC['select'] as $key=>$value){
				$res = coupon_fans_used($value,1);
			}
		}
		message('订单批量处理成功！', $this->createWebUrl('order', array('page' => $_GPC['page'])));
	}
	if (checksubmit('submit2') && !empty($_GPC['select'])) {
		pdo_update('shopping3_order', array('status' => -1), " id  IN  ('".implode("','", $_GPC['select'])."')");
		//strai
		//根据订单号和对所有订单进行处理,直接在这写
		//遍历 判断下状态
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			//有时间判断下
			foreach($_GPC['select'] as $key=>$value){
				$res = coupon_fans_used($value,2);
			}
		}
		message('订单批量处理成功！', $this->createWebUrl('order', array('page' => $_GPC['page'])));
	}
	if (checksubmit('submit3') && !empty($_GPC['select'])) {
		pdo_update('shopping3_order', array('status' => 3), " id  IN  ('".implode("','", $_GPC['select'])."')");
		message('订单批量处理成功！', $this->createWebUrl('order', array('page' => $_GPC['page'])));
	}	
	if($_GPC['confrim']==1){
		if (!empty($_GPC['tel'])) {
			$condition .= " AND a.tel LIKE '%{$_GPC['tel']}%'";
		}
		if (!empty($_GPC['status'])&&$_GPC['status']!=-1) {
			$status = intval($_GPC['status']);
			$condition .= " AND a.status = '{$status}'";
		} 
 
		if(!empty($_GPC['start']) &&!empty($_GPC['end']) ){
			$starttime=strtotime($_GPC['start']);
			$endtime=strtotime($_GPC['end'].' 23:59:59');
			$condition.=" AND  a.createtime>{$starttime}  AND  a.createtime<{$endtime}";
		}
	}elseif($_GPC['confrim']==2){
		include_once  'wldownload.php';
		exit;
	}
	$pindex = max(1, intval($_GPC['page']));
	$psize = 10;
	
	if(empty($_GPC['start'])){
		$start=date('Y-m-d',strtotime('-1 day'));
	}else{
		$start=$_GPC['start'];
	}
	if(empty($_GPC['end'])){
		$end=date('Y-m-d',strtotime('-1 day'));
	}else{
		$end=$_GPC['end'];
	}
	$list = pdo_fetchall("SELECT a.* FROM ".tablename('shopping3_order')." as a  WHERE a.weid = '{$_W['weid']}' $condition ORDER BY a.createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
 	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shopping3_order') . " as a WHERE a.weid = '{$_W['weid']}' $condition");
	$pager = pagination($total, $pindex, $psize);

 	//$express = pdo_fetchall("SELECT id,express_name FROM ".tablename('shopping3_express')." WHERE weid = '{$_W['weid']}'",array(),'id');
	//$express['0']=array('express_name'=>'未选择');

} elseif ($operation == 'detail') {
	//流程 第一步确认付款 第二步确认订单 第三步，完成订单
	$id = intval($_GPC['id']);
	//die_dump($_GPC);
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
	//开启短信发送
		$set=pdo_fetch("SELECT sms_status,shop_tel FROM ".tablename('shopping3_set')." WHERE  weid = '{$weid}' ");

		if($set['sms_status']==1){
			//查询订单
			$order = pdo_fetch("SELECT sms_sta,guest_name,ordersn,paytype,tel FROM ".tablename('shopping3_order')." WHERE id = :id", array(':id' => $id ));
			if($order!=false){
				if(empty($order['sms_sta'])){
					if($order['paytype'] == 1){
						$paystatus="余额支付";
					}elseif($order['paytype'] == 2){
						$paystatus="在线支付";
					}elseif($order['paytype'] == 3){
						$paystatus="货到付款";
					}
					 
					//确定订单发短信
					$txt="尊敬的顾客,您的微信订单已确认.付款方式为".$paystatus.".如有疑问,请联系电话:".$set['shop_tel'].'['.$set['shop_name'].']';
 					$this->_sendsms($txt,$order['tel'],$id);
				}
			}
		}
		pdo_update('shopping3_order', array('status' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
		//strai 确认订单,使用掉礼券
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			//有时间判断下
			$res = coupon_fans_used($id,1);
		}
		message('确认订单操作成功！', referer(), 'success');
	}
	if (checksubmit('cancelpay')) {
		//TODO
		pdo_update('shopping3_order', array('status' => 1,'ispay'=>0, 'remark' => $_GPC['remark']), array('id' => $id));
		message('取消订单付款操作成功！', referer(), 'success');
	}
	//确认付款
	if (checksubmit('confrimpay')) {
		//strai
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			//有时间判断下
			$res = coupon_fans_used($_GPC['id'],1);
		}
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
		//strai
		//关闭订单,同时将优惠券状态改成2 未使用,后面不管
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			//有时间判断下
			$res = coupon_fans_used($_GPC['id'],2);
		}
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
	//strai
	//关闭订单,同时将优惠券状态改成2 未使用,后面不管
	$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
	if(is_file($file)) {
		require $file;
		//有时间判断下
		$res = coupon_fans_used($_GPC['id'],2);
	}
	message('订单删除成功！', $this->createWebUrl('order', array('order' => 'display')), 'success');
}elseif ($operation == 'cprint') {
	pdo_update('shopping3_order', array('print_sta' => 0), array('weid' => $weid));
	message('取消所有未打印订单！', $this->createWebUrl('order', array('order' => 'display')), 'success');
}
include $this->template('web/order');