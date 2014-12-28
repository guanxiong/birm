<?php
/**
 * 游戏管家模块定义
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class YobygameModule extends WeModule {

	public function settingsDisplay($settings) {
				global $_GPC, $_W;
  					if(!isset($settings['gamen'])) {

				$settings['gamen'] = '3';

			}
		if(checksubmit()) {
			//字段验证, 并获得正确的数据$dat
				$dat = array(
			'gamen' => $_GPC['gamen'],
			'gamew' => $_GPC['gamew'],
			);
			$this->saveSettings($dat);
			message('保存成功', 'refresh');
		}
		include $this->template('setting');
	}

}