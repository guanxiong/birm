<?php
/**
 * 图文模块详细页面
 * @author WeEngine Team
 */
defined('IN_IA') or exit('Access Denied');

class NewsModuleSite extends WeModuleSite {

	public function doMobileDetail() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('news_reply') . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (!empty($row['url'])) {
			header("Location: ".$row['url']);
		}
		$row = istripslashes($row);
		$row['thumb'] = $_W['attachurl'] . trim($row['thumb'], '/');
		$title = $row['title'];
		include $this->template('detail');
	}
}