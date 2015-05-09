<?php
global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);
		$mgamblemoon = pdo_fetch("SELECT * FROM ".tablename('mgamblemoon_reply')." WHERE rid = '$id' LIMIT 1");
		$mgamblemoon['descriptions']=str_replace("\r","",$mgamblemoon['description']);
		$mgamblemoon['descriptions']=str_replace("\n","",$mgamblemoon['descriptions']);
		$showurl=1;
		if(!empty($fromuser)){
			$showurl=0;
			$sql="SELECT * FROM ".tablename('mgamblemoon_user')." WHERE  from_user = '$fromuser' AND rid = '$id' ";
			$myuser = pdo_fetch($sql);
			
		}
		if(empty($mgamblemoon['guzhuurl'])){
				$showurl=0;
		}
				
		$sql="SELECT * FROM ".tablename('mgamblemoon_user')." where from_user = '$fromuser' order by huodeid ASC limit 10";
		$allph=pdo_fetchall($sql);
		
		include $this->template('rank');