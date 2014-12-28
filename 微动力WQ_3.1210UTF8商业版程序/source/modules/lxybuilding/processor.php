<?php
/**
 * 股票查询模块处理程序
 *
 * @author 流星雨
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class LxybuildingModuleProcessor extends WeModuleProcessor {
    
	public $name = 'LxybuildingModuleProcessor';
	public $table_reply = 'lxy_building_reply';
	
	public function respond() {   	
    	
    	global $_W;
    	$rid = $this->rule;
    	$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
    	$row = pdo_fetch($sql, array(':rid' => $rid));
    	if (empty($row['id'])) {
    		return $this->respText("请维护需要显示的楼盘引用地址") ;
    	}
    	return $this->respNews(array(
    				'Title' => $row['title'],
    				'Description' => htmlspecialchars_decode($row['description']),
    				'PicUrl' => $_W['attachurl'] . $row['picture'],
    				'Url' =>$row['buildurl'] ,
    		));
    		 
    		
   }
 
}

