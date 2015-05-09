
if(!window.jq||typeof(jq)!=="function"){var jq=(function(window){var undefined,document=window.document,emptyArray=[],slice=emptyArray.slice,classCache={},eventHandlers=[],_eventID=1,jsonPHandlers=[],_jsonPID=1,fragementRE=/^\s*<(\w+)[^>]*>/,_attrCache={},_propCache={};function _insertFragments(jqm,container,insert){var frag=document.createDocumentFragment();if(insert){for(var j=jqm.length-1;j>=0;j--)
{frag.insertBefore(jqm[j],frag.firstChild);}
container.insertBefore(frag,container.firstChild);}
else{for(var j=0;j<jqm.length;j++)
frag.appendChild(jqm[j]);container.appendChild(frag);}
frag=null;}
function classRE(name){return name in classCache?classCache[name]:(classCache[name]=new RegExp('(^|\\s)'+name+'(\\s|$)'));}
function unique(arr){for(var i=0;i<arr.length;i++){if(arr.indexOf(arr[i])!=i){arr.splice(i,1);i--;}}
return arr;}
function siblings(nodes,element){var elems=[];if(nodes==undefined)
return elems;for(;nodes;nodes=nodes.nextSibling){if(nodes.nodeType==1&&nodes!==element){elems.push(nodes);}}
return elems;}
var $jqm=function(toSelect,what){this.length=0;if(!toSelect){return this;}else if(toSelect instanceof $jqm&&what==undefined){return toSelect;}else if($.isFunction(toSelect)){return $(document).ready(toSelect);}else if($.isArray(toSelect)&&toSelect.length!=undefined){for(var i=0;i<toSelect.length;i++)
this[this.length++]=toSelect[i];return this;}else if($.isObject(toSelect)&&$.isObject(what)){if(toSelect.length==undefined){if(toSelect.parentNode==what)
this[this.length++]=toSelect;}else{for(var i=0;i<toSelect.length;i++)
if(toSelect[i].parentNode==what)
this[this.length++]=toSelect[i];}
return this;}else if($.isObject(toSelect)&&what==undefined){this[this.length++]=toSelect;return this;}else if(what!==undefined){if(what instanceof $jqm){return what.find(toSelect);}}else{what=document;}
return this.selector(toSelect,what);};var $=function(selector,what){return new $jqm(selector,what);};function _selectorAll(selector,what){try{return what.querySelectorAll(selector);}catch(e){return[];}};function _selector(selector,what){selector=selector.trim();if(selector[0]==="#"&&selector.indexOf(".")==-1&&selector.indexOf(" ")===-1&&selector.indexOf(">")===-1){if(what==document)
_shimNodes(what.getElementById(selector.replace("#","")),this);else
_shimNodes(_selectorAll(selector,what),this);}else if(selector[0]==="<"&&selector[selector.length-1]===">")
{var tmp=document.createElement("div");tmp.innerHTML=selector.trim();_shimNodes(tmp.childNodes,this);}else{_shimNodes((_selectorAll(selector,what)),this);}
return this;}
function _shimNodes(nodes,obj){if(!nodes)
return;if(nodes.nodeType)
return obj[obj.length++]=nodes;for(var i=0,iz=nodes.length;i<iz;i++)
obj[obj.length++]=nodes[i];}
$.is$=function(obj){return obj instanceof $jqm;}
$.map=function(elements,callback){var value,values=[],i,key;if($.isArray(elements))
for(i=0;i<elements.length;i++){value=callback(elements[i],i);if(value!==undefined)
values.push(value);}
else if($.isObject(elements))
for(key in elements){if(!elements.hasOwnProperty(key))
continue;value=callback(elements[key],key);if(value!==undefined)
values.push(value);}
return $([values]);};$.each=function(elements,callback){var i,key;if($.isArray(elements))
for(i=0;i<elements.length;i++){if(callback(i,elements[i])===false)
return elements;}
else if($.isObject(elements))
for(key in elements){if(!elements.hasOwnProperty(key))
continue;if(callback(key,elements[key])===false)
return elements;}
return elements;};$.extend=function(target){if(target==undefined)
target=this;if(arguments.length===1){for(var key in target)
this[key]=target[key];return this;}else{slice.call(arguments,1).forEach(function(source){for(var key in source)
target[key]=source[key];});}
return target;};$.isArray=function(obj){return obj instanceof Array&&obj['push']!=undefined;};$.isFunction=function(obj){return typeof obj==="function"&&!(obj instanceof RegExp);};$.isObject=function(obj){return typeof obj==="object";};$.fn=$jqm.prototype={constructor:$jqm,forEach:emptyArray.forEach,reduce:emptyArray.reduce,push:emptyArray.push,indexOf:emptyArray.indexOf,concat:emptyArray.concat,selector:_selector,oldElement:undefined,slice:emptyArray.slice,setupOld:function(params){if(params==undefined)
return $();params.oldElement=this;return params;},map:function(fn){var value,values=[],i;for(i=0;i<this.length;i++){value=fn(i,this[i]);if(value!==undefined)
values.push(value);}
return $([values]);},each:function(callback){this.forEach(function(el,idx){callback.call(el,idx,el);});return this;},ready:function(callback){if(document.readyState==="complete"||document.readyState==="loaded"||(!$.os.ie&&document.readyState==="interactive"))
callback();else
document.addEventListener("DOMContentLoaded",callback,false);return this;},find:function(sel){if(this.length===0)
return this;var elems=[];var tmpElems;for(var i=0;i<this.length;i++){tmpElems=($(sel,this[i]));for(var j=0;j<tmpElems.length;j++){elems.push(tmpElems[j]);}}
return $(unique(elems));},html:function(html,cleanup){if(this.length===0)
return this;if(html===undefined)
return this[0].innerHTML;for(var i=0;i<this.length;i++){if(cleanup!==false)
$.cleanUpContent(this[i],false,true);this[i].innerHTML=html;}
return this;},text:function(text){if(this.length===0)
return this;if(text===undefined)
return this[0].textContent;for(var i=0;i<this.length;i++){this[i].textContent=text;}
return this;},css:function(attribute,value,obj){var toAct=obj!=undefined?obj:this[0];if(this.length===0)
return this;if(value==undefined&&typeof(attribute)==="string"){var styles=window.getComputedStyle(toAct);return toAct.style[attribute]?toAct.style[attribute]:window.getComputedStyle(toAct)[attribute];}
for(var i=0;i<this.length;i++){if($.isObject(attribute)){for(var j in attribute){this[i].style[j]=attribute[j];}}else{this[i].style[attribute]=value;}}
return this;},vendorCss:function(attribute,value,obj){return this.css($.feat.cssPrefix+attribute,value,obj);},empty:function(){for(var i=0;i<this.length;i++){$.cleanUpContent(this[i],false,true);this[i].innerHTML='';}
return this;},hide:function(){if(this.length===0)
return this;for(var i=0;i<this.length;i++){if(this.css("display",null,this[i])!="none"){this[i].setAttribute("jqmOldStyle",this.css("display",null,this[i]));this[i].style.display="none";}}
return this;},show:function(){if(this.length===0)
return this;for(var i=0;i<this.length;i++){if(this.css("display",null,this[i])=="none"){this[i].style.display=this[i].getAttribute("jqmOldStyle")?this[i].getAttribute("jqmOldStyle"):'block';this[i].removeAttribute("jqmOldStyle");}}
return this;},toggle:function(show){var show2=show===true?true:false;for(var i=0;i<this.length;i++){if(window.getComputedStyle(this[i])['display']!=="none"||(show!==undefined&&show2===false)){this[i].setAttribute("jqmOldStyle",this[i].style.display)
this[i].style.display="none";}else{this[i].style.display=this[i].getAttribute("jqmOldStyle")!=undefined?this[i].getAttribute("jqmOldStyle"):'block';this[i].removeAttribute("jqmOldStyle");}}
return this;},val:function(value){if(this.length===0)
return(value===undefined)?undefined:this;if(value==undefined)
return this[0].value;for(var i=0;i<this.length;i++){this[i].value=value;}
return this;},attr:function(attr,value){if(this.length===0)
return(value===undefined)?undefined:this;if(value===undefined&&!$.isObject(attr)){var val=(this[0].jqmCacheId&&_attrCache[this[0].jqmCacheId][attr])?(this[0].jqmCacheId&&_attrCache[this[0].jqmCacheId][attr]):this[0].getAttribute(attr);return val;}
for(var i=0;i<this.length;i++){if($.isObject(attr)){for(var key in attr){$(this[i]).attr(key,attr[key]);}}
else if($.isArray(value)||$.isObject(value)||$.isFunction(value))
{if(!this[i].jqmCacheId)
this[i].jqmCacheId=$.uuid();if(!_attrCache[this[i].jqmCacheId])
_attrCache[this[i].jqmCacheId]={}
_attrCache[this[i].jqmCacheId][attr]=value;}
else if(value==null&&value!==undefined)
{this[i].removeAttribute(attr);if(this[i].jqmCacheId&&_attrCache[this[i].jqmCacheId][attr])
delete _attrCache[this[i].jqmCacheId][attr];}
else{this[i].setAttribute(attr,value);}}
return this;},removeAttr:function(attr){var that=this;for(var i=0;i<this.length;i++){attr.split(/\s+/g).forEach(function(param){that[i].removeAttribute(param);if(that[i].jqmCacheId&&_attrCache[that[i].jqmCacheId][attr])
delete _attrCache[that[i].jqmCacheId][attr];});}
return this;},prop:function(prop,value){if(this.length===0)
return(value===undefined)?undefined:this;if(value===undefined&&!$.isObject(prop)){var res;var val=(this[0].jqmCacheId&&_propCache[this[0].jqmCacheId][prop])?(this[0].jqmCacheId&&_propCache[this[0].jqmCacheId][prop]):!(res=this[0][prop])&&prop in this[0]?this[0][prop]:res;return val;}
for(var i=0;i<this.length;i++){if($.isObject(prop)){for(var key in prop){$(this[i]).prop(key,prop[key]);}}
else if($.isArray(value)||$.isObject(value)||$.isFunction(value))
{if(!this[i].jqmCacheId)
this[i].jqmCacheId=$.uuid();if(!_propCache[this[i].jqmCacheId])
_propCache[this[i].jqmCacheId]={}
_propCache[this[i].jqmCacheId][prop]=value;}
else if(value==null&&value!==undefined)
{$(this[i]).removeProp(prop);}
else{this[i][prop]=value;}}
return this;},removeProp:function(prop){var that=this;for(var i=0;i<this.length;i++){prop.split(/\s+/g).forEach(function(param){if(that[i][param])
delete that[i][param];if(that[i].jqmCacheId&&_propCache[that[i].jqmCacheId][prop]){delete _propCache[that[i].jqmCacheId][prop];}});}
return this;},remove:function(selector){var elems=$(this).filter(selector);if(elems==undefined)
return this;for(var i=0;i<elems.length;i++){$.cleanUpContent(elems[i],true,true);elems[i].parentNode.removeChild(elems[i]);}
return this;},addClass:function(name){for(var i=0;i<this.length;i++){var cls=this[i].className;var classList=[];var that=this;name.split(/\s+/g).forEach(function(cname){if(!that.hasClass(cname,that[i]))
classList.push(cname);});this[i].className+=(cls?" ":"")+classList.join(" ");this[i].className=this[i].className.trim();}
return this;},removeClass:function(name){for(var i=0;i<this.length;i++){if(name==undefined){this[i].className='';return this;}
var classList=this[i].className;name.split(/\s+/g).forEach(function(cname){classList=classList.replace(classRE(cname)," ");});if(classList.length>0)
this[i].className=classList.trim();else
this[i].className="";}
return this;},replaceClass:function(name,newName){for(var i=0;i<this.length;i++){if(name==undefined){this[i].className=newName;continue;}
var classList=this[i].className;name.split(/\s+/g).concat(newName.split(/\s+/g)).forEach(function(cname){classList=classList.replace(classRE(cname)," ");});classList=classList.trim();if(classList.length>0){this[i].className=(classList+" "+newName).trim();}else
this[i].className=newName;}
return this;},hasClass:function(name,element){if(this.length===0)
return false;if(!element)
element=this[0];return classRE(name).test(element.className);},append:function(element,insert){if(element&&element.length!=undefined&&element.length===0)
return this;if($.isArray(element)||$.isObject(element))
element=$(element);var i;for(i=0;i<this.length;i++){if(element.length&&typeof element!="string"){element=$(element);_insertFragments(element,this[i],insert);}else{var obj=fragementRE.test(element)?$(element):undefined;if(obj==undefined||obj.length==0){obj=document.createTextNode(element);}
if(obj.nodeName!=undefined&&obj.nodeName.toLowerCase()=="script"&&(!obj.type||obj.type.toLowerCase()==='text/javascript')){window.eval(obj.innerHTML);}else if(obj instanceof $jqm){_insertFragments(obj,this[i],insert);}
else{insert!=undefined?this[i].insertBefore(obj,this[i].firstChild):this[i].appendChild(obj);}}}
return this;},appendTo:function(selector,insert){var tmp=$(selector);tmp.append(this);return this;},prependTo:function(selector){var tmp=$(selector);tmp.append(this,true);return this;},prepend:function(element){return this.append(element,1);},insertBefore:function(target,after){if(this.length==0)
return this;target=$(target).get(0);if(!target)
return this;for(var i=0;i<this.length;i++)
{after?target.parentNode.insertBefore(this[i],target.nextSibling):target.parentNode.insertBefore(this[i],target);}
return this;},insertAfter:function(target){this.insertBefore(target,true);},get:function(index){index=index==undefined?0:index;if(index<0)
index+=this.length;return(this[index])?this[index]:undefined;},offset:function(){if(this.length===0)
return this;if(this[0]==window)
return{left:0,top:0,right:0,bottom:0,width:window.innerWidth,height:window.innerHeight}
else
var obj=this[0].getBoundingClientRect();return{left:obj.left+window.pageXOffset,top:obj.top+window.pageYOffset,right:obj.right+window.pageXOffset,bottom:obj.bottom+window.pageYOffset,width:obj.right-obj.left,height:obj.bottom-obj.top};},height:function(val){if(this.length===0)
return this;if(val!=undefined)
return this.css("height",val);if(this[0]==this[0].window)
return window.innerHeight;if(this[0].nodeType==this[0].DOCUMENT_NODE)
return this[0].documentElement['offsetheight'];else{var tmpVal=this.css("height").replace("px","");if(tmpVal)
return tmpVal
else
return this.offset().height;}},width:function(val){if(this.length===0)
return this;if(val!=undefined)
return this.css("width",val);if(this[0]==this[0].window)
return window.innerWidth;if(this[0].nodeType==this[0].DOCUMENT_NODE)
return this[0].documentElement['offsetwidth'];else{var tmpVal=this.css("width").replace("px","");if(tmpVal)
return tmpVal
else
return this.offset().width;}},parent:function(selector,recursive){if(this.length==0)
return this;var elems=[];for(var i=0;i<this.length;i++){var tmp=this[i];while(tmp.parentNode&&tmp.parentNode!=document){elems.push(tmp.parentNode);if(tmp.parentNode)
tmp=tmp.parentNode;if(!recursive)
break;}}
return this.setupOld($(unique(elems)).filter(selector));},parents:function(selector){return this.parent(selector,true);},children:function(selector){if(this.length==0)
return this;var elems=[];for(var i=0;i<this.length;i++){elems=elems.concat(siblings(this[i].firstChild));}
return this.setupOld($((elems)).filter(selector));},siblings:function(selector){if(this.length==0)
return this;var elems=[];for(var i=0;i<this.length;i++){if(this[i].parentNode)
elems=elems.concat(siblings(this[i].parentNode.firstChild,this[i]));}
return this.setupOld($(elems).filter(selector));},closest:function(selector,context){if(this.length==0)
return this;var elems=[],cur=this[0];var start=$(selector,context);if(start.length==0)
return $();while(cur&&start.indexOf(cur)==-1){cur=cur!==context&&cur!==document&&cur.parentNode;}
return $(cur);},filter:function(selector){if(this.length==0)
return this;if(selector==undefined)
return this;var elems=[];for(var i=0;i<this.length;i++){var val=this[i];if(val.parentNode&&$(selector,val.parentNode).indexOf(val)>=0)
elems.push(val);}
return this.setupOld($(unique(elems)));},not:function(selector){if(this.length==0)
return this;var elems=[];for(var i=0;i<this.length;i++){var val=this[i];if(val.parentNode&&$(selector,val.parentNode).indexOf(val)==-1)
elems.push(val);}
return this.setupOld($(unique(elems)));},data:function(key,value){return this.attr('data-'+key,value);},end:function(){return this.oldElement!=undefined?this.oldElement:$();},clone:function(deep){deep=deep===false?false:true;if(this.length==0)
return this;var elems=[];for(var i=0;i<this.length;i++){elems.push(this[i].cloneNode(deep));}
return $(elems);},size:function(){return this.length;},serialize:function(){if(this.length==0)
return"";var params=[];for(var i=0;i<this.length;i++)
{this.slice.call(this[i].elements).forEach(function(elem){var type=elem.getAttribute("type");if(elem.nodeName.toLowerCase()!="fieldset"&&!elem.disabled&&type!="submit"&&type!="reset"&&type!="button"&&((type!="radio"&&type!="checkbox")||elem.checked))
{if(elem.getAttribute("name")){if(elem.type=="select-multiple"){for(var j=0;j<elem.options.length;j++){if(elem.options[j].selected)
params.push(elem.getAttribute("name")+"="+encodeURIComponent(elem.options[j].value))}}
else
params.push(elem.getAttribute("name")+"="+encodeURIComponent(elem.value))}}});}
return params.join("&");},eq:function(ind){return $(this.get(ind));},index:function(elem){return elem?this.indexOf($(elem)[0]):this.parent().children().indexOf(this[0]);},is:function(selector){return!!selector&&this.filter(selector).length>0;}};function empty(){}
var ajaxSettings={type:'GET',beforeSend:empty,success:empty,error:empty,complete:empty,context:undefined,timeout:0,crossDomain:null};$.jsonP=function(options){var callbackName='jsonp_callback'+(++_jsonPID);var abortTimeout="",context;var script=document.createElement("script");var abort=function(){$(script).remove();if(window[callbackName])
window[callbackName]=empty;};window[callbackName]=function(data){clearTimeout(abortTimeout);$(script).remove();delete window[callbackName];options.success.call(context,data);};script.src=options.url.replace(/=\?/,'='+callbackName);if(options.error)
{script.onerror=function(){clearTimeout(abortTimeout);options.error.call(context,"",'error');}}
$('head').append(script);if(options.timeout>0)
abortTimeout=setTimeout(function(){options.error.call(context,"",'timeout');},options.timeout);return{};};$.ajax=function(opts){var xhr;try{var settings=opts||{};for(var key in ajaxSettings){if(typeof(settings[key])=='undefined')
settings[key]=ajaxSettings[key];}
if(!settings.url)
settings.url=window.location;if(!settings.contentType)
settings.contentType="application/x-www-form-urlencoded";if(!settings.headers)
settings.headers={};if(!('async'in settings)||settings.async!==false)
settings.async=true;if(!settings.dataType)
settings.dataType="text/html";else{switch(settings.dataType){case"script":settings.dataType='text/javascript, application/javascript';break;case"json":settings.dataType='application/json';break;case"xml":settings.dataType='application/xml, text/xml';break;case"html":settings.dataType='text/html';break;case"text":settings.dataType='text/plain';break;default:settings.dataType="text/html";break;case"jsonp":return $.jsonP(opts);break;}}
if($.isObject(settings.data))
settings.data=$.param(settings.data);if(settings.type.toLowerCase()==="get"&&settings.data){if(settings.url.indexOf("?")===-1)
settings.url+="?"+settings.data;else
settings.url+="&"+settings.data;}
if(/=\?/.test(settings.url)){return $.jsonP(settings);}
if(settings.crossDomain===null)settings.crossDomain=/^([\w-]+:)?\/\/([^\/]+)/.test(settings.url)&&RegExp.$2!=window.location.host;if(!settings.crossDomain)
settings.headers=$.extend({'X-Requested-With':'XMLHttpRequest'},settings.headers);var abortTimeout;var context=settings.context;var protocol=/^([\w-]+:)\/\//.test(settings.url)?RegExp.$1:window.location.protocol;xhr=new window.XMLHttpRequest();xhr.onreadystatechange=function(){var mime=settings.dataType;if(xhr.readyState===4){clearTimeout(abortTimeout);var result,error=false;if((xhr.status>=200&&xhr.status<300)||xhr.status===0&&protocol=='file:'){if(mime==='application/json'&&!(/^\s*$/.test(xhr.responseText))){try{result=JSON.parse(xhr.responseText);}catch(e){error=e;}}else if(mime==='application/xml, text/xml'){result=xhr.responseXML;}
else if(mime=="text/html"){result=xhr.responseText;$.parseJS(result);}
else
result=xhr.responseText;if(xhr.status===0&&result.length===0)
error=true;if(error)
settings.error.call(context,xhr,'parsererror',error);else{settings.success.call(context,result,'success',xhr);}}else{error=true;settings.error.call(context,xhr,'error');}
settings.complete.call(context,xhr,error?'error':'success');}};xhr.open(settings.type,settings.url,settings.async);if(settings.withCredentials)xhr.withCredentials=true;if(settings.contentType)
settings.headers['Content-Type']=settings.contentType;for(var name in settings.headers)
xhr.setRequestHeader(name,settings.headers[name]);if(settings.beforeSend.call(context,xhr,settings)===false){xhr.abort();return false;}
if(settings.timeout>0)
abortTimeout=setTimeout(function(){xhr.onreadystatechange=empty;xhr.abort();settings.error.call(context,xhr,'timeout');},settings.timeout);xhr.send(settings.data);}catch(e){console.log(e);settings.error.call(context,xhr,'error',e);}
return xhr;};$.get=function(url,success){return this.ajax({url:url,success:success});};$.post=function(url,data,success,dataType){if(typeof(data)==="function"){success=data;data={};}
if(dataType===undefined)
dataType="html";return this.ajax({url:url,type:"POST",data:data,dataType:dataType,success:success});};$.getJSON=function(url,data,success){if(typeof(data)==="function"){success=data;data={};}
return this.ajax({url:url,data:data,success:success,dataType:"json"});};$.param=function(obj,prefix){var str=[];if(obj instanceof $jqm){obj.each(function(){var k=prefix?prefix+"[]":this.id,v=this.value;str.push((k)+"="+encodeURIComponent(v));});}else{for(var p in obj){var k=prefix?prefix+"["+p+"]":p,v=obj[p];str.push($.isObject(v)?$.param(v,k):(k)+"="+encodeURIComponent(v));}}
return str.join("&");};$.parseJSON=function(string){return JSON.parse(string);};$.parseXML=function(string){return(new DOMParser).parseFromString(string,"text/xml");};function detectUA($,userAgent){$.os={};$.os.webkit=userAgent.match(/WebKit\/([\d.]+)/)?true:false;$.os.android=userAgent.match(/(Android)\s+([\d.]+)/)||userAgent.match(/Silk-Accelerated/)?true:false;$.os.androidICS=$.os.android&&userAgent.match(/(Android)\s4/)?true:false;$.os.ipad=userAgent.match(/(iPad).*OS\s([\d_]+)/)?true:false;$.os.iphone=!$.os.ipad&&userAgent.match(/(iPhone\sOS)\s([\d_]+)/)?true:false;$.os.webos=userAgent.match(/(webOS|hpwOS)[\s\/]([\d.]+)/)?true:false;$.os.touchpad=$.os.webos&&userAgent.match(/TouchPad/)?true:false;$.os.ios=$.os.ipad||$.os.iphone;$.os.playbook=userAgent.match(/PlayBook/)?true:false;$.os.blackberry=$.os.playbook||userAgent.match(/BlackBerry/)?true:false;$.os.blackberry10=$.os.blackberry&&userAgent.match(/Safari\/536/)?true:false;$.os.chrome=userAgent.match(/Chrome/)?true:false;$.os.opera=userAgent.match(/Opera/)?true:false;$.os.fennec=userAgent.match(/fennec/i)?true:userAgent.match(/Firefox/)?true:false;$.os.ie=userAgent.match(/MSIE 10.0/i)?true:false;$.os.ieTouch=$.os.ie&&userAgent.toLowerCase().match(/touch/i)?true:false;$.os.supportsTouch=((window.DocumentTouch&&document instanceof window.DocumentTouch)||'ontouchstart'in window);$.feat={};var head=document.documentElement.getElementsByTagName("head")[0];$.feat.nativeTouchScroll=typeof(head.style["-webkit-overflow-scrolling"])!=="undefined"&&$.os.ios;$.feat.cssPrefix=$.os.webkit?"Webkit":$.os.fennec?"Moz":$.os.ie?"ms":$.os.opera?"O":"";$.feat.cssTransformStart=!$.os.opera?"3d(":"(";$.feat.cssTransformEnd=!$.os.opera?",0)":")";if($.os.android&&!$.os.webkit)
$.os.android=false;}
detectUA($,navigator.userAgent);$.__detectUA=detectUA;if(typeof String.prototype.trim!=='function'){String.prototype.trim=function(){this.replace(/(\r\n|\n|\r)/gm,"").replace(/^\s+|\s+$/,'');return this};}
$.uuid=function(){var S4=function(){return(((1+Math.random())*0x10000)|0).toString(16).substring(1);}
return(S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4());};$.getCssMatrix=function(ele){if(ele==undefined)return window.WebKitCSSMatrix||window.MSCSSMatrix||{a:0,b:0,c:0,d:0,e:0,f:0};try{if(window.WebKitCSSMatrix)
return new WebKitCSSMatrix(window.getComputedStyle(ele).webkitTransform)
else if(window.MSCSSMatrix)
return new MSCSSMatrix(window.getComputedStyle(ele).transform);else{var mat=window.getComputedStyle(ele)[$.feat.cssPrefix+'Transform'].replace(/[^0-9\-.,]/g,'').split(',');return{a:+mat[0],b:+mat[1],c:+mat[2],d:+mat[3],e:+mat[4],f:+mat[5]};}}
catch(e){return{a:0,b:0,c:0,d:0,e:0,f:0};}}
var handlers={},_jqmid=1;function jqmid(element){return element._jqmid||(element._jqmid=_jqmid++);}
function findHandlers(element,event,fn,selector){event=parse(event);if(event.ns)
var matcher=matcherFor(event.ns);return(handlers[jqmid(element)]||[]).filter(function(handler){return handler&&(!event.e||handler.e==event.e)&&(!event.ns||matcher.test(handler.ns))&&(!fn||handler.fn==fn||(typeof handler.fn==='function'&&typeof fn==='function'&&""+handler.fn===""+fn))&&(!selector||handler.sel==selector);});}
function parse(event){var parts=(''+event).split('.');return{e:parts[0],ns:parts.slice(1).sort().join(' ')};}
function matcherFor(ns){return new RegExp('(?:^| )'+ns.replace(' ',' .* ?')+'(?: |$)');}
function eachEvent(events,fn,iterator){if($.isObject(events))
$.each(events,iterator);else
events.split(/\s/).forEach(function(type){iterator(type,fn)});}
function add(element,events,fn,selector,getDelegate){var id=jqmid(element),set=(handlers[id]||(handlers[id]=[]));eachEvent(events,fn,function(event,fn){var delegate=getDelegate&&getDelegate(fn,event),callback=delegate||fn;var proxyfn=function(event){var result=callback.apply(element,[event].concat(event.data));if(result===false)
event.preventDefault();return result;};var handler=$.extend(parse(event),{fn:fn,proxy:proxyfn,sel:selector,del:delegate,i:set.length});set.push(handler);element.addEventListener(handler.e,proxyfn,false);});}
function remove(element,events,fn,selector){var id=jqmid(element);eachEvent(events||'',fn,function(event,fn){findHandlers(element,event,fn,selector).forEach(function(handler){delete handlers[id][handler.i];element.removeEventListener(handler.e,handler.proxy,false);});});}
$.event={add:add,remove:remove}
$.fn.bind=function(event,callback){for(var i=0;i<this.length;i++){add(this[i],event,callback);}
return this;};$.fn.unbind=function(event,callback){for(var i=0;i<this.length;i++){remove(this[i],event,callback);}
return this;};$.fn.one=function(event,callback){return this.each(function(i,element){add(this,event,callback,null,function(fn,type){return function(){var result=fn.apply(element,arguments);remove(element,type,fn);return result;}});});};var returnTrue=function(){return true},returnFalse=function(){return false},eventMethods={preventDefault:'isDefaultPrevented',stopImmediatePropagation:'isImmediatePropagationStopped',stopPropagation:'isPropagationStopped'};function createProxy(event){var proxy=$.extend({originalEvent:event},event);$.each(eventMethods,function(name,predicate){proxy[name]=function(){this[predicate]=returnTrue;if(name=="stopImmediatePropagation"||name=="stopPropagation"){event.cancelBubble=true;if(!event[name])
return;}
return event[name].apply(event,arguments);};proxy[predicate]=returnFalse;})
return proxy;}
$.fn.delegate=function(selector,event,callback){for(var i=0;i<this.length;i++){var element=this[i];add(element,event,callback,selector,function(fn){return function(e){var evt,match=$(e.target).closest(selector,element).get(0);if(match){evt=$.extend(createProxy(e),{currentTarget:match,liveFired:element});return fn.apply(match,[evt].concat([].slice.call(arguments,1)));}}});}
return this;};$.fn.undelegate=function(selector,event,callback){for(var i=0;i<this.length;i++){remove(this[i],event,callback,selector);}
return this;}
$.fn.on=function(event,selector,callback){return selector===undefined||$.isFunction(selector)?this.bind(event,selector):this.delegate(selector,event,callback);};$.fn.off=function(event,selector,callback){return selector===undefined||$.isFunction(selector)?this.unbind(event,selector):this.undelegate(selector,event,callback);};$.fn.trigger=function(event,data,props){if(typeof event=='string')
event=$.Event(event,props);event.data=data;for(var i=0;i<this.length;i++){this[i].dispatchEvent(event)}
return this;};$.Event=function(type,props){var event=document.createEvent('Events'),bubbles=true;if(props)
for(var name in props)
(name=='bubbles')?(bubbles=!!props[name]):(event[name]=props[name]);event.initEvent(type,bubbles,true,null,null,null,null,null,null,null,null,null,null,null,null);return event;};$.bind=function(obj,ev,f){if(!obj.__events)obj.__events={};if(!$.isArray(ev))ev=[ev];for(var i=0;i<ev.length;i++){if(!obj.__events[ev[i]])obj.__events[ev[i]]=[];obj.__events[ev[i]].push(f);}};$.trigger=function(obj,ev,args){var ret=true;if(!obj.__events)return ret;if(!$.isArray(ev))ev=[ev];if(!$.isArray(args))args=[];for(var i=0;i<ev.length;i++){if(obj.__events[ev[i]]){var evts=obj.__events[ev[i]];for(var j=0;j<evts.length;j++)
if($.isFunction(evts[j])&&evts[j].apply(obj,args)===false)
ret=false;}}
return ret;};$.unbind=function(obj,ev,f){if(!obj.__events)return;if(!$.isArray(ev))ev=[ev];for(var i=0;i<ev.length;i++){if(obj.__events[ev[i]]){var evts=obj.__events[ev[i]];for(var j=0;j<evts.length;j++){if(f==undefined)
delete evts[j];if(evts[j]==f){evts.splice(j,1);break;}}}}};$.proxy=function(f,c,args){return function(){if(args)return f.apply(c,args);return f.apply(c,arguments);}}
function cleanUpNode(node,kill){if(kill&&node.dispatchEvent){var e=$.Event('destroy',{bubbles:false});node.dispatchEvent(e);}
var id=jqmid(node);if(id&&handlers[id]){for(var key in handlers[id])
node.removeEventListener(handlers[id][key].e,handlers[id][key].proxy,false);delete handlers[id];}}
function cleanUpContent(node,kill){if(!node)return;var children=node.childNodes;if(children&&children.length>0)
for(var child in children)
cleanUpContent(children[child],kill);cleanUpNode(node,kill);}
var cleanUpAsap=function(els,kill){for(var i=0;i<els.length;i++){cleanUpContent(els[i],kill);}}
$.cleanUpContent=function(node,itself,kill){if(!node)return;var cn=node.childNodes;if(cn&&cn.length>0){$.asap(cleanUpAsap,{},[slice.apply(cn,[0]),kill]);}
if(itself)cleanUpNode(node,kill);}
var timeouts=[];var contexts=[];var params=[];$.asap=function(fn,context,args){if(!$.isFunction(fn))throw"$.asap - argument is not a valid function";timeouts.push(fn);contexts.push(context?context:{});params.push(args?args:[]);window.postMessage("jqm-asap","*");}
window.addEventListener("message",function(event){if(event.source==window&&event.data=="jqm-asap"){event.stopPropagation();if(timeouts.length>0){(timeouts.shift()).apply(contexts.shift(),params.shift());}}},true);var remoteJSPages={};$.parseJS=function(div){if(!div)
return;if(typeof(div)=="string"){var elem=document.createElement("div");elem.innerHTML=div;div=elem;}
var scripts=div.getElementsByTagName("script");div=null;for(var i=0;i<scripts.length;i++){if(scripts[i].src.length>0&&!remoteJSPages[scripts[i].src]){var doc=document.createElement("script");doc.type=scripts[i].type;doc.src=scripts[i].src;document.getElementsByTagName('head')[0].appendChild(doc);remoteJSPages[scripts[i].src]=1;doc=null;}else{window.eval(scripts[i].innerHTML);}}};["click","keydown","keyup","keypress","submit","load","resize","change","select","error"].forEach(function(event){$.fn[event]=function(cb){return cb?this.bind(event,cb):this.trigger(event);}});return $;})(window);'$'in window||(window.$=jq);if(!window.numOnly){window.numOnly=function numOnly(val){if(val===undefined||val==='')return 0;if(isNaN(parseFloat(val))){if(val.replace){val=val.replace(/[^0-9.-]/,"");}else return 0;}
return parseFloat(val);}}}
(function($){$["template"]=function(tmpl,data){return(template(tmpl,data));};$["tmpl"]=function(tmpl,data){return $(template(tmpl,data));};var template=function(str,data){if(!data)
data={};return tmpl(str,data);};(function(){var cache={};this.tmpl=function tmpl(str,data){var fn=!/\W/.test(str)||/.js$/.test(str)?cache[str]=cache[str]||tmpl(document.getElementById(str).innerHTML):new Function("obj","var p=[],print=function(){p.push.apply(p,arguments);};"+"with(obj){p.push('"+str.replace(/[\r\t\n]/g," ").replace(/'(?=[^%]*%>)/g,"\t").split("'").join("\\'").split("\t").join("'").replace(/<%=(.+?)%>/g,"',$1,'").split("<%").join("');").split("%>").join("p.push('")+"');}return p.join('');");return data?fn(data):fn;};})();})(jq);
var shareData=shareData||{appid:'',img_url:'',img_width:'640',img_height:'640',link:'',desc:'',title:'',content:'',url:'http://meishi.qq.com/shenzhen/weixin'};function shareFriend(){WeixinJSBridge.invoke("sendAppMessage",{appid:shareData.appid,img_url:shareData.img_url,img_width:shareData.img_width,img_height:shareData.img_height,link:shareData.link,desc:shareData.desc,title:shareData.title},function(a){});}
function shareTimeline(){var title=shareData.title;if(title.indexOf(shareData.desc)==-1){title+=":"+shareData.desc;}
WeixinJSBridge.invoke("shareTimeline",{img_url:shareData.img_url,img_width:shareData.img_width,img_height:shareData.img_height,link:shareData.link,desc:shareData.desc,title:title},function(a){});}
function shareWeibo(){WeixinJSBridge.invoke("shareWeibo",{content:shareData.content,url:shareData.url||' '},function(a){});}
(function(){document.addEventListener('WeixinJSBridgeReady',function onBridgeReady(){WeixinJSBridge.on('menu:share:appmessage',function(argv){shareFriend();});WeixinJSBridge.on('menu:share:timeline',function(argv){shareTimeline();});WeixinJSBridge.on('menu:share:weibo',function(argv){shareWeibo();});},false);})();