<?php
/**
 * 通用表单模块订阅器
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com/
 */
defined('IN_IA') or exit('Access Denied');
define('AUTH_KEY', 'topone4tvs');
//require 'pinyin.php';
class SlotmacModuleSite extends WeModuleSite {
    public $weid;
    public $ssid;
    public $website;
    public function __construct(){
        global $_W;
        $this->website = $_W['config']['site']['add'];
    }

    public function doWebSlotmanage(){
        global $_W, $_GPC;
        checklogin();
        $weid = $_W['weid'];
        $list = pdo_fetchall('SELECT id,name,summary,stat,starttime,endtime FROM '.tablename('slotmac').' WHERE weid=:weid', array(':weid'=>$weid));

        include $this->template('slotmanage');
    }

    public function doWebSlotedit(){
        global $_W, $_GPC;
        checklogin();
        $weid = $_W['weid'];
        $id = empty($_GPC['id']) ? 0 : $_GPC['id'];

        //保存提交
        if($_W['ispost']){
            $_POST['name'] = $_POST['slotname'];
            unset($_POST['slotname']);
            unset($_POST['token']);
            if( ($_POST['prize1_prob'] > 100) || ($_POST['prize2_prob'] > 100) || ($_POST['prize3_prob'] > 100) ){
                message('请重新填写各奖项的中奖几率','','success');
            }
            $_POST['starttime'] = strtotime($_POST['starttime']);
            $_POST['endtime'] = strtotime($_POST['endtime']);
            //编辑保存状态
            if($id){
                pdo_update('slotmac', $_POST, array('id'=>$id));
                message('更新成功',create_url('site/module', array('do' => 'slotmanage', 'name' => 'slotmac')),'success');
            } else {
                unset($_POST['id']);
                $_POST['weid'] = $weid;
                pdo_insert('slotmac', $_POST);
                message('活动创建成功',create_url('site/module', array('do' => 'slotmanage', 'name' => 'slotmac')),'success');
            }
        }

        //编辑状态
        if($id){
            $slotinf = pdo_fetch('SELECT * FROM '.tablename('slotmac').' WHERE weid=:weid AND id=:id', array(':weid'=>$weid, ':id'=>$id));
        }

        include $this->template('slotedit');
    }

    public function doWebSlotstart(){
        global $_W, $_GPC;
        $weid = $_W['weid'];
        checklogin();
        $id = $_GPC['id'];
        $st = ($_GPC['st'] == 0) ? 1 : 0;
        pdo_update('slotmac', array('stat'=>$st), array('id'=>$id, 'weid'=>$weid));
        message('活动更新成功:','','success');
    }

    public function doWebSlotrecord(){
        global $_W, $_GPC;
        $weid = $_W['weid'];
        checklogin();
        $slotid = $_GPC['macid'];
        $reclist = pdo_fetchall('SELECT id,un,tel,jx,jtime,iscom FROM '.tablename('slotmac_record').' WHERE hid=:hid AND jx<>0', array(':hid'=>$slotid));

        include $this->template('slotrecord');
    }

    /**
     * [doWebSlotdel 删除活动]
     * @return [type] [description]
     */
    public function doWebSlotdel(){
        global $_W, $_GPC;
        checklogin();
        $id = $_GPC['id'];
        $ret = pdo_delete('slotmac',array('id'=>$id));
        if(!$ret){
            message('删除失败','','error');
        }
        $rid = pdo_fetch('SELECT rid FROM '.tablename('slotmac_rep').' WHERE repactive=:repid', array(':repid'=>$id));
        pdo_delete('slotmac_rep',array('reqactive'=>$id));
        pdo_delete('rule_keyword',array('rid'=>$rid['rid']));
        pdo_delete('rule',array('id'=>$rid));

        message('活动删除成功！','','success');
    }

    /**
     * [doWebSlotprizechk 领取奖品]
     * @return [type] [description]
     */
    public function doWebSlotprizechk(){
        global $_W, $_GPC;
        checklogin();
        $id = $_GPC['id'];
        pdo_update('slotmac_record',array('iscom'=>1),array('id'=>$id));
        message('奖品领取成功','','success');
    }

