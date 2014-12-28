<?php
/**
 * 微站导航管理
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

$positions = array(
	1 => array('name' => 'home', 'title' => '首页'),
	2 => array('name' => 'profile', 'title' => '个人中心'),
	3 => array('name' => 'shortcut', 'title' => '快捷菜单'),
);
$do = in_array($do, array('post', 'delete', 'saves', 'display')) ? $do : 'display';
$modulename = $_GPC['name'];
$mod = null;
$position = intval($_GPC['position']);
$position = max(1, $position);
$position = min(3, $position);
$visiables = array();
$visiables['home'] = true;
$visiables['profile'] = true;
$visiables['shortcut'] = true;

if(!empty($modulename)) {
	$isexists = false;
	foreach($_W['account']['modules'] as $m) {
		if(strtolower($m['name']) == strtolower($modulename)) {
			$mod = array_merge($_W['modules'][$m['name']], $_W['account']['modules'][$_W['modules'][$m['name']]['mid']]);
			$isexists = true;
		}
	}
	if(!$isexists) {
		message("访问非法, 没有操作权限. (module: {$entry['module']})");
	}
}

if ($do == 'post') {
	$id = intval($_GPC['id']);
	if (checksubmit('fileupload-delete')) {
		file_delete($_GPC['fileupload-delete']);
		pdo_update('site_nav', array('icon' => ''), array('id' => $id));
		message('删除成功！', referer(), 'success');
	}
	if (!empty($id)) {
		$item = pdo_fetch("SELECT * FROM " . tablename('site_nav') . " WHERE `weid` = :weid AND `id` = :id" , array(':weid' => $_W['weid'], ':id' => $id));
		$item['css'] = unserialize($item['css']);
		if (strexists($item['icon'], 'images/')) {
			$item['fileicon'] = $item['icon'];
			$item['icon'] = '';
		}
		if (empty($item)) {
			message('抱歉，导航不存在或是已经删除！', '', 'error');
		}
	}
	if (checksubmit('submit')) {
		if (empty($_GPC['title'])) {
			message('抱歉，请输入导航菜单的名称！', '', 'error');
		}
		$data = array(
			'weid' => $_W['weid'],
			'name' => $_GPC['title'],
			'description' => $_GPC['description'],
			'displayorder' => intval($_GPC['displayorder']),
			'url' => $_GPC['url'],
			'icon' => $_GPC['icon_old'],
			'status' => intval($_GPC['status']),
		);
		if(empty($id) || empty($item['module'])) {
			$data['position'] = $position;
		}
		$data['css'] = serialize(array(
			'icon' => array(
				'font-size' => $_GPC['icon']['size'],
				'color' => $_GPC['icon']['color'],
				'width' => $_GPC['icon']['size'],
				'icon' => $_GPC['icon']['icon'],
			),
			'name' => array(
				'color' => $_GPC['color'],
			),
		));
		if (!empty($_FILES['icon']['tmp_name'])) {
			file_delete($_GPC['icon_old']);
			$upload = file_upload($_FILES['icon']);
			if (is_error($upload)) {
				message($upload['message'], '', 'error');
			}
			$data['icon'] = $upload['path'];
		}
		if (empty($id)) {
			pdo_insert('site_nav', $data);
		} else {
			pdo_update('site_nav', $data, array('id' => $id));
		}
		message('导航更新成功！', create_url('site/nav/display', array('position' => $position)), 'success');
	}

	require model('site');
	$systemmenus = site_system_menus();
	template('site/menu');
} elseif ($do == 'delete') {
	$id = intval($_GPC['id']);
	$item = pdo_fetch("SELECT * FROM " . tablename('site_nav') . " WHERE `weid` = :weid AND `id` = :id" , array(':weid' => $_W['weid'], ':id' => $id));
	if (empty($item)) {
		message('抱歉，导航不存在或是已经删除！', '', 'error');
	}
	if (!empty($item['icon'])) {
		file_delete($item['icon']);
	}
	pdo_delete('site_nav', array('id' => $id));
	message('导航删除成功！', referer(), 'success');
} elseif ($do == 'saves') {
	$titles = $_GPC['title'];
	$displayorders = $_GPC['displayorder'];
	$filter = array();
	$filter['weid'] = $_W['weid'];
	foreach($titles as $id => $t) {
		$filter['id'] = intval($id);
		if(!empty($t)) {
			$rec = array(
				'name' => $t
			);
			pdo_update('site_nav', $rec, $filter);
		}
	}
	foreach($displayorders as $id => $o) {
		$filter['id'] = intval($id);
		$rec = array(
			'displayorder' => intval($o)
		);
		pdo_update('site_nav', $rec, $filter);
	}
	message('批量编辑成功.', referer(), 'success');
} elseif ($do == 'display') {
	if(!empty($modulename)) {
		$visiables = array();
		$vsql = 'SELECT DISTINCT `entry` FROM ' . tablename('modules_bindings') . " WHERE `module`=:module AND `entry` IN ('home', 'profile', 'shortcut')";
		$es = pdo_fetchall($vsql, array(':module' => $modulename));
		if(is_array($es)) {
			foreach($es as $entry) {
				$visiables[$entry['entry']] = true;
			}
			if(!array_key_exists($positions[$position]['name'], $visiables)) {
				foreach($positions as $p => $pos) {
					if(array_key_exists($pos['name'], $visiables)) {
						header('location:' . create_url('site/nav', array('position' => $p, 'name' => $modulename)));
						exit();
					}
				}
			}
		}
	}

	if($_W['ispost']) {
		$ret = $_GPC['ret'] == 'true';
		$set = @json_decode(base64_decode($_GPC['dat']), true);
		if(is_array($set)) {
			$sql = 'SELECT * FROM ' . tablename('site_nav') . ' WHERE `weid`=:weid AND `module`=:module AND `url`=:url';
			$pars = array();
			$pars[':weid'] = $_W['weid'];
			$pars[':module'] = $set['module'];
			$pars[':url'] = $set['url'];
			$nav = pdo_fetch($sql, $pars);
			if(!empty($nav)) {
				if (empty($nav['module'])) {
					$record = array('status' => $ret);
					if(pdo_update('site_nav', $record, array('id' => $nav['id'])) !== false) {
						exit('success');
					}
				} else {
					if (empty($ret)) {
						pdo_delete('site_nav', array('id' => $nav['id']));
						exit('success');
					}
				}
			} else {
				$nav = array();
				$nav['weid'] = $_W['weid'];
				$nav['module'] = $set['module'];
				$nav['displayorder'] = 0;
				$nav['name'] = $set['title'];
				$nav['position'] = $position;
				$nav['url'] = $set['url'];
				$nav['status'] = $ret;
				if(pdo_insert('site_nav', $nav)) {
					exit('success');
				}
			}
		}
		exit();
	}

	$sql = 'SELECT * FROM ' . tablename('modules_bindings') . " WHERE `entry`=:entry AND `entry` IN ('home', 'profile', 'shortcut')";
	$pars = array();
	$pars[':entry'] = $positions[$position]['name'];
	if(!empty($modulename)) {
		$sql .= ' AND `module`=:module';
		$pars[':module'] = $modulename;
	}
	$es = pdo_fetchall($sql, $pars);
	$ds = array();
	if(is_array($es)) {
		$calls = array();
		foreach($es as $entry) {
			$mid = $_W['modules'][$entry['module']]['mid'];
			if (empty($mid) || !isset($_W['account']['modules'][$mid])) {
				continue;
			}
			if(!empty($entry['call']) && empty($calls[$entry['call']])) {
				$calls[$entry['call']] = true;
				$site = WeUtility::createModuleSite($entry['module']);
				if(method_exists($site, $entry['call'])) {
					$ret = $site->$entry['call']();
					if(is_array($ret)) {
						foreach($ret as $et) {
							$ds[] = array('module' => $entry['module'], 'from' => 'call', 'title' => $et['title'], 'url' => $et['url']);
						}
					}
				}
			} else {
				$et = array(
					'title' => $entry['title'],
					'url' => create_url("mobile/entry", array('eid' => $entry['eid'], 'weid' => $_W['weid']))
				);
				$ds[] = array('module' => $entry['module'], 'from' => 'define', 'title' => $et['title'], 'url' => $et['url']);
			}
		}
	}
	$rs = array();
	$pars = array();
	$pars[':weid'] = $_W['weid'];
	if(!empty($modulename)) {
		$condition = ' AND `module`=:module';
		$pars[':module'] = $modulename;
	}
	$sql = 'SELECT * FROM ' . tablename('site_nav') . ' WHERE `weid`=:weid AND `position`=' . $position . $condition . ' ORDER BY `displayorder`';
	$navs = pdo_fetchall($sql, $pars);
	if(is_array($navs)) {
		foreach($navs as $row) {
			$css = iunserializer($row['css']);
			if(empty($css)) {
				$css = array();
			}

			$rs[] = array(
				'id' => $row['id'],
				'module' => $row['module'],
				'title' => $row['name'],
				'url' => $row['url'],
				'from' => $row['module'] ? 'define' : 'custom',
				'checked' => $row['status'],
				'remove' => true,
				'displayorder' => $row['displayorder'],
				'icon' => $row['icon'],
				'css' => $css
			);
		}
		$rs_extend = array();
		foreach($ds as $row) {
			$match = false;
			foreach($rs as $nav) {
				if($row['module'] == $nav['module'] && $row['url'] == $nav['url']) {
					$match = true;
					break;
				}
			}
			if(!$match) {
				$rs_extend[] = $row;
			}
		}
		$ds = array_merge($rs, $rs_extend);
	}
	$froms = array(
		'call' => '动态数据',
		'define' => '模块预定义',
		'custom' => '用户添加',
	);
	template('site/menu');
}
