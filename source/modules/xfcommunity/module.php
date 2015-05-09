<?php
/**
 * community模块定义
 *
 * @author 
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class XfcommunityModule extends WeModule {
	public $comslide    	= 'community_slide';
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		return '';
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}

	public function settingsDisplay($settings) {
	
	global $_GPC, $_W;
	
	if(checksubmit()) {
	
		$cfg = array(
		'cname'   => $_GPC['cname'],
		'tel'     => $_GPC['tel'],
		'title'   => $_GPC['title'],
		'burl'    => $_GPC['burl'],
		'address' => $_GPC['address'],
		'nav_color' => $_GPC['nav_color'],
		'toggle_color' => $_GPC['toggle_color'],
		'bg_image' => $_GPC['bg_image'],
		'bg_color' => $_GPC['bg_color'],
	);
	//print_r($cfg);exit;
	$this->saveSettings($cfg);
	message('保存成功', 'refresh');
	}
	
	include $this->template('setting');
	
	}

}