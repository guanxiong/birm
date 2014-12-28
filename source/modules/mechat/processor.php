<?php
/**
 * 美洽客服接入模块处理程序
 *
 * @author Yokit QQ:182860914
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class MechatModuleProcessor extends WeModuleProcessor {
	//一定要注意类名
	public function respond() {
		global $_W;
		$content = $this->message['content'];
		/*$processor = WeUtility::createModuleProcessor("basic");
		$processor->message = $this->message;
		$processor->inContext = true;
		$processor->rule = 5;
		return $processor->respond();*/
		
		/*$insert = array('cdata' => $content);
		pdo_insert('test', $insert);*/
		
		/*$processor = WeUtility::createModuleProcessor("welcome");
		$processor->message = $this->message;
		$processor->inContext = true;
		return $processor->respond();*/
		
		$tips="";
		$this->inContext = true;
		return $this->respText($tips);
	}
}