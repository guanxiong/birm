<?php
/**
 * 模板助手
 *
 * [WNS] Copyright (c) 2013 BIRM.CO
 */
defined('IN_IA') or exit('Access Denied');

function tpl_form_field_color($name, $value = '') {
	$s = '';
	//修改define
	if (!defined('INCLUDE_COLOR')) {
		$s = '
		<script type="text/javascript" src="./resource/script/colorpicker/spectrum.js"></script>
		<script type="text/javascript">$(function(){colorpicker();});</script>
		<link type="text/css" rel="stylesheet" href="./resource/script/colorpicker/spectrum.css" />';
	}
	$s .= '
	<input class="span2" type="text" name="'.$name.'" id="color-'.md5($name).'" value="'.$value.'" placeholder="">
	<input class="colorpicker" target="color-'.md5($name).'" value="'.$value.'" />';
	define('INCLUDE_COLOR', true);
	return $s;
}

function tpl_form_field_daterange($name, $value = array(), $options = array()) {
	$s = '';
	if (!defined('INCLUDE_DATERANGE')) {
		$s = '
		<link type="text/css" rel="stylesheet" href="./resource/style/daterangepicker.css" />
		<script type="text/javascript" src="./resource/script/daterangepicker.js"></script>';
	}
	if (strexists($name, '[')) {
		$id = str_replace(array('[', ']'), '_', $name);
		$startname = $name . '[start]';
		$endname = $name . '[end]';
	} else {
		$startname = $name . '-start';
		$endname = $name . '-end';
	}
	define('INCLUDE_DATERANGE', true);
	$value['starttime'] = empty($value['starttime']) ? mktime(0, 0, 0) :  $value['starttime'];
	$value['endtime'] = empty($value['endtime']) ? ($value['starttime'] + 46800) :  $value['endtime'];
	$s .= '
	<input name="'.$startname.'" id="'.$id.'-start" type="hidden" value="'.date('Y-m-d', $value['starttime']).'" />
	<input name="'.$endname.'" id="'.$id.'-end" type="hidden" value="'.date('Y-m-d', $value['endtime']).'" />
	<button class="btn" id="'.$id.'-date-range" class="date" type="button"><span class="date-title">'.date('Y-m-d', $value['starttime']).' 至 '.date('Y-m-d', $value['endtime']).'</span> <i class="icon-caret-down"></i></button>
	<script type="text/javascript">
	$("#'.$id.'-date-range").daterangepicker({
		format: "YYYY-MM-DD",
		startDate: $(":hidden[id='.$id.'-start]").val(),
		endDate: $(":hidden[id='.$id.'-end]").val(),
		locale: {
			applyLabel: "确定",
			cancelLabel: "取消",
			fromLabel: "从",
			toLabel: "至",
			weekLabel: "周",
			customRangeLabel: "日期范围",
			daysOfWeek: moment()._lang._weekdaysMin.slice(),
			monthNames: moment()._lang._monthsShort.slice(),
			firstDay: 0
		}
	}, function(start, end){
		$("#'.$id.'-date-range .date-title").html(start.format("YYYY-MM-DD") + " 至 " + end.format("YYYY-MM-DD"));
		$(":hidden[id='.$id.'-start]").val(start.format("YYYY-MM-DD"));
		$(":hidden[id='.$id.'-end]").val(end.format("YYYY-MM-DD"));
	});</script>';
	return $s;
}

function tpl_form_field_date($name, $value = array(), $ishour = false) {
	$s = '';
	if (!defined('INCLUDE_DATE')) {
		$s = '
		<link type="text/css" rel="stylesheet" href="./resource/style/datetimepicker.css" />
		<script type="text/javascript" src="./resource/script/datetimepicker.js"></script>';
	}
	define('INCLUDE_DATE', true);
	if (strexists($name, '[')) {
		$id = str_replace(array('[', ']'), '_', $name);
	} else {
		$id = $name;
	}
	$value = empty($value) ? date('Y-m-d', mktime(0, 0, 0)) :  $value;
	$ishour = empty($ishour) ? 2 : 0;
	$s .= '
	<input type="text" class="datepicker" id="datepicker_'.$id.'" name="'.$name.'" value="'.$value.'" readonly="readonly" />
	<script type="text/javascript">
		$("#datepicker_'.$id.'").datetimepicker({
			format: "yyyy-mm-dd hh:ii",
			minView: "'.$ishour.'",
			//pickerPosition: "top-right",
			autoclose: true
		});
	</script>';
	return $s;
}

