<?php
/**
 * 
 *
 *
 */
defined('IN_IA') or exit('Access Denied');

class LxyykzsigninModuleSite extends WeModuleSite {
	private $replytable = 'lxy_ykz_signin_reply';
	private $sigintable = 'lxy_ykz_signin_record';
	private $wintable = 'lxy_ykz_signin_winner';
	
	public function doMobileRegister() {
		global $_GPC, $_W;
		if (!empty($_GPC['submit'])) {
			if (empty($_W['fans']['from_user'])) {
				message('非法访问，请重新发送消息进入签到页面！');			}

			$data = array(
				'nickname'=>$_GPC['nickname'],
				'mobile' => $_GPC['mobile'],
			);

			if(empty($_GPC['mobile']))
			{unset($data['mobile']);}			
			
			
			fans_update($_W['fans']['from_user'], $data);
			die('<script>location.href = "'.$this->createMobileUrl('success').'";</script>');
		}
		include $this->template('register');
	}
	//列出当前规则下的签到记录
	public function doWebList(){
		global $_GPC, $_W;
		checklogin();
		$rid = intval($_GPC['id']);

			$pindex = max(1, intval($_GPC['page']));
			$psize = 15;
			
			$sql = "SELECT a.rank,a.name,a.time,a.continuedays,a.sumdays,a.sumfirst,b.credit1 FROM " . tablename($this->sigintable) . " a left join ".tablename('fans')." b on a.from_user=b.from_user  WHERE a.rid={$rid}   order by a.time desc limit ". ($pindex - 1) * $psize . ',' . $psize;
		
			$signinlist = pdo_fetchall($sql);
			$total = pdo_fetchcolumn('SELECT count(1) as totle FROM '.tablename($this->sigintable).' WHERE rid= :rid order by `time` desc ', array(':rid' =>$rid) );
			$pager = pagination($total, $pindex, $psize);
			include $this->template('list');
	}
	
		public function doWebAwardlist(){
			global $_GPC, $_W;
				checklogin();
				$rid = intval($_GPC['id']);
		$pindex = max(1, intval($_GPC['page']));		
		$psize = 15;
		$signinlist = pdo_fetchall('SELECT a.*,a.id as winid,b.mobile FROM '.tablename($this->wintable).'a left join '.tablename('fans').' b on a.from_user=b.from_user  WHERE a.rid= :rid order by a.id desc LIMIT ' . ($pindex - 1) * $psize . ',' . $psize,array(':rid'=>$rid) );	
		$total = pdo_fetchcolumn('SELECT count(1) as totle FROM '.tablename($this->wintable).' WHERE rid= :rid order by `id` desc ', array(':rid' => $rid) );
		$pager = pagination($total, $pindex, $psize);
		include $this->template('awardlist');	
		}

		
		public function doWebSetout(){
			global $_GPC, $_W;
			checklogin();
			$id=intval($_GPC['id']);
			$rid=$_GPC['rid'];
			$item = pdo_fetch('SELECT * FROM '.tablename($this->wintable).'  WHERE   id=:id ',array(':id'=>$id) );
			if (checksubmit('submit')) {
				$data = array(
						'sendwcontinuedays'=>$_GPC['sendwcontinuedays']+intval($item['sendwcontinuedays']),
						'sendwsumdays'=>$_GPC['sendwsumdays']+intval($item['sendwsumdays']),
						'sendwsumfirst'=>$_GPC['sendwsumfirst']+intval($item['sendwsumfirst']),
						'wcontinuedays'=>$item['wcontinuedays']- intval($_GPC['sendwcontinuedays']),
						'wsumdays'=>$item['wsumdays']-$_GPC['sendwsumdays'],
						'wsumfirst'=>$item['wsumfirst']-$_GPC['sendwsumfirst'],
				);
				//上传图片
					if (empty($id))
				{
					message('发放异常！', '', 'error');
				}
				else
				{
					pdo_update($this->wintable, $data, array('id' => $id));
				}
				message('发放奖品成功！', $this->createWebUrl('awardlist', array('id'=>$rid)), 'success');
			}
			include $this->template('awardsent');
		}
		
	public function doMobileSuccess() {
		include $this->template('success');
	}

}
