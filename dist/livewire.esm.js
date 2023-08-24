var __create = Object.create;
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __getProtoOf = Object.getPrototypeOf;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __commonJS = (cb, mod) => function __require() {
  return mod || (0, cb[__getOwnPropNames(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
};
var __copyProps = (to, from, except, desc) => {
  if (from && typeof from === "object" || typeof from === "function") {
    for (let key of __getOwnPropNames(from))
      if (!__hasOwnProp.call(to, key) && key !== except)
        __defProp(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable });
  }
  return to;
};
var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target, mod));

// ../alpine/packages/alpinejs/dist/module.cjs.js
var require_module_cjs = __commonJS({
  "../alpine/packages/alpinejs/dist/module.cjs.js"(exports, module) {
    var __create2 = Object.create;
    var __defProp2 = Object.defineProperty;
    var __getOwnPropDesc2 = Object.getOwnPropertyDescriptor;
    var __getOwnPropNames2 = Object.getOwnPropertyNames;
    var __getProtoOf2 = Object.getPrototypeOf;
    var __hasOwnProp2 = Object.prototype.hasOwnProperty;
    var __commonJS2 = (cb, mod) => function __require() {
      return mod || (0, cb[__getOwnPropNames2(cb)[0]])((mod = { exports: {} }).exports, mod), mod.exports;
    };
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    var __copyProps2 = (to, from, except, desc) => {
      if (from && typeof from === "object" || typeof from === "function") {
        for (let key of __getOwnPropNames2(from))
          if (!__hasOwnProp2.call(to, key) && key !== except)
            __defProp2(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc2(from, key)) || desc.enumerable });
      }
      return to;
    };
    var __toESM2 = (mod, isNodeMode, target) => (target = mod != null ? __create2(__getProtoOf2(mod)) : {}, __copyProps2(isNodeMode || !mod || !mod.__esModule ? __defProp2(target, "default", { value: mod, enumerable: true }) : target, mod));
    var __toCommonJS = (mod) => __copyProps2(__defProp2({}, "__esModule", { value: true }), mod);
    var require_shared_cjs = __commonJS2({
      "node_modules/@vue/shared/dist/shared.cjs.js"(exports2) {
        "use strict";
        Object.defineProperty(exports2, "__esModule", { value: true });
        function makeMap(str, expectsLowerCase) {
          const map = /* @__PURE__ */ Object.create(null);
          const list = str.split(",");
          for (let i = 0; i < list.length; i++) {
            map[list[i]] = true;
          }
          return expectsLowerCase ? (val) => !!map[val.toLowerCase()] : (val) => !!map[val];
        }
        var PatchFlagNames = {
          [1]: `TEXT`,
          [2]: `CLASS`,
          [4]: `STYLE`,
          [8]: `PROPS`,
          [16]: `FULL_PROPS`,
          [32]: `HYDRATE_EVENTS`,
          [64]: `STABLE_FRAGMENT`,
          [128]: `KEYED_FRAGMENT`,
          [256]: `UNKEYED_FRAGMENT`,
          [512]: `NEED_PATCH`,
          [1024]: `DYNAMIC_SLOTS`,
          [2048]: `DEV_ROOT_FRAGMENT`,
          [-1]: `HOISTED`,
          [-2]: `BAIL`
        };
        var slotFlagsText = {
          [1]: "STABLE",
          [2]: "DYNAMIC",
          [3]: "FORWARDED"
        };
        var GLOBALS_WHITE_LISTED = "Infinity,undefined,NaN,isFinite,isNaN,parseFloat,parseInt,decodeURI,decodeURIComponent,encodeURI,encodeURIComponent,Math,Number,Date,Array,Object,Boolean,String,RegExp,Map,Set,JSON,Intl,BigInt";
        var isGloballyWhitelisted = /* @__PURE__ */ makeMap(GLOBALS_WHITE_LISTED);
        var range = 2;
        function generateCodeFrame(source, start22 = 0, end = source.length) {
          let lines = source.split(/(\r?\n)/);
          const newlineSequences = lines.filter((_, idx) => idx % 2 === 1);
          lines = lines.filter((_, idx) => idx % 2 === 0);
          let count = 0;
          const res = [];
          for (let i = 0; i < lines.length; i++) {
            count += lines[i].length + (newlineSequences[i] && newlineSequences[i].length || 0);
            if (count >= start22) {
              for (let j = i - range; j <= i + range || end > count; j++) {
                if (j < 0 || j >= lines.length)
                  continue;
                const line = j + 1;
                res.push(`${line}${" ".repeat(Math.max(3 - String(line).length, 0))}|  ${lines[j]}`);
                const lineLength = lines[j].length;
                const newLineSeqLength = newlineSequences[j] && newlineSequences[j].length || 0;
                if (j === i) {
                  const pad = start22 - (count - (lineLength + newLineSeqLength));
                  const length = Math.max(1, end > count ? lineLength - pad : end - start22);
                  res.push(`   |  ` + " ".repeat(pad) + "^".repeat(length));
                } else if (j > i) {
                  if (end > count) {
                    const length = Math.max(Math.min(end - count, lineLength), 1);
                    res.push(`   |  ` + "^".repeat(length));
                  }
                  count += lineLength + newLineSeqLength;
                }
              }
              break;
            }
          }
          return res.join("\n");
        }
        var specialBooleanAttrs = `itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly`;
        var isSpecialBooleanAttr = /* @__PURE__ */ makeMap(specialBooleanAttrs);
        var isBooleanAttr2 = /* @__PURE__ */ makeMap(specialBooleanAttrs + `,async,autofocus,autoplay,controls,default,defer,disabled,hidden,loop,open,required,reversed,scoped,seamless,checked,muted,multiple,selected`);
        var unsafeAttrCharRE = /[>/="'\u0009\u000a\u000c\u0020]/;
        var attrValidationCache = {};
        function isSSRSafeAttrName(name) {
          if (attrValidationCache.hasOwnProperty(name)) {
            return attrValidationCache[name];
          }
          const isUnsafe = unsafeAttrCharRE.test(name);
          if (isUnsafe) {
            console.error(`unsafe attribute name: ${name}`);
          }
          return attrValidationCache[name] = !isUnsafe;
        }
        var propsToAttrMap = {
          acceptCharset: "accept-charset",
          className: "class",
          htmlFor: "for",
          httpEquiv: "http-equiv"
        };
        var isNoUnitNumericStyleProp = /* @__PURE__ */ makeMap(`animation-iteration-count,border-image-outset,border-image-slice,border-image-width,box-flex,box-flex-group,box-ordinal-group,column-count,columns,flex,flex-grow,flex-positive,flex-shrink,flex-negative,flex-order,grid-row,grid-row-end,grid-row-span,grid-row-start,grid-column,grid-column-end,grid-column-span,grid-column-start,font-weight,line-clamp,line-height,opacity,order,orphans,tab-size,widows,z-index,zoom,fill-opacity,flood-opacity,stop-opacity,stroke-dasharray,stroke-dashoffset,stroke-miterlimit,stroke-opacity,stroke-width`);
        var isKnownAttr = /* @__PURE__ */ makeMap(`accept,accept-charset,accesskey,action,align,allow,alt,async,autocapitalize,autocomplete,autofocus,autoplay,background,bgcolor,border,buffered,capture,challenge,charset,checked,cite,class,code,codebase,color,cols,colspan,content,contenteditable,contextmenu,controls,coords,crossorigin,csp,data,datetime,decoding,default,defer,dir,dirname,disabled,download,draggable,dropzone,enctype,enterkeyhint,for,form,formaction,formenctype,formmethod,formnovalidate,formtarget,headers,height,hidden,high,href,hreflang,http-equiv,icon,id,importance,integrity,ismap,itemprop,keytype,kind,label,lang,language,loading,list,loop,low,manifest,max,maxlength,minlength,media,min,multiple,muted,name,novalidate,open,optimum,pattern,ping,placeholder,poster,preload,radiogroup,readonly,referrerpolicy,rel,required,reversed,rows,rowspan,sandbox,scope,scoped,selected,shape,size,sizes,slot,span,spellcheck,src,srcdoc,srclang,srcset,start,step,style,summary,tabindex,target,title,translate,type,usemap,value,width,wrap`);
        function normalizeStyle(value) {
          if (isArray2(value)) {
            const res = {};
            for (let i = 0; i < value.length; i++) {
              const item = value[i];
              const normalized = normalizeStyle(isString(item) ? parseStringStyle(item) : item);
              if (normalized) {
                for (const key in normalized) {
                  res[key] = normalized[key];
                }
              }
            }
            return res;
          } else if (isObject2(value)) {
            return value;
          }
        }
        var listDelimiterRE = /;(?![^(]*\))/g;
        var propertyDelimiterRE = /:(.+)/;
        function parseStringStyle(cssText) {
          const ret = {};
          cssText.split(listDelimiterRE).forEach((item) => {
            if (item) {
              const tmp = item.split(propertyDelimiterRE);
              tmp.length > 1 && (ret[tmp[0].trim()] = tmp[1].trim());
            }
          });
          return ret;
        }
        function stringifyStyle(styles) {
          let ret = "";
          if (!styles) {
            return ret;
          }
          for (const key in styles) {
            const value = styles[key];
            const normalizedKey = key.startsWith(`--`) ? key : hyphenate(key);
            if (isString(value) || typeof value === "number" && isNoUnitNumericStyleProp(normalizedKey)) {
              ret += `${normalizedKey}:${value};`;
            }
          }
          return ret;
        }
        function normalizeClass(value) {
          let res = "";
          if (isString(value)) {
            res = value;
          } else if (isArray2(value)) {
            for (let i = 0; i < value.length; i++) {
              const normalized = normalizeClass(value[i]);
              if (normalized) {
                res += normalized + " ";
              }
            }
          } else if (isObject2(value)) {
            for (const name in value) {
              if (value[name]) {
                res += name + " ";
              }
            }
          }
          return res.trim();
        }
        var HTML_TAGS = "html,body,base,head,link,meta,style,title,address,article,aside,footer,header,h1,h2,h3,h4,h5,h6,hgroup,nav,section,div,dd,dl,dt,figcaption,figure,picture,hr,img,li,main,ol,p,pre,ul,a,b,abbr,bdi,bdo,br,cite,code,data,dfn,em,i,kbd,mark,q,rp,rt,rtc,ruby,s,samp,small,span,strong,sub,sup,time,u,var,wbr,area,audio,map,track,video,embed,object,param,source,canvas,script,noscript,del,ins,caption,col,colgroup,table,thead,tbody,td,th,tr,button,datalist,fieldset,form,input,label,legend,meter,optgroup,option,output,progress,select,textarea,details,dialog,menu,summary,template,blockquote,iframe,tfoot";
        var SVG_TAGS = "svg,animate,animateMotion,animateTransform,circle,clipPath,color-profile,defs,desc,discard,ellipse,feBlend,feColorMatrix,feComponentTransfer,feComposite,feConvolveMatrix,feDiffuseLighting,feDisplacementMap,feDistanceLight,feDropShadow,feFlood,feFuncA,feFuncB,feFuncG,feFuncR,feGaussianBlur,feImage,feMerge,feMergeNode,feMorphology,feOffset,fePointLight,feSpecularLighting,feSpotLight,feTile,feTurbulence,filter,foreignObject,g,hatch,hatchpath,image,line,linearGradient,marker,mask,mesh,meshgradient,meshpatch,meshrow,metadata,mpath,path,pattern,polygon,polyline,radialGradient,rect,set,solidcolor,stop,switch,symbol,text,textPath,title,tspan,unknown,use,view";
        var VOID_TAGS = "area,base,br,col,embed,hr,img,input,link,meta,param,source,track,wbr";
        var isHTMLTag = /* @__PURE__ */ makeMap(HTML_TAGS);
        var isSVGTag = /* @__PURE__ */ makeMap(SVG_TAGS);
        var isVoidTag = /* @__PURE__ */ makeMap(VOID_TAGS);
        var escapeRE = /["'&<>]/;
        function escapeHtml(string) {
          const str = "" + string;
          const match = escapeRE.exec(str);
          if (!match) {
            return str;
          }
          let html = "";
          let escaped;
          let index;
          let lastIndex = 0;
          for (index = match.index; index < str.length; index++) {
            switch (str.charCodeAt(index)) {
              case 34:
                escaped = "&quot;";
                break;
              case 38:
                escaped = "&amp;";
                break;
              case 39:
                escaped = "&#39;";
                break;
              case 60:
                escaped = "&lt;";
                break;
              case 62:
                escaped = "&gt;";
                break;
              default:
                continue;
            }
            if (lastIndex !== index) {
              html += str.substring(lastIndex, index);
            }
            lastIndex = index + 1;
            html += escaped;
          }
          return lastIndex !== index ? html + str.substring(lastIndex, index) : html;
        }
        var commentStripRE = /^-?>|<!--|-->|--!>|<!-$/g;
        function escapeHtmlComment(src) {
          return src.replace(commentStripRE, "");
        }
        function looseCompareArrays(a, b) {
          if (a.length !== b.length)
            return false;
          let equal = true;
          for (let i = 0; equal && i < a.length; i++) {
            equal = looseEqual(a[i], b[i]);
          }
          return equal;
        }
        function looseEqual(a, b) {
          if (a === b)
            return true;
          let aValidType = isDate(a);
          let bValidType = isDate(b);
          if (aValidType || bValidType) {
            return aValidType && bValidType ? a.getTime() === b.getTime() : false;
          }
          aValidType = isArray2(a);
          bValidType = isArray2(b);
          if (aValidType || bValidType) {
            return aValidType && bValidType ? looseCompareArrays(a, b) : false;
          }
          aValidType = isObject2(a);
          bValidType = isObject2(b);
          if (aValidType || bValidType) {
            if (!aValidType || !bValidType) {
              return false;
            }
            const aKeysCount = Object.keys(a).length;
            const bKeysCount = Object.keys(b).length;
            if (aKeysCount !== bKeysCount) {
              return false;
            }
            for (const key in a) {
              const aHasKey = a.hasOwnProperty(key);
              const bHasKey = b.hasOwnProperty(key);
              if (aHasKey && !bHasKey || !aHasKey && bHasKey || !looseEqual(a[key], b[key])) {
                return false;
              }
            }
          }
          return String(a) === String(b);
        }
        function looseIndexOf(arr, val) {
          return arr.findIndex((item) => looseEqual(item, val));
        }
        var toDisplayString = (val) => {
          return val == null ? "" : isObject2(val) ? JSON.stringify(val, replacer, 2) : String(val);
        };
        var replacer = (_key, val) => {
          if (isMap(val)) {
            return {
              [`Map(${val.size})`]: [...val.entries()].reduce((entries, [key, val2]) => {
                entries[`${key} =>`] = val2;
                return entries;
              }, {})
            };
          } else if (isSet(val)) {
            return {
              [`Set(${val.size})`]: [...val.values()]
            };
          } else if (isObject2(val) && !isArray2(val) && !isPlainObject(val)) {
            return String(val);
          }
          return val;
        };
        var babelParserDefaultPlugins = [
          "bigInt",
          "optionalChaining",
          "nullishCoalescingOperator"
        ];
        var EMPTY_OBJ = Object.freeze({});
        var EMPTY_ARR = Object.freeze([]);
        var NOOP = () => {
        };
        var NO = () => false;
        var onRE = /^on[^a-z]/;
        var isOn = (key) => onRE.test(key);
        var isModelListener = (key) => key.startsWith("onUpdate:");
        var extend = Object.assign;
        var remove = (arr, el) => {
          const i = arr.indexOf(el);
          if (i > -1) {
            arr.splice(i, 1);
          }
        };
        var hasOwnProperty = Object.prototype.hasOwnProperty;
        var hasOwn = (val, key) => hasOwnProperty.call(val, key);
        var isArray2 = Array.isArray;
        var isMap = (val) => toTypeString(val) === "[object Map]";
        var isSet = (val) => toTypeString(val) === "[object Set]";
        var isDate = (val) => val instanceof Date;
        var isFunction2 = (val) => typeof val === "function";
        var isString = (val) => typeof val === "string";
        var isSymbol = (val) => typeof val === "symbol";
        var isObject2 = (val) => val !== null && typeof val === "object";
        var isPromise = (val) => {
          return isObject2(val) && isFunction2(val.then) && isFunction2(val.catch);
        };
        var objectToString = Object.prototype.toString;
        var toTypeString = (value) => objectToString.call(value);
        var toRawType = (value) => {
          return toTypeString(value).slice(8, -1);
        };
        var isPlainObject = (val) => toTypeString(val) === "[object Object]";
        var isIntegerKey = (key) => isString(key) && key !== "NaN" && key[0] !== "-" && "" + parseInt(key, 10) === key;
        var isReservedProp = /* @__PURE__ */ makeMap(",key,ref,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted");
        var cacheStringFunction = (fn) => {
          const cache = /* @__PURE__ */ Object.create(null);
          return (str) => {
            const hit = cache[str];
            return hit || (cache[str] = fn(str));
          };
        };
        var camelizeRE = /-(\w)/g;
        var camelize = cacheStringFunction((str) => {
          return str.replace(camelizeRE, (_, c) => c ? c.toUpperCase() : "");
        });
        var hyphenateRE = /\B([A-Z])/g;
        var hyphenate = cacheStringFunction((str) => str.replace(hyphenateRE, "-$1").toLowerCase());
        var capitalize = cacheStringFunction((str) => str.charAt(0).toUpperCase() + str.slice(1));
        var toHandlerKey = cacheStringFunction((str) => str ? `on${capitalize(str)}` : ``);
        var hasChanged = (value, oldValue) => value !== oldValue && (value === value || oldValue === oldValue);
        var invokeArrayFns = (fns, arg) => {
          for (let i = 0; i < fns.length; i++) {
            fns[i](arg);
          }
        };
        var def = (obj, key, value) => {
          Object.defineProperty(obj, key, {
            configurable: true,
            enumerable: false,
            value
          });
        };
        var toNumber = (val) => {
          const n = parseFloat(val);
          return isNaN(n) ? val : n;
        };
        var _globalThis;
        var getGlobalThis = () => {
          return _globalThis || (_globalThis = typeof globalThis !== "undefined" ? globalThis : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : typeof global !== "undefined" ? global : {});
        };
        exports2.EMPTY_ARR = EMPTY_ARR;
        exports2.EMPTY_OBJ = EMPTY_OBJ;
        exports2.NO = NO;
        exports2.NOOP = NOOP;
        exports2.PatchFlagNames = PatchFlagNames;
        exports2.babelParserDefaultPlugins = babelParserDefaultPlugins;
        exports2.camelize = camelize;
        exports2.capitalize = capitalize;
        exports2.def = def;
        exports2.escapeHtml = escapeHtml;
        exports2.escapeHtmlComment = escapeHtmlComment;
        exports2.extend = extend;
        exports2.generateCodeFrame = generateCodeFrame;
        exports2.getGlobalThis = getGlobalThis;
        exports2.hasChanged = hasChanged;
        exports2.hasOwn = hasOwn;
        exports2.hyphenate = hyphenate;
        exports2.invokeArrayFns = invokeArrayFns;
        exports2.isArray = isArray2;
        exports2.isBooleanAttr = isBooleanAttr2;
        exports2.isDate = isDate;
        exports2.isFunction = isFunction2;
        exports2.isGloballyWhitelisted = isGloballyWhitelisted;
        exports2.isHTMLTag = isHTMLTag;
        exports2.isIntegerKey = isIntegerKey;
        exports2.isKnownAttr = isKnownAttr;
        exports2.isMap = isMap;
        exports2.isModelListener = isModelListener;
        exports2.isNoUnitNumericStyleProp = isNoUnitNumericStyleProp;
        exports2.isObject = isObject2;
        exports2.isOn = isOn;
        exports2.isPlainObject = isPlainObject;
        exports2.isPromise = isPromise;
        exports2.isReservedProp = isReservedProp;
        exports2.isSSRSafeAttrName = isSSRSafeAttrName;
        exports2.isSVGTag = isSVGTag;
        exports2.isSet = isSet;
        exports2.isSpecialBooleanAttr = isSpecialBooleanAttr;
        exports2.isString = isString;
        exports2.isSymbol = isSymbol;
        exports2.isVoidTag = isVoidTag;
        exports2.looseEqual = looseEqual;
        exports2.looseIndexOf = looseIndexOf;
        exports2.makeMap = makeMap;
        exports2.normalizeClass = normalizeClass;
        exports2.normalizeStyle = normalizeStyle;
        exports2.objectToString = objectToString;
        exports2.parseStringStyle = parseStringStyle;
        exports2.propsToAttrMap = propsToAttrMap;
        exports2.remove = remove;
        exports2.slotFlagsText = slotFlagsText;
        exports2.stringifyStyle = stringifyStyle;
        exports2.toDisplayString = toDisplayString;
        exports2.toHandlerKey = toHandlerKey;
        exports2.toNumber = toNumber;
        exports2.toRawType = toRawType;
        exports2.toTypeString = toTypeString;
      }
    });
    var require_shared = __commonJS2({
      "node_modules/@vue/shared/index.js"(exports2, module2) {
        "use strict";
        if (false) {
          module2.exports = null;
        } else {
          module2.exports = require_shared_cjs();
        }
      }
    });
    var require_reactivity_cjs = __commonJS2({
      "node_modules/@vue/reactivity/dist/reactivity.cjs.js"(exports2) {
        "use strict";
        Object.defineProperty(exports2, "__esModule", { value: true });
        var shared = require_shared();
        var targetMap = /* @__PURE__ */ new WeakMap();
        var effectStack = [];
        var activeEffect;
        var ITERATE_KEY = Symbol("iterate");
        var MAP_KEY_ITERATE_KEY = Symbol("Map key iterate");
        function isEffect(fn) {
          return fn && fn._isEffect === true;
        }
        function effect3(fn, options = shared.EMPTY_OBJ) {
          if (isEffect(fn)) {
            fn = fn.raw;
          }
          const effect4 = createReactiveEffect(fn, options);
          if (!options.lazy) {
            effect4();
          }
          return effect4;
        }
        function stop2(effect4) {
          if (effect4.active) {
            cleanup2(effect4);
            if (effect4.options.onStop) {
              effect4.options.onStop();
            }
            effect4.active = false;
          }
        }
        var uid = 0;
        function createReactiveEffect(fn, options) {
          const effect4 = function reactiveEffect() {
            if (!effect4.active) {
              return fn();
            }
            if (!effectStack.includes(effect4)) {
              cleanup2(effect4);
              try {
                enableTracking();
                effectStack.push(effect4);
                activeEffect = effect4;
                return fn();
              } finally {
                effectStack.pop();
                resetTracking();
                activeEffect = effectStack[effectStack.length - 1];
              }
            }
          };
          effect4.id = uid++;
          effect4.allowRecurse = !!options.allowRecurse;
          effect4._isEffect = true;
          effect4.active = true;
          effect4.raw = fn;
          effect4.deps = [];
          effect4.options = options;
          return effect4;
        }
        function cleanup2(effect4) {
          const { deps } = effect4;
          if (deps.length) {
            for (let i = 0; i < deps.length; i++) {
              deps[i].delete(effect4);
            }
            deps.length = 0;
          }
        }
        var shouldTrack = true;
        var trackStack = [];
        function pauseTracking() {
          trackStack.push(shouldTrack);
          shouldTrack = false;
        }
        function enableTracking() {
          trackStack.push(shouldTrack);
          shouldTrack = true;
        }
        function resetTracking() {
          const last = trackStack.pop();
          shouldTrack = last === void 0 ? true : last;
        }
        function track2(target, type, key) {
          if (!shouldTrack || activeEffect === void 0) {
            return;
          }
          let depsMap = targetMap.get(target);
          if (!depsMap) {
            targetMap.set(target, depsMap = /* @__PURE__ */ new Map());
          }
          let dep = depsMap.get(key);
          if (!dep) {
            depsMap.set(key, dep = /* @__PURE__ */ new Set());
          }
          if (!dep.has(activeEffect)) {
            dep.add(activeEffect);
            activeEffect.deps.push(dep);
            if (activeEffect.options.onTrack) {
              activeEffect.options.onTrack({
                effect: activeEffect,
                target,
                type,
                key
              });
            }
          }
        }
        function trigger2(target, type, key, newValue, oldValue, oldTarget) {
          const depsMap = targetMap.get(target);
          if (!depsMap) {
            return;
          }
          const effects = /* @__PURE__ */ new Set();
          const add2 = (effectsToAdd) => {
            if (effectsToAdd) {
              effectsToAdd.forEach((effect4) => {
                if (effect4 !== activeEffect || effect4.allowRecurse) {
                  effects.add(effect4);
                }
              });
            }
          };
          if (type === "clear") {
            depsMap.forEach(add2);
          } else if (key === "length" && shared.isArray(target)) {
            depsMap.forEach((dep, key2) => {
              if (key2 === "length" || key2 >= newValue) {
                add2(dep);
              }
            });
          } else {
            if (key !== void 0) {
              add2(depsMap.get(key));
            }
            switch (type) {
              case "add":
                if (!shared.isArray(target)) {
                  add2(depsMap.get(ITERATE_KEY));
                  if (shared.isMap(target)) {
                    add2(depsMap.get(MAP_KEY_ITERATE_KEY));
                  }
                } else if (shared.isIntegerKey(key)) {
                  add2(depsMap.get("length"));
                }
                break;
              case "delete":
                if (!shared.isArray(target)) {
                  add2(depsMap.get(ITERATE_KEY));
                  if (shared.isMap(target)) {
                    add2(depsMap.get(MAP_KEY_ITERATE_KEY));
                  }
                }
                break;
              case "set":
                if (shared.isMap(target)) {
                  add2(depsMap.get(ITERATE_KEY));
                }
                break;
            }
          }
          const run = (effect4) => {
            if (effect4.options.onTrigger) {
              effect4.options.onTrigger({
                effect: effect4,
                target,
                key,
                type,
                newValue,
                oldValue,
                oldTarget
              });
            }
            if (effect4.options.scheduler) {
              effect4.options.scheduler(effect4);
            } else {
              effect4();
            }
          };
          effects.forEach(run);
        }
        var isNonTrackableKeys = /* @__PURE__ */ shared.makeMap(`__proto__,__v_isRef,__isVue`);
        var builtInSymbols = new Set(Object.getOwnPropertyNames(Symbol).map((key) => Symbol[key]).filter(shared.isSymbol));
        var get2 = /* @__PURE__ */ createGetter();
        var shallowGet = /* @__PURE__ */ createGetter(false, true);
        var readonlyGet = /* @__PURE__ */ createGetter(true);
        var shallowReadonlyGet = /* @__PURE__ */ createGetter(true, true);
        var arrayInstrumentations = /* @__PURE__ */ createArrayInstrumentations();
        function createArrayInstrumentations() {
          const instrumentations = {};
          ["includes", "indexOf", "lastIndexOf"].forEach((key) => {
            instrumentations[key] = function(...args) {
              const arr = toRaw2(this);
              for (let i = 0, l = this.length; i < l; i++) {
                track2(arr, "get", i + "");
              }
              const res = arr[key](...args);
              if (res === -1 || res === false) {
                return arr[key](...args.map(toRaw2));
              } else {
                return res;
              }
            };
          });
          ["push", "pop", "shift", "unshift", "splice"].forEach((key) => {
            instrumentations[key] = function(...args) {
              pauseTracking();
              const res = toRaw2(this)[key].apply(this, args);
              resetTracking();
              return res;
            };
          });
          return instrumentations;
        }
        function createGetter(isReadonly2 = false, shallow = false) {
          return function get3(target, key, receiver) {
            if (key === "__v_isReactive") {
              return !isReadonly2;
            } else if (key === "__v_isReadonly") {
              return isReadonly2;
            } else if (key === "__v_raw" && receiver === (isReadonly2 ? shallow ? shallowReadonlyMap : readonlyMap : shallow ? shallowReactiveMap : reactiveMap).get(target)) {
              return target;
            }
            const targetIsArray = shared.isArray(target);
            if (!isReadonly2 && targetIsArray && shared.hasOwn(arrayInstrumentations, key)) {
              return Reflect.get(arrayInstrumentations, key, receiver);
            }
            const res = Reflect.get(target, key, receiver);
            if (shared.isSymbol(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
              return res;
            }
            if (!isReadonly2) {
              track2(target, "get", key);
            }
            if (shallow) {
              return res;
            }
            if (isRef(res)) {
              const shouldUnwrap = !targetIsArray || !shared.isIntegerKey(key);
              return shouldUnwrap ? res.value : res;
            }
            if (shared.isObject(res)) {
              return isReadonly2 ? readonly(res) : reactive3(res);
            }
            return res;
          };
        }
        var set2 = /* @__PURE__ */ createSetter();
        var shallowSet = /* @__PURE__ */ createSetter(true);
        function createSetter(shallow = false) {
          return function set3(target, key, value, receiver) {
            let oldValue = target[key];
            if (!shallow) {
              value = toRaw2(value);
              oldValue = toRaw2(oldValue);
              if (!shared.isArray(target) && isRef(oldValue) && !isRef(value)) {
                oldValue.value = value;
                return true;
              }
            }
            const hadKey = shared.isArray(target) && shared.isIntegerKey(key) ? Number(key) < target.length : shared.hasOwn(target, key);
            const result = Reflect.set(target, key, value, receiver);
            if (target === toRaw2(receiver)) {
              if (!hadKey) {
                trigger2(target, "add", key, value);
              } else if (shared.hasChanged(value, oldValue)) {
                trigger2(target, "set", key, value, oldValue);
              }
            }
            return result;
          };
        }
        function deleteProperty(target, key) {
          const hadKey = shared.hasOwn(target, key);
          const oldValue = target[key];
          const result = Reflect.deleteProperty(target, key);
          if (result && hadKey) {
            trigger2(target, "delete", key, void 0, oldValue);
          }
          return result;
        }
        function has(target, key) {
          const result = Reflect.has(target, key);
          if (!shared.isSymbol(key) || !builtInSymbols.has(key)) {
            track2(target, "has", key);
          }
          return result;
        }
        function ownKeys(target) {
          track2(target, "iterate", shared.isArray(target) ? "length" : ITERATE_KEY);
          return Reflect.ownKeys(target);
        }
        var mutableHandlers = {
          get: get2,
          set: set2,
          deleteProperty,
          has,
          ownKeys
        };
        var readonlyHandlers = {
          get: readonlyGet,
          set(target, key) {
            {
              console.warn(`Set operation on key "${String(key)}" failed: target is readonly.`, target);
            }
            return true;
          },
          deleteProperty(target, key) {
            {
              console.warn(`Delete operation on key "${String(key)}" failed: target is readonly.`, target);
            }
            return true;
          }
        };
        var shallowReactiveHandlers = /* @__PURE__ */ shared.extend({}, mutableHandlers, {
          get: shallowGet,
          set: shallowSet
        });
        var shallowReadonlyHandlers = /* @__PURE__ */ shared.extend({}, readonlyHandlers, {
          get: shallowReadonlyGet
        });
        var toReactive = (value) => shared.isObject(value) ? reactive3(value) : value;
        var toReadonly = (value) => shared.isObject(value) ? readonly(value) : value;
        var toShallow = (value) => value;
        var getProto = (v) => Reflect.getPrototypeOf(v);
        function get$1(target, key, isReadonly2 = false, isShallow = false) {
          target = target["__v_raw"];
          const rawTarget = toRaw2(target);
          const rawKey = toRaw2(key);
          if (key !== rawKey) {
            !isReadonly2 && track2(rawTarget, "get", key);
          }
          !isReadonly2 && track2(rawTarget, "get", rawKey);
          const { has: has2 } = getProto(rawTarget);
          const wrap = isShallow ? toShallow : isReadonly2 ? toReadonly : toReactive;
          if (has2.call(rawTarget, key)) {
            return wrap(target.get(key));
          } else if (has2.call(rawTarget, rawKey)) {
            return wrap(target.get(rawKey));
          } else if (target !== rawTarget) {
            target.get(key);
          }
        }
        function has$1(key, isReadonly2 = false) {
          const target = this["__v_raw"];
          const rawTarget = toRaw2(target);
          const rawKey = toRaw2(key);
          if (key !== rawKey) {
            !isReadonly2 && track2(rawTarget, "has", key);
          }
          !isReadonly2 && track2(rawTarget, "has", rawKey);
          return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
        }
        function size(target, isReadonly2 = false) {
          target = target["__v_raw"];
          !isReadonly2 && track2(toRaw2(target), "iterate", ITERATE_KEY);
          return Reflect.get(target, "size", target);
        }
        function add(value) {
          value = toRaw2(value);
          const target = toRaw2(this);
          const proto = getProto(target);
          const hadKey = proto.has.call(target, value);
          if (!hadKey) {
            target.add(value);
            trigger2(target, "add", value, value);
          }
          return this;
        }
        function set$1(key, value) {
          value = toRaw2(value);
          const target = toRaw2(this);
          const { has: has2, get: get3 } = getProto(target);
          let hadKey = has2.call(target, key);
          if (!hadKey) {
            key = toRaw2(key);
            hadKey = has2.call(target, key);
          } else {
            checkIdentityKeys(target, has2, key);
          }
          const oldValue = get3.call(target, key);
          target.set(key, value);
          if (!hadKey) {
            trigger2(target, "add", key, value);
          } else if (shared.hasChanged(value, oldValue)) {
            trigger2(target, "set", key, value, oldValue);
          }
          return this;
        }
        function deleteEntry(key) {
          const target = toRaw2(this);
          const { has: has2, get: get3 } = getProto(target);
          let hadKey = has2.call(target, key);
          if (!hadKey) {
            key = toRaw2(key);
            hadKey = has2.call(target, key);
          } else {
            checkIdentityKeys(target, has2, key);
          }
          const oldValue = get3 ? get3.call(target, key) : void 0;
          const result = target.delete(key);
          if (hadKey) {
            trigger2(target, "delete", key, void 0, oldValue);
          }
          return result;
        }
        function clear() {
          const target = toRaw2(this);
          const hadItems = target.size !== 0;
          const oldTarget = shared.isMap(target) ? new Map(target) : new Set(target);
          const result = target.clear();
          if (hadItems) {
            trigger2(target, "clear", void 0, void 0, oldTarget);
          }
          return result;
        }
        function createForEach(isReadonly2, isShallow) {
          return function forEach(callback, thisArg) {
            const observed = this;
            const target = observed["__v_raw"];
            const rawTarget = toRaw2(target);
            const wrap = isShallow ? toShallow : isReadonly2 ? toReadonly : toReactive;
            !isReadonly2 && track2(rawTarget, "iterate", ITERATE_KEY);
            return target.forEach((value, key) => {
              return callback.call(thisArg, wrap(value), wrap(key), observed);
            });
          };
        }
        function createIterableMethod(method, isReadonly2, isShallow) {
          return function(...args) {
            const target = this["__v_raw"];
            const rawTarget = toRaw2(target);
            const targetIsMap = shared.isMap(rawTarget);
            const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
            const isKeyOnly = method === "keys" && targetIsMap;
            const innerIterator = target[method](...args);
            const wrap = isShallow ? toShallow : isReadonly2 ? toReadonly : toReactive;
            !isReadonly2 && track2(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY);
            return {
              next() {
                const { value, done } = innerIterator.next();
                return done ? { value, done } : {
                  value: isPair ? [wrap(value[0]), wrap(value[1])] : wrap(value),
                  done
                };
              },
              [Symbol.iterator]() {
                return this;
              }
            };
          };
        }
        function createReadonlyMethod(type) {
          return function(...args) {
            {
              const key = args[0] ? `on key "${args[0]}" ` : ``;
              console.warn(`${shared.capitalize(type)} operation ${key}failed: target is readonly.`, toRaw2(this));
            }
            return type === "delete" ? false : this;
          };
        }
        function createInstrumentations() {
          const mutableInstrumentations2 = {
            get(key) {
              return get$1(this, key);
            },
            get size() {
              return size(this);
            },
            has: has$1,
            add,
            set: set$1,
            delete: deleteEntry,
            clear,
            forEach: createForEach(false, false)
          };
          const shallowInstrumentations2 = {
            get(key) {
              return get$1(this, key, false, true);
            },
            get size() {
              return size(this);
            },
            has: has$1,
            add,
            set: set$1,
            delete: deleteEntry,
            clear,
            forEach: createForEach(false, true)
          };
          const readonlyInstrumentations2 = {
            get(key) {
              return get$1(this, key, true);
            },
            get size() {
              return size(this, true);
            },
            has(key) {
              return has$1.call(this, key, true);
            },
            add: createReadonlyMethod("add"),
            set: createReadonlyMethod("set"),
            delete: createReadonlyMethod("delete"),
            clear: createReadonlyMethod("clear"),
            forEach: createForEach(true, false)
          };
          const shallowReadonlyInstrumentations2 = {
            get(key) {
              return get$1(this, key, true, true);
            },
            get size() {
              return size(this, true);
            },
            has(key) {
              return has$1.call(this, key, true);
            },
            add: createReadonlyMethod("add"),
            set: createReadonlyMethod("set"),
            delete: createReadonlyMethod("delete"),
            clear: createReadonlyMethod("clear"),
            forEach: createForEach(true, true)
          };
          const iteratorMethods = ["keys", "values", "entries", Symbol.iterator];
          iteratorMethods.forEach((method) => {
            mutableInstrumentations2[method] = createIterableMethod(method, false, false);
            readonlyInstrumentations2[method] = createIterableMethod(method, true, false);
            shallowInstrumentations2[method] = createIterableMethod(method, false, true);
            shallowReadonlyInstrumentations2[method] = createIterableMethod(method, true, true);
          });
          return [
            mutableInstrumentations2,
            readonlyInstrumentations2,
            shallowInstrumentations2,
            shallowReadonlyInstrumentations2
          ];
        }
        var [mutableInstrumentations, readonlyInstrumentations, shallowInstrumentations, shallowReadonlyInstrumentations] = /* @__PURE__ */ createInstrumentations();
        function createInstrumentationGetter(isReadonly2, shallow) {
          const instrumentations = shallow ? isReadonly2 ? shallowReadonlyInstrumentations : shallowInstrumentations : isReadonly2 ? readonlyInstrumentations : mutableInstrumentations;
          return (target, key, receiver) => {
            if (key === "__v_isReactive") {
              return !isReadonly2;
            } else if (key === "__v_isReadonly") {
              return isReadonly2;
            } else if (key === "__v_raw") {
              return target;
            }
            return Reflect.get(shared.hasOwn(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
          };
        }
        var mutableCollectionHandlers = {
          get: /* @__PURE__ */ createInstrumentationGetter(false, false)
        };
        var shallowCollectionHandlers = {
          get: /* @__PURE__ */ createInstrumentationGetter(false, true)
        };
        var readonlyCollectionHandlers = {
          get: /* @__PURE__ */ createInstrumentationGetter(true, false)
        };
        var shallowReadonlyCollectionHandlers = {
          get: /* @__PURE__ */ createInstrumentationGetter(true, true)
        };
        function checkIdentityKeys(target, has2, key) {
          const rawKey = toRaw2(key);
          if (rawKey !== key && has2.call(target, rawKey)) {
            const type = shared.toRawType(target);
            console.warn(`Reactive ${type} contains both the raw and reactive versions of the same object${type === `Map` ? ` as keys` : ``}, which can lead to inconsistencies. Avoid differentiating between the raw and reactive versions of an object and only use the reactive version if possible.`);
          }
        }
        var reactiveMap = /* @__PURE__ */ new WeakMap();
        var shallowReactiveMap = /* @__PURE__ */ new WeakMap();
        var readonlyMap = /* @__PURE__ */ new WeakMap();
        var shallowReadonlyMap = /* @__PURE__ */ new WeakMap();
        function targetTypeMap(rawType) {
          switch (rawType) {
            case "Object":
            case "Array":
              return 1;
            case "Map":
            case "Set":
            case "WeakMap":
            case "WeakSet":
              return 2;
            default:
              return 0;
          }
        }
        function getTargetType(value) {
          return value["__v_skip"] || !Object.isExtensible(value) ? 0 : targetTypeMap(shared.toRawType(value));
        }
        function reactive3(target) {
          if (target && target["__v_isReadonly"]) {
            return target;
          }
          return createReactiveObject(target, false, mutableHandlers, mutableCollectionHandlers, reactiveMap);
        }
        function shallowReactive(target) {
          return createReactiveObject(target, false, shallowReactiveHandlers, shallowCollectionHandlers, shallowReactiveMap);
        }
        function readonly(target) {
          return createReactiveObject(target, true, readonlyHandlers, readonlyCollectionHandlers, readonlyMap);
        }
        function shallowReadonly(target) {
          return createReactiveObject(target, true, shallowReadonlyHandlers, shallowReadonlyCollectionHandlers, shallowReadonlyMap);
        }
        function createReactiveObject(target, isReadonly2, baseHandlers, collectionHandlers, proxyMap) {
          if (!shared.isObject(target)) {
            {
              console.warn(`value cannot be made reactive: ${String(target)}`);
            }
            return target;
          }
          if (target["__v_raw"] && !(isReadonly2 && target["__v_isReactive"])) {
            return target;
          }
          const existingProxy = proxyMap.get(target);
          if (existingProxy) {
            return existingProxy;
          }
          const targetType = getTargetType(target);
          if (targetType === 0) {
            return target;
          }
          const proxy = new Proxy(target, targetType === 2 ? collectionHandlers : baseHandlers);
          proxyMap.set(target, proxy);
          return proxy;
        }
        function isReactive2(value) {
          if (isReadonly(value)) {
            return isReactive2(value["__v_raw"]);
          }
          return !!(value && value["__v_isReactive"]);
        }
        function isReadonly(value) {
          return !!(value && value["__v_isReadonly"]);
        }
        function isProxy(value) {
          return isReactive2(value) || isReadonly(value);
        }
        function toRaw2(observed) {
          return observed && toRaw2(observed["__v_raw"]) || observed;
        }
        function markRaw(value) {
          shared.def(value, "__v_skip", true);
          return value;
        }
        var convert = (val) => shared.isObject(val) ? reactive3(val) : val;
        function isRef(r) {
          return Boolean(r && r.__v_isRef === true);
        }
        function ref(value) {
          return createRef(value);
        }
        function shallowRef(value) {
          return createRef(value, true);
        }
        var RefImpl = class {
          constructor(value, _shallow = false) {
            this._shallow = _shallow;
            this.__v_isRef = true;
            this._rawValue = _shallow ? value : toRaw2(value);
            this._value = _shallow ? value : convert(value);
          }
          get value() {
            track2(toRaw2(this), "get", "value");
            return this._value;
          }
          set value(newVal) {
            newVal = this._shallow ? newVal : toRaw2(newVal);
            if (shared.hasChanged(newVal, this._rawValue)) {
              this._rawValue = newVal;
              this._value = this._shallow ? newVal : convert(newVal);
              trigger2(toRaw2(this), "set", "value", newVal);
            }
          }
        };
        function createRef(rawValue, shallow = false) {
          if (isRef(rawValue)) {
            return rawValue;
          }
          return new RefImpl(rawValue, shallow);
        }
        function triggerRef(ref2) {
          trigger2(toRaw2(ref2), "set", "value", ref2.value);
        }
        function unref(ref2) {
          return isRef(ref2) ? ref2.value : ref2;
        }
        var shallowUnwrapHandlers = {
          get: (target, key, receiver) => unref(Reflect.get(target, key, receiver)),
          set: (target, key, value, receiver) => {
            const oldValue = target[key];
            if (isRef(oldValue) && !isRef(value)) {
              oldValue.value = value;
              return true;
            } else {
              return Reflect.set(target, key, value, receiver);
            }
          }
        };
        function proxyRefs(objectWithRefs) {
          return isReactive2(objectWithRefs) ? objectWithRefs : new Proxy(objectWithRefs, shallowUnwrapHandlers);
        }
        var CustomRefImpl = class {
          constructor(factory) {
            this.__v_isRef = true;
            const { get: get3, set: set3 } = factory(() => track2(this, "get", "value"), () => trigger2(this, "set", "value"));
            this._get = get3;
            this._set = set3;
          }
          get value() {
            return this._get();
          }
          set value(newVal) {
            this._set(newVal);
          }
        };
        function customRef(factory) {
          return new CustomRefImpl(factory);
        }
        function toRefs(object) {
          if (!isProxy(object)) {
            console.warn(`toRefs() expects a reactive object but received a plain one.`);
          }
          const ret = shared.isArray(object) ? new Array(object.length) : {};
          for (const key in object) {
            ret[key] = toRef(object, key);
          }
          return ret;
        }
        var ObjectRefImpl = class {
          constructor(_object, _key) {
            this._object = _object;
            this._key = _key;
            this.__v_isRef = true;
          }
          get value() {
            return this._object[this._key];
          }
          set value(newVal) {
            this._object[this._key] = newVal;
          }
        };
        function toRef(object, key) {
          return isRef(object[key]) ? object[key] : new ObjectRefImpl(object, key);
        }
        var ComputedRefImpl = class {
          constructor(getter, _setter, isReadonly2) {
            this._setter = _setter;
            this._dirty = true;
            this.__v_isRef = true;
            this.effect = effect3(getter, {
              lazy: true,
              scheduler: () => {
                if (!this._dirty) {
                  this._dirty = true;
                  trigger2(toRaw2(this), "set", "value");
                }
              }
            });
            this["__v_isReadonly"] = isReadonly2;
          }
          get value() {
            const self2 = toRaw2(this);
            if (self2._dirty) {
              self2._value = this.effect();
              self2._dirty = false;
            }
            track2(self2, "get", "value");
            return self2._value;
          }
          set value(newValue) {
            this._setter(newValue);
          }
        };
        function computed(getterOrOptions) {
          let getter;
          let setter;
          if (shared.isFunction(getterOrOptions)) {
            getter = getterOrOptions;
            setter = () => {
              console.warn("Write operation failed: computed value is readonly");
            };
          } else {
            getter = getterOrOptions.get;
            setter = getterOrOptions.set;
          }
          return new ComputedRefImpl(getter, setter, shared.isFunction(getterOrOptions) || !getterOrOptions.set);
        }
        exports2.ITERATE_KEY = ITERATE_KEY;
        exports2.computed = computed;
        exports2.customRef = customRef;
        exports2.effect = effect3;
        exports2.enableTracking = enableTracking;
        exports2.isProxy = isProxy;
        exports2.isReactive = isReactive2;
        exports2.isReadonly = isReadonly;
        exports2.isRef = isRef;
        exports2.markRaw = markRaw;
        exports2.pauseTracking = pauseTracking;
        exports2.proxyRefs = proxyRefs;
        exports2.reactive = reactive3;
        exports2.readonly = readonly;
        exports2.ref = ref;
        exports2.resetTracking = resetTracking;
        exports2.shallowReactive = shallowReactive;
        exports2.shallowReadonly = shallowReadonly;
        exports2.shallowRef = shallowRef;
        exports2.stop = stop2;
        exports2.toRaw = toRaw2;
        exports2.toRef = toRef;
        exports2.toRefs = toRefs;
        exports2.track = track2;
        exports2.trigger = trigger2;
        exports2.triggerRef = triggerRef;
        exports2.unref = unref;
      }
    });
    var require_reactivity = __commonJS2({
      "node_modules/@vue/reactivity/index.js"(exports2, module2) {
        "use strict";
        if (false) {
          module2.exports = null;
        } else {
          module2.exports = require_reactivity_cjs();
        }
      }
    });
    var module_exports = {};
    __export(module_exports, {
      default: () => module_default
    });
    module.exports = __toCommonJS(module_exports);
    var flushPending = false;
    var flushing = false;
    var queue = [];
    var lastFlushedIndex = -1;
    function scheduler(callback) {
      queueJob(callback);
    }
    function queueJob(job) {
      if (!queue.includes(job))
        queue.push(job);
      queueFlush();
    }
    function dequeueJob(job) {
      let index = queue.indexOf(job);
      if (index !== -1 && index > lastFlushedIndex)
        queue.splice(index, 1);
    }
    function queueFlush() {
      if (!flushing && !flushPending) {
        flushPending = true;
        queueMicrotask(flushJobs);
      }
    }
    function flushJobs() {
      flushPending = false;
      flushing = true;
      for (let i = 0; i < queue.length; i++) {
        queue[i]();
        lastFlushedIndex = i;
      }
      queue.length = 0;
      lastFlushedIndex = -1;
      flushing = false;
    }
    var reactive;
    var effect;
    var release;
    var raw;
    var shouldSchedule = true;
    function disableEffectScheduling(callback) {
      shouldSchedule = false;
      callback();
      shouldSchedule = true;
    }
    function setReactivityEngine(engine) {
      reactive = engine.reactive;
      release = engine.release;
      effect = (callback) => engine.effect(callback, { scheduler: (task) => {
        if (shouldSchedule) {
          scheduler(task);
        } else {
          task();
        }
      } });
      raw = engine.raw;
    }
    function overrideEffect(override) {
      effect = override;
    }
    function elementBoundEffect(el) {
      let cleanup2 = () => {
      };
      let wrappedEffect = (callback) => {
        let effectReference = effect(callback);
        if (!el._x_effects) {
          el._x_effects = /* @__PURE__ */ new Set();
          el._x_runEffects = () => {
            el._x_effects.forEach((i) => i());
          };
        }
        el._x_effects.add(effectReference);
        cleanup2 = () => {
          if (effectReference === void 0)
            return;
          el._x_effects.delete(effectReference);
          release(effectReference);
        };
        return effectReference;
      };
      return [wrappedEffect, () => {
        cleanup2();
      }];
    }
    function dispatch3(el, name, detail = {}) {
      el.dispatchEvent(new CustomEvent(name, {
        detail,
        bubbles: true,
        composed: true,
        cancelable: true
      }));
    }
    function walk(el, callback) {
      if (typeof ShadowRoot === "function" && el instanceof ShadowRoot) {
        Array.from(el.children).forEach((el2) => walk(el2, callback));
        return;
      }
      let skip = false;
      callback(el, () => skip = true);
      if (skip)
        return;
      let node = el.firstElementChild;
      while (node) {
        walk(node, callback, false);
        node = node.nextElementSibling;
      }
    }
    function warn(message2, ...args) {
      console.warn(`Alpine Warning: ${message2}`, ...args);
    }
    var started = false;
    function start2() {
      if (started)
        warn("Alpine has already been initialized on this page. Calling Alpine.start() more than once can cause problems.");
      started = true;
      if (!document.body)
        warn("Unable to initialize. Trying to load Alpine before `<body>` is available. Did you forget to add `defer` in Alpine's `<script>` tag?");
      dispatch3(document, "alpine:init");
      dispatch3(document, "alpine:initializing");
      startObservingMutations();
      onElAdded((el) => initTree(el, walk));
      onElRemoved((el) => destroyTree(el));
      onAttributesAdded((el, attrs) => {
        directives(el, attrs).forEach((handle) => handle());
      });
      let outNestedComponents = (el) => !closestRoot(el.parentElement, true);
      Array.from(document.querySelectorAll(allSelectors())).filter(outNestedComponents).forEach((el) => {
        initTree(el);
      });
      dispatch3(document, "alpine:initialized");
    }
    var rootSelectorCallbacks = [];
    var initSelectorCallbacks = [];
    function rootSelectors() {
      return rootSelectorCallbacks.map((fn) => fn());
    }
    function allSelectors() {
      return rootSelectorCallbacks.concat(initSelectorCallbacks).map((fn) => fn());
    }
    function addRootSelector(selectorCallback) {
      rootSelectorCallbacks.push(selectorCallback);
    }
    function addInitSelector(selectorCallback) {
      initSelectorCallbacks.push(selectorCallback);
    }
    function closestRoot(el, includeInitSelectors = false) {
      return findClosest(el, (element) => {
        const selectors = includeInitSelectors ? allSelectors() : rootSelectors();
        if (selectors.some((selector) => element.matches(selector)))
          return true;
      });
    }
    function findClosest(el, callback) {
      if (!el)
        return;
      if (callback(el))
        return el;
      if (el._x_teleportBack)
        el = el._x_teleportBack;
      if (!el.parentElement)
        return;
      return findClosest(el.parentElement, callback);
    }
    function isRoot(el) {
      return rootSelectors().some((selector) => el.matches(selector));
    }
    var initInterceptors = [];
    function interceptInit(callback) {
      initInterceptors.push(callback);
    }
    function initTree(el, walker = walk, intercept = () => {
    }) {
      deferHandlingDirectives(() => {
        walker(el, (el2, skip) => {
          intercept(el2, skip);
          initInterceptors.forEach((i) => i(el2, skip));
          directives(el2, el2.attributes).forEach((handle) => handle());
          el2._x_ignore && skip();
        });
      });
    }
    function destroyTree(root) {
      walk(root, (el) => {
        cleanupAttributes(el);
        cleanupElement(el);
      });
    }
    var onAttributeAddeds = [];
    var onElRemoveds = [];
    var onElAddeds = [];
    function onElAdded(callback) {
      onElAddeds.push(callback);
    }
    function onElRemoved(el, callback) {
      if (typeof callback === "function") {
        if (!el._x_cleanups)
          el._x_cleanups = [];
        el._x_cleanups.push(callback);
      } else {
        callback = el;
        onElRemoveds.push(callback);
      }
    }
    function onAttributesAdded(callback) {
      onAttributeAddeds.push(callback);
    }
    function onAttributeRemoved(el, name, callback) {
      if (!el._x_attributeCleanups)
        el._x_attributeCleanups = {};
      if (!el._x_attributeCleanups[name])
        el._x_attributeCleanups[name] = [];
      el._x_attributeCleanups[name].push(callback);
    }
    function cleanupAttributes(el, names) {
      if (!el._x_attributeCleanups)
        return;
      Object.entries(el._x_attributeCleanups).forEach(([name, value]) => {
        if (names === void 0 || names.includes(name)) {
          value.forEach((i) => i());
          delete el._x_attributeCleanups[name];
        }
      });
    }
    function cleanupElement(el) {
      if (el._x_cleanups) {
        while (el._x_cleanups.length)
          el._x_cleanups.pop()();
      }
    }
    var observer = new MutationObserver(onMutate);
    var currentlyObserving = false;
    function startObservingMutations() {
      observer.observe(document, { subtree: true, childList: true, attributes: true, attributeOldValue: true });
      currentlyObserving = true;
    }
    function stopObservingMutations() {
      flushObserver();
      observer.disconnect();
      currentlyObserving = false;
    }
    var recordQueue = [];
    var willProcessRecordQueue = false;
    function flushObserver() {
      recordQueue = recordQueue.concat(observer.takeRecords());
      if (recordQueue.length && !willProcessRecordQueue) {
        willProcessRecordQueue = true;
        queueMicrotask(() => {
          processRecordQueue();
          willProcessRecordQueue = false;
        });
      }
    }
    function processRecordQueue() {
      onMutate(recordQueue);
      recordQueue.length = 0;
    }
    function mutateDom(callback) {
      if (!currentlyObserving)
        return callback();
      stopObservingMutations();
      let result = callback();
      startObservingMutations();
      return result;
    }
    var isCollecting = false;
    var deferredMutations = [];
    function deferMutations() {
      isCollecting = true;
    }
    function flushAndStopDeferringMutations() {
      isCollecting = false;
      onMutate(deferredMutations);
      deferredMutations = [];
    }
    function onMutate(mutations) {
      if (isCollecting) {
        deferredMutations = deferredMutations.concat(mutations);
        return;
      }
      let addedNodes = [];
      let removedNodes = [];
      let addedAttributes = /* @__PURE__ */ new Map();
      let removedAttributes = /* @__PURE__ */ new Map();
      for (let i = 0; i < mutations.length; i++) {
        if (mutations[i].target._x_ignoreMutationObserver)
          continue;
        if (mutations[i].type === "childList") {
          mutations[i].addedNodes.forEach((node) => node.nodeType === 1 && addedNodes.push(node));
          mutations[i].removedNodes.forEach((node) => node.nodeType === 1 && removedNodes.push(node));
        }
        if (mutations[i].type === "attributes") {
          let el = mutations[i].target;
          let name = mutations[i].attributeName;
          let oldValue = mutations[i].oldValue;
          let add = () => {
            if (!addedAttributes.has(el))
              addedAttributes.set(el, []);
            addedAttributes.get(el).push({ name, value: el.getAttribute(name) });
          };
          let remove = () => {
            if (!removedAttributes.has(el))
              removedAttributes.set(el, []);
            removedAttributes.get(el).push(name);
          };
          if (el.hasAttribute(name) && oldValue === null) {
            add();
          } else if (el.hasAttribute(name)) {
            remove();
            add();
          } else {
            remove();
          }
        }
      }
      removedAttributes.forEach((attrs, el) => {
        cleanupAttributes(el, attrs);
      });
      addedAttributes.forEach((attrs, el) => {
        onAttributeAddeds.forEach((i) => i(el, attrs));
      });
      for (let node of removedNodes) {
        if (addedNodes.includes(node))
          continue;
        onElRemoveds.forEach((i) => i(node));
        destroyTree(node);
      }
      addedNodes.forEach((node) => {
        node._x_ignoreSelf = true;
        node._x_ignore = true;
      });
      for (let node of addedNodes) {
        if (removedNodes.includes(node))
          continue;
        if (!node.isConnected)
          continue;
        delete node._x_ignoreSelf;
        delete node._x_ignore;
        onElAddeds.forEach((i) => i(node));
        node._x_ignore = true;
        node._x_ignoreSelf = true;
      }
      addedNodes.forEach((node) => {
        delete node._x_ignoreSelf;
        delete node._x_ignore;
      });
      addedNodes = null;
      removedNodes = null;
      addedAttributes = null;
      removedAttributes = null;
    }
    function scope(node) {
      return mergeProxies(closestDataStack(node));
    }
    function addScopeToNode(node, data2, referenceNode) {
      node._x_dataStack = [data2, ...closestDataStack(referenceNode || node)];
      return () => {
        node._x_dataStack = node._x_dataStack.filter((i) => i !== data2);
      };
    }
    function closestDataStack(node) {
      if (node._x_dataStack)
        return node._x_dataStack;
      if (typeof ShadowRoot === "function" && node instanceof ShadowRoot) {
        return closestDataStack(node.host);
      }
      if (!node.parentNode) {
        return [];
      }
      return closestDataStack(node.parentNode);
    }
    function mergeProxies(objects) {
      let thisProxy = new Proxy({}, {
        ownKeys: () => {
          return Array.from(new Set(objects.flatMap((i) => Object.keys(i))));
        },
        has: (target, name) => {
          return objects.some((obj) => obj.hasOwnProperty(name));
        },
        get: (target, name) => {
          return (objects.find((obj) => {
            if (obj.hasOwnProperty(name)) {
              let descriptor = Object.getOwnPropertyDescriptor(obj, name);
              if (descriptor.get && descriptor.get._x_alreadyBound || descriptor.set && descriptor.set._x_alreadyBound) {
                return true;
              }
              if ((descriptor.get || descriptor.set) && descriptor.enumerable) {
                let getter = descriptor.get;
                let setter = descriptor.set;
                let property = descriptor;
                getter = getter && getter.bind(thisProxy);
                setter = setter && setter.bind(thisProxy);
                if (getter)
                  getter._x_alreadyBound = true;
                if (setter)
                  setter._x_alreadyBound = true;
                Object.defineProperty(obj, name, {
                  ...property,
                  get: getter,
                  set: setter
                });
              }
              return true;
            }
            return false;
          }) || {})[name];
        },
        set: (target, name, value) => {
          let closestObjectWithKey = objects.find((obj) => obj.hasOwnProperty(name));
          if (closestObjectWithKey) {
            closestObjectWithKey[name] = value;
          } else {
            objects[objects.length - 1][name] = value;
          }
          return true;
        }
      });
      return thisProxy;
    }
    function initInterceptors2(data2) {
      let isObject2 = (val) => typeof val === "object" && !Array.isArray(val) && val !== null;
      let recurse = (obj, basePath = "") => {
        Object.entries(Object.getOwnPropertyDescriptors(obj)).forEach(([key, { value, enumerable }]) => {
          if (enumerable === false || value === void 0)
            return;
          let path = basePath === "" ? key : `${basePath}.${key}`;
          if (typeof value === "object" && value !== null && value._x_interceptor) {
            obj[key] = value.initialize(data2, path, key);
          } else {
            if (isObject2(value) && value !== obj && !(value instanceof Element)) {
              recurse(value, path);
            }
          }
        });
      };
      return recurse(data2);
    }
    function interceptor(callback, mutateObj = () => {
    }) {
      let obj = {
        initialValue: void 0,
        _x_interceptor: true,
        initialize(data2, path, key) {
          return callback(this.initialValue, () => get(data2, path), (value) => set(data2, path, value), path, key);
        }
      };
      mutateObj(obj);
      return (initialValue) => {
        if (typeof initialValue === "object" && initialValue !== null && initialValue._x_interceptor) {
          let initialize = obj.initialize.bind(obj);
          obj.initialize = (data2, path, key) => {
            let innerValue = initialValue.initialize(data2, path, key);
            obj.initialValue = innerValue;
            return initialize(data2, path, key);
          };
        } else {
          obj.initialValue = initialValue;
        }
        return obj;
      };
    }
    function get(obj, path) {
      return path.split(".").reduce((carry, segment) => carry[segment], obj);
    }
    function set(obj, path, value) {
      if (typeof path === "string")
        path = path.split(".");
      if (path.length === 1)
        obj[path[0]] = value;
      else if (path.length === 0)
        throw error;
      else {
        if (obj[path[0]])
          return set(obj[path[0]], path.slice(1), value);
        else {
          obj[path[0]] = {};
          return set(obj[path[0]], path.slice(1), value);
        }
      }
    }
    var magics = {};
    function magic(name, callback) {
      magics[name] = callback;
    }
    function injectMagics(obj, el) {
      Object.entries(magics).forEach(([name, callback]) => {
        let memoizedUtilities = null;
        function getUtilities() {
          if (memoizedUtilities) {
            return memoizedUtilities;
          } else {
            let [utilities, cleanup2] = getElementBoundUtilities(el);
            memoizedUtilities = { interceptor, ...utilities };
            onElRemoved(el, cleanup2);
            return memoizedUtilities;
          }
        }
        Object.defineProperty(obj, `$${name}`, {
          get() {
            return callback(el, getUtilities());
          },
          enumerable: false
        });
      });
      return obj;
    }
    function tryCatch(el, expression, callback, ...args) {
      try {
        return callback(...args);
      } catch (e) {
        handleError(e, el, expression);
      }
    }
    function handleError(error2, el, expression = void 0) {
      Object.assign(error2, { el, expression });
      console.warn(`Alpine Expression Error: ${error2.message}

${expression ? 'Expression: "' + expression + '"\n\n' : ""}`, el);
      setTimeout(() => {
        throw error2;
      }, 0);
    }
    var shouldAutoEvaluateFunctions = true;
    function dontAutoEvaluateFunctions(callback) {
      let cache = shouldAutoEvaluateFunctions;
      shouldAutoEvaluateFunctions = false;
      let result = callback();
      shouldAutoEvaluateFunctions = cache;
      return result;
    }
    function evaluate(el, expression, extras = {}) {
      let result;
      evaluateLater(el, expression)((value) => result = value, extras);
      return result;
    }
    function evaluateLater(...args) {
      return theEvaluatorFunction(...args);
    }
    var theEvaluatorFunction = normalEvaluator;
    function setEvaluator(newEvaluator) {
      theEvaluatorFunction = newEvaluator;
    }
    function normalEvaluator(el, expression) {
      let overriddenMagics = {};
      injectMagics(overriddenMagics, el);
      let dataStack = [overriddenMagics, ...closestDataStack(el)];
      let evaluator = typeof expression === "function" ? generateEvaluatorFromFunction(dataStack, expression) : generateEvaluatorFromString(dataStack, expression, el);
      return tryCatch.bind(null, el, expression, evaluator);
    }
    function generateEvaluatorFromFunction(dataStack, func) {
      return (receiver = () => {
      }, { scope: scope2 = {}, params = [] } = {}) => {
        let result = func.apply(mergeProxies([scope2, ...dataStack]), params);
        runIfTypeOfFunction(receiver, result);
      };
    }
    var evaluatorMemo = {};
    function generateFunctionFromString(expression, el) {
      if (evaluatorMemo[expression]) {
        return evaluatorMemo[expression];
      }
      let AsyncFunction = Object.getPrototypeOf(async function() {
      }).constructor;
      let rightSideSafeExpression = /^[\n\s]*if.*\(.*\)/.test(expression.trim()) || /^(let|const)\s/.test(expression.trim()) ? `(async()=>{ ${expression} })()` : expression;
      const safeAsyncFunction = () => {
        try {
          return new AsyncFunction(["__self", "scope"], `with (scope) { __self.result = ${rightSideSafeExpression} }; __self.finished = true; return __self.result;`);
        } catch (error2) {
          handleError(error2, el, expression);
          return Promise.resolve();
        }
      };
      let func = safeAsyncFunction();
      evaluatorMemo[expression] = func;
      return func;
    }
    function generateEvaluatorFromString(dataStack, expression, el) {
      let func = generateFunctionFromString(expression, el);
      return (receiver = () => {
      }, { scope: scope2 = {}, params = [] } = {}) => {
        func.result = void 0;
        func.finished = false;
        let completeScope = mergeProxies([scope2, ...dataStack]);
        if (typeof func === "function") {
          let promise = func(func, completeScope).catch((error2) => handleError(error2, el, expression));
          if (func.finished) {
            runIfTypeOfFunction(receiver, func.result, completeScope, params, el);
            func.result = void 0;
          } else {
            promise.then((result) => {
              runIfTypeOfFunction(receiver, result, completeScope, params, el);
            }).catch((error2) => handleError(error2, el, expression)).finally(() => func.result = void 0);
          }
        }
      };
    }
    function runIfTypeOfFunction(receiver, value, scope2, params, el) {
      if (shouldAutoEvaluateFunctions && typeof value === "function") {
        let result = value.apply(scope2, params);
        if (result instanceof Promise) {
          result.then((i) => runIfTypeOfFunction(receiver, i, scope2, params)).catch((error2) => handleError(error2, el, value));
        } else {
          receiver(result);
        }
      } else if (typeof value === "object" && value instanceof Promise) {
        value.then((i) => receiver(i));
      } else {
        receiver(value);
      }
    }
    var prefixAsString = "x-";
    function prefix(subject = "") {
      return prefixAsString + subject;
    }
    function setPrefix(newPrefix) {
      prefixAsString = newPrefix;
    }
    var directiveHandlers = {};
    function directive2(name, callback) {
      directiveHandlers[name] = callback;
      return {
        before(directive22) {
          if (!directiveHandlers[directive22]) {
            console.warn("Cannot find directive `${directive}`. `${name}` will use the default order of execution");
            return;
          }
          const pos = directiveOrder.indexOf(directive22);
          directiveOrder.splice(pos >= 0 ? pos : directiveOrder.indexOf("DEFAULT"), 0, name);
        }
      };
    }
    function directives(el, attributes, originalAttributeOverride) {
      attributes = Array.from(attributes);
      if (el._x_virtualDirectives) {
        let vAttributes = Object.entries(el._x_virtualDirectives).map(([name, value]) => ({ name, value }));
        let staticAttributes = attributesOnly(vAttributes);
        vAttributes = vAttributes.map((attribute) => {
          if (staticAttributes.find((attr) => attr.name === attribute.name)) {
            return {
              name: `x-bind:${attribute.name}`,
              value: `"${attribute.value}"`
            };
          }
          return attribute;
        });
        attributes = attributes.concat(vAttributes);
      }
      let transformedAttributeMap = {};
      let directives2 = attributes.map(toTransformedAttributes((newName, oldName) => transformedAttributeMap[newName] = oldName)).filter(outNonAlpineAttributes).map(toParsedDirectives(transformedAttributeMap, originalAttributeOverride)).sort(byPriority);
      return directives2.map((directive22) => {
        return getDirectiveHandler(el, directive22);
      });
    }
    function attributesOnly(attributes) {
      return Array.from(attributes).map(toTransformedAttributes()).filter((attr) => !outNonAlpineAttributes(attr));
    }
    var isDeferringHandlers = false;
    var directiveHandlerStacks = /* @__PURE__ */ new Map();
    var currentHandlerStackKey = Symbol();
    function deferHandlingDirectives(callback) {
      isDeferringHandlers = true;
      let key = Symbol();
      currentHandlerStackKey = key;
      directiveHandlerStacks.set(key, []);
      let flushHandlers = () => {
        while (directiveHandlerStacks.get(key).length)
          directiveHandlerStacks.get(key).shift()();
        directiveHandlerStacks.delete(key);
      };
      let stopDeferring = () => {
        isDeferringHandlers = false;
        flushHandlers();
      };
      callback(flushHandlers);
      stopDeferring();
    }
    function getElementBoundUtilities(el) {
      let cleanups = [];
      let cleanup2 = (callback) => cleanups.push(callback);
      let [effect3, cleanupEffect] = elementBoundEffect(el);
      cleanups.push(cleanupEffect);
      let utilities = {
        Alpine: alpine_default,
        effect: effect3,
        cleanup: cleanup2,
        evaluateLater: evaluateLater.bind(evaluateLater, el),
        evaluate: evaluate.bind(evaluate, el)
      };
      let doCleanup = () => cleanups.forEach((i) => i());
      return [utilities, doCleanup];
    }
    function getDirectiveHandler(el, directive22) {
      let noop = () => {
      };
      let handler4 = directiveHandlers[directive22.type] || noop;
      let [utilities, cleanup2] = getElementBoundUtilities(el);
      onAttributeRemoved(el, directive22.original, cleanup2);
      let fullHandler = () => {
        if (el._x_ignore || el._x_ignoreSelf)
          return;
        handler4.inline && handler4.inline(el, directive22, utilities);
        handler4 = handler4.bind(handler4, el, directive22, utilities);
        isDeferringHandlers ? directiveHandlerStacks.get(currentHandlerStackKey).push(handler4) : handler4();
      };
      fullHandler.runCleanups = cleanup2;
      return fullHandler;
    }
    var startingWith = (subject, replacement) => ({ name, value }) => {
      if (name.startsWith(subject))
        name = name.replace(subject, replacement);
      return { name, value };
    };
    var into = (i) => i;
    function toTransformedAttributes(callback = () => {
    }) {
      return ({ name, value }) => {
        let { name: newName, value: newValue } = attributeTransformers.reduce((carry, transform) => {
          return transform(carry);
        }, { name, value });
        if (newName !== name)
          callback(newName, name);
        return { name: newName, value: newValue };
      };
    }
    var attributeTransformers = [];
    function mapAttributes(callback) {
      attributeTransformers.push(callback);
    }
    function outNonAlpineAttributes({ name }) {
      return alpineAttributeRegex().test(name);
    }
    var alpineAttributeRegex = () => new RegExp(`^${prefixAsString}([^:^.]+)\\b`);
    function toParsedDirectives(transformedAttributeMap, originalAttributeOverride) {
      return ({ name, value }) => {
        let typeMatch = name.match(alpineAttributeRegex());
        let valueMatch = name.match(/:([a-zA-Z0-9\-:]+)/);
        let modifiers = name.match(/\.[^.\]]+(?=[^\]]*$)/g) || [];
        let original = originalAttributeOverride || transformedAttributeMap[name] || name;
        return {
          type: typeMatch ? typeMatch[1] : null,
          value: valueMatch ? valueMatch[1] : null,
          modifiers: modifiers.map((i) => i.replace(".", "")),
          expression: value,
          original
        };
      };
    }
    var DEFAULT = "DEFAULT";
    var directiveOrder = [
      "ignore",
      "ref",
      "data",
      "id",
      "bind",
      "init",
      "for",
      "model",
      "modelable",
      "transition",
      "show",
      "if",
      DEFAULT,
      "teleport"
    ];
    function byPriority(a, b) {
      let typeA = directiveOrder.indexOf(a.type) === -1 ? DEFAULT : a.type;
      let typeB = directiveOrder.indexOf(b.type) === -1 ? DEFAULT : b.type;
      return directiveOrder.indexOf(typeA) - directiveOrder.indexOf(typeB);
    }
    var tickStack = [];
    var isHolding = false;
    function nextTick(callback = () => {
    }) {
      queueMicrotask(() => {
        isHolding || setTimeout(() => {
          releaseNextTicks();
        });
      });
      return new Promise((res) => {
        tickStack.push(() => {
          callback();
          res();
        });
      });
    }
    function releaseNextTicks() {
      isHolding = false;
      while (tickStack.length)
        tickStack.shift()();
    }
    function holdNextTicks() {
      isHolding = true;
    }
    function setClasses(el, value) {
      if (Array.isArray(value)) {
        return setClassesFromString(el, value.join(" "));
      } else if (typeof value === "object" && value !== null) {
        return setClassesFromObject(el, value);
      } else if (typeof value === "function") {
        return setClasses(el, value());
      }
      return setClassesFromString(el, value);
    }
    function setClassesFromString(el, classString) {
      let split = (classString2) => classString2.split(" ").filter(Boolean);
      let missingClasses = (classString2) => classString2.split(" ").filter((i) => !el.classList.contains(i)).filter(Boolean);
      let addClassesAndReturnUndo = (classes) => {
        el.classList.add(...classes);
        return () => {
          el.classList.remove(...classes);
        };
      };
      classString = classString === true ? classString = "" : classString || "";
      return addClassesAndReturnUndo(missingClasses(classString));
    }
    function setClassesFromObject(el, classObject) {
      let split = (classString) => classString.split(" ").filter(Boolean);
      let forAdd = Object.entries(classObject).flatMap(([classString, bool]) => bool ? split(classString) : false).filter(Boolean);
      let forRemove = Object.entries(classObject).flatMap(([classString, bool]) => !bool ? split(classString) : false).filter(Boolean);
      let added = [];
      let removed = [];
      forRemove.forEach((i) => {
        if (el.classList.contains(i)) {
          el.classList.remove(i);
          removed.push(i);
        }
      });
      forAdd.forEach((i) => {
        if (!el.classList.contains(i)) {
          el.classList.add(i);
          added.push(i);
        }
      });
      return () => {
        removed.forEach((i) => el.classList.add(i));
        added.forEach((i) => el.classList.remove(i));
      };
    }
    function setStyles(el, value) {
      if (typeof value === "object" && value !== null) {
        return setStylesFromObject(el, value);
      }
      return setStylesFromString(el, value);
    }
    function setStylesFromObject(el, value) {
      let previousStyles = {};
      Object.entries(value).forEach(([key, value2]) => {
        previousStyles[key] = el.style[key];
        if (!key.startsWith("--")) {
          key = kebabCase(key);
        }
        el.style.setProperty(key, value2);
      });
      setTimeout(() => {
        if (el.style.length === 0) {
          el.removeAttribute("style");
        }
      });
      return () => {
        setStyles(el, previousStyles);
      };
    }
    function setStylesFromString(el, value) {
      let cache = el.getAttribute("style", value);
      el.setAttribute("style", value);
      return () => {
        el.setAttribute("style", cache || "");
      };
    }
    function kebabCase(subject) {
      return subject.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
    }
    function once(callback, fallback2 = () => {
    }) {
      let called = false;
      return function() {
        if (!called) {
          called = true;
          callback.apply(this, arguments);
        } else {
          fallback2.apply(this, arguments);
        }
      };
    }
    directive2("transition", (el, { value, modifiers, expression }, { evaluate: evaluate2 }) => {
      if (typeof expression === "function")
        expression = evaluate2(expression);
      if (expression === false)
        return;
      if (!expression || typeof expression === "boolean") {
        registerTransitionsFromHelper(el, modifiers, value);
      } else {
        registerTransitionsFromClassString(el, expression, value);
      }
    });
    function registerTransitionsFromClassString(el, classString, stage) {
      registerTransitionObject(el, setClasses, "");
      let directiveStorageMap = {
        "enter": (classes) => {
          el._x_transition.enter.during = classes;
        },
        "enter-start": (classes) => {
          el._x_transition.enter.start = classes;
        },
        "enter-end": (classes) => {
          el._x_transition.enter.end = classes;
        },
        "leave": (classes) => {
          el._x_transition.leave.during = classes;
        },
        "leave-start": (classes) => {
          el._x_transition.leave.start = classes;
        },
        "leave-end": (classes) => {
          el._x_transition.leave.end = classes;
        }
      };
      directiveStorageMap[stage](classString);
    }
    function registerTransitionsFromHelper(el, modifiers, stage) {
      registerTransitionObject(el, setStyles);
      let doesntSpecify = !modifiers.includes("in") && !modifiers.includes("out") && !stage;
      let transitioningIn = doesntSpecify || modifiers.includes("in") || ["enter"].includes(stage);
      let transitioningOut = doesntSpecify || modifiers.includes("out") || ["leave"].includes(stage);
      if (modifiers.includes("in") && !doesntSpecify) {
        modifiers = modifiers.filter((i, index) => index < modifiers.indexOf("out"));
      }
      if (modifiers.includes("out") && !doesntSpecify) {
        modifiers = modifiers.filter((i, index) => index > modifiers.indexOf("out"));
      }
      let wantsAll = !modifiers.includes("opacity") && !modifiers.includes("scale");
      let wantsOpacity = wantsAll || modifiers.includes("opacity");
      let wantsScale = wantsAll || modifiers.includes("scale");
      let opacityValue = wantsOpacity ? 0 : 1;
      let scaleValue = wantsScale ? modifierValue(modifiers, "scale", 95) / 100 : 1;
      let delay = modifierValue(modifiers, "delay", 0) / 1e3;
      let origin = modifierValue(modifiers, "origin", "center");
      let property = "opacity, transform";
      let durationIn = modifierValue(modifiers, "duration", 150) / 1e3;
      let durationOut = modifierValue(modifiers, "duration", 75) / 1e3;
      let easing = `cubic-bezier(0.4, 0.0, 0.2, 1)`;
      if (transitioningIn) {
        el._x_transition.enter.during = {
          transformOrigin: origin,
          transitionDelay: `${delay}s`,
          transitionProperty: property,
          transitionDuration: `${durationIn}s`,
          transitionTimingFunction: easing
        };
        el._x_transition.enter.start = {
          opacity: opacityValue,
          transform: `scale(${scaleValue})`
        };
        el._x_transition.enter.end = {
          opacity: 1,
          transform: `scale(1)`
        };
      }
      if (transitioningOut) {
        el._x_transition.leave.during = {
          transformOrigin: origin,
          transitionDelay: `${delay}s`,
          transitionProperty: property,
          transitionDuration: `${durationOut}s`,
          transitionTimingFunction: easing
        };
        el._x_transition.leave.start = {
          opacity: 1,
          transform: `scale(1)`
        };
        el._x_transition.leave.end = {
          opacity: opacityValue,
          transform: `scale(${scaleValue})`
        };
      }
    }
    function registerTransitionObject(el, setFunction, defaultValue = {}) {
      if (!el._x_transition)
        el._x_transition = {
          enter: { during: defaultValue, start: defaultValue, end: defaultValue },
          leave: { during: defaultValue, start: defaultValue, end: defaultValue },
          in(before = () => {
          }, after = () => {
          }) {
            transition(el, setFunction, {
              during: this.enter.during,
              start: this.enter.start,
              end: this.enter.end
            }, before, after);
          },
          out(before = () => {
          }, after = () => {
          }) {
            transition(el, setFunction, {
              during: this.leave.during,
              start: this.leave.start,
              end: this.leave.end
            }, before, after);
          }
        };
    }
    window.Element.prototype._x_toggleAndCascadeWithTransitions = function(el, value, show, hide) {
      const nextTick2 = document.visibilityState === "visible" ? requestAnimationFrame : setTimeout;
      let clickAwayCompatibleShow = () => nextTick2(show);
      if (value) {
        if (el._x_transition && (el._x_transition.enter || el._x_transition.leave)) {
          el._x_transition.enter && (Object.entries(el._x_transition.enter.during).length || Object.entries(el._x_transition.enter.start).length || Object.entries(el._x_transition.enter.end).length) ? el._x_transition.in(show) : clickAwayCompatibleShow();
        } else {
          el._x_transition ? el._x_transition.in(show) : clickAwayCompatibleShow();
        }
        return;
      }
      el._x_hidePromise = el._x_transition ? new Promise((resolve, reject) => {
        el._x_transition.out(() => {
        }, () => resolve(hide));
        el._x_transitioning.beforeCancel(() => reject({ isFromCancelledTransition: true }));
      }) : Promise.resolve(hide);
      queueMicrotask(() => {
        let closest = closestHide(el);
        if (closest) {
          if (!closest._x_hideChildren)
            closest._x_hideChildren = [];
          closest._x_hideChildren.push(el);
        } else {
          nextTick2(() => {
            let hideAfterChildren = (el2) => {
              let carry = Promise.all([
                el2._x_hidePromise,
                ...(el2._x_hideChildren || []).map(hideAfterChildren)
              ]).then(([i]) => i());
              delete el2._x_hidePromise;
              delete el2._x_hideChildren;
              return carry;
            };
            hideAfterChildren(el).catch((e) => {
              if (!e.isFromCancelledTransition)
                throw e;
            });
          });
        }
      });
    };
    function closestHide(el) {
      let parent = el.parentNode;
      if (!parent)
        return;
      return parent._x_hidePromise ? parent : closestHide(parent);
    }
    function transition(el, setFunction, { during, start: start22, end } = {}, before = () => {
    }, after = () => {
    }) {
      if (el._x_transitioning)
        el._x_transitioning.cancel();
      if (Object.keys(during).length === 0 && Object.keys(start22).length === 0 && Object.keys(end).length === 0) {
        before();
        after();
        return;
      }
      let undoStart, undoDuring, undoEnd;
      performTransition(el, {
        start() {
          undoStart = setFunction(el, start22);
        },
        during() {
          undoDuring = setFunction(el, during);
        },
        before,
        end() {
          undoStart();
          undoEnd = setFunction(el, end);
        },
        after,
        cleanup() {
          undoDuring();
          undoEnd();
        }
      });
    }
    function performTransition(el, stages) {
      let interrupted, reachedBefore, reachedEnd;
      let finish = once(() => {
        mutateDom(() => {
          interrupted = true;
          if (!reachedBefore)
            stages.before();
          if (!reachedEnd) {
            stages.end();
            releaseNextTicks();
          }
          stages.after();
          if (el.isConnected)
            stages.cleanup();
          delete el._x_transitioning;
        });
      });
      el._x_transitioning = {
        beforeCancels: [],
        beforeCancel(callback) {
          this.beforeCancels.push(callback);
        },
        cancel: once(function() {
          while (this.beforeCancels.length) {
            this.beforeCancels.shift()();
          }
          ;
          finish();
        }),
        finish
      };
      mutateDom(() => {
        stages.start();
        stages.during();
      });
      holdNextTicks();
      requestAnimationFrame(() => {
        if (interrupted)
          return;
        let duration = Number(getComputedStyle(el).transitionDuration.replace(/,.*/, "").replace("s", "")) * 1e3;
        let delay = Number(getComputedStyle(el).transitionDelay.replace(/,.*/, "").replace("s", "")) * 1e3;
        if (duration === 0)
          duration = Number(getComputedStyle(el).animationDuration.replace("s", "")) * 1e3;
        mutateDom(() => {
          stages.before();
        });
        reachedBefore = true;
        requestAnimationFrame(() => {
          if (interrupted)
            return;
          mutateDom(() => {
            stages.end();
          });
          releaseNextTicks();
          setTimeout(el._x_transitioning.finish, duration + delay);
          reachedEnd = true;
        });
      });
    }
    function modifierValue(modifiers, key, fallback2) {
      if (modifiers.indexOf(key) === -1)
        return fallback2;
      const rawValue = modifiers[modifiers.indexOf(key) + 1];
      if (!rawValue)
        return fallback2;
      if (key === "scale") {
        if (isNaN(rawValue))
          return fallback2;
      }
      if (key === "duration" || key === "delay") {
        let match = rawValue.match(/([0-9]+)ms/);
        if (match)
          return match[1];
      }
      if (key === "origin") {
        if (["top", "right", "left", "center", "bottom"].includes(modifiers[modifiers.indexOf(key) + 2])) {
          return [rawValue, modifiers[modifiers.indexOf(key) + 2]].join(" ");
        }
      }
      return rawValue;
    }
    var isCloning = false;
    function skipDuringClone(callback, fallback2 = () => {
    }) {
      return (...args) => isCloning ? fallback2(...args) : callback(...args);
    }
    function onlyDuringClone(callback) {
      return (...args) => isCloning && callback(...args);
    }
    function cloneNode(from, to) {
      if (from._x_dataStack) {
        to._x_dataStack = from._x_dataStack;
        to.setAttribute("data-has-alpine-state", true);
      }
      isCloning = true;
      dontRegisterReactiveSideEffects(() => {
        initTree(to, (el, callback) => {
          callback(el, () => {
          });
        });
      });
      isCloning = false;
    }
    var isCloningLegacy = false;
    function clone(oldEl, newEl) {
      if (!newEl._x_dataStack)
        newEl._x_dataStack = oldEl._x_dataStack;
      isCloning = true;
      isCloningLegacy = true;
      dontRegisterReactiveSideEffects(() => {
        cloneTree(newEl);
      });
      isCloning = false;
      isCloningLegacy = false;
    }
    function cloneTree(el) {
      let hasRunThroughFirstEl = false;
      let shallowWalker = (el2, callback) => {
        walk(el2, (el3, skip) => {
          if (hasRunThroughFirstEl && isRoot(el3))
            return skip();
          hasRunThroughFirstEl = true;
          callback(el3, skip);
        });
      };
      initTree(el, shallowWalker);
    }
    function dontRegisterReactiveSideEffects(callback) {
      let cache = effect;
      overrideEffect((callback2, el) => {
        let storedEffect = cache(callback2);
        release(storedEffect);
        return () => {
        };
      });
      callback();
      overrideEffect(cache);
    }
    function shouldSkipRegisteringDataDuringClone(el) {
      if (!isCloning)
        return false;
      if (isCloningLegacy)
        return true;
      return el.hasAttribute("data-has-alpine-state");
    }
    function bind(el, name, value, modifiers = []) {
      if (!el._x_bindings)
        el._x_bindings = reactive({});
      el._x_bindings[name] = value;
      name = modifiers.includes("camel") ? camelCase(name) : name;
      switch (name) {
        case "value":
          bindInputValue(el, value);
          break;
        case "style":
          bindStyles(el, value);
          break;
        case "class":
          bindClasses(el, value);
          break;
        case "selected":
        case "checked":
          bindAttributeAndProperty(el, name, value);
          break;
        default:
          bindAttribute(el, name, value);
          break;
      }
    }
    function bindInputValue(el, value) {
      if (el.type === "radio") {
        if (el.attributes.value === void 0) {
          el.value = value;
        }
        if (window.fromModel) {
          el.checked = checkedAttrLooseCompare(el.value, value);
        }
      } else if (el.type === "checkbox") {
        if (Number.isInteger(value)) {
          el.value = value;
        } else if (!Array.isArray(value) && typeof value !== "boolean" && ![null, void 0].includes(value)) {
          el.value = String(value);
        } else {
          if (Array.isArray(value)) {
            el.checked = value.some((val) => checkedAttrLooseCompare(val, el.value));
          } else {
            el.checked = !!value;
          }
        }
      } else if (el.tagName === "SELECT") {
        updateSelect(el, value);
      } else {
        if (el.value === value)
          return;
        el.value = value === void 0 ? "" : value;
      }
    }
    function bindClasses(el, value) {
      if (el._x_undoAddedClasses)
        el._x_undoAddedClasses();
      el._x_undoAddedClasses = setClasses(el, value);
    }
    function bindStyles(el, value) {
      if (el._x_undoAddedStyles)
        el._x_undoAddedStyles();
      el._x_undoAddedStyles = setStyles(el, value);
    }
    function bindAttributeAndProperty(el, name, value) {
      bindAttribute(el, name, value);
      setPropertyIfChanged(el, name, value);
    }
    function bindAttribute(el, name, value) {
      if ([null, void 0, false].includes(value) && attributeShouldntBePreservedIfFalsy(name)) {
        el.removeAttribute(name);
      } else {
        if (isBooleanAttr(name))
          value = name;
        setIfChanged(el, name, value);
      }
    }
    function setIfChanged(el, attrName, value) {
      if (el.getAttribute(attrName) != value) {
        el.setAttribute(attrName, value);
      }
    }
    function setPropertyIfChanged(el, propName, value) {
      if (el[propName] !== value) {
        el[propName] = value;
      }
    }
    function updateSelect(el, value) {
      const arrayWrappedValue = [].concat(value).map((value2) => {
        return value2 + "";
      });
      Array.from(el.options).forEach((option) => {
        option.selected = arrayWrappedValue.includes(option.value);
      });
    }
    function camelCase(subject) {
      return subject.toLowerCase().replace(/-(\w)/g, (match, char) => char.toUpperCase());
    }
    function checkedAttrLooseCompare(valueA, valueB) {
      return valueA == valueB;
    }
    function isBooleanAttr(attrName) {
      const booleanAttributes = [
        "disabled",
        "checked",
        "required",
        "readonly",
        "hidden",
        "open",
        "selected",
        "autofocus",
        "itemscope",
        "multiple",
        "novalidate",
        "allowfullscreen",
        "allowpaymentrequest",
        "formnovalidate",
        "autoplay",
        "controls",
        "loop",
        "muted",
        "playsinline",
        "default",
        "ismap",
        "reversed",
        "async",
        "defer",
        "nomodule"
      ];
      return booleanAttributes.includes(attrName);
    }
    function attributeShouldntBePreservedIfFalsy(name) {
      return !["aria-pressed", "aria-checked", "aria-expanded", "aria-selected"].includes(name);
    }
    function getBinding(el, name, fallback2) {
      if (el._x_bindings && el._x_bindings[name] !== void 0)
        return el._x_bindings[name];
      return getAttributeBinding(el, name, fallback2);
    }
    function extractProp(el, name, fallback2, extract = true) {
      if (el._x_bindings && el._x_bindings[name] !== void 0)
        return el._x_bindings[name];
      if (el._x_inlineBindings && el._x_inlineBindings[name] !== void 0) {
        let binding = el._x_inlineBindings[name];
        binding.extract = extract;
        return dontAutoEvaluateFunctions(() => {
          return evaluate(el, binding.expression);
        });
      }
      return getAttributeBinding(el, name, fallback2);
    }
    function getAttributeBinding(el, name, fallback2) {
      let attr = el.getAttribute(name);
      if (attr === null)
        return typeof fallback2 === "function" ? fallback2() : fallback2;
      if (attr === "")
        return true;
      if (isBooleanAttr(name)) {
        return !![name, "true"].includes(attr);
      }
      return attr;
    }
    function debounce2(func, wait) {
      var timeout;
      return function() {
        var context = this, args = arguments;
        var later = function() {
          timeout = null;
          func.apply(context, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
      };
    }
    function throttle(func, limit) {
      let inThrottle;
      return function() {
        let context = this, args = arguments;
        if (!inThrottle) {
          func.apply(context, args);
          inThrottle = true;
          setTimeout(() => inThrottle = false, limit);
        }
      };
    }
    function entangle({ get: outerGet, set: outerSet }, { get: innerGet, set: innerSet }) {
      let firstRun = true;
      let outerHash, innerHash, outerHashLatest, innerHashLatest;
      let reference = effect(() => {
        let outer, inner;
        if (firstRun) {
          outer = outerGet();
          innerSet(JSON.parse(JSON.stringify(outer)));
          inner = innerGet();
          firstRun = false;
        } else {
          outer = outerGet();
          inner = innerGet();
          outerHashLatest = JSON.stringify(outer);
          innerHashLatest = JSON.stringify(inner);
          if (outerHashLatest !== outerHash) {
            inner = innerGet();
            innerSet(outer);
            inner = outer;
          } else {
            outerSet(JSON.parse(innerHashLatest != null ? innerHashLatest : null));
            outer = inner;
          }
        }
        outerHash = JSON.stringify(outer);
        innerHash = JSON.stringify(inner);
      });
      return () => {
        release(reference);
      };
    }
    function plugin(callback) {
      let callbacks = Array.isArray(callback) ? callback : [callback];
      callbacks.forEach((i) => i(alpine_default));
    }
    var stores = {};
    var isReactive = false;
    function store(name, value) {
      if (!isReactive) {
        stores = reactive(stores);
        isReactive = true;
      }
      if (value === void 0) {
        return stores[name];
      }
      stores[name] = value;
      if (typeof value === "object" && value !== null && value.hasOwnProperty("init") && typeof value.init === "function") {
        stores[name].init();
      }
      initInterceptors2(stores[name]);
    }
    function getStores() {
      return stores;
    }
    var binds = {};
    function bind2(name, bindings) {
      let getBindings = typeof bindings !== "function" ? () => bindings : bindings;
      if (name instanceof Element) {
        return applyBindingsObject(name, getBindings());
      } else {
        binds[name] = getBindings;
      }
      return () => {
      };
    }
    function injectBindingProviders(obj) {
      Object.entries(binds).forEach(([name, callback]) => {
        Object.defineProperty(obj, name, {
          get() {
            return (...args) => {
              return callback(...args);
            };
          }
        });
      });
      return obj;
    }
    function applyBindingsObject(el, obj, original) {
      let cleanupRunners = [];
      while (cleanupRunners.length)
        cleanupRunners.pop()();
      let attributes = Object.entries(obj).map(([name, value]) => ({ name, value }));
      let staticAttributes = attributesOnly(attributes);
      attributes = attributes.map((attribute) => {
        if (staticAttributes.find((attr) => attr.name === attribute.name)) {
          return {
            name: `x-bind:${attribute.name}`,
            value: `"${attribute.value}"`
          };
        }
        return attribute;
      });
      directives(el, attributes, original).map((handle) => {
        cleanupRunners.push(handle.runCleanups);
        handle();
      });
      return () => {
        while (cleanupRunners.length)
          cleanupRunners.pop()();
      };
    }
    var datas = {};
    function data(name, callback) {
      datas[name] = callback;
    }
    function injectDataProviders(obj, context) {
      Object.entries(datas).forEach(([name, callback]) => {
        Object.defineProperty(obj, name, {
          get() {
            return (...args) => {
              return callback.bind(context)(...args);
            };
          },
          enumerable: false
        });
      });
      return obj;
    }
    var Alpine20 = {
      get reactive() {
        return reactive;
      },
      get release() {
        return release;
      },
      get effect() {
        return effect;
      },
      get raw() {
        return raw;
      },
      version: "3.13.0",
      flushAndStopDeferringMutations,
      dontAutoEvaluateFunctions,
      disableEffectScheduling,
      startObservingMutations,
      stopObservingMutations,
      setReactivityEngine,
      onAttributeRemoved,
      onAttributesAdded,
      closestDataStack,
      skipDuringClone,
      onlyDuringClone,
      addRootSelector,
      addInitSelector,
      addScopeToNode,
      deferMutations,
      mapAttributes,
      evaluateLater,
      interceptInit,
      setEvaluator,
      mergeProxies,
      extractProp,
      findClosest,
      onElRemoved,
      closestRoot,
      destroyTree,
      interceptor,
      transition,
      setStyles,
      mutateDom,
      directive: directive2,
      entangle,
      throttle,
      debounce: debounce2,
      evaluate,
      initTree,
      nextTick,
      prefixed: prefix,
      prefix: setPrefix,
      plugin,
      magic,
      store,
      start: start2,
      clone,
      cloneNode,
      bound: getBinding,
      $data: scope,
      walk,
      data,
      bind: bind2
    };
    var alpine_default = Alpine20;
    var import_reactivity9 = __toESM2(require_reactivity());
    magic("nextTick", () => nextTick);
    magic("dispatch", (el) => dispatch3.bind(dispatch3, el));
    magic("watch", (el, { evaluateLater: evaluateLater2, effect: effect3 }) => (key, callback) => {
      let evaluate2 = evaluateLater2(key);
      let firstTime = true;
      let oldValue;
      let effectReference = effect3(() => evaluate2((value) => {
        JSON.stringify(value);
        if (!firstTime) {
          queueMicrotask(() => {
            callback(value, oldValue);
            oldValue = value;
          });
        } else {
          oldValue = value;
        }
        firstTime = false;
      }));
      el._x_effects.delete(effectReference);
    });
    magic("store", getStores);
    magic("data", (el) => scope(el));
    magic("root", (el) => closestRoot(el));
    magic("refs", (el) => {
      if (el._x_refs_proxy)
        return el._x_refs_proxy;
      el._x_refs_proxy = mergeProxies(getArrayOfRefObject(el));
      return el._x_refs_proxy;
    });
    function getArrayOfRefObject(el) {
      let refObjects = [];
      let currentEl = el;
      while (currentEl) {
        if (currentEl._x_refs)
          refObjects.push(currentEl._x_refs);
        currentEl = currentEl.parentNode;
      }
      return refObjects;
    }
    var globalIdMemo = {};
    function findAndIncrementId(name) {
      if (!globalIdMemo[name])
        globalIdMemo[name] = 0;
      return ++globalIdMemo[name];
    }
    function closestIdRoot(el, name) {
      return findClosest(el, (element) => {
        if (element._x_ids && element._x_ids[name])
          return true;
      });
    }
    function setIdRoot(el, name) {
      if (!el._x_ids)
        el._x_ids = {};
      if (!el._x_ids[name])
        el._x_ids[name] = findAndIncrementId(name);
    }
    magic("id", (el) => (name, key = null) => {
      let root = closestIdRoot(el, name);
      let id = root ? root._x_ids[name] : findAndIncrementId(name);
      return key ? `${name}-${id}-${key}` : `${name}-${id}`;
    });
    magic("el", (el) => el);
    warnMissingPluginMagic("Focus", "focus", "focus");
    warnMissingPluginMagic("Persist", "persist", "persist");
    function warnMissingPluginMagic(name, magicName, slug) {
      magic(magicName, (el) => warn(`You can't use [$${directiveName}] without first installing the "${name}" plugin here: https://alpinejs.dev/plugins/${slug}`, el));
    }
    directive2("modelable", (el, { expression }, { effect: effect3, evaluateLater: evaluateLater2, cleanup: cleanup2 }) => {
      let func = evaluateLater2(expression);
      let innerGet = () => {
        let result;
        func((i) => result = i);
        return result;
      };
      let evaluateInnerSet = evaluateLater2(`${expression} = __placeholder`);
      let innerSet = (val) => evaluateInnerSet(() => {
      }, { scope: { "__placeholder": val } });
      let initialValue = innerGet();
      innerSet(initialValue);
      queueMicrotask(() => {
        if (!el._x_model)
          return;
        el._x_removeModelListeners["default"]();
        let outerGet = el._x_model.get;
        let outerSet = el._x_model.set;
        let releaseEntanglement = entangle({
          get() {
            return outerGet();
          },
          set(value) {
            outerSet(value);
          }
        }, {
          get() {
            return innerGet();
          },
          set(value) {
            innerSet(value);
          }
        });
        cleanup2(releaseEntanglement);
      });
    });
    var teleportContainerDuringClone = document.createElement("div");
    directive2("teleport", (el, { modifiers, expression }, { cleanup: cleanup2 }) => {
      if (el.tagName.toLowerCase() !== "template")
        warn("x-teleport can only be used on a <template> tag", el);
      let target = skipDuringClone(() => {
        return document.querySelector(expression);
      }, () => {
        return teleportContainerDuringClone;
      })();
      if (!target)
        warn(`Cannot find x-teleport element for selector: "${expression}"`);
      let clone2 = el.content.cloneNode(true).firstElementChild;
      el._x_teleport = clone2;
      clone2._x_teleportBack = el;
      if (el._x_forwardEvents) {
        el._x_forwardEvents.forEach((eventName) => {
          clone2.addEventListener(eventName, (e) => {
            e.stopPropagation();
            el.dispatchEvent(new e.constructor(e.type, e));
          });
        });
      }
      addScopeToNode(clone2, {}, el);
      mutateDom(() => {
        if (modifiers.includes("prepend")) {
          target.parentNode.insertBefore(clone2, target);
        } else if (modifiers.includes("append")) {
          target.parentNode.insertBefore(clone2, target.nextSibling);
        } else {
          target.appendChild(clone2);
        }
        initTree(clone2);
        clone2._x_ignore = true;
      });
      cleanup2(() => clone2.remove());
    });
    var handler = () => {
    };
    handler.inline = (el, { modifiers }, { cleanup: cleanup2 }) => {
      modifiers.includes("self") ? el._x_ignoreSelf = true : el._x_ignore = true;
      cleanup2(() => {
        modifiers.includes("self") ? delete el._x_ignoreSelf : delete el._x_ignore;
      });
    };
    directive2("ignore", handler);
    directive2("effect", (el, { expression }, { effect: effect3 }) => effect3(evaluateLater(el, expression)));
    function on3(el, event, modifiers, callback) {
      let listenerTarget = el;
      let handler4 = (e) => callback(e);
      let options = {};
      let wrapHandler = (callback2, wrapper) => (e) => wrapper(callback2, e);
      if (modifiers.includes("dot"))
        event = dotSyntax(event);
      if (modifiers.includes("camel"))
        event = camelCase2(event);
      if (modifiers.includes("passive"))
        options.passive = true;
      if (modifiers.includes("capture"))
        options.capture = true;
      if (modifiers.includes("window"))
        listenerTarget = window;
      if (modifiers.includes("document"))
        listenerTarget = document;
      if (modifiers.includes("debounce")) {
        let nextModifier = modifiers[modifiers.indexOf("debounce") + 1] || "invalid-wait";
        let wait = isNumeric(nextModifier.split("ms")[0]) ? Number(nextModifier.split("ms")[0]) : 250;
        handler4 = debounce2(handler4, wait);
      }
      if (modifiers.includes("throttle")) {
        let nextModifier = modifiers[modifiers.indexOf("throttle") + 1] || "invalid-wait";
        let wait = isNumeric(nextModifier.split("ms")[0]) ? Number(nextModifier.split("ms")[0]) : 250;
        handler4 = throttle(handler4, wait);
      }
      if (modifiers.includes("prevent"))
        handler4 = wrapHandler(handler4, (next, e) => {
          e.preventDefault();
          next(e);
        });
      if (modifiers.includes("stop"))
        handler4 = wrapHandler(handler4, (next, e) => {
          e.stopPropagation();
          next(e);
        });
      if (modifiers.includes("self"))
        handler4 = wrapHandler(handler4, (next, e) => {
          e.target === el && next(e);
        });
      if (modifiers.includes("away") || modifiers.includes("outside")) {
        listenerTarget = document;
        handler4 = wrapHandler(handler4, (next, e) => {
          if (el.contains(e.target))
            return;
          if (e.target.isConnected === false)
            return;
          if (el.offsetWidth < 1 && el.offsetHeight < 1)
            return;
          if (el._x_isShown === false)
            return;
          next(e);
        });
      }
      if (modifiers.includes("once")) {
        handler4 = wrapHandler(handler4, (next, e) => {
          next(e);
          listenerTarget.removeEventListener(event, handler4, options);
        });
      }
      handler4 = wrapHandler(handler4, (next, e) => {
        if (isKeyEvent(event)) {
          if (isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers)) {
            return;
          }
        }
        next(e);
      });
      listenerTarget.addEventListener(event, handler4, options);
      return () => {
        listenerTarget.removeEventListener(event, handler4, options);
      };
    }
    function dotSyntax(subject) {
      return subject.replace(/-/g, ".");
    }
    function camelCase2(subject) {
      return subject.toLowerCase().replace(/-(\w)/g, (match, char) => char.toUpperCase());
    }
    function isNumeric(subject) {
      return !Array.isArray(subject) && !isNaN(subject);
    }
    function kebabCase2(subject) {
      if ([" ", "_"].includes(subject))
        return subject;
      return subject.replace(/([a-z])([A-Z])/g, "$1-$2").replace(/[_\s]/, "-").toLowerCase();
    }
    function isKeyEvent(event) {
      return ["keydown", "keyup"].includes(event);
    }
    function isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers) {
      let keyModifiers = modifiers.filter((i) => {
        return !["window", "document", "prevent", "stop", "once", "capture"].includes(i);
      });
      if (keyModifiers.includes("debounce")) {
        let debounceIndex = keyModifiers.indexOf("debounce");
        keyModifiers.splice(debounceIndex, isNumeric((keyModifiers[debounceIndex + 1] || "invalid-wait").split("ms")[0]) ? 2 : 1);
      }
      if (keyModifiers.includes("throttle")) {
        let debounceIndex = keyModifiers.indexOf("throttle");
        keyModifiers.splice(debounceIndex, isNumeric((keyModifiers[debounceIndex + 1] || "invalid-wait").split("ms")[0]) ? 2 : 1);
      }
      if (keyModifiers.length === 0)
        return false;
      if (keyModifiers.length === 1 && keyToModifiers(e.key).includes(keyModifiers[0]))
        return false;
      const systemKeyModifiers = ["ctrl", "shift", "alt", "meta", "cmd", "super"];
      const selectedSystemKeyModifiers = systemKeyModifiers.filter((modifier) => keyModifiers.includes(modifier));
      keyModifiers = keyModifiers.filter((i) => !selectedSystemKeyModifiers.includes(i));
      if (selectedSystemKeyModifiers.length > 0) {
        const activelyPressedKeyModifiers = selectedSystemKeyModifiers.filter((modifier) => {
          if (modifier === "cmd" || modifier === "super")
            modifier = "meta";
          return e[`${modifier}Key`];
        });
        if (activelyPressedKeyModifiers.length === selectedSystemKeyModifiers.length) {
          if (keyToModifiers(e.key).includes(keyModifiers[0]))
            return false;
        }
      }
      return true;
    }
    function keyToModifiers(key) {
      if (!key)
        return [];
      key = kebabCase2(key);
      let modifierToKeyMap = {
        "ctrl": "control",
        "slash": "/",
        "space": " ",
        "spacebar": " ",
        "cmd": "meta",
        "esc": "escape",
        "up": "arrow-up",
        "down": "arrow-down",
        "left": "arrow-left",
        "right": "arrow-right",
        "period": ".",
        "equal": "=",
        "minus": "-",
        "underscore": "_"
      };
      modifierToKeyMap[key] = key;
      return Object.keys(modifierToKeyMap).map((modifier) => {
        if (modifierToKeyMap[modifier] === key)
          return modifier;
      }).filter((modifier) => modifier);
    }
    directive2("model", (el, { modifiers, expression }, { effect: effect3, cleanup: cleanup2 }) => {
      let scopeTarget = el;
      if (modifiers.includes("parent")) {
        scopeTarget = el.parentNode;
      }
      let evaluateGet = evaluateLater(scopeTarget, expression);
      let evaluateSet;
      if (typeof expression === "string") {
        evaluateSet = evaluateLater(scopeTarget, `${expression} = __placeholder`);
      } else if (typeof expression === "function" && typeof expression() === "string") {
        evaluateSet = evaluateLater(scopeTarget, `${expression()} = __placeholder`);
      } else {
        evaluateSet = () => {
        };
      }
      let getValue = () => {
        let result;
        evaluateGet((value) => result = value);
        return isGetterSetter(result) ? result.get() : result;
      };
      let setValue = (value) => {
        let result;
        evaluateGet((value2) => result = value2);
        if (isGetterSetter(result)) {
          result.set(value);
        } else {
          evaluateSet(() => {
          }, {
            scope: { "__placeholder": value }
          });
        }
      };
      if (typeof expression === "string" && el.type === "radio") {
        mutateDom(() => {
          if (!el.hasAttribute("name"))
            el.setAttribute("name", expression);
        });
      }
      var event = el.tagName.toLowerCase() === "select" || ["checkbox", "radio"].includes(el.type) || modifiers.includes("lazy") ? "change" : "input";
      let removeListener = isCloning ? () => {
      } : on3(el, event, modifiers, (e) => {
        setValue(getInputValue(el, modifiers, e, getValue()));
      });
      if (modifiers.includes("fill")) {
        if ([null, ""].includes(getValue()) || el.type === "checkbox" && Array.isArray(getValue())) {
          el.dispatchEvent(new Event(event, {}));
        }
      }
      if (!el._x_removeModelListeners)
        el._x_removeModelListeners = {};
      el._x_removeModelListeners["default"] = removeListener;
      cleanup2(() => el._x_removeModelListeners["default"]());
      if (el.form) {
        let removeResetListener = on3(el.form, "reset", [], (e) => {
          nextTick(() => el._x_model && el._x_model.set(el.value));
        });
        cleanup2(() => removeResetListener());
      }
      el._x_model = {
        get() {
          return getValue();
        },
        set(value) {
          setValue(value);
        }
      };
      el._x_forceModelUpdate = (value) => {
        if (value === void 0 && typeof expression === "string" && expression.match(/\./))
          value = "";
        window.fromModel = true;
        mutateDom(() => bind(el, "value", value));
        delete window.fromModel;
      };
      effect3(() => {
        let value = getValue();
        if (modifiers.includes("unintrusive") && document.activeElement.isSameNode(el))
          return;
        el._x_forceModelUpdate(value);
      });
    });
    function getInputValue(el, modifiers, event, currentValue) {
      return mutateDom(() => {
        var _a;
        if (event instanceof CustomEvent && event.detail !== void 0)
          return (_a = event.detail) != null ? _a : event.target.value;
        else if (el.type === "checkbox") {
          if (Array.isArray(currentValue)) {
            let newValue = modifiers.includes("number") ? safeParseNumber(event.target.value) : event.target.value;
            return event.target.checked ? currentValue.concat([newValue]) : currentValue.filter((el2) => !checkedAttrLooseCompare2(el2, newValue));
          } else {
            return event.target.checked;
          }
        } else if (el.tagName.toLowerCase() === "select" && el.multiple) {
          return modifiers.includes("number") ? Array.from(event.target.selectedOptions).map((option) => {
            let rawValue = option.value || option.text;
            return safeParseNumber(rawValue);
          }) : Array.from(event.target.selectedOptions).map((option) => {
            return option.value || option.text;
          });
        } else {
          let rawValue = event.target.value;
          return modifiers.includes("number") ? safeParseNumber(rawValue) : modifiers.includes("trim") ? rawValue.trim() : rawValue;
        }
      });
    }
    function safeParseNumber(rawValue) {
      let number = rawValue ? parseFloat(rawValue) : null;
      return isNumeric2(number) ? number : rawValue;
    }
    function checkedAttrLooseCompare2(valueA, valueB) {
      return valueA == valueB;
    }
    function isNumeric2(subject) {
      return !Array.isArray(subject) && !isNaN(subject);
    }
    function isGetterSetter(value) {
      return value !== null && typeof value === "object" && typeof value.get === "function" && typeof value.set === "function";
    }
    directive2("cloak", (el) => queueMicrotask(() => mutateDom(() => el.removeAttribute(prefix("cloak")))));
    addInitSelector(() => `[${prefix("init")}]`);
    directive2("init", skipDuringClone((el, { expression }, { evaluate: evaluate2 }) => {
      if (typeof expression === "string") {
        return !!expression.trim() && evaluate2(expression, {}, false);
      }
      return evaluate2(expression, {}, false);
    }));
    directive2("text", (el, { expression }, { effect: effect3, evaluateLater: evaluateLater2 }) => {
      let evaluate2 = evaluateLater2(expression);
      effect3(() => {
        evaluate2((value) => {
          mutateDom(() => {
            el.textContent = value;
          });
        });
      });
    });
    directive2("html", (el, { expression }, { effect: effect3, evaluateLater: evaluateLater2 }) => {
      let evaluate2 = evaluateLater2(expression);
      effect3(() => {
        evaluate2((value) => {
          mutateDom(() => {
            el.innerHTML = value;
            el._x_ignoreSelf = true;
            initTree(el);
            delete el._x_ignoreSelf;
          });
        });
      });
    });
    mapAttributes(startingWith(":", into(prefix("bind:"))));
    var handler2 = (el, { value, modifiers, expression, original }, { effect: effect3 }) => {
      if (!value) {
        let bindingProviders = {};
        injectBindingProviders(bindingProviders);
        let getBindings = evaluateLater(el, expression);
        getBindings((bindings) => {
          applyBindingsObject(el, bindings, original);
        }, { scope: bindingProviders });
        return;
      }
      if (value === "key")
        return storeKeyForXFor(el, expression);
      if (el._x_inlineBindings && el._x_inlineBindings[value] && el._x_inlineBindings[value].extract) {
        return;
      }
      let evaluate2 = evaluateLater(el, expression);
      effect3(() => evaluate2((result) => {
        if (result === void 0 && typeof expression === "string" && expression.match(/\./)) {
          result = "";
        }
        mutateDom(() => bind(el, value, result, modifiers));
      }));
    };
    handler2.inline = (el, { value, modifiers, expression }) => {
      if (!value)
        return;
      if (!el._x_inlineBindings)
        el._x_inlineBindings = {};
      el._x_inlineBindings[value] = { expression, extract: false };
    };
    directive2("bind", handler2);
    function storeKeyForXFor(el, expression) {
      el._x_keyExpression = expression;
    }
    addRootSelector(() => `[${prefix("data")}]`);
    directive2("data", (el, { expression }, { cleanup: cleanup2 }) => {
      if (shouldSkipRegisteringDataDuringClone(el))
        return;
      expression = expression === "" ? "{}" : expression;
      let magicContext = {};
      injectMagics(magicContext, el);
      let dataProviderContext = {};
      injectDataProviders(dataProviderContext, magicContext);
      let data2 = evaluate(el, expression, { scope: dataProviderContext });
      if (data2 === void 0 || data2 === true)
        data2 = {};
      injectMagics(data2, el);
      let reactiveData = reactive(data2);
      initInterceptors2(reactiveData);
      let undo = addScopeToNode(el, reactiveData);
      reactiveData["init"] && evaluate(el, reactiveData["init"]);
      cleanup2(() => {
        reactiveData["destroy"] && evaluate(el, reactiveData["destroy"]);
        undo();
      });
    });
    directive2("show", (el, { modifiers, expression }, { effect: effect3 }) => {
      let evaluate2 = evaluateLater(el, expression);
      if (!el._x_doHide)
        el._x_doHide = () => {
          mutateDom(() => {
            el.style.setProperty("display", "none", modifiers.includes("important") ? "important" : void 0);
          });
        };
      if (!el._x_doShow)
        el._x_doShow = () => {
          mutateDom(() => {
            if (el.style.length === 1 && el.style.display === "none") {
              el.removeAttribute("style");
            } else {
              el.style.removeProperty("display");
            }
          });
        };
      let hide = () => {
        el._x_doHide();
        el._x_isShown = false;
      };
      let show = () => {
        el._x_doShow();
        el._x_isShown = true;
      };
      let clickAwayCompatibleShow = () => setTimeout(show);
      let toggle = once((value) => value ? show() : hide(), (value) => {
        if (typeof el._x_toggleAndCascadeWithTransitions === "function") {
          el._x_toggleAndCascadeWithTransitions(el, value, show, hide);
        } else {
          value ? clickAwayCompatibleShow() : hide();
        }
      });
      let oldValue;
      let firstTime = true;
      effect3(() => evaluate2((value) => {
        if (!firstTime && value === oldValue)
          return;
        if (modifiers.includes("immediate"))
          value ? clickAwayCompatibleShow() : hide();
        toggle(value);
        oldValue = value;
        firstTime = false;
      }));
    });
    directive2("for", (el, { expression }, { effect: effect3, cleanup: cleanup2 }) => {
      let iteratorNames = parseForExpression(expression);
      let evaluateItems = evaluateLater(el, iteratorNames.items);
      let evaluateKey = evaluateLater(el, el._x_keyExpression || "index");
      el._x_prevKeys = [];
      el._x_lookup = {};
      effect3(() => loop(el, iteratorNames, evaluateItems, evaluateKey));
      cleanup2(() => {
        Object.values(el._x_lookup).forEach((el2) => el2.remove());
        delete el._x_prevKeys;
        delete el._x_lookup;
      });
    });
    function loop(el, iteratorNames, evaluateItems, evaluateKey) {
      let isObject2 = (i) => typeof i === "object" && !Array.isArray(i);
      let templateEl = el;
      evaluateItems((items) => {
        if (isNumeric3(items) && items >= 0) {
          items = Array.from(Array(items).keys(), (i) => i + 1);
        }
        if (items === void 0)
          items = [];
        let lookup = el._x_lookup;
        let prevKeys = el._x_prevKeys;
        let scopes = [];
        let keys = [];
        if (isObject2(items)) {
          items = Object.entries(items).map(([key, value]) => {
            let scope2 = getIterationScopeVariables(iteratorNames, value, key, items);
            evaluateKey((value2) => keys.push(value2), { scope: { index: key, ...scope2 } });
            scopes.push(scope2);
          });
        } else {
          for (let i = 0; i < items.length; i++) {
            let scope2 = getIterationScopeVariables(iteratorNames, items[i], i, items);
            evaluateKey((value) => keys.push(value), { scope: { index: i, ...scope2 } });
            scopes.push(scope2);
          }
        }
        let adds = [];
        let moves = [];
        let removes = [];
        let sames = [];
        for (let i = 0; i < prevKeys.length; i++) {
          let key = prevKeys[i];
          if (keys.indexOf(key) === -1)
            removes.push(key);
        }
        prevKeys = prevKeys.filter((key) => !removes.includes(key));
        let lastKey = "template";
        for (let i = 0; i < keys.length; i++) {
          let key = keys[i];
          let prevIndex = prevKeys.indexOf(key);
          if (prevIndex === -1) {
            prevKeys.splice(i, 0, key);
            adds.push([lastKey, i]);
          } else if (prevIndex !== i) {
            let keyInSpot = prevKeys.splice(i, 1)[0];
            let keyForSpot = prevKeys.splice(prevIndex - 1, 1)[0];
            prevKeys.splice(i, 0, keyForSpot);
            prevKeys.splice(prevIndex, 0, keyInSpot);
            moves.push([keyInSpot, keyForSpot]);
          } else {
            sames.push(key);
          }
          lastKey = key;
        }
        for (let i = 0; i < removes.length; i++) {
          let key = removes[i];
          if (!!lookup[key]._x_effects) {
            lookup[key]._x_effects.forEach(dequeueJob);
          }
          lookup[key].remove();
          lookup[key] = null;
          delete lookup[key];
        }
        for (let i = 0; i < moves.length; i++) {
          let [keyInSpot, keyForSpot] = moves[i];
          let elInSpot = lookup[keyInSpot];
          let elForSpot = lookup[keyForSpot];
          let marker = document.createElement("div");
          mutateDom(() => {
            if (!elForSpot)
              warn(`x-for ":key" is undefined or invalid`, templateEl);
            elForSpot.after(marker);
            elInSpot.after(elForSpot);
            elForSpot._x_currentIfEl && elForSpot.after(elForSpot._x_currentIfEl);
            marker.before(elInSpot);
            elInSpot._x_currentIfEl && elInSpot.after(elInSpot._x_currentIfEl);
            marker.remove();
          });
          elForSpot._x_refreshXForScope(scopes[keys.indexOf(keyForSpot)]);
        }
        for (let i = 0; i < adds.length; i++) {
          let [lastKey2, index] = adds[i];
          let lastEl = lastKey2 === "template" ? templateEl : lookup[lastKey2];
          if (lastEl._x_currentIfEl)
            lastEl = lastEl._x_currentIfEl;
          let scope2 = scopes[index];
          let key = keys[index];
          let clone2 = document.importNode(templateEl.content, true).firstElementChild;
          let reactiveScope = reactive(scope2);
          addScopeToNode(clone2, reactiveScope, templateEl);
          clone2._x_refreshXForScope = (newScope) => {
            Object.entries(newScope).forEach(([key2, value]) => {
              reactiveScope[key2] = value;
            });
          };
          mutateDom(() => {
            lastEl.after(clone2);
            initTree(clone2);
          });
          if (typeof key === "object") {
            warn("x-for key cannot be an object, it must be a string or an integer", templateEl);
          }
          lookup[key] = clone2;
        }
        for (let i = 0; i < sames.length; i++) {
          lookup[sames[i]]._x_refreshXForScope(scopes[keys.indexOf(sames[i])]);
        }
        templateEl._x_prevKeys = keys;
      });
    }
    function parseForExpression(expression) {
      let forIteratorRE = /,([^,\}\]]*)(?:,([^,\}\]]*))?$/;
      let stripParensRE = /^\s*\(|\)\s*$/g;
      let forAliasRE = /([\s\S]*?)\s+(?:in|of)\s+([\s\S]*)/;
      let inMatch = expression.match(forAliasRE);
      if (!inMatch)
        return;
      let res = {};
      res.items = inMatch[2].trim();
      let item = inMatch[1].replace(stripParensRE, "").trim();
      let iteratorMatch = item.match(forIteratorRE);
      if (iteratorMatch) {
        res.item = item.replace(forIteratorRE, "").trim();
        res.index = iteratorMatch[1].trim();
        if (iteratorMatch[2]) {
          res.collection = iteratorMatch[2].trim();
        }
      } else {
        res.item = item;
      }
      return res;
    }
    function getIterationScopeVariables(iteratorNames, item, index, items) {
      let scopeVariables = {};
      if (/^\[.*\]$/.test(iteratorNames.item) && Array.isArray(item)) {
        let names = iteratorNames.item.replace("[", "").replace("]", "").split(",").map((i) => i.trim());
        names.forEach((name, i) => {
          scopeVariables[name] = item[i];
        });
      } else if (/^\{.*\}$/.test(iteratorNames.item) && !Array.isArray(item) && typeof item === "object") {
        let names = iteratorNames.item.replace("{", "").replace("}", "").split(",").map((i) => i.trim());
        names.forEach((name) => {
          scopeVariables[name] = item[name];
        });
      } else {
        scopeVariables[iteratorNames.item] = item;
      }
      if (iteratorNames.index)
        scopeVariables[iteratorNames.index] = index;
      if (iteratorNames.collection)
        scopeVariables[iteratorNames.collection] = items;
      return scopeVariables;
    }
    function isNumeric3(subject) {
      return !Array.isArray(subject) && !isNaN(subject);
    }
    function handler3() {
    }
    handler3.inline = (el, { expression }, { cleanup: cleanup2 }) => {
      let root = closestRoot(el);
      if (!root._x_refs)
        root._x_refs = {};
      root._x_refs[expression] = el;
      cleanup2(() => delete root._x_refs[expression]);
    };
    directive2("ref", handler3);
    directive2("if", (el, { expression }, { effect: effect3, cleanup: cleanup2 }) => {
      let evaluate2 = evaluateLater(el, expression);
      let show = () => {
        if (el._x_currentIfEl)
          return el._x_currentIfEl;
        let clone2 = el.content.cloneNode(true).firstElementChild;
        addScopeToNode(clone2, {}, el);
        mutateDom(() => {
          el.after(clone2);
          initTree(clone2);
        });
        el._x_currentIfEl = clone2;
        el._x_undoIf = () => {
          walk(clone2, (node) => {
            if (!!node._x_effects) {
              node._x_effects.forEach(dequeueJob);
            }
          });
          clone2.remove();
          delete el._x_currentIfEl;
        };
        return clone2;
      };
      let hide = () => {
        if (!el._x_undoIf)
          return;
        el._x_undoIf();
        delete el._x_undoIf;
      };
      effect3(() => evaluate2((value) => {
        value ? show() : hide();
      }));
      cleanup2(() => el._x_undoIf && el._x_undoIf());
    });
    directive2("id", (el, { expression }, { evaluate: evaluate2 }) => {
      let names = evaluate2(expression);
      names.forEach((name) => setIdRoot(el, name));
    });
    mapAttributes(startingWith("@", into(prefix("on:"))));
    directive2("on", skipDuringClone((el, { value, modifiers, expression }, { cleanup: cleanup2 }) => {
      let evaluate2 = expression ? evaluateLater(el, expression) : () => {
      };
      if (el.tagName.toLowerCase() === "template") {
        if (!el._x_forwardEvents)
          el._x_forwardEvents = [];
        if (!el._x_forwardEvents.includes(value))
          el._x_forwardEvents.push(value);
      }
      let removeListener = on3(el, value, modifiers, (e) => {
        evaluate2(() => {
        }, { scope: { "$event": e }, params: [e] });
      });
      cleanup2(() => removeListener());
    }));
    warnMissingPluginDirective("Collapse", "collapse", "collapse");
    warnMissingPluginDirective("Intersect", "intersect", "intersect");
    warnMissingPluginDirective("Focus", "trap", "focus");
    warnMissingPluginDirective("Mask", "mask", "mask");
    function warnMissingPluginDirective(name, directiveName2, slug) {
      directive2(directiveName2, (el) => warn(`You can't use [x-${directiveName2}] without first installing the "${name}" plugin here: https://alpinejs.dev/plugins/${slug}`, el));
    }
    alpine_default.setEvaluator(normalEvaluator);
    alpine_default.setReactivityEngine({ reactive: import_reactivity9.reactive, effect: import_reactivity9.effect, release: import_reactivity9.stop, raw: import_reactivity9.toRaw });
    var src_default = alpine_default;
    var module_default = src_default;
  }
});

