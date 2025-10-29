(() => {
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
  var __toESM = (mod, isNodeMode, target) => (target = mod != null ? __create(__getProtoOf(mod)) : {}, __copyProps(
    isNodeMode || !mod || !mod.__esModule ? __defProp(target, "default", { value: mod, enumerable: true }) : target,
    mod
  ));

  // ../alpine/packages/csp/dist/module.cjs.js
  var require_module_cjs = __commonJS({
    "../alpine/packages/csp/dist/module.cjs.js"(exports, module) {
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
      var __toESM2 = (mod, isNodeMode, target) => (target = mod != null ? __create2(__getProtoOf2(mod)) : {}, __copyProps2(
        isNodeMode || !mod || !mod.__esModule ? __defProp2(target, "default", { value: mod, enumerable: true }) : target,
        mod
      ));
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
            let index2;
            let lastIndex = 0;
            for (index2 = match.index; index2 < str.length; index2++) {
              switch (str.charCodeAt(index2)) {
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
              if (lastIndex !== index2) {
                html += str.substring(lastIndex, index2);
              }
              lastIndex = index2 + 1;
              html += escaped;
            }
            return lastIndex !== index2 ? html + str.substring(lastIndex, index2) : html;
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
          var extend2 = Object.assign;
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
          var isReservedProp = /* @__PURE__ */ makeMap(
            ",key,ref,onVnodeBeforeMount,onVnodeMounted,onVnodeBeforeUpdate,onVnodeUpdated,onVnodeBeforeUnmount,onVnodeUnmounted"
          );
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
          exports2.extend = extend2;
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
              cleanup(effect4);
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
                cleanup(effect4);
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
          function cleanup(effect4) {
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
          function ownKeys3(target) {
            track2(target, "iterate", shared.isArray(target) ? "length" : ITERATE_KEY);
            return Reflect.ownKeys(target);
          }
          var mutableHandlers = {
            get: get2,
            set: set2,
            deleteProperty,
            has,
            ownKeys: ownKeys3
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
              add: createReadonlyMethod(
                "add"
              ),
              set: createReadonlyMethod(
                "set"
              ),
              delete: createReadonlyMethod(
                "delete"
              ),
              clear: createReadonlyMethod(
                "clear"
              ),
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
              add: createReadonlyMethod(
                "add"
              ),
              set: createReadonlyMethod(
                "set"
              ),
              delete: createReadonlyMethod(
                "delete"
              ),
              clear: createReadonlyMethod(
                "clear"
              ),
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
        Alpine: () => src_default10,
        default: () => module_default10
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
        let index2 = queue.indexOf(job);
        if (index2 !== -1 && index2 > lastFlushedIndex)
          queue.splice(index2, 1);
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
        let cleanup = () => {
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
          cleanup = () => {
            if (effectReference === void 0)
              return;
            el._x_effects.delete(effectReference);
            release(effectReference);
          };
          return effectReference;
        };
        return [wrappedEffect, () => {
          cleanup();
        }];
      }
      function watch(getter, callback) {
        let firstTime = true;
        let oldValue;
        let effectReference = effect(() => {
          let value = getter();
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
        return () => release(effectReference);
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
        var _a, _b;
        (_a = el._x_effects) == null ? void 0 : _a.forEach(dequeueJob);
        while ((_b = el._x_cleanups) == null ? void 0 : _b.length)
          el._x_cleanups.pop()();
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
      var queuedMutations = [];
      function flushObserver() {
        let records = observer.takeRecords();
        queuedMutations.push(() => records.length > 0 && onMutate(records));
        let queueLengthWhenTriggered = queuedMutations.length;
        queueMicrotask(() => {
          if (queuedMutations.length === queueLengthWhenTriggered) {
            while (queuedMutations.length > 0)
              queuedMutations.shift()();
          }
        });
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
        let removedNodes = /* @__PURE__ */ new Set();
        let addedAttributes = /* @__PURE__ */ new Map();
        let removedAttributes = /* @__PURE__ */ new Map();
        for (let i = 0; i < mutations.length; i++) {
          if (mutations[i].target._x_ignoreMutationObserver)
            continue;
          if (mutations[i].type === "childList") {
            mutations[i].removedNodes.forEach((node) => {
              if (node.nodeType !== 1)
                return;
              if (!node._x_marker)
                return;
              removedNodes.add(node);
            });
            mutations[i].addedNodes.forEach((node) => {
              if (node.nodeType !== 1)
                return;
              if (removedNodes.has(node)) {
                removedNodes.delete(node);
                return;
              }
              if (node._x_marker)
                return;
              addedNodes.push(node);
            });
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
          if (addedNodes.some((i) => i.contains(node)))
            continue;
          onElRemoveds.forEach((i) => i(node));
        }
        for (let node of addedNodes) {
          if (!node.isConnected)
            continue;
          onElAddeds.forEach((i) => i(node));
        }
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
        return new Proxy({ objects }, mergeProxyTrap);
      }
      var mergeProxyTrap = {
        ownKeys({ objects }) {
          return Array.from(
            new Set(objects.flatMap((i) => Object.keys(i)))
          );
        },
        has({ objects }, name) {
          if (name == Symbol.unscopables)
            return false;
          return objects.some(
            (obj) => Object.prototype.hasOwnProperty.call(obj, name) || Reflect.has(obj, name)
          );
        },
        get({ objects }, name, thisProxy) {
          if (name == "toJSON")
            return collapseProxies;
          return Reflect.get(
            objects.find(
              (obj) => Reflect.has(obj, name)
            ) || {},
            name,
            thisProxy
          );
        },
        set({ objects }, name, value, thisProxy) {
          const target = objects.find(
            (obj) => Object.prototype.hasOwnProperty.call(obj, name)
          ) || objects[objects.length - 1];
          const descriptor = Object.getOwnPropertyDescriptor(target, name);
          if ((descriptor == null ? void 0 : descriptor.set) && (descriptor == null ? void 0 : descriptor.get))
            return descriptor.set.call(thisProxy, value) || true;
          return Reflect.set(target, name, value);
        }
      };
      function collapseProxies() {
        let keys = Reflect.ownKeys(this);
        return keys.reduce((acc, key) => {
          acc[key] = Reflect.get(this, key);
          return acc;
        }, {});
      }
      function initInterceptors(data2) {
        let isObject2 = (val) => typeof val === "object" && !Array.isArray(val) && val !== null;
        let recurse = (obj, basePath = "") => {
          Object.entries(Object.getOwnPropertyDescriptors(obj)).forEach(([key, { value, enumerable }]) => {
            if (enumerable === false || value === void 0)
              return;
            if (typeof value === "object" && value !== null && value.__v_skip)
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
        let memoizedUtilities = getUtilities(el);
        Object.entries(magics).forEach(([name, callback]) => {
          Object.defineProperty(obj, `$${name}`, {
            get() {
              return callback(el, memoizedUtilities);
            },
            enumerable: false
          });
        });
        return obj;
      }
      function getUtilities(el) {
        let [utilities, cleanup] = getElementBoundUtilities(el);
        let utils = { interceptor, ...utilities };
        onElRemoved(el, cleanup);
        return utils;
      }
      function tryCatch(el, expression, callback, ...args) {
        try {
          return callback(...args);
        } catch (e) {
          handleError(e, el, expression);
        }
      }
      function handleError(error2, el, expression = void 0) {
        error2 = Object.assign(
          error2 != null ? error2 : { message: "No error message given." },
          { el, expression }
        );
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
      function evaluate2(el, expression, extras = {}) {
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
        }, { scope: scope2 = {}, params = [], context } = {}) => {
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
            let func2 = new AsyncFunction(
              ["__self", "scope"],
              `with (scope) { __self.result = ${rightSideSafeExpression} }; __self.finished = true; return __self.result;`
            );
            Object.defineProperty(func2, "name", {
              value: `[Alpine] ${expression}`
            });
            return func2;
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
        }, { scope: scope2 = {}, params = [], context } = {}) => {
          func.result = void 0;
          func.finished = false;
          let completeScope = mergeProxies([scope2, ...dataStack]);
          if (typeof func === "function") {
            let promise = func.call(context, func, completeScope).catch((error2) => handleError(error2, el, expression));
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
              console.warn(String.raw`Cannot find directive \`${directive22}\`. \`${name}\` will use the default order of execution`);
              return;
            }
            const pos = directiveOrder.indexOf(directive22);
            directiveOrder.splice(pos >= 0 ? pos : directiveOrder.indexOf("DEFAULT"), 0, name);
          }
        };
      }
      function directiveExists(name) {
        return Object.keys(directiveHandlers).includes(name);
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
        let cleanups2 = [];
        let cleanup = (callback) => cleanups2.push(callback);
        let [effect3, cleanupEffect] = elementBoundEffect(el);
        cleanups2.push(cleanupEffect);
        let utilities = {
          Alpine: alpine_default,
          effect: effect3,
          cleanup,
          evaluateLater: evaluateLater.bind(evaluateLater, el),
          evaluate: evaluate2.bind(evaluate2, el)
        };
        let doCleanup = () => cleanups2.forEach((i) => i());
        return [utilities, doCleanup];
      }
      function getDirectiveHandler(el, directive22) {
        let noop = () => {
        };
        let handler4 = directiveHandlers[directive22.type] || noop;
        let [utilities, cleanup] = getElementBoundUtilities(el);
        onAttributeRemoved(el, directive22.original, cleanup);
        let fullHandler = () => {
          if (el._x_ignore || el._x_ignoreSelf)
            return;
          handler4.inline && handler4.inline(el, directive22, utilities);
          handler4 = handler4.bind(handler4, el, directive22, utilities);
          isDeferringHandlers ? directiveHandlerStacks.get(currentHandlerStackKey).push(handler4) : handler4();
        };
        fullHandler.runCleanups = cleanup;
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
          let valueMatch = name.match(/:([a-zA-Z0-9\-_:]+)/);
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
        "anchor",
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
      function dispatch3(el, name, detail = {}) {
        el.dispatchEvent(
          new CustomEvent(name, {
            detail,
            bubbles: true,
            composed: true,
            cancelable: true
          })
        );
      }
      function walk2(el, callback) {
        if (typeof ShadowRoot === "function" && el instanceof ShadowRoot) {
          Array.from(el.children).forEach((el2) => walk2(el2, callback));
          return;
        }
        let skip = false;
        callback(el, () => skip = true);
        if (skip)
          return;
        let node = el.firstElementChild;
        while (node) {
          walk2(node, callback, false);
          node = node.nextElementSibling;
        }
      }
      function warn(message, ...args) {
        console.warn(`Alpine Warning: ${message}`, ...args);
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
        onElAdded((el) => initTree(el, walk2));
        onElRemoved((el) => destroyTree(el));
        onAttributesAdded((el, attrs) => {
          directives(el, attrs).forEach((handle) => handle());
        });
        let outNestedComponents = (el) => !closestRoot(el.parentElement, true);
        Array.from(document.querySelectorAll(allSelectors().join(","))).filter(outNestedComponents).forEach((el) => {
          initTree(el);
        });
        dispatch3(document, "alpine:initialized");
        setTimeout(() => {
          warnAboutMissingPlugins();
        });
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
      var initInterceptors2 = [];
      function interceptInit(callback) {
        initInterceptors2.push(callback);
      }
      var markerDispenser = 1;
      function initTree(el, walker = walk2, intercept2 = () => {
      }) {
        if (findClosest(el, (i) => i._x_ignore))
          return;
        deferHandlingDirectives(() => {
          walker(el, (el2, skip) => {
            if (el2._x_marker)
              return;
            intercept2(el2, skip);
            initInterceptors2.forEach((i) => i(el2, skip));
            directives(el2, el2.attributes).forEach((handle) => handle());
            if (!el2._x_ignore)
              el2._x_marker = markerDispenser++;
            el2._x_ignore && skip();
          });
        });
      }
      function destroyTree(root, walker = walk2) {
        walker(root, (el) => {
          cleanupElement(el);
          cleanupAttributes(el);
          delete el._x_marker;
        });
      }
      function warnAboutMissingPlugins() {
        let pluginDirectives = [
          ["ui", "dialog", ["[x-dialog], [x-popover]"]],
          ["anchor", "anchor", ["[x-anchor]"]],
          ["sort", "sort", ["[x-sort]"]]
        ];
        pluginDirectives.forEach(([plugin2, directive22, selectors]) => {
          if (directiveExists(directive22))
            return;
          selectors.some((selector) => {
            if (document.querySelector(selector)) {
              warn(`found "${selector}", but missing ${plugin2} plugin`);
              return true;
            }
          });
        });
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
      function setStyles2(el, value) {
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
          setStyles2(el, previousStyles);
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
      directive2("transition", (el, { value, modifiers, expression }, { evaluate: evaluate22 }) => {
        if (typeof expression === "function")
          expression = evaluate22(expression);
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
        registerTransitionObject(el, setStyles2);
        let doesntSpecify = !modifiers.includes("in") && !modifiers.includes("out") && !stage;
        let transitioningIn = doesntSpecify || modifiers.includes("in") || ["enter"].includes(stage);
        let transitioningOut = doesntSpecify || modifiers.includes("out") || ["leave"].includes(stage);
        if (modifiers.includes("in") && !doesntSpecify) {
          modifiers = modifiers.filter((i, index2) => index2 < modifiers.indexOf("out"));
        }
        if (modifiers.includes("out") && !doesntSpecify) {
          modifiers = modifiers.filter((i, index2) => index2 > modifiers.indexOf("out"));
        }
        let wantsAll = !modifiers.includes("opacity") && !modifiers.includes("scale");
        let wantsOpacity = wantsAll || modifiers.includes("opacity");
        let wantsScale = wantsAll || modifiers.includes("scale");
        let opacityValue = wantsOpacity ? 0 : 1;
        let scaleValue = wantsScale ? modifierValue2(modifiers, "scale", 95) / 100 : 1;
        let delay3 = modifierValue2(modifiers, "delay", 0) / 1e3;
        let origin = modifierValue2(modifiers, "origin", "center");
        let property = "opacity, transform";
        let durationIn = modifierValue2(modifiers, "duration", 150) / 1e3;
        let durationOut = modifierValue2(modifiers, "duration", 75) / 1e3;
        let easing = `cubic-bezier(0.4, 0.0, 0.2, 1)`;
        if (transitioningIn) {
          el._x_transition.enter.during = {
            transformOrigin: origin,
            transitionDelay: `${delay3}s`,
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
            transitionDelay: `${delay3}s`,
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
          el._x_transitioning && el._x_transitioning.beforeCancel(() => reject({ isFromCancelledTransition: true }));
        }) : Promise.resolve(hide);
        queueMicrotask(() => {
          let closest2 = closestHide(el);
          if (closest2) {
            if (!closest2._x_hideChildren)
              closest2._x_hideChildren = [];
            closest2._x_hideChildren.push(el);
          } else {
            nextTick2(() => {
              let hideAfterChildren = (el2) => {
                let carry = Promise.all([
                  el2._x_hidePromise,
                  ...(el2._x_hideChildren || []).map(hideAfterChildren)
                ]).then(([i]) => i == null ? void 0 : i());
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
          let delay3 = Number(getComputedStyle(el).transitionDelay.replace(/,.*/, "").replace("s", "")) * 1e3;
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
            setTimeout(el._x_transitioning.finish, duration + delay3);
            reachedEnd = true;
          });
        });
      }
      function modifierValue2(modifiers, key, fallback2) {
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
      var interceptors2 = [];
      function interceptClone(callback) {
        interceptors2.push(callback);
      }
      function cloneNode(from, to) {
        interceptors2.forEach((i) => i(from, to));
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
      function clone2(oldEl, newEl) {
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
          walk2(el2, (el3, skip) => {
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
        if (isRadio3(el)) {
          if (el.attributes.value === void 0) {
            el.value = value;
          }
          if (window.fromModel) {
            if (typeof value === "boolean") {
              el.checked = safeParseBoolean(el.value) === value;
            } else {
              el.checked = checkedAttrLooseCompare(el.value, value);
            }
          }
        } else if (isCheckbox(el)) {
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
        el._x_undoAddedStyles = setStyles2(el, value);
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
        Array.from(el.options).forEach((option2) => {
          option2.selected = arrayWrappedValue.includes(option2.value);
        });
      }
      function camelCase(subject) {
        return subject.toLowerCase().replace(/-(\w)/g, (match, char) => char.toUpperCase());
      }
      function checkedAttrLooseCompare(valueA, valueB) {
        return valueA == valueB;
      }
      function safeParseBoolean(rawValue) {
        if ([1, "1", "true", "on", "yes", true].includes(rawValue)) {
          return true;
        }
        if ([0, "0", "false", "off", "no", false].includes(rawValue)) {
          return false;
        }
        return rawValue ? Boolean(rawValue) : null;
      }
      var booleanAttributes = /* @__PURE__ */ new Set([
        "allowfullscreen",
        "async",
        "autofocus",
        "autoplay",
        "checked",
        "controls",
        "default",
        "defer",
        "disabled",
        "formnovalidate",
        "inert",
        "ismap",
        "itemscope",
        "loop",
        "multiple",
        "muted",
        "nomodule",
        "novalidate",
        "open",
        "playsinline",
        "readonly",
        "required",
        "reversed",
        "selected",
        "shadowrootclonable",
        "shadowrootdelegatesfocus",
        "shadowrootserializable"
      ]);
      function isBooleanAttr(attrName) {
        return booleanAttributes.has(attrName);
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
            return evaluate2(el, binding.expression);
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
      function isCheckbox(el) {
        return el.type === "checkbox" || el.localName === "ui-checkbox" || el.localName === "ui-switch";
      }
      function isRadio3(el) {
        return el.type === "radio" || el.localName === "ui-radio";
      }
      function debounce2(func, wait) {
        let timeout;
        return function() {
          const context = this, args = arguments;
          const later = function() {
            timeout = null;
            func.apply(context, args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }
      function throttle2(func, limit) {
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
        let outerHash;
        let innerHash;
        let reference = effect(() => {
          let outer = outerGet();
          let inner = innerGet();
          if (firstRun) {
            innerSet(cloneIfObject2(outer));
            firstRun = false;
          } else {
            let outerHashLatest = JSON.stringify(outer);
            let innerHashLatest = JSON.stringify(inner);
            if (outerHashLatest !== outerHash) {
              innerSet(cloneIfObject2(outer));
            } else if (outerHashLatest !== innerHashLatest) {
              outerSet(cloneIfObject2(inner));
            } else {
            }
          }
          outerHash = JSON.stringify(outerGet());
          innerHash = JSON.stringify(innerGet());
        });
        return () => {
          release(reference);
        };
      }
      function cloneIfObject2(value) {
        return typeof value === "object" ? JSON.parse(JSON.stringify(value)) : value;
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
        initInterceptors(stores[name]);
        if (typeof value === "object" && value !== null && value.hasOwnProperty("init") && typeof value.init === "function") {
          stores[name].init();
        }
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
      var Alpine24 = {
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
        version: "3.15.1",
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
        interceptClone,
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
        setStyles: setStyles2,
        mutateDom,
        directive: directive2,
        entangle,
        throttle: throttle2,
        debounce: debounce2,
        evaluate: evaluate2,
        initTree,
        nextTick,
        prefixed: prefix,
        prefix: setPrefix,
        plugin,
        magic,
        store,
        start: start2,
        clone: clone2,
        cloneNode,
        bound: getBinding,
        $data: scope,
        watch,
        walk: walk2,
        data,
        bind: bind2
      };
      var alpine_default = Alpine24;
      var Token = class {
        constructor(type, value, start22, end) {
          this.type = type;
          this.value = value;
          this.start = start22;
          this.end = end;
        }
      };
      var Tokenizer = class {
        constructor(input) {
          this.input = input;
          this.position = 0;
          this.tokens = [];
        }
        tokenize() {
          while (this.position < this.input.length) {
            this.skipWhitespace();
            if (this.position >= this.input.length)
              break;
            const char = this.input[this.position];
            if (this.isDigit(char)) {
              this.readNumber();
            } else if (this.isAlpha(char) || char === "_" || char === "$") {
              this.readIdentifierOrKeyword();
            } else if (char === '"' || char === "'") {
              this.readString();
            } else if (char === "/" && this.peek() === "/") {
              this.skipLineComment();
            } else {
              this.readOperatorOrPunctuation();
            }
          }
          this.tokens.push(new Token("EOF", null, this.position, this.position));
          return this.tokens;
        }
        skipWhitespace() {
          while (this.position < this.input.length && /\s/.test(this.input[this.position])) {
            this.position++;
          }
        }
        skipLineComment() {
          while (this.position < this.input.length && this.input[this.position] !== "\n") {
            this.position++;
          }
        }
        isDigit(char) {
          return /[0-9]/.test(char);
        }
        isAlpha(char) {
          return /[a-zA-Z]/.test(char);
        }
        isAlphaNumeric(char) {
          return /[a-zA-Z0-9_$]/.test(char);
        }
        peek(offset2 = 1) {
          return this.input[this.position + offset2] || "";
        }
        readNumber() {
          const start22 = this.position;
          let hasDecimal = false;
          while (this.position < this.input.length) {
            const char = this.input[this.position];
            if (this.isDigit(char)) {
              this.position++;
            } else if (char === "." && !hasDecimal) {
              hasDecimal = true;
              this.position++;
            } else {
              break;
            }
          }
          const value = this.input.slice(start22, this.position);
          this.tokens.push(new Token("NUMBER", parseFloat(value), start22, this.position));
        }
        readIdentifierOrKeyword() {
          const start22 = this.position;
          while (this.position < this.input.length && this.isAlphaNumeric(this.input[this.position])) {
            this.position++;
          }
          const value = this.input.slice(start22, this.position);
          const keywords = ["true", "false", "null", "undefined", "new", "typeof", "void", "delete", "in", "instanceof"];
          if (keywords.includes(value)) {
            if (value === "true" || value === "false") {
              this.tokens.push(new Token("BOOLEAN", value === "true", start22, this.position));
            } else if (value === "null") {
              this.tokens.push(new Token("NULL", null, start22, this.position));
            } else if (value === "undefined") {
              this.tokens.push(new Token("UNDEFINED", void 0, start22, this.position));
            } else {
              this.tokens.push(new Token("KEYWORD", value, start22, this.position));
            }
          } else {
            this.tokens.push(new Token("IDENTIFIER", value, start22, this.position));
          }
        }
        readString() {
          const start22 = this.position;
          const quote = this.input[this.position];
          this.position++;
          let value = "";
          let escaped = false;
          while (this.position < this.input.length) {
            const char = this.input[this.position];
            if (escaped) {
              switch (char) {
                case "n":
                  value += "\n";
                  break;
                case "t":
                  value += "	";
                  break;
                case "r":
                  value += "\r";
                  break;
                case "\\":
                  value += "\\";
                  break;
                case quote:
                  value += quote;
                  break;
                default:
                  value += char;
              }
              escaped = false;
            } else if (char === "\\") {
              escaped = true;
            } else if (char === quote) {
              this.position++;
              this.tokens.push(new Token("STRING", value, start22, this.position));
              return;
            } else {
              value += char;
            }
            this.position++;
          }
          throw new Error(`Unterminated string starting at position ${start22}`);
        }
        readOperatorOrPunctuation() {
          const start22 = this.position;
          const char = this.input[this.position];
          const next = this.peek();
          const nextNext = this.peek(2);
          if (char === "=" && next === "=" && nextNext === "=") {
            this.position += 3;
            this.tokens.push(new Token("OPERATOR", "===", start22, this.position));
          } else if (char === "!" && next === "=" && nextNext === "=") {
            this.position += 3;
            this.tokens.push(new Token("OPERATOR", "!==", start22, this.position));
          } else if (char === "=" && next === "=") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "==", start22, this.position));
          } else if (char === "!" && next === "=") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "!=", start22, this.position));
          } else if (char === "<" && next === "=") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "<=", start22, this.position));
          } else if (char === ">" && next === "=") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", ">=", start22, this.position));
          } else if (char === "&" && next === "&") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "&&", start22, this.position));
          } else if (char === "|" && next === "|") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "||", start22, this.position));
          } else if (char === "+" && next === "+") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "++", start22, this.position));
          } else if (char === "-" && next === "-") {
            this.position += 2;
            this.tokens.push(new Token("OPERATOR", "--", start22, this.position));
          } else {
            this.position++;
            const type = "()[]{},.;:?".includes(char) ? "PUNCTUATION" : "OPERATOR";
            this.tokens.push(new Token(type, char, start22, this.position));
          }
        }
      };
      var Parser = class {
        constructor(tokens) {
          this.tokens = tokens;
          this.position = 0;
        }
        parse() {
          if (this.isAtEnd()) {
            throw new Error("Empty expression");
          }
          const expr = this.parseExpression();
          this.match("PUNCTUATION", ";");
          if (!this.isAtEnd()) {
            throw new Error(`Unexpected token: ${this.current().value}`);
          }
          return expr;
        }
        parseExpression() {
          return this.parseAssignment();
        }
        parseAssignment() {
          const expr = this.parseTernary();
          if (this.match("OPERATOR", "=")) {
            const value = this.parseAssignment();
            if (expr.type === "Identifier" || expr.type === "MemberExpression") {
              return {
                type: "AssignmentExpression",
                left: expr,
                operator: "=",
                right: value
              };
            }
            throw new Error("Invalid assignment target");
          }
          return expr;
        }
        parseTernary() {
          const expr = this.parseLogicalOr();
          if (this.match("PUNCTUATION", "?")) {
            const consequent = this.parseExpression();
            this.consume("PUNCTUATION", ":");
            const alternate = this.parseExpression();
            return {
              type: "ConditionalExpression",
              test: expr,
              consequent,
              alternate
            };
          }
          return expr;
        }
        parseLogicalOr() {
          let expr = this.parseLogicalAnd();
          while (this.match("OPERATOR", "||")) {
            const operator = this.previous().value;
            const right = this.parseLogicalAnd();
            expr = {
              type: "BinaryExpression",
              operator,
              left: expr,
              right
            };
          }
          return expr;
        }
        parseLogicalAnd() {
          let expr = this.parseEquality();
          while (this.match("OPERATOR", "&&")) {
            const operator = this.previous().value;
            const right = this.parseEquality();
            expr = {
              type: "BinaryExpression",
              operator,
              left: expr,
              right
            };
          }
          return expr;
        }
        parseEquality() {
          let expr = this.parseRelational();
          while (this.match("OPERATOR", "==", "!=", "===", "!==")) {
            const operator = this.previous().value;
            const right = this.parseRelational();
            expr = {
              type: "BinaryExpression",
              operator,
              left: expr,
              right
            };
          }
          return expr;
        }
        parseRelational() {
          let expr = this.parseAdditive();
          while (this.match("OPERATOR", "<", ">", "<=", ">=")) {
            const operator = this.previous().value;
            const right = this.parseAdditive();
            expr = {
              type: "BinaryExpression",
              operator,
              left: expr,
              right
            };
          }
          return expr;
        }
        parseAdditive() {
          let expr = this.parseMultiplicative();
          while (this.match("OPERATOR", "+", "-")) {
            const operator = this.previous().value;
            const right = this.parseMultiplicative();
            expr = {
              type: "BinaryExpression",
              operator,
              left: expr,
              right
            };
          }
          return expr;
        }
        parseMultiplicative() {
          let expr = this.parseUnary();
          while (this.match("OPERATOR", "*", "/", "%")) {
            const operator = this.previous().value;
            const right = this.parseUnary();
            expr = {
              type: "BinaryExpression",
              operator,
              left: expr,
              right
            };
          }
          return expr;
        }
        parseUnary() {
          if (this.match("OPERATOR", "++", "--")) {
            const operator = this.previous().value;
            const argument = this.parseUnary();
            return {
              type: "UpdateExpression",
              operator,
              argument,
              prefix: true
            };
          }
          if (this.match("OPERATOR", "!", "-", "+")) {
            const operator = this.previous().value;
            const argument = this.parseUnary();
            return {
              type: "UnaryExpression",
              operator,
              argument,
              prefix: true
            };
          }
          return this.parsePostfix();
        }
        parsePostfix() {
          let expr = this.parseMember();
          if (this.match("OPERATOR", "++", "--")) {
            const operator = this.previous().value;
            return {
              type: "UpdateExpression",
              operator,
              argument: expr,
              prefix: false
            };
          }
          return expr;
        }
        parseMember() {
          let expr = this.parsePrimary();
          while (true) {
            if (this.match("PUNCTUATION", ".")) {
              const property = this.consume("IDENTIFIER");
              expr = {
                type: "MemberExpression",
                object: expr,
                property: { type: "Identifier", name: property.value },
                computed: false
              };
            } else if (this.match("PUNCTUATION", "[")) {
              const property = this.parseExpression();
              this.consume("PUNCTUATION", "]");
              expr = {
                type: "MemberExpression",
                object: expr,
                property,
                computed: true
              };
            } else if (this.match("PUNCTUATION", "(")) {
              const args = this.parseArguments();
              expr = {
                type: "CallExpression",
                callee: expr,
                arguments: args
              };
            } else {
              break;
            }
          }
          return expr;
        }
        parseArguments() {
          const args = [];
          if (!this.check("PUNCTUATION", ")")) {
            do {
              args.push(this.parseExpression());
            } while (this.match("PUNCTUATION", ","));
          }
          this.consume("PUNCTUATION", ")");
          return args;
        }
        parsePrimary() {
          if (this.match("NUMBER")) {
            return { type: "Literal", value: this.previous().value };
          }
          if (this.match("STRING")) {
            return { type: "Literal", value: this.previous().value };
          }
          if (this.match("BOOLEAN")) {
            return { type: "Literal", value: this.previous().value };
          }
          if (this.match("NULL")) {
            return { type: "Literal", value: null };
          }
          if (this.match("UNDEFINED")) {
            return { type: "Literal", value: void 0 };
          }
          if (this.match("IDENTIFIER")) {
            return { type: "Identifier", name: this.previous().value };
          }
          if (this.match("PUNCTUATION", "(")) {
            const expr = this.parseExpression();
            this.consume("PUNCTUATION", ")");
            return expr;
          }
          if (this.match("PUNCTUATION", "[")) {
            return this.parseArrayLiteral();
          }
          if (this.match("PUNCTUATION", "{")) {
            return this.parseObjectLiteral();
          }
          throw new Error(`Unexpected token: ${this.current().type} "${this.current().value}"`);
        }
        parseArrayLiteral() {
          const elements = [];
          while (!this.check("PUNCTUATION", "]") && !this.isAtEnd()) {
            elements.push(this.parseExpression());
            if (this.match("PUNCTUATION", ",")) {
              if (this.check("PUNCTUATION", "]")) {
                break;
              }
            } else {
              break;
            }
          }
          this.consume("PUNCTUATION", "]");
          return {
            type: "ArrayExpression",
            elements
          };
        }
        parseObjectLiteral() {
          const properties2 = [];
          while (!this.check("PUNCTUATION", "}") && !this.isAtEnd()) {
            let key;
            let computed = false;
            if (this.match("STRING")) {
              key = { type: "Literal", value: this.previous().value };
            } else if (this.match("IDENTIFIER")) {
              const name = this.previous().value;
              key = { type: "Identifier", name };
            } else if (this.match("PUNCTUATION", "[")) {
              key = this.parseExpression();
              computed = true;
              this.consume("PUNCTUATION", "]");
            } else {
              throw new Error("Expected property key");
            }
            this.consume("PUNCTUATION", ":");
            const value = this.parseExpression();
            properties2.push({
              type: "Property",
              key,
              value,
              computed,
              shorthand: false
            });
            if (this.match("PUNCTUATION", ",")) {
              if (this.check("PUNCTUATION", "}")) {
                break;
              }
            } else {
              break;
            }
          }
          this.consume("PUNCTUATION", "}");
          return {
            type: "ObjectExpression",
            properties: properties2
          };
        }
        match(...args) {
          for (let i = 0; i < args.length; i++) {
            const arg = args[i];
            if (i === 0 && args.length > 1) {
              const type = arg;
              for (let j = 1; j < args.length; j++) {
                if (this.check(type, args[j])) {
                  this.advance();
                  return true;
                }
              }
              return false;
            } else if (args.length === 1) {
              if (this.checkType(arg)) {
                this.advance();
                return true;
              }
              return false;
            }
          }
          return false;
        }
        check(type, value) {
          if (this.isAtEnd())
            return false;
          if (value !== void 0) {
            return this.current().type === type && this.current().value === value;
          }
          return this.current().type === type;
        }
        checkType(type) {
          if (this.isAtEnd())
            return false;
          return this.current().type === type;
        }
        advance() {
          if (!this.isAtEnd())
            this.position++;
          return this.previous();
        }
        isAtEnd() {
          return this.current().type === "EOF";
        }
        current() {
          return this.tokens[this.position];
        }
        previous() {
          return this.tokens[this.position - 1];
        }
        consume(type, value) {
          if (value !== void 0) {
            if (this.check(type, value))
              return this.advance();
            throw new Error(`Expected ${type} "${value}" but got ${this.current().type} "${this.current().value}"`);
          }
          if (this.check(type))
            return this.advance();
          throw new Error(`Expected ${type} but got ${this.current().type} "${this.current().value}"`);
        }
      };
      var Evaluator = class {
        evaluate({ node, scope: scope2 = {}, context = null, allowGlobal = false, forceBindingRootScopeToFunctions = true }) {
          switch (node.type) {
            case "Literal":
              return node.value;
            case "Identifier":
              if (node.name in scope2) {
                const value2 = scope2[node.name];
                if (typeof value2 === "function") {
                  return value2.bind(scope2);
                }
                return value2;
              }
              if (allowGlobal && typeof globalThis[node.name] !== "undefined") {
                const value2 = globalThis[node.name];
                if (typeof value2 === "function") {
                  return value2.bind(globalThis);
                }
                return value2;
              }
              throw new Error(`Undefined variable: ${node.name}`);
            case "MemberExpression":
              const object = this.evaluate({ node: node.object, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
              if (object == null) {
                throw new Error("Cannot read property of null or undefined");
              }
              let memberValue;
              if (node.computed) {
                const property = this.evaluate({ node: node.property, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                memberValue = object[property];
              } else {
                memberValue = object[node.property.name];
              }
              if (typeof memberValue === "function") {
                if (forceBindingRootScopeToFunctions) {
                  return memberValue.bind(scope2);
                } else {
                  return memberValue.bind(object);
                }
              }
              return memberValue;
            case "CallExpression":
              const args = node.arguments.map((arg) => this.evaluate({ node: arg, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions }));
              if (node.callee.type === "MemberExpression") {
                const obj = this.evaluate({ node: node.callee.object, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                let func;
                if (node.callee.computed) {
                  const prop = this.evaluate({ node: node.callee.property, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                  func = obj[prop];
                } else {
                  func = obj[node.callee.property.name];
                }
                if (typeof func !== "function") {
                  throw new Error("Value is not a function");
                }
                return func.apply(obj, args);
              } else {
                if (node.callee.type === "Identifier") {
                  const name = node.callee.name;
                  let func;
                  if (name in scope2) {
                    func = scope2[name];
                  } else if (allowGlobal && typeof globalThis[name] !== "undefined") {
                    func = globalThis[name];
                  } else {
                    throw new Error(`Undefined variable: ${name}`);
                  }
                  if (typeof func !== "function") {
                    throw new Error("Value is not a function");
                  }
                  const thisContext = context !== null ? context : scope2;
                  return func.apply(thisContext, args);
                } else {
                  const callee = this.evaluate({ node: node.callee, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                  if (typeof callee !== "function") {
                    throw new Error("Value is not a function");
                  }
                  return callee.apply(context, args);
                }
              }
            case "UnaryExpression":
              const argument = this.evaluate({ node: node.argument, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
              switch (node.operator) {
                case "!":
                  return !argument;
                case "-":
                  return -argument;
                case "+":
                  return +argument;
                default:
                  throw new Error(`Unknown unary operator: ${node.operator}`);
              }
            case "UpdateExpression":
              if (node.argument.type === "Identifier") {
                const name = node.argument.name;
                if (!(name in scope2)) {
                  throw new Error(`Undefined variable: ${name}`);
                }
                const oldValue = scope2[name];
                if (node.operator === "++") {
                  scope2[name] = oldValue + 1;
                } else if (node.operator === "--") {
                  scope2[name] = oldValue - 1;
                }
                return node.prefix ? scope2[name] : oldValue;
              } else if (node.argument.type === "MemberExpression") {
                const obj = this.evaluate({ node: node.argument.object, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                const prop = node.argument.computed ? this.evaluate({ node: node.argument.property, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions }) : node.argument.property.name;
                const oldValue = obj[prop];
                if (node.operator === "++") {
                  obj[prop] = oldValue + 1;
                } else if (node.operator === "--") {
                  obj[prop] = oldValue - 1;
                }
                return node.prefix ? obj[prop] : oldValue;
              }
              throw new Error("Invalid update expression target");
            case "BinaryExpression":
              const left = this.evaluate({ node: node.left, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
              const right = this.evaluate({ node: node.right, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
              switch (node.operator) {
                case "+":
                  return left + right;
                case "-":
                  return left - right;
                case "*":
                  return left * right;
                case "/":
                  return left / right;
                case "%":
                  return left % right;
                case "==":
                  return left == right;
                case "!=":
                  return left != right;
                case "===":
                  return left === right;
                case "!==":
                  return left !== right;
                case "<":
                  return left < right;
                case ">":
                  return left > right;
                case "<=":
                  return left <= right;
                case ">=":
                  return left >= right;
                case "&&":
                  return left && right;
                case "||":
                  return left || right;
                default:
                  throw new Error(`Unknown binary operator: ${node.operator}`);
              }
            case "ConditionalExpression":
              const test = this.evaluate({ node: node.test, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
              return test ? this.evaluate({ node: node.consequent, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions }) : this.evaluate({ node: node.alternate, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
            case "AssignmentExpression":
              const value = this.evaluate({ node: node.right, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
              if (node.left.type === "Identifier") {
                scope2[node.left.name] = value;
                return value;
              } else if (node.left.type === "MemberExpression") {
                const obj = this.evaluate({ node: node.left.object, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                if (node.left.computed) {
                  const prop = this.evaluate({ node: node.left.property, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                  obj[prop] = value;
                } else {
                  obj[node.left.property.name] = value;
                }
                return value;
              }
              throw new Error("Invalid assignment target");
            case "ArrayExpression":
              return node.elements.map((el) => this.evaluate({ node: el, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions }));
            case "ObjectExpression":
              const result = {};
              for (const prop of node.properties) {
                const key = prop.computed ? this.evaluate({ node: prop.key, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions }) : prop.key.type === "Identifier" ? prop.key.name : this.evaluate({ node: prop.key, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                const value2 = this.evaluate({ node: prop.value, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
                result[key] = value2;
              }
              return result;
            default:
              throw new Error(`Unknown node type: ${node.type}`);
          }
        }
      };
      function generateRuntimeFunction(expression) {
        try {
          const tokenizer = new Tokenizer(expression);
          const tokens = tokenizer.tokenize();
          const parser = new Parser(tokens);
          const ast = parser.parse();
          const evaluator = new Evaluator();
          return function(options = {}) {
            const { scope: scope2 = {}, context = null, allowGlobal = false, forceBindingRootScopeToFunctions = false } = options;
            return evaluator.evaluate({ node: ast, scope: scope2, context, allowGlobal, forceBindingRootScopeToFunctions });
          };
        } catch (error2) {
          throw new Error(`CSP Parser Error: ${error2.message}`);
        }
      }
      function cspEvaluator(el, expression) {
        let dataStack = generateDataStack(el);
        if (typeof expression === "function") {
          return generateEvaluatorFromFunction(dataStack, expression);
        }
        let evaluator = generateEvaluator(el, expression, dataStack);
        return tryCatch.bind(null, el, expression, evaluator);
      }
      function generateDataStack(el) {
        let overriddenMagics = {};
        injectMagics(overriddenMagics, el);
        return [overriddenMagics, ...closestDataStack(el)];
      }
      function generateEvaluator(el, expression, dataStack) {
        return (receiver = () => {
        }, { scope: scope2 = {}, params = [] } = {}) => {
          let completeScope = mergeProxies([scope2, ...dataStack]);
          let evaluate22 = generateRuntimeFunction(expression);
          let returnValue = evaluate22({
            scope: completeScope,
            allowGlobal: false,
            forceBindingRootScopeToFunctions: true
          });
          if (shouldAutoEvaluateFunctions && typeof returnValue === "function") {
            let nextReturnValue = returnValue.apply(returnValue, params);
            if (nextReturnValue instanceof Promise) {
              nextReturnValue.then((i) => receiver(i));
            } else {
              receiver(nextReturnValue);
            }
          } else if (typeof returnValue === "object" && returnValue instanceof Promise) {
            returnValue.then((i) => receiver(i));
          } else {
            receiver(returnValue);
          }
        };
      }
      var import_reactivity10 = __toESM2(require_reactivity());
      magic("nextTick", () => nextTick);
      magic("dispatch", (el) => dispatch3.bind(dispatch3, el));
      magic("watch", (el, { evaluateLater: evaluateLater2, cleanup }) => (key, callback) => {
        let evaluate22 = evaluateLater2(key);
        let getter = () => {
          let value;
          evaluate22((i) => value = i);
          return value;
        };
        let unwatch = watch(getter, callback);
        cleanup(unwatch);
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
        findClosest(el, (i) => {
          if (i._x_refs)
            refObjects.push(i._x_refs);
        });
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
      magic("id", (el, { cleanup }) => (name, key = null) => {
        let cacheKey = `${name}${key ? `-${key}` : ""}`;
        return cacheIdByNameOnElement(el, cacheKey, cleanup, () => {
          let root = closestIdRoot(el, name);
          let id = root ? root._x_ids[name] : findAndIncrementId(name);
          return key ? `${name}-${id}-${key}` : `${name}-${id}`;
        });
      });
      interceptClone((from, to) => {
        if (from._x_id) {
          to._x_id = from._x_id;
        }
      });
      function cacheIdByNameOnElement(el, cacheKey, cleanup, callback) {
        if (!el._x_id)
          el._x_id = {};
        if (el._x_id[cacheKey])
          return el._x_id[cacheKey];
        let output = callback();
        el._x_id[cacheKey] = output;
        cleanup(() => {
          delete el._x_id[cacheKey];
        });
        return output;
      }
      magic("el", (el) => el);
      warnMissingPluginMagic("Focus", "focus", "focus");
      warnMissingPluginMagic("Persist", "persist", "persist");
      function warnMissingPluginMagic(name, magicName, slug) {
        magic(magicName, (el) => warn(`You can't use [$${magicName}] without first installing the "${name}" plugin here: https://alpinejs.dev/plugins/${slug}`, el));
      }
      directive2("modelable", (el, { expression }, { effect: effect3, evaluateLater: evaluateLater2, cleanup }) => {
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
          let releaseEntanglement = entangle(
            {
              get() {
                return outerGet();
              },
              set(value) {
                outerSet(value);
              }
            },
            {
              get() {
                return innerGet();
              },
              set(value) {
                innerSet(value);
              }
            }
          );
          cleanup(releaseEntanglement);
        });
      });
      directive2("teleport", (el, { modifiers, expression }, { cleanup }) => {
        if (el.tagName.toLowerCase() !== "template")
          warn("x-teleport can only be used on a <template> tag", el);
        let target = getTarget(expression);
        let clone22 = el.content.cloneNode(true).firstElementChild;
        el._x_teleport = clone22;
        clone22._x_teleportBack = el;
        el.setAttribute("data-teleport-template", true);
        clone22.setAttribute("data-teleport-target", true);
        if (el._x_forwardEvents) {
          el._x_forwardEvents.forEach((eventName) => {
            clone22.addEventListener(eventName, (e) => {
              e.stopPropagation();
              el.dispatchEvent(new e.constructor(e.type, e));
            });
          });
        }
        addScopeToNode(clone22, {}, el);
        let placeInDom = (clone3, target2, modifiers2) => {
          if (modifiers2.includes("prepend")) {
            target2.parentNode.insertBefore(clone3, target2);
          } else if (modifiers2.includes("append")) {
            target2.parentNode.insertBefore(clone3, target2.nextSibling);
          } else {
            target2.appendChild(clone3);
          }
        };
        mutateDom(() => {
          placeInDom(clone22, target, modifiers);
          skipDuringClone(() => {
            initTree(clone22);
          })();
        });
        el._x_teleportPutBack = () => {
          let target2 = getTarget(expression);
          mutateDom(() => {
            placeInDom(el._x_teleport, target2, modifiers);
          });
        };
        cleanup(
          () => mutateDom(() => {
            clone22.remove();
            destroyTree(clone22);
          })
        );
      });
      var teleportContainerDuringClone = document.createElement("div");
      function getTarget(expression) {
        let target = skipDuringClone(() => {
          return document.querySelector(expression);
        }, () => {
          return teleportContainerDuringClone;
        })();
        if (!target)
          warn(`Cannot find x-teleport element for selector: "${expression}"`);
        return target;
      }
      var handler = () => {
      };
      handler.inline = (el, { modifiers }, { cleanup }) => {
        modifiers.includes("self") ? el._x_ignoreSelf = true : el._x_ignore = true;
        cleanup(() => {
          modifiers.includes("self") ? delete el._x_ignoreSelf : delete el._x_ignore;
        });
      };
      directive2("ignore", handler);
      directive2("effect", skipDuringClone((el, { expression }, { effect: effect3 }) => {
        effect3(evaluateLater(el, expression));
      }));
      function on4(el, event, modifiers, callback) {
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
          handler4 = throttle2(handler4, wait);
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
        if (modifiers.includes("once")) {
          handler4 = wrapHandler(handler4, (next, e) => {
            next(e);
            listenerTarget.removeEventListener(event, handler4, options);
          });
        }
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
        if (modifiers.includes("self"))
          handler4 = wrapHandler(handler4, (next, e) => {
            e.target === el && next(e);
          });
        if (isKeyEvent(event) || isClickEvent(event)) {
          handler4 = wrapHandler(handler4, (next, e) => {
            if (isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers)) {
              return;
            }
            next(e);
          });
        }
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
        if ([" ", "_"].includes(
          subject
        ))
          return subject;
        return subject.replace(/([a-z])([A-Z])/g, "$1-$2").replace(/[_\s]/, "-").toLowerCase();
      }
      function isKeyEvent(event) {
        return ["keydown", "keyup"].includes(event);
      }
      function isClickEvent(event) {
        return ["contextmenu", "click", "mouse"].some((i) => event.includes(i));
      }
      function isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers) {
        let keyModifiers = modifiers.filter((i) => {
          return !["window", "document", "prevent", "stop", "once", "capture", "self", "away", "outside", "passive", "preserve-scroll"].includes(i);
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
            if (isClickEvent(e.type))
              return false;
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
          "comma": ",",
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
      directive2("model", (el, { modifiers, expression }, { effect: effect3, cleanup }) => {
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
        let event = el.tagName.toLowerCase() === "select" || ["checkbox", "radio"].includes(el.type) || modifiers.includes("lazy") ? "change" : "input";
        let removeListener = isCloning ? () => {
        } : on4(el, event, modifiers, (e) => {
          setValue(getInputValue(el, modifiers, e, getValue()));
        });
        if (modifiers.includes("fill")) {
          if ([void 0, null, ""].includes(getValue()) || isCheckbox(el) && Array.isArray(getValue()) || el.tagName.toLowerCase() === "select" && el.multiple) {
            setValue(
              getInputValue(el, modifiers, { target: el }, getValue())
            );
          }
        }
        if (!el._x_removeModelListeners)
          el._x_removeModelListeners = {};
        el._x_removeModelListeners["default"] = removeListener;
        cleanup(() => el._x_removeModelListeners["default"]());
        if (el.form) {
          let removeResetListener = on4(el.form, "reset", [], (e) => {
            nextTick(() => el._x_model && el._x_model.set(getInputValue(el, modifiers, { target: el }, getValue())));
          });
          cleanup(() => removeResetListener());
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
          if (event instanceof CustomEvent && event.detail !== void 0)
            return event.detail !== null && event.detail !== void 0 ? event.detail : event.target.value;
          else if (isCheckbox(el)) {
            if (Array.isArray(currentValue)) {
              let newValue = null;
              if (modifiers.includes("number")) {
                newValue = safeParseNumber(event.target.value);
              } else if (modifiers.includes("boolean")) {
                newValue = safeParseBoolean(event.target.value);
              } else {
                newValue = event.target.value;
              }
              return event.target.checked ? currentValue.includes(newValue) ? currentValue : currentValue.concat([newValue]) : currentValue.filter((el2) => !checkedAttrLooseCompare2(el2, newValue));
            } else {
              return event.target.checked;
            }
          } else if (el.tagName.toLowerCase() === "select" && el.multiple) {
            if (modifiers.includes("number")) {
              return Array.from(event.target.selectedOptions).map((option2) => {
                let rawValue = option2.value || option2.text;
                return safeParseNumber(rawValue);
              });
            } else if (modifiers.includes("boolean")) {
              return Array.from(event.target.selectedOptions).map((option2) => {
                let rawValue = option2.value || option2.text;
                return safeParseBoolean(rawValue);
              });
            }
            return Array.from(event.target.selectedOptions).map((option2) => {
              return option2.value || option2.text;
            });
          } else {
            let newValue;
            if (isRadio3(el)) {
              if (event.target.checked) {
                newValue = event.target.value;
              } else {
                newValue = currentValue;
              }
            } else {
              newValue = event.target.value;
            }
            if (modifiers.includes("number")) {
              return safeParseNumber(newValue);
            } else if (modifiers.includes("boolean")) {
              return safeParseBoolean(newValue);
            } else if (modifiers.includes("trim")) {
              return newValue.trim();
            } else {
              return newValue;
            }
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
      directive2("init", skipDuringClone((el, { expression }, { evaluate: evaluate22 }) => {
        if (typeof expression === "string") {
          return !!expression.trim() && evaluate22(expression, {}, false);
        }
        return evaluate22(expression, {}, false);
      }));
      directive2("text", (el, { expression }, { effect: effect3, evaluateLater: evaluateLater2 }) => {
        let evaluate22 = evaluateLater2(expression);
        effect3(() => {
          evaluate22((value) => {
            mutateDom(() => {
              el.textContent = value;
            });
          });
        });
      });
      directive2("html", (el, { expression }, { effect: effect3, evaluateLater: evaluateLater2 }) => {
        let evaluate22 = evaluateLater2(expression);
        effect3(() => {
          evaluate22((value) => {
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
      var handler2 = (el, { value, modifiers, expression, original }, { effect: effect3, cleanup }) => {
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
        let evaluate22 = evaluateLater(el, expression);
        effect3(() => evaluate22((result) => {
          if (result === void 0 && typeof expression === "string" && expression.match(/\./)) {
            result = "";
          }
          mutateDom(() => bind(el, value, result, modifiers));
        }));
        cleanup(() => {
          el._x_undoAddedClasses && el._x_undoAddedClasses();
          el._x_undoAddedStyles && el._x_undoAddedStyles();
        });
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
      directive2("data", (el, { expression }, { cleanup }) => {
        if (shouldSkipRegisteringDataDuringClone(el))
          return;
        expression = expression === "" ? "{}" : expression;
        let magicContext = {};
        injectMagics(magicContext, el);
        let dataProviderContext = {};
        injectDataProviders(dataProviderContext, magicContext);
        let data2 = evaluate2(el, expression, { scope: dataProviderContext });
        if (data2 === void 0 || data2 === true)
          data2 = {};
        injectMagics(data2, el);
        let reactiveData = reactive(data2);
        initInterceptors(reactiveData);
        let undo = addScopeToNode(el, reactiveData);
        reactiveData["init"] && evaluate2(el, reactiveData["init"]);
        cleanup(() => {
          reactiveData["destroy"] && evaluate2(el, reactiveData["destroy"]);
          undo();
        });
      });
      interceptClone((from, to) => {
        if (from._x_dataStack) {
          to._x_dataStack = from._x_dataStack;
          to.setAttribute("data-has-alpine-state", true);
        }
      });
      function shouldSkipRegisteringDataDuringClone(el) {
        if (!isCloning)
          return false;
        if (isCloningLegacy)
          return true;
        return el.hasAttribute("data-has-alpine-state");
      }
      directive2("show", (el, { modifiers, expression }, { effect: effect3 }) => {
        let evaluate22 = evaluateLater(el, expression);
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
        let toggle = once(
          (value) => value ? show() : hide(),
          (value) => {
            if (typeof el._x_toggleAndCascadeWithTransitions === "function") {
              el._x_toggleAndCascadeWithTransitions(el, value, show, hide);
            } else {
              value ? clickAwayCompatibleShow() : hide();
            }
          }
        );
        let oldValue;
        let firstTime = true;
        effect3(() => evaluate22((value) => {
          if (!firstTime && value === oldValue)
            return;
          if (modifiers.includes("immediate"))
            value ? clickAwayCompatibleShow() : hide();
          toggle(value);
          oldValue = value;
          firstTime = false;
        }));
      });
      directive2("for", (el, { expression }, { effect: effect3, cleanup }) => {
        let iteratorNames = parseForExpression(expression);
        let evaluateItems = evaluateLater(el, iteratorNames.items);
        let evaluateKey = evaluateLater(
          el,
          el._x_keyExpression || "index"
        );
        el._x_prevKeys = [];
        el._x_lookup = {};
        effect3(() => loop(el, iteratorNames, evaluateItems, evaluateKey));
        cleanup(() => {
          Object.values(el._x_lookup).forEach((el2) => mutateDom(
            () => {
              destroyTree(el2);
              el2.remove();
            }
          ));
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
              evaluateKey((value2) => {
                if (keys.includes(value2))
                  warn("Duplicate key on x-for", el);
                keys.push(value2);
              }, { scope: { index: key, ...scope2 } });
              scopes.push(scope2);
            });
          } else {
            for (let i = 0; i < items.length; i++) {
              let scope2 = getIterationScopeVariables(iteratorNames, items[i], i, items);
              evaluateKey((value) => {
                if (keys.includes(value))
                  warn("Duplicate key on x-for", el);
                keys.push(value);
              }, { scope: { index: i, ...scope2 } });
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
            if (!(key in lookup))
              continue;
            mutateDom(() => {
              destroyTree(lookup[key]);
              lookup[key].remove();
            });
            delete lookup[key];
          }
          for (let i = 0; i < moves.length; i++) {
            let [keyInSpot, keyForSpot] = moves[i];
            let elInSpot = lookup[keyInSpot];
            let elForSpot = lookup[keyForSpot];
            let marker = document.createElement("div");
            mutateDom(() => {
              if (!elForSpot)
                warn(`x-for ":key" is undefined or invalid`, templateEl, keyForSpot, lookup);
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
            let [lastKey2, index2] = adds[i];
            let lastEl = lastKey2 === "template" ? templateEl : lookup[lastKey2];
            if (lastEl._x_currentIfEl)
              lastEl = lastEl._x_currentIfEl;
            let scope2 = scopes[index2];
            let key = keys[index2];
            let clone22 = document.importNode(templateEl.content, true).firstElementChild;
            let reactiveScope = reactive(scope2);
            addScopeToNode(clone22, reactiveScope, templateEl);
            clone22._x_refreshXForScope = (newScope) => {
              Object.entries(newScope).forEach(([key2, value]) => {
                reactiveScope[key2] = value;
              });
            };
            mutateDom(() => {
              lastEl.after(clone22);
              skipDuringClone(() => initTree(clone22))();
            });
            if (typeof key === "object") {
              warn("x-for key cannot be an object, it must be a string or an integer", templateEl);
            }
            lookup[key] = clone22;
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
      function getIterationScopeVariables(iteratorNames, item, index2, items) {
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
          scopeVariables[iteratorNames.index] = index2;
        if (iteratorNames.collection)
          scopeVariables[iteratorNames.collection] = items;
        return scopeVariables;
      }
      function isNumeric3(subject) {
        return !Array.isArray(subject) && !isNaN(subject);
      }
      function handler3() {
      }
      handler3.inline = (el, { expression }, { cleanup }) => {
        let root = closestRoot(el);
        if (!root._x_refs)
          root._x_refs = {};
        root._x_refs[expression] = el;
        cleanup(() => delete root._x_refs[expression]);
      };
      directive2("ref", handler3);
      directive2("if", (el, { expression }, { effect: effect3, cleanup }) => {
        if (el.tagName.toLowerCase() !== "template")
          warn("x-if can only be used on a <template> tag", el);
        let evaluate22 = evaluateLater(el, expression);
        let show = () => {
          if (el._x_currentIfEl)
            return el._x_currentIfEl;
          let clone22 = el.content.cloneNode(true).firstElementChild;
          addScopeToNode(clone22, {}, el);
          mutateDom(() => {
            el.after(clone22);
            skipDuringClone(() => initTree(clone22))();
          });
          el._x_currentIfEl = clone22;
          el._x_undoIf = () => {
            mutateDom(() => {
              destroyTree(clone22);
              clone22.remove();
            });
            delete el._x_currentIfEl;
          };
          return clone22;
        };
        let hide = () => {
          if (!el._x_undoIf)
            return;
          el._x_undoIf();
          delete el._x_undoIf;
        };
        effect3(() => evaluate22((value) => {
          value ? show() : hide();
        }));
        cleanup(() => el._x_undoIf && el._x_undoIf());
      });
      directive2("id", (el, { expression }, { evaluate: evaluate22 }) => {
        let names = evaluate22(expression);
        names.forEach((name) => setIdRoot(el, name));
      });
      interceptClone((from, to) => {
        if (from._x_ids) {
          to._x_ids = from._x_ids;
        }
      });
      mapAttributes(startingWith("@", into(prefix("on:"))));
      directive2("on", skipDuringClone((el, { value, modifiers, expression }, { cleanup }) => {
        let evaluate22 = expression ? evaluateLater(el, expression) : () => {
        };
        if (el.tagName.toLowerCase() === "template") {
          if (!el._x_forwardEvents)
            el._x_forwardEvents = [];
          if (!el._x_forwardEvents.includes(value))
            el._x_forwardEvents.push(value);
        }
        let removeListener = on4(el, value, modifiers, (e) => {
          evaluate22(() => {
          }, { scope: { "$event": e }, params: [e] });
        });
        cleanup(() => removeListener());
      }));
      warnMissingPluginDirective("Collapse", "collapse", "collapse");
      warnMissingPluginDirective("Intersect", "intersect", "intersect");
      warnMissingPluginDirective("Focus", "trap", "focus");
      warnMissingPluginDirective("Mask", "mask", "mask");
      function warnMissingPluginDirective(name, directiveName, slug) {
        directive2(directiveName, (el) => warn(`You can't use [x-${directiveName}] without first installing the "${name}" plugin here: https://alpinejs.dev/plugins/${slug}`, el));
      }
      alpine_default.setEvaluator(cspEvaluator);
      alpine_default.setReactivityEngine({ reactive: import_reactivity10.reactive, effect: import_reactivity10.effect, release: import_reactivity10.stop, raw: import_reactivity10.toRaw });
      var src_default10 = alpine_default;
      var module_default10 = src_default10;
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
          n = clamp2(n, Settings.minimum, 1);
          NProgress2.status = n === 1 ? null : n;
          var progress = NProgress2.render(!started), bar = progress.querySelector(Settings.barSelector), speed = Settings.speed, ease = Settings.easing;
          progress.offsetWidth;
          queue(function(next) {
            if (Settings.positionUsing === "")
              Settings.positionUsing = NProgress2.getPositioningCSS();
            css2(bar, barPositionCSS(n, speed, ease));
            if (n === 1) {
              css2(progress, {
                transition: "none",
                opacity: 1
              });
              progress.offsetWidth;
              setTimeout(function() {
                css2(progress, {
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
              amount = (1 - n) * clamp2(Math.random() * n, 0.1, 0.95);
            }
            n = clamp2(n + amount, 0, 0.994);
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
          css2(bar, {
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
        function clamp2(n, min2, max2) {
          if (n < min2)
            return min2;
          if (n > max2)
            return max2;
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
        var css2 = function() {
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

  // js/utils.js
  var Bag = class {
    constructor() {
      this.arrays = {};
    }
    add(key, value) {
      if (!this.arrays[key])
        this.arrays[key] = [];
      this.arrays[key].push(value);
    }
    remove(key) {
      if (this.arrays[key])
        delete this.arrays[key];
    }
    get(key) {
      return this.arrays[key] || [];
    }
    each(key, callback) {
      return this.get(key).forEach(callback);
    }
  };
  var WeakBag = class {
    constructor() {
      this.arrays = /* @__PURE__ */ new WeakMap();
    }
    add(key, value) {
      if (!this.arrays.has(key))
        this.arrays.set(key, []);
      this.arrays.get(key).push(value);
    }
    remove(key) {
      if (this.arrays.has(key))
        this.arrays.delete(key, []);
    }
    get(key) {
      return this.arrays.has(key) ? this.arrays.get(key) : [];
    }
    each(key, callback) {
      return this.get(key).forEach(callback);
    }
  };
  function dispatch(target, name, detail = {}, bubbles = true) {
    target.dispatchEvent(
      new CustomEvent(name, {
        detail,
        bubbles,
        composed: true,
        cancelable: true
      })
    );
  }
  function listen(target, name, handler) {
    target.addEventListener(name, handler);
    return () => target.removeEventListener(name, handler);
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
      return carry?.[i];
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
  function getCsrfToken() {
    if (document.querySelector('meta[name="csrf-token"]')) {
      return document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    }
    if (document.querySelector("[data-csrf]")) {
      return document.querySelector("[data-csrf]").getAttribute("data-csrf");
    }
    if (window.livewireScriptConfig["csrf"] ?? false) {
      return window.livewireScriptConfig["csrf"];
    }
    throw "Livewire: No CSRF token detected";
  }
  var nonce;
  function getNonce() {
    if (nonce)
      return nonce;
    if (window.livewireScriptConfig && (window.livewireScriptConfig["nonce"] ?? false)) {
      nonce = window.livewireScriptConfig["nonce"];
      return nonce;
    }
    let elWithNonce = document.querySelector("style[data-livewire-style][nonce]");
    if (elWithNonce) {
      nonce = elWithNonce.nonce;
      return nonce;
    }
    return null;
  }
  function getUpdateUri() {
    return document.querySelector("[data-update-uri]")?.getAttribute("data-update-uri") ?? window.livewireScriptConfig["uri"] ?? null;
  }
  function contentIsFromDump(content) {
    return !!content.match(/<script>Sfdump\(".+"\)<\/script>/);
  }
  function splitDumpFromContent(content) {
    let dump = content.match(/.*<script>Sfdump\(".+"\)<\/script>/s);
    return [dump, content.replace(dump, "")];
  }
  function walkUpwards(el, callback) {
    let current = el;
    while (current) {
      let stop = void 0;
      callback(current, { stop: (value) => value === void 0 ? stop = current : stop = value });
      if (stop !== void 0)
        return stop;
      if (current._x_teleportBack)
        current = current._x_teleportBack;
      current = current.parentElement;
    }
  }
  function walkBackwards(el, callback) {
    let current = el;
    while (current) {
      let stop = void 0;
      callback(current, { stop: (value) => value === void 0 ? stop = current : stop = value });
      if (stop !== void 0)
        return stop;
      current = current.previousSibling;
    }
  }

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
  function handleFileUpload(el, property, component, cleanup) {
    let manager = getUploadManager(component);
    let start2 = () => el.dispatchEvent(new CustomEvent("livewire-upload-start", { bubbles: true, detail: { id: component.id, property } }));
    let finish = () => el.dispatchEvent(new CustomEvent("livewire-upload-finish", { bubbles: true, detail: { id: component.id, property } }));
    let error2 = () => el.dispatchEvent(new CustomEvent("livewire-upload-error", { bubbles: true, detail: { id: component.id, property } }));
    let cancel = () => el.dispatchEvent(new CustomEvent("livewire-upload-cancel", { bubbles: true, detail: { id: component.id, property } }));
    let progress = (progressEvent) => {
      var percentCompleted = Math.round(progressEvent.loaded * 100 / progressEvent.total);
      el.dispatchEvent(
        new CustomEvent("livewire-upload-progress", {
          bubbles: true,
          detail: { progress: percentCompleted }
        })
      );
    };
    let eventHandler = (e) => {
      if (e.target.files.length === 0)
        return;
      start2();
      if (e.target.multiple) {
        manager.uploadMultiple(property, e.target.files, finish, error2, progress, cancel);
      } else {
        manager.upload(property, e.target.files[0], finish, error2, progress, cancel);
      }
    };
    el.addEventListener("change", eventHandler);
    component.$wire.$watch(property, (value) => {
      if (!el.isConnected)
        return;
      if (value === null || value === "") {
        el.value = "";
      }
      if (el.multiple && Array.isArray(value) && value.length === 0) {
        el.value = "";
      }
    });
    let clearFileInputValue = () => {
      el.value = null;
    };
    el.addEventListener("click", clearFileInputValue);
    el.addEventListener("livewire-upload-cancel", clearFileInputValue);
    cleanup(() => {
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
    upload(name, file, finishCallback, errorCallback, progressCallback, cancelledCallback) {
      this.setUpload(name, {
        files: [file],
        multiple: false,
        finishCallback,
        errorCallback,
        progressCallback,
        cancelledCallback
      });
    }
    uploadMultiple(name, files, finishCallback, errorCallback, progressCallback, cancelledCallback) {
      this.setUpload(name, {
        files: Array.from(files),
        multiple: true,
        finishCallback,
        errorCallback,
        progressCallback,
        cancelledCallback
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
        e.detail.progress = Math.floor(e.loaded * 100 / e.total);
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
      this.uploadBag.first(name).request = request;
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
    cancelUpload(name, cancelledCallback = null) {
      unsetUploadLoading(this.component);
      let uploadItem = this.uploadBag.first(name);
      if (uploadItem) {
        if (uploadItem.request) {
          uploadItem.request.abort();
        }
        this.uploadBag.shift(name).cancelledCallback();
        if (cancelledCallback)
          cancelledCallback();
      }
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
  }, cancelledCallback = () => {
  }) {
    let uploadManager = getUploadManager(component);
    uploadManager.upload(
      name,
      file,
      finishCallback,
      errorCallback,
      progressCallback,
      cancelledCallback
    );
  }
  function uploadMultiple(component, name, files, finishCallback = () => {
  }, errorCallback = () => {
  }, progressCallback = () => {
  }, cancelledCallback = () => {
  }) {
    let uploadManager = getUploadManager(component);
    uploadManager.uploadMultiple(
      name,
      files,
      finishCallback,
      errorCallback,
      progressCallback,
      cancelledCallback
    );
  }
  function removeUpload(component, name, tmpFilename, finishCallback = () => {
  }, errorCallback = () => {
  }) {
    let uploadManager = getUploadManager(component);
    uploadManager.removeUpload(
      name,
      tmpFilename,
      finishCallback,
      errorCallback
    );
  }
  function cancelUpload(component, name, cancelledCallback = () => {
  }) {
    let uploadManager = getUploadManager(component);
    uploadManager.cancelUpload(
      name,
      cancelledCallback
    );
  }

  // js/features/supportEntangle.js
  var import_alpinejs = __toESM(require_module_cjs());
  function generateEntangleFunction(component, cleanup) {
    if (!cleanup)
      cleanup = () => {
      };
    return (name, live = false) => {
      let isLive = live;
      let livewireProperty = name;
      let livewireComponent = component.$wire;
      let livewirePropertyValue = livewireComponent.get(livewireProperty);
      let interceptor = import_alpinejs.default.interceptor((initialValue, getter, setter, path, key) => {
        if (typeof livewirePropertyValue === "undefined") {
          console.error(`Livewire Entangle Error: Livewire property ['${livewireProperty}'] cannot be found on component: ['${component.name}']`);
          return;
        }
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
        cleanup(() => release());
        return cloneIfObject(livewireComponent.get(name));
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
  function cloneIfObject(value) {
    return typeof value === "object" ? JSON.parse(JSON.stringify(value)) : value;
  }

  // js/$wire.js
  var import_alpinejs2 = __toESM(require_module_cjs());

  // js/hooks.js
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
      return runFinishers(finishers, result);
    };
  }
  async function triggerAsync(name, ...params) {
    let callbacks = listeners[name] || [];
    let finishers = [];
    for (let i = 0; i < callbacks.length; i++) {
      let finisher = await callbacks[i](...params);
      if (isFunction(finisher))
        finishers.push(finisher);
    }
    return (result) => {
      return runFinishers(finishers, result);
    };
  }
  function runFinishers(finishers, result) {
    let latest = result;
    for (let i = 0; i < finishers.length; i++) {
      let iResult = finishers[i](latest);
      if (iResult !== void 0) {
        latest = iResult;
      }
    }
    return latest;
  }

  // js/request/interactions.js
  function coordinateNetworkInteractions(messageBus2) {
    interceptPartition(({ message, compileRequest }) => {
      if (!message.component.isIsolated)
        return;
      compileRequest([message]);
    });
    interceptPartition(({ message, compileRequest }) => {
      if (message.component.isLazy && !message.component.hasBeenLazyLoaded && message.component.isLazyIsolated) {
        compileRequest([message]);
      }
    });
    interceptPartition(({ message, compileRequest }) => {
      let component = message.component;
      let bundledMessages = [];
      component.getDeepChildrenWithBindings((child) => {
        let action = constructAction(child, "$commit");
        let message2 = createOrAddToOutstandingMessage(action);
        bundledMessages.push(message2);
      });
      if (bundledMessages.length > 0) {
        compileRequest([message, ...bundledMessages]);
      }
    });
    interceptAction(({ action, reject, defer }) => {
      let isRenderless = action?.origin?.directive?.modifiers.includes("renderless");
      if (isRenderless) {
        action.metadata.renderless = true;
      }
      let message = messageBus2.activeMessageMatchingScope(action);
      if (message) {
        if (message.isAsync() || action.isAsync())
          return;
        if (action.metadata.type === "poll") {
          return reject();
        }
        if (Array.from(message.actions).every((action2) => action2.metadata.type === "poll")) {
          return message.cancel();
        }
        if (Array.from(message.actions).every((action2) => action2.metadata.type === "model.live")) {
          if (action.metadata.type === "model.live") {
            return;
          }
        }
        defer();
        message.addInterceptor(({ onFinish }) => {
          onFinish(() => {
            fireActionInstance(action);
          });
        });
      }
    });
  }

  // js/request/request.js
  var MessageRequest = class {
    messages = /* @__PURE__ */ new Set();
    controller = new AbortController();
    interceptors = [];
    aborted = false;
    uri = null;
    payload = null;
    options = null;
    addMessage(message) {
      message.setRequest(this);
      this.messages.add(message);
    }
    getActiveMessages() {
      return new Set([...this.messages].filter((message) => !message.isCancelled()));
    }
    initInterceptors(interceptorRegistry) {
      this.interceptors = interceptorRegistry.getRequestInterceptors(this);
      this.messages.forEach((message) => {
        let messageInterceptors = interceptorRegistry.getMessageInterceptors(message);
        message.setInterceptors(messageInterceptors);
      });
      this.interceptors.forEach((interceptor) => interceptor.init());
      this.messages.forEach((message) => {
        message.getInterceptors().forEach((interceptor) => interceptor.init());
      });
    }
    abort() {
      if (this.aborted)
        return;
      this.aborted = true;
      this.controller.abort();
      this.messages.forEach((message) => {
        if (message.isCancelled())
          return;
        message.cancel();
      });
    }
    hasAllCancelledMessages() {
      return this.getActiveMessages().size === 0;
    }
    isAborted() {
      return this.aborted;
    }
    onSend({ responsePromise }) {
      this.interceptors.forEach((interceptor) => interceptor.onSend({ responsePromise }));
      this.messages.forEach((message) => message.onSend());
    }
    onAbort() {
      this.interceptors.forEach((interceptor) => interceptor.onAbort());
    }
    onFailure({ error: error2 }) {
      this.interceptors.forEach((interceptor) => interceptor.onFailure({ error: error2 }));
    }
    onResponse({ response }) {
      this.interceptors.forEach((interceptor) => interceptor.onResponse({ response }));
    }
    onStream({ response }) {
      this.interceptors.forEach((interceptor) => interceptor.onStream({ response }));
    }
    onParsed({ response, responseBody }) {
      this.interceptors.forEach((interceptor) => interceptor.onParsed({ response, responseBody }));
    }
    onRedirect({ url, preventDefault }) {
      this.interceptors.forEach((interceptor) => interceptor.onRedirect({ url, preventDefault }));
    }
    onDump({ content, preventDefault }) {
      this.interceptors.forEach((interceptor) => interceptor.onDump({ content, preventDefault }));
    }
    onError({ response, responseBody, preventDefault }) {
      this.interceptors.forEach((interceptor) => interceptor.onError({ response, responseBody, preventDefault }));
      this.messages.forEach((message) => message.onError({ response, responseBody, preventDefault }));
    }
    onSuccess({ response, responseBody, responseJson }) {
      this.interceptors.forEach((interceptor) => interceptor.onSuccess({ response, responseBody, responseJson }));
    }
  };
  var PageRequest = class {
    controller = new AbortController();
    constructor(uri) {
      this.uri = uri;
    }
    cancel() {
      this.controller.abort();
    }
    isCancelled() {
      return this.controller.signal.aborted;
    }
  };

  // js/request/interceptor.js
  var MessageInterceptor = class {
    onSend = () => {
    };
    onCancel = () => {
    };
    onFailure = () => {
    };
    onError = () => {
    };
    onStream = () => {
    };
    onSuccess = () => {
    };
    onFinish = () => {
    };
    onSync = () => {
    };
    onEffect = () => {
    };
    onMorph = () => {
    };
    onRender = () => {
    };
    hasBeenSynchronouslyCancelled = false;
    constructor(message, callback) {
      this.message = message;
      this.callback = callback;
      let isInsideCallbackSynchronously = true;
      this.callback({
        message: this.message,
        actions: this.message.actions,
        component: this.message.component,
        onSend: (callback2) => this.onSend = callback2,
        onCancel: (callback2) => this.onCancel = callback2,
        onFailure: (callback2) => this.onFailure = callback2,
        onError: (callback2) => this.onError = callback2,
        onStream: (callback2) => this.onStream = callback2,
        onSuccess: (callback2) => this.onSuccess = callback2,
        onFinish: (callback2) => this.onFinish = callback2,
        cancel: () => {
          if (isInsideCallbackSynchronously) {
            this.hasBeenSynchronouslyCancelled = true;
          } else {
            this.message.cancel();
          }
        }
      });
      isInsideCallbackSynchronously = false;
    }
    init() {
      if (this.hasBeenSynchronouslyCancelled) {
        this.message.cancel();
      }
    }
  };
  var RequestInterceptor = class {
    onSend = () => {
    };
    onAbort = () => {
    };
    onFailure = () => {
    };
    onResponse = () => {
    };
    onParsed = () => {
    };
    onError = () => {
    };
    onStream = () => {
    };
    onRedirect = () => {
    };
    onDump = () => {
    };
    onSuccess = () => {
    };
    hasBeenSynchronouslyAborted = false;
    constructor(request, callback) {
      this.request = request;
      this.callback = callback;
      let isInsideCallbackSynchronously = true;
      this.callback({
        request: this.request,
        onSend: (callback2) => this.onSend = callback2,
        onAbort: (callback2) => this.onAbort = callback2,
        onFailure: (callback2) => this.onFailure = callback2,
        onResponse: (callback2) => this.onResponse = callback2,
        onParsed: (callback2) => this.onParsed = callback2,
        onError: (callback2) => this.onError = callback2,
        onStream: (callback2) => this.onStream = callback2,
        onRedirect: (callback2) => this.onRedirect = callback2,
        onDump: (callback2) => this.onDump = callback2,
        onSuccess: (callback2) => this.onSuccess = callback2,
        abort: () => {
          if (isInsideCallbackSynchronously) {
            this.hasBeenSynchronouslyAborted = true;
          } else {
            this.request.abort();
          }
        }
      });
      isInsideCallbackSynchronously = false;
    }
    init() {
      if (this.hasBeenSynchronouslyAborted) {
        this.request.abort();
      }
    }
  };
  var InterceptorRegistry = class {
    messageInterceptorCallbacks = [];
    messageInterceptorCallbacksByComponent = new WeakBag();
    requestInterceptorCallbacks = [];
    addInterceptor(component, callback) {
      this.messageInterceptorCallbacksByComponent.add(component, callback);
      return () => {
        this.messageInterceptorCallbacksByComponent.delete(component, callback);
      };
    }
    addMessageInterceptor(callback) {
      this.messageInterceptorCallbacks.push(callback);
      return () => {
        this.messageInterceptorCallbacks.splice(this.messageInterceptorCallbacks.indexOf(callback), 1);
      };
    }
    addRequestInterceptor(callback) {
      this.requestInterceptorCallbacks.push(callback);
      return () => {
        this.requestInterceptorCallbacks.splice(this.requestInterceptorCallbacks.indexOf(callback), 1);
      };
    }
    getMessageInterceptors(message) {
      let callbacks = [
        ...this.messageInterceptorCallbacksByComponent.get(message.component),
        ...this.messageInterceptorCallbacks
      ];
      return callbacks.map((callback) => {
        return new MessageInterceptor(message, callback);
      });
    }
    getRequestInterceptors(request) {
      return this.requestInterceptorCallbacks.map((callback) => {
        return new RequestInterceptor(request, callback);
      });
    }
  };

  // js/utils/modal.js
  function showHtmlModal(html) {
    let page = document.createElement("html");
    page.innerHTML = html;
    page.querySelectorAll("a").forEach(
      (a) => a.setAttribute("target", "_top")
    );
    let modal = document.getElementById("livewire-error");
    if (typeof modal != "undefined" && modal != null) {
      modal.innerHTML = "";
    } else {
      modal = document.createElement("dialog");
      modal.id = "livewire-error";
      modal.style.margin = "50px";
      modal.style.width = "calc(100% - 100px)";
      modal.style.height = "calc(100% - 100px)";
      modal.style.borderRadius = "5px";
      modal.style.padding = "0px";
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
    modal.addEventListener("close", () => cleanupModal(modal));
    modal.showModal();
    modal.focus();
    modal.blur();
  }
  function hideHtmlModal(modal) {
    modal.close();
  }
  function cleanupModal(modal) {
    modal.outerHTML = "";
    document.body.style.overflow = "visible";
  }

  // js/request/messageBus.js
  var componentSymbols = /* @__PURE__ */ new WeakMap();
  var componentIslandSymbols = /* @__PURE__ */ new WeakMap();
  function scopeSymbolFromMessage(message) {
    let component = message.component;
    let hasAllIslands = Array.from(message.actions).every((action) => action.metadata.island);
    if (hasAllIslands) {
      let islandName = Array.from(message.actions).map((action) => action.metadata.island.name).sort().join("|");
      let islandSymbols = componentIslandSymbols.get(component);
      if (!islandSymbols) {
        islandSymbols = { [islandName]: Symbol() };
        componentIslandSymbols.set(component, islandSymbols);
      }
      if (!islandSymbols[islandName]) {
        islandSymbols[islandName] = Symbol();
      }
      return islandSymbols[islandName];
    }
    if (!componentSymbols.has(component)) {
      componentSymbols.set(component, Symbol());
    }
    return componentSymbols.get(component);
  }
  function scopeSymbolFromAction(action) {
    let component = action.component;
    let isIsland = !!action.metadata.island;
    if (isIsland) {
      let islandName = action.metadata.island.name;
      let islandSymbols = componentIslandSymbols.get(component);
      if (!islandSymbols) {
        islandSymbols = { [islandName]: Symbol() };
        componentIslandSymbols.set(component, islandSymbols);
      }
      if (!islandSymbols[islandName]) {
        islandSymbols[islandName] = Symbol();
      }
      return islandSymbols[islandName];
    }
    if (!componentSymbols.has(component)) {
      componentSymbols.set(component, Symbol());
    }
    return componentSymbols.get(component);
  }
  var MessageBus = class {
    pendingMessages = /* @__PURE__ */ new Set();
    activeMessages = /* @__PURE__ */ new Set();
    bufferingMessages = /* @__PURE__ */ new Set();
    constructor() {
    }
    messageBuffer(message, callback) {
      if (this.bufferingMessages.has(message)) {
        return;
      }
      this.bufferingMessages.add(message);
      setTimeout(() => {
        callback();
        this.bufferingMessages.delete(message);
      }, 5);
    }
    addPendingMessage(message) {
      this.pendingMessages.add(message);
    }
    clearPendingMessages() {
      this.pendingMessages.clear();
    }
    getPendingMessages() {
      return Array.from(this.pendingMessages);
    }
    addActiveMessage(message) {
      this.activeMessages.add(message);
    }
    removeActiveMessage(message) {
      this.activeMessages.delete(message);
    }
    findScopedPendingMessage(action) {
      return Array.from(this.pendingMessages).find((message) => message.component === action.component);
    }
    activeMessageMatchingScope(action) {
      return Array.from(this.activeMessages).find((message) => this.matchesScope(message, action));
    }
    matchesScope(message, action) {
      return message.scope === scopeSymbolFromAction(action);
    }
    allScopedMessages(action) {
      return [...Array.from(this.activeMessages), ...Array.from(this.pendingMessages)].filter((message) => {
        return this.matchesScope(message, action);
      });
    }
    eachPendingMessage(callback) {
      Array.from(this.pendingMessages).forEach(callback);
    }
  };

  // js/request/message.js
  var Message = class {
    actions = /* @__PURE__ */ new Set();
    snapshot = null;
    updates = null;
    calls = null;
    payload = null;
    responsePayload = null;
    interceptors = [];
    cancelled = false;
    request = null;
    _scope = null;
    get scope() {
      if (!this._scope) {
        throw new Error("Message scope has not been set yet");
      }
      return this._scope;
    }
    set scope(scope) {
      this._scope = scope;
    }
    constructor(component) {
      this.component = component;
    }
    addAction(action) {
      let actionsByFingerprint = /* @__PURE__ */ new Map();
      Array.from(this.actions).forEach((action2) => {
        actionsByFingerprint.set(action2.fingerprint, action2);
      });
      if (actionsByFingerprint.has(action.fingerprint)) {
        actionsByFingerprint.get(action.fingerprint).addSquashedAction(action);
        return;
      }
      this.actions.add(action);
    }
    getActions() {
      return Array.from(this.actions);
    }
    hasActionForIsland(island) {
      return this.getActions().some((action) => {
        return action.metadata.island?.name === island.metadata.name;
      });
    }
    hasActionForComponent() {
      return this.getActions().some((action) => {
        return action.metadata.island === void 0;
      });
    }
    setInterceptors(interceptors2) {
      this.interceptors = interceptors2;
    }
    addInterceptor(callback) {
      let interceptor = new MessageInterceptor(this, callback);
      this.interceptors.push(interceptor);
      interceptor.init();
    }
    setRequest(request) {
      this.request = request;
    }
    getInterceptors() {
      return this.interceptors;
    }
    cancel() {
      if (this.cancelled)
        return;
      this.cancelled = true;
      this.onCancel();
      if (this.request.hasAllCancelledMessages()) {
        this.request.abort();
      }
    }
    isCancelled() {
      return this.cancelled;
    }
    isAsync() {
      return Array.from(this.actions).every((action) => action.isAsync());
    }
    onSend() {
      this.interceptors.forEach((interceptor) => interceptor.onSend({
        payload: this.payload
      }));
    }
    onCancel() {
      this.interceptors.forEach((interceptor) => interceptor.onCancel());
      this.rejectActionPromises("Request cancelled");
      this.onFinish();
    }
    onFailure(error2) {
      this.interceptors.forEach((interceptor) => interceptor.onFailure({ error: error2 }));
      this.rejectActionPromises("Request failed");
      this.onFinish();
    }
    onError({ response, responseBody, preventDefault }) {
      this.interceptors.forEach((interceptor) => interceptor.onError({
        response,
        responseBody,
        preventDefault
      }));
      this.rejectActionPromises("Request failed");
      this.onFinish();
    }
    onStream({ streamedJson }) {
      this.interceptors.forEach((interceptor) => interceptor.onStream({ streamedJson }));
    }
    onSuccess() {
      this.interceptors.forEach((interceptor) => {
        interceptor.onSuccess({
          payload: this.responsePayload,
          onSync: (callback) => interceptor.onSync = callback,
          onEffect: (callback) => interceptor.onEffect = callback,
          onMorph: (callback) => interceptor.onMorph = callback,
          onRender: (callback) => interceptor.onRender = callback
        });
      });
      let returns = this.responsePayload.effects["returns"] || [];
      this.resolveActionPromises(returns);
      this.onFinish();
    }
    onSync() {
      this.interceptors.forEach((interceptor) => interceptor.onSync());
    }
    onEffect() {
      this.interceptors.forEach((interceptor) => interceptor.onEffect());
    }
    onMorph() {
      this.interceptors.forEach((interceptor) => interceptor.onMorph());
    }
    onRender() {
      this.interceptors.forEach((interceptor) => interceptor.onRender());
    }
    onFinish() {
      this.interceptors.forEach((interceptor) => interceptor.onFinish());
    }
    rejectActionPromises(error2) {
      Array.from(this.actions).forEach((action) => {
        action.rejectPromise(error2);
      });
    }
    resolveActionPromises(returns) {
      let resolvedActions = /* @__PURE__ */ new Set();
      returns.forEach((value, index2) => {
        let action = Array.from(this.actions)[index2];
        if (!action)
          return;
        action.resolvePromise(value);
        resolvedActions.add(action);
      });
      Array.from(this.actions).forEach((action) => {
        if (resolvedActions.has(action))
          return;
        action.resolvePromise();
      });
    }
  };

  // js/request/action.js
  var Action = class {
    handleReturn = () => {
    };
    squashedActions = /* @__PURE__ */ new Set();
    constructor(component, method, params = [], metadata = {}, origin = null) {
      this.component = component;
      this.method = method;
      this.params = params;
      this.metadata = metadata;
      this.origin = origin;
      this.promise = new Promise((resolve, reject) => {
        this.promiseResolution = { resolve, reject };
      });
    }
    get fingerprint() {
      let componentId = this.component.id;
      let method = this.method;
      let params = JSON.stringify(this.params);
      let metadata = JSON.stringify(this.metadata);
      return window.btoa(String.fromCharCode(...new TextEncoder().encode(componentId + method + params + metadata)));
    }
    isAsync() {
      let asyncMethods = this.component.snapshot.memo?.async || [];
      let methodIsMarkedAsync = asyncMethods.includes(this.method);
      let actionIsAsync = this.origin?.directive?.modifiers.includes("async");
      return methodIsMarkedAsync || actionIsAsync;
    }
    mergeMetadata(metadata) {
      this.metadata = { ...this.metadata, ...metadata };
    }
    rejectPromise(error2) {
      this.squashedActions.forEach((action) => action.rejectPromise(error2));
      this.promiseResolution.resolve();
    }
    addSquashedAction(action) {
      this.squashedActions.add(action);
    }
    resolvePromise(value) {
      this.squashedActions.forEach((action) => action.resolvePromise(value));
      this.promiseResolution.resolve(value);
    }
  };

  // js/request/index.js
  var outstandingActionOrigin = null;
  var outstandingActionMetadata = {};
  var interceptors = new InterceptorRegistry();
  var messageBus = new MessageBus();
  var actionInterceptors = [];
  var partitionInterceptors = [];
  function setNextActionOrigin(origin) {
    outstandingActionOrigin = origin;
  }
  function setNextActionMetadata(metadata) {
    outstandingActionMetadata = metadata;
  }
  function intercept(component, callback) {
    return interceptors.addInterceptor(component, callback);
  }
  function interceptAction(callback) {
    actionInterceptors.push(callback);
    return () => {
      actionInterceptors.splice(actionInterceptors.indexOf(callback), 1);
    };
  }
  function interceptPartition(callback) {
    partitionInterceptors.push(callback);
    return () => {
      partitionInterceptors.splice(partitionInterceptors.indexOf(callback), 1);
    };
  }
  function interceptMessage(callback) {
    return interceptors.addMessageInterceptor(callback);
  }
  function interceptRequest(callback) {
    return interceptors.addRequestInterceptor(callback);
  }
  interceptMessage(({ message, onFinish }) => {
    messageBus.addActiveMessage(message);
    onFinish(() => messageBus.removeActiveMessage(message));
  });
  queueMicrotask(() => {
    coordinateNetworkInteractions(messageBus);
  });
  function fireAction(component, method, params = [], metadata = {}) {
    let action = constructAction(component, method, params, metadata);
    let prevented = false;
    actionInterceptors.forEach((callback) => {
      callback({
        action,
        reject: () => {
          action.rejectPromise();
          prevented = true;
        },
        defer: () => prevented = true
      });
    });
    if (prevented)
      return action.promise;
    return fireActionInstance(action);
  }
  function constructAction(component, method, params, metadata) {
    let origin = outstandingActionOrigin;
    outstandingActionOrigin = null;
    metadata = {
      ...metadata,
      ...outstandingActionMetadata
    };
    outstandingActionMetadata = {};
    return new Action(component, method, params, metadata, origin);
  }
  function fireActionInstance(action) {
    let message = createOrAddToOutstandingMessage(action);
    messageBus.messageBuffer(message, () => {
      sendMessages();
    });
    return action.promise;
  }
  function createOrAddToOutstandingMessage(action) {
    let message = messageBus.findScopedPendingMessage(action);
    if (!message)
      message = new Message(action.component);
    message.addAction(action);
    messageBus.addPendingMessage(message);
    return message;
  }
  function sendMessages() {
    let requests = /* @__PURE__ */ new Set();
    messageBus.eachPendingMessage((message) => {
      partitionInterceptors.forEach((callback) => {
        callback({
          message,
          compileRequest: (messages2) => {
            if (Array.from(requests).some((request2) => Array.from(request2.messages).some((message2) => messages2.includes(message2)))) {
              throw new Error("A request already contains one of the messages in this array");
            }
            let request = new MessageRequest();
            messages2.forEach((message2) => request.addMessage(message2));
            requests.add(request);
            return request;
          }
        });
      });
    });
    let messages = messageBus.getPendingMessages();
    messageBus.clearPendingMessages();
    for (let message of messages) {
      if (Array.from(requests).some((request) => request.messages.has(message))) {
        continue;
      }
      let hasFoundRequest = false;
      requests.forEach((request) => {
        if (!hasFoundRequest) {
          request.addMessage(message);
          hasFoundRequest = true;
        }
      });
      if (!hasFoundRequest) {
        let request = new MessageRequest();
        request.addMessage(message);
        requests.add(request);
      }
    }
    requests.forEach((request) => {
      request.messages.forEach((message) => {
        message.snapshot = message.component.getEncodedSnapshotWithLatestChildrenMergedIn();
        message.updates = message.component.getUpdates();
        message.calls = Array.from(message.actions).map((i) => ({
          method: i.method,
          params: i.params,
          metadata: i.metadata
        }));
        message.payload = {
          snapshot: message.snapshot,
          updates: message.updates,
          calls: message.calls
        };
      });
    });
    requests.forEach((request) => {
      request.messages.forEach((message) => {
        message.scope = scopeSymbolFromMessage(message);
      });
    });
    requests.forEach((request) => {
      request.uri = getUpdateUri();
      Object.defineProperty(request, "payload", {
        get() {
          return {
            _token: getCsrfToken(),
            components: Array.from(request.messages, (i) => i.payload)
          };
        }
      });
      Object.defineProperty(request, "options", {
        get() {
          return {
            method: "POST",
            body: JSON.stringify(request.payload),
            headers: {
              "Content-type": "application/json",
              "X-Livewire": "1"
            },
            signal: request.controller.signal
          };
        }
      });
    });
    requests.forEach((request) => {
      request.initInterceptors(interceptors);
      if (request.hasAllCancelledMessages()) {
        request.abort();
      }
      sendRequest(request, {
        send: ({ responsePromise }) => {
          request.onSend({ responsePromise });
        },
        failure: ({ error: error2 }) => {
          request.onFailure({ error: error2 });
        },
        response: ({ response }) => {
          request.onResponse({ response });
        },
        stream: async ({ response }) => {
          request.onStream({ response });
          let finalResponse = "";
          try {
            finalResponse = await interceptStreamAndReturnFinalResponse(response, (streamedJson) => {
              let componentId = streamedJson.id;
              request.messages.forEach((message) => {
                if (message.component.id === componentId) {
                  message.onStream({ streamedJson });
                }
              });
              trigger("stream", streamedJson);
            });
          } catch (e) {
            request.abort();
            throw e;
          }
          return finalResponse;
        },
        parsed: ({ response, responseBody }) => {
          request.onParsed({ response, responseBody });
        },
        error: ({ response, responseBody }) => {
          let preventDefault = false;
          request.onError({ response, responseBody, preventDefault: () => preventDefault = true });
          if (preventDefault)
            return;
          if (response.status === 419) {
            confirm(
              "This page has expired.\nWould you like to refresh the page?"
            ) && window.location.reload();
          }
          if (response.aborted)
            return;
          showHtmlModal(responseBody);
        },
        redirect: (url) => {
          let preventDefault = false;
          request.onRedirect({ url, preventDefault: () => preventDefault = true });
          if (preventDefault)
            return;
          window.location.href = url;
        },
        dump: (content) => {
          let preventDefault = false;
          request.onDump({ content, preventDefault: () => preventDefault = true });
          if (preventDefault)
            return;
          showHtmlModal(content);
        },
        success: async ({ response, responseBody, responseJson }) => {
          request.onSuccess({ response, responseBody, responseJson });
          await triggerAsync("payload.intercept", responseJson);
          let messageResponsePayloads = responseJson.components;
          request.messages.forEach((message) => {
            messageResponsePayloads.forEach((payload) => {
              if (message.isCancelled())
                return;
              let { snapshot: snapshotEncoded, effects } = payload;
              let snapshot = JSON.parse(snapshotEncoded);
              if (snapshot.memo.id === message.component.id) {
                message.responsePayload = { snapshot, effects };
                message.onSuccess();
                if (message.isCancelled())
                  return;
                message.component.mergeNewSnapshot(snapshotEncoded, effects, message.updates);
                message.onSync();
                if (message.isCancelled())
                  return;
                message.component.processEffects(effects, request);
                message.onEffect();
                if (message.isCancelled())
                  return;
                queueMicrotask(() => {
                  if (message.isCancelled())
                    return;
                  message.onMorph();
                  setTimeout(() => {
                    if (message.isCancelled())
                      return;
                    message.onRender();
                  });
                });
              }
            });
          });
        }
      });
    });
  }
  async function sendRequest(request, handlers) {
    let response;
    try {
      if (request.isAborted())
        return;
      let responsePromise = fetch(request.uri, request.options);
      if (request.isAborted())
        return;
      handlers.send({ responsePromise });
      response = await responsePromise;
    } catch (e) {
      if (request.isAborted())
        return;
      handlers.failure({ error: e });
      return;
    }
    handlers.response({ response });
    let responseBody = null;
    if (response.headers.has("X-Livewire-Stream")) {
      responseBody = await handlers.stream({ response });
    } else {
      responseBody = await response.text();
    }
    if (request.isAborted())
      return;
    handlers.parsed({ response, responseBody });
    if (!response.ok) {
      handlers.error({ response, responseBody });
      return;
    }
    if (response.redirected) {
      handlers.redirect(response.url);
    }
    if (contentIsFromDump(responseBody)) {
      let dump;
      [dump, responseBody] = splitDumpFromContent(responseBody);
      handlers.dump(dump);
    }
    let responseJson = JSON.parse(responseBody);
    handlers.success({ response, responseBody, responseJson });
  }
  async function interceptStreamAndReturnFinalResponse(response, callback) {
    let reader = response.body.getReader();
    let remainingResponse = "";
    while (true) {
      let { done, value: chunk } = await reader.read();
      let decoder = new TextDecoder();
      let output = decoder.decode(chunk);
      let [streams, remaining] = extractStreamObjects(remainingResponse + output);
      streams.forEach((stream) => {
        callback(stream);
      });
      remainingResponse = remaining;
      if (done)
        return remainingResponse;
    }
  }
  function extractStreamObjects(raw) {
    let regex = /({"stream":true.*?"endStream":true})/g;
    let matches3 = raw.match(regex);
    let parsed = [];
    if (matches3) {
      for (let i = 0; i < matches3.length; i++) {
        parsed.push(JSON.parse(matches3[i]).body);
      }
    }
    let remaining = raw.replace(regex, "");
    return [parsed, remaining];
  }
  async function sendNavigateRequest(uri, callback, errorCallback) {
    let request = new PageRequest(uri);
    let options = {
      headers: {
        "X-Livewire-Navigate": "1"
      },
      signal: request.controller.signal
    };
    trigger("navigate.request", {
      uri,
      options
    });
    let response;
    try {
      response = await fetch(uri, options);
      let destination = getDestination(uri, response);
      let html = await response.text();
      callback(html, destination);
    } catch (error2) {
      errorCallback(error2);
      throw error2;
    }
  }
  function getDestination(uri, response) {
    let destination = createUrlObjectFromString(uri);
    let finalDestination = createUrlObjectFromString(response.url);
    if (destination.pathname + destination.search === finalDestination.pathname + finalDestination.search) {
      finalDestination.hash = destination.hash;
    }
    return finalDestination;
  }
  function createUrlObjectFromString(urlString) {
    return urlString !== null && new URL(urlString, document.baseURI);
  }
  interceptRequest(({
    request,
    onFailure,
    onResponse,
    onError,
    onSuccess
  }) => {
    let respondCallbacks = [];
    let succeedCallbacks = [];
    let failCallbacks = [];
    trigger("request", {
      url: request.uri,
      options: request.options,
      payload: request.options.body,
      respond: (i) => respondCallbacks.push(i),
      succeed: (i) => succeedCallbacks.push(i),
      fail: (i) => failCallbacks.push(i)
    });
    onResponse(({ response }) => {
      respondCallbacks.forEach((callback) => callback({
        status: response.status,
        response
      }));
    });
    onSuccess(({ response, responseJson }) => {
      succeedCallbacks.forEach((callback) => callback({
        status: response.status,
        json: responseJson
      }));
    });
    onFailure(({ error: error2 }) => {
      failCallbacks.forEach((callback) => callback({
        status: 503,
        content: null,
        preventDefault: () => {
        }
      }));
    });
    onError(({ response, responseBody, preventDefault }) => {
      failCallbacks.forEach((callback) => callback({
        status: response.status,
        content: responseBody,
        preventDefault
      }));
    });
  });
  interceptMessage(({
    message,
    onCancel,
    onError,
    onSuccess,
    onFinish
  }) => {
    let respondCallbacks = [];
    let succeedCallbacks = [];
    let failCallbacks = [];
    trigger("commit", {
      component: message.component,
      commit: message.payload,
      respond: (callback) => {
        respondCallbacks.push(callback);
      },
      succeed: (callback) => {
        succeedCallbacks.push(callback);
      },
      fail: (callback) => {
        failCallbacks.push(callback);
      }
    });
    onFinish(() => {
      respondCallbacks.forEach((callback) => callback());
    });
    onSuccess(({ payload, onSync, onMorph, onRender }) => {
      onRender(() => {
        succeedCallbacks.forEach((callback) => callback({
          snapshot: payload.snapshot,
          effects: payload.effects
        }));
      });
    });
    onError(() => {
      failCallbacks.forEach((callback) => callback());
    });
    onCancel(() => {
      failCallbacks.forEach((callback) => callback());
    });
  });

  // js/features/supportErrors.js
  function getErrorsObject(component) {
    return {
      messages() {
        return component.snapshot.memo.errors;
      },
      keys() {
        return Object.keys(this.messages());
      },
      has(...keys) {
        if (this.isEmpty())
          return false;
        if (keys.length === 0 || keys.length === 1 && keys[0] == null)
          return this.any();
        if (keys.length === 1 && Array.isArray(keys[0]))
          keys = keys[0];
        for (let key of keys) {
          if (this.first(key) === "")
            return false;
        }
        return true;
      },
      hasAny(keys) {
        if (this.isEmpty())
          return false;
        if (keys.length === 1 && Array.isArray(keys[0]))
          keys = keys[0];
        for (let key of keys) {
          if (this.has(key))
            return true;
        }
        return false;
      },
      missing(...keys) {
        if (keys.length === 1 && Array.isArray(keys[0]))
          keys = keys[0];
        return !this.hasAny(keys);
      },
      first(key = null) {
        let messages = key === null ? this.all() : this.get(key);
        let firstMessage = messages.length > 0 ? messages[0] : "";
        return Array.isArray(firstMessage) ? firstMessage[0] : firstMessage;
      },
      get(key) {
        return component.snapshot.memo.errors[key] || [];
      },
      all() {
        return Object.values(this.messages()).flat();
      },
      isEmpty() {
        return !this.any();
      },
      isNotEmpty() {
        return this.any();
      },
      any() {
        return Object.keys(this.messages()).length > 0;
      },
      count() {
        return Object.values(this.messages()).reduce((total, array) => {
          return total + array.length;
        }, 0);
      }
    };
  }

  // js/features/supportRefs.js
  function findRefEl(component, name) {
    let refEl = component.el.querySelector(`[wire\\:ref="${name}"]`);
    if (!refEl)
      return console.error(`Ref "${name}" not found in component "${component.id}"`);
    return refEl;
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
    "el": "$el",
    "id": "$id",
    "js": "$js",
    "get": "$get",
    "set": "$set",
    "refs": "$refs",
    "call": "$call",
    "hook": "$hook",
    "watch": "$watch",
    "commit": "$commit",
    "errors": "$errors",
    "island": "$island",
    "upload": "$upload",
    "entangle": "$entangle",
    "dispatch": "$dispatch",
    "intercept": "$intercept",
    "dispatchTo": "$dispatchTo",
    "dispatchSelf": "$dispatchSelf",
    "removeUpload": "$removeUpload",
    "cancelUpload": "$cancelUpload",
    "uploadMultiple": "$uploadMultiple"
  };
  function generateWireObject(component, state) {
    let isScoped = false;
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
  import_alpinejs2.default.magic("wire", (el, { cleanup }) => {
    let component;
    return new Proxy({}, {
      get(target, property) {
        if (!component)
          component = findComponentByEl(el);
        if (["$entangle", "entangle"].includes(property)) {
          return generateEntangleFunction(component, cleanup);
        }
        return component.$wire[property];
      },
      set(target, property, value) {
        if (!component)
          component = findComponentByEl(el);
        component.$wire[property] = value;
        return true;
      }
    });
  });
  wireProperty("__instance", (component) => component);
  wireProperty("$get", (component) => (property, reactive = true) => dataGet(reactive ? component.reactive : component.ephemeral, property));
  wireProperty("$el", (component) => {
    return component.el;
  });
  wireProperty("$id", (component) => {
    return component.id;
  });
  wireProperty("$js", (component) => {
    let fn = component.addJsAction.bind(component);
    let jsActions = component.getJsActions();
    Object.keys(jsActions).forEach((name) => {
      fn[name] = component.getJsAction(name);
    });
    return new Proxy(fn, {
      set(target, property, value) {
        component.addJsAction(property, value);
        return true;
      }
    });
  });
  wireProperty("$set", (component) => async (property, value, live = true) => {
    dataSet(component.reactive, property, value);
    if (live) {
      component.queueUpdate(property, value);
      return fireAction(component, "$set");
    }
    return Promise.resolve();
  });
  wireProperty("$refs", (component) => {
    let fn = (name) => findRefEl(component, name);
    return new Proxy(fn, {
      get(target, property) {
        if (property in target) {
          return target[property];
        }
        return fn(property);
      }
    });
  });
  wireProperty("$intercept", (component) => (method, callback = null) => {
    if (callback === null && typeof method === "function") {
      callback = method;
      return intercept(component, callback);
    }
    return intercept(component, (options) => {
      let action = options.message.getActions().find((action2) => action2.method === method);
      if (action) {
        let el = action?.origin?.el;
        callback({
          ...options,
          el
        });
      }
    });
  });
  wireProperty("$errors", (component) => getErrorsObject(component));
  wireProperty("$call", (component) => async (method, ...params) => {
    return await component.$wire[method](...params);
  });
  wireProperty("$island", (component) => async (name, options = {}) => {
    return fireAction(component, "$refresh", [], {
      island: { name, ...options }
    });
  });
  wireProperty("$entangle", (component) => (name, live = false) => {
    return generateEntangleFunction(component)(name, live);
  });
  wireProperty("$toggle", (component) => (name, live = true) => {
    return component.$wire.set(name, !component.$wire.get(name), live);
  });
  wireProperty("$watch", (component) => (path, callback) => {
    let getter = () => {
      return dataGet(component.reactive, path);
    };
    let unwatch = import_alpinejs2.default.watch(getter, callback);
    component.addCleanup(unwatch);
    return unwatch;
  });
  wireProperty("$refresh", (component) => async () => {
    return fireAction(component, "$refresh");
  });
  wireProperty("$commit", (component) => async () => {
    return fireAction(component, "$commit");
  });
  wireProperty("$on", (component) => (...params) => listen2(component, ...params));
  wireProperty("$hook", (component) => (name, callback) => {
    let unhook = on(name, ({ component: hookComponent, ...params }) => {
      if (hookComponent === void 0)
        return callback(params);
      if (hookComponent.id === component.id)
        return callback({ component: hookComponent, ...params });
    });
    component.addCleanup(unhook);
    return unhook;
  });
  wireProperty("$dispatch", (component) => (...params) => dispatch2(component, ...params));
  wireProperty("$dispatchSelf", (component) => (...params) => dispatchSelf(component, ...params));
  wireProperty("$dispatchTo", () => (...params) => dispatchTo(...params));
  wireProperty("$upload", (component) => (...params) => upload(component, ...params));
  wireProperty("$uploadMultiple", (component) => (...params) => uploadMultiple(component, ...params));
  wireProperty("$removeUpload", (component) => (...params) => removeUpload(component, ...params));
  wireProperty("$cancelUpload", (component) => (...params) => cancelUpload(component, ...params));
  var parentMemo = /* @__PURE__ */ new WeakMap();
  wireProperty("$parent", (component) => {
    if (parentMemo.has(component))
      return parentMemo.get(component).$wire;
    let parent = component.parent;
    parentMemo.set(component, parent);
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
    return fireAction(component, property, params);
  });

  // js/component.js
  var Component = class {
    constructor(el) {
      if (el.__livewire)
        throw "Component already initialized";
      el.__livewire = this;
      this.el = el;
      this.id = el.getAttribute("wire:id");
      this.key = el.getAttribute("wire:key");
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
      this.queuedUpdates = {};
      this.jsActions = {};
      this.$wire = generateWireObject(this, this.reactive);
      el.$wire = this.$wire;
      this.cleanups = [];
      this.processEffects(this.effects);
    }
    addActionContext(context) {
      if (context.el || context.directive) {
        setNextActionOrigin({
          el: context.el,
          directive: context.directive
        });
      }
    }
    intercept(action, callback = null) {
      return this.$wire.$intercept(action, callback);
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
    queueUpdate(propertyName, value) {
      this.queuedUpdates[propertyName] = value;
    }
    mergeQueuedUpdates(diff2) {
      Object.entries(this.queuedUpdates).forEach(([updateKey, updateValue]) => {
        Object.entries(diff2).forEach(([diffKey, diffValue]) => {
          if (diffKey.startsWith(updateValue)) {
            delete diff2[diffKey];
          }
        });
        diff2[updateKey] = updateValue;
      });
      this.queuedUpdates = [];
      return diff2;
    }
    getUpdates() {
      let propertiesDiff = diff(this.canonical, this.ephemeral);
      return this.mergeQueuedUpdates(propertiesDiff);
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
      this.processEffects({ html });
    }
    processEffects(effects, request) {
      trigger("effects", this, effects);
      trigger("effect", {
        component: this,
        effects,
        cleanup: (i) => this.addCleanup(i),
        request
      });
    }
    get children() {
      let componentEl = this.el;
      let children = [];
      componentEl.querySelectorAll("[wire\\:id]").forEach((el) => {
        let parentComponentEl = el.parentElement.closest("[wire\\:id]");
        if (parentComponentEl !== componentEl)
          return;
        let componentInstance = el.__livewire;
        if (!componentInstance)
          return;
        children.push(componentInstance);
      });
      return children;
    }
    get islands() {
      let islands = this.snapshot.memo.islands;
      return islands;
    }
    get parent() {
      return findComponentByEl(this.el.parentElement);
    }
    get isIsolated() {
      return this.snapshot.memo.isolate;
    }
    get isLazy() {
      return this.snapshot.memo.lazyLoaded !== void 0;
    }
    get hasBeenLazyLoaded() {
      return this.snapshot.memo.lazyLoaded === true;
    }
    get isLazyIsolated() {
      return !!this.snapshot.memo.lazyIsolated;
    }
    getDeepChildrenWithBindings(callback) {
      this.getDeepChildren((child) => {
        if (child.hasReactiveProps() || child.hasWireModelableBindings()) {
          callback(child);
        }
      });
    }
    hasReactiveProps() {
      let meta = this.snapshot.memo;
      let props = meta.props;
      return !!props;
    }
    hasWireModelableBindings() {
      let meta = this.snapshot.memo;
      let bindings = meta.bindings;
      return !!bindings;
    }
    getDeepChildren(callback) {
      this.children.forEach((child) => {
        callback(child);
        child.getDeepChildren(callback);
      });
    }
    getEncodedSnapshotWithLatestChildrenMergedIn() {
      let { snapshotEncoded, children, snapshot } = this;
      let childrenMemo = {};
      children.forEach((child) => {
        childrenMemo[child.key] = [child.el.tagName.toLowerCase(), child.id];
      });
      return snapshotEncoded.replace(
        /"children":\{[^}]*\}/,
        `"children":${JSON.stringify(childrenMemo)}`
      );
    }
    inscribeSnapshotAndEffectsOnElement() {
      let el = this.el;
      el.setAttribute("wire:snapshot", this.snapshotEncoded);
      let effects = this.originalEffects.listeners ? { listeners: this.originalEffects.listeners } : {};
      if (this.originalEffects.url) {
        effects.url = this.originalEffects.url;
      }
      if (this.originalEffects.scripts) {
        effects.scripts = this.originalEffects.scripts;
      }
      el.setAttribute("wire:effects", JSON.stringify(effects));
      el.setAttribute("wire:key", this.key);
    }
    addJsAction(name, action) {
      this.jsActions[name] = action;
    }
    hasJsAction(name) {
      return this.jsActions[name] !== void 0;
    }
    getJsAction(name) {
      return this.jsActions[name].bind(this.$wire);
    }
    getJsActions() {
      return this.jsActions;
    }
    addCleanup(cleanup) {
      this.cleanups.push(cleanup);
    }
    cleanup() {
      delete this.el.__livewire;
      while (this.cleanups.length > 0) {
        this.cleanups.pop()();
      }
    }
  };

  // js/fragment.js
  function closestFragment(el, { isMatch, hasReachedBoundary }) {
    if (!hasReachedBoundary)
      hasReachedBoundary = () => false;
    let current = el;
    while (current) {
      let sibling = current.previousSibling;
      let foundEndMarker = [];
      while (sibling) {
        if (isEndFragmentMarker(sibling)) {
          foundEndMarker.push("a");
        }
        if (isStartFragmentMarker(sibling)) {
          if (foundEndMarker.length > 0) {
            foundEndMarker.pop();
          } else {
            let metadata = extractFragmentMetadataFromMarkerNode(sibling);
            if (isMatch(metadata)) {
              return new Fragment(sibling);
            }
          }
        }
        sibling = sibling.previousSibling;
      }
      current = current.parentElement;
      if (current && hasReachedBoundary({ el: current })) {
        break;
      }
    }
    return null;
  }
  function findFragment(el, { isMatch, hasReachedBoundary }) {
    if (!hasReachedBoundary)
      hasReachedBoundary = () => false;
    let startNode = null;
    let rootEl2 = el;
    walkElements(rootEl2, (el2, { skip, stop }) => {
      if (el2.hasAttribute && el2 !== rootEl2 && hasReachedBoundary({ el: el2 })) {
        return skip();
      }
      Array.from(el2.childNodes).forEach((node) => {
        if (isStartFragmentMarker(node)) {
          let metadata = extractFragmentMetadataFromMarkerNode(node);
          if (isMatch(metadata)) {
            startNode = node;
            stop();
          }
        }
      });
    });
    return startNode && new Fragment(startNode);
  }
  function isStartFragmentMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith("[if FRAGMENT");
  }
  function isEndFragmentMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith("[if ENDFRAGMENT");
  }
  function walkElements(el, callback) {
    let skip = false;
    let stop = false;
    callback(el, { skip: () => skip = true, stop: () => stop = true });
    if (skip || stop)
      return;
    Array.from(el.children).forEach((child) => {
      walkElements(child, callback);
      if (stop)
        return;
    });
  }
  var Fragment = class {
    constructor(startMarkerNode) {
      this.startMarkerNode = startMarkerNode;
      this.metadata = extractFragmentMetadataFromMarkerNode(startMarkerNode);
    }
    get endMarkerNode() {
      return findMatchingEndMarkerNode(this.startMarkerNode, this.metadata);
    }
    append(mountContainerTagName, html) {
      let container = document.createElement(mountContainerTagName);
      container.innerHTML = html;
      Array.from(container.childNodes).forEach((node) => {
        this.endMarkerNode.before(node);
      });
    }
    prepend(mountContainerTagName, html) {
      let container = document.createElement(mountContainerTagName);
      container.innerHTML = html;
      Array.from(container.childNodes).reverse().forEach((node) => {
        this.startMarkerNode.after(node);
      });
    }
  };
  function findMatchingEndMarkerNode(startMarkerNode, metadata) {
    let current = startMarkerNode;
    while (current) {
      if (isEndFragmentMarker(current)) {
        let currentMetadata = extractFragmentMetadataFromMarkerNode(current);
        if (Object.keys(metadata).every((key) => metadata[key] === currentMetadata[key])) {
          return current;
        }
      }
      current = current.nextSibling;
    }
    return null;
  }
  function extractInnerHtmlFromFragmentHtml(fragmentHtml) {
    let regex = /<!--\[if FRAGMENT\b.*?\]><!\[endif\]-->([\s\S]*)<!--\[if ENDFRAGMENT\b.*?\]><!\[endif\]-->/i;
    let match = fragmentHtml.match(regex);
    if (!match)
      throw new Error("Invalid fragment marker");
    let [_, html] = match;
    return html;
  }
  function extractFragmentMetadataFromHtml(fragmentHtml) {
    let regex = /\[if (FRAGMENT|ENDFRAGMENT):(.*?)\]/;
    let match = fragmentHtml.match(regex);
    if (!match)
      throw new Error("Invalid fragment marker");
    let [_, __, encodedMetadata] = match;
    return decodeMetadata(encodedMetadata);
  }
  function extractFragmentMetadataFromMarkerNode(startMarkerNode) {
    let regex = /\[if (FRAGMENT|ENDFRAGMENT):(.*?)\]/;
    let match = startMarkerNode.textContent.match(regex);
    if (!match)
      throw new Error("Invalid fragment marker");
    let [_, __, encodedMetadata] = match;
    return decodeMetadata(encodedMetadata);
  }
  function decodeMetadata(encodedMetadata) {
    let metadata = {};
    let pairs = encodedMetadata.split("|");
    pairs.forEach((pair) => {
      let [key, value] = pair.split("=");
      metadata[key] = value;
    });
    return metadata;
  }

  // js/store.js
  var components = {};
  function initComponent(el) {
    let component = new Component(el);
    if (components[component.id])
      throw "Component already registered";
    let cleanup = (i) => component.addCleanup(i);
    trigger("component.init", { component, cleanup });
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
  function hasComponent(id) {
    return !!components[id];
  }
  function findComponent(id, strict = true) {
    let component = components[id];
    if (!component) {
      if (strict)
        throw "Component not found: " + id;
      return;
    }
    return component;
  }
  function findComponentByEl(el, strict = true) {
    let componentId = walkUpwards(el, (node, { stop }) => {
      if (node.__livewire)
        return stop(node.__livewire.id);
      let endMarkers = [];
      let slotParentId = walkBackwards(node, (siblingNode, { stop: stop2 }) => {
        if (isEndFragmentMarker(siblingNode)) {
          let metadata = extractFragmentMetadataFromMarkerNode(siblingNode);
          if (metadata.type !== "slot")
            return;
          endMarkers.push("a");
          return;
        }
        if (isStartFragmentMarker(siblingNode)) {
          let metadata = extractFragmentMetadataFromMarkerNode(siblingNode);
          if (metadata.type !== "slot")
            return;
          if (endMarkers.length > 0) {
            endMarkers.pop();
          } else {
            return stop2(metadata.parent);
          }
        }
      });
      if (slotParentId)
        return stop(slotParentId);
    });
    let component = findComponent(componentId, strict);
    if (!component) {
      if (strict)
        throw "Could not find Livewire component in DOM tree";
      return;
    }
    return component;
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

  // js/events.js
  function dispatch2(component, name, params) {
    dispatchEvent(component.el, name, params);
  }
  function dispatchGlobal(name, params) {
    dispatchEvent(window, name, params);
  }
  function dispatchSelf(component, name, params) {
    dispatchEvent(component.el, name, params, false);
  }
  function dispatchEl(component, selector, name, params) {
    let targets = component.el.querySelectorAll(selector);
    targets.forEach((target) => {
      dispatchEvent(target, name, params, false);
    });
  }
  function dispatchTo(componentName, name, params) {
    let targets = componentsByName(componentName);
    targets.forEach((target) => {
      dispatchEvent(target.el, name, params, false);
    });
  }
  function dispatchRef(component, ref, name, params) {
    let el = findRefEl(component, ref);
    dispatchEvent(el, name, params, false);
  }
  function listen2(component, name, callback) {
    component.el.addEventListener(name, (e) => {
      callback(e.detail);
    });
  }
  function on2(eventName, callback) {
    let handler = (e) => {
      if (!e.__livewire)
        return;
      callback(e.detail);
    };
    window.addEventListener(eventName, handler);
    return () => {
      window.removeEventListener(eventName, handler);
    };
  }
  function dispatchEvent(target, name, params, bubbles = true) {
    if (typeof params === "string") {
      params = [params];
    }
    let e = new CustomEvent(name, { bubbles, detail: params });
    e.__livewire = { name, params, receivedBy: [] };
    target.dispatchEvent(e);
  }

  // js/directives.js
  var customDirectiveNames = /* @__PURE__ */ new Set();
  function matchesForLivewireDirective(attributeName) {
    return attributeName.match(new RegExp("wire:"));
  }
  function extractDirective(el, name) {
    let [value, ...modifiers] = name.replace(new RegExp("wire:"), "").split(".");
    return new Directive(value, modifiers, name, el);
  }
  function directive(name, callback) {
    if (customDirectiveNames.has(name))
      return;
    customDirectiveNames.add(name);
    on("directive.init", ({ el, component, directive: directive2, cleanup }) => {
      if (directive2.value === name) {
        callback({
          el,
          directive: directive2,
          component,
          $wire: component.$wire,
          cleanup
        });
      }
    });
  }
  function globalDirective(name, callback) {
    if (customDirectiveNames.has(name))
      return;
    customDirectiveNames.add(name);
    on("directive.global.init", ({ el, directive: directive2, cleanup }) => {
      if (directive2.value === name) {
        callback({ el, directive: directive2, cleanup });
      }
    });
  }
  function getDirectives(el) {
    return new DirectiveManager(el);
  }
  function customDirectiveHasBeenRegistered(name) {
    return customDirectiveNames.has(name);
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
      return Array.from(this.el.getAttributeNames().filter((name) => matchesForLivewireDirective(name)).map((name) => extractDirective(this.el, name)));
    }
  };
  var Directive = class {
    constructor(value, modifiers, rawName, el) {
      this.rawName = this.raw = rawName;
      this.el = el;
      this.eventContext;
      this.wire;
      this.value = value;
      this.modifiers = modifiers;
      this.expression = this.el.getAttribute(this.rawName);
    }
    get method() {
      const methods = this.parseOutMethodsAndParams(this.expression);
      return methods[0].method;
    }
    get methods() {
      return this.parseOutMethodsAndParams(this.expression);
    }
    get params() {
      const methods = this.parseOutMethodsAndParams(this.expression);
      return methods[0].params;
    }
    parseOutMethodsAndParams(rawMethod) {
      let methods = [];
      let parsedMethods = this.splitAndParseMethods(rawMethod);
      for (let { method, paramString } of parsedMethods) {
        let params = [];
        if (paramString.length > 0) {
          let argumentsToArray = function() {
            for (var l = arguments.length, p = new Array(l), k = 0; k < l; k++) {
              p[k] = arguments[k];
            }
            return [].concat(p);
          };
          try {
            params = Alpine.evaluate(
              document,
              "argumentsToArray(" + paramString + ")",
              {
                scope: { argumentsToArray }
              }
            );
          } catch (error2) {
            console.warn("Failed to parse parameters:", paramString, error2);
            params = [];
          }
        }
        methods.push({ method, params });
      }
      return methods;
    }
    splitAndParseMethods(methodExpression) {
      let methods = [];
      let current = "";
      let parenCount = 0;
      let inString = false;
      let stringChar = null;
      let trimmedExpression = methodExpression.trim();
      for (let i = 0; i < trimmedExpression.length; i++) {
        let char = trimmedExpression[i];
        if (!inString) {
          if (char === '"' || char === "'") {
            inString = true;
            stringChar = char;
            current += char;
          } else if (char === "(") {
            parenCount++;
            current += char;
          } else if (char === ")") {
            parenCount--;
            current += char;
          } else if (char === "," && parenCount === 0) {
            methods.push(this.parseMethodCall(current.trim()));
            current = "";
          } else {
            current += char;
          }
        } else {
          if (char === stringChar && trimmedExpression[i - 1] !== "\\") {
            inString = false;
            stringChar = null;
          }
          current += char;
        }
      }
      if (current.trim().length > 0) {
        methods.push(this.parseMethodCall(current.trim()));
      }
      return methods;
    }
    parseMethodCall(methodString) {
      let methodMatch = methodString.match(/^([^(]+)\(/);
      if (!methodMatch) {
        return {
          method: methodString.trim(),
          paramString: ""
        };
      }
      let method = methodMatch[1].trim();
      let paramStart = methodMatch[0].length - 1;
      let lastParenIndex = methodString.lastIndexOf(")");
      if (lastParenIndex === -1) {
        throw new Error(`Missing closing parenthesis for method "${method}"`);
      }
      let paramString = methodString.slice(paramStart + 1, lastParenIndex).trim();
      return {
        method,
        paramString
      };
    }
  };

  // ../alpine/packages/collapse/dist/module.esm.js
  function src_default(Alpine24) {
    Alpine24.directive("collapse", collapse);
    collapse.inline = (el, { modifiers }) => {
      if (!modifiers.includes("min"))
        return;
      el._x_doShow = () => {
      };
      el._x_doHide = () => {
      };
    };
    function collapse(el, { modifiers }) {
      let duration = modifierValue(modifiers, "duration", 250) / 1e3;
      let floor2 = modifierValue(modifiers, "min", 0);
      let fullyHide = !modifiers.includes("min");
      if (!el._x_isShown)
        el.style.height = `${floor2}px`;
      if (!el._x_isShown && fullyHide)
        el.hidden = true;
      if (!el._x_isShown)
        el.style.overflow = "hidden";
      let setFunction = (el2, styles) => {
        let revertFunction = Alpine24.setStyles(el2, styles);
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
            current = floor2;
          }
          Alpine24.transition(el, Alpine24.setStyles, {
            during: transitionStyles,
            start: { height: current + "px" },
            end: { height: full + "px" }
          }, () => el._x_isShown = true, () => {
            if (Math.abs(el.getBoundingClientRect().height - full) < 1) {
              el.style.overflow = null;
            }
          });
        },
        out(before = () => {
        }, after = () => {
        }) {
          let full = el.getBoundingClientRect().height;
          Alpine24.transition(el, setFunction, {
            during: transitionStyles,
            start: { height: full + "px" },
            end: { height: floor2 + "px" }
          }, () => el.style.overflow = "hidden", () => {
            el._x_isShown = false;
            if (el.style.height == `${floor2}px` && fullyHide) {
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

  // ../alpine/packages/focus/dist/module.esm.js
  var candidateSelectors = ["input", "select", "textarea", "a[href]", "button", "[tabindex]:not(slot)", "audio[controls]", "video[controls]", '[contenteditable]:not([contenteditable="false"])', "details>summary:first-of-type", "details"];
  var candidateSelector = /* @__PURE__ */ candidateSelectors.join(",");
  var NoElement = typeof Element === "undefined";
  var matches = NoElement ? function() {
  } : Element.prototype.matches || Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
  var getRootNode = !NoElement && Element.prototype.getRootNode ? function(element) {
    return element.getRootNode();
  } : function(element) {
    return element.ownerDocument;
  };
  var getCandidates = function getCandidates2(el, includeContainer, filter) {
    var candidates = Array.prototype.slice.apply(el.querySelectorAll(candidateSelector));
    if (includeContainer && matches.call(el, candidateSelector)) {
      candidates.unshift(el);
    }
    candidates = candidates.filter(filter);
    return candidates;
  };
  var getCandidatesIteratively = function getCandidatesIteratively2(elements, includeContainer, options) {
    var candidates = [];
    var elementsToCheck = Array.from(elements);
    while (elementsToCheck.length) {
      var element = elementsToCheck.shift();
      if (element.tagName === "SLOT") {
        var assigned = element.assignedElements();
        var content = assigned.length ? assigned : element.children;
        var nestedCandidates = getCandidatesIteratively2(content, true, options);
        if (options.flatten) {
          candidates.push.apply(candidates, nestedCandidates);
        } else {
          candidates.push({
            scope: element,
            candidates: nestedCandidates
          });
        }
      } else {
        var validCandidate = matches.call(element, candidateSelector);
        if (validCandidate && options.filter(element) && (includeContainer || !elements.includes(element))) {
          candidates.push(element);
        }
        var shadowRoot = element.shadowRoot || typeof options.getShadowRoot === "function" && options.getShadowRoot(element);
        var validShadowRoot = !options.shadowRootFilter || options.shadowRootFilter(element);
        if (shadowRoot && validShadowRoot) {
          var _nestedCandidates = getCandidatesIteratively2(shadowRoot === true ? element.children : shadowRoot.children, true, options);
          if (options.flatten) {
            candidates.push.apply(candidates, _nestedCandidates);
          } else {
            candidates.push({
              scope: element,
              candidates: _nestedCandidates
            });
          }
        } else {
          elementsToCheck.unshift.apply(elementsToCheck, element.children);
        }
      }
    }
    return candidates;
  };
  var getTabindex = function getTabindex2(node, isScope) {
    if (node.tabIndex < 0) {
      if ((isScope || /^(AUDIO|VIDEO|DETAILS)$/.test(node.tagName) || node.isContentEditable) && isNaN(parseInt(node.getAttribute("tabindex"), 10))) {
        return 0;
      }
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
    var radioScope = node.form || getRootNode(node);
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
  var isZeroArea = function isZeroArea2(node) {
    var _node$getBoundingClie = node.getBoundingClientRect(), width = _node$getBoundingClie.width, height = _node$getBoundingClie.height;
    return width === 0 && height === 0;
  };
  var isHidden = function isHidden2(node, _ref) {
    var displayCheck = _ref.displayCheck, getShadowRoot = _ref.getShadowRoot;
    if (getComputedStyle(node).visibility === "hidden") {
      return true;
    }
    var isDirectSummary = matches.call(node, "details>summary:first-of-type");
    var nodeUnderDetails = isDirectSummary ? node.parentElement : node;
    if (matches.call(nodeUnderDetails, "details:not([open]) *")) {
      return true;
    }
    var nodeRootHost = getRootNode(node).host;
    var nodeIsAttached = (nodeRootHost === null || nodeRootHost === void 0 ? void 0 : nodeRootHost.ownerDocument.contains(nodeRootHost)) || node.ownerDocument.contains(node);
    if (!displayCheck || displayCheck === "full") {
      if (typeof getShadowRoot === "function") {
        var originalNode = node;
        while (node) {
          var parentElement = node.parentElement;
          var rootNode = getRootNode(node);
          if (parentElement && !parentElement.shadowRoot && getShadowRoot(parentElement) === true) {
            return isZeroArea(node);
          } else if (node.assignedSlot) {
            node = node.assignedSlot;
          } else if (!parentElement && rootNode !== node.ownerDocument) {
            node = rootNode.host;
          } else {
            node = parentElement;
          }
        }
        node = originalNode;
      }
      if (nodeIsAttached) {
        return !node.getClientRects().length;
      }
    } else if (displayCheck === "non-zero-area") {
      return isZeroArea(node);
    }
    return false;
  };
  var isDisabledFromFieldset = function isDisabledFromFieldset2(node) {
    if (/^(INPUT|BUTTON|SELECT|TEXTAREA)$/.test(node.tagName)) {
      var parentNode = node.parentElement;
      while (parentNode) {
        if (parentNode.tagName === "FIELDSET" && parentNode.disabled) {
          for (var i = 0; i < parentNode.children.length; i++) {
            var child = parentNode.children.item(i);
            if (child.tagName === "LEGEND") {
              return matches.call(parentNode, "fieldset[disabled] *") ? true : !child.contains(node);
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
    if (node.disabled || isHiddenInput(node) || isHidden(node, options) || isDetailsWithSummary(node) || isDisabledFromFieldset(node)) {
      return false;
    }
    return true;
  };
  var isNodeMatchingSelectorTabbable = function isNodeMatchingSelectorTabbable2(options, node) {
    if (isNonTabbableRadio(node) || getTabindex(node) < 0 || !isNodeMatchingSelectorFocusable(options, node)) {
      return false;
    }
    return true;
  };
  var isValidShadowRootTabbable = function isValidShadowRootTabbable2(shadowHostNode) {
    var tabIndex = parseInt(shadowHostNode.getAttribute("tabindex"), 10);
    if (isNaN(tabIndex) || tabIndex >= 0) {
      return true;
    }
    return false;
  };
  var sortByOrder = function sortByOrder2(candidates) {
    var regularTabbables = [];
    var orderedTabbables = [];
    candidates.forEach(function(item, i) {
      var isScope = !!item.scope;
      var element = isScope ? item.scope : item;
      var candidateTabindex = getTabindex(element, isScope);
      var elements = isScope ? sortByOrder2(item.candidates) : element;
      if (candidateTabindex === 0) {
        isScope ? regularTabbables.push.apply(regularTabbables, elements) : regularTabbables.push(element);
      } else {
        orderedTabbables.push({
          documentOrder: i,
          tabIndex: candidateTabindex,
          item,
          isScope,
          content: elements
        });
      }
    });
    return orderedTabbables.sort(sortOrderedTabbables).reduce(function(acc, sortable) {
      sortable.isScope ? acc.push.apply(acc, sortable.content) : acc.push(sortable.content);
      return acc;
    }, []).concat(regularTabbables);
  };
  var tabbable = function tabbable2(el, options) {
    options = options || {};
    var candidates;
    if (options.getShadowRoot) {
      candidates = getCandidatesIteratively([el], options.includeContainer, {
        filter: isNodeMatchingSelectorTabbable.bind(null, options),
        flatten: false,
        getShadowRoot: options.getShadowRoot,
        shadowRootFilter: isValidShadowRootTabbable
      });
    } else {
      candidates = getCandidates(el, options.includeContainer, isNodeMatchingSelectorTabbable.bind(null, options));
    }
    return sortByOrder(candidates);
  };
  var focusable = function focusable2(el, options) {
    options = options || {};
    var candidates;
    if (options.getShadowRoot) {
      candidates = getCandidatesIteratively([el], options.includeContainer, {
        filter: isNodeMatchingSelectorFocusable.bind(null, options),
        flatten: true,
        getShadowRoot: options.getShadowRoot
      });
    } else {
      candidates = getCandidates(el, options.includeContainer, isNodeMatchingSelectorFocusable.bind(null, options));
    }
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
  var isFocusable = function isFocusable2(node, options) {
    options = options || {};
    if (!node) {
      throw new Error("No node provided");
    }
    if (matches.call(node, focusableCandidateSelector) === false) {
      return false;
    }
    return isNodeMatchingSelectorFocusable(options, node);
  };
  function ownKeys(object, enumerableOnly) {
    var keys = Object.keys(object);
    if (Object.getOwnPropertySymbols) {
      var symbols = Object.getOwnPropertySymbols(object);
      enumerableOnly && (symbols = symbols.filter(function(sym) {
        return Object.getOwnPropertyDescriptor(object, sym).enumerable;
      })), keys.push.apply(keys, symbols);
    }
    return keys;
  }
  function _objectSpread2(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = null != arguments[i] ? arguments[i] : {};
      i % 2 ? ownKeys(Object(source), true).forEach(function(key) {
        _defineProperty(target, key, source[key]);
      }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function(key) {
        Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
      });
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
  var getActualTarget = function getActualTarget2(event) {
    return event.target.shadowRoot && typeof event.composedPath === "function" ? event.composedPath()[0] : event.target;
  };
  var createFocusTrap = function createFocusTrap2(elements, userOptions) {
    var doc = (userOptions === null || userOptions === void 0 ? void 0 : userOptions.document) || document;
    var config = _objectSpread2({
      returnFocusOnDeactivate: true,
      escapeDeactivates: true,
      delayInitialFocus: true
    }, userOptions);
    var state = {
      containers: [],
      containerGroups: [],
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
    var findContainerIndex = function findContainerIndex2(element) {
      return state.containerGroups.findIndex(function(_ref) {
        var container = _ref.container, tabbableNodes = _ref.tabbableNodes;
        return container.contains(element) || tabbableNodes.find(function(node) {
          return node === element;
        });
      });
    };
    var getNodeForOption = function getNodeForOption2(optionName) {
      var optionValue = config[optionName];
      if (typeof optionValue === "function") {
        for (var _len2 = arguments.length, params = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
          params[_key2 - 1] = arguments[_key2];
        }
        optionValue = optionValue.apply(void 0, params);
      }
      if (optionValue === true) {
        optionValue = void 0;
      }
      if (!optionValue) {
        if (optionValue === void 0 || optionValue === false) {
          return optionValue;
        }
        throw new Error("`".concat(optionName, "` was specified but was not a node, or did not return a node"));
      }
      var node = optionValue;
      if (typeof optionValue === "string") {
        node = doc.querySelector(optionValue);
        if (!node) {
          throw new Error("`".concat(optionName, "` as selector refers to no known node"));
        }
      }
      return node;
    };
    var getInitialFocusNode = function getInitialFocusNode2() {
      var node = getNodeForOption("initialFocus");
      if (node === false) {
        return false;
      }
      if (node === void 0) {
        if (findContainerIndex(doc.activeElement) >= 0) {
          node = doc.activeElement;
        } else {
          var firstTabbableGroup = state.tabbableGroups[0];
          var firstTabbableNode = firstTabbableGroup && firstTabbableGroup.firstTabbableNode;
          node = firstTabbableNode || getNodeForOption("fallbackFocus");
        }
      }
      if (!node) {
        throw new Error("Your focus-trap needs to have at least one focusable element");
      }
      return node;
    };
    var updateTabbableNodes = function updateTabbableNodes2() {
      state.containerGroups = state.containers.map(function(container) {
        var tabbableNodes = tabbable(container, config.tabbableOptions);
        var focusableNodes = focusable(container, config.tabbableOptions);
        return {
          container,
          tabbableNodes,
          focusableNodes,
          firstTabbableNode: tabbableNodes.length > 0 ? tabbableNodes[0] : null,
          lastTabbableNode: tabbableNodes.length > 0 ? tabbableNodes[tabbableNodes.length - 1] : null,
          nextTabbableNode: function nextTabbableNode(node) {
            var forward = arguments.length > 1 && arguments[1] !== void 0 ? arguments[1] : true;
            var nodeIdx = focusableNodes.findIndex(function(n) {
              return n === node;
            });
            if (nodeIdx < 0) {
              return void 0;
            }
            if (forward) {
              return focusableNodes.slice(nodeIdx + 1).find(function(n) {
                return isTabbable(n, config.tabbableOptions);
              });
            }
            return focusableNodes.slice(0, nodeIdx).reverse().find(function(n) {
              return isTabbable(n, config.tabbableOptions);
            });
          }
        };
      });
      state.tabbableGroups = state.containerGroups.filter(function(group) {
        return group.tabbableNodes.length > 0;
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
      var node = getNodeForOption("setReturnFocus", previousActiveElement);
      return node ? node : node === false ? false : previousActiveElement;
    };
    var checkPointerDown = function checkPointerDown2(e) {
      var target = getActualTarget(e);
      if (findContainerIndex(target) >= 0) {
        return;
      }
      if (valueOrHandler(config.clickOutsideDeactivates, e)) {
        trap.deactivate({
          returnFocus: config.returnFocusOnDeactivate && !isFocusable(target, config.tabbableOptions)
        });
        return;
      }
      if (valueOrHandler(config.allowOutsideClick, e)) {
        return;
      }
      e.preventDefault();
    };
    var checkFocusIn = function checkFocusIn2(e) {
      var target = getActualTarget(e);
      var targetContained = findContainerIndex(target) >= 0;
      if (targetContained || target instanceof Document) {
        if (targetContained) {
          state.mostRecentlyFocusedNode = target;
        }
      } else {
        e.stopImmediatePropagation();
        tryFocus(state.mostRecentlyFocusedNode || getInitialFocusNode());
      }
    };
    var checkTab = function checkTab2(e) {
      var target = getActualTarget(e);
      updateTabbableNodes();
      var destinationNode = null;
      if (state.tabbableGroups.length > 0) {
        var containerIndex = findContainerIndex(target);
        var containerGroup = containerIndex >= 0 ? state.containerGroups[containerIndex] : void 0;
        if (containerIndex < 0) {
          if (e.shiftKey) {
            destinationNode = state.tabbableGroups[state.tabbableGroups.length - 1].lastTabbableNode;
          } else {
            destinationNode = state.tabbableGroups[0].firstTabbableNode;
          }
        } else if (e.shiftKey) {
          var startOfGroupIndex = findIndex(state.tabbableGroups, function(_ref2) {
            var firstTabbableNode = _ref2.firstTabbableNode;
            return target === firstTabbableNode;
          });
          if (startOfGroupIndex < 0 && (containerGroup.container === target || isFocusable(target, config.tabbableOptions) && !isTabbable(target, config.tabbableOptions) && !containerGroup.nextTabbableNode(target, false))) {
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
            return target === lastTabbableNode;
          });
          if (lastOfGroupIndex < 0 && (containerGroup.container === target || isFocusable(target, config.tabbableOptions) && !isTabbable(target, config.tabbableOptions) && !containerGroup.nextTabbableNode(target))) {
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
      if (isEscapeEvent(e) && valueOrHandler(config.escapeDeactivates, e) !== false) {
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
      var target = getActualTarget(e);
      if (findContainerIndex(target) >= 0) {
        return;
      }
      if (valueOrHandler(config.clickOutsideDeactivates, e)) {
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
      get active() {
        return state.active;
      },
      get paused() {
        return state.paused;
      },
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
        var options = _objectSpread2({
          onDeactivate: config.onDeactivate,
          onPostDeactivate: config.onPostDeactivate,
          checkCanReturnFocus: config.checkCanReturnFocus
        }, deactivateOptions);
        clearTimeout(state.delayInitialFocusTimer);
        state.delayInitialFocusTimer = void 0;
        removeListeners();
        state.active = false;
        state.paused = false;
        activeFocusTraps.deactivateTrap(trap);
        var onDeactivate = getOption(options, "onDeactivate");
        var onPostDeactivate = getOption(options, "onPostDeactivate");
        var checkCanReturnFocus = getOption(options, "checkCanReturnFocus");
        var returnFocus = getOption(options, "returnFocus", "returnFocusOnDeactivate");
        if (onDeactivate) {
          onDeactivate();
        }
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
  function src_default2(Alpine24) {
    let lastFocused;
    let currentFocused;
    window.addEventListener("focusin", () => {
      lastFocused = currentFocused;
      currentFocused = document.activeElement;
    });
    Alpine24.magic("focus", (el) => {
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
          return isFocusable(el2);
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
          return focusable(within, { displayCheck: "none" });
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
            el2.focus({ preventScroll: this.__noscroll });
          });
        }
      };
    });
    Alpine24.directive("trap", Alpine24.skipDuringClone(
      (el, { expression, modifiers }, { effect, evaluateLater, cleanup }) => {
        let evaluator = evaluateLater(expression);
        let oldValue = false;
        let options = {
          escapeDeactivates: false,
          allowOutsideClick: true,
          fallbackFocus: () => el
        };
        let undoInert = () => {
        };
        if (modifiers.includes("noautofocus")) {
          options.initialFocus = false;
        } else {
          let autofocusEl = el.querySelector("[autofocus]");
          if (autofocusEl)
            options.initialFocus = autofocusEl;
        }
        if (modifiers.includes("inert")) {
          options.onPostActivate = () => {
            Alpine24.nextTick(() => {
              undoInert = setInert(el);
            });
          };
        }
        let trap = createFocusTrap(el, options);
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
            if (modifiers.includes("noscroll"))
              undoDisableScrolling = disableScrolling();
            setTimeout(() => {
              trap.activate();
            }, 15);
          }
          if (!value && oldValue) {
            releaseFocus();
          }
          oldValue = !!value;
        }));
        cleanup(releaseFocus);
      },
      (el, { expression, modifiers }, { evaluate: evaluate2 }) => {
        if (modifiers.includes("inert") && evaluate2(expression))
          setInert(el);
      }
    ));
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
  var module_default2 = src_default2;

  // ../alpine/packages/persist/dist/module.esm.js
  function src_default3(Alpine24) {
    let persist = () => {
      let alias;
      let storage;
      try {
        storage = localStorage;
      } catch (e) {
        console.error(e);
        console.warn("Alpine: $persist is using temporary storage since localStorage is unavailable.");
        let dummy = /* @__PURE__ */ new Map();
        storage = {
          getItem: dummy.get.bind(dummy),
          setItem: dummy.set.bind(dummy)
        };
      }
      return Alpine24.interceptor((initialValue, getter, setter, path, key) => {
        let lookup = alias || `_x_${path}`;
        let initial = storageHas(lookup, storage) ? storageGet(lookup, storage) : initialValue;
        setter(initial);
        Alpine24.effect(() => {
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
    Object.defineProperty(Alpine24, "$persist", { get: () => persist() });
    Alpine24.magic("persist", persist);
    Alpine24.persist = (key, { get, set }, storage = localStorage) => {
      let initial = storageHas(key, storage) ? storageGet(key, storage) : get();
      set(initial);
      Alpine24.effect(() => {
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
    let value = storage.getItem(key);
    if (value === void 0)
      return;
    return JSON.parse(value);
  }
  function storageSet(key, value, storage) {
    storage.setItem(key, JSON.stringify(value));
  }
  var module_default3 = src_default3;

  // ../alpine/packages/intersect/dist/module.esm.js
  function src_default4(Alpine24) {
    Alpine24.directive("intersect", Alpine24.skipDuringClone((el, { value, expression, modifiers }, { evaluateLater, cleanup }) => {
      let evaluate2 = evaluateLater(expression);
      let options = {
        rootMargin: getRootMargin(modifiers),
        threshold: getThreshold(modifiers)
      };
      let observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting === (value === "leave"))
            return;
          evaluate2();
          modifiers.includes("once") && observer.disconnect();
        });
      }, options);
      observer.observe(el);
      cleanup(() => {
        observer.disconnect();
      });
    }));
  }
  function getThreshold(modifiers) {
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
    const index2 = modifiers.indexOf(key);
    if (index2 === -1)
      return fallback2;
    let values = [];
    for (let i = 1; i < 5; i++) {
      values.push(getLengthValue(modifiers[index2 + i] || ""));
    }
    values = values.filter((v) => v !== void 0);
    return values.length ? values.join(" ").trim() : fallback2;
  }
  var module_default4 = src_default4;

  // ../alpine/packages/sort/dist/module.esm.js
  function ownKeys2(object, enumerableOnly) {
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
  function _objectSpread22(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i] != null ? arguments[i] : {};
      if (i % 2) {
        ownKeys2(Object(source), true).forEach(function(key) {
          _defineProperty2(target, key, source[key]);
        });
      } else if (Object.getOwnPropertyDescriptors) {
        Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
      } else {
        ownKeys2(Object(source)).forEach(function(key) {
          Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
        });
      }
    }
    return target;
  }
  function _typeof(obj) {
    "@babel/helpers - typeof";
    if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
      _typeof = function(obj2) {
        return typeof obj2;
      };
    } else {
      _typeof = function(obj2) {
        return obj2 && typeof Symbol === "function" && obj2.constructor === Symbol && obj2 !== Symbol.prototype ? "symbol" : typeof obj2;
      };
    }
    return _typeof(obj);
  }
  function _defineProperty2(obj, key, value) {
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
  function _extends() {
    _extends = Object.assign || function(target) {
      for (var i = 1; i < arguments.length; i++) {
        var source = arguments[i];
        for (var key in source) {
          if (Object.prototype.hasOwnProperty.call(source, key)) {
            target[key] = source[key];
          }
        }
      }
      return target;
    };
    return _extends.apply(this, arguments);
  }
  function _objectWithoutPropertiesLoose(source, excluded) {
    if (source == null)
      return {};
    var target = {};
    var sourceKeys = Object.keys(source);
    var key, i;
    for (i = 0; i < sourceKeys.length; i++) {
      key = sourceKeys[i];
      if (excluded.indexOf(key) >= 0)
        continue;
      target[key] = source[key];
    }
    return target;
  }
  function _objectWithoutProperties(source, excluded) {
    if (source == null)
      return {};
    var target = _objectWithoutPropertiesLoose(source, excluded);
    var key, i;
    if (Object.getOwnPropertySymbols) {
      var sourceSymbolKeys = Object.getOwnPropertySymbols(source);
      for (i = 0; i < sourceSymbolKeys.length; i++) {
        key = sourceSymbolKeys[i];
        if (excluded.indexOf(key) >= 0)
          continue;
        if (!Object.prototype.propertyIsEnumerable.call(source, key))
          continue;
        target[key] = source[key];
      }
    }
    return target;
  }
  var version = "1.15.2";
  function userAgent(pattern) {
    if (typeof window !== "undefined" && window.navigator) {
      return !!/* @__PURE__ */ navigator.userAgent.match(pattern);
    }
  }
  var IE11OrLess = userAgent(/(?:Trident.*rv[ :]?11\.|msie|iemobile|Windows Phone)/i);
  var Edge = userAgent(/Edge/i);
  var FireFox = userAgent(/firefox/i);
  var Safari = userAgent(/safari/i) && !userAgent(/chrome/i) && !userAgent(/android/i);
  var IOS = userAgent(/iP(ad|od|hone)/i);
  var ChromeForAndroid = userAgent(/chrome/i) && userAgent(/android/i);
  var captureMode = {
    capture: false,
    passive: false
  };
  function on3(el, event, fn) {
    el.addEventListener(event, fn, !IE11OrLess && captureMode);
  }
  function off(el, event, fn) {
    el.removeEventListener(event, fn, !IE11OrLess && captureMode);
  }
  function matches2(el, selector) {
    if (!selector)
      return;
    selector[0] === ">" && (selector = selector.substring(1));
    if (el) {
      try {
        if (el.matches) {
          return el.matches(selector);
        } else if (el.msMatchesSelector) {
          return el.msMatchesSelector(selector);
        } else if (el.webkitMatchesSelector) {
          return el.webkitMatchesSelector(selector);
        }
      } catch (_) {
        return false;
      }
    }
    return false;
  }
  function getParentOrHost(el) {
    return el.host && el !== document && el.host.nodeType ? el.host : el.parentNode;
  }
  function closest(el, selector, ctx, includeCTX) {
    if (el) {
      ctx = ctx || document;
      do {
        if (selector != null && (selector[0] === ">" ? el.parentNode === ctx && matches2(el, selector) : matches2(el, selector)) || includeCTX && el === ctx) {
          return el;
        }
        if (el === ctx)
          break;
      } while (el = getParentOrHost(el));
    }
    return null;
  }
  var R_SPACE = /\s+/g;
  function toggleClass(el, name, state) {
    if (el && name) {
      if (el.classList) {
        el.classList[state ? "add" : "remove"](name);
      } else {
        var className = (" " + el.className + " ").replace(R_SPACE, " ").replace(" " + name + " ", " ");
        el.className = (className + (state ? " " + name : "")).replace(R_SPACE, " ");
      }
    }
  }
  function css(el, prop, val) {
    var style = el && el.style;
    if (style) {
      if (val === void 0) {
        if (document.defaultView && document.defaultView.getComputedStyle) {
          val = document.defaultView.getComputedStyle(el, "");
        } else if (el.currentStyle) {
          val = el.currentStyle;
        }
        return prop === void 0 ? val : val[prop];
      } else {
        if (!(prop in style) && prop.indexOf("webkit") === -1) {
          prop = "-webkit-" + prop;
        }
        style[prop] = val + (typeof val === "string" ? "" : "px");
      }
    }
  }
  function matrix(el, selfOnly) {
    var appliedTransforms = "";
    if (typeof el === "string") {
      appliedTransforms = el;
    } else {
      do {
        var transform = css(el, "transform");
        if (transform && transform !== "none") {
          appliedTransforms = transform + " " + appliedTransforms;
        }
      } while (!selfOnly && (el = el.parentNode));
    }
    var matrixFn = window.DOMMatrix || window.WebKitCSSMatrix || window.CSSMatrix || window.MSCSSMatrix;
    return matrixFn && new matrixFn(appliedTransforms);
  }
  function find2(ctx, tagName, iterator) {
    if (ctx) {
      var list = ctx.getElementsByTagName(tagName), i = 0, n = list.length;
      if (iterator) {
        for (; i < n; i++) {
          iterator(list[i], i);
        }
      }
      return list;
    }
    return [];
  }
  function getWindowScrollingElement() {
    var scrollingElement = document.scrollingElement;
    if (scrollingElement) {
      return scrollingElement;
    } else {
      return document.documentElement;
    }
  }
  function getRect(el, relativeToContainingBlock, relativeToNonStaticParent, undoScale, container) {
    if (!el.getBoundingClientRect && el !== window)
      return;
    var elRect, top, left, bottom, right, height, width;
    if (el !== window && el.parentNode && el !== getWindowScrollingElement()) {
      elRect = el.getBoundingClientRect();
      top = elRect.top;
      left = elRect.left;
      bottom = elRect.bottom;
      right = elRect.right;
      height = elRect.height;
      width = elRect.width;
    } else {
      top = 0;
      left = 0;
      bottom = window.innerHeight;
      right = window.innerWidth;
      height = window.innerHeight;
      width = window.innerWidth;
    }
    if ((relativeToContainingBlock || relativeToNonStaticParent) && el !== window) {
      container = container || el.parentNode;
      if (!IE11OrLess) {
        do {
          if (container && container.getBoundingClientRect && (css(container, "transform") !== "none" || relativeToNonStaticParent && css(container, "position") !== "static")) {
            var containerRect = container.getBoundingClientRect();
            top -= containerRect.top + parseInt(css(container, "border-top-width"));
            left -= containerRect.left + parseInt(css(container, "border-left-width"));
            bottom = top + elRect.height;
            right = left + elRect.width;
            break;
          }
        } while (container = container.parentNode);
      }
    }
    if (undoScale && el !== window) {
      var elMatrix = matrix(container || el), scaleX = elMatrix && elMatrix.a, scaleY = elMatrix && elMatrix.d;
      if (elMatrix) {
        top /= scaleY;
        left /= scaleX;
        width /= scaleX;
        height /= scaleY;
        bottom = top + height;
        right = left + width;
      }
    }
    return {
      top,
      left,
      bottom,
      right,
      width,
      height
    };
  }
  function isScrolledPast(el, elSide, parentSide) {
    var parent = getParentAutoScrollElement(el, true), elSideVal = getRect(el)[elSide];
    while (parent) {
      var parentSideVal = getRect(parent)[parentSide], visible = void 0;
      if (parentSide === "top" || parentSide === "left") {
        visible = elSideVal >= parentSideVal;
      } else {
        visible = elSideVal <= parentSideVal;
      }
      if (!visible)
        return parent;
      if (parent === getWindowScrollingElement())
        break;
      parent = getParentAutoScrollElement(parent, false);
    }
    return false;
  }
  function getChild(el, childNum, options, includeDragEl) {
    var currentChild = 0, i = 0, children = el.children;
    while (i < children.length) {
      if (children[i].style.display !== "none" && children[i] !== Sortable.ghost && (includeDragEl || children[i] !== Sortable.dragged) && closest(children[i], options.draggable, el, false)) {
        if (currentChild === childNum) {
          return children[i];
        }
        currentChild++;
      }
      i++;
    }
    return null;
  }
  function lastChild(el, selector) {
    var last = el.lastElementChild;
    while (last && (last === Sortable.ghost || css(last, "display") === "none" || selector && !matches2(last, selector))) {
      last = last.previousElementSibling;
    }
    return last || null;
  }
  function index(el, selector) {
    var index2 = 0;
    if (!el || !el.parentNode) {
      return -1;
    }
    while (el = el.previousElementSibling) {
      if (el.nodeName.toUpperCase() !== "TEMPLATE" && el !== Sortable.clone && (!selector || matches2(el, selector))) {
        index2++;
      }
    }
    return index2;
  }
  function getRelativeScrollOffset(el) {
    var offsetLeft = 0, offsetTop = 0, winScroller = getWindowScrollingElement();
    if (el) {
      do {
        var elMatrix = matrix(el), scaleX = elMatrix.a, scaleY = elMatrix.d;
        offsetLeft += el.scrollLeft * scaleX;
        offsetTop += el.scrollTop * scaleY;
      } while (el !== winScroller && (el = el.parentNode));
    }
    return [offsetLeft, offsetTop];
  }
  function indexOfObject(arr, obj) {
    for (var i in arr) {
      if (!arr.hasOwnProperty(i))
        continue;
      for (var key in obj) {
        if (obj.hasOwnProperty(key) && obj[key] === arr[i][key])
          return Number(i);
      }
    }
    return -1;
  }
  function getParentAutoScrollElement(el, includeSelf) {
    if (!el || !el.getBoundingClientRect)
      return getWindowScrollingElement();
    var elem = el;
    var gotSelf = false;
    do {
      if (elem.clientWidth < elem.scrollWidth || elem.clientHeight < elem.scrollHeight) {
        var elemCSS = css(elem);
        if (elem.clientWidth < elem.scrollWidth && (elemCSS.overflowX == "auto" || elemCSS.overflowX == "scroll") || elem.clientHeight < elem.scrollHeight && (elemCSS.overflowY == "auto" || elemCSS.overflowY == "scroll")) {
          if (!elem.getBoundingClientRect || elem === document.body)
            return getWindowScrollingElement();
          if (gotSelf || includeSelf)
            return elem;
          gotSelf = true;
        }
      }
    } while (elem = elem.parentNode);
    return getWindowScrollingElement();
  }
  function extend(dst, src) {
    if (dst && src) {
      for (var key in src) {
        if (src.hasOwnProperty(key)) {
          dst[key] = src[key];
        }
      }
    }
    return dst;
  }
  function isRectEqual(rect1, rect2) {
    return Math.round(rect1.top) === Math.round(rect2.top) && Math.round(rect1.left) === Math.round(rect2.left) && Math.round(rect1.height) === Math.round(rect2.height) && Math.round(rect1.width) === Math.round(rect2.width);
  }
  var _throttleTimeout;
  function throttle(callback, ms) {
    return function() {
      if (!_throttleTimeout) {
        var args = arguments, _this = this;
        if (args.length === 1) {
          callback.call(_this, args[0]);
        } else {
          callback.apply(_this, args);
        }
        _throttleTimeout = setTimeout(function() {
          _throttleTimeout = void 0;
        }, ms);
      }
    };
  }
  function cancelThrottle() {
    clearTimeout(_throttleTimeout);
    _throttleTimeout = void 0;
  }
  function scrollBy(el, x, y) {
    el.scrollLeft += x;
    el.scrollTop += y;
  }
  function clone(el) {
    var Polymer = window.Polymer;
    var $ = window.jQuery || window.Zepto;
    if (Polymer && Polymer.dom) {
      return Polymer.dom(el).cloneNode(true);
    } else if ($) {
      return $(el).clone(true)[0];
    } else {
      return el.cloneNode(true);
    }
  }
  function getChildContainingRectFromElement(container, options, ghostEl2) {
    var rect = {};
    Array.from(container.children).forEach(function(child) {
      var _rect$left, _rect$top, _rect$right, _rect$bottom;
      if (!closest(child, options.draggable, container, false) || child.animated || child === ghostEl2)
        return;
      var childRect = getRect(child);
      rect.left = Math.min((_rect$left = rect.left) !== null && _rect$left !== void 0 ? _rect$left : Infinity, childRect.left);
      rect.top = Math.min((_rect$top = rect.top) !== null && _rect$top !== void 0 ? _rect$top : Infinity, childRect.top);
      rect.right = Math.max((_rect$right = rect.right) !== null && _rect$right !== void 0 ? _rect$right : -Infinity, childRect.right);
      rect.bottom = Math.max((_rect$bottom = rect.bottom) !== null && _rect$bottom !== void 0 ? _rect$bottom : -Infinity, childRect.bottom);
    });
    rect.width = rect.right - rect.left;
    rect.height = rect.bottom - rect.top;
    rect.x = rect.left;
    rect.y = rect.top;
    return rect;
  }
  var expando = "Sortable" + new Date().getTime();
  function AnimationStateManager() {
    var animationStates = [], animationCallbackId;
    return {
      captureAnimationState: function captureAnimationState() {
        animationStates = [];
        if (!this.options.animation)
          return;
        var children = [].slice.call(this.el.children);
        children.forEach(function(child) {
          if (css(child, "display") === "none" || child === Sortable.ghost)
            return;
          animationStates.push({
            target: child,
            rect: getRect(child)
          });
          var fromRect = _objectSpread22({}, animationStates[animationStates.length - 1].rect);
          if (child.thisAnimationDuration) {
            var childMatrix = matrix(child, true);
            if (childMatrix) {
              fromRect.top -= childMatrix.f;
              fromRect.left -= childMatrix.e;
            }
          }
          child.fromRect = fromRect;
        });
      },
      addAnimationState: function addAnimationState(state) {
        animationStates.push(state);
      },
      removeAnimationState: function removeAnimationState(target) {
        animationStates.splice(indexOfObject(animationStates, {
          target
        }), 1);
      },
      animateAll: function animateAll(callback) {
        var _this = this;
        if (!this.options.animation) {
          clearTimeout(animationCallbackId);
          if (typeof callback === "function")
            callback();
          return;
        }
        var animating = false, animationTime = 0;
        animationStates.forEach(function(state) {
          var time = 0, target = state.target, fromRect = target.fromRect, toRect = getRect(target), prevFromRect = target.prevFromRect, prevToRect = target.prevToRect, animatingRect = state.rect, targetMatrix = matrix(target, true);
          if (targetMatrix) {
            toRect.top -= targetMatrix.f;
            toRect.left -= targetMatrix.e;
          }
          target.toRect = toRect;
          if (target.thisAnimationDuration) {
            if (isRectEqual(prevFromRect, toRect) && !isRectEqual(fromRect, toRect) && (animatingRect.top - toRect.top) / (animatingRect.left - toRect.left) === (fromRect.top - toRect.top) / (fromRect.left - toRect.left)) {
              time = calculateRealTime(animatingRect, prevFromRect, prevToRect, _this.options);
            }
          }
          if (!isRectEqual(toRect, fromRect)) {
            target.prevFromRect = fromRect;
            target.prevToRect = toRect;
            if (!time) {
              time = _this.options.animation;
            }
            _this.animate(target, animatingRect, toRect, time);
          }
          if (time) {
            animating = true;
            animationTime = Math.max(animationTime, time);
            clearTimeout(target.animationResetTimer);
            target.animationResetTimer = setTimeout(function() {
              target.animationTime = 0;
              target.prevFromRect = null;
              target.fromRect = null;
              target.prevToRect = null;
              target.thisAnimationDuration = null;
            }, time);
            target.thisAnimationDuration = time;
          }
        });
        clearTimeout(animationCallbackId);
        if (!animating) {
          if (typeof callback === "function")
            callback();
        } else {
          animationCallbackId = setTimeout(function() {
            if (typeof callback === "function")
              callback();
          }, animationTime);
        }
        animationStates = [];
      },
      animate: function animate(target, currentRect, toRect, duration) {
        if (duration) {
          css(target, "transition", "");
          css(target, "transform", "");
          var elMatrix = matrix(this.el), scaleX = elMatrix && elMatrix.a, scaleY = elMatrix && elMatrix.d, translateX = (currentRect.left - toRect.left) / (scaleX || 1), translateY = (currentRect.top - toRect.top) / (scaleY || 1);
          target.animatingX = !!translateX;
          target.animatingY = !!translateY;
          css(target, "transform", "translate3d(" + translateX + "px," + translateY + "px,0)");
          this.forRepaintDummy = repaint(target);
          css(target, "transition", "transform " + duration + "ms" + (this.options.easing ? " " + this.options.easing : ""));
          css(target, "transform", "translate3d(0,0,0)");
          typeof target.animated === "number" && clearTimeout(target.animated);
          target.animated = setTimeout(function() {
            css(target, "transition", "");
            css(target, "transform", "");
            target.animated = false;
            target.animatingX = false;
            target.animatingY = false;
          }, duration);
        }
      }
    };
  }
  function repaint(target) {
    return target.offsetWidth;
  }
  function calculateRealTime(animatingRect, fromRect, toRect, options) {
    return Math.sqrt(Math.pow(fromRect.top - animatingRect.top, 2) + Math.pow(fromRect.left - animatingRect.left, 2)) / Math.sqrt(Math.pow(fromRect.top - toRect.top, 2) + Math.pow(fromRect.left - toRect.left, 2)) * options.animation;
  }
  var plugins = [];
  var defaults = {
    initializeByDefault: true
  };
  var PluginManager = {
    mount: function mount(plugin) {
      for (var option2 in defaults) {
        if (defaults.hasOwnProperty(option2) && !(option2 in plugin)) {
          plugin[option2] = defaults[option2];
        }
      }
      plugins.forEach(function(p) {
        if (p.pluginName === plugin.pluginName) {
          throw "Sortable: Cannot mount plugin ".concat(plugin.pluginName, " more than once");
        }
      });
      plugins.push(plugin);
    },
    pluginEvent: function pluginEvent(eventName, sortable, evt) {
      var _this = this;
      this.eventCanceled = false;
      evt.cancel = function() {
        _this.eventCanceled = true;
      };
      var eventNameGlobal = eventName + "Global";
      plugins.forEach(function(plugin) {
        if (!sortable[plugin.pluginName])
          return;
        if (sortable[plugin.pluginName][eventNameGlobal]) {
          sortable[plugin.pluginName][eventNameGlobal](_objectSpread22({
            sortable
          }, evt));
        }
        if (sortable.options[plugin.pluginName] && sortable[plugin.pluginName][eventName]) {
          sortable[plugin.pluginName][eventName](_objectSpread22({
            sortable
          }, evt));
        }
      });
    },
    initializePlugins: function initializePlugins(sortable, el, defaults2, options) {
      plugins.forEach(function(plugin) {
        var pluginName = plugin.pluginName;
        if (!sortable.options[pluginName] && !plugin.initializeByDefault)
          return;
        var initialized = new plugin(sortable, el, sortable.options);
        initialized.sortable = sortable;
        initialized.options = sortable.options;
        sortable[pluginName] = initialized;
        _extends(defaults2, initialized.defaults);
      });
      for (var option2 in sortable.options) {
        if (!sortable.options.hasOwnProperty(option2))
          continue;
        var modified = this.modifyOption(sortable, option2, sortable.options[option2]);
        if (typeof modified !== "undefined") {
          sortable.options[option2] = modified;
        }
      }
    },
    getEventProperties: function getEventProperties(name, sortable) {
      var eventProperties = {};
      plugins.forEach(function(plugin) {
        if (typeof plugin.eventProperties !== "function")
          return;
        _extends(eventProperties, plugin.eventProperties.call(sortable[plugin.pluginName], name));
      });
      return eventProperties;
    },
    modifyOption: function modifyOption(sortable, name, value) {
      var modifiedValue;
      plugins.forEach(function(plugin) {
        if (!sortable[plugin.pluginName])
          return;
        if (plugin.optionListeners && typeof plugin.optionListeners[name] === "function") {
          modifiedValue = plugin.optionListeners[name].call(sortable[plugin.pluginName], value);
        }
      });
      return modifiedValue;
    }
  };
  function dispatchEvent2(_ref) {
    var sortable = _ref.sortable, rootEl2 = _ref.rootEl, name = _ref.name, targetEl = _ref.targetEl, cloneEl2 = _ref.cloneEl, toEl = _ref.toEl, fromEl = _ref.fromEl, oldIndex2 = _ref.oldIndex, newIndex2 = _ref.newIndex, oldDraggableIndex2 = _ref.oldDraggableIndex, newDraggableIndex2 = _ref.newDraggableIndex, originalEvent = _ref.originalEvent, putSortable2 = _ref.putSortable, extraEventProperties = _ref.extraEventProperties;
    sortable = sortable || rootEl2 && rootEl2[expando];
    if (!sortable)
      return;
    var evt, options = sortable.options, onName = "on" + name.charAt(0).toUpperCase() + name.substr(1);
    if (window.CustomEvent && !IE11OrLess && !Edge) {
      evt = new CustomEvent(name, {
        bubbles: true,
        cancelable: true
      });
    } else {
      evt = document.createEvent("Event");
      evt.initEvent(name, true, true);
    }
    evt.to = toEl || rootEl2;
    evt.from = fromEl || rootEl2;
    evt.item = targetEl || rootEl2;
    evt.clone = cloneEl2;
    evt.oldIndex = oldIndex2;
    evt.newIndex = newIndex2;
    evt.oldDraggableIndex = oldDraggableIndex2;
    evt.newDraggableIndex = newDraggableIndex2;
    evt.originalEvent = originalEvent;
    evt.pullMode = putSortable2 ? putSortable2.lastPutMode : void 0;
    var allEventProperties = _objectSpread22(_objectSpread22({}, extraEventProperties), PluginManager.getEventProperties(name, sortable));
    for (var option2 in allEventProperties) {
      evt[option2] = allEventProperties[option2];
    }
    if (rootEl2) {
      rootEl2.dispatchEvent(evt);
    }
    if (options[onName]) {
      options[onName].call(sortable, evt);
    }
  }
  var _excluded = ["evt"];
  var pluginEvent2 = function pluginEvent3(eventName, sortable) {
    var _ref = arguments.length > 2 && arguments[2] !== void 0 ? arguments[2] : {}, originalEvent = _ref.evt, data = _objectWithoutProperties(_ref, _excluded);
    PluginManager.pluginEvent.bind(Sortable)(eventName, sortable, _objectSpread22({
      dragEl,
      parentEl,
      ghostEl,
      rootEl,
      nextEl,
      lastDownEl,
      cloneEl,
      cloneHidden,
      dragStarted: moved,
      putSortable,
      activeSortable: Sortable.active,
      originalEvent,
      oldIndex,
      oldDraggableIndex,
      newIndex,
      newDraggableIndex,
      hideGhostForTarget: _hideGhostForTarget,
      unhideGhostForTarget: _unhideGhostForTarget,
      cloneNowHidden: function cloneNowHidden() {
        cloneHidden = true;
      },
      cloneNowShown: function cloneNowShown() {
        cloneHidden = false;
      },
      dispatchSortableEvent: function dispatchSortableEvent(name) {
        _dispatchEvent({
          sortable,
          name,
          originalEvent
        });
      }
    }, data));
  };
  function _dispatchEvent(info) {
    dispatchEvent2(_objectSpread22({
      putSortable,
      cloneEl,
      targetEl: dragEl,
      rootEl,
      oldIndex,
      oldDraggableIndex,
      newIndex,
      newDraggableIndex
    }, info));
  }
  var dragEl;
  var parentEl;
  var ghostEl;
  var rootEl;
  var nextEl;
  var lastDownEl;
  var cloneEl;
  var cloneHidden;
  var oldIndex;
  var newIndex;
  var oldDraggableIndex;
  var newDraggableIndex;
  var activeGroup;
  var putSortable;
  var awaitingDragStarted = false;
  var ignoreNextClick = false;
  var sortables = [];
  var tapEvt;
  var touchEvt;
  var lastDx;
  var lastDy;
  var tapDistanceLeft;
  var tapDistanceTop;
  var moved;
  var lastTarget;
  var lastDirection;
  var pastFirstInvertThresh = false;
  var isCircumstantialInvert = false;
  var targetMoveDistance;
  var ghostRelativeParent;
  var ghostRelativeParentInitialScroll = [];
  var _silent = false;
  var savedInputChecked = [];
  var documentExists = typeof document !== "undefined";
  var PositionGhostAbsolutely = IOS;
  var CSSFloatProperty = Edge || IE11OrLess ? "cssFloat" : "float";
  var supportDraggable = documentExists && !ChromeForAndroid && !IOS && "draggable" in document.createElement("div");
  var supportCssPointerEvents = function() {
    if (!documentExists)
      return;
    if (IE11OrLess) {
      return false;
    }
    var el = document.createElement("x");
    el.style.cssText = "pointer-events:auto";
    return el.style.pointerEvents === "auto";
  }();
  var _detectDirection = function _detectDirection2(el, options) {
    var elCSS = css(el), elWidth = parseInt(elCSS.width) - parseInt(elCSS.paddingLeft) - parseInt(elCSS.paddingRight) - parseInt(elCSS.borderLeftWidth) - parseInt(elCSS.borderRightWidth), child1 = getChild(el, 0, options), child2 = getChild(el, 1, options), firstChildCSS = child1 && css(child1), secondChildCSS = child2 && css(child2), firstChildWidth = firstChildCSS && parseInt(firstChildCSS.marginLeft) + parseInt(firstChildCSS.marginRight) + getRect(child1).width, secondChildWidth = secondChildCSS && parseInt(secondChildCSS.marginLeft) + parseInt(secondChildCSS.marginRight) + getRect(child2).width;
    if (elCSS.display === "flex") {
      return elCSS.flexDirection === "column" || elCSS.flexDirection === "column-reverse" ? "vertical" : "horizontal";
    }
    if (elCSS.display === "grid") {
      return elCSS.gridTemplateColumns.split(" ").length <= 1 ? "vertical" : "horizontal";
    }
    if (child1 && firstChildCSS["float"] && firstChildCSS["float"] !== "none") {
      var touchingSideChild2 = firstChildCSS["float"] === "left" ? "left" : "right";
      return child2 && (secondChildCSS.clear === "both" || secondChildCSS.clear === touchingSideChild2) ? "vertical" : "horizontal";
    }
    return child1 && (firstChildCSS.display === "block" || firstChildCSS.display === "flex" || firstChildCSS.display === "table" || firstChildCSS.display === "grid" || firstChildWidth >= elWidth && elCSS[CSSFloatProperty] === "none" || child2 && elCSS[CSSFloatProperty] === "none" && firstChildWidth + secondChildWidth > elWidth) ? "vertical" : "horizontal";
  };
  var _dragElInRowColumn = function _dragElInRowColumn2(dragRect, targetRect, vertical) {
    var dragElS1Opp = vertical ? dragRect.left : dragRect.top, dragElS2Opp = vertical ? dragRect.right : dragRect.bottom, dragElOppLength = vertical ? dragRect.width : dragRect.height, targetS1Opp = vertical ? targetRect.left : targetRect.top, targetS2Opp = vertical ? targetRect.right : targetRect.bottom, targetOppLength = vertical ? targetRect.width : targetRect.height;
    return dragElS1Opp === targetS1Opp || dragElS2Opp === targetS2Opp || dragElS1Opp + dragElOppLength / 2 === targetS1Opp + targetOppLength / 2;
  };
  var _detectNearestEmptySortable = function _detectNearestEmptySortable2(x, y) {
    var ret;
    sortables.some(function(sortable) {
      var threshold = sortable[expando].options.emptyInsertThreshold;
      if (!threshold || lastChild(sortable))
        return;
      var rect = getRect(sortable), insideHorizontally = x >= rect.left - threshold && x <= rect.right + threshold, insideVertically = y >= rect.top - threshold && y <= rect.bottom + threshold;
      if (insideHorizontally && insideVertically) {
        return ret = sortable;
      }
    });
    return ret;
  };
  var _prepareGroup = function _prepareGroup2(options) {
    function toFn(value, pull) {
      return function(to, from, dragEl2, evt) {
        var sameGroup = to.options.group.name && from.options.group.name && to.options.group.name === from.options.group.name;
        if (value == null && (pull || sameGroup)) {
          return true;
        } else if (value == null || value === false) {
          return false;
        } else if (pull && value === "clone") {
          return value;
        } else if (typeof value === "function") {
          return toFn(value(to, from, dragEl2, evt), pull)(to, from, dragEl2, evt);
        } else {
          var otherGroup = (pull ? to : from).options.group.name;
          return value === true || typeof value === "string" && value === otherGroup || value.join && value.indexOf(otherGroup) > -1;
        }
      };
    }
    var group = {};
    var originalGroup = options.group;
    if (!originalGroup || _typeof(originalGroup) != "object") {
      originalGroup = {
        name: originalGroup
      };
    }
    group.name = originalGroup.name;
    group.checkPull = toFn(originalGroup.pull, true);
    group.checkPut = toFn(originalGroup.put);
    group.revertClone = originalGroup.revertClone;
    options.group = group;
  };
  var _hideGhostForTarget = function _hideGhostForTarget2() {
    if (!supportCssPointerEvents && ghostEl) {
      css(ghostEl, "display", "none");
    }
  };
  var _unhideGhostForTarget = function _unhideGhostForTarget2() {
    if (!supportCssPointerEvents && ghostEl) {
      css(ghostEl, "display", "");
    }
  };
  if (documentExists && !ChromeForAndroid) {
    document.addEventListener("click", function(evt) {
      if (ignoreNextClick) {
        evt.preventDefault();
        evt.stopPropagation && evt.stopPropagation();
        evt.stopImmediatePropagation && evt.stopImmediatePropagation();
        ignoreNextClick = false;
        return false;
      }
    }, true);
  }
  var nearestEmptyInsertDetectEvent = function nearestEmptyInsertDetectEvent2(evt) {
    if (dragEl) {
      evt = evt.touches ? evt.touches[0] : evt;
      var nearest = _detectNearestEmptySortable(evt.clientX, evt.clientY);
      if (nearest) {
        var event = {};
        for (var i in evt) {
          if (evt.hasOwnProperty(i)) {
            event[i] = evt[i];
          }
        }
        event.target = event.rootEl = nearest;
        event.preventDefault = void 0;
        event.stopPropagation = void 0;
        nearest[expando]._onDragOver(event);
      }
    }
  };
  var _checkOutsideTargetEl = function _checkOutsideTargetEl2(evt) {
    if (dragEl) {
      dragEl.parentNode[expando]._isOutsideThisEl(evt.target);
    }
  };
  function Sortable(el, options) {
    if (!(el && el.nodeType && el.nodeType === 1)) {
      throw "Sortable: `el` must be an HTMLElement, not ".concat({}.toString.call(el));
    }
    this.el = el;
    this.options = options = _extends({}, options);
    el[expando] = this;
    var defaults2 = {
      group: null,
      sort: true,
      disabled: false,
      store: null,
      handle: null,
      draggable: /^[uo]l$/i.test(el.nodeName) ? ">li" : ">*",
      swapThreshold: 1,
      invertSwap: false,
      invertedSwapThreshold: null,
      removeCloneOnHide: true,
      direction: function direction() {
        return _detectDirection(el, this.options);
      },
      ghostClass: "sortable-ghost",
      chosenClass: "sortable-chosen",
      dragClass: "sortable-drag",
      ignore: "a, img",
      filter: null,
      preventOnFilter: true,
      animation: 0,
      easing: null,
      setData: function setData(dataTransfer, dragEl2) {
        dataTransfer.setData("Text", dragEl2.textContent);
      },
      dropBubble: false,
      dragoverBubble: false,
      dataIdAttr: "data-id",
      delay: 0,
      delayOnTouchOnly: false,
      touchStartThreshold: (Number.parseInt ? Number : window).parseInt(window.devicePixelRatio, 10) || 1,
      forceFallback: false,
      fallbackClass: "sortable-fallback",
      fallbackOnBody: false,
      fallbackTolerance: 0,
      fallbackOffset: {
        x: 0,
        y: 0
      },
      supportPointer: Sortable.supportPointer !== false && "PointerEvent" in window && !Safari,
      emptyInsertThreshold: 5
    };
    PluginManager.initializePlugins(this, el, defaults2);
    for (var name in defaults2) {
      !(name in options) && (options[name] = defaults2[name]);
    }
    _prepareGroup(options);
    for (var fn in this) {
      if (fn.charAt(0) === "_" && typeof this[fn] === "function") {
        this[fn] = this[fn].bind(this);
      }
    }
    this.nativeDraggable = options.forceFallback ? false : supportDraggable;
    if (this.nativeDraggable) {
      this.options.touchStartThreshold = 1;
    }
    if (options.supportPointer) {
      on3(el, "pointerdown", this._onTapStart);
    } else {
      on3(el, "mousedown", this._onTapStart);
      on3(el, "touchstart", this._onTapStart);
    }
    if (this.nativeDraggable) {
      on3(el, "dragover", this);
      on3(el, "dragenter", this);
    }
    sortables.push(this.el);
    options.store && options.store.get && this.sort(options.store.get(this) || []);
    _extends(this, AnimationStateManager());
  }
  Sortable.prototype = {
    constructor: Sortable,
    _isOutsideThisEl: function _isOutsideThisEl(target) {
      if (!this.el.contains(target) && target !== this.el) {
        lastTarget = null;
      }
    },
    _getDirection: function _getDirection(evt, target) {
      return typeof this.options.direction === "function" ? this.options.direction.call(this, evt, target, dragEl) : this.options.direction;
    },
    _onTapStart: function _onTapStart(evt) {
      if (!evt.cancelable)
        return;
      var _this = this, el = this.el, options = this.options, preventOnFilter = options.preventOnFilter, type = evt.type, touch = evt.touches && evt.touches[0] || evt.pointerType && evt.pointerType === "touch" && evt, target = (touch || evt).target, originalTarget = evt.target.shadowRoot && (evt.path && evt.path[0] || evt.composedPath && evt.composedPath()[0]) || target, filter = options.filter;
      _saveInputCheckedState(el);
      if (dragEl) {
        return;
      }
      if (/mousedown|pointerdown/.test(type) && evt.button !== 0 || options.disabled) {
        return;
      }
      if (originalTarget.isContentEditable) {
        return;
      }
      if (!this.nativeDraggable && Safari && target && target.tagName.toUpperCase() === "SELECT") {
        return;
      }
      target = closest(target, options.draggable, el, false);
      if (target && target.animated) {
        return;
      }
      if (lastDownEl === target) {
        return;
      }
      oldIndex = index(target);
      oldDraggableIndex = index(target, options.draggable);
      if (typeof filter === "function") {
        if (filter.call(this, evt, target, this)) {
          _dispatchEvent({
            sortable: _this,
            rootEl: originalTarget,
            name: "filter",
            targetEl: target,
            toEl: el,
            fromEl: el
          });
          pluginEvent2("filter", _this, {
            evt
          });
          preventOnFilter && evt.cancelable && evt.preventDefault();
          return;
        }
      } else if (filter) {
        filter = filter.split(",").some(function(criteria) {
          criteria = closest(originalTarget, criteria.trim(), el, false);
          if (criteria) {
            _dispatchEvent({
              sortable: _this,
              rootEl: criteria,
              name: "filter",
              targetEl: target,
              fromEl: el,
              toEl: el
            });
            pluginEvent2("filter", _this, {
              evt
            });
            return true;
          }
        });
        if (filter) {
          preventOnFilter && evt.cancelable && evt.preventDefault();
          return;
        }
      }
      if (options.handle && !closest(originalTarget, options.handle, el, false)) {
        return;
      }
      this._prepareDragStart(evt, touch, target);
    },
    _prepareDragStart: function _prepareDragStart(evt, touch, target) {
      var _this = this, el = _this.el, options = _this.options, ownerDocument = el.ownerDocument, dragStartFn;
      if (target && !dragEl && target.parentNode === el) {
        var dragRect = getRect(target);
        rootEl = el;
        dragEl = target;
        parentEl = dragEl.parentNode;
        nextEl = dragEl.nextSibling;
        lastDownEl = target;
        activeGroup = options.group;
        Sortable.dragged = dragEl;
        tapEvt = {
          target: dragEl,
          clientX: (touch || evt).clientX,
          clientY: (touch || evt).clientY
        };
        tapDistanceLeft = tapEvt.clientX - dragRect.left;
        tapDistanceTop = tapEvt.clientY - dragRect.top;
        this._lastX = (touch || evt).clientX;
        this._lastY = (touch || evt).clientY;
        dragEl.style["will-change"] = "all";
        dragStartFn = function dragStartFn2() {
          pluginEvent2("delayEnded", _this, {
            evt
          });
          if (Sortable.eventCanceled) {
            _this._onDrop();
            return;
          }
          _this._disableDelayedDragEvents();
          if (!FireFox && _this.nativeDraggable) {
            dragEl.draggable = true;
          }
          _this._triggerDragStart(evt, touch);
          _dispatchEvent({
            sortable: _this,
            name: "choose",
            originalEvent: evt
          });
          toggleClass(dragEl, options.chosenClass, true);
        };
        options.ignore.split(",").forEach(function(criteria) {
          find2(dragEl, criteria.trim(), _disableDraggable);
        });
        on3(ownerDocument, "dragover", nearestEmptyInsertDetectEvent);
        on3(ownerDocument, "mousemove", nearestEmptyInsertDetectEvent);
        on3(ownerDocument, "touchmove", nearestEmptyInsertDetectEvent);
        on3(ownerDocument, "mouseup", _this._onDrop);
        on3(ownerDocument, "touchend", _this._onDrop);
        on3(ownerDocument, "touchcancel", _this._onDrop);
        if (FireFox && this.nativeDraggable) {
          this.options.touchStartThreshold = 4;
          dragEl.draggable = true;
        }
        pluginEvent2("delayStart", this, {
          evt
        });
        if (options.delay && (!options.delayOnTouchOnly || touch) && (!this.nativeDraggable || !(Edge || IE11OrLess))) {
          if (Sortable.eventCanceled) {
            this._onDrop();
            return;
          }
          on3(ownerDocument, "mouseup", _this._disableDelayedDrag);
          on3(ownerDocument, "touchend", _this._disableDelayedDrag);
          on3(ownerDocument, "touchcancel", _this._disableDelayedDrag);
          on3(ownerDocument, "mousemove", _this._delayedDragTouchMoveHandler);
          on3(ownerDocument, "touchmove", _this._delayedDragTouchMoveHandler);
          options.supportPointer && on3(ownerDocument, "pointermove", _this._delayedDragTouchMoveHandler);
          _this._dragStartTimer = setTimeout(dragStartFn, options.delay);
        } else {
          dragStartFn();
        }
      }
    },
    _delayedDragTouchMoveHandler: function _delayedDragTouchMoveHandler(e) {
      var touch = e.touches ? e.touches[0] : e;
      if (Math.max(Math.abs(touch.clientX - this._lastX), Math.abs(touch.clientY - this._lastY)) >= Math.floor(this.options.touchStartThreshold / (this.nativeDraggable && window.devicePixelRatio || 1))) {
        this._disableDelayedDrag();
      }
    },
    _disableDelayedDrag: function _disableDelayedDrag() {
      dragEl && _disableDraggable(dragEl);
      clearTimeout(this._dragStartTimer);
      this._disableDelayedDragEvents();
    },
    _disableDelayedDragEvents: function _disableDelayedDragEvents() {
      var ownerDocument = this.el.ownerDocument;
      off(ownerDocument, "mouseup", this._disableDelayedDrag);
      off(ownerDocument, "touchend", this._disableDelayedDrag);
      off(ownerDocument, "touchcancel", this._disableDelayedDrag);
      off(ownerDocument, "mousemove", this._delayedDragTouchMoveHandler);
      off(ownerDocument, "touchmove", this._delayedDragTouchMoveHandler);
      off(ownerDocument, "pointermove", this._delayedDragTouchMoveHandler);
    },
    _triggerDragStart: function _triggerDragStart(evt, touch) {
      touch = touch || evt.pointerType == "touch" && evt;
      if (!this.nativeDraggable || touch) {
        if (this.options.supportPointer) {
          on3(document, "pointermove", this._onTouchMove);
        } else if (touch) {
          on3(document, "touchmove", this._onTouchMove);
        } else {
          on3(document, "mousemove", this._onTouchMove);
        }
      } else {
        on3(dragEl, "dragend", this);
        on3(rootEl, "dragstart", this._onDragStart);
      }
      try {
        if (document.selection) {
          _nextTick(function() {
            document.selection.empty();
          });
        } else {
          window.getSelection().removeAllRanges();
        }
      } catch (err) {
      }
    },
    _dragStarted: function _dragStarted(fallback2, evt) {
      awaitingDragStarted = false;
      if (rootEl && dragEl) {
        pluginEvent2("dragStarted", this, {
          evt
        });
        if (this.nativeDraggable) {
          on3(document, "dragover", _checkOutsideTargetEl);
        }
        var options = this.options;
        !fallback2 && toggleClass(dragEl, options.dragClass, false);
        toggleClass(dragEl, options.ghostClass, true);
        Sortable.active = this;
        fallback2 && this._appendGhost();
        _dispatchEvent({
          sortable: this,
          name: "start",
          originalEvent: evt
        });
      } else {
        this._nulling();
      }
    },
    _emulateDragOver: function _emulateDragOver() {
      if (touchEvt) {
        this._lastX = touchEvt.clientX;
        this._lastY = touchEvt.clientY;
        _hideGhostForTarget();
        var target = document.elementFromPoint(touchEvt.clientX, touchEvt.clientY);
        var parent = target;
        while (target && target.shadowRoot) {
          target = target.shadowRoot.elementFromPoint(touchEvt.clientX, touchEvt.clientY);
          if (target === parent)
            break;
          parent = target;
        }
        dragEl.parentNode[expando]._isOutsideThisEl(target);
        if (parent) {
          do {
            if (parent[expando]) {
              var inserted = void 0;
              inserted = parent[expando]._onDragOver({
                clientX: touchEvt.clientX,
                clientY: touchEvt.clientY,
                target,
                rootEl: parent
              });
              if (inserted && !this.options.dragoverBubble) {
                break;
              }
            }
            target = parent;
          } while (parent = parent.parentNode);
        }
        _unhideGhostForTarget();
      }
    },
    _onTouchMove: function _onTouchMove(evt) {
      if (tapEvt) {
        var options = this.options, fallbackTolerance = options.fallbackTolerance, fallbackOffset = options.fallbackOffset, touch = evt.touches ? evt.touches[0] : evt, ghostMatrix = ghostEl && matrix(ghostEl, true), scaleX = ghostEl && ghostMatrix && ghostMatrix.a, scaleY = ghostEl && ghostMatrix && ghostMatrix.d, relativeScrollOffset = PositionGhostAbsolutely && ghostRelativeParent && getRelativeScrollOffset(ghostRelativeParent), dx = (touch.clientX - tapEvt.clientX + fallbackOffset.x) / (scaleX || 1) + (relativeScrollOffset ? relativeScrollOffset[0] - ghostRelativeParentInitialScroll[0] : 0) / (scaleX || 1), dy = (touch.clientY - tapEvt.clientY + fallbackOffset.y) / (scaleY || 1) + (relativeScrollOffset ? relativeScrollOffset[1] - ghostRelativeParentInitialScroll[1] : 0) / (scaleY || 1);
        if (!Sortable.active && !awaitingDragStarted) {
          if (fallbackTolerance && Math.max(Math.abs(touch.clientX - this._lastX), Math.abs(touch.clientY - this._lastY)) < fallbackTolerance) {
            return;
          }
          this._onDragStart(evt, true);
        }
        if (ghostEl) {
          if (ghostMatrix) {
            ghostMatrix.e += dx - (lastDx || 0);
            ghostMatrix.f += dy - (lastDy || 0);
          } else {
            ghostMatrix = {
              a: 1,
              b: 0,
              c: 0,
              d: 1,
              e: dx,
              f: dy
            };
          }
          var cssMatrix = "matrix(".concat(ghostMatrix.a, ",").concat(ghostMatrix.b, ",").concat(ghostMatrix.c, ",").concat(ghostMatrix.d, ",").concat(ghostMatrix.e, ",").concat(ghostMatrix.f, ")");
          css(ghostEl, "webkitTransform", cssMatrix);
          css(ghostEl, "mozTransform", cssMatrix);
          css(ghostEl, "msTransform", cssMatrix);
          css(ghostEl, "transform", cssMatrix);
          lastDx = dx;
          lastDy = dy;
          touchEvt = touch;
        }
        evt.cancelable && evt.preventDefault();
      }
    },
    _appendGhost: function _appendGhost() {
      if (!ghostEl) {
        var container = this.options.fallbackOnBody ? document.body : rootEl, rect = getRect(dragEl, true, PositionGhostAbsolutely, true, container), options = this.options;
        if (PositionGhostAbsolutely) {
          ghostRelativeParent = container;
          while (css(ghostRelativeParent, "position") === "static" && css(ghostRelativeParent, "transform") === "none" && ghostRelativeParent !== document) {
            ghostRelativeParent = ghostRelativeParent.parentNode;
          }
          if (ghostRelativeParent !== document.body && ghostRelativeParent !== document.documentElement) {
            if (ghostRelativeParent === document)
              ghostRelativeParent = getWindowScrollingElement();
            rect.top += ghostRelativeParent.scrollTop;
            rect.left += ghostRelativeParent.scrollLeft;
          } else {
            ghostRelativeParent = getWindowScrollingElement();
          }
          ghostRelativeParentInitialScroll = getRelativeScrollOffset(ghostRelativeParent);
        }
        ghostEl = dragEl.cloneNode(true);
        toggleClass(ghostEl, options.ghostClass, false);
        toggleClass(ghostEl, options.fallbackClass, true);
        toggleClass(ghostEl, options.dragClass, true);
        css(ghostEl, "transition", "");
        css(ghostEl, "transform", "");
        css(ghostEl, "box-sizing", "border-box");
        css(ghostEl, "margin", 0);
        css(ghostEl, "top", rect.top);
        css(ghostEl, "left", rect.left);
        css(ghostEl, "width", rect.width);
        css(ghostEl, "height", rect.height);
        css(ghostEl, "opacity", "0.8");
        css(ghostEl, "position", PositionGhostAbsolutely ? "absolute" : "fixed");
        css(ghostEl, "zIndex", "100000");
        css(ghostEl, "pointerEvents", "none");
        Sortable.ghost = ghostEl;
        container.appendChild(ghostEl);
        css(ghostEl, "transform-origin", tapDistanceLeft / parseInt(ghostEl.style.width) * 100 + "% " + tapDistanceTop / parseInt(ghostEl.style.height) * 100 + "%");
      }
    },
    _onDragStart: function _onDragStart(evt, fallback2) {
      var _this = this;
      var dataTransfer = evt.dataTransfer;
      var options = _this.options;
      pluginEvent2("dragStart", this, {
        evt
      });
      if (Sortable.eventCanceled) {
        this._onDrop();
        return;
      }
      pluginEvent2("setupClone", this);
      if (!Sortable.eventCanceled) {
        cloneEl = clone(dragEl);
        cloneEl.removeAttribute("id");
        cloneEl.draggable = false;
        cloneEl.style["will-change"] = "";
        this._hideClone();
        toggleClass(cloneEl, this.options.chosenClass, false);
        Sortable.clone = cloneEl;
      }
      _this.cloneId = _nextTick(function() {
        pluginEvent2("clone", _this);
        if (Sortable.eventCanceled)
          return;
        if (!_this.options.removeCloneOnHide) {
          rootEl.insertBefore(cloneEl, dragEl);
        }
        _this._hideClone();
        _dispatchEvent({
          sortable: _this,
          name: "clone"
        });
      });
      !fallback2 && toggleClass(dragEl, options.dragClass, true);
      if (fallback2) {
        ignoreNextClick = true;
        _this._loopId = setInterval(_this._emulateDragOver, 50);
      } else {
        off(document, "mouseup", _this._onDrop);
        off(document, "touchend", _this._onDrop);
        off(document, "touchcancel", _this._onDrop);
        if (dataTransfer) {
          dataTransfer.effectAllowed = "move";
          options.setData && options.setData.call(_this, dataTransfer, dragEl);
        }
        on3(document, "drop", _this);
        css(dragEl, "transform", "translateZ(0)");
      }
      awaitingDragStarted = true;
      _this._dragStartId = _nextTick(_this._dragStarted.bind(_this, fallback2, evt));
      on3(document, "selectstart", _this);
      moved = true;
      if (Safari) {
        css(document.body, "user-select", "none");
      }
    },
    _onDragOver: function _onDragOver(evt) {
      var el = this.el, target = evt.target, dragRect, targetRect, revert, options = this.options, group = options.group, activeSortable = Sortable.active, isOwner = activeGroup === group, canSort = options.sort, fromSortable = putSortable || activeSortable, vertical, _this = this, completedFired = false;
      if (_silent)
        return;
      function dragOverEvent(name, extra) {
        pluginEvent2(name, _this, _objectSpread22({
          evt,
          isOwner,
          axis: vertical ? "vertical" : "horizontal",
          revert,
          dragRect,
          targetRect,
          canSort,
          fromSortable,
          target,
          completed,
          onMove: function onMove(target2, after2) {
            return _onMove(rootEl, el, dragEl, dragRect, target2, getRect(target2), evt, after2);
          },
          changed
        }, extra));
      }
      function capture() {
        dragOverEvent("dragOverAnimationCapture");
        _this.captureAnimationState();
        if (_this !== fromSortable) {
          fromSortable.captureAnimationState();
        }
      }
      function completed(insertion) {
        dragOverEvent("dragOverCompleted", {
          insertion
        });
        if (insertion) {
          if (isOwner) {
            activeSortable._hideClone();
          } else {
            activeSortable._showClone(_this);
          }
          if (_this !== fromSortable) {
            toggleClass(dragEl, putSortable ? putSortable.options.ghostClass : activeSortable.options.ghostClass, false);
            toggleClass(dragEl, options.ghostClass, true);
          }
          if (putSortable !== _this && _this !== Sortable.active) {
            putSortable = _this;
          } else if (_this === Sortable.active && putSortable) {
            putSortable = null;
          }
          if (fromSortable === _this) {
            _this._ignoreWhileAnimating = target;
          }
          _this.animateAll(function() {
            dragOverEvent("dragOverAnimationComplete");
            _this._ignoreWhileAnimating = null;
          });
          if (_this !== fromSortable) {
            fromSortable.animateAll();
            fromSortable._ignoreWhileAnimating = null;
          }
        }
        if (target === dragEl && !dragEl.animated || target === el && !target.animated) {
          lastTarget = null;
        }
        if (!options.dragoverBubble && !evt.rootEl && target !== document) {
          dragEl.parentNode[expando]._isOutsideThisEl(evt.target);
          !insertion && nearestEmptyInsertDetectEvent(evt);
        }
        !options.dragoverBubble && evt.stopPropagation && evt.stopPropagation();
        return completedFired = true;
      }
      function changed() {
        newIndex = index(dragEl);
        newDraggableIndex = index(dragEl, options.draggable);
        _dispatchEvent({
          sortable: _this,
          name: "change",
          toEl: el,
          newIndex,
          newDraggableIndex,
          originalEvent: evt
        });
      }
      if (evt.preventDefault !== void 0) {
        evt.cancelable && evt.preventDefault();
      }
      target = closest(target, options.draggable, el, true);
      dragOverEvent("dragOver");
      if (Sortable.eventCanceled)
        return completedFired;
      if (dragEl.contains(evt.target) || target.animated && target.animatingX && target.animatingY || _this._ignoreWhileAnimating === target) {
        return completed(false);
      }
      ignoreNextClick = false;
      if (activeSortable && !options.disabled && (isOwner ? canSort || (revert = parentEl !== rootEl) : putSortable === this || (this.lastPutMode = activeGroup.checkPull(this, activeSortable, dragEl, evt)) && group.checkPut(this, activeSortable, dragEl, evt))) {
        vertical = this._getDirection(evt, target) === "vertical";
        dragRect = getRect(dragEl);
        dragOverEvent("dragOverValid");
        if (Sortable.eventCanceled)
          return completedFired;
        if (revert) {
          parentEl = rootEl;
          capture();
          this._hideClone();
          dragOverEvent("revert");
          if (!Sortable.eventCanceled) {
            if (nextEl) {
              rootEl.insertBefore(dragEl, nextEl);
            } else {
              rootEl.appendChild(dragEl);
            }
          }
          return completed(true);
        }
        var elLastChild = lastChild(el, options.draggable);
        if (!elLastChild || _ghostIsLast(evt, vertical, this) && !elLastChild.animated) {
          if (elLastChild === dragEl) {
            return completed(false);
          }
          if (elLastChild && el === evt.target) {
            target = elLastChild;
          }
          if (target) {
            targetRect = getRect(target);
          }
          if (_onMove(rootEl, el, dragEl, dragRect, target, targetRect, evt, !!target) !== false) {
            capture();
            if (elLastChild && elLastChild.nextSibling) {
              el.insertBefore(dragEl, elLastChild.nextSibling);
            } else {
              el.appendChild(dragEl);
            }
            parentEl = el;
            changed();
            return completed(true);
          }
        } else if (elLastChild && _ghostIsFirst(evt, vertical, this)) {
          var firstChild = getChild(el, 0, options, true);
          if (firstChild === dragEl) {
            return completed(false);
          }
          target = firstChild;
          targetRect = getRect(target);
          if (_onMove(rootEl, el, dragEl, dragRect, target, targetRect, evt, false) !== false) {
            capture();
            el.insertBefore(dragEl, firstChild);
            parentEl = el;
            changed();
            return completed(true);
          }
        } else if (target.parentNode === el) {
          targetRect = getRect(target);
          var direction = 0, targetBeforeFirstSwap, differentLevel = dragEl.parentNode !== el, differentRowCol = !_dragElInRowColumn(dragEl.animated && dragEl.toRect || dragRect, target.animated && target.toRect || targetRect, vertical), side1 = vertical ? "top" : "left", scrolledPastTop = isScrolledPast(target, "top", "top") || isScrolledPast(dragEl, "top", "top"), scrollBefore = scrolledPastTop ? scrolledPastTop.scrollTop : void 0;
          if (lastTarget !== target) {
            targetBeforeFirstSwap = targetRect[side1];
            pastFirstInvertThresh = false;
            isCircumstantialInvert = !differentRowCol && options.invertSwap || differentLevel;
          }
          direction = _getSwapDirection(evt, target, targetRect, vertical, differentRowCol ? 1 : options.swapThreshold, options.invertedSwapThreshold == null ? options.swapThreshold : options.invertedSwapThreshold, isCircumstantialInvert, lastTarget === target);
          var sibling;
          if (direction !== 0) {
            var dragIndex = index(dragEl);
            do {
              dragIndex -= direction;
              sibling = parentEl.children[dragIndex];
            } while (sibling && (css(sibling, "display") === "none" || sibling === ghostEl));
          }
          if (direction === 0 || sibling === target) {
            return completed(false);
          }
          lastTarget = target;
          lastDirection = direction;
          var nextSibling = target.nextElementSibling, after = false;
          after = direction === 1;
          var moveVector = _onMove(rootEl, el, dragEl, dragRect, target, targetRect, evt, after);
          if (moveVector !== false) {
            if (moveVector === 1 || moveVector === -1) {
              after = moveVector === 1;
            }
            _silent = true;
            setTimeout(_unsilent, 30);
            capture();
            if (after && !nextSibling) {
              el.appendChild(dragEl);
            } else {
              target.parentNode.insertBefore(dragEl, after ? nextSibling : target);
            }
            if (scrolledPastTop) {
              scrollBy(scrolledPastTop, 0, scrollBefore - scrolledPastTop.scrollTop);
            }
            parentEl = dragEl.parentNode;
            if (targetBeforeFirstSwap !== void 0 && !isCircumstantialInvert) {
              targetMoveDistance = Math.abs(targetBeforeFirstSwap - getRect(target)[side1]);
            }
            changed();
            return completed(true);
          }
        }
        if (el.contains(dragEl)) {
          return completed(false);
        }
      }
      return false;
    },
    _ignoreWhileAnimating: null,
    _offMoveEvents: function _offMoveEvents() {
      off(document, "mousemove", this._onTouchMove);
      off(document, "touchmove", this._onTouchMove);
      off(document, "pointermove", this._onTouchMove);
      off(document, "dragover", nearestEmptyInsertDetectEvent);
      off(document, "mousemove", nearestEmptyInsertDetectEvent);
      off(document, "touchmove", nearestEmptyInsertDetectEvent);
    },
    _offUpEvents: function _offUpEvents() {
      var ownerDocument = this.el.ownerDocument;
      off(ownerDocument, "mouseup", this._onDrop);
      off(ownerDocument, "touchend", this._onDrop);
      off(ownerDocument, "pointerup", this._onDrop);
      off(ownerDocument, "touchcancel", this._onDrop);
      off(document, "selectstart", this);
    },
    _onDrop: function _onDrop(evt) {
      var el = this.el, options = this.options;
      newIndex = index(dragEl);
      newDraggableIndex = index(dragEl, options.draggable);
      pluginEvent2("drop", this, {
        evt
      });
      parentEl = dragEl && dragEl.parentNode;
      newIndex = index(dragEl);
      newDraggableIndex = index(dragEl, options.draggable);
      if (Sortable.eventCanceled) {
        this._nulling();
        return;
      }
      awaitingDragStarted = false;
      isCircumstantialInvert = false;
      pastFirstInvertThresh = false;
      clearInterval(this._loopId);
      clearTimeout(this._dragStartTimer);
      _cancelNextTick(this.cloneId);
      _cancelNextTick(this._dragStartId);
      if (this.nativeDraggable) {
        off(document, "drop", this);
        off(el, "dragstart", this._onDragStart);
      }
      this._offMoveEvents();
      this._offUpEvents();
      if (Safari) {
        css(document.body, "user-select", "");
      }
      css(dragEl, "transform", "");
      if (evt) {
        if (moved) {
          evt.cancelable && evt.preventDefault();
          !options.dropBubble && evt.stopPropagation();
        }
        ghostEl && ghostEl.parentNode && ghostEl.parentNode.removeChild(ghostEl);
        if (rootEl === parentEl || putSortable && putSortable.lastPutMode !== "clone") {
          cloneEl && cloneEl.parentNode && cloneEl.parentNode.removeChild(cloneEl);
        }
        if (dragEl) {
          if (this.nativeDraggable) {
            off(dragEl, "dragend", this);
          }
          _disableDraggable(dragEl);
          dragEl.style["will-change"] = "";
          if (moved && !awaitingDragStarted) {
            toggleClass(dragEl, putSortable ? putSortable.options.ghostClass : this.options.ghostClass, false);
          }
          toggleClass(dragEl, this.options.chosenClass, false);
          _dispatchEvent({
            sortable: this,
            name: "unchoose",
            toEl: parentEl,
            newIndex: null,
            newDraggableIndex: null,
            originalEvent: evt
          });
          if (rootEl !== parentEl) {
            if (newIndex >= 0) {
              _dispatchEvent({
                rootEl: parentEl,
                name: "add",
                toEl: parentEl,
                fromEl: rootEl,
                originalEvent: evt
              });
              _dispatchEvent({
                sortable: this,
                name: "remove",
                toEl: parentEl,
                originalEvent: evt
              });
              _dispatchEvent({
                rootEl: parentEl,
                name: "sort",
                toEl: parentEl,
                fromEl: rootEl,
                originalEvent: evt
              });
              _dispatchEvent({
                sortable: this,
                name: "sort",
                toEl: parentEl,
                originalEvent: evt
              });
            }
            putSortable && putSortable.save();
          } else {
            if (newIndex !== oldIndex) {
              if (newIndex >= 0) {
                _dispatchEvent({
                  sortable: this,
                  name: "update",
                  toEl: parentEl,
                  originalEvent: evt
                });
                _dispatchEvent({
                  sortable: this,
                  name: "sort",
                  toEl: parentEl,
                  originalEvent: evt
                });
              }
            }
          }
          if (Sortable.active) {
            if (newIndex == null || newIndex === -1) {
              newIndex = oldIndex;
              newDraggableIndex = oldDraggableIndex;
            }
            _dispatchEvent({
              sortable: this,
              name: "end",
              toEl: parentEl,
              originalEvent: evt
            });
            this.save();
          }
        }
      }
      this._nulling();
    },
    _nulling: function _nulling() {
      pluginEvent2("nulling", this);
      rootEl = dragEl = parentEl = ghostEl = nextEl = cloneEl = lastDownEl = cloneHidden = tapEvt = touchEvt = moved = newIndex = newDraggableIndex = oldIndex = oldDraggableIndex = lastTarget = lastDirection = putSortable = activeGroup = Sortable.dragged = Sortable.ghost = Sortable.clone = Sortable.active = null;
      savedInputChecked.forEach(function(el) {
        el.checked = true;
      });
      savedInputChecked.length = lastDx = lastDy = 0;
    },
    handleEvent: function handleEvent(evt) {
      switch (evt.type) {
        case "drop":
        case "dragend":
          this._onDrop(evt);
          break;
        case "dragenter":
        case "dragover":
          if (dragEl) {
            this._onDragOver(evt);
            _globalDragOver(evt);
          }
          break;
        case "selectstart":
          evt.preventDefault();
          break;
      }
    },
    toArray: function toArray() {
      var order = [], el, children = this.el.children, i = 0, n = children.length, options = this.options;
      for (; i < n; i++) {
        el = children[i];
        if (closest(el, options.draggable, this.el, false)) {
          order.push(el.getAttribute(options.dataIdAttr) || _generateId(el));
        }
      }
      return order;
    },
    sort: function sort(order, useAnimation) {
      var items = {}, rootEl2 = this.el;
      this.toArray().forEach(function(id, i) {
        var el = rootEl2.children[i];
        if (closest(el, this.options.draggable, rootEl2, false)) {
          items[id] = el;
        }
      }, this);
      useAnimation && this.captureAnimationState();
      order.forEach(function(id) {
        if (items[id]) {
          rootEl2.removeChild(items[id]);
          rootEl2.appendChild(items[id]);
        }
      });
      useAnimation && this.animateAll();
    },
    save: function save() {
      var store = this.options.store;
      store && store.set && store.set(this);
    },
    closest: function closest$1(el, selector) {
      return closest(el, selector || this.options.draggable, this.el, false);
    },
    option: function option(name, value) {
      var options = this.options;
      if (value === void 0) {
        return options[name];
      } else {
        var modifiedValue = PluginManager.modifyOption(this, name, value);
        if (typeof modifiedValue !== "undefined") {
          options[name] = modifiedValue;
        } else {
          options[name] = value;
        }
        if (name === "group") {
          _prepareGroup(options);
        }
      }
    },
    destroy: function destroy() {
      pluginEvent2("destroy", this);
      var el = this.el;
      el[expando] = null;
      off(el, "mousedown", this._onTapStart);
      off(el, "touchstart", this._onTapStart);
      off(el, "pointerdown", this._onTapStart);
      if (this.nativeDraggable) {
        off(el, "dragover", this);
        off(el, "dragenter", this);
      }
      Array.prototype.forEach.call(el.querySelectorAll("[draggable]"), function(el2) {
        el2.removeAttribute("draggable");
      });
      this._onDrop();
      this._disableDelayedDragEvents();
      sortables.splice(sortables.indexOf(this.el), 1);
      this.el = el = null;
    },
    _hideClone: function _hideClone() {
      if (!cloneHidden) {
        pluginEvent2("hideClone", this);
        if (Sortable.eventCanceled)
          return;
        css(cloneEl, "display", "none");
        if (this.options.removeCloneOnHide && cloneEl.parentNode) {
          cloneEl.parentNode.removeChild(cloneEl);
        }
        cloneHidden = true;
      }
    },
    _showClone: function _showClone(putSortable2) {
      if (putSortable2.lastPutMode !== "clone") {
        this._hideClone();
        return;
      }
      if (cloneHidden) {
        pluginEvent2("showClone", this);
        if (Sortable.eventCanceled)
          return;
        if (dragEl.parentNode == rootEl && !this.options.group.revertClone) {
          rootEl.insertBefore(cloneEl, dragEl);
        } else if (nextEl) {
          rootEl.insertBefore(cloneEl, nextEl);
        } else {
          rootEl.appendChild(cloneEl);
        }
        if (this.options.group.revertClone) {
          this.animate(dragEl, cloneEl);
        }
        css(cloneEl, "display", "");
        cloneHidden = false;
      }
    }
  };
  function _globalDragOver(evt) {
    if (evt.dataTransfer) {
      evt.dataTransfer.dropEffect = "move";
    }
    evt.cancelable && evt.preventDefault();
  }
  function _onMove(fromEl, toEl, dragEl2, dragRect, targetEl, targetRect, originalEvent, willInsertAfter) {
    var evt, sortable = fromEl[expando], onMoveFn = sortable.options.onMove, retVal;
    if (window.CustomEvent && !IE11OrLess && !Edge) {
      evt = new CustomEvent("move", {
        bubbles: true,
        cancelable: true
      });
    } else {
      evt = document.createEvent("Event");
      evt.initEvent("move", true, true);
    }
    evt.to = toEl;
    evt.from = fromEl;
    evt.dragged = dragEl2;
    evt.draggedRect = dragRect;
    evt.related = targetEl || toEl;
    evt.relatedRect = targetRect || getRect(toEl);
    evt.willInsertAfter = willInsertAfter;
    evt.originalEvent = originalEvent;
    fromEl.dispatchEvent(evt);
    if (onMoveFn) {
      retVal = onMoveFn.call(sortable, evt, originalEvent);
    }
    return retVal;
  }
  function _disableDraggable(el) {
    el.draggable = false;
  }
  function _unsilent() {
    _silent = false;
  }
  function _ghostIsFirst(evt, vertical, sortable) {
    var firstElRect = getRect(getChild(sortable.el, 0, sortable.options, true));
    var childContainingRect = getChildContainingRectFromElement(sortable.el, sortable.options, ghostEl);
    var spacer = 10;
    return vertical ? evt.clientX < childContainingRect.left - spacer || evt.clientY < firstElRect.top && evt.clientX < firstElRect.right : evt.clientY < childContainingRect.top - spacer || evt.clientY < firstElRect.bottom && evt.clientX < firstElRect.left;
  }
  function _ghostIsLast(evt, vertical, sortable) {
    var lastElRect = getRect(lastChild(sortable.el, sortable.options.draggable));
    var childContainingRect = getChildContainingRectFromElement(sortable.el, sortable.options, ghostEl);
    var spacer = 10;
    return vertical ? evt.clientX > childContainingRect.right + spacer || evt.clientY > lastElRect.bottom && evt.clientX > lastElRect.left : evt.clientY > childContainingRect.bottom + spacer || evt.clientX > lastElRect.right && evt.clientY > lastElRect.top;
  }
  function _getSwapDirection(evt, target, targetRect, vertical, swapThreshold, invertedSwapThreshold, invertSwap, isLastTarget) {
    var mouseOnAxis = vertical ? evt.clientY : evt.clientX, targetLength = vertical ? targetRect.height : targetRect.width, targetS1 = vertical ? targetRect.top : targetRect.left, targetS2 = vertical ? targetRect.bottom : targetRect.right, invert = false;
    if (!invertSwap) {
      if (isLastTarget && targetMoveDistance < targetLength * swapThreshold) {
        if (!pastFirstInvertThresh && (lastDirection === 1 ? mouseOnAxis > targetS1 + targetLength * invertedSwapThreshold / 2 : mouseOnAxis < targetS2 - targetLength * invertedSwapThreshold / 2)) {
          pastFirstInvertThresh = true;
        }
        if (!pastFirstInvertThresh) {
          if (lastDirection === 1 ? mouseOnAxis < targetS1 + targetMoveDistance : mouseOnAxis > targetS2 - targetMoveDistance) {
            return -lastDirection;
          }
        } else {
          invert = true;
        }
      } else {
        if (mouseOnAxis > targetS1 + targetLength * (1 - swapThreshold) / 2 && mouseOnAxis < targetS2 - targetLength * (1 - swapThreshold) / 2) {
          return _getInsertDirection(target);
        }
      }
    }
    invert = invert || invertSwap;
    if (invert) {
      if (mouseOnAxis < targetS1 + targetLength * invertedSwapThreshold / 2 || mouseOnAxis > targetS2 - targetLength * invertedSwapThreshold / 2) {
        return mouseOnAxis > targetS1 + targetLength / 2 ? 1 : -1;
      }
    }
    return 0;
  }
  function _getInsertDirection(target) {
    if (index(dragEl) < index(target)) {
      return 1;
    } else {
      return -1;
    }
  }
  function _generateId(el) {
    var str = el.tagName + el.className + el.src + el.href + el.textContent, i = str.length, sum = 0;
    while (i--) {
      sum += str.charCodeAt(i);
    }
    return sum.toString(36);
  }
  function _saveInputCheckedState(root) {
    savedInputChecked.length = 0;
    var inputs = root.getElementsByTagName("input");
    var idx = inputs.length;
    while (idx--) {
      var el = inputs[idx];
      el.checked && savedInputChecked.push(el);
    }
  }
  function _nextTick(fn) {
    return setTimeout(fn, 0);
  }
  function _cancelNextTick(id) {
    return clearTimeout(id);
  }
  if (documentExists) {
    on3(document, "touchmove", function(evt) {
      if ((Sortable.active || awaitingDragStarted) && evt.cancelable) {
        evt.preventDefault();
      }
    });
  }
  Sortable.utils = {
    on: on3,
    off,
    css,
    find: find2,
    is: function is(el, selector) {
      return !!closest(el, selector, el, false);
    },
    extend,
    throttle,
    closest,
    toggleClass,
    clone,
    index,
    nextTick: _nextTick,
    cancelNextTick: _cancelNextTick,
    detectDirection: _detectDirection,
    getChild
  };
  Sortable.get = function(element) {
    return element[expando];
  };
  Sortable.mount = function() {
    for (var _len = arguments.length, plugins2 = new Array(_len), _key = 0; _key < _len; _key++) {
      plugins2[_key] = arguments[_key];
    }
    if (plugins2[0].constructor === Array)
      plugins2 = plugins2[0];
    plugins2.forEach(function(plugin) {
      if (!plugin.prototype || !plugin.prototype.constructor) {
        throw "Sortable: Mounted plugin must be a constructor function, not ".concat({}.toString.call(plugin));
      }
      if (plugin.utils)
        Sortable.utils = _objectSpread22(_objectSpread22({}, Sortable.utils), plugin.utils);
      PluginManager.mount(plugin);
    });
  };
  Sortable.create = function(el, options) {
    return new Sortable(el, options);
  };
  Sortable.version = version;
  var autoScrolls = [];
  var scrollEl;
  var scrollRootEl;
  var scrolling = false;
  var lastAutoScrollX;
  var lastAutoScrollY;
  var touchEvt$1;
  var pointerElemChangedInterval;
  function AutoScrollPlugin() {
    function AutoScroll() {
      this.defaults = {
        scroll: true,
        forceAutoScrollFallback: false,
        scrollSensitivity: 30,
        scrollSpeed: 10,
        bubbleScroll: true
      };
      for (var fn in this) {
        if (fn.charAt(0) === "_" && typeof this[fn] === "function") {
          this[fn] = this[fn].bind(this);
        }
      }
    }
    AutoScroll.prototype = {
      dragStarted: function dragStarted(_ref) {
        var originalEvent = _ref.originalEvent;
        if (this.sortable.nativeDraggable) {
          on3(document, "dragover", this._handleAutoScroll);
        } else {
          if (this.options.supportPointer) {
            on3(document, "pointermove", this._handleFallbackAutoScroll);
          } else if (originalEvent.touches) {
            on3(document, "touchmove", this._handleFallbackAutoScroll);
          } else {
            on3(document, "mousemove", this._handleFallbackAutoScroll);
          }
        }
      },
      dragOverCompleted: function dragOverCompleted(_ref2) {
        var originalEvent = _ref2.originalEvent;
        if (!this.options.dragOverBubble && !originalEvent.rootEl) {
          this._handleAutoScroll(originalEvent);
        }
      },
      drop: function drop3() {
        if (this.sortable.nativeDraggable) {
          off(document, "dragover", this._handleAutoScroll);
        } else {
          off(document, "pointermove", this._handleFallbackAutoScroll);
          off(document, "touchmove", this._handleFallbackAutoScroll);
          off(document, "mousemove", this._handleFallbackAutoScroll);
        }
        clearPointerElemChangedInterval();
        clearAutoScrolls();
        cancelThrottle();
      },
      nulling: function nulling() {
        touchEvt$1 = scrollRootEl = scrollEl = scrolling = pointerElemChangedInterval = lastAutoScrollX = lastAutoScrollY = null;
        autoScrolls.length = 0;
      },
      _handleFallbackAutoScroll: function _handleFallbackAutoScroll(evt) {
        this._handleAutoScroll(evt, true);
      },
      _handleAutoScroll: function _handleAutoScroll(evt, fallback2) {
        var _this = this;
        var x = (evt.touches ? evt.touches[0] : evt).clientX, y = (evt.touches ? evt.touches[0] : evt).clientY, elem = document.elementFromPoint(x, y);
        touchEvt$1 = evt;
        if (fallback2 || this.options.forceAutoScrollFallback || Edge || IE11OrLess || Safari) {
          autoScroll(evt, this.options, elem, fallback2);
          var ogElemScroller = getParentAutoScrollElement(elem, true);
          if (scrolling && (!pointerElemChangedInterval || x !== lastAutoScrollX || y !== lastAutoScrollY)) {
            pointerElemChangedInterval && clearPointerElemChangedInterval();
            pointerElemChangedInterval = setInterval(function() {
              var newElem = getParentAutoScrollElement(document.elementFromPoint(x, y), true);
              if (newElem !== ogElemScroller) {
                ogElemScroller = newElem;
                clearAutoScrolls();
              }
              autoScroll(evt, _this.options, newElem, fallback2);
            }, 10);
            lastAutoScrollX = x;
            lastAutoScrollY = y;
          }
        } else {
          if (!this.options.bubbleScroll || getParentAutoScrollElement(elem, true) === getWindowScrollingElement()) {
            clearAutoScrolls();
            return;
          }
          autoScroll(evt, this.options, getParentAutoScrollElement(elem, false), false);
        }
      }
    };
    return _extends(AutoScroll, {
      pluginName: "scroll",
      initializeByDefault: true
    });
  }
  function clearAutoScrolls() {
    autoScrolls.forEach(function(autoScroll2) {
      clearInterval(autoScroll2.pid);
    });
    autoScrolls = [];
  }
  function clearPointerElemChangedInterval() {
    clearInterval(pointerElemChangedInterval);
  }
  var autoScroll = throttle(function(evt, options, rootEl2, isFallback) {
    if (!options.scroll)
      return;
    var x = (evt.touches ? evt.touches[0] : evt).clientX, y = (evt.touches ? evt.touches[0] : evt).clientY, sens = options.scrollSensitivity, speed = options.scrollSpeed, winScroller = getWindowScrollingElement();
    var scrollThisInstance = false, scrollCustomFn;
    if (scrollRootEl !== rootEl2) {
      scrollRootEl = rootEl2;
      clearAutoScrolls();
      scrollEl = options.scroll;
      scrollCustomFn = options.scrollFn;
      if (scrollEl === true) {
        scrollEl = getParentAutoScrollElement(rootEl2, true);
      }
    }
    var layersOut = 0;
    var currentParent = scrollEl;
    do {
      var el = currentParent, rect = getRect(el), top = rect.top, bottom = rect.bottom, left = rect.left, right = rect.right, width = rect.width, height = rect.height, canScrollX = void 0, canScrollY = void 0, scrollWidth = el.scrollWidth, scrollHeight = el.scrollHeight, elCSS = css(el), scrollPosX = el.scrollLeft, scrollPosY = el.scrollTop;
      if (el === winScroller) {
        canScrollX = width < scrollWidth && (elCSS.overflowX === "auto" || elCSS.overflowX === "scroll" || elCSS.overflowX === "visible");
        canScrollY = height < scrollHeight && (elCSS.overflowY === "auto" || elCSS.overflowY === "scroll" || elCSS.overflowY === "visible");
      } else {
        canScrollX = width < scrollWidth && (elCSS.overflowX === "auto" || elCSS.overflowX === "scroll");
        canScrollY = height < scrollHeight && (elCSS.overflowY === "auto" || elCSS.overflowY === "scroll");
      }
      var vx = canScrollX && (Math.abs(right - x) <= sens && scrollPosX + width < scrollWidth) - (Math.abs(left - x) <= sens && !!scrollPosX);
      var vy = canScrollY && (Math.abs(bottom - y) <= sens && scrollPosY + height < scrollHeight) - (Math.abs(top - y) <= sens && !!scrollPosY);
      if (!autoScrolls[layersOut]) {
        for (var i = 0; i <= layersOut; i++) {
          if (!autoScrolls[i]) {
            autoScrolls[i] = {};
          }
        }
      }
      if (autoScrolls[layersOut].vx != vx || autoScrolls[layersOut].vy != vy || autoScrolls[layersOut].el !== el) {
        autoScrolls[layersOut].el = el;
        autoScrolls[layersOut].vx = vx;
        autoScrolls[layersOut].vy = vy;
        clearInterval(autoScrolls[layersOut].pid);
        if (vx != 0 || vy != 0) {
          scrollThisInstance = true;
          autoScrolls[layersOut].pid = setInterval(function() {
            if (isFallback && this.layer === 0) {
              Sortable.active._onTouchMove(touchEvt$1);
            }
            var scrollOffsetY = autoScrolls[this.layer].vy ? autoScrolls[this.layer].vy * speed : 0;
            var scrollOffsetX = autoScrolls[this.layer].vx ? autoScrolls[this.layer].vx * speed : 0;
            if (typeof scrollCustomFn === "function") {
              if (scrollCustomFn.call(Sortable.dragged.parentNode[expando], scrollOffsetX, scrollOffsetY, evt, touchEvt$1, autoScrolls[this.layer].el) !== "continue") {
                return;
              }
            }
            scrollBy(autoScrolls[this.layer].el, scrollOffsetX, scrollOffsetY);
          }.bind({
            layer: layersOut
          }), 24);
        }
      }
      layersOut++;
    } while (options.bubbleScroll && currentParent !== winScroller && (currentParent = getParentAutoScrollElement(currentParent, false)));
    scrolling = scrollThisInstance;
  }, 30);
  var drop = function drop2(_ref) {
    var originalEvent = _ref.originalEvent, putSortable2 = _ref.putSortable, dragEl2 = _ref.dragEl, activeSortable = _ref.activeSortable, dispatchSortableEvent = _ref.dispatchSortableEvent, hideGhostForTarget = _ref.hideGhostForTarget, unhideGhostForTarget = _ref.unhideGhostForTarget;
    if (!originalEvent)
      return;
    var toSortable = putSortable2 || activeSortable;
    hideGhostForTarget();
    var touch = originalEvent.changedTouches && originalEvent.changedTouches.length ? originalEvent.changedTouches[0] : originalEvent;
    var target = document.elementFromPoint(touch.clientX, touch.clientY);
    unhideGhostForTarget();
    if (toSortable && !toSortable.el.contains(target)) {
      dispatchSortableEvent("spill");
      this.onSpill({
        dragEl: dragEl2,
        putSortable: putSortable2
      });
    }
  };
  function Revert() {
  }
  Revert.prototype = {
    startIndex: null,
    dragStart: function dragStart(_ref2) {
      var oldDraggableIndex2 = _ref2.oldDraggableIndex;
      this.startIndex = oldDraggableIndex2;
    },
    onSpill: function onSpill(_ref3) {
      var dragEl2 = _ref3.dragEl, putSortable2 = _ref3.putSortable;
      this.sortable.captureAnimationState();
      if (putSortable2) {
        putSortable2.captureAnimationState();
      }
      var nextSibling = getChild(this.sortable.el, this.startIndex, this.options);
      if (nextSibling) {
        this.sortable.el.insertBefore(dragEl2, nextSibling);
      } else {
        this.sortable.el.appendChild(dragEl2);
      }
      this.sortable.animateAll();
      if (putSortable2) {
        putSortable2.animateAll();
      }
    },
    drop
  };
  _extends(Revert, {
    pluginName: "revertOnSpill"
  });
  function Remove() {
  }
  Remove.prototype = {
    onSpill: function onSpill2(_ref4) {
      var dragEl2 = _ref4.dragEl, putSortable2 = _ref4.putSortable;
      var parentSortable = putSortable2 || this.sortable;
      parentSortable.captureAnimationState();
      dragEl2.parentNode && dragEl2.parentNode.removeChild(dragEl2);
      parentSortable.animateAll();
    },
    drop
  };
  _extends(Remove, {
    pluginName: "removeOnSpill"
  });
  Sortable.mount(new AutoScrollPlugin());
  Sortable.mount(Remove, Revert);
  var sortable_esm_default = Sortable;
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
  function src_default5(Alpine24) {
    Alpine24.directive("sort", (el, { value, modifiers, expression }, { effect, evaluate: evaluate2, evaluateLater, cleanup }) => {
      if (value === "config") {
        return;
      }
      if (value === "handle") {
        return;
      }
      if (value === "group") {
        return;
      }
      if (value === "key" || value === "item") {
        if ([void 0, null, ""].includes(expression))
          return;
        el._x_sort_key = evaluate2(expression);
        return;
      }
      let preferences = {
        hideGhost: !modifiers.includes("ghost"),
        useHandles: !!el.querySelector("[x-sort\\:handle],[wire\\:sort\\:handle]"),
        group: getGroupName(el, modifiers)
      };
      let handleSort = generateSortHandler(expression, evaluateLater);
      let config = getConfigurationOverrides(el, modifiers, evaluate2);
      let sortable = initSortable(el, config, preferences, (key, position) => {
        handleSort(key, position);
      });
      cleanup(() => sortable.destroy());
    });
  }
  function generateSortHandler(expression, evaluateLater) {
    if ([void 0, null, ""].includes(expression))
      return () => {
      };
    let handle = evaluateLater(expression);
    return (key, position) => {
      Alpine.dontAutoEvaluateFunctions(() => {
        handle(
          (received) => {
            if (typeof received === "function")
              received(key, position);
          },
          { scope: {
            $key: key,
            $item: key,
            $position: position
          } }
        );
      });
    };
  }
  function getConfigurationOverrides(el, modifiers, evaluate2) {
    if (el.hasAttribute("x-sort:config")) {
      return evaluate2(el.getAttribute("x-sort:config"));
    }
    if (el.hasAttribute("wire:sort:config")) {
      return evaluate2(el.getAttribute("wire:sort:config"));
    }
    return {};
  }
  function initSortable(el, config, preferences, handle) {
    let ghostRef;
    let options = {
      animation: 150,
      handle: preferences.useHandles ? "[x-sort\\:handle],[wire\\:sort\\:handle]" : null,
      group: preferences.group,
      scroll: true,
      forceAutoScrollFallback: true,
      scrollSensitivity: 50,
      preventOnFilter: false,
      filter(e) {
        if (e.target.hasAttribute("x-sort:ignore") || e.target.hasAttribute("wire:sort:ignore"))
          return true;
        if (e.target.closest("[x-sort\\:ignore]") || e.target.closest("[wire\\:sort\\:ignore]"))
          return true;
        if (!el.querySelector("[x-sort\\:item],[wire\\:sort\\:item]"))
          return false;
        let itemHasAttribute = e.target.closest("[x-sort\\:item],[wire\\:sort\\:item]");
        return itemHasAttribute ? false : true;
      },
      onSort(e) {
        if (e.from !== e.to) {
          if (e.to !== e.target) {
            return;
          }
        }
        let key = void 0;
        walk(e.item, (el2, skip) => {
          if (key !== void 0)
            return;
          if (el2._x_sort_key) {
            key = el2._x_sort_key;
            skip();
          }
        });
        let position = e.newIndex;
        if (key !== void 0 || key !== null) {
          handle(key, position);
        }
      },
      onStart() {
        document.body.classList.add("sorting");
        ghostRef = document.querySelector(".sortable-ghost");
        if (preferences.hideGhost && ghostRef)
          ghostRef.style.opacity = "0";
      },
      onEnd() {
        document.body.classList.remove("sorting");
        if (preferences.hideGhost && ghostRef)
          ghostRef.style.opacity = "1";
        ghostRef = void 0;
        keepElementsWithinMorphMarkers(el);
      }
    };
    return new sortable_esm_default(el, { ...options, ...config });
  }
  function keepElementsWithinMorphMarkers(el) {
    let cursor = el.firstChild;
    while (cursor.nextSibling) {
      if (cursor.textContent.trim() === "[if ENDBLOCK]><![endif]") {
        el.append(cursor);
        break;
      }
      cursor = cursor.nextSibling;
    }
  }
  function getGroupName(el, modifiers) {
    if (el.hasAttribute("x-sort:group")) {
      return el.getAttribute("x-sort:group");
    }
    if (el.hasAttribute("wire:sort:group")) {
      return el.getAttribute("wire:sort:group");
    }
    return modifiers.indexOf("group") !== -1 ? modifiers[modifiers.indexOf("group") + 1] : null;
  }
  var module_default5 = src_default5;

  // node_modules/@alpinejs/resize/dist/module.esm.js
  function src_default6(Alpine24) {
    Alpine24.directive("resize", Alpine24.skipDuringClone((el, { value, expression, modifiers }, { evaluateLater, cleanup }) => {
      let evaluator = evaluateLater(expression);
      let evaluate2 = (width, height) => {
        evaluator(() => {
        }, { scope: { "$width": width, "$height": height } });
      };
      let off2 = modifiers.includes("document") ? onDocumentResize(evaluate2) : onElResize(el, evaluate2);
      cleanup(() => off2());
    }));
  }
  function onElResize(el, callback) {
    let observer = new ResizeObserver((entries) => {
      let [width, height] = dimensions(entries);
      callback(width, height);
    });
    observer.observe(el);
    return () => observer.disconnect();
  }
  var documentResizeObserver;
  var documentResizeObserverCallbacks = /* @__PURE__ */ new Set();
  function onDocumentResize(callback) {
    documentResizeObserverCallbacks.add(callback);
    if (!documentResizeObserver) {
      documentResizeObserver = new ResizeObserver((entries) => {
        let [width, height] = dimensions(entries);
        documentResizeObserverCallbacks.forEach((i) => i(width, height));
      });
      documentResizeObserver.observe(document.documentElement);
    }
    return () => {
      documentResizeObserverCallbacks.delete(callback);
    };
  }
  function dimensions(entries) {
    let width, height;
    for (let entry of entries) {
      width = entry.borderBoxSize[0].inlineSize;
      height = entry.borderBoxSize[0].blockSize;
    }
    return [width, height];
  }
  var module_default6 = src_default6;

  // ../alpine/packages/anchor/dist/module.esm.js
  var min = Math.min;
  var max = Math.max;
  var round = Math.round;
  var floor = Math.floor;
  var createCoords = (v) => ({
    x: v,
    y: v
  });
  var oppositeSideMap = {
    left: "right",
    right: "left",
    bottom: "top",
    top: "bottom"
  };
  var oppositeAlignmentMap = {
    start: "end",
    end: "start"
  };
  function clamp(start2, value, end) {
    return max(start2, min(value, end));
  }
  function evaluate(value, param) {
    return typeof value === "function" ? value(param) : value;
  }
  function getSide(placement) {
    return placement.split("-")[0];
  }
  function getAlignment(placement) {
    return placement.split("-")[1];
  }
  function getOppositeAxis(axis) {
    return axis === "x" ? "y" : "x";
  }
  function getAxisLength(axis) {
    return axis === "y" ? "height" : "width";
  }
  function getSideAxis(placement) {
    return ["top", "bottom"].includes(getSide(placement)) ? "y" : "x";
  }
  function getAlignmentAxis(placement) {
    return getOppositeAxis(getSideAxis(placement));
  }
  function getAlignmentSides(placement, rects, rtl) {
    if (rtl === void 0) {
      rtl = false;
    }
    const alignment = getAlignment(placement);
    const alignmentAxis = getAlignmentAxis(placement);
    const length = getAxisLength(alignmentAxis);
    let mainAlignmentSide = alignmentAxis === "x" ? alignment === (rtl ? "end" : "start") ? "right" : "left" : alignment === "start" ? "bottom" : "top";
    if (rects.reference[length] > rects.floating[length]) {
      mainAlignmentSide = getOppositePlacement(mainAlignmentSide);
    }
    return [mainAlignmentSide, getOppositePlacement(mainAlignmentSide)];
  }
  function getExpandedPlacements(placement) {
    const oppositePlacement = getOppositePlacement(placement);
    return [getOppositeAlignmentPlacement(placement), oppositePlacement, getOppositeAlignmentPlacement(oppositePlacement)];
  }
  function getOppositeAlignmentPlacement(placement) {
    return placement.replace(/start|end/g, (alignment) => oppositeAlignmentMap[alignment]);
  }
  function getSideList(side, isStart, rtl) {
    const lr = ["left", "right"];
    const rl = ["right", "left"];
    const tb = ["top", "bottom"];
    const bt = ["bottom", "top"];
    switch (side) {
      case "top":
      case "bottom":
        if (rtl)
          return isStart ? rl : lr;
        return isStart ? lr : rl;
      case "left":
      case "right":
        return isStart ? tb : bt;
      default:
        return [];
    }
  }
  function getOppositeAxisPlacements(placement, flipAlignment, direction, rtl) {
    const alignment = getAlignment(placement);
    let list = getSideList(getSide(placement), direction === "start", rtl);
    if (alignment) {
      list = list.map((side) => side + "-" + alignment);
      if (flipAlignment) {
        list = list.concat(list.map(getOppositeAlignmentPlacement));
      }
    }
    return list;
  }
  function getOppositePlacement(placement) {
    return placement.replace(/left|right|bottom|top/g, (side) => oppositeSideMap[side]);
  }
  function expandPaddingObject(padding) {
    return {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0,
      ...padding
    };
  }
  function getPaddingObject(padding) {
    return typeof padding !== "number" ? expandPaddingObject(padding) : {
      top: padding,
      right: padding,
      bottom: padding,
      left: padding
    };
  }
  function rectToClientRect(rect) {
    return {
      ...rect,
      top: rect.y,
      left: rect.x,
      right: rect.x + rect.width,
      bottom: rect.y + rect.height
    };
  }
  function computeCoordsFromPlacement(_ref, placement, rtl) {
    let {
      reference,
      floating
    } = _ref;
    const sideAxis = getSideAxis(placement);
    const alignmentAxis = getAlignmentAxis(placement);
    const alignLength = getAxisLength(alignmentAxis);
    const side = getSide(placement);
    const isVertical = sideAxis === "y";
    const commonX = reference.x + reference.width / 2 - floating.width / 2;
    const commonY = reference.y + reference.height / 2 - floating.height / 2;
    const commonAlign = reference[alignLength] / 2 - floating[alignLength] / 2;
    let coords;
    switch (side) {
      case "top":
        coords = {
          x: commonX,
          y: reference.y - floating.height
        };
        break;
      case "bottom":
        coords = {
          x: commonX,
          y: reference.y + reference.height
        };
        break;
      case "right":
        coords = {
          x: reference.x + reference.width,
          y: commonY
        };
        break;
      case "left":
        coords = {
          x: reference.x - floating.width,
          y: commonY
        };
        break;
      default:
        coords = {
          x: reference.x,
          y: reference.y
        };
    }
    switch (getAlignment(placement)) {
      case "start":
        coords[alignmentAxis] -= commonAlign * (rtl && isVertical ? -1 : 1);
        break;
      case "end":
        coords[alignmentAxis] += commonAlign * (rtl && isVertical ? -1 : 1);
        break;
    }
    return coords;
  }
  var computePosition = async (reference, floating, config) => {
    const {
      placement = "bottom",
      strategy = "absolute",
      middleware = [],
      platform: platform2
    } = config;
    const validMiddleware = middleware.filter(Boolean);
    const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(floating));
    let rects = await platform2.getElementRects({
      reference,
      floating,
      strategy
    });
    let {
      x,
      y
    } = computeCoordsFromPlacement(rects, placement, rtl);
    let statefulPlacement = placement;
    let middlewareData = {};
    let resetCount = 0;
    for (let i = 0; i < validMiddleware.length; i++) {
      const {
        name,
        fn
      } = validMiddleware[i];
      const {
        x: nextX,
        y: nextY,
        data,
        reset
      } = await fn({
        x,
        y,
        initialPlacement: placement,
        placement: statefulPlacement,
        strategy,
        middlewareData,
        rects,
        platform: platform2,
        elements: {
          reference,
          floating
        }
      });
      x = nextX != null ? nextX : x;
      y = nextY != null ? nextY : y;
      middlewareData = {
        ...middlewareData,
        [name]: {
          ...middlewareData[name],
          ...data
        }
      };
      if (reset && resetCount <= 50) {
        resetCount++;
        if (typeof reset === "object") {
          if (reset.placement) {
            statefulPlacement = reset.placement;
          }
          if (reset.rects) {
            rects = reset.rects === true ? await platform2.getElementRects({
              reference,
              floating,
              strategy
            }) : reset.rects;
          }
          ({
            x,
            y
          } = computeCoordsFromPlacement(rects, statefulPlacement, rtl));
        }
        i = -1;
        continue;
      }
    }
    return {
      x,
      y,
      placement: statefulPlacement,
      strategy,
      middlewareData
    };
  };
  async function detectOverflow(state, options) {
    var _await$platform$isEle;
    if (options === void 0) {
      options = {};
    }
    const {
      x,
      y,
      platform: platform2,
      rects,
      elements,
      strategy
    } = state;
    const {
      boundary = "clippingAncestors",
      rootBoundary = "viewport",
      elementContext = "floating",
      altBoundary = false,
      padding = 0
    } = evaluate(options, state);
    const paddingObject = getPaddingObject(padding);
    const altContext = elementContext === "floating" ? "reference" : "floating";
    const element = elements[altBoundary ? altContext : elementContext];
    const clippingClientRect = rectToClientRect(await platform2.getClippingRect({
      element: ((_await$platform$isEle = await (platform2.isElement == null ? void 0 : platform2.isElement(element))) != null ? _await$platform$isEle : true) ? element : element.contextElement || await (platform2.getDocumentElement == null ? void 0 : platform2.getDocumentElement(elements.floating)),
      boundary,
      rootBoundary,
      strategy
    }));
    const rect = elementContext === "floating" ? {
      ...rects.floating,
      x,
      y
    } : rects.reference;
    const offsetParent = await (platform2.getOffsetParent == null ? void 0 : platform2.getOffsetParent(elements.floating));
    const offsetScale = await (platform2.isElement == null ? void 0 : platform2.isElement(offsetParent)) ? await (platform2.getScale == null ? void 0 : platform2.getScale(offsetParent)) || {
      x: 1,
      y: 1
    } : {
      x: 1,
      y: 1
    };
    const elementClientRect = rectToClientRect(platform2.convertOffsetParentRelativeRectToViewportRelativeRect ? await platform2.convertOffsetParentRelativeRectToViewportRelativeRect({
      rect,
      offsetParent,
      strategy
    }) : rect);
    return {
      top: (clippingClientRect.top - elementClientRect.top + paddingObject.top) / offsetScale.y,
      bottom: (elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom) / offsetScale.y,
      left: (clippingClientRect.left - elementClientRect.left + paddingObject.left) / offsetScale.x,
      right: (elementClientRect.right - clippingClientRect.right + paddingObject.right) / offsetScale.x
    };
  }
  var flip = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "flip",
      options,
      async fn(state) {
        var _middlewareData$arrow, _middlewareData$flip;
        const {
          placement,
          middlewareData,
          rects,
          initialPlacement,
          platform: platform2,
          elements
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = true,
          fallbackPlacements: specifiedFallbackPlacements,
          fallbackStrategy = "bestFit",
          fallbackAxisSideDirection = "none",
          flipAlignment = true,
          ...detectOverflowOptions
        } = evaluate(options, state);
        if ((_middlewareData$arrow = middlewareData.arrow) != null && _middlewareData$arrow.alignmentOffset) {
          return {};
        }
        const side = getSide(placement);
        const isBasePlacement = getSide(initialPlacement) === initialPlacement;
        const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
        const fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipAlignment ? [getOppositePlacement(initialPlacement)] : getExpandedPlacements(initialPlacement));
        if (!specifiedFallbackPlacements && fallbackAxisSideDirection !== "none") {
          fallbackPlacements.push(...getOppositeAxisPlacements(initialPlacement, flipAlignment, fallbackAxisSideDirection, rtl));
        }
        const placements2 = [initialPlacement, ...fallbackPlacements];
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const overflows = [];
        let overflowsData = ((_middlewareData$flip = middlewareData.flip) == null ? void 0 : _middlewareData$flip.overflows) || [];
        if (checkMainAxis) {
          overflows.push(overflow[side]);
        }
        if (checkCrossAxis) {
          const sides2 = getAlignmentSides(placement, rects, rtl);
          overflows.push(overflow[sides2[0]], overflow[sides2[1]]);
        }
        overflowsData = [...overflowsData, {
          placement,
          overflows
        }];
        if (!overflows.every((side2) => side2 <= 0)) {
          var _middlewareData$flip2, _overflowsData$filter;
          const nextIndex = (((_middlewareData$flip2 = middlewareData.flip) == null ? void 0 : _middlewareData$flip2.index) || 0) + 1;
          const nextPlacement = placements2[nextIndex];
          if (nextPlacement) {
            return {
              data: {
                index: nextIndex,
                overflows: overflowsData
              },
              reset: {
                placement: nextPlacement
              }
            };
          }
          let resetPlacement = (_overflowsData$filter = overflowsData.filter((d) => d.overflows[0] <= 0).sort((a, b) => a.overflows[1] - b.overflows[1])[0]) == null ? void 0 : _overflowsData$filter.placement;
          if (!resetPlacement) {
            switch (fallbackStrategy) {
              case "bestFit": {
                var _overflowsData$map$so;
                const placement2 = (_overflowsData$map$so = overflowsData.map((d) => [d.placement, d.overflows.filter((overflow2) => overflow2 > 0).reduce((acc, overflow2) => acc + overflow2, 0)]).sort((a, b) => a[1] - b[1])[0]) == null ? void 0 : _overflowsData$map$so[0];
                if (placement2) {
                  resetPlacement = placement2;
                }
                break;
              }
              case "initialPlacement":
                resetPlacement = initialPlacement;
                break;
            }
          }
          if (placement !== resetPlacement) {
            return {
              reset: {
                placement: resetPlacement
              }
            };
          }
        }
        return {};
      }
    };
  };
  async function convertValueToCoords(state, options) {
    const {
      placement,
      platform: platform2,
      elements
    } = state;
    const rtl = await (platform2.isRTL == null ? void 0 : platform2.isRTL(elements.floating));
    const side = getSide(placement);
    const alignment = getAlignment(placement);
    const isVertical = getSideAxis(placement) === "y";
    const mainAxisMulti = ["left", "top"].includes(side) ? -1 : 1;
    const crossAxisMulti = rtl && isVertical ? -1 : 1;
    const rawValue = evaluate(options, state);
    let {
      mainAxis,
      crossAxis,
      alignmentAxis
    } = typeof rawValue === "number" ? {
      mainAxis: rawValue,
      crossAxis: 0,
      alignmentAxis: null
    } : {
      mainAxis: 0,
      crossAxis: 0,
      alignmentAxis: null,
      ...rawValue
    };
    if (alignment && typeof alignmentAxis === "number") {
      crossAxis = alignment === "end" ? alignmentAxis * -1 : alignmentAxis;
    }
    return isVertical ? {
      x: crossAxis * crossAxisMulti,
      y: mainAxis * mainAxisMulti
    } : {
      x: mainAxis * mainAxisMulti,
      y: crossAxis * crossAxisMulti
    };
  }
  var offset = function(options) {
    if (options === void 0) {
      options = 0;
    }
    return {
      name: "offset",
      options,
      async fn(state) {
        const {
          x,
          y
        } = state;
        const diffCoords = await convertValueToCoords(state, options);
        return {
          x: x + diffCoords.x,
          y: y + diffCoords.y,
          data: diffCoords
        };
      }
    };
  };
  var shift = function(options) {
    if (options === void 0) {
      options = {};
    }
    return {
      name: "shift",
      options,
      async fn(state) {
        const {
          x,
          y,
          placement
        } = state;
        const {
          mainAxis: checkMainAxis = true,
          crossAxis: checkCrossAxis = false,
          limiter = {
            fn: (_ref) => {
              let {
                x: x2,
                y: y2
              } = _ref;
              return {
                x: x2,
                y: y2
              };
            }
          },
          ...detectOverflowOptions
        } = evaluate(options, state);
        const coords = {
          x,
          y
        };
        const overflow = await detectOverflow(state, detectOverflowOptions);
        const crossAxis = getSideAxis(getSide(placement));
        const mainAxis = getOppositeAxis(crossAxis);
        let mainAxisCoord = coords[mainAxis];
        let crossAxisCoord = coords[crossAxis];
        if (checkMainAxis) {
          const minSide = mainAxis === "y" ? "top" : "left";
          const maxSide = mainAxis === "y" ? "bottom" : "right";
          const min2 = mainAxisCoord + overflow[minSide];
          const max2 = mainAxisCoord - overflow[maxSide];
          mainAxisCoord = clamp(min2, mainAxisCoord, max2);
        }
        if (checkCrossAxis) {
          const minSide = crossAxis === "y" ? "top" : "left";
          const maxSide = crossAxis === "y" ? "bottom" : "right";
          const min2 = crossAxisCoord + overflow[minSide];
          const max2 = crossAxisCoord - overflow[maxSide];
          crossAxisCoord = clamp(min2, crossAxisCoord, max2);
        }
        const limitedCoords = limiter.fn({
          ...state,
          [mainAxis]: mainAxisCoord,
          [crossAxis]: crossAxisCoord
        });
        return {
          ...limitedCoords,
          data: {
            x: limitedCoords.x - x,
            y: limitedCoords.y - y
          }
        };
      }
    };
  };
  function getNodeName(node) {
    if (isNode(node)) {
      return (node.nodeName || "").toLowerCase();
    }
    return "#document";
  }
  function getWindow(node) {
    var _node$ownerDocument;
    return (node == null ? void 0 : (_node$ownerDocument = node.ownerDocument) == null ? void 0 : _node$ownerDocument.defaultView) || window;
  }
  function getDocumentElement(node) {
    var _ref;
    return (_ref = (isNode(node) ? node.ownerDocument : node.document) || window.document) == null ? void 0 : _ref.documentElement;
  }
  function isNode(value) {
    return value instanceof Node || value instanceof getWindow(value).Node;
  }
  function isElement(value) {
    return value instanceof Element || value instanceof getWindow(value).Element;
  }
  function isHTMLElement(value) {
    return value instanceof HTMLElement || value instanceof getWindow(value).HTMLElement;
  }
  function isShadowRoot(value) {
    if (typeof ShadowRoot === "undefined") {
      return false;
    }
    return value instanceof ShadowRoot || value instanceof getWindow(value).ShadowRoot;
  }
  function isOverflowElement(element) {
    const {
      overflow,
      overflowX,
      overflowY,
      display
    } = getComputedStyle2(element);
    return /auto|scroll|overlay|hidden|clip/.test(overflow + overflowY + overflowX) && !["inline", "contents"].includes(display);
  }
  function isTableElement(element) {
    return ["table", "td", "th"].includes(getNodeName(element));
  }
  function isContainingBlock(element) {
    const webkit = isWebKit();
    const css2 = getComputedStyle2(element);
    return css2.transform !== "none" || css2.perspective !== "none" || (css2.containerType ? css2.containerType !== "normal" : false) || !webkit && (css2.backdropFilter ? css2.backdropFilter !== "none" : false) || !webkit && (css2.filter ? css2.filter !== "none" : false) || ["transform", "perspective", "filter"].some((value) => (css2.willChange || "").includes(value)) || ["paint", "layout", "strict", "content"].some((value) => (css2.contain || "").includes(value));
  }
  function getContainingBlock(element) {
    let currentNode = getParentNode(element);
    while (isHTMLElement(currentNode) && !isLastTraversableNode(currentNode)) {
      if (isContainingBlock(currentNode)) {
        return currentNode;
      } else {
        currentNode = getParentNode(currentNode);
      }
    }
    return null;
  }
  function isWebKit() {
    if (typeof CSS === "undefined" || !CSS.supports)
      return false;
    return CSS.supports("-webkit-backdrop-filter", "none");
  }
  function isLastTraversableNode(node) {
    return ["html", "body", "#document"].includes(getNodeName(node));
  }
  function getComputedStyle2(element) {
    return getWindow(element).getComputedStyle(element);
  }
  function getNodeScroll(element) {
    if (isElement(element)) {
      return {
        scrollLeft: element.scrollLeft,
        scrollTop: element.scrollTop
      };
    }
    return {
      scrollLeft: element.pageXOffset,
      scrollTop: element.pageYOffset
    };
  }
  function getParentNode(node) {
    if (getNodeName(node) === "html") {
      return node;
    }
    const result = node.assignedSlot || node.parentNode || isShadowRoot(node) && node.host || getDocumentElement(node);
    return isShadowRoot(result) ? result.host : result;
  }
  function getNearestOverflowAncestor(node) {
    const parentNode = getParentNode(node);
    if (isLastTraversableNode(parentNode)) {
      return node.ownerDocument ? node.ownerDocument.body : node.body;
    }
    if (isHTMLElement(parentNode) && isOverflowElement(parentNode)) {
      return parentNode;
    }
    return getNearestOverflowAncestor(parentNode);
  }
  function getOverflowAncestors(node, list, traverseIframes) {
    var _node$ownerDocument2;
    if (list === void 0) {
      list = [];
    }
    if (traverseIframes === void 0) {
      traverseIframes = true;
    }
    const scrollableAncestor = getNearestOverflowAncestor(node);
    const isBody = scrollableAncestor === ((_node$ownerDocument2 = node.ownerDocument) == null ? void 0 : _node$ownerDocument2.body);
    const win = getWindow(scrollableAncestor);
    if (isBody) {
      return list.concat(win, win.visualViewport || [], isOverflowElement(scrollableAncestor) ? scrollableAncestor : [], win.frameElement && traverseIframes ? getOverflowAncestors(win.frameElement) : []);
    }
    return list.concat(scrollableAncestor, getOverflowAncestors(scrollableAncestor, [], traverseIframes));
  }
  function getCssDimensions(element) {
    const css2 = getComputedStyle2(element);
    let width = parseFloat(css2.width) || 0;
    let height = parseFloat(css2.height) || 0;
    const hasOffset = isHTMLElement(element);
    const offsetWidth = hasOffset ? element.offsetWidth : width;
    const offsetHeight = hasOffset ? element.offsetHeight : height;
    const shouldFallback = round(width) !== offsetWidth || round(height) !== offsetHeight;
    if (shouldFallback) {
      width = offsetWidth;
      height = offsetHeight;
    }
    return {
      width,
      height,
      $: shouldFallback
    };
  }
  function unwrapElement(element) {
    return !isElement(element) ? element.contextElement : element;
  }
  function getScale(element) {
    const domElement = unwrapElement(element);
    if (!isHTMLElement(domElement)) {
      return createCoords(1);
    }
    const rect = domElement.getBoundingClientRect();
    const {
      width,
      height,
      $
    } = getCssDimensions(domElement);
    let x = ($ ? round(rect.width) : rect.width) / width;
    let y = ($ ? round(rect.height) : rect.height) / height;
    if (!x || !Number.isFinite(x)) {
      x = 1;
    }
    if (!y || !Number.isFinite(y)) {
      y = 1;
    }
    return {
      x,
      y
    };
  }
  var noOffsets = /* @__PURE__ */ createCoords(0);
  function getVisualOffsets(element) {
    const win = getWindow(element);
    if (!isWebKit() || !win.visualViewport) {
      return noOffsets;
    }
    return {
      x: win.visualViewport.offsetLeft,
      y: win.visualViewport.offsetTop
    };
  }
  function shouldAddVisualOffsets(element, isFixed, floatingOffsetParent) {
    if (isFixed === void 0) {
      isFixed = false;
    }
    if (!floatingOffsetParent || isFixed && floatingOffsetParent !== getWindow(element)) {
      return false;
    }
    return isFixed;
  }
  function getBoundingClientRect(element, includeScale, isFixedStrategy, offsetParent) {
    if (includeScale === void 0) {
      includeScale = false;
    }
    if (isFixedStrategy === void 0) {
      isFixedStrategy = false;
    }
    const clientRect = element.getBoundingClientRect();
    const domElement = unwrapElement(element);
    let scale = createCoords(1);
    if (includeScale) {
      if (offsetParent) {
        if (isElement(offsetParent)) {
          scale = getScale(offsetParent);
        }
      } else {
        scale = getScale(element);
      }
    }
    const visualOffsets = shouldAddVisualOffsets(domElement, isFixedStrategy, offsetParent) ? getVisualOffsets(domElement) : createCoords(0);
    let x = (clientRect.left + visualOffsets.x) / scale.x;
    let y = (clientRect.top + visualOffsets.y) / scale.y;
    let width = clientRect.width / scale.x;
    let height = clientRect.height / scale.y;
    if (domElement) {
      const win = getWindow(domElement);
      const offsetWin = offsetParent && isElement(offsetParent) ? getWindow(offsetParent) : offsetParent;
      let currentIFrame = win.frameElement;
      while (currentIFrame && offsetParent && offsetWin !== win) {
        const iframeScale = getScale(currentIFrame);
        const iframeRect = currentIFrame.getBoundingClientRect();
        const css2 = getComputedStyle2(currentIFrame);
        const left = iframeRect.left + (currentIFrame.clientLeft + parseFloat(css2.paddingLeft)) * iframeScale.x;
        const top = iframeRect.top + (currentIFrame.clientTop + parseFloat(css2.paddingTop)) * iframeScale.y;
        x *= iframeScale.x;
        y *= iframeScale.y;
        width *= iframeScale.x;
        height *= iframeScale.y;
        x += left;
        y += top;
        currentIFrame = getWindow(currentIFrame).frameElement;
      }
    }
    return rectToClientRect({
      width,
      height,
      x,
      y
    });
  }
  function convertOffsetParentRelativeRectToViewportRelativeRect(_ref) {
    let {
      rect,
      offsetParent,
      strategy
    } = _ref;
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    const documentElement = getDocumentElement(offsetParent);
    if (offsetParent === documentElement) {
      return rect;
    }
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    let scale = createCoords(1);
    const offsets = createCoords(0);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && strategy !== "fixed") {
      if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isHTMLElement(offsetParent)) {
        const offsetRect = getBoundingClientRect(offsetParent);
        scale = getScale(offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      }
    }
    return {
      width: rect.width * scale.x,
      height: rect.height * scale.y,
      x: rect.x * scale.x - scroll.scrollLeft * scale.x + offsets.x,
      y: rect.y * scale.y - scroll.scrollTop * scale.y + offsets.y
    };
  }
  function getClientRects(element) {
    return Array.from(element.getClientRects());
  }
  function getWindowScrollBarX(element) {
    return getBoundingClientRect(getDocumentElement(element)).left + getNodeScroll(element).scrollLeft;
  }
  function getDocumentRect(element) {
    const html = getDocumentElement(element);
    const scroll = getNodeScroll(element);
    const body = element.ownerDocument.body;
    const width = max(html.scrollWidth, html.clientWidth, body.scrollWidth, body.clientWidth);
    const height = max(html.scrollHeight, html.clientHeight, body.scrollHeight, body.clientHeight);
    let x = -scroll.scrollLeft + getWindowScrollBarX(element);
    const y = -scroll.scrollTop;
    if (getComputedStyle2(body).direction === "rtl") {
      x += max(html.clientWidth, body.clientWidth) - width;
    }
    return {
      width,
      height,
      x,
      y
    };
  }
  function getViewportRect(element, strategy) {
    const win = getWindow(element);
    const html = getDocumentElement(element);
    const visualViewport = win.visualViewport;
    let width = html.clientWidth;
    let height = html.clientHeight;
    let x = 0;
    let y = 0;
    if (visualViewport) {
      width = visualViewport.width;
      height = visualViewport.height;
      const visualViewportBased = isWebKit();
      if (!visualViewportBased || visualViewportBased && strategy === "fixed") {
        x = visualViewport.offsetLeft;
        y = visualViewport.offsetTop;
      }
    }
    return {
      width,
      height,
      x,
      y
    };
  }
  function getInnerBoundingClientRect(element, strategy) {
    const clientRect = getBoundingClientRect(element, true, strategy === "fixed");
    const top = clientRect.top + element.clientTop;
    const left = clientRect.left + element.clientLeft;
    const scale = isHTMLElement(element) ? getScale(element) : createCoords(1);
    const width = element.clientWidth * scale.x;
    const height = element.clientHeight * scale.y;
    const x = left * scale.x;
    const y = top * scale.y;
    return {
      width,
      height,
      x,
      y
    };
  }
  function getClientRectFromClippingAncestor(element, clippingAncestor, strategy) {
    let rect;
    if (clippingAncestor === "viewport") {
      rect = getViewportRect(element, strategy);
    } else if (clippingAncestor === "document") {
      rect = getDocumentRect(getDocumentElement(element));
    } else if (isElement(clippingAncestor)) {
      rect = getInnerBoundingClientRect(clippingAncestor, strategy);
    } else {
      const visualOffsets = getVisualOffsets(element);
      rect = {
        ...clippingAncestor,
        x: clippingAncestor.x - visualOffsets.x,
        y: clippingAncestor.y - visualOffsets.y
      };
    }
    return rectToClientRect(rect);
  }
  function hasFixedPositionAncestor(element, stopNode) {
    const parentNode = getParentNode(element);
    if (parentNode === stopNode || !isElement(parentNode) || isLastTraversableNode(parentNode)) {
      return false;
    }
    return getComputedStyle2(parentNode).position === "fixed" || hasFixedPositionAncestor(parentNode, stopNode);
  }
  function getClippingElementAncestors(element, cache) {
    const cachedResult = cache.get(element);
    if (cachedResult) {
      return cachedResult;
    }
    let result = getOverflowAncestors(element, [], false).filter((el) => isElement(el) && getNodeName(el) !== "body");
    let currentContainingBlockComputedStyle = null;
    const elementIsFixed = getComputedStyle2(element).position === "fixed";
    let currentNode = elementIsFixed ? getParentNode(element) : element;
    while (isElement(currentNode) && !isLastTraversableNode(currentNode)) {
      const computedStyle = getComputedStyle2(currentNode);
      const currentNodeIsContaining = isContainingBlock(currentNode);
      if (!currentNodeIsContaining && computedStyle.position === "fixed") {
        currentContainingBlockComputedStyle = null;
      }
      const shouldDropCurrentNode = elementIsFixed ? !currentNodeIsContaining && !currentContainingBlockComputedStyle : !currentNodeIsContaining && computedStyle.position === "static" && !!currentContainingBlockComputedStyle && ["absolute", "fixed"].includes(currentContainingBlockComputedStyle.position) || isOverflowElement(currentNode) && !currentNodeIsContaining && hasFixedPositionAncestor(element, currentNode);
      if (shouldDropCurrentNode) {
        result = result.filter((ancestor) => ancestor !== currentNode);
      } else {
        currentContainingBlockComputedStyle = computedStyle;
      }
      currentNode = getParentNode(currentNode);
    }
    cache.set(element, result);
    return result;
  }
  function getClippingRect(_ref) {
    let {
      element,
      boundary,
      rootBoundary,
      strategy
    } = _ref;
    const elementClippingAncestors = boundary === "clippingAncestors" ? getClippingElementAncestors(element, this._c) : [].concat(boundary);
    const clippingAncestors = [...elementClippingAncestors, rootBoundary];
    const firstClippingAncestor = clippingAncestors[0];
    const clippingRect = clippingAncestors.reduce((accRect, clippingAncestor) => {
      const rect = getClientRectFromClippingAncestor(element, clippingAncestor, strategy);
      accRect.top = max(rect.top, accRect.top);
      accRect.right = min(rect.right, accRect.right);
      accRect.bottom = min(rect.bottom, accRect.bottom);
      accRect.left = max(rect.left, accRect.left);
      return accRect;
    }, getClientRectFromClippingAncestor(element, firstClippingAncestor, strategy));
    return {
      width: clippingRect.right - clippingRect.left,
      height: clippingRect.bottom - clippingRect.top,
      x: clippingRect.left,
      y: clippingRect.top
    };
  }
  function getDimensions(element) {
    return getCssDimensions(element);
  }
  function getRectRelativeToOffsetParent(element, offsetParent, strategy) {
    const isOffsetParentAnElement = isHTMLElement(offsetParent);
    const documentElement = getDocumentElement(offsetParent);
    const isFixed = strategy === "fixed";
    const rect = getBoundingClientRect(element, true, isFixed, offsetParent);
    let scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    const offsets = createCoords(0);
    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== "body" || isOverflowElement(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }
      if (isOffsetParentAnElement) {
        const offsetRect = getBoundingClientRect(offsetParent, true, isFixed, offsetParent);
        offsets.x = offsetRect.x + offsetParent.clientLeft;
        offsets.y = offsetRect.y + offsetParent.clientTop;
      } else if (documentElement) {
        offsets.x = getWindowScrollBarX(documentElement);
      }
    }
    return {
      x: rect.left + scroll.scrollLeft - offsets.x,
      y: rect.top + scroll.scrollTop - offsets.y,
      width: rect.width,
      height: rect.height
    };
  }
  function getTrueOffsetParent(element, polyfill) {
    if (!isHTMLElement(element) || getComputedStyle2(element).position === "fixed") {
      return null;
    }
    if (polyfill) {
      return polyfill(element);
    }
    return element.offsetParent;
  }
  function getOffsetParent(element, polyfill) {
    const window2 = getWindow(element);
    if (!isHTMLElement(element)) {
      return window2;
    }
    let offsetParent = getTrueOffsetParent(element, polyfill);
    while (offsetParent && isTableElement(offsetParent) && getComputedStyle2(offsetParent).position === "static") {
      offsetParent = getTrueOffsetParent(offsetParent, polyfill);
    }
    if (offsetParent && (getNodeName(offsetParent) === "html" || getNodeName(offsetParent) === "body" && getComputedStyle2(offsetParent).position === "static" && !isContainingBlock(offsetParent))) {
      return window2;
    }
    return offsetParent || getContainingBlock(element) || window2;
  }
  var getElementRects = async function(_ref) {
    let {
      reference,
      floating,
      strategy
    } = _ref;
    const getOffsetParentFn = this.getOffsetParent || getOffsetParent;
    const getDimensionsFn = this.getDimensions;
    return {
      reference: getRectRelativeToOffsetParent(reference, await getOffsetParentFn(floating), strategy),
      floating: {
        x: 0,
        y: 0,
        ...await getDimensionsFn(floating)
      }
    };
  };
  function isRTL(element) {
    return getComputedStyle2(element).direction === "rtl";
  }
  var platform = {
    convertOffsetParentRelativeRectToViewportRelativeRect,
    getDocumentElement,
    getClippingRect,
    getOffsetParent,
    getElementRects,
    getClientRects,
    getDimensions,
    getScale,
    isElement,
    isRTL
  };
  function observeMove(element, onMove) {
    let io = null;
    let timeoutId;
    const root = getDocumentElement(element);
    function cleanup() {
      clearTimeout(timeoutId);
      io && io.disconnect();
      io = null;
    }
    function refresh(skip, threshold) {
      if (skip === void 0) {
        skip = false;
      }
      if (threshold === void 0) {
        threshold = 1;
      }
      cleanup();
      const {
        left,
        top,
        width,
        height
      } = element.getBoundingClientRect();
      if (!skip) {
        onMove();
      }
      if (!width || !height) {
        return;
      }
      const insetTop = floor(top);
      const insetRight = floor(root.clientWidth - (left + width));
      const insetBottom = floor(root.clientHeight - (top + height));
      const insetLeft = floor(left);
      const rootMargin = -insetTop + "px " + -insetRight + "px " + -insetBottom + "px " + -insetLeft + "px";
      const options = {
        rootMargin,
        threshold: max(0, min(1, threshold)) || 1
      };
      let isFirstUpdate = true;
      function handleObserve(entries) {
        const ratio = entries[0].intersectionRatio;
        if (ratio !== threshold) {
          if (!isFirstUpdate) {
            return refresh();
          }
          if (!ratio) {
            timeoutId = setTimeout(() => {
              refresh(false, 1e-7);
            }, 100);
          } else {
            refresh(false, ratio);
          }
        }
        isFirstUpdate = false;
      }
      try {
        io = new IntersectionObserver(handleObserve, {
          ...options,
          root: root.ownerDocument
        });
      } catch (e) {
        io = new IntersectionObserver(handleObserve, options);
      }
      io.observe(element);
    }
    refresh(true);
    return cleanup;
  }
  function autoUpdate(reference, floating, update, options) {
    if (options === void 0) {
      options = {};
    }
    const {
      ancestorScroll = true,
      ancestorResize = true,
      elementResize = typeof ResizeObserver === "function",
      layoutShift = typeof IntersectionObserver === "function",
      animationFrame = false
    } = options;
    const referenceEl = unwrapElement(reference);
    const ancestors = ancestorScroll || ancestorResize ? [...referenceEl ? getOverflowAncestors(referenceEl) : [], ...getOverflowAncestors(floating)] : [];
    ancestors.forEach((ancestor) => {
      ancestorScroll && ancestor.addEventListener("scroll", update, {
        passive: true
      });
      ancestorResize && ancestor.addEventListener("resize", update);
    });
    const cleanupIo = referenceEl && layoutShift ? observeMove(referenceEl, update) : null;
    let reobserveFrame = -1;
    let resizeObserver = null;
    if (elementResize) {
      resizeObserver = new ResizeObserver((_ref) => {
        let [firstEntry] = _ref;
        if (firstEntry && firstEntry.target === referenceEl && resizeObserver) {
          resizeObserver.unobserve(floating);
          cancelAnimationFrame(reobserveFrame);
          reobserveFrame = requestAnimationFrame(() => {
            resizeObserver && resizeObserver.observe(floating);
          });
        }
        update();
      });
      if (referenceEl && !animationFrame) {
        resizeObserver.observe(referenceEl);
      }
      resizeObserver.observe(floating);
    }
    let frameId;
    let prevRefRect = animationFrame ? getBoundingClientRect(reference) : null;
    if (animationFrame) {
      frameLoop();
    }
    function frameLoop() {
      const nextRefRect = getBoundingClientRect(reference);
      if (prevRefRect && (nextRefRect.x !== prevRefRect.x || nextRefRect.y !== prevRefRect.y || nextRefRect.width !== prevRefRect.width || nextRefRect.height !== prevRefRect.height)) {
        update();
      }
      prevRefRect = nextRefRect;
      frameId = requestAnimationFrame(frameLoop);
    }
    update();
    return () => {
      ancestors.forEach((ancestor) => {
        ancestorScroll && ancestor.removeEventListener("scroll", update);
        ancestorResize && ancestor.removeEventListener("resize", update);
      });
      cleanupIo && cleanupIo();
      resizeObserver && resizeObserver.disconnect();
      resizeObserver = null;
      if (animationFrame) {
        cancelAnimationFrame(frameId);
      }
    };
  }
  var computePosition2 = (reference, floating, options) => {
    const cache = /* @__PURE__ */ new Map();
    const mergedOptions = {
      platform,
      ...options
    };
    const platformWithCache = {
      ...mergedOptions.platform,
      _c: cache
    };
    return computePosition(reference, floating, {
      ...mergedOptions,
      platform: platformWithCache
    });
  };
  function src_default7(Alpine24) {
    Alpine24.magic("anchor", (el) => {
      if (!el._x_anchor)
        throw "Alpine: No x-anchor directive found on element using $anchor...";
      return el._x_anchor;
    });
    Alpine24.interceptClone((from, to) => {
      if (from && from._x_anchor && !to._x_anchor) {
        to._x_anchor = from._x_anchor;
      }
    });
    Alpine24.directive("anchor", Alpine24.skipDuringClone(
      (el, { expression, modifiers, value }, { cleanup, evaluate: evaluate2 }) => {
        let { placement, offsetValue, unstyled } = getOptions(modifiers);
        el._x_anchor = Alpine24.reactive({ x: 0, y: 0 });
        let reference = evaluate2(expression);
        if (!reference)
          throw "Alpine: no element provided to x-anchor...";
        let compute = () => {
          let previousValue;
          computePosition2(reference, el, {
            placement,
            middleware: [flip(), shift({ padding: 5 }), offset(offsetValue)]
          }).then(({ x, y }) => {
            unstyled || setStyles(el, x, y);
            if (JSON.stringify({ x, y }) !== previousValue) {
              el._x_anchor.x = x;
              el._x_anchor.y = y;
            }
            previousValue = JSON.stringify({ x, y });
          });
        };
        let release = autoUpdate(reference, el, () => compute());
        cleanup(() => release());
      },
      (el, { expression, modifiers, value }, { cleanup, evaluate: evaluate2 }) => {
        let { placement, offsetValue, unstyled } = getOptions(modifiers);
        if (el._x_anchor) {
          unstyled || setStyles(el, el._x_anchor.x, el._x_anchor.y);
        }
      }
    ));
  }
  function setStyles(el, x, y) {
    Object.assign(el.style, {
      left: x + "px",
      top: y + "px",
      position: "absolute"
    });
  }
  function getOptions(modifiers) {
    let positions = ["top", "top-start", "top-end", "right", "right-start", "right-end", "bottom", "bottom-start", "bottom-end", "left", "left-start", "left-end"];
    let placement = positions.find((i) => modifiers.includes(i));
    let offsetValue = 0;
    if (modifiers.includes("offset")) {
      let idx = modifiers.findIndex((i) => i === "offset");
      offsetValue = modifiers[idx + 1] !== void 0 ? Number(modifiers[idx + 1]) : offsetValue;
    }
    let unstyled = modifiers.includes("no-style");
    return { placement, offsetValue, unstyled };
  }
  var module_default7 = src_default7;

  // js/plugins/navigate/history.js
  var Snapshot = class {
    constructor(url, html) {
      this.url = url;
      this.html = html;
    }
  };
  var snapshotCache = {
    currentKey: null,
    currentUrl: null,
    keys: [],
    lookup: {},
    limit: 10,
    has(location) {
      return this.lookup[location] !== void 0;
    },
    retrieve(location) {
      let snapshot = this.lookup[location];
      if (snapshot === void 0)
        throw "No back button cache found for current location: " + location;
      return snapshot;
    },
    replace(key, snapshot) {
      if (this.has(key)) {
        this.lookup[key] = snapshot;
      } else {
        this.push(key, snapshot);
      }
    },
    push(key, snapshot) {
      this.lookup[key] = snapshot;
      let index2 = this.keys.indexOf(key);
      if (index2 > -1)
        this.keys.splice(index2, 1);
      this.keys.unshift(key);
      this.trim();
    },
    trim() {
      for (let key of this.keys.splice(this.limit)) {
        delete this.lookup[key];
      }
    }
  };
  function updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks() {
    let url = new URL(window.location.href, document.baseURI);
    replaceUrl(url, document.documentElement.outerHTML);
  }
  function updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks(key, url) {
    let html = document.documentElement.outerHTML;
    snapshotCache.replace(key, new Snapshot(url, html));
  }
  function whenTheBackOrForwardButtonIsClicked(registerFallback, handleHtml) {
    let fallback2;
    registerFallback((i) => fallback2 = i);
    window.addEventListener("popstate", (e) => {
      let state = e.state || {};
      let alpine = state.alpine || {};
      if (Object.keys(state).length === 0)
        return;
      if (!alpine.snapshotIdx)
        return;
      if (snapshotCache.has(alpine.snapshotIdx)) {
        let snapshot = snapshotCache.retrieve(alpine.snapshotIdx);
        handleHtml(snapshot.html, snapshot.url, snapshotCache.currentUrl, snapshotCache.currentKey);
      } else {
        fallback2(alpine.url);
      }
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
    let key = url.toString() + "-" + Math.random();
    method === "pushState" ? snapshotCache.push(key, new Snapshot(url, html)) : snapshotCache.replace(key = snapshotCache.currentKey ?? key, new Snapshot(url, html));
    let state = history.state || {};
    if (!state.alpine)
      state.alpine = {};
    state.alpine.snapshotIdx = key;
    state.alpine.url = url.toString();
    try {
      history[method](state, JSON.stringify(document.title), url);
      snapshotCache.currentKey = key;
      snapshotCache.currentUrl = url;
    } catch (error2) {
      if (error2 instanceof DOMException && error2.name === "SecurityError") {
        console.error(
          "Livewire: You can't use wire:navigate with a link to a different root domain: " + url
        );
      }
      console.error(error2);
    }
  }

  // js/plugins/navigate/links.js
  function whenThisLinkIsPressed(el, callback) {
    let isProgrammaticClick = (e) => !e.isTrusted;
    let isNotPlainLeftClick = (e) => e.which > 1 || e.altKey || e.ctrlKey || e.metaKey || e.shiftKey;
    let isNotPlainEnterKey = (e) => e.which !== 13 || e.altKey || e.ctrlKey || e.metaKey || e.shiftKey;
    el.addEventListener("click", (e) => {
      if (isProgrammaticClick(e)) {
        e.preventDefault();
        callback((whenReleased) => whenReleased());
        return;
      }
      if (isNotPlainLeftClick(e))
        return;
      e.preventDefault();
    });
    el.addEventListener("mousedown", (e) => {
      if (isNotPlainLeftClick(e))
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
    el.addEventListener("keydown", (e) => {
      if (isNotPlainEnterKey(e))
        return;
      e.preventDefault();
      callback((whenReleased) => whenReleased());
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
    return createUrlObjectFromString2(linkEl.getAttribute("href"));
  }
  function createUrlObjectFromString2(urlString) {
    return urlString !== null && new URL(urlString, document.baseURI);
  }
  function getUriStringFromUrlObject(urlObject) {
    return urlObject.pathname + urlObject.search + urlObject.hash;
  }

  // js/plugins/navigate/fetch.js
  function fetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination);
    performFetch(uri, (html, finalDestination) => {
      callback(html, finalDestination);
    }, errorCallback);
  }
  function performFetch(uri, callback, errorCallback) {
    sendNavigateRequest(uri, callback, errorCallback);
  }

  // js/plugins/navigate/prefetch.js
  var prefetches = {};
  var cacheDuration = 3e4;
  function prefetchHtml(destination, callback, errorCallback) {
    let uri = getUriStringFromUrlObject(destination);
    if (prefetches[uri])
      return;
    prefetches[uri] = { finished: false, html: null, whenFinished: () => setTimeout(() => delete prefetches[uri], cacheDuration) };
    performFetch(uri, (html, routedUri) => {
      callback(html, routedUri);
    }, () => {
      delete prefetches[uri];
      errorCallback();
    });
  }
  function storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination) {
    let state = prefetches[getUriStringFromUrlObject(destination)];
    state.html = html;
    state.finished = true;
    state.finalDestination = finalDestination;
    state.whenFinished();
  }
  function getPretchedHtmlOr(destination, receive, ifNoPrefetchExists) {
    let uri = getUriStringFromUrlObject(destination);
    if (!prefetches[uri])
      return ifNoPrefetchExists();
    if (prefetches[uri].finished) {
      let html = prefetches[uri].html;
      let finalDestination = prefetches[uri].finalDestination;
      delete prefetches[uri];
      return receive(html, finalDestination);
    } else {
      prefetches[uri].whenFinished = () => {
        let html = prefetches[uri].html;
        let finalDestination = prefetches[uri].finalDestination;
        delete prefetches[uri];
        receive(html, finalDestination);
      };
    }
  }

  // js/plugins/navigate/teleport.js
  var import_alpinejs3 = __toESM(require_module_cjs());
  function packUpPersistedTeleports(persistedEl) {
    import_alpinejs3.default.mutateDom(() => {
      persistedEl.querySelectorAll("[data-teleport-template]").forEach((i) => i._x_teleport.remove());
    });
  }
  function removeAnyLeftOverStaleTeleportTargets(body) {
    import_alpinejs3.default.mutateDom(() => {
      body.querySelectorAll("[data-teleport-target]").forEach((i) => i.remove());
    });
  }
  function unPackPersistedTeleports(persistedEl) {
    import_alpinejs3.default.walk(persistedEl, (el, skip) => {
      if (!el._x_teleport)
        return;
      el._x_teleportPutBack();
      skip();
    });
  }
  function isTeleportTarget(el) {
    return el.hasAttribute("data-teleport-target");
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
  function restoreScrollPositionOrScrollToTop() {
    let scroll = (el) => {
      if (!el.hasAttribute("data-scroll-x")) {
        window.scrollTo({ top: 0, left: 0, behavior: "instant" });
      } else {
        el.scrollTo({
          top: Number(el.getAttribute("data-scroll-y")),
          left: Number(el.getAttribute("data-scroll-x")),
          behavior: "instant"
        });
        el.removeAttribute("data-scroll-x");
        el.removeAttribute("data-scroll-y");
      }
    };
    queueMicrotask(() => {
      queueMicrotask(() => {
        scroll(document.body);
        document.querySelectorAll(["[x-navigate\\:scroll]", "[wire\\:scroll]"]).forEach(scroll);
      });
    });
  }

  // js/plugins/navigate/persist.js
  var import_alpinejs4 = __toESM(require_module_cjs());
  var els = {};
  function storePersistantElementsForLater(callback) {
    els = {};
    document.querySelectorAll("[x-persist]").forEach((i) => {
      els[i.getAttribute("x-persist")] = i;
      callback(i);
      import_alpinejs4.default.mutateDom(() => {
        i.remove();
      });
    });
  }
  function putPersistantElementsBack(callback) {
    let usedPersists = [];
    document.querySelectorAll("[x-persist]").forEach((i) => {
      let old = els[i.getAttribute("x-persist")];
      if (!old)
        return;
      usedPersists.push(i.getAttribute("x-persist"));
      old._x_wasPersisted = true;
      callback(old, i);
      import_alpinejs4.default.mutateDom(() => {
        i.replaceWith(old);
      });
    });
    Object.entries(els).forEach(([key, el]) => {
      if (usedPersists.includes(key))
        return;
      import_alpinejs4.default.destroyTree(el);
    });
    els = {};
  }
  function isPersistedElement(el) {
    return el.hasAttribute("x-persist");
  }

  // js/plugins/navigate/bar.js
  var import_nprogress = __toESM(require_nprogress());
  import_nprogress.default.configure({
    minimum: 0.1,
    trickleSpeed: 200,
    showSpinner: false,
    parent: "body"
  });
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
  }
  function removeAnyLeftOverStaleProgressBars() {
    import_nprogress.default.remove();
  }
  function injectStyles() {
    let style = document.createElement("style");
    style.innerHTML = `/* Make clicks pass-through */

    #nprogress {
      pointer-events: none;
    }

    #nprogress .bar {
      background: var(--livewire-progress-bar-color, #29d);

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
      box-shadow: 0 0 10px var(--livewire-progress-bar-color, #29d), 0 0 5px var(--livewire-progress-bar-color, #29d);
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
      border-top-color: var(--livewire-progress-bar-color, #29d);
      border-left-color: var(--livewire-progress-bar-color, #29d);
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
    let nonce2 = getNonce();
    if (nonce2)
      style.nonce = nonce2;
    document.head.appendChild(style);
  }

  // js/plugins/navigate/popover.js
  function packUpPersistedPopovers(persistedEl) {
    if (!isPopoverSupported())
      return;
    persistedEl.querySelectorAll(":popover-open").forEach((el) => {
      el.setAttribute("data-navigate-popover-open", "");
      let animations = el.getAnimations();
      el._pausedAnimations = animations.map((animation) => ({
        keyframes: animation.effect.getKeyframes(),
        options: {
          duration: animation.effect.getTiming().duration,
          easing: animation.effect.getTiming().easing,
          fill: animation.effect.getTiming().fill,
          iterations: animation.effect.getTiming().iterations
        },
        currentTime: animation.currentTime,
        playState: animation.playState
      }));
      animations.forEach((i) => i.pause());
    });
  }
  function unPackPersistedPopovers(persistedEl) {
    if (!isPopoverSupported())
      return;
    persistedEl.querySelectorAll("[data-navigate-popover-open]").forEach((el) => {
      el.removeAttribute("data-navigate-popover-open");
      queueMicrotask(() => {
        if (!el.isConnected)
          return;
        el.showPopover();
        el.getAnimations().forEach((i) => i.finish());
        if (el._pausedAnimations) {
          el._pausedAnimations.forEach(({ keyframes, options, currentTime, now, playState }) => {
            let animation = el.animate(keyframes, options);
            animation.currentTime = currentTime;
          });
          delete el._pausedAnimations;
        }
      });
    });
  }
  function isPopoverSupported() {
    return typeof document.createElement("div").showPopover === "function";
  }

  // js/plugins/navigate/page.js
  var oldBodyScriptTagHashes = [];
  var attributesExemptFromScriptTagHashing = [
    "data-csrf",
    "nonce",
    "aria-hidden"
  ];
  function swapCurrentPageWithNewHtml(html, andThen) {
    let newDocument = new DOMParser().parseFromString(html, "text/html");
    let newHtml = newDocument.documentElement;
    let newBody = document.adoptNode(newDocument.body);
    let newHead = document.adoptNode(newDocument.head);
    oldBodyScriptTagHashes = oldBodyScriptTagHashes.concat(Array.from(document.body.querySelectorAll("script")).map((i) => {
      return simpleHash(ignoreAttributes(i.outerHTML, attributesExemptFromScriptTagHashing));
    }));
    let afterRemoteScriptsHaveLoaded = () => {
    };
    replaceHtmlAttributes(newHtml);
    mergeNewHead(newHead).finally(() => {
      afterRemoteScriptsHaveLoaded();
    });
    prepNewBodyScriptTagsToRun(newBody, oldBodyScriptTagHashes);
    let oldBody = document.body;
    document.body.replaceWith(newBody);
    Alpine.destroyTree(oldBody);
    andThen((i) => afterRemoteScriptsHaveLoaded = i);
  }
  function prepNewBodyScriptTagsToRun(newBody, oldBodyScriptTagHashes2) {
    newBody.querySelectorAll("script").forEach((i) => {
      if (i.hasAttribute("data-navigate-once")) {
        let hash = simpleHash(
          ignoreAttributes(i.outerHTML, attributesExemptFromScriptTagHashing)
        );
        if (oldBodyScriptTagHashes2.includes(hash))
          return;
      }
      i.replaceWith(cloneScriptTag(i));
    });
  }
  function replaceHtmlAttributes(newHtmlElement) {
    let currentHtmlElement = document.documentElement;
    Array.from(newHtmlElement.attributes).forEach((attr) => {
      const name = attr.name;
      const value = attr.value;
      if (currentHtmlElement.getAttribute(name) !== value) {
        currentHtmlElement.setAttribute(name, value);
      }
    });
    Array.from(currentHtmlElement.attributes).forEach((attr) => {
      if (!newHtmlElement.hasAttribute(attr.name)) {
        currentHtmlElement.removeAttribute(attr.name);
      }
    });
  }
  function mergeNewHead(newHead) {
    let children = Array.from(document.head.children);
    let headChildrenHtmlLookup = children.map((i) => i.outerHTML);
    let garbageCollector = document.createDocumentFragment();
    let touchedHeadElements = [];
    let remoteScriptsPromises = [];
    for (let child of Array.from(newHead.children)) {
      if (isAsset(child)) {
        if (!headChildrenHtmlLookup.includes(child.outerHTML)) {
          if (isTracked(child)) {
            if (ifTheQueryStringChangedSinceLastRequest(child, children)) {
              setTimeout(() => window.location.reload());
            }
          }
          if (isScript(child)) {
            try {
              remoteScriptsPromises.push(
                injectScriptTagAndWaitForItToFullyLoad(
                  cloneScriptTag(child)
                )
              );
            } catch (error2) {
            }
          } else {
            document.head.appendChild(child);
          }
        } else {
          garbageCollector.appendChild(child);
        }
        touchedHeadElements.push(child);
      }
    }
    for (let child of Array.from(document.head.children)) {
      if (!isAsset(child))
        child.remove();
    }
    for (let child of Array.from(newHead.children)) {
      if (child.tagName.toLowerCase() === "noscript")
        continue;
      document.head.appendChild(child);
    }
    return Promise.all(remoteScriptsPromises);
  }
  async function injectScriptTagAndWaitForItToFullyLoad(script) {
    return new Promise((resolve, reject) => {
      if (script.src) {
        script.onload = () => resolve();
        script.onerror = () => reject();
      } else {
        resolve();
      }
      document.head.appendChild(script);
    });
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
    result = result.replaceAll(" ", "");
    return result.trim();
  }

  // js/plugins/navigate/index.js
  var enablePersist = true;
  var showProgressBar = true;
  var restoreScroll = true;
  var autofocus = false;
  function navigate_default(Alpine24) {
    Alpine24.navigate = (url, options = {}) => {
      let { preserveScroll = false } = options;
      let destination = createUrlObjectFromString2(url);
      let prevented = fireEventForOtherLibrariesToHookInto("alpine:navigate", {
        url: destination,
        history: false,
        cached: false
      });
      if (prevented)
        return;
      navigateTo(destination, { preserveScroll });
    };
    Alpine24.navigate.disableProgressBar = () => {
      showProgressBar = false;
    };
    Alpine24.addInitSelector(() => `[${Alpine24.prefixed("navigate")}]`);
    Alpine24.directive("navigate", (el, { modifiers }) => {
      let shouldPrefetchOnHover = modifiers.includes("hover");
      let preserveScroll = modifiers.includes("preserve-scroll");
      shouldPrefetchOnHover && whenThisLinkIsHoveredFor(el, 60, () => {
        let destination = extractDestinationFromLink(el);
        if (!destination)
          return;
        prefetchHtml(destination, (html, finalDestination) => {
          storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination);
        }, () => {
          showProgressBar && finishAndHideProgressBar();
        });
      });
      whenThisLinkIsPressed(el, (whenItIsReleased) => {
        let destination = extractDestinationFromLink(el);
        if (!destination)
          return;
        prefetchHtml(destination, (html, finalDestination) => {
          storeThePrefetchedHtmlForWhenALinkIsClicked(html, destination, finalDestination);
        }, () => {
          showProgressBar && finishAndHideProgressBar();
        });
        whenItIsReleased(() => {
          let prevented = fireEventForOtherLibrariesToHookInto("alpine:navigate", {
            url: destination,
            history: false,
            cached: false
          });
          if (prevented)
            return;
          navigateTo(destination, { preserveScroll });
        });
      });
    });
    function navigateTo(destination, { preserveScroll = false, shouldPushToHistoryState = true }) {
      showProgressBar && showAndStartProgressBar();
      fetchHtmlOrUsePrefetchedHtml(destination, (html, finalDestination) => {
        fireEventForOtherLibrariesToHookInto("alpine:navigating");
        restoreScroll && storeScrollInformationInHtmlBeforeNavigatingAway();
        cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement();
        updateCurrentPageHtmlInHistoryStateForLaterBackButtonClicks();
        preventAlpineFromPickingUpDomChanges(Alpine24, (andAfterAllThis) => {
          enablePersist && storePersistantElementsForLater((persistedEl) => {
            packUpPersistedTeleports(persistedEl);
            packUpPersistedPopovers(persistedEl);
          });
          if (shouldPushToHistoryState) {
            updateUrlAndStoreLatestHtmlForFutureBackButtons(html, finalDestination);
          } else {
            replaceUrl(finalDestination, html);
          }
          swapCurrentPageWithNewHtml(html, (afterNewScriptsAreDoneLoading) => {
            removeAnyLeftOverStaleTeleportTargets(document.body);
            enablePersist && putPersistantElementsBack((persistedEl, newStub) => {
              unPackPersistedTeleports(persistedEl);
              unPackPersistedPopovers(persistedEl);
            });
            !preserveScroll && restoreScrollPositionOrScrollToTop();
            afterNewScriptsAreDoneLoading(() => {
              andAfterAllThis(() => {
                setTimeout(() => {
                  autofocus && autofocusElementsWithTheAutofocusAttribute();
                });
                nowInitializeAlpineOnTheNewPage(Alpine24);
                fireEventForOtherLibrariesToHookInto("alpine:navigated");
                showProgressBar && finishAndHideProgressBar();
              });
            });
          });
        });
      }, () => {
        showProgressBar && finishAndHideProgressBar();
      });
    }
    whenTheBackOrForwardButtonIsClicked(
      (ifThePageBeingVisitedHasntBeenCached) => {
        ifThePageBeingVisitedHasntBeenCached((url) => {
          let destination = createUrlObjectFromString2(url);
          let prevented = fireEventForOtherLibrariesToHookInto("alpine:navigate", {
            url: destination,
            history: true,
            cached: false
          });
          if (prevented)
            return;
          navigateTo(destination, { shouldPushToHistoryState: false });
        });
      },
      (html, url, currentPageUrl, currentPageKey) => {
        let destination = createUrlObjectFromString2(url);
        let prevented = fireEventForOtherLibrariesToHookInto("alpine:navigate", {
          url: destination,
          history: true,
          cached: true
        });
        if (prevented)
          return;
        storeScrollInformationInHtmlBeforeNavigatingAway();
        fireEventForOtherLibrariesToHookInto("alpine:navigating");
        updateCurrentPageHtmlInSnapshotCacheForLaterBackButtonClicks(currentPageUrl, currentPageKey);
        preventAlpineFromPickingUpDomChanges(Alpine24, (andAfterAllThis) => {
          enablePersist && storePersistantElementsForLater((persistedEl) => {
            packUpPersistedTeleports(persistedEl);
            packUpPersistedPopovers(persistedEl);
          });
          swapCurrentPageWithNewHtml(html, () => {
            removeAnyLeftOverStaleProgressBars();
            removeAnyLeftOverStaleTeleportTargets(document.body);
            enablePersist && putPersistantElementsBack((persistedEl, newStub) => {
              unPackPersistedTeleports(persistedEl);
              unPackPersistedPopovers(persistedEl);
            });
            restoreScrollPositionOrScrollToTop();
            andAfterAllThis(() => {
              autofocus && autofocusElementsWithTheAutofocusAttribute();
              nowInitializeAlpineOnTheNewPage(Alpine24);
              fireEventForOtherLibrariesToHookInto("alpine:navigated");
            });
          });
        });
      }
    );
    setTimeout(() => {
      fireEventForOtherLibrariesToHookInto("alpine:navigated");
    });
  }
  function fetchHtmlOrUsePrefetchedHtml(fromDestination, callback, errorCallback) {
    getPretchedHtmlOr(fromDestination, callback, () => {
      fetchHtml(fromDestination, callback, errorCallback);
    });
  }
  function preventAlpineFromPickingUpDomChanges(Alpine24, callback) {
    Alpine24.stopObservingMutations();
    callback((afterAllThis) => {
      Alpine24.startObservingMutations();
      queueMicrotask(() => {
        afterAllThis();
      });
    });
  }
  function fireEventForOtherLibrariesToHookInto(name, detail) {
    let event = new CustomEvent(name, {
      cancelable: true,
      bubbles: true,
      detail
    });
    document.dispatchEvent(event);
    return event.defaultPrevented;
  }
  function nowInitializeAlpineOnTheNewPage(Alpine24) {
    Alpine24.initTree(document.body, void 0, (el, skip) => {
      if (el._x_wasPersisted)
        skip();
    });
  }
  function autofocusElementsWithTheAutofocusAttribute() {
    document.querySelector("[autofocus]") && document.querySelector("[autofocus]").focus();
  }
  function cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement() {
    let walker = function(root, callback) {
      Alpine.walk(root, (el, skip) => {
        if (isPersistedElement(el))
          skip();
        if (isTeleportTarget(el))
          skip();
        else
          callback(el, skip);
      });
    };
    Alpine.destroyTree(document.body, walker);
  }

  // js/plugins/history/index.js
  function history2(Alpine24) {
    Alpine24.magic("queryString", (el, { interceptor }) => {
      let alias;
      let alwaysShow = false;
      let usePush = false;
      return interceptor((initialSeedValue, getter, setter, path, key) => {
        let queryKey = alias || path;
        let { initial, replace: replace2, push: push2, pop } = track(queryKey, initialSeedValue, alwaysShow);
        setter(initial);
        if (!usePush) {
          Alpine24.effect(() => replace2(getter()));
        } else {
          Alpine24.effect(() => push2(getter()));
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
    Alpine24.history = { track };
  }
  function track(name, initialSeedValue, alwaysShow = false, except = null) {
    let { has, get, set, remove } = queryStringUtils();
    let url = new URL(window.location.href);
    let isInitiallyPresentInUrl = has(url, name);
    let initialValue = isInitiallyPresentInUrl ? get(url, name) : initialSeedValue;
    let initialValueMemo = JSON.stringify(initialValue);
    let exceptValueMemo = [false, null, void 0].includes(except) ? initialSeedValue : JSON.stringify(except);
    let hasReturnedToInitialValue = (newValue) => JSON.stringify(newValue) === initialValueMemo;
    let hasReturnedToExceptValue = (newValue) => JSON.stringify(newValue) === exceptValueMemo;
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
      } else if (newValue === void 0) {
        url2 = remove(url2, name);
      } else if (!alwaysShow && hasReturnedToExceptValue(newValue)) {
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
        let handler = (e) => {
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
        };
        window.addEventListener("popstate", handler);
        return () => window.removeEventListener("popstate", handler);
      }
    };
  }
  function replace(url, key, object) {
    let state = window.history.state || {};
    if (!state.alpine)
      state.alpine = {};
    state.alpine[key] = unwrap(object);
    try {
      window.history.replaceState(state, "", url.toString());
    } catch (e) {
      console.error(e);
    }
  }
  function push(url, key, object) {
    let state = window.history.state || {};
    if (!state.alpine)
      state.alpine = {};
    state = { alpine: { ...state.alpine, ...{ [key]: unwrap(object) } } };
    try {
      window.history.pushState(state, "", url.toString());
    } catch (e) {
      console.error(e);
    }
  }
  function unwrap(object) {
    if (object === void 0)
      return void 0;
    return JSON.parse(JSON.stringify(object));
  }
  function queryStringUtils() {
    return {
      has(url, key) {
        let search = url.search;
        if (!search)
          return false;
        let data = fromQueryString(search, key);
        return Object.keys(data).includes(key);
      },
      get(url, key) {
        let search = url.search;
        if (!search)
          return false;
        let data = fromQueryString(search, key);
        return data[key];
      },
      set(url, key, value) {
        let data = fromQueryString(url.search, key);
        data[key] = stripNulls(unwrap(value));
        url.search = toQueryString(data);
        return url;
      },
      remove(url, key) {
        let data = fromQueryString(url.search, key);
        delete data[key];
        url.search = toQueryString(data);
        return url;
      }
    };
  }
  function stripNulls(value) {
    if (!isObjecty(value))
      return value;
    for (let key in value) {
      if (value[key] === null)
        delete value[key];
      else
        value[key] = stripNulls(value[key]);
    }
    return value;
  }
  function toQueryString(data) {
    let isObjecty2 = (subject) => typeof subject === "object" && subject !== null;
    let buildQueryStringEntries = (data2, entries2 = {}, baseKey = "") => {
      Object.entries(data2).forEach(([iKey, iValue]) => {
        let key = baseKey === "" ? iKey : `${baseKey}[${iKey}]`;
        if (iValue === null) {
          entries2[key] = "";
        } else if (!isObjecty2(iValue)) {
          entries2[key] = encodeURIComponent(iValue).replaceAll("%20", "+").replaceAll("%2C", ",");
        } else {
          entries2 = { ...entries2, ...buildQueryStringEntries(iValue, entries2, key) };
        }
      });
      return entries2;
    };
    let entries = buildQueryStringEntries(data);
    return Object.entries(entries).map(([key, value]) => `${key}=${value}`).join("&");
  }
  function fromQueryString(search, queryKey) {
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
    let data = /* @__PURE__ */ Object.create(null);
    entries.forEach(([key, value]) => {
      if (typeof value == "undefined")
        return;
      value = decodeURIComponent(value.replaceAll("+", "%20"));
      let decodedKey = decodeURIComponent(key);
      let shouldBeHandledAsArray = decodedKey.includes("[") && decodedKey.startsWith(queryKey);
      if (!shouldBeHandledAsArray) {
        data[key] = value;
      } else {
        let dotNotatedKey = decodedKey.replaceAll("[", ".").replaceAll("]", "");
        insertDotNotatedValueIntoData(dotNotatedKey, value, data);
      }
    });
    return data;
  }

  // ../alpine/packages/morph/dist/module.esm.js
  function morph(from, toHtml, options) {
    monkeyPatchDomSetAttributeToAllowAtSymbols();
    let context = createMorphContext(options);
    let toEl = typeof toHtml === "string" ? createElement(toHtml) : toHtml;
    if (window.Alpine && window.Alpine.closestDataStack && !from._x_dataStack) {
      toEl._x_dataStack = window.Alpine.closestDataStack(from);
      toEl._x_dataStack && window.Alpine.cloneNode(from, toEl);
    }
    context.patch(from, toEl);
    return from;
  }
  function morphBetween(startMarker, endMarker, toHtml, options = {}) {
    monkeyPatchDomSetAttributeToAllowAtSymbols();
    let context = createMorphContext(options);
    let fromContainer = startMarker.parentNode;
    let fromBlock = new Block(startMarker, endMarker);
    let toContainer = typeof toHtml === "string" ? (() => {
      let container = document.createElement("div");
      container.insertAdjacentHTML("beforeend", toHtml);
      return container;
    })() : toHtml;
    let toStartMarker = document.createComment("[morph-start]");
    let toEndMarker = document.createComment("[morph-end]");
    toContainer.insertBefore(toStartMarker, toContainer.firstChild);
    toContainer.appendChild(toEndMarker);
    let toBlock = new Block(toStartMarker, toEndMarker);
    if (window.Alpine && window.Alpine.closestDataStack) {
      toContainer._x_dataStack = window.Alpine.closestDataStack(fromContainer);
      toContainer._x_dataStack && window.Alpine.cloneNode(fromContainer, toContainer);
    }
    context.patchChildren(fromBlock, toBlock);
  }
  function createMorphContext(options = {}) {
    let defaultGetKey = (el) => el.getAttribute("key");
    let noop = () => {
    };
    let context = {
      key: options.key || defaultGetKey,
      lookahead: options.lookahead || false,
      updating: options.updating || noop,
      updated: options.updated || noop,
      removing: options.removing || noop,
      removed: options.removed || noop,
      adding: options.adding || noop,
      added: options.added || noop
    };
    context.patch = function(from, to) {
      if (context.differentElementNamesTypesOrKeys(from, to)) {
        return context.swapElements(from, to);
      }
      let updateChildrenOnly = false;
      let skipChildren = false;
      let skipUntil = (predicate) => context.skipUntilCondition = predicate;
      if (shouldSkipChildren(context.updating, () => skipChildren = true, skipUntil, from, to, () => updateChildrenOnly = true))
        return;
      if (from.nodeType === 1 && window.Alpine) {
        window.Alpine.cloneNode(from, to);
        if (from._x_teleport && to._x_teleport) {
          context.patch(from._x_teleport, to._x_teleport);
        }
      }
      if (textOrComment(to)) {
        context.patchNodeValue(from, to);
        context.updated(from, to);
        return;
      }
      if (!updateChildrenOnly) {
        context.patchAttributes(from, to);
      }
      context.updated(from, to);
      if (!skipChildren) {
        context.patchChildren(from, to);
      }
    };
    context.differentElementNamesTypesOrKeys = function(from, to) {
      return from.nodeType != to.nodeType || from.nodeName != to.nodeName || context.getKey(from) != context.getKey(to);
    };
    context.swapElements = function(from, to) {
      if (shouldSkip(context.removing, from))
        return;
      let toCloned = to.cloneNode(true);
      if (shouldSkip(context.adding, toCloned))
        return;
      from.replaceWith(toCloned);
      context.removed(from);
      context.added(toCloned);
    };
    context.patchNodeValue = function(from, to) {
      let value = to.nodeValue;
      if (from.nodeValue !== value) {
        from.nodeValue = value;
      }
    };
    context.patchAttributes = function(from, to) {
      if (from._x_transitioning)
        return;
      if (from._x_isShown && !to._x_isShown) {
        return;
      }
      if (!from._x_isShown && to._x_isShown) {
        return;
      }
      let domAttributes = Array.from(from.attributes);
      let toAttributes = Array.from(to.attributes);
      for (let i = domAttributes.length - 1; i >= 0; i--) {
        let name = domAttributes[i].name;
        if (!to.hasAttribute(name)) {
          from.removeAttribute(name);
        }
      }
      for (let i = toAttributes.length - 1; i >= 0; i--) {
        let name = toAttributes[i].name;
        let value = toAttributes[i].value;
        if (from.getAttribute(name) !== value) {
          from.setAttribute(name, value);
        }
      }
    };
    context.patchChildren = function(from, to) {
      let fromKeys = context.keyToMap(from.children);
      let fromKeyHoldovers = {};
      let currentTo = getFirstNode(to);
      let currentFrom = getFirstNode(from);
      while (currentTo) {
        seedingMatchingId(currentTo, currentFrom);
        let toKey = context.getKey(currentTo);
        let fromKey = context.getKey(currentFrom);
        if (context.skipUntilCondition) {
          let fromDone = !currentFrom || context.skipUntilCondition(currentFrom);
          let toDone = !currentTo || context.skipUntilCondition(currentTo);
          if (fromDone && toDone) {
            context.skipUntilCondition = null;
          } else {
            if (!fromDone)
              currentFrom = currentFrom && getNextSibling(from, currentFrom);
            if (!toDone)
              currentTo = currentTo && getNextSibling(to, currentTo);
            continue;
          }
        }
        if (!currentFrom) {
          if (toKey && fromKeyHoldovers[toKey]) {
            let holdover = fromKeyHoldovers[toKey];
            from.appendChild(holdover);
            currentFrom = holdover;
            fromKey = context.getKey(currentFrom);
          } else {
            if (!shouldSkip(context.adding, currentTo)) {
              let clone2 = currentTo.cloneNode(true);
              from.appendChild(clone2);
              context.added(clone2);
            }
            currentTo = getNextSibling(to, currentTo);
            continue;
          }
        }
        let isIf = (node) => node && node.nodeType === 8 && node.textContent === "[if BLOCK]><![endif]";
        let isEnd = (node) => node && node.nodeType === 8 && node.textContent === "[if ENDBLOCK]><![endif]";
        if (isIf(currentTo) && isIf(currentFrom)) {
          let nestedIfCount = 0;
          let fromBlockStart = currentFrom;
          while (currentFrom) {
            let next = getNextSibling(from, currentFrom);
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
          context.patchChildren(fromBlock, toBlock);
          continue;
        }
        if (currentFrom.nodeType === 1 && context.lookahead && !currentFrom.isEqualNode(currentTo)) {
          let nextToElementSibling = getNextSibling(to, currentTo);
          let found = false;
          while (!found && nextToElementSibling) {
            if (nextToElementSibling.nodeType === 1 && currentFrom.isEqualNode(nextToElementSibling)) {
              found = true;
              currentFrom = context.addNodeBefore(from, currentTo, currentFrom);
              fromKey = context.getKey(currentFrom);
            }
            nextToElementSibling = getNextSibling(to, nextToElementSibling);
          }
        }
        if (toKey !== fromKey) {
          if (!toKey && fromKey) {
            fromKeyHoldovers[fromKey] = currentFrom;
            currentFrom = context.addNodeBefore(from, currentTo, currentFrom);
            fromKeyHoldovers[fromKey].remove();
            currentFrom = getNextSibling(from, currentFrom);
            currentTo = getNextSibling(to, currentTo);
            continue;
          }
          if (toKey && !fromKey) {
            if (fromKeys[toKey]) {
              currentFrom.replaceWith(fromKeys[toKey]);
              currentFrom = fromKeys[toKey];
              fromKey = context.getKey(currentFrom);
            }
          }
          if (toKey && fromKey) {
            let fromKeyNode = fromKeys[toKey];
            if (fromKeyNode) {
              fromKeyHoldovers[fromKey] = currentFrom;
              currentFrom.replaceWith(fromKeyNode);
              currentFrom = fromKeyNode;
              fromKey = context.getKey(currentFrom);
            } else {
              fromKeyHoldovers[fromKey] = currentFrom;
              currentFrom = context.addNodeBefore(from, currentTo, currentFrom);
              fromKeyHoldovers[fromKey].remove();
              currentFrom = getNextSibling(from, currentFrom);
              currentTo = getNextSibling(to, currentTo);
              continue;
            }
          }
        }
        let currentFromNext = currentFrom && getNextSibling(from, currentFrom);
        context.patch(currentFrom, currentTo);
        currentTo = currentTo && getNextSibling(to, currentTo);
        currentFrom = currentFromNext;
      }
      let removals = [];
      while (currentFrom) {
        if (!shouldSkip(context.removing, currentFrom))
          removals.push(currentFrom);
        currentFrom = getNextSibling(from, currentFrom);
      }
      while (removals.length) {
        let domForRemoval = removals.shift();
        domForRemoval.remove();
        context.removed(domForRemoval);
      }
    };
    context.getKey = function(el) {
      return el && el.nodeType === 1 && context.key(el);
    };
    context.keyToMap = function(els2) {
      let map = {};
      for (let el of els2) {
        let theKey = context.getKey(el);
        if (theKey) {
          map[theKey] = el;
        }
      }
      return map;
    };
    context.addNodeBefore = function(parent, node, beforeMe) {
      if (!shouldSkip(context.adding, node)) {
        let clone2 = node.cloneNode(true);
        parent.insertBefore(clone2, beforeMe);
        context.added(clone2);
        return clone2;
      }
      return node;
    };
    return context;
  }
  morph.step = () => {
  };
  morph.log = () => {
  };
  function shouldSkip(hook, ...args) {
    let skip = false;
    hook(...args, () => skip = true);
    return skip;
  }
  function shouldSkipChildren(hook, skipChildren, skipUntil, ...args) {
    let skip = false;
    hook(...args, () => skip = true, skipChildren, skipUntil);
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
      while (currentNode && currentNode !== this.endComment) {
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
  function seedingMatchingId(to, from) {
    let fromId = from && from._x_bindings && from._x_bindings.id;
    if (!fromId)
      return;
    if (!to.setAttribute)
      return;
    to.setAttribute("id", fromId);
    to.id = fromId;
  }
  function src_default8(Alpine24) {
    Alpine24.morph = morph;
    Alpine24.morphBetween = morphBetween;
  }
  var module_default8 = src_default8;

  // ../alpine/packages/mask/dist/module.esm.js
  function src_default9(Alpine24) {
    Alpine24.directive("mask", (el, { value, expression }, { effect, evaluateLater, cleanup }) => {
      let templateFn = () => expression;
      let lastInputValue = "";
      queueMicrotask(() => {
        if (["function", "dynamic"].includes(value)) {
          let evaluator = evaluateLater(expression);
          effect(() => {
            templateFn = (input) => {
              let result;
              Alpine24.dontAutoEvaluateFunctions(() => {
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
        if (el._x_model) {
          if (el._x_model.get() === el.value)
            return;
          if (el._x_model.get() === null && el.value === "")
            return;
          el._x_model.set(el.value);
        }
      });
      const controller = new AbortController();
      cleanup(() => {
        controller.abort();
      });
      el.addEventListener("input", () => processInputValue(el), {
        signal: controller.signal,
        capture: true
      });
      el.addEventListener("blur", () => processInputValue(el, false), { signal: controller.signal });
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
    let newPosition = buildUp(
      template,
      stripDown(
        template,
        beforeLeftOfCursorBeforeFormatting
      )
    ).length;
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
    if (thousands === null || thousands === void 0) {
      thousands = delimiter === "," ? "." : ",";
    }
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
  var module_default9 = src_default9;

  // js/lifecycle.js
  var import_alpinejs5 = __toESM(require_module_cjs());
  function start() {
    setTimeout(() => ensureLivewireScriptIsntMisplaced());
    dispatch(document, "livewire:init");
    dispatch(document, "livewire:initializing");
    import_alpinejs5.default.plugin(module_default8);
    import_alpinejs5.default.plugin(history2);
    import_alpinejs5.default.plugin(module_default4);
    import_alpinejs5.default.plugin(module_default5);
    import_alpinejs5.default.plugin(module_default6);
    import_alpinejs5.default.plugin(module_default);
    import_alpinejs5.default.plugin(module_default7);
    import_alpinejs5.default.plugin(module_default2);
    import_alpinejs5.default.plugin(module_default3);
    import_alpinejs5.default.plugin(navigate_default);
    import_alpinejs5.default.plugin(module_default9);
    import_alpinejs5.default.addRootSelector(() => "[wire\\:id]");
    import_alpinejs5.default.onAttributesAdded((el, attributes) => {
      if (!Array.from(attributes).some((attribute) => matchesForLivewireDirective(attribute.name)))
        return;
      let component = findComponentByEl(el, false);
      if (!component)
        return;
      attributes.forEach((attribute) => {
        if (!matchesForLivewireDirective(attribute.name))
          return;
        let directive2 = extractDirective(el, attribute.name);
        trigger("directive.init", { el, component, directive: directive2, cleanup: (callback) => {
          import_alpinejs5.default.onAttributeRemoved(el, directive2.raw, callback);
        } });
      });
    });
    import_alpinejs5.default.interceptInit(
      import_alpinejs5.default.skipDuringClone(
        (el) => {
          if (!Array.from(el.attributes).some((attribute) => matchesForLivewireDirective(attribute.name)))
            return;
          if (el.hasAttribute("wire:id") && !el.__livewire && !hasComponent(el.getAttribute("wire:id"))) {
            let component2 = initComponent(el);
            import_alpinejs5.default.onAttributeRemoved(el, "wire:id", () => {
              destroyComponent(component2.id);
            });
          }
          let directives = Array.from(el.getAttributeNames()).filter((name) => matchesForLivewireDirective(name)).map((name) => extractDirective(el, name));
          directives.forEach((directive2) => {
            trigger("directive.global.init", { el, directive: directive2, cleanup: (callback) => {
              import_alpinejs5.default.onAttributeRemoved(el, directive2.raw, callback);
            } });
          });
          let component = findComponentByEl(el, false);
          if (component) {
            trigger("element.init", { el, component });
            directives.forEach((directive2) => {
              trigger("directive.init", { el, component, directive: directive2, cleanup: (callback) => {
                import_alpinejs5.default.onAttributeRemoved(el, directive2.raw, callback);
              } });
            });
          }
        },
        (el) => {
          if (!Array.from(el.attributes).some((attribute) => matchesForLivewireDirective(attribute.name)))
            return;
          let directives = Array.from(el.getAttributeNames()).filter((name) => matchesForLivewireDirective(name)).map((name) => extractDirective(el, name));
          directives.forEach((directive2) => {
            trigger("directive.global.init", { el, directive: directive2, cleanup: (callback) => {
              import_alpinejs5.default.onAttributeRemoved(el, directive2.raw, callback);
            } });
          });
        }
      )
    );
    import_alpinejs5.default.start();
    setTimeout(() => window.Livewire.initialRenderIsFinished = true);
    dispatch(document, "livewire:initialized");
  }
  function ensureLivewireScriptIsntMisplaced() {
    let el = document.querySelector("script[data-update-uri][data-csrf]");
    if (!el)
      return;
    let livewireEl = el.closest("[wire\\:id]");
    if (livewireEl) {
      console.warn("Livewire: missing closing tags found. Ensure your template elements contain matching closing tags.", livewireEl);
    }
  }

  // js/index.js
  var import_alpinejs22 = __toESM(require_module_cjs());

  // js/features/supportListeners.js
  on("effect", ({ component, effects }) => {
    registerListeners(component, effects.listeners || []);
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
        if (!e.__livewire)
          return;
        if (e.bubbles)
          return;
        if (e.__livewire)
          e.__livewire.receivedBy.push(component.id);
        component.$wire.call("__dispatch", name, e.detail || {});
      });
    });
  }

  // js/features/supportScriptsAndAssets.js
  var import_alpinejs7 = __toESM(require_module_cjs());

  // js/evaluator.js
  var import_alpinejs6 = __toESM(require_module_cjs());
  function evaluateExpression(component, el, expression, options = {}) {
    options = {
      ...{
        scope: {
          $wire: component.$wire
        },
        context: component.$wire,
        ...options.scope,
        ...options.context
      },
      ...options
    };
    return import_alpinejs6.default.evaluate(el, expression, options);
  }
  function evaluateActionExpression(component, el, expression, options = {}) {
    let negated = false;
    if (expression.startsWith("!")) {
      negated = true;
      expression = expression.slice(1).trim();
    }
    let contextualExpression = negated ? `! $wire.${expression}` : `$wire.${expression}`;
    return import_alpinejs6.default.evaluate(el, contextualExpression, options);
  }
  function evaluateActionExpressionWithoutComponentScope(el, expression, options = {}) {
    let negated = false;
    if (expression.startsWith("!")) {
      negated = true;
      expression = expression.slice(1).trim();
    }
    let contextualExpression = negated ? `! $wire.${expression}` : `$wire.${expression}`;
    return import_alpinejs6.default.evaluate(el, contextualExpression, options);
  }

  // js/features/supportScriptsAndAssets.js
  var executedScripts = /* @__PURE__ */ new WeakMap();
  var executedAssets = /* @__PURE__ */ new Set();
  on("payload.intercept", async ({ assets }) => {
    if (!assets)
      return;
    for (let [key, asset] of Object.entries(assets)) {
      await onlyIfAssetsHaventBeenLoadedAlreadyOnThisPage(key, async () => {
        await addAssetsToHeadTagOfPage(asset);
      });
    }
  });
  on("component.init", ({ component }) => {
    let assets = component.snapshot.memo.assets;
    if (assets) {
      assets.forEach((key) => {
        if (executedAssets.has(key))
          return;
        executedAssets.add(key);
      });
    }
  });
  on("effect", ({ component, effects }) => {
    let scripts = effects.scripts;
    if (scripts) {
      Object.entries(scripts).forEach(([key, content]) => {
        onlyIfScriptHasntBeenRunAlreadyForThisComponent(component, key, () => {
          let scriptContent = extractScriptTagContent(content);
          import_alpinejs7.default.dontAutoEvaluateFunctions(() => {
            evaluateExpression(component, component.el, scriptContent, {
              scope: {
                "$wire": component.$wire
              }
            });
          });
        });
      });
    }
  });
  function onlyIfScriptHasntBeenRunAlreadyForThisComponent(component, key, callback) {
    if (executedScripts.has(component)) {
      let alreadyRunKeys2 = executedScripts.get(component);
      if (alreadyRunKeys2.includes(key))
        return;
    }
    callback();
    if (!executedScripts.has(component))
      executedScripts.set(component, []);
    let alreadyRunKeys = executedScripts.get(component);
    alreadyRunKeys.push(key);
    executedScripts.set(component, alreadyRunKeys);
  }
  function extractScriptTagContent(rawHtml) {
    let scriptRegex = /<script\b[^>]*>([\s\S]*?)<\/script>/gm;
    let matches3 = scriptRegex.exec(rawHtml);
    let innards = matches3 && matches3[1] ? matches3[1].trim() : "";
    return innards;
  }
  async function onlyIfAssetsHaventBeenLoadedAlreadyOnThisPage(key, callback) {
    if (executedAssets.has(key))
      return;
    executedAssets.add(key);
    await callback();
  }
  async function addAssetsToHeadTagOfPage(rawHtml) {
    let newDocument = new DOMParser().parseFromString(rawHtml, "text/html");
    let newHead = document.adoptNode(newDocument.head);
    for (let child of newHead.children) {
      try {
        await runAssetSynchronously(child);
      } catch (error2) {
      }
    }
  }
  async function runAssetSynchronously(child) {
    return new Promise((resolve, reject) => {
      if (isScript2(child)) {
        let script = cloneScriptTag2(child);
        if (script.src) {
          script.onload = () => resolve();
          script.onerror = () => reject();
        } else {
          resolve();
        }
        document.head.appendChild(script);
      } else {
        document.head.appendChild(child);
        resolve();
      }
    });
  }
  function isScript2(el) {
    return el.tagName.toLowerCase() === "script";
  }
  function cloneScriptTag2(el) {
    let script = document.createElement("script");
    script.textContent = el.textContent;
    script.async = el.async;
    for (let attr of el.attributes) {
      script.setAttribute(attr.name, attr.value);
    }
    return script;
  }

  // js/features/supportJsEvaluation.js
  var import_alpinejs8 = __toESM(require_module_cjs());
  import_alpinejs8.default.magic("js", (el) => {
    let component = findComponentByEl(el);
    return component.$wire.js;
  });
  on("effect", ({ component, effects }) => {
    let js = effects.js;
    let xjs = effects.xjs;
    if (js) {
      Object.entries(js).forEach(([method, body]) => {
        overrideMethod(component, method, () => {
          evaluateExpression(component, component.el, body);
        });
      });
    }
    if (xjs) {
      xjs.forEach(({ expression, params }) => {
        params = Object.values(params);
        evaluateExpression(component, component.el, expression, { scope: component.jsActions, params });
      });
    }
  });

  // js/morph.js
  var import_alpinejs9 = __toESM(require_module_cjs());
  function morph2(component, el, html) {
    let wrapperTag = el.parentElement ? el.parentElement.tagName.toLowerCase() : "div";
    let customElement = customElements.get(wrapperTag);
    wrapperTag = customElement ? customElement.name : wrapperTag;
    let wrapper = document.createElement(wrapperTag);
    wrapper.innerHTML = html;
    let parentComponent;
    try {
      parentComponent = findComponentByEl(el.parentElement);
    } catch (e) {
    }
    parentComponent && (wrapper.__livewire = parentComponent);
    let to = wrapper.firstElementChild;
    to.setAttribute("wire:snapshot", component.snapshotEncoded);
    let effects = { ...component.effects };
    delete effects.html;
    to.setAttribute("wire:effects", JSON.stringify(effects));
    to.__livewire = component;
    trigger("morph", { el, toEl: to, component });
    let existingComponentsMap = {};
    el.querySelectorAll("[wire\\:id]").forEach((component2) => {
      existingComponentsMap[component2.getAttribute("wire:id")] = component2;
    });
    to.querySelectorAll("[wire\\:id]").forEach((child) => {
      if (child.hasAttribute("wire:snapshot"))
        return;
      let wireId = child.getAttribute("wire:id");
      let existingComponent = existingComponentsMap[wireId];
      if (existingComponent) {
        child.replaceWith(existingComponent.cloneNode(true));
      }
    });
    import_alpinejs9.default.morph(el, to, getMorphConfig(component));
    trigger("morphed", { el, component });
  }
  function morphFragment(component, startNode, endNode, toHTML) {
    let fromContainer = startNode.parentElement;
    let fromContainerTag = fromContainer ? fromContainer.tagName.toLowerCase() : "div";
    let toContainer = document.createElement(fromContainerTag);
    toContainer.innerHTML = toHTML;
    toContainer.__livewire = component;
    let parentElement = component.el.parentElement;
    let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : "div";
    let parentComponent;
    try {
      parentComponent = parentElement ? findComponentByEl(parentElement) : null;
    } catch (e) {
    }
    if (parentComponent) {
      let parentProviderWrapper = document.createElement(parentElementTag);
      parentProviderWrapper.appendChild(toContainer);
      parentProviderWrapper.__livewire = parentComponent;
    }
    trigger("island.morph", { startNode, endNode, component });
    import_alpinejs9.default.morphBetween(startNode, endNode, toContainer, getMorphConfig(component));
    trigger("island.morphed", { startNode, endNode, component });
  }
  function getMorphConfig(component) {
    return {
      updating: (el, toEl, childrenOnly, skip, skipChildren, skipUntil) => {
        if (isStartFragmentMarker(el) && isStartFragmentMarker(toEl)) {
          let metadata = extractFragmentMetadataFromMarkerNode(toEl);
          if (metadata.mode !== "morph") {
            skipUntil((node) => {
              if (isEndFragmentMarker(node)) {
                let endMarkerMetadata = extractFragmentMetadataFromMarkerNode(node);
                return endMarkerMetadata.token === metadata.token;
              }
              return false;
            });
          }
        }
        if (isntElement(el))
          return;
        trigger("morph.updating", { el, toEl, component, skip, childrenOnly, skipChildren, skipUntil });
        if (el.__livewire_replace === true)
          el.innerHTML = toEl.innerHTML;
        if (el.__livewire_replace_self === true) {
          el.outerHTML = toEl.outerHTML;
          return skip();
        }
        if (el.__livewire_ignore === true)
          return skip();
        if (el.__livewire_ignore_self === true)
          childrenOnly();
        if (el.__livewire_ignore_children === true)
          return skipChildren();
        if (isComponentRootEl(el) && el.getAttribute("wire:id") !== component.id)
          return skip();
        if (isComponentRootEl(el))
          toEl.__livewire = component;
      },
      updated: (el) => {
        if (isntElement(el))
          return;
        trigger("morph.updated", { el, component });
      },
      removing: (el, skip) => {
        if (isntElement(el))
          return;
        trigger("morph.removing", { el, component, skip });
      },
      removed: (el) => {
        if (isntElement(el))
          return;
        trigger("morph.removed", { el, component });
      },
      adding: (el) => {
        trigger("morph.adding", { el, component });
      },
      added: (el) => {
        if (isntElement(el))
          return;
        const findComponentByElId = findComponentByEl(el).id;
        trigger("morph.added", { el });
      },
      key: (el) => {
        if (isntElement(el))
          return;
        return el.hasAttribute(`wire:id`) ? el.getAttribute(`wire:id`) : el.hasAttribute(`wire:key`) ? el.getAttribute(`wire:key`) : el.id;
      },
      lookahead: false
    };
  }
  function isntElement(el) {
    return typeof el.hasAttribute !== "function";
  }
  function isComponentRootEl(el) {
    return el.hasAttribute("wire:id");
  }

  // js/features/supportMorphDom.js
  interceptMessage(({ message, onSuccess }) => {
    onSuccess(({ payload, onMorph }) => {
      onMorph(() => {
        let html = payload.effects.html;
        if (!html)
          return;
        morph2(message.component, message.component.el, html);
      });
    });
  });

  // js/features/supportDispatches.js
  on("effect", ({ component, effects }) => {
    queueMicrotask(() => {
      queueMicrotask(() => {
        queueMicrotask(() => {
          dispatchEvents(component, effects.dispatches || []);
        });
      });
    });
  });
  function dispatchEvents(component, dispatches) {
    dispatches.forEach(({ name, params = {}, self: self2 = false, component: componentName, ref, el }) => {
      if (self2)
        dispatchSelf(component, name, params);
      else if (componentName)
        dispatchTo(componentName, name, params);
      else if (ref)
        dispatchRef(component, ref, name, params);
      else if (el)
        dispatchEl(component, el, name, params);
      else
        dispatch2(component, name, params);
    });
  }

  // js/features/supportDisablingFormsDuringRequest.js
  var import_alpinejs10 = __toESM(require_module_cjs());
  var cleanups = new Bag();
  on("directive.init", ({ el, directive: directive2, cleanup, component }) => setTimeout(() => {
    if (directive2.value !== "submit")
      return;
    el.addEventListener("submit", () => {
      let componentId = directive2.expression.startsWith("$parent") ? component.parent.id : component.id;
      let cleanup2 = disableForm(el);
      cleanups.add(componentId, cleanup2);
    });
  }));
  on("commit", ({ component, respond }) => {
    respond(() => {
      cleanups.each(component.id, (i) => i());
      cleanups.remove(component.id);
    });
  });
  function disableForm(formEl) {
    let undos = [];
    import_alpinejs10.default.walk(formEl, (el, skip) => {
      if (!formEl.contains(el))
        return;
      if (el.hasAttribute("wire:ignore"))
        return skip();
      if (shouldMarkDisabled(el)) {
        undos.push(markDisabled(el));
      } else if (shouldMarkReadOnly(el)) {
        undos.push(markReadOnly(el));
      }
    });
    return () => {
      while (undos.length > 0)
        undos.shift()();
    };
  }
  function shouldMarkDisabled(el) {
    let tag = el.tagName.toLowerCase();
    if (tag === "select")
      return true;
    if (tag === "button" && el.type === "submit")
      return true;
    if (tag === "input" && (el.type === "checkbox" || el.type === "radio"))
      return true;
    return false;
  }
  function shouldMarkReadOnly(el) {
    return ["input", "textarea"].includes(el.tagName.toLowerCase());
  }
  function markDisabled(el) {
    let undo = el.disabled ? () => {
    } : () => el.disabled = false;
    el.disabled = true;
    return undo;
  }
  function markReadOnly(el) {
    let undo = el.readOnly ? () => {
    } : () => el.readOnly = false;
    el.readOnly = true;
    return undo;
  }

  // js/features/supportFileDownloads.js
  on("commit", ({ succeed }) => {
    succeed(({ effects }) => {
      let download = effects.download;
      if (!download)
        return;
      let urlObject = window.webkitURL || window.URL;
      let url = urlObject.createObjectURL(
        base64toBlob(download.content, download.contentType)
      );
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
    for (let offset2 = 0; offset2 < byteCharacters.length; offset2 += sliceSize) {
      let slice = byteCharacters.slice(offset2, offset2 + sliceSize);
      let byteNumbers = new Array(slice.length);
      for (let i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
      }
      let byteArray = new Uint8Array(byteNumbers);
      byteArrays.push(byteArray);
    }
    return new Blob(byteArrays, { type: contentType });
  }

  // js/features/supportQueryString.js
  var import_alpinejs11 = __toESM(require_module_cjs());
  on("effect", ({ component, effects, cleanup }) => {
    let queryString = effects["url"];
    if (!queryString)
      return;
    Object.entries(queryString).forEach(([key, value]) => {
      let { name, as, use, alwaysShow, except } = normalizeQueryStringEntry(key, value);
      if (!as)
        as = name;
      let initialValue = [false, null, void 0].includes(except) ? dataGet(component.ephemeral, name) : except;
      let { replace: replace2, push: push2, pop } = track(as, initialValue, alwaysShow, except);
      if (use === "replace") {
        let effectReference = import_alpinejs11.default.effect(() => {
          replace2(dataGet(component.reactive, name));
        });
        cleanup(() => import_alpinejs11.default.release(effectReference));
      } else if (use === "push") {
        let forgetCommitHandler = on("commit", ({ component: commitComponent, succeed }) => {
          if (component !== commitComponent)
            return;
          let beforeValue = dataGet(component.canonical, name);
          succeed(() => {
            let afterValue = dataGet(component.canonical, name);
            if (JSON.stringify(beforeValue) === JSON.stringify(afterValue))
              return;
            push2(afterValue);
          });
        });
        let forgetPopHandler = pop(async (newValue) => {
          await component.$wire.set(name, newValue);
          document.querySelectorAll("input").forEach((el) => {
            el._x_forceModelUpdate && el._x_forceModelUpdate(el._x_model.get());
          });
        });
        cleanup(() => {
          forgetCommitHandler();
          forgetPopHandler();
        });
      }
    });
  });
  function normalizeQueryStringEntry(key, value) {
    let defaults2 = { use: "replace", alwaysShow: false };
    if (typeof value === "string") {
      return { ...defaults2, name: value, as: value };
    } else {
      let fullerDefaults = { ...defaults2, name: key, as: key };
      return { ...fullerDefaults, ...value };
    }
  }

  // js/features/supportLaravelEcho.js
  on("request", ({ options }) => {
    if (window.Echo) {
      options.headers["X-Socket-ID"] = window.Echo.socketId();
    }
  });
  on("effect", ({ component, effects }) => {
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
          let handler = (e) => dispatchSelf(component, event, [e]);
          window.Echo[channel_type](channel).listen(event_name, handler);
          component.addCleanup(() => {
            window.Echo[channel_type](channel).stopListening(event_name, handler);
          });
        } else if (channel_type == "presence") {
          if (["here", "joining", "leaving"].includes(event_name)) {
            window.Echo.join(channel)[event_name]((e) => {
              dispatchSelf(component, event, [e]);
            });
          } else {
            let handler = (e) => dispatchSelf(component, event, [e]);
            window.Echo.join(channel).listen(event_name, handler);
            component.addCleanup(() => {
              window.Echo.leaveChannel(channel);
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

  // js/features/supportStreaming.js
  interceptMessage(({ message, onStream }) => {
    onStream(({ streamedJson }) => {
      let { id, type, name, el, ref, content, mode: mode2 } = streamedJson;
      if (type === "island")
        return;
      let component = findComponent(id);
      let targetEl = null;
      if (type === "directive") {
        replaceEl = component.el.querySelector(`[wire\\:stream.replace="${name}"]`);
        if (replaceEl) {
          targetEl = replaceEl;
          mode2 = "replace";
        } else {
          targetEl = component.el.querySelector(`[wire\\:stream="${name}"]`);
        }
      } else if (type === "ref") {
        targetEl = findRefEl(component, ref);
      } else if (type === "element") {
        targetEl = component.el.querySelector(el);
      }
      if (!targetEl)
        return;
      if (mode2 === "replace") {
        targetEl.innerHTML = content;
      } else {
        targetEl.insertAdjacentHTML("beforeend", content);
      }
    });
  });

  // js/features/supportNavigate.js
  document.addEventListener("livewire:initialized", () => {
    shouldHideProgressBar() && Alpine.navigate.disableProgressBar();
  });
  document.addEventListener("alpine:navigate", (e) => forwardEvent("livewire:navigate", e));
  document.addEventListener("alpine:navigating", (e) => forwardEvent("livewire:navigating", e));
  document.addEventListener("alpine:navigated", (e) => forwardEvent("livewire:navigated", e));
  function forwardEvent(name, original) {
    let event = new CustomEvent(name, { cancelable: true, bubbles: true, detail: original.detail });
    document.dispatchEvent(event);
    if (event.defaultPrevented) {
      original.preventDefault();
    }
  }
  function shouldRedirectUsingNavigateOr(effects, url, or) {
    let forceNavigate = effects.redirectUsingNavigate;
    if (forceNavigate) {
      Alpine.navigate(url);
    } else {
      or();
    }
  }
  function shouldHideProgressBar() {
    if (!!document.querySelector("[data-no-progress-bar]"))
      return true;
    if (window.livewireScriptConfig && window.livewireScriptConfig.progressBar === "data-no-progress-bar")
      return true;
    return false;
  }

  // js/features/supportRedirects.js
  on("effect", ({ effects, request }) => {
    if (!effects["redirect"])
      return;
    let preventDefault = false;
    request.onRedirect({ url: effects["redirect"], preventDefault: () => preventDefault = true });
    if (preventDefault)
      return;
    let url = effects["redirect"];
    shouldRedirectUsingNavigateOr(effects, url, () => {
      window.location.href = url;
    });
  });

  // js/features/supportIslands.js
  interceptAction(({ action }) => {
    let origin = action.origin;
    if (!origin)
      return;
    let el = origin.el;
    let islandAttributeName = el.getAttribute("wire:island");
    let prependIslandAttributeName = el.getAttribute("wire:island.prepend");
    let appendIslandAttributeName = el.getAttribute("wire:island.append");
    let islandName = islandAttributeName || prependIslandAttributeName || appendIslandAttributeName;
    if (islandName) {
      let mode2 = appendIslandAttributeName ? "append" : prependIslandAttributeName ? "prepend" : "morph";
      action.mergeMetadata({
        island: {
          name: islandName,
          mode: mode2
        }
      });
      return;
    }
    let fragment = closestIsland(origin.el);
    if (!fragment)
      return;
    action.mergeMetadata({
      island: {
        name: fragment.metadata.name,
        mode: "morph"
      }
    });
  });
  interceptMessage(({ message, onSuccess, onStream }) => {
    onStream(({ streamedJson }) => {
      let { type, islandFragment } = streamedJson;
      if (type !== "island")
        return;
      renderIsland(message.component, islandFragment);
    });
    onSuccess(({ payload, onMorph }) => {
      onMorph(() => {
        let fragments = payload.effects.islandFragments || [];
        fragments.forEach((fragmentHtml) => {
          renderIsland(message.component, fragmentHtml);
        });
      });
    });
  });
  function closestIsland(el) {
    return closestFragment(el, {
      isMatch: ({ type }) => {
        return type === "island";
      }
    });
  }
  function renderIsland(component, islandHtml) {
    let metadata = extractFragmentMetadataFromHtml(islandHtml);
    let fragment = findFragment(component.el, {
      isMatch: ({ type, token }) => {
        return type === metadata.type && token === metadata.token;
      }
    });
    if (!fragment)
      return;
    let incomingMetadata = extractFragmentMetadataFromHtml(islandHtml);
    let strippedContent = extractInnerHtmlFromFragmentHtml(islandHtml);
    let parentElement = fragment.startMarkerNode.parentElement;
    let parentElementTag = parentElement ? parentElement.tagName.toLowerCase() : "div";
    mode = incomingMetadata.mode || "morph";
    if (mode === "morph") {
      morphFragment(component, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent);
    } else if (mode === "append") {
      fragment.append(parentElementTag, strippedContent);
    } else if (mode === "prepend") {
      fragment.prepend(parentElementTag, strippedContent);
    }
  }

  // js/features/supportSlots.js
  interceptMessage(({ message, onSuccess, onStream }) => {
    onSuccess(({ payload, onMorph }) => {
      onMorph(() => {
        let fragments = payload.effects.slotFragments || [];
        fragments.forEach((fragmentHtml) => {
          renderSlot(message.component, fragmentHtml);
        });
      });
    });
  });
  function renderSlot(component, fragmentHtml) {
    let metadata = extractFragmentMetadataFromHtml(fragmentHtml);
    let targetComponent = findComponent(metadata.id);
    let fragment = findFragment(targetComponent.el, {
      isMatch: ({ name, token }) => {
        return name === metadata.name && token === metadata.token;
      }
    });
    if (!fragment)
      return;
    let strippedContent = extractInnerHtmlFromFragmentHtml(fragmentHtml);
    morphFragment(targetComponent, fragment.startMarkerNode, fragment.endMarkerNode, strippedContent);
  }

  // js/features/supportDataLoading.js
  interceptMessage(({ actions, onSend, onFinish }) => {
    let undos = [];
    onSend(() => {
      actions.forEach((action) => {
        let origin = action.origin;
        if (!origin || !origin.el)
          return;
        if (action.metadata?.type === "poll")
          return;
        origin.el.setAttribute("data-loading", "true");
        undos.push(() => {
          origin.el.removeAttribute("data-loading");
        });
      });
    });
    onFinish(() => undos.forEach((undo) => undo()));
  });

  // js/features/supportPreserveScroll.js
  interceptMessage(({ actions, onSuccess }) => {
    onSuccess(({ onSync, onMorph, onRender }) => {
      actions.forEach((action) => {
        let origin = action.origin;
        if (!origin || !origin.directive)
          return;
        let directive2 = origin.directive;
        if (!directive2.modifiers.includes("preserve-scroll"))
          return;
        let oldHeight;
        let oldScroll;
        onSync(() => {
          oldHeight = document.body.scrollHeight;
          oldScroll = window.scrollY;
        });
        onMorph(() => {
          let heightDiff = document.body.scrollHeight - oldHeight;
          window.scrollTo(0, oldScroll + heightDiff);
          oldHeight = null;
          oldScroll = null;
        });
      });
    });
  });

  // js/features/supportWireIntersect.js
  var import_alpinejs12 = __toESM(require_module_cjs());
  import_alpinejs12.default.interceptInit((el) => {
    for (let i = 0; i < el.attributes.length; i++) {
      if (el.attributes[i].name.startsWith("wire:intersect")) {
        let { name, value } = el.attributes[i];
        let directive2 = extractDirective(el, name);
        let modifierString = name.split("wire:intersect")[1];
        let expression = value.trim();
        import_alpinejs12.default.bind(el, {
          ["x-intersect" + modifierString](e) {
            directive2.eventContext = e;
            let component = el.closest("[wire\\:id]")?.__livewire;
            component.addActionContext({
              el,
              directive: directive2
            });
            evaluateActionExpression(component, el, expression);
          }
        });
      }
    }
  });

  // js/features/supportWireSort.js
  var import_alpinejs13 = __toESM(require_module_cjs());
  import_alpinejs13.default.interceptInit((el) => {
    for (let i = 0; i < el.attributes.length; i++) {
      if (el.attributes[i].name.startsWith("wire:sort:item")) {
        let directive2 = extractDirective(el, el.attributes[i].name);
        let modifierString = directive2.modifiers.join(".");
        let expression = directive2.expression;
        import_alpinejs13.default.bind(el, {
          ["x-sort:item" + modifierString]() {
            return expression;
          }
        });
      } else if (el.attributes[i].name.startsWith("wire:sort:group")) {
        return;
      } else if (el.attributes[i].name.startsWith("wire:sort")) {
        let directive2 = extractDirective(el, el.attributes[i].name);
        let attribute = directive2.rawName.replace("wire:", "x-");
        if (directive2.modifiers.includes("async")) {
          attribute = attribute.replace(".async", "");
        }
        if (directive2.modifiers.includes("renderless")) {
          attribute = attribute.replace(".renderless", "");
        }
        let expression = directive2.expression;
        import_alpinejs13.default.bind(el, {
          [attribute]() {
            setNextActionOrigin({ el, directive: directive2 });
            return evaluateActionExpressionWithoutComponentScope(el, expression, { scope: {
              $item: this.$item,
              $position: this.$position
            } });
          }
        });
      }
    }
  });

  // js/features/supportJsModules.js
  on("effect", ({ component, effects }) => {
    let scriptModuleHash = effects.scriptModule;
    if (scriptModuleHash) {
      let encodedName = component.name.replace(".", "--").replace("::", "---").replace(":", "----");
      let path = `/livewire/js/${encodedName}.js?v=${scriptModuleHash}`;
      import(path).then((module) => {
        module.run.call(component.$wire, [
          component.$wire
        ]);
      });
    }
  });

  // js/directives/wire-transition.js
  var import_alpinejs14 = __toESM(require_module_cjs());
  on("morph.added", ({ el }) => {
    el.__addedByMorph = true;
  });
  directive("transition", ({ el, directive: directive2, component, cleanup }) => {
    for (let i = 0; i < el.attributes.length; i++) {
      if (el.attributes[i].name.startsWith("wire:show")) {
        import_alpinejs14.default.bind(el, {
          [directive2.rawName.replace("wire:transition", "x-transition")]: directive2.expression
        });
        return;
      }
    }
    let visibility = import_alpinejs14.default.reactive({ state: el.__addedByMorph ? false : true });
    import_alpinejs14.default.bind(el, {
      [directive2.rawName.replace("wire:", "x-")]: "",
      "x-show"() {
        return visibility.state;
      }
    });
    el.__addedByMorph && setTimeout(() => visibility.state = true);
    let cleanups2 = [];
    cleanups2.push(on("morph.removing", ({ el: el2, skip }) => {
      skip();
      el2.addEventListener("transitionend", () => {
        el2.remove();
      });
      visibility.state = false;
      cleanups2.push(on("morph", ({ component: morphComponent }) => {
        if (morphComponent !== component)
          return;
        el2.remove();
        cleanups2.forEach((i) => i());
      }));
    }));
    cleanup(() => cleanups2.forEach((i) => i()));
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
  var import_alpinejs15 = __toESM(require_module_cjs());
  on("directive.init", ({ el, directive: directive2, cleanup, component }) => {
    if (["snapshot", "effects", "model", "init", "loading", "poll", "ignore", "id", "data", "key", "target", "dirty", "sort"].includes(directive2.value))
      return;
    if (customDirectiveHasBeenRegistered(directive2.value))
      return;
    let attribute = directive2.rawName.replace("wire:", "x-on:");
    if (directive2.value === "submit" && !directive2.modifiers.includes("prevent")) {
      attribute = attribute + ".prevent";
    }
    if (directive2.modifiers.includes("async")) {
      attribute = attribute.replace(".async", "");
    }
    if (directive2.modifiers.includes("renderless")) {
      attribute = attribute.replace(".renderless", "");
    }
    let cleanupBinding = import_alpinejs15.default.bind(el, {
      [attribute](e) {
        directive2.eventContext = e;
        directive2.wire = component.$wire;
        let execute = () => {
          callAndClearComponentDebounces(component, () => {
            setNextActionOrigin({ el, directive: directive2 });
            evaluateActionExpression(component, el, directive2.expression, { scope: { $event: e } });
          });
        };
        if (el.__livewire_confirm) {
          el.__livewire_confirm(() => {
            execute();
          }, () => {
            e.stopImmediatePropagation();
          });
        } else {
          execute();
        }
      }
    });
    cleanup(cleanupBinding);
  });

  // js/directives/wire-navigate.js
  var import_alpinejs16 = __toESM(require_module_cjs());
  import_alpinejs16.default.addInitSelector(() => `[wire\\:navigate]`);
  import_alpinejs16.default.addInitSelector(() => `[wire\\:navigate\\.hover]`);
  import_alpinejs16.default.addInitSelector(() => `[wire\\:navigate\\.preserve-scroll]`);
  import_alpinejs16.default.addInitSelector(() => `[wire\\:navigate\\.preserve-scroll\\.hover]`);
  import_alpinejs16.default.addInitSelector(() => `[wire\\:navigate\\.hover\\.preserve-scroll]`);
  import_alpinejs16.default.interceptInit(
    import_alpinejs16.default.skipDuringClone((el) => {
      if (el.hasAttribute("wire:navigate")) {
        import_alpinejs16.default.bind(el, { ["x-navigate"]: true });
      } else if (el.hasAttribute("wire:navigate.hover")) {
        import_alpinejs16.default.bind(el, { ["x-navigate.hover"]: true });
      } else if (el.hasAttribute("wire:navigate.preserve-scroll")) {
        import_alpinejs16.default.bind(el, { ["x-navigate.preserve-scroll"]: true });
      } else if (el.hasAttribute("wire:navigate.preserve-scroll.hover")) {
        import_alpinejs16.default.bind(el, { ["x-navigate.preserve-scroll.hover"]: true });
      } else if (el.hasAttribute("wire:navigate.hover.preserve-scroll")) {
        import_alpinejs16.default.bind(el, { ["x-navigate.hover.preserve-scroll"]: true });
      }
    })
  );
  document.addEventListener("alpine:navigating", () => {
    Livewire.all().forEach((component) => {
      component.inscribeSnapshotAndEffectsOnElement();
    });
  });

  // js/directives/wire-confirm.js
  directive("confirm", ({ el, directive: directive2 }) => {
    let message = directive2.expression;
    let shouldPrompt = directive2.modifiers.includes("prompt");
    message = message.replaceAll("\\n", "\n");
    if (message === "")
      message = "Are you sure?";
    el.__livewire_confirm = (action, instead) => {
      if (shouldPrompt) {
        let [question, expected] = message.split("|");
        if (!expected) {
          console.warn("Livewire: Must provide expectation with wire:confirm.prompt");
        } else {
          let input = prompt(question);
          if (input === expected) {
            action();
          } else {
            instead();
          }
        }
      } else {
        if (confirm(message))
          action();
        else
          instead();
      }
    };
  });

  // js/directives/wire-current.js
  var import_alpinejs17 = __toESM(require_module_cjs());
  import_alpinejs17.default.addInitSelector(() => `[wire\\:current]`);
  var onPageChanges = /* @__PURE__ */ new Map();
  document.addEventListener("livewire:navigated", () => {
    onPageChanges.forEach((i) => i(new URL(window.location.href)));
  });
  globalDirective("current", ({ el, directive: directive2, cleanup }) => {
    let expression = directive2.expression;
    let options = {
      exact: directive2.modifiers.includes("exact"),
      strict: directive2.modifiers.includes("strict")
    };
    if (expression.startsWith("#"))
      return;
    if (!el.hasAttribute("href"))
      return;
    let href = el.getAttribute("href");
    let hrefUrl = new URL(href, window.location.href);
    let classes = expression.split(" ").filter(String);
    let refreshCurrent = (url) => {
      if (pathMatches(hrefUrl, url, options)) {
        el.classList.add(...classes);
        el.setAttribute("data-current", "");
      } else {
        el.classList.remove(...classes);
        el.removeAttribute("data-current");
      }
    };
    refreshCurrent(new URL(window.location.href));
    onPageChanges.set(el, refreshCurrent);
    cleanup(() => onPageChanges.delete(el));
  });
  function pathMatches(hrefUrl, actualUrl, options) {
    if (hrefUrl.hostname !== actualUrl.hostname)
      return false;
    let hrefPath = options.strict ? hrefUrl.pathname : hrefUrl.pathname.replace(/\/+$/, "");
    let actualPath = options.strict ? actualUrl.pathname : actualUrl.pathname.replace(/\/+$/, "");
    if (options.exact) {
      return hrefPath === actualPath;
    }
    let hrefPathSegments = hrefPath.split("/");
    let actualPathSegments = actualPath.split("/");
    for (let i = 0; i < hrefPathSegments.length; i++) {
      if (hrefPathSegments[i] !== actualPathSegments[i])
        return false;
    }
    return true;
  }

  // js/directives/shared.js
  function toggleBooleanStateDirective(el, directive2, isTruthy, cachedDisplay = null) {
    isTruthy = directive2.modifiers.includes("remove") ? !isTruthy : isTruthy;
    if (directive2.modifiers.includes("class")) {
      let classes = directive2.expression.split(" ").filter(String);
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
      let cache = cachedDisplay ?? window.getComputedStyle(el, null).getPropertyValue("display");
      let display = ["inline", "list-item", "block", "table", "flex", "grid", "inline-flex"].filter((i) => directive2.modifiers.includes(i))[0] || "inline-block";
      display = directive2.modifiers.includes("remove") && !isTruthy ? cache : display;
      el.style.display = isTruthy ? display : "none";
    }
  }

  // js/directives/wire-offline.js
  var offlineHandlers = /* @__PURE__ */ new Set();
  var onlineHandlers = /* @__PURE__ */ new Set();
  window.addEventListener("offline", () => offlineHandlers.forEach((i) => i()));
  window.addEventListener("online", () => onlineHandlers.forEach((i) => i()));
  directive("offline", ({ el, directive: directive2, cleanup }) => {
    let setOffline = () => toggleBooleanStateDirective(el, directive2, true);
    let setOnline = () => toggleBooleanStateDirective(el, directive2, false);
    offlineHandlers.add(setOffline);
    onlineHandlers.add(setOnline);
    cleanup(() => {
      offlineHandlers.delete(setOffline);
      onlineHandlers.delete(setOnline);
    });
  });

  // js/directives/wire-loading.js
  directive("loading", ({ el, directive: directive2, component, cleanup }) => {
    let { targets, inverted } = getTargets(el);
    let [delay3, abortDelay] = applyDelay(directive2);
    let cleanupA = whenTargetsArePartOfRequest(component, el, targets, inverted, [
      () => delay3(() => toggleBooleanStateDirective(el, directive2, true)),
      () => abortDelay(() => toggleBooleanStateDirective(el, directive2, false))
    ]);
    let cleanupB = whenTargetsArePartOfFileUpload(component, targets, [
      () => delay3(() => toggleBooleanStateDirective(el, directive2, true)),
      () => abortDelay(() => toggleBooleanStateDirective(el, directive2, false))
    ]);
    cleanup(() => {
      cleanupA();
      cleanupB();
    });
  });
  function applyDelay(directive2) {
    if (!directive2.modifiers.includes("delay") || directive2.modifiers.includes("none"))
      return [(i) => i(), (i) => i()];
    let duration = 200;
    let delayModifiers = {
      "shortest": 50,
      "shorter": 100,
      "short": 150,
      "default": 200,
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
      async (callback) => {
        if (started) {
          await callback();
          started = false;
        } else {
          clearTimeout(timeout);
        }
      }
    ];
  }
  function whenTargetsArePartOfRequest(component, el, targets, inverted, [startLoading, endLoading]) {
    return interceptMessage(({ message, onSend, onFinish }) => {
      if (component !== message.component)
        return;
      let island = closestIsland(el);
      if (island && !message.hasActionForIsland(island)) {
        return;
      }
      if (!island && !message.hasActionForComponent()) {
        return;
      }
      let matches3 = true;
      onSend(({ payload }) => {
        if (targets.length > 0 && containsTargets(payload, targets) === inverted) {
          matches3 = false;
        }
        matches3 && startLoading();
      });
      onFinish(() => {
        matches3 && endLoading();
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
    let cleanupA = listen(window, "livewire-upload-start", (e) => {
      if (eventMismatch(e))
        return;
      startLoading();
    });
    let cleanupB = listen(window, "livewire-upload-finish", (e) => {
      if (eventMismatch(e))
        return;
      endLoading();
    });
    let cleanupC = listen(window, "livewire-upload-error", (e) => {
      if (eventMismatch(e))
        return;
      endLoading();
    });
    return () => {
      cleanupA();
      cleanupB();
      cleanupC();
    };
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
        if (property.includes(".")) {
          let propertyRoot = property.split(".")[0];
          if (propertyRoot === target)
            return true;
        }
        return property === target;
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
    let inverted = false;
    if (directives.has("target")) {
      let directive2 = directives.get("target");
      if (directive2.modifiers.includes("except"))
        inverted = true;
      directive2.methods.forEach(({ method, params }) => {
        targets.push({
          target: method,
          params: params && params.length > 0 ? quickHash(JSON.stringify(params)) : void 0
        });
      });
    } else {
      let nonActionOrModelLivewireDirectives = ["init", "dirty", "offline", "navigate", "target", "loading", "poll", "ignore", "key", "id"];
      directives.all().filter((i) => !nonActionOrModelLivewireDirectives.includes(i.value)).map((i) => i.expression.split("(")[0]).forEach((target) => targets.push({ target }));
    }
    return { targets, inverted };
  }
  function quickHash(subject) {
    return btoa(encodeURIComponent(subject));
  }

  // js/directives/wire-replace.js
  directive("replace", ({ el, directive: directive2 }) => {
    if (directive2.modifiers.includes("self")) {
      el.__livewire_replace_self = true;
    } else {
      el.__livewire_replace = true;
    }
  });

  // js/directives/wire-ignore.js
  directive("ignore", ({ el, directive: directive2 }) => {
    if (directive2.modifiers.includes("self")) {
      el.__livewire_ignore_self = true;
    } else if (directive2.modifiers.includes("children")) {
      el.__livewire_ignore_children = true;
    } else {
      el.__livewire_ignore = true;
    }
  });

  // js/directives/wire-cloak.js
  var import_alpinejs18 = __toESM(require_module_cjs());
  import_alpinejs18.default.interceptInit((el) => {
    if (el.hasAttribute("wire:cloak")) {
      import_alpinejs18.default.mutateDom(() => el.removeAttribute("wire:cloak"));
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
    let oldIsDirty = false;
    let initialDisplay = el.style.display;
    let refreshDirtyState = (isDirty) => {
      toggleBooleanStateDirective(el, directive2, isDirty, initialDisplay);
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
      targets = targets.concat(
        directives.get("target").expression.split(",").map((s) => s.trim())
      );
    }
    return targets;
  }

  // js/directives/wire-model.js
  var import_alpinejs19 = __toESM(require_module_cjs());
  directive("model", ({ el, directive: directive2, component, cleanup }) => {
    component = findComponentByEl(el);
    let { expression, modifiers } = directive2;
    if (!expression) {
      return console.warn("Livewire: [wire:model] is missing a value.", el);
    }
    if (componentIsMissingProperty(component, expression)) {
      return console.warn('Livewire: [wire:model="' + expression + '"] property does not exist on component: [' + component.name + "]", el);
    }
    if (el.type && el.type.toLowerCase() === "file") {
      return handleFileUpload(el, expression, component, cleanup);
    }
    let isLive = modifiers.includes("live");
    let isLazy = modifiers.includes("lazy") || modifiers.includes("change");
    let onBlur = modifiers.includes("blur");
    let isDebounced = modifiers.includes("debounce");
    let update = () => {
      setNextActionOrigin({ el, directive: directive2 });
      if (isLive || isDebounced) {
        setNextActionMetadata({ type: "model.live" });
      }
      expression.startsWith("$parent") ? component.$wire.$parent.$commit() : component.$wire.$commit();
    };
    let debouncedUpdate = isTextInput(el) && !isDebounced && isLive ? debounce(update, 150) : update;
    import_alpinejs19.default.bind(el, {
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
      let parent = findComponentByEl(component.el.parentElement, false);
      if (!parent)
        return true;
      return componentIsMissingProperty(parent, property.split("$parent.")[1]);
    }
    let baseProperty = property.split(".")[0];
    return !Object.keys(component.canonical).includes(baseProperty);
  }
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

  // js/directives/wire-init.js
  directive("init", ({ component, el, directive: directive2 }) => {
    let fullMethod = directive2.expression ? directive2.expression : "$refresh";
    setNextActionOrigin({ el, directive: directive2 });
    evaluateActionExpression(component, el, fullMethod);
  });

  // js/directives/wire-poll.js
  directive("poll", ({ el, directive: directive2, component }) => {
    let interval = extractDurationFrom(directive2.modifiers, 2e3);
    let { start: start2, pauseWhile, throttleWhile, stopWhen } = poll(() => {
      triggerComponentRequest(el, directive2, component);
    }, interval);
    start2();
    throttleWhile(() => theTabIsInTheBackground() && theDirectiveIsMissingKeepAlive(directive2));
    pauseWhile(() => theDirectiveHasVisible(directive2) && theElementIsNotInTheViewport(el));
    pauseWhile(() => theDirectiveIsOffTheElement(el));
    pauseWhile(() => livewireIsOffline());
    stopWhen(() => theElementIsDisconnected(el));
  });
  function triggerComponentRequest(el, directive2, component) {
    setNextActionOrigin({ el, directive: directive2 });
    setNextActionMetadata({ type: "poll" });
    let fullMethod = directive2.expression ? directive2.expression : "$refresh";
    evaluateActionExpression(component, el, fullMethod);
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

  // js/directives/wire-show.js
  var import_alpinejs20 = __toESM(require_module_cjs());
  import_alpinejs20.default.interceptInit((el) => {
    for (let i = 0; i < el.attributes.length; i++) {
      if (el.attributes[i].name.startsWith("wire:show")) {
        let { name, value } = el.attributes[i];
        let modifierString = name.split("wire:show")[1];
        let expression = value.trim();
        import_alpinejs20.default.bind(el, {
          ["x-show" + modifierString]() {
            return evaluateActionExpressionWithoutComponentScope(el, expression);
          }
        });
      }
    }
  });

  // js/directives/wire-text.js
  var import_alpinejs21 = __toESM(require_module_cjs());
  import_alpinejs21.default.interceptInit((el) => {
    for (let i = 0; i < el.attributes.length; i++) {
      if (el.attributes[i].name.startsWith("wire:text")) {
        let { name, value } = el.attributes[i];
        let modifierString = name.split("wire:text")[1];
        let expression = value.trim();
        import_alpinejs21.default.bind(el, {
          ["x-text" + modifierString]() {
            return evaluateActionExpressionWithoutComponentScope(el, expression);
          }
        });
      }
    }
  });

  // js/index.js
  var Livewire2 = {
    directive,
    dispatchTo,
    interceptMessage: (callback) => interceptMessage(callback),
    interceptRequest: (callback) => interceptRequest(callback),
    fireAction: (component, method, params = [], metadata = {}) => fireAction(component, method, params, metadata),
    start,
    first,
    find,
    getByName,
    all,
    hook: on,
    trigger,
    triggerAsync,
    dispatch: dispatchGlobal,
    on: on2,
    get navigate() {
      return import_alpinejs22.default.navigate;
    }
  };
  var warnAboutMultipleInstancesOf = (entity) => console.warn(`Detected multiple instances of ${entity} running`);
  if (window.Livewire)
    warnAboutMultipleInstancesOf("Livewire");
  if (window.Alpine)
    warnAboutMultipleInstancesOf("Alpine");
  window.Livewire = Livewire2;
  window.Alpine = import_alpinejs22.default;
  if (window.livewireScriptConfig === void 0) {
    window.Alpine.__fromLivewire = true;
    document.addEventListener("DOMContentLoaded", () => {
      if (window.Alpine.__fromLivewire === void 0) {
        warnAboutMultipleInstancesOf("Alpine");
      }
      Livewire2.start();
    });
  }
})();
/* NProgress, (c) 2013, 2014 Rico Sta. Cruz - http://ricostacruz.com/nprogress
 * @license MIT */
/*! Bundled license information:

sortablejs/modular/sortable.esm.js:
  (**!
   * Sortable 1.15.2
   * @author	RubaXa   <trash@rubaxa.org>
   * @author	owenm    <owen23355@gmail.com>
   * @license MIT
   *)
*/
/*! Bundled license information:

tabbable/dist/index.esm.js:
  (*!
  * tabbable 5.3.3
  * @license MIT, https://github.com/focus-trap/tabbable/blob/master/LICENSE
  *)

focus-trap/dist/focus-trap.esm.js:
  (*!
  * focus-trap 6.9.4
  * @license MIT, https://github.com/focus-trap/focus-trap/blob/master/LICENSE
  *)
*/
