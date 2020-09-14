(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
  typeof define === 'function' && define.amd ? define(factory) :
  (global = global || self, global.Livewire = factory());
}(this, (function () { 'use strict';

  function _typeof(obj) {
    "@babel/helpers - typeof";

    if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") {
      _typeof = function (obj) {
        return typeof obj;
      };
    } else {
      _typeof = function (obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
      };
    }

    return _typeof(obj);
  }

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }

  function _defineProperty(obj, key, value) {
    if (key in obj) {
      Object.defineProperty(obj, key, {
        value: value,
        enumerable: true,
        configurable: true,
        writable: true
      });
    } else {
      obj[key] = value;
    }

    return obj;
  }

  function ownKeys(object, enumerableOnly) {
    var keys = Object.keys(object);

    if (Object.getOwnPropertySymbols) {
      var symbols = Object.getOwnPropertySymbols(object);
      if (enumerableOnly) symbols = symbols.filter(function (sym) {
        return Object.getOwnPropertyDescriptor(object, sym).enumerable;
      });
      keys.push.apply(keys, symbols);
    }

    return keys;
  }

  function _objectSpread2(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i] != null ? arguments[i] : {};

      if (i % 2) {
        ownKeys(Object(source), true).forEach(function (key) {
          _defineProperty(target, key, source[key]);
        });
      } else if (Object.getOwnPropertyDescriptors) {
        Object.defineProperties(target, Object.getOwnPropertyDescriptors(source));
      } else {
        ownKeys(Object(source)).forEach(function (key) {
          Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key));
        });
      }
    }

    return target;
  }

  function _inherits(subClass, superClass) {
    if (typeof superClass !== "function" && superClass !== null) {
      throw new TypeError("Super expression must either be null or a function");
    }

    subClass.prototype = Object.create(superClass && superClass.prototype, {
      constructor: {
        value: subClass,
        writable: true,
        configurable: true
      }
    });
    if (superClass) _setPrototypeOf(subClass, superClass);
  }

  function _getPrototypeOf(o) {
    _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) {
      return o.__proto__ || Object.getPrototypeOf(o);
    };
    return _getPrototypeOf(o);
  }

  function _setPrototypeOf(o, p) {
    _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) {
      o.__proto__ = p;
      return o;
    };

    return _setPrototypeOf(o, p);
  }

  function _isNativeReflectConstruct() {
    if (typeof Reflect === "undefined" || !Reflect.construct) return false;
    if (Reflect.construct.sham) return false;
    if (typeof Proxy === "function") return true;

    try {
      Date.prototype.toString.call(Reflect.construct(Date, [], function () {}));
      return true;
    } catch (e) {
      return false;
    }
  }

  function _assertThisInitialized(self) {
    if (self === void 0) {
      throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
    }

    return self;
  }

  function _possibleConstructorReturn(self, call) {
    if (call && (typeof call === "object" || typeof call === "function")) {
      return call;
    }

    return _assertThisInitialized(self);
  }

  function _createSuper(Derived) {
    var hasNativeReflectConstruct = _isNativeReflectConstruct();

    return function _createSuperInternal() {
      var Super = _getPrototypeOf(Derived),
          result;

      if (hasNativeReflectConstruct) {
        var NewTarget = _getPrototypeOf(this).constructor;

        result = Reflect.construct(Super, arguments, NewTarget);
      } else {
        result = Super.apply(this, arguments);
      }

      return _possibleConstructorReturn(this, result);
    };
  }

  function _slicedToArray(arr, i) {
    return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest();
  }

  function _toArray(arr) {
    return _arrayWithHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableRest();
  }

  function _toConsumableArray(arr) {
    return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
  }

  function _arrayWithoutHoles(arr) {
    if (Array.isArray(arr)) return _arrayLikeToArray(arr);
  }

  function _arrayWithHoles(arr) {
    if (Array.isArray(arr)) return arr;
  }

  function _iterableToArray(iter) {
    if (typeof Symbol !== "undefined" && Symbol.iterator in Object(iter)) return Array.from(iter);
  }

  function _iterableToArrayLimit(arr, i) {
    if (typeof Symbol === "undefined" || !(Symbol.iterator in Object(arr))) return;
    var _arr = [];
    var _n = true;
    var _d = false;
    var _e = undefined;

    try {
      for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
        _arr.push(_s.value);

        if (i && _arr.length === i) break;
      }
    } catch (err) {
      _d = true;
      _e = err;
    } finally {
      try {
        if (!_n && _i["return"] != null) _i["return"]();
      } finally {
        if (_d) throw _e;
      }
    }

    return _arr;
  }

  function _unsupportedIterableToArray(o, minLen) {
    if (!o) return;
    if (typeof o === "string") return _arrayLikeToArray(o, minLen);
    var n = Object.prototype.toString.call(o).slice(8, -1);
    if (n === "Object" && o.constructor) n = o.constructor.name;
    if (n === "Map" || n === "Set") return Array.from(o);
    if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
  }

  function _arrayLikeToArray(arr, len) {
    if (len == null || len > arr.length) len = arr.length;

    for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i];

    return arr2;
  }

  function _nonIterableSpread() {
    throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }

  function _nonIterableRest() {
    throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }

  function debounce(func, wait, immediate) {
    var timeout;
    return function () {
      var context = this,
          args = arguments;

      var later = function later() {
        timeout = null;
        if (!immediate) func.apply(context, args);
      };

      var callNow = immediate && !timeout;
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
      if (callNow) func.apply(context, args);
    };
  }

  function wireDirectives(el) {
    return new DirectiveManager(el);
  }

  var DirectiveManager = /*#__PURE__*/function () {
    function DirectiveManager(el) {
      _classCallCheck(this, DirectiveManager);

      this.el = el;
      this.directives = this.extractTypeModifiersAndValue();
    }

    _createClass(DirectiveManager, [{
      key: "all",
      value: function all() {
        return this.directives;
      }
    }, {
      key: "has",
      value: function has(type) {
        return this.directives.map(function (directive) {
          return directive.type;
        }).includes(type);
      }
    }, {
      key: "missing",
      value: function missing(type) {
        return !this.has(type);
      }
    }, {
      key: "get",
      value: function get(type) {
        return this.directives.find(function (directive) {
          return directive.type === type;
        });
      }
    }, {
      key: "extractTypeModifiersAndValue",
      value: function extractTypeModifiersAndValue() {
        var _this = this;

        return Array.from(this.el.getAttributeNames() // Filter only the livewire directives.
        .filter(function (name) {
          return name.match(new RegExp('wire:'));
        }) // Parse out the type, modifiers, and value from it.
        .map(function (name) {
          var _name$replace$split = name.replace(new RegExp('wire:'), '').split('.'),
              _name$replace$split2 = _toArray(_name$replace$split),
              type = _name$replace$split2[0],
              modifiers = _name$replace$split2.slice(1);

          return new Directive(type, modifiers, name, _this.el);
        }));
      }
    }]);

    return DirectiveManager;
  }();

  var Directive = /*#__PURE__*/function () {
    function Directive(type, modifiers, rawName, el) {
      _classCallCheck(this, Directive);

      this.type = type;
      this.modifiers = modifiers;
      this.rawName = rawName;
      this.el = el;
      this.eventContext;
    }

    _createClass(Directive, [{
      key: "setEventContext",
      value: function setEventContext(context) {
        this.eventContext = context;
      }
    }, {
      key: "durationOr",
      value: function durationOr(defaultDuration) {
        var durationInMilliSeconds;
        var durationInMilliSecondsString = this.modifiers.find(function (mod) {
          return mod.match(/([0-9]+)ms/);
        });
        var durationInSecondsString = this.modifiers.find(function (mod) {
          return mod.match(/([0-9]+)s/);
        });

        if (durationInMilliSecondsString) {
          durationInMilliSeconds = Number(durationInMilliSecondsString.replace('ms', ''));
        } else if (durationInSecondsString) {
          durationInMilliSeconds = Number(durationInSecondsString.replace('s', '')) * 1000;
        }

        return durationInMilliSeconds || defaultDuration;
      }
    }, {
      key: "parseOutMethodAndParams",
      value: function parseOutMethodAndParams(rawMethod) {
        var method = rawMethod;
        var params = [];
        var methodAndParamString = method.match(/(.*?)\((.*)\)/);

        if (methodAndParamString) {
          // This "$event" is for use inside the livewire event handler.
          var $event = this.eventContext;
          method = methodAndParamString[1]; // use a function that returns it's arguments to parse and eval all params

          params = eval("(function () {\n                for (var l=arguments.length, p=new Array(l), k=0; k<l; k++) {\n                    p[k] = arguments[k];\n                }\n                return [].concat(p);\n            })(".concat(methodAndParamString[2], ")"));
        }

        return {
          method: method,
          params: params
        };
      }
    }, {
      key: "cardinalDirectionOr",
      value: function cardinalDirectionOr() {
        var fallback = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 'right';
        if (this.modifiers.includes('up')) return 'up';
        if (this.modifiers.includes('down')) return 'down';
        if (this.modifiers.includes('left')) return 'left';
        if (this.modifiers.includes('right')) return 'right';
        return fallback;
      }
    }, {
      key: "value",
      get: function get() {
        return this.el.getAttribute(this.rawName);
      }
    }, {
      key: "method",
      get: function get() {
        var _this$parseOutMethodA = this.parseOutMethodAndParams(this.value),
            method = _this$parseOutMethodA.method;

        return method;
      }
    }, {
      key: "params",
      get: function get() {
        var _this$parseOutMethodA2 = this.parseOutMethodAndParams(this.value),
            params = _this$parseOutMethodA2.params;

        return params;
      }
    }]);

    return Directive;
  }();

  // A little DOM-tree walker.
  // (TreeWalker won't do because I need to conditionaly ignore sub-trees using the callback)
  function walk(root, callback) {
    if (callback(root) === false) return;
    var node = root.firstElementChild;

    while (node) {
      walk(node, callback);
      node = node.nextElementSibling;
    }
  }

  function dispatch(eventName) {
    var event = document.createEvent('Events');
    event.initEvent(eventName, true, true);
    document.dispatchEvent(event);
    return event;
  }

  function getCsrfToken() {
    var tokenTag = document.head.querySelector('meta[name="csrf-token"]');
    var token;

    if (!tokenTag) {
      if (!window.livewire_token) {
        throw new Error('Whoops, looks like you haven\'t added a "csrf-token" meta tag');
      }

      token = window.livewire_token;
    } else {
      token = tokenTag.content;
    }

    return token;
  }

  function kebabCase(subject) {
    return subject.replace(/([a-z])([A-Z])/g, '$1-$2').replace(/[_\s]/, '-').toLowerCase();
  }

  /*!
   * isobject <https://github.com/jonschlinkert/isobject>
   *
   * Copyright (c) 2014-2017, Jon Schlinkert.
   * Released under the MIT License.
   */

  var isobject = function isObject(val) {
    return val != null && typeof val === 'object' && Array.isArray(val) === false;
  };

  /*!
   * get-value <https://github.com/jonschlinkert/get-value>
   *
   * Copyright (c) 2014-2018, Jon Schlinkert.
   * Released under the MIT License.
   */



  var getValue = function(target, path, options) {
    if (!isobject(options)) {
      options = { default: options };
    }

    if (!isValidObject(target)) {
      return typeof options.default !== 'undefined' ? options.default : target;
    }

    if (typeof path === 'number') {
      path = String(path);
    }

    const isArray = Array.isArray(path);
    const isString = typeof path === 'string';
    const splitChar = options.separator || '.';
    const joinChar = options.joinChar || (typeof splitChar === 'string' ? splitChar : '.');

    if (!isString && !isArray) {
      return target;
    }

    if (isString && path in target) {
      return isValid(path, target, options) ? target[path] : options.default;
    }

    let segs = isArray ? path : split(path, splitChar, options);
    let len = segs.length;
    let idx = 0;

    do {
      let prop = segs[idx];
      if (typeof prop === 'number') {
        prop = String(prop);
      }

      while (prop && prop.slice(-1) === '\\') {
        prop = join([prop.slice(0, -1), segs[++idx] || ''], joinChar, options);
      }

      if (prop in target) {
        if (!isValid(prop, target, options)) {
          return options.default;
        }

        target = target[prop];
      } else {
        let hasProp = false;
        let n = idx + 1;

        while (n < len) {
          prop = join([prop, segs[n++]], joinChar, options);

          if ((hasProp = prop in target)) {
            if (!isValid(prop, target, options)) {
              return options.default;
            }

            target = target[prop];
            idx = n - 1;
            break;
          }
        }

        if (!hasProp) {
          return options.default;
        }
      }
    } while (++idx < len && isValidObject(target));

    if (idx === len) {
      return target;
    }

    return options.default;
  };

  function join(segs, joinChar, options) {
    if (typeof options.join === 'function') {
      return options.join(segs);
    }
    return segs[0] + joinChar + segs[1];
  }

  function split(path, splitChar, options) {
    if (typeof options.split === 'function') {
      return options.split(path);
    }
    return path.split(splitChar);
  }

  function isValid(key, target, options) {
    if (typeof options.isValid === 'function') {
      return options.isValid(key, target);
    }
    return true;
  }

  function isValidObject(val) {
    return isobject(val) || Array.isArray(val) || typeof val === 'function';
  }

  var _default = /*#__PURE__*/function () {
    function _default(el) {
      var skipWatcher = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

      _classCallCheck(this, _default);

      this.el = el;
      this.skipWatcher = skipWatcher;

      this.resolveCallback = function () {};

      this.rejectCallback = function () {};
    }

    _createClass(_default, [{
      key: "toId",
      value: function toId() {
        return btoa(encodeURIComponent(this.el.outerHTML));
      }
    }, {
      key: "onResolve",
      value: function onResolve(callback) {
        this.resolveCallback = callback;
      }
    }, {
      key: "onReject",
      value: function onReject(callback) {
        this.rejectCallback = callback;
      }
    }, {
      key: "resolve",
      value: function resolve(thing) {
        this.resolveCallback(thing);
      }
    }, {
      key: "reject",
      value: function reject(thing) {
        this.rejectCallback(thing);
      }
    }]);

    return _default;
  }();

  var _default$1 = /*#__PURE__*/function (_Action) {
    _inherits(_default, _Action);

    var _super = _createSuper(_default);

    function _default(event, params, el) {
      var _this;

      _classCallCheck(this, _default);

      _this = _super.call(this, el);
      _this.type = 'fireEvent';
      _this.payload = {
        event: event,
        params: params
      };
      return _this;
    } // Overriding toId() becuase some EventActions don't have an "el"


    _createClass(_default, [{
      key: "toId",
      value: function toId() {
        return btoa(encodeURIComponent(this.type, this.payload.event, JSON.stringify(this.payload.params)));
      }
    }]);

    return _default;
  }(_default);

  var MessageBus = /*#__PURE__*/function () {
    function MessageBus() {
      _classCallCheck(this, MessageBus);

      this.listeners = {};
    }

    _createClass(MessageBus, [{
      key: "register",
      value: function register(name, callback) {
        if (!this.listeners[name]) {
          this.listeners[name] = [];
        }

        this.listeners[name].push(callback);
      }
    }, {
      key: "call",
      value: function call(name) {
        for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          params[_key - 1] = arguments[_key];
        }

        (this.listeners[name] || []).forEach(function (callback) {
          callback.apply(void 0, params);
        });
      }
    }, {
      key: "has",
      value: function has(name) {
        return Object.keys(this.listeners).includes(name);
      }
    }]);

    return MessageBus;
  }();

  var HookManager = {
    availableHooks: [
    /**
     * Public Hooks
     */
    'component.initialized', 'element.initialized', 'element.updating', 'element.updated', 'element.removed', 'message.sent', 'message.failed', 'message.received', 'message.processed',
    /**
     * Private Hooks
     */
    'interceptWireModelSetValue', 'interceptWireModelAttachListener', 'beforeReplaceState', 'beforePushState'],
    bus: new MessageBus(),
    register: function register(name, callback) {
      if (!this.availableHooks.includes(name)) {
        throw "Livewire: Referencing unknown hook: [".concat(name, "]");
      }

      this.bus.register(name, callback);
    },
    call: function call(name) {
      var _this$bus;

      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        params[_key - 1] = arguments[_key];
      }

      (_this$bus = this.bus).call.apply(_this$bus, [name].concat(params));
    }
  };

  var DirectiveManager$1 = {
    directives: new MessageBus(),
    register: function register(name, callback) {
      if (this.has(name)) {
        throw "Livewire: Directive already registered: [".concat(name, "]");
      }

      this.directives.register(name, callback);
    },
    call: function call(name, el, directive, component) {
      this.directives.call(name, el, directive, component);
    },
    has: function has(name) {
      return this.directives.has(name);
    }
  };

  var store = {
    componentsById: {},
    listeners: new MessageBus(),
    initialRenderIsFinished: false,
    livewireIsInBackground: false,
    livewireIsOffline: false,
    sessionHasExpired: false,
    directives: DirectiveManager$1,
    hooks: HookManager,
    onErrorCallback: function onErrorCallback() {},
    components: function components() {
      var _this = this;

      return Object.keys(this.componentsById).map(function (key) {
        return _this.componentsById[key];
      });
    },
    addComponent: function addComponent(component) {
      return this.componentsById[component.id] = component;
    },
    findComponent: function findComponent(id) {
      return this.componentsById[id];
    },
    getComponentsByName: function getComponentsByName(name) {
      return this.components().filter(function (component) {
        return component.name === name;
      });
    },
    hasComponent: function hasComponent(id) {
      return !!this.componentsById[id];
    },
    tearDownComponents: function tearDownComponents() {
      var _this2 = this;

      this.components().forEach(function (component) {
        _this2.removeComponent(component);
      });
    },
    on: function on(event, callback) {
      this.listeners.register(event, callback);
    },
    emit: function emit(event) {
      var _this$listeners;

      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        params[_key - 1] = arguments[_key];
      }

      (_this$listeners = this.listeners).call.apply(_this$listeners, [event].concat(params));

      this.componentsListeningForEvent(event).forEach(function (component) {
        return component.addAction(new _default$1(event, params));
      });
    },
    emitUp: function emitUp(el, event) {
      for (var _len2 = arguments.length, params = new Array(_len2 > 2 ? _len2 - 2 : 0), _key2 = 2; _key2 < _len2; _key2++) {
        params[_key2 - 2] = arguments[_key2];
      }

      this.componentsListeningForEventThatAreTreeAncestors(el, event).forEach(function (component) {
        return component.addAction(new _default$1(event, params));
      });
    },
    emitSelf: function emitSelf(componentId, event) {
      var component = this.findComponent(componentId);

      if (component.listeners.includes(event)) {
        for (var _len3 = arguments.length, params = new Array(_len3 > 2 ? _len3 - 2 : 0), _key3 = 2; _key3 < _len3; _key3++) {
          params[_key3 - 2] = arguments[_key3];
        }

        component.addAction(new _default$1(event, params));
      }
    },
    emitTo: function emitTo(componentName, event) {
      for (var _len4 = arguments.length, params = new Array(_len4 > 2 ? _len4 - 2 : 0), _key4 = 2; _key4 < _len4; _key4++) {
        params[_key4 - 2] = arguments[_key4];
      }

      var components = this.getComponentsByName(componentName);
      components.forEach(function (component) {
        if (component.listeners.includes(event)) {
          component.addAction(new _default$1(event, params));
        }
      });
    },
    componentsListeningForEventThatAreTreeAncestors: function componentsListeningForEventThatAreTreeAncestors(el, event) {
      var parentIds = [];
      var parent = el.parentElement.closest('[wire\\:id]');

      while (parent) {
        parentIds.push(parent.getAttribute('wire:id'));
        parent = parent.parentElement.closest('[wire\\:id]');
      }

      return this.components().filter(function (component) {
        return component.listeners.includes(event) && parentIds.includes(component.id);
      });
    },
    componentsListeningForEvent: function componentsListeningForEvent(event) {
      return this.components().filter(function (component) {
        return component.listeners.includes(event);
      });
    },
    registerDirective: function registerDirective(name, callback) {
      this.directives.register(name, callback);
    },
    registerHook: function registerHook(name, callback) {
      this.hooks.register(name, callback);
    },
    callHook: function callHook(name) {
      var _this$hooks;

      for (var _len5 = arguments.length, params = new Array(_len5 > 1 ? _len5 - 1 : 0), _key5 = 1; _key5 < _len5; _key5++) {
        params[_key5 - 1] = arguments[_key5];
      }

      (_this$hooks = this.hooks).call.apply(_this$hooks, [name].concat(params));
    },
    changeComponentId: function changeComponentId(component, newId) {
      var oldId = component.id;
      component.id = newId;
      component.fingerprint.id = newId;
      this.componentsById[newId] = component;
      delete this.componentsById[oldId]; // Now go through any parents of this component and change
      // the component's child id references.

      this.components().forEach(function (component) {
        var children = component.serverMemo.children || {};
        Object.entries(children).forEach(function (_ref) {
          var _ref2 = _slicedToArray(_ref, 2),
              key = _ref2[0],
              _ref2$ = _ref2[1],
              id = _ref2$.id,
              tagName = _ref2$.tagName;

          if (id === oldId) {
            children[key].id = newId;
          }
        });
      });
    },
    removeComponent: function removeComponent(component) {
      // Remove event listeners attached to the DOM.
      component.tearDown(); // Remove the component from the store.

      delete this.componentsById[component.id];
    },
    onError: function onError(callback) {
      this.onErrorCallback = callback;
    },
    getClosestParentId: function getClosestParentId(childId, subsetOfParentIds) {
      var _this3 = this;

      var distancesByParentId = {};
      subsetOfParentIds.forEach(function (parentId) {
        var distance = _this3.getDistanceToChild(parentId, childId);

        if (distance) distancesByParentId[parentId] = distance;
      });
      var smallestDistance = Math.min.apply(Math, _toConsumableArray(Object.values(distancesByParentId)));
      var closestParentId;
      Object.entries(distancesByParentId).forEach(function (_ref3) {
        var _ref4 = _slicedToArray(_ref3, 2),
            parentId = _ref4[0],
            distance = _ref4[1];

        if (distance === smallestDistance) closestParentId = parentId;
      });
      return closestParentId;
    },
    getDistanceToChild: function getDistanceToChild(parentId, childId) {
      var distanceMemo = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
      var parentComponent = this.findComponent(parentId);
      if (!parentComponent) return;
      var childIds = parentComponent.childIds;
      if (childIds.includes(childId)) return distanceMemo;

      for (var i = 0; i < childIds.length; i++) {
        var distance = this.getDistanceToChild(childIds[i], childId, distanceMemo + 1);
        if (distance) return distance;
      }
    }
  };

  /**
   * This is intended to isolate all native DOM operations. The operations that happen
   * one specific element will be instance methods, the operations you would normally
   * perform on the "document" (like "document.querySelector") will be static methods.
   */

  var DOM = {
    rootComponentElements: function rootComponentElements() {
      return Array.from(document.querySelectorAll("[wire\\:id]"));
    },
    rootComponentElementsWithNoParents: function rootComponentElementsWithNoParents() {
      var node = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;

      if (node === null) {
        node = document;
      } // In CSS, it's simple to select all elements that DO have a certain ancestor.
      // However, it's not simple (kinda impossible) to select elements that DONT have
      // a certain ancestor. Therefore, we will flip the logic: select all roots that DO have
      // have a root ancestor, then select all roots that DONT, then diff the two.
      // Convert NodeLists to Arrays so we can use ".includes()". Ew.


      var allEls = Array.from(node.querySelectorAll("[wire\\:initial-data]"));
      var onlyChildEls = Array.from(node.querySelectorAll("[wire\\:initial-data] [wire\\:initial-data]"));
      return allEls.filter(function (el) {
        return !onlyChildEls.includes(el);
      });
    },
    allModelElementsInside: function allModelElementsInside(root) {
      return Array.from(root.querySelectorAll("[wire\\:model]"));
    },
    getByAttributeAndValue: function getByAttributeAndValue(attribute, value) {
      return document.querySelector("[wire\\:".concat(attribute, "=\"").concat(value, "\"]"));
    },
    nextFrame: function nextFrame(fn) {
      var _this = this;

      requestAnimationFrame(function () {
        requestAnimationFrame(fn.bind(_this));
      });
    },
    closestRoot: function closestRoot(el) {
      return this.closestByAttribute(el, 'id');
    },
    closestByAttribute: function closestByAttribute(el, attribute) {
      var closestEl = el.closest("[wire\\:".concat(attribute, "]"));

      if (!closestEl) {
        throw "\nLivewire Error:\n\nCannot find parent element in DOM tree containing attribute: [wire:".concat(attribute, "].\n\nUsually this is caused by Livewire's DOM-differ not being able to properly track changes.\n\nReference the following guide for common causes: https://laravel-livewire.com/docs/troubleshooting \n\nReferenced element:\n\n").concat(el.outerHTML, "\n");
      }

      return closestEl;
    },
    isComponentRootEl: function isComponentRootEl(el) {
      return this.hasAttribute(el, 'id');
    },
    hasAttribute: function hasAttribute(el, attribute) {
      return el.hasAttribute("wire:".concat(attribute));
    },
    getAttribute: function getAttribute(el, attribute) {
      return el.getAttribute("wire:".concat(attribute));
    },
    removeAttribute: function removeAttribute(el, attribute) {
      return el.removeAttribute("wire:".concat(attribute));
    },
    setAttribute: function setAttribute(el, attribute, value) {
      return el.setAttribute("wire:".concat(attribute), value);
    },
    hasFocus: function hasFocus(el) {
      return el === document.activeElement;
    },
    isInput: function isInput(el) {
      return ['INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName.toUpperCase());
    },
    isTextInput: function isTextInput(el) {
      return ['INPUT', 'TEXTAREA'].includes(el.tagName.toUpperCase()) && !['checkbox', 'radio'].includes(el.type);
    },
    valueFromInput: function valueFromInput(el, component) {
      if (el.type === 'checkbox') {
        var modelName = wireDirectives(el).get('model').value; // If there is an update from wire:model.defer in the chamber,
        // we need to pretend that is the actual data from the server.

        var modelValue = component.deferredActions[modelName] ? component.deferredActions[modelName].payload.value : getValue(component.data, modelName);

        if (Array.isArray(modelValue)) {
          return this.mergeCheckboxValueIntoArray(el, modelValue);
        }

        if (el.checked) {
          return el.getAttribute('value') || true;
        } else {
          return false;
        }
      } else if (el.tagName === 'SELECT' && el.multiple) {
        return this.getSelectValues(el);
      }

      return el.value;
    },
    mergeCheckboxValueIntoArray: function mergeCheckboxValueIntoArray(el, arrayValue) {
      if (el.checked) {
        return arrayValue.includes(el.value) ? arrayValue : arrayValue.concat(el.value);
      }

      return arrayValue.filter(function (item) {
        return item !== el.value;
      });
    },
    setInputValueFromModel: function setInputValueFromModel(el, component) {
      var modelString = wireDirectives(el).get('model').value;
      var modelValue = getValue(component.data, modelString); // Don't manually set file input's values.

      if (el.tagName.toLowerCase() === 'input' && el.type === 'file') return;
      this.setInputValue(el, modelValue);
    },
    setInputValue: function setInputValue(el, value) {
      store.callHook('interceptWireModelSetValue', value, el);

      if (el.type === 'radio') {
        el.checked = el.value == value;
      } else if (el.type === 'checkbox') {
        if (Array.isArray(value)) {
          // I'm purposely not using Array.includes here because it's
          // strict, and because of Numeric/String mis-casting, I
          // want the "includes" to be "fuzzy".
          var valueFound = false;
          value.forEach(function (val) {
            if (val == el.value) {
              valueFound = true;
            }
          });
          el.checked = valueFound;
        } else {
          el.checked = !!value;
        }
      } else if (el.tagName === 'SELECT') {
        this.updateSelect(el, value);
      } else {
        value = value === undefined ? '' : value;
        el.value = value;
      }
    },
    getSelectValues: function getSelectValues(el) {
      return Array.from(el.options).filter(function (option) {
        return option.selected;
      }).map(function (option) {
        return option.value || option.text;
      });
    },
    updateSelect: function updateSelect(el, value) {
      var arrayWrappedValue = [].concat(value).map(function (value) {
        return value + '';
      });
      Array.from(el.options).forEach(function (option) {
        option.selected = arrayWrappedValue.includes(option.value);
      });
    }
  };

  var ceil = Math.ceil;
  var floor = Math.floor;

  // `ToInteger` abstract operation
  // https://tc39.github.io/ecma262/#sec-tointeger
  var toInteger = function (argument) {
    return isNaN(argument = +argument) ? 0 : (argument > 0 ? floor : ceil)(argument);
  };

  // `RequireObjectCoercible` abstract operation
  // https://tc39.github.io/ecma262/#sec-requireobjectcoercible
  var requireObjectCoercible = function (it) {
    if (it == undefined) throw TypeError("Can't call method on " + it);
    return it;
  };

  // `String.prototype.{ codePointAt, at }` methods implementation
  var createMethod = function (CONVERT_TO_STRING) {
    return function ($this, pos) {
      var S = String(requireObjectCoercible($this));
      var position = toInteger(pos);
      var size = S.length;
      var first, second;
      if (position < 0 || position >= size) return CONVERT_TO_STRING ? '' : undefined;
      first = S.charCodeAt(position);
      return first < 0xD800 || first > 0xDBFF || position + 1 === size
        || (second = S.charCodeAt(position + 1)) < 0xDC00 || second > 0xDFFF
          ? CONVERT_TO_STRING ? S.charAt(position) : first
          : CONVERT_TO_STRING ? S.slice(position, position + 2) : (first - 0xD800 << 10) + (second - 0xDC00) + 0x10000;
    };
  };

  var stringMultibyte = {
    // `String.prototype.codePointAt` method
    // https://tc39.github.io/ecma262/#sec-string.prototype.codepointat
    codeAt: createMethod(false),
    // `String.prototype.at` method
    // https://github.com/mathiasbynens/String.prototype.at
    charAt: createMethod(true)
  };

  var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

  function createCommonjsModule(fn, module) {
  	return module = { exports: {} }, fn(module, module.exports), module.exports;
  }

  var check = function (it) {
    return it && it.Math == Math && it;
  };

  // https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
  var global_1 =
    // eslint-disable-next-line no-undef
    check(typeof globalThis == 'object' && globalThis) ||
    check(typeof window == 'object' && window) ||
    check(typeof self == 'object' && self) ||
    check(typeof commonjsGlobal == 'object' && commonjsGlobal) ||
    // eslint-disable-next-line no-new-func
    Function('return this')();

  var fails = function (exec) {
    try {
      return !!exec();
    } catch (error) {
      return true;
    }
  };

  // Thank's IE8 for his funny defineProperty
  var descriptors = !fails(function () {
    return Object.defineProperty({}, 1, { get: function () { return 7; } })[1] != 7;
  });

  var isObject = function (it) {
    return typeof it === 'object' ? it !== null : typeof it === 'function';
  };

  var document$1 = global_1.document;
  // typeof document.createElement is 'object' in old IE
  var EXISTS = isObject(document$1) && isObject(document$1.createElement);

  var documentCreateElement = function (it) {
    return EXISTS ? document$1.createElement(it) : {};
  };

  // Thank's IE8 for his funny defineProperty
  var ie8DomDefine = !descriptors && !fails(function () {
    return Object.defineProperty(documentCreateElement('div'), 'a', {
      get: function () { return 7; }
    }).a != 7;
  });

  var anObject = function (it) {
    if (!isObject(it)) {
      throw TypeError(String(it) + ' is not an object');
    } return it;
  };

  // `ToPrimitive` abstract operation
  // https://tc39.github.io/ecma262/#sec-toprimitive
  // instead of the ES6 spec version, we didn't implement @@toPrimitive case
  // and the second argument - flag - preferred type is a string
  var toPrimitive = function (input, PREFERRED_STRING) {
    if (!isObject(input)) return input;
    var fn, val;
    if (PREFERRED_STRING && typeof (fn = input.toString) == 'function' && !isObject(val = fn.call(input))) return val;
    if (typeof (fn = input.valueOf) == 'function' && !isObject(val = fn.call(input))) return val;
    if (!PREFERRED_STRING && typeof (fn = input.toString) == 'function' && !isObject(val = fn.call(input))) return val;
    throw TypeError("Can't convert object to primitive value");
  };

  var nativeDefineProperty = Object.defineProperty;

  // `Object.defineProperty` method
  // https://tc39.github.io/ecma262/#sec-object.defineproperty
  var f = descriptors ? nativeDefineProperty : function defineProperty(O, P, Attributes) {
    anObject(O);
    P = toPrimitive(P, true);
    anObject(Attributes);
    if (ie8DomDefine) try {
      return nativeDefineProperty(O, P, Attributes);
    } catch (error) { /* empty */ }
    if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported');
    if ('value' in Attributes) O[P] = Attributes.value;
    return O;
  };

  var objectDefineProperty = {
  	f: f
  };

  var createPropertyDescriptor = function (bitmap, value) {
    return {
      enumerable: !(bitmap & 1),
      configurable: !(bitmap & 2),
      writable: !(bitmap & 4),
      value: value
    };
  };

  var createNonEnumerableProperty = descriptors ? function (object, key, value) {
    return objectDefineProperty.f(object, key, createPropertyDescriptor(1, value));
  } : function (object, key, value) {
    object[key] = value;
    return object;
  };

  var setGlobal = function (key, value) {
    try {
      createNonEnumerableProperty(global_1, key, value);
    } catch (error) {
      global_1[key] = value;
    } return value;
  };

  var SHARED = '__core-js_shared__';
  var store$1 = global_1[SHARED] || setGlobal(SHARED, {});

  var sharedStore = store$1;

  var functionToString = Function.toString;

  // this helper broken in `3.4.1-3.4.4`, so we can't use `shared` helper
  if (typeof sharedStore.inspectSource != 'function') {
    sharedStore.inspectSource = function (it) {
      return functionToString.call(it);
    };
  }

  var inspectSource = sharedStore.inspectSource;

  var WeakMap = global_1.WeakMap;

  var nativeWeakMap = typeof WeakMap === 'function' && /native code/.test(inspectSource(WeakMap));

  var hasOwnProperty = {}.hasOwnProperty;

  var has = function (it, key) {
    return hasOwnProperty.call(it, key);
  };

  var shared = createCommonjsModule(function (module) {
  (module.exports = function (key, value) {
    return sharedStore[key] || (sharedStore[key] = value !== undefined ? value : {});
  })('versions', []).push({
    version: '3.6.5',
    mode:  'global',
    copyright: '© 2020 Denis Pushkarev (zloirock.ru)'
  });
  });

  var id = 0;
  var postfix = Math.random();

  var uid = function (key) {
    return 'Symbol(' + String(key === undefined ? '' : key) + ')_' + (++id + postfix).toString(36);
  };

  var keys = shared('keys');

  var sharedKey = function (key) {
    return keys[key] || (keys[key] = uid(key));
  };

  var hiddenKeys = {};

  var WeakMap$1 = global_1.WeakMap;
  var set, get, has$1;

  var enforce = function (it) {
    return has$1(it) ? get(it) : set(it, {});
  };

  var getterFor = function (TYPE) {
    return function (it) {
      var state;
      if (!isObject(it) || (state = get(it)).type !== TYPE) {
        throw TypeError('Incompatible receiver, ' + TYPE + ' required');
      } return state;
    };
  };

  if (nativeWeakMap) {
    var store$2 = new WeakMap$1();
    var wmget = store$2.get;
    var wmhas = store$2.has;
    var wmset = store$2.set;
    set = function (it, metadata) {
      wmset.call(store$2, it, metadata);
      return metadata;
    };
    get = function (it) {
      return wmget.call(store$2, it) || {};
    };
    has$1 = function (it) {
      return wmhas.call(store$2, it);
    };
  } else {
    var STATE = sharedKey('state');
    hiddenKeys[STATE] = true;
    set = function (it, metadata) {
      createNonEnumerableProperty(it, STATE, metadata);
      return metadata;
    };
    get = function (it) {
      return has(it, STATE) ? it[STATE] : {};
    };
    has$1 = function (it) {
      return has(it, STATE);
    };
  }

  var internalState = {
    set: set,
    get: get,
    has: has$1,
    enforce: enforce,
    getterFor: getterFor
  };

  var nativePropertyIsEnumerable = {}.propertyIsEnumerable;
  var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

  // Nashorn ~ JDK8 bug
  var NASHORN_BUG = getOwnPropertyDescriptor && !nativePropertyIsEnumerable.call({ 1: 2 }, 1);

  // `Object.prototype.propertyIsEnumerable` method implementation
  // https://tc39.github.io/ecma262/#sec-object.prototype.propertyisenumerable
  var f$1 = NASHORN_BUG ? function propertyIsEnumerable(V) {
    var descriptor = getOwnPropertyDescriptor(this, V);
    return !!descriptor && descriptor.enumerable;
  } : nativePropertyIsEnumerable;

  var objectPropertyIsEnumerable = {
  	f: f$1
  };

  var toString = {}.toString;

  var classofRaw = function (it) {
    return toString.call(it).slice(8, -1);
  };

  var split$1 = ''.split;

  // fallback for non-array-like ES3 and non-enumerable old V8 strings
  var indexedObject = fails(function () {
    // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
    // eslint-disable-next-line no-prototype-builtins
    return !Object('z').propertyIsEnumerable(0);
  }) ? function (it) {
    return classofRaw(it) == 'String' ? split$1.call(it, '') : Object(it);
  } : Object;

  // toObject with fallback for non-array-like ES3 strings



  var toIndexedObject = function (it) {
    return indexedObject(requireObjectCoercible(it));
  };

  var nativeGetOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

  // `Object.getOwnPropertyDescriptor` method
  // https://tc39.github.io/ecma262/#sec-object.getownpropertydescriptor
  var f$2 = descriptors ? nativeGetOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {
    O = toIndexedObject(O);
    P = toPrimitive(P, true);
    if (ie8DomDefine) try {
      return nativeGetOwnPropertyDescriptor(O, P);
    } catch (error) { /* empty */ }
    if (has(O, P)) return createPropertyDescriptor(!objectPropertyIsEnumerable.f.call(O, P), O[P]);
  };

  var objectGetOwnPropertyDescriptor = {
  	f: f$2
  };

  var redefine = createCommonjsModule(function (module) {
  var getInternalState = internalState.get;
  var enforceInternalState = internalState.enforce;
  var TEMPLATE = String(String).split('String');

  (module.exports = function (O, key, value, options) {
    var unsafe = options ? !!options.unsafe : false;
    var simple = options ? !!options.enumerable : false;
    var noTargetGet = options ? !!options.noTargetGet : false;
    if (typeof value == 'function') {
      if (typeof key == 'string' && !has(value, 'name')) createNonEnumerableProperty(value, 'name', key);
      enforceInternalState(value).source = TEMPLATE.join(typeof key == 'string' ? key : '');
    }
    if (O === global_1) {
      if (simple) O[key] = value;
      else setGlobal(key, value);
      return;
    } else if (!unsafe) {
      delete O[key];
    } else if (!noTargetGet && O[key]) {
      simple = true;
    }
    if (simple) O[key] = value;
    else createNonEnumerableProperty(O, key, value);
  // add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
  })(Function.prototype, 'toString', function toString() {
    return typeof this == 'function' && getInternalState(this).source || inspectSource(this);
  });
  });

  var path = global_1;

  var aFunction = function (variable) {
    return typeof variable == 'function' ? variable : undefined;
  };

  var getBuiltIn = function (namespace, method) {
    return arguments.length < 2 ? aFunction(path[namespace]) || aFunction(global_1[namespace])
      : path[namespace] && path[namespace][method] || global_1[namespace] && global_1[namespace][method];
  };

  var min = Math.min;

  // `ToLength` abstract operation
  // https://tc39.github.io/ecma262/#sec-tolength
  var toLength = function (argument) {
    return argument > 0 ? min(toInteger(argument), 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991
  };

  var max = Math.max;
  var min$1 = Math.min;

  // Helper for a popular repeating case of the spec:
  // Let integer be ? ToInteger(index).
  // If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).
  var toAbsoluteIndex = function (index, length) {
    var integer = toInteger(index);
    return integer < 0 ? max(integer + length, 0) : min$1(integer, length);
  };

  // `Array.prototype.{ indexOf, includes }` methods implementation
  var createMethod$1 = function (IS_INCLUDES) {
    return function ($this, el, fromIndex) {
      var O = toIndexedObject($this);
      var length = toLength(O.length);
      var index = toAbsoluteIndex(fromIndex, length);
      var value;
      // Array#includes uses SameValueZero equality algorithm
      // eslint-disable-next-line no-self-compare
      if (IS_INCLUDES && el != el) while (length > index) {
        value = O[index++];
        // eslint-disable-next-line no-self-compare
        if (value != value) return true;
      // Array#indexOf ignores holes, Array#includes - not
      } else for (;length > index; index++) {
        if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
      } return !IS_INCLUDES && -1;
    };
  };

  var arrayIncludes = {
    // `Array.prototype.includes` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.includes
    includes: createMethod$1(true),
    // `Array.prototype.indexOf` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.indexof
    indexOf: createMethod$1(false)
  };

  var indexOf = arrayIncludes.indexOf;


  var objectKeysInternal = function (object, names) {
    var O = toIndexedObject(object);
    var i = 0;
    var result = [];
    var key;
    for (key in O) !has(hiddenKeys, key) && has(O, key) && result.push(key);
    // Don't enum bug & hidden keys
    while (names.length > i) if (has(O, key = names[i++])) {
      ~indexOf(result, key) || result.push(key);
    }
    return result;
  };

  // IE8- don't enum bug keys
  var enumBugKeys = [
    'constructor',
    'hasOwnProperty',
    'isPrototypeOf',
    'propertyIsEnumerable',
    'toLocaleString',
    'toString',
    'valueOf'
  ];

  var hiddenKeys$1 = enumBugKeys.concat('length', 'prototype');

  // `Object.getOwnPropertyNames` method
  // https://tc39.github.io/ecma262/#sec-object.getownpropertynames
  var f$3 = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
    return objectKeysInternal(O, hiddenKeys$1);
  };

  var objectGetOwnPropertyNames = {
  	f: f$3
  };

  var f$4 = Object.getOwnPropertySymbols;

  var objectGetOwnPropertySymbols = {
  	f: f$4
  };

  // all object keys, includes non-enumerable and symbols
  var ownKeys$1 = getBuiltIn('Reflect', 'ownKeys') || function ownKeys(it) {
    var keys = objectGetOwnPropertyNames.f(anObject(it));
    var getOwnPropertySymbols = objectGetOwnPropertySymbols.f;
    return getOwnPropertySymbols ? keys.concat(getOwnPropertySymbols(it)) : keys;
  };

  var copyConstructorProperties = function (target, source) {
    var keys = ownKeys$1(source);
    var defineProperty = objectDefineProperty.f;
    var getOwnPropertyDescriptor = objectGetOwnPropertyDescriptor.f;
    for (var i = 0; i < keys.length; i++) {
      var key = keys[i];
      if (!has(target, key)) defineProperty(target, key, getOwnPropertyDescriptor(source, key));
    }
  };

  var replacement = /#|\.prototype\./;

  var isForced = function (feature, detection) {
    var value = data[normalize(feature)];
    return value == POLYFILL ? true
      : value == NATIVE ? false
      : typeof detection == 'function' ? fails(detection)
      : !!detection;
  };

  var normalize = isForced.normalize = function (string) {
    return String(string).replace(replacement, '.').toLowerCase();
  };

  var data = isForced.data = {};
  var NATIVE = isForced.NATIVE = 'N';
  var POLYFILL = isForced.POLYFILL = 'P';

  var isForced_1 = isForced;

  var getOwnPropertyDescriptor$1 = objectGetOwnPropertyDescriptor.f;






  /*
    options.target      - name of the target object
    options.global      - target is the global object
    options.stat        - export as static methods of target
    options.proto       - export as prototype methods of target
    options.real        - real prototype method for the `pure` version
    options.forced      - export even if the native feature is available
    options.bind        - bind methods to the target, required for the `pure` version
    options.wrap        - wrap constructors to preventing global pollution, required for the `pure` version
    options.unsafe      - use the simple assignment of property instead of delete + defineProperty
    options.sham        - add a flag to not completely full polyfills
    options.enumerable  - export as enumerable property
    options.noTargetGet - prevent calling a getter on target
  */
  var _export = function (options, source) {
    var TARGET = options.target;
    var GLOBAL = options.global;
    var STATIC = options.stat;
    var FORCED, target, key, targetProperty, sourceProperty, descriptor;
    if (GLOBAL) {
      target = global_1;
    } else if (STATIC) {
      target = global_1[TARGET] || setGlobal(TARGET, {});
    } else {
      target = (global_1[TARGET] || {}).prototype;
    }
    if (target) for (key in source) {
      sourceProperty = source[key];
      if (options.noTargetGet) {
        descriptor = getOwnPropertyDescriptor$1(target, key);
        targetProperty = descriptor && descriptor.value;
      } else targetProperty = target[key];
      FORCED = isForced_1(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced);
      // contained in target
      if (!FORCED && targetProperty !== undefined) {
        if (typeof sourceProperty === typeof targetProperty) continue;
        copyConstructorProperties(sourceProperty, targetProperty);
      }
      // add a flag to not completely full polyfills
      if (options.sham || (targetProperty && targetProperty.sham)) {
        createNonEnumerableProperty(sourceProperty, 'sham', true);
      }
      // extend global
      redefine(target, key, sourceProperty, options);
    }
  };

  // `ToObject` abstract operation
  // https://tc39.github.io/ecma262/#sec-toobject
  var toObject = function (argument) {
    return Object(requireObjectCoercible(argument));
  };

  var correctPrototypeGetter = !fails(function () {
    function F() { /* empty */ }
    F.prototype.constructor = null;
    return Object.getPrototypeOf(new F()) !== F.prototype;
  });

  var IE_PROTO = sharedKey('IE_PROTO');
  var ObjectPrototype = Object.prototype;

  // `Object.getPrototypeOf` method
  // https://tc39.github.io/ecma262/#sec-object.getprototypeof
  var objectGetPrototypeOf = correctPrototypeGetter ? Object.getPrototypeOf : function (O) {
    O = toObject(O);
    if (has(O, IE_PROTO)) return O[IE_PROTO];
    if (typeof O.constructor == 'function' && O instanceof O.constructor) {
      return O.constructor.prototype;
    } return O instanceof Object ? ObjectPrototype : null;
  };

  var nativeSymbol = !!Object.getOwnPropertySymbols && !fails(function () {
    // Chrome 38 Symbol has incorrect toString conversion
    // eslint-disable-next-line no-undef
    return !String(Symbol());
  });

  var useSymbolAsUid = nativeSymbol
    // eslint-disable-next-line no-undef
    && !Symbol.sham
    // eslint-disable-next-line no-undef
    && typeof Symbol.iterator == 'symbol';

  var WellKnownSymbolsStore = shared('wks');
  var Symbol$1 = global_1.Symbol;
  var createWellKnownSymbol = useSymbolAsUid ? Symbol$1 : Symbol$1 && Symbol$1.withoutSetter || uid;

  var wellKnownSymbol = function (name) {
    if (!has(WellKnownSymbolsStore, name)) {
      if (nativeSymbol && has(Symbol$1, name)) WellKnownSymbolsStore[name] = Symbol$1[name];
      else WellKnownSymbolsStore[name] = createWellKnownSymbol('Symbol.' + name);
    } return WellKnownSymbolsStore[name];
  };

  var ITERATOR = wellKnownSymbol('iterator');
  var BUGGY_SAFARI_ITERATORS = false;

  var returnThis = function () { return this; };

  // `%IteratorPrototype%` object
  // https://tc39.github.io/ecma262/#sec-%iteratorprototype%-object
  var IteratorPrototype, PrototypeOfArrayIteratorPrototype, arrayIterator;

  if ([].keys) {
    arrayIterator = [].keys();
    // Safari 8 has buggy iterators w/o `next`
    if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS = true;
    else {
      PrototypeOfArrayIteratorPrototype = objectGetPrototypeOf(objectGetPrototypeOf(arrayIterator));
      if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype = PrototypeOfArrayIteratorPrototype;
    }
  }

  if (IteratorPrototype == undefined) IteratorPrototype = {};

  // 25.1.2.1.1 %IteratorPrototype%[@@iterator]()
  if ( !has(IteratorPrototype, ITERATOR)) {
    createNonEnumerableProperty(IteratorPrototype, ITERATOR, returnThis);
  }

  var iteratorsCore = {
    IteratorPrototype: IteratorPrototype,
    BUGGY_SAFARI_ITERATORS: BUGGY_SAFARI_ITERATORS
  };

  // `Object.keys` method
  // https://tc39.github.io/ecma262/#sec-object.keys
  var objectKeys = Object.keys || function keys(O) {
    return objectKeysInternal(O, enumBugKeys);
  };

  // `Object.defineProperties` method
  // https://tc39.github.io/ecma262/#sec-object.defineproperties
  var objectDefineProperties = descriptors ? Object.defineProperties : function defineProperties(O, Properties) {
    anObject(O);
    var keys = objectKeys(Properties);
    var length = keys.length;
    var index = 0;
    var key;
    while (length > index) objectDefineProperty.f(O, key = keys[index++], Properties[key]);
    return O;
  };

  var html = getBuiltIn('document', 'documentElement');

  var GT = '>';
  var LT = '<';
  var PROTOTYPE = 'prototype';
  var SCRIPT = 'script';
  var IE_PROTO$1 = sharedKey('IE_PROTO');

  var EmptyConstructor = function () { /* empty */ };

  var scriptTag = function (content) {
    return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
  };

  // Create object with fake `null` prototype: use ActiveX Object with cleared prototype
  var NullProtoObjectViaActiveX = function (activeXDocument) {
    activeXDocument.write(scriptTag(''));
    activeXDocument.close();
    var temp = activeXDocument.parentWindow.Object;
    activeXDocument = null; // avoid memory leak
    return temp;
  };

  // Create object with fake `null` prototype: use iframe Object with cleared prototype
  var NullProtoObjectViaIFrame = function () {
    // Thrash, waste and sodomy: IE GC bug
    var iframe = documentCreateElement('iframe');
    var JS = 'java' + SCRIPT + ':';
    var iframeDocument;
    iframe.style.display = 'none';
    html.appendChild(iframe);
    // https://github.com/zloirock/core-js/issues/475
    iframe.src = String(JS);
    iframeDocument = iframe.contentWindow.document;
    iframeDocument.open();
    iframeDocument.write(scriptTag('document.F=Object'));
    iframeDocument.close();
    return iframeDocument.F;
  };

  // Check for document.domain and active x support
  // No need to use active x approach when document.domain is not set
  // see https://github.com/es-shims/es5-shim/issues/150
  // variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
  // avoid IE GC bug
  var activeXDocument;
  var NullProtoObject = function () {
    try {
      /* global ActiveXObject */
      activeXDocument = document.domain && new ActiveXObject('htmlfile');
    } catch (error) { /* ignore */ }
    NullProtoObject = activeXDocument ? NullProtoObjectViaActiveX(activeXDocument) : NullProtoObjectViaIFrame();
    var length = enumBugKeys.length;
    while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];
    return NullProtoObject();
  };

  hiddenKeys[IE_PROTO$1] = true;

  // `Object.create` method
  // https://tc39.github.io/ecma262/#sec-object.create
  var objectCreate = Object.create || function create(O, Properties) {
    var result;
    if (O !== null) {
      EmptyConstructor[PROTOTYPE] = anObject(O);
      result = new EmptyConstructor();
      EmptyConstructor[PROTOTYPE] = null;
      // add "__proto__" for Object.getPrototypeOf polyfill
      result[IE_PROTO$1] = O;
    } else result = NullProtoObject();
    return Properties === undefined ? result : objectDefineProperties(result, Properties);
  };

  var defineProperty = objectDefineProperty.f;



  var TO_STRING_TAG = wellKnownSymbol('toStringTag');

  var setToStringTag = function (it, TAG, STATIC) {
    if (it && !has(it = STATIC ? it : it.prototype, TO_STRING_TAG)) {
      defineProperty(it, TO_STRING_TAG, { configurable: true, value: TAG });
    }
  };

  var iterators = {};

  var IteratorPrototype$1 = iteratorsCore.IteratorPrototype;





  var returnThis$1 = function () { return this; };

  var createIteratorConstructor = function (IteratorConstructor, NAME, next) {
    var TO_STRING_TAG = NAME + ' Iterator';
    IteratorConstructor.prototype = objectCreate(IteratorPrototype$1, { next: createPropertyDescriptor(1, next) });
    setToStringTag(IteratorConstructor, TO_STRING_TAG, false);
    iterators[TO_STRING_TAG] = returnThis$1;
    return IteratorConstructor;
  };

  var aPossiblePrototype = function (it) {
    if (!isObject(it) && it !== null) {
      throw TypeError("Can't set " + String(it) + ' as a prototype');
    } return it;
  };

  // `Object.setPrototypeOf` method
  // https://tc39.github.io/ecma262/#sec-object.setprototypeof
  // Works with __proto__ only. Old v8 can't work with null proto objects.
  /* eslint-disable no-proto */
  var objectSetPrototypeOf = Object.setPrototypeOf || ('__proto__' in {} ? function () {
    var CORRECT_SETTER = false;
    var test = {};
    var setter;
    try {
      setter = Object.getOwnPropertyDescriptor(Object.prototype, '__proto__').set;
      setter.call(test, []);
      CORRECT_SETTER = test instanceof Array;
    } catch (error) { /* empty */ }
    return function setPrototypeOf(O, proto) {
      anObject(O);
      aPossiblePrototype(proto);
      if (CORRECT_SETTER) setter.call(O, proto);
      else O.__proto__ = proto;
      return O;
    };
  }() : undefined);

  var IteratorPrototype$2 = iteratorsCore.IteratorPrototype;
  var BUGGY_SAFARI_ITERATORS$1 = iteratorsCore.BUGGY_SAFARI_ITERATORS;
  var ITERATOR$1 = wellKnownSymbol('iterator');
  var KEYS = 'keys';
  var VALUES = 'values';
  var ENTRIES = 'entries';

  var returnThis$2 = function () { return this; };

  var defineIterator = function (Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
    createIteratorConstructor(IteratorConstructor, NAME, next);

    var getIterationMethod = function (KIND) {
      if (KIND === DEFAULT && defaultIterator) return defaultIterator;
      if (!BUGGY_SAFARI_ITERATORS$1 && KIND in IterablePrototype) return IterablePrototype[KIND];
      switch (KIND) {
        case KEYS: return function keys() { return new IteratorConstructor(this, KIND); };
        case VALUES: return function values() { return new IteratorConstructor(this, KIND); };
        case ENTRIES: return function entries() { return new IteratorConstructor(this, KIND); };
      } return function () { return new IteratorConstructor(this); };
    };

    var TO_STRING_TAG = NAME + ' Iterator';
    var INCORRECT_VALUES_NAME = false;
    var IterablePrototype = Iterable.prototype;
    var nativeIterator = IterablePrototype[ITERATOR$1]
      || IterablePrototype['@@iterator']
      || DEFAULT && IterablePrototype[DEFAULT];
    var defaultIterator = !BUGGY_SAFARI_ITERATORS$1 && nativeIterator || getIterationMethod(DEFAULT);
    var anyNativeIterator = NAME == 'Array' ? IterablePrototype.entries || nativeIterator : nativeIterator;
    var CurrentIteratorPrototype, methods, KEY;

    // fix native
    if (anyNativeIterator) {
      CurrentIteratorPrototype = objectGetPrototypeOf(anyNativeIterator.call(new Iterable()));
      if (IteratorPrototype$2 !== Object.prototype && CurrentIteratorPrototype.next) {
        if ( objectGetPrototypeOf(CurrentIteratorPrototype) !== IteratorPrototype$2) {
          if (objectSetPrototypeOf) {
            objectSetPrototypeOf(CurrentIteratorPrototype, IteratorPrototype$2);
          } else if (typeof CurrentIteratorPrototype[ITERATOR$1] != 'function') {
            createNonEnumerableProperty(CurrentIteratorPrototype, ITERATOR$1, returnThis$2);
          }
        }
        // Set @@toStringTag to native iterators
        setToStringTag(CurrentIteratorPrototype, TO_STRING_TAG, true);
      }
    }

    // fix Array#{values, @@iterator}.name in V8 / FF
    if (DEFAULT == VALUES && nativeIterator && nativeIterator.name !== VALUES) {
      INCORRECT_VALUES_NAME = true;
      defaultIterator = function values() { return nativeIterator.call(this); };
    }

    // define iterator
    if ( IterablePrototype[ITERATOR$1] !== defaultIterator) {
      createNonEnumerableProperty(IterablePrototype, ITERATOR$1, defaultIterator);
    }
    iterators[NAME] = defaultIterator;

    // export additional methods
    if (DEFAULT) {
      methods = {
        values: getIterationMethod(VALUES),
        keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
        entries: getIterationMethod(ENTRIES)
      };
      if (FORCED) for (KEY in methods) {
        if (BUGGY_SAFARI_ITERATORS$1 || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
          redefine(IterablePrototype, KEY, methods[KEY]);
        }
      } else _export({ target: NAME, proto: true, forced: BUGGY_SAFARI_ITERATORS$1 || INCORRECT_VALUES_NAME }, methods);
    }

    return methods;
  };

  var charAt = stringMultibyte.charAt;



  var STRING_ITERATOR = 'String Iterator';
  var setInternalState = internalState.set;
  var getInternalState = internalState.getterFor(STRING_ITERATOR);

  // `String.prototype[@@iterator]` method
  // https://tc39.github.io/ecma262/#sec-string.prototype-@@iterator
  defineIterator(String, 'String', function (iterated) {
    setInternalState(this, {
      type: STRING_ITERATOR,
      string: String(iterated),
      index: 0
    });
  // `%StringIteratorPrototype%.next` method
  // https://tc39.github.io/ecma262/#sec-%stringiteratorprototype%.next
  }, function next() {
    var state = getInternalState(this);
    var string = state.string;
    var index = state.index;
    var point;
    if (index >= string.length) return { value: undefined, done: true };
    point = charAt(string, index);
    state.index += point.length;
    return { value: point, done: false };
  });

  var aFunction$1 = function (it) {
    if (typeof it != 'function') {
      throw TypeError(String(it) + ' is not a function');
    } return it;
  };

  // optional / simple context binding
  var functionBindContext = function (fn, that, length) {
    aFunction$1(fn);
    if (that === undefined) return fn;
    switch (length) {
      case 0: return function () {
        return fn.call(that);
      };
      case 1: return function (a) {
        return fn.call(that, a);
      };
      case 2: return function (a, b) {
        return fn.call(that, a, b);
      };
      case 3: return function (a, b, c) {
        return fn.call(that, a, b, c);
      };
    }
    return function (/* ...args */) {
      return fn.apply(that, arguments);
    };
  };

  // call something on iterator step with safe closing on error
  var callWithSafeIterationClosing = function (iterator, fn, value, ENTRIES) {
    try {
      return ENTRIES ? fn(anObject(value)[0], value[1]) : fn(value);
    // 7.4.6 IteratorClose(iterator, completion)
    } catch (error) {
      var returnMethod = iterator['return'];
      if (returnMethod !== undefined) anObject(returnMethod.call(iterator));
      throw error;
    }
  };

  var ITERATOR$2 = wellKnownSymbol('iterator');
  var ArrayPrototype = Array.prototype;

  // check on default Array iterator
  var isArrayIteratorMethod = function (it) {
    return it !== undefined && (iterators.Array === it || ArrayPrototype[ITERATOR$2] === it);
  };

  var createProperty = function (object, key, value) {
    var propertyKey = toPrimitive(key);
    if (propertyKey in object) objectDefineProperty.f(object, propertyKey, createPropertyDescriptor(0, value));
    else object[propertyKey] = value;
  };

  var TO_STRING_TAG$1 = wellKnownSymbol('toStringTag');
  var test = {};

  test[TO_STRING_TAG$1] = 'z';

  var toStringTagSupport = String(test) === '[object z]';

  var TO_STRING_TAG$2 = wellKnownSymbol('toStringTag');
  // ES3 wrong here
  var CORRECT_ARGUMENTS = classofRaw(function () { return arguments; }()) == 'Arguments';

  // fallback for IE11 Script Access Denied error
  var tryGet = function (it, key) {
    try {
      return it[key];
    } catch (error) { /* empty */ }
  };

  // getting tag from ES6+ `Object.prototype.toString`
  var classof = toStringTagSupport ? classofRaw : function (it) {
    var O, tag, result;
    return it === undefined ? 'Undefined' : it === null ? 'Null'
      // @@toStringTag case
      : typeof (tag = tryGet(O = Object(it), TO_STRING_TAG$2)) == 'string' ? tag
      // builtinTag case
      : CORRECT_ARGUMENTS ? classofRaw(O)
      // ES3 arguments fallback
      : (result = classofRaw(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : result;
  };

  var ITERATOR$3 = wellKnownSymbol('iterator');

  var getIteratorMethod = function (it) {
    if (it != undefined) return it[ITERATOR$3]
      || it['@@iterator']
      || iterators[classof(it)];
  };

  // `Array.from` method implementation
  // https://tc39.github.io/ecma262/#sec-array.from
  var arrayFrom = function from(arrayLike /* , mapfn = undefined, thisArg = undefined */) {
    var O = toObject(arrayLike);
    var C = typeof this == 'function' ? this : Array;
    var argumentsLength = arguments.length;
    var mapfn = argumentsLength > 1 ? arguments[1] : undefined;
    var mapping = mapfn !== undefined;
    var iteratorMethod = getIteratorMethod(O);
    var index = 0;
    var length, result, step, iterator, next, value;
    if (mapping) mapfn = functionBindContext(mapfn, argumentsLength > 2 ? arguments[2] : undefined, 2);
    // if the target is not iterable or it's an array with the default iterator - use a simple case
    if (iteratorMethod != undefined && !(C == Array && isArrayIteratorMethod(iteratorMethod))) {
      iterator = iteratorMethod.call(O);
      next = iterator.next;
      result = new C();
      for (;!(step = next.call(iterator)).done; index++) {
        value = mapping ? callWithSafeIterationClosing(iterator, mapfn, [step.value, index], true) : step.value;
        createProperty(result, index, value);
      }
    } else {
      length = toLength(O.length);
      result = new C(length);
      for (;length > index; index++) {
        value = mapping ? mapfn(O[index], index) : O[index];
        createProperty(result, index, value);
      }
    }
    result.length = index;
    return result;
  };

  var ITERATOR$4 = wellKnownSymbol('iterator');
  var SAFE_CLOSING = false;

  try {
    var called = 0;
    var iteratorWithReturn = {
      next: function () {
        return { done: !!called++ };
      },
      'return': function () {
        SAFE_CLOSING = true;
      }
    };
    iteratorWithReturn[ITERATOR$4] = function () {
      return this;
    };
    // eslint-disable-next-line no-throw-literal
    Array.from(iteratorWithReturn, function () { throw 2; });
  } catch (error) { /* empty */ }

  var checkCorrectnessOfIteration = function (exec, SKIP_CLOSING) {
    if (!SKIP_CLOSING && !SAFE_CLOSING) return false;
    var ITERATION_SUPPORT = false;
    try {
      var object = {};
      object[ITERATOR$4] = function () {
        return {
          next: function () {
            return { done: ITERATION_SUPPORT = true };
          }
        };
      };
      exec(object);
    } catch (error) { /* empty */ }
    return ITERATION_SUPPORT;
  };

  var INCORRECT_ITERATION = !checkCorrectnessOfIteration(function (iterable) {
    Array.from(iterable);
  });

  // `Array.from` method
  // https://tc39.github.io/ecma262/#sec-array.from
  _export({ target: 'Array', stat: true, forced: INCORRECT_ITERATION }, {
    from: arrayFrom
  });

  var from_1 = path.Array.from;

  var UNSCOPABLES = wellKnownSymbol('unscopables');
  var ArrayPrototype$1 = Array.prototype;

  // Array.prototype[@@unscopables]
  // https://tc39.github.io/ecma262/#sec-array.prototype-@@unscopables
  if (ArrayPrototype$1[UNSCOPABLES] == undefined) {
    objectDefineProperty.f(ArrayPrototype$1, UNSCOPABLES, {
      configurable: true,
      value: objectCreate(null)
    });
  }

  // add a key to Array.prototype[@@unscopables]
  var addToUnscopables = function (key) {
    ArrayPrototype$1[UNSCOPABLES][key] = true;
  };

  var defineProperty$1 = Object.defineProperty;
  var cache = {};

  var thrower = function (it) { throw it; };

  var arrayMethodUsesToLength = function (METHOD_NAME, options) {
    if (has(cache, METHOD_NAME)) return cache[METHOD_NAME];
    if (!options) options = {};
    var method = [][METHOD_NAME];
    var ACCESSORS = has(options, 'ACCESSORS') ? options.ACCESSORS : false;
    var argument0 = has(options, 0) ? options[0] : thrower;
    var argument1 = has(options, 1) ? options[1] : undefined;

    return cache[METHOD_NAME] = !!method && !fails(function () {
      if (ACCESSORS && !descriptors) return true;
      var O = { length: -1 };

      if (ACCESSORS) defineProperty$1(O, 1, { enumerable: true, get: thrower });
      else O[1] = 1;

      method.call(O, argument0, argument1);
    });
  };

  var $includes = arrayIncludes.includes;



  var USES_TO_LENGTH = arrayMethodUsesToLength('indexOf', { ACCESSORS: true, 1: 0 });

  // `Array.prototype.includes` method
  // https://tc39.github.io/ecma262/#sec-array.prototype.includes
  _export({ target: 'Array', proto: true, forced: !USES_TO_LENGTH }, {
    includes: function includes(el /* , fromIndex = 0 */) {
      return $includes(this, el, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  // https://tc39.github.io/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables('includes');

  var call = Function.call;

  var entryUnbind = function (CONSTRUCTOR, METHOD, length) {
    return functionBindContext(call, global_1[CONSTRUCTOR].prototype[METHOD], length);
  };

  var includes = entryUnbind('Array', 'includes');

  // `IsArray` abstract operation
  // https://tc39.github.io/ecma262/#sec-isarray
  var isArray = Array.isArray || function isArray(arg) {
    return classofRaw(arg) == 'Array';
  };

  // `FlattenIntoArray` abstract operation
  // https://tc39.github.io/proposal-flatMap/#sec-FlattenIntoArray
  var flattenIntoArray = function (target, original, source, sourceLen, start, depth, mapper, thisArg) {
    var targetIndex = start;
    var sourceIndex = 0;
    var mapFn = mapper ? functionBindContext(mapper, thisArg, 3) : false;
    var element;

    while (sourceIndex < sourceLen) {
      if (sourceIndex in source) {
        element = mapFn ? mapFn(source[sourceIndex], sourceIndex, original) : source[sourceIndex];

        if (depth > 0 && isArray(element)) {
          targetIndex = flattenIntoArray(target, original, element, toLength(element.length), targetIndex, depth - 1) - 1;
        } else {
          if (targetIndex >= 0x1FFFFFFFFFFFFF) throw TypeError('Exceed the acceptable array length');
          target[targetIndex] = element;
        }

        targetIndex++;
      }
      sourceIndex++;
    }
    return targetIndex;
  };

  var flattenIntoArray_1 = flattenIntoArray;

  var SPECIES = wellKnownSymbol('species');

  // `ArraySpeciesCreate` abstract operation
  // https://tc39.github.io/ecma262/#sec-arrayspeciescreate
  var arraySpeciesCreate = function (originalArray, length) {
    var C;
    if (isArray(originalArray)) {
      C = originalArray.constructor;
      // cross-realm fallback
      if (typeof C == 'function' && (C === Array || isArray(C.prototype))) C = undefined;
      else if (isObject(C)) {
        C = C[SPECIES];
        if (C === null) C = undefined;
      }
    } return new (C === undefined ? Array : C)(length === 0 ? 0 : length);
  };

  // `Array.prototype.flat` method
  // https://github.com/tc39/proposal-flatMap
  _export({ target: 'Array', proto: true }, {
    flat: function flat(/* depthArg = 1 */) {
      var depthArg = arguments.length ? arguments[0] : undefined;
      var O = toObject(this);
      var sourceLen = toLength(O.length);
      var A = arraySpeciesCreate(O, 0);
      A.length = flattenIntoArray_1(A, O, O, sourceLen, 0, depthArg === undefined ? 1 : toInteger(depthArg));
      return A;
    }
  });

  // this method was added to unscopables after implementation
  // in popular engines, so it's moved to a separate module


  addToUnscopables('flat');

  var flat = entryUnbind('Array', 'flat');

  var push = [].push;

  // `Array.prototype.{ forEach, map, filter, some, every, find, findIndex }` methods implementation
  var createMethod$2 = function (TYPE) {
    var IS_MAP = TYPE == 1;
    var IS_FILTER = TYPE == 2;
    var IS_SOME = TYPE == 3;
    var IS_EVERY = TYPE == 4;
    var IS_FIND_INDEX = TYPE == 6;
    var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
    return function ($this, callbackfn, that, specificCreate) {
      var O = toObject($this);
      var self = indexedObject(O);
      var boundFunction = functionBindContext(callbackfn, that, 3);
      var length = toLength(self.length);
      var index = 0;
      var create = specificCreate || arraySpeciesCreate;
      var target = IS_MAP ? create($this, length) : IS_FILTER ? create($this, 0) : undefined;
      var value, result;
      for (;length > index; index++) if (NO_HOLES || index in self) {
        value = self[index];
        result = boundFunction(value, index, O);
        if (TYPE) {
          if (IS_MAP) target[index] = result; // map
          else if (result) switch (TYPE) {
            case 3: return true;              // some
            case 5: return value;             // find
            case 6: return index;             // findIndex
            case 2: push.call(target, value); // filter
          } else if (IS_EVERY) return false;  // every
        }
      }
      return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
    };
  };

  var arrayIteration = {
    // `Array.prototype.forEach` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.foreach
    forEach: createMethod$2(0),
    // `Array.prototype.map` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.map
    map: createMethod$2(1),
    // `Array.prototype.filter` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.filter
    filter: createMethod$2(2),
    // `Array.prototype.some` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.some
    some: createMethod$2(3),
    // `Array.prototype.every` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.every
    every: createMethod$2(4),
    // `Array.prototype.find` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.find
    find: createMethod$2(5),
    // `Array.prototype.findIndex` method
    // https://tc39.github.io/ecma262/#sec-array.prototype.findIndex
    findIndex: createMethod$2(6)
  };

  var $find = arrayIteration.find;



  var FIND = 'find';
  var SKIPS_HOLES = true;

  var USES_TO_LENGTH$1 = arrayMethodUsesToLength(FIND);

  // Shouldn't skip holes
  if (FIND in []) Array(1)[FIND](function () { SKIPS_HOLES = false; });

  // `Array.prototype.find` method
  // https://tc39.github.io/ecma262/#sec-array.prototype.find
  _export({ target: 'Array', proto: true, forced: SKIPS_HOLES || !USES_TO_LENGTH$1 }, {
    find: function find(callbackfn /* , that = undefined */) {
      return $find(this, callbackfn, arguments.length > 1 ? arguments[1] : undefined);
    }
  });

  // https://tc39.github.io/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables(FIND);

  var find = entryUnbind('Array', 'find');

  var nativeAssign = Object.assign;
  var defineProperty$2 = Object.defineProperty;

  // `Object.assign` method
  // https://tc39.github.io/ecma262/#sec-object.assign
  var objectAssign = !nativeAssign || fails(function () {
    // should have correct order of operations (Edge bug)
    if (descriptors && nativeAssign({ b: 1 }, nativeAssign(defineProperty$2({}, 'a', {
      enumerable: true,
      get: function () {
        defineProperty$2(this, 'b', {
          value: 3,
          enumerable: false
        });
      }
    }), { b: 2 })).b !== 1) return true;
    // should work with symbols and should have deterministic property order (V8 bug)
    var A = {};
    var B = {};
    // eslint-disable-next-line no-undef
    var symbol = Symbol();
    var alphabet = 'abcdefghijklmnopqrst';
    A[symbol] = 7;
    alphabet.split('').forEach(function (chr) { B[chr] = chr; });
    return nativeAssign({}, A)[symbol] != 7 || objectKeys(nativeAssign({}, B)).join('') != alphabet;
  }) ? function assign(target, source) { // eslint-disable-line no-unused-vars
    var T = toObject(target);
    var argumentsLength = arguments.length;
    var index = 1;
    var getOwnPropertySymbols = objectGetOwnPropertySymbols.f;
    var propertyIsEnumerable = objectPropertyIsEnumerable.f;
    while (argumentsLength > index) {
      var S = indexedObject(arguments[index++]);
      var keys = getOwnPropertySymbols ? objectKeys(S).concat(getOwnPropertySymbols(S)) : objectKeys(S);
      var length = keys.length;
      var j = 0;
      var key;
      while (length > j) {
        key = keys[j++];
        if (!descriptors || propertyIsEnumerable.call(S, key)) T[key] = S[key];
      }
    } return T;
  } : nativeAssign;

  // `Object.assign` method
  // https://tc39.github.io/ecma262/#sec-object.assign
  _export({ target: 'Object', stat: true, forced: Object.assign !== objectAssign }, {
    assign: objectAssign
  });

  var assign = path.Object.assign;

  // `Object.prototype.toString` method implementation
  // https://tc39.github.io/ecma262/#sec-object.prototype.tostring
  var objectToString = toStringTagSupport ? {}.toString : function toString() {
    return '[object ' + classof(this) + ']';
  };

  // `Object.prototype.toString` method
  // https://tc39.github.io/ecma262/#sec-object.prototype.tostring
  if (!toStringTagSupport) {
    redefine(Object.prototype, 'toString', objectToString, { unsafe: true });
  }

  // iterable DOM collections
  // flag - `iterable` interface - 'entries', 'keys', 'values', 'forEach' methods
  var domIterables = {
    CSSRuleList: 0,
    CSSStyleDeclaration: 0,
    CSSValueList: 0,
    ClientRectList: 0,
    DOMRectList: 0,
    DOMStringList: 0,
    DOMTokenList: 1,
    DataTransferItemList: 0,
    FileList: 0,
    HTMLAllCollection: 0,
    HTMLCollection: 0,
    HTMLFormElement: 0,
    HTMLSelectElement: 0,
    MediaList: 0,
    MimeTypeArray: 0,
    NamedNodeMap: 0,
    NodeList: 1,
    PaintRequestList: 0,
    Plugin: 0,
    PluginArray: 0,
    SVGLengthList: 0,
    SVGNumberList: 0,
    SVGPathSegList: 0,
    SVGPointList: 0,
    SVGStringList: 0,
    SVGTransformList: 0,
    SourceBufferList: 0,
    StyleSheetList: 0,
    TextTrackCueList: 0,
    TextTrackList: 0,
    TouchList: 0
  };

  var ARRAY_ITERATOR = 'Array Iterator';
  var setInternalState$1 = internalState.set;
  var getInternalState$1 = internalState.getterFor(ARRAY_ITERATOR);

  // `Array.prototype.entries` method
  // https://tc39.github.io/ecma262/#sec-array.prototype.entries
  // `Array.prototype.keys` method
  // https://tc39.github.io/ecma262/#sec-array.prototype.keys
  // `Array.prototype.values` method
  // https://tc39.github.io/ecma262/#sec-array.prototype.values
  // `Array.prototype[@@iterator]` method
  // https://tc39.github.io/ecma262/#sec-array.prototype-@@iterator
  // `CreateArrayIterator` internal method
  // https://tc39.github.io/ecma262/#sec-createarrayiterator
  var es_array_iterator = defineIterator(Array, 'Array', function (iterated, kind) {
    setInternalState$1(this, {
      type: ARRAY_ITERATOR,
      target: toIndexedObject(iterated), // target
      index: 0,                          // next index
      kind: kind                         // kind
    });
  // `%ArrayIteratorPrototype%.next` method
  // https://tc39.github.io/ecma262/#sec-%arrayiteratorprototype%.next
  }, function () {
    var state = getInternalState$1(this);
    var target = state.target;
    var kind = state.kind;
    var index = state.index++;
    if (!target || index >= target.length) {
      state.target = undefined;
      return { value: undefined, done: true };
    }
    if (kind == 'keys') return { value: index, done: false };
    if (kind == 'values') return { value: target[index], done: false };
    return { value: [index, target[index]], done: false };
  }, 'values');

  // argumentsList[@@iterator] is %ArrayProto_values%
  // https://tc39.github.io/ecma262/#sec-createunmappedargumentsobject
  // https://tc39.github.io/ecma262/#sec-createmappedargumentsobject
  iterators.Arguments = iterators.Array;

  // https://tc39.github.io/ecma262/#sec-array.prototype-@@unscopables
  addToUnscopables('keys');
  addToUnscopables('values');
  addToUnscopables('entries');

  var ITERATOR$5 = wellKnownSymbol('iterator');
  var TO_STRING_TAG$3 = wellKnownSymbol('toStringTag');
  var ArrayValues = es_array_iterator.values;

  for (var COLLECTION_NAME in domIterables) {
    var Collection = global_1[COLLECTION_NAME];
    var CollectionPrototype = Collection && Collection.prototype;
    if (CollectionPrototype) {
      // some Chrome versions have non-configurable methods on DOMTokenList
      if (CollectionPrototype[ITERATOR$5] !== ArrayValues) try {
        createNonEnumerableProperty(CollectionPrototype, ITERATOR$5, ArrayValues);
      } catch (error) {
        CollectionPrototype[ITERATOR$5] = ArrayValues;
      }
      if (!CollectionPrototype[TO_STRING_TAG$3]) {
        createNonEnumerableProperty(CollectionPrototype, TO_STRING_TAG$3, COLLECTION_NAME);
      }
      if (domIterables[COLLECTION_NAME]) for (var METHOD_NAME in es_array_iterator) {
        // some Chrome versions have non-configurable methods on DOMTokenList
        if (CollectionPrototype[METHOD_NAME] !== es_array_iterator[METHOD_NAME]) try {
          createNonEnumerableProperty(CollectionPrototype, METHOD_NAME, es_array_iterator[METHOD_NAME]);
        } catch (error) {
          CollectionPrototype[METHOD_NAME] = es_array_iterator[METHOD_NAME];
        }
      }
    }
  }

  var nativePromiseConstructor = global_1.Promise;

  var redefineAll = function (target, src, options) {
    for (var key in src) redefine(target, key, src[key], options);
    return target;
  };

  var SPECIES$1 = wellKnownSymbol('species');

  var setSpecies = function (CONSTRUCTOR_NAME) {
    var Constructor = getBuiltIn(CONSTRUCTOR_NAME);
    var defineProperty = objectDefineProperty.f;

    if (descriptors && Constructor && !Constructor[SPECIES$1]) {
      defineProperty(Constructor, SPECIES$1, {
        configurable: true,
        get: function () { return this; }
      });
    }
  };

  var anInstance = function (it, Constructor, name) {
    if (!(it instanceof Constructor)) {
      throw TypeError('Incorrect ' + (name ? name + ' ' : '') + 'invocation');
    } return it;
  };

  var iterate_1 = createCommonjsModule(function (module) {
  var Result = function (stopped, result) {
    this.stopped = stopped;
    this.result = result;
  };

  var iterate = module.exports = function (iterable, fn, that, AS_ENTRIES, IS_ITERATOR) {
    var boundFunction = functionBindContext(fn, that, AS_ENTRIES ? 2 : 1);
    var iterator, iterFn, index, length, result, next, step;

    if (IS_ITERATOR) {
      iterator = iterable;
    } else {
      iterFn = getIteratorMethod(iterable);
      if (typeof iterFn != 'function') throw TypeError('Target is not iterable');
      // optimisation for array iterators
      if (isArrayIteratorMethod(iterFn)) {
        for (index = 0, length = toLength(iterable.length); length > index; index++) {
          result = AS_ENTRIES
            ? boundFunction(anObject(step = iterable[index])[0], step[1])
            : boundFunction(iterable[index]);
          if (result && result instanceof Result) return result;
        } return new Result(false);
      }
      iterator = iterFn.call(iterable);
    }

    next = iterator.next;
    while (!(step = next.call(iterator)).done) {
      result = callWithSafeIterationClosing(iterator, boundFunction, step.value, AS_ENTRIES);
      if (typeof result == 'object' && result && result instanceof Result) return result;
    } return new Result(false);
  };

  iterate.stop = function (result) {
    return new Result(true, result);
  };
  });

  var SPECIES$2 = wellKnownSymbol('species');

  // `SpeciesConstructor` abstract operation
  // https://tc39.github.io/ecma262/#sec-speciesconstructor
  var speciesConstructor = function (O, defaultConstructor) {
    var C = anObject(O).constructor;
    var S;
    return C === undefined || (S = anObject(C)[SPECIES$2]) == undefined ? defaultConstructor : aFunction$1(S);
  };

  var engineUserAgent = getBuiltIn('navigator', 'userAgent') || '';

  var engineIsIos = /(iphone|ipod|ipad).*applewebkit/i.test(engineUserAgent);

  var location = global_1.location;
  var set$1 = global_1.setImmediate;
  var clear = global_1.clearImmediate;
  var process = global_1.process;
  var MessageChannel = global_1.MessageChannel;
  var Dispatch = global_1.Dispatch;
  var counter = 0;
  var queue = {};
  var ONREADYSTATECHANGE = 'onreadystatechange';
  var defer, channel, port;

  var run = function (id) {
    // eslint-disable-next-line no-prototype-builtins
    if (queue.hasOwnProperty(id)) {
      var fn = queue[id];
      delete queue[id];
      fn();
    }
  };

  var runner = function (id) {
    return function () {
      run(id);
    };
  };

  var listener = function (event) {
    run(event.data);
  };

  var post = function (id) {
    // old engines have not location.origin
    global_1.postMessage(id + '', location.protocol + '//' + location.host);
  };

  // Node.js 0.9+ & IE10+ has setImmediate, otherwise:
  if (!set$1 || !clear) {
    set$1 = function setImmediate(fn) {
      var args = [];
      var i = 1;
      while (arguments.length > i) args.push(arguments[i++]);
      queue[++counter] = function () {
        // eslint-disable-next-line no-new-func
        (typeof fn == 'function' ? fn : Function(fn)).apply(undefined, args);
      };
      defer(counter);
      return counter;
    };
    clear = function clearImmediate(id) {
      delete queue[id];
    };
    // Node.js 0.8-
    if (classofRaw(process) == 'process') {
      defer = function (id) {
        process.nextTick(runner(id));
      };
    // Sphere (JS game engine) Dispatch API
    } else if (Dispatch && Dispatch.now) {
      defer = function (id) {
        Dispatch.now(runner(id));
      };
    // Browsers with MessageChannel, includes WebWorkers
    // except iOS - https://github.com/zloirock/core-js/issues/624
    } else if (MessageChannel && !engineIsIos) {
      channel = new MessageChannel();
      port = channel.port2;
      channel.port1.onmessage = listener;
      defer = functionBindContext(port.postMessage, port, 1);
    // Browsers with postMessage, skip WebWorkers
    // IE8 has postMessage, but it's sync & typeof its postMessage is 'object'
    } else if (
      global_1.addEventListener &&
      typeof postMessage == 'function' &&
      !global_1.importScripts &&
      !fails(post) &&
      location.protocol !== 'file:'
    ) {
      defer = post;
      global_1.addEventListener('message', listener, false);
    // IE8-
    } else if (ONREADYSTATECHANGE in documentCreateElement('script')) {
      defer = function (id) {
        html.appendChild(documentCreateElement('script'))[ONREADYSTATECHANGE] = function () {
          html.removeChild(this);
          run(id);
        };
      };
    // Rest old browsers
    } else {
      defer = function (id) {
        setTimeout(runner(id), 0);
      };
    }
  }

  var task = {
    set: set$1,
    clear: clear
  };

  var getOwnPropertyDescriptor$2 = objectGetOwnPropertyDescriptor.f;

  var macrotask = task.set;


  var MutationObserver = global_1.MutationObserver || global_1.WebKitMutationObserver;
  var process$1 = global_1.process;
  var Promise$1 = global_1.Promise;
  var IS_NODE = classofRaw(process$1) == 'process';
  // Node.js 11 shows ExperimentalWarning on getting `queueMicrotask`
  var queueMicrotaskDescriptor = getOwnPropertyDescriptor$2(global_1, 'queueMicrotask');
  var queueMicrotask = queueMicrotaskDescriptor && queueMicrotaskDescriptor.value;

  var flush, head, last, notify, toggle, node, promise, then;

  // modern engines have queueMicrotask method
  if (!queueMicrotask) {
    flush = function () {
      var parent, fn;
      if (IS_NODE && (parent = process$1.domain)) parent.exit();
      while (head) {
        fn = head.fn;
        head = head.next;
        try {
          fn();
        } catch (error) {
          if (head) notify();
          else last = undefined;
          throw error;
        }
      } last = undefined;
      if (parent) parent.enter();
    };

    // Node.js
    if (IS_NODE) {
      notify = function () {
        process$1.nextTick(flush);
      };
    // browsers with MutationObserver, except iOS - https://github.com/zloirock/core-js/issues/339
    } else if (MutationObserver && !engineIsIos) {
      toggle = true;
      node = document.createTextNode('');
      new MutationObserver(flush).observe(node, { characterData: true });
      notify = function () {
        node.data = toggle = !toggle;
      };
    // environments with maybe non-completely correct, but existent Promise
    } else if (Promise$1 && Promise$1.resolve) {
      // Promise.resolve without an argument throws an error in LG WebOS 2
      promise = Promise$1.resolve(undefined);
      then = promise.then;
      notify = function () {
        then.call(promise, flush);
      };
    // for other environments - macrotask based on:
    // - setImmediate
    // - MessageChannel
    // - window.postMessag
    // - onreadystatechange
    // - setTimeout
    } else {
      notify = function () {
        // strange IE + webpack dev server bug - use .call(global)
        macrotask.call(global_1, flush);
      };
    }
  }

  var microtask = queueMicrotask || function (fn) {
    var task = { fn: fn, next: undefined };
    if (last) last.next = task;
    if (!head) {
      head = task;
      notify();
    } last = task;
  };

  var PromiseCapability = function (C) {
    var resolve, reject;
    this.promise = new C(function ($$resolve, $$reject) {
      if (resolve !== undefined || reject !== undefined) throw TypeError('Bad Promise constructor');
      resolve = $$resolve;
      reject = $$reject;
    });
    this.resolve = aFunction$1(resolve);
    this.reject = aFunction$1(reject);
  };

  // 25.4.1.5 NewPromiseCapability(C)
  var f$5 = function (C) {
    return new PromiseCapability(C);
  };

  var newPromiseCapability = {
  	f: f$5
  };

  var promiseResolve = function (C, x) {
    anObject(C);
    if (isObject(x) && x.constructor === C) return x;
    var promiseCapability = newPromiseCapability.f(C);
    var resolve = promiseCapability.resolve;
    resolve(x);
    return promiseCapability.promise;
  };

  var hostReportErrors = function (a, b) {
    var console = global_1.console;
    if (console && console.error) {
      arguments.length === 1 ? console.error(a) : console.error(a, b);
    }
  };

  var perform = function (exec) {
    try {
      return { error: false, value: exec() };
    } catch (error) {
      return { error: true, value: error };
    }
  };

  var process$2 = global_1.process;
  var versions = process$2 && process$2.versions;
  var v8 = versions && versions.v8;
  var match, version;

  if (v8) {
    match = v8.split('.');
    version = match[0] + match[1];
  } else if (engineUserAgent) {
    match = engineUserAgent.match(/Edge\/(\d+)/);
    if (!match || match[1] >= 74) {
      match = engineUserAgent.match(/Chrome\/(\d+)/);
      if (match) version = match[1];
    }
  }

  var engineV8Version = version && +version;

  var task$1 = task.set;










  var SPECIES$3 = wellKnownSymbol('species');
  var PROMISE = 'Promise';
  var getInternalState$2 = internalState.get;
  var setInternalState$2 = internalState.set;
  var getInternalPromiseState = internalState.getterFor(PROMISE);
  var PromiseConstructor = nativePromiseConstructor;
  var TypeError$1 = global_1.TypeError;
  var document$2 = global_1.document;
  var process$3 = global_1.process;
  var $fetch = getBuiltIn('fetch');
  var newPromiseCapability$1 = newPromiseCapability.f;
  var newGenericPromiseCapability = newPromiseCapability$1;
  var IS_NODE$1 = classofRaw(process$3) == 'process';
  var DISPATCH_EVENT = !!(document$2 && document$2.createEvent && global_1.dispatchEvent);
  var UNHANDLED_REJECTION = 'unhandledrejection';
  var REJECTION_HANDLED = 'rejectionhandled';
  var PENDING = 0;
  var FULFILLED = 1;
  var REJECTED = 2;
  var HANDLED = 1;
  var UNHANDLED = 2;
  var Internal, OwnPromiseCapability, PromiseWrapper, nativeThen;

  var FORCED = isForced_1(PROMISE, function () {
    var GLOBAL_CORE_JS_PROMISE = inspectSource(PromiseConstructor) !== String(PromiseConstructor);
    if (!GLOBAL_CORE_JS_PROMISE) {
      // V8 6.6 (Node 10 and Chrome 66) have a bug with resolving custom thenables
      // https://bugs.chromium.org/p/chromium/issues/detail?id=830565
      // We can't detect it synchronously, so just check versions
      if (engineV8Version === 66) return true;
      // Unhandled rejections tracking support, NodeJS Promise without it fails @@species test
      if (!IS_NODE$1 && typeof PromiseRejectionEvent != 'function') return true;
    }
    // We can't use @@species feature detection in V8 since it causes
    // deoptimization and performance degradation
    // https://github.com/zloirock/core-js/issues/679
    if (engineV8Version >= 51 && /native code/.test(PromiseConstructor)) return false;
    // Detect correctness of subclassing with @@species support
    var promise = PromiseConstructor.resolve(1);
    var FakePromise = function (exec) {
      exec(function () { /* empty */ }, function () { /* empty */ });
    };
    var constructor = promise.constructor = {};
    constructor[SPECIES$3] = FakePromise;
    return !(promise.then(function () { /* empty */ }) instanceof FakePromise);
  });

  var INCORRECT_ITERATION$1 = FORCED || !checkCorrectnessOfIteration(function (iterable) {
    PromiseConstructor.all(iterable)['catch'](function () { /* empty */ });
  });

  // helpers
  var isThenable = function (it) {
    var then;
    return isObject(it) && typeof (then = it.then) == 'function' ? then : false;
  };

  var notify$1 = function (promise, state, isReject) {
    if (state.notified) return;
    state.notified = true;
    var chain = state.reactions;
    microtask(function () {
      var value = state.value;
      var ok = state.state == FULFILLED;
      var index = 0;
      // variable length - can't use forEach
      while (chain.length > index) {
        var reaction = chain[index++];
        var handler = ok ? reaction.ok : reaction.fail;
        var resolve = reaction.resolve;
        var reject = reaction.reject;
        var domain = reaction.domain;
        var result, then, exited;
        try {
          if (handler) {
            if (!ok) {
              if (state.rejection === UNHANDLED) onHandleUnhandled(promise, state);
              state.rejection = HANDLED;
            }
            if (handler === true) result = value;
            else {
              if (domain) domain.enter();
              result = handler(value); // can throw
              if (domain) {
                domain.exit();
                exited = true;
              }
            }
            if (result === reaction.promise) {
              reject(TypeError$1('Promise-chain cycle'));
            } else if (then = isThenable(result)) {
              then.call(result, resolve, reject);
            } else resolve(result);
          } else reject(value);
        } catch (error) {
          if (domain && !exited) domain.exit();
          reject(error);
        }
      }
      state.reactions = [];
      state.notified = false;
      if (isReject && !state.rejection) onUnhandled(promise, state);
    });
  };

  var dispatchEvent = function (name, promise, reason) {
    var event, handler;
    if (DISPATCH_EVENT) {
      event = document$2.createEvent('Event');
      event.promise = promise;
      event.reason = reason;
      event.initEvent(name, false, true);
      global_1.dispatchEvent(event);
    } else event = { promise: promise, reason: reason };
    if (handler = global_1['on' + name]) handler(event);
    else if (name === UNHANDLED_REJECTION) hostReportErrors('Unhandled promise rejection', reason);
  };

  var onUnhandled = function (promise, state) {
    task$1.call(global_1, function () {
      var value = state.value;
      var IS_UNHANDLED = isUnhandled(state);
      var result;
      if (IS_UNHANDLED) {
        result = perform(function () {
          if (IS_NODE$1) {
            process$3.emit('unhandledRejection', value, promise);
          } else dispatchEvent(UNHANDLED_REJECTION, promise, value);
        });
        // Browsers should not trigger `rejectionHandled` event if it was handled here, NodeJS - should
        state.rejection = IS_NODE$1 || isUnhandled(state) ? UNHANDLED : HANDLED;
        if (result.error) throw result.value;
      }
    });
  };

  var isUnhandled = function (state) {
    return state.rejection !== HANDLED && !state.parent;
  };

  var onHandleUnhandled = function (promise, state) {
    task$1.call(global_1, function () {
      if (IS_NODE$1) {
        process$3.emit('rejectionHandled', promise);
      } else dispatchEvent(REJECTION_HANDLED, promise, state.value);
    });
  };

  var bind = function (fn, promise, state, unwrap) {
    return function (value) {
      fn(promise, state, value, unwrap);
    };
  };

  var internalReject = function (promise, state, value, unwrap) {
    if (state.done) return;
    state.done = true;
    if (unwrap) state = unwrap;
    state.value = value;
    state.state = REJECTED;
    notify$1(promise, state, true);
  };

  var internalResolve = function (promise, state, value, unwrap) {
    if (state.done) return;
    state.done = true;
    if (unwrap) state = unwrap;
    try {
      if (promise === value) throw TypeError$1("Promise can't be resolved itself");
      var then = isThenable(value);
      if (then) {
        microtask(function () {
          var wrapper = { done: false };
          try {
            then.call(value,
              bind(internalResolve, promise, wrapper, state),
              bind(internalReject, promise, wrapper, state)
            );
          } catch (error) {
            internalReject(promise, wrapper, error, state);
          }
        });
      } else {
        state.value = value;
        state.state = FULFILLED;
        notify$1(promise, state, false);
      }
    } catch (error) {
      internalReject(promise, { done: false }, error, state);
    }
  };

  // constructor polyfill
  if (FORCED) {
    // 25.4.3.1 Promise(executor)
    PromiseConstructor = function Promise(executor) {
      anInstance(this, PromiseConstructor, PROMISE);
      aFunction$1(executor);
      Internal.call(this);
      var state = getInternalState$2(this);
      try {
        executor(bind(internalResolve, this, state), bind(internalReject, this, state));
      } catch (error) {
        internalReject(this, state, error);
      }
    };
    // eslint-disable-next-line no-unused-vars
    Internal = function Promise(executor) {
      setInternalState$2(this, {
        type: PROMISE,
        done: false,
        notified: false,
        parent: false,
        reactions: [],
        rejection: false,
        state: PENDING,
        value: undefined
      });
    };
    Internal.prototype = redefineAll(PromiseConstructor.prototype, {
      // `Promise.prototype.then` method
      // https://tc39.github.io/ecma262/#sec-promise.prototype.then
      then: function then(onFulfilled, onRejected) {
        var state = getInternalPromiseState(this);
        var reaction = newPromiseCapability$1(speciesConstructor(this, PromiseConstructor));
        reaction.ok = typeof onFulfilled == 'function' ? onFulfilled : true;
        reaction.fail = typeof onRejected == 'function' && onRejected;
        reaction.domain = IS_NODE$1 ? process$3.domain : undefined;
        state.parent = true;
        state.reactions.push(reaction);
        if (state.state != PENDING) notify$1(this, state, false);
        return reaction.promise;
      },
      // `Promise.prototype.catch` method
      // https://tc39.github.io/ecma262/#sec-promise.prototype.catch
      'catch': function (onRejected) {
        return this.then(undefined, onRejected);
      }
    });
    OwnPromiseCapability = function () {
      var promise = new Internal();
      var state = getInternalState$2(promise);
      this.promise = promise;
      this.resolve = bind(internalResolve, promise, state);
      this.reject = bind(internalReject, promise, state);
    };
    newPromiseCapability.f = newPromiseCapability$1 = function (C) {
      return C === PromiseConstructor || C === PromiseWrapper
        ? new OwnPromiseCapability(C)
        : newGenericPromiseCapability(C);
    };

    if ( typeof nativePromiseConstructor == 'function') {
      nativeThen = nativePromiseConstructor.prototype.then;

      // wrap native Promise#then for native async functions
      redefine(nativePromiseConstructor.prototype, 'then', function then(onFulfilled, onRejected) {
        var that = this;
        return new PromiseConstructor(function (resolve, reject) {
          nativeThen.call(that, resolve, reject);
        }).then(onFulfilled, onRejected);
      // https://github.com/zloirock/core-js/issues/640
      }, { unsafe: true });

      // wrap fetch result
      if (typeof $fetch == 'function') _export({ global: true, enumerable: true, forced: true }, {
        // eslint-disable-next-line no-unused-vars
        fetch: function fetch(input /* , init */) {
          return promiseResolve(PromiseConstructor, $fetch.apply(global_1, arguments));
        }
      });
    }
  }

  _export({ global: true, wrap: true, forced: FORCED }, {
    Promise: PromiseConstructor
  });

  setToStringTag(PromiseConstructor, PROMISE, false);
  setSpecies(PROMISE);

  PromiseWrapper = getBuiltIn(PROMISE);

  // statics
  _export({ target: PROMISE, stat: true, forced: FORCED }, {
    // `Promise.reject` method
    // https://tc39.github.io/ecma262/#sec-promise.reject
    reject: function reject(r) {
      var capability = newPromiseCapability$1(this);
      capability.reject.call(undefined, r);
      return capability.promise;
    }
  });

  _export({ target: PROMISE, stat: true, forced:  FORCED }, {
    // `Promise.resolve` method
    // https://tc39.github.io/ecma262/#sec-promise.resolve
    resolve: function resolve(x) {
      return promiseResolve( this, x);
    }
  });

  _export({ target: PROMISE, stat: true, forced: INCORRECT_ITERATION$1 }, {
    // `Promise.all` method
    // https://tc39.github.io/ecma262/#sec-promise.all
    all: function all(iterable) {
      var C = this;
      var capability = newPromiseCapability$1(C);
      var resolve = capability.resolve;
      var reject = capability.reject;
      var result = perform(function () {
        var $promiseResolve = aFunction$1(C.resolve);
        var values = [];
        var counter = 0;
        var remaining = 1;
        iterate_1(iterable, function (promise) {
          var index = counter++;
          var alreadyCalled = false;
          values.push(undefined);
          remaining++;
          $promiseResolve.call(C, promise).then(function (value) {
            if (alreadyCalled) return;
            alreadyCalled = true;
            values[index] = value;
            --remaining || resolve(values);
          }, reject);
        });
        --remaining || resolve(values);
      });
      if (result.error) reject(result.value);
      return capability.promise;
    },
    // `Promise.race` method
    // https://tc39.github.io/ecma262/#sec-promise.race
    race: function race(iterable) {
      var C = this;
      var capability = newPromiseCapability$1(C);
      var reject = capability.reject;
      var result = perform(function () {
        var $promiseResolve = aFunction$1(C.resolve);
        iterate_1(iterable, function (promise) {
          $promiseResolve.call(C, promise).then(capability.resolve, reject);
        });
      });
      if (result.error) reject(result.value);
      return capability.promise;
    }
  });

  // `Promise.allSettled` method
  // https://github.com/tc39/proposal-promise-allSettled
  _export({ target: 'Promise', stat: true }, {
    allSettled: function allSettled(iterable) {
      var C = this;
      var capability = newPromiseCapability.f(C);
      var resolve = capability.resolve;
      var reject = capability.reject;
      var result = perform(function () {
        var promiseResolve = aFunction$1(C.resolve);
        var values = [];
        var counter = 0;
        var remaining = 1;
        iterate_1(iterable, function (promise) {
          var index = counter++;
          var alreadyCalled = false;
          values.push(undefined);
          remaining++;
          promiseResolve.call(C, promise).then(function (value) {
            if (alreadyCalled) return;
            alreadyCalled = true;
            values[index] = { status: 'fulfilled', value: value };
            --remaining || resolve(values);
          }, function (e) {
            if (alreadyCalled) return;
            alreadyCalled = true;
            values[index] = { status: 'rejected', reason: e };
            --remaining || resolve(values);
          });
        });
        --remaining || resolve(values);
      });
      if (result.error) reject(result.value);
      return capability.promise;
    }
  });

  // Safari bug https://bugs.webkit.org/show_bug.cgi?id=200829
  var NON_GENERIC = !!nativePromiseConstructor && fails(function () {
    nativePromiseConstructor.prototype['finally'].call({ then: function () { /* empty */ } }, function () { /* empty */ });
  });

  // `Promise.prototype.finally` method
  // https://tc39.github.io/ecma262/#sec-promise.prototype.finally
  _export({ target: 'Promise', proto: true, real: true, forced: NON_GENERIC }, {
    'finally': function (onFinally) {
      var C = speciesConstructor(this, getBuiltIn('Promise'));
      var isFunction = typeof onFinally == 'function';
      return this.then(
        isFunction ? function (x) {
          return promiseResolve(C, onFinally()).then(function () { return x; });
        } : onFinally,
        isFunction ? function (e) {
          return promiseResolve(C, onFinally()).then(function () { throw e; });
        } : onFinally
      );
    }
  });

  // patch native Promise.prototype for native async functions
  if ( typeof nativePromiseConstructor == 'function' && !nativePromiseConstructor.prototype['finally']) {
    redefine(nativePromiseConstructor.prototype, 'finally', getBuiltIn('Promise').prototype['finally']);
  }

  var promise$1 = path.Promise;

  var setInternalState$3 = internalState.set;
  var getInternalAggregateErrorState = internalState.getterFor('AggregateError');

  var $AggregateError = function AggregateError(errors, message) {
    var that = this;
    if (!(that instanceof $AggregateError)) return new $AggregateError(errors, message);
    if (objectSetPrototypeOf) {
      that = objectSetPrototypeOf(new Error(message), objectGetPrototypeOf(that));
    }
    var errorsArray = [];
    iterate_1(errors, errorsArray.push, errorsArray);
    if (descriptors) setInternalState$3(that, { errors: errorsArray, type: 'AggregateError' });
    else that.errors = errorsArray;
    if (message !== undefined) createNonEnumerableProperty(that, 'message', String(message));
    return that;
  };

  $AggregateError.prototype = objectCreate(Error.prototype, {
    constructor: createPropertyDescriptor(5, $AggregateError),
    message: createPropertyDescriptor(5, ''),
    name: createPropertyDescriptor(5, 'AggregateError')
  });

  if (descriptors) objectDefineProperty.f($AggregateError.prototype, 'errors', {
    get: function () {
      return getInternalAggregateErrorState(this).errors;
    },
    configurable: true
  });

  _export({ global: true }, {
    AggregateError: $AggregateError
  });

  // `Promise.try` method
  // https://github.com/tc39/proposal-promise-try
  _export({ target: 'Promise', stat: true }, {
    'try': function (callbackfn) {
      var promiseCapability = newPromiseCapability.f(this);
      var result = perform(callbackfn);
      (result.error ? promiseCapability.reject : promiseCapability.resolve)(result.value);
      return promiseCapability.promise;
    }
  });

  var PROMISE_ANY_ERROR = 'No one promise resolved';

  // `Promise.any` method
  // https://github.com/tc39/proposal-promise-any
  _export({ target: 'Promise', stat: true }, {
    any: function any(iterable) {
      var C = this;
      var capability = newPromiseCapability.f(C);
      var resolve = capability.resolve;
      var reject = capability.reject;
      var result = perform(function () {
        var promiseResolve = aFunction$1(C.resolve);
        var errors = [];
        var counter = 0;
        var remaining = 1;
        var alreadyResolved = false;
        iterate_1(iterable, function (promise) {
          var index = counter++;
          var alreadyRejected = false;
          errors.push(undefined);
          remaining++;
          promiseResolve.call(C, promise).then(function (value) {
            if (alreadyRejected || alreadyResolved) return;
            alreadyResolved = true;
            resolve(value);
          }, function (e) {
            if (alreadyRejected || alreadyResolved) return;
            alreadyRejected = true;
            errors[index] = e;
            --remaining || reject(new (getBuiltIn('AggregateError'))(errors, PROMISE_ANY_ERROR));
          });
        });
        --remaining || reject(new (getBuiltIn('AggregateError'))(errors, PROMISE_ANY_ERROR));
      });
      if (result.error) reject(result.value);
      return capability.promise;
    }
  });

  var MATCH = wellKnownSymbol('match');

  // `IsRegExp` abstract operation
  // https://tc39.github.io/ecma262/#sec-isregexp
  var isRegexp = function (it) {
    var isRegExp;
    return isObject(it) && ((isRegExp = it[MATCH]) !== undefined ? !!isRegExp : classofRaw(it) == 'RegExp');
  };

  var notARegexp = function (it) {
    if (isRegexp(it)) {
      throw TypeError("The method doesn't accept regular expressions");
    } return it;
  };

  var MATCH$1 = wellKnownSymbol('match');

  var correctIsRegexpLogic = function (METHOD_NAME) {
    var regexp = /./;
    try {
      '/./'[METHOD_NAME](regexp);
    } catch (e) {
      try {
        regexp[MATCH$1] = false;
        return '/./'[METHOD_NAME](regexp);
      } catch (f) { /* empty */ }
    } return false;
  };

  var getOwnPropertyDescriptor$3 = objectGetOwnPropertyDescriptor.f;






  var nativeStartsWith = ''.startsWith;
  var min$2 = Math.min;

  var CORRECT_IS_REGEXP_LOGIC = correctIsRegexpLogic('startsWith');
  // https://github.com/zloirock/core-js/pull/702
  var MDN_POLYFILL_BUG =  !CORRECT_IS_REGEXP_LOGIC && !!function () {
    var descriptor = getOwnPropertyDescriptor$3(String.prototype, 'startsWith');
    return descriptor && !descriptor.writable;
  }();

  // `String.prototype.startsWith` method
  // https://tc39.github.io/ecma262/#sec-string.prototype.startswith
  _export({ target: 'String', proto: true, forced: !MDN_POLYFILL_BUG && !CORRECT_IS_REGEXP_LOGIC }, {
    startsWith: function startsWith(searchString /* , position = 0 */) {
      var that = String(requireObjectCoercible(this));
      notARegexp(searchString);
      var index = toLength(min$2(arguments.length > 1 ? arguments[1] : undefined, that.length));
      var search = String(searchString);
      return nativeStartsWith
        ? nativeStartsWith.call(that, search, index)
        : that.slice(index, index + search.length) === search;
    }
  });

  var startsWith = entryUnbind('String', 'startsWith');

  var global$1 =
    (typeof globalThis !== 'undefined' && globalThis) ||
    (typeof self !== 'undefined' && self) ||
    (typeof global$1 !== 'undefined' && global$1);

  var support = {
    searchParams: 'URLSearchParams' in global$1,
    iterable: 'Symbol' in global$1 && 'iterator' in Symbol,
    blob:
      'FileReader' in global$1 &&
      'Blob' in global$1 &&
      (function() {
        try {
          new Blob();
          return true
        } catch (e) {
          return false
        }
      })(),
    formData: 'FormData' in global$1,
    arrayBuffer: 'ArrayBuffer' in global$1
  };

  function isDataView(obj) {
    return obj && DataView.prototype.isPrototypeOf(obj)
  }

  if (support.arrayBuffer) {
    var viewClasses = [
      '[object Int8Array]',
      '[object Uint8Array]',
      '[object Uint8ClampedArray]',
      '[object Int16Array]',
      '[object Uint16Array]',
      '[object Int32Array]',
      '[object Uint32Array]',
      '[object Float32Array]',
      '[object Float64Array]'
    ];

    var isArrayBufferView =
      ArrayBuffer.isView ||
      function(obj) {
        return obj && viewClasses.indexOf(Object.prototype.toString.call(obj)) > -1
      };
  }

  function normalizeName(name) {
    if (typeof name !== 'string') {
      name = String(name);
    }
    if (/[^a-z0-9\-#$%&'*+.^_`|~!]/i.test(name) || name === '') {
      throw new TypeError('Invalid character in header field name')
    }
    return name.toLowerCase()
  }

  function normalizeValue(value) {
    if (typeof value !== 'string') {
      value = String(value);
    }
    return value
  }

  // Build a destructive iterator for the value list
  function iteratorFor(items) {
    var iterator = {
      next: function() {
        var value = items.shift();
        return {done: value === undefined, value: value}
      }
    };

    if (support.iterable) {
      iterator[Symbol.iterator] = function() {
        return iterator
      };
    }

    return iterator
  }

  function Headers(headers) {
    this.map = {};

    if (headers instanceof Headers) {
      headers.forEach(function(value, name) {
        this.append(name, value);
      }, this);
    } else if (Array.isArray(headers)) {
      headers.forEach(function(header) {
        this.append(header[0], header[1]);
      }, this);
    } else if (headers) {
      Object.getOwnPropertyNames(headers).forEach(function(name) {
        this.append(name, headers[name]);
      }, this);
    }
  }

  Headers.prototype.append = function(name, value) {
    name = normalizeName(name);
    value = normalizeValue(value);
    var oldValue = this.map[name];
    this.map[name] = oldValue ? oldValue + ', ' + value : value;
  };

  Headers.prototype['delete'] = function(name) {
    delete this.map[normalizeName(name)];
  };

  Headers.prototype.get = function(name) {
    name = normalizeName(name);
    return this.has(name) ? this.map[name] : null
  };

  Headers.prototype.has = function(name) {
    return this.map.hasOwnProperty(normalizeName(name))
  };

  Headers.prototype.set = function(name, value) {
    this.map[normalizeName(name)] = normalizeValue(value);
  };

  Headers.prototype.forEach = function(callback, thisArg) {
    for (var name in this.map) {
      if (this.map.hasOwnProperty(name)) {
        callback.call(thisArg, this.map[name], name, this);
      }
    }
  };

  Headers.prototype.keys = function() {
    var items = [];
    this.forEach(function(value, name) {
      items.push(name);
    });
    return iteratorFor(items)
  };

  Headers.prototype.values = function() {
    var items = [];
    this.forEach(function(value) {
      items.push(value);
    });
    return iteratorFor(items)
  };

  Headers.prototype.entries = function() {
    var items = [];
    this.forEach(function(value, name) {
      items.push([name, value]);
    });
    return iteratorFor(items)
  };

  if (support.iterable) {
    Headers.prototype[Symbol.iterator] = Headers.prototype.entries;
  }

  function consumed(body) {
    if (body.bodyUsed) {
      return Promise.reject(new TypeError('Already read'))
    }
    body.bodyUsed = true;
  }

  function fileReaderReady(reader) {
    return new Promise(function(resolve, reject) {
      reader.onload = function() {
        resolve(reader.result);
      };
      reader.onerror = function() {
        reject(reader.error);
      };
    })
  }

  function readBlobAsArrayBuffer(blob) {
    var reader = new FileReader();
    var promise = fileReaderReady(reader);
    reader.readAsArrayBuffer(blob);
    return promise
  }

  function readBlobAsText(blob) {
    var reader = new FileReader();
    var promise = fileReaderReady(reader);
    reader.readAsText(blob);
    return promise
  }

  function readArrayBufferAsText(buf) {
    var view = new Uint8Array(buf);
    var chars = new Array(view.length);

    for (var i = 0; i < view.length; i++) {
      chars[i] = String.fromCharCode(view[i]);
    }
    return chars.join('')
  }

  function bufferClone(buf) {
    if (buf.slice) {
      return buf.slice(0)
    } else {
      var view = new Uint8Array(buf.byteLength);
      view.set(new Uint8Array(buf));
      return view.buffer
    }
  }

  function Body() {
    this.bodyUsed = false;

    this._initBody = function(body) {
      /*
        fetch-mock wraps the Response object in an ES6 Proxy to
        provide useful test harness features such as flush. However, on
        ES5 browsers without fetch or Proxy support pollyfills must be used;
        the proxy-pollyfill is unable to proxy an attribute unless it exists
        on the object before the Proxy is created. This change ensures
        Response.bodyUsed exists on the instance, while maintaining the
        semantic of setting Request.bodyUsed in the constructor before
        _initBody is called.
      */
      this.bodyUsed = this.bodyUsed;
      this._bodyInit = body;
      if (!body) {
        this._bodyText = '';
      } else if (typeof body === 'string') {
        this._bodyText = body;
      } else if (support.blob && Blob.prototype.isPrototypeOf(body)) {
        this._bodyBlob = body;
      } else if (support.formData && FormData.prototype.isPrototypeOf(body)) {
        this._bodyFormData = body;
      } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
        this._bodyText = body.toString();
      } else if (support.arrayBuffer && support.blob && isDataView(body)) {
        this._bodyArrayBuffer = bufferClone(body.buffer);
        // IE 10-11 can't handle a DataView body.
        this._bodyInit = new Blob([this._bodyArrayBuffer]);
      } else if (support.arrayBuffer && (ArrayBuffer.prototype.isPrototypeOf(body) || isArrayBufferView(body))) {
        this._bodyArrayBuffer = bufferClone(body);
      } else {
        this._bodyText = body = Object.prototype.toString.call(body);
      }

      if (!this.headers.get('content-type')) {
        if (typeof body === 'string') {
          this.headers.set('content-type', 'text/plain;charset=UTF-8');
        } else if (this._bodyBlob && this._bodyBlob.type) {
          this.headers.set('content-type', this._bodyBlob.type);
        } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
          this.headers.set('content-type', 'application/x-www-form-urlencoded;charset=UTF-8');
        }
      }
    };

    if (support.blob) {
      this.blob = function() {
        var rejected = consumed(this);
        if (rejected) {
          return rejected
        }

        if (this._bodyBlob) {
          return Promise.resolve(this._bodyBlob)
        } else if (this._bodyArrayBuffer) {
          return Promise.resolve(new Blob([this._bodyArrayBuffer]))
        } else if (this._bodyFormData) {
          throw new Error('could not read FormData body as blob')
        } else {
          return Promise.resolve(new Blob([this._bodyText]))
        }
      };

      this.arrayBuffer = function() {
        if (this._bodyArrayBuffer) {
          var isConsumed = consumed(this);
          if (isConsumed) {
            return isConsumed
          }
          if (ArrayBuffer.isView(this._bodyArrayBuffer)) {
            return Promise.resolve(
              this._bodyArrayBuffer.buffer.slice(
                this._bodyArrayBuffer.byteOffset,
                this._bodyArrayBuffer.byteOffset + this._bodyArrayBuffer.byteLength
              )
            )
          } else {
            return Promise.resolve(this._bodyArrayBuffer)
          }
        } else {
          return this.blob().then(readBlobAsArrayBuffer)
        }
      };
    }

    this.text = function() {
      var rejected = consumed(this);
      if (rejected) {
        return rejected
      }

      if (this._bodyBlob) {
        return readBlobAsText(this._bodyBlob)
      } else if (this._bodyArrayBuffer) {
        return Promise.resolve(readArrayBufferAsText(this._bodyArrayBuffer))
      } else if (this._bodyFormData) {
        throw new Error('could not read FormData body as text')
      } else {
        return Promise.resolve(this._bodyText)
      }
    };

    if (support.formData) {
      this.formData = function() {
        return this.text().then(decode)
      };
    }

    this.json = function() {
      return this.text().then(JSON.parse)
    };

    return this
  }

  // HTTP methods whose capitalization should be normalized
  var methods = ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT'];

  function normalizeMethod(method) {
    var upcased = method.toUpperCase();
    return methods.indexOf(upcased) > -1 ? upcased : method
  }

  function Request(input, options) {
    if (!(this instanceof Request)) {
      throw new TypeError('Please use the "new" operator, this DOM object constructor cannot be called as a function.')
    }

    options = options || {};
    var body = options.body;

    if (input instanceof Request) {
      if (input.bodyUsed) {
        throw new TypeError('Already read')
      }
      this.url = input.url;
      this.credentials = input.credentials;
      if (!options.headers) {
        this.headers = new Headers(input.headers);
      }
      this.method = input.method;
      this.mode = input.mode;
      this.signal = input.signal;
      if (!body && input._bodyInit != null) {
        body = input._bodyInit;
        input.bodyUsed = true;
      }
    } else {
      this.url = String(input);
    }

    this.credentials = options.credentials || this.credentials || 'same-origin';
    if (options.headers || !this.headers) {
      this.headers = new Headers(options.headers);
    }
    this.method = normalizeMethod(options.method || this.method || 'GET');
    this.mode = options.mode || this.mode || null;
    this.signal = options.signal || this.signal;
    this.referrer = null;

    if ((this.method === 'GET' || this.method === 'HEAD') && body) {
      throw new TypeError('Body not allowed for GET or HEAD requests')
    }
    this._initBody(body);

    if (this.method === 'GET' || this.method === 'HEAD') {
      if (options.cache === 'no-store' || options.cache === 'no-cache') {
        // Search for a '_' parameter in the query string
        var reParamSearch = /([?&])_=[^&]*/;
        if (reParamSearch.test(this.url)) {
          // If it already exists then set the value with the current time
          this.url = this.url.replace(reParamSearch, '$1_=' + new Date().getTime());
        } else {
          // Otherwise add a new '_' parameter to the end with the current time
          var reQueryString = /\?/;
          this.url += (reQueryString.test(this.url) ? '&' : '?') + '_=' + new Date().getTime();
        }
      }
    }
  }

  Request.prototype.clone = function() {
    return new Request(this, {body: this._bodyInit})
  };

  function decode(body) {
    var form = new FormData();
    body
      .trim()
      .split('&')
      .forEach(function(bytes) {
        if (bytes) {
          var split = bytes.split('=');
          var name = split.shift().replace(/\+/g, ' ');
          var value = split.join('=').replace(/\+/g, ' ');
          form.append(decodeURIComponent(name), decodeURIComponent(value));
        }
      });
    return form
  }

  function parseHeaders(rawHeaders) {
    var headers = new Headers();
    // Replace instances of \r\n and \n followed by at least one space or horizontal tab with a space
    // https://tools.ietf.org/html/rfc7230#section-3.2
    var preProcessedHeaders = rawHeaders.replace(/\r?\n[\t ]+/g, ' ');
    preProcessedHeaders.split(/\r?\n/).forEach(function(line) {
      var parts = line.split(':');
      var key = parts.shift().trim();
      if (key) {
        var value = parts.join(':').trim();
        headers.append(key, value);
      }
    });
    return headers
  }

  Body.call(Request.prototype);

  function Response(bodyInit, options) {
    if (!(this instanceof Response)) {
      throw new TypeError('Please use the "new" operator, this DOM object constructor cannot be called as a function.')
    }
    if (!options) {
      options = {};
    }

    this.type = 'default';
    this.status = options.status === undefined ? 200 : options.status;
    this.ok = this.status >= 200 && this.status < 300;
    this.statusText = 'statusText' in options ? options.statusText : '';
    this.headers = new Headers(options.headers);
    this.url = options.url || '';
    this._initBody(bodyInit);
  }

  Body.call(Response.prototype);

  Response.prototype.clone = function() {
    return new Response(this._bodyInit, {
      status: this.status,
      statusText: this.statusText,
      headers: new Headers(this.headers),
      url: this.url
    })
  };

  Response.error = function() {
    var response = new Response(null, {status: 0, statusText: ''});
    response.type = 'error';
    return response
  };

  var redirectStatuses = [301, 302, 303, 307, 308];

  Response.redirect = function(url, status) {
    if (redirectStatuses.indexOf(status) === -1) {
      throw new RangeError('Invalid status code')
    }

    return new Response(null, {status: status, headers: {location: url}})
  };

  var DOMException = global$1.DOMException;
  try {
    new DOMException();
  } catch (err) {
    DOMException = function(message, name) {
      this.message = message;
      this.name = name;
      var error = Error(message);
      this.stack = error.stack;
    };
    DOMException.prototype = Object.create(Error.prototype);
    DOMException.prototype.constructor = DOMException;
  }

  function fetch$1(input, init) {
    return new Promise(function(resolve, reject) {
      var request = new Request(input, init);

      if (request.signal && request.signal.aborted) {
        return reject(new DOMException('Aborted', 'AbortError'))
      }

      var xhr = new XMLHttpRequest();

      function abortXhr() {
        xhr.abort();
      }

      xhr.onload = function() {
        var options = {
          status: xhr.status,
          statusText: xhr.statusText,
          headers: parseHeaders(xhr.getAllResponseHeaders() || '')
        };
        options.url = 'responseURL' in xhr ? xhr.responseURL : options.headers.get('X-Request-URL');
        var body = 'response' in xhr ? xhr.response : xhr.responseText;
        setTimeout(function() {
          resolve(new Response(body, options));
        }, 0);
      };

      xhr.onerror = function() {
        setTimeout(function() {
          reject(new TypeError('Network request failed'));
        }, 0);
      };

      xhr.ontimeout = function() {
        setTimeout(function() {
          reject(new TypeError('Network request failed'));
        }, 0);
      };

      xhr.onabort = function() {
        setTimeout(function() {
          reject(new DOMException('Aborted', 'AbortError'));
        }, 0);
      };

      function fixUrl(url) {
        try {
          return url === '' && global$1.location.href ? global$1.location.href : url
        } catch (e) {
          return url
        }
      }

      xhr.open(request.method, fixUrl(request.url), true);

      if (request.credentials === 'include') {
        xhr.withCredentials = true;
      } else if (request.credentials === 'omit') {
        xhr.withCredentials = false;
      }

      if ('responseType' in xhr) {
        if (support.blob) {
          xhr.responseType = 'blob';
        } else if (
          support.arrayBuffer &&
          request.headers.get('Content-Type') &&
          request.headers.get('Content-Type').indexOf('application/octet-stream') !== -1
        ) {
          xhr.responseType = 'arraybuffer';
        }
      }

      if (init && typeof init.headers === 'object' && !(init.headers instanceof Headers)) {
        Object.getOwnPropertyNames(init.headers).forEach(function(name) {
          xhr.setRequestHeader(name, normalizeValue(init.headers[name]));
        });
      } else {
        request.headers.forEach(function(value, name) {
          xhr.setRequestHeader(name, value);
        });
      }

      if (request.signal) {
        request.signal.addEventListener('abort', abortXhr);

        xhr.onreadystatechange = function() {
          // DONE (success or failure)
          if (xhr.readyState === 4) {
            request.signal.removeEventListener('abort', abortXhr);
          }
        };
      }

      xhr.send(typeof request._bodyInit === 'undefined' ? null : request._bodyInit);
    })
  }

  fetch$1.polyfill = true;

  if (!global$1.fetch) {
    global$1.fetch = fetch$1;
    global$1.Headers = Headers;
    global$1.Request = Request;
    global$1.Response = Response;
  }

  // https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttributeNames#Polyfill
  if (Element.prototype.getAttributeNames == undefined) {
    Element.prototype.getAttributeNames = function () {
      var attributes = this.attributes;
      var length = attributes.length;
      var result = new Array(length);

      for (var i = 0; i < length; i++) {
        result[i] = attributes[i].name;
      }

      return result;
    };
  }

  // https://developer.mozilla.org/en-US/docs/Web/API/Element/matches#Polyfill
  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.matchesSelector || Element.prototype.mozMatchesSelector || Element.prototype.msMatchesSelector || Element.prototype.oMatchesSelector || Element.prototype.webkitMatchesSelector || function (s) {
      var matches = (this.document || this.ownerDocument).querySelectorAll(s),
          i = matches.length;

      while (--i >= 0 && matches.item(i) !== this) {}

      return i > -1;
    };
  }

  // https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill
  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
  }

  if (!Element.prototype.closest) {
    Element.prototype.closest = function (s) {
      var el = this;

      do {
        if (el.matches(s)) return el;
        el = el.parentElement || el.parentNode;
      } while (el !== null && el.nodeType === 1);

      return null;
    };
  }

  var Connection = /*#__PURE__*/function () {
    function Connection() {
      _classCallCheck(this, Connection);
    }

    _createClass(Connection, [{
      key: "onMessage",
      value: function onMessage(message, payload) {
        message.component.receiveMessage(message, payload);
      }
    }, {
      key: "onError",
      value: function onError(message, status) {
        message.component.messageSendFailed();
        return store.onErrorCallback(status);
      }
    }, {
      key: "sendMessage",
      value: function sendMessage(message) {
        var _this = this;

        var payload = message.payload();

        if (window.__testing_request_interceptor) {
          return window.__testing_request_interceptor(payload, this);
        } // Forward the query string for the ajax requests.


        fetch("".concat(window.livewire_app_url, "/livewire/message/").concat(payload.fingerprint.name), {
          method: 'POST',
          body: JSON.stringify(payload),
          // This enables "cookies".
          credentials: 'same-origin',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'text/html, application/xhtml+xml',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Socket-ID': this.getSocketId(),
            'X-Livewire': true,
            // We'll set this explicitly to mitigate potential interference from ad-blockers/etc.
            'Referer': window.location.href
          }
        }).then(function (response) {
          if (response.ok) {
            response.text().then(function (response) {
              if (_this.isOutputFromDump(response)) {
                _this.onError(message);

                _this.showHtmlModal(response);
              } else {
                _this.onMessage(message, JSON.parse(response));
              }
            });
          } else {
            if (_this.onError(message, response.status) === false) return;

            if (response.status === 419) {
              if (store.sessionHasExpired) return;
              store.sessionHasExpired = true;
              confirm('This page has expired due to inactivity.\nWould you like to refresh the page?') && window.location.reload();
            } else {
              response.text().then(function (response) {
                _this.showHtmlModal(response);
              });
            }
          }
        }).catch(function () {
          _this.onError(message);
        });
      }
    }, {
      key: "isOutputFromDump",
      value: function isOutputFromDump(output) {
        return !!output.match(/<script>Sfdump\(".+"\)<\/script>/);
      }
    }, {
      key: "getSocketId",
      value: function getSocketId() {
        if (typeof Echo !== 'undefined') {
          return Echo.socketId();
        }
      } // This code and concept is all Jonathan Reinink - thanks main!

    }, {
      key: "showHtmlModal",
      value: function showHtmlModal(html) {
        var _this2 = this;

        var page = document.createElement('html');
        page.innerHTML = html;
        page.querySelectorAll('a').forEach(function (a) {
          return a.setAttribute('target', '_top');
        });
        var modal = document.getElementById('livewire-error');

        if (typeof modal != 'undefined' && modal != null) {
          // Modal already exists.
          modal.innerHTML = '';
        } else {
          modal = document.createElement('div');
          modal.id = 'livewire-error';
          modal.style.position = 'fixed';
          modal.style.width = '100vw';
          modal.style.height = '100vh';
          modal.style.padding = '50px';
          modal.style.backgroundColor = 'rgba(0, 0, 0, .6)';
          modal.style.zIndex = 200000;
        }

        var iframe = document.createElement('iframe');
        iframe.style.backgroundColor = '#17161A';
        iframe.style.borderRadius = '5px';
        iframe.style.width = '100%';
        iframe.style.height = '100%';
        modal.appendChild(iframe);
        document.body.prepend(modal);
        document.body.style.overflow = 'hidden';
        iframe.contentWindow.document.open();
        iframe.contentWindow.document.write(page.outerHTML);
        iframe.contentWindow.document.close(); // Close on click.

        modal.addEventListener('click', function () {
          return _this2.hideHtmlModal(modal);
        }); // Close on escape key press.

        modal.setAttribute('tabindex', 0);
        modal.addEventListener('keydown', function (e) {
          if (e.key === 'Escape') _this2.hideHtmlModal(modal);
        });
        modal.focus();
      }
    }, {
      key: "hideHtmlModal",
      value: function hideHtmlModal(modal) {
        modal.outerHTML = '';
        document.body.style.overflow = 'visible';
      }
    }]);

    return Connection;
  }();

  var _default$2 = /*#__PURE__*/function (_Action) {
    _inherits(_default, _Action);

    var _super = _createSuper(_default);

    function _default(method, params, el) {
      var _this;

      var skipWatcher = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

      _classCallCheck(this, _default);

      _this = _super.call(this, el, skipWatcher);
      _this.type = 'callMethod';
      _this.method = method;
      _this.payload = {
        method: method,
        params: params
      };
      return _this;
    }

    return _default;
  }(_default);

  function Polling () {
    store.registerHook('element.initialized', function (el, component) {
      var directives = wireDirectives(el);
      if (directives.missing('poll')) return;
      var intervalId = fireActionOnInterval(el, component);
      component.addListenerForTeardown(function () {
        clearInterval(intervalId);
      });
      el.__livewire_polling_interval = intervalId;
    });
    store.registerHook('element.updating', function (from, to, component) {
      if (from.__livewire_polling_interval !== undefined) return;

      if (wireDirectives(from).missing('poll') && wireDirectives(to).has('poll')) {
        setTimeout(function () {
          var intervalId = fireActionOnInterval(from, component);
          component.addListenerForTeardown(function () {
            clearInterval(intervalId);
          });
          from.__livewire_polling_interval = intervalId;
        }, 0);
      }
    });
  }

  function fireActionOnInterval(node, component) {
    var interval = wireDirectives(node).get('poll').durationOr(2000);
    return setInterval(function () {
      if (node.isConnected === false) return;
      var directives = wireDirectives(node); // Don't poll when directive is removed from element.

      if (directives.missing('poll')) return;
      var directive = directives.get('poll');
      var method = directive.method || '$refresh'; // Don't poll when the tab is in the background.
      // (unless the "wire:poll.keep-alive" modifier is attached)

      if (store.livewireIsInBackground && !directive.modifiers.includes('keep-alive')) {
        // This "Math.random" business effectivlly prevents 95% of requests
        // from executing. We still want "some" requests to get through.
        if (Math.random() < .95) return;
      } // Don't poll if livewire is offline as well.


      if (store.livewireIsOffline) return;
      component.addAction(new _default$2(method, directive.params, node));
    }, interval);
  }

  var _default$3 = /*#__PURE__*/function () {
    function _default(component, updateQueue) {
      _classCallCheck(this, _default);

      this.component = component;
      this.updateQueue = updateQueue;
    }

    _createClass(_default, [{
      key: "payload",
      value: function payload() {
        return {
          fingerprint: this.component.fingerprint,
          serverMemo: this.component.serverMemo,
          // This ensures only the type & payload properties only get sent over.
          updates: this.updateQueue.map(function (update) {
            return {
              type: update.type,
              payload: update.payload
            };
          })
        };
      }
    }, {
      key: "shouldSkipWatcher",
      value: function shouldSkipWatcher() {
        return this.updateQueue.every(function (update) {
          return update.skipWatcher;
        });
      }
    }, {
      key: "storeResponse",
      value: function storeResponse(payload) {
        return this.response = payload;
      }
    }, {
      key: "resolve",
      value: function resolve() {
        var returns = this.response.effects.returns || [];
        this.updateQueue.forEach(function (update) {
          if (update.type !== 'callMethod') return;
          update.resolve(returns[update.method] !== undefined ? returns[update.method] : null);
        });
      }
    }, {
      key: "reject",
      value: function reject() {
        this.updateQueue.forEach(function (update) {
          update.reject();
        });
      }
    }]);

    return _default;
  }();

  var _default$4 = /*#__PURE__*/function (_Message) {
    _inherits(_default, _Message);

    var _super = _createSuper(_default);

    function _default(component, action) {
      _classCallCheck(this, _default);

      return _super.call(this, component, [action]);
    }

    _createClass(_default, [{
      key: "prefetchId",
      get: function get() {
        return this.updateQueue[0].toId();
      }
    }]);

    return _default;
  }(_default$3);

  /**
   * I don't want to look at "value" attributes when diffing.
   * I commented out all the lines that compare "value"
   *
   */
  function morphAttrs(fromNode, toNode) {
    var attrs = toNode.attributes;
    var i;
    var attr;
    var attrName;
    var attrNamespaceURI;
    var attrValue;
    var fromValue; // update attributes on original DOM element

    for (i = attrs.length - 1; i >= 0; --i) {
      attr = attrs[i];
      attrName = attr.name;
      attrNamespaceURI = attr.namespaceURI;
      attrValue = attr.value;

      if (attrNamespaceURI) {
        attrName = attr.localName || attrName;
        fromValue = fromNode.getAttributeNS(attrNamespaceURI, attrName);

        if (fromValue !== attrValue) {
          if (attr.prefix === 'xmlns') {
            attrName = attr.name; // It's not allowed to set an attribute with the XMLNS namespace without specifying the `xmlns` prefix
          }

          fromNode.setAttributeNS(attrNamespaceURI, attrName, attrValue);
        }
      } else {
        fromValue = fromNode.getAttribute(attrName);

        if (fromValue !== attrValue) {
          // @livewireModification: This is the case where we don't want morphdom to pre-emptively add
          // a "display:none" if it's going to be transitioned out by Alpine.
          if (attrName === 'style' && fromNode.__livewire_transition && /display: none;/.test(attrValue)) {
            delete fromNode.__livewire_transition;
            attrValue = attrValue.replace('display: none;', '');
          }

          fromNode.setAttribute(attrName, attrValue);
        }
      }
    } // Remove any extra attributes found on the original DOM element that
    // weren't found on the target element.


    attrs = fromNode.attributes;

    for (i = attrs.length - 1; i >= 0; --i) {
      attr = attrs[i];

      if (attr.specified !== false) {
        attrName = attr.name;
        attrNamespaceURI = attr.namespaceURI;

        if (attrNamespaceURI) {
          attrName = attr.localName || attrName;

          if (!toNode.hasAttributeNS(attrNamespaceURI, attrName)) {
            fromNode.removeAttributeNS(attrNamespaceURI, attrName);
          }
        } else {
          if (!toNode.hasAttribute(attrName)) {
            // @livewireModification: This is the case where we don't want morphdom to pre-emptively remove
            // a "display:none" if it's going to be transitioned in by Alpine.
            if (attrName === 'style' && fromNode.__livewire_transition && /display: none;/.test(attr.value)) {
              delete fromNode.__livewire_transition;
              continue;
            }

            fromNode.removeAttribute(attrName);
          }
        }
      }
    }
  }

  var range; // Create a range object for efficently rendering strings to elements.

  var NS_XHTML = 'http://www.w3.org/1999/xhtml';
  var doc = typeof document === 'undefined' ? undefined : document;
  var HAS_TEMPLATE_SUPPORT = !!doc && 'content' in doc.createElement('template');
  var HAS_RANGE_SUPPORT = !!doc && doc.createRange && 'createContextualFragment' in doc.createRange();

  function createFragmentFromTemplate(str) {
    var template = doc.createElement('template');
    template.innerHTML = str;
    return template.content.childNodes[0];
  }

  function createFragmentFromRange(str) {
    if (!range) {
      range = doc.createRange();
      range.selectNode(doc.body);
    }

    var fragment = range.createContextualFragment(str);
    return fragment.childNodes[0];
  }

  function createFragmentFromWrap(str) {
    var fragment = doc.createElement('body');
    fragment.innerHTML = str;
    return fragment.childNodes[0];
  }
  /**
   * This is about the same
   * var html = new DOMParser().parseFromString(str, 'text/html');
   * return html.body.firstChild;
   *
   * @method toElement
   * @param {String} str
   */


  function toElement(str) {
    str = str.trim();

    if (HAS_TEMPLATE_SUPPORT) {
      // avoid restrictions on content for things like `<tr><th>Hi</th></tr>` which
      // createContextualFragment doesn't support
      // <template> support not available in IE
      return createFragmentFromTemplate(str);
    } else if (HAS_RANGE_SUPPORT) {
      return createFragmentFromRange(str);
    }

    return createFragmentFromWrap(str);
  }
  /**
   * Returns true if two node's names are the same.
   *
   * NOTE: We don't bother checking `namespaceURI` because you will never find two HTML elements with the same
   *       nodeName and different namespace URIs.
   *
   * @param {Element} a
   * @param {Element} b The target element
   * @return {boolean}
   */

  function compareNodeNames(fromEl, toEl) {
    var fromNodeName = fromEl.nodeName;
    var toNodeName = toEl.nodeName;

    if (fromNodeName === toNodeName) {
      return true;
    }

    if (toEl.actualize && fromNodeName.charCodeAt(0) < 91 &&
    /* from tag name is upper case */
    toNodeName.charCodeAt(0) > 90
    /* target tag name is lower case */
    ) {
        // If the target element is a virtual DOM node then we may need to normalize the tag name
        // before comparing. Normal HTML elements that are in the "http://www.w3.org/1999/xhtml"
        // are converted to upper case
        return fromNodeName === toNodeName.toUpperCase();
      } else {
      return false;
    }
  }
  /**
   * Create an element, optionally with a known namespace URI.
   *
   * @param {string} name the element name, e.g. 'div' or 'svg'
   * @param {string} [namespaceURI] the element's namespace URI, i.e. the value of
   * its `xmlns` attribute or its inferred namespace.
   *
   * @return {Element}
   */

  function createElementNS(name, namespaceURI) {
    return !namespaceURI || namespaceURI === NS_XHTML ? doc.createElement(name) : doc.createElementNS(namespaceURI, name);
  }
  /**
   * Copies the children of one DOM element to another DOM element
   */

  function moveChildren(fromEl, toEl) {
    var curChild = fromEl.firstChild;

    while (curChild) {
      var nextChild = curChild.nextSibling;
      toEl.appendChild(curChild);
      curChild = nextChild;
    }

    return toEl;
  }

  function syncBooleanAttrProp(fromEl, toEl, name) {
    if (fromEl[name] !== toEl[name]) {
      fromEl[name] = toEl[name];

      if (fromEl[name]) {
        fromEl.setAttribute(name, '');
      } else {
        fromEl.removeAttribute(name);
      }
    }
  }

  var specialElHandlers = {
    OPTION: function OPTION(fromEl, toEl) {
      var parentNode = fromEl.parentNode;

      if (parentNode) {
        var parentName = parentNode.nodeName.toUpperCase();

        if (parentName === 'OPTGROUP') {
          parentNode = parentNode.parentNode;
          parentName = parentNode && parentNode.nodeName.toUpperCase();
        }

        if (parentName === 'SELECT' && !parentNode.hasAttribute('multiple')) {
          if (fromEl.hasAttribute('selected') && !toEl.selected) {
            // Workaround for MS Edge bug where the 'selected' attribute can only be
            // removed if set to a non-empty value:
            // https://developer.microsoft.com/en-us/microsoft-edge/platform/issues/12087679/
            fromEl.setAttribute('selected', 'selected');
            fromEl.removeAttribute('selected');
          } // We have to reset select element's selectedIndex to -1, otherwise setting
          // fromEl.selected using the syncBooleanAttrProp below has no effect.
          // The correct selectedIndex will be set in the SELECT special handler below.


          parentNode.selectedIndex = -1;
        }
      }

      syncBooleanAttrProp(fromEl, toEl, 'selected');
    },

    /**
     * The "value" attribute is special for the <input> element since it sets
     * the initial value. Changing the "value" attribute without changing the
     * "value" property will have no effect since it is only used to the set the
     * initial value.  Similar for the "checked" attribute, and "disabled".
     */
    INPUT: function INPUT(fromEl, toEl) {
      syncBooleanAttrProp(fromEl, toEl, 'checked');
      syncBooleanAttrProp(fromEl, toEl, 'disabled');

      if (fromEl.value !== toEl.value) {
        fromEl.value = toEl.value;
      }

      if (!toEl.hasAttribute('value')) {
        fromEl.removeAttribute('value');
      }
    },
    TEXTAREA: function TEXTAREA(fromEl, toEl) {
      var newValue = toEl.value;

      if (fromEl.value !== newValue) {
        fromEl.value = newValue;
      }

      var firstChild = fromEl.firstChild;

      if (firstChild) {
        // Needed for IE. Apparently IE sets the placeholder as the
        // node value and vise versa. This ignores an empty update.
        var oldValue = firstChild.nodeValue;

        if (oldValue == newValue || !newValue && oldValue == fromEl.placeholder) {
          return;
        }

        firstChild.nodeValue = newValue;
      }
    },
    SELECT: function SELECT(fromEl, toEl) {
      if (!toEl.hasAttribute('multiple')) {
        var selectedIndex = -1;
        var i = 0; // We have to loop through children of fromEl, not toEl since nodes can be moved
        // from toEl to fromEl directly when morphing.
        // At the time this special handler is invoked, all children have already been morphed
        // and appended to / removed from fromEl, so using fromEl here is safe and correct.

        var curChild = fromEl.firstChild;
        var optgroup;
        var nodeName;

        while (curChild) {
          nodeName = curChild.nodeName && curChild.nodeName.toUpperCase();

          if (nodeName === 'OPTGROUP') {
            optgroup = curChild;
            curChild = optgroup.firstChild;
          } else {
            if (nodeName === 'OPTION') {
              if (curChild.hasAttribute('selected')) {
                selectedIndex = i;
                break;
              }

              i++;
            }

            curChild = curChild.nextSibling;

            if (!curChild && optgroup) {
              curChild = optgroup.nextSibling;
              optgroup = null;
            }
          }
        }

        fromEl.selectedIndex = selectedIndex;
      }
    }
  };

  // From Caleb: I had to change all the "isSameNode"s to "isEqualNode"s and now everything is working great!
  var ELEMENT_NODE = 1;
  var DOCUMENT_FRAGMENT_NODE = 11;
  var TEXT_NODE = 3;
  var COMMENT_NODE = 8;

  function noop() {}

  function defaultGetNodeKey(node) {
    return node.id;
  }

  function callHook(hook) {
    if (hook.name !== 'getNodeKey' && hook.name !== 'onBeforeElUpdated') ; // Don't call hook on non-"DOMElement" elements.


    for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      params[_key - 1] = arguments[_key];
    }

    if (typeof params[0].hasAttribute !== 'function') return;
    return hook.apply(void 0, params);
  }

  function morphdomFactory(morphAttrs) {
    return function morphdom(fromNode, toNode, options) {
      if (!options) {
        options = {};
      }

      if (typeof toNode === 'string') {
        if (fromNode.nodeName === '#document' || fromNode.nodeName === 'HTML') {
          var toNodeHtml = toNode;
          toNode = doc.createElement('html');
          toNode.innerHTML = toNodeHtml;
        } else {
          toNode = toElement(toNode);
        }
      }

      var getNodeKey = options.getNodeKey || defaultGetNodeKey;
      var onBeforeNodeAdded = options.onBeforeNodeAdded || noop;
      var onNodeAdded = options.onNodeAdded || noop;
      var onBeforeElUpdated = options.onBeforeElUpdated || noop;
      var onElUpdated = options.onElUpdated || noop;
      var onBeforeNodeDiscarded = options.onBeforeNodeDiscarded || noop;
      var onNodeDiscarded = options.onNodeDiscarded || noop;
      var onBeforeElChildrenUpdated = options.onBeforeElChildrenUpdated || noop;
      var childrenOnly = options.childrenOnly === true; // This object is used as a lookup to quickly find all keyed elements in the original DOM tree.

      var fromNodesLookup = Object.create(null);
      var keyedRemovalList = [];

      function addKeyedRemoval(key) {
        keyedRemovalList.push(key);
      }

      function walkDiscardedChildNodes(node, skipKeyedNodes) {
        if (node.nodeType === ELEMENT_NODE) {
          var curChild = node.firstChild;

          while (curChild) {
            var key = undefined;

            if (skipKeyedNodes && (key = callHook(getNodeKey, curChild))) {
              // If we are skipping keyed nodes then we add the key
              // to a list so that it can be handled at the very end.
              addKeyedRemoval(key);
            } else {
              // Only report the node as discarded if it is not keyed. We do this because
              // at the end we loop through all keyed elements that were unmatched
              // and then discard them in one final pass.
              callHook(onNodeDiscarded, curChild);

              if (curChild.firstChild) {
                walkDiscardedChildNodes(curChild, skipKeyedNodes);
              }
            }

            curChild = curChild.nextSibling;
          }
        }
      }
      /**
       * Removes a DOM node out of the original DOM
       *
       * @param  {Node} node The node to remove
       * @param  {Node} parentNode The nodes parent
       * @param  {Boolean} skipKeyedNodes If true then elements with keys will be skipped and not discarded.
       * @return {undefined}
       */


      function removeNode(node, parentNode, skipKeyedNodes) {
        if (callHook(onBeforeNodeDiscarded, node) === false) {
          return;
        }

        if (parentNode) {
          parentNode.removeChild(node);
        }

        callHook(onNodeDiscarded, node);
        walkDiscardedChildNodes(node, skipKeyedNodes);
      }

      function indexTree(node) {
        if (node.nodeType === ELEMENT_NODE || node.nodeType === DOCUMENT_FRAGMENT_NODE) {
          var curChild = node.firstChild;

          while (curChild) {
            var key = callHook(getNodeKey, curChild);

            if (key) {
              fromNodesLookup[key] = curChild;
            } // Walk recursively


            indexTree(curChild);
            curChild = curChild.nextSibling;
          }
        }
      }

      indexTree(fromNode);

      function handleNodeAdded(el) {
        callHook(onNodeAdded, el);

        if (el.skipAddingChildren) {
          return;
        }

        var curChild = el.firstChild;

        while (curChild) {
          var nextSibling = curChild.nextSibling;
          var key = callHook(getNodeKey, curChild);

          if (key) {
            var unmatchedFromEl = fromNodesLookup[key];

            if (unmatchedFromEl && compareNodeNames(curChild, unmatchedFromEl)) {
              curChild.parentNode.replaceChild(unmatchedFromEl, curChild);
              morphEl(unmatchedFromEl, curChild); // @livewireModification
              // Otherwise, "curChild" will be unnatached when it is passed to "handleNodeAdde"
              // things like .parent and .closest will break.

              curChild = unmatchedFromEl;
            }
          }

          handleNodeAdded(curChild);
          curChild = nextSibling;
        }
      }

      function cleanupFromEl(fromEl, curFromNodeChild, curFromNodeKey) {
        // We have processed all of the "to nodes". If curFromNodeChild is
        // non-null then we still have some from nodes left over that need
        // to be removed
        while (curFromNodeChild) {
          var fromNextSibling = curFromNodeChild.nextSibling;

          if (curFromNodeKey = callHook(getNodeKey, curFromNodeChild)) {
            // Since the node is keyed it might be matched up later so we defer
            // the actual removal to later
            addKeyedRemoval(curFromNodeKey);
          } else {
            // NOTE: we skip nested keyed nodes from being removed since there is
            //       still a chance they will be matched up later
            removeNode(curFromNodeChild, fromEl, true
            /* skip keyed nodes */
            );
          }

          curFromNodeChild = fromNextSibling;
        }
      }

      function morphEl(fromEl, toEl, childrenOnly) {
        var toElKey = callHook(getNodeKey, toEl);

        if (toElKey) {
          // If an element with an ID is being morphed then it will be in the final
          // DOM so clear it out of the saved elements collection
          delete fromNodesLookup[toElKey];
        }

        if (!childrenOnly) {
          if (callHook(onBeforeElUpdated, fromEl, toEl) === false) {
            return;
          } // @livewireModification.
          // I added this check to enable wire:ignore.self to not fire
          // morphAttrs, but not skip updating children as well.
          // A task that's currently impossible with the provided hooks.


          if (!fromEl.skipElUpdatingButStillUpdateChildren) {
            morphAttrs(fromEl, toEl);
          }

          callHook(onElUpdated, fromEl);

          if (callHook(onBeforeElChildrenUpdated, fromEl, toEl) === false) {
            return;
          }
        }

        if (fromEl.nodeName !== 'TEXTAREA') {
          morphChildren(fromEl, toEl);
        } else {
          if (fromEl.innerHTML != toEl.innerHTML) {
            // @livewireModification
            // Only mess with the "value" of textarea if the new dom has something
            // inside the <textarea></textarea> tag.
            specialElHandlers.TEXTAREA(fromEl, toEl);
          }
        }
      }

      function morphChildren(fromEl, toEl) {
        var curToNodeChild = toEl.firstChild;
        var curFromNodeChild = fromEl.firstChild;
        var curToNodeKey;
        var curFromNodeKey;
        var fromNextSibling;
        var toNextSibling;
        var matchingFromEl; // walk the children

        outer: while (curToNodeChild) {
          toNextSibling = curToNodeChild.nextSibling;
          curToNodeKey = callHook(getNodeKey, curToNodeChild); // walk the fromNode children all the way through

          while (curFromNodeChild) {
            fromNextSibling = curFromNodeChild.nextSibling;

            if (curToNodeChild.isSameNode && curToNodeChild.isSameNode(curFromNodeChild)) {
              curToNodeChild = toNextSibling;
              curFromNodeChild = fromNextSibling;
              continue outer;
            }

            curFromNodeKey = callHook(getNodeKey, curFromNodeChild);
            var curFromNodeType = curFromNodeChild.nodeType; // this means if the curFromNodeChild doesnt have a match with the curToNodeChild

            var isCompatible = undefined;

            if (curFromNodeType === curToNodeChild.nodeType) {
              if (curFromNodeType === ELEMENT_NODE) {
                // Both nodes being compared are Element nodes
                if (curToNodeKey) {
                  // The target node has a key so we want to match it up with the correct element
                  // in the original DOM tree
                  if (curToNodeKey !== curFromNodeKey) {
                    // The current element in the original DOM tree does not have a matching key so
                    // let's check our lookup to see if there is a matching element in the original
                    // DOM tree
                    if (matchingFromEl = fromNodesLookup[curToNodeKey]) {
                      if (fromNextSibling === matchingFromEl) {
                        // Special case for single element removals. To avoid removing the original
                        // DOM node out of the tree (since that can break CSS transitions, etc.),
                        // we will instead discard the current node and wait until the next
                        // iteration to properly match up the keyed target element with its matching
                        // element in the original tree
                        isCompatible = false;
                      } else {
                        // We found a matching keyed element somewhere in the original DOM tree.
                        // Let's move the original DOM node into the current position and morph
                        // it.
                        // NOTE: We use insertBefore instead of replaceChild because we want to go through
                        // the `removeNode()` function for the node that is being discarded so that
                        // all lifecycle hooks are correctly invoked
                        fromEl.insertBefore(matchingFromEl, curFromNodeChild); // fromNextSibling = curFromNodeChild.nextSibling;

                        if (curFromNodeKey) {
                          // Since the node is keyed it might be matched up later so we defer
                          // the actual removal to later
                          addKeyedRemoval(curFromNodeKey);
                        } else {
                          // NOTE: we skip nested keyed nodes from being removed since there is
                          //       still a chance they will be matched up later
                          removeNode(curFromNodeChild, fromEl, true
                          /* skip keyed nodes */
                          );
                        }

                        curFromNodeChild = matchingFromEl;
                      }
                    } else {
                      // The nodes are not compatible since the "to" node has a key and there
                      // is no matching keyed node in the source tree
                      isCompatible = false;
                    }
                  }
                } else if (curFromNodeKey) {
                  // The original has a key
                  isCompatible = false;
                }

                isCompatible = isCompatible !== false && compareNodeNames(curFromNodeChild, curToNodeChild);

                if (isCompatible) {
                  // @livewireModification
                  // If the two nodes are different, but the next element is an exact match,
                  // we can assume that the new node is meant to be inserted, instead of
                  // used as a morph target.
                  if (!curToNodeChild.isEqualNode(curFromNodeChild) && curToNodeChild.nextElementSibling && curToNodeChild.nextElementSibling.isEqualNode(curFromNodeChild)) {
                    isCompatible = false;
                  } else {
                    // We found compatible DOM elements so transform
                    // the current "from" node to match the current
                    // target DOM node.
                    // MORPH
                    morphEl(curFromNodeChild, curToNodeChild);
                  }
                }
              } else if (curFromNodeType === TEXT_NODE || curFromNodeType == COMMENT_NODE) {
                // Both nodes being compared are Text or Comment nodes
                isCompatible = true; // Simply update nodeValue on the original node to
                // change the text value

                if (curFromNodeChild.nodeValue !== curToNodeChild.nodeValue) {
                  curFromNodeChild.nodeValue = curToNodeChild.nodeValue;
                }
              }
            }

            if (isCompatible) {
              // Advance both the "to" child and the "from" child since we found a match
              // Nothing else to do as we already recursively called morphChildren above
              curToNodeChild = toNextSibling;
              curFromNodeChild = fromNextSibling;
              continue outer;
            } // @livewireModification
            // Before we just remove the original element, let's see if it's the very next
            // element in the "to" list. If it is, we can assume we can insert the new
            // element before the original one instead of removing it. This is kind of
            // a "look-ahead".


            if (curToNodeChild.nextElementSibling && curToNodeChild.nextElementSibling.isEqualNode(curFromNodeChild)) {
              var nodeToBeAdded = curToNodeChild.cloneNode(true);
              fromEl.insertBefore(nodeToBeAdded, curFromNodeChild);
              handleNodeAdded(nodeToBeAdded);
              curToNodeChild = curToNodeChild.nextElementSibling.nextSibling;
              curFromNodeChild = fromNextSibling;
              continue outer;
            } else {
              // No compatible match so remove the old node from the DOM and continue trying to find a
              // match in the original DOM. However, we only do this if the from node is not keyed
              // since it is possible that a keyed node might match up with a node somewhere else in the
              // target tree and we don't want to discard it just yet since it still might find a
              // home in the final DOM tree. After everything is done we will remove any keyed nodes
              // that didn't find a home
              if (curFromNodeKey) {
                // Since the node is keyed it might be matched up later so we defer
                // the actual removal to later
                addKeyedRemoval(curFromNodeKey);
              } else {
                // NOTE: we skip nested keyed nodes from being removed since there is
                //       still a chance they will be matched up later
                removeNode(curFromNodeChild, fromEl, true
                /* skip keyed nodes */
                );
              }
            }

            curFromNodeChild = fromNextSibling;
          } // END: while(curFromNodeChild) {}
          // If we got this far then we did not find a candidate match for
          // our "to node" and we exhausted all of the children "from"
          // nodes. Therefore, we will just append the current "to" node
          // to the end


          if (curToNodeKey && (matchingFromEl = fromNodesLookup[curToNodeKey]) && compareNodeNames(matchingFromEl, curToNodeChild)) {
            fromEl.appendChild(matchingFromEl); // MORPH

            morphEl(matchingFromEl, curToNodeChild);
          } else {
            var onBeforeNodeAddedResult = callHook(onBeforeNodeAdded, curToNodeChild);

            if (onBeforeNodeAddedResult !== false) {
              if (onBeforeNodeAddedResult) {
                curToNodeChild = onBeforeNodeAddedResult;
              }

              if (curToNodeChild.actualize) {
                curToNodeChild = curToNodeChild.actualize(fromEl.ownerDocument || doc);
              }

              fromEl.appendChild(curToNodeChild);
              handleNodeAdded(curToNodeChild);
            }
          }

          curToNodeChild = toNextSibling;
          curFromNodeChild = fromNextSibling;
        }

        cleanupFromEl(fromEl, curFromNodeChild, curFromNodeKey);
        var specialElHandler = specialElHandlers[fromEl.nodeName];

        if (specialElHandler && !fromEl.isLivewireModel) {
          specialElHandler(fromEl, toEl);
        }
      } // END: morphChildren(...)


      var morphedNode = fromNode;
      var morphedNodeType = morphedNode.nodeType;
      var toNodeType = toNode.nodeType;

      if (!childrenOnly) {
        // Handle the case where we are given two DOM nodes that are not
        // compatible (e.g. <div> --> <span> or <div> --> TEXT)
        if (morphedNodeType === ELEMENT_NODE) {
          if (toNodeType === ELEMENT_NODE) {
            if (!compareNodeNames(fromNode, toNode)) {
              callHook(onNodeDiscarded, fromNode);
              morphedNode = moveChildren(fromNode, createElementNS(toNode.nodeName, toNode.namespaceURI));
            }
          } else {
            // Going from an element node to a text node
            morphedNode = toNode;
          }
        } else if (morphedNodeType === TEXT_NODE || morphedNodeType === COMMENT_NODE) {
          // Text or comment node
          if (toNodeType === morphedNodeType) {
            if (morphedNode.nodeValue !== toNode.nodeValue) {
              morphedNode.nodeValue = toNode.nodeValue;
            }

            return morphedNode;
          } else {
            // Text node to something else
            morphedNode = toNode;
          }
        }
      }

      if (morphedNode === toNode) {
        // The "to node" was not compatible with the "from node" so we had to
        // toss out the "from node" and use the "to node"
        callHook(onNodeDiscarded, fromNode);
      } else {
        if (toNode.isSameNode && toNode.isSameNode(morphedNode)) {
          return;
        }

        morphEl(morphedNode, toNode, childrenOnly); // We now need to loop over any keyed nodes that might need to be
        // removed. We only do the removal if we know that the keyed node
        // never found a match. When a keyed node is matched up we remove
        // it out of fromNodesLookup and we use fromNodesLookup to determine
        // if a keyed node has been matched up or not

        if (keyedRemovalList) {
          for (var i = 0, len = keyedRemovalList.length; i < len; i++) {
            var elToRemove = fromNodesLookup[keyedRemovalList[i]];

            if (elToRemove) {
              removeNode(elToRemove, elToRemove.parentNode, false);
            }
          }
        }
      }

      if (!childrenOnly && morphedNode !== fromNode && fromNode.parentNode) {
        if (morphedNode.actualize) {
          morphedNode = morphedNode.actualize(fromNode.ownerDocument || doc);
        } // If we had to swap out the from node with a new node because the old
        // node was not compatible with the target node then we need to
        // replace the old DOM node in the original DOM tree. This is only
        // possible if the original DOM node was part of a DOM tree which
        // we know is the case if it has a parent node.


        fromNode.parentNode.replaceChild(morphedNode, fromNode);
      }

      return morphedNode;
    };
  }

  var morphdom = morphdomFactory(morphAttrs);

  var _default$5 = /*#__PURE__*/function (_Action) {
    _inherits(_default, _Action);

    var _super = _createSuper(_default);

    function _default(name, value, el) {
      var _this;

      _classCallCheck(this, _default);

      _this = _super.call(this, el);
      _this.type = 'syncInput';
      _this.name = name;
      _this.payload = {
        name: name,
        value: value
      };
      return _this;
    }

    return _default;
  }(_default);

  var _default$6 = /*#__PURE__*/function (_Action) {
    _inherits(_default, _Action);

    var _super = _createSuper(_default);

    function _default(name, value, el) {
      var _this;

      var skipWatcher = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

      _classCallCheck(this, _default);

      _this = _super.call(this, el, skipWatcher);
      _this.type = 'syncInput';
      _this.name = name;
      _this.payload = {
        name: name,
        value: value
      };
      return _this;
    }

    return _default;
  }(_default);

  var nodeInitializer = {
    initialize: function initialize(el, component) {
      var _this = this;

      if (store.initialRenderIsFinished && el.tagName.toLowerCase() === 'script') {
        eval(el.innerHTML);
        return false;
      }

      wireDirectives(el).all().forEach(function (directive) {
        switch (directive.type) {
          case 'init':
            _this.fireActionRightAway(el, directive, component);

            break;

          case 'model':
            DOM.setInputValueFromModel(el, component);

            _this.attachModelListener(el, directive, component);

            break;

          default:
            if (store.directives.has(directive.type)) {
              store.directives.call(directive.type, el, directive, component);
            }

            _this.attachDomListener(el, directive, component);

            break;
        }
      });
      store.callHook('element.initialized', el, component);
    },
    fireActionRightAway: function fireActionRightAway(el, directive, component) {
      var method = directive.value ? directive.method : '$refresh';
      component.addAction(new _default$2(method, directive.params, el));
    },
    attachModelListener: function attachModelListener(el, directive, component) {
      // This is used by morphdom: morphdom.js:391
      el.isLivewireModel = true;
      var isLazy = directive.modifiers.includes('lazy');

      var debounceIf = function debounceIf(condition, callback, time) {
        return condition ? component.modelSyncDebounce(callback, time) : callback;
      };

      var hasDebounceModifier = directive.modifiers.includes('debounce');
      store.callHook('interceptWireModelAttachListener', directive, el, component); // File uploads are handled by UploadFiles.js.

      if (el.tagName.toLowerCase() === 'input' && el.type === 'file') return;
      var event = el.tagName.toLowerCase() === 'select' || ['checkbox', 'radio'].includes(el.type) || directive.modifiers.includes('lazy') ? 'change' : 'input'; // If it's a text input and not .lazy, debounce, otherwise fire immediately.

      var handler = debounceIf(hasDebounceModifier || DOM.isTextInput(el) && !isLazy, function (e) {
        var model = directive.value;
        var el = e.target; // We have to check for typeof e.detail here for IE 11.

        var value = e instanceof CustomEvent && typeof e.detail != 'undefined' && typeof window.document.documentMode == 'undefined' ? e.detail : DOM.valueFromInput(el, component);

        if (directive.modifiers.includes('defer')) {
          component.addAction(new _default$6(model, value, el));
        } else {
          component.addAction(new _default$5(model, value, el));
        }
      }, directive.durationOr(150));
      el.addEventListener(event, handler);
      component.addListenerForTeardown(function () {
        el.removeEventListener(event, handler);
      });
    },
    attachDomListener: function attachDomListener(el, directive, component) {
      switch (directive.type) {
        case 'keydown':
        case 'keyup':
          this.attachListener(el, directive, component, function (e) {
            // Detect system modifier key combinations if specified.
            var systemKeyModifiers = ['ctrl', 'shift', 'alt', 'meta', 'cmd', 'super'];
            var selectedSystemKeyModifiers = systemKeyModifiers.filter(function (key) {
              return directive.modifiers.includes(key);
            });

            if (selectedSystemKeyModifiers.length > 0) {
              var selectedButNotPressedKeyModifiers = selectedSystemKeyModifiers.filter(function (key) {
                // Alias "cmd" and "super" to "meta"
                if (key === 'cmd' || key === 'super') key = 'meta';
                return !e["".concat(key, "Key")];
              });
              if (selectedButNotPressedKeyModifiers.length > 0) return false;
            } // Handle spacebar


            if (e.keyCode === 32 || e.key === ' ' || e.key === 'Spacebar') {
              return directive.modifiers.includes('space');
            } // Strip 'debounce' modifier and time modifiers from modifiers list


            var modifiers = directive.modifiers.filter(function (modifier) {
              return !modifier.match(/^debounce$/) && !modifier.match(/^[0-9]+m?s$/);
            }); // Only handle listener if no, or matching key modifiers are passed.
            // It's important to check that e.key exists - OnePassword's extension does weird things.

            return Boolean(modifiers.length === 0 || e.key && modifiers.includes(kebabCase(e.key)));
          });
          break;

        case 'click':
          this.attachListener(el, directive, component, function (e) {
            // We only care about elements that have the .self modifier on them.
            if (!directive.modifiers.includes('self')) return; // This ensures a listener is only run if the event originated
            // on the elemenet that registered it (not children).
            // This is useful for things like modal back-drop listeners.

            return el.isSameNode(e.target);
          });
          break;

        default:
          this.attachListener(el, directive, component);
          break;
      }
    },
    attachListener: function attachListener(el, directive, component, callback) {
      var _this2 = this;

      if (directive.modifiers.includes('prefetch')) {
        el.addEventListener('mouseenter', function () {
          component.addPrefetchAction(new _default$2(directive.method, directive.params, el));
        });
      }

      var event = directive.type;

      var handler = function handler(e) {
        if (callback && callback(e) === false) {
          return;
        }

        component.callAfterModelDebounce(function () {
          var el = e.target;
          directive.setEventContext(e); // This is outside the conditional below so "wire:click.prevent" without
          // a value still prevents default.

          _this2.preventAndStop(e, directive.modifiers);

          var method = directive.method;
          var params = directive.params;

          if (params.length === 0 && e instanceof CustomEvent && e.detail) {
            params.push(e.detail);
          } // Check for global event emission.


          if (method === '$emit') {
            var _component$scopedList;

            (_component$scopedList = component.scopedListeners).call.apply(_component$scopedList, _toConsumableArray(params));

            store.emit.apply(store, _toConsumableArray(params));
            return;
          }

          if (method === '$emitUp') {
            store.emitUp.apply(store, [el].concat(_toConsumableArray(params)));
            return;
          }

          if (method === '$emitSelf') {
            store.emitSelf.apply(store, [component.id].concat(_toConsumableArray(params)));
            return;
          }

          if (method === '$emitTo') {
            store.emitTo.apply(store, _toConsumableArray(params));
            return;
          }

          if (directive.value) {
            component.addAction(new _default$2(method, params, el));
          }
        });
      };

      var debounceIf = function debounceIf(condition, callback, time) {
        return condition ? debounce(callback, time) : callback;
      };

      var hasDebounceModifier = directive.modifiers.includes('debounce');
      var debouncedHandler = debounceIf(hasDebounceModifier, handler, directive.durationOr(150));
      el.addEventListener(event, debouncedHandler);
      component.addListenerForTeardown(function () {
        el.removeEventListener(event, debouncedHandler);
      });
    },
    preventAndStop: function preventAndStop(event, modifiers) {
      modifiers.includes('prevent') && event.preventDefault();
      modifiers.includes('stop') && event.stopPropagation();
    }
  };

  var PrefetchManager = /*#__PURE__*/function () {
    function PrefetchManager(component) {
      _classCallCheck(this, PrefetchManager);

      this.component = component;
      this.prefetchMessagesByActionId = {};
    }

    _createClass(PrefetchManager, [{
      key: "addMessage",
      value: function addMessage(message) {
        this.prefetchMessagesByActionId[message.prefetchId] = message;
      }
    }, {
      key: "actionHasPrefetch",
      value: function actionHasPrefetch(action) {
        return Object.keys(this.prefetchMessagesByActionId).includes(action.toId());
      }
    }, {
      key: "actionPrefetchResponseHasBeenReceived",
      value: function actionPrefetchResponseHasBeenReceived(action) {
        return !!this.getPrefetchMessageByAction(action).response;
      }
    }, {
      key: "getPrefetchMessageByAction",
      value: function getPrefetchMessageByAction(action) {
        return this.prefetchMessagesByActionId[action.toId()];
      }
    }, {
      key: "clearPrefetches",
      value: function clearPrefetches() {
        this.prefetchMessagesByActionId = {};
      }
    }]);

    return PrefetchManager;
  }();

  function LoadingStates () {
    store.registerHook('component.initialized', function (component) {
      component.targetedLoadingElsByAction = {};
      component.genericLoadingEls = [];
      component.currentlyActiveLoadingEls = [];
      component.currentlyActiveUploadLoadingEls = [];
    });
    store.registerHook('element.initialized', function (el, component) {
      var directives = wireDirectives(el);
      if (directives.missing('loading')) return;
      var loadingDirectives = directives.directives.filter(function (i) {
        return i.type === 'loading';
      });
      loadingDirectives.forEach(function (directive) {
        processLoadingDirective(component, el, directive);
      });
    });
    store.registerHook('message.sent', function (message, component) {
      var actions = message.updateQueue.filter(function (action) {
        return action.type === 'callMethod';
      }).map(function (action) {
        return action.payload.method;
      });
      var models = message.updateQueue.filter(function (action) {
        return action.type === 'syncInput';
      }).map(function (action) {
        return action.payload.name;
      });
      setLoading(component, actions.concat(models));
    });
    store.registerHook('message.failed', function (message, component) {
      unsetLoading(component);
    });
    store.registerHook('message.received', function (message, component) {
      unsetLoading(component);
    });
    store.registerHook('element.removed', function (el, component) {
      removeLoadingEl(component, el);
    });
  }

  function processLoadingDirective(component, el, directive) {
    // If this element is going to be dealing with loading states.
    // We will initialize an "undo" stack upfront, so we don't
    // have to deal with isset() type conditionals later.
    el.__livewire_on_finish_loading = [];
    var actionNames = false;
    var directives = wireDirectives(el);

    if (directives.get('target')) {
      // wire:target overrides any automatic loading scoping we do.
      actionNames = directives.get('target').value.split(',').map(function (s) {
        return s.trim();
      });
    } else {
      // If there is no wire:target, let's check for the existance of a wire:click="foo" or something,
      // and automatically scope this loading directive to that action.
      var nonActionOrModelLivewireDirectives = ['init', 'dirty', 'offline', 'target', 'loading', 'poll', 'ignore', 'key', 'id'];
      actionNames = directives.all().filter(function (i) {
        return !nonActionOrModelLivewireDirectives.includes(i.type);
      }).map(function (i) {
        return i.method;
      }); // If we found nothing, just set the loading directive to the global component. (run on every request)

      if (actionNames.length < 1) actionNames = false;
    }

    addLoadingEl(component, el, directive, actionNames);
  }

  function addLoadingEl(component, el, directive, actionsNames) {
    if (actionsNames) {
      actionsNames.forEach(function (actionsName) {
        if (component.targetedLoadingElsByAction[actionsName]) {
          component.targetedLoadingElsByAction[actionsName].push({
            el: el,
            directive: directive
          });
        } else {
          component.targetedLoadingElsByAction[actionsName] = [{
            el: el,
            directive: directive
          }];
        }
      });
    } else {
      component.genericLoadingEls.push({
        el: el,
        directive: directive
      });
    }
  }

  function removeLoadingEl(component, el) {
    // Look through the global/generic elements for the element to remove.
    component.genericLoadingEls.forEach(function (element, index) {
      if (element.el.isSameNode(el)) {
        component.genericLoadingEls.splice(index, 1);
      }
    }); // Look through the targeted elements to remove.

    Object.keys(component.targetedLoadingElsByAction).forEach(function (key) {
      component.targetedLoadingElsByAction[key] = component.targetedLoadingElsByAction[key].filter(function (element) {
        return !element.el.isSameNode(el);
      });
    });
  }

  function setLoading(component, actions) {
    var actionTargetedEls = actions.map(function (action) {
      return component.targetedLoadingElsByAction[action];
    }).filter(function (el) {
      return el;
    }).flat();
    var allEls = component.genericLoadingEls.concat(actionTargetedEls);
    startLoading(allEls);
    component.currentlyActiveLoadingEls = allEls;
  }

  function setUploadLoading(component, modelName) {
    var actionTargetedEls = component.targetedLoadingElsByAction[modelName] || [];
    var allEls = component.genericLoadingEls.concat(actionTargetedEls);
    startLoading(allEls);
    component.currentlyActiveUploadLoadingEls = allEls;
  }
  function unsetUploadLoading(component) {
    endLoading(component.currentlyActiveUploadLoadingEls);
    component.currentlyActiveUploadLoadingEls = [];
  }

  function unsetLoading(component) {
    endLoading(component.currentlyActiveLoadingEls);
    component.currentlyActiveLoadingEls = [];
  }

  function startLoading(els) {
    els.forEach(function (_ref) {
      var el = _ref.el,
          directive = _ref.directive;

      if (directive.modifiers.includes('class')) {
        var classes = directive.value.split(' ').filter(Boolean);
        doAndSetCallbackOnElToUndo(el, directive, function () {
          var _el$classList;

          return (_el$classList = el.classList).add.apply(_el$classList, _toConsumableArray(classes));
        }, function () {
          var _el$classList2;

          return (_el$classList2 = el.classList).remove.apply(_el$classList2, _toConsumableArray(classes));
        });
      } else if (directive.modifiers.includes('attr')) {
        doAndSetCallbackOnElToUndo(el, directive, function () {
          return el.setAttribute(directive.value, true);
        }, function () {
          return el.removeAttribute(directive.value);
        });
      } else {
        var cache = window.getComputedStyle(el, null).getPropertyValue('display');
        doAndSetCallbackOnElToUndo(el, directive, function () {
          el.style.display = directive.modifiers.includes('remove') ? cache : 'inline-block';
        }, function () {
          el.style.display = 'none';
        });
      }
    });
  }

  function doAndSetCallbackOnElToUndo(el, directive, doCallback, undoCallback) {
    if (directive.modifiers.includes('remove')) {
      var _ref2 = [undoCallback, doCallback];
      doCallback = _ref2[0];
      undoCallback = _ref2[1];
    }

    if (directive.modifiers.includes('delay')) {
      var timeout = setTimeout(function () {
        doCallback();

        el.__livewire_on_finish_loading.push(function () {
          return undoCallback();
        });
      }, 200);

      el.__livewire_on_finish_loading.push(function () {
        return clearTimeout(timeout);
      });
    } else {
      doCallback();

      el.__livewire_on_finish_loading.push(function () {
        return undoCallback();
      });
    }
  }

  function endLoading(els) {
    els.forEach(function (_ref3) {
      var el = _ref3.el;

      while (el.__livewire_on_finish_loading.length > 0) {
        el.__livewire_on_finish_loading.shift()();
      }
    });
  }

  var MessageBag = /*#__PURE__*/function () {
    function MessageBag() {
      _classCallCheck(this, MessageBag);

      this.bag = {};
    }

    _createClass(MessageBag, [{
      key: "add",
      value: function add(name, thing) {
        if (!this.bag[name]) {
          this.bag[name] = [];
        }

        this.bag[name].push(thing);
      }
    }, {
      key: "push",
      value: function push(name, thing) {
        this.add(name, thing);
      }
    }, {
      key: "first",
      value: function first(name) {
        if (!this.bag[name]) return null;
        return this.bag[name][0];
      }
    }, {
      key: "last",
      value: function last(name) {
        return this.bag[name].slice(-1)[0];
      }
    }, {
      key: "get",
      value: function get(name) {
        return this.bag[name];
      }
    }, {
      key: "shift",
      value: function shift(name) {
        return this.bag[name].shift();
      }
    }, {
      key: "call",
      value: function call(name) {
        for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          params[_key - 1] = arguments[_key];
        }

        (this.listeners[name] || []).forEach(function (callback) {
          callback.apply(void 0, params);
        });
      }
    }, {
      key: "has",
      value: function has(name) {
        return Object.keys(this.listeners).includes(name);
      }
    }]);

    return MessageBag;
  }();

  var UploadManager = /*#__PURE__*/function () {
    function UploadManager(component) {
      _classCallCheck(this, UploadManager);

      this.component = component;
      this.uploadBag = new MessageBag();
      this.removeBag = new MessageBag();
    }

    _createClass(UploadManager, [{
      key: "registerListeners",
      value: function registerListeners() {
        var _this = this;

        this.component.on('upload:generatedSignedUrl', function (name, url) {
          // We have to add reduntant "setLoading" calls because the dom-patch
          // from the first response will clear the setUploadLoading call
          // from the first upload call.
          setUploadLoading(_this.component, name);

          _this.handleSignedUrl(name, url);
        });
        this.component.on('upload:generatedSignedUrlForS3', function (name, payload) {
          setUploadLoading(_this.component, name);

          _this.handleS3PreSignedUrl(name, payload);
        });
        this.component.on('upload:finished', function (name, tmpFilenames) {
          return _this.markUploadFinished(name, tmpFilenames);
        });
        this.component.on('upload:errored', function (name) {
          return _this.markUploadErrored(name);
        });
        this.component.on('upload:removed', function (name, tmpFilename) {
          return _this.removeBag.shift(name).finishCallback(tmpFilename);
        });
      }
    }, {
      key: "upload",
      value: function upload(name, file, finishCallback, errorCallback, progressCallback) {
        this.setUpload(name, {
          files: [file],
          multiple: false,
          finishCallback: finishCallback,
          errorCallback: errorCallback,
          progressCallback: progressCallback
        });
      }
    }, {
      key: "uploadMultiple",
      value: function uploadMultiple(name, files, finishCallback, errorCallback, progressCallback) {
        this.setUpload(name, {
          files: Array.from(files),
          multiple: true,
          finishCallback: finishCallback,
          errorCallback: errorCallback,
          progressCallback: progressCallback
        });
      }
    }, {
      key: "removeUpload",
      value: function removeUpload(name, tmpFilename, finishCallback) {
        this.removeBag.push(name, {
          tmpFilename: tmpFilename,
          finishCallback: finishCallback
        });
        this.component.call('removeUpload', name, tmpFilename);
      }
    }, {
      key: "setUpload",
      value: function setUpload(name, uploadObject) {
        this.uploadBag.add(name, uploadObject);

        if (this.uploadBag.get(name).length === 1) {
          this.startUpload(name, uploadObject);
        }
      }
    }, {
      key: "handleSignedUrl",
      value: function handleSignedUrl(name, url) {
        var formData = new FormData();
        Array.from(this.uploadBag.first(name).files).forEach(function (file) {
          return formData.append('files[]', file);
        });
        var headers = {
          'X-CSRF-TOKEN': getCsrfToken(),
          'Accept': 'application/json'
        };
        this.makeRequest(name, formData, 'post', url, headers, function (response) {
          return response.paths;
        });
      }
    }, {
      key: "handleS3PreSignedUrl",
      value: function handleS3PreSignedUrl(name, payload) {
        var formData = this.uploadBag.first(name).files[0];
        var headers = payload.headers;
        if ('Host' in headers) delete headers.Host;
        var url = payload.url;
        this.makeRequest(name, formData, 'put', url, headers, function (response) {
          return [payload.path];
        });
      }
    }, {
      key: "makeRequest",
      value: function makeRequest(name, formData, method, url, headers, retrievePaths) {
        var _this2 = this;

        var request = new XMLHttpRequest();
        request.open(method, url);
        Object.entries(headers).forEach(function (_ref) {
          var _ref2 = _slicedToArray(_ref, 2),
              key = _ref2[0],
              value = _ref2[1];

          request.setRequestHeader(key, value);
        });
        request.upload.addEventListener('progress', function (e) {
          e.detail = {};
          e.detail.progress = Math.round(e.loaded * 100 / e.total);

          _this2.uploadBag.first(name).progressCallback(e);
        });
        request.addEventListener('load', function () {
          if ((request.status + '')[0] === '2') {
            var paths = retrievePaths(request.response && JSON.parse(request.response));

            _this2.component.call('finishUpload', name, paths, _this2.uploadBag.first(name).multiple);

            return;
          }

          var errors = null;

          if (request.status === 422) {
            errors = request.response;
          }

          _this2.component.call('uploadErrored', name, errors, _this2.uploadBag.first(name).multiple);
        });
        request.send(formData);
      }
    }, {
      key: "startUpload",
      value: function startUpload(name, uploadObject) {
        var fileInfos = uploadObject.files.map(function (file) {
          return {
            name: file.name,
            size: file.size,
            type: file.type
          };
        });
        this.component.call('startUpload', name, fileInfos, uploadObject.multiple);
        setUploadLoading(this.component, name);
      }
    }, {
      key: "markUploadFinished",
      value: function markUploadFinished(name, tmpFilenames) {
        unsetUploadLoading(this.component);
        var uploadObject = this.uploadBag.shift(name);
        uploadObject.finishCallback(uploadObject.multiple ? tmpFilenames : tmpFilenames[0]);
        if (this.uploadBag.get(name).length > 0) this.startUpload(name, this.uploadBag.last(name));
      }
    }, {
      key: "markUploadErrored",
      value: function markUploadErrored(name) {
        unsetUploadLoading(this.component);
        this.uploadBag.shift(name).errorCallback();
        if (this.uploadBag.get(name).length > 0) this.startUpload(name, this.uploadBag.last(name));
      }
    }]);

    return UploadManager;
  }();

  var Component = /*#__PURE__*/function () {
    function Component(el, connection) {
      _classCallCheck(this, Component);

      el.__livewire = this;
      this.el = el;
      this.lastFreshHtml = this.el.outerHTML;
      this.id = this.el.getAttribute('wire:id');
      this.connection = connection;
      var initialData = JSON.parse(this.el.getAttribute('wire:initial-data'));
      this.el.removeAttribute('wire:initial-data');
      this.fingerprint = initialData.fingerprint;
      this.serverMemo = initialData.serverMemo;
      this.effects = initialData.effects;
      this.listeners = this.effects.listeners;
      this.updateQueue = [];
      this.deferredActions = {};
      this.tearDownCallbacks = [];
      this.messageInTransit = undefined;
      this.scopedListeners = new MessageBus();
      this.prefetchManager = new PrefetchManager(this);
      this.uploadManager = new UploadManager(this);
      this.watchers = {};
      store.callHook('component.initialized', this);
      this.initialize();
      this.uploadManager.registerListeners();
      if (this.effects.redirect) return this.redirect(this.effects.redirect);
    }

    _createClass(Component, [{
      key: "initialize",
      value: function initialize() {
        var _this = this;

        this.walk( // Will run for every node in the component tree (not child component nodes).
        function (el) {
          return nodeInitializer.initialize(el, _this);
        }, // When new component is encountered in the tree, add it.
        function (el) {
          return store.addComponent(new Component(el, _this.connection));
        });
      }
    }, {
      key: "get",
      value: function get(name) {
        // The .split() stuff is to support dot-notation.
        return name.split('.').reduce(function (carry, segment) {
          return carry[segment];
        }, this.data);
      }
    }, {
      key: "updateServerMemoFromResponseAndMergeBackIntoResponse",
      value: function updateServerMemoFromResponseAndMergeBackIntoResponse(message) {
        var _this2 = this;

        // We have to do a fair amount of object merging here, but we can't use expressive syntax like {...}
        // because browsers mess with the object key order which will break Livewire request checksum checks.
        Object.entries(message.response.serverMemo).forEach(function (_ref) {
          var _ref2 = _slicedToArray(_ref, 2),
              key = _ref2[0],
              value = _ref2[1];

          // Because "data" is "partial" from the server, we have to deep merge it.
          if (key === 'data') {
            Object.entries(value || {}).forEach(function (_ref3) {
              var _ref4 = _slicedToArray(_ref3, 2),
                  dataKey = _ref4[0],
                  dataValue = _ref4[1];

              _this2.serverMemo.data[dataKey] = dataValue;
              if (message.shouldSkipWatcher()) return; // Because Livewire (for payload reduction purposes) only returns the data that has changed,
              // we can use all the data keys from the response as watcher triggers.

              Object.entries(_this2.watchers).forEach(function (_ref5) {
                var _ref6 = _slicedToArray(_ref5, 2),
                    key = _ref6[0],
                    watchers = _ref6[1];

                var originalSplitKey = key.split('.');
                var basePropertyName = originalSplitKey.shift();
                var restOfPropertyName = originalSplitKey.join('.');

                if (basePropertyName == dataKey) {
                  // If the key deals with nested data, use the "get" function to get
                  // the most nested data. Otherwise, return the entire data chunk.
                  var potentiallyNestedValue = !!restOfPropertyName ? getValue(dataValue, restOfPropertyName) : dataValue;
                  watchers.forEach(function (watcher) {
                    return watcher(potentiallyNestedValue);
                  });
                }
              });
            });
          } else {
            // Every other key, we can just overwrite.
            _this2.serverMemo[key] = value;
          }
        }); // Merge back serverMemo changes so the response data is no longer incomplete.

        message.response.serverMemo = Object.assign({}, this.serverMemo);
      }
    }, {
      key: "watch",
      value: function watch(name, callback) {
        if (!this.watchers[name]) this.watchers[name] = [];
        this.watchers[name].push(callback);
      }
    }, {
      key: "set",
      value: function set(name, value) {
        var defer = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
        var skipWatcher = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;

        if (defer) {
          this.addAction(new _default$6(name, value, this.el, skipWatcher));
        } else {
          this.addAction(new _default$2('$set', [name, value], this.el, skipWatcher));
        }
      }
    }, {
      key: "sync",
      value: function sync(name, value) {
        var defer = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

        if (defer) {
          this.addAction(new _default$6(name, value, this.el));
        } else {
          this.addAction(new _default$5(name, value, this.el));
        }
      }
    }, {
      key: "call",
      value: function call(method) {
        var _this3 = this;

        for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          params[_key - 1] = arguments[_key];
        }

        return new Promise(function (resolve, reject) {
          var action = new _default$2(method, params, _this3.el);

          _this3.addAction(action);

          action.onResolve(function (thing) {
            return resolve(thing);
          });
          action.onReject(function (thing) {
            return reject(thing);
          });
        });
      }
    }, {
      key: "on",
      value: function on(event, callback) {
        this.scopedListeners.register(event, callback);
      }
    }, {
      key: "addAction",
      value: function addAction(action) {
        if (action instanceof _default$6) {
          this.deferredActions[action.name] = action;
          return;
        }

        if (this.prefetchManager.actionHasPrefetch(action) && this.prefetchManager.actionPrefetchResponseHasBeenReceived(action)) {
          var message = this.prefetchManager.getPrefetchMessageByAction(action);
          this.handleResponse(message);
          this.prefetchManager.clearPrefetches();
          return;
        }

        this.updateQueue.push(action); // This debounce is here in-case two events fire at the "same" time:
        // For example: if you are listening for a click on element A,
        // and a "blur" on element B. If element B has focus, and then,
        // you click on element A, the blur event will fire before the "click"
        // event. This debounce captures them both in the actionsQueue and sends
        // them off at the same time.
        // Note: currently, it's set to 5ms, that might not be the right amount, we'll see.

        debounce(this.fireMessage, 5).apply(this); // Clear prefetches.

        this.prefetchManager.clearPrefetches();
      }
    }, {
      key: "fireMessage",
      value: function fireMessage() {
        var _this4 = this;

        if (this.messageInTransit) return;
        Object.entries(this.deferredActions).forEach(function (_ref7) {
          var _ref8 = _slicedToArray(_ref7, 2),
              modelName = _ref8[0],
              action = _ref8[1];

          _this4.updateQueue.unshift(action);
        });
        this.deferredActions = {};
        this.messageInTransit = new _default$3(this, this.updateQueue);

        var sendMessage = function sendMessage() {
          _this4.connection.sendMessage(_this4.messageInTransit);

          store.callHook('message.sent', _this4.messageInTransit, _this4);
          _this4.updateQueue = [];
        };

        if (window.capturedRequestsForDusk) {
          window.capturedRequestsForDusk.push(sendMessage);
        } else {
          sendMessage();
        }
      }
    }, {
      key: "messageSendFailed",
      value: function messageSendFailed() {
        store.callHook('message.failed', this.messageInTransit, this);
        this.messageInTransit.reject();
        this.messageInTransit = null;
      }
    }, {
      key: "receiveMessage",
      value: function receiveMessage(message, payload) {
        message.storeResponse(payload);
        if (message instanceof _default$4) return;
        this.handleResponse(message); // This bit of logic ensures that if actions were queued while a request was
        // out to the server, they are sent when the request comes back.

        if (this.updateQueue.length > 0) {
          this.fireMessage();
        }

        dispatch('livewire:update');
      }
    }, {
      key: "handleResponse",
      value: function handleResponse(message) {
        var _this5 = this;

        var response = message.response;
        this.updateServerMemoFromResponseAndMergeBackIntoResponse(message);
        store.callHook('message.received', message, this); // This means "$this->redirect()" was called in the component. let's just bail and redirect.

        if (response.effects.redirect) {
          this.redirect(response.effects.redirect);
          return;
        }

        if (response.effects.html) {
          // If we get HTML from the server, store it for the next time we might not.
          this.lastFreshHtml = response.effects.html;
          this.handleMorph(response.effects.html.trim());
        } else {
          // It's important to still "morphdom" even when the server HTML hasn't changed,
          // because Alpine needs to be given the chance to update.
          this.handleMorph(this.lastFreshHtml);
        }

        if (response.effects.dirty) {
          this.forceRefreshDataBoundElementsMarkedAsDirty(response.effects.dirty);
        }

        if (!message.replaying) {
          this.messageInTransit && this.messageInTransit.resolve();
          this.messageInTransit = null;

          if (response.effects.emits && response.effects.emits.length > 0) {
            response.effects.emits.forEach(function (event) {
              var _this5$scopedListener;

              (_this5$scopedListener = _this5.scopedListeners).call.apply(_this5$scopedListener, [event.event].concat(_toConsumableArray(event.params)));

              if (event.selfOnly) {
                store.emitSelf.apply(store, [_this5.id, event.event].concat(_toConsumableArray(event.params)));
              } else if (event.to) {
                store.emitTo.apply(store, [event.to, event.event].concat(_toConsumableArray(event.params)));
              } else if (event.ancestorsOnly) {
                store.emitUp.apply(store, [_this5.el, event.event].concat(_toConsumableArray(event.params)));
              } else {
                store.emit.apply(store, [event.event].concat(_toConsumableArray(event.params)));
              }
            });
          }

          if (response.effects.dispatches && response.effects.dispatches.length > 0) {
            response.effects.dispatches.forEach(function (event) {
              var data = event.data ? event.data : {};
              var e = new CustomEvent(event.event, {
                bubbles: true,
                detail: data
              });

              _this5.el.dispatchEvent(e);
            });
          }
        }

        store.callHook('message.processed', message, this);
      }
    }, {
      key: "redirect",
      value: function redirect(url) {
        if (window.Turbolinks && window.Turbolinks.supported) {
          window.Turbolinks.visit(url);
        } else {
          window.location.href = url;
        }
      }
    }, {
      key: "forceRefreshDataBoundElementsMarkedAsDirty",
      value: function forceRefreshDataBoundElementsMarkedAsDirty(dirtyInputs) {
        var _this6 = this;

        this.walk(function (el) {
          var directives = wireDirectives(el);
          if (directives.missing('model')) return;
          var modelValue = directives.get('model').value;
          if (DOM.hasFocus(el) && !dirtyInputs.includes(modelValue)) return;
          DOM.setInputValueFromModel(el, _this6);
        });
      }
    }, {
      key: "addPrefetchAction",
      value: function addPrefetchAction(action) {
        if (this.prefetchManager.actionHasPrefetch(action)) {
          return;
        }

        var message = new _default$4(this, action);
        this.prefetchManager.addMessage(message);
        this.connection.sendMessage(message);
      }
    }, {
      key: "handleMorph",
      value: function handleMorph(dom) {
        var _this7 = this;

        this.morphChanges = {
          changed: [],
          added: [],
          removed: []
        };
        morphdom(this.el, dom, {
          childrenOnly: false,
          getNodeKey: function getNodeKey(node) {
            // This allows the tracking of elements by the "key" attribute, like in VueJs.
            return node.hasAttribute("wire:key") ? node.getAttribute("wire:key") : // If no "key", then first check for "wire:id", then "id"
            node.hasAttribute("wire:id") ? node.getAttribute("wire:id") : node.id;
          },
          onBeforeNodeAdded: function onBeforeNodeAdded(node) {//
          },
          onBeforeNodeDiscarded: function onBeforeNodeDiscarded(node) {
            // If the node is from x-if with a transition.
            if (node.__x_inserted_me && Array.from(node.attributes).some(function (attr) {
              return /x-transition/.test(attr.name);
            })) {
              return false;
            }
          },
          onNodeDiscarded: function onNodeDiscarded(node) {
            store.callHook('element.removed', node, _this7);

            if (node.__livewire) {
              store.removeComponent(node.__livewire);
            }

            _this7.morphChanges.removed.push(node);
          },
          onBeforeElChildrenUpdated: function onBeforeElChildrenUpdated(node) {//
          },
          onBeforeElUpdated: function onBeforeElUpdated(from, to) {
            // Because morphdom also supports vDom nodes, it uses isSameNode to detect
            // sameness. When dealing with DOM nodes, we want isEqualNode, otherwise
            // isSameNode will ALWAYS return false.
            if (from.isEqualNode(to)) {
              return false;
            }

            store.callHook('element.updating', from, to, _this7); // Reset the index of wire:modeled select elements in the
            // "to" node before doing the diff, so that the options
            // have the proper in-memory .selected value set.

            if (from.hasAttribute('wire:model') && from.tagName.toUpperCase() === 'SELECT') {
              to.selectedIndex = -1;
            } // If the element is x-show.transition.


            if (Array.from(from.attributes).map(function (attr) {
              return attr.name;
            }).some(function (name) {
              return /x-show.transition/.test(name) || /x-transition/.test(name);
            })) {
              from.__livewire_transition = true;
            }

            var fromDirectives = wireDirectives(from); // Honor the "wire:ignore" attribute or the .__livewire_ignore element property.

            if (fromDirectives.has('ignore') || from.__livewire_ignore === true || from.__livewire_ignore_self === true) {
              if (fromDirectives.has('ignore') && fromDirectives.get('ignore').modifiers.includes('self') || from.__livewire_ignore_self === true) {
                // Don't update children of "wire:ingore.self" attribute.
                from.skipElUpdatingButStillUpdateChildren = true;
              } else {
                return false;
              }
            } // Children will update themselves.


            if (DOM.isComponentRootEl(from) && from.getAttribute('wire:id') !== _this7.id) return false; // If the element we are updating is an Alpine component...

            if (from.__x) {
              // Then temporarily clone it (with it's data) to the "to" element.
              // This should simulate backend Livewire being aware of Alpine changes.
              window.Alpine.clone(from.__x, to);
            }
          },
          onElUpdated: function onElUpdated(node) {
            _this7.morphChanges.changed.push(node);

            store.callHook('element.updated', node, _this7);
          },
          onNodeAdded: function onNodeAdded(node) {
            var closestComponentId = DOM.closestRoot(node).getAttribute('wire:id');

            if (closestComponentId === _this7.id) {
              if (nodeInitializer.initialize(node, _this7) === false) {
                return false;
              }
            } else if (DOM.isComponentRootEl(node)) {
              store.addComponent(new Component(node, _this7.connection)); // We don't need to initialize children, the
              // new Component constructor will do that for us.

              node.skipAddingChildren = true;
            }

            _this7.morphChanges.added.push(node);
          }
        });
      }
    }, {
      key: "walk",
      value: function walk$1(callback) {
        var _this8 = this;

        var callbackWhenNewComponentIsEncountered = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function (el) {};

        walk(this.el, function (el) {
          // Skip the root component element.
          if (el.isSameNode(_this8.el)) {
            callback(el);
            return;
          } // If we encounter a nested component, skip walking that tree.


          if (el.hasAttribute('wire:id')) {
            callbackWhenNewComponentIsEncountered(el);
            return false;
          }

          if (callback(el) === false) {
            return false;
          }
        });
      }
    }, {
      key: "modelSyncDebounce",
      value: function modelSyncDebounce(callback, time) {
        // Prepare yourself for what's happening here.
        // Any text input with wire:model on it should be "debounced" by ~150ms by default.
        // We can't use a simple debounce function because we need a way to clear all the pending
        // debounces if a user submits a form or performs some other action.
        // This is a modified debounce function that acts just like a debounce, except it stores
        // the pending callbacks in a global property so we can "clear them" on command instead
        // of waiting for their setTimeouts to expire. I know.
        if (!this.modelDebounceCallbacks) this.modelDebounceCallbacks = []; // This is a "null" callback. Each wire:model will resister one of these upon initialization.

        var callbackRegister = {
          callback: function callback() {}
        };
        this.modelDebounceCallbacks.push(callbackRegister); // This is a normal "timeout" for a debounce function.

        var timeout;
        return function (e) {
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            callback(e);
            timeout = undefined; // Because we just called the callback, let's return the
            // callback register to it's normal "null" state.

            callbackRegister.callback = function () {};
          }, time); // Register the current callback in the register as a kind-of "escape-hatch".

          callbackRegister.callback = function () {
            clearTimeout(timeout);
            callback(e);
          };
        };
      }
    }, {
      key: "callAfterModelDebounce",
      value: function callAfterModelDebounce(callback) {
        // This is to protect against the following scenario:
        // A user is typing into a debounced input, and hits the enter key.
        // If the enter key submits a form or something, the submission
        // will happen BEFORE the model input finishes syncing because
        // of the debounce. This makes sure to clear anything in the debounce queue.
        if (this.modelDebounceCallbacks) {
          this.modelDebounceCallbacks.forEach(function (callbackRegister) {
            callbackRegister.callback();

            callbackRegister = function callbackRegister() {};
          });
        }

        callback();
      }
    }, {
      key: "addListenerForTeardown",
      value: function addListenerForTeardown(teardownCallback) {
        this.tearDownCallbacks.push(teardownCallback);
      }
    }, {
      key: "tearDown",
      value: function tearDown() {
        this.tearDownCallbacks.forEach(function (callback) {
          return callback();
        });
      }
    }, {
      key: "upload",
      value: function upload(name, file) {
        var finishCallback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : function () {};
        var errorCallback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : function () {};
        var progressCallback = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : function () {};
        this.uploadManager.upload(name, file, finishCallback, errorCallback, progressCallback);
      }
    }, {
      key: "uploadMultiple",
      value: function uploadMultiple(name, files) {
        var finishCallback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : function () {};
        var errorCallback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : function () {};
        var progressCallback = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : function () {};
        this.uploadManager.uploadMultiple(name, files, finishCallback, errorCallback, progressCallback);
      }
    }, {
      key: "removeUpload",
      value: function removeUpload(name, tmpFilename) {
        var finishCallback = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : function () {};
        var errorCallback = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : function () {};
        this.uploadManager.removeUpload(name, tmpFilename, finishCallback, errorCallback);
      }
    }, {
      key: "name",
      get: function get() {
        return this.fingerprint.name;
      }
    }, {
      key: "data",
      get: function get() {
        return this.serverMemo.data;
      }
    }, {
      key: "childIds",
      get: function get() {
        return Object.values(this.serverMemo.children).map(function (child) {
          return child.id;
        });
      }
    }, {
      key: "$wire",
      get: function get() {
        if (this.dollarWireProxy) return this.dollarWireProxy;
        var refObj = {};
        var component = this;
        return this.dollarWireProxy = new Proxy(refObj, {
          get: function get(object, property) {
            if (property === 'entangle') {
              return function (name) {
                var defer = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
                return {
                  isDeferred: defer,
                  livewireEntangle: name,

                  get defer() {
                    this.isDeferred = true;
                    return this;
                  }

                };
              };
            }

            if (property === '__instance') return component; // Forward "emits" to base Livewire object.

            if (property.match(/^emit.*/)) return function () {
              for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
                args[_key2] = arguments[_key2];
              }

              if (property === 'emitSelf') return store.emitSelf.apply(store, [component.id].concat(args));
              return store[property].apply(component, args);
            };

            if (['get', 'set', 'sync', 'call', 'on', 'upload', 'uploadMultiple', 'removeUpload'].includes(property)) {
              // Forward public API methods right away.
              return function () {
                for (var _len3 = arguments.length, args = new Array(_len3), _key3 = 0; _key3 < _len3; _key3++) {
                  args[_key3] = arguments[_key3];
                }

                return component[property].apply(component, args);
              };
            } // If the property exists on the data, return it.


            var getResult = component.get(property); // If the property does not exist, try calling the method on the class.

            if (getResult === undefined) {
              return function () {
                for (var _len4 = arguments.length, args = new Array(_len4), _key4 = 0; _key4 < _len4; _key4++) {
                  args[_key4] = arguments[_key4];
                }

                return component.call.apply(component, [property].concat(args));
              };
            }

            return getResult;
          },
          set: function set(obj, prop, value) {
            component.set(prop, value);
            return true;
          }
        });
      }
    }]);

    return Component;
  }();

  function FileUploads () {
    store.registerHook('interceptWireModelAttachListener', function (directive, el, component) {
      if (!(el.tagName.toLowerCase() === 'input' && el.type === 'file')) return;

      var start = function start() {
        return el.dispatchEvent(new CustomEvent('livewire-upload-start', {
          bubbles: true
        }));
      };

      var finish = function finish() {
        return el.dispatchEvent(new CustomEvent('livewire-upload-finish', {
          bubbles: true
        }));
      };

      var error = function error() {
        return el.dispatchEvent(new CustomEvent('livewire-upload-error', {
          bubbles: true
        }));
      };

      var progress = function progress(progressEvent) {
        var percentCompleted = Math.round(progressEvent.loaded * 100 / progressEvent.total);
        el.dispatchEvent(new CustomEvent('livewire-upload-progress', {
          bubbles: true,
          detail: {
            progress: percentCompleted
          }
        }));
      };

      var eventHandler = function eventHandler(e) {
        if (e.target.files.length === 0) return;
        start();

        if (e.target.multiple) {
          component.uploadMultiple(directive.value, e.target.files, finish, error, progress);
        } else {
          component.upload(directive.value, e.target.files[0], finish, error, progress);
        }
      };

      el.addEventListener('change', eventHandler);
      component.addListenerForTeardown(function () {
        el.removeEventListener('change', eventHandler);
      });
    });
  }

  function LaravelEcho () {
    store.registerHook('component.initialized', function (component) {
      if (Array.isArray(component.listeners)) {
        component.listeners.forEach(function (event) {
          if (event.startsWith('echo')) {
            if (typeof Echo === 'undefined') {
              console.warn('Laravel Echo cannot be found');
              return;
            }

            var event_parts = event.split(/(echo:|echo-)|:|,/);

            if (event_parts[1] == 'echo:') {
              event_parts.splice(2, 0, 'channel', undefined);
            }

            if (event_parts[2] == 'notification') {
              event_parts.push(undefined, undefined);
            }

            var _event_parts = _slicedToArray(event_parts, 7),
                s1 = _event_parts[0],
                signature = _event_parts[1],
                channel_type = _event_parts[2],
                s2 = _event_parts[3],
                channel = _event_parts[4],
                s3 = _event_parts[5],
                event_name = _event_parts[6];

            if (['channel', 'private'].includes(channel_type)) {
              Echo[channel_type](channel).listen(event_name, function (e) {
                store.emit(event, e);
              });
            } else if (channel_type == 'presence') {
              Echo.join(channel)[event_name](function (e) {
                store.emit(event, e);
              });
            } else if (channel_type == 'notification') {
              Echo.private(channel).notification(function (notification) {
                store.emit(event, notification);
              });
            } else {
              console.warn('Echo channel type not yet supported');
            }
          }
        });
      }
    });
  }

  function DirtyStates () {
    store.registerHook('component.initialized', function (component) {
      component.dirtyEls = [];
    });
    store.registerHook('element.initialized', function (el, component) {
      if (wireDirectives(el).missing('dirty')) return;
      component.dirtyEls.push(el);
    });
    store.registerHook('interceptWireModelAttachListener', function (directive, el, component) {
      var property = directive.value;
      el.addEventListener('input', function () {
        component.dirtyEls.forEach(function (dirtyEl) {
          var directives = wireDirectives(dirtyEl);

          if (directives.has('model') && directives.get('model').value === property || directives.has('target') && directives.get('target').value.split(',').map(function (s) {
            return s.trim();
          }).includes(property)) {
            var isDirty = DOM.valueFromInput(el, component) != component.get(property);
            setDirtyState(dirtyEl, isDirty);
          }
        });
      });
    });
    store.registerHook('message.received', function (message, component) {
      component.dirtyEls.forEach(function (element) {
        if (element.__livewire_dirty_cleanup) {
          element.__livewire_dirty_cleanup();

          delete element.__livewire_dirty_cleanup;
        }
      });
    });
    store.registerHook('element.removed', function (el, component) {
      component.dirtyEls.forEach(function (element, index) {
        if (element.isSameNode(el)) {
          component.dirtyEls.splice(index, 1);
        }
      });
    });
  }

  function setDirtyState(el, isDirty) {
    var directive = wireDirectives(el).get('dirty');

    if (directive.modifiers.includes('class')) {
      var classes = directive.value.split(' ');

      if (directive.modifiers.includes('remove') !== isDirty) {
        var _el$classList;

        (_el$classList = el.classList).add.apply(_el$classList, _toConsumableArray(classes));

        el.__livewire_dirty_cleanup = function () {
          var _el$classList2;

          return (_el$classList2 = el.classList).remove.apply(_el$classList2, _toConsumableArray(classes));
        };
      } else {
        var _el$classList3;

        (_el$classList3 = el.classList).remove.apply(_el$classList3, _toConsumableArray(classes));

        el.__livewire_dirty_cleanup = function () {
          var _el$classList4;

          return (_el$classList4 = el.classList).add.apply(_el$classList4, _toConsumableArray(classes));
        };
      }
    } else if (directive.modifiers.includes('attr')) {
      if (directive.modifiers.includes('remove') !== isDirty) {
        el.setAttribute(directive.value, true);

        el.__livewire_dirty_cleanup = function () {
          return el.removeAttribute(directive.value);
        };
      } else {
        el.removeAttribute(directive.value);

        el.__livewire_dirty_cleanup = function () {
          return el.setAttribute(directive.value, true);
        };
      }
    } else if (!wireDirectives(el).get('model')) {
      el.style.display = isDirty ? 'inline-block' : 'none';

      el.__livewire_dirty_cleanup = function () {
        return el.style.display = isDirty ? 'none' : 'inline-block';
      };
    }
  }

  var cleanupStackByComponentId = {};
  function DisableForms () {
    store.registerHook('element.initialized', function (el, component) {
      var directives = wireDirectives(el);
      if (directives.missing('submit')) return; // Set a forms "disabled" state on inputs and buttons.
      // Livewire will clean it all up automatically when the form
      // submission returns and the new DOM lacks these additions.

      el.addEventListener('submit', function () {
        cleanupStackByComponentId[component.id] = [];
        component.walk(function (node) {
          if (!el.contains(node)) return;
          if (node.hasAttribute('wire:ignore')) return false;

          if ( // <button type="submit">
          node.tagName.toLowerCase() === 'button' && node.type === 'submit' || // <select>
          node.tagName.toLowerCase() === 'select' || // <input type="checkbox|radio">
          node.tagName.toLowerCase() === 'input' && (node.type === 'checkbox' || node.type === 'radio')) {
            if (!node.disabled) cleanupStackByComponentId[component.id].push(function () {
              return node.disabled = false;
            });
            node.disabled = true;
          } else if ( // <input type="text">
          node.tagName.toLowerCase() === 'input' || // <textarea>
          node.tagName.toLowerCase() === 'textarea') {
            if (!node.readOnly) cleanupStackByComponentId[component.id].push(function () {
              return node.readOnly = false;
            });
            node.readOnly = true;
          }
        });
      });
    });
    store.registerHook('message.failed', function (message, component) {
      return cleanup(component);
    });
    store.registerHook('message.received', function (message, component) {
      return cleanup(component);
    });
  }

  function cleanup(component) {
    if (!cleanupStackByComponentId[component.id]) return;

    while (cleanupStackByComponentId[component.id].length > 0) {
      cleanupStackByComponentId[component.id].shift()();
    }
  }

  function FileDownloads () {
    store.registerHook('message.received', function (message, component) {
      var response = message.response;
      if (!response.effects.download) return;
      var url = window.URL.createObjectURL(base64toBlob(response.effects.download.content));
      var invisibleLink = document.createElement('a');
      invisibleLink.style.display = 'none';
      invisibleLink.href = url;
      invisibleLink.download = response.effects.download.name;
      document.body.appendChild(invisibleLink);
      invisibleLink.click();
      window.URL.revokeObjectURL(url);
    });
  }

  function base64toBlob(b64Data) {
    var contentType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
    var sliceSize = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 512;
    var byteCharacters = atob(b64Data);
    var byteArrays = [];

    for (var offset = 0; offset < byteCharacters.length; offset += sliceSize) {
      var slice = byteCharacters.slice(offset, offset + sliceSize);
      var byteNumbers = new Array(slice.length);

      for (var i = 0; i < slice.length; i++) {
        byteNumbers[i] = slice.charCodeAt(i);
      }

      var byteArray = new Uint8Array(byteNumbers);
      byteArrays.push(byteArray);
    }

    return new Blob(byteArrays, {
      type: contentType
    });
  }

  var offlineEls = [];
  function OfflineStates () {
    store.registerHook('element.initialized', function (el) {
      if (wireDirectives(el).missing('offline')) return;
      offlineEls.push(el);
    });
    window.addEventListener('offline', function () {
      store.livewireIsOffline = true;
      offlineEls.forEach(function (el) {
        toggleOffline(el, true);
      });
    });
    window.addEventListener('online', function () {
      store.livewireIsOffline = false;
      offlineEls.forEach(function (el) {
        toggleOffline(el, false);
      });
    });
    store.registerHook('element.removed', function (el) {
      offlineEls = offlineEls.filter(function (el) {
        return !el.isSameNode(el);
      });
    });
  }

  function toggleOffline(el, isOffline) {
    var directives = wireDirectives(el);
    var directive = directives.get('offline');

    if (directive.modifiers.includes('class')) {
      var classes = directive.value.split(' ');

      if (directive.modifiers.includes('remove') !== isOffline) {
        var _el$classList;

        (_el$classList = el.classList).add.apply(_el$classList, _toConsumableArray(classes));
      } else {
        var _el$classList2;

        (_el$classList2 = el.classList).remove.apply(_el$classList2, _toConsumableArray(classes));
      }
    } else if (directive.modifiers.includes('attr')) {
      if (directive.modifiers.includes('remove') !== isOffline) {
        el.setAttribute(directive.value, true);
      } else {
        el.removeAttribute(directive.value);
      }
    } else if (!directives.get('model')) {
      el.style.display = isOffline ? 'inline-block' : 'none';
    }
  }

  function SyncBrowserHistory () {
    var initializedPath = false; // This is to prevent exponentially increasing the size of our state on page refresh.

    if (window.history.state) window.history.state.livewire = new LivewireState().toStateArray();
    store.registerHook('component.initialized', function (component) {
      if (!component.effects.path) return; // We are using setTimeout() to make sure all the components on the page have
      // loaded before we store anything in the history state (because the position
      // of a component on a page matters for generating its state signature).

      setTimeout(function () {
        var state = generateNewState(component, generateInitialFauxResponse(component));
        var url = initializedPath ? undefined : component.effects.path;
        store.callHook('beforeReplaceState', state, url, component);
        history.replaceState(state, '', onlyChangeThePathAndQueryString(url));
        initializedPath = true;
      });
    });
    store.registerHook('message.processed', function (message, component) {
      // Preventing a circular dependancy.
      if (message.replaying) return;
      var response = message.response;
      var effects = response.effects || {};

      if ('path' in effects && effects.path !== window.location.href) {
        var state = generateNewState(component, response);
        store.callHook('beforePushState', state, effects.path, component);
        history.pushState(state, '', onlyChangeThePathAndQueryString(effects.path));
      }
    });
    window.addEventListener('popstate', function (event) {
      if (!(event.state && event.state.livewire)) return;
      new LivewireState(event.state.livewire).replayResponses(function (response, component) {
        var message = new _default$3(component, []);
        message.storeResponse(response);
        message.replaying = true;
        component.handleResponse(message);
      });
    });

    function generateNewState(component, response) {
      var state = history.state && history.state.livewire ? new LivewireState(_toConsumableArray(history.state.livewire)) : new LivewireState();
      state.storeResponse(response, component);
      return {
        livewire: state.toStateArray()
      };
    }

    function generateInitialFauxResponse(component) {
      var serverMemo = component.serverMemo,
          effects = component.effects,
          el = component.el;
      return {
        serverMemo: serverMemo,
        effects: _objectSpread2(_objectSpread2({}, effects), {}, {
          html: el.outerHTML
        })
      };
    }

    function onlyChangeThePathAndQueryString(url) {
      if (!url) return;
      var destination = new URL(url);
      var afterOrigin = destination.href.replace(destination.origin, '');
      return window.location.origin + afterOrigin + window.location.hash;
    }

    store.registerHook('element.updating', function (from, to, component) {
      // It looks like the element we are about to update is the root
      // element of the component. Let's store this knowledge to
      // reference after update in the "element.updated" hook.
      if (from.getAttribute('wire:id') === component.id) {
        component.lastKnownDomId = component.id;
      }
    });
    store.registerHook('element.updated', function (node, component) {
      // If the element that was just updated was the root DOM element.
      if (component.lastKnownDomId) {
        // Let's check and see if the wire:id was the thing that changed.
        if (node.getAttribute('wire:id') !== component.lastKnownDomId) {
          // If so, we need to change this ID globally everwhere it's referenced.
          store.changeComponentId(component, node.getAttribute('wire:id'));
        } // Either way, we'll unset this for the next update.


        delete component.lastKnownDomId;
      } // We have to update the component ID because we are replaying responses
      // from similar components but with completely different IDs. If didn't
      // update the component ID, the checksums would fail.

    });
  }

  var LivewireState = /*#__PURE__*/function () {
    function LivewireState() {
      var stateArray = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];

      _classCallCheck(this, LivewireState);

      this.items = stateArray;
    }

    _createClass(LivewireState, [{
      key: "toStateArray",
      value: function toStateArray() {
        return this.items;
      }
    }, {
      key: "pushItemInProperOrder",
      value: function pushItemInProperOrder(signature, storageKey, component) {
        var _this = this;

        var targetItem = {
          signature: signature,
          storageKey: storageKey
        }; // First, we'll check if this signature already has an entry, if so, replace it.

        var existingIndex = this.items.findIndex(function (item) {
          return item.signature === signature;
        });
        if (existingIndex !== -1) return this.items[existingIndex] = targetItem; // If it doesn't already exist, we'll add it, but we MUST first see if any of its
        // parents components have entries, and insert it immediately before them.
        // This way, when we replay responses, we will always start with the most
        // inward components and go outwards.

        var closestParentId = store.getClosestParentId(component.id, this.componentIdsWithStoredResponses());
        if (!closestParentId) return this.items.unshift(targetItem);
        var closestParentIndex = this.items.findIndex(function (item) {
          var _this$parseSignature = _this.parseSignature(item.signature),
              originalComponentId = _this$parseSignature.originalComponentId;

          if (originalComponentId === closestParentId) return true;
        });
        this.items.splice(closestParentIndex, 0, targetItem);
      }
    }, {
      key: "storeResponse",
      value: function storeResponse(response, component) {
        // Add ALL properties as "dirty" so that when the back button is pressed,
        // they ALL are forced to refresh on the page (even if the HTML didn't change).
        response.effects.dirty = Object.keys(response.serverMemo.data);
        var storageKey = this.storeInSession(response);
        var signature = this.getComponentNameBasedSignature(component);
        this.pushItemInProperOrder(signature, storageKey, component);
      }
    }, {
      key: "replayResponses",
      value: function replayResponses(callback) {
        var _this2 = this;

        this.items.forEach(function (_ref) {
          var signature = _ref.signature,
              storageKey = _ref.storageKey;

          var component = _this2.findComponentBySignature(signature);

          if (!component) return;

          var response = _this2.getFromSession(storageKey);

          if (!response) return console.warn("Livewire: sessionStorage key not found: ".concat(storageKey));
          callback(response, component);
        });
      }
    }, {
      key: "storeInSession",
      value: function storeInSession(value) {
        var key = 'livewire:' + new Date().getTime();
        var stringifiedValue = JSON.stringify(Object.entries(value));
        this.tryToStoreInSession(key, stringifiedValue);
        return key;
      }
    }, {
      key: "tryToStoreInSession",
      value: function tryToStoreInSession(key, value) {
        try {
          sessionStorage.setItem(key, value);
        } catch (error) {
          // 22 is Chrome, 1-14 is other browsers.
          if (![22, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14].includes(error.code)) return;
          var oldestTimestamp = Object.keys(sessionStorage).map(function (key) {
            return Number(key.replace('livewire:', ''));
          }).sort().shift();
          if (!oldestTimestamp) return;
          sessionStorage.removeItem('livewire:' + oldestTimestamp);
          this.tryToStoreInSession(key, value);
        }
      }
    }, {
      key: "getFromSession",
      value: function getFromSession(key) {
        var item = sessionStorage.getItem(key);
        if (!item) return;
        return Object.fromEntries(JSON.parse(item));
      } // We can't just store component reponses by their id because
      // ids change on every refresh, so history state won't have
      // a component to apply it's changes to. Instead we must
      // generate a unique id based on the components name
      // and it's relative position amongst others with
      // the same name that are loaded on the page.

    }, {
      key: "getComponentNameBasedSignature",
      value: function getComponentNameBasedSignature(component) {
        var componentName = component.fingerprint.name;
        var sameNamedComponents = store.getComponentsByName(componentName);
        var componentIndex = sameNamedComponents.indexOf(component);
        return "".concat(component.id, ":").concat(componentName, ":").concat(componentIndex);
      }
    }, {
      key: "findComponentBySignature",
      value: function findComponentBySignature(signature) {
        var _this$parseSignature2 = this.parseSignature(signature),
            componentName = _this$parseSignature2.componentName,
            componentIndex = _this$parseSignature2.componentIndex;

        var sameNamedComponents = store.getComponentsByName(componentName); // If we found the component in the proper place, return it,
        // otherwise return the first one.

        return sameNamedComponents[componentIndex] || sameNamedComponents[0] || console.warn("Livewire: couldn't find component on page: ".concat(componentName));
      }
    }, {
      key: "parseSignature",
      value: function parseSignature(signature) {
        var _signature$split = signature.split(':'),
            _signature$split2 = _slicedToArray(_signature$split, 3),
            originalComponentId = _signature$split2[0],
            componentName = _signature$split2[1],
            componentIndex = _signature$split2[2];

        return {
          originalComponentId: originalComponentId,
          componentName: componentName,
          componentIndex: componentIndex
        };
      }
    }, {
      key: "componentIdsWithStoredResponses",
      value: function componentIdsWithStoredResponses() {
        var _this3 = this;

        return this.items.map(function (_ref2) {
          var signature = _ref2.signature;

          var _this3$parseSignature = _this3.parseSignature(signature),
              originalComponentId = _this3$parseSignature.originalComponentId;

          return originalComponentId;
        });
      }
    }]);

    return LivewireState;
  }();

  var Livewire = /*#__PURE__*/function () {
    function Livewire() {
      _classCallCheck(this, Livewire);

      this.connection = new Connection();
      this.components = store;

      this.onLoadCallback = function () {};
    }

    _createClass(Livewire, [{
      key: "first",
      value: function first() {
        return Object.values(this.components.componentsById)[0].$wire;
      }
    }, {
      key: "find",
      value: function find(componentId) {
        return this.components.componentsById[componentId].$wire;
      }
    }, {
      key: "all",
      value: function all() {
        return Object.values(this.components.componentsById).map(function (component) {
          return component.$wire;
        });
      }
    }, {
      key: "directive",
      value: function directive(name, callback) {
        this.components.registerDirective(name, callback);
      }
    }, {
      key: "hook",
      value: function hook(name, callback) {
        this.components.registerHook(name, callback);
      }
    }, {
      key: "onLoad",
      value: function onLoad(callback) {
        this.onLoadCallback = callback;
      }
    }, {
      key: "onError",
      value: function onError(callback) {
        this.components.onErrorCallback = callback;
      }
    }, {
      key: "emit",
      value: function emit(event) {
        var _this$components;

        for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
          params[_key - 1] = arguments[_key];
        }

        (_this$components = this.components).emit.apply(_this$components, [event].concat(params));
      }
    }, {
      key: "emitTo",
      value: function emitTo(name, event) {
        var _this$components2;

        for (var _len2 = arguments.length, params = new Array(_len2 > 2 ? _len2 - 2 : 0), _key2 = 2; _key2 < _len2; _key2++) {
          params[_key2 - 2] = arguments[_key2];
        }

        (_this$components2 = this.components).emitTo.apply(_this$components2, [name, event].concat(params));
      }
    }, {
      key: "on",
      value: function on(event, callback) {
        this.components.on(event, callback);
      }
    }, {
      key: "restart",
      value: function restart() {
        this.stop();
        this.start();
      }
    }, {
      key: "stop",
      value: function stop() {
        this.components.tearDownComponents();
      }
    }, {
      key: "start",
      value: function start() {
        var _this = this;

        DOM.rootComponentElementsWithNoParents().forEach(function (el) {
          _this.components.addComponent(new Component(el, _this.connection));
        });
        this.setupAlpineCompatibility();
        this.onLoadCallback();
        dispatch('livewire:load');
        document.addEventListener('visibilitychange', function () {
          _this.components.livewireIsInBackground = document.hidden;
        }, false);
        this.components.initialRenderIsFinished = true;
      }
    }, {
      key: "rescan",
      value: function rescan() {
        var _this2 = this;

        var node = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        DOM.rootComponentElementsWithNoParents(node).forEach(function (el) {
          var componentId = wireDirectives(el).get('id').value;
          if (_this2.components.hasComponent(componentId)) return;

          _this2.components.addComponent(new Component(el, _this2.connection));
        });
      }
    }, {
      key: "setupAlpineCompatibility",
      value: function setupAlpineCompatibility() {
        var _this3 = this;

        if (!window.Alpine) return;

        if (window.Alpine.onBeforeComponentInitialized) {
          window.Alpine.onBeforeComponentInitialized(function (component) {
            var livewireEl = component.$el.closest('[wire\\:id]');

            if (livewireEl && livewireEl.__livewire) {
              Object.entries(component.unobservedData).forEach(function (_ref) {
                var _ref2 = _slicedToArray(_ref, 2),
                    key = _ref2[0],
                    value = _ref2[1];

                if (!!value && _typeof(value) === 'object' && value.livewireEntangle) {
                  // Ok, it looks like someone set an Alpine property to $wire.entangle or @entangle.
                  var livewireProperty = value.livewireEntangle;
                  var isDeferred = value.isDeferred;
                  var livewireComponent = livewireEl.__livewire; // Let's set the initial value of the Alpine prop to the Livewire prop's value.

                  component.unobservedData[key] = livewireEl.__livewire.get(livewireProperty);
                  var blockAlpineWatcher = false; // Now, we'll watch for changes to the Alpine prop, and fire the update to Livewire.

                  component.unobservedData.$watch(key, function (value) {
                    // Let's also make sure that this watcher isn't a result of a Livewire response.
                    // If it is, we don't need to "re-update" Livewire. (sending an extra useless) request.
                    if (blockAlpineWatcher === true) {
                      blockAlpineWatcher = false;
                      return;
                    } // If the Alpine value is the same as the Livewire value, we'll skip the update for 2 reasons:
                    // - It's just more efficient, why send needless requests.
                    // - This prevents a circular dependancy with the other watcher below.


                    if (value === livewireEl.__livewire.get(livewireProperty)) return; // We'll tell Livewire to update the property, but we'll also tell Livewire
                    // to not call the normal property watchers on the way back to prevent another
                    // circular dependancy.

                    livewireComponent.set(livewireProperty, value, isDeferred, true // Skip firing Livewire watchers when the request comes back.
                    );
                  }); // We'll also listen for changes to the Livewire prop, and set them in Alpine.

                  livewireComponent.watch(livewireProperty, function (value) {
                    blockAlpineWatcher = true;
                    component.$data[key] = value;
                  });
                }
              });
            }
          });
        }

        if (window.Alpine.onComponentInitialized) {
          window.Alpine.onComponentInitialized(function (component) {
            var livewireEl = component.$el.closest('[wire\\:id]');

            if (livewireEl && livewireEl.__livewire) {
              _this3.hook('message.processed', function (livewireComponent) {
                if (livewireComponent === livewireEl.__livewire) {
                  component.updateElements(component.$el);
                }
              });
            }
          });
        }

        if (window.Alpine.addMagicProperty) {
          window.Alpine.addMagicProperty('wire', function (componentEl) {
            var wireEl = componentEl.closest('[wire\\:id]');
            if (!wireEl) console.warn('Alpine: Cannot reference "$wire" outside a Livewire component.');
            var component = wireEl.__livewire;
            return component.$wire;
          });
        }
      }
    }]);

    return Livewire;
  }();

  if (!window.Livewire) {
    window.Livewire = Livewire;
  }

  SyncBrowserHistory();
  FileDownloads();
  OfflineStates();
  LoadingStates();
  DisableForms();
  FileUploads();
  LaravelEcho();
  DirtyStates();
  Polling();
  dispatch('livewire:available');

  return Livewire;

})));
