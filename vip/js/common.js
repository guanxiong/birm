var DM=($.browser.msie||$.browser.mozilla||$.browser.opera)?document.documentElement:document.body;
$new=function(el){
        return document.createElement(el);
};
$empty=function(el){
        return function(){};
};

if($.browser.msie&&$.browser.version<7){
        $IE6=true;
}else{
        $IE6=false;
}
$test=function(t){
        $('#test').html(t);
}
$tests=function(t){
        $('#test').html($('#test').html()+'<br>'+t);
}
/*下拉菜单*/
;
(function($){
        $.fn.extend({
                fancySelect: function(opts){
                        this.options = {
                                even:'enter',							//激活事件
                                autoHide:true,							//点击后自动隐藏
                                onSelcted:$empty						//选中事件
                        };
                        $.extend(true,this.options,opts);
                        var state=true;
                        var _this=this;
                        var ul=$(this).find('ul');
                        var uh=$(ul).height();
                        ul.css({
                                width:$(this).width()-(ul.outerWidth()-ul.width())
                        });
                        //$test(dt);
                        this.show=function(){
                                $(ul).css({
                                        'display':'inline-block',
                                        'height':0,
                                        'overflow-y':'hidden'
                                });
                                var h=uh;
                                if(uh+$(ul).offset().top>DM.scrollTop+document.documentElement.clientHeight&&!($.browser.msie&&$.browser.version<6)){
                                        h=DM.scrollTop+document.documentElement.clientHeight-$(ul).offset().top-5;
                                        if(h<50){
                                                h=50;
                                        }
                                };
                                $(this).find('div').addClass('hover');
				
                                $(ul).animate({
                                        'height':h
                                },'normal','easeOut',function(){
                                        if(h<uh){
                                                $(ul).css({
                                                        'overflow-y':'auto',
                                                        'height':h
                                                });	
                                        //alert($(ul).height()+'/'+uh+'/'+$(ul).css('overflow-y'));
                                        //alert($(ul));
                                        };
                                });
                        };
                        if(this.options.even=='enter'){
                                $(this).mouseenter(function(){
                                        if(state){
                                                _this.show();
                                        }
                                });
                        }else{
                                $(this).click(function(){
                                        if(state){
                                                _this.show();
                                        }
                                });
                        };
                        $(this).mouseleave(function(){
                                state=false;
                                $(this).find('div').removeClass('hover');
                                $($(this).find('ul')).css({
                                        'overflow-y':'hidden'
                                }).slideUp('normal',function(){
                                        state=true;		
                                });
                        });
                        $($(this).find('li')).mouseenter(function(){
                                $(this).addClass('hover');	  
                        }).mouseleave(function(){
                                $(this).removeClass('hover');			  
                        }).click(function(){
                                var span=$(this).parent().parent().find('span');
                                $(this).addClass('selected').siblings().removeClass('selected'); 
                                $(span).attr('alt',$(this).attr('alt'));
                                $(span).html($(this).html());
                                _this.options.onSelcted({
                                        'value':$(this).attr('alt'),
                                        'html':$(this).html(),
                                        'index':$($(_this).find('li')).index($(this))
                                });
                                if(_this.options.autoHide){
                                        state=false;
                                        $(this).parent().css({
                                                'overflow-y':'hidden'
                                        }).slideUp('normal',function(){
                                                state=true;		
                                        }); 
                                }
                        });
                }
        });
})(jQuery);

/*滚动页面*/
	
function pageScrollTo(){
        //$test(DM.scrollTop+'/'+$('.scrollPageNum2').offset().top);
        if(DM.scrollTop>=$('.scrollPageNum1').offset().top){
                //$test($('.pageBtn').css('opacity'));
                if(!$('.pageBtn').attr('name')||$('.pageBtn').attr('name')!=1){
                        if($.browser.msie&&$.browser.version=='6.0'){
                                $('.pageBtn').css({
                                        'opacity':1
                                });
                        }else{
                                $('.pageBtn').animate({
                                        'opacity':1,
                                        'top':150
                                });
                        };
                        $('.pageBtn').css('display','block');
                        $('.pageBtn').attr('name',1);
                }
                if($.browser.msie&&$.browser.version=='6.0'){
                        $('.pageBtn').css('top',DM.scrollTop+150);
                }
        }else{
		
                if($('.pageBtn').attr('name')==1){
                        if($.browser.msie&&$.browser.version=='6.0'){
                                $('.pageBtn').hide();
                        }else{
                                $('.pageBtn').animate({
                                        'opacity':0,
                                        'top':550
                                },function(){
                                        $('.pageBtn').hide();											  
                                });
                        };
                        $('.pageBtn').attr('name',0);
                }
        }
        if(!pageScroll){
                pageNum=getPageNum(0);
        }
};
function scrollPage(num){
        //$('#test').text(num);

        if(pageNum!=num){
                $(DM).scrollTo($('.scrollPageNum'+num),{
                        duration:'normal',
                        easing:'easeIn', 
                        queue:true,
                        onAfter:function(){
                                scrollPageState=true;	
                        }
                });
                pageNum=num;
        }else{
                scrollPageState=true;	
        }
};
function getPageNum(num){
        $('.scrollPageNum').each(function(i){
                if(DM.scrollTop>=$(this).offset().top){
                        //num=$('.scrollPageNum').index($(this))+1;
                        num++;
                };
                //alert(DM.scrollHeight+'/'+(parseInt(DM.scrollTop)+$H));
                if(DM.scrollHeight==(parseInt(DM.scrollTop)+$H)){
                        //num=$('.scrollPageNum').index($(this))+1;
                        num=pageTotal;
                };
        });
        return num;
}

