<?php

/***
 * 中秋节博饼
 * Meepo Union 大学生创业联盟 
 * URL : http://meepo.com.cn
 */
 
defined('IN_IA') or exit('Access Denied');
 
 class MgamblemoonModuleSite extends WeModuleSite{
 
 
	public $modulename = 'mgamblemoon';
	
	public $award = '';
	
	public function getHomeTitles(){
		global $_W;
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'mgamblemoon'");
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('lottery', array('id' => $row['id'])));
			}
		}
		return $urls;
	}
	public function doWebFormDisplay() {
		global $_W, $_GPC;
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$result['content']['id'] = $GLOBALS['id'] = 'add-row-news-'.$_W['timestamp'];
		$result['content']['html'] = $this->template('item', TEMPLATE_FETCH);
		exit(json_encode($result));
	}
	
	public function doWebFajiang(){
	
		global $_W,$_GPC;
		$id= $_GPC['id'];
		$rid = $_GPC['rid'];
		$data = array(
			
			'status'=>$_GPC['status'],
		
		);
		
		pdo_update('mgamblemoon_user',$data,array('id'=>$id));
		
		message('奖品发放成功',$this->createWebUrl('awardlist', array('id' => $rid, 'page' => $_GPC['page'])),'success');
	}
	//奖项设置
	public function doWebAwardSet(){
	
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$weid = $_W['weid'];
		$from_user = $_W['fans']['from_user'];
		
		$row = pdo_fetchall("SELECT award FROM " . tablename('mgamblemoon_award_set') . " WHERE weid={$weid} AND awardid in (0,1,2,3,4,5,6,7,8)");
		
		//print_r($row);
		
		$names = array('状元插金花','五红','五子','普通状元','对堂','四进','三红','二举','一秀');
		
		
		
		if($_W['ispost']){
			$award = $_GPC['award'];
			print_r($award);
			
			foreach($names as $key=>$name){
				
				$insert = array(
					
					'weid'=>$weid,
					'awardname' =>$name,
					'awardid' =>$key,
					'award' => $_GPC[$name],
					'createtime'=>TIMESTAMP ,
				
				);
				$is=pdo_fetch("SELECT * FROM ". tablename('mgamblemoon_award_set'). "WHERE awardid='{$key}' AND weid = {$weid}");
				if(empty($is)){
					pdo_insert('mgamblemoon_award_set',$insert);
				}else{
					pdo_update('mgamblemoon_award_set',$insert,array('id'=>$is['id']));
				}
				
			}
			
			
			$url = $this->createWebUrl('awardset',array('id'=>$id));
			message('保存数据成功',$url,success);
		}
		include $this->template('awardset');
	}
	public function doWebAwardlist() {
		global $_GPC, $_W;
		checklogin();
		//$from_user = $_W['fans']['from_user'];
		$id = $_GPC['id'];
		if (checksubmit('delete')) {
			pdo_delete('mgamblemoon_user', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($_GPC['wid'])) {
			$wid = intval($_GPC['wid']);
			pdo_update('mgamblemoon_user', array('status' => intval($_GPC['status'])), array('id' => $wid));
			message('标识领奖成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$where = '';
		
		
		$condition = array(
			'mobile' => " AND b.mobile ='{$_GPC['profilevalue']}'",
			'realname' => " AND b.realname ='{$_GPC['profilevalue']}'",
		);
		
		
		if (!empty($_GPC['profile'])) {
			$where .= $condition[$_GPC['profile']];
		}
		
		
		$sql = "SELECT a.id,  a.award,a.createtime,a.num,a.status, b.nickname, b.mobile FROM ".tablename('mgamblemoon_user')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = {$id}  $where ORDER BY a.huodeid ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('mgamblemoon_user')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' $where");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('awardlist');
	}
	
	public function getCovers() {
		return array(
			array('title' => '第一期摇骰子', 'url' => $this->createWebUrl('first')),
		);
	}

	
	
	public function __comm($f_name){

		include_once  'site/'.$f_name.'.php';

	}
	
	//活动简介
	public function doMobileInfo(){
		$this->__comm(__FUNCTION__);
	}
	//进入博饼页面
	public function doMobileLottery(){
		$this->__comm(__FUNCTION__);
	}
	//我的奖品
	public function doMobileMyAward(){
		$this->__comm(__FUNCTION__);
	}
	//最近中奖
	public function doMobileRecentWinner(){
		$this->__comm(__FUNCTION__);
	}
	//排名
	public function doMobilerank(){
	
		$this->__comm(__FUNCTION__);
	}
	//分享 统计点击次数 并跳转到引导页
	public function doMobileUcount(){
		$this->__comm(__FUNCTION__);
	
	}
	//领取奖品
	public function doMobileGetAward(){
		$this->__comm(__FUNCTION__);
	
	}
	
	public function doMobileTest(){
	
		
	
	}
	
	public function search($i,$level){
	
		do{
			$is = array_search($i,$level);
			if($is){
				$arr[] = $is;
			}
			unset($level[$is]);
		
		}while($is);
		
		return $arr;
	}
	
 
	/*
	* $userhad 用户今天已使用
	* $maxlottery 每天系统送
	* $prace_times 每天最多奖励次数
	* $friedsend 朋友送
	* return array
	* today_has 今天还可以摇的次数
	* todayalltimes 剩余的次数
	* 
	* 
	*/	
	public function get_today_times($userhad,$maxlottery,$prace_times,$friedsend){
		$arr=array(
			'today_has'=>0,
			'todayalltimes'=>$friedsend,
		);
		
		
		
		if($userhad>=($maxlottery+$prace_times)){
			$arr['today_has']=0;
			return $arr;
		}
		
		if(($userhad>=$maxlottery) && !$friedsend){
			$arr['today_has']=0;
			return $arr;
		}
		
		if(($userhad + $friedsend) >=($prace_times+$maxlottery)){
			$arr['today_has']=$prace_times+$maxlottery -$userhad ;
			return $arr;
		}
		
		if($userhad < $maxlottery ){
			if($friedsend < $prace_times){
				$arr['today_has']=$maxlottery+$friedsend - $userhad;
			}else{
				$arr['today_has']=$maxlottery+$prace_times - $userhad;
			
			}
		}else{
			if($friedsend+$userhad > $maxlottery+$prace_times){
				$arr['today_has']=$maxlottery+$prace_times - $userhad;
			}else{
				$arr['today_has']=$friedsend;
			
			}
		
		}
		return $arr;
		
		
	}
 }