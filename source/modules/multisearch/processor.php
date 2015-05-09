<?php
/**
 * 万能查询模块处理程序
 *
 * @author WeEngine Team
 * @url http://we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class MultisearchModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$reid = pdo_fetchcolumn("SELECT reid FROM ".tablename('multisearch_reply')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (!empty($reid)) {
			$item = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = '$reid'");
			$response = array();
			$response[] = array(
				'title' => $item['title'],
				'description' => $item['description'],
				'picurl' => $item['cover'],
				'url' => $this->buildSiteUrl($this->createMobileUrl('detail', array('id' => $item['id']))),
			);
			return $this->respNews($response);
		}
	}
}