<?php
/**
 * [WeEngine System] 更多模块请浏览：bbs.we7.cc
 */
defined('IN_IA') or exit('Access Denied');
include 'plugin/phpqrcode.php';

class IcardModuleSite extends WeModuleSite {
    public $tablename = 'icard_reply';

    public $action = 'style';//方法
    public $actions_titles = array(
        'style' => '会员卡设置',
        'business' => '商家设置',
        'score' => '积分策略',
        'level' => '等级设置',
        'privilege' => '会员特权',
        'card' => '会员管理',
        'announce' => '通知管理',
        'gift' => '礼品券管理',
        'coupon' => '优惠券管理',
        'outlet' => '门店系统'
    );

	public function doMobileIndex() {
        global $_W,$_GPC;
        $rid = intval($_GPC['rid']);
        $weid = intval($_GPC['weid']);
        include $this->template('index');
	}

    /*
     *
     *会员卡
     *
     */
    //会员卡首页
    public function doMobileWapIndex(){
        global $_GPC;
        $weid = intval($_GET['weid']);
        $page_from_user = $_GPC['from_user'];
        $do = 'index';
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $issign = $this -> get_today_sign_state($weid, $from_user);

        //会员卡
        $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE weid = :weid and from_user=:from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));
        if(!empty($card)){
            $money = $card['money'];
            $balance_score = $card['balance_score'];
            $coin = $card['coin'];
        }else{
            $money = 0;//消费总额
            $balance_score = 0;//剩余积分
            $coin = 0;//余额
        }
        $total_score = intval($card['total_score']);
        $level = $this -> get_user_level($weid, $total_score);
        if(empty($level))$level['levelname'] = '普通会员';
        //通知数量
        $announceTotal = $this -> get_announce_count($weid, $from_user, $level);
        //礼品券数量
        $giftTotal = $this -> get_gift_count($weid);
        //特权
        $privilegeTotal = $this -> get_privilege_count($weid, $level['id']);

        //优惠券
        $strwhere = $this -> get_coupon_strwhere($weid, $from_user, $level['id']);
        //优惠券数量
        $coupontotal = pdo_fetchcolumn("SELECT count(1) FROM ".tablename('icard_coupon')." WHERE weid = :weid And (".$strwhere.") ", array(':weid' => $weid));

        //会员卡样式
        $style = pdo_fetch("SELECT * FROM ".tablename('icard_style')." WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $weid));
        //商家信息
        $business = pdo_fetch("SELECT * FROM ".tablename('icard_business')." WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $weid));

        //会员积分
        include $this->template('wap_index');
    }

    //会员卡说明
    public function doMobileWapCardInfo(){
        global $_GPC;
        $weid = intval($_GET['weid']);
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $score = pdo_fetch("SELECT * FROM ".tablename('icard_score')." WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $weid));
        $business = pdo_fetch("SELECT * FROM ".tablename('icard_business')." WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $weid));
        include $this->template('wap_cardinfo');
    }

    //签到首页
    public function doMobileWapSign(){
        global $_GPC;
        $do = 'sign';
        $weid = intval($_GET['weid']);
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);
        //用户积分信息
        $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE weid = :weid and from_user=:from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));
        if(!empty($card)){
            $spend_score = $card['spend_score'];
            $balance_score = $card['balance_score'];
            $sign_score = $card['sign_score'];
            $total_score = $card['total_score'];
        }else{
            $spend_score = 0;//消费积分
            $balance_score = 0;//剩余积分
            $coin = 0;//余额
            $sign_score =0;
        }

        //签到信息
        $m = intval($_GPC['m']);//月份
        if($m < 1 || $m > 12){
            $m = date("m", TIMESTAMP);
        }
        else if($m < 10 && $m != 0)
        {
            $m = '0'.$m;
        }

        $year = date("Y", TIMESTAMP);//当前年份2013
        $day = date("d", TIMESTAMP);//当前日子
        $now_time = strtotime($year.'-'.$m.'-'.$day);
        $month = date("m", $now_time);//当前月份
        $daysofmonth = date("t", $now_time);//当月天数
        $arrWeekday = array(0=>'星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
        $issign = $this -> get_today_sign_state($weid, $from_user);
        $data_month = $this -> get_sign_in_month($year, $month, $weid, $from_user);//用户该月签到数据
        $signlist = array();
        $totalscore = 0;//总积分
        for($i = 1; $i <= $daysofmonth; $i++){
            $daytime = strtotime($year.'-'.$month.'-'.$i);
            $score = 0;
            $state = 0;
            foreach($data_month as $key => $value){
                $d = date('d', $value['dateline']);
                if($i == $d){//日期相同的时候
                    $state = 1;
                    $score = $value['score'];
                    if($value > 0){
                        $totalscore += $score;
                    }
                    break;
                }
            }
            //查询该月的签到记录
            $signlist[] = array('day' => date('m月d日', $daytime), 'week' => $arrWeekday[date('w', $daytime)], 'state' => $state, 'score' => $score);
        }
        include $this->template('wap_sign');
    }
    //签到
    public function doMobileWapSetSign(){
        global $_GPC;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user);

        $count = 0;//连续签到次数
        $score = 0;//获得积分
        $state = $this -> get_today_sign_state($weid, $from_user);
        if($state == 0){//未签到
            $obj_score = pdo_fetch("SELECT * FROM ".tablename('icard_score')." WHERE weid = :weid ", array(':weid' => $weid));
            $day_score = $obj_score['day_score'];//每天签到积分
            $dayx_score = $obj_score['dayx_score'];//连续签到积分
            $sign_last = $this -> getLastSign($weid, $from_user);//上一次签到的数据
            if(!empty($sign_last)){
                $count = intval($sign_last['count']);
                $lasttime = intval($sign_last['dateline']);
                $lasttime = strtotime(date('Y-m-d', $lasttime))+86400;//时间变为23.59
                if((TIMESTAMP - $lasttime) > (3600*24)){//时间差大于24小时清零
                    $count = 0;
                }
            }
            if($count == 6)$count = 0;//上一次为连续6天的时候清零
            $count += 1;
            //积分
            $score = $day_score;
            if($dayx_score != 0){//连续签到积分
                if($count == 6){
                    $score = $dayx_score;
                    $count = 0;
                }
            }
            $data_sign = array(
                'weid' => $weid,
                'from_user' => $from_user,
                'score' => $score,
                'count' => $count,
                'dateline' => TIMESTAMP
            );
            $flag = pdo_insert('icard_sign', $data_sign);
            if($flag>0){//增加会员卡积分
                $card = pdo_fetch("SELECT id FROM ".tablename('icard_card')." WHERE weid = :weid and from_user = :from_user order by id desc limit 1 ", array(':weid' => $weid, ':from_user' => $from_user));
                if(!empty($card)){
                    //增加剩余积分、总积分、签到积分
                    pdo_query("UPDATE ".tablename('icard_card')." SET total_score=total_score+:score,balance_score=balance_score+:score,sign_score=sign_score+:score WHERE id=:id", array(':score' => $score, ':id' => $card['id']));
                }
            }
        }else{
            $result['msg'] = '今天你已经签到了!';
        }
        $result['state'] = $state;
        message($result, '', 'ajax');
    }
    //领取会员卡
    public function doMobileWapGetCard(){
        global $_GPC;
        $result['state'] = 0;
        $weid = intval($_GET['weid']);
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user);
        if(($this -> ishave_card($weid, $from_user))){
            $result['msg'] = '该用户已经领取会员卡.';
            message($result, '', 'ajax');
        }

        //注册用户
        $data_user = array(
            'weid' => $weid,
            'from_user' => $from_user,
            'username' => $_GPC['username'],
            'tel' => $_GPC['tel'],
            'birthday' => $_GPC['birthday'],
            'age' => $_GPC['age'],
            'sex' => $_GPC['sex'],
            'address' => $_GPC['address'],
            'updatetime' => TIMESTAMP,
            'dateline' => TIMESTAMP
        );
        if(empty($data_user['username'])){
            $result['msg'] = '请输入用户名.';
            message($result, '', 'ajax');
        }
        if(empty($data_user['tel'])){
            $result['msg'] = '请输入手机号码.';
            message($result, '', 'ajax');
        }
        $flag = pdo_insert('icard_user',$data_user);
        if($flag == 0){
            $result['msg'] = '注册用户失败.';
            message($result, '', 'ajax');
        }
        $card = pdo_fetch("select cardpre from ".tablename('icard_style')." where weid =".$weid." order by id desc limit 1");
        //注册会员卡
        $data_card = array(
            'weid' => $weid,
            'from_user' => $from_user,
            'cardpre' => trim($card['cardpre']),
            'cardno' => $this->get_card_number($weid),
            'coin' => 0,
            'balance_score' => 0,
            'total_score' => 0,
            'spend_score' => 0,
            'sign_score' => 0,
            'money' => 0,
            'state' => 0,
            'updatetime' => TIMESTAMP,
            'dateline' => TIMESTAMP
        );
        $flag = pdo_insert('icard_card',$data_card);
        if($flag > 0){
            $result['state'] = 1;
            $result['msg'] = '成功领取会员卡.';
            message($result, '', 'ajax');
        }
    }
    //个人资料
    public function doMobileWapUserinfo(){
        global $_GPC;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user);

        $user = pdo_fetch("SELECT * FROM ".tablename('icard_user')." WHERE weid = :weid and from_user=:from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));

        include $this->template('wap_userinfo');
    }

    //更新用户资料
    public function doMobileUpdateUserinfo(){
        global $_GPC;
        $result['state'] = 0;
        $weid = intval($_GET['weid']);
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user);
        $data = array(
            'username' => $_GPC['username'],
            'tel' => $_GPC['tel'],
            'birthday' => strtotime($_GPC['birthday']),
            'address' => $_GPC['address'],
            'sex' => intval($_GPC['sex']),
            'age' => intval($_GPC['age']),
            'remark' => $_GPC['remark'],
            'updatetime' => TIMESTAMP,
        );
        $flag = pdo_update('icard_user', $data, array('weid' => $weid, 'from_user' => $from_user));
        if($flag > 0){
            $result['state'] = 1;
            $result['msg'] = '操作成功.';
            message($result, '', 'ajax');
        }
    }

    //最新通知
    public function doMobileWapAnnounce(){
        global $_GPC;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);

        $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE weid = :weid and  from_user=:from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));
        $level = pdo_fetch("SELECT id FROM ".tablename('icard_level')." WHERE weid = :weid and :totalscore>=min and :totalscore<=max ORDER BY `min` limit 1", array(':weid' => $weid, ':totalscore' => $card['total_score']));
        if(empty($level)){
            $level['id'] = 0;
        }
        //全部会员通知//levelid=0//所属等级通知//levelid=$level//type=1 and from_user=$from_user //用户消费通知
        $announces = pdo_fetchall("SELECT * FROM ".tablename('icard_announce')." WHERE weid = :weid AND (((levelid = 0  OR levelid = :level) AND type = 0) OR (type IN(2,3,4) AND from_user=:from_user)) ORDER BY id DESC limit 50", array(':weid' => $weid, ':from_user' => $from_user, ':level' => $level['id']));
        include $this->template('wap_announce');
    }

    //会员礼品卡
    public function doMobileWapGift(){
        global $_GPC,$_W;
        $do = 'gift';
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);
        $type = 4;
        //会员卡
        $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE weid = :weid and  from_user=:from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));
        //礼品列表
        $gifts = pdo_fetchall("SELECT * FROM ".tablename('icard_gift')." WHERE weid = :weid and :time<endtime ORDER BY id DESC", array(':weid' => $weid, ':time' => TIMESTAMP));
        //礼品券使用次数
        $giftcount_arr = $this->get_announce_usetimes($weid, $from_user, 4);

        include $this->template('wap_gift');
    }

    public function doMobileWapCoupon(){
        global $_GPC,$_W;
        $do = 'coupon';
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);
        $type = 2;

        //物品使用次数
        $giftcount_arr = $this -> get_announce_usetimes($weid, $from_user, 2);
        //会员卡
        $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE weid = :weid and  from_user=:from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));
        $total_score = intval($card['total_score']);
        $level = $this -> get_user_level($weid, $total_score);
        $strwhere = $this -> get_coupon_strwhere($weid, $from_user, $level['id']);
        $coupons = pdo_fetchall("SELECT * FROM ".tablename('icard_coupon')." WHERE weid = :weid And (".$strwhere.") ORDER BY displayorder DESC,id DESC limit 50", array(':weid' => $weid));
        //$coupons = istripslashes($coupons);
        include $this->template('wap_coupon');
    }

    public function doMobileWapMakeSncode(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);
        //用户会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))$this -> showMessage('会员卡不存在.');
        if($card['state']==1)
            message('您的会员卡已被冻结,请联系商户.', create_url('index/module', array('do' => 'wapindex', 'from_user' => $page_from_user, 'name' => 'icard', 'weid' => $weid)), 'error');

        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid AND is_show=1 ORDER BY displayorder DESC,id DESC", array(':weid' => $weid));

        $type = intval($_GPC['type']);;//类型id
        $titles = array('2' => '优惠券消费', '3' => '特权消费', '4' => '礼品卡消费');
        $title = $titles[$type];
        $typeArr = array(2,3,4);

        $id = intval($_GPC['id']);//商品id
        //类型id是否存在
        if(!in_array($type, $typeArr, true)){
            message('非法参数');
        }

        if($type == 4){
            $gift = $this -> get_gift($id, $weid);
            if(empty($gift))message('sorry,找不到相关数据.');

            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 4);
                if($usetimes[$id] >= $gift['count']){
                    $this -> showMessage('兑换次数已经用完.', 0, false);
                }
            }
        }else if($type == 3){
            $gift = $this -> get_privilege($id, $weid);
            if(empty($gift))message('sorry,找不到相关数据.');
            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 3);
                if($usetimes[$id] >= $gift['count']){
                    $this -> showMessage('使用次数已经用完.', 0, false);
                }
            }
        }else if($type == 2){
            $gift = $this -> get_coupon($id, $weid);
            if(empty($gift))message('sorry,找不到相关数据.');
            if($gift['count'] > 0){//判断使用次数
                //$usetimes = pdo_fetchcolumn( "SELECT count(1) FROM ".tablename('icard_announce')." WHERE giftid = :giftid and weid = :weid and from_user = :from_user", array(':giftid' => $id, ':weid' => $weid, ':from_user' => $from_user));
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 2);
                if($usetimes[$id] >= $gift['count']){
                    $this -> showMessage('使用次数已经用完.', 0, false);
                }
            }else{
                $this -> showMessage('没有优惠券了.', 0, false);
            }
        }
        //查询商品是否已经存在兑换码
        $sncode_data = $this -> ishave_sncode($id, $type, $from_user);

        if(!empty($sncode_data)){
            $sncode = $sncode_data['sncode'];
            $snid = $sncode_data['id'];
        }else{
            //生成兑换码
            $sncode = 'A00'.random(11,1);
            $sncode = $this -> get_newsncode($weid, $sncode);
            //添加兑换码
            $data = array(
                'pid' => $id,
                'type' => $type,
                'weid' => $weid,
                'from_user' => $from_user,
                'sncode' => $sncode
            );
            $snid = $this -> add_sncode($data);
        }
        include $this->template('wap_makesncode');
    }

    public function doMobileWapMakeSncodeAdmin(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);
        //用户会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))$this -> showMessage('会员卡不存在.');
        if($card['state']==1)
            message('您的会员卡已被冻结,请联系商户.', create_url('index/module', array('do' => 'wapindex', 'from_user' => $page_from_user, 'name' => 'icard', 'weid' => $weid)), 'error');

        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid AND is_show=1 ORDER BY displayorder DESC,id DESC", array(':weid' => $weid));
        $type = intval($_GPC['type']);;//类型id
        $titles = array('2' => '优惠券消费', '3' => '特权消费', '4' => '礼品卡消费');
        $title = $titles[$type];
        $typeArr = array(2,3,4);

        $id = intval($_GPC['id']);//商品id
        //类型id是否存在
        if(!in_array($type, $typeArr, true)){
            message('非法参数');
        }
        if($type == 4){
            $gift = $this -> get_gift($id, $weid);
            if(empty($gift))message('sorry,找不到相关数据.');

            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 4);
                if($usetimes[$id] >= $gift['count']){
                    $this -> showMessage('兑换次数已经用完.', 0, false);
                }
            }
        }else if($type == 3){
            $gift = $this -> get_privilege($id, $weid);
            if(empty($gift))message('sorry,找不到相关数据.');
            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 3);
                if($usetimes[$id] >= $gift['count']){
                    $this -> showMessage('使用次数已经用完.', 0, false);
                }
            }
        }else if($type == 2){
            $gift = $this -> get_coupon($id, $weid);
            if(empty($gift))message('sorry,找不到相关数据.');
            if($gift['count'] > 0){//判断使用次数
                //$usetimes = pdo_fetchcolumn( "SELECT count(1) FROM ".tablename('icard_announce')." WHERE giftid = :giftid and weid = :weid and from_user = :from_user", array(':giftid' => $id, ':weid' => $weid, ':from_user' => $from_user));
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 2);
                if($usetimes[$id] >= $gift['count']){
                    $this -> showMessage('使用次数已经用完.', 0, false);
                }
            } else {
                $this -> showMessage('没有优惠券了.', 0, false);
            }
        }
        //查询商品是否已经存在兑换码
        $sncode_data = $this -> ishave_sncode($id, $type, $from_user);

        if(!empty($sncode_data)){
            $sncode = $sncode_data['sncode'];
            $snid = $sncode_data['id'];
        }else{
            //生成兑换码
            $sncode = 'A00'.random(11,1);
            $sncode = $this -> get_newsncode($weid, $sncode);
            //添加兑换码
            $data = array(
                'pid' => $id,
                'type' => $type,
                'weid' => $weid,
                'from_user' => $from_user,
                'sncode' => $sncode
            );
            $snid = $this -> add_sncode($data);
        }
        include $this->template('wap_makesncode_admin');
    }

    public function ishave_sncode($id, $type, $from_user){
        $sncode = pdo_fetch("SELECT * FROM ".tablename('icard_sncode')." WHERE pid = :pid AND type = :type AND state !=1 AND from_user=:from_user ORDER BY `id` DESC limit 1", array(':pid' => $id, ':type' => $type, ':from_user' => $from_user));
        return $sncode;
    }

    public function get_newsncode($weid, $sncode){
        $sn = pdo_fetch("SELECT sncode FROM ".tablename('icard_sncode')." WHERE weid = :weid and sncode = :sn ORDER BY `id` DESC limit 1", array(':weid' => $weid, ':sn' => $sncode));
        if(!empty($sn)){
            $sncode = 'A00'.random(11,1);
            $this -> get_newsncode($weid, $sncode);
        }
        return $sncode;
    }

    //使用兑换码
    public function doMobileUseSncode(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user);
        $snid = intval($_GPC['snid']);
        $pid = intval($_GPC['pid']);//产品id
        $type = intval($_GPC['type']);//类型id
        $pwd = $_GPC['pwd'];//输入确认密码
        $outletid = intval($_GPC['storeid']);//门店id
        //用户会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))$this -> showMessage('会员卡不存在.');
        if($card['state']==1)$this -> showMessage('您的会员卡已被冻结,请联系商户.');
        $money = intval($_GPC['money']);
        $payment = intval($_GPC['payment']);//0:现金消费 1:会员卡余额消费
        if($type != 4){//不是礼品券的时候
            if($money == 0){
                $this -> showMessage('请输入消费金额.');
            }
            if($payment == 1){//余额消费
                if($money > intval($card['coin'])){
                    $this -> showMessage('会员卡余额不足,请使用其它支付方式.');
                }
            }
        }

        //检查密码
        if(empty($pwd)){
            $this -> showMessage('请输入消费密码.');
        }
        if($outletid == 0){
            $flag = $this -> check_card_password($weid, $pwd);
            if(empty($flag))$this -> showMessage('商家确认消费密码输入错误，请到会员卡中心设置此密码.');
        }else{
            $flag = $this -> check_outlet_password($weid, $pwd, $outletid);
            if(empty($flag))$this -> showMessage('门店确认消费密码输入错误，请到会员卡中心设置此密码.');
        }
        //check_outlet_password

        //剩余积分
        $balance_score = intval($card['balance_score']);
        //兑换物品所需积分
        $need_score = 0;
        //检查积分
        if($type == 4){//礼品券兑换 //兑换码对应的类型 0:普通通知  2:优惠券 3:会员卡特权 4:礼品券
            $gift = $this -> get_gift($pid, $weid);
            if(empty($gift))$this -> showMessage('礼品券不存在.');
            if($gift['count'] > 0){//判断使用次数
                //$usetimes = pdo_fetchcolumn( "SELECT count(1) FROM ".tablename('icard_announce')." WHERE giftid = :giftid and weid = :weid and from_user = :from_user", array(':giftid' => $pid, ':weid' => $weid, ':from_user' => $from_user));
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 4);
                if($usetimes[$pid] >= $gift['count']){
                    $this -> showMessage('兑换次数已经用完.');
                }
            }
            $need_score = intval($gift['needscore']);
            if($need_score > $balance_score){
                $this -> showMessage('积分不足,不能使用.');
            }
        }else if($type == 3){
            $gift = $this -> get_privilege($pid, $weid);
            if(empty($gift))$this -> showMessage('数据不存在.');
            if($gift['count'] > 0){//判断使用次数
                //$times = pdo_fetchcolumn( "SELECT count(1) FROM ".tablename('icard_announce')." WHERE giftid = :giftid and weid = :weid and from_user = :from_user", array(':giftid' => $pid, ':weid' => $weid, ':from_user' => $from_user));
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 3);
                if($usetimes[$pid] >= $gift['count']){
                    $this -> showMessage('使用次数已经用完.');
                }
            }
        }else if($type == 2){
            $gift = $this -> get_coupon($pid, $weid);
            if(empty($gift))$this -> showMessage('数据不存在.');
            if($gift['count'] > 0){//判断使用次数
                //$times = pdo_fetchcolumn( "SELECT count(1) FROM ".tablename('icard_announce')." WHERE giftid = :giftid and weid = :weid and from_user = :from_user", array(':giftid' => $pid, ':weid' => $weid, ':from_user' => $from_user));
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 2);
                if($usetimes[$pid] >= $gift['count']){
                    $this -> showMessage('使用次数已经用完.');
                }
            }
        }

        $data_sncode = array(
            'snid' => $snid,
            'pid' => $pid,
            'type' => $type,
            'weid' => $weid,
            'outletid' => $outletid,
            'from_user' => $from_user,
            'money' => $money
        );
        //更新兑换码状态
        $flag = $this -> update_sncodestate($data_sncode, 1);
        if($flag > 0){
            $data_announce = array(
                'weid' => $weid,
                'giftid' => $gift['id'],
                'from_user' => $from_user,
                'type' => $type,
                'title' => $gift['title'],
            );
            if($type == 4){//礼品券兑换
                //减去积分
                $this -> update_balancescore($weid, $from_user, $need_score);
                //添加通知
                $data_announce['content'] = "您好，您的会员卡于".date('Y-m-d H:i:s',TIMESTAMP)."兑换礼品卡\"".$gift['title']."\"一次,本次兑换消费".$need_score."积分。";
                $this -> add_announce($data_announce);
            } else if($type == 3 || $type == 2){
                //积分策略
                $obj_score = pdo_fetch("SELECT * FROM ".tablename('icard_score')." WHERE weid = :weid ", array(':weid' => $weid));
                $spend_score = $obj_score['payx_score'];
                //本次消费积分
                $totalspendscore = 0;
                if($spend_score != 0){
                    $totalspendscore = $money * $spend_score;
                }
                $paymentstr = '';
                if($payment == 1){//余额消费
                    $paymentstr = '余额消费';
                    //剩余积分+、消费积分+、总积分+、消费总额+、余额-
                    pdo_query("UPDATE ".tablename('icard_card')." SET total_score=total_score+:score,balance_score=balance_score+:score,spend_score=spend_score+:score,money=money+:money,coin=coin-:money WHERE id=:id", array(':score' => $totalspendscore, ':id' => $card['id'], ':money' => $money));
                }else{//现金消费
                    $paymentstr = '现金消费';
                    //剩余积分+、消费积分+、总积分+、消费总额+、
                    pdo_query("UPDATE ".tablename('icard_card')." SET total_score=total_score+:score,balance_score=balance_score+:score,spend_score=spend_score+:score,money=money+:money WHERE id=:id", array(':score' => $totalspendscore, ':id' => $card['id'], ':money' => $money));
                }

                //消费金额记录
                $data_money = array(
                    'weid' => $weid,
                    'from_user' => $from_user,
                    'giftid' => $gift['id'],
                    'type' => $type,
                    'payment' => $payment,
                    'outletid' => $outletid,
                    'money' => $money,
                    'score' => $totalspendscore,
                    'dateline' => TIMESTAMP
                );
                pdo_insert('icard_money_log', $data_money);

                $announce_tmp = $type==3?"会员卡特权":"优惠券特权";
                $data_announce['content'] = "您好，您的会员卡于".date('Y-m-d H:i:s',TIMESTAMP)."使用".$announce_tmp."\"".$gift['title']."\"一次,本次消费使用".$paymentstr.",金额为".$money."元,获得".$totalspendscore."个积分。";

                $data_announce['money'] = $money;
                $this -> add_announce($data_announce);
            }
            $this -> showMessage($data_announce['content'], 1);
        }else{
            $this -> showMessage('兑换失败');
        }
    }

    //门店
    public function doMobileWapStore(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $stores = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid and is_show=1 ORDER BY displayorder DESC,id DESC limit 50", array(':weid' => $weid));
        include $this->template('wap_store');
    }

    //会员特权
    public function doMobileWapPrivilege(){
        global $_GPC,$_W;
        $do = 'privilege';
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);
        $type = 3;

        //会员卡
        $card = $this -> get_card($weid, $from_user);
        $total_score = intval($card['total_score']);
        $level = $this -> get_user_level($weid, $total_score);
        //礼品列表
        $privileges = pdo_fetchall("SELECT * FROM ".tablename('icard_privilege')." WHERE weid = :weid and :time<endtime and (FIND_IN_SET(:levelid,levelids) Or FIND_IN_SET(0,levelids)) ORDER BY displayorder DESC,id DESC", array(':weid' => $weid, ':time' => TIMESTAMP, ':levelid' => $level['id']));


