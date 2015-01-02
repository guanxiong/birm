<?php
/**
 * 漂流瓶模块处理程序
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com
 */
defined('IN_IA') or exit('Access Denied');

class FloaterModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码

		$response = array();
		$response['FromUserName'] = $this->message['to'];
		$response['ToUserName'] = $this->message['from'];
		$response['MsgType'] = 'news';
		$response['ArticleCount'] = count($list)+1;
		$response['Articles'][1] = array(
              	'Title' => '祈愿祝福 ',
        		'Description' =>$_W['siteroot'],
        		'PicUrl' => $_W['siteroot'].'/source/modules/commform/template/banner.jpg',
        		'Url' =>$_W['siteroot'].create_url('mobile/module',array('do' => 'wish','weid'=>$_W['weid'],'name' => 'floater','userid'=>$this->message['from'])),
        		'TagName' => 'item',
		);
		return $response;
	}
}