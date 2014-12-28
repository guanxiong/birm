<?php
/**
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

class MemberModuleSite extends WeModuleSite {

	public function doWebCoupon() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$type = array(
			1 => '现金券',
			2 => '折扣券',
			3 => '体验券',
			4 => '礼品券',
			5 => '特价券',
			6 => '换购券'
		);
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
				$starttime = strtotime($_GPC['datelimit-start']);
				$endtime = strtotime($_GPC['datelimit-end']);

				if ($endtime == $starttime) {
					$endtime = $endtime + 86399;
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'displayorder' => intval($_GPC['displayorder']),
					'status' => intval($_GPC['status']),
					'starttime' => $starttime,
					'endtime' => $endtime,
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
				$condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
			}

			if (isset($_GPC['status'])) {
				$condition .= " AND status = '".intval($_GPC['status'])."'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('card_coupon')." WHERE weid = '{$_W['weid']}' $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('card_coupon') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('card_coupon')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，优惠券不存在或是已经被删除！');
			}
			pdo_delete('card_coupon', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} elseif ($operation == 'history') {
			$receiver = pdo_fetchall("SELECT name FROM ".tablename('card_password')." WHERE weid = {$_W['weid']}");
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = "weid = '{$_W['weid']}' AND status = '2'";
			$params = array();
			if (!empty($_GPC['receiver'])) {
				$condition .= " AND receiver = :receiver";
				$params[':receiver'] = $_GPC['receiver'];
			}
			$list = pdo_fetchall("SELECT * FROM ".tablename('card_members_coupon')." WHERE $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('card_members_coupon') . " WHERE $condition", $params);
			$pager = pagination($total, $pindex, $psize);

			if (!empty($list)) {
				foreach ($list as $row) {
					$couponids[] = $row['couponid'];
				}
				$coupons = pdo_fetchall("SELECT id, title FROM ".tablename('card_coupon')." WHERE id IN (".implode(',', $couponids).")", array(), 'id');
			}
		}
		include $this->template('coupon');
	}

	public function doMobileCard () {
		global $_W, $_GPC;
		checkauth();
		$card = pdo_fetch("SELECT * FROM ".tablename('card')." WHERE weid = '{$_W['weid']}'");
		if (!empty($card)) {
			$card['color'] = iunserializer($card['color']);
			$card['background'] = iunserializer($card['background']);
			$card['fields'] = iunserializer($card['fields']);
		} else {
			message('此商家暂不开启会员卡功能！');
		}
		$member = pdo_fetch("SELECT id, cardsn FROM ".tablename('card_members')." WHERE from_user = :from_user AND weid = '{$_W['weid']}'", array(':from_user' => $_W['fans']['from_user']));
		if (!empty($member)) {
			header('Location: '.$_W['siteroot'] . create_url('mobile/channel', array('name' => 'home', 'weid' => $_W['weid'])));
			exit;
		}
		if (checksubmit('submit')) {
			if (!empty($member['cardsn'])) {
				message('您已经领取过会员卡！');
			}
			$cardsn = $card['format'];
			preg_match_all('/(\*+)/', $card['format'], $matchs);
			if (!empty($matchs)) {
				foreach ($matchs[1] as $row) {
					$cardsn = str_replace($row, random(strlen($row), 1), $cardsn);
				}
			}
			preg_match('/(\#+)/', $card['format'], $matchs);
			$length = strlen($matchs[1]);
			$pos = strpos($card['format'], '#');
			$cardsn = str_replace($matchs[1], str_pad($card['snpos']++, $length - strlen($number), '0', STR_PAD_LEFT), $cardsn);
			//更新卡号位数
			pdo_update('card', array('snpos' => $card['snpos']), array('weid' => $_W['weid']));
			//写入会员卡
			$data = array(
				'weid' => $_W['weid'],
				'from_user' => $_W['fans']['from_user'],
				'cardsn' => $cardsn,
				'credit1' => 0,
				'credit2' => 0,
				'status' => 1,
				'createtime' => TIMESTAMP,
			);
			pdo_insert('card_members', $data);
			//更新粉丝资料
			$data = array();
			if (!empty($card['fields'])) {
				foreach ($card['fields'] as $row) {
					if (!empty($row['require']) && empty($_GPC[$row['bind']])) {
						message('请输入'.$row['title'].'！');
					}
					$data[$row['bind']] = $_GPC[$row['bind']];
				}
			}
			fans_update($_W['fans']['from_user'], $data);
			message('会员卡领取成功！', create_url('mobile/channel', array('name' => 'home', 'weid' => $_W['weid'])), 'success');
		}
		$card['background']['image'] = $card['background']['background'] == 'user' ? $_W['attachurl'] . $card['background']['image'] : $_W['siteroot'] . 'source/modules/member/images/card/'.$card['background']['image'].'.png';
		include $this->template('card');
	}

	public function doMobileEntrycoupon() {
		global $_W, $_GPC;
		checkauth();
		$type = array(
			1 => '现金券',
			2 => '折扣券',
			3 => '体验券',
			4 => '礼品券',
			5 => '特价券',
			6 => '换购券'
		);
		$pastlist = pdo_fetchall("SELECT * FROM ".tablename('card_coupon')." WHERE status = '1' AND endtime < '".TIMESTAMP."' AND endtime > '".(strtotime('-1 month'))."' AND weid = '{$_W['weid']}'");
		$list = pdo_fetchall("SELECT *, (endtime - starttime) AS time FROM ".tablename('card_coupon')." WHERE status = '1' AND weid = '{$_W['weid']}' ORDER BY time ASC");
		$mylist = pdo_fetchall("SELECT a.status, b.title, b.type, b.id, a.id AS mycouponid FROM ".tablename('card_members_coupon')." AS a LEFT JOIN ".tablename('card_coupon')." AS b ON a.couponid = b.id WHERE a.from_user = :from_user AND a.weid = :weid", array(':weid' => $_W['weid'], ':from_user' => $_W['fans']['from_user']));
		include $this->template('mycoupon');
	}

	public function doMobileMycard() {
		global $_W, $_GPC;
		checkauth();
		$card = pdo_fetch("SELECT * FROM ".tablename('card')." WHERE weid = '{$_W['weid']}'");
		if (!empty($card)) {
			$card['color'] = iunserializer($card['color']);
			$card['background'] = iunserializer($card['background']);
		}
		$member = pdo_fetch("SELECT id, cardsn, createtime FROM ".tablename('card_members')." WHERE from_user = :from_user AND weid = '{$_W['weid']}'", array(':from_user' => $_W['fans']['from_user']));
		include $this->template('mycard');
	}

	public function doMobileGetCoupon() {
		global $_W, $_GPC;
		checkauth();
		$id = intval($_GPC['id']);
		$coupon = pdo_fetch("SELECT * FROM ".tablename('card_coupon')." WHERE id = :id", array(':id' => $id));
		if (!empty($coupon['starttime']) && $coupon['starttime'] > TIMESTAMP) {
			message('此优惠券还未开始！');
		}
		$coupon['endtime'] = $coupon['starttime'] == $coupon['endtime'] ? $coupon['endtime'] + 86400 : $coupon['endtime'];
		if (!empty($coupon['endtime']) && $coupon['endtime'] < TIMESTAMP) {
			message('此优惠券已经结束！');
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('card_members_coupon')." WHERE weid = :weid AND couponid = :couponid", array(':weid' => $_W['weid'], ':couponid' => $id));
		if ($coupon['total'] != -1 && $coupon['total'] == 0) {
			message('抱歉，该优惠券已经被领取完！');
		}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('card_members_coupon')." WHERE weid = :weid AND from_user = :from_user AND couponid = :couponid", array(':weid' => $_W['weid'], ':from_user' => $_W['fans']['from_user'], ':couponid' => $id));
		if (!empty($total) && $total >= $coupon['pretotal']) {
			message('抱歉，您已经领取了该优惠券的最大可领数！');
		}
		$data = array(
			'weid' => $_W['weid'],
			'from_user' => $_W['fans']['from_user'],
			'couponid' => $id,
			'status' => 1,
			'createtime' => TIMESTAMP,
		);
		pdo_insert('card_members_coupon', $data);
		//更新优惠券数量
		pdo_update('card_coupon', array('total' => $coupon['total'] - 1), array('id' => $id));
		message('领取优惠券成功！', $this->createMobileUrl('entrycoupon'), 'success');
	}

	public function doMobileUseCoupon() {
		global $_W, $_GPC;
		checkauth();
		$id = intval($_GPC['id']);
		$item = pdo_fetch("SELECT id, status, couponid, consumetime FROM ".tablename('card_members_coupon')." WHERE weid = :weid AND from_user = :from_user AND id = :id", array(':weid' => $_W['weid'], ':from_user' => $_W['fans']['from_user'], ':id' => $id));
		if (empty($item)) {
			message('您尚未领取该优惠券！');
		}
		$coupon = pdo_fetch("SELECT * FROM ".tablename('card_coupon')." WHERE id = :id", array(':id' => $item['couponid']));
		if (!empty($coupon['starttime']) && $coupon['starttime'] > TIMESTAMP) {
			message('此优惠券还未开始！');
		}
		if (!empty($coupon['endtime']) && $coupon['endtime'] < TIMESTAMP) {
			message('此优惠券已经结束！');
		}

		if (checksubmit('submit')) {
			if (empty($_GPC['password'])) {
				message('请输入验证密码！');
			}
			$password = member_hash($_GPC['password'], '');
			$row = pdo_fetch("SELECT id, name FROM ".tablename('card_password')." WHERE password = :password", array(':password' => $password));
			if (!empty($row)) {
				pdo_update('card_members_coupon', array(
					'status' => 2,
					'receiver' => $row['name'],
					'consumetime' => TIMESTAMP,
				), array('weid' => $_W['weid'], 'from_user' => $_W['fans']['from_user'], 'id' => $id));
				message('消费成功！', $this->createMobileUrl('entrycoupon'), 'success');
			} else {
				message('消费密码验证失败，请重试！', $this->createMobileUrl('entrycoupon'), 'error');
			}
		}
		include $this->template('usecoupon');
	}

	public function doMobileCharge() {
		global $_W, $_GPC;
		checkauth();
		if( (empty($_W['account']['payment']['alipay']['switch']) || empty($_W['account']['payment']['alipay']['secret'])) &&
			(empty($_W['account']['payment']['wechat']['switch']) || empty($_W['account']['payment']['wechat']['secret']))) {
			message('还没有提供支付方式, 请联系' . $_W['account']['name']);
		}

		$alipay = $_W['account']['payment']['alipay'];
		if(empty($alipay)) {
			message('还没有提供支付方式, 请联系' . $_W['account']['name']);
		}
		if(checksubmit()) {
			$fee = floatval($_GPC['fee']);
			if($fee < 10) {
				//message('支付金额不能小于 10 元');
			}
			//关闭余额支付
			$_W['account']['payment']['credit']['switch'] = 0;
			$params['tid'] = TIMESTAMP.random(10, 1);
			$params['user'] = $_W['fans']['from_user'];
			$params['fee'] = $fee;
			$params['title'] = $_W['account']['name'] . "用户充值{$fee}";
			$this->pay($params);
		}
		include $this->template('charge');
	}

	public function doMobileMyCredit() {
		global $_W, $_GPC;
		checkauth();
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('card_log')." WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' AND type = '3' ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, array(), 'id');
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('card_log') . " WHERE weid = '{$_W['weid']}' AND from_user = '{$_W['fans']['from_user']}' AND type = '3'");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('mycredit');
	}

	public function payResult($params) {
		global $_W;
		$fee = floatval($params['fee']);
		$sql = 'UPDATE ' . tablename('card_members') . " SET `credit2`=`credit2`+{$fee} WHERE `weid`=:weid AND `from_user`=:openid";
		$pars = array();
		$pars[':weid'] = $params['weid'];
		$pars[':openid'] = $params['user'];
		pdo_query($sql, $pars);
		//写入充值日志
		pdo_insert('card_log', array(
			'weid' => $_W['weid'],
			'from_user' => $params['user'],
			'type' => 3,
			'content' => '充值'.$fee.'元',
			'createtime' => TIMESTAMP,
		));
		if ($params['from'] == 'return') {
			message('充值成功！', '../../' . create_url('mobile/channel', array('name' => 'home', 'weid' => $params['weid'])), 'success');
		}
	}

}