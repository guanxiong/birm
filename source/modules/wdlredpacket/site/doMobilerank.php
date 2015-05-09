<?php
global $_GPC, $_W;
		$fromuser = $_W['fans']['from_user'];
		$id = intval($_GPC['id']);
		$wdlredpacket = pdo_fetch("SELECT * FROM ".tablename('wdlredpacket_reply')." WHERE rid = '$id' LIMIT 1");
		$wdlredpacket['descriptions']=str_replace("\r","",$wdlredpacket['description']);
		$wdlredpacket['descriptions']=str_replace("\n","",$wdlredpacket['descriptions']);
		$showurl=1;
		if(!empty($fromuser)){
			$showurl=0;
			$sql="SELECT * FROM ".tablename('wdlredpacket_user')." WHERE  from_user = '$fromuser' AND rid = '$id' ";
			$myuser = pdo_fetch($sql);
			
		}
		if(empty($wdlredpacket['guzhuurl'])){
				$showurl=0;
		}
				
		$sql="SELECT * FROM ".tablename('wdlredpacket_user')." where from_user = '$fromuser' order by huodeid ASC limit 10";
		$allph=pdo_fetchall($sql);
		
		include $this->template('rank');