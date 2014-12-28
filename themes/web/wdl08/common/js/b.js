(function($){
	$.fn.slide=function(options){
		$.fn.slide.deflunt={
		effect : "fade", //效果 || fade：渐显； || top：上滚动；|| left：左滚动；|| topLoop：上循环滚动；|| leftLoop：左循环滚动；|| topMarquee：上无缝循环滚动；|| leftMarquee：左无缝循环滚动；
		autoPlay:false, //自动运行
		delayTime : 500, //效果持续时间
		interTime : 2500,//自动运行间隔。当effect为无缝滚动的时候，相当于运行速度。
		defaultIndex : 0,//默认的当前位置索引。0是第一个
		titCell:".hd li",//导航元素
		mainCell:".bd",//内容元素的父层对象
		trigger: "mouseover",//触发方式 || mouseover：鼠标移过触发；|| click：鼠标点击触发；
		scroll:1,//每次滚动个数。
		vis:1,//visible，可视范围个数，当内容个数少于可视个数的时候，不执行效果。
		titOnClassName:"on",//当前位置自动增加的class名称
		autoPage:false,//系统自动分页，当为true时，titCell则为导航元素父层对象，同时系统会在titCell里面自动插入分页li元素(1.2版本新增)
		prevCell:".prev",//前一个按钮元素。
		nextCell:".next"//后一个按钮元素。
		};

		return this.each(function() {
			var opts = $.extend({},$.fn.slide.deflunt,options);
			var index=opts.defaultIndex;
			var prevBtn = $(opts.prevCell, $(this));
			var nextBtn = $(opts.nextCell, $(this));
			var navObj = $(opts.titCell, $(this));//导航子元素结合
			var navObjSize = navObj.size();
			var conBox = $(opts.mainCell , $(this));//内容元素父层对象
			var conBoxSize=conBox.children().size();
			var slideH=0;
			var slideW=0;
			var selfW=0;
			var selfH=0;
			var autoPlay = opts.autoPlay;
			var inter=null;//setInterval名称 
			var oldIndex = index;

			if(conBoxSize<opts.vis) return; //当内容个数少于可视个数，不执行效果。

			//处理分页
			if( navObjSize==0 )navObjSize=conBoxSize;
			if( opts.autoPage ){
				var tempS = conBoxSize-opts.vis;
				navObjSize=1+parseInt(tempS%opts.scroll!=0?(tempS/opts.scroll+1):(tempS/opts.scroll)); 
				navObj.html(""); 
				for( var i=0; i<navObjSize; i++ ){ navObj.append("<li>"+(i+1)+"</li>") }
				var navObj = $("li", navObj);//重置导航子元素对象
			}

			conBox.children().each(function(){ //取最大值
				if( $(this).width()>selfW ){ selfW=$(this).width(); slideW=$(this).outerWidth(true);  }
				if( $(this).height()>selfH ){ selfH=$(this).height(); slideH=$(this).outerHeight(true);  }
			});

			switch(opts.effect)
			{
				case "top": conBox.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; height:'+opts.vis*slideH+'px"></div>').css( { "position":"relative","padding":"0","margin":"0"}).children().css( {"height":selfH} ); break;
				case "left": conBox.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; width:'+opts.vis*slideW+'px"></div>').css( { "width":conBoxSize*slideW,"position":"relative","overflow":"hidden","padding":"0","margin":"0"}).children().css( {"float":"left","width":selfW} ); break;
				case "leftLoop":
				case "leftMarquee":
					conBox.children().clone().appendTo(conBox).clone().prependTo(conBox); 
					conBox.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; width:'+opts.vis*slideW+'px"></div>').css( { "width":conBoxSize*slideW*3,"position":"relative","overflow":"hidden","padding":"0","margin":"0","left":-conBoxSize*slideW}).children().css( {"float":"left","width":selfW}  ); break;
				case "topLoop":
				case "topMarquee":
					conBox.children().clone().appendTo(conBox).clone().prependTo(conBox); 
					conBox.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; height:'+opts.vis*slideH+'px"></div>').css( { "height":conBoxSize*slideH*3,"position":"relative","padding":"0","margin":"0","top":-conBoxSize*slideH}).children().css( {"height":selfH} ); break;
			}

			//效果函数
			var doPlay=function(){
				switch(opts.effect)
				{
					case "fade": case "top": case "left": if ( index >= navObjSize) { index = 0; } else if( index < 0) { index = navObjSize-1; } break;
					case "leftMarquee":case "topMarquee": if ( index>= 2) { index=1; } else if( index<0) { index = 0; } break;
					case "leftLoop": case "topLoop":
						var tempNum = index - oldIndex; 
						if( navObjSize>2 && tempNum==-(navObjSize-1) ) tempNum=1;
						if( navObjSize>2 && tempNum==(navObjSize-1) ) tempNum=-1;
						var scrollNum = Math.abs( tempNum*opts.scroll );
						if ( index >= navObjSize) { index = 0; } else if( index < 0) { index = navObjSize-1; }
					break;
				}

				switch (opts.effect)
				{
					case "fade":conBox.children().stop(true,true).eq(index).fadeIn(opts.delayTime).siblings().hide();break;
					case "top":conBox.stop(true,true).animate({"top":-index*opts.scroll*slideH},opts.delayTime);break;
					case "left":conBox.stop(true,true).animate({"left":-index*opts.scroll*slideW},opts.delayTime);break;
					case "leftLoop":
						if(tempNum<0 ){
								conBox.stop(true,true).animate({"left":-(conBoxSize-scrollNum )*slideW},opts.delayTime,function(){
								for(var i=0;i<scrollNum;i++){ conBox.children().last().prependTo(conBox); }
								conBox.css("left",-conBoxSize*slideW);
							});
						}
						else{
							conBox.stop(true,true).animate({"left":-( conBoxSize + scrollNum)*slideW},opts.delayTime,function(){
								for(var i=0;i<scrollNum;i++){ conBox.children().first().appendTo(conBox); }
								conBox.css("left",-conBoxSize*slideW);
							});
						}break;// leftLoop end

					case "topLoop":
						if(tempNum<0 ){
								conBox.stop(true,true).animate({"top":-(conBoxSize-scrollNum )*slideH},opts.delayTime,function(){
								for(var i=0;i<scrollNum;i++){ conBox.children().last().prependTo(conBox); }
								conBox.css("top",-conBoxSize*slideH);
							});
						}
						else{
							conBox.stop(true,true).animate({"top":-( conBoxSize + scrollNum)*slideH},opts.delayTime,function(){
								for(var i=0;i<scrollNum;i++){ conBox.children().first().appendTo(conBox); }
								conBox.css("top",-conBoxSize*slideH);
							});
						}break;//topLoop end

					case "leftMarquee":
						var tempLeft = conBox.css("left").replace("px",""); 

						if(index==0 ){
								conBox.animate({"left":++tempLeft},0,function(){
									if( conBox.css("left").replace("px","")>= 0){ for(var i=0;i<conBoxSize;i++){ conBox.children().last().prependTo(conBox); }conBox.css("left",-conBoxSize*slideW);}
								});
						}
						else{
								conBox.animate({"left":--tempLeft},0,function(){
									if(  conBox.css("left").replace("px","")<= -conBoxSize*slideW*2){ for(var i=0;i<conBoxSize;i++){ conBox.children().first().appendTo(conBox); }conBox.css("left",-conBoxSize*slideW);}
								});
						}break;// leftMarquee end

						case "topMarquee":
						var tempTop = conBox.css("top").replace("px",""); 
							if(index==0 ){
									conBox.animate({"top":++tempTop},0,function(){
										if( conBox.css("top").replace("px","") >= 0){ for(var i=0;i<conBoxSize;i++){ conBox.children().last().prependTo(conBox); }conBox.css("top",-conBoxSize*slideH);}
									});
							}
							else{
									conBox.animate({"top":--tempTop},0,function(){
										if( conBox.css("top").replace("px","")<= -conBoxSize*slideH*2){ for(var i=0;i<conBoxSize;i++){ conBox.children().first().appendTo(conBox); }conBox.css("top",-conBoxSize*slideH);}
									});
							}break;// topMarquee end


				}//switch end
					navObj.removeClass(opts.titOnClassName).eq(index).addClass(opts.titOnClassName);
					oldIndex=index;
			};
			//初始化执行
			doPlay();

			//自动播放
			if (autoPlay) {
					if( opts.effect=="leftMarquee" || opts.effect=="topMarquee"  ){
						index++; inter = setInterval(doPlay, opts.interTime);
						conBox.hover(function(){if(autoPlay){clearInterval(inter); }},function(){if(autoPlay){clearInterval(inter);inter = setInterval(doPlay, opts.interTime);}});
					}else{
						 inter=setInterval(function(){index++; doPlay() }, opts.interTime); 
						$(this).hover(function(){if(autoPlay){clearInterval(inter); }},function(){if(autoPlay){clearInterval(inter); inter=setInterval(function(){index++; doPlay() }, opts.interTime); }});
					}
			}

			//鼠标事件
			var mst;
			if(opts.trigger=="mouseover"){
				navObj.hover(function(){ clearTimeout(mst); index=navObj.index(this); mst = window.setTimeout(doPlay,200); }, function(){ if(!mst)clearTimeout(mst); });
			}else{ navObj.click(function(){index=navObj.index(this);  doPlay(); })  }
			nextBtn.click(function(){ index++; doPlay(); });
			prevBtn.click(function(){  index--; doPlay(); });

    	});//each End

	};//slide End

})(jQuery);


