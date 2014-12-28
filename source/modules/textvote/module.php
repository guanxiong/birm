<?php
/**
 * 文本投票模块定义
 *
 * @author nbnat.com
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class TextvoteModule extends WeModule {
	public $table_vote='nb_textvote';
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		$sql = "SELECT * FROM " . tablename($this->table_vote) . " WHERE `rid`=:rid LIMIT 1";
		$reply = pdo_fetch($sql, array(':rid' => $rid));
		
		$reply['config']=json_decode($reply['config']);
		$reply['start_time'] = empty($reply['start_time']) ? strtotime(date('Y-m-d')) : $reply['start_time'];
		$reply['end_time'] = empty($reply['end_time']) ? TIMESTAMP : $reply['end_time'] + 86399;
		$reply['checkkeyword'] = empty($reply['checkkeyword']) ? "分享排名" : $reply['checkkeyword'];
		include $this->template('form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
		$config_list = trim($_GPC['config']);  
        $config_arr = explode("\r\n", $config_list);
		for ($i=0;$i<count($config_arr);$i++){
			$res_arr[]=0;
		}
		$config=json_encode($config_arr);
		$insert = array(
			'rid' => $rid,
			'config'=>str_replace(" ","",$config),
			'result'=>json_encode($res_arr),
			'start_time' => strtotime($_GPC['start_time']),
			'end_time' => strtotime($_GPC['end_time']),
			'status' => $_GPC['status']
		);
		if (empty($id)) {
			pdo_insert($this->table_vote, $insert);
		} else {			
			pdo_update($this->table_vote, $insert, array('id' => $id));
		}		

		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
		global $_W;
		$replies = pdo_fetchall("SELECT id FROM ".tablename($this->table_vote)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {								
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->table_vote, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}

	public function settingsDisplay($settings) {
		//点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
		//在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
	}

}