<?php
/**
 * NOW大了模块处理程序
 *
 * @author yuexiage
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class NowbigModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码
	    global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('nowbig_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = 1;
		$response['Articles'] = array();
		$response['Articles'][] = array(
				'Title' => $row['title'],
				'Description' => $row['description'],
				'PicUrl' => empty($row['thumb'])?'':$_W['attachurl'].$row['thumb'],
				'Url' => $_W['siteroot'] .$this->createMobileUrl('nowbig', array('name' => 'nowbig','id' => $rid)),
				'TagName' => 'item',
		);
		return $response;
	}
}