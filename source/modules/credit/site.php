<?php
/**
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

class CreditModuleSite extends WeModuleSite {

	public function doWebAward() {
		// 1. display credit
		// 2. add credit
		// 3. delete credit
		// 4. update credit
		global $_W;
		global $_GPC; // 获取query string中的参数
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		if ($operation == 'post') { // 增加或者更新兑换商品
			$award_id = intval($_GPC['award_id']);
			if (!empty($award_id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('award_list')." WHERE award_id = :award_id" , array(':award_id' => $award_id));
				if (empty($item)) {
					message('抱歉，兑换商品不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入兑换商品名称！');
				}
				if (empty($_GPC['credit_cost'])) {
					message('请输入兑换商品需要消耗的积分数量！');
				}
				if (empty($_GPC['price'])) {
					message('请输入商品实际价值！');
				}
				$credit_cost = intval($_GPC['credit_cost']);
				$price = intval($_GPC['price']);
				$amount = intval($_GPC['amount']);
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'logo' => $_GPC['logo'],
					'deadline' => $_GPC['deadline'],
					'amount' => $amount,
					'credit_cost' => $credit_cost,
					'price' => $price,
					'content' => $_GPC['content'],
					'createtime' => TIMESTAMP,
				);
				if (!empty($award_id)) {
					pdo_update('award_list', $data, array('award_id' => $award_id));
				} else {
					pdo_insert('award_list', $data);
				}
				message('商品更新成功！', create_url('site/module/award', array('name' => 'credit', 'op' => 'display')), 'success');
			}
		}
		else if ($operation == 'delete') { //删除商品
			$award_id = intval($_GPC['award_id']);
			$row = pdo_fetch("SELECT award_id FROM ".tablename('award_list')." WHERE award_id = :award_id", array(':award_id' => $award_id));
			if (empty($row)) {
				message('抱歉，商品'.$award_id.'不存在或是已经被删除！');
			}
			pdo_delete('award_list', array('award_id' => $award_id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('award_list')." WHERE weid = '{$_W['weid']}' $condition ORDER BY createtime DESC");
		}
		include $this->template('award');
	}

	public function doWebCredit() {
		// 1. display reservation
		// 2. add credit
		// 3. delete credit
		// 4. update credit
		global $_W;
		global $_GPC; // 获取query string中的参数
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'delete') { //删除兑换请求
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename('credit_request')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，编号为'.$id.'的兑换请求不存在或是已经被删除！');
			}
			pdo_delete('credit_request', array('id' => $id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$sql = "SELECT * FROM ".tablename('award_list')." as t1,".tablename('credit_request')."as t2 WHERE t1.award_id=t2.award_id AND t1.weid = '{$_W['weid']}' ORDER BY t2.createtime DESC";
			$list = pdo_fetchall($sql);
			$ar = pdo_fetchall($sql, array(), 'from_user');
			$fans = fans_search(array_keys($ar), array('realname', 'mobile', 'credit1', 'residedist'));
		}
		include $this->template('credit_request');
	}

	public function doMobileAward() {
		global $_W, $_GPC;
		checkauth();
		$award_list = pdo_fetchall("SELECT * FROM ".tablename('award_list')." WHERE weid = '{$_W['weid']}' and NOW() < deadline and amount > 0");
		$profile = fans_search($_W['fans']['from_user']);
		include $this->template('award_new');
	}
	
	public function doMobileFillInfo() {
		global $_W, $_GPC;
		checkauth();
		$award_id = intval($_GPC['award_id']);
		$profile = fans_search($_W['fans']['from_user']);
		$award_info = pdo_fetch("SELECT * FROM ".tablename('award_list')." WHERE award_id = $award_id AND weid = '{$_W['weid']}'");
		include $this->template('fillinfo_new');
	}

	public function doMobileCredit() {
		global $_W, $_GPC;
		checkauth();
		$award_id = intval($_GPC['award_id']);
		if (!empty($_GPC['award_id']))
		{
			$fans = fans_search($_W['fans']['from_user'], array('credit1'));
			$award_info = pdo_fetch("SELECT * FROM ".tablename('award_list')." WHERE award_id = $award_id AND weid = '{$_W['weid']}'");
			if ($fans['credit1'] >= $award_info['credit_cost'] && $award_info['amount'] > 0)
			{
				$data = array(
					'amount' => $award_info['amount'] - 1
				);
				pdo_update('award_list', $data, array('weid' => $_W['weid'], 'award_id' => $award_id));

				$data = array(
					'weid' => $_W['weid'],
					'from_user' => $_W['fans']['from_user'],
					'award_id' => $award_id,
					'createtime' => TIMESTAMP
				);
				pdo_insert('credit_request', $data);

				$data = array(
					'realname' => $_GPC['realname'],
					'mobile' => $_GPC['mobile'],
					'credit1' => $fans['credit1'] - $award_info['credit_cost'],
					'residedist' => $_GPC['residedist'],
				);
				fans_update($_W['fans']['from_user'], $data);

				// navigate to user profile page
				message('积分兑换成功！', create_url('mobile/module/mycredit', array('weid' => $_W['weid'], 'name' => 'credit', 'do' => 'mycredit','op' => 'display')), 'success');
			}
			else
			{
				message('积分不足或商品已经兑空，请重新选择商品！<br>当前商品所需积分:'.$award_info['credit_cost'].'<br>您的积分:'.$fans['credit1']
					. '. 商品剩余数量:' . $award_info['amount']
					. '<br><br>小提示：<br>每日签到，在线订票，宾馆预订可以赚取积分',

					create_url('mobile/module/award', array('weid' => $_W['weid'], 'name' => 'credit')), 'error');
			}
		}
		else
		{
			message('请选择要兑换的商品！', create_url('mobile/module/award', array('weid' => $_W['weid'], 'name' => 'credit')), 'error');
		}
	}


	public function doMobileMycredit() {
		global $_W, $_GPC;
		checkauth();
		$award_list = pdo_fetchall("SELECT * FROM ".tablename('award_list')." as t1,".tablename('credit_request')."as t2 WHERE t1.award_id=t2.award_id AND from_user='{$_W['fans']['from_user']}' AND t1.weid = '{$_W['weid']}' ORDER BY t2.createtime DESC");
		$profile = fans_search($_W['fans']['from_user']);
		include $this->template('mycredit_new');
	}

}
