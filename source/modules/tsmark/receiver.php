<?php
/**
 * 跳蚤市场模块订阅器
 *
 * @author yuexiage
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class TsMarkModuleReceiver extends WeModuleReceiver {
	public function receive() {
		//这里定义此模块进行消息订阅时的, 消息到达以后的具体处理过程, 请查看微动力文档来编写你的代码
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('message_reply') . " WHERE `rid`=:rid LIMIT 1";
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
				'Url' => createMobileUrl('lottery', array( 'name' => 'tsmark', 'id' => $rid,'weid'=>$_W['weid'],'from_user' => base64_encode(authcode($this->message['from'], 'ENCODE')))),
				'TagName' => 'item',
		);
		return $response;
	}
}