<?php

defined('IN_IA') or exit('Access Denied');
define('RES','./source/modules/cgtsignin/template/style/');

class CgtsigninModuleSite extends WeModuleSite {

	

	public function doWebDisplay(){
		global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
	    if (checksubmit('delete')) {		
			pdo_delete('cgt_signin_record', " from_user IN ('".implode("','", $_GPC['select'])."')");			
			message('删除成功！', $this->createWebUrl('display', array('id' => $id, 'page' => $_GPC['page'])));		
		}
		$pindex = max(1, intval($_GPC['page']));		
		$psize = 15;
		$sql="SELECT * FROM (SELECT *  FROM ".tablename('cgt_signin_record')." WHERE weid= :weid ORDER BY TIME DESC ) pcc GROUP BY from_user";
		$signinlist = pdo_fetchall($sql.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['weid']) );	
		$total = pdo_fetchcolumn('SELECT count(1) as totle FROM '.tablename('cgt_signin_record').' WHERE weid= :weid group by `from_user` desc ', array(':weid' => $_W['weid']) );
		$pager = pagination($total, $pindex, $psize);
		include $this->template('display');
	}

	//签到详细记录
	public function doWebDetail(){
		global $_GPC, $_W;
	    checklogin();
		$id = intval($_GPC['id']);
		$fromuser=$_GPC['fromuser'];
		
		 if (checksubmit('delete')) {		
			pdo_delete('cgt_signin_record', " id IN ('".implode("','", $_GPC['select'])."')");			
			message('删除成功！', $this->createWebUrl('detail', array('from_user' => $fromuser, 'page' => $_GPC['page'])));		
			}
		
		$pindex = max(1, intval($_GPC['page']));		
		$psize = 15;
		$sql="SELECT * FROM ".tablename('cgt_signin_record')." WHERE weid= :weid and from_user=:fromuser ";
		$signinlist = pdo_fetchall($sql.' order by `id` desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize, array(':weid' => $_W['weid'],':fromuser' =>$fromuser) );	
		$total = pdo_fetchcolumn('SELECT count(1) as totle FROM '.tablename('cgt_signin_record').' WHERE weid= :weid and from_user=:fromuser order by `id` desc ', array(':weid' => $_W['weid'],':fromuser' =>$fromuser) );
		$pager = pagination($total, $pindex, $psize);
		include $this->template('detail');
	}
	
	public function doMobileSignhome(){
		global $_GPC, $_W;
		$sql="select id from ".tablename("rule")." where weid=:weid and module=:module";
		$rids = pdo_fetch($sql, array(':weid' => $_W['weid'],':module'=>'cgtsignin'));	
		$_GPC['rid']=$rids['id'];
		print_r($_GPC['rid']);
	    $this->doMobileSignindex();
	}
	
	
	public function doMobileSigninfo() {
		global $_GPC, $_W;
	    $weid=$_W['weid'];
		$fromuser = $_W['fans']['from_user'];  
		$end=strtotime(date('Y-m-d'))+86400;
		$start=$end-806400;	
		$rid=intval($_GPC['rid']);

		 $sql = "SELECT thumb FROM " . tablename('cgt_signin_reply') . " WHERE `rid`=:rid LIMIT 1";		
		$thumb= pdo_fetchcolumn($sql, array(':rid' => $rid));		
		//$thumb=$reply['thumb'];
		//print_r($reply);
		$row = pdo_fetchall("SELECT time,FROM_UNIXTIME(time, '%Y-%m-%d' )  as time1, FROM_UNIXTIME(time,'%T') time2 FROM " . tablename('cgt_signin_record') . 
        " WHERE  `from_user` = :from_user and `weid`=:weid and  `time` >= :date and `time` <:date1 order by 1 desc", 
			array(':from_user' => $fromuser,':weid'=>$weid,':date' => $start,':date1'=>$end));
	  include $this->template('signinfo');		
	}
	
	
	public function doMobileSignindex() {
        global $_GPC, $_W;
	    $fromuser = $_W['fans']['from_user'];  
	    $profile = fans_require($fromuser, array('realname', 'mobile'), '需要完善资料才能签到.');
		
		$rid=intval($_GPC['rid']);
	   $sql = "SELECT * FROM " . tablename('cgt_signin_reply') . " WHERE `rid`=:rid LIMIT 1";		
		$row = pdo_fetch($sql, array(':rid' => $rid));		

	    $todaystart=strtotime(date('Y-m-d'));
		$yesterdaystart=$todaystart-86400;	
		$nowday=$todaystart+86400;	
		$weid=$_W['weid'];
       $fromuser = $_W['fans']['from_user'];  
	    if ($fromuser==""){
	    	include $this->template('signindex');
	    	return;
	    }
	    
       
	    
	    $todayrs = pdo_fetch("SELECT continuedays FROM " . tablename('cgt_signin_record') . 
        " WHERE  `from_user` = :from_user and `weid`=:weid and  `time` >= :date and `time` <:date1", 
			array(':from_user' => $fromuser,':weid'=>$weid,':date' => $todaystart,':date1'=>$nowday));
		if ($todayrs!=false){
			$continuedays=$todayrs['continuedays'];
			 $leftdays=$row['days']-$continuedays;
		   include $this->template('signindex');
	    	return;
		}
	 
		$yeserdayrs = pdo_fetch("SELECT continuedays FROM " . tablename('cgt_signin_record') . 
        " WHERE  `from_user` = :from_user and `weid`=:weid and  `time` >= :date and `time` <:date1", 
			array(':from_user' => $fromuser,':weid'=>$weid,':date' => $yesterdaystart,':date1'=>$todaystart));
			
		$continuedays=$yeserdayrs['continuedays'];
		if(empty($continuedays))
		{			
	      $continuedays=1;	
		}
		else
		{
			$continuedays=$continuedays+1;
		}
		if ($row['days']>=$continuedays){
		 $leftdays=$row['days']-$continuedays;
		} else
	     $leftdays=0;
		
		 $profile = fans_search($fromuser);		
		    	
		 $insert = array(							
					'weid' => $_W['weid'],					
					'from_user' => $fromuser,					
					'name' =>$profile['realname'],	
					'mobile' =>$profile['mobile'],	
					'time' => time(),		
					'continuedays'=>$continuedays	,
					'credit'=>$profile['credit1']+$row["credit"]							
					);					
			$ret=pdo_insert('cgt_signin_record', $insert);
			if ($ret==false){
				message("出现错误");
			}
					
			$credit=array(credit1=>$profile['credit1']+$row["credit"]);
		   fans_update($fromuser, $credit);
		   include $this->template('signindex');
	} 
}
