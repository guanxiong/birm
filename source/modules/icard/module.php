<?php
/**
 * 会员卡模块
 * [WeEngine System] 更多模块请浏览：BBS.b2ctui.com
 */
defined('IN_IA') or exit('Access Denied');

class IcardModule extends WeModule {
    public $name = 'Icard';
    public $title = '';
    public $ability = '';
    public $tablename = 'icard_reply';

    public $action = 'style';//方法
    public $modulename = 'icard';//模块标识
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
        'outlet' => '门店系统',
        'userlog' => '会员统计'
    );
    //2优惠券3特权4礼品券

    public function fieldsFormSubmit($rid = 0) {
        global $_GPC, $_W;
        $id = intval($_GPC['reply_id']);
        $data = array(
            'rid' => $rid,
            'weid' => $_W['weid'],
            'title' => $_GPC['title'],
            'title_not' => $_GPC['title_not'],
            'picture' => $_GPC['picture'],
            'picture_not' => $_GPC['picture_not'],
            'description' => $_GPC['description'],
            'description_not' => $_GPC['description_not'],
            'dateline' => TIMESTAMP
        );

        if (empty($id)) {
            pdo_insert($this->tablename, $data);
        } else {
            if (!empty($_GPC['picture'])) {
                file_delete($_GPC['picture-old']);
            } else {
                unset($data['picture']);
            }
            unset($data['dateline']);
            pdo_update($this->tablename, $data, array('id' => $id));
        }
    }

    public function fieldsFormDisplay($rid = 0) {
        global $_W;
        if (!empty($rid)) {
            $reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
            $starttime = intval($reply['starttime']) == 0? TIMESTAMP : $reply['starttime'];
            $endtime = intval($reply['endtime']) == 0 ? TIMESTAMP + 86400 * 10 : $reply['endtime'];
        } else {
            $starttime = TIMESTAMP;
            $endtime = TIMESTAMP + 86400 * 10;
        }
        include $this->template('form');
    }

    public function fieldsFormValidate($rid = 0) {
        return true;
    }

    //删除规则
    public function ruleDeleted($rid = 0) {
        global $_W;
        $replies = pdo_fetchall("SELECT id FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
        $deleteid = array();
        if (!empty($replies)) {
            foreach ($replies as $index => $row) {
                //file_delete($row['thumb']);
                $deleteid[] = $row['id'];
            }
        }
        pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
        return true;
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

    public function get_user_level($weid, $total_score){
        $sql = "SELECT id,levelname FROM ".tablename('icard_level')." WHERE weid = :weid and :totalscore>=min and :totalscore<=max ORDER BY `min` limit 1";
        return pdo_fetch($sql, array(':weid' => $weid, ':totalscore' => $total_score));
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

    public function doTest(){

    }

    /*
     *
     * 后台
     *
     */
    //会员卡样式编辑
    public function doStyle(){
        global $_GPC, $_W;
        checklogin();
        $action = 'style';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        $this -> icard_style_check();
        if (checksubmit('submit')) {
            $data = array();
            $data['cardpre'] = trim($_GPC['cardpre']);
            $data['cardstart'] = intval($_GPC['cardstart']);
            $data['cardname'] = trim($_GPC['cardname']);
            $data['cardnamecolor'] = trim($_GPC['cardnamecolor']);
            $data['cardnumcolor'] = trim($_GPC['cardnumcolor']);
            $data['bg'] = trim($_GPC['hidbg']);
            $data['logo'] = trim($_GPC['logo']);
            $data['diybg'] = trim($_GPC['diybg']);
            $data['pwd'] = trim($_GPC['pwd']);
            $data['cardstart'] = intval($_GPC['cardstart']);
            $data['show_privilege'] = intval($_GPC['show_privilege']);
            $data['show_coupon'] = intval($_GPC['show_coupon']);
            $data['show_gift'] = intval($_GPC['show_gift']);
            $data['dateline'] = TIMESTAMP;

            if(empty($data['cardname'])) {
                message('会员卡必须填写！');
            }
            if(strlen($data['pwd'])>20){
                message('输入的密码必须小于20个字符！');
            }
            //默认颜色#000000
            if(empty($data['cardnamecolor'])) {
                $data['cardnamecolor'] = '#000000';
            }
            if(empty($data['cardnumcolor'])) {
                $data['cardnumcolor'] = '#000000';
            }
            //当自定义网址不为空的时候
            if(!empty($data['diybg'])) {
                $data['bg'] = $data['diybg'];
            }
            //自定义卡号英文编号
            if(!empty($data['cardpre'])){
                if(strlen($data['cardpre'])>8){
                    message('自定义卡号英文编号不能大于8位.');
                }
            }
            //初始卡号
            if(!empty($data['cardstart'])){
                if(strlen($data['cardstart']) > 9){
                    message('初始卡号不能大于9位.');
                }
            } else {
                $data['cardstart'] = 1000001;
            }

            $this -> icard_style_update($data);
            message('操作成功！', $url);
        } else {
            $reply = pdo_fetch("select * from ".tablename('icard_style')." where weid =".$_W['weid']);
            $page_sel_bg = '';
            for($i=1; $i<38; $i++){
                if($i<10)
                    $bg_num = '0'.$i;
                else
                    $bg_num = $i;
                $imgpath = "./source/modules/icard/template/images/card_bg".$bg_num.".png";
                if($reply['bg'] == $imgpath){
                    $page_sel_bg .= '<option value="'.$imgpath.'" selected>'.$imgpath.'</option>';
                    $page_sel_bg .= '<option value="'.$imgpath.'" selected>'.$imgpath.'</option>';
                }else{
                    $page_sel_bg .= "<option value=";
                    $page_sel_bg .= '<option value="'.$imgpath.'">'.$imgpath.'</option>';
                }
            }

            include $this->template('style');
        }
    }
    //商家编辑
    public function doBusiness(){
        global $_GPC, $_W;
        checklogin();
        $id = intval($_GPC['id']);

        $action = 'business';
        $title = $this -> actions_titles[$action];

        $reply = pdo_fetch("select * from ".tablename('icard_business')." where weid =". $_W['weid']);
        if (checksubmit('submit')) {
            $data = array();
            $data['title'] = trim($_GPC['title']);
            $data['logo'] = trim($_GPC['hidlogo']);
            $data['content'] = trim($_GPC['content']);
            $data['info'] = trim($_GPC['info']);
            $data['tel'] = trim($_GPC['tel']);
            $data['address'] = trim($_GPC['address']);
            $data['location_p'] = trim($_GPC['location_p']);
            $data['location_c'] = trim($_GPC['location_c']);
            $data['location_a'] = trim($_GPC['location_a']);
            $data['category_f'] = trim($_GPC['category_f']);
            $data['category_s'] = trim($_GPC['category_s']);
            $data['place'] = trim($_GPC['place']);
            $data['lng'] = trim($_GPC['lng']);
            $data['lat'] = trim($_GPC['lat']);

            if(!empty($reply)){
                if (!empty($_GPC['hidlogo'])) {
                    if($_GPC['hidlogo'] != $_GPC['hidlogo-old']){
                        file_delete($_GPC['hidlogo-old']);
                    }
                }
                $this -> icard_business_update($data);
            }else{
                $this -> icard_business_insert($data);
            }
            message('操作成功！', create_url('site/module', array('do' => $action, 'name' => $this->modulename)));
        } else {
            include $this->template('business');
        }
    }
    //积分策略
    public function doScore(){
        global $_GPC, $_W;
        checklogin();
        $action = 'score';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        $reply = pdo_fetch("select * from ".tablename('icard_score')." where weid =". $_W['weid']);
        if (checksubmit('submit')) {
            $data = array();
            $data['card_info'] = trim($_GPC['card_info']);
            $data['score_info'] = trim($_GPC['score_info']);
            $data['day_score'] = intval($_GPC['day_score']);
            $data['dayx_score'] = intval($_GPC['dayx_score']);
            $data['payx_score'] = intval($_GPC['payx_score']);

            if($data['day_score'] < 0 || $data['dayx_score'] < 0 || $data['payx_score'] < 0){
                message('积分请不要输入负数','','error');
            }
            if($data['day_score'] > 100000 || $data['dayx_score'] > 100000 || $data['payx_score'] > 100000){
                message('积分请不要输入大于10万','','error');
            }

            if(!empty($reply)){
                $this -> icard_score_update($data);
            }else{
                $this -> icard_score_insert($data);
            }
            message('操作成功！', $url);
        } else {
            include $this->template('score');
        }
    }
    //等级设置
    public function doLevel(){
        global $_GPC, $_W;
        checklogin();
        $action = 'level';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        if(checksubmit('submit')){
            // 修改
            if(is_array($_GPC['levelname'])) {
                foreach($_GPC['levelname'] as $id => $val) {
                    $levelname = trim($_GPC['levelname'][$id]);
                    $min = intval($_GPC['min'][$id]);
                    $max = intval($_GPC['max'][$id]);
                    if(empty($levelname)){
                        continue;
                    }
                    if($max <= $min){
                        message($levelname.'积分范围有误，请重新输入.', $url, 'error');
                    }
                    if($max < 0 || $min < 0){
                        message('积分不允许负数，请重新输入.', $url, 'error');
                    }
                    $data = array(
                        'levelname' => $levelname,
                        'min' => $min,
                        'max' => $max,
                        'weid' => $_W['weid']
                    );
                    pdo_update('icard_level', $data, array('id' => $id));
                }
            }
            //增加
            if(is_array($_GPC['newlevelname'])) {
                foreach($_GPC['newlevelname'] as $nid => $val) {
                    $levelname = trim($_GPC['newlevelname'][$nid]);
                    $min = intval($_GPC['newmin'][$nid]);
                    $max = intval($_GPC['newmax'][$nid]);
                    if(empty($levelname)){
                        continue;
                    }
                    if($max <= $min){
                        message($levelname.'积分范围有误，请重新输入.', $url, 'error');
                    }
                    if($max < 0 || $min < 0){
                        message('积分不允许负数，请重新输入.', $url, 'error');
                    }
                    $data = array(
                        'levelname' => $levelname,
                        'min' => $min,
                        'max' => $max,
                        'dateline' => TIMESTAMP,
                        'weid' => $_W['weid']
                    );
                    pdo_insert('icard_level', $data);
                }
            }
            message('操作成功.', $url, 'success');
        }else{
            $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_level')." WHERE weid = '{$_W['weid']}' order by max ");
            include $this->template('level');
        }
    }
    //删除等级
    public function doLevelDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'level';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_level', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }
    //通知管理
    public function doAnnounce(){
        global $_W, $_GPC;
        checklogin();
        $action = 'announce';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $levels = $this -> get_levels();//会员等级
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = " WHERE weid = '{$_W['weid']}' and type=0 ";

        $announces = pdo_fetchall("SELECT * FROM ".tablename('icard_announce')." {$where} order by displayorder desc,id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($announces)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_announce')." $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('announce');
    }

    function get_levels(){
        global $_W;
        $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_level')." WHERE weid = '{$_W['weid']}' ");
        $arr = array();
        $arr[0] = "所有会员";
        foreach($levels as $key => $value){
            $arr[$value['id']] = $value['levelname'];
        }
        return $arr;
    }

    public function doAnnounceForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'announce';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        $reply = pdo_fetch("select * from ".tablename('icard_announce')." where id =".$id);
        $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_level')." WHERE weid = '{$_W['weid']}' order by max");

        if(checksubmit('submit')){
            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['type'] = 0;
            $data['title'] = trim($_GPC['title']);
            $data['content'] = trim($_GPC['content']);
            $data['levelid'] = intval($_GPC['levelid']);
            $data['displayorder'] = intval($_GPC['displayorder']);
            $data['updatetime'] = TIMESTAMP;
            $data['dateline'] = TIMESTAMP;

            if(istrlen($data['title']) == 0){
                message('没有输入标题.','','error');
            }
            if(istrlen($data['title']) > 30) {
                message('标题不能多于30个字。','error');
            }
            if(istrlen($data['content']) == 0){
                message('没有输入内容.','','error');
            }
            if(istrlen($data['content']) > 2000){
                message('内容过多请重新输入.','','error');
            }

            if(!empty($reply)){
                unset($data['dateline']);
                pdo_update('icard_announce', $data, array('id' => $id, 'weid' => $_W['weid']));
            }else{
                pdo_insert('icard_announce', $data);
            }
            message('操作成功!', $url);
        }
        include $this->template('announce_form');
    }

    public function doAnnounceDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'announce';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_announce', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }
    //礼品券管理
    public function doGift(){
        global $_W, $_GPC;
        checklogin();
        $action = 'gift';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        if (checksubmit('submit')) {//排序
            if(is_array($_GPC['displayorder'])) {
                foreach($_GPC['displayorder'] as $id => $val) {
                    $data = array('displayorder' => intval($_GPC['displayorder'][$id]));
                    pdo_update('icard_gift', $data, array('id' => $id));
                }
            }
            message('操作成功!', $url);
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        $gifts = pdo_fetchall("SELECT * FROM ".tablename('icard_gift')." {$where} order by displayorder desc,id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($gifts)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_gift')." $where");
            $pager = pagination($total, $pindex, $psize);
        }

        include $this->template('gift');
    }

    public function doGiftForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'gift';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        $reply = pdo_fetch("select * from ".tablename('icard_gift')." where id =".$id);

        $starttime = TIMESTAMP;
        $endtime = TIMESTAMP + 86400 * 10;
        if(!empty($reply)){
            $starttime = $reply['starttime'];
            $endtime = $reply['endtime'];
        }

        if(checksubmit('submit')){
            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['title'] = trim($_GPC['title']);
            $data['content'] = trim($_GPC['content']);
            $data['picture'] = trim($_GPC['hidpicture']);
            $data['needscore'] = intval($_GPC['needscore']);
            $data['count'] = intval($_GPC['count']);
            $data['displayorder'] = intval($_GPC['displayorder']);
            $data['starttime'] = strtotime($_GPC['starttime']);
            $data['endtime'] = strtotime($_GPC['endtime']);
            $data['updatetime'] = TIMESTAMP;
            $data['dateline'] = TIMESTAMP;

            if(istrlen($data['title']) == 0){
                message('没有输入标题.','','error');
            }
            if(istrlen($data['title']) > 30) {
                message('标题不能多于30个字。','','error');
            }
            if(istrlen($data['content']) == 0){
                message('没有输入内容.','','error');
            }
            if(istrlen($data['content']) > 2000){
                message('内容过多请重新输入.','','error');
            }
            if($data['needscore'] <= 0){
                message('所需积分必须大于0.','','error');
            }

            if(!empty($reply)){
                unset($data['dateline']);
                if (!empty($_GPC['hidpicture'])) {
                    if($_GPC['hidpicture'] != $_GPC['hidpicture-old']){
                        file_delete($_GPC['hidpicture-old']);
                    }
                }
                pdo_update('icard_gift', $data, array('id' => $id, 'weid' => $_W['weid']));
            }else{
                pdo_insert('icard_gift', $data);
            }
            message('操作成功!', $url);
        }
        include $this->template('gift_form');
    }

    public function doGiftDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'gift';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_gift', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }
	

    //优惠券
    public function doCoupon(){
        global $_W, $_GPC;
        checklogin();
        $action = 'coupon';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        if (checksubmit('submit')) {//排序
            if(is_array($_GPC['displayorder'])) {
                foreach($_GPC['displayorder'] as $id => $val) {
                    $data = array('displayorder' => intval($_GPC['displayorder'][$id]));
                    pdo_update('icard_coupon', $data, array('id' => $id));
                }
            }
            message('操作成功!', $url);
        }
        $levels = $this -> get_levels();
        $levels[-2] = '开卡从未消费的会员';
        $levels[-3] = '一个月未消费的会员';
        $levels[-4] = '单次消费满X元的会员';
        $levels[-5] = '累计消费满X元的会员';

        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        $coupons = pdo_fetchall("SELECT * FROM ".tablename('icard_coupon')." {$where} order by displayorder desc,id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($gifts)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_coupon')." $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('coupon');
    }

    public function doCouponForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'coupon';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        $reply = pdo_fetch("select * from ".tablename('icard_coupon')." where id =".$id);
        if(!empty($reply)){
            $levelarr = explode(',', $reply['levelids']);
        }

        $starttime = TIMESTAMP;
        $endtime = TIMESTAMP + 86400 * 10;
        if(!empty($reply)){
            $starttime = $reply['starttime'];
            $endtime = $reply['endtime'];
        }
        //等级
        $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_level')." WHERE weid = '{$_W['weid']}' order by max");

        if(checksubmit('submit')){
            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['title'] = trim($_GPC['title']);
            $data['content'] = trim($_GPC['content']);
            $data['levelid'] = intval($_GPC['levelid']);
            $data['count'] = intval($_GPC['count']);
            $data['permoney'] = intval($_GPC['permoney']);
            $data['allmoney'] = intval($_GPC['allmoney']);
            $data['displayorder'] = intval($_GPC['displayorder']);
            $data['starttime'] = strtotime($_GPC['starttime']);
            $data['endtime'] = strtotime($_GPC['endtime']);
            $data['updatetime'] = TIMESTAMP;
            $data['dateline'] = TIMESTAMP;

            if(istrlen($data['title']) == 0){
                message('没有输入标题.','','error');
            }
            if(istrlen($data['title']) > 30) {
                message('标题不能多于30个字。','','error');
            }
            if(istrlen($data['content']) == 0){
                message('没有输入内容.','','error');
            }
            if(istrlen($data['content']) > 3000){
                message('内容过多请重新输入.','','error');
            }
            if($data['count']<=0){
                message('优惠券张数必须大于0.','','error');
            }
            if($data['count']>10000){
                message('优惠券张数不能大于10000.','','error');
            }

            if(!empty($reply)){
                unset($data['dateline']);
                pdo_update('icard_coupon', $data, array('id' => $id, 'weid' => $_W['weid']));
            }else{
                pdo_insert('icard_coupon', $data);
            }
            message('操作成功!', $url);
        }
        include $this->template('coupon_form');
    }

    public function doCouponDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'coupon';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_coupon', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }

    //特权管理
    public function doPrivilege(){
        global $_W, $_GPC;
        checklogin();
        $action = 'privilege';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        if (checksubmit('submit')) {//排序
            if(is_array($_GPC['displayorder'])) {
                foreach($_GPC['displayorder'] as $id => $val) {
                    $data = array('displayorder' => intval($_GPC['displayorder'][$id]));
                    pdo_update('icard_privilege', $data, array('id' => $id));
                }
            }
            message('操作成功!', $url);
        }

        $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_level')." WHERE weid = '{$_W['weid']}' order by max");
        foreach($levels as $key => $value){
            $levelarr[$value['id']] = $value['levelname'];

        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        $gifts = pdo_fetchall("SELECT * FROM ".tablename('icard_privilege')." {$where} order by displayorder desc,id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($gifts)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_privilege')." $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('privilege');
    }

    public function doPrivilegeForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'privilege';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        $reply = pdo_fetch("select * from ".tablename('icard_privilege')." where id =".$id);
        if(!empty($reply)){
            $levelarr = explode(',', $reply['levelids']);
        }

        $starttime = TIMESTAMP;
        $endtime = TIMESTAMP + 86400 * 10;
        if(!empty($reply)){
            $starttime = $reply['starttime'];
            $endtime = $reply['endtime'];
        }
        //等级
        $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_level')." WHERE weid = '{$_W['weid']}' order by max");

        if(checksubmit('submit')){

            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['title'] = trim($_GPC['title']);
            $data['content'] = trim($_GPC['content']);
            $data['levelids'] = trim(implode(',',$_GPC['levelids']));

            //implode(',',$biuuu); //arr to str
            //explode //str to arr
            //message($data['levelids']);
            $data['count'] = intval($_GPC['count']);
            $data['displayorder'] = intval($_GPC['displayorder']);
            $data['starttime'] = strtotime($_GPC['starttime']);
            $data['endtime'] = strtotime($_GPC['endtime']);
            $data['updatetime'] = TIMESTAMP;
            $data['dateline'] = TIMESTAMP;

            if(istrlen($data['title']) == 0){
                message('没有输入标题.','','error');
            }
            if(istrlen($data['title']) > 30) {
                message('标题不能多于30个字。','','error');
            }
            if(istrlen($data['content']) == 0){
                message('没有输入内容.','','error');
            }
            if(istrlen($data['content']) > 400){
                message('内容过多请重新输入.','','error');
            }
            if(strlen($data['levelids'])==''){
                message('请选择所属人群', '', 'error');
            }

            if(!empty($reply)){
                unset($data['dateline']);
                pdo_update('icard_privilege', $data, array('id' => $id, 'weid' => $_W['weid']));
            }else{
                $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename('icard_privilege')." where weid = {$_W['weid']}");
                if($total >= 8){
                    message('您最多可以创建8条特权!', $url);
                }
                pdo_insert('icard_privilege', $data);
            }
            message('操作成功!', $url);
        }
        include $this->template('privilege_form');
    }

    public function doPrivilegeDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'privilege';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_privilege', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }

    public function doOutlet(){
        global $_W, $_GPC;
        checklogin();
        $action = 'outlet';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        if (checksubmit('submit')) {//排序
            if(is_array($_GPC['displayorder'])) {
                foreach($_GPC['displayorder'] as $id => $val) {
                    $data = array('displayorder' => intval($_GPC['displayorder'][$id]));
                    pdo_update('icard_outlet', $data, array('id' => $id));
                }
            }
            message('操作成功!', $url);
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." {$where} order by displayorder desc,id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($gifts)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_outlet')." $where");
            $pager = pagination($total, $pindex, $psize);
        }

        include $this->template('outlet');
    }

    public function doOutLetForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'outlet';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        $reply = pdo_fetch("select * from ".tablename('icard_outlet')." where id =".$id);

        if(checksubmit('submit')){
            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['title'] = trim($_GPC['title']);
            $data['content'] = trim($_GPC['content']);
            $data['tel'] = trim($_GPC['tel']);
            $data['address'] = trim($_GPC['address']);
            $data['location_p'] = trim($_GPC['location_p']);
            $data['location_c'] = trim($_GPC['location_c']);
            $data['location_a'] = trim($_GPC['location_a']);
            $data['password'] = trim($_GPC['password']);
            $data['recharging_password'] = trim($_GPC['recharging_password']);
            $data['is_show'] = intval($_GPC['is_show']);
            $data['place'] = trim($_GPC['place']);
            $data['lng'] = trim($_GPC['lng']);
            $data['lat'] = trim($_GPC['lat']);
            $data['updatetime'] = TIMESTAMP;
            $data['dateline'] = TIMESTAMP;

            if(istrlen($data['title']) == 0){
                message('没有输入标题.','','error');
            }
            if(istrlen($data['title']) > 30) {
                message('标题不能多于30个字。','','error');
            }
            if(istrlen($data['content']) == 0){
                message('没有输入内容.','','error');
            }
            if(istrlen($data['content']) > 1000){
                message('内容过多请重新输入.','','error');
            }
            if(istrlen($data['tel']) == 0){
                message('没有输入联系电话.','','error');
            }
            if(istrlen($data['address']) == 0) {
                message('请输入地址。','','error');
            }
            if(istrlen($data['password']) == 0){
                message('没有输入确认密码.','','error');
            }
            if(istrlen($data['password']) > 16){
                message('确认密码不能大于16个字符.','','error');
            }
            if(istrlen($data['recharging_password']) == 0){
                message('没有输入充值密码.','','error');
            }
            if(istrlen($data['recharging_password']) > 16){
                message('充值密码不能大于16个字符.','','error');
            }

            if(!empty($reply)){
                unset($data['dateline']);
//                if (!empty($_GPC['hidpicture'])) {
//                    if($_GPC['hidpicture'] != $_GPC['hidpicture-old']){
//                        file_delete($_GPC['hidpicture-old']);
//                    }
//                }
                pdo_update('icard_outlet', $data, array('id' => $id, 'weid' => $_W['weid']));
            }else{
                pdo_insert('icard_outlet', $data);
            }
            message('操作成功!', $url);
        }
        include $this->template('outlet_form');
    }

    public function doOutletDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'outlet';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_outlet', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }

    public function doSncodeList(){
        global $_GPC, $_W;
        checklogin();
        $action =  'gift';
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        $type = intval($_GPC['type']);
        $action = $this -> get_action($type);
        $title = $this -> actions_titles[$action];
        $pid = intval($_GPC['pid']);

        $users = $this -> get_users_arr();
        $gifts = $this -> get_gifts_arr($type);

        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid ORDER BY displayorder DESC,id DESC", array(':weid' => $_W['weid']));

        $outlet = array();
        foreach($outlets as $key => $value){
            $outlet[$value['id']] = $value['title'];
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}' and type = '{$type}' and pid = '{$pid}'";
        $list = pdo_fetchall("SELECT * FROM ".tablename('icard_sncode')." {$where} order by id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_sncode')." $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('sncode_list');
    }

    public function doUseSncodeAdmin(){
        global $_GPC, $_W;
        checklogin();
        $action = 'gift';
        $title = $this -> actions_titles[$action];

        $type = intval($_GPC['type']);
        $pid = intval($_GPC['pid']);//商品id
        $url = create_url('site/module', array('do' => 'sncodelist', 'name' => $this->modulename, 'type' => $type, 'pid' => $pid));
        $snid = intval($_GPC['snid']);//兑换码id
        $outletid = intval($_GPC['store_id']);//门店id
        $weid = $_W['weid'];

        $sncode = pdo_fetch("SELECT * FROM ".tablename('icard_sncode')." WHERE weid = '{$_W['weid']}' AND id='{$snid}'");
        $from_user = $sncode['from_user'];

        //会员卡
        $card = $this -> get_card($weid, $from_user);
        if(empty($card))message('会员卡不存在.', $url, 'success');

        $money = intval($_GPC['money']);
        $payment = intval($_GPC['payment']);//0:现金消费 1:会员卡余额消费
        if($type != 4){//不是礼品券的时候
            if($money == 0){
                message('请输入消费金额.', $url, 'success');
            }
            if($payment == 1){//余额消费
                if($money > intval($card['coin'])){
                    message('会员卡余额不足,请使用其它支付方式.', $url, 'success');
                }
            }
        }

        //剩余积分
        $balance_score = intval($card['balance_score']);
        //兑换物品所需积分
        $need_score = 0;
        //检查积分
        if($type == 4){//礼品券兑换 //兑换码对应的类型 0:普通通知  2:优惠券 3:会员卡特权 4:礼品券
            $gift = $this -> get_gift($pid, $weid);
            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 4);
                if($usetimes[$pid] >= $gift['count'])message('该用户兑换次数已经用完.', $url, 'success');
            }
            $need_score = intval($gift['needscore']);
            if(empty($gift)) message('礼品券不存在.', $url, 'success');
            if($need_score > $balance_score)message('该用户积分不足,不能使用.', $url, 'success');
        }else if($type == 3){
            $gift = $this -> get_privilege($pid, $weid);
            if(empty($gift)) message('数据不存在.', $url, 'success');
            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 3);
                if($usetimes[$pid] >= $gift['count']){
                    message('使用次数已经用完.', $url, 'success');
                }
            }
        }else if($type == 2){
            $gift = $this -> get_coupon($pid, $weid);
            if(empty($gift))$this -> showMessage('数据不存在.');
            if($gift['count'] > 0){//判断使用次数
                $usetimes = $this -> get_announce_usetimes($weid, $from_user, 2);
                if($usetimes[$pid] >= $gift['count']){
                    message('使用次数已经用完.', $url, 'success');
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
                $data_announce = array(
                    'weid' => $weid,
                    'giftid' => $gift['id'],
                    'from_user' => $from_user,
                    'type' => $type,
                    'title' => $gift['title'],
                );
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
                //您好，您的会员卡于2013-12-27 11:23:34使用会员卡特权特权一次，本次消费使用现金消费,金额为10000元，获得0个积分。
                $announce_tmp = $type==3?"会员卡特权":"优惠券特权";
                $data_announce['content'] = "您好，您的会员卡于".date('Y-m-d H:i:s',TIMESTAMP)."使用".$announce_tmp."\"".$gift['title']."\"一次,本次消费使用".$paymentstr.",金额为".$money."元,获得".$totalspendscore."个积分。";

                $data_announce['money'] = $money;
                $this -> add_announce($data_announce);
            }
            message('操作成功', $url, 'success');
        }else{
            message('操作失败', $url, 'success');
        }
    }

    public function doShopingLog(){
        global $_GPC, $_W;
        checklogin();
        $action =  'card';
        $weid = $_W['weid'];
        $cardid = intval($_GPC['cardid']);
        $card = pdo_fetch("select * from ".tablename('icard_card')." where id =".$cardid);
        if(empty($card))message('会员卡不存在');

        $title = $card['cardpre'].$card['cardno'];

        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}' and from_user = '{$card['from_user']}'";
        $list = pdo_fetchall("SELECT * FROM ".tablename('icard_money_log')." {$where} order by id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_money_log')." $where");
            $pager = pagination($total, $pindex, $psize);
        }
        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid ORDER BY displayorder DESC,id DESC", array(':weid' => $_W['weid']));
        $outlet = array();
        foreach($outlets as $key => $value){
            $outlet[$value['id']] = $value['title'];
        }
        $gifts_arr = $this -> get_gifts_arr_front(4, $weid);
        $privilege_arr = $this -> get_gifts_arr_front(3, $weid);
        $coupon_arr = $this -> get_gifts_arr_front(2, $weid);

        include $this->template('shopinglog_list');
    }

    //消费日志excel
    public function doShopingLogExcel(){
        global $_GPC, $_W;
        checklogin();
        $weid = $_W['weid'];
        $cardid = intval($_GPC['cardid']);
        $card = pdo_fetch("select * from ".tablename('icard_card')." where id =".$cardid);

        $where = "WHERE from_user = '{$card['from_user']}'";
        $list = pdo_fetchall("SELECT * FROM ".tablename('icard_money_log')." {$where} order by id desc");

        //门店
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid ORDER BY displayorder DESC,id DESC", array(':weid' => $weid));
        $outlet = array();
        foreach($outlets as $key => $value){
            $outlet[$value['id']] = $value['title'];
        }
        $gifts_arr = $this -> get_gifts_arr_front(4, $weid);
        $privilege_arr = $this -> get_gifts_arr_front(3, $weid);
        $coupon_arr = $this -> get_gifts_arr_front(2, $weid);

        $filename = '会员卡'.$card["cardpre"].$card['cardno'].'消费记录_'.date('YmdHis').'.csv';
        $exceler = new Jason_Excel_Export();
        $exceler->charset('UTF-8');
        // 生成excel格式 这里根据后缀名不同而生成不同的格式。jason_excel.csv
        $exceler->setFileName($filename);
        // 设置excel标题行
        $excel_title = array('编号', '名称', '消费类型', '付款方式', '金额', '奖励积分', '操作门店', '消费时间');
        $exceler->setTitle($excel_title);
        // 设置excel内容
        $excel_data = array();
        foreach($list as $key => $value){
            if($value['type'] == 3){
                $name = $privilege_arr[$value['giftid']];
                $type ='特权';
            }else if( $value['type'] == 3){
                $name = $gifts_arr[$value['giftid']];
                $type ='礼品券';
            }else if ($value['type'] == 2){
                $name = $coupon_arr[$value['giftid']];
                $type ='优惠券';
            }else{
                $name = '没有相关数据';
                $type = '没有相关数据';
            }
            $payment = $value == 0?"现金":"余额";
            $shop = empty($outlet[$value["outletid"]])?'后台':$outlet[$value["outletid"]];
            if($value['payment']==0){
                $payment = '现金消费';
            }else{
                $payment = '余额消费';
            }
            $excel_data[] = array($value["id"], $name, $type, $payment, $value['money'], $value['score'], $shop, date("Y-m-d H:i:s",$value["dateline"]));
        }
        $exceler->setContent($excel_data);
        // 生成excel
        $exceler->export();
    }

    public function doRechargeLog(){
        global $_GPC, $_W;
        checklogin();
        $action =  'card';
        $cardid = intval($_GPC['cardid']);
        $card = pdo_fetch("select * from ".tablename('icard_card')." where id =".$cardid);
        $title = $card['cardpre'].$card['cardno'];

        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}' and cardid = '{$cardid}'";
        $list = pdo_fetchall("SELECT * FROM ".tablename('icard_card_log')." {$where} order by id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_card_log')." $where");
            $pager = pagination($total, $pindex, $psize);
        }

        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid ORDER BY displayorder DESC,id DESC", array(':weid' => $_W['weid']));
        $outlet = array();
        foreach($outlets as $key => $value){
            $outlet[$value['id']] = $value['title'];
        }

        include $this->template('rechargelog_list');
    }

    //消费日志excel
    public function doRechargeLogExcel(){
        global $_GPC, $_W;
        checklogin();
        $cardid = intval($_GPC['cardid']);
        $where = "WHERE cardid = '{$cardid}'";
        $list = pdo_fetchall("SELECT * FROM ".tablename('icard_card_log')." {$where} order by id desc");
        $outlets = pdo_fetchall("SELECT * FROM ".tablename('icard_outlet')." WHERE weid = :weid ORDER BY displayorder DESC,id DESC", array(':weid' => $_W['weid']));
        $outlet = array();
        foreach($outlets as $key => $value){
            $outlet[$value['id']] = $value['title'];
        }
        $card = pdo_fetch("select * from ".tablename('icard_card')." where id =".$cardid);

        $filename = '会员卡'.$card["cardpre"].$card['cardno'].'消费记录_'.date('YmdHis').'.csv';
        $exceler = new Jason_Excel_Export();
        $exceler->charset('UTF-8');
        // 生成excel格式 这里根据后缀名不同而生成不同的格式。jason_excel.csv
        $exceler->setFileName($filename);
        // 设置excel标题行
        $excel_title = array('编号', '充值类型', '充值数量', '操作门店', '时间');
        $exceler->setTitle($excel_title);
        // 设置excel内容
        $excel_data = array();
        foreach($list as $key => $value){
            $type = $value == 2?"积分":"金额";
            $shop = empty($outlet[$value["outletid"]])?'后台':$outlet[$value["outletid"]];
            $excel_data[] = array($value["id"], $type, $value["score"], $shop, date("Y-m-d H:i:s",$value["dateline"]));
        }
        $exceler->setContent($excel_data);

        // 生成excel
        $exceler->export();
    }
    //会员卡excel
    public function doCardExcel(){
        global $_GPC, $_W;
        checklogin();

        $cardlist = pdo_fetchall("select * from ".tablename('icard_card')." where weid =:weid", array(':weid' => $_W['weid']));

        $filename = '会员卡_'.date('YmdHis').'.csv';
        $exceler = new Jason_Excel_Export();
        $exceler->charset('UTF-8');
        // 生成excel格式 这里根据后缀名不同而生成不同的格式。jason_excel.csv
        $exceler->setFileName($filename);
        // 设置excel标题行
        $excel_title = array('会员卡号', '姓名', '手机号码', '领卡时间', '余额', '剩余积分', '总积分', '状态');
        $exceler->setTitle($excel_title);
        // 设置excel内容
        $excel_data = array();
        $users = $this -> get_users_arr();
        foreach($cardlist as $key => $value){
            $cardno = $value["cardpre"].$value["cardno"];//卡号
            $username = $users[$value['from_user']]['username'];//姓名
            $tel = $users[$value['from_user']]['tel'];//手机号码
            $date = date('Y-m-d H:i:s',$value['dateline']);//领卡时间
            $coin = $value["coin"];//余额
            $balance_score = $value["balance_score"];//剩余积分
            $total_score = $value["total_score"];//总积分
            $state = $value["state"]==0 ? "正常" : "冻结";//状态
            $excel_data[] = array($cardno,
                $username,
                $tel,
                $date,
                $coin,
                $balance_score,
                $total_score,
                $state
            );
        }
        $exceler->setContent($excel_data);
        // 生成excel
        $exceler->export();
    }

    public function get_action($type){
        switch($type){
            case 4:
                return 'gift';
            case 3:
                return 'privilege';
            case 2:
                return 'coupon';
            default:
                return 'gift';
        }
    }

    public function get_gifts_arr($type){
        global $_W;
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
        $levels = pdo_fetchall("SELECT * FROM ".$tablename." WHERE weid = '{$_W['weid']}' ");
        $arr = array();
        foreach($levels as $key => $value){
            $arr[$value['id']] = $value['title'];
        }
        return $arr;
    }

    public function get_users_arr(){
        global $_W;
        $levels = pdo_fetchall("SELECT * FROM ".tablename('icard_user')." WHERE weid = '{$_W['weid']}' ");
        $arr = array();
        foreach($levels as $key => $value){
            $arr[$value['from_user']]['username'] = $value['username'];
            $arr[$value['from_user']]['tel'] = $value['tel'];
        }
        return $arr;
    }

    public function doCard(){
        global $_W, $_GPC;
        checklogin();
        $action = 'card';
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        $users = $this -> get_users_arr();
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        if(checksubmit('submit')){
            $type = $_GPC["type"];
            $keyword = $_GPC["keyword"];
            switch($type){
                case 'cardno':
                    $where .= " and cardno = '".$keyword."' ";
                    break;
                case 'username':
                    $user = pdo_fetchall(" SELECT from_user FROM ".tablename("icard_user")." where username like '%".$keyword."%' and weid=".$_W['weid']);
                    $arr = array();
                    foreach($user as $key => $value){
                        $arr[] = "'".$value['from_user']."'";
                    }
                    if(!empty($user)){
                        $userstr = implode(',', $arr);
                        $where .= " and from_user in (".$userstr.") ";
                    }
                    //message($where);
                    break;
                case 'tel':
                    $user = pdo_fetchall(" SELECT from_user FROM ".tablename("icard_user")." where tel like '%".$keyword."%' and weid=".$_W['weid']);
                    $arr = array();
                    foreach($user as $key => $value){
                        $arr[] = "'".$value['from_user']."'";
                    }
                    if(!empty($user)){
                        $userstr = implode(',', $arr);
                        $where .= " and from_user in (".$userstr.") ";
                    }
                    break;
            }

            //message($_GPC["type"]);
        }
        $list = pdo_fetchall("SELECT * FROM ".tablename('icard_card')." {$where} order by id desc LIMIT ".($pindex - 1) * $psize.",{$psize}");
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_card')." $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('card_list');
    }

    public function doUserlog(){
        global $_W, $_GPC;
        checklogin();
        $action = 'userlog';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        $weid = intval($_W['weid']);
        //总用户数量
        $user_total_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_card')." WHERE weid = '{$weid}'");
        //今天新增数量
        $user_today_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_card')." WHERE weid = '{$weid}' AND  date_format(from_UNIXTIME(`dateline`),'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')");
        //昨天新增数量
        $user_yesterday_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_card')." WHERE weid = '{$weid}' AND to_days(now())-to_days(`dateline`)<=1");

        //一个月内的会员数据
        $data_user = pdo_fetchall("Select date_format(FROM_UNIXTIME(dateline),'%Y-%m-%d') as date,count(date_format(FROM_UNIXTIME(dateline),'%Y-%m-%d')) as usercount FROM (SELECT * FROM ".tablename('icard_card')." where DATE_SUB(CURDATE(), INTERVAL 1 month) <= date(FROM_UNIXTIME(dateline)) AND weid='{$weid}' ) a Group by date_format(FROM_UNIXTIME(dateline),'%Y-%m-%d')");

        //今天消费次数
        $consume_today_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_money_log')." WHERE weid = '{$weid}' AND  date_format(from_UNIXTIME(`dateline`),'%Y-%m-%d') = date_format(now(),'%Y-%m-%d')");
        //昨天消费次数
        $consume_yesterday_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_money_log')." WHERE weid = '{$weid}' AND to_days(now())-to_days(`dateline`)<=1");
        //总消费次数
        $consume_total_count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_money_log')." WHERE weid = '{$weid}'");
        //一个月内的消费数据
        //$data_money = pdo_fetchall("SELECT * FROM ".tablename('icard_money_log')." where DATE_SUB(CURDATE(), INTERVAL 1 MONTH) <= date(FROM_UNIXTIME(dateline))");
        $data_money = pdo_fetchall("Select date_format(FROM_UNIXTIME(dateline),'%Y-%m-%d') as date,count(date_format(FROM_UNIXTIME(dateline),'%Y-%m-%d')) as moneycount FROM (SELECT * FROM ".tablename('icard_money_log')." where DATE_SUB(CURDATE(), INTERVAL 1 month) <= date(FROM_UNIXTIME(dateline)) AND weid='{$weid}' ) a Group by date_format(FROM_UNIXTIME(dateline),'%Y-%m-%d')");

        $days = array();
        $moneys = array();
        $premonth = date('Y-m-d',strtotime('-1 month'));
        $nowmonth = date('Y-m-d');
        $y = date('Y',strtotime('-2 month'));
        $m = date('m',strtotime('-2 month'));
        $d = date('d',strtotime('-2 month'));

        $usercount = 0;//一个月内用户总数
        $moneycount = 0;//一个月内消费总数
        $count = $this -> count_days(time(), strtotime('-1 month'));
        for($i = 0; $i <= $count; $i++){
            $date = date("Y-m-d", strtotime(' -'. $i . 'day'));
            $days[$date] = '0';
            foreach($data_user as $key => $value){
                if( $date == $value['date']){
                    $days[$date] = $value['usercount'];
                    $usercount = $usercount + $value['usercount'];
                    //message($date.$value['usercount']);
                }
            }
            $moneys[$date] = '0';
            foreach($data_money as $key => $value){
                if( $date == $value['date']){
                    $moneys[$date] = $value['moneycount'];
                    $moneycount = $moneycount + $value['moneycount'];
                }
            }
        }

        $user_str = '';
        $is_first = true;

        foreach(array_reverse($days) as $key => $value){
            if($is_first){
                $user_str .= $value;
                $is_first = false;
            }else{
                $user_str .= ','.$value;
            }
        }

        $money_str = '';
        $is_first = true;
        foreach(array_reverse($moneys) as $key => $value){
            if($is_first){
                $money_str .= $value;
                $is_first = false;
            }else{
                $money_str .= ','.$value;
            }
        }

        include $this->template('userlog');
    }

    function count_days($a, $b){
        $a_dt=getdate($a);
        $b_dt=getdate($b);
        $a_new=mktime(12,0,0,$a_dt['mon'],$a_dt['mday'],$a_dt['year']);
        $b_new=mktime(12,0,0,$b_dt['mon'],$b_dt['mday'],$b_dt['year']);
        return round(abs($a_new-$b_new)/86400);
    }

    public function doCardForm(){
        global $_W, $_GPC;
        checklogin();
        $action = 'card';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        $id = intval($_GPC['id']);
        $reply = pdo_fetch("select * from ".tablename('icard_card')." where id =".$id);
        if(empty($reply)){
            message('非法参数!', $url, 'error');
        }
        $user = pdo_fetch("select * from ".tablename('icard_user')." where from_user ='".$reply['from_user']."'");
        if(empty($user)){
            message($reply['from_user'].'用户不存在!', $url, 'error');
        }
        $level = $this -> get_user_level($reply['weid'], $reply['total_score']);
        if(checksubmit('submit')){
            $data = array();
            $data['username'] = trim($_GPC['username']);
            $data['tel'] = trim($_GPC['tel']);
            $data['address'] = trim($_GPC['address']);
            $data['birthday'] = strtotime($_GPC['birthday']);
            $data['sex'] = intval($_GPC['sex']);
            $data['age'] = intval($_GPC['age']);

            if(istrlen($data['username']) == 0){
                message('没有输入姓名.','','error');
            }
            if(istrlen($data['username']) > 16) {
                message('姓名输入过长.','','error');
            }

            if(!empty($reply)){
                pdo_update('icard_user', $data, array('id' => $user['id']));
            }
            message('操作成功!', $url);
        }
        include $this->template('card_form');
    }
    //会员卡后台审核
    public function doCheckedCardState(){
        global $_GPC, $_W;
        checklogin();
        $action =  'card';
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $cardid = intval($_GPC['id']);
        $state = intval($_GPC['state']);

        pdo_query("UPDATE ".tablename('icard_card')." SET state = abs(:state - 1) WHERE id=:id", array(':state' => $state, ':id' => $cardid));
        message('操作成功!', $url);
    }
	
    public function doCardDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'card';
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']);
        if($id>0){
            pdo_delete('icard_card', array('id' => $id, 'weid' => $_W['weid']));
        }
        message('操作成功!', $url);
    }
	


    public function doAddCardPrice(){
        global $_W, $_GPC;
        checklogin();
        $action = 'card';
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $cardid = intval($_GPC['id']);
        $price = intval($_GPC['price']);
        $state = intval($_GPC['state']);

        if($state == 1){
            message('该帐号已经被冻结，不能充值', $url, 'error');
        }
        //if($price <= 0){
         //   message('输入错误，请重新输入', $url, 'error');
       // }
        if($price > 1000){
            message('每次充值最多1000.', $url, 'error');
        }
        pdo_query("UPDATE ".tablename('icard_card')." SET coin = coin+:price WHERE id=:id", array(':price' => $price, ':id' => $cardid));
        $this -> addCardLog(1, $price, 0,$cardid);
        message('操作成功!', $url);
    }

    public function doAddCardScore(){
        global $_W, $_GPC;
        checklogin();
        $action = 'card';
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $cardid = intval($_GPC['id']);
        $score = intval($_GPC['score']);
        $state = intval($_GPC['state']);
        if($state == 1){
            message('该帐号已经被冻结，不能充值', $url,'error');
        }
       // if($score <= 0){
       //    message('输入错误，请重新输入', $url, 'error');
       // }
        if($score > 1000){
            message('赠送积分每次最多1000.', $url, 'error');
        }
        pdo_query("UPDATE ".tablename('icard_card')." SET balance_score = balance_score+:score,total_score = total_score+:score WHERE id=:id", array(':score' => $score, ':id' => $cardid));
        $this -> addCardLog(2, $score, 0,$cardid);
        message('操作成功!', $url);
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

    //添加入口
    public function doSetRule(){
        global $_W;
        $rule = pdo_fetch("SELECT id FROM ".tablename('rule')." WHERE module = 'icard' AND weid = '{$_W['weid']}' order by id desc");
        if (empty($rule)) {
            header('Location: '.$_W['siteroot'] . create_url('rule/post', array('module' => 'icard')));
            exit;
        } else {
            header('Location: '.$_W['siteroot'] . create_url('rule/post', array('module' => 'icard', 'id' => $rule['id'])));
            exit;
        }
        //0.5
        /*
        $rulename = "{$_W['account']['name']}公众号的会员卡";
        $rule = pdo_fetch("SELECT id FROM ".tablename('rule')." WHERE name = '$rulename' AND weid = '{$_W['weid']}'");
        if (empty($rule)) {
            header('Location: '.$_W['siteroot'] . create_url('rule/post', array('module' => 'icard', 'name' => $rulename)));
            exit;
        } else {
            header('Location: '.$_W['siteroot'] . create_url('rule/post', array('module' => 'icard', 'id' => $rule['id'])));
            exit;
        }*/
    }

    /*
    ** model
    */
    //会员卡
    function icard_style_update($data) {
        global $_W;
        $data['updatetime'] = TIMESTAMP;
        return pdo_update('icard_style', $data, array('weid' => $_W['weid']));
    }

    function icard_style_check() {
        global $_W;
        $card_had = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('icard_style')." WHERE weid = '{$_W['weid']}'");
        if(!$card_had) {
            $data['weid'] = $_W['weid'];
            $data['cardname'] = '会员卡';
            $data['cardnamecolor'] = '#000000';
            $data['cardnumcolor'] = '#000000';
            $data['bg'] = '';
            $data['logo'] = '';
            $data['diybg'] = '';
            $data['dateline'] = $data['updatetime'] = TIMESTAMP;
            pdo_insert('icard_style', $data);
        }
    }
    //商家信息
    function icard_business_insert($data) {
        global $_W;
        $data['weid'] = $_W['weid'];
        $data['dateline'] = TIMESTAMP;
        $data['updatetime'] = TIMESTAMP;
        return pdo_insert('icard_business', $data);
    }
    function icard_business_update($data) {
        global $_W;
        $data['updatetime'] = TIMESTAMP;
        return pdo_update('icard_business', $data, array('weid' => $_W['weid']));
    }
    //积分策略
    function icard_score_insert($data) {
        global $_W;
        $data['weid'] = $_W['weid'];
        $data['dateline'] = TIMESTAMP;
        $data['updatetime'] = TIMESTAMP;
        return pdo_insert('icard_score', $data);
    }
    function icard_score_update($data) {
        global $_W;
        $data['updatetime'] = TIMESTAMP;
        return pdo_update('icard_score', $data, array('weid' => $_W['weid']));
    }
    /*
    ** 设置切换导航
    */
    public function set_tabbar($action) {
        $actions_titles = $this->actions_titles;
        $html = '<ul class="nav nav-tabs">';
        foreach($actions_titles as $key => $value) {
            $url = 'site.php?act=module&do='.$key.'&name='.$this -> modulename;
            $html .= '<li class="'. ($key == $action ? 'active' : '') .'"><a href="'.$url.'">'.$value.'</a></li>';
        }
        $html .= '</ul>';
        return $html;
        /*
         1.在类前加
         public $action = 'style';//默认方法
         public $modulename = 'icard';
         public $actions_titles = array(
          'style' => '会员卡设置',
          'business' => '商家设置',
          'score' => '积分策略',
          'index' => '会员资料管理',
         );
         2.在调用的方法加
         $action = 'business';//方法名
         $title = $this -> actions_titles[$action];
        */
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
    public function doWapTest(){
        //include $this->template('waptest');
        $data = array();
        $data['2012-12-1'] = 0;
        $data['2012-12-2'] = 1;
        $data['2012-12-3'] = 3;
        $data['2012-12-4'] = 4;
        $data['2012-12-5'] = 4;
        $data['2012-12-6'] = 4;
        $data['2012-12-7'] = 4;
        $data['2012-12-8'] = 4;

        for($i = 0; $i<=30; $i++){
            $date = date("Y-m-d", strtotime(' -'. $i . 'day'));
//            $days[$date] = '';
//            foreach($data_user as $key => $value){
//                if( $date == $value['date']){
//                    $days[$date] = $value['usercount'];
//                }
//            }
        }

        $msg = '';
        foreach($data as $key => $value){
            $msg .= $key.':'.$value.'-----<br>';
        }
        message($msg);
    }

}

