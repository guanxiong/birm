<?php
/**
 *
 *
 * [WNS] Copyright (c) 2013 BIRM.CO
 */
defined('IN_IA') or exit('Access Denied');

class SharevoteModule extends WeModule {
    public $name = 'Sharevote';
    public $title = '投票';
    public $ability = '';
    public $tablename = 'news_reply';

    public function fieldsFormDisplay($rid = 0) {
        global $_W;
        $result = pdo_fetchall("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `parentid` ASC, `id` ASC", array(':rid' => $rid));
        $result = istripslashes($result);
        $reply = array();
        if (!empty($result)) {
            foreach ($result as $index => $row) {
                if (empty($row['parentid'])) {
                    $reply[$row['id']] = $row;
                } else {
                    $reply[$row['parentid']]['children'][] = $row;
                }
            }
        }
        include $this->template('display');
    }

    public function fieldsFormValidate($rid = 0) {
        return true;
    }

    public function fieldsFormSubmit($rid = 0) {
        global $_GPC, $_W;
        if (!empty($_GPC['news-title'])) {
            foreach ($_GPC['news-title'] as $groupid => $items) {
                if (empty($items)) {
                    continue;
                }
                foreach ($items as $itemid => $row) {
                    if (empty($row)) {
                        continue;
                    }
                    $update = array(
                        'title' => $_GPC['news-title'][$groupid][$itemid],
                        'description' => $_GPC['news-description'][$groupid][$itemid],
                        'thumb' => $_GPC['news-picture-old'][$groupid][$itemid],
                        'content' => $_GPC['news-content'][$groupid][$itemid],
                        'url' => $_GPC['news-url'][$groupid][$itemid],
                    );
                    if (!empty($_GPC['news-picture'][$groupid][$itemid])) {
                        $update['thumb'] = $_GPC['news-picture'][$groupid][$itemid];
                        file_delete($_GPC['news-picture-old'][$groupid][$itemid]);
                    }
                    pdo_update($this->tablename, $update, array('id' => $itemid));
                    //处理新增子项
                    if (!empty($_GPC['news-title-new'][$groupid])) {
                        foreach ($_GPC['news-title-new'][$groupid] as $index => $title) {
                            if (empty($title)) {
                                continue;
                            }
                            unset($_GPC['news-title-new'][$groupid]);
                            $insert = array(
                                'rid' => $rid,
                                'parentid' => $itemid,
                                'title' => $title,
                                'description' => $_GPC['news-description-new'][$groupid][$index],
                                'thumb' => $_GPC['news-picture-new'][$groupid][$index],
                                'content' => $_GPC['news-content-new'][$groupid][$index],
                                'url' => $_GPC['news-url-new'][$groupid][$index],
                            );
                            pdo_insert($this->tablename, $insert);
                        }
                    }
                }
            }
        }
        //处理添加
        if (!empty($_GPC['news-title-new'])) {
            foreach ($_GPC['news-title-new'] as $itemid => $titles) {
                if (!empty($titles)) {
                    $parentid = 0;
                    foreach ($titles as $index => $title) {
                        if (empty($title)) {
                            continue;
                        }
                        $insert = array(
                            'rid' => $rid,
                            'parentid' => $parentid,
                            'title' => $title,
                            'description' => $_GPC['news-description-new'][$itemid][$index],
                            'thumb' => $_GPC['news-picture-new'][$itemid][$index],
                            'content' => $_GPC['news-content-new'][$itemid][$index],
                            'url' => $_GPC['news-url-new'][$itemid][$index],
                        );
                        pdo_insert($this->tablename, $insert);
                        if (empty($parentid)) {
                            $parentid = pdo_insertid();
                        }
                    }
                }
            }
        }
        return true;
    }

    public function ruleDeleted($rid = 0) {
        global $_W;
        $replies = pdo_fetchall("SELECT id, thumb FROM ".tablename($this->tablename)." WHERE rid = '$rid'");
        $deleteid = array();
        if (!empty($replies)) {
            foreach ($replies as $index => $row) {
                file_delete($row['thumb']);
                $deleteid[] = $row['id'];
            }
        }
        pdo_delete($this->tablename, "id IN ('".implode("','", $deleteid)."')");
        return true;
    }

    public function doFormDisplay() {
        global $_W, $_GPC;
        $result = array('error' => 0, 'message' => '', 'content' => '');
        $result['content']['id'] = $GLOBALS['id'] = 'add-row-news-'.$_W['timestamp'];
        $result['content']['html'] = template('modules/news/'.$_GPC['tpl'].'_form_display', TEMPLATE_FETCH);
        exit(json_encode($result));
    }

    public function doDetail() {
        global $_W, $_GPC;
        $id = intval($_GPC['id']);
        $sql = "SELECT * FROM " . tablename($this->tablename) . " WHERE `id`=:id";
        $row = pdo_fetch($sql, array(':id'=>$id));
        if (!empty($row['url'])) {
            header("Location: ".$row['url']);
        }
        $row = istripslashes($row);
        $row['thumb'] = $_W['attachurl'] . trim($row['thumb'], '/');
        include $this->template('detail');
    }

