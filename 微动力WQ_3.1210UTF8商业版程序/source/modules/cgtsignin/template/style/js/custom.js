// JavaScript Document

$(document).ready(function(){
	
	$('.close-nav').click(function(){
		snapper.close();
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