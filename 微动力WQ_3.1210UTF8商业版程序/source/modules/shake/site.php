<?php
defined('IN_IA') or exit('Access Denied');

class ShakeModuleSite extends WeModuleSite {
	public function doMobileDetail() {
		global $_W, $_GPC;
		checkauth();
		$rid = intval($_GPC['rid']);
		$reply = pdo_fetch("SELECT * FROM ".tablename('shake_reply')." WHERE rid = :rid", array(':rid' => $rid));
		if (empty($reply)) {
			message('抱歉，此活动不存在或是还未开始！', 'refresh', 'error');
		}
		$profile = fans_search($_W['fans']['from_user'], array('realname', 'mobile'));
		if (empty($profile['realname']) || empty($profile['mobile'])) {
			fans_require($_W['fans']['from_user'], array('realname', 'mobile'));
		}
		$member = pdo_fetch("SELECT * FROM ".tablename('shake_member')." WHERE rid = :rid AND openid = :openid", array(':rid' => $reply['rid'], ':openid' => $_W['fans']['from_user']));
		if (empty($member)) {
			pdo_insert('shake_member', array(
				'rid' => $rid,
				'openid' => $_W['fans']['from_user'],
				'createtime' => TIMESTAMP,
				'shakecount' => 0,
			));
		}
		include $this->template('detail');
	}

	public function doMobileShake() {
		global $_W, $_GPC;
		$rid = $_GPC['rid'];
		$reply = pdo_fetch("SELECT * FROM ".tablename('shake_reply')." WHERE rid = :rid AND status = 1", array(':rid' => $rid));
		if (empty($reply)) {
			message(array('status' => 0), '', 'ajax');
		}

		$maxshakecount = pdo_fetchcolumn("SELECT max(shakecount) FROM ".tablename('shake_member')." WHERE rid =  :rid", array(':rid' => $rid));
		if ($maxshakecount >= $reply['maxshake']) {
			pdo_update('shake_reply', array('status' => 2), array('rid' => $rid));
			message(array('status' => 2), '', 'ajax');
		} else {
			pdo_query("UPDATE ".tablename('shake_member')." SET shakecount = shakecount + 1, lastupdate = '".TIMESTAMP."' WHERE openid = :openid AND rid = :rid", array(':rid' => $rid, ':openid' => $_W['fans']['from_user']));
			$member = pdo_fetch("SELECT * FROM ".tablename('shake_member')." WHERE rid = :rid AND openid = :openid", array(':rid' => $rid, ':openid' => $_W['fans']['from_user']));
			message(array('status' => 1, 'shakecount' => $member['shakecount'], 'lastupdate' => $member['lastupdate']), '', 'ajax');
		}
	}

	public function doWebDetail() {
		global $_W, $_GPC;
		checklogin();
		$id = intval($_GPC['id']);

		$reply = pdo_fetch("SELECT * FROM ".tablename('shake_reply')." WHERE rid = :id", array(':id' => $id));
		if (empty($reply)) {
			message('抱歉，此活动不存在或是还未开始！', 'refresh', 'error');
		}
		$reply['rule'] = htmlspecialchars_decode($reply['rule']);
		$limit = empty($reply['maxwinner']) ? 10 : $reply['maxwinner'];
		$list = pdo_fetchall("SELECT openid, shakecount FROM ".tablename('shake_member')." WHERE rid = :rid ORDER BY shakecount DESC LIMIT $limit", array(':rid' => $reply['rid']), 'openid');
		$fans = fans_search(array_keys($list), array('realname', 'mobile'));

		$reply['keyword'] = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$reply['rid']}'");
		include $this->template('detail');
	}

	public function doWebGetRank() {
		global $_GPC; $_W;
		checklogin();
		$result = array('status' => 0, 'message' => '');
		$id = intval($_GPC['id']);

		$reply = pdo_fetch("SELECT * FROM ".tablename('shake_reply')." WHERE id = :id", array(':id' => $id));
		if (empty($reply['status'])) {
			$result['message'] = '活动还未开始！';
			message($result, $this->createWebUrl('detail', array('id' => $reply['rid'], 'weid' => $_W['weid'], 'status' => 0)), 'ajax');
		}
		if ($reply['status'] == 2) {
			$result['status'] = 2;
			$result['message'] = '活动已经结束！';
			message($result, $this->createWebUrl('detail', array('id' => $reply['rid'], 'weid' => $_W['weid'])), 'ajax');
		}
		$limit = empty($reply['maxwinner']) ? 10 : $reply['maxwinner'];
		$result['message'] = pdo_fetchall("SELECT openid, shakecount FROM ".tablename('shake_member')." WHERE rid = :rid ORDER BY shakecount DESC LIMIT $limit", array(':rid' => $reply['rid']), 'openid');
		$result['status'] = 1;
		if (!empty($result['message'])) {
			$result['fans'] = fans_search(array_keys($result['message']), array('realname', 'mobile'));
		}
		message($result, '', 'ajax');
	}

	public function doWebManage() {
		global $_W, $_GPC;
		checklogin();
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$rid = intval($_GPC['id']);

		if ($operation == 'display') {
			if (!empty($_GPC['realname'])) {
				$openids = pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE realname = :realname", array(':realname' => $_GPC['realname']), 'from_user');
				if (!empty($openids)) {
					$condition = " AND openid IN ('".implode("','", array_keys($openids))."')";
				}
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename('shake_member')." WHERE rid = :rid $condition ORDER BY shakecount DESC LIMIT ".($pindex - 1) * $psize.','.$psize, array(':rid' => $rid), 'openid');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('shake_member') . " WHERE rid = :rid $condition", array(':rid' => $rid));
			$pager = pagination($total, $pindex, $psize);
			$fans = fans_search(array_keys($list), array('realname', 'mobile'));
		} elseif ($operation == 'post') {
			$id = intval($_GPC['id']);
			$item = pdo_fetch("SELECT * FROM ".tablename('shake_member')." WHERE id = '$id'");
			if (checksubmit('submit')) {
				pdo_update('shake_member', array(
					'remark' => $_GPC['remark'],
				), array('id' => $id));
				message('更新信息成功！', $this->createWebUrl('manage', array('id' => $item['rid'])), 'success');
			}
			$item['profile'] = fans_search($item['openid'], array('mobile', 'realname'));
		}

		include $this->template('manage');
	}

	public function doWebAddShakecount() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$shake = pdo_fetch("SELECT shakecount, rid FROM ".tablename('shake_member')." WHERE id = :id", array(':id' => $id));
		$item = pdo_fetch("SELECT maxshake FROM ".tablename('shake_reply')." WHERE rid = '{$shake['rid']}'");

		if ($item['maxshake'] > $shake['shakecount']) {
			pdo_update('shake_member', array('shakecount' => $shake['shakecount'] + 1), array('id' => $id));
		}
		message($shake['shakecount'], '', 'ajax');
	}

	public function doWebChangeStatus() {
		global $_W, $_GPC;
		checklogin();
		$id = intval($_GPC['id']);
		$status = intval($_GPC['status']);

		pdo_update('shake_reply', array('status' => $status), array('rid' => $id));
		message(array('status' => 1), '', 'ajax');
	}
}