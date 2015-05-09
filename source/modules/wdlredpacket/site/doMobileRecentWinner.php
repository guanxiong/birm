<?php
global $_GPC, $_W;
$id = intval($_GPC['id']);

$sql="select u.huode,u.award,f.nickname from ".tablename('wdlredpacket_winner')." as u left join ".tablename('fans')." as f on u.from_user=f.from_user  where u.rid = '$id' order by u.createtime DESC ,u.id ASC limit 20";
$winners=pdo_fetchall($sql);


include $this->template('recentwinner');