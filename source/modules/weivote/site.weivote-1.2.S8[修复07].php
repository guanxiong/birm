<?php
/**
 * WeiVote
 */
defined('IN_IA') or exit('Access Denied');

class WeivoteModuleSite extends WeModuleSite {

    var $data_alert_msg;
    
    //public function doCheckedMobile() {}
    public function doCheckedMobile() {
        global $_GPC, $_W;

        /*
        $strxx = '';
        foreach ($_W as $key => $value) {
            if (is_array($value)) {
                $strxx = $strxx.$key.'=>[1----------------]<br>';
                foreach ($value as $key2 => $value2) {
                    

                    if (is_array($value2)) {
                            $strxx = $strxx.$key2.'=>[2------------]<br>';
                            foreach ($value2 as $key3 => $value3) {
                                if (is_array($value3)) {
                                        $strxx = $strxx.$key3.'=>[3------------]<br>';
                                        foreach ($value3 as $key4 => $value4) {
                                            $strxx = $strxx.$key4.'=>'.$value4.'<br>';
                                        }
                                    } else {
                                        $strxx = $strxx.$key3.'=>'.$value3.'<br>';
                                    }
                            }
                        } else {
                            $strxx = $strxx.$key2.'=>'.$value2.'<br>';
                        }


                }
            } else {
                $strxx = $strxx.$key.'=>'.$value.'<br>';
            }
            
        }
        message($strxx);
        */
        
        //include $this->template('test');exit;
        //message('xxxx','weixin://qr/EUOCYovlXD4l9EiG0W8T');

		//$url = $arrays[$_SERVER['HTTP_HOST']];
		//message($url);

		$servername = $_SERVER['SERVER_NAME'];//取得域名
		if ((strpos($servername, 'ztjuz.com') !== false) || (strpos($servername, 'zttv.cn') !== false)) {} else {
			message($servername.'您未获得授权，请联系作者 QQ 125879930 ');
		}

        $useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			message('非法访问，请通过微信打开！');
		}
        
        if(empty($_W['fans'])) {
			echo "Error Code: 0X00001".";<br> Message: ".$_W['fans']['from_user'] ;
            $this->checkAuth();exit;//关注页面//message('请先关注公众号 '.$_W['account']['account'].' ,重新发送消息参与活动！');
        }
        if($_W['fans']['follow'] == 0) { 
			echo "Error Code: 0X00002".";<br> Message: ".$_W['fans']['from_user'] ;
			$this->checkAuth();exit;//关注页面//message('请先关注公众号 '.$_W['account']['account'].' ,重新发送消息参与活动！');
        }
        if(empty($_W['fans']['from_user']) || $_W['fans']['from_user'] == '') {
			echo "Error Code: 0X00003".";<br> Message: ".$_W['fans']['from_user'] ;
            $this->checkAuth();exit;//关注页面//message('请先关注公众号 '.$_W['account']['account'].' ,重新发送消息参与活动！');
        }


