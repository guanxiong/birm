<?php
/**
 * @author 更多模块请浏览bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class QuickHotelModuleSite extends WeModuleSite {

	public function doWebQuickHotel() {
		// 1. display quickhotel
		// 2. add quickhotel
		// 3. delete quickhotel
		// 4. update quickhotel
		global $_W;
		global $_GPC; // 获取query string中的参数
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		if ($operation == 'post') { // 增加或者更新酒店
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('quickhotel_list')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，酒店不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入酒店名称和房型！');
				}
				if (empty($_GPC['room'])) {
					message('请输入空房数量！');
				}
				if (empty($_GPC['price'])) {
					message('请输入房间价格！');
				}
				$room = intval($_GPC['room']);
				$price = intval($_GPC['price']);
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'logo' => $_GPC['logo'],
					'room' => $room,
					'price' => $price,
					'position' => $_GPC['position'],
					'content' => $_GPC['content'],
					'createtime' => TIMESTAMP,
				);
				if (!empty($id)) {
					pdo_update('quickhotel_list', $data, array('id' => $id));
				} else {
					pdo_insert('quickhotel_list', $data);
				}
				message('酒店更新成功！', create_url('site/module/quickhotel', array('name' => 'quickhotel', 'op' => 'display')), 'success');
			}
		}
		else if ($operation == 'delete') { //删除酒店
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('quickhotel_list')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，酒店不存在或是已经被删除！');
			}
			pdo_delete('quickhotel_list', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('quickhotel_list')." WHERE weid = '{$_W['weid']}' $condition ORDER BY createtime DESC");
		}
		include $this->template('quickhotel');
	}

	public function doWebReservation() {
		// 1. display reservation
		// 2. add quickhotel
		// 3. delete quickhotel
		// 4. update quickhotel
		global $_W;
		global $_GPC; // 获取query string中的参数
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'delete') { //删除酒店
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('quickhotel_reservation')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，编号为'.$id.'的预订不存在或是已经被删除！');
			}
			pdo_delete('quickhotel_reservation', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$sql = "SELECT * FROM ".tablename('quickhotel_list')." as t1,".tablename('quickhotel_reservation')."as t2 WHERE t1.id=t2.quickhotel_id AND t1.weid = '{$_W['weid']}' ORDER BY t2.createtime DESC";
			$list = pdo_fetchall($sql);
			$ar = pdo_fetchall($sql, array(), 'from_user');
			$fans = fans_search(array_keys($ar), array('realname', 'mobile'));
		}
		include $this->template('reservation');
	}

	public function doMobileQuickHotel() {
		global $_W, $_GPC;
		checkauth();
		$quickhotel_list = pdo_fetchall("SELECT * FROM ".tablename('quickhotel_list')." WHERE weid = '{$_W['weid']}'");
		include $this->template('quickhotel_new');
	}
	
	public function doMobileFillInfo() {
		global $_W, $_GPC;
		checkauth();
		$id = intval($_GPC['id']);
		$profile = fans_search($_W['fans']['from_user']);
		$room_info = pdo_fetch("SELECT * FROM ".tablename('quickhotel_list')." WHERE id = $id AND weid = '{$_W['weid']}'");
		include $this->template('fillinfo_new');
	}

	public function doMobileReservation() {
		global $_W, $_GPC;
		checkauth();
		$id = intval($_GPC['id']);
		$room = intval($_GPC['room']);
		$data = array(
			'weid' => $_W['weid'],
			'from_user' => $_W['fans']['from_user'],
			'quickhotel_id' => $id,
			'room_count' => $room,
			'createtime' => TIMESTAMP
		);
		pdo_insert('quickhotel_reservation', $data);

		$data = array(
			'realname' => $_GPC['realname'],
			'mobile' => $_GPC['mobile'],
		);
		fans_update($_W['fans']['from_user'], $data);

		// navigate to user profile page
		message('预订酒店更新成功！', create_url('mobile/module/quickhotel', array('weid' => $_W['weid'], 'name' => 'quickhotel', 'do' => 'myquickhotel','op' => 'display')), 'success');
	}


	public function doMobileMyquickhotel() {
		global $_W, $_GPC;
		checkauth();
		$quickhotel_list = pdo_fetchall("SELECT * FROM ".tablename('quickhotel_list')." as t1,".tablename('quickhotel_reservation')."as t2 WHERE t1.id=t2.quickhotel_id AND from_user='{$_W['fans']['from_user']}' AND t1.weid = '{$_W['weid']}' ORDER BY t2.createtime DESC");
		include $this->template('myquickhotel_new');
	}

}
