<?php
/**
 * oauth2 授权模块定义
 *
 * @author on3
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class Oauth2Module extends WeModule {
	public $table_config = 'oconfig';

	public function settingsDisplay($settings) {
		global $_W,$_GPC;
		$settings = pdo_fetch('SELECT * FROM'.tablename($this->table_config)." WHERE weid = :weid",array(':weid'=>$_W['weid']));
		if(checksubmit()) {
			$data = array(
				'ising'=>$_GPC['ising'],
				'weid'=>$_W['weid']
				);
			if(empty($settings)){
				pdo_insert($this->table_config,$data);
			}else{
				pdo_update($this->table_config,array('ising'=>$_GPC['ising']),array('weid'=>$_W['weid']));
			}
			message('操作成功',referer(),'success');
		}
		include $this->template('settings');
	}
}