<?php
defined('IN_IA') or exit('Access Denied');

class Game2048ModuleSite extends WeModuleSite {
	public function doMobileindex(){
		checkauth();
		include $this->template('index');
	}
}