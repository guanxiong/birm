<?php

/**
 * 微考试模块定义
 *
 */
defined('IN_IA') or exit('Access Denied');
include "./source/modules/ewei_exam/model.php";
class Ewei_examModule extends WeModule
{
    public $_weid = '';
    public $_types_config = '';
    public $_set_info = array();
    public $_answer_array = array();

    function __construct()
    {
        global $_W;
        $this->_weid = $_W['weid'];

        $this->_set_info =  get_ewei_exam_sysset();
        $init_param =  get_init_param();
        $this->_types_config = $init_param['types_config'];
        $this->_answer_array = $init_param['answer_array'];

        //print_r($init_param);exit;
    }


    public function fieldsFormDisplay($rid = 0)
    {
        //要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
        global $_W, $_GPC;
        if (!empty($rid)) {
            $reply = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_reply') . " WHERE rid = :rid", array(':rid' => $rid));
            if (!empty($reply)) {
                foreach ($reply as $row) {
                    $paperids[$row['paperid']] = $row['paperid'];
                }
                $album = pdo_fetchall("SELECT id, title, thumb, description FROM " . tablename('ewei_exam_paper') . " WHERE id IN (" . implode(',', $paperids) . ")", array(), 'id');
            }
        }
        include $this->template('rule');
    }

    public function fieldsFormValidate($rid = 0)
    {
        //规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
        return '';
    }

    public function fieldsFormSubmit($rid)
    {
        //规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
        global $_W, $_GPC;
        if (!empty($_GPC['paperid'])) {
            foreach ($_GPC['paperid'] as $aid) {
                pdo_insert('ewei_exam_reply', array(
                    'rid' => $rid,
                    'paperid' => $aid,
                    'weid' => $_W['weid'],
                ));
            }
        }
    }

    public function ruleDeleted($rid)
    {
        //删除规则时调用，这里 $rid 为对应的规则编号
    }

    public function dodelete() {
        global $_GPC, $_W;

        $id = intval($_GPC['id']);
        pdo_delete('ewei_exam_reply', array('id' => $id));
        message("删除成功!", referer(), "success");
    }

