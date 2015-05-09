<?php
defined('IN_IA') or exit('Access Denied');

class Bj_tgameModuleSite extends WeModuleSite {
		
    public function doWebAwardlist() {
    			global $_W, $_GPC;
		$rid = $_GPC['id'];
       $pms = pdo_fetchall("select * from ".tablename('bj_tgame_user')." where rid =". $rid." order by score desc limit 10");
		
        include $this->template('awardlist');
    }
	public function doMobileIndex(){
		global $_W, $_GPC;
		checkauth();
		$fromuser = fans_require($_W['fans']['from_user'], array('nickname', 'mobile'), '需要完善资料后才能玩.');
		$rid = $_GPC['rid'];
		$follow = fans_search($_W['fans']['from_user'], array('follow'));
		if($follow['follow'] == 1){
			if(intval($_GPC['id'])){
			$score = pdo_fetchcolumn("select score from".tablename('bj_tgame_user')."where rid =". $rid. ".and from_user ='".$fromuser['from_user']. "'");
			if($score < $_GPC['score']){
				$update = array(
					'score'=>$_GPC['score'],
					'realname'=>$fromuser['nickname']
				);
				$score = $_GPC['score'];
				pdo_update('bj_tgame_user', $update, array('id'=>$_GPC['id'], 'weid'=>$_W['weid']));
			}
			message($score,'','ajax');
			}
			$from_user = pdo_fetch("select id from_user, rid from".tablename('bj_tgame_user')."where from_user ='".$fromuser['from_user']. "'");
			if(empty($from_user['from_user'])){
				$insert = array(
					'id'=>$_GPC['id'],
					'weid'=>$_W['weid'],
					'rid'=>$rid,
					'from_user'=>$fromuser['from_user'],
					'realname'=>$fromuser['nickname'],
					'score'=>0
				);
				pdo_insert('bj_tgame_user', $insert);
			}
			//$from_user = pdo_fetch("select id, rid from".tablename('bj_tgame_user')."where from_user ='".$fromuser['from_user']. "'");
			if($from_user['rid'] != $rid){
				$update = array(
					'rid'=>$rid,
				);
				pdo_update('bj_tgame_user', $update, array('id'=>$from_user['id']));
			}
			$realname = $fromuser['nickname'];
			$user = pdo_fetch("select * from".tablename('bj_tgame_user')."where rid =". $rid. ".and from_user ='".$fromuser['from_user']. "'");
			
		}
		$set = pdo_fetch("select * from".tablename('bj_tgame_reply')."where rid =". $rid);
		include $this->template('index');
	}
	
	public function doMobilePhb(){
		global $_W, $_GPC;
		$rid = $_GPC['rid'];
		
		$set = pdo_fetch("select * from ".tablename('bj_tgame_reply')." where rid =". $rid);
		$users = pdo_fetchall("select * from ".tablename('bj_tgame_user')." where rid =". $rid." order by score desc limit 10");
		
		include $this->template('phb');
	}
		public function doMobileHelp(){

		include $this->template('help');
	}
		public function doMobileRule(){

		include $this->template('rule');
	}
}