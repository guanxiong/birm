<?php
/**
 * 留言板模块定义
 *
 * @author daduing
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class MsgModuleSite extends WeModuleSite {
	public $tablename = 'msg_reply';
  	/*
	 * 内容管理
	 */
	public function doWebmanage() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete($this->tablename, " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/manage', array('name' => 'msg', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$list = pdo_fetchall("SELECT * FROM ".tablename('msg_reply')." AS a INNER JOIN ".tablename('fans')." AS b ON a.fid=b.id WHERE a.rid = '{$id}' ORDER BY a.create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('msg_reply') . " WHERE rid = '{$id}'");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('manage');
	}
}
