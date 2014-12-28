$(document).ready(function(){
    $(".inputbox1").focus(function(){
        $("#loginname").css("border-color","#67ad03");
        $("#loginname").css("background-color","#f0ffda");
    });
    $(".inputbox1").blur(function(){
        $("#loginname").css("border-color","#666");
        $("#loginname").css("background-color","#fff");
    });
    $(".inputbox2").focus(function(){
        $("#loginname").css("border-bottom-color","#67ad03");
        $("#loginpassword").css("border-color","#67ad03");
        $("#loginpassword").css("background-color","#f0ffda");
    });
    $(".inputbox2").blur(function(){
        $("#loginname").css("border-bottom-color","#666");
        $("#loginpassword").css("border-color","#666");
        $("#loginpassword").css("background-color","#fff");
    });
});



function photowalk(div,arr){
    var html = '';
    var box = $("#"+div);
    var box_width = box.width();
    var box_height = box.height();
    var btn_height = box_height - 40;
    if(btn_height>100){
        btn_height = 100;
    }
    var btn_mtop = (box_height - btn_height)/2;
    var box_main_width = box_width - 64-20;
    var box_main_height = box_height - 40;
    
    var len = arr['img'].length;
    var nowid = 0;
    if(len%2 == 0){
        nowid = len/2;
    }else{
        nowid = (len+1)/2 - 1;
    }
    
    var imgbox = new Array();
    for(i=0;i<len;i++){
        imgbox[i] = new Array();
        imgbox[i]['width'] = arr['data']['width'];
        imgbox[i]['height'] = arr['data']['height'];
    }
    
    html += '<style type="text/css">';
    html += '#'+div+'_left{width:20px;height:'+btn_height+'px;overflow:hidden;border:1px solid #ccc;margin-top:'+btn_mtop+'px;margin-left:10px;float:left;background:#e6e6e6;font-size:14px;line-height:'+btn_height+'px;cursor:pointer;}';
    html += '#'+div+'_right{width:20px;height:'+btn_height+'px;overflow:hidden;border:1px solid #ccc;margin-top:'+btn_mtop+'px;margin-right:10px;float:right;background:#e6e6e6;font-size:14px;line-height:'+btn_height+'px;cursor:pointer;}';
    html += '#'+div+'_main{width:'+box_main_width+'px;height:'+box_main_height+'px;float:left;margin-top:20px;margin-left:10px;}';
    html += '#'+div+'_mainin{width:'+box_main_width+'px;height:'+box_main_height+'px;position: absolute;overflow:hidden;}';
    html += '.'+div+'_imgbox{width:'+arr['data']['width']+'px;height:'+arr['data']['height']+'px;position: absolute;cursor:pointer;}';
    html += '#'+div+'_url{width:'+arr['data']['width']+'px;height:'+arr['data']['height']+'px;position: absolute; z-index:150;}';
    html += '#'+div+'_url a{width:'+arr['data']['width']+'px;height:'+arr['data']['height']+'px;display:block;background:#eee;}';
    html += '#'+div+'_txt{width:'+arr['data']['width']+'px;height:'+arr['data']['height']+'px;position: absolute; z-index:150;text-align:center;}';
    html += '#'+div+'_txt a{font:bold 14px/24px "微软雅黑";color:#333;}';
    html += '.'+div+'_m{width:10px;height:40px;overflow:hidden;margin-left:5px;margin-top:'+(btn_height-40)/2+'px;position: absolute;}';
    html += '.'+div+'_ml{width:20px;height:40px;overflow:hidden;position: absolute;font:40px/40px "微软雅黑";color:#999;margin-left:-2px;}';
    html += '.'+div+'_mr{width:20px;height:40px;overflow:hidden;position: absolute;font:40px/40px "微软雅黑";color:#999;margin-left:-11px;}';
    html += '</style>';
    
    html += '<div id="'+div+'_left"><div class="'+div+'_m"><div class="'+div+'_ml">◆</div></div></div>';
    html += '<div id="'+div+'_main">';
    html += '<div id="'+div+'_mainin">';
    for(i=0;i<len;i++){
        html += '<div class="'+div+'_imgbox" id="'+div+'_imgbox'+i+'" rel="'+i+'">';
        html += '<img src="'+arr['img'][i]+'" />';
        html += '</div>';
    }
    html += '<div id="'+div+'_url"><a href="' + arr['url'][nowid] + '" target="_blank"></a></div>';
    html += '<div id="'+div+'_txt"><a href="' + arr['url'][nowid] + '" target="_blank">' + arr['txt'][nowid] + '</a></div>';
    html += '</div>';
    html += '</div>';
    html += '<div id="'+div+'_right"><div class="'+div+'_m"><div class="'+div+'_mr">◆</div></div></div>';
    
    box.append(html);
    var startposx = (box_main_width/2) - (arr['data']['width']/2);
    $("."+div+"_imgbox").css("margin-left", startposx+"px");
    $("#"+div+"_url").css("margin-left", startposx+"px");
    $("#"+div+"_url a").fadeTo(0,0);
    $("#"+div+"_txt").css("margin-left", startposx+"px");
    $("#"+div+"_txt").css("margin-top", arr['data']['height']+"px");
    for(i=0;i<len;i++){
        $("#"+div+"_imgbox"+i).css("z-index",100-Math.abs(i-nowid));        
    }
    $("."+div+"_imgbox").click(function(){
        var mm = $(this).attr("rel");
        imgto(mm);
    });
    $("#"+div+"_left").click(function(){
        var mm = nowid - 1;
        if(mm>=0){
            imgto(mm);
        }        
    });
    $("#"+div+"_right").click(function(){
        var mm = nowid + 1;
        if(mm<len){
            imgto(mm);
        }        
    });
    
    imgto(nowid);
    
    function imgto(n){
        nowid = n;        
        $("#"+div+"_url a").attr("href",arr['url'][n]);
        $("#"+div+"_txt a").attr("href",arr['url'][n]);
        $("#"+div+"_txt a").html(arr['txt'][n]);
        $("#"+div+"_txt").css("margin-left",imgbox[n]['left']+"px");
        $("#"+div+"_txt").fadeTo(0,0);
        for(i=0;i<len;i++){
            var dex = Math.pow(arr['data']['dex'],Math.abs(n-i));
            imgbox[i]['width'] = arr['data']['width']*dex;
            imgbox[i]['height'] = arr['data']['height']*dex;
        }
        
        
        imgbox[n]['left'] = (box_main_width/2) - (imgbox[n]['width']/2);
        
        
        if(n>0){
            for(i=n-1;i>=0;i--){
                imgbox[i]['left'] = imgbox[i+1]['left'] - imgbox[i]['width'] - arr['data']['border'];
            }
        }
            

        for(i=n;i<len;i++){
            if(i>0){
                imgbox[i]['left'] = imgbox[i-1]['left'] + imgbox[i-1]['width'] + arr['data']['border'];
            }            
        }
        $("#"+div+"_txt").stop().animate({
            "margin-left":imgbox[n]['left']+"px",
            opacity: 1
        },arr['data']['speed']);
        for(i=0;i<len;i++){
            $("#"+div+"_imgbox"+i).css("z-index",100-Math.abs(i-nowid));                   
            $("#"+div+"_imgbox"+i).stop().animate({
                "width":imgbox[i]['width']+"px",
                "height":imgbox[i]['height']+"px",
                "margin-left":imgbox[i]['left']+"px",
                "margin-top":((arr['data']['height']-imgbox[i]['height'])/2)+"px"
            },arr['data']['speed']);
            $("#"+div+"_imgbox"+i).children("img").stop().animate({
                "width":imgbox[i]['width']+"px",
                "height":imgbox[i]['height']+"px"
            },arr['data']['speed']);
        }
    }
}