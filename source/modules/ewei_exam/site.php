<?php

/**
 * 微考试
 *
 */
defined('IN_IA') or exit('Access Denied');

include "./source/modules/ewei_exam/model.php";
class Ewei_examModuleSite extends WeModuleSite
{
    public $_from_user = '';
    public $_types_config = '';

    public $_img_url = './source/modules/ewei_exam/images/';
    //public $_css_url = './source/modules/hotel2/template/style/css/';
    public $_script_url = './source/modules/ewei_exam/style/';
    public $_search_key = '__exam_search';
    public $_set_info = array();
    public $_answer_array = array();

    function __construct()
    {
        global $_W;
        $this->_weid = $_W['weid'];
        $this->_from_user = $_W['fans']['from_user'];
        //$this->_from_user = 'fromUser3';

        $this->_set_info =  get_ewei_exam_sysset();
        $init_param =  get_init_param();
        $this->_types_config = $init_param['types_config'];
        $this->_answer_array = $init_param['answer_array'];
    }

    public function getItemTiles()
    {
        global $_W;
        $urls = array();
        $urls[] = array('title' => "首页", 'url' => $this->createMobileUrl('index'));
        $urls[] = array('title' => "我的预约", 'url' => $this->createMobileUrl('reservelist'));
        $list = pdo_fetchall("SELECT id,title FROM " . tablename('ewei_exam_paper') . " WHERE weid = '{$_W['weid']}'");
        if (!empty($list)) {
            foreach ($list as $row) {
                $urls[] = array('title' => $row['title'], 'url' => $this->createMobileUrl('ready', array('id' => $row['id'])));
            }
        }
                
        return $urls;
    }

    private function _message($err, $msg = '', $ispost = false)
    {
        if (!empty($err)) {
            if ($ispost) {
                die(json_encode(array("result" => 0, "error" => $err)));
            } else {
                message($err, '', 'error');
            }
        }
        if (!empty($msg)) {

            die(json_encode(array("result" => 1, "msg" => $msg)));
        }
    }

    //登录页
    public function doMobilelogin()
    {
        global $_GPC, $_W;;

        if (checksubmit()) {
            $member = array();
            $username = trim($_GPC['username']);
            $userid = $_GPC['userid'];

            if (empty($username)) {
                die(json_encode(array("result" => 2, "error" => "请输入姓名")));
            }

            if (empty($userid)) {
                die(json_encode(array("result" => 2, "error" => "请输入用户名")));
            }

            $member['username'] = $username;
            $member['userid'] = $userid;

            $params = array();
            $params[':username'] = $member['username'];
            $params[':userid'] = $member['userid'];
            $params[':weid'] = $this->_weid;

            $sql = "SELECT * FROM " . tablename('ewei_exam_member') . " WHERE weid = :weid AND username = :username AND userid = :userid LIMIT 1";
            $item = pdo_fetch($sql, $params);

            if ($item['id']) {
                if ($item['status'] == 0) {
                    die(json_encode(array("result" => 2, "error" => "抱歉，你的姓名和用户名被禁用，无法使用")));
                }

                $data = array();
                $data['realname'] = $username;
                fans_update($this->_from_user, $data);

                pdo_update('ewei_exam_member', array('from_user' => $this->_from_user), array('id' => $item['id']));

                $url = $this->createMobileUrl('index');
                die(json_encode(array("result" => 1, "url" => $url)));
                //if ($item['from_user'])
                //return 1;
            } else {
                die(json_encode(array("result" => 2, "error" => "抱歉，你输入的姓名和用户名不在本系统中，无法使用")));
            }

        } else {
            include $this->template('login');
        }
    }

    public function check_member()
    {
        global $_W, $_GPC;

        $weid = $this->_weid;
        $from_user = $this->_from_user;

        $set = $this->_set_info;
        $login_flag = $set['login_flag'];
        $user_info = pdo_fetch("SELECT * FROM " . tablename('ewei_exam_member') . " WHERE from_user = :from_user AND weid = :weid limit 1", array(':from_user' => $from_user, ':weid' => $weid));

        if ($login_flag == 1) {
            if (empty($user_info['username']) || empty($user_info['userid'])) {
                //用户帐号不存在或者用户第一次登录，没有录入姓名 用户名,用户进入登录页

                $url = $this->createMobileUrl('login');
                header("Location: $url");
                exit;
            } else {
                if ($user_info['status'] == 0) {
                    message('帐号被禁用，请联系管理员', '', 'error');
                    exit;
                }
                //登录成功
                //exam_set_userinfo(1, $user_info);
            }
        } else {
            if (empty($user_info['id'])) {
                //用户不存在，自动添加一个用户
                $member = array();
                $member['weid'] = $weid;
                $member['from_user'] = $from_user;
                $member['createtime'] = time();
                $member['status'] = 1;
                pdo_insert('ewei_exam_member', $member);
                $member['id'] = pdo_insertid();
                //自动添加成功，将用户信息放入cookie
                //exam_set_userinfo(0, $member);
            } else {
                if ($user_info['status'] == 0) {
                    message('帐号被禁用，请联系管理员', '', 'error');
                    exit;
                }
                //登录成功
                //exam_set_userinfo(1, $user_info);
            }
        }

    }


