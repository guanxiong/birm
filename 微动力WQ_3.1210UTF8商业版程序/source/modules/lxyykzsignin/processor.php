<?php
/**
 * 签到活动
 *
 * @author 
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class LxyykzsigninModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
		
		 $replytable = 'lxy_ykz_signin_reply';	
		 $sigintable = 'lxy_ykz_signin_record';	
		 $wintable = 'lxy_ykz_signin_winner';	
		
		
		
		global $_W;		
		$rid = $this->rule;		
		$weid=$_W['weid'];
		$sql = "SELECT * FROM " . tablename($replytable) . " WHERE `rid`=:rid LIMIT 1";		
		$row = pdo_fetch($sql, array(':rid' => $rid));		//签到规则回复设置
		if (empty($row['id'])) {		
			return array();	
			}		
		$now = time();		
		$start_time = $this->module['config']['start_time'];				
		$start_time = strtotime($start_time);		
		
		$end_time = $this->module['config']['end_time'];			
		$end_time = strtotime($end_time);		
		
		$start_date_time=strtotime($row['s_date']);
		$end_date_time=strtotime($row['e_date']);
		
		$par_continudays = $row['continuedays'];//连续签到天数
		$par_sumdays = $row['sumdays'];//累计签到天数
		$par_sumfirst= $row['sumfirst'];//累计第一天数

		$todaystart=strtotime(date('Y-m-d'));//今天
		$yesterdaystart=$todaystart-86400;	//昨天
		$times = $this->module['config']['times'];//一天允许重复签到次数
		$credit = $this->module['config']['credit'];//每次获奖的积分		
		$limit = $this->module['config']['rank'];	//排行榜前几名显示	
		$message = $this->message;
		$from = $message['from'];
		
		
		
		//用户的排名
		$userrank = pdo_fetchcolumn("SELECT count(1)+1 as paimin FROM " . tablename($sigintable) . " WHERE  `time` >= :date and `rid`=:rid", array(':date' => $todaystart,':rid'=>$rid));		
		
		//用户昨天的情况
		$yeserdayrs = pdo_fetch("SELECT rank,continuedays,sumdays,sumfirst FROM " . tablename($sigintable) . "   WHERE  `from_user` = :from_user and `rid`=:rid and  `time` >= :date and `time` <:date1", array(':from_user' => $from,':rid'=>$rid,':date' => $yesterdaystart,':date1'=>$todaystart));
	
		//获取签到之前的相关信息
		$yeserdayrank=intval($yeserdayrs['rank']);//昨天的排名
		$yes_continuedays=intval($yeserdayrs['continuedays']);		
		
		$rs_befornow=pdo_fetch("SELECT sumdays,sumfirst FROM " . tablename($sigintable) . " WHERE `from_user` = :from_user and `rid`=:rid order by time desc limit 1", array( ':rid'=>$rid ,':from_user' => $from));
		
		$yes_sumdays=intval( $rs_befornow['sumdays']);
		$yes_sumfirst=intval($rs_befornow['sumfirst']);

		//签到次数
		$signinnum = pdo_fetchcolumn("SELECT count(1) as zs FROM " . tablename($sigintable) . " WHERE `from_user` = :from_user and `rid`=:rid and `time` >= :date ", array( ':rid'=>$rid ,':from_user' => $from, ':date' => $todaystart));
		
		//定义今天结果变量
		$how_continuedays=-1;//本次签到是第几次连续签到
		$how_sumdays=-1;//本次签到是累计第几次签到
		//本次第一次签到是累计第几次
		$how_sumfirst=-1;
		$how_credit_unit=0;//今天获得的积分单位(要乘以设置值)，按照每获奖一次获得一个单位积分
		
		//最新的获奖情况
		$winrs = pdo_fetch("SELECT id, wcontinuedays,wsumdays,wsumfirst FROM " . tablename($wintable) . "   WHERE  `from_user` = :from_user and `rid`=:rid  ", array(':from_user' => $from,':rid'=>$rid));
		$how_wcontinuedays=intval($winrs['wcontinuedays']);//累计连续签到获奖次数
		$how_wsumdays=intval($winrs['wsumdays']);//累计X天签到获奖次数
		$how_wsumfirst=intval($winrs['wsumfirst']);//累计X天第一的获奖次数
		
		//今天的签到信息
		$how_sumdays=$yes_sumdays+1;//累计签到		
		if(	$userrank==1)//签到第一
		{
			$how_sumfirst=$yes_sumfirst+1;
		}
		else 
		{
			$how_sumfirst=$yes_sumfirst;
		}
		//昨天签到过了
		if(!empty($yeserdayrank))
		{		
			$how_continuedays=$yes_continuedays+1;//累计
		}
		else
		{
			//昨天没有签到
			$how_continuedays=1;//从今天开始算连续签到
     	}

     	
     	//如果统计阈值超过设置值累加奖品
     	//获连续签到奖了
     	$flag_continudays=false;
     	$flag_sumdays=false;
     	$flag_sumfirst=false;
     	
     	if($par_continudays>0&&$how_continuedays>=$par_continudays)//有获奖规则设置
     	{
     		$flag_continudays=true;
     		$how_credit_unit++;
     		$how_wcontinuedays++;//获奖次数+1
     		$how_continuedays=0;//清零重置
     	}
     	//累计签到奖
     	if($par_sumdays>0&&$how_sumdays>=$par_sumdays)
     	{
     		$flag_sumdays=true;
     		$how_credit_unit++;
     		$how_wsumdays++;//获奖次数+1
     		$how_sumdays=0;//清零重置
     	}
     	//累计第一奖
     	if($par_sumfirst>0&&$how_sumfirst>=$par_sumfirst)
     	{
     		$flag_sumfirst=true;
     		$how_credit_unit++;
     		$how_wsumfirst++;
     		$how_sumfirst=0;//清零重置
     	}	
		
     	$fenge.="--------------------------\n";
     	$awardflag='☆';
     	
		//进入判断是否登记个人信息
		$profile = fans_search($from);		
		if(!empty($profile['nickname'])){		
		    if($now >= $start_time && $now <= $end_time&&$start_date_time<=$todaystart&&$end_date_time>=$todaystart)
		    {	
		    	//没有超出签到时间
		    	//$logstr=$signinnum.'-'.$times;
		    	
			    if($signinnum < $times){		
		
					$insert = array(			
					'id'=>null,		
					'rid' => $rid,					
					'weid' => $_W['weid'],					
					'from_user' => $from,					
					'name' => $profile['nickname'],					
					'time' => $now,					
					'rank' => $userrank,
					'continuedays'=>$how_continuedays,
					'sumdays'=>$how_sumdays,			
					'sumfirst'=>$how_sumfirst,
					);			
					

					//签到记录插入
					pdo_insert($sigintable, $insert);


					//判断是否需要变更获奖者记录信息
					if($how_credit_unit>0)
					{
						$wdata=array(
								'id'=>null,
								'rid' => $rid,
								'weid' => $_W['weid'],
								'from_user' => $from,
								'name' => $profile['nickname'],								
								'wcontinuedays'=>$how_wcontinuedays,
								'wsumdays'=>$how_wsumdays,
								'wsumfirst'=>$how_wsumfirst,
						);
						//获奖者记录
						if(empty($winrs))
						{
							pdo_insert($wintable, $wdata);
						}
						else
						{
							unset($wdata['id']);
							unset($wdata['rid']);
							unset($wdata['weid']);
							unset($wdata['from_user']);
							unset($wdata['name']);							
							pdo_update($wintable,$wdata,array('id'=>$winrs['id']));
						}
					}			

					//粉丝的积分计算
					$nowcredite=$how_credit_unit*$credit + $profile['credit1'];
					$data = array(
						'credit1' =>$nowcredite,
					);
					fans_update($from, $data);	
					
					//开始进行排名及中奖情况展示
					$top = "SELECT * FROM " . tablename($sigintable) . " WHERE  `rid`=:rid  and  `time` >= :date order by rank asc limit $limit";					
					$rs = pdo_fetchall($top, array(':rid'=>$rid, ':date' => $todaystart));					
					$value = array(); 	
					foreach( $rs as $value )
					{
						$record.='NO.'.$value['rank'].'   '.$value['name'].'   '.date('H:i',$value['time'])."\n";
					}					

					
					//表示设置了竞赛规则
					if($par_continudays||$par_sumdays||$par_sumfirst)
					{
						$str4=$row['awardrules'];
						
						
						if($how_continuedays>=0&&$par_continudays)
						{
							$str1='历史连续签到：'.$how_continuedays."天\n";							
						}
						if($how_wcontinuedays>=0&&$flag_continudays)
						{
							$str1.=str_replace('{s3}', $how_wcontinuedays, $awardflag.$row['continuedaystip'])."\n";
						}
						if($str1!="")
						{
							$str1.=$fenge;
						}
						
						
						if($how_sumdays>=0&&$par_sumdays)
						{
							$str2='历史签到：'.$how_sumdays."次\n";
						}
						if($how_wsumdays>0&&$flag_sumdays)
						{
							$str2.=str_replace('{s2}', $how_wsumdays, $awardflag.$row['sumdaystip'])."\n";
						}
						if($str2!="")
						{
							$str2.=$fenge;
						}
						
						if($how_sumfirst>=0&&$par_sumfirst)
						{
							$str3='历史签到NO.1：'.$how_sumfirst."次\n";
						}
						if($how_wsumfirst>0&&$flag_sumfirst)
						{
							$str3.=str_replace('{s1}', $how_wsumfirst, $awardflag.$row['sumfirsttip'])."\n";
						}
						if($str3!="")
						{
							$str3.=$fenge;
						}
					}
					$gglist=$this->getggg($rid);//获取广告
					$strall='签到竞赛规则:'."\n".$str4."\n".'今天签到☞NO.'.$userrank."\n".$fenge.$str2.$str1.$str3.'累计拥有'.$nowcredite.'个积分'."\n".'今日签到竞赛榜：'."\n".$record.$fenge.$gglist['3'];
					return $this->respText($strall);
				}				
				else{
					//已经签到过的显示内容
					
					$top = "SELECT a.rank,a.name,a.time,a.continuedays,a.sumdays,a.sumfirst,b.wcontinuedays,b.wsumdays,b.wsumfirst FROM " . tablename($sigintable) . " a left join ".tablename($wintable)." b on a.from_user=b.from_user and a.rid=b.rid  WHERE a.rid={$rid}  and a.from_user = '{$from}' and a.time >= {$todaystart} order by a.rank asc limit 10";
					
					//return $this->respText($top);
					$rs = pdo_fetchall($top);

					$value = array(); 
					foreach( $rs as $value )
					{
					$record.='NO.'.$value['rank'].'   '.$value['name'].'   '.date('m-d',$value['time'])."\n";
					}
					
					
					$top = "SELECT a.rank,a.name,a.time,a.continuedays,a.sumdays,a.sumfirst,b.wcontinuedays,b.wsumdays,b.wsumfirst FROM " . tablename($sigintable) . " a left join ".tablename($wintable)." b on a.from_user=b.from_user and a.rid=b.rid  WHERE a.rid={$rid}  and a.from_user = '{$from}' and a.time >= {$todaystart} order by a.time desc limit 1";
				
					//return $this->respText($top);
					$rs = pdo_fetch($top);
					//获奖情况获取
						
						if($par_continudays)
						{
							$str1='历史连续签到：'.$rs['continuedays']."天\n";
							if($rs['wcontinuedays']>0)
							{
								$str1.=str_replace('{s3}', $rs['wcontinuedays'], $awardflag.$row['continuedaystip'])."\n";
							}							
						}	

						if($par_sumdays)
						{
							$str2='历史签到：'.$rs['sumdays']."次\n";
							if($rs['wsumdays']>0)
							{
								$str2.=str_replace('{s2}', $rs['wsumdays'], $awardflag.$row['sumdaystip'])."\n";
							}
						}
					
						
						if($par_sumfirst)
						{
							
							$str3='历史签到NO.1：'.$rs['sumfirst']."次\n";
							if($rs['wsumfirst']>0)
							{
								$str3.=str_replace('{s1}', $rs['wsumfirst'], $awardflag.$row['sumfirsttip'])."\n";
							}
						}
					
					$gglist=$this->getggg($rid);//获取广告
					if($str3!="")
					{
						$str3.=$fenge;
					}
					if($str2!="")
					{
						$str2.=$fenge;
					}
					if($str1!="")
					{
						$str1.=$fenge;
					}
					$strall=$row['overnum']."\n".$fenge.$str2.$str1.$str3.'您的签到记录为'."\n".$record.$fenge.$gglist['2'];
					return $this->respText($strall);
				}
		    }
		    //超出了签到时间
		    else{
		    	$gglist=$this->getggg($rid);//获取广告
			  return $this->respText($row['overtime']."\n".$fenge.$gglist['1']);		  
		    }
		}			
		
		else{
			$rs=pdo_fetch("select * from ".tablename($replytable)." where rid='{$rid}'");		
			
			return $this->respNews(array(
					'Title' => $rs['title'],
					'Description' => $rs['description'],
					'PicUrl' => $_W['attachurl'] . $rs['picture'],
					'Url' =>$_W['siteroot'].$this->createMobileUrl('register') ,
			));
			
		}	
		
	}
	
	//获取广告
	private function getggg($rid)
	{
		$replytable = 'lxy_ykz_signin_reply';
		$top = "SELECT * FROM " . tablename($replytable) . " WHERE  `rid`=:rid";
		$rs = pdo_fetch($top, array(':rid'=>$rid));
		if(empty($rs))
		{
			return null;
		}
		$prex='☞';
		$ggs=array();
		
		for($i=1;$i<4;$i++)
			{
				$key='gg'.$i;
				for($j=1;$j<6;$j++)
				{
					if(!empty($rs["ggtext$i$j"])&&!empty($rs["gglink$i$j"]))
					{	
						$$key.=$prex. "<a href='".$rs["gglink$i$j"]."'>".$rs["ggtext$i$j"]."</a>\n";
					}
					if(!empty($rs["ggtext$i$j"])&&empty($rs["gglink$i$j"]))
					{
						$$key.=$prex. $rs["ggtext$i$j"]."\n";
					}
				}
				$ggs[$i]=$$key;
			}
		return $ggs;
	}

}