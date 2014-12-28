<?php
/**
 * 微拍模块定义
 *
 * @author 清逸
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class QyweipaiwebModule extends WeModule {
	public $tablename = 'qywpweb';
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
		global $_GPC, $_W;
 
        $id = intval($_GPC['reply_id']);
		empty($_GPC['msg'])?$_GPC['msg'] = '欢迎进入微拍活动！':'';
		empty($_GPC['msg_succ'])?$_GPC['msg_succ'] = '参与活动成功。':'';
		empty($_GPC['msg_fail'])?$_GPC['msg_fail'] = '提交失败，请重试。':'';
		$insert = array(
			'rid' => $rid,
			'weid' => $_W['weid'],
			'maxnum' => $_GPC['maxnum'],
			'pwd' => '111111',
			'mpwd' => $_GPC['mpwd'],
			'picture1' => $_GPC['picture1'],
			'picture2' => $_GPC['picture2'],
			'msg' => $_GPC['msg'],
			'msg_succ' => $_GPC['msg_succ'],
			'msg_fail' => $_GPC['msg_fail'],
			'status' => intval($_GPC['wpstatus']),
			'lyok' => intval($_GPC['wplyok']),
			'ispwd' => intval($_GPC['ispwd'])
		);
		if (empty($id)) {
			$id=pdo_insert($this->tablename, $insert);
		} else {
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
		$filenamep = 'qywp/' . $rid . '/pwd.txt';
		$pwd1='lyqywp111111';
		file_write($filenamep, $pwd1);
		$filename = 'qywp/' . $rid . '/moban.txt';
		$s='lyqywp<s>'.$_W['attachurl']. $_GPC['picture1'].'</s>';
		$h=$s.'<h>'.$_W['attachurl']. $_GPC['picture2'].'</h>';
		file_write($filename, $h);

      	return true;
	}
	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;
		$replies = pdo_fetchall("SELECT id,rid FROM ".tablename('qywpweb')." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
          	foreach ($replies as $index => $row) {
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete('qywpweb_reply', " id IN ('".implode("','", $deleteid)."')");
		pdo_delete($this->tablename, "rid =".$rid."");          
		return true;
	}


}