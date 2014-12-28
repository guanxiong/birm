<?php
/**
 * 翻牌抽奖
 * 作者:迷失卍国度/Zombieszy
 * qq:15595755/214983937
 */
defined('IN_IA') or exit('Access Denied');
include "model.php";
class IflopModuleProcessor extends WeModuleProcessor {
	public $name = 'IflopModuleProcessor';
	
	public function respond() {
		global $_W;
        $rid = $this->rule;
        $sql = "SELECT * FROM " . tablename('iflop_reply') . " WHERE `rid`=:rid LIMIT 1";
        $row = pdo_fetch($sql, array(':rid' => $rid));
		if ($row == false) {
            return $this->respText("活动已取消...");
        }
		
  		return $this->respNews(array(
               'Title' => $row['title'],
               'Description' =>  htmlspecialchars_decode($row['description']),
               'PicUrl' => empty($row['picture'])?$_W['siteroot'] . "source/modules/iflop/preview.jpg": $row['picture'],
               'Url' => $this->createMobileUrl('index', array('id' => $rid,'weid' => $_W['weid'])),
        ));
        
      
	}
	 
}
