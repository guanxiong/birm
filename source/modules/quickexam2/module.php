<?php
/**
 * 微试卷
 * QQ群：304081212
 * 作者：微动力, 547753994
 *
 * 网站：www.xuehuar.com
 */

defined('IN_IA') or exit('Access Denied');

class QuickExam2Module extends WeModule {
	public $table_reply = 'quickexam2_reply';
	public $table_paper = 'quickexam2_paper';

	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		if ($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename($this->table_reply) . " WHERE rid = :rid", array(':rid' => $rid));
			$paper = pdo_fetch("SELECT * FROM " . tablename($this->table_paper) ." WHERE `weid` = :weid AND `paper_id` = :paper_id", array(':weid'=>$_W['weid'], ':paper_id'=>$reply['paper_id']));
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		global $_W, $_GPC;
		if (isset($_GPC['paper_id'])) {
			$paper_id = intval($_GPC['paper_id']);
			if (!empty($paper_id)) {
				$paper = pdo_fetch("SELECT * FROM ".tablename($this->table_paper)." WHERE `paper_id` = :paper_id", array(':paper_id' => $paper_id));
				if (!empty($paper)) {
					return;
				}
			}
		}
		return '没有选择合适的试卷';
	}

	public function fieldsFormSubmit($rid) {
		global $_GPC;
		if (isset($_GPC['paper_id'])) {
			$record = array('paper_id' => intval($_GPC['paper_id']), 'rid' => $rid);
			$reply = pdo_fetch("SELECT * FROM " .tablename($this->table_reply) . " WHERE `rid` = :rid", array(':rid' => $rid));
			if ($reply) {
				pdo_update($this->table_reply, $record, array('id' => $reply['id']));	
			} else {
				pdo_insert($this->table_reply, $record);	
			}
		}
	}

	public function ruleDeleted($rid) {
		pdo_delete($this->table_reply, array('rid' => $rid));
	}

	private function getPicUrl($url) {
		global $_W;
		if (empty($url)) {
			$r = $_W['siteroot'] . "/source/modules/quickexam2/images/default_cover.jpg";
		} else {
			$r = $_W['attachurl'] . $url;
		}
		return $r;
	}
}