/**
 * 导出Excel
 *
 * @package:     Jason
 * @subpackage:  Excel
 * @version:     1.0
 */
class Jason_Excel_Export
{
    /**
     * Excel 标题
     *
     * @type: Array
     */
    private $_titles            = array();

    /**
     * Excel 标题数目
     *
     * @type: int
     */
    private $_titles_count      = 0;

    /**
     * Excel 内容
     *
     * @type:  Array
     */
    private $_contents          = array();

    /**
     * Excel 内容数据
     *
     * @type:  Array
     */
    private $_contents_count    = 0;

    /**
     * Excel 文件名
     *
     * @type: string
     */
    private $_fileName  = '';
    private $_split     = "\t";

    private $_charset   = '';

    /**
     * 默认文件名
     *
     * @const :
     */
    const DEFAULT_FILE_NAME = 'jason_excel.xls';


    /**
     * 构造函数..
     *
     * @param    string  param
     * @return   mixed   return
     */
    function __construct($fileName = null)
    {
        if ($fileName !== null)
        {
            $this->_fileName = $fileName;
        }
        else
        {
            $this->setFileName();
        }
    }

    /**
     * 设置生成文件名
     *
     * @param    string  param
     * @return   mixed   Jason_Excel_Export
     */
    public function setFileName($fileName = self::DEFAULT_FILE_NAME)
    {
        $this->_fileName = $fileName;
        $this->setSplite();
        return $this;
    }