// ../alpine/packages/collapse/dist/module.cjs.js
var require_module_cjs2 = __commonJS({
  "../alpine/packages/collapse/dist/module.cjs.js"(exports, module) {
    var __defProp2 = Object.defineProperty;
    var __getOwnPropDesc2 = Object.getOwnPropertyDescriptor;
    var __getOwnPropNames2 = Object.getOwnPropertyNames;
    var __hasOwnProp2 = Object.prototype.hasOwnProperty;
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    var __copyProps2 = (to, from, except, desc) => {
      if (from && typeof from === "object" || typeof from === "function") {
        for (let key of __getOwnPropNames2(from))
          if (!__hasOwnProp2.call(to, key) && key !== except)
            __defProp2(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc2(from, key)) || desc.enumerable });
      }
      return to;
    };
    var __toCommonJS = (mod) => __copyProps2(__defProp2({}, "__esModule", { value: true }), mod);
    var module_exports = {};
    __export(module_exports, {
      default: () => module_default
    });
    module.exports = __toCommonJS(module_exports);
    function src_default(Alpine20) {
      Alpine20.directive("collapse", collapse2);
      collapse2.inline = (el, { modifiers }) => {
        if (!modifiers.includes("min"))
          return;
        el._x_doShow = () => {
        };
        el._x_doHide = () => {
        };
      };
      function collapse2(el, { modifiers }) {
        let duration = modifierValue(modifiers, "duration", 250) / 1e3;
        let floor = modifierValue(modifiers, "min", 0);
        let fullyHide = !modifiers.includes("min");
        if (!el._x_isShown)
          el.style.height = `${floor}px`;
        if (!el._x_isShown && fullyHide)
          el.hidden = true;
        if (!el._x_isShown)
          el.style.overflow = "hidden";
        let setFunction = (el2, styles) => {
          let revertFunction = Alpine20.setStyles(el2, styles);
          return styles.height ? () => {
          } : revertFunction;
        };
        let transitionStyles = {
          transitionProperty: "height",
          transitionDuration: `${duration}s`,
          transitionTimingFunction: "cubic-bezier(0.4, 0.0, 0.2, 1)"
        };
        el._x_transition = {
          in(before = () => {
          }, after = () => {
          }) {
            if (fullyHide)
              el.hidden = false;
            if (fullyHide)
              el.style.display = null;
            let current = el.getBoundingClientRect().height;
            el.style.height = "auto";
            let full = el.getBoundingClientRect().height;
            if (current === full) {
              current = floor;
            }
            Alpine20.transition(el, Alpine20.setStyles, {
              during: transitionStyles,
              start: { height: current + "px" },
              end: { height: full + "px" }
            }, () => el._x_isShown = true, () => {
              if (el.getBoundingClientRect().height == full) {
                el.style.overflow = null;
              }
            });
          },
          out(before = () => {
          }, after = () => {
          }) {
            let full = el.getBoundingClientRect().height;
            Alpine20.transition(el, setFunction, {
              during: transitionStyles,
              start: { height: full + "px" },
              end: { height: floor + "px" }
            }, () => el.style.overflow = "hidden", () => {
              el._x_isShown = false;
              if (el.style.height == `${floor}px` && fullyHide) {
                el.style.display = "none";
                el.hidden = true;
              }
            });
          }
        };
      }
    }
    function modifierValue(modifiers, key, fallback2) {
      if (modifiers.indexOf(key) === -1)
        return fallback2;
      const rawValue = modifiers[modifiers.indexOf(key) + 1];
      if (!rawValue)
        return fallback2;
      if (key === "duration") {
        let match = rawValue.match(/([0-9]+)ms/);
        if (match)
          return match[1];
      }
      if (key === "min") {
        let match = rawValue.match(/([0-9]+)px/);
        if (match)
          return match[1];
      }
      return rawValue;
    }
    var module_default = src_default;
  }
});

