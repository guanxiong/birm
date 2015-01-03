<?php
/**
 * 微路由
 *
 * @author 大路货 QQ:792454007
 * @url 
 * [WNS]更多模块请浏览：BBS.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class LxyrtrouterModuleSite extends WeModuleSite {
	public $routertable='lxy_rtrouter_info';
	public $replytable='lxy_rtrouter_reply';
	public $table_authlist = 'lxy_rtrouter_authentication';
	
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}
	
	
	public function doWebRouteradd() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		if(!empty($id))
		{
			$item = pdo_fetch("SELECT * FROM ".tablename($this->routertable)." WHERE weid=:weid and id=:id", array(':weid' => $weid,':id'=>$id));
			if(empty($item))
			{
				message('抱歉,您编辑的路由器信息不存在或已删除');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['rname'])) {
				message('请输入路由器名称！');
			}
			$data = array(
					'rname'=>$_GPC['rname'],					
					'weid' => $_W['weid'],
					'iurl'=> $_GPC['iurl'],
					'appid'=> $_GPC['appid'],
					'appkey'=> $_GPC['appkey'],
					'nodeid'=> $_GPC['nodeid'],					
					'status'=>$_GPC['status'],
			);
			if (empty($id))
			{
				pdo_insert($this->routertable, $data);
			}
			else
			{
				pdo_update($this->routertable, $data, array('id' => $id));
			}
			message('路由器信息更新成功！', $this->createWebUrl('routerlist'), 'success');
		}
		include $this->template('routeradd');
	}
	
	public function doWebRouterlist() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND rname LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->routertable)." WHERE weid = '{$weid}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->routertable) . " WHERE weid = '{$weid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('routerlist');
	}
	
	public function doWebDelrouter() {
		global $_GPC,$_W;
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->routertable)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，指定路由器不存在或是已经删除！', '', 'error');
		}
		pdo_delete($this->routertable, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}
	
	public function doWebAuthlist() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND b.rname LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT a.*,b.rname FROM ".tablename($this->table_authlist)." a left join ".tablename($this->routertable)." b on a.routerid=b.id  WHERE a.weid = '{$weid}' $condition ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn("SELECT count(*) FROM ".tablename($this->table_authlist)." a left join ".tablename($this->routertable)." b on a.routerid=b.id  WHERE a.weid = '{$weid}'  $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('authlist');
	}
	
	
}
