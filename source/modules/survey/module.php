<?php
/**
 * 调研模块定义
 *
 * @author 更多模块请浏览bbs.birm.co
 * @url http://bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class SurveyModule extends WeModule {
	//用
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('survey_reply') . " WHERE rid = :rid", array(':rid' => $rid));
			$sql = 'SELECT * FROM ' . tablename('survey') . ' WHERE `weid`=:weid AND `sid`=:sid';
			$activity = pdo_fetch($sql, array(':weid' => $_W['weid'], ':sid' => $reply['sid']));
		}
		include $this->template('form');
	}
	//用
	public function fieldsFormValidate($rid = 0) {
		global $_GPC;
		$sid = intval($_GPC['activity']);
		if($sid) {
			$sql = 'SELECT * FROM ' . tablename('survey') . " WHERE `sid`=:sid";
			$params = array();
			$params[':sid'] =$sid;
			$activity = pdo_fetch($sql, $params);
			if(!empty($activity)) {
				return '';
			}
		}
		return '没有选择合适的调研活动';
	}
	//用
	public function fieldsFormSubmit($rid) {
		global $_GPC;
		$sid = intval($_GPC['activity']);
		$record = array();
		$record['sid'] = $sid;
		$record['rid'] = $rid;
		$reply = pdo_fetch("SELECT * FROM " . tablename('survey_reply') . " WHERE rid = :rid", array(':rid' => $rid));
		if($reply) {
			pdo_update('survey_reply', $record, array('id' => $reply['id']));
		} else {
			pdo_insert('survey_reply', $record);
		}
	}
	//用
	public function ruleDeleted($rid) {
		pdo_delete('survey_reply', array('rid' => $rid));
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
