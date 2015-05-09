<?php
/**
 * 首页
 *
 * @author 超级无聊
 * @url
 */	$tid = intval($_GPC['tid']);
	$groupon = pdo_fetch("SELECT * FROM ".tablename('groupon_list')." WHERE id={$tid} AND weid = '{$_W['weid']}' AND status = '1' ");
	 
 	if (empty($groupon)) {
		message('抱歉，商品不存在或是已经被删除！');
	}else{
		  
	}
	$users = fans_search($_W['fans']['from_user'], array('realname', 'mobile'));
	if($users==false){
		message('非法访问途径，请从微信公共号访问！');
	}else{
		if(!empty($users['mobile'])){
			$fans= pdo_fetch("SELECT * FROM ".tablename('groupon_fans')." WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}'");
			if($fans==false){
				$temp=pdo_insert('groupon_fans',array('weid'=>$_W['weid'],'from_user'=>$_W['fans']['from_user'],'mobile'=>$users['mobile']));
				$users['fansid']=pdo_insertid();
			}else{
				$users['fansid']=$fans['id'];
			}
		}
	}
 	include $this->template('wl_check');