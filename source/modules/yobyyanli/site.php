<?php
/**
 * 最强眼力模块微站定义
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class YobyyanliModuleSite extends WeModuleSite {

	public function doMobileFm() {
	global $_W;
		include $this->template('bi');
	}

}