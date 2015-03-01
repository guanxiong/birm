<?php
/**
 * 文件操作
 * 
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

/**
 * 将数据写入某文件，如果文件或目录不存在，则创建
 * @param string $filename 要写入的目标
 * @param string $data 要写入的数据
 * @return bool
 */
function file_write($filename, $data) {
	global $_W;
	$filename = IA_ROOT . '/' . $GLOBALS['_W']['config']['upload']['attachdir'] . $filename;
	mkdirs(dirname($filename));
	file_put_contents($filename, $data);
	@chmod($filename, $_W['config']['setting']['filemode']);
	return is_file($filename);
}

/**
 * 将文件移动至目标位置，如果目标位置目录不存在，则创建
 * @param string $filename 要移动的文件
 * @param string $desc 移动的目标位置
 * @return bool
 */
function file_move($filename, $dest) {
	global $_W;
	mkdirs(dirname($dest));
	if(is_uploaded_file($filename)) {
		move_uploaded_file($filename, $dest);
	} else {
		rename($filename, $dest);
	}
	@chmod($filename, $_W['config']['setting']['filemode']);
	return is_file($dest);
}

/**
 * 递归创建目录树
 * @param string $path 目录树
 * @return bool
 */
function mkdirs($path) {
	if(!is_dir($path)) {
		mkdirs(dirname($path));
		mkdir($path);
	}
	return is_dir($path);
}

/**
 * 删除目录（递归删除内容）
 * @param string $path 目录位置
 * @param bool $clean 不删除目录，仅删除目录内文件
 * @return bool
 */
function rmdirs($path, $clean=false) {
	if(!is_dir($path)) {
		return false;
	}
	$files = glob($path . '/*');
	if($files) {
		foreach($files as $file) {
			is_dir($file) ? rmdirs($file) : @unlink($file);
		}
	}
	return $clean ? true : @rmdir($path);
}

/**
 * 上传文件保存，缩略图暂未实现
 * @param string $fname 上传的$_FILE字段
 * @param string $type 上传类型（将按分类保存不同子目录，image -> images）
 * @param string $sname 保存的文件名，如果为 auto 则自动生成文件名，否则请指定从附件目录开始的完整相对路径（包括文件名，不包括文件扩展名）
 * @return array 返回结果数组，字段包括：success => bool 是否上传成功，path => 保存路径（从附件目录开始的完整相对路径），message => 提示信息
 */
function file_upload($file, $type = 'image', $sname = 'auto') {
	if(empty($file)) {
		return error(-1, '没有上传内容');
	}
	global $_W;
	if (empty($_W['uploadsetting'])) {
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = 'images';
		$_W['uploadsetting']['image']['extentions'] = $_W['config']['upload']['image']['extentions'];
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
	}
	$settings = $_W['uploadsetting'];
	if(!array_key_exists($type, $settings)) {
		return error(-1, '未知的上传类型');
	}
	$extention = pathinfo($file['name'], PATHINFO_EXTENSION);
	if(!in_array(strtolower($extention), $settings[$type]['extentions'])) {
		return error(-1, '不允许上传此类文件');
	}
	if(!empty($settings[$type]['limit']) && $settings[$type]['limit'] * 1024 < filesize($file['tmp_name'])) {
		return error(-1, "上传的文件超过大小限制，请上传小于 {$settings[$type]['limit']}k 的文件");
	}
	$result = array();
	$path = '/'.$_W['config']['upload']['attachdir'];

	if($sname == 'auto') {
		$result['path'] = "{$settings[$type]['folder']}/" . date('Y/m/');
		mkdirs(IA_ROOT . $path . $result['path']);
		do {
			$filename = random(30) . ".{$extention}";
		} while(file_exists(IA_ROOT . $path . $filename));
		$result['path'] .= $filename;
	} else {
		$result['path'] = "{$settings[$type]['folder']}/" . $sname . '.' . $extention;  
		mkdirs(IA_ROOT . dirname($path));
	}
	$filename = IA_ROOT . $path . $result['path'];
	if(!file_move($file['tmp_name'], $filename)) {
		return error(-1, '保存上传文件失败');
	}
	$result['success'] = true;
	return $result; 
}
/**
 * 删除文件
 * 
 */
