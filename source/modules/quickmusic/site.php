<?php
/**
 * 微专辑
 * QQ群：304081212
 * 作者：微动力, 547753994
 *
 * 网站：www.xuehuar.com
 */

defined('IN_IA') or exit('Access Denied');
function my_quickmusic_display_order_sort($a, $b) {
	if ($a['order'] == $b['order']) {
		return 0;
	}
	return ($a['order'] > $b['order']) ? 1 : -1;
}

class QuickMusicModuleSite extends WeModuleSite {
	public $table_tape = "quickmusic_tape";
	public $table_music = "quickmusic_music";

	function __construct() {	
	}
	
	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename($this->table_tape) . ' WHERE `weid`=:weid AND `title` LIKE :title';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		include $this->template('query');
	}
	

	public function doWebMusic() {
		global $_W;
		global $_GPC; // 获取query string中的参数
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		// 首次使用时没有试卷，直接进入新建试题界面
		if (empty($_GPC['op']) && $this->isMusicLibraryEmpty()) {
			$operation = 'post';
		}	

		if ($operation == 'post') {
			$music_id = intval($_GPC['music_id']);
			if (!empty($music_id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename($this->table_music)." WHERE music_id =".$music_id);
				if (empty($item)) {
					message('抱歉，音乐不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入音乐名称');
				}
				if (empty($_GPC['url'])) {
					message('请输入音乐链接！');
				}
				
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'author' => $_GPC['author'],
					'cover' => $_GPC['cover'],
					'url' => $_GPC['url'],
					'explain' => $_GPC['explain'],
					'lyrics' => $_GPC['lyrics'],
				);
				if (!empty($music_id)) {
					pdo_update($this->table_music, $data, array('music_id' => $music_id));
				} else {
					pdo_insert($this->table_music, $data);
				}
				message('更新成功！', $this->createWebUrl('music', array('op' => 'display')), 'success');
			}
		}
		else if ($operation == 'delete') {
			$music_id = intval($_GPC['music_id']);
			$row = pdo_fetch("SELECT music_id FROM ".tablename($this->table_music)." WHERE music_id = ".$music_id);
			if (empty($row)) {
				message('抱歉，音乐不存在或是已经被删除！');
			}
			pdo_delete($this->table_music, array('music_id' => $music_id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename($this->table_music)." WHERE weid = '{$_W['weid']}' $condition ORDER BY music_id DESC");
		}
		include $this->template('music');
	}

	public function doWebAnalysis() {
		echo "开发中...";
	}

	private function isMusicLibraryEmpty() {
		global $_W;
		$result = pdo_fetch("SELECT count(*) as cnt FROM ".tablename($this->table_music)." WHERE weid = '{$_W['weid']}'");
		return ($result['cnt'] <= 0);
	}

	public function doWebTape() {
		global $_W;
		global $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		// 首次使用时没有试卷，直接进入新建试题界面
		if (empty($_GPC['op']) && $this->isTapeLibraryEmpty()) {
			$operation = 'post';
		}

		if ($operation == 'post') {
			$tape_id = intval($_GPC['tape_id']);
			$music_ids = array();
			if (!empty($tape_id)) {
				$tape = pdo_fetch("SELECT * FROM ".tablename($this->table_tape)." WHERE tape_id =".$tape_id);
				if (empty($tape)) {
					message('抱歉，专辑不存在或是已经删除！', '', 'error');
				}
				$music_ids = iunserializer($tape['music_ids']);
				$music_ids_seq = iunserializer($tape['music_ids_seq']);
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入专辑标题');
				}

				$new_music_ids = array();
				foreach($_GPC['music_id'] as $music_id => $on)
				{
					$new_music_ids[] = $music_id;
				}
				$new_music_ids_seq = array();
				foreach($_GPC['music_ids_seq'] as $t_cid => $t_seq) {
					// 41_1 - music 41 is the first one
					// 43_2 - music 43 is the second one
					$new_music_ids_seq[$t_cid] = $t_seq;
				}

				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'logo' => $_GPC['logo'],
					'background' => $_GPC['background'],
					'explain' => $_GPC['explain'],
					'music_ids' => iserializer($new_music_ids),
					'music_ids_seq' => iserializer($new_music_ids_seq),
				);
				if (!empty($tape_id)) {
					pdo_update($this->table_tape, $data, array('tape_id' => $tape_id));
				} else {
					pdo_insert($this->table_tape, $data);
				}
				message('更新成功！', $this->CreateWebUrl('Tape', array('op' => 'display')), 'success');
			}
			$condition = '';
			$music_list = pdo_fetchall("SELECT * FROM ".tablename($this->table_music)." WHERE weid = '{$_W['weid']}' $condition ORDER BY music_id DESC");
		} else if ($operation == 'delete') { //删除
			$tape_id = intval($_GPC['tape_id']);
			$row = pdo_fetch("SELECT tape_id FROM ".tablename($this->table_tape)." WHERE tape_id = ".$tape_id);
			if (empty($row)) {
				message('抱歉，专辑不存在或是已经被删除！');
			}
			pdo_delete($this->table_tape, array('tape_id' => $tape_id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$tape_list = pdo_fetchall("SELECT * FROM ".tablename($this->table_tape)." WHERE weid = '{$_W['weid']}' $condition ORDER BY tape_id DESC");
		}
		
		include $this->template('tape');
	}
		
	private function isTapeLibraryEmpty() {
		global $_W;
		$result = pdo_fetch("SELECT count(*) as cnt FROM ".tablename($this->table_tape)." WHERE weid = '{$_W['weid']}'");
		return ($result['cnt'] <= 0);
	}

	public function doWebHelp() {
		global $_W;
		include $this->template('help');
	}





	public function doMobileCenter() {
		global $_W, $_GPC;
		//$this->checkAuth();
		$profile = fans_search($_W['fans']['from_user']);
		$tape_list = pdo_fetchall("SELECT * FROM ".tablename($this->table_tape)." WHERE weid={$_W['weid']}");
		foreach($tape_list as &$item) {
			$item['explain'] = $this->deleteSpace(strip_tags(htmlspecialchars_decode($item['explain'])));
			$item['logo'] = $this->getPicUrl($item['logo']);
		}
		include $this->template('center');
	}

	public function doMobileTape() {
		global $_W, $_GPC;
		$preview = intval($_GPC['preview']);
		$music_id = intval($_GPC['music_id']);
		$tape_id = intval($_GPC['tape_id']);
	
		if (!$preview) {
			$this->checkTapeState();	
		}
	
		// 检查用户权限		
		if (!$preview) {
			//$this->checkAuth();
			//$fans = fans_require($_W['fans']['from_user'], array('realname', 'mobile'));
			$fans = fans_search($_W['fans']['from_user']);
		} else {
			$fans = fans_search($_GPC['from_user']);
		}

		// support music and tape
		if ($preview && !empty($music_id)) {
			
			
			$where = "weid = '{$_W['weid']}' AND music_id = $music_id";
			$list = pdo_fetchall("SELECT * FROM ".tablename($this->table_music)." WHERE {$where}", array(), "music_id");

		} else if (!empty($tape_id)) {
			
			
			$where = "weid = '{$_W['weid']}'";
			$tape = pdo_fetch("SELECT * FROM ".tablename($this->table_tape). "WHERE {$where} AND tape_id=$tape_id");
			if (empty($tape)) {
				message('抱歉，专辑不存在或是已经删除！', '', 'error');
      }else {
        $music_ids = iunserializer($tape['music_ids']);
        if (count($music_ids) <= 0) {
          message('还没有添加单曲的音乐不能播放哦', referer(), 'error');
        }
        $music_ids_str = join(',', $music_ids);
				$list = pdo_fetchall("SELECT * FROM ".tablename($this->table_music)." WHERE {$where} AND music_id in ($music_ids_str)", array(), "music_id");
			}

			$ids_seq = iunserializer($tape['music_ids_seq']);
			//按照ids_seq排序专辑
			foreach($list as &$t_elem) {
				$t_elem['order'] = (empty($ids_seq[$t_elem['music_id']]) ? 0 : $ids_seq[$t_elem['music_id']]); 
			}
			usort($list, "my_quickmusic_display_order_sort");
		} else {
			message('必须指定专辑！', '', 'error');
		}

		// 显示专辑
		// $list = $this->parseBodyMusics($list);

		include $this->template('player');
		//include $this->template('tape');
	}


	private function getBackgroundPicUrl($url) {
		global $_W;
		if (empty($url)) {
			$day = date("d", time()) % 12;
			$r = $_W['siteroot'] . "/source/modules/quickmusic/images/bg/bg".$day.".jpg";
		} else {
			if(!preg_match('/^(http|https)/', $url)) {  //如果是相对路径
				$r = $_W['attachurl'] .  $url;
			} else {
				$r = $url;
			}
		}
		return $r;
	}


	private function getPicUrl($url) {
		global $_W;
		if (empty($url)) {
			$r = $_W['siteroot'] . "/source/modules/quickmusic/images/default_cover.jpg";
			/*} else {
				if(!preg_match('/^(http|https)/', $url)) {  //如果是相对路径
					$r = $_W['attachurl'] .  $url;
				} else {
					$r = $url;
				}	
			}*/
		} else {
			if(!preg_match('/^(http|https)/', $url)) {  //如果是相对路径
				$r = $_W['attachurl'] .  $url;
			} else {
				$r = $url;
			}
		}
		return $r;
	}

	private function checkAuth() {
		global $_W;
		if (empty($_W['fans']['from_user'])) {
			include $this->template('auth');
			exit;
		} else {
			checkauth(); // fallback to org check
		}
	}


	private function getTape($tape_id) {
		global $_W;
		$tape_id = intval($tape_id);
		$where = "weid = '{$_W['weid']}'  AND tape_id={$tape_id}";
		$sql_tape_info = "SELECT * FROM " . tablename($this->table_tape) . "WHERE {$where}";	
		$tape = pdo_fetch($sql_tape_info);
		if (empty($tape)) {
			message('对不起，专辑不存在，可能已经被删除！', '', 'error');
		}
		return $tape;
	}

	private function checkTapeState() {
		global $_W, $_GPC;

		$where = "weid = '{$_W['weid']}'  AND tape_id={$_GPC['tape_id']}";
		$sql_tape_info = "SELECT * FROM " . tablename($this->table_tape) . "WHERE {$where}";	
	
		$tape = pdo_fetch($sql_tape_info);

		if (empty($tape)) {
			message('对不起，专辑不存在，可能已经被删除！', '', 'error');
		}
	}

	private function deleteSpace($str) {
		$str = trim($str);
		$str = strtr($str,"\t","");
		$str = strtr($str,"\r\n","");
		$str = strtr($str,"\r","");
		$str = strtr($str,"\n","");
		$str = strtr($str," ","");
		$str = str_replace('&nbsp;', "",$str);
		return trim($str);
	}

  public function getCategoryTiles() {
    global $_W;
    $list = pdo_fetchall("SELECT * FROM ".tablename('quickmusic_tape')." WHERE weid={$_W['weid']}");
    //$urls[] = array('title' => '法国香颂', 'url' => $this->createMobileUrl('Center'));
    if (!empty($list)) {
      foreach($list as $item) {
        $urls[] = array('title' => $item['title'], 'url' => $this->createMobileUrl('Tape', array("tape_id" => $item['tape_id'])));
      }
    }
    return $urls;
  }
}
