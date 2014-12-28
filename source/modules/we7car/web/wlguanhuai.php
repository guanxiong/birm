<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
if($_GPC['action']=="save"){
	//保存数据
	$insert=array(
		'weid'=>$weid,				
		'guanhuai_thumb'=>$_GPC['guanhuai_thumb'],	
		'create_time'=>time(),
	);
	$temp = pdo_update('weicar_set', $insert,array('weid'=>$weid));
	if($temp==false){
		$temp = pdo_insert('weicar_set', $insert);
	}
	if($temp==false){
		message('抱歉，刚才操作的数据失败！','', 'error');              
	}else{
		message('更新设置数据成功！', create_url('site/module', array('do' => 'Guanhuai','op'=>'set', 'name' => 'we7car','weid'=>$weid)), 'success');      
	}	
 }
$row = pdo_fetch  ("SELECT guanhuai_thumb FROM ".tablename('weicar_set')." WHERE  weid=:weid  " , array(':weid'=>$_W['weid']));				
include $this->template('web/guanhuai_set');
