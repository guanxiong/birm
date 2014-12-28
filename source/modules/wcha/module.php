<?php
/**
 * 微餐饮查单模块定义
 *
 * @author 超级无聊
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class WchaModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W, $_GPC;
 		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename('wcha_reply')." WHERE rid = :rid ", array(':rid' => $rid));
		}
		include $this->template('from');
	}
	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_W, $_GPC;
		 
		$data = array(
 			'rid' => $rid,
			'openidstr' => $_GPC['openidstr'],
			'wtype' => $_GPC['wtype'],
		);
		$reply = pdo_fetch("SELECT * FROM ".tablename('wcha_reply')." WHERE rid = :rid ", array(':rid' => $rid));
		if ($reply==false) {
			pdo_insert('wcha_reply', $data);
		} else {
			unset($data['rid']);
			pdo_update('wcha_reply', $data, array('rid' => $rid));
		}
		return true;
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}
	
	public function doDelete() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$rid = intval($_GPC['rid']);
		if (!empty($id) && !empty($rid)) {
			pdo_delete('wcha_reply', array('id' => $id, 'rid' => $rid));
		}
 	}
}