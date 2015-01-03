<?php
/**
 * 看看你是有多色模块定义
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class YobyseModule extends WeModule {

	public function settingsDisplay($settings) {
					global $_GPC, $_W;
  					if(!isset($settings['g'])) {

				$settings['g'] = 'http://bbs.birm.co';

			}
				if(!isset($settings['url'])) {

				$mo = pdo_fetch("SELECT eid FROM ".tablename('modules_bindings')." WHERE module = :module", array(':module' =>"yobyse"));
				$settings['url'] =$_W['siteroot']."mobile.php?act=entry&eid=".$mo['eid']."&weid=".$_W['weid']."";

			}
			
		if(checksubmit()) {
			//字段验证, 并获得正确的数据$dat
				$dat = array(
			'g' => $_GPC['g'],
			'url' => $_GPC['url'],
			'gameurl' => $_GPC['gameurl'],
			);
			$this->saveSettings($dat);
			message('保存成功', 'refresh');
		}
		include $this->template('setting');
	}

}