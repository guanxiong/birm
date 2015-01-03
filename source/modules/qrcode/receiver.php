<?php
/**
 * 粉丝管理模块订阅器
 *
 * @author WeNewstar Team
 * @url http://bbs.birm.co/forum.php?mod=forumdisplay&fid=36&filter=typeid&typeid=1
 */
defined('IN_IA') or exit('Access Denied');

class QrcodeModuleReceiver extends WeModuleReceiver {
	public function receive() {
		if ($this->message['msgtype'] == 'event') {
			if ($this->message['event'] == 'subscribe' && !empty($this->message['ticket'])) {
				$sceneid = $this->message['eventkey'];
				
				$row = pdo_fetch("SELECT id, name FROM ".tablename('qrcode')." WHERE qrcid = '{$sceneid}'");
				$insert = array(
					'weid' => $GLOBALS['_W']['weid'],
					'qid' => $row['id'],
					'openid' => $this->message['from'],
					'type' => 1,
					'qrcid' => $sceneid,
					'name' => $row['name'],
					'createtime' => TIMESTAMP,
				);
				pdo_insert('qrcode_stat', $insert);
			} elseif ($this->message['event'] == 'SCAN') {
				$sceneid = $this->message['eventkey'];
				
				$row = pdo_fetch("SELECT id, name FROM ".tablename('qrcode')." WHERE qrcid = '{$sceneid}'");
				$insert = array(
					'weid' => $GLOBALS['_W']['weid'],
					'qid' => $row['id'],
					'openid' => $this->message['from'],
					'type' => 2,
					'qrcid' => $sceneid,
					'name' => $row['name'],
					'createtime' => TIMESTAMP,
				);
				pdo_insert('qrcode_stat', $insert);
			}
		}
	}
}
