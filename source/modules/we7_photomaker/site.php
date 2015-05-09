<?php
/**
 * 微动力微拍模块微站定义
 *
 * @author 微动力团队
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class We7_photomakerModuleSite extends WeModuleSite {

	public function doWebDevice() {
		header('Location: rule.php?act=post&module=we7_photomaker');
		exit;
	}
	
	public function doMobileAjaxdelete() {
		global $_GPC;
		$delurl = $_GPC['pic'];
		if(file_delete($delurl)) {
			echo 1;
		} else {
			echo 0;
		}
	}
	
	public function doWebGetSetting() {
		global $_GPC, $_W;
		$result = array('status' => 0, 'data' => '');
		
		$sn = $_GPC['sn'];
		$sign = $_GPC['sign'];
		
		$device = pdo_fetch("SELECT * FROM ".tablename('we7_photomaker')." WHERE sn = :sn", array(':sn' => $sn));
		if (empty($device)) {
			$result['status'] = -1;
			$result['message'] = '您绑定的设备不存在或是已经被删除，请根据客户端机器码重新添加设备！';
			message($result, '', 'ajax');
		}
		if (!$this->checkSign($device['token'])) {
			$result['status'] = -2;
			$result['message'] = '您的请求来源不正确。';
			//message($result, '', 'ajax');
		}
		
		$data = array(
			'fontfamily' => $device['fontfamily'],
			'fontcolor' => $device['fontcolor'],
			'width' => $device['width'],
			'height' => $device['height'],
			'size' => $device['size'],
			'qrcode' => $_W['attachurl'] . (empty($device['qrcode']) ? 'qrcode_'.$device['weid'].'.jpg' : $device['qrcode']),
		);
		if ($device['adtype'] == 1) {
			$data['admsg'] = '';
			$data['adurlv'] = $device['adurlv'] ? $_W['attachurl'] . '/' .$device['adurlv'] : $_W['siteroot'] . 'images/logov.jpg';
			$data['adurlh'] = $device['adurlh'] ? $_W['attachurl'] . '/' .$device['adurlh'] : $_W['siteroot'] . 'images/logoh.jpg';
		} elseif ($device['adtype'] == 2) {
			$data['admsg'] = $device['admsg'];
			$data['adurlv'] = '';
			$data['adurlh'] = '';
		} else {
			$data['admsg'] = '';
			$data['adurlv'] = '';
			$data['adurlh'] = '';
		}
		$result['data'] = $data;
		message($result, '', 'ajax');
	}
	
	public function doWebMain() {
		global $_GPC, $_W;
		
		$sn = $_GPC['sn'];
		$sign = $_GPC['sign'];
		
		$device = pdo_fetch("SELECT * FROM ".tablename('we7_photomaker')." WHERE sn = :sn", array(':sn' => $sn));
		if (empty($device)) {
			$result['status'] = -1;
			$result['message'] = '您绑定的设备不存在或是已经被删除，请根据客户端机器码重新添加设备！';
			message($result, '', 'ajax');
		}
		
		if (!$this->checkSign($device['token'])) {
			$result['status'] = -2;
			$result['message'] = '您的请求来源不正确。';
			message($result, '', 'ajax');
		}
		
		$device['adpics'] = iunserializer($device['adpics']);
		$device['qrcode'] = $_W['attachurl'] . (empty($device['qrcode']) ? 'qrcode_'.$device['weid'].'.jpg' : $device['qrcode']);
		include $this->template('main_default');
	}
	
	public function doWebGetData() {
		global $_W, $_GPC;
		$result = array('status' => 0, 'data' => '');
		
		$sn = $_GPC['sn'];
		$sign = $_GPC['sign'];
		
		$device = pdo_fetch("SELECT enablemsg, leavemsg, rid, token FROM ".tablename('we7_photomaker')." WHERE sn = :sn", array(':sn' => $sn));
		if (empty($device)) {
			$result['status'] = -1;
			$result['message'] = '您绑定的设备不存在或是已经被删除，请根据客户端机器码重新添加设备！';
			message($result, '', 'ajax');
		}
		
		if (!$this->checkSign($device['token'])) {
			$result['status'] = -2;
			$result['message'] = '您的请求来源不正确。';
			message($result, '', 'ajax');
		}
		
		$data = array();
		$list = pdo_fetchall("SELECT id, leavemsg, photo FROM ".tablename('we7_photomaker_log')." WHERE status = 1 AND rid = '{$device['rid']}' LIMIT 50");
		if (!empty($list)) {
			foreach ($list as $row) {
				if (empty($row['leavemsg']) && !empty($device['enablemsg'])) {
					$row['leavemsg'] = $device['leavemsg'];
				}
				$data[] = $row;
			}
		}
		$result['data'] = $data;
		message($result, '', 'ajax');
	}
	
	public function doWebFinished() {
		global $_W, $_GPC;
		$result = array('status' => 0, 'data' => '');

		$sign = $_GPC['sign'];
		$id = intval($_GPC['id']);
		
		pdo_update('we7_photomaker_log', array('status' => 2), array('id' => $id));
		exit('success');
	}
	
	public function doWebFailed() {
		global $_W, $_GPC;
		$result = array('status' => 0, 'data' => '');
		
		$sign = $_GPC['sign'];
		$id = intval($_GPC['id']);
		
		pdo_update('we7_photomaker_log', array('status' => 3), array('id' => $id));
		exit('success');
	}
	
	public function doWebBatchfinish() {
		global $_W, $_GPC;
		$result = array('status' => 0, 'data' => '');
		
		$sn = $_GPC['sn'];
		$data = $_GPC['data'];
		
		$device = pdo_fetch("SELECT enablemsg, leavemsg, rid, token FROM ".tablename('we7_photomaker')." WHERE sn = :sn", array(':sn' => $sn));
		if (empty($device)) {
			$result['status'] = -1;
			$result['message'] = '您绑定的设备不存在或是已经被删除，请根据客户端机器码重新添加设备！';
			message($result, '', 'ajax');
		}
		
		if (!$this->checkSign($device['token'])) {
			$result['status'] = -2;
			$result['message'] = '您的请求来源不正确。';
			message($result, '', 'ajax');
		}
		
		$data = json_decode($data, true);
		if (!empty($data)) {
			foreach ($data as $i => $row) {
				pdo_update('we7_photomaker_log', array('status' => $row['state']), array('id' => $row['id']));
			}
		}
		exit('success');
	}
	
	protected function checkSign($token = '') {
		global $_W, $_GPC;
		$sign = $_GET;
		$sign[] = $token;
		unset($sign['sign']);
		sort($sign);
		$signstr = md5(implode('', $sign));
		if ($signstr == $_GET['sign']) {
			return true;
		} else {
			return false;
		}
	}
}