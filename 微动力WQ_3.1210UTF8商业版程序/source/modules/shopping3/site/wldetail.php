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
	}else{
		if(!empty($goods['thumb_url'])){
			$goods['thumbArr']=explode('|',$goods['thumb_url']);	
 		}else{
			$goods['thumbArr']=array($goods['thumb']);
		}
		//4个最新的
		//$list = pdo_fetchall("SELECT * FROM ".tablename('shopping3_goods')." WHERE weid = '{$weid}' AND status = '1' AND pcate={$goods['pcate']} AND id!={$goods['id']} ORDER BY displayorder DESC, id DESC LIMIT 6");
		
	}
	pdo_update('shopping3_goods',array('hits'=>$goods['hits']+1),array('id'=>$goods['id']));
	include $this->template('wl_detail');