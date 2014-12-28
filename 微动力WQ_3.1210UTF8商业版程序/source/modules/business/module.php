<?php
/**
 * 商户管理模块定义
 *
 * @author 微动力团队
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class BusinessModule extends WeModule {
	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$cfg = array(
				'range' => intval($_GPC['range']),	
			);
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}
		if(!isset($settings['range'])) {
			$settings['range'] = '5';
		}
		include $this->template('setting');
	}
}
