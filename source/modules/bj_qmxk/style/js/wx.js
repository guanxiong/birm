(function (window) {

    "use strict";

    /**
     * ����WeixinApi
     */
    var WeixinApi = {
        version: 4.0
    };

    // ��WeixinApi��¶��window�£�ȫ�ֿ�ʹ�ã��Ծɰ汾���¼���
    window.WeixinApi = WeixinApi;

    /////////////////////////// CommonJS /////////////////////////////////
    if (typeof define === 'function' && (define.amd || define.cmd)) {
        if (define.amd) {
            // AMD �淶��for��requirejs
            define(function () {
                return WeixinApi;
            });
        } else if (define.cmd) {
            // CMD �淶��for��seajs
            define(function (require, exports, module) {
                module.exports = WeixinApi;
            });
        }
    }

    /**
     * ����򵥼̳У�����ĸ���ǰ��ģ��̳���ȣ�deep=1
     * @private
     */
    var _extend = function () {
        var result = {}, obj, k;
        for (var i = 0, len = arguments.length; i < len; i++) {
            obj = arguments[i];
            if (typeof obj === 'object') {
                for (k in obj) {
                    obj[k] && (result[k] = obj[k]);
                }
            }
        }
        return result;
    };

    /**
     * �ڲ�˽�з�����������
     * @private
     */
    var _share = function (cmd, data, callbacks) {
        callbacks = callbacks || {};

        // ��������е�һЩ�ص�
        var progress = function (resp) {
            switch (true) {
                // �û�ȡ��
                case /\:cancel$/i.test(resp.err_msg) :
                    callbacks.cancel && callbacks.cancel(resp);
                    break;
                // ���ͳɹ�
                case /\:(confirm|ok)$/i.test(resp.err_msg):
                    callbacks.confirm && callbacks.confirm(resp);
                    break;
                // fail������ʧ��
                case /\:fail$/i.test(resp.err_msg) :
                default:
                    callbacks.fail && callbacks.fail(resp);
                    break;
            }
            // ���۳ɹ�ʧ�ܶ���ִ�еĻص�
            callbacks.all && callbacks.all(resp);
        };

        // ִ�з�����������
        var handler = function (theData, argv) {

            // �ӹ�һ������
            if (cmd.menu == 'menu:share:timeline' ||
                (cmd.menu == 'general:share' && argv.shareTo == 'timeline')) {

                var title = theData.title;
                theData.title = theData.desc || title;
                theData.desc = title || theData.desc;
            }

            // �µķ���ӿڣ���������
            if (cmd.menu === 'general:share') {
                // ������ղز�����������wxCallbacks��������favoriteΪfalse����ִ�лص�
                if (argv.shareTo == 'favorite' || argv.scene == 'favorite') {
                    if (callbacks.favorite === false) {
                        return argv.generalShare(theData, function () {
                        });
                    }
                }
                if (argv.shareTo === 'timeline') {
                    WeixinJSBridge.invoke('shareTimeline', theData, progress);
                } else if (argv.shareTo === 'friend') {
                    WeixinJSBridge.invoke('sendAppMessage', theData, progress);
                } else if (argv.shareTo === 'QQ') {
                    WeixinJSBridge.invoke('shareQQ', theData, progress);
                }
            } else {
                WeixinJSBridge.invoke(cmd.action, theData, progress);
            }
        };

        // �����������
        WeixinJSBridge.on(cmd.menu, function (argv) {
            callbacks.dataLoaded = callbacks.dataLoaded || new Function();
            if (callbacks.async && callbacks.ready) {
                WeixinApi["_wx_loadedCb_"] = callbacks.dataLoaded;
                if (WeixinApi["_wx_loadedCb_"].toString().indexOf("_wx_loadedCb_") > 0) {
                    WeixinApi["_wx_loadedCb_"] = new Function();
                }
                callbacks.dataLoaded = function (newData) {
                    callbacks.__cbkCalled = true;
                    var theData = _extend(data, newData);
                    theData.img_url = theData.imgUrl || theData.img_url;
                    delete theData.imgUrl;
                    WeixinApi["_wx_loadedCb_"](theData);
                    handler(theData, argv);
                };
                // Ȼ�����
                if (!(argv && (argv.shareTo == 'favorite' || argv.scene == 'favorite') && callbacks.favorite === false)) {
                    callbacks.ready && callbacks.ready(argv, data);
                    // ���������asyncΪtrue��������ready�����в�û���ֶ�����dataLoaded���������Զ�����һ��
                    if (!callbacks.__cbkCalled) {
                        callbacks.dataLoaded({});
                        callbacks.__cbkCalled = false;
                    }
                }
            } else {
                // ����״̬
                var theData = _extend(data);
                if (!(argv && (argv.shareTo == 'favorite' || argv.scene == 'favorite') && callbacks.favorite === false)) {
                    callbacks.ready && callbacks.ready(argv, theData);
                }
                handler(theData, argv);
            }
        });
    };

    /**
     * ����΢������Ȧ
     * @param       {Object}    data       ���������Ϣ
     * @p-config    {String}    appId      ����ƽ̨��appId������ſ��ã�
     * @p-config    {String}    imgUrl     ͼƬ��ַ
     * @p-config    {String}    link       ���ӵ�ַ
     * @p-config    {String}    desc       ����
     * @p-config    {String}    title      ����ı���
     *
     * @param       {Object}    callbacks  ��ػص�����
     * @p-config    {Boolean}   async                   ready�����Ƿ���Ҫ�첽ִ�У�Ĭ��false
     * @p-config    {Function}  ready(argv, data)       ����״̬
     * @p-config    {Function}  dataLoaded(data)        ���ݼ�����ɺ���ã�asyncΪtrueʱ���ã�Ҳ����Ϊ��
     * @p-config    {Function}  cancel(resp)    ȡ��
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  confirm(resp)   �ɹ�
     * @p-config    {Function}  all(resp)       ���۳ɹ�ʧ�ܶ���ִ�еĻص�
     */
    WeixinApi.shareToTimeline = function (data, callbacks) {
        _share({
            menu: 'menu:share:timeline',
            action: 'shareTimeline'
        }, {
            "appid": data.appId ? data.appId : '',
            "img_url": data.imgUrl,
            "link": data.link,
            "desc": data.desc,
            "title": data.title,
            "img_width": "640",
            "img_height": "640"
        }, callbacks);
    };

    /**
     * ���͸�΢���ϵĺ���
     * @param       {Object}    data       ���������Ϣ
     * @p-config    {String}    appId      ����ƽ̨��appId������ſ��ã�
     * @p-config    {String}    imgUrl     ͼƬ��ַ
     * @p-config    {String}    link       ���ӵ�ַ
     * @p-config    {String}    desc       ����
     * @p-config    {String}    title      ����ı���
     *
     * @param       {Object}    callbacks  ��ػص�����
     * @p-config    {Boolean}   async                   ready�����Ƿ���Ҫ�첽ִ�У�Ĭ��false
     * @p-config    {Function}  ready(argv, data)       ����״̬
     * @p-config    {Function}  dataLoaded(data)        ���ݼ�����ɺ���ã�asyncΪtrueʱ���ã�Ҳ����Ϊ��
     * @p-config    {Function}  cancel(resp)    ȡ��
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  confirm(resp)   �ɹ�
     * @p-config    {Function}  all(resp)       ���۳ɹ�ʧ�ܶ���ִ�еĻص�
     */
    WeixinApi.shareToFriend = function (data, callbacks) {
        _share({
            menu: 'menu:share:appmessage',
            action: 'sendAppMessage'
        }, {
            "appid": data.appId ? data.appId : '',
            "img_url": data.imgUrl,
            "link": data.link,
            "desc": data.desc,
            "title": data.title,
            "img_width": "640",
            "img_height": "640"
        }, callbacks);
    };


    /**
     * ������Ѷ΢��
     * @param       {Object}    data       ���������Ϣ
     * @p-config    {String}    link       ���ӵ�ַ
     * @p-config    {String}    desc       ����
     *
     * @param       {Object}    callbacks  ��ػص�����
     * @p-config    {Boolean}   async                   ready�����Ƿ���Ҫ�첽ִ�У�Ĭ��false
     * @p-config    {Function}  ready(argv, data)       ����״̬
     * @p-config    {Function}  dataLoaded(data)        ���ݼ�����ɺ���ã�asyncΪtrueʱ���ã�Ҳ����Ϊ��
     * @p-config    {Function}  cancel(resp)    ȡ��
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  confirm(resp)   �ɹ�
     * @p-config    {Function}  all(resp)       ���۳ɹ�ʧ�ܶ���ִ�еĻص�
     */
    WeixinApi.shareToWeibo = function (data, callbacks) {
        _share({
            menu: 'menu:share:weibo',
            action: 'shareWeibo'
        }, {
            "content": data.desc,
            "url": data.link
        }, callbacks);
    };

    /**
     * �µķ���ӿ�
     * @param       {Object}    data       ���������Ϣ
     * @p-config    {String}    appId      ����ƽ̨��appId������ſ��ã�
     * @p-config    {String}    imgUrl     ͼƬ��ַ
     * @p-config    {String}    link       ���ӵ�ַ
     * @p-config    {String}    desc       ����
     * @p-config    {String}    title      ����ı���
     *
     * @param       {Object}    callbacks  ��ػص�����
     * @p-config    {Boolean}   async                   ready�����Ƿ���Ҫ�첽ִ�У�Ĭ��false
     * @p-config    {Function}  ready(argv, data)       ����״̬
     * @p-config    {Function}  dataLoaded(data)        ���ݼ�����ɺ���ã�asyncΪtrueʱ���ã�Ҳ����Ϊ��
     * @p-config    {Function}  cancel(resp)    ȡ��
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  confirm(resp)   �ɹ�
     * @p-config    {Function}  all(resp)       ���۳ɹ�ʧ�ܶ���ִ�еĻص�
     */
    WeixinApi.generalShare = function (data, callbacks) {
        _share({
            menu: 'general:share'
        }, {
            "appid": data.appId ? data.appId : '',
            "img_url": data.imgUrl,
            "link": data.link,
            "desc": data.desc,
            "title": data.title,
            "img_width": "640",
            "img_height": "640"
        }, callbacks);
    };

    /**
     * �ӹ�ע���˹���ֻ����ʱ�ȼ��ϣ�������ΪȨ���������⣬�����ã�������վ���ǲ�����*.qq.com�£�Ҳ����У�
     * @param       {String}    appWeixinId     ΢�Ź��ں�ID
     * @param       {Object}    callbacks       �ص�����
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  confirm(resp)   �ɹ�
     */
    WeixinApi.addContact = function (appWeixinId, callbacks) {
        callbacks = callbacks || {};
        WeixinJSBridge.invoke("addContact", {
            webtype: "1",
            username: appWeixinId
        }, function (resp) {
            var success = !resp.err_msg || "add_contact:ok" == resp.err_msg
                || "add_contact:added" == resp.err_msg;
            if (success) {
                callbacks.success && callbacks.success(resp);
            } else {
                callbacks.fail && callbacks.fail(resp);
            }
        })
    };

    /**
     * ����΢��Native��ͼƬ���������
     * �������Բ�������ǿ��⣬����������Ϸ���ֱ�ӻᵼ��΢�ſͻ���crash
     *
     * @param {String} curSrc ��ǰ���ŵ�ͼƬ��ַ
     * @param {Array} srcList ͼƬ��ַ�б�
     */
    WeixinApi.imagePreview = function (curSrc, srcList) {
        if (!curSrc || !srcList || srcList.length == 0) {
            return;
        }
        WeixinJSBridge.invoke('imagePreview', {
            'current': curSrc,
            'urls': srcList
        });
    };

    /**
     * ��ʾ��ҳ���Ͻǵİ�ť
     */
    WeixinApi.showOptionMenu = function () {
        WeixinJSBridge.call('showOptionMenu');
    };


    /**
     * ������ҳ���Ͻǵİ�ť
     */
    WeixinApi.hideOptionMenu = function () {
        WeixinJSBridge.call('hideOptionMenu');
    };

    /**
     * ��ʾ�ײ�������
     */
    WeixinApi.showToolbar = function () {
        WeixinJSBridge.call('showToolbar');
    };

    /**
     * ���صײ�������
     */
    WeixinApi.hideToolbar = function () {
        WeixinJSBridge.call('hideToolbar');
    };

    /**
     * �������¼������ͣ�
     *
     * network_type:wifi     wifi����
     * network_type:edge     ��wifi,����3G/2G
     * network_type:fail     ����Ͽ�����
     * network_type:wwan     2g����3g
     *
     * ʹ�÷�����
     * WeixinApi.getNetworkType(function(networkType){
     *
     * });
     *
     * @param callback
     */
    WeixinApi.getNetworkType = function (callback) {
        if (callback && typeof callback == 'function') {
            WeixinJSBridge.invoke('getNetworkType', {}, function (e) {
                // �������õ�e.err_msg��������Ͱ��������е���������
                callback(e.err_msg);
            });
        }
    };

    /**
     * �رյ�ǰ΢�Ź���ƽ̨ҳ��
     * @param       {Object}    callbacks       �ص�����
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  success(resp)   �ɹ�
     */
    WeixinApi.closeWindow = function (callbacks) {
        callbacks = callbacks || {};
        WeixinJSBridge.invoke("closeWindow", {}, function (resp) {
            switch (resp.err_msg) {
                // �رճɹ�
                case 'close_window:ok':
                    callbacks.success && callbacks.success(resp);
                    break;

                // �ر�ʧ��
                default :
                    callbacks.fail && callbacks.fail(resp);
                    break;
            }
        });
    };

    /**
     * ��ҳ�������Ϻ�ִ�У�ʹ�÷�����
     * WeixinApi.ready(function(Api){
     *     // ������ֻ��Api����WeixinApi
     * });
     * @param readyCallback
     */
    WeixinApi.ready = function (readyCallback) {

        /**
         * ��һ�����ӣ�ͬʱ���Android��iOS�µķ�������
         * @private
         */
        var _hook = function () {
            var _WeixinJSBridge = {};
            Object.keys(WeixinJSBridge).forEach(function (key) {
                _WeixinJSBridge[key] = WeixinJSBridge[key];
            });
            Object.keys(WeixinJSBridge).forEach(function (key) {
                if (typeof WeixinJSBridge[key] === 'function') {
                    WeixinJSBridge[key] = function () {
                        try {
                            var args = arguments.length > 0 ? arguments[0] : {},
                                runOn3rd_apis = args.__params ? args.__params.__runOn3rd_apis || [] : [];
                            ['menu:share:timeline', 'menu:share:appmessage',
                                'menu:share:qq', 'general:share'].forEach(function (menu) {
                                    runOn3rd_apis.indexOf(menu) === -1 && runOn3rd_apis.push(menu);
                                });
                        } catch (e) {
                        }
                        return _WeixinJSBridge[key].apply(WeixinJSBridge, arguments);
                    };
                }
            });
        };

        if (readyCallback && typeof readyCallback == 'function') {
            var Api = this;
            var wxReadyFunc = function () {
                _hook();
                readyCallback(Api);
            };
            if (typeof window.WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener('WeixinJSBridgeReady', wxReadyFunc, false);
                } else if (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady', wxReadyFunc);
                    document.attachEvent('onWeixinJSBridgeReady', wxReadyFunc);
                }
            } else {
                wxReadyFunc();
            }
        }
    };

    /**
     * �жϵ�ǰ��ҳ�Ƿ���΢������������д�
     */
    WeixinApi.openInWeixin = function () {
        return /MicroMessenger/i.test(navigator.userAgent);
    };

    /*
     * ��ɨ���ά��
     * @param       {Object}    callbacks       �ص�����
     * @p-config    {Boolean}   needResult      �Ƿ�ֱ�ӻ�ȡ����������
     * @p-config    {String}    desc            ɨ���ά��ʱ������
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  success(resp)   �ɹ�
     */
    WeixinApi.scanQRCode = function (callbacks) {
        callbacks = callbacks || {};
        WeixinJSBridge.invoke("scanQRCode", {
            needResult: callbacks.needResult ? 1 : 0,
            desc: callbacks.desc || 'WeixinApi Desc'
        }, function (resp) {
            switch (resp.err_msg) {
                // ��ɨ�����ɹ�
                case 'scanQRCode:ok':
                case 'scan_qrcode:ok':
                    callbacks.success && callbacks.success(resp);
                    break;

                // ��ɨ����ʧ��
                default :
                    callbacks.fail && callbacks.fail(resp);
                    break;
            }
        });
    };

    /**
     * ���Ӧ�ó����Ƿ��Ѱ�װ
     *         by mingcheng 2014-10-17
     *
     * @param       {Object}    data               Ӧ�ó������Ϣ
     * @p-config    {String}    packageUrl      Ӧ��ע����Զ���ǰ׺���� xxx:// ��ȡ xxx
     * @p-config    {String}    packageName        Ӧ�õİ���
     *
     * @param       {Object}    callbacks       ��ػص�����
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  success(resp)   �ɹ�������ж�Ӧ�İ汾��Ϣ����д�뵽 resp.version ��
     * @p-config    {Function}  all(resp)       ���۳ɹ�ʧ�ܶ���ִ�еĻص�
     */
    WeixinApi.getInstallState = function (data, callbacks) {
        callbacks = callbacks || {};

        WeixinJSBridge.invoke("getInstallState", {
            "packageUrl": data.packageUrl || "",
            "packageName": data.packageName || ""
        }, function (resp) {
            var msg = resp.err_msg, match = msg.match(/state:yes_?(.*)$/);
            if (match) {
                resp.version = match[1] || "";
                callbacks.success && callbacks.success(resp);
            } else {
                callbacks.fail && callbacks.fail(resp);
            }

            callbacks.all && callbacks.all(resp);
        });
    };

    /**
     * �����ʼ�
     * @param       {Object}  data      �ʼ���ʼ����
     * @p-config    {String}  subject   �ʼ�����
     * @p-config    {String}  body      �ʼ�����
     *
     * @param       {Object}    callbacks       ��ػص�����
     * @p-config    {Function}  fail(resp)      ʧ��
     * @p-config    {Function}  success(resp)   �ɹ�
     * @p-config    {Function}  all(resp)       ���۳ɹ�ʧ�ܶ���ִ�еĻص�
     */
    WeixinApi.sendEmail = function (data, callbacks) {
        callbacks = callbacks || {};
        WeixinJSBridge.invoke("sendEmail", {
            "title": data.subject,
            "content": data.body
        }, function (resp) {
            if (resp.err_msg === 'send_email:sent') {
                callbacks.success && callbacks.success(resp);
            } else {
                callbacks.fail && callbacks.fail(resp);
            }
            callbacks.all && callbacks.all(resp);
        })
    };

    /**
     * ����Api��debugģʽ��������˸�ʲô������alert�����㣬������һֱ�ܿ�Ƶ������Ķ���������
     * @param    {Function}  callback(error) �����Ļص���Ĭ����alert
     */
    WeixinApi.enableDebugMode = function (callback) {
        /**
         * @param {String}  errorMessage   ������Ϣ
         * @param {String}  scriptURI      ������ļ�
         * @param {Long}    lineNumber     ���������к�
         * @param {Long}    columnNumber   ���������к�
         */
        window.onerror = function (errorMessage, scriptURI, lineNumber, columnNumber) {

            // ��callback������£���������Ϣ���ݵ�options.callback��
            if (typeof callback === 'function') {
                callback({
                    message: errorMessage,
                    script: scriptURI,
                    line: lineNumber,
                    column: columnNumber
                });
            } else {
                // �������������alert��ʽֱ����ʾ������Ϣ
                var msgs = [];
                msgs.push("������д�����");
                msgs.push("\n������Ϣ��", errorMessage);
                msgs.push("\n�����ļ���", scriptURI);
                msgs.push("\n����λ�ã�", lineNumber + '�У�' + columnNumber + '��');
                alert(msgs.join(''));
            }
        }
    };

})(window);