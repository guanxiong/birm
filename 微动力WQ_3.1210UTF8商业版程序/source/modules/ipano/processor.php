<?php
/**
 * 360全景
 *
 * 作者:迷失卍国度
 *
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');

class IpanoModuleProcessor extends WeModuleProcessor {
	
	public $name = 'IpanoModuleProcessor';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
        $rid = $this->rule;
        $sql = "SELECT * FROM " . tablename('ipano_reply') . " WHERE `rid`=:rid LIMIT 1";
        $row = pdo_fetch($sql, array(':rid' => $rid));

        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'news';
        $response['ArticleCount'] = 1;
        $response['Articles'] = array();
        $response['Articles'][] = array(
            'Title' => $row['title'],
            'Description' => $row['description'],
            'PicUrl' =>empty($row['picture'])?'':($_W['attachurl'] . $row['picture']),
            'Url' => $_W['siteroot'] . create_url('mobile/module/index', array('name' => 'ipano', 'weid' => $row['weid'], 'rid' => $row['rid'], 'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')))),
            'TagName' => 'item',
        );
        return $response;
	}

	public function isNeedSaveContext() {
		return false;
	}
}
