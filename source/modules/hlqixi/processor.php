<?php
/**
 * 语音回复处理类
 *
 * 疯狂却不失细腻——厦门火池网络		www.weixiamen.cn
 */
defined('IN_IA') or exit('Access Denied');

class hlqixiModuleProcessor extends WeModuleProcessor {
	
	public $tablename = 'hlqixi_reply';
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM ".tablename($this->tablename)." WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		
		
		
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		$array=array(
			'Title' => $title,
			'Description' => $row['description'],
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			'Url' => $this->createMobileUrl('index', array('id' => $rid)),
		);
		
		return $this->respNews($array);
	}
}