    //获取酒店列表
    public function doMobilesortlist()
    {
        global $_GPC, $_W;

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $data = array();
            $data['result'] = 1;

            $params = array();
            $params[':weid'] = $this->_weid;

            $sql = "SELECT distinct(memberid)";
            $sql .= " FROM " .tablename('ewei_exam_paper_member_record');
            $sql .= " WHERE 1 = 1";
            $sql .= " AND weid = :weid";
            //$sql .= " AND did = 1";

            $member_list = pdo_fetchall($sql, $params);

            //print_r($member_list);exit;

            foreach ($member_list as $key => $value) {
                $user_info = get_user_info($value['memberid']);
                $member_list[$key]['username'] = $user_info['username'];
                $member_list[$key]['userid'] = $user_info['userid'];

                $member_list[$key]['total'] = get_user_question_count($value['memberid'], 2);
                $member_list[$key]['right'] = get_user_question_count($value['memberid'], 1);
                $member_list[$key]['rate'] = round(($member_list[$key]['right'] / $member_list[$key]['total']) * 100, 2);
                //$member_list[$key]['rate'] = round((3 / 7) * 100, 2);
            }

            $member_list = array_sort($member_list, 'rate', 1);
            $member_list = array_values($member_list);

            $total = count($member_list);

            //最多显示10页
            if ($pindex > 10) {
                $data['total'] = $total;
                $data['isshow'] = 0;
                die(json_encode($data));
            }


            if ($total <= $psize) {
                $list = $member_list;
            } else {
                // 需要分页
                if($pindex > 0) {
                    $list_array = array_chunk($member_list, $psize);
                    $list = $list_array[($pindex-1)];
                } else {
                    $list = $member_list;
                }
            }

            $sort_num = ($pindex - 1) * $psize;
            //$sort_num += 100;

            $page_array = get_page_array($total, $pindex, $psize);

            ob_start();
            include $this->template('sort_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];

            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }

            die(json_encode($data));
            //print_r($member_list);exit;
        }

