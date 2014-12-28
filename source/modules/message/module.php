<?php
/**
 * 留言墙模块定义
 *
 * @author 超级无聊
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class MessageModule extends WeModule {
	public $name = 'Message';
	public $tablename = 'message_reply';	
	public function fieldsFormDisplay($rid = 0) {
        
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		return '';

	}

	public function ruleDeleted($rid) {
		return true;
	}
	
 
	public function dolist(){
		global $_GPC, $_W;
		checklogin();
		$weid = intval($_W['weid']);
		if (checksubmit('verify') && !empty($_GPC['select'])) {
			pdo_update('message_list', array('isshow' => 1, 'create_time' => TIMESTAMP), " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('审核成功！', create_url('site/module', array('do' => 'list', 'name' => 'message', 'weid' => $weid, 'page' => $_GPC['page'])));
		}
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('message_list', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module', array('do' => 'list', 'name' => 'message', 'weid' => $weid, 'page' => $_GPC['page'])));
		}
		$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$message = pdo_fetch("SELECT id, isshow, weid FROM ".tablename('message_reply')." WHERE weid = '{$weid}' LIMIT 1");
		$list = pdo_fetchall("SELECT * FROM ".tablename('message_list')." WHERE weid = '{$message['weid']}' AND isshow = '$isshow' ORDER BY create_time DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('message_list') . " WHERE weid = '{$message['weid']}' AND isshow = '$isshow'");
			$pager = pagination($total, $pindex, $psize);

			foreach ($list as &$row) {
				$row['content'] = emotion($row['content']);
				$userids[] = $row['from_user'];
			}
			unset($row);
		}
		include $this->template('list');	
	}
	
	public function doSet(){
		global $_GPC, $_W;
		checklogin();
		if($_GPC['action']=='save'){
			$id = intval($_GPC['id']);
			$data=array(
				'weid'=>$_W['weid'],
				'status'=>intval($_GPC['status']),
				'isshow'=>intval($_GPC['isshow']),
			);
			if($id==0){
				$temp=pdo_insert('message_reply',$data);
			}else{	
				$temp=pdo_update('message_reply',$data,array('weid'=>$_W['weid'],'id'=>$id));
			}
			if($temp===false){
				message('数据保存错误，请稍后再试试');
			}else{
				message('数据更新成功！', $this->createWebUrl('Set'), 'success');
			}		
		}
		$message = pdo_fetch("SELECT id, isshow FROM ".tablename('message_reply')." WHERE weid = '{$_W['weid']}' LIMIT 1");
		if($message==false){
			$message=array(
				'id'=>0,
				'status'=>1,
				'isshow'=>1,
			);
		}
		include $this->template('set');	
	}

}