<?php
/**
 * 微点餐
 *
 * 作者:迷失卍国度
 *
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');

class IdishModuleProcessor extends WeModuleProcessor {
	
	public $name = 'IdishModuleProcessor';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
        global $_W;
        $rid = $this->rule;
        $sql = "SELECT * FROM " . tablename('idish_reply') . " WHERE `rid`=:rid LIMIT 1";
        $row = pdo_fetch($sql, array(':rid' => $rid));
        $site_index = 1;
        $site_store = 2;
        $site_list = 3;
        $site_menu = 4;
        $site_intelligent = 5;

        if (empty($row['id'])) {
            return array();
        }

        $method_name = 'wapindex';//默认为首页

        if ($row['type'] == $site_store) {
            $method_name = 'waprestlist';
        } else if($row['type'] == $site_list) {
            $method_name = 'waplist';
        } else if($row['type'] == $site_menu) {
            $method_name = 'wapmenu';
        } else if($row['type'] == $site_intelligent) {
            $method_name = 'wapselect';
        }

        $url = $_W['siteroot'] . create_url('mobile/module', array('do' => $method_name, 'name' => 'idish', 'weid' => $_W['weid'],'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')), 'storeid' => $row['storeid']));

        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $this->message['from'];
        $response['MsgType'] = 'news';
        $response['ArticleCount'] = 1;
        $response['Articles'] = array();
        $response['Articles'][] = array(
            'Title' => $row['title'],
            'Description' => $row['description'],
            'PicUrl' => !strexists($row['picture'], 'http://') ? $_W['attachurl'] . $row['picture'] : $row['picture'],
            'Url' => $url,
            'TagName' => 'item',
        );
        return $response;
	}

	public function isNeedSaveContext() {
		return false;
	}
}
