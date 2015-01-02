<?php
/**
 * 通用表单模块处理程序
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com
 */
defined('IN_IA') or exit('Access Denied');

class CommformModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		global $_W;
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码
		$news=pdo_fetch("select * from ".tablename('defineform')." where keyword=:keyword",array("keyword"=>$content));
		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = 1;
		$response['Articles'][1] = array(
              	'Title' => $news['name'],
        		'Description' =>$news['intro'],
        		'PicUrl' => $_W['attachurl'].'/'.$news['bannerurl'],
        		'Url' => $_W['siteroot'].$this->createMobileUrl('showform',array('userid'=>$this->message['from'],fid=>$news['id'],fn=>$news['name'])),
        		'TagName' => 'item',
		);
		return $response;
	}
}