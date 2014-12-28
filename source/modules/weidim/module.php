<?php
/**
 * 微预约模块定义
 *
 * @author 微鼎
 * @url http://www.weidim.com/
 */
defined('IN_IA') or exit('Access Denied');

class WeidimModule extends WeModule {
	public $name = 'WeidimModule';
	public $title = '微预约';
	public $ability = '';
	public $table_reply  = 'weidim_reply';
	public $table_item   = 'weidim_item';
	public $table_order  = 'weidim_order';

	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		if (!empty($rid)) {
			$reply = pdo_fetch("SELECT * FROM ".tablename($this->table_reply)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));	
			$items = pdo_fetchall("SELECT * FROM ".tablename($this->table_item)." WHERE rid = :rid ORDER BY `orderid` ASC", array(':rid' => $rid));
 		} 
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;
		include $this->template('weidim/form');
		
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
			'picture' => $_GPC['picture'],
			'headimage' => $_GPC['headimage'],
			'description' => $_GPC['description'],					
			'address' => $_GPC['address'],
			'tel' => $_GPC['tel'],			
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
			if(!empty($_GPC['headimage'])){
				file_delete($_GPC['headimage-old']);
			}else{
				unset($insert['headimage']);
			}
			pdo_update($this->table_reply, $insert, array('id' => $id));
		}
		if (!empty($_GPC['item-fieldname'])) {
			foreach ($_GPC['item-fieldname'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$update = array(
					'rid' => $rid,
					'type' => intval($_GPC['item-type'][$index]),
					'fieldname' => $_GPC['item-fieldname'][$index],	
					'fieldcontent' => $_GPC['item-fieldcontent'][$index],
					'isdefault' => intval($_GPC['item-isdefault'][$index]),
					'orderid' => intval($_GPC['item-orderid'][$index])
				);
				pdo_update('weidim_item', $update, array('id' => $index));
			}
		}
		//处理添加
		//print_r($_GPC);
		if (!empty($_GPC['item-fieldname-new'])) {
			foreach ($_GPC['item-fieldname-new'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$insert = array(
					'rid' => $rid,										
					'type' => intval($_GPC['item-type-new'][$index]),
					'fieldname' => $_GPC['item-fieldname-new'][$index],
					'fieldcontent' => $_GPC['item-fieldcontent-new'][$index],
					'isdefault' => intval($_GPC['item-isdefault-new'][$index]),
					'orderid' => intval($_GPC['item-orderid-new'][$index])
				);
				pdo_insert('weidim_item', $insert);
			}
		}
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;
		$replies = pdo_fetchall("SELECT id, picture, headimage FROM ".tablename($this->table_reply)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);
				file_delete($row['headimage']);
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->table_reply, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}	
}