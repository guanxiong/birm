<?php
/**
 * 上传图片
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
error_reporting(0);

$do = !empty($_GPC['do']) ? $_GPC['do'] : exit('Access Denied');
$result = array('error' => 1, 'message' => '');
if ($do == 'upload') {
	if (!empty($_FILES['imgFile']['name'])) {
		if ($_FILES['imgFile']['error'] != 0) {
			$result['message'] = '上传失败，请重试！';
			exit(json_encode($result));
		}
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = '/images/' . $_W['weid'];
		$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$file = file_upload($_FILES['imgFile'], 'image');
		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = $_W['attachurl'].$result['filename'];
		pdo_insert('attachment', array(
			'weid' => $_W['weid'],
			'uid' => $_W['uid'],
			'filename' => $_FILES['imgFile']['name'],
			'attachment' => $result['filename'],
			'type' => 1,
			'createtime' => TIMESTAMP,
		));
		exit(json_encode($result));
	} else {
		$result['message'] = '请选择要上传的图片！';
		exit(json_encode($result));
	}
} elseif ($do == 'ueupload') {
	$result = array(
		'url' => '',
		'title' => '',
		'original' => '',
		'state' => 'SUCCESS',
	);
	if (!empty($_FILES['imgFile']['name'])) {
		if ($_FILES['imgFile']['error'] != 0) {
			$result['state'] = '上传失败，请重试！';
			exit(json_encode($result));
		}
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = 'images/' . $_W['weid'];
		$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$file = file_upload($_FILES['imgFile'], 'image');
		if (is_error($file)) {
			$result['state'] = $file['message'];
			exit(json_encode($result));
		}
		$result['url'] = $file['path'];
		$result['title'] = '';
		$result['original'] = '';
		
		pdo_insert('attachment', array(
			'weid' => $_W['weid'],
			'uid' => $_W['uid'],
			'filename' => $_FILES['imgFile']['name'],
			'attachment' => $result['filename'],
			'type' => 1,
			'createtime' => TIMESTAMP,
		));
		exit(json_encode($result));
	} else {
		$result['state'] = '请选择要上传的图片！';
		exit(json_encode($result));
	}
} elseif ($do == 'delete') {
	if (empty($_GPC['filename'])) {
		$result['message'] = '请选择要删除的图片！';
		exit(json_encode($result));
	}
	file_delete($_GPC['filename']);
	$result['error'] = 0;
	exit(json_encode($result));
} elseif ($do == 'manager') {
	$dir = $_GPC['dir'] ? $_GPC['dir'] : '';
	$path = !empty($_GPC['path']) ? $_GPC['path'] : $_W['weid'] . '/';
	$order = empty($_GPC['order']) ? 'name' : strtolower($_GPC['order']);
	$rootpath = IA_ROOT . '/resource/attachment/images/';
	$exts = array('gif', 'jpg', 'jpeg', 'png', 'bmp');

	if (empty($path)) {
		$currentpath = $rootpath;
		$parentpath = '';
	} else {
		$currentpath = $rootpath . $path;
		$parentpath = preg_replace('/(.*?)[^\/]+\/$/', '$1', $path);
	}
	if (preg_match('/\.\./', $currentpath)) {
		echo 'Access is not allowed.';
		exit;
	}
	//最后一个字符不是/
	if (!preg_match('/\/$/', $currentpath)) {
		echo 'Parameter is not valid.';
		exit;
	}

	function cmp_func($a, $b) {
		global $order;
		if ($a['is_dir'] && !$b['is_dir']) {
			return -1;
		} else if (!$a['is_dir'] && $b['is_dir']) {
			return 1;
		} else {
			if ($order == 'size') {
				if ($a['filesize'] > $b['filesize']) {
					return 1;
				} else if ($a['filesize'] < $b['filesize']) {
					return -1;
				} else {
					return 0;
				}
			} else if ($order == 'type') {
				return strcmp($a['filetype'], $b['filetype']);
			} else {
				return strcmp($a['filename'], $b['filename']);
			}
		}
	}
	//遍历目录取得文件信息
	$files = array();
	if (is_dir($currentpath)) {
		if ($handle = opendir($currentpath)) {
			while (false !== ($filename = readdir($handle))) {
				if ($filename{0} == '.') continue;
				$file = $currentpath . $filename;
				if (is_dir($file)) {
					$files[] = array(
						'filename' => $filename,
						'is_dir' => true,
						'is_photo' => false,
						'has_file' => true,
						'filesize' => 0,
						'filetype' => '',
						'datetime' => date('Y-m-d H:i:s', filemtime($file)),
					);
				} else {
					$fileext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
					$files[] = array(
						'filename' => $filename,
						'is_dir' => false,
						'is_photo' => in_array($fileext, $exts),
						'has_file' => false,
						'filesize' => filesize($file),
						'filetype' => $fileext,
						'dir_path' => '',
						'datetime' => date('Y-m-d H:i:s', filemtime($file)),
					);
				}
			}
		}
	}
	usort($files, 'cmp_func');

	$result = array();
	$result['moveup_dir_path'] = $parentpath;
	$result['current_dir_path'] = $path;
	$result['current_url'] = $_W['attachurl'] . '/images/' . $path;
	$result['total_count'] = count($files);
	$result['file_list'] = $files;
	header('Content-type: application/json; charset=UTF-8');
	echo json_encode($result);

}


elseif ($do == 'uploadify') {
	if (!empty($_FILES['Filedata']['name'])) {
		if ($_FILES['Filedata']['error'] != 0) {
			$result['message'] = '上传失败，请重试！';
			exit(json_encode($result));
		}
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = 'images/' . $_W['weid'];
		$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$file = file_upload($_FILES['Filedata'], 'image');
		if (is_error($file)) {
			$result['message'] = $file['message'];
			exit(json_encode($result));
		}
		$result['url'] = $file['url'];
		$result['error'] = 0;
		$result['filename'] = $file['path'];
		$result['url'] = $_W['attachurl'].$result['filename'];
	 
		//echo $result['url'];
		$return=array(
			'result'=>'SUCCESS',
			'image'=>array(
				'id'=>0,
				'thm_url'=>$result['url'],
				'title'=>'',
				'url'=>$result['filename'],
			),
		);
		exit(json_encode($return));
	} else {
		$result['message'] = '请选择要上传的图片！';
		exit(json_encode($result));
	}
} elseif ($do == 'deleteify') {
	if (empty($_GPC['url'])) {
		$result['message'] = '请选择要删除的图片！';
		exit(json_encode($result));
	}
	file_delete($_GPC['url']);
	$result['error'] = 0;
	exit(json_encode($result));
}

?>