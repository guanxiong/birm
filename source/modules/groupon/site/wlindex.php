<?php
/**
 * 首页
 *
 * @author 超级无聊
 * @url
 */	 $condition= " AND starttime<".time()." AND endtime>".time()." ";
	//获取最新的8件商品
	$list = pdo_fetchall("SELECT id,price,market_price,title,summary,thumb_list,sell_nums,virtual_sales,endtime FROM ".tablename('groupon_list')." WHERE weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY listorder DESC, id DESC LIMIT 8");
 	include $this->template('wl_index');