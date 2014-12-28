<?php
/**
 * 微官网模块定义
 *
 * @author WeEngine Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class SiteModule extends WeModule {

	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W, $_GPC;
		$isfill = false;
		if (!empty($rid)) {
			$reply = pdo_fetchall("SELECT * FROM ".tablename('article_reply')." WHERE rid = :rid AND articleid > 0", array(':rid' => $rid));
			if (!empty($reply)) {
				foreach ($reply as $row) {
					$ids[$row['articleid']] = $row['articleid'];
				}
				$article = pdo_fetchall("SELECT id, title, thumb, content FROM ".tablename('article')." WHERE id IN (".implode(',', $ids).")", array(), 'id');
				$isfill = $reply[0]['isfill'];
			} else {
				$isfill = pdo_fetchcolumn("SELECT isfill FROM ".tablename('article_reply')." WHERE rid = :rid", array(':rid' => $rid));
			}
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
		if (!empty($_GPC['ids'])) {
			foreach ($_GPC['ids'] as $id) {
				$isexists = pdo_fetchcolumn("SELECT id FROM ".tablename('article_reply')." WHERE rid = :rid AND articleid = :articleid", array(':articleid' => $id, ':rid' => $rid));
				if (!$isexists) {
					pdo_insert('article_reply', array(
						'rid' => $rid,
						'articleid' => $id,
						'isfill' => 0,
					));
				}
			}
		}
		if (isset($_GPC['isfill'])) {
			$isexists = pdo_fetchcolumn("SELECT id FROM ".tablename('article_reply')." WHERE rid = :rid AND articleid = '0'", array(':rid' => $rid));
			if (empty($isexists)) {
				pdo_insert('article_reply', array(
					'rid' => $rid,
					'articleid' => 0,
					'isfill' => intval($_GPC['isfill']),
				));
			} else {
				pdo_update('article_reply', array(
					'isfill' => intval($_GPC['isfill']),
				), array('articleid' => 0, 'rid' => $rid));
			}
		}
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}
	
	public function doDelete() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$rid = intval($_GPC['rid']);
		if (!empty($id) && !empty($rid)) {
			pdo_delete('article_reply', array('id' => $id, 'rid' => $rid));
		}
		message('删除成功！', referer(), 'success');
	}
}