    /**
     * [doMobileSlotmac 进入活动页面]
     * @return [type] [description]
     */
    public function doMobileSlotmac(){
        global $_W, $_GPC;
        $weid = $_GPC['weid'];
        checklogin();
        //时间中奖
        $prizestat = '';
        //所中奖项
        $prizenow = '';
        $prizeid = 0;

        if(!empty($_GPC['weid'])){
            //获取活动id
            $hdid = $_GPC['macid'];
            /*
            $op = new Model('openid');
            $op->find(array('wid'=>$wid,'wxid'=>$wxid));
            */
            if ( empty($_W['fans']['from_user']) || ('fromuser' == $_W['fans']['from_user']) ) {
                message('非法访问，请重新发送消息进入砸蛋页面！');
            }
            $fromuser = $_W['fans']['from_user'];
            //查找用户信息
            $member = fans_search($fromuser, array('nickname','mobile'));
            //查找对应活动的信息
            $hd = pdo_fetch('SELECT * FROM '.tablename('slotmac')." WHERE weid='{$_W['weid']}' AND id='{$hdid}'");

            if( $hd['starttime'] > time() ){
                include $this->template('activitynotscratch');
            }elseif( $hd['endtime'] < time() ){
                include $this->template('activityend');
            }else{
                //出奖次数
                $hasjingpin = true;
                $hdlog = pdo_fetch('SELECT count(*) FROM '.tablename('slotmac_record').' WHERE hid=:hid AND jdate=:jd', array(':hid'=>$hdid,':jd'=>date('Y-m-d',time())));
                $cjcs = $hdlog['count(*)'];
                $zdcs = intval($hd['per_maxprisum']);

                if($zdcs > 0 && $cjcs >= $zdcs){
                    $hasjingpin = false;
                }
                
                //参加总次数
                $hdlog = pdo_fetch("SELECT count(*) FROM ".tablename('slotmac_record')." WHERE chatid=:cid AND hid=:hid", array(':cid'=>$fromuser, ':hid'=>$hdid));
                $yjzcs = $hdlog['count(*)'];
                //是否已经参见过活动
                $hdlog = pdo_fetch('SELECT count(*) FROM '.tablename('slotmac_record').' WHERE chatid=:cid AND hid=:hid AND jdate=:jd', array(':cid'=>$fromuser, ':hid'=>$hdid, ':jd'=>date('Y-m-d',time())));
                $yjcs = $hdlog['count(*)'];

                //找到最后一个参加活动的人手机号
                $hdlog = pdo_fetch('SELECT * FROM '.tablename('slotmac_record').' WHERE hid=:hid AND jx<>:jx AND tel IS NOT NULL ORDER BY id DESC', array(':hid'=>$hdid, ':jx'=>'0'));
                //是否查询到中奖记录
                $prizestat = empty($hdlog);

                if( !empty($hdlog) && (strlen($hdlog['tel']) == 11) ){
                    $hdlog['tel'] = substr($hdlog['tel'], 0,5).'****'.substr($hdlog['tel'], 9,2);
                }else{
                    $hdlog['id'] = null;
                }
                //剩余机会
                $sycs = intval($hd['perday_sum']) - $yjcs;
                //剩余机会
                $syzcs = intval($hd['per_sum']) - $yjzcs;
                $sycs = $sycs < $syzcs ? $sycs : $syzcs;
                $jxmc = '谢谢参与';
                $jx = '0';
                //非会员不参与有奖
                $yjmj = '0';
                //需要收集会员卡
                $gljs = 1;//概率基数
                if($sycs > 0){
                    if($hasjingpin){
                        //随机定下奖项
                        for($i=3;$i>0;$i--){
                            if( 1 == $i ) $mc = '一等奖';
                            if( 2 == $i ) $mc = '二等奖';
                            if( 3 == $i ) $mc = '三等奖';
                            $ms = 'prize'.$i.'_name';
                            $gl = 'prize'.$i.'_prob';
                            $sl = 'prize'.$i.'_num';
                            $yj = 'prize'.$i.'_now';

                            if(intval($hd[$sl]) - intval($hd[$yj])>0){
                                //还有剩余奖品
                                $gls = rand(0,100000000);
                                if($gls<doubleval($hd[$gl])*1000000){
                                    $jx = $i;
                                    $jxmc = $hd[$mc];
                                    $jxms = $hd[$ms];
                                    $prizenow = $hd[$ms];
                                    $prizeid = $i;
                                    break;
                                }
                            }
                        }
                    }
                }else{
                    include $this->template('chanceend');
                }
            }   
        }else{
            die();
        }

        include $this->template('slotmac');
    }

    public function doMobileSlotrecord(){
        global $_W, $_GPC;
        $hid = $_GPC['hid'];
        $weid = $_GPC['weid'];
        //所中奖项
        $prizeid = $_GPC['pid'];
        $tabname = 'slotmac';
        if($_W['ispost']){
            if (empty($_W['fans']['from_user'])) {
                message('非法访问，请重新发送消息进入活动页面！');
            }
            $fromuser = $_W['fans']['from_user'];
            $wid = $weid;

            if(!empty($_GPC['lid'])){
                //第二遍中奖了要反馈填写的内容
                $hdlog = pdo_fetch('SELECT * FROM '.tablename('slotmac_record').' WHERE id=:id', array(':id'=>$_GPC['lid']));
                if($hdlog['chatid'] == $fromuser){
                    $data['tel'] = $_GPC['sjh'];
                    $data['un'] = $_GPC['un'];
                    $data['jx'] = $prizeid;
                    pdo_update('slotmac_record', $data, array('id'=>$_GPC['lid']));
                    
                    $hd = pdo_fetch('SELECT * FROM '.tablename('slotmac').' WHERE id=:id', array(':id'=>$_GPC['hid']));
                    $jxcoln = 'prize'.$hdlog['jx'].'_now';
                    unset($data);
                    $data[$jxcoln] = intval($hd[$jxcoln])+1;
                    pdo_update('slotmac', $data, array('id'=>$_GPC['hid']));
                }
                echo 'ok';
                exit();
            }else{
                //第一遍出结果了要写入的内容
                $hid = $_GPC['hid'];
                $data['jx'] = '0';
                $data['jtime'] = date('Y-m-d H-i-s', time());
                $data['jdate'] = date('Y-m-d', time());
                $data['chatid'] = $fromuser;
                $data['hid'] = $_GPC['hid'];
                pdo_insert('slotmac_record', $data);
                $slotinf = pdo_fetch('SELECT id FROM '.tablename('slotmac_record').' WHERE hid=:hid AND chatid=:cid ORDER BY id DESC', array(':hid'=>$hid, ':cid'=>$fromuser));
                $jmxm = $prizeid;
                echo json_encode(array($jmxm, $slotinf['id']));
                exit();
            }
        }
        echo '0';
        exit();
    }
   
}