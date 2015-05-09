<?php

		global $_GPC, $_W;
		$title = '摇骰子抽奖';
		$id = intval($_GPC['id']);
	
		$fromuser = $_W['fans']['from_user'];
		$sql="SELECT COUNT(*) FROM ".tablename('mgamblemoon_user')." WHERE  from_user = '{$fromuser}' and rid=".$id;
		$isuser = pdo_fetchcolumn($sql);
		
		//用户不存在，就插入
		if (!$isuser){
			$mgamblemoon_user=array(
				'rid'=>$id,
				'count'=>0,
				'points'=>0,
				'from_user'=>$fromuser,
				'createtime'=>TIMESTAMP,
			);
			pdo_insert('mgamblemoon_user', $mgamblemoon_user);
		}
		$profile = fans_require($fromuser, array('nickname', 'mobile'), '需要完善资料后才能摇骰子.');
		
		$mgamblemoon = pdo_fetch("SELECT * FROM ".tablename('mgamblemoon_reply')." WHERE rid = '$id' LIMIT 1");
		
		$mgamblemoon['descriptions']=str_replace("\r","",$mgamblemoon['description']);
		$mgamblemoon['descriptions']=str_replace("\n","",$mgamblemoon['descriptions']);
		if (empty($mgamblemoon)) {
			message('非法访问，请重新发送消息进入摇骰子页面！');
		}
		$mgamblemoon['description']=str_replace("\n","",$mgamblemoon['description']);
		$mgamblemoon['description']=str_replace("\r","",$mgamblemoon['description']);
		$sql="SELECT COUNT(*) FROM ".tablename('mgamblemoon_winner')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND from_user = '$fromuser' and rid=".$id;
		$totals = pdo_fetchcolumn($sql);
		$myuser=pdo_fetch("SELECT id,points,count FROM ".tablename('mgamblemoon_user')." WHERE  from_user = '{$fromuser}' AND rid=".$id);
		
		$arr_times=$this->get_today_times($totals,$mgamblemoon['maxlottery'],$mgamblemoon['prace_times'],$myuser['count']);
	
		include $this->template('info');