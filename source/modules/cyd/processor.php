<?php
/**
 * 语音回复处理类
 *
 * [WDL] Copyright (c) 2013 B2CTUI.COM
 */
defined('IN_IA') or exit('Access Denied');

class CydModuleProcessor extends WeModuleProcessor {

	public function respond() {
		global $_W;
		$rid = $this->rule;
		$sql = "SELECT * FROM " . tablename('cyd_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		if (empty($row['id'])) {
			return array();
		}
		
		
		
		$title = pdo_fetchcolumn("SELECT name FROM ".tablename('rule')." WHERE id = :rid LIMIT 1", array(':rid' => $rid));
		$array=array(
			'Title' => $title,
			'Description' => $row['description'],
			'PicUrl' => $_W['attachurl'] . $row['picture'],
			'Url' => $this->createMobileUrl('lottery', array('id' => $rid)),
		);
		if(TIMESTAMP < $row['start_time'] ){
			$array['Description']='活动还没开始.'.$row['description'];
		
		}
		if(TIMESTAMP > $row['end_time'] && $now <= $row['end_time']){
			$array['Description']='活动已结束,'.$row['description'];
		
		}
		return $this->respNews($array);
	}
}
