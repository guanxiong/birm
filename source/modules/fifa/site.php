<?php
/**
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

class FifaModuleSite extends WeModuleSite {

	public function doWebManageuser() {
		
		global $_W, $_GPC; // 获取query string中的参数
		checklogin();
		$weid=$_W['weid'];
		$sql="SELECT sum(`score`) as v ,f.nickname,f.mobile,f.from_user FROM `ims_fifa_beat` as b LEFT JOIN `ims_fans` as f on b.from_user=f.from_user where b.weid={$weid} GROUP BY b.from_user ORDER BY v desc";
		$list = pdo_fetchall($sql);
		
		include $this->template('users');
	}
	
	public function doWebShowuser() {
		
		global $_W, $_GPC; // 获取query string中的参数
		checklogin();
		$weid=$_W['weid'];
		$from_user=$_GPC['from_user'];
		$sql="SELECT r.*,b.whowin as whowins,b.createtime,b.score as scores FROM `ims_fifa_beat` AS b LEFT JOIN `ims_fifa_race` AS r on b.raceid=r.id where  b.from_user='{$from_user}' AND b.weid={$weid} ";
		//echo $sql;
		$list = pdo_fetchall($sql);
		$profile = fans_search($from_user);
		include $this->template('showuser');
	}
	
	//比赛设置
	public function doWebManageset() {
		global $_W, $_GPC;
		checklogin();
		$weid=$_W['weid'];
		$sql="SELECT * FROM ".tablename('fifa_setting')." WHERE weid=".$weid;
		$reply=pdo_fetch($sql);
		
		
		if (checksubmit('submit')) {
				
				
				$data = array(
					
					'description' =>htmlspecialchars_decode($_GPC['description']),
					'rule' =>htmlspecialchars_decode($_GPC['rule']),
					'digger' =>$_GPC['digger'],
					'huodong' =>$_GPC['huodong'],
					'score' =>intval($_GPC['score']),
					'score8' =>intval($_GPC['score8']),
					'score4' =>intval($_GPC['score4']),
					'champion' =>intval($_GPC['champion']),
				);
				if ($reply) {
					pdo_update('fifa_setting', $data, array('weid' => $weid));
					
					message('更新成功！', create_url('site/module/manageset', array('name' => 'fifa')), 'success');
				} else {
					$data['weid'] = $weid;
					pdo_insert('fifa_setting', $data);
					message('成功更新！', create_url('site/module/manageset', array('name' => 'fifa')), 'success');
				}
				
			}
		if(!$reply){
			$reply=array(
				'score'=>10,
				'score8'=>10,
				'score4'=>10,
				'champion'=>10,
			
			);
		
		}
		include $this->template('manageset');	
	}
	
	//比赛管理
	public function doWebManagevs() {
		global $_W, $_GPC;
		checklogin();
		$list = pdo_fetchall("SELECT * FROM ".tablename('fifa_race')."  ORDER BY id ASC");
		
		
		include $this->template('managevs');	
	}
	
	public function doWebRace() {
		global $_W, $_GPC;
		checklogin();
		$id=$_GPC['id'];
		$team = pdo_fetchall("SELECT * FROM ".tablename('fifa_team')."  ORDER BY id ASC");
		if (checksubmit('submit')) {
				
				
				$data = array(
					
					'teama' =>$_GPC['description'],
					'teamb' =>$_GPC['description'],
					'digger' =>$_GPC['digger'],
					'huodong' =>$_GPC['huodong'],
					'score' =>intval($_GPC['score']),
					'score8' =>intval($_GPC['score8']),
					'score4' =>intval($_GPC['score4']),
					'champion' =>intval($_GPC['champion']),
				);
				if ($reply) {
					//pdo_update('fifa_setting', $data, array('weid' => $weid));
					
					message('更新成功！', create_url('site/module/manageset', array('name' => 'fifa')), 'success');
				} else {
					$data['weid'] = $weid;
					//pdo_insert('fifa_setting', $data);
					message('成功更新！', create_url('site/module/manageset', array('name' => 'fifa')), 'success');
				}
				
			}
		
		
		include $this->template('race');	
	}
	
	public function doWebScore() {
		global $_W, $_GPC;
		checklogin();
		$id=$_GPC['id'];
		if(!intval($id)){
			
			message('非法参数');
		}
		$race = pdo_fetch("SELECT * FROM ".tablename('fifa_race')."  where id=".$id );
		
		if (checksubmit('submit')) {
				$data = array(
					'whowin' =>$_GPC['whowin'],
					'score' =>$_GPC['score'],
				);
				if ($race) {
					pdo_update('fifa_race', $data, array('id' => $id));
					message('更新成功！', create_url('site/module/managevs', array('name' => 'fifa')), 'success');
				}
				
		}
			
		include $this->template('score');	
	}

	public function doWebRaceupme() {
		global $_W, $_GPC;
		checklogin();
		$id=$_GPC['id'];
		if(!intval($id)){
			
			message('非法参数');
		}
		$race = pdo_fetch("SELECT * FROM ".tablename('fifa_race')."  where id=".$id );
		
		$sql="SELECT * FROM ".tablename('fifa_setting')." WHERE weid=".$_W['weid'];
		$reply=pdo_fetch($sql);
		
		if ($race['whowin']<>'') {
		
			pdo_update('fifa_beat',array('score'=>$reply['score']),array('whowin'=>$race['whowin'],'raceid'=>$id,'weid'=>$_W['weid']));	
			
			message('更新成功！', create_url('site/module/managevs', array('name' => 'fifa')), 'success');
		}else{
			message('更新失败,结果还没出来！', create_url('site/module/managevs', array('name' => 'fifa')), 'error');
		}
			
		include $this->template('score');	
	}

	public function doMobileRule() {
		global $_W, $_GPC;
		$weid=$_W['weid'];
		$title="竞猜规则";
		$sql="SELECT * FROM ".tablename('fifa_setting')." WHERE weid=".$weid;
		$reply=pdo_fetch($sql);
		$content=$reply['rule'];
		include $this->template('news');	
	}
	public function doMobileRegister() {
		global $_GPC, $_W;
		$title = '登记个人信息';
		if (empty($_W['fans']['from_user'])) {
				message('非法访问，请重新发送消息进入砸蛋页面！');
			}
		if (checksubmit('submit')) {
			
			$data = array(
				'nickname' => $_GPC['nickname'],
				'mobile' => $_GPC['mobile'],
				
			);
			if (empty($data['nickname'])) {
				die('<script>alert("请填写您的真实姓名！");location.reload();</script>');
			}
			if (empty($data['mobile'])) {
				die('<script>alert("请填写您的手机号码！");location.reload();</script>');
			}
			fans_update($_W['fans']['from_user'], $data);
			die('<script>alert("登记成功！");location.href = "'.$this->createMobileUrl('index', array('id' => $_GPC['id'])).'";</script>');
		}
		$profile = fans_search($_W['fans']['from_user']);
		
		
		
		
		include $this->template('register');
	}
	public function doMobileDescription() {
		global $_W, $_GPC;
		$title="竞猜活动";
		$weid=$_W['weid'];
		$sql="SELECT * FROM ".tablename('fifa_setting')." WHERE weid=".$weid;
		$reply=pdo_fetch($sql);
		$content=$reply['description'];
		include $this->template('news');	
	}
	
	public function doMobileIndex() {
		global $_W, $_GPC;
		$title="2014FIFA竞猜";
		$reply=pdo_fetch("select * from  ".tablename('fifa_setting')." WHERE weid=".$_W['weid']);
		if (TIMESTAMP >1402675200){
		$racetime=TIMESTAMP + 5400;
			$list1=pdo_fetch("SELECT * FROM ".tablename('fifa_race')." WHERE racetime<".TIMESTAMP."  ORDER BY id DESC LIMIT 1");
			$list2=pdo_fetch("SELECT * FROM ".tablename('fifa_race')." WHERE racetime>".$racetime."  ORDER BY id ASC LIMIT 1");
			$list=array();
			$list[]=$list1;
			$list[]=$list2;
		}else{
			$list=pdo_fetchall("SELECT * FROM ".tablename('fifa_race')."  ORDER BY id ASC LIMIT 2");
		
		}
		$from_user=$_W['fans']['from_user'];
		if(!empty($from_user)){
			$myscore=pdo_fetch("select sum(`score`) as myscore,count(`score`) as mywin from  ".tablename('fifa_beat')." where `from_user`='".$from_user."' AND score>0" );

		}
		
		
		$weekarray=array("日","一","二","三","四","五","六");
		
		foreach($list as $k=>$v){
			$list[$k]['weeks']="周".$weekarray[date("w",$v['racetime'])];
			if(!$v['score']){
				$list[$k]['score']='?:?';
			}
		}		
		include $this->template('index');	
	
	}
	
	public function doMobileList() {
		global $_W, $_GPC;
		$lastday=strtotime(date('Y-m-d',TIMESTAMP-3600*24))-1 ;
		$weekarray=array("日","一","二","三","四","五","六");
		$weeksday="星期".$weekarray[date("w",1403798400)];
		$race_list = pdo_fetchall("SELECT * FROM ".tablename('fifa_race')." WHERE racetime > ".$lastday."  ORDER BY id ASC LIMIT 20");
		foreach($race_list as $k=>$v){
			$race_list[$k]['weeks']="周".$weekarray[date("w",$v['racetime'])];
			if(!$v['score']){
				$race_list[$k]['score']='?:?';
			}
		}		
		
		include $this->template('race');	
	}
	
	public function doMobileDetail() {
		global $_W, $_GPC;
		$id=$_GPC['id'];
		
		if(!intval($id)){
			message('数据错误', create_url('mobile/module/index', array('name' => 'fifa','weid'=>$_W['weid'])), 'success');
		}
		$title="竞猜";
		checkauth();
		
		$from_user=$_W['fans']['from_user'];
		
		$profile = fans_search($_W['fans']['from_user']);
		
		if(empty($profile['nickname']) ||  empty($profile['mobile'])){
			message('请完善您的个人信息', $this->createMobileUrl('register', array('id' => $_GPC['id'])), 'success');
		}
		
		$isbeat=pdo_fetchcolumn("SELECT * FROM ".tablename('fifa_beat')." WHERE from_user='{$from_user}' AND raceid='{$id}' AND weid=".$_W['weid']);
		$race_detail = pdo_fetch("SELECT * FROM ".tablename('fifa_race')." WHERE id = '{$id}' ");
		if (!$race_detail){
			message('不存在此比赛');
		}
		
		if (checksubmit('submit')) {
				if (intval($race_detail['racetime'])<TIMESTAMP){
					message('错过投注时间');
				
				}
				
				if(intval($isbeat)){
					message('你已投注过,不能再投');
				}
				if (!isset($_GPC['whowin'])) {
					message('请选择那个队赢或打平！！');
				}
				
				if ($_GPC['whowin']<>$race_detail['groupa'] && $_GPC['whowin']<>$race_detail['groupb'] && $_GPC['whowin']<>0) {
					message('请选择那个队赢或打平！！');
				}
				
				$data = array(
					'weid' => $_W['weid'],
					'raceid' => $id,
					'whowin' => $_GPC['whowin'],
					'from_user' => $from_user,
					'createtime' => TIMESTAMP,
				);
				
				pdo_insert('fifa_beat', $data);
				
				message('投注成功！', create_url('mobile/module/detail', array('name' => 'fifa','id'=>$id,'weid'=>$_W['weid'])), 'success');
			}
		$allcount=pdo_fetchall("select whowin,count(*) as a  from ".tablename('fifa_beat')." where raceid=$id GROUP BY whowin");
		
		$weekarray=array("日","一","二","三","四","五","六");
		$weeksday="周".$weekarray[date("w",$race_detail['racetime'])];
		$allbeat=0;
		foreach($allcount as $k=>$v){
			if($v['whowin']==$race_detail['groupa']){
				$teama=intval($v['a']);
				
			}
			if($v['whowin']==$race_detail['groupb']){
				$teamb=intval($v['a']);
			}
			if($v['whowin']=='0'){
				$team0=intval($v['a']);
			}
			
			$allbeat=$allbeat + intval($v['a']);
			
		}
		if($allbeat==0){
			$teamas=0;
			$team0s=0;
			$teambs=0;
		}else{
			
			$teamas=intval($teama*100/$allbeat);
			$team0s=intval($team0*100/$allbeat);
			$teambs=intval($teamb*100/$allbeat);
		}
		
		include $this->template('tz');
	}
	
	public function getItemTiles() {
        global $_W;
        $urls = array(
            array('title' => "足球竞猜", 'url' => $this->createMobileUrl('index')),
           
        );
        return $urls;
    }

	
	public function doMobileChampion() {
		global $_W, $_GPC;
		
		$weid=$_W['weid'];
		
		$title="2014FIFA谁是冠军";
		checkauth();
		
		$from_user=$_W['fans']['from_user'];
		$profile = fans_search($_W['fans']['from_user']);
		
		if(empty($profile['nickname']) ||  empty($profile['mobile'])){
			message('请完善您的个人信息', $this->createMobileUrl('register', array('id' => $_GPC['id'])), 'success');
		}
		$sql="SELECT count(`id`) as v  FROM ".tablename('fifa_beat')." where type=4 AND weid={$weid}  GROUP BY whowin ";
		$listcp = pdo_fetchall($sql);
		$listch=array();
		foreach($listcp as $k=>$v){
			$listch[$v['id']]=$listcp[$k];
		}
		$listcp='';
		$sql="SELECT t.*,b.whowin FROM ".tablename('fifa_team')." AS t LEFT JOIN ".tablename('fifa_beat')."  AS b ON t.id=b.whowin  and b.from_user='{$from_user}' and b.type=4   ORDER BY id ASC limit 100";
		$list = pdo_fetchall($sql);
		//var_dump($list);
		//$isbeat=pdo_fetchcolumn("SELECT * FROM ".tablename('fifa_beat')." WHERE from_user='{$from_user}' AND raceid=0 AND weid=".$_W['weid']." AND type=4");
		include $this->template('champion');
	}
	public function doMobileCppost() {
		global $_W, $_GPC;
		
		$weid=$_W['weid'];
		$id=$_GPC['id'];
		$title="2014FIFA谁是冠军";
		checkauth();
		$from_user=$_W['fans']['from_user'];
		$mycp=pdo_fetchall("SELECT * FROM ".tablename('fifa_beat')." WHERE from_user='{$from_user}' AND type=4");
		if(count($mycp)>3){
			message('你已经选择了4个队！');
		
		}
		foreach($mycp as $v){
		
			if(intval($v['whowin'])==$id){
				message('你已经选择了这个队！');
			
			}
		}
		$data = array(
					'weid' => $_W['weid'],
					'raceid' => 0,
					'whowin' => $id,
					'from_user' => $from_user,
					'createtime' => TIMESTAMP,
					'type' => 4,
				);
				
				pdo_insert('fifa_beat', $data);
				
		message('冠军队选择成功！', create_url('mobile/module/champion', array('name' => 'fifa','weid'=>$_W['weid'])), 'success');
		
		
		
		
	}
	public function doMobileMy(){
		global $_W, $_GPC;
		
		checkauth();
		$title="我竞猜的比赛";
		$from_user=$_W['fans']['from_user'];
		$profile = fans_search($_W['fans']['from_user']);
		
		if(empty($profile['nickname']) ||  empty($profile['mobile'])){
			message('请完善您的个人信息', $this->createMobileUrl('register', array('id' => $_GPC['id'])), 'success');
		}
		$sql="SELECT t.*,b.createtime,b.whowin FROM ".tablename('fifa_beat')." as b LEFT JOIN ".tablename('fifa_team')." as t on  t.id=b.whowin where b.from_user='{$from_user}' and b.type=4 ";
		$mycp=pdo_fetchall($sql);
		
		$sql="SELECT r.*,b.createtime,b.whowin as whowins,b.score as scores FROM ".tablename('fifa_beat')." as b LEFT JOIN ".tablename('fifa_race')." as r on  r.id=b.raceid where b.from_user='{$from_user}' and b.type=1 order by r.id asc";
		$mybeat = pdo_fetchall($sql);
		$myscore=pdo_fetchcolumn("SELECT SUM(`score`) FROM ".tablename('fifa_beat')." WHERE from_user='{$from_user}' ");
		//$profile = fans_search($_W['fans']['from_user']);
		include $this->template('my');	
	
	}
	public function doMobileMybeat(){
		$this->doMobileMy();
	}
	public function doMobileRank(){
		global $_W, $_GPC;
		
		//checkauth();
		$title="竞猜排行";
		$weid=$_W['weid'];
		$sql="SELECT sum(`score`) as v ,f.nickname FROM `ims_fifa_beat` as b LEFT JOIN `ims_fans` as f on b.from_user=f.from_user where b.weid={$weid} GROUP BY b.from_user ORDER BY v desc limit 20";
		$list = pdo_fetchall($sql);
		include $this->template('rank');	
	
	}


	public function doWebRaceup(){
		global $_W, $_GPC;
		checklogin();
		$url='http://wq.weixiamen.net/';
		$weid=$_W['weid'];
		$sql="SELECT * FROM ".tablename('fifa_setting')." WHERE weid=".$weid;
		$reply=pdo_fetch($sql);
		if(!$reply){
			message('更新失败！,你还没有设置',create_url('site/module/manageset', array('name' => 'fifa')));
		}
		$url=$url.'res.php?act=fifa&id=';
		$id=intval($_GPC['id']);
		if(!$id){
			return false;
		}
		$ch = curl_init();
		
		
		$url=$url.$id;
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_HEADER,0);
		$output = curl_exec($ch);
		$arr=json_decode($output,true);
		
		curl_close($ch);
		if($arr){
			if($arr['error']){
				message($arr['message']);
			}else{
				$sql=$arr['str'];
				pdo_query($sql);
				$sql="SELECT * FROM ".tablename('fifa_setting')." WHERE weid=".$weid;
				$reply=pdo_fetch($sql);
				pdo_update('fifa_beat', array('score'=>$reply['score']), array('weid' => $weid,'id'=>$id));
			}
			
			message('更新成功！','','success');
		}else{
			message('更新失败！');
		}
		
		
	}	
	
	
}
