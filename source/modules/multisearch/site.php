<?php
/**
 * 万能查询模块定义
 *
 * @author WeEngine Team
 * @url http://we7.cc
 */
defined('IN_IA') or exit('Access Denied');

class MultisearchModuleSite extends WeModuleSite {
	public $types = array(
		'text' => '字串(text)',
		'number' => '数字(number)',
		'textarea' => '文本(textarea)',
		'radio' => '单选(radio)',
		'checkbox' => '多选(checkbox)',
		'select' => '选择(select)',
		'calendar' => '日历(calendar)',
		'calendarrange' => '日历范围(calendarrange)',
		'image' => '上传图片(image)',
	);

	public function getMenuTiles() {
		global $_W, $_GPC;
		$menus = array();
		$list = pdo_fetchall("SELECT * FROM ".tablename('multisearch')." WHERE weid = :weid", array(':weid' => $_W['weid']));
		if (!empty($list)) {
			foreach ($list as $row) {
				$menus[] = array('title' => $row['title'], 'url' => $this->createWebUrl('content', array('op' => 'display', 'reid' => $row['id'])));
			}
		}
		return $menus;
	}

	public function doWebStruct() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (!empty($id)) {
				$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $id));
				$search['fields']  = pdo_fetchall("SELECT * FROM ".tablename('multisearch_fields')." WHERE reid = :reid ORDER BY displayorder DESC", array(':reid' => $id), 'variable');
				$search['status'] = iunserializer($search['status']);
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入标题！');
				}
				$variables = array();
				$hassearch = !empty($search['fields']) ?  true : false;
				if (!empty($_GPC['fields-new']['title'])) {
					foreach ($_GPC['fields-new']['title'] as $i => $row) {
						if (empty($_GPC['fields-new']['bind'][$i]) && !empty($_GPC['fields-new']['field'][$i])) {
							$_GPC['fields-new']['bind'][$i] = $_GPC['fields-new']['field'][$i];
						}
						if (empty($_GPC['fields-new']['title'][$i])) {
							message('表单名称为必填项，请返回修改！');
						}
						if (empty($_GPC['fields-new']['variable'][$i])) {
							message('表单变量名为必填项，请返回修改！');
						}
						if(!preg_match("/^[a-z_]+[a-z\d]*$/i", $_GPC['fields-new']['variable'][$i])) {
							message('变量名只能是字母，数字，下划线，并以字母或下划线开头，以字母或数字结尾！');
						}
						if (in_array($_GPC['fields-new']['variable'][$i], $variables) || !empty($search['fields'][$_GPC['fields-new']['variable'][$i]])) {
							message('表单变量名不得重复，请返回修改！');
						}
						if (!empty($_GPC['fields-new']['search'][$i])) {
							$hassearch = true;
						}
						$variables[] = $_GPC['fields-new']['variable'][$i];
					}
					if (empty($hassearch)) {
						message('表单中最少需要指定一个查询项，请返回修改！');
					}
				}
				$data = array(
					'weid' => intval($_W['weid']),
					'title' => $_GPC['title'],
					'description' => $_GPC['description'],
					'isresearch' => intval($_GPC['isresearch']),
					'cover' => $_GPC['cover'],
					'template' => !empty($_GPC['template']) ? $_GPC['template'] : 'default',
					'mobile' => trim($_GPC['mobile']),
					'noticeemail' => $_GPC['noticeemail'],
				);
				if (!empty($_GPC['status'])) {
					$i = 1;
					foreach ($_GPC['status'] as $name) {
						$data['status'][$i] = $name;
						$i++;
					}
					$data['status'] = iserializer($data['status']);
				}
				if (!empty($id)) {
					pdo_update('multisearch', $data, array('id' => $id));
				} else {
					pdo_insert('multisearch', $data);
					$id = pdo_insertid();
					$sql = "
					CREATE TABLE IF NOT EXISTS ".tablename("multisearch_data_".$id)." (
					  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
					  `weid` INT UNSIGNED NOT NULL,
					  `reid` INT UNSIGNED NOT NULL,
					  `data` text NOT NULL COMMENT '数据',
					  `createtime` INT( 10 ) UNSIGNED NOT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;";
					pdo_run($sql);
				}
				$tablename = "multisearch_data_".$id;
				if (!empty($_GPC['fields-new']['title'])) {
					foreach ($_GPC['fields-new']['title'] as $i => $row) {
						$data = array(
							'reid' => $id,
							'type' => intval($_GPC['fields-new']['type'][$i]),
							'title' => $_GPC['fields-new']['title'][$i],
							'variable' => $_GPC['fields-new']['variable'][$i],
							'displayorder' => intval($_GPC['fields-new']['displayorder'][$i]),
							'required' => intval($_GPC['fields-new']['required'][$i]),
							'search' => intval($_GPC['fields-new']['search'][$i]),
							'likesearch' => intval($_GPC['fields-new']['like'][$i]),
							'bind' => $_GPC['fields-new']['bind'][$i],
							'description' => $_GPC['fields-new']['description'][$i],
							'options' => $_GPC['fields-new']['options'][$i],
						);
						pdo_insert('multisearch_fields', $data);
						if ($data['type'] == 1 && $data['search'] && !pdo_fieldexists($tablename, $data['variable'])) {
							if ($data['bind'] == 'number') {
								$sql = "ALTER TABLE ".tablename($tablename)." ADD `{$data['variable']}` INT( 10 ) NOT NULL DEFAULT '0';";
							} else {
								$sql = "ALTER TABLE ".tablename($tablename)." ADD `{$data['variable']}`  TEXT NOT NULL DEFAULT '';";
							}
							if (!pdo_fieldexists($tablename, $data['variable'])) {
								pdo_query($sql);
							}
						}
					}
				}
				if (!empty($_GPC['fields']['title'])) {
					foreach ($_GPC['fields']['title'] as $i => $row) {
						if (empty($_GPC['fields']['title'][$i])) {
							continue;
						}
						$data = array(
							'title' => $_GPC['fields']['title'][$i],
							'required' => intval($_GPC['fields']['required'][$i]),
							'likesearch' => intval($_GPC['fields']['like'][$i]),
							'displayorder' => intval($_GPC['fields']['displayorder'][$i]),
							'description' => $_GPC['fields']['description'][$i],
							'options' => $_GPC['fields']['options'][$i],
						);
						if (!empty($_GPC['fields']['bind'][$i]) || !empty($_GPC['fields']['field'][$i])) {
							$data['bind'] = !empty($_GPC['fields']['bind'][$i]) ? $_GPC['fields']['bind'][$i] : $_GPC['fields']['field'][$i];
						}
						pdo_update('multisearch_fields', $data, array('id' => $i));
					}
				}
				message('更新成功！', $this->createWebUrl('struct', array('op' => 'post', 'id' => $id)), 'success');
			}

			$fields = pdo_fetchall("SELECT field, title FROM ".tablename('profile_fields'));
			$path = IA_ROOT . '/source/modules/multisearch/template/mobile/';
			if (is_dir($path)) {
				if ($handle = opendir($path)) {
					while (false !== ($templatepath = readdir($handle))) {
						if ($templatepath != '.' && $templatepath != '..' && is_dir($path . $templatepath)) {
							$template[] = $templatepath;
						}
					}
				}
			}
		} elseif ($operation == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename('multisearch')." WHERE weid = '{$_W['weid']}' $condition ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize);
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('multisearch') . " WHERE weid = '{$_W['weid']}' $condition");
			$pager = pagination($total, $pindex, $psize);
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			if ($_GPC['type'] == 'item') {
				$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $id));
				$search['tablename'] = 'multisearch_data_'.$search['id'];
				if (empty($search)) {
					message('抱歉，数据不存在或是已经被删除！');
				}
				pdo_delete('multisearch', array('id' => $id));
				pdo_query("DROP TABLE IF EXISTS ".tablename($search['tablename']));

			} elseif ($_GPC['type'] == 'field') {
				$reid = intval($_GPC['reid']);
				$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $reid));
				$search['tablename'] = 'multisearch_data_'.$search['id'];

				$row = pdo_fetch("SELECT id, variable, search FROM ".tablename('multisearch_fields')." WHERE id = :id", array(':id' => $id));
				if (empty($row)) {
					message('抱歉，数据不存在或是已经被删除！');
				}
				pdo_delete('multisearch_fields', array('id' => $id));
				if ($row['search']) {
					$sql = "ALTER TABLE ".tablename($search['tablename'])." DROP {$row['variable']}";
					if (pdo_fieldexists($search['tablename'], $row['variable'])) {
						pdo_query($sql);
					}
				}
			}
			message('删除成功！', referer(), 'success');
		}
		include $this->template('struct');
	}

	public function doWebContent() {
		global $_GPC, $_W;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';

		$reid = intval($_GPC['reid']);
		$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $reid));
		$search['tablename'] = 'multisearch_data_'.$search['id'];
		$search['fields']  = pdo_fetchall("SELECT * FROM ".tablename('multisearch_fields')." WHERE reid = :reid AND type = '1' ORDER BY displayorder DESC", array(':reid' => $reid));

		if ($operation == 'post') {
			$id = intval($_GPC['id']);
			if (checksubmit('submit')) {
				if (empty($search['fields'])) {
					message('此查询暂未添加任何数据结构！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'reid' => $reid,
				);
				foreach ($search['fields'] as $row) {
					if ($row['required'] && empty($_GPC[$row['variable']])) {
						message($row['title'].'为必填项，请返回修改！');
					}
					if ($row['search']) {
						$data[$row['variable']] = $_GPC[$row['variable']];
					} else {
						$data['data'][$row['variable']] = $_GPC[$row['variable']];
					}
				}
				$data['data'] = !empty($data['data']) ? iserializer($data['data']) : '';
				if (!empty($id)) {
					pdo_update($search['tablename'], $data, array('id' => $id));
				} else {
					$data['createtime'] = TIMESTAMP;
					pdo_insert($search['tablename'], $data);
				}
				message('更新记录成功！', referer(), 'success');
			}
			if (!empty($id)) {
				$data = pdo_fetch("SELECT * FROM ".tablename($search['tablename'])." WHERE id = :id", array(':id' => $id));
				$data['data'] = !empty($data['data']) ? iunserializer($data['data']) : '';
			}
		} elseif ($operation == 'display') {
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename($search['tablename'])." WHERE weid = '{$_W['weid']}' AND reid = :reid ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize, array(':reid' => $reid));
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($search['tablename']) . " WHERE weid = '{$_W['weid']}' AND reid = :reid", array(':reid' => $reid));
			$pager = pagination($total, $pindex, $psize);
		} elseif ($operation == 'delete') {
			$id = intval($_GPC['id']);
			$data = pdo_fetch("SELECT * FROM ".tablename($search['tablename'])." WHERE id = :id", array(':id' => $id));
			$data['data'] = !empty($data['data']) ? iunserializer($data['data']) : '';

			$imagefields = pdo_fetchall("SELECT id FROM ".tablename('multisearch_fields')." WHERE reid = :reid AND bind = 'image'", array(':reid' => $reid), 'id');
			if (!empty($imagefields)) {
				foreach ($imagefields as $row) {
					if (!empty($data[$row])) {
						file_delete($data[$row]);
					}
					if (!empty($data['data'][$row])) {
						file_delete($data['data'][$row]);
					}
				}
			}
			pdo_delete($search['tablename'], array('id' => $id));
			message('删除成功！', referer(), 'success');
		} elseif ($operation == 'batch') {
			if (checksubmit('submit')) {
				$file = file_get_contents($_FILES['file']['tmp_name']);
				if (!empty($file)) {
					$file = iconv('gbk', 'utf-8', $file);
					$file = explode("\r\n", $file);
					unset($file[0]);
					unset($file[1]);
				
					foreach ($file as $row) {
						$row = explode(',', $row);
						if (!empty($row)) {
							$data = array();
							foreach ($row as $i => $value) {
								if (!empty($search['fields'][$i]['search'])) {
									if (empty($value)) {
										continue;
									}
									$data[$search['fields'][$i]['variable']] = $value;
								} else {
									$data['data'][$search['fields'][$i]['variable']] = $value;
								}
							}
							if (!empty($data)) {
								$data['weid'] = $_W['weid'];
								$data['reid'] = $reid;
								$data['createtime'] = TIMESTAMP;
								if (!empty($data['data'])) {
									$data['data'] = serialize($data['data']);
								}
								pdo_insert($search['tablename'], $data);
							}
						}
					}
				}
				message('批量导入数据成功！', $this->createWebUrl('content', array('op' => 'display', 'reid' => $reid)));
			}
		} elseif ($operation == 'downexcel') {
			if (empty($search['fields'])) {
				message('请添加查询数据结构！');
			}
			
			header("Content-type:application/vnd.ms-excel;charset=gbk");
			header("Content-Disposition:attachment; filename=batch.csv");
			foreach ($search['fields'] as $row) {
				$titles[] = iconv('utf-8', 'gbk', $row['title']);
			}
			echo iconv('utf-8', 'gbk', '批量导入“'.$search['title'].'”文件，请根据对应的列添加数据，一行一条') . "\r\n";
			echo implode(',', $titles) . "\r\n";
			exit;
		}
		include $this->template('content');
	}

	public function doWebResearch() {
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		$reid = intval($_GPC['reid']);
		$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $reid));
		$search['tablename'] = 'multisearch_data_'.$search['id'];
		$search['fields']  = pdo_fetchall("SELECT * FROM ".tablename('multisearch_fields')." WHERE reid = :reid AND type = '1' ORDER BY displayorder DESC", array(':reid' => $reid));
		$search['status'] = iunserializer($search['status']);
		if ($operation == 'display') {
			if (checksubmit('submit')) {
				if (!empty($_GPC['select'])) {
					pdo_query("UPDATE ".tablename('multisearch_research')." SET status = '".intval($_GPC['status'])."' WHERE id IN (".implode(',', $_GPC['select']).")");
				}
				message('状态更新成功！', referer(), 'success');
			}
			$pindex = max(1, intval($_GPC['page']));
			$psize = 50;
			$list = pdo_fetchall("SELECT * FROM ".tablename('multisearch_research')." WHERE weid = '{$_W['weid']}' AND reid = :reid ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize, array(':reid' => $reid));
			$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('multisearch_research') . " WHERE weid = '{$_W['weid']}' AND reid = :reid", array(':reid' => $reid));
			$pager = pagination($total, $pindex, $psize);
			if (!empty($list)) {
				foreach ($list as $row) {
					$rowids[$row['rowid']] = $row['rowid'];
				}
				$rows = pdo_fetchall("SELECT * FROM ".tablename($search['tablename'])." WHERE id IN (".implode(',', $rowids).")", array(), 'id');
			}
		} elseif ($operation == 'post') {
			$id = intval($_GPC['id']);
			$research = pdo_fetch("SELECT * FROM ".tablename('multisearch_research')." WHERE id = :id", array(':id' => $id));
			if (!empty($research['data'])) {
				$research['data'] = iunserializer($research['data']);
			}
			$search['research']  = pdo_fetchall("SELECT * FROM ".tablename('multisearch_fields')." WHERE reid = :reid AND type = '2' ORDER BY displayorder DESC", array(':reid' => $reid));
			if (checksubmit('submit')) {
				if (!empty($search['research'])) {
					foreach ($search['research'] as $row) {
						if (isset($this->types[$row['bind']])) {
							$extra[$row['variable']] = $_GPC[$row['variable']];
						} else {
							$fans[$row['bind']] = $_GPC[$row['bind']];
						}
					}
				}
				if (!empty($fans)) {
					fans_update($research['openid'], $fans);
				}
				pdo_update('multisearch_research', array('data' => iserializer($extra), 'remark' => $_GPC['remark']), array('id' => $research['id']));
				message('更新成功！', '', 'success');
			}
			if (!empty($search['research'])) {
				foreach ($search['research'] as $row) {
					if (isset($this->types[$row['bind']])) {
						$extra[] = $row['bind'];
					} else {
						$fans[] = $row['bind'];
					}
				}
			}
			if (!empty($fans)) {
				$research['fans'] = fans_search($research['openid'], $fans);
			}
		}
		include $this->template('research');
	}

	private function formatForm($field, $value = '') {
		$html = '';
		$type = $field['bind'];
		$name = $field['variable'];

		switch ($type) {
			case 'number':
				$html = '<input type="text" class="'.(defined('IN_MOBILE') ? 'form-control' : 'span5').'" name="'.$name.'" value="'.$value.'" />';
				break;
			case 'title':
			case 'text':
				$html = '<input type="text" class="'.(defined('IN_MOBILE') ? 'form-control' : 'span5').'" name="'.$name.'" value="'.$value.'" />';
				break;
			case 'textarea':
				$html = '<textarea class="'.(defined('IN_MOBILE') ? 'form-control' : 'span5').'" name="'.$name.'" rows="3">'.$value.'</textarea>';
				break;
			case 'radio':
			case 'select':
				if (!empty($field['options'])) {
					$field['options'] = str_replace('，', ',', $field['options']);
				}
				$options = explode(',', $field['options']);
				$html = '<select name="'.$name.'">';
				if (!empty($options)) {
					foreach ($options as $val) {
						$html .= '<option value="'.$val.'" '.($val == $value ? 'selected="selected"' : '').'>'.$val.'</option>';
					}
				}
				$html .= '</select>';
				break;
			case 'checkbox':
				if (!empty($field['options'])) {
					$field['options'] = str_replace('，', ',', $field['options']);
				}
				$options = explode(',', $field['options']);
				if (!empty($options)) {
					foreach ($options as $val) {
						$html .= '<label class="checkbox inline"><input type="checkbox" name="'.$name.'[]" value="'.$val.'" '.($val == $value ? 'checked' : '').' />'.$val.'</label>';
					}
				}
				break;
			case 'calendarrange':
				$html = tpl_form_field_daterange($name, $value);
				break;
			case 'calendar' :
				$html = tpl_form_field_date($name, $value);
				break;
			case 'image':
				$html = tpl_form_field_image($name, $value);
				break;
		}
		if (!empty($row['description'])) {
			$html .= '<span class="help-block">'.urldecode($row['description']).'</span>';
		}
		return $html;
	}

	public function doMobileDetail() {
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $id));
		$search['fields']  = pdo_fetchall("SELECT * FROM ".tablename('multisearch_fields')." WHERE reid = :reid ORDER BY displayorder DESC", array(':reid' => $id));
		$search['tablename'] = 'multisearch_data_'.$search['id'];
		if (checksubmit('submit')) {
			if (!empty($_GPC['search'])) {
				$fieldids = pdo_fetchall("SELECT id, variable, likesearch FROM ".tablename('multisearch_fields')." WHERE reid = :reid AND variable IN ('".implode("','", array_keys($_GPC['search']))."')", array(':reid' => $id));
				if (!empty($fieldids)) {
					foreach ($fieldids as $row) {
						if (!empty($row['likesearch'])) {
							$condition[] = " `{$row['variable']}` LIKE :{$row['variable']}";
							$params[":{$row['variable']}"] = "%{$_GPC['search'][$row['variable']]}%";
						} else {
							$condition[] = " `{$row['variable']}` = :{$row['variable']}";
							$params[":{$row['variable']}"] = $_GPC['search'][$row['variable']];
						}
					}
				}
			}

			$sql = "SELECT * FROM ".tablename($search['tablename'])." WHERE reid = '$id' AND ".implode(' AND ', $condition);
			$list = pdo_fetchall($sql, $params);
			if (!empty($list)) {
				foreach ($list as &$row) {
					$row['data'] = iunserializer($row['data']);
					$row['createtime'] = date('Y-m-d H:i', $row['createtime']);
				}
			}
			unset($row);
			include $this->template($search['template'].'/result');
			exit;
		}
		include $this->template($search['template'].'/index');
	}

	public function doMobileResearch() {
		global $_W, $_GPC;
		$reid = intval($_GPC['reid']);
		$id = intval($_GPC['id']);

		$search = pdo_fetch("SELECT * FROM ".tablename('multisearch')." WHERE id = :id", array(':id' => $reid));
		$search['tablename'] = 'multisearch_data_'.$search['id'];
		$search['fields']  = pdo_fetchall("SELECT * FROM ".tablename('multisearch_fields')." WHERE reid = :reid AND type = 2 ORDER BY displayorder DESC", array(':reid' => $reid));
		if (empty($search['isresearch'])) {
			message('该查询暂不支持预定！');
		}

		$research = pdo_fetch("SELECT id, data FROM ".tablename('multisearch_research')." WHERE rowid = :rowid AND openid = :openid", array(':rowid' => $id, ':openid' => $_W['fans']['from_user']));
		$research['data'] = iunserializer($research['data']);

		if (checksubmit('submit')) {
			if (!empty($search['fields'])) {
				foreach ($search['fields'] as $row) {
					if ($row['type'] == 2 && !empty($row['bind'])) {
						if (isset($this->types[$row['bind']])) {
							$extra[$row['variable']] = $_GPC[$row['variable']];
						} else {
							$data[$row['variable']] = $_GPC[$row['variable']];
						}
					}
				}
				if (!empty($data)) {
					fans_update($_W['fans']['from_user'], $data);
				}
				if (!empty($search['noticeemail'])) {
					$body = '';
					foreach ($search['fields'] as $row) {
						if ($row['type'] == 2) {
							$body .= "{$row['title']} : {$_GPC[$row['variable']]} \r\n";
						}
					}
					ihttp_email($search['noticeemail'], '【'.$search['title'].'】的预约通知', $body);
				}
				if (!empty($research['id'])) {
					$data = array(
						'data' => iserializer($extra),
					);
					pdo_update('multisearch_research', $data, array('id' => $research['id']));
				} else {
					$data = array(
						'weid' => $_W['weid'],
						'openid' => $_W['fans']['from_user'],
						'reid' => $reid,
						'rowid' => $id,
						'status' => 0,
						'data' => iserializer($extra),
						'createtime' => TIMESTAMP,
					);
					pdo_insert('multisearch_research', $data);
				}
			}
			
			message('提交信息成功！', $this->createMobileUrl('detail', array('id' => $reid)), 'success');
		}
		$sql = "SELECT * FROM ".tablename($search['tablename'])." WHERE id = '$id'";
		$fields = pdo_fetch($sql);
		$fields['data'] = iunserializer($fields['data']);

		if (!empty($search['fields'])) {
			foreach ($search['fields'] as $row) {
				if ($row['type'] == 2) {
					if (!empty($row['bind']) && !isset($this->types[$row['bind']])) {
						$fansfields[] = $row['bind'];
					}
				}
			}
		}
		if (!empty($fansfields)) {
			$fans = fans_search($_W['fans']['from_user'], $fansfields);
		}
		include $this->template($search['template'].'/research');
	}
}