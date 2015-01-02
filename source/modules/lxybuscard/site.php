<?php
/**
 * 
 *
 * [WeEngine System] 更多模块请浏览：bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class LxybuscardModuleSite extends WeModuleSite {
	public $cardtable='lxy_bussiness_card';
	public $coptable='lxy_bussiness_card_cop';
	public $classtable='lxy_bussiness_card_class';
	public $replytable='lxy_bussiness_card_reply';
	public $tplname=array('default','card_yellow','card_bull','card_fas','card_bull_s','card_deful','card_mount');
	
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}
	
	
	public function doWebCardlist() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND username LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->cardtable)." WHERE weid = '{$weid}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->cardtable) . " WHERE weid = '{$weid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('cardlist');
	}
	
	public function doWebCardadd() {
		global $_GPC, $_W;
		$weid=$_W['weid'];
		$id = intval($_GPC['id']);
		if (!empty($id)) 
		{
			$item = pdo_fetch("SELECT * FROM ".tablename($this->cardtable)." WHERE id = :id", array(':id' => $id));
			if (empty($item))
			{
				message('抱歉，名片不存在或是已经删除！', '', 'error');
			}
		}	
	
		if (checksubmit('submit')) {
			if (empty($_GPC['username'])) {
				message('请输入姓名！');
			}
			$data = array(
					'username'=>$_GPC['username'],
					'weid' => $_W['weid'],
					'degree' => $_GPC['degree'],
					'mobile' => $_GPC['mobile'],
					'company' => $_GPC['company'],
					'tel' => $_GPC['tel'],
					'qq' => $_GPC['qq'],
					'email' => $_GPC['email'],
					'websiteswitch' => $_GPC['websiteswitch'],
					'website' =>  $_GPC['website'],
					'addrswitch' => $_GPC['addrswitch'],
					'addr' => $_GPC['addr'],
					'jw_addr' => $_GPC['jw_addr'],
					'lng' => $_GPC['lng'],
					'lat' => $_GPC['lat'],					
					'province' => $_GPC['resideprovince'],
					'city' => $_GPC['residecity'],
					'dist' => $_GPC['residedist'],					
					'createtime' => TIMESTAMP,
			);
			//上传图片
			if (!empty($_FILES['thumb']['tmp_name'])) {
				file_delete($_GPC['thumb_old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}	
			if (empty($id))
			{
				pdo_insert($this->cardtable, $data);
			} else {
				unset($data['createtime']);
				pdo_update($this->cardtable, $data, array('id' => $id));
			}
			message('名片信息更新成功！', $this->createWebUrl('cardlist', array()), 'success');
	
		}
		include $this->template('cardadd');
	}
	
	public function doWebCopadd() {
		global $_GPC, $_W;
		$weid=$_W['weid'];
		$item = pdo_fetch("SELECT * FROM ".tablename($this->coptable)." WHERE weid = :weid", array(':weid' => $weid));
		if (checksubmit('submit')) {
			if (empty($_GPC['copname'])) {
				message('请设置公司名称！');
			}
			$data = array(
					'copname'=>$_GPC['copname'],
					'copintro'=>$_GPC['copintro'],
					'weid' => $_W['weid'],
					'website' =>  $_GPC['website'],
					'bankuser'=>$_GPC['bankuser'],
					'bankname'=>$_GPC['bankname'],
					'bankaccount'=>$_GPC['bankaccount'],
					'addr' => $_GPC['addr'],
					'jw_addr' => $_GPC['jw_addr'],
					'lng' => $_GPC['lng'],
					'lat' => $_GPC['lat'],
					'province' => $_GPC['resideprovince'],
					'city' => $_GPC['residecity'],
					'dist' => $_GPC['residedist'],
					'createtime' => TIMESTAMP,
			);
			//上传图片
			if (!empty($_FILES['thumb']['tmp_name'])) {
				file_delete($_GPC['thumb_old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}
			
			if (empty($item))
			{
				pdo_insert($this->coptable, $data);
			}
			 else
		  {
		  	unset($data['createtime']);
		  	pdo_update($this->coptable, $data, array('weid' => $weid));			  	
			}
			message('通用信息更新成功！', $this->createWebUrl('copadd', array()), 'success');	
		}
		include $this->template('copadd');
	}
	
	public function doWebClasslist() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND cname LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->classtable)." WHERE weid = '{$weid}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->classtable) . " WHERE weid = '{$weid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('classlist');
	}
	
	
	public function doWebClassadd() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		if(!empty($id))
		{
			$item = pdo_fetch("SELECT * FROM ".tablename($this->classtable)." WHERE weid = :weid and id=:id", array(':weid' => $weid,':id'=>$id));
			if(empty($item))
			{
				message('抱歉,您编辑的分类不存在');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['cname'])) {
				message('请输入分类名称！');
			}
			$data = array(
					'cname'=>$_GPC['cname'],
					'isshow'=>$_GPC['isshow'],
					'weid' => $_W['weid'],		
					'outurl'=> $_GPC['outurl'],					
			);
			//上传图片
			if (!empty($_FILES['thumb']['tmp_name'])) {
				file_delete($_GPC['thumb_old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}
				
			if (empty($id))
			{
				pdo_insert($this->classtable, $data);
			}
			else
			{
				pdo_update($this->classtable, $data, array('id' => $id));
			}
			message('产品分类更新成功！', $this->createWebUrl('classlist', array()), 'success');
		}
		include $this->template('classadd');
	}
	
	public function doWebTplsetindex(){
		global $_GPC, $_W;
		$id = $_GPC['id'];
		$weid=$_W['weid'];
		if(empty($id))
		{
			message('抱歉，您查看的名片不存在或已经删除！','','error');
		}
		$list=$this->tplname;
		$style = pdo_fetchcolumn("SELECT style FROM ".tablename($this->cardtable)." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$weid));
		if(empty($style))
		{
			$style='default';
		}
		include $this->template('tplsetindex');
	}
	
	public function doWebAjaxChangetpl()
	{
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$tplname = $_GPC['tpl'];
		$tplname=in_array($tplname, $this->tplname)?$tplname:'';
		$ishave= pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->cardtable)." WHERE id = :id and weid=:weid", array(':id' => $id,':weid'=>$weid));
		//正常情况
		if($tplname!=''&&$ishave)
		{
			$data=array(
					'style'=>$tplname,
			);
			
			$ret=pdo_update($this->cardtable, $data, array('id' => $id));
			if ($ret)
			{
				header('Content-type: '.'text/html');
				echo '1';
				die();
			}
			
		}
		echo '0';
	}
	
	public function doWebstatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		$data = array(
				'status' => $_GPC['status'],
		);	
		if(pdo_update($this->replytable,$data,array('rid' => $rid)))
		{
			message('模块操作成功！', referer(), 'success');
		}
		else 
		{
			message('请保存规则后再进行设置！', referer(), 'success');
		}
	}
	
	public function doWebDeletecard() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->cardtable)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，名片不存在或是已经删除！', '', 'error');
		}
		if (!empty($item['thumb'])) {
			file_delete($item['thumb']);
		}
		pdo_delete($this->cardtable, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}
	
	public function doWebDeleteclass() {
		global $_GPC,$_W;
		$weid=$_W['weid'];
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->classtable)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，分类不存在或是已经删除！', '', 'error');
		}
		if (!empty($item['thumb'])) {
			file_delete($item['thumb']);
		}
		pdo_delete($this->classtable, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}
	
	public function doMobileViewcard() {
		global $_GPC, $_W;
		$id = $_GPC['id'];
		$weid=$_W['weid'];
		$weaccount=$_W['account'];
		$item = pdo_fetch("SELECT * FROM ".tablename($this->cardtable)." WHERE id = :id", array(':id' => $id));		
		$copinfo=pdo_fetch("SELECT * FROM ".tablename($this->coptable)." WHERE weid = :weid", array(':weid' => $weid));		
		if(empty($item))
		{
			message('您指定的名片不存在货已经被删除','','error');			
		}
		if(empty($copinfo))
		{
			message('请先完善公司设置中相关信息，谢谢！','','error');
		}
		$item['copname']=$copinfo['copname'];
		$item['coplogo']=$copinfo['thumb'];
		$item['bankuser']=$copinfo['bankuser'];
		$item['bankname']=$copinfo['bankname'];
		$item['bankaccount']=$copinfo['bankaccount'];
		if($item['addrswitch']==1)
		{
			$item['addr']=$copinfo['addr'];
			$item['jw_addr']=$copinfo['jw_addr'];
			$item['lng']=$copinfo['lng'];
			$item['lat']=$copinfo['lat'];			
		}
		if($item['websiteswitch']==1)
		{
			$item['website']=$copinfo['website'];
		}
	
		$classes = pdo_fetchall("SELECT * FROM ".tablename($this->classtable)." WHERE weid = :weid and isshow=1", array(':weid' => $weid));
		include $this->template('tpl'.$item['style']);
	}
}
