/*
** ����ֵ
*/
	/* 
	** ҳ���л���Ч������ 
	*/
var Msize = $(".m-page").size(), 	//ҳ�����Ŀ
	page_n			= 1,			//��ʼҳ��λ��
	initP			= null,			//��ֵ����ֵ
	moveP			= null,			//ÿ�λ�ȡ����ֵ
	firstP			= null,			//��һ�λ�ȡ��ֵ
	newM			= null,			//���¼��صĸ���
	p_b				= null,			//�������ֵ
	indexP			= null, 		//������ҳ����ֱ����ת�����һҳ
	move			= null,			//�����ܻ���ҳ��
	start			= true, 		//���ƶ�����ʼ
	startM			= null,			//��ʼ�ƶ�
	position		= null,			//����ֵ
	DNmove			= false,		//������������ҳ���л�
	mapS			= null,			//��ͼ����ֵ
	
	/*
	** �������ܵĿ���
	*/
	audio_switch_btn= true,			//�������ؿ���ֵ
	audio_btn		= true,			//�����������
	audio_loop		= false,		//����ѭ��
	audioTime		= null,         //����������ʱ
	audio_interval	= null,			//����ѭ��������
	audio_start		= null,			//�����������
	audio_stop		= null,			//�����Ƿ���ֹͣ
	mousedown		= null;			//PC��������갴�»�ȡֵ


/* 
** ��ҳ�л� ����Ԫ��fixed ����body�߶� 
*/
	var v_h	= null;		//��¼�豸�ĸ߶�
	
	function init_pageH(){
		var fn_h = function() {
			if(document.compatMode == "BackCompat")
				var Node = document.body;
			else
				var Node = document.documentElement;
			 return Math.max(Node.scrollHeight,Node.clientHeight);
		}
		var page_h = fn_h();
		var m_h = $(".m-page").height();
		page_h >= m_h ? v_h = page_h : v_h = m_h ;
		
		//���ø���ģ��ҳ��ĸ߶ȣ���չ��������Ļ�߶�
		$(".m-page").height(v_h); 	
		$(".p-index").height(v_h);
		
	}(init_pageH());
	
