<?php
/**
 * 我的订单
 *
 * @author 微动力
 * @url
 */
WeSession::$expire = 600;	
WeSession::start();
$sms_resgister = pdo_fetchcolumn ("SELECT sms_resgister FROM ".tablename('shopping3_set')." WHERE weid = :weid", array(':weid' => $weid));
if($_GPC['action']=='sendcode'){
 	$return=array(
		'status'=>0,
	);
	$fans = pdo_fetch("SELECT id FROM ".tablename('shopping3_fans')." WHERE weid = '{$weid}' AND phone='{$_GPC['telephone']}'");
	if($fans!=false){
		//已经注册
		die(json_encode(array('status'=>-2)));
	}
	if(empty($_GPC['telephone'])){
		die(json_encode($return));
	}
	if($_GPC['telephone']==$_SESSION['phone']){
		$code=$_SESSION['code'];
	}else{
		$code= random(6,1);
		$_SESSION['phone']=$_GPC['telephone'];
		$_SESSION['code']=$code;
	}
	$return=array(
		'status'=>1,
		'code'=>$code,
	);
	die(json_encode($return));
}elseif($_GPC['action']=='save'){
	//如果开启了短信验证，则判断验证码不能为空
	if($sms_resgister==1&&empty($_GPC['Checkcode'])){
		die(json_encode(array('status'=>-1)));
	}
	if(empty($_GPC['MobilePhone']) || empty($_GPC['Password'])){
		die(json_encode(array('status'=>-1)));
	};
	
	//如果开启了短信验证，则判断手机号和验证码是否匹配
	if($sms_resgister==1&&$_GPC['MobilePhone']==$_SESSION['phone']&&$_GPC['Checkcode']==$_SESSION['code']){
		die(json_encode(array('status'=>-3)));
	}
	
	//判断手机号码是否存在
	$fans = pdo_fetch("SELECT id FROM ".tablename('shopping3_fans')." WHERE weid = '{$weid}' AND phone='{$_GPC['MobilePhone']}'");
	if($fans!=false){
		//已经注册
		die(json_encode(array('status'=>-2)));
	}else{
		$insert=array(
			'weid'=>$weid,
			'phone'=>$_GPC['MobilePhone'],
			'password'=>md5($_GPC['Password']),
			'crteate_time'=>time(),
		);
		$temp=pdo_insert('shopping3_fans',$insert);
		if($temp==false){
			die(json_encode(array('status'=>-4)));
		}else{
			//更新fans的from_user
			$fansid=pdo_insertid();
			if(empty($from)){
				$from='nowxuser_'.$fansid;
			}
			//更新fans
			$temp=pdo_update('shopping3_fans',array('from_user'=>$from),array('id'=>$fansid));
			die(json_encode(array('status'=>1,'jump_url'=>$this->createMobileUrl('wlmember',array('weid' => $weid,'openid'=>$from)))));
		}
	}
	 
 
}include $this->template('wl_register');
exit;