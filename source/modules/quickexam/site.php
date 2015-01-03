<?php
/**
 * @author 更多模块请浏览bbs.birm.co
 */
defined('IN_IA') or exit('Access Denied');

class QuickExamModuleSite extends WeModuleSite {

	public function doWebChoice() {
		global $_W;
		global $_GPC; // 获取query string中的参数
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		
		if ($operation == 'post') {
			$choice_id = intval($_GPC['choice_id']);
			if (!empty($choice_id)) {
				$item = pdo_fetch("SELECT * FROM ".tablename('exam_choice')." WHERE choice_id = :id" , array(':id' => $choice_id));
				if (empty($item)) {
					message('抱歉，试题不存在或是已经删除！', '', 'error');
				}
			}
			if (checksubmit('submit')) {
				if (empty($_GPC['title'])) {
					message('请输入题干');
				}
				if (empty($_GPC['body'])) {
					message('请输入选项！');
				}
				if (empty($_GPC['answer'])) {
					message('请输入答案！');
				}
				$data = array(
					'weid' => $_W['weid'],
					'title' => $_GPC['title'],
					'body' => $_GPC['body'],
					'answer' => $_GPC['answer'],
					'explain' => $_GPC['explain'],
				);
				if (!empty($choice_id)) {
					pdo_update('exam_choice', $data, array('choice_id' => $choice_id));
				} else {
					pdo_insert('exam_choice', $data);
				}
				message('更新成功！', create_url('site/module/choice', array('name' => 'exam', 'op' => 'display')), 'success');
			}
		}
		else if ($operation == 'delete') { //删除酒店
			$choice_id = intval($_GPC['choice_id']);
			$row = pdo_fetch("SELECT choice_id FROM ".tablename('exam_choice')." WHERE choice_id = :choice_id", array(':choice_id' => $choice_id));
			if (empty($row)) {
				message('抱歉，酒店不存在或是已经被删除！');
			}
			pdo_delete('exam_choice', array('choice_id' => $choice_id));
			message('删除成功！', referer(), 'success');
		} else if ($operation == 'display') {
			$condition = '';
			$list = pdo_fetchall("SELECT * FROM ".tablename('exam_choice')." WHERE weid = '{$_W['weid']}' $condition ORDER BY choice_id DESC");
		}
		include $this->template('choice');
	}


	public function doMobileExam() {
		//checkauth();
		global $_W, $_GPC;
		$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
		if ($operation == 'post') {
			var_dump($_GPC['choice']);
			var_dump($_GPC['answer']);
		} else {
			$list = pdo_fetchall("SELECT * FROM ".tablename('exam_choice')." WHERE weid = '{$_W['weid']}'");
			foreach($list as &$list_item) {
				$options = explode("\n", $list_item['body']);
				foreach($options as $op) {
					$option = array();
					$option['body'] = $op;
					$option['seq'] = substr($op, 0, 1);
					$list_item['options'][] = $option;
				}
			}
		}
		include $this->template('choice');
	}
	
}
