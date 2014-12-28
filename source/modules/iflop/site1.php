<?php
/**
 * 翻牌抽奖
 * 作者:迷失卍国度/Zombieszy
 *
 * qq:15595755/214983937
 */
defined('IN_IA') or exit('Access Denied');
include "model.php";
class IflopModuleSite extends WeModuleSite {

	public $tablename = 'iflop_reply';
	public $tablefans = 'iflop_winner';
	
	public function getItemTiles() {
        global $_W;
        $articles = pdo_fetchall("SELECT id,rid, title FROM " . tablename('iflop_reply') . " WHERE weid = '{$_W['weid']}'");
        if (!empty($articles)) {
            foreach ($articles as $row) {
                $urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('index', array('id' => $row['rid'],'name' => 'iflop', 'id' => $rid, 'weid' => $_W['weid'])));
            }
            return $urls;
        }
    }
    
	public function doCheckedMobile() {
        global $_GPC, $_W;
        //$servername = $_SERVER['SERVER_NAME'];
        $useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
        if (strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false) {
            message('非法访问，请通过微信打开！');
        }
        /*if (empty($_W['fans'])) {
            echo "Error Code: 0X00001" . ";<br> Message: " . $_W['fans']['from_user'];
            $this->checkAuth();
            exit;
        }
        if ($_W['fans']['follow'] == 0) {
            echo "Error Code: 0X00002" . ";<br> Message: " . $_W['fans']['from_user'];
            $this->checkAuth();
            exit;
        }
        if (empty($_W['fans']['from_user']) || $_W['fans']['from_user'] == '') {
            echo "Error Code: 0X00003" . ";<br> Message: " . $_W['fans']['from_user'];
            $this->checkAuth();
            exit;
        }*/
    }	
    
 	private function checkAuth() {
        global $_W;
        $site = $GLOBALS['site'];
        $account = $GLOBALS['_W']['account'];
        $rid = intval($_GPC['rid']);
        if (!empty($rid)) {
            $keywords = pdo_fetchall("SELECT content FROM " . tablename('rule_keyword') . " WHERE rid = '{$rid}'");
        }
        if (!empty($GLOBALS['entry'])) {
            $rule = pdo_fetch("SELECT rid FROM " . tablename('cover_reply') . " WHERE module = '{$GLOBALS['entry']['module']}' AND do = '{$GLOBALS['entry']['do']}' AND weid = '{$account['weid']}'");
            $keywords = pdo_fetchall("SELECT content FROM " . tablename('rule_keyword') . " WHERE rid = '{$rule['rid']}'");
        }
        include template('auth', TEMPLATE_INCLUDEPATH);
    }
	
    public function doMobileIndex(){
        global $_W,$_GPC;
     //   $userAgent = $_SERVER['HTTP_USER_AGENT'];
     	
        $this->doCheckedMobile();
        
      /*  $sharefromuser = authcode(base64_decode($_GPC['sharefromuser']), 'DECODE') ; 
    	if (empty($sharefromuser)) {
			exit('非法参数！');
		}*/
		
        $id = intval($_GPC['id']);
     	if (empty($id)) {
            message('抱歉，参数错误！', '', 'error');
        }
        
    	$reply = pdo_fetch("SELECT * FROM " . tablename($this->tablename) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $id));
        
    	if ($reply == false) {
            message('抱歉，活动已经结束，下次再来吧！', '', 'error');
        }
    	//获得关键词
        $keyword = pdo_fetch("select content from ".tablename('rule_keyword')." where rid=:rid and type=1",array(":rid"=>$id));
        $reply['keyword']=  $keyword['content'];
        
        if (empty($_W['fans']) || $_GPC['share'] == 1) {
        	if (!empty($reply['share_url'])) {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $reply['share_url'] . "");
                exit();
            }
            $isshare = 1;
            $running = false;
            $msg = '请先关注公共号。';
        } else { 
            $fansID = $_W['fans']['id'];
            $from_user = $_W['fans']['from_user'];
            $fans = pdo_fetch("SELECT * FROM " . tablename($this->tablefans) . " WHERE rid = " . $id . " and fansID=" . $fansID . " and from_user='" . $from_user . "'");
            if ($fans == false) {
                $insert = array(
                    'rid' => $id,
                	'fansID' => $fansID,
                    'from_user' => $from_user,
                    'todaynum' => 0,
                    'totalnum' => 0,
                	'share_times' => 0,
                	'IPaddress' => $_W['clientip'],
                    'createtime' => time(),
                );
                
                $temp = pdo_insert($this->tablefans, $insert);
                if ($temp == false) {
                    message('抱歉，刚才操作数据失败！', '', 'error');
                }
                //增加人数，和浏览次数
                pdo_update($this->tablename, array('fansnum' => $reply['fansnum'] + 1, 'viewnum' => $reply['viewnum'] + 1), array('id' => $reply['id']));
            } else {
                //增加浏览次数
                pdo_update($this->tablename, array('viewnum' => $reply['viewnum'] + 1), array('id' => $reply['id']));
            } 
            
            //判断是否获奖
            $award = pdo_fetchall("SELECT * FROM " . tablename('iflop_award') . " WHERE weid=" . $_W['weid'] . " and rid = " . $id . " and fansID=" . $fansID . " and from_user='" . $from_user . "' order by id desc");
            if ($award != false) {
                $awardone = $award[0];
                $share_times = pdo_fetchcolumn("SELECT share_times FROM " . tablename($this->tablefans) . " WHERE rid = " . $id . " and fansID=" . $fansID . " and from_user='" . $from_user . "' order by id desc");
                if ($share_times > 0) {
	                if ($reply['share_times'] > 0 && $reply['share_times'] ==$share_times ) {//分享次数
	         			$isCan = 1;//可以领奖了
	        	 	}
                }
            }
            
            $running = true;
        	//判断是否可以刮刮
            if ($awardone && empty($fans['tel'])) {
                $running = false;
                $msg = '请先填写用户资料';
            }
         	//判断用户抽奖次数
            $nowtime = mktime(0, 0, 0);
            if ($fans['last_time'] < $nowtime) {
                $fans['todaynum'] = 0;
            }
            
        //判断总次数超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
            if ($running && $fans['totalnum'] >= $reply['number_times'] && $reply['number_times'] > 0) {
                $running = false;
                $msg = '您已经超过翻牌总限制次数，无法翻牌了!';
            }
            
        //判断当日是否超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
            if ($running && $fans['todaynum'] >= $reply['most_num_times'] && $reply['most_num_times'] > 0) {
                $running = false;
                $msg = '您已经超过今天的翻牌次数，明天再来吧!';
            }
        }
        
        $cArr = array('one', 'two', 'three', 'four', 'five');
        foreach ($cArr as $c) {
            if (empty($reply['c_type_' . $c]))
                break;
            $awardstr.='<p>' . $reply['c_type_' . $c] . '：' . $reply['c_name_' . $c];
            if ($reply['show_num'] == 1) {
                $awardstr.='  奖品数量： ' . intval($reply['c_num_' . $c] - $reply['c_draw_' . $c]);
            }
            $awardstr.='</p>';
        }
        
	    if ($reply['most_num_times'] > 0 && $reply['number_times'] > 0) {
            $detail = '本次活动共可以翻' . $reply['number_times'] . '次，每天可以翻 ' . intval($reply['most_num_times']) . ' 次卡! 你共已经翻了 <span id="totalcount">' . intval($fans['totalnum']) . '</span> 次 ，今天翻了<span id="count">' . intval($fans['todaynum']). '</span> 次.';
            $Tcount = $reply['most_num_times'];
            $Lcount = $reply['most_num_times'] - $fans['todaynum'];
        } elseif ($reply['most_num_times'] > 0) {
            $detail = '本次活动每天可以翻 ' . $reply['most_num_times'] . ' 次卡!你共已经翻了 <span id="totalcount">' . intval($fans['totalnum']) . '</span> 次 ，今天翻了<span id="count">' . intval($fans['todaynum']) . '</span> 次.';
            $Tcount = $reply['most_num_times'];
            $Lcount = $reply['most_num_times'] - $fans['todaynum'];
        } elseif ($reply['number_times'] > 0) {
            $detail = '本次活动共可以翻' . $reply['number_times'] . '次卡!你共已经翻了 <span id="totalcount">' . intval($fans['totalnum']) . '</span> 次。';
            $Tcount = $reply['number_times'];
            $Lcount = $reply['number_times'] - $fans['totalnum'];
        } else {
            $detail = '您很幸运，本次活动没有任何限制，您可以随意翻!你共已经翻了 <span id="totalcount">' . intval($fans['totalnum']) . '</span> 次。';
            $Tcount = 10000;
            $Lcount = 10000;
        }
        
        $detail.='<br/>' . htmlspecialchars_decode($reply['description']);
        
        $loclurl=$_W['siteroot'].$this->createMobileUrl('index', array('id' => $id));		
        $sharetitle = empty($reply['share_title']) ? '欢迎参加翻牌抽奖活动' : $reply['share_title'];
        $sharedesc = empty($reply['share_desc']) ? '亲，欢迎参加翻牌抽奖活动，祝您好运哦！！' : str_replace("\r\n"," ", $reply['share_desc']);
        $shareimg = img_url($reply['picture']);
        
        include $this->template('wap_index');
    }

    
    //抽奖
    public function doMobilelottery(){
        global $_W,$_GPC;
        
    	if (empty($_W['fans']['from_user'])) {
			$this->message(array("success"=>2, "msg"=>'非法访问，请重新发送消息进入翻牌抽奖页面。'),"");
		}
        $id = intval($_GPC['id']);
        $id2 = intval($_GPC['id']);
    	if ($id<0) {
            message('抱歉，参数错误！', '', 'error');
        }
     	$reply = pdo_fetch("SELECT * FROM " . tablename($this->tablename) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $id));
        if ($reply == false) {
           $this->message(array("success"=>2, "msg"=>'该活动已经结束。'),"");
        }
          
        //先判断有没有资格领取
        if (empty($_W['fans'])) {
            $this->message('', 'fan数据为空');
        }
        $fansID = $_W['fans']['id'];
        $from_user = $_W['fans']['from_user'];
        //第一步，判断有中奖
        $award = pdo_fetchall("SELECT * FROM " . tablename('iflop_award') . " WHERE weid=" . $_W['weid'] . " and rid = " . $id . " and fansID=" . $fansID . " and from_user='" . $from_user . "' order by id desc");
     	if ($award != false) {//中奖
     		$data = array(
              'success' => 2,
        	   'msg' =>'您已经中过奖了，不能再翻牌抽奖了，把机会留给其他人吧。',
        	);
            $this->message($data);      
        }
            
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->tablefans) . " WHERE rid = " . $id . " and fansID=" . $fansID . " and from_user='" . $from_user . "'");
		//不存在false的情况，如果是false，则表明是非法
        if ($fans == false) {
             $fans = array(
                 'rid' => $id,
                 'fansID' => $fansID,
                 'from_user' => $from_user,
                 'todaynum' => 0,
                 'totalnum' => 0,
          		 'IPaddress' => $_W['clientip'],
                 'createtime' => time(),
             );
             pdo_insert($this->tablefans, $fans);
              
             $fans['id'] = pdo_insertid();
        }
    
        //更新当日次数
        $nowtime = mktime(0, 0, 0);
        if ($fans['last_time'] < $nowtime) {
            $fans['todaynum'] = 0;
        }
        //判断总次数超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
        if ($fans['totalnum'] >= $reply['number_times'] && $reply['number_times'] > 0) {
           $this->message(array("success"=>2, "msg"=>'您超过翻牌抽奖总次数了，不能翻牌抽奖了!'),"");
        }
        //判断当日是否超过限制,一般情况不会到这里的，考虑特殊情况,回复提示文字msg，便于测试
        if ($fans['todaynum'] >= $reply['most_num_times'] && $reply['most_num_times'] > 0) {
             $this->message(array("success"=>2, "msg"=>'您超过当日翻牌抽奖次数了，不能翻牌抽奖了，请明天再来吧!'),"");
        }
        
        $last_time = strtotime( date("Y-m-d",mktime(0,0,0)));
        //当天抽奖次数
        pdo_update('iflop_winner', array('todaynum' => $fans['todaynum'] + 1,'last_time'=>$last_time), array('id' => $fans['id']));
        //总抽奖次数
        pdo_update('iflop_winner', array('totalnum' => $fans['totalnum'] + 1), array('id' => $fans['id']));

        $prize_arr = array(
             "0"=>array('id'=>1,"prize"=>$reply['c_name_one'], "v"=>$reply['c_num_one']),
             "1"=>array('id'=>2,"prize"=>$reply['c_name_two'], "v"=>$reply['c_num_two']),
             "2"=>array('id'=>3,"prize"=>$reply['c_name_three'],"v"=>$reply['c_num_three']),
             "3"=>array('id'=>4,"prize"=>$reply['c_name_four'], "v"=>$reply['c_num_four']),
             "4"=>array('id'=>5,"prize"=> empty($reply['c_name_five'])?"再接再厉":$reply['c_name_five'],"v"=>$reply['c_num_five']),
             "5"=>array('id'=>6,"prize"=> empty($reply['c_name_six'])?"继续努力":$reply['c_name_six'],"v"=>$reply['c_num_six']),
        ) ; 
      
    	foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        $probability =  $reply['c_rate_one'];//中奖概率
        $rate = 1;
    	$probability = $probability * $rate;
        
        $id = $this -> get_rand($arr ); //根据概率获取奖项id
        $yes = $prize_arr[$id-1]['prize'];
        $c_name_five= empty($reply['c_name_five'])?"再接再厉":$reply['c_name_five'] ;
        $c_name_six =empty($reply['c_name_six'])?"继续努力":$reply['c_name_six'];
        if($yes!=$c_name_five && $yes!=$c_name_six){
        	$isRegister = 0;
        	$tel = pdo_fetchcolumn("SELECT tel FROM " . tablename($this->tablefans) . " WHERE rid = " . $id2 . " and fansID=" . $fansID . " and from_user='" . $from_user . "'");
            if (!empty($tel)) {
                $isRegister = 1; 
            }
        	$data = array(
             'yes' => $yes,
             'success' => 1,
        	 'isRegister'=>$isRegister,
        	 'msg' =>'恭喜你中了'.$yes."需要分享给".$reply['share_times'].'个有效好友才能领奖哦！',
        	);
        	
        	$isupdate = pdo_fetchall("SELECT * FROM " . tablename('iflop_award') . " WHERE  rid = " . $id2 . " and fansID=" . $fansID . " and from_user='" . $from_user . "' order by id desc");
        	$insert = array(
                'weid' => $_W['weid'],
                'rid' => $id2,
                'fansID' => $fansID,
                'from_user' => $from_user,
                'name' => $yes,
         		'consumetime' => time(),
                'description' => '您中的奖品为：'.$yes,
                'createtime' => time(),
         		'status' => 1,
            );
        	if ($isupdate == false) {
            	pdo_insert('iflop_award', $insert);
        	}else{
        		pdo_update('iflop_award', $insert, array ('id' => $isupdate['id']) );
        	}
        	
            $this->message($data); 
        }else{
         	unset($prize_arr[$id-1]); //将中奖项从数组中剔除，剩下未中奖项
	        shuffle($prize_arr); //打乱数组顺序
	        for($i=0;$i<count($prize_arr);$i++){
	            $pr[] = $prize_arr[$i]['prize'];
	        }
        	$data = array(
             'msg' => $yes,
        	 'no' => $pr,
             'success' =>2,
        	);
        	$this->message($data); 
        }
        
        $this->message();
    }
    
	//json
    public function message($_data = '', $_msg = '') {
        if (!empty($_data['succes']) && $_data['success'] != 2) {
            $this->setfans();
        }
        if (empty($_data)) {
            $_data = array(
                'name' => "谢谢参与",
                'success' => 0,
            );
        }
        if (!empty($_msg)) {
            $_data['msg'] = $_msg;
        }
        die(json_encode($_data));
    }

    //取得随机产品
    function get_rand($proArr ) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr );
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
    
 	public function setfans() {
        global $_GPC, $_W;
        //增加fans次数
        //记录用户信息
        $id = intval($_GPC['id']);
        $fansID = $_W['fans']['id'];
        if (empty($fansID) || empty($id))
            return;
        $fans = pdo_fetch("SELECT * FROM " . tablename($this->tablefans) . " WHERE rid = " . $id . " and fansID=" . $fansID . "");
        $nowtime = mktime(0, 0, 0);
        if ($fans['last_time'] < $nowtime) {
            $fans['todaynum'] = 0;
        }
        $update = array(
            'todaynum' => $fans['todaynum'] + 1,
            'totalnum' => $fans['totalnum'] + 1,
            'last_time' => time(),
        );
        pdo_update($this->tablefans, $update, array('id' => $fans['id']));
    }
    
	//提交用户手机号码
	public function doMobilesettel() {
        global $_GPC, $_W;
        $id = intval($_GPC['id']);
        $fansID = $_W['fans']['id'];
        $from_user = $_W['fans']['from_user'];
        $fans = pdo_fetch("SELECT id FROM " . tablename($this->tablefans) . " WHERE rid = " . $id . " and fansID=" . $fansID . " and from_user='" . $from_user . "'");
        if ($fans == false) {
            $data = array(
                'success' => 0,
                'msg' => '保存数据错误！',
            );
        } else {
            $temp = pdo_update($this->tablefans, 
            array('tel' => $_GPC['tel']), 
            array('rid' => $id, 'fansID' => $fansID));
            
            if ($temp === false) {
                $data = array(
                    'success' => 0,
                    'msg' => '保存数据错误！',
                );
            } else {
                $temp = pdo_update("fans", array('mobile' => $_GPC['tel']), array('id' => $fansID));
	            /*$pan = pdo_fetch("SELECT * FROM ".tablename('iflop_winner')." WHERE rid = '$id' and fansID = '$fansID' and from_user='$fromuser'  LIMIT 1");
				if(empty($pan)){
					$updateData = array(
						'weid' => $_GPC['weid'],
						'rid' => $_GPC['id'],
						'id' => $_GPC['id'],
					    'fansID' => $pan['id'],
						'from_user' => $fromuser,
					);
					pdo_update('iflop_winner', $updateData);*/
					$data = array( 'success' => 1, 'msg' => '成功提交数据', );
            }
        }
        echo json_encode($data);
    }
    
    
	// 点击分享量统计
	public function doMobilesharelottery(){
		global $_GPC, $_W;
		$IPaddress = $_W['clientip'];
		$id = intval($_GPC['id']);
		$effective= true ;
		
		$this->doCheckedMobile();
		 
		$uid = intval($_GPC['uid']);
		if (!$uid) {
			$effective = false ;
		}
		
		$url=$this->createMobileUrl('index', array('id' => $id,'name' => 'iflop'));
        
		$user = pdo_fetch("SELECT * FROM ".tablename('iflop_winner')." WHERE id = '{$uid}' and rid=".$id." LIMIT 1"); 
		if($user){
			$member = fans_search($user['from_user']);
			if($uid && $effective){
				if($IPaddress != $winner['IPaddress']){
					if(!isset($_COOKIE["shareiflop"])){ 
						setcookie('shareiflop',1,TIMESTAMP+86400);
						$data = array(
							'share_times' => $user['share_times'] +1 ,
						);
						pdo_update('iflop_winner', $data,array('id' => $uid,'rid'=>$id));
						$msg='你已成功为'.$member['nickname'].'点击转发抽奖！';			
					}
				}
			}
		}
		
		message($msg, $url);
		
	}
	
}