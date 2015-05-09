<?php
/**
 * 微会员模块定义
 *
 * @author WeEngine Team
 * @url
 */
defined('IN_IA') or exit('Access Denied');

class MemberModule extends WeModule {

	public function doCard() {
		global $_GPC, $_W;
		$setting = pdo_fetch("SELECT * FROM ".tablename('card')." WHERE weid = '{$_W['weid']}'");
		if (!empty($setting)) {
			$setting['color'] = iunserializer($setting['color']);
			$setting['background'] = iunserializer($setting['background']);
			$setting['fields'] = iunserializer($setting['fields']);
		}
		if (checksubmit('submit')) {
			if (empty($_GPC['title'])) {
				message('请输入会员卡名称！');
			}
			if (empty($_GPC['format'])) {
				message('请输入会员卡的卡号生成格式！');
			}
			$data = array(
				'title' => $_GPC['title'],
				'color' => iserializer(array(
					'title' => $_GPC['color-title'],
					'number' => $_GPC['color-number'],
				)),
				'background' => iserializer(array(
					'background' => $_GPC['background'],
					'image' => $_GPC[$_GPC['background'].'-bg'],
				)),
				'logo' => $_GPC['logo'],
				'format' => $_GPC['format'],
			);
			if (!empty($_GPC['fields'])) {
				foreach ($_GPC['fields']['title'] as $index => $row) {
					if (empty($_GPC['fields']['title'])) {
						continue;
					}
					$data['fields'][] = array(
						'title' => $_GPC['fields']['title'][$index],
						'require' => intval($_GPC['fields']['require'][$index]),
						'bind' => $_GPC['fields']['bind'][$index],
					);
				}
				$data['fields'] = iserializer($data['fields']);
			}
			if (!empty($setting)) {
				pdo_update('card', $data, array('weid' => $_W['weid']));
			} else {
				$data['weid'] = $_W['weid'];
				pdo_insert('card', $data);
			}
			message('会员卡设置成功！', referer(), 'success');
		}
		$fields = fans_fields();
		include $this->template('card');
	}

	public function doPassword() {
		global $_W, $_GPC;
		if ($_GPC['op'] == 'info') {
			include $this->template('passwordinfo');
			exit;
		}
		if (checksubmit('submit')) {
			if (!empty($_GPC['title-new'])) {
				foreach ($_GPC['title-new'] as $index => $row) {
					$data = array(
						'weid' => $_W['weid'],
						'name' => $_GPC['title-new'][$index],
						'password' => member_hash($_GPC['password-new'][$index], ''),
					);
					pdo_insert('card_password', $data);
				}
			}
			if (!empty($_GPC['title'])) {
				foreach ($_GPC['title'] as $index => $row) {
					$data = array(
						'name' => $_GPC['title'][$index],
					);
					if (!empty($_GPC['password'][$index])) {
						$data['password'] = member_hash($_GPC['password'][$index], '');
					}
					pdo_update('card_password', $data, array('id' => $index));
				}
			}
			if (!empty($_GPC['delete'])) {
				pdo_query("DELETE FROM ".tablename('card_password')." WHERE id IN (".implode(',', $_GPC['delete']).")");
			}
			message('消费密码更新成功！', referer(), 'success');
		}
		$list = pdo_fetchall("SELECT * FROM ".tablename('card_password')." WHERE weid = :weid", array(':weid' => $_W['weid']));
		include $this->template('password');
	}

	public function doStore() {
		global $_W, $_GPC;
		$setting = $_W['account']['modules'][$this->_saveing_params['mid']]['config'];
		if (checksubmit('submit')) {
			$setting['store'] = array(
				'title' => $_GPC['title'],
				'thumb' => $_GPC['thumb'],
				'content' => $_GPC['content'],
				'phone' => $_GPC['phone'],
				'qq' => $_GPC['qq'],
				'province' => $_GPC['resideprovince'],
				'city' => $_GPC['residecity'],
				'dist' => $_GPC['residedist'],
				'address' => $_GPC['address'],
				'lng' => $_GPC['lng'],
				'lat' => $_GPC['lat'],
				'industry1' => $_GPC['industry_1'],
				'industry2' => $_GPC['industry_2'],
			);
			$this->saveSettings($setting);
			message('商家设置成功！', referer(), 'success');
		}
		$setting = $setting['store'];
		include $this->template('store');
	}

