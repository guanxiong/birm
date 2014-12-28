<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
if($subcp=='ajax'){
	if($_GPC['appid']==1){
		if($_GPC['chk']=='true'){
			pdo_update('groupon_set',array('payset1'=>1),array('weid'=>$_W['weid']));
		}else{
			pdo_update('groupon_set',array('payset1'=>0),array('weid'=>$_W['weid']));
		}
	}elseif($_GPC['appid']==2){
		if($_GPC['chk']=='true'){
			pdo_update('groupon_set',array('payset2'=>1),array('weid'=>$_W['weid']));
		}else{
			pdo_update('groupon_set',array('payset2'=>0),array('weid'=>$_W['weid']));
		}	
	}elseif($_GPC['appid']==3){
		if($_GPC['chk']=='true'){
			pdo_update('groupon_set',array('payset3'=>1),array('weid'=>$_W['weid']));
		}else{
			pdo_update('groupon_set',array('payset3'=>0),array('weid'=>$_W['weid']));
		}	
	}
}else{
	//判断set是否存在，不存在则创建一个
	$payset=pdo_fetch("SELECT * FROM ".tablename('groupon_set')." WHERE  weid = '{$_W['weid']}'");
	if($payset==false){
		pdo_insert('groupon_set',array('weid'=>$_W['weid']));
		$payset=array(
			'payset1'=>0,
			'payset2'=>0,
			'payset3'=>0,
		);
	}
	
	include $this->template('payset');			
}