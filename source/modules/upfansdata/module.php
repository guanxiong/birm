<?php
/**
 * 更新资料有礼模块定义
 *
 * @author 微鼎
 * @url http://www.weidim.com/
 */
defined('IN_IA') or exit('Access Denied');

class upfansdataModule extends WeModule {
	public $name = 'upfansdataModule';
	public $title = '更新资料有礼';
	public $ability = '';
	public $table_reply  = 'upfansdata_reply';
	public $table_list   = 'upfansdata_list';

	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;			
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));	
			if (!empty($reply)) {
			  $reply['fields'] = iunserializer($reply['fields']);
		    }
 		} 
		$fields = fans_fields();
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;
		$reply['checkkeyword'] = empty($reply['checkkeyword']) ? "分享排名" : $reply['checkkeyword'];
		include $this->template('upfansdata/form');
		
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
            'title' => $_GPC['title'],
			'credit' => $_GPC['credit'],
			'picture' => $_GPC['picture'],
			'upfansdataurl' => $_GPC['upfansdataurl'],
			'description' => $_GPC['description'],			
			'content' => $_GPC['content'],	
			'start_time' => strtotime($_GPC['start_time']),
			'end_time' => strtotime($_GPC['end_time']),
			'status' => $_GPC['status']
		);
		if (!empty($_GPC['fields'])) {
				foreach ($_GPC['fields']['title'] as $index => $row) {
					if (empty($_GPC['fields']['title'])) {
						continue;
					}
					$insert['fields'][] = array(
						'title' => $_GPC['fields']['title'][$index],
						'require' => intval($_GPC['fields']['require'][$index]),
						'bind' => $_GPC['fields']['bind'][$index],
					);
				}
				$insert['fields'] = iserializer($insert['fields']);
		}
		if (empty($id)) {
			pdo_insert($this->table_reply, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
			pdo_update($this->table_reply, $insert, array('id' => $id));
		}		

	}

	public function doDeleteImage() {
		global $_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id, thumb FROM " . tablename($this->tablename) . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，活动不存在或是已经被删除！', '', 'error');
		}
		if (pdo_update($this->tablename, array('thumb' => ''), array('id' => $id))) {
			file_delete($row['thumb']);
		}
		message('删除图片成功！', '', 'success');
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;
		$replies = pdo_fetchall("SELECT id, picture FROM ".tablename($this->table_reply)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);				
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->table_reply, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}

}