	public function doMember() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('card_coupon')." WHERE id = :id" , array(':id' => $id));
				if (empty($item)) {
					message('抱歉，优惠券不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入优惠券的标题！');
				}
				if (empty($_GPC['type'])) {
					message('请选择优惠券的类型！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'displayorder' => intval($_GPC['displayorder']),
					'status' => intval($_GPC['status']),
					'starttime' => strtotime($_GPC['datelimit-start']),
					'endtime' => strtotime($_GPC['datelimit-end']),
					'type' => intval($_GPC['type']),
					'pretotal' => intval($_GPC['pretotal']),
					'total' => intval($_GPC['total']),
					'content' => $_GPC['content'],
					'createtime' => TIMESTAMP,
				);
				if (!empty($id)) {
					pdo_update('card_coupon', $data, array('id' => $id));
				} else {
					pdo_insert('card_coupon', $data);
				}
				message('优惠券更新成功！', create_url('site/module/coupon', array('name' => 'member', 'op' => 'display')), 'success');
			}
		} elseif ($operation == 'display') {

			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND cardsn LIKE '%{$_GPC['keyword']}%'";
			}

			if (isset($_GPC['status'])) {
				$condition .= " AND status = '".intval($_GPC['status'])."'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('card_members')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, array(), 'from_user');
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('card_members') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);

			$fans = fans_search(array_keys($list), array('realname'));
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$member = pdo_fetch("SELECT id, status FROM ".tablename('card_members')." WHERE id = :id", array(':id' => $id));
			if (empty($member)) {
				message('抱歉，会员不存在或是已经被删除！');
			}
			pdo_delete('card_members', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} elseif ($operation == 'credit') {
			$id = intval($_GPC['id']);
			$member = pdo_fetch("SELECT from_user, id, credit1, credit2 FROM ".tablename('card_members')." WHERE id = :id", array(':id' => $id));
			if (empty($member)) {
				message('抱歉，会员不存在或是已经被删除！');
			}
			if (checksubmit('submit')) {
				$data = array();
				$credit1 = intval($_GPC['credit1']);
				$credit2 = doubleval($_GPC['credit2']);

				if ($credit1 != $member['credit1']) {
					$data['credit1'] = $credit1;
				}
				if ($credit2 != $member['credit2']) {
					$data['credit2'] = $credit2;
				}
				if (!empty($data) && pdo_update('card_members', $data, array('id' => $id))) {
					if (!empty($data['credit1'])) {
						pdo_insert('card_log', array(
						'weid' => $_W['weid'],
						'from_user' => $member['from_user'],
						'type' => 1,
						'content' => $credit1,
						'createtime' => TIMESTAMP,
						));
					}
					if (!empty($data['credit1'])) {
						pdo_insert('card_log', array(
						'weid' => $_W['weid'],
						'from_user' => $member['from_user'],
						'type' => 2,
						'content' => $credit2,
						'createtime' => TIMESTAMP,
						));
					}
				}
				message('更新用户积分成功！', referer(), 'success');
			}
		} elseif ($operation == 'status') {
			$id = intval($_GPC['id']);
			$member = pdo_fetch("SELECT id, status FROM ".tablename('card_members')." WHERE id = :id", array(':id' => $id));
			if (empty($member)) {
				message('抱歉，会员不存在或是已经被删除！');
			}
			if (empty($member['status'])) {
				$status = 1;
			} else {
				$status = 0;
			}
			pdo_update('card_members', array('status' => $status), array('id' => $id));
			message('更新用户状态成功！', referer(), 'success');
		}
		include $this->template('member');
	}

}