<?php
/**
 * 水果达人抽奖模块
 * 作者:迷失卍国度
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');

class IfruitModule extends WeModule {
	public $name = 'IfruitModule';
	public $title = '水果达人';
	public $ability = '';
	public $tablename = 'ifruit_reply';

    public $action = 'detail';//方法
    public $modulename = 'ifruit';//模块标识
    public $debug = false;//调试
    public $actions_titles = array(
        'actlist' => '活动列表',
        'detail' => '详细设置',
        'award' => '奖品设置',
        'snlist' => 'SN发放管理'
    );

	public function fieldsFormDisplay($rid = 0) {
        global $_W;
        if (!empty($rid)) {
            //回复信息
            $sql_reply = "SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC";
            $reply = pdo_fetch($sql_reply, array(':rid' => $rid));
            $starttime = intval($reply['starttime']) == 0? TIMESTAMP : $reply['starttime'];
            $endtime = intval($reply['endtime']) == 0 ? TIMESTAMP + 86400 * 10 : $reply['endtime'];
        } else {
            $starttime = TIMESTAMP;
            $endtime = TIMESTAMP + 86400 * 10;
        }
		include $this->template('ifruit/form');
	}

	public function fieldsFormValidate($rid = 0) {
		return true;
	}

	public function fieldsFormSubmit($rid = 0) {
		global $_GPC, $_W;
		$id = intval($_GPC['reply_id']);
        $weid = intval($_W['weid']);
        $data = array(
            'rid' => $rid,
            'weid' => $weid,
            'title' => trim($_GPC['title']),
            'title_end' => trim($_GPC['title_end']),
            'description' => trim($_GPC['description']),
            'description_end' => trim($_GPC['description_end']),
            'picture' => trim($_GPC['picture']),
            'picture_end' => trim($_GPC['picture_end']),
            'starttime' => strtotime($_GPC['starttime']),
            'endtime' => strtotime($_GPC['endtime']),
            'dateline' => TIMESTAMP
        );

        if(strlen($data['title']) > 100){
            message('活动名称必须小于100个字符！');
        }
        if(strlen($data['title']) == 0){
            message('请输入活动名称！');
        }
        if(strlen($data['title_end']) > 100){
            message('活动结束主题必须小于100个字符！');
        }
        if(strlen($data['title_end']) == 0){
            message('请输入活动结束主题！');
        }
        if(strlen($data['description']) > 1000){
            message('活动简介必须小于1000个字符！');
        }
        if(strlen($data['description']) == 0){
            message('请输入活动简介！');
        }
        if(strlen($data['description_end']) > 1000){
            message('活动结束描述必须小于1000个字符！');
        }
        if(strlen($data['description_end']) == 0){
            message('请输入活动结束描述！');
        }

        if (empty($id)) {
            pdo_insert($this->tablename, $data);
        } else {
            if (!empty($_GPC['picture'])) {//活动封面
                file_delete($_GPC['picture_old']);
            } else {
                unset($data['picture']);
            }
            if (!empty($_GPC['picture_end'])) {//活动结束封面
                file_delete($_GPC['picture_end_old']);
            } else {
                unset($data['picture_end']);
            }
            pdo_update($this->tablename, $data, array('id' => $id));
        }

        $url = create_url('site/module', array('do' => 'detail', 'name' => $this->modulename, 'rid' => $rid));
        if(empty($id)){
            message('添加成功,请继续配置活动的详细信息！', $url, 'success');
        } else {
            message('编辑成功！', '', 'success');
        }
	}

	public function ruleDeleted($rid = 0) {
		global $_W;
		$replies = pdo_fetchall("SELECT id,picture,picture_end FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
		$deleteid = array();
		if (!empty($replies)) {
			foreach ($replies as $index => $row) {
				file_delete($row['picture']);
                file_delete($row['picture_end']);
				$deleteid[] = $row['id'];
			}
		}
		pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
		return true;
	}

	public function doFormDisplay() {
		global $_W, $_GPC;
	}

    //详细设置
    public function doDetail(){
        global $_GPC, $_W;
        checklogin();
        $action = 'detail';
        $title = $this -> actions_titles[$action];
        $rid = intval($_GPC['rid']);
        $weid = intval($_W['weid']);
        //验证是否存在规则
        $this -> is_have_rule($rid, $weid);
        $reply = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_detail')." WHERE rid={$rid}");
        if(checksubmit()){
            $data = array(
                'rid' => $rid,//规则ID
                'weid' => $weid,//公众号ID
                'rule' => trim($_GPC['rule']),//活动说明
                'pwd' => trim($_GPC['pwd']),//兑换密码
                'ticket_information' => trim($_GPC['ticket_information']),//中奖后页面显示的兑奖信息
                'repeat_lottery_reply' => trim($_GPC['repeat_lottery_reply']),//抽奖后的提示
                'lottery_times' => intval($_GPC['lottery_times']),//每天抽奖次数
                'max_lottery' => intval($_GPC['max_lottery']),//总抽奖次数
                'is_repeat_lottery' => intval($_GPC['is_repeat_lottery']),//是否允许重复中奖
                'show_award_num' => $_GPC['show_award_num'],//是否显示奖品数量
                'status' => 1,//活动状态
                'is_card_score' => intval($_GPC['is_card_score']),//是否启用会员积分兑换
                'card_score' => intval($_GPC['card_score']),//兑换积分
                'copyright' => trim($_GPC['copyright']),
                'dateline' => TIMESTAMP
            );
            if(strlen($data['rule']) == 0){
                message('请输入活动说明！');
            }
            if(strlen($data['rule']) > 1000){
                message('活动说明必须小于1000个字符！');
            }
            if($data['lottery_times'] < 1 || $data['lottery_times'] > 30){
                message('最大抽奖次数必须大于0小于30 推荐只设置1次!','','error');
            }
            if($data['max_lottery'] > $data['lottery_times']){
                message('每天抽奖次数必须小于最大抽奖次数!','','error');
            }
            if($data['is_card_score'] == 1){
                if($data['card_score'] <= 0){
                    message('启用会员卡积分兑换次数,兑换积分必须大于0.');
                }
            }
            if($data['card_score'] > 1000000){
                message('兑换积分过大,请重新输入.');
            }
            if(empty($reply)){
                pdo_insert($this->modulename.'_detail', $data);
            } else {
                pdo_update($this->modulename.'_detail', $data, array('rid' => $rid));
            }
            $url = create_url('site/module', array('do' => 'award', 'name' => $this->modulename, 'rid' => $rid));
            if(empty($reply)){
                message('添加成功,请继续配置活动的相关奖品！', $url, 'success');
            } else {
                message('编辑成功！', '', 'success');
            }
        }
        include $this->template('ifruit/detail');
    }

    public function doAward(){
        global $_GPC, $_W;
        checklogin();
        $action = 'award';
        $rid = intval($_GPC['rid']);
        $weid = intval($_W['weid']);
        $title = $this -> actions_titles[$action];
        $url = create_url('site/module', array('do' => 'award', 'name' => $this->modulename, 'rid' => $rid));;

        if(checksubmit('submit')){
            // 修改
            if(is_array($_GPC['award_title'])) {
                foreach($_GPC['award_title'] as $id => $val) {
                    $title = trim($_GPC['award_title'][$id]);//奖品名称
                    $award_probalilty = trim($_GPC['award_probalilty'][$id]);//中奖率
                    //$award_total = trim($_GPC['award_total'][$id]);//数量
                    $award_level = intval($_GPC['award_level'][$id]);
                    $award_levelname = trim($_GPC['award_levelname'][$id]);//奖品等级类别
                    if(empty($title)){
                        continue;
                    }
                    if($award_level > 6 || $award_level < 1)message('奖品级别必须在1~6之间','','error');
                    $data = array(
                        'rid' => $rid,
                        'weid' => $weid,
                        'title' => $title,
                        'probalilty' => $award_probalilty,
                        'level' => $award_level,
                        'levelname' => $award_levelname,
                    );
                    pdo_update($this->modulename.'_award', $data, array('id' => $id));
                }
            }
            //增加
            if(is_array($_GPC['new_award_title'])) {
                foreach($_GPC['new_award_title'] as $nid => $val) {
                    $title = trim($_GPC['new_award_title'][$nid]);//奖品名称
                    $award_probalilty = trim($_GPC['new_award_probalilty'][$nid]);//中奖率
                    $award_total = intval($_GPC['new_award_total'][$nid]);//数量
                    $award_level = intval($_GPC['new_award_level'][$nid]);
                    $award_levelname = trim($_GPC['new_award_levelname'][$nid]);//奖品等级类别
                    if(empty($title)){
                        continue;
                    }
                    if($award_level > 6 || $award_level < 1)message('奖品级别必须在1~6之间','','error');
                    $data = array(
                        'rid' => $rid,
                        'weid' => $weid,
                        'title' => $title,
                        'probalilty' => $award_probalilty,
                        'total' => $award_total,
                        'level' => $award_level,
                        'levelname' => $award_levelname,
                        'dateline' => TIMESTAMP
                    );
                    pdo_insert($this->modulename.'_award', $data);
                    $awardid = pdo_insertid();
                    for($i = 0;$i < $award_total; $i++){
                        $sn = 'A00'.random(11,1);
                        $insert = array(
                            'rid' => $rid,
                            'weid' => $weid,
                            'awardid' => $awardid,
                            'from_user' => '',
                            'sn' => $this -> get_new_sncode($awardid, $sn),
                            'mobile' => '',
                            'status' => 0,
                            'winningtime' => 0,
                            'usetime' => 0,
                            'dateline' => TIMESTAMP
                        );
                        pdo_insert($this->modulename.'_sn', $insert);
                    }
                }
            }
            message('操作成功.', $url, 'success');
        } else {
            $awards = pdo_fetchall("SELECT * FROM ".tablename($this->modulename.'_award')." WHERE weid = '{$weid}' AND rid='{$rid}' order by level,id desc");
            include $this->template('ifruit/award');
        }
    }

    //删除奖品
    public function doAwardDelete(){
        global $_GPC, $_W;
        checklogin();
        $action = 'award';
        $title = $this -> actions_titles[$action];

        $id = intval($_GPC['id']);
        $rid = intval($_GPC['rid']);
        $weid = $_W['weid'];
        $sql = "SELECT id FROM ".tablename($this->modulename.'_award')." WHERE  weid = :weid AND id=:id";

        $award = pdo_fetch($sql, array(':weid' => $weid, ':id' => $id));
        if(empty($award)){
            message('非法操作,您没该数据的操作权限!');
        }
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename, 'rid' => $rid));
        ////删除奖品
        $flag = pdo_delete($this->modulename.'_award', array('id' => $id, 'weid' => $weid));
        ////删除奖品对应的sn码
        if($flag > 0){
            pdo_delete($this->modulename.'_sn', array('awardid' => $id, 'weid' => $weid));
        }
        message('删除数据成功!', $url);
    }

    public function doActlist() {
        global $_GPC, $_W;
        checklogin();
        $modulename = $this->modulename;
        $action = 'actlist';
        $title = $this -> actions_titles[$action];
        $weid = intval($_W['weid']);
        if (empty($weid))$this -> showMessage('非法参数！', -1);

        $page_index = max(1, intval($_GPC['page']));
        $page_size = 15;
        $where = '';

        $sql = "SELECT * FROM ".tablename($this->modulename.'_detail')." a INNER JOIN ".tablename($this->modulename.'_reply')." b ON a.rid=b.rid INNER JOIN ".tablename('rule_keyword')." c ON a.rid=c.rid  WHERE a.weid='$weid' $where  LIMIT ".($page_index - 1) * $page_size.",{$page_size}";

        $list = pdo_fetchall($sql);
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->modulename.'_detail')." WHERE  weid='$weid' $where");
            $pager = pagination($total, $page_index, $page_size);
        }
        include $this->template('ifruit/actlist');
    }

    public function doSnlist() {
        global $_GPC, $_W;
        checklogin();
        $modulename = $this->modulename;
        $action = 'snlist';
        $title = $this -> actions_titles[$action];
        $rid = intval($_GPC['rid']);
        $weid = intval($_W['weid']);
        if (empty($weid))$this -> showMessage('非法参数！', -1);

        //总数量
        $sn_total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->modulename.'_sn')." WHERE rid = '$rid'  AND weid='$weid'");
        //已消费
        $sn_win_total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->modulename.'_sn')." WHERE rid = '$rid'  AND weid='$weid' AND status=1");
        //已兑换
        $sn_use_total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->modulename.'_sn')." WHERE rid = '$rid'  AND weid='$weid' AND status=2");

        $page_index = max(1, intval($_GPC['page']));
        $page_size = 10;
        $where = '';
        $starttime = !empty($_GPC['starttime']) ? strtotime($_GPC['starttime']) : time()-86400*10;
        $endtime = !empty($_GPC['endtime']) ? strtotime($_GPC['endtime'])+86400-1 : strtotime(date('Y-m-d'))+86400-1;
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
        $condition = array(
            'status' => " AND status = '{$_GPC['status']}'",
            'sn' => " AND sn = '{$_GPC['profilevalue']}'",
            'mobile' => " AND mobile = '{$_GPC['profilevalue']}'",
            'starttime' => " AND dateline >= '$starttime'",
            'endtime' => " AND dateline <= '$endtime'",
        );
        if ($_GPC['status']!='') $where .= $condition['status'];
        if (!empty($_GPC['profile'])) $where .= $condition[$_GPC['profile']];
        if (!empty($starttime)) $where .= $condition['starttime'];
        if (!empty($endtime)) $where .= $condition['endtime'];

        $sql = "SELECT * FROM ".tablename($this->modulename.'_sn')." WHERE rid = '$rid' AND weid='$weid' $where ORDER BY status DESC,dateline DESC LIMIT ".($page_index - 1) * $page_size.",{$page_size}";
        //message($sql);
        $list = pdo_fetchall($sql);
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(1) FROM ".tablename($this->modulename.'_sn')." WHERE rid = '$rid' AND weid='$weid' $where");
            $pager = pagination($total, $page_index, $page_size);
        }
        //增加sn码数量
        $page_sel_sn = '';
        $awards = pdo_fetchall("SELECT * FROM ".tablename($this->modulename.'_award')." WHERE rid = :rid ORDER BY `id` ASC", array(':rid' => $rid));
        foreach($awards as $key => $value){
            $page_sel_sn .= '<option value="'.$value['id'].'">'.$value['title'].'</option>';
        }
        $award_arr = $this -> get_award_arr($rid);
        include $this->template('ifruit/snlist');
    }

    //后台增加sn码
    public function doAddSn(){
        global $_GPC,$_W;
        checklogin();
        $weid = intval($_W['weid']);
        $rid = intval($_GPC['rid']);//规则id
        $awardid = intval($_GPC['awardid']);//奖品id

        if (empty($awardid) || empty($rid))exit('非法参数！');

        //sn码数量
        $sncount = intval($_GPC['sncount']);
        if($sncount<0 || $sncount>100) message("输入的兑换码数量请在1到10之间!");

        $totalcount = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename($this->modulename.'_sn')." where rid={$rid} AND weid={$weid}");

        if($totalcount>2000) message("本次活动您添加的兑换码已经超过限制!");

        //查询奖品是否存在
        $award = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_award')." WHERE rid = '$rid' AND id = '$awardid' AND weid={$weid} limit 1");

        //更新奖品数量
        if(!empty($award)){
            $rowcount = pdo_query("UPDATE ".tablename($this->modulename.'_award')." SET total=total+'$sncount' WHERE rid = '$rid' AND id = '$awardid' AND weid={$weid}");
        }else{
            exit('非法操作');
        }
        //增加sn码数量
        for($i = 0;$i < $sncount; $i++){
            $sn = 'A00'.random(11,1);
            $insert = array(
                'rid' => $rid,
                'weid' => $weid,
                'awardid' => $awardid,
                'from_user' => '',
                'sn' => $this -> get_new_sncode($awardid, $sn),
                'mobile' => '',
                'status' => 0,
                'winningtime' => 0,
                'usetime' => 0,
                'dateline' => TIMESTAMP
            );
            pdo_insert($this->modulename.'_sn', $insert);
        }
        message('操作成功', create_url('site/module', array('do' => 'snlist', 'name' => $this->modulename, 'rid' => $rid)), 'success');
    }

    public function doUpdateSn(){
        global $_GPC,$_W;
        checklogin();
        $rid = intval($_GPC['rid']);
        $id = intval($_GPC['id']);//snid
        $weid = intval($_W['weid']);
        $status = intval($_GPC['status']);
        if (empty($id) || empty($weid) || empty($status))exit('非法参数！');

        $sn = pdo_fetch("SELECT * FROM ".tablename($this->modulename.'_sn')." WHERE id = {$id} AND weid={$weid} limit 1");
        $snstate = 0;
        if(!empty($sn)){
            $snstate = $sn['status'];
        }else{
            exit('非法操作！');
        }

        $strwhere = " WHERE id={$id} ";
        $fields = " status={$status} ";

        if($status == 0){
            exit('非法参数！');
        }elseif($status == 1){//已抽中
            $strwhere .= " and winningtime=0 ";
            $fields .= ",winningtime=".TIMESTAMP;
        }elseif($status == 2){//已兑换
            $fields .= ",usetime=".TIMESTAMP;
        }
        $rowcount = pdo_query("UPDATE ".tablename($this->modulename.'_sn')." SET {$fields} {$strwhere}");
        if($rowcount > 0){
            if($snstate == 0){//未抽奖过的sn码 减去奖品数量1
                pdo_query("UPDATE ".tablename($this->modulename.'_award')." SET total=total-1 WHERE id = ".$sn['awardid']);
            }
        }


        $url = create_url('site/module', array('do' => 'snlist', 'name' => $this->modulename, 'rid' => $rid));
        message('操作成功', $url, 'success');
    }

    //我的中奖记录
    public function show_my_record($weid, $rid, $from_user){
        $sql = "SELECT *,a.id as snid FROM ".tablename($this->modulename.'_sn')." a inner join ".tablename($this->modulename.'_award')." b  ON a.awardid=b.id   WHERE a.from_user = '{$from_user}' AND a.weid={$weid} AND a.rid='{$rid}' AND (a.status=1 or a.status=2) ORDER BY winningtime";
        $awards = pdo_fetchall($sql);
        return $awards;
    }

    /*
    ** 设置切换导航
    */
    public function set_tabbar($action, $rid){
        $actions_titles = $this->actions_titles;
        $html = '<ul class="nav nav-tabs">';
        foreach($actions_titles as $key => $value) {
            $url = 'site.php?act=module&do='.$key.'&name='.$this -> modulename.'&rid='.$rid;
            $html .= '<li class="'. ($key == $action ? 'active' : '') .'"><a href="'.$url.'">'.$value.'</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

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
    //admin
    public function get_award_arr($rid){
        $awards = pdo_fetchall("SELECT * FROM ".tablename($this->modulename."_award")." WHERE rid = '{$rid}' ");
        $arr = array();
        foreach($awards as $key => $value){
            $arr[$value['id']] = $value['title'];
        }
        return $arr;
    }

    //admin sn码
    public function get_new_sncode($awardid, $sn){
        $sql = "SELECT sn FROM ".tablename($this->modulename.'_sn')." WHERE awardid=:awardid and sn = :sn ORDER BY `id` DESC limit 1";
        $object = pdo_fetch($sql, array(':awardid' => $awardid, ':sn' => $sn));
        if(!empty($object)) {
            $sn = 'A00'.random(11, 1);
            $this -> get_new_sncode($awardid, $sn);
        }
        return $sn;
    }
}