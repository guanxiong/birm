<?php
/**
 * 摇一摇抽奖模块
 *
 * [天蓝创想] www.v0591.com 5517286
 */
defined('IN_IA') or exit('Access Denied');

class YyyModuleSite extends WeModuleSite {

	public function doWebFormDisplay() {
		global $_W, $_GPC;
		$result = array('error' => 0, 'message' => '', 'content' => '');
		$result['content']['id'] = $GLOBALS['id'] = 'add-row-news-'.$_W['timestamp'];
		$result['content']['html'] = $this->template('item', TEMPLATE_FETCH);
		exit(json_encode($result));
	}

	public function doWebAwardlist() {
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete('yyy_winner', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($_GPC['wid'])) {
			$wid = intval($_GPC['wid']);
			
			pdo_update('yyy_winner', array('shakestatus' => intval($_GPC['status'])), array('id' => $wid));
			message('标识领奖成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$where = '';
		$starttime = !empty($_GPC['start']) ? strtotime($_GPC['start']) : TIMESTAMP;
		$endtime = !empty($_GPC['start']) ? strtotime($_GPC['end']) : TIMESTAMP;
		if (!empty($starttime) && $starttime == $endtime) {
			$endtime = $endtime + 86400 - 1;
		}
		$condition = array(
			'isregister' => array(
				'',
				" AND b.nickname <> ''",
				" AND b.nickname = ''",
			),
			
			'qq' => " AND b.qq ='{$_GPC['profilevalue']}'",
			'mobile' => " AND b.mobile ='{$_GPC['profilevalue']}'",
			'nickname' => " AND b.nickname ='{$_GPC['profilevalue']}'",
			'starttime' => " AND a.createtime >= '$starttime'",
			'endtime' => " AND a.createtime <= '$endtime'",
		);
		if (!isset($_GPC['isregister'])) {
			$_GPC['isregister'] = 1;
		}
		$where .= $condition['isregister'][$_GPC['isregister']];
		
		if (!empty($_GPC['profile'])) {
			$where .= $condition[$_GPC['profile']];
		}
		if (!empty($_GPC['award'])) {
			$where .= $condition[$_GPC['award']];
		}
		/*
		if (!empty($starttime)) {
			$where .= $condition['starttime'];
		}
		if (!empty($endtime)) {
			$where .= $condition['endtime'];
		}
		*/
		$sql = "SELECT a.id,a.status,(a.endtime -a.createtime  ) as usertime ,a.createtime, b.nickname, b.mobile, b.qq FROM ".tablename('yyy_winner')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id'  $where ORDER BY usertime ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$list = pdo_fetchall($sql);
		
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yyy_winner')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' $where");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('awardlist');
	}

	public function doWebDelete() {
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id FROM " . tablename('yyy_award') . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，奖品不存在或是已经被删除！', '', 'error');
		}
		if (pdo_delete('yyy_award', array('id' => $id))) {
			message('删除奖品成功', '', 'success');
		}
	}
	public function doWebClear() {
		global $_W,$_GPC;
		$rid = intval($_GPC['rid']);
		//$sql = "DELETE FROM " . tablename('yyy_award') . " WHERE `rid`=:rid and weid=".$_W['weid'];
		//$row = pdo_query($sql, array(':id'=>$id));
		
		if (pdo_delete('yyy_winner', array('rid' => $rid ))) {
			message('清空数据成功', '', 'success');
		}
	}
	public function getCovers() {
		return array(
			array('title' => '摇一摇', 'url' => $this->createWebUrl('first')),
		);
	}

	public function getHomeTiles() {
		global $_W;
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'yyy'");
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('lottery', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileYyy() {
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			echo "404 error";
			exit;
		}
		global $_GPC, $_W;
		
		$title = '摇一摇抽奖';
		checkauth();
		
		$fromuser = $_W['fans']['from_user'];
		$profile = fans_require($fromuser, array('nickname', 'mobile'), '需要完善资料后才能摇一摇.');
		
		$id = intval($_GPC['id']);
		$yyy = pdo_fetch("SELECT * FROM ".tablename('yyy_reply')." WHERE rid = '$id' LIMIT 1");
		
		if (empty($yyy)) {
			message('不存在此活动，请重新发送消息进入摇一摇页面！');
		}
		
		if ($yyy['shakestatus']==1){
			if($yyy['starttime']>time()){
				$yyy['lefttime']=$yyy['starttime']-time();
			}else{
				$yyy['lefttime']=3;
			}
		
		}
		if ($yyy['shakestatus']==0){
			
			$yyy['lefttime']=1000000;
			
		}
		
		
		$totay=strtotime(date('y-m-d',time()));
		$sql="SELECT count FROM ".tablename('yyy_winner')." WHERE  from_user = '$fromuser'  AND rid = '$id' AND  createtime >$totay AND status=2 ";
		$isaward = pdo_fetchcolumn($sql);
		if (intval($isaward)) {
			$this->doMobileEnd();
			exit;
		}
		
		$sql="SELECT * FROM ".tablename('yyy_winner')." WHERE  from_user = '$fromuser' AND rid = '$id' ";
		$winner = pdo_fetch($sql);
		$total=intval($winner['count']);
		if ($yyy['shaketimes'] == $total){
			$this->doMobileEnd();
			exit;
		
		}
		if ($yyy['endtime'] < TIMESTAMP || $yyy['shakestatus']==2){
			$this->doMobileEnd();
			exit;
		
		}
		//$member = fans_search($fromuser);
		$ruletype=intval($yyy['ruletype']);
		if ($ruletype){
			//include $this->template('yyys');
			include $this->template('yyyc');
		}else{
			include $this->template('yyyc');
		}
	}
	//计次提交
	public function doMobilePostJson() {
		global $_GPC, $_W;
		if (empty($_W['fans']['from_user'])) {
			message('非法访问，请重新发送消息进入摇一摇页面！');
		}
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);
		$count=$_GPC['ucount'];
		$mictimes=(microtime(TRUE)-1395000000)*100;
		if ($count==0){
			$yyy = pdo_fetch("SELECT id,starttime FROM ".tablename('yyy_reply')." WHERE rid = '$id' LIMIT 1");
			if (empty($yyy)) {
				message('非法访问，请重新发送消息进入摇一摇页面！');
			}
			
			$starttime=(intval($yyy['starttime']) - 1395000000)*100;
			
			$data=array('rid'=>$id,'weid'=>$_W['weid'] ,'from_user'=>$fromuser,'nickname'=>$_W['fans']['nickname'],'count'=>$count,'createtime'=>$starttime,'endtime'=>$mictimes);
		
			
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('yyy_winner')." WHERE  from_user = '$fromuser'  AND rid = '$id'");
			if (!intval($total)){
				pdo_insert('yyy_winner', $data);
			}
		}else{
		
			$updata =" count= $count  , endtime=$mictimes" ;
			$sql="UPDATE ".tablename('yyy_winner')." SET $updata  WHERE from_user='$fromuser' AND rid = '$id' limit 1";
			pdo_query($sql);
		}
		
		message($count, '');
	}
	public function doMobileStarTime() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$yyy = pdo_fetch("SELECT shakestatus,starttime FROM ".tablename('yyy_reply')." WHERE rid = '$id' LIMIT 1");
		
