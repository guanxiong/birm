var browser={ 
    versions:function(){ 
    var u = navigator.userAgent, app = navigator.appVersion; 
    return { //移动终端浏览器版本信息 
    trident: u.indexOf('Trident') > -1, //IE内核 
    presto: u.indexOf('Presto') > -1, //opera内核 
    webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核 
    gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核 
    mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端 
    ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端 
    android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器 
    iPhone: u.indexOf('iPhone') > -1 , //是否为iPhone或者QQHD浏览器 
    iPad: u.indexOf('iPad') > -1, //是否iPad 
    webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部 
    }; 
    }(), 
    language:(navigator.browserLanguage || navigator.language).toLowerCase() 
} 
/*document.writeln("语言版本: "+browser.language); 
document.writeln(" 是否为移动终端: "+browser.versions.mobile); 
document.writeln(" ios终端: "+browser.versions.ios); 
document.writeln(" android终端: "+browser.versions.android); 
document.writeln(" 是否为iPhone: "+browser.versions.iPhone); 
document.writeln(" 是否iPad: "+browser.versions.iPad); 
document.writeln(navigator.userAgent);*/ 


var canvas, ctx;
var canvasObj, ctxObj;
var canvasResult, ctxResult;
var iDstW = 150;
var iDstH = 150;
var iXSpeed = 1;
var iYSpeed = 1;
var iLastX = iDstW / 2;
var iLastY = iDstH / 2;
var oImage;
var aMap = [];
var aBitmap;
var mask = document.getElementById("mask");
var fileBox = document.getElementById("file");
var fileObj;
var imgBox = document.getElementById("img");
var mpImg;
var rotate = [,6,3,8];
var i=0, btAld = false;
var autoImg, turnImg;
var value0 = document.getElementById("img0"),
    canvas0 = document.getElementById("canvas0"),
    saveImg0;
// creating canvas and context objects
canvas = document.getElementById('slideshow');
canvasObj = document.getElementById('obj');
canvasResult = document.getElementById("result");
changeBtn = document.getElementById("btn_change");

ctx = canvas.getContext('2d');
ctxObj = canvasObj.getContext('2d');
ctxResult = canvasResult.getContext('2d');

var init = function(){

    fileBox.onchange = function(){
        fileObj = this;
        getFile(fileObj);
        $(".btn_save").show().siblings().hide();
        $(".prompt_choose").show().siblings().hide();
        $(".tools").show();
        $(".now_container").show();
        btAld = false;

        setTimeout(function(){
            autoImg = new Image();
            autoImg.src = canvas.toDataURL("image/jpeg")
        },2000)
            
    }
    changeBtn.onchange = function(){
        fileObj = this;
        getFile(fileObj);
        btAld = false;
        setTimeout(function(){
            autoImg = new Image();
            autoImg.src = canvas.toDataURL("image/jpeg")
        },2000)
    }
    mask.onclick = function(e){
        updateScene(e.offsetX,e.offsetY)
    }
}

var getFile = function(source,turn){
    var file = source.files[0];
    if(window.FileReader) {
        var fr = new FileReader();  
        fr.onloadend = function(e) {

            makePhoto(file,e.target.result,turn);

        };  
        fr.readAsDataURL(file);  
    }
}

var mathSphere = function(px, py) {
    var x = px - iDstW / 2;
    var y = py - iDstH / 2;
    var r = Math.sqrt(x * x + y * y);
    var maxR = iDstW / 2;
    if (r > maxR) return {'x':px, 'y':py};

    var a = Math.atan2(y, x);
    var k = (r / maxR) * (r / maxR) * 0.5 + 0.5;
    var dx = Math.cos(a) * r * k;
    var dy = Math.sin(a) * r * k;
    return {'x': dx + iDstW / 2, 'y': dy + iDstH / 2};
}


