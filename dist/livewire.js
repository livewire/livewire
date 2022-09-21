(() => {
  // synthetic/js/utils.js
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
  function deeplyEqual(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
  }
  function each(subject, callback) {
    Object.entries(subject).forEach(([key, value2]) => callback(key, value2));
  }
  function dataGet(object2, key) {
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
    if (typeof left !== typeof right || isObject(left) && isArray(right) || isArray(left) && isObject(right)) {
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

  // js/state.js
  var state = {
    components: {}
  };
  function findComponent(id) {
    let component = state.components[id];
    if (!component)
      throw "Component not found: ".id;
    return component;
  }
  function storeComponent(id, component) {
    state.components[id] = component;
  }
  var releasePool = {};
  function releaseComponent(id) {
    let component = state.components[id];
    let effects = deepClone(component.synthetic.effects);
    delete effects[""]["html"];
    releasePool[id] = {
      effects,
      snapshot: deepClone(component.synthetic.snapshot)
    };
    delete state.components[id];
  }
  function resurrect(id) {
    if (!releasePool[id]) {
      throw "Cant find holdover resurrection component";
    }
    return releasePool[id];
  }
  function first() {
    return Object.values(state.components)[0].$wire;
  }

  // synthetic/node_modules/@vue/shared/dist/shared.esm-bundler.js
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
  var EMPTY_OBJ = true ? Object.freeze({}) : {};
  var EMPTY_ARR = true ? Object.freeze([]) : [];
  var extend = Object.assign;
  var hasOwnProperty = Object.prototype.hasOwnProperty;
  var hasOwn = (val, key) => hasOwnProperty.call(val, key);
  var isArray2 = Array.isArray;
  var isMap = (val) => toTypeString(val) === "[object Map]";
  var isString = (val) => typeof val === "string";
  var isSymbol = (val) => typeof val === "symbol";
  var isObject2 = (val) => val !== null && typeof val === "object";
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

  // synthetic/node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js
  function warn(msg, ...args) {
    console.warn(`[Vue warn] ${msg}`, ...args);
  }
  var activeEffectScope;
  function recordEffectScope(effect5, scope = activeEffectScope) {
    if (scope && scope.active) {
      scope.effects.push(effect5);
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
  var finalizeDepMarkers = (effect5) => {
    const { deps } = effect5;
    if (deps.length) {
      let ptr = 0;
      for (let i = 0; i < deps.length; i++) {
        const dep = deps[i];
        if (wasTracked(dep) && !newTracked(dep)) {
          dep.delete(effect5);
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
  var ITERATE_KEY = Symbol(true ? "iterate" : "");
  var MAP_KEY_ITERATE_KEY = Symbol(true ? "Map key iterate" : "");
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
  function cleanupEffect(effect5) {
    const { deps } = effect5;
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].delete(effect5);
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
      const eventInfo = true ? { effect: activeEffect, target, type, key } : void 0;
      trackEffects(dep, eventInfo);
    }
  }
  function trackEffects(dep, debuggerEventExtraInfo) {
    let shouldTrack3 = false;
    if (effectTrackDepth <= maxMarkerBits) {
      if (!newTracked(dep)) {
        dep.n |= trackOpBit;
        shouldTrack3 = !wasTracked(dep);
      }
    } else {
      shouldTrack3 = !dep.has(activeEffect);
    }
    if (shouldTrack3) {
      dep.add(activeEffect);
      activeEffect.deps.push(dep);
      if (activeEffect.onTrack) {
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
    } else if (key === "length" && isArray2(target)) {
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
          if (!isArray2(target)) {
            deps.push(depsMap.get(ITERATE_KEY));
            if (isMap(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY));
            }
          } else if (isIntegerKey(key)) {
            deps.push(depsMap.get("length"));
          }
          break;
        case "delete":
          if (!isArray2(target)) {
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
    const eventInfo = true ? { target, type, key, newValue, oldValue, oldTarget } : void 0;
    if (deps.length === 1) {
      if (deps[0]) {
        if (true) {
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
      if (true) {
        triggerEffects(createDep(effects), eventInfo);
      } else {
        triggerEffects(createDep(effects));
      }
    }
  }
  function triggerEffects(dep, debuggerEventExtraInfo) {
    const effects = isArray2(dep) ? dep : [...dep];
    for (const effect5 of effects) {
      if (effect5.computed) {
        triggerEffect(effect5, debuggerEventExtraInfo);
      }
    }
    for (const effect5 of effects) {
      if (!effect5.computed) {
        triggerEffect(effect5, debuggerEventExtraInfo);
      }
    }
  }
  function triggerEffect(effect5, debuggerEventExtraInfo) {
    if (effect5 !== activeEffect || effect5.allowRecurse) {
      if (effect5.onTrigger) {
        effect5.onTrigger(extend({ effect: effect5 }, debuggerEventExtraInfo));
      }
      if (effect5.scheduler) {
        effect5.scheduler();
      } else {
        effect5.run();
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
  function createGetter(isReadonly3 = false, shallow = false) {
    return function get3(target, key, receiver) {
      if (key === "__v_isReactive") {
        return !isReadonly3;
      } else if (key === "__v_isReadonly") {
        return isReadonly3;
      } else if (key === "__v_isShallow") {
        return shallow;
      } else if (key === "__v_raw" && receiver === (isReadonly3 ? shallow ? shallowReadonlyMap : readonlyMap : shallow ? shallowReactiveMap : reactiveMap).get(target)) {
        return target;
      }
      const targetIsArray = isArray2(target);
      if (!isReadonly3 && targetIsArray && hasOwn(arrayInstrumentations, key)) {
        return Reflect.get(arrayInstrumentations, key, receiver);
      }
      const res = Reflect.get(target, key, receiver);
      if (isSymbol(key) ? builtInSymbols.has(key) : isNonTrackableKeys(key)) {
        return res;
      }
      if (!isReadonly3) {
        track(target, "get", key);
      }
      if (shallow) {
        return res;
      }
      if (isRef(res)) {
        return targetIsArray && isIntegerKey(key) ? res : res.value;
      }
      if (isObject2(res)) {
        return isReadonly3 ? readonly(res) : reactive(res);
      }
      return res;
    };
  }
  var set = /* @__PURE__ */ createSetter();
  function createSetter(shallow = false) {
    return function set3(target, key, value2, receiver) {
      let oldValue = target[key];
      if (isReadonly(oldValue) && isRef(oldValue) && !isRef(value2)) {
        return false;
      }
      if (!shallow && !isReadonly(value2)) {
        if (!isShallow(value2)) {
          value2 = toRaw(value2);
          oldValue = toRaw(oldValue);
        }
        if (!isArray2(target) && isRef(oldValue) && !isRef(value2)) {
          oldValue.value = value2;
          return true;
        }
      }
      const hadKey = isArray2(target) && isIntegerKey(key) ? Number(key) < target.length : hasOwn(target, key);
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
    track(target, "iterate", isArray2(target) ? "length" : ITERATE_KEY);
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
      if (true) {
        warn(`Set operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    },
    deleteProperty(target, key) {
      if (true) {
        warn(`Delete operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    }
  };
  var toShallow = (value2) => value2;
  var getProto = (v) => Reflect.getPrototypeOf(v);
  function get$1(target, key, isReadonly3 = false, isShallow3 = false) {
    target = target["__v_raw"];
    const rawTarget = toRaw(target);
    const rawKey = toRaw(key);
    if (!isReadonly3) {
      if (key !== rawKey) {
        track(rawTarget, "get", key);
      }
      track(rawTarget, "get", rawKey);
    }
    const { has: has3 } = getProto(rawTarget);
    const wrap = isShallow3 ? toShallow : isReadonly3 ? toReadonly : toReactive;
    if (has3.call(rawTarget, key)) {
      return wrap(target.get(key));
    } else if (has3.call(rawTarget, rawKey)) {
      return wrap(target.get(rawKey));
    } else if (target !== rawTarget) {
      target.get(key);
    }
  }
  function has$1(key, isReadonly3 = false) {
    const target = this["__v_raw"];
    const rawTarget = toRaw(target);
    const rawKey = toRaw(key);
    if (!isReadonly3) {
      if (key !== rawKey) {
        track(rawTarget, "has", key);
      }
      track(rawTarget, "has", rawKey);
    }
    return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
  }
  function size(target, isReadonly3 = false) {
    target = target["__v_raw"];
    !isReadonly3 && track(toRaw(target), "iterate", ITERATE_KEY);
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
    const { has: has3, get: get3 } = getProto(target);
    let hadKey = has3.call(target, key);
    if (!hadKey) {
      key = toRaw(key);
      hadKey = has3.call(target, key);
    } else if (true) {
      checkIdentityKeys(target, has3, key);
    }
    const oldValue = get3.call(target, key);
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
    const { has: has3, get: get3 } = getProto(target);
    let hadKey = has3.call(target, key);
    if (!hadKey) {
      key = toRaw(key);
      hadKey = has3.call(target, key);
    } else if (true) {
      checkIdentityKeys(target, has3, key);
    }
    const oldValue = get3 ? get3.call(target, key) : void 0;
    const result = target.delete(key);
    if (hadKey) {
      trigger(target, "delete", key, void 0, oldValue);
    }
    return result;
  }
  function clear() {
    const target = toRaw(this);
    const hadItems = target.size !== 0;
    const oldTarget = true ? isMap(target) ? new Map(target) : new Set(target) : void 0;
    const result = target.clear();
    if (hadItems) {
      trigger(target, "clear", void 0, void 0, oldTarget);
    }
    return result;
  }
  function createForEach(isReadonly3, isShallow3) {
    return function forEach(callback, thisArg) {
      const observed = this;
      const target = observed["__v_raw"];
      const rawTarget = toRaw(target);
      const wrap = isShallow3 ? toShallow : isReadonly3 ? toReadonly : toReactive;
      !isReadonly3 && track(rawTarget, "iterate", ITERATE_KEY);
      return target.forEach((value2, key) => {
        return callback.call(thisArg, wrap(value2), wrap(key), observed);
      });
    };
  }
  function createIterableMethod(method, isReadonly3, isShallow3) {
    return function(...args) {
      const target = this["__v_raw"];
      const rawTarget = toRaw(target);
      const targetIsMap = isMap(rawTarget);
      const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
      const isKeyOnly = method === "keys" && targetIsMap;
      const innerIterator = target[method](...args);
      const wrap = isShallow3 ? toShallow : isReadonly3 ? toReadonly : toReactive;
      !isReadonly3 && track(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY : ITERATE_KEY);
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
      if (true) {
        const key = args[0] ? `on key "${args[0]}" ` : ``;
        console.warn(`${capitalize(type)} operation ${key}failed: target is readonly.`, toRaw(this));
      }
      return type === "delete" ? false : this;
    };
  }
  function createInstrumentations() {
    const mutableInstrumentations3 = {
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
    const shallowInstrumentations3 = {
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
    const readonlyInstrumentations3 = {
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
    const shallowReadonlyInstrumentations3 = {
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
      mutableInstrumentations3[method] = createIterableMethod(method, false, false);
      readonlyInstrumentations3[method] = createIterableMethod(method, true, false);
      shallowInstrumentations3[method] = createIterableMethod(method, false, true);
      shallowReadonlyInstrumentations3[method] = createIterableMethod(method, true, true);
    });
    return [
      mutableInstrumentations3,
      readonlyInstrumentations3,
      shallowInstrumentations3,
      shallowReadonlyInstrumentations3
    ];
  }
  var [mutableInstrumentations, readonlyInstrumentations, shallowInstrumentations, shallowReadonlyInstrumentations] = /* @__PURE__ */ createInstrumentations();
  function createInstrumentationGetter(isReadonly3, shallow) {
    const instrumentations = shallow ? isReadonly3 ? shallowReadonlyInstrumentations : shallowInstrumentations : isReadonly3 ? readonlyInstrumentations : mutableInstrumentations;
    return (target, key, receiver) => {
      if (key === "__v_isReactive") {
        return !isReadonly3;
      } else if (key === "__v_isReadonly") {
        return isReadonly3;
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
  function checkIdentityKeys(target, has3, key) {
    const rawKey = toRaw(key);
    if (rawKey !== key && has3.call(target, rawKey)) {
      const type = toRawType(target);
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
  function createReactiveObject(target, isReadonly3, baseHandlers, collectionHandlers, proxyMap) {
    if (!isObject2(target)) {
      if (true) {
        console.warn(`value cannot be made reactive: ${String(target)}`);
      }
      return target;
    }
    if (target["__v_raw"] && !(isReadonly3 && target["__v_isReactive"])) {
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
    const raw3 = observed && observed["__v_raw"];
    return raw3 ? toRaw(raw3) : observed;
  }
  var toReactive = (value2) => isObject2(value2) ? reactive(value2) : value2;
  var toReadonly = (value2) => isObject2(value2) ? readonly(value2) : value2;
  function isRef(r) {
    return !!(r && r.__v_isRef === true);
  }
  var _a;
  _a = "__v_isReadonly";

  // synthetic/js/modal.js
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

  // synthetic/js/events.js
  var listeners = [];
  function on(name, callback) {
    if (!listeners[name])
      listeners[name] = [];
    listeners[name].push(callback);
  }
  function trigger2(name, ...params) {
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
        latest = finishers[i](latest);
      }
      return latest;
    };
  }

  // synthetic/js/features/methods.js
  function methods_default() {
    on("decorate", (target, path, addProp, decorator, symbol) => {
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
    on("decorate", (target, path, addProp) => {
      let effects = target.effects[path];
      if (!effects)
        return;
      let methods = effects["js"] || [];
      let AsyncFunction = Object.getPrototypeOf(async function() {
      }).constructor;
      each(methods, (name, expression) => {
        let func = new AsyncFunction([], expression);
        addProp(name, () => {
          func.bind(dataGet(target.reactive, path))();
        });
      });
    });
  }

  // synthetic/js/features/prefetch.js
  function prefetch_default() {
  }

  // synthetic/js/features/redirect.js
  function redirect_default() {
    on("effects", (target, effects) => {
      if (!effects["redirect"])
        return;
      let url = effects["redirect"];
      window.location.href = url;
    });
  }

  // synthetic/js/features/loading.js
  function loading_default() {
    on("new", (target) => {
      target.__loading = reactive2({ state: false });
    });
    on("target.request", (target, payload) => {
      target.__loading.state = true;
      return () => target.__loading.state = false;
    });
    on("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$loading", { get() {
        return target.__loading.state;
      } });
    });
  }

  // synthetic/js/features/polling.js
  function polling_default() {
    on("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$poll", (callback) => {
        syncronizedInterval(2500, () => {
          callback();
          target.ephemeral.$commit();
        });
      });
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

  // synthetic/js/features/errors.js
  function errors_default() {
    on("new", (target, path) => {
      target.__errors = reactive2({ state: [] });
    });
    on("decorate", (target, path) => {
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
    on("effects", (target, effects, path) => {
      let errors = effects["errors"] || [];
      target.__errors.state = errors;
    });
  }

  // synthetic/js/features/dirty.js
  function dirty_default() {
    on("new", (target) => {
      target.__dirty = reactive2({ state: 0 });
    });
    on("target.request", (target, payload) => {
      return () => target.__dirty.state = +new Date();
    });
    on("decorate", (target, path) => {
      return (decorator) => {
        Object.defineProperty(decorator, "$dirty", { get() {
          let throwaway = target.__dirty.state;
          let thing1 = dataGet(target.canonical, path);
          let thing2 = dataGet(target.reactive, path);
          return !deeplyEqual(thing1, thing2);
        } });
        return decorator;
      };
    });
  }

  // synthetic/js/features/index.js
  methods_default();
  prefetch_default();
  redirect_default();
  loading_default();
  polling_default();
  errors_default();
  dirty_default();

  // synthetic/js/index.js
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
        if (isObject(value2) && deeplyEqual(Object.keys(value2), ["get"]) || deeplyEqual(Object.keys(value2), ["get", "set"])) {
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
          let value2 = dataGet(target.reactive, path2);
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

  // js/morph.js
  function morph(component, el, html) {
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
      adding: (el2) => {
        trigger2("morph.adding", el2);
      },
      added: (el2) => {
        if (isntElement(el2))
          return;
        trigger2("morph.added", el2);
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

  // js/features/morphDom.js
  function morphDom_default() {
    on("effects", (target, effects, path) => {
      let html = effects.html;
      if (!html)
        return;
      let component = findComponent(target.__livewireId);
      queueMicrotask(() => {
        morph(component, component.el, html);
      });
    });
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

  // js/utils.js
  function debounce(func, wait, immediate) {
    var timeout;
    return function() {
      var context = this, args = arguments;
      var later = function() {
        timeout = null;
        if (!immediate)
          func.apply(context, args);
      };
      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow)
        func.apply(context, args);
    };
  }
  function dataGet2(object2, key) {
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
  var listeners2 = new Bag();
  function on2(name, callback) {
    listeners2.add(name, callback);
  }

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
      let live = directive.modifiers.includes("live");
      let update = debounce((component2) => {
        if (!live)
          return;
        component2.$wire.$commit();
      }, 250);
      Alpine.bind(el, {
        ["@change"]() {
          if (lazy) {
          }
        },
        ["x-model" + modifierTail]() {
          return {
            get() {
              return dataGet2(closestComponent(el).$wire, directive.value);
            },
            set(value2) {
              let component2 = closestComponent(el);
              dataSet(component2.$wire, directive.value, value2);
              update(component2);
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
    on("element.init", (el, component) => {
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

  // ../synthetic/node_modules/@vue/shared/dist/shared.esm-bundler.js
  function makeMap2(str, expectsLowerCase) {
    const map = /* @__PURE__ */ Object.create(null);
    const list = str.split(",");
    for (let i = 0; i < list.length; i++) {
      map[list[i]] = true;
    }
    return expectsLowerCase ? (val) => !!map[val.toLowerCase()] : (val) => !!map[val];
  }
  var specialBooleanAttrs2 = `itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly`;
  var isBooleanAttr2 = /* @__PURE__ */ makeMap2(specialBooleanAttrs2 + `,async,autofocus,autoplay,controls,default,defer,disabled,hidden,loop,open,required,reversed,scoped,seamless,checked,muted,multiple,selected`);
  var EMPTY_OBJ2 = true ? Object.freeze({}) : {};
  var EMPTY_ARR2 = true ? Object.freeze([]) : [];
  var extend2 = Object.assign;
  var hasOwnProperty2 = Object.prototype.hasOwnProperty;
  var hasOwn2 = (val, key) => hasOwnProperty2.call(val, key);
  var isArray3 = Array.isArray;
  var isMap2 = (val) => toTypeString2(val) === "[object Map]";
  var isString2 = (val) => typeof val === "string";
  var isSymbol2 = (val) => typeof val === "symbol";
  var isObject3 = (val) => val !== null && typeof val === "object";
  var objectToString2 = Object.prototype.toString;
  var toTypeString2 = (value2) => objectToString2.call(value2);
  var toRawType2 = (value2) => {
    return toTypeString2(value2).slice(8, -1);
  };
  var isIntegerKey2 = (key) => isString2(key) && key !== "NaN" && key[0] !== "-" && "" + parseInt(key, 10) === key;
  var cacheStringFunction2 = (fn) => {
    const cache = /* @__PURE__ */ Object.create(null);
    return (str) => {
      const hit = cache[str];
      return hit || (cache[str] = fn(str));
    };
  };
  var camelizeRE2 = /-(\w)/g;
  var camelize2 = cacheStringFunction2((str) => {
    return str.replace(camelizeRE2, (_, c) => c ? c.toUpperCase() : "");
  });
  var hyphenateRE2 = /\B([A-Z])/g;
  var hyphenate2 = cacheStringFunction2((str) => str.replace(hyphenateRE2, "-$1").toLowerCase());
  var capitalize2 = cacheStringFunction2((str) => str.charAt(0).toUpperCase() + str.slice(1));
  var toHandlerKey2 = cacheStringFunction2((str) => str ? `on${capitalize2(str)}` : ``);
  var hasChanged2 = (value2, oldValue) => !Object.is(value2, oldValue);

  // ../synthetic/node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js
  function warn2(msg, ...args) {
    console.warn(`[Vue warn] ${msg}`, ...args);
  }
  var activeEffectScope2;
  function recordEffectScope2(effect5, scope = activeEffectScope2) {
    if (scope && scope.active) {
      scope.effects.push(effect5);
    }
  }
  var createDep2 = (effects) => {
    const dep = new Set(effects);
    dep.w = 0;
    dep.n = 0;
    return dep;
  };
  var wasTracked2 = (dep) => (dep.w & trackOpBit2) > 0;
  var newTracked2 = (dep) => (dep.n & trackOpBit2) > 0;
  var initDepMarkers2 = ({ deps }) => {
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].w |= trackOpBit2;
      }
    }
  };
  var finalizeDepMarkers2 = (effect5) => {
    const { deps } = effect5;
    if (deps.length) {
      let ptr = 0;
      for (let i = 0; i < deps.length; i++) {
        const dep = deps[i];
        if (wasTracked2(dep) && !newTracked2(dep)) {
          dep.delete(effect5);
        } else {
          deps[ptr++] = dep;
        }
        dep.w &= ~trackOpBit2;
        dep.n &= ~trackOpBit2;
      }
      deps.length = ptr;
    }
  };
  var targetMap2 = /* @__PURE__ */ new WeakMap();
  var effectTrackDepth2 = 0;
  var trackOpBit2 = 1;
  var maxMarkerBits2 = 30;
  var activeEffect2;
  var ITERATE_KEY2 = Symbol(true ? "iterate" : "");
  var MAP_KEY_ITERATE_KEY2 = Symbol(true ? "Map key iterate" : "");
  var ReactiveEffect2 = class {
    constructor(fn, scheduler = null, scope) {
      this.fn = fn;
      this.scheduler = scheduler;
      this.active = true;
      this.deps = [];
      this.parent = void 0;
      recordEffectScope2(this, scope);
    }
    run() {
      if (!this.active) {
        return this.fn();
      }
      let parent = activeEffect2;
      let lastShouldTrack = shouldTrack2;
      while (parent) {
        if (parent === this) {
          return;
        }
        parent = parent.parent;
      }
      try {
        this.parent = activeEffect2;
        activeEffect2 = this;
        shouldTrack2 = true;
        trackOpBit2 = 1 << ++effectTrackDepth2;
        if (effectTrackDepth2 <= maxMarkerBits2) {
          initDepMarkers2(this);
        } else {
          cleanupEffect2(this);
        }
        return this.fn();
      } finally {
        if (effectTrackDepth2 <= maxMarkerBits2) {
          finalizeDepMarkers2(this);
        }
        trackOpBit2 = 1 << --effectTrackDepth2;
        activeEffect2 = this.parent;
        shouldTrack2 = lastShouldTrack;
        this.parent = void 0;
        if (this.deferStop) {
          this.stop();
        }
      }
    }
    stop() {
      if (activeEffect2 === this) {
        this.deferStop = true;
      } else if (this.active) {
        cleanupEffect2(this);
        if (this.onStop) {
          this.onStop();
        }
        this.active = false;
      }
    }
  };
  function cleanupEffect2(effect5) {
    const { deps } = effect5;
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].delete(effect5);
      }
      deps.length = 0;
    }
  }
  function effect3(fn, options) {
    if (fn.effect) {
      fn = fn.effect.fn;
    }
    const _effect = new ReactiveEffect2(fn);
    if (options) {
      extend2(_effect, options);
      if (options.scope)
        recordEffectScope2(_effect, options.scope);
    }
    if (!options || !options.lazy) {
      _effect.run();
    }
    const runner = _effect.run.bind(_effect);
    runner.effect = _effect;
    return runner;
  }
  function stop2(runner) {
    runner.effect.stop();
  }
  var shouldTrack2 = true;
  var trackStack2 = [];
  function pauseTracking2() {
    trackStack2.push(shouldTrack2);
    shouldTrack2 = false;
  }
  function resetTracking2() {
    const last = trackStack2.pop();
    shouldTrack2 = last === void 0 ? true : last;
  }
  function track2(target, type, key) {
    if (shouldTrack2 && activeEffect2) {
      let depsMap = targetMap2.get(target);
      if (!depsMap) {
        targetMap2.set(target, depsMap = /* @__PURE__ */ new Map());
      }
      let dep = depsMap.get(key);
      if (!dep) {
        depsMap.set(key, dep = createDep2());
      }
      const eventInfo = true ? { effect: activeEffect2, target, type, key } : void 0;
      trackEffects2(dep, eventInfo);
    }
  }
  function trackEffects2(dep, debuggerEventExtraInfo) {
    let shouldTrack3 = false;
    if (effectTrackDepth2 <= maxMarkerBits2) {
      if (!newTracked2(dep)) {
        dep.n |= trackOpBit2;
        shouldTrack3 = !wasTracked2(dep);
      }
    } else {
      shouldTrack3 = !dep.has(activeEffect2);
    }
    if (shouldTrack3) {
      dep.add(activeEffect2);
      activeEffect2.deps.push(dep);
      if (activeEffect2.onTrack) {
        activeEffect2.onTrack(Object.assign({ effect: activeEffect2 }, debuggerEventExtraInfo));
      }
    }
  }
  function trigger3(target, type, key, newValue, oldValue, oldTarget) {
    const depsMap = targetMap2.get(target);
    if (!depsMap) {
      return;
    }
    let deps = [];
    if (type === "clear") {
      deps = [...depsMap.values()];
    } else if (key === "length" && isArray3(target)) {
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
          if (!isArray3(target)) {
            deps.push(depsMap.get(ITERATE_KEY2));
            if (isMap2(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY2));
            }
          } else if (isIntegerKey2(key)) {
            deps.push(depsMap.get("length"));
          }
          break;
        case "delete":
          if (!isArray3(target)) {
            deps.push(depsMap.get(ITERATE_KEY2));
            if (isMap2(target)) {
              deps.push(depsMap.get(MAP_KEY_ITERATE_KEY2));
            }
          }
          break;
        case "set":
          if (isMap2(target)) {
            deps.push(depsMap.get(ITERATE_KEY2));
          }
          break;
      }
    }
    const eventInfo = true ? { target, type, key, newValue, oldValue, oldTarget } : void 0;
    if (deps.length === 1) {
      if (deps[0]) {
        if (true) {
          triggerEffects2(deps[0], eventInfo);
        } else {
          triggerEffects2(deps[0]);
        }
      }
    } else {
      const effects = [];
      for (const dep of deps) {
        if (dep) {
          effects.push(...dep);
        }
      }
      if (true) {
        triggerEffects2(createDep2(effects), eventInfo);
      } else {
        triggerEffects2(createDep2(effects));
      }
    }
  }
  function triggerEffects2(dep, debuggerEventExtraInfo) {
    const effects = isArray3(dep) ? dep : [...dep];
    for (const effect5 of effects) {
      if (effect5.computed) {
        triggerEffect2(effect5, debuggerEventExtraInfo);
      }
    }
    for (const effect5 of effects) {
      if (!effect5.computed) {
        triggerEffect2(effect5, debuggerEventExtraInfo);
      }
    }
  }
  function triggerEffect2(effect5, debuggerEventExtraInfo) {
    if (effect5 !== activeEffect2 || effect5.allowRecurse) {
      if (effect5.onTrigger) {
        effect5.onTrigger(extend2({ effect: effect5 }, debuggerEventExtraInfo));
      }
      if (effect5.scheduler) {
        effect5.scheduler();
      } else {
        effect5.run();
      }
    }
  }
  var isNonTrackableKeys2 = /* @__PURE__ */ makeMap2(`__proto__,__v_isRef,__isVue`);
  var builtInSymbols2 = new Set(/* @__PURE__ */ Object.getOwnPropertyNames(Symbol).filter((key) => key !== "arguments" && key !== "caller").map((key) => Symbol[key]).filter(isSymbol2));
  var get2 = /* @__PURE__ */ createGetter2();
  var readonlyGet2 = /* @__PURE__ */ createGetter2(true);
  var arrayInstrumentations2 = /* @__PURE__ */ createArrayInstrumentations2();
  function createArrayInstrumentations2() {
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
        pauseTracking2();
        const res = toRaw2(this)[key].apply(this, args);
        resetTracking2();
        return res;
      };
    });
    return instrumentations;
  }
  function createGetter2(isReadonly3 = false, shallow = false) {
    return function get3(target, key, receiver) {
      if (key === "__v_isReactive") {
        return !isReadonly3;
      } else if (key === "__v_isReadonly") {
        return isReadonly3;
      } else if (key === "__v_isShallow") {
        return shallow;
      } else if (key === "__v_raw" && receiver === (isReadonly3 ? shallow ? shallowReadonlyMap2 : readonlyMap2 : shallow ? shallowReactiveMap2 : reactiveMap2).get(target)) {
        return target;
      }
      const targetIsArray = isArray3(target);
      if (!isReadonly3 && targetIsArray && hasOwn2(arrayInstrumentations2, key)) {
        return Reflect.get(arrayInstrumentations2, key, receiver);
      }
      const res = Reflect.get(target, key, receiver);
      if (isSymbol2(key) ? builtInSymbols2.has(key) : isNonTrackableKeys2(key)) {
        return res;
      }
      if (!isReadonly3) {
        track2(target, "get", key);
      }
      if (shallow) {
        return res;
      }
      if (isRef2(res)) {
        return targetIsArray && isIntegerKey2(key) ? res : res.value;
      }
      if (isObject3(res)) {
        return isReadonly3 ? readonly2(res) : reactive3(res);
      }
      return res;
    };
  }
  var set2 = /* @__PURE__ */ createSetter2();
  function createSetter2(shallow = false) {
    return function set3(target, key, value2, receiver) {
      let oldValue = target[key];
      if (isReadonly2(oldValue) && isRef2(oldValue) && !isRef2(value2)) {
        return false;
      }
      if (!shallow && !isReadonly2(value2)) {
        if (!isShallow2(value2)) {
          value2 = toRaw2(value2);
          oldValue = toRaw2(oldValue);
        }
        if (!isArray3(target) && isRef2(oldValue) && !isRef2(value2)) {
          oldValue.value = value2;
          return true;
        }
      }
      const hadKey = isArray3(target) && isIntegerKey2(key) ? Number(key) < target.length : hasOwn2(target, key);
      const result = Reflect.set(target, key, value2, receiver);
      if (target === toRaw2(receiver)) {
        if (!hadKey) {
          trigger3(target, "add", key, value2);
        } else if (hasChanged2(value2, oldValue)) {
          trigger3(target, "set", key, value2, oldValue);
        }
      }
      return result;
    };
  }
  function deleteProperty2(target, key) {
    const hadKey = hasOwn2(target, key);
    const oldValue = target[key];
    const result = Reflect.deleteProperty(target, key);
    if (result && hadKey) {
      trigger3(target, "delete", key, void 0, oldValue);
    }
    return result;
  }
  function has2(target, key) {
    const result = Reflect.has(target, key);
    if (!isSymbol2(key) || !builtInSymbols2.has(key)) {
      track2(target, "has", key);
    }
    return result;
  }
  function ownKeys2(target) {
    track2(target, "iterate", isArray3(target) ? "length" : ITERATE_KEY2);
    return Reflect.ownKeys(target);
  }
  var mutableHandlers2 = {
    get: get2,
    set: set2,
    deleteProperty: deleteProperty2,
    has: has2,
    ownKeys: ownKeys2
  };
  var readonlyHandlers2 = {
    get: readonlyGet2,
    set(target, key) {
      if (true) {
        warn2(`Set operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    },
    deleteProperty(target, key) {
      if (true) {
        warn2(`Delete operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    }
  };
  var toShallow2 = (value2) => value2;
  var getProto2 = (v) => Reflect.getPrototypeOf(v);
  function get$12(target, key, isReadonly3 = false, isShallow3 = false) {
    target = target["__v_raw"];
    const rawTarget = toRaw2(target);
    const rawKey = toRaw2(key);
    if (!isReadonly3) {
      if (key !== rawKey) {
        track2(rawTarget, "get", key);
      }
      track2(rawTarget, "get", rawKey);
    }
    const { has: has3 } = getProto2(rawTarget);
    const wrap = isShallow3 ? toShallow2 : isReadonly3 ? toReadonly2 : toReactive2;
    if (has3.call(rawTarget, key)) {
      return wrap(target.get(key));
    } else if (has3.call(rawTarget, rawKey)) {
      return wrap(target.get(rawKey));
    } else if (target !== rawTarget) {
      target.get(key);
    }
  }
  function has$12(key, isReadonly3 = false) {
    const target = this["__v_raw"];
    const rawTarget = toRaw2(target);
    const rawKey = toRaw2(key);
    if (!isReadonly3) {
      if (key !== rawKey) {
        track2(rawTarget, "has", key);
      }
      track2(rawTarget, "has", rawKey);
    }
    return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
  }
  function size2(target, isReadonly3 = false) {
    target = target["__v_raw"];
    !isReadonly3 && track2(toRaw2(target), "iterate", ITERATE_KEY2);
    return Reflect.get(target, "size", target);
  }
  function add2(value2) {
    value2 = toRaw2(value2);
    const target = toRaw2(this);
    const proto = getProto2(target);
    const hadKey = proto.has.call(target, value2);
    if (!hadKey) {
      target.add(value2);
      trigger3(target, "add", value2, value2);
    }
    return this;
  }
  function set$12(key, value2) {
    value2 = toRaw2(value2);
    const target = toRaw2(this);
    const { has: has3, get: get3 } = getProto2(target);
    let hadKey = has3.call(target, key);
    if (!hadKey) {
      key = toRaw2(key);
      hadKey = has3.call(target, key);
    } else if (true) {
      checkIdentityKeys2(target, has3, key);
    }
    const oldValue = get3.call(target, key);
    target.set(key, value2);
    if (!hadKey) {
      trigger3(target, "add", key, value2);
    } else if (hasChanged2(value2, oldValue)) {
      trigger3(target, "set", key, value2, oldValue);
    }
    return this;
  }
  function deleteEntry2(key) {
    const target = toRaw2(this);
    const { has: has3, get: get3 } = getProto2(target);
    let hadKey = has3.call(target, key);
    if (!hadKey) {
      key = toRaw2(key);
      hadKey = has3.call(target, key);
    } else if (true) {
      checkIdentityKeys2(target, has3, key);
    }
    const oldValue = get3 ? get3.call(target, key) : void 0;
    const result = target.delete(key);
    if (hadKey) {
      trigger3(target, "delete", key, void 0, oldValue);
    }
    return result;
  }
  function clear2() {
    const target = toRaw2(this);
    const hadItems = target.size !== 0;
    const oldTarget = true ? isMap2(target) ? new Map(target) : new Set(target) : void 0;
    const result = target.clear();
    if (hadItems) {
      trigger3(target, "clear", void 0, void 0, oldTarget);
    }
    return result;
  }
  function createForEach2(isReadonly3, isShallow3) {
    return function forEach(callback, thisArg) {
      const observed = this;
      const target = observed["__v_raw"];
      const rawTarget = toRaw2(target);
      const wrap = isShallow3 ? toShallow2 : isReadonly3 ? toReadonly2 : toReactive2;
      !isReadonly3 && track2(rawTarget, "iterate", ITERATE_KEY2);
      return target.forEach((value2, key) => {
        return callback.call(thisArg, wrap(value2), wrap(key), observed);
      });
    };
  }
  function createIterableMethod2(method, isReadonly3, isShallow3) {
    return function(...args) {
      const target = this["__v_raw"];
      const rawTarget = toRaw2(target);
      const targetIsMap = isMap2(rawTarget);
      const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
      const isKeyOnly = method === "keys" && targetIsMap;
      const innerIterator = target[method](...args);
      const wrap = isShallow3 ? toShallow2 : isReadonly3 ? toReadonly2 : toReactive2;
      !isReadonly3 && track2(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY2 : ITERATE_KEY2);
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
  function createReadonlyMethod2(type) {
    return function(...args) {
      if (true) {
        const key = args[0] ? `on key "${args[0]}" ` : ``;
        console.warn(`${capitalize2(type)} operation ${key}failed: target is readonly.`, toRaw2(this));
      }
      return type === "delete" ? false : this;
    };
  }
  function createInstrumentations2() {
    const mutableInstrumentations3 = {
      get(key) {
        return get$12(this, key);
      },
      get size() {
        return size2(this);
      },
      has: has$12,
      add: add2,
      set: set$12,
      delete: deleteEntry2,
      clear: clear2,
      forEach: createForEach2(false, false)
    };
    const shallowInstrumentations3 = {
      get(key) {
        return get$12(this, key, false, true);
      },
      get size() {
        return size2(this);
      },
      has: has$12,
      add: add2,
      set: set$12,
      delete: deleteEntry2,
      clear: clear2,
      forEach: createForEach2(false, true)
    };
    const readonlyInstrumentations3 = {
      get(key) {
        return get$12(this, key, true);
      },
      get size() {
        return size2(this, true);
      },
      has(key) {
        return has$12.call(this, key, true);
      },
      add: createReadonlyMethod2("add"),
      set: createReadonlyMethod2("set"),
      delete: createReadonlyMethod2("delete"),
      clear: createReadonlyMethod2("clear"),
      forEach: createForEach2(true, false)
    };
    const shallowReadonlyInstrumentations3 = {
      get(key) {
        return get$12(this, key, true, true);
      },
      get size() {
        return size2(this, true);
      },
      has(key) {
        return has$12.call(this, key, true);
      },
      add: createReadonlyMethod2("add"),
      set: createReadonlyMethod2("set"),
      delete: createReadonlyMethod2("delete"),
      clear: createReadonlyMethod2("clear"),
      forEach: createForEach2(true, true)
    };
    const iteratorMethods = ["keys", "values", "entries", Symbol.iterator];
    iteratorMethods.forEach((method) => {
      mutableInstrumentations3[method] = createIterableMethod2(method, false, false);
      readonlyInstrumentations3[method] = createIterableMethod2(method, true, false);
      shallowInstrumentations3[method] = createIterableMethod2(method, false, true);
      shallowReadonlyInstrumentations3[method] = createIterableMethod2(method, true, true);
    });
    return [
      mutableInstrumentations3,
      readonlyInstrumentations3,
      shallowInstrumentations3,
      shallowReadonlyInstrumentations3
    ];
  }
  var [mutableInstrumentations2, readonlyInstrumentations2, shallowInstrumentations2, shallowReadonlyInstrumentations2] = /* @__PURE__ */ createInstrumentations2();
  function createInstrumentationGetter2(isReadonly3, shallow) {
    const instrumentations = shallow ? isReadonly3 ? shallowReadonlyInstrumentations2 : shallowInstrumentations2 : isReadonly3 ? readonlyInstrumentations2 : mutableInstrumentations2;
    return (target, key, receiver) => {
      if (key === "__v_isReactive") {
        return !isReadonly3;
      } else if (key === "__v_isReadonly") {
        return isReadonly3;
      } else if (key === "__v_raw") {
        return target;
      }
      return Reflect.get(hasOwn2(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
    };
  }
  var mutableCollectionHandlers2 = {
    get: /* @__PURE__ */ createInstrumentationGetter2(false, false)
  };
  var readonlyCollectionHandlers2 = {
    get: /* @__PURE__ */ createInstrumentationGetter2(true, false)
  };
  function checkIdentityKeys2(target, has3, key) {
    const rawKey = toRaw2(key);
    if (rawKey !== key && has3.call(target, rawKey)) {
      const type = toRawType2(target);
      console.warn(`Reactive ${type} contains both the raw and reactive versions of the same object${type === `Map` ? ` as keys` : ``}, which can lead to inconsistencies. Avoid differentiating between the raw and reactive versions of an object and only use the reactive version if possible.`);
    }
  }
  var reactiveMap2 = /* @__PURE__ */ new WeakMap();
  var shallowReactiveMap2 = /* @__PURE__ */ new WeakMap();
  var readonlyMap2 = /* @__PURE__ */ new WeakMap();
  var shallowReadonlyMap2 = /* @__PURE__ */ new WeakMap();
  function targetTypeMap2(rawType) {
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
  function getTargetType2(value2) {
    return value2["__v_skip"] || !Object.isExtensible(value2) ? 0 : targetTypeMap2(toRawType2(value2));
  }
  function reactive3(target) {
    if (isReadonly2(target)) {
      return target;
    }
    return createReactiveObject2(target, false, mutableHandlers2, mutableCollectionHandlers2, reactiveMap2);
  }
  function readonly2(target) {
    return createReactiveObject2(target, true, readonlyHandlers2, readonlyCollectionHandlers2, readonlyMap2);
  }
  function createReactiveObject2(target, isReadonly3, baseHandlers, collectionHandlers, proxyMap) {
    if (!isObject3(target)) {
      if (true) {
        console.warn(`value cannot be made reactive: ${String(target)}`);
      }
      return target;
    }
    if (target["__v_raw"] && !(isReadonly3 && target["__v_isReactive"])) {
      return target;
    }
    const existingProxy = proxyMap.get(target);
    if (existingProxy) {
      return existingProxy;
    }
    const targetType = getTargetType2(target);
    if (targetType === 0) {
      return target;
    }
    const proxy = new Proxy(target, targetType === 2 ? collectionHandlers : baseHandlers);
    proxyMap.set(target, proxy);
    return proxy;
  }
  function isReadonly2(value2) {
    return !!(value2 && value2["__v_isReadonly"]);
  }
  function isShallow2(value2) {
    return !!(value2 && value2["__v_isShallow"]);
  }
  function toRaw2(observed) {
    const raw3 = observed && observed["__v_raw"];
    return raw3 ? toRaw2(raw3) : observed;
  }
  var toReactive2 = (value2) => isObject3(value2) ? reactive3(value2) : value2;
  var toReadonly2 = (value2) => isObject3(value2) ? readonly2(value2) : value2;
  function isRef2(r) {
    return !!(r && r.__v_isRef === true);
  }
  var _a2;
  _a2 = "__v_isReadonly";

  // ../synthetic/js/utils.js
  function isObjecty2(subject) {
    return typeof subject === "object" && subject !== null;
  }
  function isObject4(subject) {
    return isObjecty2(subject) && !isArray4(subject);
  }
  function isArray4(subject) {
    return Array.isArray(subject);
  }
  function isFunction4(subject) {
    return typeof subject === "function";
  }
  function isPrimitive2(subject) {
    return typeof subject !== "object" || subject === null;
  }
  function deepClone2(obj) {
    return JSON.parse(JSON.stringify(obj));
  }
  function deeplyEqual2(a, b) {
    return JSON.stringify(a) === JSON.stringify(b);
  }
  function each2(subject, callback) {
    Object.entries(subject).forEach(([key, value2]) => callback(key, value2));
  }
  function dataGet3(object2, key) {
    if (key === "")
      return object2;
    return key.split(".").reduce((carry, i) => {
      if (carry === void 0)
        return void 0;
      return carry[i];
    }, object2);
  }
  function diff2(left, right, diffs = {}, path = "") {
    if (left === right)
      return diffs;
    if (typeof left !== typeof right || isObject4(left) && isArray4(right) || isArray4(left) && isObject4(right)) {
      diffs[path] = right;
      return diffs;
    }
    if (isPrimitive2(left) || isPrimitive2(right)) {
      diffs[path] = right;
      return diffs;
    }
    let leftKeys = Object.keys(left);
    Object.entries(right).forEach(([key, value2]) => {
      diffs = { ...diffs, ...diff2(left[key], right[key], diffs, path === "" ? key : `${path}.${key}`) };
      leftKeys = leftKeys.filter((i) => i !== key);
    });
    leftKeys.forEach((key) => {
      diffs[`${path}.${key}`] = "__rm__";
    });
    return diffs;
  }

  // ../synthetic/js/modal.js
  function showHtmlModal2(html) {
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
    modal.addEventListener("click", () => hideHtmlModal2(modal));
    modal.setAttribute("tabindex", 0);
    modal.addEventListener("keydown", (e) => {
      if (e.key === "Escape")
        hideHtmlModal2(modal);
    });
    modal.focus();
  }
  function hideHtmlModal2(modal) {
    modal.outerHTML = "";
    document.body.style.overflow = "visible";
  }

  // ../synthetic/js/events.js
  var listeners3 = [];
  function on3(name, callback) {
    if (!listeners3[name])
      listeners3[name] = [];
    listeners3[name].push(callback);
  }
  function trigger4(name, ...params) {
    let callbacks = listeners3[name] || [];
    let finishers = [];
    for (let i = 0; i < callbacks.length; i++) {
      let finisher = callbacks[i](...params);
      if (isFunction4(finisher))
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
  function methods_default2() {
    on3("decorate", (target, path, addProp, decorator, symbol) => {
      let effects = target.effects[path];
      if (!effects)
        return;
      let methods = effects["methods"] || [];
      methods.forEach((method) => {
        addProp(method, async (...params) => {
          if (params.length === 1 && params[0] instanceof Event) {
            params = [];
          }
          return await callMethod2(symbol, path, method, params);
        });
      });
    });
    on3("decorate", (target, path, addProp) => {
      let effects = target.effects[path];
      if (!effects)
        return;
      let methods = effects["js"] || [];
      let AsyncFunction = Object.getPrototypeOf(async function() {
      }).constructor;
      each2(methods, (name, expression) => {
        let func = new AsyncFunction([], expression);
        addProp(name, () => {
          func.bind(dataGet3(target.reactive, path))();
        });
      });
    });
  }

  // ../synthetic/js/features/prefetch.js
  function prefetch_default2() {
  }

  // ../synthetic/js/features/redirect.js
  function redirect_default2() {
    on3("effects", (target, effects) => {
      if (!effects["redirect"])
        return;
      let url = effects["redirect"];
      window.location.href = url;
    });
  }

  // ../synthetic/js/features/loading.js
  function loading_default2() {
    on3("new", (target) => {
      target.__loading = reactive4({ state: false });
    });
    on3("target.request", (target, payload) => {
      target.__loading.state = true;
      return () => target.__loading.state = false;
    });
    on3("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$loading", { get() {
        return target.__loading.state;
      } });
    });
  }

  // ../synthetic/js/features/polling.js
  function polling_default2() {
    on3("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$poll", (callback) => {
        syncronizedInterval2(2500, () => {
          callback();
          target.ephemeral.$commit();
        });
      });
    });
  }
  var clocks2 = [];
  function syncronizedInterval2(ms, callback) {
    if (!clocks2[ms]) {
      let clock = {
        timer: setInterval(() => each2(clock.callbacks, (key, value2) => value2()), ms),
        callbacks: []
      };
      clocks2[ms] = clock;
    }
    clocks2[ms].callbacks.push(callback);
  }

  // ../synthetic/js/features/errors.js
  function errors_default2() {
    on3("new", (target, path) => {
      target.__errors = reactive4({ state: [] });
    });
    on3("decorate", (target, path) => {
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
    on3("effects", (target, effects, path) => {
      let errors = effects["errors"] || [];
      target.__errors.state = errors;
    });
  }

  // ../synthetic/js/features/dirty.js
  function dirty_default2() {
    on3("new", (target) => {
      target.__dirty = reactive4({ state: 0 });
    });
    on3("target.request", (target, payload) => {
      return () => target.__dirty.state = +new Date();
    });
    on3("decorate", (target, path) => {
      return (decorator) => {
        Object.defineProperty(decorator, "$dirty", { get() {
          let throwaway = target.__dirty.state;
          let thing1 = dataGet3(target.canonical, path);
          let thing2 = dataGet3(target.reactive, path);
          return !deeplyEqual2(thing1, thing2);
        } });
        return decorator;
      };
    });
  }

  // ../synthetic/js/features/index.js
  methods_default2();
  prefetch_default2();
  redirect_default2();
  loading_default2();
  polling_default2();
  errors_default2();
  dirty_default2();

  // ../synthetic/js/index.js
  var reactive4 = reactive3;
  var release2 = stop2;
  var effect4 = effect3;
  var raw2 = toRaw2;
  document.addEventListener("alpine:init", () => {
    reactive4 = Alpine.reactive;
    effect4 = Alpine.effect;
    release2 = Alpine.release;
    raw2 = Alpine.raw;
  });
  var store2 = /* @__PURE__ */ new Map();
  function extractData2(payload, symbol, decorate2 = (i) => i, path = "") {
    let value2 = isSynthetic2(payload) ? payload[0] : payload;
    let meta = isSynthetic2(payload) ? payload[1] : void 0;
    if (isObjecty2(value2)) {
      Object.entries(value2).forEach(([key, iValue]) => {
        value2[key] = extractData2(iValue, symbol, decorate2, path === "" ? key : `${path}.${key}`);
      });
    }
    return meta !== void 0 && isObjecty2(value2) ? decorate2(value2, meta, symbol, path) : value2;
  }
  function isSynthetic2(subject) {
    return Array.isArray(subject) && subject.length === 2 && typeof subject[1] === "object" && Object.keys(subject[1]).includes("s");
  }
  async function callMethod2(symbol, path, method, params) {
    let result = await requestMethodCall2(symbol, path, method, params);
    return result;
  }
  var requestTargetQueue2 = /* @__PURE__ */ new Map();
  function requestMethodCall2(symbol, path, method, params) {
    requestCommit2(symbol);
    return new Promise((resolve, reject) => {
      let queue = requestTargetQueue2.get(symbol);
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
  function requestCommit2(symbol) {
    if (!requestTargetQueue2.has(symbol)) {
      requestTargetQueue2.set(symbol, { calls: [], receivers: [] });
    }
    triggerSend2();
    return new Promise((resolve, reject) => {
      let queue = requestTargetQueue2.get(symbol);
      queue.handleResponse = () => resolve();
    });
  }
  var requestBufferTimeout2;
  function triggerSend2() {
    if (requestBufferTimeout2)
      return;
    requestBufferTimeout2 = setTimeout(() => {
      sendMethodCall2();
      requestBufferTimeout2 = void 0;
    }, 5);
  }
  async function sendMethodCall2() {
    requestTargetQueue2.forEach((request2, symbol) => {
      let target = store2.get(symbol);
      trigger4("request.before", target);
    });
    let payload = [];
    let receivers = [];
    requestTargetQueue2.forEach((request2, symbol) => {
      let target = store2.get(symbol);
      let propertiesDiff = diff2(target.canonical, target.ephemeral);
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
      let finish2 = trigger4("target.request", target, targetPaylaod);
      receivers.push((snapshot, effects) => {
        mergeNewSnapshot2(symbol, snapshot, effects);
        processEffects2(target);
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
    requestTargetQueue2.clear();
    let finish = trigger4("request", payload);
    let request = await fetch("/synthetic/update", {
      method: "POST",
      body: JSON.stringify({
        _token: getCsrfToken2(),
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
      trigger4("response.success");
    } else {
      let html = await request.text();
      showHtmlModal2(html);
      trigger4("response.failure");
    }
    finish();
  }
  function getCsrfToken2() {
    if (document.querySelector('meta[name="csrf"]')) {
      return document.querySelector('meta[name="csrf"]').content;
    }
    return window.__csrf;
  }
  function mergeNewSnapshot2(symbol, snapshot, effects) {
    let target = store2.get(symbol);
    target.snapshot = snapshot;
    target.effects = effects;
    target.canonical = extractData2(deepClone2(snapshot.data), symbol);
    let newData = extractData2(deepClone2(snapshot.data), symbol);
    Object.entries(target.ephemeral).forEach(([key, value2]) => {
      if (!deeplyEqual2(target.ephemeral[key], newData[key])) {
        target.reactive[key] = newData[key];
      }
    });
  }
  function processEffects2(target) {
    let effects = target.effects;
    each2(effects, (key, value2) => trigger4("effects", target, value2, key));
  }

  // src/Features/SupportHotReloading/SupportHotReloading.js
  function SupportHotReloading_default(enabled) {
    if (!navigator.userAgent.includes("Electron"))
      return;
    if (!enabled.includes("hot-reloading"))
      return;
    on3("effects", (target, effects, path) => {
      queueMicrotask(() => {
        let files = effects.hotReload;
        if (!files)
          return;
        let component = findComponent(target.__livewireId);
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
      if (data.file && listeners4[data.file]) {
        listeners4[data.file].forEach((cb) => cb());
      }
    });
    es.onerror = function(err) {
    };
    es.onopen = function(err) {
    };
  }
  var listeners4 = [];
  function whenFileIsModified(file, callback) {
    if (!listeners4[file])
      listeners4[file] = [];
    listeners4[file].push(callback);
  }

  // js/features/wireLoading.js
  function wireLoading_default() {
    on2("element.init", (el, component) => {
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

  // js/features/wirePoll.js
  function wirePoll_default() {
    on("element.init", (el, component) => {
      let elDirectives = directives(el);
      if (elDirectives.missing("poll"))
        return;
      let directive = elDirectives.get("poll");
      Alpine.bind(el, {
        "x-init"() {
          component.$wire.$poll(() => {
            directive.value ? Alpine.evaluate(el, "$wire." + directive.value) : Alpine.evaluate(el, "$wire.$commit()");
          });
        }
      });
    });
  }

  // js/features/wireParent.js
  function wireParent_default() {
    on("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$parent", { get() {
        let component = findComponent(target.__livewireId);
        let parent = closestComponent(component.el.parentElement);
        return parent.$wire;
      } });
    });
  }

  // js/features/wireTransition.js
  function wireTransition_default() {
    on("morph.added", (el) => {
      el.__addedByMorph = true;
    });
    on("element.init", (el, component) => {
      if (!el.__addedByMorph)
        return;
      let elDirectives = directives(el);
      if (elDirectives.missing("transition"))
        return;
      let directive = elDirectives.get("transition");
      let visibility = Alpine.reactive({ state: false });
      Alpine.bind(el, {
        [directive.rawName.replace("wire:", "x-")]: "",
        "x-show"() {
          return visibility.state;
        },
        "x-init"() {
          setTimeout(() => visibility.state = true);
        }
      });
    });
  }

  // js/features/wireNavigate.js
  function wireNavigate_default() {
    return;
    on("element.init", (el, component) => {
      let elDirectives = directives(el);
      if (elDirectives.missing("navigate"))
        return;
      let directive = elDirectives.get("navigate");
      Alpine.bind(el, {
        "x-init"() {
          component.$wire.$poll(() => {
            directive.value ? Alpine.evaluate(el, "$wire." + directive.value) : Alpine.evaluate(el, "$wire.$commit()");
          });
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
    on("request.before", (target) => {
      let meta = target.snapshot.data[1];
      let childIds = Object.values(meta.children).map((i) => i[1]);
      childIds.forEach((id) => {
        let child = findComponent(id);
        let childSynthetic = child.synthetic;
        let childMeta = childSynthetic.snapshot.data[1];
        let props = childMeta.props;
        if (props)
          childSynthetic.ephemeral.$commit();
      });
    });
  }

  // js/features/index.js
  function features_default(enabledFeatures) {
    wire_default(enabledFeatures);
    props_default(enabledFeatures);
    morphDom_default(enabledFeatures);
    wireModel_default(enabledFeatures);
    wireParent_default(enabledFeatures);
    wirePoll_default(enabledFeatures);
    wireLoading_default(enabledFeatures);
    wireTransition_default(enabledFeatures);
    wireNavigate_default(enabledFeatures);
    wireWildcard_default(enabledFeatures);
    SupportHotReloading_default(enabledFeatures);
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
  function start(options) {
    let enabledFeatures = options.features || [];
    features_default(enabledFeatures);
    Alpine.interceptInit(Alpine.skipDuringClone((el) => {
      initElement(el);
    }));
  }
  function initElement(el) {
    if (el.hasAttribute("wire:id")) {
      let id = el.getAttribute("wire:id");
      let initialData = JSON.parse(el.getAttribute("wire:initial-data"));
      if (!initialData) {
        initialData = resurrect(id);
      }
      let component2 = new Component(synthetic(initialData).__target, el, id);
      el.__livewire = component2;
      Alpine.bind(el, {
        "x-data"() {
          return component2.synthetic.reactive;
        },
        "x-destroy"() {
          releaseComponent(component2.id);
        }
      });
      storeComponent(component2.id, component2);
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
  window.synthetic = synthetic;
  window.syntheticOn = on;
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
