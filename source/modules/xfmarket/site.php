<?php

/**
 */
defined('IN_IA') or exit('Access Denied');
class XfmarketModuleSite extends WeModuleSite {
	public $goods= 'xfmarket_goods';

	public function getHomeTiles() {
		global $_W;
		$urls= array();
		$list= pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'xfmarket'");
		if(!empty($list)) {
			foreach($list as $row) {
				$urls[]= array('title' => $row['name'], 'url' => $this->createMobileUrl('list', array('rid' => $row['id'])));
			}
		}
		return $urls;
	}
	
	
	public function __mobile($f_name){
		global $_W,$_GPC;
		$weid = $_W['weid']; 
		include_once  'mobile/'.strtolower(substr($f_name,8)).'.php';
	}
	  
	public function doMobileAdd(){
		$this->__mobile(__FUNCTION__);
	}

	//列表
	public function doMobileList() { 
		$this->__mobile(__FUNCTION__); 
	}
	//详情
	public function doMobileDetail() {
		$this->__mobile(__FUNCTION__);
	}
	public function doMobileMygoods() {
		$this->__mobile(__FUNCTION__);
	}
	
	  
	//后台管理
	public function doWebGoods() {
		global $_GPC, $_W;
		$item= pdo_fetchall("SELECT * FROM".tablename($this->goods)."WHERE weid='{$_W['weid']}'");
		$goods= array();
		foreach($item as $key => $value) {
			$category= pdo_fetch("SELECT * FROM".tablename('xfmarket_category')."WHERE id='{$value['pcate']}'");
			$goods[]= array('id' => $value['id'], 'title' => $vlaue['title'], 'rolex' => $value['rolex'], 'price' => $value['price'], 'realname' => $value['realname'], 'sex' => $value['sex'], 'mobile' => $value['mobile'], 'name' => $category['name'], 'createtime' => $value['createtime'], 'status' => $value['status'], 'weid' => $value['weid'],);
		}
		if($_GPC['foo'] == 'delete') {
			pdo_delete($this->goods, array('id' => $_GPC['id']));
			message('删除成功', referer(), 'success');
		}
		if($_GPC['foo'] == 'update') {
			//echo $_GPC['id'].$_GPC['status'];exit;
			pdo_query("UPDATE ".tablename('xfmarket_goods')." SET status='{$_GPC['status']}' WHERE id='{$_GPC['id']}'");
			message('更新成功', referer(), 'success');
		}
		include $this->template('goods');
	}
	//分类
	public function doWebCategory() {
		global $_GPC, $_W;
		$op= !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$id= intval($_GPC['id']);
		if($op == 'post') {
			if(!empty($id)) {
				$item= pdo_fetch("SELECT * FROM".tablename('xfmarket_category')."WHERE id='{$id}'");
			}
			if($_W['ispost']) {
				$data= array('weid' => $_W['weid'], 'name' => $_GPC['cname'], 'enabled' => $_GPC['enabled'],);
				if(empty($id)) {
					pdo_insert('xfmarket_category', $data);
				} else {
					 
					pdo_update('xfmarket_category', $data, array('id' => $id));
				}
				message('更新成功', referer(), 'success');
			}
		}elseif($op == 'display') {
			$row= pdo_fetchall("SELECT * FROM".tablename('xfmarket_category')."WHERE weid='{$_W['weid']}'");
		}
		if(checksubmit('delete')) {
			pdo_delete('xfmarket_category', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功', referer(), 'success');
		}
		include $this->template('category');
	}
	
	private function checkAuth() {
		global $_W;
		checkauth();
	}
}