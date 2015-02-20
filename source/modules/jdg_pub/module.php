<?php
/**
 * 微PUB模块定义
 *
 * @author on3
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Jdg_pubModule extends WeModule {
	public $table = "idg_pub_wineadmin";

	public function settingsDisplay($settings) {
		//点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
		//在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
		global $_W,$_GPC;
		$config = $this->module['config'];
		$config['ischeck'] = $config['ischeck']?$config['ischeck']:0;
		if(checksubmit()) {
			//字段验证, 并获得正确的数据$dat
			$data = $_GPC['ischeck'];
			if($this->saveSettings($data)) {
				message('参数设置成功', 'refresh');
			}else{
				message('参数设置错误','refresh');
			}
		}
		//这里来展示设置项表单
		include $this->template('settings');
	}
	
	public function doWineAdmin(){
		global $_W,$_GPC;
		$weid = $_W["weid"];
		$res = pdo_fetchall("select id,snid,name,FROM_UNIXTIME(creattime)creattime,(CASE  status when 0 then '暂未存酒' else '已经存酒'end)status from ims_jdg_pub_wineadmin where 1=1 and weid={$weid}");
		$select = pdo_fetch("select id,content from ims_jdg_pub_rule where 1=1 and weid={$weid}");
		//print_r($res) ;
		include $this->template("wineadmin");
	}
	
	public function doselect(){
		global $_GPC;
		$snid = $_GPC['snid'];
		$result = pdo_fetchall("select id,snid,name,(CASE type when 1 then '红酒' when 2 then '香槟' when 3 then '洋酒'  else '啤酒'end)type,winename,winenumber,winenum,FROM_UNIXTIME(creattime)creattime,FROM_UNIXTIME(endtime)endtime,(case status when 0 then '未取' else '已取'end)status from ims_jdg_pub_winelog where 1=1 and snid={$snid}");
		//print_r($result);
		include $this->template("select");
	}
	
	public function doupdata(){
		global $_W,$_GPC;
		$name = $_GPC['name'];
		$snid = $_GPC['snid'];
		$arr = array('name'=>$name,"snid"=>$snid);
		
		$result=pdo_fetch("select snid from ims_jdg_pub_winelog where 1=1 and snid = {$snid}");
		if($result==""){
			include $this->template("sub");
		}else{
		$res = pdo_query("select * from ims_jdg_pub_wineadmin");
		
			include $this->template("sub");
		}
		
	}
	
	public  function dodelete(){
			global $_GPC;
			$snid = $_GPC['snid'];
			$status = pdo_query("delete from ims_jdg_pub_wineadmin where 1=1 and snid = {$snid}");
			if($status){
				$logstatus= pdo_query("delete from ims_jdg_pub_winelog where 1=1 and snid = {$snid}");
				if($logstatus){
				$url = $this->createWebUrl('wineadmin');  
				echo "<script language='javascript' type='text/javascript'>alert('执行成功,点击确定跳转!');";  
				echo "window.location.href='$url'";  
				echo "</script>"; 
				}else{
				echo "<script language='javascript' type='text/javascript'>alert('删除该用户名下存取记录操作失败，可能是该用户没有存酒!');"; 
				echo "</script>"; 
				}
				
			}else{
				echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，操作失败，请重新尝试!');"; 
				echo "</script>"; 
			}
		
	}
	
	public function doAddwine(){
		global $_W,$_GPC;
		$arr_num = count($_GPC['winename']);
		$type = $_GPC["type"];
		$winename= $_GPC['winename'];
		$winenumber = $_GPC['winenumber'];
		$winenum = $_GPC['winenum'];
		$snid = $_GPC['snid'];
		
		$name = $_GPC['name'];
		$time = time();
		//by 瞻园
		$tongzhi = pdo_fetchcolumn("select count(*) from information_schema.tables where table_name = 'ims_jcard_announce'");
		 $jdg_pub =   pdo_fetch("select * from ".tablename('jdg_pub')." where  weid= {$_W['weid']}");
		  $wineadmin =   pdo_fetch("select * from ".tablename('jdg_pub_wineadmin')." where  snid= {$snid}");
		
		for($i=0;$i<$arr_num;$i++){
		$status = pdo_query("insert into ims_jdg_pub_winelog(snid,winename,winenumber,winenum,creattime,type) values({$snid},'".$winename[$i]."','".$winenumber[$i]."','".$winenum[$i]."',{$time},'".$type[$i]."')");
				if($tongzhi!=-1){            
                           $data_announce = array(
                        'weid' => $_W['weid'],                      
                        'from_user' => $wineadmin['fansid'],
                        'type' => 10,
                        'title' => $jdg_pub['pub_name']."存酒",
						'updatetime' => time(),
						'dateline' => time(),
					);
						$data_announce['content'] = "尊敬的用户存酒码是{$snid}，您在".$jdg_pub['pub_name']."存放".$winenumber[$i]."瓶".$winename[$i]."存放的柜号是".$winenum[$i];
     
					}
			 pdo_insert('jcard_announce', $data_announce);
		}

		if($status===false){
				
		echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，操作失败，请重新尝试!');"; 
		echo "</script>"; 
			}else{
		$sql = pdo_query("update ims_jdg_pub_wineadmin set status = 1 where 1=1 and snid=".$snid."");
		
		if($sql){
			echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，可能操作失败，请重新尝试!');"; 
			echo "</script>";
		
		}else{
		$url = $this->createWebUrl('wineadmin');  
		echo "<script language='javascript' type='text/javascript'>alert('执行成功,点击确定跳转!');";  
		echo "window.location.href='$url'";  
		echo "</script>"; 
			
		}
	}
}


	public function doupdatabj(){
		global $_W,$_GPC;
		$id=$_GPC['id'];
		$snid=$_GPC['snid'];
		$time = time();
		$status = pdo_query("update ims_jdg_pub_winelog set status=1 ,endtime={$time} where 1=1 and id={$id}");
		if($status==FALSE){
			echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，可能操作失败，请重新尝试!');"; 
			echo "</script>";
		}else{
				$url = $this->createWebUrl('select',array('snid'=>$snid));  
		echo "<script language='javascript' type='text/javascript'>alert('执行成功,点击确定跳转!');";  
		echo "window.location.href='$url'";  
		echo "</script>"; 
			
		}
	}
	
	public function dodeletejt(){
		global $_W,$_GPC;
		$id=$_GPC['id'];
		$snid=$_GPC['snid'];
		$status = pdo_query("delete from ims_jdg_pub_winelog where 1=1 and id={$id} ");
		if($status==FALSEl){
			echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，可能操作失败，请重新尝试!');"; 
			echo "</script>";
		}else{
				$url = $this->createWebUrl('select',array('snid'=>$snid));  
		echo "<script language='javascript' type='text/javascript'>alert('执行成功,点击确定跳转!');";  
		echo "window.location.href='$url'";  
		echo "</script>"; 
			
		}
	}

	public function doinsertRule(){
		global $_W,$_GPC;
		$weid = $_W['weid'];
		$content = htmlspecialchars_decode($_GPC['content']);
		$select = pdo_fetch("select id from ims_jdg_pub_rule where 1=1 and weid={$weid}");
		if($select=""){
		$insert = pdo_query("insert into ims_jdg_pub_rule(weid,content) values({$weid},{$content})");
			if($insert==FALSEl){
				echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，可能操作失败，请重新尝试!');"; 
				echo "</script>";
			}else{
			$url = $this->createWebUrl('WineAdmin');  
			echo "<script language='javascript' type='text/javascript'>alert('执行成功,点击确定跳转!');";  
			echo "window.location.href='$url'";  
			echo "</script>"; 
		}
		
		}else{
		$update = pdo_query("update ims_jdg_pub_rule set content='{$content}' where 1=1 and weid={$weid}");
		if($update==FALSEl){
				$url = $this->createWebUrl('WineAdmin');  
				echo "<script language='javascript' type='text/javascript'>alert('因为网络原因，可能操作失败，请重新尝试!');"; 
				echo "window.location.href='$url'";  
				echo "</script>";
			}else{
			$url = $this->createWebUrl('WineAdmin');  
			echo "<script language='javascript' type='text/javascript'>alert('执行成功,点击确定跳转!');";  
			echo "window.location.href='$url'";  
			echo "</script>"; 
		}
	}
	}
	
	
	
}