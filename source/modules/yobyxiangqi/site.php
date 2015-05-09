<?php
/**
 * 中国象棋模块微站定义
 *
 * @author yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class YobyxiangqiModuleSite extends WeModuleSite {

	public function doMobileFm() {
					global $_W;
		
		$url = 'http://'.$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$src = $_W['siteroot'].'source/modules/yobyxiangqi/images/';
	$weixin = "添加公众号【".$_W['account']['name']."】";
		
		include $this->template('xq');
	}

}