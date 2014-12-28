<?php
/**
 * 微团购模块微站定义
 *
 * @author 
 * @url 
 */
	$id= intval($_GPC['id']);
 	if ($_GPC['action']=='save') {
		$insert=array(
			'notice'=>$_GPC['notice'],
		);
		$temp = pdo_update('groupon_list', $insert,array('weid'=>$_W['weid'],'id'=>$id));
		if($temp===false){
			$this->message('抱歉，刚才添加的数据失败！','', -1);              
		}else{
			$this->message('团购商品通知数据成功！', create_url('site/module', array('do' => 'goods', 'name' => 'groupon')), 0);      
		}
	}
	$row = pdo_fetch("SELECT id,title,notice FROM ".tablename('groupon_list')." WHERE  weid=:weid  AND id={$_GPC['id']}" , array(':weid'=>$_W['weid']));				
	if($row==false){
		$this->error('参数错误，或者团购已经被删除');
	}
	include $this->template('notice');			