$(document).ready(function(){basesz();
	var navH = $("#Slider").offset().top;
	$(window).scroll(function(){
		var scroH = $(this).scrollTop();
		if(scroH>=navH){
			$("#head").css({"position":"fixed","top":0,"height":"96px","overflow":"hidden"});
			$("#Slider").css({"margin-top":"96px"});
		}else if(scroH<navH){
			$("#head").css({"position":"fixed","top":0,"height":"96px","overflow":"hidden"});
			$("#Slider").css({"margin-top":"96px"});
		}
	})
});
$(window).load(function(){
						
});
function ckhz(str){if (escape(str).indexOf("%u")!=-1){return true;}else{return false;}}
function ckcn(cs){  
  var regu = "^[a-zA-Z\u4e00-\u9fa5]+$";
  var re = new RegExp(regu);
  if (cs.search(re) != -1){return true;}else{return false;}
}
function basesz()
{
	$("a.blank").click(function(){window.open($(this).attr("href"));return false;});
	$("#Sethome").click(function(){this.style.behavior='url(#default#homepage)';this.setHomePage(location.href);});
	$("#AddFavorite").click(function(){return sc();return false;window.external.AddFavorite(location.href,document.title)});
	T_bd();csh();$("#dosubmit").click(function(){return submits();});
	if ($(".igg").size()>1){setInterval('scroll_tj()',4000);}
	jQuery("#Slider").slide( { mainCell:".bd ul",effect:"left",autoPlay:true} );
}

