<?php
/**
 * 趣味测试模块定义
 *
 * @author 冯齐跃
 * @url http://www.admin9.com
 */
defined('IN_IA') or exit('Access Denied');

class Feng_testingModule extends WeModule {

	// 基本设置
	public function settingsDisplay($settings) {
		global $_GPC, $_W;
		if(checksubmit()) {
			$dat = $_GPC['add'];
			$dat['istesting'] = intval($dat['istesting']);
			if(isset($_GPC['banner'])){
				$dat['banner'] = $_GPC['banner'];
			}
			$this->saveSettings($dat);
			message('操作成功！','refresh');
		}
		// if(!isset($settings['banner'])){
		// 	$settings['banner']='./source/modules/feng_testing/template/mobile/image/type_banner.jpg';
		// }
		include $this->template('setting');
		
	}

	// 要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	public function fieldsFormDisplay($rid = 0) {
		global $_W, $_GPC;
		$isfill = false;
		if (!empty($rid)) {
			$reply = pdo_fetchall("SELECT * FROM ".tablename('feng_testingreply')." WHERE rid = :rid AND testingid > 0", array(':rid' => $rid));
			if (!empty($reply)) {
				foreach ($reply as $row) {
					$ids[$row['testingid']] = $row['testingid'];
				}
				$article = pdo_fetchall("SELECT id,title,photo,smalltext FROM ".tablename('feng_testing')." WHERE id IN (".implode(',', $ids).")", array(), 'id');
				$isfill = $reply[0]['isfill'];
			} else {
				$isfill = pdo_fetchcolumn("SELECT isfill FROM ".tablename('feng_testingreply')." WHERE rid = :rid", array(':rid' => $rid));
			}
		}
		include $this->template('rule');
	}

	// 规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
	public function fieldsFormValidate($rid = 0) {
		return '';
	}

	// 规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	public function fieldsFormSubmit($rid) {
		
		global $_W, $_GPC;
		if (!empty($_GPC['ids'])) {
			foreach ($_GPC['ids'] as $id) {
				$isexists = pdo_fetchcolumn("SELECT id FROM ".tablename('feng_testingreply')." WHERE rid = :rid AND testingid = :testingid", array(':testingid' => $id, ':rid' => $rid));
				if (!$isexists) {
					pdo_insert('feng_testingreply', array(
						'rid' => $rid,
						'testingid' => $id,
						'isfill' => 0,
					));
				}
			}
		}
		if (isset($_GPC['isfill'])) {
			$isexists = pdo_fetchcolumn("SELECT id FROM ".tablename('feng_testingreply')." WHERE rid = :rid AND testingid = '0'", array(':rid' => $rid));
			if (empty($isexists)) {
				pdo_insert('feng_testingreply', array(
					'rid' => $rid,
					'testingid' => 0,
					'isfill' => intval($_GPC['isfill']),
				));
			} else {
				pdo_update('feng_testingreply', array(
					'isfill' => intval($_GPC['isfill']),
				), array('testingid' => 0, 'rid' => $rid));
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
			pdo_delete('feng_testingreply', array('id' => $id, 'rid' => $rid));
		}
		message('删除成功！', referer(), 'success');
	}
}