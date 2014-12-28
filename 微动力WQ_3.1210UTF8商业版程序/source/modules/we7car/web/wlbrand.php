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
		if (empty($_GPC['title'])) {
			message('抱歉，品牌名称必填项，请选回修改！');
		}
		//保存数据
		$insert=array(
			'weid'=>$_W['weid'],
			'listorder'=>$_GPC['listorder'],
			'title'=>$_GPC['title'],
			'officialweb'=>$_GPC['officialweb'],				
			'logo'=>$_GPC['logo'],				
			'description'=>$_GPC['description'],			
			'status'=>$_GPC['status'],
			'createtime'=>time(),
		);
		if($id==0){
			//
			$count=pdo_fetchcolumn("SELECT count(id) FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  ");
			if($count>0){
				message('目前系统仅仅支持一个品牌.');
			}
			$temp = pdo_insert('weicar_brand', $insert);
		}else{
			$temp = pdo_update('weicar_brand', $insert,array('id'=>$id));
		}
		if($temp==false){
			message('抱歉，刚才操作的数据失败！','', 'error');              
		}else{
			message('更新设置数据成功！', create_url('site/module', array('do' => 'Brand','op'=>'list', 'name' => 'we7car')), 'success');      
		}			
	}			
 	if($_GPC['id']>0){
		$theone = pdo_fetch("SELECT * FROM ".tablename('weicar_brand')." WHERE  weid=:weid  AND id=".$id."" , array(':weid'=>$weid));				
	}else{
		$count=pdo_fetchcolumn("SELECT count(id) FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  ");
		if($count>0){
			message('目前系统仅仅支持一个品牌.');
		}
	
	}
	//数据为空，赋值
	if(empty($theone)){
		$theone=array(
			'listorder'=>0,
			'status'=>1,
		);
	}
	include $this->template('web/brand_post');
}elseif($op=='list'){
    $list = pdo_fetchall("SELECT * FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc");
	include $this->template('web/brand_list');
}elseif($op=='del'){
	$id= intval($_GPC['id']);
	$up = pdo_fetch("SELECT * FROM ".tablename('weicar_series')." WHERE  weid=:weid  AND bid={$id}" , array(':weid'=>$_W['weid']));		
	if($up!=false){
		message('抱歉，此品牌下存在车系，无法删除！','', 'error');              
	}
	
	$temp = pdo_delete("weicar_brand",array("weid"=>$weid,'id'=>$id));
	if($temp==false){
		message('抱歉，刚才修改的数据失败！','', 'error');              
	}else{
		message('删除数据成功！', create_url('site/module', array('do' => 'Brand','op'=>'list','name' => 'we7car','weid'=>$weid)), 'success');      
	}		
}