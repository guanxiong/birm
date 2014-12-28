// JavaScript Document

$(window).load(function() {    

	var theWindow        = $(window),
	    $bg              = $("#bg"),
	    aspectRatio      = $bg.width() / $bg.height();
	    			    		
	function resizeBg() {		
		if ( (theWindow.width() / theWindow.height()) < aspectRatio ) {
		    $bg
		    	.removeClass()
		    	.addClass('bgheight');
		} else {
		    $bg
		    	.removeClass()
		    	.addClass('bgwidth');
		}			
	}            			
	theWindow.resize(resizeBg).trigger("resize");
});

$(document).ready(function(){
	
	$('.show-navigation').click(function(){
		$('body,html').animate({
			scrollTop:0
		}, 800, 'easeOutExpo');
		$(this).hide();
		$('.hide-navigation').show();
		$('.navigation').animate({
			top: '60',
		}, 300, 'easeInOutQuad', function(){});
		return false;
	});
	
	$('.hide-navigation').click(function(){
		$(this).hide();
		$('.show-navigation').show();
		$('.navigation').animate({
			top: '-300%',
		}, 300, 'easeInOutQuad', function(){});
		return false;
	});
		
	$('.submenu-item').click(function(){
		$(this).parent().find('.dropdown-item').toggleClass('active-dropdown');
		$(this).parent().find('.submenu').toggle(150);
		return false;
	});
	
	$('.wide-image a').click(function(){
		$(this).parent().parent().find('.wide-active').toggle(100);
	});
	
	$('.update-button').click(function(){
		$(this).parent().find('.page-update-text').toggle(100);
		$(this).parent().find('.update-icon').toggleClass('active-update-icon');
	});
	
	$('.style-changer').click(function(){
		return false;
	});
	
	$('.close-nav, .sidebar-close, .shortcut-close').click(function(){
		snapper.close();
	});
	
	$('.shortcut-search').click(function(){
		$('.sidebar-shortcuts').hide();
		$('.sidebar-search').show();
	});
	
	$('.search-close').click(function(){
		$('.sidebar-search').hide();
		$('.sidebar-shortcuts').show();
	});

	$('.open-nav').click(function(){
		//$(this).toggleClass('remove-sidebar');
		if( snapper.state().state=="left" ){
			snapper.close();
		} else {
			snapper.open('left');
		}
		return false;
	});
	
	$('.wide-image').click(function(){
		$(this).parent().find('.wide-item-content').toggle(50);
		return false;
	});
	
	var snapper = new Snap({
	  element: document.getElementById('content')
	});

	$('.deploy-sidebar').click(function(){
		//$(this).toggleClass('remove-sidebar');
		if( snapper.state().state=="left" ){
			snapper.close();
		} else {
			snapper.open('left');
		}
		return false;
	});
	

	

});