function file_delete($file) {
	global $_W;
	if (empty($file)) {
		return FALSE;	
	}	
	if (file_exists(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/' . $file)) {
		unlink(IA_ROOT . '/' . $_W['config']['upload']['attachdir'] . '/' . $file);
	}
	return TRUE;
}

/**
 * 图像缩略处理 
 * 需要能够处理 jpg和png图像 
 * 如果原图像宽度小于指定宽度, 直接复制到目标地址
 * 如果原图像宽度大于指定宽度, 按比例缩放至指定宽度后保存至目标地址
 * 失败返回error, 说明错误原因
 * 成功返回true
 * @param string $srcfile 原图像地址
 * @param string $des 新图像地址(绝对路径)
 * @param number $width
 */
function file_image_thumb($srcfile, $des, $width = 600) {
	//得到原始文件名，缩放后的文件名与原始名相同
	$imgname=explode('/',$srcfile);
	$arrcount=count($imgname);
	$filename = $imgname[$arrcount-1];
	//原图像信息
	$org_info = @getimagesize($srcfile);
	if($width == '0' || $width > $org_info[0]) {
		copy($srcfile,$path.$filename); 
		return true;
	}
	if($org_info) {
		if($org_info[2] == 1) { //gif不处理
			if(function_exists("imagecreatefromgif")) {
				$img_org = imagecreatefromgif($srcfile);
			}
		} elseif($org_info[2] == 2) {
			if(function_exists("imagecreatefromjpeg")) {
				$img_org = imagecreatefromjpeg($srcfile);
			}
		} elseif($org_info[2] == 3) {
			if(function_exists("imagecreatefrompng")) {
				$img_org = imagecreatefrompng($srcfile);
			}
		}
	} else {
		return error('-1','获取原始图像信息失败');
	}
	//源图像的宽高比
	$scale_org = $org_info[0] / $org_info[1];
	//缩放后的高
	$height = $width / $scale_org;
	if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$img_dst = imagecreatetruecolor($width, $height)) {
		imagecopyresampled($img_dst, $img_org, 0, 0, 0, 0, $width, $height, $org_info[0], $org_info[1]);
	} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && @$img_dst = imagecreate($width, $height)) {
		imagecopyresized($img_dst, $img_org, 0, 0, 0, 0, $width, $height, $org_info[0], $org_info[1]);
	} else {
		return error('-1','PHP环境不支持图片处理');
	}
	if(function_exists('imagejpeg')) {
		imagejpeg($img_dst, $des.$filename);
	} elseif(function_exists('imagepng')) {
		imagepng($img_dst, $des.$filename);
	} 
	imagedestroy($img_dst);
	imagedestroy($img_org);
	return true;
}

/**
 * 图像裁切处理
 * 需要能够处理 jpg和png图像
 * 如果原图像宽度小于指定宽度(高度), 不处理宽度(高度)
 * 如果原图像宽度大于指定宽度(高度), 则按照裁剪位置裁切指定宽度(高度)
 * 将裁切成功的图像保存至目标地址
 * 失败返回error, 说明错误原因
 * 成功返回true
 * @param string $src 原图像地址
 * @param string $des 新图像地址
 * @param number $width 要裁切的宽度
 * @param number $height 要裁切的高度
 * @param number $position 开始裁切的位置, 按照九宫格1-9指定位置
 */
function file_image_crop($src, $des, $width = 400, $height = 300, $position = 1) {
	//得到原始文件名，缩放后的文件名与原始名相同
	$imgname=explode('/',$src);
	$arrcount=count($imgname);
	$filename = $imgname[$arrcount-1];
	//原图像信息
	$org_info = @getimagesize($src);
	if($org_info) {
		if($org_info[2] == 1) { //gif不处理
			if(function_exists("imagecreatefromgif")) {
				$img_org = imagecreatefromgif($src);
			}
		} elseif($org_info[2] == 2) {
			if(function_exists("imagecreatefromjpeg")) {
				$img_org = imagecreatefromjpeg($src);
			}
		} elseif($org_info[2] == 3) {
			if(function_exists("imagecreatefrompng")) {
				$img_org = imagecreatefrompng($src);
			}
		}
	} else {
		return error('-1','获取原始图像信息失败');
	}
	
	//处理裁剪的宽高
	if($width == '0' || $width > $org_info[0]) {
		$width = $org_info[0];
	}
	if($height == '0' || $height > $org_info[1]) {
		$height = $org_info[1];
	}
	//获取裁剪的起点坐标
	switch ($position) {
		case 0 :
		case 1 :
			$dst_x = 0; $dst_y = 0;
			break;
		case 2 :
			$dst_x = ($org_info[0] - $width) / 2; $dst_y = 0;
			break;
		case 3 :
			$dst_x = $org_info[0] - $width; $dst_y = 0;
			break;
		case 4 :
			$dst_x = 0; $dst_y = ($org_info[1] - $height) / 2;
			break;
		case 5 :
			$dst_x = ($org_info[0] - $width) / 2; $dst_y = ($org_info[1] - $height) / 2;
			break;
		case 6 :
			$dst_x = $org_info[0] - $width; $dst_y = ($org_info[1] - $height) / 2;
			break;
		case 7 :
			$dst_x = 0; $dst_y = $org_info[1] - $height;
			break;
		case 8 :
			$dst_x = ($org_info[0] - $width) / 2; $dst_y = $org_info[1] - $height;
			break;
		case 9 :
			$dst_x = $org_info[0] - $width; $dst_y = $org_info[1] - $height;
			break;
		default:
			$dst_x = 0; $dst_y = 0;
	}
	if($width == $org_info[0]) {
		$dst_x = 0;
	}
	if($height == $org_info[1]) {
		$dst_y = 0;
	}
	
	if(function_exists("imagecreatetruecolor") && function_exists("imagecopyresampled") && @$img_dst = imagecreatetruecolor($width, $height)) {
		imagecopyresampled($img_dst, $img_org, 0, 0, $dst_x, $dst_y, $width, $height, $width, $height);
	} elseif(function_exists("imagecreate") && function_exists("imagecopyresized") && @$img_dst = imagecreate($width, $height)) {
		imagecopyresized($img_dst, $img_org, 0, 0, $dst_x, $dst_y, $width, $height, $width, $height);
	} else {
		return error('-1','PHP环境不支持图片处理');
	}
	if(function_exists('imagejpeg')) {
		imagejpeg($img_dst, $des);
	} elseif(function_exists('imagepng')) {
		imagepng($img_dst, $des);
	}
	imagedestroy($img_dst);
	imagedestroy($img_org);
	return true;
	
	
}
