<?php
/**
 * 砸蛋抽奖模块
 *
 * [WeEngine System] 更多模块请浏览：BBS.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class ScratchModuleSite extends WeModuleSite {
	public $tablename = 'scratch_reply';
	public $tablefans = 'scratch_fans';
	public function getProfileTiles() {

	}

	public function getHomeTiles($keyword = '') {

	}

	public function doMobileindex() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		//录入scratch_fans数据
		
		if(empty($id)){
			message('抱歉，参数错误！','', 'error');              
		}
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $id));	
		if($reply==false){
			message('抱歉，活动已经结束！','', 'error');              
		}
		
		if(empty($_W['fans']) || $_GPC['share']==1){
			//301跳转
			if(!empty($reply['share_url'])){
				header("HTTP/1.1 301 Moved Permanently"); 
				header("Location: ".$reply['share_url'].""); 
				exit(); 
			}
			//message('抱歉，参数错误！','', 'error');              
			$isshare=1;
			$running=false;
			$msg='请先关注公共号。';
		}else{
			$fansID=$_W['fans']['id'];
			$from_user=$_W['fans']['from_user'];		
			$fans = pdo_fetch("SELECT * FROM ".tablename($this->tablefans)." WHERE rid = ".$id." and fansID=".$fansID." and from_user='".$from_user."'");		

			if($fans==false){
				$insert=array(
					'rid'=>$id,
					'fansID'=>$fansID,
					'from_user'=>$from_user,
					'todaynum'=>0,
					'totalnum'=>0,
					'awardnum'=>0,
					'createtime'=>time(),	
				);
				$temp=pdo_insert($this->tablefans, $insert);
				if($temp==false){
					message('抱歉，刚才操作数据失败！','', 'error');              
				}
				//增加人数，和浏览次数
				pdo_update($this->tablename, array('fansnum'=>$reply['fansnum']+1,'viewnum'=>$reply['viewnum']+1),array('id'=>$reply['id']));
			}else{
				//增加浏览次数
				pdo_update($this->tablename, array('viewnum'=>$reply['viewnum']+1),array('id'=>$reply['id']));
			}
			//判断是否获奖
			$award= pdo_fetchall("SELECT * FROM ".tablename('award')." WHERE weid=".$_W['weid']." and rid = ".$id." and fansID=".$fansID." and from_user='".$from_user."' order by id desc");		
			if($award!=false){
				$awardone=$award[0];
			}
			$running=true;
			//判断是否可以刮刮
			if ($awardone&&empty($fans['tel'])){
				$running=false;
				$msg='请先填写用户资料';
			}
			
			//判断用户抽奖次数
			$nowtime=mktime(0,0,0);
			if($fans['last_time']<$nowtime){
				$fans['todaynum']=0;
			}
			//判断总次数超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
			if($running && $reply['starttime']>time()){
				$running=false;
				$msg='活动还没有开始呢！';
			}	
			//判断总次数超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
			if($running && $reply['endtime']<time()){
				$running=false;
				$msg='活动已经结束了，下次再来吧！';
			}	
			//判断总次数超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
			if($running && $fans['totalnum']>=$reply['number_times'] && $reply['number_times']>0){
				$running=false;
				$msg='您已经超过抽奖总限制次数，无法抽奖了!';
			}	
			//判断当日是否超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
			if($running && $fans['todaynum']>=$reply['most_num_times']&& $reply['most_num_times']>0){
				$running=false;
				$msg='您已经超过今天的抽奖次数，明天再来吧!';
			}			
		}
		$cArr=array('one','two','three','four','five','six');
		foreach($cArr as $c){
			if(empty($reply['c_type_'.$c])) break;
			$awardstr.='<p>'.$reply['c_type_'.$c].'：'. $reply['c_name_'.$c];
			if($reply['show_num']==1){$awardstr.='  奖品数量： '.$reply['c_num_'.$c];}
			$awardstr.='</p>';
		}
		if($reply['most_num_times']>0 && $reply['number_times']>0){
			$detail='本次活动共可以刮'.$reply['number_times'].'次，每天可以刮 '.$reply['most_num_times'].' 次卡! 你共已经刮了 <span id="totalcount">'.intval($fans['totalnum']).'</span> 次 ，今天刮了<span id="count">'.intval($fans['todaynum']).'</span> 次.';
		}elseif($reply['most_num_times']>0){
			$detail='本次活动每天可以刮 '.$reply['most_num_times'].' 次卡!你共已经刮了 <span id="totalcount">'.intval($fans['totalnum']).'</span> 次 ，今天刮了<span id="count">'.intval($fans['todaynum']).'</span> 次.';
		}elseif($reply['number_times']>0){
			$detail='本次活动共可以刮'.$reply['number_times'].'次卡!你共已经刮了 <span id="totalcount">'.intval($fans['totalnum']).'</span> 次。';
		}else{
			$detail='您很幸运，本次活动没有任何限制，您可以随意刮!你共已经刮了 <span id="totalcount">'.intval($fans['totalnum']).'</span> 次。';
		}
		
		if(empty($reply['sn_rename'])){$reply['sn_rename']='SN码';}
		if(empty($reply['tel_rename'])){$reply['tel_rename']='手机号';}
		if(empty($reply['Repeat_lottery_reply'])){$reply['Repeat_lottery_reply']='亲，继续努力哦！';}
		if(empty($fans['todaynum'])){$fans['todaynum']=0;}
		if(empty($fans['totalnum'])){$fans['totalnum']=0;}
		//分享信息
		$sharelink=empty($reply['share_url'])?($_W['siteroot'].$this->createMobileUrl('index', array('id' => $id,'share'=>1))):$reply['share_url'];
		$sharetitle=empty($reply['share_title'])?'欢迎参加刮刮卡活动':$reply['share_title'];
		$sharedesc=empty($reply['share_desc'])?'亲，欢迎参加刮刮卡抽奖活动，祝您好运哦！！':$reply['share_desc'];		
		$shareimg=$_W['siteroot'].trim($reply['start_picurl'],'/');
		include $this->template('index');
	}
	
	public function doMobilegetaward(){
		global $_GPC, $_W;	
 
		$id = intval($_GPC['id']);
		//开始抽奖咯
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $id));		
		if($reply==false){
			$this->message();
		}
		//先判断有没有资格领取
		if(empty($_W['fans'])){
			$this->message('','fan数据为空');
		}
		$fansID=$_W['fans']['id'];
		$from_user=$_W['fans']['from_user'];
		//第一步，判断有没有已经领取奖品了，如果领取了，则不能再领取了
		$fans = pdo_fetch("SELECT * FROM ".tablename($this->tablefans)." WHERE rid = ".$id." and fansID=".$fansID." and from_user='".$from_user."'");		
		if($fans==false){
			//不存在false的情况，如果是false，则表明是非法
			$this->message();
		}
		//更新当日次数
		$nowtime=mktime(0,0,0);
		if($fans['last_time']<$nowtime){
			$fans['todaynum']=0;
		}
		//判断总次数超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
		if($fans['totalnum']>=$reply['number_times'] && $reply['number_times']>0){
			$this->message('','超过抽奖总限制次数');
		}	
		//判断当日是否超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
		if($fans['todaynum']>=$reply['most_num_times']&& $reply['most_num_times']>0){
			$this->message('','超过当日限制次数');
		}
		//判断有没有奖品可以领取
		$rate = 1;
		if ($reply['probability'] < 1) {
			$temp = explode('.', $gift['probability']);
			$temp = pow(10, strlen($temp[1]));
			$rate = $temp < $rate ? $rate : $temp;
		}
		$all = 100 * $rate;
		$rand=rand(0,$all);
		$probability=$reply['probability']*$rate;
		if($rand>$probability){
			//超过获奖区间，不获奖
			$this->message('','超过范围!'.$rand);
		}
		//获奖了，然后看是几等奖
		$rand=rand(0,$reply['total_num']);
		$cArr=array('one','two','three','four','five','six');
		foreach($cArr as $c){
			if($rand<$reply['c_num_'.$c]){
				if($reply['c_draw_'.$c]>=$reply['c_num_'.$c]){
					$this->message('','超过个人领奖总次数');
				}
				
				if($fans['awardnum']>=$reply['award_times'] && $reply['award_times']>0){
					$this->message('','超过个人获奖次数限制!');
				}
				//增加一个中奖人数，添加中奖信息
				$sn=random(16);
				$temp = pdo_update('scratch_reply',array('c_draw_'.$c=>$reply['c_draw_'.$c]+1),array('id'=>$reply['id']));
				if($temp==false){
					$this->message('','中奖纪录没保存，算没有中奖。');
				}
				//保存sn到award中
				$insert=array(
					'weid'=>$_W['weid'],
					'rid'=>$id,
					'type'=>'scratch',
					'fansID'=>$fansID,
					'from_user'=>$from_user,
					'name'=>$reply['c_type_'.$c],
					'description'=>$reply['c_name_'.$c],
					'prizetype'=>$c,
					'award_sn'=>$sn,
					'createtime'=>time(),
					'status'=>1,
				);
				$temp=pdo_insert('award', $insert);		
				if($temp==false){
					$this->message('','award没保存，算没有中奖。');
				}				
				//保存中奖人信息到fans中
				pdo_update('scratch_fans', array('awardnum'=>$fans['awardnum']+1),array('id'=>$fans['id']));			
				//判断中奖情况
				$data=array(
					'name'=> $reply['c_type_'.$c],
					'award'=>$reply['c_name_'.$c],
					'sn'=>$sn,
					'success'=> 1,
				);
				$this->message($data);
			}
			$rand=$rand-$reply['c_num_'.$c];
			if($rand<0) break;			
		}
		$this->message();
	}

	public function doMobilesettel(){
		global $_GPC, $_W;	
		$id = intval($_GPC['id']);
		$fansID=$_W['fans']['id'];
		$from_user=$_W['fans']['from_user'];
		$fans = pdo_fetch("SELECT id FROM ".tablename($this->tablefans)." WHERE rid = ".$id." and fansID=".$fansID." and from_user='".$from_user."'");				
		if($fans==false){
			$data=array(
				'success'=>0,
				'msg'=>'保存数据错误！',
			);	
		}else{
			$temp=pdo_update($this->tablefans, array('tel'=>$_GPC['mobile']),array('rid'=>$id,'fansID'=>$fansID));
			if($temp==false){
				$data=array(
					'success'=>0,
					'msg'=>'保存数据错误！',
				);
			}else{
				$data=array(
					'success'=>1,
					'msg'=>'保存数据成功！',
				);
			
			}
		}
		echo json_encode($data);
	}
	//json
	public function message($_data='',$_msg=''){
		$this->setfans();
		if(empty($_data)){
			$_data=array(
				'name'=> "谢谢参与",
				'success'=> 0,
			);
		}
		if(!empty($_msg)){
			$_data['msg']=$_msg;
		}
		die(json_encode($_data));
	}
	public function setfans(){
		global $_GPC,$_W;	
		//增加fans次数
		//记录用户信息
		$id = intval($_GPC['id']);
		$fansID=$_W['fans']['id'];
		if(empty($fansID)||empty($id)) return ;
		$fans=pdo_fetch("SELECT * FROM ".tablename($this->tablefans)." WHERE rid = ".$id." and fansID=".$fansID."");
		$nowtime=mktime(0,0,0);
		if($fans['last_time']<$nowtime){
			$fans['todaynum']=0;
		}
		$update=array(
			'todaynum'=>$fans['todaynum']+1,
			'totalnum'=>$fans['totalnum']+1,
			'last_time'=>time(),
		);			
		pdo_update($this->tablefans, $update, array('id' => $fans['id']));
	}


}