//        return pdo_fetchcolumn( "SELECT count(1) FROM ".tablename('icard_privilege')." WHERE weid = :weid and :time<endtime And (FIND_IN_SET(:levelid,levelids) Or FIND_IN_SET(0,levelids)) ORDER BY id DESC", array(':weid' => $weid, ':time' => TIMESTAMP, ':levelid' => $levelid));

        //物品使用次数
        $giftcount_arr = $this -> get_announce_usetimes($weid, $from_user, 3);

        include $this->template('wap_privilege');
    }

    public function doMobileWapShoppingLog(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);

        $gifts_arr = $this -> get_gifts_arr_front(4, $weid);
        $privilege_arr = $this -> get_gifts_arr_front(3, $weid);
        $coupon_arr = $this -> get_gifts_arr_front(2, $weid);

        //会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))message('您的会员卡已被冻结,请联系商户.', create_url('index/module', array('do' => 'wapindex', 'from_user' => $page_from_user, 'name' => 'icard', 'weid' => $weid)), 'error');

        $m = intval($_GPC['m']);//月份
        if($m < 1 || $m > 12){
            $m = date("m", TIMESTAMP);
        }
        else if($m < 10 && $m != 0)
        {
            $m = '0'.$m;
        }

        $y = date("Y", TIMESTAMP);//当前年份2013
        //消费记录
        $sql_child = "select * from ".tablename('icard_money_log')." where weid =:weid AND from_user=:from_user AND date_format(FROM_UNIXTIME(dateline), '%Y-%m')='{$y}-{$m}'";
        $sql = "SELECT giftid,sum(money) as totalmoney,sum(score) as totalscore,date_format(FROM_UNIXTIME(dateline), '%Y-%m-%d') as date FROM (".$sql_child.") a GROUP BY date, from_user,weid";
        $list = pdo_fetchall($sql, array(':weid' => $weid, ':from_user' => $from_user));
        $datalist = pdo_fetchall("SELECT * FROM ".tablename('icard_money_log')." WHERE weid = :weid AND from_user=:from_user ORDER BY id DESC", array(':weid' => $weid, ':from_user' => $from_user));

        $date_arr = array();
        foreach($list as $key => $value){
            $date_arr[] = $value['date'];
        }
        //message($str);

        $data_arr = array();
        foreach($list as $key => $value){
            foreach($datalist as $key2 => $value2){
                $logdate = date('Y-m-d', $value2['dateline']);
                if($value['date'] == $logdate){
                    $data_arr[$logdate][] = array('giftid' => $value2['giftid'], 'type' => $value2['type'], 'score' => $value2['score'], 'money' => $value2['money']);
                }
            }
        }

        include $this->template('wap_shopping_log');
    }

    //商业积分
    public function doMobileWapRecharge(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);

        //用户会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))$this -> showMessage('会员卡不存在.');
        if($card['state']==1)
            message('您的会员卡已被冻结,请联系商户.', create_url('index/module', array('do' => 'wapindex', 'from_user' => $page_from_user, 'name' => 'icard', 'weid' => $weid)), 'error');

        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid AND is_show=1 ORDER BY displayorder DESC,id DESC", array(':weid' => $weid));

        include $this->template('wap_recharge');
    }
    public function doMobileRecharge(){
        global $_GPC;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, true);

        //用户会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))$this -> showMessage('会员卡不存在.');
        if($card['state']==1) showMessage('您的会员卡已被冻结,请联系商户.');
        $outletid = intval($_GPC['storeid']);//门店id
        $pwd = $_GPC['pwd'];//登录密码
        $pwdrecharge = $_GPC['pwdrecharge'];//充值密码
        $money = intval($_GPC['money']);
        $payment = intval($_GPC['payment']);//0:积分 1:金额
        if(empty($pwd))showMessage('请输入商家登录密码.');
        if(empty($pwdrecharge))showMessage('请输入商家充值密码.');
        if($money <= 0){
            if($payment == 2)
                $this -> showMessage('请输入充值积分.');
            else
                $this -> showMessage('请输入充值金额.');
        }else if($money > 1000){
            $this -> showMessage('每次充值不能大于1000.');
        }
        if($outletid == 0){
            $this -> showMessage('请选择门店.');
        }else{
            //门店
            $outlet = pdo_fetch("select * from ".tablename('icard_outlet')." where weid =:weid And id = :id", array(':weid' => $weid, ':id' => $outletid));
            if(empty($outlet))showMessage('没有相关门店.');
            //密码
            if($outlet['password'] != $pwd){
                $this -> showMessage('登录密码错误.');
            }
            if($outlet['recharging_password'] != $pwdrecharge){
                $this -> showMessage('充值密码错误.');
            }
            //充值积分或金额
            if($payment == 2){
                $rowcount = pdo_query("UPDATE ".tablename('icard_card')." SET balance_score = balance_score+:score,total_score = total_score+:score WHERE id=:id", array(':score' => $money, ':id' => $card["id"]));
            }else{
                $rowcount = pdo_query("UPDATE ".tablename('icard_card')." SET coin = coin+:price WHERE id=:id", array(':price' => $money, ':id' => $card["id"]));
            }
            if($rowcount > 0){
                $result['state'] = 1;
                //日志
                $this -> addCardLog($payment, $money, $outletid, $card["id"]);
                $this -> showMessage('操作成功.',1);
            }else{
                $this -> showMessage('操作失败.');
            }
        }
    }

    //商业积分
    public function doMobileWapRechargeAdmin(){
        global $_GPC,$_W;
        $weid = intval($_GET['weid']);
        $result['state'] = 0;
        $page_from_user = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $this -> ishave_user($weid, $from_user, false);

        //用户会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))$this -> showMessage('会员卡不存在.');
        if($card['state']==1)
            message('您的会员卡已被冻结,请联系商户.', create_url('index/module', array('do' => 'wapindex', 'from_user' => $page_from_user, 'name' => 'icard', 'weid' => $weid)), 'error');

        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid AND is_show=1 ORDER BY displayorder DESC,id DESC", array(':weid' => $weid));

        include $this->template('wap_recharge_admin');
    }

    //今天签到状态
    public function get_today_sign_state($weid, $from_user){
        $date = date('Y-m-d');
        $sign = pdo_fetch("SELECT * FROM ".tablename('icard_sign')." WHERE weid = :weid and from_user = :from_user and  date_format(FROM_UNIXTIME(dateline), '%Y-%m-%d') = :date ", array(':weid' => $weid, ':from_user' => $from_user, ':date' => $date));
        if(!empty($sign)){
            return 1;
        }else{
            return 0;
        }
    }
    //根据月份取得数据
    public function get_sign_in_month($y, $m, $weid, $from_user){
        $data = pdo_fetchall("SELECT * FROM ".tablename('icard_sign')." WHERE from_user = '{$from_user}' and weid = {$weid} and date_format(FROM_UNIXTIME(dateline),'%Y-%m') = '{$y}-{$m}'");
        return $data;
    }

    public function get_user_level($weid, $total_score){
        $sql = "SELECT id,levelname FROM ".tablename('icard_level')." WHERE weid = :weid and :totalscore>=min and :totalscore<=max ORDER BY `min` limit 1";
        return pdo_fetch($sql, array(':weid' => $weid, ':totalscore' => $total_score));
    }

    public function get_announce_count($weid, $from_user, $level){
        $sql = "SELECT COUNT(*) FROM ".tablename('icard_announce')." WHERE weid = :weid and (levelid=0 or levelid = :level or (levelid=-1 and from_user=:from_user)) ORDER BY id DESC limit 50";
        return pdo_fetchcolumn($sql, array(':weid' => $weid, ':from_user' => $from_user, ':level' => $level['id']));
    }

    public function get_gift_count($weid){
        $sql = "SELECT count(1) FROM ".tablename('icard_gift')." WHERE weid = :weid and :time<endtime ORDER BY id DESC";
        return pdo_fetchcolumn($sql, array(':weid' => $weid, ':time' => TIMESTAMP));
    }

    public function get_privilege_count($weid, $levelid){
        $sql = "SELECT count(1) FROM ".tablename('icard_privilege')." WHERE weid = :weid and :time<endtime And (FIND_IN_SET(:levelid,levelids) Or FIND_IN_SET(0,levelids)) ORDER BY id DESC";
        return pdo_fetchcolumn($sql, array(':weid' => $weid, ':time' => TIMESTAMP, ':levelid' => $levelid));
    }

    //最后签到数据
    public function getLastSign($weid, $from_user){
        $sign = pdo_fetch("SELECT * FROM ".tablename('icard_sign')." WHERE weid = :weid and from_user = :from_user order by id desc limit 1 ", array(':weid' => $weid, ':from_user' => $from_user));
        return $sign;
    }

    //是否存在用户
    public function ishave_user($weid, $from_user, $isajax = true){
        $result['state'] = 0;
        $msg = '没有相关用户';
        if(empty($from_user) || $weid == 0){
            $result['msg'] = $msg;
            if($isajax){
                message($result, '', 'ajax');
            }else{
                message($msg, '', 'error');
            }
        }else{
            $user = pdo_fetch("SELECT * FROM ".tablename('fans')." WHERE from_user = '".$from_user."' and weid = ".$weid." limit 1" );
            if(empty($user)){
                $result['msg'] =  $msg;
                if($isajax){
                    message($result, '', 'ajax');
                }else{
                    message($msg, '', 'error');
                }
            }
        }
    }

    //是否领取会员卡
    public function ishave_card($weid, $from_user){
        $result['state'] = 0;
        $msg = '没有相关用户';
        if(empty($from_user) || $weid == 0){
            $result['msg'] = $msg;
            message($result, '', 'ajax');
        }else{
            $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE from_user = '".$from_user."' and weid = ".$weid." limit 1" );
            if(empty($card)){
                return false;
            }else{
                return true;
            }
        }
    }

    //会员卡号码
    public function get_card_number($weid){
        //当前会员卡
        $card = pdo_fetch("select cardstart from ".tablename('icard_style')." where weid =".$weid." order by id desc limit 1");
        if(!empty($card)){
            $cardstart = intval($card['cardstart']);
        }
        //查询公众号会员卡目前最大卡号
        $user_card = pdo_fetch("select cardno from ".tablename('icard_card')." where weid =".$weid." order by id desc limit 1");
        if(!empty($user_card)){
            return intval($user_card['cardno'])+1;
        } else {
            if(empty($cardstart)){
                return 1000001;
            }else{
                return $cardstart;
            }
        }
    }

    //取得礼品券使用次数
    public function get_announce_usetimes($weid, $from_user, $type){
        //取得兑换礼品兑换次数
        $arr = array();
        $announces = pdo_fetchall("SELECT COUNT(1) as count,giftid FROM ".tablename('icard_announce')." GROUP BY from_user,type,giftid,weid having weid = :weid AND from_user=:from_user AND type=:type ", array(':weid' => $weid, ':from_user' => $from_user, ':type' => $type));

        foreach($announces as $key => $value){
            $arr[$value['giftid']] = $value['count'];
        }
        return $arr;
    }

    //添加通知
    public function add_announce($announce = array()){
        $data = array();
        $data['weid'] = $announce['weid'];
        $data['giftid'] = $announce['giftid'];
        $data['from_user'] = $announce['from_user'];
        $data['type'] = $announce['type'];
        $data['title'] = $announce['title'];
        $data['content'] = $announce['content'];
        $data['levelid'] = -1;
        $data['displayorder'] = 0;
        $data['updatetime'] = TIMESTAMP;
        $data['dateline'] = TIMESTAMP;
        pdo_insert('icard_announce', $data);
    }

    public function update_balancescore($weid, $from_user, $need_score){
        pdo_query("UPDATE ".tablename('icard_card')." SET balance_score = balance_score-:needscore WHERE weid = :weid AND from_user = :from_user ", array(':needscore' => $need_score, ':weid' => $weid, ':from_user' => $from_user));
    }

    public function get_gift($id, $weid){
        return pdo_fetch("SELECT * FROM ".tablename('icard_gift')." WHERE weid = :weid and id = :id ORDER BY `id` DESC limit 1", array(':weid' => $weid ,':id' => $id));
    }

    public function get_privilege($id, $weid){
        return pdo_fetch("SELECT * FROM ".tablename('icard_privilege')." WHERE weid = :weid and id = :id ORDER BY `id` DESC limit 1", array(':weid' => $weid ,':id' => $id));
    }

    public function get_coupon($id, $weid){
        return pdo_fetch("SELECT * FROM ".tablename('icard_coupon')." WHERE weid = :weid and id = :id ORDER BY `id` DESC limit 1", array(':weid' => $weid ,':id' => $id));
    }

    public function get_card($weid, $from_user){
        return pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE weid = :weid and from_user = :from_user ORDER BY `id` DESC limit 1", array(':weid' => $weid ,':from_user' => $from_user));
    }

    //更新sncode状态
    public function update_sncodestate($sncode = array(), $state){
        $data = array(
            'state' => $state,
            'usetime' => TIMESTAMP,
            'outletid' => $sncode['outletid'],
            'money' => $sncode['money'],
        );
        $where = array(
            'id' => $sncode['snid'],
            'pid' => $sncode['pid'],
            'type' => $sncode['type'],
            'weid' => $sncode['weid'],
            'from_user' => $sncode['from_user']
        );

        $sncode = pdo_fetch("SELECT * FROM ".tablename('icard_sncode')." WHERE id = :snid AND weid = :weid and from_user = :from_user ORDER BY `id` DESC limit 1", array(':snid' => $sncode['snid'] , ':weid' => $sncode['weid'] ,':from_user' => $sncode['from_user']));

        if(empty($sncode)){
            $this -> showMessage('兑换码不存在.');
        }else if($sncode['state'] == 1){
            $this -> showMessage('兑换码已经兑换过.');
        }

        return pdo_update('icard_sncode', $data, $where);
    }

    //检查密码
    public function check_card_password($weid, $pwd){
        return pdo_fetch("SELECT id FROM ".tablename('icard_style')." WHERE weid = :weid and pwd=:pwd ORDER BY `id` DESC limit 1", array(':weid' => $weid ,':pwd' => $pwd));
    }
    public function check_outlet_password($weid, $pwd, $id){
        return pdo_fetch("SELECT id FROM ".tablename('icard_outlet')." WHERE weid = :weid and password=:pwd and id=:id ORDER BY `id` DESC limit 1", array(':weid' => $weid ,':pwd' => $pwd, ':id' => $id));
    }

    //新增兑换码
    public function add_sncode($data = array()){
        $data = array(
            'pid' => $data['pid'],
            'type' => $data['type'],
            'weid' => $data['weid'],
            'sncode' => $data['sncode'],
            'from_user' => $data['from_user'],
            'state' => 0,
            'winningtime' => TIMESTAMP,
            'usetime' => 0,
            'dateline' => TIMESTAMP
        );
        pdo_insert('icard_sncode', $data);
        return pdo_insertid();
    }

    public function get_coupon_strwhere($weid, $from_user, $levelid){
        $isNotConsume = 0;//从未消费
        $isNotConsumeInMonth = 0;//在一个月内从未消费
        $singleConsume = 0;//单次消费
        $totalConsume = 0;//累计消费

        //从未消费
        $money_obj = pdo_fetch("SELECT * FROM ".tablename('icard_money_log')." WHERE weid = :weid AND from_user=:from_user ORDER BY `id` DESC", array(':weid' => $weid, ':from_user' => $from_user));
        if(empty($money_obj))$isNotConsume = 1;
        //一个月内从未消费
        $money_month_obj = pdo_fetch("SELECT * FROM ".tablename('icard_money_log')." WHERE weid = :weid AND from_user=:from_user AND DATE_SUB(CURDATE(), INTERVAL 1 MONTH) <= date(FROM_UNIXTIME(dateline)) ORDER BY `id` DESC", array(':weid' => $weid, ':from_user' => $from_user));
        if(empty($money_month_obj))$isNotConsumeInMonth = 1;
        //单次消费
        $money_single_obj = pdo_fetch("SELECT money FROM ".tablename('icard_money_log')." WHERE weid = :weid AND from_user=:from_user  ORDER BY `money` DESC limit 1", array(':weid' => $weid, ':from_user' => $from_user));
        if(!empty($money_single_obj)){
            $singleConsume = $money_single_obj['money'];
        }
        //累计消费
        $totalConsume = pdo_fetchcolumn("SELECT sum(money) FROM ".tablename('icard_money_log')." WHERE weid = :weid AND from_user=:from_user", array(':weid' => $weid, ':from_user' => $from_user));
        $strwhere = ' levelid=0 ';
        if($levelid != 0){
            $strwhere .= ' OR levelid= '.$levelid;
        }
        if($isNotConsume == 1){
            $strwhere .= ' OR levelid=-2 ';
        }
        if($isNotConsumeInMonth == 1){
            $strwhere .= ' OR levelid=-3 ';
        }
        if($singleConsume > 0){
            $strwhere .= ' OR (levelid=-4 AND permoney<= '.$singleConsume.') ';
        }
        if($totalConsume > 0){
            $strwhere .= ' OR (levelid=-5 AND allmoney<= '.$totalConsume.') ';
        }
        return $strwhere;
    }

    public function get_gifts_arr_front($type, $weid){
        $tablename ='';
        switch($type){
            case 4:
                $tablename = tablename('icard_gift');
                break;
            case 3:
                $tablename = tablename('icard_privilege');
                break;
            case 2:
                $tablename = tablename('icard_coupon');
                break;
        }
        $levels = pdo_fetchall("SELECT * FROM ".$tablename." WHERE weid = '{$weid}' ");
        $arr = array();
        foreach($levels as $key => $value){
            $arr[$value['id']] = $value['title'];
        }
        return $arr;
    }

    public function showMessage($msg = '', $state = 0, $isajax = true){
        $result['msg'] = $msg;
        $result['state'] = $state;//1代表成功
        if($isajax){
            message($result, '', 'ajax');
        }else{
            message($result['msg'], '', 'error');
        }
    }

    //取得二维码
    public function getQRImage($filename, $url){
        global $_W;
        $filepath ='source/modules/icard/data/';
        QRcode::png($_W['siteroot'].$url, $filepath.$filename.'.png', QR_ECLEVEL_L, 4);
        echo '<img src="'.$filepath.$filename.'.png" />';
    }

    public function addCardLog($type, $score, $outletid = 0, $cardid = 0){
        global $_W;
        $data = array(
            'weid' => $_W['weid'],
            'type' => $type,
            'score' => $score,
            'outletid' => $outletid,
            'cardid' => $cardid,
            'dateline' => TIMESTAMP
        );
        pdo_insert('icard_card_log', $data);
    }
}