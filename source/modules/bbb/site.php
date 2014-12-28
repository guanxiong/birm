<?php
/**
 * 摇骰子吧抽奖模块
 *
 * [皓蓝] www.weixiamen.cn 5517286
 */
defined('IN_IA') or exit('Access Denied');

class BbbModuleSite extends WeModuleSite {

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
			pdo_delete('bbb_user', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($_GPC['wid'])) {
			$wid = intval($_GPC['wid']);
			pdo_update('bbb_user', array('status' => intval($_GPC['status'])), array('id' => $wid));
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
		
		
		$sql = "SELECT a.id,  a.points,a.createtime, b.nickname, b.mobile FROM ".tablename('bbb_user')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id'  $where ORDER BY a.points DESC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('bbb_user')." AS a
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

	public function getHomeTiles() {
		global $_W;
		$urls = array();
		$list = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND module = 'bbb'");
		if (!empty($list)) {
			foreach ($list as $row) {
				$urls[] = array('title'=>$row['name'], 'url'=> $this->createMobileUrl('lottery', array('id' => $row['id'])));
			}
		}
		return $urls;
	}

	public function doMobileLottery() {
		global $_GPC, $_W;
		$title = '摇骰子抽奖';
		$id = intval($_GPC['id']);
		
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			echo " 404";
			exit;
		}
		
		checkauth();
		
			
		$fromuser = $_W['fans']['from_user'];
		$sql="SELECT COUNT(*) FROM ".tablename('bbb_user')." WHERE  from_user = '{$fromuser}' and rid=".$id;
		$isuser = pdo_fetchcolumn($sql);
		
		//用户不存在，就插入
		if (!$isuser){
			$bbb_user=array(
				'rid'=>$id,
				'count'=>0,
				'points'=>0,
				'from_user'=>$fromuser,
				'createtime'=>TIMESTAMP,
			);
			pdo_insert('bbb_user', $bbb_user);
		}
		$profile = fans_require($fromuser, array('nickname', 'mobile'), '需要完善资料后才能摇骰子.');
		
		$bbb = pdo_fetch("SELECT * FROM ".tablename('bbb_reply')." WHERE rid = '$id' LIMIT 1");
		
		
		if ($bbb['start_time']>TIMESTAMP){
			
			$str="活动于". date('Y-m-d H:i') ." 开始!";
			message('活动没开始', $this->createWebUrl('info', array('id' => $id)));
		}
		if ($bbb['end_time']<TIMESTAMP){
			
			message('活动已结束,稍等带你去看排名..', $this->createWebUrl('rank', array('id' => $id)));
		}
		
		
		$bbb['descriptions']=str_replace("\r","",$bbb['description']);
		$bbb['descriptions']=str_replace("\n","",$bbb['descriptions']);
		if (empty($bbb)) {
			message('非法访问，请重新发送消息进入摇骰子页面！');
		}
		$bbb['description']=str_replace("\n","",$bbb['description']);
		$bbb['description']=str_replace("\r","",$bbb['description']);
		$sql="SELECT COUNT(*) FROM ".tablename('bbb_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid=".$id;
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('bbb_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$bbb['maxlottery'],$bbb['prace_times'],$myuser['count']);
	
		include $this->template('bbb');
	}
	
	public function doMobileInfo() {
		global $_GPC, $_W;
		$title = '摇骰子抽奖';
		$id = intval($_GPC['id']);
		
		
			
		$fromuser = $_W['fans']['from_user'];
		$sql="SELECT COUNT(*) FROM ".tablename('bbb_user')." WHERE  from_user = '{$fromuser}' and rid=".$id;
		$isuser = pdo_fetchcolumn($sql);
		
		//用户不存在，就插入
		if (!$isuser){
			$bbb_user=array(
				'rid'=>$id,
				'count'=>0,
				'points'=>0,
				'from_user'=>$fromuser,
				'createtime'=>TIMESTAMP,
			);
			pdo_insert('bbb_user', $bbb_user);
		}
		$profile = fans_require($fromuser, array('nickname', 'mobile'), '需要完善资料后才能摇骰子.');
		
		$bbb = pdo_fetch("SELECT * FROM ".tablename('bbb_reply')." WHERE rid = '$id' LIMIT 1");
		
		$bbb['descriptions']=str_replace("\r","",$bbb['description']);
		$bbb['descriptions']=str_replace("\n","",$bbb['descriptions']);
		if (empty($bbb)) {
			message('非法访问，请重新发送消息进入摇骰子页面！');
		}
		$bbb['description']=str_replace("\n","",$bbb['description']);
		$bbb['description']=str_replace("\r","",$bbb['description']);
		$sql="SELECT COUNT(*) FROM ".tablename('bbb_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid=".$id;
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('bbb_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$bbb['maxlottery'],$bbb['prace_times'],$myuser['count']);
	
		include $this->template('info');
	}
	
	public function doMobileRank() {
		
		global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);
		$bbb = pdo_fetch("SELECT * FROM ".tablename('bbb_reply')." WHERE rid = '$id' LIMIT 1");
		$bbb['descriptions']=str_replace("\r","",$bbb['description']);
		$bbb['descriptions']=str_replace("\n","",$bbb['descriptions']);
		$showurl=1;
		if(!empty($fromuser)){
			$showurl=0;
			$sql="SELECT * FROM ".tablename('bbb_user')." WHERE  from_user = '$fromuser' AND rid = '$id' ";
			$myuser = pdo_fetch($sql);
			if($myuser){
				$sql="SELECT count(*) FROM ".tablename('bbb_user')." WHERE  rid = ".$id." and points >".$myuser['points'];
				
				$ph=pdo_fetchcolumn($sql);
				
				$myph=intval($ph)+1;
				if ($myph<12){
					$str=$myuser['points'].'点';
				}else{
					$str=$myph."名";
				}
			}
		}else{
		
			$str="";
		}
		if(empty($bbb['guzhuurl'])){
				$showurl=0;
		}
				
			$sql="select u.points,f.nickname from ".tablename('bbb_user')." as u left join ".tablename('fans')." as f on u.from_user=f.from_user  where u.rid = '$id' order by u.points DESC ,u.id ASC limit 10";
			$allph=pdo_fetchall($sql);
		
		include $this->template('rank');
	}
	
	// 点击量统计
	public function doMobileUcount(){
		global $_GPC, $_W;
		
		$effective= true ;
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			$effective = false ;
		}
		
		$id = intval($_GPC['id']);
		$uid = intval($_GPC['uid']);
		if (!$uid) {
			$effective = false ;
		}
		$url=$this->createMobileUrl('rank', array('id' => $id));
		$replay = pdo_fetch("SELECT * FROM ".tablename('bbb_reply')." WHERE rid = '{$id}' LIMIT 1");
		$user = pdo_fetch("SELECT * FROM ".tablename('bbb_user')." WHERE id = '{$uid}' and rid = '{$id}'  LIMIT 1");
		
		if($uid && $effective){
			//cookies不存在
			if(!isset($_COOKIE["hlbbb"])){ 
				
				setcookie('hlbbb',1,time()+86400);
				$data = array(
					'count' => $user['count'] +1,
					'friendcount'=> $user['friendcount'] +1,
				);
				pdo_update('bbb_user', $data,array('id' => $uid));	
			}
			
		}
		
		if(!empty($replay['guzhuurl'])){
			$url=$replay['guzhuurl'];
		}
		
		die('<script>location.href = "'.$url.'";</script>');
		
	}	
	

	public function doMobileGetAward() {
		global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		
		if (empty($fromuser)) {
			exit('非法参数1！');
		}
		$id = intval($_GPC['id']);
		$bbb = pdo_fetch("SELECT * FROM ".tablename('bbb_reply')." WHERE rid = '$id' LIMIT 1");
		
		if (empty($bbb)) {
			exit('非法参数2！');
		}
		$sql="SELECT COUNT(*) FROM ".tablename('bbb_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid = '$id' ";
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('bbb_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$bbb['maxlottery'],$bbb['prace_times'],$myuser['count']);
		
		if ($arr_times['today_has'] <=0 ) {
			echo json_encode(array('level'=>1,'errmessage'=>'今天你的抽奖次数用完了,明天再来吧!'));
			exit;
		}
		
		//点数概率
		$level=array();

		$level['a']=rand(1,6);
		$level['b']=rand(1,6);
		$level['c']=rand(1,6);
		$level['d']=rand(1,6);
		$level['e']=rand(1,6);
		$level['f']=rand(1,6);
		$level['title']='bbb';
		$level['key']= $level['a'] + $level['b'] + $level['c'] + $level['d'] + $level['e'] + $level['f'] ;
		
		$user=array();
		$user['name']='ss';
		$user['num']=$arr_times['today_has']-1;
		$user['usercont']=$arr_times['todayalltimes'];
		$data=array(
			'rid'=>$id,
			'point'=>$level['key'],
			'from_user'=>$fromuser,
			'createtime'=>TIMESTAMP,
		);
		pdo_insert('bbb_winner', $data);
		
		if ($totals>=$bbb['maxlottery']){
				pdo_query("UPDATE  ".tablename('bbb_user')." SET count=count-1 , points=points+".$level['key']." WHERE from_user = '{$fromuser}' AND rid=".$id);
				$user['usercont']=$user['usercont']-1;
			
		}else{
			pdo_query("UPDATE  ".tablename('bbb_user')." SET points=points+".$level['key']." WHERE from_user = '{$fromuser}' AND rid=".$id);
		}
			
		$user['mytotal'] = $myuser['points']+ $level['key'];

		echo json_encode(array('user'=>$user,'level'=>$level,'errmessage'=>''));
		exit;
		
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
