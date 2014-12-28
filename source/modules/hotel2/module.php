<?php

/**
 * 微酒店
 *
 * @author WeEngine Team & ewei
 * @url
 */
defined('IN_IA') or exit('Access Denied');

include "./source/modules/hotel2/model.php";

class Hotel2Module extends WeModule {
 public $_img_url = './source/modules/hotel2/template/style/img/';

    public $_css_url = './source/modules/hotel2/template/style/css/';

    public $_script_url = './source/modules/hotel2/template/style/js/';
    public $_hotel_level_config = array(5 => '五星级酒店', 4 => '四星级酒店', 3 => '三星级酒店', 2 => '两星级以下', 15 => '豪华酒店', 14 => '高档酒店', 13 => '舒适酒店', 12 => '经济型酒店', );
   
    public function fieldsFormDisplay($rid = 0) {  }
    public function fieldsFormValidate($rid = 0) {     return '';   }
    public function fieldsFormSubmit($rid) {  }
    public function ruleDeleted($rid) {  }

    public function doHotel() {
        global $_GPC, $_W;
         
        $op = $_GPC['op'];
        $weid = $_W['weid'];
        $hotel_level_config = $this->_hotel_level_config;
        if ($op == 'edit') {
            //编辑
            $id = intval($_GPC['id']);
            if (checksubmit()) {
                $insert = array(
                    'weid' => $weid,
                    'displayorder' => $_GPC['displayorder'],
                    'title' => $_GPC['title'],
                    'address' => $_GPC['address'],
                    'location_p' => $_GPC['location_p'],
                    'location_c' => $_GPC['location_c'],
                    'location_a' => $_GPC['location_a'],
                    'lng' => $_GPC['lng'],
                    'lat' => $_GPC['lat'],
                    'phone' => $_GPC['phone'],
                    'mail' => $_GPC['mail'],
                    'description' => $_GPC['description'],
                    'content' => $_GPC['content'],
                    'traffic' => $_GPC['traffic'],
                    'sales' => $_GPC['sales'],
                    'level' => $_GPC['level'],
                     'status'=>$_GPC['status'],
                    'brandid'=>$_GPC['brandid'],
                    'businessid'=>$_GPC['businessid'],
                );
                 
                 $device = array();
                if ($_GPC['device']) {
                    foreach ($_GPC['device'] as $key => $value) {
                        
                        $device[$key]['value'] = $value;
                        $device[$key]['isdel'] = 0;
                        if ($_GPC['show_device']) {
                            if (array_key_exists($key, $_GPC['show_device'])) {
                                $device[$key]['isshow'] = 1;
                            } else {
                                $device[$key]['isshow'] = 0;
                            }
                        } else {
                            $device[$key]['isshow'] = 0;
                        }
                    }
                    $num = count($device);
                    if ($_GPC['new_device']) {
                        foreach ($_GPC['new_device'] as $key => $value) {
                            if (empty($value)) {
                                break;
                            }
                            $device[$num]['value'] = $value;
                            $device[$num]['isdel'] = 1;
                            if ($_GPC['show_new_device']) {
                                if (array_key_exists($key, $_GPC['show_new_device'])) {
                                    $device[$num]['isshow'] = 1;
                                } else {
                                    $device[$num]['isshow'] = 0;
                                }
                            } else {
                                $device[$num]['isshow'] = 0;
                            }
                            $num++;
                        }
                    }
                }
                //print_r($device);exit;

                $insert['device'] = serialize($device);
        
                
                 if (!empty($_FILES['thumb']['tmp_name'])) {
                    file_delete($_GPC['thumb-old']);
                    $upload = file_upload($_FILES['thumb']);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
                     $insert['thumb'] = $upload['path'];
                }
                $cur_index = 0;
                if (!empty($_GPC['attachment-new'])) {
                    foreach ($_GPC['attachment-new'] as $index => $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $hsdata[$index] = array(
                            'attachment' => $_GPC['attachment-new'][$index],
                        );
                    }
                    $cur_index = $index + 1;
                }
                if (!empty($_GPC['attachment'])) {
                    foreach ($_GPC['attachment'] as $index => $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $hsdata[$cur_index + $index] = array(
                            'attachment' => $_GPC['attachment'][$index]
                        );
                    }
                }

                $insert['thumbs'] = serialize($hsdata);
                
                
                if (empty($id)) {
                    pdo_insert('hotel2', $insert);
                } else {
                    pdo_update('hotel2', $insert, array('id' => $id));
                }
                message("酒店信息保存成功!", $this->createWebUrl('hotel'), "success");
            }
            $item = pdo_fetch("select * from " . tablename('hotel2') . " where id=:id limit 1", array(":id" => $id));
            $device = array();
            if(!empty($item)){
                 $piclist = unserialize($item['thumbs']);    
                 $device = unserialize($item['device']);
            }
            if(!$device || count($device)<=0){
                $device = array(
                    array('isdel'=>0,'value'=>'有线上网'), 
                    array('isdel'=>0,'isshow'=>0, 'value'=>'WIFI无线上网'), 
                      array('isdel'=>0,'isshow'=>0,'value'=>'可提供早餐'), 
                     array('isdel'=>0,'isshow'=>0,'value'=> '免费停车场'), 
                     array('isdel'=>0,'isshow'=>0,'value'=> '会议室'), 
                     array('isdel'=>0,'isshow'=>0,'value'=> '健身房'), 
                     array('isdel'=>0,'isshow'=>0,'value'=> '游泳池')
                 );
           }
           //品牌
           $brands =pdo_fetchall("select * from ".tablename('hotel2_brand')." where weid=:weid ",array(":weid"=>$weid));
            include $this->template('hotel_form');
        } else if ($op == 'delete') {

            $id = intval($_GPC['id']);

            if (!empty($id)) {
                $item = pdo_fetch("SELECT id FROM " . tablename('hotel2_order') . " WHERE hotelid = :hotelid LIMIT 1", array(':hotelid' => $id));
                if (!empty($item)) {
                    message('抱歉，请先删除该酒店的订单,再删除该酒店！', '', 'error');
                }
            } else{
                message('抱歉，参数错误！', '', 'error');
            }

            pdo_delete("hotel2_order", array("hotelid" => $id));
            pdo_delete("hotel2_room", array("hotelid" => $id));
            pdo_delete("hotel2", array("id" => $id));
            
            message("酒店信息删除成功!",  referer(),"success");
            
        } else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    $item = pdo_fetch("SELECT id FROM " . tablename('hotel2_order') . " WHERE hotelid = :hotelid LIMIT 1", array(':hotelid' => $id));
                    if (!empty($item)) {
                        message('抱歉，请先删除该酒店的订单,再删除该酒店！', '', 'error');
                    }
                } else{
                    message('抱歉，参数错误！', '', 'error');
                }

