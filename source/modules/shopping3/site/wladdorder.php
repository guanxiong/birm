<?php
/**
 * 我的订单
 *
 * @author 微动力
 * @url
 */
 if($subcp=='unchoose'){
	$temp=pdo_delete('shopping3_cart', array('from_user' => $from, 'weid' => $weid, 'goodsid' => $_GPC['goodsid']));
	echo $temp;
 }elseif($subcp=='choosen'){
	if(!empty($_GPC['goodsid'])){
		$goods= pdo_fetch("SELECT id FROM ".tablename('shopping3_cart')." WHERE goodsid={$_GPC['goodsid']} AND weid = '{$weid}'  and from_user='{$from}' ");
		if($goods==false){
				$data=array(
					'weid'=>$weid,
					'goodsid'=>$_GPC['goodsid'],
					'price'=>$_GPC['price'],
					'total'=>1,
					'from_user'=>$from,
					'create_time'=>time(),
				);
				$temp=pdo_insert('shopping3_cart',$data);
				echo $temp;
			}
	}
 }else{
	$order=$_POST['order'];
	if(empty($order)){
		$return=array(
			'status'=>0,
			'msg'=>'购物车不能为空',
		);
	}else{
		$goodsArr=json_decode($order,true);
		foreach($goodsArr as $k=>$v){
			$goods= pdo_fetch("SELECT id FROM ".tablename('shopping3_cart')." WHERE goodsid={$v['dishes_id']} AND weid = '{$weid}'  and from_user='{$from}' ");
			if($goods==false){
				$data=array(
					'weid'=>$weid,
					'goodsid'=>$v['dishes_id'],
					'price'=>$v['price'],
					'total'=>$v['nums'],
					'from_user'=>$from,
					'create_time'=>time(),
				);
				pdo_insert('shopping3_cart',$data);
			}
		}
	}
}	 