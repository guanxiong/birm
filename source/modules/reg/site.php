<?php
/**
 * 会员注册
 *
 * @author 19.3CM
 * @QQ 81324093
 * 注：本模块同步微信资料暂时只支持高级接口权限
 */
defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/19.3cm.php';
class regModuleSite extends WeModuleSite {
	public $name = 'reg';
	public $title = '会员注册';
	public $ability = '';
	public $tablename = 'reg_reply';
	
	/*
	 * 内容管理
	 */
  	public function doWebManage() {
		global $_GPC, $_W;
		checklogin();
		$type=$_GPC['type'];
		
		$id = intval($_GPC['id']);
		if (checksubmit('verify') && !empty($_GPC['select'])) {
			pdo_update('fans', array('isshow' => 0, 'createtime' => TIMESTAMP), " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('审核成功！', create_url('site/module/manage', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
		}
		if (checksubmit('getuser') ) {
			$fans=pdo_fetch("SELECT from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' ORDER BY  `id` DESC"); 
			message('开始进入导入用户！', create_url('site/module/getuser', array('name' => 'reg','dr'=>intval($_GPC['dr']),'pagesize'=>intval($_GPC['num']))));
		}
		
		if (checksubmit('tongbu') && !empty($_GPC['select'])) {
			
				$fanslist=pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND id  IN  ('".implode("','", $_GPC['select'])."') ");
				if($fanslist){
					foreach ($fanslist as $fans){
						$user=gjgetuserinfo($fans['from_user'],1);
							if(!empty($user['from_user'])&&is_array($user)){
								$gxusr=pdo_update('fans', $user, array('from_user' =>$user['from_user']));
							}
						}
				}
				
              //pdo_debug();exit;
				if($gxusr){	
					message('同步资料成功！', referer(), 'success');
				}else{
					message('同步用户数据失败！'.$user, create_url('site/module/manage', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
					}
		
		}
		if (checksubmit('editgroup') && !empty($_GPC['select'])) {
			pdo_update('fans', array('groupid' => $_GPC['gid']), " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('重置分组成功！', create_url('site/module/manage', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
		}
		if (checksubmit('delete') && !empty($_GPC['select'])) {
			pdo_delete('fans', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/manage', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$isshow = isset($_GPC['isshow']) ? intval($_GPC['isshow']) : 0;
		$gz=isset($_GPC['isfollow']) ? intval($_GPC['isfollow']) : 0;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;

		$wall = pdo_fetch("SELECT id, isshow, rid FROM ".tablename('reg_reply')." WHERE rid = '{$id}' LIMIT 1");
		$grouplist=$this->doWebGroupdata($id);
		
		//增加按各种条件查询
		//按时间
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
    	$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		
		//print_r($condition);
	/*
		if(intval($_GPC['group'])||intval($_GPC['group'])==0){
			$condition='AND groupid='.intval($_GPC['group']);	
		}
		if($_GPC['group']=='all')
		{unset($condition);}
		*/
		if($gz){
			$condition='AND follow=0 ';
			}
		if($_GPC['group']<>'all'&&$_GPC['group']<>''){
			$condition='AND groupid='.intval($_GPC['group']);	
		}
		//按性别
		
		if($_GPC['sex']=='0'||$_GPC['sex']=='1'||$_GPC['sex']=='2'){
			$condition .='AND gender='.intval($_GPC['sex']);
		}
		
		if($_GPC['start']&&$_GPC['end']){
			$condition='AND createtime >= '.$starttime.' AND createtime <= '.$endtime  ;
		}
		!empty($_GPC['keyword']) && $condition .= " AND nickname LIKE '%{$_GPC['keyword']}%' OR from_user LIKE '%{$_GPC['keyword']}%' OR realname LIKE '%{$_GPC['keyword']}%'";
		
		$totalmember = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' $condition AND isblacklist = '0'");
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' $condition AND isshow = '$isshow' ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		//查重复
		if (checksubmit('chongfu') ) {
			//$list=pdo_fetchall("SELECT count(*)  FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}'$condition AND isshow = '$isshow'  group by from_user ORDER BY  count(*) DESC LIMIT ".($pindex - 1) * $psize.",{$psize}"); 
			$users = pdo_fetchall("SELECT `from_user`,count(*) as `count` FROM `ims_fans` WHERE weid = '{$_W['weid']}' GROUP BY `from_user` ORDER BY count(*) DESC");
			$i=0;
				foreach ($users as $user) {
					if ($user['count']>1) {
    					$del = $user['count'] - 1;
    					pdo_query("DELETE FROM `ims_fans` WHERE `weid` = '{$_W['weid']}' AND `from_user` = '{$user['from_user']}' LIMIT {$del}");
    				//处理重复
					} else {
    					break;
					}
					$i++;
				}
				pdo_query("ALTER TABLE  `ims_fans` ADD UNIQUE (`from_user`)");
				
				message('重复数据删除成功，共删除'.$i.'个重复用户！', create_url('index/module', array('do' => 'manage', 'name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
			
		}
		
		
		if (!empty($list)) {
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('fans') . " WHERE weid = '{$_W['weid']}' AND isshow = '$isshow' $condition ");
			$pager = pagination($total, $pindex, $psize);

			foreach ($list as $k=>$row) {
				if($row['gender']==0){
					$row['sex']='保密';
				}
				elseif($row['gender']==1){
					$row['sex']='男';
					}
				else{	$row['sex']='女';}
				
              $usrinfo[$k] = "<div class=info>ID：".$row['id']."<span>&nbsp;</span>真名：".$row['realname']."<span>&nbsp;</span>昵称".$row['nickname']."<span>&nbsp;</span>QQ".$row['qq']."<span>&nbsp;</span>电话".$row['mobile']."<span>&nbsp;</span>性别".$row['sex']."<span>&nbsp;</span>年龄".$row['age']."<span>&nbsp;</span>积分".$row['credit1']."<span>&nbsp;</span>地址:".$row['city'];
				
				$userids[] = $row['from_user'];
				//下面这句，请在您安装了我的客服系统后，去掉//打开查询，这样会员管理页才能显示此用户是否为客服或者设置客服链接
				//$kfusr[$k] = pdo_fetch("SELECT * FROM ".tablename('kf_kfuser')." WHERE weid = '{$_W['weid']}' AND uid='{$row['id']}'   LIMIT 1 ");
              //print_r($kfusr);
				
			}
			unset($row);

			if (!empty($userids)) {
				$member = pdo_fetchall("SELECT avatar, nickname, from_user, isblacklist,isshow FROM ".tablename('fans')." WHERE from_user IN ('".implode("','", $userids)."')", array(), 'from_user');
			}
		}
		//print_r('test');exit;
		include $this->template('manage');
	}

	
	public function doWebBlacklist() {
		global $_W, $_GPC;
		if (checksubmit('delete')) {
			pdo_update('fans', array('isblacklist' => 0), " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('解除黑名单成功！', create_url('site/module/blacklist', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$id = intval($_GPC['id']);
		if (!empty($_GPC['from_user'])) {
			pdo_update('fans', array('isblacklist' => intval($_GPC['switch'])), array('from_user' => $_GPC['from_user']));
			message('黑名单操作成功！', create_url('site/module/manage', array('name' => 'reg', 'id' => $id)));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans')." WHERE isblacklist = '1' ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		include $this->template('blacklist');
		
	}
	//添加群组数据
	
	//设置群组
	public function doWebgroup() {
			
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		$data = array(				
				'groupname' =>$_GPC['groupname'],
				'credit' =>$_GPC['credit'],
				'info' =>$_GPC['info'],
				'weid' =>$_W['weid'],				
			);
		if (checksubmit('submit')) {	
			if (empty($data['groupname'])) {
				die('<script>alert("请填写群组名");location.reload();</script>');
			}
			if (empty($data['credit'])) {
				die('<script>alert("请填写群组要求积分！");location.reload();</script>');
			}
			pdo_insert('fans_group', $data);
			message('添加成功！', create_url('site/module/group', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));			
		}
		
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans_group')." WHERE weid = '{$_W['weid']}'  ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.",{$psize}");
		if (!empty($list)) {
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('fans_group') . " WHERE weid = '{$_W['weid']}' ");
			$pager = pagination($total, $pindex, $psize);
		}
		
		if (checksubmit('del')&& !empty($_GPC['select'])) {			
			pdo_delete('fans_group', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('site/module/group', array('name' => 'reg', 'id' => $id, 'page' => $_GPC['page'])));
						
		}
		include $this->template('group');
		
	}
	
	//编辑群信息
	public function doWebEditgroup($gid) {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$gid = intval($_GPC['gid']);
		if (checksubmit('edit')) {			
			$data = array(				
				'groupname' =>$_GPC['groupname'],
				'credit' =>$_GPC['credit'],
				'info' =>$_GPC['info'],
								
			);
			if (empty($data['groupname'])) {
				die('<script>alert("请填写群组名");location.reload();</script>');
			}
			if (empty($data['credit'])) {
				die('<script>alert("请填写群组要求积分！");location.reload();</script>');
			}
			pdo_update('fans_group', $data,array('id' => $gid));
			message('编辑成功！', create_url('site/module/group', array('name' => 'reg', 'id' => $id)));			
		}
		$list = pdo_fetch("SELECT * FROM ".tablename('fans_group')." WHERE weid = '{$_W['weid']}' AND id='{$gid}'   LIMIT 1");
		
		include $this->template('editgroup');
	}
	//群组信息数据返回
	public function doWebGroupdata($id) {
		global $_GPC, $_W;
		
		if(!$id){
		$gid = intval($_GPC['gid']);
		}
		if($gid){
			$list = pdo_fetch("SELECT * FROM ".tablename('fans_group')." WHERE weid = '{$_W['weid']}' AND id='{$gid}'   LIMIT 1");
		}else{
		
		$list = pdo_fetchall("SELECT * FROM ".tablename('fans_group')." WHERE weid = '{$_W['weid']}' $where   ORDER BY id DESC");
		}
		return $list;
	}
	
	
	//用户编辑
	public function doWebeditusr() {
		global $_W, $_GPC;
		
		$id = intval($_GPC['id']);
		include_once model('fans');
		if (checksubmit('submit')) {
			if (!empty($_GPC)) {
				$from_user = $_GPC['from'];
				foreach ($_GPC as $field => $value) {
					if (empty($value) || in_array($field, array('from_user','act', 'name', 'token', 'submit'))) {
						unset($_GPC[$field]);
						continue;
					}
				}
				fans_update($from_user, $_GPC);
			}
			message('更新资料成功！', referer(), 'success');
		}
		if (checksubmit('tb')) {
			if (!empty($_GPC)) {
				$from_user = $_GPC['from'];
			}
			else{
				message('请确定OID有填写！', referer(), 'success');
				exit;
				}
			$user=gjgetuserinfo($from_user,$_GPC['gxtou']);
			if(!empty($user['from_user'])&&is_array($user)){
					pdo_update('fans', $user, array('from_user' =>$from_user));
					//fans_update($from_user, $user);
              //pdo_debug();exit;
					}
			message('同步资料成功！', referer(), 'success');
		}
		$profile = fans_search($_GPC['from']);
		
		$form = array(
			'birthday' => array(
				'year' => array(date('Y'), '1914'),
			),
			'bloodtype' => array('A', 'B', 'AB', 'O', '其它'),
			'education' => array('博士','硕士','本科','专科','中学','小学','其它'),
			'constellation' => array('水瓶座','双鱼座','白羊座','金牛座','双子座','巨蟹座','狮子座','处女座','天秤座','天蝎座','射手座','摩羯座'),
			'zodiac' => array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'),
		);
		$gname=$this->doWebGroupdata($member['groupid']);
		$groupname=$gname?'未分组':$gname['groupname'];
		$grouplist=$this->doWebGroupdata();
		
		
		
		include $this->template('usr');
		
	}
	
	public function doWebgetuser($oid) {
		//用户入库
		global $_GPC, $_W;
		$oid=$_GPC['oid'];
		$page=$_GPC['page']?$_GPC['page']:0;
		$pagesize=$_GPC['pagesize']?$_GPC['pagesize']:20;
		$dr=$_GPC['dr'];
		$userlist=gj_getuserlist(0,$oid);
		//print_r($pagesize);exit;
		if($dr){
			$fans=pdo_fetchall("SELECT from_user FROM ".tablename('fans')." WHERE  WEID='{$_W['weid']}'  ");
			foreach ($fans as $f){
				$oldfans['data']['openid'][]=$f['from_user'];
				$oldfans['total'] =count($fans);
				 $oldfans[count] = count($fans);
				 $oldfans[next_openid]=$fans['from_user'];  
				}
			$userlist['data']['openid'] = array_diff ($userlist['data']['openid'], $oldfans['data']['openid']);
			}
		//print_r($userlist['data']['openid']);exit;
		if($userlist['ret']){
			message('出错啦!出错代码：'.$userlist['ret'].'出错信息：'.$userlist['message'], create_url('site/module/manage', array('name' => 'reg', 'type' => '3')) , 'error');
			exit;
		}
		if($pagesize>=$userlist['total']){
          	message('导入数过多，请不要设置超过总用户数,你当前用户总数为'.$userlist['total'].'请返回重新设置', create_url('site/module/manage', array('name' => 'reg', 'type' => '3')) , 'error');
			$total=$userlist['total'];
		}else{
			$total=$pagesize*($page+1);
		}
		if($userlist['total']&&!empty($userlist)){
			
			//循环第一次导入1万个号。
			//foreach ($userlist['data']['openid'] as $uid) 
				
				for($i=$pagesize*$page;$i<=$total-1;$i++){
					//$oid=$dr?$userlist['data']['openid'][$i]:$userlist['data']['openid'][$i];
					$oid=$userlist['data']['openid'][$i];
					$u=pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND from_user='{$oid}'  LIMIT 1");
					
			if($oid){
						
					$user=gj_getuserinfo($oid);
					
					/*如果用户资料查义出错跳出循环
					if(empty($user['from_user'])){
						message($user.'未获到用户数据！'.$total, create_url('index/module', array('name' => 'reg', 'do' => 'manage')) , 'error');
                      	break;
						}
						*/
					
					//改为，如果未取到用户资料，就跳过插入数据
				//print_r($user);
					//pdo_debug();exit;from_user]
				if(is_array($user)){
					if(empty($u['from_user'])){
							$inesert=pdo_insert('fans', $user);
							
							if($inesert){
								
								$tips.=$user['nickname'].'导入成功<br />';
								}
								else{
									$tips.=$user['nickname'].'本次入库失败 <br />';
									//pdo_debug();exit;
								}
							
					}else{
							
							$gx=pdo_update('fans', $user, array('id' =>$u['id']));
							
							if($gx==0||$gx){
								$tips.='本次更新用户'.$user['nickname'].'成功<br/>';
								}else
							{$tips.='<b>本次更新用户'.$user['nickname']. $userlist['data']['openid'][$i].'失败</b> <br />';}
					}
					
					
				 }else{//print_r($user);
					 $tips.=$user.'无此用户数据不导入 <br />';
				}
					
             }else{
					 $tips.='用户数据OID 为空不导入 <br />';
				}
				//END，如果没用户数据，就不执行导入。	
				//判断本批一万个，循环时给出下一批用户开如ID	
					if($i<$userlist['total']-1&&$i<>$userlist['count']){
							$next_openid='';
							
					}else{
							$next_openid=$userlist['next_openid'];
					}
					
					
				}
				
				if(empty($userlist['next_openid'])||$i>=$userlist['total']||$userlist['total']<=$pagesize){
						message('本次导入任务结束！', create_url('site/module/manage', array('name' => 'reg', 'type' => '3')) , 'success');
					}
				else{
						message('请勿关闭浏览器还在导入中...！共'.($userlist['total']).'人,正在导入第'.($page+1).'批次<br />'.$tips, create_url('site/module/getuser', array('name' => 'reg', 'type' => '3')).'&page='.($page+1).'&pagesize='.$pagesize.'&dr='.$dr.'&oid='.$next_openid , 'success');
					}
			//print_r('下一个OID'.$next_openid.'总用户数'.$userlist['total'].'计算总数和循环数差值'.($userlist['total']-(($pagesize*$page))+($i%$pagesize)));
		}else{
			message($userlist['errmsg'].'错误,未获到数据！', create_url('site/module/manage', array('name' => 'reg', 'type' => '3')) , 'error');
		}
		
			
		
	}
		
}
