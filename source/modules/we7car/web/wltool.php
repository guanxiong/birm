<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
 if($_GPC['action']=="change"){
 
	//保存数据
	$insert=array(
		'weid'=>$weid,				
		'tools'=>$_GPC['tools'],	
		'create_time'=>time(),
	);
	
	$temp = pdo_update('weicar_set', $insert,array('weid'=>$weid));
	if($temp==false){
		$temp = pdo_insert('weicar_set', $insert);
	}
	if($temp==false){
		message('抱歉，刚才操作的数据失败！','', 'error');              
	}else{
		message('更新设置数据成功！', create_url('site/module', array('do' => 'Message','op'=>'set', 'name' => 'we7car','weid'=>$weid)), 'success');      
	}	
 }
 	$tools = pdo_fetchcolumn  ("SELECT tools FROM ".tablename('weicar_set')." WHERE  weid=:weid  " , array(':weid'=>$_W['weid']));				
	if($tools===false){
		$toolsArr=array(
			'1'=>true,
			'2'=>true,
			'3'=>true,
			'4'=>true,
			'5'=>true,
		);
	}else{
		$toolsArr=array();
		$tools=explode(",",$tools);
		foreach($tools as $v){
			$toolsArr[$v]=true;
		}
	}
include $this->template('web/tool_set');
