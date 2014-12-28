<?php

/**
 * 砸蛋抽奖模块
 *
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 */
defined('IN_IA') or exit('Access Denied');

class EggModuleSite extends WeModuleSite {

    public function doWebFormDisplay() {
        global $_W, $_GPC;
        $result = array('error' => 0, 'message' => '', 'content' => '');
        $result['content']['id'] = $GLOBALS['id'] = 'add-row-news-' . $_W['timestamp'];
        $result['content']['html'] = $this->template('item', TEMPLATE_FETCH);
        exit(json_encode($result));
    }

    public function doWebAwardlist() {
        global $_GPC, $_W;
        checklogin();
        $id = intval($_GPC['id']);
        if (checksubmit('delete')) {
            pdo_delete('egg_winner', " id  IN  ('" . implode("','", $_GPC['select']) . "')");
            message('删除成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
        }
        if (!empty($_GPC['wid'])) {
            $wid = intval($_GPC['wid']);
            pdo_update('egg_winner', array('status' => intval($_GPC['status'])), array('id' => $wid));
            message('操作成功！', $this->createWebUrl('awardlist', array('id' => $id, 'page' => $_GPC['page'])));
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 50;
        $where = '';
        $starttime = !empty($_GPC['start']) ? strtotime($_GPC['start']) : TIMESTAMP;
        $endtime = !empty($_GPC['start']) ? strtotime($_GPC['end']) : TIMESTAMP;
        if (!empty($starttime) && $starttime == $endtime) {
            $endtime = $endtime + 86400 - 1;
        }
        $condition = array(
            'isregister' => array(
                '',
                " AND b.realname <> ''",
                " AND b.realname = ''",
            ),
            'isaward' => array(
                '',
                " AND a.award <> ''",
                " AND a.award = ''",
            ),
            'qq' => " AND b.qq ='{$_GPC['profilevalue']}'",
            'mobile' => " AND b.mobile ='{$_GPC['profilevalue']}'",
            'realname' => " AND b.realname ='{$_GPC['profilevalue']}'",
            'title' => " AND a.award = '{$_GPC['awardvalue']}'",
            'description' => " AND a.description = '{$_GPC['awardvalue']}'",
            'starttime' => " AND a.createtime >= '$starttime'",
            'endtime' => " AND a.createtime <= '$endtime'",
        );
        if (!isset($_GPC['isregister'])) {
            $_GPC['isregister'] = 1;
        }
        $where .= $condition['isregister'][$_GPC['isregister']];
        if (!isset($_GPC['isaward'])) {
            $_GPC['isaward'] = 1;
        }
        $where .= $condition['isaward'][$_GPC['isaward']];
        if (!empty($_GPC['profile'])) {
            $where .= $condition[$_GPC['profile']];
        }
        if (!empty($_GPC['award'])) {
            $where .= $condition[$_GPC['award']];
        }
        if (!empty($starttime)) {
            $where .= $condition['starttime'];
        }
        if (!empty($endtime)) {
            $where .= $condition['endtime'];
        }
        $sql = "SELECT a.id, a.award, a.description, a.status, a.createtime, b.realname, b.mobile, b.qq FROM " . tablename('egg_winner') . " AS a
				LEFT JOIN " . tablename('fans') . " AS b ON a.from_user = b.from_user WHERE a.rid = '$id' AND a.award <> '' $where ORDER BY a.createtime DESC, a.status ASC LIMIT " . ($pindex - 1) * $psize . ",{$psize}";
        $list = pdo_fetchall($sql);
        if (!empty($list)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('egg_winner') . " AS a
				LEFT JOIN " . tablename('fans') . " AS b ON a.from_user = b.from_user WHERE a.rid = '$id' $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('awardlist');
    }

    public function doWebDelete() {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        $sql = "SELECT id FROM " . tablename('egg_award') . " WHERE `id`=:id";
        $row = pdo_fetch($sql, array(':id' => $id));
        if (empty($row)) {
            message('抱歉，奖品不存在或是已经被删除！', '', 'error');
        }
        if (pdo_delete('egg_award', array('id' => $id))) {
            message('删除奖品成功', '', 'success');
        }
    }

    public function getCovers() {
        return array(
            array('title' => '第一期砸蛋', 'url' => $this->createWebUrl('first')),
        );
    }

    public function getHomeTiles() {
        global $_W;
        $urls = array();
        $list = pdo_fetchall("SELECT name, id FROM " . tablename('rule') . " WHERE weid = '{$_W['weid']}' AND module = 'egg'");
        if (!empty($list)) {
            foreach ($list as $row) {
                $urls[] = array('title' => $row['name'], 'url' => $this->createMobileUrl('lottery', array('id' => $row['id'])));
            }
        }
        return $urls;
    }

    public function doMobileLottery() {
        global $_GPC, $_W;
        $title = '砸蛋抽奖';
        if (empty($_W['fans']['from_user'])) {
            message('非法访问，请重新发送消息进入砸蛋页面！');
        }
        $fromuser = $_W['fans']['from_user'];
        $profile = fans_require($fromuser, array('realname', 'mobile', 'qq'), '需要完善资料后才能砸蛋.');
        $id = intval($_GPC['id']);
        $egg = pdo_fetch("SELECT id, maxlottery, default_tips, rule FROM " . tablename('egg_reply') . " WHERE rid = '$id' LIMIT 1");
        if (empty($egg)) {
            message('非法访问，请重新发送消息进入砸蛋页面！');
        }
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('egg_winner') . " WHERE createtime > '" . strtotime(date('Y-m-d')) . "' AND from_user = '$fromuser' AND status <> 3 AND award <> ''");
        $member = fans_search($fromuser);
        $myaward = pdo_fetchall("SELECT award, description FROM " . tablename('egg_winner') . " WHERE from_user = '{$fromuser}' AND award <> '' AND rid = '$id' ORDER BY createtime DESC");

        $sql = "SELECT a.award, b.realname FROM " . tablename('egg_winner') . " AS a
				LEFT JOIN " . tablename('fans') . " AS b ON a.from_user = b.from_user WHERE b.mobile <> '' AND b.realname <> '' AND a.award <> '' AND a.rid = '$id' ORDER BY a.createtime DESC LIMIT 20";
        $otheraward = pdo_fetchall($sql);
        include $this->template('lottery');
    }

    public function doMobileGetAward() {
        global $_GPC, $_W;
        if (empty($_W['fans']['from_user'])) {
            message('非法访问，请重新发送消息进入砸蛋页面！');
        }
        $fromuser = $_W['fans']['from_user'];
        $id = intval($_GPC['id']);
        $egg = pdo_fetch("SELECT id, periodlottery, maxlottery, default_tips, misscredit, hitcredit FROM " . tablename('egg_reply') . " WHERE rid = '$id' LIMIT 1");
        if (empty($egg)) {
            message('非法访问，请重新发送消息进入砸蛋页面！');
        }
        $result = array('status' => -1, 'message' => '');
        if (!empty($egg['periodlottery'])) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('egg_winner') . " WHERE createtime > '" . (strtotime(date('Y-m-d')) - 86400 * $egg['periodlottery']) . "' AND from_user = '$fromuser' AND status <> 3 AND rid = '$id'");
            $lastdate = pdo_fetchcolumn("SELECT createtime FROM " . tablename('egg_winner') . " WHERE from_user = '$fromuser' AND status <> 3 ORDER BY createtime DESC");
            if (($total >= intval($egg['maxlottery'])) && strtotime(date('Y-m-d')) < strtotime(date('Y-m-d', $lastdate)) + $egg['periodlottery'] * 86400) {
                $result['message'] = '您还未到达可以再次砸蛋的时间。下次可砸时间为' . date('Y-m-d', strtotime(date('Y-m-d', $lastdate)) + $egg['periodlottery'] * 86400);
                message($result, '', 'ajax');
            }
        } else {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('egg_winner') . " WHERE createtime > '" . strtotime(date('Y-m-d')) . "' AND from_user = '$fromuser' AND status <> 3");
            if (!empty($egg['maxlottery']) && $total >= $egg['maxlottery']) {
                $result['message'] = $egg['periodlottery'] ? '您已经超过当日砸蛋次数' : '您已经超过最大砸蛋次数';
                message($result, '', 'ajax');
            }
        }
        $gifts = pdo_fetchall("SELECT id, probalilty,inkind,total FROM " . tablename('egg_award') . " WHERE rid = '$id' ORDER BY probalilty ASC");
          //计算每个礼物的概率
        $probability = 0;
        $rate = 1;
        $award = array();
        $awardids = array(); //奖品ID (同时可中多个奖品，然后随机派奖)
        foreach ($gifts as $name => $gift) {
            if (empty($gift['probalilty'])) {
                continue;
            }
            $probability = $gift['probalilty'];
            if ($probability< 1) {
                $temp = explode('.', $probability);
                $temp = pow(10, strlen($temp[1]));
                $rate = $temp < $rate ? $rate : $temp;
                $probability = $probability * $rate;
            }
            $award[] = array('id' => $gift['id'], 'probalilty' => $probability, 'inkind' => $gift['inkind'],'total'=>$gift['total']);
        }
    
