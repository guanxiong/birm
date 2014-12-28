<?php
/**
 * 砸蛋抽奖模块
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class ShareModuleSite extends WeModuleSite {	
	
	public $table_reply = 'share_reply';
	public $table_list   = 'share_list';	
	public $table_data   = 'share_data';

	public function getProfileTiles() {
		
	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'share'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('share', array('id' => $row['id'])));
			}
		}
		return $urls;
	}
	
	public function doMobileShare() {
		//分享达人分享页面显示。
		global $_GPC,$_W;

		if (empty($_GPC['rid'])) {
		$rid = $_GPC['id'];
		}else{
		$rid = $_GPC['rid'];
		}
      	if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));			
 		}

		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		$shareip = getip();
		$now = time();
		//取得分享达人数据
		if(!empty($fromuser)) {
		$list = pdo_fetch("SELECT * FROM ".tablename($this->table_list)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' limit 1" );
		
		if(!empty($list)){
			$shareid = $list['id'];
			//取得分享详细数据，判断浏览者是否是同一人24小时内同一IP访问
			$share_data = pdo_fetch("SELECT * FROM ".tablename($this->table_data)." WHERE from_user = '".$fromuser."' and rid = '".$rid."' and shareip= '".$shareip."' limit 1" );
			if(!empty($share_data)){
				$sid		=	$share_data['id'];
				$sharetime	=	$share_data['sharetime'];
				$updatetime	=	$now-$sharetime;
				//访问如果是在24小时后，更新分享数据，更新分享数
				if($updatetime > 24*3600){
					$updatedata = array(
						'sharetime' => $now
						);	
					$updatelist = array(
						'sharenum' => $list['sharenum']+1,
						'sharetime' => $now
						);	
					pdo_update($this->table_data,$updatedata,array('id' => $sid));
					pdo_update($this->table_list,$updatelist,array('id' => $shareid));
				}
			}else{
					$insertdata = array(
						'rid' => $rid,
						'from_user' => $fromuser,
						'shareip'	=> $shareip,
						'sharetime' => $now
						);	
					$updatelist = array(
						'sharenum' => $list['sharenum']+1,
						'sharetime' => $now
						);	
					pdo_insert($this->table_data, $insertdata);
					pdo_update($this->table_list,$updatelist,array('id' => $shareid));
			}
		}else{
			$insertdata = array(
				'rid' => $rid,
				'from_user' => $fromuser,
				'shareip'	=> $shareip,
				'sharetime' => $now
			);	
     		$insertlist = array(
				'rid' => $rid,			
				'from_user' => $fromuser,
				'sharenum' => '1',	
				'sharetime'=>$now			
			);
			pdo_insert($this->table_data, $insertdata);
			pdo_insert($this->table_list, $insertlist);

		}   
		}
		//整理数据进行页面显示
		$imgurl=$_W['attachurl'] . $reply['picture'];
      	$title = $reply['title'];
		$loclurl=$_W['siteroot'].$this->createMobileUrl('share', array('rid' => $rid, 'from_user' => $_GPC['from_user']));		
   		
		if ($reply['status']) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
				if (strpos($user_agent, 'MicroMessenger') === false) {
					echo "本页面仅支持微信访问!非微信浏览器禁止浏览!";
				} else { 
					include $this->template('share');
				}
		} else {
			echo '<h1>分享达人活动已结束!</h1>';
			exit;			
		}
	}
	public function doWebSharelist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['account']['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_list, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('index/module', array('do' => 'sharelist', 'name' => 'share', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'share\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享达人列表
		$list_share = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE rid= :rid order by `sharenum` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $id) );	
		$listtotal = pdo_fetchall('SELECT * FROM '.tablename($this->table_list).' WHERE rid= :rid order by `id` desc ', array(':rid' => $id) );
		$total = count($listtotal);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('sharelist');

	}
	public function doWebstatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_reply,$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
	public function doWebdos( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		echo $id;
		$insert = array(
			'status' => $_GPC['status']
		);
		
		pdo_update($this->table_list,$insert,array('id' => $id,'rid' => $rid));
		message('屏蔽操作成功！', create_url('site/module/sharelist', array('name' => 'share', 'id' => $rid, 'page' => $_GPC['page'])));
	}
	public function doWebSharedata($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['account']['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete($this->table_data, " id IN ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'share', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$rules = pdo_fetchall('SELECT `id`,`name` FROM '.tablename('rule').' WHERE `module`=\'share\'');
		$pindex = max(1, intval($_GPC['page']));
		$psize = 15;

		//取得分享点击详细数据
		$list_sharedata = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE rid= :rid order by `sharetime` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':rid' => $id) );	
		$listtotal = pdo_fetchall('SELECT * FROM '.tablename($this->table_data).' WHERE rid= :rid order by `sharetime` desc ', array(':rid' => $id) );
		$total = count($listtotal);
		$pager = pagination($total, $pindex, $psize);
		include $this->template('sharedata');

	}
	public function doWebdeldata( $id = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$id = $_GPC['id'];
		if (!empty($id)) {
			pdo_delete($this->table_data, " id = ".$id);
			message('删除成功！', create_url('site/module/sharedata', array('name' => 'share', 'id' => $rid, 'page' => $_GPC['page'])));
		}		
		
	}
}