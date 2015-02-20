<?php
/**
 * 微名片
 *
 * @author 大路货 QQ:792454007
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class LxybuscardperModuleProcessor extends WeModuleProcessor {    
	public $table_reply = 'lxy_bussiness_per_card_reply';
	
	public function respond() {   	
    	
    	global $_W;
    	$rid = $this->rule;
    	$sql = "SELECT * FROM " . tablename($this->table_reply) . " WHERE `rid`=:rid LIMIT 1";
    	$row = pdo_fetch($sql, array(':rid' => $rid));
    	if (empty($row['id'])) {
    		return $this->respText("请确认您要展示的名片规则已维护") ;
    	}
    	return $this->respNews(array(
    				'Title' => $row['title'],
    				'Description' => htmlspecialchars_decode($row['description']),
    				'PicUrl' => $_W['attachurl'] . $row['picture'],
    				'Url' =>$_W['siteroot'].$this->createMobileUrl('viewcard',array('id'=>$row['cid'])) ,
    		));
    		 
    		
   }
 
}