                pdo_delete("hotel2_order", array("hotelid" => $id));
                pdo_delete("hotel2_room", array("hotelid" => $id));
                pdo_delete("hotel2", array("id" => $id));
            }
            $this->message('酒店信息删除成功！', '', 0);
            exit();
        } else if ($op == 'showall') {
            if ($_GPC['show_name'] == 'showall') {
                $show_status = 1;
            } else {
                $show_status = 0;
            }

            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    pdo_update('hotel2', array('status' => $show_status), array('id' => $id));
                }
            }
            $this->message('操作成功！', '', 0);
            exit();
        } else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('hotel2', array('status' => $_GPC['status']), array('id' => $id));
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        }  else if($op=='query'){
               $kwd = trim($_GPC['keyword']);

               $sql = 'SELECT id,title FROM ' . tablename('hotel2') . ' WHERE `weid`=:weid';
               $params = array();
               $params[':weid'] = $_W['weid'];
               if (!empty($kwd)) {
                   $sql.=" AND `title` LIKE :title";
                   $params[':title'] = "%{$kwd}%";
               }
               $ds = pdo_fetchall($sql, $params);
               include $this->template('query');
        
        }
        
        else {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND `title` LIKE :keywords';
                $params[':keywords'] = "%{$_GPC['title']}%";
            }
            if (!empty($_GPC['level'])) {
                $sql .= ' AND level=:level';
                $params[':level'] = intval($_GPC['level']);
            }
            
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename('hotel2') . " WHERE weid = '{$_W['weid']}'  $sql ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            foreach($list as &$row){
                $row['level'] = $this->_hotel_level_config[$row['level']];
            }
            unset($row);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel2') . " WHERE weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('hotel');
        }
    }
    
    public function settingsDisplay($settings) {
        global $_GPC, $_W;
        if (checksubmit()) {
            if (empty($_GPC['sendmail']) || empty($_GPC['senduser']) || empty($_GPC['sendpwd'])) {
                message('请完整填写邮件配置信息', 'refresh', 'error');
            }
            if ($_GPC['host'] == 'smtp.qq.com' || $_GPC['host'] == 'smtp.gmail.com') {
                $secure = 'ssl';
                $port = '465';
            } else {
                $secure = 'tls';
                $port = '25';
            }
            $result = $this->sendmail($_GPC['host'], $secure, $port, $_GPC['sendmail'], $_GPC['senduser'], $_GPC['sendpwd'], $_GPC['sendmail']);
            $cfg = array(
                'host' => $_GPC['host'],
                'secure' => $secure,
                'port' => $port,
                'sendmail' => $_GPC['sendmail'],
                'senduser' => $_GPC['senduser'],
                'sendpwd' => $_GPC['sendpwd'],
                'status' => $result
            );
            if ($result == 1) {
                $this->saveSettings($cfg);
                message('邮箱配置成功', 'refresh');
            } else {
                message('邮箱配置信息有误', 'refresh', 'error');
            }
        }
        include $this->template('setting');
    }

    public function doCopyroom() {
        global $_GPC, $_W;

        $hotelid = $_GPC['hotelid'];
        $roomid = $_GPC['roomid'];

        if (empty($hotelid) || empty($roomid)) {
            message('参数错误', 'refresh', 'error');
        }

        $item = pdo_fetch("SELECT * FROM " . tablename('hotel2_room') . " WHERE id = :id", array(':id' => $roomid));

        unset($item['id']);
        $item['status'] = 0;
        //$item['sortid'] = $roomid;

        pdo_insert('hotel2_room', $item);
        $id = pdo_insertid();
        $url = $this->createWebUrl('room',array('op'=>'edit','hotelid'=>$hotelid,'id'=>$id));
        header("Location: $url");
        exit;
    }

    //批量修改房价
    public function doRoom_price() {
        global $_GPC, $_W;

        $hotelid = $_GPC['hotelid'];
        $weid = $_W['weid'];

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            if (empty($_GPC['start']) || empty($_GPC['end'])) {
                die(json_encode(array("result" => 0, "error" => "请选择时间")));
            }
            $start =$_GPC['start'];
            $end = $_GPC['end'];
          
            $btime = strtotime($start);
            $etime = strtotime($end);

            //日期列
            $days = ceil(($etime - $btime) / 86400);

            $pagesize = 10;
            $totalpage =ceil($days / $pagesize);
            $page = intval($_GPC['page']); 
            if($page>$totalpage){
                $page = $totalpage;
            }else if($page<=1){
                $page = 1;
            }
            $currentindex =  ($page-1) * $pagesize;
            $start = date('Y-m-d',strtotime( date('Y-m-d')."+$currentindex day" ));
            
           // echo "start=".$start;
            //btime = strtotime( date('Y-m-d' ,strtotime("$start +$pagesize day")));
          //  echo "end=".date('Y-m-d' ,strtotime("$start +$pagesize day"));
            $btime=  strtotime($start);
            $etime = strtotime( date('Y-m-d' ,strtotime("$start +$pagesize day")));
            $date_array = array();
            $date_array[0]['date'] = $start;
            $date_array[0]['day'] = date('j', $btime);
            $date_array[0]['time'] = $btime;
            $date_array[0]['month'] = date('m',$btime);

            for($i = 1; $i <= $pagesize; $i++) {
                $date_array[$i]['time'] = $date_array[$i-1]['time'] + 86400;
                $date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
                $date_array[$i]['day'] = date('j', $date_array[$i]['time']);
                $date_array[$i]['month'] = date('m', $date_array[$i]['time']);
            }
 
            $params = array();
            $sql = "SELECT r.* FROM " . tablename('hotel2_room') . "as r";
            //$sql .= " LEFT JOIN ". tablename('hotel2_room_price') . "as p ON r.id = p.roomid";
            $sql .= " WHERE 1 = 1";
            $sql .= " AND r.hotelid = $hotelid";
            $sql .= " AND r.weid = $weid";

            $list = pdo_fetchall($sql, $params);

            foreach ($list as $key => $value) {
                $sql = "SELECT * FROM " . tablename('hotel2_room_price');
                $sql .= " WHERE 1 = 1";
                $sql .= " AND roomid = " . $value['id'];
                $sql .= " AND roomdate >= " . $btime;
                $sql .= " AND roomdate < " . ($etime + 86400);

                $item = pdo_fetchall($sql);

                if ($item) {
                    $flag = 1;
                } else {
                    $flag = 0;
                }
                $list[$key]['price_list'] = array();
                if ($flag == 1) {
                    for($i = 0; $i <= $pagesize; $i++) {
                        $k = $date_array[$i]['time'];
                        foreach ($item as $p_key => $p_value) {
                            //判断价格表中是否有当天的数据
                            if($p_value['roomdate'] == $k) {
                                $list[$key]['price_list'][$k]['oprice'] = $p_value['oprice'];
                                $list[$key]['price_list'][$k]['cprice'] = $p_value['cprice'];
                                $list[$key]['price_list'][$k]['mprice'] = $p_value['mprice'];
                                $list[$key]['price_list'][$k]['roomid'] = $value['id'];
                                $list[$key]['price_list'][$k]['hotelid'] =$hotelid;
                                $list[$key]['price_list'][$k]['has'] = 1;
                                break;
                            }
                        }
                        //价格表中没有当天数据
                        if (empty($list[$key]['price_list'][$k]['oprice'])) {
                            $list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
                            $list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
                            $list[$key]['price_list'][$k]['mprice'] = $value['mprice'];
                            $list[$key]['price_list'][$k]['roomid'] = $value['id'];
                            $list[$key]['price_list'][$k]['hotelid'] =$hotelid;
                        }
                    }
                } else {
                    //价格表中没有数据
                    for($i = 0; $i <= $pagesize; $i++) {
                        $k = $date_array[$i]['time'];
                        $list[$key]['price_list'][$k]['oprice'] = $value['oprice'];
                        $list[$key]['price_list'][$k]['cprice'] = $value['cprice'];
                        $list[$key]['price_list'][$k]['mprice'] = $value['mprice'];
                        $list[$key]['price_list'][$k]['roomid'] = $value['id'];
                        $list[$key]['price_list'][$k]['hotelid'] =$hotelid;
                    }
                }

            }

            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('room_price_list');
            $data['code'] = ob_get_contents();
            ob_clean();

            die(json_encode($data));
        } else if($ac=='submitPrice'){  //修改价格
            $hotelid =intval($_GPC['hotelid']);
            $roomid = intval($_GPC['roomid']);
            $price = $_GPC['price'];
            $pricetype = $_GPC['pricetype'];
            $date = $_GPC['date'];
            $roomprice =$this->getRoomPrice($hotelid, $roomid, $date);
            $roomprice[$pricetype] = $price;
             if(empty($roomprice['id'])){
                 pdo_insert("hotel2_room_price",$roomprice);
             }
             else{
                 pdo_update("hotel2_room_price",$roomprice,array("id"=>$roomprice['id']));
             }
             die(json_encode(array("result"=>1,"hotelid"=>$hotelid,"roomid"=>$roomid,"pricetype"=>$pricetype,"price"=>$price)));
        }
        else if($ac=='updatelot'){
            //批量修改房价
            $startime = time();
            $firstday = date('Y-m-01', time());
              //当月最后一天
            $endtime = strtotime( date('Y-m-d', strtotime("$firstday +1 month -1 day")) );
            $rooms = pdo_fetchall("select * from ".tablename("hotel2_room")." where hotelid=".$hotelid);
            include $this->template('room_price_lot');
            exit();
            
        }
        else if($ac=='updatelot_create'){
            $rooms = $_GPC['rooms'];
            if(empty($rooms)){
                die("");
            }
            $days = $_GPC['days'];
            $days_arr  = implode(",",$days);
            $rooms_arr  = implode(",",$rooms);
            $start= $_GPC['start'];
            $end = $_GPC['end'];
            $list = pdo_fetchall("select * from ".tablename("hotel2_room")." where id in (". implode(",",$rooms).")");
            ob_start();
            include $this->template('room_price_lot_list');
            $data['result'] = 1;
            $data['code'] = ob_get_contents();
            ob_clean();
            die(json_encode($data));
        }else if($ac=='updatelot_submit'){
            $rooms = $_GPC['rooms'];
            $rooms_arr = explode(",",$rooms);
            $days = $_GPC['days'];
            $days_arr = explode(",",$days);
            $oprices = $_GPC['oprice'];
            $cprices = $_GPC['cprice'];
            $mprices =$_GPC['mprice'];
            $start = strtotime($_GPC['start']);
            $end = strtotime($_GPC['end']);
            foreach($rooms_arr as $v){
                for($time = $start;$time<=$end;$time+=86400){
                      $week = date('w',$time);
                      if(in_array($week,$days_arr)){
                            $roomprice = $this->getRoomPrice($hotelid, $v,date('Y-m-d',$time));
                            $roomprice['oprice'] =  $oprices[$v];
                            $roomprice['cprice'] =  $cprices[$v];
                            $roomprice['mprice'] = $mprices[$v];
                             if(empty($roomprice['id'])){
                                pdo_insert("hotel2_room_price",$roomprice);
                            }
                            else{
                                pdo_update("hotel2_room_price",$roomprice,array("id"=>$roomprice['id']));
                            }
                      }
                }
            }
            message("批量修改房价成功!",$this->createWebUrl('room_price',array("hotelid"=>$hotelid)),"success");
        }

        $startime = time();
        $firstday = date('Y-m-01', time());
        //当月最后一天
        $endtime = strtotime( date('Y-m-d', strtotime("$firstday +1 month -1 day")) );
        include $this->template('room_price');

    }
    
    
    
     //批量修改房价
    public function doRoom_status() {
        global $_GPC, $_W;

        $hotelid = $_GPC['hotelid'];
        $weid = $_W['weid'];

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            if (empty($_GPC['start']) || empty($_GPC['end'])) {
                die(json_encode(array("result" => 0, "error" => "请选择时间")));
            }
            $start =$_GPC['start'];
            $end = $_GPC['end'];
          
            $btime = strtotime($start);
            $etime = strtotime($end);

            //日期列
            $days = ceil(($etime - $btime) / 86400);
            
            $pagesize = 10;
            $totalpage =ceil($days / $pagesize);
            $page = intval($_GPC['page']); 
            if($page>$totalpage){
                $page = $totalpage;
            }else if($page<=1){
                $page = 1;
            }
            $currentindex =  ($page-1) * $pagesize;
            $start = date('Y-m-d',strtotime( date('Y-m-d')."+$currentindex day" ));
          
            $btime=  strtotime($start);
            $etime = strtotime( date('Y-m-d' ,strtotime("$start +$pagesize day")));
            $date_array = array();
            $date_array[0]['date'] = $start;
            $date_array[0]['day'] = date('j', $btime);
            $date_array[0]['time'] = $btime;
            $date_array[0]['month'] = date('m',$btime);
            
            for($i = 1; $i <= $pagesize; $i++) {
                $date_array[$i]['time'] = $date_array[$i-1]['time'] + 86400;
                $date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
                $date_array[$i]['day'] = date('j', $date_array[$i]['time']);
                $date_array[$i]['month'] = date('m', $date_array[$i]['time']);
            }
 
            $params = array();
            $sql = "SELECT r.* FROM " . tablename('hotel2_room') . "as r";
            //$sql .= " LEFT JOIN ". tablename('hotel2_room_price') . "as p ON r.id = p.roomid";
            $sql .= " WHERE 1 = 1";
            $sql .= " AND r.hotelid = $hotelid";
            $sql .= " AND r.weid = $weid";

            $list = pdo_fetchall($sql, $params);

            foreach ($list as $key => $value) {
                $sql = "SELECT * FROM " . tablename('hotel2_room_price');
                $sql .= " WHERE 1 = 1";
                $sql .= " AND roomid = " . $value['id'];
                $sql .= " AND roomdate >= " . $btime;
                $sql .= " AND roomdate < " . ($etime + 86400);

                $item = pdo_fetchall($sql);

                if ($item) {
                    $flag = 1;
                } else {
                    $flag = 0;
                }
                $list[$key]['price_list'] = array();
                if ($flag == 1) {
                    for($i = 0; $i <= $pagesize; $i++) {
                        $k = $date_array[$i]['time'];
             
                        foreach ($item as $p_key => $p_value) {
                            //判断价格表中是否有当天的数据
                            if($p_value['roomdate'] == $k) {
                                
                                $list[$key]['price_list'][$k]['status'] = $p_value['status'];
                                if (empty($p_value['num'])) {
                                    $list[$key]['price_list'][$k]['num'] = 0;
                                } else if ($p_value['num'] == -1) {
                                    $list[$key]['price_list'][$k]['num'] = "不限";
                                } else {
                                    $list[$key]['price_list'][$k]['num'] = $p_value['num'];
                                }
                                $list[$key]['price_list'][$k]['roomid'] = $value['id'];
                                $list[$key]['price_list'][$k]['hotelid'] =$hotelid;
                                $list[$key]['price_list'][$k]['has'] = 1;
                                break;
                            }
                        }
                        //价格表中没有当天数据
                        if (empty($list[$key]['price_list'][$k])) {
                            $list[$key]['price_list'][$k]['num'] = "不限";
                            $list[$key]['price_list'][$k]['status'] = 1;
                            $list[$key]['price_list'][$k]['roomid'] = $value['id'];
                            $list[$key]['price_list'][$k]['hotelid'] =$hotelid;
                        }
                    }
                } else {
                    //价格表中没有数据
                    for($i = 0; $i <= $pagesize; $i++) {
                        $k = $date_array[$i]['time'];
                        $list[$key]['price_list'][$k]['num'] = "不限";
                        $list[$key]['price_list'][$k]['status'] = 1;
                        $list[$key]['price_list'][$k]['roomid'] = $value['id'];
                        $list[$key]['price_list'][$k]['hotelid'] =$hotelid;
                    }
                }

            }
 
            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('room_status_list');
            $data['code'] = ob_get_contents();
            ob_clean();

            die(json_encode($data));
        } else if($ac=='submitPrice'){  //修改价格
            $hotelid =intval($_GPC['hotelid']);
            $roomid = intval($_GPC['roomid']);
            $price = $_GPC['price'];
            $pricetype = $_GPC['pricetype'];
            $date = $_GPC['date'];
            $roomprice =$this->getRoomPrice($hotelid, $roomid, $date);
            if($pricetype=='num'){
               $roomprice['num'] = $_GPC['price'];
             }
             else{
               $roomprice['status'] = $_GPC['status']; 
             }
             
             if(empty($roomprice['id'])){
                 pdo_insert("hotel2_room_price",$roomprice);
             }
             else{
                 pdo_update("hotel2_room_price",$roomprice,array("id"=>$roomprice['id']));
             }
             die(json_encode(array("result"=>1,"hotelid"=>$hotelid,"roomid"=>$roomid,"pricetype"=>$pricetype,"price"=>$price)));
        }
        else if($ac=='updatelot'){
            //批量修改房价
            $startime = time();
            $firstday = date('Y-m-01', time());
              //当月最后一天
            $endtime = strtotime( date('Y-m-d', strtotime("$firstday +1 month -1 day")) );
            $rooms = pdo_fetchall("select * from ".tablename("hotel2_room")." where hotelid=".$hotelid);
            include $this->template('room_status_lot');
            exit();
            
        }
        else if($ac=='updatelot_create'){
            $rooms = $_GPC['rooms'];
            if(empty($rooms)){
                die("");
            }
            $days = $_GPC['days'];
            $days_arr  = implode(",",$days);
            $rooms_arr  = implode(",",$rooms);
            $start= $_GPC['start'];
            $end = $_GPC['end'];
            $list = pdo_fetchall("select * from ".tablename("hotel2_room")." where id in (". implode(",",$rooms).")");
            ob_start();
            include $this->template('room_status_lot_list');
            $data['result'] = 1;
            $data['code'] = ob_get_contents();
            ob_clean();
            die(json_encode($data));
        }else if($ac=='updatelot_submit'){
            $rooms = $_GPC['rooms'];
            $rooms_arr = explode(",",$rooms);
            $days = $_GPC['days'];
            $days_arr = explode(",",$days);
            $nums = $_GPC['num'];
            $statuses = $_GPC['status'];
            $start = strtotime($_GPC['start']);
            $end = strtotime($_GPC['end']);
            foreach($rooms_arr as $v){
                for($time = $start;$time<=$end;$time+=86400){
                      $week = date('w',$time);
                      if(in_array($week,$days_arr)){
                            $roomprice = $this->getRoomPrice($hotelid, $v,date('Y-m-d',$time));
                            $roomprice['num'] =  $nums[$v];
                            $roomprice['status'] =  $statuses[$v];
                             if(empty($roomprice['id'])){
                                pdo_insert("hotel2_room_price",$roomprice);
                            }
                            else{
                                pdo_update("hotel2_room_price",$roomprice,array("id"=>$roomprice['id']));
                            }
                      }
                }
            }
            message("批量修改房量房态成功!",$this->createWebUrl('room_status',array("hotelid"=>$hotelid)),"success");
        }

        $startime = time();
        $firstday = date('Y-m-01', time());
        //当月最后一天
        $endtime = strtotime( date('Y-m-d', strtotime("$firstday +1 month -1 day")) );
        include $this->template('room_status');

    }
    
    //获取房型某天的记录
    private function getRoomPrice($hotelid,$roomid,$date){
        global $_W;
         $btime = strtotime($date);
         $sql = "SELECT * FROM " . tablename('hotel2_room_price');
         $sql .= " WHERE 1 = 1";
         $sql .=" and weid=".$_W['weid'];
         $sql .= " AND hotelid = " . $hotelid;
         $sql .= " AND roomid = " . $roomid;
         $sql .= " AND roomdate = " . $btime;
         $sql .=" limit 1";
         $roomprice = pdo_fetch($sql);
         
         if(empty($roomprice)){
                 $room =$this->getRoom($hotelid, $roomid);
                 $roomprice = array(
                     "weid"=>$_W['weid'],
                     "hotelid"=>$hotelid,
                     "roomid"=>$roomid,
                     "oprice"=>$room['oprice'],
                     "cprice"=>$room['cprice'],
                     "mprice"=>$room['mprice'],
                     "status"=>$room['status'],
                     "roomdate"=>  strtotime( $date ),
                     "thisdate"=>$date,
                     "num"=>"-1",
                     "status"=>1,
                 );
         }
         return $roomprice;
             
    }
    
    private function getRoom($hotelid,$roomid){
         $sql = "SELECT * FROM " . tablename('hotel2_room');
         $sql .= " WHERE 1 = 1";
         $sql .= " AND hotelid = " . $hotelid;
         $sql .= " AND id = " . $roomid;
         $sql .=" limit 1";
         return pdo_fetch($sql);
    }
  
    public function doRoom() {
        global $_GPC, $_W;
        $op = $_GPC['op'];
        if ($op == 'edit') {
            $id = intval($_GPC['id']);
            $hotelid = intval($_GPC['hotelid']);
            $hotel = pdo_fetch("select id,title from ".tablename('hotel2')."where id=:id limit 1",array(":id"=>$hotelid));
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('hotel2_room') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，房型不存在或是已经删除！', '', 'error');
                }
                //print_r($item);exit;
                $piclist = unserialize($item['thumbs']);
            }
            
            if (checksubmit('submit')) {
                if (empty($_GPC['title'])) {
                    message('请输入房型！');
                }
                $data = array(
                    'weid' => $_W['weid'],
                    'hotelid' => $hotelid,
                    'title' => $_GPC['title'],
                    'breakfast' => $_GPC['breakfast'],
                    'oprice' => $_GPC['oprice'],
                    'cprice' => $_GPC['cprice'],
                    'mprice' => $_GPC['mprice'],
                    'area' => $_GPC['area'],
                    'area_show' => $_GPC['area_show'],
                    'bed' => $_GPC['bed'],
                    'bed_show' => $_GPC['bed_show'],
                    'bedadd' => $_GPC['bedadd'],
                    'bedadd_show' => $_GPC['bedadd_show'],
                    'persons' => $_GPC['persons'],
                    'persons_show' => $_GPC['persons_show'],
                    'sales' => $_GPC['sales'],
                    'device' => $_GPC['device'],
                    'floor' => $_GPC['floor'],
                    'floor_show' => $_GPC['floor_show'],
                    'smoke' => $_GPC['smoke'],
                    'smoke_show' => $_GPC['smoke_show'],
                    'score' => intval( $_GPC['score'] ),
                    'status'=>$_GPC['status'],
                );
                if (!empty($_FILES['thumb']['tmp_name'])) {
                    file_delete($_GPC['thumb-old']);
                    $upload = file_upload($_FILES['thumb']);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
                    $data['thumb'] = $upload['path'];
                }
                $cur_index = 0;
                if (!empty($_GPC['attachment-new'])) {
                    foreach ($_GPC['attachment-new'] as $index => $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $hsdata[$index] = array(
                            'attachment' => $_GPC['attachment-new'][$index],
                        );
                    }
                    $cur_index = $index + 1;
                }
                if (!empty($_GPC['attachment'])) {
                    foreach ($_GPC['attachment'] as $index => $row) {
                        if (empty($row)) {
                            continue;
                        }
                        $hsdata[$cur_index + $index] = array(
                            'attachment' => $_GPC['attachment'][$index]
                        );
                    }
                }

                $data['thumbs'] = serialize($hsdata);

                if (empty($id)) {
                    pdo_insert('hotel2_room', $data);
                } else {
                    pdo_update('hotel2_room', $data, array('id' => $id));
                }
                pdo_query("update " . tablename('hotel2') . " set roomcount=(select count(*) from " . tablename('hotel2_room') . " where hotelid=:hotelid) where id=:hotelid", array(":hotelid" => $hotelid));
                message('房型信息更新成功！', $this->createWebUrl('room', array('hotelid' => $hotelid)), 'success');
            }
            include $this->template('room_form');
            
        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);

            if (!empty($id)) {
                $item = pdo_fetch("SELECT id FROM " . tablename('hotel2_order') . " WHERE roomid = :roomid LIMIT 1", array(':roomid' => $id));
                if (!empty($item)) {
                    message('抱歉，请先删除该房间的订单,再删除该房间！', '', 'error');
                }
            } else{
                message('抱歉，参数错误！', '', 'error');
            }

            pdo_delete('hotel2_room', array('id' => $id));
            pdo_delete('hotel2_order', array('roomid' => $id));
            pdo_query("update " . tablename('hotel2') . " set roomcount=(select count(*) from " . tablename('hotel2_room') . " where hotelid=:hotelid) where id=:hotelid", array(":hotelid" => $id));
            
            message('删除成功！', referer(), 'success');
        }  else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    $item = pdo_fetch("SELECT id FROM " . tablename('hotel2_order') . " WHERE roomid = :roomid LIMIT 1", array(':roomid' => $id));
                    if (!empty($item)) {
                        message('抱歉，请先删除该房间的订单,再删除该房间！', '', 'error');
                    }
                } else{
                    message('抱歉，参数错误！', '', 'error');
                }

                pdo_delete('hotel2_room', array('id' => $id));
                pdo_delete('hotel2_order', array('roomid' => $id));
                pdo_query("update " . tablename('hotel2') . " set roomcount=(select count(*) from " . tablename('hotel2_room') . " where hotelid=:hotelid) where id=:hotelid", array(":hotelid" => $id));
            }
            $this->message('删除成功！', '', 0);
            exit();
        } else if ($op == 'showall') {
            if ($_GPC['show_name'] == 'showall') {
                $show_status = 1;
            } else {
                $show_status = 0;
            }

            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    pdo_update('hotel2_room', array('status' => $show_status), array('id' => $id));
                }
            }
            $this->message('操作成功！', '', 0);
            exit();
        }else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('hotel2_room', array('status' => $_GPC['status']), array('id' => $id));
            
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        } else {
            $hotelid = intval( $_GPC['hotelid']);
            $hotel = pdo_fetch("select title from ".tablename('hotel2')."where id=:id limit 1",array(":id"=>$hotelid));
            
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND `title` LIKE :keywords';
                $params[':keywords'] = "%{$_GPC['title']}%";
            }
            if(!empty($hotelid)){
                $sql.=' and r.hotelid=:hotelid';
                $params[':hotelid'] = $hotelid;
            }
            if (!empty($_GPC['title'])) {
                $sql .= ' AND r.title LIKE :keywords';
                $params[':keywords'] = "%{$_GPC['title']}%";
            }
            if (!empty($_GPC['hoteltitle'])) {
                $sql .= ' AND h.title LIKE :keywords';
                $params[':keywords'] = "%{$_GPC['hoteltitle']}%";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT r.*,h.title as hoteltitle FROM " . tablename('hotel2_room') . " r left join ".tablename('hotel2')." h on r.hotelid = h.id WHERE r.weid = '{$_W['weid']}' $sql ORDER BY h.id, r.displayorder, r.sortid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel2_room') . " r left join ".tablename('hotel2')." h on r.hotelid = h.id WHERE r.weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('room');
        }
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

    public function doOrder() {
        global $_GPC, $_W;
        checklogin();
        $hotelid = intval($_GPC['hotelid']);
        $hotel = pdo_fetch("select id,title from ".tablename('hotel2')." where id=:id limit 1",array(":id"=>$hotelid));
        $roomid = intval($_GPC['roomid']);
        $room = pdo_fetch("select id,title from ".tablename('hotel2_room')." where id=:id limit 1",array(":id"=>$roomid));

        $op = $_GPC['op'];
        if($op=='edit'){
            $id = $_GPC['id'];
             if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('hotel2_order') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，订单不存在或是已经删除！', '', 'error');
                }
            }
            if (checksubmit('submit')) {
                $old_status = $_GPC['old_status'];

                $data = array(
                    'status' => $_GPC['status'],
                    'msg' => $_GPC['msg'],
                    'mngtime' => time(),
                );

                $params = array();
                $sql = "SELECT id, roomdate, num FROM " . tablename('hotel2_room_price');
                $sql .= " WHERE 1 = 1";
                $sql .= " AND roomid = :roomid";
                $sql .= " AND roomdate >= :btime AND roomdate < :etime";
                $sql .= " AND status = 1";

                $params[':roomid'] = $item['roomid'];
                $params[':btime'] = $item['btime'];
                $params[':etime'] = $item['etime'];

                //订单确认
                if ($data['status'] == 1 && $old_status != 1) {
                    $room_date_list = pdo_fetchall($sql, $params);
                    if ($room_date_list) {
                        //$change_data = array();

                        foreach ($room_date_list as $key => $value) {
                            $num = $value['num'];
                            if ($num > 0) {
                                if ($num > $item['nums']) {
                                    $now_num = $num - $item['nums'];
                                } else {
                                    $now_num = 0;
                                }
                                pdo_update('hotel2_room_price', array('num' => $now_num), array('id' => $value['id']));
                            }
                        }
                    }
                }

                //订单取消
                //print_r($old_status . '=>' . $data['status']); exit;
                if ($old_status == 1 && ($data['status'] == -1 || $data['status'] == 2)) {
                    $room_date_list = pdo_fetchall($sql, $params);
                    if ($room_date_list) {
                        foreach ($room_date_list as $key => $value) {
                            $num = $value['num'];
                            if ($num >= 0) {
                                $now_num = $num + $item['nums'];
                                pdo_update('hotel2_room_price', array('num' => $now_num), array('id' => $value['id']));
                            }
                        }
                    }
                }

                pdo_update('hotel2_order', $data, array('id' => $id));
                message('订单信息处理完成！', $this->createWebUrl('order',array('hotelid'=>$hotelid,"roomid"=>$roomid)) , 'success');    
            }

            $btime = $item['btime'];
            $etime = $item['etime'];

            $start = date('m-d', $btime);
            $end = date('m-d', $etime);

            //日期列
            $days = ceil(($etime - $btime) / 86400);

            //print_r($days);exit;

            $date_array = array();
            $date_array[0]['date'] = $start;
            $date_array[0]['day'] = date('j', $btime);
            $date_array[0]['time'] = $btime;
            $date_array[0]['month'] = date('m',$btime);

            if ($days > 1) {
                for($i = 1; $i < $days; $i++) {
                    $date_array[$i]['time'] = $date_array[$i-1]['time'] + 86400;
                    $date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
                    $date_array[$i]['day'] = date('j', $date_array[$i]['time']);
                    $date_array[$i]['month'] = date('m', $date_array[$i]['time']);
                }
            }

            //print_r($date_array);exit;

            $sql = "SELECT id, roomdate, num, status FROM " . tablename('hotel2_room_price');
            $sql .= " WHERE 1 = 1";
            $sql .= " AND roomid = :roomid";
            $sql .= " AND roomdate >= :btime AND roomdate < :etime";
            $sql .= " AND status = 1";

            $params[':roomid'] = $item['roomid'];
            $params[':btime'] = $item['btime'];
            $params[':etime'] = $item['etime'];

            $room_date_list = pdo_fetchall($sql, $params);

            if ($room_date_list) {
                $flag = 1;
            } else {
                $flag = 0;
            }
            $list = array();

            if ($flag == 1) {
                for($i = 0; $i < $days; $i++) {
                    $k = $date_array[$i]['time'];

                    foreach ($room_date_list as $p_key => $p_value) {
                        //判断价格表中是否有当天的数据
                        if($p_value['roomdate'] == $k) {
                            $list[$k]['status'] = $p_value['status'];
                            if (empty($p_value['num'])) {
                                $list[$k]['num'] = 0;
                            } else if ($p_value['num'] == -1) {
                                $list[$k]['num'] = "不限";
                            } else {
                                $list[$k]['num'] =  $p_value['num'];
                            }
                            $list[$k]['has'] = 1;
                            break;
                        }
                    }
                    //价格表中没有当天数据
                    if (empty($list[$k])) {
                        $list[$k]['num'] = "不限";
                        $list[$k]['status'] = 1;
                    }
                }
            } else {
                //价格表中没有数据
                for($i = 0; $i < $days; $i++) {
                    $k = $date_array[$i]['time'];
                    $list[$k]['num'] = "不限";
                    $list[$k]['status'] = 1;
                }
            }

            //print_r($list);exit;

            $member_info = pdo_fetch("SELECT from_user,isauto FROM " . tablename('hotel2_member') . " WHERE id = :id LIMIT 1", array(':id' => $item['memberid']));

            include $this->template('order_form');
        }
        else if($op=='delete'){
             $id = intval($_GPC['id']);
             $item = pdo_fetch("SELECT id FROM " . tablename('hotel2_order') . " WHERE id = :id LIMIT 1", array(':id' => $id));

             if (empty($item)) {
                  message('抱歉，订单不存在或是已经删除！', '', 'error');
             }
             pdo_delete('hotel2_order', array('id' => $id));
             message('删除成功！', referer(), 'success');
        }
        else{
      
            $weid = $_W['weid'];
            $realname = $_GPC['realname'];
            $mobile =$_GPC['mobile'];
            $ordersn =$_GPC['ordersn'];
            $roomtitle = $_GPC['roomtitle'];
            $hoteltitle = $_GPC['hoteltitle'];
            $condition = '';
            $params = array();
            if (!empty($hoteltitle)) {
                $condition .= ' AND h.title LIKE :hoteltitle';
                $params[':hoteltitle'] = "%{$hoteltitle}%";
            }
            if (!empty($roomtitle)) {
                $condition .= ' AND r.title LIKE :roomtitle';
                $params[':roomtitle'] = "%{$roomtitle}%";
            }
            
            if (!empty($realname)) {
                $condition .= ' AND o.name LIKE :realname';
                $params[':realname'] = "%{$realname}%";
            }
            if (!empty($mobile)) {
                $condition .= ' AND o.mobile LIKE :mobile';
                $params[':mobile'] = "%{$mobile}%";
            }
            if (!empty($ordersn)) {
                $condition .= ' AND o.ordersn LIKE :ordersn';
                $params[':ordersn'] = "%{$ordersn}%";
            }
            if(!empty($hotelid)){
                $condition.=" and o.hotelid=".$hotelid;
            }
            if(!empty($roomid)){
                $condition.=" and o.roomid=".$roomid;
            }
            $status = $_GPC['status'];
            if($status!=''){
                $condition.=" and o.status=".intval($status);
            }
            $paystatus = $_GPC['paystatus'];
            if($paystatus!=''){
                $condition.=" and o.paystatus=".intval($paystatus);
            }
 
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT o.*,h.title as hoteltitle,r.title as roomtitle FROM " . tablename('hotel2_order') . " o left join " . tablename('hotel2') . 
                    "h on o.hotelid=h.id left join ".tablename("hotel2_room")." r on r.id = o.roomid  WHERE o.weid = '{$_W['weid']}' $condition ORDER BY o.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,$params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM  ' . tablename('hotel2_order') . " o left join " . tablename('hotel2') . 
                    "h on o.hotelid=h.id left join ".tablename("hotel2_room")." r on r.id = o.roomid  WHERE o.weid = '{$_W['weid']}' $condition",$params);

            $pager = pagination($total, $pindex, $psize);
            include $this->template('order');
        }
    }
    
     public function doMember() {
        global $_GPC, $_W;
        $op = $_GPC['op'];
        if ($op == 'edit') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('hotel2_member') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，用户不存在或是已经删除！', '', 'error');
                }
            }
            
            if (checksubmit('submit')) {
                $data = array(
                    'weid' => $_W['weid'],
                    'username' => $_GPC['username'],
                    'realname' => $_GPC['realname'],
                    'mobile' => $_GPC['mobile'],
                    'score'=>$_GPC['score'],
                    'userbind'=>$_GPC['userbind'],
                    'isauto'=>$_GPC['isauto'],
                    'status'=>$_GPC['status'],
                );
                if(!empty($_GPC['password'])){
                    $data['salt'] = random(8);
                    $data['password'] = hotel_member_hash($_GPC['password'], $data['salt']);
                    //$data['password'] = md5($_GPC['password']);
                }

                if (empty($id)) {
                    $c = pdo_fetchcolumn("select count(*) from ".tablename('hotel2_member')." where username=:username ",array(":username"=>$data['username']));
                    if($c>0){
                        message("用户名 ".$data['username']." 已经存在!","","error");
                    }
                    $data['createtime'] = time();
                    pdo_insert('hotel2_member', $data);
                } else {
                    pdo_update('hotel2_member', $data, array('id' => $id));
                }
                message('用户信息更新成功！', $this->createWebUrl('member'), 'success');
            }
            include $this->template('member_form');
            
        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete('hotel2_member', array('id' => $id));
            pdo_delete('hotel2_order', array('memberid' => $id));
            message('删除成功！', referer(), 'success');
            
        }  else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {

                $id = intval($id);
                pdo_delete('hotel2_member', array('id' => $id));
                pdo_delete('hotel2_order', array('memberid' => $id));
            }
            $this->message('规则操作成功！', '', 0);
            exit();
        } else if ($op == 'showall') {
            if ($_GPC['show_name'] == 'showall') {
                $show_status = 1;
            } else {
                $show_status = 0;
            }

            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    pdo_update('hotel2_member', array('status' => $show_status), array('id' => $id));
                }
            }
            $this->message('操作成功！', '', 0);
            exit();
        } else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('hotel2_member', array('status' => $_GPC['status']), array('id' => $id));
            
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        } else {
            $sql = "";
            $params = array();
            if (!empty($_GPC['realname'])) {
                $sql .= ' AND `realname` LIKE :realname';
                $params[':realname'] = "%{$_GPC['realname']}%";
            }
             if (!empty($_GPC['mobile'])) {
                $sql .= ' AND `mobile` LIKE :mobile';
                $params[':mobile'] = "%{$_GPC['mobile']}%";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename('hotel2_member') . " WHERE weid = '{$_W['weid']}' $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel2_member') . " WHERE weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('member');
        }
    }

    
    
    public function dohotelset() {
        global $_GPC, $_W;

        $id = intval($_GPC['id']);
        if (checksubmit('submit')) {
            $data = array(
                "weid" => $_W['weid'],
                "location_p" => $_GPC['location_p'],
                "location_c" => $_GPC['location_c'],
                "version" => $_GPC['version'],
                "user" => $_GPC['user'],
                "reg" => $_GPC['reg'],
                "regcontent" => $_GPC['regcontent'],
                "bind" => $_GPC['bind'],
                "ordertype" => $_GPC['ordertype'],
                "paytype1" => $_GPC['paytype1'],
                "paytype2" => $_GPC['paytype2'],
                "paytype3" => $_GPC['paytype3'],
                "is_unify" => $_GPC['is_unify'],
                "tel" => $_GPC['tel'],
            );
            if (!empty($id)) {
                pdo_update("hotel2_set", $data, array("id" => $id));
            } else {
                pdo_insert("hotel2_set", $data);
            }
            message("保存设置成功!", referer(), "success");
        }

        $set = pdo_fetch("select * from " . tablename('hotel2_set') . " where weid=:weid limit 1", array(":weid" => $_W['weid']));
        if (empty($set)) {
            $set = array(
                "user" => 1,
                "reg" => 1,
                "bind" => 1,
            );
        }
        include $this->template("hotelset");
    }
    public function doBrand() {
        global $_GPC, $_W;
        $op = $_GPC['op'];
        if ($op == 'edit') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('hotel2_brand') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，品牌不存在或是已经删除！', '', 'error');
                }
            }
            
            if (checksubmit('submit')) {
                $data = array(
                    'weid' => $_W['weid'],
                    'title' => $_GPC['title'],
                    'status' => $_GPC['status'],
                );

                if (empty($id)) {
                    pdo_insert('hotel2_brand', $data);
                } else {
                    pdo_update('hotel2_brand', $data, array('id' => $id));
                }
                message('品牌信息更新成功！', $this->createWebUrl('brand'), 'success');
            }
            include $this->template('brand_form');
            
        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete('hotel2_brand', array('id' => $id));
            message('删除成功！', referer(), 'success');
            
        }  else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {

                $id = intval($id);
                pdo_delete('hotel2_brand', array('id' => $id));
            }
            $this->message('规则操作成功！', '', 0);
            exit();
        } else if ($op == 'showall') {
            if ($_GPC['show_name'] == 'showall') {
                $show_status = 1;
            } else {
                $show_status = 0;
            }

            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    pdo_update('hotel2_brand', array('status' => $show_status), array('id' => $id));
                }
            }
            $this->message('操作成功！', '', 0);
            exit();
        }  else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('hotel2_brand', array('status' => $_GPC['status']), array('id' => $id));
            
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        }else {
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND `title` LIKE :title';
                $params[':title'] = "%{$_GPC['title']}%";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename('hotel2_brand') . " WHERE weid = '{$_W['weid']}' $sql ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel2_brand') . " WHERE weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('brand');
        }
    }

     public function doGetBusiness(){
         global $_W,$_GPC;
         $location_p = $_GPC['location_p'];
         $location_c = $_GPC['location_c'];
         $location_a = $_GPC['location_a'];
         $bs = pdo_fetchall("select * from ".tablename('hotel2_business')." where location_p=:location_p and location_c=:location_c and location_a=:location_a and weid=:weid",
                 array(":location_p"=>$_GPC['location_p'],
                     ":location_c"=>$_GPC['location_c'],
                 ":location_a"=>$_GPC['location_a'],
                     ":weid"=>$_W['weid']));
         die( json_encode($bs) );
     }
     public function doBusiness() {
        global $_GPC, $_W;
        $op = $_GPC['op'];
        if ($op == 'edit') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('hotel2_business') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，商圈不存在或是已经删除！', '', 'error');
                }
            }
            
            if (checksubmit('submit')) {
                $data = array(
                    'weid' => $_W['weid'],
                    'title' => $_GPC['title'],
                    'location_p' => $_GPC['location_p'],
                    'location_c' => $_GPC['location_c'],
                    'location_a' => $_GPC['location_a'],
                    'displayorder' => $_GPC['displayorder'],
                    'status' => $_GPC['status'],
                );

                if (empty($id)) {
                    pdo_insert('hotel2_business', $data);
                } else {
                    pdo_update('hotel2_business', $data, array('id' => $id));
                }
                message('商圈信息更新成功！', $this->createWebUrl('business'), 'success');
            }
            include $this->template('business_form');
            
        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete('hotel2_business', array('id' => $id));
            message('删除成功！', referer(), 'success');
            
        }  else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {

                $id = intval($id);
                pdo_delete('hotel2_business', array('id' => $id));
            }
            $this->message('规则操作成功！', '', 0);
            exit();
        }   else if ($op == 'showall') {
            if ($_GPC['show_name'] == 'showall') {
                $show_status = 1;
            } else {
                $show_status = 0;
            }

            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    pdo_update('hotel2_business', array('status' => $show_status), array('id' => $id));
                }
            }
            $this->message('操作成功！', '', 0);
            exit();
        } else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('hotel2_business', array('status' => $_GPC['status']), array('id' => $id));
            
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        }else {
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND `title` LIKE :title';
                $params[':title'] = "%{$_GPC['title']}%";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $list = pdo_fetchall("SELECT * FROM " . tablename('hotel2_business') . " WHERE weid = '{$_W['weid']}' $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('hotel2_business') . " WHERE weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('business');
        }
    }
}