var changeiDst = function(type){
    if( type == "up" ){
        iDstW += 10;
        iDstH += 10;
    }
    if( type == "down" ){
        if( iDstW == 10 ){
            return;
        }
        iDstW -= 10;
        iDstH -= 10;
    }
    canvasObj.width = iDstW;
    canvasObj.height = iDstH;
    fileObj && getFile(fileObj);
}

var reset = function(){
    fileObj && getFile(fileObj);
}
var turn = function(i){
    fileObj && getFile(fileObj,rotate[i]);
    setTimeout(function(){
        turnImg = new Image();
        turnImg.src = canvas.toDataURL("image/jpeg")
    },2000)
}


var makePhoto = function(file,url,turn){
    //console.log(file);
    //console.log(url);
    canvasObj.style.left = -999 + "px";
    canvasObj.style.top = -999 + "px";
    var tempImg = new Image(),iw,ih;
    tempImg.src = url;
    tempImg.onload = function(){

        iw = tempImg.width;
        ih = tempImg.height;
        mpImg = new MegaPixImage(file);
        mpImg.render(canvas0, { maxWidth: 720, maxHeight: 2000 });


        if(turn == 6 || turn == 8){
            if(iw < ih){
                $(".canvas_box").css("top", "-100px");
                mpImg.render(canvas, { maxWidth: 2000, maxHeight: 540, orientation: turn });
            }else{
                mpImg.render(canvas, { maxWidth: 2000, maxHeight: 540, orientation: turn });
            }
        }else{
            

            if(browser.versions.iPhone){
                $(".canvas_box").css("top", "-100px");
                mpImg.render(canvas, { maxWidth: 720, maxHeight: 2000, orientation: turn });
            }else{
                if( iw < ih ){
                    $(".canvas_box").css("top", "-100px");
                    mpImg.render(canvas, { maxWidth: 720, orientation: turn });
                }else{
                    mpImg.render(canvas, { maxWidth: 740, orientation: turn });
                }
            }
        }

        aBitmap = ctx.getImageData(0, 0, iDstW, iDstH);
        for (var y = 0; y < iDstH; y++) {
            for (var x = 0; x < iDstW; x++) {
                var t = mathSphere(x, y);
                aMap[(x + y * iDstH) * 2 + 0] = Math.max(Math.min(t.x, iDstW - 1), 0);
                aMap[(x + y * iDstH) * 2 + 1] = Math.max(Math.min(t.y, iDstH - 1), 0);
            }
        }
        
            
    }

}
    

var updateScene = function(ex,ey) {

    // update last coordinates
    iLastX = ex;
    iLastY = ey;

    // shifting of the second object
    canvasObj.style.left = iLastX - Math.floor(iDstW / 2) + 'px';
    canvasObj.style.top = iLastY - (Math.floor(iDstH / 2)) + 'px';

    // draw result Sphere
    var aData = ctx.getImageData(iLastX - Math.ceil(iDstW / 2), iLastY - Math.ceil(iDstH / 2), iDstW, iDstH + 1);
    for (var j = 0; j < iDstH; j++) {
        for (var i = 0; i < iDstW; i++) {
            var u = aMap[(i + j * iDstH) * 2];
            var v = aMap[(i + j * iDstH) * 2 + 1];
            var x = Math.floor(u);
            var y = Math.floor(v);
            var kx = u - x;
            var ky = v - y;
            for (var c = 0; c < 4; c++) {
                aBitmap.data[(i + j * iDstH) * 4 + c] =
                  (aData.data[(x + y * iDstH) * 4 + c] * (1 - kx) + aData.data[((x + 1) + y * iDstH) * 4 + c] * kx) * (1-ky) +
                  (aData.data[(x + (y + 1) * iDstH) * 4 + c] * (1 - kx) + aData.data[((x + 1) + (y + 1) * iDstH) * 4 + c] * kx) * (ky);
            }
        }
    }
    ctxObj.putImageData(aBitmap,0,0);
}
var transformCoordinate = function(canvas, width, height, orientation) {
    switch (orientation) {
      case 5:
      case 6:
      case 7:
      case 8:
        canvas.width = height;
        canvas.height = width;
        break;
      default:
        canvas.width = width;
        canvas.height = height;
    }
    var ctx = canvas.getContext('2d');
    switch (orientation) {
      case 2:
        // horizontal flip
        ctx.translate(width, 0);
        ctx.scale(-1, 1);
        break;
      case 3:
        // 180 rotate left
        ctx.translate(width, height);
        ctx.rotate(Math.PI);
        break;
      case 4:
        // vertical flip
        ctx.translate(0, height);
        ctx.scale(1, -1);
        break;
      case 5:
        // vertical flip + 90 rotate right
        ctx.rotate(0.5 * Math.PI);
        ctx.scale(1, -1);
        break;
      case 6:
        // 90 rotate right
        ctx.rotate(0.5 * Math.PI);
        ctx.translate(0, -height);
        break;
      case 7:
        // horizontal flip + 90 rotate right
        ctx.rotate(0.5 * Math.PI);
        ctx.translate(width, -height);
        ctx.scale(-1, 1);
        break;
      case 8:
        // 90 rotate left
        ctx.rotate(-0.5 * Math.PI);
        ctx.translate(-width, 0);
        break;
      default:
        break;
    }
}

