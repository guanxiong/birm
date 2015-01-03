<?php

/**

 * 留言板模块处理程序

 *

 * @author daduing

 * @url http://www.we7.cc

 */

defined('IN_IA') or exit('Access Denied');



class MsgModuleProcessor extends WeModuleProcessor {

	public $tablename = 'msg';

	public function respond() {

		//这里定义此模块进行消息处理时的具体过程, 请查看微新星文档来编写你的代码

		global $_W;

		$rid = $this->rule;

		$message = $this->message;

		$content = $message['content'];

		$from_user = $message['from'];

		$fans = fans_search($from_user);

		$reply = pdo_fetch("SELECT * FROM ".tablename($this->tablename)." WHERE rid = :rid ORDER BY `id` DESC", array(':rid' => $rid));	

		if(!$this->inContext) {

			$this->beginContext(60);

			return $this->respText($reply['msg']);

		}

		if($content == '0'){

			$this->endContext();

			session_destroy();

			return $this->respText($reply['msg_fail']);

		}

		if($content == '1'){

			$msg = pdo_fetchall("SELECT * FROM ".tablename('msg_reply')." WHERE `rid` = :rid AND `fid` = :fid ORDER BY `id` DESC", array(':rid' => $rid,':fid'=>$fans['id']));

			if(empty($msg)){

				$this->endContext();

				session_destroy();

				return $this->respText('暂无留言……');

			}

			$i = 1;

			foreach($msg as $value){

				$reply_txt = $reply_txt.$i++.'、'.$value['msg']."\t".date('m-d',$value['create_time'])."\n";

			}

			$this->endContext();

			session_destroy();

			return $this->respText($reply_txt);

		}

		$insert = array(

			'rid' => $rid,

			'fid' => $fans['id'],

			'weid' => $_W['weid'],

			'msg' => $content,

			'create_time' => time()

		);

		if($id=pdo_insert('msg_reply', $insert)){

			$this->endContext();

			session_destroy();

			return $this->respText($reply['msg_succ']);

		}else{

			return $this->respText($reply['msg_fail']);

		}



	}

}