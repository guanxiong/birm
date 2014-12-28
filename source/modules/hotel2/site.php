<?php
/**
 * 微酒店
 *
 * @author WeEngine Team & ewei
 * @url
 */
defined('IN_IA') or exit('Access Denied');

//ini_set('display_errors','on');
//error_reporting(E_ALL);
include "./source/modules/hotel2/model.php";
class Hotel2ModuleSite extends WeModuleSite {

    public $_img_url = './source/modules/hotel2/template/style/img/';

    public $_css_url = './source/modules/hotel2/template/style/css/';

    public $_script_url = './source/modules/hotel2/template/style/js/';

    public $_search_key = '__hotel2_search';

    public $_from_user = '';

    public $_weid = '';

    public $_version = 0;

    public $_hotel_level_config = array(5 => '五星级酒店', 4 => '四星级修店', 3 => '三星级酒店', 2 => '两星级以下', 15 => '豪华酒店', 14 => '高档酒店', 13 => '舒适酒店', 12 => '经济型酒店', );

    public $_set_info = array();

    public $_user_info = array();



    function __construct()
    {
        global $_W;
        $this->_from_user = $_W['fans']['from_user'];
        $this->_weid = $_W['weid'];
        $this->_set_info =  get_hotel_set();
        $this->_version = $this->_set_info['version'];

    }

    public function getItemTiles() {
        global $_W;
        $urls = array(
            array('title' => "酒店首页", 'url' => $this->createMobileUrl('index')),
            array('title' => "我的订单", 'url' => $this->createMobileUrl('orderlist')),
        );
        return $urls;
    }

    function getSearchArray(){

        $search_array = get_cookie($this->_search_key);
        if (empty($search_array)) {
            //默认搜索参数
            $search_array['order_type'] = 1;
            $search_array['order_name'] = 2;
            $search_array['location_p'] = $this->_set_info['location_p'];
            $search_array['location_c'] = $this->_set_info['location_c'];
            if (strpos($search_array['location_p'], '市') > -1) {
                //直辖市
                $search_array['municipality'] = 1;
                $search_array['city_name'] = $search_array['location_p'];
            } else {
                $search_array['municipality'] = 0;
                $search_array['city_name'] = $search_array['location_c'];
            }
            $search_array['business_id'] = 0;
            $search_array['business_title'] = '';
            $search_array['brand_id'] = 0;
            $search_array['brand_title'] = '';

            $weekarray = array("日", "一", "二", "三", "四", "五", "六");

            $date = date('Y-m-d');
            $time = strtotime($date);
            $search_array['btime'] = $time;
            $search_array['etime'] = $time + 86400;
            $search_array['bdate'] = $date;
            $search_array['edate'] = date('Y-m-d', $search_array['etime']);
            $search_array['bweek'] = '星期' . $weekarray[date("w", $time)];
            $search_array['eweek'] = '星期' . $weekarray[date("w", $search_array['etime'])];
            $search_array['day'] = 1;
            insert_cookie($this->_search_key, $search_array);
        }
        //print_r($search_array);exit;
        return $search_array;
    }

