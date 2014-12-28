<?php
/**
 * 微点餐
 *
 * 作者:迷失卍国度
 *
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');

class IdishModule extends WeModule
{
    public $name = 'IdishModule';
    public $title = '微点餐';
    public $ability = '';
    public $tablename = 'idish_reply';
    public $action = 'detail'; //方法
    public $modulename = 'idish'; //模块标识
    public $actions_titles = array(
        'stores' => '返回列表',
        'order' => '订单管理',
        'category' => '类别管理',
        'goods' => '菜品管理',
        'intelligent' => '智能选菜',
        'smssetting' => '短信设置',
        'emailsetting' => '邮件设置',
        'printsetting' => '打印机设置'
    );

    public function fieldsFormDisplay($rid = 0)
    {
        global $_W;
        $stores = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $_W['weid']));
        if (empty($stores)) {
            $url = create_url('site/module', array('do' => 'storesform', 'name' => 'idish'));
            message('请先添加门店', $url);
        }
        if (!empty($rid)) {
            //回复信息
            $sql_reply = "SELECT * FROM " . tablename($this->tablename) . " WHERE rid = :rid ORDER BY `id` DESC";
            $reply = pdo_fetch($sql_reply, array(':rid' => $rid));
        }
        include $this->template('idish/form');
    }

    public function fieldsFormSubmit($rid = 0)
    {
        global $_GPC, $_W;
        $id = intval($_GPC['reply_id']);
        $weid = intval($_W['weid']);
        $data = array(
            'rid' => $rid,
            'weid' => $weid,
            'title' => trim($_GPC['title']),
            'type' => intval($_GPC['type']),
            'storeid' => intval($_GPC['store']),
            'description' => trim($_GPC['description']),
            'picture' => trim($_GPC['picture']),
            'dateline' => TIMESTAMP
        );
        if (strlen($data['title']) > 100) {
            message('活动名称必须小于100个字符！');
        }
        if (strlen($data['title']) == 0) {
            message('请输入名称！');
        }
        if (strlen($data['description']) > 1000) {
            message('活动简介必须小于1000个字符！');
        }
        if (strlen($data['description']) == 0) {
            message('请输入活动简介！');
        }
        if (empty($id)) {
            pdo_insert($this->tablename, $data);
        } else {
            if (!empty($_GPC['picture'])) { //封面
                file_delete($_GPC['picture_old']);
            } else {
                unset($data['picture']);
            }
            pdo_update($this->tablename, $data, array('id' => $id));
        }
        $url = create_url('site/module', array('do' => 'detail', 'name' => $this->modulename, 'rid' => $rid));
        if (empty($id)) {
            message('添加成功！', '', 'success');
        } else {
            message('编辑成功！', '', 'success');
        }
    }

    public function doNave()
    {
        global $_W, $_GPC;
        checklogin();

        $action = 'nave';
        $title = '导航管理'; //$title = $this->actions_titles[$action];

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update($this->modulename . '_nave', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('排序更新成功！', $this->createWebUrl('nave', array('op' => 'display')), 'success');
            }
            $children = array();
            $nave = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_nave') . " WHERE weid = '{$_W['weid']}' ORDER BY displayorder DESC");
            include $this->template('site_nave');
        } elseif ($operation == 'post') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $nave = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_nave') . " WHERE id = '$id'");
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['linkname'])) {
                    message('抱歉，请输入导航名称！');
                }

                $data = array(
                    'weid' => $_W['weid'],
                    'type' => intval($_GPC['type']),
                    'name' => trim($_GPC['linkname']),
                    'link' => trim($_GPC['link']),
                    'status' => intval($_GPC['status']),
                    'displayorder' => intval($_GPC['displayorder']),
                );

                if (!empty($id)) {
                    pdo_update($this->modulename . '_nave', $data, array('id' => $id));
                } else {
                    pdo_insert($this->modulename . '_nave', $data);
                    $id = pdo_insertid();
                }
                message('更新成功！', $this->createWebUrl('nave', array('op' => 'display')), 'success');
            }
            include $this->template('site_nave');
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $nave = pdo_fetch("SELECT id FROM " . tablename($this->modulename . '_nave') . " WHERE id = '$id'");
            if (empty($nave)) {
                message('抱歉，不存在或是已经被删除！', $this->createWebUrl('nave', array('op' => 'display')), 'error');
            }
            pdo_delete($this->modulename . '_nave', array('id' => $id));
            message('删除成功！', $this->createWebUrl('nave', array('op' => 'display')), 'success');
        }
    }

    public function doSmsSetting()
    {
        global $_GPC, $_W;
        checklogin();
        $action = 'smssetting';
        $title = $this->actions_titles[$action];
        $storeid = intval($_GPC['storeid']);

        $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE weid = :weid AND id=:storeid ORDER BY `id` DESC", array(':weid' => $_W['weid'], ':storeid' => $storeid));
        if (empty($store)) {
            message('非法操作.');
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_sms_setting') . " WHERE weid = :weid AND storeid=:storeid", array(':weid' => $_W['weid'], ':storeid' => $storeid));
        if (checksubmit('submit')) {
            $data = array(
                'weid' => $_W['weid'],
                'storeid' => $storeid,
                'sms_enable' => intval($_GPC['sms_enable']),
                'sms_username' => trim($_GPC['sms_username']),
                'sms_pwd' => trim($_GPC['sms_pwd']),
                'sms_verify_enable' => intval($_GPC['sms_verify_enable']),
                'sms_mobile' => trim($_GPC['sms_mobile']),
                'sms_business_tpl' => trim($_GPC['sms_business_tpl']),
                'dateline' => TIMESTAMP
            );

            if (empty($setting)) {
                pdo_insert($this->modulename . '_sms_setting', $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->modulename . '_sms_setting', $data, array('weid' => $_W['weid'], 'storeid' => $storeid));
            }
            message('操作成功', $this->createWebUrl('smssetting', array('storeid' => $storeid)), 'success');
        }
        include $this->template('sms_setting');
    }

    public function doEmailSetting()
    {
        global $_GPC, $_W;
        checklogin();
        $action = 'emailsetting';
        $title = $this->actions_titles[$action];
        $storeid = intval($_GPC['storeid']);

        $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE weid = :weid AND id=:storeid ORDER BY `id` DESC", array(':weid' => $_W['weid'], ':storeid' => $storeid));
        if (empty($store)) {
            message('非法操作.');
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_email_setting') . " WHERE weid = :weid AND storeid=:storeid", array(':weid' => $_W['weid'], ':storeid' => $storeid));

        if (checksubmit('submit')) {
            if (empty($_GPC['email_send'])||empty($_GPC['email_user'])||empty($_GPC['email_pwd'])) {
                message('请完整填写邮件配置信息', 'refresh', 'error');
            }
            if( $_GPC['email_host'] == 'smtp.qq.com' || $_GPC['email_host'] == 'smtp.gmail.com' ){
                $secure = 'ssl';
                $port = '465';
            } else {
                $secure = 'tls';
                $port = '25';
            }
            //$result = $this->sendmail($_GPC['email_host'], $secure, $port, $_GPC['email_send'], $_GPC['email_user'], $_GPC['email_pwd'], $_GPC['email_send']);
            //public function sendmail($cfghost,$cfgsecure,$cfgport,$cfgsendmail,$cfgsenduser,$cfgsendpwd,$mailaddress) {

            $mail_config = array();
            $mail_config['host'] = $_GPC['email_host'];
            $mail_config['secure'] = $secure;
            $mail_config['port'] = $port;
            $mail_config['username'] = $_GPC['email_user'];
            $mail_config['sendmail'] = $_GPC['email_send'];
            $mail_config['password'] = $_GPC['email_pwd'];
            $mail_config['mailaddress'] = $_GPC['email'];
            $mail_config['subject'] = '微点餐提醒';
            $mail_config['body'] = '邮箱测试';

            $result = $this->sendmail($mail_config);
            $data = array(
                'weid' => $_W['weid'],
                'storeid' => $storeid,
                'email_enable' => intval($_GPC['email_enable']),
                'email_host' => $_GPC['email_host'],
                'email_send' => $_GPC['email_send'],
                'email_pwd' => $_GPC['email_pwd'],
                'email_user' => $_GPC['email_user'],
                'email' => trim($_GPC['email']),
                'email_business_tpl' => trim($_GPC['email_business_tpl']),
                'dateline' => TIMESTAMP
            );
            if ($result == 1) {
                if (empty($setting)) {
                    pdo_insert($this->modulename . '_email_setting', $data);
                } else {
                    unset($data['dateline']);
                    pdo_update($this->modulename . '_email_setting', $data, array('weid' => $_W['weid'], 'storeid' => $storeid));
                }
                message('邮箱配置成功', 'refresh');
            } else {
                message('邮箱配置信息有误', 'refresh', 'error');
            }


            message('操作成功', $this->createWebUrl('emailsetting', array('storeid' => $storeid)), 'success');
        }
        include $this->template('email_setting');
    }

    public function sendmail($config) {
        include 'plugin/email/class.phpmailer.php';
        $mail             = new PHPMailer();
        $mail->CharSet    = "utf-8";
        $body             = $config['body'];
        $mail->IsSMTP();
        $mail->SMTPAuth   = true;                      // enable SMTP authentication
        $mail->SMTPSecure = $config['secure'];         // sets the prefix to the servier
        $mail->Host       = $config['host'];           // sets the SMTP server
        $mail->Port       = $config['port'];
        $mail->Username   = $config['sendmail'];       // 发件邮箱用户名
        $mail->Password   = $config['password'];       // 发件邮箱密码
        $mail->From       = $config['sendmail'];	   //发件邮箱
        $mail->FromName   = $config['username'];       //发件人名称
        $mail->Subject    = $config['subject']; //主题
        $mail->WordWrap   = 50; // set word wrap
        $mail->MsgHTML($body);
        $mail->AddAddress($config['mailaddress'],'');  //收件人地址、名称
        $mail->IsHTML(true); // send as HTML
        if(!$mail->Send()) {
            $status = 0;
        } else {
            $status = 1;
        }
        return $status;
    }

    //打印机设置
    public function doPrintSetting()
    {
        global $_GPC, $_W;
        checklogin();
        $action = 'printsetting';
        $title = $this->actions_titles[$action];
        $storeid = intval($_GPC['storeid']);

        $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE weid = :weid AND id=:storeid ORDER BY `id` DESC", array(':weid' => $_W['weid'], ':storeid' => $storeid));
        if (empty($store)) {
            message('非法操作！门店不存在.');
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_print_setting') . " WHERE weid = :weid AND storeid=:storeid", array(':weid' => $_W['weid'], ':storeid' => $storeid));
        if (checksubmit('submit')) {
            $data = array(
                'weid' => $_W['weid'],
                'storeid' => $storeid,
                'weid' => $_W['weid'],
                'print_status' => trim($_GPC['print_status']),
                'print_type' => trim($_GPC['print_type']),
                'print_usr' => trim($_GPC['print_usr']),
                'print_nums' => trim($_GPC['print_nums']),
                'print_top' => trim($_GPC['print_top']),
                'print_bottom' => trim($_GPC['print_bottom']),
                'dateline' => TIMESTAMP
            );
            if (empty($setting)) {
                pdo_insert($this->modulename . '_print_setting', $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->modulename . '_print_setting', $data, array('weid' => $_W['weid'], 'storeid' => $storeid));
            }
            message('操作成功', $this->createWebUrl('printsetting', array('storeid' => $storeid)), 'success');
        }
        include $this->template('print_setting');
    }

    //网站配置
    public function doSetting()
    {
        global $_W, $_GPC;
        checklogin();
        $action = 'setting';
        $title = '网站设置'; //$title = $this->actions_titles[$action];
        $stores = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE weid = :weid ORDER BY `id` DESC", array(':weid' => $_W['weid']));
        if (empty($stores)) {
            $url = create_url('site/module', array('do' => 'storesform', 'name' => 'idish'));
            message('请先添加门店', $url);
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_setting') . " WHERE weid = :weid", array(':weid' => $_W['weid']));
        if (checksubmit('submit')) {
            $data = array(
                'weid' => $_W['weid'],
                'title' => trim($_GPC['title']),
                'entrance_type' => intval($_GPC['entrance_type']),
                'entrance_storeid' => intval($_GPC['entrance_storeid']),
                'order_enable' => intval($_GPC['order_enable']),
                'dining_mode' => intval($_GPC['dining_mode']),
                'dateline' => TIMESTAMP
            );
            if (!empty($_FILES['thumb']['tmp_name'])) {
                file_delete($_GPC['thumb_old']);
                $upload = file_upload($_FILES['thumb']);
                if (is_error($upload)) {
                    message($upload['message'], '', 'error');
                }
                $data['thumb'] = $upload['path'];
            }
            if (empty($setting)) {
                pdo_insert($this->modulename . '_setting', $data);
            } else {
                unset($data['dateline']);
                pdo_update($this->modulename . '_setting', $data, array('weid' => $_W['weid']));
            }
            message('操作成功', $this->createWebUrl('setting'), 'success');
        }
        include $this->template('setting');
    }

    //门店添加
    public function doStoresForm()
    {
        global $_GPC, $_W;
        checklogin();
        $action = 'stores';
        $title = $this->actions_titles[$action];
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));
        $id = intval($_GPC['id']); //门店编号

        $reply = pdo_fetch("select * from " . tablename($this->modulename . '_stores') . " where id=:id and weid =:weid", array(':id' => $id, ':weid' => $_W['weid']));
        if (!empty($id)) {
            if (empty($reply)) {
                message('抱歉，数据不存在或是已经删除！', '', 'error');
            } else {
                if (!empty($reply['thumb_url'])) {
                    $reply['thumbArr'] = explode('|', $reply['thumb_url']);
                }
            }
        }

        if (checksubmit('submit')) {
            $data = array();
            $data['weid'] = intval($_W['weid']);
            $data['title'] = trim($_GPC['title']);
            $data['info'] = trim($_GPC['info']);
            $data['content'] = trim($_GPC['content']);
            $data['tel'] = trim($_GPC['tel']);
            $data['address'] = trim($_GPC['address']);
            $data['location_p'] = trim($_GPC['location_p']);
            $data['location_c'] = trim($_GPC['location_c']);
            $data['location_a'] = trim($_GPC['location_a']);
            $data['password'] = trim($_GPC['password']);
            $data['recharging_password'] = trim($_GPC['recharging_password']);
            $data['is_show'] = intval($_GPC['is_show']);
            $data['place'] = trim($_GPC['place']);
            $data['hours'] = trim($_GPC['hours']);
            $data['lng'] = trim($_GPC['lng']);
            $data['lat'] = trim($_GPC['lat']);
            $data['enable_wifi'] = intval($_GPC['enable_wifi']);
            $data['enable_card'] = intval($_GPC['enable_card']);
            $data['enable_room'] = intval($_GPC['enable_room']);
            $data['enable_park'] = intval($_GPC['enable_park']);
            $data['thumb_url'] = implode('|', $_GPC['thumb_url']);
            $data['updatetime'] = TIMESTAMP;
            $data['dateline'] = TIMESTAMP;

            if (istrlen($data['title']) == 0) {
                message('没有输入标题.', '', 'error');
            }
            if (istrlen($data['title']) > 30) {
                message('标题不能多于30个字。', '', 'error');
            }
            if (istrlen($data['content']) == 0) {
                message('没有输入内容.', '', 'error');
            }
            if (istrlen(trim($data['content'])) > 1000) {
                message('内容过多请重新输入.', '', 'error');
            }
            if (istrlen($data['tel']) == 0) {
                message('没有输入联系电话.', '', 'error');
            }
            if (istrlen($data['address']) == 0) {
                message('请输入地址。', '', 'error');
            }
//            if (istrlen($data['password']) == 0) {
//                message('没有输入确认密码.','','error');
//            }
//            if (istrlen($data['password']) > 16) {
//                message('确认密码不能大于16个字符.','','error');
//            }
//            if (istrlen($data['recharging_password']) == 0) {
//                message('没有输入充值密码.','','error');
//            }
//            if (istrlen($data['recharging_password']) > 16) {
//                message('充值密码不能大于16个字符.','','error');
//            }

            if (!empty($_FILES['logo']['tmp_name'])) {
                file_delete($_GPC['logo_old']);
                $upload = file_upload($_FILES['logo']);
                if (is_error($upload)) {
                    message($upload['message'], '', 'error');
                }
                $data['logo'] = $upload['path'];
            }

            if (!empty($reply)) {
                unset($data['dateline']);
                pdo_update($this->modulename . '_stores', $data, array('id' => $id, 'weid' => $_W['weid']));
            } else {
                pdo_insert($this->modulename . '_stores', $data);
            }
            message('操作成功!', $url);
        }
        include $this->template('stores_form');
    }

    public function doStoresDelete()
    {
        global $_W, $_GPC;
        checklogin();
        $id = intval($_GPC['id']);
        $store = pdo_fetch("SELECT id FROM " . tablename($this->modulename . '_stores') . " WHERE id = '$id'");
        if (empty($store)) {
            message('抱歉，不存在或是已经被删除！', $this->createWebUrl('$stores', array('op' => 'display')), 'error');
        }
        pdo_delete($this->modulename . '_stores', array('id' => $id, 'weid' => $_W['weid']));
        message('删除成功！', $this->createWebUrl('stores', array('op' => 'display')), 'success');
    }

    //门店管理
    public function doStores()
    {
        global $_W, $_GPC;
        checklogin();
        $action = 'stores';
        //$title = $this->actions_titles[$action];
        $title = '门店管理';
        $url = create_url('site/module', array('do' => $action, 'name' => $this->modulename));

        if (checksubmit('submit')) { //排序
            if (is_array($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $val) {
                    $data = array('displayorder' => intval($_GPC['displayorder'][$id]));
                    pdo_update($this->modulename . '_stores', $data, array('id' => $id));
                }
            }
            message('操作成功!', $url);
        }
        $pindex = max(1, intval($_GPC['page']));
        $psize = 15;
        $where = "WHERE weid = '{$_W['weid']}'";
        $storeslist = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_stores') . " {$where} order by displayorder desc,id desc LIMIT " . ($pindex - 1) * $psize . ",{$psize}");
        if (!empty($gifts)) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename($this->modulename . '_stores') . " $where");
            $pager = pagination($total, $pindex, $psize);
        }
        include $this->template('stores');
    }

    public function doCategory()
    {
        global $_GPC, $_W;
        checklogin();
        $action = 'category';
        $title = $this->actions_titles[$action];
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        $storeid = intval($_GPC['storeid']);

        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update($this->modulename . '_category', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('category', array('op' => 'display', 'storeid' => $storeid)), 'success');
            }
            $children = array();
            $category = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_category') . " WHERE weid = '{$_W['weid']}'  AND storeid ={$storeid} ORDER BY parentid ASC, displayorder DESC");
            foreach ($category as $index => $row) {
                if (!empty($row['parentid'])) {
                    $children[$row['parentid']][] = $row;
                    unset($category[$index]);
                }
            }
            include $this->template('category');
        } elseif ($operation == 'post') {
            $parentid = intval($_GPC['parentid']);
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $category = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_category') . " WHERE id = '$id'");
            } else {
                $category = array(
                    'displayorder' => 0,
                );
            }

            if (!empty($parentid)) {
                $parent = pdo_fetch("SELECT id, name FROM " . tablename($this->modulename . '_category') . " WHERE id = '$parentid'");
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
                    'storeid' => $_GPC['storeid'],
                    'name' => $_GPC['catename'],
                    'displayorder' => intval($_GPC['displayorder']),
                    'parentid' => intval($parentid),
                );

                if (empty($data['storeid'])) {
                    message('非法参数');
                }

                if (!empty($id)) {
                    unset($data['parentid']);
                    pdo_update($this->modulename . '_category', $data, array('id' => $id));
                } else {
                    pdo_insert($this->modulename . '_category', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('category', array('op' => 'display', 'storeid' => $storeid)), 'success');
            }
            include $this->template('category');

        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id, parentid FROM " . tablename($this->modulename . '_category') . " WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category', array('op' => 'display', 'storeid' => $storeid)), 'error');
            }
            pdo_delete($this->modulename . '_category', array('id' => $id, 'parentid' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('category', array('op' => 'display', 'storeid' => $storeid)), 'success');
        }
    }

    //菜品
    public function doGoods()
    {
        global $_GPC, $_W;
        checklogin();
        $action = 'goods';
        $title = $this->actions_titles[$action];
        $storeid = intval($_GPC['storeid']);

        $category = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_category') . " WHERE weid = '{$_W['weid']}' And storeid={$storeid} ORDER BY parentid ASC, displayorder DESC", array(), 'id');
        if (!empty($category)) {
            $children = '';
            foreach ($category as $cid => $cate) {
                if (!empty($cate['parentid'])) {
                    $children[$cate['parentid']][$cate['id']] = array($cate['id'], $cate['name']);
                }
            }
        }

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'post') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $item = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE id = :id", array(':id' => $id));
                if (empty($item)) {
                    message('抱歉，菜品不存在或是已经删除！', '', 'error');
                } else {
                    if (!empty($item['thumb_url'])) {
                        $item['thumbArr'] = explode('|', $item['thumb_url']);
                    }
                }
            }
            if (checksubmit('submit')) {
                $data = array(
                    'weid' => intval($_W['weid']),
                    'storeid' => intval($_GPC['storeid']),
                    'displayorder' => intval($_GPC['displayorder']),
                    'title' => trim($_GPC['goodsname']),
                    'pcate' => intval($_GPC['pcate']),
                    'ccate' => intval($_GPC['ccate']),
                    'status' => intval($_GPC['status']),
                    'recommend' => intval($_GPC['recommend']),
                    'unitname' => trim($_GPC['unitname']),
                    'description' => trim($_GPC['description']),
                    'taste' => trim($_GPC['taste']),
                    'isspecial' => intval($_GPC['isspecial']),
                    'marketprice' => trim($_GPC['marketprice']),
                    'productprice' => trim($_GPC['productprice']),
                    'subcount' => intval($_GPC['subcount']),
                    'createtime' => TIMESTAMP,
                );

                if (empty($data['title'])) {
                    message('请输入菜品名称！');
                }
                if (empty($data['pcate'])) {
                    message('请选择菜品分类！');
                }
                if (empty($data['storeid'])) {
                    message('非法参数');
                }

                if (!empty($_FILES['thumb']['tmp_name'])) {
                    file_delete($_GPC['thumb_old']);
                    $upload = file_upload($_FILES['thumb']);
                    if (is_error($upload)) {
                        message($upload['message'], '', 'error');
                    }
                    $data['thumb'] = $upload['path'];
                }
                if (empty($id)) {
                    pdo_insert($this->modulename . '_goods', $data);
                } else {
                    unset($data['createtime']);
                    pdo_update($this->modulename . '_goods', $data, array('id' => $id));
                }
                message('菜品更新成功！', $this->createWebUrl('goods', array('op' => 'display', 'storeid' => $storeid)), 'success');
            }
        } elseif ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update($this->modulename . '_goods', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('排序更新成功！', $this->createWebUrl('goods', array('op' => 'display', 'storeid' => $storeid)), 'success');
            }

            $pindex = max(1, intval($_GPC['page']));
            $psize = 10;
            $condition = '';
            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
            }

            if (!empty($_GPC['category_id'])) {
                $cid = intval($_GPC['category_id']);
                $condition .= " AND pcate = '{$cid}'";
            }

            if (isset($_GPC['status'])) {
                $condition .= " AND status = '" . intval($_GPC['status']) . "'";
            }

            $list = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE weid = '{$_W['weid']}' AND storeid ={$storeid} $condition ORDER BY status DESC, displayorder DESC, id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->modulename . '_goods') . " WHERE weid = '{$_W['weid']}' AND storeid ={$storeid} $condition");

            $pager = pagination($total, $pindex, $psize);
        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $row = pdo_fetch("SELECT id, thumb FROM " . tablename($this->modulename . '_goods') . " WHERE id = :id", array(':id' => $id));
            if (empty($row)) {
                message('抱歉，菜品 不存在或是已经被删除！');
            }
            if (!empty($row['thumb'])) {
                file_delete($row['thumb']);
            }
            pdo_delete($this->modulename . '_goods', array('id' => $id));
            message('删除成功！', referer(), 'success');
        }
        include $this->template('goods');
    }

    //智能选菜
    public function doIntelligent()
    {
        global $_W, $_GPC;
        checklogin();
        $action = 'intelligent';
        $title = $this->actions_titles[$action];
        $storeid = intval($_GPC['storeid']);
        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

        if ($operation == 'display') {
            if (!empty($_GPC['displayorder'])) {
                foreach ($_GPC['displayorder'] as $id => $displayorder) {
                    pdo_update($this->modulename . '_intelligent', array('displayorder' => $displayorder), array('id' => $id));
                }
                message('分类排序更新成功！', $this->createWebUrl('intelligent', array('op' => 'display', 'storeid' => $storeid)), 'success');
            }
            $children = array();
            $intelligents = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE weid = '{$_W['weid']}'  AND storeid ={$storeid} ORDER BY displayorder DESC");

            $goods = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE weid = '{$_W['weid']}'  AND storeid ={$storeid} ORDER BY displayorder DESC");
            $goods_arr = array();
            foreach ($goods as $key => $value) {
                $goods_arr[$value['id']] = $value['title'];
            }
            include $this->template('intelligent');
        } elseif ($operation == 'post') {
            $id = intval($_GPC['id']);
            if (!empty($id)) {
                $intelligent = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE id = '$id'");
                if (!empty($intelligent)) {
                    $goodsids = explode(',', $intelligent['content']);
                }
            } else {
                $intelligent = array(
                    'displayorder' => 0,
                );
            }

            $categorys = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_category') . " WHERE weid = '{$_W['weid']}'  AND storeid ={$storeid} ORDER BY displayorder DESC");
            $goods = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE weid = '{$_W['weid']}'  AND storeid ={$storeid} ORDER BY displayorder DESC");
            $goods_arr = array();
            foreach ($goods as $key => $value) {
                foreach ($categorys as $key2 => $value2) {
                    if ($value['pcate'] == $value2['id']) {
                        $goods_arr[$value['pcate']][] = array('id' => $value['id'], 'title' => $value['title']);
                    }
                }
            }

            if (checksubmit('submit')) {
                if (empty($_GPC['name'])) {
                    message('抱歉，请输入分类名称！');
                }

                $data = array(
                    'weid' => $_W['weid'],
                    'storeid' => $_GPC['storeid'],
                    'name' => intval($_GPC['catename']),
                    'content' => trim(implode(',', $_GPC['goodsids'])),
                    'displayorder' => intval($_GPC['displayorder']),
                );

                if ($data['name'] <= 0) {
                    message('人数必须大于0!');
                }

                if (empty($data['storeid'])) {
                    message('非法参数');
                }

                if (!empty($id)) {
                    pdo_update($this->modulename . '_intelligent', $data, array('id' => $id));
                } else {
                    pdo_insert($this->modulename . '_intelligent', $data);
                    $id = pdo_insertid();
                }
                message('更新分类成功！', $this->createWebUrl('intelligent', array('op' => 'display', 'storeid' => $storeid)), 'success');
            }
            include $this->template('intelligent');

        } elseif ($operation == 'delete') {
            $id = intval($_GPC['id']);
            $category = pdo_fetch("SELECT id FROM " . tablename($this->modulename . '_intelligent') . " WHERE id = '$id'");
            if (empty($category)) {
                message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('intelligent', array('op' => 'display', 'storeid' => $storeid)), 'error');
            }
            pdo_delete($this->modulename . '_intelligent', array('id' => $id), 'OR');
            message('分类删除成功！', $this->createWebUrl('category', array('op' => 'display', 'storeid' => $storeid)), 'success');
        }
    }

    public function doOrder()
    {
        global $_W, $_GPC;
        checklogin();
        $action = 'order';
        $title = $this->actions_titles[$action];
        $storeid = intval($_GPC['storeid']);
        if (empty($storeid)) {
            message('请先选择门店!');
        }

        $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
        if ($operation == 'display') {
            $pindex = max(1, intval($_GPC['page']));
            $psize = 20;
            $condition = '';

            if (!empty($_GPC['keyword'])) {
                $condition .= " AND title LIKE '%{$_GPC['keyword']}%'";
            }
            if (!empty($_GPC['cate_2'])) {
                $cid = intval($_GPC['cate_2']);
                $condition .= " AND ccate = '{$cid}'";
            } elseif (!empty($_GPC['cate_1'])) {
                $cid = intval($_GPC['cate_1']);
                $condition .= " AND pcate = '{$cid}'";
            }

            if (isset($_GPC['status'])) {
                $condition .= " AND status = '" . intval($_GPC['status']) . "'";
            }

            $list = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE weid = '{$_W['weid']}' AND storeid={$storeid} $condition ORDER BY id desc, dateline DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);

            $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->modulename . '_order') . " WHERE weid = '{$_W['weid']}' $condition");

            $pager = pagination($total, $pindex, $psize);

            if (!empty($list)) {
                foreach ($list as $row) {
                    $userids[$row['from_user']] = $row['from_user'];
                }
            }

            $users = fans_search($userids, array('realname', 'resideprovince', 'residecity', 'residedist', 'address', 'mobile', 'qq'));

        } elseif ($operation == 'detail') {
            //流程 第一步确认付款 第二步确认订单 第三步，完成订单
            $id = intval($_GPC['id']);
            if (checksubmit('finish')) {
                pdo_update($this->modulename . '_order', array('status' => 3, 'remark' => $_GPC['remark']), array('id' => $id));
                message('订单操作成功！', referer(), 'success');
            }
            if (checksubmit('cancel')) {
                pdo_update($this->modulename . '_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
                message('取消完成订单操作成功！', referer(), 'success');
            }
            if (checksubmit('confirm')) {
                pdo_update($this->modulename . '_order', array('status' => 1, 'remark' => $_GPC['remark']), array('id' => $id));
                message('确认订单操作成功！', referer(), 'success');
            }
            if (checksubmit('cancelpay')) {
                pdo_update($this->modulename . '_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
                message('取消订单付款操作成功！', referer(), 'success');
            }
            if (checksubmit('confrimpay')) {
                //debug
                $order = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE id=:id AND status<>2  LIMIT 1", array(':id' => $id));
                pdo_update($this->modulename . '_order', array('status' => 2, 'remark' => $_GPC['remark']), array('id' => $id));
                message('确认订单付款操作成功！', referer(), 'success');
            }
            if (checksubmit('close')) {
                pdo_update($this->modulename . '_order', array('status' => -1, 'remark' => $_GPC['remark']), array('id' => $id));
                message('订单关闭操作成功！', referer(), 'success');
            }
            if (checksubmit('open')) {
                pdo_update($this->modulename . '_order', array('status' => 0, 'remark' => $_GPC['remark']), array('id' => $id));
                message('开启订单操作成功！', referer(), 'success');
            }
            $item = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE id = :id", array(':id' => $id));
            //$address=pdo_fetch("SELECT * FROM ".tablename('ishopping_address')." WHERE id = :id", array(':id' => $item['aid']));
            $item['user'] = fans_search($item['from_user'], array('realname', 'resideprovince', 'residecity', 'residedist', 'address', 'mobile', 'qq'));
            $goodsid = pdo_fetchall("SELECT goodsid, total FROM " . tablename($this->modulename . '_order_goods') . " WHERE orderid = '{$item['id']}'", array(), 'goodsid');

            $goods = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . "  WHERE id IN ('" . implode("','", array_keys($goodsid)) . "')");
            $item['goods'] = $goods;
        }
        include $this->template('order');
    }



    /*
    ** 设置切换导航
    */
    public function set_tabbar($action, $storeid)
    {
        $actions_titles = $this->actions_titles;
        $html = '<ul class="nav nav-tabs">';
        foreach ($actions_titles as $key => $value) {
            $url = 'site.php?act=module&do=' . $key . '&name=' . $this->modulename . '&storeid=' . $storeid;
            $html .= '<li class="' . ($key == $action ? 'active' : '') . '"><a href="' . $url . '">' . $value . '</a></li>';
        }
        $html .= '</ul>';
        return $html;
    }

    //入口设置
    public function doSetRule()
    {
        global $_W;
        $rule = pdo_fetch("SELECT id FROM " . tablename('rule') . " WHERE module = 'idish' AND weid = '{$_W['weid']}' order by id desc");
        if (empty($rule)) {
            header('Location: ' . $_W['siteroot'] . create_url('rule/post', array('module' => 'idish', 'name' => '微点餐')));
            exit;
        } else {
            header('Location: ' . $_W['siteroot'] . create_url('rule/post', array('module' => 'idish', 'id' => $rule['id'])));
            exit;
        }
    }
}