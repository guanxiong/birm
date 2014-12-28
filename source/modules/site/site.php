<?php
/**
 * 微官网模块微站定义
 *
 * @author WeEngine Team
 * @url http://www.we7.cc
 */
defined('IN_IA') or exit('Access Denied');
include_once IA_ROOT . '/source/modules/site/model.php';
class SiteModuleSite extends WeModuleSite {

	public function doWebCategory() {
		global $_W, $_GPC;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'display';
		if ($foo == 'display') {
			if (!empty($_GPC['displayorder'])) {
				foreach ($_GPC['displayorder'] as $id => $displayorder) {
					pdo_update('article_category', array('displayorder' => $displayorder), array('id' => $id));
				}
				message('分类排序更新成功！', 'refresh', 'success');
			}
			$children = array();
			$category = pdo_fetchall("SELECT * FROM ".tablename('article_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ");
			foreach ($category as $index => $row) {
				if (!empty($row['parentid'])){
					$children[$row['parentid']][] = $row;
					unset($category[$index]);
				}
			}
			include $this->template('category');
		} elseif ($foo == 'post') {
			$parentid = intval($_GPC['parentid']);
			$id = intval($_GPC['id']);
			//微站风格模板
			$template = account_template();

			if(!empty($id)) {
				$category = pdo_fetch("SELECT * FROM ".tablename('article_category')." WHERE id = '$id'");
				if (!empty($category['css'])) {
					$category['css'] = iunserializer($category['css']);
				} else {
					$category['css'] = array();
				}
				if (!empty($category['template'])) {
					$files = array();
					if ($category['ishomepage']) {
						$path = IA_ROOT . '/themes/mobile/' . $category['template'];
						$strexists = 'index';
					} else {
						$path = IA_ROOT . '/themes/mobile/' . $category['template'] . '/site';
						$strexists = '.html';
					}
					if (is_dir($path)) {
						if ($handle = opendir($path)) {
							while (false !== ($filepath = readdir($handle))) {
								if ($filepath != '.' && $filepath != '..' && strexists($filepath, $strexists)) {
									$files[] = $filepath;
								}
							}
						}
					}
				}
			} else {
				$category = array(
					'displayorder' => 0,
					'css' => array(),
				);
			}
			if (!empty($parentid)) {
				$parent = pdo_fetch("SELECT id, name FROM ".tablename('article_category')." WHERE id = '$parentid'");
				if (empty($parent)) {
					message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('category', array('foo' => 'display')), 'error');
				}
			}
			if (checksubmit('fileupload-delete')) {
				file_delete($_GPC['fileupload-delete']);
				pdo_update('site_nav', array('icon' => ''), array('id' => $category['nid']));
				message('删除成功！', referer(), 'success');
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['cname'])) {
					message('抱歉，请输入分类名称！');
				}
				$pathinfo = pathinfo($_GPC['file']);
				$data = array(
					'weid' => $_W['weid'],
					'name' => $_GPC['cname'],
					'displayorder' => intval($_GPC['displayorder']),
					'parentid' => intval($parentid),
					'description' => $_GPC['description'],
					'template' => $_GPC['template'],
					'templatefile' => $pathinfo['filename'],
					'linkurl' => $_GPC['linkurl'],
					'ishomepage' => intval($_GPC['ishomepage']),
				);
				
				$data['icontype'] = intval($_GPC['icontype']);
				$data['css'] = serialize(array(
					'icon' => array(
						'font-size' => $_GPC['icon']['size'],
						'color' => $_GPC['icon']['color'],
						'width' => $_GPC['icon']['size'],
						'icon' => $_GPC['icon']['icon'],
					),
				));
				if (!empty($_FILES['icon']['tmp_name'])) {
					file_delete($_GPC['icon_old']);
					$upload = file_upload($_FILES['icon']);
					if (is_error($upload)) {
						message($upload['message'], '', 'error');
					}
					$data['icon'] = $upload['path'];
				}
				if (!empty($id)) {
					unset($data['parentid']);
					pdo_update('article_category', $data, array('id' => $id));
				} else {
					pdo_insert('article_category', $data);
					$id = pdo_insertid();
				}
				message('更新分类成功！', $this->createWebUrl('category'), 'success');
			}
			include $this->template('category');
		} elseif ($foo == 'fetch') {
			$category = pdo_fetchall("SELECT id, name FROM ".tablename('article_category')." WHERE parentid = '".intval($_GPC['parentid'])."' ORDER BY id ASC, displayorder ASC, id ASC ");
			message($category, '', 'ajax');
		} elseif ($foo == 'delete') {
			$id = intval($_GPC['id']);
			$category = pdo_fetch("SELECT id, parentid, nid FROM ".tablename('article_category')." WHERE id = '$id'");
			if (empty($category)) {
				message('抱歉，分类不存在或是已经被删除！', $this->createWebUrl('category'), 'error');
			}
			$navs = pdo_fetchall("SELECT icon, id FROM ".tablename('site_nav')." WHERE id IN (SELECT nid FROM ".tablename('article_category')." WHERE id = {$id} OR parentid = '$id')", array(), 'id');
			if (!empty($navs)) {
				foreach ($navs as $row) {
					file_delete($row['icon']);
				}
				pdo_query("DELETE FROM ".tablename('site_nav')." WHERE id IN (".implode(',', array_keys($navs)).")");
			}
			pdo_delete('article_category', array('id' => $id, 'parentid' => $id), 'OR');
			message('分类删除成功！', $this->createWebUrl('category'), 'success');
		} elseif ($foo == 'templatefiles') {
			$result = array('status' => -1, 'message' => '');
			$template = $_GPC['template'];
			$ishomepage = intval($_GPC['ishomepage']);
			if ($ishomepage) {
				$path = IA_ROOT . '/themes/mobile/' . $template;
				$strexists = 'index';
			} else {
				$path = IA_ROOT . '/themes/mobile/' . $template . '/site';
				$strexists = '.html';
				if (!is_dir($path)) {
					$result['message'] = '请在当前风格下新建“site”目录，并新建分类模板。例如：list_1.html';
					message($result, '', 'ajax');
				}
			}
			$files = array();
			$path .= '';
			if (is_dir($path)) {
				if ($handle = opendir($path)) {
					while (false !== ($filepath = readdir($handle))) {
						if ($filepath != '.' && $filepath != '..' && strexists($filepath, $strexists)) {
							$files[] = $filepath;
						}
					}
				}
			}
			$result['status'] = 0;
			$result['message'] = $files;
			message($result, '', 'ajax');
		}
	}

	public function doWebArticle() {
		global $_W, $_GPC;
		$foo = !empty($_GPC['foo']) ? $_GPC['foo'] : 'display';
		$category = pdo_fetchall("SELECT * FROM ".tablename('article_category')." WHERE weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
		if (!empty($category)) {
			$children = '';
			foreach ($category as $cid => $cate) {
				if (!empty($cate['parentid'])) {
					$children[$cate['parentid']][] = array($cate['id'], $cate['name']);
				}
			}
		}
		if ($foo == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 20;
			$condition = '';
			$params = array();
			if (!empty($_GPC['keyword'])) {
				$condition .= " AND title LIKE :keyword";
				$params[':keyword'] = "%{$_GPC['keyword']}%";
			}

			if (!empty($_GPC['cate_2'])) {
				$cid = intval($_GPC['cate_2']);
				$condition .= " AND ccate = '{$cid}'";
			} elseif (!empty($_GPC['cate_1'])) {
				$cid = intval($_GPC['cate_1']);
				$condition .= " AND pcate = '{$cid}'";
			}

			$list = pdo_fetchall("SELECT * FROM ".tablename('article')." WHERE weid = '{$_W['weid']}' $condition ORDER BY displayorder DESC, id DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('article') . " WHERE weid = '{$_W['weid']}'");
			$pager = pagination($total, $pindex, $psize);

			include $this->template('article');
		} elseif ($foo == 'post') {
			$id = intval($_GPC['id']);
			//微站风格模板
			$template = account_template();

			if (!empty($id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('article')." WHERE id = :id" , array(':id' => $id));
				$item['type'] = explode(',', $item['type']);
				if (empty($item)) {
					message('抱歉，文章不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('fileupload-delete')) {
				file_delete($_GPC['fileupload-delete']);
				pdo_update('article', array('thumb' => ''), array('id' => $id));
				message('删除成功！', referer(), 'success');
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('标题不能为空，请输入标题！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'iscommend' => intval($_GPC['option']['commend']),
					'ishot' => intval($_GPC['option']['hot']),
					'pcate' => intval($_GPC['cate_1']),
					'ccate' => intval($_GPC['cate_2']),
					'template' => $_GPC['template'],
					'title' => $_GPC['title'],
					'description' => $_GPC['description'],
					'content' => htmlspecialchars_decode($_GPC['content']),
					'source' => $_GPC['source'],
					'author' => $_GPC['author'],
					'displayorder' => intval($_GPC['displayorder']),
					'linkurl' => $_GPC['linkurl'],
					'createtime' => TIMESTAMP,
				);
				if (!empty($_GPC['thumb'])) {
					$data['thumb'] = $_GPC['thumb'];
					file_delete($_GPC['thumb-old']);
				} elseif (!empty($_GPC['autolitpic'])) {
					$match = array();
					preg_match('/attachment\/(.*?)(\.gif|\.jpg|\.png|\.bmp)/', $_GPC['content'], $match);
					if (!empty($match[1])) {
						$data['thumb'] = $match[1].$match[2];
					}
				}
				if (empty($id)) {
					pdo_insert('article', $data);
				} else {
					unset($data['createtime']);
					pdo_update('article', $data, array('id' => $id));
				}
				message('文章更新成功！', $this->createWebUrl('article', array('foo' => 'display')), 'success');
			} else {
				include $this->template('article');
			}
		} elseif ($foo == 'delete') {
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id, thumb FROM ".tablename('article')." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，文章不存在或是已经被删除！');
			}
			if (!empty($row['thumb'])) {
				file_delete($row['thumb']);
			}
			pdo_delete('article', array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
	}

	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT * FROM ' . tablename('article') . ' WHERE `weid`=:weid AND `title` LIKE :title ORDER BY id DESC LIMIT 0,8';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['id'] = $row['id'];
			$r['title'] = $row['title'];
			$r['description'] = cutstr(strip_tags($row['content']), 100);
			$r['thumb'] = $row['thumb'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}

	public function doWebDelete() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		pdo_delete('album_reply', array('id' => $id));
		message('删除成功！', referer(), 'success');
	}
	

	public function doMobileList() {
		global $_GPC, $_W;
		$cid = intval($_GPC['cid']);
		$category = pdo_fetch("SELECT * FROM ".tablename('article_category')." WHERE id = '{$cid}' ");
		if (empty($category)) {
			message('分类不存在或是已经被删除！');
		}
		if (!empty($category['linkurl'])) {
			header('Location: '.$category['linkurl']);
			exit;
		}
		$title = $category['name'];
		if (empty($category['ishomepage'])) {
			//独立选择分类模板
			if(!empty($category['template'])) {
				$_W['account']['template'] = $category['template'];
			}
			if (!empty($category['templatefile'])) {
				include $this->template($category['templatefile']);
				exit;
			} else {
				include $this->template('list');
				exit;
			}
		} else {
			if(!empty($category['template'])) {
				$_W['account']['template'] = $category['template'];
			}
			$navs = pdo_fetchall("SELECT * FROM ".tablename('article_category')." WHERE weid = '{$_W['weid']}' AND parentid = '$cid' ORDER BY displayorder ASC");
			if (!empty($navs)) {
				foreach ($navs as &$row) {
					$row['url'] = $this->createMobileUrl('list', array('cid' => $row['id']));
					if (!empty($row['icontype']) && $row['icontype'] == 1) {
						$row['css'] = iunserializer($row['css']);
						$row['icon'] = '';
					} 
					if (!empty($row['icontype']) && $row['icontype'] == 2) {
						$row['css'] = '';
					} 
				}
			}
			if (!empty($category['templatefile'])) {
				include $this->template($category['templatefile']);
				exit;
			} else {
				include $this->template('index');
				exit;
			}
		}
	}

	public function doMobileDetail() {
		global $_GPC, $_W;
		$id = intval($_GPC['id']);
		$sql = "SELECT * FROM " . tablename('article') . " WHERE `id`=:id";
		$detail = pdo_fetch($sql, array(':id'=>$id));
		if (!empty($detail['linkurl'])) {
			header('Location: '.$detail['linkurl']);
			exit;
		}
		$detail = istripslashes($detail);
		$detail['thumb'] = $_W['attachurl'] . trim($detail['thumb'], '/');
		$title = $detail['title'];
		//独立选择内容模板
		if(!empty($detail['template'])) {
			$_W['account']['template'] = $detail['template'];
		}
		include $this->template('detail');
	}

	public function getCategoryTiles() {
		global $_W;
		$category = pdo_fetchall("SELECT id, name FROM ".tablename('article_category')." WHERE enabled = '1' AND weid = '{$_W['weid']}' ORDER BY parentid ASC, displayorder ASC, id ASC ");
		if (!empty($category)) {
			foreach ($category as $row) {
				$urls[] = array('title' => $row['name'], 'url' => $this->createMobileUrl('list', array('cid' => $row['id'])));
			}
			return $urls;
		}
	}
}
