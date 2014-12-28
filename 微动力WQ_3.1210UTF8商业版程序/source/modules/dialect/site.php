<?php
/**
 * 地方话模块定义
 *
 * @author 冯齐跃
 * @url http://wx.admin9.com/
 */
defined('IN_IA') or exit('Access Denied');
define('APP_PUBLIC', './source/modules/dialect/');
class DialectModuleSite extends WeModuleSite {
    public $name = 'dialect';
    public $title = '地方话';
    public $dialect = 'feng_dialect';

    public function getHomeTiles() {
        global $_W;
        $urls = array();
        $list = pdo_fetchall("SELECT * FROM " . tablename($this->dialect) . " WHERE weid = '{$_W['weid']}'");
        if (!empty($list)) {
            foreach ($list as $row) {
                $urls[] = array(
                	'title' => $row['title'], 
                	'url' => $this->createMobileUrl('detail', array('id' => $row['id']))
                );
            }
        }
        return $urls;
    }

	public function doWebManage() {
		global $_W,$_GPC;
		$pindex = max(1, intval($_GPC['page']));
		$psize = 20;
		$condition = "weid = '{$_W['weid']}'";
		if (!empty($_GPC['keywords'])) {
			$condition .= " AND (title LIKE '%{$_GPC['keywords']}%'";
		}
		$sql="SELECT * FROM ".tablename($this->dialect)." WHERE $condition ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize;
		$list = pdo_fetchall($sql);
		$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename($this->dialect) . " WHERE $condition");
		$pager = pagination($total, $pindex, $psize);
		include $this->template('manage');
	}

	// 添加
	public function doWebpost(){
		global $_W,$_GPC;
		$id = (int) $_GPC['id'];
		if($id){
			$subject = pdo_fetch("SELECT dialect,title,photo,smalltext,share_title,share_desc,share_cancel,share_url FROM ".tablename($this->dialect)." WHERE id={$id} ORDER BY id DESC LIMIT 1");
		}
		else{
			$subject['share_cancel'] = '别这样的啦，好东西要和朋友分享的嘛！分享后我告诉你一个秘密。';
		}
		// 删除
		if($_GPC['op']=='delete'){
			$id = intval($_GPC['id']);
			$row = pdo_fetch("SELECT id FROM ".tablename($this->dialect)." WHERE id = :id", array(':id' => $id));
			if (empty($row)) {
				message('抱歉，信息不存在或是已经被删除！');
			}
			pdo_delete($this->dialect, array('id' => $id));
			message('删除成功！', referer(), 'success');
		}
		if (checksubmit()) {
			$data = array(
				'dialect' => $_GPC['dialect'],
				'title' => $_GPC['title'],
				'smalltext' => $_GPC['smalltext'],
				'share_title' => $_GPC['share_title'],
				'share_desc' => $_GPC['share_desc'],
				'share_cancel' => $_GPC['share_cancel'],
				'share_url' => $_GPC['share_url']
			);
	        if (!empty($_GPC['photo'])) {
	            $data['photo'] = $_GPC['photo'];
	            file_delete($_GPC['photo-old']);
	        }
            if (!empty($id)) {
                pdo_update($this->dialect, $data, array('id' => $id));
            }
            else {
            	$data['weid'] = $_W['weid'];
                pdo_insert($this->dialect, $data);
                $id = pdo_insertid();
            }
            message('更新成功，前往设置题库', $this->createWebUrl('question',array('id'=>$id)));
		}

		include $this->template('post');
	}

	// 题库
	public function doWebquestion(){
		global $_W,$_GPC;
		$id = intval($_GPC['id']);
		if(!$id){
			message('参数错误', 'refresh', 'error');
		}

		// 添加
		if (checksubmit()) {
			$questions = $_GPC['question'];
			$answers = $_GPC['answers'];
			$score = $_GPC['score'];
			$question=array();
			foreach ($questions as $key => $val) {
				$aaa=trim($answers[$key]);
				$sss=trim($score[$key]);
				if(empty($aaa) || empty($sss)){
					message('请填写完整所有对应的数据', 'refresh', 'error');
				}
				$question[]=array(
					'question'=>$val,
					'answers'=>explode("\r\n", $aaa),
					'score'=>explode("\r\n", $sss)
				);
			}
			$add=array();
			$add['titlenum'] = count($question);
			$add['questions'] = iserializer($question);
			pdo_update($this->dialect, $add, array('id'=>$id));
			$refresh = $this->createWebUrl('scoretxt',array('id'=>$id));
			message('题库更新成功，前往设置评分规则！', $this->createWebUrl('scoretxt',array('id'=>$id)));
		}
		$sql="SELECT questions FROM ".tablename($this->dialect)." WHERE id=$id ORDER BY id DESC LIMIT 1";
		$question = pdo_fetchcolumn($sql);
		$list = iunserializer($question);
		if(is_array($list) && !empty($list)){
			foreach ($list as &$value) {
				$value['answers'] = implode("\r\n", $value['answers']);
				$value['score'] = implode("\r\n", $value['score']);
			}
		}
		include $this->template('questions');
	}

	// 评分管理
	public function doWebscoretxt(){
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		if(!$id){
			message('参数错误', 'refresh', 'error');
		}

		if (checksubmit()) {
			$add['scoretext'] = iserializer($_GPC['add']);
			pdo_update($this->dialect, $add, array('id'=>$id));
			message('规则更新成功', $this->createWebUrl('manage'));
		}
		$sql="SELECT scoretext FROM ".tablename($this->dialect)." WHERE id=$id ORDER BY id DESC LIMIT 1";
		$scoretxt = pdo_fetchcolumn($sql);
		$list = array();
		if($scoretxt){
			$list = iunserializer($scoretxt);
		}
		include $this->template('scoretext');
	}

	public function doWebQuery() {
		global $_W, $_GPC;
		$kwd = $_GPC['keyword'];
		$sql = 'SELECT id,title,photo FROM ' . tablename($this->dialect) . ' WHERE `weid`=:weid AND `title` LIKE :title';
		$params = array();
		$params[':weid'] = $_W['weid'];
		$params[':title'] = "%{$kwd}%";
		$ds = pdo_fetchall($sql, $params);
		foreach($ds as &$row) {
			$r = array();
			$r['title'] = $row['title'];
			$r['description'] = '';
			$r['thumb'] = $row['photo'];
			$r['mid'] = $row['id'];
			$row['entry'] = $r;
		}
		include $this->template('query');
	}

	public function doMobiledetail(){
		global $_W, $_GPC;
		$id = intval($_GPC['id']);
		if(empty($id)){
			message('参数错误', 'refresh', 'error');
		}

		if($_W['isajax']){
			if($_GPC['op']=='share'){
				pdo_query("UPDATE ".tablename($this->dialect)." SET share_num=share_num+1 WHERE id=".$id);
			}
			else{
				pdo_query("UPDATE ".tablename($this->dialect)." SET people=people+1 WHERE id=".$id);
			}
		}
		// 访问人数
		pdo_query("UPDATE ".tablename($this->dialect)." SET viewnum=viewnum+1 WHERE id=".$id);

		$r = pdo_fetch("SELECT * FROM ".tablename($this->dialect)." WHERE id={$id} ORDER BY id DESC LIMIT 1");
		// 问题
		if($r['questions']){
			$questions = iunserializer($r['questions']);
			unset($r['questions']);
		}
		// 评分
		if($r['scoretext']){
			$scoretext = iunserializer($r['scoretext']);
			unset($r['scoretext']);
		}
		$setting = $this->module['config'];
		$setting['thumb'] = toimage($setting['thumb']);
		include $this->template('detail');
	}
}