// ../../../../usr/local/lib/node_modules/@alpinejs/focus/dist/module.cjs.js
var require_module_cjs3 = __commonJS({
  "../../../../usr/local/lib/node_modules/@alpinejs/focus/dist/module.cjs.js"(exports) {
    var __create2 = Object.create;
    var __defProp2 = Object.defineProperty;
    var __getProtoOf2 = Object.getPrototypeOf;
    var __hasOwnProp2 = Object.prototype.hasOwnProperty;
    var __getOwnPropNames2 = Object.getOwnPropertyNames;
    var __getOwnPropDesc2 = Object.getOwnPropertyDescriptor;
    var __markAsModule = (target) => __defProp2(target, "__esModule", { value: true });
    var __commonJS2 = (callback, module2) => () => {
      if (!module2) {
        module2 = { exports: {} };
        callback(module2.exports, module2);
      }
      return module2.exports;
    };
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    var __exportStar = (target, module2, desc) => {
      if (module2 && typeof module2 === "object" || typeof module2 === "function") {
        for (let key of __getOwnPropNames2(module2))
          if (!__hasOwnProp2.call(target, key) && key !== "default")
            __defProp2(target, key, { get: () => module2[key], enumerable: !(desc = __getOwnPropDesc2(module2, key)) || desc.enumerable });
      }
      return target;
    };
    var __toModule = (module2) => {
      return __exportStar(__markAsModule(__defProp2(module2 != null ? __create2(__getProtoOf2(module2)) : {}, "default", module2 && module2.__esModule && "default" in module2 ? { get: () => module2.default, enumerable: true } : { value: module2, enumerable: true })), module2);
    };
    var require_dist = __commonJS2((exports2) => {
      "use strict";
      Object.defineProperty(exports2, "__esModule", { value: true });
      var candidateSelectors = ["input", "select", "textarea", "a[href]", "button", "[tabindex]", "audio[controls]", "video[controls]", '[contenteditable]:not([contenteditable="false"])', "details>summary:first-of-type", "details"];
      var candidateSelector = /* @__PURE__ */ candidateSelectors.join(",");
      var matches = typeof Element === "undefined" ? function() {
      } : Element.prototype.matches || Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
      var getCandidates = function getCandidates2(el, includeContainer, filter) {
        var candidates = Array.prototype.slice.apply(el.querySelectorAll(candidateSelector));
        if (includeContainer && matches.call(el, candidateSelector)) {
          candidates.unshift(el);
        }
        candidates = candidates.filter(filter);
        return candidates;
      };
      var isContentEditable = function isContentEditable2(node) {
        return node.contentEditable === "true";
      };
      var getTabindex = function getTabindex2(node) {
        var tabindexAttr = parseInt(node.getAttribute("tabindex"), 10);
        if (!isNaN(tabindexAttr)) {
          return tabindexAttr;
        }
        if (isContentEditable(node)) {
          return 0;
        }
        if ((node.nodeName === "AUDIO" || node.nodeName === "VIDEO" || node.nodeName === "DETAILS") && node.getAttribute("tabindex") === null) {
          return 0;
        }
        return node.tabIndex;
      };
      var sortOrderedTabbables = function sortOrderedTabbables2(a, b) {
        return a.tabIndex === b.tabIndex ? a.documentOrder - b.documentOrder : a.tabIndex - b.tabIndex;
      };
      var isInput = function isInput2(node) {
        return node.tagName === "INPUT";
      };
      var isHiddenInput = function isHiddenInput2(node) {
        return isInput(node) && node.type === "hidden";
      };
      var isDetailsWithSummary = function isDetailsWithSummary2(node) {
        var r = node.tagName === "DETAILS" && Array.prototype.slice.apply(node.children).some(function(child) {
          return child.tagName === "SUMMARY";
        });
        return r;
      };
      var getCheckedRadio = function getCheckedRadio2(nodes, form) {
        for (var i = 0; i < nodes.length; i++) {
          if (nodes[i].checked && nodes[i].form === form) {
            return nodes[i];
          }
        }
      };
      var isTabbableRadio = function isTabbableRadio2(node) {
        if (!node.name) {
          return true;
        }
        var radioScope = node.form || node.ownerDocument;
        var queryRadios = function queryRadios2(name) {
          return radioScope.querySelectorAll('input[type="radio"][name="' + name + '"]');
        };
        var radioSet;
        if (typeof window !== "undefined" && typeof window.CSS !== "undefined" && typeof window.CSS.escape === "function") {
          radioSet = queryRadios(window.CSS.escape(node.name));
        } else {
          try {
            radioSet = queryRadios(node.name);
          } catch (err) {
            console.error("Looks like you have a radio button with a name attribute containing invalid CSS selector characters and need the CSS.escape polyfill: %s", err.message);
            return false;
          }
        }
        var checked = getCheckedRadio(radioSet, node.form);
        return !checked || checked === node;
      };
      var isRadio = function isRadio2(node) {
        return isInput(node) && node.type === "radio";
      };
      var isNonTabbableRadio = function isNonTabbableRadio2(node) {
        return isRadio(node) && !isTabbableRadio(node);
      };
      var isHidden = function isHidden2(node, displayCheck) {
        if (getComputedStyle(node).visibility === "hidden") {
          return true;
        }
        var isDirectSummary = matches.call(node, "details>summary:first-of-type");
        var nodeUnderDetails = isDirectSummary ? node.parentElement : node;
        if (matches.call(nodeUnderDetails, "details:not([open]) *")) {
          return true;
        }
        if (!displayCheck || displayCheck === "full") {
          while (node) {
            if (getComputedStyle(node).display === "none") {
              return true;
            }
            node = node.parentElement;
          }
        } else if (displayCheck === "non-zero-area") {
          var _node$getBoundingClie = node.getBoundingClientRect(), width = _node$getBoundingClie.width, height = _node$getBoundingClie.height;
          return width === 0 && height === 0;
        }
        return false;
      };
      var isDisabledFromFieldset = function isDisabledFromFieldset2(node) {
        if (isInput(node) || node.tagName === "SELECT" || node.tagName === "TEXTAREA" || node.tagName === "BUTTON") {
          var parentNode = node.parentElement;
          while (parentNode) {
            if (parentNode.tagName === "FIELDSET" && parentNode.disabled) {
              for (var i = 0; i < parentNode.children.length; i++) {
                var child = parentNode.children.item(i);
                if (child.tagName === "LEGEND") {
                  if (child.contains(node)) {
                    return false;
                  }
                  return true;
                }
              }
              return true;
            }
            parentNode = parentNode.parentElement;
          }
        }
        return false;
      };
      var isNodeMatchingSelectorFocusable = function isNodeMatchingSelectorFocusable2(options, node) {
        if (node.disabled || isHiddenInput(node) || isHidden(node, options.displayCheck) || isDetailsWithSummary(node) || isDisabledFromFieldset(node)) {
          return false;
        }
        return true;
      };
      var isNodeMatchingSelectorTabbable = function isNodeMatchingSelectorTabbable2(options, node) {
        if (!isNodeMatchingSelectorFocusable(options, node) || isNonTabbableRadio(node) || getTabindex(node) < 0) {
          return false;
        }
        return true;
      };
      var tabbable = function tabbable2(el, options) {
        options = options || {};
        var regularTabbables = [];
        var orderedTabbables = [];
        var candidates = getCandidates(el, options.includeContainer, isNodeMatchingSelectorTabbable.bind(null, options));
        candidates.forEach(function(candidate, i) {
          var candidateTabindex = getTabindex(candidate);
          if (candidateTabindex === 0) {
            regularTabbables.push(candidate);
          } else {
            orderedTabbables.push({
              documentOrder: i,
              tabIndex: candidateTabindex,
              node: candidate
            });
          }
        });
        var tabbableNodes = orderedTabbables.sort(sortOrderedTabbables).map(function(a) {
          return a.node;
        }).concat(regularTabbables);
        return tabbableNodes;
      };
      var focusable2 = function focusable3(el, options) {
        options = options || {};
        var candidates = getCandidates(el, options.includeContainer, isNodeMatchingSelectorFocusable.bind(null, options));
        return candidates;
      };
      var isTabbable = function isTabbable2(node, options) {
        options = options || {};
        if (!node) {
          throw new Error("No node provided");
        }
        if (matches.call(node, candidateSelector) === false) {
          return false;
        }
        return isNodeMatchingSelectorTabbable(options, node);
      };
      var focusableCandidateSelector = /* @__PURE__ */ candidateSelectors.concat("iframe").join(",");
      var isFocusable2 = function isFocusable3(node, options) {
        options = options || {};
        if (!node) {
          throw new Error("No node provided");
        }
        if (matches.call(node, focusableCandidateSelector) === false) {
          return false;
        }
        return isNodeMatchingSelectorFocusable(options, node);
      };
      exports2.focusable = focusable2;
      exports2.isFocusable = isFocusable2;
      exports2.isTabbable = isTabbable;
      exports2.tabbable = tabbable;
    });
    var require_focus_trap = __commonJS2((exports2) => {
      "use strict";
      Object.defineProperty(exports2, "__esModule", { value: true });
      var tabbable = require_dist();
      function ownKeys(object, enumerableOnly) {
        var keys = Object.keys(object);
        if (Object.getOwnPropertySymbols) {
          var symbols = Object.getOwnPropertySymbols(object);
          if (enumerableOnly) {
            symbols = symbols.filter(function(sym) {
              return Object.getOwnPropertyDescriptor(object, sym).enumerable;
            });
          }
          keys.push.apply(keys, symbols);
        }
        return keys;
      }
      function _objectSpread2(target) {
        for (var i = 1; i < arguments.length; i++) {
          var source = arguments[i] != null ? arguments[i] : {};
          if (i % 2) {
            ownKeys(Object(source), true).forEach(function(key) {
              _defineProperty(target, key, source[key]);
            });
          } else if (Object.getOwnPropertyDescriptors) {
            Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
          } else {
            ownKeys(Object(source)).forEach(function(key) {
              Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
            });
          }
        }
        return target;
      }
      function _defineProperty(obj, key, value) {
        if (key in obj) {
          Object.defineProperty(obj, key, {
            value,
            enumerable: true,
            configurable: true,
            writable: true
          });
        } else {
          obj[key] = value;
        }
        return obj;
      }
      var activeFocusTraps = function() {
        var trapQueue = [];
        return {
          activateTrap: function activateTrap(trap) {
            if (trapQueue.length > 0) {
              var activeTrap = trapQueue[trapQueue.length - 1];
              if (activeTrap !== trap) {
                activeTrap.pause();
              }
            }
            var trapIndex = trapQueue.indexOf(trap);
            if (trapIndex === -1) {
              trapQueue.push(trap);
            } else {
              trapQueue.splice(trapIndex, 1);
              trapQueue.push(trap);
            }
          },
          deactivateTrap: function deactivateTrap(trap) {
            var trapIndex = trapQueue.indexOf(trap);
            if (trapIndex !== -1) {
              trapQueue.splice(trapIndex, 1);
            }
            if (trapQueue.length > 0) {
              trapQueue[trapQueue.length - 1].unpause();
            }
          }
        };
      }();
      var isSelectableInput = function isSelectableInput2(node) {
        return node.tagName && node.tagName.toLowerCase() === "input" && typeof node.select === "function";
      };
      var isEscapeEvent = function isEscapeEvent2(e) {
        return e.key === "Escape" || e.key === "Esc" || e.keyCode === 27;
      };
      var isTabEvent = function isTabEvent2(e) {
        return e.key === "Tab" || e.keyCode === 9;
      };
      var delay = function delay2(fn) {
        return setTimeout(fn, 0);
      };
      var findIndex = function findIndex2(arr, fn) {
        var idx = -1;
        arr.every(function(value, i) {
          if (fn(value)) {
            idx = i;
            return false;
          }
          return true;
        });
        return idx;
      };
      var valueOrHandler = function valueOrHandler2(value) {
        for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          params[_key - 1] = arguments[_key];
        }
        return typeof value === "function" ? value.apply(void 0, params) : value;
      };
      var createFocusTrap2 = function createFocusTrap3(elements, userOptions) {
        var doc = document;
        var config = _objectSpread2({
          returnFocusOnDeactivate: true,
          escapeDeactivates: true,
          delayInitialFocus: true
        }, userOptions);
        var state = {
          containers: [],
          tabbableGroups: [],
          nodeFocusedBeforeActivation: null,
          mostRecentlyFocusedNode: null,
          active: false,
          paused: false,
          delayInitialFocusTimer: void 0
        };
        var trap;
        var getOption = function getOption2(configOverrideOptions, optionName, configOptionName) {
          return configOverrideOptions && configOverrideOptions[optionName] !== void 0 ? configOverrideOptions[optionName] : config[configOptionName || optionName];
        };
        var containersContain = function containersContain2(element) {
          return state.containers.some(function(container) {
            return container.contains(element);
          });
        };
        var getNodeForOption = function getNodeForOption2(optionName) {
          var optionValue = config[optionName];
          if (!optionValue) {
            return null;
          }
          var node = optionValue;
          if (typeof optionValue === "string") {
            node = doc.querySelector(optionValue);
            if (!node) {
              throw new Error("`".concat(optionName, "` refers to no known node"));
            }
          }
          if (typeof optionValue === "function") {
            node = optionValue();
            if (!node) {
              throw new Error("`".concat(optionName, "` did not return a node"));
            }
          }
          return node;
        };
        var getInitialFocusNode = function getInitialFocusNode2() {
          var node;
          if (getOption({}, "initialFocus") === false) {
            return false;
          }
          if (getNodeForOption("initialFocus") !== null) {
            node = getNodeForOption("initialFocus");
          } else if (containersContain(doc.activeElement)) {
            node = doc.activeElement;
          } else {
            var firstTabbableGroup = state.tabbableGroups[0];
            var firstTabbableNode = firstTabbableGroup && firstTabbableGroup.firstTabbableNode;
            node = firstTabbableNode || getNodeForOption("fallbackFocus");
          }
          if (!node) {
            throw new Error("Your focus-trap needs to have at least one focusable element");
          }
          return node;
        };
        var updateTabbableNodes = function updateTabbableNodes2() {
          state.tabbableGroups = state.containers.map(function(container) {
            var tabbableNodes = tabbable.tabbable(container);
            if (tabbableNodes.length > 0) {
              return {
                container,
                firstTabbableNode: tabbableNodes[0],
                lastTabbableNode: tabbableNodes[tabbableNodes.length - 1]
              };
            }
            return void 0;
          }).filter(function(group) {
            return !!group;
          });
          if (state.tabbableGroups.length <= 0 && !getNodeForOption("fallbackFocus")) {
            throw new Error("Your focus-trap must have at least one container with at least one tabbable node in it at all times");
          }
        };
        var tryFocus = function tryFocus2(node) {
          if (node === false) {
            return;
          }
          if (node === doc.activeElement) {
            return;
          }
          if (!node || !node.focus) {
            tryFocus2(getInitialFocusNode());
            return;
          }
          node.focus({
            preventScroll: !!config.preventScroll
          });
          state.mostRecentlyFocusedNode = node;
          if (isSelectableInput(node)) {
            node.select();
          }
        };
        var getReturnFocusNode = function getReturnFocusNode2(previousActiveElement) {
          var node = getNodeForOption("setReturnFocus");
          return node ? node : previousActiveElement;
        };
        var checkPointerDown = function checkPointerDown2(e) {
          if (containersContain(e.target)) {
            return;
          }
          if (valueOrHandler(config.clickOutsideDeactivates, e)) {
            trap.deactivate({
              returnFocus: config.returnFocusOnDeactivate && !tabbable.isFocusable(e.target)
            });
            return;
          }
          if (valueOrHandler(config.allowOutsideClick, e)) {
            return;
          }
          e.preventDefault();
        };
        var checkFocusIn = function checkFocusIn2(e) {
          var targetContained = containersContain(e.target);
          if (targetContained || e.target instanceof Document) {
            if (targetContained) {
              state.mostRecentlyFocusedNode = e.target;
            }
          } else {
            e.stopImmediatePropagation();
            tryFocus(state.mostRecentlyFocusedNode || getInitialFocusNode());
          }
        };
        var checkTab = function checkTab2(e) {
          updateTabbableNodes();
          var destinationNode = null;
          if (state.tabbableGroups.length > 0) {
            var containerIndex = findIndex(state.tabbableGroups, function(_ref) {
              var container = _ref.container;
              return container.contains(e.target);
            });
            if (containerIndex < 0) {
              if (e.shiftKey) {
                destinationNode = state.tabbableGroups[state.tabbableGroups.length - 1].lastTabbableNode;
              } else {
                destinationNode = state.tabbableGroups[0].firstTabbableNode;
              }
            } else if (e.shiftKey) {
              var startOfGroupIndex = findIndex(state.tabbableGroups, function(_ref2) {
                var firstTabbableNode = _ref2.firstTabbableNode;
                return e.target === firstTabbableNode;
              });
              if (startOfGroupIndex < 0 && state.tabbableGroups[containerIndex].container === e.target) {
                startOfGroupIndex = containerIndex;
              }
              if (startOfGroupIndex >= 0) {
                var destinationGroupIndex = startOfGroupIndex === 0 ? state.tabbableGroups.length - 1 : startOfGroupIndex - 1;
                var destinationGroup = state.tabbableGroups[destinationGroupIndex];
                destinationNode = destinationGroup.lastTabbableNode;
              }
            } else {
              var lastOfGroupIndex = findIndex(state.tabbableGroups, function(_ref3) {
                var lastTabbableNode = _ref3.lastTabbableNode;
                return e.target === lastTabbableNode;
              });
              if (lastOfGroupIndex < 0 && state.tabbableGroups[containerIndex].container === e.target) {
                lastOfGroupIndex = containerIndex;
              }
              if (lastOfGroupIndex >= 0) {
                var _destinationGroupIndex = lastOfGroupIndex === state.tabbableGroups.length - 1 ? 0 : lastOfGroupIndex + 1;
                var _destinationGroup = state.tabbableGroups[_destinationGroupIndex];
                destinationNode = _destinationGroup.firstTabbableNode;
              }
            }
          } else {
            destinationNode = getNodeForOption("fallbackFocus");
          }
          if (destinationNode) {
            e.preventDefault();
            tryFocus(destinationNode);
          }
        };
        var checkKey = function checkKey2(e) {
          if (isEscapeEvent(e) && valueOrHandler(config.escapeDeactivates) !== false) {
            e.preventDefault();
            trap.deactivate();
            return;
          }
          if (isTabEvent(e)) {
            checkTab(e);
            return;
          }
        };
        var checkClick = function checkClick2(e) {
          if (valueOrHandler(config.clickOutsideDeactivates, e)) {
            return;
          }
          if (containersContain(e.target)) {
            return;
          }
          if (valueOrHandler(config.allowOutsideClick, e)) {
            return;
          }
          e.preventDefault();
          e.stopImmediatePropagation();
        };
        var addListeners = function addListeners2() {
          if (!state.active) {
            return;
          }
          activeFocusTraps.activateTrap(trap);
          state.delayInitialFocusTimer = config.delayInitialFocus ? delay(function() {
            tryFocus(getInitialFocusNode());
          }) : tryFocus(getInitialFocusNode());
          doc.addEventListener("focusin", checkFocusIn, true);
          doc.addEventListener("mousedown", checkPointerDown, {
            capture: true,
            passive: false
          });
          doc.addEventListener("touchstart", checkPointerDown, {
            capture: true,
            passive: false
          });
          doc.addEventListener("click", checkClick, {
            capture: true,
            passive: false
          });
          doc.addEventListener("keydown", checkKey, {
            capture: true,
            passive: false
          });
          return trap;
        };
        var removeListeners = function removeListeners2() {
          if (!state.active) {
            return;
          }
          doc.removeEventListener("focusin", checkFocusIn, true);
          doc.removeEventListener("mousedown", checkPointerDown, true);
          doc.removeEventListener("touchstart", checkPointerDown, true);
          doc.removeEventListener("click", checkClick, true);
          doc.removeEventListener("keydown", checkKey, true);
          return trap;
        };
        trap = {
          activate: function activate(activateOptions) {
            if (state.active) {
              return this;
            }
            var onActivate = getOption(activateOptions, "onActivate");
            var onPostActivate = getOption(activateOptions, "onPostActivate");
            var checkCanFocusTrap = getOption(activateOptions, "checkCanFocusTrap");
            if (!checkCanFocusTrap) {
              updateTabbableNodes();
            }
            state.active = true;
            state.paused = false;
            state.nodeFocusedBeforeActivation = doc.activeElement;
            if (onActivate) {
              onActivate();
            }
            var finishActivation = function finishActivation2() {
              if (checkCanFocusTrap) {
                updateTabbableNodes();
              }
              addListeners();
              if (onPostActivate) {
                onPostActivate();
              }
            };
            if (checkCanFocusTrap) {
              checkCanFocusTrap(state.containers.concat()).then(finishActivation, finishActivation);
              return this;
            }
            finishActivation();
            return this;
          },
          deactivate: function deactivate(deactivateOptions) {
            if (!state.active) {
              return this;
            }
            clearTimeout(state.delayInitialFocusTimer);
            state.delayInitialFocusTimer = void 0;
            removeListeners();
            state.active = false;
            state.paused = false;
            activeFocusTraps.deactivateTrap(trap);
            var onDeactivate = getOption(deactivateOptions, "onDeactivate");
            var onPostDeactivate = getOption(deactivateOptions, "onPostDeactivate");
            var checkCanReturnFocus = getOption(deactivateOptions, "checkCanReturnFocus");
            if (onDeactivate) {
              onDeactivate();
            }
            var returnFocus = getOption(deactivateOptions, "returnFocus", "returnFocusOnDeactivate");
            var finishDeactivation = function finishDeactivation2() {
              delay(function() {
                if (returnFocus) {
                  tryFocus(getReturnFocusNode(state.nodeFocusedBeforeActivation));
                }
                if (onPostDeactivate) {
                  onPostDeactivate();
                }
              });
            };
            if (returnFocus && checkCanReturnFocus) {
              checkCanReturnFocus(getReturnFocusNode(state.nodeFocusedBeforeActivation)).then(finishDeactivation, finishDeactivation);
              return this;
            }
            finishDeactivation();
            return this;
          },
          pause: function pause() {
            if (state.paused || !state.active) {
              return this;
            }
            state.paused = true;
            removeListeners();
            return this;
          },
          unpause: function unpause() {
            if (!state.paused || !state.active) {
              return this;
            }
            state.paused = false;
            updateTabbableNodes();
            addListeners();
            return this;
          },
          updateContainerElements: function updateContainerElements(containerElements) {
            var elementsAsArray = [].concat(containerElements).filter(Boolean);
            state.containers = elementsAsArray.map(function(element) {
              return typeof element === "string" ? doc.querySelector(element) : element;
            });
            if (state.active) {
              updateTabbableNodes();
            }
            return this;
          }
        };
        trap.updateContainerElements(elements);
        return trap;
      };
      exports2.createFocusTrap = createFocusTrap2;
    });
    __markAsModule(exports);
    __export(exports, {
      default: () => module_default
    });
    var import_focus_trap = __toModule(require_focus_trap());
    var import_tabbable = __toModule(require_dist());
    function src_default(Alpine20) {
      let lastFocused;
      let currentFocused;
      window.addEventListener("focusin", () => {
        lastFocused = currentFocused;
        currentFocused = document.activeElement;
      });
      Alpine20.magic("focus", (el) => {
        let within = el;
        return {
          __noscroll: false,
          __wrapAround: false,
          within(el2) {
            within = el2;
            return this;
          },
          withoutScrolling() {
            this.__noscroll = true;
            return this;
          },
          noscroll() {
            this.__noscroll = true;
            return this;
          },
          withWrapAround() {
            this.__wrapAround = true;
            return this;
          },
          wrap() {
            return this.withWrapAround();
          },
          focusable(el2) {
            return (0, import_tabbable.isFocusable)(el2);
          },
          previouslyFocused() {
            return lastFocused;
          },
          lastFocused() {
            return lastFocused;
          },
          focused() {
            return currentFocused;
          },
          focusables() {
            if (Array.isArray(within))
              return within;
            return (0, import_tabbable.focusable)(within, { displayCheck: "none" });
          },
          all() {
            return this.focusables();
          },
          isFirst(el2) {
            let els2 = this.all();
            return els2[0] && els2[0].isSameNode(el2);
          },
          isLast(el2) {
            let els2 = this.all();
            return els2.length && els2.slice(-1)[0].isSameNode(el2);
          },
          getFirst() {
            return this.all()[0];
          },
          getLast() {
            return this.all().slice(-1)[0];
          },
          getNext() {
            let list = this.all();
            let current = document.activeElement;
            if (list.indexOf(current) === -1)
              return;
            if (this.__wrapAround && list.indexOf(current) === list.length - 1) {
              return list[0];
            }
            return list[list.indexOf(current) + 1];
          },
          getPrevious() {
            let list = this.all();
            let current = document.activeElement;
            if (list.indexOf(current) === -1)
              return;
            if (this.__wrapAround && list.indexOf(current) === 0) {
              return list.slice(-1)[0];
            }
            return list[list.indexOf(current) - 1];
          },
          first() {
            this.focus(this.getFirst());
          },
          last() {
            this.focus(this.getLast());
          },
          next() {
            this.focus(this.getNext());
          },
          previous() {
            this.focus(this.getPrevious());
          },
          prev() {
            return this.previous();
          },
          focus(el2) {
            if (!el2)
              return;
            setTimeout(() => {
              if (!el2.hasAttribute("tabindex"))
                el2.setAttribute("tabindex", "0");
              el2.focus({ preventScroll: this._noscroll });
            });
          }
        };
      });
      Alpine20.directive("trap", Alpine20.skipDuringClone((el, { expression, modifiers }, { effect, evaluateLater, cleanup: cleanup2 }) => {
        let evaluator = evaluateLater(expression);
        let oldValue = false;
        let options = {
          escapeDeactivates: false,
          allowOutsideClick: true,
          fallbackFocus: () => el
        };
        let autofocusEl = el.querySelector("[autofocus]");
        if (autofocusEl)
          options.initialFocus = autofocusEl;
        let trap = (0, import_focus_trap.createFocusTrap)(el, options);
        let undoInert = () => {
        };
        let undoDisableScrolling = () => {
        };
        const releaseFocus = () => {
          undoInert();
          undoInert = () => {
          };
          undoDisableScrolling();
          undoDisableScrolling = () => {
          };
          trap.deactivate({
            returnFocus: !modifiers.includes("noreturn")
          });
        };
        effect(() => evaluator((value) => {
          if (oldValue === value)
            return;
          if (value && !oldValue) {
            setTimeout(() => {
              if (modifiers.includes("inert"))
                undoInert = setInert(el);
              if (modifiers.includes("noscroll"))
                undoDisableScrolling = disableScrolling();
              trap.activate();
            });
          }
          if (!value && oldValue) {
            releaseFocus();
          }
          oldValue = !!value;
        }));
        cleanup2(releaseFocus);
      }, (el, { expression, modifiers }, { evaluate }) => {
        if (modifiers.includes("inert") && evaluate(expression))
          setInert(el);
      }));
    }
    function setInert(el) {
      let undos = [];
      crawlSiblingsUp(el, (sibling) => {
        let cache = sibling.hasAttribute("aria-hidden");
        sibling.setAttribute("aria-hidden", "true");
        undos.push(() => cache || sibling.removeAttribute("aria-hidden"));
      });
      return () => {
        while (undos.length)
          undos.pop()();
      };
    }
    function crawlSiblingsUp(el, callback) {
      if (el.isSameNode(document.body) || !el.parentNode)
        return;
      Array.from(el.parentNode.children).forEach((sibling) => {
        if (sibling.isSameNode(el)) {
          crawlSiblingsUp(el.parentNode, callback);
        } else {
          callback(sibling);
        }
      });
    }
    function disableScrolling() {
      let overflow = document.documentElement.style.overflow;
      let paddingRight = document.documentElement.style.paddingRight;
      let scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
      document.documentElement.style.overflow = "hidden";
      document.documentElement.style.paddingRight = `${scrollbarWidth}px`;
      return () => {
        document.documentElement.style.overflow = overflow;
        document.documentElement.style.paddingRight = paddingRight;
      };
    }
    var module_default = src_default;
  }
});

