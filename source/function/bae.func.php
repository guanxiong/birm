<?php 
/**
 * BAE兼容函数
 * [WNS]Copyright (c) 2013 BIRM.CO
 */
include_once IA_ROOT . '/source/library/bcs/bcs.class.php';
/**
 * 兼容 file_upload 函数
 */
function file_uploadBAE($file, $type) {
	global $_W;
	$settings = $_W['uploadsetting'];
	$result = array('error' => 1, 'message' => '');
	if (empty($_W['config']['bae']['ak']) || empty($_W['config']['bae']['sk'])) {
		return error(-1, '请设置BAE的存储AK与SK');
	}
	$extention = pathinfo($file['name'], PATHINFO_EXTENSION);
	$result = array();
	$result['path'] = "/{$settings[$type]['folder']}/" . date('Y/m/');
	do {
		$filename = random(30) . ".{$extention}";
	} while(file_exists(IA_ROOT . $path . $filename));
	$result['path'] .= $filename;
	$result['url'] = 'http://bcs.duapp.com/'.$_W['config']['bae']['bucket'].$result['path'];
	$baiduBCS = new BaiduBCS($_W['config']['bae']['ak'], $_W['config']['bae']['sk']);
	try {
		$response = $baiduBCS->create_object($_W['config']['bae']['bucket'], $result['path'], $file['tmp_name'], array('acl' => BaiduBCS::BCS_SDK_ACL_TYPE_PUBLIC_READ));
	} catch (Exception $e) {
		return error(-1, $e->getMessage());
	}
	if ($response->isOK()) {
		$baiduBCS->set_object_meta($_W['config']['bae']['bucket'], $result['path'], array("Content-Type" => BCS_MimeTypes::get_mimetype($extention)));
		$result['success'] = true;
	}
	return $result;
}

function file_deleteBAE($file) {
	global $_W;
	$baiduBCS = new BaiduBCS($_W['config']['bae']['ak'], $_W['config']['bae']['sk']);
	if ($file[0] == '/' && $baiduBCS->is_object_exist($_W['config']['bae']['bucket'], $file)) {
		$response = $baiduBCS->delete_object($_W['config']['bae']['bucket'], $file);
	}
	return TRUE;
}

function file_writeBAE($file, $data) {
	global $_W;
	$file = str_replace(IA_ROOT.'/', '', $file);
	$file = $file[0] == '/' ? $file : '/'.$file;
	$pathinfo = pathinfo($file);
	$baiduBCS = new BaiduBCS($_W['config']['bae']['ak'], $_W['config']['bae']['sk']);
	$response = $baiduBCS->create_object_by_content($_W['config']['bae']['bucket'], $file, $data, array('acl' => BaiduBCS::BCS_SDK_ACL_TYPE_PUBLIC_READ));
	if ($response->isOK()) {
		$baiduBCS->set_object_meta($_W['config']['bae']['bucket'], $file, array("Content-Type" => BCS_MimeTypes::get_mimetype($pathinfo['extension'])));
		$result['success'] = true;
	}
}
