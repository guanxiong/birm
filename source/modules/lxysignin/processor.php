<?php
/**
 * 签到活动
 *
 * @author 大路货
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class LxysigninModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
	
		global $_W;		
		$rid = $this->rule;		
		$weid=$_W['weid'];
		$sql = "SELECT * FROM " . tablename('lxy_signin_reply') . " WHERE `rid`=:rid LIMIT 1";		
		$row = pdo_fetch($sql, array(':rid' => $rid));		
		if (empty($row['id'])) {		
			return array();	
			}		
		$now = time();		
		$start_time = $this->module['config']['start_time'];		
		$start_time = strtotime($start_time);		
		$end_time = $this->module['config']['end_time'];		
		$end_time = strtotime($end_time);		
		$par_days = $row['days'];

		$todaystart=strtotime(date('Y-m-d'));
		$yesterdaystart=$todaystart-86400;	
		$times = $this->module['config']['times'];
		$credit = $this->module['config']['credit'];		
		$limit = $this->module['config']['rank'];		
		$message = $this->message;
		$from = $message['from'];
		$userrank = pdo_fetchcolumn("SELECT count(1)+1 as paimin FROM " . tablename('lxy_signin_record') . " WHERE  `time` >= :date and `weid`=:weid", array(':date' => $todaystart,':weid'=>$weid));		
		$yeserdayrs = pdo_fetchcolumn("SELECT rank,continuedays,continuefirst FROM " . tablename('lxy_signin_record') . " WHERE  `from_user` = :from_user and `weid`=:weid and  `time` >= :date and `time` <:date1", array(':from_user' => $from,':weid'=>$weid,':date' => $yesterdaystart,':date1'=>$todaystart));
		$yeserdayrank=$yeserdayrs['rank'];
		$continuedays=$yeserdayrs['continuedays'];
		$continuefirst=$yeserdayrs['continuefirst'];
		
		$signinnum = pdo_fetchcolumn("SELECT count(1) as zs FROM " . tablename('lxy_signin_record') . " WHERE `from_user` = :from_user and `weid`=:weid and `time` >= :date ", array( ':weid'=>$weid ,':from_user' => $from, ':date' => $todaystart));
		
		$howdays=1;
		$howfirst=0;
		if(empty($yeserdayrank))
		{			
			if(	$userrank==1)
			{
				$howfirst=1;
			}			
		}
		else
		{
			$howdays=$continuedays+1;
			if(($yeserdayrank==1)&&($userrank==1))
			{				
				$howfirst=$continuefirst+1;			
			}
		}
		
		$profile = fans_search($from);		
		//if(!empty($profile['realname'])){		
		    if($now >= $start_time && $now <= $end_time){	
		    	//$logstr=$signinnum.'-'.$times;
			    if($signinnum < $times){				
					$insert = array(					
					'id' => null,					
					'weid' => $_W['weid'],					
					'from_user' => $from,					
					'name' => $profile['nickname'],					
					'time' => $now,					
					'rank' => $userrank,
					'continuedays'=>$howdays,
					'continuefirst'=>$howfirst,					
					);					
					pdo_insert('lxy_signin_record', $insert);
					$data = array(
						'credit1' => $credit + $profile['credit1'],
					);
					fans_update($from, $data);					
					$top = "SELECT * FROM " . tablename('lxy_signin_record') . " WHERE  `weid`=:weid and  `time` >= :date order by rank asc limit $limit";					
					$rs = pdo_fetchall($top, array(':weid'=>$weid,':date' => $todaystart));					
					$value = array(); 	
					foreach( $rs as $value )
					{
						$record.='NO.'.$value['rank'].'      '.$value['name'].'      '.date('H:i',$value['time'])."\n";
					}					
					$nowcredite = fans_search($from);		

					if($par_days>0)
					{
						$str4="\n\n".$row['awardrules'];
						if($howdays>0)
						{
							$str1='累计'.$howdays.'天连续签到'."\n\n";
						}
						if($howfirst>0)
						{
							$str2='累计'.$howdays.'天连续NO.1'."\n\n";
									if($howdays>=$par_days)
									{
										$str2.='/:hug恭喜您已获胜/:hug'."\n\n".$row['awardinfo'];
									}
								$str2.="\n\n";
						}
					}
					
					$str3='签到竞赛规则:'."\n".$str4."\n"."\n".'今天签到☞NO.'.$userrank."\n".$str2.$str1."\n".'累计拥有'.$nowcredite['credit1'].'个积分'."\n".'今日签到竞赛榜：'."\n\n".$record;
					return $this->respText($str3);
				}
				else{
					$top = "SELECT * FROM " . tablename('lxy_signin_record') . " WHERE `weid` = :weid and `from_user` = :from_user and `time` >= :date order by rank asc limit 10";
					$rs = pdo_fetchall($top, array(':weid'=>$weid,':from_user' => $from, ':date' => $todaystart));
					$value = array(); 
					foreach( $rs as $value )
					{
					$record.='NO.'.$value['rank'].'      '.date('m-d',$value['time'])."\n";
					}
					return $this->respText($row['overnum']."\n\n".'您的签到记录为'."\n".$record);
				}
		    }
		    else{
			  return $this->respText($row['overtime']);		  
		    }			
		/*}
		else{		
			return $this->respNews(array(
			'Title' => "请先登记",
			'Description' => "点击进入登记",
			'PicUrl' => "",
			'Url' => $this->createMobileUrl('register'),			
			));
		}	
		*/
	}

}