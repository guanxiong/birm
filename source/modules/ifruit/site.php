<?php
/**
 * 幸运机
 * 作者:迷失卍国度
 * qq : 15595755
 * [WNS]更多模块请浏览：BBS.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class IfruitModuleSite extends WeModuleSite {
    public $modulename = 'ifruit';//模块标识

    //首页
    public function doMobileWapIndex(){
        global $_GPC;
        $title = '水果达人';
        $rid = intval($_GPC['rid']);
        $weid = intval($_GET['weid']);
        $snid = intval($_GET['snid']);
        $page_from_user = $_GPC['from_user'];
        $card_flag = intval($_GET['card']);

        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        if (empty($from_user))exit('非法参数！');//debug
        //验证是否存在规则
        $this -> is_have_rule($rid, $weid);//debug

        //活动状态 0:活动过期;1:正常进行;2:抽奖次数已到
        $act_status = 1;
        //sn状态码 0:没抽过;1:已抽中;2:已兑换
        $sn_status = 0;

        //判断活动时间
        $reply = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_reply')." WHERE rid = '$rid' AND weid='$weid' LIMIT 1");
        if(TIMESTAMP < $reply['starttime'] || TIMESTAMP > $reply['endtime']) {
            $act_status = 0;//活动结束
        }

        //活动开始时间
        $starttime = date('Y-m-d H:i:s', $reply['starttime']);
        //活动结束时间
        $endtime = date('Y-m-d H:i:s', $reply['endtime']);
        $detail = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_detail')." WHERE rid = '$rid' AND weid='$weid' LIMIT 1");
        //单次中奖//多次中奖
        $is_repeat_lottery = intval($detail['is_repeat_lottery']);
        $strwhere = " where from_user = '{$from_user}' AND rid='{$rid}'";
        //是否允许重复抽奖
        if($is_repeat_lottery == 1){
            //允许重复抽奖时读取已抽中未兑换的sn码
            $strwhere .= " AND status=1 ";
        } else {
            $strwhere .= " AND (status=1 OR status=2) ";
        }
        //兑换码id
        if($snid != 0) $strwhere .= " AND id=".$snid;
        $sn = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_sn').$strwhere." ORDER BY status,mobile,winningtime DESC LIMIT 1");
        if(!empty($sn)){
            $sn_status = intval($sn['status']);
            $awardid = $sn['awardid'];
            $award = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_award')." WHERE rid = '$rid' AND weid='$weid' AND id=$awardid LIMIT 1");
        }

        //判断今日抽奖次数
        $today_lottery_times = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->modulename.'_lottery_log')." WHERE dateline > '".strtotime(date('Y-m-d'))."' AND from_user = '$from_user' AND rid=".$rid);
        if (!empty($detail['max_lottery']) && $today_lottery_times >= $detail['max_lottery'] ) {
            $tip = '您已经超过当日抽奖次数限制啦！';
            $act_status = 2;
        }
        $have_lottery_times = 0;
        //判断用户总抽奖次数
        $user_lottery_times = $this -> get_total_times($from_user, $rid);

        if (!empty($detail['lottery_times']) && $user_lottery_times >= $detail['lottery_times']) {
            $tip = '您的抽奖次数已经用完啦！';
            $act_status = 2;
        }
        //剩余抽奖次数
        $have_lottery_times = $detail['lottery_times'] - $user_lottery_times;
        if($have_lottery_times < 0) $have_lottery_times = 0;
        //我的中奖记录
        $page_lottery_recored = '';
        //奖品显示
        $page_award = '';
        $awards = pdo_fetchall("SELECT * FROM ".tablename($this->modulename.'_award')." WHERE rid = '$rid' AND weid='$weid' ORDER BY level");
        $page_award = $this -> get_awards($awards, $detail['show_award_num']);
        //我抽中的奖品
        $my_awards = $this -> show_my_record($weid, $rid, $from_user);

        //联系电话
        $page_mobile = $this -> get_user_mobile($from_user);

        //会员卡
        if($card_flag == 1){
            $card = $this -> get_card($from_user, $weid);
        }


        include $this->template('wap_index');
    }

    //抽奖
    public function doMobileLottery(){
        global $_GPC;
        $title = '水果达人';
        $rid = intval($_GPC['rid']);
        $weid = intval($_GET['weid']);
        $page_fromuser = $_GPC['from_user'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $card_flag = intval($_GET['card']);//是否会员卡积分抽奖
        if(!$this -> debug){
            if (empty($from_user))exit('非法参数！');
        }
        //活动状态 0:活动过期;1:正常进行;2:抽奖次数已到
        $act_status = 1;
        //用户sn状态码 0:没抽过;1:已抽中;2:已兑换
        $sn_status = 0;
        $detail = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_detail')." WHERE rid = '$rid' AND weid='$weid' LIMIT 1");
        $reply = pdo_fetch("select * FROM ".tablename($this->modulename.'_reply')." where weid = '{$weid}' and rid='{$rid}' limit 1");
        if(empty($reply)) $this -> showMessage('非法参数.',-1);
        if(TIMESTAMP < $reply['starttime'] || TIMESTAMP > $reply['endtime']) $this -> showMessage('活动已经结束了,请继续关注我们的活动.',-1);

        //判断sn码是否存在
        $sn = pdo_fetch("select * FROM ".tablename($this->modulename.'_sn')." where from_user = '{$from_user}' and rid='{$rid}' and (status=1 or status=2) limit 1");
        if(!empty($sn)){
            if(intval($detail['is_repeat_lottery'])==0){
                $this -> showMessage('您已经中过奖啦,请继续关注我们的活动哦.',-1);
            }
        }

        if($card_flag == 0){
            //判断用户总抽奖次数
            $user_lottery_times = $this -> get_total_times($from_user, $rid);
            if (!empty($detail['lottery_times']) && $user_lottery_times >= $detail['lottery_times']) {
                $msg = '您的抽奖次数已经用完啦！';
                $is_card_score = $detail["is_card_score"];
                $card_score = $detail["card_score"];
                if($is_card_score == 1){
                    $msg .= "您可以使用会员积分进行抽奖，每次抽奖的积分为".$card_score.".";
                }
                $this -> showMessage($msg, -2);
            }
            //今日抽奖次数
            $today_times = $this -> get_today_times($from_user, $rid);
            if (!empty($detail['lottery_times']) && $today_times >= $detail['lottery_times'] && $detail['lottery_times'] != 0) {
                $this -> showMessage('您已经超过当日抽奖次数限制啦！', -1);
            }
        } else {
            //使用会员卡积分抽奖
            $card = $this -> get_card($from_user, $weid);
            if(empty($card)){
                $this -> showMessage('您还没有绑定会员卡!', -1);
            }else{
                //判断积分是否足够
                $card_score = intval($detail['card_score']);
                $balance_score = intval($card['balance_score']);
                if($card_score <= 0) $this -> showMessage('积分设置错误，请联系商家!', -1);
                if($card_score > $balance_score){
                    $this -> showMessage("积分不足,本次活动积分抽奖每次需要{$card_score}积分,你会员卡剩余{$balance_score}积分.", -1);
                } else {
                    pdo_query("UPDATE ".tablename('icard_card')." SET balance_score=balance_score-{$card_score} WHERE from_user='{$from_user}' AND weid = {$weid}");
                }
            }
        }

        ///
        ///抽奖
        ///
        //默认回复
        $result = array('left' => rand(0,8), 'middle' => rand(0,8), 'right' => rand(0,8), 'type' => 'bad', 'msg' => $detail['repeat_lottery_reply']);
        //抽取奖品id
        $awardid = $this -> getLotteryAward($rid);
        if(!empty($awardid)){
            $award = pdo_fetch("select * FROM ".tablename($this->modulename.'_award')." where id='{$awardid}' limit 1");
            if(!empty($award) && $award['total'] > 0){
                //奖品不为0的时候
                $rand = rand(0,8);
                $result['left'] = $rand;
                $result['middle'] = $rand;
                $result['right'] = $rand;
                //减去一个产品
                pdo_query("UPDATE ".tablename($this->modulename.'_award')." SET total = total - 1 WHERE rid = '$rid' AND id = '$awardid'");
                //奖品sn码
                $lotery_sn = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_sn')." WHERE rid = '$rid' AND awardid = '$awardid' and status = 0 limit 1");
                if(!empty($lotery_sn)){
                    //更新抽中sn码 状态、中奖时间
                    pdo_query("UPDATE ".tablename($this->modulename.'_sn')." SET status = 1,winningtime =".TIMESTAMP.",from_user = '$from_user' WHERE id = '{$lotery_sn['id']}'");
                    $result['award'] = $award['title'];//奖品
                    $result['levelname'] = $award['levelname'];//等级
                    $result['sn'] = $lotery_sn['sn'];//sn码
                    $result['dateline'] = date('Y-m-d  H:i:s', TIMESTAMP);
                    $result['type'] = 'lottery';
                    if(!empty($sn)) $result['type'] = 'repeat_lottery';//重复中奖中奖后做跳转
                }
            }
        }
        //添加抽奖记录
        $data = array(
            'rid' => $rid,
            'weid' => $weid,
            'awardid' => $awardid,
            'from_user' => $from_user,
            'status' => 1,
            'dateline' => TIMESTAMP,
        );
        if($card_flag == 1) $data['type'] = 1;
        pdo_insert($this->modulename.'_lottery_log', $data);
        //返回抽奖信息
        $result['success'] = 1;
        message($result, '', 'ajax');
    }

    //用户已抽奖次数
    public function get_total_times($from_user, $rid){
        return pdo_fetchcolumn("SELECT count(1) FROM ".tablename($this->modulename.'_lottery_log')."  WHERE from_user = '{$from_user}' AND rid={$rid} AND type=0");
    }

    //用户今天抽奖次数
    public function get_today_times($from_user, $rid){
        return pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->modulename.'_lottery_log')." WHERE dateline > '".strtotime(date('Y-m-d'))."' AND from_user = '$from_user' AND rid={$rid} AND type=0");
    }

    //取得会员卡
    public function get_card($from_user, $weid){
        $card = pdo_fetch("SELECT * FROM ".tablename('icard_card')." WHERE from_user = '$from_user' AND weid='$weid' LIMIT 1");
        return $card;
    }

    //更新手机号码
    public function doMobileUpdateMobile(){
        global $_GPC;
        $result = array('success' => '-1', 'msg' => '操作失败.');
        $rid = intval($_GPC['rid']);
        $weid = intval($_GET['weid']);
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $sn = $_GPC['sn'];
        $mobile = $_GPC['tel'];
        if (empty($rid) || empty($weid) || empty($from_user) || empty($sn))$this -> showMessage('非法参数！', -1);
        if (empty($mobile)) $this -> showMessage('请输入联系信息！', -1);

        $rowcount = pdo_query("UPDATE ".tablename($this->modulename.'_sn')." SET mobile = '{$mobile}',status=1 WHERE rid={$rid} AND from_user= '$from_user' AND sn='{$sn}' AND mobile='' AND weid={$weid}");
        if($rowcount > 0){//更新成功
            $result['success'] = 1;
        }else{
            $result['success'] = 0;
        }
        $result['msg'] = '操作失败!';
        message($result, '', 'ajax');
    }

    //商家密码确认 更新兑换码状态
    public function doMobileUpdatePwd(){
        global $_GPC;
        $result = array('success' => '-1', 'msg' => '操作失败.');
        $rid = intval($_GPC['rid']);
        $weid = intval($_GET['weid']);
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $sn = $_GPC['sn'];
        $pwd = trim($_GPC['pwd']);

        if (empty($weid) || empty($from_user) || empty($sn))$this -> showMessage('非法参数！', -1);
        if (empty($pwd)) $this -> showMessage('请输入密码！', -1);

        //判断手机号码有没提交
        $obj_sn = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_sn')." WHERE rid = {$rid} AND status=1 AND from_user = '$from_user' AND sn='{$sn}' AND weid={$weid} LIMIT 1");
        if(empty($obj_sn)){
            $this -> showMessage('非法操作！', -1);
        }else{
            if(trim($obj_sn['mobile']) == '')$this -> showMessage('请先填写联系电话！', -1);
        }

        $detail = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_detail')." WHERE rid = {$rid} AND weid={$weid} LIMIT 1");
        if($detail['pwd'] == $pwd && !empty($detail['pwd'])) {//更新状态
            $rowcount = pdo_query("UPDATE ".tablename($this->modulename.'_sn')." SET status = 2,usetime=".TIMESTAMP." WHERE rid={$rid} AND from_user = '$from_user' AND sn='{$sn}'");
            if($rowcount > 0) {//更新成功
                $result['success'] = 1;
                $result['msg'] = '恭喜您，提交成功!';
            }
        } else {
            $result['success'] = -1;
            $result['msg'] = '密码错误，请重新输入!';
        }
        message($result, '', 'ajax');
    }

    //抽取奖品
    public function getLotteryAward($rid = 0){
        if($rid==0) return 0;
        $awards = pdo_fetchall("SELECT id, probalilty FROM ".tablename($this->modulename.'_award')." WHERE rid = '$rid' and total<>0 ORDER BY probalilty ASC");
        //计算每个礼物的概率
        $probability = 0;
        $rate = 1;
        $award = array();
        foreach ($awards as $key => $value){
            if (empty($value['probalilty'])) {
                continue;
            }
            if ($value['probalilty'] < 1) {
                $temp = explode('.', $value['probalilty']);
                $temp = pow(10, strlen($temp[1]));
                $rate = $temp < $rate ? $rate : $temp;
            }
            $probability = $probability + $value['probalilty'] * $rate;
            $award[] = array('id' => $value['id'], 'probalilty' => $probability);
        }
        $all = 100 * $rate;
        if($probability < $all){
            $award[] = array('title' => '','probalilty' => $all);
        }
        mt_srand((double) microtime()*1000000);
        $rand = mt_rand(1, $all);
        foreach ($award as $key => $value){
            if(isset($award[$key - 1])){
                if($rand > $award[$key -1]['probalilty'] && $rand <= $value['probalilty']){
                    $awardid = $value['id'];
                    break;
                }
            }else{
                if($rand > 0 && $rand <= $value['probalilty']){
                    $awardid = $value['id'];
                    break;
                }
            }
        }
        return $awardid;
    }

    //我的中奖记录
    public function show_my_record($weid, $rid, $from_user){
        $sql = "SELECT *,a.id as snid FROM ".tablename($this->modulename.'_sn')." a inner join ".tablename($this->modulename.'_award')." b  ON a.awardid=b.id   WHERE a.from_user = '{$from_user}' AND a.weid={$weid} AND a.rid='{$rid}' AND (a.status=1 or a.status=2) ORDER BY winningtime";
        $awards = pdo_fetchall($sql);
        return $awards;
    }

    //展示产品
    public function get_awards($awards, $show_award_num){
        $level = 0;
        $page_award_info = '';
        $isfirst = false;
        foreach($awards as $key => $value){
            $page_award_info .= "<li>".$value['levelname']."： ".$value['title']."";
            if($show_award_num){
                $page_award_info .= "  数量： ".$value['total']."</li>";
            }
        }
        return $page_award_info;
    }

    //web
    public function get_user_mobile($from_user){
        $sn = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_sn')." WHERE from_user = '$from_user' AND mobile<>'' ORDER BY winningtime DESC LIMIT 1");
        return $sn['mobile'];
    }

    //web
    public function showMessage($msg, $success = 1, $redirect = '', $type = 'ajax'){
        $result['success'] = $success;
        $result['msg'] = $msg;
        message($result, $redirect, $type);
    }

    //web admin
    public function is_have_rule($rid, $weid){
        if($rid <= 0) {
            message('非法操作.');
        } else {
            $rule = pdo_fetch("SELECT id FROM ".tablename('rule')." WHERE module = '{$this->modulename}' AND weid = '{$weid}' AND id={$rid}");
            if(empty($rule)) {
                message('非法操作,找不到相关数据!');
            }
        }
    }
}