<?php
/**
 * 猜拳模块
 *
 * [WDL]更多模块请浏览：BBS.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class MoraModuleSite extends WeModuleSite {	
	
	public $table_reply = 'mora_reply';
	public $table_list   = 'mora_list';

	public function getProfileTiles() {
		
	}
	
	public function getHomeTiles($keyword = '') {
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE module = 'mora'".(!empty($keyword) ? " AND name LIKE '%{$keyword}%'" : ''));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('mora', array('id' => $row['id'])));
			}
		}
		return $urls;
	}
	
	public function doWebMoralist($rid, $state) {		
		global $_GPC, $_W;
		checklogin();
		$weid = $_W['account']['weid'];//当前公众号ID
		$id = intval($_GPC['id']);
		
		include $this->template('moralist');

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
}