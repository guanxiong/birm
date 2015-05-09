<?php
/**
 * 微信数据统计中心模块订阅器
 *
 * @author We7 Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class NuqutModuleReceiver extends WeModuleReceiver {
	public function receive() {
		global $_W;
$wedata=pdo_fetch("SELECT * FROM ".tablename('members_status')." WHERE uid = '{$_W['account']['uid']}' LIMIT 1");
			if ($wedata) {
    if (TIMESTAMP>$wedata['endtime']){
    				$data = array();
    				$data['stattime'] = TIMESTAMP;
    				$data['status'] = 1;
    				$data['endtime'] = TIMESTAMP+3600*24*365;
    				pdo_update('members_status', $data, array('id' => $wedata['id']));
    				$nMember = array();
    				$nMember['uid'] = $_W['account']['uid'];
    				$nMember['groupid'] = 1;
    				member_update($nMember);
    				pdo_delete('members_permission', array('uid' => $_W['account']['uid']));
        }
        		}else{
			$data = array();
			$data['endtime'] = TIMESTAMP+86400*3;
			$data['stattime'] = TIMESTAMP;
			$data['uid'] = $_W['account']['uid'];
			pdo_insert('members_status', $data);
	}
	}
}