    public function doDelete() {
        global $_W,$_GPC;
        $id = intval($_GPC['id']);
        $sql = "SELECT id, parentid, rid, thumb FROM " . tablename($this->tablename) . " WHERE `id`=:id";
        $row = pdo_fetch($sql, array(':id'=>$id));
        if (empty($row)) {
            message('抱歉，回复不存在或是已经被删除！', '', 'error');
        }
        if (pdo_delete($this->tablename, array('id' => $id))) {
            file_delete($row['thumb']);
            if ($row['parentid'] == 0) {
                $list = pdo_fetchall("SELECT thumb FROM " . tablename($this->tablename) . " WHERE `parentid`=:parentid", array(':parentid' => $row['id']));
                if (!empty($list)) {
                    foreach ($list as $thumb) {
                        file_delete($thumb['thumb']);
                    }
                }
                pdo_delete($this->tablename, array('parentid' => $row['id']));
            }
        }
        message('删除回复成功', '', 'success');
    }

    public function doindex() {
        global $_GPC,$_W;
        include $this->template('index');
    }

    public function dosend_vote(){
        global $_GPC,$_W;
        if($_GPC['submit'] == 1){
            $title = $_POST['title'];
            $add = $_POST['add'];
            $switch = $_POST['switch'];
            $vote = '';
            $voteValue = '';
            for($i = 1;$i < 50;$i++){
                if($_POST['vote-'.$i] != '' && $_POST['vote-'.$i] != '投票选项'){
                    $vote = $vote.$_POST['vote-'.$i]."-";
                    $voteValue = $voteValue.$i."|0-";
                }
            }
            $result = $_POST['result'];
            $username = $_POST['username'];
            $sql = "INSERT INTO ims_share_vote VALUES(0,'".$title."','".$add."','".$switch."','".$vote."','".$voteValue."','".$result."','".$username."','".time()."',0,0)";
            pdo_query($sql);
            $vRow = pdo_fetch("SELECT * FROM ims_share_vote WHERE title='".$title."' and voteadd='".$add."' and switch='".$switch."' and vote_item='".$vote."' and result='".$result."' and username='".$username."' ORDER BY time DESC");
            setcookie('username',$username, time()+60*60*24*30*12,'/');
        }

        if(isset($_COOKIE['username'])){
            $username = $_COOKIE['username'];
        }
        include $this->template('send_vote');
        if(count($vRow) != 0){
            $imgUrl = $_W['siteroot'].'/source/modules/sharevote/icon.jpg';
            $title = $vRow['title'];
            $desc = $vRow['voteadd'];
            $item = 'vote';
            $itemid = $vRow['id'];
            include $this->template('share');
        }
        include $this->template('footer');
    }
    /*
     * 显示该投票页面
     */
    public function dopreview_vote(){
        global $_GPC;
        global $_W;
        $vId = $_GPC['id'];
        /**更新阅读次数**/
        pdo_query("UPDATE ims_share_vote SET read_times=read_times+1 WHERE id=".$vId);

        $vRow = pdo_fetch("SELECT * FROM ims_share_vote WHERE id=".$vId);
        $tData = time()-$vRow['time'];
        $curName = '刚刚';
        if(floor($tData/60)>0){
            $curName = '分钟前';
            $tData = floor($tData/60);
            if(floor($tData/60)>0){
                $curName = '小时前';
                $tData = floor($tData/60);
                 if(floor($tData/24)>0){
                    $curName = '天前';
                    $tData = floor($tData/24);
                    if(floor($tData/30)>0){
                        $curName = '月前';
                        $tData = floor($tData/30);
                    }
                }
            }
        }
        if($curName != '刚刚'){
        $curName = ($tData.$curName);
        }
        $iRow = explode('-',$vRow['vote_item']);    //投票选项

        /*计算总票数*/
        $allVotes = 0;                              //总票数
        $voteArr = array();                     //每个选项得票
        $rTimes = $vRow['read_times'];          //阅读次数
        $sTimes = $vRow['share_times'];         //分享次数
        $vvRow = explode('-',$vRow['vote_value']);
        for($m = 0;$m < count($vvRow);$m++){
            $inRow = explode('|',$vvRow[$m]);
             $allVotes = $allVotes+$inRow[1];
        }
        /*计算百分比*/
        $voteNum = 0;
        for($m = 0;$m < count($vvRow);$m++){
            $inRow = explode('|',$vvRow[$m]);
            $voteArr[$m]['votes_val'] = $inRow[1];
            if($m == (count($vvRow)-2)){
                $voteArr[$m]['votes_per'] = 100-$voteNum;
            }else{
                $voteArr[$m]['votes_per'] = floor(($inRow[1]/$allVotes)*100);
                $voteNum = $voteNum+$voteArr[$m]['votes_per'];
            }
        }
        $hasVote = 'false';
        if(isset($_COOKIE['VOTE'.$vId])){
            $hasVote = 'true';
        }
        include $this->template('preview_vote');
        if(count($vRow) != 0){
            $imgUrl = $_W['siteroot'].'/source/modules/sharevote/icon.jpg';
            $title = $vRow['title'];
            $desc = $vRow['voteadd'];
            $item = 'vote';
            $itemid = $vRow['id'];
            include $this->template('share');
        }
        $fData="我也要发起投票";
        include $this->template('footer2');
    }
    /*
    * 显示该投票页面
    */
    /*ajax加载票数*/
    public function doajax_vote(){
        global $_GPC;
        global $_W;

        $vId = $_GPC['vid'];                 //该《投票》的id
        $voteId = $_GPC['voteid'];          //投票选项id
        $allVotes = $_GPC['allvote'];       //总票数
        setcookie('VOTE'.$vId,'true', time()+60*60*24*30*12,'/');
        $vRow = pdo_fetch("SELECT * FROM ims_share_vote WHERE id=".$vId);
        $vvRow = explode('-',$vRow['vote_value']);

        for($m = 0;$m < count($vvRow);$m++){
            $inRow = explode('|',$vvRow[$m]);
            if(($voteId+1) == $inRow[0]){
                $vRow['vote_value']  = str_replace($inRow[0]."|".$inRow[1],$inRow[0]."|".($inRow[1]+1),$vRow['vote_value']);
                $allVotes++;
            }
        }
        pdo_query("UPDATE ims_share_vote SET vote_value='".$vRow['vote_value']."' WHERE id=".$vId);
        /*计算百分比*/
        $voteNum = 0;
        $voteArr = array();                     //每个选项得票
        $vRow = pdo_fetch("SELECT * FROM ims_share_vote WHERE id=".$vId);
        $vvRow = explode('-',$vRow['vote_value']);
        for($m = 0;$m < count($vvRow);$m++){
            $inRow = explode('|',$vvRow[$m]);
            if($vvRow[$m] != ''){
                $voteArr[$m]['votes_val'] = $inRow[1];
                if($m == (count($vvRow)-2)){
                    $voteArr[$m]['votes_per'] = 100-$voteNum;
                }else{
                    $voteArr[$m]['votes_per'] = floor(($inRow[1]/$allVotes)*100);
                    $voteNum = $voteNum+$voteArr[$m]['votes_per'];
                }
            }
        }
        $data = array();
        $data[] = (object)array('allvotes'=>$allVotes,'votearr'=>$voteArr);
        echo json_encode($data);
    }
    /**分享次数计算**/
    public function doajax_share(){
        global $_GPC;
        $item = $_GPC['item'];
        $itemid = $_GPC['itemid'];
        $sql = "UPDATE ims_share_".$item." SET share_times=share_times+1 WHERE id=".$itemid;
        echo $sql;
        pdo_query($sql);
    }

