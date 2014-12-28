	var message = function(c) {
        if (! (this instanceof message)) {
            return new message(c)
        }
        c = c || {};
        this.wrap = $("#WG_Message");
        this.content = this.wrap.find("p");
        this._init()
    };
    $.augment(message, {
        _init: function() {
            if (!this.wrap || !this.content) {
                return
            }
            return this
        },
        show: function(d, c) {
            this.content.html(d).attr("class", c || "error");
            this.wrap.show();
        },
        hide: function() {
            this.wrap.hide();
        },
        reset: function() {
            this.hide();
            this.content.html("")
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
            //if (!this.input || !this.placeholder || this.input.val()) {
			if (!this.input || !this.placeholder) {
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
            var d = this.input.position();
            this.placeholder.css({
                position: "absolute",
                left: d.left + 25
            }).css({
                top: d.top + Math.floor((this.input.outerHeight() + 2 - this.placeholder.outerHeight()) / 2)
            })
        }
    });
	 /*输入后可以清理名称*/
	 var md_inputclear = function(c) {
        if (! (this instanceof md_inputclear)) {
            return new md_inputclear(c)
        }
        this.input = c.input;
        this.uid = new Date().getTime();
        this.el = null;
        this.force = !!c.force;
        this._init()
    };
    $.augment(md_inputclear, {
        _init: function() {
            if (!this.input || (!this.force && this.nativeSupport())) {
                return
            }
			var ts = this;
            ts.wrap = ts.input.parent();
            ts.input.on("change paste keyup",
            function() {
                if (ts.input.val().length) {
                    ts.show()
                } else {
                    ts.hide()
                }
            });
            ts.input.trigger("change")
        },
        _create: function() {
			var ts = this;
            var c = $('<span class="sl-chacha" id="WG_NickX' + this.uid+'"></span>');
            this.wrap.append(c);
            this.el = $("#WG_NickX" + this.uid);
            this.el.on("click",
            function() {
                ts.input.val("");
                ts.input.focus();
                ts.hide();
				return false;
            })
        },
        nativeSupport: function() {
            return $.browser.msie && $.browser.msie >= 10
        },
        show: function() {
            if (!this.el) {
                this._create()
            }
            this.el[0].style.display = "block";
            return this
        },
        hide: function() {
            if (this.el) {
                this.el[0].style.display = "none"
            }
            return this
        }
    });
	/*用户名*/
	var md_username = function(g) {
        if (! (this instanceof md_username)) {
            return new md_username(g);
        }
        g = g || {};
        this.input = g.elUserName;
        this.checkURL = g.checkURL;
        this.usePhoneTips = !!g.usePhoneTips;
        this.bCBU = g.bCBU;
        this.url = g.checkUserNameURL;
        this.checkcode = g.checkcode;
        this.longlogin = g.longlogin;
        this.CACHE = {};
		this.checkcodeFix = g.checkcodeFix;
        this._init()
    };
    $.augment(md_username, {
        _init: function() {
            if (!this.input) {
                return
            }
            var h = md_tracknick({
                bCBU: this.bCBU
            }).get();
            if (h && !this.input.val()) {
                this.input.val(h)
            }
            md_inputclear({
                input: this.input
            });
            if (this.bCBU) {
                return
            }
            var g = this;
            $.later(function() {
                g._check()
            },
            300, false);
            this.input.on("blur",
            function() {
                g._check();
            })
        },
        _checkLongLogin: function(g) {
            if (!this.longlogin || this.from === "winguo") {
                return
            }
            if (!g) {
                this.longlogin.hide().notCheck()
            } else {
                this.longlogin.show()
            }
        },
        _checkCode: function(g) {
            if (!this.checkcode) {
                return
            }
            if (g) {
                if (this.checkcode.isShow()) {
                    this.checkcode.refresh()
                } else {
                    this.checkcode.show()
                }
            } else {
                this.checkcode.hide()
            }
        },
        _check: function() {
            var h = window.encodeURIComponent($.trim(this.input.val()));
            if (!h) {
                if (this.longlogin) {
                    this.longlogin.hide().notCheck()
                }
                return
            }
            if (this.CACHE.hasOwnProperty(h) && !typeof(this.CACHE[h].needcode)==undefined) {
                this._checkCode(this.CACHE[h].needcode);
                this._checkLongLogin(this.CACHE[h].tag);
                return
            }
            if (this._checking) {
                return
            }
            this._checking = true;
            var g = this;
            $.ajax({
                type: "get",
                url: this.url,
				data:this.input.attr("name")+"="+h,
                cache: false,
                dataType: "json",
                success: function(i) {
                    g.CACHE[h] = i;
                    g._checkCode(i.needcode);
                    g._checkLongLogin(i.tag);
                    g._checking = false;
					g.checkcodeFix.fix()
                },
                error: function() {
                    g._checking = false
                }
            })
        }
    });
	var md_longlogin = function(e) {
        if (! (this instanceof md_longlogin)) {
            return new md_longlogin(e)
        }
        e = e || {};
        this.elLoginBox = e.elLoginBox;
        this.elUserName = e.elUserName;
        this.elWrap = $("#WG_LongLogin_box"),
        this.elChk = $("#WG_LongLogin_input");
        this.elTip = this.elWrap ? this.elWrap.parent().find(".login-tips-content") : null;
        this.defChked = this.elChk ? this.elChk.prop("checked") : false;
        this._init()
    };
    $.augment(md_longlogin, {
        _init: function() {
            if (!this.elWrap || !this.elChk || !this.elLoginBox) {
                return
            }
            var e = this;
            $(function() {
                e.resetChk();
                e.elChk.on("click",
                function(f) {
                    e.updateVal()
                })
            })
        },
        show: function() {
            this.elLoginBox.removeClass("no-longlogin");
            return this
        },
        hide: function() {
            this.elLoginBox.addClass("no-longlogin");
            return this
        },
        notCheck: function() {
            this.elChk.prop("checked", false).val(0);
            return this
        },
        resetChk: function() {
            var e = $("#isLongLogin").val(),
            f = this.elUserName ? this.elUserName.val(): "";
            if (e !== "" && e === f) {
                this.elChk.prop("checked", true)
            } else {
                this.elChk.prop("checked", this.defChked)
            }
            this.updateVal()
        },
        updateVal: function() {
            if (this.elChk.is(":checked")) {
                this.elChk.val(1)
            } else {
                this.elChk.val(0)
            }
        }
    });
	/*提交判断*/
	var md_validator = function (f){
		if (! (this instanceof md_validator)) {
            return new md_validator(f)
        }
        f = f || {};
        this.message = f.message;
        this.checkcode = f.checkcode;
        this.password = f.elPassword;
        this.elUserName = f.elUserName;
        this._init();
	};
	$.augment(md_validator, {
		_init: function() {
            if (!this.message || !this.type) {
                return
            }
        },
        check: function() {
            var f = "";
            f = this._checkStaticForm();
            if (f) {
                if (this.message) {
                    this.message.show(f)
                }
                return false
            }
            return true
        },
		_checkStaticForm: function() {
			var h = "";
			var i = this.elUserName ? $.trim(this.elUserName.val()) : "",
			f = this.checkcode ? $.trim(this.checkcode.val()) : "",
			g = this.password ? this.password.val() : "";
			var a = {
				on: function(f) {
					f.addClass("err-input")
				},
				off: function(f) {
					f.removeClass("err-input")
				}
			};
			if (!i) {
				h = "\u8d26\u6237\u540d";
				if (this.elUserName) {
					a.on(this.elUserName)
				}
			} else {
				if (this.elUserName) {
					a.off(this.elUserName)
				}
			}
			if (!g) {
				if (h) {
					h += "\u548c\u5bc6\u7801"
				} else {
					h = "\u5bc6\u7801";
					if (this.password) {
						this.password.focus()
					}
				}
				if (this.password) {
					a.on(this.password)
				}
			} else {
				if (this.password) {
					a.off(this.password)
				}
			}
			if (this.checkcode && this.checkcode.on && !f) {
				if (!h) {
					h = "\u9a8c\u8bc1\u7801";
					this.checkcode.focus();
					a.on(this.checkcode.input)
				} else {
					a.off(this.checkcode.input)
				}
			}
			return h ? ("\u8bf7\u8f93\u5165" + h) : ""	
		}
	});
	/*提交的按钮*/
	var submitbutton = function(c) {
        if (! (this instanceof submitbutton)) {
            return new submitbutton(c)
        }
        c = c || {};
        this.el = c.el;
        this._init()
    };
    $.augment(submitbutton, {
        _init: function() {
            if (!this.el) {
                return
            }
            this.text = this.el.text();
            return this
        },
        ing: function(c) {
            this.el.text(c);
            return this
        },
        reset: function() {
            this.el.prop("disabled", false);
            this.el.text(this.text);
            return this
        }
    });
	/*表单提交*/
	var LoginSubmit = function(f){
		if (! (this instanceof LoginSubmit)) {
            return new LoginSubmit(f)
        }
		this.cfg = f = f || {};
		this.form = f.elStaticForm;
        this.message = f.message;
		this.havanaEnable = f.havanaEnable;
        this.getTokenURL = f.loginUrl;
		this.password = f.password;
        this.checkcode = f.checkcode;
		this._init()
	}
	$.augment(LoginSubmit, {
		_init: function() {
			if (!this.form) {
                return
            };
            this.validator = md_validator($.extend(this.cfg, {
                message: this.message,
                password: this.password,
                checkcode: this.checkcode
            }));
            this.submitbutton = submitbutton({
                el:this.form.find(".btn-login")
            });
            this._bind();
		},
		_bind: function() {
            var i = this;
            this.form.on("submit",
            function(j) {
                if (i.submitbutton) {
                    i.submitbutton.ing("\u6b63\u5728\u767b\u5f55...")
                }
				if (i.validator.check()) {
					i._login()
				} else {
					if (i.submitbutton) {
						i.submitbutton.reset()
					}
				}
				return false;
            })
        },
		_login: function() {
            var i = this;
            if (!this.havanaEnable) {
                this._submit();
                return
            }
            this._getToken()
        },
        _getToken: function() {
            try {
                var j = this.form[0].elements.newlogin,
                l = this.form[0].elements.callback;
                j && (j.value = "1");
                l && (l.value = "1")
            } catch(k) {}
            var i = this;
            $.ajax({
                type: "post",
                dataType: "json",
                cache: false,
                url: this.getTokenURL,
                data: this.form.serialize(),
                timeout:8000,
                success: function(o) {
                    if (!o) {
                        i._submit();
                        return
                    }
                    if (!o.state) {
                        var n = o.data ? o.data.code: 0;
                        if (!n) {
                            i._submit();
                            return
                        }
                        if (o.data.needrefresh && o.data.url) {
                            window.top.location.href = o.data.url;
                            return
                        }
						if (o.data.token) {
                            $("#WG_Token").val(o.data.token);
                            return
                        }
                        if (i.message && o.message) {
                            if (n === 5 || n === 3101 || n === 3153) {
                            	o.message += ' <a href="#" target="_blank">\u5fd8\u8bb0\u8d26\u6237\u540d</a>\uff1f'
								//忘记账户名
                            }
                            if (n === 3501) {
                                o.message += '<a href="#" target="_blank">\u5fd8\u8bb0\u5bc6\u7801</a>\u6216<a href="#" target="_blank">\u5fd8\u8bb0\u8d26\u6237\u540d</a>\uff1f'
								//忘记密码和账户名
                            }
                            i.message.show(o.message || "\u51fa\u9519\u4e86\uff0c\u8bf7\u7a0d\u540e\u91cd\u8bd5\uff01", "error")
							//出错了，请稍后重试！
                        }
                        if (i.submitbutton) {
                            i.submitbutton.reset()
                        }
                        if (n === 3425) {
                            if (i.checkcode && i.checkcode.isShow()) {
                                i.checkcode.refresh().focus()
                            } else {
                                if (i.checkcode) {
                                    i.checkcode.show().focus()
                                }
                            }
                        } else {
                            i.checkcode && i.checkcode.isShow() && i.checkcode.refresh()
                        }
                    } else {
                        if (o.data) {
							if (o.data.script) {
                            	window.location.href = o.data.url
							} else {
								window.top.location.href = o.data.url
							}
                        } else {
                            i._submit()
                        }
                    }
                },
                error: function() {
                    i._submit()
                }
            })
        },
        _submit: function() {
            var i = this.form[0];
            try {
                var j = i.elements.newlogin,
                l = i.elements.callback;
                j && (j.value = "0");
                l && (l.value = "")
            } catch(k) {}
            i.submit()
        }
	});
	/*验证码*/
	var md_checkcode = function(f) {
        if (! (this instanceof md_checkcode)) {
            return new md_checkcode(f)
        }
        f = f || {};
        this.form = f.elStaticForm;
        this.input = f.elCheckCode;
        this.img = f.elCheckCodeImg;
        this.imgHandler = f.elCheckCodeImgHandler;
        this.wrap = $("#WG_checkcode_box");
        this.elNeedCheckCode = this.form ? $(this.form[0].elements.need_check_code) : null;
        this.src = this.img ? this.img.attr("data-src") : "";
        this.bMini = f.bMini;
        this._refreshed = false;
        this._init();
    };
    $.augment(md_checkcode, {
        _init: function() {
            if (!this.img || !this.imgHandler || !this.wrap) {
                return
            }
            var f = this;
            this.imgHandler.on("click",
            function(g) {
                f.refresh();
                f.focus();
				return false;
            });
            this.img.on("click",
            function() {
                f.refresh();
                f.focus()
            });
            if (this.isShow()) {
                this.refresh()
            }
        },
        refresh: function() {
            this.img.attr("src", this.src + (this.src.indexOf("?") > 0 ? "&": "?") + "_r_=" + new Date().getTime());
            this._refreshed = true
        },
        show: function() {
            if (this.img.attr("src").indexOf("blank") >= 0 || (!this._refreshed && this.bMini && this.isShow())) {
                this.refresh()
            }
            this.wrap.removeClass("hidden");
            this.input.val("");
            if (this.elNeedCheckCode) {
                this.elNeedCheckCode.val("true")
            }
            this.on = true;
            return this
        },
        hide: function() {
            this.wrap.addClass("hidden");
            if (this.elNeedCheckCode) {
                this.elNeedCheckCode.val("")
            }
            this.on = false;
            return this
        },
        val: function() {
            return this.input.val()
        },
        isShow: function() {
            this.on = !this.wrap.hasClass("hidden");
            return this.on
        },
        focus: function() {
            this.input[0].focus();
            return this
        }
    });
	/*判断站点*/
	jQuery.cookie = function(name, value, options) {
		if (typeof value != 'undefined') {
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
	var md_tracknick = function(c) {
        if (! (this instanceof md_tracknick)) {
            return new md_tracknick(c)
        }
        c = c || {};
        this.cookiename = c.bCBU ? "lid": "tracknick";
        this._init()
    };
    $.augment(md_tracknick, {
        _init: function() {},
        get: function() {
            var c = $.cookie(this.cookiename);
            c = c ? window.unescape(c.replace(/(?:#88)$/, "").replace(/\\u/g, "%u")) : "";
            return c
        }
    });
	/*第一步*/
	var fn_mLogin = function(){
		var i = $.unparam(window.location.search.slice(1));
		var s = {
            elLoginBox:$("#WG_loginbox"),
            elStaticForm: $("#SSO_login_form"),
            elUserName: $("#WG_username_input"),
			elUserNamePh: $("#WG_username_ph"),
            elPassword: $("#WG_password"),
			elPasswordPh: $("#WG_password_ph"),
            elCheckCode: $("#WG_checkcode_input"),
			elCheckCodePh: $("#WG_checkcode_ph"),
            elCheckCodeImg: $("#WG_checkcode_Img"),
            elCheckCodeImgHandler: $("#WG_checkcode_refresher"),
			bMini: i && i.style ? !!i.style.match(/^(?:mini|b2b)/) : false,
            bHttps: window.location.protocol === "https:",
			bCBU: ($("#WG_loginsite") ? $("#WG_loginsite").val() : "") === "3"
        };
		s.elLoginBox.removeClass("loading-login");/*加载完毕*/
		var m1 = md_fn_placeholder({
            input:s.elUserName,
            placeholder:s.elUserNamePh,
            blurCls: "ph_blur"
        });
		var m2 = md_fn_placeholder({
            input:s.elPassword,
            placeholder:s.elPasswordPh,
            blurCls: "ph_blur"
        });
		m1 && m1.init();m2 && m2.init();
		this.cfg = {};
		b1 = window.SSOConfig || {};
        b1.havanaEnable = !!b1.enable;
		if (s) {
            this.cfg = $.extend(WG_SSO.INITDATA,s);
			this.cfg = $.extend(this.cfg,b1);
        }
		this.message = message();
		var h = this;
		var j = $.extend(h.cfg,{message:h.message});
		LoginSubmit($.extend(j, {
            form: j.elStaticForm
        }));
	}