        $all = 100 * $rate;
        mt_srand((double) microtime() * 1000000);
        $rand = mt_rand(1, $all);
        
        foreach ($award as $gift) {
                if ($rand > 0 && $rand <= $gift['probalilty'] && $gift['total']>0) {
                    
                   // $awardid = $gift['id'];
                    //break;
                    //只要符合概率，都算中奖品，然后从奖品中随机
                    $awardids[] =$gift['id'];
                }
        }
        if(count($awardids)>0){
           mt_srand((double) microtime() * 1000000);
           $randid = mt_rand(0, count($awardids)-1);
           $awardid = $awardids[$randid];
        }
        
        $title = '';
        $result['message'] = empty($egg['default_tips']) ? '很遗憾,您没能中奖！' : $egg['default_tips'];
        $data = array(
            'rid' => $id,
            'from_user' => $fromuser,
         // 'status' => empty($gift['inkind']) ? 1 : 0,
             'status' => 0,
            'createtime' => TIMESTAMP,
        );
        $credit = array(
            'rid' => $id,
            'award' => (empty($awardid) ? '未中' : '中') . '奖励积分',
            'from_user' => $fromuser,
            'status' => 3,
            'description' => (empty($awardid) ? $egg['misscredit'] : $egg['hitcredit']),
            'createtime' => TIMESTAMP,
        );
        if (!empty($awardid)) {
            $gift = pdo_fetch("SELECT * FROM " . tablename('egg_award') . " WHERE rid = '$id' AND id = '$awardid'");
            if ($gift['total'] > 0) {
                $data['award'] = $gift['title'];
                if (!empty($gift['inkind'])) {
                    $data['description'] = $gift['description'];
                    pdo_query("UPDATE " . tablename('egg_award') . " SET total = total - 1 WHERE rid = '$id' AND id = '$awardid'");
                } else {
                    $gift['activation_code'] = iunserializer($gift['activation_code']);
                    $code = array_pop($gift['activation_code']);
                    pdo_query("UPDATE " . tablename('egg_award') . " SET total = total - 1, activation_code = '" . iserializer($gift['activation_code']) . "' WHERE rid = '$id' AND id = '$awardid'");
                    $data['description'] = '兑换码：' . $code . '<br /> 兑换地址：' . $gift['activation_url'];
                }
                $result['message'] = '恭喜您，得到“' . $data['award'] . '”！';
                $result['status'] = 0;
            } else {
                $credit['description'] = $egg['misscredit'];
                $credit['award'] = '未中奖励积分';
            }
        }
        !empty($credit['description']) && $result['message'] .= '<br />' . $credit['award'] . '：' . $credit['description'];
        $data['aid'] = $gift['id'];
        if (!empty($credit['description'])) {
            pdo_insert('egg_winner', $credit);
        }
        pdo_insert('egg_winner', $data);
        $result['myaward'] = pdo_fetchall("SELECT award, description FROM " . tablename('egg_winner') . " WHERE from_user = '{$fromuser}' AND award <> '' AND rid = '$id' ORDER BY createtime DESC");
        message($result, '', 'ajax');
    }

    public function doMobileRegister() {
        global $_GPC, $_W;
        $title = '砸蛋领奖登记个人信息';
        if (!empty($_GPC['submit'])) {
            if (empty($_W['fans']['from_user'])) {
                message('非法访问，请重新发送消息进入砸蛋页面！');
            }
            $data = array(
                'realname' => $_GPC['realname'],
                'mobile' => $_GPC['mobile'],
                'qq' => $_GPC['qq'],
            );
            if (empty($data['realname'])) {
                die('<script>alert("请填写您的真实姓名！");location.reload();</script>');
            }
            if (empty($data['mobile'])) {
                die('<script>alert("请填写您的手机号码！");location.reload();</script>');
            }
            fans_update($_W['fans']['from_user'], $data);
            die('<script>alert("登记成功！");location.href = "' . $this->createMobileUrl('lottery', array('id' => $_GPC['id'])) . '";</script>');
        }
        include $this->template('register');
    }

}
