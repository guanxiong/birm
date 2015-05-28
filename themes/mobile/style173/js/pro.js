$(function(){
		rolltab();									
		roll();
	});
	var timer=null;
	var offset=3000;
	var y=0;	
	var q=-1;
	function rolltab(){
		q++;				
		if(q>3){
			q=0	
		}
		tab(q);
		timer=window.setTimeout(rolltab,offset)			
	}
	function tab(e){
		var t=-(25*e)+"%";										
		$('.mid01_box').css({'-webkit-transform':"translate("+t+")",'-webkit-transition':'500ms linear'} );						
		$('.dot_line').each(function(){
			$(this).find('li').eq(e).addClass('on');
			$(this).find('li').eq(e).siblings().removeClass('on');
		})	
	}
	function roll(){
		$(".roll").each(function(){				
			$(this).swipe({			
				swipeLeft:function(){
					clearTimeout(timer);
					q = $(this).prevAll().length+1;
					tab(q);
					timer = window.setTimeout(rolltab, offset);  
					var i=$(this).index();
					if(i==3){
						return false;
					}else{
						var n=i+1;
						y=25*n;
						var t=-y+"%";					
						$('.mid01_box').css({'-webkit-transform':"translate("+t+")",'-webkit-transition':'500ms linear'} );									
						$('.dot_line li').eq(n).addClass('on');
						$('.dot_line li').eq(n).siblings().removeClass('on')
					}
					
				},
				swipeRight:function() {
					clearTimeout(timer);
					q = $(this).prevAll().length-1;
					if(q==-1){
						return false;
					}
					tab(q);
					timer = window.setTimeout(rolltab, offset);
					var i=$(this).index();
					if(i==0){
						return false;	
					}else{
						var n=i-1;
						y=-25*n;							
						var t=y+"%";					
						$('.mid01_box').css({'-webkit-transform':"translate("+t+")",'-webkit-transition':'500ms linear'} );								
						$('.dot_line li').eq(n).addClass('on');
						$('.dot_line li').eq(n).siblings().removeClass('on')
					}					
				}
			});
		})	
	}