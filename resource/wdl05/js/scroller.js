var Tstart_scroll;
var Tstop_scroll;
var Tscroll_left;
var Tscroll_right;

(function(){
	var srcoll_obj=$_('scroller');
	var contents_0=$_('scroller_contents_0');
	var contents_1=$_('scroller_contents_1');
	
	var speed=20;	//速度(毫秒)
	var space=1;	//每次移动(px)
	var scroll_lock=false;
	var scroll_timer_obj;
	var current_scroll_dir='scroll_right()';
	
	Tstart_scroll=start_scroll;
	Tstop_scroll=stop_scroll;
	Tscroll_left=scroll_left;
	Tscroll_right=scroll_right;
	contents_1.innerHTML=contents_0.innerHTML+contents_0.innerHTML+contents_0.innerHTML;
	start_scroll();
	
	function $_(obj){
		return document.getElementById(obj)?document.getElementById(obj):'';
	}

	function scroll_left(){
		stop_scroll_right();
		if(scroll_lock){
			return;
		}
		current_scroll_dir='scroll_left()';
		scroll_lock=true;
		scroll_timer_obj=setInterval(scroll_left_run, speed);
	}
	
	function stop_scroll_left(){
		clearInterval(scroll_timer_obj);
		scroll_lock=false;
	}
	
	function scroll_left_run(){
		//alert(srcoll_obj.scrollLeft+'---'+contents_0.offsetWidth);
		if(srcoll_obj.scrollLeft<=0){
			srcoll_obj.scrollLeft=srcoll_obj.scrollLeft+contents_0.offsetWidth;
		}
		srcoll_obj.scrollLeft-=space;
	}
	
	function scroll_right(){
		stop_scroll_left();
		clearInterval(scroll_timer_obj);
		if(scroll_lock){
			return;
		}
		current_scroll_dir='scroll_right()';
		scroll_lock=true;
		scroll_right_run();
		scroll_timer_obj=setInterval(scroll_right_run, speed);
	}
	
	function stop_scroll_right(){
		clearInterval(scroll_timer_obj);
		scroll_lock=false;
	}
	
	function stop_scroll(){
		stop_scroll_left();
		stop_scroll_right();
	}
	
	function start_scroll(){
		eval(current_scroll_dir);
	}
	
	function scroll_right_run(){
		if(srcoll_obj.scrollLeft>=contents_0.scrollWidth){
			srcoll_obj.scrollLeft=srcoll_obj.scrollLeft-contents_0.scrollWidth;
		}
		srcoll_obj.scrollLeft+=space;
	}
})()