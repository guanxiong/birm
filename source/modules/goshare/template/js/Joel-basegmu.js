//For Memory,For Joel//
//Joel-Wap-前端函数 V1.0//
//Auth:蒋金辰 Joel
//Mail:54006600@qq.com
//(c) Copyright 2014 Joel. All Rights Reserved.

//Joel Alert重置
function Joel_gmuAlert(title,content,cancel,ok){
	var cancelfun=cancel?cancel:function(){this.destroy();};
	var okfun=ok?ok:function(){this.destroy();};
	var opts={
		'title':title,
		'content':content,
		'buttons': {
         	'取消':cancelfun,
         	'确定':okfun
     	}
	};
	var alt=new gmu.Dialog(opts);
}
function Joel_gmuAlert2(title,content,cancel,ok){
	var cancelfun=cancel?cancel:function(){this.destroy();};
	var okfun=ok?ok:function(){this.destroy();};
	var opts={
		'title':title,
		'content':content,
		'buttons': {
         	'确定':okfun
     	}
	};
	var alt=new gmu.Dialog(opts);
}

//2014-07-28 By CC
//单按钮提示框,动画弹出,倒计时结束动画消失
function Joel_gmuMsg(title,content){
	var times=3;//倒计时秒数
	var intval;
	var opts={
		id:'id01',
		title:title,
		content:content,
		closeBtn:false,
		buttons: {
         	'确定 ':function(){endfun();}
     	}
	};

	var alt=new gmu.Dialog(opts);
	
	var midpx=$('.ui-dialog').css('top');//当前页面居中的高度
	var mid=parseInt(midpx.substring(0,midpx.length-2));//当前页面居中的高度值，无“px”
	var distance=window.screen.height*0.4;//动画距离:屏幕高度40%
	var startpx=(mid-distance)+'px';//起始位置
	var endpx=(mid+distance)+'px';//结束位置
	
	$('.ui-btn').text("确定 ("+(times)+")");
	$('.ui-dialog').css({'top':startpx,'opacity':0,'-webkit-transform':'scale(0.1)'});
	$('.ui-btn').css('color','#0079FF');
	$('.ui-dialog-title').css('background','#2b88cf');
	startfun();
	
	function endfun(){
		window.clearInterval(intval);
		$(".ui-dialog").animate({
	        opacity: 0,
	        top:endpx,
	        scale:'0.1',
	        rotateZ:'720deg',
	        translate3d:'0,10px,0',
	     },400,'ease-in',function(){alt.destroy();})
	}
	
	function startfun(){
		$(".ui-dialog").animate({
	        opacity: 1,
	        top:midpx,
	        scale:'1',
	        //rotateZ:'720deg',
	        //translate3d:'0,10px,0',
	     },400,'ease-out',function(){intval=setInterval(intfun,1000);})
	}
	
	var intfun=function(){
		if ((times-1)<1) {
			endfun();
		} else {
			$('.ui-btn').text("确定 ("+(times-1)+")");
			times--;
		}
	}
	
}
