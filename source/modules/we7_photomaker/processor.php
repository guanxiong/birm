<?php
/**
 * 微动力微拍模块处理程序
 *
 * @author 微动力团队
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class We7_photomakerModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		global $_W;
		$device = pdo_fetch("SELECT tipsentry, tipssuccess, tipserror, maxuse, enablemsg, enableauthcode FROM ".tablename('we7_photomaker')." WHERE rid = :rid", array(':rid' => $this->rule));
		if (empty($device)) {
			$this->endContext();
			return false;
		}
		if ($this->inContext) {
			if ($this->message['type'] == 'image') {
				$printlog = pdo_fetch("SELECT id, createtime FROM ".tablename('we7_photomaker_log')." WHERE weid = :weid AND openid = :openid AND rid = :rid AND status = '0'", array(':weid' => $_W['weid'], ':rid' => $this->rule, ':openid' => $this->message['from']));
				$data = array(
					'weid' => $_W['weid'],
					'rid' => $this->rule,
					'openid' => $this->message['from'],
					'createtime' => TIMESTAMP,
					'status' => 0,
				);
				if (!empty($printlog['id'])) {
					pdo_update('we7_photomaker_log', $data, array('id' => $printlog['id']));
				} else {
					$data['photo'] = $this->message['picurl'];
					$data['authcode'] = random(6, 1);
					pdo_insert('we7_photomaker_log', $data);
					$printlog['id'] = pdo_insertid();
				}
				if (!empty($device['enablemsg'])) {
					$this->refreshContext(300);
					return $this->respText('请您输入一段留言，将打印在照片上。字数不超过十个字。');
				}
				if (!empty($device['enableauthcode'])) {
					if (!empty($printlog['id']) && TIMESTAMP - $printlog['createtime'] < 60) {
						return false;
					}
					$this->refreshContext(300);
					return $this->showauthcode($device);
				}
				$this->endContext();
				pdo_query("UPDATE ".tablename('we7_photomaker_log')." SET status = '1' WHERE id = :id", array(':id' => $printlog['id']));
				return $this->respText($device['tipssuccess']);
			} elseif ($this->message['type'] == 'text') {
				$isexists = pdo_fetchcolumn("SELECT id FROM ".tablename('we7_photomaker_log')." WHERE weid = :weid AND openid = :openid AND rid = :rid AND status = '0'", array(':weid' => $_W['weid'], ':rid' => $this->rule, ':openid' => $this->message['from']));
				if (!empty($isexists)) {
					if (is_numeric($this->message['content'])) {
						$authcode = pdo_fetchcolumn("SELECT authcode FROM ".tablename('we7_photomaker_log')." WHERE weid = :weid AND openid = :openid AND rid = :rid AND status = '0'", array(':weid' => $_W['weid'], ':rid' => $this->rule, ':openid' => $this->message['from']));
						if ($authcode != $this->message['content']) {
							return $this->respText('抱歉，验证码输入有误请重新输入！');
						}
						$this->endContext();
						pdo_query("UPDATE ".tablename('we7_photomaker_log')." SET status = '1' WHERE id = :id", array(':id' => $isexists));
						return $this->respText($device['tipssuccess']);
					} elseif (is_string($this->message['content']) && $device['enablemsg']) {
						$content = mb_substr($this->message['content'], 0, 30);
						pdo_update('we7_photomaker_log', array('leavemsg' => $content), array('id' => $isexists));
						$authcodereply = $this->showauthcode($device);
						if (!is_error($authcodereply)) {
							return $authcodereply;
						}
						pdo_query("UPDATE ".tablename('we7_photomaker_log')." SET status = '1' WHERE id = :id", array(':id' => $isexists));
						$this->endContext();
						return $this->respText($device['tipssuccess']);
					} else {
						return $this->respText('抱歉，验证码输入有误请重新输入！');
					}
				} else {
					return $this->respText('请您发送一张照片信息！');
				}
			}
		} else {
			$log = pdo_fetch("SELECT COUNT(*) as total FROM ".tablename('we7_photomaker_log')." WHERE weid = :weid AND openid = :openid AND rid = :rid AND status = 1 AND createtime > '".strtotime(date('Y-m-d'))."'", array(':weid' => $_W['weid'], ':rid' => $this->rule, ':openid' => $this->message['from']));
			if ($log['total'] >= $device['maxuse']) {
				pdo_query("DELETE FROM ".tablename('we7_photomaker_log')." WHERE createtime < '".strtotime(date('Y-m-d'))."'");
				return $this->respText('抱歉，您已经超过今天最大使用次数！');
			}
			$this->beginContext(86400);
			return $this->respText($device['tipsentry']);
		}
	}
	
	private function showauthcode($device) {
		global $_W;
		if (!empty($device['enableauthcode'])) {
			$log = pdo_fetch("SELECT authcode FROM ".tablename('we7_photomaker_log')." WHERE weid = :weid AND openid = :openid AND rid = :rid AND status = '0'", array(':weid' => $_W['weid'], ':rid' => $this->rule, ':openid' => $this->message['from']));
			return $this->respText('请输入验证码，确定打印照片。验证码：'.$log['authcode']);
		} else {
			return error('-1');
		}
	}
}