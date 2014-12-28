<?php
/**
 * 订阅就绑定FAKEID
 *
 * @author 19.3CM
 * @QQ 81324093
 */
defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/19.3cm.php';
class RegModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_W;
		//增加关注就插入最基础的FROMID和WEID 以及绑定微信号
		
		
		if($this->message['type']=='subscribe'){
			
				//高级权限号执行
				$u=pdo_fetch("SELECT id,from_user,nickname,avatar FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND from_user='{$this->message['from']}'  LIMIT 1");
				if(empty($u['nickname'])||empty($u['avatar'])){
					//增加判断用户昵称或者头像为空，才执行重取同步微信资料
					$user=gj_getuserinfo($this->message['from']);
				
					if(empty($u['from_user'])){
						if(!empty($user['from_user'])){
							pdo_insert('fans', $user);
						}
					}
					else{
						if(!empty($user['from_user'])){
						unset($user['from_user']);	
						pdo_update('fans', $user, array('from_user' =>$this->message['from']));
						}
					}
				//END
				}
				
			
			
		//end
		}
		/*
		else{
			if($this->message['type']!='unsubscribe'){
			$u=pdo_fetch("SELECT id,from_user,nickname,avatar FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND from_user='{$this->message['from']}'  LIMIT 1");
			$user=gj_getuserinfo($this->message['from']);
			if(empty($u['nickname'])||empty($u['avatar'])){
				if(!empty($user['from_user'])){
							pdo_insert('fans', $user);
						}
				}
			
			}
			
		}*/
	}
	
}
