<?php
/**
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

class HuabaoModuleSite extends WeModuleSite {
	
	public function doMobileDetail() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$huabao = pdo_fetch("SELECT * FROM ".tablename('huabao')." WHERE id = :id", array(':id' => $id));
		if (empty($huabao)) {
			message('画报不存在或是已经被删除！');
		}
		if (!preg_match("/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $huabao['music'])){
			$huabao['music'] = $_W['attachurl'] . $huabao['music'];
		}
		$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('huabao_photo')." WHERE huabaoid = :huabaoid ORDER BY displayorder ASC", array(':huabaoid' => $huabao['id']));
		foreach ($result['list'] as &$photo) {
			$photo['items'] = pdo_fetchall("SELECT * FROM ".tablename('huabao_item')." WHERE photoid = :photoid", array(':photoid' => $photo['id']));
		}
		include $this->template('detail');
	}

	public function doWebUploadMusic() {
		global $_W;
		checklogin();
		if (empty($_FILES['imgFile']['name'])) {
			$result['message'] = '请选择要上传的音乐！';
			exit(json_encode($result));
		}

		if ($_FILES['imgFile']['error'] != 0) {
			$result['message'] = '上传失败，请重试！';
			exit(json_encode($result));
		}
		if ($file = $this->fileUpload($_FILES['imgFile'], 'music')) {
			if (!$file['success']) {
				exit(json_encode($file));
			}
			$result['url'] = $_W['config']['upload']['attachdir'] . $file['path'];
			$result['error'] = 0;
			$result['filename'] = $file['path'];
			exit(json_encode($result));
		}
	}

	private function fileUpload($file, $type) {
		global $_W;
		set_time_limit(0);
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['music']['folder'] = 'music';
		$_W['uploadsetting']['music']['extentions'] = array('mp3', 'wma', 'wav', 'amr');
		$_W['uploadsetting']['music']['limit'] = 50000;
		$result = array();
		$upload = file_upload($file, 'music');
		if (is_error($upload)) {
			message($upload['message'], '', 'ajax');
		}
		$result['url'] = $upload['url'];
		$result['error'] = 0;
		$result['filename'] = $upload['path'];
		return $result;
	}

	public function doWebList() {
		global $_W, $_GPC;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'display';

		if ($foo == 'create') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('huabao')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，画报不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('fileupload-delete')) {
				$data = array();
				$data['thumb'] = '';
				pdo_update('huabao', $data, array('id' => $id));
				message('封面删除成功！', '', 'success');
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入画报名称！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'music' => $_GPC['music'],
					'open' => $_GPC['open'],
					'ostyle' => $_GPC['ostyle'],
					'icon' => $_GPC['icon'],
					'share' => $_GPC['share'],
					'content' => $_GPC['content'],
					'displayorder' => intval($_GPC['displayorder']),
					'isloop' => intval($_GPC['isloop']),
					'isview' => intval($_GPC['isview']),
					'type' => intval($_GPC['type']),
					'createtime' => TIMESTAMP,
				);
				if ($_GPC['mset'][0]) {
					$data['mauto'] = 1;
				}else{
					$data['mauto'] = 0;
				}
				if ($_GPC['mset'][1]) {
					$data['mloop'] = 1;
				}else{
					$data['mloop'] = 0;
				}
				if (!empty($_FILES['thumb']['tmp_name'])) {
					file_delete($_GPC['thumb_old']);
					$upload = file_upload($_FILES['thumb']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['thumb'] = $upload['path'];
				}
				if (empty($id)) {
					pdo_insert('huabao', $data);
				} else {
					unset($data['createtime']);
					pdo_update('huabao', $data, array('id' => $id));
				}
				message('画报更新成功！', $this->createWebUrl('list', array('foo' => 'display')), 'success');
			}
			include $this->template('huabao');
		} elseif ($foo == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('huabao')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('huabao') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
			if (!empty($list)) {
				foreach ($list as &$row) {
					$row['total'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('huabao_photo')." WHERE huabaoid = :huabaoid", array(':huabaoid' => $row['id']));
				}
			}
			include $this->template('huabao');
		} elseif ($foo == 'photo') {
			$id = intval($_GPC['huabaoid']);
			$huabao = pdo_fetch("SELECT id, type FROM ".tablename('huabao')." WHERE id = :id", array(':id' => $id));
			if (empty($huabao)) {
				message('画报不存在或是已经被删除！');
			}
			if (checksubmit('reset')) {
			if (!empty($_GPC['item'])) {
			if (!empty($_GPC['id'])) {
			pdo_delete('huabao_item',array('id' => $_GPC['id']));
			}}
			
			}
			
			if (checksubmit('submit')) {
		if (!empty($_GPC['item'])) {
			if (!empty($_GPC['id'])) {
				$data = array(
					'weid' => $_W['weid'],
					'huabaoid' => intval($_GPC['huabaoid']),
					'photoid' => intval($_GPC['photoid']),
					'type' => $_GPC['type'],
					'item' => $_GPC['item'],
					'url' => $_GPC['url'],
					'x' => $_GPC['x'],
					'y' => $_GPC['y'],
					'animation' => $_GPC['animation'],
				);
				pdo_update('huabao_item', $data, array('id' => $_GPC['id']));
			}else{
				$data = array(
					'weid' => $_W['weid'],
					'huabaoid' => intval($_GPC['huabaoid']),
					'photoid' => intval($_GPC['photoid']),
					'type' => $_GPC['type'],
					'item' => $_GPC['item'],
					'url' => $_GPC['url'],
					'x' => $_GPC['x'],
					'y' => $_GPC['y'],
					'animation' => $_GPC['animation'],
				);
				pdo_insert('huabao_item', $data);
			}
		}
				if (!empty($_GPC['attachment-new'])) {
					foreach ($_GPC['attachment-new'] as $index => $row) {
						if (empty($row)) {
							continue;
						}
						$data = array(
							'weid' => $_W['weid'],
							'huabaoid' => intval($_GPC['huabaoid']),
							'title' => $_GPC['title-new'][$index],
							'url' => $_GPC['url-new'][$index],
							'attachment' => $_GPC['attachment-new'][$index],
							'displayorder' => $_GPC['displayorder-new'][$index],
						);
						pdo_insert('huabao_photo', $data);
					}
				}
				if (!empty($_GPC['attachment'])) {
					foreach ($_GPC['attachment'] as $index => $row) {
						if (empty($row)) {
							continue;
						}
						$data = array(
							'weid' => $_W['weid'],
							'huabaoid' => intval($_GPC['huabaoid']),
							'title' => $_GPC['title'][$index],
							'url' => $_GPC['url'][$index],
							'attachment' => $_GPC['attachment'][$index],
							'displayorder' => $_GPC['displayorder'][$index],
						);
						pdo_update('huabao_photo', $data, array('id' => $index));
					}
				}
				message('画报更新成功！', $this->createWebUrl('list', array('foo' => 'photo', 'huabaoid' => $huabao['id'])));
			}
			$photos = pdo_fetchall("SELECT * FROM ".tablename('huabao_photo')." WHERE huabaoid = :huabaoid ORDER BY displayorder ASC", array(':huabaoid' => $huabao['id']));
			foreach ($photos as &$photo1) {
				$photo1['items'] = pdo_fetchall("SELECT * FROM ".tablename('huabao_item')." WHERE photoid = :photoid", array(':photoid' => $photo1['id']));
			}
			include $this->template('huabao');
		} elseif ($foo == 'delete') {
			$type = $_GPC['type'];
			$id = intval($_GPC['id']);
			if ($type == 'photo') {
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM ".tablename('huabao_photo')." WHERE id = :id", array(':id' => $id));
					if (empty($item)) {
						message('图片不存在或是已经被删除！');
					}
					pdo_delete('huabao_photo', array('id' => $item['id']));
				} else {
					$item['attachment'] = $_GPC['attachment'];
				}
				file_delete($item['attachment']);
			} elseif ($type == 'huabao') {
				$huabao = pdo_fetch("SELECT id, thumb FROM ".tablename('huabao')." WHERE id = :id", array(':id' => $id));
				if (empty($huabao)) {
					message('画报不存在或是已经被删除！');
				}
				$photos = pdo_fetchall("SELECT id, attachment FROM ".tablename('huabao_photo')." WHERE huabaoid = :huabaoid", array(':huabaoid' => $id));
				if (!empty($photos)) {
					foreach ($photos as $row) {
						file_delete($row['attachment']);
					}
				}
				pdo_delete('huabao', array('id' => $id));
				pdo_delete('huabao_photo', array('huabaoid' => $id));
			}
			message('删除成功！', referer(), 'success');
		} elseif ($foo == 'cover') {
			$id = intval($_GPC['huabaoid']);
			$attachment = $_GPC['thumb'];
			if (empty($attachment)) {
				message('抱歉，参数错误，请重试！', '', 'error');
			}
			$item = pdo_fetch("SELECT * FROM ".tablename('huabao')." WHERE id = :id" , array(':id' => $id));
			if (empty($item)) {
				message('抱歉，画报不存在或是已经删除！', '', 'error');
			}
			pdo_update('huabao', array('thumb' => $attachment), array('id' => $id));
			message('设置封面成功！', '', 'success');
		}
	}

	public function doWebItem() {
		global $_W, $_GPC;
		$huabaoid = intval($_GPC['huabaoid']);
		$photoid = intval($_GPC['photoid']);
		$id = intval($_GPC['id']);
		$photo = pdo_fetch("SELECT * FROM ".tablename('huabao_photo')." WHERE id = :id", array(':id' => $photoid));
		if (empty($photo)) {
			message('场景不存在或是已经被删除！');
		}
		if (!empty($id)) {
			$item = pdo_fetch("SELECT * FROM ".tablename('huabao_item')." WHERE id = :id", array(':id' => $id));
		}
		include $this->template('item');
	}

	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('huabao') . ' WHERE `weid`=:weid AND `title` LIKE :title';
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
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		pdo_delete('huabao_reply', array('id' => $id));
		message('删除成功！', referer(), 'success');
	}

	public function doMobileList() {
		global $_W, $_GPC;
		$_W['styles'] = mobile_styles();
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('huabao')." WHERE weid = '{$_W['weid']}' AND isview = '1' ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize .',' .$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('huabao') . " WHERE weid = '{$_W['weid']}' AND isview = '1'");
		$result['pager'] = pagination($total, $pindex, $psize);
		include $this->template('list');
	}

	public function doMobileResearch() {
		global $_W, $_GPC;
		$reid = intval($_GPC['id']);
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `reid`=:reid';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':reid'] = $reid;
		$activity = pdo_fetch($sql, $params);
		$title = $activity['title'];
		if($activity['status'] != '1') {
			message('当前预约活动已经停止.');
		}
		if(!$activity) {
			message('非法访问.');
		}
		if ($activity['starttime'] > TIMESTAMP) {
			message('当前预约活动还未开始！');
		}
		if ($activity['endtime'] < TIMESTAMP) {
			message('当前预约活动已经结束！');
		}
		$sql = 'SELECT * FROM ' . tablename('research_fields') . ' WHERE `reid`=:reid ORDER BY `refid`';
		$params = array();
		$params[':reid'] = $reid;
		$ds = pdo_fetchall($sql, $params);
		if(!$ds) {
			message('非法访问.');
		}
		$initRange = false;
		$initCalendar = false;
		$binds = array();
		foreach($ds as &$r) {
			if($r['type'] == 'range') {
				$initRange = true;
			}
			if($r['type'] == 'calendar') {
				$initCalendar = true;
			}
			if($r['value']) {
				$r['options'] = explode(',', $r['value']);
			}
			if($r['bind']) {
				$binds[] = $r['bind'];
			}
		}
		if(!empty($_W['fans']['from_user']) && !empty($binds)) {
			$profile = fans_search($_W['fans']['from_user'], $binds);
			if($profile['gender']) {
				if($profile['gender'] == '0') $profile['gender'] = '保密';
				if($profile['gender'] == '1') $profile['gender'] = '男';
				if($profile['gender'] == '2') $profile['gender'] = '女';
			}
			foreach($ds as &$r) {
				if($profile[$r['bind']]) {
					$r['default'] = $profile[$r['bind']];
				}
			}
		}
		include $this->template('submit');
	}

	public function doWebRes() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('research') . ' WHERE `weid`=:weid AND `title` LIKE :title ORDER BY reid DESC LIMIT 0,8';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['title'] = $row['title'];
			$r['reid'] = $row['reid'];
			$r['link'] = $this->createMobileUrl('research', array('id' => $r['reid']));
			$row['entry'] = $r;
		}
		include $this->template('res');
	}

	public function getHuabaoTiles() {
		global $_W;
		$huabaos = pdo_fetchall("SELECT id, title FROM ".tablename('huabao')." WHERE isview = '1' AND weid = '{$_W['weid']}'");
		if (!empty($huabaos)) {
			foreach ($huabaos as $row) {
				$urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('detail', array('id' => $row['id'])));
			}
			return $urls;
		}
	}

}
