	var md_Cache = function(c) {
        if (! (this instanceof md_Cache)) {
            return new md_Cache(c)
        }
        this._cache = []
    };
    $.augment(md_Cache, {
        init: function() {
            return this
        },
        set: function(d, c) {
            if ($.isString(c)) {
                this.del(c, d[c])
            }
            this._cache.push(d);
            return this
        },
        getIndex: function(d, e) {
            var c = 0;
            while (this._cache[c] && this._cache[c][d] !== e) {
                c++
            }
            return c >= this._cache.length ? -1 : c
        },
        get: function(e, f) {
            var d = this.getIndex(e, f);
            var c = d >= 0 ? this._cache[d] : null;
            return c
        },
        del: function(d, e) {
            var c = this.getIndex(d, e);
            if (c >= 0) {
                this._cache.splice(c, 1)
            }
        }
    });
	/*消息方法*/
	var messageFun = function(f, e){
		this.msg = f;
        if (!this.msg) {
            return
        }
        this.title = this.msg && this.msg.find(".msg-tit");
        this.content = this.msg && this.msg.find(".msg-cnt");
        this.source = $.isPlainObject(e) ? e: null;
        this.type = this._getType()
	};
	var message = function(f, e) {
        return new messageFun(f, e)
    };
	$.augment(messageFun, {
		
		/*改变样式*/
		change: function(f, e) {
            if (!this.msg) {
                return this
            }
            this.show();
            var f = f && f.toUpperCase() || "";
            switch (f) {
            case "OK":
            case "ERROR":
            case "TIPS":
            case "NOTICE":
            case "ATTENTION":
            case "QUESTION":
            case "STOP":
                this._changeType(f);
                break;
            default:
                break
            }
            this.type = this._getType();
            this._changeText(e);
            return this
        },
		_changeType: function(f) {
            var e = this.msg.attr("class"),
            g = /\bmsg-ok\b|\bmsg-error\b|\bmsg-tips\b|\bmsg-notice\b|\bmsg-attention\b|\bmsg-question\b|\bmsg-stop\b/g;
            if (e.match(g)) {
                this.msg.attr("class", e.replace(g, "msg-" + f.toLowerCase()))
            } else {
                this.msg.addClass("msg-" + f.toLowerCase())
            }
        },
        _changeTitle: function(e) {
            if (!this.title || !$.type(e)==="string") {
                return
            }
            this.title.html(e)
        },
        _changeContent: function(e) {
            if (!this.content || !$.type(e)==="string") {
                return
            }
            this.content.html(this.source ? this.source[e] || "": e)
        },
        _changeText: function(e) {
			
            var g = $.isPlainObject(e) && $.type(e.title)==="string" ? e.title: "",
            f = $.isPlainObject(e) && $.type(e.content)==="string" ? e.content: ($.type(e)==="string" ? e: "");
            this._changeTitle(g);
            this._changeContent(f);
            if (!g && !f && this.msg) {
                this.msg.addClass("msg-weak")
            }
        },
		ok: function(e) {
            this.change("ok", e);
            return this
        },
        error: function(e) {
            this.change("error", e);
            return this
        },
        tips: function(e) {
            this.change("tips", e);
            return this
        },
        notice: function(e) {
            this.change("notice", e);
            return this
        },
        attention: function(e) {
            this.change("attention", e);
            return this
        },
        question: function(e) {
            this.change("question", e);
            return this
        },
        stop: function(e) {
            this.change("stop", e);
            return this
        },
		weak: function() {
            this.msg.replaceClass("msg", "msg-weak");
            this.msg.replaceClass("msg-b", "msg-b-weak")
        },
        _getType: function() {
            var f = "",
            e = this.msg.attr("class");
            if (e.match(/\bmsg-(b-)?error\b/)) {
                f = "ERROR"
            } else {
                if (e.match(/\bmsg-(b-)?-tips\b/)) {
                    f = "TIPS"
                } else {
                    if (e.match(/\bmsg-(b-)?-attention\b/)) {
                        f = "ATTENTION"
                    } else {
                        if (e.match(/\bmsg-(b-)?-notice\b/)) {
                            f = "NOTICE"
                        } else {
                            if (e.match(/msg-ok|msg-b-ok/)) {
                                f = "OK"
                            } else {
                                if (e.match(/\bmsg-(b-)?-question\b/)) {
                                    f = "QUESTION"
                                } else {
                                    if (e.match(/\bmsg-(b-)?-stop\b/)) {
                                        f = "STOP"
                                    }
                                }
                            }
                        }
                    }
                }
            }
            return f
        },
		isHide: function() {
            return this.msg.css("visibility") == "hidden" || this.msg.css("display") == "none"
        },
        hide: function() {
            this.msg.css("visibility", "hidden").removeClass("show").addClass("hide");
            return this
        },
        show: function() {
            this.msg.css("visibility", "visible").removeClass("hide").addClass("show");
            return this
        }
	});
	var md_placeholder = function(d) {
        this.input = d.input && $(d.input);
        this.placeholder = d.placeholder && $(d.placeholder);
        this.blurCls = d.blurCls || "ph_blur"
    };
    var md_fn_placeholder = function(d) {
        return new md_placeholder(d)
    };
    $.augment(md_placeholder, {
        init: function() {
            if (!this.input || !this.placeholder || this.input.val()) {
                return this
            }
            var d = this;
            this.placeholder.show();
            this.fix();
            this.placeholder.on("click",
            function() {
                d.input.trigger("focus")
            });
            this.input.on("change paste keyup",
            function() {
                if (d.input.val().length) {
                    d.placeholder.hide()
                } else {
                    d.placeholder.show();
                    d.fix();
                    d.placeholder.addClass(d.blurCls)
                }
            }).on("blur",
            function() {
                if (!d.input.val().length) {
                    d.placeholder.show();
                    d.fix();
                    d.placeholder.removeClass(d.blurCls)
                }
            }).on("focus",
            function() {
                if (d.input.val().length) {
                    d.placeholder.hide()
                } else {
                    d.placeholder.show();
                    d.fix();
                    d.placeholder.addClass(d.blurCls)
                }
            });
            $(window).on("resize",
            function() {
                d.fix()
            });
            return this
        },
        fix: function() {
			
            var d = this.input.offset();
            this.placeholder.css({
                position: "absolute",
                left: d.left + 7
            }).css({
                top: d.top + Math.floor((this.input.outerHeight() + 2 - this.placeholder.outerHeight()) / 2)
            })
        }
    });
	/*用户名称验证模块*/
	var md_username = function(g){
		this.type = (g.type || "REG").toUpperCase();
		this.input = g.input && $(g.input);
		this.nick = this.input.attr("name");
		this.tip = g.tip && $(g.tip) ? message($(g.tip)) : null;
		this.async = false;
		this.on = g.on || "keyup blur";
		this.errCls = $.type(g.errCls)==="string" && g.errCls || "";
		this.timeout = $.isNumeric(g.timeout) ? g.timeout: 0;
		this.defaultOn = g.defaultOn || "";
        this.defaultTip = g.defaultTip || null;
		this.checkCallback = $.isFunction(g.checkCallback) ? g.checkCallback: function() {};
		this.checkUrl = g.checkUrl || "";
		this.checkData = $.isPlainObject(g.checkData) && g.checkData || {};
		this.tipMsg = $.isPlainObject(g.tipMsg) ? g.tipMsg: {};
		this.disabledMsg = $.isString(g.disabledMsg) ? g.disabledMsg: "\u7528\u6237\u540d\u8f93\u5165\u6709\u8bef\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\uff01";
        this.disabled = true;
		this.recommendNick = null;
		this.placeholder = g.placeholder && g.input ? md_fn_placeholder({
            input: g.input,
            placeholder: g.placeholder,
            blurCls: "ph_blur"
        }) : null;
	};
	var md_fn_username = function(g) {
        return new md_username(g);
    };
	$.augment(md_username,{
		statusCode: {
            netError: {
                code: -1,
                msg: "\u7f51\u7edc\u9519\u8bef"
            },
            empty: {
                code: 1,
                msg: "\u4e0d\u80fd\u4e3a\u7a7a"
            },
            formatError: {
                code: 2,
                msg: "\u7528\u6237\u540d\u683c\u5f0f\u9519\u8bef"
            },
            ajaxError: {
                code: 3,
                msg: "\u7528\u6237\u540d\u9519\u8bef"
            },
            sizeError: {
                code: 4,
                msg: "5-25\u4e2a\u5b57\u7b26"
            },
            allNumberError: {
                code: 5,
                msg: "\u4e0d\u80fd\u5168\u4e3a\u6570\u5b57"
            },
            illegalError: {
                code: 6,
                msg: "\u5305\u542b\u975e\u6cd5\u5b57\u7b26"
            },
            ok: {
                code: 100,
                msg: ""
            }
        },
		regex: {
            illegal: /[~\uff5e]|[!\uff01]|[?\uff1f]|\.\.|--|__|\uff0d|\uff3f|\u203b|\u25b2|\u25b3|\u3000| |@/,
            allNumber: /^\d+$/
        },
        init: function() {
			
            if (!this.input || !this.tip) {
                return this
            }
			this.placeholder && this.placeholder.init();
            var g = this;
			
            g.validate(true, false);
            g.input.on(this.on,function() {
                $.later(function() {g.validate(false, true)},g.timeout, false, this)
            });
            this.defaultOn && (this.input.on(this.defaultOn,function() { 
				! $.trim(g.input.val()) && g.reset()
            }));
			
            /*this.suggestNickList && this.suggestNickKey && $.Event.delegate(document, "click", ".i_radio_nick",
            function(h) {
                g.input.val(h.currentTarget.value);
                if (g.suggestAutoClose) {
                    g.suggestNick.hide()
                }
                g.validate()
            });*/
            return this
        },
		validate: function(h) {
            if (!this.input) {
                this.disabled = false;
                return this.disabled
            }
            var j = this;
            var g = arguments,
            h = $.isPlainObject(g[0]) ? g[0] : {
                def: !!g[0],
                async: !!g[1],
                callback: null,
                context: window
            };
            var m = h.def,
            l = h.async,
            n = h.callback,
            k = h.context;
            this.checkAble(function(o) {
                j.validateCallback(m);
                $.isFunction(n) && n.call(this, o)
            },
            l, k)
        },
		validateCallback: function(g) {
			
            if (g && this.tip.type.toLowerCase() == "error" && !this.tip.isHide()) {
                return this.disabled
            }
            this.check(g);
            if (!this.recommendNick) {
                this.suggestNick && this.suggestNick.hide()
            } else {
                if (this.async) {
                    this.updateSuggestList()
                }
            }
            this.async && this.updateSuggestList(); ! g && this.checkCallback(this.disabled, this.asyncRetData, this.recommendNick);
            return this.disabled
        },
		updateSuggestList: function() {
            if (!this.recommendNick) {
                return
            }
            var h = this.suggestNickTemplate,
            g = this;
            var j = "";
            $.each(this.recommendNick,
            function(k, l) {
                j += $.substitute(h, {
                    id: "nick_" + l,
                    nick: k,
                    checked: k === g.input.val() ? "checked": ""
                })
            });
            this.suggestNickList.html(j);
            this.suggestNick.show()
        },
        check: function(g) {
            if (g && !$.trim(this.input.val())) { ! this.defaultOn && this.reset();
                return
            }
            switch (this.stat.code) {
            case - 1 : case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                this.tip.error(this.stat.msg);
                this.input.removeClass(this.errCls);
                $.later(function() {
                    this.input.addClass(this.errCls)
                },
                1, false, this);
                break;
            case 100:
                this.tip.ok(this.tipMsg.ok || "");
                this.input.removeClass(this.errCls);
                break;
            default:
                break
            }
        },
        checkAble: function(m, k, j) {
            var h = this,
            l, g;
            var j = j || h,
            k = !!k,
            m = $.isFunction(m) && m ||
            function() {};
            this.asyncRetData = null;
            this.recommendNick = null;
            h.async = k;
            if (!$.trim(h.input.val()).length) {
                h.disabled = true;
                h.stat = h.statusCode.empty;
                m.call(j, h.disabled);
                return
            } else {
                if (!h.cache || h.cache.getIndex("value", h.input.val()) == -1) {
                    if (l = h._checkAble()) {
                        h.disabled = true;
                        if (h.type == "REG") {
                            switch (l) {
                            case 4:
                                h.stat = h.statusCode.sizeError;
                                break;
                            case 5:
                                h.stat = h.statusCode.allNumberError;
                                break;
                            case 6:
                                h.stat = h.statusCode.illegalError;
                                break;
                            default:
                                break
                            }
                        } else {
                            h.stat = h.statusCode.formatError
                        }
                    } else {
                        h.disabled = false;
                        h.stat = h.statusCode.ok
                    }
                    if (!h.checkUrl || h.disabled || !k) {
                        if (h.cache) {
                            g = h._updateCache();
                            h.cache.set(g, "value")
                        }
                        m.call(j, h.disabled);
                        return
                    }
                    $.ajax({
                        url: h.checkUrl,
                         // data: $.mix(h.checkData,{h.nick+":"+$.trim(h.input.val())}),
						data:h.nick+"="+$.trim(h.input.val()),
                        type: "post",
                        dataType: "json",
                        success: function(n) {
                            var o = n.msg || n.reason || "";
                            o = h.msgTemplate ? h.msgTemplate[o] || o: o;
                            if (n.success) {
                                h.disabled = false;
                                h.stat = {
                                    code: h.statusCode.ok.code,
                                    msg: o || h.statusCode.ok.msg
                                };
                                h.recommendNick = null
                            } else {
                                h.disabled = true;
                                h.stat = {
                                    code: h.statusCode.ajaxError.code,
                                    msg: o || h.statusCode.ajaxError.msg
                                };
                                h.recommendNick = h.suggestNick && h.suggestNickList && h.suggestNickKey ? n[h.suggestNickKey] : null
                            }
                            this.asyncRetData = n;
                            m.call(j, h.disabled);
                            if (h.cache) {
                                g = h._updateCache();
                                h.cache.set(g, "value")
                            }
                        },
                        error: function() {
                            this.asyncRetData = null;
                            h.stat = {
                                code: h.statusCode.netError.code,
                                value: $.trim(h.input.val()),
                                msg: h.statusCode.netError.msg
                            };
                            h.recommendNick = null;
                            m.call(j, h.disabled)
                        }
                    })
                } else {
                    g = h.cache.get("value", h.input.val());
                    h.disabled = g.disabled;
                    h.stat = g.stat;
                    h.recommendNick = g.recommendNick;
                    m.call(j, h.disabled)
                }
            }
        },
        _checkAble: function() {
            var h = 0,
            g;
            if (!this.matchSize()) {
                h = 4
            } else {
                if (this.type == "REG" && this.isAllNumber()) {
                    h = 5
                } else {
                    if (g = this.isIllegal()) {
                        this.statusCode.illegalError = {
                            code: 6,
                            msg: "\u5305\u542b\u975e\u6cd5\u5b57\u7b26" + g
                        };
                        h = 6
                    }
                }
            }
            return h
        },
		_updateCache: function() {
            return {
                value: $.trim(this.input.val()),
                disabled: this.disabled,
                stat: this.stat,
                recommendNick: this.recommendNick
            }
        },
        _updateRecommendNick: function() {
            if (!this.recommendNick || !this.recommendNick.length) {
                return this
            }
            var h = "",
            g = 0;
            while (g < this.recommendNick.length) {
                h += $.substitute(this.suggestNickListTemplate, {
                    index: g,
                    nick: this.recommendNick[g]
                });
                g++
            }
            this.suggestNickList.html(h);
            return this
        },
        isAllNumber: function() {
            return !! this.input.val().match(this.regex.allNumber)
        },
        isIllegal: function() {
            var g = this.input.val().match(this.regex.illegal);
            return g ? g.join(" ") : ""
        },
        size: function() {
            return $.trim(this.input.val()).replace(/[^\x00-\xff]/g, "***").length
        },
        matchSize: function() {
            var g = this.type == "REG" ? 5 : 2;
            return this.size() >= g && this.size() <= (this.type == "REG" ? 25 : 9999)
        },
		reset: function() {
            this.input && this.input.removeClass(this.errCls);
            this.resetTip();
            return this
        },
		resetTip: function() {
            if (!this.tip) {
                return this
            }
            if (this.defaultTip && this.defaultTip.type && this.defaultTip.msg) {
                this.tip.change(this.defaultTip.type, this.defaultTip.msg)
            } else {
                this.tip.hide()
            }
            return this
        }
	})
	/*密码基础*/
	var md_PasswordCore = function(d) {
        this.password = d || ""
    };
    var PasswordCore= function(d) {
        return new md_PasswordCore(d)
    };
    $.augment(md_PasswordCore, {
        regex:{
			illegal:/[^-+=|,0-9a-zA-Z!@#$%^&*?_.~+/\\(){}\[\]<>]/g,
			allNumber:/^\d+$/,
			allLetter:/^[a-zA-Z]+$/,
			allCharacter:/^[-+=|,!@#$%^&*?_.~+/\\(){}\[\]<>]+$/,
			allSame:/^([\s\S])\1*$/,
			number:/\d/g,letter:/[a-zA-Z]/g,
			lowerAndUpperLetter:/[a-z][^A-Z]*[A-Z]|[A-Z][^a-z]*[a-z]/,
			numberAndLetter:/\d[^a-zA-Z]*[a-zA-Z]|[a-zA-Z][^\d]*\d/,character:/[-+=|,!@#$%^&*?_.~+/\\()|{}\[\]<>]/g},
        score: function() {
            var g = 0;
            if (this.isIllegal()) {
                return g
            }
            var j = this.size();
            if (j <= 4) {
                g += 5
            } else {
                if (j > 4 && j < 8) {
                    g += 10
                } else {
                    if (j >= 8) {
                        g += 25
                    }
                }
            }
            var f = this.hasLowerAndUpperLetter(),
            e = this.hasLetter();
            if (f) {
                g += 20
            } else {
                if (e) {
                    g += 10
                }
            }
            var d = this.hasNumber();
            if (d >= 3) {
                g += 20
            } else {
                if (d) {
                    g += 10
                }
            }
            var h = this.hasCharacter();
            if (h >= 3) {
                g += 25
            } else {
                if (h) {
                    g += 10
                }
            }
            if (f && d && h) {
                g += 10
            } else {
                if (e && d && h) {
                    g += 5
                } else {
                    if ((e && d) || (e && h) || (d && h)) {
                        g += 2
                    }
                }
            }
            return g
        },
        level: function() {
            var e = 0;
            var d = Math.floor(this.score() / 10);
            switch (d) {
            case 10:
            case 9:
                e = 7;
                break;
            case 8:
                e = 6;
                break;
            case 7:
                e = 5;
                break;
            case 6:
                e = 4;
                break;
            case 5:
            case 4:
            case 3:
                e = 3;
                break;
            case 2:
                e = 2;
                break;
            default:
                e = 1;
                break
            }
            return e
        },
        size: function() {
            return this.password.length
        },
        isIllegal: function() {
            return !! this.password.match(this.regex.illegal)
        },
        isAllNumber: function() {
            return !! this.password.match(this.regex.allNumber)
        },
        isAllLetter: function() {
            return !! this.password.match(this.regex.allLetter)
        },
        isAllSame: function() {
            return !! this.password.match(this.regex.allSame)
        },
        hasNumber: function() {
            return (this.password.match(this.regex.number) || []).length
        },
        hasLetter: function() {
            return (this.password.match(this.regex.letter) || []).length
        },
        hasLowerAndUpperLetter: function() {
            return !! this.password.match(this.regex.lowerAndUpperLetter)
        },
        hasNumberAndLetter: function() {
            return !! this.password.match(this.regex.numberAndLetter)
        },
        hasCharacter: function() {
            return (this.password.match(this.regex.character) || []).length
        }
    });
	var md_Similar = {
        _str1: null,
        _str3: null,
        _matrix: null,
        init: function(e, d) {
            if (!$.isString(e) || !$.isString(d)) {
                return
            }
            this._str1 = e;
            this._str2 = d;
            e.length && d.length && this._createMatrix(e.length + 1, d.length + 1);
            this._matrix && this._initMatrix();
            return this
        },
        get: function() {
            return 1 - this._getDistance() / Math.max(this._str1.length, this._str2.length)
        },
        _getDistance: function() {
            var g = this._str1.length,
            e = this._str2.length;
            if (!g || !e) {
                return Math.max(g, e)
            }
            var l = this._str1.split(""),
            k = this._str2.split("");
            var h = 0,
            f = 0,
            d = 0;
            while (h++<g) {
                f = 0;
                while (f++<e) {
                    d = l[h - 1] === k[f - 1] ? 0 : 1;
                    this._matrix[h][f] = Math.min(this._matrix[h - 1][f] + 1, this._matrix[h][f - 1] + 1, this._matrix[h - 1][f - 1] + d)
                }
            }
            return this._matrix[h - 1][f - 1]
        },
        _initMatrix: function() {
            var f = this._matrix[0].length,
            e = this._matrix.length;
            var d = Math.max(f, e);
            while (d--) {
                f - 1 >= d && (this._matrix[0][d] = d);
                e - 1 >= d && (this._matrix[d][0] = d)
            }
        },
        _createMatrix: function(e, d) {
            if (!$.isNumeric(e) || !$.isNumeric(d) || e < 1 || d < 1) {
                return
            }
            this._matrix = new Array(e),
            i = 0;
            while (i < e) {
                this._matrix[i++] = new Array(d)
            }
        }
    },
    Similar= function(e, d) {
        return md_Similar.init(e, d).get()
    };
	/*密码*/
	var md_Password = function(g) {
        this.input = g.input && $(g.input);
        this.reinput = g.reinput && $(g.reinput);
        this.strengthInput = g.strengthInput && $(g.strengthInput);
        this.timeout = $.isNumeric(g.timeout) ? g.timeout: 0;
        this.errCls = $.isString(g.errCls) && g.errCls || "";
        this.checkCallback = $.isFunction(g.checkCallback) ? g.checkCallback: function() {};
        this.tip = g.tip && $(g.tip) ? message($(g.tip)) : null;
        this.retip = g.retip && $(g.retip) ? message($(g.retip)) : null;
        this.strength = g.strength && $(g.strength);
        this.strengthCls = g.strengthCls ? g.strengthCls: {
            weak: "weak",
            medium: "medium",
            strong: "strong"
        };
        this.on = g.on || "keyup blur";
        this.defaultOn = g.defaultOn || "";
        this.username = g.username && $(g.username);
        this.defaultTip = g.defaultTip || null;
        this.redefaultTip = g.redefaultTip || null;
        this.disabledMsg = $.isString(g.disabledMsg) ? g.disabledMsg: "\u5bc6\u7801\u6216\u91cd\u590d\u5bc6\u7801\u683c\u5f0f\u9519\u8bef\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\uff01";
        this.password = null;
        this._disabled = true;
        this._redisabled = !!this.reinput;
        this.disabled = this._disabled || this._redisabled;
        this.stat = {
            code: 0,
            msg: ""
        };
        this.restat = {
            code: 0,
            msg: ""
        }
    };
    var Password = function(g) {
        return new md_Password(g)
    };
    $.augment(md_Password, {
        ctype: "CHECKER",
        statusCode: {
            empty: {
                code: 1,
                msg: "\u4e0d\u80fd\u4e3a\u7a7a"
            },
            size: {
                code: 2,
                msg: "\u957f\u5ea6\u5e94\u4e3a6-16\u4e2a\u5b57\u7b26"
            },
            illegal: {
                code: 3,
                msg: "\u4e0d\u80fd\u5305\u542b\u975e\u6cd5\u5b57\u7b26"
            },
            same: {
                code: 4,
                msg: "\u4e0d\u80fd\u4e3a\u540c\u4e00\u5b57\u7b26"
            },
            allLetter: {
                code: 5,
                msg: "\u4e0d\u80fd\u5168\u4e3a\u5b57\u6bcd"
            },
            allNumber: {
                code: 6,
                msg: "\u4e0d\u80fd\u5168\u4e3a\u6570\u5b57"
            },
            allCharacter: {
                code: 7,
                msg: "\u4e0d\u80fd\u5168\u4e3a\u7b26\u53f7"
            },
            weak: {
                code: 8,
                msg: "\u60a8\u7684\u5bc6\u7801\u5b89\u5168\u6027\u8f83\u4f4e\uff0c\u5efa\u8bae\u4f7f\u7528\u82f1\u6587\u5b57\u6bcd\u52a0\u6570\u5b57\u6216\u7b26\u53f7\u7ec4\u5408"
            },
            similar: {
                code: 9,
                msg: "\u5bc6\u7801\u548c\u8d26\u6237\u540d\u592a\u76f8\u4f3c"
            },
            reEmpty: {
                code: 10,
                msg: "\u518d\u8f93\u4e00\u6b21\u5bc6\u7801"
            },
            reError: {
                code: 11,
                msg: "\u4e24\u6b21\u5bc6\u7801\u8f93\u5165\u4e0d\u4e00\u81f4"
            },
            reOk: {
                code: 99,
                msg: ""
            },
            ok: {
                code: 100,
                msg: ""
            }
        },
        init: function() {
            if (!this.input || !this.tip) {
                return this
            }
            var g = this;
            this.validate(true);
            this.input.on("keyup",
            function() {
                g.checkPassword()
            }).on(this.on,
            function() {
                $.later(function() {
                    g.checkPassword();
                    g.check()
                },
                g.timeout, false, g)
            });
            this.defaultOn && (this.input.on(this.defaultOn,
            function() { ! g.input.val().length && g.reset()
            }));
            if (!this.reinput || !this.retip) {
                return this
            }
            this.reinput.on(this.on,
            function() {
                g.checkRePassword();
                g.checkRe()
            });
            this.defaultOn && (this.reinput.on(this.defaultOn,
            function() { ! g.reinput.val().length && g.resetRe()
            }));
            return this
        },
        validate: function(h) {
            if (!this.input) {
                this.disabled = false;
                return this.disabled
            }
            var g = arguments,
            h = $.isPlainObject(g[0]) ? g[0] : {
                def: !!g[0],
                async: !!g[1],
                callback: null,
                context: window
            };
            var l = h.def,
            k = h.async,
            m = h.callback,
            j = h.context;
            this.checkPassword();
            this.checkRePassword();
            this.validateCallback(l);
            $.isFunction(m) && m.call(j, this.disabled)
        },
        validateCallback: function(g) {
            if (g && this.tip.type.toLowerCase() == "error" && !this.tip.isHide()) {
                return this.disabled
            }
            this.check(g);
            this.checkRe(g); ! g && this.checkCallback(this.disabled);
            return this.disabled
        },
        check: function(g) {
            if (g && !this.input.val().length) { ! this.defaultOn && this.reset();
                return
            }
            switch (this.stat.code) {
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
                this.tip.error(this.stat.msg);
                this.input.removeClass(this.errCls);
                $.later(function() {
                    this.input.addClass(this.errCls)
                },
                1, false, this);
                break;
            case 100:
                this.tip.ok();
                this.input.removeClass(this.errCls);
                break;
            default:
                break
            }
        },
        checkRe: function(g) {
            if (this.reinput.prop("disabled")) {
                return
            }
            if (g && !this.reinput.val().length) { ! this.defaultOn && this.resetRe();
                return
            }
            switch (this.restat.code) {
            case 10:
            case 11:
                this.retip.error(this.restat.msg);
                this.reinput.removeClass(this.errCls);
                $.later(function() {
                    this.reinput.addClass(this.errCls)
                },
                1, false, this);
                break;
            case 99:
                this.retip.ok();
                this.reinput.removeClass(this.errCls);
            default:
                break
            }
        },
        checkPassword: function() {
            var g = this.password = PasswordCore(this.input.val());
            if (g.size() == 0) {
                this._disabled = true;
                this.stat = this.statusCode.empty
            } else {
                if (g.isIllegal()) {
                    this._disabled = true;
                    this.stat = this.statusCode.illegal
                } else {
                    if (g.isAllSame()) {
                        this._disabled = true;
                        this.stat = this.statusCode.same
                    } else {
                        if (g.isAllLetter()) {
                            this._disabled = true;
                            this.stat = this.statusCode.allLetter
                        } else {
                            if (g.isAllNumber()) {
                                this._disabled = true;
                                this.stat = this.statusCode.allNumber
                            } else {
                                if (g.size() < 6 && g.size() > 0 || g.size() > 16) {
                                    this._disabled = true;
                                    this.stat = this.statusCode.size
                                } else {
                                    this._disabled = false;
                                    this.stat = this.statusCode.ok;
                                    if (this.username) {
                                        var h = Similar($.trim(this.username.val()), this.input.val());
                                        if (h >= 0.8) {
                                            this._disabled = true;
                                            this.stat = this.statusCode.similar
                                        }
                                    }
                                }

                            }
                        }
                    }
                }
            }
            this.checkStrength();
            this.checkReDisabled();
            if (!this._disabled && this.reinput && !!this.reinput.val().length) {
                this.checkRePassword();
                this.checkRe()
            }
            return this
        },
        checkStrength: function() {
            if (!this.input || !this.strength) {
                return
            }
            var h = this.password,
            g = this.strengthCls,
            j = 1;
            var k = h.level();
            if (k >= 6) {
                this.strength.removeClass(g.weak).removeClass(g.medium).addClass(g.strong);
                j = 3
            } else {
                if (k > 2) {
                    this.strength.removeClass(g.weak).removeClass(g.strong).addClass(g.medium);
                    j = 2
                } else {
                    this.strength.removeClass(g.strong).removeClass(g.medium).addClass(g.weak);
                    j = 1
                }
            }
            if (!this._disabled && j <= 1) {
                this._disabled = true;
                this.stat = this.statusCode.weak
            }
            if (this.strengthInput) {
                this.strengthInput.val(j)
            }
            this.disabled = this._disabled || this._redisabled;
            return j
        },
        resetTip: function() {
            this.resetReTip();
            if (!this.tip) {
                return this
            }
            if (this.defaultTip && this.defaultTip.type && this.defaultTip.msg) {
                this.tip.change(this.defaultTip.type, this.defaultTip.msg)
            } else {
                this.tip.hide()
            }
            return this
        },
        reset: function() {
            this.input && this.input.val("") && this.input.removeClass(this.errCls);
            this.resetTip();
            return this
        },
        checkRePassword: function() {
            if (this.reinput.prop("disabled")) {
                return
            }
            if (!this.reinput.val().length) {
                this._redisabled = true;
                this.restat = this.statusCode.reEmpty
            } else {
                if (this.input.val() === this.reinput.val()) {
                    this._redisabled = false;
                    this.restat = this.statusCode.reOk
                } else {
                    this._redisabled = true;
                    this.restat = this.statusCode.reError
                }
            }
            this.disabled = this._disabled || this._redisabled;
            return this
        },
        checkReDisabled: function(g) {
            this.reinput && this.reinput.prop("disabled", $.isBoolean(g) ? g: this._disabled);
            this._disabled && this.resetReTip()
        },
        resetReTip: function() {
            if (!this.retip) {
                return this
            }
            if (this._disabled) {
                this.retip.hide()
            } else {
                if (this.redefaultTip && this.redefaultTip.type && this.redefaultTip.msg) {
                    this.retip.change(this.redefaultTip.type, this.redefaultTip.msg)
                } else {
                    this.retip.hide()
                }
            }
            return this
        },
        resetRe: function() {
            this.reinput && this.reinput.val("") && this.reinput.removeClass(this.errCls);
            this.resetReTip()
        }
    });
	/*验证码基础*/
	var md_CheckCode = function(p) {
		if (! (this instanceof md_CheckCode)) {
			return new md_CheckCode(p)
		}
		this.input = p.input && $(p.input);
		this.container = p.container && $(p.container);
		this.prefixCls = $.isString(p.prefixCls) ? p.prefixCls: "";
		this.identity = $.isString(p.identity) ? p.identity: "";
		this.sessionid = $.isString(p.sessionid) ? p.sessionid: "";
		this.refresher = p.refresher && $(p.refresher);
		this.checkedCode = "";
		//this.uid = g++;
		this.now =  new Date().getTime();
		this.img = p.checkCodeImg;
		this.getImgURL = WRegister.INITDATA.getImgURL;
        this.checkImgURL =  WRegister.INITDATA.checkImgURL;
		this.k = /^[\da-zA-Z]{4}$/,
		this.codeType = "IMG",
		this.h = {}
	};
	$.augment(md_CheckCode, {
		init: function() {
			
			if (!this.container || !this.input || !this.identity || !this.sessionid) {
				return
			}
			
			if (this.INITED) {
				return this
			}
			
			this.bind();
			this.INITED = true;
			return this
		},
		bind: function() {
			var p = this;
			this.bindImg();
			this.refresher.on("click",
			function(q) {
				q.preventDefault();
				q.stopPropagation();
				p.refresh();
				p.focus()
			});
			this.input.on("change",
			function(q) {
				p.now = new Date().getTime();
			}).on("paste",
			function() {
				if (this.value.length !== 0) {
					return
				}
				p.now = new Date().getTime();
			})
		},
		bindImg: function() {
			if (!this.img) {
				return this
			}
			var p = this;
			this.SHOWED = true;
			this.img && this.img.on("click",
			function() {
				p.refresh();
				p.focus()
			}).on("load",
			function() {}).on("error",
			function() {
				
			});
			return this
		},
		switchTo: function(p) {
			if (!p || !b.isString(p)) {
				return this
			}
			var p = p.toUpperCase();
			if (p === "IMG") {
				this.audioCode.hide();
				this.stopAudio();
				this.imgCode.css({
					display: "block"
				});
				this.codeType = p
			} else {
				if (p === "AUDIO") {
					this.imgCode.hide();
					this.audioProgress.width(0);
					this.audioStateText.removeClass(this.prefixCls + "audio-replay");
					this.audioCode.css({
						display: "block"
					});
					this.codeType = p
				}
			}
			this.checkedCode = "";
			this.toggleRefresher();
			this.SHOWED = true;
			this.fire("switch");
			return this
		},
		toggleRefresher: function() {
			if (this.codeType !== "AUDIO") {
				this.refresher.show();
				return
			}
			if (!this.audioSupport || c) {
				this.refresher.hide()
			}
		},
		refreshImg: function() {
			if (!this.getImgURL) {
				return this
			}
			var p = this.getImgURL + (this.getImgURL.indexOf("?") >= 0 ? "&t=": "?t=") + this.now;
			this.img.attr("src", p);
			return this
		},
		refresh: function(p) {
			var p = $.isString(p) && p ? p.toUpperCase() : this.codeType;
			this.refreshImg();
			this.checkedCode = "";
			//this.fire("refresh");
			//j = d = new Date().getTime();
		},
		focus: function() {
			this.input[0].focus();
			this.input[0].select()
		},
		showImg: function() {
			this.switchTo("img");
			this.refresh();
			return this
		},
		check: function(q) {
			var p = $.trim(this.input.val()),
			q = $.isFunction(q) ? q: function() {};
			if (!this.k.test(p)) {
				q({
					success: false,
					codeType: this.codeType
				});
				return
			}
			if (this.checkedCode && this.checkedCode === p) {
				q({
					success: true,
					codeType: this.codeType
				});
				return
			}
			this.h[p] = q;
			if (this.checkingCode) {
				if (this.checkingCode === p) {
					return
				} else {
					this.io && this.io.abort && this.io.abort()
				}
			}
			this.checkingCode = p;
			$.later(function() {
				this._check(q)
			},
			500, false, this)
		},
		_check: function(t) {
			var p = this.checkImgURL,
			s = $.trim(this.input.val());
			var q = this;
			q.io = $.ajax({
				url: p,
				data: {
					code: s
				},
				dataType: "jsonp",
				success: function(u) {
					q.checkingCode = "";
					if (u && u.message === "SUCCESS.") {
						
						q.checkedCode = s;
						q.h[s] && q.h[s]({
							success: true,
							codeType: q.codeType
						})
					} else {
						r()
					}
				},
				error: function() {
					r()
				}
			});
			function r() {
				
				if (q.codeType === "IMG") {
					q.refresh()
				}
				q.checkedCode = "";
				q.h[s] && q.h[s]({
					success: false,
					codeType: q.codeType
				})
			}
		}
	});
	/*验证码区域___________________________*/
	var SuperCheckCode = function(e) {
        if (! (this instanceof SuperCheckCode)) {
            return new SuperCheckCode(e)
        }
        this.input = e.input && $(e.input);
        this.errCls = $.isString(e.errCls) && e.errCls || "";
        this.container = e.container && $(e.container);
        this.defaultType = $.isString(e.defaultType) ? e.defaultType: "img";
        this.apiserver = e.apiserver;
        this.identity = e.identity;
        this.sessionid = e.sessionid;
		this.refresher = e.refresher && $(e.refresher);
        this.prefixCls = $.isString(e.prefixCls) ? e.prefixCls: "";
        this.checkData = $.isPlainObject(e.checkData) && e.checkData || {};
        this.checkCallback = $.isFunction(e.checkCallback) ? e.checkCallback: function() {};
        this.tip = e.tip && $(e.tip) ? message($(e.tip)) : null;
        this.checkOn = e.on || "keyup blur";
        this.defaultTip = e.defaultTip || null;
        this.disabledMsg = $.isString(e.disabledMsg) ? e.disabledMsg: "\u9a8c\u8bc1\u7801\u683c\u5f0f\u6709\u8bef\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\uff01";
        this.disabled = true;
		this.checkCodeImg  = e.checkCodeImg && $(e.checkCodeImg);
        this.stat = {
            code: 0,
            msg: ""
        };
        this.checkcode = null;
        this.codeType = ""
    };
    $.augment(SuperCheckCode, {
        ctype: "CHECKER",
        statusCode: {
            netError: {
                code: -1,
                msg: "\u7f51\u7edc\u9519\u8bef"
            },
            empty: {
                code: 1,
                msg: "\u4e0d\u80fd\u4e3a\u7a7a"
            },
            formatError: {
                code: 2,
                msg: "\u9a8c\u8bc1\u7801\u683c\u5f0f\u9519\u8bef"
            },
            ajaxError: {
                code: 3,
                msg: "\u9a8c\u8bc1\u7801\u9519\u8bef"
            },
            ok: {
                code: 100,
                msg: ""
            }
        },
        init: function() {
            if (!this.input || !this.container || !this.identity || !this.sessionid) {
                return this
            }
            var e = this;
            this.checkcode = md_CheckCode({
                input: e.input,
                container: e.container,
                apiserver: e.apiserver,
                identity: e.identity,
                sessionid: e.sessionid,
                prefixCls: e.prefixCls,
				refresher:e.refresher,
				checkCodeImg:e.checkCodeImg
            }).init();
            this.validate(true);
            this.input.on(this.checkOn,
            function() {
                e.validate(false, true)
            });
            return this
        },
        validate: function(f) {
            var g = this;
            var e = arguments,
            f = $.isPlainObject(e[0]) ? e[0] : {
                def: !!e[0],
                async: !!e[1],
                callback: null,
                context: window
            };
            var k = f.def,
            j = f.async,
            l = f.callback,
            h = f.context;
            this.checkAble(function(m) {
                g.validateCallback(k);
                $.isFunction(l) && l.call(this, m)
            },
            j, h)
        },
        validateCallback: function(e) {
            if (e && this.tip.type.toLowerCase() == "error" && !this.tip.isHide()) {
                return this.disabled
            }
            this.check(e); ! e && this.checkCallback(this.disabled);
            return this.disabled
        },
        check: function(e) {
            if (e && !$.trim(this.input.val())) {
                this.reset();
                return
            }
            switch (this.stat.code) {
            case 1:
            case 2:
            case 3:
                this.tip.error(this.stat.msg);
                this.input.removeClass(this.errCls);
                $.later(function() {
                    this.input.addClass(this.errCls)
                },
                1, false, this);
                break;
            case 100:
                this.tip.ok();
                this.input.removeClass(this.errCls);
                break;
            default:

                break
            }
        },
        checkAble: function(j, g, f) {
            var e = this,
            h;
            var f = f || e,
            g = !!g,
            j = $.isFunction(j) && j ||
            function() {};
            if (!$.trim(this.input.val()).length) {
                this.disabled = true;
                this.disabledMsg = "\u9a8c\u8bc1\u7801\u4e0d\u80fd\u4e3a\u7a7a";
                this.stat = this.statusCode.empty;
                j.call(f, e.disabled);
                return
            } else {
                if (!$.trim(this.input.val()).match(/^[\da-zA-Z]{4,6}$/)) {
                    this.disabled = true;
                    this.disabledMsg = "\u9a8c\u8bc1\u7801\u683c\u5f0f\u9519\u8bef";
                    this.stat = this.statusCode.formatError
                } else {
                    this.disabled = false;
                    this.stat = this.statusCode.ok
                }
            }
            if (e.disabled || !g) {
                j.call(f, e.disabled);
                return
            }
            if (!this.checkcode || !this.checkcode.INITED || !this.checkcode.SHOWED) {
                j.call(f, e.disabled);
                return
            }
            this.checkcode.check(function(k) {
                if (k.success) {
                    e.disabled = false;
                    e.stat = e.statusCode.ok
                } else {
                    e.disabled = true;
                    e.stat = e.statusCode.ajaxError
                }
                j.call(f, e.disabled);
                e.checkCallback(e.disabled)
            })
        },
        reset: function() {
            this.input && this.input.val("") && this.input.removeClass(this.errCls);
            this.resetTip();
            return this
        },
        resetTip: function() {
            if (!this.tip) {
                return this
            }
            if (this.defaultTip && this.defaultTip.type && this.defaultTip.msg) {
                this.tip.change(this.defaultTip.type, this.defaultTip.msg)
            } else {
                this.tip.hide()
            }
        },
        showCode: function(f) {
            var f = f || this.defaultType,
            e = this;
            if (this.checkcode && !this.checkcode.SHOWED) {
                if (!this.checkcode.INITED) {
                    this.checkcode.init();
                    this.checkcode.on("switch",
                    function() {
                        e.disabled = true;
                        e.tip.hide();
                        e.codeType = this.codeType;
                        e.fire("switch")
                    }).on("refresh",
                    function() {
                        e.disabled = true;
                        e.tip.hide();
                        e.fire("refresh")
                    })
                }
                if (f.toLowerCase() === "audio") {
                    this.checkcode.showAudio()
                } else {
                    this.checkcode.showImg()
                }
            }
            this.INITED = this.checkcode && this.checkcode.INITED;
            this.SHOWED = this.checkcode && this.checkcode.SHOWED
        }
    });
	/*表单提交*/
	var md_SubmitForm = function(e) {
        this.form = e.form && $(e.form);
        this.off = !!e.off;
        this.tip = e.tip && $(e.tip) ? message($(e.tip)) : null;
        this.stopTip = !!e.stopTip;
        this.asyncURL = $.isString(e.asyncURL) ? e.asyncURL: "";
        this.async = !!e.asyncURL;
        this.asyncType = $.isString(e.asyncType) ? e.asyncType: "post";
        this.asyncDataType = $.isString(e.asyncDataType) ? e.asyncDataType: "";
        this.asyncExternalData = $.isFunction(e.asyncExternalData) || $.isPlainObject(e.asyncExternalData) ? e.asyncExternalData: {};
        this.trigger = e.trigger ? $(e.trigger) : null;
        this.stop = $.isBoolean(e.stop) && e.stop || true;
        this.checkers = $.isArray(e.checkers) && e.checkers || ($.isFunction(e.checkers) ? e.checkers: []);
        this.disabledMsg = $.isString(e.disabledMsg) ? e.disabledMsg: "\u4fe1\u606f\u8f93\u5165\u6709\u8bef\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\uff01";
        this.checkCallback = $.isFunction(e.checkCallback) ? e.checkCallback: null;
        this.asyncCallback = $.isFunction(e.asyncCallback) ? e.asyncCallback: null;
        this.checkerDisabledMsg = "";
        this.disabled = false
    };
    var SubmitForm = function(e) {
        return new md_SubmitForm(e)
    };
    $.augment(md_SubmitForm, {
        init: function() {
            if (!this.form) {
                return
            }
            var e = this;
            if (this.trigger) {
                this.trigger.on("click",
                function() {
                    e.validate()
                })
            }
            if (!e.off) {
                this.form.on("submit",
                function(f) {
					f.preventDefault();
					f.stopPropagation();
                    e.validate()
                })
            }
            return this
        },
        validateCallback: function() {
            var e = this;
            if (e.disabled && e.stop && !e.stopTip) {
                e.tip && e.tip.error(e.checkerDisabledMsg || e.disabledMsg).laterHide(2000);
                return
            }
            if (e.checkCallback) {
                e.checkCallback(e.disabled);
                return
            }
            if (e.disabled && this.stop) {
                return
            }
            if (e.async && e.asyncURL) {
                var f = $.isFunction(e.asyncExternalData) ? e.asyncExternalData() : e.asyncExternalData;
                $.ajax({
                    url: e.asyncURL,
                    type: e.asyncType,
                    data: $.mix($.unparam(e.form.serialize(e.form)), f),
                    dataType: e.asyncDataType,
                    success: function(h, g, j) {
                        e.asyncCallback.call(e, h, g, j)
                    }
                })
            } else {
                this.form[0].submit()
            }
        },
        validate: function() {
            this.disabled = false;
            var e = $.isFunction(this.checkers) ? this.checkers() : this.checkers;
            if (!e.length) {
                return
            }
            this.validateIndex = 0;
            this.checkers = e;
            if ($.isFunction(e[this.validateIndex].validate)) {
                $.isFunction(e[0].validate) && e[0].validate({
                    async: true,
                    callback: this._check,
                    context: this
                })
            }
        },
        _check: function(e) {
            if (e) {
                this.disabled = true;
                if (this.checkers[this.validateIndex].disabledMsg) {
                    this.checkerDisabledMsg = this.checkers[this.validateIndex].disabledMsg
                }
                this.validateCallback();
                return
            } else {
                if (this.checkers[++this.validateIndex] && $.isFunction(this.checkers[this.validateIndex].validate)) {
                    this.checkers[this.validateIndex].validate({
                        async: true,
                        callback: this._check,
                        context: this
                    })
                } else {
                    this.validateCallback()
                }
            }
        },
        resetTip: function() {
            if (!this.tip) {
                return this
            }
            if (this.defaultTip && this.defaultTip.type && this.defaultTip.msg) {
                this.tip.change(this.defaultTip.type, this.defaultTip.msg)
            } else {
                this.tip.hide()
            }
            return this
        },
        reset: function() {
            this.disabled = false;
            this.resetTip();
            return this
        }
    });
	/*自算*/
	var ATP = function(b) {
		var a = {
			fire: function(c, e) {
				var c = $.isString(c) ? c: "";
				if (!c) {
					return
				}
				var d = new Image(),
				e = !$.isPlainObject(e) && !$.isString(e) ? "": ($.isPlainObject(e) ? $.param(e) : e);
				d.src = c + ($.indexOf("?") >= 0 ? "&": "?") + e
			}
		};
		return a
	};
	/*step1注册主程序*/
	var step_mainFn1 = function(){
		isIllegal=function() {
			var g = this.input.val().match(this.regex.illegal);
			return g ? g.join(" ") : ""
		};
		var r = this;
		var z = md_fn_username({
			type: "reg",
			input: "#WG_UserId",
			tip: "#WG_UserIdTip",
			form:"#step_form_no1",
			on: "blur",
			errCls: "err-input",
			placeholder: "#WG_username_ph",
			checkUrl: WRegister.INITDATA.userNameValidateUrl,
			defaultOn: "focus",
			defaultTip: {
				type: "tips",
				msg: {
					content: "5-25\u4e2a\u5b57\u7b26\uff0c\u4e00\u4e2a\u6c49\u5b57\u4e3a\u4e09\u4e2a\u5b57\u7b26\uff0c\u63a8\u8350\u4f7f\u7528\u4e2d\u6587\u4f1a\u5458\u540d\u3002"
				}
			},
			cache: true,
			tipMsg: {
				ok: "\u4e00\u65e6\u6ce8\u518c\u6210\u529f\u4e0d\u80fd\u4fee\u6539"
			},
			msgTemplate: WRegister.CONSTANTS,
			checkCallback: function(B) {
				/*j("#J_NumTip");
				ATP.fire(p + "1", {
					ok: B ? 0 : 1
				})*/
			}
		}).init();
		
		
		var g = Password({
            input: "#password",
            reinput: "#WG_RePwd",
            username: "#WG_UserId",
            errCls: "err-input",
            tip: "#WG_PwdTip",
            retip: "#WG_RePwdTip",
            timeout: 200,
            strengthInput: "#WG_PwdStrengthInput",
            strength: "#WG_PwdStrength",
            strengthCls: {
                weak: "pw-weak",
                medium: "pw-medium",
                strong: "pw-strong"
            },
            defaultOn: "focus",
            defaultTip: {
                type: "tips",
                msg: {
                    content: '6-16\u4e2a\u5b57\u7b26\uff0c\u8bf7\u4f7f\u7528\u5b57\u6bcd\u52a0\u6570\u5b57\u6216\u7b26\u53f7\u7684\u7ec4\u5408\u5bc6\u7801\uff0c\u4e0d\u80fd\u5355\u72ec\u4f7f\u7528\u5b57\u6bcd\u3001\u6570\u5b57\u6216\u7b26\u53f7\u3002'
                }
            },
            redefaultTip: {
                type: "tips",
                msg: {
                    content: "\u518d\u8f93\u4e00\u6b21\u5bc6\u7801"
                }
            },
            on: "blur",
            checkCallback: function(B) {
              
            }
        }).init();
		var p = md_fn_Email({
            input: "#WG_Email",
            tip: "#WG_EmailTip",
            on: "blur",
            suggest: true,
            errCls: "err-input",
            msgTemplate: WRegister.CONSTANTS,
            host: ["", "163.com", "qq.com", "126.com", "hotmail.com", "gmail.com", "yahoo.com", "263.com", "sohu.com", "sina.com"],
            timeout: 200,
            defaultTip: {
                type: "tips",
                msg: {
                    content: "\u7535\u5b50\u90ae\u7bb1\u5c06\u4e0e\u652f\u4ed8\u53ca\u4f18\u60e0\u76f8\u5173\uff0c\u8bf7\u586b\u5199\u6b63\u786e\u7684\u90ae\u7bb1"
                }
            },
            defaultOn: "focus",
            cache: true,
            checkUseCache: function(z, A) {
                return true
            },
            checkCallback: function(A, B) {
				return;
            }
        }).init();
		/*手机*/
		var d = md_fn_Phone({
            input: "#WG_PhoneInput",
            tip: "#WG_PhoneTip",
            on: "blur",
            select: "#WG_Area",
            area: "#WG_AreaCode",
            errCls: "err-input",
            cache: true,
            checkUrl: WRegister.INITDATA.phoneValidateUrl,
            msgTemplate: WRegister.CONSTANTS,
            checkData: {
               
            },
            checkUseCache: function(w, x) {
                return true
            },
            checkCallback: function(x, y) {
                //q && q.tip && q.tip.hide();
                if (!y) {
                    return
                }
                //m && m.prop("checked", C && !!y.success);
               // j && j.val(m && m.prop("checked") || "false");
                var z = y.msg || y.reason;
                if (z === "ERROR_CELLPHONE_DIRTY" || z === "ERROR_CELLPHONE_MEMBER_FANGKE_ERROR") {
                    d.tip && d.tip.attention(WRegister.CONSTANTS[z] || z);
                    return
                }
                if (z === "ERROR_CELLPHONE_EXISTED") {
                    var B = d.tip && d.tip.msg && d.tip.msg.find("a"),
                    w = B.attr("href"),
                    A = "TPL_username=" + $.trim(d.input.val()) + "&disableQuickLogin=true";
                    B.attr("href", w + (w.indexOf("?") >= 0 ? "&": "?") + A);
                    return
                }
            }
        }).init();
		k = md_fn_DynamicCheckCode({
            trigger: "#WG_PhoneCheckCodeTrigger",
            triggerTip: "#WG_PhoneCheckCodeTip",
            input: "#WG_PhoneCheckCode",
            inputTip: "#WG_PhoneCheckCodeTip",
            defaultInputTip: {
                type: "tips",
                msg: "\u8bf7\u8f93\u5165\u60a8\u6536\u5230\u76846\u4f4d\u9a8c\u8bc1\u7801"
            },
            defaultOn: "focus",
            btnTimeoutText: "\u91cd\u65b0\u53d1\u9001\u9a8c\u8bc1\u7801",
            btnWaitText: "%t%\u79d2\u540e\u91cd\u65b0\u53d1\u9001",
            on: "blur",
            getData: function() {
                //u.fire(e + "7");
                return {
                    mobile: d.input.val(),
                    userId: $("#WG_UserId").val(),
                    mobile_area: d.select.val()
                }
            },
            getUrl: WRegister.INITDATA.reSendCodeUrl,
            checkCallback: function(w, x) {
                //g && g.tip && g.tip.hide();
                //if (!x) {
                    return
                //}
            }
        }).init();
		
		var q = SubmitForm({
            form: "#step_form_no1",
            checkers: [z,g,p,d,k],
			asyncURL:$("#step_form_no1").attr("action"),
			asyncType:"post",
        	asyncDataType:"json",
			asyncExternalData: function() {
                /*return {
                    mobile: d.input.val(),
                    mobile_area: d.select.val(),
                    userNumId: $("#WG_UserNumId").val()
                }*/
            },
			asyncCallback:function(data){
				$(".ui-form-validate").hide();
				if (!data.result){
					
					$("#page-feedback-msg-box h3").html(data.errorTitle);
					var s = "";
					$.each(data.infos,function(i,item){
						if (item.showInTop){
						s+="<li>"+item.text+"</li>";
						}
						var tip = message($("#"+item.id));
						tip.error(item.text);
					});
					$("#page-feedback-msg-box ol").html(s);
					$("#page-feedback-msg-box").show();
					$(document).scrollTop(0);
				}else{
					alert(data.info);
					window.location.href=data.url;
				}
			}
        }).init();
	}
	/*step2 手机注册*/
	var md_PhoneCore = function(d) {
        this.phone = d || ""
    };
    var md_fn_PhoneCore = function(d) {
        return new md_PhoneCore(d)
    };
    $.augment(md_PhoneCore, {
        regex: {
            cm: /^(?:0?1)((?:3[56789]|5[0124789]|8[278])\d|34[0-8]|47\d)\d{7}$/,
            cu: /^(?:0?1)(?:3[012]|4[5]|5[356]|8[356]\d|349)\d{7}$/,
            ce: /^(?:0?1)(?:33|53|8[079])\d{8}$/,
            cn: /^(?:0?1)[3458]\d{9}$/,
            hk: /^(?:0?[1569])(?:\d{7}|\d{8}|\d{12})$/,
            macao: /^6\d{7}$/,
            tw: /^(?:0?[679])(?:\d{7}|\d{8}|\d{10})$/,
            kr: /^(?:0?[17])(?:\d{9}|\d{8})$/,
            jp: /^(?:0?[789])(?:\d{9}|\d{8})$/
        },
        defaultArea: ["cn"],
        check: function(f) {
            if (!this.phone) {
                return
            }
            var f = $.isArray(f) ? f: ($.isString(f) ? [f] : this.defaultArea);
            var d = f.length;
            while (d-->0) {
                var e = this.regex[f[d].toLowerCase()];
                if ( !! this.phone.match(e)) {
                    return true
                }
            }
            return false
        }
    });
	var md_Phone = function(g){
        this.input = g.input && $(g.input);
        this.errCls = $.isString(g.errCls) && g.errCls || "";
        this.tipBox = g.tipBox && $(g.tipBox);
        this.tip = (g.tip && $(g.tip)) ? message($(g.tip)) : null;
        this.on = g.on || "keyup blur";
        this.defaultTip = g.defaultTip || null;
        this.checkUrl = g.checkUrl || "";
        this.checkData = $.isPlainObject(g.checkData) || $.isFunction(g.checkData) ? g.checkData: {};
        this.checkCallback = $.isFunction(g.checkCallback) ? g.checkCallback: function() {};
        this.checkUseCache = $.isFunction(g.checkUseCache) ? g.checkUseCache: function() {
            return true
        };
        this.timeout = $.isNumeric(g.timeout) ? g.timeout: 0;
        this.select = g.select && $(g.select);
        this.area = g.area && $(g.area);
        this.type = "";
        this.retrieveTrigger = g.retrieveTrigger && $(g.retrieveTrigger);
        this.panel = g.panel && $(g.panel);
        this._showPanel = !!g.showPanel;
        this.ifrRetrieve = g.ifrRetrieve && $(g.ifrRetrieve);
        this._ifrRetrieveUrl = this.ifrRetrieve && this.ifrRetrieve.attr("data-src") || "";
        this.disabledMsg = $.isString(g.disabledMsg) ? g.disabledMsg: "\u624b\u673a\u53f7\u7801\u683c\u5f0f\u6709\u8bef\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\uff01";
        this.disabled = true;
        this.msgTemplate = $.isPlainObject(g.msgTemplate) ? g.msgTemplate: null;
        this.stat = {
            code: 0,
            msg: ""
        };
        this._validated = false;
        this._retrieving = false;
        this.cache = !!g.cache ? md_Cache().init() : null;
        this.code = "";
        this.asyncRetData = null
	};
	var md_fn_Phone = function(g) {
        return new md_Phone(g);
    };
	$.augment(md_Phone,{
		ctype: "CHECKER",
        areaCode: {
            cn: "86",
            hk: "852",
            macao: "853",
            tw: "886",
            kr: "82",
            jp: "81"
        },
        statusCode: {
            netError: {
                code: -1,
                msg: "\u7f51\u7edc\u9519\u8bef"
            },
            empty: {
                code: 1,
                msg: "\u4e0d\u80fd\u4e3a\u7a7a"
            },
            formatError: {
                code: 2,
                msg: "\u624b\u673a\u53f7\u7801\u9519\u8bef"
            },
            ajaxError: {
                code: 3,
                msg: "\u53f7\u7801\u4e0d\u53ef\u7528"
            },
            used: {
                code: 4,
                msg: "\u5df2\u88ab\u5360\u7528\uff0c\u8bf7\u66f4\u6362\u5176\u4ed6\u53f7\u7801\uff0c\u82e5\u60a8\u662f\u6b64\u53f7\u7801\u7684\u4f7f\u7528\u8005"
            },
            ok: {
                code: 100,
                msg: ""
            }
        },
        init: function() {
            if (!this.input || !this.tip) {
                return this
            }
            this.type = this.getArea();
            var g = this;
            this.input.on(this.on,
            function() {
                $.later(function() {
                    g.validate(false, true)
                },
                g.timeout, false, g)
            });
            this.select && this.area && this.select.on("change",
            function() {
                g.type = g.getArea();
                g.code = g.areaCode[g.type] || "";
                g.area.html("+" + g.code);
                if (g._validated) {
                    $.later(function() {
                        g.validate(false, true)
                    },
                    this.timeout, false, this)
                }
            }) && this.select.trigger("change");
            this._showPanel && this.showPanel();
            this.retrieveTrigger && this.retrieveTrigger.on("click",
            function() {
                g.togglePanel()
            });
            this.validate(true, true);
            return this
        },
        validate: function(h) {
            if (!this.input) {
                this.disabled = false;
                return this.disabled
            }
            var j = this;
            var g = arguments,
            h = $.isPlainObject(g[0]) ? g[0] : {
                def: !!g[0],
                async: !!g[1],
                callback: null,
                context: window
            };
            var m = h.def,
            l = h.async,
            n = $.isFunction(h.callback) ? h.callback: function() {},
            k = h.context;
            this.externalCallback = n;
            if (this.ajaxing) {
                if (this._getFullNumber() === this.ajaxingValue) {
                    return
                } else {
                    this.io && this.io.abort && this.io.abort()
                }
            }
            this.checkAble(function(o) {
                j.ajaxing = false;
                j.ajaxingValue = "";
                j.validateCallback(m);
                j.externalCallback.call(this, o)
            },
            l, k)
        },
        validateCallback: function(g) {
            if (g && this.tip.type.toLowerCase() == "error" && !this.tip.isHide()) {
                return this.disabled
            }
            this.check(g);
			
            this.checkCallback(this.disabled, this.asyncRetData, g);
            return this.disabled
        },
        check: function(g) {
            if (g && !$.trim(this.input.val())) {
                this.resetTip();
                return
            }
            this._validated = true;
            switch (this.stat.code) {
            case - 1 : case 1:
            case 2:
            case 3:
                this.showTipBox({
                    type:
                    "error",
                    msg: {
                        content: this.stat.msg
                    }
                });
                break;
            case 100:
                this.showTipBox({
                    type:
                    "ok"
                });
                break;
            default:
                break
            }
        },
        checkAble: function(m, k, j) {
            var h = this,
            l, g;
            var j = j || h,
            k = !!k,
            m = $.isFunction(m) && m ||
            function() {};
            h.asyncRetData = null;
            if (this.isEmpty()) {
                this.disabled = true;
                this.stat = this.statusCode.empty;
                m.call(j, h.disabled)
            } else {
                if (!h.cache || h.cache.getIndex("value", h._getFullNumber()) == -1) {
                    if (md_fn_PhoneCore($.trim(this.input.val())).check(this.type)) {
                        this.disabled = false;
                        this.stat = this.statusCode.ok
                    } else {
                        this.disabled = true;
                        this.stat = this.statusCode.formatError
                    }
                    if (!h.checkUrl || h.disabled || !k) {
                        if (h.cache) {
                            g = h._updateCache();
                            h.cache.set(g, "value")
                        }
                        m.call(j, h.disabled);
                        return
                    }
                    this.ajaxing = true;
                    this.ajaxingValue = this._getFullNumber();
                    this.io = $.ajax({
                        url: h.checkUrl,
                        data: $.mix($.isFunction(h.checkData) ? h.checkData() : h.checkData, {
                            mobile_area: h.select.val(),
                            mobile: $.trim(h.input.val())
                        }),
                        type: "post",
                        dataType: "json",
                        success: function(n) {
                            h.asyncRetData = n;
                            if (n) {
                                var o = n.msg || n.reason || "";
                                o = h.msgTemplate ? h.msgTemplate[o] || o: o;
                                if (n.success) {
									
                                    h.disabled = false;
                                    h.stat = {
                                        code: h.statusCode.ok.code,
                                        msg: o || h.statusCode.ok.msg
                                    }
                                } else {
                                    h.disabled = true;
                                    h.stat = {
                                        code: h.statusCode.ajaxError.code,
                                        msg: o || h.statusCode.ajaxError.msg
                                    }
                                }
                                if (h.cache) {
                                    g = h._updateCache();
                                    checkCache = h.checkUseCache(h.disabled, h.asyncRetData);
                                    if (checkCache) {
                                        h.cache.set(g, "value")
                                    } else {
                                        h.cache.del("value", g.value)
                                    }
                                }
                            } else {
                                h.stat = {
                                    code: h.statusCode.netError.code,
                                    msg: h.statusCode.netError.msg,
                                    value: $.trim(h.input.val())
                                }
                            }
							
                            m.call(j, h.disabled)
                        }
                    })
                } else {
                    g = h.cache.get("value", this._getFullNumber());
                    h.disabled = g.disabled;
                    h.stat = g.stat;
                    h.asyncRetData = g.data;
                    m.call(j, h.disabled)
                }
            }
        },
        _updateCache: function() {
            return {
                value: this._getFullNumber(),
                disabled: this.disabled,
                data: this.asyncRetData,
                stat: this.stat
            }
        },
        _getFullNumber: function() {
            return (this.select ? this.select.val() + "-": "") + $.trim(this.input.val())
        },
        getArea: function() {
            var g;
            if (!this.select) {
                g = "cn";
                return g
            }
            switch (this.select.val()) {
            case "1":
                g = "cn";
                break;
            case "2":
                g = "hk";
                break;
            case "3":
                g = "macao";
                break;
            case "4":
                g = "tw";
                break;
            case "5":
                g = "kr";
                break;
            case "6":
                g = "jp";
                break;
            default:
                break
            }
            return g
        },
        showPanel: function() {
            this.panel.show();
            this.retrieveTrigger.html("\u53d6\u6d88\u7533\u8bf7 \u25b2");
            this._retrieving = true;
            this.select.prop("disabled", true);
            this.input.prop("disabled", true);
            this._ifrRetrieveUrl && this.ifrRetrieve.attr("src", this._ifrRetrieveUrl)
        },
        hidePanel: function() {
            this.panel.hide();
            this.retrieveTrigger.html("\u7533\u8bf7\u7ed1\u5b9a\u6b64\u53f7\u7801 \u25bc");
            this._retrieving = false;
            this.select.prop("disabled", false);
            this.input.prop("disabled", false);
            this._ifrRetrieveUrl && this.ifrRetrieve.attr("src", "about:blank")
        },
        togglePanel: function() {
            this._retrieving ? this.hidePanel() : this.showPanel()
        },
        isEmpty: function() {
            return ! $.trim(this.input.val()).length
        },
        showTipBox: function(g) {
            this.tipBox && this.tipBox.removeClass("hide").addClass("show");
            this.retrieveTrigger && this.retrieveTrigger.hide();
            if (!$.isPlainObject(g)) {
                return
            }
            switch (g.type.toLowerCase()) {
            case "error":
                this.tip.error(g.msg || "");
                this.input.removeClass(this.errCls);
                $.later(function() {
                    this.input.addClass(this.errCls)
                },
                1, false, this);
                break;
            case "tips":
                this.tip.tips(g.msg || "");
                break;
            case "attention":
                this.tip.attention(g.msg || "");
                break;
            case "ok":
                this.tip.ok(g.msg || "");
                this.input.removeClass(this.errCls);
                break;
            case "stop":
                this.tip.stop(g.msg || "");
                break;
            case "notice":
                this.tip.notice(g.msg || "");
                break;
            case "question":
                this.tip.question(g.msg || "");
                break;
            case "retrieve":
                this.tip.error(g.msg || "\u5df2\u88ab\u5360\u7528\uff0c\u8bf7\u66f4\u6362\u5176\u4ed6\u53f7\u7801\uff0c\u82e5\u60a8\u662f\u6b64\u53f7\u7801\u7684\u4f7f\u7528\u8005");
                this.retrieveTrigger && this.retrieveTrigger.show();
                break;
            default:
                break
            }
        },
        hideTipBox: function() {
            this.tipBox && this.tipBox.removeClass("show").addClass("hide");
            this.tip.hide();
            this.input.removeClass(this.errCls)
        },
        reset: function() {
            this.input && this.input.val("") && this.input.removeClass(this.errCls);
            this.resetTip();
            return this
        },
        resetTip: function() {
            if (!this.tip) {
                return this
            }
            if (this.defaultTip && this.defaultTip.type && this.defaultTip.msg) {
                this.tip.change(this.defaultTip.type, this.defaultTip.msg)
            } else {
                this.tip.hide()
            }
        }
	});
	/*邮箱注册的建议提示*/
	var md_EmailSuggest = function(g){
        this.input = g.input && $(g.input) || null;
        this.host = $.isString(g.host) && [g.host] || $.isArray(g.host) && g.host || null;
        this.list = null;
        this.lis = null;
        this.current = -1;
        this.length = this.host && this.host.length || 0;
        this.events = {};
        this.ing = false
	};
	
	var md_fn_EmailSuggest = function(g) {
        return new md_EmailSuggest(g);
    };
	$.augment(md_EmailSuggest,{
		suggest: null,
        bound: false,
        laterId: 0,
        init: function() {
            if (!this.input || !this.host) {
                return this
            }
            var d = this;
            this.input.on("focus",
            function() {
                d.show()
            }).on("keyup",
            function(e) {
                var f = e.keyCode;
                if (f != 13 && f != 40 && f != 38) {
                    d.update();
                    d.bind()
                } else {
                    if (f == 38 && d.ing) {
                        d.select( - 1)
                    } else {
                        if (f == 40 && d.ing) {
                            d.select(1)
                        } else {
                            if (f == 13 && d.ing) {
                                d.complete()
                            }
                        }
                    }
                }
            }).on("blur",
            function() {
                d.complete()
            });
            $(window).on("resize",
            function() {
                d.fix()
            });
            return this
        },
        show: function() {
            this.suggest || this.create();
            this.input.parent().append(this.suggest);
            this.fix();
            this.ing = true;
            if (!$.trim(this.input.val())) {
                this.hide()
            } else {
                this.update()
            }
        },
        hide: function() {
            this.suggest.hide();
            this.lis && this.lis.length && this.lis.eq(this.current).removeClass("current");
            this.current = -1;
            this.ing = false;
            this.events.hide && this.events.hide()
        },
        complete: function() {
			
            if (!this.lis || !this.lis.length) {
                return
            }
			if (this.current==-1){
				this.hide();
				return
			}else{
				this.input.val(this.lis.eq(this.current).text());
				this.hide();
				this.events.complete && this.events.complete(this.input.val())
			}
		},
        bind: function() {
            if (!this.lis || !this.lis.length) {
                return
            }
            var d = this;
            this.lis.on("mouseover",
            function() {
                d.lis.eq(d.current).removeClass("current");
                d.current = parseInt($(this).attr("data-index"));
                d.lis.eq(d.current).addClass("current")
            })
        },
        create: function() {
            this.suggest = $('<div class="nomad-email-suggest"><span class="nomad-email-suggest-title">\u8bf7\u9009\u62e9</span><ul></ul></div>');
        },
        update: function() {
            if (this.input.val().indexOf("@") > -1 || !$.trim(this.input.val())) {
                this.hide();
                return
            }
            if (!this.list) {
                this.list = this.suggest.find("ul")
            }
            var j = this.lis || this.list.find("li");
            var g = j.length && j || "",
            f = $.trim(this.input.val());
            var e = this;
            if (!g) {
                for (var h = 0,
                d = this.host.length; h < d; h++) {
                    g += '<li data-index="' + h + '">' + f + "@" + this.host[h] + "</li>"
                }
                this.list.html(g)
            } else {
                for (var h = 0,
                d = this.host.length; h < d; h++) {
                    g.eq(h).html(f + "@" + e.host[h])
                }
            }
            this.lis = this.lis || this.suggest.find("li");
            this.suggest.show();
            this.ing = true
        },
        fix: function() {
            var d = this.input.offset();
            this.suggest && this.suggest.css({
                position: "absolute",
                left: d.left,
                top: d.top + this.input[0].clientHeight,
                minWidth: this.input[0].clientWidth
            });
            if ($.browser.msie&&$.browser.version=="6.0") {
                this.suggest.css("width", this.input[0].clientWidth)
            }
        },
        select: function(e) {
            if (!this.lis || !this.lis.length) {
                return
            }
            if (this.current >= 0) {
                this.lis.eq(this.current).removeClass("current")
            }
            if (e > 0) {
                this.current = this.current + 1 >= this.length ? 0 : this.current + 1
            } else {
                if (e < 0) {
                    this.current = this.current - 1 < 0 ? this.length - 1 : this.current - 1
                }
            }
            this.lis.eq(this.current).addClass("current")
        },
        on: function(d, e) {
            if (!$.isString(d) || !$.isFunction(e)) {
                return this
            }
            switch (d.toLowerCase()) {
            case "hide":
                this.events.hide = e;
                break;
            case "complete":
                this.events.complete = e;
                break;
            default:
                break
            }
            return this
        }
	});
	/*邮箱注册*/
	var md_Email = function(g){
        this.input = g.input && $(g.input);
        this.errCls = $.isString(g.errCls) && g.errCls || "";
        this.tip = g.tip && $(g.tip) && message($(g.tip));
        this.on = g.on || "keydown blur";
        this.checkUrl = g.checkUrl || "";
        this.checkData = $.isPlainObject(g.checkData) && g.checkData || {};
        this.checkCallback = $.isFunction(g.checkCallback) ? g.checkCallback: function() {};
        this.checkUseCache = $.isFunction(g.checkUseCache) ? g.checkUseCache: function() {
            return true
        };
        this.suggest = !!g.suggest;
        this.host = g.host || "";
        this.timeout = $.isNumeric(g.timeout) ? g.timeout: 0;
        this.defaultTip = g.defaultTip || null;
        this.defaultOn = g.defaultOn || "";
        this.msgTemplate = $.isPlainObject(g.msgTemplate) ? g.msgTemplate: null;
        this.disabledMsg = $.isString(g.disabledMsg) ? g.disabledMsg: "\u8f93\u5165\u683c\u5f0f\u8bef\uff0c\u8bf7\u91cd\u65b0\u8f93\u5165\uff01";
        this.disabled = true;
        this.checked = false;
        this._suggest = null;
        this.stat = {
            code: 0,
            msg: ""
        };
        this.cache = !!g.cache ? md_Cache().init() : null;
        this.asyncRetData = null
	};
	
	var md_fn_Email = function(g) {
		
        return new md_Email(g);
    };
	$.augment(md_Email,{
		ctype: "CHECKER",
        pattern: /^[a-zA-Z\d][-\.\w]*@(?:[-\w]+\.)+(?:[a-zA-Z])+$/,
        statusCode: {
            netError: {
                code: -1,
                msg: "\u7f51\u7edc\u9519\u8bef"
            },
            empty: {
                code: 1,
                msg: "\u4e0d\u80fd\u4e3a\u7a7a"
            },
            formatError: {
                code: 2,
                msg: "\u683c\u5f0f\u9519\u8bef"
            },
            ajaxError: {
                code: 3,
                msg: "\u8f93\u5165\u9519\u8bef"
            },
            ok: {
                code: 100,
                msg: ""
            }
        },
        init: function() {
            if (!this.input || !this.tip || !this.pattern) {
                return this
            }
            var g = this;
            this.input.on(this.on,
            function() {
                $.later(function() {
                    if (!g._suggest || !g._suggest.ing) {
                        g.validate(false, true);
                        g._inputValidated = true
                    } else {
                        g._inputValidated = false
                    }
                },
                g.timeout, false, g)
            });
            this.defaultOn && (this.input.on(this.defaultOn,
            function() { ! $.trim(g.input.val()) && g.reset()
            }));
            if (this.suggest) {
                this._suggest = md_fn_EmailSuggest({
                    input: this.input,
                    host: this.host
                }).init().on("complete",
                function() {
                    if (!g._inputValidated) {
                        g.validate(false, true)
                    }
                })
            }
            this.validate(true, true);
            return this
        },
        validate: function(m) {
            if (!this.input) {
                this.disabled = false;
                return this.disabled
            }
            if (this.suggest && this._suggest && this._suggest.ing) {
                this.disabled = true;
                return this.disabled
            }
            var j = this;
            var g = arguments,
            h = $.isPlainObject(g[0]) ? g[0] : {
                def: !!g[0],
                async: !!g[1],
                callback: null,
                context: window
            },
            m = h.def,
            l = h.async,
            n = $.isFunction(h.callback) ? h.callback: function() {},
            k = h.context;
            this.externalCallback = n;
            if (this.ajaxing) {
                if ($.trim(this.input.val()) === this.ajaxingValue) {
                    return
                } else {
                    this.io && this.io.abort && this.io.abort()
                }
            }
            this.checkAble(function(o) {
                j.ajaxing = false;
                j.ajaxingValue = "";
                j.validateCallback(m);
                j.externalCallback.call(this, o)
            },
            l, k)
        },
        validateCallback: function(g) {
            if (g && this.tip.type.toLowerCase() == "error" && !this.tip.isHide()) {
                return this.disabled
            }
            this.check(g);
            this.checkCallback(this.disabled, this.asyncRetData, g);
            return this.disabled
        },
        check: function(g) {
            if (g && !$.trim(this.input.val())) { ! this.defaultOn && this.reset();
                return
            }
            switch (this.stat.code) {
            case - 1 : case 1:
            case 2:
            case 3:
                this.tip.error(this.stat.msg);
                this.input.removeClass(this.errCls);
                $.later(function() {
                    this.input.addClass(this.errCls)
                },
                1, false, this);
                break;
            case 100:
                this.tip.ok();
                this.input.removeClass(this.errCls);
                break;
            default:
                break
            }
        },
        checkAble: function(n, k, j) {
            var h = this,
            l, g, j = j || this,
            k = !!k,
            n = $.isFunction(n) && n ||
            function() {},
            m = $.trim(this.input.val());
            if (!m.length) {
                this.disabled = true;
                this.stat = this.statusCode.empty;
                n.call(j, this.disabled);
                return
            } else {
                if (!this.cache || this.cache.getIndex("value", m) == -1) {
                    if (m.match(this.pattern)) {
                        this.disabled = false;
                        this.stat = this.statusCode.ok
                    } else {
                        this.disabled = true;
                        this.stat = this.statusCode.formatError
                    }
                    if (!this.checkUrl || this.disabled || !k) {
                        if (this.cache) {
                            g = this._updateCache();
                            this.cache.set(g, "value")
                        }
                        this.asyncRetData = null;
                        n.call(j, this.disabled);
                        return
                    }
                    this.ajaxing = true;
                    this.ajaxingValue = m;
                    this.io = $.ajax({
                        url: this.checkUrl,
                        data: $.mix(this.checkData, {
                            email: m
                        }),
                        type: "post",
                        dataType: "json",
                        success: function(o) {
                            h.asyncRetData = o;
                            if (o) {
                                var p = o.msg || o.reason || "";
                                p = h.msgTemplate ? h.msgTemplate[p] || p: p;
                                if (o.success) {
                                    h.disabled = false;
                                    h.stat = {
                                        code: h.statusCode.ok.code,
                                        msg: p || ""
                                    }
                                } else {
                                    h.disabled = true;
                                    h.stat = {
                                        code: h.statusCode.ajaxError.code,
                                        msg: p || h.statusCode.ajaxError.msg
                                    }
                                }
                                if (h.cache) {
                                    g = h._updateCache();
                                    checkCache = h.checkUseCache(h.disabled, h.asyncRetData);
                                    if (checkCache) {
                                        h.cache.set(g, "value")
                                    } else {
                                        h.cache.del("value", g.value)
                                    }
                                }
                            } else {
                                h.stat = {
                                    code: h.statusCode.netError.code,
                                    msg: h.statusCode.netError.msg,
                                    value: d.trim(h.input.val())
                                }
                            }
                            n.call(j, h.disabled)
                        }
                    })
                } else {
                    g = this.cache.get("value", m);
                    this.disabled = g.disabled;
                    this.stat = g.stat;
                    this.asyncRetData = g.data;
                    n.call(j, this.disabled)
                }
            }
        },
        _updateCache: function() {
            return {
                value: $.trim(this.input.val()),
                disabled: this.disabled,
                data: this.asyncRetData,
                stat: this.stat
            }
        },
        reset: function() {
            this.input && this.input.val("") && this.input.removeClass(this.errCls);
            this.resetTip();
            return this
        },
        resetTip: function() {
            if (!this.tip) {
                return this
            }
            if (this.defaultTip && this.defaultTip.type && this.defaultTip.msg) {
                this.tip.change(this.defaultTip.type, this.defaultTip.msg)
            } else {
                this.tip.hide()
            }
            return this
        }
	});
	var md_TimeoutBtn = function(d) {
        this.btn = d.btn && $(d.btn);
        this.timeout = d.timeout ? (Number(d.timeout) || 0) : 0;
        this.callback = $.isFunction(d.callback) && d.callback || null;
        this.text = d.text || (this.btn ? this.btn.text() : "");
        this.waitText = d.waitText || "%t%\u79d2\u540e\u53ef\u91cd\u65b0\u64cd\u4f5c";
        this.timeoutText = d.timeoutText || this.text;
        this.timeoutCls = d.timeoutCls || "";
        this.disabledCls = d.disabledCls || "";
        this.autoStart = !!d.autoStart;
        this._id = 0;
        this.autoStart && this.start()
    };
    var md_fn_TimeoutBtn = function(d) {
        return new md_TimeoutBtn(d)
    };
    $.augment(md_TimeoutBtn, {
        _counter: 0,
        start: function() {
            if (!this.btn || !this.timeout) {
                return
            }
            this._counter = 0;
            this.btn.prop("disabled", true);
            this.refresh();
            var d = this;
            if (this._id) {
                window.clearInterval(this._id)
            }
            this._id = setInterval(function() {
                d.disabled()
            },
            1000)
        },
        disabled: function() {
            this._counter++;
            if (this._counter == this.timeout) {
                this.clear();
                this.callback && this.callback()
            } else {
                this.refresh()
            }
        },
        clear: function() {
            window.clearInterval(this._id);
            this._id = "";
            this._counter = 0;
            this.btn.prop("disabled", false);
            this.btn.text(this.timeoutText);
            this.btn.removeClass(this.timeoutCls);
            this.btn.removeClass(this.disabledCls);
            this.btn.text(this.text)
        },
        refresh: function() {
            this.btn.text(this.waitText.replace("%t%", this.timeout - this._counter));
            this.btn.addClass(this.disabledCls)
        },
        reset: function() {
            this.clear()
        }
    });
	var md_DynamicCheckCode = function(f) {
        this.trigger = f.trigger && $(f.trigger);
        this.triggerTip = f.triggerTip && $(f.triggerTip) ? message($(f.triggerTip)) : null;
        this.input = f.input && $(f.input);
        this.inputTip = f.inputTip && $(f.inputTip) ? message($(f.inputTip)) : null;
        this.errCls = $.isString(f.errCls) && f.errCls || "";
        this.on = f.on || "keyup blur";
        this.defaultOn = f.defaultOn || "";
        this.getUrl = f.getUrl || "";
        this.checkUrl = f.checkUrl || "";
        this.checkData = $.isPlainObject(f.checkData) || $.isFunction(f.checkData) ? f.checkData: {};
        this.checkCallback = $.isFunction(f.checkCallback) ? f.checkCallback: function() {};
        this.getData = $.isPlainObject(f.getData) || $.isFunction(f.getData) ? f.getData: {};
        this.defaultMsg = {
            error: "\u9a8c\u8bc1\u7801\u53d1\u9001\u5931\u8d25\uff0c\u8bf7\u91cd\u8bd5\uff01",
            ok: "\u9a8c\u8bc1\u7801\u5df2\u53d1\u9001\uff0c\u8bf7\u67e5\u6536\uff01"
        };
        this.msg = f.msg || {};
        this.msg.error = this.msg.error || this.defaultMsg.error;
        this.msg.ok = this.msg.ok || this.defaultMsg.ok;
        this.defaultTriggerTip = f.defaultTriggerTip || null;
        this.defaultInputTip = f.defaultInputTip || null;
        this.msgTemplate = $.isPlainObject(f.msgTemplate) ? f.msgTemplate: null;
        this.disabledBtnCls = f.disabledBtnCls || "";
        this.btnText = f.btnText || "\u514d\u8d39\u83b7\u53d6\u9a8c\u8bc1\u7801";
        this.btnWaitText = f.btnWaitText || "%t%\u79d2\u540e\u53ef\u91cd\u65b0\u53d1\u9001";
        this.btnWaitCls = f.btnWaitCls || "";
        this.btnTimeoutText = f.btnTimeoutText || "\u91cd\u65b0\u53d1\u9001";
        this.btnTimeoutCls = f.btnTimeoutCls || "";
        this.btnAutoStart = !!f.btnAutoStart;
        this.tobtn = null;
        this.disabledMsg = $.isString(f.disabledMsg) ? f.disabledMsg: "\u9a8c\u8bc1\u7801\u9519\u8bef";
        this.disabled = true;
        this.stat = {
            code: 0,
            msg: ""
        }
    };
    var md_fn_DynamicCheckCode = function(f) {
        return new md_DynamicCheckCode(f)
    };
    $.augment(md_DynamicCheckCode, {
        ctype: "CHECKER",
        statusCode: {
            netError: {
                code: -1,
                msg: "\u7f51\u7edc\u9519\u8bef"
            },
            empty: {
                code: 1,
                msg: "\u4e0d\u80fd\u4e3a\u7a7a"
            },
            formatError: {
                code: 2,
                msg: "\u683c\u5f0f\u9519\u8bef"
            },
            ajaxError: {
                code: 3,
                msg: "\u9a8c\u8bc1\u7801\u9519\u8bef"
            },
            ok: {
                code: 100,
                msg: ""
            }
        },
        init: function() {
            if (!this.trigger || !this.input || !this.getUrl) {
                return this
            }
            var f = this;
            this.tobtn = md_fn_TimeoutBtn({
                btn: this.trigger,
                timeout: 60,
                disabledCls: f.disabledBtnCls,
                callback: function() {
                    f.resetTriggerTip()
                },
                text: f.btnText,
                waitText: f.btnWaitText,
                waitCls: f.btnWaitCls,
                timeoutText: f.btnTimeoutText,
                timeoutCls: f.btnTimeoutCls,
                autoStart: f.btnAutoStart
            });
            this.trigger.on("click",
            function() {
                f.getCode()
            });
            this.defaultOn && (this.input.on(this.defaultOn,
            function() { ! $.trim(f.input.val()) && f.resetInputTip()
            }));
            this.input.on(this.on,
            function() {
                f.validate()
            });
            this.validate(true);
            return this
        },
        validate: function(l) {
            if (!this.trigger || !this.input || !this.getUrl) {
                this.disabled = false;
                return this.disabled
            }
            var h = this;
            var f = arguments,
            g = $.isPlainObject(f[0]) ? f[0] : {
                def: !!f[0],
                async: !!f[1],
                callback: null,
                context: window
            };
            var l = g.def,
            k = g.async,
            m = g.callback,
            j = g.context;
            this.checkAble(function(n) {
                h.validateCallback(l);
                $.isFunction(m) && m.call(this, n)
            },
            k, j)
        },
        validateCallback: function(f) {
            if (f && this.inputTip.type.toLowerCase() == "error" && !this.inputTip.isHide()) {
                return this.disabled
            }
            this.check(f); ! f && this.checkCallback(this.disabled);
            return this.disabled
        },
        getCode: function() {
			var c = $;
            var f = this;
            this.reset();
            $.ajax({
                url: f.getUrl,
                data: c.isFunction(f.getData) ? f.getData() : f.getData,
                type: "post",
                dataType: "json",
                success: function(g) {
                    if (g.success) {
                        if (f.triggerTip) {
                            f.triggerTip.ok(g.msg || f.msg.ok || f.defaultMsg.ok)
                        }
                        f.input.trigger("focus");
                        f.tobtn.start()
                    } else {
                        f.disabledMsg = "\u8bf7\u91cd\u65b0\u83b7\u53d6\u9a8c\u8bc1\u7801\uff01";
                        if (f.triggerTip) {
                            f.triggerTip.attention(g.msg || f.msg.error || f.defaultMsg.error)
                        }
                    }
                },
                error: function() {
                    f.disabledMsg = "\u8bf7\u91cd\u65b0\u83b7\u53d6\u9a8c\u8bc1\u7801\uff01";
                    if (f.triggerTip) {
                        f.triggerTip.error("\u9a8c\u8bc1\u7801\u53d1\u9001\u5931\u8d25\uff0c\u8bf7\u91cd\u8bd5\uff01")
                    }
                }
            })
        },
        check: function(f) {
            if (f && !$.trim(this.input.val())) { ! this.defaultOn && this.resetInputTip();
                this.resetTriggerTip();
                return
            }
            switch (this.stat.code) {
            case - 1 : case 1:
            case 2:
            case 3:
                this.inputTip.error(this.stat.msg);
                this.input.removeClass(this.errCls);
                $.later(function() {
                    this.input.addClass(this.errCls)
                },
                1, false, this);
                break;
            case 100:
                this.inputTip.hide();
                this.input.removeClass(this.errCls);
                break;
            default:
                break
            }
        },
        checkAble: function(l, j, h) {
            var g = this,
            k, f;
            var h = h || g,
            j = !!j,
            l = $.isFunction(l) && l ||
            function() {};
            if (!$.trim(this.input.val()).length) {
                this.disabled = true;
                this.stat = this.statusCode.empty;
                l.call(h, g.disabled);
                return
            } else {
                if (this._checkAble()) {
                    this.disabled = false;
                    this.stat = this.statusCode.ok
                } else {
                    this.disabled = true;
                    this.stat = this.statusCode.formatError
                }
            }
            if (!this.checkUrl || g.disabled) {
                l.call(h, g.disabled);
                return
            }
            $.io({
                url: g.checkUrl,
                data: $.mix($.isFunction(g.checkData) ? g.checkData() : g.checkData, {
                    code: $.trim(g.input.val())
                }),
                type: "post",
                dataType: "json",
                success: function(m) {
                    var n = m.msg || m.reason || "";
                    n = g.msgTemplate ? g.msgTemplate[n] || n: n;
                    if (m.success) {
                        g.disalbed = false;
                        g.stat = {
                            code: g.statusCode.ok.code,
                            msg: n || g.statusCode.ok.msg
                        }
                    } else {
                        g.disabled = true;
                        g.disabledMsg = "\u9a8c\u8bc1\u7801\u9519\u8bef";
                        g.stat = {
                            code: g.statusCode.ajaxError.code,
                            msg: n || g.statusCode.ajaxError.msg
                        }
                    }
                    l.call(h, g.disabled)
                },
                error: function() {
                    l.call(h, g.disabled)
                }
            })
        },
        _checkAble: function() {
            return !! $.trim(this.input.val()).match(/^\d{6}$/)
        },
        reset: function(f) {
            if (f) {
                return this
            }
            this.disabled = true;
            this.tobtn && this.tobtn.reset();
            this.input && this.input.val("") && this.input.removeClass(this.errCls);
            this.resetTip();
            return this
        },
        resetTriggerTip: function() {
            if (!this.triggerTip) {
                return
            }
            if (this.defaultTriggerTip && this.defaultTriggerTip.type && this.defaultTriggerTip.msg) {
                this.triggerTip.change(this.defaultTriggerTip.type, this.defaultTriggerTip.msg)
            } else {
                this.triggerTip.hide()
            }
        },
        resetInputTip: function() {
            if (!this.inputTip) {
                return this
            }
            if (this.defaultInputTip && this.defaultInputTip.type && this.defaultInputTip.msg) {
                this.inputTip.change(this.defaultInputTip.type, this.defaultInputTip.msg)
            } else {
                this.inputTip.hide()
            }
            return this
        },
        resetTip: function() {
            this.input && this.input.val("");
            this.resetTriggerTip();
            this.resetInputTip();
            return this
        },
        timeoutStart: function() {
            this.tobtn && this.tobtn.start();
            return this
        }
    });
	/*邮箱注册等待确认的页面*/
	var md_Email2Web = function(d) {
        this.email = d || ""
    };
    var md_fn_Email2Web = function(d) {
        return new md_Email2Web(d)
    };
    $.augment(md_Email2Web, {
        loginUrls: {
            "gmail.com": "https://mail.google.com/",
            "live.com": "http://mail.live.com/",
            "live.cn": "http://mail.live.com/",
            "126.com": "http://www.126.com/",
            "163.com": "http://mail.163.com/",
            "163.net": "http://mail.163.net/",
            "188.com": "http://mail.188.com/",
            "sina.com": "http://mail.sina.com.cn/",
            "hotmail.com": "http://www.hotmail.com/",
            "yahoo.com.cn": "http://mail.cn.yahoo.com/",
            "yahoo.cn": "http://mail.cn.yahoo.com/",
            "sohu.com": "http://mail.sohu.com/",
            "21cn.com": "http://mail.21cn.com/",
            "eyou.com": "http://www.eyou.com/",
            "sina.com.cn": "http://mail.sina.com.cn/",
            "qq.com": "http://mail.qq.com/",
            "tom.com": "http://mail.tom.com/",
            "sogou.com": "http://mail.sogou.com/",
            "aol.com": "http://mail.aol.com/"
        },
        regexp: /[^@]+@(.+\.\w+)$/,
        getUrl: function(d) {
            this.email = !!d && d || "";
            if (!this.email) {
                return ""
            }
            var e = this.email.replace(this.regexp, "$1");
            return e && this.loginUrls[e] || ""
        }
    });