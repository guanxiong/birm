<?php

defined('IN_IA') or exit('Access Denied');

class WeizpModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		
		$reply = pdo_fetch("SELECT * FROM ".tablename('weizp_reply')." WHERE rid = :rid", array(':rid' => $rid));
	
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_W, $_GPC;
		$reid = intval($_GPC['reply_id']);
		
		$data = array(
			'rid' => $rid,
			'cover' => $_GPC['cover'],
			'title' => $_GPC['title'],
			'description' => $_GPC['description'],
			'content' => $_GPC['content']
		);
		if (empty($reid)) {
			pdo_insert('weizp_reply', $data);
		} else {
			pdo_update('weizp_reply', $data, array('id' => $reid));
		}
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		pdo_delete('weizp_reply', array('rid' => $rid));
	}


}