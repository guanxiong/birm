<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
if($op=='set'){
	$id= intval($_GPC['id']);
	if ($_GPC['submit']!=''){
		if (empty($_GPC['title'])) {
			message('抱歉，请输入显示名称，请返回修改！');
		}
		if (empty($_GPC['thumb'])) {
			message('抱歉，请输入头部图片，请返回修改！');
		}
		//保存数据
		$insert=array(
			'weid'=>$weid,
			'title'=>$_GPC['title'],
			'thumb'=>$_GPC['thumb'],
			'status'=>$_GPC['status'],				
			'isshow'=>$_GPC['isshow'],	
			'create_time'=>time(),
		);
		if($id==0){
			$temp = pdo_insert('weicar_message_set', $insert);
		}else{
			$temp = pdo_update('weicar_message_set', $insert,array('id'=>$id));
		}
		if($temp==false){
			message('抱歉，刚才操作的数据失败！','', 'error');              
		}else{
			message('更新设置数据成功！', create_url('site/module', array('do' => 'Message','op'=>'set', 'name' => 'we7car','weid'=>$weid)), 'success');      
		}			
	}			
	$theone = pdo_fetch("SELECT * FROM ".tablename('weicar_message_set')." WHERE  weid=:weid  " , array(':weid'=>$_W['weid']));				
	//数据为空，赋值
	if(empty($theone)){
		$theone=array(
			'status'=>1,
			'isshow'=>1,
		);
	}
 	
	include $this->template('web/message_set');
}elseif($op=='list'){
	if (checksubmit('verify') && !empty($_GPC['select'])) {
		pdo_update('weicar_message_list', array('isshow' => 1, 'create_time' => TIMESTAMP), " id  IN  ('".implode("','", $_GPC['select'])."')");
		message('审核成功！', create_url('site/module', array('do' => 'Message', 'name' => 'we7car', 'weid' => $weid, 'page' => $_GPC['page'],'isshow'=>$_GPC['isshow'])));
	}
	if (checksubmit('delete') && !empty($_GPC['select'])) {
		pdo_delete('weicar_message_list', " id  IN  ('".implode("','", $_GPC['select'])."')");
		message('删除成功！', create_url('site/module', array('do' => 'Message', 'name' => 'we7car', 'weid' => $weid, 'page' => $_GPC['page'],'isshow'=>$_GPC['isshow'])));
	}
	$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
	$pindex = max(1, intval($_GPC['page']));
	$psize = 20;

	$message = pdo_fetch("SELECT id, isshow, weid FROM ".tablename('weicar_message_set')." WHERE weid = '{$weid}' LIMIT 1");
	$list = pdo_fetchall("SELECT * FROM ".tablename('weicar_message_list')." WHERE weid = '{$message['weid']}' AND isshow = '$isshow' ORDER BY create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
	if (!empty($list)) {
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('weicar_message_list') . " WHERE weid = '{$message['weid']}' AND isshow = '$isshow'");
		$pager = pagination($total, $pindex, $psize);

		foreach ($list as &$row) {
			$row['content'] = emotion($row['content']);
			$userids[] = $row['from_user'];
		}
		unset($row);
	}
	include $this->template('web/message_list');
}