    private function _getType()
    {
        return substr($this->_fileName, strrpos($this->_fileName, '.') + 1);
    }

    public function setSplite($split = null)
    {
        if ($split === null)
        {
            switch ($this->_getType())
            {
                case 'xls': $this->_split = "\t"; break;
                case 'csv': $this->_split = ","; break;
            }
        }
        else
            $this->_split = $split;
    }

    /**
     * 设置Excel标题
     *
     * @param    string  param
     * @return   mixed   Jason_Excel_Export
     */
    public function setTitle(&$title = array())
    {
        $this->_titles = $title;
        $this->_titles_count = count($title);
        return $this;
    }

    /**
     * 设置Excel内容
     *
     * @param    string  param
     * @return   mixed   Jason_Excel_Export
     */
    public function setContent(&$content = array())
    {
        $this->_contents          = $content;
        $this->_contents_count    = count($content);
        return $this;
    }

    /**
     * 向excel中添加一行内容
     */
    public function addRow($row = array())
    {
        $this->_contents[] = $row;
        $this->_contents_count++;
        return $this;
    }

    /**
     * 向excel中添加多行内容
     */
    public function addRows($rows = array())
    {
        $this->_contents = array_merge($this->_contents, $rows);
        $this->_contents_count += count($rows);
        return $this;
    }


