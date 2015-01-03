<?php
/**
 * 首页
 *
 * @author 超级无聊
 * @url
 */ 
	$oid=$_GPC['oid'];
	
	if($subcp=='refund'){
		$temp=pdo_update('groupon_order',array('status'=>-1),array('weid'=>$weid,'from_user'=>$from,'id'=>$oid));
		if($temp===false){
			$data=array(
				'result'=>0,
			);
		}else{
			$data=array(
				'result'=>1,
			);		
		}
		die(json_encode($data));
	
	
	}else{
		$condition=" AND a.id=".$oid." ";
		$item = pdo_fetch("SELECT a.*,b.title,b.price,b.thumb_list,b.valid_endtime,valid_starttime FROM ".tablename('groupon_order')." as a 
		left join ".tablename('groupon_list')." as b on a.tid=b.id
		WHERE a.weid = '{$weid}'  $condition");
	 
		include $this->template('wl_orderdetail');
	}