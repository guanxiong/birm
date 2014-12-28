<?php
/**
 * panorama模块处理程序
 *
 * @author x-feng
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class PanoramaModuleProcessor extends WeModuleProcessor {
	// public $manage = 'panorama_manage';
	public function respond() {
		global $_W,$_GPC;
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		// $sql = "select * from".tablename('rule_keyword')."where content=".$content;
		// $item = pdo_fetch($sql);
		// if (empty($item)) {
		// 	message('没有相关全景信息!');
		// }else{
		// 	$sql = "select * from".tablename($this->manage);
		// 	$list = pdo_fetchall($sql);
			
		// }

	}
}