    public function doCourse()
    {
        global $_GPC, $_W;

        $op = $_GPC['op'];
        $weid = $_W['weid'];

        //echo tpl_form_field_daterange('datelimit', array('starttime'=>$item['starttime'],'endtime'=>$item['endtime']), array('time'=>true));

        //exit;

        if ($op == 'edit') {
            //编辑
            $id = intval($_GPC['id']);
            //$tid = intval($_GPC['tid']);

            if (checksubmit()) {
                $insert = array(
                    'weid' => $weid,
                    'displayorder' => $_GPC['displayorder'],
                    'title' => $_GPC['title'],
                    'ccate' => $_GPC['ccate'],
                    'ctype' => $_GPC['ctype'],
                    'ctotal' => $_GPC['ctotal'],
                    'teachers' => $_GPC['teachers'],
                    'starttime' => strtotime($_GPC['datelimit-start']),
                    'endtime' => strtotime($_GPC['datelimit-end']),
                    'coursetime' => strtotime($_GPC['coursetime']),
                    'times' => $_GPC['times'],
                    'week' => $_GPC['week'],
                    'description' => $_GPC['description'],
                    'content' => $_GPC['content'],
                    'address' => $_GPC['address'],
                    'week' => $_GPC['week'],
                    'location_p' => $_GPC['location_p'],
                    'location_c' => $_GPC['location_c'],
                    'location_a' => $_GPC['location_a'],
                    'lat' => $_GPC['lat'],
                    'lng' => $_GPC['lng'],
                    'status' => $_GPC['status'],
                );

                if (!empty($_FILES['thumb']['tmp_name'])) {
                    file_delete($_GPC['thumb-old']);
                    $upload = file_upload($_FILES['thumb']);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
                    $insert['thumb'] = $upload['path'];
                }


                if (empty($id)) {
                    pdo_insert('ewei_exam_course', $insert);
                } else {
                    pdo_update('ewei_exam_course', $insert, array('id' => $id));
                }
                message("课程信息保存成功!", $this->createWebUrl('course'), "success");
            }
            if (!empty($id)) {
                $item = pdo_fetch("select * from " . tablename('ewei_exam_course') . " where id=:id limit 1", array(":id" => $id));
            }

            if(!empty($item)){
                $paper_category = pdo_fetch("select id, cname as title from ".tablename('ewei_exam_course_category')." where id=:id limit 1",array(':id'=>$item['ccate']));
            }

            //print_r($paper_category);exit;
            include $this->template('course_form');

        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete("ewei_exam_course", array("id" => $id));
            message("课程信息删除成功!", referer(), "success");

        } else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                pdo_delete("ewei_exam_course", array("id" => $id));
            }
            $this->message('课程信息删除成功！', '', 0);
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
                    pdo_update('ewei_exam_course', array('status' => $show_status), array('id' => $id));
                }
            }
            //message('操作成功！', '', 0);
            exit();
        } else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('ewei_exam_course', array('status' => $_GPC['status']), array('id' => $id));
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        } else {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND `title` LIKE :keywords';
                $params[':keywords'] = "%{$_GPC['title']}%";
            }

            if (!empty($_GPC['ccate'])) {
                $ccate = intval($_GPC['ccate']);
                //判断是否为一级分类
                $cate_sql = "SELECT id FROM " .tablename('ewei_exam_paper_category');
                $cate_sql .=  " WHERE parentid = " . $ccate;
                $cate_sql .=  " AND weid = " . $weid;
                //$cate_sql .= " AND status = 1";

                $item = pdo_fetchall($cate_sql);
                $cate_num = count($item);

                if ($cate_num == 0) {
                    $sql .= " AND ccate = :ccate";
                    $params[':ccate'] = $ccate;
                } else if ($cate_num > 0) {
                    $item[$cate_num]['id'] = $ccate;
                    $cate_str = '';
                    foreach ($item as $k => $v) {
                        $cate_str .= $v['id'] . ",";
                    }
                    $cate_str = trim($cate_str, ",");
                    $sql .= " AND ccate in (" . $cate_str . ")";
                }
            }

            $select_sql = "SELECT * FROM " . tablename('ewei_exam_course') . " as c";
            $select_sql .= " WHERE c.weid = '{$_W['weid']}'  $sql ORDER BY displayorder DESC,id  LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($select_sql, $params);

            $category = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_course_category') . " WHERE weid = '{$_W['weid']}' AND status = 1 ORDER BY parentid ASC, displayorder DESC");

            //print_r($category);exit;

            $count_sql = "SELECT COUNT(c.id) FROM " . tablename('ewei_exam_paper') . " as c";
            $count_sql .= " WHERE c.weid = '{$_W['weid']}'" . $sql;

            $total = pdo_fetchcolumn($count_sql, $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('course');
        }

    }

    public function doCourse_category()
    {
        global $_GPC, $_W;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update('ewei_exam_course_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('course_category', array('op' => 'display')), 'success');
            }
            $children = array();
            $category = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_course_category') . " WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC");
            foreach ($category as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($category[$index]);
                }
            }
            include $this->template('course_category');
        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('ewei_exam_course_category') . " WHERE id = '$id'");
            } else {
                $item = array(
                    'displayorder' => 0,
                );
            }

            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, cname FROM " . tablename('ewei_exam_course_category') . " WHERE id = '$parentid'");
                if (empty($parent)) {
                    message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入分类名称！');
                }

                $data = array(
                    'weid' => $_W['weid'],
                    'cname' => $_GPC['cname'],
                    'displayorder' => intval($_GPC['displayorder']),
                    'parentid' => intval($parentid),
                    'description' => $_GPC['description'],
                    'status' => intval($_GPC['status'])
                );
                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update('ewei_exam_course_category', $data, array('id' => $id));
                } else {
                    pdo_insert('ewei_exam_course_category', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('course_category', array('op' => 'display')), 'success');
            }
            include $this->template('course_category');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id, parentid FROM " . tablename('ewei_exam_course_category') . " WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('course_category', array('op' => 'display')), 'error');
            }
            pdo_delete('ewei_exam_course_category', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('course_category', array('op' => 'display')), 'success');
        } else if ($operation == 'query') {
            $kwd = trim($_GPC['keyword']);

            $sql = 'SELECT id, cname as title FROM ' . tablename('ewei_exam_course_category') . ' WHERE `weid`=:weid';
            $sql .= ' ORDER BY parentid ASC, displayorder DESC';
            $params = array();
            $params[':weid'] = $_W['weid'];
            if (!empty($kwd)) {
                $sql .= " AND `cname` LIKE :cname";
                $params[':cname'] = "%{$kwd}%";
            }
            $ds = pdo_fetchall($sql, $params);
            include $this->template('course_category_query');
        }
    }

    public function doPaper_category()
    {
        global $_GPC, $_W;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update('ewei_exam_paper_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('paper_category', array('op' => 'display')), 'success');
            }
            $children = array();
            $category = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_paper_category') . " WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder DESC");
            foreach ($category as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($category[$index]);
                }
            }
            include $this->template('paper_category');
        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('ewei_exam_paper_category') . " WHERE id = '$id'");
            } else {
                $item = array(
                    'displayorder' => 0,
                );
            }

            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, cname FROM " . tablename('ewei_exam_paper_category') . " WHERE id = '$parentid'");
                if (empty($parent)) {
                    message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('post'), 'error');
                }
            }
            if (checksubmit('submit')) {
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入分类名称！');
                }

                $data = array(
                    'weid' => $_W['weid'],
                    'cname' => $_GPC['cname'],
                    'displayorder' => intval($_GPC['displayorder']),
                    'parentid' => intval($parentid),
                    'description' => $_GPC['description'],
                    'status' => intval($_GPC['status'])
                );
                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update('ewei_exam_paper_category', $data, array('id' => $id));
                } else {
                    pdo_insert('ewei_exam_paper_category', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('paper_category', array('op' => 'display')), 'success');
            }
            include $this->template('paper_category');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id, parentid FROM " . tablename('ewei_exam_paper_category') . " WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('paper_category', array('op' => 'display')), 'error');
            }
            pdo_delete('ewei_exam_paper_category', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('paper_category', array('op' => 'display')), 'success');
        } else if ($operation == 'query') {
            $kwd = trim($_GPC['keyword']);

            $sql = 'SELECT id, cname as title FROM ' . tablename('ewei_exam_paper_category') . ' WHERE `weid`=:weid';
            $sql .= ' ORDER BY parentid ASC, displayorder DESC';
            $params = array();
            $params[':weid'] = $_W['weid'];
            if (!empty($kwd)) {
                $sql .= " AND `cname` LIKE :cname";
                $params[':cname'] = "%{$kwd}%";
            }
            $ds = pdo_fetchall($sql, $params);
            include $this->template('paper_category_query');

        }
    }

    public function doReserve()
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];

        $op = $_GPC['op'];
        if($op=='edit'){
            $id = intval($_GPC['id']);
            if (!empty($id)) {

                $params = array();
                $params[':weid'] = $weid;
                $params[':id'] = $id;

                $sql = "SELECT p.courseid, p.msg, p.username, p.mobile, p.email, p.status as reserve_stauts, p.createtime as reserve_createtime, c.*";
                $sql .= " FROM " .tablename('ewei_exam_course_reserve') ." AS p";
                $sql .= " LEFT JOIN " .tablename('ewei_exam_course') . " AS c ON p.courseid = c.id";

                $sql .= " WHERE 1 = 1";
                $sql .= " AND p.weid = :weid";
                $sql .= " AND p.id = :id";

                $item = pdo_fetch($sql, $params);

                if (empty($item)) {
                    message('抱歉，订单不存在或是已经删除！', '', 'error');
                }
            } else {
                message('抱歉，参数错误！', '', 'error');
            }

            if (checksubmit('submit')) {
                $old_status = $_GPC['oldstatus'];

                $data = array(
                    'status' => $_GPC['status'],
                    'msg' => $_GPC['msg'],
                    'mngtime' => time(),
                );

                //订单确认
                if ($data['status'] == 1 && $old_status != 1) {
                    pdo_query("update " . tablename('ewei_exam_course') . " set fansnum = fansnum + 1 where id=:id", array(":id" => $item['courseid']));
                }

                //订单取消
                if ($old_status == 1 && ($data['status'] != 1)) {
                    if ($item['fansnum'] > 0) {
                        pdo_query("update " . tablename('ewei_exam_course') . " set fansnum = fansnum - 1 where id=:id", array(":id" => $item['courseid']));
                    }
                }

                pdo_update('ewei_exam_course_reserve', $data, array('id' => $id));
                message('订单信息处理完成！', $this->createWebUrl('reserve') , 'success');
            }


            include $this->template('reserve_form');
        }
        else if($op=='delete'){
            $id = intval($_GPC['id']);
            $item = pdo_fetch("SELECT id FROM " . tablename('ewei_exam_course_reserve') . " WHERE id = :id LIMIT 1", array(':id' => $id));

            if (empty($item)) {
                message('抱歉，订单不存在或是已经删除！', '', 'error');
            }
            pdo_delete('ewei_exam_course_reserve', array('id' => $id));
            message('删除成功！', referer(), 'success');
        } else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                pdo_delete('ewei_exam_course_reserve', array('id' => $id));
            }
            //$this->message('订单删除成功！', '', 0);
            exit();
        } else{

            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;

            $title = $_GPC['title'];
            $username = $_GPC['username'];
            $mobile = $_GPC['mobile'];
            $ordersn = $_GPC['ordersn'];
            $status =  $_GPC['status'];
            $ctype =  $_GPC['ctype'];

            $params = array();
            $params[':weid'] = $weid;

            $sql = "SELECT p.*, c.title, c.coursetime, c.ctype";
            $count_sql = "SELECT COUNT(p.id)";

            $where = '';
            $where .= " FROM " .tablename('ewei_exam_course_reserve') ." AS p";
            $where .= " LEFT JOIN " .tablename('ewei_exam_course') . " AS c ON p.courseid = c.id";

            $where .= " WHERE 1 = 1";
            $where .= " AND p.weid = :weid";

            if (!empty($title)) {
                $where .= ' AND c.title LIKE :title';
                $params[':title'] = "%{$title}%";
            }

            if (!empty($username)) {
                $where .= ' AND p.username LIKE :username';
                $params[':username'] = "%{$username}%";
            }

            if (!empty($mobile)) {
                $where .= ' AND p.mobile LIKE :mobile';
                $params[':mobile'] = "%{$mobile}%";
            }

            if (!empty($ordersn)) {
                $where .= ' AND p.ordersn LIKE :ordersn';
                $params[':ordersn'] = "%{$ordersn}%";
            }

            if($status != ''){
                $where.=" and p.status=".intval($status);
            }

            if($ctype != ''){
                $where.=" and c.ctype=".intval($ctype);
            }

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

            //print_r($ctype);exit;

//

//
//            $roomtitle = $_GPC['roomtitle'];
//            $hoteltitle = $_GPC['hoteltitle'];
//            $condition = '';
//            $params = array();
//            if (!empty($hoteltitle)) {
//                $condition .= ' AND h.title LIKE :hoteltitle';
//                $params[':hoteltitle'] = "%{$hoteltitle}%";
//            }
//            if (!empty($roomtitle)) {
//                $condition .= ' AND r.title LIKE :roomtitle';
//                $params[':roomtitle'] = "%{$roomtitle}%";
//            }
//
//            if (!empty($realname)) {
//                $condition .= ' AND o.name LIKE :realname';
//                $params[':realname'] = "%{$realname}%";
//            }
//            if (!empty($mobile)) {
//                $condition .= ' AND o.mobile LIKE :mobile';
//                $params[':mobile'] = "%{$mobile}%";
//            }

//            if(!empty($hotelid)){
//                $condition.=" and o.hotelid=".$hotelid;
//            }
//            if(!empty($roomid)){
//                $condition.=" and o.roomid=".$roomid;
//            }

//
//            $pindex = max(1, intval($_GPC['page']));
//            $psize = 20;
//            $list = pdo_fetchall("SELECT o.*,h.title as hoteltitle,r.title as roomtitle FROM " . tablename('hotel2_order') . " o left join " . tablename('hotel2') .
//                "h on o.hotelid=h.id left join ".tablename("hotel2_room")." r on r.id = o.roomid  WHERE o.weid = '{$_W['weid']}' $condition ORDER BY o.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize,$params);
//            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM  ' . tablename('hotel2_order') . " o left join " . tablename('hotel2') .
//                "h on o.hotelid=h.id left join ".tablename("hotel2_room")." r on r.id = o.roomid  WHERE o.weid = '{$_W['weid']}' $condition",$params);


            //$pager = pagination($total, $pindex, $psize);
            include $this->template('order');
        }
    }

    public function doQuestion()
    {
        global $_GPC, $_W;

        $op = $_GPC['op'];
        $weid = $_W['weid'];
        $types_config = $this->_types_config;


        if ($op == 'edit') {
            //编辑
            $id = intval($_GPC['id']);
            $paperid = intval($_GPC['paperid']);
            $referer = intval($_GPC['referer']);
            $isimg = intval($_GPC['isimg']);

            $answer_array = $this->_answer_array;

            if ($_W['ispost']) {
                $is_next = $_GPC['is_next'];

                $insert = array(
                    'weid' => $weid,
                    'question' => $_GPC['question'],
                    'type' => $_GPC['type'],
                    'paperid' => $_GPC['paperid'],
                    'level' => $_GPC['level'],
                    'poolid' => $_GPC['poolid'],
                    'explain' => $_GPC['explain'],
                );

                $check_flag = 1;
                if (!empty($id) && ($_GPC['old_type'] == $_GPC['type'])) {
                    $check_flag = 0;
                }

                if (empty($_GPC['paperid'])) {
                    $check_flag = 0;
                }

                if ($check_flag == 1) {
                    $data = $this->checkAddQuestion($insert);
                    if ($data['result'] != 1) {
                        message($data['msg'], '', 'error');
                    }
                }

                unset($insert['paperid']);
                $type = $insert['type'];
                $items = "";
                $answer = "";
                if ($type == 1) {
                    $answer = $_GPC['answer1'];
                } else if ($type == 2) {
                    $answer = $_GPC['answer2'];
                    $items = serialize($_GPC['items2']);
                } else if ($type == 3) {
                    $arr = $_GPC['answer3'];
                    $answer = implode("", $arr);
                    $items = serialize($_GPC['items3']);
                } else if ($type == 4) {
                    $answer = $_GPC['answer4'];
                } else if ($type == 5) {
                    $answer = $_GPC['answer5'];
                }
                $insert['answer'] = $answer;
                $insert['items'] = $items;
                $insert['thumb'] = $_GPC['thumb'];
                $insert['isimg'] = $isimg;

                //print_r($_GPC);exit;

                if ($isimg == 1 && ($type == 2 || $type == 3)) {
                    $img_items = array();
                    $item_name = "img_items" . $type . "_";

                    foreach ($answer_array as $key => $value) {
                        $img_items[$key] = $_GPC[$item_name . $key];

                        if (!empty($_GPC[$item_name . $key . '-old'])) {
                            file_delete($_GPC[$item_name . $key . '-old']);
                        }
                    }
                    $insert['img_items'] = serialize($img_items);
                }

                //print_r($insert);exit;

                if (!empty($_GPC['thumb-old'])) {
                    file_delete($_GPC['thumb-old']);
                }
                tpl_form_field_image(1,2);

                if (empty($id)) {
                    pdo_insert('ewei_exam_question', $insert);

                    //排序表
                    if (!empty($paperid)) {
                        $paper_question_data = array();
                        $paper_question_data['displayorder'] = 0;
                        $paper_question_data['paperid'] = $paperid;
                        $paper_question_data['questionid'] = pdo_insertid();
                        pdo_insert('ewei_exam_paper_question', $paper_question_data);
                        $this->check_paper_full($paperid);
                    }

                } else {
                    pdo_update('ewei_exam_question', $insert, array('id' => $id));

                    //排序表
//                    if (!empty($paperid)) {
//                        $paper_question_data['questionid'] = pdo_insertid();
//                        pdo_insert('exam_paper_question', $paper_question_data);
//                    } else {
//                        $order_id = pdo_fetch("select id from " . tablename('ewei_exam_paper_question') . " where questionid=:id limit 1", array(":id" => $id));
//                        if ($order_id) {
//                            pdo_update('exam_paper_question', $paper_question_data, array('questionid' => $id));
//                        } else {
//                            $paper_question_data['questionid'] = $id;
//                            pdo_insert('exam_paper_question', $paper_question_data);
//                        }
//                    }

                }

                if ($is_next) {
                    $array = array();
                    $array['op'] = 'edit';
                    $array['poolid'] = $insert['poolid'];
                    $array['paperid'] = $paperid;

                    $url = $this->createWebUrl('question', $array);
                } else {
                    if ($referer == 1) {
                        session_start();
                        $url = $_SESSION['last_url'];
                    } else {
                        $url = $this->createWebUrl('question');
                    }
                }

                message("试题信息保存成功!", $url, "success");
            }

            if (!empty($id)) {
                //修改试题
                $item = pdo_fetch("select * from " . tablename('ewei_exam_question') . " where id=:id limit 1", array(":id" => $id));

                if (!empty($item)) {
                    $item['items'] = unserialize($item['items']);
                    $item['img_items'] = unserialize($item['img_items']);
                    //$paperid = $item['paperid'];
                    $pool_id = $item['poolid'];
                }
            } else {
                //添加试题
                //$paperid = $_GPC['paperid'];
                $pool_id = $_GPC['poolid'];
                $item['type'] = intval($_GPC['type_key']);
            }

            if (!empty($paperid)) {
                $paper = pdo_fetch("select id,title from " . tablename('ewei_exam_paper') . " where id=:id limit 1", array(':id' => $paperid));
                $paper_info = $this->getPaperInfo($paperid);

                $d_question = $this->getDefaultPaperQuestion($paper_info);
                $now_question_data = $d_question['data'];

                $submit_array = array();
                $pager_str = '该试卷包含 ';
                foreach ($paper_info['types'] as $k => $v) {
                    if ($v['has'] == 1) {
                        $pager_str .= $types_config[$k] . "(" .$now_question_data[$k]['num'] . "/". $v['num'] .")道 ";
                        if ($now_question_data[$k]['num'] < $v['num']) {
                            //正常可以添加
                            $submit_array[$k]['status'] = 1;
                        } else {
                            //试卷此题型已满
                            $submit_array[$k]['status'] = 2;
                        }
                    } else {
                        //试卷不包含此题型
                        $submit_array[$k]['status'] = 3;
                    }
                }
                //$now_score = $d_question['score'];
                //$score = $paper_info['score'];
            }

            if (!empty($pool_id)) {
                $pool = pdo_fetch("select id,title from " . tablename('ewei_exam_pool') . " where id=:id limit 1", array(':id' => $pool_id));
            }



            include $this->template('question_form');
        } else if ($op == 'deleteFromPaper') {
            $id = intval($_GPC['id']);
            $paperid = intval($_GPC['paperid']);

            if (!empty($id) && !empty($paperid)) {
                pdo_delete("ewei_exam_paper_question", array("questionid" => $id, "paperid" => $paperid));
                $this->check_paper_full($paperid);
                //pdo_update("ewei_exam_paper_question",array("paperid" => 0),array("questionid" => $id, "paperid" => $paperid));
            }

            message("试题已经从该试卷删除!", referer(), "success");
        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete("ewei_exam_question", array("id" => $id));
            pdo_delete("ewei_exam_paper_question", array("questionid" => $id));
            $this->check_paper_full_by_questionid($id);

            message("试题信息删除成功!", referer(), "success");
        } else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                pdo_delete("ewei_exam_question", array("id" => $id));
                pdo_delete("ewei_exam_paper_question", array("questionid" => $id));
                $this->check_paper_full_by_questionid($id);
            }
            $this->message('试题信息删除成功！', '', 0);
            exit();
        } else if ($op == 'query') {
            $kwd = trim($_GPC['keyword']);
            $type = intval($_GPC['type']);
            $poolid = intval($_GPC['poolid']);
            $sql = 'SELECT id,title FROM ' . tablename('ewei_exam_question') . ' WHERE `weid`=:weid';
            $params = array();
            $params[':weid'] = $_W['weid'];
            if (!empty($kwd)) {
                $sql .= " AND `title` LIKE :title";
                $params[':title'] = "%{$kwd}%";
            }
            if (!empty($type)) {
                $sql .= " and type=:type";
                $params[':type'] = $type;
            }
            if (!empty($poolid)) {
                $sql .= " and poolid=:poolid";
                $params[':poolid'] = $poolid;
            }
            $ds = pdo_fetchall($sql, $params);
            include $this->template('question_query');
        } else if ($op == 'checkaddquestion') {
            $array = array();
            $array['type'] = $_GPC['type'];
            $array['paperid'] = $_GPC['paperid'];
            $data = $this->checkAddQuestion($array);

            die(json_encode($data));
        } else if ($op == 'addquestion') {
            $array = array();

            $id = $_GPC['id'];
            $idArr = $_GPC['idArr'];
            if (empty($id) && empty($idArr)) {
                message('参数错误', '', 'error');
            }

            $array['paperid'] = intval($_GPC['paperid']);
            $array['type'] = intval($_GPC['type']);

            if (empty($array['paperid']) || empty($array['type'])) {
                message('参数错误', '', 'error');
            }

            $data = $this->checkAddQuestion($array);

            //print_r($data);exit;

            if ($data['result'] == 1) {
                //pdo_update("ewei_exam_question",array("paperid" => $array['paperid']),array("id" => $id));
                //pdo_update("ewei_exam_paper_question",array("paperid" => $array['paperid']),array("questionid" => $id));
                if (!empty($id)) {
                    pdo_insert("ewei_exam_paper_question", array("paperid" => $array['paperid'], "questionid" => $id, "displayorder" => 0));
                }

                if (!empty($idArr)) {

                    $item = pdo_fetch("select tid from " . tablename('ewei_exam_paper') . " where id=:id limit 1", array(":id" => $array['paperid']));
                    $tid = $item['tid'];

                    $type_item = pdo_fetch("select * from " . tablename('ewei_exam_paper_type') . " where id=:id limit 1", array(':id' => $tid));
                    $types = unserialize($type_item['types']);


                    $question_array = array();
                    $question_array['id'] = $array['paperid'];
                    $question_array['types'] = $array['type'];
                    $d_question = $this->getDefaultPaperQuestion($question_array);
                    $now_question_data = $d_question['data'];

                    foreach ($types as $key => $value) {
                        if ($value['has'] == 1) {
                            $now_question_data[$key]['need'] = $value['num'] - $now_question_data[$key]['num'];
                        } else {
                            $now_question_data[$key]['need'] = 0;
                        }
                    }
                    $num = $now_question_data[$array['type']]['need'];

                    if ($num) {
                        $i = 0;
                        foreach ($idArr as $k => $arrid) {
                            if ($arrid == 'on') {
                                continue;
                            }
                            if($i >= $num) {
                                break;
                            }
                            pdo_insert("ewei_exam_paper_question", array("paperid" => $array['paperid'], "questionid" => $arrid, "displayorder" => 0));
                            $i++;
                        }
                    }
                }

                $this->check_paper_full($array['paperid']);
                if ($_W['isajax']) {
                    $this->message('试题已经添加到试卷中!', referer(), 0);
                } else {
                    message("试题已经添加到试卷中!", referer(), "success");
                }

            } else {
                if ($_W['isajax']) {
                    $this->message($data['msg']);
                } else {
                    message($data['msg'], '', "error");
                }
            }
        } else {
            $sql = "";
            $sql .= " FROM " . tablename('ewei_exam_question') . " q";
            $sql .= " LEFT JOIN " . tablename('ewei_exam_pool') . " p ON q.poolid = p.id";
            $sql .= " WHERE q.weid = '{$_W['weid']}'";

            $params = array();

            if (!empty($_GPC['poolid'])) {
                $sql .= ' AND q.poolid=:poolid';
                $params[':poolid'] = intval($_GPC['poolid']);
            }

            if (!empty($_GPC['type'])) {
                $sql .= ' AND q.type=:type';
                $params[':type'] = intval($_GPC['type']);
            }

            if (!empty($_GPC['question'])) {
                $sql .= ' AND q.question LIKE :question';
                $params[':question'] = "%{$_GPC['question']}%";
            }

            if ($_GPC['add_paper'] == 1 && !empty($_GPC['paperid'])) {
                $add_paper = 1;
                session_start();
                $url = $_SESSION['last_url'];
                $paperid = intval($_GPC['paperid']);
                $sql .= ' AND q.id NOT IN(SELECT questionid FROM ' . tablename('ewei_exam_paper_question') . 'WHERE paperid =:paperid)';
                $params[':paperid'] = $paperid;
            } else {
                $add_paper = 0;
            }

            $pindex = max(1, intval($_GPC['page']));
            $psize = 100;

            $select_sql = "SELECT q.*, p.title as pooltitle " . $sql . " ORDER BY q.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $count_sql = "SELECT COUNT(q.id) " . $sql;

            $list = pdo_fetchall($select_sql, $params);
            foreach ($list as &$row) {
                $row['type_name'] = $this->_types_config[$row['type']];
                $row['percent'] = round($row['correctnum'] / (empty($row['fansnum']) ? 1 : $row['fansnum']), 3) * 100;
            }
            unset($row);
            $total = pdo_fetchcolumn($count_sql, $params);
            $pager = pagination($total, $pindex, $psize);

            $poollist = pdo_fetchall("SELECT id, title from " . tablename('ewei_exam_pool') . " WHERE weid = :weid", array(':weid' => $this->_weid));
            //print_r($poollist);exit;

            include $this->template('question');
        }
    }

    //检查试卷是否可以添加该类型考题
    public function checkAddQuestion($array)
    {
        global $_GPC, $_W;

        $paperid = $array['paperid'];
        $type = $array['type'];

        if (!empty($paperid)) {
            $paper = pdo_fetch("select id,title from " . tablename('ewei_exam_paper') . " where id=:id limit 1", array(':id' => $paperid));
            $paper_info = $this->getPaperInfo($paperid);

            $d_question = $this->getDefaultPaperQuestion($paper_info);
            $now_question_data = $d_question['data'];

            $types = $paper_info['types'][$type];
            $msg = '';

            if ($types['has'] == 1) {
                if ($now_question_data[$type]['num'] < $types['num']) {
                    //正常可以添加
                    $result = 1;
                } else {
                    //试卷此题型已满
                    $result = 2;
                    $msg = "试卷该题型已满，不能再添加了！";
                }
            } else {
                //试卷不包含此题型
                $result = 3;
                $msg = "试卷不包含该题型，不能添加！";
            }
            $data = array();
            $data['result'] = $result;
            $data['msg'] = $msg;

            return $data;
        }
    }

    //检查试卷中是否已经存在该试题
    public function checkQuestion($paperid, $questionid)
    {
        global $_GPC, $_W;

        $params = array();
        $params[':paperid'] = $paperid;
        $params[':questionid'] = $questionid;

        $sql = "SELECT id FROM " . tablename('ewei_exam_paper_question') . " WHERE paperid = :paperid AND questionid = :questionid LIMIT 1";
        $item = pdo_fetchall($sql, $params);

        if ($item) {
            return 1;
        } else {
            return 0;
        }
    }

    //检查试卷中是否已经存在该试题
    public function addQuestion($paperid, $questionid, $check = 0)
    {
        global $_GPC, $_W;

        $insert = array();
        $insert['paperid'] = $paperid;
        $insert['questionid'] = $questionid;

        pdo_insert('ewei_exam_paper_question', $insert);
    }

    //检查试卷中是否已经存在该试题
    public function autoAddQuestion($paperid, &$list, $count, $num)
    {
//        if ($num == 0) {
//            return 1;
//        }

        $rand = rand(1, $count) - 1;
        $questionid = $list[$rand]['id'];
        $this->addQuestion($paperid, $questionid);
        return 1;

//        $check = $this->checkQuestion($paperid, $questionid);
//        if w($check) {
//            $num--;
//            $this->autoAddQuestion($paperid, $list, $count, $num);
//        } else {
//            $this->addQuestion($paperid, $questionid);
//            return 1;
//        }
    }

    //获取试卷和试卷类型的信息
    public function getPaperInfo($paper_id)
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];
        $sql = "SELECT p.id, p.title, p.level, p.year, t.score, t.types, t.times FROM " . tablename('ewei_exam_paper') . " AS p";
        $sql .= " LEFT JOIN " . tablename('ewei_exam_paper_type') . " AS t on p.tid = t.id";
        $sql .= " WHERE p.id = :id AND p.weid = :weid";
        $sql .= " LIMIT 1";

        $params = array();
        $params[':id'] = intval($paper_id);
        $params[':weid'] = $weid;

        $item = pdo_fetch($sql, $params);
        $item['types'] = $types = unserialize($item['types']);

        return $item;
    }

    //获取试卷中当前的考题和分数情况
    public function getDefaultPaperQuestion($array)
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];
        $score_array = array();
        $score = 0;
        $params = array();
        $params[':paperid'] = $array['id'];

        foreach ($this->_types_config as $key => $value) {
            $params[':type'] = $key;
            $count_sql = "SELECT COUNT(q.id) FROM " . tablename('ewei_exam_question') . " as q";
            $count_sql .= " LEFT JOIN " . tablename('ewei_exam_paper_question') . " as pq ON q.id = pq.questionid";
            $count_sql .= " WHERE pq.paperid = :paperid AND q.type = :type";
            $total = pdo_fetchcolumn($count_sql, $params);
            $score_array[$key]['num'] = $total;
            $score_array[$key]['score'] = $total * $array['types'][$key]['one_score'];
            $score += $score_array[$key]['score'];
        }

        $data = array();
        $data['data'] = $score_array;
        $data['score'] = $score;

        return $data;
    }

    public function doPool()
    {
        global $_GPC, $_W;

        $op = $_GPC['op'];
        $weid = $_W['weid'];

        if ($op == 'edit') {
            //编辑
            $id = intval($_GPC['id']);
            if (checksubmit()) {
                $insert = array(
                    'weid' => $weid,
                    'title' => $_GPC['title'],
                    'description' => $_GPC['description'],
                );
                if (empty($id)) {
                    pdo_insert('ewei_exam_pool', $insert);
                } else {
                    pdo_update('ewei_exam_pool', $insert, array('id' => $id));
                }
                message("题库信息保存成功!", $this->createWebUrl('pool'), "success");
            }
            $item = pdo_fetch("select * from " . tablename('ewei_exam_pool') . " where id=:id limit 1", array(":id" => $id));
            include $this->template('pool_form');
        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_update("ewei_exam_question", array("poolid" => 0), array("poolid" => $id));
            pdo_delete("ewei_exam_pool", array("id" => $id));
            message("题库信息删除成功!", referer(), "success");

        } else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                pdo_update("ewei_exam_question", array("poolid" => 0), array("poolid" => $id));
                pdo_delete("ewei_exam_pool", array("id" => $id));
            }
            $this->message('题库信息删除成功！', '', 0);
            exit();
        } else if ($op == 'query') {
            $kwd = trim($_GPC['keyword']);

            $sql = 'SELECT id,title FROM ' . tablename('ewei_exam_pool') . ' WHERE `weid`=:weid';
            $params = array();
            $params[':weid'] = $_W['weid'];
            if (!empty($kwd)) {
                $sql .= " AND `title` LIKE :title";
                $params[':title'] = "%{$kwd}%";
            }
            $ds = pdo_fetchall($sql, $params);
            include $this->template('pool_query');

        } else if ($op == 'addquestion') {
            //自动生成试题
            $data = array();
            $idArr = $_GPC['idArr'];
            if(empty($id) && empty($idArr)){
                $data['errno'] = '参数错误';
                die(json_encode($data));
            }

            $paperid = intval($_GPC['paperid']);
            //$array = array();
            //$array['type'] = intval($_GPC['type']);

            if (empty($paperid)) {
                $data['errno'] = '参数错误';
                die(json_encode($data));
            }

            //print_r($idArr);exit;

            $item = pdo_fetch("select tid from " . tablename('ewei_exam_paper') . " where id=:id limit 1", array(":id" => $paperid));
            $tid = $item['tid'];

            $type_item = pdo_fetch("select * from " . tablename('ewei_exam_paper_type') . " where id=:id limit 1", array(':id' => $tid));
            $types = unserialize($type_item['types']);

            $question_array = array();
            $question_array['id'] = $paperid;
            $question_array['types'] = $types;
            $d_question = $this->getDefaultPaperQuestion($question_array);
            $now_question_data = $d_question['data'];

            foreach ($types as $key => $value) {
                if ($value['has'] == 1) {
                    $now_question_data[$key]['need'] = $value['num'] - $now_question_data[$key]['num'];
                } else {
                    $now_question_data[$key]['need'] = 0;
                }
            }

            $id_count = 0;
            $in = "(";
            foreach ($idArr as $k => $arrid) {
                if ($arrid != 'on') {
                    $id_count++;
                    $in .= $arrid .",";
                }
            }
            $in = trim($in, ",");
            $in .= ")";

            if ($id_count == 0) {
                $data['errno'] = '请选择要从中填充试题的题库!';
                die(json_encode($data));
            }

            $params = array();
            $params[':weid'] = $this->_weid;

            $sql = " SELECT id FROM " . tablename('ewei_exam_question');
            $sql .= " WHERE weid = :weid";
            $sql .= " AND poolid IN " . $in;

            foreach ($now_question_data as $key => $value) {
                $need = $value['need'];

                if ($need > 0) {
                    $limit_num = $need + 300;

                    $question_sql = $sql;
                    $question_sql .= " AND type = " . $key;
                    $question_sql .= " AND id NOT IN (SELECT questionid FROM " . tablename('ewei_exam_paper_question') . " WHERE paperid = " . $paperid . ")";
                    $question_sql .= " limit 0," . $limit_num;

                    $list = pdo_fetchall($question_sql, $params);
                    $count = count($list);

                    if ($count == 0) {
                        continue;
                    }

                    if ($need < $count) {
                        for ($i = 0; $i < $need; $i++) {
                            $this->autoAddQuestion($paperid, $list, $count, $count * 2);
                        }

                    } else {
                        foreach ($list as $k => $v) {
                            $this->addQuestion($paperid, $v['id']);
//                            $check = $this->checkQuestion($paperid, $v['id']);
//                            if ($check) {
//                                continue;
//                            } else {
//                                $this->addQuestion($paperid, $v['id']);
//                            }
                        }
                    }
                }
            }

            $this->check_paper_full($paperid);

            session_start();
            $url = $_SESSION['last_url'];

            $data['errno'] = 0;
            $data['url'] = $url;
            die(json_encode($data));
           // message("试题添加成功!", $url, "success");
        } else {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND `title` LIKE :title';
                $params[':title'] = "%{$_GPC['title']}%";
            }
            $list = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_pool') . " WHERE weid = '{$_W['weid']}'  $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            foreach($list as &$r){
                //计算试题数
                $r['nums'] = pdo_fetchcolumn("select count(*) from ".tablename('ewei_exam_question')." WHERE weid = '{$_W['weid']}' and poolid=".$r['id']);
            }
            unset($r);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('ewei_exam_pool') . " WHERE weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);

            if ($_GPC['add_paper'] == 1 && !empty($_GPC['paperid'])) {
                $add_paper = 1;
                session_start();
                $url = $_SESSION['last_url'];
                $paperid = intval($_GPC['paperid']);

            } else {
                $add_paper = 0;
            }

            //print_r($add_paper);exit;

            include $this->template('pool');
        }
    }

    //根据试题id检查试卷试题是否完整
    function check_paper_full_by_questionid($questionid)
    {
        if (!empty($questionid)) {
            $sql = "SELECT paperid FROM " . tablename('ewei_exam_paper_question') . " WHERE questionid =" . $questionid;
            $list = pdo_fetchall($sql);
            foreach($list as $k => $v) {
                if (!empty($v['paperid'])) {
                    $this->check_paper_full($v['paperid']);
                }
            }
        }
    }

    //检查试卷试题是否完整
    function check_paper_full($paperid)
    {
        if (!empty($paperid)) {
            $paper_info = $this->getPaperInfo($paperid);
            $d_question = $this->getDefaultPaperQuestion($paper_info);
            $now_question_data = $d_question['data'];
            $flag = 1;
            foreach ($paper_info['types'] as $k => $v) {
                if ($v['has'] == 1) {
                    if ($now_question_data[$k]['num'] != $v['num']) {
                        $flag = 0;
                        break;
                    }
                }
            }
            pdo_update('ewei_exam_paper', array("isfull" => $flag), array('id' => $paperid));
            //print_r($pager_str);exit;
            //print_r($now_question_data);exit;
        }
    }

    public function doPaper_member()
    {
        global $_GPC, $_W;

        $weid = $_W['weid'];
        $paperid = intval($_GPC['id']);
        $username = $_GPC['username'];

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;

        $params = array();
        $params[':weid'] = $weid;
        $params[':paperid'] = $paperid;

        $select_sql = "SELECT r.*, m.username, m.mobile, p.title, p.avscore, p.avtimes";
        $count_sql = "SELECT COUNT(r.id)";

        $sql = " FROM " . tablename('ewei_exam_paper_member_record') . " AS r";
        $sql .= " LEFT JOIN  " . tablename('ewei_exam_member') . " as m ON r.memberid = m.id";
        $sql .= " LEFT JOIN  " . tablename('ewei_exam_paper') . " as p ON p.id = r.paperid";
        $sql .= " WHERE r.paperid = :paperid AND r.weid = :weid AND m.weid = :weid AND did = 1";

         if (!empty($username)) {
             $sql .= ' AND m.username LIKE :username';
            $params[':username'] = "%{$username}%";
         }

        $select_sql .= $sql;
        $count_sql .= $sql;
        $select_sql .= " ORDER BY r.score DESC, r.times ASC";

        if($pindex > 0) {
            // 需要分页
            $start = ($pindex - 1) * $psize;
            $select_sql .= " LIMIT {$start},{$psize}";
        }

        $list = pdo_fetchall($select_sql, $params);
        $total = pdo_fetchcolumn($count_sql, $params);

        $pager = pagination($total, $pindex, $psize);

        //print_r($list);exit;

        include $this->template('paper_member');

    }

    public function doPaper()
    {
        global $_GPC, $_W;

        $op = $_GPC['op'];
        $weid = $_W['weid'];
        $types_config = $this->_types_config;
        if ($op == 'edit') {
            //编辑
            $id = intval($_GPC['id']);
            $tid = intval($_GPC['tid']);
            $year_array = array();
            for ($i = date("Y"); $i >= 2000; $i--) {
                $year_array[] = $i;
            }

            if (checksubmit()) {
                $insert = array(
                    'weid' => $weid,
                    'displayorder' => $_GPC['displayorder'],
                    'title' => $_GPC['title'],
                    'level' => $_GPC['level'],
                    'year' => $_GPC['year'],
                    'tid' => $_GPC['tid'],
                    'description' => $_GPC['description'],
                    'status' => $_GPC['status'],
                    'pcate' => $_GPC['pcate'],
                );

                if (empty($id)) {
                    pdo_insert('ewei_exam_paper', $insert);
                } else {
                    pdo_update('ewei_exam_paper', $insert, array('id' => $id));
                }
                message("试卷信息保存成功!", $this->createWebUrl('paper'), "success");
            }
            if (!empty($id)) {
                $item = pdo_fetch("select * from " . tablename('ewei_exam_paper') . " where id=:id limit 1", array(":id" => $id));
                $tid = $item['tid'];
            }

            if(!empty($item)){
                $paper_category = pdo_fetch("select id, cname as title from ".tablename('ewei_exam_paper_category')." where id=:id limit 1",array(':id'=>$item['pcate']));
            }

//            if(!empty($item)){
//                $paper = pdo_fetch("select id,title from ".tablename('ewei_exam_paper')." where id=:id limit 1",array(':id'=>$item['paperid']));
//            }

            $type_item = pdo_fetch("select * from " . tablename('ewei_exam_paper_type') . " where id=:id limit 1", array(':id' => $tid));
            $types = unserialize($type_item['types']);

            if (!empty($id)) {
                $question_array = array();
                $question_array['id'] = $id;
                $question_array['types'] = $types;
                $d_question = $this->getDefaultPaperQuestion($question_array);
                $now_question_data = $d_question['data'];
            }
            //print_r($question_item);exit;
            include $this->template('paper_form');

        } else if ($op == 'editquestion') {
            session_start();
            $_SESSION['last_url'] = $_SERVER['REQUEST_URI'];

            //编辑
            $id = intval($_GPC['id']);
            $tid = intval($_GPC['tid']);

            if (checksubmit()) {
                //更改排序
                foreach ($_GPC['displayorder'] as $k => $v) {
                    if (empty($v)) {
                        $v = 0;
                    }
                    pdo_update('ewei_exam_paper_question', array('displayorder' => $v), array('paperid' => $id, 'questionid' => $k));
                }
            }

            if (!empty($id)) {
                $item = pdo_fetch("select * from " . tablename('ewei_exam_paper') . " where id=:id limit 1", array(":id" => $id));
                $tid = $item['tid'];
            }

            if(!empty($item)){
                $paper_category = pdo_fetch("select id, cname as title from ".tablename('ewei_exam_paper_category')." where id=:id limit 1",array(':id'=>$item['pcate']));
            }

            $type_item = pdo_fetch("select * from " . tablename('ewei_exam_paper_type') . " where id=:id limit 1", array(':id' => $tid));
            $types = unserialize($type_item['types']);

            if (!empty($id)) {
                $question_array = array();
                $question_array['id'] = $id;
                $question_array['types'] = $types;
                $d_question = $this->getDefaultPaperQuestion($question_array);
                $now_question_data = $d_question['data'];
            }

            $question_item = get_paper_question_list($id);
            //print_r($question_item);exit;

            include $this->template('paper_question_form');
        }else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete("ewei_exam_paper_question", array("paperid" => $id));
            //pdo_delete("ewei_exam_paper_member", array("questionid" => $id));
            //pdo_delete("ewei_exam_paper_member_data", array("questionid" => $id));
            pdo_delete("ewei_exam_paper", array("id" => $id));
            message("试题信息删除成功!", referer(), "success");

        } else if ($op == 'deleteall') {
            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);
                pdo_delete("ewei_exam_paper_question", array("paperid" => $id));
                //pdo_delete("ewei_exam_paper_member", array("questionid" => $id));
                //pdo_delete("ewei_exam_paper_member_data", array("questionid" => $id));
                pdo_delete("ewei_exam_paper", array("id" => $id));
            }
            $this->message('试题信息删除成功！', '', 0);
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
                    pdo_update('ewei_exam_paper', array('status' => $show_status), array('id' => $id));
                }
            }
            //message('操作成功！', '', 0);
            exit();
        } else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('ewei_exam_paper', array('status' => $_GPC['status']), array('id' => $id));
            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        } else if ($op == 'query') {
            $kwd = trim($_GPC['keyword']);

            $sql = "SELECT p.id, p.title, p.description,t.types, t.score FROM " . tablename('ewei_exam_paper') . " AS p";
            $sql .= " LEFT JOIN " . tablename('ewei_exam_paper_type') . " AS t on p.tid = t.id";
            $sql .= " WHERE p.weid = :weid";
            $params = array();
            $params[':weid'] = $_W['weid'];
            if (!empty($kwd)) {
                $sql .= " AND p.title LIKE :title";
                $params[':title'] = "%{$kwd}%";
            }
            $ds = pdo_fetchall($sql, $params);
            foreach ($ds as $key => $value) {
                $value['types'] = unserialize($value['types']);
                $d_question = $this->getDefaultPaperQuestion($value);
                $ds[$key]['now_score'] = $d_question['score'];
                $now_question_data = $d_question['data'];

                $pager_str = '该试卷包含 ';
                foreach ($value['types'] as $k => $v) {
                    if ($v['has'] == 1) {
                        $pager_str .= $types_config[$k] . "(" .$now_question_data[$k]['num'] . "/". $v['num'] .")道 ";
                    }
                }
                $ds[$key]['pager_str'] = $pager_str;
            }

            include $this->template('paper_query');

        } else {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $sql = "";
            $params = array();
            if (!empty($_GPC['title'])) {
                $sql .= ' AND p.title LIKE :keywords';
                $params[':keywords'] = "%{$_GPC['title']}%";
            }
            if (!empty($_GPC['level'])) {
                $sql .= ' AND p.level=:level';
                $params[':level'] = intva($_GPC['level']);
            }

            if (!empty($_GPC['pcate'])) {
                $pcate = intval($_GPC['pcate']);
                //判断是否为一级分类
                $cate_sql = "SELECT id FROM " .tablename('ewei_exam_paper_category');
                $cate_sql .=  " WHERE parentid = " . $pcate;
                $cate_sql .=  " AND weid = " . $weid;
                //$cate_sql .= " AND status = 1";

                $item = pdo_fetchall($cate_sql);
                $cate_num = count($item);

                if ($cate_num == 0) {
                    $sql .= " AND p.pcate = :pcate";
                    $params[':pcate'] = $pcate;
                } else if ($cate_num > 0) {
                    $item[$cate_num]['id'] = $pcate;
                    $cate_str = '';
                    foreach ($item as $k => $v) {
                        $cate_str .= $v['id'] . ",";
                    }
                    $cate_str = trim($cate_str, ",");
                    $sql .= " AND p.pcate in (" . $cate_str . ")";
                }
            }

            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;

            $select_sql = "SELECT p.*, t.score, t.times FROM " . tablename('ewei_exam_paper') . " as p";
            $select_sql .= " LEFT JOIN " . tablename('ewei_exam_paper_type') . " as t on p.tid = t.id";
            $select_sql .= " WHERE p.weid = '{$_W['weid']}'  $sql ORDER BY displayorder DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize;
            $list = pdo_fetchall($select_sql, $params);

            $count_sql = "SELECT COUNT(p.id) FROM " . tablename('ewei_exam_paper') . " as p";
            $count_sql .= " LEFT JOIN " . tablename('ewei_exam_paper_type') . " as t on p.tid = t.id";
            $count_sql .= " WHERE p.weid = '{$_W['weid']}'" . $sql;

            $category = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_paper_category') . " WHERE weid = '{$_W['weid']}' AND status = 1 ORDER BY parentid ASC, displayorder DESC");
            //print_r($category);exit;

            $total = pdo_fetchcolumn($count_sql, $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('paper');
        }
    }


    public function doPaper_type()
    {
        global $_GPC, $_W;
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $types_config = $this->_types_config;
        if ($operation == 'display') {
            $types = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_paper_type') . " WHERE weid = '{$_W['weid']}' ORDER BY id desc");
            foreach ($types as $key => $value) {
                $types[$key]['types'] = unserialize($types[$key]['types']);
            }

            //print_r($types);exit;

            include $this->template('paper_type');

        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (checksubmit('submit')) {
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入分类名称！');
                }

                //print_r($_GPC);exit;

                if (empty($_GPC['has'])) {
                    message('抱歉，请至少选择一种类型！');
                } else {
                    $has = $_GPC['has'];
                }

                $num = $_GPC['num'];
                $one_score = $_GPC['one_score'];

                $array = array();
                foreach ($types_config as $key => $value) {
                    if (array_key_exists($key, $has)) {
                        $array[$key]['has'] = 1;
                        $array[$key]['num'] = $num[$key];
                        $array[$key]['one_score'] = $one_score[$key];
                        $array[$key]['sum_score'] = $num[$key] * $one_score[$key];
                    } else {
                        $array[$key]['has'] = 0;
                        $array[$key]['num'] = 0;
                        $array[$key]['one_score'] = 0;
                        $array[$key]['sum_score'] = 0;
                    }
                }

                $data = array(
                    'weid' => $_W['weid'],
                    'title' => $_GPC['title'],
                    'times' => $_GPC['times'],
                    'score' => $_GPC['score'],
                    'types' => serialize($array),
                );

                //print_r($data);exit;

                if (!empty($id)) {
                    pdo_update('ewei_exam_paper_type', $data, array('id' => $id));
                } else {
                    pdo_insert('ewei_exam_paper_type', $data);
                    $id = pdo_insertid();
                }
                message('更新类型成功！', $this->createWebUrl('paper_type', array('op' => 'display')), 'success');
            }
            $item = pdo_fetch("select * from " . tablename('ewei_exam_paper_type') . " where id=:id limit 1", array(":id" => $id));

            $arr = array();
            if (!empty($item)) {
                $arr = unserialize($item['types']);
            }
            if (count($arr) <= 0) {
                foreach ($types_config as $key => $value) {
                    $arr[$key]['has'] = 0;
                    $arr[$key]['num'] = 0;
                    $arr[$key]['one_score'] = 0;
                    $arr[$key]['sum_score'] = 0;

//                    $arr[] =array(
//                        "key"=>$key,
//                        "value"=>$value,
//                        "num"=>0,
//                        "score"=>0
//                    );
                }
            }

            include $this->template('paper_type');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);

            if (!empty($id)) {
                $item = pdo_fetch("SELECT id FROM " . tablename('ewei_exam_paper') . " WHERE tid = :tid LIMIT 1", array(':tid' => $id));
                if (!empty($item)) {
                    message('抱歉，请先删除该类型下的试卷,再删除该类型！', '', 'error');
                }
            } else{
                message('抱歉，参数错误！', '', 'error');
            }

            pdo_delete('ewei_exam_paper_type', array('id' => $id));
            message('类型删除成功！', $this->createWebUrl('paper_type', array('op' => 'display')), 'success');
        }
    }

    public function doMember() {
        global $_GPC, $_W;
        $op = $_GPC['op'];
        if ($op == 'edit') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename('ewei_exam_member') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，用户不存在或是已经删除！', '', 'error');
                }
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['username'])) {
                    message('抱歉，姓名不能为空！', '', 'error');
                }

