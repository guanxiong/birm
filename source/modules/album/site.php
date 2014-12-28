<?php
/**
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

class AlbumModuleSite extends WeModuleSite {
    public function doMobileDetailMore(){
        global $_GPC, $_W;
		$id = intval($_GPC['id']);
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $list = pdo_fetchall("SELECT * FROM ".tablename('album_photo')." WHERE albumid = :albumid ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':albumid' => $id));
        include $this->template('detail_more');
    }
	public function doMobileDetail() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$album = pdo_fetch("SELECT * FROM ".tablename('album')." WHERE id = :id", array(':id' => $id));
		if (empty($album)) {
			message('相册不存在或是已经被删除！');
		}
		$pindex = max(1, intval($_GPC['page']));
        $psize = 10;
		$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('album_photo')." WHERE albumid = :albumid ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, array(':albumid' => $album['id']));
		$url = $this->createMobileUrl('detail', array('id' => $id));
		//360全景
		if($album['type'] == 1 && $_GPC['gettype'] == 'xml') {
			$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('album_photo')." WHERE albumid = :albumid ORDER BY displayorder ASC", array(':albumid' => $album['id']));
			header("Content-type: text/xml");
			$xml =  '<?xml version="1.0" encoding="UTF-8"?>
			<panorama id="" hideabout="1">
				<view fovmode="0" pannorth="0">
					<start pan="5.5" fov="80" tilt="1.5"/>
					<min pan="0" fov="80" tilt="-90"/>
					<max pan="360" fov="80" tilt="90"/>
				</view>
				<userdata title="" datetime="2013:05:23 21:01:02" description="" copyright="" tags="" author="" source="" comment="" info="" longitude="" latitude=""/>
				<hotspots width="180" height="20" wordwrap="1">
					<label width="180" backgroundalpha="1" enabled="1" height="20" backgroundcolor="0xffffff" bordercolor="0x000000" border="1" textcolor="0x000000" background="1" borderalpha="1" borderradius="1" wordwrap="1" textalpha="1"/>
					<polystyle mode="0" backgroundalpha="0.2509803921568627" backgroundcolor="0x0000ff" bordercolor="0x0000ff" borderalpha="1"/>
				</hotspots>
				<media/>
				<input tilesize="700" tilescale="1.014285714285714" tile0url="'.$_W['attachurl'] . $result['list']['0']['attachment'].'" tile1url="'.$_W['attachurl'] . $result['list']['1']['attachment'].'" tile2url="'.$_W['attachurl'] . $result['list']['2']['attachment'].'" tile3url="'.$_W['attachurl'] . $result['list']['3']['attachment'].'" tile4url="'.$_W['attachurl'] . $result['list']['4']['attachment'].'" tile5url="'.$_W['attachurl'] . $result['list']['5']['attachment'].'"/>
				<autorotate speed="0.200" nodedelay="0.00" startloaded="1" returntohorizon="0.000" delay="5.00"/>
				<control simulatemass="1" lockedmouse="0" lockedkeyboard="0" dblclickfullscreen="0" invertwheel="0" lockedwheel="0" invertcontrol="1" speedwheel="1" sensitivity="8"/>
			</panorama>';
			return $xml;
		}
		include $this->template('detail');
	}

	public function doWebList() {
		global $_W, $_GPC;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'display';

		if ($foo == 'create') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('album')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，相册不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入相册名称！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'content' => $_GPC['content'],
					'displayorder' => intval($_GPC['displayorder']),
					'isview' => intval($_GPC['isview']),
					'type' => intval($_GPC['type']),
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
					pdo_insert('album', $data);
				} else {
					unset($data['createtime']);
					pdo_update('album', $data, array('id' => $id));
				}
				message('相册更新成功！', $this->createWebUrl('list', array('foo' => 'display')), 'success');
			}
			include $this->template('album');
		} elseif ($foo == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('album')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('album') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
			if (!empty($list)) {
				foreach ($list as &$row) {
					$row['total'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('album_photo')." WHERE albumid = :albumid", array(':albumid' => $row['id']));
				}
			}
			include $this->template('album');
		} elseif ($foo == 'photo') {
			$id = intval($_GPC['albumid']);
			$album = pdo_fetch("SELECT id, type FROM ".tablename('album')." WHERE id = :id", array(':id' => $id));
			if (empty($album)) {
				message('相册不存在或是已经被删除！');
			}
			if (checksubmit('submit')) {
				if (!empty($_GPC['attachment-new'])) {
					foreach ($_GPC['attachment-new'] as $index => $row) {
						if (empty($row)) {
							continue;
						}
						$data = array(
							'weid' => $_W['weid'],
							'albumid' => intval($_GPC['albumid']),
							'title' => $_GPC['title-new'][$index],
							'description' => $_GPC['description-new'][$index],
							'attachment' => $_GPC['attachment-new'][$index],
							'displayorder' => $_GPC['displayorder-new'][$index],
						);
						pdo_insert('album_photo', $data);
					}
				}
				if (!empty($_GPC['attachment'])) {
					foreach ($_GPC['attachment'] as $index => $row) {
						if (empty($row)) {
							continue;
						}
						$data = array(
							'weid' => $_W['weid'],
							'albumid' => intval($_GPC['albumid']),
							'title' => $_GPC['title'][$index],
							'description' => $_GPC['description'][$index],
							'attachment' => $_GPC['attachment'][$index],
							'displayorder' => $_GPC['displayorder'][$index],
						);
						pdo_update('album_photo', $data, array('id' => $index));
					}
				}
				message('相册更新成功！', $this->createWebUrl('list', array('foo' => 'photo', 'albumid' => $album['id'])));
			}
			if($album['type'] == 0) {
				$photos = pdo_fetchall("SELECT * FROM ".tablename('album_photo')." WHERE albumid = :albumid ORDER BY displayorder DESC", array(':albumid' => $album['id']));
			} else {
				$photos = pdo_fetchall("SELECT * FROM ".tablename('album_photo')." WHERE albumid = :albumid ORDER BY displayorder ASC", array(':albumid' => $album['id']));
			}
			include $this->template('album');
		} elseif ($foo == 'delete') {
			$type = $_GPC['type'];
			$id = intval($_GPC['id']);
			if ($type == 'photo') {
				if (!empty($id)) {
					$item = pdo_fetch("SELECT * FROM ".tablename('album_photo')." WHERE id = :id", array(':id' => $id));
					if (empty($item)) {
						message('图片不存在或是已经被删除！');
					}
					pdo_delete('album_photo', array('id' => $item['id']));
				} else {
					$item['attachment'] = $_GPC['attachment'];
				}
				file_delete($item['attachment']);
			} elseif ($type == 'album') {
				$album = pdo_fetch("SELECT id, thumb FROM ".tablename('album')." WHERE id = :id", array(':id' => $id));
				if (empty($album)) {
					message('相册不存在或是已经被删除！');
				}
				$photos = pdo_fetchall("SELECT id, attachment FROM ".tablename('album_photo')." WHERE albumid = :albumid", array(':albumid' => $id));
				if (!empty($photos)) {
					foreach ($photos as $row) {
						file_delete($row['attachment']);
					}
				}
				pdo_delete('album', array('id' => $id));
				pdo_delete('album_photo', array('albumid' => $id));
			}
			message('删除成功！', referer(), 'success');
		} elseif ($foo == 'cover') {
			$id = intval($_GPC['albumid']);
			$attachment = $_GPC['thumb'];
			if (empty($attachment)) {
				message('抱歉，参数错误，请重试！', '', 'error');
			}
			$item = pdo_fetch("SELECT * FROM ".tablename('album')." WHERE id = :id" , array(':id' => $id));
			if (empty($item)) {
				message('抱歉，相册不存在或是已经删除！', '', 'error');
			}
			pdo_update('album', array('thumb' => $attachment), array('id' => $id));
			message('设置封面成功！', '', 'success');
		}
	}

	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('album') . ' WHERE `weid`=:weid AND `title` LIKE :title';
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
		pdo_delete('album_reply', array('id' => $id));
		message('删除成功！', referer(), 'success');
	}

	public function doMobileList() {
		global $_W, $_GPC;
		$_W['styles'] = mobile_styles();
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$result['list'] = pdo_fetchall("SELECT * FROM ".tablename('album')." WHERE weid = '{$_W['weid']}' AND isview = '1' ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize .',' .$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('album') . " WHERE weid = '{$_W['weid']}' AND isview = '1'");
		$result['pager'] = pagination($total, $pindex, $psize);
		include $this->template('list');
	}

	public function getAlbumTiles() {
		global $_W;
		$albums = pdo_fetchall("SELECT id, title FROM ".tablename('album')." WHERE isview = '1' AND weid = '{$_W['weid']}'");
		if (!empty($albums)) {
			foreach ($albums as $row) {
				$urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('detail', array('id' => $row['id'])));
			}
			return $urls;
		}
	}

}
