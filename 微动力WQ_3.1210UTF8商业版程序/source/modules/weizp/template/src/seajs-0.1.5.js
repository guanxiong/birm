(function(aA, az) {
	function ay(e) {
		return function(f) {
			return Object.prototype.toString.call(f) === "[object " + e + "]"
		}
	}
	function ab() {
		return ax++
	}
	function aj(e, h) {
		var f;
		f = e.charAt(0);
		if (aa.test(e)) {
			f = e
		} else {
			if ("." === f) {
				f = (h ? h.match(an)[0] : aJ.cwd) + e;
				for (f = f.replace(o, "/"); f.match(ai);) {
					f = f.replace(ai, "/")
				}
			} else {
				f = "/" === f ? (f = aJ.cwd.match(l)) ? f[0] + e.substring(1) : e : aJ.base + e
			}
		}
		return f
	}
	function ah(h, s) {
		if (!h) {
			return ""
		}
		var k = h,
			r = aJ.alias,
			k = h = r && am(r[k]) ? r[k] : k,
			r = aJ.paths,
			n;
		if (r && (n = k.match(i)) && am(r[n[1]])) {
			k = r[n[1]] + n[2]
		}
		n = k;
		var q = aJ.vars;
		q && -1 < n.indexOf("{") && (n = n.replace(g, function(f, e) {
			return am(q[e]) ? q[e] : f
		}));
		k = n.length - 1;
		r = n.charAt(k);
		h = "#" === r ? n.substring(0, k) : ".js" === n.substring(k - 2) || 0 < n.indexOf("?") || ".css" === n.substring(k - 3) || "/" === r ? n : n + ".js";
		n = aj(h, s);
		var k = aJ.map,
			j = n;
		if (k) {
			for (var r = 0, p = k.length; r < p && !(j = k[r], j = aw(j) ? j(n) || n : n.replace(j[0], j[1]), j !== n); r++) {}
		}
		return j
	}
	function ag(e, k) {
		var f = e.sheet,
			j;
		if (af) {
			f && (j = !0)
		} else {
			if (f) {
				try {
					f.cssRules && (j = !0)
				} catch (h) {
					"NS_ERROR_DOM_SECURITY_ERR" === h.name && (j = !0)
				}
			}
		}
		setTimeout(function() {
			j ? k() : ag(e, k)
		}, 20)
	}
	function d() {
		if (av) {
			return av
		}
		if (au && "interactive" === au.readyState) {
			return au
		}
		for (var e = aB.getElementsByTagName("script"), h = e.length - 1; 0 <= h; h--) {
			var f = e[h];
			if ("interactive" === f.readyState) {
				return au = f
			}
		}
	}
	function aL(e, f) {
		this.uri = e;
		this.dependencies = f || [];
		this.exports = null;
		this.status = 0;
		this._waitings = {};
		this._remain = 0
	}
	if (!aA.seajs) {
		var aK = aA.seajs = {
			version: "2.1.1"
		},
			aJ = aK.data = {},
			c = ay("Object"),
			am = ay("String"),
			ar = Array.isArray || ay("Array"),
			aw = ay("Function"),
			ax = 0,
			aE = aJ.events = {};
		aK.on = function(e, f) {
			(aE[e] || (aE[e] = [])).push(f);
			return aK
		};
		aK.off = function(e, j) {
			if (!e && !j) {
				return aE = aJ.events = {}, aK
			}
			var f = aE[e];
			if (f) {
				if (j) {
					for (var h = f.length - 1; 0 <= h; h--) {
						f[h] === j && f.splice(h, 1)
					}
				} else {
					delete aE[e]
				}
			}
			return aK
		};
		var aG = aK.emit = function(e, j) {
				var f = aE[e],
					h;
				if (f) {
					for (f = f.slice(); h = f.shift();) {
						h(j)
					}
				}
				return aK
			},
			an = /[^?#]*\//,
			o = /\/\.\//g,
			ai = /\/[^/]+\/\.\.\//,
			i = /^([^/:]+)(\/.+)$/,
			g = /{([^{]+)}/g,
			aa = /^\/\/.|:\//,
			l = /^.*?\/\/.*?\//,
			aF = document,
			aD = location,
			aq = aD.href.match(an)[0],
			aH = aF.getElementsByTagName("script"),
			aH = aF.getElementById("seajsnode") || aH[aH.length - 1],
			aH = ((aH.hasAttribute ? aH.src : aH.getAttribute("src", 4)) || aq).match(an)[0],
			aB = aF.getElementsByTagName("head")[0] || aF.documentElement,
			ae = aB.getElementsByTagName("base")[0],
			ad = /\.css(?:\?|$)/i,
			b = /^(?:loaded|complete|undefined)$/,
			av, au, af = 536 > 1 * navigator.userAgent.replace(/.*AppleWebKit\/(\d+)\..*/, "$1"),
			a = /"(?:\\"|[^"])*"|'(?:\\'|[^'])*'|\/\*[\S\s]*?\*\/|\/(?:\\\/|[^\/\r\n])+\/(?=[^\/])|\/\/.*|\.\s*require|(?:^|[^$])\brequire\s*\(\s*(["'])(.+?)\1\s*\)/g,
			at = /\\\\/g,
			aC = aK.cache = {},
			ap, al = {},
			ak = {},
			ao = {},
			aI = aL.STATUS = {
				FETCHING: 1,
				SAVED: 2,
				LOADING: 3,
				LOADED: 4,
				EXECUTING: 5,
				EXECUTED: 6
			};
		aL.prototype.resolve = function() {
			for (var e = this.dependencies, j = [], f = 0, h = e.length; f < h; f++) {
				j[f] = aL.resolve(e[f], this.uri)
			}

			return j
		};
		aL.prototype.load = function() {
			if (!(this.status >= aI.LOADING)) {
				this.status = aI.LOADING;
				var e = this.resolve();
				aG("load", e);
				for (var p = this._remain = e.length, f, n = 0; n < p; n++) {
					f = aL.get(e[n]), f.status < aI.LOADED ? f._waitings[this.uri] = (f._waitings[this.uri] || 0) + 1 : this._remain--
				}
				if (0 === this._remain) {
					this.onload()
				} else {
					for (var k = {}, n = 0; n < p; n++) {
						f = aC[e[n]], f.status < aI.FETCHING ? f.fetch(k) : f.status === aI.SAVED && f.load()
					}
					for (var j in k) {
						if (k.hasOwnProperty(j)) {
							k[j]()
						}
					}
				}
			}
		};
		aL.prototype.onload = function() {
			this.status = aI.LOADED;
			this.callback && this.callback();
			var e = this._waitings,
				h, f;
			for (h in e) {
				if (e.hasOwnProperty(h) && (f = aC[h], f._remain -= e[h], 0 === f._remain)) {
					f.onload()
				}
			}
			delete this._waitings;
			delete this._remain
		};
		aL.prototype.fetch = function(e) {
			function p() {
				var r = j.requestUri,
					q = j.onRequest,
					v = j.charset,
					u = ad.test(r),
					t = aF.createElement(u ? "link" : "script");
				if (v && (v = aw(v) ? v(r) : v)) {
					t.charset = v
				}
				var s = t;
				u && (af || !("onload" in s)) ? setTimeout(function() {
					ag(s, q)
				}, 1) : s.onload = s.onerror = s.onreadystatechange = function() {
					b.test(s.readyState) && (s.onload = s.onerror = s.onreadystatechange = null, !u && !aJ.debug && aB.removeChild(s), s = null, q())
				};
				u ? (t.rel = "stylesheet", t.href = r) : (t.async = !0, t.src = r);
				av = t;
				ae ? aB.insertBefore(t, ae) : aB.appendChild(t);
				av = null
			}
			function h() {
				delete al[k];
				ak[k] = !0;
				ap && (aL.save(n, ap), ap = null);
				var q, f = ao[k];
				for (delete ao[k]; q = f.shift();) {
					q.load()
				}
			}
			var n = this.uri;
			this.status = aI.FETCHING;
			var j = {
				uri: n
			};
			aG("fetch", j);
			var k = j.requestUri || n;
			!k || ak[k] ? this.load() : al[k] ? ao[k].push(this) : (al[k] = !0, ao[k] = [this], aG("request", j = {
				uri: n,
				requestUri: k,
				onRequest: h,
				charset: aJ.charset
			}), j.requested || (e ? e[j.requestUri] = p : p()))
		};
		aL.prototype.exec = function() {
			function e(j) {
				return aL.get(e.resolve(j)).exec()
			}
			if (this.status >= aI.EXECUTING) {
				return this.exports
			}
			this.status = aI.EXECUTING;
			var h = this.uri;
			e.resolve = function(j) {
				return aL.resolve(j, h)
			};
			e.async = function(j, k) {
				aL.use(j, k, h + "_async_" + ax++);
				return e
			};
			var f = this.factory,
				f = aw(f) ? f(e, this.exports = {}, this) : f;
			f === az && (f = this.exports);
			null === f && !ad.test(h) && aG("error", this);
			delete this.factory;
			this.exports = f;
			this.status = aI.EXECUTED;
			aG("exec", this);
			return f
		};
		aL.resolve = function(e, h) {
			var f = {
				id: e,
				refUri: h
			};
			aG("resolve", f);
			return f.uri || ah(f.id, h)
		};
		aL.define = function(e, p, h) {
			var n = arguments.length;
			1 === n ? (h = e, e = az) : 2 === n && (h = p, ar(e) ? (p = e, e = az) : p = az);
			if (!ar(p) && aw(h)) {
				var j = [];
				h.toString().replace(at, "").replace(a, function(q, f, r) {
					r && j.push(r)
				});
				p = j
			}
			n = {
				id: e,
				uri: aL.resolve(e),
				deps: p,
				factory: h
			};
			if (!n.uri && aF.attachEvent) {
				var k = d();
				k && (n.uri = k.src)
			}
			aG("define", n);
			n.uri ? aL.save(n.uri, n) : ap = n
		};
		aL.save = function(e, h) {
			var f = aL.get(e);
			f.status < aI.SAVED && (f.id = h.id || e, f.dependencies = h.deps || [], f.factory = h.factory, f.status = aI.SAVED)
		};
		aL.get = function(e, f) {
			return aC[e] || (aC[e] = new aL(e, f))
		};
		aL.use = function(e, j, f) {
			var h = aL.get(f, ar(e) ? e : [e]);
			h.callback = function() {
				for (var n = [], k = h.resolve(), q = 0, p = k.length; q < p; q++) {
					n[q] = aC[k[q]].exec()
				}
				j && j.apply(aA, n);
				delete h.callback
			};
			h.load()
		};
		aL.preload = function(e) {
			var h = aJ.preload,
				f = h.length;
			f ? aL.use(h, function() {
				h.splice(0, f);
				aL.preload(e)
			}, aJ.cwd + "_preload_" + ax++) : e()
		};
		aK.use = function(e, f) {
			aL.preload(function() {
				aL.use(e, f, aJ.cwd + "_use_" + ax++)
			});
			return aK
		};
		aL.define.cmd = {};
		aA.define = aL.define;
		aK.Module = aL;
		aJ.fetchedList = ak;
		aJ.cid = ab;
		aK.resolve = ah;
		aK.require = function(e) {
			return (aC[aL.resolve(e)] || {}).exports
		};
		aJ.base = (aH.match(/^(.+?\/)(\?\?)?(seajs\/)+/) || ["", aH])[1];
		aJ.dir = aH;
		aJ.cwd = aq;
		aJ.charset = "utf-8";
		var aq = aJ,
			ac = [],
			aD = aD.search.replace(/(seajs-\w+)(&|$)/g, "$1=1$2"),
			aD = aD + (" " + aF.cookie);
		aD.replace(/(seajs-\w+)=1/g, function(e, f) {
			ac.push(f)
		});
		aq.preload = ac;
		aK.config = function(f) {
			for (var n in f) {
				var h = f[n],
					k = aJ[n];
				if (k && c(k)) {
					for (var j in h) {
						k[j] = h[j]
					}
				} else {
					ar(k) ? h = k.concat(h) : "base" === n && ("/" === h.slice(-1) || (h += "/"), h = aj(h)), aJ[n] = h
				}
			}
			aG("config", f);
			return aK
		}
	}
})(this);
if (typeof(ARS_TIME) == "undefined") {
	var ARS_TIME = "0"
}
function getParameter(b) {
	var c = new RegExp("(\\?|#|&)" + b + "=([^&#?]*)(&|#|\\?|$)"),
		a = location.href.match(c);
	return decodeURIComponent(!a ? "" : a[2])
}
var dir = getParameter("dir") || "";
var base = PathUtil.getCPath() + dir;
if (typeof(debug) == "undefined") {
	var debug = /^(ttest|ntouch|dtouch|ndtouch|touch)\.m\.wsq\.qq\.com$/i.test(location.host) ? 1 : 0
}
if (location.href.indexOf("debug") > 0) {
	debug = !debug
}
var map = [
	[/(\/manifest.js)$/i, "$1?v=" + ARS_TIME]
];
if (debug) {
	map.push([/(.+\.js)$/i, "$1?v=" + Math.random()])
}
seajs.config({
	base: './source/modules/weizp/template/src/',
	charset: "utf-8",
	timeout: 5 * 60 * 1000,
	debug: true,
	preload: ["seajs-combo", "seajs-localcache"],
	alias: {
		store: "lib/store.js",
		zepto: "lib/zepto.js",
		imageview: "lib/imageview.js"
	},
	map: map,
	//comboSyntax: ["", ","]
});
define("dependencies", function() {
	return {
		"module/navBar.js": ["module/followSite"],
		"module/mySiteIndex.js": ["module/followSite"],
		"module/newthread.js": ["module/emotion"],
		"module/siteCategory.js": ["module/gps", "module/followSite"],
		"module/portal.js": ["module/gps", "module/followSite"],
		"module/site.js": ["lib/scroll"],
		"module/viewthread.js": ["lib/scroll"],
		"module/emotion.js": ["lib/scroll"],
		"module/userThread.js": ["lib/scroll"]
	}
});
define("seajs-combo", ["dependencies"], function(a) {
	if (seajs.data.debug) {
		return
	}
	var i = seajs.Module;
	var n = i.STATUS.FETCHING;
	var o = a("dependencies");
	var h = seajs.data;
	h.comboHash = {};
	var e = h.comboHash;
	var q = ["??", ","];
	var d = 2000;
	var l;
	var g = /(^http:\/\/[^\/]+)([^\?]+)/;
	seajs.on("load", c);
	seajs.on("fetch", k);

	function j(s, t) {
		var u = o[t.replace(PathUtil.getCPath(), "")];
		if (u) {
			u.forEach(function(v) {
				v = PathUtil.getCPath() + v + ".js";
				!~s.indexOf(v) && s.push(v);
				j(s, v)
			})
		}
	}
	function c(t) {
		var s = t.length;
		if (h.comboSyntax) {
			q = h.comboSyntax
		}
		if (h.comboMaxLength) {
			d = h.comboMaxLength
		}
		l = h.comboExcludes;
		var x = [];
		for (var v = 0; v < s; v++) {
			var w = t[v];
			if (e[w]) {
				continue
			}
			var u = i.get(w);
			if (u.status < n && !b(w) && !f(w) && !~x.indexOf(w)) {
				x.push(w);
				j(x, w)
			}
		}
		if (x.length > 1) {
			p(x)
		}
	}
	function k(s) {
		if (e && e[s.uri]) {
			s.requestUri = e[s.uri]
		} else {
			s.requestUri = s.uri
		}
	}
	function p(z) {
		var w = g.exec(z[0]);
		var y = w[1];
		var t = y.length + 2;
		var s = [];
		for (var v = 0, x = z.length; v < x; v++) {
			var A = z[v];
			w = g.exec(A);
			var u = w[2];
			if (t + u.length + 1 > d) {
				r(y, s);
				s = [];
				t = y.length + 2
			} else {
				s.push(u);
				t += u.length + 1
			}
		}
		if (s.length != 0) {
			r(y, s)
		}
		return e
	}
	function r(t, w) {
		var v = t + q[0] + w.join(q[1]);
		for (var u = 0, s = w.length; u < s; u++) {
			e[t + w[u]] = v
		}
	}
	function b(s) {
		if (l) {
			return l.test ? l.test(s) : l(s)
		}
	}
	function f(v) {
		var u = h.comboSyntax || ["??", ","];
		var t = u[0];
		var s = u[1];
		return t && v.indexOf(t) > 0 || s && v.indexOf(s) > 0
	}
});
define("seajs-localcache", ["manifest"], function(j) {
	if (!window.localStorage || seajs.data.debug) {
		return
	}
	var b = seajs.Module,
		x = seajs.data,
		r = b.prototype.fetch,
		k = ["??", ","];
	var i = j("manifest");
	var w = {
		_maxRetry: 1,
		_retry: true,
		get: function(y, B) {
			var A;
			try {
				A = localStorage.getItem(y)
			} catch (z) {
				return undefined
			}
			if (A && A.charAt(0) == '"') {
				try {
					return JSON.parse(A)
				} catch (z) {
					return A
				}
			} else {
				if (B) {
					return JSON.parse(A)
				} else {
					return A
				}
			}
		},
		set: function(A, C, z) {
			z = typeof z == "undefined" ? this._retry : z;
			try {
				localStorage.setItem(A, C)
			} catch (B) {
				if (z) {
					var y = this._maxRetry;
					while (y > 0) {
						y--;
						this.removeAll();
						this.set(A, C, false)
					}
				}
			}
		},
		remove: function(y) {
			try {
				localStorage.removeItem(y)
			} catch (z) {}
		},
		removeAll: function() {
			var z = x.localcache && x.localcache.prefix || /^https?\:/;
			for (var y = localStorage.length - 1; y >= 0; y--) {
				var e = localStorage.key(y);
				if (!z.test(e)) {
					continue
				}
				if (!i[e]) {
					localStorage.removeItem(e)
				}
			}
		}
	};
	try {
		var q = w.get("manifest", true) || {}
	} catch (v) {}
	var o = function(e, y) {
			if (!y || !e || y == "undefined") {
				return false
			}
			var A;
			if (/\.js(?:\?|$)/i.test(e)) {
				A = e.substr(x.base.length);
				A = A.substr(0, A.length - 3);
				var z = y.match(/define\(/);
				if (z && z.length === 1 && y.match(new RegExp("define\\([\\\"|']" + A.replace(/\//g, "\\/") + "[\\\"|'],"))) {
					return true
				}
			} else {
				if (/\.css(?:\?|$)/i.test(e)) {
					return true
				}
			}
			return false
		};
	var n = function(e, A) {
			var y = new window.XMLHttpRequest;
			var z = setTimeout(function() {
				y.abort();
				A(null)
			}, 30000);
			y.open("GET", e, true);
			y.onreadystatechange = function() {
				if (y.readyState === 4) {
					clearTimeout(z);
					if (y.status === 200) {
						A(y.responseText)
					} else {
						A(null)
					}
				}
			};
			y.send(null)
		};
	var s = function(y, B, E) {
			if (B && /\S/.test(B)) {
				if (/\.css(?:\?|$)/i.test(y)) {
					var D = document,
						A = D.createElement("style");
					D.getElementsByTagName("head")[0].appendChild(A);
					if (A.styleSheet) {
						A.styleSheet.cssText = B
					} else {
						A.appendChild(D.createTextNode(B))
					}
				} else {
					try {
						var z = B + "\r\n//@ sourceURL=" + y;
						if (window.execScript) {
							window.execScript.call(window, z)
						} else {
							window["eval"].call(window, z)
						}
					} catch (C) {
						if (!C) {
							return true
						}
						if (C.message) {
							var F = C.message;
							if (F.indexOf && F.indexOf("Unexpected") >= 0) {
								if (!B || !B.length) {
									file = "empty file"
								} else {
									if (B.length > 100) {
										file = B.substring(0, 50) + "<--" + B.length + "-->" + B.substring(B.length - 50)
									} else {
										file = B
									}
								}
								if (E) {
									file += " ==>from:" + E
								}
								return false
							}
						}
						if (C.stack) {
							f("CacheErr(use)::" + C.stack)
						}
					}
				}
			}
			return true
		};
	var a = function(y) {
			var e = x.comboSyntax && x.comboSyntax[0] || "??";
			return y.indexOf(e) >= 0
		};
	var d = function(B) {
			var A = x.comboSyntax || k;
			var z = B.split(A[0]);
			if (z.length != 2) {
				return B
			}
			var D = z[0];
			var E = z[1].split(A[1]);
			var y = {};
			y.host = D;
			y.files = [];
			for (var C = 0, e = E.length; C < e; C++) {
				y.files.push(E[C])
			}
			return y
		};
	var l = function(e) {
			e = g(e);
			return e.match(/(\(function\(\)\{\s*var\s*mods\s*=\s*\[\].*?seajs\.version\);\s*)?define\([\s\S]*?\);*(?=;*[;\s]*\(function\(\)\{\s*var\s*mods\s*=\s*\[\]|\s*;*\s*;define\(|[;\s]*$)/g)
		};
	var g = function(e) {
			return e.replace(/try\{\(function\(_w\)\{_w\._javascript_file_map.*?catch\(ign\)\{\};?/mg, "").replace(/\/\*\s*\|xGv00\|.*?\*\//mg, "").replace(/^\s*\/\*[\s\S]*?\*\//mg, "").replace(/^\s*\/\/.*$/mg, "")
		};
	var p = "/fed_localstorage_hit2";
	var t = function(z, e, y) {};
	var f = function(e) {
			console.info(e)
		};
	var u = function(y) {
			var e = y.split("/scripts/");
			if (e.length == 2) {
				return e[1]
			}
			return y
		};
	var h = {};
	var c = function(e) {
			var y = h[e];
			delete h[e];
			while (m = y.shift()) {
				m.load()
			}
		};
	if (!i) {
		t(2, 8, 0);
		return
	}
	b.prototype.fetch = function(z) {
		var O = this;
		var A;
		try {
			seajs.emit("fetch", O);
			A = O.requestUri || O.uri;
			var C = u(A);
			var D = a(A);
			var H = A.lastIndexOf("."),
				B = A.substring(H);
			var J = function(e) {
					delete h[e];
					r.call(O, z);
					t(2, 9, 0)
				};
			if (!(B == ".js" || B == ".css")) {
				r.call(O, z);
				return
			}
			if (h[A]) {
				h[A].push(O);
				return
			}
			h[A] = [O];
			if (!D && i[C]) {
				var F = w.get(C);
				var y = o(A, F);
				if (i[C] == q[C] && y) {
					if (!s(A, F)) {
						J(A)
					} else {
						c(A);
						t(1, 1, 0)
					}
				} else {
					n(A + "?v=" + Math.random().toString(), function(e) {
						if (e && o(A, e)) {
							if (!s(A, e)) {
								J(A)
							} else {
								w.set(C, e);
								q[C] = i[C];
								w.set("manifest", JSON.stringify(q));
								c(A);
								t(2, 5, 0)
							}
						} else {
							J(A)
						}
					})
				}
			} else {
				if (D) {
					var P = d(A),
						E = false;
					for (var K = P.files.length - 1; K >= 0; K--) {
						var N = P.host + P.files[K];
						var G = u(N);
						var F = w.get(G);
						var y = o(N, F);
						if (i[G]) {
							E = true;
							if (i[G] == q[G] && y) {
								if (s(N, F)) {
									P.files.splice(K, 1);
									t(1, 2, 0)
								}
							}
						}
					}
					if (P.files.length == 0) {
						c(A);
						return
					}
					if (!E) {
						delete h[A];
						r.call(O, z);
						return
					}
					var L = x.comboSyntax || k,
						I = P.host + L[0] + P.files.join(L[1]);
					n(I, function(U) {
						if (!U) {
							J(A);
							return
						}
						var T = l(U);
						if (P.files.length == T.length) {
							for (var S = 0, e = P.files.length; S < e; S++) {
								var R = P.host + P.files[S];
								var Q = u(R);
								if (!s(R, T[S], I)) {
									J(A);
									return
								} else {
									q[Q] = i[Q];
									w.set(Q, T[S]);
									t(2, 5, 0)
								}
							}
							w.set("manifest", JSON.stringify(q));
							c(A)
						} else {
							J(A)
						}
					})
				} else {
					if (q[C]) {
						delete q[C];
						w.set("manifest", JSON.stringify(q));
						w.remove(C)
					}
					delete h[A];
					r.call(O, z);
					t(2, 6, 0);
					if (Math.random() < 0.05) {
						f("NoCache::" + A)
					}
				}
			}
		} catch (M) {
			if (A) {
				delete h[A]
			}
			r.call(O, z);
			t(2, 7, 0);
			if (M && M.stack) {
				f("CacheErr::" + M.stack)
			}
		}
	}
});
define("lib/store", function(b, a, c) {
	var d = {};
	d.isString = function(e) {
		return toString.call(e) === "[object String]"
	};
	d.forEach = Array.prototype.forEach ?
	function(e, f) {
		e.forEach(f)
	} : function(e, g) {
		for (var f = 0; f < e.length; f++) {
			g(e[f], f, e)
		}
	};
	d.map = Array.prototype.map ?
	function(e, f) {
		return e.map(f)
	} : function(e, g) {
		var f = [];
		d.forEach(e, function(k, j, h) {
			f.push(g(k, j, h))
		});
		return f
	};
	d.keys = Object.keys;
	if (!d.keys) {
		util.keys = function(g) {
			var e = [];
			for (var f in g) {
				if (g.hasOwnProperty(f)) {
					e.push(f)
				}
			}
			return e
		}
	}
	a.createStorage = function(g) {
		var e = {},
			j, f = g,
			i = window;
		var j = function() {
				if (!(f == "localStorage" || f == "sessionStorage")) {
					return false
				}
				var k = !! (f in i && i[f] && i[f].getItem);
				if (k && !i[f].length) {
					try {
						i[f].setItem("support", 1);
						i[f].removeItem("support")
					} catch (l) {
						k = false;
						if (window.ErrTrace && ErrTrace.triggerError) {
							ErrTrace.triggerError("QUOTA_EXCEEDED_ERR")
						}
					}
				}
				return k
			};
		e.isSupport = j();
		if (e.isSupport) {
			var h = i[f];
			e.get = function(k) {
				try {
					var n = h.getItem(k)
				} catch (l) {}
				if (!n || n == "undefined") {
					return undefined
				} else {
					try {
						var n = JSON.parse(n)
					} catch (l) {}
					return n
				}
			};
			e.set = function(k, n) {
				n = JSON.stringify(n);
				try {
					h.setItem(k, n)
				} catch (l) {
					if (window.ErrTrace && ErrTrace.triggerError) {
						ErrTrace.triggerError("QUOTA_EXCEEDED_ERR")
					}
				}
			};
			e.remove = function(k) {
				h.removeItem(k)
			};
			e.clear = function(k) {
				var k = arguments[0];
				if (arguments.length) {
					d.map(d.keys(h), function(l) {
						l.indexOf(k) == 0 && e.remove(l)
					});
					return
				}
				h.clear()
			};
			e.getAll = function(l) {
				var k = {},
					l = arguments[0];
				if (arguments.length) {
					d.map(d.keys(h), function(n) {
						n.indexOf(l) == 0 && (k[n] = e.get(n))
					})
				} else {
					d.map(d.keys(h), function(n) {
						k[n] = e.get(n)
					})
				}
				if (d.keys(k).length == 0) {
					return null
				} else {
					return k
				}
			}
		}
		return e
	}
});
g_ts.js_start = new Date;
//seajs.use(["lib/jquery.min", "lib/template.min", "lib/fastclick", "lib/global"].concat(g_module), function(a) {
//	g_ts.js_end = new Date
//});
seajs.use('lib/jquery.min');
seajs.use('lib/template.min');
seajs.use('lib/fastclick');
seajs.use('lib/global');
seajs.use('module/topBar');
seajs.use('module/newthread');