// ../../../../usr/local/lib/node_modules/@alpinejs/persist/dist/module.cjs.js
var require_module_cjs4 = __commonJS({
  "../../../../usr/local/lib/node_modules/@alpinejs/persist/dist/module.cjs.js"(exports) {
    var __defProp2 = Object.defineProperty;
    var __markAsModule = (target) => __defProp2(target, "__esModule", { value: true });
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    __markAsModule(exports);
    __export(exports, {
      default: () => module_default
    });
    function src_default(Alpine20) {
      let persist2 = () => {
        let alias;
        let storage = localStorage;
        return Alpine20.interceptor((initialValue, getter, setter, path, key) => {
          let lookup = alias || `_x_${path}`;
          let initial = storageHas(lookup, storage) ? storageGet(lookup, storage) : initialValue;
          setter(initial);
          Alpine20.effect(() => {
            let value = getter();
            storageSet(lookup, value, storage);
            setter(value);
          });
          return initial;
        }, (func) => {
          func.as = (key) => {
            alias = key;
            return func;
          }, func.using = (target) => {
            storage = target;
            return func;
          };
        });
      };
      Object.defineProperty(Alpine20, "$persist", { get: () => persist2() });
      Alpine20.magic("persist", persist2);
      Alpine20.persist = (key, { get, set }, storage = localStorage) => {
        let initial = storageHas(key, storage) ? storageGet(key, storage) : get();
        set(initial);
        Alpine20.effect(() => {
          let value = get();
          storageSet(key, value, storage);
          set(value);
        });
      };
    }
    function storageHas(key, storage) {
      return storage.getItem(key) !== null;
    }
    function storageGet(key, storage) {
      return JSON.parse(storage.getItem(key, storage));
    }
    function storageSet(key, value, storage) {
      storage.setItem(key, JSON.stringify(value));
    }
    var module_default = src_default;
  }
});

