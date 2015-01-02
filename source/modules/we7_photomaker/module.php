<?php
/**
 * 微新星微拍模块定义
 *
 * @author 微新星团队
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class We7_photomakerModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		global $_W, $_GPC;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('we7_photomaker') . " WHERE rid = :rid", array(':rid' => $rid));
			$reply['adpics'] = iunserializer($reply['adpics']);
		} else {
			$reply = array(
				'token' => random(6, 1),
				'maxuse' => 1,
				'status' => 1,
				'enablemsg' => 1,
				'enableauthcode' => 1,
				'size' => '5',
				'maxtotal' => '-1',
				'adtype' => 1,
			);
		}
		include $this->template('rule');
	}

	public function fieldsFormValidate($rid = 0) {
		return '';
	}

	public function fieldsFormSubmit($rid) {
		global $_W, $_GPC;
		$id = intval($_GPC['replyid']);
		$data = array(
			'rid' => $rid,
			'weid' => $_W['weid'],
			'sn' => $_GPC['sn'],
			'token' => $_GPC['apitoken'],
			'maxuse' => intval($_GPC['maxuse']),
			'maxtotal' => intval($_GPC['maxtotal']),
			'adtype' => intval($_GPC['adtype']),
			'adurlv' => $_GPC['adurlv'],
			'adurlh' => $_GPC['adurlh'],
			'admsg' => $_GPC['admsg'],
			'leavemsg' => $_GPC['leavemsg'],
			'fontfamily' => $_GPC['fontfamily'],
			'fontcolor' => $_GPC['fontcolor'],
			'qrcode' => $_GPC['qrcode'],
			'mainpic' => $_GPC['mainpic'],
			'adpics' => '',
			'width' => $_GPC['width'],
			'height' => $_GPC['height'],
			'size' => intval($_GPC['photosize']),
			'status' => intval($_GPC['status']),
			'enableauthcode' => intval($_GPC['enableauthcode']),
			'enablemsg' => intval($_GPC['enablemsg']),
			'content' => $_GPC['content'],
			'tipsentry' => $_GPC['tipsentry'],
			'tipssuccess' => $_GPC['tipssuccess'],
			'tipserror' => $_GPC['tipserror'],
		);
		if (!empty($_GPC['sort'])) {
			asort($_GPC['sort']);
			foreach ($_GPC['sort'] as $key => $value) {
				$data['adpics'][] = $_GPC['adpics'][$key];
			}
			$data['adpics'] = iserializer($data['adpics']);
		} else {
			$data['adpics'] = iserializer($_GPC['adpics']);
		}
		if (empty($id)) {
			pdo_insert('we7_photomaker', $data);
		} else {
			if (!empty($_GPC['qrcode'])) {
				file_delete($_GPC['qrcode-old']);
			}
			if (!empty($_GPC['mainpic'])) {
				file_delete($_GPC['mainpic-old']);
			}
			pdo_update('we7_photomaker', $data, array('id' => $id));
		}
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}
}