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
		'order_limit'=>trim($_GPC['order_limit']),
 	);
	if (empty($_GPC['id'])) {
		pdo_insert('shopping3_set', $insert);
	} else {
		pdo_update('shopping3_set', $insert, array('weid'=>$_W['weid'],'id' => $_GPC['id']));
	}
	message('短信数据保存成功', $this->createWebUrl('orderset'), 'success');
}
$set = pdo_fetch("SELECT * FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $_W['weid']));
if($set==false){
	$set=array(
		'id'=>0,
	);
}else{

}
include $this->template('web/orderset');