<?php
  function site_slide_search($params = array()) {
			global $_GPC, $_W;
			extract($params);
		 	$sql = "SELECT * FROM ".tablename('xcommunity_slide'). " WHERE weid = '{$_W['weid']}' ORDER BY id DESC LIMIT $limit";
		 	$list = pdo_fetchall($sql);
	 		if (!empty($list)) {
				foreach ($list as &$row) {
					$row['url'] = strexists($row['url'], 'http') ? $row['url'] : '';
		 		}
		 	}
		 	return $list;
		}
?>