// ../alpine/packages/intersect/dist/module.cjs.js
var require_module_cjs5 = __commonJS({
  "../alpine/packages/intersect/dist/module.cjs.js"(exports, module) {
    var __defProp2 = Object.defineProperty;
    var __getOwnPropDesc2 = Object.getOwnPropertyDescriptor;
    var __getOwnPropNames2 = Object.getOwnPropertyNames;
    var __hasOwnProp2 = Object.prototype.hasOwnProperty;
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    var __copyProps2 = (to, from, except, desc) => {
      if (from && typeof from === "object" || typeof from === "function") {
        for (let key of __getOwnPropNames2(from))
          if (!__hasOwnProp2.call(to, key) && key !== except)
            __defProp2(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc2(from, key)) || desc.enumerable });
      }
      return to;
    };
    var __toCommonJS = (mod) => __copyProps2(__defProp2({}, "__esModule", { value: true }), mod);
    var module_exports = {};
    __export(module_exports, {
      default: () => module_default
    });
    module.exports = __toCommonJS(module_exports);
    function src_default(Alpine20) {
      Alpine20.directive("intersect", (el, { value, expression, modifiers }, { evaluateLater, cleanup: cleanup2 }) => {
        let evaluate = evaluateLater(expression);
        let options = {
          rootMargin: getRootMargin(modifiers),
          threshold: getThreshhold(modifiers)
        };
        let observer = new IntersectionObserver((entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting === (value === "leave"))
              return;
            evaluate();
            modifiers.includes("once") && observer.disconnect();
          });
        }, options);
        observer.observe(el);
        cleanup2(() => {
          observer.disconnect();
        });
      });
    }
    function getThreshhold(modifiers) {
      if (modifiers.includes("full"))
        return 0.99;
      if (modifiers.includes("half"))
        return 0.5;
      if (!modifiers.includes("threshold"))
        return 0;
      let threshold = modifiers[modifiers.indexOf("threshold") + 1];
      if (threshold === "100")
        return 1;
      if (threshold === "0")
        return 0;
      return Number(`.${threshold}`);
    }
    function getLengthValue(rawValue) {
      let match = rawValue.match(/^(-?[0-9]+)(px|%)?$/);
      return match ? match[1] + (match[2] || "px") : void 0;
    }
    function getRootMargin(modifiers) {
      const key = "margin";
      const fallback2 = "0px 0px 0px 0px";
      const index = modifiers.indexOf(key);
      if (index === -1)
        return fallback2;
      let values = [];
      for (let i = 1; i < 5; i++) {
        values.push(getLengthValue(modifiers[index + i] || ""));
      }
      values = values.filter((v) => v !== void 0);
      return values.length ? values.join(" ").trim() : fallback2;
    }
    var module_default = src_default;
  }
});

// node_modules/nprogress/nprogress.js
var require_nprogress = __commonJS({
  "node_modules/nprogress/nprogress.js"(exports, module) {
    (function(root, factory) {
      if (typeof define === "function" && define.amd) {
        define(factory);
      } else if (typeof exports === "object") {
        module.exports = factory();
      } else {
        root.NProgress = factory();
      }
    })(exports, function() {
      var NProgress2 = {};
      NProgress2.version = "0.2.0";
      var Settings = NProgress2.settings = {
        minimum: 0.08,
        easing: "ease",
        positionUsing: "",
        speed: 200,
        trickle: true,
        trickleRate: 0.02,
        trickleSpeed: 800,
        showSpinner: true,
        barSelector: '[role="bar"]',
        spinnerSelector: '[role="spinner"]',
        parent: "body",
        template: '<div class="bar" role="bar"><div class="peg"></div></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
      };
      NProgress2.configure = function(options) {
        var key, value;
        for (key in options) {
          value = options[key];
          if (value !== void 0 && options.hasOwnProperty(key))
            Settings[key] = value;
        }
        return this;
      };
      NProgress2.status = null;
      NProgress2.set = function(n) {
        var started = NProgress2.isStarted();
        n = clamp(n, Settings.minimum, 1);
        NProgress2.status = n === 1 ? null : n;
        var progress = NProgress2.render(!started), bar = progress.querySelector(Settings.barSelector), speed = Settings.speed, ease = Settings.easing;
        progress.offsetWidth;
        queue(function(next) {
          if (Settings.positionUsing === "")
            Settings.positionUsing = NProgress2.getPositioningCSS();
          css(bar, barPositionCSS(n, speed, ease));
          if (n === 1) {
            css(progress, {
              transition: "none",
              opacity: 1
            });
            progress.offsetWidth;
            setTimeout(function() {
              css(progress, {
                transition: "all " + speed + "ms linear",
                opacity: 0
              });
              setTimeout(function() {
                NProgress2.remove();
                next();
              }, speed);
            }, speed);
          } else {
            setTimeout(next, speed);
          }
        });
        return this;
      };
      NProgress2.isStarted = function() {
        return typeof NProgress2.status === "number";
      };
      NProgress2.start = function() {
        if (!NProgress2.status)
          NProgress2.set(0);
        var work = function() {
          setTimeout(function() {
            if (!NProgress2.status)
              return;
            NProgress2.trickle();
            work();
          }, Settings.trickleSpeed);
        };
        if (Settings.trickle)
          work();
        return this;
      };
      NProgress2.done = function(force) {
        if (!force && !NProgress2.status)
          return this;
        return NProgress2.inc(0.3 + 0.5 * Math.random()).set(1);
      };
      NProgress2.inc = function(amount) {
        var n = NProgress2.status;
        if (!n) {
          return NProgress2.start();
        } else {
          if (typeof amount !== "number") {
            amount = (1 - n) * clamp(Math.random() * n, 0.1, 0.95);
          }
          n = clamp(n + amount, 0, 0.994);
          return NProgress2.set(n);
        }
      };
      NProgress2.trickle = function() {
        return NProgress2.inc(Math.random() * Settings.trickleRate);
      };
      (function() {
        var initial = 0, current = 0;
        NProgress2.promise = function($promise) {
          if (!$promise || $promise.state() === "resolved") {
            return this;
          }
          if (current === 0) {
            NProgress2.start();
          }
          initial++;
          current++;
          $promise.always(function() {
            current--;
            if (current === 0) {
              initial = 0;
              NProgress2.done();
            } else {
              NProgress2.set((initial - current) / initial);
            }
          });
          return this;
        };
      })();
      NProgress2.render = function(fromStart) {
        if (NProgress2.isRendered())
          return document.getElementById("nprogress");
        addClass(document.documentElement, "nprogress-busy");
        var progress = document.createElement("div");
        progress.id = "nprogress";
        progress.innerHTML = Settings.template;
        var bar = progress.querySelector(Settings.barSelector), perc = fromStart ? "-100" : toBarPerc(NProgress2.status || 0), parent = document.querySelector(Settings.parent), spinner;
        css(bar, {
          transition: "all 0 linear",
          transform: "translate3d(" + perc + "%,0,0)"
        });
        if (!Settings.showSpinner) {
          spinner = progress.querySelector(Settings.spinnerSelector);
          spinner && removeElement(spinner);
        }
        if (parent != document.body) {
          addClass(parent, "nprogress-custom-parent");
        }
        parent.appendChild(progress);
        return progress;
      };
      NProgress2.remove = function() {
        removeClass(document.documentElement, "nprogress-busy");
        removeClass(document.querySelector(Settings.parent), "nprogress-custom-parent");
        var progress = document.getElementById("nprogress");
        progress && removeElement(progress);
      };
      NProgress2.isRendered = function() {
        return !!document.getElementById("nprogress");
      };
      NProgress2.getPositioningCSS = function() {
        var bodyStyle = document.body.style;
        var vendorPrefix = "WebkitTransform" in bodyStyle ? "Webkit" : "MozTransform" in bodyStyle ? "Moz" : "msTransform" in bodyStyle ? "ms" : "OTransform" in bodyStyle ? "O" : "";
        if (vendorPrefix + "Perspective" in bodyStyle) {
          return "translate3d";
        } else if (vendorPrefix + "Transform" in bodyStyle) {
          return "translate";
        } else {
          return "margin";
        }
      };
      function clamp(n, min, max) {
        if (n < min)
          return min;
        if (n > max)
          return max;
        return n;
      }
      function toBarPerc(n) {
        return (-1 + n) * 100;
      }
      function barPositionCSS(n, speed, ease) {
        var barCSS;
        if (Settings.positionUsing === "translate3d") {
          barCSS = { transform: "translate3d(" + toBarPerc(n) + "%,0,0)" };
        } else if (Settings.positionUsing === "translate") {
          barCSS = { transform: "translate(" + toBarPerc(n) + "%,0)" };
        } else {
          barCSS = { "margin-left": toBarPerc(n) + "%" };
        }
        barCSS.transition = "all " + speed + "ms " + ease;
        return barCSS;
      }
      var queue = function() {
        var pending = [];
        function next() {
          var fn = pending.shift();
          if (fn) {
            fn(next);
          }
        }
        return function(fn) {
          pending.push(fn);
          if (pending.length == 1)
            next();
        };
      }();
      var css = function() {
        var cssPrefixes = ["Webkit", "O", "Moz", "ms"], cssProps = {};
        function camelCase(string) {
          return string.replace(/^-ms-/, "ms-").replace(/-([\da-z])/gi, function(match, letter) {
            return letter.toUpperCase();
          });
        }
        function getVendorProp(name) {
          var style = document.body.style;
          if (name in style)
            return name;
          var i = cssPrefixes.length, capName = name.charAt(0).toUpperCase() + name.slice(1), vendorName;
          while (i--) {
            vendorName = cssPrefixes[i] + capName;
            if (vendorName in style)
              return vendorName;
          }
          return name;
        }
        function getStyleProp(name) {
          name = camelCase(name);
          return cssProps[name] || (cssProps[name] = getVendorProp(name));
        }
        function applyCss(element, prop, value) {
          prop = getStyleProp(prop);
          element.style[prop] = value;
        }
        return function(element, properties2) {
          var args = arguments, prop, value;
          if (args.length == 2) {
            for (prop in properties2) {
              value = properties2[prop];
              if (value !== void 0 && properties2.hasOwnProperty(prop))
                applyCss(element, prop, value);
            }
          } else {
            applyCss(element, args[1], args[2]);
          }
        };
      }();
      function hasClass(element, name) {
        var list = typeof element == "string" ? element : classList(element);
        return list.indexOf(" " + name + " ") >= 0;
      }
      function addClass(element, name) {
        var oldList = classList(element), newList = oldList + name;
        if (hasClass(oldList, name))
          return;
        element.className = newList.substring(1);
      }
      function removeClass(element, name) {
        var oldList = classList(element), newList;
        if (!hasClass(element, name))
          return;
        newList = oldList.replace(" " + name + " ", " ");
        element.className = newList.substring(1, newList.length - 1);
      }
      function classList(element) {
        return (" " + (element.className || "") + " ").replace(/\s+/gi, " ");
      }
      function removeElement(element) {
        element && element.parentNode && element.parentNode.removeChild(element);
      }
      return NProgress2;
    });
  }
});

