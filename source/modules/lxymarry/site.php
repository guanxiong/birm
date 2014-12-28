<?php
/**
 * 微喜帖
 *
 * @author 大路货
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class LxymarryModuleSite extends WeModuleSite {
	public $headtable='lxy_marry_list';
	public $listtable='lxy_marry_info';
	public $reply_table='lxy_marry_reply';
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
				'sendtitle' => $_GPC['sendtitle'],
				'senddescription' => $_GPC['senddescription'],
			);
			//上传图片
			if (!empty($_GPC['art_pic'])) {
				file_delete($_GPC['art_pic_old']);
				$data['art_pic'] = $_GPC['art_pic'];
			}

			if (!empty($_GPC['donghua_pic'])) {
				file_delete($_GPC['donghua_pic_old']);
				$data['donghua_pic'] = $_GPC['donghua_pic'];
			}

			if (!empty($_GPC['suolue_pic'])) {
				file_delete($_GPC['suolue_pic_old']);
				$data['suolue_pic'] = $_GPC['suolue_pic'];
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
			//-o

			if (empty($id))
			{
				pdo_insert($this->headtable, $data);
			} else {
				unset($data['createtime']);
				pdo_update($this->headtable, $data, array('id' => $id));
			}
			message('喜帖信息更新成功！', create_url('site/module/manager', array('name' => 'lxymarry')), 'success');

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
		$fromuser = $_W['fans']['from_user'];

		$inputinfo = pdo_fetch("SELECT name,tel FROM ".tablename($this->listtable)." WHERE sid = :sid and fromuser=:fromuser", array(':sid' => $id,':fromuser'=>$fromuser));
		if(!$inputinfo) {
			$reginfo=fans_search($fromuser,array('realname','mobile'));
		} else {
			$reginfo=array();
			$reginfo['realname']=$inputinfo['name'];
			$reginfo['mobile']=$inputinfo['tel'];
		}

		$item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id", array(':id' => $id));
		if(empty($item)) {
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
			if(pdo_update($this->listtable,$data,array('id'=>$rs)))
			{
				if($data['type']==1)
				{
					$result='赴宴信息更新成功:['.$data['name'].',手机:'.$data['tel'].',参加人数'.$data['rs'].'人]';
				}
				else
				{
					$result='祝福信息更新成功';
				}
			}
		}
		echo $result;
	}

	public  function  doMobileAjaxdelete()
	{
		global $_GPC;
		$delurl = $_GPC['pic'];
		if(file_delete($delurl))
		{
			echo 1;
		}
		else
		{echo 0;
		}
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
				$img_url='./source/modules/lxymarry/template/img/art_pic.png';
				break;
			case 2:
				$img_url='./source/modules/lxymarry/template/img/open_pic.jpg';
				break;
			case 3:
				$img_url='source/modules/lxymarry/template/img/open_pic.jpg';
				break;
			case 4:
				$img_url='./source/modules/lxymarry/template/img/YouGotMe.mp3';
				break;
			default:
				$img_url='./source/modules/lxymarry/template/img/art_pic.png';
		}
		return $img_url;



	}

}
