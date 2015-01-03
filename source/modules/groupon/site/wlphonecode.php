<?php
/**
 * 详情
 *
 * @author 超级无聊
 * @url
 */	WeSession::$expire = 600;	
	WeSession::start();
	if(isset($_SESSION['code'])){
		$code=$_SESSION['code'];
	}else{
		$code= random(6,1);
		$_SESSION['code']=$code;
	}
	
	$data=array(
		'errno'=>0,
		'code'=>$code,
	);
 
	echo json_encode($data);