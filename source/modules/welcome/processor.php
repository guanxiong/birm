<?php
/**
 * 欢迎信息处理类
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class WelcomeModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W, $engine;
		$sql = "SELECT `welcome` FROM " . tablename('wechats') . " WHERE `weid`=:weid";
		$default = pdo_fetchcolumn($sql, array(':weid' => $_W['weid']));
		if (is_array(iunserializer($default))) {
			$default = iunserializer($default);
			$_W['module'] = $default['module'];
			$processor = WeUtility::createModuleProcessor($default['module']);
			$processor->message = $this->message;
			$processor->inContext = $this->inContext;
			$processor->rule = $default['id'];
			$processor->module = $_W['account']['modules'][$default['module']];
			$engine->response['rule'] = $default['id'];
			return $processor->respond();
		}
		return $this->respText($default);
	}
}
