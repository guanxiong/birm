<?php

		global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		
		if (empty($fromuser)) {
			exit('非法参数1！');
		}
		$id = intval($_GPC['id']);
		$mgamblemoon = pdo_fetch("SELECT * FROM ".tablename('mgamblemoon_reply')." WHERE rid = '$id' LIMIT 1");
		
		if (empty($mgamblemoon)) {
			exit('非法参数2！');
		}
		$sql="SELECT COUNT(*) FROM ".tablename('mgamblemoon_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid = '$id' ";
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('mgamblemoon_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$mgamblemoon['maxlottery'],$mgamblemoon['prace_times'],$myuser['count']);
		
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
		$level['title']='mgamblemoon';
		$level['key']= $level['a'] + $level['b'] + $level['c'] + $level['d'] + $level['e'] + $level['f'] ;
		//排序
		
		$num1 = $this->search(1,$level);
		$num2 = $this->search(2,$level);
		$num3 = $this->search(3,$level);
		$num4 = $this->search(4,$level);
		$num5 = $this->search(5,$level);
		$num6 = $this->search(6,$level);
		
		$huode = '未中奖';
		$huodeid = 9;
		$award = '积分奖励';
		if(count($num4) == 1){
			
			$huode = '一秀';
			$huodeid = 8;
		}
		if(count($num4) == 2){
			$huode= '二举';
			$huodeid = 7;
		}
		if(count($num4) == 3){
			$huode= '三红';
			$huodeid = 6;
		}
		if(count($num2) == 4){
			$huode = '四进';
			$huodeid = 5;
		}
		if(count($num1) == 1 && count($num2) == 1 && count($num3) == 1 && count($num4) == 1 && count($num5) == 1 && count($num6) == 1 ){
			$huode = '对堂';
			
			$huodeid = 4;
		}
		if(count($num4) == 4){
			$huode = '普通状元';
			$huodeid = 3;
		}
		if(count($num3) == 5){
			$huode = '五子';
			$huodeid = 2;
		}
		if(count($num4) == 5){
			$huode = '五红';
			$huodeid = 1;
		}
		if(count($num4) == 4 && count($num1) == 2){
			$huode = '状元插金花';
			$huodeid = 0;
		}
		
		$row = pdo_fetch("SELECT * FROM " .tablename('mgamblemoon_award_set') . " WHERE awardid={$huodeid} AND weid={$_W['weid']}");
		
		if(!empty($row)){
			$award = $row['award'];
		}
		$user=array();
		$user['name']='ss';
		$user['num']=$arr_times['today_has']-1;
		$user['usercont']=$arr_times['todayalltimes'];
		$data=array(
			'rid'=>$id,
			'point'=>$level['key'],
			'huodeid'=>$huodeid,
			'huode' => $huode,
			'award' => $award,
			'from_user'=>$fromuser,
			'createtime'=>TIMESTAMP,
		);
		pdo_insert('mgamblemoon_winner', $data);
		
	
			$insert = array(
				
				'rid'=>$id,
				'from_user'=>$fromuser,
				'num'=>1,
				'huodeid'=>$huodeid,
				'huode'=>$huode,
				'award'=>$award,
				'createtime'=>TIMESTAMP,
			);
			
			$is=pdo_fetch("SELECT * FROM ". tablename('mgamblemoon_user'). "WHERE from_user = '{$fromuser}' AND huodeid='{$huodeid}' AND rid = {$id}");
			
			//print_r($is);
			
			if(empty($is)){
				pdo_insert('mgamblemoon_user',$insert);
			}else{
				$numadd = 1;
				pdo_query("UPDATE  ".tablename('mgamblemoon_user')." SET `num` = `num`+1 WHERE from_user = :from_user AND huodeid={$huodeid} AND rid={$id}",array(':from_user'=>$fromuser));
			}
			
		
		if ($totals>=$mgamblemoon['maxlottery']){
				pdo_query("UPDATE  ".tablename('mgamblemoon_user')." SET count=count-1 , points=points+".$level['key']." WHERE from_user = '{$fromuser}' AND rid=".$id);
				$user['usercont']=$user['usercont']-1;
			
		}else{
			pdo_query("UPDATE  ".tablename('mgamblemoon_user')." SET points=points+".$level['key']." WHERE from_user = '{$fromuser}' AND rid=".$id);
		}
			
		//$user['mytotal'] = $myuser['points']+ $level['key'];
		
		//print_r($user);
		//print_r($huode);
		echo json_encode(array('user'=>$user,'level'=>$level,'huode'=>$huode,'errmessage'=>$numadd));
		exit;