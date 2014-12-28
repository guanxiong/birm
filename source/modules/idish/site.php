<?php
/**
 * 微点餐
 *
 * 作者:迷失卍国度
 *
 * qq : 15595755
 */
defined('IN_IA') or exit('Access Denied');

class IdishModuleSite extends WeModuleSite
{
    //模块标识
    public $modulename = 'idish';
    //入口类型
    public $entrance_type_index = 1;
    public $entrance_type_rest = 2;
    public $entrance_type_list = 3;
    public $entrance_type_menu = 4;
    public $msg_status_success = 1;
    public $msg_status_bad = 0;

    //网站入口
    public function doMobileEntrance()
    {
        global $_W, $_GPC;
        $this->checkAuth();
        $template_name = 'dish_index'; //默认进入首页
        $weid = $_W['weid'];
        $title = '微点餐';
        $page_from_user = base64_encode(authcode($_W['fans']['from_user'], 'ENCODE'));
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_setting') . " WHERE weid={$weid}  ORDER BY id DESC LIMIT 1");
        $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . "  WHERE weid={$weid}  ORDER BY id DESC LIMIT 1");
        $storeid = $store['id'];
        $title = $setting['title'];

        if (empty($page_from_user) || empty($storeid)) {
            message('参数错误!');
        }

        $nave = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_nave') . " WHERE weid={$weid} AND status=1 ORDER BY displayorder DESC,id DESC");