    /*举报*/
    public function doreport(){
        global $_GPC;
        $item = $_GPC['item'];
        $itemid = $_GPC['itemid'];
        $content = $_GPC['content'];
        $hasReport = 'false';
        if($content != ''){
            pdo_query("INSERT INTO ims_share_report VALUES(0,'".$item."','".$itemid."','".$content."','".time()."')");
            $hasReport = 'true';
        }
        include $this->template('report');
    }
    /*管理投票*/
    public function domanagevote(){
        global $_GPC;
        $deleteid = $_GPC['deleteid'];
        if($deleteid != ''){
            pdo_query("DELETE from ims_share_vote WHERE id=".$deleteid);
            pdo_query("DELETE from ims_share_report WHERE item_id=".$deleteid);
        }
        $voteArr = array();
        $list = pdo_fetchall("SELECT * FROM ims_share_vote  ORDER BY time DESC");
        for($i = 0;$i < count($list);$i++){
            $vName = explode('-',$list[$i]['vote_item']);
            for($m = 0;$m < count($vName);$m++){
                if($vName[$m] != ''){
                    $voteArr[$i][$m]['vname'] = $vName[$m];
                }
            }

            $vValue = explode('-',$list[$i]['vote_value']);
            for($m = 0;$m < count($vValue);$m++){
                if($vValue[$m] != ''){
                    $inRow = explode('|',$vValue[$m]);
                    $voteArr[$i][$m]['vvalue'] = $inRow[1];
                }
            }
        }

        include $this->template('manage-vote');
    }
    public function domanagereport(){
        global $_GPC;
        $itemid = $_GPC['itemid'];
        $id = $_GPC['id'];
        if($itemid != ''){
            pdo_query("DELETE from ims_share_vote WHERE id=".$itemid);
            pdo_query("DELETE from ims_share_report WHERE id=".$id);
        }
        $voteArr = array();
        $list = pdo_fetchall("SELECT * FROM ims_share_report ORDER BY time DESC");
        for($i = 0;$i < count($list);$i++){
            $vlist = pdo_fetch("SELECT * FROM ims_share_vote WHERE id=".$list[$i]['item_id']);
            $list[$i]['title'] = $vlist['title'];
        }

        include $this->template('manage-report');
    }
}