<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微擎
 * @author 微新星
 * @url
 */
if (checksubmit('submit')) {
	$insert=array(
		'weid'=>$_W['weid'],
		'shop_name'=>$_GPC['shop_name'],
		'shop_tel'=>$_GPC['shop_tel'],
		'shop_address'=>$_GPC['shop_address'],
		'shop_notice'=>$_GPC['shop_notice'],
		'lng'=>$_GPC['lng'],
		'lat'=>$_GPC['lat'],
	);
	if(!empty($_GPC['thumb_url'])){
		$insert['thumb']=implode('|',$_GPC['thumb_url']);
	}else{
		$insert['thumb']='';
	}
	
	if (empty($_GPC['id'])) {
		pdo_insert('shopping3_set', $insert);
	} else {
		pdo_update('shopping3_set', $insert, array('id' => $_GPC['id']));
	}
	message('商城数据保存成功', $this->createWebUrl('Shopset'), 'success');
}
$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $_W['weid']));
 
if($set==false){
	$set=array(
		'id'=>0,
		'lat'=>39.915177,
		'lng'=>116.403851,
	);
}else{
	if(!empty($set['thumb'])){
		$set['thumbArr']=explode('|',$set['thumb']);	
	}
	unset($set['thumb']);
}
include $this->template('web/shopset');