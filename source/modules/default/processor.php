<?php
/**
 * 默认回复处理类
 * 优先回复“优先级”大于默认级别的模块。
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class DefaultModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W, $engine;
		
		$level = array();
		if (!empty($_W['account']['modules'])) {
			foreach ($_W['account']['modules'] as $row) {
				if (!empty($row['displayorder']) && $row['displayorder'] < 127) {
					$level[$row['displayorder']] = $row;
				}
			}
		}
		if (!empty($level)) {
			$response = '';
			for ($i = 1; $i <= 5; $i++) {
				if (!empty($response)) {
					$engine->response['module'] = $_W['module'];
					return $response;
					break;
				}
				if (empty($level[$i])) {
					continue;
				}
				$_W['module'] = $level[$i]['name'];
				$processor = WeUtility::createModuleProcessor($_W['module']);
				$processor->message = $this->message;
				$processor->inContext = false;
				$processor->rule = $this->rule;
				$engine->response['rule'] = $default['id'];
				$processor->module = $_W['account']['modules'][$_W['module']];
				$response = $processor->respond();
			}
		}
		
		if (!empty($_W['account']['default_period'])) {
			$_W['cache']['default_period'] = TRUE;
			$row = pdo_fetch("SELECT id, lastupdate FROM ".tablename('default_reply_log')." WHERE from_user = '{$this->message['from']}' AND weid = '{$_W['weid']}'");
			if (!empty($row)) {
				if (TIMESTAMP - intval($row['lastupdate']) > intval($_W['account']['default_period'])) {
					$_W['cache']['default_period'] = TRUE;
				} else {
					$_W['cache']['default_period'] = FALSE;
				}
			}
			if (!empty($row)) {
				pdo_update('default_reply_log', array('lastupdate' => TIMESTAMP), array('id' => $row['id']));
			} else {
				pdo_insert('default_reply_log', array('from_user' => $this->message['from'], 'lastupdate' => TIMESTAMP, 'weid' => $_W['weid']));
			}
		}
		
		if (!empty($_W['account']['default_period']) && empty($_W['cache']['default_period'])) {
			return;
		}
		$default = pdo_fetchcolumn("SELECT `default` FROM " . tablename('wechats') . " WHERE `weid`=:weid", array(':weid' => $_W['weid']));
		if (is_array(iunserializer($default))) {
			$default = iunserializer($default);
			$_W['module'] = $default['module'];
			$processor = WeUtility::createModuleProcessor($default['module']);
			$processor->message = $this->message;
			$processor->inContext = $this->inContext;
			$processor->rule = $default['id'];
			$processor->module = $_W['account']['modules'][$default['module']];
			return $processor->respond();
		}
		return $this->respText($default);
	}
}
