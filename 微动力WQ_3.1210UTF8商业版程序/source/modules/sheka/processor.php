<?php
/**
 * 贺卡模块处理程序
 *
 * @author nuqut
 * @url  heka.weibaza.com
 */
defined('IN_IA') or exit('Access Denied');

class ShekaModuleProcessor extends WeModuleProcessor {
	public function respond() {
		global $_W;
		$rid = $this->rule;
		if($rid) {
			$reply = pdo_fetch("SELECT * FROM " . tablename('sheka_reply') . " WHERE rid = :rid", array(':rid' => $rid));	
			if($reply) {
			

	if(!empty($reply['cid'])) {
					$condition = " WHERE ";
			$condition .= "classid = '{$reply['cid']}'";
}
	
	$orderby = 'id DESC  LIMIT 0,8';
				$news = array();
			$news[] = array('title' => $reply['title'], 'reply' => $reply['title'], 'url' => create_url('mobile/module', array('classid' => $reply['cid'],'name' => 'sheka','do' => 'list','weid' => $reply['weid'])), 'picurl' =>$_W['siteroot'] ."/source/modules/sheka/images/img/small-". $reply['cid'].".jpg");
		if($reply['cid']==1) {

	$sql = "SELECT * FROM ".tablename('sheka_list'). $condition. ' ORDER BY '. $orderby;
		$data = pdo_fetchall($sql);
	
		if (!empty($data)) {
		foreach($data as $c) {
				$row = array();
				$row['title'] =$c['title'];
				$row['description'] ="";
				$row['picurl'] =$_W['attachurl'] . $c['thumb'];
				$querystring['name'] = "sheka";
				$querystring['do'] = "preview";
				$querystring['id'] = $c['id'];
				$querystring['weid'] = $_W['weid'];
				$row['url'] =create_url('mobile/module', $querystring);
				$news[] = $row;

			}
			}
			}
					return $this->respNews($news);
			}
		}

	}
	}