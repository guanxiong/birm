<?php
/**
 * 多客服转接模块定义
 *
 * @author WeEngine Team
 * @url http://bbs.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class PpcrmtransferModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		return '';
	}

	public function fieldsFormSubmit($rid) {
	}

	public function ruleDeleted($rid) {
	}
}