<?php
/**
 * 微喜帖
 *
 */
defined('IN_IA') or exit('Access Denied');

class bm_marryModuleSite extends WeModuleSite {
	public $headtable='bm_marry_list';
	public $listtable='bm_marry_info';
	public $reply_table='bm_marry_reply';
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}
   //喜帖管理
	public function doWebAdd() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id", array(':id' => $id));
			if (empty($item)) {
				message('抱歉，喜帖不存在或是已经删除！', '', 'error');
			}
			$hslists=unserialize($item['hs_pic']);
		}
		
		
		if (checksubmit('submit')) {
			if (empty($_GPC['title'])) {
				message('请输入喜帖标题！');
			}
			$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'xl_name' => $_GPC['xl_name'],
					'xn_name' => $_GPC['xn_name'],
					'is_front' => $_GPC['is_front'],
					'tel' => $_GPC['tel'],
					'hy_time' => $_GPC['hy_time'],
					'hy_addr' => $_GPC['hy_addr'],
					'jw_addr' => $_GPC['jw_addr'],
					'lng' => $_GPC['lng'],
					'lat' => $_GPC['lat'],
					'video' =>  $_GPC['video'],					
					'music' => $_GPC['music'],
					'pwd' => $_GPC['pwd'],
					'word' =>  htmlspecialchars_decode($_GPC['word']),
					'province' => $_GPC['resideprovince'],
					'city' => $_GPC['residecity'],
					'dist' => $_GPC['residedist'],					
					'createtime' => TIMESTAMP,
					'traffic' => $_GPC['traffic'],					
			);
			//上传图片
			if (!empty($_FILES['art_pic']['tmp_name'])) {
				file_delete($_GPC['art_pic_old']);
				$upload = file_upload($_FILES['art_pic']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['art_pic'] = $upload['path'];
			}
			//上传图片
			if (!empty($_FILES['first_pic']['tmp_name'])) {
				file_delete($_GPC['first_pic_old']);
				$upload = file_upload($_FILES['first_pic']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['first_pic'] = $upload['path'];
			}
			//上传图片
			if (!empty($_FILES['donghua_pic']['tmp_name'])) {
				file_delete($_GPC['donghua_pic_old']);
				$upload = file_upload($_FILES['donghua_pic']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['donghua_pic'] = $upload['path'];
			}
			
			if (!empty($_FILES['suolue_pic']['tmp_name'])) {
				file_delete($_GPC['suolue_pic_old']);
				$upload = file_upload($_FILES['suolue_pic']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['suolue_pic'] = $upload['path'];
			}
			

			$cur_index=0;
			if (!empty($_GPC['attachment-new'])) {
				foreach ($_GPC['attachment-new'] as $index => $row) {
					if (empty($row)) {
						continue;
					}
					$hsdata[$index] = array(
									'description' => $_GPC['description-new'][$index],
									'attachment' => $_GPC['attachment-new'][$index],
							);
				}
				$cur_index=$index+1;
			}
			if (!empty($_GPC['attachment']))
			{
				foreach ($_GPC['attachment'] as $index => $row) {
					if (empty($row)) {
						continue;
					}
					$hsdata[$cur_index+$index] = array(
							'description' => $_GPC['description'][$index],
							'attachment' => $_GPC['attachment'][$index],
					);
				}
			}
			$data['hs_pic']=serialize($hsdata);
			
			if (empty($id))
			 {
				pdo_insert($this->headtable, $data);
			} else {
				unset($data['createtime']);
				pdo_update($this->headtable, $data, array('id' => $id));
			}
			message('喜帖信息更新成功！', create_url('site/module/manager', array('name' => 'bm_marry')), 'success');
				
		}
		include $this->template('add');
	}
	
	public function doWebManager() {
		global $_W,$_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND (xl_name LIKE '%{$_GPC['keyword']}%' OR xn_name LIKE '%{$_GPC['keyword']}%')";
		}
		$sql="SELECT * FROM ".tablename($this->headtable)." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;

		$list = pdo_fetchall($sql);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->headtable) . " WHERE weid = '{$_W['weid']}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('manager');
	}
	
	public function doWebInfolist() {
		global $_W,$_GPC;
		$sid=$_GPC['sid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND name LIKE '%{$_GPC['keyword']}%'";
		}
		$sql="SELECT * FROM ".tablename($this->listtable)." WHERE weid = '{$_W['weid']}' and sid=$sid and type=1  $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
	
		$list = pdo_fetchall($sql);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->listtable) . " WHERE weid = '{$_W['weid']}' and sid=$sid and type=1  $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('infolist');
	}
	
	public function doMobileDetail() {
		global $_GPC, $_W;
		$id = $_GPC['id'];
		$fromuser = authcode(base64_decode($_GPC['from_user']), 'DECODE');
		
		$inputinfo = pdo_fetch("SELECT name,tel FROM ".tablename($this->listtable)." WHERE sid = :sid and fromuser=:fromuser", array(':sid' => $id,':fromuser'=>$fromuser));
		/*if(!$inputinfo)
		{
			$reginfo=fans_search($fromuser,array('realname','mobile'));
		}
		else 
		{
			$reginfo=array();
			$reginfo['realname']=$inputinfo['name'];
			$reginfo['mobile']=$inputinfo['tel'];
		}
		*/
		$item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id", array(':id' => $id));
		if(empty($item))
		{
			message('该喜帖已经删除','','error');
		}
		
		$hslists=unserialize($item['hs_pic']);
		include $this->template('detail');
	}
	
	public function doMobileInputpwd()
	{
		global $_GPC,$_W;
		$type=intval( $_GPC['type']);
		$id=intval($_GPC['id']);
		include $this->template('inputpwd');
	}

	public function doMobileChangepwd()
	{
		global $_GPC,$_W;
		$type=intval( $_GPC['type']);
		$id=intval($_GPC['id']);
		$op=intval($_GPC['op']);		
		$ipwd=$_GPC['pwd'];
		$pwd1=$_GPC['pwd1'];
		$pwd2=$_GPC['pwd2'];
		if ($op == 'pwd' && $pwd1<>'' && $pwd2<>'') {
			//print_r($ipwd);print_r('/');print_r($pwd1);print_r('/');print_r($pwd2);print_r('/');print_r($id);
			$pwd = pdo_fetchcolumn('SELECT pwd FROM ' . tablename($this->headtable) . " WHERE id ={$id}");			
			if($pwd==$ipwd)	{		
				if ($pwd1==$pwd2) {
					pdo_update($this->headtable, array('pwd' => $pwd1) , array('id' => $id));
					message('密码已成功修改！', create_url('mobile/module/detail', array('name' => 'bm_marry','id' => $id,'weid' => $_W['weid'])), 'success');									
				} else {
					$msg='输入的两遍新密码不符！请重新输入';
					include $this->template('inputpwd');
				}
			} else {
				$msg='输入密码错误！请确认大小写是否正确';
				include $this->template('inputpwd');
			}			
		}
		include $this->template('changepwd');
	}
	
	public function doMobileChkpwd()
	{
		global $_GPC,$_W;
		$type=intval( $_GPC['type']);
		$id=intval($_GPC['id']);
		$ipwd=$_GPC['pwd'];
			$pwd = pdo_fetchcolumn('SELECT pwd FROM ' . tablename($this->headtable) . " WHERE id ={$id} ");
			if($pwd==$ipwd)
			{
				$res=pdo_fetchall("SELECT * FROM ".tablename($this->listtable)." WHERE sid = '{$id}' and type='{$type}'  ");
				
				//赴宴
				if ($type==1)
				{
					$td_name='人数';					
				}
				else 
				{
					$td_name='祝福语';						
				}

				include $this->template('infolist');
			}
			else 
			{
				$msg='输入密码错误！请确认大小写是否正确';
				include $this->template('inputpwd');
			}

	}
	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];		
		$sql = 'SELECT * FROM ' . tablename($this->headtable) . ' WHERE `weid`=:weid AND `title` LIKE :title';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['title'] = $row['title'];
			$r['description'] = $row['word'];
			$r['thumb'] = $row['art_pic'];
			$r['mid'] = $row['id'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}
	
	public function doWebInfobless() {
		global $_W,$_GPC;
		$sid=$_GPC['sid'];
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND name LIKE '%{$_GPC['keyword']}%'";
		}
		$sql="SELECT * FROM ".tablename($this->listtable)." WHERE weid = '{$_W['weid']}' and sid=$sid and type=2  $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
	
		$list = pdo_fetchall($sql);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->listtable) . " WHERE weid = '{$_W['weid']}' and sid=$sid and type=2  $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('infobless');
	}
	
	//*1:insert ok ;2:update ok
	public function doMobileAjaxsubmit() {
		
		global $_GPC, $_W;

		$data=array(
				'fromuser'=>$_GPC['fromuser'],
				'sid'=>$_GPC['sid'],
				'weid'=>$_W['weid'],
				'name'=>$_GPC['un'],
				'tel'=>$_GPC['tel'],
				'type'=>$_GPC['type'],
				'ctime'=>date('Y-m-d H:i:s', time()),
				);
		if(empty($data['fromuser']))
		{/*
			$sql='SELECT b.name,b.account FROM ' . tablename($this->headtable) .'  a left join '.tablename('wechats')." b on a.weid=b.weid   WHERE a.id = '{$data['sid']}' ";
			$rs=pdo_fetch($sql)	;
			$wechatname=$rs['name'];
			$wechataccount=$rs['account'];
			$sql='SELECT b.content FROM ' . tablename($this->reply_table) .'  a left join '.tablename('rule_keyword')." b on a.rid=b.rid  WHERE a.marryid = '{$data['sid']}' order by type limit 1 ";
			$rpkeyword=pdo_fetchcolumn($sql)	;

			if(!empty($wechatname)||!empty($rpkeyword))
			{
				echo "登记信息请关注公众号：{$wechatname} ID: {$wechataccount} 发送关键字:'{$rpkeyword}'收到回复进入后登记！";
				die();
			}
			else 
			{
				echo '您访问的喜帖异常,请联系公众号技术人员！';
				die();
			}
			*/
		}
		if($data['type']==1)
		{
			$data['rs']=$_GPC['rs'];			
		}
		else 
		{
			$data['zhufu']=$_GPC['zhufu'];			
		}
		$rs=pdo_fetchcolumn('SELECT id FROM ' . tablename($this->listtable) . " WHERE fromuser = '{$data['fromuser']}' and sid={$data['sid']} and type={$data['type']}");
		
		$result='更新失败';//error
		$result=$rs;
		if(empty($rs))			
		{
			if(pdo_insert($this->listtable,$data))
			{
				if($data['type']==1)
				{
				$result='赴宴信息提交成功:['.$data['name'].',手机:'.$data['tel'].',参加人数'.$data['rs'].'人]';
				}
				else 
				{
				$result='祝福信息提交成功';
				}
			}
			
		}
		else 
		{
			if(pdo_insert($this->listtable,$data))
			{
				if($data['type']==1)
				{
				$result='赴宴信息提交成功:['.$data['name'].',手机:'.$data['tel'].',参加人数'.$data['rs'].'人]';
				}
				else 
				{
				$result='祝福信息提交成功';
				}
			}		
		
			/*if(pdo_update($this->listtable,$data,array('id'=>$rs)))
			{
				if($data['type']==1)
				{
				$result='赴宴信息更新成功:['.$data['name'].',手机:'.$data['tel'].',参加人数'.$data['rs'].'人]';
				}
				else 
				{
				$result='祝福信息更新成功';
				}
			}*/
		}
		echo $result;
	}

	public  function  doMobileAjaxdelete()
	{
		global $_GPC;
		$delurl = $_GPC['pic'];
		if(file_delete($delurl))
		{echo 1;}
		else 
		{echo 0;}
	}
	
	public function doWebDelete() {
		global $_GPC,$_W;
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id and weid=:weid" , array(':id' => $id,':weid'=>$_W['weid']));
		if (empty($item)) {
			message('抱歉，该喜帖不存在或是已经删除！', '', 'error');
		}
		if (!empty($item['art_pic'])) {
			file_delete($item['art_pic']);
		}
		if (!empty($item['suolue_pic'])) {
			file_delete($item['suolue_pic']);
		}
		if (!empty($item['donghua_pic'])) {
			file_delete($item['donghua_pic']);
		}
		
		$hspiclist= unserialize($item['hs_pic']);
		foreach ($hspiclist as &$row) {
			file_delete($row['attachment']);
		}
		pdo_delete($this->listtable,array('sid'=>$item['id']));
		pdo_delete($this->headtable, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}
	
	public function geturl($type=1)
	{
		switch ($type)
		{
			case 1:
				$img_url='./source/modules/bm_marry/template/img/art_pic.png';
				break;
			case 2:
				$img_url='./source/modules/bm_marry/template/img/open_pic.jpg';
				break;
			case 3:
				$img_url='source/modules/bm_marry/template/img/open_pic.jpg';
				break;
			case 4:
				$img_url='./source/modules/bm_marry/template/img/YouGotMe.mp3';
				break;
			default:
				$img_url='./source/modules/bm_marry/template/img/art_pic.png';
		}
		return $img_url;	
				

			
	}

}