// ../alpine/packages/morph/dist/module.cjs.js
var require_module_cjs6 = __commonJS({
  "../alpine/packages/morph/dist/module.cjs.js"(exports, module) {
    var __defProp2 = Object.defineProperty;
    var __getOwnPropDesc2 = Object.getOwnPropertyDescriptor;
    var __getOwnPropNames2 = Object.getOwnPropertyNames;
    var __hasOwnProp2 = Object.prototype.hasOwnProperty;
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    var __copyProps2 = (to, from, except, desc) => {
      if (from && typeof from === "object" || typeof from === "function") {
        for (let key of __getOwnPropNames2(from))
          if (!__hasOwnProp2.call(to, key) && key !== except)
            __defProp2(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc2(from, key)) || desc.enumerable });
      }
      return to;
    };
    var __toCommonJS = (mod) => __copyProps2(__defProp2({}, "__esModule", { value: true }), mod);
    var module_exports = {};
    __export(module_exports, {
      default: () => module_default,
      morph: () => morph3
    });
    module.exports = __toCommonJS(module_exports);
    function morph3(from, toHtml, options) {
      monkeyPatchDomSetAttributeToAllowAtSymbols();
      let fromEl;
      let toEl;
      let key, lookahead, updating, updated, removing, removed, adding, added;
      function assignOptions(options2 = {}) {
        let defaultGetKey = (el) => el.getAttribute("key");
        let noop = () => {
        };
        updating = options2.updating || noop;
        updated = options2.updated || noop;
        removing = options2.removing || noop;
        removed = options2.removed || noop;
        adding = options2.adding || noop;
        added = options2.added || noop;
        key = options2.key || defaultGetKey;
        lookahead = options2.lookahead || false;
      }
      function patch(from2, to) {
        if (differentElementNamesTypesOrKeys(from2, to)) {
          return swapElements(from2, to);
        }
        let updateChildrenOnly = false;
        if (shouldSkip(updating, from2, to, () => updateChildrenOnly = true))
          return;
        if (from2.nodeType === 1 && window.Alpine) {
          window.Alpine.cloneNode(from2, to);
        }
        if (textOrComment(to)) {
          patchNodeValue(from2, to);
          updated(from2, to);
          return;
        }
        if (!updateChildrenOnly) {
          patchAttributes(from2, to);
        }
        updated(from2, to);
        patchChildren(from2, to);
      }
      function differentElementNamesTypesOrKeys(from2, to) {
        return from2.nodeType != to.nodeType || from2.nodeName != to.nodeName || getKey(from2) != getKey(to);
      }
      function swapElements(from2, to) {
        if (shouldSkip(removing, from2))
          return;
        let toCloned = to.cloneNode(true);
        if (shouldSkip(adding, toCloned))
          return;
        from2.replaceWith(toCloned);
        removed(from2);
        added(toCloned);
      }
      function patchNodeValue(from2, to) {
        let value = to.nodeValue;
        if (from2.nodeValue !== value) {
          from2.nodeValue = value;
        }
      }
      function patchAttributes(from2, to) {
        if (from2._x_transitioning)
          return;
        if (from2._x_isShown && !to._x_isShown) {
          return;
        }
        if (!from2._x_isShown && to._x_isShown) {
          return;
        }
        let domAttributes = Array.from(from2.attributes);
        let toAttributes = Array.from(to.attributes);
        for (let i = domAttributes.length - 1; i >= 0; i--) {
          let name = domAttributes[i].name;
          if (!to.hasAttribute(name)) {
            from2.removeAttribute(name);
          }
        }
        for (let i = toAttributes.length - 1; i >= 0; i--) {
          let name = toAttributes[i].name;
          let value = toAttributes[i].value;
          if (from2.getAttribute(name) !== value) {
            from2.setAttribute(name, value);
          }
        }
      }
      function patchChildren(from2, to) {
        let fromKeys = keyToMap(from2.children);
        let fromKeyHoldovers = {};
        let currentTo = getFirstNode(to);
        let currentFrom = getFirstNode(from2);
        while (currentTo) {
          let toKey = getKey(currentTo);
          let fromKey = getKey(currentFrom);
          if (!currentFrom) {
            if (toKey && fromKeyHoldovers[toKey]) {
              let holdover = fromKeyHoldovers[toKey];
              from2.appendChild(holdover);
              currentFrom = holdover;
            } else {
              if (!shouldSkip(adding, currentTo)) {
                let clone = currentTo.cloneNode(true);
                from2.appendChild(clone);
                added(clone);
              }
              currentTo = getNextSibling(to, currentTo);
              continue;
            }
          }
          let isIf = (node) => node && node.nodeType === 8 && node.textContent === " __BLOCK__ ";
          let isEnd = (node) => node && node.nodeType === 8 && node.textContent === " __ENDBLOCK__ ";
          if (isIf(currentTo) && isIf(currentFrom)) {
            let nestedIfCount = 0;
            let fromBlockStart = currentFrom;
            while (currentFrom) {
              let next = getNextSibling(from2, currentFrom);
              if (isIf(next)) {
                nestedIfCount++;
              } else if (isEnd(next) && nestedIfCount > 0) {
                nestedIfCount--;
              } else if (isEnd(next) && nestedIfCount === 0) {
                currentFrom = next;
                break;
              }
              currentFrom = next;
            }
            let fromBlockEnd = currentFrom;
            nestedIfCount = 0;
            let toBlockStart = currentTo;
            while (currentTo) {
              let next = getNextSibling(to, currentTo);
              if (isIf(next)) {
                nestedIfCount++;
              } else if (isEnd(next) && nestedIfCount > 0) {
                nestedIfCount--;
              } else if (isEnd(next) && nestedIfCount === 0) {
                currentTo = next;
                break;
              }
              currentTo = next;
            }
            let toBlockEnd = currentTo;
            let fromBlock = new Block(fromBlockStart, fromBlockEnd);
            let toBlock = new Block(toBlockStart, toBlockEnd);
            patchChildren(fromBlock, toBlock);
            continue;
          }
          if (currentFrom.nodeType === 1 && lookahead && !currentFrom.isEqualNode(currentTo)) {
            let nextToElementSibling = getNextSibling(to, currentTo);
            let found = false;
            while (!found && nextToElementSibling) {
              if (nextToElementSibling.nodeType === 1 && currentFrom.isEqualNode(nextToElementSibling)) {
                found = true;
                currentFrom = addNodeBefore(from2, currentTo, currentFrom);
                fromKey = getKey(currentFrom);
              }
              nextToElementSibling = getNextSibling(to, nextToElementSibling);
            }
          }
          if (toKey !== fromKey) {
            if (!toKey && fromKey) {
              fromKeyHoldovers[fromKey] = currentFrom;
              currentFrom = addNodeBefore(from2, currentTo, currentFrom);
              fromKeyHoldovers[fromKey].remove();
              currentFrom = getNextSibling(from2, currentFrom);
              currentTo = getNextSibling(to, currentTo);
              continue;
            }
            if (toKey && !fromKey) {
              if (fromKeys[toKey]) {
                currentFrom.replaceWith(fromKeys[toKey]);
                currentFrom = fromKeys[toKey];
              }
            }
            if (toKey && fromKey) {
              let fromKeyNode = fromKeys[toKey];
              if (fromKeyNode) {
                fromKeyHoldovers[fromKey] = currentFrom;
                currentFrom.replaceWith(fromKeyNode);
                currentFrom = fromKeyNode;
              } else {
                fromKeyHoldovers[fromKey] = currentFrom;
                currentFrom = addNodeBefore(from2, currentTo, currentFrom);
                fromKeyHoldovers[fromKey].remove();
                currentFrom = getNextSibling(from2, currentFrom);
                currentTo = getNextSibling(to, currentTo);
                continue;
              }
            }
          }
          let currentFromNext = currentFrom && getNextSibling(from2, currentFrom);
          patch(currentFrom, currentTo);
          currentTo = currentTo && getNextSibling(to, currentTo);
          currentFrom = currentFromNext;
        }
        let removals = [];
        while (currentFrom) {
          if (!shouldSkip(removing, currentFrom))
            removals.push(currentFrom);
          currentFrom = getNextSibling(from2, currentFrom);
        }
        while (removals.length) {
          let domForRemoval = removals.shift();
          domForRemoval.remove();
          removed(domForRemoval);
        }
      }
      function getKey(el) {
        return el && el.nodeType === 1 && key(el);
      }
      function keyToMap(els2) {
        let map = {};
        for (let el of els2) {
          let theKey = getKey(el);
          if (theKey) {
            map[theKey] = el;
          }
        }
        return map;
      }
      function addNodeBefore(parent, node, beforeMe) {
        if (!shouldSkip(adding, node)) {
          let clone = node.cloneNode(true);
          parent.insertBefore(clone, beforeMe);
          added(clone);
          return clone;
        }
        return node;
      }
      assignOptions(options);
      fromEl = from;
      toEl = typeof toHtml === "string" ? createElement(toHtml) : toHtml;
      if (window.Alpine && window.Alpine.closestDataStack && !from._x_dataStack) {
        toEl._x_dataStack = window.Alpine.closestDataStack(from);
        toEl._x_dataStack && window.Alpine.cloneNode(from, toEl);
      }
      patch(from, toEl);
      fromEl = void 0;
      toEl = void 0;
      return from;
    }
    morph3.step = () => {
    };
    morph3.log = () => {
    };
    function shouldSkip(hook, ...args) {
      let skip = false;
      hook(...args, () => skip = true);
      return skip;
    }
    var patched = false;
    function createElement(html) {
      const template = document.createElement("template");
      template.innerHTML = html;
      return template.content.firstElementChild;
    }
    function textOrComment(el) {
      return el.nodeType === 3 || el.nodeType === 8;
    }
    var Block = class {
      constructor(start2, end) {
        this.startComment = start2;
        this.endComment = end;
      }
      get children() {
        let children = [];
        let currentNode = this.startComment.nextSibling;
        while (currentNode !== void 0 && currentNode !== this.endComment) {
          children.push(currentNode);
          currentNode = currentNode.nextSibling;
        }
        return children;
      }
      appendChild(child) {
        this.endComment.before(child);
      }
      get firstChild() {
        let first2 = this.startComment.nextSibling;
        if (first2 === this.endComment)
          return;
        return first2;
      }
      nextNode(reference) {
        let next = reference.nextSibling;
        if (next === this.endComment)
          return;
        return next;
      }
      insertBefore(newNode, reference) {
        reference.before(newNode);
        return newNode;
      }
    };
    function getFirstNode(parent) {
      return parent.firstChild;
    }
    function getNextSibling(parent, reference) {
      if (reference._x_teleport) {
        return reference._x_teleport;
      } else if (reference.teleportBack) {
        return reference.teleportBack;
      }
      let next;
      if (parent instanceof Block) {
        next = parent.nextNode(reference);
      } else {
        next = reference.nextSibling;
      }
      return next;
    }
    function monkeyPatchDomSetAttributeToAllowAtSymbols() {
      if (patched)
        return;
      patched = true;
      let original = Element.prototype.setAttribute;
      let hostDiv = document.createElement("div");
      Element.prototype.setAttribute = function newSetAttribute(name, value) {
        if (!name.includes("@")) {
          return original.call(this, name, value);
        }
        hostDiv.innerHTML = `<span ${name}="${value}"></span>`;
        let attr = hostDiv.firstElementChild.getAttributeNode(name);
        hostDiv.firstElementChild.removeAttributeNode(attr);
        this.setAttributeNode(attr);
      };
    }
    function src_default(Alpine20) {
      Alpine20.morph = morph3;
    }
    var module_default = src_default;
  }
});

// ../alpine/packages/mask/dist/module.cjs.js
var require_module_cjs7 = __commonJS({
  "../alpine/packages/mask/dist/module.cjs.js"(exports, module) {
    var __defProp2 = Object.defineProperty;
    var __getOwnPropDesc2 = Object.getOwnPropertyDescriptor;
    var __getOwnPropNames2 = Object.getOwnPropertyNames;
    var __hasOwnProp2 = Object.prototype.hasOwnProperty;
    var __export = (target, all2) => {
      for (var name in all2)
        __defProp2(target, name, { get: all2[name], enumerable: true });
    };
    var __copyProps2 = (to, from, except, desc) => {
      if (from && typeof from === "object" || typeof from === "function") {
        for (let key of __getOwnPropNames2(from))
          if (!__hasOwnProp2.call(to, key) && key !== except)
            __defProp2(to, key, { get: () => from[key], enumerable: !(desc = __getOwnPropDesc2(from, key)) || desc.enumerable });
      }
      return to;
    };
    var __toCommonJS = (mod) => __copyProps2(__defProp2({}, "__esModule", { value: true }), mod);
    var module_exports = {};
    __export(module_exports, {
      default: () => module_default,
      stripDown: () => stripDown
    });
    module.exports = __toCommonJS(module_exports);
    function src_default(Alpine20) {
      Alpine20.directive("mask", (el, { value, expression }, { effect, evaluateLater }) => {
        let templateFn = () => expression;
        let lastInputValue = "";
        queueMicrotask(() => {
          if (["function", "dynamic"].includes(value)) {
            let evaluator = evaluateLater(expression);
            effect(() => {
              templateFn = (input) => {
                let result;
                Alpine20.dontAutoEvaluateFunctions(() => {
                  evaluator((value2) => {
                    result = typeof value2 === "function" ? value2(input) : value2;
                  }, { scope: {
                    "$input": input,
                    "$money": formatMoney.bind({ el })
                  } });
                });
                return result;
              };
              processInputValue(el, false);
            });
          } else {
            processInputValue(el, false);
          }
          if (el._x_model)
            el._x_model.set(el.value);
        });
        el.addEventListener("input", () => processInputValue(el));
        el.addEventListener("blur", () => processInputValue(el, false));
        function processInputValue(el2, shouldRestoreCursor = true) {
          let input = el2.value;
          let template = templateFn(input);
          if (!template || template === "false")
            return false;
          if (lastInputValue.length - el2.value.length === 1) {
            return lastInputValue = el2.value;
          }
          let setInput = () => {
            lastInputValue = el2.value = formatInput(input, template);
          };
          if (shouldRestoreCursor) {
            restoreCursorPosition(el2, template, () => {
              setInput();
            });
          } else {
            setInput();
          }
        }
        function formatInput(input, template) {
          if (input === "")
            return "";
          let strippedDownInput = stripDown(template, input);
          let rebuiltInput = buildUp(template, strippedDownInput);
          return rebuiltInput;
        }
      }).before("model");
    }
    function restoreCursorPosition(el, template, callback) {
      let cursorPosition = el.selectionStart;
      let unformattedValue = el.value;
      callback();
      let beforeLeftOfCursorBeforeFormatting = unformattedValue.slice(0, cursorPosition);
      let newPosition = buildUp(template, stripDown(template, beforeLeftOfCursorBeforeFormatting)).length;
      el.setSelectionRange(newPosition, newPosition);
    }
    function stripDown(template, input) {
      let inputToBeStripped = input;
      let output = "";
      let regexes = {
        "9": /[0-9]/,
        "a": /[a-zA-Z]/,
        "*": /[a-zA-Z0-9]/
      };
      let wildcardTemplate = "";
      for (let i = 0; i < template.length; i++) {
        if (["9", "a", "*"].includes(template[i])) {
          wildcardTemplate += template[i];
          continue;
        }
        for (let j = 0; j < inputToBeStripped.length; j++) {
          if (inputToBeStripped[j] === template[i]) {
            inputToBeStripped = inputToBeStripped.slice(0, j) + inputToBeStripped.slice(j + 1);
            break;
          }
        }
      }
      for (let i = 0; i < wildcardTemplate.length; i++) {
        let found = false;
        for (let j = 0; j < inputToBeStripped.length; j++) {
          if (regexes[wildcardTemplate[i]].test(inputToBeStripped[j])) {
            output += inputToBeStripped[j];
            inputToBeStripped = inputToBeStripped.slice(0, j) + inputToBeStripped.slice(j + 1);
            found = true;
            break;
          }
        }
        if (!found)
          break;
      }
      return output;
    }
    function buildUp(template, input) {
      let clean = Array.from(input);
      let output = "";
      for (let i = 0; i < template.length; i++) {
        if (!["9", "a", "*"].includes(template[i])) {
          output += template[i];
          continue;
        }
        if (clean.length === 0)
          break;
        output += clean.shift();
      }
      return output;
    }
    function formatMoney(input, delimiter = ".", thousands, precision = 2) {
      if (input === "-")
        return "-";
      if (/^\D+$/.test(input))
        return "9";
      thousands = thousands != null ? thousands : delimiter === "," ? "." : ",";
      let addThousands = (input2, thousands2) => {
        let output = "";
        let counter = 0;
        for (let i = input2.length - 1; i >= 0; i--) {
          if (input2[i] === thousands2)
            continue;
          if (counter === 3) {
            output = input2[i] + thousands2 + output;
            counter = 0;
          } else {
            output = input2[i] + output;
          }
          counter++;
        }
        return output;
      };
      let minus = input.startsWith("-") ? "-" : "";
      let strippedInput = input.replaceAll(new RegExp(`[^0-9\\${delimiter}]`, "g"), "");
      let template = Array.from({ length: strippedInput.split(delimiter)[0].length }).fill("9").join("");
      template = `${minus}${addThousands(template, thousands)}`;
      if (precision > 0 && input.includes(delimiter))
        template += `${delimiter}` + "9".repeat(precision);
      queueMicrotask(() => {
        if (this.el.value.endsWith(delimiter))
          return;
        if (this.el.value[this.el.selectionStart - 1] === delimiter) {
          this.el.setSelectionRange(this.el.selectionStart - 1, this.el.selectionStart - 1);
        }
      });
      return template;
    }
    var module_default = src_default;
  }
});

// js/utils.js
var WeakBag = class {
  constructor() {
    this.arrays = /* @__PURE__ */ new WeakMap();
  }
  add(key, value) {
    if (!this.arrays.has(key))
      this.arrays.set(key, []);
    this.arrays.get(key).push(value);
  }
  get(key) {
    return this.arrays.has(key) ? this.arrays.get(key) : [];
  }
  each(key, callback) {
    return this.get(key).forEach(callback);
  }
};
function dispatch(el, name, detail = {}, bubbles = true) {
  el.dispatchEvent(new CustomEvent(name, {
    detail,
    bubbles,
    composed: true,
    cancelable: true
  }));
}
function isObjecty(subject) {
  return typeof subject === "object" && subject !== null;
}
function isObject(subject) {
  return isObjecty(subject) && !isArray(subject);
}
function isArray(subject) {
  return Array.isArray(subject);
}
function isFunction(subject) {
  return typeof subject === "function";
}
function isPrimitive(subject) {
  return typeof subject !== "object" || subject === null;
}
function deepClone(obj) {
  return JSON.parse(JSON.stringify(obj));
}
function dataGet(object, key) {
  if (key === "")
    return object;
  return key.split(".").reduce((carry, i) => {
    if (carry === void 0)
      return void 0;
    return carry[i];
  }, object);
}
function dataSet(object, key, value) {
  let segments = key.split(".");
  if (segments.length === 1) {
    return object[key] = value;
  }
  let firstSegment = segments.shift();
  let restOfSegments = segments.join(".");
  if (object[firstSegment] === void 0) {
    object[firstSegment] = {};
  }
  dataSet(object[firstSegment], restOfSegments, value);
}
function diff(left, right, diffs = {}, path = "") {
  if (left === right)
    return diffs;
  if (typeof left !== typeof right || isObject(left) && isArray(right) || isArray(left) && isObject(right)) {
    diffs[path] = right;
    return diffs;
  }
  if (isPrimitive(left) || isPrimitive(right)) {
    diffs[path] = right;
    return diffs;
  }
  let leftKeys = Object.keys(left);
  Object.entries(right).forEach(([key, value]) => {
    diffs = { ...diffs, ...diff(left[key], right[key], diffs, path === "" ? key : `${path}.${key}`) };
    leftKeys = leftKeys.filter((i) => i !== key);
  });
  leftKeys.forEach((key) => {
    diffs[`${path}.${key}`] = "__rm__";
  });
  return diffs;
}
function extractData(payload) {
  let value = isSynthetic(payload) ? payload[0] : payload;
  let meta = isSynthetic(payload) ? payload[1] : void 0;
  if (isObjecty(value)) {
    Object.entries(value).forEach(([key, iValue]) => {
      value[key] = extractData(iValue);
    });
  }
  return value;
}
function isSynthetic(subject) {
  return Array.isArray(subject) && subject.length === 2 && typeof subject[1] === "object" && Object.keys(subject[1]).includes("s");
}
var csrf;
function getCsrfToken() {
  if (csrf)
    return csrf;
  if (document.querySelector("[data-csrf]")) {
    csrf = document.querySelector("[data-csrf]").getAttribute("data-csrf");
    return csrf;
  }
  if (window.livewireScriptConfig["csrf"] ?? false) {
    csrf = window.livewireScriptConfig["csrf"];
    return csrf;
  }
  throw "Livewire: No CSRF token detected";
}
function contentIsFromDump(content) {
  return !!content.match(/<script>Sfdump\(".+"\)<\/script>/);
}
function splitDumpFromContent(content) {
  let dump2 = content.match(/.*<script>Sfdump\(".+"\)<\/script>/s);
  return [dump2, content.replace(dump2, "")];
}

// js/modal.js
function showHtmlModal(html) {
  let page = document.createElement("html");
  page.innerHTML = html;
  page.querySelectorAll("a").forEach((a) => a.setAttribute("target", "_top"));
  let modal = document.getElementById("livewire-error");
  if (typeof modal != "undefined" && modal != null) {
    modal.innerHTML = "";
  } else {
    modal = document.createElement("div");
    modal.id = "livewire-error";
    modal.style.position = "fixed";
    modal.style.width = "100vw";
    modal.style.height = "100vh";
    modal.style.padding = "50px";
    modal.style.backgroundColor = "rgba(0, 0, 0, .6)";
    modal.style.zIndex = 2e5;
  }
  let iframe = document.createElement("iframe");
  iframe.style.backgroundColor = "#17161A";
  iframe.style.borderRadius = "5px";
  iframe.style.width = "100%";
  iframe.style.height = "100%";
  modal.appendChild(iframe);
  document.body.prepend(modal);
  document.body.style.overflow = "hidden";
  iframe.contentWindow.document.open();
  iframe.contentWindow.document.write(page.outerHTML);
  iframe.contentWindow.document.close();
  modal.addEventListener("click", () => hideHtmlModal(modal));
  modal.setAttribute("tabindex", 0);
  modal.addEventListener("keydown", (e) => {
    if (e.key === "Escape")
      hideHtmlModal(modal);
  });
  modal.focus();
}
function hideHtmlModal(modal) {
  modal.outerHTML = "";
  document.body.style.overflow = "visible";
}

// js/events.js
var listeners = [];
function on(name, callback) {
  if (!listeners[name])
    listeners[name] = [];
  listeners[name].push(callback);
  return () => {
    listeners[name] = listeners[name].filter((i) => i !== callback);
  };
}
function trigger(name, ...params) {
  let callbacks = listeners[name] || [];
  let finishers = [];
  for (let i = 0; i < callbacks.length; i++) {
    let finisher = callbacks[i](...params);
    if (isFunction(finisher))
      finishers.push(finisher);
  }
  return (result) => {
    let latest = result;
    for (let i = 0; i < finishers.length; i++) {
      let iResult = finishers[i](latest);
      if (iResult !== void 0) {
        latest = iResult;
      }
    }
    return latest;
  };
}

// js/request.js
var updateUri = document.querySelector("[data-uri]")?.getAttribute("data-uri") ?? window.livewireScriptConfig["uri"] ?? null;
function triggerSend() {
  bundleMultipleRequestsTogetherIfTheyHappenWithinFiveMsOfEachOther(() => {
    sendRequestToServer();
  });
}
var requestBufferTimeout;
function bundleMultipleRequestsTogetherIfTheyHappenWithinFiveMsOfEachOther(callback) {
  if (requestBufferTimeout)
    return;
  requestBufferTimeout = setTimeout(() => {
    callback();
    requestBufferTimeout = void 0;
  }, 5);
}
async function sendRequestToServer() {
  prepareCommitPayloads();
  await queueNewRequestAttemptsWhile(async () => {
    let [payload, handleSuccess, handleFailure] = compileCommitPayloads();
    let options = {
      method: "POST",
      body: JSON.stringify({
        _token: getCsrfToken(),
        components: payload
      }),
      headers: {
        "Content-type": "application/json",
        "X-Livewire": ""
      }
    };
    let succeedCallbacks = [];
    let failCallbacks = [];
    let respondCallbacks = [];
    let succeed = (fwd) => succeedCallbacks.forEach((i) => i(fwd));
    let fail = (fwd) => failCallbacks.forEach((i) => i(fwd));
    let respond = (fwd) => respondCallbacks.forEach((i) => i(fwd));
    let finishProfile = trigger("request.profile", options);
    trigger("request", {
      url: updateUri,
      options,
      payload: options.body,
      respond: (i) => respondCallbacks.push(i),
      succeed: (i) => succeedCallbacks.push(i),
      fail: (i) => failCallbacks.push(i)
    });
    let response = await fetch(updateUri, options);
    let mutableObject = {
      status: response.status,
      response
    };
    respond(mutableObject);
    response = mutableObject.response;
    let content = await response.text();
    if (!response.ok) {
      finishProfile({ content: "{}", failed: true });
      let preventDefault = false;
      handleFailure();
      fail({
        status: response.status,
        content,
        preventDefault: () => preventDefault = true
      });
      if (preventDefault)
        return;
      if (response.status === 419) {
        handlePageExpiry();
      }
      return showFailureModal(content);
    }
    if (response.redirected) {
      window.location.href = response.url;
    }
    if (contentIsFromDump(content)) {
      [dump, content] = splitDumpFromContent(content);
      showHtmlModal(dump);
      finishProfile({ content: "{}", failed: true });
    } else {
      finishProfile({ content, failed: false });
    }
    let { components: components2 } = JSON.parse(content);
    handleSuccess(components2);
    succeed({ status: response.status, json: JSON.parse(content) });
  });
}
function prepareCommitPayloads() {
  let commits = getCommits();
  commits.forEach((i) => i.prepare());
}
function compileCommitPayloads() {
  let commits = getCommits();
  let commitPayloads = [];
  let successReceivers = [];
  let failureReceivers = [];
  flushCommits((commit) => {
    let [payload, succeed2, fail2] = commit.toRequestPayload();
    commitPayloads.push(payload);
    successReceivers.push(succeed2);
    failureReceivers.push(fail2);
  });
  let succeed = (components2) => successReceivers.forEach((receiver) => receiver(components2.shift()));
  let fail = () => failureReceivers.forEach((receiver) => receiver());
  return [commitPayloads, succeed, fail];
}
function handlePageExpiry() {
  confirm("This page has expired.\nWould you like to refresh the page?") && window.location.reload();
}
function showFailureModal(content) {
  let html = content;
  showHtmlModal(html);
}
var sendingRequest = false;
var afterSendStack = [];
async function waitUntilTheCurrentRequestIsFinished(callback) {
  return new Promise((resolve) => {
    if (sendingRequest) {
      afterSendStack.push(() => resolve(callback()));
    } else {
      resolve(callback());
    }
  });
}
async function queueNewRequestAttemptsWhile(callback) {
  sendingRequest = true;
  await callback();
  sendingRequest = false;
  while (afterSendStack.length > 0)
    afterSendStack.shift()();
}

// js/commit.js
var commitQueue = [];
function getCommits() {
  return commitQueue;
}
function flushCommits(callback) {
  while (commitQueue.length > 0) {
    callback(commitQueue.shift());
  }
}
function findOrCreateCommit(component) {
  let commit = commitQueue.find((i) => {
    return i.component.id === component.id;
  });
  if (!commit) {
    commitQueue.push(commit = new Commit(component));
  }
  return commit;
}
async function requestCommit(component) {
  return await waitUntilTheCurrentRequestIsFinished(() => {
    let commit = findOrCreateCommit(component);
    triggerSend();
    return new Promise((resolve, reject) => {
      commit.addResolver(resolve);
    });
  });
}
async function requestCall(component, method, params) {
  return await waitUntilTheCurrentRequestIsFinished(() => {
    let commit = findOrCreateCommit(component);
    triggerSend();
    return new Promise((resolve, reject) => {
      commit.addCall(method, params, (value) => resolve(value));
    });
  });
}
var Commit = class {
  constructor(component) {
    this.component = component;
    this.calls = [];
    this.receivers = [];
    this.resolvers = [];
  }
  addResolver(resolver) {
    this.resolvers.push(resolver);
  }
  addCall(method, params, receiver) {
    this.calls.push({
      path: "",
      method,
      params,
      handleReturn(value) {
        receiver(value);
      }
    });
  }
  prepare() {
    trigger("commit.prepare", { component: this.component });
  }
  toRequestPayload() {
    let propertiesDiff = diff(this.component.canonical, this.component.ephemeral);
    let payload = {
      snapshot: this.component.snapshotEncoded,
      updates: propertiesDiff,
      calls: this.calls.map((i) => ({
        path: i.path,
        method: i.method,
        params: i.params
      }))
    };
    let succeedCallbacks = [];
    let failCallbacks = [];
    let respondCallbacks = [];
    let succeed = (fwd) => succeedCallbacks.forEach((i) => i(fwd));
    let fail = () => failCallbacks.forEach((i) => i());
    let respond = () => respondCallbacks.forEach((i) => i());
    let finishTarget = trigger("commit", {
      component: this.component,
      commit: payload,
      succeed: (callback) => {
        succeedCallbacks.push(callback);
      },
      fail: (callback) => {
        failCallbacks.push(callback);
      },
      respond: (callback) => {
        respondCallbacks.push(callback);
      }
    });
    let handleResponse = (response) => {
      let { snapshot, effects } = response;
      respond();
      this.component.mergeNewSnapshot(snapshot, effects, propertiesDiff);
      processEffects(this.component, this.component.effects);
      if (effects["returns"]) {
        let returns = effects["returns"];
        let returnHandlerStack = this.calls.map(({ handleReturn }) => handleReturn);
        returnHandlerStack.forEach((handleReturn, index) => {
          handleReturn(returns[index]);
        });
      }
      let parsedSnapshot = JSON.parse(snapshot);
      finishTarget({ snapshot: parsedSnapshot, effects });
      this.resolvers.forEach((i) => i());
      succeed(response);
    };
    let handleFailure = () => {
      respond();
      fail();
    };
    return [payload, handleResponse, handleFailure];
  }
};
function processEffects(target, effects) {
  trigger("effects", target, effects);
}

// js/features/supportEntangle.js
var import_alpinejs = __toESM(require_module_cjs());
function generateEntangleFunction(component, cleanup2) {
  if (!cleanup2)
    cleanup2 = () => {
    };
  return (name, live) => {
    let isLive = live;
    let livewireProperty = name;
    let livewireComponent = component.$wire;
    let livewirePropertyValue = livewireComponent.get(livewireProperty);
    let interceptor = import_alpinejs.default.interceptor((initialValue, getter, setter, path, key) => {
      if (typeof livewirePropertyValue === "undefined") {
        console.error(`Livewire Entangle Error: Livewire property ['${livewireProperty}'] cannot be found on component: ['${component.name}']`);
        return;
      }
      queueMicrotask(() => {
        let release = import_alpinejs.default.entangle({
          get() {
            return livewireComponent.get(name);
          },
          set(value) {
            livewireComponent.set(name, value, isLive);
          }
        }, {
          get() {
            return getter();
          },
          set(value) {
            setter(value);
          }
        });
        cleanup2(() => release());
      });
      return livewireComponent.get(name);
    }, (obj) => {
      Object.defineProperty(obj, "live", {
        get() {
          isLive = true;
          return obj;
        }
      });
    });
    return interceptor(livewirePropertyValue);
  };
}

// js/$wire.js
var import_alpinejs2 = __toESM(require_module_cjs());

// js/features/supportFileUploads.js
var uploadManagers = /* @__PURE__ */ new WeakMap();
function getUploadManager(component) {
  if (!uploadManagers.has(component)) {
    let manager = new UploadManager(component);
    uploadManagers.set(component, manager);
    manager.registerListeners();
  }
  return uploadManagers.get(component);
}
function handleFileUpload(el, property, component, cleanup2) {
  let manager = getUploadManager(component);
  let start2 = () => el.dispatchEvent(new CustomEvent("livewire-upload-start", { bubbles: true, detail: { id: component.id, property } }));
  let finish = () => el.dispatchEvent(new CustomEvent("livewire-upload-finish", { bubbles: true, detail: { id: component.id, property } }));
  let error2 = () => el.dispatchEvent(new CustomEvent("livewire-upload-error", { bubbles: true, detail: { id: component.id, property } }));
  let progress = (progressEvent) => {
    var percentCompleted = Math.round(progressEvent.loaded * 100 / progressEvent.total);
    el.dispatchEvent(new CustomEvent("livewire-upload-progress", {
      bubbles: true,
      detail: { progress: percentCompleted }
    }));
  };
  let eventHandler = (e) => {
    if (e.target.files.length === 0)
      return;
    start2();
    if (e.target.multiple) {
      manager.uploadMultiple(property, e.target.files, finish, error2, progress);
    } else {
      manager.upload(property, e.target.files[0], finish, error2, progress);
    }
  };
  el.addEventListener("change", eventHandler);
  let clearFileInputValue = () => {
    el.value = null;
  };
  el.addEventListener("click", clearFileInputValue);
  cleanup2(() => {
    el.removeEventListener("change", eventHandler);
    el.removeEventListener("click", clearFileInputValue);
  });
}
var UploadManager = class {
  constructor(component) {
    this.component = component;
    this.uploadBag = new MessageBag();
    this.removeBag = new MessageBag();
  }
  registerListeners() {
    this.component.$wire.$on("upload:generatedSignedUrl", ({ name, url }) => {
      setUploadLoading(this.component, name);
      this.handleSignedUrl(name, url);
    });
    this.component.$wire.$on("upload:generatedSignedUrlForS3", ({ name, payload }) => {
      setUploadLoading(this.component, name);
      this.handleS3PreSignedUrl(name, payload);
    });
    this.component.$wire.$on("upload:finished", ({ name, tmpFilenames }) => this.markUploadFinished(name, tmpFilenames));
    this.component.$wire.$on("upload:errored", ({ name }) => this.markUploadErrored(name));
    this.component.$wire.$on("upload:removed", ({ name, tmpFilename }) => this.removeBag.shift(name).finishCallback(tmpFilename));
  }
  upload(name, file, finishCallback, errorCallback, progressCallback) {
    this.setUpload(name, {
      files: [file],
      multiple: false,
      finishCallback,
      errorCallback,
      progressCallback
    });
  }
  uploadMultiple(name, files, finishCallback, errorCallback, progressCallback) {
    this.setUpload(name, {
      files: Array.from(files),
      multiple: true,
      finishCallback,
      errorCallback,
      progressCallback
    });
  }
  removeUpload(name, tmpFilename, finishCallback) {
    this.removeBag.push(name, {
      tmpFilename,
      finishCallback
    });
    this.component.$wire.call("_removeUpload", name, tmpFilename);
  }
  setUpload(name, uploadObject) {
    this.uploadBag.add(name, uploadObject);
    if (this.uploadBag.get(name).length === 1) {
      this.startUpload(name, uploadObject);
    }
  }
  handleSignedUrl(name, url) {
    let formData = new FormData();
    Array.from(this.uploadBag.first(name).files).forEach((file) => formData.append("files[]", file, file.name));
    let headers = {
      "Accept": "application/json"
    };
    let csrfToken = getCsrfToken();
    if (csrfToken)
      headers["X-CSRF-TOKEN"] = csrfToken;
    this.makeRequest(name, formData, "post", url, headers, (response) => {
      return response.paths;
    });
  }
  handleS3PreSignedUrl(name, payload) {
    let formData = this.uploadBag.first(name).files[0];
    let headers = payload.headers;
    if ("Host" in headers)
      delete headers.Host;
    let url = payload.url;
    this.makeRequest(name, formData, "put", url, headers, (response) => {
      return [payload.path];
    });
  }
  makeRequest(name, formData, method, url, headers, retrievePaths) {
    let request = new XMLHttpRequest();
    request.open(method, url);
    Object.entries(headers).forEach(([key, value]) => {
      request.setRequestHeader(key, value);
    });
    request.upload.addEventListener("progress", (e) => {
      e.detail = {};
      e.detail.progress = Math.round(e.loaded * 100 / e.total);
      this.uploadBag.first(name).progressCallback(e);
    });
    request.addEventListener("load", () => {
      if ((request.status + "")[0] === "2") {
        let paths = retrievePaths(request.response && JSON.parse(request.response));
        this.component.$wire.call("_finishUpload", name, paths, this.uploadBag.first(name).multiple);
        return;
      }
      let errors = null;
      if (request.status === 422) {
        errors = request.response;
      }
      this.component.$wire.call("_uploadErrored", name, errors, this.uploadBag.first(name).multiple);
    });
    request.send(formData);
  }
  startUpload(name, uploadObject) {
    let fileInfos = uploadObject.files.map((file) => {
      return { name: file.name, size: file.size, type: file.type };
    });
    this.component.$wire.call("_startUpload", name, fileInfos, uploadObject.multiple);
    setUploadLoading(this.component, name);
  }
  markUploadFinished(name, tmpFilenames) {
    unsetUploadLoading(this.component);
    let uploadObject = this.uploadBag.shift(name);
    uploadObject.finishCallback(uploadObject.multiple ? tmpFilenames : tmpFilenames[0]);
    if (this.uploadBag.get(name).length > 0)
      this.startUpload(name, this.uploadBag.last(name));
  }
  markUploadErrored(name) {
    unsetUploadLoading(this.component);
    this.uploadBag.shift(name).errorCallback();
    if (this.uploadBag.get(name).length > 0)
      this.startUpload(name, this.uploadBag.last(name));
  }
};
var MessageBag = class {
  constructor() {
    this.bag = {};
  }
  add(name, thing) {
    if (!this.bag[name]) {
      this.bag[name] = [];
    }
    this.bag[name].push(thing);
  }
  push(name, thing) {
    this.add(name, thing);
  }
  first(name) {
    if (!this.bag[name])
      return null;
    return this.bag[name][0];
  }
  last(name) {
    return this.bag[name].slice(-1)[0];
  }
  get(name) {
    return this.bag[name];
  }
  shift(name) {
    return this.bag[name].shift();
  }
  call(name, ...params) {
    (this.listeners[name] || []).forEach((callback) => {
      callback(...params);
    });
  }
  has(name) {
    return Object.keys(this.listeners).includes(name);
  }
};
function setUploadLoading() {
}
function unsetUploadLoading() {
}
function upload(component, name, file, finishCallback = () => {
}, errorCallback = () => {
}, progressCallback = () => {
}) {
  let uploadManager = getUploadManager(component);
  uploadManager.upload(name, file, finishCallback, errorCallback, progressCallback);
}
function uploadMultiple(component, name, files, finishCallback = () => {
}, errorCallback = () => {
}, progressCallback = () => {
}) {
  let uploadManager = getUploadManager(component);
  uploadManager.uploadMultiple(name, files, finishCallback, errorCallback, progressCallback);
}
function removeUpload(component, name, tmpFilename, finishCallback = () => {
}, errorCallback = () => {
}) {
  let uploadManager = getUploadManager(component);
  uploadManager.removeUpload(name, tmpFilename, finishCallback, errorCallback);
}

