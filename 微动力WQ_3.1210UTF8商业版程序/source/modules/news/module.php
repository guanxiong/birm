<?php
/**
 * 图文回复模块
 * 
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class NewsModule extends WeModule {
	public $tablename = 'news_reply';
	
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		$result = pdo_fetchall("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `parentid` ASC, `id` ASC", array(':rid' => $rid));	
		$result = istripslashes($result);
		$reply = array();
		if (!empty($result)) {
			foreach ($result as $index => $row) {
				if (empty($row['parentid'])) {
					$reply[$row['id']] = $row;
				} else {
					$reply[$row['parentid']]['children'][] = $row;
				}
			}
		}
		include $this->template('display');
	}
	
	public function fieldsFormValidate($rid = 0) {
		return true;	
	}
	
	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		if (!empty($_GPC['news-title'])) {
			foreach ($_GPC['news-title'] as $groupid => $items) {
				if (empty($items)) {
					continue;
				}
				foreach ($items as $itemid => $row) {
					if (empty($row)) {
						continue;
					}
					$update = array(
						'title' => $_GPC['news-title'][$groupid][$itemid],
						'description' => $_GPC['news-description'][$groupid][$itemid],
						'thumb' => $_GPC['news-picture-old'][$groupid][$itemid],
						'content' => htmlspecialchars_decode($_GPC['news-content'][$groupid][$itemid]),
						'url' => $_GPC['news-url'][$groupid][$itemid],
					);
					if (!empty($_GPC['news-picture'][$groupid][$itemid])) {
						$update['thumb'] = $_GPC['news-picture'][$groupid][$itemid];
						file_delete($_GPC['news-picture-old'][$groupid][$itemid]);
					}
					pdo_update($this->tablename, $update, array('id' => $itemid));
					//处理新增子项
					if (!empty($_GPC['news-title-new'][$groupid])) {
						foreach ($_GPC['news-title-new'][$groupid] as $index => $title) {
							if (empty($title)) {
								continue;
							}
							unset($_GPC['news-title-new'][$groupid]);
							$insert = array(
								'rid' => $rid,
								'parentid' => $itemid,
								'title' => $title,
								'description' => $_GPC['news-description-new'][$groupid][$index],
								'thumb' => $_GPC['news-picture-new'][$groupid][$index],
								'content' => htmlspecialchars_decode($_GPC['news-content-new'][$groupid][$index]),
								'url' => $_GPC['news-url-new'][$groupid][$index],
							);
							pdo_insert($this->tablename, $insert);
						}
					}
				}
			}
		}
		//处理添加
		if (!empty($_GPC['news-title-new'])) {
			foreach ($_GPC['news-title-new'] as $itemid => $titles) {
				if (!empty($titles)) {
					$parentid = 0;
					foreach ($titles as $index => $title) {
						if (empty($title)) {
							continue;
						}
						$insert = array(
							'rid' => $rid,
							'parentid' => $parentid,
							'title' => $title,
							'description' => $_GPC['news-description-new'][$itemid][$index],
							'thumb' => $_GPC['news-picture-new'][$itemid][$index],
							'content' => htmlspecialchars_decode($_GPC['news-content-new'][$itemid][$index]),
							'url' => $_GPC['news-url-new'][$itemid][$index],
						);
						pdo_insert($this->tablename, $insert);
						if (empty($parentid)) {
							$parentid = pdo_insertid();
						}
					}
				}
			}
		}
		return true;	
	}
	
	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id, thumb FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['thumb']);
				$deleteid[] = $row['id'];		
			}
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");  
		return true;
	}
	
	public function doFormDisplay() {
		global $_W, $_GPC;
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$result['content']['id'] = $GLOBALS['id'] = 'add-row-news-'.$_W['timestamp'];
		$result['content']['html'] = template('modules/news/'.$_GPC['tpl'].'_form_display', TEMPLATE_FETCH);
		exit(json_encode($result));
	}
	
	public function doDelete() {
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, parentid, rid, thumb FROM " . tablename($this->tablename) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，回复不存在或是已经被删除！', '', 'error');
		}
		if (pdo_delete($this->tablename, array('id' => $id))) {
			file_delete($row['thumb']);
			if ($row['parentid'] == 0) {
				$list = pdo_fetchall("SELECT thumb FROM " . tablename($this->tablename) . " WHERE `parentid`=:parentid", array(':parentid' => $row['id']));
				if (!empty($list)) {
					foreach ($list as $thumb) {
						file_delete($thumb['thumb']);
					}
				}
				pdo_delete($this->tablename, array('parentid' => $row['id']));
			}
		}
		message('删除回复成功', '', 'success');
	}
	
	public function doDeleteImage() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, thumb FROM " . tablename($this->tablename) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，回复不存在或是已经被删除！', '', 'error');
		}
		if (pdo_update($this->tablename, array('thumb' => ''), array('id' => $id))) {
			file_delete($row['thumb']);
		}
		message('删除图片成功！', '', 'success');
	}
	
	public function doGetResource() {
		global $_GPC, $_W;
		$module = $_GPC['channel'];
		$keyword = $_GPC['keyword'];
		if (empty($keyword)) {
			return '';
		}
		if ($module == 'article') {
			$list = pdo_fetchall("SELECT * FROM ".tablename('article')." WHERE title LIKE '%{$keyword}%' AND weid = '{$_W['weid']}'");
			if (!empty($list)) {
				foreach ($list as $row) {
					$result[$row['id']] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => cutstr(strip_tags($row['content']), 200),
						'picurl' => $row['thumb'],
						'url' => create_url('mobile/channel', array('name' => 'detail', 'id' => $row['id'], 'weid' => $_W['weid'])),
					);
				}
			}
		} elseif ($module == 'album') {
			$list = pdo_fetchall("SELECT * FROM ".tablename('album')." WHERE title LIKE '%{$keyword}%' AND weid = '{$_W['weid']}'");
			if (!empty($list)) {
				foreach ($list as $row) {
					$result[$row['id']] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => cutstr(strip_tags($row['content']), 200),
						'picurl' => $row['thumb'],
						'url' => create_url('mobile/channel', array('name' => 'photo', 'id' => $row['id'], 'weid' => $_W['weid'])),
					);
				}
			}
		} elseif ($module == 'business') {
			$list = pdo_fetchall("SELECT * FROM ".tablename('business')." WHERE title LIKE '%{$keyword}%' AND weid = '{$_W['weid']}'");
			if (!empty($list)) {
				foreach ($list as $row) {
					$result[$row['id']] = array(
						'id' => $row['id'],
						'title' => $row['title'],
						'description' => cutstr(strip_tags($row['content']), 200),
						'picurl' => $row['thumb'],
						'url' => create_url('mobile/channel', array('name' => 'business', 'id' => $row['id'], 'weid' => $_W['weid'])),
					);
				}
			}
		}
		include $this->template('resource');
	}
}
