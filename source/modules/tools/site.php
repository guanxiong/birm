<?php
defined('IN_IA') or exit('Access Denied');

class ToolsModuleSite extends WeModuleSite {

	public function doMobileFm() {
	global $_W;
		include $this->template('index');
	}


}
