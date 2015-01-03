<?php

/**

 * 微相册模块定义

 * @author WeNewstar Team

 * @url http://www.we7.cc

 */

defined('IN_IA') or exit('Access Denied');



class LxyecowzpModule extends WeModule {
	public $table_reply = 'lxy_ecowzp_reply';
	public $indextable='lxy_ecowzp';
	public function fieldsFormDisplay($rid = 0) {

		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0

		global $_W, $_GPC;

		if (!empty($rid)) {

			$reply = pdo_fetchall("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid", array(':rid' => $rid));

			if (!empty($reply)) {

				foreach ($reply as $row) {

					$lxyecowzpids[$row['hid']] = $row['hid'];

				}

				$lxyecowzp = pdo_fetchall("SELECT id, title, thumb, content FROM ".tablename($this->indextable)." WHERE id IN (".implode(',', $lxyecowzpids).")", array(), 'id');

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

		if (!empty($_GPC['hid'])) {

			foreach ($_GPC['hid'] as $aid) {

				pdo_insert($this->table_reply, array(

					'rid' => $rid,

					'hid' => $aid,

				));

			}

		}

	}



	public function ruleDeleted($rid) {

		//删除规则时调用，这里 $rid 为对应的规则编号

	}
}

