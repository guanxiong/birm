function setWidth(uiWidth, uiHeight){
    var setDensitydpi = function(spec){
        var dpr = window.devicePixelRatio,
            deviceWidth = 0,
            getTargetDensitydpi = 0,
            temContent = "";
        deviceWidth = dpr == 2 ? 720 : ( dpr == 1.5 ? 480 : ( dpr == 1 ? 320 : 240 ) );
        getTargetDensitydpi = uiWidth / deviceWidth * dpr * 160;
        temContent = 'target-densitydpi=' + getTargetDensitydpi + ', width=device-width , user-scalable=no' ;
        return temContent;
    };
    var setDeviceWidth = function(){
        return 'target-densitydpi=device-dpi, width='+uiWidth +', user-scalable=no';
    };

    var setZoom = function(){
        $("body").hide();
        setTimeout(function(){
            $("body").fadeIn();
        },300);
        $(function(){
            //$("body").css("zoom",Math.max(document.documentElement.clientWidth,screen.width)/uiWidth);
            $(".wrap").css("WebkitTransform", "scale(" + Math.max(document.documentElement.clientWidth,screen.width)/uiWidth + ")");
            $("body").css({"height": Math.max(document.documentElement.clientWidth,screen.width)/uiWidth * uiHeight, "overflow": "hidden"})
        });
        return 'target-densitydpi=device-dpi, width=device-width , user-scalable=no';
    };

    var vp = document.createElement("meta");
    vp.setAttribute("name","viewport");
    var vpContent = "";
    var ua = navigator.userAgent;

    
    vpContent = setDeviceWidth();
    ( /Android/i.test(ua) && /(2\.[1|2|3]\.[\d])/i.test(ua) ) && ( vpContent = setDensitydpi() );
    ( /Android/i.test(ua) && /(4\.[0|1|2|3|4]\.[\d])/i.test(ua) ) && ( vpContent = setZoom() );
    vp.setAttribute( 'content', vpContent);
    document.getElementsByTagName('head')[0].appendChild(vp);

    
}



function resize(){
    var w = $(window).width();
    if( w >= 1490 ) return false;
    var scale =  w / 1770;
    $(".wrap").css({
        "zoom":scale,
        "MozTransform":"scale("+ scale +")",
        "WebkitTransform":"scale("+ scale +")",
        "OTransform":"scale("+ scale +")"
    });
    $("body").css({"height": 768*scale, "overfolw":"hidden"})
    $("html,body").css("overfolw-x","hidden");
}



function signboard(){
    var light = $(".signboard .light");
    setInterval(function(){
        if( light.is(":hidden") ){
            light.fadeIn();
        }else{
            light.fadeOut();
        }
    }, 600)
    function star(){
        var star1 = $(".star1_on"),
            star2 = $(".star2_on"),
            star3 = $(".star3_on"),
            star_on = $(".star_on");
        setTimeout(function(){ star1.show(); },100);
        setTimeout(function(){ star2.show(); },400);
        setTimeout(function(){ star3.show(); },700);
        setTimeout(function(){ star_on.hide(); },1400);
    }
    
    setInterval(function(){
        star();
    },2000)
}
$(function(){
    signboard();
})


