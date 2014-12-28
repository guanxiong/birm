<?php
/**
 * LBS定位及周边查询模块模块处理程序
 *
 * @author 微信通
 * @url http://bbs.we7.cc/forum.php?mod=forumdisplay&fid=36
 */
defined('IN_IA') or exit('Access Denied');
class SharevoteModuleProcessor extends WeModuleProcessor {

	public function respond() {
        global $_W;
        $rid = $this->rule;
        $sql = "SELECT id FROM " . tablename('news_reply') . " WHERE `rid`=:rid AND parentid = 0 ORDER BY RAND()";
        $main = pdo_fetch($sql, array(':rid' => $rid));
        if (empty($main['id'])) {
        return array();
        }
        $sql = "SELECT * FROM " . tablename('news_reply') . " WHERE id = :id OR parentid = :parentid ORDER BY parentid ASC, id ASC LIMIT 10";
        $commends = pdo_fetchall($sql, array(':id'=>$main['id'], ':parentid'=>$main['id']));
        $news = array();
        foreach($commends as $c) {
            $row = array();
            $row['title'] = $c['title'];
            $row['description'] = $c['description'];
            !empty($c['thumb']) && $row['picurl'] = $_W['attachurl'] . trim($c['thumb'], '/');
            $row['url'] =$_W['siteroot'].create_url('index/module', array('do' => 'send_vote', 'name' => 'sharevote'));
            $news[] = $row;
        }
        return $this->respNews($news);
        }
}