function T_bd()
{
	$(".newList .normal").hover(function(){$(this).addClass("hover");},function(){$(this).removeClass("hover");});
}
function csh()
{
$("#fom1").focus(function(){if ($(this).val()=="填写姓名"){$(this).val("");}});
$("#fom1").blur(function(){var keyword=$(this).val();if (keyword==""){$(this).val("填写姓名");}});
$("#fom2").focus(function(){if ($(this).val()=="联系电话"){$(this).val("");}});
$("#fom2").blur(function(){var keyword=$(this).val();if (keyword==""){$(this).val("联系电话");}});
$("#fom3").focus(function(){if ($(this).val()=="电子邮箱"){$(this).val("");}});
$("#fom3").blur(function(){var keyword=$(this).val();if (keyword==""){$(this).val("电子邮箱");}});
$("#fom4").focus(function(){if ($(this).val()=="您的公司"){$(this).val("");}});
$("#fom4").blur(function(){var keyword=$(this).val();if (keyword==""){$(this).val("您的公司");}});
$("#fom5").focus(function(){if ($(this).val()=="请填写您的需求或建议"){$(this).val("");}});
$("#fom5").blur(function(){var keyword=$(this).val();if (keyword==""){$(this).val("请填写您的需求或建议");}});	
}

function scroll_tj(){$(".igg").eq(0).fadeOut(600,function(){$(this).clone().appendTo($(this).parent()).fadeIn(400);$(this).remove();});}
function submits()
{
	var f1=$('#fom1').val();
	var f2=$('#fom2').val();
	var f3=$('#fom3').val();
	var f4=$('#fom4').val();
	var f5=$('#fom5').val();
	if (f1=="填写姓名"){f1="";}
	if (f2=="联系电话"){f2="";}
	if (f3=="电子邮箱"){f3="";}
	if (f4=="您的公司"){f4="";}
	if (f5=="请填写您的需求或建议"){f5=="";}
	if (f1==""){$("#fom1").val("");$("#fom1").focus();return false;}
	if (f1.length<2 || check_chinese(f1)==false){alert("请填写中文姓名");$("#fom1").focus();return false;}
	if (f2==""){$("#fom2").val("");$("#fom2").focus();return false;}
	if (check_tel(f2)==false){alert("请输入正确的联系电话");$("#fom2").focus();return false;}
	if (isEmail(f3)==false){$("#fom3").focus();alert("邮箱格式错误");return false;}
	if (f4!="" && f4.length<4){alert("请认真填写公司名称");$("#fom4").focus();return false;}
	if(f5.length<10 || ckhz(f5)==false){$("#fom5").focus();alert('需求或建议 必须包含中文同时不少于10字符'); return false;}
   $.ajax({
   type: "POST",
   url: "../action.php.htm"/*tpa=http://www.wifiguanjia.com/action.php*/,
   data: "act=save&f1="+encodeURIComponent(f1)+"&f2="+encodeURIComponent(f2)+"&f3="+encodeURIComponent(f3)+"&f4="+encodeURIComponent(f4)+"&f5="+encodeURIComponent(f5),
   success: function(msg){
	   }
	}); 
	alert("发送成功");$("#fom1,#fom2,#fom3,#fom4,#fom5").val("");
	return false;
}
function check_cnname(str){if (str.search(/^[\u0391-\uFFE5]+$/)==-1){return false;}else{return true;}}
function isEmail(str) {if (str.search(/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/) == -1){return false;}else{return true;}}
function check_tel(str) {if (str.search(/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$|^((\(\d{2,3}\))|(\d{3}\-))?1\d{10}$/) == -1){return false;}else{return true;}}
function check_zip(str){if (str.search(/^[1-9]\d{5}$/)==-1){return false;}else{return true;}}
function check_zip2(str){if (str.search(/^[1-9]\d{3}$/)==-1){return false;}else{return true;}}
function check_qq(str){if (str.search(/^[1-9]\d{4,11}$/)==-1){return false;}else{return true;}}
function check_age(str){if (str.search(/^[1-9]\d{1}$/)==-1){return false;}else{return true;}}
function check_num(str){if (str.search(/^((\(\d{2,3}\))|(\d{3}\-))?1\d{10}$/)==-1 && str!="") {return false;}else{return true;}}
function check_time(str){if (str.search(/^(\d{4})\-(\d{2})\-(\d{2})$/)==-1){return false;}else{return true;}}
function check_chinese(str){if (str.search(/^([\u4E00-\uFA29]|[\uE7C7-\uE7F3])*$/)==-1){return false;}else{return true;}}
function sc() {
    var url = window.location;
    var title = document.title;
    var ua = navigator.userAgent.toLowerCase();
    if (ua.indexOf("360se") > -1) {
        alert("由于360浏览器功能限制，请按 Ctrl+D 手动收藏！");
    }
    else if (ua.indexOf("msie 8") > -1) {
        window.external.AddToFavoritesBar(url, title); //IE8
    }
    else if (document.all) {
  try{
   window.external.addFavorite(url, title);
  }catch(e){
   alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
  }
    }
    else if (window.sidebar) {
        window.sidebar.addPanel(title, url, "");
    }
    else {
  alert('您的浏览器不支持,请按 Ctrl+D 手动收藏!');
    }
}