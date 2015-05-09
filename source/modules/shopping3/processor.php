<?php
class shopping3ModuleProcessor extends WeModuleProcessor {
	public $message = array('from' => 'fromUser');
	public function respond() {}
	public function index() {
		global $_GPC;
		$template=trim($_GPC['template']);
		return $this->buildSiteUrl(create_url('mobile/module', array('name' => 'shopping3','do'=>'wlhome','weid' => $GLOBALS['_W']['weid'],"template"=>$template)));
	}
}


