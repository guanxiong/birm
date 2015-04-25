<?php
/**
 * 2014-2-24
 * 购物车 分类管理 
 * 支持二级分类 来自微动力
 * @author 微动力
 * @url
 */
 if (checksubmit('submit')) {
	//校验营业时间
	if(strlen($_GPC['yy_start_time'])==5||strlen($_GPC['yy_end_time'])==5){
		if(strpos($_GPC['yy_start_time'],':') == true){ 
			$start_time = explode(':',$_GPC['yy_start_time']);
		}elseif(strpos($_GPC['yy_start_time'],'：') == true){ 
			$start_time = explode('：',$set['yy_start_time']);
		}else{
			message('营业时间设置错误');
		}
		if($start_time[0]<0||$start_time[0]>23||$start_time[1]<0||$start_time[1]>59){
			message('营业时间设置错误');
		}
		$time1 = $start_time[0]*60+$start_time[1];
		if(strpos($_GPC['yy_end_time'],':') == true){ 
			$end_time = explode(':',$_GPC['yy_end_time']);
		}elseif(strpos($_GPC['yy_end_time'],'：') == true){ 
			$end_time = explode('：',$set['yy_end_time']);
		}else{
			message('营业时间设置错误');
		}
		if($end_time[0]<0||$end_time[0]>23||$end_time[1]<0||$end_time[1]>59){
			message('营业时间设置错误');
		}
		$time2 = $end_time[0]*60+$end_time[1];
		if($time1>$time2){
			message('营业时间设置错误');
		}
	};
	$insert=array(
		'weid'=>$_W['weid'],
		'shop_name'=>$_GPC['shop_name'],
		'shop_tel'=>$_GPC['shop_tel'],
		'shop_address'=>$_GPC['shop_address'],
		'shop_notice'=>$_GPC['shop_notice'],
		'address_list'=>$_GPC['address_list'],
		'desk_list'=>$_GPC['desk_list'],
		'room_list'=>$_GPC['room_list'],
		'lng'=>$_GPC['lng'],
		'lat'=>$_GPC['lat'],
		'yy_start_time'=>$_GPC['yy_start_time'],
		'yy_end_time'=>$_GPC['yy_end_time'],
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