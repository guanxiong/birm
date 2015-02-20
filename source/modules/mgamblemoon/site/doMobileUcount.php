<?php

global $_GPC, $_W;
		
		$effective= true ;
		$useragent = addslashes($_SERVER['HTTP_USER_AGENT']);
		if(strpos($useragent, 'MicroMessenger') === false && strpos($useragent, 'Windows Phone') === false ){
			$effective = false ;
		}
		
		$id = intval($_GPC['id']);
		$uid = intval($_GPC['uid']);
		if (!$uid) {
			$effective = false ;
		}
		$url=$this->createMobileUrl('rank', array('id' => $id));
		$replay = pdo_fetch("SELECT * FROM ".tablename('mgamblemoon_reply')." WHERE rid = '{$id}' LIMIT 1");
		$user = pdo_fetch("SELECT * FROM ".tablename('mgamblemoon_user')." WHERE id = '{$uid}' and rid = '{$id}'  LIMIT 1");
		
		if($uid && $effective){
			//cookies不存在
			if(!isset($_COOKIE["hlmgamblemoon"])){ 
				
				setcookie('hlmgamblemoon',1,time()+86400);
				$data = array(
					'count' => $user['count'] +1,
					'friendcount'=> $user['friendcount'] +1,
				);
				pdo_update('mgamblemoon_user', $data,array('id' => $uid));	
			}
			
		}
		
		if(!empty($replay['guzhuurl'])){
			$url=$replay['guzhuurl'];
		}
		
		die('<script>location.href = "'.$url.'";</script>');