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
		$insert=array(
			'weid'=>$weid,
			'listorder'=>$_GPC['listorder'],
			'title'=>$_GPC['title'],
			'bid'=>$_GPC['bid'],				
			'sid'=>$_GPC['sid'],	
			'pyear'=>$_GPC['pyear'],	
			'price1'=>$_GPC['price1'],				
			'price2'=>$_GPC['price2'],				
			'thumb'=>$_GPC['thumb'],				
			'output'=>$_GPC['output'],				
			'gearnum'=>$_GPC['gearnum'],				
			'gear_box'=>$_GPC['gear_box'],								
			'xiangceid'=>$_GPC['xiangceid'],	
			'status'=>$_GPC['status'],												
			'createtime'=>time(),
		);
		if(!empty($_GPC['thumb_url'])){
			$insert['thumb']=$_GPC['thumb_url'][0];  
			$insert['thumbArr']=implode('|',$_GPC['thumb_url']);
		}else{
			$insert['thumb']=NULL;  
			$insert['thumbArr']=NULL;
		}
		
		if($id==0){
			$temp = pdo_insert('weicar_type', $insert);
		}else{
			$temp = pdo_update('weicar_type', $insert,array('id'=>$id));
		}
		if($temp==false){
			message('抱歉，刚才操作的数据失败！','', 'error');              
		}else{
			message('更新设置数据成功！', create_url('site/module', array('do' => 'Type','op'=>'list', 'name' => 'we7car')), 'success');      
		}			
	}			
 	if($id>0){
		$theone = pdo_fetch("SELECT * FROM ".tablename('weicar_type')." WHERE  weid=:weid  AND id={$id}" , array(':weid'=>$_W['weid']));		
		if(!empty($theone['thumbArr'])){
			$theone['thumb_url']=explode('|',$theone['thumbArr']);	
		}		
	}
	//数据为空，赋值
	if(empty($theone)){
		$theone=array(
			'listorder'=>0,
			'status'=>1,
		);
	}
	$brand = pdo_fetchall("SELECT * FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc");		
	if(!empty($theone['bid'])){
		$series=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_series')." WHERE weid = ".$weid." and bid=".$theone['bid']."  order by listorder desc");					
	}	
	include $this->template('web/type_post');
}elseif($op=='list'){
	$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_type')." WHERE weid = ".$weid."  order by listorder desc");

	$brandArr=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc",array(),"id");		

	$seriesArr=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_series')." WHERE weid = ".$weid."  order by listorder desc",array(),"id");		

	
	include $this->template('web/type_list');
}elseif($op=='del'){
	$id= intval($_GPC['id']);
	$temp = pdo_delete("weicar_type",array("weid"=>$weid,'id'=>$id));
	if($temp==false){
		message('抱歉，刚才修改的数据失败！','', 'error');              
	}else{
		message('删除数据成功！', create_url('site/module', array('do' => 'Type','op'=>'list','name' => 'we7car','weid'=>$weid)), 'success');      
	}		
}