<?php
/*


小区活动详细页面

*/
$id = intval($_GPC['id']);
$item = pdo_fetch("SELECT * FROM".tablename('xcommunity_activity')."WHERE id='{$id}'");
$enddate = strtotime($item['enddate']);
include $this->template('detail');
