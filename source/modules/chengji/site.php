<?php
/**
 * 优白前后台模板模块微站定义
 *
 * @author Yoby
 * @url http://weixin10.sianapp.com
 */
defined('IN_IA') or exit('Access Denied');

class ChengjiModuleSite extends WeModuleSite {

	public function doMobilelogin() {
		//这个操作被定义用来呈现 管理中心导航菜单
		
		include $this->template('login');
	}

}