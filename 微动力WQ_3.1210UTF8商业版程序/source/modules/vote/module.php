<?php

/**
 * 投票系统
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');
include "model.php";
class VoteModule extends WeModule {

    public $name = 'Vote';
    public $title = '投票系统';
    public $ability = '';
    public $tablename = 'vote_reply';

    public function fieldsFormDisplay($rid = 0) {
        global $_W;
        if (!empty($rid)) {
            $reply = pdo_fetch("SELECT * FROM " . tablename($this->tablename) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
            $options = pdo_fetchall("select * from ".tablename('vote_option')." where rid=:rid order by id asc", array(':rid' => $rid));
           
        }
        if (!$reply) {
            $now = time();
            $reply = array(
                "starttime" => $now,
                "endtime" => strtotime(date("Y-m-d H:i", $now + 7 * 24 * 3600)),
                "share_title" => "欢迎参加投票活动",
                "share_desc" => "亲，欢迎参加投票活动！ 亲，需要绑定账号才可以参加哦",
                "share_txt" => "&lt;p&gt;1. 关注微信公众账号\"()\"&lt;/p&gt;&lt;p&gt;2. 发送消息\"投票\", 点击返回的消息即可参加&lt;/p&gt;",
            );
        }
        include $this->template('form');
    }

    public function fieldsFormValidate($rid = 0) {

        return true;
    }

    public function fieldsFormSubmit($rid = 0) {
        global $_GPC, $_W;
        $id = intval($_GPC['reply_id']);
         $insert = array(
            'rid' => $rid,
            'weid' => $_W['weid'],
            'title' => $_GPC['title'],
            'description' => $_GPC['description'],
            'votetype' => $_GPC['votetype'],
            'votelimit' => $_GPC['votelimit'],
            'votetimes' => $_GPC['votetimes'],
            'votetotal' => $_GPC['votetotal'],
            'isimg' => $_GPC['isimg'],
            'share_title' => $_GPC['share_title'],
            'share_desc' => $_GPC['share_desc'],
            'share_url' => $_GPC['share_url'],
            'share_txt' => $_GPC['share_txt'],
            'starttime' => strtotime($_GPC['datelimit-start']),
            'endtime' => strtotime($_GPC['datelimit-end'])
        );

        if (!empty($_GPC['thumb'])) {
            $insert['thumb'] = $_GPC['thumb'];
            file_delete($_GPC['thumb-old']);
        }

        if (empty($id)) {
            if ($insert['starttime'] <= time()) {
                $insert['isshow'] = 1;
            } else {
                $insert['isshow'] = 0;
            }
            $id = pdo_insert($this->tablename, $insert);
        } else {
            pdo_update($this->tablename, $insert, array('id' => $id));
        }  
              
        $options = array();
        $option_ids = $_POST['option_id'];
        $option_titles = $_POST['option_title'];
        $option_thumb_olds = $_POST['option_thumb_old'];
        $files =$_FILES;
        $len = count($option_ids);
        $ids = array();
        
        for ($i = 0; $i < $len; $i++) {
             $item_id  = $option_ids[$i];
             $a = array(
                 "title"=>$option_titles[$i],
                 "rid"=>$rid
             );
             
             $f = 'option_thumb_'.$item_id;
             $old = $_GPC['option_thumb_'.$item_id];
      
             if (!empty($files[$f]['tmp_name'])) {
                    
                        $upload = file_upload($files[$f]);
                        if (is_error($upload)) {
                             message($upload['message'], '', 'error');
                        }
                       $a['thumb'] = $upload['path'];
            
                    }else if(!empty($old)){
                        $a['thumb'] = $old;
                    }
               if((int)$item_id==0){
                    pdo_insert("vote_option", $a);
                    $item_id = pdo_insertid();
                } else {
                    pdo_update("vote_option", $a, array('id' => $item_id));
                }  
                $ids[] = $item_id;
         }
         if(!empty($ids)){
            pdo_query("delete from ".tablename('vote_option')." where id not in ( ".implode(',',$ids).") and rid = ".$rid);    
         }
        return true;
    }

    public function ruleDeleted($rid = 0) {
        pdo_delete('vote_reply', array('rid' => $rid));
        pdo_delete('vote_fans', array('rid' => $rid));
        pdo_delete('vote_option', array('rid' => $rid));
        return true;
    }
    public function doitem(){
        $tag = random(32);
        global $_GPC;
        $type = $_GPC['type'];
        include $this->template('item');
    }
    public function doManage() {
        global $_GPC, $_W;
        include model('rule');
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "weid = :weid AND `module` = :module";
        $params = array();
        $params[':weid'] = $_W['weid'];
        $params[':module'] = 'vote';

        if (isset($_GPC['keywords'])) {
            $sql .= ' AND `name` LIKE :keywords';
            $params[':keywords'] = "%{$_GPC['keywords']}%";
        }
        $list = rule_search($sql, $params, $pindex, $psize, $total);
        $pager = pagination($total, $pindex, $psize);

        if (!empty($list)) {
            foreach ($list as &$item) {
                $condition = "`rid`={$item['id']}";
                $item['keywords'] = rule_keywords_search($condition);
                $vote = pdo_fetch("SELECT title,votenum,votetimes,votelimit,votetotal,viewnum,starttime,endtime,status FROM " . tablename('vote_reply') . " WHERE rid = :rid ", array(':rid' => $item['id']));
                $item['title'] = $vote['title'];
                $item['votenum'] = $vote['votenum'];
                $item['votetimes'] = $vote['votetimes'];
                $item['viewnum'] = $vote['viewnum'];
                $item['starttime'] = date('Y-m-d H:i', $vote['starttime']);
                $endtime = $vote['endtime'] + 86399;
                $item['endtime'] = date('Y-m-d H:i', $endtime);

                $limits = "";
                if ($vote['votelimit'] == 1) {
                    $limits = "允许投票 " . $vote['votetotal'] . " 人";
                } else {
                    $limits = "投票期限: " . date('Y-m-d H:i', $vote['starttime']) . " 至 " . date('Y-m-d H:i', $endtime);
                }
                $item['limits'] = $limits;

                $nowtime = time();
                if($item['votelimit']==1){
                  if ($item['votetotal']>0 && $item['votenum']>=$item['votetotal']) {
                        $item['status'] = '<span class="label label-blue">已结束</span>';
                        $item['show'] = 0;
                    } else  {
                        $item['status'] = '<span class="label label-satgreen">已开始</span>';
                        $item['show'] = 2;
                 } 
        }else {
                if ($vote['starttime'] > $nowtime) {
                    $item['status'] = '<span class="label label-red">未开始</span>';
                    $item['show'] = 1;
                } elseif (($vote['endtime'] + 86399) < $nowtime) {
                    $item['status'] = '<span class="label label-blue">已结束</span>';
                    $item['show'] = 0;
                } else {
                    if ($vote['status'] == 1) {
                        $item['status'] = '<span class="label label-satgreen">已开始</span>';
                        $item['show'] = 2;
                    } else {
                        $item['status'] = '<span class="label ">已暂停</span>';
                        $item['show'] = 1;
                    }
                }
            } }
        }
        include $this->template('manage');
    }

    public function dodelete() {
        global $_GPC, $_W;
        $rid = intval($_GPC['rid']);
        $rule = pdo_fetch("SELECT id, module FROM " . tablename('rule') . " WHERE id = :id and weid=:weid", array(':id' => $rid, ':weid' => $_W['weid']));
        if (empty($rule)) {
            message('抱歉，要修改的规则不存在或是已经被删除！');
        }
        if (pdo_delete('rule', array('id' => $rid))) {
            pdo_delete('rule_keyword', array('rid' => $rid));
            //删除统计相关数据
            pdo_delete('stat_rule', array('rid' => $rid));
            pdo_delete('stat_keyword', array('rid' => $rid));
            //调用模块中的删除
            $module = WeUtility::createModule($rule['module']);
            if (method_exists($module, 'ruleDeleted')) {
                $module->ruleDeleted($rid);
            }
        }


        message('规则操作成功！', create_url('site/module/manage', array('name' => 'vote')), 'success');
    }

    public function dodeleteAll() {
        global $_GPC, $_W;

        foreach ($_GPC['idArr'] as $k => $rid) {
            $rid = intval($rid);
            if ($rid == 0)
                continue;
            $rule = pdo_fetch("SELECT id, module FROM " . tablename('rule') . " WHERE id = :id and weid=:weid", array(':id' => $rid, ':weid' => $_W['weid']));
            if (empty($rule)) {
                $this->message('抱歉，要修改的规则不存在或是已经被删除！');
            }
            if (pdo_delete('rule', array('id' => $rid))) {
                pdo_delete('rule_keyword', array('rid' => $rid));
                //删除统计相关数据
                pdo_delete('stat_rule', array('rid' => $rid));
                pdo_delete('stat_keyword', array('rid' => $rid));
                //调用模块中的删除
                $module = WeUtility::createModule($rule['module']);
                if (method_exists($module, 'ruleDeleted')) {
                    $module->ruleDeleted($rid);
                }
            }
        }
        $this->message('规则操作成功！', '', 0);
    }

    public function dostatus($rid = 0) {
        global $_GPC;
        $rid = $_GPC['rid'];
        $insert = array(
            'status' => $_GPC['status']
        );

        pdo_update($this->tablename, $insert, array('rid' => $rid));
        message('模块操作成功！', referer(), 'success');
    }

    public function doresult() {
        global $_GPC;
        $rid = $_GPC['id'];
        $list = pdo_fetchall("SELECT * FROM " . tablename('vote_option') . " WHERE rid = :rid ORDER by `id` asc", array(':rid' => $rid));
        foreach ($list as $v) {
            echo '<b>'.$v['title'] . '</b><br>选票:<span style="color:#ff6600;font-weight:bold">' . $v['vote_num'] . '</span><br>';
        }
    }
  //投票记录
    public function dovotelist() {
        global $_W;

        checklogin();
        checkaccount();
        $list = pdo_fetchall("select from_user,votes,votetime from " . tablename('vote_fans') . "  order by votetime desc");
        foreach($list as &$r)
        {
            $votes = "";
            $options = pdo_fetchall("select title from ".tablename('vote_option')." where id in (".$r['votes'].")");
            foreach($options as $o){
                $votes.=mb_substr($o['title'],0,10,"utf-8")."<br/>";
            }
            $r['votes'] = $votes;
        }
        unset($r);
        include $this->template('list');
    }
    public function message($error, $url = '', $errno = -1) {
        $data = array();
        $data['errno'] = $errno;
        if (!empty($url)) {
            $data['url'] = $url;
        }
        $data['error'] = $error;
        echo json_encode($data);
        exit;
    }

}
