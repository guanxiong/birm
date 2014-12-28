<?php
/**
 * 微动力统计中心模块定义
 *
 * @author We7 Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class StatModuleSite extends WeModuleSite {

	public function doWebKeyword() {
		global $_W, $_GPC;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'hit';

		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";

		if ($foo == 'hit') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename('stat_keyword')." WHERE  weid = '{$_W['weid']}' $where ORDER BY hit DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
			if (!empty($list)) {
				foreach ($list as $index => &$history) {
					if (!empty($history['rid'])) {
						$rids[$history['rid']] = $history['rid'];
					}
					$kids[$history['kid']] = $history['kid'];
				}
			}
			if (!empty($rids)) {
				$rules = pdo_fetchall("SELECT name, id, module FROM ".tablename('rule')." WHERE id IN (".implode(',', $rids).")", array(), 'id');
			}
			if (!empty($kids)) {
				$keywords = pdo_fetchall("SELECT content, id FROM ".tablename('rule_keyword')." WHERE id IN (".implode(',', $kids).")", array(), 'id');
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('stat_keyword')." WHERE  weid = '{$_W['weid']}' $where");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('keyword_hit');
		} elseif ($foo == 'miss') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT content, id, module, rid FROM ".tablename('rule_keyword')." WHERE weid = '{$_W['weid']}' AND id NOT IN (SELECT kid FROM ".tablename('stat_keyword')." WHERE  weid = '{$_W['weid']}' $where) LIMIT ".($pindex - 1) * $psize.','. $psize);
			if (!empty($list)) {
				foreach ($list as $index => $row) {
					if (!empty($row['rid'])) {
						$rids[$row['rid']] = $row['rid'];
					}
				}
			}
			if (!empty($rids)) {
				$rules = pdo_fetchall("SELECT name, id, module FROM ".tablename('rule')." WHERE id IN (".implode(',', $rids).")", array(), 'id');
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('rule_keyword')." WHERE weid = '{$_W['weid']}' AND id NOT IN (SELECT kid FROM ".tablename('stat_keyword')." WHERE  weid = '{$_W['weid']}' $where)");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('keyword_miss');
		}

	}

	public function doWebRule() {
		global $_W, $_GPC;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'hit';

		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";

		if ($foo == 'hit') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename('stat_rule')." WHERE  weid = '{$_W['weid']}' $where ORDER BY hit DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
			if (!empty($list)) {
				foreach ($list as $index => &$history) {
					if (!empty($history['rid'])) {
						$rids[$history['rid']] = $history['rid'];
					}
				}
			}
			if (!empty($rids)) {
				$rules = pdo_fetchall("SELECT name, id, module FROM ".tablename('rule')." WHERE id IN (".implode(',', $rids).")", array(), 'id');
			}
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('stat_rule')." WHERE weid = '{$_W['weid']}' $where");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('rule_hit');
		} elseif ($foo == 'miss') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT name, id, module FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND id NOT IN (SELECT rid FROM ".tablename('stat_rule')." WHERE  weid = '{$_W['weid']}' $where) LIMIT ".($pindex - 1) * $psize.','. $psize);
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('rule')." WHERE weid = '{$_W['weid']}' AND id NOT IN (SELECT rid FROM ".tablename('stat_rule')." WHERE  weid = '{$_W['weid']}' $where)");
			$pager = pagination($total, $pindex, $psize);
			include $this->template('rule_miss');
		}

	}

	public function doWebHistory() {
		global $_W, $_GPC;
		$where = '';
		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$where .= " AND createtime >= '$starttime' AND createtime < '$endtime'";
		!empty($_GPC['keyword']) && $where .= " AND message LIKE '%{$_GPC['keyword']}%'";

		switch ($_GPC['searchtype']) {
			case 'default':
				$where .= " AND module = 'default'";
				break;
			case 'rule':
			default:
				$where .= " AND module <> 'default'";
				break;
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$list = pdo_fetchall("SELECT * FROM ".tablename('stat_msg_history')." WHERE weid = '{$_W['weid']}' $where ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','. $psize);
		if (!empty($list)) {
			foreach ($list as $index => &$history) {
				if ($history['type'] == 'link') {
					$history['message'] = iunserializer($history['message']);
					$history['message'] = '<a href="'.$history['message']['link'].'" target="_blank" title="'.$history['message']['description'].'">'.$history['message']['title'].'</a>';
				} elseif ($history['type'] == 'image') {
					$history['message'] = '<a href="'.$history['message'].'" target="_blank">查看图片</a>';
				} elseif ($history['type'] == 'location') {
					$history['message'] = iunserializer($history['message']);
					$history['message'] = '<a href="http://st.map.soso.com/api?size=800*600&center='.$history['message']['y'].','.$history['message']['x'].'&zoom=16&markers='.$history['message']['y'].','.$history['message']['x'].',1" target="_blank">查看方位</a>';
				} else {
					$history['message'] = emotion($history['message']);
				}
				if (!empty($history['rid'])) {
					$rids[$history['rid']] = $history['rid'];
				}
			}

		}
		if (!empty($rids)) {
			$rules = pdo_fetchall("SELECT name, id FROM ".tablename('rule')." WHERE id IN (".implode(',', $rids).")", array(), 'id');
		}
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('stat_msg_history') . " WHERE weid = '{$_W['weid']}' $where");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('history');
	}

	public function doWebTrend() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);

		$starttime = empty($_GPC['start']) ? strtotime(date('Y-m-d')) - 7 * 46800 : strtotime($_GPC['start']);
		$endtime = empty($_GPC['end']) ? TIMESTAMP : strtotime($_GPC['end']) + 86399;
		$list = pdo_fetchall("SELECT createtime, hit  FROM ".tablename('stat_rule')." WHERE weid = '{$_W['weid']}' AND rid = :rid AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array(':rid' => $id, ':createtime' => $starttime, ':endtime' => $endtime));
		$day = $hit = array();
		if (!empty($list)) {
			foreach ($list as $row) {
				$day[] = date('m-d', $row['createtime']);
				$hit[] = intval($row['hit']);
			}
		}

		$list = pdo_fetchall("SELECT createtime, hit, rid, kid FROM ".tablename('stat_keyword')." WHERE weid = '{$_W['weid']}' AND rid = :rid AND createtime >= :createtime AND createtime <= :endtime ORDER BY createtime ASC", array(':rid' => $id, ':createtime' => $starttime, ':endtime' => $endtime));
		if (!empty($list)) {
			foreach ($list as $row) {
				$keywords[$row['kid']]['hit'][] = $row['hit'];
				$keywords[$row['kid']]['day'][] = date('m-d', $row['createtime']);
			}
			$keywordnames = pdo_fetchall("SELECT content, id FROM ".tablename('rule_keyword')." WHERE id IN (".implode(',', array_keys($keywords)).")", array(), 'id');
		}
		include $this->template('trend');
	}
}
