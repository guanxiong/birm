<?php

defined('IN_IA') or exit('Access Denied');
include "model.php";
class VoteModuleSite extends WeModuleSite {

    public $tablename = 'vote_reply';
    public $tablefans = 'vote_fans';

   public function getItemTiles() {
        global $_W;
        $articles = pdo_fetchall("SELECT id,rid, title FROM " . tablename('vote_reply') . " WHERE weid = '{$_W['weid']}'");
        if (!empty($articles)) {
            foreach ($articles as $row) {
                $urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('index', array('id' => $row['rid'])));
            }
            return $urls;
        }
    }
    
    public function doMobileindex() {
        global $_GPC, $_W;
        $rid = $_GPC['id'];
        $weid = $_W['weid'];

        if (empty($rid)) {
            message('抱歉，参数错误！', '', 'error');
        }
        $from_user = $_GPC['from_user'];
        $reply = pdo_fetch("SELECT * FROM " . tablename('vote_reply') . " WHERE `rid`=:rid LIMIT 1", array(':rid' => $rid));
        if ($reply == false) {
            message('活动已经取消了！', '', 'error');
        }
        $nowtime = time();
        $endtime = $reply['endtime'] + 86399;
        if ($reply['status'] == 0) {
            message('投票已经暂停！', '', 'error');
        }

        if ($reply['votelimit'] == 1) {
            if ($reply['votenum'] >= $reply['votetotal']) {

                message('投票人数已满！', '', 'error');
            }
        } else {
            if ($reply['starttime'] > $nowtime) {
                message('投票未开始！', '', 'error');
            } elseif ($endtime < $nowtime) {
                message('投票已经结束！', '', 'error');
            } else {
//                if ($reply['status'] == 1) {
//
//                } else {
//                    message('投票已经暂停！', '', 'error');
//                }
            }
        }

        if (empty($_W['fans']) || $_GPC['share'] == 1) {
            //301跳转
            if (!empty($reply['share_url'])) {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: " . $reply['share_url'] . "");
                exit();
            }
            $isshare = 1;
            $running = false;
            $msg = '请先关注公共号。';
        } else {
            $isshare = 0;
        }

        $limits = "";
        if ($reply['votelimit'] == 1) {
            $limits = "参数人数 " . $reply['votenum'] . " /  允许总数 " . $reply['votetotal'];
        } else {
            $limits = "投票期限: " . date('Y-m-d H:i', $reply['starttime']) . " 至 " . date('Y-m-d H:i', $endtime);
        }
        $selects = "";
        if ($reply['votetype'] == 0) {
            $selects = "最多选择一项";
        } else {
            $selects = "可以选择多项";
        }
        //判断有没有投票过
        $votetimes = pdo_fetch("SELECT count(*) as cnt FROM " . tablename('vote_fans') . "where rid=" . $rid . " and from_user='" . $_W['fans']['from_user'] . "'");
        $votetimes =$votetimes['cnt'];
       
        $isvote = $votetimes>0;

        if(empty($_GPC['search'])){
            $list = pdo_fetchall("SELECT * FROM " . tablename('vote_option') . " WHERE rid = :rid ORDER by `id` ASC", array(':rid' => $rid));
        }else{
            $list = pdo_fetchall("SELECT * FROM " . tablename('vote_option') . " WHERE rid = :rid and `title` like :search ORDER by `id` ASC", array(':rid' => $rid,':search' => '%'.$_GPC['search'].'%'));
        }
       

        $sumnum = pdo_fetch("SELECT sum(vote_num) FROM " . tablename('vote_option') . " WHERE rid = :rid ", array(':rid' => $rid));
        $sumnum = $sumnum["sum(vote_num)"];
         foreach ($list as &$r) {
            if ($sumnum == 0) {
                $r['percent'] = 0;
            } else {
                $r['percent'] = floor($r['vote_num']  / $sumnum * 100);
            }
        }
        unset($r);
    
        //判断粉丝是否要继续投票
        $can =true;
        if($reply['votetimes']>0){
            if($votetimes>=$reply['votetimes']){
               $can =false;    
            }
        }
        
        $canvotetimes =intval( $reply['votetimes'] - $votetimes);

        //分享信息
        $sharelink = empty($reply['share_url']) ? ($_W['siteroot'] . $this->createMobileUrl('index', array('id' => $rid, 'name' => 'vote', 'share' => 1))) : $reply['share_url'];
        $sharetitle = empty($reply['share_title']) ? '欢迎参加投票活动' : $reply['share_title'];
        $sharedesc = empty($reply['share_desc']) ? '亲，欢迎参加投票活动！' : $reply['share_desc'];
        //$shareimg = $_W['siteroot'] . trim($reply['start_picurl'], '/');
        $shareimg = img_url($reply['thumb']);

        if( $can )  {
            pdo_fetch("UPDATE " . tablename('vote_reply') . " SET viewnum = (viewnum + 1) WHERE rid = :rid AND weid = :weid", array(':rid' => $rid, ':weid' => $weid));

            include $this->template('vote-content');
        }
        else{
             include $this->template('vote-end');
        }
       
    }

    function doMobilesubmit() {
        global $_GPC, $_W;
        //判断用户是否存在
        $rid = $_GPC['id'];
        $from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');

        if (empty($rid)) {
            die("参数错误!");
        }
        $reply = pdo_fetch("SELECT * FROM " . tablename($this->tablename) . " WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
        if (!$reply) {
            die("参数错误!");
        }

        if ($reply['status'] == 0) {
            die("投票已经暂停!");
        }

        $nowtime = time();
        $endtime = $reply['endtime'] + 86399;

        if ($reply['votelimit'] == 1) {
            if ($reply['votenum'] >= $reply['votetotal']) {
                die("投票人数已满!");
            }
        } else {
            if ($reply['starttime'] > $nowtime) {
                die("投票未开始!");
            } elseif ($endtime < $nowtime) {

                die("投票已经结束!");
            } else {
//                if ($reply['status'] == 1) {
//
//                } else {
//                    die("投票已经暂停!");
//                }
            }
        }
        //print_r($reply);exit;

            //判断用户投票次数
            $vc =  pdo_fetch("select count(*) as cnt from ".tablename('vote_fans')." where from_user=:from_user and rid=:rid",array(":from_user"=>$from_user,":rid"=>$rid));
            if($reply['votetimes']>0 && $vc['cnt']>=$reply['votetimes']) {
                //今天已经投票过了
                die('您已经超过投票次数了!');
                
            } else {

                $ids = $_GPC['ids'];
                if(empty($ids)){
                       die("参数错误!");
                }
                //粉丝投票次数
                pdo_insert('vote_fans', array('from_user'=>$from_user,'rid'=>$rid, 'votes' => $ids,'votetime'=>time()));
                //参与人数
                pdo_update('vote_reply', array('votenum' => ($reply['votenum'] + 1)), array('rid' =>$rid));
                //投票记录
                $item_ids = explode(",",$ids);
                foreach($item_ids as $item_id){
                     //查找投票项是否存在
                    $vote = pdo_fetch("SELECT * FROM " . tablename('vote_option') . " WHERE rid = :rid and id=" . $item_id . " ORDER by `id` ASC", array(':rid' => $rid));
                    if($vote){
                        pdo_update('vote_option', array('vote_num' => ($vote['vote_num'] + 1)), array('id' =>$item_id));
                    }
                }
                die('');
            }
     
    }
    
     public function doMobileresult() {
        global $_GPC, $_W;

        $rid = $_GPC['id'];
        if (empty($rid)) {
            message('抱歉，参数错误！', '', 'error');
        }
        $from_user = $_GPC['from_user'];
        $reply = pdo_fetch("SELECT * FROM " . tablename('vote_reply') . " WHERE `rid`=:rid LIMIT 1", array(':rid' => $rid));
        if ($reply == false) {
            message('活动已经取消了！', '', 'error');
        }
       
        $limits = "";
        if ($reply['votelimit'] == 1) {
            $limits = "参数人数 " . $reply['votenum'] . " /  允许总数 " . $reply['votetotal'];
        } else {
            $endtime = $reply['endtime'] + 86399;
            $limits = "投票期限: " . date('Y-m-d H:i', $reply['starttime']) . " 至 " . date('Y-m-d H:i', $endtime);
        }
        $selects = "";
        if ($reply['votetype'] == 0) {
            $selects = "最多选择一项";
        } else {
            $selects = "可以选择多项";
        }
        //判断有没有投票过
        $votetimes = pdo_fetchcolumn("SELECT count(*) as cnt FROM " . tablename('vote_fans') . "where rid=" . $rid . " and from_user='" . authcode(base64_decode($_GPC['from_user']), 'DECODE') . "'");
      
        $list = pdo_fetchall("SELECT * FROM " . tablename('vote_option') . " WHERE rid = :rid ORDER by `id` ASC", array(':rid' => $rid));
        $sumnum = pdo_fetch("SELECT sum(vote_num) FROM " . tablename('vote_option') . " WHERE rid = :rid ", array(':rid' => $rid));
        $sumnum = $sumnum["sum(vote_num)"];
  
        foreach ($list as &$r) {
                  
        
            if ($sumnum == 0) {
                $r['percent'] = 0;
            } else {
                $r['percent'] = floor($r['vote_num'] * 100 / $sumnum);
            }
        }
        unset($r);
		 //分享信息
        $sharelink = empty($reply['share_url']) ? ($_W['siteroot'] . $this->createMobileUrl('index', array('id' => $rid, 'name' => 'vote', 'share' => 1))) : $reply['share_url'];
        $sharetitle = empty($reply['share_title']) ? '欢迎参加投票活动' : $reply['share_title'];
        $sharedesc = empty($reply['share_desc']) ? '亲，欢迎参加投票活动！' : $reply['share_desc'];
        //$shareimg = $_W['siteroot'] . trim($reply['start_picurl'], '/');
        $shareimg = img_url($reply['thumb']);
        include $this->template('vote-end');
    }

}
