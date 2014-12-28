<?php
/**
 * 图文签到
 *
 * @author 福州程序员
 * @url 
 */
defined('IN_IA') or exit('Access Denied');


class CgtsigninModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
	    global $_W;		
		$rid = $this->rule;		
		$weid=$_W['weid'];
		$sql = "SELECT * FROM " . tablename('cgt_signin_reply') . " WHERE `rid`=:rid LIMIT 1";		
		$row = pdo_fetch($sql, array(':rid' => $rid));		
		if (empty($row['id'])) {		
			return $this->respText("签到规则不存在");
		}
		$id=$row['id'];
		
		$fromuser = $this->message['from'];
        if (!empty($row['start_time']) &&  $row['start_time']>time()){
    		  	return $this->respText("签到活动还未开始");	
         }
    		  	
         if (!empty($row['end_time']) && $row['end_time']<time()){
    		  return $this->respText("签到活动已经结束");	
    	}
				
			 	
    		
		 $news = array();			
		 $news[] = array(
						'title' => "签到",
						'description' =>trim(strip_tags("签到送好礼")),
						'picurl' =>$_W['attachurl'].$row['thumb'],
						
						'url' => $_W['rootsite'].$this->createMobileUrl('signindex', array('rid' =>$rid,'from_user' => base64_encode(authcode($fromuser, 'ENCODE'))))
				);
			return $this->respNews($news);
		}
	  


}