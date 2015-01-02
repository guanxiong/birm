<?php
/**
 * 首页
 *
 * @author 微新星
 * @url
 */
 
	//获取最新的8件商品
	//$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_goods')." WHERE weid = '{$weid}' AND status = '1' AND isindex=1 $condition ORDER BY displayorder DESC, id DESC LIMIT 8");
	$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $weid));
	$title=$set['shop_name'];
	$thumbArr=explode('|',$set['thumb']);
 	$cart = pdo_fetchall("SELECT goodsid,price FROM ".tablename('shopping3_cart')." WHERE  weid = '{$weid}' AND from_user = '{$_W['fans']['from_user']}'", array(), 'goodsid');
	$idArr=array();
	foreach($cart as $v){
		$idArr[$v['goodsid']]=array(
			'num'=>1,
			'price'=>$v['price'],
			);
	}
	 
	if(!empty($idArr)){
		//$idstr=implode('|',$idArr);
		$idstr=json_encode($idArr);
	}
	
 	include $this->template('wl_index');