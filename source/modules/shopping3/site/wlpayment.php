<?php
/**
 * 详情
 *
 * @author 微动力
 * @url
 */
	$goodsid = intval($_GPC['goodsid']);
	$goods = pdo_fetch("SELECT * FROM ".tablename('shopping3_goods')." WHERE weid={$weid} and  id = {$goodsid}");
	if (empty($goods)) {
		message('抱歉，商品不存在或是已经被删除！');
	}		
	
	include $this->template('wl_detail');