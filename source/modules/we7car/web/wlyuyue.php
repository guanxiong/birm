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
			$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']);
	
		if(empty($_GPC['name_show'])) $_GPC['name_show']=0;
		if(empty($_GPC['tel_show'])) $_GPC['tel_show']=0;
		if(empty($_GPC['date_show'])) $_GPC['date_show']=0;
		if(empty($_GPC['time_show'])) $_GPC['time_show']=0;
		
		$trtxt=array();		
		for($i=0;$i<20;$i++){
			if(!empty($_GPC['txt'.$i])){				
				$tempArr['txt']=$_GPC['txt'.$i];
				$tempArr['value']=$_GPC['value'.$i];
				$trtxt[]=$tempArr;
				unset($tempArr);
			}else{
				break;
			}
		}
		$strtxt=array();
		for($i=0;$i<10;$i++){
			if(!empty($_GPC['select'.$i])){
				$tempArr['select']=$_GPC['select'.$i];
				$tempArr['svalue']=$_GPC['svalue'.$i];
				$strtxt[]=$tempArr;
				unset($tempArr);					
			}else{
				break;
			}
		}	
		$insert = array(
			'weid' => $_W['weid'],
			'title' => $_GPC['title'],
			'yytype' => $_GPC['yytype'],
			'thumb' => $_GPC['picture'],
			'description' => $_GPC['description'],			
			'start_time' => $starttime,
			'address' => $_GPC['address'],
			'mobile' => $_GPC['mobile'],
			'end_time' => $endtime,
			'location_x' => $_GPC['location_x'],
			'location_y' => $_GPC['location_y'],
			
			'topbanner' => $_GPC['topbanner'],

			'typename1' => $_GPC['typename1'],
			'typename2' => $_GPC['typename2'],
			'typename3' => $_GPC['typename3'],
			
			'name_show' => $_GPC['name_show'],
			'tel_show' => $_GPC['tel_show'],
			'date_show' => $_GPC['date_show'],
			'time_show' => $_GPC['time_show'],
			'datename'	 => $_GPC['datename'],
			'copyright'	 => $_GPC['copyright'],
			'shareurl'	 => $_GPC['shareurl'],
			'isshow' => $_GPC['isshow'],
			'trtxt'=>serialize($trtxt),
			'strtxt'=>serialize($strtxt),			
		);
		
		if($id==0){
			$temp = pdo_insert('weicar_yuyue_set', $insert);
		}else{
			$temp = pdo_update('weicar_yuyue_set', $insert,array('id'=>$id));
		}
		if($temp==false){
			message('抱歉，刚才操作的数据失败！','', 'error');              
		}else{
			message('更新设置数据成功！', create_url('site/module', array('do' => 'Yuyue','op'=>'list', 'name' => 'we7car')), 'success');      
		}			
	}			
 	if($id>0){
		$item = pdo_fetch("SELECT * FROM ".tablename('weicar_yuyue_set')." WHERE  weid=:weid  AND id={$id}" , array(':weid'=>$_W['weid']));		

	}
	//数据为空，赋值
	if($item==false){
		$item=array(
			'start_time'=>time(),
			'end_time'=>strtotime("+7 day"),
			'isshow'=>1,
		);
	}else{
		$trtxt=unserialize($item['trtxt']);
		unset($item['trtxt']);
		$strtxt=unserialize ($item['strtxt']);
		unset($item['strtxt']);
	}
	$brand = pdo_fetchall("SELECT * FROM ".tablename('weicar_brand')." WHERE weid = ".$weid."  order by listorder desc");		
	if(!empty($theone['bid'])){
		$series=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_series')." WHERE weid = ".$weid." and bid=".$theone['bid']."  order by listorder desc");					
	}	
	include $this->template('web/yuyue_post');
}elseif($op=='list'){
	$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_yuyue_set')." WHERE weid = ".$weid."  order by id desc");
	
	include $this->template('web/yuyue_list');
}elseif($op=='show'){
	if(!empty($_GPC['status'])){
		//处理状态
		$id=$_GPC['id'];
		$status=$_GPC['status'];
		$temp=pdo_update('weicar_yuyue_list',array('status'=>$status), array('id' => $id,'weid'=>$weid));
		if($temp==false){
			message('抱歉，修改状态失败！','', 'error');              
		}else{
			message('成功修改预约订单状态！', create_url('site/module', array('do' => 'Yuyue', 'op'=>'show','name' => 'we7car','weid'=>$weid)), 'success');      
		}		

	}
	
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;
	$condition = '';
 
	if (isset($_GPC['status'])) {
		$condition .= " AND status = '".intval($_GPC['status'])."'";
	}
	$list = pdo_fetchall("select * from ".tablename('weicar_yuyue_list')." where weid=".$weid ."  order by id desc LIMIT ".($pindex - 1) * $psize.','.$psize); 
	$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('weicar_yuyue_list') . "  WHERE weid = '{$weid}' $condition");
	$pager = pagination($total, $pindex, $psize);

	include $this->template('web/yuyue_show');
}elseif($op=='changestatus'){
	//处理状态
	$id=$_GPC['id'];
	$status=$_GPC['status'];
	$temp=pdo_update('weicar_yuyue_list',array('status'=>$status), array('id' => $id,'weid'=>$weid));
	if($temp==false){
		message('抱歉，修改状态失败！','', 'error');              
	}else{
		message('成功修改预约订单状态！', create_url('site/module', array('do' => 'Yuyue','op'=>'show', 'name' => 'we7car','weid'=>$weid)), 'success');      
	}		
}
 