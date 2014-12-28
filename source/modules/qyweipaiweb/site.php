<?php
/**
 * 微拍模块微站定义
 *
 * @author 清逸
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class QyweipaiwebModuleSite extends WeModuleSite {

	public $tablename = 'qywpweb_reply';
	public $tablenamelog = 'qywpweb_log';
	public function doWebAwardlist() {
		//这个操作被定义用来呈现 规则列表
		global $_GPC, $_W;
		//checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablename, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$list = pdo_fetchall("SELECT a.id,a.rid,a.pic,a.msg,a.create_time,a.fid,b.from_user,b.nickname,b.realname FROM ".tablename('qywpweb_reply')." AS a INNER JOIN ".tablename('fans')." AS b ON a.fid=b.id WHERE a.rid = '{$id}' ORDER BY a.create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qywpweb_reply') . " WHERE rid = '{$id}'");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('manage');
	}
	public function doWebloglist() {
		//这个操作被定义用来呈现 规则列表
		global $_GPC, $_W;
		//checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablenamelog, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('loglist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$list = pdo_fetchall("SELECT a.id,a.rid,a.mguser,a.count,a.create_time,a.fid,b.from_user,b.nickname,b.realname FROM ".tablename('qywpweb_log')." AS a INNER JOIN ".tablename('fans')." AS b ON a.fid=b.id WHERE a.rid = '{$id}' ORDER BY a.create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('qywpweb_log') . " WHERE rid = '{$id}'");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('loglist');
	}

}