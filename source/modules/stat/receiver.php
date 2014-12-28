<?php
/**
 * 微信数据统计中心模块订阅器
 *
 * @author We7 Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class StatModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_W;

		$cfg = $this->module['config'];
		if(!is_array($cfg) || empty($cfg)) {
			$cfg = array();
			$cfg['msg_maxday'] = 0;
			$cfg['msg_history'] = 1;
			$cfg['use_ratio'] = 1;
		}
		if (!empty($cfg['msg_maxday']) && $cfg['msg_maxday'] > 0) {
			pdo_delete('stat_msg_history', " createtime < ".TIMESTAMP.' - '. $cfg['msg_maxday'] * 86400);
		}
		if ($cfg['msg_history']) {
			switch ($this->message['type']) {
				case 'image':
					$content = $this->message['picurl'];
					break;
				case 'location':
					$content = iserializer(array('x' => $this->message['location_x'], 'y' => $this->message['location_y']));
					break;
				case 'link':
					$content = iserializer(array('title' => $this->message['title'], 'description' => $this->message['description'], 'link' => $this->message['link']));
					break;
				case 'event':
					$content = iserializer(array('event' => $this->message['event'], 'key' => $this->message['eventkey']));
					break;
				default:
					$content = $this->message['content'];
			}
			pdo_insert('stat_msg_history', array(
				'weid' => $_W['weid'],
				'module' => $this->params['module'],
				'from_user' => $this->message['from'],
				'rid' => intval($this->params['rule']),
				'kid' => $this->keyword['id'],
				'message' => $content,
				'type' => $this->message['msgtype'],
				'createtime' => $this->message['time'],
			));
		}
		if (!empty($cfg['use_ratio'])) {
			$updateid = pdo_query("UPDATE ".tablename('stat_rule')." SET hit = hit + 1, lastupdate = '".TIMESTAMP."' WHERE rid = :rid AND createtime = :createtime", array(':rid' => $this->params['rule'], ':createtime' => strtotime(date('Y-m-d'))));
			if (empty($updateid)) {
				pdo_insert('stat_rule', array(
					'weid' => $_W['weid'],
					'rid' => $this->params['rule'],
					'createtime' => strtotime(date('Y-m-d')),
					'hit' => 1,
					'lastupdate' => $this->message['time'],
				));
			}
			if (!empty($this->keyword['id'])) {
				$updateid = pdo_query("UPDATE ".tablename('stat_keyword')." SET hit = hit + 1, lastupdate = '".TIMESTAMP."' WHERE kid = :kid AND createtime = :createtime", array(':kid' => $this->keyword['id'], ':createtime' => strtotime(date('Y-m-d'))));
				if (empty($updateid)) {
					pdo_insert('stat_keyword', array(
						'weid' => $_W['weid'],
						'rid' => $this->params['rule'],
						'kid' => $this->keyword['id'],
						'createtime' => strtotime(date('Y-m-d')),
						'hit' => 1,
						'lastupdate' => $this->message['time'],
					));
				}
			}
		}
	}
}
