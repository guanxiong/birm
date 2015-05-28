/**
 * http://www.yunlai.cn
 * @author alai
 * @email jpcomputer@163.com
 */
;(function($) {
    $.fn.yl3d = function(options) {
        // Ĭ�ϲ���
        $.fn.yl3d.defaults = {
            speed	: 'auto',       // ת���ٶȣ�ԽСԽ�졣���ˮƽÿ�ƶ��������أ���תһ��ͼƬ��autoΪ�Զ����
			stopEle	: null,			// �����ƶ������Ƿ񴥷�
			startFn	: null,			// ��ȡtouchstart�󶨺���
			moveFn	: null,			// ��ȡtouchmove�󶨺���
			endFn	: null,			// ��ȡtouchend�󶨺���
			V_start : null			// ��start������ֵ
        };
        
        /* ��ʼֵ�̳� */
        var param = $.extend({},$.fn.yl3d.defaults, options);
    	
    	return this.each(function(){
    		var box  = $(this);
            var srcVal = box.find('input[type="hidden"]').val();
            var urls = srcVal.split(",");
			var imageNum = urls.length;
			var speed	= param.speed;

    		var startPoint_x; // mousedown �� touchstart ��ʼλ��X���
    		var startPoint_y; // mousedown �� touchstart ��ʼλ��y���
    		var movePoint_x; // ��ǰ�б任ͼƬ��move��λ��X���
    		var movePoint_y; // ��ǰ�б任ͼƬ��move��λ��y���
			var position_3d = null; //�жϷ���
			var moveStart_3d = true; //�ƶ���ʼ
			
    		var currentImage = 1; // ��ǰ��ʾͼƬ����0��ʼ
    		
    		// auto�ٶȷ�ʽ��ͼƬ����ת�ٶȿ죬�ﵽ�������Ч��
    		if(speed == 'auto'){
    			if(imageNum >= 36){
    				speed = 10;
    			}else if(imageNum < 36 && imageNum >= 24){
    				speed = 14;
    			}else if(imageNum < 24){
    				speed = 18;
    			}
    		}

			// ���¼�
			box.on('mousedown touchstart', function(e) {
                 if (e.type == "touchstart") {
                	 startPoint_x = window.event.touches[0].pageX;
                	 startPoint_y = window.event.touches[0].pageY;
                 } else {
                	 startPoint_x = e.pageX||e.x;
                	 startPoint_y = e.pageX||e.y;
                 }
                 movePoint_x = startPoint_x;
                 movePoint_y = startPoint_y;
				
				//ȡ�������������¼� 
				if(param.stopEle){
					param.stopEle.off('mousedown touchstart');
					param.stopEle.off('mousemove touchmove');
					param.stopEle.off('mouseup touchend mouseout');
				}

 			});

 			box.on('mousemove touchmove', function(e){
                e.preventDefault();
 				if (startPoint_x) {
					 //��ȡ�ƶ���x��yֵ
                     var pageX;
					 var pageY;
                     if (e.type == "touchmove") {
                         pageX = window.event.targetTouches[0].pageX;
                         pageY = window.event.targetTouches[0].pageY;
                     } else {
                         pageX = e.pageX||e.x;
                         pageY = e.pageY||e.y;
                     }
					 //�ж������»��������һ�
					 if(moveStart_3d){
						 if(param.stopEle){
							//trueΪ��ֱ,falseΪˮƽ
 					 		Math.abs(pageY-movePoint_y)>=Math.abs(pageX-movePoint_x) ? position_3d = true : position_3d = false ; 
						 }else{
							position_3d = false; 
						 }
						moveStart_3d = false;
					 }else{
						position_3d = false; 	 
					 }
					 //�ƶ�����
					 if(!position_3d){
						 if(Math.abs(movePoint_x - pageX) >= speed) {
							box.find('img').eq(currentImage-1).css("display","none"); 
							 if (movePoint_x - pageX > 0) {
								 currentImage++;
								 if (currentImage >= imageNum) {
									 currentImage = 1;
								 }
							 } else {
								 currentImage--;
								 if (currentImage < 0) {
									 currentImage = imageNum;
								 }
							 }
							 movePoint_x = pageX;
							box.find('img').eq(currentImage-1).css("display","inline"); 
						 }
					 }else{
						param.V_start(startPoint_y);
						param.stopEle.on('mousedown touchstart',param.startFn);
						param.stopEle.on('mousemove touchmove',param.moveFn);
						param.stopEle.on('mouseup touchend mouseout',param.endFn);
					 }
 				}	
			
			})
			
			box.on('mouseup touchend mouseout', function() {
				//��ʼ��״ֵ̬
 				startPoint_x = null;
				startPoint_y = null;
				movePoint_y	= null;
				movePoint_x = null;
				//����Ĭ���ƶ��ǿ���״̬
				moveStart_3d = true;
				position_3d = null;
				//���������������¼�
				if(param.stopEle){
					param.stopEle.on('mousedown touchstart',param.startFn);
					param.stopEle.on('mousemove touchmove',param.moveFn);
					param.stopEle.on('mouseup touchend mouseout',param.endFn);
				}
      		});

            // ����ͼƬ
            window.onload = imginit();
            function imginit(){
                for(var i=1; i<imageNum; i++){
                    $('<img/>').attr({'src':urls[i]}).appendTo(box);
                }
                box.find('img').hide();
                box.find('img').eq(0).show();
            }

    	});
    };
    
})(jQuery);