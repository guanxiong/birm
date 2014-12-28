<?php
/*
 *  凑一对模块 
 *
 *  [艮随] Copyright (c)
 */
defined('IN_IA') or exit('Access Denied');

class GrabseatModuleSite extends WeModuleSite {	

	public function getProfileTiles() {
		
	}
	
	public function getHomeTiles($keyword = '') {
	
		$urls = array();
		
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'greetingcard'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		
		if (!empty($list)) {
		
			foreach ($list as $row) {
			
				
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('bless', array('id' => $row['id'])));
			
			}
		}
		
		return $urls;
	
	}
	
	public function doWebList(){
	
		global $_GPC, $_W;
		
		checklogin();
		
		$weid = $_W['account']['weid'];//当前公众号ID
		
		$id = intval($_GPC['id']);
		
		$condition = '';
		
		if (!empty($_GPC['name'])) {
		
			$condition .= " AND ( grabername LIKE '%{$_GPC['realname']}%' OR fitername LIKE '%{$_GPC['realname']}%' )";
		
		}
		if (!empty($_GPC['mobile'])) {
		
			$condition .= " AND ( grabermobile = '{$_GPC['mobile']}' OR fitermobile = '{$_GPC['mobile']}' )";
		
		}
		
		if (checksubmit('delete')) {
		
			
			pdo_delete('grabseat_record', " id IN ('".implode("','", $_GPC['select'])."')");
			
			message('删除成功！', $this->createWebUrl('list', array('id' => $id, 'page' => $_GPC['page'])));
		
		}
		
		if (!empty($_GPC['wid'])) {

			$wid = intval($_GPC['wid']);

			pdo_update('grabseat_record', array('status' => intval($_GPC['status'])), array('id' => $wid));

			message('标识领奖成功！', $this->createWebUrl('list', array('id' => $id, 'page' => $_GPC['page'])));

		}
		
		$pindex = max(1, intval($_GPC['page']));
		
		$psize = 25;

		$list = pdo_fetchall("SELECT * FROM ".tablename('grabseat_record')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize );
		
		$listtotal = pdo_fetchall("SELECT * FROM ".tablename('grabseat_record')." WHERE weid = '{$_W['weid']}' $condition ");
		
		$total = count($listtotal);
		
		$pager = pagination($total, $pindex, $psize);
		
		include $this->template('list');
	
	}
	
	public function doMobileGrabseat() {

		global $_GPC,$_W;

		$rid = $_GPC['rid'];
		
		$weid = $_W['weid'];
		
		$sql = "SELECT * FROM " . tablename('grabseat_reply') . " WHERE `rid`=:rid";

		$row = pdo_fetch($sql, array(':rid'=>$rid));

		$row['picture'] = $_W['attachurl'] . trim($row['picture'], '/');

 		$fromuser = $_W['fans']['from_user'];
		
		$profile = fans_search($fromuser);

		if (!empty($_GPC['submit'])) {
		
			if($_GPC['sel'] == 'grab'){
			
				if (empty($profile['from_user'])) {

					message('您还没有关注，请先关注再参与活动~', 'refresh', 'error');

				}
			
				$graber = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND (graberopenid = :graberopenid OR fiteropenid = :fiteropenid) ", array(':rid' => $rid,':graberopenid' => $fromuser,':fiteropenid' => $fromuser ));
			
				if(empty($graber)){
				
					$data = array(

						'realname' => $_GPC['realname'],
					
						'mobile' => $_GPC['mobile'],
						
						'gender' => $_GPC['gender'],

					);

					fans_update($fromuser, $data);
						
					$insert = array(
						
						'weid' => $_GPC['weid'],
						
						'rid' => $rid,
						
						'graberopenid' => $fromuser,
						
						'grabername' => $_GPC['realname'],
						
						'grabermobile' => $_GPC['mobile'],
						
						'grabtime' => time(),
						
						'status' => 0,
						
					);
					
					if(pdo_insert('grabseat_record', $insert)){
					
						$id=pdo_insertid();
					
					}
					
					die('<script>location.href = "'.$this->createMobileUrl('showseat', array('rid' => $_GPC['rid'], 'id' => $id)).'";</script>');
					
				}
				else{

					message('不能重复抢座或者您已经就坐~~', 'refresh', 'error');
				
				}

			}
			else{
			
				if (empty($profile['from_user'])) {

					message('您还没有关注，请先关注再参与活动~', 'refresh', 'error');

				}
			
				$fiter = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND (graberopenid = :graberopenid OR fiteropenid = :fiteropenid) ", array(':rid' => $rid,':graberopenid' => $fromuser,':fiteropenid' => $fromuser ));
			
				if(empty($fiter)){
				
					$id = $_GPC['tablenum'];
					
					if (empty($id)) {

						message('座位号忘了填写哟~~', 'refresh', 'error');

					}
					
					$tableid = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND id = :id", array(':rid' => $rid, ':id' => $id ));
					
					if (empty($tableid['id'])) {

						message('没有这个座位号哦~~', 'refresh', 'error');

					}
					
					if (!empty($tableid['fiteropenid'])) {

						message('您来晚了，这个座位已经有人做了哟~~', 'refresh', 'error');

					}
				
					$data = array(

						'realname' => $_GPC['realname'],
					
						'mobile' => $_GPC['mobile'],
						
						'gender' => $_GPC['gender'],

					);

					fans_update($fromuser, $data);
						
					$insert = array(
						
						'fiteropenid' => $fromuser,
						
						'fitername' => $_GPC['realname'],
						
						'fitermobile' => $_GPC['mobile'],
						
						'fittime' => time(),
						
						'status' => 1,
						
					);
					
					pdo_update('grabseat_record', $insert, array('id' => $id));
					
					die('<script>location.href = "'.$this->createMobileUrl('fitseat', array('rid' => $_GPC['rid'], 'id' => $id)).'";</script>');	

				}
				else{

					message('您已经就坐，不能换座位了哟~~', 'refresh', 'error');
				
				}
				
			}	
	
		}
		
		include $this->template('grabseat');
    
	}


	public function doMobileShowseat() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$profile = fans_search($fromuser);
		
		$seatnum = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND graberopenid = :from_user", array(':rid' => $rid, ':from_user' => $fromuser ));
		
		$graber = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE id = :id ", array(':id' => $id ));

		$showseaturl = $_W['siteroot'].$this->createMobileUrl('showseat1', array('rid' => $rid,'id' => $id, 'back' => 1));
		
		if (!empty($rid)) {
		
			$reply = pdo_fetch("SELECT * FROM ".tablename('grabseat_reply')." WHERE rid = :rid ", array(':rid' => $rid));			
 		
		}
		
		$Picurl = $_W['attachurl'].$reply['picture'];
		
		include $this->template('showseat');

	}
	
	public function doMobileShowseat1() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$profile = fans_search($fromuser);
		
		$seatnum = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE rid = :rid AND graberopenid = :from_user", array(':rid' => $rid, ':from_user' => $fromuser ));
		
		$graber = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE id = :id ", array(':id' => $id ));

		$showseaturl = $_W['siteroot'].$this->createMobileUrl('showseat1', array('rid' => $rid,'id' => $id, 'back' => 1));
		
		if (!empty($rid)) {
		
			$reply = pdo_fetch("SELECT * FROM ".tablename('grabseat_reply')." WHERE rid = :rid ", array(':rid' => $rid));			
 		
		}
		
		$Picurl = $_W['attachurl'].$reply['picture'];
		
		include $this->template('showseat1');

	}
	
	public function doMobileFitseat() {
	
		global $_GPC, $_W;
		
		$weid = $_W['account']['weid'];
		
		$rid = intval($_GPC['rid']);
		
		$id = intval($_GPC['id']);
		
		$fromuser = $_W['fans']['from_user'];
		
		$fiter = pdo_fetch("SELECT * FROM ".tablename('grabseat_record')." WHERE id = :id ", array(':id' => $id ));

		$fitseaturl = $_W['siteroot'].$this->createMobileUrl('fitseat', array('rid' => $rid,'id' => $id, 'back' => 1));
		
		if (!empty($rid)) {
		
			$reply = pdo_fetch("SELECT * FROM ".tablename('grabseat_reply')." WHERE rid = :rid ", array(':rid' => $rid));			
 		
		}
		
		$Picurl = $_W['attachurl'].$reply['picture'];
		
		include $this->template('fitseat');

	}
	
}