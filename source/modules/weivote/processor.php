<?php
/**
 * 模块处理程序
 *
 * @author 回忆Kiss
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class WeivoteModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];

        global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('weivote_setting') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
        
        //$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		
        /**
         * 预定义的操作, 构造返回图文消息结构
         * @param array $news 回复的图文定义(定义为元素集合, 每个元素结构定义为 title - string: 新闻标题, description - string: 新闻描述, picurl - string: 图片链接, url - string: 原文链接)
         * @return array 返回的消息数组结构
         */
        return $this->respNews(array(
			'title' => $row['title'] == '' ? '微投票': $row['title'],
			'description' => $row['description'] == '' ? '微投票': $row['description'],
			'picUrl' => $_W['attachurl'] . $row['picture'],
			'url' => $this->createMobileUrl('url', array('id' => $rid)),
		));
	}
}