<?php 
/**
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');
class PreviewWeModuleProcessor extends WeModuleProcessor {
	public $message = array('from' => 'fromUser');
	public function respond() {}
	public function index() {
		global $_GPC;
		return $this->buildSiteUrl(create_url('mobile/channel', array('name' => 'index', 'weid' => $GLOBALS['_W']['weid'], 'styleid' => $_GPC['styleid'])));
	}
}
$preview = new PreviewWeModuleProcessor();
header('Location: '.$preview->index());

