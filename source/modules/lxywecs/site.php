<?php
/**
 * 微客服
 *
 * @author BIN MODIFIED BY大路货 QQ:792454007
 * @url 
 */

defined('IN_IA') or exit('Access Denied');

class LxywecsModuleSite extends WeModuleSite {
	public $cstable='lxy_wecs';

	
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}
	
	public function doWebDisplay() {
		global $_GPC, $_W;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
	
		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime('-1 month') : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";
		
		$list = pdo_fetchall("SELECT a.id,a.from_user, a.nickname,a.createtime,b.id as cstid FROM ".tablename('fans')." a left join ".tablename($this->cstable)." b on a.from_user=b.openid and a.weid=b.weid WHERE a.follow = 1 AND a.weid = '{$_W['weid']}'  AND from_user <> '' $where ORDER BY b.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('fans')." WHERE follow = 1 AND weid = '{$_W['weid']}' $where ");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('display');
	}
	
	public function doWebSetascs() {
		global $_GPC, $_W;		
		$id = intval($_GPC['id']);
		$fan = pdo_fetch("SELECT * FROM ".tablename("fans")." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($fan)) {
			message('抱歉，找不到您要设置的粉丝信息！', '', 'error');
		}		
		$item = pdo_fetch("SELECT * FROM ".tablename($this->cstable)." WHERE openid=:openid and weid=:weid" , array(':openid' => $fan['from_user'],':weid'=>$_W['weid']));
		if (empty($item)) {
			
			$data = array(
					'workid'=>$fan['nickname'],
					'weid' => $_W['weid'],
					'openid'=> $fan['from_user'],
					'start_time'=>'08:30',
					'end_time'=>'17:30',
					'busy'=> 1,
			);
			pdo_insert($this->cstable, $data);
			message('设置客服成功！', referer(), 'success');
		}
		else
		{
			if($item['busy']==2)
			{
				message('当前客服正在服务...请先将客服状态设置为非忙碌状态后重试！', referer(), 'error');
			}
			else 
			{
				pdo_delete($this->cstable, array('id' => $item['id']));
				message('你指定的用户已被取消客服设置！', referer(), 'success');
			}
		}
		
	}
	
	
	public function doWebCsadd() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		if(!empty($id))
		{
			$item = pdo_fetch("SELECT * FROM ".tablename($this->cstable)." WHERE weid=:weid and id=:id", array(':weid' => $weid,':id'=>$id));
			if(empty($item))
			{
				message('抱歉,您编辑的客服资料不存在或已删除');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['openid'])) {
				message('请输入客服openid！');
			}
			$data = array(
					'workid'=>$_GPC['workid'],					
					'weid' => $_W['weid'],
					'openid'=> $_GPC['openid'],
					'csid'=> $_GPC['csid'],
					'start_time'=>$_GPC['start_time'],
					'end_time'=>$_GPC['end_time'],
					'busy'=> $_GPC['busy'],
			);
			if (empty($id))
			{
				pdo_insert($this->cstable, $data);
			}
			else
			{
				pdo_update($this->cstable, $data, array('id' => $id));
			}
			message('客服信息更新成功！', $this->createWebUrl('cslist'), 'success');
		}
		include $this->template('csadd');
	}
	
	public function doWebCsedit() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];

		if(empty($id))
		{
			message('抱歉,请先指定您要编辑的客服！');
		}
		
		$item = pdo_fetch("SELECT * FROM ".tablename($this->cstable)." WHERE weid=:weid and id=:id", array(':weid' => $weid,':id'=>$id));
		if(empty($item))
		{
			message('抱歉,您编辑的客服资料不存在或已删除');
		}

		if (checksubmit('submit')) {
			if (empty($_GPC['openid'])) {
				message('请输入客服openid！');
			}
			$data = array(
					'busy'=> $_GPC['busy'],
					'start_time' => $_GPC['start_time'],
					'end_time' => $_GPC['end_time'],
			);

			pdo_update($this->cstable, $data, array('id' => $id));

			message('客服状态更新成功！', $this->createWebUrl('cslist'), 'success');
		}
		include $this->template('csedit');
	}
	
	public function doWebCslist() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND b.nickname LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT a.*,b.nickname,b.realname FROM ".tablename($this->cstable)."a left join ".tablename('fans'). " b on a.openid=b.from_user WHERE a.weid = '{$weid}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->cstable) . " WHERE weid = '{$weid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('cslist');
	}
	
	public function doWebDelcs() {
		global $_GPC,$_W;
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->cstable)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，指定客服不存在或是已经删除！', '', 'error');
		}
		pdo_delete($this->cstable, array('id' => $item['id']));
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
