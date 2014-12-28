<?php
/**
 * 地方话模块定义
 *
 * @author 冯齐跃
 * @url http://wx.admin9.com/
 */
defined('IN_IA') or exit('Access Denied');

class DialectModule extends WeModule {

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if($rid) {
			$activity = pdo_fetch("SELECT id, title, photo, smalltext FROM " . tablename('feng_dialect') . " WHERE rid = :rid", array(':rid' => $rid));
			$activity['photo'] = toimage($activity['photo']);
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		global $_W, $_GPC;
		echo $id = intval($_GPC['activity']);
		if(!empty($id)) {
			$sql = 'SELECT * FROM ' . tablename('feng_dialect') . " WHERE `id`=:id";
			$params = array();
			$params[':id'] = $id;
			$activity = pdo_fetch($sql, $params);
			return ;
			if(!empty($activity)) {
				return '';
			}
		}
		return '没有选择合适的内容';
	}

	public function fieldsFormSubmit($rid) {
		global $_GPC;
		$id = intval($_GPC['activity']);
		$record = array();
		$record['rid'] = $rid;
		$reply = pdo_fetch("SELECT * FROM " . tablename('feng_dialect') . " WHERE id = :id", array(':id' => $id));
		if($reply) {
			pdo_update('feng_dialect', $record, array('id' => $reply['id']));
		}
	}

	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$dat['lottery_id']=$_GPC['lottery_id'];
			$dat['thumb']=$_GPC['thumb'];
			$dat['thumb_url']=$_GPC['thumb_url'];
			$dat['wei']=$_GPC['wei'];
			$dat['account']=$_GPC['account'];
			$dat['summary']=trim($_GPC['summary']);
			$dat['share_type']=intval($_GPC['share_type']);
			if($this->saveSettings($dat)) {
				message('保存成功', 'refresh');
			}
		}
		if(!isset($settings['summary'])) {
			$settings['summary'] = "点击参与竞猜参加竞猜活动，选择你预测进入小组的球队进行预测。\r\n将结果分享到朋友圈可参与抽奖活动。";
		}
		//这里来展示设置项表单
		include $this->template('setting');
	}
}