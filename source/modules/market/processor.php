<?php
/**
 * 微生活模块处理程序
 *
 * @author 微新星
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class MarketModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W; 	
		$content=$this->message['content'];
		if($content=='微生活'){
			return $this->respNews(array(
				'Title' =>'微生活',
				'Description' => '微生活',
				'PicUrl' => '',
				'Url' => $this->createMobileUrl('classlist'),
			));
		}else{		
			$rid = $this->rule;
			$sql = "SELECT id,title,infos,picurl  FROM " . tablename('market_business') . " WHERE `rid`=:rid LIMIT 1";
			$row = pdo_fetch($sql, array(':rid' => $rid));
			if ($row==false) {
				return array();
			}
					
			return $this->respNews(array(
				'Title' => $row['title'],
				'Description' => $row['infos'],
				'PicUrl' => $row['picurl'],
				'Url' => $this->createMobileUrl('fans', array('id' => $row['id'])),
			));
		}

  	}
}