    $(function(){
        //字符串截取
    	$(".contBorder p[strData]").each(function(){
            var _this = $(this);
            _this.html($(this).attr("strData"));
            if(_this.height() <= 75)
    		{
            	_this.parent().next().hide();
            }
            if(_this.height() > 75)
    		{
            	_this.parent().css({"height":"75px","overflow":"hidden"});
            }
        })
        $(".contBorder p.more").click(function(){
        	var descDiv = $(this).prev();
        	descDiv.attr('style','');
        	descDiv.height("auto");
        	descDiv.css({"overflow":"none"});
        	descDiv.find('p').html(descDiv.find('p').attr("strData"));
            $(this).hide();
        });

        var mySwiper = $('.swiper-container').swiper({
            mode:'horizontal',
            pagination: '.pagination',
            autoplayStopOnLast:false,
            grabCursor: true,
            paginationClickable: true
        });
        
        $(".pagination").append('<p class="totalNum"><img src="http://mat1.gtimg.com/house/searchhouse/product/amount.png"><span>'+($(".swiper-wrapper .swiper-slide").length)+'</span><span>张</span></p>');

        //判断字符串
        var houseStr = $("#houseCon").html();
        $("#houseCon").html(cutstr ($("#houseCon").attr("strData"),62));
})

    function cutstr (str, len) {
        str = str+'';
        var str_length = 0;
        var str_len = 0;
        str_cut = new String();
        str_len = str.length;
        var alllength = str.match(/[^ -~]/g) == null ? str.length : str.length + str.match(/[^ -~]/g).length ;
        var isch = false;//是否是中文
        for(var i = 0;i<str_len;i++){
            a = str.charAt(i);
            str_length++;
            if(a.length > 4){
                //中文字符的长度经编码之后大于4
                str_length++;
                isch = true;
            }else{
                isch = false;
            }
            str_cut = str_cut.concat(a);
            if(str_length>=len){
                if(isch){
                    if(alllength > (parseInt(len)+1)){
                        str_cut = str_cut.concat("...");
                    }
                }else{
                    if(alllength > len){
                        str_cut = str_cut.concat("...");
                    }
                }
                return str_cut;
            }

        }
        //如果给定字符串小于指定长度，则返回源字符串；
        if(str_length<=len){
            return  str;
        }
    }
