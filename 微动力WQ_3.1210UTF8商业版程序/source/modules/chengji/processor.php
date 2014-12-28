<?php
/**
 * 成绩查询模块处理程序
 *
 * @author Yoby
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class ChengjiModuleProcessor extends WeModuleProcessor {
	public function respond() {
	global $_W;
		$content = $this->message['content'];
		
		$zfurl = "http://218.94.144.230/ly/";//地址改成你域名
		
	$re = preg_match('/^绑定.*/', $content, $matchs);
	if($re){
		    $match[1] =  preg_replace("/绑定/", "", $matchs[0]);
$arrs  =   preg_split("/[\s]+/", $match[1]);
      if(empty($arrs[0]) || empty($arrs[1])){
      return $this->respText("绑定失败,请使用关键字:绑定+用户名+空格+密码,例如:绑定12345678 woshimima");
      }else{
      $cj = pdo_fetch("SELECT * FROM ".tablename('chengji')." where from_user='{$this->message['from']}'");
      if(empty($cj)){
        $cfga = array('cj_user'=>$arrs[0],
        'cj_pass'=>$arrs[1],
        "weid"=>$_W['weid'],
        'from_user'=>$this->message['from'],
        'createtime'=>time()
        );
        pdo_insert('chengji', $cfga);
				$reid = pdo_insertid();
				if($reid) {
					return $this->respText("亲,您绑定成功,下次直接查询");
				}
				
				}
				
				else{
					return $this->respText('你已绑定过了,亲不要胡来,直接查询!');
				}
        
        }
	}
		$cj = pdo_fetch("SELECT cj_user,cj_pass FROM ".tablename('chengji')." where from_user='{$this->message['from']}'");
		$user = $cj['cj_user'];
		$pwd = $cj['cj_pass'];
		if(empty($cj)){
      return $this->respText("绑定学号密码后才能查询,请发送:绑定+用户名+空格+密码.\n例如:绑定12345678 123abc");
}
switch($content){
	case '查成绩':
	$rs = json_decode(file_get_contents($zfurl.'zfapi.php?user='.$user.'&pwd='.$pwd.'&op=chengji'),TRUE);
	if(is_array($rs)){
	$info  ='';
	foreach($rs as $v1){
		$info .= $v1[0].$v1[1].$v1[2].$v1[3].$v1[4].$v1[5];
	}}else
	{
		$info  =$rs;
	}
		break;
	case '查课表':
	$rs = json_decode(file_get_contents($zfurl.'zfapi.php?user='.$user.'&pwd='.$pwd.'&op=kebiao'),TRUE);
	$xq =date('w',time());
	$xq1 = ($xq==0)?'7':$xq;
	$info1  ='';
	foreach($rs[$xq1] as $v1){
		if(!empty($v1)){
			$info1 .=$v1."\n";
		}
		
	}
	$info = (empty($info1))?"今日没有课哦":$info1;
		break;
	case '查等级考试':
	$rs = json_decode(file_get_contents($zfurl.'zfapi.php?user='.$user.'&pwd='.$pwd.'&op=dengji'),TRUE);
	if(is_array($rs)){
	$info  ='';
	foreach($rs as $v1){
		$info .= $v1[0].$v1[1].$v1[2].$v1[3].$v1[4].$v1[5].$v1[6].$v1[7].$v1[8].$v1[9];
	}
	}else{
		$info  =$rs;
	}
		break;
	case '查补考':
	$rs = json_decode(file_get_contents($zfurl.'zfapi.php?user='.$user.'&pwd='.$pwd.'&op=bukao'),TRUE);
	if(is_array($rs)){
			$info  ='';
	foreach($rs as $v1){
		$info .= $v1[0].$v1[1].$v1[2].$v1[3].$v1[4].$v1[5].$v1[6];
	}
	}else{
		$info  =$rs;
	}
	

		break;
}
return $this->respText($info);

		
		}
   
}