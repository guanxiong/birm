<?php
/**
 * 小黄鸡模块处理程序
 *
 * @author Camaro
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class SimModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		global $_W,$_GPC;
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$result = $this->xhj($content);
		return $this->respText($result);
	}
	private function xhj($keywords){
		$keywords = iconv('UTF-8', 'GBK', $keywords);
		$api = $this->module['config']['skapi'];
		$api = $api.$keywords;
		$output =  file_get_contents($api);
		$strs = array('[msg]'=>'','[/msg]'=>'');
		$data = strtr($output,$strs);
		return $data;
	}
}