		if (empty($yyy)) {
			message('不存在此活动，请重新发送消息进入摇一摇页面！');
		}
		
		if ($yyy['shakestatus']==1){
			if($yyy['starttime']>time()){
				$yyy['lefttime']=$yyy['starttime']-time();
			}else{
				$yyy['lefttime']=1;
			}
		
		}
		if ($yyy['shakestatus']==0){
			
			$yyy['lefttime']=1000000;
			
		}
		echo $yyy['lefttime'];
	}
	
	public function doWebBigscreen() {
		global $_GPC, $_W;
		
		checklogin();
		$id = intval($_GPC['id']);
		$reply = pdo_fetch("SELECT * FROM ".tablename('yyy_reply')." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $id));
		
		
		if (intval($reply['starttime'])>10){
			$reply['lefttime']=intval($reply['starttime'])-time();
		
		}else{
			$reply['lefttime']=3;
		
		}
		$reply['keyword']=pdo_fetchcolumn("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$reply['rid']}'");
		$sql = "SELECT id, createtime,(endtime -createtime  ) as usertime  FROM ".tablename('yyy_winner')." 
			WHERE rid = '$id' $where ORDER BY count DESC,endtime ASC LIMIT 10";
		
		$list = pdo_fetchall($sql);
		
		include $this->template('screen');
	}
	
	public function doWebScreenJson() {
		global $_GPC, $_W;
		
		
		$id = intval($_GPC['id']);
		$sql = "SELECT count,nickname, (endtime -createtime  ) as usertime  FROM ".tablename('yyy_winner')."  WHERE rid = '$id' and weid = '{$_W['weid']}' $where ORDER BY count DESC, endtime ASC LIMIT 10";
		$list = pdo_fetchall($sql);
		$templist=array(
				'count' => 0,
				
				'nickname' => '-',
				'usertime' => 0,
			);
		
		$list_nums=count($list);
		while($list_nums<10)
		{
			$list[]=$templist;
			$list_nums++;
		}
		echo json_encode($list);
		
		
	}
	public function doMobileEnd() {
		global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);
		$yyy = pdo_fetch("SELECT id, rule,starttime,description,shakespace,shakestrong,shaketimes,ruletype FROM ".tablename('yyy_reply')." WHERE rid = '$id' LIMIT 1");
		$sql="SELECT * FROM ".tablename('yyy_winner')." WHERE  from_user = '$fromuser' AND rid = '$id' ";
		$winner = pdo_fetch($sql);
		$total=intval($winner['count']);
			if ($total<intval($yyy['shaketimes'])){
				$str="没摇完,没成绩";
				
			}else{
				$mmytime=$winner['endtime'] - $winner['createtime'];
				$mytime=$mmytime/100;
				$mytime=round($mytime,2);
				//round(1.95583,?2)
				//$mytime=number_format($mytime,2);
				$sql="SELECT count(*) FROM ".tablename('yyy_winner')." WHERE  rid = '$id' AND  endtime <".$winner['endtime'] ;
				$ph=pdo_fetchcolumn($sql);
				$myph=intval($ph);
				if ($myph<12){
					$str="我的成绩:".$mytime."秒.</br>";
				}else{
					$str="我的成绩:".$mytime."秒;</br>排名第".$myph.";</br>";
				}
			}
			
			$sql="select nickname, endtime - createtime as ptime from ".tablename('yyy_winner')."  where rid = '$id' AND count=".$yyy['shaketimes']." order by count DESC ,endtime ASC limit 10";
			
			$allph=pdo_fetchall($sql);
			foreach ($allph as $k=>$v){
				$allph[$k]['ptime']=round($v['ptime']/100,2);
				
			}
		include $this->template('end');
		
		
		
	}
	public function doWebChangeStatus() {
		global $_W, $_GPC;
		checklogin();
		$id = intval($_GPC['id']);
		pdo_update('yyy_reply', array('shakestatus' => 1,'starttime'=> (TIMESTAMP+9) ), array('rid' => $id));
		message(array('status' => 1), '', 'ajax');
	}
	
}


