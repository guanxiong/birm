<?php
/**
 * 二维码推广模块微站定义
 *
 */
defined('IN_IA') or exit('Access Denied');

require_once  IA_ROOT . '/source/class/account.class.php';

class QrcodeModuleSite extends WeModuleSite {
	public $tablename = 'qrcode';
	public $tablename2 = 'qrcode_stat';

	public function doWebPost() {
		global $_W,$_GPC;
		//这个操作被定义用来呈现 管理中心导航菜单
		if(checksubmit('submit')){
			//二维码结构定义
			$barcode = array(
					'expire_seconds' => '',
					'action_name' => '',
					'action_info' => array(
							'scene' => array('scene_id' => ''),
					),
			);
			$qrctype = intval($_GPC['qrc-model']);
			$uniacccount = WeAccount::create($_W['weid']);
				
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$qrcrow = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE id = '{$id}'");
				$update = array(
					'keyword' => $_GPC['keyword'],
					'name' => $_GPC['scene-name'],
					'status' => '1',
				);
				if ($qrcrow['model'] == 1) {
					$barcode['action_info']['scene']['scene_id'] = $qrcrow['qrcid'];
					$barcode['expire_seconds'] = intval($_GPC['expire-seconds']);
					$barcode['action_name'] = 'QR_SCENE';
					$result = $uniacccount->barCodeCreateDisposable($barcode);
					$update['ticket'] = $result['ticket'];
					$update['expire'] = $result['expire_seconds'];
					$update['createtime'] = TIMESTAMP;
				} elseif ($qrcrow['model'] == 2) {
					$barcode['action_info']['scene']['scene_id'] = $qrcrow['qrcid'];
					$barcode['action_name'] = 'QR_LIMIT_SCENE';
					$result = $uniacccount->barCodeCreateFixed($barcode);
					$update['ticket'] = $result['ticket'];
					$update['createtime'] = TIMESTAMP;
				}
				pdo_update('qrcode', $update, array('id' => $id));
				message('恭喜，更新带参数二维码成功！', create_url('site/module/list', array('name' => 'qrcode')), 'success');
			}
				
			if ($qrctype == 1) {
				$qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename('qrcode')." WHERE model = '1' ORDER BY qrcid DESC");
				$barcode['action_info']['scene']['scene_id'] = !empty($qrcid) ? ($qrcid+1) : 100001;
				$barcode['expire_seconds'] = intval($_GPC['expire-seconds']);
				$barcode['action_name'] = 'QR_SCENE';
				$result = $uniacccount->barCodeCreateDisposable($barcode);
			} else if ($qrctype == 2) {
				$qrcid = pdo_fetchcolumn("SELECT qrcid FROM ".tablename('qrcode')." WHERE model = '2' ORDER BY qrcid DESC");
				$barcode['action_info']['scene']['scene_id'] = !empty($qrcid) ? ($qrcid+1) : 1;
				if ($barcode['action_info']['scene']['scene_id'] > 100000) {
					message('抱歉，永久二维码已经生成最大数量，请先删除一些。');
				}
				$barcode['action_name'] = 'QR_LIMIT_SCENE';
				$result = $uniacccount->barCodeCreateFixed($barcode);
			} else {
				message('抱歉，此公众号暂不支持您请求的二维码类型！');
			}
				
