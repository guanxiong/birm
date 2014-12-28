<?php
/**
 * 看看你是有多色模块微站定义
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class YobyseModuleSite extends WeModuleSite {

	public function doMobileFm() {
			global $_W,$_GPC;
		
		$src= $_W['siteroot'].'source/modules/yobyse/images/';
		$weixin = "点击这里[". $_W['account']['name']."]关注我";
		
		$g = $this->module['config']['g'];
		$url = $this->module['config']['url'];
		$gameurl =$this->module['config']['gameurl'];
		include $this->template('se');
	}
	public function doMobileDh() {
				global $_W,$_GPC;
		
		$src= $_W['siteroot'].'source/modules/yobyse/images/';
		$weixin = "点击这里[". $_W['account']['name']."]关注我";
		
		$g = $this->module['config']['g'];
		$url = $this->module['config']['url'];
		$gameurl =$this->module['config']['gameurl'];
		include $this->template('se');
	}

}