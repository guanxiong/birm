<?php
/**
 * 独立商城模块微站定义
 *
 * @author Weidongli
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class WDL_brokeModuleSite extends WeModuleSite {

	public function doMobileFm() {
	global $_W;
		include $this->template('index');
	}
	public function doWebWdlbroke() {
		global $_GPC, $W;
		include $this->template('wdlbroke');
	}
}