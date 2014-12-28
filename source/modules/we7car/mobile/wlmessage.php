<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url 
 */
if($op=='ajax'){
	$from_user = $_W['fans']['from_user'];  
	if(empty($from_user)){
		$data['msg']='登陆过期，请重新从微信进入!';
		$data['success']=false;
	}else{
		$message = pdo_fetch("SELECT * FROM ".tablename('weicar_message_list')." WHERE from_user = '".$from_user."' and weid=".$_W['weid']." order by create_time desc limit 1" );	
		//判断是否要审核留言
		$isshow = pdo_fetchcolumn("SELECT isshow FROM ".tablename('weicar_message_set')." WHERE weid = :weid limit 1", array(':weid' => $weid));				
		if($isshow===false){
			$isshow=1;
		}
		$insert = array(
			'weid'=>$weid,
			'nickname'=>$_GPC['nickname'],
			'info'=>$_GPC['info'],
			'fid'=>$_GPC['fid'],
			'from_user'=>$from_user,
			'isshow'=>$isshow,
			'create_time'=>time(),
		);
		if(empty($insert['nickname'])){
			$insert['nickname']=pdo_fetchcolumn("Select nickname from ".tablename('fans')." where weid=".$weid."  and  from_user='".$_W['fans']['from_user']."' limit 1");
		}
		
		if($message==false){
			$id=pdo_insert('weicar_message_list', $insert);
			$data['success']=true;
			$data['msg']='留言发表成功';			
			if($isshow==0){$data['msg']=$data['msg'].',进入审核流程';}          
		}else{
			if((time()-$message['create_time'])<5){
				$data['msg']='您的留言速度太快了';
				$data['success']=false;
			}else{
				$id=pdo_insert('weicar_message_list', $insert);
				$data['success']=true;
				$data['msg']='留言发表成功';				
				if($isshow==0){$data['msg']=$data['msg'].',进入审核流程';}
			}
		}
	}
	echo json_encode($data);
}elseif($op=='list'){
	$set = pdo_fetch("SELECT * FROM ".tablename('weicar_message_set')." WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $weid));	
	if($set==false){
		$set=array(
			'status'=>1,
			'isshow'=>1,
		);             
	}
	//1是审核过的
	if($set['status']==0){
		message('留言墙尚未开启,请耐心等待');
	}
	$messagecount=pdo_fetchcolumn ("SELECT count(id) FROM ".tablename('weicar_message_list')." WHERE  isshow=1 and weid=".$weid);
	$p=isset($_GET['p'])?$_GET['p']:1;		
	$pagenum=10;
	$totalpage=floor($messagecount/$pagenum)+1;
	$prow=($p-1)*$pagenum;
	$messagelist = pdo_fetchall("SELECT * FROM ".tablename('weicar_message_list')." WHERE  weid=".$weid." and fid=0 and isshow=1  order by create_time desc  limit $prow,$pagenum" );	
	foreach($messagelist as $k=>$v){
		$messagelist[$k]['reply']=pdo_fetchall("SELECT * FROM ".tablename('weicar_message_list')." WHERE  weid=".$weid." and fid=".$v['id']." and isshow=1  limit 20" );	
	}		
	//获取fans表中的username
	$nickname=pdo_fetchcolumn("Select nickname from ".tablename('fans')." where weid=".$weid."  and  from_user='".$_W['fans']['from_user']."' limit 1");
	include $this->template('message_list');
}