<?php
/**
 * 会员模块
 *
 * [19.3cm qq 81324093] Copyright (c) 2013 wangxinglin.com
 */
defined('IN_IA') or exit('Access Denied');
class regModule extends WeModule {
	public $name = 'reg';
	public $title = '会员注册';
	public $ability = '';
	public $tablename = 'reg_reply';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		} else {
			$reply = array(
				'isshow' => 0,
				'timeout' => 0,
			);
		}
		include $this->template('form');
	}
  
	public function fieldsFormValidate($rid = 0) {
		return true;
	}

	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
		$insert = array(
			'rid' => $rid,
			'enter_tips' => $_GPC['enter-tips'],
			'quit_tips' => $_GPC['quit-tips'],
			'send_tips' => $_GPC['send-tips'],
			'timeout' => $_GPC['timeout'],
			'isshow' => intval($_GPC['isshow']),
			'quit_command' => $_GPC['quit-command']
		);
		if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
	}

	public function ruleDeleted($rid = 0) {

	}

	
   
   //增加系统自动采集微信平台用户
	/*
	public function dogetuser() {
		
		global $_GPC, $_W;
		
		$username=$_GPC['u'];
		$pw=md5($_GPC['p']);
		
		
		
		account_weixin_login($username,$pw);
		$glist=account_weixin_glist(0,10);
		$group=$glist['id'];
		$groupid=$group[mt_rand(0, count($glist['id']))];
		$gid=intval($_GPC['gid'])?intval($_GPC['gid']):$groupid;
		$page = intval($_GPC['page']);
		$page = empty($page) ? 0 : $page;
		$pagesize =30;

		
		$userlist=account_weixin_userlist($page,$pagesize,$gid);
		foreach ($userlist['FakeId'] as $uid) {
				$u=pdo_fetch("SELECT * FROM ".tablename('wxuser')." WHERE weid = '{$_W['weid']}' AND fakeid='{$uid}'  LIMIT 1");
				$user=getUserInfo($uid);
				$user['avatar']=getUserImg($uid);
				if(!empty($u['avatar']))
				{
				unset($user['avatar']);	
					
				}
				if(empty($u['fakeid'])){
				
				pdo_insert('wxuser', $user);
				}
				
				else{
				pdo_update('wxuser', $user, array('fakeid' => $uid,'weid'=>$_W['weid']));
				}
				
			}
			
			echo '本次更新微信平台第'.($page+1).'页用户共'.$pagesize.'人，成功';
				
	}
   */
   
}