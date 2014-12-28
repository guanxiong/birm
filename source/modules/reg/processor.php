<?php
/**
 * 会员注册类
 *
 * [19.3cm QQ 81324093] Copyright (c) 2013 WangXingLin.com
 */
defined('IN_IA') or exit('Access Denied');

class regModuleProcessor extends WeModuleProcessor {

	public $name = 'regChatRobotModuleProcessor';
	
	public function isNeedInitContext() {
		return 0;
	}
	
	public function respond() {
		global $_W;
		$rid = $this->rule;
		$fromuser=$this->message['from'];
		$message = $this->message;
		$member = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = '{$fromuser}' LIMIT 1");
		$reg = pdo_fetch("SELECT * FROM ".tablename('reg_reply')." WHERE rid = '{$rid}' LIMIT 1");
		
		//开始进入，启动上下文
		if(!$this->inContext) {
			//先判断用户是否存在,进行相应处理。
			if (!empty($member)) {
			
			pdo_update('fans', array('isjoin' => 1,'rid' =>$rid,'createtime' => TIMESTAMP), array('from_user' => $this->message['from']));
			} 
			else 
			{
				$data = array(
				'from_user' => $this->message['from'],
				'isjoin' => 1,
				'createtime' => TIMESTAMP,
				'rid' =>$rid,
			);
			
			pdo_insert('fans', $data);
			}
		
		$this->beginContext($reg['timeout']); 
		if(empty($member['avatar']))
		{ $tip = '您还未上传头像，请从手机里发送一张您的相片作为您的头像。谢谢- 更新自己坐标信息前请先进入<a target="_blank" href="'. create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])).'">会员信息登记</a>您的信息。';} 
        elseif(empty($member['x'])||empty($member['y'])||empty($member['city']))
		{
        $tip = '请点对话框左侧的+号按钮，然后发送坐标信息给我，补充您的位置信息。谢谢- 更新自己坐标信息前请先进入<a target="_blank" href="'. create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])).'">会员信息登记</a>您的信息。';
		} 
		elseif($member['gender']=='0')
		{$tip= '您的性别等信息还未完善。<a tart="_blank" href="'. create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])).'">请点此完善您的会员资料</a>。';
		}
		elseif (!in_array('',$member)) {
      	$tip=$member['nickname'].'已经完成会员注册，请输入退出,如果您要更改您的资料。您可以输入：[更新] 来更改自己的资料，也可以输入：[我的信息] 查看自己的资料';
		}
		else{
		$tip = $reg['enter_tips'] . (empty($member['nickname']) ? ' - 更新自己坐标信息前请先进入<a target="_blank" href="'. create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])).'">会员信息登记</a>您的信息。' : '');
		}
		
		return $this->respText($tip);
		
	}
	else
	{
		//这块为退出上下文的处理
		if ( $member['isjoin'] == 1&&!empty($reg['timeout']) && !empty($member) && $reg['timeout'] > 0 && TIMESTAMP - $member['createtime'] >= $reg['timeout']) 
		{
			pdo_update('fans', array('isjoin' => 0), array('from_user' => $this->message['from']));
			$this->endContext();
			return $this->respText($member['nickname'].$reg['quit_tips']);
			
		}
		elseif ((empty($reg['quit_command']) && $message['content'] == '退出') ||(!empty($reg['quit_command']) && $message['content'] == $reg['quit_command'])) 
		{
			
			$this->endContext();
			return $this->respText($member['nickname'].$reg['quit_tips']);
		}
		else
		{
			//开始录入资料操作
				//坐标信息
			if($this->message['type']=='location'){ 
				$x  = urldecode($this->message['location_x']);//获取微信按下的纬度
 				$y  = urldecode($this->message['location_y']);//获取微信按下的经度
				$xy = $x.",".$y;
				$url = "http://api.map.baidu.com/geocoder/v2/?ak=05b16f0c022c323a701015e4ee789c0b&callback=renderReverse&location=".$xy."&output=xml&pois=0";
				$loc = file_get_contents($url);
				$loc = (array)simplexml_load_string($loc);
				$add = (array)$loc['result'];
				$streets = (array)$add['addressComponent'];
				$province = $streets['province'];
				$city = $streets['city'];
				$district = $streets['district'];
				$street = $streets['street'];	
				$cityall = $province.",".$city.",".$district.",".$street;
				//==以上为获取到的地址信息，下面是数据库操作==
				$data = array(
					'x' => $x,
					'y' => $y,
					'resideprovince'=>$province,
					'residecity'=>$city,
					'residedist'=>$district,
					'address'=>$street,
					'city' => $cityall,
					'createtime' =>TIMESTAMP
					);	
				pdo_update('fans', $data, array(from_user => $message['from']));
				$ts="已经更新了您的位置信息";
				return $this->respText($member['nickname'].$ts);
				}
				//头像
			if ($message['type'] == 'image') {
			$image = ihttp_request($message['picurl']);
			$filename = 'avatar/' . $member['rid'] . '/' . salt(30) . '.jpg';
			$tou=file_write($filename, $image['content']);
			if($tou){
				$data['avatar'] = $filename;
				$ts="已经更新了您的头像";
			}else{
				$ts='头像生成失败。请重新发送图片重试。';
			}
			$data['createtime'] = TIMESTAMP;
			pdo_update('fans', $data, array(from_user => $message['from']));
			
			return $this->respText($member['nickname'].$ts);
			}
			//文字信息，根据内容提示
			if ($message['type'] == 'text') {
					if ($message['content'] == '更新') {
						$ts= '请点这儿<a target="_blank" href="'. create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])).'">更新您的信息</a>。';
						return $this->respText($ts);
						}
					elseif ($member['isblacklist']=='1') {
						$ts= '你已被列入黑名单，请联系管理员审核！';
						return $this->respText($ts);
						}
					
					//返回新闻信息	
					elseif ($message['content'] == '查询'||$message['content'] == '我的信息') {
							if (strexists($member['avatar'], 'avatar_'))
								{
								$headpic=$_W['siteroot']. 'resource/image/avatar/'.  $member['avatar'];
								}
								else
								{
								$headpic=$_W['attachurl'].$member['avatar'];
								}
							if($member['gender']==0){
								$member['sex']='保密';
								}
							if($member['gender']==1){
								$member['sex']='男';
								}
							if($member['gender']==2){	$member['sex']='女';}
							$title="真名：".$member['realname']." 昵称:".$member['nickname']." 性别:".$member['sex'];
							$news[1] = array(
             		 		'title' => $title,
        					'description' => '',
        					'picirl' => $headpic,
        					'url' => create_url('mobile/module', array('name' => 'fans', 'do' => 'profile', 'weid'=>$_W['weid'])),
							);
							$news[2] = array(
              				'title' => 'QQ:'.$member['qq'].' 微信:'.$member['wxusr'].' 电话:'.$member['mobile'],
        					'description' => '',
        					'picirl' => '',
        					'ul' => '',
							);
							$news[3] = array(
              				'title' => '年龄:'.$member['age'].' 积分:'.$member['credit'],
        					'description' => '',
        					'picirl' => '',
        					'url' => '',
							);
							$news[4] = array(
              				'title' => '地址:'.$member['city'],
        					'description' => '',
        					'picurl' => '',
        					'url' => '',
							);
							
						return $this->respNews($news);		
					}
					else{
					$ts=$reg['send_tips'];
					return $this->respText($ts);
					}
				
			}
		
		}
	
	}
}

public function isNeedSaveContext() {
		return false;
	}
}
	