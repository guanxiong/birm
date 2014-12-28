// JavaScript Document
$(function(){
	
	$('#sub_a').click(function(){
		$('#bd_zhezhao').show();
		$('#facebox').fadeIn();	
	})
	$('#facebox a.close').click(function(){
		$('#bd_zhezhao').hide();
		$('#facebox').fadeOut();	
	})
	//
	$('.bd_subnav_r span a.span_list').mouseover(function(){
		$('#bd_nav').addClass('show_bd_nav').animate({height:'60px'},200);
	})
	$('.bd_subnav_r span a.span_phone').mouseover(function(){
		$('.bd_subnav_r span.span_tel').animate({width:'163px'},200);
	})
	$('#bd_subnav').mouseleave(function(){
		$('.bd_subnav_r span.span_tel').animate({width:'0'},200);
	})
	//主导航效果
		
		var $liCur1 = $("#ul_nav li.cur"),
		  curP1 = $liCur1.position().left,
		  curW1 = $liCur1.outerWidth(true);
		 var $slider = $(".back"),
		  $navBox = $("#bd_nav"),
		 $targetEle = $("#ul_nav li a");
		 $slider.stop().animate({
		  "left":curP1,
		  "width":curW1
		});	
		$targetEle.mouseenter(function () {
		  var $_parent = $(this).parent(),
			_width = $_parent.outerWidth(true),
			posL = $_parent.position().left;
		  $slider.stop(true, true).animate({
			"left":posL,
			"width":_width
		  }, "fast");
		});
		$navBox.mouseleave(function (cur, wid) {
		   var $liCur = $("#ul_nav li.cur"),
		  curP = $liCur.position().left,
		  curW = $liCur.outerWidth(true);
		  $slider.stop().animate({
		  "left":curP,
		  "width":curW
		});
		});


	$('.freeinhere_nav ul li').click(function(){
		var mycon_index = $('.mycon').eq($(this).index());//使浮窗菜单同内容块相关联，产生对应关系
		$('html,body').animate({scrollTop:mycon_index.offset().top-40},500);//定位动画效果,时间可通过调整数值自行控制
	})
	
	
			
	$(window).scroll(function(){//定义页面滚动函数
		function get_scrollTop_of_body(){ //scrollTop能力检测函数
			var scrollTop; 
			if(typeof window.pageYOffset != 'undefined'){ 
			scrollTop = window.pageYOffset; 
			} 
			else 
			if(typeof document.compatMode != 'undefined' && 
			document.compatMode != 'BackCompat'){ 
			scrollTop = document.documentElement.scrollTop; 
			} 
			else 
			if(typeof document.body != 'undefined'){ 
			scrollTop = document.body.scrollTop; 
			} 
			return scrollTop; 
		}
		var numLi; //声明位置区间信号量
		var myScrollTop = parseInt(get_scrollTop_of_body());//获取ScrollTop的值
		//console.log(myScrollTop);
		if( myScrollTop >= 0 && myScrollTop <560){//信号量预判内容块区间
				$('#bd_top').slideDown(300);
				//$('#bd_subnav').fadeOut(500);
				$('#bd_nav').css({height:'60px'});
				$('#bd_nav').removeClass('show_bd_nav');
			}else{
				$('#bd_nav').css({height:'0'});
				//$('#bd_subnav').fadeIn(100);
				$('#bd_top').slideUp(300);
			}
			if(myScrollTop >= 1660 && myScrollTop <4562){
				$('.freeinhere_nav').fadeIn(500);
				}
			var nowBdLi;
			var backLeft;
			var backWidth;
			var numLi1;
			if( myScrollTop >= 0 && myScrollTop <1661){
				numLi1 = 0;
			}else if( myScrollTop >= 1661 && myScrollTop <4871){
				numLi1 = 1;
			}else if( myScrollTop >= 4871 && myScrollTop <5321){
				numLi1 = 2;
			}else if( myScrollTop >= 5321 && myScrollTop <6849){
				numLi1 = 3;
			}else if( myScrollTop >= 6849 && myScrollTop <7100){
				numLi1 = 4;
			}else{numLi1 = 4;}
			nowBdLi = $('#bd_nav li').eq(numLi1);
			backLeft = nowBdLi.position().left;
			backWidth = nowBdLi.outerWidth(true);
			nowBdLi.addClass('cur').siblings('li').removeClass('cur');
			$slider.css({"left" : backLeft,"width" : backWidth});
				
				
				
			if( myScrollTop >= 1660 && myScrollTop <2364){
				numLi = 0;
			}else if( myScrollTop >= 2364 && myScrollTop <3050){
				numLi = 1;
			}else if( myScrollTop >= 3050 && myScrollTop <3610){
				numLi = 2;
			}else if( myScrollTop >= 3610 && myScrollTop <4238){
				numLi = 3;
			}else if( myScrollTop >= 4238 && myScrollTop <4562){
				numLi = 4;
			}else{
				$('.freeinhere_nav').fadeOut(500);
			}
		$('.freeinhere_nav ul li').eq(numLi).addClass('current').siblings().removeClass('current');//滚动内容块区间改变对应浮窗菜单样式
	})
		
	//*********************************轮播一
	
	var num_01 = 1; //初始化
	var timer_01;
	$('ol.ol_li li').mouseover(function(){
		$(this).addClass('current').siblings().removeClass('current');
		$('ul.teb_out li').hide();
		$('ul.teb_out li').eq($(this).index()).fadeIn();
		num_01 = $(this).index()+1;
	})
		
	function autoplay_01(){
			if(num_01 == 4){
				num_01 = 0;
			}
			$('ol.ol_li li').eq(num_01).addClass('current').siblings().removeClass();
			$('ul.teb_out li').eq(num_01).fadeIn().siblings().hide();
			num_01++; 
		}
		timer_01 = setInterval(autoplay_01,8000);  
		$('#bd_cont_04 li').mouseover(function(){
			clearInterval(timer_01);
		}) .mouseout(function(){
			timer_01 = setInterval(autoplay_01,8000); 
		})

	//*********************************	
	
	//*********************************轮播二
	$('.build_cont span a.build_goleft').hide();
	var goLeft = $('#bd_cont_05 .build_goleft');
	var goRight = $('#bd_cont_05 .build_goright');
	$('#bd_cont_05 ol li').click(function(){
		var nowIndex = $(this).index();
		$(this).addClass('current').siblings().removeClass('current');
		if(nowIndex == 0){
			goLeft.hide();
			goRight.show();
		}else if(nowIndex == 2){
			goLeft.show();
			goRight.hide();
		}else{
			goLeft.show();
			goRight.show();
		}
		$('#bd_cont_05 ul.build_ul').stop().animate({left:nowIndex * (-890)},500);
	})
	$('#bd_cont_05 .build_goright').click(function(){
		var nowLeft = $('#bd_cont_05 ul.build_ul').css('left') ;
		var index = parseInt(nowLeft.substring(0,nowLeft.length-2));
		var nowIndex = index / (-890)+1;
		if(nowIndex == 2){
			goLeft.show();
			goRight.hide();
		}else{
			goLeft.show();
			goRight.show();
		}
		if(index > -1780){
			$('#bd_cont_05 ul.build_ul').stop().animate({left:index - 890},200);
			$('#bd_cont_05 ol li').eq(nowIndex).addClass('current').siblings().removeClass('current');
		}
	});
	$('#bd_cont_05 .build_goleft').click(function(){
		var nowLeft = $('#bd_cont_05 ul.build_ul').css('left') ;
		var index = parseInt(nowLeft.substring(0,nowLeft.length-2));
		var nowIndex = index / (-890)-1;
		if(nowIndex == 0){
			goLeft.hide();
			goRight.show();
		}else{
			goLeft.show();
			goRight.show();
		}
		if(index < 0){
			$('#bd_cont_05 ul.build_ul').stop().animate({left:index + 890},200);
			$('#bd_cont_05 ol li').eq(nowIndex).addClass('current').siblings().removeClass('current');
		}
	})
	//*********************************	
	
	//*********************************轮播三
	var goLeft1 = $('#bd_cont_06 .build_goleft');
	var goRight1 = $('#bd_cont_06 .build_goright');
	$('#bd_cont_06 ol li').click(function(){
		var nowIndex=$(this).index();
		$(this).addClass('current').siblings().removeClass('current');
		if(nowIndex == 0){
			goLeft1.hide();
			goRight1.show();
		}else if(nowIndex == 3){
			goLeft1.show();
			goRight1.hide();
		}else{
			goLeft1.show();
			goRight1.show();
		}
		$('#bd_cont_06 ul.build_ul').stop().animate({left:nowIndex * (-890)},500);
	})
	$('#bd_cont_06 .build_goright').click(function(){
		var nowLeft = $('#bd_cont_06 ul.build_ul').css('left') ;
		var index = parseInt(nowLeft.substring(0,nowLeft.length-2));
		var nowIndex = index / (-890)+1;
		if(nowIndex == 3){
			goLeft1.show();
			goRight1.hide();
		}else{
			goLeft1.show();
			goRight1.show();
		}
		if(index > -2670){
			$('#bd_cont_06 ul.build_ul').stop().animate({left:index - 890},200);
			$('#bd_cont_06 ol li').eq(nowIndex).addClass('current').siblings().removeClass('current');
		}
	});
	$('#bd_cont_06 .build_goleft').click(function(){
		var nowLeft = $('#bd_cont_06 ul.build_ul').css('left') ;
		var index = parseInt(nowLeft.substring(0,nowLeft.length-2));
		var nowIndex = index / (-890)-1;
		if(nowIndex == 0){
			goLeft1.hide();
			goRight1.show();
		}else{
			goLeft1.show();
			goRight1.show();
		}
		if(index < 0){
			$('#bd_cont_06 ul.build_ul').stop().animate({left:index + 890},200);
			$('#bd_cont_06 ol li').eq(nowIndex).addClass('current').siblings().removeClass('current');
		}
	})
	//*********************************	

	
	//页内锚定位
    $('a[href*=#]').click(function() {
        if (location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') && location.hostname == this.hostname) {
            var $target = $(this.hash);
            $target = $target.length && $target || $('[name=' + this.hash.slice(1) + ']');
            if ($target.length) {
                var targetOffset = $target.offset().top;
                $('html,body').animate({
                    scrollTop: targetOffset-60
                },
                1000);
                return false;
            }
        }
    });
	$body = (window.opera) ? (document.compatMode == "CSS1Compat" ? $('html') : $('body')) : $('html,body');



// 返回顶部
    var $backToTopTxt = "返回顶部", $backToTopEle = $('<div class="backToTop"></div>').appendTo($("body"))
        .attr("title", $backToTopTxt).click(function() {
            $("html, body").animate({ scrollTop: 0 }, 1000);
    }), $backToTopFun = function() {
        var st = $(document).scrollTop(), winh = $(window).height();
        (st > 0)? $backToTopEle.show(): $backToTopEle.hide();    
        //IE6下的定位
        if (!window.XMLHttpRequest) {
            $backToTopEle.css("top", st + winh - 166);    
        }
    };
    $(window).bind("scroll", $backToTopFun);
    $(function() { $backToTopFun(); });
	
	$(".backToTop").hover(function(){
    $(this).stop().fadeTo("fast",1);
  },function(){
	$(this).stop().fadeTo("fast",0.6);
  });

	$("#bd_cont_08 ul li").hover(function(){
    $(this).stop().fadeTo("fast",1);
  },function(){
	$(this).stop().fadeTo("fast",0.6);
  });


})


$(document).ready(function(){
	// Cache the Window object
	$window = $(window);      
   $('div[data-type="background"]').each(function(){
     var $bgobj = $(this); // assigning the object                    
      $(window).scroll(function() {                    
		// Scroll the background at var speed
		// the yPos is a negative value because we're scrolling it UP!								
		var yPos = -($window.scrollTop() / $bgobj.data('speed')); 
		// Put together our final background position
		var coords = '50% '+ yPos + 'px';
		// Move the background
		$bgobj.css({ backgroundPosition: coords });
}); // window scroll Ends

 });	

});



