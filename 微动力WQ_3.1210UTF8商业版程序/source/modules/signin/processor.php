<?php
	/**
	 * 签到模块
	 * 作者：艮随
	 */
defined('IN_IA') or exit('Access Denied');

class SigninModuleProcessor extends WeModuleProcessor {
	
	public function respond() {
	
		global $_W;
		
		$rid = $this->rule;
		
		$sql = "SELECT * FROM " . tablename('signin_reply') . " WHERE `rid`=:rid LIMIT 1";
		
		$row = pdo_fetch($sql, array(':rid' => $rid));
		
		if (empty($row['id'])) {
		
			return array();
		}
		
		$now = time();
		
		$start_time = $this->module['config']['start_time'];
		
		$start_time = strtotime($start_time);
		
		$end_time = $this->module['config']['end_time'];
		
		$end_time = strtotime($end_time);
		
		$date=date('Y-m-d');
		
		$date = strtotime($date);
		
		$times = $this->module['config']['times'];

		$credit = $this->module['config']['credit'];
		
		$limit = $this->module['config']['rank'];
		
		$message = $this->message;

		$from = $message['from'];

		$todaytotal = pdo_fetchall("SELECT * FROM " . tablename('signin_record') . " WHERE `time` >= :date ", array(':date' => $date));
		
		$totalnum = count($todaytotal);
		
		$userrank = $totalnum+1;
		
		$todaysignin = pdo_fetchall("SELECT * FROM " . tablename('signin_record') . " WHERE `from_user` = :from_user and `time` >= :date ", array(':from_user' => $from, ':date' => $date));
		
		$signinednum = count($todaysignin);

		$signinnum = $signinednum+1;
		
		$profile = fans_search($from);
		
		if(!empty($profile['realname'])){
		
		    if($now >= $start_time && $now <= $end_time){
			
			    if($signinednum < $times){
				
					$insert = array(
					
					'id' => null,
					
					'weid' => $_W['weid'],
					
					'from_user' => $from,
					
					'name' => $profile['realname'],
					
					'time' => $now,
					
					'rank' => $userrank,
					
					);
					
					pdo_insert('signin_record', $insert);

					$data = array(

						'credit1' => $credit + $profile['credit1'],

					);

					fans_update($from, $data);
					
					$top = "SELECT * FROM " . tablename('signin_record') . " WHERE `time` >= :date order by rank asc limit $limit";
					
					$rs = pdo_fetchall($top, array(':date' => $date));
					
					$value = array(); 
	
					foreach( $rs as $value )
					{

						$record.='NO.'.$value['rank'].'      '.$value['name'].'      '.date('H:i',$value['time'])."\n";

					}
					
					$nowcredite = fans_search($from);
					
					return $this->respText('这是您今天第'.$signinnum.'次签到'."\n\n".'排名第'.$userrank."\n\n".'本次获取'.$credit.'个积分'."\n\n".'累计拥有'.$nowcredite['credit1'].'个积分'."\n\n".'今日签到排行榜：'."\n\n".$record);
				
				}
				else{


					$top = "SELECT * FROM " . tablename('signin_record') . " WHERE `from_user` = :from_user and `time` >= :date order by rank asc limit 10";
					
					$rs = pdo_fetchall($top, array(':from_user' => $from, ':date' => $date));
					
					$value = array(); 
	
					foreach( $rs as $value )
					{

						$record.='NO.'.$value['rank'].'      '.date('m-d H:i:s',$value['time'])."\n";

					}
			  
					return $this->respText($row['overnum']."\n\n".'您的签到记录为'."\n".$record);
				
				}
		  
		    }
		    else{
			  
			  return $this->respText($row['overtime']);
		  
		    }
			
		}
		else{
		
			return $this->respNews(array(

			'Title' => "请先登记",

			'Description' => "点击进入登记",

			'PicUrl' => "",

			'Url' => $this->createMobileUrl('register'),
			
			));

		}
			
	
	}

}