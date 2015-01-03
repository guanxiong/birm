<?php
/**
 * 购物车
 *
 * @author 微新星
 * @url
 */
	$likearr= pdo_fetchall("SELECT a.goodsid,a.checked,b.id,b.title,b.marketprice,b.productprice,b.thumb,b.description FROM ".tablename('shopping3_fans_like')." as a  left join ".tablename('shopping3_goods')." as b on a.goodsid=b.id  WHERE a.checked=1 AND a.weid ={$weid}  and a.from_user='{$from}' ",array(),'goodsid');
	$cart=pdo_fetchall("SELECT goodsid,total FROM ".tablename('shopping3_cart')." WHERE from_user = :from_user AND weid = '{$weid}' ", array(':from_user' => $from),'goodsid');
	foreach($likearr as $k=>$v){
		if(empty($v['title'])){
			unset($likearr[$k]);
		}
	}
	include $this->template('wl_mylike');
	