!function(e,t){"function"==typeof define&&define.amd?define("sifter",t):"object"==typeof exports?module.exports=t():e.Sifter=t()}(this,function(){var e=function(e,t){this.items=e,this.settings=t||{diacritics:!0}};e.prototype.tokenize=function(e){if(e=i(String(e||"").toLowerCase()),!e||!e.length)return[];var t,n,r,a,l=[],u=e.split(/ +/);for(t=0,n=u.length;n>t;t++){if(r=o(u[t]),this.settings.diacritics)for(a in s)s.hasOwnProperty(a)&&(r=r.replace(new RegExp(a,"g"),s[a]));l.push({string:u[t],regex:new RegExp(r,"i")})}return l},e.prototype.iterator=function(e,t){var n;n=r(e)?Array.prototype.forEach||function(e){for(var t=0,n=this.length;n>t;t++)e(this[t],t,this)}:function(e){for(var t in this)this.hasOwnProperty(t)&&e(this[t],t,this)},n.apply(e,[t])},e.prototype.getScoreFunction=function(e,t){var n,i,o,r;n=this,e=n.prepareSearch(e,t),o=e.tokens,i=e.options.fields,r=o.length;var s=function(e,t){var n,i;return e?(e=String(e||""),i=e.search(t.regex),-1===i?0:(n=t.string.length/e.length,0===i&&(n+=.5),n)):0},a=function(){var e=i.length;return e?1===e?function(e,t){return s(t[i[0]],e)}:function(t,n){for(var o=0,r=0;e>o;o++)r+=s(n[i[o]],t);return r/e}:function(){return 0}}();return r?1===r?function(e){return a(o[0],e)}:"and"===e.options.conjunction?function(e){for(var t,n=0,i=0;r>n;n++){if(t=a(o[n],e),0>=t)return 0;i+=t}return i/r}:function(e){for(var t=0,n=0;r>t;t++)n+=a(o[t],e);return n/r}:function(){return 0}},e.prototype.getSortFunction=function(e,n){var i,o,r,s,a,l,u,p,c,d,h;if(r=this,e=r.prepareSearch(e,n),h=!e.query&&n.sort_empty||n.sort,c=function(e,t){return"$score"===e?t.score:r.items[t.id][e]},a=[],h)for(i=0,o=h.length;o>i;i++)(e.query||"$score"!==h[i].field)&&a.push(h[i]);if(e.query){for(d=!0,i=0,o=a.length;o>i;i++)if("$score"===a[i].field){d=!1;break}d&&a.unshift({field:"$score",direction:"desc"})}else for(i=0,o=a.length;o>i;i++)if("$score"===a[i].field){a.splice(i,1);break}for(p=[],i=0,o=a.length;o>i;i++)p.push("desc"===a[i].direction?-1:1);return l=a.length,l?1===l?(s=a[0].field,u=p[0],function(e,n){return u*t(c(s,e),c(s,n))}):function(e,n){var i,o,r;for(i=0;l>i;i++)if(r=a[i].field,o=p[i]*t(c(r,e),c(r,n)))return o;return 0}:null},e.prototype.prepareSearch=function(e,t){if("object"==typeof e)return e;t=n({},t);var i=t.fields,o=t.sort,s=t.sort_empty;return i&&!r(i)&&(t.fields=[i]),o&&!r(o)&&(t.sort=[o]),s&&!r(s)&&(t.sort_empty=[s]),{options:t,query:String(e||"").toLowerCase(),tokens:this.tokenize(e),total:0,items:[]}},e.prototype.search=function(e,t){var n,i,o,r,s=this;return i=this.prepareSearch(e,t),t=i.options,e=i.query,r=t.score||s.getScoreFunction(i),e.length?s.iterator(s.items,function(e,o){n=r(e),(t.filter===!1||n>0)&&i.items.push({score:n,id:o})}):s.iterator(s.items,function(e,t){i.items.push({score:1,id:t})}),o=s.getSortFunction(i,t),o&&i.items.sort(o),i.total=i.items.length,"number"==typeof t.limit&&(i.items=i.items.slice(0,t.limit)),i};var t=function(e,t){return"number"==typeof e&&"number"==typeof t?e>t?1:t>e?-1:0:(e=String(e||"").toLowerCase(),t=String(t||"").toLowerCase(),e>t?1:t>e?-1:0)},n=function(e){var t,n,i,o;for(t=1,n=arguments.length;n>t;t++)if(o=arguments[t])for(i in o)o.hasOwnProperty(i)&&(e[i]=o[i]);return e},i=function(e){return(e+"").replace(/^\s+|\s+$|/g,"")},o=function(e){return(e+"").replace(/([.?*+^$[\]\\(){}|-])/g,"\\$1")},r=Array.isArray||$&&$.isArray||function(e){return"[object Array]"===Object.prototype.toString.call(e)},s={a:"[aÀÁÂÃÄÅàáâãäå]",c:"[cÇçćĆčČ]",d:"[dđĐ]",e:"[eÈÉÊËèéêë]",i:"[iÌÍÎÏìíîï]",n:"[nÑñ]",o:"[oÒÓÔÕÕÖØòóôõöø]",s:"[sŠš]",u:"[uÙÚÛÜùúûü]",y:"[yŸÿý]",z:"[zŽž]"};return e}),function(e,t){"function"==typeof define&&define.amd?define("microplugin",t):"object"==typeof exports?module.exports=t():e.MicroPlugin=t()}(this,function(){var e={};e.mixin=function(e){e.plugins={},e.prototype.initializePlugins=function(e){var n,i,o,r=this,s=[];if(r.plugins={names:[],settings:{},requested:{},loaded:{}},t.isArray(e))for(n=0,i=e.length;i>n;n++)"string"==typeof e[n]?s.push(e[n]):(r.plugins.settings[e[n].name]=e[n].options,s.push(e[n].name));else if(e)for(o in e)e.hasOwnProperty(o)&&(r.plugins.settings[o]=e[o],s.push(o));for(;s.length;)r.require(s.shift())},e.prototype.loadPlugin=function(t){var n=this,i=n.plugins,o=e.plugins[t];if(!e.plugins.hasOwnProperty(t))throw new Error('Unable to find "'+t+'" plugin');i.requested[t]=!0,i.loaded[t]=o.fn.apply(n,[n.plugins.settings[t]||{}]),i.names.push(t)},e.prototype.require=function(e){var t=this,n=t.plugins;if(!t.plugins.loaded.hasOwnProperty(e)){if(n.requested[e])throw new Error('Plugin has circular dependency ("'+e+'")');t.loadPlugin(e)}return n.loaded[e]},e.define=function(t,n){e.plugins[t]={name:t,fn:n}}};var t={isArray:Array.isArray||function(e){return"[object Array]"===Object.prototype.toString.call(e)}};return e}),function(e,t){"function"==typeof define&&define.amd?define("selectize",["jquery","sifter","microplugin"],t):e.Selectize=t(e.jQuery,e.Sifter,e.MicroPlugin)}(this,function(e,t,n){"use strict";var i=function(e,t){if("string"!=typeof t||t.length){var n="string"==typeof t?new RegExp(t,"i"):t,i=function(e){var t=0;if(3===e.nodeType){var o=e.data.search(n);if(o>=0&&e.data.length>0){var r=e.data.match(n),s=document.createElement("span");s.className="highlight";var a=e.splitText(o),l=(a.splitText(r[0].length),a.cloneNode(!0));s.appendChild(l),a.parentNode.replaceChild(s,a),t=1}}else if(1===e.nodeType&&e.childNodes&&!/(script|style)/i.test(e.tagName))for(var u=0;u<e.childNodes.length;++u)u+=i(e.childNodes[u]);return t};return e.each(function(){i(this)})}},o=function(){};o.prototype={on:function(e,t){this._events=this._events||{},this._events[e]=this._events[e]||[],this._events[e].push(t)},off:function(e,t){var n=arguments.length;return 0===n?delete this._events:1===n?delete this._events[e]:(this._events=this._events||{},void(e in this._events!=!1&&this._events[e].splice(this._events[e].indexOf(t),1)))},trigger:function(e){if(this._events=this._events||{},e in this._events!=!1)for(var t=0;t<this._events[e].length;t++)this._events[e][t].apply(this,Array.prototype.slice.call(arguments,1))}},o.mixin=function(e){for(var t=["on","off","trigger"],n=0;n<t.length;n++)e.prototype[t[n]]=o.prototype[t[n]]};var r=/Mac/.test(navigator.userAgent),s=65,a=13,l=27,u=37,p=38,c=39,d=40,h=8,g=46,f=16,v=r?91:17,m=r?18:17,y=9,w=1,$=2,O=function(e){return"undefined"!=typeof e},C=function(e){return"undefined"==typeof e||null===e?"":"boolean"==typeof e?e?"1":"0":e+""},b=function(e){return(e+"").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")},S=function(e){return(e+"").replace(/\$/g,"$$$$")},x={};x.before=function(e,t,n){var i=e[t];e[t]=function(){return n.apply(e,arguments),i.apply(e,arguments)}},x.after=function(e,t,n){var i=e[t];e[t]=function(){var t=i.apply(e,arguments);return n.apply(e,arguments),t}};var I=function(t,n){if(!e.isArray(n))return n;var i,o,r={};for(i=0,o=n.length;o>i;i++)n[i].hasOwnProperty(t)&&(r[n[i][t]]=n[i]);return r},_=function(e){var t=!1;return function(){t||(t=!0,e.apply(this,arguments))}},D=function(e,t){var n;return function(){var i=this,o=arguments;window.clearTimeout(n),n=window.setTimeout(function(){e.apply(i,o)},t)}},A=function(t,n,i){var o,r=t.trigger,s={};t.trigger=function(){var i=arguments[0];return-1===e.inArray(i,n)?r.apply(t,arguments):void(s[i]=arguments)},i.apply(t,[]),t.trigger=r;for(o in s)s.hasOwnProperty(o)&&r.apply(t,s[o])},F=function(e,t,n,i){e.on(t,n,function(t){for(var n=t.target;n&&n.parentNode!==e[0];)n=n.parentNode;return t.currentTarget=n,i.apply(this,[t])})},k=function(e){var t={};if("selectionStart"in e)t.start=e.selectionStart,t.length=e.selectionEnd-t.start;else if(document.selection){e.focus();var n=document.selection.createRange(),i=document.selection.createRange().text.length;n.moveStart("character",-e.value.length),t.start=n.text.length-i,t.length=i}return t},P=function(e,t,n){var i,o,r={};if(n)for(i=0,o=n.length;o>i;i++)r[n[i]]=e.css(n[i]);else r=e.css();t.css(r)},z=function(t,n){var i=e("<test>").css({position:"absolute",top:-99999,left:-99999,width:"auto",padding:0,whiteSpace:"pre"}).text(t).appendTo("body");P(n,i,["letterSpacing","fontSize","fontFamily","fontWeight","textTransform"]);var o=i.width();return i.remove(),o},T=function(e){var t=function(t){var n,i,o,r,s,a,l,u;t=t||window.event||{},t.metaKey||t.altKey||e.data("grow")!==!1&&(n=e.val(),t.type&&"keydown"===t.type.toLowerCase()&&(i=t.keyCode,o=i>=97&&122>=i||i>=65&&90>=i||i>=48&&57>=i||32===i,i===g||i===h?(u=k(e[0]),u.length?n=n.substring(0,u.start)+n.substring(u.start+u.length):i===h&&u.start?n=n.substring(0,u.start-1)+n.substring(u.start+1):i===g&&"undefined"!=typeof u.start&&(n=n.substring(0,u.start)+n.substring(u.start+1))):o&&(a=t.shiftKey,l=String.fromCharCode(t.keyCode),l=a?l.toUpperCase():l.toLowerCase(),n+=l)),r=e.attr("placeholder")||"",!n.length&&r.length&&(n=r),s=z(n,e)+4,s!==e.width()&&(e.width(s),e.triggerHandler("resize")))};e.on("keydown keyup update blur",t),t()},j=function(n,i){var o,r,s=this;r=n[0],r.selectize=s,o=window.getComputedStyle?window.getComputedStyle(r,null).getPropertyValue("direction"):r.currentStyle&&r.currentStyle.direction,o=o||n.parents("[dir]:first").attr("dir")||"",e.extend(s,{settings:i,$input:n,tagType:"select"===r.tagName.toLowerCase()?w:$,rtl:/rtl/i.test(o),eventNS:".selectize"+ ++j.count,highlightedValue:null,isOpen:!1,isDisabled:!1,isRequired:n.is("[required]"),isInvalid:!1,isLocked:!1,isFocused:!1,isInputHidden:!1,isSetup:!1,isShiftDown:!1,isCmdDown:!1,isCtrlDown:!1,ignoreFocus:!1,ignoreHover:!1,hasOptions:!1,currentResults:null,lastValue:"",caretPos:0,loading:0,loadedSearches:{},$activeOption:null,$activeItems:[],optgroups:{},options:{},userOptions:{},items:[],renderCache:{},onSearchChange:D(s.onSearchChange,i.loadThrottle)}),s.sifter=new t(this.options,{diacritics:i.diacritics}),e.extend(s.options,I(i.valueField,i.options)),delete s.settings.options,e.extend(s.optgroups,I(i.optgroupValueField,i.optgroups)),delete s.settings.optgroups,s.settings.mode=s.settings.mode||(1===s.settings.maxItems?"single":"multi"),"boolean"!=typeof s.settings.hideSelected&&(s.settings.hideSelected="multi"===s.settings.mode),s.initializePlugins(s.settings.plugins),s.setupCallbacks(),s.setupTemplates(),s.setup()};return o.mixin(j),n.mixin(j),e.extend(j.prototype,{setup:function(){var t,n,i,o,s,a,l,u,p,c,d=this,h=d.settings,g=d.eventNS,y=e(window),$=e(document);l=d.settings.mode,u=d.$input.attr("tabindex")||"",p=d.$input.attr("class")||"",t=e("<div>").addClass(h.wrapperClass).addClass(p).addClass(l),n=e("<div>").addClass(h.inputClass).addClass("items").appendTo(t),i=e('<input type="text" autocomplete="off">').appendTo(n).attr("tabindex",u),a=e(h.dropdownParent||t),o=e("<div>").addClass(h.dropdownClass).addClass(p).addClass(l).hide().appendTo(a),s=e("<div>").addClass(h.dropdownContentClass).appendTo(o),t.css({width:d.$input[0].style.width}),d.plugins.names.length&&(c="plugin-"+d.plugins.names.join(" plugin-"),t.addClass(c),o.addClass(c)),(null===h.maxItems||h.maxItems>1)&&d.tagType===w&&d.$input.attr("multiple","multiple"),d.settings.placeholder&&i.attr("placeholder",h.placeholder),d.$wrapper=t,d.$control=n,d.$control_input=i,d.$dropdown=o,d.$dropdown_content=s,o.on("mouseenter","[data-selectable]",function(){return d.onOptionHover.apply(d,arguments)}),o.on("mousedown","[data-selectable]",function(){return d.onOptionSelect.apply(d,arguments)}),F(n,"mousedown","*:not(input)",function(){return d.onItemSelect.apply(d,arguments)}),T(i),n.on({mousedown:function(){return d.onMouseDown.apply(d,arguments)},click:function(){return d.onClick.apply(d,arguments)}}),i.on({mousedown:function(e){e.stopPropagation()},keydown:function(){return d.onKeyDown.apply(d,arguments)},keyup:function(){return d.onKeyUp.apply(d,arguments)},keypress:function(){return d.onKeyPress.apply(d,arguments)},resize:function(){d.positionDropdown.apply(d,[])},blur:function(){return d.onBlur.apply(d,arguments)},focus:function(){return d.onFocus.apply(d,arguments)}}),$.on("keydown"+g,function(e){d.isCmdDown=e[r?"metaKey":"ctrlKey"],d.isCtrlDown=e[r?"altKey":"ctrlKey"],d.isShiftDown=e.shiftKey}),$.on("keyup"+g,function(e){e.keyCode===m&&(d.isCtrlDown=!1),e.keyCode===f&&(d.isShiftDown=!1),e.keyCode===v&&(d.isCmdDown=!1)}),$.on("mousedown"+g,function(e){if(d.isFocused){if(e.target===d.$dropdown[0]||e.target.parentNode===d.$dropdown[0])return!1;d.$control.has(e.target).length||e.target===d.$control[0]||d.blur()}}),y.on(["scroll"+g,"resize"+g].join(" "),function(){d.isOpen&&d.positionDropdown.apply(d,arguments)}),y.on("mousemove"+g,function(){d.ignoreHover=!1}),this.revertSettings={$children:d.$input.children().detach(),tabindex:d.$input.attr("tabindex")},d.$input.attr("tabindex",-1).hide().after(d.$wrapper),e.isArray(h.items)&&(d.setValue(h.items),delete h.items),d.$input[0].validity&&d.$input.on("invalid"+g,function(e){e.preventDefault(),d.isInvalid=!0,d.refreshState()}),d.updateOriginalInput(),d.refreshItems(),d.refreshState(),d.updatePlaceholder(),d.isSetup=!0,d.$input.is(":disabled")&&d.disable(),d.on("change",this.onChange),d.trigger("initialize"),h.preload&&d.onSearchChange("")},setupTemplates:function(){var t=this,n=t.settings.labelField,i=t.settings.optgroupLabelField,o={optgroup:function(e){return'<div class="optgroup">'+e.html+"</div>"},optgroup_header:function(e,t){return'<div class="optgroup-header">'+t(e[i])+"</div>"},option:function(e,t){return'<div class="option">'+t(e[n])+"</div>"},item:function(e,t){return'<div class="item">'+t(e[n])+"</div>"},option_create:function(e,t){return'<div class="create">Add <strong>'+t(e.input)+"</strong>&hellip;</div>"}};t.settings.render=e.extend({},o,t.settings.render)},setupCallbacks:function(){var e,t,n={initialize:"onInitialize",change:"onChange",item_add:"onItemAdd",item_remove:"onItemRemove",clear:"onClear",option_add:"onOptionAdd",option_remove:"onOptionRemove",option_clear:"onOptionClear",dropdown_open:"onDropdownOpen",dropdown_close:"onDropdownClose",type:"onType"};for(e in n)n.hasOwnProperty(e)&&(t=this.settings[n[e]],t&&this.on(e,t))},onClick:function(e){var t=this;t.isFocused||(t.focus(),e.preventDefault())},onMouseDown:function(t){{var n=this,i=t.isDefaultPrevented();e(t.target)}if(n.isFocused){if(t.target!==n.$control_input[0])return"single"===n.settings.mode?n.isOpen?n.close():n.open():i||n.setActiveItem(null),!1}else i||window.setTimeout(function(){n.focus()},0)},onChange:function(){this.$input.trigger("change")},onKeyPress:function(e){if(this.isLocked)return e&&e.preventDefault();var t=String.fromCharCode(e.keyCode||e.which);return this.settings.create&&t===this.settings.delimiter?(this.createItem(),e.preventDefault(),!1):void 0},onKeyDown:function(e){var t=(e.target===this.$control_input[0],this);if(t.isLocked)return void(e.keyCode!==y&&e.preventDefault());switch(e.keyCode){case s:if(t.isCmdDown)return void t.selectAll();break;case l:return void t.close();case d:if(!t.isOpen&&t.hasOptions)t.open();else if(t.$activeOption){t.ignoreHover=!0;var n=t.getAdjacentOption(t.$activeOption,1);n.length&&t.setActiveOption(n,!0,!0)}return void e.preventDefault();case p:if(t.$activeOption){t.ignoreHover=!0;var i=t.getAdjacentOption(t.$activeOption,-1);i.length&&t.setActiveOption(i,!0,!0)}return void e.preventDefault();case a:return t.isOpen&&t.$activeOption&&t.onOptionSelect({currentTarget:t.$activeOption}),void e.preventDefault();case u:return void t.advanceSelection(-1,e);case c:return void t.advanceSelection(1,e);case y:return void(t.settings.create&&t.createItem()&&e.preventDefault());case h:case g:return void t.deleteSelection(e)}return t.isFull()||t.isInputHidden?void e.preventDefault():void 0},onKeyUp:function(e){var t=this;if(t.isLocked)return e&&e.preventDefault();var n=t.$control_input.val()||"";t.lastValue!==n&&(t.lastValue=n,t.onSearchChange(n),t.refreshOptions(),t.trigger("type",n))},onSearchChange:function(e){var t=this,n=t.settings.load;n&&(t.loadedSearches.hasOwnProperty(e)||(t.loadedSearches[e]=!0,t.load(function(i){n.apply(t,[e,i])})))},onFocus:function(e){var t=this;return t.isFocused=!0,t.isDisabled?(t.blur(),e&&e.preventDefault(),!1):void(t.ignoreFocus||("focus"===t.settings.preload&&t.onSearchChange(""),t.$activeItems.length||(t.showInput(),t.setActiveItem(null),t.refreshOptions(!!t.settings.openOnFocus)),t.refreshState()))},onBlur:function(){var e=this;e.isFocused=!1,e.ignoreFocus||(e.settings.create&&e.settings.createOnBlur&&e.createItem(),e.close(),e.setTextboxValue(""),e.setActiveItem(null),e.setActiveOption(null),e.setCaret(e.items.length),e.refreshState())},onOptionHover:function(e){this.ignoreHover||this.setActiveOption(e.currentTarget,!1)},onOptionSelect:function(t){var n,i,o=this;t.preventDefault&&(t.preventDefault(),t.stopPropagation()),i=e(t.currentTarget),i.hasClass("create")?o.createItem():(n=i.attr("data-value"),n&&(o.lastQuery=null,o.setTextboxValue(""),o.addItem(n),!o.settings.hideSelected&&t.type&&/mouse/.test(t.type)&&o.setActiveOption(o.getOption(n))))},onItemSelect:function(e){var t=this;t.isLocked||"multi"===t.settings.mode&&(e.preventDefault(),t.setActiveItem(e.currentTarget,e))},load:function(e){var t=this,n=t.$wrapper.addClass("loading");t.loading++,e.apply(t,[function(e){t.loading=Math.max(t.loading-1,0),e&&e.length&&(t.addOption(e),t.refreshOptions(t.isFocused&&!t.isInputHidden)),t.loading||n.removeClass("loading"),t.trigger("load",e)}])},setTextboxValue:function(e){this.$control_input.val(e).triggerHandler("update"),this.lastValue=e},getValue:function(){return this.tagType===w&&this.$input.attr("multiple")?this.items:this.items.join(this.settings.delimiter)},setValue:function(t){A(this,["change"],function(){this.clear();for(var n=e.isArray(t)?t:[t],i=0,o=n.length;o>i;i++)this.addItem(n[i])})},setActiveItem:function(t,n){var i,o,r,s,a,l,u,p,c=this;if("single"!==c.settings.mode){if(t=e(t),!t.length)return e(c.$activeItems).removeClass("active"),c.$activeItems=[],void(c.isFocused&&c.showInput());if(i=n&&n.type.toLowerCase(),"mousedown"===i&&c.isShiftDown&&c.$activeItems.length){for(p=c.$control.children(".active:last"),s=Array.prototype.indexOf.apply(c.$control[0].childNodes,[p[0]]),a=Array.prototype.indexOf.apply(c.$control[0].childNodes,[t[0]]),s>a&&(u=s,s=a,a=u),o=s;a>=o;o++)l=c.$control[0].childNodes[o],-1===c.$activeItems.indexOf(l)&&(e(l).addClass("active"),c.$activeItems.push(l));n.preventDefault()}else"mousedown"===i&&c.isCtrlDown||"keydown"===i&&this.isShiftDown?t.hasClass("active")?(r=c.$activeItems.indexOf(t[0]),c.$activeItems.splice(r,1),t.removeClass("active")):c.$activeItems.push(t.addClass("active")[0]):(e(c.$activeItems).removeClass("active"),c.$activeItems=[t.addClass("active")[0]]);c.hideInput(),this.isFocused||c.focus()}},setActiveOption:function(t,n,i){var o,r,s,a,l,u=this;u.$activeOption&&u.$activeOption.removeClass("active"),u.$activeOption=null,t=e(t),t.length&&(u.$activeOption=t.addClass("active"),(n||!O(n))&&(o=u.$dropdown_content.height(),r=u.$activeOption.outerHeight(!0),n=u.$dropdown_content.scrollTop()||0,s=u.$activeOption.offset().top-u.$dropdown_content.offset().top+n,a=s,l=s-o+r,s+r>o+n?u.$dropdown_content.stop().animate({scrollTop:l},i?u.settings.scrollDuration:0):n>s&&u.$dropdown_content.stop().animate({scrollTop:a},i?u.settings.scrollDuration:0)))},selectAll:function(){var e=this;"single"!==e.settings.mode&&(e.$activeItems=Array.prototype.slice.apply(e.$control.children(":not(input)").addClass("active")),e.$activeItems.length&&(e.hideInput(),e.close()),e.focus())},hideInput:function(){var e=this;e.setTextboxValue(""),e.$control_input.css({opacity:0,position:"absolute",left:e.rtl?1e4:-1e4}),e.isInputHidden=!0},showInput:function(){this.$control_input.css({opacity:1,position:"relative",left:0}),this.isInputHidden=!1},focus:function(){var e=this;e.isDisabled||(e.ignoreFocus=!0,e.$control_input[0].focus(),window.setTimeout(function(){e.ignoreFocus=!1,e.onFocus()},0))},blur:function(){this.$control_input.trigger("blur")},getScoreFunction:function(e){return this.sifter.getScoreFunction(e,this.getSearchOptions())},getSearchOptions:function(){var e=this.settings,t=e.sortField;return"string"==typeof t&&(t={field:t}),{fields:e.searchField,conjunction:e.searchConjunction,sort:t}},search:function(t){var n,i,o,r=this,s=r.settings,a=this.getSearchOptions();if(s.score&&(o=r.settings.score.apply(this,[t]),"function"!=typeof o))throw new Error('Selectize "score" setting must be a function that returns a function');if(t!==r.lastQuery?(r.lastQuery=t,i=r.sifter.search(t,e.extend(a,{score:o})),r.currentResults=i):i=e.extend(!0,{},r.currentResults),s.hideSelected)for(n=i.items.length-1;n>=0;n--)-1!==r.items.indexOf(C(i.items[n].id))&&i.items.splice(n,1);return i},refreshOptions:function(t){var n,o,r,s,a,l,u,p,c,d,h,g,f,v,m,y;"undefined"==typeof t&&(t=!0);var w=this,$=w.$control_input.val(),O=w.search($),b=w.$dropdown_content,S=w.$activeOption&&C(w.$activeOption.attr("data-value"));if(s=O.items.length,"number"==typeof w.settings.maxOptions&&(s=Math.min(s,w.settings.maxOptions)),a={},w.settings.optgroupOrder)for(l=w.settings.optgroupOrder,n=0;n<l.length;n++)a[l[n]]=[];else l=[];for(n=0;s>n;n++)for(u=w.options[O.items[n].id],p=w.render("option",u),c=u[w.settings.optgroupField]||"",d=e.isArray(c)?c:[c],o=0,r=d&&d.length;r>o;o++)c=d[o],w.optgroups.hasOwnProperty(c)||(c=""),a.hasOwnProperty(c)||(a[c]=[],l.push(c)),a[c].push(p);for(h=[],n=0,s=l.length;s>n;n++)c=l[n],w.optgroups.hasOwnProperty(c)&&a[c].length?(g=w.render("optgroup_header",w.optgroups[c])||"",g+=a[c].join(""),h.push(w.render("optgroup",e.extend({},w.optgroups[c],{html:g})))):h.push(a[c].join(""));if(b.html(h.join("")),w.settings.highlight&&O.query.length&&O.tokens.length)for(n=0,s=O.tokens.length;s>n;n++)i(b,O.tokens[n].regex);if(!w.settings.hideSelected)for(n=0,s=w.items.length;s>n;n++)w.getOption(w.items[n]).addClass("selected");f=w.settings.create&&O.query.length,f&&(b.prepend(w.render("option_create",{input:$})),y=e(b[0].childNodes[0])),w.hasOptions=O.items.length>0||f,w.hasOptions?(O.items.length>0?(m=S&&w.getOption(S),m&&m.length?v=m:"single"===w.settings.mode&&w.items.length&&(v=w.getOption(w.items[0])),v&&v.length||(v=y&&!w.settings.addPrecedence?w.getAdjacentOption(y,1):b.find("[data-selectable]:first"))):v=y,w.setActiveOption(v),t&&!w.isOpen&&w.open()):(w.setActiveOption(null),t&&w.isOpen&&w.close())},addOption:function(t){var n,i,o,r=this;if(e.isArray(t))for(n=0,i=t.length;i>n;n++)r.addOption(t[n]);else o=C(t[r.settings.valueField]),o&&!r.options.hasOwnProperty(o)&&(r.userOptions[o]=!0,r.options[o]=t,r.lastQuery=null,r.trigger("option_add",o,t))},addOptionGroup:function(e,t){this.optgroups[e]=t,this.trigger("optgroup_add",e,t)},updateOption:function(t,n){var i,o,r,s,a,l,u=this;if(t=C(t),r=C(n[u.settings.valueField]),u.options.hasOwnProperty(t)){if(!r)throw new Error("Value must be set in option data");r!==t&&(delete u.options[t],s=u.items.indexOf(t),-1!==s&&u.items.splice(s,1,r)),u.options[r]=n,a=u.renderCache.item,l=u.renderCache.option,O(a)&&(delete a[t],delete a[r]),O(l)&&(delete l[t],delete l[r]),-1!==u.items.indexOf(r)&&(i=u.getItem(t),o=e(u.render("item",n)),i.hasClass("active")&&o.addClass("active"),i.replaceWith(o)),u.isOpen&&u.refreshOptions(!1)}},removeOption:function(e){var t=this;e=C(e),delete t.userOptions[e],delete t.options[e],t.lastQuery=null,t.trigger("option_remove",e),t.removeItem(e)},clearOptions:function(){var e=this;e.loadedSearches={},e.userOptions={},e.options=e.sifter.items={},e.lastQuery=null,e.trigger("option_clear"),e.clear()},getOption:function(e){return this.getElementWithValue(e,this.$dropdown_content.find("[data-selectable]"))},getAdjacentOption:function(t,n){var i=this.$dropdown.find("[data-selectable]"),o=i.index(t)+n;return o>=0&&o<i.length?i.eq(o):e()},getElementWithValue:function(t,n){if(t=C(t))for(var i=0,o=n.length;o>i;i++)if(n[i].getAttribute("data-value")===t)return e(n[i]);return e()},getItem:function(e){return this.getElementWithValue(e,this.$control.children())},addItem:function(t){A(this,["change"],function(){var n,i,o,r,s=this,a=s.settings.mode;return t=C(t),-1!==e.inArray(t,s.items)?void("single"===a&&s.close()):void(s.options.hasOwnProperty(t)&&("single"===a&&s.clear(),"multi"===a&&s.isFull()||(n=e(s.render("item",s.options[t])),s.items.splice(s.caretPos,0,t),s.insertAtCaret(n),s.refreshState(),s.isSetup&&(o=s.$dropdown_content.find("[data-selectable]"),i=s.getOption(t),r=s.getAdjacentOption(i,1).attr("data-value"),s.refreshOptions(s.isFocused&&"single"!==a),r&&s.setActiveOption(s.getOption(r)),!o.length||null!==s.settings.maxItems&&s.items.length>=s.settings.maxItems?s.close():s.positionDropdown(),s.updatePlaceholder(),s.trigger("item_add",t,n),s.updateOriginalInput()))))})},removeItem:function(e){var t,n,i,o=this;t="object"==typeof e?e:o.getItem(e),e=C(t.attr("data-value")),n=o.items.indexOf(e),-1!==n&&(t.remove(),t.hasClass("active")&&(i=o.$activeItems.indexOf(t[0]),o.$activeItems.splice(i,1)),o.items.splice(n,1),o.lastQuery=null,!o.settings.persist&&o.userOptions.hasOwnProperty(e)&&o.removeOption(e),n<o.caretPos&&o.setCaret(o.caretPos-1),o.refreshState(),o.updatePlaceholder(),o.updateOriginalInput(),o.positionDropdown(),o.trigger("item_remove",e))},createItem:function(){var t=this,n=e.trim(t.$control_input.val()||""),i=t.caretPos;if(!n.length)return!1;t.lock();var o="function"==typeof t.settings.create?this.settings.create:function(e){var n={};return n[t.settings.labelField]=e,n[t.settings.valueField]=e,n},r=_(function(e){if(t.unlock(),e&&"object"==typeof e){var n=C(e[t.settings.valueField]);n&&(t.setTextboxValue(""),t.addOption(e),t.setCaret(i),t.addItem(n),t.refreshOptions("single"!==t.settings.mode))}}),s=o.apply(this,[n,r]);return"undefined"!=typeof s&&r(s),!0},refreshItems:function(){if(this.lastQuery=null,this.isSetup)for(var e=0;e<this.items.length;e++)this.addItem(this.items);this.refreshState(),this.updateOriginalInput()},refreshState:function(){var e=this,t=e.isRequired&&!e.items.length;t||(e.isInvalid=!1),e.$control_input.prop("required",t),e.refreshClasses()},refreshClasses:function(){var t=this,n=t.isFull(),i=t.isLocked;t.$wrapper.toggleClass("rtl",t.rtl),t.$control.toggleClass("focus",t.isFocused).toggleClass("disabled",t.isDisabled).toggleClass("required",t.isRequired).toggleClass("invalid",t.isInvalid).toggleClass("locked",i).toggleClass("full",n).toggleClass("not-full",!n).toggleClass("input-active",t.isFocused&&!t.isInputHidden).toggleClass("dropdown-active",t.isOpen).toggleClass("has-options",!e.isEmptyObject(t.options)).toggleClass("has-items",t.items.length>0),t.$control_input.data("grow",!n&&!i)},isFull:function(){return null!==this.settings.maxItems&&this.items.length>=this.settings.maxItems},updateOriginalInput:function(){var e,t,n,i=this;if("select"===i.$input[0].tagName.toLowerCase()){for(n=[],e=0,t=i.items.length;t>e;e++)n.push('<option value="'+b(i.items[e])+'" selected="selected"></option>');n.length||this.$input.attr("multiple")||n.push('<option value="" selected="selected"></option>'),i.$input.html(n.join(""))}else i.$input.val(i.getValue());i.isSetup&&i.trigger("change",i.$input.val())},updatePlaceholder:function(){if(this.settings.placeholder){var e=this.$control_input;this.items.length?e.removeAttr("placeholder"):e.attr("placeholder",this.settings.placeholder),e.triggerHandler("update")}},open:function(){var e=this;e.isLocked||e.isOpen||"multi"===e.settings.mode&&e.isFull()||(e.focus(),e.isOpen=!0,e.refreshState(),e.$dropdown.css({visibility:"hidden",display:"block"}),e.positionDropdown(),e.$dropdown.css({visibility:"visible"}),e.trigger("dropdown_open",e.$dropdown))},close:function(){var e=this,t=e.isOpen;"single"===e.settings.mode&&e.items.length&&e.hideInput(),e.isOpen=!1,e.$dropdown.hide(),e.setActiveOption(null),e.refreshState(),t&&e.trigger("dropdown_close",e.$dropdown)},positionDropdown:function(){var e=this.$control,t="body"===this.settings.dropdownParent?e.offset():e.position();t.top+=e.outerHeight(!0),this.$dropdown.css({width:e.outerWidth(),top:t.top,left:t.left})},clear:function(){var e=this;e.items.length&&(e.$control.children(":not(input)").remove(),e.items=[],e.setCaret(0),e.updatePlaceholder(),e.updateOriginalInput(),e.refreshState(),e.showInput(),e.trigger("clear"))},insertAtCaret:function(t){var n=Math.min(this.caretPos,this.items.length);0===n?this.$control.prepend(t):e(this.$control[0].childNodes[n]).before(t),this.setCaret(n+1)},deleteSelection:function(t){var n,i,o,r,s,a,l,u,p,c=this;if(o=t&&t.keyCode===h?-1:1,r=k(c.$control_input[0]),c.$activeOption&&!c.settings.hideSelected&&(l=c.getAdjacentOption(c.$activeOption,-1).attr("data-value")),s=[],c.$activeItems.length){for(p=c.$control.children(".active:"+(o>0?"last":"first")),a=c.$control.children(":not(input)").index(p),o>0&&a++,n=0,i=c.$activeItems.length;i>n;n++)s.push(e(c.$activeItems[n]).attr("data-value"));t&&(t.preventDefault(),t.stopPropagation())}else(c.isFocused||"single"===c.settings.mode)&&c.items.length&&(0>o&&0===r.start&&0===r.length?s.push(c.items[c.caretPos-1]):o>0&&r.start===c.$control_input.val().length&&s.push(c.items[c.caretPos]));if(!s.length||"function"==typeof c.settings.onDelete&&c.settings.onDelete.apply(c,[s])===!1)return!1;for("undefined"!=typeof a&&c.setCaret(a);s.length;)c.removeItem(s.pop());return c.showInput(),c.positionDropdown(),c.refreshOptions(!0),l&&(u=c.getOption(l),u.length&&c.setActiveOption(u)),!0},advanceSelection:function(e,t){var n,i,o,r,s,a,l=this;0!==e&&(l.rtl&&(e*=-1),n=e>0?"last":"first",i=k(l.$control_input[0]),l.isFocused&&!l.isInputHidden?(r=l.$control_input.val().length,s=0>e?0===i.start&&0===i.length:i.start===r,s&&!r&&l.advanceCaret(e,t)):(a=l.$control.children(".active:"+n),a.length&&(o=l.$control.children(":not(input)").index(a),l.setActiveItem(null),l.setCaret(e>0?o+1:o))))},advanceCaret:function(e,t){var n,i,o=this;0!==e&&(n=e>0?"next":"prev",o.isShiftDown?(i=o.$control_input[n](),i.length&&(o.hideInput(),o.setActiveItem(i),t&&t.preventDefault())):o.setCaret(o.caretPos+e))},setCaret:function(t){var n=this;t="single"===n.settings.mode?n.items.length:Math.max(0,Math.min(n.items.length,t));var i,o,r,s;for(r=n.$control.children(":not(input)"),i=0,o=r.length;o>i;i++)s=e(r[i]).detach(),t>i?n.$control_input.before(s):n.$control.append(s);n.caretPos=t},lock:function(){this.close(),this.isLocked=!0,this.refreshState()},unlock:function(){this.isLocked=!1,this.refreshState()},disable:function(){var e=this;e.$input.prop("disabled",!0),e.isDisabled=!0,e.lock()},enable:function(){var e=this;e.$input.prop("disabled",!1),e.isDisabled=!1,e.unlock()},destroy:function(){var t=this,n=t.eventNS,i=t.revertSettings;t.trigger("destroy"),t.off(),t.$wrapper.remove(),t.$dropdown.remove(),t.$input.html("").append(i.$children).removeAttr("tabindex").attr({tabindex:i.tabindex}).show(),e(window).off(n),e(document).off(n),e(document.body).off(n),delete t.$input[0].selectize},render:function(e,t){var n,i,o="",r=!1,s=this,a=/^[\t ]*<([a-z][a-z0-9\-_]*(?:\:[a-z][a-z0-9\-_]*)?)/i;return("option"===e||"item"===e)&&(n=C(t[s.settings.valueField]),r=!!n),r&&(O(s.renderCache[e])||(s.renderCache[e]={}),s.renderCache[e].hasOwnProperty(n))?s.renderCache[e][n]:(o=s.settings.render[e].apply(this,[t,b]),("option"===e||"option_create"===e)&&(o=o.replace(a,"<$1 data-selectable")),"optgroup"===e&&(i=t[s.settings.optgroupValueField]||"",o=o.replace(a,'<$1 data-group="'+S(b(i))+'"')),("option"===e||"item"===e)&&(o=o.replace(a,'<$1 data-value="'+S(b(n||""))+'"')),r&&(s.renderCache[e][n]=o),o)}}),j.count=0,j.defaults={plugins:[],delimiter:",",persist:!0,diacritics:!0,create:!1,createOnBlur:!1,highlight:!0,openOnFocus:!0,maxOptions:1e3,maxItems:null,hideSelected:null,addPrecedence:!1,preload:!1,scrollDuration:60,loadThrottle:300,dataAttr:"data-data",optgroupField:"optgroup",valueField:"value",labelField:"text",optgroupLabelField:"label",optgroupValueField:"value",optgroupOrder:null,sortField:"$order",searchField:["text"],searchConjunction:"and",mode:null,wrapperClass:"selectize-control",inputClass:"selectize-input",dropdownClass:"selectize-dropdown",dropdownContentClass:"selectize-dropdown-content",dropdownParent:null,render:{}},e.fn.selectize=function(t){var n=e.fn.selectize.defaults,i=e.extend({},n,t),o=i.dataAttr,r=i.labelField,s=i.valueField,a=i.optgroupField,l=i.optgroupLabelField,u=i.optgroupValueField,p=function(t,n){var o,a,l,u,p=e.trim(t.val()||"");
if(p.length){for(l=p.split(i.delimiter),o=0,a=l.length;a>o;o++)u={},u[r]=l[o],u[s]=l[o],n.options[l[o]]=u;n.items=l}},c=function(t,n){var i,p,c,d,h=0,g=n.options,f=function(e){var t=o&&e.attr(o);return"string"==typeof t&&t.length?JSON.parse(t):null},v=function(t,i){var o,l;if(t=e(t),o=t.attr("value")||"",o.length){if(g.hasOwnProperty(o))return void(i&&(g[o].optgroup?e.isArray(g[o].optgroup)?g[o].optgroup.push(i):g[o].optgroup=[g[o].optgroup,i]:g[o].optgroup=i));l=f(t)||{},l[r]=l[r]||t.text(),l[s]=l[s]||o,l[a]=l[a]||i,l.$order=++h,g[o]=l,t.is(":selected")&&n.items.push(o)}},m=function(t){var i,o,r,s,a;for(t=e(t),r=t.attr("label"),r&&(s=f(t)||{},s[l]=r,s[u]=r,n.optgroups[r]=s),a=e("option",t),i=0,o=a.length;o>i;i++)v(a[i],r)};for(n.maxItems=t.attr("multiple")?null:1,d=t.children(),i=0,p=d.length;p>i;i++)c=d[i].tagName.toLowerCase(),"optgroup"===c?m(d[i]):"option"===c&&v(d[i])};return this.each(function(){if(!this.selectize){var i,o=e(this),r=this.tagName.toLowerCase(),s={placeholder:o.children('option[value=""]').text()||o.attr("placeholder"),options:{},optgroups:{},items:[]};"select"===r?c(o,s):p(o,s),i=new j(o,e.extend(!0,{},n,s,t)),o.data("selectize",i),o.addClass("selectized")}})},e.fn.selectize.defaults=j.defaults,j.define("drag_drop",function(){if(!e.fn.sortable)throw new Error('The "drag_drop" plugin requires jQuery UI "sortable".');if("multi"===this.settings.mode){var t=this;t.lock=function(){var e=t.lock;return function(){var n=t.$control.data("sortable");return n&&n.disable(),e.apply(t,arguments)}}(),t.unlock=function(){var e=t.unlock;return function(){var n=t.$control.data("sortable");return n&&n.enable(),e.apply(t,arguments)}}(),t.setup=function(){var n=t.setup;return function(){n.apply(this,arguments);var i=t.$control.sortable({items:"[data-value]",forcePlaceholderSize:!0,disabled:t.isLocked,start:function(e,t){t.placeholder.css("width",t.helper.css("width")),i.css({overflow:"visible"})},stop:function(){i.css({overflow:"hidden"});var n=t.$activeItems?t.$activeItems.slice():null,o=[];i.children("[data-value]").each(function(){o.push(e(this).attr("data-value"))}),t.setValue(o),t.setActiveItem(n)}})}}()}}),j.define("dropdown_header",function(t){var n=this;t=e.extend({title:"Untitled",headerClass:"selectize-dropdown-header",titleRowClass:"selectize-dropdown-header-title",labelClass:"selectize-dropdown-header-label",closeClass:"selectize-dropdown-header-close",html:function(e){return'<div class="'+e.headerClass+'"><div class="'+e.titleRowClass+'"><span class="'+e.labelClass+'">'+e.title+'</span><a href="javascript:void(0)" class="'+e.closeClass+'">&times;</a></div></div>'}},t),n.setup=function(){var i=n.setup;return function(){i.apply(n,arguments),n.$dropdown_header=e(t.html(t)),n.$dropdown.prepend(n.$dropdown_header)}}()}),j.define("optgroup_columns",function(t){var n=this;t=e.extend({equalizeWidth:!0,equalizeHeight:!0},t),this.getAdjacentOption=function(t,n){var i=t.closest("[data-group]").find("[data-selectable]"),o=i.index(t)+n;return o>=0&&o<i.length?i.eq(o):e()},this.onKeyDown=function(){var e=n.onKeyDown;return function(t){var i,o,r,s;return!this.isOpen||t.keyCode!==u&&t.keyCode!==c?e.apply(this,arguments):(n.ignoreHover=!0,s=this.$activeOption.closest("[data-group]"),i=s.find("[data-selectable]").index(this.$activeOption),s=t.keyCode===u?s.prev("[data-group]"):s.next("[data-group]"),r=s.find("[data-selectable]"),o=r.eq(Math.min(r.length-1,i)),void(o.length&&this.setActiveOption(o)))}}();var i=function(){var i,o,r,s,a,l,u;if(u=e("[data-group]",n.$dropdown_content),o=u.length,o&&n.$dropdown_content.width()){if(t.equalizeHeight){for(r=0,i=0;o>i;i++)r=Math.max(r,u.eq(i).height());u.css({height:r})}t.equalizeWidth&&(l=n.$dropdown_content.innerWidth(),s=Math.round(l/o),u.css({width:s}),o>1&&(a=l-s*(o-1),u.eq(o-1).css({width:a})))}};(t.equalizeHeight||t.equalizeWidth)&&(x.after(this,"positionDropdown",i),x.after(this,"refreshOptions",i))}),j.define("remove_button",function(t){if("single"!==this.settings.mode){t=e.extend({label:"&times;",title:"Remove",className:"remove",append:!0},t);var n=this,i='<a href="javascript:void(0)" class="'+t.className+'" tabindex="-1" title="'+b(t.title)+'">'+t.label+"</a>",o=function(e,t){var n=e.search(/(<\/[^>]+>\s*)$/);return e.substring(0,n)+t+e.substring(n)};this.setup=function(){var r=n.setup;return function(){if(t.append){var s=n.settings.render.item;n.settings.render.item=function(){return o(s.apply(this,arguments),i)}}r.apply(this,arguments),this.$control.on("click","."+t.className,function(t){if(t.preventDefault(),!n.isLocked){var i=e(t.target).parent();n.setActiveItem(i),n.deleteSelection()&&n.setCaret(n.items.length)}})}}()}}),j.define("restore_on_backspace",function(e){var t=this;e.text=e.text||function(e){return e[this.settings.labelField]},this.onKeyDown=function(){var n=t.onKeyDown;return function(t){var i,o;return t.keyCode===h&&""===this.$control_input.val()&&!this.$activeItems.length&&(i=this.caretPos-1,i>=0&&i<this.items.length)?(o=this.options[this.items[i]],this.deleteSelection(t)&&(this.setTextboxValue(e.text.apply(this,[o])),this.refreshOptions(!0)),void t.preventDefault()):n.apply(this,arguments)}}()}),j});