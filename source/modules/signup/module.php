<?php
/**
 * 微报名模块定义
 *
 * @美院微生活
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class SignupModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('signup_reply') . " WHERE rid = :rid", array(':rid' => $rid));
			$sql = 'SELECT * FROM ' . tablename('signup') . ' WHERE `weid`=:weid AND `reid`=:reid';
			$activity = pdo_fetch($sql, array(':weid' => $_W['weid'], ':reid' => $reply['reid']));
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		global $_GPC;
		$reid = intval($_GPC['activity']);
		if($reid) {
			$sql = 'SELECT * FROM ' . tablename('signup') . " WHERE `reid`=:reid";
			$params = array();
			$params[':reid'] = $reid;
			$activity = pdo_fetch($sql, $params);
			if(!empty($activity)) {
				return '';
			}
		}
		return '没有选择合适的报名活动';
	}

	public function fieldsFormSubmit($rid) {
		global $_GPC;
		$reid = intval($_GPC['activity']);
		$record = array();
		$record['reid'] = $reid;
		$record['rid'] = $rid;
		$reply = pdo_fetch("SELECT * FROM " . tablename('signup_reply') . " WHERE rid = :rid", array(':rid' => $rid));
		if($reply) {
			pdo_update('signup_reply', $record, array('id' => $reply['id']));
		} else {
			pdo_insert('signup_reply', $record);
		}
	}

	public function ruleDeleted($rid) {
		pdo_delete('signup_reply', array('rid' => $rid));
	}

	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$cfg = array(
				'noticeemail' => $_GPC['noticeemail'],
			);
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}
		include $this->template('setting');
	}
}