// js/$wire.js
var properties = {};
var fallback;
function wireProperty(name, callback, component = null) {
  properties[name] = callback;
}
function wireFallback(callback) {
  fallback = callback;
}
var aliases = {
  "on": "$on",
  "get": "$get",
  "set": "$set",
  "call": "$call",
  "commit": "$commit",
  "watch": "$watch",
  "entangle": "$entangle",
  "dispatch": "$dispatch",
  "dispatchTo": "$dispatchTo",
  "dispatchSelf": "$dispatchSelf",
  "upload": "$upload",
  "uploadMultiple": "$uploadMultiple",
  "removeUpload": "$removeUpload"
};
function generateWireObject(component, state) {
  return new Proxy({}, {
    get(target, property) {
      if (property === "__instance")
        return component;
      if (property in aliases) {
        return getProperty(component, aliases[property]);
      } else if (property in properties) {
        return getProperty(component, property);
      } else if (property in state) {
        return state[property];
      } else if (!["then"].includes(property)) {
        return getFallback(component)(property);
      }
    },
    set(target, property, value) {
      if (property in state) {
        state[property] = value;
      }
      return true;
    }
  });
}
function getProperty(component, name) {
  return properties[name](component);
}
function getFallback(component) {
  return fallback(component);
}
import_alpinejs2.default.magic("wire", (el, { cleanup: cleanup2 }) => {
  let component;
  return new Proxy({}, {
    get(target, property) {
      if (!component)
        component = closestComponent(el);
      if (property === "entangle") {
        return generateEntangleFunction(component, cleanup2);
      }
      return component.$wire[property];
    },
    set(target, property, value) {
      if (!component)
        component = closestComponent(el);
      component.$wire[property] = value;
      return true;
    }
  });
});
wireProperty("__instance", (component) => component);
wireProperty("$get", (component) => (property, reactive = true) => dataGet(reactive ? component.reactive : component.ephemeral, property));
wireProperty("$set", (component) => async (property, value, live = true) => {
  dataSet(component.reactive, property, value);
  return live ? await requestCommit(component) : Promise.resolve();
});
wireProperty("$call", (component) => async (method, ...params) => {
  return await component.$wire[method](...params);
});
wireProperty("$entangle", (component) => (name, live = false) => {
  return generateEntangleFunction(component)(name, live);
});
wireProperty("$toggle", (component) => (name) => {
  return component.$wire.set(name, !component.$wire.get(name));
});
wireProperty("$watch", (component) => (path, callback) => {
  let firstTime = true;
  let oldValue = void 0;
  import_alpinejs2.default.effect(() => {
    let value = dataGet(component.reactive, path);
    JSON.stringify(value);
    if (!firstTime) {
      queueMicrotask(() => {
        callback(value, oldValue);
        oldValue = value;
      });
    } else {
      oldValue = value;
    }
    firstTime = false;
  });
});
wireProperty("$refresh", (component) => component.$wire.$commit);
wireProperty("$commit", (component) => async () => await requestCommit(component));
wireProperty("$on", (component) => (...params) => listen(component, ...params));
wireProperty("$dispatch", (component) => (...params) => dispatch2(component, ...params));
wireProperty("$dispatchSelf", (component) => (...params) => dispatchSelf(component, ...params));
wireProperty("$dispatchTo", (component) => (...params) => dispatchTo(component, ...params));
wireProperty("$upload", (component) => (...params) => upload(component, ...params));
wireProperty("$uploadMultiple", (component) => (...params) => uploadMultiple(component, ...params));
wireProperty("$removeUpload", (component) => (...params) => removeUpload(component, ...params));
var parentMemo;
wireProperty("$parent", (component) => {
  if (parentMemo)
    return parentMemo.$wire;
  let parent = closestComponent(component.el.parentElement);
  parentMemo = parent;
  return parent.$wire;
});
var overriddenMethods = /* @__PURE__ */ new WeakMap();
function overrideMethod(component, method, callback) {
  if (!overriddenMethods.has(component)) {
    overriddenMethods.set(component, {});
  }
  let obj = overriddenMethods.get(component);
  obj[method] = callback;
  overriddenMethods.set(component, obj);
}
wireFallback((component) => (property) => async (...params) => {
  if (params.length === 1 && params[0] instanceof Event) {
    params = [];
  }
  if (overriddenMethods.has(component)) {
    let overrides = overriddenMethods.get(component);
    if (typeof overrides[property] === "function") {
      return overrides[property](params);
    }
  }
  return await requestCall(component, property, params);
});

// js/component.js
var Component = class {
  constructor(el) {
    if (el.__livewire)
      throw "Component already initialized";
    el.__livewire = this;
    this.el = el;
    this.id = el.getAttribute("wire:id");
    this.__livewireId = this.id;
    this.snapshotEncoded = el.getAttribute("wire:snapshot");
    this.snapshot = JSON.parse(this.snapshotEncoded);
    if (!this.snapshot) {
      throw `Snapshot missing on Livewire component with id: ` + this.id;
    }
    this.name = this.snapshot.memo.name;
    this.effects = JSON.parse(el.getAttribute("wire:effects"));
    this.originalEffects = deepClone(this.effects);
    this.canonical = extractData(deepClone(this.snapshot.data));
    this.ephemeral = extractData(deepClone(this.snapshot.data));
    this.reactive = Alpine.reactive(this.ephemeral);
    this.$wire = generateWireObject(this, this.reactive);
    this.cleanups = [];
    processEffects(this, this.effects);
  }
  mergeNewSnapshot(snapshotEncoded, effects, updates = {}) {
    let snapshot = JSON.parse(snapshotEncoded);
    let oldCanonical = deepClone(this.canonical);
    let updatedOldCanonical = this.applyUpdates(oldCanonical, updates);
    let newCanonical = extractData(deepClone(snapshot.data));
    let dirty = diff(updatedOldCanonical, newCanonical);
    this.snapshotEncoded = snapshotEncoded;
    this.snapshot = snapshot;
    this.effects = effects;
    this.canonical = extractData(deepClone(snapshot.data));
    let newData = extractData(deepClone(snapshot.data));
    Object.entries(dirty).forEach(([key, value]) => {
      let rootKey = key.split(".")[0];
      this.reactive[rootKey] = newData[rootKey];
    });
    return dirty;
  }
  applyUpdates(object, updates) {
    for (let key in updates) {
      dataSet(object, key, updates[key]);
    }
    return object;
  }
  replayUpdate(snapshot, html) {
    let effects = { ...this.effects, html };
    this.mergeNewSnapshot(JSON.stringify(snapshot), effects);
    processEffects(this, { html });
  }
  get children() {
    let meta = this.snapshot.memo;
    let childIds = Object.values(meta.children).map((i) => i[1]);
    return childIds.map((id) => findComponent(id));
  }
  inscribeSnapshotAndEffectsOnElement() {
    let el = this.el;
    el.setAttribute("wire:snapshot", this.snapshotEncoded);
    let effects = this.originalEffects.listeners ? { listeners: this.originalEffects.listeners } : {};
    el.setAttribute("wire:effects", JSON.stringify(effects));
  }
  addCleanup(cleanup2) {
    this.cleanups.push(cleanup2);
  }
  cleanup() {
    while (this.cleanups.length > 0) {
      this.cleanups.pop()();
    }
  }
};

// js/store.js
var components = {};
function initComponent(el) {
  let component = new Component(el);
  if (components[component.id])
    throw "Component already registered";
  trigger("component.init", { component });
  components[component.id] = component;
  return component;
}
function destroyComponent(id) {
  let component = components[id];
  if (!component)
    return;
  component.cleanup();
  delete components[id];
}
function findComponent(id) {
  let component = components[id];
  if (!component)
    throw "Component not found: ".id;
  return component;
}
function closestComponent(el, strict = true) {
  let closestRoot = Alpine.findClosest(el, (i) => i.__livewire);
  if (!closestRoot) {
    if (strict)
      throw "Could not find Livewire component in DOM tree";
    return;
  }
  return closestRoot.__livewire;
}
function componentsByName(name) {
  return Object.values(components).filter((component) => {
    return name == component.name;
  });
}
function getByName(name) {
  return componentsByName(name).map((i) => i.$wire);
}
function find(id) {
  let component = components[id];
  return component && component.$wire;
}
function first() {
  return Object.values(components)[0].$wire;
}
function all() {
  return Object.values(components);
}

// js/features/supportEvents.js
var import_alpinejs3 = __toESM(require_module_cjs());
on("effects", (component, effects) => {
  registerListeners(component, effects.listeners || []);
  dispatchEvents(component, effects.dispatches || []);
});
function registerListeners(component, listeners2) {
  listeners2.forEach((name) => {
    let handler = (e) => {
      if (e.__livewire)
        e.__livewire.receivedBy.push(component);
      component.$wire.call("__dispatch", name, e.detail || {});
    };
    window.addEventListener(name, handler);
    component.addCleanup(() => window.removeEventListener(name, handler));
    component.el.addEventListener(name, (e) => {
      if (e.__livewire && e.bubbles)
        return;
      if (e.__livewire)
        e.__livewire.receivedBy.push(component.id);
      component.$wire.call("__dispatch", name, e.detail || {});
    });
  });
}
function dispatchEvents(component, dispatches) {
  dispatches.forEach(({ name, params = {}, self: self2 = false, to }) => {
    if (self2)
      dispatchSelf(component, name, params);
    else if (to)
      dispatchTo(component, to, name, params);
    else
      dispatch2(component, name, params);
  });
}
function dispatchEvent(target, name, params, bubbles = true) {
  let e = new CustomEvent(name, { bubbles, detail: params });
  e.__livewire = { name, params, receivedBy: [] };
  target.dispatchEvent(e);
}
function dispatch2(component, name, params) {
  dispatchEvent(component.el, name, params);
}
function dispatchGlobal(name, params) {
  dispatchEvent(window, name, params);
}
function dispatchSelf(component, name, params) {
  dispatchEvent(component.el, name, params, false);
}
function dispatchTo(component, componentName, name, params) {
  let targets = componentsByName(componentName);
  targets.forEach((target) => {
    dispatchEvent(target.el, name, params, false);
  });
}
function listen(component, name, callback) {
  component.el.addEventListener(name, (e) => {
    callback(e.detail);
  });
}
function on2(eventName, callback) {
  window.addEventListener(eventName, (e) => {
    if (!e.__livewire)
      return;
    callback(e.detail);
  });
}

// js/directives.js
var import_alpinejs4 = __toESM(require_module_cjs());
function matchesForLivewireDirective(attributeName) {
  return attributeName.match(new RegExp("wire:"));
}
function extractDirective(el, name) {
  let [value, ...modifiers] = name.replace(new RegExp("wire:"), "").split(".");
  return new Directive(value, modifiers, name, el);
}
function directive(name, callback) {
  on("directive.init", ({ el, component, directive: directive2, cleanup: cleanup2 }) => {
    if (directive2.value === name) {
      callback({
        el,
        directive: directive2,
        component,
        cleanup: cleanup2
      });
    }
  });
}
function getDirectives(el) {
  return new DirectiveManager(el);
}
var DirectiveManager = class {
  constructor(el) {
    this.el = el;
    this.directives = this.extractTypeModifiersAndValue();
  }
  all() {
    return this.directives;
  }
  has(value) {
    return this.directives.map((directive2) => directive2.value).includes(value);
  }
  missing(value) {
    return !this.has(value);
  }
  get(value) {
    return this.directives.find((directive2) => directive2.value === value);
  }
  extractTypeModifiersAndValue() {
    return Array.from(this.el.getAttributeNames().filter((name) => matchesForLivewireDirective(name)).map((name) => {
      const [value, ...modifiers] = name.replace(new RegExp("wire:"), "").split(".");
      return new Directive(value, modifiers, name, this.el);
    }));
  }
};
var Directive = class {
  constructor(value, modifiers, rawName, el) {
    this.rawName = this.raw = rawName;
    this.el = el;
    this.eventContext;
    this.value = value;
    this.modifiers = modifiers;
    this.expression = this.el.getAttribute(this.rawName);
  }
  get method() {
    const { method } = this.parseOutMethodAndParams(this.expression);
    return method;
  }
  get params() {
    const { params } = this.parseOutMethodAndParams(this.expression);
    return params;
  }
  parseOutMethodAndParams(rawMethod) {
    let method = rawMethod;
    let params = [];
    const methodAndParamString = method.match(/(.*?)\((.*)\)/s);
    if (methodAndParamString) {
      method = methodAndParamString[1];
      let func = new Function("$event", `return (function () {
                for (var l=arguments.length, p=new Array(l), k=0; k<l; k++) {
                    p[k] = arguments[k];
                }
                return [].concat(p);
            })(${methodAndParamString[2]})`);
      params = func(this.eventContext);
    }
    return { method, params };
  }
};

// js/lifecycle.js
var import_collapse = __toESM(require_module_cjs2());
var import_focus = __toESM(require_module_cjs3());
var import_persist2 = __toESM(require_module_cjs4());
var import_intersect = __toESM(require_module_cjs5());

// js/plugins/navigate/history.js
function updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks() {
  let url = new URL(window.location.href, document.baseURI);
  replaceUrl(url, document.documentElement.outerHTML);
}
function whenTheBackOrForwardButtonIsClicked(callback) {
  window.addEventListener("popstate", (e) => {
    let state = e.state || {};
    let alpine = state.alpine || {};
    if (!alpine._html)
      return;
    let html = fromSessionStorage(alpine._html);
    callback(html);
  });
}
function updateUrlAndStoreLatestHtmlForFutureBackButtons(html, destination) {
  pushUrl(destination, html);
}
function pushUrl(url, html) {
  updateUrl("pushState", url, html);
}
function replaceUrl(url, html) {
  updateUrl("replaceState", url, html);
}
function updateUrl(method, url, html) {
  let key = new Date().getTime();
  tryToStoreInSession(key, html);
  let state = history.state || {};
  if (!state.alpine)
    state.alpine = {};
  state.alpine._html = key;
  history[method](state, document.title, url);
}
function fromSessionStorage(timestamp) {
  let state = JSON.parse(sessionStorage.getItem("alpine:" + timestamp));
  return state;
}
function tryToStoreInSession(timestamp, value) {
  try {
    sessionStorage.setItem("alpine:" + timestamp, JSON.stringify(value));
  } catch (error2) {
    if (![22, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14].includes(error2.code))
      return;
    let oldestTimestamp = Object.keys(sessionStorage).map((key) => Number(key.replace("alpine:", ""))).sort().shift();
    if (!oldestTimestamp)
      return;
    sessionStorage.removeItem("alpine:" + oldestTimestamp);
    tryToStoreInSession(timestamp, value);
  }
}

// js/plugins/navigate/prefetch.js
var prefetches = {};
function prefetchHtml(destination, callback) {
  let path = destination.pathname;
  if (prefetches[path])
    return;
  prefetches[path] = { finished: false, html: null, whenFinished: () => {
  } };
  fetch(path).then((i) => i.text()).then((html) => {
    callback(html);
  });
}
function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination) {
  let state = prefetches[destination.pathname];
  state.html = html;
  state.finished = true;
  state.whenFinished();
}
function getPretchedHtmlOr(destination, receive, ifNoPrefetchExists) {
  let uri = destination.pathname + destination.search;
  if (!prefetches[uri])
    return ifNoPrefetchExists();
  if (prefetches[uri].finished) {
    let html = prefetches[uri].html;
    delete prefetches[uri];
    return receive(html);
  } else {
    prefetches[uri].whenFinished = () => {
      let html = prefetches[uri].html;
      delete prefetches[uri];
      receive(html);
    };
  }
}

// js/plugins/navigate/links.js
function whenThisLinkIsPressed(el, callback) {
  el.addEventListener("click", (e) => e.preventDefault());
  el.addEventListener("mousedown", (e) => {
    if (e.button !== 0)
      return;
    e.preventDefault();
    callback((whenReleased) => {
      let handler = (e2) => {
        e2.preventDefault();
        whenReleased();
        el.removeEventListener("mouseup", handler);
      };
      el.addEventListener("mouseup", handler);
    });
  });
}
function whenThisLinkIsHoveredFor(el, ms = 60, callback) {
  el.addEventListener("mouseenter", (e) => {
    let timeout = setTimeout(() => {
      callback(e);
    }, ms);
    let handler = () => {
      clearTimeout(timeout);
      el.removeEventListener("mouseleave", handler);
    };
    el.addEventListener("mouseleave", handler);
  });
}
function extractDestinationFromLink(linkEl) {
  return createUrlObjectFromString(linkEl.getAttribute("href"));
}
function createUrlObjectFromString(urlString) {
  return new URL(urlString, document.baseURI);
}

// js/plugins/navigate/scroll.js
function storeScrollInformationInHtmlBeforeNavigatingAway() {
  document.body.setAttribute("data-scroll-x", document.body.scrollLeft);
  document.body.setAttribute("data-scroll-y", document.body.scrollTop);
  document.querySelectorAll(["[x-navigate\\:scroll]", "[wire\\:scroll]"]).forEach((el) => {
    el.setAttribute("data-scroll-x", el.scrollLeft);
    el.setAttribute("data-scroll-y", el.scrollTop);
  });
}
function restoreScrollPosition() {
  let scroll = (el) => {
    el.scrollTo(Number(el.getAttribute("data-scroll-x")), Number(el.getAttribute("data-scroll-y")));
    el.removeAttribute("data-scroll-x");
    el.removeAttribute("data-scroll-y");
  };
  queueMicrotask(() => {
    scroll(document.body);
    document.querySelectorAll(["[x-navigate\\:scroll]", "[wire\\:scroll]"]).forEach(scroll);
  });
}

// js/plugins/navigate/persist.js
var import_alpinejs5 = __toESM(require_module_cjs());
var els = {};
function storePersistantElementsForLater() {
  els = {};
  document.querySelectorAll("[x-persist]").forEach((i) => {
    els[i.getAttribute("x-persist")] = i;
    import_alpinejs5.default.mutateDom(() => {
      i.remove();
    });
  });
}
function putPersistantElementsBack() {
  document.querySelectorAll("[x-persist]").forEach((i) => {
    let old = els[i.getAttribute("x-persist")];
    if (!old)
      return;
    old._x_wasPersisted = true;
    import_alpinejs5.default.mutateDom(() => {
      i.replaceWith(old);
    });
  });
}

// js/plugins/navigate/bar.js
var import_nprogress = __toESM(require_nprogress());
import_nprogress.default.configure({ minimum: 0.1 });
import_nprogress.default.configure({ trickleSpeed: 200 });
injectStyles();
var inProgress = false;
function showAndStartProgressBar() {
  inProgress = true;
  setTimeout(() => {
    if (!inProgress)
      return;
    import_nprogress.default.start();
  }, 150);
}
function finishAndHideProgressBar() {
  inProgress = false;
  import_nprogress.default.done();
  import_nprogress.default.remove();
}
function injectStyles() {
  let style = document.createElement("style");
  style.innerHTML = `/* Make clicks pass-through */
    #nprogress {
      pointer-events: none;
    }

    #nprogress .bar {
    //   background: #FC70A9;
      background: #29d;

      position: fixed;
      z-index: 1031;
      top: 0;
      left: 0;

      width: 100%;
      height: 2px;
    }

    /* Fancy blur effect */
    #nprogress .peg {
      display: block;
      position: absolute;
      right: 0px;
      width: 100px;
      height: 100%;
      box-shadow: 0 0 10px #29d, 0 0 5px #29d;
      opacity: 1.0;

      -webkit-transform: rotate(3deg) translate(0px, -4px);
          -ms-transform: rotate(3deg) translate(0px, -4px);
              transform: rotate(3deg) translate(0px, -4px);
    }

    /* Remove these to get rid of the spinner */
    #nprogress .spinner {
      display: block;
      position: fixed;
      z-index: 1031;
      top: 15px;
      right: 15px;
    }

    #nprogress .spinner-icon {
      width: 18px;
      height: 18px;
      box-sizing: border-box;

      border: solid 2px transparent;
      border-top-color: #29d;
      border-left-color: #29d;
      border-radius: 50%;

      -webkit-animation: nprogress-spinner 400ms linear infinite;
              animation: nprogress-spinner 400ms linear infinite;
    }

    .nprogress-custom-parent {
      overflow: hidden;
      position: relative;
    }

    .nprogress-custom-parent #nprogress .spinner,
    .nprogress-custom-parent #nprogress .bar {
      position: absolute;
    }

    @-webkit-keyframes nprogress-spinner {
      0%   { -webkit-transform: rotate(0deg); }
      100% { -webkit-transform: rotate(360deg); }
    }
    @keyframes nprogress-spinner {
      0%   { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    `;
  document.head.appendChild(style);
}

// js/plugins/navigate/page.js
var oldBodyScriptTagHashes = [];
var attributesExemptFromScriptTagHashing = [
  "data-csrf"
];
function swapCurrentPageWithNewHtml(html, andThen) {
  let newDocument = new DOMParser().parseFromString(html, "text/html");
  let newBody = document.adoptNode(newDocument.body);
  let newHead = document.adoptNode(newDocument.head);
  oldBodyScriptTagHashes = oldBodyScriptTagHashes.concat(Array.from(document.body.querySelectorAll("script")).map((i) => {
    return simpleHash(ignoreAttributes(i.outerHTML, attributesExemptFromScriptTagHashing));
  }));
  mergeNewHead(newHead);
  prepNewBodyScriptTagsToRun(newBody, oldBodyScriptTagHashes);
  transitionOut(document.body);
  let oldBody = document.body;
  document.body.replaceWith(newBody);
  Alpine.destroyTree(oldBody);
  transitionIn(newBody);
  andThen();
}
function transitionOut(body) {
  return;
  body.style.transition = "all .5s ease";
  body.style.opacity = "0";
}
function transitionIn(body) {
  return;
  body.style.opacity = "0";
  body.style.transition = "all .5s ease";
  requestAnimationFrame(() => {
    body.style.opacity = "1";
  });
}
function prepNewBodyScriptTagsToRun(newBody, oldBodyScriptTagHashes2) {
  newBody.querySelectorAll("script").forEach((i) => {
    if (i.hasAttribute("data-navigate-once")) {
      let hash = simpleHash(ignoreAttributes(i.outerHTML, attributesExemptFromScriptTagHashing));
      if (oldBodyScriptTagHashes2.includes(hash))
        return;
    }
    i.replaceWith(cloneScriptTag(i));
  });
}
function mergeNewHead(newHead) {
  let children = Array.from(document.head.children);
  let headChildrenHtmlLookup = children.map((i) => i.outerHTML);
  let garbageCollector = document.createDocumentFragment();
  for (let child of Array.from(newHead.children)) {
    if (isAsset(child)) {
      if (!headChildrenHtmlLookup.includes(child.outerHTML)) {
        if (isTracked(child)) {
          if (ifTheQueryStringChangedSinceLastRequest(child, children)) {
            setTimeout(() => window.location.reload());
          }
        }
        if (isScript(child)) {
          document.head.appendChild(cloneScriptTag(child));
        } else {
          document.head.appendChild(child);
        }
      } else {
        garbageCollector.appendChild(child);
      }
    }
  }
  for (let child of Array.from(document.head.children)) {
    if (!isAsset(child))
      child.remove();
  }
  for (let child of Array.from(newHead.children)) {
    document.head.appendChild(child);
  }
}
function cloneScriptTag(el) {
  let script = document.createElement("script");
  script.textContent = el.textContent;
  script.async = el.async;
  for (let attr of el.attributes) {
    script.setAttribute(attr.name, attr.value);
  }
  return script;
}
function isTracked(el) {
  return el.hasAttribute("data-navigate-track");
}
function ifTheQueryStringChangedSinceLastRequest(el, currentHeadChildren) {
  let [uri, queryString] = extractUriAndQueryString(el);
  return currentHeadChildren.some((child) => {
    if (!isTracked(child))
      return false;
    let [currentUri, currentQueryString] = extractUriAndQueryString(child);
    if (currentUri === uri && queryString !== currentQueryString)
      return true;
  });
}
function extractUriAndQueryString(el) {
  let url = isScript(el) ? el.src : el.href;
  return url.split("?");
}
function isAsset(el) {
  return el.tagName.toLowerCase() === "link" && el.getAttribute("rel").toLowerCase() === "stylesheet" || el.tagName.toLowerCase() === "style" || el.tagName.toLowerCase() === "script";
}
function isScript(el) {
  return el.tagName.toLowerCase() === "script";
}
function simpleHash(str) {
  return str.split("").reduce((a, b) => {
    a = (a << 5) - a + b.charCodeAt(0);
    return a & a;
  }, 0);
}
function ignoreAttributes(subject, attributesToRemove) {
  let result = subject;
  attributesToRemove.forEach((attr) => {
    const regex = new RegExp(`${attr}="[^"]*"|${attr}='[^']*'`, "g");
    result = result.replace(regex, "");
  });
  return result.trim();
}

// js/plugins/navigate/fetch.js
function fetchHtml(destination, callback) {
  let uri = destination.pathname + destination.search;
  fetch(uri).then((i) => i.text()).then((html) => {
    callback(html);
  });
}

// js/plugins/navigate/index.js
var import_alpinejs6 = __toESM(require_module_cjs());
var enablePersist = true;
var showProgressBar = true;
var restoreScroll = true;
var autofocus = false;
function navigate_default(Alpine20) {
  Alpine20.navigate = (url) => {
    navigateTo(createUrlObjectFromString(url));
  };
  Alpine20.navigate.disableProgressBar = () => {
    showProgressBar = false;
  };
  Alpine20.addInitSelector(() => `[${Alpine20.prefixed("navigate")}]`);
  Alpine20.directive("navigate", (el, { value, expression, modifiers }, { evaluateLater, cleanup: cleanup2 }) => {
    let shouldPrefetchOnHover = modifiers.includes("hover");
    shouldPrefetchOnHover && whenThisLinkIsHoveredFor(el, 60, () => {
      let destination = extractDestinationFromLink(el);
      prefetchHtml(destination, (html) => {
        storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination);
      });
    });
    whenThisLinkIsPressed(el, (whenItIsReleased) => {
      let destination = extractDestinationFromLink(el);
      prefetchHtml(destination, (html) => {
        storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination);
      });
      whenItIsReleased(() => {
        navigateTo(destination);
      });
    });
  });
  function navigateTo(destination) {
    showProgressBar && showAndStartProgressBar();
    fetchHtmlOrUsePrefetchedHtml(destination, (html) => {
      fireEventForOtherLibariesToHookInto("alpine:navigating");
      restoreScroll && storeScrollInformationInHtmlBeforeNavigatingAway();
      showProgressBar && finishAndHideProgressBar();
      updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks();
      preventAlpineFromPickingUpDomChanges(Alpine20, (andAfterAllThis) => {
        enablePersist && storePersistantElementsForLater();
        swapCurrentPageWithNewHtml(html, () => {
          enablePersist && putPersistantElementsBack();
          restoreScroll && restoreScrollPosition();
          fireEventForOtherLibariesToHookInto("alpine:navigated");
          updateUrlAndStoreLatestHtmlForFutureBackButtons(html, destination);
          andAfterAllThis(() => {
            autofocus && autofocusElementsWithTheAutofocusAttribute();
            nowInitializeAlpineOnTheNewPage(Alpine20);
          });
        });
      });
    });
  }
  whenTheBackOrForwardButtonIsClicked((html) => {
    storeScrollInformationInHtmlBeforeNavigatingAway();
    preventAlpineFromPickingUpDomChanges(Alpine20, (andAfterAllThis) => {
      enablePersist && storePersistantElementsForLater();
      swapCurrentPageWithNewHtml(html, (andThen) => {
        enablePersist && putPersistantElementsBack();
        restoreScroll && restoreScrollPosition();
        fireEventForOtherLibariesToHookInto("alpine:navigated");
        andAfterAllThis(() => {
          autofocus && autofocusElementsWithTheAutofocusAttribute();
          nowInitializeAlpineOnTheNewPage(Alpine20);
        });
      });
    });
  });
  setTimeout(() => {
    fireEventForOtherLibariesToHookInto("alpine:navigated", true);
  });
}
function fetchHtmlOrUsePrefetchedHtml(fromDestination, callback) {
  getPretchedHtmlOr(fromDestination, callback, () => {
    fetchHtml(fromDestination, callback);
  });
}
function preventAlpineFromPickingUpDomChanges(Alpine20, callback) {
  Alpine20.stopObservingMutations();
  callback((afterAllThis) => {
    Alpine20.startObservingMutations();
    setTimeout(() => {
      afterAllThis();
    });
  });
}
function fireEventForOtherLibariesToHookInto(eventName, init = false) {
  document.dispatchEvent(new CustomEvent(eventName, { bubbles: true, detail: { init } }));
}
function nowInitializeAlpineOnTheNewPage(Alpine20) {
  Alpine20.initTree(document.body, void 0, (el, skip) => {
    if (el._x_wasPersisted)
      skip();
  });
}
function autofocusElementsWithTheAutofocusAttribute() {
  document.querySelector("[autofocus]") && document.querySelector("[autofocus]").focus();
}

// js/plugins/history/index.js
function history2(Alpine20) {
  Alpine20.magic("queryString", (el, { interceptor }) => {
    let alias;
    let alwaysShow = false;
    let usePush = false;
    return interceptor((initialSeedValue, getter, setter, path, key) => {
      let queryKey = alias || path;
      let { initial, replace: replace2, push: push2, pop } = track(queryKey, initialSeedValue, alwaysShow);
      setter(initial);
      if (!usePush) {
        console.log(getter());
        Alpine20.effect(() => replace2(getter()));
      } else {
        Alpine20.effect(() => push2(getter()));
        pop(async (newValue) => {
          setter(newValue);
          let tillTheEndOfTheMicrotaskQueue = () => Promise.resolve();
          await tillTheEndOfTheMicrotaskQueue();
        });
      }
      return initial;
    }, (func) => {
      func.alwaysShow = () => {
        alwaysShow = true;
        return func;
      };
      func.usePush = () => {
        usePush = true;
        return func;
      };
      func.as = (key) => {
        alias = key;
        return func;
      };
    });
  });
  Alpine20.history = { track };
}
function track(name, initialSeedValue, alwaysShow = false) {
  let { has, get, set, remove } = queryStringUtils();
  let url = new URL(window.location.href);
  let isInitiallyPresentInUrl = has(url, name);
  let initialValue = isInitiallyPresentInUrl ? get(url, name) : initialSeedValue;
  let initialValueMemo = JSON.stringify(initialValue);
  let hasReturnedToInitialValue = (newValue) => JSON.stringify(newValue) === initialValueMemo;
  if (alwaysShow)
    url = set(url, name, initialValue);
  replace(url, name, { value: initialValue });
  let lock = false;
  let update = (strategy, newValue) => {
    if (lock)
      return;
    let url2 = new URL(window.location.href);
    if (!alwaysShow && !isInitiallyPresentInUrl && hasReturnedToInitialValue(newValue)) {
      url2 = remove(url2, name);
    } else {
      url2 = set(url2, name, newValue);
    }
    strategy(url2, name, { value: newValue });
  };
  return {
    initial: initialValue,
    replace(newValue) {
      update(replace, newValue);
    },
    push(newValue) {
      update(push, newValue);
    },
    pop(receiver) {
      window.addEventListener("popstate", (e) => {
        if (!e.state || !e.state.alpine)
          return;
        Object.entries(e.state.alpine).forEach(([iName, { value: newValue }]) => {
          if (iName !== name)
            return;
          lock = true;
          let result = receiver(newValue);
          if (result instanceof Promise) {
            result.finally(() => lock = false);
          } else {
            lock = false;
          }
        });
      });
    }
  };
}
function replace(url, key, object) {
  let state = window.history.state || {};
  if (!state.alpine)
    state.alpine = {};
  state.alpine[key] = unwrap(object);
  window.history.replaceState(state, "", url.toString());
}
function push(url, key, object) {
  let state = { alpine: { ...window.history.state.alpine, ...{ [key]: unwrap(object) } } };
  window.history.pushState(state, "", url.toString());
}
function unwrap(object) {
  return JSON.parse(JSON.stringify(object));
}
function queryStringUtils() {
  return {
    has(url, key) {
      let search = url.search;
      if (!search)
        return false;
      let data = fromQueryString(search);
      return Object.keys(data).includes(key);
    },
    get(url, key) {
      let search = url.search;
      if (!search)
        return false;
      let data = fromQueryString(search);
      return data[key];
    },
    set(url, key, value) {
      let data = fromQueryString(url.search);
      data[key] = value;
      url.search = toQueryString(data);
      return url;
    },
    remove(url, key) {
      let data = fromQueryString(url.search);
      delete data[key];
      url.search = toQueryString(data);
      return url;
    }
  };
}
function toQueryString(data) {
  let isObjecty2 = (subject) => typeof subject === "object" && subject !== null;
  let buildQueryStringEntries = (data2, entries2 = {}, baseKey = "") => {
    Object.entries(data2).forEach(([iKey, iValue]) => {
      let key = baseKey === "" ? iKey : `${baseKey}[${iKey}]`;
      if (!isObjecty2(iValue)) {
        entries2[key] = encodeURIComponent(iValue).replaceAll("%20", "+");
      } else {
        entries2 = { ...entries2, ...buildQueryStringEntries(iValue, entries2, key) };
      }
    });
    return entries2;
  };
  let entries = buildQueryStringEntries(data);
  return Object.entries(entries).map(([key, value]) => `${key}=${value}`).join("&");
}
function fromQueryString(search) {
  search = search.replace("?", "");
  if (search === "")
    return {};
  let insertDotNotatedValueIntoData = (key, value, data2) => {
    let [first2, second, ...rest] = key.split(".");
    if (!second)
      return data2[key] = value;
    if (data2[first2] === void 0) {
      data2[first2] = isNaN(second) ? {} : [];
    }
    insertDotNotatedValueIntoData([second, ...rest].join("."), value, data2[first2]);
  };
  let entries = search.split("&").map((i) => i.split("="));
  let data = {};
  entries.forEach(([key, value]) => {
    if (!value)
      return;
    value = decodeURIComponent(value.replaceAll("+", "%20"));
    if (!key.includes("[")) {
      data[key] = value;
    } else {
      let dotNotatedKey = key.replaceAll("[", ".").replaceAll("]", "");
      insertDotNotatedValueIntoData(dotNotatedKey, value, data);
    }
  });
  return data;
}

