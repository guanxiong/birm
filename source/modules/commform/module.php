<?php
/**
 * 通用表单模块定义
 *
 * @author Godietion Koo
 * @url http://beidoulbs.com
 */
defined('IN_IA') or exit('Access Denied');

class CommformModule extends WeModule {
	public function fieldsFormDisplay($rid = 0) {
		//要嵌入规则编辑页的自定义内容，这里 $rid 为对应的规则编号，新增时为 0
		global $_W;
		$list=pdo_fetchall("select * from ".tablename("defineform")." where weid=:weid order by id desc",array(':weid'=>$_W['weid']));
		$selected=pdo_fetch("select * from ".tablename("defineform")." where ruleid>0 and ruleid=:ruleid order by id desc",array(':ruleid'=>$rid));		
		include $this->template('setting');
	}

	public function fieldsFormValidate($rid = 0) {
		//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
		if(empty($_POST['formid'])){
			return '请选择已经存在的通用表单页面!如未创建，请先在配置页面完成后再试！';
		}else{
			return '';
		}
	}

	public function fieldsFormSubmit($rid) {
		//规则验证无误保存入库时执行，这里应该进行自定义字段的保存。这里 $rid 为对应的规则编号
		$row=pdo_fetch("select * from ".tablename('defineform')." where keyword=:keyword",array(':keyword'=>$_POST['keywords']));
		pdo_update('defineform',array('keyword' =>'','ruleid'=>''),array('id'=>$row['id']));
		pdo_update('defineform',array('keyword' => $_POST['keywords'],'ruleid'=>$rid),array('id'=>$_POST['formid']));
	}

	public function ruleDeleted($rid) {
		//删除规则时调用，这里 $rid 为对应的规则编号
	}

	public function settingsDisplay($settings) {
		//点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
		//在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
    	global $_GPC,$_W;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 5;
		$list = pdo_fetchall("SELECT * FROM ".tablename('defineform')." WHERE weid = '{$_W['weid']}' ORDER BY id ASC LIMIT ".($pindex - 1) * $psize.','.$psize);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('defineform') . " WHERE weid = '{$_W['weid']}' ");
		$pager = pagination($total, $pindex, $psize);		
		include $this->template('showforms');
	}

}