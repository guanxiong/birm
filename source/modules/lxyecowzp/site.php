<?php
/**
 * 
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class LxyecowzpModuleSite extends WeModuleSite {
	public $indextable='lxy_ecowzp';	
	public $zwlist='lxy_ecowzp_list_add';
	public $zworder='lxy_ecowzp_order';	
	public $table_reply = 'lxy_ecowzp_reply';


	public function getHomeTiles() {
		global $_W;
		$urls = array();
		$weid=$_W['weid'];
	
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->indextable)." WHERE weid=:weid ", array(':weid'=>$weid));
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[]=array('title'=>$row['title'],'url'=>$this->createMobileurl('showindex',array('id'=>$row['id'])));
				}
		}
		$urls[]=array('title'=>'所有招聘信息','url'=>$this->createMobileurl('showallzwlist'));
		return $urls;
	}
	
	public function doWebQuery() {

		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename($this->indextable) . ' WHERE `weid`=:weid AND `title` LIKE :title';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['id'] = $row['id'];
			$r['title'] = $row['title'];
			$r['description'] = $row['content'];
			$r['thumb'] = $row['thumb'];
			$row['entry'] = $r;
		}

		include $this->template('query');
	}
	
	public function doWebDelete() {

		global $_GPC;
		$id = intval($_GPC['id']);
		pdo_delete($this->table_reply, array('id' => $id));
		message('删除成功！', referer(), 'success');

	}
	public function doWebWzpindex() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->indextable)." WHERE weid = '{$weid}' $condition ORDER BY displayorder DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->indextable) . " WHERE weid = '{$weid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('wzpindex');
	}


	public function doWebDelwzpqy() {
		global $_GPC,$_W;
		$weid=$_W['weid'];
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->indextable)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，该企业不存在或已经删除！', '', 'error');
		}
		if (!empty($item['thumb'])) {
			file_delete($item['thumb']);
		}
		pdo_delete($this->indextable, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}
	
	public function doWebwzpset() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		if(!empty($id))
		{
			$item = pdo_fetch("SELECT * FROM ".tablename($this->indextable)." WHERE weid = :weid and id=:id", array(':weid' => $weid,':id'=>$id));
			if(empty($item))
			{
				message('抱歉,您编辑的公司不存在');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['title'])) {
				message('请输入公司名称！');
			}
			$data = array(
					'weid' => $_W['weid'],
					'title'=>$_GPC['title'],
					'thumb'=>$_GPC['thumb'],
					'address'=>$_GPC['address'],
					'tel'=>$_GPC['tel'],
					'mobile'=>$_GPC['mobile'],
					'info'=>$_GPC['info'],
					'content'=> $_GPC['content'],
					'displayorder'=>$_GPC['displayorder'],
					'jw_addr' => $_GPC['jw_addr'],
					'lng' => $_GPC['lng'],
					'email'=>$_GPC['email'],
					'lat' => $_GPC['lat'],
					'province' => $_GPC['resideprovince'],
					'city' => $_GPC['residecity'],
					'dist' => $_GPC['residedist'],
					'createtime' => TIMESTAMP,
			);
			
			if (empty($id))
			{
				pdo_insert($this->indextable, $data);
			}
			else
			{	
				unset($data['createtime']);		
				pdo_update($this->indextable, $data, array('id' => $id));
			}
			message('公司更新成功！', $this->createWebUrl('wzpindex', array()), 'success');
		}
		include $this->template('wzpset');

	}
	
	public function doWebWzplist() {
		global $_W,$_GPC;
		$weid=$_W['weid'];
		$pindex = max(1, intval($_GPC['page']));
		$hid=$_GPC['hid'];
		$psize = 20;
		$condition = '';
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->zwlist)." WHERE weid = '{$weid}' and hid = '{$hid}' $condition ORDER BY displayorder DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->zwlist) . " WHERE weid = '{$weid}' and hid = '{$hid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('wzplist');
	}


	public function doWebDelwzplist() {
		global $_GPC,$_W;
		$weid=$_W['weid'];
		$id = intval($_GPC['id']);
		$hid=$_GPC['hid'];
		$item = pdo_fetch("SELECT * FROM ".tablename($this->zwlist)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，该企业招聘岗位不存在或已经删除！', '', 'error');
		}
		if (!empty($item['thumb'])) {
			file_delete($item['thumb']);
		}
		pdo_delete($this->zwlist, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}	
	
	public function doWebWzpadd() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$hid=$_GPC['hid'];
		$row=pdo_fetch("select * from ".tablename($this->indextable)." where id={$hid}");
		$copname=$row['title'];
		$addr=$row['address'];
		$weid=$_W['weid'];
		if(!empty($id))
		{
			$item = pdo_fetch("SELECT * FROM ".tablename($this->zwlist)." WHERE weid = :weid and id=:id", array(':weid' => $weid,':id'=>$id));
			if(empty($item))
			{
				message('抱歉,您编辑的职位不存在');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['title'])) {
				message('请输入招聘岗位名称！');
			}
			$data = array(
					'hid'=>$hid,
					'title'=>$_GPC['title'],
					'copname'=>$copname,
					'zptype'=>$_GPC['zptype'],
					'contactperson'=>$_GPC['contactperson'],
					'addr'=>$_GPC['addr'],
					'contacttel'=>$_GPC['contacttel'],
					'weid' =>$weid,
					'number'=> $_GPC['number'],
					'info'=> htmlspecialchars_decode($_GPC['info']),
					'displayorder'=>$_GPC['displayorder'],
					'createtime'=>TIMESTAMP,
			);
	
			if (empty($id))
			{
				pdo_insert($this->zwlist, $data);
			}
			else
			{
				pdo_update($this->zwlist, $data, array('id' => $id));
			}
			message('招聘岗位更新成功！', $this->createWebUrl('wzplist', array('hid'=>$hid)), 'success');
		}
		include $this->template('wzpadd');
	}
	
	public function doWebWzpadmin() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$hid=$_GPC['id'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		$count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->zworder)." WHERE hid = '{$id}'", array(':weid' => $row['weid']));
		$ok_count  = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->zworder)." WHERE hid = '{$id}' AND order_status = 1",  array(':weid'=>$_W['weid']));
		$lost_count  = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->zworder)." WHERE hid = '{$id}' AND order_status = 2",  array(':weid'=>$_W['weid']));
		$no_count  = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->zworder)." WHERE hid = '{$id}' AND order_status = 3",  array(':weid'=>$_W['weid']));
		
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->zworder)." WHERE weid = '{$weid}' AND hid = '{$id}'$condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->zworder) . " WHERE weid = '{$weid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		
		if (checksubmit('submit')) {
			$data = array(
					'order_status'=>$_GPC['status'],
			);
		pdo_update($this->zworder, $data);		
			}

		
		include $this->template('wzpadmin');
	}
	
	
	public function doMobileShowindex() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$item = pdo_fetch("SELECT * FROM ".tablename($this->indextable)."  WHERE weid = :weid and id=:id", array(':weid' => $weid,':id'=>$id));
		include $this->template('index');
	}
	
	
	public function doWebSetread()
	{
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$hid=$_GPC['hid'];
		pdo_update($this->zworder,array('order_status'=>1),array('id'=>$id));
		message('设置已阅成功！',$this->createWebUrl('wzpadmin', array('id'=>$hid)), 'success');
		
	}
	
	public function doMobileShowlist() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$hid=$_GPC['hid'];
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->zwlist)." WHERE weid = '{$weid}' and hid = '{$hid}' $condition ORDER BY displayorder DESC");
		if (empty($list)) {
			message('该公司暂无招聘信息');
		}

		$zlist= pdo_fetchall("SELECT * FROM ".tablename($this->zwlist)." WHERE weid = :weid  order by displayorder desc", array(':weid' => $weid));
		if (checksubmit('submit')) {

			if (empty($_GPC['name'])) {
				message('请输入您的姓名！');
			}
			$data = array(
					'id'=>$_GPC['id'],
					'hid'=>$_GPC['hid'],
					'title'=>$_GPC['yptitle'],
					'weid' => $_W['weid'],
					'tel'=> $_GPC['ypphone'],
					'people'=> $_GPC['ypname'],
					'createtime' => TIMESTAMP,
			);
	
			if (empty($id))
			{
				pdo_insert($this->zworder, $data);
			}
			else
			{
				pdo_update($this->zworder, $data, array('id' => $id));
			}
			message('招聘岗位更新成功！', $this->createMobileUrl('showindex', array('weid' => $_W['weid'])), 'success');
		}
		include $this->template('list');
	}
	
	
	public function doMobileShowdetail() {
		global $_GPC;
		$id=$_GPC['id'];
		$item = pdo_fetch("SELECT * FROM ".tablename($this->zwlist)." WHERE id={$id} ");
		if (empty($item)) {
			message('出错，找不到信息！');
		}
		pdo_update($this->zwlist,array('hitnumber'=>$item['hitnumber']+1),array('id'=>$id));
		
		include $this->template('zwdetail');
	}
	
	
	public function doMobileShowallzwlist() {
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$hid=$_GPC['hid'];

		$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->zwlist)." WHERE weid = '{$weid}' ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

		include $this->template('zwlist');
	}
	
	public function doMobileAjaxgetzwlist() {
		global $_GPC, $_W;
		$nowpage=$_GPC['pages'];
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$hid=$_GPC['hid'];
	
		$pindex = max(2, intval($nowpage));
		$psize = 10;
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->zwlist)." WHERE weid = '{$weid}' ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		
		$info=array();
		foreach ($list as $item)
		{
			
			$row=array(
					'id'=>$item['id'],
					'company'=>$item['copname'],
					'position'=>$item['title'],
					'number'=>$item['number'],
					'pubdate'=>date('Y-m-d H:i:s',$item['createtime']),				
			);
			$info[]=$row;			
		}
		$result=array(
			'success'=>1,
			'list'=>$info,	
		);
		echo json_encode($result);
	}
	
	//*1:insert ok ;2:update ok
	public function doMobileAjaxypsubmit() {
	
		global $_GPC, $_W;
		$id=$_GPC['id'];
		$weid=$_W['weid'];
		$hid=$_GPC['hid'];
		$result="";
		$data=array(
				'hid'=>$_GPC['hid'],
				'title'=>$_GPC['yptitle'],
				'order_status'=>2,
				'people'=>$_GPC['ypname'],
				'tel'=>$_GPC['tel'],
				'sex'=>$_GPC['sex'],
				'older'=>$_GPC['older'],
				'experience'=>$_GPC['experience'],
				'weid' => $weid,
				'createtime' => TIMESTAMP,
		);
		
		if(pdo_insert($this->zworder,$data))
		{
			$copinfo=pdo_fetch("SELECT * FROM ".tablename($this->indextable)." WHERE id = '{$data['hid']}'");
			//发送邮件
			if (!empty($copinfo['email'])) {
				
					$body .= "应聘企业 : {$copinfo['title']} <br />";
					$body .= "应聘岗位 : {$data['title']} <br />";
					$body .= "姓名: {$data['people']} <br />";
					$body .= "电话: {$data['tel']} <br />";
					$body .= "性别: {$data['tel']} <br />";
					$body .= "个人履历: {$data['experience']} <br />";

				ihttp_email($copinfo['email'], '您有一封应聘'.$copinfo['title'].$data['title'].'岗位的简历请查收', $body);
			}			
			
			$result="您的应聘信息提交成功！";
		}
		else

		{
			$result="您的应聘信息提交失败！";
		}
		echo $result;
	}	
}
