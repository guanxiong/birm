<?php
defined('IN_IA') or exit('Access Denied');

class BlackModuleSite extends WeModuleSite {
	public function doMobileindex(){
		checkauth();
		include $this->template('index');
	}
}