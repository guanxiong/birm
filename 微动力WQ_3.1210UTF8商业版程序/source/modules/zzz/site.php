<?php
/**
 * 送粽子模块
 *
 * [皓蓝] www.weixiamen.cn 5517286@qq.com
 */
defined('IN_IA') or exit('Access Denied');

class ZzzModuleSite extends WeModuleSite {

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
			pdo_delete('zzz_user', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
		}
		if (!empty($_GPC['wid'])) {
			$wid = intval($_GPC['wid']);
			pdo_update('zzz_user', array('status' => intval($_GPC['status'])), array('id' => $wid));
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
		
		
		$sql = "SELECT a.id,a.friendcount,a.points,a.createtime, b.realname,b.nickname, b.mobile FROM ".tablename('zzz_user')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id'  $where ORDER BY a.points DESC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		//echo $sql;
		$list = pdo_fetchall($sql);
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('zzz_user')." AS a
				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' $where");
			$pager = pagination($total, $pindex, $psize);
		}
		include $this->template('awardlist');
	}

	
	public function doMobileIntroduce() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$fromuser = $_W['fans']['from_user'];
		$zzz = pdo_fetch("SELECT * FROM ".tablename('zzz_reply')." WHERE rid = '$id' LIMIT 1");
		$sql="SELECT * FROM ".tablename('zzz_user')." WHERE  from_user = '{$fromuser}' and rid=".$id;
		$myuser = pdo_fetch($sql);
		
		
		include $this->template('introduce');
	}
	public function doMobileLottery() {
		global $_GPC, $_W;
		$title = '活动';
		$id = intval($_GPC['id']);
		
		$zzz = pdo_fetch("SELECT * FROM ".tablename('zzz_reply')." WHERE rid = '$id' LIMIT 1");
		if (empty($zzz)) {
			message('非法访问，请重新发送消息进入！');
		}
		
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			header('Location: '.$zzz['guzhuurl']);
			exit;
			
		}
		
		checkauth();
		$fromuser = $_W['fans']['from_user'];
		$sql="SELECT * FROM ".tablename('zzz_user')." WHERE  from_user = '{$fromuser}' and rid=".$id;
		$myuser = pdo_fetch($sql);
		$startgame=1;
		if ($zzz['start_time']>TIMESTAMP){
			$startgame=0;
			$str="活动没开始";
		}
		if ($zzz['end_time']<TIMESTAMP){
			$startgame=0;
			
			$str="活动已结束";
		}
		//用户不存在，就插入
		if (!$myuser){
			$zzz_user=array(
				'rid'=>$id,
				'count'=>0,
				'points'=>0,
				'from_user'=>$fromuser,
				'createtime'=>TIMESTAMP,
			);
			pdo_insert('zzz_user', $zzz_user);
		}
		$profile = fans_require($fromuser, array('nickname', 'mobile'), '需要完善资料后才能继续.');
		
		if($myuser){
				$sql="SELECT count(*) FROM ".tablename('zzz_user')." WHERE  rid = ".$id." and points >".$myuser['points'];
				
				$ph=pdo_fetchcolumn($sql);
				
				$myph=intval($ph)+1;
				
		}else{
			$myph='';
		}
		
		$energylimit=($zzz['maxlottery'] + $zzz['prace_times'])*10;
	
		include $this->template('gamex');
	}
	
	public function doMobileGetplayer(){
		header("Content-type: application/json");
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$fromuser = $_W['fans']['from_user'];
		$zzz = pdo_fetch("SELECT * FROM ".tablename('zzz_reply')." WHERE rid = '$id' LIMIT 1");
		$sql="SELECT COUNT(*) FROM ".tablename('zzz_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid=".$id;
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('zzz_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$zzz['maxlottery'],$zzz['prace_times'],$myuser['count']);
		
		$arr['power']=$myuser['points'];
		$arr['weekPower']=rand(1,6);
		$arr['ranking']=56;
		$arr['weekRanking']=57;
		$arr['energy']=$arr_times['today_has'] * 10;
		echo json_encode(array('result'=>$arr,'success'=>true));
		exit;
	}
	
	
	
	public function doMobilePowerup(){
		header("Content-type: application/json");
		
		global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		
		if (empty($fromuser)) {
			exit('非法参数1！');
		}
		$id = intval($_GPC['id']);
		$zzz = pdo_fetch("SELECT * FROM ".tablename('zzz_reply')." WHERE rid = '$id' LIMIT 1");
		
		if (empty($zzz)) {
			exit('非法参数2！');
		}
		$sql="SELECT COUNT(*) FROM ".tablename('zzz_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid=".$id;
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('zzz_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$zzz['maxlottery'],$zzz['prace_times'],$myuser['count']);
		
		$arr['powerUpResult']['type']=rand(1,2);
		$arr['powerUpResult']['value']=rand(100,300);
		$arr['weekPower']=1;
		$arr['power']=$myuser['points'];
		$arr['weekRanking']=55;
		$arr['energy']=20;
		
		if ($arr_times['today_has'] <=0 ) {
			$arr['powerUpResult']['value']=0;
			echo json_encode(array('result'=>$arr,'success'=>true));
			exit;
		}
		
		
		$data=array(
			'rid'=>$id,
			'point'=>$arr['powerUpResult']['value'],
			'from_user'=>$fromuser,
			'createtime'=>TIMESTAMP,
		);
		pdo_insert('zzz_winner', $data);
		
		if ($totals>=$zzz['maxlottery']){
				pdo_query("UPDATE  ".tablename('zzz_user')." SET count=count-1 , points=points+".$arr['powerUpResult']['value']." WHERE from_user = '{$fromuser}' AND rid=".$id);
				$user['usercont']=$user['usercont']-1;
			
		}else{
			pdo_query("UPDATE  ".tablename('zzz_user')." SET points=points+".$arr['powerUpResult']['value']." WHERE from_user = '{$fromuser}' AND rid=".$id);
		}
			
		echo json_encode(array('result'=>$arr,'success'=>true));
		exit;
		
		
	}
	public function doMobileRank() {
		
		global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);
		$zzz = pdo_fetch("SELECT * FROM ".tablename('zzz_reply')." WHERE rid = '$id' LIMIT 1");
		$showurl=1;
		if(!empty($fromuser)){
			$showurl=0;
				
			$sql="SELECT * FROM ".tablename('zzz_user')." WHERE  from_user = '$fromuser' AND rid = '$id' ";
		
			$myuser = pdo_fetch($sql);
			
			if($myuser){
				$sql="SELECT count(*) FROM ".tablename('zzz_user')." WHERE  rid = ".$id." and points >".$myuser['points'] ." and rid=".$id;
				
				$ph=pdo_fetchcolumn($sql);
				
				$myph=intval($ph)+1;
				if ($myph<11){
					$str=intval($myuser['points']/2000).'个';
				}else{
					$str=$myph."名";
				}
			}
		}else{
		
			$str="";
		}
		if(empty($zzz['guzhuurl'])){
				$showurl=0;
		}
				
			$sql="select u.points,f.nickname from ".tablename('zzz_user')." as u left join ".tablename('fans')." as f on u.from_user=f.from_user  where u.rid = '$id' order by u.points DESC  limit 20";
			
			$allph=pdo_fetchall($sql);
			foreach ($allph as $k=>$v) {
				$allph[$k]['zz']=intval($v['points']/2000);
				$allph[$k]['ypoints']=intval($v['points']%2000);
			}
			//var_dump($allph);
		
		include $this->template('rank');
	}
	
	// 点击量统计
	public function doMobileUcount(){
		global $_GPC, $_W;
		
		$effective= true ;
		$msg="输送体力未成功";
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			$effective = false ;
			$msg="只能在微信中输送哦!";
		}
		
		$id = intval($_GPC['id']);
		$uid = intval($_GPC['uid']);
		if (!$uid) {
			$effective = false ;
		}
		$url=$this->createMobileUrl('rank', array('id' => $id));
		//$replay = pdo_fetch("SELECT * FROM ".tablename('zzz_reply')." WHERE rid = '{$id}' LIMIT 1");
		$user = pdo_fetch("SELECT * FROM ".tablename('zzz_user')." WHERE id = '{$uid}' and rid=".$id." LIMIT 1");
		
		
		
		if($user){
			$member = fans_search($user['from_user']);
			if($uid && $effective){
			
				if(!isset($_COOKIE["hlzzzx"])){ 
				//cookies不存在
				
					setcookie('hlzzzx',1,TIMESTAMP+86400);
				
					$data = array(
						'count' => $user['count'] +1,
						'friendcount'=> $user['friendcount'] +1,
					);
				
					pdo_update('zzz_user', $data,array('id' => $uid,'rid'=>$id));
					$msg='你已成功为'.$member['nickname'].'输送体力！';			
				}else{
					$msg='一天只能输送一次体力哦!';	
				}
			
			}
		}
			
		
		
		message($msg, $url);
		//跳转
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
	
	public function doWebCvs() {
		global $_GPC, $_W;
		set_time_limit(0);
		$fname="ZZZ_".date('Ymd_Hi',TIMESTAMP).".csv";

		header("Content-Type: application/vnd.ms-excel; charset=UTF-8"); 
		header("Pragma: public"); 
		header("Expires: 0"); 
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
		header("Content-Type: application/force-download"); 
		header("Content-Type: application/octet-stream"); 
		header("Content-Type: application/download"); 
		header("Content-Disposition: attachment;filename=".$fname); 
		header("Content-Transfer-Encoding: binary "); 
		ob_start();
		$header_str =  iconv("utf-8",'gb2312',"编号,分数,朋友,时间,昵称,姓名,手机\n");
		$file_str="";
	

		$sql="select u.id,u.points,u.friendcount,FROM_UNIXTIME(u.createtime) as time,f.nickname,f.realname,f.mobile from ".tablename('zzz_user')." as u left join ims_fans as f on u.from_user=f.from_user WHERE u.rid='{$_GPC['id']}' order BY u.points desc Limit 1000 ";

		$result=pdo_fetchall($sql);

		if($result){
			foreach ($result as $row) {
	
				$file_str.= $row['id'].','.iconv("utf-8",'gb2312',$row['points']).','.$row['friendcount'].','.$row['time'].','.iconv("utf-8",'gb2312',$row['nickname']).','.iconv("utf-8",'gb2312',$row['realname']).','.iconv("utf-8",'gb2312',$row['mobile'])."\n";
			}
		}else{
			echo "nonono!!!";
		}
		//$file_str=  iconv("utf-8",'gb2312',$file_str);
		ob_end_clean();
		//var_dump($result);
		echo $header_str;
		echo $file_str;
		
	}
	
}
