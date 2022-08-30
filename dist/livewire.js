(() => {
  // js/utils.js
  function dataGet(object2, key) {
    return key.split(".").reduce((carry, i) => {
      if (carry === void 0)
        return void 0;
      return carry[i];
    }, object2);
  }
  function dataSet(object2, key, value2) {
    let segments = key.split(".");
    if (segments.length === 1) {
      return object2[key] = value2;
    }
    let firstSegment = segments.shift();
    let restOfSegments = segments.join(".");
    if (object2[firstSegment] === void 0) {
      object2[firstSegment] = {};
    }
    dataSet(object2[firstSegment], restOfSegments, value2);
  }
  var Bag = class {
    constructor() {
      this.arrays = {};
    }
    add(key, value2) {
      if (!this.arrays[key])
        this.arrays[key] = [];
      this.arrays[key].push(value2);
    }
    get(key) {
      return this.arrays[key] || [];
    }
    each(key, callback) {
      return this.get(key).forEach(callback);
    }
  };

  // js/events.js
  var listeners = new Bag();
  function on(name, callback) {
    listeners.add(name, callback);
  }

  // js/state.js
  var state = {
    components: {}
  };
  function first() {
    return Object.values(state.components)[0].$wire;
  }

  // ../synthetic/node_modules/@vue/shared/dist/shared.esm-bundler.js
  function makeMap(str, expectsLowerCase) {
    const map = /* @__PURE__ */ Object.create(null);
    const list = str.split(",");
    for (let i = 0; i < list.length; i++) {
      map[list[i]] = true;
    }
    return expectsLowerCase ? (val) => !!map[val.toLowerCase()] : (val) => !!map[val];
  }
  var specialBooleanAttrs = `itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly`;
  var isBooleanAttr = /* @__PURE__ */ makeMap(specialBooleanAttrs + `,async,autofocus,autoplay,controls,default,defer,disabled,hidden,loop,open,required,reversed,scoped,seamless,checked,muted,multiple,selected`);
  var EMPTY_OBJ = false ? Object.freeze({}) : {};
  var EMPTY_ARR = false ? Object.freeze([]) : [];
  var extend = Object.assign;
  var hasOwnProperty = Object.prototype.hasOwnProperty;
  var hasOwn = (val, key) => hasOwnProperty.call(val, key);
  var isArray = Array.isArray;
  var isMap = (val) => toTypeString(val) === "[object Map]";
  var isString = (val) => typeof val === "string";
  var isSymbol = (val) => typeof val === "symbol";
  var isObject = (val) => val !== null && typeof val === "object";
  var objectToString = Object.prototype.toString;
  var toTypeString = (value2) => objectToString.call(value2);
  var toRawType = (value2) => {
    return toTypeString(value2).slice(8, -1);
  };
  var isIntegerKey = (key) => isString(key) && key !== "NaN" && key[0] !== "-" && "" + parseInt(key, 10) === key;
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
  var hasChanged = (value2, oldValue) => !Object.is(value2, oldValue);

  // ../synthetic/node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js
  var activeEffectScope;
  function recordEffectScope(effect3, scope = activeEffectScope) {
    if (scope && scope.active) {
      scope.effects.push(effect3);
    }
  }
  var createDep = (effects) => {
    const dep = new Set(effects);
    dep.w = 0;
    dep.n = 0;
    return dep;
  };
  var wasTracked = (dep) => (dep.w & trackOpBit) > 0;
  var newTracked = (dep) => (dep.n & trackOpBit) > 0;
  var initDepMarkers = ({ deps }) => {
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].w |= trackOpBit;
      }
    }
  };
  var finalizeDepMarkers = (effect3) => {
    const { deps } = effect3;
    if (deps.length) {
      let ptr = 0;
      for (let i = 0; i < deps.length; i++) {
        const dep = deps[i];
        if (wasTracked(dep) && !newTracked(dep)) {
          dep.delete(effect3);
        } else {
          deps[ptr++] = dep;
        }
        dep.w &= ~trackOpBit;
        dep.n &= ~trackOpBit;
      }
      deps.length = ptr;
    }
  };
  var targetMap = /* @__PURE__ */ new WeakMap();
  var effectTrackDepth = 0;
  var trackOpBit = 1;
  var maxMarkerBits = 30;
  var activeEffect;
  var ITERATE_KEY = Symbol(false ? "iterate" : "");
  var MAP_KEY_ITERATE_KEY = Symbol(false ? "Map key iterate" : "");
  var ReactiveEffect = class {
    constructor(fn, scheduler = null, scope) {
      this.fn = fn;
      this.scheduler = scheduler;
      this.active = true;
      this.deps = [];
      this.parent = void 0;
      recordEffectScope(this, scope);
    }
    run() {
      if (!this.active) {
        return this.fn();
      }
      let parent = activeEffect;
      let lastShouldTrack = shouldTrack;
      while (parent) {
        if (parent === this) {
          return;
        }
        parent = parent.parent;
      }
      try {
        this.parent = activeEffect;
        activeEffect = this;
        shouldTrack = true;
        trackOpBit = 1 << ++effectTrackDepth;
        if (effectTrackDepth <= maxMarkerBits) {
          initDepMarkers(this);
        } else {
          cleanupEffect(this);
        }
        return this.fn();
      } finally {
        if (effectTrackDepth <= maxMarkerBits) {
          finalizeDepMarkers(this);
        }
        trackOpBit = 1 << --effectTrackDepth;
        activeEffect = this.parent;
        shouldTrack = lastShouldTrack;
        this.parent = void 0;
        if (this.deferStop) {
          this.stop();
        }
      }
    }
    stop() {
      if (activeEffect === this) {
        this.deferStop = true;
      } else if (this.active) {
        cleanupEffect(this);
        if (this.onStop) {
          this.onStop();
        }
        this.active = false;
      }
    }
  };
  function cleanupEffect(effect3) {
    const { deps } = effect3;
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].delete(effect3);
      }
      deps.length = 0;
    }
  }
  function effect(fn, options) {
    if (fn.effect) {
      fn = fn.effect.fn;
    }
    const _effect = new ReactiveEffect(fn);
    if (options) {
      extend(_effect, options);
      if (options.scope)
        recordEffectScope(_effect, options.scope);
    }
    if (!options || !options.lazy) {
      _effect.run();
    }
    const runner = _effect.run.bind(_effect);
    runner.effect = _effect;
    return runner;
  }
  function stop(runner) {
    runner.effect.stop();
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
  function track(target, type, key) {
    if (shouldTrack && activeEffect) {
      let depsMap = targetMap.get(target);
      if (!depsMap) {
        targetMap.set(target, depsMap = /* @__PURE__ */ new Map());
      }
      let dep = depsMap.get(key);
      if (!dep) {
        depsMap.set(key, dep = createDep());
      }
      const eventInfo = false ? { effect: activeEffect, target, type, key } : void 0;
      trackEffects(dep, eventInfo);
    }
  }
  function trackEffects(dep, debuggerEventExtraInfo) {
    let shouldTrack2 = false;
    if (effectTrackDepth <= maxMarkerBits) {
      if (!newTracked(dep)) {
        dep.n |= trackOpBit;
        shouldTrack2 = !wasTracked(dep);
      }
    } else {
      shouldTrack2 = !dep.has(activeEffect);
    }
    if (shouldTrack2) {
      dep.add(activeEffect);
      activeEffect.deps.push(dep);
      if (false) {
        activeEffect.onTrack(Object.assign({ effect: activeEffect }, debuggerEventExtraInfo));
      }
    }
  }
  function trigger(target, type, key, newValue, oldValue, oldTarget) {
    const depsMap = targetMap.get(target);
    if (!depsMap) {
      return;
    }
    let deps = [];
    if (type === "clear") {
      deps = [...depsMap.values()];
    } else if (key === "length" && isArray(target)) {
      depsMap.forEach((dep, key2) => {
        if (key2 === "length" || key2 >= newValue) {
          deps.push(dep);
        }
      });
    } else {
      if (key !== void 0) {
        deps.push(depsMap.get(key));
      }
      switch (type) {
        case "add":
          if (!isArray(target)) {
            deps.push(depsMap.get(ITERATE_KEY));
            if (isMap(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          } else if (isIntegerKey(key)) {
            deps.push(depsMap.get("length"));
          }
          break;
        case "delete":
          if (!isArray(target)) {
            deps.push(depsMap.get(ITERATE_KEY));
            if (isMap(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          }
          break;
        case "set":
          if (isMap(target)) {
            deps.push(depsMap.get(ITERATE_KEY));
          }
          break;
      }
    }
    const eventInfo = false ? { target, type, key, newValue, oldValue, oldTarget } : void 0;
    if (deps.length === 1) {
      if (deps[0]) {
        if (false) {
          triggerEffects(deps[0], eventInfo);
        } else {
          triggerEffects(deps[0]);
        }
      }
    } else {
      const effects = [];
      for (const dep of deps) {
        if (dep) {
          effects.push(...dep);
        }
      }
      if (false) {
        triggerEffects(createDep(effects), eventInfo);
      } else {
        triggerEffects(createDep(effects));
      }
    }
  }
  function triggerEffects(dep, debuggerEventExtraInfo) {
    const effects = isArray(dep) ? dep : [...dep];
    for (const effect3 of effects) {
      if (effect3.computed) {
        triggerEffect(effect3, debuggerEventExtraInfo);
      }
    }
    for (const effect3 of effects) {
      if (!effect3.computed) {
        triggerEffect(effect3, debuggerEventExtraInfo);
      }
    }
  }
  function triggerEffect(effect3, debuggerEventExtraInfo) {
    if (effect3 !== activeEffect || effect3.allowRecurse) {
      if (false) {
        effect3.onTrigger(extend({ effect: effect3 }, debuggerEventExtraInfo));
      }
      if (effect3.scheduler) {
        effect3.scheduler();
      } else {
        effect3.run();
      }
    }
  }
  var isNonTrackableKeys = /* @__PURE__ */ makeMap(`__proto__,__v_isRef,__isVue`);
  var builtInSymbols = new Set(/* @__PURE__ */ Object.getOwnPropertyNames(Symbol).filter((key) => key !== "arguments" && key !== "caller").map((key) => Symbol[key]).filter(isSymbol));
  var get = /* @__PURE__ */ createGetter();
  var readonlyGet = /* @__PURE__ */ createGetter(true);
  var arrayInstrumentations = /* @__PURE__ */ createArrayInstrumentations();
  function createArrayInstrumentations() {
    const instrumentations = {};
    ["includes", "indexOf", "lastIndexOf"].forEach((key) => {
      instrumentations[key] = function(...args) {
        const arr = toRaw(this);
        for (let i = 0, l = this.length; i < l; i++) {
          track(arr, "get", i + "");
        }
        const res = arr[key](...args);
        if (res === -1 || res === false) {
          return arr[key](...args.map(toRaw));
        } else {
          return res;
        }
      };
    });
    ["push", "pop", "shift", "unshift", "splice"].forEach((key) => {
      instrumentations[key] = function(...args) {
        pauseTracking();
        const res = toRaw(this)[key].apply(this, args);
        resetTracking();
        return res;
      };
    });
    return instrumentations;
  }
  function createGetter(isReadonly2 = false, shallow = false) {
    return function get2(target, key, receiver) {
      if (key === "__v_isReactive") {
        return !isReadonly2;
      } else if (key === "__v_isReadonly") {
        return isReadonly2;
      } else if (key === "__v_isShallow") {
        return shallow;
      } else if (key === "__v_raw" && receiver === (isReadonly2 ? shallow ? shallowReadonlyMap : readonlyMap : shallow ? shallowReactiveMap : reactiveMap).get(target)) {
        return target;
      }
      const targetIsArray = isArray(target);
      if (!isReadonly2 && targetIsArray && hasOwn(arrayInstrumentations, key)) {
        return Reflect.get(arrayInstrumentations, key, receiver);
      }
      const res = Reflect.get(target, key, receiver);
      if (isSymbol(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
        return res;
      }
      if (!isReadonly2) {
        track(target, "get", key);
      }
      if (shallow) {
        return res;
      }
      if (isRef(res)) {
        return targetIsArray && isIntegerKey(key) ? res : res.value;
      }
      if (isObject(res)) {
        return isReadonly2 ? readonly(res) : reactive(res);
      }
      return res;
    };
  }
  var set = /* @__PURE__ */ createSetter();
  function createSetter(shallow = false) {
    return function set2(target, key, value2, receiver) {
      let oldValue = target[key];
      if (isReadonly(oldValue) && isRef(oldValue) && !isRef(value2)) {
        return false;
      }
      if (!shallow && !isReadonly(value2)) {
        if (!isShallow(value2)) {
          value2 = toRaw(value2);
          oldValue = toRaw(oldValue);
        }
        if (!isArray(target) && isRef(oldValue) && !isRef(value2)) {
          oldValue.value = value2;
          return true;
        }
      }
      const hadKey = isArray(target) && isIntegerKey(key) ? Number(key) < target.length : hasOwn(target, key);
      const result = Reflect.set(target, key, value2, receiver);
      if (target === toRaw(receiver)) {
        if (!hadKey) {
          trigger(target, "add", key, value2);
        } else if (hasChanged(value2, oldValue)) {
          trigger(target, "set", key, value2, oldValue);
        }
      }
      return result;
    };
  }
  function deleteProperty(target, key) {
    const hadKey = hasOwn(target, key);
    const oldValue = target[key];
    const result = Reflect.deleteProperty(target, key);
    if (result && hadKey) {
      trigger(target, "delete", key, void 0, oldValue);
    }
    return result;
  }
  function has(target, key) {
    const result = Reflect.has(target, key);
    if (!isSymbol(key) || !builtInSymbols.has(key)) {
      track(target, "has", key);
    }
    return result;
  }
  function ownKeys(target) {
    track(target, "iterate", isArray(target) ? "length" : ITERATE_KEY);
    return Reflect.ownKeys(target);
  }
  var mutableHandlers = {
    get,
    set,
    deleteProperty,
    has,
    ownKeys
  };
  var readonlyHandlers = {
    get: readonlyGet,
    set(target, key) {
      if (false) {
        warn(`Set operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    },
    deleteProperty(target, key) {
      if (false) {
        warn(`Delete operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    }
  };
  var toShallow = (value2) => value2;
  var getProto = (v) => Reflect.getPrototypeOf(v);
  function get$1(target, key, isReadonly2 = false, isShallow2 = false) {
    target = target["__v_raw"];
    const rawTarget = toRaw(target);
    const rawKey = toRaw(key);
    if (!isReadonly2) {
      if (key !== rawKey) {
        track(rawTarget, "get", key);
      }
      track(rawTarget, "get", rawKey);
    }
    const { has: has2 } = getProto(rawTarget);
    const wrap = isShallow2 ? toShallow : isReadonly2 ? toReadonly : toReactive;
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
    const rawTarget = toRaw(target);
    const rawKey = toRaw(key);
    if (!isReadonly2) {
      if (key !== rawKey) {
        track(rawTarget, "has", key);
      }
      track(rawTarget, "has", rawKey);
    }
    return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
  }
  function size(target, isReadonly2 = false) {
    target = target["__v_raw"];
    !isReadonly2 && track(toRaw(target), "iterate", ITERATE_KEY);
    return Reflect.get(target, "size", target);
  }
  function add(value2) {
    value2 = toRaw(value2);
    const target = toRaw(this);
    const proto = getProto(target);
    const hadKey = proto.has.call(target, value2);
    if (!hadKey) {
      target.add(value2);
      trigger(target, "add", value2, value2);
    }
    return this;
  }
  function set$1(key, value2) {
    value2 = toRaw(value2);
    const target = toRaw(this);
    const { has: has2, get: get2 } = getProto(target);
    let hadKey = has2.call(target, key);
    if (!hadKey) {
      key = toRaw(key);
      hadKey = has2.call(target, key);
    } else if (false) {
      checkIdentityKeys(target, has2, key);
    }
    const oldValue = get2.call(target, key);
    target.set(key, value2);
    if (!hadKey) {
      trigger(target, "add", key, value2);
    } else if (hasChanged(value2, oldValue)) {
      trigger(target, "set", key, value2, oldValue);
    }
    return this;
  }
  function deleteEntry(key) {
    const target = toRaw(this);
    const { has: has2, get: get2 } = getProto(target);
    let hadKey = has2.call(target, key);
    if (!hadKey) {
      key = toRaw(key);
      hadKey = has2.call(target, key);
    } else if (false) {
      checkIdentityKeys(target, has2, key);
    }
    const oldValue = get2 ? get2.call(target, key) : void 0;
    const result = target.delete(key);
    if (hadKey) {
      trigger(target, "delete", key, void 0, oldValue);
    }
    return result;
  }
  function clear() {
    const target = toRaw(this);
    const hadItems = target.size !== 0;
    const oldTarget = false ? isMap(target) ? new Map(target) : new Set(target) : void 0;
    const result = target.clear();
    if (hadItems) {
      trigger(target, "clear", void 0, void 0, oldTarget);
    }
    return result;
  }
  function createForEach(isReadonly2, isShallow2) {
    return function forEach(callback, thisArg) {
      const observed = this;
      const target = observed["__v_raw"];
      const rawTarget = toRaw(target);
      const wrap = isShallow2 ? toShallow : isReadonly2 ? toReadonly : toReactive;
      !isReadonly2 && track(rawTarget, "iterate", ITERATE_KEY);
      return target.forEach((value2, key) => {
        return callback.call(thisArg, wrap(value2), wrap(key), observed);
      });
    };
  }
  function createIterableMethod(method, isReadonly2, isShallow2) {
    return function(...args) {
      const target = this["__v_raw"];
      const rawTarget = toRaw(target);
      const targetIsMap = isMap(rawTarget);
      const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
      const isKeyOnly = method === "keys" && targetIsMap;
      const innerIterator = target[method](...args);
      const wrap = isShallow2 ? toShallow : isReadonly2 ? toReadonly : toReactive;
      !isReadonly2 && track(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY);
      return {
        next() {
          const { value: value2, done } = innerIterator.next();
          return done ? { value: value2, done } : {
            value: isPair ? [wrap(value2[0]), wrap(value2[1])] : wrap(value2),
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
      if (false) {
        const key = args[0] ? `on key "${args[0]}" ` : ``;
        console.warn(`${capitalize(type)} operation ${key}failed: target is readonly.`, toRaw(this));
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
      return Reflect.get(hasOwn(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
    };
  }
  var mutableCollectionHandlers = {
    get: /* @__PURE__ */ createInstrumentationGetter(false, false)
  };
  var readonlyCollectionHandlers = {
    get: /* @__PURE__ */ createInstrumentationGetter(true, false)
  };
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
  function getTargetType(value2) {
    return value2["__v_skip"] || !Object.isExtensible(value2) ? 0 : targetTypeMap(toRawType(value2));
  }
  function reactive(target) {
    if (isReadonly(target)) {
      return target;
    }
    return createReactiveObject(target, false, mutableHandlers, mutableCollectionHandlers, reactiveMap);
  }
  function readonly(target) {
    return createReactiveObject(target, true, readonlyHandlers, readonlyCollectionHandlers, readonlyMap);
  }
  function createReactiveObject(target, isReadonly2, baseHandlers, collectionHandlers, proxyMap) {
    if (!isObject(target)) {
      if (false) {
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
  function isReadonly(value2) {
    return !!(value2 && value2["__v_isReadonly"]);
  }
  function isShallow(value2) {
    return !!(value2 && value2["__v_isShallow"]);
  }
  function toRaw(observed) {
    const raw2 = observed && observed["__v_raw"];
    return raw2 ? toRaw(raw2) : observed;
  }
  var toReactive = (value2) => isObject(value2) ? reactive(value2) : value2;
  var toReadonly = (value2) => isObject(value2) ? readonly(value2) : value2;
  function isRef(r) {
    return !!(r && r.__v_isRef === true);
  }
  var _a;
  _a = "__v_isReadonly";

  // ../synthetic/js/utils.js
  function isObjecty(subject) {
    return typeof subject === "object" && subject !== null;
  }
  function isObject2(subject) {
    return isObjecty(subject) && !isArray2(subject);
  }
  function isArray2(subject) {
    return Array.isArray(subject);
  }
  function isFunction2(subject) {
    return typeof subject === "function";
  }
  function isPrimitive(subject) {
    return typeof subject !== "object" || subject === null;
  }
  function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj));
  }
  function deeplyEqual(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
  }
  function each(subject, callback) {
    Object.entries(subject).forEach(([key, value2]) => callback(key, value2));
  }
  function dataGet2(object2, key) {
    if (key === "")
      return object2;
    return key.split(".").reduce((carry, i) => {
      if (carry === void 0)
        return void 0;
      return carry[i];
    }, object2);
  }
  function diff(left, right, diffs = {}, path = "") {
    if (left === right)
      return diffs;
    if (typeof left !== typeof right || isObject2(left) && isArray2(right) || isArray2(left) && isObject2(right)) {
      diffs[path] = right;
      return diffs;
    }
    if (isPrimitive(left) || isPrimitive(right)) {
      diffs[path] = right;
      return diffs;
    }
    let leftKeys = Object.keys(left);
    Object.entries(right).forEach(([key, value2]) => {
      diffs = { ...diffs, ...diff(left[key], right[key], diffs, path === "" ? key : `${path}.${key}`) };
      leftKeys = leftKeys.filter((i) => i !== key);
    });
    leftKeys.forEach((key) => {
      diffs[`${path}.${key}`] = "__rm__";
    });
    return diffs;
  }

  // ../synthetic/js/modal.js
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

  // ../synthetic/js/events.js
  var listeners2 = [];
  function on2(name, callback) {
    if (!listeners2[name])
      listeners2[name] = [];
    listeners2[name].push(callback);
  }
  function trigger2(name, ...params) {
    let callbacks = listeners2[name] || [];
    let finishers = [];
    for (let i = 0; i < callbacks.length; i++) {
      let finisher = callbacks[i](...params);
      if (isFunction2(finisher))
        finishers.push(finisher);
    }
    return (result) => {
      let latest = result;
      for (let i = 0; i < finishers.length; i++) {
        latest = finishers[i](latest);
      }
      return latest;
    };
  }

  // ../synthetic/js/features/methods.js
  function methods_default() {
    on2("decorate", (target, path, addProp, decorator, symbol) => {
      let effects = target.effects[path];
      if (!effects)
        return;
      let methods = effects["methods"] || [];
      methods.forEach((method) => {
        addProp(method, async (...params) => {
          if (params.length === 1 && params[0] instanceof Event) {
            params = [];
          }
          return await callMethod(symbol, path, method, params);
        });
      });
    });
    on2("decorate", (target, path, addProp) => {
      let effects = target.effects[path];
      if (!effects)
        return;
      let methods = effects["js"] || [];
      each(methods, (name, expression) => {
        let func = new Function([], "return " + expression);
        let boundFunc = func.bind(dataGet2(target.reactive, path));
        let run = boundFunc();
        addProp(name, run);
      });
    });
  }

  // ../synthetic/js/features/prefetch.js
  function prefetch_default() {
  }

  // ../synthetic/js/features/redirect.js
  function redirect_default() {
    on2("effects", (target, effects) => {
      if (!effects["redirect"])
        return;
      let url = effects["redirect"];
      window.location.href = url;
    });
  }

  // ../synthetic/js/features/loading.js
  function loading_default() {
    on2("new", (target) => {
      target.__loading = reactive2({ state: false });
    });
    on2("target.request", (target, payload) => {
      target.__loading.state = true;
      return () => target.__loading.state = false;
    });
    on2("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$loading", { get() {
        return target.__loading.state;
      } });
    });
  }

  // ../synthetic/js/features/polling.js
  function polling_default() {
    on2("decorate", (target, path) => {
      return (decorator) => {
        Object.defineProperty(decorator, "$poll", { value: () => {
          syncronizedInterval(2500, () => {
            target.ephemeral.$commit();
          });
        } });
        return decorator;
      };
    });
  }
  var clocks = [];
  function syncronizedInterval(ms, callback) {
    if (!clocks[ms]) {
      let clock = {
        timer: setInterval(() => each(clock.callbacks, (key, value2) => value2()), ms),
        callbacks: []
      };
      clocks[ms] = clock;
    }
    clocks[ms].callbacks.push(callback);
  }

  // ../synthetic/js/features/errors.js
  function errors_default() {
    on2("new", (target, path) => {
      target.__errors = reactive2({ state: [] });
    });
    on2("decorate", (target, path) => {
      return (decorator) => {
        Object.defineProperty(decorator, "$errors", { get() {
          let errors = {};
          Object.entries(target.__errors.state).forEach(([key, value2]) => {
            errors[key] = value2[0];
          });
          return errors;
        } });
        return decorator;
      };
    });
    on2("effects", (target, effects, path) => {
      let errors = effects["errors"] || [];
      target.__errors.state = errors;
    });
  }

  // ../synthetic/js/features/dirty.js
  function dirty_default() {
    on2("new", (target) => {
      target.__dirty = reactive2({ state: 0 });
    });
    on2("target.request", (target, payload) => {
      return () => target.__dirty.state = +new Date();
    });
    on2("decorate", (target, path) => {
      return (decorator) => {
        Object.defineProperty(decorator, "$dirty", { get() {
          let throwaway = target.__dirty.state;
          let thing1 = dataGet2(target.canonical, path);
          let thing2 = dataGet2(target.reactive, path);
          return !deeplyEqual(thing1, thing2);
        } });
        return decorator;
      };
    });
  }

  // ../synthetic/js/features/index.js
  methods_default();
  prefetch_default();
  redirect_default();
  loading_default();
  polling_default();
  errors_default();
  dirty_default();

  // ../synthetic/js/index.js
  var reactive2 = reactive;
  var release = stop;
  var effect2 = effect;
  var raw = toRaw;
  document.addEventListener("alpine:init", () => {
    reactive2 = Alpine.reactive;
    effect2 = Alpine.effect;
    release = Alpine.release;
    raw = Alpine.raw;
  });
  var store = /* @__PURE__ */ new Map();
  window.synthetic = synthetic;
  window.syntheticOn = on2;
  function synthetic(provided) {
    if (typeof provided === "string")
      return newUp(provided);
    let target = {
      methods: provided.effects["methods"] || [],
      effects: raw(provided.effects),
      snapshot: raw(provided.snapshot)
    };
    let symbol = Symbol();
    store.set(symbol, target);
    let canonical = extractData(deepClone(target.snapshot.data), symbol);
    let ephemeral = extractDataAndDecorate(deepClone(target.snapshot.data), symbol);
    target.canonical = canonical;
    target.ephemeral = ephemeral;
    target.reactive = reactive2(ephemeral);
    trigger2("new", target);
    processEffects(target);
    return target.reactive;
  }
  async function newUp(name) {
    return synthetic(await requestNew(name));
  }
  function extractDataAndDecorate(payload, symbol) {
    return extractData(payload, symbol, (object2, meta, symbol2, path) => {
      let target = store.get(symbol2);
      let decorator = {};
      let addProp = (key, value2, options = {}) => {
        let base = { enumerable: false, configurable: true, ...options };
        if (isObject2(value2) && deeplyEqual(Object.keys(value2), ["get"]) || deeplyEqual(Object.keys(value2), ["get", "set"])) {
          Object.defineProperty(object2, key, {
            get: value2.get,
            set: value2.set,
            ...base
          });
        } else {
          Object.defineProperty(object2, key, {
            value: value2,
            ...base
          });
        }
      };
      let finish = trigger2("decorate", target, path, addProp, decorator, symbol2);
      addProp("__target", { get() {
        return target;
      } });
      addProp("$watch", (path2, callback) => {
        let firstTime = true;
        let old = void 0;
        effect2(() => {
          let value2 = dataGet2(target.reactive, path2);
          if (firstTime) {
            firstTime = false;
            return;
          }
          pauseTracking();
          callback(value2, old);
          old = value2;
          enableTracking();
        });
      });
      addProp("$watchEffect", (callback) => effect2(callback));
      addProp("$refresh", async () => await requestCommit(symbol2));
      addProp("$commit", async (callback) => {
        return await requestCommit(symbol2);
      });
      each(Object.getOwnPropertyDescriptors(decorator), (key, value2) => {
        Object.defineProperty(object2, key, value2);
      });
      return object2;
    });
  }
  function extractData(payload, symbol, decorate2 = (i) => i, path = "") {
    let value2 = isSynthetic(payload) ? payload[0] : payload;
    let meta = isSynthetic(payload) ? payload[1] : void 0;
    if (isObjecty(value2)) {
      Object.entries(value2).forEach(([key, iValue]) => {
        value2[key] = extractData(iValue, symbol, decorate2, path === "" ? key : `${path}.${key}`);
      });
    }
    return meta !== void 0 && isObjecty(value2) ? decorate2(value2, meta, symbol, path) : value2;
  }
  function isSynthetic(subject) {
    return Array.isArray(subject) && subject.length === 2 && typeof subject[1] === "object" && Object.keys(subject[1]).includes("s");
  }
  async function callMethod(symbol, path, method, params) {
    let result = await requestMethodCall(symbol, path, method, params);
    return result;
  }
  var requestTargetQueue = /* @__PURE__ */ new Map();
  function requestMethodCall(symbol, path, method, params) {
    requestCommit(symbol);
    return new Promise((resolve, reject) => {
      let queue = requestTargetQueue.get(symbol);
      queue.calls.push({
        path,
        method,
        params,
        handleReturn(value2) {
          resolve(value2);
        }
      });
    });
  }
  function requestCommit(symbol) {
    if (!requestTargetQueue.has(symbol)) {
      requestTargetQueue.set(symbol, { calls: [], receivers: [] });
    }
    triggerSend();
    return new Promise((resolve, reject) => {
      let queue = requestTargetQueue.get(symbol);
      queue.handleResponse = () => resolve();
    });
  }
  var requestBufferTimeout;
  function triggerSend() {
    if (requestBufferTimeout)
      return;
    requestBufferTimeout = setTimeout(() => {
      sendMethodCall();
      requestBufferTimeout = void 0;
    }, 5);
  }
  async function sendMethodCall() {
    requestTargetQueue.forEach((request2, symbol) => {
      let target = store.get(symbol);
      trigger2("request.before", target);
    });
    let payload = [];
    let receivers = [];
    requestTargetQueue.forEach((request2, symbol) => {
      let target = store.get(symbol);
      let propertiesDiff = diff(target.canonical, target.ephemeral);
      let targetPaylaod = {
        snapshot: target.snapshot,
        diff: propertiesDiff,
        calls: request2.calls.map((i) => ({
          path: i.path,
          method: i.method,
          params: i.params
        }))
      };
      payload.push(targetPaylaod);
      let finish2 = trigger2("target.request", target, targetPaylaod);
      receivers.push((snapshot, effects) => {
        mergeNewSnapshot(symbol, snapshot, effects);
        processEffects(target);
        for (let i = 0; i < request2.calls.length; i++) {
          let { path, handleReturn } = request2.calls[i];
          let forReturn = void 0;
          if (effects)
            Object.entries(effects).forEach(([iPath, iEffects]) => {
              if (path === iPath) {
                if (iEffects["return"] !== void 0)
                  forReturn = iEffects["return"];
              }
            });
          handleReturn(forReturn);
        }
        finish2();
        request2.handleResponse();
      });
    });
    requestTargetQueue.clear();
    let finish = trigger2("request", payload);
    let request = await fetch("/synthetic/update", {
      method: "POST",
      body: JSON.stringify({
        _token: getCsrfToken(),
        targets: payload
      }),
      headers: { "Content-type": "application/json" }
    });
    if (request.ok) {
      let response = await request.json();
      for (let i = 0; i < response.length; i++) {
        let { snapshot, effects } = response[i];
        receivers[i](snapshot, effects);
      }
      trigger2("response.success");
    } else {
      let html = await request.text();
      showHtmlModal(html);
      trigger2("response.failure");
    }
    finish();
  }
  async function requestNew(name) {
    let request = await fetch("/synthetic/new", {
      method: "POST",
      body: JSON.stringify({
        _token: getCsrfToken(),
        name
      }),
      headers: { "Content-type": "application/json" }
    });
    if (request.ok) {
      return await request.json();
    } else {
      let html = await request.text();
      showHtmlModal(html);
    }
  }
  function getCsrfToken() {
    if (document.querySelector('meta[name="csrf"]')) {
      return document.querySelector('meta[name="csrf"]').content;
    }
    return window.__csrf;
  }
  function mergeNewSnapshot(symbol, snapshot, effects) {
    let target = store.get(symbol);
    target.snapshot = snapshot;
    target.effects = effects;
    target.canonical = extractData(deepClone(snapshot.data), symbol);
    let newData = extractData(deepClone(snapshot.data), symbol);
    Object.entries(target.ephemeral).forEach(([key, value2]) => {
      if (!deeplyEqual(target.ephemeral[key], newData[key])) {
        target.reactive[key] = newData[key];
      }
    });
  }
  function processEffects(target) {
    let effects = target.effects;
    each(effects, (key, value2) => trigger2("effects", target, value2, key));
  }

  // js/features/morphDom.js
  function morphDom_default() {
    on2("request.before", (target) => {
      let childIds = Object.values(target.snapshot.data[1].children).map((i) => i[1]);
    });
    on2("effects", (target, effects, path) => {
      let component = state.components[target.__livewireId];
      let html = effects.html;
      if (!html)
        return;
      queueMicrotask(() => {
        doMorph(component, component.el, html);
      });
    });
  }
  function doMorph(component, el, html) {
    let wrapper = document.createElement("div");
    wrapper.innerHTML = html;
    let parentComponent;
    try {
      parentComponent = closestComponent(el.parentElement);
    } catch (e) {
    }
    parentComponent && (wrapper.__livewire = parentComponent);
    let to = wrapper.firstElementChild;
    to.__livewire = component;
    Alpine.morph(el, to, {
      updating: (el2, toEl, childrenOnly, skip) => {
        if (isntElement(el2))
          return;
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
      },
      removing: (el2, skip) => {
        if (isntElement(el2))
          return;
      },
      removed: (el2) => {
        if (isntElement(el2))
          return;
      },
      added: (el2) => {
        if (isntElement(el2))
          return;
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
      lookahead: true
    });
  }
  function isntElement(el) {
    return typeof el.hasAttribute !== "function";
  }
  function isComponentRootEl(el) {
    return el.hasAttribute("wire:id");
  }

  // js/directives.js
  function directives(el) {
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
    has(type) {
      return this.directives.map((directive) => directive.type).includes(type);
    }
    missing(type) {
      return !this.has(type);
    }
    get(type) {
      return this.directives.find((directive) => directive.type === type);
    }
    extractTypeModifiersAndValue() {
      return Array.from(this.el.getAttributeNames().filter((name) => name.match(new RegExp("wire:"))).map((name) => {
        const [type, ...modifiers] = name.replace(new RegExp("wire:"), "").split(".");
        return new Directive(type, modifiers, name, this.el);
      }));
    }
  };
  var Directive = class {
    constructor(type, modifiers, rawName, el) {
      this.type = type;
      this.modifiers = modifiers;
      this.rawName = rawName;
      this.el = el;
      this.eventContext;
    }
    get value() {
      return this.el.getAttribute(this.rawName);
    }
    get method() {
      const { method } = this.parseOutMethodAndParams(this.value);
      return method;
    }
    get params() {
      const { params } = this.parseOutMethodAndParams(this.value);
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

  // js/features/wireModel.js
  function wireModel_default() {
    on("element.init", (el, component) => {
      let allDirectives = directives(el);
      if (allDirectives.missing("model"))
        return;
      let directive = allDirectives.get("model");
      if (!directive.value) {
        console.warn("Livewire: [wire:model] is missing a value.", el);
        return;
      }
      let lazy = directive.modifiers.includes("lazy");
      let modifierTail = getModifierTail(directive.modifiers);
      Alpine.bind(el, {
        ["@change"]() {
          if (lazy) {
          }
        },
        ["x-model.unintrusive" + modifierTail]() {
          return {
            get() {
              return dataGet(closestComponent(el).$wire, directive.value);
            },
            set(value2) {
              dataSet(closestComponent(el).$wire, directive.value, value2);
            }
          };
        }
      });
    });
  }
  function getModifierTail(modifiers) {
    modifiers = modifiers.filter((i) => ![
      "lazy",
      "defer"
    ].includes(i));
    if (modifiers.length === 0)
      return "";
    return "." + modifiers.join(".");
  }

  // js/features/wireWildcard.js
  function wireWildcard_default() {
    on2("element.init", (el, component) => {
      directives(el).all().forEach((directive) => {
        if (["model", "init", "loading", "poll", "ignore", "id", "initial-data", "key", "target", "dirty"].includes(directive.type))
          return;
        let attribute = directive.rawName.replace("wire:", "x-on:");
        Alpine.bind(el, {
          [attribute](e) {
            Alpine.evaluate(el, "$wire." + directive.value, { scope: { $event: e } });
          }
        });
      });
    });
  }

  // js/features/hotReloading.js
  function hotReloading_default() {
    on2("effects", (target, effects, path) => {
      queueMicrotask(() => {
        let files = effects.hotReload;
        if (!files)
          return;
        let component = state.components[target.__livewireId];
        if (files) {
          files.forEach((file) => {
            whenFileIsModified(file, () => {
              component.$wire.$refresh();
            });
          });
        }
      });
    });
    let es = new EventSource("/livewire/hot-reload");
    es.addEventListener("message", function(event) {
      let data = JSON.parse(event.data);
      data.file && console.log(data.file, listeners3);
      if (data.file && listeners3[data.file]) {
        listeners3[data.file].forEach((cb) => cb());
      }
    });
    es.onerror = function(err) {
      console.log("EventSource failed:", err);
    };
    es.onopen = function(err) {
      console.log("opened", err);
    };
  }
  var listeners3 = [];
  function whenFileIsModified(file, callback) {
    if (!listeners3[file])
      listeners3[file] = [];
    listeners3[file].push(callback);
  }

  // js/features/wireLoading.js
  function wireLoading_default() {
    on("element.init", (el, component) => {
      let elDirectives = directives(el);
      if (elDirectives.missing("loading"))
        return;
      Alpine.bind(el, {
        "x-show"() {
          return component.$wire.$loading;
        }
      });
    });
  }

  // js/features/$wire.js
  function wire_default() {
    Alpine.magic("wire", (el) => closestComponent(el).$wire);
  }

  // js/features/props.js
  function props_default() {
    on2("request.before", (target) => {
      let meta = target.snapshot.data[1];
      let childIds = Object.values(meta.children).map((i) => i[1]);
      childIds.forEach((id) => {
        let childSynthetic = state.components[id].synthetic;
        let childMeta = childSynthetic.snapshot.data[1];
        let props = childMeta.props;
        if (props)
          childSynthetic.ephemeral.$commit();
      });
    });
  }

  // js/features/index.js
  function features_default() {
    wire_default();
    props_default();
    morphDom_default();
    wireModel_default();
    wireLoading_default();
    wireWildcard_default();
    hotReloading_default();
  }

  // js/component.js
  var Component = class {
    constructor(synthetic2, el, id) {
      this.synthetic = synthetic2;
      this.$wire = this.synthetic.reactive;
      this.el = el;
      this.id = id;
      synthetic2.__livewireId = this.id;
    }
  };

  // js/lifecycle.js
  function start() {
    features_default();
    Alpine.interceptInit(Alpine.skipDuringClone((el) => {
      initElement(el);
    }));
  }
  function initElement(el) {
    if (el.hasAttribute("wire:id")) {
      let id = el.getAttribute("wire:id");
      let raw2 = JSON.parse(el.getAttribute("wire:initial-data"));
      let component2 = new Component(synthetic(raw2).__target, el, id);
      el.__livewire = component2;
      Alpine.bind(el, {
        "x-data"() {
          return component2.synthetic.reactive;
        }
      });
      state.components[component2.id] = component2;
      trigger2("component.initialized", component2);
    }
    let component;
    try {
      component = closestComponent(el);
    } catch (e) {
    }
    component && trigger2("element.init", el, component);
  }
  function closestComponent(el) {
    let closestRoot = Alpine.findClosest(el, (i) => i.__livewire);
    if (!closestRoot) {
      throw "Could not find Livewire component in DOM tree";
    }
    return closestRoot.__livewire;
  }

  // js/index.js
  var Livewire = {
    start,
    hook: on,
    on,
    first
  };
  if (!window.Livewire)
    window.Livewire = Livewire;
  function monkeyPatchDomSetAttributeToAllowAtSymbols() {
    let original = Element.prototype.setAttribute;
    let hostDiv = document.createElement("div");
    Element.prototype.setAttribute = function newSetAttribute(name, value2) {
      if (!name.includes("@")) {
        return original.call(this, name, value2);
      }
      hostDiv.innerHTML = `<span ${name}="${value2}"></span>`;
      let attr = hostDiv.firstElementChild.getAttributeNode(name);
      hostDiv.firstElementChild.removeAttributeNode(attr);
      this.setAttributeNode(attr);
    };
  }
  monkeyPatchDomSetAttributeToAllowAtSymbols();
})();
