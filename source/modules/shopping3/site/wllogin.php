<?php
/**
 * 我的订单
 *
 * @author 微动力
 * @url
 */if(empty($from)){
	//来路
	$nofrom=false;
}else{
	//微信来路
	$nofrom=true;
	$user = pdo_fetch("SELECT id FROM ".tablename('shopping3_fans')." WHERE weid = '{$weid}'  AND from_user='".$from."'");
}
 if($_GPC['action']=='save'){	
	$return=array(
		'status'=>0,
	);
 	if(empty($_GPC['MobilePhone'])||empty($_GPC['MobilePhone'])){
		die(json_encode($return));
	}
	//用户绑定用户，或者注册用户
	$phone=$_GPC['MobilePhone'];
	$password=Md5($_GPC['Password']);
	//验证有效性
	$fans = pdo_fetch("SELECT * FROM ".tablename('shopping3_fans')." WHERE weid = '{$weid}'  AND phone='".$phone."' AND password='".$password."'");
	if($fans==false){
		die(json_encode($return));
	}
	
	if($nofrom==false){
		//直接跳转
		$return =array(
			'status'=>1,
			'msg'=>'登录成功',
			'jump_url'=>$this->createMobileUrl('wlmember',array('weid' => $weid,'openid'=>$fans['from_user'])),
		);
		die(json_encode($return));	
	}else{
		//有from，判断是绑定还是登录
		//原来没有这个用户
		if($user==false){
			//新绑定,更新fans的from_user
			if(empty($from)){
				$temp=pdo_update('shopping3_fans',array('from_user'=>$from),array('weid'=>$weid,'id'=>$fans['id']));
			}
			$from=$fans['from_user'];
		}else{
			//旧的重新绑定，删除旧的
			//解除旧的//绑定新的
			if(!empty($from)){
				$temp=pdo_update('shopping3_fans',array('from_user'=>'nowxuser_'.$user['id']),array('weid'=>$weid,'id'=>$user['id']))&&pdo_update('shopping3_fans',array('from_user'=>$from),array('weid'=>$weid,'id'=>$fans['id']));
			}
		}
		if($temp!=false){
			$return =array(
				'status'=>1,
				'msg'=>'绑定成功',
				'jump_url'=>$this->createMobileUrl('wlmember',array('weid' => $weid,'openid'=>$from)),
			);
			die(json_encode($return));
		}else{
			die(json_encode($return));
		}
	}
}
include $this->template('wl_login');
exit;