var beautify = function(){
    
    if( btAld ) return;

        var btImg = new Image(),
            btresult = new Image();
        btImg.src = canvas.toDataURL("image/jpeg");
        btImg.onload = function(){
            var m = psLib(btImg).add(
                psLib(btImg).act("高斯模糊",5),"滤色"
            ).act("亮度",-6,1).show();
            btresult.src = m.canvas.toDataURL("image/jpeg");
            //alert(btresult.src);
            btresult.onload = function(){
                ctx.drawImage(btresult,0,0);
                btAld = true;
            }
           
            m.canvas.style.display = 'none';
            
           // postPhoto(m.canvas.toDataURL("image/jpeg"));
            
        }
    
        
    
}


var savePhoto = function(){
    var temp = new Image(),temp2,temp3,temp4;
    temp.src = canvasObj.toDataURL();
    temp.onload = function(){
        temp2 = new Image();
        temp2.src = canvas.toDataURL();
        temp2.onload = function(){
            canvasResult.width = canvas.width;
            canvasResult.height = canvas.height;
            ctxResult.drawImage(temp2,0,0);
            ctxResult.drawImage(temp,0,0,iDstW,iDstH,parseInt(canvasObj.style.left),parseInt(canvasObj.style.top),iDstW,iDstH);

            
            

            saveImg0 = new Image();
            saveImg0.src = canvas0.toDataURL("image/jpeg");
            saveImg0.onload = function(){
                value0.value = saveImg0.src;
            }

            temp3 = new Image();
            temp3.src = canvasResult.toDataURL("image/jpeg");
            temp3.onload = function(){
                console.log(turnImg)
                if(turnImg){
                    postPhoto(temp3.src, autoImg.src, turnImg.src);
                }else{
                    postPhoto(temp3.src, autoImg.src);
                }
                
            }
                
        }
    }
}

var postPhoto = function(data, data0, data1){
   
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data: {
            "photo" : data,
            "photo0" : data0,
            "photo1" : data1,
			"weixin" : 1
        },
        beforeSend: function(){

        },
        success: function(data){
            if(data.ok == 1){
                $("#photo").val(siteurl+"source/modules/nowbig/template/img/"+data.p+".jpg");//动态图片
                $("#lineLink").val(attachurl+'&p='+data.p);//分享到朋友圈动画
				$("#lcode").html(data.lcode);//中奖号码
            }else if(data.ok == 0){
                alert(data.error);
            }
            $(".pop_loading").hide();
        }

    });
}