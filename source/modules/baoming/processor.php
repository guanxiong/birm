<?php
/**
 * 九个一报名系统模块
 * 2013-11-02
 * BY:拥抱
 */
defined('IN_IA') or exit('Access Denied');

class BaomingModuleProcessor extends WeModuleProcessor {
	
	public $name = 'BaomingModuleProcessor';
	public $table = 'baoming_reply';
	
	
	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename($this->table) . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = 1;
		$response['Articles'] = array();
		$response['Articles'][] = array(
			'Title' => $row['title'],
			'Description' => $row['description'],
			'PicUrl' => $_W['attachurl'] . $row['thumb'],
			'Url' => $_W['siteroot'] . create_url('index/module', array('do' => 'list', 'name' => 'baoming', 'id' => $rid, 'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')))),
			'TagName' => 'item',
		);
		return $response;
	}

	public function isNeedSaveContext() {
		return false;
	}
}
