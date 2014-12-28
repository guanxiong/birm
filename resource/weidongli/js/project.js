(function(e, t) {
	function n() {
		if (!m.isReady) {
			try {
				b.documentElement.doScroll("left")
			} catch(e) {
				setTimeout(n, 1);
				return
			}
			m.ready()
		}
	}
	function r(e, t) {
		t.src ? m.ajax({
			url: t.src,
			async: !1,
			dataType: "script"
		}) : m.globalEval(t.text || t.textContent || t.innerHTML || ""),
		t.parentNode && t.parentNode.removeChild(t)
	}
	function i(e, n, r, s, o, u) {
		var a = e.length;
		if (typeof n == "object") {
			for (var f in n) i(e, f, n[f], s, o, r);
			return e
		}
		if (r !== t) {
			s = !u && s && m.isFunction(r);
			for (f = 0; f < a; f++) o(e[f], n, s ? r.call(e[f], f, o(e[f], n)) : r, u);
			return e
		}
		return a ? o(e[0], n) : t
	}
	function s() {
		return (new Date).getTime()
	}
	function o() {
		return ! 1
	}
	function u() {
		return ! 0
	}
	function a(e, t, n) {
		return n[0].type = e,
		m.event.handle.apply(t, n)
	}
	function f(e) {
		var t, n = [],
		r = [],
		i = arguments,
		s,
		o,
		u,
		a,
		f,
		l;
		o = m.data(this, "events");
		if (! (e.liveFired === this || !o || !o.live || e.button && e.type === "click")) {
			e.liveFired = this;
			var c = o.live.slice(0);
			for (a = 0; a < c.length; a++) o = c[a],
			o.origType.replace(V, "") === e.type ? r.push(o.selector) : c.splice(a--, 1);
			s = m(e.target).closest(r, e.currentTarget),
			f = 0;
			for (l = s.length; f < l; f++) for (a = 0; a < c.length; a++) {
				o = c[a];
				if (s[f].selector === o.selector) {
					u = s[f].elem,
					r = null;
					if (o.preType === "mouseenter" || o.preType === "mouseleave") r = m(e.relatedTarget).closest(o.selector)[0]; (!r || r !== u) && n.push({
						elem: u,
						handleObj: o
					})
				}
			}
			f = 0;
			for (l = n.length; f < l; f++) {
				s = n[f],
				e.currentTarget = s.elem,
				e.data = s.handleObj.data,
				e.handleObj = s.handleObj;
				if (s.handleObj.origHandler.apply(s.elem, i) === !1) {
					t = !1;
					break
				}
			}
			return t
		}
	}
	function l(e, t) {
		return "live." + (e && e !== "*" ? e + ".": "") + t.replace(/\./g, "`").replace(/ /g, "&")
	}
	function c(e) {
		return ! e || !e.parentNode || e.parentNode.nodeType === 11
	}
	function h(e, t) {
		var n = 0;
		t.each(function() {
			if (this.nodeName === (e[n] && e[n].nodeName)) {
				var t = m.data(e[n++]),
				r = m.data(this, t);
				if (t = t && t.events) {
					delete r.handle,
					r.events = {};
					for (var i in t) for (var s in t[i]) m.event.add(this, i, t[i][s], t[i][s].data)
				}
			}
		})
	}
	function p(e, t, n) {
		var r, i, s;
		return t = t && t[0] ? t[0].ownerDocument || t[0] : b,
		e.length === 1 && typeof e[0] == "string" && e[0].length < 512 && t === b && !pt.test(e[0]) && (m.support.checkClone || !dt.test(e[0])) && (i = !0, (s = m.fragments[e[0]]) && s !== 1 && (r = s)),
		r || (r = t.createDocumentFragment(), m.clean(e, t, r, n)),
		i && (m.fragments[e[0]] = s ? r: 1),
		{
			fragment: r,
			cacheable: i
		}
	}
	function d(e, t) {
		var n = {};
		return m.each(Xt.concat.apply([], Xt.slice(0, t)),
		function() {
			n[this] = e
		}),
		n
	}
	function v(e) {
		return "scrollTo" in e && e.document ? e: e.nodeType === 9 ? e.defaultView || e.parentWindow: !1
	}
	var m = function(e, t) {
		return new m.fn.init(e, t)
	},
	g = e.jQuery,
	y = e.$,
	b = e.document,
	w,
	E = /^[^<]*(<[\w\W]+>)[^>]*$|^#([\w-]+)$/,
	S = /^.[^:#\[\.,]*$/,
	x = /\S/,
	T = /^(\s|\u00A0)+|(\s|\u00A0)+$/g,
	N = /^<(\w+)\s*\/?>(?:<\/\1>)?$/,
	C = navigator.userAgent,
	k = !1,
	L = [],
	A,
	O = Object.prototype.toString,
	M = Object.prototype.hasOwnProperty,
	_ = Array.prototype.push,
	D = Array.prototype.slice,
	P = Array.prototype.indexOf;
	m.fn = m.prototype = {
		init: function(e, n) {
			var r, i;
			if (!e) return this;
			if (e.nodeType) return this.context = this[0] = e,
			this.length = 1,
			this;
			if (e === "body" && !n) return this.context = b,
			this[0] = b.body,
			this.selector = "body",
			this.length = 1,
			this;
			if (typeof e == "string") {
				if ((r = E.exec(e)) && (r[1] || !n)) {
					if (r[1]) return i = n ? n.ownerDocument || n: b,
					(e = N.exec(e)) ? m.isPlainObject(n) ? (e = [b.createElement(e[1])], m.fn.attr.call(e, n, !0)) : e = [i.createElement(e[1])] : (e = p([r[1]], [i]), e = (e.cacheable ? e.fragment.cloneNode(!0) : e.fragment).childNodes),
					m.merge(this, e);
					if (n = b.getElementById(r[2])) {
						if (n.id !== r[2]) return w.find(e);
						this.length = 1,
						this[0] = n
					}
					return this.context = b,
					this.selector = e,
					this
				}
				return ! n && /^\w+$/.test(e) ? (this.selector = e, this.context = b, e = b.getElementsByTagName(e), m.merge(this, e)) : !n || n.jquery ? (n || w).find(e) : m(n).find(e)
			}
			return m.isFunction(e) ? w.ready(e) : (e.selector !== t && (this.selector = e.selector, this.context = e.context), m.makeArray(e, this))
		},
		selector: "",
		jquery: "1.4.2",
		length: 0,
		size: function() {
			return this.length
		},
		toArray: function() {
			return D.call(this, 0)
		},
		get: function(e) {
			return e == null ? this.toArray() : e < 0 ? this.slice(e)[0] : this[e]
		},
		pushStack: function(e, t, n) {
			var r = m();
			return m.isArray(e) ? _.apply(r, e) : m.merge(r, e),
			r.prevObject = this,
			r.context = this.context,
			t === "find" ? r.selector = this.selector + (this.selector ? " ": "") + n: t && (r.selector = this.selector + "." + t + "(" + n + ")"),
			r
		},
		each: function(e, t) {
			return m.each(this, e, t)
		},
		ready: function(e) {
			return m.bindReady(),
			m.isReady ? e.call(b, m) : L && L.push(e),
			this
		},
		eq: function(e) {
			return e === -1 ? this.slice(e) : this.slice(e, +e + 1)
		},
		first: function() {
			return this.eq(0)
		},
		last: function() {
			return this.eq( - 1)
		},
		slice: function() {
			return this.pushStack(D.apply(this, arguments), "slice", D.call(arguments).join(","))
		},
		map: function(e) {
			return this.pushStack(m.map(this,
			function(t, n) {
				return e.call(t, n, t)
			}))
		},
		end: function() {
			return this.prevObject || m(null)
		},
		push: _,
		sort: [].sort,
		splice: [].splice
	},
	m.fn.init.prototype = m.fn,
	m.extend = m.fn.extend = function() {
		var e = arguments[0] || {},
		n = 1,
		r = arguments.length,
		i = !1,
		s,
		o,
		u,
		a;
		typeof e == "boolean" && (i = e, e = arguments[1] || {},
		n = 2),
		typeof e != "object" && !m.isFunction(e) && (e = {}),
		r === n && (e = this, --n);
		for (; n < r; n++) if ((s = arguments[n]) != null) for (o in s) u = e[o],
		a = s[o],
		e !== a && (i && a && (m.isPlainObject(a) || m.isArray(a)) ? (u = u && (m.isPlainObject(u) || m.isArray(u)) ? u: m.isArray(a) ? [] : {},
		e[o] = m.extend(i, u, a)) : a !== t && (e[o] = a));
		return e
	},
	m.extend({
		noConflict: function(t) {
			return e.$ = y,
			t && (e.jQuery = g),
			m
		},
		isReady: !1,
		ready: function() {
			if (!m.isReady) {
				if (!b.body) return setTimeout(m.ready, 13);
				m.isReady = !0;
				if (L) {
					for (var e, t = 0; e = L[t++];) e.call(b, m);
					L = null
				}
				m.fn.triggerHandler && m(b).triggerHandler("ready")
			}
		},
		bindReady: function() {
			if (!k) {
				k = !0;
				if (b.readyState === "complete") return m.ready();
				if (b.addEventListener) b.addEventListener("DOMContentLoaded", A, !1),
				e.addEventListener("load", m.ready, !1);
				else if (b.attachEvent) {
					b.attachEvent("onreadystatechange", A),
					e.attachEvent("onload", m.ready);
					var t = !1;
					try {
						t = e.frameElement == null
					} catch(r) {}
					b.documentElement.doScroll && t && n()
				}
			}
		},
		isFunction: function(e) {
			return O.call(e) === "[object Function]"
		},
		isArray: function(e) {
			return O.call(e) === "[object Array]"
		},
		isPlainObject: function(e) {
			if (!e || O.call(e) !== "[object Object]" || e.nodeType || e.setInterval) return ! 1;
			if (e.constructor && !M.call(e, "constructor") && !M.call(e.constructor.prototype, "isPrototypeOf")) return ! 1;
			var n;
			for (n in e);
			return n === t || M.call(e, n)
		},
		isEmptyObject: function(e) {
			for (var t in e) return ! 1;
			return ! 0
		},
		error: function(e) {
			throw e
		},
		parseJSON: function(t) {
			if (typeof t != "string" || !t) return null;
			t = m.trim(t);
			if (/^[\],:{}\s]*$/.test(t.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, "@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, "]").replace(/(?:^|:|,)(?:\s*\[)+/g, ""))) return e.JSON && e.JSON.parse ? e.JSON.parse(t) : (new Function("return " + t))();
			m.error("Invalid JSON: " + t)
		},
		noop: function() {},
		globalEval: function(e) {
			if (e && x.test(e)) {
				var t = b.getElementsByTagName("head")[0] || b.documentElement,
				n = b.createElement("script");
				n.type = "text/javascript",
				m.support.scriptEval ? n.appendChild(b.createTextNode(e)) : n.text = e,
				t.insertBefore(n, t.firstChild),
				t.removeChild(n)
			}
		},
		nodeName: function(e, t) {
			return e.nodeName && e.nodeName.toUpperCase() === t.toUpperCase()
		},
		each: function(e, n, r) {
			var i, s = 0,
			o = e.length,
			u = o === t || m.isFunction(e);
			if (r) {
				if (u) {
					for (i in e) if (n.apply(e[i], r) === !1) break
				} else for (; s < o;) if (n.apply(e[s++], r) === !1) break
			} else if (u) {
				for (i in e) if (n.call(e[i], i, e[i]) === !1) break
			} else for (r = e[0]; s < o && n.call(r, s, r) !== !1; r = e[++s]);
			return e
		},
		trim: function(e) {
			return (e || "").replace(T, "")
		},
		makeArray: function(e, t) {
			return t = t || [],
			e != null && (e.length == null || typeof e == "string" || m.isFunction(e) || typeof e != "function" && e.setInterval ? _.call(t, e) : m.merge(t, e)),
			t
		},
		inArray: function(e, t) {
			if (t.indexOf) return t.indexOf(e);
			for (var n = 0,
			r = t.length; n < r; n++) if (t[n] === e) return n;
			return - 1
		},
		merge: function(e, n) {
			var r = e.length,
			i = 0;
			if (typeof n.length == "number") for (var s = n.length; i < s; i++) e[r++] = n[i];
			else for (; n[i] !== t;) e[r++] = n[i++];
			return e.length = r,
			e
		},
		grep: function(e, t, n) {
			for (var r = [], i = 0, s = e.length; i < s; i++) ! n != !t(e[i], i) && r.push(e[i]);
			return r
		},
		map: function(e, t, n) {
			for (var r = [], i, s = 0, o = e.length; s < o; s++) i = t(e[s], s, n),
			i != null && (r[r.length] = i);
			return r.concat.apply([], r)
		},
		guid: 1,
		proxy: function(e, n, r) {
			return arguments.length === 2 && (typeof n == "string" ? (r = e, e = r[n], n = t) : n && !m.isFunction(n) && (r = n, n = t)),
			!n && e && (n = function() {
				return e.apply(r || this, arguments)
			}),
			e && (n.guid = e.guid = e.guid || n.guid || m.guid++),
			n
		},
		uaMatch: function(e) {
			return e = e.toLowerCase(),
			e = /(webkit)[ \/]([\w.]+)/.exec(e) || /(opera)(?:.*version)?[ \/]([\w.]+)/.exec(e) || /(msie) ([\w.]+)/.exec(e) || !/compatible/.test(e) && /(mozilla)(?:.*? rv:([\w.]+))?/.exec(e) || [],
			{
				browser: e[1] || "",
				version: e[2] || "0"
			}
		},
		browser: {}
	}),
	C = m.uaMatch(C),
	C.browser && (m.browser[C.browser] = !0, m.browser.version = C.version),
	m.browser.webkit && (m.browser.safari = !0),
	P && (m.inArray = function(e, t) {
		return P.call(t, e)
	}),
	w = m(b),
	b.addEventListener ? A = function() {
		b.removeEventListener("DOMContentLoaded", A, !1),
		m.ready()
	}: b.attachEvent && (A = function() {
		b.readyState === "complete" && (b.detachEvent("onreadystatechange", A), m.ready())
	}),
	function() {
		m.support = {};
		var t = b.documentElement,
		n = b.createElement("script"),
		r = b.createElement("div"),
		i = "script" + s();
		r.style.display = "none",
		r.innerHTML = "   <link/><table></table><a href='/a' style='color:red;float:left;opacity:.55;'>a</a><input type='checkbox'/>";
		var o = r.getElementsByTagName("*"),
		u = r.getElementsByTagName("a")[0];
		if (! (!o || !o.length || !u)) {
			m.support = {
				leadingWhitespace: r.firstChild.nodeType === 3,
				tbody: !r.getElementsByTagName("tbody").length,
				htmlSerialize: !!r.getElementsByTagName("link").length,
				style: /red/.test(u.getAttribute("style")),
				hrefNormalized: u.getAttribute("href") === "/a",
				opacity: /^0.55$/.test(u.style.opacity),
				cssFloat: !!u.style.cssFloat,
				checkOn: r.getElementsByTagName("input")[0].value === "on",
				optSelected: b.createElement("select").appendChild(b.createElement("option")).selected,
				parentNode: r.removeChild(r.appendChild(b.createElement("div"))).parentNode === null,
				deleteExpando: !0,
				checkClone: !1,
				scriptEval: !1,
				noCloneEvent: !0,
				boxModel: null
			},
			n.type = "text/javascript";
			try {
				n.appendChild(b.createTextNode("window." + i + "=1;"))
			} catch(a) {}
			t.insertBefore(n, t.firstChild),
			e[i] && (m.support.scriptEval = !0, delete e[i]);
			try {
				delete n.test
			} catch(f) {
				m.support.deleteExpando = !1
			}
			t.removeChild(n),
			r.attachEvent && r.fireEvent && (r.attachEvent("onclick",
			function l() {
				m.support.noCloneEvent = !1,
				r.detachEvent("onclick", l)
			}), r.cloneNode(!0).fireEvent("onclick")),
			r = b.createElement("div"),
			r.innerHTML = "<input type='radio' name='radiotest' checked='checked'/>",
			t = b.createDocumentFragment(),
			t.appendChild(r.firstChild),
			m.support.checkClone = t.cloneNode(!0).cloneNode(!0).lastChild.checked,
			m(function() {
				var e = b.createElement("div");
				e.style.width = e.style.paddingLeft = "1px",
				b.body.appendChild(e),
				m.boxModel = m.support.boxModel = e.offsetWidth === 2,
				b.body.removeChild(e).style.display = "none"
			}),
			t = function(e) {
				var t = b.createElement("div");
				e = "on" + e;
				var n = e in t;
				return n || (t.setAttribute(e, "return;"), n = typeof t[e] == "function"),
				n
			},
			m.support.submitBubbles = t("submit"),
			m.support.changeBubbles = t("change"),
			t = n = r = o = u = null
		}
	} (),
	m.props = {
		"for": "htmlFor",
		"class": "className",
		readonly: "readOnly",
		maxlength: "maxLength",
		cellspacing: "cellSpacing",
		rowspan: "rowSpan",
		colspan: "colSpan",
		tabindex: "tabIndex",
		usemap: "useMap",
		frameborder: "frameBorder"
	};
	var H = "jQuery" + s(),
	B = 0,
	j = {};
	m.extend({
		cache: {},
		expando: H,
		noData: {
			embed: !0,
			object: !0,
			applet: !0
		},
		data: function(n, r, i) {
			if (!n.nodeName || !m.noData[n.nodeName.toLowerCase()]) {
				n = n == e ? j: n;
				var s = n[H],
				o = m.cache;
				return ! s && typeof r == "string" && i === t ? null: (s || (s = ++B), typeof r == "object" ? (n[H] = s, o[s] = m.extend(!0, {},
				r)) : o[s] || (n[H] = s, o[s] = {}), n = o[s], i !== t && (n[r] = i), typeof r == "string" ? n[r] : n)
			}
		},
		removeData: function(t, n) {
			if (!t.nodeName || !m.noData[t.nodeName.toLowerCase()]) {
				t = t == e ? j: t;
				var r = t[H],
				i = m.cache,
				s = i[r];
				n ? s && (delete s[n], m.isEmptyObject(s) && m.removeData(t)) : (m.support.deleteExpando ? delete t[m.expando] : t.removeAttribute && t.removeAttribute(m.expando), delete i[r])
			}
		}
	}),
	m.fn.extend({
		data: function(e, n) {
			if (typeof e == "undefined" && this.length) return m.data(this[0]);
			if (typeof e == "object") return this.each(function() {
				m.data(this, e)
			});
			var r = e.split(".");
			r[1] = r[1] ? "." + r[1] : "";
			if (n === t) {
				var i = this.triggerHandler("getData" + r[1] + "!", [r[0]]);
				return i === t && this.length && (i = m.data(this[0], e)),
				i === t && r[1] ? this.data(r[0]) : i
			}
			return this.trigger("setData" + r[1] + "!", [r[0], n]).each(function() {
				m.data(this, e, n)
			})
		},
		removeData: function(e) {
			return this.each(function() {
				m.removeData(this, e)
			})
		}
	}),
	m.extend({
		queue: function(e, t, n) {
			if (e) {
				t = (t || "fx") + "queue";
				var r = m.data(e, t);
				return n ? (!r || m.isArray(n) ? r = m.data(e, t, m.makeArray(n)) : r.push(n), r) : r || []
			}
		},
		dequeue: function(e, t) {
			t = t || "fx";
			var n = m.queue(e, t),
			r = n.shift();
			r === "inprogress" && (r = n.shift()),
			r && (t === "fx" && n.unshift("inprogress"), r.call(e,
			function() {
				m.dequeue(e, t)
			}))
		}
	}),
	m.fn.extend({
		queue: function(e, n) {
			return typeof e != "string" && (n = e, e = "fx"),
			n === t ? m.queue(this[0], e) : this.each(function() {
				var t = m.queue(this, e, n);
				e === "fx" && t[0] !== "inprogress" && m.dequeue(this, e)
			})
		},
		dequeue: function(e) {
			return this.each(function() {
				m.dequeue(this, e)
			})
		},
		delay: function(e, t) {
			return e = m.fx ? m.fx.speeds[e] || e: e,
			t = t || "fx",
			this.queue(t,
			function() {
				var n = this;
				setTimeout(function() {
					m.dequeue(n, t)
				},
				e)
			})
		},
		clearQueue: function(e) {
			return this.queue(e || "fx", [])
		}
	});
	var F = /[\n\t]/g,
	I = /\s+/,
	q = /\r/g,
	R = /href|src|style/,
	U = /(button|input)/i,
	z = /(button|input|object|select|textarea)/i,
	W = /^(a|area)$/i,
	X = /radio|checkbox/;
	m.fn.extend({
		attr: function(e, t) {
			return i(this, e, t, !0, m.attr)
		},
		removeAttr: function(e) {
			return this.each(function() {
				m.attr(this, e, ""),
				this.nodeType === 1 && this.removeAttribute(e)
			})
		},
		addClass: function(e) {
			if (m.isFunction(e)) return this.each(function(t) {
				var n = m(this);
				n.addClass(e.call(this, t, n.attr("class")))
			});
			if (e && typeof e == "string") for (var t = (e || "").split(I), n = 0, r = this.length; n < r; n++) {
				var i = this[n];
				if (i.nodeType === 1) if (i.className) {
					for (var s = " " + i.className + " ",
					o = i.className,
					u = 0,
					a = t.length; u < a; u++) s.indexOf(" " + t[u] + " ") < 0 && (o += " " + t[u]);
					i.className = m.trim(o)
				} else i.className = e
			}
			return this
		},
		removeClass: function(e) {
			if (m.isFunction(e)) return this.each(function(t) {
				var n = m(this);
				n.removeClass(e.call(this, t, n.attr("class")))
			});
			if (e && typeof e == "string" || e === t) for (var n = (e || "").split(I), r = 0, i = this.length; r < i; r++) {
				var s = this[r];
				if (s.nodeType === 1 && s.className) if (e) {
					for (var o = (" " + s.className + " ").replace(F, " "), u = 0, a = n.length; u < a; u++) o = o.replace(" " + n[u] + " ", " ");
					s.className = m.trim(o)
				} else s.className = ""
			}
			return this
		},
		toggleClass: function(e, t) {
			var n = typeof e,
			r = typeof t == "boolean";
			return m.isFunction(e) ? this.each(function(n) {
				var r = m(this);
				r.toggleClass(e.call(this, n, r.attr("class"), t), t)
			}) : this.each(function() {
				if (n === "string") for (var i, s = 0,
				o = m(this), u = t, a = e.split(I); i = a[s++];) u = r ? u: !o.hasClass(i),
				o[u ? "addClass": "removeClass"](i);
				else if (n === "undefined" || n === "boolean") this.className && m.data(this, "__className__", this.className),
				this.className = this.className || e === !1 ? "": m.data(this, "__className__") || ""
			})
		},
		hasClass: function(e) {
			e = " " + e + " ";
			for (var t = 0,
			n = this.length; t < n; t++) if ((" " + this[t].className + " ").replace(F, " ").indexOf(e) > -1) return ! 0;
			return ! 1
		},
		val: function(e) {
			if (e === t) {
				var n = this[0];
				if (n) {
					if (m.nodeName(n, "option")) return (n.attributes.value || {}).specified ? n.value: n.text;
					if (m.nodeName(n, "select")) {
						var r = n.selectedIndex,
						i = [],
						s = n.options;
						n = n.type === "select-one";
						if (r < 0) return null;
						var o = n ? r: 0;
						for (r = n ? r + 1 : s.length; o < r; o++) {
							var u = s[o];
							if (u.selected) {
								e = m(u).val();
								if (n) return e;
								i.push(e)
							}
						}
						return i
					}
					return X.test(n.type) && !m.support.checkOn ? n.getAttribute("value") === null ? "on": n.value: (n.value || "").replace(q, "")
				}
				return t
			}
			var a = m.isFunction(e);
			return this.each(function(t) {
				var n = m(this),
				r = e;
				if (this.nodeType === 1) {
					a && (r = e.call(this, t, n.val())),
					typeof r == "number" && (r += "");
					if (m.isArray(r) && X.test(this.type)) this.checked = m.inArray(n.val(), r) >= 0;
					else if (m.nodeName(this, "select")) {
						var i = m.makeArray(r);
						m("option", this).each(function() {
							this.selected = m.inArray(m(this).val(), i) >= 0
						}),
						i.length || (this.selectedIndex = -1)
					} else this.value = r
				}
			})
		}
	}),
	m.extend({
		attrFn: {
			val: !0,
			css: !0,
			html: !0,
			text: !0,
			data: !0,
			width: !0,
			height: !0,
			offset: !0
		},
		attr: function(e, n, r, i) {
			if (!e || e.nodeType === 3 || e.nodeType === 8) return t;
			if (i && n in m.attrFn) return m(e)[n](r);
			i = e.nodeType !== 1 || !m.isXMLDoc(e);
			var s = r !== t;
			n = i && m.props[n] || n;
			if (e.nodeType === 1) {
				var o = R.test(n);
				return n in e && i && !o ? (s && (n === "type" && U.test(e.nodeName) && e.parentNode && m.error("type property can't be changed"), e[n] = r), m.nodeName(e, "form") && e.getAttributeNode(n) ? e.getAttributeNode(n).nodeValue: n === "tabIndex" ? (n = e.getAttributeNode("tabIndex")) && n.specified ? n.value: z.test(e.nodeName) || W.test(e.nodeName) && e.href ? 0 : t: e[n]) : !m.support.style && i && n === "style" ? (s && (e.style.cssText = "" + r), e.style.cssText) : (s && e.setAttribute(n, "" + r), e = !m.support.hrefNormalized && i && o ? e.getAttribute(n, 2) : e.getAttribute(n), e === null ? t: e)
			}
			return m.style(e, n, r)
		}
	});
	var V = /\.(.*)$/,
	$ = function(e) {
		return e.replace(/[^\w\s\.\|`]/g,
		function(e) {
			return "\\" + e
		})
	};
	m.event = {
		add: function(n, r, i, s) {
			if (n.nodeType !== 3 && n.nodeType !== 8) {
				n.setInterval && n !== e && !n.frameElement && (n = e);
				var o, u;
				i.handler && (o = i, i = o.handler),
				i.guid || (i.guid = m.guid++);
				if (u = m.data(n)) {
					var a = u.events = u.events || {},
					f = u.handle;
					f || (u.handle = f = function() {
						return typeof m != "undefined" && !m.event.triggered ? m.event.handle.apply(f.elem, arguments) : t
					}),
					f.elem = n,
					r = r.split(" ");
					for (var l, c = 0,
					h; l = r[c++];) {
						u = o ? m.extend({},
						o) : {
							handler: i,
							data: s
						},
						l.indexOf(".") > -1 ? (h = l.split("."), l = h.shift(), u.namespace = h.slice(0).sort().join(".")) : (h = [], u.namespace = ""),
						u.type = l,
						u.guid = i.guid;
						var p = a[l],
						d = m.event.special[l] || {};
						if (!p) {
							p = a[l] = [];
							if (!d.setup || d.setup.call(n, s, h, f) === !1) n.addEventListener ? n.addEventListener(l, f, !1) : n.attachEvent && n.attachEvent("on" + l, f)
						}
						d.add && (d.add.call(n, u), u.handler.guid || (u.handler.guid = i.guid)),
						p.push(u),
						m.event.global[l] = !0
					}
					n = null
				}
			}
		},
		global: {},
		remove: function(e, t, n, r) {
			if (e.nodeType !== 3 && e.nodeType !== 8) {
				var i, s = 0,
				o, u, a, f, l, c, h = m.data(e),
				p = h && h.events;
				if (h && p) {
					t && t.type && (n = t.handler, t = t.type);
					if (!t || typeof t == "string" && t.charAt(0) === ".") {
						t = t || "";
						for (i in p) m.event.remove(e, i + t)
					} else {
						for (t = t.split(" "); i = t[s++];) {
							f = i,
							o = i.indexOf(".") < 0,
							u = [],
							o || (u = i.split("."), i = u.shift(), a = new RegExp("(^|\\.)" + m.map(u.slice(0).sort(), $).join("\\.(?:.*\\.)?") + "(\\.|$)"));
							if (l = p[i]) if (n) {
								f = m.event.special[i] || {};
								for (d = r || 0; d < l.length; d++) {
									c = l[d];
									if (n.guid === c.guid) {
										if (o || a.test(c.namespace)) r == null && l.splice(d--, 1),
										f.remove && f.remove.call(e, c);
										if (r != null) break
									}
								}
								if (l.length === 0 || r != null && l.length === 1)(!f.teardown || f.teardown.call(e, u) === !1) && J(e, i, h.handle),
								delete p[i]
							} else for (var d = 0; d < l.length; d++) {
								c = l[d];
								if (o || a.test(c.namespace)) m.event.remove(e, f, c.handler, d),
								l.splice(d--, 1)
							}
						}
						if (m.isEmptyObject(p)) {
							if (t = h.handle) t.elem = null;
							delete h.events,
							delete h.handle,
							m.isEmptyObject(h) && m.removeData(e)
						}
					}
				}
			}
		},
		trigger: function(e, n, r, i) {
			var s = e.type || e;
			if (!i) {
				e = typeof e == "object" ? e[H] ? e: m.extend(m.Event(s), e) : m.Event(s),
				s.indexOf("!") >= 0 && (e.type = s = s.slice(0, -1), e.exclusive = !0),
				r || (e.stopPropagation(), m.event.global[s] && m.each(m.cache,
				function() {
					this.events && this.events[s] && m.event.trigger(e, n, this.handle.elem)
				}));
				if (!r || r.nodeType === 3 || r.nodeType === 8) return t;
				e.result = t,
				e.target = r,
				n = m.makeArray(n),
				n.unshift(e)
			}
			e.currentTarget = r,
			(i = m.data(r, "handle")) && i.apply(r, n),
			i = r.parentNode || r.ownerDocument;
			try {
				r && r.nodeName && m.noData[r.nodeName.toLowerCase()] || r["on" + s] && r["on" + s].apply(r, n) === !1 && (e.result = !1)
			} catch(o) {}
			if (!e.isPropagationStopped() && i) m.event.trigger(e, n, i, !0);
			else if (!e.isDefaultPrevented()) {
				i = e.target;
				var u, a = m.nodeName(i, "a") && s === "click",
				f = m.event.special[s] || {};
				if ((!f._default || f._default.call(r, e) === !1) && !a && !(i && i.nodeName && m.noData[i.nodeName.toLowerCase()])) {
					try {
						if (i[s]) {
							if (u = i["on" + s]) i["on" + s] = null;
							m.event.triggered = !0,
							i[s]()
						}
					} catch(l) {}
					u && (i["on" + s] = u),
					m.event.triggered = !1
				}
			}
		},
		handle: function(n) {
			var r, i, s, o;
			n = arguments[0] = m.event.fix(n || e.event),
			n.currentTarget = this,
			r = n.type.indexOf(".") < 0 && !n.exclusive,
			r || (i = n.type.split("."), n.type = i.shift(), s = new RegExp("(^|\\.)" + i.slice(0).sort().join("\\.(?:.*\\.)?") + "(\\.|$)")),
			o = m.data(this, "events"),
			i = o[n.type];
			if (o && i) {
				i = i.slice(0),
				o = 0;
				for (var u = i.length; o < u; o++) {
					var a = i[o];
					if (r || s.test(a.namespace)) {
						n.handler = a.handler,
						n.data = a.data,
						n.handleObj = a,
						a = a.handler.apply(this, arguments),
						a !== t && (n.result = a, a === !1 && (n.preventDefault(), n.stopPropagation()));
						if (n.isImmediatePropagationStopped()) break
					}
				}
			}
			return n.result
		},
		props: "altKey attrChange attrName bubbles button cancelable charCode clientX clientY ctrlKey currentTarget data detail eventPhase fromElement handler keyCode layerX layerY metaKey newValue offsetX offsetY originalTarget pageX pageY prevValue relatedNode relatedTarget screenX screenY shiftKey srcElement target toElement view wheelDelta which".split(" "),
		fix: function(e) {
			if (e[H]) return e;
			var n = e;
			e = m.Event(n);
			for (var r = this.props.length,
			i; r;) i = this.props[--r],
			e[i] = n[i];
			return e.target || (e.target = e.srcElement || b),
			e.target.nodeType === 3 && (e.target = e.target.parentNode),
			!e.relatedTarget && e.fromElement && (e.relatedTarget = e.fromElement === e.target ? e.toElement: e.fromElement),
			e.pageX == null && e.clientX != null && (n = b.documentElement, r = b.body, e.pageX = e.clientX + (n && n.scrollLeft || r && r.scrollLeft || 0) - (n && n.clientLeft || r && r.clientLeft || 0), e.pageY = e.clientY + (n && n.scrollTop || r && r.scrollTop || 0) - (n && n.clientTop || r && r.clientTop || 0)),
			!e.which && (e.charCode || e.charCode === 0 ? e.charCode: e.keyCode) && (e.which = e.charCode || e.keyCode),
			!e.metaKey && e.ctrlKey && (e.metaKey = e.ctrlKey),
			!e.which && e.button !== t && (e.which = e.button & 1 ? 1 : e.button & 2 ? 3 : e.button & 4 ? 2 : 0),
			e
		},
		guid: 1e8,
		proxy: m.proxy,
		special: {
			ready: {
				setup: m.bindReady,
				teardown: m.noop
			},
			live: {
				add: function(e) {
					m.event.add(this, e.origType, m.extend({},
					e, {
						handler: f
					}))
				},
				remove: function(e) {
					var t = !0,
					n = e.origType.replace(V, "");
					m.each(m.data(this, "events").live || [],
					function() {
						if (n === this.origType.replace(V, "")) return t = !1
					}),
					t && m.event.remove(this, e.origType, f)
				}
			},
			beforeunload: {
				setup: function(e, t, n) {
					return this.setInterval && (this.onbeforeunload = n),
					!1
				},
				teardown: function(e, t) {
					this.onbeforeunload === t && (this.onbeforeunload = null)
				}
			}
		}
	};
	var J = b.removeEventListener ?
	function(e, t, n) {
		e.removeEventListener(t, n, !1)
	}: function(e, t, n) {
		e.detachEvent("on" + t, n)
	};
	m.Event = function(e) {
		if (!this.preventDefault) return new m.Event(e);
		e && e.type ? (this.originalEvent = e, this.type = e.type) : this.type = e,
		this.timeStamp = s(),
		this[H] = !0
	},
	m.Event.prototype = {
		preventDefault: function() {
			this.isDefaultPrevented = u;
			var e = this.originalEvent;
			e && (e.preventDefault && e.preventDefault(), e.returnValue = !1)
		},
		stopPropagation: function() {
			this.isPropagationStopped = u;
			var e = this.originalEvent;
			e && (e.stopPropagation && e.stopPropagation(), e.cancelBubble = !0)
		},
		stopImmediatePropagation: function() {
			this.isImmediatePropagationStopped = u,
			this.stopPropagation()
		},
		isDefaultPrevented: o,
		isPropagationStopped: o,
		isImmediatePropagationStopped: o
	};
	var K = function(e) {
		var t = e.relatedTarget;
		try {
			for (; t && t !== this;) t = t.parentNode;
			t !== this && (e.type = e.data, m.event.handle.apply(this, arguments))
		} catch(n) {}
	},
	Q = function(e) {
		e.type = e.data,
		m.event.handle.apply(this, arguments)
	};
	m.each({
		mouseenter: "mouseover",
		mouseleave: "mouseout"
	},
	function(e, t) {
		m.event.special[e] = {
			setup: function(n) {
				m.event.add(this, t, n && n.selector ? Q: K, e)
			},
			teardown: function(e) {
				m.event.remove(this, t, e && e.selector ? Q: K)
			}
		}
	}),
	m.support.submitBubbles || (m.event.special.submit = {
		setup: function() {
			if (this.nodeName.toLowerCase() === "form") return ! 1;
			m.event.add(this, "click.specialSubmit",
			function(e) {
				var t = e.target,
				n = t.type;
				if ((n === "submit" || n === "image") && m(t).closest("form").length) return a("submit", this, arguments)
			}),
			m.event.add(this, "keypress.specialSubmit",
			function(e) {
				var t = e.target,
				n = t.type;
				if ((n === "text" || n === "password") && m(t).closest("form").length && e.keyCode === 13) return a("submit", this, arguments)
			})
		},
		teardown: function() {
			m.event.remove(this, ".specialSubmit")
		}
	});
	if (!m.support.changeBubbles) {
		var G = /textarea|input|select/i,
		Y, Z = function(e) {
			var t = e.type,
			n = e.value;
			return t === "radio" || t === "checkbox" ? n = e.checked: t === "select-multiple" ? n = e.selectedIndex > -1 ? m.map(e.options,
			function(e) {
				return e.selected
			}).join("-") : "": e.nodeName.toLowerCase() === "select" && (n = e.selectedIndex),
			n
		},
		et = function(e, n) {
			var r = e.target,
			i, s;
			if ( !! G.test(r.nodeName) && !r.readOnly) {
				i = m.data(r, "_change_data"),
				s = Z(r),
				(e.type !== "focusout" || r.type !== "radio") && m.data(r, "_change_data", s);
				if (i !== t && s !== i) if (i != null || s) return e.type = "change",
				m.event.trigger(e, n, r)
			}
		};
		m.event.special.change = {
			filters: {
				focusout: et,
				click: function(e) {
					var t = e.target,
					n = t.type;
					if (n === "radio" || n === "checkbox" || t.nodeName.toLowerCase() === "select") return et.call(this, e)
				},
				keydown: function(e) {
					var t = e.target,
					n = t.type;
					if (e.keyCode === 13 && t.nodeName.toLowerCase() !== "textarea" || e.keyCode === 32 && (n === "checkbox" || n === "radio") || n === "select-multiple") return et.call(this, e)
				},
				beforeactivate: function(e) {
					e = e.target,
					m.data(e, "_change_data", Z(e))
				}
			},
			setup: function() {
				if (this.type === "file") return ! 1;
				for (var e in Y) m.event.add(this, e + ".specialChange", Y[e]);
				return G.test(this.nodeName)
			},
			teardown: function() {
				return m.event.remove(this, ".specialChange"),
				G.test(this.nodeName)
			}
		},
		Y = m.event.special.change.filters
	}
	b.addEventListener && m.each({
		focus: "focusin",
		blur: "focusout"
	},
	function(e, t) {
		function n(e) {
			return e = m.event.fix(e),
			e.type = t,
			m.event.handle.call(this, e)
		}
		m.event.special[t] = {
			setup: function() {
				this.addEventListener(e, n, !0)
			},
			teardown: function() {
				this.removeEventListener(e, n, !0)
			}
		}
	}),
	m.each(["bind", "one"],
	function(e, n) {
		m.fn[n] = function(e, r, i) {
			if (typeof e == "object") {
				for (var s in e) this[n](s, r, e[s], i);
				return this
			}
			m.isFunction(r) && (i = r, r = t);
			var o = n === "one" ? m.proxy(i,
			function(e) {
				return m(this).unbind(e, o),
				i.apply(this, arguments)
			}) : i;
			if (e === "unload" && n !== "one") this.one(e, r, i);
			else {
				s = 0;
				for (var u = this.length; s < u; s++) m.event.add(this[s], e, o, r)
			}
			return this
		}
	}),
	m.fn.extend({
		unbind: function(e, t) {
			if (typeof e == "object" && !e.preventDefault) for (var n in e) this.unbind(n, e[n]);
			else {
				n = 0;
				for (var r = this.length; n < r; n++) m.event.remove(this[n], e, t)
			}
			return this
		},
		delegate: function(e, t, n, r) {
			return this.live(t, n, r, e)
		},
		undelegate: function(e, t, n) {
			return arguments.length === 0 ? this.unbind("live") : this.die(t, null, n, e)
		},
		trigger: function(e, t) {
			return this.each(function() {
				m.event.trigger(e, t, this)
			})
		},
		triggerHandler: function(e, t) {
			if (this[0]) return e = m.Event(e),
			e.preventDefault(),
			e.stopPropagation(),
			m.event.trigger(e, t, this[0]),
			e.result
		},
		toggle: function(e) {
			for (var t = arguments,
			n = 1; n < t.length;) m.proxy(e, t[n++]);
			return this.click(m.proxy(e,
			function(r) {
				var i = (m.data(this, "lastToggle" + e.guid) || 0) % n;
				return m.data(this, "lastToggle" + e.guid, i + 1),
				r.preventDefault(),
				t[i].apply(this, arguments) || !1
			}))
		},
		hover: function(e, t) {
			return this.mouseenter(e).mouseleave(t || e)
		}
	});
	var tt = {
		focus: "focusin",
		blur: "focusout",
		mouseenter: "mouseover",
		mouseleave: "mouseout"
	};
	m.each(["live", "die"],
	function(e, n) {
		m.fn[n] = function(e, r, i, s) {
			var o, u = 0,
			a, f, c = s || this.selector,
			h = s ? this: m(this.context);
			m.isFunction(r) && (i = r, r = t);
			for (e = (e || "").split(" "); (o = e[u++]) != null;) s = V.exec(o),
			a = "",
			s && (a = s[0], o = o.replace(V, "")),
			o === "hover" ? e.push("mouseenter" + a, "mouseleave" + a) : (f = o, o === "focus" || o === "blur" ? (e.push(tt[o] + a), o += a) : o = (tt[o] || o) + a, n === "live" ? h.each(function() {
				m.event.add(this, l(o, c), {
					data: r,
					selector: c,
					handler: i,
					origType: o,
					origHandler: i,
					preType: f
				})
			}) : h.unbind(l(o, c), i));
			return this
		}
	}),
	m.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error".split(" "),
	function(e, t) {
		m.fn[t] = function(e) {
			return e ? this.bind(t, e) : this.trigger(t)
		},
		m.attrFn && (m.attrFn[t] = !0)
	}),
	e.attachEvent && !e.addEventListener && e.attachEvent("onunload",
	function() {
		for (var e in m.cache) if (m.cache[e].handle) try {
			m.event.remove(m.cache[e].handle.elem)
		} catch(t) {}
	}),
	function() {
		function e(t) {
			for (var n = "",
			r, i = 0; t[i]; i++) r = t[i],
			r.nodeType === 3 || r.nodeType === 4 ? n += r.nodeValue: r.nodeType !== 8 && (n += e(r.childNodes));
			return n
		}
		function n(e, t, n, r, i, s) {
			i = 0;
			for (var o = r.length; i < o; i++) {
				var u = r[i];
				if (u) {
					u = u[e];
					for (var a = !1; u;) {
						if (u.sizcache === n) {
							a = r[u.sizset];
							break
						}
						u.nodeType === 1 && !s && (u.sizcache = n, u.sizset = i);
						if (u.nodeName.toLowerCase() === t) {
							a = u;
							break
						}
						u = u[e]
					}
					r[i] = a
				}
			}
		}
		function r(e, t, n, r, i, s) {
			i = 0;
			for (var o = r.length; i < o; i++) {
				var u = r[i];
				if (u) {
					u = u[e];
					for (var a = !1; u;) {
						if (u.sizcache === n) {
							a = r[u.sizset];
							break
						}
						if (u.nodeType === 1) {
							s || (u.sizcache = n, u.sizset = i);
							if (typeof t != "string") {
								if (u === t) {
									a = !0;
									break
								}
							} else if (f.filter(t, [u]).length > 0) {
								a = u;
								break
							}
						}
						u = u[e]
					}
					r[i] = a
				}
			}
		}
		var i = /((?:\((?:\([^()]+\)|[^()]+)+\)|\[(?:\[[^[\]]*\]|['"][^'"]*['"]|[^[\]'"]+)+\]|\\.|[^ >+~,(\[\\]+)+|[>+~])(\s*,\s*)?((?:.|\r|\n)*)/g,
		s = 0,
		o = Object.prototype.toString,
		u = !1,
		a = !0; [0, 0].sort(function() {
			return a = !1,
			0
		});
		var f = function(e, t, n, r) {
			n = n || [];
			var s = t = t || b;
			if (t.nodeType !== 1 && t.nodeType !== 9) return [];
			if (!e || typeof e != "string") return n;
			for (var u = [], a, h, d, v, m = !0, E = y(t), S = e; (i.exec(""), a = i.exec(S)) !== null;) {
				S = a[3],
				u.push(a[1]);
				if (a[2]) {
					v = a[3];
					break
				}
			}
			if (u.length > 1 && c.exec(e)) if (u.length === 2 && l.relative[u[0]]) h = w(u[0] + u[1], t);
			else for (h = l.relative[u[0]] ? [t] : f(u.shift(), t); u.length;) e = u.shift(),
			l.relative[e] && (e += u.shift()),
			h = w(e, h);
			else { ! r && u.length > 1 && t.nodeType === 9 && !E && l.match.ID.test(u[0]) && !l.match.ID.test(u[u.length - 1]) && (a = f.find(u.shift(), t, E), t = a.expr ? f.filter(a.expr, a.set)[0] : a.set[0]);
				if (t) {
					a = r ? {
						expr: u.pop(),
						set: p(r)
					}: f.find(u.pop(), u.length !== 1 || u[0] !== "~" && u[0] !== "+" || !t.parentNode ? t: t.parentNode, E),
					h = a.expr ? f.filter(a.expr, a.set) : a.set,
					u.length > 0 ? d = p(h) : m = !1;
					for (; u.length;) {
						var x = u.pop();
						a = x,
						l.relative[x] ? a = u.pop() : x = "",
						a == null && (a = t),
						l.relative[x](d, a, E)
					}
				} else d = []
			}
			d || (d = h),
			d || f.error(x || e);
			if (o.call(d) === "[object Array]") if (m) if (t && t.nodeType === 1) for (e = 0; d[e] != null; e++) d[e] && (d[e] === !0 || d[e].nodeType === 1 && g(t, d[e])) && n.push(h[e]);
			else for (e = 0; d[e] != null; e++) d[e] && d[e].nodeType === 1 && n.push(h[e]);
			else n.push.apply(n, d);
			else p(d, n);
			return v && (f(v, s, n, r), f.uniqueSort(n)),
			n
		};
		f.uniqueSort = function(e) {
			if (v) {
				u = a,
				e.sort(v);
				if (u) for (var t = 1; t < e.length; t++) e[t] === e[t - 1] && e.splice(t--, 1)
			}
			return e
		},
		f.matches = function(e, t) {
			return f(e, null, null, t)
		},
		f.find = function(e, t, n) {
			var r, i;
			if (!e) return [];
			for (var s = 0,
			o = l.order.length; s < o; s++) {
				var u = l.order[s];
				if (i = l.leftMatch[u].exec(e)) {
					var a = i[1];
					i.splice(1, 1);
					if (a.substr(a.length - 1) !== "\\") {
						i[1] = (i[1] || "").replace(/\\/g, ""),
						r = l.find[u](i, t, n);
						if (r != null) {
							e = e.replace(l.match[u], "");
							break
						}
					}
				}
			}
			return r || (r = t.getElementsByTagName("*")),
			{
				set: r,
				expr: e
			}
		},
		f.filter = function(e, n, r, i) {
			for (var s = e,
			o = [], u = n, a, c, h = n && n[0] && y(n[0]); e && n.length;) {
				for (var p in l.filter) if ((a = l.leftMatch[p].exec(e)) != null && a[2]) {
					var d = l.filter[p],
					v,
					m;
					m = a[1],
					c = !1,
					a.splice(1, 1);
					if (m.substr(m.length - 1) !== "\\") {
						u === o && (o = []);
						if (l.preFilter[p]) if (a = l.preFilter[p](a, u, r, o, i, h)) {
							if (a === !0) continue
						} else c = v = !0;
						if (a) for (var g = 0; (m = u[g]) != null; g++) if (m) {
							v = d(m, a, g, u);
							var b = i ^ !!v;
							r && v != null ? b ? c = !0 : u[g] = !1 : b && (o.push(m), c = !0)
						}
						if (v !== t) {
							r || (u = o),
							e = e.replace(l.match[p], "");
							if (!c) return [];
							break
						}
					}
				}
				if (e === s) {
					if (c != null) break;
					f.error(e)
				}
				s = e
			}
			return u
		},
		f.error = function(e) {
			throw "Syntax error, unrecognized expression: " + e
		};
		var l = f.selectors = {
			order: ["ID", "NAME", "TAG"],
			match: {
				ID: /#((?:[\w\u00c0-\uFFFF-]|\\.)+)/,
				CLASS: /\.((?:[\w\u00c0-\uFFFF-]|\\.)+)/,
				NAME: /\[name=['"]*((?:[\w\u00c0-\uFFFF-]|\\.)+)['"]*\]/,
				ATTR: /\[\s*((?:[\w\u00c0-\uFFFF-]|\\.)+)\s*(?:(\S?=)\s*(['"]*)(.*?)\3|)\s*\]/,
				TAG: /^((?:[\w\u00c0-\uFFFF\*-]|\\.)+)/,
				CHILD: /:(only|nth|last|first)-child(?:\((even|odd|[\dn+-]*)\))?/,
				POS: /:(nth|eq|gt|lt|first|last|even|odd)(?:\((\d*)\))?(?=[^-]|$)/,
				PSEUDO: /:((?:[\w\u00c0-\uFFFF-]|\\.)+)(?:\((['"]?)((?:\([^\)]+\)|[^\(\)]*)+)\2\))?/
			},
			leftMatch: {},
			attrMap: {
				"class": "className",
				"for": "htmlFor"
			},
			attrHandle: {
				href: function(e) {
					return e.getAttribute("href")
				}
			},
			relative: {
				"+": function(e, t) {
					var n = typeof t == "string",
					r = n && !/\W/.test(t);
					n = n && !r,
					r && (t = t.toLowerCase()),
					r = 0;
					for (var i = e.length,
					s; r < i; r++) if (s = e[r]) {
						for (; (s = s.previousSibling) && s.nodeType !== 1;);
						e[r] = n || s && s.nodeName.toLowerCase() === t ? s || !1 : s === t
					}
					n && f.filter(t, e, !0)
				},
				">": function(e, t) {
					var n = typeof t == "string";
					if (n && !/\W/.test(t)) {
						t = t.toLowerCase();
						for (var r = 0,
						i = e.length; r < i; r++) {
							var s = e[r];
							s && (n = s.parentNode, e[r] = n.nodeName.toLowerCase() === t ? n: !1)
						}
					} else {
						r = 0;
						for (i = e.length; r < i; r++) if (s = e[r]) e[r] = n ? s.parentNode: s.parentNode === t;
						n && f.filter(t, e, !0)
					}
				},
				"": function(e, t, i) {
					var o = s++,
					u = r;
					if (typeof t == "string" && !/\W/.test(t)) {
						var a = t = t.toLowerCase();
						u = n
					}
					u("parentNode", t, o, e, a, i)
				},
				"~": function(e, t, i) {
					var o = s++,
					u = r;
					if (typeof t == "string" && !/\W/.test(t)) {
						var a = t = t.toLowerCase();
						u = n
					}
					u("previousSibling", t, o, e, a, i)
				}
			},
			find: {
				ID: function(e, t, n) {
					if (typeof t.getElementById != "undefined" && !n) return (e = t.getElementById(e[1])) ? [e] : []
				},
				NAME: function(e, t) {
					if (typeof t.getElementsByName != "undefined") {
						var n = [];
						t = t.getElementsByName(e[1]);
						for (var r = 0,
						i = t.length; r < i; r++) t[r].getAttribute("name") === e[1] && n.push(t[r]);
						return n.length === 0 ? null: n
					}
				},
				TAG: function(e, t) {
					return t.getElementsByTagName(e[1])
				}
			},
			preFilter: {
				CLASS: function(e, t, n, r, i, s) {
					e = " " + e[1].replace(/\\/g, "") + " ";
					if (s) return e;
					s = 0;
					for (var o; (o = t[s]) != null; s++) o && (i ^ (o.className && (" " + o.className + " ").replace(/[\t\n]/g, " ").indexOf(e) >= 0) ? n || r.push(o) : n && (t[s] = !1));
					return ! 1
				},
				ID: function(e) {
					return e[1].replace(/\\/g, "")
				},
				TAG: function(e) {
					return e[1].toLowerCase()
				},
				CHILD: function(e) {
					if (e[1] === "nth") {
						var t = /(-?)(\d*)n((?:\+|-)?\d*)/.exec(e[2] === "even" && "2n" || e[2] === "odd" && "2n+1" || !/\D/.test(e[2]) && "0n+" + e[2] || e[2]);
						e[2] = t[1] + (t[2] || 1) - 0,
						e[3] = t[3] - 0
					}
					return e[0] = s++,
					e
				},
				ATTR: function(e, t, n, r, i, s) {
					return t = e[1].replace(/\\/g, ""),
					!s && l.attrMap[t] && (e[1] = l.attrMap[t]),
					e[2] === "~=" && (e[4] = " " + e[4] + " "),
					e
				},
				PSEUDO: function(e, t, n, r, s) {
					if (e[1] === "not") {
						if (! ((i.exec(e[3]) || "").length > 1 || /^\w/.test(e[3]))) return e = f.filter(e[3], t, n, !0 ^ s),
						n || r.push.apply(r, e),
						!1;
						e[3] = f(e[3], null, null, t)
					} else if (l.match.POS.test(e[0]) || l.match.CHILD.test(e[0])) return ! 0;
					return e
				},
				POS: function(e) {
					return e.unshift(!0),
					e
				}
			},
			filters: {
				enabled: function(e) {
					return e.disabled === !1 && e.type !== "hidden"
				},
				disabled: function(e) {
					return e.disabled === !0
				},
				checked: function(e) {
					return e.checked === !0
				},
				selected: function(e) {
					return e.selected === !0
				},
				parent: function(e) {
					return !! e.firstChild
				},
				empty: function(e) {
					return ! e.firstChild
				},
				has: function(e, t, n) {
					return !! f(n[3], e).length
				},
				header: function(e) {
					return /h\d/i.test(e.nodeName)
				},
				text: function(e) {
					return "text" === e.type
				},
				radio: function(e) {
					return "radio" === e.type
				},
				checkbox: function(e) {
					return "checkbox" === e.type
				},
				file: function(e) {
					return "file" === e.type
				},
				password: function(e) {
					return "password" === e.type
				},
				submit: function(e) {
					return "submit" === e.type
				},
				image: function(e) {
					return "image" === e.type
				},
				reset: function(e) {
					return "reset" === e.type
				},
				button: function(e) {
					return "button" === e.type || e.nodeName.toLowerCase() === "button"
				},
				input: function(e) {
					return /input|select|textarea|button/i.test(e.nodeName)
				}
			},
			setFilters: {
				first: function(e, t) {
					return t === 0
				},
				last: function(e, t, n, r) {
					return t === r.length - 1
				},
				even: function(e, t) {
					return t % 2 === 0
				},
				odd: function(e, t) {
					return t % 2 === 1
				},
				lt: function(e, t, n) {
					return t < n[3] - 0
				},
				gt: function(e, t, n) {
					return t > n[3] - 0
				},
				nth: function(e, t, n) {
					return n[3] - 0 === t
				},
				eq: function(e, t, n) {
					return n[3] - 0 === t
				}
			},
			filter: {
				PSEUDO: function(t, n, r, i) {
					var s = n[1],
					o = l.filters[s];
					if (o) return o(t, r, n, i);
					if (s === "contains") return (t.textContent || t.innerText || e([t]) || "").indexOf(n[3]) >= 0;
					if (s === "not") {
						n = n[3],
						r = 0;
						for (i = n.length; r < i; r++) if (n[r] === t) return ! 1;
						return ! 0
					}
					f.error("Syntax error, unrecognized expression: " + s)
				},
				CHILD: function(e, t) {
					var n = t[1],
					r = e;
					switch (n) {
					case "only":
					case "first":
						for (; r = r.previousSibling;) if (r.nodeType === 1) return ! 1;
						if (n === "first") return ! 0;
						r = e;
					case "last":
						for (; r = r.nextSibling;) if (r.nodeType === 1) return ! 1;
						return ! 0;
					case "nth":
						n = t[2];
						var i = t[3];
						if (n === 1 && i === 0) return ! 0;
						t = t[0];
						var s = e.parentNode;
						if (s && (s.sizcache !== t || !e.nodeIndex)) {
							var o = 0;
							for (r = s.firstChild; r; r = r.nextSibling) r.nodeType === 1 && (r.nodeIndex = ++o);
							s.sizcache = t
						}
						return e = e.nodeIndex - i,
						n === 0 ? e === 0 : e % n === 0 && e / n >= 0
					}
				},
				ID: function(e, t) {
					return e.nodeType === 1 && e.getAttribute("id") === t
				},
				TAG: function(e, t) {
					return t === "*" && e.nodeType === 1 || e.nodeName.toLowerCase() === t
				},
				CLASS: function(e, t) {
					return (" " + (e.className || e.getAttribute("class")) + " ").indexOf(t) > -1
				},
				ATTR: function(e, t) {
					var n = t[1];
					e = l.attrHandle[n] ? l.attrHandle[n](e) : e[n] != null ? e[n] : e.getAttribute(n),
					n = e + "";
					var r = t[2];
					return t = t[4],
					e == null ? r === "!=": r === "=" ? n === t: r === "*=" ? n.indexOf(t) >= 0 : r === "~=" ? (" " + n + " ").indexOf(t) >= 0 : t ? r === "!=" ? n !== t: r === "^=" ? n.indexOf(t) === 0 : r === "$=" ? n.substr(n.length - t.length) === t: r === "|=" ? n === t || n.substr(0, t.length + 1) === t + "-": !1 : n && e !== !1
				},
				POS: function(e, t, n, r) {
					var i = l.setFilters[t[2]];
					if (i) return i(e, n, t, r)
				}
			}
		},
		c = l.match.POS;
		for (var h in l.match) l.match[h] = new RegExp(l.match[h].source + /(?![^\[]*\])(?![^\(]*\))/.source),
		l.leftMatch[h] = new RegExp(/(^(?:.|\r|\n)*?)/.source + l.match[h].source.replace(/\\(\d+)/g,
		function(e, t) {
			return "\\" + (t - 0 + 1)
		}));
		var p = function(e, t) {
			return e = Array.prototype.slice.call(e, 0),
			t ? (t.push.apply(t, e), t) : e
		};
		try {
			Array.prototype.slice.call(b.documentElement.childNodes, 0)
		} catch(d) {
			p = function(e, t) {
				t = t || [];
				if (o.call(e) === "[object Array]") Array.prototype.push.apply(t, e);
				else if (typeof e.length == "number") for (var n = 0,
				r = e.length; n < r; n++) t.push(e[n]);
				else for (n = 0; e[n]; n++) t.push(e[n]);
				return t
			}
		}
		var v;
		b.documentElement.compareDocumentPosition ? v = function(e, t) {
			return ! e.compareDocumentPosition || !t.compareDocumentPosition ? (e == t && (u = !0), e.compareDocumentPosition ? -1 : 1) : (e = e.compareDocumentPosition(t) & 4 ? -1 : e === t ? 0 : 1, e === 0 && (u = !0), e)
		}: "sourceIndex" in b.documentElement ? v = function(e, t) {
			return ! e.sourceIndex || !t.sourceIndex ? (e == t && (u = !0), e.sourceIndex ? -1 : 1) : (e = e.sourceIndex - t.sourceIndex, e === 0 && (u = !0), e)
		}: b.createRange && (v = function(e, t) {
			if (!e.ownerDocument || !t.ownerDocument) return e == t && (u = !0),
			e.ownerDocument ? -1 : 1;
			var n = e.ownerDocument.createRange(),
			r = t.ownerDocument.createRange();
			return n.setStart(e, 0),
			n.setEnd(e, 0),
			r.setStart(t, 0),
			r.setEnd(t, 0),
			e = n.compareBoundaryPoints(Range.START_TO_END, r),
			e === 0 && (u = !0),
			e
		}),
		function() {
			var e = b.createElement("div"),
			n = "script" + (new Date).getTime();
			e.innerHTML = "<a name='" + n + "'/>";
			var r = b.documentElement;
			r.insertBefore(e, r.firstChild),
			b.getElementById(n) && (l.find.ID = function(e, n, r) {
				if (typeof n.getElementById != "undefined" && !r) return (n = n.getElementById(e[1])) ? n.id === e[1] || typeof n.getAttributeNode != "undefined" && n.getAttributeNode("id").nodeValue === e[1] ? [n] : t: []
			},
			l.filter.ID = function(e, t) {
				var n = typeof e.getAttributeNode != "undefined" && e.getAttributeNode("id");
				return e.nodeType === 1 && n && n.nodeValue === t
			}),
			r.removeChild(e),
			r = e = null
		} (),
		function() {
			var e = b.createElement("div");
			e.appendChild(b.createComment("")),
			e.getElementsByTagName("*").length > 0 && (l.find.TAG = function(e, t) {
				t = t.getElementsByTagName(e[1]);
				if (e[1] === "*") {
					e = [];
					for (var n = 0; t[n]; n++) t[n].nodeType === 1 && e.push(t[n]);
					t = e
				}
				return t
			}),
			e.innerHTML = "<a href='#'></a>",
			e.firstChild && typeof e.firstChild.getAttribute != "undefined" && e.firstChild.getAttribute("href") !== "#" && (l.attrHandle.href = function(e) {
				return e.getAttribute("href", 2)
			}),
			e = null
		} (),
		b.querySelectorAll &&
		function() {
			var e = f,
			t = b.createElement("div");
			t.innerHTML = "<p class='TEST'></p>";
			if (!t.querySelectorAll || t.querySelectorAll(".TEST").length !== 0) {
				f = function(t, n, r, i) {
					n = n || b;
					if (!i && n.nodeType === 9 && !y(n)) try {
						return p(n.querySelectorAll(t), r)
					} catch(s) {}
					return e(t, n, r, i)
				};
				for (var n in e) f[n] = e[n];
				t = null
			}
		} (),
		function() {
			var e = b.createElement("div");
			e.innerHTML = "<div class='test e'></div><div class='test'></div>",
			!!e.getElementsByClassName && e.getElementsByClassName("e").length !== 0 && (e.lastChild.className = "e", e.getElementsByClassName("e").length !== 1 && (l.order.splice(1, 0, "CLASS"), l.find.CLASS = function(e, t, n) {
				if (typeof t.getElementsByClassName != "undefined" && !n) return t.getElementsByClassName(e[1])
			},
			e = null))
		} ();
		var g = b.compareDocumentPosition ?
		function(e, t) {
			return !! (e.compareDocumentPosition(t) & 16)
		}: function(e, t) {
			return e !== t && (e.contains ? e.contains(t) : !0)
		},
		y = function(e) {
			return (e = (e ? e.ownerDocument || e: 0).documentElement) ? e.nodeName !== "HTML": !1
		},
		w = function(e, t) {
			var n = [],
			r = "",
			i;
			for (t = t.nodeType ? [t] : t; i = l.match.PSEUDO.exec(e);) r += i[0],
			e = e.replace(l.match.PSEUDO, "");
			e = l.relative[e] ? e + "*": e,
			i = 0;
			for (var s = t.length; i < s; i++) f(e, t[i], n);
			return f.filter(r, n)
		};
		m.find = f,
		m.expr = f.selectors,
		m.expr[":"] = m.expr.filters,
		m.unique = f.uniqueSort,
		m.text = e,
		m.isXMLDoc = y,
		m.contains = g
	} ();
	var nt = /Until$/,
	rt = /^(?:parents|prevUntil|prevAll)/,
	it = /,/;
	D = Array.prototype.slice;
	var st = function(e, t, n) {
		if (m.isFunction(t)) return m.grep(e,
		function(e, r) {
			return !! t.call(e, r, e) === n
		});
		if (t.nodeType) return m.grep(e,
		function(e) {
			return e === t === n
		});
		if (typeof t == "string") {
			var r = m.grep(e,
			function(e) {
				return e.nodeType === 1
			});
			if (S.test(t)) return m.filter(t, r, !n);
			t = m.filter(t, r)
		}
		return m.grep(e,
		function(e) {
			return m.inArray(e, t) >= 0 === n
		})
	};
	m.fn.extend({
		find: function(e) {
			for (var t = this.pushStack("", "find", e), n = 0, r = 0, i = this.length; r < i; r++) {
				n = t.length,
				m.find(e, this[r], t);
				if (r > 0) for (var s = n; s < t.length; s++) for (var o = 0; o < n; o++) if (t[o] === t[s]) {
					t.splice(s--, 1);
					break
				}
			}
			return t
		},
		has: function(e) {
			var t = m(e);
			return this.filter(function() {
				for (var e = 0,
				n = t.length; e < n; e++) if (m.contains(this, t[e])) return ! 0
			})
		},
		not: function(e) {
			return this.pushStack(st(this, e, !1), "not", e)
		},
		filter: function(e) {
			return this.pushStack(st(this, e, !0), "filter", e)
		},
		is: function(e) {
			return !! e && m.filter(e, this).length > 0
		},
		closest: function(e, t) {
			if (m.isArray(e)) {
				var n = [],
				r = this[0],
				i,
				s = {},
				o;
				if (r && e.length) {
					i = 0;
					for (var u = e.length; i < u; i++) o = e[i],
					s[o] || (s[o] = m.expr.match.POS.test(o) ? m(o, t || this.context) : o);
					for (; r && r.ownerDocument && r !== t;) {
						for (o in s) {
							i = s[o];
							if (i.jquery ? i.index(r) > -1 : m(r).is(i)) n.push({
								selector: o,
								elem: r
							}),
							delete s[o]
						}
						r = r.parentNode
					}
				}
				return n
			}
			var a = m.expr.match.POS.test(e) ? m(e, t || this.context) : null;
			return this.map(function(n, r) {
				for (; r && r.ownerDocument && r !== t;) {
					if (a ? a.index(r) > -1 : m(r).is(e)) return r;
					r = r.parentNode
				}
				return null
			})
		},
		index: function(e) {
			return ! e || typeof e == "string" ? m.inArray(this[0], e ? m(e) : this.parent().children()) : m.inArray(e.jquery ? e[0] : e, this)
		},
		add: function(e, t) {
			return e = typeof e == "string" ? m(e, t || this.context) : m.makeArray(e),
			t = m.merge(this.get(), e),
			this.pushStack(c(e[0]) || c(t[0]) ? t: m.unique(t))
		},
		andSelf: function() {
			return this.add(this.prevObject)
		}
	}),
	m.each({
		parent: function(e) {
			return (e = e.parentNode) && e.nodeType !== 11 ? e: null
		},
		parents: function(e) {
			return m.dir(e, "parentNode")
		},
		parentsUntil: function(e, t, n) {
			return m.dir(e, "parentNode", n)
		},
		next: function(e) {
			return m.nth(e, 2, "nextSibling")
		},
		prev: function(e) {
			return m.nth(e, 2, "previousSibling")
		},
		nextAll: function(e) {
			return m.dir(e, "nextSibling")
		},
		prevAll: function(e) {
			return m.dir(e, "previousSibling")
		},
		nextUntil: function(e, t, n) {
			return m.dir(e, "nextSibling", n)
		},
		prevUntil: function(e, t, n) {
			return m.dir(e, "previousSibling", n)
		},
		siblings: function(e) {
			return m.sibling(e.parentNode.firstChild, e)
		},
		children: function(e) {
			return m.sibling(e.firstChild)
		},
		contents: function(e) {
			return m.nodeName(e, "iframe") ? e.contentDocument || e.contentWindow.document: m.makeArray(e.childNodes)
		}
	},
	function(e, t) {
		m.fn[e] = function(n, r) {
			var i = m.map(this, t, n);
			return nt.test(e) || (r = n),
			r && typeof r == "string" && (i = m.filter(r, i)),
			i = this.length > 1 ? m.unique(i) : i,
			(this.length > 1 || it.test(r)) && rt.test(e) && (i = i.reverse()),
			this.pushStack(i, e, D.call(arguments).join(","))
		}
	}),
	m.extend({
		filter: function(e, t, n) {
			return n && (e = ":not(" + e + ")"),
			m.find.matches(e, t)
		},
		dir: function(e, n, r) {
			var i = [];
			for (e = e[n]; e && e.nodeType !== 9 && (r === t || e.nodeType !== 1 || !m(e).is(r));) e.nodeType === 1 && i.push(e),
			e = e[n];
			return i
		},
		nth: function(e, t, n) {
			t = t || 1;
			for (var r = 0; e; e = e[n]) if (e.nodeType === 1 && ++r === t) break;
			return e
		},
		sibling: function(e, t) {
			for (var n = []; e; e = e.nextSibling) e.nodeType === 1 && e !== t && n.push(e);
			return n
		}
	});
	var ot = / jQuery\d+="(?:\d+|null)"/g,
	ut = /^\s+/,
	at = /(<([\w:]+)[^>]*?)\/>/g,
	ft = /^(?:area|br|col|embed|hr|img|input|link|meta|param)$/i,
	lt = /<([\w:]+)/,
	ct = /<tbody/i,
	ht = /<|&#?\w+;/,
	pt = /<script|<object|<embed|<option|<style/i,
	dt = /checked\s*(?:[^=]|=\s*.checked.)/i,
	vt = function(e, t, n) {
		return ft.test(n) ? e: t + "></" + n + ">"
	},
	mt = {
		option: [1, "<select multiple='multiple'>", "</select>"],
		legend: [1, "<fieldset>", "</fieldset>"],
		thead: [1, "<table>", "</table>"],
		tr: [2, "<table><tbody>", "</tbody></table>"],
		td: [3, "<table><tbody><tr>", "</tr></tbody></table>"],
		col: [2, "<table><tbody></tbody><colgroup>", "</colgroup></table>"],
		area: [1, "<map>", "</map>"],
		_default: [0, "", ""]
	};
	mt.optgroup = mt.option,
	mt.tbody = mt.tfoot = mt.colgroup = mt.caption = mt.thead,
	mt.th = mt.td,
	m.support.htmlSerialize || (mt._default = [1, "div<div>", "</div>"]),
	m.fn.extend({
		text: function(e) {
			return m.isFunction(e) ? this.each(function(t) {
				var n = m(this);
				n.text(e.call(this, t, n.text()))
			}) : typeof e != "object" && e !== t ? this.empty().append((this[0] && this[0].ownerDocument || b).createTextNode(e)) : m.text(this)
		},
		wrapAll: function(e) {
			if (m.isFunction(e)) return this.each(function(t) {
				m(this).wrapAll(e.call(this, t))
			});
			if (this[0]) {
				var t = m(e, this[0].ownerDocument).eq(0).clone(!0);
				this[0].parentNode && t.insertBefore(this[0]),
				t.map(function() {
					for (var e = this; e.firstChild && e.firstChild.nodeType === 1;) e = e.firstChild;
					return e
				}).append(this)
			}
			return this
		},
		wrapInner: function(e) {
			return m.isFunction(e) ? this.each(function(t) {
				m(this).wrapInner(e.call(this, t))
			}) : this.each(function() {
				var t = m(this),
				n = t.contents();
				n.length ? n.wrapAll(e) : t.append(e)
			})
		},
		wrap: function(e) {
			return this.each(function() {
				m(this).wrapAll(e)
			})
		},
		unwrap: function() {
			return this.parent().each(function() {
				m.nodeName(this, "body") || m(this).replaceWith(this.childNodes)
			}).end()
		},
		append: function() {
			return this.domManip(arguments, !0,
			function(e) {
				this.nodeType === 1 && this.appendChild(e)
			})
		},
		prepend: function() {
			return this.domManip(arguments, !0,
			function(e) {
				this.nodeType === 1 && this.insertBefore(e, this.firstChild)
			})
		},
		before: function() {
			if (this[0] && this[0].parentNode) return this.domManip(arguments, !1,
			function(e) {
				this.parentNode.insertBefore(e, this)
			});
			if (arguments.length) {
				var e = m(arguments[0]);
				return e.push.apply(e, this.toArray()),
				this.pushStack(e, "before", arguments)
			}
		},
		after: function() {
			if (this[0] && this[0].parentNode) return this.domManip(arguments, !1,
			function(e) {
				this.parentNode.insertBefore(e, this.nextSibling)
			});
			if (arguments.length) {
				var e = this.pushStack(this, "after", arguments);
				return e.push.apply(e, m(arguments[0]).toArray()),
				e
			}
		},
		remove: function(e, t) {
			for (var n = 0,
			r; (r = this[n]) != null; n++) if (!e || m.filter(e, [r]).length) ! t && r.nodeType === 1 && (m.cleanData(r.getElementsByTagName("*")), m.cleanData([r])),
			r.parentNode && r.parentNode.removeChild(r);
			return this
		},
		empty: function() {
			for (var e = 0,
			t; (t = this[e]) != null; e++) for (t.nodeType === 1 && m.cleanData(t.getElementsByTagName("*")); t.firstChild;) t.removeChild(t.firstChild);
			return this
		},
		clone: function(e) {
			var t = this.map(function() {
				if (!m.support.noCloneEvent && !m.isXMLDoc(this)) {
					var e = this.outerHTML,
					t = this.ownerDocument;
					return e || (e = t.createElement("div"), e.appendChild(this.cloneNode(!0)), e = e.innerHTML),
					m.clean([e.replace(ot, "").replace(/=([^="'>\s]+\/)>/g, '="$1">').replace(ut, "")], t)[0]
				}
				return this.cloneNode(!0)
			});
			return e === !0 && (h(this, t), h(this.find("*"), t.find("*"))),
			t
		},
		html: function(e) {
			if (e === t) return this[0] && this[0].nodeType === 1 ? this[0].innerHTML.replace(ot, "") : null;
			if (typeof e == "string" && !pt.test(e) && (m.support.leadingWhitespace || !ut.test(e)) && !mt[(lt.exec(e) || ["", ""])[1].toLowerCase()]) {
				e = e.replace(at, vt);
				try {
					for (var n = 0,
					r = this.length; n < r; n++) this[n].nodeType === 1 && (m.cleanData(this[n].getElementsByTagName("*")), this[n].innerHTML = e)
				} catch(i) {
					this.empty().append(e)
				}
			} else m.isFunction(e) ? this.each(function(t) {
				var n = m(this),
				r = n.html();
				n.empty().append(function() {
					return e.call(this, t, r)
				})
			}) : this.empty().append(e);
			return this
		},
		replaceWith: function(e) {
			return this[0] && this[0].parentNode ? m.isFunction(e) ? this.each(function(t) {
				var n = m(this),
				r = n.html();
				n.replaceWith(e.call(this, t, r))
			}) : (typeof e != "string" && (e = m(e).detach()), this.each(function() {
				var t = this.nextSibling,
				n = this.parentNode;
				m(this).remove(),
				t ? m(t).before(e) : m(n).append(e)
			})) : this.pushStack(m(m.isFunction(e) ? e() : e), "replaceWith", e)
		},
		detach: function(e) {
			return this.remove(e, !0)
		},
		domManip: function(e, n, i) {
			function s(e) {
				return m.nodeName(e, "table") ? e.getElementsByTagName("tbody")[0] || e.appendChild(e.ownerDocument.createElement("tbody")) : e
			}
			var o, u, a = e[0],
			f = [],
			l;
			if (!m.support.checkClone && arguments.length === 3 && typeof a == "string" && dt.test(a)) return this.each(function() {
				m(this).domManip(e, n, i, !0)
			});
			if (m.isFunction(a)) return this.each(function(r) {
				var s = m(this);
				e[0] = a.call(this, r, n ? s.html() : t),
				s.domManip(e, n, i)
			});
			if (this[0]) {
				o = a && a.parentNode,
				o = m.support.parentNode && o && o.nodeType === 11 && o.childNodes.length === this.length ? {
					fragment: o
				}: p(e, this, f),
				l = o.fragment;
				if (u = l.childNodes.length === 1 ? l = l.firstChild: l.firstChild) {
					n = n && m.nodeName(u, "tr");
					for (var c = 0,
					h = this.length; c < h; c++) i.call(n ? s(this[c], u) : this[c], c > 0 || o.cacheable || this.length > 1 ? l.cloneNode(!0) : l)
				}
				f.length && m.each(f, r)
			}
			return this
		}
	}),
	m.fragments = {},
	m.each({
		appendTo: "append",
		prependTo: "prepend",
		insertBefore: "before",
		insertAfter: "after",
		replaceAll: "replaceWith"
	},
	function(e, t) {
		m.fn[e] = function(n) {
			var r = [];
			n = m(n);
			var i = this.length === 1 && this[0].parentNode;
			if (i && i.nodeType === 11 && i.childNodes.length === 1 && n.length === 1) return n[t](this[0]),
			this;
			i = 0;
			for (var s = n.length; i < s; i++) {
				var o = (i > 0 ? this.clone(!0) : this).get();
				m.fn[t].apply(m(n[i]), o),
				r = r.concat(o)
			}
			return this.pushStack(r, e, n.selector)
		}
	}),
	m.extend({
		clean: function(e, t, n, r) {
			t = t || b,
			typeof t.createElement == "undefined" && (t = t.ownerDocument || t[0] && t[0].ownerDocument || b);
			for (var i = [], s = 0, o; (o = e[s]) != null; s++) {
				typeof o == "number" && (o += "");
				if (o) {
					if (typeof o == "string" && !ht.test(o)) o = t.createTextNode(o);
					else if (typeof o == "string") {
						o = o.replace(at, vt);
						var u = (lt.exec(o) || ["", ""])[1].toLowerCase(),
						a = mt[u] || mt._default,
						f = a[0],
						l = t.createElement("div");
						for (l.innerHTML = a[1] + o + a[2]; f--;) l = l.lastChild;
						if (!m.support.tbody) {
							f = ct.test(o),
							u = u === "table" && !f ? l.firstChild && l.firstChild.childNodes: a[1] === "<table>" && !f ? l.childNodes: [];
							for (a = u.length - 1; a >= 0; --a) m.nodeName(u[a], "tbody") && !u[a].childNodes.length && u[a].parentNode.removeChild(u[a])
						} ! m.support.leadingWhitespace && ut.test(o) && l.insertBefore(t.createTextNode(ut.exec(o)[0]), l.firstChild),
						o = l.childNodes
					}
					o.nodeType ? i.push(o) : i = m.merge(i, o)
				}
			}
			if (n) for (s = 0; i[s]; s++) r && m.nodeName(i[s], "script") && (!i[s].type || i[s].type.toLowerCase() === "text/javascript") ? r.push(i[s].parentNode ? i[s].parentNode.removeChild(i[s]) : i[s]) : (i[s].nodeType === 1 && i.splice.apply(i, [s + 1, 0].concat(m.makeArray(i[s].getElementsByTagName("script")))), n.appendChild(i[s]));
			return i
		},
		cleanData: function(e) {
			for (var t, n, r = m.cache,
			i = m.event.special,
			s = m.support.deleteExpando,
			o = 0,
			u; (u = e[o]) != null; o++) if (n = u[m.expando]) {
				t = r[n];
				if (t.events) for (var a in t.events) i[a] ? m.event.remove(u, a) : J(u, a, t.handle);
				s ? delete u[m.expando] : u.removeAttribute && u.removeAttribute(m.expando),
				delete r[n]
			}
		}
	});
	var gt = /z-?index|font-?weight|opacity|zoom|line-?height/i,
	yt = /alpha\([^)]*\)/,
	bt = /opacity=([^)]*)/,
	wt = /float/i,
	Et = /-([a-z])/ig,
	St = /([A-Z])/g,
	xt = /^-?\d+(?:px)?$/i,
	Tt = /^-?\d/,
	Nt = {
		position: "absolute",
		visibility: "hidden",
		display: "block"
	},
	Ct = ["Left", "Right"],
	kt = ["Top", "Bottom"],
	Lt = b.defaultView && b.defaultView.getComputedStyle,
	At = m.support.cssFloat ? "cssFloat": "styleFloat",
	Ot = function(e, t) {
		return t.toUpperCase()
	};
	m.fn.css = function(e, n) {
		return i(this, e, n, !0,
		function(e, n, r) {
			if (r === t) return m.curCSS(e, n);
			typeof r == "number" && !gt.test(n) && (r += "px"),
			m.style(e, n, r)
		})
	},
	m.extend({
		style: function(e, n, r) {
			if (!e || e.nodeType === 3 || e.nodeType === 8) return t; (n === "width" || n === "height") && parseFloat(r) < 0 && (r = t);
			var i = e.style || e,
			s = r !== t;
			return ! m.support.opacity && n === "opacity" ? (s && (i.zoom = 1, n = parseInt(r, 10) + "" == "NaN" ? "": "alpha(opacity=" + r * 100 + ")", e = i.filter || m.curCSS(e, "filter") || "", i.filter = yt.test(e) ? e.replace(yt, n) : n), i.filter && i.filter.indexOf("opacity=") >= 0 ? parseFloat(bt.exec(i.filter)[1]) / 100 + "": "") : (wt.test(n) && (n = At), n = n.replace(Et, Ot), s && (i[n] = r), i[n])
		},
		css: function(e, t, n, r) {
			if (t === "width" || t === "height") {
				var i, s = t === "width" ? Ct: kt;
				function o() {
					i = t === "width" ? e.offsetWidth: e.offsetHeight,
					r !== "border" && m.each(s,
					function() {
						r || (i -= parseFloat(m.curCSS(e, "padding" + this, !0)) || 0),
						r === "margin" ? i += parseFloat(m.curCSS(e, "margin" + this, !0)) || 0 : i -= parseFloat(m.curCSS(e, "border" + this + "Width", !0)) || 0
					})
				}
				return e.offsetWidth !== 0 ? o() : m.swap(e, Nt, o),
				Math.max(0, Math.round(i))
			}
			return m.curCSS(e, t, n)
		},
		curCSS: function(e, t, n) {
			var r, i = e.style;
			if (!m.support.opacity && t === "opacity" && e.currentStyle) return r = bt.test(e.currentStyle.filter || "") ? parseFloat(RegExp.$1) / 100 + "": "",
			r === "" ? "1": r;
			wt.test(t) && (t = At);
			if (!n && i && i[t]) r = i[t];
			else if (Lt) {
				wt.test(t) && (t = "float"),
				t = t.replace(St, "-$1").toLowerCase(),
				i = e.ownerDocument.defaultView;
				if (!i) return null;
				if (e = i.getComputedStyle(e, null)) r = e.getPropertyValue(t);
				t === "opacity" && r === "" && (r = "1")
			} else if (e.currentStyle) {
				n = t.replace(Et, Ot),
				r = e.currentStyle[t] || e.currentStyle[n];
				if (!xt.test(r) && Tt.test(r)) {
					t = i.left;
					var s = e.runtimeStyle.left;
					e.runtimeStyle.left = e.currentStyle.left,
					i.left = n === "fontSize" ? "1em": r || 0,
					r = i.pixelLeft + "px",
					i.left = t,
					e.runtimeStyle.left = s
				}
			}
			return r
		},
		swap: function(e, t, n) {
			var r = {};
			for (var i in t) r[i] = e.style[i],
			e.style[i] = t[i];
			n.call(e);
			for (i in t) e.style[i] = r[i]
		}
	}),
	m.expr && m.expr.filters && (m.expr.filters.hidden = function(e) {
		var t = e.offsetWidth,
		n = e.offsetHeight,
		r = e.nodeName.toLowerCase() === "tr";
		return t === 0 && n === 0 && !r ? !0 : t > 0 && n > 0 && !r ? !1 : m.curCSS(e, "display") === "none"
	},
	m.expr.filters.visible = function(e) {
		return ! m.expr.filters.hidden(e)
	});
	var Mt = s(),
	_t = /<script(.|\s)*?\/script>/gi,
	Dt = /select|textarea/i,
	Pt = /color|date|datetime|email|hidden|month|number|password|range|search|tel|text|time|url|week/i,
	Ht = /=\?(&|$)/,
	Bt = /\?/,
	jt = /(\?|&)_=.*?(&|$)/,
	Ft = /^(\w+:)?\/\/([^\/?#]+)/,
	It = /%20/g,
	qt = m.fn.load;
	m.fn.extend({
		load: function(e, t, n) {
			if (typeof e != "string") return qt.call(this, e);
			if (!this.length) return this;
			var r = e.indexOf(" ");
			if (r >= 0) {
				var i = e.slice(r, e.length);
				e = e.slice(0, r)
			}
			r = "GET",
			t && (m.isFunction(t) ? (n = t, t = null) : typeof t == "object" && (t = m.param(t, m.ajaxSettings.traditional), r = "POST"));
			var s = this;
			return m.ajax({
				url: e,
				type: r,
				dataType: "html",
				data: t,
				complete: function(e, t) { (t === "success" || t === "notmodified") && s.html(i ? m("<div />").append(e.responseText.replace(_t, "")).find(i) : e.responseText),
					n && s.each(n, [e.responseText, t, e])
				}
			}),
			this
		},
		serialize: function() {
			return m.param(this.serializeArray())
		},
		serializeArray: function() {
			return this.map(function() {
				return this.elements ? m.makeArray(this.elements) : this
			}).filter(function() {
				return this.name && !this.disabled && (this.checked || Dt.test(this.nodeName) || Pt.test(this.type))
			}).map(function(e, t) {
				return e = m(this).val(),
				e == null ? null: m.isArray(e) ? m.map(e,
				function(e) {
					return {
						name: t.name,
						value: e
					}
				}) : {
					name: t.name,
					value: e
				}
			}).get()
		}
	}),
	m.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "),
	function(e, t) {
		m.fn[t] = function(e) {
			return this.bind(t, e)
		}
	}),
	m.extend({
		get: function(e, t, n, r) {
			return m.isFunction(t) && (r = r || n, n = t, t = null),
			m.ajax({
				type: "GET",
				url: e,
				data: t,
				success: n,
				dataType: r
			})
		},
		getScript: function(e, t) {
			return m.get(e, null, t, "script")
		},
		getJSON: function(e, t, n) {
			return m.get(e, t, n, "json")
		},
		post: function(e, t, n, r) {
			return m.isFunction(t) && (r = r || n, n = t, t = {}),
			m.ajax({
				type: "POST",
				url: e,
				data: t,
				success: n,
				dataType: r
			})
		},
		ajaxSetup: function(e) {
			m.extend(m.ajaxSettings, e)
		},
		ajaxSettings: {
			url: location.href,
			global: !0,
			type: "GET",
			contentType: "application/x-www-form-urlencoded",
			processData: !0,
			async: !0,
			xhr: e.XMLHttpRequest && (e.location.protocol !== "file:" || !e.ActiveXObject) ?
			function() {
				return new e.XMLHttpRequest
			}: function() {
				try {
					return new e.ActiveXObject("Microsoft.XMLHTTP")
				} catch(t) {}
			},
			accepts: {
				xml: "application/xml, text/xml",
				html: "text/html",
				script: "text/javascript, application/javascript",
				json: "application/json, text/javascript",
				text: "text/plain",
				_default: "*/*"
			}
		},
		lastModified: {},
		etag: {},
		ajax: function(n) {
			function r() {
				u.success && u.success.call(c, l, f, E),
				u.global && o("ajaxSuccess", [E, u])
			}
			function i() {
				u.complete && u.complete.call(c, E, f),
				u.global && o("ajaxComplete", [E, u]),
				u.global && !--m.active && m.event.trigger("ajaxStop")
			}
			function o(e, t) { (u.context ? m(u.context) : m.event).trigger(e, t)
			}
			var u = m.extend(!0, {},
			m.ajaxSettings, n),
			a,
			f,
			l,
			c = n && n.context || u,
			h = u.type.toUpperCase();
			u.data && u.processData && typeof u.data != "string" && (u.data = m.param(u.data, u.traditional));
			if (u.dataType === "jsonp") {
				if (h === "GET") Ht.test(u.url) || (u.url += (Bt.test(u.url) ? "&": "?") + (u.jsonp || "callback") + "=?");
				else if (!u.data || !Ht.test(u.data)) u.data = (u.data ? u.data + "&": "") + (u.jsonp || "callback") + "=?";
				u.dataType = "json"
			}
			u.dataType === "json" && (u.data && Ht.test(u.data) || Ht.test(u.url)) && (a = u.jsonpCallback || "jsonp" + Mt++, u.data && (u.data = (u.data + "").replace(Ht, "=" + a + "$1")), u.url = u.url.replace(Ht, "=" + a + "$1"), u.dataType = "script", e[a] = e[a] ||
			function(n) {
				l = n,
				r(),
				i(),
				e[a] = t;
				try {
					delete e[a]
				} catch(s) {}
				v && v.removeChild(g)
			}),
			u.dataType === "script" && u.cache === null && (u.cache = !1);
			if (u.cache === !1 && h === "GET") {
				var p = s(),
				d = u.url.replace(jt, "$1_=" + p + "$2");
				u.url = d + (d === u.url ? (Bt.test(u.url) ? "&": "?") + "_=" + p: "")
			}
			u.data && h === "GET" && (u.url += (Bt.test(u.url) ? "&": "?") + u.data),
			u.global && !(m.active++) && m.event.trigger("ajaxStart"),
			p = (p = Ft.exec(u.url)) && (p[1] && p[1] !== location.protocol || p[2] !== location.host);
			if (u.dataType === "script" && h === "GET" && p) {
				var v = b.getElementsByTagName("head")[0] || b.documentElement,
				g = b.createElement("script");
				g.src = u.url,
				u.scriptCharset && (g.charset = u.scriptCharset);
				if (!a) {
					var y = !1;
					g.onload = g.onreadystatechange = function() { ! y && (!this.readyState || this.readyState === "loaded" || this.readyState === "complete") && (y = !0, r(), i(), g.onload = g.onreadystatechange = null, v && g.parentNode && v.removeChild(g))
					}
				}
				return v.insertBefore(g, v.firstChild),
				t
			}
			var w = !1,
			E = u.xhr();
			if (E) {
				u.username ? E.open(h, u.url, u.async, u.username, u.password) : E.open(h, u.url, u.async);
				try { (u.data || n && n.contentType) && E.setRequestHeader("Content-Type", u.contentType),
					u.ifModified && (m.lastModified[u.url] && E.setRequestHeader("If-Modified-Since", m.lastModified[u.url]), m.etag[u.url] && E.setRequestHeader("If-None-Match", m.etag[u.url])),
					p || E.setRequestHeader("X-Requested-With", "XMLHttpRequest"),
					E.setRequestHeader("Accept", u.dataType && u.accepts[u.dataType] ? u.accepts[u.dataType] + ", */*": u.accepts._default)
				} catch(S) {}
				if (u.beforeSend && u.beforeSend.call(c, E, u) === !1) return u.global && !--m.active && m.event.trigger("ajaxStop"),
				E.abort(),
				!1;
				u.global && o("ajaxSend", [E, u]);
				var x = E.onreadystatechange = function(e) {
					if (!E || E.readyState === 0 || e === "abort") w || i(),
					w = !0,
					E && (E.onreadystatechange = m.noop);
					else if (!w && E && (E.readyState === 4 || e === "timeout")) {
						w = !0,
						E.onreadystatechange = m.noop,
						f = e === "timeout" ? "timeout": m.httpSuccess(E) ? u.ifModified && m.httpNotModified(E, u.url) ? "notmodified": "success": "error";
						var t;
						if (f === "success") try {
							l = m.httpData(E, u.dataType, u)
						} catch(n) {
							f = "parsererror",
							t = n
						}
						f === "success" || f === "notmodified" ? a || r() : m.handleError(u, E, f, t),
						i(),
						e === "timeout" && E.abort(),
						u.async && (E = null)
					}
				};
				try {
					var T = E.abort;
					E.abort = function() {
						E && T.call(E),
						x("abort")
					}
				} catch(N) {}
				u.async && u.timeout > 0 && setTimeout(function() {
					E && !w && x("timeout")
				},
				u.timeout);
				try {
					E.send(h === "POST" || h === "PUT" || h === "DELETE" ? u.data: null)
				} catch(C) {
					m.handleError(u, E, null, C),
					i()
				}
				return u.async || x(),
				E
			}
		},
		handleError: function(e, t, n, r) {
			e.error && e.error.call(e.context || e, t, n, r),
			e.global && (e.context ? m(e.context) : m.event).trigger("ajaxError", [t, e, r])
		},
		active: 0,
		httpSuccess: function(e) {
			try {
				return ! e.status && location.protocol === "file:" || e.status >= 200 && e.status < 300 || e.status === 304 || e.status === 1223 || e.status === 0
			} catch(t) {}
			return ! 1
		},
		httpNotModified: function(e, t) {
			var n = e.getResponseHeader("Last-Modified"),
			r = e.getResponseHeader("Etag");
			return n && (m.lastModified[t] = n),
			r && (m.etag[t] = r),
			e.status === 304 || e.status === 0
		},
		httpData: function(e, t, n) {
			var r = e.getResponseHeader("content-type") || "",
			i = t === "xml" || !t && r.indexOf("xml") >= 0;
			return e = i ? e.responseXML: e.responseText,
			i && e.documentElement.nodeName === "parsererror" && m.error("parsererror"),
			n && n.dataFilter && (e = n.dataFilter(e, t)),
			typeof e == "string" && (t === "json" || !t && r.indexOf("json") >= 0 ? e = m.parseJSON(e) : (t === "script" || !t && r.indexOf("javascript") >= 0) && m.globalEval(e)),
			e
		},
		param: function(e, n) {
			function r(e, t) {
				m.isArray(t) ? m.each(t,
				function(t, s) {
					n || /\[\]$/.test(e) ? i(e, s) : r(e + "[" + (typeof s == "object" || m.isArray(s) ? t: "") + "]", s)
				}) : !n && t != null && typeof t == "object" ? m.each(t,
				function(t, n) {
					r(e + "[" + t + "]", n)
				}) : i(e, t)
			}
			function i(e, t) {
				t = m.isFunction(t) ? t() : t,
				s[s.length] = encodeURIComponent(e) + "=" + encodeURIComponent(t)
			}
			var s = [];
			n === t && (n = m.ajaxSettings.traditional);
			if (m.isArray(e) || e.jquery) m.each(e,
			function() {
				i(this.name, this.value)
			});
			else for (var o in e) r(o, e[o]);
			return s.join("&").replace(It, "+")
		}
	});
	var Rt = {},
	Ut = /toggle|show|hide/,
	zt = /^([+-]=)?([\d+-.]+)(.*)$/,
	Wt, Xt = [["height", "marginTop", "marginBottom", "paddingTop", "paddingBottom"], ["width", "marginLeft", "marginRight", "paddingLeft", "paddingRight"], ["opacity"]];
	m.fn.extend({
		show: function(e, t) {
			if (e || e === 0) return this.animate(d("show", 3), e, t);
			e = 0;
			for (t = this.length; e < t; e++) {
				var n = m.data(this[e], "olddisplay");
				this[e].style.display = n || "";
				if (m.css(this[e], "display") === "none") {
					n = this[e].nodeName;
					var r;
					if (Rt[n]) r = Rt[n];
					else {
						var i = m("<" + n + " />").appendTo("body");
						r = i.css("display"),
						r === "none" && (r = "block"),
						i.remove(),
						Rt[n] = r
					}
					m.data(this[e], "olddisplay", r)
				}
			}
			e = 0;
			for (t = this.length; e < t; e++) this[e].style.display = m.data(this[e], "olddisplay") || "";
			return this
		},
		hide: function(e, t) {
			if (e || e === 0) return this.animate(d("hide", 3), e, t);
			e = 0;
			for (t = this.length; e < t; e++) {
				var n = m.data(this[e], "olddisplay"); ! n && n !== "none" && m.data(this[e], "olddisplay", m.css(this[e], "display"))
			}
			e = 0;
			for (t = this.length; e < t; e++) this[e].style.display = "none";
			return this
		},
		_toggle: m.fn.toggle,
		toggle: function(e, t) {
			var n = typeof e == "boolean";
			return m.isFunction(e) && m.isFunction(t) ? this._toggle.apply(this, arguments) : e == null || n ? this.each(function() {
				var t = n ? e: m(this).is(":hidden");
				m(this)[t ? "show": "hide"]()
			}) : this.animate(d("toggle", 3), e, t),
			this
		},
		fadeTo: function(e, t, n) {
			return this.filter(":hidden").css("opacity", 0).show().end().animate({
				opacity: t
			},
			e, n)
		},
		animate: function(e, t, n, r) {
			var i = m.speed(t, n, r);
			return m.isEmptyObject(e) ? this.each(i.complete) : this[i.queue === !1 ? "each": "queue"](function() {
				var t = m.extend({},
				i),
				n,
				r = this.nodeType === 1 && m(this).is(":hidden"),
				s = this;
				for (n in e) {
					var o = n.replace(Et, Ot);
					n !== o && (e[o] = e[n], delete e[n], n = o);
					if (e[n] === "hide" && r || e[n] === "show" && !r) return t.complete.call(this); (n === "height" || n === "width") && this.style && (t.display = m.css(this, "display"), t.overflow = this.style.overflow),
					m.isArray(e[n]) && ((t.specialEasing = t.specialEasing || {})[n] = e[n][1], e[n] = e[n][0])
				}
				return t.overflow != null && (this.style.overflow = "hidden"),
				t.curAnim = m.extend({},
				e),
				m.each(e,
				function(n, i) {
					var o = new m.fx(s, t, n);
					if (Ut.test(i)) o[i === "toggle" ? r ? "show": "hide": i](e);
					else {
						var u = zt.exec(i),
						a = o.cur(!0) || 0;
						if (u) {
							i = parseFloat(u[2]);
							var f = u[3] || "px";
							f !== "px" && (s.style[n] = (i || 1) + f, a = (i || 1) / o.cur(!0) * a, s.style[n] = a + f),
							u[1] && (i = (u[1] === "-=" ? -1 : 1) * i + a),
							o.custom(a, i, f)
						} else o.custom(a, i, "")
					}
				}),
				!0
			})
		},
		stop: function(e, t) {
			var n = m.timers;
			return e && this.queue([]),
			this.each(function() {
				for (var e = n.length - 1; e >= 0; e--) n[e].elem === this && (t && n[e](!0), n.splice(e, 1))
			}),
			t || this.dequeue(),
			this
		}
	}),
	m.each({
		slideDown: d("show", 1),
		slideUp: d("hide", 1),
		slideToggle: d("toggle", 1),
		fadeIn: {
			opacity: "show"
		},
		fadeOut: {
			opacity: "hide"
		}
	},
	function(e, t) {
		m.fn[e] = function(e, n) {
			return this.animate(t, e, n)
		}
	}),
	m.extend({
		speed: function(e, t, n) {
			var r = e && typeof e == "object" ? e: {
				complete: n || !n && t || m.isFunction(e) && e,
				duration: e,
				easing: n && t || t && !m.isFunction(t) && t
			};
			return r.duration = m.fx.off ? 0 : typeof r.duration == "number" ? r.duration: m.fx.speeds[r.duration] || m.fx.speeds._default,
			r.old = r.complete,
			r.complete = function() {
				r.queue !== !1 && m(this).dequeue(),
				m.isFunction(r.old) && r.old.call(this)
			},
			r
		},
		easing: {
			linear: function(e, t, n, r) {
				return n + r * e
			},
			swing: function(e, t, n, r) {
				return ( - Math.cos(e * Math.PI) / 2 + .5) * r + n
			}
		},
		timers: [],
		fx: function(e, t, n) {
			this.options = t,
			this.elem = e,
			this.prop = n,
			t.orig || (t.orig = {})
		}
	}),
	m.fx.prototype = {
		update: function() {
			this.options.step && this.options.step.call(this.elem, this.now, this),
			(m.fx.step[this.prop] || m.fx.step._default)(this),
			(this.prop === "height" || this.prop === "width") && this.elem.style && (this.elem.style.display = "block")
		},
		cur: function(e) {
			return this.elem[this.prop] == null || !!this.elem.style && this.elem.style[this.prop] != null ? (e = parseFloat(m.css(this.elem, this.prop, e))) && e > -1e4 ? e: parseFloat(m.curCSS(this.elem, this.prop)) || 0 : this.elem[this.prop]
		},
		custom: function(e, t, n) {
			function r(e) {
				return i.step(e)
			}
			this.startTime = s(),
			this.start = e,
			this.end = t,
			this.unit = n || this.unit || "px",
			this.now = this.start,
			this.pos = this.state = 0;
			var i = this;
			r.elem = this.elem,
			r() && m.timers.push(r) && !Wt && (Wt = setInterval(m.fx.tick, 13))
		},
		show: function() {
			this.options.orig[this.prop] = m.style(this.elem, this.prop),
			this.options.show = !0,
			this.custom(this.prop === "width" || this.prop === "height" ? 1 : 0, this.cur()),
			m(this.elem).show()
		},
		hide: function() {
			this.options.orig[this.prop] = m.style(this.elem, this.prop),
			this.options.hide = !0,
			this.custom(this.cur(), 0)
		},
		step: function(e) {
			var t = s(),
			n = !0;
			if (e || t >= this.options.duration + this.startTime) {
				this.now = this.end,
				this.pos = this.state = 1,
				this.update(),
				this.options.curAnim[this.prop] = !0;
				for (var r in this.options.curAnim) this.options.curAnim[r] !== !0 && (n = !1);
				if (n) {
					this.options.display != null && (this.elem.style.overflow = this.options.overflow, e = m.data(this.elem, "olddisplay"), this.elem.style.display = e ? e: this.options.display, m.css(this.elem, "display") === "none" && (this.elem.style.display = "block")),
					this.options.hide && m(this.elem).hide();
					if (this.options.hide || this.options.show) for (var i in this.options.curAnim) m.style(this.elem, i, this.options.orig[i]);
					this.options.complete.call(this.elem)
				}
				return ! 1
			}
			return i = t - this.startTime,
			this.state = i / this.options.duration,
			e = this.options.easing || (m.easing.swing ? "swing": "linear"),
			this.pos = m.easing[this.options.specialEasing && this.options.specialEasing[this.prop] || e](this.state, i, 0, 1, this.options.duration),
			this.now = this.start + (this.end - this.start) * this.pos,
			this.update(),
			!0
		}
	},
	m.extend(m.fx, {
		tick: function() {
			for (var e = m.timers,
			t = 0; t < e.length; t++) e[t]() || e.splice(t--, 1);
			e.length || m.fx.stop()
		},
		stop: function() {
			clearInterval(Wt),
			Wt = null
		},
		speeds: {
			slow: 600,
			fast: 200,
			_default: 400
		},
		step: {
			opacity: function(e) {
				m.style(e.elem, "opacity", e.now)
			},
			_default: function(e) {
				e.elem.style && e.elem.style[e.prop] != null ? e.elem.style[e.prop] = (e.prop === "width" || e.prop === "height" ? Math.max(0, e.now) : e.now) + e.unit: e.elem[e.prop] = e.now
			}
		}
	}),
	m.expr && m.expr.filters && (m.expr.filters.animated = function(e) {
		return m.grep(m.timers,
		function(t) {
			return e === t.elem
		}).length
	}),
	m.fn.offset = "getBoundingClientRect" in b.documentElement ?
	function(e) {
		var t = this[0];
		if (e) return this.each(function(t) {
			m.offset.setOffset(this, e, t)
		});
		if (!t || !t.ownerDocument) return null;
		if (t === t.ownerDocument.body) return m.offset.bodyOffset(t);
		var n = t.getBoundingClientRect(),
		r = t.ownerDocument;
		return t = r.body,
		r = r.documentElement,
		{
			top: n.top + (self.pageYOffset || m.support.boxModel && r.scrollTop || t.scrollTop) - (r.clientTop || t.clientTop || 0),
			left: n.left + (self.pageXOffset || m.support.boxModel && r.scrollLeft || t.scrollLeft) - (r.clientLeft || t.clientLeft || 0)
		}
	}: function(e) {
		var t = this[0];
		if (e) return this.each(function(t) {
			m.offset.setOffset(this, e, t)
		});
		if (!t || !t.ownerDocument) return null;
		if (t === t.ownerDocument.body) return m.offset.bodyOffset(t);
		m.offset.initialize();
		var n = t.offsetParent,
		r = t,
		i = t.ownerDocument,
		s, o = i.documentElement,
		u = i.body;
		r = (i = i.defaultView) ? i.getComputedStyle(t, null) : t.currentStyle;
		for (var a = t.offsetTop,
		f = t.offsetLeft; (t = t.parentNode) && t !== u && t !== o;) {
			if (m.offset.supportsFixedPosition && r.position === "fixed") break;
			s = i ? i.getComputedStyle(t, null) : t.currentStyle,
			a -= t.scrollTop,
			f -= t.scrollLeft,
			t === n && (a += t.offsetTop, f += t.offsetLeft, m.offset.doesNotAddBorder && (!m.offset.doesAddBorderForTableAndCells || !/^t(able|d|h)$/i.test(t.nodeName)) && (a += parseFloat(s.borderTopWidth) || 0, f += parseFloat(s.borderLeftWidth) || 0), r = n, n = t.offsetParent),
			m.offset.subtractsBorderForOverflowNotVisible && s.overflow !== "visible" && (a += parseFloat(s.borderTopWidth) || 0, f += parseFloat(s.borderLeftWidth) || 0),
			r = s
		}
		if (r.position === "relative" || r.position === "static") a += u.offsetTop,
		f += u.offsetLeft;
		return m.offset.supportsFixedPosition && r.position === "fixed" && (a += Math.max(o.scrollTop, u.scrollTop), f += Math.max(o.scrollLeft, u.scrollLeft)),
		{
			top: a,
			left: f
		}
	},
	m.offset = {
		initialize: function() {
			var e = b.body,
			t = b.createElement("div"),
			n,
			r,
			i,
			s = parseFloat(m.curCSS(e, "marginTop", !0)) || 0;
			m.extend(t.style, {
				position: "absolute",
				top: 0,
				left: 0,
				margin: 0,
				border: 0,
				width: "1px",
				height: "1px",
				visibility: "hidden"
			}),
			t.innerHTML = "<div style='position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;'><div></div></div><table style='position:absolute;top:0;left:0;margin:0;border:5px solid #000;padding:0;width:1px;height:1px;' cellpadding='0' cellspacing='0'><tr><td></td></tr></table>",
			e.insertBefore(t, e.firstChild),
			n = t.firstChild,
			r = n.firstChild,
			i = n.nextSibling.firstChild.firstChild,
			this.doesNotAddBorder = r.offsetTop !== 5,
			this.doesAddBorderForTableAndCells = i.offsetTop === 5,
			r.style.position = "fixed",
			r.style.top = "20px",
			this.supportsFixedPosition = r.offsetTop === 20 || r.offsetTop === 15,
			r.style.position = r.style.top = "",
			n.style.overflow = "hidden",
			n.style.position = "relative",
			this.subtractsBorderForOverflowNotVisible = r.offsetTop === -5,
			this.doesNotIncludeMarginInBodyOffset = e.offsetTop !== s,
			e.removeChild(t),
			m.offset.initialize = m.noop
		},
		bodyOffset: function(e) {
			var t = e.offsetTop,
			n = e.offsetLeft;
			return m.offset.initialize(),
			m.offset.doesNotIncludeMarginInBodyOffset && (t += parseFloat(m.curCSS(e, "marginTop", !0)) || 0, n += parseFloat(m.curCSS(e, "marginLeft", !0)) || 0),
			{
				top: t,
				left: n
			}
		},
		setOffset: function(e, t, n) { / static / .test(m.curCSS(e, "position")) && (e.style.position = "relative");
			var r = m(e),
			i = r.offset(),
			s = parseInt(m.curCSS(e, "top", !0), 10) || 0,
			o = parseInt(m.curCSS(e, "left", !0), 10) || 0;
			m.isFunction(t) && (t = t.call(e, n, i)),
			n = {
				top: t.top - i.top + s,
				left: t.left - i.left + o
			},
			"using" in t ? t.using.call(e, n) : r.css(n)
		}
	},
	m.fn.extend({
		position: function() {
			if (!this[0]) return null;
			var e = this[0],
			t = this.offsetParent(),
			n = this.offset(),
			r = /^body|html$/i.test(t[0].nodeName) ? {
				top: 0,
				left: 0
			}: t.offset();
			return n.top -= parseFloat(m.curCSS(e, "marginTop", !0)) || 0,
			n.left -= parseFloat(m.curCSS(e, "marginLeft", !0)) || 0,
			r.top += parseFloat(m.curCSS(t[0], "borderTopWidth", !0)) || 0,
			r.left += parseFloat(m.curCSS(t[0], "borderLeftWidth", !0)) || 0,
			{
				top: n.top - r.top,
				left: n.left - r.left
			}
		},
		offsetParent: function() {
			return this.map(function() {
				for (var e = this.offsetParent || b.body; e && !/^body|html$/i.test(e.nodeName) && m.css(e, "position") === "static";) e = e.offsetParent;
				return e
			})
		}
	}),
	m.each(["Left", "Top"],
	function(e, n) {
		var r = "scroll" + n;
		m.fn[r] = function(n) {
			var i = this[0],
			s;
			return i ? n !== t ? this.each(function() { (s = v(this)) ? s.scrollTo(e ? m(s).scrollLeft() : n, e ? n: m(s).scrollTop()) : this[r] = n
			}) : (s = v(i)) ? "pageXOffset" in s ? s[e ? "pageYOffset": "pageXOffset"] : m.support.boxModel && s.document.documentElement[r] || s.document.body[r] : i[r] : null
		}
	}),
	m.each(["Height", "Width"],
	function(e, n) {
		var r = n.toLowerCase();
		m.fn["inner" + n] = function() {
			return this[0] ? m.css(this[0], r, !1, "padding") : null
		},
		m.fn["outer" + n] = function(e) {
			return this[0] ? m.css(this[0], r, !1, e ? "margin": "border") : null
		},
		m.fn[r] = function(e) {
			var i = this[0];
			return i ? m.isFunction(e) ? this.each(function(t) {
				var n = m(this);
				n[r](e.call(this, t, n[r]()))
			}) : "scrollTo" in i && i.document ? i.document.compatMode === "CSS1Compat" && i.document.documentElement["client" + n] || i.document.body["client" + n] : i.nodeType === 9 ? Math.max(i.documentElement["client" + n], i.body["scroll" + n], i.documentElement["scroll" + n], i.body["offset" + n], i.documentElement["offset" + n]) : e === t ? m.css(i, r) : this.css(r, typeof e == "string" ? e: e + "px") : e == null ? null: this
		}
	}),
	e.jQuery = e.$ = m
})(window);;
var swfobject = function() {
	function E() {
		p.readyState == "complete" && (p.parentNode.removeChild(p), S())
	}
	function S() {
		if (g) return;
		if (b.ie && b.win) {
			var e = H("span");
			try {
				var t = u.getElementsByTagName("body")[0].appendChild(e);
				t.parentNode.removeChild(t)
			} catch(n) {
				return
			}
		}
		g = !0,
		d && (clearInterval(d), d = null);
		var r = f.length;
		for (var i = 0; i < r; i++) f[i]()
	}
	function x(e) {
		g ? e() : f[f.length] = e
	}
	function T(t) {
		if (typeof o.addEventListener != e) o.addEventListener("load", t, !1);
		else if (typeof u.addEventListener != e) u.addEventListener("load", t, !1);
		else if (typeof o.attachEvent != e) B(o, "onload", t);
		else if (typeof o.onload == "function") {
			var n = o.onload;
			o.onload = function() {
				n(),
				t()
			}
		} else o.onload = t
	}
	function N() {
		var e = l.length;
		for (var t = 0; t < e; t++) {
			var n = l[t].id;
			if (b.pv[0] > 0) {
				var r = P(n);
				r && (l[t].width = r.getAttribute("width") ? r.getAttribute("width") : "0", l[t].height = r.getAttribute("height") ? r.getAttribute("height") : "0", j(l[t].swfVersion) ? (b.webkit && b.webkit < 312 && C(r), I(n, !0)) : l[t].expressInstall && !y && j("6.0.65") && (b.win || b.mac) ? k(l[t]) : L(r))
			} else I(n, !0)
		}
	}
	function C(e) {
		var n = e.getElementsByTagName(t)[0];
		if (n) {
			var r = H("embed"),
			i = n.attributes;
			if (i) {
				var s = i.length;
				for (var o = 0; o < s; o++) i[o].nodeName == "DATA" ? r.setAttribute("src", i[o].nodeValue) : r.setAttribute(i[o].nodeName, i[o].nodeValue)
			}
			var u = n.childNodes;
			if (u) {
				var a = u.length;
				for (var f = 0; f < a; f++) u[f].nodeType == 1 && u[f].nodeName == "PARAM" && r.setAttribute(u[f].getAttribute("name"), u[f].getAttribute("value"))
			}
			e.parentNode.replaceChild(r, e)
		}
	}
	function k(e) {
		y = !0;
		var t = P(e.id);
		if (t) {
			if (e.altContentId) {
				var n = P(e.altContentId);
				n && (v = n, m = e.altContentId)
			} else v = A(t); ! /%$/.test(e.width) && parseInt(e.width, 10) < 310 && (e.width = "310"),
			!/%$/.test(e.height) && parseInt(e.height, 10) < 137 && (e.height = "137"),
			u.title = u.title.slice(0, 47) + " - Flash Player Installation";
			var r = b.ie && b.win ? "ActiveX": "PlugIn",
			i = u.title,
			a = "MMredirectURL=" + o.location + "&MMplayerType=" + r + "&MMdoctitle=" + i,
			f = e.id;
			if (b.ie && b.win && t.readyState != 4) {
				var l = H("div");
				f += "SWFObjectNew",
				l.setAttribute("id", f),
				t.parentNode.insertBefore(l, t),
				t.style.display = "none";
				var c = function() {
					t.parentNode.removeChild(t)
				};
				B(o, "onload", c)
			}
			O({
				data: e.expressInstall,
				id: s,
				width: e.width,
				height: e.height
			},
			{
				flashvars: a
			},
			f)
		}
	}
	function L(e) {
		if (b.ie && b.win && e.readyState != 4) {
			var t = H("div");
			e.parentNode.insertBefore(t, e),
			t.parentNode.replaceChild(A(e), t),
			e.style.display = "none";
			var n = function() {
				e.parentNode.removeChild(e)
			};
			B(o, "onload", n)
		} else e.parentNode.replaceChild(A(e), e)
	}
	function A(e) {
		var n = H("div");
		if (b.win && b.ie) n.innerHTML = e.innerHTML;
		else {
			var r = e.getElementsByTagName(t)[0];
			if (r) {
				var i = r.childNodes;
				if (i) {
					var s = i.length;
					for (var o = 0; o < s; o++)(i[o].nodeType != 1 || i[o].nodeName != "PARAM") && i[o].nodeType != 8 && n.appendChild(i[o].cloneNode(!0))
				}
			}
		}
		return n
	}
	function O(n, r, s) {
		var o, u = P(s);
		if (u) {
			typeof n.id == e && (n.id = s);
			if (b.ie && b.win) {
				var a = "";
				for (var f in n) n[f] != Object.prototype[f] && (f.toLowerCase() == "data" ? r.movie = n[f] : f.toLowerCase() == "styleclass" ? a += ' class="' + n[f] + '"': f.toLowerCase() != "classid" && (a += " " + f + '="' + n[f] + '"'));
				var l = "";
				for (var h in r) r[h] != Object.prototype[h] && (l += '<param name="' + h + '" value="' + r[h] + '" />');
				u.outerHTML = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"' + a + ">" + l + "</object>",
				c[c.length] = n.id,
				o = P(n.id)
			} else if (b.webkit && b.webkit < 312) {
				var p = H("embed");
				p.setAttribute("type", i);
				for (var d in n) n[d] != Object.prototype[d] && (d.toLowerCase() == "data" ? p.setAttribute("src", n[d]) : d.toLowerCase() == "styleclass" ? p.setAttribute("class", n[d]) : d.toLowerCase() != "classid" && p.setAttribute(d, n[d]));
				for (var v in r) r[v] != Object.prototype[v] && v.toLowerCase() != "movie" && p.setAttribute(v, r[v]);
				u.parentNode.replaceChild(p, u),
				o = p
			} else {
				var m = H(t);
				m.setAttribute("type", i);
				for (var g in n) n[g] != Object.prototype[g] && (g.toLowerCase() == "styleclass" ? m.setAttribute("class", n[g]) : g.toLowerCase() != "classid" && m.setAttribute(g, n[g]));
				for (var y in r) r[y] != Object.prototype[y] && y.toLowerCase() != "movie" && M(m, y, r[y]);
				u.parentNode.replaceChild(m, u),
				o = m
			}
		}
		return o
	}
	function M(e, t, n) {
		var r = H("param");
		r.setAttribute("name", t),
		r.setAttribute("value", n),
		e.appendChild(r)
	}
	function _(e) {
		var t = P(e);
		t && (t.nodeName == "OBJECT" || t.nodeName == "EMBED") && (b.ie && b.win ? t.readyState == 4 ? D(e) : o.attachEvent("onload",
		function() {
			D(e)
		}) : t.parentNode.removeChild(t))
	}
	function D(e) {
		var t = P(e);
		if (t) {
			for (var n in t) typeof t[n] == "function" && (t[n] = null);
			t.parentNode.removeChild(t)
		}
	}
	function P(e) {
		var t = null;
		try {
			t = u.getElementById(e)
		} catch(n) {}
		return t
	}
	function H(e) {
		return u.createElement(e)
	}
	function B(e, t, n) {
		e.attachEvent(t, n),
		h[h.length] = [e, t, n]
	}
	function j(e) {
		var t = b.pv,
		n = e.split(".");
		return n[0] = parseInt(n[0], 10),
		n[1] = parseInt(n[1], 10) || 0,
		n[2] = parseInt(n[2], 10) || 0,
		t[0] > n[0] || t[0] == n[0] && t[1] > n[1] || t[0] == n[0] && t[1] == n[1] && t[2] >= n[2] ? !0 : !1
	}
	function F(n, r) {
		if (b.ie && b.mac) return;
		var i = u.getElementsByTagName("head")[0],
		s = H("style");
		s.setAttribute("type", "text/css"),
		s.setAttribute("media", "screen"),
		(!b.ie || !b.win) && typeof u.createTextNode != e && s.appendChild(u.createTextNode(n + " {" + r + "}")),
		i.appendChild(s);
		if (b.ie && b.win && typeof u.styleSheets != e && u.styleSheets.length > 0) {
			var o = u.styleSheets[u.styleSheets.length - 1];
			typeof o.addRule == t && o.addRule(n, r)
		}
	}
	function I(e, t) {
		var n = t ? "visible": "hidden";
		g && P(e) ? P(e).style.visibility = n: F("#" + e, "visibility:" + n)
	}
	function q(e) {
		var t = /[\\\"<>\.;]/,
		n = t.exec(e) != null;
		return n ? encodeURIComponent(e) : e
	}
	var e = "undefined",
	t = "object",
	n = "Shockwave Flash",
	r = "ShockwaveFlash.ShockwaveFlash",
	i = "application/x-shockwave-flash",
	s = "SWFObjectExprInst",
	o = window,
	u = document,
	a = navigator,
	f = [],
	l = [],
	c = [],
	h = [],
	p,
	d = null,
	v = null,
	m = null,
	g = !1,
	y = !1,
	b = function() {
		var s = typeof u.getElementById != e && typeof u.getElementsByTagName != e && typeof u.createElement != e,
		f = [0, 0, 0],
		l = null;
		if (typeof a.plugins != e && typeof a.plugins[n] == t) l = a.plugins[n].description,
		l && (typeof a.mimeTypes == e || !a.mimeTypes[i] || !!a.mimeTypes[i].enabledPlugin) && (l = l.replace(/^.*\s+(\S+\s+\S+$)/, "$1"), f[0] = parseInt(l.replace(/^(.*)\..*$/, "$1"), 10), f[1] = parseInt(l.replace(/^.*\.(.*)\s.*$/, "$1"), 10), f[2] = /r/.test(l) ? parseInt(l.replace(/^.*r(.*)$/, "$1"), 10) : 0);
		else if (typeof o.ActiveXObject != e) {
			var c = null,
			h = !1;
			try {
				c = new ActiveXObject(r + ".7")
			} catch(p) {
				try {
					c = new ActiveXObject(r + ".6"),
					f = [6, 0, 21],
					c.AllowScriptAccess = "always"
				} catch(p) {
					f[0] == 6 && (h = !0)
				}
				if (!h) try {
					c = new ActiveXObject(r)
				} catch(p) {}
			}
			if (!h && c) try {
				l = c.GetVariable("$version"),
				l && (l = l.split(" ")[1].split(","), f = [parseInt(l[0], 10), parseInt(l[1], 10), parseInt(l[2], 10)])
			} catch(p) {}
		}
		var d = a.userAgent.toLowerCase(),
		v = a.platform.toLowerCase(),
		m = /webkit/.test(d) ? parseFloat(d.replace(/^.*webkit\/(\d+(\.\d+)?).*$/, "$1")) : !1,
		g = !1,
		y = v ? /win/.test(v) : /win/.test(d),
		b = v ? /mac/.test(v) : /mac/.test(d);
		return {
			w3cdom: s,
			pv: f,
			webkit: m,
			ie: g,
			win: y,
			mac: b
		}
	} (),
	w = function() {
		if (!b.w3cdom) return;
		x(N);
		if (b.ie && b.win) try {
			u.write("<script id=__ie_ondomload defer=true src=//:></script>"),
			p = P("__ie_ondomload"),
			p && B(p, "onreadystatechange", E)
		} catch(t) {}
		b.webkit && typeof u.readyState != e && (d = setInterval(function() { / loaded | complete / .test(u.readyState) && S()
		},
		10)),
		typeof u.addEventListener != e && u.addEventListener("DOMContentLoaded", S, null),
		T(S)
	} (),
	R = function() {
		b.ie && b.win && window.attachEvent("onunload",
		function() {
			var e = h.length;
			for (var t = 0; t < e; t++) h[t][0].detachEvent(h[t][1], h[t][2]);
			var n = c.length;
			for (var r = 0; r < n; r++) _(c[r]);
			for (var i in b) b[i] = null;
			b = null;
			for (var s in swfobject) swfobject[s] = null;
			swfobject = null
		})
	} ();
	return {
		registerObject: function(e, t, n) {
			if (!b.w3cdom || !e || !t) return;
			var r = {};
			r.id = e,
			r.swfVersion = t,
			r.expressInstall = n ? n: !1,
			l[l.length] = r,
			I(e, !1)
		},
		getObjectById: function(n) {
			var r = null;
			if (b.w3cdom) {
				var i = P(n);
				if (i) {
					var s = i.getElementsByTagName(t)[0]; ! s || s && typeof i.SetVariable != e ? r = i: typeof s.SetVariable != e && (r = s)
				}
			}
			return r
		},
		embedSWF: function(n, r, i, s, o, u, a, f, l) {
			if (!b.w3cdom || !n || !r || !i || !s || !o) return;
			i += "",
			s += "";
			if (j(o)) {
				I(r, !1);
				var c = {};
				if (l && typeof l === t) for (var h in l) l[h] != Object.prototype[h] && (c[h] = l[h]);
				c.data = n,
				c.width = i,
				c.height = s;
				var p = {};
				if (f && typeof f === t) for (var d in f) f[d] != Object.prototype[d] && (p[d] = f[d]);
				if (a && typeof a === t) for (var v in a) a[v] != Object.prototype[v] && (typeof p.flashvars != e ? p.flashvars += "&" + v + "=" + a[v] : p.flashvars = v + "=" + a[v]);
				x(function() {
					O(c, p, r),
					c.id == r && I(r, !0)
				})
			} else u && !y && j("6.0.65") && (b.win || b.mac) && (y = !0, I(r, !1), x(function() {
				var e = {};
				e.id = e.altContentId = r,
				e.width = i,
				e.height = s,
				e.expressInstall = u,
				k(e)
			}))
		},
		getFlashPlayerVersion: function() {
			return {
				major: b.pv[0],
				minor: b.pv[1],
				release: b.pv[2]
			}
		},
		hasFlashPlayerVersion: j,
		createSWF: function(e, t, n) {
			return b.w3cdom ? O(e, t, n) : undefined
		},
		removeSWF: function(e) {
			b.w3cdom && _(e)
		},
		createCSS: function(e, t) {
			b.w3cdom && F(e, t)
		},
		addDomLoadEvent: x,
		addLoadEvent: T,
		getQueryParamValue: function(e) {
			var t = u.location.search || u.location.hash;
			if (e == null) return q(t);
			if (t) {
				var n = t.substring(1).split("&");
				for (var r = 0; r < n.length; r++) if (n[r].substring(0, n[r].indexOf("=")) == e) return q(n[r].substring(n[r].indexOf("=") + 1))
			}
			return ""
		},
		expressInstallCallback: function() {
			if (y && v) {
				var e = P(s);
				e && (e.parentNode.replaceChild(v, e), m && (I(m, !0), b.ie && b.win && (v.style.display = "block")), v = null, m = null, y = !1)
			}
		}
	}
} ();;
$(function() {
	var minHeight = $(window).height() - 105;
	$('.full-height').css('min-height', minHeight);
});