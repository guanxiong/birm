<?php
/**
 * 微信会员卡
 * 作者:迷失卍国度
 * qq: 15595755
 * [WNS]更多模块请浏览：BBS.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class IcardModuleProcessor extends WeModuleProcessor {
    public function respond() {
        global $_W;
        $rid = $this->rule;
        $sql = "SELECT * FROM ".tablename('icard_reply')." WHERE `rid`=:rid LIMIT 1";
        $row = pdo_fetch($sql, array(':rid' => $rid));
        $weid = $row['weid'];
        $from_user = $this->message['from'];
        $card = pdo_fetch("SELECT id FROM " . tablename('icard_card') . " WHERE `from_user`=:from_user AND `weid`=:weid LIMIT 1", array(':from_user' => $from_user, 'weid'=>$weid));

        $response['FromUserName'] = $this->message['to'];
        $response['ToUserName'] = $from_user;
        $response['MsgType'] = 'news';
        $response['ArticleCount'] = 1;
        $response['Articles'] = array();
        $content = !empty($card)?htmlspecialchars_decode($row['description']): htmlspecialchars_decode($row['description_not']);
        $response['Articles'][] = array(
            'Title' => !empty($card)?$row['title']:$row['title_not'],
            'Description' => $content,
            'PicUrl' =>!empty($card)?$_W['attachurl'] . $row['picture']:$_W['attachurl'] . $row['picture_not'],
            'Url' => $_W['siteroot'] . create_url('mobile/module', array('do' => 'wapindex', 'name' => 'icard', 'weid' => $weid, 'from_user' => base64_encode(authcode($from_user, 'ENCODE')))),
            'TagName' => 'item',
        );
        return $response;
    }
}
