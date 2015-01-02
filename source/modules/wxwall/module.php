<?php
/**
 * 微信墙模块
 *
 * [WeEngine System] 更多模块请浏览：bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class WxwallModule extends WeModule {
	public $tablename = 'wxwall_reply';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$reply['syncwall'] = unserialize($reply['syncwall']);
		} else {
			$reply = array(
				'isshow' => 0,
				'timeout' => 0,
			);
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
			'enter_tips' => $_GPC['enter-tips'],
			'quit_tips' => $_GPC['quit-tips'],
			'send_tips' => $_GPC['send-tips'],
			'timeout' => $_GPC['timeout'],
			'isshow' => intval($_GPC['isshow']),
			'quit_command' => $_GPC['quit-command'],
			'logo' => $_GPC['logo'],
			'background' => $_GPC['background'],
			'syncwall' => array(
				'tx' => array(
					'status' => intval($_GPC['walls']['tx']['status']),
					'subject' => $_GPC['walls']['tx']['subject'],
				),
			),
		);
		
		$insert['syncwall'] = serialize($insert['syncwall']);
		if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
	}

	public function ruleDeleted($rid = 0) {

	}

}
