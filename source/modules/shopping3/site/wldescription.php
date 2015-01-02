<?php
/**
 * 商品详情
 *
 * @author 微新星
 * @url
 */
	$goodsid=intval($_GPC['goodsid']);
	//搜索订单
 	$goods = pdo_fetch("SELECT content FROM ".tablename('shopping3_goods')." WHERE weid={$weid} and  id = {$goodsid}");
	if (empty($goods)) {
		message('抱歉，商品不存在或是已经被删除！');
	}		
 
	include $this->template('wl_description');
