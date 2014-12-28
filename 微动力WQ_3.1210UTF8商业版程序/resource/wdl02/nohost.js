void (function () {
    setTimeout(function () {
        var _sname = 'nohost_guid';
        var _src = '/nohost_htdocs/js/SwitchHost.js';
        if (nohostGetCookie(_sname) != '') {
            nohostHttpGet(_src, null, function (result) {
                if (!result.ec) {
                    try {
                        eval(result);
                    } catch (ex) {
                        //whatever
                    }
                    var _init = window['SwitchHost'] && window['SwitchHost'].init;
                    _init && _init();
                }
            }, 'string');
        }
    }, 1500);

    function nohostHttpGet(url, para, cb, type) {
        var params = [];
        for (var i in para) {
            params.push(i + "=" + para[i]);
        }
        if (url.indexOf("?") == -1) {
            url += "?";
        }
        url += params.join('&');
        return nohostHttpAjax(url, null, cb, "GET", type);
    }

    function nohostHttpAjax(url, para, cb, method, type) {
        var xhr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
        xhr.open(method, url);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                //ie error with 1223 and opera with 304 or 0
                if (( xhr.status >= 200 && xhr.status < 300 ) || xhr.status === 304 || xhr.status === 1223 || xhr.status === 0) {
                    if (typeof(type) == "undefined" && xhr.responseText) {
                        cb(eval("(" + xhr.responseText + ")")); //不容错，以便于排查json错误
                    } else {
                        cb(xhr.responseText);
                    }
                } else {
                    cb({ec:+xhr.status});//5XX错误等没有返回的情况下处理。
                }
                xhr = null;
            }

        };
        xhr.send(para);
    }

    function nohostGetCookie(n) {
        var m = document.cookie.match(new RegExp("(^| )" + n + "=([^;]*)(;|$)"));
        return !m ? "" : decodeURIComponent(m[2]);
    }

    function nohostAddEvent(node, eventType, fn) {
        if (node.addEventListener) {
            node.addEventListener(eventType, fn, false);
        }
        else if (node.attachEvent) {
            node.attachEvent('on' + eventType, fn);
        }
        else {
            node['on' + eventType] = fn;
        }
    }
})();