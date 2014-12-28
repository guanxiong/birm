<?php
/**
 * 跳蚤市场模块定义
 *
 * @author yuexiage
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class TsMarkModule extends WeModule {
	public $name = 'TsMark';
	public $title = '跳骚市场';
	public $tablename = 'tsmark_reply';
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_GPC, $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
		}
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return true;
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_GPC, $_W;
		
		$id = intval($_GPC['reply_id']);
		$insert = array(
				'rid' => $rid,
				'title' => $_GPC['title'],
				'description' => $_GPC['description'],
				'thumb' => $_GPC['picture'],
				'isshow'=>$_GPC['isshow'],
		);
		//处理图片
		if (!empty($_GPC['picture'])) {
			file_delete($_GPC['picture-old']);
		} else {
			unset($insert['thumb']);
		}
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
		return true;
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;
		$replies = pdo_fetchall("SELECT id,rid FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}
			pdo_delete('tsmark', "rid =".$rid."");
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}

}