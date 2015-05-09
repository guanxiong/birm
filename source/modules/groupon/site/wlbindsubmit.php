<?php
/**
 * 详情
 *
 * @author 超级无聊
 * @url
 */	
	WeSession::start();
	$code=$_SESSION['code'];
	if($_POST['validCode']==$code){
		$data=array(
			'errno'=>0,
		);
		//改变用户密码
		fans_update($_GPC['wechatid'],array('mobile'=>$_GPC['telephone']));
	}else{
		$data=array(
		'errno'=>-1,
		);
	}
	echo json_encode($data);