<?php
/**
 * 时光轴模块处理程序
 *
 * @author topone4tvs
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class TimeaxisModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$content = $this->message['content'];
		$rid = $this->rule;
		$rule = pdo_fetch('SELECT reptitle,repinfo,repimg,axisid FROM '.tablename('timeaxis_rep').' WHERE rid=:rid',array(':rid'=>$rid));
		return $this->respNews(array(
				'Title' => $rule['reptitle'],
				'Description' => $rule['repinfo'],
		 		'PicUrl' => $_W['attachurl'].$rule['repimg'],
		 		'Url' => $this->createMobileUrl('index',array('tid'=>$rule['axisid']))
			));
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
	}
}