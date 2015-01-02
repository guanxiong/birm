<?php
/**
 * 微拍模块处理程序
 *
 * @author 清逸
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class QyweipaiwebModuleProcessor extends WeModuleProcessor {
	public $tablename = 'qywpweb';
	public function respond() {
		$content = $this->message['content'];
		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码
		global $_W;
		$rid = $this->rule;
		$message = $this->message;
		$content = $message['content'];
		$from_user = $message['from'];
		$fans = fans_search($from_user);
		$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));	
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('qywpweb_reply')." WHERE  rid = '".$rid."' and create_time > '".strtotime(date('Y-m-d'))."' AND  fid = '".$fans['id']."'");
		if (empty($_SESSION['pwd'])) {
			$_SESSION['pwd']=$reply['pwd'];
		}
		if (empty($reply['status']) && $reply['status']<>1) {
			return $this->respText('活动还没启动呢！');
		}
		if (!empty($reply['maxnum']) && $total >= $reply['maxnum']) {
			$fuser = pdo_fetch("SELECT * FROM ".tablename('qywpweb_count')." WHERE rid = :rid AND fid = :fid order by id desc", array(':rid' => $rid,':fid' => $fans['id']));

			if(empty($fuser) || $fuser['count']<=0){
				return $this->respText('你本次活动的参与次数已用完！');
			}
			else{
				$_SESSION['ucount']=$fuser['count'];
				$_SESSION['uid']=$fuser['id'];
			}
		}

		if(!$this->inContext) {
			$this->beginContext(300);

			if(!empty($reply['pwd']) && ($reply['ispwd']==1)) {
				$_SESSION['img']='0';
				return $this->respText($reply['msg'].'请输入屏幕上的活动验证码！');
			}else{
				$_SESSION['img']='1';	
				return $this->respText($reply['msg'].'请选择一张照片上传(点对话框后面 + 号，选择图片)：');				
			}
		}else{
			if ( $content == '退出') {
				$this->endContext();
				session_destroy();
				return $this->respText('您已回到普通模式！');
			}

			if(!empty($reply['pwd']) && !empty($content) && ($content!=$_SESSION['pwd']) && empty($_SESSION['img'])){
				//$this->endContext();
				//session_destroy();
				return $this->respText('验证码不对哦，请核对屏幕上的验证码后输入正确验证码：');
			}else{
				if ($_SESSION['img']=='0') {
					$filenamep = 'qywp/' . $rid . '/pwd.txt';
					$pwd1=random(6,true);
					file_write($filenamep, 'lyqywp'.$pwd1);
					pdo_update($this->tablename,array('pwd'=>$pwd1),array('rid'=>$rid));
					$_SESSION['img']='1';	
					return $this->respText('请选择一张照片上传(点对话框后面 + 号，选择图片)：');				
				}
				if ($_SESSION['img']=='1') {
					if (($this->message['type'] == 'image') && empty($_SESSION['piccontent'])) {

						$image = ihttp_request($this->message['picurl']);
						$time = strtotime(now)*1000+random(3,true);  
						$filename = 'qywp/' . $rid . '/' . $time. '.jpg';
						file_write($filename, $image['content']);

						$_SESSION['piccontent'] = $filename;
						if ( $reply['lyok']==1){
							$_SESSION['img']='2';
							return $this->respText('上传照片成功！请输入你想留在照片上的话（10个字以内），输入 # 则放弃留言：');
						}else{
							$_SESSION['img']='3';
						}
					}else{
						return $this->respText('只能传照片哦！');
					}
				}
				if ($_SESSION['img']=='2') {
					if (($this->message['type'] != 'text')|| empty($content)){
						return $this->respText('只能输入文字：');
					}elseif(mb_strlen($content)>=33){
						return $this->respText('你输入的文字超长了吧，重新输入：');
					}else{
						if (($content=='#')||($content=='＃')) {
							$_SESSION['msg']='';		
						}else{	
							$_SESSION['msg']=$content;					
						}
						$_SESSION['img']='3';
					}
				}
				if ($_SESSION['img']=='3') {
					$filenamec = 'qywp/' . $rid . '/count.txt';
					$buffer='10000';
					$file_name = IA_ROOT . '/' . $GLOBALS['_W']['config']['upload']['attachdir'] . $filenamec;
					if (file_exists($file_name)) {
						 $fp=fopen($file_name,'r');
						 while(!feof($fp))
						 {
						  $buffer=fgets($fp);
						 }
						 fclose($fp);
					}else{
						file_write($filenamec, $buffer);			
					}
					$buffer++;
					file_write($filenamec, $buffer);	
					$filenamem = 'qywp/' . $rid . '/msg.html';
					$msghead='lyqywp<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
					$msgwrite='<wp><id>'.$buffer.'</id><purl>'.substr($_SESSION['piccontent'],-17).'</purl><msg>'.$_SESSION['msg'].'</msg></wp>';
					$file_name = IA_ROOT . '/' . $GLOBALS['_W']['config']['upload']['attachdir'] . $filenamem;
					if (file_exists($file_name)) {
						file_put_contents($file_name,$msgwrite, FILE_APPEND);
					}else{
						$msgwrite=$msghead.$msgwrite;
						file_write($filenamem, $msgwrite);			
					}
					$insert = array(
						'rid' => $rid,
						'fid' => $fans['id'],
						'weid' => $_W['weid'],
						'msg' => $_SESSION['msg'],
						'pic' => $_SESSION['piccontent'],
						'bianhao' => $buffer,
						'create_time' => time()
					);

					if($id=pdo_insert('qywpweb_reply', $insert)){
						$data = array(
							'count' => $_SESSION['ucount']-1,
						);
						pdo_update('qywpweb_count',$data,array('id'=>$_SESSION['uid']));
						$this->endContext();
						session_destroy();
						return $this->respText($reply['msg_succ'].' 你的照片编号为'.$buffer);
					}else{
						$this->endContext();
						session_destroy();
						return $this->respText($reply['msg_fail']);
					}
				}
				$this->endContext();
				session_destroy();
				return $this->respText('操作异常哦，重来吧！');
			}	
		}

	}

}