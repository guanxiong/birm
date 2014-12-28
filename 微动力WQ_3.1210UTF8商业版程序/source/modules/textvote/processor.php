<?php
/**
 * 文本投票模块处理程序
 *
 * @author nbnat.com
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class TextvoteModuleProcessor extends WeModuleProcessor {
	public $tabl_vote='nb_textvote';
		
	public function respond() {
		global $_W;	
		$rid = $this->rule;
		$from= $this->message['from'];
		$message = $this->message['content'];
        $sql = "SELECT * FROM " . tablename($this->tabl_vote) . " WHERE `rid`=:rid LIMIT 1";
		$row = pdo_fetch($sql, array(':rid' => $rid));
		$now = TIME();
		
		$info=json_decode($row['config']);
		$res=json_decode($row['result']);
	
		if($now >= $row['start_time'] && $now <= $row['end_time']){
			if ($this->inContext) {
				if(is_numeric((int)$message) && ((int)$message>=1 && (int)$message<=count($info))){
					$res[$message-1]++;	

					$_res=json_encode($res);
					$insert=array('result'=>$_res);
					pdo_update($this->tabl_vote, $insert, array('id' => $row['id']));
					$this->endContext();
					$reply="你投票给了{$message} 共有 ({$res[$message-1]}) 谢谢您!";
				}else{
					
					$reply="你的投票无效！";
				}
				
				return $this->respText($reply);
			} else {
				
				$_info="本次投票选项:\r\n";
				foreach($info as $k=>$v){
					$_info.=($k+1).'.'.$v."({$res[$k]})\r\n";
				}
				$_info.="回复相应数字进行投票！";
				$reply=$_info;
				$this->beginContext(3600);
				return $this->respText($reply);
				
			}
		}else{
			$reply = $rid."亲，投票活动已结束了！";
			return $this->respText($reply);				
		}
        
        // 返回至系统

	}
}