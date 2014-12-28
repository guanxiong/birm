<?php
/**
 * 万能卡模块定义
 * * @author 石头鱼

 * @url http://www.00393.com/
 */
defined('IN_IA') or exit('Access Denied');

class sharecardsModule extends WeModule {
	public $name = 'sharecardsModule';
	public $title = '万能卡';
	public $ability = '';
	public $table_reply  = 'sharecards_reply';
	public $table_card   = 'sharecards_date';
	public $table_styles = 'sharecards_category';

	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		$category = pdo_fetchall("SELECT * FROM ".tablename($this->table_styles)." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC");
		if (!$category) {
			message('请先设置预设词才能添加活动！', $this->createWebUrl('sharecardscategory'), 'success');
		}
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
 		}		
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;
		include $this->template('sharecards/form');

	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
		$insert = array(
			'rid' => $rid,
			'cid' => $_GPC['cid'],
            'title' => $_GPC['title'],
			'picture' => $_GPC['picture'],
			'bgimage' => $_GPC['bgimage'],
			'description' => $_GPC['description'],
			'start_time' => strtotime($_GPC['start_time']),
			'end_time' => strtotime($_GPC['end_time']),
			'status' => $_GPC['status']
		);
		if (empty($id)) {
			pdo_insert($this->table_reply, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
			if(!empty($_GPC['bgimage'])){
				file_delete($_GPC['bgimage-old']);
			}else{
				unset($insert['bgimage']);
			}
			pdo_update($this->table_reply, $insert, array('id' => $id));
		}

	}

	public function doDeleteImage() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, picture FROM " . tablename($this->table_reply) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，回复不存在或是已经被删除！', '', 'error');
		}
		if (pdo_update($this->table_reply, array('picture' => ''), array('id' => $id))) {
			file_delete($row['picture']);
		}
		if (pdo_update($this->table_reply, array('bgimage' => ''), array('id' => $id))) {
			file_delete($row['bgimage']);
		}
		message('删除图片成功！', '', 'success');
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;
		$replies = pdo_fetchall("SELECT id, picture, bgimage FROM ".tablename($this->table_reply)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);
				file_delete($row['bgimage']);
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->table_reply, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}

}