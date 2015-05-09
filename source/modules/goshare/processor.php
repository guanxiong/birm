<?php
/**
 * 
 *
 * @author dongyue
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class GoshareModuleProcessor extends WeModuleProcessor {
	public $themetable='goshare_theme';
	public function respond() {
		$content = $this->message['content'];
		$reply = pdo_fetch("SELECT * FROM ".tablename('goshare_theme')." WHERE themekey = :themekey ", array(':themekey' => $content));
			
		if (!empty($reply)) {
			
			$response = array (
					'title' => $reply [headtitle],
					'description' => $reply [themetitle],
					'picurl' => $reply [themelogo],
					'url' => $this->buildSiteUrl ( $this->createMobileUrl ( 'index', array ('themeid' => $reply [id] 
					) ) ) 
			);

			return $this->respNews($response);
		}else{
			return $this->respText('您好,该活动已经结束');
		}
	}
}