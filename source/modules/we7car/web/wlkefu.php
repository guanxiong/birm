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
		if (empty($_GPC['kefuname'])) {
			message('抱歉，请输入客服名称，请返回修改！');
		}
		if (empty($_GPC['kefutel'])) {
			message('抱歉，请输入客服电话，请返回修改！');
		}
		//保存数据
		//保存数据
		if(empty($_GPC['pre_sales'])) $_GPC['pre_sales']=0;
		if(empty($_GPC['aft_sales'])) $_GPC['aft_sales']=0;	
		if($_GPC['pre_sales']==0&&$_GPC['aft_sales']==0){
			message('抱歉，请选择客服类型，请返回修改！');
		}
		$insert=array(
			'weid'=>$weid,
			'listorder'=>$_GPC['listorder'],
			'kefuname'=>$_GPC['kefuname'],				
			'headthumb'=>$_GPC['headthumb'],
			'kefutel'=>$_GPC['kefutel'],				
			'pre_sales'=>$_GPC['pre_sales'],				
			'aft_sales'=>$_GPC['aft_sales'],								
			'description'=>$_GPC['description'],	
			'status'=>$_GPC['status'],	
			'createtime'=>time(),
		);	
		if($id==0){
			$temp = pdo_insert('weicar_kefu', $insert);
		}else{
			$temp = pdo_update('weicar_kefu', $insert,array('id'=>$id));
		}
		if($temp==false){
			message('抱歉，刚才操作的数据失败！','', 'error');              
		}else{
			message('更新设置数据成功！', create_url('site/module', array('do' => 'Kefu','op'=>'list', 'name' => 'we7car')), 'success');      
		}			
	}			
 	if($id>0){
		$theone = pdo_fetch("SELECT * FROM ".tablename('weicar_kefu')." WHERE  weid=:weid  AND id={$id}" , array(':weid'=>$_W['weid']));				
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
	include $this->template('web/kefu_post');
}elseif($op=='list'){
	$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_kefu')." WHERE weid = ".$weid." order by listorder desc");
 	include $this->template('web/kefu_list');
}elseif($op=='del'){
	$id= intval($_GPC['id']);
	$kefu= pdo_fetch("SELECT headthumb FROM ".tablename('weicar_kefu')." WHERE weid=".$weid." and id=".$id." limit 1");		 		
	if($kefu!=false){
		if(!empty($kefu['headthumb'])){
			file_delete($kefu['headthumb']);		
		}
	}
	unset($kefu);
	$temp = pdo_delete("weicar_kefu",array("weid"=>$weid,'id'=>$id));
	if($temp==false){
		message('抱歉，刚才修改的数据失败！','', 'error');              
	}else{
		message('删除数据成功！', create_url('site/module', array('do' => 'Kefu', 'name' => 'we7car','weid'=>$weid)), 'success');      
	}		
}