//                if (empty($_GPC['userid'])) {
//                    message('抱歉，用户名不能为空！', '', 'error');
//                }

                $data = array(
                    'weid' => $_W['weid'],
                    'username' => $_GPC['username'],
                    'mobile' => $_GPC['mobile'],
                    'email'=>$_GPC['email'],
                    'status'=>$_GPC['status'],
                );

                if (!empty($_GPC['userid'])) {
                    $data['userid'] = $_GPC['userid'];
                }

                $check_flag = check_userid($data, $id);
                if ($check_flag) {
                    message('抱歉，用户名已经存在！', '', 'error');
                }
                //print_r($check_flag);exit;

                if (empty($id)) {
                    $data['createtime'] = time();
                    pdo_insert('ewei_exam_member', $data);
                } else {
                    unset($data['weid']);
                    pdo_update('ewei_exam_member', $data, array('id' => $id));
                }
                message('用户信息更新成功！', $this->createWebUrl('member'), 'success');
            }
            include $this->template('member_form');

        } else if ($op == 'delete') {
            $id = intval($_GPC['id']);
            pdo_delete('ewei_exam_member', array('id' => $id));

            message('删除成功！', referer(), 'success');

        }  else if ($op == 'deleteall') {

            foreach ($_GPC['idArr'] as $k => $id) {

                $id = intval($id);
                pdo_delete('ewei_exam_member', array('id' => $id));

            }
            //message('规则操作成功！', '', 0);
            //exit();
        } else if ($op == 'showall') {
            if ($_GPC['show_name'] == 'showall') {
                $show_status = 1;
            } else {
                $show_status = 0;
            }

            foreach ($_GPC['idArr'] as $k => $id) {
                $id = intval($id);

                if (!empty($id)) {
                    pdo_update('ewei_exam_member', array('status' => $show_status), array('id' => $id));
                }
            }
            //message('操作成功！', '', 0);
            //exit();
        } else if ($op == 'status') {

            $id = intval($_GPC['id']);
            if (empty($id)) {
                message('抱歉，传递的参数错误！', '', 'error');
            }
            $temp = pdo_update('ewei_exam_member', array('status' => $_GPC['status']), array('id' => $id));

            if ($temp == false) {
                message('抱歉，刚才操作数据失败！', '', 'error');
            } else {
                message('状态设置成功！', referer(), 'success');
            }
        } else {
            $sql = "";
            $params = array();
            if (!empty($_GPC['username'])) {
                $sql .= ' AND `username` LIKE :username';
                $params[':username'] = "%{$_GPC['username']}%";
            }
            if (!empty($_GPC['userid'])) {
                $sql .= ' AND `userid` LIKE :userid';
                $params[':userid'] = "%{$_GPC['userid']}%";
            }
            if (!empty($_GPC['mobile'])) {
                $sql .= ' AND `mobile` LIKE :mobile';
                $params[':mobile'] = "%{$_GPC['mobile']}%";
            }
            $pindex = max(1, intval($_GPC['page']));
            $psize = 100;
            $list = pdo_fetchall("SELECT * FROM " . tablename('ewei_exam_member') . " WHERE weid = '{$_W['weid']}' $sql ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize, $params);
            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('ewei_exam_member') . " WHERE weid = '{$_W['weid']}' $sql", $params);
            $pager = pagination($total, $pindex, $psize);
            include $this->template('member');
        }
    }


    public function doUploadExcel()
    {
        global $_GPC, $_W;

        
        if($_GPC['leadExcel'] == "true")
        {
            $filename = $_FILES['inputExcel']['name'];
            $tmp_name = $_FILES['inputExcel']['tmp_name'];
         
            //print_r($_FILES);exit;

            $msg = uploadFile($filename, $tmp_name, $_GPC);

            //print_r($msg);exit;
            if ($msg == 1) {
                message('导入成功！', referer(), 'success');
            } else {
                message($msg, '', 'error');
            }
        }
    }

    public function doUpload_question()
    {
        global $_GPC, $_W;
        $poollist = pdo_fetchall("SELECT id, title from " . tablename('ewei_exam_pool') ." WHERE weid = :weid", array(':weid' => $this->_weid));

        include $this->template('upload_question');
    }

    public function doSysset()
    {
        global $_GPC, $_W;

        $id = intval($_GPC['id']);

        if (checksubmit('submit')) {
            $data = array();
            $data['weid'] = $this->_weid;
            $data['about'] = htmlspecialchars_decode($_GPC['about']);
            $data['classopen'] =intval($_GPC['classopen']);
            $data['login_flag'] =intval($_GPC['login_flag']);

            if (!empty($id)) {
                pdo_update("ewei_exam_sysset", $data, array("id" => $id));
            } else {
                pdo_insert("ewei_exam_sysset", $data);
            }
            message("保存设置成功!", referer(), "success");
        }

        $item = pdo_fetch("select * from " . tablename('ewei_exam_sysset') . " where weid=:weid limit 1", array(":weid" => $_W['weid']));
        include $this->template('sysset');
    }

//    public function settingsDisplay($settings) {
//        global $_GPC, $_W;
//
//        if(checksubmit()) {
//            $cfg = array(
//                'exam_about' => htmlspecialchars_decode($_GPC['exam_about']),
//            );
//            if($this->saveSettings($cfg)) {
//                message('保存成功', 'refresh');
//            }
//        }
//        include $this->template('setting');
//    }

    
	 public function message($error,$url='',$errno=-1){
		$data=array();
		$data['errno']=$errno;
		if(!empty($url)){
			$data['url']=$url;		
		}
		$data['error']=$error;		
		echo json_encode($data);
		exit;
	 }
}