        include $this->template('sortlist');
    }


    //获取课程列表
    public function doMobileCourselist()
    {
        global $_GPC, $_W;

        $this->check_member();
        $weid = $this->_weid;

        $params = array();
        $params[':weid'] = $weid;

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            //$ccate = max(1, intval($_GPC['ccate']));
            $ccate = intval($_GPC['ccate']);

            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $sql = "SELECT p.id, p.title, p.displayorder";
            $count_sql = "SELECT COUNT(p.id)";

            $where = '';
            $where .= " FROM " .tablename('ewei_exam_course') ." AS p";
            if (!empty($pcate)){
                $where .= " LEFT JOIN " .tablename('ewei_exam_course_category') . " AS c ON p.pcate = c.id";
            }
            $where .= " WHERE 1 = 1";
            $where .= " AND p.weid = :weid";

            $where .= " AND p.status = 1";
            if (!empty($ccate)){
                $where .= " AND c.weid = :weid";

                //判断是否为一级分类
                $cate_sql = "SELECT id FROM " .tablename('ewei_exam_course_category');
                $cate_sql .=  " WHERE parentid = " . $ccate;
                $cate_sql .=  " AND weid = " . $weid;

                $item = pdo_fetchall($cate_sql);
                $cate_num = count($item);

                if ($cate_num == 0) {
                    $where .= " AND c.id = :pcate";
                    $params[':ccate'] = $ccate;
                } else if ($cate_num > 0) {
                    $item[$cate_num]['id'] = $ccate;
                    $cate_str = '';
                    foreach ($item as $k => $v) {
                        $cate_str .= $v['id'] . ",";
                    }
                    $cate_str = trim($cate_str, ",");
                    $where .= " AND c.id in (" . $cate_str . ")";
                }
            }

            $sql .= $where;
            $sql .= " ORDER BY p.displayorder DESC";
            if($pindex > 0) {
                // 需要分页
                $start = ($pindex - 1) * $psize;
                $sql .= " LIMIT {$start},{$psize}";
            }

            $count_sql .= $where;
            $list = pdo_fetchall($sql, $params);
            $total = pdo_fetchcolumn($count_sql, $params);

            $page_array = get_page_array($total, $pindex, $psize);

            //print_r($list);exit;

            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('course_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }
            die(json_encode($data));
        } else {

            $year_array = array();
            for ($i = date("Y"); $i >= 2000; $i--) {
                $year_array[] = $i;
            }

//            $sql = "SELECT id, cname, parentid FROM " .tablename('ewei_exam_paper_category');
//            $sql .= " WHERE weid = :weid AND status = 1";
//            $sql .= " ORDER BY displayorder DESC,parentid";
//            $cate_list = pdo_fetchall($sql, $params);

            //print_r($cate_list);exit;

            include $this->template('courselist');
        }
    }


    //获取课程列表
    public function doMobileCourse()
    {
        global $_GPC, $_W;

        $this->check_member();

        $id = intval($_GPC['id']);
        if (empty($id)) {
            exit;
        }
        $weid = $_W['weid'];
        $member_info = $this->getMemberInfo();


        if (!empty($id)) {
            $item = pdo_fetch("select * from " . tablename('ewei_exam_course') . " where id=:id AND status = 1 limit 1", array(":id" => $id));
        }

        //print_r($item);exit;

        $is_reserve = 0;

        if ($item['ctype']) {
            if ($item['fansnum'] < $item['ctotal']) {
                $is_reserve = 1;
            }
        } else {
            $time = time();
            if ($time >= $item['starttime'] && $time <= $item['endtime']) {
                $is_reserve = 1;
            }
        }

        //$is_reserve = 0;

        //$paper_info = $this->getPaperInfo($id);
        //print_r($item);exit;

        if (checksubmit()) {
            $username = trim($_GPC['username']);
            $mobile = trim($_GPC['mobile']);
            $email = trim($_GPC['email']);

            $data = array(
                'realname' => $username,
                'mobile' => $mobile,
            );
            //fans_update($from_user, $data);

            //更新用户信息
            $array = array();
            $array['username'] = $username;
            $array['mobile'] = $mobile;
            $array['email'] = $email;

            $params = array();
            $params['from_user'] = $this->_from_user;
            $params['weid'] = $weid;
            pdo_update('ewei_exam_member', $array, $params);

            //更新考试人数记录
            //$this->updateCourseMemberNum($id, 1);

            //插入学员考试记录
            $data = array();
            $data['weid'] = $weid;
            $data['ordersn'] = date('md') . sprintf("%04d", $_W['fans']['id']) . random(4, 1);
            $data['courseid'] = $id;
            $data['memberid'] = $member_info['id'];
            $data['username'] = $username;
            $data['mobile'] = $mobile;
            $data['email'] = $email;
            $data['times'] = 0;
            $data['createtime'] = time();
            $data['times'] = 0;
            pdo_insert('ewei_exam_course_reserve', $data);
            $reserveid = pdo_insertid();

            //exit;

            $url = $this->createMobileUrl('reserve', array('id' => $reserveid));
            die(json_encode(array("result" => 1, "url" => $url)));
        } else {
  $fans = fans_search($_W['fans']['from_user'],array('nickname','email','mobile'));
            //更新访问人数记录
            $this->updateCourseMemberNum($id, 0);

            include $this->template('course');
        }

    }


    //获取预定课程
    public function doMobileReserve()
    {
        global $_GPC, $_W;

        $this->check_member();
        $weid = $_W['weid'];

        $id = intval($_GPC['id']);
        if (empty($id)) {
            exit;
        }

        $params = array();
        $params[':weid'] = $weid;
        $params[':id'] = $id;

        $sql = "SELECT p.username, p.mobile, p.email, p.status as reserve_stauts, c.*";
        $sql .= " FROM " .tablename('ewei_exam_course_reserve') ." AS p";
        $sql .= " LEFT JOIN " .tablename('ewei_exam_course') . " AS c ON p.courseid = c.id";

        $sql .= " WHERE 1 = 1";
        $sql .= " AND p.weid = :weid";
        $sql .= " AND p.id = :id";

        $item = pdo_fetch($sql, $params);

        $data = array();
        $data['result'] = 1;

        include $this->template('reserve');
    }


    //获取预定课程列表
    public function doMobileReservelist()
    {
        global $_GPC, $_W;

        $this->check_member();
        $weid = $_W['weid'];
        //$member_info = $this->getMemberInfo();


        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $params = array();
            $params[':weid'] = $weid;

            $sql = "SELECT p.*, c.title, c.coursetime";
            $count_sql = "SELECT COUNT(p.id)";

            $where = '';
            $where .= " FROM " .tablename('ewei_exam_course_reserve') ." AS p";
            $where .= " LEFT JOIN " .tablename('ewei_exam_course') . " AS c ON p.courseid = c.id";

            $where .= " WHERE 1 = 1";
            $where .= " AND p.weid = :weid";

            $sql .= $where;
            $sql .= " ORDER BY id DESC";
            if($pindex > 0) {
                // 需要分页
                $start = ($pindex - 1) * $psize;
                $sql .= " LIMIT {$start},{$psize}";
            }

            $count_sql .= $where;
            $list = pdo_fetchall($sql, $params);
            $total = pdo_fetchcolumn($count_sql, $params);

            $page_array = get_page_array($total, $pindex, $psize);

            //print_r($list);exit;

            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('reserve_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }
            die(json_encode($data));

        } else {
            include $this->template('reservelist');
        }
    }


    //获取试卷列表
    public function doMobilepaperlist()
    {
        global $_GPC, $_W;

        $this->check_member();

        $weid = $_W['weid'];
        $search_array = get_cookie($this->_search_key);
        //print_r($search_array);exit;
                $member = $this->getMemberInfo();
        $params = array();
        $params[':weid'] = $weid;
        //$this->check_login();

        if(empty($search_array['year_value'])) {
            $year = 0;
            $year_title = "年份";
        } else {
            $year = $search_array['year_value'];
            $year_title = $year . "年";
        }

        if(empty($search_array['cate_value'])) {
            $pcate = 0;
            $cate_title = "分类";
        } else {
            $pcate = $search_array['cate_value'];
            $cate_title = $search_array['cate_name'];
        }

        $ac = $_GPC['ac'];
        if ($ac == "getDate") {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $sql = "SELECT p.id, p.title, p.displayorder";
            $count_sql = "SELECT COUNT(p.id)";

            $where = '';
            $where .= " FROM " .tablename('ewei_exam_paper') ." AS p";
            if (!empty($pcate)){
                $where .= " LEFT JOIN " .tablename('ewei_exam_paper_category') . " AS c ON p.pcate = c.id";
            }
            $where .= " WHERE 1 = 1";
            $where .= " AND p.weid = :weid";
            if (!empty($year)){
                $where .= " AND p.year = :year";
                $params[':year'] = $year;
            }
            $where .= " AND p.status = 1";
            if (!empty($pcate)){
                $where .= " AND c.weid = :weid";
                $where .= " AND c.status = 1";

                //判断是否为一级分类
                $cate_sql = "SELECT id FROM " .tablename('ewei_exam_paper_category');
                $cate_sql .=  " WHERE parentid = " . $pcate;
                $cate_sql .=  " AND weid = " . $weid;
                //$cate_sql .= " AND status = 1";

                $item = pdo_fetchall($cate_sql);
                $cate_num = count($item);

                if ($cate_num == 0) {
                    $where .= " AND c.id = :pcate";
                    $params[':pcate'] = $pcate;
                } else if ($cate_num > 0) {
                    $item[$cate_num]['id'] = $pcate;
                    $cate_str = '';
                    foreach ($item as $k => $v) {
                        $cate_str .= $v['id'] . ",";
                    }
                    $cate_str = trim($cate_str, ",");
                    $where .= " AND c.id in (" . $cate_str . ")";
                }
            }

            $sql .= $where;
            $sql .= " ORDER BY p.displayorder DESC";
            if($pindex > 0) {
                // 需要分页
                $start = ($pindex - 1) * $psize;
                $sql .= " LIMIT {$start},{$psize}";
            }

            $count_sql .= $where;
            $list = pdo_fetchall($sql, $params);
            foreach($list as &$row){
                 $r = pdo_fetch("select did from ".tablename('ewei_exam_paper_member_record')." where did=1  and weid=:weid and paperid=:paperid and memberid=:memberid limit 1",
                        array(":weid"=>$_W['weid'],
                            ":memberid"=>$member['id'],
                            ":paperid"=>$row['id']));
                $row['did'] = !empty($r);
            }
            unset($row);
            $total = pdo_fetchcolumn($count_sql, $params);

            $page_array = get_page_array($total, $pindex, $psize);

            //print_r($total);exit;

            $data = array();
            $data['result'] = 1;

            ob_start();
            include $this->template('paper_crumb');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }
            die(json_encode($data));
        } else {

            $year_array = array();
            for ($i = date("Y"); $i >= 2000; $i--) {
                $year_array[] = $i;
            }

            $sql = "SELECT id, cname, parentid FROM " .tablename('ewei_exam_paper_category');
            $sql .= " WHERE weid = :weid AND status = 1";
            $sql .= " ORDER BY displayorder DESC,parentid";
            $cate_list = pdo_fetchall($sql, $params);

            //print_r($cate_list);exit;

            include $this->template('paperlist');
        }
    }

    //准备考试
    public function doMobileContinue()
    {
        global $_GPC, $_W;

        $this->check_member();

        $weid = $_W['weid'];
        $member_info = $this->getMemberInfo();

        $sql = "SELECT * FROM " . tablename('ewei_exam_paper_member_record');
        $sql .= " WHERE memberid = :memberid AND weid = :weid AND did = 0";
        $sql .= " ORDER BY id DESC LIMIT 1";

        $params = array();
        $params[':memberid'] = $member_info['id'];
        $params[':weid'] = $weid;
        $item = pdo_fetch($sql, $params);

        if ($item) {
            $params[':recordid'] = $item['id'];

            $sql = "SELECT id, questionid, pageid FROM " . tablename('ewei_exam_paper_member_data');
            $sql .= " WHERE recordid = :recordid AND memberid = :memberid AND weid = :weid";
            $sql .= " ORDER BY pageid DESC LIMIT 1";

            $question_item = pdo_fetch($sql, $params);

            if ($question_item) {
                $pageid = $question_item['pageid'];
            } else {
                $pageid = 1;
            }

            $url = $this->createMobileUrl('start', array('paperid' => $item['paperid'], 'recordid' => $item['id'], 'page' => $pageid));
        } else {
            $url = $this->createMobileUrl('index');

        }
        header("Location:" . $url);
    }

    //准备考试
    public function doMobileReady()
    {
        global $_GPC, $_W;

        $this->check_member();

        $id = intval($_GPC['id']);
        if (empty($id)) {
            exit;
        }
        $weid = $_W['weid'];
        $member_info = $this->getMemberInfo();
        $paper_info = $this->getPaperInfo($id);

        //print_r($paper_info);exit;

        if (checksubmit()) {
            $username = trim($_GPC['username']);
            $mobile = trim($_GPC['mobile']);
            $email = trim($_GPC['email']);

            $data = array();
            $data['realname'] = $username;
            $data['mobile'] = $mobile;
            fans_update($this->_from_user, $data);

            //更新用户信息
            $array = array();
            $array['username'] = $username;
            $array['mobile'] = $mobile;
            $array['email'] = $email;

            $params = array();
            $params['from_user'] = $this->_from_user;
            $params['weid'] = $weid;
            pdo_update('ewei_exam_member', $array, $params);

            //更新考试人数记录
            $this->updatePaperMemberNum($id, 1);

            //插入学员考试记录
            $data = array();
            $data['weid'] = $weid;
            $data['paperid'] = $id;
            $data['memberid'] = $member_info['id'];
            $data['times'] = 0;
            $data['countdown'] = $paper_info['times'] * 60;
            $data['score'] = 0;
            $data['did'] = 0;
            $data['createtime'] = time();
            pdo_insert('ewei_exam_paper_member_record', $data);
            $recordid = pdo_insertid();

            $url = $this->createMobileUrl('start', array('paperid' => $id, 'recordid' => $recordid, 'page' => 1));
            die(json_encode(array("result" => 1, "url" => $url)));
        } else {
            //更新访问人数记录
            $fans = fans_search($_W['fans']['from_user'],array('nickname','email','mobile'));
            $this->updatePaperMemberNum($id, 0);

            include $this->template('ready');
        }
    }



    //准备考试
    public function doMobileAnswer()
    {
        global $_GPC, $_W;

        $this->check_member();

        $weid = $_W['weid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 1;
        $paperid = intval($_GPC['paperid']);
        $recordid = intval($_GPC['recordid']);
        $types_config = $this->_types_config;
        $answer_array = $this->_answer_array;

        $ac = $_GPC['ac'];
        //获取题目信息
        if ($ac == "getDate") {

            $question_item = get_paper_question_list($paperid);
            $total = count($question_item);

            $member_info = $this->getMemberInfo();

            $data = array();
            $data['result'] = 1;

            if ($pindex > $total) {
                $data['result'] = 0;
                $data['error'] = "抱歉，题数参数错误";
                die(json_encode($data));
            }

            $question_info = $question_item[($pindex - 1)];

            if (!$question_info) {
                $data['result'] = 0;
                $data['error'] = "抱歉，试题错误";
                die(json_encode($data));
            }

            //判断用户是否回答过
            $params = array();
            $params[':weid'] = $weid;
            $params[':paperid'] = $paperid;
            $params[':memberid'] = $member_info['id'];
            $params[':recordid'] = $recordid;
            $params[':questionid'] = $question_info['id'];
            $item = get_one_member_question($params);

            if ($item) {
                //已经回答过
                $is_has = 1;
            } else {
                //还没有回答过
                $is_has = 0;
            }

            $page_array = get_page_array($total, $pindex, $psize);

            //print_r($total);exit;

            ob_start();
            include $this->template('question_answer_form');
            $data['code'] = ob_get_contents();
            ob_clean();

            $data['total'] = $total;
            $data['isshow'] = $page_array['isshow'];
            if ($page_array['isshow'] == 1) {
                $data['nindex'] = $page_array['nindex'];
            }
            die(json_encode($data));

        } else {
            $paper_info = $this->getPaperInfo($paperid);
            include $this->template('question_answer');
        }

    }


    //准备考试
    public function doMobileStart()
    {
        global $_GPC, $_W;

        $this->check_member();

        $weid = $_W['weid'];
        $pindex = max(1, intval($_GPC['page']));
        $psize = 1;
        $paperid = intval($_GPC['paperid']);
        $recordid = intval($_GPC['recordid']);
        $types_config = $this->_types_config;
        $answer_array = $this->_answer_array;

        if (empty($paperid) || empty($recordid)) {
            echo "传递参数错误";
            exit;
        }

        $record_info = $this->getRecordInfo($recordid);

        //该试卷已经完成，不能再继续了
        if ($record_info['did'] == 1) {
            echo "该试卷已经完成，不能再继续了";
            exit;
        }

        //print_r($record_info);exit;
        $member_info = $this->getMemberInfo();

        //提交答案
        if (checksubmit()) {
            //print_r($_GPC);exit;

            $data = array();
            $data['result'] = 1;

            $count_flag = intval($_GPC['count_flag']);
            $questionid = intval($_GPC['questionid']);
            $now_page = intval($_GPC['now_page']);
            $btime = intval($_GPC['btime']);
            $type = intval($_GPC['type']);
            $items = "";
            $answer = "";
            $now_time = time();

            //试题类型
            switch ($type)
            {
                case 1:
                    //判断题
                    $answer = $_GPC['answer1'];
                    break;

                case 2:
                    //单选题
                    $answer = $_GPC['answer2'];
                    //$items = serialize($_GPC['items2']);
                    break;

                case 3:
                    //多选题
                    $arr = $_GPC['answer3'];
                    if (empty($arr)) {
                        $answer = '';
                    } else {
                        $answer = implode("", $arr);
                    }
                    //$items = serialize($_GPC['items3']);
                    break;
            }

            //判断答案是否正确
            $now_question_info = get_one_question($questionid);

            if (empty($answer)) {
                $isright = 0;
            } else {
                if ($now_question_info['answer'] == $answer) {
                    $isright = 1;
                } else {
                    $isright = 0;
                }
            }

            //判断用户是否回答过
            $params = array();
            $params[':weid'] = $weid;
            $params[':paperid'] = $paperid;
            $params[':memberid'] = $member_info['id'];
            $params[':recordid'] = $recordid;
            $params[':questionid'] = $questionid;
            $item = get_one_member_question($params);

            //要添加或者更新的数据
            $array = array();
            $array['isright'] = $isright;
            $array['answer'] = $answer;
            $array['type'] = $type;
            $array['pageid'] = $now_page;

            if ($item) {
                //已经回答过
                pdo_update('ewei_exam_paper_member_data', $array, array('id' => $item['id']));

                if ($isright == 1 && $item['isright'] == 0) {
                    //多少人正确+1
                    $this->updateQuestionMemberNum($questionid, 2);
                }

            } else {
                //还没有回答过
                $array['weid'] = $weid;
                $array['paperid'] = $paperid;
                $array['memberid'] = $member_info['id'];
                $array['recordid'] = $recordid;
                $array['questionid'] = $questionid;
                //$data['times'] = $now_time - $btime;
                $array['createtime'] = $now_time;
                pdo_insert('ewei_exam_paper_member_data', $array);

                if ($isright) {
                    //多少人做过,正确+1
                    $this->updateQuestionMemberNum($questionid, 3);
                } else {
                    //多少人做过+1
                    $this->updateQuestionMemberNum($questionid, 1);
                }
            }

            //统计该用户当前试卷的做题情况
            if ($count_flag) {
                $paper_info = $this->getPaperInfo($paperid);
                $total = $paper_info['total'];
                $now_total = get_count_one_paper_record($params);

                if($now_total == $total) {
                    $msg = "共" . $total . "题，您已全部做完";
                } else {
                    $msg = "共" . $total . "题，您做了" . $now_total . "题，还剩" . ($total - $now_total) . "题未做";
                }
                $data['count_msg'] = $msg;
            }

            //print_r($data);exit;
            //$recordid = pdo_insertid();

            die(json_encode($data));

        } else {
            $question_item = get_paper_question_list($paperid);
            //print_r($question_item);exit;
            $total = count($question_item);

            $ac = $_GPC['ac'];
            //获取题目信息
            if ($ac == "getDate") {
                $data = array();
                $data['result'] = 1;

                if ($pindex > $total) {
                    $data['result'] = 0;
                    $data['error'] = "抱歉，题数参数错误";
                    die(json_encode($data));
                }

                $question_info = $question_item[($pindex - 1)];

                //print_r($question_info);exit;

                if (!$question_info) {
                    $data['result'] = 0;
                    $data['error'] = "抱歉，试题错误";
                    die(json_encode($data));
                }


                //判断用户是否回答过
                $params = array();
                $params[':weid'] = $weid;
                $params[':paperid'] = $paperid;
                $params[':memberid'] = $member_info['id'];
                $params[':recordid'] = $recordid;
                $params[':questionid'] = $question_info['id'];
                $item = get_one_member_question($params);

                if ($item) {
                    //已经回答过
                    $is_has = 1;
                } else {
                    //还没有回答过
                    $is_has = 0;
                }

                $page_array = get_page_array($total, $pindex, $psize);

                //print_r($total);exit;

                ob_start();
                include $this->template('question_form');
                $data['code'] = ob_get_contents();
                ob_clean();

                $data['total'] = $total;
                $data['isshow'] = $page_array['isshow'];
                if ($page_array['isshow'] == 1) {
                    $data['nindex'] = $page_array['nindex'];
                }
                die(json_encode($data));

            } else if ($ac == "close_exam") {
                $paper_info = $this->getPaperInfo($paperid);
                $now_question = $this->getRecordQuestion($paper_info, $recordid);

                //结束本次考试记录
                $data = array();
                $data['score'] = $now_question['score'];
                $data['did'] = 1;
                pdo_update('ewei_exam_paper_member_record', $data, array('id' => $recordid));

                //计算平均分和用时
                $sql = "SELECT AVG(score) as avg_score, AVG(times) as avg_times FROM " . tablename('ewei_exam_paper_member_record');
                $sql .= " WHERE paperid = :paperid AND weid = :weid AND did = 1";
                $question_item = pdo_fetch($sql, array(':paperid' => $paperid, ':weid' => $weid));

                $avg_score = round($question_item['avg_score'], 2);
                //$avg_times = round($question_item['avg_times']);
                $avg_times = $question_item['avg_times'];

                //更新到试卷信息表中
                $data = array();
                $data['avscore'] = $avg_score;
                $data['avtimes'] = $avg_times;
                pdo_update('ewei_exam_paper', $data, array('id' => $paperid));

                $url = $this->createMobileUrl('score', array('paperid' => $paperid, 'recordid' => $recordid));
                die(json_encode(array("result" => 1, "url" => $url)));

            } else if ($ac == "update_countdown") {
                $paper_info = $this->getPaperInfo($paperid);

                if ($record_info['countdown'] > 0) {
                    $countdown = intval($_GPC['total_time']);
                    //更新考试剩余时间
                    $data = array();
                    $data['countdown'] = $countdown;
                    $data['times'] = $paper_info['times'] * 60 - $countdown;
                    pdo_update('ewei_exam_paper_member_record', $data, array('id' => $recordid));
                } else {
                    $data['times'] = $paper_info['times'] * 60;
                    pdo_update('ewei_exam_paper_member_record', $data, array('id' => $recordid));
                }
            } else {
                $paper_info = $this->getPaperInfo($paperid);
                include $this->template('question');
            }

        }

    }

    //准备考试
    public function doMobileScore()
    {
        global $_GPC, $_W;

        $this->check_member();

        $weid = $_W['weid'];

        $paperid = intval($_GPC['paperid']);
        $recordid = intval($_GPC['recordid']);

        $sql = "SELECT * FROM " . tablename('ewei_exam_paper_member_record');
        $sql .= " WHERE id = :id AND weid = :weid AND did = 1";
        $record_info = pdo_fetch($sql, array(':id' => $recordid, ':weid' => $weid));
        $paper_info = $this->getPaperInfo($paperid);

        //$record_info = $this->getRecordInfo($recordid);

        $sql = "SELECT r.*, m.userid, m.username FROM " . tablename('ewei_exam_paper_member_record') . " AS r";
        $sql .= " LEFT JOIN  " . tablename('ewei_exam_member') . " as m ON r.memberid = m.id";
        $sql .= " WHERE r.paperid = :paperid AND r.weid = :weid AND m.weid = :weid AND did = 1";
        $sql .= " ORDER BY r.score DESC, r.times ASC LIMIT 10";
        $order_info = pdo_fetchall($sql, array(':paperid' => $paperid, ':weid' => $weid));

        $url_answer = $this->createMobileUrl('answer', array('paperid' => $paperid, 'recordid' => $recordid, 'page' => 1));
        $url = $this->createMobileUrl('ready', array('id' => $paperid));
        //print_r($order_info);exit;

        include $this->template('score');
    }


    //获取试卷中当前的考题和分数情况
    public function getRecordQuestion($array, $recordid)
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];
        $params = array();
        $params[':paperid'] = $array['id'];
        $params[':recordid'] = $recordid;
        $params[':weid'] = $weid;

        $score_array = array();
        $score = 0;

        foreach ($array['types'] as $key => $value) {
            if ($value['has'] == 1) {
                $params[':type'] = $key;

                $count_sql = "SELECT COUNT(id) FROM " . tablename('ewei_exam_paper_member_data');
                $count_sql .= " WHERE recordid = :recordid ";
                $count_sql .= " AND paperid = :paperid ";
                //$count_sql .= " AND memberid = :memberid ";
                $count_sql .= " AND weid = :weid";
                $count_sql .= " AND type = :type";
                $count_sql .= " AND isright = 1";
                $total = pdo_fetchcolumn($count_sql, $params);

                $score_array[$key]['num'] = $total;
                $score_array[$key]['score'] = $total * $array['types'][$key]['one_score'];
                $score += $score_array[$key]['score'];
            }
        }

        $data = array();
        $data['data'] = $score_array;
        $data['score'] = $score;

        //print_r($data);exit;

        return $data;
    }

    //获取试卷和试卷类型的信息
    public function getPaperInfo($paper_id)
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];
        $sql = "SELECT p.id, p.title, p.level, p.year, p.avscore, p.avtimes, t.score, t.types, t.times,p.description FROM " . tablename('ewei_exam_paper') . " AS p";
        $sql .= " LEFT JOIN " . tablename('ewei_exam_paper_type') . " AS t on p.tid = t.id";
        $sql .= " WHERE p.id = :id AND p.weid = :weid";
        $sql .= " LIMIT 1";

        $params = array();
        $params[':id'] = intval($paper_id);
        $params[':weid'] = $weid;

        $item = pdo_fetch($sql, $params);
        $item['types'] = unserialize($item['types']);

        $total = 0;
        foreach ($item['types'] as $k => $v) {
            if ($v['has'] == 1) {
                $total += $v['num'];
            }
        }

        $item['total'] = $total;
        return $item;
    }

    //获取试卷和试卷类型的信息
    public function getRecordInfo($recordid)
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];
        $sql = "SELECT * FROM " . tablename('ewei_exam_paper_member_record');
        $sql .= " WHERE id = :id AND weid = :weid";
        $sql .= " LIMIT 1";

        $params = array();
        $params[':id'] = intval($recordid);
        $params[':weid'] = $weid;
        $item = pdo_fetch($sql, $params);
        return $item;
    }

    //获取用户的基本信息
    public function getMemberInfo()
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];

        $item = pdo_fetch("SELECT * FROM " . tablename('ewei_exam_member') . " WHERE from_user = :from_user AND weid = :weid LIMIT 1", array(':from_user' => $this->_from_user, ':weid' => $weid));

        //print_r($item);exit;

        return $item;
    }

    //ajax数据处理,包含 年份选择 分类选择
    public function doMobileajaxData()
    {
        global $_GPC, $_W;
        $referer = $_GPC['referer'];
        $data = $this->getSearchArray();
        $key = $this->_search_key;
        switch ($_GPC['ac'])
        {
            //选择年份选，分类
            case 'year':
                $data['year_value'] = intval($_GPC['year_value']);
                $data['cate_value']= intval($_GPC['cate_value']);
                $data['cate_name'] = $_GPC['cate_name'];

                insert_cookie($key, $data);
                die(json_encode(array("result" => 1)));
                break;
        }
    }


    function getSearchArray(){
        $search_array = get_cookie($this->_search_key);
        if (empty($search_array)) {
            //默认搜索参数
            $search_array['year_value'] = 0;
            $search_array['cate_value'] = 0;
            $search_array['cate_name'] = '';

            insert_cookie($this->_search_key, $search_array);
        }

        return $search_array;
    }

    //更新试卷统计 $type 0访问人数 1报名人数
    public function updateCourseMemberNum($courseid, $type)
    {
        global $_GPC, $_W;

        switch ($type)
        {
            case 1:
                //报名人数+1
                $set = " fansnum = fansnum + 1";
                break;
            case 0:
                //访问人数确+1
                $set = " viewnum = viewnum + 1";
                break;
        }
        pdo_query("update " . tablename('ewei_exam_course') . " set " . $set . " where id=:id", array(":id" => $courseid));
    }

    //更新试卷统计 $type 0访问人数 1考试人数
    public function updatePaperMemberNum($paperid, $type)
    {
        global $_GPC, $_W;

        switch ($type)
        {
            case 1:
                //报名人数+1
                $set = " fansnum = fansnum + 1";
                break;
            case 0:
                //访问人数确+1
                $set = " viewnum = viewnum + 1";
                break;
        }
        pdo_query("update " . tablename('ewei_exam_paper') . " set " . $set . " where id=:id", array(":id" => $paperid));
    }

    //更新试题统计
    public function updateQuestionMemberNum($questionid, $type)
    {
        switch ($type)
        {
            case 1:
                //多少人做过+1
                $set = " fansnum = fansnum + 1";
                break;
            case 2:
                //多少人正确+1
                $set = " correctnum = correctnum + 1";
                break;
            case 3:
                //多少人做过,正确+1
                $set = " fansnum = fansnum + 1, correctnum = correctnum + 1";
                break;
        }

        pdo_query("update " . tablename('ewei_exam_question') . " set " . $set . " where id=:id", array(":id" => $questionid));
    }

    public function doMobileabout()
    {
        global $_W, $_GPC;

        $item = pdo_fetch("select about from " . tablename('ewei_exam_sysset') . " where weid=:weid limit 1", array(":weid" => $_W['weid']));
        $about = $item['about'];
        $this->check_member();
        include $this->template('about');
    }

    public function doMobileindex()
    {
        global $_W, $_GPC;

        $this->check_member();
        $set = $this->_set_info;
        include $this->template('index');
    }

}