        //message(var_dump($_W));
        //$profile = fans_require($fromuser, array('realname', 'mobile', 'qq'), '需要完善资料后才能参与.');
        
    }
    //public function doCheckedParam() {}
    public function doCheckedParam() {
        global $_GPC, $_W;

        if (empty($_GPC['id'])) {
			message('非法访问，请重新发送消息进入页面！');
		}    
    }

    //public function doCheckedVote($setting) {}
    public function doCheckedVote($setting) {
        global $_GPC, $_W;
        
        $id = intval($_GPC['id']);
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser);
        $mylogcount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE from_user = '{$fromuser}' AND rid = '$id'");
        //获取传参数据和检测
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        $title = $weivote_setting['title'];
        $cover = $_W['attachurl'].$weivote_setting['picture'];
        
        /****条件限制：1活动时间，2是否已经参与***/
        
        //检查活动时间是否有效
        if ($setting['start_time'] > TIMESTAMP ) {
            message('非法访问，本次投票尚未开始!');
        }
        if($setting['end_time'] < TIMESTAMP) {
            $alert_msg = '本次投票时间已结束!';
            $urlName = 'result';
            include $this->template('weivote');
            exit;
        }
        
        //检查当前用户是否已经参与过
        if ($mylogcount >= $setting['max_vote_count']) {
            $alert_msg = '您的投票次数已达活动上限!';
            $urlName = 'result';
            include $this->template('weivote');
            exit;
        }
        
        
        /****实名登记限制：***/
        //如果登记过就直接跳转到投票页面
        if ($setting['name_state'] == 1) {
            if (empty($member['realname']) || empty($member['mobile']) || empty($member['qq'])) {

                //$urlName = 'register';
                //include $this->template('weivote');
				//exit;
				$this->data_alert_msg = 'register';

            }
        }
    }

	public function doCheckedVVote($setting) {
        global $_GPC, $_W;
        
        $id = intval($_GPC['id']);
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser);
        $mylogcount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE from_user = '{$fromuser}' AND rid = '$id'");
        //获取传参数据和检测
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        $title = $weivote_setting['title'];
        $cover = $_W['attachurl'].$weivote_setting['picture'];
        
        
        /****条件限制：1活动时间，2是否已经参与***/
        
        //检查活动时间是否有效
        if ($setting['start_time'] > TIMESTAMP ) {
            return $this->weivoteJson(-1,'非法访问，本次投票尚未开始!','','null');
        }
        if($setting['end_time'] < TIMESTAMP) {
            $this->data_alert_msg = '本次投票时间已结束!';
            return $this->doMobileVresult();
            exit;
        }
        
        /****实名登记限制：***/
        //如果登记过就直接跳转到投票页面
        if ($setting['name_state'] == 1) {
            if (empty($member['realname']) || empty($member['mobile']) || empty($member['qq'])) {

                //$urlName = 'register';
                //include $this->template('weivote');
                //exit;
                $this->data_alert_msg = 'register';

            }
        }
    }

        
    public function doMobileUrl() {
        global $_GPC, $_W;

		$this -> doCheckedMobile();
        $this -> doCheckedParam();

        
        //获取传参数据和检测
		$id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        $fromuser = $_W['fans']['from_user'];
		$member = fans_search($fromuser); 
        if (empty($member)) {
			message('非法访问，请重新发送消息进入页面！');
		}

        //投票条件限制检测
        $this -> doCheckedVote($weivote_setting);


        $title = $weivote_setting['title'];

        
//      $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE createtime > '".strtotime(date('Y-m-d'))."' AND rid = '$id'");
//      $mylog = pdo_fetchall("SELECT options FROM ".tablename('weivote_log')." WHERE from_user = '{$fromuser}' AND rid = '$id' ORDER BY createtime DESC");
//      $sql = "SELECT a.options, b.realname FROM ".tablename('weivote_log')." AS a
//				LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE b.mobile <> '' AND b.realname <> ''  AND a.rid = '$id' ORDER BY a.createtime DESC LIMIT 20";
//		$otheroption = pdo_fetchall($sql);
	
		
        //如果登记过就直接跳转到投票页面
		$alert_msg = $this->data_alert_msg;
        $this->data_alert_msg = '';
		if ($alert_msg == 'register') {
			$alert_msg = '';
			$urlName = 'register';
				
			//选项
			$sql = "SELECT * FROM ".tablename('weivote_option')." WHERE rid = '$id'";
			$weivote_options = pdo_fetchall($sql);
			
			//标题封面总数
			$title = $weivote_setting['title'];
			$cover = $_W['attachurl'].$weivote_setting['picture'];
			$voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");

			include $this->template('weivote');
			exit;
        } else {
            $this->doMobileVote();
            exit;
        }
    }

    public function doMobileVregister() {
        global $_GPC, $_W;
        
        
        $this -> doCheckedMobile();
        $this -> doCheckedParam();
        
        //获取传参数据和检测
        $id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
            message('非法访问，请重新发送消息进入页面！');
        }
        
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser); 
        if (empty($member)) {
            message('非法访问，请重新发送消息进入页面！');
        }

        $isregister = $_GPC['isregister'];
        if ($isregister != 'false') {

            $data = array(
                'realname' => $_GPC['realname'],
                'mobile' => $_GPC['mobile'],
                'qq' => $_GPC['qq'],
            );
            
            if (empty($data['realname'])) {
                return $this->weivoteJson(-1,'请填写您的真实姓名！','','null');
                exit;
            }
            if (empty($data['mobile'])) {
                return $this->weivoteJson(-1,'请填写您的手机号码！','','null');
                exit;
            }
            
            fans_update($_W['fans']['from_user'], $data);
            
            return $this->weivoteJson(1,'','vote','null');
            exit;
        } else {
            return $this->weivoteJson(2,'','','null');
            exit;
        }

    }
    public function doMobileRegister() {
        
        global $_GPC, $_W;
        
        
        $this -> doCheckedMobile();
        $this -> doCheckedParam();
        
        //获取传参数据和检测
		$id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        $fromuser = $_W['fans']['from_user'];
		$member = fans_search($fromuser); 
        if (empty($member)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        //投票条件限制检测
        //$this -> doCheckedVote($weivote_setting);
        
        
		$isregister = $_GPC['isregister'];
        if ($isregister != 'false') {

			$data = array(
				'realname' => $_GPC['realname'],
				'mobile' => $_GPC['mobile'],
				'qq' => $_GPC['qq'],
			);
            
			if (empty($data['realname'])) {
				//die('<script>alert("请填写您的真实姓名！");location.reload();</script>');
                //die('<script>location.href = "'.$this->createMobileUrl('alert', array('alert_msg' => '请填写您的真实姓名！', 'alert_url' => 'register', 'id' => $id)).'";</script>');
                $data_alert_msg = '请填写您的真实姓名！';
                $title = $weivote_setting['title'].'登记信息';
                include $this->template('register');
                exit;
			}
			if (empty($data['mobile'])) {
				//die('<script>alert("请填写您的手机号码！");location.reload();</script>');
                //die('<script>location.href = "'.$this->createMobileUrl('alert', array('alert_msg' => '请填写您的手机号码！', 'alert_url' => 'register', 'id' => $id)).'";</script>');
                $data_alert_msg = '请填写您的手机号码！';
                $title = $weivote_setting['title'].'登记信息';
                include $this->template('register');
                exit;
			}
            
			fans_update($_W['fans']['from_user'], $data);
            
            //echo '我的ID是：'.$id;
			//die('<script>alert("登记成功!");location.href = "'.$this->createMobileUrl('vote', array('id' => $id)).'";</script>');
            //die('<script>location.href = "'.$this->createMobileUrl('alert', array('alert_msg' => '登记成功!', 'alert_url' => 'vote', 'id' => $id)).'";</script>');
            $this->data_alert_msg = '登记成功!';
            $this -> doMobileVote();
            exit;
		} else {
            $title = $weivote_setting['title'].'登记信息';
            include $this->template('register');
        }
        
    }
    
    public function doMobileVote() {
        
        global $_GPC, $_W;
        

        $this -> doCheckedMobile();
        $this -> doCheckedParam();
        
        //获取传参数据和检测
		$id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        $fromuser = $_W['fans']['from_user'];
		$member = fans_search($fromuser); 
        if (empty($member)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        //投票条件限制检测
        $this -> doCheckedVote($weivote_setting);
        

        
        
        $sql = "SELECT * FROM ".tablename('weivote_option')." WHERE rid = '$id'";
		$weivote_options = pdo_fetchall($sql);
        
        $title = $weivote_setting['title'];
        $cover = $_W['attachurl'].$weivote_setting['picture'];
        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        
        $urlName = 'vote';
        include $this->template('weivote');
    }


        
    public function doMobileVoter() {
        
        global $_GPC, $_W;
        
        
        $this -> doCheckedMobile();
        $this -> doCheckedParam();
        
        //获取传参数据和检测
		$id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        $fromuser = $_W['fans']['from_user'];
		$member = fans_search($fromuser); 
        if (empty($member)) {
			message('非法访问，请重新发送消息进入页面！');
		}
        
        //投票条件限制检测
        $this -> doCheckedVote($weivote_setting);
        
        
        $oid = intval($_GPC['oid']);
        $sql = "SELECT * FROM ".tablename('weivote_option')." WHERE id = '$oid'";
		$weivote_option = pdo_fetch($sql);
        
        
		$title = $weivote_setting['title'];
        $cover = $_W['attachurl'].$weivote_setting['picture'];
        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
    
        include $this->template('voter');
    }

    public function doMobileVvoter() {
        global $_GPC, $_W;
        
        
        $this -> doCheckedMobile();
        $this -> doCheckedParam();
        


        //获取传参数据和检测
        $id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
            message('非法访问，请重新发送消息进入页面！');
        }
        
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser); 
        if (empty($member)) {
            message('非法访问，请重新发送消息进入页面！');
        }
        
        //投票条件限制检测
        $this -> doCheckedVote($weivote_setting);

		//登记验证
		$alert_msg = $this->data_alert_msg;
        $this->data_alert_msg = '';
		if ($alert_msg == 'register') {
			return $this->weivoteJson(-1,'请先登记信息','register',$data);
			exit;
        }


        $oid = intval($_GPC['oid']);
        $sql = "SELECT * FROM ".tablename('weivote_option')." WHERE id = '$oid'";
        $weivote_option = pdo_fetch($sql);

        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");

        $data = array('oid' => $oid,
                      'weivote_option' => $weivote_option,
                      'voter_count' => $voter_count);

        return $this->weivoteJson(1,'','voter',$data);
        exit;
    }
    
    public function doMobileVsubmit() {
        global $_GPC, $_W;


        $this -> doCheckedMobile();
        $this -> doCheckedParam();

        //获取传参数据和检测
        $id = intval($_GPC['id']);
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id' LIMIT 1");
        if (empty($weivote_setting)) {
            message('非法访问，请重新发送消息进入页面！');
        }
        
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser); 
        if (empty($member)) {
            message('非法访问，请重新发送消息进入页面！');
        }
       
        //投票条件限制检测
        $this -> doCheckedVVote($weivote_setting);

		//登记验证
		$alert_msg = $this->data_alert_msg;
        $this->data_alert_msg = '';
		if ($alert_msg == 'register') {
			return $this->weivoteJson(-1,'请先登记信息','register',$data);
			exit;
        }

        $today = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $tomorrow = mktime(0,0,0,date("m"),date("d")+1,date("Y"));

        //检查当前用户今天投票是否已达上限//所用参数id,fromuser,today,tomorrow
        $mylogtodaycount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE from_user = '{$fromuser}' AND rid = '$id' AND createtime > ".$today." AND createtime < ".$tomorrow );
        if ($mylogtodaycount >= $weivote_setting['max_vote_day']) {
            $this->data_alert_msg = '您今天投票次数已达上限!';
            return $this->doMobileVresult();
            exit;
        }
        
        $mylogcount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE from_user = '{$fromuser}' AND rid = '$id'");
        //检查当前用户是否已经参与过
        if ($mylogcount >= $weivote_setting['max_vote_count']) {
            $this->data_alert_msg = '您的投票次数已达活动上限!';
            return $this->doMobileVresult();
            exit;
        }
        
        //检查当前用户投票数量
        $oid = $_GPC['options-choose'];
        if (count($oid) < 1 || count($oid) > $weivote_setting['max_vote_day'] || count($oid) > ($weivote_setting['max_vote_count'] - $mylogtodaycount)) {
            return $this->weivoteJson(-1,'投票数量不符合要求，请重新选择!','','null');
            exit;
        }
        
        //检查用户今天投票选项是否已选过
        if ($weivote_setting['type_vote'] == 1) {
            $mylog = pdo_fetchall("SELECT oid FROM ".tablename('weivote_log')." WHERE from_user = '{$fromuser}' AND rid = '$id' AND createtime > ".$today." AND createtime < ".$tomorrow );
            foreach ($mylog as $mylog_one) {
                foreach ($oid as $oid_one) {
                    if($mylog_one['oid'] == $oid_one) {
                        return $this->weivoteJson(-1,'已投过该选项，请投给其他选项!','','null');
                        exit;
                    }
                }
            }
        }

        
        foreach ($oid as $oid_one) {

            $options = pdo_fetch("SELECT title FROM ".tablename('weivote_option')." WHERE id = '$oid_one' LIMIT 1");

            //保存到数据库
            $data = array(
                'rid' => $id,
                'oid' => $oid_one,
                'from_user' => $fromuser,
                'realname' => $member['realname'],
                'qq' => $member['qq'],
                'mobile' => $member['mobile'],
                'state' => 0,
                'createtime' => TIMESTAMP,
                'options' => $options['title'],
                'clientip' => $_W['clientip'],
            );
            pdo_insert('weivote_log', $data);
        }
        

        $this->data_alert_msg = '';
        return $this->doMobileVresult();
        exit;
    }

    public function doMobileTresult() {
        global $_GPC, $_W;

        //获取传参数据和检测
        $id = intval($_GPC['id']);        
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser); 
        if (empty($member)) {
            message('非法访问，请重新发送消息进入页面！');
        }

        //读取统计结果
        $sql = "SELECT id, title, description, picture, state FROM ".tablename('weivote_option')." WHERE rid = '$id'";
        $weivote_options = pdo_fetchall($sql);
        


        
        $options_count = pdo_fetchcolumn("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        
        $options = array();
        
        foreach ($weivote_options as $weivote_option)
        {
            
            $weivote_option_id = $weivote_option['id'];
            $options_one = pdo_fetch("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id' AND oid = '$weivote_option_id'");
            
            $options_obj = array(
                'title' => $weivote_option['title'],
                'total' => $options_one['total'],
                'proportion' => intval(doubleval($options_one['total'])/$options_count*10000)/100,
                'picture' => $weivote_option['picture'],
                'id' => $weivote_option['id'],
            );
            //echo $weivote_option['id'].'<br>';
            //echo $options_count.' - '.$options_one['total'].' - '.$weivote_option_id.' -- '.$options_obj['title'].' -- '.$options_obj['total'].' -- '.$options_obj['proportion'].' -- '.$options_obj['picture'].' -- '.$options_obj['id'].'<br>';
            
            
            array_push($options,$options_obj);
        }

        if (count($options) > 0) {
            $options = $this->doSort($options, 'total');
        }


        $data = array('voter_count' => $voter_count,
                      'options_count' => $options_count,
                      'options' => $options);
        return $this->weivoteJson(1,'','result',$data);
        exit;        
    }

    public function doMobileVresult() {
        global $_GPC, $_W;

        //获取传参数据和检测
        $id = intval($_GPC['id']);        
        $fromuser = $_W['fans']['from_user'];
        $member = fans_search($fromuser); 
        if (empty($member)) {
            message('非法访问，请重新发送消息进入页面！');
        }

        //读取统计结果
        $sql = "SELECT id, title, description, picture, state FROM ".tablename('weivote_option')." WHERE rid = '$id'";
        $weivote_options = pdo_fetchall($sql);
        


        
        $options_count = pdo_fetchcolumn("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        
        $options = array();
        
        foreach ($weivote_options as $weivote_option)
        {
            
            $weivote_option_id = $weivote_option['id'];
            $options_one = pdo_fetch("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id' AND oid = '$weivote_option_id'");
            
            $options_obj = array(
                'title' => $weivote_option['title'],
                'total' => $options_one['total'],
                'proportion' => intval(doubleval($options_one['total'])/$options_count*10000)/100,
                'picture' => $weivote_option['picture'],
                'id' => $weivote_option['id'],
            );
            //echo $weivote_option['id'].'<br>';
            //echo $options_count.' - '.$options_one['total'].' - '.$weivote_option_id.' -- '.$options_obj['title'].' -- '.$options_obj['total'].' -- '.$options_obj['proportion'].' -- '.$options_obj['picture'].' -- '.$options_obj['id'].'<br>';
            
            
            array_push($options,$options_obj);
        }

        if (count($options) > 0) {
            $options = $this->doSort($options, 'total');
        }



        $alert_msg = $this->data_alert_msg;
        $this->data_alert_msg = '';

        $data = array('voter_count' => $voter_count,
                      'options_count' => $options_count,
                      'options' => $options);

        if ($alert_msg != '') {
            return $this->weivoteJson(-1,$alert_msg,'result',$data);
        } else {
            return $this->weivoteJson(0,'投票成功!','result',$data);
        }

        exit;        
    }
    

    public function weivoteJson($resultCode, $resultMsg, $urlName, $data) {
        $jsonArray = array(
            'resultCode' => $resultCode,
            'resultMsg' => $resultMsg,
            'urlName' => $urlName,
            'data' => $data);
        $jsonStr = json_encode($jsonArray);
        return $jsonStr;
    }

	private function checkAuth() {
		global $_W;
		$site = $GLOBALS['site'];
		$account = $GLOBALS['_W']['account'];
		$rid = intval($_GPC['rid']);
		if (!empty($rid)) {
			$keywords = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$rid}'");
		}
		if (!empty($GLOBALS['entry'])) {
			$rule = pdo_fetch("SELECT rid FROM ".tablename('cover_reply')." WHERE module = '{$GLOBALS['entry']['module']}' AND do = '{$GLOBALS['entry']['do']}' AND weid = '{$account['weid']}'");
			$keywords = pdo_fetchall("SELECT content FROM ".tablename('rule_keyword')." WHERE rid = '{$rule['rid']}'");
		}
		include template('auth', TEMPLATE_INCLUDEPATH);
	}

    //冒泡排序
    private function doSort($array, $sortField){  

        $count = count($array);   
        if ($count <= 0) return false;   
        
        for($i=0; $i<$count; $i++){   
            for($j=$count-1; $j>$i; $j--){ 

                if ($array[$j][$sortField] > $array[$j-1][$sortField]){   
                    $tmp = $array[$j];   
                    $array[$j] = $array[$j-1];   
                    $array[$j-1] = $tmp;   
                }
                
            }   
        }

        return $array;   
    } 
  

    public function getDevice() {

        $useragent=$_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
        {
            echo "终端访问~".$useragent;
        }
        else
        {
            echo "其他PC访问!".$useragent;
        }
        
    }
    
}