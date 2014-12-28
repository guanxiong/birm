<?php
/**
 * 账号绑定模块处理程序
 *
 * @author 冯齐跃
 * @url http://t.qq.com/fengqiyue
 */
defined('IN_IA') or exit('Access Denied');

class BindingModuleProcessor extends WeModuleProcessor {
	public function respond() {
		$content = $this->message['content'];
		if($this->inContext){			
			if(!preg_match('/^1[3458][0-9]{9}$/',$content)){
				return $this->respText("请输入正确的手机号码！");
			}
			pdo_update('fans', array('mobile'=>$content), array('from_user' => $this->message['fromusername']));
			$this->endContext();	// 结束上下文
			return $this->respText("绑定成功！");
		}
		else{
			$sql = "SELECT mobile FROM ".tablename('fans')." WHERE `from_user`=:from_user";
			$fans = pdo_fetch($sql, array(':from_user' => $this->message['fromusername']));
			if($fans['mobile']){
				return $this->respText("你的手机号码已经经绑定！");
			}
			$this->beginContext();//开启上下文
			return $this->respText("请输入你的手机号码！");
		}
	}
}