/*
**ģ���л�ҳ���Ч��
*/
	//���¼�
	$(".m-page").on('mousedown touchstart',page_touchstart);
	$(".m-page").on('mousemove touchmove',page_touchmove);
	$(".m-page").on('mouseup touchend mouseout',page_touchend);

	//��������갴�£���ʼ����
	function page_touchstart(e){
		if (e.type == "touchstart") {
			initP = window.event.touches[0].pageY;
		} else {
			initP = e.y || e.pageY;
			mousedown = true;
		}
		firstP = initP;	
	};
	
	//�����ȡ������ֵ
	function V_start(val){
		initP = val;
		mousedown = true;
		firstP = initP;		
	};
	
	//�����ƶ�������ƶ�����ʼ����
	function page_touchmove(e){
		e.preventDefault();

		//�ж��Ƿ�ʼ�������ƶ��л�ȡֵ
		if(start||startM){
			startM = true;
			if (e.type == "touchmove") {
				moveP = window.event.touches[0].pageY;
			} else { 
				if(mousedown) moveP = e.y || e.pageY;
			}
			page_n == 1 ? indexP = false : indexP = true ;	//false Ϊ���ǵ�һҳ trueΪ��һҳ
		}
		
		//����һ��ҳ�濪ʼ�ƶ�
		if(moveP&&startM){
			
			//�жϷ�����һ��ҳ����ֿ�ʼ�ƶ�
			if(!p_b){
				p_b = true;
				position = moveP - initP > 0 ? true : false;	//true Ϊ���»��� false Ϊ���ϻ���
				if(position){
				//�����ƶ�
					if(indexP){								
						if( page_n == 1 ) newM = Msize ;
						else newM = page_n - 1 ;
						$(".m-page").eq(newM-1).addClass("active").css("top",-v_h)
						move = true ;
					}else{
						move = false;
					}
							
				}else{
				//�����ƶ�
					if( page_n == Msize ) newM = 1 ;
					else newM = page_n + 1 ;
					$(".m-page").eq(newM-1).addClass("active").css("top",v_h);
					move = true ;
				} 
			}
			
			//�����ƶ�����ҳ���ֵ
			if(!DNmove){
				//��������ҳ�滬��
				if(move){				
					
					//��������
					if($("#car_audio").length>0&&audio_switch_btn&&Math.abs(moveP - firstP)>100){
						$("#car_audio")[0].play();
						audio_loop = true;
					}
				
					//�ƶ�������ҳ���ֵ��top��
					start = false;
					var topV = parseInt($(".m-page").eq(newM-1).css("top"));
					$(".m-page").eq(newM-1).css({'top':topV+moveP-initP});	
					initP = moveP;
				}else{
					moveP = null;	
				}
			}else{
				moveP = null;	
			}
		}
	};

	//����������������������뿪Ԫ�أ���ʼ����
	function page_touchend(e){	
			
		//��������ҳ��
		startM =null;
		p_b = false;
		
		//�ر�����
		 audio_close();
		
		//�ж��ƶ��ķ���
		var move_p;	
		position ? move_p = moveP - firstP > 100 : move_p = firstP - moveP > 100 ;
		if(move){
			//�л�ҳ��(�ƶ��ɹ�)
			if( move_p && Math.abs(moveP) >5 ){	
				$(".m-page").eq(newM-1).animate({'top':0},300,"easeOutSine",function(){
					/*
					** �л��ɹ��ص��ĺ���
					*/
					success();
				})
			//����ҳ��(�ƶ�ʧ��)
			}else if (Math.abs(moveP) >=5){	//ҳ���˻�ȥ
				position ? $(".m-page").eq(newM-1).animate({'top':-v_h},100,"easeOutSine") : $(".m-page").eq(newM-1).animate({'top':v_h},100,"easeOutSine");
				$(".m-page").eq(newM-1).removeClass("active");
				start = true;
			}
		}
		/* ��ʼ��ֵ */
		initP		= null,			//��ֵ����ֵ
		moveP		= null,			//ÿ�λ�ȡ����ֵ
		firstP		= null,			//��һ�λ�ȡ��ֵ
		mousedown	= null;			//ȡ����갴�µĿ���ֵ
	};
/*
** �л��ɹ��ĺ���
*/
	function success(){
		/*
		** �л��ɹ��ص��ĺ���
		*/							
		//����ҳ��ĳ���
		$(".m-page").eq(page_n-1).removeClass("show active").addClass("hide");
		$(".m-page").eq(newM-1).removeClass("active hide").addClass("show");
		
		
		//��������ҳ���ƶ��Ŀ���ֵ
		page_n = newM;
		start = true;
		
		//��ͼ����
		if($(".m-page").eq(page_n-1).find(".ylMap").length>0&&!mapS){
			if(!mapS) mapS = new ylmap.init;
		}
		
		//txt���ı�����Ӧ����
		txtExtand();
	
		//ҳ���л���Ƶ����ֹͣ
		if($('.m-video').find("video")[0]!=undefined){$('.m-video').find("video")[0].pause()};
		
		//�ı�����
		$(".m-txt").removeClass("open");	
	}

	//�ر�����
		function audio_close(){
			if(audio_btn&&audio_loop){
				audio_btn =false;
				audioTime = Number($("#car_audio")[0].duration-$("#car_audio")[0].currentTime)*1000;	
				if(audioTime<0) audioTime=0
				
				if(!isNaN(audioTime)&&audioTime!=0){
					setTimeout(
						function(){	
							audioTime = null;
							$("#car_audio")[0].pause();
							$("#car_audio")[0].currentTime = 0;
							audio_btn = true;	
						},audioTime);
				}else{
					audio_interval = setInterval(function(){
						if(!isNaN($("#car_audio")[0].duration)){
							if($("#car_audio")[0].currentTime == $("#car_audio")[0].duration){
								$("#car_audio")[0].currentTime = 0;	
								clearInterval(audio_interval);
								audio_btn = true;
							}
						}
					},200)	
				}
				
			}
		}
	
	//ҳ����������
	$(function(){
		//��ȡ����Ԫ��
		var btn_au = $(".fn-audio").find(".btn");
		
		//�󶨵���¼�
		btn_au.on('click touchstart',audio_switch);
		function audio_switch(evt){
			evt.preventDefault();
			evt.stopPropagation();
			if($("#car_audio")==undefined){
				return;
			}
			if(audio_switch_btn){
				//�ر�����
				$("#car_audio")[0].pause();
				audio_switch_btn = false;
				$("#car_audio")[0].currentTime = 0;
				btn_au.find("img").eq(0).css("display","none");
				btn_au.find("img").eq(1).css("display","inline");
			}
			//��������
			else{
				audio_switch_btn = true;
				btn_au.find("img").eq(1).css("display","none");
				btn_au.find("img").eq(0).css("display","inline");
			}
		}
	})

