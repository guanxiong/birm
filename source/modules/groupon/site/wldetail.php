<?php
/**
 * 详情
 *
 * @author 超级无聊
 * @url
 */	$tid = intval($_GPC['tid']);
	$condition= " AND starttime<".time()." AND endtime>".time()." ";
	$groupon = pdo_fetch("SELECT * FROM ".tablename('groupon_list')." WHERE id={$tid} AND weid = '{$_W['weid']}' AND status = '1' $condition ORDER BY listorder DESC, id DESC LIMIT 8");
	 
 	if (empty($groupon)) {
		message('抱歉，商品不存在或是已经被删除！');
	}else{
		  
	}
	//浏览量
	pdo_update('groupon_list',array('hits'=>$groupon['hits']+1),array('id'=>$groupon['id']));
	include $this->template('wl_detail');