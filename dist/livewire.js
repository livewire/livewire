(() => {
  // js/synthetic/utils.js
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
  function componentsByName(name) {
    return Object.values(state.components).filter((component) => {
      debugger;
      return name == component.name;
    });
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
  function find(id) {
    let component = state.components[id];
    return component && component.$wire;
  }
  function first() {
    return Object.values(state.components)[0].$wire;
  }

  // node_modules/@vue/shared/dist/shared.esm-bundler.js
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
  var toNumber = (val) => {
    const n = parseFloat(val);
    return isNaN(n) ? val : n;
  };

  // node_modules/@vue/reactivity/dist/reactivity.esm-bundler.js
  var activeEffectScope;
  function recordEffectScope(effect4, scope2 = activeEffectScope) {
    if (scope2 && scope2.active) {
      scope2.effects.push(effect4);
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
  var finalizeDepMarkers = (effect4) => {
    const { deps } = effect4;
    if (deps.length) {
      let ptr = 0;
      for (let i = 0; i < deps.length; i++) {
        const dep = deps[i];
        if (wasTracked(dep) && !newTracked(dep)) {
          dep.delete(effect4);
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
    constructor(fn, scheduler2 = null, scope2) {
      this.fn = fn;
      this.scheduler = scheduler2;
      this.active = true;
      this.deps = [];
      this.parent = void 0;
      recordEffectScope(this, scope2);
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
  function cleanupEffect(effect4) {
    const { deps } = effect4;
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].delete(effect4);
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
    } else if (key === "length" && isArray2(target)) {
      const newLength = toNumber(newValue);
      depsMap.forEach((dep, key2) => {
        if (key2 === "length" || key2 >= newLength) {
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
    const effects = isArray2(dep) ? dep : [...dep];
    for (const effect4 of effects) {
      if (effect4.computed) {
        triggerEffect(effect4, debuggerEventExtraInfo);
      }
    }
    for (const effect4 of effects) {
      if (!effect4.computed) {
        triggerEffect(effect4, debuggerEventExtraInfo);
      }
    }
  }
  function triggerEffect(effect4, debuggerEventExtraInfo) {
    if (effect4 !== activeEffect || effect4.allowRecurse) {
      if (false) {
        effect4.onTrigger(extend({ effect: effect4 }, debuggerEventExtraInfo));
      }
      if (effect4.scheduler) {
        effect4.scheduler();
      } else {
        effect4.run();
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
    return function get3(target, key, receiver) {
      if (key === "__v_isReactive") {
        return !isReadonly2;
      } else if (key === "__v_isReadonly") {
        return isReadonly2;
      } else if (key === "__v_isShallow") {
        return shallow;
      } else if (key === "__v_raw" && receiver === (isReadonly2 ? shallow ? shallowReadonlyMap : readonlyMap : shallow ? shallowReactiveMap : reactiveMap).get(target)) {
        return target;
      }
      const targetIsArray = isArray2(target);
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
      if (isObject2(res)) {
        return isReadonly2 ? readonly(res) : reactive(res);
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
      if (!shallow) {
        if (!isShallow(value2) && !isReadonly(value2)) {
          oldValue = toRaw(oldValue);
          value2 = toRaw(value2);
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
    const { has: has3 } = getProto(rawTarget);
    const wrap = isShallow2 ? toShallow : isReadonly2 ? toReadonly : toReactive;
    if (has3.call(rawTarget, key)) {
      return wrap(target.get(key));
    } else if (has3.call(rawTarget, rawKey)) {
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
    const { has: has3, get: get3 } = getProto(target);
    let hadKey = has3.call(target, key);
    if (!hadKey) {
      key = toRaw(key);
      hadKey = has3.call(target, key);
    } else if (false) {
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
    } else if (false) {
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
    const iteratorMethods2 = ["keys", "values", "entries", Symbol.iterator];
    iteratorMethods2.forEach((method) => {
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
    if (!isObject2(target)) {
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
  var _a$1;
  _a$1 = "__v_isReadonly";

  // js/synthetic/modal.js
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

  // js/synthetic/events.js
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

  // ../alpine/packages/alpinejs/dist/module.esm.js
  var flushPending = false;
  var flushing = false;
  var queue = [];
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
    if (index !== -1)
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
    }
    queue.length = 0;
    flushing = false;
  }
  var reactive2;
  var effect2;
  var release;
  var raw;
  var shouldSchedule = true;
  function disableEffectScheduling(callback) {
    shouldSchedule = false;
    callback();
    shouldSchedule = true;
  }
  function setReactivityEngine(engine) {
    reactive2 = engine.reactive;
    release = engine.release;
    effect2 = (callback) => engine.effect(callback, { scheduler: (task) => {
      if (shouldSchedule) {
        scheduler(task);
      } else {
        task();
      }
    } });
    raw = engine.raw;
  }
  function overrideEffect(override) {
    effect2 = override;
  }
  function elementBoundEffect(el) {
    let cleanup22 = () => {
    };
    let wrappedEffect = (callback) => {
      let effectReference = effect2(callback);
      if (!el._x_effects) {
        el._x_effects = /* @__PURE__ */ new Set();
        el._x_runEffects = () => {
          el._x_effects.forEach((i) => i());
        };
      }
      el._x_effects.add(effectReference);
      cleanup22 = () => {
        if (effectReference === void 0)
          return;
        el._x_effects.delete(effectReference);
        release(effectReference);
      };
      return effectReference;
    };
    return [wrappedEffect, () => {
      cleanup22();
    }];
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
    Object.entries(el._x_attributeCleanups).forEach(([name, value2]) => {
      if (names === void 0 || names.includes(name)) {
        value2.forEach((i) => i());
        delete el._x_attributeCleanups[name];
      }
    });
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
        let add22 = () => {
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
          add22();
        } else if (el.hasAttribute(name)) {
          remove();
          add22();
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
      if (node._x_cleanups) {
        while (node._x_cleanups.length)
          node._x_cleanups.pop()();
      }
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
  function refreshScope(element, scope2) {
    let existingScope = element._x_dataStack[0];
    Object.entries(scope2).forEach(([key, value2]) => {
      existingScope[key] = value2;
    });
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
              let property2 = descriptor;
              getter = getter && getter.bind(thisProxy);
              setter = setter && setter.bind(thisProxy);
              if (getter)
                getter._x_alreadyBound = true;
              if (setter)
                setter._x_alreadyBound = true;
              Object.defineProperty(obj, name, {
                ...property2,
                get: getter,
                set: setter
              });
            }
            return true;
          }
          return false;
        }) || {})[name];
      },
      set: (target, name, value2) => {
        let closestObjectWithKey = objects.find((obj) => obj.hasOwnProperty(name));
        if (closestObjectWithKey) {
          closestObjectWithKey[name] = value2;
        } else {
          objects[objects.length - 1][name] = value2;
        }
        return true;
      }
    });
    return thisProxy;
  }
  function initInterceptors(data2) {
    let isObject22 = (val) => typeof val === "object" && !Array.isArray(val) && val !== null;
    let recurse = (obj, basePath = "") => {
      Object.entries(Object.getOwnPropertyDescriptors(obj)).forEach(([key, { value: value2, enumerable }]) => {
        if (enumerable === false || value2 === void 0)
          return;
        let path = basePath === "" ? key : `${basePath}.${key}`;
        if (typeof value2 === "object" && value2 !== null && value2._x_interceptor) {
          obj[key] = value2.initialize(data2, path, key);
        } else {
          if (isObject22(value2) && value2 !== obj && !(value2 instanceof Element)) {
            recurse(value2, path);
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
        return callback(this.initialValue, () => get2(data2, path), (value2) => set2(data2, path, value2), path, key);
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
  function get2(obj, path) {
    return path.split(".").reduce((carry, segment) => carry[segment], obj);
  }
  function set2(obj, path, value2) {
    if (typeof path === "string")
      path = path.split(".");
    if (path.length === 1)
      obj[path[0]] = value2;
    else if (path.length === 0)
      throw error;
    else {
      if (obj[path[0]])
        return set2(obj[path[0]], path.slice(1), value2);
      else {
        obj[path[0]] = {};
        return set2(obj[path[0]], path.slice(1), value2);
      }
    }
  }
  var magics = {};
  function magic(name, callback) {
    magics[name] = callback;
  }
  function injectMagics(obj, el) {
    Object.entries(magics).forEach(([name, callback]) => {
      Object.defineProperty(obj, `$${name}`, {
        get() {
          let [utilities, cleanup22] = getElementBoundUtilities(el);
          utilities = { interceptor, ...utilities };
          onElRemoved(el, cleanup22);
          return callback(el, utilities);
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
    evaluateLater(el, expression)((value2) => result = value2, extras);
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
    if (typeof expression === "function") {
      return generateEvaluatorFromFunction(dataStack, expression);
    }
    let evaluator = generateEvaluatorFromString(dataStack, expression, el);
    return evaluator;
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
    let rightSideSafeExpression = /^[\n\s]*if.*\(.*\)/.test(expression) || /^(let|const)\s/.test(expression) ? `(async()=>{ ${expression} })()` : expression;
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
  function runIfTypeOfFunction(receiver, value2, scope2, params, el) {
    if (shouldAutoEvaluateFunctions && typeof value2 === "function") {
      let result = value2.apply(scope2, params);
      if (result instanceof Promise) {
        result.then((i) => runIfTypeOfFunction(receiver, i, scope2, params)).catch((error2) => handleError(error2, el, value2));
      } else {
        receiver(result);
      }
    } else if (typeof value2 === "object" && value2 instanceof Promise) {
      value2.then((i) => receiver(i));
    } else {
      receiver(value2);
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
  function directive(name, callback) {
    directiveHandlers[name] = callback;
    return {
      before(directive2) {
        if (!directiveHandlers[directive2]) {
          console.warn("Cannot find directive `${directive}`. `${name}` will use the default order of execution");
          return;
        }
        const pos = directiveOrder.indexOf(directive2) ?? directiveOrder.indexOf("DEFAULT");
        if (pos >= 0) {
          directiveOrder.splice(pos, 0, name);
        }
      }
    };
  }
  function directives(el, attributes, originalAttributeOverride) {
    attributes = Array.from(attributes);
    if (el._x_virtualDirectives) {
      let vAttributes = Object.entries(el._x_virtualDirectives).map(([name, value2]) => ({ name, value: value2 }));
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
    let directives22 = attributes.map(toTransformedAttributes((newName, oldName) => transformedAttributeMap[newName] = oldName)).filter(outNonAlpineAttributes).map(toParsedDirectives(transformedAttributeMap, originalAttributeOverride)).sort(byPriority);
    return directives22.map((directive2) => {
      return getDirectiveHandler(el, directive2);
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
    let cleanup22 = (callback) => cleanups.push(callback);
    let [effect32, cleanupEffect2] = elementBoundEffect(el);
    cleanups.push(cleanupEffect2);
    let utilities = {
      Alpine: alpine_default,
      effect: effect32,
      cleanup: cleanup22,
      evaluateLater: evaluateLater.bind(evaluateLater, el),
      evaluate: evaluate.bind(evaluate, el)
    };
    let doCleanup = () => cleanups.forEach((i) => i());
    return [utilities, doCleanup];
  }
  function getDirectiveHandler(el, directive2) {
    let noop = () => {
    };
    let handler4 = directiveHandlers[directive2.type] || noop;
    let [utilities, cleanup22] = getElementBoundUtilities(el);
    onAttributeRemoved(el, directive2.original, cleanup22);
    let fullHandler = () => {
      if (el._x_ignore || el._x_ignoreSelf)
        return;
      handler4.inline && handler4.inline(el, directive2, utilities);
      handler4 = handler4.bind(handler4, el, directive2, utilities);
      isDeferringHandlers ? directiveHandlerStacks.get(currentHandlerStackKey).push(handler4) : handler4();
    };
    fullHandler.runCleanups = cleanup22;
    return fullHandler;
  }
  var startingWith = (subject, replacement) => ({ name, value: value2 }) => {
    if (name.startsWith(subject))
      name = name.replace(subject, replacement);
    return { name, value: value2 };
  };
  var into = (i) => i;
  function toTransformedAttributes(callback = () => {
  }) {
    return ({ name, value: value2 }) => {
      let { name: newName, value: newValue } = attributeTransformers.reduce((carry, transform) => {
        return transform(carry);
      }, { name, value: value2 });
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
    return ({ name, value: value2 }) => {
      let typeMatch = name.match(alpineAttributeRegex());
      let valueMatch = name.match(/:([a-zA-Z0-9\-:]+)/);
      let modifiers = name.match(/\.[^.\]]+(?=[^\]]*$)/g) || [];
      let original = originalAttributeOverride || transformedAttributeMap[name] || name;
      return {
        type: typeMatch ? typeMatch[1] : null,
        value: valueMatch ? valueMatch[1] : null,
        modifiers: modifiers.map((i) => i.replace(".", "")),
        expression: value2,
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
    "radio",
    "tabs",
    "switch",
    "disclosure",
    "menu",
    "listbox",
    "combobox",
    "bind",
    "init",
    "for",
    "mask",
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
  function dispatch(el, name, detail = {}) {
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
  function start() {
    if (!document.body)
      warn("Unable to initialize. Trying to load Alpine before `<body>` is available. Did you forget to add `defer` in Alpine's `<script>` tag?");
    dispatch(document, "alpine:init");
    dispatch(document, "alpine:initializing");
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
    dispatch(document, "alpine:initialized");
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
  function initTree(el, walker = walk, intercept = () => {
  }) {
    deferHandlingDirectives(() => {
      walker(el, (el2, skip) => {
        intercept(el2, skip);
        initInterceptors2.forEach((i) => i(el2, skip));
        directives(el2, el2.attributes).forEach((handle) => handle());
        el2._x_ignore && skip();
      });
    });
  }
  function destroyTree(root) {
    walk(root, (el) => cleanupAttributes(el));
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
  function setClasses(el, value2) {
    if (Array.isArray(value2)) {
      return setClassesFromString(el, value2.join(" "));
    } else if (typeof value2 === "object" && value2 !== null) {
      return setClassesFromObject(el, value2);
    } else if (typeof value2 === "function") {
      return setClasses(el, value2());
    }
    return setClassesFromString(el, value2);
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
  function setStyles(el, value2) {
    if (typeof value2 === "object" && value2 !== null) {
      return setStylesFromObject(el, value2);
    }
    return setStylesFromString(el, value2);
  }
  function setStylesFromObject(el, value2) {
    let previousStyles = {};
    Object.entries(value2).forEach(([key, value22]) => {
      previousStyles[key] = el.style[key];
      if (!key.startsWith("--")) {
        key = kebabCase(key);
      }
      el.style.setProperty(key, value22);
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
  function setStylesFromString(el, value2) {
    let cache = el.getAttribute("style", value2);
    el.setAttribute("style", value2);
    return () => {
      el.setAttribute("style", cache || "");
    };
  }
  function kebabCase(subject) {
    return subject.replace(/([a-z])([A-Z])/g, "$1-$2").toLowerCase();
  }
  function once(callback, fallback = () => {
  }) {
    let called = false;
    return function() {
      if (!called) {
        called = true;
        callback.apply(this, arguments);
      } else {
        fallback.apply(this, arguments);
      }
    };
  }
  directive("transition", (el, { value: value2, modifiers, expression }, { evaluate: evaluate2 }) => {
    if (typeof expression === "function")
      expression = evaluate2(expression);
    if (!expression) {
      registerTransitionsFromHelper(el, modifiers, value2);
    } else {
      registerTransitionsFromClassString(el, expression, value2);
    }
  });
  function registerTransitionsFromClassString(el, classString, stage) {
    registerTransitionObject(el, setClasses, "");
    let directiveStorageMap = {
      enter: (classes) => {
        el._x_transition.enter.during = classes;
      },
      "enter-start": (classes) => {
        el._x_transition.enter.start = classes;
      },
      "enter-end": (classes) => {
        el._x_transition.enter.end = classes;
      },
      leave: (classes) => {
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
    let delay = modifierValue(modifiers, "delay", 0);
    let origin = modifierValue(modifiers, "origin", "center");
    let property2 = "opacity, transform";
    let durationIn = modifierValue(modifiers, "duration", 150) / 1e3;
    let durationOut = modifierValue(modifiers, "duration", 75) / 1e3;
    let easing = `cubic-bezier(0.4, 0.0, 0.2, 1)`;
    if (transitioningIn) {
      el._x_transition.enter.during = {
        transformOrigin: origin,
        transitionDelay: delay,
        transitionProperty: property2,
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
        transitionDelay: delay,
        transitionProperty: property2,
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
  window.Element.prototype._x_toggleAndCascadeWithTransitions = function(el, value2, show, hide) {
    const nextTick2 = document.visibilityState === "visible" ? requestAnimationFrame : setTimeout;
    let clickAwayCompatibleShow = () => nextTick2(show);
    if (value2) {
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
  function modifierValue(modifiers, key, fallback) {
    if (modifiers.indexOf(key) === -1)
      return fallback;
    const rawValue = modifiers[modifiers.indexOf(key) + 1];
    if (!rawValue)
      return fallback;
    if (key === "scale") {
      if (isNaN(rawValue))
        return fallback;
    }
    if (key === "duration") {
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
  function skipDuringClone(callback, fallback = () => {
  }) {
    return (...args) => isCloning ? fallback(...args) : callback(...args);
  }
  function onlyDuringClone(callback) {
    return (...args) => isCloning && callback(...args);
  }
  function clone(oldEl, newEl) {
    if (!newEl._x_dataStack)
      newEl._x_dataStack = oldEl._x_dataStack;
    isCloning = true;
    dontRegisterReactiveSideEffects(() => {
      cloneTree(newEl);
    });
    isCloning = false;
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
    let cache = effect2;
    overrideEffect((callback2, el) => {
      let storedEffect = cache(callback2);
      release(storedEffect);
      return () => {
      };
    });
    callback();
    overrideEffect(cache);
  }
  function bind(el, name, value2, modifiers = []) {
    if (!el._x_bindings)
      el._x_bindings = reactive2({});
    el._x_bindings[name] = value2;
    name = modifiers.includes("camel") ? camelCase(name) : name;
    switch (name) {
      case "value":
        bindInputValue(el, value2);
        break;
      case "style":
        bindStyles(el, value2);
        break;
      case "class":
        bindClasses(el, value2);
        break;
      default:
        bindAttribute(el, name, value2);
        break;
    }
  }
  function bindInputValue(el, value2) {
    if (el.type === "radio") {
      if (el.attributes.value === void 0) {
        el.value = value2;
      }
      if (window.fromModel) {
        el.checked = checkedAttrLooseCompare(el.value, value2);
      }
    } else if (el.type === "checkbox") {
      if (Number.isInteger(value2)) {
        el.value = value2;
      } else if (!Number.isInteger(value2) && !Array.isArray(value2) && typeof value2 !== "boolean" && ![null, void 0].includes(value2)) {
        el.value = String(value2);
      } else {
        if (Array.isArray(value2)) {
          el.checked = value2.some((val) => checkedAttrLooseCompare(val, el.value));
        } else {
          el.checked = !!value2;
        }
      }
    } else if (el.tagName === "SELECT") {
      updateSelect(el, value2);
    } else {
      if (el.value === value2)
        return;
      el.value = value2;
    }
  }
  function bindClasses(el, value2) {
    if (el._x_undoAddedClasses)
      el._x_undoAddedClasses();
    el._x_undoAddedClasses = setClasses(el, value2);
  }
  function bindStyles(el, value2) {
    if (el._x_undoAddedStyles)
      el._x_undoAddedStyles();
    el._x_undoAddedStyles = setStyles(el, value2);
  }
  function bindAttribute(el, name, value2) {
    if ([null, void 0, false].includes(value2) && attributeShouldntBePreservedIfFalsy(name)) {
      el.removeAttribute(name);
    } else {
      if (isBooleanAttr2(name))
        value2 = name;
      setIfChanged(el, name, value2);
    }
  }
  function setIfChanged(el, attrName, value2) {
    if (el.getAttribute(attrName) != value2) {
      el.setAttribute(attrName, value2);
    }
  }
  function updateSelect(el, value2) {
    const arrayWrappedValue = [].concat(value2).map((value22) => {
      return value22 + "";
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
  function isBooleanAttr2(attrName) {
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
  function getBinding(el, name, fallback) {
    if (el._x_bindings && el._x_bindings[name] !== void 0)
      return el._x_bindings[name];
    return getAttributeBinding(el, name, fallback);
  }
  function extractProp(el, name, fallback, extract = true) {
    if (el._x_bindings && el._x_bindings[name] !== void 0)
      return el._x_bindings[name];
    if (el._x_inlineBindings && el._x_inlineBindings[name] !== void 0) {
      let binding = el._x_inlineBindings[name];
      binding.extract = extract;
      return dontAutoEvaluateFunctions(() => {
        return evaluate(el, binding.expression);
      });
    }
    return getAttributeBinding(el, name, fallback);
  }
  function getAttributeBinding(el, name, fallback) {
    let attr = el.getAttribute(name);
    if (attr === null)
      return typeof fallback === "function" ? fallback() : fallback;
    if (attr === "")
      return true;
    if (isBooleanAttr2(name)) {
      return !![name, "true"].includes(attr);
    }
    return attr;
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
    let outerHash, innerHash;
    let reference = effect2(() => {
      let outer, inner;
      if (firstRun) {
        outer = outerGet();
        innerSet(outer);
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
          outerSet(inner);
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
    callback(alpine_default);
  }
  var stores = {};
  var isReactive = false;
  function store(name, value2) {
    if (!isReactive) {
      stores = reactive2(stores);
      isReactive = true;
    }
    if (value2 === void 0) {
      return stores[name];
    }
    stores[name] = value2;
    if (typeof value2 === "object" && value2 !== null && value2.hasOwnProperty("init") && typeof value2.init === "function") {
      stores[name].init();
    }
    initInterceptors(stores[name]);
  }
  function getStores() {
    return stores;
  }
  var binds = {};
  function bind2(name, bindings) {
    let getBindings = typeof bindings !== "function" ? () => bindings : bindings;
    if (name instanceof Element) {
      applyBindingsObject(name, getBindings());
    } else {
      binds[name] = getBindings;
    }
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
    let attributes = Object.entries(obj).map(([name, value2]) => ({ name, value: value2 }));
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
  var Alpine2 = {
    get reactive() {
      return reactive2;
    },
    get release() {
      return release;
    },
    get effect() {
      return effect2;
    },
    get raw() {
      return raw;
    },
    version: "3.10.5",
    flushAndStopDeferringMutations,
    dontAutoEvaluateFunctions,
    disableEffectScheduling,
    startObservingMutations,
    stopObservingMutations,
    setReactivityEngine,
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
    closestRoot,
    destroyTree,
    interceptor,
    transition,
    setStyles,
    mutateDom,
    directive,
    entangle,
    throttle,
    debounce,
    evaluate,
    initTree,
    nextTick,
    prefixed: prefix,
    prefix: setPrefix,
    plugin,
    magic,
    store,
    start,
    clone,
    bound: getBinding,
    $data: scope,
    walk,
    data,
    bind: bind2
  };
  var alpine_default = Alpine2;
  function makeMap2(str, expectsLowerCase) {
    const map = /* @__PURE__ */ Object.create(null);
    const list = str.split(",");
    for (let i = 0; i < list.length; i++) {
      map[list[i]] = true;
    }
    return expectsLowerCase ? (val) => !!map[val.toLowerCase()] : (val) => !!map[val];
  }
  var specialBooleanAttrs2 = `itemscope,allowfullscreen,formnovalidate,ismap,nomodule,novalidate,readonly`;
  var isBooleanAttr22 = /* @__PURE__ */ makeMap2(specialBooleanAttrs2 + `,async,autofocus,autoplay,controls,default,defer,disabled,hidden,loop,open,required,reversed,scoped,seamless,checked,muted,multiple,selected`);
  var EMPTY_OBJ2 = false ? Object.freeze({}) : {};
  var EMPTY_ARR2 = false ? Object.freeze([]) : [];
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
  var hasChanged2 = (value2, oldValue) => value2 !== oldValue && (value2 === value2 || oldValue === oldValue);
  var targetMap2 = /* @__PURE__ */ new WeakMap();
  var effectStack = [];
  var activeEffect2;
  var ITERATE_KEY2 = Symbol(false ? "iterate" : "");
  var MAP_KEY_ITERATE_KEY2 = Symbol(false ? "Map key iterate" : "");
  function isEffect(fn) {
    return fn && fn._isEffect === true;
  }
  function effect22(fn, options = EMPTY_OBJ2) {
    if (isEffect(fn)) {
      fn = fn.raw;
    }
    const effect32 = createReactiveEffect(fn, options);
    if (!options.lazy) {
      effect32();
    }
    return effect32;
  }
  function stop2(effect32) {
    if (effect32.active) {
      cleanup(effect32);
      if (effect32.options.onStop) {
        effect32.options.onStop();
      }
      effect32.active = false;
    }
  }
  var uid = 0;
  function createReactiveEffect(fn, options) {
    const effect32 = function reactiveEffect() {
      if (!effect32.active) {
        return fn();
      }
      if (!effectStack.includes(effect32)) {
        cleanup(effect32);
        try {
          enableTracking2();
          effectStack.push(effect32);
          activeEffect2 = effect32;
          return fn();
        } finally {
          effectStack.pop();
          resetTracking2();
          activeEffect2 = effectStack[effectStack.length - 1];
        }
      }
    };
    effect32.id = uid++;
    effect32.allowRecurse = !!options.allowRecurse;
    effect32._isEffect = true;
    effect32.active = true;
    effect32.raw = fn;
    effect32.deps = [];
    effect32.options = options;
    return effect32;
  }
  function cleanup(effect32) {
    const { deps } = effect32;
    if (deps.length) {
      for (let i = 0; i < deps.length; i++) {
        deps[i].delete(effect32);
      }
      deps.length = 0;
    }
  }
  var shouldTrack2 = true;
  var trackStack2 = [];
  function pauseTracking2() {
    trackStack2.push(shouldTrack2);
    shouldTrack2 = false;
  }
  function enableTracking2() {
    trackStack2.push(shouldTrack2);
    shouldTrack2 = true;
  }
  function resetTracking2() {
    const last = trackStack2.pop();
    shouldTrack2 = last === void 0 ? true : last;
  }
  function track2(target, type, key) {
    if (!shouldTrack2 || activeEffect2 === void 0) {
      return;
    }
    let depsMap = targetMap2.get(target);
    if (!depsMap) {
      targetMap2.set(target, depsMap = /* @__PURE__ */ new Map());
    }
    let dep = depsMap.get(key);
    if (!dep) {
      depsMap.set(key, dep = /* @__PURE__ */ new Set());
    }
    if (!dep.has(activeEffect2)) {
      dep.add(activeEffect2);
      activeEffect2.deps.push(dep);
      if (false) {
        activeEffect2.options.onTrack({
          effect: activeEffect2,
          target,
          type,
          key
        });
      }
    }
  }
  function trigger3(target, type, key, newValue, oldValue, oldTarget) {
    const depsMap = targetMap2.get(target);
    if (!depsMap) {
      return;
    }
    const effects = /* @__PURE__ */ new Set();
    const add22 = (effectsToAdd) => {
      if (effectsToAdd) {
        effectsToAdd.forEach((effect32) => {
          if (effect32 !== activeEffect2 || effect32.allowRecurse) {
            effects.add(effect32);
          }
        });
      }
    };
    if (type === "clear") {
      depsMap.forEach(add22);
    } else if (key === "length" && isArray3(target)) {
      depsMap.forEach((dep, key2) => {
        if (key2 === "length" || key2 >= newValue) {
          add22(dep);
        }
      });
    } else {
      if (key !== void 0) {
        add22(depsMap.get(key));
      }
      switch (type) {
        case "add":
          if (!isArray3(target)) {
            add22(depsMap.get(ITERATE_KEY2));
            if (isMap2(target)) {
              add22(depsMap.get(MAP_KEY_ITERATE_KEY2));
            }
          } else if (isIntegerKey2(key)) {
            add22(depsMap.get("length"));
          }
          break;
        case "delete":
          if (!isArray3(target)) {
            add22(depsMap.get(ITERATE_KEY2));
            if (isMap2(target)) {
              add22(depsMap.get(MAP_KEY_ITERATE_KEY2));
            }
          }
          break;
        case "set":
          if (isMap2(target)) {
            add22(depsMap.get(ITERATE_KEY2));
          }
          break;
      }
    }
    const run = (effect32) => {
      if (false) {
        effect32.options.onTrigger({
          effect: effect32,
          target,
          key,
          type,
          newValue,
          oldValue,
          oldTarget
        });
      }
      if (effect32.options.scheduler) {
        effect32.options.scheduler(effect32);
      } else {
        effect32();
      }
    };
    effects.forEach(run);
  }
  var isNonTrackableKeys2 = /* @__PURE__ */ makeMap2(`__proto__,__v_isRef,__isVue`);
  var builtInSymbols2 = new Set(Object.getOwnPropertyNames(Symbol).map((key) => Symbol[key]).filter(isSymbol2));
  var get22 = /* @__PURE__ */ createGetter2();
  var shallowGet = /* @__PURE__ */ createGetter2(false, true);
  var readonlyGet2 = /* @__PURE__ */ createGetter2(true);
  var shallowReadonlyGet = /* @__PURE__ */ createGetter2(true, true);
  var arrayInstrumentations2 = {};
  ["includes", "indexOf", "lastIndexOf"].forEach((key) => {
    const method = Array.prototype[key];
    arrayInstrumentations2[key] = function(...args) {
      const arr = toRaw2(this);
      for (let i = 0, l = this.length; i < l; i++) {
        track2(arr, "get", i + "");
      }
      const res = method.apply(arr, args);
      if (res === -1 || res === false) {
        return method.apply(arr, args.map(toRaw2));
      } else {
        return res;
      }
    };
  });
  ["push", "pop", "shift", "unshift", "splice"].forEach((key) => {
    const method = Array.prototype[key];
    arrayInstrumentations2[key] = function(...args) {
      pauseTracking2();
      const res = method.apply(this, args);
      resetTracking2();
      return res;
    };
  });
  function createGetter2(isReadonly2 = false, shallow = false) {
    return function get3(target, key, receiver) {
      if (key === "__v_isReactive") {
        return !isReadonly2;
      } else if (key === "__v_isReadonly") {
        return isReadonly2;
      } else if (key === "__v_raw" && receiver === (isReadonly2 ? shallow ? shallowReadonlyMap2 : readonlyMap2 : shallow ? shallowReactiveMap2 : reactiveMap2).get(target)) {
        return target;
      }
      const targetIsArray = isArray3(target);
      if (!isReadonly2 && targetIsArray && hasOwn2(arrayInstrumentations2, key)) {
        return Reflect.get(arrayInstrumentations2, key, receiver);
      }
      const res = Reflect.get(target, key, receiver);
      if (isSymbol2(key) ? builtInSymbols2.has(key) : isNonTrackableKeys2(key)) {
        return res;
      }
      if (!isReadonly2) {
        track2(target, "get", key);
      }
      if (shallow) {
        return res;
      }
      if (isRef2(res)) {
        const shouldUnwrap = !targetIsArray || !isIntegerKey2(key);
        return shouldUnwrap ? res.value : res;
      }
      if (isObject3(res)) {
        return isReadonly2 ? readonly2(res) : reactive22(res);
      }
      return res;
    };
  }
  var set22 = /* @__PURE__ */ createSetter2();
  var shallowSet = /* @__PURE__ */ createSetter2(true);
  function createSetter2(shallow = false) {
    return function set3(target, key, value2, receiver) {
      let oldValue = target[key];
      if (!shallow) {
        value2 = toRaw2(value2);
        oldValue = toRaw2(oldValue);
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
    get: get22,
    set: set22,
    deleteProperty: deleteProperty2,
    has: has2,
    ownKeys: ownKeys2
  };
  var readonlyHandlers2 = {
    get: readonlyGet2,
    set(target, key) {
      if (false) {
        console.warn(`Set operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    },
    deleteProperty(target, key) {
      if (false) {
        console.warn(`Delete operation on key "${String(key)}" failed: target is readonly.`, target);
      }
      return true;
    }
  };
  var shallowReactiveHandlers = extend2({}, mutableHandlers2, {
    get: shallowGet,
    set: shallowSet
  });
  var shallowReadonlyHandlers = extend2({}, readonlyHandlers2, {
    get: shallowReadonlyGet
  });
  var toReactive2 = (value2) => isObject3(value2) ? reactive22(value2) : value2;
  var toReadonly2 = (value2) => isObject3(value2) ? readonly2(value2) : value2;
  var toShallow2 = (value2) => value2;
  var getProto2 = (v) => Reflect.getPrototypeOf(v);
  function get$12(target, key, isReadonly2 = false, isShallow2 = false) {
    target = target["__v_raw"];
    const rawTarget = toRaw2(target);
    const rawKey = toRaw2(key);
    if (key !== rawKey) {
      !isReadonly2 && track2(rawTarget, "get", key);
    }
    !isReadonly2 && track2(rawTarget, "get", rawKey);
    const { has: has22 } = getProto2(rawTarget);
    const wrap = isShallow2 ? toShallow2 : isReadonly2 ? toReadonly2 : toReactive2;
    if (has22.call(rawTarget, key)) {
      return wrap(target.get(key));
    } else if (has22.call(rawTarget, rawKey)) {
      return wrap(target.get(rawKey));
    } else if (target !== rawTarget) {
      target.get(key);
    }
  }
  function has$12(key, isReadonly2 = false) {
    const target = this["__v_raw"];
    const rawTarget = toRaw2(target);
    const rawKey = toRaw2(key);
    if (key !== rawKey) {
      !isReadonly2 && track2(rawTarget, "has", key);
    }
    !isReadonly2 && track2(rawTarget, "has", rawKey);
    return key === rawKey ? target.has(key) : target.has(key) || target.has(rawKey);
  }
  function size2(target, isReadonly2 = false) {
    target = target["__v_raw"];
    !isReadonly2 && track2(toRaw2(target), "iterate", ITERATE_KEY2);
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
    const { has: has22, get: get3 } = getProto2(target);
    let hadKey = has22.call(target, key);
    if (!hadKey) {
      key = toRaw2(key);
      hadKey = has22.call(target, key);
    } else if (false) {
      checkIdentityKeys(target, has22, key);
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
    const { has: has22, get: get3 } = getProto2(target);
    let hadKey = has22.call(target, key);
    if (!hadKey) {
      key = toRaw2(key);
      hadKey = has22.call(target, key);
    } else if (false) {
      checkIdentityKeys(target, has22, key);
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
    const oldTarget = false ? isMap2(target) ? new Map(target) : new Set(target) : void 0;
    const result = target.clear();
    if (hadItems) {
      trigger3(target, "clear", void 0, void 0, oldTarget);
    }
    return result;
  }
  function createForEach2(isReadonly2, isShallow2) {
    return function forEach(callback, thisArg) {
      const observed = this;
      const target = observed["__v_raw"];
      const rawTarget = toRaw2(target);
      const wrap = isShallow2 ? toShallow2 : isReadonly2 ? toReadonly2 : toReactive2;
      !isReadonly2 && track2(rawTarget, "iterate", ITERATE_KEY2);
      return target.forEach((value2, key) => {
        return callback.call(thisArg, wrap(value2), wrap(key), observed);
      });
    };
  }
  function createIterableMethod2(method, isReadonly2, isShallow2) {
    return function(...args) {
      const target = this["__v_raw"];
      const rawTarget = toRaw2(target);
      const targetIsMap = isMap2(rawTarget);
      const isPair = method === "entries" || method === Symbol.iterator && targetIsMap;
      const isKeyOnly = method === "keys" && targetIsMap;
      const innerIterator = target[method](...args);
      const wrap = isShallow2 ? toShallow2 : isReadonly2 ? toReadonly2 : toReactive2;
      !isReadonly2 && track2(rawTarget, "iterate", isKeyOnly ? MAP_KEY_ITERATE_KEY2 : ITERATE_KEY2);
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
      if (false) {
        const key = args[0] ? `on key "${args[0]}" ` : ``;
        console.warn(`${capitalize2(type)} operation ${key}failed: target is readonly.`, toRaw2(this));
      }
      return type === "delete" ? false : this;
    };
  }
  var mutableInstrumentations2 = {
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
  var shallowInstrumentations2 = {
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
  var readonlyInstrumentations2 = {
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
  var shallowReadonlyInstrumentations2 = {
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
  var iteratorMethods = ["keys", "values", "entries", Symbol.iterator];
  iteratorMethods.forEach((method) => {
    mutableInstrumentations2[method] = createIterableMethod2(method, false, false);
    readonlyInstrumentations2[method] = createIterableMethod2(method, true, false);
    shallowInstrumentations2[method] = createIterableMethod2(method, false, true);
    shallowReadonlyInstrumentations2[method] = createIterableMethod2(method, true, true);
  });
  function createInstrumentationGetter2(isReadonly2, shallow) {
    const instrumentations = shallow ? isReadonly2 ? shallowReadonlyInstrumentations2 : shallowInstrumentations2 : isReadonly2 ? readonlyInstrumentations2 : mutableInstrumentations2;
    return (target, key, receiver) => {
      if (key === "__v_isReactive") {
        return !isReadonly2;
      } else if (key === "__v_isReadonly") {
        return isReadonly2;
      } else if (key === "__v_raw") {
        return target;
      }
      return Reflect.get(hasOwn2(instrumentations, key) && key in target ? instrumentations : target, key, receiver);
    };
  }
  var mutableCollectionHandlers2 = {
    get: createInstrumentationGetter2(false, false)
  };
  var shallowCollectionHandlers = {
    get: createInstrumentationGetter2(false, true)
  };
  var readonlyCollectionHandlers2 = {
    get: createInstrumentationGetter2(true, false)
  };
  var shallowReadonlyCollectionHandlers = {
    get: createInstrumentationGetter2(true, true)
  };
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
  function reactive22(target) {
    if (target && target["__v_isReadonly"]) {
      return target;
    }
    return createReactiveObject2(target, false, mutableHandlers2, mutableCollectionHandlers2, reactiveMap2);
  }
  function readonly2(target) {
    return createReactiveObject2(target, true, readonlyHandlers2, readonlyCollectionHandlers2, readonlyMap2);
  }
  function createReactiveObject2(target, isReadonly2, baseHandlers, collectionHandlers, proxyMap) {
    if (!isObject3(target)) {
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
    const targetType = getTargetType2(target);
    if (targetType === 0) {
      return target;
    }
    const proxy = new Proxy(target, targetType === 2 ? collectionHandlers : baseHandlers);
    proxyMap.set(target, proxy);
    return proxy;
  }
  function toRaw2(observed) {
    return observed && toRaw2(observed["__v_raw"]) || observed;
  }
  function isRef2(r) {
    return Boolean(r && r.__v_isRef === true);
  }
  magic("nextTick", () => nextTick);
  magic("dispatch", (el) => dispatch.bind(dispatch, el));
  magic("watch", (el, { evaluateLater: evaluateLater2, effect: effect32 }) => (key, callback) => {
    let evaluate2 = evaluateLater2(key);
    let firstTime = true;
    let oldValue;
    let effectReference = effect32(() => evaluate2((value2) => {
      JSON.stringify(value2);
      if (!firstTime) {
        queueMicrotask(() => {
          callback(value2, oldValue);
          oldValue = value2;
        });
      } else {
        oldValue = value2;
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
  directive("modelable", (el, { expression }, { effect: effect32, evaluateLater: evaluateLater2, cleanup: cleanup22 }) => {
    let func = evaluateLater2(expression);
    let innerGet = () => {
      let result;
      func((i) => result = i);
      return result;
    };
    let evaluateInnerSet = evaluateLater2(`${expression} = __placeholder`);
    let innerSet = (val) => evaluateInnerSet(() => {
    }, { scope: { __placeholder: val } });
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
        set(value2) {
          outerSet(value2);
        }
      }, {
        get() {
          return innerGet();
        },
        set(value2) {
          innerSet(value2);
        }
      });
      cleanup22(releaseEntanglement);
    });
  });
  var teleportContainerDuringClone = document.createElement("div");
  directive("teleport", (el, { modifiers, expression }, { cleanup: cleanup22 }) => {
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
    cleanup22(() => clone2.remove());
  });
  var handler = () => {
  };
  handler.inline = (el, { modifiers }, { cleanup: cleanup22 }) => {
    modifiers.includes("self") ? el._x_ignoreSelf = true : el._x_ignore = true;
    cleanup22(() => {
      modifiers.includes("self") ? delete el._x_ignoreSelf : delete el._x_ignore;
    });
  };
  directive("ignore", handler);
  directive("effect", (el, { expression }, { effect: effect32 }) => effect32(evaluateLater(el, expression)));
  function on2(el, event, modifiers, callback) {
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
    if (modifiers.includes("debounce")) {
      let nextModifier = modifiers[modifiers.indexOf("debounce") + 1] || "invalid-wait";
      let wait = isNumeric(nextModifier.split("ms")[0]) ? Number(nextModifier.split("ms")[0]) : 250;
      handler4 = debounce(handler4, wait);
    }
    if (modifiers.includes("throttle")) {
      let nextModifier = modifiers[modifiers.indexOf("throttle") + 1] || "invalid-wait";
      let wait = isNumeric(nextModifier.split("ms")[0]) ? Number(nextModifier.split("ms")[0]) : 250;
      handler4 = throttle(handler4, wait);
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
    if ([" ", "_"].includes(subject))
      return subject;
    return subject.replace(/([a-z])([A-Z])/g, "$1-$2").replace(/[_\s]/, "-").toLowerCase();
  }
  function isKeyEvent(event) {
    return ["keydown", "keyup"].includes(event);
  }
  function isListeningForASpecificKeyThatHasntBeenPressed(e, modifiers) {
    let keyModifiers = modifiers.filter((i) => {
      return !["window", "document", "prevent", "stop", "once"].includes(i);
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
      ctrl: "control",
      slash: "/",
      space: " ",
      spacebar: " ",
      cmd: "meta",
      esc: "escape",
      up: "arrow-up",
      down: "arrow-down",
      left: "arrow-left",
      right: "arrow-right",
      period: ".",
      equal: "=",
      minus: "-",
      underscore: "_"
    };
    modifierToKeyMap[key] = key;
    return Object.keys(modifierToKeyMap).map((modifier) => {
      if (modifierToKeyMap[modifier] === key)
        return modifier;
    }).filter((modifier) => modifier);
  }
  directive("model", (el, { modifiers, expression }, { effect: effect32, cleanup: cleanup22 }) => {
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
      evaluateGet((value2) => result = value2);
      return isGetterSetter(result) ? result.get() : result;
    };
    let setValue = (value2) => {
      let result;
      evaluateGet((value22) => result = value22);
      if (isGetterSetter(result)) {
        result.set(value2);
      } else {
        evaluateSet(() => {
        }, {
          scope: { __placeholder: value2 }
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
    let removeListener = on2(el, event, modifiers, (e) => {
      setValue(getInputValue(el, modifiers, e, getValue()));
    });
    if (!el._x_removeModelListeners)
      el._x_removeModelListeners = {};
    el._x_removeModelListeners["default"] = removeListener;
    cleanup22(() => el._x_removeModelListeners["default"]());
    if (el.form) {
      let removeResetListener = on2(el.form, "reset", [], (e) => {
        nextTick(() => el._x_model && el._x_model.set(el.value));
      });
      cleanup22(() => removeResetListener());
    }
    el._x_model = {
      get() {
        return getValue();
      },
      set(value2) {
        setValue(value2);
      }
    };
    el._x_forceModelUpdate = (value2) => {
      value2 = value2 === void 0 ? getValue() : value2;
      if (value2 === void 0 && typeof expression === "string" && expression.match(/\./))
        value2 = "";
      window.fromModel = true;
      mutateDom(() => bind(el, "value", value2));
      delete window.fromModel;
    };
    effect32(() => {
      let value2 = getValue();
      if (modifiers.includes("unintrusive") && document.activeElement.isSameNode(el))
        return;
      el._x_forceModelUpdate(value2);
    });
  });
  function getInputValue(el, modifiers, event, currentValue) {
    return mutateDom(() => {
      if (event instanceof CustomEvent && event.detail !== void 0) {
        return event.detail || event.target.value;
      } else if (el.type === "checkbox") {
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
  function isGetterSetter(value2) {
    return value2 !== null && typeof value2 === "object" && typeof value2.get === "function" && typeof value2.set === "function";
  }
  directive("cloak", (el) => queueMicrotask(() => mutateDom(() => el.removeAttribute(prefix("cloak")))));
  addInitSelector(() => `[${prefix("init")}]`);
  directive("init", skipDuringClone((el, { expression }, { evaluate: evaluate2 }) => {
    if (typeof expression === "string") {
      return !!expression.trim() && evaluate2(expression, {}, false);
    }
    return evaluate2(expression, {}, false);
  }));
  directive("text", (el, { expression }, { effect: effect32, evaluateLater: evaluateLater2 }) => {
    let evaluate2 = evaluateLater2(expression);
    effect32(() => {
      evaluate2((value2) => {
        mutateDom(() => {
          el.textContent = value2;
        });
      });
    });
  });
  directive("html", (el, { expression }, { effect: effect32, evaluateLater: evaluateLater2 }) => {
    let evaluate2 = evaluateLater2(expression);
    effect32(() => {
      evaluate2((value2) => {
        mutateDom(() => {
          el.innerHTML = value2;
          el._x_ignoreSelf = true;
          initTree(el);
          delete el._x_ignoreSelf;
        });
      });
    });
  });
  mapAttributes(startingWith(":", into(prefix("bind:"))));
  var handler2 = (el, { value: value2, modifiers, expression, original }, { effect: effect32 }) => {
    if (!value2) {
      let bindingProviders = {};
      injectBindingProviders(bindingProviders);
      let getBindings = evaluateLater(el, expression);
      getBindings((bindings) => {
        applyBindingsObject(el, bindings, original);
      }, { scope: bindingProviders });
      return;
    }
    if (value2 === "key")
      return storeKeyForXFor(el, expression);
    if (el._x_inlineBindings && el._x_inlineBindings[value2] && el._x_inlineBindings[value2].extract) {
      return;
    }
    let evaluate2 = evaluateLater(el, expression);
    effect32(() => evaluate2((result) => {
      if (result === void 0 && typeof expression === "string" && expression.match(/\./)) {
        result = "";
      }
      mutateDom(() => bind(el, value2, result, modifiers));
    }));
  };
  handler2.inline = (el, { value: value2, modifiers, expression }) => {
    if (!value2)
      return;
    if (!el._x_inlineBindings)
      el._x_inlineBindings = {};
    el._x_inlineBindings[value2] = { expression, extract: false };
  };
  directive("bind", handler2);
  function storeKeyForXFor(el, expression) {
    el._x_keyExpression = expression;
  }
  addRootSelector(() => `[${prefix("data")}]`);
  directive("data", skipDuringClone((el, { expression }, { cleanup: cleanup22 }) => {
    expression = expression === "" ? "{}" : expression;
    let magicContext = {};
    injectMagics(magicContext, el);
    let dataProviderContext = {};
    injectDataProviders(dataProviderContext, magicContext);
    let data2 = evaluate(el, expression, { scope: dataProviderContext });
    if (data2 === void 0)
      data2 = {};
    injectMagics(data2, el);
    let reactiveData = reactive2(data2);
    initInterceptors(reactiveData);
    let undo = addScopeToNode(el, reactiveData);
    reactiveData["init"] && evaluate(el, reactiveData["init"]);
    cleanup22(() => {
      reactiveData["destroy"] && evaluate(el, reactiveData["destroy"]);
      undo();
    });
  }));
  directive("show", (el, { modifiers, expression }, { effect: effect32 }) => {
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
    let toggle = once((value2) => value2 ? show() : hide(), (value2) => {
      if (typeof el._x_toggleAndCascadeWithTransitions === "function") {
        el._x_toggleAndCascadeWithTransitions(el, value2, show, hide);
      } else {
        value2 ? clickAwayCompatibleShow() : hide();
      }
    });
    let oldValue;
    let firstTime = true;
    effect32(() => evaluate2((value2) => {
      if (!firstTime && value2 === oldValue)
        return;
      if (modifiers.includes("immediate"))
        value2 ? clickAwayCompatibleShow() : hide();
      toggle(value2);
      oldValue = value2;
      firstTime = false;
    }));
  });
  directive("for", (el, { expression }, { effect: effect32, cleanup: cleanup22 }) => {
    let iteratorNames = parseForExpression(expression);
    let evaluateItems = evaluateLater(el, iteratorNames.items);
    let evaluateKey = evaluateLater(el, el._x_keyExpression || "index");
    el._x_prevKeys = [];
    el._x_lookup = {};
    effect32(() => loop(el, iteratorNames, evaluateItems, evaluateKey));
    cleanup22(() => {
      Object.values(el._x_lookup).forEach((el2) => el2.remove());
      delete el._x_prevKeys;
      delete el._x_lookup;
    });
  });
  function loop(el, iteratorNames, evaluateItems, evaluateKey) {
    let isObject22 = (i) => typeof i === "object" && !Array.isArray(i);
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
      if (isObject22(items)) {
        items = Object.entries(items).map(([key, value2]) => {
          let scope2 = getIterationScopeVariables(iteratorNames, value2, key, items);
          evaluateKey((value22) => keys.push(value22), { scope: { index: key, ...scope2 } });
          scopes.push(scope2);
        });
      } else {
        for (let i = 0; i < items.length; i++) {
          let scope2 = getIterationScopeVariables(iteratorNames, items[i], i, items);
          evaluateKey((value2) => keys.push(value2), { scope: { index: i, ...scope2 } });
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
        if (!!lookup[key]._x_forCleanup) {
          lookup[key]._x_forCleanup();
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
          elForSpot.after(marker);
          elInSpot.after(elForSpot);
          elForSpot._x_currentIfEl && elForSpot.after(elForSpot._x_currentIfEl);
          marker.before(elInSpot);
          elInSpot._x_currentIfEl && elInSpot.after(elInSpot._x_currentIfEl);
          marker.remove();
        });
        refreshScope(elForSpot, scopes[keys.indexOf(keyForSpot)]);
      }
      for (let i = 0; i < adds.length; i++) {
        let [lastKey2, index] = adds[i];
        let lastEl = lastKey2 === "template" ? templateEl : lookup[lastKey2];
        if (lastEl._x_currentIfEl)
          lastEl = lastEl._x_currentIfEl;
        let scope2 = scopes[index];
        let key = keys[index];
        let clone2 = document.importNode(templateEl.content, true).firstElementChild;
        addScopeToNode(clone2, reactive2(scope2), templateEl);
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
        refreshScope(lookup[sames[i]], scopes[keys.indexOf(sames[i])]);
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
  handler3.inline = (el, { expression }, { cleanup: cleanup22 }) => {
    let root = closestRoot(el);
    if (!root._x_refs)
      root._x_refs = {};
    root._x_refs[expression] = el;
    cleanup22(() => delete root._x_refs[expression]);
  };
  directive("ref", handler3);
  directive("if", (el, { expression }, { effect: effect32, cleanup: cleanup22 }) => {
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
    effect32(() => evaluate2((value2) => {
      value2 ? show() : hide();
    }));
    cleanup22(() => el._x_undoIf && el._x_undoIf());
  });
  directive("id", (el, { expression }, { evaluate: evaluate2 }) => {
    let names = evaluate2(expression);
    names.forEach((name) => setIdRoot(el, name));
  });
  mapAttributes(startingWith("@", into(prefix("on:"))));
  directive("on", skipDuringClone((el, { value: value2, modifiers, expression }, { cleanup: cleanup22 }) => {
    let evaluate2 = expression ? evaluateLater(el, expression) : () => {
    };
    if (el.tagName.toLowerCase() === "template") {
      if (!el._x_forwardEvents)
        el._x_forwardEvents = [];
      if (!el._x_forwardEvents.includes(value2))
        el._x_forwardEvents.push(value2);
    }
    let removeListener = on2(el, value2, modifiers, (e) => {
      evaluate2(() => {
      }, { scope: { $event: e }, params: [e] });
    });
    cleanup22(() => removeListener());
  }));
  warnMissingPluginDirective("Collapse", "collapse", "collapse");
  warnMissingPluginDirective("Intersect", "intersect", "intersect");
  warnMissingPluginDirective("Focus", "trap", "focus");
  warnMissingPluginDirective("Mask", "mask", "mask");
  function warnMissingPluginDirective(name, directiveName2, slug) {
    directive(directiveName2, (el) => warn(`You can't use [x-${directiveName2}] without first installing the "${name}" plugin here: https://alpinejs.dev/plugins/${slug}`, el));
  }
  alpine_default.setEvaluator(normalEvaluator);
  alpine_default.setReactivityEngine({ reactive: reactive22, effect: effect22, release: stop2, raw: toRaw2 });
  var src_default = alpine_default;
  var module_default = src_default;

  // js/synthetic/features/methods.js
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

  // js/synthetic/features/prefetch.js
  function prefetch_default() {
  }

  // js/synthetic/features/redirect.js
  function redirect_default() {
    on("effects", (target, effects) => {
      if (!effects["redirect"])
        return;
      let url = effects["redirect"];
      window.location.href = url;
    });
  }

  // js/synthetic/features/loading.js
  function loading_default() {
    on("new", (target) => {
      target.__loading = reactive3({ state: false });
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

  // js/synthetic/features/polling.js
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

  // js/synthetic/features/errors.js
  function errors_default() {
    on("new", (target, path) => {
      target.__errors = reactive3({ state: [] });
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

  // js/synthetic/features/dirty.js
  function dirty_default() {
    on("new", (target) => {
      target.__dirty = reactive3({ state: 0 });
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

  // js/synthetic/features/index.js
  methods_default();
  prefetch_default();
  redirect_default();
  loading_default();
  polling_default();
  errors_default();
  dirty_default();

  // js/synthetic/index.js
  var reactive3 = reactive;
  var release2 = stop;
  var effect3 = effect;
  var raw2 = toRaw;
  document.addEventListener("alpine:init", () => {
    reactive3 = module_default.reactive;
    effect3 = module_default.effect;
    release2 = module_default.release;
    raw2 = module_default.raw;
  });
  var store2 = /* @__PURE__ */ new Map();
  function synthetic(dehydrated) {
    if (typeof dehydrated === "string")
      return newUp(dehydrated);
    let target = {
      effects: raw2(dehydrated.effects),
      snapshot: raw2(dehydrated.snapshot)
    };
    let symbol = Symbol();
    store2.set(symbol, target);
    let canonical = extractData(deepClone(target.snapshot.data), symbol);
    let ephemeral = extractDataAndDecorate(deepClone(target.snapshot.data), symbol);
    target.canonical = canonical;
    target.ephemeral = ephemeral;
    target.reactive = reactive3(ephemeral);
    trigger2("new", target);
    processEffects(target);
    return target.reactive;
  }
  async function newUp(name) {
    return synthetic(await requestNew(name));
  }
  function extractDataAndDecorate(payload, symbol) {
    return extractData(payload, symbol, (object2, meta, symbol2, path) => {
      let target = store2.get(symbol2);
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
        effect3(() => {
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
      addProp("$watchEffect", (callback) => effect3(callback));
      addProp("$refresh", async () => await requestCommit(symbol2));
      addProp("get", (property2) => dataGet(target.reactive, property2));
      addProp("set", async (property2, value2) => {
        dataSet(target.reactive, property2, value2);
        return await requestCommit(symbol2);
      });
      addProp("call", (method, ...params) => {
        return target.reactive[method](...params);
      });
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
      let queue2 = requestTargetQueue.get(symbol);
      queue2.calls.push({
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
      let queue2 = requestTargetQueue.get(symbol);
      queue2.handleResponse = () => resolve();
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
    trigger2("request.prepare", requestTargetQueue);
    requestTargetQueue.forEach((request2, symbol) => {
      let target = store2.get(symbol);
      trigger2("target.request.prepare", target);
    });
    let payload = [];
    let successReceivers = [];
    let failureReceivers = [];
    requestTargetQueue.forEach((request2, symbol) => {
      let target = store2.get(symbol);
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
      let finishTarget = trigger2("target.request", target, targetPaylaod);
      failureReceivers.push(() => {
        let failed = true;
        finishTarget(failed);
      });
      successReceivers.push((snapshot, effects) => {
        mergeNewSnapshot(symbol, snapshot, effects);
        processEffects(target);
        let returnHandlerStack = request2.calls.map(({ path, handleReturn }) => [path, handleReturn]);
        let returnStack = [];
        Object.entries(effects || []).forEach(([iPath, iEffects]) => {
          if (!iEffects["returns"])
            return;
          let iReturns = iEffects["returns"];
          iReturns.forEach((iReturn) => returnStack.push([iPath, iReturn]));
        });
        returnHandlerStack.forEach(([path, handleReturn], index) => {
          let [iPath, iReturn] = returnStack[index];
          if (path !== path)
            return;
          handleReturn(iReturn);
        });
        finishTarget();
        request2.handleResponse();
      });
    });
    requestTargetQueue.clear();
    let finish = trigger2("request");
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
        successReceivers[i](snapshot, effects);
      }
      finish(false);
      trigger2("response.success");
    } else {
      let html = await request.text();
      showHtmlModal(html);
      for (let i = 0; i < failureReceivers.length; i++) {
        failureReceivers[i]();
      }
      let failed = true;
      finish(failed);
    }
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
    if (document.querySelector("[data-livewire-scripts]")) {
      return document.querySelector("[data-livewire-scripts]").getAttribute("data-csrf");
    }
    return window.__csrf;
  }
  function mergeNewSnapshot(symbol, snapshot, effects) {
    let target = store2.get(symbol);
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
    module_default.morph(el, to, {
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
          let data2;
          if (message.fingerprint && closestComponentId == message.fingerprint.id) {
            data2 = {
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
  function directives2(el) {
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
      return this.directives.map((directive2) => directive2.type).includes(type);
    }
    missing(type) {
      return !this.has(type);
    }
    get(type) {
      return this.directives.find((directive2) => directive2.type === type);
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
  function debounce2(func, wait, immediate) {
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
  function dataSet2(object2, key, value2) {
    let segments = key.split(".");
    if (segments.length === 1) {
      return object2[key] = value2;
    }
    let firstSegment = segments.shift();
    let restOfSegments = segments.join(".");
    if (object2[firstSegment] === void 0) {
      object2[firstSegment] = {};
    }
    dataSet2(object2[firstSegment], restOfSegments, value2);
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
  function dispatch2(el, name, detail = {}, bubbles = true) {
    el.dispatchEvent(new CustomEvent(name, {
      detail,
      bubbles,
      composed: true,
      cancelable: true
    }));
  }

  // js/events.js
  var listeners2 = new Bag();

  // js/features/wireModel.js
  function wireModel_default() {
    on("element.init", (el, component) => {
      let allDirectives = directives2(el);
      if (allDirectives.missing("model"))
        return;
      let directive2 = allDirectives.get("model");
      if (!directive2.value) {
        console.warn("Livewire: [wire:model] is missing a value.", el);
        return;
      }
      let lazy = directive2.modifiers.includes("lazy");
      let modifierTail = getModifierTail(directive2.modifiers);
      let live = directive2.modifiers.includes("live");
      let update = debounce2((component2) => {
        if (!live)
          return;
        component2.$wire.$commit();
      }, 250);
      module_default.bind(el, {
        ["@change"]() {
          if (lazy) {
          }
        },
        ["x-model" + modifierTail]() {
          return {
            get() {
              return dataGet2(closestComponent(el).$wire, directive2.value);
            },
            set(value2) {
              let component2 = closestComponent(el);
              dataSet2(component2.$wire, directive2.value, value2);
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
      directives2(el).all().forEach((directive2) => {
        if (["model", "init", "loading", "poll", "ignore", "id", "initial-data", "key", "target", "dirty"].includes(directive2.type))
          return;
        let attribute = directive2.rawName.replace("wire:", "x-on:");
        if (directive2.type === "submit" && !directive2.modifiers.includes("prevent")) {
          attribute = attribute + ".prevent";
        }
        module_default.bind(el, {
          [attribute](e) {
            module_default.evaluate(el, "$wire." + directive2.value, { scope: { $event: e } });
          }
        });
      });
    });
  }

  // src/Features/SupportEntangle/SupportEntangle.js
  function SupportEntangle_default(enabled) {
    on("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("entangle", (name, defer = true) => {
        let component = findComponent(target.__livewireId);
        return generateEntangleFunction(component)(name, defer);
      });
    });
  }
  function generateEntangleFunction(component) {
    return (name, defer = true) => {
      let isDeferred = defer;
      let livewireProperty = name;
      let livewireComponent = component.$wire;
      let livewirePropertyValue = livewireComponent.get(livewireProperty);
      let interceptor2 = module_default.interceptor((initialValue, getter, setter, path, key) => {
        if (typeof livewirePropertyValue === "undefined") {
          console.error(`Livewire Entangle Error: Livewire property '${livewireProperty}' cannot be found`);
          return;
        }
        module_default.entangle({
          get() {
            return livewireComponent.get(name);
          },
          set(value2) {
            livewireComponent.set(name, value2);
          }
        }, {
          get() {
            return getter();
          },
          set(value2) {
            setter(value2);
          }
        });
        return livewireComponent.get(name);
      }, (obj) => {
        Object.defineProperty(obj, "defer", {
          get() {
            isDeferred = true;
            return obj;
          }
        });
      });
      return interceptor2(livewirePropertyValue);
    };
  }

  // js/features/$wire.js
  function wire_default() {
    module_default.magic("wire", (el) => closestComponent(el).$wire);
  }

  // js/features/disableFormsDuringRequest.js
  var cleanupStackByComponentId = {};
  function disableFormsDuringRequest_default() {
    on("element.init", (el, component) => {
      let directives3 = directives2(el);
      if (directives3.missing("submit"))
        return;
      el.addEventListener("submit", () => {
        cleanupStackByComponentId[component.id] = [];
        module_default.walk(component.el, (node, skip) => {
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
    on("target.request", (target) => {
      let component = findComponent(target.__livewireId);
      return () => {
        cleanup2(component);
      };
    });
  }
  function cleanup2(component) {
    if (!cleanupStackByComponentId[component.id])
      return;
    while (cleanupStackByComponentId[component.id].length > 0) {
      cleanupStackByComponentId[component.id].shift()();
    }
  }

  // js/features/dispatchBrowserEvents.js
  function dispatchBrowserEvents_default() {
    on("effects", (target, effects, path) => {
      let dispatches = effects.dispatches;
      if (!dispatches)
        return;
      let component = findComponent(target.__livewireId);
      dispatches.forEach(({ event, data: data2 }) => {
        data2 = data2 || {};
        let e = new CustomEvent(event, {
          bubbles: true,
          detail: data2
        });
        component.el.dispatchEvent(e);
      });
    });
  }

  // js/features/magicMethods.js
  function magicMethods_default() {
    on("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$set", (...params) => {
        let component = findComponent(target.__livewireId);
        return component.$wire.set(...params);
      });
      addProp("$toggle", (name) => {
        let component = findComponent(target.__livewireId);
        return component.$wire.set(name, !component.$wire.get(name));
      });
    });
  }

  // js/features/events.js
  var globalListeners = new Bag();
  function events_default() {
    on("effects", (target, effects, path) => {
      let listeners3 = effects.listeners;
      if (!listeners3)
        return;
      listeners3.forEach((name) => {
        globalListeners.add(name, (...params) => {
          let component = findComponent(target.__livewireId);
          component.$wire.call("__emit", name, ...params);
        });
        queueMicrotask(() => {
          let component = findComponent(target.__livewireId);
          component.el.addEventListener("__lwevent:" + name, (e) => {
            component.$wire.call("__emit", name, ...e.detail.params);
          });
        });
      });
    });
    on("decorate", (target, path, addProp, decorator, symbol) => {
      addProp("$emit", (...params) => {
        emit(...params);
      });
      addProp("$emitUp", (...params) => {
        let component = findComponent(target.__livewireId);
        emitUp(component.el, ...params);
      });
      addProp("$emitSelf", (...params) => {
        let component = findComponent(target.__livewireId);
        emitSelf(component.id, ...params);
      });
      addProp("$emitTo", (...params) => {
        emitTo(...params);
      });
    });
  }
  function emit(name, ...params) {
    globalListeners.each(name, (i) => i(...params));
  }
  function emitUp(el, name, ...params) {
    dispatch2(el, "__lwevent:" + name, { params });
  }
  function emitSelf(id, name, ...params) {
    let component = findComponent(id);
    dispatch2(component.el, "__lwevent:" + name, { params }, false);
  }
  function emitTo(componentName, name, ...params) {
    let components = componentsByName(componentName);
    components.forEach((component) => {
      dispatch2(component.el, "__lwevent:" + name, { params }, false);
    });
  }
  function on4(name, callback) {
    globalListeners.add(name, callback);
  }

  // js/features/index.js
  function features_default(enabledFeatures) {
    wire_default(enabledFeatures);
    morphDom_default(enabledFeatures);
    wireModel_default(enabledFeatures);
    events_default();
    wireWildcard_default(enabledFeatures);
    magicMethods_default();
    dispatchBrowserEvents_default();
    disableFormsDuringRequest_default(enabledFeatures);
    SupportEntangle_default();
  }

  // js/component.js
  var Component = class {
    constructor(synthetic2, el, id) {
      this.synthetic = synthetic2;
      this.$wire = this.synthetic.reactive;
      this.el = el;
      this.id = id;
      this.name = this.synthetic.snapshot.data[1].name;
      synthetic2.__livewireId = this.id;
    }
  };

  // ../alpine/packages/morph/dist/module.esm.js
  function createElement(html) {
    const template = document.createElement("template");
    template.innerHTML = html;
    return template.content.firstElementChild;
  }
  function textOrComment(el) {
    return el.nodeType === 3 || el.nodeType === 8;
  }
  var dom = {
    replace(children, old, replacement) {
      let index = children.indexOf(old);
      if (index === -1)
        throw "Cant find element in children";
      old.replaceWith(replacement);
      children[index] = replacement;
      return children;
    },
    before(children, reference, subject) {
      let index = children.indexOf(reference);
      if (index === -1)
        throw "Cant find element in children";
      reference.before(subject);
      children.splice(index, 0, subject);
      return children;
    },
    append(children, subject, appendFn) {
      let last = children[children.length - 1];
      appendFn(subject);
      children.push(subject);
      return children;
    },
    remove(children, subject) {
      let index = children.indexOf(subject);
      if (index === -1)
        throw "Cant find element in children";
      subject.remove();
      return children.filter((i) => i !== subject);
    },
    first(children) {
      return this.teleportTo(children[0]);
    },
    next(children, reference) {
      let index = children.indexOf(reference);
      if (index === -1)
        return;
      return this.teleportTo(this.teleportBack(children[index + 1]));
    },
    teleportTo(el) {
      if (!el)
        return el;
      if (el._x_teleport)
        return el._x_teleport;
      return el;
    },
    teleportBack(el) {
      if (!el)
        return el;
      if (el._x_teleportBack)
        return el._x_teleportBack;
      return el;
    }
  };
  var resolveStep = () => {
  };
  var logger = () => {
  };
  function morph2(from, toHtml, options) {
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
        return patchElement(from2, to);
      }
      let updateChildrenOnly = false;
      if (shouldSkip(updating, from2, to, () => updateChildrenOnly = true))
        return;
      window.Alpine && initializeAlpineOnTo(from2, to, () => updateChildrenOnly = true);
      if (textOrComment(to)) {
        patchNodeValue(from2, to);
        updated(from2, to);
        return;
      }
      if (!updateChildrenOnly) {
        patchAttributes(from2, to);
      }
      updated(from2, to);
      patchChildren(Array.from(from2.childNodes), Array.from(to.childNodes), (toAppend) => {
        from2.appendChild(toAppend);
      });
    }
    function differentElementNamesTypesOrKeys(from2, to) {
      return from2.nodeType != to.nodeType || from2.nodeName != to.nodeName || getKey(from2) != getKey(to);
    }
    function patchElement(from2, to) {
      if (shouldSkip(removing, from2))
        return;
      let toCloned = to.cloneNode(true);
      if (shouldSkip(adding, toCloned))
        return;
      dom.replace([from2], from2, toCloned);
      removed(from2);
      added(toCloned);
    }
    function patchNodeValue(from2, to) {
      let value2 = to.nodeValue;
      if (from2.nodeValue !== value2) {
        from2.nodeValue = value2;
      }
    }
    function patchAttributes(from2, to) {
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
        let value2 = toAttributes[i].value;
        if (from2.getAttribute(name) !== value2) {
          from2.setAttribute(name, value2);
        }
      }
    }
    function patchChildren(fromChildren, toChildren, appendFn) {
      let fromKeyDomNodeMap = {};
      let fromKeyHoldovers = {};
      let currentTo = dom.first(toChildren);
      let currentFrom = dom.first(fromChildren);
      while (currentTo) {
        let toKey = getKey(currentTo);
        let fromKey = getKey(currentFrom);
        if (!currentFrom) {
          if (toKey && fromKeyHoldovers[toKey]) {
            let holdover = fromKeyHoldovers[toKey];
            fromChildren = dom.append(fromChildren, holdover, appendFn);
            currentFrom = holdover;
          } else {
            if (!shouldSkip(adding, currentTo)) {
              let clone2 = currentTo.cloneNode(true);
              fromChildren = dom.append(fromChildren, clone2, appendFn);
              added(clone2);
            }
            currentTo = dom.next(toChildren, currentTo);
            continue;
          }
        }
        let isIf = (node) => node.nodeType === 8 && node.textContent === " __BLOCK__ ";
        let isEnd = (node) => node.nodeType === 8 && node.textContent === " __ENDBLOCK__ ";
        if (isIf(currentTo) && isIf(currentFrom)) {
          let newFromChildren = [];
          let appendPoint;
          let nestedIfCount = 0;
          while (currentFrom) {
            let next = dom.next(fromChildren, currentFrom);
            if (isIf(next)) {
              nestedIfCount++;
            } else if (isEnd(next) && nestedIfCount > 0) {
              nestedIfCount--;
            } else if (isEnd(next) && nestedIfCount === 0) {
              currentFrom = dom.next(fromChildren, next);
              appendPoint = next;
              break;
            }
            newFromChildren.push(next);
            currentFrom = next;
          }
          let newToChildren = [];
          nestedIfCount = 0;
          while (currentTo) {
            let next = dom.next(toChildren, currentTo);
            if (isIf(next)) {
              nestedIfCount++;
            } else if (isEnd(next) && nestedIfCount > 0) {
              nestedIfCount--;
            } else if (isEnd(next) && nestedIfCount === 0) {
              currentTo = dom.next(toChildren, next);
              break;
            }
            newToChildren.push(next);
            currentTo = next;
          }
          patchChildren(newFromChildren, newToChildren, (node) => appendPoint.before(node));
          continue;
        }
        if (currentFrom.nodeType === 1 && lookahead) {
          let nextToElementSibling = dom.next(toChildren, currentTo);
          let found = false;
          while (!found && nextToElementSibling) {
            if (currentFrom.isEqualNode(nextToElementSibling)) {
              found = true;
              [fromChildren, currentFrom] = addNodeBefore(fromChildren, currentTo, currentFrom);
              fromKey = getKey(currentFrom);
            }
            nextToElementSibling = dom.next(toChildren, nextToElementSibling);
          }
        }
        if (toKey !== fromKey) {
          if (!toKey && fromKey) {
            fromKeyHoldovers[fromKey] = currentFrom;
            [fromChildren, currentFrom] = addNodeBefore(fromChildren, currentTo, currentFrom);
            fromChildren = dom.remove(fromChildren, fromKeyHoldovers[fromKey]);
            currentFrom = dom.next(fromChildren, currentFrom);
            currentTo = dom.next(toChildren, currentTo);
            continue;
          }
          if (toKey && !fromKey) {
            if (fromKeyDomNodeMap[toKey]) {
              fromChildren = dom.replace(fromChildren, currentFrom, fromKeyDomNodeMap[toKey]);
              currentFrom = fromKeyDomNodeMap[toKey];
            }
          }
          if (toKey && fromKey) {
            let fromKeyNode = fromKeyDomNodeMap[toKey];
            if (fromKeyNode) {
              fromKeyHoldovers[fromKey] = currentFrom;
              fromChildren = dom.replace(fromChildren, currentFrom, fromKeyNode);
              currentFrom = fromKeyNode;
            } else {
              fromKeyHoldovers[fromKey] = currentFrom;
              [fromChildren, currentFrom] = addNodeBefore(fromChildren, currentTo, currentFrom);
              fromChildren = dom.remove(fromChildren, fromKeyHoldovers[fromKey]);
              currentFrom = dom.next(fromChildren, currentFrom);
              currentTo = dom.next(toChildren, currentTo);
              continue;
            }
          }
        }
        let currentFromNext = currentFrom && dom.next(fromChildren, currentFrom);
        patch(currentFrom, currentTo);
        currentTo = currentTo && dom.next(toChildren, currentTo);
        currentFrom = currentFromNext;
      }
      let removals = [];
      while (currentFrom) {
        if (!shouldSkip(removing, currentFrom))
          removals.push(currentFrom);
        currentFrom = dom.next(fromChildren, currentFrom);
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
    function keyToMap(els) {
      let map = {};
      els.forEach((el) => {
        let theKey = getKey(el);
        if (theKey) {
          map[theKey] = el;
        }
      });
      return map;
    }
    function addNodeBefore(children, node, beforeMe) {
      if (!shouldSkip(adding, node)) {
        let clone2 = node.cloneNode(true);
        children = dom.before(children, beforeMe, clone2);
        added(clone2);
        return [children, clone2];
      }
      return [children, node];
    }
    assignOptions(options);
    fromEl = from;
    toEl = typeof toHtml === "string" ? createElement(toHtml) : toHtml;
    if (window.Alpine && window.Alpine.closestDataStack && !from._x_dataStack) {
      toEl._x_dataStack = window.Alpine.closestDataStack(from);
      toEl._x_dataStack && window.Alpine.clone(from, toEl);
    }
    patch(from, toEl);
    fromEl = void 0;
    toEl = void 0;
    return from;
  }
  morph2.step = () => resolveStep();
  morph2.log = (theLogger) => {
    logger = theLogger;
  };
  function shouldSkip(hook, ...args) {
    let skip = false;
    hook(...args, () => skip = true);
    return skip;
  }
  function initializeAlpineOnTo(from, to, childrenOnly) {
    if (from.nodeType !== 1)
      return;
    if (from._x_dataStack) {
      window.Alpine.clone(from, to);
    }
  }
  function src_default2(Alpine3) {
    Alpine3.morph = morph2;
  }
  var module_default2 = src_default2;

  // js/lifecycle.js
  function start2(options = {}) {
    monkeyPatchDomSetAttributeToAllowAtSymbols();
    let enabledFeatures = options.features || [];
    features_default(enabledFeatures);
    module_default.interceptInit(module_default.skipDuringClone((el) => {
      initElement(el);
    }));
    module_default.plugin(module_default2);
    module_default.addRootSelector(() => "[wire\\:id]");
    module_default.start();
    setTimeout(() => {
      window.Livewire.initialRenderIsFinished = true;
    });
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
      module_default.bind(el, {
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
    let closestRoot2 = module_default.findClosest(el, (i) => i.__livewire);
    if (!closestRoot2) {
      throw "Could not find Livewire component in DOM tree";
    }
    return closestRoot2.__livewire;
  }

  // js/index.js
  window.synthetic = synthetic;
  window.syntheticOn = on;
  var Livewire = {
    start: start2,
    hook: on,
    on: on4,
    emit,
    first,
    find
  };
  if (window.Livewire)
    console.warn("Detected multiple instances of Livewire running");
  if (window.Alpine)
    console.warn("Detected multiple instances of Alpine running");
  window.Livewire = Livewire;
  window.Alpine = module_default;
  Livewire.start();
})();
