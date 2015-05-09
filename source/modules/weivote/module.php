<?php
/**
 * 模块定义
 *
 * @author 回忆Kiss
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class WeivoteModule extends WeModule {
    
	public $tablename = 'weivote_setting';    
    
    /**
	 * 可能需要实现的操作, 需要附加至规则表单的字段内容, 编辑规则时如果模块类型为当前模块, 则调用此方法将返回内容附加至规则表单之后
	 * @param int $rid 如果操作为更新规则, 则此参数传递为规则编号, 如果为新建此参数为 0
	 * @return string 要附加的内容(html格式)
	 */
	public function fieldsFormDisplay($rid = 0) {//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
        global $_W;
        
        if (!empty($rid)) {	
            $setting = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));
			$option_decode = pdo_fetchall("SELECT * FROM ".tablename('weivote_option')." WHERE rid = :rid ORDER BY `id` ASC", array(':rid' => $rid));
			$option = array();

			//echo "<xmp>";
			foreach ($option_decode as $op) {
				$op['description'] = htmlspecialchars_decode($op['description']);
				array_push($option, $op);

				//echo $op['description'];
			}
			//echo "</xmp>";exit;
			
		} else {
            
            $setting = array(
				'max_vote_day' => 1,
				'max_vote_count' => 1,
				'state' => 1,
				'type_vote' => 1,
				'name_state' => 1,
                'start_time' => TIMESTAMP,
                'end_time' => TIMESTAMP + 86399*7,
			);
            
        }
        
		include $this->template('weivote/form');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
        global $_GPC, $_W;
        //echo "start_time :".strtotime($_GPC['start_time']).' - '.$_GPC['start_time'];
        //echo "end_time :".strtotime($_GPC['end_time']).' - '.$_GPC['end_time'];
        //exit;
        
        $id = intval($_GPC['setting_id']);
        $insert = array(
			'rid' => $rid,
			'title' => $_GPC['title'],
			'picture' => $_GPC['picture'],
			'description' => $_GPC['description'],
			'max_vote_day' => intval($_GPC['max_vote_day']),
			'max_vote_count' => intval($_GPC['max_vote_count']),
			'type_vote' => intval($_GPC['type_vote']),
			'name_state' => intval($_GPC['name_state']),
			'state' => intval($_GPC['state']),
			'rule' => htmlspecialchars_decode($_GPC['rule']),
			'default_tips' => $_GPC['default_tips'],
			'start_time' => empty($_GPC['start_time']) ? TIMESTAMP : strtotime($_GPC['start_time']),
			'end_time' => empty($_GPC['end_time']) ? TIMESTAMP + 86399*7 : strtotime($_GPC['end_time']),
		);
        if (empty($id)) {
			pdo_insert($this->tablename, $insert);
		} else {
			if (!empty($_GPC['picture'])) {
				file_delete($_GPC['picture-old']);
			} else {
				unset($insert['picture']);
			}
			pdo_update($this->tablename, $insert, array('id' => $id));
		}
        
        if (!empty($_GPC['option-title'])) {
			foreach ($_GPC['option-title'] as $index => $title) {
				if (empty($title)) {
					continue;
				}
				$description = htmlspecialchars_decode($_GPC['option-description'][$index]);
				$update = array(
					'title' => $title,
					'description' => $description,
					'picture' => $_GPC['option-picture'][$index],
			        'state' => intval($_GPC['state']),
					
				);
				
				pdo_update('weivote_option', $update, array('id' => $index));
			}
		}
        
        //处理添加
		if (!empty($_GPC['option-title-new'])) {
			foreach ($_GPC['option-title-new'] as $index => $title) {
				if (empty($title)) {
					continue;
				}

				$description = htmlspecialchars_decode($_GPC['option-description-new'][$index]);
				$insert = array(
					'rid' => $rid,
					'title' => $title,
					'description' => $description,
					'picture' => $_GPC['option-picture-new'][$index],
			        'state' => intval($_GPC['state']),
					
				);

				
				pdo_insert('weivote_option', $insert);
			}
		}
        
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}

    
    public function doResult() {
        global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete('weivote_log', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('index/module', array('do' => 'result', 'name' => 'weivote', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$where = '';
		//$start_time = !empty($_GPC['start']) ? strtotime($_GPC['start']) : TIMESTAMP;
		//$end_time = !empty($_GPC['start']) ? strtotime($_GPC['end']) : TIMESTAMP;
		//if (!empty($start_time) && $start_time == $end_time) {
		//	$end_time = $end_time + 86400 - 1;
		//}
//		$condition = array(
//			'isregister' => array(
//				'',
//				" AND b.realname <> ''",
//				" AND b.realname = ''",
//			),
//			'isoption' => array(
//				'',
//				" AND a.options <> ''",
//				" AND a.options = ''",
//			),
//			'qq' => " AND b.qq ='{$_GPC['profilevalue']}'",
//			'mobile' => " AND b.mobile ='{$_GPC['profilevalue']}'",
//			'realname' => " AND b.realname ='{$_GPC['profilevalue']}'",
//			'title' => " AND a.options = '{$_GPC['optionvalue']}'",
//			'starttime' => " AND a.createtime >= '$start_time'",
//			'endtime' => " AND a.createtime <= '$end_time'",
//		);
//		if (!isset($_GPC['isregister'])) {
//			$_GPC['isregister'] = 1;
//		}
//		$where .= $condition['isregister'][$_GPC['isregister']];
//		if (!isset($_GPC['isoption'])) {
//			$_GPC['isoption'] = 1;
//		}
//		$where .= $condition['isoption'][$_GPC['isoption']];
//		if (!empty($_GPC['profile'])) {
//			$where .= $condition[$_GPC['profile']];
//		}
//		if (!empty($_GPC['option'])) {
//			$where .= $condition[$_GPC['option']];
//		}
//		if (!empty($start_time)) {
//			$where .= $condition['start_time'];
//		}
//		if (!empty($end_time)) {
//			$where .= $condition['end_time'];
//		}
		//$sql = "SELECT a.id, a.options, a.state, a.createtime, b.realname, b.mobile, b.qq FROM ".tablename('weivote_log')." AS a
		//		LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' AND a.options <> '' $where ORDER BY a.createtime DESC, a.state ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$sql = "SELECT * FROM ".tablename('weivote_log')." WHERE rid = '$id' AND options <> '' ORDER BY createtime DESC, state ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
        $list = pdo_fetchall($sql);
		if (!empty($list)) {
			//$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." AS a
			//	LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' $where");
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE from_user != '' AND rid = '$id'");
			$pager = pagination($total, $pindex, $psize);
		}
        
        
        //读取统计数据
        
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id'");
		
        $sql = "SELECT * FROM ".tablename('weivote_option')." WHERE rid = '$id'";
		$weivote_options = pdo_fetchall($sql);
        
        
        $options_count = pdo_fetchcolumn("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        
        
        $options=array();
        
        foreach ($weivote_options as $weivote_option)
        {
            
            $weivote_option_id = $weivote_option['id'];
            $options_one_total = pdo_fetchcolumn("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id' AND oid = '$weivote_option_id'");
            
            $options_obj = array(
                'title' => $weivote_option['title'],
                'total' => $options_one_total,
                'proportion' => intval(doubleval($options_one_total)/$options_count*10000)/100,
                'picture' => $weivote_option['picture'],
                'id' => $weivote_option['id'],
            );
            //echo $weivote_option['id'].'<br>';
            
            //echo $options_count.' - '.$options_one_total.' - '.$weivote_option_id.' -- '.$options_obj['title'].' -- '.$options_obj['total'].' -- '.$options_obj['proportion'].'<br>';
            
            
            array_push($options,$options_obj);
        }
        
        
                       
        if (count($options) > 0) {
        	$options = $this->doSort($options, 'total');
        }
        
        
		include $this->template('weivote/result');
	}
    
    public function doLog() {
        global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete('weivote_log', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('index/module', array('do' => 'result', 'name' => 'weivote', 'id' => $id, 'page' => $_GPC['page'])));
		}
		$pindex = max(1, intval($_GPC['page']));
		$psize = 50;
		$where = '';
		$start_time = !empty($_GPC['start']) ? strtotime($_GPC['start']) : TIMESTAMP;
		$end_time = !empty($_GPC['start']) ? strtotime($_GPC['end']) : TIMESTAMP;
		if (!empty($start_time) && $start_time == $end_time) {
			$end_time = $end_time + 86400 - 1;
		}
//		$condition = array(
//			'isregister' => array(
//				'',
//				" AND b.realname <> ''",
//				" AND b.realname = ''",
//			),
//			'isoption' => array(
//				'',
//				" AND a.options <> ''",
//				" AND a.options = ''",
//			),
//			'qq' => " AND b.qq ='{$_GPC['profilevalue']}'",
//			'mobile' => " AND b.mobile ='{$_GPC['profilevalue']}'",
//			'realname' => " AND b.realname ='{$_GPC['profilevalue']}'",
//			'title' => " AND a.options = '{$_GPC['optionvalue']}'",
//			'starttime' => " AND a.createtime >= '$start_time'",
//			'endtime' => " AND a.createtime <= '$end_time'",
//		);
//		if (!isset($_GPC['isregister'])) {
//			$_GPC['isregister'] = 1;
//		}
//		$where .= $condition['isregister'][$_GPC['isregister']];
//		if (!isset($_GPC['isoption'])) {
//			$_GPC['isoption'] = 1;
//		}
//		$where .= $condition['isoption'][$_GPC['isoption']];
//		if (!empty($_GPC['profile'])) {
//			$where .= $condition[$_GPC['profile']];
//		}
//		if (!empty($_GPC['option'])) {
//			$where .= $condition[$_GPC['option']];
//		}
//		if (!empty($start_time)) {
//			$where .= $condition['start_time'];
//		}
//		if (!empty($end_time)) {
//			$where .= $condition['end_time'];
//		}
		//$sql = "SELECT a.id, a.options, a.state, a.createtime, b.realname, b.mobile, b.qq FROM ".tablename('weivote_log')." AS a
		//		LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' AND a.options <> '' $where ORDER BY a.createtime DESC, a.state ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
		$sql = "SELECT * FROM ".tablename('weivote_log')." WHERE rid = '$id' AND options <> '' ORDER BY createtime DESC, state ASC LIMIT ".($pindex - 1) * $psize.",{$psize}";
        $list = pdo_fetchall($sql);
		if (!empty($list)) {
			//$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." AS a
			//	LEFT JOIN ".tablename('fans')." AS b ON a.from_user = b.from_user WHERE a.rid = '$id' $where");
			$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('weivote_log')." WHERE from_user != '' AND rid = '$id'");
			$pager = pagination($total, $pindex, $psize);
		}
        
        
        //读取统计数据
        
        $weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id'");
		
        $sql = "SELECT * FROM ".tablename('weivote_option')." WHERE rid = '$id'";
		$weivote_options = pdo_fetchall($sql);
        
        $options_count = pdo_fetchcolumn("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        $voter_count = pdo_fetchcolumn("SELECT count(distinct from_user) as total FROM ".tablename('weivote_log')." WHERE rid = '$id'");
        
        $options=array();
        
        foreach ($weivote_options as $weivote_option)
        {
            
            $weivote_option_id = $weivote_option['id'];
            $options_one_total = pdo_fetchcolumn("SELECT count(*) as total FROM ".tablename('weivote_log')." WHERE rid = '$id' AND oid = '$weivote_option_id'");
            
            $options_obj = array(
                'title' => $weivote_option['title'],
                'total' => $options_one_total,
                'proportion' => intval(doubleval($options_one_total)/$options_count*10000)/100,
                'picture' => $weivote_option['picture'],
                'id' => $weivote_option['id'],
            );
            //echo $weivote_option['id'].'<br>';
            
            //echo $options_count.' - '.$options_one_total.' - '.$weivote_option_id.' -- '.$options_obj['title'].' -- '.$options_obj['total'].' -- '.$options_obj['proportion'].'<br>';
            
            
            array_push($options,$options_obj);
        }
        
		include $this->template('weivote/log');
	}
    
    public function doDelete() {
        global $_W,$_GPC;
		$id = intval($_GPC['id']);
		$sql = "SELECT id FROM " . tablename('weivote_option') . " WHERE `id`=:id";
		$row = pdo_fetch($sql, array(':id'=>$id));
		if (empty($row)) {
			message('抱歉，选项不存在或是已经被删除！', '', 'error');
		}
		if (pdo_delete('weivote_option', array('id' => $id))) {
			message('删除选项成功', '', 'success');
		}
	}

	//冒泡排序
	private function doSort($array, $sortField){  

		$count = count($array);   
		if ($count <= 0) return false;   
		
		for($i=0; $i<$count; $i++){   
			for($j=$count-1; $j>$i; $j--){ 

				if ($array[$j][$sortField] > $array[$j-1][$sortField]){   
					$tmp = $array[$j];   
					$array[$j] = $array[$j-1];   
					$array[$j-1] = $tmp;   
				}
				
			}   
		}

		return $array;   
	}   

	public function doExport() {
		error_reporting(E_ALL);
		ini_set('display_errors', TRUE);
		ini_set('display_startup_errors', TRUE);
		date_default_timezone_set('Europe/London');

		if (PHP_SAPI == 'cli')
			die('This example should only be run from a Web Browser');

		/** Include PHPExcel */
		require_once dirname(__FILE__) . '/template/mobile/classes/PHPExcel.php';


		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
									 ->setLastModifiedBy("Maarten Balliauw")
									 ->setTitle("Office 2007 XLSX Test Document")
									 ->setSubject("Office 2007 XLSX Test Document")
									 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
									 ->setKeywords("office 2007 openxml php")
									 ->setCategory("Test result file");




        
        global $_GPC, $_W;
		checklogin();
		$id = intval($_GPC['id']);
		if (checksubmit('delete')) {
			pdo_delete('weivote_log', " id  IN  ('".implode("','", $_GPC['select'])."')");
			message('删除成功！', create_url('index/module', array('do' => 'result', 'name' => 'weivote', 'id' => $id, 'page' => $_GPC['page'])));
		}
		
		$where = '';
		$start_time = !empty($_GPC['start']) ? strtotime($_GPC['start']) : TIMESTAMP;
		$end_time = !empty($_GPC['start']) ? strtotime($_GPC['end']) : TIMESTAMP;
		if (!empty($start_time) && $start_time == $end_time) {
			$end_time = $end_time + 86400 - 1;
		}
		$sql = "SELECT * FROM ".tablename('weivote_log')." WHERE rid = '$id' AND options <> '' ORDER BY createtime DESC, state ASC LIMIT 5000";//待优化
        $list = pdo_fetchall($sql);
		
		$weivote_setting = pdo_fetch("SELECT * FROM ".tablename('weivote_setting')." WHERE rid = '$id'");
		

		if (!empty($list)) {

			$objPHPExcel->setActiveSheetIndex(0)
			            ->setCellValue('A1','编号')
			            ->setCellValue('B1','微信fakeid')
			            ->setCellValue('C1','姓名')
			            ->setCellValue('D1','手机')
			            ->setCellValue('E1','QQ')
			            ->setCellValue('F1','客户端IP')
			            ->setCellValue('G1','选项编号')
			            ->setCellValue('H1','选项')
			            ->setCellValue('I1','投票时间');
			$i=2;
			foreach($list as $k=>$v){
				$objPHPExcel->setActiveSheetIndex(0)
				            ->setCellValue('A'.$i,$v['id'])
				            ->setCellValue('B'.$i,$v['from_user'])
				            ->setCellValue('C'.$i,$v['realname'])
				            ->setCellValue('D'.$i,$v['mobile'])
				            ->setCellValue('E'.$i,$v['qq'])
				            ->setCellValue('F'.$i,$v['clientip'])
				            ->setCellValue('G'.$i,$v['oid'])
				            ->setCellValue('H'.$i,$v['options'])
				            ->setCellValue('I'.$i,date('Y-m-d H:i:s', $v['createtime']));
				$i++;
			}

		}
		// Rename worksheet
		$objPHPExcel->getActiveSheet()->setTitle($weivote_setting['title'].'投票结果');
		// 设置自动宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true); 
		$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true); 






		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);


		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$weivote_setting['title'].'投票结果'.'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

}