//处理表单元素
function placeholder(){
        if($.browser.msie){
                $(".placeholder").each(function(){
                        var value=this.value;
                        var placeholder=$(this).attr('placeholder');
                        var input=this;
                        if(value==''&&placeholder){
					
                                this.value=placeholder;
                                if($(this).attr('type')=='password'){
                                        $(this).hide();
                                        var el=$new('input');
                                        el.className=this.className;
                                        $(el).attr({
                                                'type':'text',
                                                'value':placeholder
                                        }).insertAfter($(this));
                                        $(el).focus(function(){
                                                $(this).hide();	
                                                if(this.value==placeholder){
                                                        input.value='';
                                                }
                                                $(input).show().focus();
                                        });
                                        $(this).blur(function(){
                                                if(this.value==''){
                                                        $(this).hide();
                                                        el.value=placeholder;
                                                        $(el).show();
                                                };
							
                                        });	
                                }else{
                                        $(this).focus(function(){
                                                if(this.value==placeholder){
                                                        this.value='';
                                                }
                                        });								
                                        $(this).blur(function(){
                                                if(this.value==''){
                                                        this.value=placeholder;
                                                };
							
                                        });	
                                }
                        };
										
                });	
        }
};
/*表单验证*/
function isRegisterUserName(s){
        var patrn=/^[A-Za-z0-9]+$/; 
        if (!patrn.exec(s)) return '只能输入字母或数字';
        return ''
};
function isRegisterEmail(s){
        if (s.search(/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/)!=0) return '请输入正确的email地址';
        return false
};
function isTel(s)
{
        var patrn=/^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/;
        if (!patrn.exec(s)) return  '请输入正确的电话号码';
        return false
} 
/*提示*/
;
(function($){
        $.fn.extend({
                tips: function(tip,opts){
                        this.options = {
                                autoHide:2000,								//自动隐藏时间，0则不自动隐藏
                                focusHide:true,							//获得焦点后隐藏
                                onClick:$empty							//点击后事件
                        };
                        if(!tip)return;
                        opts = opts ? opts : {};		
                        for(key in opts){
                                this.options[key] = opts[key];
                        };
                        var _this=this;
                        var html='<div class="tipsContent">'+tip+'</div><div class="tipsArrow"></div>';
                        var div=$new('div');
                        var p=$.browser.msie?8:2;
                        $(div).addClass('tips').html(html).appendTo($(document.body)).css({
                                'position':'absolute',
                                'z-index':'110001',
                                'opacity':0,
                                'top':$(this).offset().top-$(div).height()-p,
                                'left':$(this).offset().left
                        }).animate({
                                'opacity':1,
                                'top':$(this).offset().top-$(div).height()+$($(div).find('.tipsArrow')).outerHeight()/2-p
                        },'fast','easeIn').click(function(){
                                _this.options.onClick();
                                $(div).animate({
                                        'opacity':0,
                                        'top':$(this).offset().top-$(div).height()-p
                                },'fast','easeIn',function(){
                                        $(div).remove();
                                });
                        });
                        if(this.options.autoHide>0){
                                $(div).delay(this.options.autoHide).animate({
                                        'opacity':0,
                                        'top':$(this).offset().top-$(div).height()-p
                                },'fast','easeIn',function(){
                                        $(div).remove();
                                });
                        };
                        if(this.options.focusHide){
                                $(this).focus(function(){
                                        $(div).stop().animate({
                                                'opacity':0,
                                                'top':$(this).offset().top-$(div).height()-p
                                        },'fast','easeIn',function(){
                                                $(div).remove();
                                        });
                                });
                        };
                }
        });
})(jQuery);

jQuery.cookie = function(name, value, options) {
        if (typeof value != 'undefined') {
                if(typeof options == 'number'){
                        options={
                                expires:options
                        };
                };
                options = options || {};
                if (value === null) {
                        value = '';
                        options = $.extend({}, options);
                        options.expires = -1;
                }
                var expires = '';
                if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
                        var date;
                        if (typeof options.expires == 'number') {
                                date = new Date();
                                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                        } else {
                                date = options.expires;
                        }
                        expires = '; expires=' + date.toUTCString();
                }
                var path = options.path ? '; path=' + (options.path) : '';
                var domain = options.domain ? '; domain=' + (options.domain) : '';
                var secure = options.secure ? '; secure' : '';
                document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
        } else {
                var cookieValue = null;
                if (document.cookie && document.cookie != '') {
                        var cookies = document.cookie.split(';');
                        for (var i = 0; i < cookies.length; i++) {
                                var cookie = jQuery.trim(cookies[i]);
                                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                                        break;
                                }
                        }
                }
                return cookieValue;
        }
};
/*
记录跳转地址
*/
;
function saveRedirect(){
        $.cookie('redirect',window.location.href,{path:'/'});
};
/*
清除跳转地址
*/
;
function clearRedirect(){
        $.cookie('redirect','',{path:'/'});
};