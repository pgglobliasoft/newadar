!function(t){null==t.isNumeric&&(t.isNumeric=function(t){return null!=t&&t.constructor===Number}),null==t.isFunction&&(t.isFunction=function(t){return null!=t&&t instanceof Function});var i=t(window),e=t(document),n={defaultConfig:{animate:!1,cellW:100,cellH:100,delay:0,engine:"giot",fixSize:null,gutterX:15,gutterY:15,keepOrder:!1,selector:"> div",draggable:!1,cacheSize:!0,rightToLeft:!1,bottomToTop:!1,onGapFound:function(){},onComplete:function(){},onResize:function(){},onBlockDrag:function(){},onBlockMove:function(){},onBlockDrop:function(){},onBlockReady:function(){},onBlockFinish:function(){},onBlockActive:function(){},onBlockResize:function(){}},plugin:{},totalGrid:1,transition:!1,loadBlock:function(i,e){var n=e.runtime,a=n.gutterX,o=n.gutterY,l=n.cellH,r=n.cellW,h=null,s=t(i),u=s.data("active"),c=s.attr("data-position"),d=parseInt(s.attr("data-fixSize")),f=n.lastId+++"-"+n.totalGrid;if(s.hasClass("fw-float"))return null;s.attr({id:f,"data-delay":i.index}),e.animate&&this.transition&&this.setTransition(i,""),isNaN(d)&&(d=null),null==d&&(d=e.fixSize);var g=d?"ceil":"round";null==s.attr("data-height")&&s.attr("data-height",s.height()),null==s.attr("data-width")&&s.attr("data-width",s.width());var m=1*s.attr("data-height"),w=1*s.attr("data-width");e.cacheSize||(i.style.width="",w=s.width(),i.style.height="",m=s.height());var p=w?Math[g]((w+a)/r):0,v=m?Math[g]((m+o)/l):0;if(d||"auto"!=e.cellH||(s.width(r*p-a),i.style.height="",v=(m=s.height())?Math.round((m+o)/l):0),d||"auto"!=e.cellW||(s.height(l*v-o),i.style.width="",p=(w=s.width())?Math.round((w+a)/r):0),null!=d&&(p>n.limitCol||v>n.limitRow))h=null;else if(v&&v<n.minHoB&&(n.minHoB=v),p&&p<n.minWoB&&(n.minWoB=p),v>n.maxHoB&&(n.maxHoB=v),p>n.maxWoB&&(n.maxWoB=p),0==w&&(p=0),0==m&&(v=0),h={resize:!1,id:f,width:p,height:v,fixSize:d},c){c=c.split("-"),h.y=1*c[0],h.x=1*c[1],h.width=null!=d?p:Math.min(p,n.limitCol-h.x),h.height=null!=d?v:Math.min(v,n.limitRow-h.y);var x=h.y+"-"+h.x+"-"+h.width+"-"+h.height;u?(n.holes[x]={id:h.id,top:h.y,left:h.x,width:h.width,height:h.height},this.setBlock(h,e)):delete n.holes[x]}return null==s.attr("data-state")?s.attr("data-state","init"):s.attr("data-state","move"),e.onBlockReady.call(i,h,e),c&&u?null:h},setBlock:function(t,i){var e=i.runtime,n=e.gutterX,a=e.gutterY,o=t.height,l=t.width,r=e.cellH,h=e.cellW,s=t.x,u=t.y;i.rightToLeft&&(s=e.limitCol-s-l),i.bottomToTop&&(u=e.limitRow-u-o);var c={fixSize:t.fixSize,resize:t.resize,top:u*r,left:s*h,width:h*l-n,height:r*o-a};return c.top=1*c.top.toFixed(2),c.left=1*c.left.toFixed(2),c.width=1*c.width.toFixed(2),c.height=1*c.height.toFixed(2),t.id&&(e.blocks[t.id]=c),c},showBlock:function(i,e){var n=e.runtime,a=e.animate&&!this.transition?"animate":"css",o=n.blocks[i.id],l=t(i),r=this,h="move"!=l.attr("data-state"),s=h?"width 0.5s, height 0.5s":"top 0.5s, left 0.5s, width 0.5s, height 0.5s, opacity 0.5s";function u(){if(h&&l.attr("data-state","start"),e.animate&&r.transition&&r.setTransition(i,s),n.length-=1,o)o.fixSize&&(o.height=1*l.attr("data-height"),o.width=1*l.attr("data-width")),l.css({opacity:1,width:o.width,height:o.height}),l[a]({top:o.top,left:o.left}),null!=l.attr("data-nested")&&r.nestedGrid(i,e);else{var t=parseInt(i.style.height)||0,u=parseInt(i.style.width)||0,c=parseInt(i.style.left)||0,d=parseInt(i.style.top)||0;l[a]({left:c+u/2,top:d+t/2,width:0,height:0,opacity:0})}if(e.onBlockFinish.call(i,o,e),0==n.length){var f=e.animate?500:0;i.delay=setTimeout(function(){e.onComplete.call(i,o,e)},f)}}i.delay&&clearTimeout(i.delay),l.hasClass("fw-float")||(r.setTransition(i,""),i.style.position="absolute",e.onBlockActive.call(i,o,e),o&&o.resize&&e.onBlockResize.call(i,o,e),e.delay>0?i.delay=setTimeout(u,e.delay*l.attr("data-delay")):u())},nestedGrid:function(i,e){var n,a=t(i),l=e.runtime,r=a.attr("data-gutterX")||e.gutterX,h=a.attr("data-gutterY")||e.gutterY,s=a.attr("data-method")||"fitZone",u=a.attr("data-nested")||"> div",c=a.attr("data-cellH")||e.cellH,d=a.attr("data-cellW")||e.cellW,f=l.blocks[i.id];if(f)switch((n=new o(a)).reset({cellH:c,cellW:d,gutterX:1*r,gutterY:1*h,selector:u,cacheSize:!1}),s){case"fitHeight":n[s](f.height);break;case"fitWidth":n[s](f.width);break;case"fitZone":n[s](f.width,f.height)}},adjustBlock:function(i,e){var n=e.runtime,a=n.gutterX,o=n.gutterY,l=t("#"+i.id),r=n.cellH,h=n.cellW;"auto"==e.cellH&&(l.width(i.width*h-a),l[0].style.height="",i.height=Math.round((l.height()+o)/r))},adjustUnit:function(i,e,n){var a=n.gutterX,o=n.gutterY,l=n.runtime,r=n.cellW,h=n.cellH;if(t.isFunction(r)&&(r=r(i)),r*=1,!t.isNumeric(r)&&(r=1),t.isFunction(h)&&(h=h(e)),h*=1,!t.isNumeric(h)&&(h=1),t.isNumeric(i)){r<1&&(r*=i);var s=Math.max(1,Math.floor(i/r));t.isNumeric(a)||(a=(i-s*r)/Math.max(1,s-1),a=Math.max(0,a)),s=Math.floor((i+a)/r),l.cellW=(i+a)/Math.max(s,1),l.cellS=l.cellW/r,l.gutterX=a,l.limitCol=s}if(t.isNumeric(e)){h<1&&(h*=e);var u=Math.max(1,Math.floor(e/h));t.isNumeric(o)||(o=(e-u*h)/Math.max(1,u-1),o=Math.max(0,o)),u=Math.floor((e+o)/h),l.cellH=(e+o)/Math.max(u,1),l.cellS=l.cellH/h,l.gutterY=o,l.limitRow=u}t.isNumeric(i)||(r<1&&(r=l.cellH),l.cellW=1!=r?r*l.cellS:1,l.gutterX=a,l.limitCol=666666),t.isNumeric(e)||(h<1&&(h=l.cellW),l.cellH=1!=h?h*l.cellS:1,l.gutterY=o,l.limitRow=666666),l.keepOrder=n.keepOrder},resetGrid:function(t){t.blocks={},t.length=0,t.cellH=0,t.cellW=0,t.lastId=1,t.matrix={},t.totalCol=0,t.totalRow=0},setDraggable:function(i,n){var a=!1,o={startX:0,startY:0,top:0,left:0,handle:null,onDrop:function(){},onDrag:function(){},onStart:function(){}};t(i).each(function(){var i=t.extend({},o,n),l=i.handle||this,r=this,h=t(r),s=t(l);function u(t){t=t.originalEvent,a&&(t=t.changedTouches[0]),h.css({top:i.top-(i.startY-t.clientY),left:i.left-(i.startX-t.clientX)}),i.onDrag.call(r,t)}function c(t){t=t.originalEvent,a&&(t=t.changedTouches[0]),i.onDrop.call(r,t),e.unbind("mouseup touchend",c),e.unbind("mousemove touchmove",u)}"absolute"!=h.css("position")&&h.css("position","relative"),h.find("iframe, form, input, textarea, .ignore-drag").each(function(){t(this).on("touchstart mousedown",function(t){t.stopPropagation()})}),e.unbind("mouseup touchend",c),e.unbind("mousemove touchmove",u),s.unbind("mousedown touchstart").bind("mousedown touchstart",function(t){return t.stopPropagation(),(t=t.originalEvent).touches&&(a=!0,t=t.changedTouches[0]),2!=t.button&&3!=t.which&&(i.onStart.call(r,t),i.startX=t.clientX,i.startY=t.clientY,i.top=parseInt(h.css("top"))||0,i.left=parseInt(h.css("left"))||0,e.bind("mouseup touchend",c),e.bind("mousemove touchmove",u)),!1})})},setTransition:function(i,e){var n=i.style,a=t(i);!this.transition&&a.stop?a.stop():null!=n.webkitTransition?n.webkitTransition=e:null!=n.MozTransition?n.MozTransition=e:null!=n.msTransition?n.msTransition=e:null!=n.OTransition?n.OTransition=e:n.transition=e},getFreeArea:function(t,i,e){for(var n=Math.min(t+e.maxHoB,e.limitRow),a=Math.min(i+e.maxWoB,e.limitCol),o=a,l=n,r=e.matrix,h=t;h<l;++h)for(var s=i;s<a;++s)r[h+"-"+s]&&i<s&&s<o&&(o=s);for(h=t;h<n;++h)for(s=i;s<o;++s)r[h+"-"+s]&&t<h&&h<l&&(l=h);return{top:t,left:i,width:o-i,height:l-t}},setWallSize:function(t,i){var e=t.totalRow,n=t.totalCol,a=t.gutterY,o=t.gutterX,l=t.cellH,r=t.cellW,h=Math.max(0,r*n-o),s=Math.max(0,l*e-a);i.attr({"data-total-col":n,"data-total-row":e,"data-wall-width":Math.ceil(h),"data-wall-height":Math.ceil(s)}),t.limitCol<t.limitRow&&!i.attr("data-height")&&i.height(Math.ceil(s))}},a={giot:function(t,i){var e=i.runtime,a=e.limitRow,o=e.limitCol,l=0,r=0,h=e.totalCol,s=e.totalRow,u={},c=e.holes,d=null,f=e.matrix,g=Math.max(o,a),m=null,w=null,p=o<a?1:0,v=null,x=Math.min(o,a);function M(t,i,e,n,a){for(var o=i;o<i+a;){for(var l=e;l<e+n;)f[o+"-"+l]=t,++l>h&&(h=l);++o>s&&(s=o)}}for(var k in c)c.hasOwnProperty(k)&&M(c[k].id||!0,c[k].top,c[k].left,c[k].width,c[k].height);for(var y=0;y<g&&t.length;++y){p?r=y:l=y,v=null;for(var B=0;B<x&&t.length;++B)if(d=null,p?l=B:r=B,!e.matrix[r+"-"+l]){if(m=n.getFreeArea(r,l,e),null==i.fixSize){if(v&&!p&&e.minHoB>m.height){v.height+=m.height,v.resize=!0,M(v.id,v.y,v.x,v.width,v.height),n.setBlock(v,i);continue}if(v&&p&&e.minWoB>m.width){v.width+=m.width,v.resize=!0,M(v.id,v.y,v.x,v.width,v.height),n.setBlock(v,i);continue}}if(e.keepOrder)(d=t.shift()).resize=!0;else{for(k=0;k<t.length;++k)if(!(t[k].height>m.height||t[k].width>m.width)){d=t.splice(k,1)[0];break}if(null==d&&null==i.fixSize)for(k=0;k<t.length;++k)if(null==t[k].fixSize){(d=t.splice(k,1)[0]).resize=!0;break}}if(null!=d)d.resize&&(p?(d.width=m.width,"auto"==i.cellH&&n.adjustBlock(d,i),d.height=Math.min(d.height,m.height)):(d.height=m.height,d.width=Math.min(d.width,m.width))),u[d.id]={id:d.id,x:l,y:r,width:d.width,height:d.height,resize:d.resize,fixSize:d.fixSize},M((v=u[d.id]).id,v.y,v.x,v.width,v.height),n.setBlock(v,i);else{w={x:l,y:r,fixSize:0};if(p){w.width=m.width,w.height=0;for(var z=l-1,b=r;f[b+"-"+z];)f[b+"-"+l]=!0,w.height+=1,b+=1}else{w.height=m.height,w.width=0;for(b=r-1,z=l;f[b+"-"+z];)f[r+"-"+z]=!0,w.width+=1,z+=1}i.onGapFound(n.setBlock(w,i),i)}}}e.matrix=f,e.totalRow=s,e.totalCol=h}};function o(e){var o=t(e);"static"==o.css("position")&&o.css("position","relative");var l=Number.MAX_VALUE,r=this;n.totalGrid+=1;var h=t.extend({},n.defaultConfig),s={arguments:null,blocks:{},events:{},matrix:{},holes:{},cellW:0,cellH:0,cellS:1,filter:"",lastId:0,length:0,maxWoB:0,maxHoB:0,minWoB:l,minHoB:l,running:0,gutterX:15,gutterY:15,totalCol:0,totalRow:0,limitCol:666666,limitRow:666666,sortFunc:null,keepOrder:!1};h.runtime=s,s.totalGrid=n.totalGrid;var u=document.body.style;function c(i){s.gutterX,s.gutterY;var e=s.cellH,a=s.cellW,o=t(i),l=o.find(o.attr("data-handle"));n.setDraggable(i,{handle:l[0],onStart:function(t){h.animate&&n.transition&&n.setTransition(this,""),o.css("z-index",9999).addClass("fw-float"),h.onBlockDrag.call(i,t)},onDrag:function(t,n){var l=o.position(),u=Math.round(l.top/e),c=Math.round(l.left/a),d=Math.round(o.width()/a),f=Math.round(o.height()/e);u=Math.min(Math.max(0,u),s.limitRow-f),c=Math.min(Math.max(0,c),s.limitCol-d),r.setHoles({top:u,left:c,width:d,height:f}),r.refresh(),h.onBlockMove.call(i,t)},onDrop:function(n){var l,u,c,d,f=o.position(),g=Math.round(f.top/e),m=Math.round(f.left/a),w=Math.round(o.width()/a),p=Math.round(o.height()/e);for(g=Math.min(Math.max(0,g),s.limitRow-p),m=Math.min(Math.max(0,m),s.limitCol-w),o.removeClass("fw-float"),o.css({zIndex:"auto",top:g*e,left:m*a}),u=0;u<p;++u)for(l=0;l<w;++l)c=u+g+"-"+(l+m),(d=s.matrix[c])&&1!=d&&t("#"+d).removeAttr("data-position");s.holes={},o.attr({"data-width":o.width(),"data-height":o.height(),"data-position":g+"-"+m}),r.refresh(),h.onBlockDrop.call(i,n)}})}n.transition||(null!=u.webkitTransition||null!=u.MozTransition||null!=u.msTransition||null!=u.OTransition||null!=u.transition)&&(n.transition=!0),t.extend(r,{addCustomEvent:function(t,i){var e=s.events;return!e[t=t.toLowerCase()]&&(e[t]=[]),i.eid=e[t].length,e[t].push(i),this},appendBlock:function(i){var e=t(i).appendTo(o),l=null,r=[];s.arguments&&(t.isFunction(s.sortFunc)&&e.sort(s.sortFunc),e.each(function(t,i){i.index=++t,(l=n.loadBlock(i,h))&&r.push(l)}),a[h.engine](r,h),n.setWallSize(s,o),s.length=e.length,e.each(function(t,i){n.showBlock(i,h),(h.draggable||i.getAttribute("data-draggable"))&&c(i)}))},appendHoles:function(t){var i,e=[].concat(t),n={};for(i=0;i<e.length;++i)n=e[i],s.holes[n.top+"-"+n.left+"-"+n.width+"-"+n.height]=n;return this},container:o,destroy:function(){o.find(h.selector).removeAttr("id").each(function(i,e){$item=t(e);var n=1*$item.attr("data-width")||"",a=1*$item.attr("data-height")||"";$item.width(n).height(a).css({position:"static"})})},fillHoles:function(t){if(0==arguments.length)s.holes={};else{var i,e=[].concat(t),n={};for(i=0;i<e.length;++i)n=e[i],delete s.holes[n.top+"-"+n.left+"-"+n.width+"-"+n.height]}return this},filter:function(t){return s.filter=t,s.arguments&&this.refresh(),this},fireEvent:function(t,i,e){var n=s.events;if(n[t=t.toLowerCase()]&&n[t].length)for(var a=0;a<n[t].length;++a)n[t][a].call(this,i,e);return this},fitHeight:function(t){t=t||(o.height()||i.height());this.fitZone("auto",t),s.arguments=arguments},fitWidth:function(t){t=t||(o.width()||i.width());this.fitZone(t,"auto"),s.arguments=arguments},fitZone:function(e,l){var u=o.find(h.selector).removeAttr("id"),d=null,f=[];l=l||(o.height()||i.height()),e=e||(o.width()||i.width()),s.arguments=arguments,n.resetGrid(s),n.adjustUnit(e,l,h),s.filter?(u.data("active",0),u.filter(s.filter).data("active",1)):u.data("active",1),t.isFunction(s.sortFunc)&&u.sort(s.sortFunc),u.each(function(i,e){var a=t(e);e.index=++i,(d=n.loadBlock(e,h))&&a.data("active")&&f.push(d)}),r.fireEvent("onGridReady",o,h),a[h.engine](f,h),n.setWallSize(s,o),r.fireEvent("onGridArrange",o,h),s.length=u.length,u.each(function(t,i){n.showBlock(i,h),(h.draggable||i.getAttribute("data-draggable"))&&c(i)})},fixPos:function(i){return t(i.block).attr({"data-position":i.top+"-"+i.left}),this},fixSize:function(i){return null!=i.height&&t(i.block).attr({"data-height":i.height}),null!=i.width&&t(i.block).attr({"data-width":i.width}),this},prepend:function(t){return o.prepend(t),s.arguments&&this.refresh(),this},refresh:function(){var t=arguments.length?arguments:s.arguments,i=s.arguments;return(i?i.callee:this.fitWidth).apply(this,Array.prototype.slice.call(t,0)),this},reset:function(i){return t.extend(h,i),this},setHoles:function(t){var i,e=[].concat(t),n={};for(s.holes={},i=0;i<e.length;++i)n=e[i],s.holes[n.top+"-"+n.left+"-"+n.width+"-"+n.height]=n;return this},sortBy:function(t){return s.sortFunc=t,s.arguments&&this.refresh(),this},unFilter:function(){return delete s.filter,this.refresh(),this}}),o.attr("data-min-width",80*Math.floor(i.width()/80));for(var d in n.plugin)n.plugin.hasOwnProperty(d)&&n.plugin[d].call(r,h,o);i.resize(function(){s.running||(s.running=1,setTimeout(function(){s.running=0,h.onResize.call(r,o)},122),o.attr("data-min-width",80*Math.floor(i.width()/80)))})}o.addConfig=function(i){t.extend(n.defaultConfig,i)},o.createEngine=function(i){t.extend(a,i)},o.createPlugin=function(i){t.extend(n.plugin,i)},o.getMethod=function(t){return n[t]},window.Freewall=window.freewall=o}(window.Zepto||window.jQuery);