    //入口文件
    public function doMobileIndex()
    {
        global $_GPC, $_W;
        $weid = $this->_weid;
        $from_user = $this->_from_user;
        $set = $this->_set_info;
        //$user_info = pdo_fetch("SELECT * FROM " . tablename('hotel2_member') . " WHERE from_user = :from_user AND status=1 limit 1", array(':from_user' => $from_user));
        $user_info = pdo_fetch("SELECT * FROM " . tablename('hotel2_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $from_user, ':weid' => $weid));

        //独立用户
        if ($set['user'] == 2) {
            if (empty($user_info['id'])) {
                //用户不存在
                if ($set['reg'] == 1) {
                    //开启注册
                    $url = $this->createMobileUrl('register');
                } else {
                    //禁止注册
                    $url = $this->createMobileUrl('login');
                }
            } else {
                //用户已经存在，判断用户是否登录
                $check = check_hotel_user_login($this->_set_info);
                if ($check) {
                    if ($user_info['status'] == 1) {
                        $url = $this->createMobileUrl('search');
                    } else {
                        $url = $this->createMobileUrl('login');
                    }
                } else {
                    $url = $this->createMobileUrl('login');
                }
            }
        } else {
            //微信用户
            if (empty($user_info[id])) {
                //用户不存在，自动添加一个用户
                $member = array();
                $member['weid'] = $weid;
                $member['from_user'] = $from_user;
                $member['createtime'] = time();
                $member['isauto'] = 1;
                $member['status'] = 1;
                pdo_insert('hotel2_member', $member);

                $member['id'] = pdo_insertid();
                $member['user_set'] = $set['user'];

                //自动添加成功，将用户信息放入cookie
                hotel_set_userinfo(0, $member);
            } else {
                if ($user_info['status'] == 1) {
                    $user_info['user_set'] = $set['user'];
                    //用户已经存在，将用户信息放入cookie
                    hotel_set_userinfo(1, $user_info);
                } else {
                    //用户帐号被禁用
                    $msg = "抱歉，你的帐号被禁用，请联系酒店解决。";

                    if ($this->_set_info['is_unify'] == 1) {
                        $msg .= "酒店电话：" . $this->_set_info['tel'] . "。";
                    }

                    $url = $this->createMobileUrl('error',array('msg' => $msg));
                    header("Location: $url");
                    exit;
                }
            }
            //微信粉丝，可以直接使用
            $url = $this->createMobileUrl('search');
        }
        header("Location: $url");
        exit;
    }

    //检查酒店版本
    public function check_version()
    {
        global $_GPC, $_W;
        $weid = $this->_weid;

        //单酒店版
        if ($this->_version == 0) {
            $data = pdo_fetch("SELECT id FROM " . tablename('hotel2') . " WHERE weid = :weid AND status=1 ORDER BY displayorder DESC limit 1", array(':weid' => $weid));
            $hid = $data['id'];
            if (empty($hid)) {
                echo "酒店信息获取失败";exit;
            }

            $url = $this->createMobileUrl('detail', array('hid' => $hid));
            header("Location: $url");
        }
    }

    //查询条件页
    public function doMobilesearch()
    {
        global $_GPC, $_W;

        $this->check_login();
        $search_array = $this->getSearchArray();
        //$search_array = get_cookie($this->_search_key);

        $this->check_version();

        $key_word = '';
        if (!empty($search_array['keyword'])) {
            $key_word .= $search_array['keyword'];
        }

        if (!empty($search_array['business_id'])) {
            if (!empty($key_word)) {
                $key_word .= "/";
            }
            $key_word .= $search_array['business_title'];
        }

        if (!empty($search_array['brand_id'])) {
            if (!empty($key_word)) {
                $key_word .= "/";
            }
            $key_word .= $search_array['brand_title'];
        }

        if (empty($key_word)) {
            $key_word = '酒店名/商圈/品牌';
        }

        include $this->template('search');
    }

    //日期选择页
    public function doMobiledate()
    {
        $this->check_login();

        $search_array = get_cookie($this->_search_key);
        $referer = referer();

        if ($search_array && !empty($search_array['bdate']) && !empty($search_array['day'])) {
            $bdate = $search_array['bdate'];
            $day = $search_array['day'];
        } else {
            $bdate = date('Y-m-d');
            $day = 1;
        }

        include $this->template('date');
    }

    //登录页
    public function doMobilelogin()
    {
        global $_GPC, $_W;;
        $set = $this->_set_info;

        if (checksubmit()) {
            $member = array();
            $username = trim($_GPC['username']);

            if (empty($username)) {
                die(json_encode(array("result" => 2, "error" => "请输入要登录的用户名")));
            }
            $member['username'] = $username;
            $member['password'] = $_GPC['password'];
            //$member['status'] = 1;

            if (empty($member['password'])) {
                die(json_encode(array("result" => 3, "error" => "请输入登录密码")));
            }

            $weid = $this->_weid;
            $from_user = $this->_from_user;
            $set = $this->_set_info;

            $member['weid'] = $weid;
            $record = hotel_member_single($member);

            if (!empty($record)) {
                if ( ($set['bind'] == 3 && ($record['userbind'] == 1) || $set['bind'] == 2)) {
                    if (!empty($record['from_user'])) {
                        if ($record['from_user'] != $this->_from_user) {
                            die(json_encode(array("result" => 0, "error" => "登录失败，您的帐号与绑定的微信帐号不符！")));
                        }
                    }
                }

                if (empty($record['status'])) {
                    die(json_encode(array("result" => 0, "error" => "登录失败，您的帐号被禁止登录，请联系酒店解决！")));
                }

                $record['user_set'] = $set['user'];

                //登录成功
                hotel_set_userinfo(0, $record);

                $url = $this->createMobileUrl('search');
                die(json_encode(array("result" => 1, "url" => $url)));
            } else {
                die(json_encode(array("result" => 0, "error" => "登录失败，请检查您输入的用户名和密码！")));
            }
        } else {
            include $this->template('login');
        }
    }

    //ajax数据处理,包含 城市选择 时间选择 价格选择
    public function doMobileajaxData()
    {
        global $_GPC, $_W;
        $referer = $_GPC['referer'];
        $data = $this->getSearchArray();
        $key = $this->_search_key;
        switch ($_GPC['ac'])
        {
            //选择日期
            case 'time':
                $bdate = $_GPC['bdate'];
                $day = $_GPC['day'];

                if (!empty($bdate) && !empty($day)) {
                    $btime = strtotime($bdate);
                    $etime = $btime + $day * 86400;

                    $weekarray = array("日", "一", "二", "三", "四", "五", "六");

                    $data['btime'] = $btime;
                    $data['etime'] = $etime;
                    $data['bdate'] = $bdate;
                    $data['edate'] = date('Y-m-d', $etime);
                    $data['bweek'] = '星期' . $weekarray[date("w", $btime)];
                    $data['eweek'] = '星期' . $weekarray[date("w", $etime)];
                    $data['day'] = $day;

                    insert_cookie($this->_search_key, $data);
                    //$url = $this->createMobileUrl('search');
                    $url = $referer;
                    die(json_encode(array("result" => 1, "url" => $url)));
                }
                break;

            //选择价格和星级
            case 'price':
                $price_type = $_GPC['price_type'];
                $price_value = $_GPC['price_value'];

                if (empty($price_value)) {
                    $data['price_type'] = 0;
                } else {
                    $data['price_type'] = $price_type;
                }
                $data['price_value'] = $price_value;
                insert_cookie($key, $data);
                die(json_encode(array("result" => 1)));
                break;

            //选择城市
            case 'city':
                $location_p = $_GPC['location_p'];
                $location_c = $_GPC['location_c'];

                if (!empty($location_p) && !empty($location_c)) {

                    if (strpos($location_p, '市') > -1) {
                        //直辖市
                        $data['municipality'] = 1;
                        $data['city_name'] = $location_p;
                    } else {
                        $data['municipality'] = 0;
                        $data['city_name'] = $location_c;
                    }

                    $data['location_p'] = $location_p;
                    $data['location_c'] = $location_c;

                    insert_cookie($key, $data);
                }
                $url = $this->createMobileUrl('search');
                die(json_encode(array("result" => 1, "url" => $url)));
                break;

            //价格排序
            case 'orderby':
                $order_name = $_GPC['order_name'];
                $order_type = $_GPC['order_type'];

                $data['order_name'] = $order_name;
                $data['order_type'] = $order_type;

                insert_cookie($key, $data);
                $url = $this->createMobileUrl('list');
                die(json_encode(array("result" => 1, "order_type"=>$order_type,"order_name"=>$order_name, "url" => $url)));
                break;

            //选择品牌商圈
            case 'brand':
                $business_id = $_GPC['business_id'];
                $business_title = $_GPC['business_title'];
                $brand_id = $_GPC['brand_id'];
                $brand_title = $_GPC['brand_title'];
                $keyword = $_GPC['keyword'];

                $data['business_id'] = $business_id;
                $data['brand_id'] = $brand_id;
                if (!empty($business_title)) {
                    $data['business_title'] = $business_title;
                }
                if (!empty($brand_title)) {
                    $data['brand_title'] = $brand_title;
                }
                $data['keyword'] = $keyword;

                insert_cookie($key, $data);
                $url = $this->createMobileUrl('search');
                die(json_encode(array("result" => 1, "url" => $url)));
                break;

            //清除品牌商圈信息
            case 'clear_brand':
                $data['business_id'] = 0;
                $data['brand_id'] = 0;
                $data['business_title'] = '';
                $data['brand_title'] = '';
                $data['keyword'] = '';

                insert_cookie($key, $data);
                $url = $this->createMobileUrl('search');
                die(json_encode(array("result" => 1, "url" => $url)));
                break;
        }
    }

    //城市选择页
    public function doMobilecity()
    {
        global $_GPC, $_W;

        $this->check_login();
        $search_array = get_cookie($this->_search_key);
        include $this->template('city');
    }

    //预定页，预定信息提交页
    public function doMobileOrder()
    {
        global $_GPC, $_W;

        $this->check_login();
        $isauto = $this->_user_info['isauto'];

        $hid = $_GPC['hid'];
        $id = $_GPC['id'];
        $weid = $this->_weid;
        $price = $_GPC['price'];
        //$total_price = $_GPC['total_price'];

        if(empty($hid) || empty($id)){
            message("参数错误1！");
        }

        $search_array = $this->getSearchArray();
        if (!$search_array || empty($search_array['btime']) || empty($search_array['day'])) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        }

        $is_submit = checksubmit();
        $reply = pdo_fetch("SELECT title,mail FROM " . tablename('hotel2') . " WHERE id = :id ", array(':id' => $hid));
        if (empty($reply)) {
            if ($is_submit) {
                die(json_encode(array("result" => 0, "error" => "酒店未找到!")));
            } else {
                message("酒店未找到, 请联系管理员!");
            }

        }

        $pricefield = $this->_user_info['isauto']==1?"cprice":"mprice";

        $room = pdo_fetch("SELECT *, $pricefield as roomprice FROM " . tablename('hotel2_room') . " WHERE id = :id AND hotelid = :hotelid ", array(':id' => $id, ':hotelid' => $hid));
        if (empty($room)) {
            if ($is_submit) {
                die(json_encode(array("result" => 0, "error" => "房型未找到!")));
            } else {
                message("房型未找到, 请联系管理员!");
            }
        }

        //入住
        $btime = $search_array['btime'];
        $bdate = $search_array['bdate'];

        //住几天
        $days =intval($search_array['day']);

        //离店
        $etime = $search_array['etime'];
        $edate = $search_array['edate'] ;

        $date_array = array();
        $date_array[0]['date'] = $bdate;
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

        $sql = "SELECT id, roomdate, num, status FROM " . tablename('hotel2_room_price');
        $sql .= " WHERE 1 = 1";
        $sql .= " AND roomid = :roomid";
        $sql .= " AND roomdate >= :btime AND roomdate < :etime";
        $sql .= " AND status = 1";

        $params[':roomid'] = $id;
        $params[':btime'] = $btime;
        $params[':etime'] = $etime;

        $room_date_list = pdo_fetchall($sql, $params);

        //print_r($room_date_list);exit;

        if ($room_date_list) {
            $flag = 1;
        } else {
            $flag = 0;
        }
        $list = array();
        $max_room = 8;

        $is_order = 1;
        if ($flag == 1) {
            for($i = 0; $i < $days; $i++) {
                $k = $date_array[$i]['time'];
                foreach ($room_date_list as $p_key => $p_value) {
                    //判断价格表中是否有当天的数据
                    if($p_value['roomdate'] == $k) {
                        $room_num = $p_value['num'];
                        if (empty($room_num)) {
                            $is_order = 0;
                            $max_room = 0;
                            $list['num'] = 0;
                            $list['date'] =  $date_array[$i]['date'];
                        } else if ($room_num > 0 && $room_num < $max_room) {
                            $max_room = $room_num;
                            $list['num'] =  $room_num;
                            $list['date'] =  $date_array[$i]['date'];
                        }
                        break;
                    }
                }
            }
        }

        if ($max_room == 0) {
            $msg = $list['date'] . '当天没有空房间了,请选择其他房型。';
            $url = $this->createMobileUrl('error',array('msg' => $msg));
            header("Location: $url");
            exit;
        }

        $user_info = hotel_get_userinfo();
        if (empty($user_info['id'])) {
            $memberid = 0;
        } else {
            $memberid = $user_info['id'];
        }

        //显示会员价还是普通价
        $pricefield = $isauto==1?"cprice":"mprice";

        $params = array(
            ":weid"=>$weid,
            ":hotelid"=>$hid
        );

        $r_sql = "SELECT roomdate, num, status, " . $pricefield . " as m_price FROM " . tablename('hotel2_room_price');
        $r_sql .= " WHERE 1 = 1";
        $r_sql .= " AND roomid = " . $id;
        $r_sql .= " AND weid = :weid";
        $r_sql .= " AND hotelid = :hotelid";
        $r_sql .= " AND roomdate >=" . $btime ." AND roomdate <" .$etime;

        $price_list = pdo_fetchall($r_sql, $params);

        $this_price = $old_price = $room['roomprice'];
        $totalprice =  $old_price * $days;

        if ($price_list) {
            //价格表中存在
            $check_date = array();

            foreach($price_list as $k => $v) {
                $new_price = $v['m_price'];
                $roomdate = $v['roomdate'];
                if ($v['status'] == 0 || $v['num'] == 0 ) {
                    $has = 0;
                } else {
                    if ($new_price && $roomdate) {
                        if (!in_array($roomdate, $check_date)) {
                            $check_date[] = $roomdate;
                            if ($old_price != $new_price) {
                                $totalprice = $totalprice - $old_price + $new_price;
                            }
                        }
                    }
                }
            }
            $this_price = round( $totalprice / $days);
        }

        //print_r($this_price);exit;

        if ($is_submit) {
            $from_user = $this->_from_user;
            $name = $_GPC['uname'];
            $contact_name = $_GPC['contact_name'];
            $mobile = $_GPC['mobile'];

            if (empty($name)) {
                die(json_encode(array("result" => 0, "error" => "入住人不能为空!")));
            }

            if (empty($contact_name)) {
                die(json_encode(array("result" => 0, "error" => "联系人不能为空!")));
            }

            if (empty($mobile)) {
                die(json_encode(array("result" => 0, "error" => "手机号不能为空!")));
            }

            if ($_GPC['nums'] > $max_room) {
                die(json_encode(array("result" => 0, "error" => "您的预定数量超过最大限制!")));
            }
            $data = array(
                'realname' => $name,
                'mobile' => $mobile,
            );
            fans_update($from_user, $data);
            pdo_update("hotel2_member",$data,array("from_user"=>$from_user));

            $insert = array(
                'weid' => $weid,
                'ordersn' => date('md') . sprintf("%04d", $_W['fans']['id']) . random(4, 1),
                'hotelid' => $hid,
                'openid' => $from_user,
                'roomid' => $id,
                'memberid' => $memberid,
                'name' => $name,
                'contact_name' => $contact_name,
                'mobile' => $mobile,
                'btime' => $search_array['btime'],
                'etime' => $search_array['etime'],
                'day' => $search_array['day'],
                'style' => $room['title'],
                'nums' => intval($_GPC['nums']),
                'oprice' => $room['oprice'],
                'cprice' => $room['cprice'],
                'mprice' => $room['mprice'],
                //'sum_price' => ($search_array['day'] * $room['cprice'] * $_GPC['nums']),
                //'info' => $_GPC['info'],
                'time' => time(),
                'paytype'=>$_GPC['paytype']
            );

            $insert[$pricefield] = $this_price;
            $insert['sum_price'] = $totalprice * $insert['nums'];
//            $is_repeat = check_orderinfo($insert);
//            if ($is_repeat == 1){
//                die(json_encode(array("result" => 0, "error" => "您已经预定成功,请不要重复提交")));
//            }

            pdo_insert('hotel2_order', $insert);
            $order_id = pdo_insertid();

            //如果有接受订单的邮件,
            if (!empty($reply['mail'])) {
                $subject = "微信公共帐号 [" . $_W['account']['name'] . "] 微酒店订单提醒.";
                $body = "您后台有一个预定订单: <br/><br/>";
                $body .= "预定酒店: " . $reply['title'] . "<br/>";
                $body .= "预定房型: " . $room['title'] . "<br/>";
                $body .= "预定数量: " . $insert['nums'] . "<br/>";
                $body .= "预定价格: " . $insert['sum_price'] . "<br/>";
                $body .= "预定人: " . $insert['name'] . "<br/>";
                $body .= "预定电话: " . $insert['mobile'] . "<br/>";
                $body .= "到店时间: " . $bdate . "<br/>";
                $body .= "离店时间: " . $edate . "<br/><br/>";
                //$body .= "到店时间: " . $_GPC['btime'] . "<br/>";
                //$body .= "离店时间: " . $_GPC['btime'] . "<br/><br/>";
                $body .= "请您到管理后台仔细查看. <a href='" .$_W['siteroot'] .create_url('member/login') . "' target='_blank'>立即登录后台</a>";
                $result = ihttp_email($reply['mail'], $subject, $body);
            }
            //$url = $this->createMobileUrl('index');

            $url = $this->createMobileUrl('orderdetail', array('id' => $order_id));
            die(json_encode(array("result" => 1, "url" => $url)));
        } else {
            $price = $totalprice;

            $member = array();
            $member['from_user'] = $this->_from_user;
            $record = hotel_member_single($member);

            if ($record) {
                $realname = $record['realname'];
                $mobile = $record['mobile'];
            } else {
                $fans = pdo_fetch("SELECT id, realname, mobile FROM " . tablename('fans') . " WHERE from_user = :from_user limit 1", array(':from_user' => $this->_from_user));

                if(!empty($fans)){
                    $realname = $fans['realname'];
                    $mobile = $fans['mobile'];
                }
            }

            include $this->template('order');
        }
    }

    //酒店详情页，显示房间列表
    public function doMobilekeyword()
    {
        global $_GPC, $_W;

        $this->check_login();
        $referer = referer();
        $search_array = $this->getSearchArray();

        //print_r($search_array);exit;

        if (!$search_array || empty($search_array['location_p']) || empty($search_array['location_c'])) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        }

        $search_array['business_id'] =  intval($search_array['business_id']);
        $search_array['brand_id'] =  intval($search_array['brand_id']);

        $business_sql = "SELECT id, title FROM " . tablename('hotel2_business') . " WHERE weid = '{$this->_weid}'";
        $business_sql .= " AND location_p ='" . $search_array['location_p'] . "'";
        $business_sql .= " AND location_c ='" . $search_array['location_c'] . "'";
        $business_sql .= " AND status = 1 ORDER BY displayorder DESC";
        $business_list = pdo_fetchall($business_sql);
        //print_r($business_list);exit;

        $brand_sql = "SELECT id, title FROM " . tablename('hotel2_brand') . " WHERE weid = '{$this->_weid}' AND status = 1 ORDER BY displayorder DESC";
        $brand_list = pdo_fetchall($brand_sql);

        include $this->template('keyword');

    }

