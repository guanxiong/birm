var M = {
    baseDomain: "http://" + document.domain,
    version: "704039",
    doc: document,
    win: window,
    w: document.body.offsetWidth,
    h: document.body.offsetHeight,
    urlQuery: function (name) {
        var href = location.search;
        href = href.replace(/#[^&]*$/, '');
        var _index = href.indexOf(name);
        if (_index != -1) {
            var a = href.substr(_index);
            var b = new Array();
            if (a.indexOf("&") == -1) {
                b = a.split("=");
            } else {
                b = a.substr(0, a.indexOf("&")).split("=");
            }
            return b[1];
        } else {
            return "";
        }
    },
    doLogin : function(){
        var iframe_url = M.baseDomain+"/mobile.php?act=module&name="+module+"&weid="+weid+"&do=login";
        M.doFrame(iframe_url);
    },
    doFrame : function(url){
        if($("#tool_bar_bg").size()){
            $("#tool_bar_bg").fadeIn();
            $("#floatDiv_closeWrap").fadeIn();
            Iframe.src(url);//调取iframe登录
        }
        else{
            var _d = document;
            var _b = _d.body;
            var tool_bar_bg = _d.createElement("div");
            tool_bar_bg.setAttribute("id","tool_bar_bg");//black
            _b.appendChild(tool_bar_bg);
            var floatDiv_closeWrap = _d.createElement("div");
            floatDiv_closeWrap.setAttribute("id","floatDiv_closeWrap");//close
            _b.appendChild(floatDiv_closeWrap);
            $(tool_bar_bg).fadeIn(function(){
                Iframe.src(url);//调取iframe登录
            });
            $(floatDiv_closeWrap).html("<em id='floatDiv_closeWrap_a'>&nbsp;</em><em id='floatDiv_closeWrap_b'>&nbsp;</em>").fadeIn(function(){
                //第一次绑定 以后不用了
                $(this).on("click",function(){//右上角关闭
                    $(this).fadeOut();
                    $(tool_bar_bg).fadeOut();
                    cookie.set('ml_obj_type','');
                    Iframe.remove();
                })
            });
        }
    },
    isLogin: function(){
        if(uid!='0'){
            return true;
        }
        else{
            return false;
        }
    },
    message: function (txt, callback, noClose) {
        var _d = window.top.document;
        var _alert_bg = _d.createElement("div");
        _alert_bg.setAttribute("id", "message_bg");
        _d.body.appendChild(_alert_bg);
     
        var _alert_content = _d.createElement("div");
        _alert_content.setAttribute("id", "message_content");
        _alert_bg.appendChild(_alert_content);
     
        var _this = $(_alert_content);
        _this.html(txt).fadeIn(function () {
            if (noClose) {
                callback && callback();
            } else {
                setTimeout(function () {
                    _this.fadeOut(function () {
                        $(_alert_bg).fadeOut(function () {
                            $(this).remove();
                        });
                        callback && callback();
                    })
                }, 1500)
            }
        });
    },
    getlist: function(json_url,page) {
        Jsonp.request(M.baseDomain + '/' +json_url + '&doajax=1&page='+page, {
            "t": new Date().getTime()
        });
    }
}
var cookie = {
    'prefix': cookiepre,
    // 保存 Cookie
    'set': function (name, value, seconds) {
        expires = new Date();
        expires.setTime(expires.getTime() + (1000 * seconds));
        document.cookie = this.name(name) + "=" + escape(value) + "; expires=" + expires.toGMTString() + "; path=/";
    },
    // 获取 Cookie
    'get': function (name) {
        cookie_name = this.name(name) + "=";
        cookie_length = document.cookie.length;
        cookie_begin = 0;
        while (cookie_begin < cookie_length) {
            value_begin = cookie_begin + cookie_name.length;
            if (document.cookie.substring(cookie_begin, value_begin) == cookie_name) {
                var value_end = document.cookie.indexOf(";", value_begin);
                if (value_end == -1) {
                    value_end = cookie_length;
                }
                return unescape(document.cookie.substring(value_begin, value_end));
            }
            cookie_begin = document.cookie.indexOf(" ", cookie_begin) + 1;
            if (cookie_begin == 0) {
                break;
            }
        }
        return null;
    },
    // 清除 Cookie
    'del': function (name) {
        var expireNow = new Date();
        document.cookie = this.name(name) + "=" + "; expires=Thu, 01-Jan-70 00:00:01 GMT" + "; path=/";
    },
    'name': function (name) {
        return this.prefix + name;
    }
};

var Iframe = {
    target: "",
    height: function (i) {
        console.log(i);
        Iframe.target.css({
            "visibility": "hidden",
            "display": "block"
        });
        i = window.top.document.getElementById("theIframe").contentWindow.document.body.offsetHeight;
        Iframe.target.css({
            "height": i + "px",
            "visibility": "visible",
            "display": "none"
        });
        window.scroll(0, 0);
        Iframe.target.fadeIn();
    },
    src: function (src) {
        Iframe.init();
        var _src = src.indexOf("?") != -1 ? (src + "&v="+M.version) : (src + "?v="+M.version);
        if (Iframe.isFirst) {
            Iframe.target.attr("src", _src);
        } else {
            Iframe.target.fadeOut(function () {
                Iframe.target.attr("src", _src);
            });
        }
    }, //切换的时候先关闭，然后换地址 加载完毕 换高度 然后show
    remove: function () {
        Iframe.target.fadeOut(function () {
            Iframe.target.remove();
        });
    },
    reload: function () {
        location.reload();
    },
    isFirst: false,
    init: function () {
        if (!$("#theIframe").size()) {
            var iframe = document.createElement("iframe");
            iframe.setAttribute("id", "theIframe");
            document.body.appendChild(iframe);
            Iframe.isFirst = true;
        } else {
            Iframe.isFirst = false;
        }
        Iframe.target = $("#theIframe");
    }
};
 
var Jsonp = {
    loadScript: function (url) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (script.readyState) {
            script.onreadystatechange = function () {
                if (this.readyState == "loaded" || this.readyState == "complete") {
                    this.onreadystatechange = null;
                    document.body.removeChild(this);
                }
            };
        } else {
            script.onload = function () {
                document.body.removeChild(this);
            };
        }
        script.setAttribute('src', url);
        document.body.appendChild(script);
    },
    encodeParameters: function (arg, parameters) {
        var paras = [];
        for (parameter in parameters) {
            paras.push(escape(parameter) + "=" + escape(parameters[parameter]));
        }
        return paras.length > 0 ? (arg == -1 ? '?' : '&') + paras.join('&') : '';
    },
    request: function (url, param) {
        if (typeof url === 'string') var str = url.indexOf('?');
        this.loadScript(url + this.encodeParameters(str, param));
    }
};