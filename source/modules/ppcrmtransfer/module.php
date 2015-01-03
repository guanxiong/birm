<?php
/**
 * 多客服转接模块定义
 *
 * @author WeNewstar Team
 * @url http://bbs.birm.co
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