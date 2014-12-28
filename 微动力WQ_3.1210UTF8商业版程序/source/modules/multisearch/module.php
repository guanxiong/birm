<?php
/**
 * 万能查询模块定义
 *
 * @author WeEngine Team
 * @url http://we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class MultisearchModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		global $_W, $_GPC;
		if (!empty($rid)) {
			$reid = pdo_fetchcolumn("SELECT reid FROM ".tablename('multisearch_reply')." WHERE rid = :rid", array(':rid' => $rid));
			$item = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = '$reid'");
		}
		include $this->template('rule');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_W, $_GPC;
		if (!empty($_GPC['reid'])) {
			pdo_delete('multisearch_reply', array('rid' => $rid));
			pdo_insert('multisearch_reply', array(
				'rid' => $rid,
				'reid' => intval($_GPC['reid']),
			));
		}
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}
	
	public function doQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('multisearch') . ' WHERE `weid`=:weid AND `title` LIKE :title';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['id'] = $row['id'];
			$r['title'] = $row['title'];
			$r['description'] = $row['description'];
			$r['thumb'] = $row['cover'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}


}