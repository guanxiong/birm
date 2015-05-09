<?php
/**
 * 微官网模块处理程序
 *
 * @author WeEngine Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class SiteModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微动力文档来编写你的代码
		$isfill = pdo_fetchcolumn("SELECT isfill FROM ".tablename('article_reply')." WHERE rid =:rid AND articleid = '0'", array(':rid' => $this->rule));
		$reply = pdo_fetchall("SELECT * FROM ".tablename('article_reply')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (!empty($reply)) {
			foreach ($reply as $row) {
				$ids[$row['articleid']] = $row['articleid'];
			}
			$article = pdo_fetchall("SELECT id, title, thumb, content, description FROM ".tablename('article')." WHERE id IN (".implode(',', $ids).")", array(), 'id');
		}
		if ($isfill && ($count = 8 - count($reply)) > 0) {
			$articlefill = pdo_fetchall("SELECT id, title, thumb, content, description FROM ".tablename('article')." WHERE weid = '{$_W['weid']}' AND id NOT IN (".implode(',', $ids).") ORDER BY id DESC LIMIT $count", array(), 'id');
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
				if(!empty($row)) {
					$response[] = array(
						'title' => $row['title'],
						'description' => $row['description'],
						'picurl' => $row['thumb'],
						'url' => $this->buildSiteUrl($this->createMobileUrl('detail', array('id' => $row['id']))),
					);
				}
			}
		}
		return $this->respNews($response);
	}
}