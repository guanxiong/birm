/**
 * 右下角弹框组件
 */
(function($){
	$.messagetip = {
		show:function(opt){
			opt= $.extend({
				header : '',
                title : '消息提醒',
                content : '&nbsp'
            },opt);
			var html = '<div class="message-tip">'+
						'<div class="header"><a href="javascript:;" class="c">关闭</a><h2>'+opt.header+'</h2></div>'+
							'<div class="content">'+
								'<dl>'+
									'<dt><a href="javascript:;">'+opt.title+'</a></dt>'+
									'<dd>'+opt.content+'</dd>'+
								'</dl>'+
							'</div>'+
				       '</div>';
			var $messageTip = $(html).appendTo(document.body).css('z-index', 3000).hide();
			$messageTip.find('.c').click(function(){
				$messageTip.remove();
			});
			$messageTip.slideDown('slow');
		}
	};
})(jQuery);
