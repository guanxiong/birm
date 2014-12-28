<?php
/**
 * 留言板模块定义
 *
 * @author daduing
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class MsgModule extends WeModule {
	public $tablename = 'msg';
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
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
		empty($_GPC['msg'])?$_GPC['msg'] = '欢迎给我们提意见！请直接回复留言：':'';
		empty($_GPC['msg_succ'])?$_GPC['msg_succ'] = '谢谢您的留言。':'';
		empty($_GPC['msg_fail'])?$_GPC['msg_fail'] = '留言未保存，请重试。':'';
		$insert = array(
			'rid' => $rid,
			'weid' => $_W['weid'],
			'msg' => $_GPC['msg'],
			'msg_succ' => $_GPC['msg_succ'],
			'msg_fail' => $_GPC['msg_fail']
		);
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
		$replies = pdo_fetchall("SELECT id,rid FROM ".tablename('msg')." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
          	foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete('msg_reply', " id IN ('".implode("','", $deleteid)."')");
		pdo_delete($this->tablename, "rid =".$rid."");          
		return true;
	}


}