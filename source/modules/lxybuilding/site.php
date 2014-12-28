<?php
/**
 * 
 *
 * [WeEngine System] 更多模块请浏览：BBS.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class LxybuildingModuleSite extends WeModuleSite {
	public $headtable='lxy_building_info_head';
	public $listtable='lxy_building_info_list';
	public function getProfileTiles() {

	}

	public function getHomeTiles() {
	}

	public function doWebPost() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		if (!empty($id)) {
			$item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id", array(':id' => $id));
			if (empty($item)) {
				message('抱歉，楼盘不存在或是已经删除！', '', 'error');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['title'])) {
				message('请输入商户名称！');
			}
			$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'buildingintro' => htmlspecialchars_decode($_GPC['buildingintro']),
					'traffic' => $_GPC['traffic'],
					'projectintro' => $_GPC['projectintro'],
					'phone' => $_GPC['phone'],
					'province' => $_GPC['resideprovince'],
					'city' => $_GPC['residecity'],
					'dist' => $_GPC['residedist'],
					'address' => $_GPC['address'],
					'lng' => $_GPC['lng'],
					'lat' => $_GPC['lat'],
					'createtime' => TIMESTAMP,
			);
			if (!empty($_FILES['thumb']['tmp_name'])) {
				file_delete($_GPC['thumb_old']);
				$upload = file_upload($_FILES['thumb']);
				if (is_error($upload)) {
					message($upload['message'], '', 'error');
				}
				$data['thumb'] = $upload['path'];
			}
			if (empty($id)) {
				pdo_insert($this->headtable, $data);
			} else {
				unset($data['createtime']);
				pdo_update($this->headtable, $data, array('id' => $id));
			}
			message('商户信息更新成功！', create_url('site/module/display', array('name' => 'lxybuilding')), 'success');
				
		}
		include $this->template('post');
	}
	
	public function doWebDisplay() {
		global $_W,$_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
		}
		$sql="SELECT a.*,b.rows FROM ".tablename($this->headtable)." a left join (select hid,count(1) as rows from ".tablename($this->listtable)." group by hid) b on a.id=b.hid WHERE a.weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
	
		//$list = pdo_fetchall("SELECT * FROM ".tablename($this->headtable)." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$list = pdo_fetchall($sql);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->headtable) . " WHERE weid = '{$_W['weid']}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('display');
	}
	
	public function doWebDelete() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id" , array(':id' => $id));
		if (empty($item)) {
			message('抱歉，楼盘不存在或是已经删除！', '', 'error');
		}
		if (!empty($item['thumb'])) {
			file_delete($item['thumb']);
		}
		pdo_delete($this->headtable, array('id' => $item['id']));
		message('删除成功！', referer(), 'success');
	}
	
	
	public function doWebStylePost () {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$hid = intval($_GPC['hid']);
		$htitle=$_GPC['title'];
		//新建户型
		if (empty($id)&&!empty($hid)){
			$item=array();
			$item['title']=$htitle;
			$typelist = pdo_fetchall("SELECT distinct typename FROM ".tablename($this->listtable)." WHERE hid={$hid} ");
		}
	
		if (!empty($id)&&!empty($hid)) {
			$item = pdo_fetch("SELECT * FROM ".tablename($this->listtable)." WHERE id = :id and hid=:hid" , array(':id' => $id,':hid'=>$hid));
			$typelist = pdo_fetchall("SELECT distinct typename FROM ".tablename($this->listtable)." WHERE hid={$hid} ");
			if (empty($item)) {
				message('抱歉，户型不存在或是已经删除！', '', 'error');
			}
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['typename'])) {
				message('请输入户型类别！');
			}
			$data = array(
					'hid' => $hid,
					'typename' => $_GPC['typename'],
					'housename' => $_GPC['housename'],
					'rooms' => $_GPC['rooms'],
					'size' => $_GPC['size'],
					'photourl' => $_GPC['photourl'],
					'panourl' => $_GPC['panourl'],
					'createtime' => TIMESTAMP,
			);
			if (empty($id)) {
				pdo_insert($this->listtable, $data);
			} else {
				unset($data['createtime']);
				pdo_update($this->listtable, $data, array('id' => $id,'hid'=>$hid));
			}
			message('户型信息更新成功！', create_url('site/module/StyleList', array('name' => 'lxybuilding')), 'success');
	
		}
		include $this->template('housestyle');
	}
	
	public function doWebStyleList() {
		global $_W,$_GPC;
		$hid = intval($_GPC['hid']);
		$title = $_GPC['title'];
		if(empty($hid)){message('没有您需要查看的楼盘户型！', '', 'error');}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = '';
		if (!empty($_GPC['keyword'])) {
			$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename($this->listtable)." WHERE hid = '{$hid}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->listtable) . " WHERE hid = '{$hid}' $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('houselist');
	}
	
	
	
	public function doMobileDetail() {
		global $_GPC, $_W;
        $id = $_GPC['id'];        
        $item = pdo_fetch("SELECT * FROM ".tablename($this->headtable)." WHERE id = :id", array(':id' => $id));
		$hlist=  pdo_fetchall("SELECT distinct( typename) FROM ".tablename($this->listtable)." WHERE hid={$id} order by typename");
		include $this->template('detail');		
	}
	public function doMobilePhotoIndex() {
		global $_GPC, $_W;
		$weid=$_GPC['weid'];
		$photo= pdo_fetchall("SELECT * FROM ".tablename('album')." WHERE weid = :weid", array(':weid' => $weid));
		if(empty($photo))
		{
			message('该用户没有建立相册','','error');		
		}
		include $this->template('Photo_index');
	}
	
	public function doMobilePhotoList() {
		global $_GPC, $_W;
		$weid=$_GPC['weid'];
		$aid=$_GPC['id'];
		
		$photolist= pdo_fetchall("SELECT * FROM ".tablename('album_photo')." WHERE weid = :weid and albumid=:aid order by displayorder", array(':weid' => $_W['weid'],':aid'=>$aid));
		if(empty($photolist))
		{
			message('该相册没有上次图片','','error');
		}
		include $this->template('Photo_plist');
	}
	public function doWebstatus( $rid = 0) {
		global $_GPC;
		$rid = $_GPC['rid'];
		echo $rid;
		$insert = array(
				'status' => $_GPC['status']
		);
	
		pdo_update("lxy_building_reply",$insert,array('rid' => $rid));
		message('模块操作成功！', referer(), 'success');
	}
}
