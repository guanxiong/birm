<?php
/**
 * 一张独立图片存放在page thumb中
 * 场景独立参数，存在param中
 * 排序id越大
 * 
 * 
 */
defined('IN_IA') or exit('Access Denied');
/*
非关注用户也可以下单
if(empty($_W['fans'])){
	$return=array(
		'data'=>200,
		'success'=>false,
		'message'=>'提交数据，无会员信息'
	);
	die(json_encode($return));
}
*/
$insert=array(
	'weid'=>$_W['weid'],
	'list_id'=>$id,
	'str1'=>$_GPC['str1'],
	'str2'=>$_GPC['str2'],
	'str3'=>$_GPC['str3'],
	'from_user'=>$_W['fans']['from_user'],
	'create_time'=>time(),
);
/*
$book=pdo_fetch("select * from ".tablename('izc_lightbox_book')." where weid=:weid AND list_id=:list_id AND from_user=:from_user AND status=0",array(':weid'=>$_W['weid'],':list_id'=>$id,':from_user'=>$_W['fans']['from_user']));
if($book==false){
	$temp=pdo_insert('izc_lightbox_book',$insert);
}else{
	$temp=pdo_update('izc_lightbox_book',$insert,array('id'=>$book['id']));
}
*/
$temp=pdo_insert('izc_lightbox_book',$insert);

if($temp==false){
	$return=array(
		'data'=>200,
		'success'=>false,
		'message'=>'数据提交失败'
	);
	die(json_encode($return));
 
}else{
	if($_GPC['isyuyue']==0){
		pdo_update('izc_lightbox_list',array('isyuyue'=>1),array('id'=>$_GPC['id']));
	}
	$return=array(
		'data'=>200,
		'success'=>true,
		'message'=>'数据提交成功'
	);
	die(json_encode($return));
}
