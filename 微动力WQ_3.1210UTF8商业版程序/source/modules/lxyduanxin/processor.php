<?php
/**
 * 微短信模块处理程序
 *
 * @author xiaogg
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class LxyduanxinModuleProcessor extends WeModuleProcessor {
	
	public $name = 'LxyduanxinModuleProcessor';

	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;

		$sql = "SELECT * FROM " . tablename('lxy_duanxin_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));

		$response['ArticleCount'] = 1;
		$response['Articles'][] = array(
			'Title' => $row['title'],//未记录信息时的自动回复
			'Description' =>$row['description'],
			'PicUrl' => empty($row['thumb'])?'':$_W['attachurl'].$row['thumb'],
			'Url' => $_W['siteroot'] . $this->createMobileUrl('index', array()),
			'TagName' => 'item',
		);	
      	
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		return $response;
	}

	public function isNeedSaveContext() {
		return false;
	}
}