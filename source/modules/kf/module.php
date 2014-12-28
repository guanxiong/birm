<?php
/**
 * 客服模块
 *
 * [19.3cm qq81324093] Copyright (c) 2013 wangxinglin.com
 */
defined('IN_IA') or exit('Access Denied');

class kfModule extends WeModule {
	public $name = 'kfModule';
	public $title = '客服交流';
	public $ability = '';
	public $tablename = 'kf';
	
	
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
			//查询微CMS模块分类
			//$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('article_category')." WHERE weid = '{$_W['weid']}' AND parentid=0 ORDER BY parentid ASC");
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			
			
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
			'cateid'=>$_GPC['cate_1'],
			'picture' => $_GPC['picture'],
			'description' => $_GPC['description'],
			'default_tips' => $_GPC['default_tips'],
			'send_tips' =>$_GPC['send_tips'],
			'wechattype' =>$_GPC['wechattype'],
			'timeout' => $_GPC['timeout'],
		);
		if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
		
		
		//模型参数设置完成
		
	}

	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id, thumb FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}
	
	
	
}