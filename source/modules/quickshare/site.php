<?php
defined('IN_IA') or exit('Access Denied');

class QuickShareModuleSite extends WeModuleSite {
	public $table_iptable = 'quickshare_iptable';
	public $table_event = 'quickshare_event';
	public $table_goods = 'shopping_goods';

  public function doMobileDiscount()
  {
    global $_GPC, $_W;
    $goodsid = intval($_GPC['goodsid']);
    $orderid = intval($_GPC['orderid']);
    $weid = intval($_GPC['weid']);
    $from_user = $_W['fans']['from_user'];
    // checkauth();
    $goods = pdo_fetch('SELECT * FROM ' . tablename('shopping_goods') . ' WHERE id=:goodsid AND weid=:weid', array(':goodsid'=>$goodsid, ':weid'=>$weid));
    $event = pdo_fetch('SELECT * FROM ' . tablename($this->table_event) . ' WHERE goodsid=:goodsid AND weid=:weid', array(':goodsid'=>$goodsid, ':weid'=>$weid));
    if ($goods['timestart'] > TIMESTAMP) {
      message('活动尚未开始', '', 'error');
    } else if ($goods['timeend'] < TIMESTAMP) {
      message('活动已经于'.date('m-d H:i', $goods['timeend']).'结束', '', 'error');
    } else if ($goods['istime'] != 1) {
      message('对不起，本品活动已经结束，不再参加聚友杀价', '', 'error');
    } else if ($event['discount'] == 0) {
      message('活动还在筹备中，请稍后再试哦', referer(), 'error');
    } else if (true != $this->addClick($orderid, $goodsid)) {
      message('你已经帮忙杀过价啦，谢谢', '', 'error');
    }
    $max_click = intval($event['discount_limit'] / $event['discount']);
    $act_click = $this->getClickCount($orderid, $goodsid);
    if ($max_click < $act_click) {
      message('多亏有了你的帮助，这个订单已经满满的都是赞啦！', referer(), 'success');
    }
    pdo_query("UPDATE " .tablename('shopping_order'). " SET price = price - {$event['discount']}, remark=CONCAT(remark,'杀',NOW()) WHERE id = {$orderid} AND price>0");
    $order = pdo_fetch('SELECT * FROM ' . tablename('shopping_order') . ' WHERE id=:orderid', array(':orderid'=>$orderid));
    $is_result = true;
    include $this->template('share');
  }

  public function doWebShare() {
    global $_W, $_GPC;

    $operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display-log';

    if ($operation == 'post') {
      $goodsid = $_GPC['goodsid'];
      $item = pdo_fetch("SELECT * FROM " . tablename($this->table_event) . " WHERE goodsid=:goodsid", array(":goodsid"=>$goodsid));
      $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_goods) . " WHERE id=:goodsid", array(":goodsid"=>$goodsid));
      if (checksubmit()) {
        $share_title = $_GPC['share_title'];
        $share_content = $_GPC['share_content'];
        $discount = $_GPC['discount'];
        $discount_limit = $_GPC['discount_limit'];
        if (false == $item) {
          pdo_insert($this->table_event, array('weid'=>$_W['weid'], 'share_title'=>$share_title, 'share_content'=>$share_content, 'goodsid'=>$goodsid,
            'discount'=>$discount, 'discount_limit'=>$discount_limit));
        } else {
          pdo_update($this->table_event, array('weid'=>$_W['weid'], 'share_title'=>$share_title, 'share_content'=>$share_content,'discount'=>$discount, 'discount_limit'=>$discount_limit), array('goodsid'=>$goodsid));
        }
        message("更新成功", $this->createWebUrl("Share", array("op"=>"display-event")), "success");
      }
    }
    if ($operation == 'display-event') {
      $list = pdo_fetchall("SELECT * FROM " . tablename($this->table_goods) . " WHERE weid=:weid AND istime=1", array(":weid"=>$_W['weid']));
    }
    if ($operation == 'display-log') {
      $list = pdo_fetchall("SELECT COUNT(goodsid) as click, goodsid, orderid, title  FROM " . tablename($this->table_iptable) . " WHERE weid=:weid GROUP BY orderid",
        array(":weid" => $_W['weid']));
    }
    include $this->template('share');
  }

  public function doMobileShare()
  {
    global $_GPC, $_W;
    $goodsid = intval($_GPC['goodsid']);
    $orderid = intval($_GPC['orderid']);
    $from_user = $_W['fans']['from_user'];
    $goods = pdo_fetch('SELECT * FROM ' . tablename('shopping_goods') . ' WHERE id=:goodsid', array(':goodsid'=>$goodsid));
    $event = pdo_fetch('SELECT * FROM ' . tablename($this->table_event) . ' WHERE goodsid=:goodsid', array(':goodsid'=>$goodsid));
    if ($event['discount'] == 0) {
      message('对不起，该商品的杀价折扣尚未设置，请联系客服处理', referer(), 'error');
    }
    $act_click = $this->getClickCount($orderid, $goodsid);
    $max_click = intval($event['discount_limit'] / $event['discount']);

    include $this->template('share');
  }

  private function addClick($orderid, $goodsid) {
    global $_W;
    $ip = getip(); //'10.1.1.1';
    $isOK = false;
    $from_user = $_W['fans']['from_user'];
    // 检查cookie
    if (true) {
      $cookie_name = "quickshare-" . $_W['weid'] . "-" . $orderid . "-" . $goodsid;
      if (isset($_COOKIE[$cookie_name])) {
        return false;
      } else {
        setcookie($cookie_name, 'killed', time()+60*60*24*7); // 7天内本订单内的每个商品最多杀一次
      }
    }

    if (empty($from_user)) {
      $history = pdo_fetch("SELECT * FROM "
        . tablename($this->table_iptable)
        . " WHERE weid=:weid AND ip=:ip AND orderid=:orderid AND goodsid=:goodsid AND TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(createtime), NOW()) < 24",
          array(":weid"=>$_W['weid'], ":ip" => $ip, ":orderid"=>$orderid, ":goodsid" => $goodsid));
    } else {
      $history = pdo_fetch("SELECT * FROM "
        . tablename($this->table_iptable)
        . " WHERE weid=:weid AND ip=:ip AND orderid=:orderid AND goodsid=:goodsid AND from_user=:from_user AND TIMESTAMPDIFF(HOUR, FROM_UNIXTIME(createtime), NOW()) < 24",
      array(":weid"=>$_W['weid'], ":ip" => $ip, ":orderid"=>$orderid, ":goodsid" => $goodsid, ":from_user"=>$from_user));
    }
    if (false == $history) {
      $isOK = true;
      $goods = pdo_fetch("SELECT * FROM " . tablename($this->table_goods) . " WHERE id=:goodsid", array("goodsid"=>$goodsid));
      pdo_insert($this->table_iptable,
        array("weid"=>$_W['weid'],
        "ip"=>$ip,
        "orderid"=>$orderid,
        "goodsid" => $goodsid,
        "title"=>$goods['title'],
        "createtime"=>TIMESTAMP,
        "from_user"=>$from_user));
    } else {
      $isOK = false;
    }
    return $isOK;
  }

  private function getClickCount($orderid, $goodsid) {
    global $_W;
    $act_click = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->table_iptable) . ' WHERE orderid=:orderid AND goodsid=:goodsid AND weid=:weid',
      array(':orderid'=>$orderid, ':goodsid'=>$goodsid, ':weid'=>$_W['weid']));
    return $act_click;
  }
  public function doWebhelper(){
		global $_W;
		include $this->template('helper');
	}
}
