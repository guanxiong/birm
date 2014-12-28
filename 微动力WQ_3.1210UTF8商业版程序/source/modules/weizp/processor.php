<?php

defined('IN_IA') or exit('Access Denied');

class WeizpModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$from_user = $this->message['from'];
		$author = pdo_fetch("SELECT * FROM ".tablename('weizp_author')." WHERE from_user = :from_user AND rid = :rid", array(':rid' =>$this-> rule,':from_user'=>$from_user));
		if(empty($author['from_user'])){
			pdo_insert('weizp_author',array('from_user'=>$from_user,'rid'=>$this->rule));
			
		}
		$reply = pdo_fetch("SELECT * FROM ".tablename('weizp_reply')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (!empty($reply)) {
			$response[] = array(
				'title' => $reply['title'],
				'description' => $reply['description'],
				'picurl' => $reply['cover'],
				'url' => $this->buildSiteUrl($this->createMobileUrl('index', array('rid' => $reply['rid'],'form_user'=>$from_user))),
			);
			return $this->respNews($response);
		}
	}
}