			if(!is_error($result)) {
				$insert = array(
						'weid' => $_W['weid'],
						'qrcid' => $barcode['action_info']['scene']['scene_id'],
						'keyword' => $_GPC['keyword'],
						'name' => $_GPC['scene-name'],
						'model' => $_GPC['qrc-model'],
						'ticket' => $result['ticket'],
						'expire' => $result['expire_seconds'],
						'createtime' => TIMESTAMP,
						'status' => '1',
				);
				pdo_insert('qrcode', $insert);
				message('恭喜，生成带参数二维码成功！', create_url('site/module/list', array('name' => 'qrcode')), 'success');
			} else {
				message("公众平台返回接口错误. <br />错误代码为: {$result['errorcode']} <br />错误信息为: {$result['message']}");
			}
		}
		$id = intval($_GPC['id']);
		$row = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE id = '{$id}'");
		include $this->template('post');
	}
	
	public function doWebList() {
		global $_W,$_GPC;
		//这个操作被定义用来呈现 管理中心导航菜单
		$uniacccount = WeAccount::create($_W['weid']);
		$qrcodeurl = $uniacccount->apis['barcode']['display'];
		
		!empty($_GPC['keyword']) && $where .= " AND name LIKE '%{$_GPC['keyword']}%'";
		
		//删除过期二维码
		pdo_query("UPDATE ".tablename($this->tablename)." SET status = '0' WHERE weid = '{$_W['weid']}' AND model = '1' AND createtime < '{$_W['timestamp']}' - expire");
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->tablename)." WHERE weid = '{$_W['weid']}' $where ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$qrcode) {
				$qrcode['showurl'] = sprintf($qrcodeurl, urlencode($qrcode['ticket']));
				$qrcode['endtime'] = $qrcode['createtime'] + $qrcode['expire'];
				if (time()>$qrcode['endtime']) {
					$qrcode['endtime'] = '<font color="red">已过期</font>';
				}else{
					$qrcode['endtime'] = date('Y-m-d H:i:s',$qrcode['endtime']);
				}
				if ($qrcode['model'] == 2) {
					$qrcode['modellabel']="永久";
					$qrcode['expire']="永不";
					$qrcode['endtime'] = '<font color="green">永不</font>';
				} else {
					$qrcode['modellabel']="临时";
				}
			}
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tablename) . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('list');
	}
	public function doWebDisplay() {
		global $_W,$_GPC;
		//这个操作被定义用来呈现 管理中心导航菜单
		$qrcodeurl=$this->gateway['get'];
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";
		!empty($_GPC['keyword']) && $where .= " AND name LIKE '%{$_GPC['keyword']}%'";
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->tablename2)." WHERE weid = '{$_W['weid']}' $where ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$qrcode) {
				if ($qrcode['type'] == 1) {
					$qrcode['type']="关注";
				} else {
					$qrcode['type']="扫描";
				}
			}
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tablename2) . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('display');
	}

	public function doWebdel(){
		global $_W,$_GPC;
		if ($_GPC['scgq']) {
			$list = pdo_fetchall("SELECT id FROM ".tablename('qrcode')." WHERE weid = '{$_W['weid']}' AND status = '0'", array(), 'id');
			if (!empty($list)) {
				pdo_query("DELETE FROM ".tablename('qrcode')." WHERE id IN (".implode(',', array_keys($list)).")");
				pdo_query("DELETE FROM ".tablename('qrcode_stat')." WHERE qid IN (".implode(',', array_keys($list)).")");
			}
			message('执行成功<br />删除二维码：'.count($list),create_url('site/module/list', array('name' => 'qrcode')),'success');
		}else{
			$id = $_GPC['id'];
			pdo_delete('qrcode', array('id' =>$id));
			pdo_delete('qrcode_stat',array('qid' => $id));
			message('删除成功',create_url('site/module/list', array('name' => 'qrcode')),'success');
		}
	}

	public function doWebdelsata(){
		global $_W,$_GPC;
		$id = $_GPC['id'];
		$b = pdo_delete('qrcode_stat',array('id' =>$id, ));
		if ($b){
			message('删除成功',create_url('site/module/display', array('name' => 'qrcode')),'success');
		}else{
			message('删除失败',create_url('site/module/display', array('name' => 'qrcode')),'error');
		}
	}
	
	public function doWebExtend() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$qrcrow = pdo_fetch("SELECT * FROM ".tablename('qrcode')." WHERE id = '{$id}'");
			$update = array();
			if ($qrcrow['model'] == 1) {
				$uniacccount = WeAccount::create($_W['weid']);
				$barcode['action_info']['scene']['scene_id'] = $qrcrow['qrcid'];
				$barcode['expire_seconds'] = 1800;
				$barcode['action_name'] = 'QR_SCENE';
				$result = $uniacccount->barCodeCreateDisposable($barcode);
				$update['ticket'] = $result['ticket'];
				$update['expire'] = $result['expire_seconds'];
				$update['createtime'] = TIMESTAMP;
				pdo_update('qrcode', $update, array('id' => $id));
			}
			message('恭喜，延长临时二维码时间成功！', referer(), 'success');
		}
	}

	public function doWebSubDisplay() {
		global $_W,$_GPC;
		//这个操作被定义用来呈现 管理中心导航菜单
		$qrcodeurl=$this->gateway['get'];
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";
		!empty($_GPC['keyword']) && $where .= " AND name LIKE '%{$_GPC['keyword']}%'";
		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->tablename2)." WHERE weid = '{$_W['weid']}' $where ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$qrcode) {
				if ($qrcode['issubs'] == 1) {
					$qrcode['issubs']="是";
				} else {
					$qrcode['issubs']="否";
				}
			}
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->tablename2) . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('display');
	}
}