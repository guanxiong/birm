<?php
/**
 * 通用表单模块定义
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com
 */
defined('IN_IA') or exit('Access Denied');

class SlotmacModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		global $_W;
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		$slotlists = pdo_fetchall('SELECT id,name FROM '.tablename('slotmac').' WHERE weid=:weid AND stat=:st', array(':weid'=>$_W['weid'], ':st'=>1));
		if(!empty($rid)){
			$repinf = pdo_fetch('SELECT * FROM '.tablename('slotmac_rep').' WHERE weid=:wid AND rid=:rid', array(':wid'=>$_W['weid'],':rid'=>$rid));
		}
		include $this->template('replyset');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return true;
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_W, $_GPC;
		$weid = $_W['weid'];
		$repid = $_GPC['reply_id'];
		$data = array(
				'rid' => $rid,
				'reptitle' => $_GPC['reptitle'],
				'repimg' => $_GPC['picture'],
				'repinfo' => $_GPC['repinfo'],
				'repactive' => $_GPC['repactive'],
				'weid' => $_W['weid']
			);
		if(empty($repid)){
			pdo_insert('slotmac_rep', $data);
		} else {
			pdo_update('slotmac_rep', $data, array('id'=>$repid));
		}

	}

	public function ruleDeleted($rid) {
		global $_W;
		//删除规则时调用，这里 $rid 为对应的规则编号
		$sql = 'DELETE FROM '.tablename('slotmac_rep')." WHERE id='{$rid}' AND weid='{$_W['weid']}'";
		pdo_query($sql);
	}
}