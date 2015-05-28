$.fn.smartFloat = function() {
	var position = function(element) {
		var top = element.position().top, pos = element.css("position");
		$(window).scroll(function() {
			var scrolls = $(this).scrollTop();
			if (scrolls > top) { //如果滚动到页面超出了当前元素element的相对页面顶部的高度
				if (window.XMLHttpRequest) { //如果不是ie6
					element.css({
						position: "fixed",
						top:0
					}).addClass("shadow");	
				} else { //如果是ie6
					element.css({
						top: scrolls
					});	
				}
			}else {
				element.css({
					position: pos,
					top: top
				}).removeClass("shadow");	
			}
		});
	};
	return $(this).each(function() {
		position($(this));						 
	});
};
$(function(){
	//$("#tip").smartFloat();
	$("#ssmm").smartFloat();
});