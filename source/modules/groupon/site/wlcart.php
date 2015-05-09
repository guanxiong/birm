<?php
/**
 * 购物车
 *
 * @author 超级无聊
 * @url
 */	if($_GPC['subcp']=='ajax'&&$_GPC['d']=='add'){
			$goodsid = intval($_GPC['goodsid']);
			$goods = pdo_fetch("SELECT id, type, total,marketprice FROM ".tablename('shopping1_goods')." WHERE id = :id", array(':id' => $goodsid));
			if (empty($goods)) {
				$result['message'] = '抱歉，该商品不存在或是已经被删除！';
				message($result, '', 'ajax');
			}
			$row = pdo_fetch("SELECT id, total FROM ".tablename('shopping1_cart')." WHERE from_user = :from_user AND weid = '{$_W['weid']}' AND goodsid = :goodsid", array(':from_user' => $_W['fans']['from_user'], ':goodsid' => $goodsid));
			if($row==false){
				//不存在
				$data = array(
					'weid' =>$_W['weid'],
					'goodsid' => $goodsid,
					'goodstype' => $goods['type'],
					'price'=> $goods['marketprice'],
					'from_user' => $_W['fans']['from_user'],
					'total' => $_GPC['nums'],
				);
				pdo_insert('shopping1_cart', $data);
			}else{
				//存在
				$data=array(
					'price'=> $goods['marketprice'],
					'total' => ($_GPC['nums']+$row['total']),
				);
				pdo_update('shopping1_cart', $data,array('id'=>$row['id']));
			}
			
			//返回数据
			$row = pdo_fetchall("SELECT total,price FROM ".tablename('shopping1_cart')." WHERE from_user = :from_user AND weid = '{$_W['weid']}' ", array(':from_user' => $_W['fans']['from_user']));
			
			
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
				pdo_delete('shopping1_cart', array('from_user' => $_W['fans']['from_user'], 'weid' => $_W['weid'], 'id' => $_GPC['CId']));
			}else{
				foreach ($_GPC['CId'] as $k=>$v){
					pdo_update('shopping1_cart', array('total'=>$_GPC['Qty'][$k]),array('id'=>$v));
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
			$addresslist=pdo_fetchall("SELECT id,username,tel FROM ".tablename('shopping1_address')." WHERE weid = {$_W['weid']} and from_user='{$_W['fans']['from_user']}'");			
			include $this->template('wl_checkout');
		}else{
			$cart = pdo_fetchall("SELECT * FROM ".tablename('shopping1_cart')." WHERE  weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}'", array(), 'goodsid');
			$otalprice=0;
			if (!empty($cart)) {
				$goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unit, total FROM ".tablename('shopping1_goods')." WHERE id IN ('".implode("','", array_keys($cart))."')");
				if (!empty($goods)) {
					foreach ($goods as $row) {
						if (empty($cart[$row['id']]['total'])) {
							continue;
						}
						if ($row['total'] != -1 && $row['total'] < $cart[$row['id']]['total']) {
							message('抱歉，“'.$row['title'].'”此商品库存不足！', $this->createMobileUrl('wlcart'), 'error');
						}
						$price += (floatval($row['marketprice']) * intval($cart[$row['id']]['total']));
					}
				}
			}
			include $this->template('wl_cart');
		}