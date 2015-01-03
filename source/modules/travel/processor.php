<?php
/**
 * 微游记模块处理程序
 *
 * @author 珊瑚海
 * @url #
 */
defined('IN_IA') or exit('Access Denied');

class TravelModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码
		$isfill = pdo_fetchcolumn("SELECT id FROM ".tablename('travel_reply')." WHERE rid =:rid AND articleid = '0'", array(':rid' => $this->rule));
		$reply = pdo_fetchall("SELECT * FROM ".tablename('travel_reply')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (!empty($reply)) {
			foreach ($reply as $row) {
				$ids[$row['articleid']] = $row['articleid'];
			}
			$article = pdo_fetchall("SELECT id, title, thumb, content FROM ".tablename('travel')." WHERE id IN (".implode(',', $ids).")", array(), 'id');
		}
		if ($isfill && ($count = 8 - count($reply)) > 0) {
			$articlefill = pdo_fetchall("SELECT id, title, thumb, content FROM ".tablename('travel')." WHERE id NOT IN (".implode(',', $ids).") ORDER BY id DESC LIMIT $count", array(), 'id');
			if (!empty($articlefill)) {
				foreach ($articlefill as $row) {
					$article[$row['id']] = $row;
					$reply[]['articleid'] = $row['id'];
				}
				unset($articlefill);
			}
		}
		if (!empty($reply)) {
			$response = array();
			foreach ($reply as $row) {
				$row = $article[$row['articleid']];
				$response[] = array(
					'title' => $row['title'],
					'description' => $row['content'],
					'picurl' => $row['thumb'],
					'url' => $this->buildSiteUrl($this->createMobileUrl('detail', array('id' => $row['id']))),
				);
			}
		}
		return $this->respNews($response);
	}
}