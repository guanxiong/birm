<?php
/**
 * 粉丝模块模块定义
 *
 * @author 珊瑚海
 * @url http://www.vfanm.com/
 */
defined('IN_IA') or exit('Access Denied');

class IfansModule extends WeModule {

	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$cfg = array(
				'guanzhupp' => $_GPC['guanzhupp'],	
				'huoyuepp' => $_GPC['huoyuepp'],
			);
			if($this->saveSettings($cfg)) {
				message('保存成功', 'refresh');
			}
		}
		$weid = $this->_saveing_params['weid'];
		$mid = $this->_saveing_params['mid'];
		if(empty($weid) || empty($mid)) {
			message('访问出错, 请返回重试. ');
		}else{
			$data = pdo_fetch("SELECT settings FROM ".tablename('wechats_modules')." WHERE mid = :mid AND weid = :weid", array(':mid' => $mid, ':weid' => $weid));
			$settings = iunserializer($data['settings']);
		}
		include $this->template('settings');
	}

}