        include $this->template($template_name);
    }

    //导航首页
    public function doMobileWapIndex()
    {
        global $_GPC, $_W;
        $title = '微点餐';
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        if (empty($from_user)) {
            message('会话已经过时，请从微信端重新发送关键字登录！');
        }

        if (!empty($storeid)) {
            $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE id=" . $storeid);
        } else {
            $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . "  WHERE weid={$weid}  ORDER BY id DESC LIMIT 1");
            $storeid = $store['id'];
        }

        if (empty($store)) {
            message('非法参数');
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_setting') . " WHERE weid={$weid}  ORDER BY id DESC LIMIT 1");
        $title = $setting['title'];

        $nave = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_nave') . " WHERE weid={$weid} AND status=1 ORDER BY displayorder DESC,id DESC");

        include $this->template('dish_index');
    }

    //菜品列表
    public function doMobileWapList()
    {
        global $_GPC, $_W;
        $title = '全部菜品';
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        if (empty($from_user) || empty($storeid)) {
            message('会话已过期，请重新发送关键字!');
        }

        $pindex = max(1, intval($_GPC['page']));
        $psize = 20;
        $condition = '';

        if (!empty($_GPC['ccate'])) {
            $cid = intval($_GPC['ccate']);
            $condition .= " AND ccate = '{$cid}'";
        } elseif (!empty($_GPC['pcate'])) {
            $cid = intval($_GPC['pcate']);
            $condition .= " AND pcate = '{$cid}'";
        }

        $children = array();
        $category = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_category') . " WHERE weid = '{$weid}' AND storeid={$storeid} ORDER BY  displayorder DESC,id DESC");

        $cid = intval($category[0]['id']);
        $category_in_cart = pdo_fetchall("SELECT goodstype,count(1) as 'goodscount' FROM " . tablename($this->modulename . '_cart') . " GROUP BY weid,storeid,goodstype,from_user  having weid = '{$weid}' AND storeid='{$storeid}' AND from_user='{$from_user}'");
        $category_arr = array();
        foreach ($category_in_cart as $key => $value) {
            $category_arr[$value['goodstype']] = $value['goodscount'];
        }

        $list = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE weid = '{$weid}' AND storeid={$storeid} AND status = '1' AND pcate={$cid} ORDER BY displayorder DESC, subcount DESC, id DESC ");

        $dish_arr = $this->getDishCountInCart($from_user, $storeid, $weid);

        //智能点餐
        $intelligents = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE weid={$weid} AND storeid={$storeid} GROUP BY name ORDER by name");

        include $this->template('dish_list');
    }

    //我的菜单
    public function doMobileWapMenu()
    {
        global $_GPC;
        $title = '我的菜单';
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        if (empty($from_user) || empty($storeid)) {
            message('会话已过期，请重新发送关键字!');
        }

        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_setting') . " WHERE weid={$weid} LIMIT 1");
        $cart = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_cart') . " a LEFT JOIN " . tablename('idish_goods') . " b ON a.goodsid=b.id WHERE a.weid='{$weid}' AND a.from_user='{$from_user}' AND a.storeid='{$storeid}'");
        $order = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE weid={$weid} AND from_user=:from_user ORDER BY id DESC LIMIT 1", array(':from_user' => $from_user));
        include $this->template('dish_menu');
    }

    //门店列表
    public function doMobileWapRestList()
    {
        global $_GPC, $_W;
        $title = '我的菜单';

        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');

        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }
        $restlist = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_stores') . " where weid = '{$weid}' and is_show=1");
        include $this->template('dish_rest_list');
    }

    //门店实景
    public function doMobileWapShopShow()
    {
        global $_GPC;
        $title = '商家店面';
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        $storeid = intval($_GPC['storeid']);
        if (empty($from_user) || empty($storeid)) {
            echo '会话已过期，请重新发送关键字!';
            exit;
        }
        $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE id=:id", array(':id' => $storeid));
        if (empty($store)) {
            echo '没有相关数据!';
            exit;
        }
        $thumb_Arr = explode('|', $store['thumb_url']);
        include $this->template('dish_shop_show');
    }

    //获取各个分类被选中菜品的数量
    public function doMobileGetDishNumOfCategory()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }

        $data = array();
        $category_in_cart = pdo_fetchall("SELECT goodstype,count(1) as 'goodscount' FROM " . tablename($this->modulename . '_cart') . " GROUP BY weid,storeid,goodstype,from_user  having weid = '{$weid}' AND storeid='{$storeid}' AND from_user='{$from_user}'");
        $category_arr = array();
        foreach ($category_in_cart as $key => $value) {
            $category_arr[$value['goodstype']] = $value['goodscount'];
        }

        $category = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_cart') . " GROUP BY weid,storeid  having weid = '{$weid}' AND storeid='{$storeid}'");

        foreach ($category as $index => $row) {
            //$data[$row['id']] = $row['name'];
            $data[$row['id']] = intval($category_arr[$row['id']]);
        }

        $result['data'] = $data;
        message($result, '', 'ajax');
    }

    //从购物车移除
    public function doMobileRemoveDishNumOfCategory()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']); //门店id
        $dishid = intval($_GPC['dishid']); //菜品id
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        $action = $_GPC['action'];
        if (empty($from_user) || $action != 'remove' || empty($storeid)) {
            $result['msg'] = '非法操作';
            message($result, '', 'ajax');
        }

        //查询购物车有没该商品
        $cart = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_cart') . " WHERE goodsid=:goodsid AND weid=:weid AND storeid=:storeid AND from_user='" . $from_user . "'", array(':goodsid' => $dishid, ':weid' => $weid, ':storeid' => $storeid));
        if (empty($cart)) {
            $result['msg'] = '购物车为空!';
            message($result, '', 'ajax');
        } else {
            pdo_delete('idish_cart', array('id' => $cart['id']));
        }
        $result['code'] = 0;
        message($result, '', 'ajax');
    }

    //购物车增加菜品
    public function doMobileUpdateDishNumOfCategory()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']); //门店id
        $dishid = intval($_GPC['dishid']); //菜品id
        $total = intval($_GPC['o2uNum']); //更新数量
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        if (empty($from_user)) {
            $result['msg'] = '非法操作';
            message($result, '', 'ajax');
        }

        //查询菜品是否存在
        $goods = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE  id=:id", array(":id" => $dishid));
        if (empty($goods)) {
            $result['msg'] = '没有相关商品';
            message($result, '', 'ajax');
        }

        //查询购物车有没该商品
        $cart = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_cart') . " WHERE goodsid=:goodsid AND weid=:weid AND storeid=:storeid AND from_user='" . $from_user . "'", array(':goodsid' => $dishid, ':weid' => $weid, ':storeid' => $storeid));

        if (empty($cart)) {
            //不存在的话增加菜品点击量
            pdo_query("UPDATE " . tablename($this->modulename . '_goods') . " SET subcount=subcount+1 WHERE id=:id", array(':id' => $dishid));
            //添加进购物车
            $data = array(
                'weid' => $weid,
                'storeid' => $goods['storeid'],
                'goodsid' => $goods['id'],
                'goodstype' => $goods['pcate'],
                'price' => $goods['isspecial'] == 1 ? $goods['productprice'] : $goods['marketprice'],
                'from_user' => $from_user,
                'total' => 1
            );
            pdo_insert($this->modulename . '_cart', $data);
        } else {
            //更新菜品在购物车中的数量
            pdo_query("UPDATE " . tablename($this->modulename . '_cart') . " SET total=" . $total . " WHERE id=:id", array(':id' => $cart['id']));
        }

        $result['code'] = 0;
        message($result, '', 'ajax');
    }

    //取得菜品列表
    public function doMobileGetDishList()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }

        $categoryid = intval($_GPC['categoryid']);
        $list = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE weid = '{$weid}' AND status = '1' AND storeid='{$storeid}' AND pcate={$categoryid} order by displayorder DESC,id DESC");
        $dish_arr = $this->getDishCountInCart($from_user, $storeid, $weid);

        foreach ($list as $key => $row) {
            $subcount = intval($row['subcount']);
            $data[$key] = array(
                'id' => $row['id'],
                'title' => $row['title'],
                'dSpecialPrice' => $row['marketprice'],
                'dPrice' => $row['productprice'],
                'dDescribe' => $row['description'], //描述
                'dTaste' => $row['taste'], //口味
                'dSubCount' => $row['subcount'], //被点次数
                'thumb' => $row['thumb'],
                'unitname' => $row['unitname'],
                'dIsSpecial' => $row['isspecial'],
                'dIsHot' => $subcount > 20 ? 2 : 0,
                'total' => empty($dish_arr) ? 0 : intval($dish_arr[$row['id']]) //菜品数量
            );
        }
        $result['data'] = $data;
        $result['categoryid'] = $categoryid;
        message($result, '', 'ajax');
    }

    //清空购物车
    public function doMobileClearMenu()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        $url = create_url('mobile/module', array('do' => 'waplist', 'from_user' => $page_from_user, 'name' => 'idish', 'weid' => $weid, 'storeid' => $storeid));
        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }

        pdo_delete('idish_cart', array('weid' => $weid, 'from_user' => $from_user, 'storeid' => $storeid));
        message('操作成功', $url, 'success');
    }

    //智能点餐_选人数
    public function doMobileWapSelect()
    {
        global $_GPC;
        $title = '微点餐';
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }

        $intelligents = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE weid={$weid} AND storeid={$storeid} GROUP BY name ORDER by name");
        include $this->template('dish_select');
    }

    //智能点餐_菜单页
    public function doMobileWapSelectList()
    {
        global $_GPC, $_W;
        $title = '微点餐';
        $storeid = intval($_GPC['storeid']);
        $num = intval($_GPC['num']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }
        if ($num <= 0 || $storeid <= 0) {
            message('非法参数');
        }

        $intelligent_count = pdo_fetchcolumn('SELECT COUNT(1) FROM ' . tablename($this->modulename . '_intelligent') . " WHERE name={$num} AND weid={$weid} AND storeid={$storeid}");

        //智能菜单id
        $intelligentid = intval($_GPC['intelligentid']);
        if ($intelligent_count > 1) {
            //随机抽取推荐菜单
            $intelligent = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE name={$num} AND weid={$weid} AND storeid={$storeid} AND id<>{$intelligentid} ORDER BY RAND() limit 1");
        } else {
            $intelligent = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE name={$num} AND weid={$weid} AND storeid={$storeid} ORDER BY RAND() limit 1");
        }
        $intelligentid = intval($intelligent['id']);

        //读取相关产品
        $goods = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE FIND_IN_SET(id, '{$intelligent['content']}') AND weid={$weid} AND storeid={$storeid}");

        $total_money = 0;
        foreach ($goods as $key => $value) {
            $goods_arr[$value['id']] = array(
                'id' => $value['id'],
                'pcate' => $value['pcate'],
                'name' => $value['name'],
                'thumb' => $value['thumb'],
                'isspecial' => $value['isspecial'],
                'productprice' => $value['productprice'],
                'unitname' => $value['unitname'],
                'marketprice' => $value['marketprice'],
                'subcount' => $value['subcount'],
                'taste' => $value['taste'],
                'description' => $value['description']);
            $goods_tmp[] = $value['pcate'];
            $total_money += $value['isspecial'] == 1 ? intval($value['productprice']) : intval($value['marketprice']);
        }
        $condition = trim(implode(',', $goods_tmp));
        //读取类别
        $categorys = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_category') . " WHERE weid={$weid} AND storeid={$storeid} AND FIND_IN_SET(id, '{$condition}') ORDER BY displayorder DESC");
        include $this->template('dish_select_list');
    }

    //添加菜品到菜单
    public function doMobileAddToMenu()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        if (empty($from_user)) {
            message('会话已过期，请重新发送关键字!');
        }
        $clearMenu = intval($_GPC['clearMenu']);
        //清空购物车
        if ($clearMenu == 1) {
            pdo_delete('idish_cart', array('weid' => $weid, 'from_user' => $from_user, 'storeid' => $storeid));
        }

        //添加菜单所属菜品到
        $intelligentid = intval($_GPC['intelligentid']);
        $intelligent = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_intelligent') . " WHERE id={$intelligentid} limit 1");

        if (!empty($intelligent)) {
            $goods = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . " WHERE FIND_IN_SET(id, '{$intelligent['content']}') AND weid={$weid} AND storeid={$storeid}");

            foreach ($goods as $key => $item) {
                //查询购物车有没该商品
                $cart = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_cart') . " WHERE goodsid=:goodsid AND weid=:weid AND storeid=:storeid AND from_user='" . $from_user . "'", array(':goodsid' => $item['id'], ':weid' => $weid, ':storeid' => $storeid));
                if (empty($cart)) {
                    //不存在的话增加菜品点击量
                    pdo_query("UPDATE " . tablename($this->modulename . '_goods') . " SET subcount=subcount+1 WHERE id=:id", array(':id' => $item['id']));
                    //添加进购物车
                    $data = array(
                        'weid' => $weid,
                        'storeid' => $item['storeid'],
                        'goodsid' => $item['id'],
                        'goodstype' => $item['pcate'],
                        'price' => $item['isspecial'] == 1 ? $item['productprice'] : $item['marketprice'],
                        'from_user' => $from_user,
                        'total' => 1
                    );
                    pdo_insert($this->modulename . '_cart', $data);
                }
            }
        }
        //跳转
        $url = create_url('mobile/module', array('do' => 'wapmenu', 'from_user' => $page_from_user, 'name' => 'idish', 'weid' => $weid, 'storeid' => $storeid));
        message('操作成功', $url, 'success');
    }

    //提交订单
    public function doMobileAddToOrder()
    {
        global $_W, $_GPC;
        //$this->checkAuth();
        $weid = $_W['weid']; //$weid = intval($_GET['weid']);
        if (empty($weid)) {
            $this->showMessageAjax('请重新发送关键字进入系统!', $this->msg_status_bad);
        }
        //$from_user = $_W['fans']['from_user'];//$from_user = authcode(base64_decode($_GPC['from_user']), 'DECODE');
        //$page_from_user = base64_encode(authcode($_W['fans']['from_user'], 'ENCODE'));//$page_from_user = $_GPC['from_user'];
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        $storeid = intval($_GPC['storeid']);
        if (empty($from_user) || empty($storeid)) {
            $this->showMessageAjax('请重新发送关键字进入系统!', $this->msg_status_bad);
        }

        //查询购物车
        $cart = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_cart') . " WHERE weid = '{$weid}' AND from_user = '{$from_user}' AND storeid={$storeid}", array(), 'goodsid'); //debug

        if (empty($cart)) { //购物车为空
            $this->showMessageAjax('请先添加菜品!', $this->msg_status_bad);
        } else {
            $goods = pdo_fetchall("SELECT id, title, thumb, marketprice, unitname FROM " . tablename($this->modulename . '_goods') . " WHERE id IN ('" . implode("','", array_keys($cart)) . "')");
        }

        //1.判断提交信息
        $guest_name = trim($_GPC['guest_name']); //用户名
        $tel = trim($_GPC['tel']); //电话
        $sex = trim($_GPC['sex']); //性别
        $sdate = trim($_GPC['time']); //订餐时间
        $counts = intval($_GPC['counts']); //预订人数
        $seat_type = intval($_GPC['seat_type']); //就餐形式
        $carports = intval($_GPC['carports']); //预订车位
        $remark = trim($_GPC['remark']); //备注
        $address = trim($_GPC['address']); //地址
        $tables = intval($_GPC['tables']); //桌号
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_setting') . " WHERE weid={$weid} LIMIT 1");

        $ordertype = intval($_GPC['ordertype']) == 0 ? 1 : intval($_GPC['ordertype']);

        //if (!empty($setting) && $setting['dining_mode'] == 1) {
        if (empty($guest_name)) {
            $this->showMessageAjax('请输入姓名!', $this->msg_status_bad);
        }
        if (empty($tel)) {
            $this->showMessageAjax('请输入联系电话!', $this->msg_status_bad);
        }

        if ($ordertype == 1) {
            //店内
            if ($counts <= 0) {
                $this->showMessageAjax('预订人数必须大于0!', $this->msg_status_bad);
            }
            if ($seat_type == 0) {
                $this->showMessageAjax('请选择就餐形式!', $this->msg_status_bad);
            }
            if ($tables == 0) {
                $this->showMessageAjax('请输入桌号!', $this->msg_status_bad);
            }
        } else if ($ordertype == 2) {
            //外卖
            if (empty($address)) {
                $this->showMessageAjax('请输入联系地址!', $this->msg_status_bad);
            }
        }

        $sdate = $sdate . trim($_GPC['time_hour']) . trim($_GPC['time_second']);
        //2.购物车 //a.添加订单、订单产品
        //保存新订单 //提交、确认、付款、取消
        $totalnum = 0;
        $totalprice = 0;

        foreach ($cart as $value) {
            $totalnum = $totalnum + intval($value['total']);
            $totalprice = $totalprice + (intval($value['total']) * floatval($value['price']));
        }

        $fansid = $_W['fans']['id'];
        $data = array(
            'weid' => $weid,
            'from_user' => $from_user,
            'storeid' => $storeid,
            'ordersn' => date('md') . sprintf("%04d", $fansid) . random(4, 1), //订单号
            'totalnum' => $totalnum, //产品数量
            'totalprice' => $totalprice, //总价
            'paytype' => 0, //付款类型
            'username' => $guest_name,
            'tel' => $tel,
            'meal_time' => $sdate,
            'counts' => $counts,
            'seat_type' => $seat_type,
            'carports' => $carports,
            //'dining_mode' => $setting['dining_mode'], //用餐模式
            'dining_mode' => $ordertype, //订单类型
            'remark' => $remark, //备注
            'address' => $address, //地址
            'status' => 0, //状态
            'dateline' => TIMESTAMP
        );

        //保存订单
        pdo_insert($this->modulename . '_order', $data);
        $orderid = pdo_insertid();

        //保存新订单商品
        foreach ($cart as $row) {
            if (empty($row) || empty($row['total'])) {
                continue;
            }

            pdo_insert($this->modulename . '_order_goods', array(
                'weid' => $_W['weid'],
                'storeid' => $row['storeid'],
                'goodsid' => $row['goodsid'],
                'orderid' => $orderid,
                'price' => $row['price'],
                'total' => $row['total'],
                'dateline' => TIMESTAMP,
            ));
        }

        //清空购物车
        pdo_delete($this->modulename . '_cart', array('weid' => $weid, 'from_user' => $from_user, 'storeid' => $storeid));
        $result['orderid'] = $orderid;
        $result['code'] = $this->msg_status_success;
        $result['msg'] = '操作成功';
        message($result, '', 'ajax');
        //$this->showMessageAjax('操作成功!', $this->msg_status_success);//
    }

    //订单
    public function doMobileOrderConfirm()
    {
        global $_W, $_GPC;
        $orderid = intval($_GPC['orderid']);
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        if (empty($from_user) || empty($storeid)) {
            message('会话已过期，请重新发送关键字!');
        }

        $order = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE id=:id AND weid=:weid AND storeid=:storeid AND status=0", array(':id' => $orderid, ':weid' => $weid, ':storeid' => $storeid));
        if (empty($order)) {
            message('订单不存在或订单已经确认过了!');
        }
        //产品信息
        $goodslist = pdo_fetchall("SELECT a.*,b.* FROM " . tablename($this->modulename . '_order_goods') . " as a left join " . tablename($this->modulename . '_goods') . " as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$order['id']}");

        //门店信息
        $store = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_stores') . " WHERE id=:id", array(':id' => $order['storeid']));

        include $this->template('dish_order_confirm');
    }

    //确认订单
    public function doMobileOrderConfirmUpdate()
    {
        global $_W, $_GPC;
        $orderid = intval($_GPC['orderid']);
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];
        $order = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE id=:id AND weid=:weid AND storeid=:storeid", array(':id' => $orderid, ':weid' => $weid, ':storeid' => $storeid));

        if (!empty($order)) {
            if ($order['status'] == 1) {
                $this->showMessageAjax('该订单已经确认过了，无需重复提交!', $this->msg_status_bad);
            }
        }
        pdo_query("UPDATE " . tablename($this->modulename . '_order') . " SET status=1 WHERE id=:id", array(':id' => $orderid));

        //发送短信提醒
        $smsSetting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_sms_setting') . " WHERE weid=:weid AND storeid=:storeid LIMIT 1", array(':weid' => $weid, ':storeid' => $storeid));
        $sendInfo = array();
        $goods_str = '';
        //本订单产品
        $goods = pdo_fetchall("SELECT a.*,b.title,b.unitname FROM " . tablename($this->modulename . '_order_goods') . " as a left join  " . tablename($this->modulename . '_goods') . " as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$orderid}");
        $goods_str = '';
        $flag = false;
        foreach ($goods as $key => $value) {
            if (!$flag) {
                $goods_str .= "{$value['title']}{$value['total']}{$value['unitname']}";
                $flag = true;
            } else {
                $goods_str .= ",{$value['title']}{$value['total']}{$value['unitname']}";
            }
        }

        if (!empty($smsSetting)) {
            if ($smsSetting['sms_enable'] == 1 && !empty($smsSetting['sms_mobile'])) {
                //模板
                if (empty($smsSetting['sms_business_tpl'])) {
                    $smsSetting['sms_business_tpl'] = '您有新的订单：[sn]，收货人：[name]，电话：[tel]，请及时确认订单！';
                }
                //订单号
                $smsSetting['sms_business_tpl'] = str_replace('[sn]', $order['ordersn'], $smsSetting['sms_business_tpl']);
                //用户名
                $smsSetting['sms_business_tpl'] = str_replace('[name]', $order['username'], $smsSetting['sms_business_tpl']);
                //就餐时间
                $smsSetting['sms_business_tpl'] = str_replace('[date]', $order['meal_time'], $smsSetting['sms_business_tpl']);
                //电话
                $smsSetting['sms_business_tpl'] = str_replace('[tel]', $order['tel'], $smsSetting['sms_business_tpl']);
                $smsSetting['sms_business_tpl'] = str_replace('[totalnum]', $order['totalnum'], $smsSetting['sms_business_tpl']);
                $smsSetting['sms_business_tpl'] = str_replace('[totalprice]', $order['totalprice'], $smsSetting['sms_business_tpl']);
                $smsSetting['sms_business_tpl'] = str_replace('[address]', $order['address'], $smsSetting['sms_business_tpl']);
                $smsSetting['sms_business_tpl'] = str_replace('[remark]', $order['remark'], $smsSetting['sms_business_tpl']);
                $smsSetting['sms_business_tpl'] = str_replace('[goods]', $goods_str, $smsSetting['sms_business_tpl']);

                $sendInfo['username'] = $smsSetting['sms_username'];
                $sendInfo['pwd'] = $smsSetting['sms_pwd'];
                $sendInfo['mobile'] = $smsSetting['sms_mobile'];
                $sendInfo['content'] = $smsSetting['sms_business_tpl'];
                //debug
                //$this->showMessageAjax($sendInfo['content'], $this->msg_status_success);
                $this->_sendSms($sendInfo);
                //$this->sendTestSms();
            }
        }

        //发送邮件提醒
        $emailSetting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_email_setting') . " WHERE weid=:weid AND storeid=:storeid LIMIT 1", array(':weid' => $weid, ':storeid' => $storeid));
        if (!empty($emailSetting) && !empty($emailSetting['email'])) {
            $emailSetting['email_business_tpl'] = str_replace('[sn]', $order['ordersn'], $emailSetting['email_business_tpl']);
            //用户名
            $emailSetting['email_business_tpl'] = str_replace('[name]', $order['username'], $emailSetting['email_business_tpl']);
            //就餐时间
            $emailSetting['email_business_tpl'] = str_replace('[date]', $order['meal_time'], $emailSetting['email_business_tpl']);
            //电话
            $emailSetting['email_business_tpl'] = str_replace('[tel]', $order['tel'], $emailSetting['email_business_tpl']);
            $emailSetting['email_business_tpl'] = str_replace('[goods]', $goods_str, $emailSetting['email_business_tpl']);
            $emailSetting['email_business_tpl'] = str_replace('[totalnum]', $order['totalnum'], $emailSetting['email_business_tpl']);
            $emailSetting['email_business_tpl'] = str_replace('[totalprice]', $order['totalprice'], $emailSetting['email_business_tpl']);
            $emailSetting['email_business_tpl'] = str_replace('[address]', $order['address'], $emailSetting['email_business_tpl']);
            $emailSetting['email_business_tpl'] = str_replace('[remark]', $order['remark'], $emailSetting['email_business_tpl']);


            if ($emailSetting['email_host'] == 'smtp.qq.com' || $emailSetting['email_host'] == 'smtp.gmail.com') {
                $secure = 'ssl';
                $port = '465';
            } else {
                $secure = 'tls';
                $port = '25';
            }

            $mail_config = array();
            $mail_config['host'] = $emailSetting['email_host'];
            $mail_config['secure'] = $secure;
            $mail_config['port'] = $port;
            $mail_config['username'] = $emailSetting['email_user'];
            $mail_config['sendmail'] = $emailSetting['email_send'];
            $mail_config['password'] = $emailSetting['email_pwd'];
            $mail_config['mailaddress'] = $emailSetting['email'];
            $mail_config['subject'] = '订单提醒';
            $mail_config['body'] = $emailSetting['email_business_tpl'];
            $result = $this->sendmail($mail_config);
            //$result = ihttp_email($emailSetting['email'], '订单提醒', $emailSetting['email_business_tpl']);
        }
        $this->showMessageAjax('订单确认成功，请等待处理!', $this->msg_status_success);
    }

    //我的订单
    public function doMobileOrderList()
    {
        global $_W, $_GPC;
        $storeid = intval($_GPC['storeid']);
        $weid = !empty($_W['weid']) ? $_W['weid'] : intval($_GET['weid']);
        $from_user = !empty($_W['fans']['from_user']) ? $_W['fans']['from_user'] : authcode(base64_decode($_GPC['from_user']), 'DECODE');
        $page_from_user = !empty($_W['fans']['from_user']) ? base64_encode(authcode($_W['fans']['from_user'], 'ENCODE')) : $_GPC['from_user'];

        if (empty($weid) || empty($storeid) || empty($from_user)) {
            message('非法操作');
        }

        //未确认
        $order_list_part1 = pdo_fetchall("SELECT a.*,b.address FROM " . tablename($this->modulename . '_order') . " AS a LEFT JOIN " . tablename($this->modulename . '_stores') . " AS b ON a.storeid=b.id  WHERE a.status=0 AND a.storeid={$storeid} AND a.from_user='{$from_user}' ORDER BY a.id DESC LIMIT 20");
        foreach ($order_list_part1 as $key => $value) {
            $order_list_part1[$key]['goods'] = pdo_fetchall("SELECT a.*,b.title FROM " . tablename($this->modulename . '_order_goods') . " AS a LEFT JOIN " . tablename($this->modulename . '_goods') . " as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$value['id']}");
        }
        //数量
        $order_total_part1 = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename($this->modulename . '_order') . " WHERE status=0 AND storeid={$storeid} AND from_user='{$from_user}' ORDER BY id DESC");
        //已确认
        $order_list_part2 = pdo_fetchall("SELECT a.*,b.address FROM " . tablename($this->modulename . '_order') . " AS a LEFT JOIN " . tablename($this->modulename . '_stores') . " AS b ON a.storeid=b.id  WHERE (a.status=1 OR a.status=2 OR a.status=3) AND a.storeid={$storeid} AND a.from_user='{$from_user}' ORDER BY a.id DESC LIMIT 20");
        //数量
        $order_total_part2 = pdo_fetchcolumn("SELECT COUNT(1) FROM " . tablename($this->modulename . '_order') . " WHERE (status=1 OR status=2 OR status=3) AND storeid={$storeid} AND from_user='{$from_user}' ORDER BY id DESC");
        foreach ($order_list_part2 as $key => $value) {
            $order_list_part2[$key]['goods'] = pdo_fetchall("SELECT a.*,b.title FROM " . tablename($this->modulename . '_order_goods') . " as a left join  " . tablename($this->modulename . '_goods') . " as b on a.goodsid=b.id WHERE a.weid = '{$weid}' and a.orderid={$value['id']}");
        }
        include $this->template('dish_order_list');
    }

    //我的订单
    public function doMobileMyBook()
    {
        global $_W, $_GPC;
        include $this->template('mybook');
    }

    public function  doMobileOrderDetail()
    {
        global $_W, $_GPC;
        include $this->template('orderdetail');
    }

    //提示信息
    public function showMessageAjax($msg, $code)
    {
        $result['code'] = $code;
        $result['msg'] = $msg;
        message($result, '', 'ajax');
    }

    //取得购物车中的菜品
    public function getDishCountInCart($from_user, $storeid, $weid)
    {
        $dishlist = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_cart') . " WHERE  storeid='{$storeid}' AND from_user='" . $from_user . "' AND weid={$weid}");
        foreach ($dishlist as $key => $value) {
            $arr[$value['goodsid']] = $value['total'];
        }
        return $arr;
    }

    //发送短信
    public function _sendSms($sendinfo)
    {
        global $_W;
        $weid = $_W['weid'];
        $username = $sendinfo['username'];
        $pwd = $sendinfo['pwd'];
        $mobile = $sendinfo['mobile'];
        $content = $sendinfo['content'];
        $target = "http://www.dxton.com/webservice/sms.asmx/Submit";
        //替换成自己的测试账号,参数顺序和wenservice对应
        $post_data = "account=" . $username . "&password=" . $pwd . "&mobile=" . $mobile . "&content=" . rawurlencode($content);
        //请自己解析$gets字符串并实现自己的逻辑
        //<result>100</result>表示成功,其它的参考文档

        $result = ihttp_request($target, $post_data);
        $xml = simplexml_load_string($result['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        $result = (string)$xml->result;
        $message = (string)$xml->message;
        return true;
    }

    private function checkAuth()
    {
        global $_W;
        if (empty($_W['fans']['from_user'])) {
            message('会话已过期，请重新发送关键字!');
        }
    }

    public function sendmail($config)
    {
        include 'plugin/email/class.phpmailer.php';
        $mail = new PHPMailer();
        $mail->CharSet = "utf-8";
        $body = $config['body'];
        $mail->IsSMTP();
        $mail->SMTPAuth = true; // enable SMTP authentication
        $mail->SMTPSecure = $config['secure']; // sets the prefix to the servier
        $mail->Host = $config['host']; // sets the SMTP server
        $mail->Port = $config['port'];
        $mail->Username = $config['sendmail']; // 发件邮箱用户名
        $mail->Password = $config['password']; // 发件邮箱密码
        $mail->From = $config['sendmail']; //发件邮箱
        $mail->FromName = $config['username']; //发件人名称
        $mail->Subject = $config['subject']; //主题
        $mail->WordWrap = 50; // set word wrap
        $mail->MsgHTML($body);
        $mail->AddAddress($config['mailaddress'], ''); //收件人地址、名称
        $mail->IsHTML(true); // send as HTML
        if (!$mail->Send()) {
            $status = 0;
        } else {
            $status = 1;
        }
        return $status;
    }

    //打印数据
    public function doWebPrint()
    {
        global $_W, $_GPC;
        $weid = $_W['weid'];
        $usr = !empty($_GET['usr']) ? $_GET['usr'] : '355839026790719';
        $ord = !empty($_GET['ord']) ? $_GET['ord'] : 'no';
        $sgn = !empty($_GET['sgn']) ? $_GET['sgn'] : 'no';

        $print_type_confirmed = 0;
        $print_type_payment = 1;

        //更新打印状态
        if (isset($_GET['sta'])) {
            $id = intval($_GPC['id']);
            $sta = intval($_GPC['sta']);
            pdo_update($this->modulename . '_order', array('print_sta' => $sta), array('id' => $id));
            exit;
        }

        //获取门店信息
        $setting = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_print_setting') . " WHERE print_usr = :usr AND print_status=1", array(':usr' => $usr));
        if ($setting == false) {
            exit;
        }

        //门店id
        $storeid = $setting['storeid'];

        $condition = "";
        if ($setting['print_type'] == $print_type_confirmed) {
            //已确认订单 //status == 1
            $condition = ' AND status=1 ';
        } else if ($setting['print_type'] == $print_type_payment) {
            //已付款订单 //已完成
            $condition = ' AND (status=2 or status=3) ';
        }

        //根据订单id读取相关订单
        $order = pdo_fetch("SELECT * FROM " . tablename($this->modulename . '_order') . " WHERE storeid = :storeid AND print_sta=-1 {$condition} limit 1", array(':storeid' => $storeid));

        //没有新订单
        if ($order == false) {
            message('没有任何数据!');
            exit;
        }

        //菜品id数组
        $goodsid = pdo_fetchall("SELECT goodsid, total FROM " . tablename($this->modulename . '_order_goods') . " WHERE orderid = '{$order['id']}'", array(), 'goodsid');

        //菜品
        $goods = pdo_fetchall("SELECT * FROM " . tablename($this->modulename . '_goods') . "  WHERE id IN ('" . implode("','", array_keys($goodsid)) . "')");
        $order['goods'] = $goods;

        if (!empty($setting['print_top'])) {
            $content = "%10" . $setting['print_top'] . "\n";
        } else {
            $content = '';
        }

        $content .= '%00单号:' . $order['ordersn'] . "\n";

        $content .= '下单日期:' . date('Y-m-d H:i:s', $order['dateline']) . "\n";
        $content .= '预约时间:' . $order['meal_time'] . "\n";
        if (!empty($order['remark'])) {
            $content .= '备注:' . $order['remark'] . "\n";
        }

        $content .= "%00\n名称              数量  单价 \n";
        $content .= "----------------------------\n";

        $content1 = '';
        foreach ($order['goods'] as $v) {
            $content1 .= $this->stringformat($v['title'], 16) . $this->stringformat($goodsid[$v['id']]['total'], 4, false) . $this->stringformat(number_format($v['marketprice'], 1), 7, false) . "\n";
        }

        $content2 = "----------------------------\n";
        $content2 .= "%10总数量:" . $order['totalnum'] . "   总价:" . number_format($order['totalprice'], 1) . "元\n%00";
        if (!empty($order['guest_name'])) {
            $content2 .= '姓名:' . $order['guest_name'] . "\n";
        }
        if (!empty($order['tel'])) {
            $content2 .= '手机:' . $order['tel'] . "\n";
        }
        if (!empty($order['address'])) {
            $content2 .= '地址:' . $order['address'] . "\n";
        }
        if (!empty($order['tables'])) {
            $content2 .= '桌号:' . $order['tables'] . "\n";
        }
        if (!empty($setting['print_bottom'])) {
            $content2 .= "%10" . $setting['print_bottom'] . "\n%00";
        }

        $content = iconv("UTF-8", "GB2312//IGNORE", $content);
        $content1 = iconv("UTF-8", "GB2312//IGNORE", $content1);
        $content2 = iconv("UTF-8", "GB2312//IGNORE", $content2);

        $setting = '<setting>124:' . $setting['print_nums'] . '|134:0</setting>';
        $setting = iconv("UTF-8", "GB2312//IGNORE", $setting);
        echo '<?xml version="1.0" encoding="GBK"?><r><id>' . $order['id'] . '</id><time>' . date('Y-m-d H:i:s', $order['dateline']) . '</time><content>' . $content . $content1 . $content2 . '</content>' . $setting . '</r>';
    }

    //用户打印机处理订单
    private function stringformat($string, $length = 0, $isleft = true)
    {
        if ($length == 0 || $string == '') {
            return $string;
        }
        if (strlen($string) > $length) {
            for ($i = 0; $i < $length; $i++) {
                $substr = $substr . "_";
            }
            $string = $string . '%%' . $substr;
        } else {
            for ($i = strlen($string); $i < $length; $i++) {
                $substr = $substr . " ";
            }
            $string = $isleft ? ($string . $substr) : ($substr . $string);
        }
        return $string;
    }
}