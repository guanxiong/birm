<?php
/**
 * 图片魔方模块定义
 *
 * @author 智策技术
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class izclightboxModule extends WeModule {
	public $table_reply  = 'izclightbox_reply';
	public $table_list  = 'izclightbox_list';
	
	public function fieldsFormDisplay($rid = 0) {
 		global $_W;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid", array(':rid' => $rid));
			$sql = 'SELECT id,reply_title,reply_thumb,reply_description FROM ' . tablename($this->table_list) . ' WHERE `weid`=:weid AND `id`=:list_id';
			$activity = pdo_fetch($sql, array(':weid' => $_W['weid'], ':list_id' => $reply['list_id']));
 		}
 		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		global $_W, $_GPC;
		$list_id= intval($_GPC['activity']);
		if(!empty($list_id)) {
			$sql = 'SELECT * FROM ' . tablename($this->table_list) . " WHERE `id`=:list_id";
			$params = array();
			$params[':list_id'] = $list_id;
			$activity = pdo_fetch($sql, $params);
			return ;
			if(!empty($activity)) {
				return '';
			}
		}
		return '没有选择合适的场景';
	}

	public function fieldsFormSubmit($rid) {
		global $_GPC;
		$list_id = intval($_GPC['activity']);
		$record = array();
		$record['list_id'] = $list_id;
		$record['rid'] = $rid;
		$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid", array(':rid' => $rid));
		if($reply) {
			pdo_update($this->table_reply, $record, array('id' => $reply['id']));
		} else {
			pdo_insert($this->table_reply, $record);
		}
	}

	public function ruleDeleted($rid) {
		pdo_delete($this->table_reply, array('rid' => $rid));
	}
}