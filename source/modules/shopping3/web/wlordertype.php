<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微动力
 * @author 微动力
 * @url
 */
$set = pdo_fetch("SELECT id,weid,ordretype1,ordretype2,ordretype3 FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $_W['weid']));
 
if ($_W['ispost']) {
	$insert=array(
		'weid'=>$_W['weid'],
		'ordretype1'=>0,
		'ordretype2'=>0,
		'ordretype3'=>0,
	);
	$ischose=false;
	if(!empty($_GPC['ordertype'])){
		$ordertype=$_GPC['ordertype'];
		$ordertypeArr=explode(',',$_GPC['ordertype']);
		
		foreach($ordertypeArr as $v){
			if($v==1){
				$ischose=true;
				$insert['ordretype1']=1;
			}elseif($v==2){
				$ischose=true;
				$insert['ordretype2']=1;
			}elseif($v==3){
				$ischose=true;
				$insert['ordretype3']=1;
			}
		}
	}
	if($ischose==false){
		message('必须要选择一个店铺类型!');
	}
	if ($set==false) {
		pdo_insert('shopping3_set', $insert);
	} else {
		$temp=pdo_update('shopping3_set', $insert, array('weid'=>$_W['weid'],'id' => $set['id']));
	}
 
	message('数据保存成功', $this->createWebUrl('ordertype'), 'success');
}
if($set==false){
	$set=array(
		'id'=>0,
	);
} 
include $this->template('web/ordertype');