/*
**�ı�չ��Ч��
*/
	//�жϸ��ı��Ƿ�չ��
	function txtExtand(){
		$(".m-txt").each(function(){
			var txt 	= $(this).find(".wtxt");
			var txtH	= txt.height();
			var hH		= parseInt(txt.children().eq(0).height())+parseInt(txt.children().eq(0).css("margin-bottom"));
			var pH		= txt.find("p").height();
			if(pH+hH<txtH){
				$(this).addClass("hide_poniter");
			}else{
				$(this).removeClass("hide_poniter");
			}
		})
	}(txtExtand());

	//���ı��л�
	$(function(){
		$(".m-txt").on('click',extant)
		function extant(){
			if(!$(this).hasClass("hide_poniter")) $(this).toggleClass("open");
		}	
	});



/*
**�豸��ת��ʾ
*/
/*	$(function(){
		var bd = $(document.body);
		window.addEventListener('onorientationchange' in window ? 'orientationchange' : 'resize', _orientationchange, false);
		function _orientationchange() {
			scrollTo(0, 1);
			switch(window.orientation){
				case 0:		//����
					$(function(){
						bd.addClass("landscape").removeClass("portrait");
						init_pageH();
					})
					break;
				case 180:	//����
					$(function(){
						bd.addClass("landscape").removeClass("portrait");	
						init_pageH();
					})
					break;
				case -90: 	//����
					$(function(){
						init_pageH();
						bd.addClass("portrait").removeClass("landscape");
						if($(".m-video video")[0].paused)
							alert("�������鿴ҳ�棬Ч������");
					})
					break;
				case 90: 	//����
					$(function(){
						init_pageH();
						bd.addClass("portrait").removeClass("landscape");
						if($(".m-video video")[0].paused)
							alert("�������鿴ҳ�棬Ч������");
					})
					break;
			}
		}
		window.onload = _orientationchange();
	});
*/

/*
** ҳ�����ݼ���loading��ʾ
*/
	//��ʾ
	function loadingPageShow(){
		$('.pageLoading').show();	
	}
	//����
	function loadingPageHide(){
		$('.pageLoading').hide();	
	}

/*
** ҳ����س�ʼ��
*/
	function initPage(){
		//��ʼ��һ��ҳ��
		$(".m-page").addClass("hide").eq(page_n-1).addClass("show").removeClass("hide");
		
		//��ʼ����ͼ
		if($(".m-page").eq(page_n-1).find(".ylMap").length>0&&!mapS){
			mapS = new ylmap.init;
		}
		
		//�ж������Ƿ����
		if($("#car_audio").length>0&&!isNaN($("#car_audio")[0].currentTime)){
			audio_start = true;
		}
		
		//PC��ͼƬ�����������ק
		$(document.body).find("img").on("mousedown",function(e){
			e.preventDefault();
		})	
		
		//��ť����ı仯
		$('.btn-boder-color').click(function(){
			if(!$(this).hasClass("open"))	$(this).addClass('open');
			setTimeout(function(){
				$('.btn-boder-color').removeClass('open');	
			},600)
			
		})
		
	}(initPage());



/**
 * loadingͼ������
 * string type loading:���ּ���ͼƬ��end ��������ͼƬ
 */
function loading(type){
	if('loading'==type){
		$('.loading').css({display:'block'});
	}else if('end'==type){
		$('.loading').css({display:'none'});
	}
}