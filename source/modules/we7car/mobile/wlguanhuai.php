<?php
/**
 * 品牌管理
 *
 * @author 超级无聊
 * @url
 */
if($op=='caredit'){
	$from_user = $_W['fans']['from_user'];
	if(isset($_GPC['submit'])){
		$from_user = $_W['fans']['from_user'];
		$guan= pdo_fetch("SELECT id,car_photo FROM ".tablename('weicar_guan')." WHERE weid = :weid and from_user='".$from_user."'", array(':weid' => $weid));
		if($guan==false){
			//添加数据
			$insert=array(
				'weid'=>$weid,
				'from_user'=>$from_user,
				'car_model'=>$_GPC['car_model'],
				'car_series'=>$_GPC['car_series'],
				'car_type'=>$_GPC['car_type'],
				'car_no'=>$_GPC['car_no'],
				'car_userName'=>$_GPC['car_userName'],
				'car_startTime'=>$_GPC['car_startTime'],
				'car_insurance_lastDate'=>$_GPC['car_insurance_lastDate'],
				'car_insurance_lastCost'=>$_GPC['car_insurance_lastCost'],
				'car_care_mileage'=>$_GPC['car_care_mileage'],
				'car_care_lastDate'=>$_GPC['car_care_lastDate'],
				'car_care_lastCost'=>$_GPC['car_care_lastCost'],
				'car_insurance_lastDate'=>$_GPC['car_insurance_lastDate'],
				'createtime'=>time(),
			);
			if (!empty($_FILES['car_photo']['tmp_name'])) {
				$upload = file_upload($_FILES['car_photo']);
				if (is_error($upload)) {
					message($upload['message']);
				}
				$insert['car_photo'] = $upload['path'];
			}

			$temp=pdo_insert('weicar_guan',$insert);
		}else{
			//修改数据
			$insert=array(
				'from_user'=>$from_user,
				'car_model'=>$_GPC['car_model'],
				'car_series'=>$_GPC['car_series'],
				'car_type'=>$_GPC['car_type'],
				'car_no'=>$_GPC['car_no'],
				'car_userName'=>$_GPC['car_userName'],
				'car_startTime'=>$_GPC['car_startTime'],
				'car_insurance_lastDate'=>$_GPC['car_insurance_lastDate'],
				'car_insurance_lastCost'=>$_GPC['car_insurance_lastCost'],
				'car_care_mileage'=>$_GPC['car_care_mileage'],
				'car_care_lastDate'=>$_GPC['car_care_lastDate'],
				'car_care_lastCost'=>$_GPC['car_care_lastCost'],
				'car_insurance_lastDate'=>$_GPC['car_insurance_lastDate'],
				'createtime'=>time(),
			);

			if (!empty($_FILES['car_photo']['tmp_name'])) {
				if (!empty($guan['car_photo'])) {
					file_delete($guan['car_photo']);
				}
				$upload = file_upload($_FILES['car_photo']);
				if (is_error($upload)) {
					message($upload['message']);
				}
				$insert['car_photo'] = $upload['path'];
			}
			$temp=pdo_update('weicar_guan', $insert, array('id'=>$guan['id']));
		}

		if($temp==false){
			message('抱歉，刚才修改的数据失败！', create_url('mobile/module', array('do' => 'Guanhuai', 'name' => 'we7car','op'=>'caredit','from_user'=>$from_user)), 'error');
		}else{
			message('更新设置数据成功！', create_url('mobile/module', array('do' => 'Guanhuai', 'name' => 'we7car','op'=>'caredit','weid'=>$weid,'from_user'=>$from_user)), 'success');
		}
	}else{
		$guan= pdo_fetch("SELECT * FROM ".tablename('weicar_guan')." WHERE weid = :weid and from_user='".$from_user."'", array(':weid' => $weid));
		if($guan==false){

		}
	}
	//获取汽车分类配置
	$series=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_series')." WHERE weid = ".$weid."  order by listorder desc");
	$choose='请选择-0$请选择-0';
	foreach($series as $row){
		$choose.='#'.$row['title'].'-'.$row['id'].'@'.$row['title'];
		$type=pdo_fetchall("SELECT id,title FROM ".tablename('weicar_type')." WHERE weid = ".$weid." AND sid=".$row['id']." order by listorder desc");
		foreach($type as $row2){
			$choose.='$'.$row2['title'].'-'.$row2['id'];
		}
	}
 	include $this->template('guanhuai_caredit');
}elseif($op=='list'){
 	$from_user = $_W['fans']['from_user'];
	$guan= pdo_fetch("SELECT * FROM ".tablename('weicar_guan')." WHERE weid = :weid and from_user='".$from_user."'", array(':weid' => $weid));
 		if($guan){
			$tempArr=explode('@',$guan['car_model']);
			if(count($tempArr)==2){
				$guan['car_model']=$tempArr[1];
			}
			$tempArr=explode('@',$guan['car_series']);
			if(count($tempArr)==2){
				$guan['car_series']=$tempArr[1];
			}

			if($guan['car_insurance_lastDate']){
				list($year,$month,$day)=explode("-",$guan['car_insurance_lastDate']);
				$temptime=mktime(0,0,0,$month,$day,$year);
				$time1=strtotime('+3 month',$temptime);
				$guan['time1']=date("Y-m-d",$time1);
			}
			if($guan['car_insurance_lastDate']){
				list($year,$month,$day)=explode("-",$guan['car_insurance_lastDate']);
				$guan['time2']=($year+1).'-'.$month.'-'.$day;
			}
			if($guan['car_startTime']){
				list($year,$month,$day)=explode("-",$guan['car_startTime']);
				$now=date("Y-m-d");
				list($year1,$month1,$day1)=explode("-",$now);
				if($month<$month1){
					$guan['time3']=$year1.'-'.$month;
				}elseif($month==$month1){
					$guan['time3']=$year1.'-'.$month;
				}elseif($month>$month1){
					$guan['time3']=($year1+1).'-'.$month;
				}
			}
			if(!empty($guan['car_photo'])){
				$head_pic=$_W['attachurl'].$guan['car_photo'];
			}
		}
		if(empty($head_pic)){
			$guanguan_thumb= pdo_fetchcolumn  ("SELECT guanhuai_thumb FROM ".tablename('weicar_set')." WHERE  weid=:weid  " , array(':weid'=>$_W['weid']));
			if($guanguan_thumb!=false){
				$head_pic=$_W['attachurl'].$guanguan_thumb;
			}
		}
		if(empty($head_pic)){
			$head_pic='./source/modules/weicar/style/img/car_series.jpg';
		}
	include $this->template('guanhuai_index');
}