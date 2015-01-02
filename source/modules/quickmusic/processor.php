<?php
/**
 * 微问卷
 * QQ群：304081212
 * 作者：微新星, 547753994
 *
 * 网站：www.xuehuar.com
 */

defined('IN_IA') or exit('Access Denied');

class QuickMusicModuleProcessor extends WeModuleProcessor {
	public $table_reply = 'quickmusic_reply';
	public $table_tape = 'quickmusic_tape';

	public function respond() {
		global $_W;
		$rid = $this->rule;
		$from_user = $this->message['from'];
		if ($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid` = :rid", array(':rid' => $rid));
			if ($reply) {
				$tape = pdo_fetch("SELECT * FROM " .tablename($this->table_tape) . " WHERE `tape_id` = :tape_id AND `weid` = :weid", array('tape_id' => $reply['tape_id'], 'weid' => $_W['weid']));
				$news = array();
				if (!empty($tape)) {
					$news = array(
						'title' => htmlspecialchars_decode($tape['title']),
						'description' =>$this->deleteSpace(strip_tags(htmlspecialchars_decode($tape['explain']))),
						'picurl' => $this->getPicUrl($tape['logo']),
						'url' => $_W['rootsite'] . $this->createMobileUrl('tape', array('tape_id' => $tape['tape_id']))
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
			$r = $_W['siteroot'] . "/source/modules/quickmusic/images/default_cover.jpg";
		} else {
			if(!preg_match('/^(http|https)/', $url)) {  //如果是相对路径
				$r = $_W['attachurl'] .  $url;
			} else {
				$r = $url;
			}   	   
		}
		return $r;
	}
}
