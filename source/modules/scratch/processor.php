<?php
/**
 * 刮刮卡模块处理程序
 *
 * @author 微动力
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class ScratchModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W; 		
		$rid = $this->rule;
		$sql = "SELECT title,description,start_picurl,isshow,starttime,endtime,end_theme,end_instruction,end_picurl FROM " . tablename('scratch_reply') . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
	
		if ($row==false) {
			return array();
		}
		if($row['isshow']==0){
			return array();
		}
		if($row['endtime']<time()){
			if(substr($row['end_picurl'],0,7)=="http://"){
				$picurl=$row['end_picurl'];
			}else{
				$picurl=$_W['siteroot'] .trim($row['end_picurl'],'/');
			}
			return $this->respNews(array(
				'Title' => $row['end_theme'],
				'Description' => $row['end_instruction'],
				'PicUrl' => $picurl,
				'Url' => $this->createMobileUrl('index', array('id' => $rid)),
			));		
		}else{	
			if(substr($row['start_picurl'],0,7)=="http://"){
				$picurl=$row['start_picurl'];
			}else{
				$picurl=$_W['siteroot'] .trim($row['start_picurl'],'/');
			}		
			return $this->respNews(array(
				'Title' => $row['title'],
				'Description' => $row['description'],
				'PicUrl' => $picurl,
				'Url' => $this->createMobileUrl('index', array('id' => $rid)),
			));
		}
	}
}