<?php
/**
 * 购物车
 *
 * @author 微动力
 * @url
 */
 	if($_GPC['subcp']=='ajax'&&$_GPC['d']=='add'){
		$goodsid = intval($_GPC['goodsid']);
		$goods = pdo_fetch("SELECT id, type, total,marketprice FROM ".tablename('shopping3_goods')." WHERE id = :id", array(':id' => $goodsid));
		if (empty($goods)) {
			$result['message'] = '抱歉，该商品不存在或是已经被删除！';
			message($result, '', 'ajax');
		}
		$row = pdo_fetch("SELECT id, total FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' AND goodsid = :goodsid", array(':from_user' => $from, ':goodsid' => $goodsid));
		if($row==false){
			//不存在
			$data = array(
				'weid' =>$weid,
				'goodsid' => $goodsid,
				'goodstype' => $goods['type'],
				'price'=> $goods['marketprice'],
				'from_user' => $_W['fans']['from_user'],
				'total' => $_GPC['nums'],
			);
			pdo_insert('shopping3_cart', $data);
		}else{
			//存在
			$data=array(
				'price'=> $goods['marketprice'],
				'total' => ($_GPC['nums']+$row['total']),
			);
			pdo_update('shopping3_cart', $data,array('id'=>$row['id']));
		}
		
		//返回数据
		$row = pdo_fetchall("SELECT total,price FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $_W['fans']['from_user']));

		$totalnum=0;
		$totalprice=0;
		foreach ($row as $v){
			$totalnum=$totalnum+intval($v['total']);		
			$totalprice=$totalprice+(intval($v['total'])*floatval($v['price']));
		}
		$result=array(
			'status'=>1,
			'qty'=>$totalnum,
			'total'=>$totalprice,
		);
		die(json_encode($result));
	}elseif($_GPC['subcp']=='ajax'&&$_GPC['d']=='list'){
		if($_GPC['a']=='del'){
			pdo_delete('shopping3_cart', array('from_user' => $from, 'weid' => $weid, 'id' => $_GPC['CId']));
		}else{
			foreach ($_GPC['CId'] as $k=>$v){
				pdo_update('shopping3_cart', array('total'=>$_GPC['Qty'][$k]),array('id'=>$v));
			}
		}
		$result=array(
			'status'=>1,
		);
		die(json_encode($result));					
	}elseif($_GPC['subcp']=='ajax'){
		print_r($_GPC);
		exit;
	}
	if($_GPC['d']=='checkout'){
		//验证是否绑定用户
		$this->checklogin($from);

		$addresslist=pdo_fetchall("SELECT id,username,tel,address FROM ".tablename('shopping3_address')." WHERE weid = {$weid} and from_user='{$_W['fans']['from_user']}'");			
		$expresslist=pdo_fetchall("SELECT id,express_name,express_price FROM ".tablename('shopping3_express')." WHERE weid = {$weid}  order by displayorder desc" );			
		//找出总价格
		$row = pdo_fetchall("SELECT total,price FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $_W['fans']['from_user']));
		$totalnum=0;
		$totalprice=0;
		foreach ($row as $v){
			$totalnum=$totalnum+intval($v['total']);		
			$totalprice=$totalprice+(intval($v['total'])*floatval($v['price']));
		}
		include $this->template('wl_checkout');
	}else{
		$cart = pdo_fetchall("SELECT * FROM ".tablename('shopping3_cart')." WHERE  weid = '{$weid}' AND from_user = '{$_W['fans']['from_user']}'", array(), 'goodsid');
		$otalprice=0;
		if (!empty($cart)) {
			$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM ".tablename('shopping3_goods')." WHERE id IN ('".implode("','", array_keys($cart))."')");
			if (!empty($goods)) {
				foreach ($goods as $row) {
					if (empty($cart[$row['id']]['total'])) {
						continue;
					}
					//购物车不考虑库存
					/* if ($row['total'] != -1 && $row['total'] < $cart[$row['id']]['total']) {
						message('抱歉，“'.$row['title'].'”此商品库存不足！', $this->createMobileUrl('wlcart'), 'error');
					} */
					$price += (floatval($row['marketprice']) * intval($cart[$row['id']]['total']));
				}
			}
		}
		//fans_update($_W['fans']['from_user'], $newsfans);

		$fans=fans_search($from,array('nickname','mobile','gender','address'));
		//
		$shopping_mobile=pdo_fetch("SELECT phone FROM ".tablename('shopping3_fans')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $_W['fans']['from_user']));
		if(!empty($shopping_mobile['phone'])){
			$fans['mobile']=$shopping_mobile['phone'];
		}
		//strai
		$shopping_fans=pdo_fetch("SELECT * FROM ".tablename('shopping3_fans')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $_W['fans']['from_user']));
		$file = IA_ROOT . "/source/modules/izc_strcoupon/advcard.mod.php";
		if(is_file($file)) {
			require $file;
			$mycoupons = coupon_fans_fetchall();
		}

		//判断营业时间strai
		$now = time();
		$H = date('H',$now);
		$i = date('i',$now);
		$time = $H*60 + $i;

		if(strpos($set['yy_start_time'],':') == true){ 
			$start_time = explode(':',$set['yy_start_time']);
		}elseif(strpos($set['yy_start_time'],'：') == true){ 
			$start_time = explode('：',$set['yy_start_time']);
		}else{
			message('营业时间设置错误');
		}
		$time1 = $start_time[0]*60+$start_time[1];

		if(strpos($set['yy_end_time'],':') == true){ 
			$end_time = explode(':',$set['yy_end_time']);
		}elseif(strpos($set['yy_end_time'],'：') == true){ 
			$end_time = explode('：',$set['yy_end_time']);
		}else{
			message('营业时间设置错误');
		}
		$time2 = $end_time[0]*60+$end_time[1];

		if($time>=$time1&&$time<=$time2){
			$status = 1;
		}
		include $this->template('wl_cart');
	}