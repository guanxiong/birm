﻿var a, background = function() {
    var b = function() {
        var h = max.global;
        if (c) {
            c.style.height = h.getFullHeight() + "px";
            c.style.width = h.getFullWidth() + "px"
        }
    },
    c,
    d = false;
    c = addElement("div");
    setStyle(c, {
        backgroundColor: "black",
        position: "absolute",
        top: "0px",
        left: "0px",
        zIndex: 50
    });
    opacity(c, 30);
    addHandler(window, "resize", b);
    if (max.browser.isIE6) {
        var e;
        e = max.$T("select");
        if (e.length > 0) {
            d = [];
            for (var f, g = 0; g < e.length; g++) {
                f = e[g];
                if (f.style.display != "none" && f.style.visibility != "hidden") {
                    d.push(f);
                    f.style.visibility = "hidden"
                }
            }
        }
        e = null
    }
    b();
    return {
        destroy: function() {
            removeHandler(document, "resize", b);
            if (d) {
                for (var h = 0; h < d.length; h++) d[h].style.visibility = "";
                d = null
            }
            removeElement(c);
            c = null
        }
    }
};
function setButtonDisable(b, c) {
    var d = max.$(b);
    if (d) if (!c && arguments.length == 2) window.setTimeout("setButtonDisable('" + b + "',false,0);", 100);
    else {
        d.disabled = c;
        var e = d.parentNode.parentNode,
        f = "";
        if (e.className.indexOf("minbtn-wrap") > -1) f = "minbtn-disable";
        else if (e.className.indexOf("btn-wrap") > -1) f = "btn-disable";
        if (f) c ? addCssClass(e, f) : removeCssClass(e, f);
        if (c) {
            for (e = d.parentNode; e && e.nodeName.toLowerCase() != "form";) e = e.parentNode; (f = max.$("max_click_object")) && removeElement(f);
            f = document.createElement("span");
            f.style.dispaly = "none";
            f.innerHTML = String.format('<input type="hidden" id="{1}" name="{0}" value="1" />', d.name, "max_click_object");
            e.appendChild(f)
        }
    }
}
var dropdown = function(b, c, d) {
    popupBase.call(this, b, c, d);
    var e = this;
    for (b = 0; b < this.triggers.length; b++) {
        addHandler(this.triggers[b], this.auto ? "mouseover": "click",
        function() {
            e.show()
        });
        addHandler(document.documentElement, "click",
        function() {
            e.hide()
        })
    }
    maxPopupCollection.instance().add(this)
};
dropdown.prototype.show = function() {
    this.list.style.display = "";
    this.onShow && this.onShow(this)
};
dropdown.prototype.hide = function() {
    this.list.style.display = "none"
};
function clickButton(b, c) {
    var d = c ? max.$(c) : document.forms[0];
    c = addElement("input", d);
    c.style.display = "none";
    c.name = b;
    c.value = "1";
    d.submit()
}
function announcement(b) {
    function c() {
        if (d.scrollTop % f || h) {
            d.scrollTop++;
            d.scrollTop %= d.scrollHeight >> 1
        }
        setTimeout(function() {
            c()
        },
        d.scrollTop % f ? 10 : 2E3)
    }
    var d = max.$(b);
    b = 0;
    var e = d.innerHTML,
    f = 0,
    g = d.getElementsByTagName("li");
    b = g.length;
    var h = 1;
    d.onmouseover = function() {
        h = 0
    };
    d.onmouseout = function() {
        h = 1
    };
    addHandler(window, "load",
    function() {
        f += g[0].offsetHeight;
        setStyle(d, {
            height: f + "px",
            overflow: "hidden"
        });
        d.innerHTML += e;
        c()
    })
}
var swfupload_loaded = false;
function initUploader(b) {
    b && loadScript(root + "/max-assets/swfupload/swfupload.js", null,
    function() {
        loadScript(root + "/max-assets/javascript/swfupload_handler.js", null,
        function() {
            swfupload_loaded = true
        })
    })
}
var editorToolBar = {
    full: ["save", "bold", "italic", "underline", "left", "center", "right", "justify", "ol", "ul", "fontSize", "fontFamily", "indent", "outdent", "image", "upload", "link", "unlink", "forecolor", "bgcolor", "removeformat", "xhtml"],
    simple: ["bold", "italic", "underline", "forecolor", "bgcolor"],
    normal: ["bold", "italic", "underline", "left", "center", "right", "justify", "ol", "ul", "fontSize", "fontFamily", "indent", "outdent", "image", "link", "unlink", "forecolor", "bgcolor", "removeformat", "xhtml"],
    setting: ["bold", "italic", "underline", "fontSize", "fontFamily", "image", "link", "unlink", "forecolor", "bgcolor", "removeformat", "xhtml"]
};
function initMiniEditor(b, c, d) {
    var e = null,
    f = {};
    if (typeof b == "string") if (d != true) f.maxHeight = max.coor.height(max.$$(b)[0]);
    f.buttonList = c || editorToolBar.full;
    f.iconsPath = root + "/max-assets/nicedit/nicEditorIcons.gif";
    if (typeof b == "string") {
        if (b = max.$$(b)[0]) e = (new nicEditor(f)).panelInstance(b).nicInstances[0]
    } else nicEditors.allTextAreas(f);
    return e
}
function initEditor(b) {
    KE.init(b);
    KE.create(b.id)
}
function initDisplay(b, c) {
    var d = "";
    _display = function(e, f) {
        for (var g = document.getElementsByName(e), h = 0; h < g.length; h++) {
            var i = g[h];
            if (i.nodeName == "SELECT") {
                d = i.value;
                i.onchange = function() {
                    _display(e, f)
                }
            } else if (i.nodeName == "INPUT" && i.getAttribute("type") == "radio") {
                if (i.checked) d = i.value;
                i.onclick = function() {
                    _display(e, f)
                }
            }
        }
        d = d.toLowerCase();
        for (h = 0; h < f.length; h++) {
            g = f[h];
            if (g.value.toLowerCase() == d) if (typeof g.id == "string") max.$(g.id).style.display = g.display ? "": "none";
            else for (i = 0; i < g.id.length; i++) max.$(g.id[i]).style.display = g.display ? "": "none"
        }
        if (max.browser.isIE6) if (pageLoadComplete) for (var j in maxPanelManager) if ((g = maxPanelManager[j]) && g.panel.frame) {
            h = g.panel.frame;
            g = getRect(g.panel);
            setStyle(h, {
                left: g.left + 5 + "px",
                top: g.top + 5 + "px",
                width: g.width - 10 + "px",
                height: g.height - 10 + "px"
            })
        }
    };
    pageLoadComplete ? _display(b, c) : addPageEndEvent(function() {
        _display(b, c)
    })
}
var pageEndEvents = [],
pageLoadComplete = 0;
function addPageEndEvent(b) {
    pageEndEvents.push(b)
}
function page_end() {
    if (!pageLoadComplete) {
        for (var b in pageEndEvents) pageEndEvents[b] && pageEndEvents[b]();
        if (isExecuteJobTime) {
            ajaxRequest(jobUrl);
            isExecuteJobTime = false
        }
        var c = document.getElementsByTagName("input");
        for (b = 0; b < c.length; b++) {
            var d = c[b];
            if (! (d.type != "submit" || !d.name)) {
                var e = d.id;
                if (!e) {
                    e = "max_unnamedsubmit_" + b;
                    d.id = e
                }
                addHandler(d, "click", new Function('window.setTimeout(function(){ setButtonDisable("' + e + '",true)},20)'))
            }
        }
    }
    pageLoadComplete = 1
}
function showAlert(b) {
    alert(b)
}
function showSuccess(b) {
    alert(b)
}
ajaxCallback = function(b) {
    if (b != null) if (b.iswarning) showAlert(b.message);
    else b.issuccess && showSuccess(b.message)
};
ajaxCallbackLocationError = function(b) {
    if (b != null) if (b.iswarning) showAlert(b.message);
    else b.issuccess && showSuccess(b.message);
    else location.href = "#errormsg"
};
function maxConfirm(b, c, d, e) {
    if (confirm(c)) {
        d();
        return true
    } else return false
}
function showVCode(b, c) {
    var d = b.pop;
    if (b.pop) b.pop.show();
    else {
        b.value = "";
        b.style.color = "black";
        d = addElement("div");
        setStyle(d, {
            border: "solid 1px #ccc",
            backgroundColor: "white",
            height: "25px"
        });
        var e = addElement("a", d);
        e.href = "javascript:;";
        e.onclick = function() {
            f.nodeValue = " \u8f7d\u5165... ";
            var h = this.childNodes[1],
            i = attachQuery(h.src, "rnd", (new Date).getTime());
            removeElement(h);
            h = addElement("img", this);
            setVisible(h, 0);
            h.onload = g;
            h.src = i
        };
        var f = document.createTextNode(" \u8f7d\u5165... ");
        e.appendChild(f);
        e = addElement("img", e);
        setVisible(e, 0);
        e.border = 0;
        d = new popup(d, b, false, null, "top");
        d.createBack = 0;
        d.show();
        b.pop = d;
        function g() {
            var h = this;
            f.nodeValue = "";
            setVisible(h, 1);
            h.parentNode.parentNode.style.height = "auto";
            b.pop.show({
                target: b
            })
        }
        e.onload = g;
        e.src = attachQuery(c, "rnd", (new Date).getTime())
    }
}
function textCounter(b, c, d) {
    var e = d - b.value.length;
    if (e < 0) {
        b.value = b.value.substr(0, d);
        e = 0
    }
    document.getElementById(c).innerHTML = e
}
function ImageError(b) {
    b.error = true;
    b.onload = null;
    b.onerror = null;
    b.src = BBSMAX.ImageDefault;
    b.style.width = "auto";
    b.style.height = "auto";
    b.style.background = "url()"
}
function showImage(b) {
    var c = window._IV;
    if (!c) {
        if (!window.createImageViewer) return;
        c = createImageViewer();
        window._IV = c
    }
    b = b || window.event;
    c.open((b.target || b.srcElement).src)
}
function ImageLoaded(b) {
    addHandler(b, "click", showImage);
    b.processed = 1;
    var c = null;
    c = getRect(b.parentNode).width * 0.95;
    b.firstWidth = b.width;
    b.firstHeight = b.height;
    if (window.threadImageCollection) for (var d = 0; d < threadImageCollection.length; d++) {
        var e = threadImageCollection[d];
        if (b.src == e.src) {
            e.width = b.width;
            e.height = b.height;
            break
        }
    }
    b.width > c && imageScale(b, c);
    if (b.alt) {
        b.otitle = b.alt;
        b.alt = ""
    }
}
function openDialog() {
    var b = arguments;
    if (b.length < 1) return true;
    var c;
    if (typeof b[0] == "string") {
        c = {
            src: b[0]
        };
        if (b.length == 2) if (typeof b[1] == "function") c.return_handler = b[1];
        else {
            if (typeof b[1] == "object") c.trigger = b[1]
        } else if (b.length == 3) if (typeof b[1] == "function") {
            c.return_handler = b[1];
            c.trigger = b[2]
        } else {
            c.return_handler = b[2];
            c.trigger = b[1]
        }
    } else c = b[0];
    b = c.src;
    if (b.indexOf("#") > -1) {
        var d = b.indexOf("#");
        temp = b.substr(d, b.length - d);
        b = b.substr(0, d)
    }
    var e = new ajaxPanel(b, c.trigger, c.return_handler);
    e.waiting();
    e.relocation();
    e.loadPage();
    window.setTimeout(function() {
        e.focus()
    },
    20);
    return false
}
function postToDialog(b) {
    var c, d, e;
    c = b.formId;
    d = b.url;
    b = b.callback;
    e = c ? max.$(c) : document.forms[0];
    if (!e.id) e.id = "bbsmax_default_form";
    if (d.startsWith("?")) d = "default.aspx" + d;
    else if (d.toLowerCase().startsWith(root.toLowerCase() + "/?")) d = root + "/default.aspx" + d.substr(root.length + 1);
    if (typeof d == "undefined") d = e.action;
    c = getFormData(max.$(c), null);
    d = new ajaxPanel(d, null, b);
    d.waiting();
    d.relocation();
    d.postToPage(null, c);
    d.focus();
    return false
}
function loadScript(b, c, d) {
    var e = max.$T("head")[0],
    f = e.getElementsByTagName("script");
    if (f && f.length > 0) for (var g = 0; g < f.length; g++) if (f[g]._src && f[g]._src == b) {
        d && d();
        return
    }
    var h = document.createElement("script");
    h.type = "text/javascript";
    h._src = b;
    h.src = b;
    if (c) h.charset = c;
    if (d) if (max.browser.isIE) h.onreadystatechange = function() {
        if ("complete" == h.readyState || h.readyState == "loaded") d && d()
    };
    else h.onload = function() {
        d && d()
    };
    e.appendChild(h)
}
var maxPanelManager = {},
maxPanelCore = function() {};
a = maxPanelCore.prototype;
a.resize = function(b, c) {
    this.width = b;
    this.height = c;
    setStyle(this.panel, {
        width: b + "px",
        height: c + "px"
    })
};
a.relocation = function() {
    this.trigger ? showPopup(this.panel, this.trigger, this.position, this.offsetLeft, this.offsetTop) : moveToCenter(this.panel);
    if (max.browser.isIE6) if (this.panel.frame) {
        var b = this.panel.frame,
        c = getRect(this.panel);
        setStyle(b, {
            left: c.left + 5 + "px",
            top: c.top + 5 + "px",
            width: c.width - 10 + "px",
            height: c.height - 10 + "px"
        })
    }
};
a.submit = function(b) {
    this.postToPage(null, getFormData(this.forms[0], b))
};
a.setContent = function(b) {
    this.forms = [];
    window.currentPanel = this;
    window.dialog = this;
    var c = this.panel,
    d = b.indexOf("<body>");
    if (d == -1) d = b.indexOf("<body ");
    if (d > -1) {
        b = b.substr(d, b.length - d);
        d = b.indexOf(">") + 1;
        b = b.substr(d, b.length - d)
    }
    d = b.indexOf("</body>");
    if (d > -1) b = b.substr(0, d);
    d = /<script\s[^>]*?src="([^"]+?)"[^>]*?>\s*<\/script>/ig;
    for (var e; e = d.exec(b);) loadScript(e[1]);
    c.body.innerHTML = b;
    d = ["input", "button"];
    e = 0;
    for (var f = (new Date).getMilliseconds().toString(), g = 0, h = 0; h < d.length; h++) {
        var i = c.getElementsByTagName(d[h]);
        if (i && i.length) for (var j = 0; j < i.length; j++) {
            var k = i[j];
            if (k.type == "submit") {
                if (e == 0) {
                    var m = k.parentNode,
                    n = 0;
                    do {
                        n++;
                        if (b > 20) {
                            m = null;
                            break
                        }
                        if (m.tagName.toLowerCase() == "form") break
                    } while ( m = m . parentNode );
                    if (m == null || m.target) continue;
                    f = m.id;
                    if (!f) {
                        f = c.id + "_form_" + e;
                        m.id = f
                    }
                    this.forms.push(m);
                    e++
                }
                if (!k.id) k.id = String.format("max_submit_{0}_{1}", k.name, (new Date).getTime());
                var q = k.id;
				//alert(q);
				/*
                if (e > 0) {
                    n = String.format('var e = arguments.length > 0 ? arguments[0] : event;var p=maxPanelManager["{0}"];  var t = e.srcElement || e.target;', this.panelID);
                    var l = String.format('if(p.onsubmit){ if(!p.onsubmit("{0}")){ return false; }  } document.forms["{0}"].onkeypress=null; ajaxPostForm("{0}",null, "{1}", function(r) { var p=maxPanelManager["{2}"];if(p)p.setContent(r); });  endEvent(e); return false;', f, k.name, this.panelID);
                    q = String.format(n + 't=max.$("' + q + '"); t.disabled="none";if(t.disabled) return false; t.disabled="none";addCssClass(t,"btn-disable"); p.panel.style.cursor="wait";' + l);
                    if (g == 0) {
                        m.defaultButton = k;
                        if (max.browser.isIE || max.browser.isSafari) {
                            m.onsubmit = function(o) {
                                endEvent(o);
                                return false
                            };
                            m.onkeypress = new Function(n + ' if (e.keyCode!=13)return true;  var tn = t.tagName.toLowerCase(); if(tn!="input" && tn!="select") return; ' + String.format(" var f = document.forms['{0}'];  if(!f.defaultButton || f.defaultButton.disabled) return; f.onkeypress=null;", f) + 'p.panel.style.cursor="wait";' + l)
                        }
                    }
                    addHandler(k, "click", new Function(q))
                }
				*/
                g++
            }
        }
    }
    if (this.useAjaxLink) {
        c = c.body.getElementsByTagName("a");
        for (m = 0; m < c.length; m++) {
            d = c[m];
            if (!d.target) {
                e = d.href;
                if (e.indexOf("/max-dialogs/") != -1) if (! (e.indexOf("javascript:") > -1)) if (! (e.indexOf("#") > -1)) if (!d.onclick) {
                    if (e.indexOf("isdialog=1") == -1) e += (hasQuery(e) ? "&": "?") + "isdialog=1";
                    d.href = "javascript:void(panel.loadPage('" + e + "'))"
                }
            }
        }
    }
    this.hasContent || this.relocation();
    this.hasContent = 1;
    execInnerJavascript(b);
    this.panel.style.cursor = "default";
    window.currentPanel = null
};
a.waiting = function() {
    this.setContent(String.format('<div class="dialogloader"><span>\u6b63\u5728\u8f7d\u5165...</span></div>', max.consts.loading16));
    this.hasContent = 0
};
a.loadPage = function(b) {
    var c = this;
    if (b && b.indexOf("isdialog=1") == -1) b += (hasQuery(b) ? "&": "?") + "isdialog=1";
    ajaxRequest(b || this.url,
    function(d) {
        c.setContent(d)
    });
    return false
};
a.refresh = function() {
    this.loadPage()
};
a.postToPage = function(b, c) {
    var d = this;
    ajaxPostData(b || this.url, c,
    function(e) {
        d.setContent(e)
    })
};
a.close = function() {
    if (window.panel == this) window.panel = null;
    if (max.browser.isIE6) if (this.panel.frame) try {
        removeElement(this.panel.frame)
    } catch(b) {}
    this.panel.style.display = "none";
    window.setTimeout(String.format("delete maxPanelManager['{0}']; try{ removeElement(max.$('{0}'))}catch(e){};", this.panelID), 50);
    this.closeCallback && this.result && this.closeCallback(this.result);
    this.onclick && this.onclick(this.panelID);
    if (this.closeHandlers) for (var c = 0; c < this.closeHandlers.length; c++) this.closeHandlers[c](this.panelID);
    return false
};
a.show = function() {
    this.panel.style.display = ""
};
a.addCloseHandler = function(b) {
    if (!this.closeHandlers) this.closeHandlers = [];
    this.closeHandlers.push(b)
};
a.focus = function() {
    window.panel = this;
    window.dialog = this;
    for (var b in maxPanelManager) {
        var c = maxPanelManager[b];
        if (c.panelID != this.panelID) c.panel.style.zIndex = 50
    }
    this.panel.style.zIndex = 51
};
var maxPanel = function(b) {
    var c = 0;
    if (maxPanelManager[b.id]) c = 1;
    var d = b.trigger,
    e = b.w,
    f = b.h,
    g = b.position,
    h = b.cssClass,
    i = b.innerCssClass,
    j = b.bodyCssClass;
    if (c) {
        c = maxPanelManager[b.id].panel;
        c.isOld = 1
    } else {
        c = addElement("div", document.body);
        c.id = b.id;
        c.onclick = function() {
            window.isClickDialog = 1
        }; (c.className = h) && addCssClass(c, h);
        c.inner = addElement("div", c);
        c.inner.className = i;
        c.body = addElement("div", c.inner);
        if (j) c.body.className = j;
        addHandler(c, "mouseover", new Function(String.format('var p=maxPanelManager["{0}"]; window.panel=p; p.isFocus=1;        window.dialog = window.panel;', b.id)));
        addHandler(c, "mouseout", new Function(String.format('maxPanelManager["{0}"].isFocus=0;', b.id)));
        addHandler(c, "mousedown", new Function(String.format('maxPanelManager["{0}"].focus();', b.id)));
        h = "absolute";
        if (!d && !max.browser.isIE6) h = "fixed";
        setStyle(c, {
            position: h,
            zIndex: 50
        });
        maxPanelManager[b.id] = this;
        if (max.browser.isIE6) {
            h = document.createElement("iframe");
            setStyle(h, {
                position: "absolute",
                left: "-0px",
                top: "-0px",
                height: "1px",
                width: "1px",
                zIndex: 1
            });
            h.frameBorder = "0";
            document.body.appendChild(h);
            c.frame = h
        }
    }
    if (e) {
        c.inner.style.width = e + "px";
        this.width = e
    }
    if (f) {
        c.inner.style.height = f + "px";
        this.height = f
    }
    this.panelID = b.id;
    this.trigger = d;
    this.position = g;
    this.panel = c
};
maxPanel.prototype = new maxPanelCore;
function ajaxPanel(b, c, d) {
    var e = root + "/dialogs/",
    f = 0;
    f = "";
    var g = -1;
    f = b.toLowerCase();
    if (f.indexOf("http://") > -1) {
        f = f.substr(f.indexOf("http://") + 7);
        f = f.substr(f.indexOf("/"))
    }
    f = f.substr(e.length);
    if (f.indexOf("/") > -1) f = f.substr(0, f.indexOf("/")).getHashCode();
    else {
        g = f.indexOf("/?") > -1 ? f.indexOf("?", f.indexOf("/?") + 2) : f.indexOf("?");
        f = g == -1 ? f: f.substr(0, g);
        f = f.getHashCode()
    }
    if (f == 0) f = b.getHashCode();
    b += hasQuery(b) ? "&isdialog=1": "?isdialog=1";
    this.url = b;
    maxPanel.call(this, {
        id: "mx_dialog_" + f,
        cssClass: "dialog",
        innerCssClass: "dialog-inner",
        trigger: c
    });
    this.closeCallback = d;
    this.useAjaxLink = 1;
    return this
}
ajaxPanel.prototype = new maxPanelCore;
function openPanel(b, c, d, e, f, g, h) {
    var i = "max_panel_" + b.getHashCode(),
    j = new maxPanel({
        id: i,
        w: e,
        h: f,
        trigger: c,
        position: g,
        cssClass: d,
        innerCssClass: "dropdownmenu",
        bodyCssClass: "clearfix dropdownmenu-inner"
    });
    j.closeCallback = h;
    d = j.panel.body;
    e = String.format('<iframe height="{1}" width="{0}" frameborder="0" scrolling="no"></iframe>', e - 4, f - 4);
    d.innerHTML = e;
    e = d.childNodes[0];
    f = e.contentDocument ? e.contentDocument: e.contentWindow.document;
    f.open();
    f.write(String.format('<img src="{0}" alt=""  />\u6b63\u5728\u8f7d\u5165...', max.consts.loading16));
    f.close();
    h = max.browser.isIE6;
    f = null;
    if (c) {
        i = d = 0;
        for (i = c.parentNode; i;) {
            if (!d && i.className && (i.className.indexOf("scroller") > -1 || i.className.indexOf("datatablewrap")) > -1) {
                d = 0 - i.scrollTop;
                if (h) break
            }
            if (i.nodeName.indexOf("document") != -1) break;
            var k = i.getAttribute("id");
            if (!h && k && k.indexOf("mx_dialog_") > -1) {
                f = i;
                break
            }
            i = i.parentNode
        }
        h = "absolute";
        if (f) h = "fixed";
        j.panel.style.position = h;
        showPopup(j.panel, c, g, 0, d)
    } else moveToCenter(j.panel);
    e.src = b;
    window.setTimeout(function() {
        j.focus()
    },
    20);
    return j
}
var ajaxLayer = function(b, c, d, e) {
    b = "mx_layer_" + b.getHashCode();
    if (ajaxLayer.instance) {
        var f = ajaxLayer.instance;
        f.panelID != b && f.close()
    }
    ajaxLayer._clickTrigger = 1;
    maxPanel.call(this, {
        id: b,
        trigger: c,
        position: d,
        cssClass: e,
        innerCssClass: "dropdownmenu",
        bodyCssClass: "clearfix dropdownmenu-inner"
    });
    if (!ajaxLayer.inited) {
        ajaxLayer.inited = 1;
        ajaxLayer._clickTrigger = 0;
        window.setTimeout(function() {
            addHandler(document.documentElement, "click",
            function() {
                if (!window.isClickDialog) if (ajaxLayer._clickPanel) ajaxLayer._clickPanel = 0;
                else if (ajaxLayer._clickTrigger) ajaxLayer._clickTrigger = 0;
                else if (ajaxLayer.instance) {
                    try {
                        ajaxLayer.instance.close()
                    } catch(g) {}
                    ajaxLayer.instance = null
                }
            })
        },
        20)
    }
    if (max.browser.isIE6) if (this.panel.frame) {
        removeElement(this.panel.frame);
        this.panel.frame = null
    }
    addHandler(this.panel, "click",
    function() {
        ajaxLayer._clickPanel = 1
    });
    ajaxLayer.instance = this
};
ajaxLayer.prototype = new maxPanelCore;
var topLayer = function(b, c, d, e) {
    this.offsetLeft = 15;
    this.position = "right";
    ajaxLayer.call(this, b, c, d, e)
};
topLayer.prototype = new maxPanelCore;
function openTopbarLayer(b, c, d, e, f, g, h) {
    d = d ? "dropdownmenu-wrap " + d: "dropdownmenu-wrap";
    c = new topLayer(b, c, g, d);
    c.setContent('<div class="dropdownmenuloader"><span>\u6b63\u5728\u8f7d\u5165...</span></div>');
    c.hasContent = 0;
    c.loadPage(b);
    c.focus();
    return false
}
function openAjaxLayer(b, c, d, e, f, g, h) {
    d = d ? "dropdownmenu-wrap " + d: "dropdownmenu-wrap";
    c = new ajaxLayer(b, c, g, d);
    c.setContent('<div class="dropdownmenuloader"><span>\u6b63\u5728\u8f7d\u5165...</span></div>');
    c.hasContent = 0;
    c.loadPage(b);
    c.focus();
    return false
}
function openFriendList(b, c, d, e, f, g, h) {
    e = "mx_layer_" + b.getHashCode();
    d = d ? "dropdownmenu-wrap " + d: "dropdownmenu-wrap";
    c = new maxPanel({
        id: e,
        trigger: c,
        position: g,
        cssClass: d,
        innerCssClass: "dropdownmenu",
        bodyCssClass: "clearfix dropdownmenu-inner"
    });
    max.browser.isIE6 || setStyle(c.panel, {
        position: "fixed"
    });
    c.setContent('<div class="dropdownmenuloader"><span>\u6b63\u5728\u8f7d\u5165...</span></div>');
    c.hasContent = 0;
    c.loadPage(b);
    c.focus();
    return false
}
var DynamicTable = function(b, c) {
    this.maxKey = 1;
    var d = max.$$(c);
    if (d) for (c = 0; c < d.length; c++) if (d[c].value != "{0}") if (parseInt(d[c].value) >= this.maxKey) this.maxKey = parseInt(d[c].value) + 1;
    this.newRowId = "newrow-{0}";
    this.deleteTrigger = "deleteRow{0}";
    this.cellContentTemplates = [];
    this.table = max.$(b);
    this.body = null;
    b = this.table.getElementsByTagName("tbody");
    this.body = b.length ? b[0] : this.table;
    this.tamplate = "";
    for (c = 0; c < this.body.rows.length; c++) if (this.body.rows[c].id == "newrow") {
        for (b = 0; b < this.body.rows[c].cells.length; b++) this.cellContentTemplates.push({
            innerHTML: this.body.rows[c].cells[b].innerHTML,
            style: this.body.rows[c].cells[b].style
        });
        break
    }
    removeElement(max.$("newrow"))
};
DynamicTable.prototype.insertRow = function(b) {
    for (var c, d = this.body.insertRow(this.body.rows.length), e = 0; e < this.cellContentTemplates.length; e++) {
        c = d.insertCell(e);
        c.innerHTML = String.format(this.cellContentTemplates[e].innerHTML, this.maxKey);
        if (this.cellContentTemplates[e].style) for (var f in this.cellContentTemplates[e].style) try {
            c.style[f] = this.cellContentTemplates[e].style[f]
        } catch(g) {}
    }
    d.id = String.format(this.newRowId, this.maxKey);
    var h = this,
    i = this.maxKey;
    addHandler(max.$(String.format(this.deleteTrigger, this.maxKey)), "click",
    function() {
        h.deleteRow(i)
    });
    b && b(this.maxKey);
    this.maxKey++
};
DynamicTable.prototype.deleteRow = function(b) {
    b = String.format(this.newRowId, b);
    removeElement(max.$(b))
};
function copyToClipboard(b) {
    if (window.clipboardData) {
        window.clipboardData.clearData();
        window.clipboardData.setData("Text", b)
    } else if (navigator.userAgent.indexOf("Opera") != -1) window.location = b;
    else if (window.netscape) {
        try {
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect")
        } catch(c) {
            alert("\u88ab\u6d4f\u89c8\u5668\u62d2\u7edd\uff01\n\u8bf7\u5728\u6d4f\u89c8\u5668\u5730\u5740\u680f\u8f93\u5165'about:config'\u5e76\u56de\u8f66\n\u7136\u540e\u5c06'signed.applets.codebase_principal_support'\u8bbe\u7f6e\u4e3a'true'");
            return
        }
        var d = Components.classes["@mozilla.org/widget/clipboard;1"].createInstance(Components.interfaces.nsIClipboard);
        if (!d) return;
        var e = Components.classes["@mozilla.org/widget/transferable;1"].createInstance(Components.interfaces.nsITransferable);
        if (!e) return;
        e.addDataFlavor("text/unicode");
        var f = {};
        f = {};
        f = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
        f.data = b;
        e.setTransferData("text/unicode", f, b.length * 2);
        if (!d) return;
        d.setData(e, null, Components.interfaces.nsIClipboard.kGlobalClipboard)
    }
    alert("\u590d\u5236\u6210\u529f\uff01")
}
function initDatePicker(b, c) {
    function d() {
        if (initDatePicker.inited) e();
        else {
            initDatePicker.inited = 1;
            addHandler(document.documentElement, "mousedown", e)
        }
        var h = root + "/max-assets/javascript/datepicker.html?date=" + f.value;
        window.setTimeout(function() {
            var i = openPanel(h, f, "", 310, 190, "auto",
            function(j) {
                f.value = j
            });
            i.focus();
            window.datePanel = i
        },
        50)
    }
    function e() {
        window.datePanel && window.datePanel.close();
        window.datePanel = null
    }
    var f, g;
    f = typeof b == "string" ? max.$(b) : b;
    if (c) g = max.$(c);
    if (! (f.readOnly || f.disabled)) {
        addHandler(f, "click", d);
        g && addHandler(g, "click", d)
    }
}
function initColorSelector(b, c) {
    function d(i) {
        if (g) {
            var j;
            if (g && g.childNodes && g.childNodes.length > 0) j = g.childNodes[0];
            if (j) j.style.backgroundColor = i
        }
    }
    function e() {
        if (initColorSelector.inited) f();
        else {
            initColorSelector.inited = 1;
            addHandler(document.documentElement, "mousedown", f)
        }
        var i = h.value.replace("#", "");
        if (i == "") i = "ffffff";
        var j = root + "/template/default/scripts/colorboard.html?color=" + i;
        window.setTimeout(function() {
            window.colorPanel = openPanel(j, h, "", 240, 240, "auto",
            function(k) {
                h.value = k;
                d(k)
            });
            window.colorPanel.focus()
        },
        50)
    }
    function f() {
        window.colorPanel && window.colorPanel.close();
        window.colorPanel = null
    }
    var g;
    if (c) g = max.$(c);
    var h = typeof b == "string" ? max.$(b) : b;
    b = h.value;
    if (b == "") b = "ffffff";
    else {
        d(b);
        b = b.replace("#", "")
    }
    b = parseInt(b, 16);
    b = to16(16777215 ^ b);
    if (!h.readOnly) {
        h.readOnly = true;
        addHandler(h, "click", e);
        g && addHandler(g, "click", e)
    }
};
if (!window.root) window.root = "";
var keyEnum = {
    enter: "13",
    ctrl: "17",
    space: "32",
    backspace: "8",
    shift: "16",
    esc: "27"
};
a = String.prototype;
a.contains = function(b) {
    return this.indexOf(b) > -1
};
a.trim = function(b) {
    return b ? this.trimEnd(b).trimStart(b) : this.replace(/(^[ \t\n\r]*)|([ \t\n\r]*$)/g, "")
};
a.trimEnd = function(b) {
    if (this.endsWith(b)) return this.substring(0, this.length - b.length);
    return this
};
a.trimStart = function(b) {
    if (this.startsWith(b)) return this.slice(b.length);
    return this
};
a.startsWith = function(b) {
    return this.indexOf(b) == 0
};
a.endsWith = function(b) {
    return b.length <= this.length && this.substr(this.length - b.length, b.length) == b
};
a.remove = function(b, c) {
    var d = this.substring(0, b);
    b = this.substring(b + c, this.length);
    return d + b
};
a.insert = function(b, c) {
    var d = this.substring(0, b);
    b = this.substring(b, this.length);
    return d + c + b
};
a.getHashCode = function() {
    for (var b = 31,
    c = 0,
    d = this.length; c < d;) b ^= (b << 5) + (b >> 2) + this.charCodeAt(c++);
    return b
};
String.isNullOrEmpty = function(b) {
    return b
};
String.format = function() {
    for (var b = arguments[0], c = 1; c < arguments.length; c++) b = b.replace(new RegExp("\\{" + (c - 1) + "\\}", "ig"), arguments[c]);
    return b
};
Array.prototype.contains = function(b) {
    for (var c = 0; c < this.length; c++) if (b == this[c]) return true;
    return false
};
var stringBuilder = function(b) {
    this.arr = [];
    this.length = 0;
    if (b) {
        this.arr.push(b);
        this.length += b.length
    }
    if (!stringBuilder.created) {
        stringBuilder.prototype.append = function(c) {
            this.arr.push(c);
            this.length += c.length;
            return this
        };
        stringBuilder.prototype.appendFormat = function() {
            for (var c = arguments[0], d = 1; d < arguments.length; d++) c = c.replace(new RegExp("\\{" + (d - 1) + "\\}", "ig"), arguments[d]);
            this.append(c)
        };
        stringBuilder.prototype.clear = function() {
            this.arr.splice(0, this.arr.length);
            this.length = 0;
            return this
        };
        stringBuilder.prototype.insert = function(c, d) {
            var e = 0,
            f = 0;
            if (c == 0) this.arr.unshift(d);
            else for (var g = 0; g < this.arr.length; g++) {
                e += this.arr[g].length;
                if (e >= c) {
                    f = e - c;
                    c = this.arr[g];
                    f = c.length - f;
                    c = c.substring(0, f) + d + c.substring(f, c.length);
                    this.arr[g] = c;
                    break
                }
            }
            return this
        };
        stringBuilder.prototype.remove = function(c, d) {
            var e = 0,
            f = -1,
            g = -1,
            h = 0,
            i = c + d;
            if (d <= 0) return this;
            for (d = 0; d < this.arr.length; d++) {
                e += this.arr[d].length;
                if (e >= c && f == -1) {
                    f = d + 1;
                    var j = this.arr[d];
                    h = j.length - (e - c);
                    if (h < j.length) {
                        var k = j.substring(h, j.length);
                        j = j.substring(0, h);
                        this.arr[d] = j;
                        if (this.arr.length == d + 1) this.arr.push(k);
                        else this.arr[d + 1] = k + this.arr[d + 1];
                        e -= k.length
                    }
                } else if (e >= i) {
                    g = d;
                    h = i - e;
                    if (h > 0) {
                        j = this.arr[d];
                        h = j.length - h;
                        k = j.substring(h, j.length);
                        j = j.substring(0, j.length - h);
                        this.arr[d] = j;
                        this.arr.splice(d, 0, k)
                    }
                    this.arr.splice(f, g - f);
                    break
                }
            }
            return this
        };
        stringBuilder.prototype.toString = function() {
            return this.arr.join("")
        };
        stringBuilder.created = true
    }
},
max = {
    browser: {
        isIE: navigator.userAgent.toLowerCase().contains("msie"),
        isIE5: navigator.userAgent.toLowerCase().contains("msie 5"),
        isIE6: navigator.userAgent.toLowerCase().contains("msie 6"),
        isIE7: navigator.userAgent.toLowerCase().contains("msie 7"),
        isIE8: navigator.userAgent.toLowerCase().contains("msie 8"),
        isIE9: navigator.userAgent.toLowerCase().contains("msie 9"),
        isGecko: navigator.userAgent.toLowerCase().contains("gecko"),
        isSafari: navigator.userAgent.toLowerCase().contains("safari"),
        isOpera: navigator.userAgent.toLowerCase().contains("opera")
    },
    global: {
        getClientWidth: function() {
            return document.documentElement && document.documentElement.clientWidth || document.body.clientWidth
        },
        getClientHeight: function() {
            return document.documentElement && document.documentElement.clientHeight || document.body.clientHeight
        },
        getScrollTop: function() {
            return document.documentElement && document.documentElement.scrollTop || document.body.scrollTop
        },
        getScrollLeft: function() {
            return document.documentElement && document.documentElement.scrollLeft || document.body.scrollLeft
        },
        getFullHeight: function() {
            return document.documentElement.clientHeight > document.documentElement.scrollHeight ? document.documentElement.clientHeight: document.documentElement.scrollHeight
        },
        getFullWidth: function() {
            return document.documentElement.scrollWidth
        },
        getBrowserRect: function() {
            var b = {};
            b.left = this.getScrollLeft();
            b.top = this.getScrollTop();
            b.width = this.getClientWidth();
            b.height = this.getClientHeight();
            b.bottom = b.top + b.height;
            b.right = b.left + b.width;
            return b
        }
    },
    coor: {
        left: function(b, c) {
            if (typeof c == "number") {
                b.style.position = "absolute";
                b.style.left = c + "px"
            } else {
                c = b.offsetLeft;
                if (b.offsetParent != null) c += Left(b.offsetParent);
                return c
            }
        },
        top: function(b, c) {
            if (typeof c == "number") {
                b.style.position = "absolute";
                b.style.top = c + "px"
            } else {
                c = b.offsetTop;
                if (b.offsetParent != null) c += Top(b.offsetParent);
                return c
            }
        },
        width: function(b, c) {
            if (typeof c == "number") b.style.width = c + "px";
            else return b.offsetWidth
        },
        height: function(b, c) {
            if (typeof c == "number") b.style.height = c + "px";
            else return b.offsetHeight
        },
        getRect: function(b) {
            var c = {};
            c.left = getLeft(b);
            c.top = getTop(b);
            c.width = getWidth(b);
            c.height = getHeight(b);
            c.bottom = c.top + c.height;
            c.right = c.left + c.width;
            return c
        }
    },
    consts: {
        loading16: root + "/Public/agent/images/loading.gif",
        loading32: root + "/Public/agent/images/loading.gif"
    },
    eval: function(b) {
        if (b) {
            b = b.trim();
            if (b.indexOf("<!--") == 0) b = b.substr(4);
            if (b) return max.browser.isIE ? execScript(b) : window.eval(b)
        }
    },
    $: function(b) {
        return typeof b == "string" ? document.getElementById(b) : b
    },
    $$: function(b) {
        return typeof b == "string" ? document.getElementsByName(b) : null
    },
    $T: function(b) {
        return typeof b == "string" ? document.getElementsByTagName(b) : null
    }
};
function setStyle(b, c) {
    for (var d in c) b.style[d] = c[d]
}
function addCssClass(b, c) {
    if (b.className) b.className.indexOf(c) > -1 || (b.className += " " + c);
    else b.className = c
}
function removeCssClass(b, c) {
    var d = b.className;
    if (d) {
        var e = d.indexOf(c);
        if (e != -1) {
            if (d.length == c.length) d = "";
            else if (e == 0) d = d.remove(0, c.length).trim();
            else if (e > 0) d = d.remove(e, c.length).trim().replace("  ", " ");
            b.className = d
        }
    }
}
function addElement(b, c) {
    b = document.createElement(b);
    typeof c != "undefined" ? c.appendChild(b) : document.body.appendChild(b);
    return b
}
function getFileSize(b) {
    for (var c = 0; b > 1024;) {
        c++;
        b /= 1024
    }
    b = parseFloat(b).toFixed(2);
    return b + ["B", "KB", "MB", "GB", "TB"][c]
}
function showPreview(b, c) {
    var d = max.$("face_preview"),
    e = max.$("preview_container");
    if (!d) {
        d = addElement("div");
        d.id = "face_preview";
        e = addElement("div", d);
        e.id = "preview_container"
    }
    e = addElement("img", e);
    e.onload = function() {
        if (this.parentNode) {
            AvatarLoaded(this);
            d.style.visibility = "visible"
        }
    };
    e.width = d.offsetWidth;
    e.height = d.offsetHeight;
    e.src = c;
    c = getLeft(b);
    e = d.style;
    e.left = c > 100 ? "10px": "auto";
    e.right = c > 100 ? "auto": "10px";
    e.position = "absolute";
    b.onmouseout || (b.onmouseout = hidePreview)
}
function hidePreview() {
    max.$("preview_container").innerHTML = "";
    max.$("face_preview").style.visibility = "hidden"
}
function AvatarLoaded(b, c) {
    function d() {
        b.width == 0 && b.height == 0 && setTimeout(d, 10)
    }
    b.onload = null;
    b.onerror = null
}
function removeElement(b) {
    if (typeof b == "string") b = max.$(b);
    b.parentNode.removeChild(b)
}
function attr(b, c, d) {
    if (d) b.setAttribute(c, d);
    else return b.getAttribute(c)
}
var endEvent = function(b) {
    if (max.browser.isIE) {
        if (event) {
            event.cancelBubble = true;
            event.returnValue = false
        }
    } else {
        b.preventDefault();
        b.stopPropagation()
    }
    return false
};
function addHandler(b, c, d) {
    if (c.indexOf("on", 0) == 0) c = c.substring(2, c.length);
    max.browser.isIE ? b.attachEvent("on" + c, d) : b.addEventListener(c, d, false)
}
function hideElement(b, c) {
    var d = new timer(50,
    function(e) {
        if (e <= 5) opacity(b, 100 - e * 20);
        else {
            c && c();
            d.stop();
            d = null
        }
    });
    d.start()
}
function HTMLEncode(b) {
    var c = document.createElement("div");
    c.textContent != null ? (c.textContent = b) : (c.innerText = b);
    return c.innerHTML
}
function refresh() {
    var b = location.href;
    if (b.indexOf("#", 0) > -1) b = b.substring(0, b.indexOf("#", 0));
    location.replace(b)
}
function writeCookie(b, c, d) {
    var e = "";
    if (d) {
        e = new Date((new Date).getTime() + d * 36E5);
        e = "; expires=" + e.toGMTString()
    }
    document.cookie = b + "=" + escape(c) + e
}
function deleteCookie(b) {
    var c = new Date;
    c.setTime(c.getTime() - 1);
    document.cookie = b + "=; expires=" + c.toGMTString()
}
function readCookie(b) {
    var c = "";
    b = b + "=";
    if (document.cookie.length > 0) {
        offset = document.cookie.indexOf(b);
        if (offset != -1) {
            offset += b.length;
            end = document.cookie.indexOf(";", offset);
            if (end == -1) end = document.cookie.length;
            c = unescape(document.cookie.substring(offset, end))
        }
    }
    return c
}
function hide(b, c) {
    hideElement(b,
    function() {
        var d = max.coor.height(b);
        if (b.style.borderTopWidth != "" && !isNaN(b.style.borderTopWidth)) d -= parseInt(b.style.borderTopWidth);
        if (b.style.borderBottomWidth != "" && !isNaN(b.style.borderBottomWidth)) d -= parseInt(b.style.borderBottomWidth);
        if (b.style.paddingTop != "" && !isNaN(b.style.paddingTop)) d -= parseInt(b.style.paddingTop);
        if (b.style.paddingBottom != "" && !isNaN(b.style.paddingBottom)) d -= parseInt(b.style.paddingBottom);
        _buf.push({
            id: b.id,
            ov: b.style.overflow,
            height: d,
            ds: b.style.display
        });
        setStyle(b, {
            overflow: "hidden"
        });
        var e, f = new timer(10,
        function(g) {
            if (g <= 10) {
                e = (10 - g) / 10 * d + "px";
                setStyle(b, {
                    height: e
                })
            } else {
                f.stop();
                f = null;
                setStyle(b, {
                    display: "none"
                });
                c && c()
            }
        });
        f.start()
    })
}
var _buf = [];
function show(b) {
    var c = null,
    d;
    for (var e in _buf) if (_buf[e].id == b.id) {
        d = e;
        c = _buf[e];
        break
    }
    if (c == null) setStyle(b, {
        display: ""
    });
    else {
        setStyle(b, {
            display: c.ds
        });
        var f = new timer(10,
        function(g) {
            if (g <= 10) setStyle(b, {
                height: c.height * (g / 10) + "px"
            });
            else if (g <= 20) opacity(b, (g - 10) * 10);
            else {
                f.stop();
                f = null;
                setStyle(b, {
                    overflow: c.ov,
                    height: c.height + "px"
                });
                _buf.splice(d, 1)
            }
        });
        f.start()
    }
}
function delElement(b, c) {
    if (typeof b.toString().toLowerCase() == "string") b = max.$(b);
    hide(b,
    function() {
        for (var d in _buf) if (_buf[d].id == b.id) {
            _buf.splice(d, 1);
            break
        }
        removeElement(b);
        c && c()
    })
}
function setVisible(b, c) {
    b.style.display = c ? "": "none"
}
function opacity(b, c) {
    if (max.browser.isIE) b.style.filter = "alpha(opacity=" + c + ")";
    else setStyle(b, {
        opacity: c / 100,
        MozOpacity: c / 100,
        KhtmlOpacity: c / 100,
        filter: "alpha(opacity=" + c + ")"
    })
}
function removeHandler(b, c, d) {
    if (c.indexOf("on", 0) >= 0) c = c.substring(2, c.length);
    max.browser.isIE ? b.detachEvent("on" + c, d) : b.removeEventListener(c, d, false)
}
function isUndefined(b) {
    return typeof b == "undefined" ? true: false
}
function getTop(b) {
    var c = b.offsetTop;
    if (b.offsetParent != null) c += getTop(b.offsetParent);
    return c
}
function getLeft(b) {
    var c = b.offsetLeft;
    if (b.offsetParent != null) c += getLeft(b.offsetParent);
    return c
}
function getWidth(b) {
    return b.offsetWidth
}
function getHeight(b) {
    return b.offsetHeight
}
function getRect(b) {
    var c = {};
    c.left = getLeft(b);
    c.top = getTop(b);
    c.width = getWidth(b);
    c.height = getHeight(b);
    c.bottom = c.top + c.height;
    c.right = c.left + c.width;
    return c
}
function onEnterSubmit(b, c) {
    addHandler(max.$(b), "keydown",
    function(d) {
        if ((d = d || window.event) && (d.keyCode == 13 || d.which == 13)) {
            for (d = d.target || d.srcElement; d && d.nodeName.toLowerCase() != "form";) d = d.parentNode;
            c ? clickButton(c, d.id) : d.submit();
            return false
        }
    })
}
function onCtrlEnter(b, c) {
    for (var d = typeof b != "object" ? max.$(b) : b; d && d.nodeName.toLowerCase() != "form";) d = d.parentNode;
    d && addHandler(d, "keydown",
    function(e) {
        e = e || window.event;
        if (d.focus) if (e && (e.keyCode == 13 || e.which == 13) && e.ctrlKey) c(e)
    })
}
var attachQuery = function(b, c, d) {
    var e = new RegExp("(\\?|&)" + c + "=.*?(&|$)", "ig");
    if (e.test(b)) b = b.replace(e, "$1" + c + "=" + escape(d) + "$2");
    else {
        e = b.indexOf("?") > -1 ? "&": "?";
        b += e + c + "=" + escape(d)
    }
    return b
},
moveToCenter = function(b) {
    var c = getRect(b),
    d = max.global.getBrowserRect();
    setVisible(b, true);
    var e = d.top + (d.height - c.height) / 2;
    c = d.left + (d.width - c.width) / 2;
    if (b.style.position == "fixed") {
        e -= d.top;
        c -= d.left
    }
    setStyle(b, {
        left: c + "px",
        top: e + "px"
    })
},
maxDragObject = function(b, c) {
    function d(h) {
        if (b.parentNode == null) {
            removeHandler(window, "mouseup", d);
            removeHandler(window, "mousemove", e)
        } else {
            h = h || window.event;
            if (b.drag) {
                b.drag = 0;
                if (max.browser.isIE) g.releaseCapture();
                else {
                    window.releaseEvents(Event.MOUSEMOVE | Event.MOUSEUP);
                    h.preventDefault()
                }
                document.body.onselectstart = null
            }
        }
    }
    function e(h) {
        if (b.drag) {
            h = h || window.event;
            var i;
            i = h.clientX - b._x;
            h = h.clientY - b._y;
            setStyle(b, {
                left: i + "px",
                top: h + "px"
            });
            max.browser.isIE6 && b.frame && setStyle(b.frame, {
                left: i + 5 + "px",
                top: h + 5 + "px"
            })
        }
    }
    function f(h) {
        h = h || window.event;
        if (max.browser.isIE) g.setCapture();
        else {
            window.captureEvents(Event.MOUSEMOVE | Event.MOUSEUP);
            h.preventDefault()
        }
        var i = getLeft(b),
        j = getTop(b);
        b._x = h.clientX - i;
        b._y = h.clientY - j;
        b.drag = 1;
        document.body.onselectstart = function() {
            return false
        }
    }
    var g = c || b;
    addHandler(g, "mousedown", f);
    if (!max.browser.isIE) g = g = window;
    addHandler(g, "mousemove", e);
    addHandler(g, "mouseup", d)
},
showPopup = function(b, c, d, e, f) {
    d || (d = "auto");
    var g = b.style;
    g.display = "";
    var h;
    rt = max.coor.getRect(c);
    rl = max.coor.getRect(b);
    rw = max.global.getBrowserRect();
    b = rt.bottom + rl.height < rw.bottom ? rt.bottom: rw.bottom - rt.bottom > rt.top - rw.top ? rt.bottom: rt.top - rl.height;
    h = rt.left + rl.width > rw.right ? rt.right - rl.width: rt.left;
    if (d.indexOf("left") > -1) h = rt.left;
    else if (d.indexOf("right") > -1) h = rt.right - rl.width;
    else if (d.indexOf("center") > -1) h = rt.left + rt.width / 2 - rl.width / 2;
    if (d.indexOf("top") > -1) b = rt.top - rl.height;
    else if (d.indexOf("bottom") > -1) b = rt.bottom;
    if (c.style.position == "fixed") {
        h += rw.left;
        b += rw.top
    }
    if (e) h += e;
    if (f) b += f;
    g.left = h + "px";
    g.top = b + "px";
    if (c) if (c = elementInDialog(c)) {
        c = c.style.zIndex;
        g.zIndex = c ? c + 1 : 50
    }
},
timer = function(b, c) {
    this.counter = 0;
    this.interval = b;
    this.ontick = c;
    this.handler = null
};
timer.prototype.start = function() {
    var b = this;
    this.handler = window.setInterval(function() {
        b.ontick(++b.counter)
    },
    this.interval)
};
timer.prototype.stop = function() {
    window.clearInterval(this.handler);
    this.interval = 0
};
var checkboxList = function(b, c, d) {
    function e() {
        if (i) if (j.length) {
            for (var l = true,
            o = 0; o < j.length; o++) if (j[o].checked != true) {
                l = false;
                break
            }
            i.checked = l
        }
    }
    function f() {
        for (var l = 0; l < j.length; l++) {
            j[l].checked = i.checked;
            this.onSelectAllCallItemChange && g(j[l])
        }
    }
    function g(l) {
        k && k(l)
    }
    function h(l) {
        function o(p) {
            t = (max.browser.isIE ? window.event.keyCode: p.which) == keyEnum.shift
        }
        function r() {
            t = false
        }
        function v() {
            if (t == true) {
                s = -1;
                for (var p = 0; p < j.length; p++) if (j[p].checked == true) {
                    if (s == -1) s = p;
                    u = p
                }
                if (s > -1) for (p = s; p <= u; p++) {
                    j[p].checked = true;
                    g(j[p])
                }
                e()
            }
        }
        var s, u, t = false;
        l = l ? max.$(l) : document.body;
        addHandler(l, "click", v);
        addHandler(l, "keydown", o);
        addHandler(l, "keyup", r)
    }
    var i, j, k;
    this.onSelectAllCallItemChange = true;
    if (b instanceof Array) {
        j = [];
        for (var m, n = 0; n < b.length; n++) {
            m = max.$$(b[n]);
            for (var q = 0; q < m.length; q++) j.push(m[q])
        }
    } else j = max.$$(b);
    if (c) {
        i = max.$(c);
        i != null && addHandler(i, "click", f)
    }
    for (n = 0; n < j.length; n++) addHandler(j[n], "click",
    function(l) {
        i && e();
        g(max.browser.isIE ? window.event.srcElement: l.target)
    });
    h(d);
    return {
        selectedList: function() {
            var l, o = 0;
            l = [];
            for (var r = 0; r < j.length; r++) if (j[r].checked) {
                l[o] = j[r].value;
                o++
            }
            return l
        },
        selectCount: function() {
            for (var l = 0,
            o = 0; o < j.length; o++) j[o].checked && l++;
            return l
        },
        reverse: function() {
            for (var l = 0; l < j.length; l++) {
                j[l].checked = !j[l].checked;
                g(j[l])
            }
            e()
        },
        selectAll: function() {
            for (var l = 0; l < j.length; l++) {
                j[l].checked = true;
                g(j[l])
            }
            e()
        },
        SetItemChangeHandler: function(l) {
            k = l
        }
    }
};
function findElement(b, c) {
    var d;
    b = max.$T(b);
    d = [];
    for (var e = 0; e < b.length; e++) b[e].id && b[e].id.indexOf(c, 0) >= 0 && d.push(b[e]);
    return d
}
function textareaInsert(b, c, d) {
    max.$(b).value += c
}
function imageScale(b, c, d) {
    var e = b.width,
    f = b.height,
    g = e / f;
    if (c && e > c) {
        b.width = c;
        b.height = c / g
    }
    if (d && f > d) {
        b.height = d;
        b.width = d * g
    }
    if (c && d) if (b.width > c) imageScale(b, c);
    else b.height > d && imageScale(b, 0, d);
    if (max.browser.isIE6) if (b.parentNode.className == "thumb") {
        c = b.parentNode.offsetHeight;
        if (c > b.height) b.style.paddingTop = (c - b.height) / 2 + "px"
    }
}
function hasQuery(b) {
    if (b.contains("/?")) {
        var c = b.toString().indexOf("/?");
        return b.toString().indexOf("?", c + 2) != -1
    }
    c = b.toLowerCase();
    if (c.contains("/default.aspx?")) {
        c = c.toString().indexOf("/default.aspx?");
        return b.toString().indexOf("?", c + 14) != -1
    } else if (c.contains("/index.aspx?")) {
        c = c.toString().indexOf("/index.aspx?");
        return b.toString().indexOf("?", c + 12) != -1
    } else return b.contains("?")
}
function scrollToBottom(b) {
    max.$(b).scrollTop = 65535
}
function ctrlEnterEvent(b, c, d) {
    for (var e = 1; e < arguments.length; e++) {
        var f = document.getElementById(arguments[e]);
        if (f) f.onkeydown = function(g) {
            g = g || window.event;
            g.ctrlKey && g.keyCode == 13 && b && b()
        }
    }
}
var execInnerJavascript = function(b) {
    var c, d;
    c = max.browser.isIE ? /<script[^>]+?>((?:.|\n|\r)+?)<\/script>/ig: /<script[^>]*?>((?:.|\n|\r)*?)<\/script>/ig;
    d = /<!--\{js(?:\s|\n)((?:.|\n|\r)+?)\}--\>/ig;
    for (var e; e = c.exec(b);) max.eval(e[1]);
    for (; e = d.exec(b);) max.eval(e[1])
};
function to16(b) {
    function c(e) {
        switch (e) {
        case 0:
            return "0";
        case 1:
            return "1";
        case 2:
            return "2";
        case 3:
            return "3";
        case 4:
            return "4";
        case 5:
            return "5";
        case 6:
            return "6";
        case 7:
            return "7";
        case 8:
            return "8";
        case 9:
            return "9";
        case 10:
            return "A";
        case 11:
            return "B";
        case 12:
            return "C";
        case 13:
            return "D";
        case 14:
            return "E";
        case 15:
            return "F"
        }
    }
    b = b;
    if (isNaN(b)) return 0;
    else {
        for (var d = ""; b >= 16;) {
            d = c(b % 16) + d;
            b = parseInt(b / 16)
        }
        return d = c(b % 16) + d
    }
}
ajaxWorker = function(b, c, d) {
    this.r = null;
    this.url = b;
    this.method = c;
    this.content = d;
    this.header = {};
    this.header["Content-type"] = "application/x-www-form-urlencoded";
    this.header["If-Modified-Since"] = "0";
    this.header["X-Requested-With"] = "xmlhttprequest";
    var e = this;
    if (window.XMLHttpRequest) this.r = new XMLHttpRequest;
    else if (window.ActiveXObject) try {
        this.r = new ActiveXObject("Msxml2.XMLHTTP")
    } catch(f) {
        try {
            this.r = new ActiveXObject("Microsoft.XMLHTTP")
        } catch(g) {}
    }
    this.addListener = function(h, i) {
        if (!this.L) this.L = [];
        this.L[h] = i;
        return this
    };
    this.setHeader = function(h, i) {
        this.header[h] = i;
        this.r.setRequestHeader(h, i);
        return this
    };
    this.send = function(h) {
        if (this.method != "post" && this.method != "get") this.method = "get";
        this.r.open(this.method, h ? h: this.url, true);
        for (var i in this.header) this.r.setRequestHeader(i, this.header[i]);
        this.r.send(this.content)
    };
    if (this.r) this.r.onreadystatechange = function() {
        e.r.readyState == 4 && e.L[e.r.status] != null && e.L[e.r.status](e.r.responseText)
    }
};
ajaxRender = function(b, c, d) {
    if (!b || b == "") b = location.href;
    if (!c || c == "") c = "*";
    var e = "";
    if (b.indexOf("#") > -1) {
        var f = b.indexOf("#");
        e = b.substr(f, b.length - f);
        b = b.substr(0, f)
    }
    b = b.replace(/&?_max_ajaxids_=[^&$]+/ig, "");
    b = b.replace(/&?_random_query_id_=\d+/ig, "");
    b = b.trimEnd("?");
    b += hasQuery(b) ? "&_max_ajaxids_=" + c + "&_random_query_id_=" + (new Date).getMilliseconds() : "?_max_ajaxids_=" + c + "&_random_query_id_=" + (new Date).getMilliseconds();
    b += e; (new ajaxWorker(b)).addListener(200,
    function(g) {
        for (var h = {},
        i = 0;;) {
            var j = g.indexOf("|", i);
            if (j <= i) break;
            var k = g.substring(i, j);
            i = j + 1;
            var m = g.indexOf("|", i);
            if (m <= i) break;
            i = parseInt(g.substring(i, m));
            j = g.substr(m + 1, i);
            i = m + i + 2;
            h[k] = j;
            if (i >= g.length) break
        }
        g = null;
        for (k in h) if (k == "_max_ajaxresult_") g = eval("(" + h[k] + ")");
        else {
            i = max.$(k);
            j = h[k];
            if (i) {
                i.onUpdate && i.onUpdate(i);
                i.innerHTML = j
            }
            execInnerJavascript(j)
        }
        d != null && d(g)
    }).send();
    return false
};
getFormData = function(b, c) {
    for (var d = {},
    e = 0,
    f = b.elements.length; e < f; e++) {
        var g = b.elements[e],
        h = g.name;
        if (!g.disabled) if (h) {
            d[h] || (d[h] = []);
            switch (g.tagName.toLowerCase()) {
            case "input":
                switch (g.type) {
                case "text":
                case "hidden":
                case "password":
                    d[h].push(g.value);
                case "radio":
                case "checkbox":
                    g.checked && d[h].push(g.value);
                    break
                }
                break;
            case "textarea":
                d[h].push(g.value);
                break;
            case "select":
                for (var i = 0,
                j = 0; j < g.options.length; j++) if (g.options[j].selected) {
                    d[h].push(g.options[j].value);
                    i = 1
                }
                i || g.selectedIndex >= 0 && d[h].push(g.options[g.selectedIndex].value);
                break
            }
        }
    }
    if (c) {
        d[c] || (d[c] = []);
        d[c].push("")
    }
    b = "";
    for (g in d) {
        e = 0;
        for (f = d[g].length; e < f; e++) {
            if (b != "") b += "&";
            b += g + "=" + encodeURIComponent(d[g][e])
        }
    }
    return b
};
ajaxPostData = function(b, c, d) {
    c = new ajaxWorker(b, "post", c);
    c.addListener(200,
    function(e) {
        d && d(e)
    });
    c.addListener(500,
    function(e) {
        d && d(e)
    });
    c.addListener(404,
    function() {
        d && d("404 Page Not Found")
    });
    c.addListener(403,
    function() {
        d && d("403 Forbidden")
    });
    c.addListener(400,
    function() {
        d && d("400 \u57df\u540d\u4e0d\u80fd\u6b63\u786e\u89e3\u6790" + b)
    });
    c.send()
};
function ajaxRequest(b, c) {
    b = new ajaxWorker(b, "GET");
    b.addListener(200,
    function(d) {
        c && c(d)
    });
    b.send()
}
ajaxPostForm = function(b, c, d, e) {
    b = max.$(b);
    c = c || b.action || location.href;
    if (!b) return true;
    d = getFormData(b, d);
    ajaxPostData(c, d, e)
};
ajaxSubmit = function(b, c, d, e, f, g) {
    f = max.$(b);
    b = f.action || location.href;
    if (!d || d == "") d = "*";
    b = b.replace(/&?_max_ajaxids_=[^&$]+/ig, "");
    b = b.trimEnd("?");
    b += hasQuery(b) ? "&_max_ajaxids_=" + d: "?_max_ajaxids_=" + d;
    if (!f) return true;
    d = getFormData(f, c); (new ajaxWorker(b, "post", d)).addListener(200,
    function(h) {
        var i = maxPopupCollection.innerList;
        if (i) for (var j = 0; j < i.length; j++) i[j].hide();
        setButtonDisable(c, false);
        if (!g) if (e) e(h);
        else return;
        i = {};
        for (var k = 0;;) {
            j = h.indexOf("|", k);
            if (j <= k) break;
            var m = h.substring(k, j);
            k = j + 1;
            var n = h.indexOf("|", k);
            if (n <= k) break;
            k = parseInt(h.substring(k, n));
            j = h.substr(n + 1, k);
            k = n + k + 2;
            i[m] = j;
            if (k >= h.length) break
        }
        k = null;
        j = false;
        for (m in i) if (m == "_max_ajaxresult_") k = eval("(" + i[m] + ")");
        else {
            n = document.getElementById(m);
            n.onUpdate && n.onUpdate(n);
            j = i[m];
            n.innerHTML = j;
            execInnerJavascript(j);
            j = true
        }
        j || execInnerJavascript(h);
        e != null && e(k)
    }).send();
    return false
};
function imageScale2(b, c, d, e) {
    var f = b.offsetWidth,
    g = b.offsetHeight;
    b.ow = f;
    b.oh = g;
    if (f / g > c / d) if (f > c) {
        b.style.width = c + "px";
        b.style.height = c / f * g + "px";
        e || (b.style.marginTop = (d - c / f * g) / 2 + "px")
    } else e || (b.style.marginTop = (d - g) / 2 + "px");
    else if (g > d) {
        b.style.height = d + "px";
        b.style.width = d / g * f + "px"
    } else e || (b.style.marginTop = (d - g) / 2 + "px")
}
function AvatarLoaded2(b, c) {
    function d() {
        if (b.width == 0 && b.height == 0) setTimeout(d, 10);
        else {
            b.style.visibility = "inherit";
            var e = b.width,
            f = b.height;
            b.style.width = "auto";
            b.style.height = "auto";
            e && f && imageScale2(b, e, f, !!c);
            b.parentNode && (b.parentNode.style.backgroundImage = "url(" + BBSMAX.SpaceImage + ")")
        }
    }
    b.onload = null;
    b.onerror = null;
    d()
}
function AvatarError2(b, c) {
    b.onload = null;
    b.onerror = null;
    b.src = BBSMAX.SpaceImage;
    b.parentNode && (b.parentNode.style.background = "url(" + (c || BBSMAX.AvatarDefault) + ") no-repeat center center")
}
var maxPopupCollection = function() {
    var b = maxPopupCollection;
    if (!b.innerList) b.innerList = []
};
maxPopupCollection.prototype.add = function(b) {
    b.onShow = this.onshow;
    maxPopupCollection.innerList.push(b)
};
maxPopupCollection.prototype.onshow = function(b) {
    for (var c = maxPopupCollection,
    d = 0; d < c.innerList.length; d++) c.innerList[d] != b && c.innerList[d].hide()
};
maxPopupCollection.instance = function() {
    if (!window.maxPopupManager) window.maxPopupManager = new maxPopupCollection;
    return window.maxPopupManager
};
maxPopupCollection.prototype.hideAll = function() {
    for (var b = 0; b < maxPopupCollection.innerList.length; b++) maxPopupCollection.innerList[b].hide()
};
var popupBase = function(b, c, d) {
    this.triggers = [];
    this.list = typeof b == "object" ? b: max.$(b);
    this.auto = d;
    if (c instanceof Array) for (b = 0; b < c.length; b++) {
        d = max.$(c[b]);
        d != null && this.triggers.push(d)
    } else c instanceof Object ? this.triggers.push(c) : this.triggers.push(max.$(c))
};
popupBase.prototype.show = null;
popupBase.prototype.hide = null;
popupBase.prototype.onShow = null;
popupBase.prototype.onHide = null;
var popup = function(b, c, d, e, f) {
    if (max.browser.isIE6) this.createBack = true;
    this.focus = false;
    popupBase.call(this, b, c, d);
    this.triggerStyle = e;
    this.position = f ? f: "auto";
    var g = this;
    b = this.list.style;
    b.position = "absolute";
    b.zIndex = 50;
    for (b = 0; b < this.triggers.length; b++) {
        c = this.triggers[b];
        addHandler(c, this.auto ? "mouseover": "click",
        function(h) {
            g.show(h)
        });
        if (this.auto) addHandler(c, "mouseout",
        function() {
            g.hideList()
        });
        else {
            addHandler(c, "mouseout",
            function() {
                g.focus = false
            });
            addHandler(c, "mouseover",
            function() {
                g.focus = true
            })
        }
    }
    if (this.auto) {
        addHandler(this.list, "mouseover",
        function() {
            g.focus = true
        });
        addHandler(this.list, "mouseout",
        function() {
            g.hideList()
        })
    } else {
        addHandler(this.list, "mouseout",
        function() {
            g.focus = false
        });
        addHandler(this.list, "mouseover",
        function() {
            g.focus = true
        });
        addHandler(document.documentElement, "click",
        function(h) {
            g.focus == false && g.hide(h)
        })
    }
    maxPopupCollection.instance().add(this)
};
popup.prototype.hideList = function() {
    var b = this;
    setTimeout(function() {
        b.focus == false && b.hide()
    },
    1E3);
    b.focus = false
};
popup.prototype.show = function(b, c, d) {
    if (!c && this.auto) {
        this.focus = true;
        b = b || window.event;
        var e = this;
        d = b.target ? b.target: b.srcElement;
        window.setTimeout(function() {
            e.focus && e.show(b, 1, d)
        },
        200)
    } else {
        b = b || window.event;
        this.focus = true;
        d || (d = b ? b.target ? b.target: b.srcElement: this.triggers[0]);
        for (c = false; d && this.triggers.length;) {
            for (var f = 0; f < this.triggers.length; f++) {
                var g = this.triggers[f];
                if (d.id) if (g == d || g.id == d.id) {
                    d = g;
                    c = true;
                    break
                }
            }
            if (c) break;
            d = d.parentNode
        }
        if (! (this.triggers.length && !c)) {
            showPopup(this.list, d, this.position, this.offsetLeft, this.offsetTop);
            if (this.createBack) {
                if (!this.list.frame) {
                    this.list.frame = addElement("iframe");
                    setStyle(this.list.frame, {
                        position: "absolute",
                        zIndex: 48,
                        border: "none"
                    })
                }
                c = getRect(this.list);
                setStyle(this.list.frame, {
                    left: c.left + 2 + "px",
                    top: c.top + 2 + "px",
                    width: c.width - 4 + "px",
                    height: c.height - 4 + "px"
                });
                setVisible(this.list.frame, 1)
            }
            if (c = this.triggerStyle) {
                addCssClass(d, c);
                this.cssNode = d
            }
            this.onShow && this.onShow.call(maxPopupCollection.instance(), this)
        }
    }
};
popup.prototype.hide = function() {
    if (! (this.maskDialog && window.isClickDialog)) {
        this.list.style.display = "none";
        var b = this.triggerStyle;
        b && this.cssNode && removeCssClass(this.cssNode, b);
        max.browser.isIE6 && this.list.frame && setVisible(this.list.frame, 0);
        this.onHide && this.onHide.call(maxPopupCollection.instance(), this)
    }
};
var userMenus = {},
userMenu = function(b, c, d, e) {
    d = "user_menu_" + (new Date).getMilliseconds();
    var f = max.$("usermenuTemplate");
    new stringBuilder;
    e = addElement("div", e ? document.getElementsByTagName("body")[0] : c.parentNode);
    e.className = f.className;
    f = f.innerHTML;
    f = f.replace(/%7B/ig, "{");
    f = f.replace(/%7D/ig, "}");
    f = String.format(f, b);
    e.id = d;
    e.innerHTML = f;
    e.style.display = "none";
    return new popup(e.id, c, false)
};
function openUserMenu(b, c, d) {
    function e() {
        g.push(this); (new userMenu(c, this, d, f)).show()
    }
    if (!b.id) b.id = "UT_" + c + "_" + (new Date).getMilliseconds();
    var f = true,
    g = userMenus[c.toString() + b.id];
    if (g) {
        for (var h = false,
        i = 0; i < g.length; i++) if (g[i] == b) {
            h = true;
            break
        }
        h == false && e.call(b)
    } else {
        g = [];
        userMenus[c.toString() + b.id] = g;
        e.call(b)
    }
    return false
}
var elementInDialog = function(b) {
    b = b;
    for (var c = 0; b && c < 20;) {
        if (b.className == "dialog" && b.id && b.id.indexOf("mx_dialog_") > -1) return b;
        c++;
        b = b.parentNode;
        if (b.nodeName == "body" || b.nodeName == "BODY") break
    }
    return false
};