<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url
 */

$from_user = $_W['fans']['from_user'];
//0为预约试驾，1为预约保养
$yytype=empty($_GPC['yytype'])?0:1;
if($op=='ajax'){
	if(empty($from_user)){
			$data=array('status'=>0,'msg'=>'请先关注微信号,或从微信入口进入！');
			echo json_encode($data);
			exit;
	}
	$insert = array(
		'weid' => $_GPC['weid'],
		'contact' => $_GPC['contact'],
		'dateline' => $_GPC['dateline'],
		'timepart' => $_GPC['timepart'],
		'remark' => $_GPC['remark'],
		'contacttel' => $_GPC['contacttel'],
		'fieldsigle' => $_GPC['fieldsigle'],
		'fielddownload' => $_GPC['fielddownload'],
		'from_user'=>$from_user,
		'ip' => getip(),
		'createtime'=>time(),
	);
	if(empty($_GPC['yuyueid'])){
		$list = pdo_fetch("SELECT id FROM ".tablename('weicar_yuyue_list')." WHERE from_user = '".$from_user."'And yytype=".$yytype." and status=0 limit 1" );
		if ($list==false){
			pdo_insert('weicar_yuyue_list', $insert);
			$data=array('status'=>1,'msg'=>'提交预约成功！');
		} else {
			$data=array('status'=>0,'msg'=>'您有预约未处理，请联系客服或修改预约！');
			//pdo_update('yuyue_list', $insert, array('id' => $list['id']));
		}
	}else{
		$temp=pdo_update('weicar_yuyue_list', $insert, array('id' => $_GPC['yuyueid'],'status'=>0));
		if($temp==false){
			$data=array('status'=>0,'msg'=>'更新数据失败');
		}else{
			$data=array('status'=>1,'msg'=>'更新数据成功');
		}
	}
	echo json_encode($data);
}elseif($op=='list'){
	$weid = $_GPC['weid'];
	$yytype=empty($_GPC['yytype'])?1:$_GPC['yytype'];
	if (!empty($weid)) {

		$reply = pdo_fetch("SELECT * FROM ".tablename('weicar_yuyue_set')." WHERE weid = :weid AND yytype=:yytype ORDER BY `id` DESC", array(':weid' => $weid, ':yytype' => $yytype));
		if($reply==false){
			echo '<h1>预约活动结束!</h1>';
			exit;
		}else{
			$trtxt=unserialize($reply['trtxt']);
			unset($reply['trtxt']);
			$strtxt=unserialize ($reply['strtxt']);
			unset($reply['strtxt']);
		}
	}else{
		echo '<h1>预约活动结束!</h1>';
		exit;
	}

	if(empty($reply['typename1'])){$reply['typename1']='预约订单';}
	if(empty($reply['typename2'])){$reply['typename2']='预约说明';}
	if(empty($reply['typename3'])){$reply['typename3']='预约电话';}

	if(empty($reply['shareurl'])){
		$reply['shareurl']=$_W['siteroot'].create_url('site/module', array('do' => 'Yuyue', 'name' => 'we7car', 'id' => $weid));
	}

	if(!empty($from_user)) {
		$list = pdo_fetch("SELECT * FROM ".tablename('weicar_yuyue_list')." WHERE from_user = '".$from_user."' and weid=".$weid." limit 1" );
		if($list==false){
			$yuyuenum=0;
		}else{
			$yuyuenum=1;
		}
	}else{
		$yuyuenum=0;
	}

	//预约情况
	if($_GPC['action']=='setinfo'){
		$insert = array(
			'weid' => $weid,
			'name' => $_GPC['username'],
			'tel' => $_GPC['tel'],
			'qq' => $_GPC['qq'],
			'from_user'=>$from_user,
			'ip' => getip()
		);
		$name = pdo_fetch("SELECT * FROM ".tablename('weicar_yuyue_list')." WHERE name = '".$_GPC['username']."' AND from_user='{$from_user}' limit 1" );
		if (empty($from_user)) {
			if (empty($name)) {
				$id=pdo_insert($this->table, $insert);
			} else {
				echo '用户已存在!';
			}
		} else {
			if ($list=='false'){
				pdo_update('user', $insert, array('from_user' => $from_user));
			} else {
				pdo_insert($this->table, $insert);
			}
		}
		die(true);
	}

	$title = '报名页面';
	$loclurl=create_url('mobile/module', array('do' => 'Yuyue', 'name' => 'we7car','yytype'=>$yytype, 'weid' => $weid, 'from_user' => $from_user));
	//获取fans记录
	$fans=fans_search($from_user,array('id','realname','mobile'));

	if ($reply['isshow']) {
		//获取车系列表
		$seriesArr=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_series')." WHERE weid = ".$weid."  order by listorder desc",array(),"id");
		$typeArr = pdo_fetchall("SELECT id,title,sid FROM ".tablename('weicar_type')." WHERE weid = ".$weid."  order by listorder desc");
		$cartype='请选择-0$请选择-0';
		foreach($typeArr as $k=>$v){
			if(isset($seriesArr[$v['sid']])){
				$cartype.="#".$seriesArr[$v['sid']]['title']."-".$seriesArr[$v['sid']]['id'].'（'.$seriesArr[$v['sid']]['title']."）@".$seriesArr[$v['sid']]['title'];
				$cartype.="$".$v['title']."-".$v['id'].'（'.$v['title'].'）';
				unset($seriesArr[$v['sid']]);
			}else{
				$cartype.="$".$v['title']."-".$v['id'].'（'.$v['title'].'）';
			}
		}


		include $this->template('yuyue_index');
	}else {
		echo '<h1>报名结束!</h1>';
	}
}elseif($_GPC['op']=='mybook'){
	$result  = pdo_fetchall("SELECT * FROM ".tablename('weicar_yuyue_list')." WHERE weid = :weid AND from_user = :from_user AND yytype=:yytype", array(':weid' => $weid, ':from_user' => $from_user, ':yytype' => $yytype));
	foreach($result as $k => $yuyue) {
		if($result[$k]['fielddownload']){
			$tempArr=explode('$',$result[$k]['fielddownload']);
			$tempArr=array_splice($tempArr, 1);
			foreach($tempArr as $v){
				$tempArr2=explode('#',$v);
				$result[$k]['select'][]=array(
					'title'=>$tempArr2[0],
					'str'=>$tempArr2[1],
				);
			}
		}
		if($result[$k]['fieldsigle']){
			$tempArr=explode('$',$result[$k]['fieldsigle']);
			$tempArr=array_splice($tempArr, 1);
			foreach($tempArr as $v){
				$tempArr2=explode('#',$v);
				$result[$k]['text'][]=array(
					'title'=>$tempArr2[0],
					'str'=>$tempArr2[1],
				);
			}
		}
	}
	include $this->template('yuyue_mybook');
}