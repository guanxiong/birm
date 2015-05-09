<?php
/**
 * 微信墙模块
 *
 * [WeEngine System] 更多模块请浏览：BBS.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class WxwallModuleSite extends WeModuleSite {

	public function doWebDetail() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$wall = $this->getWall($id);
		$wall['onlinemember'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('wxwall_members')." WHERE rid = '{$wall['rid']}'");
		$list = pdo_fetchall("SELECT id, content, from_user, type, createtime FROM ".tablename('wxwall_message')." WHERE rid = '{$wall['rid']}' AND isshow = '2' AND from_user <> '' ORDER BY createtime DESC");
		$this->formatMsg($list);
		include $this->template('detail');
	}

	/*
	 * 内容管理
	 */
	public function doWebManage() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('verify') && !empty($_GPC['select'])) {
			pdo_update('wxwall_message', array('isshow' => 1, 'createtime' => TIMESTAMP), " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('审核成功！', $this->createWebUrl('manage', array('id' => $id, 'page' => $_GPC['page'])));
		}
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('wxwall_message', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('manage', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
		$condition = '';
		if($isshow == 0) {
			$condition .= 'AND isshow = '.$isshow;
		} else {
			$condition .= 'AND isshow > 0';
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$wall = pdo_fetch("SELECT id, isshow, rid FROM ".tablename('wxwall_reply')." WHERE rid = '{$id}' LIMIT 1");
		$list = pdo_fetchall("SELECT * FROM ".tablename('wxwall_message')." WHERE rid = '{$wall['rid']}' {$condition} ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('wxwall_message') . " WHERE rid = '{$wall['rid']}' {$condition}");
			$pager = pagination($total, $pindex, $psize);

			foreach ($list as &$row) {
				if ($row['type'] == 'link') {
					$row['content'] = iunserializer($row['content']);
					$row['content'] = '<a href="'.$row['content']['link'].'" target="_blank" title="'.$row['content']['description'].'">'.$row['content']['title'].'</a>';
				} elseif ($row['type'] == 'image') {
					$row['content'] = '<img src="'. $row['content'].'" />';
				} else {
					$row['content'] = emotion($row['content']);
				}
				$userids[] = $row['from_user'];
			}
			unset($row);

			if (!empty($userids)) {
				$member = fans_search($userids, array('nickname', 'avatar'));
				$blacklist = pdo_fetchall("SELECT from_user, isblacklist FROM ".tablename('wxwall_members')." WHERE from_user IN ('".implode("','", $userids)."')", array(), 'from_user');
				foreach ($member as &$row) {
					$row['isblacklist'] = $blacklist[$row['from_user']]['isblacklist'];
				}
				unset($row);
			}
		}
		include $this->template('manage');
	}

	/*
	 * 增量数据调用
	 */
	public function doWebIncoming() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$lastmsgtime = intval($_GPC['lastmsgtime']);
		$sql = "SELECT id, content, from_user, type, createtime FROM ".tablename('wxwall_message')." WHERE rid = '{$id}'";
		$page = max(1, intval($_GPC['page']));
		if (!empty($lastmsgtime)) {
			$sql .= " AND createtime >= '$lastmsgtime' AND isshow > 0 ORDER BY id ASC LIMIT ".($page-1).", 1";
		} else {
			$sql .= " AND isshow = '1' ORDER BY createtime ASC  LIMIT 1";
		}
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$this->formatMsg($list);
			$row = $list[0];
			pdo_update('wxwall_message', array('isshow' => '2'), array('id' => $row['id']));
			$row['content'] = emotion($row['content'], '48px');
			message($row, '', 'ajax');
		}
	}

	/*
	 * 登记
	 */
	public function doWebRegister() {
		global $_GPC, $_W;
		$title = '微信墙登记';
		$member = fans_search($_GPC['from'], array('nickname', 'avatar'));
		if (!empty($_GPC['submit'])) {
			$data = array(
				'weid' => $_W['weid'],
				'nickname' => $_GPC['nickname'],
			);
			if (empty($data['nickname'])) {
				die('<script>alert("请填写您的昵称！");location.reload();</script>');
			}
			$data['avatar'] = $_GPC['avatar_radio'];
			if (!empty($_FILES['avatar']['tmp_name'])) {
				$_W['uploadsetting'] = array();
				$_W['uploadsetting']['wxwall']['folder'] = 'wxwall/avatar';
				$_W['uploadsetting']['wxwall']['extentions'] = $_W['config']['upload']['image']['extentions'];
				$_W['uploadsetting']['wxwall']['limit'] = $_W['config']['upload']['image']['limit'];
				$upload = file_upload($_FILES['avatar'], 'wxwall', $_GPC['from']);
				if (is_error($upload)) {
					die('<script>alert("登记失败！请重试！");location.reload();</script>');
				}
				$data['avatar'] = $upload['path'];
			}
			fans_update($_GPC['from'], $data);
			die('<script>alert("登记成功！现在进入话题发表内容！");location.href = "' . $this->createWebUrl('register', array('from' => $_GPC['from'])).'";</script>');

		}
		include $this->template('register');
	}

	public function doWebBlacklist() {
		global $_W, $_GPC;
		if (checksubmit('delete')) {
			pdo_update('wxwall_members', array('isblacklist' => 0), " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('解除黑名单成功！', $this->createWebUrl('blacklist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$id = intval($_GPC['id']);
		if (!empty($_GPC['from_user'])) {
			pdo_update('wxwall_members', array('isblacklist' => intval($_GPC['switch'])), array('from_user' => $_GPC['from_user']));
			message('黑名单操作成功！', $this->createWebUrl('manage', array('id' => $id)));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT from_user, lastupdate FROM ".tablename('wxwall_members')." WHERE isblacklist = '1' ORDER BY lastupdate DESC LIMIT ".($pindex - 1) * $psize.",{$psize}", array(), 'from_user');
		$member = fans_search(array_keys($list), array('nickname', 'avatar'));
		include $this->template('blacklist');
	}

	public function doWebQrcode() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$wall = $this->getWall($id);
		include $this->template('qrcode');
	}

	public function doWebLottery() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$type = intval($_GPC['type']);
		$wall = $this->getWall($id);
		if ($type == 1) {
			$list = pdo_fetchall("SELECT id, content, from_user, type, createtime FROM ".tablename('wxwall_message')." WHERE rid = '{$wall['rid']}' AND isshow = '2' AND from_user <> '' ORDER BY createtime DESC");
		} else {
			$list = pdo_fetchall("SELECT id, content, from_user, type, createtime FROM ".tablename('wxwall_message')." WHERE rid = '{$wall['rid']}' AND isshow = '2' AND from_user <> '' GROUP BY from_user ORDER BY createtime DESC LIMIT 10");
		}
		$this->formatMsg($list);
		include $this->template('lottery');
	}

	public function doWebAward() {
		global $_GPC, $_W;
		checklogin();
		$message = pdo_fetch("SELECT * FROM ".tablename('wxwall_message')." WHERE id = '{$_GPC['mid']}' LIMIT 1");
		if (empty($message)) {
			message('抱歉，参数不正确！', '', 'error');
		}
		$data = array(
			'rid' => $message['rid'],
			'from_user' => $message['from_user'],
			'createtime' => TIMESTAMP,
			'status' => 0,
		);
		pdo_insert('wxwall_award', $data);
		message('', '', 'success');
	}

	public function doWebAwardlist() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete('wxwall_award', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($_GPC['wid'])) {
			$wid = intval($_GPC['wid']);
			pdo_update('wxwall_award', array('status' => intval($_GPC['status'])), array('id' => $wid));
			message('标识领奖成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$sql = "SELECT * FROM ".tablename('wxwall_award')." WHERE rid = '$id' ORDER BY status ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('wxwall_award')." WHERE rid = '$id'");
			$pager = pagination($total, $pindex, $psize);
			foreach ($list as $index => $row) {
				$users[$row['from_user']] = $row['from_user'];
			}
			$users = fans_search($users, array('nickname', 'avatar'));
		}
		include $this->template('awardlist');
	}

	private function getWall($id) {
		$wall = pdo_fetch("SELECT id, isshow, rid, syncwall, logo, background FROM ".tablename('wxwall_reply')." WHERE rid = '{$id}' LIMIT 1");
		$wall['syncwall'] = unserialize($wall['syncwall']);
		$wall['rule'] = pdo_fetch("SELECT name, weid FROM ".tablename('rule')." WHERE id = '{$id}' LIMIT 1");
		$wall['account'] = pdo_fetch("SELECT account, name FROM ".tablename('wechats')." WHERE weid = '{$wall['rule']['weid']}' LIMIT 1");
		$wall['keyword'] = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$id}'");
		return $wall;
	}

	private function formatMsg(&$list) {
		global $_W;
		if (empty($list)) {
			return false;
		}
		$uids = $members = array();
		foreach ($list as &$row) {
			$uids[$row['from_user']] = $row['from_user'];
			if ($row['type'] == 'link') {
				$row['content'] = iunserializer($row['content']);
				$row['content'] = '<a href="'.$row['content']['link'].'" target="_blank" title="'.$row['content']['description'].'">'.$row['content']['title'].'</a>';
			} elseif ($row['type'] == 'image') {
				$row['content'] = '<img src="'.$row['content'].'" />';
			} elseif ($row['type'] == 'txwall') {
				$content = unserialize($row['content']);
				$row['content'] = $content['content'];
				$row['avatar'] = $content['avatar'];
				$row['nickname'] = $content['nickname'];
			}
			$row['content'] = emotion($row['content'], '48px');
		}
		unset($row);
		if (!empty($uids)) {
			$members = fans_search($uids, array('nickname', 'avatar'));
		}
		if (!empty($members)) {
			foreach ($list as $index => &$row) {
				if ($row['type'] == 'txwall') {
					continue;
				}
				$row['nickname'] = $members[$row['from_user']]['nickname'];
				$row['avatar'] = $members[$row['from_user']]['avatar'];
			}
			unset($row);
		}
	}

	public function doWebIncomingTxWall() {
		global $_W, $_GPC;
		$result = array('status' => 0);
		$id = intval($_GPC['id']);
		$lastmsgtime = intval($_GPC['lastmsgtime']);
		$lastuser = '';

		$wall = pdo_fetchcolumn("SELECT syncwall FROM ".tablename('wxwall_reply')." WHERE rid = '{$id}' LIMIT 1");
		if (empty($wall)) {
			message($result, '', 'ajax');
		}
		$wall = unserialize($wall);
		if (empty($wall['tx']['status'])) {
			message($result, '', 'ajax');
		}
		$response = ihttp_request('http://wall.v.t.qq.com/index.php?c=wall&a=topic&ak=801424380&t='.$wall['tx']['subject'].'&fk=&fn=&rnd='.TIMESTAMP);
		if (empty($response['content'])) {
			$result['status'] = -1;
			message($result, '', 'ajax');
		}
		$last = pdo_fetch("SELECT createtime, from_user FROM ".tablename('wxwall_message')." WHERE createtime >= '$lastmsgtime' AND type = 'txwall' AND rid = $id ORDER BY createtime DESC LIMIT 1");
		if (!empty($last)) {
			$lastmsgtime = $last['createtime'];
			$lastuser = $last['from_user'];
		}
		$list = json_decode($response['content'], true);
		if (!empty($list['data']['info'])) {
			foreach ($list['data']['info'] as $row) {
				if ($row['timestamp'] < $lastmsgtime || ($lastmsgtime == $row['timestamp'] && !empty($lastuser) && $lastuser == $row['name'])) {
					break;
				}
				$content = array('nickname' => $row['nick'], 'avatar' => !empty($row['head']) ? $row['head'] . '/120' : '', 'content' => $row['text']);
				$insert[] = array(
					'rid' => $id,
					'content' => serialize($content),
					'from_user' => $row['name'],
					'type' => 'txwall',
					'isshow' => 1,
					'createtime' => $row['timestamp'],
				);
			}
			unset($row);
			$insert = array_reverse($insert);
			foreach ($insert as $row) {
				pdo_insert('wxwall_message', $row);
			}
			$lastmsgtime = $row['timestamp'];
			$result = array(
				'status' => 1,
				'lastmsgtime' => $lastmsgtime,
			);
			message($result, '', 'ajax');
		} else {
			message($result, '', 'ajax');
		}
	}

	public function doMobileRegister() {
		global $_GPC, $_W;
		$title = '微信墙登记';
		$member = fans_search($_W['fans']['from_user'], array('nickname', 'avatar'));
		if (!empty($_GPC['submit'])) {
			$data = array(
				'weid' => $_W['weid'],
				'nickname' => $_GPC['nickname'],
			);
			if (empty($data['nickname'])) {
				die('<script>alert("请填写您的昵称！");location.reload();</script>');
			}

			if (!empty($_FILES['avatar']['tmp_name'])) {
				$data['avatar'] = '';
			} else {
				$data['avatar'] = $_GPC['avatar_radio'];
			}
			fans_update($_W['fans']['from_user'], $data);
			die('<script>alert("登记成功！现在进入话题发表内容！");location.href = "'.$this->createMobileUrl('register').'";</script>');
		}
		include $this->template('register');
	}
}
