<?php
/**
 * 一张独立图片存放在page thumb中
 * 场景独立参数，存在param中
 * 排序id越大
 * 
 * 
 */
	
	defined('IN_IA') or exit('Access Denied');

	$id=intval($_GPC['id']);
	if($id>0){
		$item=pdo_fetch('select * from '.tablename('izc_lightbox_page').' where id=:id AND list_id=:list_id AND weid=:weid',array(':weid'=>$_W['weid'],':list_id'=>$list_id,':id'=>$id));
	}
	if($_GPC['op']=='del'){
		if($item!=false){
			$temp=pdo_delete('izc_lightbox_page',array('id'=>$item['id']));
		}
		if($temp==false){
			$this->message('数据提交失败');
		}else{
			$this->message('数据提交成功',$this->createWeburl('listpage',array('list_id'=>$list_id)),'success');
		}
	}
	//保存数据
	if($_W['ispost']){
		$insert=array(
			'weid'=>$weid,
			'list_id'=>$list_id,
			'listorder'=>intval($_GPC['listorder']),
			'm_type'=>intval($_GPC['m_type']),
			'thumb'=>$_GPC['thumb'],
		);
		 
		if($insert['m_type']==11){
			$data=$_GPC['pics'];
		}elseif($insert['m_type']==12){
			$data=$_GPC['txtpic'];
		}elseif($insert['m_type']==13){
			$data=$_GPC['last'];
		}  
		
		if(!empty($data)){
			$insert['param']=iserializer($data);
		}
		if($item==false){
			$temp=pdo_insert('izc_lightbox_page',$insert);
		}else{
			$temp=pdo_update('izc_lightbox_page',$insert,array('id'=>$item['id']));
		}
		if($temp===false){
			$this->message('数据提交失败');
		}else{
			$this->message('数据提交成功',$this->createWeburl('listpage',array('list_id'=>$list_id)),'success');
		}
	}
	 
	if($item==false){
		$item=array(
			'listorder'=>0,
			'thumb'=>$_W['siteroot'].'source/modules/izc_lightbox/style/img/default_bg.jpg',
		);
		$data=array(
		
		);
	}else{
		$data=iunserializer($item['param']);
	}
	if(empty($data['btnimg'])){
		$data['btnimg']=$_W['siteroot'].'source/modules/izc_lightbox/style/img/default_btn.png';
	}
	 
	include $this->template($list['iden'].'/page');