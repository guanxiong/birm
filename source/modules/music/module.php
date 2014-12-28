<?php
/**
 * 语音回复模块
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class MusicModule extends WeModule {
	public $tablename = 'music_reply';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if (!empty($rid)) {
			$list = pdo_fetchall("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` ASC", array(':rid' => $rid));
			$list = istripslashes($list);
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		return true;
	}

	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		if (!empty($_GPC['music-title'])) {
			foreach ($_GPC['music-title'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$update = array(
					'title' => $title,
					'description' => $_GPC['music-description'][$index],
					'url' => $_GPC['music-url'][$index],
					'hqurl' => $_GPC['music-hqurl'][$index],
				);
				if (!empty($_GPC['music-url-old'][$index]) && $_GPC['music-url'][$index] != $_GPC['music-url-old'][$index]) {
					file_delete($_GPC['music-url-old'][$index]);
				}
				pdo_update($this->tablename, $update, array('id' => $index));
			}
		}
		//处理添加
		if (!empty($_GPC['music-title-new'])) {
			foreach ($_GPC['music-title-new'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$insert = array(
					'rid' => $rid,
					'title' => $title,
					'description' => $_GPC['music-description-new'][$index],
					'url' => $_GPC['music-url-new'][$index],
					'hqurl' => $_GPC['music-hqurl-new'][$index],
				);
				pdo_insert($this->tablename, $insert);
			}
		}
		return true;
	}

	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id, url FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['url']);
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
		$result['content']['html'] = $this->template('item', TEMPLATE_FETCH);
		exit(json_encode($result));
	}

	public function doUploadMusic() {
		global $_W;
		checklogin();
		if (empty($_FILES['attachFile']['name'])) {
			$result['message'] = '请选择要上传的音乐！';
			exit(json_encode($result));
		}

		if ($_FILES['attachFile']['error'] != 0) {
			$result['message'] = '上传失败，请重试！';
			exit(json_encode($result));
		}
		if ($file = $this->fileUpload($_FILES['attachFile'], 'music')) {
			if (!$file['success']) {
				exit(json_encode($file));
			}
			$result['url'] = $_W['config']['upload']['attachdir'] . $file['path'];
			$result['error'] = 0;
			$result['filename'] = $file['path'];
			exit(json_encode($result));
		}
	}

	public function doDelete() {
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, rid, url, hqurl FROM " . tablename($this->tablename) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，回复不存在或是已经被删除！', '', 'error');
		}
		if (pdo_delete($this->tablename, array('id' => $id))) {
			if (!strexists($row['url'], 'http')) {
				file_delete($row['url']);
			}
			if (!strexists($row['hqurl'], 'http')) {
				file_delete($row['hqurl']);
			}
			message('删除回复成功', '', 'success');
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
}