    /**
     * 数据编码转换
     */
    public function toCode($type = 'GB2312', $from = 'auto')
    {
        foreach ($this->_titles as $k => $title)
        {
            $this->_titles[$k] = mb_convert_encoding($title, $type, $from);
        }

        foreach ($this->_contents as $i => $contents)
        {
            $this->_contents[$i] = $this->_toCodeArr($contents);
        }

        return $this;
    }

    private function _toCodeArr(&$arr = array(), $type = 'GB2312', $from = 'auto')
    {
        foreach ($arr as $k => $val)
        {
            $arr[$k] = mb_convert_encoding($val, $type, $from);
        }

        return $arr;
    }

    public function charset($charset = '')
    {
        if ($charset == '')
            $this->_charset = '';
        else
        {
            $charset = strtoupper($charset);
            switch($charset)
            {
                case 'UTF-8' :
                case 'UTF8' :
                    $this->_charset = ';charset=UTF-8';
                    break;

                default:
                    $this->_charset = ';charset=' . $charset;
            }
        }

        return $this;
    }



    /**
     * 导出Excel
     *
     * @param    string  param
     * @return   mixed   return
     */
    public function export()
    {
        $header = '';
        $data   = array();

        $header = implode($this->_split, $this->_titles);

        for ( $i = 0; $i < $this->_contents_count; $i++ )
        {
            $line_arr   = array();
            foreach ( $this->_contents[$i] as $value )
            {
                if (!isset($value) || $value == "")
                {
                    $value = '""';
                }
                else
                {
                    $value = str_replace('"', '""', $value);
                    $value = '"' . $value . '"';
                }

                $line_arr[] = $value;
            }

            $data[] = implode($this->_split, $line_arr);
        }

        $data = implode("\n", $data);
        $data = str_replace("\r", "", $data);

        if ($data == "")
        {
            $data = "\n(0) Records Found!\n";
        }

        header("Content-type: application/vnd.ms-excel" . $this->_charset);
        header("Content-Disposition: attachment; filename=$this->_fileName");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "\xEF\xBB\xBF".$header . "\n" . $data;
    }
}
