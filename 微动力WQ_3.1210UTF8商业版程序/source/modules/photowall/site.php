<?php
/**
 * 照片墙模块微站定义
 */
defined('IN_IA') or exit('Access Denied');

class PhotowallModuleSite extends WeModuleSite {
	public $tablename = 'photowall_reply';

    public function updateNums($pid,$type){
        global $_W;
        $num = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('photowall_comment')." WHERE weid = '{$_W['weid']}' AND pid = '{$pid}' AND type = '{$type}'");
        if ($type == '1') {
           pdo_update('photowall_data',array('votenum' => $num),array('id'=>$pid));
        }else{
            pdo_update('photowall_data',array('commontnum' => $num),array('id'=>$pid));
        } 
        return true;
    }

    public function getNickName($from_user){
        global $_W;
        $nickname = pdo_fetch("SELECT nickname,realname FROM ".tablename('fans')." WHERE weid = '{$_W['weid']}' AND from_user = '{$from_user}'");
        if (!empty($nickname)) {
            return $nickname['nickname'];
        }elseif (!empty($realname)) {
            return $nickname['realname'];
        }else{
            return '<a href="mobile.php?act=module&do=profile&name=fans&weid='.$_W['weid'].'#qq.com#wechat_redirect">登记信息</a>';
        }       
    }
    public function getCommentNum($pid,$type,$from_user='0'){
        global $_W,$_GPC;
        $condition = '';
        if (!empty($from_user)) {
            $condition.="AND from_user = '{$from_user}'";
        }
        $result = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('photowall_comment')." WHERE weid = '{$_W['weid']}' AND pid = '{$pid}' AND type = '{$type}'" . $condition);
        return $result;
    }
    public function doMobilevote(){
        global $_GPC, $_W;
        checkAuth();
        $pid = $_GPC['pid'];
        if (($_GPC['tp'] == '1') && ($this->getCommentNum($pid,'1',$_W['fans']['from_user']) == '0')) {
            $data = array(
                'pid' => $pid,
                'rid' => $_GPC['rid'],
                'weid' => $_W['weid'],
                'from_user' => $_W['fans']['from_user'],
                'type' => '1',
                'time' => time(),
                );
            pdo_insert('photowall_comment',$data);
            $this->updateNums($pid,'1');
            message('点赞成功',$this->createMobileUrl('display',array('rid'=>$_GPC['rid'])),'success');
        }elseif(($_GPC['tp'] == '0') && ($this->getCommentNum($pid,'1',$_W['fans']['from_user']) != '0')){
            pdo_delete('photowall_comment',array('pid'=>$pid,'from_user'=>$_W['fans']['from_user']));
            $this->updateNums($pid,'1');
            message('取消点赞成功',$this->createMobileUrl('display',array('rid'=>$_GPC['rid'])),'success');
        }else{
            message('参数错误',$this->createMobileUrl('display',array('rid'=>$_GPC['rid'],'type' => '1')),'error');
        }
    }
    public function doMobilecommont(){
        global $_GPC, $_W;
        checkAuth();
        message('正在开发中');
    }
	public function doMobileList() {
		//这个操作被定义用来呈现 功能封面
        global $_GPC, $_W;
        $pindex = max(1, intval($_GPC['page']));
        $psize = 10;
        $photowall = pdo_fetchall("SELECT * FROM " . tablename('photowall_reply') . " WHERE weid = '{$_W['weid']}' ORDER BY votenum DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('photowall_reply')." WHERE weid = '{$_W['weid']}'");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('list');
	}

    public function doMobileMyphotos() {
        //这个操作被定义用来呈现 功能封面
        global $_GPC, $_W;
        checkAuth();
        $from_user = $_W['fans']['from_user'];
        $reply['copyright'] = '微动力技术支持';
        $data = pdo_fetchall("SELECT * FROM ".tablename('photowall_data')." WHERE from_user = '{$from_user}' AND status = '1' ORDER BY id DESC LIMIT 6");
        if (empty($data)) {
            message('您暂时没有提交任何照片！');
        }
        include $this->template('myphotos');
    }

    public function doMobilegetMyData(){
        global $_W,$_GPC;
        checkAuth();
        $from_user = $_W['fans']['from_user'];
        //下拉的时候的条件
        if($_GPC['nextrow']){
            $pagestart=$_GPC['nextrow'];
        }else{
            $pagestart=0;
        }
        $data = pdo_fetchall("SELECT * FROM ".tablename('photowall_data')." WHERE from_user = '{$from_user}' AND status = '1' ORDER BY id DESC LIMIT ".$pagestart .", 4 ");
        if (empty($data)){
            echo "1";
            exit();
        }
        foreach ($data as &$value) {
            $value['from_user'] = $this->getNickName($value['from_user']);
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
            $html1 ='<button type="button" class="btn btn-default btn-lg active">得赞数：'.$this->getCommentNum($value['id'],'1').'</button>';   
            $value['html1'] = $html1;
        }
        echo json_encode( $data );
    
    }

    public function doMobileDisplay() {
        global $_W, $_GPC;
        $rid = intval($_GPC['rid']);
        $reply = pdo_fetch("SELECT * FROM ".tablename('photowall_reply')." WHERE weid = '{$_W['weid']}' AND rid = '{$rid}' ");
        $data = pdo_fetchall("SELECT * FROM ".tablename('photowall_data')." WHERE rid = '{$rid}' AND status = '1' ORDER BY votenum DESC LIMIT 6");
        if (empty($data)) {
            message('活动不存在或是已经被删除！或者活动没有任何图片...');
        }
        //print_r($photos);
        include $this->template('display');
    }
    public function doMobilegetData(){
        global $_W,$_GPC;
        //下拉的时候的条件
        if($_GPC['nextrow']){
            $pagestart=$_GPC['nextrow'];
        }else{
            $pagestart=0;
        }
        $rid = intval($_GPC['rid']);
        $data = pdo_fetchall("SELECT * FROM ".tablename('photowall_data')." WHERE rid = '{$rid}' AND status = '1' ORDER BY votenum DESC LIMIT ".$pagestart .", 4 ");
        if (empty($data)){
            echo "1";
            exit();
        }
        foreach ($data as &$value) {
            $value['from_user'] = $this->getNickName($value['from_user']);
            $value['time'] = date('Y-m-d H:i:s',$value['time']);
            if ( $this->getCommentNum($value['id'],'1',$_W['fans']['from_user']) == '0') {
                $html1 ='<a href="'.$this->createMobileUrl('vote',array('pid'=>$value['id'],'weid'=>$_W['weid'],'tp' => '1','rid'=>$_GPC['rid'])).'"><button type="button" class="btn btn-default btn-lg">点赞('.$this->getCommentNum($value['id'],'1').')</button></a>';   
            }else{
                $html1 = '<a href="'.$this->createMobileUrl('vote',array('pid'=>$value['id'],'weid'=>$_W['weid'],'tp' => '0','rid'=>$_GPC['rid'])).'"><button type="button" class="btn btn-default btn-lg active">已点赞('.$this->getCommentNum($value['id'],'1').')</button></a>';
            }
            if ( $this->getCommentNum($value['id'],'2',$_W['fans']['from_user']) == '0') {
                $html2 ='<a href="'.$this->createMobileUrl('commont',array('pid'=>$value['id'],'weid'=>$_W['weid'],'tp' => '1','rid'=>$_GPC['rid'])).'"><button type="button" class="btn btn-default btn-lg" style="float: right;">评论('.$this->getCommentNum($value['id'],'2').')</button></a>';   
            }else{
                $html2 = '<a href="'.$this->createMobileUrl('commont',array('pid'=>$value['id'],'weid'=>$_W['weid'],'tp' => '0','rid'=>$_GPC['rid'])).'"><button type="button" class="btn btn-default btn-lg active" style="float: right;">已评论('.$this->getCommentNum($value['id'],'2').')</button></a>';
            }
            $value['html1'] = $html1;
            $value['html2'] = $html2;
        }
        echo json_encode( $data );
    
    }
	public function doWebList() {
		//这个操作被定义用来呈现 管理中心导航菜单
		global $_GPC, $_W;
        include model('rule');
        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $sql = "weid = :weid AND `module` = :module";
        $params = array();
        $params[':weid'] = $_W['weid'];
        $params[':module'] = 'photowall';

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
                $photowall = pdo_fetch("SELECT sendtimes,daysendtimes,starttime,endtime,isshow,isdes,status FROM " . tablename('photowall_reply') . " WHERE rid = :rid ", array(':rid' => $item['id']));
                $item['starttime'] = date('Y-m-d H:i', $photowall['starttime']);
                $endtime = $photowall['endtime'] + 86399;
                $item['endtime'] = date('Y-m-d H:i', $endtime);
                $nowtime = time();
                if (($photowall['starttime'] > $nowtime) || ($photowall['status'] == '0')) {
                    $item['statuss'] = '<span class="label label-red">未开始</span>';
                    $item['show'] = 1;
                } elseif (($endtime < $nowtime) ||  ($photowall['status'] == '3')) {
                    $item['statuss'] = '<span class="label label-blue">已结束</span>';
                    $item['show'] = 0;
                } elseif($photowall['status'] == '2') {
                    $item['statuss'] = '<span class="label ">已暂停</span>';
                }elseif($photowall['status'] == '1'){
                    $item['statuss'] = '<span class="label label-green">已开始</span>';
                }else{
                    $item['statuss'] = '<span class="label label-green">状态未知</span>';
                }
                $item['sendtimes'] = $photowall['sendtimes'];
                $item['daysendtimes'] = $photowall['daysendtimes'];
                $item['isshow'] = $photowall['isshow'];
                $item['isdes'] = $photowall['isdes'];
                $item['status'] = $photowall['status'];
            }
        }
        include $this->template('manage');
	}

    public function doWebDisplay() {
        global $_GPC, $_W;
        if (checksubmit('delete') && !empty($_GPC['select'])) {
            foreach ($_GPC['select'] as $key => $value) {
                pdo_delete('photowall_data', array('id' => $value));
                pdo_delete('photowall_comment', array('pid' => $value));
            }
            //print_r($_GPC['select']);
            message('图片删除成功！', $this->createWebUrl('display', array('rid' => $_GPC['rid'])), 'success');
        }
        if (!empty($_W['ispost'])) {
            $ret = $_GPC['ret'] == 'true';
            $set = @json_decode(base64_decode($_GPC['dat']), true);
            $ree = pdo_fetch("SELECT status FROM ".tablename('photowall_data')." WHERE id = '{$set}'");
            if ($ree['status'] == '0') {
                $re = '1';
            }else{
                $re = '0';
            }
            if (pdo_update('photowall_data',array('status' => $re, ),array('id'=> $set))) {
                exit('success');
            }
        }
        $rid = $_GPC['rid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 50;
        $list = pdo_fetchall("SELECT * FROM ".tablename('photowall_data')." WHERE weid = '{$_W['weid']}' AND rid = '{$rid}' ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('photowall_data')." WHERE weid = '{$_W['weid']}' AND rid = '{$rid}' ");
        $pager = pagination($total, $pindex, $psize);
        include $this->template('display');
    }

}