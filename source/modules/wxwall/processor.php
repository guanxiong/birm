<?php
/**
 * 图文回复处理类
 *
 * [WeEngine System] 更多模块请浏览：BBS.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class WxwallModuleProcessor extends WeModuleProcessor {
	public $name = 'WxwallChatRobotModuleProcessor';
	
	public function respond() {
		if ($this->inContext) {
			return $this->post();
		} else {
			return $this->register();
		}
	}
	
	private function register() {
		global $_W;
		$rid = $this->rule;
		$wall = pdo_fetch("SELECT * FROM ".tablename('wxwall_reply')." WHERE rid = '{$rid}' LIMIT 1");
		if (empty($wall)) {
			return array();
		}
		$member = $this->getMember();

		$message = $wall['enter_tips'];
		if (empty($member['nickname'])) {
			$message .= ' - 发表话题前请<a target="_blank" href="'.$this->createMobileUrl('register').'">登记</a>您的信息。';
		}
		
		if (!empty($member)) {
			pdo_update('wxwall_members', array(
				'lastupdate' => TIMESTAMP
			), array(
				'from_user' => $this->message['from'],
				'rid' => $this->rule,
			));
		} else {
			$data = array(
				'from_user' => $this->message['from'],
				'rid' => $this->rule,
				'lastupdate' => TIMESTAMP,
				'isblacklist' => 0,
			);
			pdo_insert('wxwall_members', $data);
		}
		$this->beginContext();
		return $this->respText($message);
	}
	
	private function post() {
		global $_W, $engine;
		if (!in_array($this->message['msgtype'], array('text', 'image'))) {
			return false;
		}
		$member = $this->getMember();
		$wall = pdo_fetch("SELECT * FROM ".tablename('wxwall_reply')." WHERE rid = '{$member['rid']}' LIMIT 1");
		
		if ((!empty($wall['timeout']) && $wall['timeout'] > 0 && TIMESTAMP - $member['lastupdate'] >= $wall['timeout'])) {
			$this->endContext();
			return $this->respText('由于您长时间未操作，请重新进入微信墙！');
		}
		$this->refreshContext();
		if ((empty($wall['quit_command']) && $this->message['content'] == '退出') ||
				(!empty($wall['quit_command']) && $this->message['content'] == $wall['quit_command'])) {
			$this->endContext();
			return $this->respText($wall['quit_tips']);
		}
		
		if (empty($member['nickname'])) {
			return $this->respText('发表话题前请<a target="_blank" href="'.$this->createMobileUrl('register').'">登记</a>您的信息。');
		}
		
		$data = array(
			'rid' => $member['rid'],
			'from_user' => $this->message['from'],
			'type' => $this->message['type'],
			'createtime' => TIMESTAMP,
		);
		if (empty($wall['isshow']) && empty($member['isblacklist'])) {
			$data['isshow'] = 1;
		} else {
			$data['isshow'] = 0;
		}
		if ($this->message['type'] == 'text') {
			$data['content'] = $this->message['content'];
		}
		if ($this->message['type'] == 'image') {
			$image = ihttp_request($this->message['picurl']);
			$filename = 'wxwall/' . $member['rid'] . '/' . random(30) . '.jpg';
			file_write($filename, $image['content']);
			$data['content'] = $filename;
		}
		if ($this->message['type'] == 'link') {
			$data['content'] = iserializer(array('title' => $this->message['title'], 'description' => $this->message['description'], 'link' => $this->message['link']));
		}
		pdo_insert('wxwall_message', $data);
		pdo_update('wxwall_members', array('lastupdate' => TIMESTAMP), array('from_user' => $this->message['from']));
		
		$content = $wall['send_tips'];
		if (!empty($member['isblacklist'])) {
			$content .= '你已被列入黑名单，发送的消息需要管理员审核！';
		}
		return $this->respText($content);
	}
	
	private function getMember() {
		global $_W;
		$rid = $this->rule;
		$member = pdo_fetch("SELECT id, lastupdate, rid FROM ".tablename('wxwall_members')." WHERE from_user = '{$this->message['from']}' AND rid = '$rid' LIMIT 1");
		if (!empty($member)) {
			$profile = (array)pdo_fetch("SELECT nickname, avatar FROM ".tablename('fans')." WHERE from_user = '{$this->message['from']}' LIMIT 1");
			if (!empty($profile)) {
				$member = array_merge($member, $profile);
			}
		}
		return $member;
	}
	
	public function hookBefore() {
		global $_W, $engine;
	}
}
