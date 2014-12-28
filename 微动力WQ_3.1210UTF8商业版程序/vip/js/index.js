$(function(){

	//FAQ
	$("#FAQ h3").mouseover(function(){
		$(this).css({"color":"#000"});
	}).mouseout(function(){
		$(this).css({"color":""});
	}).click(function(){
		$("#FAQ").find("div").slideUp("fast");
		$(this).next("div").slideDown("fast");
	});
	
	
});
