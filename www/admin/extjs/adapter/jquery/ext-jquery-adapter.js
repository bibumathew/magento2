/*
 * Ext JS Library 1.0
 * Copyright(c) 2006-2007, Ext JS, LLC.
 * licensing@extjs.com
 * 
 * http://www.extjs.com/license
 */


Ext={};window["undefined"]=window["undefined"];Ext.apply=function(o,c,defaults){if(defaults){Ext.apply(o,defaults);}
if(o&&c&&typeof c=='object'){for(var p in c){o[p]=c[p];}}
return o;};(function(){var idSeed=0;var ua=navigator.userAgent.toLowerCase();var isStrict=document.compatMode=="CSS1Compat",isOpera=ua.indexOf("opera")>-1,isSafari=(/webkit|khtml/).test(ua),isIE=ua.indexOf("msie")>-1,isIE7=ua.indexOf("msie 7")>-1,isGecko=!isSafari&&ua.indexOf("gecko")>-1,isBorderBox=isIE&&!isStrict,isWindows=(ua.indexOf("windows")!=-1||ua.indexOf("win32")!=-1),isMac=(ua.indexOf("macintosh")!=-1||ua.indexOf("mac os x")!=-1),isSecure=window.location.href.toLowerCase().indexOf("https")===0;if(isIE&&!isIE7){try{document.execCommand("BackgroundImageCache",false,true);}catch(e){}}
Ext.apply(Ext,{isStrict:isStrict,isSecure:isSecure,isReady:false,SSL_SECURE_URL:"javascript:false",BLANK_IMAGE_URL:"http:/"+"/extjs.com/s.gif",emptyFn:function(){},applyIf:function(o,c){if(o&&c){for(var p in c){if(typeof o[p]=="undefined"){o[p]=c[p];}}}
return o;},addBehaviors:function(o){if(!Ext.isReady){Ext.onReady(function(){Ext.addBehaviors(o);});return;}
var cache={};for(var b in o){var parts=b.split('@');if(parts[1]){var s=parts[0];if(!cache[s]){cache[s]=Ext.select(s);}
cache[s].on(parts[1],o[b]);}}
cache=null;},id:function(el,prefix){prefix=prefix||"ext-gen";el=Ext.getDom(el);var id=prefix+(++idSeed);return el?(el.id?el.id:(el.id=id)):id;},extend:function(){var io=function(o){for(var m in o){this[m]=o[m];}};return function(sb,sp,overrides){if(typeof sp=='object'){overrides=sp;sp=sb;sb=function(){sp.apply(this,arguments);};}
var F=function(){},sbp,spp=sp.prototype;F.prototype=spp;sbp=sb.prototype=new F();sbp.constructor=sb;sb.superclass=spp;if(spp.constructor==Object.prototype.constructor){spp.constructor=sp;}
sb.override=function(o){Ext.override(sb,o);};sbp.override=io;sbp.__extcls=sb;Ext.override(sb,overrides);return sb;};}(),override:function(origclass,overrides){if(overrides){var p=origclass.prototype;for(var method in overrides){p[method]=overrides[method];}}},namespace:function(){var a=arguments,o=null,i,j,d,rt;for(i=0;i<a.length;++i){d=a[i].split(".");rt=d[0];eval('if (typeof '+rt+' == "undefined"){'+rt+' = {};} o = '+rt+';');for(j=1;j<d.length;++j){o[d[j]]=o[d[j]]||{};o=o[d[j]];}}},urlEncode:function(o){if(!o){return"";}
var buf=[];for(var key in o){var ov=o[key];var type=typeof ov;if(type=='undefined'){buf.push(encodeURIComponent(key),"=&");}else if(type!="function"&&type!="object"){buf.push(encodeURIComponent(key),"=",encodeURIComponent(ov),"&");}else if(ov instanceof Array){for(var i=0,len=ov.length;i<len;i++){buf.push(encodeURIComponent(key),"=",encodeURIComponent(ov[i]===undefined?'':ov[i]),"&");}}}
buf.pop();return buf.join("");},urlDecode:function(string,overwrite){if(!string||!string.length){return{};}
var obj={};var pairs=string.split('&');var pair,name,value;for(var i=0,len=pairs.length;i<len;i++){pair=pairs[i].split('=');name=decodeURIComponent(pair[0]);value=decodeURIComponent(pair[1]);if(overwrite!==true){if(typeof obj[name]=="undefined"){obj[name]=value;}else if(typeof obj[name]=="string"){obj[name]=[obj[name]];obj[name].push(value);}else{obj[name].push(value);}}else{obj[name]=value;}}
return obj;},each:function(array,fn,scope){if(typeof array.length=="undefined"||typeof array=="string"){array=[array];}
for(var i=0,len=array.length;i<len;i++){if(fn.call(scope||array[i],array[i],i,array)===false){return i;};}},combine:function(){var as=arguments,l=as.length,r=[];for(var i=0;i<l;i++){var a=as[i];if(a instanceof Array){r=r.concat(a);}else if(a.length!==undefined&&!a.substr){r=r.concat(Array.prototype.slice.call(a,0));}else{r.push(a);}}
return r;},escapeRe:function(s){return s.replace(/([.*+?^${}()|[\]\/\\])/g,"\\$1");},callback:function(cb,scope,args,delay){if(typeof cb=="function"){if(delay){cb.defer(delay,scope,args||[]);}else{cb.apply(scope,args||[]);}}},getDom:function(el){if(!el){return null;}
return el.dom?el.dom:(typeof el=='string'?document.getElementById(el):el);},getCmp:function(id){return Ext.ComponentMgr.get(id);},num:function(v,defaultValue){if(typeof v!='number'){return defaultValue;}
return v;},destroy:function(){for(var i=0,a=arguments,len=a.length;i<len;i++){var as=a[i];if(as){if(as.dom){as.removeAllListeners();as.remove();continue;}
if(typeof as.purgeListeners=='function'){as.purgeListeners();}
if(typeof as.destroy=='function'){as.destroy();}}}},isOpera:isOpera,isSafari:isSafari,isIE:isIE,isIE7:isIE7,isGecko:isGecko,isBorderBox:isBorderBox,isWindows:isWindows,isMac:isMac,useShims:((isIE&&!isIE7)||(isGecko&&isMac))});})();Ext.namespace("Ext","Ext.util","Ext.grid","Ext.dd","Ext.tree","Ext.data","Ext.form","Ext.menu","Ext.state","Ext.lib","Ext.layout");Ext.apply(Function.prototype,{createCallback:function(){var args=arguments;var method=this;return function(){return method.apply(window,args);};},createDelegate:function(obj,args,appendArgs){var method=this;return function(){var callArgs=args||arguments;if(appendArgs===true){callArgs=Array.prototype.slice.call(arguments,0);callArgs=callArgs.concat(args);}else if(typeof appendArgs=="number"){callArgs=Array.prototype.slice.call(arguments,0);var applyArgs=[appendArgs,0].concat(args);Array.prototype.splice.apply(callArgs,applyArgs);}
return method.apply(obj||window,callArgs);};},defer:function(millis,obj,args,appendArgs){var fn=this.createDelegate(obj,args,appendArgs);if(millis){return setTimeout(fn,millis);}
fn();return 0;},createSequence:function(fcn,scope){if(typeof fcn!="function"){return this;}
var method=this;return function(){var retval=method.apply(this||window,arguments);fcn.apply(scope||this||window,arguments);return retval;};},createInterceptor:function(fcn,scope){if(typeof fcn!="function"){return this;}
var method=this;return function(){fcn.target=this;fcn.method=method;if(fcn.apply(scope||this||window,arguments)===false){return;}
return method.apply(this||window,arguments);};}});Ext.applyIf(String,{escape:function(string){return string.replace(/('|\\)/g,"\\$1");},leftPad:function(val,size,ch){var result=new String(val);if(ch==null){ch=" ";}
while(result.length<size){result=ch+result;}
return result;},format:function(format){var args=Array.prototype.slice.call(arguments,1);return format.replace(/\{(\d+)\}/g,function(m,i){return args[i];});}});String.prototype.toggle=function(value,other){return this==value?other:value;};Ext.applyIf(Number.prototype,{constrain:function(min,max){return Math.min(Math.max(this,min),max);}});Ext.applyIf(Array.prototype,{indexOf:function(o){for(var i=0,len=this.length;i<len;i++){if(this[i]==o)return i;}
return-1;},remove:function(o){var index=this.indexOf(o);if(index!=-1){this.splice(index,1);}}});Date.prototype.getElapsed=function(date){return Math.abs((date||new Date()).getTime()-this.getTime());};

if(typeof jQuery=="undefined"){throw"Unable to load Ext, jQuery not found.";}
(function(){Ext.lib.Dom={getViewWidth:function(full){return full?Math.max(jQuery(document).width(),jQuery(window).width()):jQuery(window).width();},getViewHeight:function(full){return full?Math.max(jQuery(document).height(),jQuery(window).height()):jQuery(window).height();},isAncestor:function(p,c){p=Ext.getDom(p);c=Ext.getDom(c);if(!p||!c){return false;}
if(p.contains&&!Ext.isSafari){return p.contains(c);}else if(p.compareDocumentPosition){return!!(p.compareDocumentPosition(c)&16);}else{var parent=c.parentNode;while(parent){if(parent==p){return true;}
else if(!parent.tagName||parent.tagName.toUpperCase()=="HTML"){return false;}
parent=parent.parentNode;}
return false;}},getRegion:function(el){return Ext.lib.Region.getRegion(el);},getY:function(el){return jQuery(el).offset({scroll:false}).top;},getX:function(el){return jQuery(el).offset({scroll:false}).left;},getXY:function(el){var o=jQuery(el).offset({scroll:false});return[o.left,o.top];},setXY:function(el,xy){el=Ext.fly(el,'_setXY');el.position();var pts=el.translatePoints(xy);if(xy[0]!==false){el.dom.style.left=pts.left+"px";}
if(xy[1]!==false){el.dom.style.top=pts.top+"px";}},setX:function(el,x){this.setXY(el,[x,false]);},setY:function(el,y){this.setXY(el,[false,y]);}};Ext.lib.Event={getPageX:function(e){e=e.browserEvent||e;return e.pageX;},getPageY:function(e){e=e.browserEvent||e;return e.pageY;},getXY:function(e){e=e.browserEvent||e;return[e.pageX,e.pageY];},getTarget:function(e){return e.target;},on:function(el,eventName,fn,scope,override){jQuery(el).bind(eventName,fn);},un:function(el,eventName,fn){jQuery(el).unbind(eventName,fn);},purgeElement:function(el){jQuery(el).unbind();},preventDefault:function(e){e=e.browserEvent||e;e.preventDefault();},stopPropagation:function(e){e=e.browserEvent||e;e.stopPropagation();},stopEvent:function(e){e=e.browserEvent||e;e.preventDefault();e.stopPropagation();},onAvailable:function(id,fn,scope){var start=new Date();var f=function(){if(start.getElapsed()>10000){clearInterval(iid);}
var el=document.getElementById(id);if(el){clearInterval(iid);fn.call(scope||window,el);}};var iid=setInterval(f,50);},resolveTextNode:function(node){if(node&&3==node.nodeType){return node.parentNode;}else{return node;}},getRelatedTarget:function(ev){ev=ev.browserEvent||ev;var t=ev.relatedTarget;if(!t){if(ev.type=="mouseout"){t=ev.toElement;}else if(ev.type=="mouseover"){t=ev.fromElement;}}
return this.resolveTextNode(t);}};Ext.lib.Ajax=function(){var createComplete=function(cb){return function(xhr,status){if((status=='error'||status=='timeout')&&cb.failure){cb.failure.call(cb.scope||window,{responseText:xhr.responseText,responseXML:xhr.responseXML,argument:cb.argument});}else if(cb.success){cb.success.call(cb.scope||window,{responseText:xhr.responseText,responseXML:xhr.responseXML,argument:cb.argument});}};};return{request:function(method,uri,cb,data){jQuery.ajax({type:method,url:uri,data:data,timeout:cb.timeout,complete:createComplete(cb)});},formRequest:function(form,uri,cb,data,isUpload,sslUri){jQuery.ajax({type:Ext.getDom(form).method||'POST',url:uri,data:jQuery(form).formSerialize()+(data?'&'+data:''),timeout:cb.timeout,complete:createComplete(cb)});},isCallInProgress:function(trans){return false;},abort:function(trans){return false;},serializeForm:function(form){return jQuery(form.dom||form).formSerialize();}};}();Ext.lib.Anim=function(){var createAnim=function(cb,scope){var animated=true;return{stop:function(skipToLast){},isAnimated:function(){return animated;},proxyCallback:function(){animated=false;Ext.callback(cb,scope);}};};return{scroll:function(el,args,duration,easing,cb,scope){var anim=createAnim(cb,scope);el=Ext.getDom(el);el.scrollLeft=args.scroll.to[0];el.scrollTop=args.scroll.to[1];anim.proxyCallback();return anim;},motion:function(el,args,duration,easing,cb,scope){return this.run(el,args,duration,easing,cb,scope);},color:function(el,args,duration,easing,cb,scope){var anim=createAnim(cb,scope);anim.proxyCallback();return anim;},run:function(el,args,duration,easing,cb,scope,type){var anim=createAnim(cb,scope);var o={};for(var k in args){switch(k){case'points':var by,pts,e=Ext.fly(el,'_animrun');e.position();if(by=args.points.by){var xy=e.getXY();pts=e.translatePoints([xy[0]+by[0],xy[1]+by[1]]);}else{pts=e.translatePoints(args.points.to);}
o.left=pts.left;o.top=pts.top;if(!parseInt(e.getStyle('left'),10)){e.setLeft(0);}
if(!parseInt(e.getStyle('top'),10)){e.setTop(0);}
break;case'width':o.width=args.width.to;break;case'height':o.height=args.height.to;break;case'opacity':o.opacity=args.opacity.to;break;default:o[k]=args[k].to;break;}}
jQuery(el).animate(o,duration*1000,undefined,anim.proxyCallback);return anim;}};}();Ext.lib.Region=function(t,r,b,l){this.top=t;this[1]=t;this.right=r;this.bottom=b;this.left=l;this[0]=l;};Ext.lib.Region.prototype={contains:function(region){return(region.left>=this.left&&region.right<=this.right&&region.top>=this.top&&region.bottom<=this.bottom);},getArea:function(){return((this.bottom-this.top)*(this.right-this.left));},intersect:function(region){var t=Math.max(this.top,region.top);var r=Math.min(this.right,region.right);var b=Math.min(this.bottom,region.bottom);var l=Math.max(this.left,region.left);if(b>=t&&r>=l){return new Ext.lib.Region(t,r,b,l);}else{return null;}},union:function(region){var t=Math.min(this.top,region.top);var r=Math.max(this.right,region.right);var b=Math.max(this.bottom,region.bottom);var l=Math.min(this.left,region.left);return new Ext.lib.Region(t,r,b,l);},adjust:function(t,l,b,r){this.top+=t;this.left+=l;this.right+=r;this.bottom+=b;return this;}};Ext.lib.Region.getRegion=function(el){var p=Ext.lib.Dom.getXY(el);var t=p[1];var r=p[0]+el.offsetWidth;var b=p[1]+el.offsetHeight;var l=p[0];return new Ext.lib.Region(t,r,b,l);};Ext.lib.Point=function(x,y){if(x instanceof Array){y=x[1];x=x[0];}
this.x=this.right=this.left=this[0]=x;this.y=this.top=this.bottom=this[1]=y;};Ext.lib.Point.prototype=new Ext.lib.Region();if(Ext.isIE){jQuery(window).unload(function(){var p=Function.prototype;delete p.createSequence;delete p.defer;delete p.createDelegate;delete p.createCallback;delete p.createInterceptor;});}})();
