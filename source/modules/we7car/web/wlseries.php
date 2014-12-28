<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
if($op=='post'){
	$id= intval($_GPC['id']);
	if ($_GPC['submit']!=''){
		if (empty($_GPC['bid'])) {
			message('抱歉，请选择车辆品牌，请选回修改！');
		}
		if (empty($_GPC['title'])) {
			message('抱歉，车系名称必填项，请选回修改！');
		}
		//保存数据
			//保存数据
		$insert=array(
			'weid'=>$_W['weid'],
			'listorder'=>$_GPC['listorder'],
			'bid'=>$_GPC['bid'],				
			'title'=>$_GPC['title'],
			'subtitle'=>$_GPC['subtitle'],				
			'thumb'=>$_GPC['thumb'],				
			'description'=>$_GPC['description'],				
			'createtime'=>time(),
			'status'=>$_GPC['status'],								
		);
		if($id==0){
			$temp = pdo_insert('weicar_series', $insert);
		}else{
			$temp = pdo_update('weicar_series', $insert,array('id'=>$id));
		}
		if($temp==false){
			message('抱歉，刚才操作的数据失败！','', 'error');              
		}else{
			message('更新设置数据成功！', create_url('site/module', array('do' => 'Series','op'=>'list', 'name' => 'we7car')), 'success');      
		}			
	}			
 	if($_GPC['id']>0){
		$theone = pdo_fetch("SELECT * FROM ".tablename('weicar_series')." WHERE  weid=:weid  AND id={$_GPC['id']}" , array(':weid'=>$_W['weid']));				
	}
	//数据为空，赋值
	if(empty($theone)){
		$theone=array(
			'listorder'=>0,
			'status'=>1,
		);
	}
	$brand = pdo_fetchall("SELECT * FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc");			
	include $this->template('web/series_post');
}elseif($op=='list'){
	$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_series')." WHERE weid = ".$weid."  order by listorder desc");
	$series=pdo_fetchall("SELECT * FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc");		
	$seriesArr=array();
	foreach($series as $v){
		$seriesArr[$v['id']]=$v['title'];
	}
	include $this->template('web/series_list');
}elseif($op=='del'){
	$id= intval($_GPC['id']);
	$up = pdo_fetch("SELECT * FROM ".tablename('weicar_type')." WHERE  weid=:weid  AND sid={$id}" , array(':weid'=>$_W['weid']));		
	if($up!=false){
		message('抱歉，此车系下存在车型，无法删除！','', 'error');              
	}

	$temp = pdo_delete("weicar_series",array("weid"=>$weid,'id'=>$id));
	if($temp==false){
		message('抱歉，刚才修改的数据失败！','', 'error');              
	}else{
		message('删除数据成功！', create_url('site/module', array('do' => 'Series','op'=>'list','name' => 'we7car','weid'=>$weid)), 'success');      
	}		
}