function tpl_form_field_image($name, $value = '', $default = './resource/image/module-nopic-small.jpg') {
	$s = '';
	if (!defined('INCLUDE_KINDEDITOR')) {
		$s = '
		<script type="text/javascript" src="./resource/script/kindeditor/kindeditor-min.js"></script>
		<script type="text/javascript" src="./resource/script/kindeditor/lang/zh_CN.js"></script>
		<link type="text/css" rel="stylesheet" href="./resource/script/kindeditor/themes/default/default.css" />';
	}
	if (strexists($name, '[')) {
		$id = str_replace(array('[', ']'), '_', $name);
	} else {
		$id = $name;
	}
	$val = $default;
	if(!empty($value)) {
		$val = toimage($value);
	}
	$s .= '
	<div style="display:block; margin-top:5px;" class="input-append">
	<input type="text" value="'.$value.'" name="'.$name.'" id="upload-image-url-'.$id.'" class="span3" autocomplete="off">
	<input type="hidden" value="" name="'.$name.'_old" id="upload-image-url-'.$id.'-old">
	<button class="btn" type="button" id="upload-image-'.$id.'">选择图片</button>
	</div>
	<div id="upload-image-preview-'.$id.'" style="margin-top:10px;"><img src="' . $val . '" width="100" onload="thumb(this)" /></div>
	<script type="text/javascript">
	function thumb(obj) {
		if (obj.height > obj.width) { obj.style.height = "100px"; obj.style.width = "auto"}
	}
	var editor = KindEditor.editor({
		allowFileManager : true,
		uploadJson : "./index.php?act=attachment&do=upload",
		fileManagerJson : "./index.php?act=attachment&do=manager",
		afterUpload : function(url, data) {
		}
	});
	$("#upload-image-'.$id.'").click(function() {
		editor.loadPlugin("image", function() {
			editor.plugin.imageDialog({
				tabIndex : 1,
				imageUrl : $("#upload-image-url-'.$id.'").val(),
				clickFn : function(url) {
					editor.hideDialog();
					var val = url;
					if(url.toLowerCase().indexOf("http://") == -1 && url.toLowerCase().indexOf("https://") == -1) {
						var filename = /images(.*)/.exec(url);
						if(filename && filename[0]) {
							val = filename[0];
						}
					}
					$("#upload-image-url-'.$id.'-old").val($("#upload-image-url-'.$id.'").val());
					$("#upload-image-url-'.$id.'").val(val);
					$("#upload-image-preview-'.$id.'").html(\'<img src="\'+url+\'" width="100"  onload="thumb(this)" />\');
				}
			});
		});
	});
	</script>';
	define('INCLUDE_KINDEDITOR', true);
	return $s;
}

function tpl_form_field_coordinate($field, $value = array()) {
	if (!defined('INCLUDE_COORDINATE')) {
		$s = '
		<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=F51571495f717ff1194de02366bb8da9"></script>';
	}
	$prefix = $field.'_';
	$s .= '<input type="text" name="lng" id="'.$prefix.'lng" value="'.$value['lng'].'"  class="span3" /> - <input type="text" id="'.$prefix.'lat" name="lat" value="'.$value['lat'].'"  class="span3" /> <button onclick="$(\'#'.$prefix.'panel\').css(\'margin-top\', \'auto\');$(\'#'.$prefix.'panel\').modal();" value="选择坐标" class="btn" type="button">选择坐标</button>';
	$s .= '
	
	<div id="'.$prefix.'panel" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" style=" width:800px; margin-top:-9999px;">
		<div class="modal-header"><button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button><h3>选择地图上的坐标</h3></div>
		<div class="modal-body">
			<table class="tb">
				<tr>
					<th><label for="">详细地址</label></th>
					<td><div class="input-append"><input type="text" id="'.$prefix.'address" value="" class="span5" /><button type="button" class="btn" name="submit" value="搜索" onclick="bmap.searchMapByAddress($(\'#'.$prefix.'address\').val())">搜索</button></div><span class="help-block">可以通过查询地址，快速定位地图位置。</span></td>
				</tr>
				<tr>
					<th></th>
					<td><div id="'.$prefix.'viewer" style="width:600px; height:300px;"></div></td>
				</tr>
			</table>
			<div id="module-menus"></div>
		</div>
		<div class="modal-footer"><a href="#" class="btn" data-dismiss="modal" aria-hidden="true">关闭</a></div>
	</div>
	<script type="text/javascript">
		var bmap = {
			\'option\' : {
				\'lock\' : false,
				\'container\' : \''.$prefix.'viewer\',
				\'point\' : {\'lng\' : 116.403851, \'lat\' : 39.915177}
			},
			\'init\' : function(option) {
				var $this = this;
				if ($(\'#'.$prefix.'lng\').val()) {
					$this.option.point.lng = $(\'#'.$prefix.'lng\').val();
				}
				if ($(\'#'.$prefix.'lat\').val()) {
					$this.option.point.lat = $(\'#'.$prefix.'lat\').val();
				}
				if ($(\'#\' + $this.option.container).get(0).isload) {
					return;
				}
				$(\'#\' + $this.option.container).get(0).isload = true;
				$this.option = $.extend({},$this.option,option);
				
				$this.option.defaultPoint = new BMap.Point($this.option.point.lng, $this.option.point.lat);
				$this.bgeo = new BMap.Geocoder();
				
				$this.bmap = new BMap.Map($this.option.container);
				$this.bmap.centerAndZoom($this.option.defaultPoint, 15);
				$this.bmap.enableScrollWheelZoom();
				$this.bmap.enableDragging();
				$this.bmap.enableContinuousZoom();
				$this.bmap.addControl(new BMap.NavigationControl());
				$this.bmap.addControl(new BMap.OverviewMapControl());
				//添加标注
				$this.marker = new BMap.Marker($this.option.defaultPoint);
				$this.marker.setLabel(new BMap.Label(\'请您移动此标记，选择您的坐标！\', {\'offset\':new BMap.Size(10,-20)}));
				$this.marker.enableDragging();
				$this.bmap.addOverlay($this.marker);
				//$this.marker.setAnimation(BMAP_ANIMATION_BOUNCE);
				$this.showPointValue($this.marker.getPosition());
				//拖动地图事件
				$this.bmap.addEventListener("dragging", function() {
					$this.setMarkerCenter();
					$this.option.lock = false;
				});
				//缩入地图事件
				$this.bmap.addEventListener("zoomend", function() {
					$this.option.lock = false;
				});
				//拖动标记事件
				$this.marker.addEventListener("dragend", function (e) {
					$this.showPointValue();
					$this.showAddress();
					$this.bmap.panTo(new BMap.Point(e.point.lng, e.point.lat));
					$this.option.lock = false;
					$this.marker.setAnimation(null);
				});
				
			},
			\'searchMapByAddress\' : function(address) {
				var $this = this;
				$this.bgeo.getPoint(address, function (point) {
					if (point) {
						$this.showPointValue();
						$this.bmap.panTo(point);
						$this.setMarkerCenter();
					}
				});
			},
			\'searchMapByPCD\' : function(address) {
				var $this = this;
				$this.option.lock = true;
				$this.searchMapByAddress($(\'#sel-provance\').val()+$(\'#sel-city\').val()+$(\'#sel-area\').val());
			},
			\'setMarkerCenter\' : function() {
				var $this = this;
				var center = $this.bmap.getCenter();
				$this.marker.setPosition(new BMap.Point(center.lng, center.lat));
				$this.showPointValue();
				//$this.showAddress();
			},
			\'showPointValue\' : function() {
				var $this = this;
				var point = $this.marker.getPosition();
				$(\'#'.$prefix.'lng\').val(point.lng);
				$(\'#'.$prefix.'lat\').val(point.lat);
			},
			\'showAddress\' : function() {
				var $this = this;
				var point = $this.marker.getPosition();
				$this.bgeo.getLocation(point, function (s) {
					if (s) {
						$(\'#'.$prefix.'address\').val(s.address);
						if (!$this.option.lock) {
							//cascdeInit(s.addressComponents.province,s.addressComponents.city,s.addressComponents.district);
						}
					}
				});
			}
		};
		$(function(){
			var option = {};
			bmap.init();
		});
	</script>';
	return $s;
}

function tpl_fans_form($field, $value = '') {
	switch ($field) {
		case 'avatar':
			$avatar_url = 'resource/image/avatar/';
			$html = '
				<input type="hidden" value="" name="avatar" id="avatar">
				<div class="file">
					<input type="file" name="avatar" style="width:51%;">
					<div class="input-prepend input-append">
						<button class="btn" type="button" style="width:51%; text-align:center;"><i class="icon-upload-alt"></i> 上传头像</button>
						<button class="btn btn-inverse" type="button" style="width:50%; text-align:center;" data-toggle="modal" data-target="#sysavatar"><i class="icon-github-alt"></i> 系统头像</button>
					</div>
				</div>';
			$html .= '
			<div id="sysavatar" class="modal hide fade" tabindex="-1" style="margin-left:0; left:5%; width:90%;" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<style>
				#sysavatar span{overflow:hidden; display: inline-block; border:2px #E1E1E1 solid; margin-bottom:5px; cursor:pointer;}
				#sysavatar span img{width:50px; height:50px;}
				</style>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">选择系统头像</h3>
				</div>
				<div class="modal-body" style="padding-bottom:10px;">
					<span><img src="'.$avatar_url.'avatar_1.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_2.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_3.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_4.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_5.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_6.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_7.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_8.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_9.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_10.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_11.jpg" /></span>
					<span><img src="'.$avatar_url.'avatar_12.jpg" /></span>
				</div>
				<div class="modal-footer">
					<button class="btn btn-primary" data-dismiss="modal" aria-hidden="true">完成</button>
				</div>
			</div>';
			$html .= '
				<script type="text/javascript">
				$("#sysavatar .modal-body").delegate("span", "click", function(){
					$("#sysavatar .modal-body span").css("border", "2px #E1E1E1 solid");
					$(this).css("border", "2px #ED2F2F solid");
					$("#avatar").val($(this).find("img").attr("src"));
				});
				</script>';
			break;
		case 'nickname':
			$html = '<input type="text" name="nickname" class="form-control" value="'.$value.'">';
			break;
		case 'realname':
			$html = '<input type="text" id="" name="realname" class="form-control" value="'.$value.'">';
			break;
		case 'gender':
			$html = '<select name="gender" class="form-control">
						<option value="0" '.($value == 0 ? 'selected ' : '').'>保密</option>
						<option value="1" '.($value == 1 ? 'selected ' : '').'>男</option>
						<option value="2" '.($value == 2 ? 'selected ' : '').'>女</option>
					</select>';
			break;
		case 'birth':
		case 'birthyear':
		case 'birthmonth':
		case 'birthday':
			$year = array(date('Y'), '1914');
			$html = '<select name="birthyear" class="form-control" style="width:30%; margin-right:5%;"><option value="0">年</option>';
			for ($i = $year[0]; $i >= $year[1]; $i--) {
				$html .= '<option value="'.$i.'" '.($i == $value['birthyear'] ? 'selected ' : '').'>'.$i.'</option>';
			}
			$html .= '</select>';
			$html .= '<select name="birthmonth" class="form-control" style="width:30%; margin-right:5%;"><option value="0">月</option>';
			for ($i = 1; $i <= 12; $i++) {
				$html .= '<option value="'.$i.'" '.($i == $value['birthmonth'] ? 'selected ' : '').'>'.$i.'</option>';
			}
			$html .= '</select>';
			$html .= '<select name="birthday" class="form-control" style="width:30%;"><option value="0">日</option>';
			for ($i = 1; $i <= 31; $i++) {
				$html .= '<option value="'.$i.'" '.($i == $value['birthday'] ? 'selected ' : '').'>'.$i.'</option>';
			}
			$html .= '</select>';
			break;
		case 'reside':
		case 'resideprovince':
		case 'residecity':
		case 'residedist':
			$html = '<select name="resideprovince" class="form-control" id="sel-provance" onChange="selectCity();" style="width:30%; margin-right:5%;">';
			$html .= '<option value="" selected="true">省/直辖市</option>';
			$html .= '</select>';
			$html .= '<select name="residecity" class="form-control" id="sel-city" onChange="selectcounty()" style="width:30%; margin-right:5%;">';
			$html .= '<option value="" selected="true">请选择</option>';
			$html .= '</select>';
			$html .= '<select name="residedist" class="form-control" id="sel-area" style="width:30%;">';
			$html .= '<option value="" selected="true">请选择</option>';
			$html .= '</select>';
			$html .= '<script type="text/javascript" src="./resource/script/cascade.js"></script>';
			$html .= "<script type=\"text/javascript\">cascdeInit('{$value['resideprovince']}','{$value['residecity']}','{$value['residedist']}');</script>";
			break;
		case 'address':
			$html = '<input type="text" class="form-control" id="" name="address" value="'.$value.'" />';
			break;
		case 'mobile':
			$html = '<input type="text" class="form-control" id="" name="mobile" value="'.$value.'" />';
			break;
		case 'qq':
			$html = '<input type="text" class="form-control" id="" name="qq" value="'.$value.'">';
			break;
		case 'msn':
			$html = '<input type="text" class="form-control" id="" name="msn" value="'.$value.'">';
			break;
		case 'email':
			$html = '<input type="text" class="form-control" id="" name="email" value="'.$value.'">';
			break;
		case 'telephone':
			$html = '<input type="text" class="form-control" id="" name="telephone" value="'.$value.'">';
			break;
		case 'taobao':
			$html = '<input type="text" class="form-control" id="" name="taobao" value="'.$value.'">';
			break;
		case 'alipay':
			$html = '<input type="text" class="form-control" id="" name="alipay" value="'.$value.'">';
			break;
		case 'studentid':
			$html = '<input type="text" class="form-control" id="" name="studentid" value="'.$value.'">';
			break;
		case 'grade':
			$html = '<input type="text" class="form-control" id="" name="grade" value="'.$value.'">';
			break;
		case 'graduateschool':
			$html = '<input type="text" class="form-control" id="" name="graduateschool" value="'.$value.'">';
			break;
		case 'education':
			$options = array('博士','硕士','本科','专科','中学','小学','其它');
			$html = '<select name="education" class="form-control">';
			foreach ($options as $item) {
				$html .= '<option value="'.$item.'" '.($value == $item ? 'selected ' : '').'>'.$item.'</option>';
			}
			$html .= '</select>';
			break;
		case 'company':
			$html = '<input type="text" class="form-control" id="" name="company" value="'.$value.'">';
			break;
		case 'occupation':
			$html = '<input type="text" class="form-control" id="" name="occupation" value="'.$value.'">';
			break;
		case 'position':
			$html = '<input type="text" class="form-control" id="" name="position" value="'.$value.'">';
			break;
		case 'revenue':
			$html = '<input type="text" class="form-control" id="" name="revenue" value="'.$value.'">';
			break;
		case 'constellation':
			$options = array('水瓶座','双鱼座','白羊座','金牛座','双子座','巨蟹座','狮子座','处女座','天秤座','天蝎座','射手座','摩羯座');
			$html = '<select name="constellation" class="form-control">';
			foreach ($options as $item) {
				$html .= '<option value="'.$item.'" '.($value == $item ? 'selected ' : '').'>'.$item.'</option>';
			}
			$html .= '</select>';
			break;
		case 'zodiac':
			$options = array('鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪');
			$html = '<select name="zodiac" class="form-control">';
			foreach ($options as $item) {
				$html .= '<option value="'.$item.'" '.($value == $item ? 'selected ' : '').'>'.$item.'</option>';
			}
			$html .= '</select>';
			break;
		case 'nationality':
			$html = '<input type="text" class="form-control" id="" name="nationality" value="'.$value.'">';
			break;
		case 'height':
			$html = '<input type="text" class="form-control" id="" name="height" value="'.$value.'">';
			break;
		case 'weight':
			$html = '<input type="text" id="" name="weight" value="'.$value.'">';
			break;
		case 'bloodtype':
			$options = array('A', 'B', 'AB', 'O', '其它');
			$html = '<select name="bloodtype" class="form-control">';
			foreach ($options as $item) {
				$html .= '<option value="'.$item.'" '.($value == $item ? 'selected ' : '').'>'.$item.'</option>';
			}
			$html .= '</select>';
			break;
		case 'idcard':
			$html = '<input type="text" class="form-control" id="" name="idcard" value="'.$value.'">';
			break;
		case 'zipcode':
			$html = '<input type="text" class="form-control" id="" name="zipcode" value="'.$value.'">';
			break;
		case 'site':
			$html = '<input type="text" class="form-control" id="" name="site" value="'.$value.'">';
			break;
		case 'affectivestatus':
			$html = '<input type="text" class="form-control" id="" name="affectivestatus" value="'.$value.'">';
			break;
		case 'lookingfor':
			$html = '<input type="text" class="form-control" id="" name="lookingfor" value="'.$value.'">';
			break;
		case 'bio':
			$html = '<textarea name="bio" class="form-control">'.$value.'</textarea>';
			break;
		case 'interest':
			$html = '<textarea name="interest" class="form-control">'.$value.'</textarea>';
			break;
	}
	return $html;
}