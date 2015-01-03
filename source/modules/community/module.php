<?php
/**
 * 微社区模块定义
 *
 * @author WeNewstar Team
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class CommunityModule extends WeModule {
	
	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$data = $_GPC['data'];
			$this->saveSettings($data);
			message('设置成功！', referer(), 'success');
		}
		include $this->template('setting');
	}
}