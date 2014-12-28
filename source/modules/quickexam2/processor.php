<?php
/**
 * 微试卷
 * QQ群：304081212
 * 作者：微动力, 547753994
 *
 * 网站：www.xuehuar.com
 */

defined('IN_IA') or exit('Access Denied');

class QuickExam2ModuleProcessor extends WeModuleProcessor {
	public $table_reply = 'quickexam2_reply';
	public $table_paper = 'quickexam2_paper';

	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from_user = $this->message['from'];
		if ($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid` = :rid", array(':rid' => $rid));
			if ($reply) {
				$paper = pdo_fetch("SELECT * FROM " .tablename($this->table_paper) . " WHERE `paper_id` = :paper_id AND `weid` = :weid", array('paper_id' => $reply['paper_id'], 'weid' => $_W['weid']));
				$news = array();
				if (!empty($paper)) {
					$news = array(
						'title' => htmlspecialchars_decode($paper['title']),
						'description' =>$this->deleteSpace(strip_tags(htmlspecialchars_decode($paper['explain']))),
						'picurl' => $this->getPicUrl($paper['logo']),
						'url' => $_W['rootsite'] . $this->createMobileUrl('paper', array('paper_id' => $paper['paper_id']))
					);
					return $this->respNews($news);
				} else {
					return $this->respText('您好,该活动已经结束');
				}
			}
		}
	}


	private function deleteSpace($str) {
		$str = trim($str);
		$str = strtr($str,"\t","");
		$str = strtr($str,"\r\n","");
		$str = strtr($str,"\r","");
		$str = strtr($str,"\n","");
		$str = strtr($str," ","");
		$str = str_replace('&nbsp;', "",$str);
		return trim($str);
	}
	
	private function getPicUrl($url) {
		global $_W;
		if (empty($url)) {
			$r = $_W['siteroot'] . "/source/modules/quickexam2/images/default_cover.jpg";
		} else {
			$r = strpos($url, 'http://') === FALSE ? $_W['attachurl'] . $url : $url;
		}
		return $r;
	}
}