    //酒店详情页，显示房间列表
    public function doMobiledetail()
    {
        global $_GPC, $_W;

        $this->check_login();

        $hid = $_GPC['hid'];
        $weid = $this->_weid;

        $referer = referer();
        $search_array =$this->getSearchArray();
        if (!$search_array) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        }

        $reply = pdo_fetch("SELECT * FROM " . tablename('hotel2') . " WHERE id = :id ", array(':id' => $hid));
        if(empty($reply)){
            message("酒店未找到, 请联系管理员!");
        }

        $thumbs = unserialize($reply['thumbs']);
        $thumbcount = count($thumbs) + 1;

        if ($this->_set_info['is_unify'] == 1) {
            $tel = $this->_set_info['tel'];
        } else {
            $tel = $reply['phone'];
        }

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {

            $isauto = $this->_user_info['isauto'];
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;

            //显示会员价还是普通价
            $pricefield = $isauto==1?"cprice":"mprice";

            //入住
            $bdate = $search_array['bdate'];
            $btime = $search_array['btime'];

            //住几天
            $day =intval($search_array['day']);

            //离店
            $edate = $search_array['edate'];
            $etime = $search_array['etime'];

            $params = array(
                ":weid"=>$weid,
                ":hotelid"=>$hid
            );

            $sql = "SELECT id, hotelid, id as roomid, title, breakfast, thumb, thumbs, " . $pricefield . " as m_price";
            $sql .= " FROM " .tablename('hotel2_room');
            $sql .= " WHERE 1 = 1";
            $sql .= " AND hotelid = :hotelid";
            $sql .= " AND weid = :weid";
            $sql .= " AND status = 1";
            $sql .= " ORDER BY displayorder, sortid DESC";

            $room_list = pdo_fetchall($sql, $params);

            //print_r($room_list);exit;

            //循环房间列表
            foreach($room_list as $key => $value) {
                $room_list[$key]['thumbs'] = unserialize($value['thumbs']);

                $r_sql = "SELECT roomdate, num, status, " . $pricefield . " as m_price FROM " . tablename('hotel2_room_price');
                $r_sql .= " WHERE 1 = 1";
                $r_sql .= " AND roomid = " . $value['roomid'];
                $r_sql .= " AND weid = :weid";
                $r_sql .= " AND hotelid = :hotelid";
                $r_sql .= " AND roomdate >=" . $btime ." AND roomdate <" .$etime;

                $price_list = pdo_fetchall($r_sql, $params);

                if ($price_list) {
                    //价格表中存在
                    $has = 1;
                    $avg = 0;
                    $old_price = $value['m_price'];
                    $totalprice =  $old_price * $day;
                    $check_date = array();

                    foreach($price_list as $k => $v) {
                        $new_price = $v['m_price'];
                        $roomdate = $v['roomdate'];

                        if ($new_price && $roomdate) {
                            if (!in_array($roomdate, $check_date)) {
                                $check_date[] = $roomdate;
                                if ($old_price != $new_price) {
                                    $avg = 1;
                                    $totalprice = $totalprice - $old_price + $new_price;
                                }
                            }
                        }

                        if ($v['status'] == 0 || $v['num'] == 0 ) {
                            $has = 0;
                        } else {

                        }
                    }
                    $room_list[$key]['has'] = $has;
                    $room_list[$key]['price'] = round( $totalprice / $day);
                    $room_list[$key]['total_price'] = $totalprice;
                    if($day == 1) {
                        $avg = 0;
                    }
                    $room_list[$key]['avg'] = $avg;
                } else {
                    //价格表中不存在
                    $room_list[$key]['has'] = 1;
                    $room_list[$key]['price'] = $value['m_price'];
                    $room_list[$key]['total_price'] = $value['m_price'] * $day;
                    $room_list[$key]['avg'] = 0;
                }
            }

            //print_r($room_list);exit;

            if ($search_array['price_type'] == 1) {
                $price_value = $search_array['price_value'];
                if (!empty($price_value)) {

                    foreach($room_list as $key => $value) {
                        $new_price = $value['price'];
                        $price_flag = 1;

                        if (strstr($price_value, '-') !== false) {
                            $price_array = explode("-", $price_value);
                            if ($new_price >= intval($price_array[0]) && $new_price <= intval($price_array[1])) {
                                $price_flag = 1;
                            } else {
                                $price_flag = 0;
                            }
                        } else {
                            if ($price_value == 150) {
                                if ($new_price <= 150) {
                                    $price_flag = 1;
                                } else {
                                    $price_flag = 0;
                                }
                            }else if ($price_value == 1000) {
                                if ($new_price >= 1000) {
                                    $price_flag = 1;
                                } else {
                                    $price_flag = 0;
                                }
                            }
                        }

                        if ($price_flag == 0) {
                            unset($room_list[$key]);
                        }
                    }
                }
            }


            $total = count($room_list);

            //print_r($room_list);exit;

            if ($total <= $psize) {
                $list = $room_list;
            } else {
                // 需要分页
                if($pindex > 0) {
                    $list_array = array_chunk($room_list, $psize, true);
                    $list = $list_array[($pindex-1)];
                } else {
                    $list = $room_list;
                }
            }

            //print_r($list);exit;

            $data = array();
            $data['result'] = 1;
            $page_array = get_page_array($total, $pindex, $psize);

            ob_start();
            include $this->template('room_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }

            die(json_encode($data));



            exit;


            $where.=" GROUP BY r.id";

            if ($search_array['price_type'] == 1) {
                $price_value = $search_array['price_value'];
                if (!empty($price_value)) {
                    $where .= " HAVING";
                    if (strstr($price_value, '-') !== false) {
                        $price_array = explode("-", $price_value);
                        $where .= " price BETWEEN " . intval($price_array[0]) . " AND " . intval($price_array[1]);
                    } else {
                        if ($price_value == 150) {
                            $where .= " price <= 150";
                        }else if ($price_value == 1000) {
                            $where .= " price >= 1000";
                        }
                    }
                }
            }

            $sql .= $where;
            $count_sql = "select count(1) as num from (" . $sql . ") count_test";

            $sql .= " ORDER BY displayorder, sortid DESC";

//            $sql = "SELECT * FROM " . tablename('hotel2_room');
//            $where = " WHERE 1 = 1";
//            $where .= " AND hotelid = $hid";
//            $where .= " AND status = 1";
//
//            $sql .= $where;
//            $count_sql = "SELECT (id) FROM " . tablename('hotel2_room') . $where;
//
//            $sql .= " ORDER BY displayorder, sortid DESC";

            if($pindex > 0) {
                // 需要分页
                $start = ($pindex - 1) * $psize;
                $sql .= " LIMIT {$start},{$psize}";
            }

            $rooms = pdo_fetchall($sql);

            foreach($rooms as &$r){
                $pricedays = pdo_fetchall("select $pricefield as price,roomdate from ew_hotel2_room_price where roomid={$r['id']} and roomdate>=$btime and roomdate<=$etime");

                //找出$day天的价格记录
                $totalprice =  0 ;
                $prices = array();
                for($d=0;$d<$day;$d++){
                    $t = $btime+ 86400 * $d;
                    $p = $r['roomprice'];
                    foreach($pricedays as $pd){
                        if($pd['roomdate']==$t){
                            $p = $pd['price'];
                        }
                    }
                    $prices[] = $p;
                    $totalprice+=$p;
                }

                //价格表的价格是否都相同
                $prices1 = array_unique($prices);
                $r['avg'] = count($prices1)!=1;
                $r['price'] = round( $totalprice/$day );

            }
            unset($r);
            $total = pdo_fetchcolumn($count_sql);
            //$total = pdo_fetchcolumn("select count(*) from ew_hotel2_room r left join ew_hotel2_room_price p on r.id = p.roomid ".$where);
            $page_array = get_page_array($total, $pindex, $psize);
            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('room_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }

            //print_r($data);exit;
            die(json_encode($data));

            //print_r($html);exit;

            //            foreach ($rooms as &$data) {
            //                if (!empty($data['room_device'])) {
            //                    $data['room_device'] = unserialize($data['room_device']);
            //                    foreach ($data['room_device'] as $key => $value) {
            //                        if ($value['isshow'] == 1) {
            //                            $data['r_device'] .= $value['value'] . ' ';
            //                        }
            //                    }
            //                }
            //            }

            //            $rooms_num = count($rooms);
            //            if(empty($rooms_num)){
            //                message("房间未找到, 请联系管理员!");
            //            }
        } else {
            $device = '';
            $reply['device'] = unserialize($reply['device']);
            if ($reply['device']) {
                foreach ($reply['device'] as $key => $value) {
                    if ($value['isshow'] == 1) {
                        $device .= $value['value'] . ' ';
                    }
                }
            }
            include $this->template('detail');
        }
    }

    //获取房型信息
    public function doMobileroomdevice()
    {
        global $_GPC, $_W;

        $this->check_login();

        $id = $_GPC['id'];
        $hid = $_GPC['hid'];
        $has = $_GPC['has'];
        $price = $_GPC['price'];
        $total_price = $_GPC['total_price'];

        $search_array = $this->getSearchArray();

        $pricefield = $this->_user_info['isauto']==1?"cprice":"mprice";

        $data = array();
        if(empty($id) || empty($hid)) {
            $data['result'] = 0;
        } else {
            $sql = "SELECT *,$pricefield as roomprice ";
            $sql .= " FROM " .tablename('hotel2_room');
            $sql .= " WHERE id = :id AND hotelid = :hotelid AND status = 1";
            $sql .= " LIMIT 1";

            $params = array();
            $params[':hotelid'] = $hid;
            $params[':id'] = $id;
            $item = pdo_fetch($sql, $params);

            //计算价格
            //   //显示会员价还是普通价

            //入住
//            $bdate = $search_array['bdate'];
//            $btime = strtotime($bdate);
//
//            //住几天
//            $day =intval($search_array['day']);
//
//            //离店
//            $etime = $btime+86400;
//            $edate = date('Y-m-d',$etime) ;
//            $pricedays = pdo_fetchall("select $pricefield as price,roomdate from ew_hotel2_room_price where roomid={$item['id']} and roomdate>=$btime and roomdate<=$etime");
//
//
//            //找出$day天的价格记录
//            $totalprice =  0 ;
//            $prices = array();
//             $ts = array();
//             for($d=0;$d<$day;$d++){
//                    $t = $btime+ 86400 * $d;
//                    $p = $item['roomprice'];
//                    foreach($pricedays as $pd){
//                        if($pd['roomdate']==$t){
//                            $p = $pd['price'];
//                        }
//                    }
//                    $prices[] = $p;
//                    $totalprice+=$p;
//                }
//                //价格表的价格是否都相同
//                $prices1 = array_unique($prices);
//                $item['avg'] = count($prices1)!=1;
//                $item['price'] = round( $totalprice/$day );

            //print_r($item);exit;

            $data['result'] = 1;

            ob_start();
            include $this->template('room_device');
            $data['code'] = ob_get_contents();
            ob_clean();
        }

        die(json_encode($data));
    }

    //获取酒店列表
    public function doMobilelist()
    {
        global $_GPC, $_W;

        $this->check_login();
        $search_array =$this->getSearchArray();
        if (!$search_array || empty($search_array['city_name'])) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        }

        //0 默认推荐 1 价格排序
        $order_name = intval($search_array['order_name']);
        $order_type =intval( $search_array['order_type'] );

        //print_r($search_array);exit;

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            $weid = $this->_weid;
            $price_type = $search_array['price_type'];
            $price_value = $search_array['price_value'];

            $data = array();
            $data['result'] = 1;
            $data['title'] = $search_array['city_name'];

            //print_r($search_array);exit;

            //入住
            $bdate = $search_array['bdate'];
            $btime = $search_array['btime'];

            //住几天 
            $day =intval($search_array['day']);

            //离店
            $edate = $search_array['edate'];
            $etime = $search_array['etime'];

            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;

            $params = array(
                ":weid"=>$weid
            );
            $pricefield = $this->_user_info['isauto']==1?"cprice":"mprice";

            $sql = "SELECT h.id, r.id as roomid, h.title, h.thumb, h.level, h.displayorder, r.title as style, " . $pricefield . " as m_price";
            $sql .= " FROM " .tablename('hotel2') ." AS h";
            $sql .= " right JOIN " .tablename('hotel2_room') ." AS r ON h.id = r.hotelid";
            $sql .= " WHERE 1 = 1";
            $sql .= " AND r.weid = :weid";
            $sql .= " AND h.status = 1 AND r.status = 1";

            //商圈
            if (!empty($search_array['business_id'])) {
                $sql .= " AND h.businessid =:businessid";
                $params[':businessid'] = $search_array['business_id'];
            }
            //品牌
            if (!empty($search_array['brand_id'])) {
                $sql .= " AND h.brandid = :brandid";
                $params[':brandid'] = $search_array['brand_id'];
            }
            //名称
            if (!empty($search_array['keyword'])) {
                $sql .= " AND h.title LIKE :keyword";
                $params[':keyword'] = "%{$search_array['keyword']}%";
            }
            //城市
            if (!empty($search_array['city_name'])) {
                if ($search_array['municipality'] == 1) {
                    $sql .= " AND h.location_p =:city";
                } else {
                    $sql .= " AND h.location_c =:city";
                }
                $params[':city'] = $search_array['city_name'];
            }
            //星级
            if ($price_type == 2) {
                if (!empty($price_value)) {
                    $sql .= " AND h.level in( $price_value,". ($price_value + 10) .")";
                }
            }

            $room_list = pdo_fetchall($sql, $params);

            if (!$room_list) {
                $data['total'] = 0;
                $data['isshow'] = 0;
                die(json_encode($data));
            }

            $day = intval($search_array['day']);

            //循环房间列表
            foreach($room_list as $key => $value) {
                $r_sql = "SELECT count(id) as num, min(" . $pricefield . ") as m_price FROM " . tablename('hotel2_room_price') . " as p";
                $r_sql .= " WHERE 1 = 1";
                $r_sql .= " AND roomid = " . $value['roomid'];
                $r_sql .= " AND status = 1";
                $r_sql .= " AND roomdate >=" . $btime ." AND roomdate <" .$etime;
                $r_sql .= " AND num != 0";
                $r_price = pdo_fetch($r_sql);
                $min_price = intval($r_price['m_price']);
                $r_num = intval($r_price['num']);

                //如果价格表中设置了价格
                if ($r_num && !empty($min_price)) {
                    if ($r_num == $day) {
                        //如果选择的天数都设置了价格
                        $room_list[$key]['m_price'] = $min_price;
                    } else {
                        //如果价格表存在更低的价格
                        if ($min_price < $value['m_price']) {
                            $room_list[$key]['m_price'] = $min_price;
                        }
                    }
                }
            }

            $hotel_list = array();
            foreach($room_list as $key => $value) {
                $hotelid = $value['id'];
                $roomid = $value['roomid'];
                $new_price = $value['m_price'];
                $price_flag = 1;

                //用户选择了价格区间
                if ($price_type == 1) {
                    if (!empty($price_value)) {
                        if (strstr($price_value, '-') !== false) {
                            $price_array = explode("-", $price_value);
                            if ($new_price >= intval($price_array[0]) && $new_price <= intval($price_array[1])) {
                                $price_flag = 1;
                            } else {
                                $price_flag = 0;
                            }
                        } else {
                            if ($price_value == 150) {
                                if ($new_price <= 150) {
                                    $price_flag = 1;
                                } else {
                                    $price_flag = 0;
                                }
                            }else if ($price_value == 1000) {
                                if ($new_price >= 1000) {
                                    $price_flag = 1;
                                } else {
                                    $price_flag = 0;
                                }
                            }
                        }
                    }
                }

                if ($price_flag == 0) {
                    continue;
                }

                //取出酒店最低价放入数组中
                if (array_key_exists($hotelid, $hotel_list)) {
                    $old_price = $hotel_list[$hotelid]['m_price'];
                    if ($new_price < $old_price) {
                        $hotel_list[$hotelid] = $value;
                    }
                } else {
                    $hotel_list[$hotelid] = $value;
                }
            }

            //排序
            switch ($order_name)
            {
                case 0:
                    //优先级
                    $hotel_list = array_sort($hotel_list, 'displayorder', 1);
                    break;
                case 1:
                    if ($order_type == 1) {
                        //价格降序
                        $hotel_list = array_sort($hotel_list, 'm_price', 1);
                    } else {
                        //价格升序
                        $hotel_list = array_sort($hotel_list, 'm_price', 0);
                    }
                    break;
            }

            $total = count($hotel_list);

            //print_r($hotel_list);exit;

            if ($total <= $psize) {
                $list = $hotel_list;
            } else {
                // 需要分页
                if($pindex > 0) {
                    $list_array = array_chunk($hotel_list, $psize);
                    $list = $list_array[($pindex-1)];
                    //print_r($list);exit;
                } else {
                    $list = $hotel_list;
                }
            }

            $page_array = get_page_array($total, $pindex, $psize);

            ob_start();
            include $this->template('hotel_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['title'] .=  "(" . $total . ")";
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }

            die(json_encode($data));
        } else {

            include $this->template('list');
        }
    }

    //订单列表
    public function doMobileorderlist()
    {
        global $_GPC, $_W;

        $weid = $this->_weid;
        $this->check_login();

        $memberid = $this->_user_info['id'];
        if (empty($memberid)) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        }
        //print_r($memberid);exit;

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {

            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $sql = "SELECT o.*, h.title ";
            $where = " FROM " .tablename('hotel2_order') ." AS o";
            $where .= " LEFT JOIN " .tablename('hotel2') ." AS h ON o.hotelid = h.id";
            $where .= " WHERE 1 = 1";
            $where .= " AND o.memberid = $memberid";
            $where .= " AND o.weid = $weid";

            $count_sql = "SELECT COUNT(o.id) " . $where;
            $sql .= $where;
            $sql .= " ORDER BY o.id DESC";
            if($pindex > 0) {
                // 需要分页
                $start = ($pindex - 1) * $psize;
                $sql .= " LIMIT {$start},{$psize}";
            }
            $list = pdo_fetchall($sql);



            $total = pdo_fetchcolumn($count_sql);
            $page_array = get_page_array($total, $pindex, $psize);


            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('order_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }
            die(json_encode($data));
        } else {
            include $this->template('orderlist');
        }
    }

    //订单详情
    public function doMobileorderdetail()
    {
        global $_GPC, $_W;

        $weid = $this->_weid;
        $id = $_GPC['id'];

        $this->check_login();

        if (empty($id)) {
            $url = $this->createMobileUrl('orderlist');
            header("Location: $url");
        }

        $memberid = $this->_user_info['id'];
        if (empty($memberid)) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        }
        $sql = "SELECT o.*, h.title, h.address, h.phone";
        $sql .= " FROM " .tablename('hotel2_order') ." AS o";
        $sql .= " LEFT JOIN " .tablename('hotel2') ." AS h ON o.hotelid = h.id";
        $sql .= " WHERE 1 = 1";
        $sql .= " AND o.id = :id";
        $sql .= " AND o.memberid = :memberid";
        $sql .= " AND o.weid = :weid";

        $params = array();
        $params[':memberid'] = $memberid;
        $params[':weid'] = $weid;
        $params[':id'] = $id;

        $sql .= " LIMIT 1";
        $item = pdo_fetch($sql, $params);

        if ($this->_set_info['is_unify'] == 1) {
            $tel = $this->_set_info['tel'];
        } else {
            $tel = $item['phone'];
        }
        //print_r($item);exit;
        $params['module'] = "hotel2";
        $params['ordersn'] = $item['ordersn'];
        $params['tid'] = $item['id'];
        $params['user'] = $_W['fans']['from_user'];
        $params['fee'] = $item['sum_price'];
        $params['title'] = $_W['account']['name'] . "酒店订单{$item['ordersn']}";

        include $this->template('orderdetail');
    }

    //检查用户是否登录
    public function check_login()
    {
        $check = check_hotel_user_login($this->_set_info);

        if ($check == 0) {
            $url = $this->createMobileUrl('index');
            header("Location: $url");
        } else {
            if(empty($this->_user_info)) {
                //$this->_user_info = hotel_get_userinfo();
                $weid = $this->_weid;
                $from_user = $this->_from_user;
                $user_info = pdo_fetch("SELECT * FROM " . tablename('hotel2_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $from_user, ':weid' => $weid));
                $this->_user_info = $user_info;
            }
        }
    }

    public function doMobileorderinfo()
    {
        global $_GPC, $_W;
        include $this->template('orderinfo');
    }
    public function doMobileorderpay(){

        global $_W,$_GPC;
        //立即支付
        $orderid = intval($_GPC['id']);
        $order = pdo_fetch("SELECT * FROM " . tablename('hotel2_order') . " WHERE id = :id", array(':id' => $orderid));
        if ($order['paystatus'] != '0' && $order['paytype'] != '1' && $order['paytype'] != '2') {
            message('抱歉，您的订单已付款或是被关闭！', $this->createMobileUrl('orderdetail',array("id"=>$order['id'])), 'error');
        }

        $params['ordersn'] = $order['ordersn'];
        $params['tid'] = $orderid;
        $params['user'] = $_W['fans']['from_user'];
        $params['fee'] = $order['sum_price'];
        $params['title'] = $_W['account']['name'] . "酒店订单{$order['ordersn']}";
        $this->pay($params);

    }

    public function payResult($params) {

        global $_GPC, $_W;

        $weid = $this->_weid;

        $order = pdo_fetch("SELECT id, status, hotelid, roomid FROM " . tablename('hotel2_order') . " WHERE id = {$params['tid']} AND weid = {$weid} LIMIT 1");

        pdo_update('hotel2_order', array('paystatus' => 1), array('id' => $params['tid']));
        if ($params['from'] == 'return') {
            $roomid = $order['roomid'];
            $room = pdo_fetch("SELECT score FROM " . tablename('hotel2_room') . " WHERE id = {$roomid} AND weid = {$weid} LIMIT 1");
            $score = intval($room['score']);

            if ($score) {
                $from_user = $this->_from_user;
                pdo_fetch("UPDATE " . tablename('hotel2_member') . " SET score = (score + " . $score . ") WHERE from_user = '".$from_user."' AND weid = ".$weid."");
                pdo_fetch("UPDATE " . tablename('fans') . " SET credit1 = (credit1 + " . $score . ") WHERE from_user = '".$from_user."' AND weid = ".$weid."");
            }
            message('支付成功！', $this->createMobileUrl('orderdetail',array("id"=>$order['id'])), 'success');
        }
    }


    //用户注册
    public function doMobileregister()
    {
        global $_GPC, $_W;

        if (checksubmit()) {
            $weid = $this->_weid;
            $from_user = $this->_from_user;
            $set = $this->_set_info;

            $member = array();
            $member['from_user'] = $from_user;
            $member['username'] = $_GPC['username'];
            $member['password'] = $_GPC['password'];

            //print_r($_GPC);exit;

            if (!preg_match(REGULAR_USERNAME, $member['username'])) {
                die(json_encode(array("result" => 0, "error" => "必须输入用户名，格式为 3-15 位字符，可以包括汉字、字母（不区分大小写）、数字、下划线和句点。")));
            }

            if (!preg_match(REGULAR_USERNAME, $member['from_user'])) {
                die(json_encode(array("result" => 0, "error" => "微信号码获取失败。")));
            }

            if (hotel_member_check(array('from_user' => $member['from_user'], 'weid' => $weid))) {
                die(json_encode(array("result" => 0, "error" => "非常抱歉，此用微信号已经被注册，你可以直接使用注册时的用户名登录，或者更换微信号注册！")));
            }

            if (hotel_member_check(array('username' => $member['username'], 'weid' => $weid))) {
                die(json_encode(array("result" => 0, "error" => "非常抱歉，此用户名已经被注册，你需要更换注册用户名！")));
            }

            if (istrlen($member['password']) < 6) {
                die(json_encode(array("result" => 0, "error" => "必须输入密码，且密码长度不得低于6位。")));
            }
            $member['salt'] = random(8);
            $member['password'] = hotel_member_hash($member['password'], $member['salt']);

            $member['weid'] = $weid;
            $member['mobile'] = $_GPC['mobile'];
            $member['realname'] = $_GPC['realname'];
            $member['createtime'] = time();
            $member['status'] = 1;
            $member['isauto'] = 0;

            pdo_insert('hotel2_member', $member);

            $member['id'] = pdo_insertid();
            $member['user_set'] = $set['user'];

            //注册成功
            hotel_set_userinfo(1, $member);

            $url = $this->createMobileUrl('search');
            die(json_encode(array("result" => 1, "url" => $url)));
        } else {
            //$css_url = $this->_css_url;
            include $this->template('register');
        }
    }

    //错误信息提示页
    public function doMobileError()
    {
        global $_GPC, $_W;

        $msg = $_GPC['msg'];
        include $this->template('error');
    }

    public  function  doMobileAjaxdelete()
    {
        global $_GPC;
        $delurl = $_GPC['pic'];
        if(file_delete($delurl)) {
            echo 1;
        } else {
            echo 0;
        }
    }
}
