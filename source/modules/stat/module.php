<?php
/**
 * 微新星统计中心模块定义
 *
 * @author We7 Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class StatModule extends WeModule {
	public function settingsDisplay($settings) {
		global $_W, $_GPC;
		if(checksubmit()) {
			$cfg = array();
			$cfg['msg_history'] = $_GPC['msg_history'] == '1';
			$cfg['msg_maxday'] = intval($_GPC['msg_maxday']);
			$cfg['use_ratio'] = $_GPC['use_ratio'] == '1';
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}
		if(!isset($settings['msg_history'])) {
			$settings['msg_history'] = '1';
		}
		if(!isset($settings['msg_maxday'])) {
			$settings['msg_maxday'] = '0';
		}
		if(!isset($settings['use_ratio'])) {
			$settings['use_ratio'] = '1';
		}
		include $this->template('setting');
	}
}