// js/lifecycle.js
var import_morph = __toESM(require_module_cjs6());
var import_mask = __toESM(require_module_cjs7());
var import_alpinejs7 = __toESM(require_module_cjs());
function start() {
  dispatch(document, "livewire:init");
  dispatch(document, "livewire:initializing");
  import_alpinejs7.default.plugin(import_morph.default);
  import_alpinejs7.default.plugin(history2);
  import_alpinejs7.default.plugin(import_intersect.default);
  import_alpinejs7.default.plugin(import_collapse.default);
  import_alpinejs7.default.plugin(import_focus.default);
  import_alpinejs7.default.plugin(import_persist2.default);
  import_alpinejs7.default.plugin(navigate_default);
  import_alpinejs7.default.plugin(import_mask.default);
  import_alpinejs7.default.addRootSelector(() => "[wire\\:id]");
  import_alpinejs7.default.onAttributesAdded((el, attributes) => {
    let component = closestComponent(el, false);
    if (!component)
      return;
    attributes.forEach((attribute) => {
      if (!matchesForLivewireDirective(attribute.name))
        return;
      let directive2 = extractDirective(el, attribute.name);
      trigger("directive.init", { el, component, directive: directive2, cleanup: (callback) => {
        import_alpinejs7.default.onAttributeRemoved(el, directive2.raw, callback);
      } });
    });
  });
  import_alpinejs7.default.interceptInit(import_alpinejs7.default.skipDuringClone((el) => {
    if (el.hasAttribute("wire:id")) {
      let component2 = initComponent(el);
      import_alpinejs7.default.onAttributeRemoved(el, "wire:id", () => {
        destroyComponent(component2.id);
      });
    }
    let component = closestComponent(el, false);
    if (component) {
      trigger("element.init", { el, component });
      let directives = Array.from(el.getAttributeNames()).filter((name) => matchesForLivewireDirective(name)).map((name) => extractDirective(el, name));
      directives.forEach((directive2) => {
        trigger("directive.init", { el, component, directive: directive2, cleanup: (callback) => {
          import_alpinejs7.default.onAttributeRemoved(el, directive2.raw, callback);
        } });
      });
    }
  }));
  import_alpinejs7.default.start();
  setTimeout(() => window.Livewire.initialRenderIsFinished = true);
  dispatch(document, "livewire:initialized");
}
function stop() {
}
function rescan() {
}

// js/index.js
var import_alpinejs18 = __toESM(require_module_cjs());

// js/features/supportWireModelingNestedComponents.js
on("commit.prepare", ({ component }) => {
  component.children.forEach((child) => {
    let childMeta = child.snapshot.memo;
    let bindings = childMeta.bindings;
    if (bindings)
      child.$wire.$commit();
  });
});

// js/features/supportDisablingFormsDuringRequest.js
var import_alpinejs8 = __toESM(require_module_cjs());
var cleanupStackByComponentId = {};
on("element.init", ({ el, component }) => {
  let directives = getDirectives(el);
  if (directives.missing("submit"))
    return;
  el.addEventListener("submit", () => {
    cleanupStackByComponentId[component.id] = [];
    import_alpinejs8.default.walk(component.el, (node, skip) => {
      if (!el.contains(node))
        return;
      if (node.hasAttribute("wire:ignore"))
        return skip();
      if (node.tagName.toLowerCase() === "button" && node.type === "submit" || node.tagName.toLowerCase() === "select" || node.tagName.toLowerCase() === "input" && (node.type === "checkbox" || node.type === "radio")) {
        if (!node.disabled)
          cleanupStackByComponentId[component.id].push(() => node.disabled = false);
        node.disabled = true;
      } else if (node.tagName.toLowerCase() === "input" || node.tagName.toLowerCase() === "textarea") {
        if (!node.readOnly)
          cleanupStackByComponentId[component.id].push(() => node.readOnly = false);
        node.readOnly = true;
      }
    });
  });
});
on("commit", ({ component, respond }) => {
  respond(() => {
    cleanup(component);
  });
});
function cleanup(component) {
  if (!cleanupStackByComponentId[component.id])
    return;
  while (cleanupStackByComponentId[component.id].length > 0) {
    cleanupStackByComponentId[component.id].shift()();
  }
}

// js/features/supportFileDownloads.js
on("commit", ({ component, succeed }) => {
  succeed(({ effects }) => {
    let download = effects.download;
    if (!download)
      return;
    let urlObject = window.webkitURL || window.URL;
    let url = urlObject.createObjectURL(base64toBlob(download.content, download.contentType));
    let invisibleLink = document.createElement("a");
    invisibleLink.style.display = "none";
    invisibleLink.href = url;
    invisibleLink.download = download.name;
    document.body.appendChild(invisibleLink);
    invisibleLink.click();
    setTimeout(function() {
      urlObject.revokeObjectURL(url);
    }, 0);
  });
});
function base64toBlob(b64Data, contentType = "", sliceSize = 512) {
  const byteCharacters = atob(b64Data);
  const byteArrays = [];
  if (contentType === null)
    contentType = "";
  for (let offset = 0; offset < byteCharacters.length; offset += sliceSize) {
    let slice = byteCharacters.slice(offset, offset + sliceSize);
    let byteNumbers = new Array(slice.length);
    for (let i = 0; i < slice.length; i++) {
      byteNumbers[i] = slice.charCodeAt(i);
    }
    let byteArray = new Uint8Array(byteNumbers);
    byteArrays.push(byteArray);
  }
  return new Blob(byteArrays, { type: contentType });
}

// js/features/supportJsEvaluation.js
var import_alpinejs9 = __toESM(require_module_cjs());
on("effects", (component, effects) => {
  let js = effects.js;
  let xjs = effects.xjs;
  if (js) {
    Object.entries(js).forEach(([method, body]) => {
      overrideMethod(component, method, () => {
        import_alpinejs9.default.evaluate(component.el, body);
      });
    });
  }
  if (xjs) {
    xjs.forEach((expression) => {
      import_alpinejs9.default.evaluate(component.el, expression);
    });
  }
});

// js/features/supportQueryString.js
var import_alpinejs10 = __toESM(require_module_cjs());
on("component.init", ({ component }) => {
  let effects = component.effects;
  let queryString = effects["url"];
  if (!queryString)
    return;
  Object.entries(queryString).forEach(([key, value]) => {
    let { name, as, use, alwaysShow } = normalizeQueryStringEntry(key, value);
    if (!as)
      as = name;
    let initialValue = dataGet(component.ephemeral, name);
    let { initial, replace: replace2, push: push2, pop } = track(as, initialValue, alwaysShow);
    if (use === "replace") {
      import_alpinejs10.default.effect(() => {
        replace2(dataGet(component.reactive, name));
      });
    } else if (use === "push") {
      on("commit", ({ component: component2, succeed }) => {
        let beforeValue = dataGet(component2.canonical, name);
        succeed(() => {
          let afterValue = dataGet(component2.canonical, name);
          if (JSON.stringify(beforeValue) === JSON.stringify(afterValue))
            return;
          push2(afterValue);
        });
      });
      pop(async (newValue) => {
        await component.$wire.set(name, newValue);
        document.querySelectorAll("input").forEach((el) => {
          el._x_forceModelUpdate && el._x_forceModelUpdate(el._x_model.get());
        });
      });
    }
  });
});
function normalizeQueryStringEntry(key, value) {
  let defaults = { use: "replace", alwaysShow: false };
  if (typeof value === "string") {
    return { ...defaults, name: value, as: value };
  } else {
    let fullerDefaults = { ...defaults, name: key, as: key };
    return { ...fullerDefaults, ...value };
  }
}

// js/features/supportLaravelEcho.js
on("request", ({ options }) => {
  if (window.Echo) {
    options.headers["X-Socket-ID"] = window.Echo.socketId();
  }
});
on("effects", (component, effects) => {
  let listeners2 = effects.listeners || [];
  listeners2.forEach((event) => {
    if (event.startsWith("echo")) {
      if (typeof window.Echo === "undefined") {
        console.warn("Laravel Echo cannot be found");
        return;
      }
      let event_parts = event.split(/(echo:|echo-)|:|,/);
      if (event_parts[1] == "echo:") {
        event_parts.splice(2, 0, "channel", void 0);
      }
      if (event_parts[2] == "notification") {
        event_parts.push(void 0, void 0);
      }
      let [
        s1,
        signature,
        channel_type,
        s2,
        channel,
        s3,
        event_name
      ] = event_parts;
      if (["channel", "private", "encryptedPrivate"].includes(channel_type)) {
        window.Echo[channel_type](channel).listen(event_name, (e) => {
          dispatchSelf(component, event, [e]);
        });
      } else if (channel_type == "presence") {
        if (["here", "joining", "leaving"].includes(event_name)) {
          window.Echo.join(channel)[event_name]((e) => {
            dispatchSelf(component, event, [e]);
          });
        } else {
          window.Echo.join(channel).listen(event_name, (e) => {
            dispatchSelf(component, event, [e]);
          });
        }
      } else if (channel_type == "notification") {
        window.Echo.private(channel).notification((notification) => {
          dispatchSelf(component, event, [notification]);
        });
      } else {
        console.warn("Echo channel type not yet supported");
      }
    }
  });
});

// js/features/supportNavigate.js
var isNavigating = false;
shouldHideProgressBar() && Alpine.navigate.disableProgressBar();
document.addEventListener("alpine:navigated", (e) => {
  if (e.detail && e.detail.init)
    return;
  isNavigating = true;
  document.dispatchEvent(new CustomEvent("livewire:navigated", { bubbles: true }));
});
document.addEventListener("alpine:navigating", (e) => {
  document.dispatchEvent(new CustomEvent("livewire:navigating", { bubbles: true }));
});
function shouldRedirectUsingNavigateOr(effects, url, or) {
  let forceNavigate = effects.redirectUsingNavigate;
  if (forceNavigate || isNavigating) {
    Alpine.navigate(url);
  } else {
    or();
  }
}
function shouldHideProgressBar() {
  if (!!document.querySelector("[data-no-progress-bar]"))
    return true;
  if (window.livewireScriptConfig && window.livewireScriptConfig.progressBar === false)
    return true;
  return false;
}

// js/features/supportRedirects.js
on("effects", (component, effects) => {
  if (!effects["redirect"])
    return;
  let url = effects["redirect"];
  shouldRedirectUsingNavigateOr(effects, url, () => {
    window.location.href = url;
  });
});

// js/morph.js
var import_alpinejs11 = __toESM(require_module_cjs());
function morph2(component, el, html) {
  let wrapperTag = el.parentElement ? el.parentElement.tagName.toLowerCase() : "div";
  let wrapper = document.createElement(wrapperTag);
  wrapper.innerHTML = html;
  let parentComponent;
  try {
    parentComponent = closestComponent(el.parentElement);
  } catch (e) {
  }
  parentComponent && (wrapper.__livewire = parentComponent);
  let to = wrapper.firstElementChild;
  to.__livewire = component;
  trigger("morph", { el, toEl: to, component });
  import_alpinejs11.default.morph(el, to, {
    updating: (el2, toEl, childrenOnly, skip) => {
      if (isntElement(el2))
        return;
      trigger("morph.updating", { el: el2, toEl, component, skip, childrenOnly });
      if (el2.__livewire_ignore === true)
        return skip();
      if (el2.__livewire_ignore_self === true)
        childrenOnly();
      if (isComponentRootEl(el2) && el2.getAttribute("wire:id") !== component.id)
        return skip();
      if (isComponentRootEl(el2))
        toEl.__livewire = component;
    },
    updated: (el2, toEl) => {
      if (isntElement(el2))
        return;
      trigger("morph.updated", { el: el2, component });
    },
    removing: (el2, skip) => {
      if (isntElement(el2))
        return;
      trigger("morph.removing", { el: el2, component, skip });
    },
    removed: (el2) => {
      if (isntElement(el2))
        return;
      trigger("morph.removed", { el: el2, component });
    },
    adding: (el2) => {
      trigger("morph.adding", { el: el2, component });
    },
    added: (el2) => {
      if (isntElement(el2))
        return;
      trigger("morph.added", el2);
      const closestComponentId = closestComponent(el2).id;
      if (closestComponentId === component.id) {
      } else if (isComponentRootEl(el2)) {
        let data;
        if (message.fingerprint && closestComponentId == message.fingerprint.id) {
          data = {
            fingerprint: message.fingerprint,
            serverMemo: message.response.serverMemo,
            effects: message.response.effects
          };
        }
        el2.skipAddingChildren = true;
      }
    },
    key: (el2) => {
      if (isntElement(el2))
        return;
      return el2.hasAttribute(`wire:key`) ? el2.getAttribute(`wire:key`) : el2.hasAttribute(`wire:id`) ? el2.getAttribute(`wire:id`) : el2.id;
    },
    lookahead: false
  });
}
function isntElement(el) {
  return typeof el.hasAttribute !== "function";
}
function isComponentRootEl(el) {
  return el.hasAttribute("wire:id");
}

// js/features/supportMorphDom.js
on("effects", (component, effects) => {
  let html = effects.html;
  if (!html)
    return;
  queueMicrotask(() => {
    morph2(component, component.el, html);
  });
});

// js/features/supportProps.js
on("commit.prepare", ({ component }) => {
  getChildrenRecursively(component, (child) => {
    let childMeta = child.snapshot.memo;
    let props = childMeta.props;
    if (props)
      child.$wire.$commit();
  });
});
function getChildrenRecursively(component, callback) {
  component.children.forEach((child) => {
    callback(child);
    getChildrenRecursively(child, callback);
  });
}

// js/directives/wire-transition.js
var import_alpinejs12 = __toESM(require_module_cjs());
on("morph.added", (el) => {
  el.__addedByMorph = true;
});
directive("transition", ({ el, directive: directive2, component, cleanup: cleanup2 }) => {
  let visibility = import_alpinejs12.default.reactive({ state: false });
  import_alpinejs12.default.bind(el, {
    [directive2.rawName.replace("wire:", "x-")]: "",
    "x-show"() {
      return visibility.state;
    }
  });
  el.__addedByMorph && setTimeout(() => visibility.state = true);
  let cleanups = [];
  cleanups.push(on("morph.removing", ({ el: el2, skip }) => {
    skip();
    el2.addEventListener("transitionend", () => {
      el2.remove();
    });
    visibility.state = false;
    cleanups.push(on("morph", ({ component: morphComponent }) => {
      if (morphComponent !== component)
        return;
      el2.remove();
      cleanups.forEach((i) => i());
    }));
  }));
  cleanup2(() => cleanups.forEach((i) => i()));
});

// js/debounce.js
var callbacksByComponent = new WeakBag();
function callAndClearComponentDebounces(component, callback) {
  callbacksByComponent.each(component, (callbackRegister) => {
    callbackRegister.callback();
    callbackRegister.callback = () => {
    };
  });
  callback();
}

// js/directives/wire-wildcard.js
var import_alpinejs13 = __toESM(require_module_cjs());
on("directive.init", ({ el, directive: directive2, cleanup: cleanup2, component }) => {
  if (["snapshot", "effects", "model", "init", "loading", "poll", "ignore", "id", "data", "key", "target", "dirty"].includes(directive2.value))
    return;
  let attribute = directive2.rawName.replace("wire:", "x-on:");
  if (directive2.value === "submit" && !directive2.modifiers.includes("prevent")) {
    attribute = attribute + ".prevent";
  }
  let cleanupBinding = import_alpinejs13.default.bind(el, {
    [attribute](e) {
      callAndClearComponentDebounces(component, () => {
        import_alpinejs13.default.evaluate(el, "$wire." + directive2.expression, { scope: { $event: e } });
      });
    }
  });
  cleanup2(cleanupBinding);
});

// js/directives/wire-navigate.js
var import_alpinejs14 = __toESM(require_module_cjs());
import_alpinejs14.default.addInitSelector(() => `[wire\\:navigate]`);
import_alpinejs14.default.addInitSelector(() => `[wire\\:navigate\\.hover]`);
import_alpinejs14.default.interceptInit(import_alpinejs14.default.skipDuringClone((el) => {
  if (el.hasAttribute("wire:navigate")) {
    import_alpinejs14.default.bind(el, { ["x-navigate"]: true });
  } else if (el.hasAttribute("wire:navigate.hover")) {
    import_alpinejs14.default.bind(el, { ["x-navigate.hover"]: true });
  }
}));
document.addEventListener("alpine:navigating", () => {
  Livewire.all().forEach((component) => {
    component.inscribeSnapshotAndEffectsOnElement();
  });
});

// js/directives/shared.js
function toggleBooleanStateDirective(el, directive2, isTruthy) {
  isTruthy = directive2.modifiers.includes("remove") ? !isTruthy : isTruthy;
  if (directive2.modifiers.includes("class")) {
    let classes = directive2.expression.split(" ");
    if (isTruthy) {
      el.classList.add(...classes);
    } else {
      el.classList.remove(...classes);
    }
  } else if (directive2.modifiers.includes("attr")) {
    if (isTruthy) {
      el.setAttribute(directive2.expression, true);
    } else {
      el.removeAttribute(directive2.expression);
    }
  } else {
    let cache = window.getComputedStyle(el, null).getPropertyValue("display");
    let display = ["inline", "block", "table", "flex", "grid", "inline-flex"].filter((i) => directive2.modifiers.includes(i))[0] || "inline-block";
    display = directive2.modifiers.includes("remove") ? cache : display;
    el.style.display = isTruthy ? display : "none";
  }
}

// js/directives/wire-offline.js
var offlineHandlers = /* @__PURE__ */ new Set();
var onlineHandlers = /* @__PURE__ */ new Set();
window.addEventListener("offline", () => offlineHandlers.forEach((i) => i()));
window.addEventListener("online", () => onlineHandlers.forEach((i) => i()));
directive("offline", ({ el, directive: directive2, cleanup: cleanup2 }) => {
  let setOffline = () => toggleBooleanStateDirective(el, directive2, true);
  let setOnline = () => toggleBooleanStateDirective(el, directive2, false);
  offlineHandlers.add(setOffline);
  onlineHandlers.add(setOnline);
  cleanup2(() => {
    offlineHandlers.delete(setOffline);
    onlineHandlers.delete(setOnline);
  });
});

// js/directives/wire-loading.js
directive("loading", ({ el, directive: directive2, component }) => {
  let targets = getTargets(el);
  let [delay, abortDelay] = applyDelay(directive2);
  whenTargetsArePartOfRequest(component, targets, [
    () => delay(() => toggleBooleanStateDirective(el, directive2, true)),
    () => abortDelay(() => toggleBooleanStateDirective(el, directive2, false))
  ]);
  whenTargetsArePartOfFileUpload(component, targets, [
    () => delay(() => toggleBooleanStateDirective(el, directive2, true)),
    () => abortDelay(() => toggleBooleanStateDirective(el, directive2, false))
  ]);
});
function applyDelay(directive2) {
  if (!directive2.modifiers.includes("delay"))
    return [(i) => i(), (i) => i()];
  let duration = 200;
  let delayModifiers = {
    "shortest": 50,
    "shorter": 100,
    "short": 150,
    "long": 300,
    "longer": 500,
    "longest": 1e3
  };
  Object.keys(delayModifiers).some((key) => {
    if (directive2.modifiers.includes(key)) {
      duration = delayModifiers[key];
      return true;
    }
  });
  let timeout;
  let started = false;
  return [
    (callback) => {
      timeout = setTimeout(() => {
        callback();
        started = true;
      }, duration);
    },
    (callback) => {
      if (started) {
        callback();
      } else {
        clearTimeout(timeout);
      }
    }
  ];
}
function whenTargetsArePartOfRequest(component, targets, [startLoading, endLoading]) {
  on("commit", ({ component: iComponent, commit: payload, respond }) => {
    if (iComponent !== component)
      return;
    if (targets.length > 0 && !containsTargets(payload, targets))
      return;
    startLoading();
    respond(() => {
      endLoading();
    });
  });
}
function whenTargetsArePartOfFileUpload(component, targets, [startLoading, endLoading]) {
  let eventMismatch = (e) => {
    let { id, property } = e.detail;
    if (id !== component.id)
      return true;
    if (targets.length > 0 && !targets.map((i) => i.target).includes(property))
      return true;
    return false;
  };
  window.addEventListener("livewire-upload-start", (e) => {
    if (eventMismatch(e))
      return;
    startLoading();
  });
  window.addEventListener("livewire-upload-finish", (e) => {
    if (eventMismatch(e))
      return;
    endLoading();
  });
  window.addEventListener("livewire-upload-error", (e) => {
    if (eventMismatch(e))
      return;
    endLoading();
  });
}
function containsTargets(payload, targets) {
  let { updates, calls } = payload;
  return targets.some(({ target, params }) => {
    if (params) {
      return calls.some(({ method, params: methodParams }) => {
        return target === method && params === quickHash(JSON.stringify(methodParams));
      });
    }
    let hasMatchingUpdate = Object.keys(updates).some((property) => {
      return property.startsWith(target);
    });
    if (hasMatchingUpdate)
      return true;
    if (calls.map((i) => i.method).includes(target))
      return true;
  });
}
function getTargets(el) {
  let directives = getDirectives(el);
  let targets = [];
  if (directives.has("target")) {
    let directive2 = directives.get("target");
    let raw = directive2.expression;
    if (raw.includes("(") && raw.includes(")")) {
      targets.push({ target: directive2.method, params: quickHash(JSON.stringify(directive2.params)) });
    } else if (raw.includes(",")) {
      raw.split(",").map((i) => i.trim()).forEach((target) => {
        targets.push({ target });
      });
    } else {
      targets.push({ target: raw });
    }
  } else {
    let nonActionOrModelLivewireDirectives = ["init", "dirty", "offline", "target", "loading", "poll", "ignore", "key", "id"];
    directives.all().filter((i) => !nonActionOrModelLivewireDirectives.includes(i.value)).map((i) => i.expression.split("(")[0]).forEach((target) => targets.push({ target }));
  }
  return targets;
}
function quickHash(subject) {
  return btoa(encodeURIComponent(subject));
}

// js/directives/wire-stream.js
directive("stream", ({ el, directive: directive2, component, cleanup: cleanup2 }) => {
  let { expression, modifiers } = directive2;
  let off = on("stream", ({ name, content, replace: replace2 }) => {
    if (name !== expression)
      return;
    if (modifiers.includes("replace") || replace2) {
      el.innerHTML = content;
    } else {
      el.innerHTML = el.innerHTML + content;
    }
  });
  cleanup2(off);
});
on("request", ({ respond }) => {
  respond((mutableObject) => {
    let response = mutableObject.response;
    if (!response.headers.has("X-Livewire-Stream"))
      return;
    mutableObject.response = {
      ok: true,
      redirected: false,
      status: 200,
      async text() {
        let finalResponse = await interceptStreamAndReturnFinalResponse(response, (streamed) => {
          trigger("stream", streamed);
        });
        if (contentIsFromDump(finalResponse)) {
          this.ok = false;
        }
        return finalResponse;
      }
    };
  });
});
async function interceptStreamAndReturnFinalResponse(response, callback) {
  let reader = response.body.getReader();
  let finalResponse = "";
  while (true) {
    let { done, value: chunk } = await reader.read();
    let decoder = new TextDecoder();
    let output = decoder.decode(chunk);
    let [streams, remaining] = extractStreamObjects(output);
    streams.forEach((stream) => {
      callback(stream);
    });
    finalResponse = finalResponse + remaining;
    if (done)
      return finalResponse;
  }
}
function extractStreamObjects(raw) {
  let regex = /({"stream":true.*?"endStream":true})/g;
  let matches = raw.match(regex);
  let parsed = [];
  if (matches) {
    for (let i = 0; i < matches.length; i++) {
      parsed.push(JSON.parse(matches[i]).body);
    }
  }
  let remaining = raw.replace(regex, "");
  return [parsed, remaining];
}

// js/directives/wire-ignore.js
directive("ignore", ({ el, directive: directive2 }) => {
  if (directive2.modifiers.includes("self")) {
    el.__livewire_ignore_self = true;
  } else {
    el.__livewire_ignore = true;
  }
});

// js/directives/wire-dirty.js
var refreshDirtyStatesByComponent = new WeakBag();
on("commit", ({ component, respond }) => {
  respond(() => {
    setTimeout(() => {
      refreshDirtyStatesByComponent.each(component, (i) => i(false));
    });
  });
});
directive("dirty", ({ el, directive: directive2, component }) => {
  let targets = dirtyTargets(el);
  let dirty = Alpine.reactive({ state: false });
  let oldIsDirty = false;
  let refreshDirtyState = (isDirty) => {
    toggleBooleanStateDirective(el, directive2, isDirty);
    oldIsDirty = isDirty;
  };
  refreshDirtyStatesByComponent.add(component, refreshDirtyState);
  Alpine.effect(() => {
    let isDirty = false;
    if (targets.length === 0) {
      isDirty = JSON.stringify(component.canonical) !== JSON.stringify(component.reactive);
    } else {
      for (let i = 0; i < targets.length; i++) {
        if (isDirty)
          break;
        let target = targets[i];
        isDirty = JSON.stringify(dataGet(component.canonical, target)) !== JSON.stringify(dataGet(component.reactive, target));
      }
    }
    if (oldIsDirty !== isDirty) {
      refreshDirtyState(isDirty);
    }
    oldIsDirty = isDirty;
  });
});
function dirtyTargets(el) {
  let directives = getDirectives(el);
  let targets = [];
  if (directives.has("model")) {
    targets.push(directives.get("model").expression);
  }
  if (directives.has("target")) {
    targets = targets.concat(directives.get("target").expression.split(",").map((s) => s.trim()));
  }
  return targets;
}

// js/directives/wire-model.js
var import_alpinejs15 = __toESM(require_module_cjs());
function debounce(func, wait) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      func.apply(context, args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}
directive("model", ({ el, directive: directive2, component, cleanup: cleanup2 }) => {
  let { expression, modifiers } = directive2;
  if (!expression) {
    return console.warn("Livewire: [wire:model] is missing a value.", el);
  }
  if (componentIsMissingProperty(component, expression)) {
    return console.warn('Livewire: [wire:model="' + expression + '"] property does not exist on component: [' + component.name + "]", el);
  }
  if (el.type && el.type.toLowerCase() === "file") {
    return handleFileUpload(el, expression, component, cleanup2);
  }
  let isLive = modifiers.includes("live");
  let isLazy = modifiers.includes("lazy");
  let onBlur = modifiers.includes("blur");
  let isDebounced = modifiers.includes("debounce");
  let update = () => component.$wire.$commit();
  let debouncedUpdate = isTextInput(el) && !isDebounced && isLive ? debounce(update, 150) : update;
  import_alpinejs15.default.bind(el, {
    ["@change"]() {
      isLazy && update();
    },
    ["@blur"]() {
      onBlur && update();
    },
    ["x-model" + getModifierTail(modifiers)]() {
      return {
        get() {
          return dataGet(component.$wire, expression);
        },
        set(value) {
          dataSet(component.$wire, expression, value);
          isLive && !isLazy && !onBlur && debouncedUpdate();
        }
      };
    }
  });
});
function getModifierTail(modifiers) {
  modifiers = modifiers.filter((i) => ![
    "lazy",
    "defer"
  ].includes(i));
  if (modifiers.length === 0)
    return "";
  return "." + modifiers.join(".");
}
function isTextInput(el) {
  return ["INPUT", "TEXTAREA"].includes(el.tagName.toUpperCase()) && !["checkbox", "radio"].includes(el.type);
}
function componentIsMissingProperty(component, property) {
  if (property.startsWith("$parent")) {
    let parent = closestComponent(component.el.parentElement, false);
    if (!parent)
      return true;
    return componentIsMissingProperty(parent, property.split("$parent.")[1]);
  }
  let baseProperty = property.split(".")[0];
  return !Object.keys(component.canonical).includes(baseProperty);
}

// js/directives/wire-init.js
var import_alpinejs16 = __toESM(require_module_cjs());
directive("init", ({ el, directive: directive2 }) => {
  let fullMethod = directive2.expression ?? "$refresh";
  import_alpinejs16.default.evaluate(el, `$wire.${fullMethod}`);
});

// js/directives/wire-poll.js
var import_alpinejs17 = __toESM(require_module_cjs());
directive("poll", ({ el, directive: directive2, component }) => {
  let interval = extractDurationFrom(directive2.modifiers, 2e3);
  let { start: start2, pauseWhile, throttleWhile, stopWhen } = poll(() => {
    triggerComponentRequest(el, directive2);
  }, interval);
  start2();
  throttleWhile(() => theTabIsInTheBackground() && theDirectiveIsMissingKeepAlive(directive2));
  pauseWhile(() => theDirectiveHasVisible(directive2) && theElementIsNotInTheViewport(el));
  pauseWhile(() => theDirectiveIsOffTheElement(el));
  pauseWhile(() => livewireIsOffline());
  stopWhen(() => theElementIsDisconnected(el));
});
function triggerComponentRequest(el, directive2) {
  import_alpinejs17.default.evaluate(el, directive2.expression ? "$wire." + directive2.expression : "$wire.$commit()");
}
function poll(callback, interval = 2e3) {
  let pauseConditions = [];
  let throttleConditions = [];
  let stopConditions = [];
  return {
    start() {
      let clear = syncronizedInterval(interval, () => {
        if (stopConditions.some((i) => i()))
          return clear();
        if (pauseConditions.some((i) => i()))
          return;
        if (throttleConditions.some((i) => i()) && Math.random() < 0.95)
          return;
        callback();
      });
    },
    pauseWhile(condition) {
      pauseConditions.push(condition);
    },
    throttleWhile(condition) {
      throttleConditions.push(condition);
    },
    stopWhen(condition) {
      stopConditions.push(condition);
    }
  };
}
var clocks = [];
function syncronizedInterval(ms, callback) {
  if (!clocks[ms]) {
    let clock = {
      timer: setInterval(() => clock.callbacks.forEach((i) => i()), ms),
      callbacks: /* @__PURE__ */ new Set()
    };
    clocks[ms] = clock;
  }
  clocks[ms].callbacks.add(callback);
  return () => {
    clocks[ms].callbacks.delete(callback);
    if (clocks[ms].callbacks.size === 0) {
      clearInterval(clocks[ms].timer);
      delete clocks[ms];
    }
  };
}
var isOffline = false;
window.addEventListener("offline", () => isOffline = true);
window.addEventListener("online", () => isOffline = false);
function livewireIsOffline() {
  return isOffline;
}
var inBackground = false;
document.addEventListener("visibilitychange", () => {
  inBackground = document.hidden;
}, false);
function theTabIsInTheBackground() {
  return inBackground;
}
function theDirectiveIsOffTheElement(el) {
  return !getDirectives(el).has("poll");
}
function theDirectiveIsMissingKeepAlive(directive2) {
  return !directive2.modifiers.includes("keep-alive");
}
function theDirectiveHasVisible(directive2) {
  return directive2.modifiers.includes("visible");
}
function theElementIsNotInTheViewport(el) {
  let bounding = el.getBoundingClientRect();
  return !(bounding.top < (window.innerHeight || document.documentElement.clientHeight) && bounding.left < (window.innerWidth || document.documentElement.clientWidth) && bounding.bottom > 0 && bounding.right > 0);
}
function theElementIsDisconnected(el) {
  return el.isConnected === false;
}
function extractDurationFrom(modifiers, defaultDuration) {
  let durationInMilliSeconds;
  let durationInMilliSecondsString = modifiers.find((mod) => mod.match(/([0-9]+)ms/));
  let durationInSecondsString = modifiers.find((mod) => mod.match(/([0-9]+)s/));
  if (durationInMilliSecondsString) {
    durationInMilliSeconds = Number(durationInMilliSecondsString.replace("ms", ""));
  } else if (durationInSecondsString) {
    durationInMilliSeconds = Number(durationInSecondsString.replace("s", "")) * 1e3;
  }
  return durationInMilliSeconds || defaultDuration;
}

// js/index.js
var Livewire2 = {
  directive,
  dispatchTo,
  start,
  stop,
  rescan,
  first,
  find,
  getByName,
  all,
  hook: on,
  trigger,
  dispatch: dispatchGlobal,
  on: on2
};
if (window.Livewire)
  console.warn("Detected multiple instances of Livewire running");
if (window.Alpine)
  console.warn("Detected multiple instances of Alpine running");
window.Livewire = Livewire2;
window.Alpine = import_alpinejs18.default;
if (window.livewireScriptConfig === void 0) {
  document.addEventListener("DOMContentLoaded", () => {
    Livewire2.start();
  });
}
var export_Alpine = import_alpinejs18.default;
export {
  export_Alpine as Alpine,
  Livewire2 as Livewire
};
/* NProgress, (c) 2013, 2014 Rico Sta. Cruz - http://ricostacruz.com/nprogress
 * @license MIT */
/*!
* focus-trap 6.6.1
* @license MIT, https://github.com/focus-trap/focus-trap/blob/master/LICENSE
*/
/*!
* tabbable 5.2.1
* @license MIT, https://github.com/focus-trap/tabbable/blob/master/LICENSE
*/
