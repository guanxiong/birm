<?php
/**
 * 首次验证手机号码
 *
 * @author 微动力
 * @url
 */WeSession::$expire = 600;	
WeSession::start();
	if($_GPC['action']=='code'){
		if($_GPC['mobile']==$_SESSION['phone']){
			$rnd=$_SESSION['code'];
		}else{
			$rnd= random(6,1);
			$_SESSION['phone']=$_GPC['mobile'];
			$_SESSION['code']=$rnd;
		}
		//将缓存保存在session中
		$return=array(
			'code'=>'1',
			'msg'=>'验证码发送成功',	
			'lefttime'=>60,
		);
		//发送短信验证码
		$txt="【微信验证】您的本次操作的验证码为：".$rnd.".十分钟内有效";
		$this->_sendsms($txt,$_GPC['mobile']);
		
		echo json_encode($return);
		exit;
	}elseif($_GPC['action']=='reg'){
		//验证码验证
		if($_GPC['mobile']==$_SESSION['phone']&&$_GPC['verificode']==$_SESSION['code']){
			$fans=pdo_fetch("select * from ".tablename("shopping3_fans")." where weid=:weid AND  from_user=:from_user",array(':weid'=>$_W['weid'],':from_user'=>$_W['fans']['from_user']));
			if($fans==false){
				$insert=array(
					'weid'=>$_W['weid'],
					'phone'=>$_GPC['mobile'],
					'sex'=>intval($_GPC['pvSex']),
					'from_user'=>$_W['fans']['from_user'],
				);
				$temp=pdo_insert('shopping3_fans',$insert);
			}else{
				$insert=array(
					'phone'=>$_GPC['mobile'],
					'sex'=>intval($_GPC['pvSex']),
					//'from_user'=>$_W['fans']['from_user'],				
				);
				$temp=pdo_update('shopping3_fans',$insert,array('id'=>$fans['id']));
			}
			if($temp==false){
				message('数据保存失败');
			}else{
				message('',$this->createMobileUrl('wlindex'));
			}
		}else{
			message("验证码错误，请重新输入");
		}
	}
	include $this->template('wl_reg');