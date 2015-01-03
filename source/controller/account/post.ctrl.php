<?php
/**
 * [WNS] Copyright (c) 2013 BIRM.CO
 */
defined('IN_IA') or exit('Access Denied');

$id = intval($_GPC['id']);
if ($_GPC['id'] == 'current') {
	$id = $_W['weid'];
}
if (!checkpermission('wechats', $id)) {
	message('公众号不存在或是您没有权限操作！');
}

if (checksubmit('submit') || checksubmit('sync')) {
	if (!empty($_GPC['parentid'])) {
		$parentwechat = pdo_fetch("SELECT weid, parentid FROM ".tablename('wechats')." WHERE weid = :weid", array(':weid' => $_GPC['parentid']));
		if (!empty($parentwechat['parentid'])) {
			message('抱歉，不能给子公众号添加子号！');
		}
		if (!$_W['isfounder']) {
			$maxsubaccount = pdo_fetchcolumn("SELECT maxsubaccount FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $_W['member']['groupid']));
			if (!empty($maxsubaccount)) {
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('wechats')." WHERE uid = :uid AND parentid > 0", array(':uid' => $_W['uid']));
				if (!empty($total) && $total >= $maxsubaccount) {
					message('抱歉，您已拥有最大限制数量的子公众号！');
				}
			}
		}
	} else {
		if (!$_W['isfounder'] && empty($id)) {
			$maxaccount = pdo_fetchcolumn("SELECT maxaccount FROM ".tablename('members_group')." WHERE id = :id", array(':id' => $_W['member']['groupid']));
			
			if (!empty($maxaccount)) {
				$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('wechats')." WHERE uid = :uid AND parentid = '0'", array(':uid' => $_W['uid']));
				if (!empty($total) && $total >= $maxaccount) {
					message('抱歉，您已拥有最大限制数量的公众号！');
				}
			}
		}
	}
	$data = array(
		'type' => intval($_GPC['type']),
		'uid' => $_W['uid'],
		'name' => $_GPC['name'],
		'account' => $_GPC['account'],
		'accountlink' => $_GPC['accountlink'],
		'original' => $_GPC['original'],
		'token' => $_GPC['wetoken'],
		'level' => intval($_GPC['level']),
		'key' => $_GPC['key'],
		'secret' => $_GPC['secret'],
		'signature' => '',
		'country' => '',
		'province' => '',
		'city' => '',
		'username' => '',
		'password' => '',
		'welcome' => '',
		'default' => '',
		'lastupdate' => '0',
		'default_period' => '0',
		'styleid' => 1,
		'parentid' => intval($_GPC['parentid']),
	);
	if (checksubmit('sync')) {
		$wechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = '$id'");
		$username = $wechat['username'];
		$password = $wechat['password'];
	} elseif (!empty($_GPC['wxusername']) && !empty($_GPC['wxpassword'])) {
		$username = $_GPC['wxusername'];
		$password = md5($_GPC['wxpassword']);
	}
	
	if (!empty($username) && !empty($password)) {
		if ($_GPC['type'] == 1) {
			$loginstatus = account_weixin_login($username, $password, $_GPC['verify']);
			$basicinfo = account_weixin_basic($username);
		} elseif ($_GPC['type'] == 2) {
			$loginstatus = account_yixin_login($username, $password, $_GPC['verify']);
			$basicinfo = account_yixin_basic($username);
		}
		if (empty($basicinfo['name'])) {
			message('一键获取信息失败，请手动添加该公众帐号并反馈此信息给管理员！');
		}
		$data['username'] = $_GPC['wxusername'];
		$data['password'] = md5($_GPC['wxpassword']);
		$data['lastupdate'] = TIMESTAMP;
		$data['name'] = $basicinfo['name'];
		$data['account'] = $basicinfo['account'];
		$data['accountlink'] = $_GPC['accountlink'];
		$data['original'] = $basicinfo['original'];
		$data['signature'] = $basicinfo['signature'];
		$data['country'] = $basicinfo['country'];
		$data['province'] = $basicinfo['province'];
		$data['city'] = $basicinfo['city'];
		$data['key'] = $basicinfo['key'];
		$data['secret'] = $basicinfo['secret'];
	}
	if(empty($data['name'])) {
		message('必须输入公众号的名称！');
	}
	if (!empty($id)) {
		$update = array(
			'type' => intval($_GPC['type']),
			'name' => $data['name'],
			'account' => $data['account'],
			'accountlink' => $data['accountlink'],
			'original' => $data['original'],
			'token' => $data['token'],
			'level' => $data['level'],
			'key' => $data['key'],
			'secret' => $data['secret'],
		);
		if (!empty($data['password'])) {
			$update['username'] = $data['username'];
			$update['password'] = $data['password'];
			$update['lastupdate'] = $data['lastupdate'];
		}
		//保存图片
		if (!empty($basicinfo['headimg'])) {
			file_write('headimg_'.$id.'.jpg', $basicinfo['headimg']);
		}
		if (!empty($basicinfo['qrcode'])) {
			file_write('qrcode_'.$id.'.jpg', $basicinfo['qrcode']);
		}
		pdo_update('wechats', $update, array('weid' => $id));
	} else {
		$data['hash'] = random(5);
		$data['token'] = random(32);
		if (pdo_insert('wechats', $data)) {
			$id = pdo_insertid();
			//保存图片
			if (!empty($basicinfo['headimg'])) {
				file_write('headimg_'.$id.'.jpg', $basicinfo['headimg']);
			}
			if (!empty($basicinfo['qrcode'])) {
				file_write('qrcode_'.$id.'.jpg', $basicinfo['qrcode']);
			}
			if (!empty($loginstatus)) {
				//尝试一键接入，关闭编辑模式，开启开发模式，接入API地址
				if ($_GPC['type'] == 1) {
					$result = account_weixin_interface($data['username'], $data['hash'], $data['token']);
					if (is_error($result)) {
						$error = $result['message'];
					}
				}
			}
			if (!empty($data['parentid'])) {
				$subwechats = pdo_fetchall("SELECT weid FROM ".tablename('wechats')." WHERE parentid = :parentid", array(':parentid' => $data['parentid']), 'weid');
				if (!empty($subwechats)) {
					pdo_update('wechats', array('subwechats' => implode(',', array_keys($subwechats))), array('weid' => $data['parentid']));
				}
			}
		}
	}
	if (!empty($_FILES['qrcode']['tmp_name'])) {
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = '';
		$_W['uploadsetting']['image']['extentions'] = array('jpg');
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$upload = file_upload($_FILES['qrcode'], 'image', "qrcode_{$id}");
	}
	if (!empty($_FILES['headimg']['tmp_name'])) {
		$_W['uploadsetting'] = array();
		$_W['uploadsetting']['image']['folder'] = '';
		$_W['uploadsetting']['image']['extentions'] = array('jpg');
		$_W['uploadsetting']['image']['limit'] = $_W['config']['upload']['image']['limit'];
		$upload = file_upload($_FILES['headimg'], 'image', "headimg_{$id}");
	}
	message('更新公众号成功！'.(!empty($error) ? '<br /> 接入公众平台失败。错误：'.$error.'，请手动接入！' : (!empty($loginstatus) ? '已成功接入公众平台！' : '')), create_url('account/post', array('id' => $id, 'parentid' => $data['parentid'])), !empty($error) ? 'error' : 'success');
}

$wechat = array();
if (!empty($id)) {
	$wechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = '$id'");
}
$parentid = intval($_GPC['parentid']);
if (!empty($parentid)) {
	$parentwechat = pdo_fetch("SELECT * FROM ".tablename('wechats')." WHERE weid = :weid", array(':weid' => $parentid));
}
template('account/post');
