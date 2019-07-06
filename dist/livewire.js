(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(window, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/action/event.js":
/*!********************************!*\
  !*** ./src/js/action/event.js ***!
  \********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./src/js/action/index.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }



var _default =
/*#__PURE__*/
function (_Action) {
  _inherits(_default, _Action);

  function _default(event, params, el) {
    var _this;

    _classCallCheck(this, _default);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(_default).call(this, el));
    _this.type = 'fireEvent';
    _this.payload = {
      event: event,
      params: params
    };
    return _this;
  }

  return _default;
}(___WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./src/js/action/index.js":
/*!********************************!*\
  !*** ./src/js/action/index.js ***!
  \********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var _default =
/*#__PURE__*/
function () {
  function _default(el) {
    _classCallCheck(this, _default);

    this.el = el;
  }

  _createClass(_default, [{
    key: "ref",
    get: function get() {
      return this.el ? this.el.ref : null;
    }
  }]);

  return _default;
}();



/***/ }),

/***/ "./src/js/action/method.js":
/*!*********************************!*\
  !*** ./src/js/action/method.js ***!
  \*********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./src/js/action/index.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }



var _default =
/*#__PURE__*/
function (_Action) {
  _inherits(_default, _Action);

  function _default(method, params, el) {
    var _this;

    _classCallCheck(this, _default);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(_default).call(this, el));
    _this.type = 'callMethod';
    _this.payload = {
      method: method,
      params: params
    };
    return _this;
  }

  return _default;
}(___WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./src/js/action/model.js":
/*!********************************!*\
  !*** ./src/js/action/model.js ***!
  \********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
/* harmony import */ var ___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! . */ "./src/js/action/index.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }



var _default =
/*#__PURE__*/
function (_Action) {
  _inherits(_default, _Action);

  function _default(name, value, el) {
    var _this;

    _classCallCheck(this, _default);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(_default).call(this, el));
    _this.type = 'syncInput';
    _this.payload = {
      name: name,
      value: value
    };
    return _this;
  }

  return _default;
}(___WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./src/js/component/handle_loading_directives.js":
/*!*******************************************************!*\
  !*** ./src/js/component/handle_loading_directives.js ***!
  \*******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _dom_dom_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../dom/dom_element */ "./src/js/dom/dom_element.js");

/* harmony default export */ __webpack_exports__["default"] = ({
  loadingEls: [],
  loadingElsByRef: {},
  addLoadingEl: function addLoadingEl(el, value, targetRef, remove) {
    if (targetRef) {
      if (this.loadingElsByRef[targetRef]) {
        this.loadingElsByRef[targetRef].push({
          el: el,
          value: value,
          remove: remove
        });
      } else {
        this.loadingElsByRef[targetRef] = [{
          el: el,
          value: value,
          remove: remove
        }];
      }
    } else {
      this.loadingEls.push({
        el: el,
        value: value,
        remove: remove
      });
    }
  },
  removeLoadingEl: function removeLoadingEl(node) {
    var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_0__["default"](node);
    this.loadingEls = this.loadingEls.filter(function (_ref) {
      var el = _ref.el;
      return !el.isSameNode(node);
    });

    if (el.ref in this.loadingElsByRef) {
      delete this.loadingElsByRef[el.ref];
    }
  },
  setLoading: function setLoading(refs) {
    var _this = this;

    var refEls = refs.map(function (ref) {
      return _this.loadingElsByRef[ref];
    }).filter(function (el) {
      return el;
    }).flat();
    var allEls = this.loadingEls.concat(refEls);
    allEls.forEach(function (el) {
      var directive = el.el.directives.get('loading');
      el = el.el.el; // I'm so sorry @todo

      if (directive.modifiers.includes('class')) {
        if (directive.modifiers.includes('remove')) {
          el.classList.remove(directive.value);
        } else {
          el.classList.add(directive.value);
        }
      } else if (directive.modifiers.includes('attr')) {
        if (directive.modifiers.includes('remove')) {
          el.removeAttribute(directive.value);
        } else {
          el.setAttribute(directive.value, true);
        }
      } else {
        el.style.display = 'inline-block';
      }
    });
    return allEls;
  },
  unsetLoading: function unsetLoading(loadingEls) {// No need to "unset" loading because the dom-diffing will automatically reverse any changes.
  }
});

/***/ }),

/***/ "./src/js/component/index.js":
/*!***********************************!*\
  !*** ./src/js/component/index.js ***!
  \***********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _message__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../message */ "./src/js/message.js");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../util */ "./src/js/util/index.js");
/* harmony import */ var _dom_morphdom__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../dom/morphdom */ "./src/js/dom/morphdom/index.js");
/* harmony import */ var _dom_dom__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../dom/dom */ "./src/js/dom/dom.js");
/* harmony import */ var _dom_dom_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../dom/dom_element */ "./src/js/dom/dom_element.js");
/* harmony import */ var _handle_loading_directives__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./handle_loading_directives */ "./src/js/component/handle_loading_directives.js");
/* harmony import */ var _node_initializer__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../node_initializer */ "./src/js/node_initializer.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../store */ "./src/js/store.js");
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }










var Component =
/*#__PURE__*/
function () {
  function Component(el, connection) {
    _classCallCheck(this, Component);

    this.id = el.getAttribute('id');
    this.data = JSON.parse(el.getAttribute('data'));
    this.events = JSON.parse(el.getAttribute('events'));
    this.children = JSON.parse(el.getAttribute('children'));
    this.middleware = el.getAttribute('middleware');
    this.checksum = el.getAttribute('checksum');
    this.name = el.getAttribute('name');
    this.connection = connection;
    this.actionQueue = [];
    this.messageInTransit = null;
    this.initialize();
    this.registerEchoListeners();
  }

  _createClass(Component, [{
    key: "initialize",
    value: function initialize() {
      var _this = this;

      this.walk(function (el) {
        // Will run for every node in the component tree (not child component nodes).
        _node_initializer__WEBPACK_IMPORTED_MODULE_6__["default"].initialize(el, _this);
      }, function (el) {
        // When new component is encountered in the tree, add it.
        _store__WEBPACK_IMPORTED_MODULE_7__["default"].addComponent(new Component(el, _this.connection));
      });
    }
  }, {
    key: "addAction",
    value: function addAction(action) {
      this.actionQueue.push(action); // This debounce is here in-case two events fire at the "same" time:
      // For example: if you are listening for a click on element A,
      // and a "blur" on element B. If element B has focus, and then,
      // you click on element A, the blur event will fire before the "click"
      // event. This debounce captures them both in the actionsQueue and sends
      // them off at the same time.
      // Note: currently, it's set to 5ms, that might not be the right amount, we'll see.

      Object(_util__WEBPACK_IMPORTED_MODULE_1__["debounce"])(this.fireMessage, 5).apply(this);
    }
  }, {
    key: "fireMessage",
    value: function fireMessage() {
      if (this.messageInTransit) return;
      this.messageInTransit = new _message__WEBPACK_IMPORTED_MODULE_0__["default"](this, this.actionQueue);
      this.connection.sendMessage(this.messageInTransit);
      this.actionQueue = [];
    }
  }, {
    key: "messageSendFailed",
    value: function messageSendFailed() {
      this.messageInTransit = null;
    }
  }, {
    key: "receiveMessage",
    value: function receiveMessage(payload) {
      var response = this.messageInTransit.storeResponse(payload);
      this.data = response.data;
      this.children = response.children; // This means "$this->redirect()" was called in the component. let's just bail and redirect.

      if (response.redirectTo) {
        window.location.href = response.redirectTo;
        return;
      }

      this.replaceDom(response.dom, response.dirtyInputs);
      this.forceRefreshDataBoundElementsMarkedAsDirty(response.dirtyInputs);
      this.unsetLoading(this.messageInTransit.loadingEls);
      this.messageInTransit = null;

      if (response.eventQueue && response.eventQueue.length > 0) {
        response.eventQueue.forEach(function (event) {
          _store__WEBPACK_IMPORTED_MODULE_7__["default"].emit.apply(_store__WEBPACK_IMPORTED_MODULE_7__["default"], [event.event].concat(_toConsumableArray(event.params)));
        });
      }
    }
  }, {
    key: "forceRefreshDataBoundElementsMarkedAsDirty",
    value: function forceRefreshDataBoundElementsMarkedAsDirty(dirtyInputs) {
      var _this2 = this;

      this.walk(function (el) {
        if (el.directives.missing('model')) return;
        var modelValue = el.directives.get('model').value;
        if (el.isFocused() && !dirtyInputs.includes(modelValue)) return;
        el.setInputValueFromModel(_this2);
      });
    }
  }, {
    key: "replaceDom",
    value: function replaceDom(rawDom) {
      this.handleMorph(this.formatDomBeforeDiffToAvoidConflictsWithVue(rawDom.trim()));
    }
  }, {
    key: "formatDomBeforeDiffToAvoidConflictsWithVue",
    value: function formatDomBeforeDiffToAvoidConflictsWithVue(inputDom) {
      if (!window.Vue) return inputDom;
      var div = document.createElement('div');
      div.innerHTML = inputDom;
      new window.Vue().$mount(div.firstElementChild);
      return div.firstElementChild.outerHTML;
    }
  }, {
    key: "handleMorph",
    value: function handleMorph(dom) {
      var _this3 = this;

      Object(_dom_morphdom__WEBPACK_IMPORTED_MODULE_2__["default"])(this.el.rawNode(), dom, {
        childrenOnly: true,
        getNodeKey: function getNodeKey(node) {
          // This allows the tracking of elements by the "key" attribute, like in VueJs.
          return node.hasAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].prefix, ":key")) ? node.getAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].prefix, ":key")) // If no "key", then first check for "wire:id", then "wire:model", then "id"
          : node.hasAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].prefix, ":id")) ? node.getAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].prefix, ":id")) : node.hasAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].prefix, ":model")) ? node.getAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].prefix, ":model")) : node.id;
        },
        onBeforeNodeAdded: function onBeforeNodeAdded(node) {
          return new _dom_dom_element__WEBPACK_IMPORTED_MODULE_4__["default"](node).transitionElementIn();
        },
        onBeforeNodeDiscarded: function onBeforeNodeDiscarded(node) {
          return new _dom_dom_element__WEBPACK_IMPORTED_MODULE_4__["default"](node).transitionElementOut(function (nodeDiscarded) {
            // Cleanup after removed element.
            _this3.removeLoadingEl(nodeDiscarded);
          });
        },
        onBeforeElChildrenUpdated: function onBeforeElChildrenUpdated(node) {//
        },
        onBeforeElUpdated: function onBeforeElUpdated(from, to) {
          var fromEl = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_4__["default"](from); // Honor the "wire:ignore" attribute.

          if (fromEl.hasAttribute('ignore')) return false; // Children will update themselves.

          if (fromEl.isComponentRootEl() && fromEl.getAttribute('id') !== _this3.id) return false; // Don't touch Vue components

          if (fromEl.isVueComponent()) return false;
        },
        onElUpdated: function onElUpdated(node) {//
        },
        onNodeDiscarded: function onNodeDiscarded(node) {
          // Elements with loading directives are stored, release this
          // element from storage because it no longer exists on the DOM.
          _this3.removeLoadingEl(node);
        },
        onNodeAdded: function onNodeAdded(node) {
          var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_4__["default"](node);
          var closestComponentId = el.closestRoot().getAttribute('id');

          if (closestComponentId === _this3.id) {
            _node_initializer__WEBPACK_IMPORTED_MODULE_6__["default"].initialize(el, _this3);
          } else if (el.isComponentRootEl()) {
            _store__WEBPACK_IMPORTED_MODULE_7__["default"].addComponent(new Component(el, _this3.connection));
          } // Skip.

        }
      });
    }
  }, {
    key: "walk",
    value: function walk(callback) {
      var _this4 = this;

      var callbackWhenNewComponentIsEncountered = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function (el) {};

      Object(_util__WEBPACK_IMPORTED_MODULE_1__["walk"])(this.el.rawNode(), function (node) {
        var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_4__["default"](node); // Skip the root component element.

        if (el.isSameNode(_this4.el)) return; // If we encounter a nested component, skip walking that tree.

        if (el.isComponentRootEl()) {
          callbackWhenNewComponentIsEncountered(el);
          return false;
        }

        callback(el);
      });
    }
  }, {
    key: "registerEchoListeners",
    value: function registerEchoListeners() {
      if (Array.isArray(this.events)) {
        this.events.forEach(function (event) {
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
                _store__WEBPACK_IMPORTED_MODULE_7__["default"].emit(event, e);
              });
            } else if (channel_type == 'presence') {
              Echo.join(channel)[event_name](function (e) {
                _store__WEBPACK_IMPORTED_MODULE_7__["default"].emit(event, e);
              });
            } else if (channel_type == 'notification') {
              Echo.private(channel).notification(function (notification) {
                _store__WEBPACK_IMPORTED_MODULE_7__["default"].emit(event, notification);
              });
            } else {
              console.warn('Echo channel type not yet supported');
            }
          }
        });
      }
    }
  }, {
    key: "el",
    get: function get() {
      return _dom_dom__WEBPACK_IMPORTED_MODULE_3__["default"].getByAttributeAndValue('id', this.id);
    }
  }]);

  return Component;
}();

Object(_util__WEBPACK_IMPORTED_MODULE_1__["addMixin"])(Component, _handle_loading_directives__WEBPACK_IMPORTED_MODULE_5__["default"]);
/* harmony default export */ __webpack_exports__["default"] = (Component);

/***/ }),

/***/ "./src/js/connection/drivers/http.js":
/*!*******************************************!*\
  !*** ./src/js/connection/drivers/http.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony default export */ __webpack_exports__["default"] = ({
  onError: null,
  onMessage: null,
  init: function init() {//
  },
  keepAlive: function keepAlive() {
    fetch('/livewire/keep-alive', {
      credentials: "same-origin",
      headers: {
        'X-CSRF-TOKEN': this.getCSRFToken(),
        'X-Livewire-Keep-Alive': true
      }
    });
  },
  sendMessage: function sendMessage(payload) {
    var _this = this;

    // Forward the query string for the ajax requests.
    fetch('/livewire/message' + window.location.search, {
      method: 'POST',
      body: JSON.stringify(payload),
      // This enables "cookies".
      credentials: "same-origin",
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/html, application/xhtml+xml',
        'X-CSRF-TOKEN': this.getCSRFToken(),
        'X-Livewire': true
      }
    }).then(function (response) {
      if (response.ok) {
        response.text().then(function (response) {
          _this.onMessage.call(_this, JSON.parse(response));
        });
      } else {
        response.text().then(function (response) {
          _this.onError(payload);

          _this.showHtmlModal(response);
        });
      }
    }).catch(function () {
      _this.onError(payload);
    });
  },
  getCSRFToken: function getCSRFToken() {
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
  },
  // This code and concept is all Jonathan Reinink - thanks main!
  showHtmlModal: function showHtmlModal(html) {
    var _this2 = this;

    var page = document.createElement('html');
    page.innerHTML = html;
    page.querySelectorAll('a').forEach(function (a) {
      return a.setAttribute('target', '_top');
    });
    var modal = document.createElement('div');
    modal.id = 'burst-error';
    modal.style.position = 'fixed';
    modal.style.width = '100vw';
    modal.style.height = '100vh';
    modal.style.padding = '50px';
    modal.style.backgroundColor = 'rgba(0, 0, 0, .6)';
    modal.style.zIndex = 200000;
    var iframe = document.createElement('iframe');
    iframe.style.backgroundColor = 'white';
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
  },
  hideHtmlModal: function hideHtmlModal(modal) {
    modal.outerHTML = '';
    document.body.style.overflow = 'visible';
  }
});

/***/ }),

/***/ "./src/js/connection/drivers/index.js":
/*!********************************************!*\
  !*** ./src/js/connection/drivers/index.js ***!
  \********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _http__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./http */ "./src/js/connection/drivers/http.js");

/* harmony default export */ __webpack_exports__["default"] = ({
  http: _http__WEBPACK_IMPORTED_MODULE_0__["default"]
});

/***/ }),

/***/ "./src/js/connection/index.js":
/*!************************************!*\
  !*** ./src/js/connection/index.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Connection; });
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../util */ "./src/js/util/index.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../store */ "./src/js/store.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }




var Connection =
/*#__PURE__*/
function () {
  function Connection(driver) {
    var _this = this;

    _classCallCheck(this, Connection);

    this.driver = driver;

    this.driver.onMessage = function (payload) {
      _this.onMessage(payload);
    };

    this.driver.onError = function (payload) {
      _this.onError(payload);
    }; // This prevents those annoying CSRF 419's by keeping the cookie fresh.
    // Yum! No one likes stale cookies...


    if (typeof this.driver.keepAlive !== 'undefined') {
      setInterval(function () {
        _this.driver.keepAlive();
      }, 600000); // Every ten minutes.
    }

    this.driver.init();
  }

  _createClass(Connection, [{
    key: "onMessage",
    value: function onMessage(payload) {
      _store__WEBPACK_IMPORTED_MODULE_1__["default"].findComponent(payload.id).receiveMessage(payload);
      Object(_util__WEBPACK_IMPORTED_MODULE_0__["dispatch"])('livewire:update');
    }
  }, {
    key: "onError",
    value: function onError(payloadThatFailedSending) {
      _store__WEBPACK_IMPORTED_MODULE_1__["default"].findComponent(payloadThatFailedSending.id).messageSendFailed();
    }
  }, {
    key: "sendMessage",
    value: function sendMessage(message) {
      message.prepareForSend();
      this.driver.sendMessage(message.payload());
    }
  }]);

  return Connection;
}();



/***/ }),

/***/ "./src/js/dom/directive.js":
/*!*********************************!*\
  !*** ./src/js/dom/directive.js ***!
  \*********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var _default =
/*#__PURE__*/
function () {
  function _default(type, modifiers, rawName, el) {
    _classCallCheck(this, _default);

    this.type = type;
    this.modifiers = modifiers;
    this.rawName = rawName;
    this.el = el;
    this.eventContext;
  }

  _createClass(_default, [{
    key: "setEventContext",
    value: function setEventContext(context) {
      this.eventContext = context;
    }
  }, {
    key: "durationOr",
    value: function durationOr(defaultDuration) {
      var durationInMilliSeconds;
      var durationInMilliSecondsString = this.modifiers.find(function (mod) {
        return mod.match(/(.*)ms/);
      });
      var durationInSecondsString = this.modifiers.find(function (mod) {
        return mod.match(/(.*)s/);
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
      var methodAndParamString = method.match(/(.*)\((.*)\)/);

      if (methodAndParamString) {
        // This "$event" is for use inside the livewire event handler.
        var $event = this.eventContext;
        method = methodAndParamString[1];
        params = methodAndParamString[2].split(', ').map(function (param) {
          return eval(param);
        });
      }

      return {
        method: method,
        params: params
      };
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

  return _default;
}();



/***/ }),

/***/ "./src/js/dom/directive_manager.js":
/*!*****************************************!*\
  !*** ./src/js/dom/directive_manager.js ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
/* harmony import */ var _directive__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./directive */ "./src/js/dom/directive.js");
function _toArray(arr) { return _arrayWithHoles(arr) || _iterableToArray(arr) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var prefix = __webpack_require__(/*! ./prefix.js */ "./src/js/dom/prefix.js")();

var _default =
/*#__PURE__*/
function () {
  function _default(el) {
    _classCallCheck(this, _default);

    this.el = el;
    this.directives = this.extractTypeModifiersAndValue();
  }

  _createClass(_default, [{
    key: "all",
    value: function all() {
      return Object.values(this.directives);
    }
  }, {
    key: "has",
    value: function has(type) {
      return Object.keys(this.directives).includes(type);
    }
  }, {
    key: "missing",
    value: function missing(type) {
      return !Object.keys(this.directives).includes(type);
    }
  }, {
    key: "get",
    value: function get(type) {
      return this.directives[type];
    }
  }, {
    key: "extractTypeModifiersAndValue",
    value: function extractTypeModifiersAndValue() {
      var _this = this;

      var directives = {};
      this.el.getAttributeNames() // Filter only the livewire directives.
      .filter(function (name) {
        return name.match(new RegExp(prefix + ':'));
      }) // Parse out the type, modifiers, and value from it.
      .forEach(function (name) {
        var _name$replace$split = name.replace(new RegExp(prefix + ':'), '').split('.'),
            _name$replace$split2 = _toArray(_name$replace$split),
            type = _name$replace$split2[0],
            modifiers = _name$replace$split2.slice(1);

        directives[type] = new _directive__WEBPACK_IMPORTED_MODULE_0__["default"](type, modifiers, name, _this.el);
      });
      return directives;
    }
  }]);

  return _default;
}();



/***/ }),

/***/ "./src/js/dom/dom.js":
/*!***************************!*\
  !*** ./src/js/dom/dom.js ***!
  \***************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DOM; });
/* harmony import */ var _dom_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dom_element */ "./src/js/dom/dom_element.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var prefix = __webpack_require__(/*! ./prefix.js */ "./src/js/dom/prefix.js")();
/**
 * This is intended to isolate all native DOM operations. The operations that happen
 * one specific element will be instance methods, the operations you would normally
 * perform on the "document" (like "document.querySelector") will be static methods.
 */


var DOM =
/*#__PURE__*/
function () {
  function DOM() {
    _classCallCheck(this, DOM);
  }

  _createClass(DOM, null, [{
    key: "rootComponentElements",
    value: function rootComponentElements() {
      return Array.from(document.querySelectorAll("[".concat(prefix, "\\:id]"))).map(function (el) {
        return new _dom_element__WEBPACK_IMPORTED_MODULE_0__["default"](el);
      });
    }
  }, {
    key: "rootComponentElementsWithNoParents",
    value: function rootComponentElementsWithNoParents() {
      // In CSS, it's simple to select all elements that DO have a certain ancestor.
      // However, it's not simple (kinda impossible) to select elements that DONT have
      // a certain ancestor. Therefore, we will flip the logic: select all roots that DO have
      // have a root ancestor, then select all roots that DONT, then diff the two.
      // Convert NodeLists to Arrays so we can use ".includes()". Ew.
      var allEls = Array.from(document.querySelectorAll("[".concat(prefix, "\\:id]")));
      var onlyChildEls = Array.from(document.querySelectorAll("[".concat(prefix, "\\:id] [").concat(prefix, "\\:id]")));
      return allEls.filter(function (el) {
        return !onlyChildEls.includes(el);
      }).map(function (el) {
        return new _dom_element__WEBPACK_IMPORTED_MODULE_0__["default"](el);
      });
    }
  }, {
    key: "allModelElementsInside",
    value: function allModelElementsInside(root) {
      return Array.from(root.querySelectorAll("[".concat(prefix, "\\:model]"))).map(function (el) {
        return new _dom_element__WEBPACK_IMPORTED_MODULE_0__["default"](el);
      });
    }
  }, {
    key: "getByAttributeAndValue",
    value: function getByAttributeAndValue(attribute, value) {
      return new _dom_element__WEBPACK_IMPORTED_MODULE_0__["default"](document.querySelector("[".concat(prefix, "\\:").concat(attribute, "=\"").concat(value, "\"]")));
    }
  }, {
    key: "prefix",
    get: function get() {
      return prefix;
    }
  }]);

  return DOM;
}();



/***/ }),

/***/ "./src/js/dom/dom_element.js":
/*!***********************************!*\
  !*** ./src/js/dom/dom_element.js ***!
  \***********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return DOMElement; });
/* harmony import */ var _directive_manager__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./directive_manager */ "./src/js/dom/directive_manager.js");
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }



var prefix = __webpack_require__(/*! ./prefix.js */ "./src/js/dom/prefix.js")();
/**
 * Consider this a decorator for the ElementNode JavaScript object. (Hence the
 * method forwarding I have to do at the bottom)
 */


var DOMElement =
/*#__PURE__*/
function () {
  function DOMElement(el) {
    _classCallCheck(this, DOMElement);

    this.el = el;
    this.directives = new _directive_manager__WEBPACK_IMPORTED_MODULE_0__["default"](el);
  }

  _createClass(DOMElement, [{
    key: "nextFrame",
    value: function nextFrame(fn) {
      var _this = this;

      requestAnimationFrame(function () {
        requestAnimationFrame(fn.bind(_this));
      });
    }
  }, {
    key: "rawNode",
    value: function rawNode() {
      return this.el;
    }
  }, {
    key: "transitionElementIn",
    value: function transitionElementIn() {
      var _this2 = this;

      if (!this.directives.has('transition')) return;
      var directive = this.directives.get('transition'); // If ".out" modifier is passed, don't fade in.

      if (directive.modifiers.includes('out') && !directive.modifiers.includes('in')) {
        return true;
      }

      if (directive.modifiers.includes('fade')) {
        this.el.style.opacity = 0;
        this.el.style.transition = "opacity ".concat(directive.durationOr(300) / 1000, "s ease");
        this.nextFrame(function () {
          _this2.el.style.opacity = 1;
        });
        return;
      }

      var transitionName = directive.value;
      this.el.classList.add("".concat(transitionName, "-enter"));
      this.el.classList.add("".concat(transitionName, "-enter-active"));
      this.nextFrame(function () {
        _this2.el.classList.remove("".concat(transitionName, "-enter"));

        var duration = Number(getComputedStyle(_this2.el).transitionDuration.replace('s', '')) * 1000;
        setTimeout(function () {
          _this2.el.classList.remove("".concat(transitionName, "-enter-active"));
        }, duration);
      });
    }
  }, {
    key: "transitionElementOut",
    value: function transitionElementOut(onDiscarded) {
      var _this3 = this;

      if (!this.directives.has('transition')) return true;
      var directive = this.directives.get('transition'); // If ".in" modifier is passed, don't fade out.

      if (directive.modifiers.includes('in') && !directive.modifiers.includes('out')) {
        return true;
      }

      if (directive.modifiers.includes('fade')) {
        this.nextFrame(function () {
          _this3.el.style.opacity = 0;
          setTimeout(function () {
            onDiscarded(_this3.el);

            _this3.el.remove();
          }, directive.durationOr(300));
        });
        return false;
      }

      var transitionName = directive.value;
      this.el.classList.add("".concat(transitionName, "-leave-active"));
      this.nextFrame(function () {
        _this3.el.classList.add("".concat(transitionName, "-leave"));

        var duration = Number(getComputedStyle(_this3.el).transitionDuration.replace('s', '')) * 1000;
        setTimeout(function () {
          onDiscarded(_this3.el);

          _this3.el.remove();
        }, duration);
      });
      return false;
    }
  }, {
    key: "closestRoot",
    value: function closestRoot() {
      return this.closestByAttribute('id');
    }
  }, {
    key: "closestByAttribute",
    value: function closestByAttribute(attribute) {
      return new DOMElement(this.el.closest("[".concat(prefix, "\\:").concat(attribute, "]")));
    }
  }, {
    key: "isComponentRootEl",
    value: function isComponentRootEl() {
      return this.hasAttribute('id');
    }
  }, {
    key: "isVueComponent",
    value: function isVueComponent() {
      return !!this.asVueComponent();
    }
  }, {
    key: "asVueComponent",
    value: function asVueComponent() {
      return this.rawNode().__vue__;
    }
  }, {
    key: "hasAttribute",
    value: function hasAttribute(attribute) {
      return this.el.hasAttribute("".concat(prefix, ":").concat(attribute));
    }
  }, {
    key: "getAttribute",
    value: function getAttribute(attribute) {
      return this.el.getAttribute("".concat(prefix, ":").concat(attribute));
    }
  }, {
    key: "setAttribute",
    value: function setAttribute(attribute, value) {
      return this.el.setAttribute("".concat(prefix, ":").concat(attribute), value);
    }
  }, {
    key: "isFocused",
    value: function isFocused() {
      return this.el === document.activeElement;
    }
  }, {
    key: "hasFocus",
    value: function hasFocus() {
      return this.el === document.activeElement;
    }
  }, {
    key: "preserveValueAttributeIfNotDirty",
    value: function preserveValueAttributeIfNotDirty(fromEl, dirtyInputs) {
      if (this.directives.missing('model')) return; // If the input is not dirty && the input element is focused, keep the
      // value the same, but change other attributes.

      if (!Array.from(dirtyInputs).includes(this.directives.get('model').value) && fromEl.isFocused()) {
        // Transfer the current "fromEl" value (preserving / overriding it).
        this.setInputValue(fromEl.valueFromInput());
      }
    }
  }, {
    key: "isInput",
    value: function isInput() {
      return ['INPUT', 'TEXTAREA', 'SELECT'].includes(this.el.tagName.toUpperCase());
    }
  }, {
    key: "isTextInput",
    value: function isTextInput() {
      return ['INPUT', 'TEXTAREA'].includes(this.el.tagName.toUpperCase()) && !['checkbox', 'radio'].includes(this.el.type);
    }
  }, {
    key: "valueFromInput",
    value: function valueFromInput() {
      if (this.el.type === 'checkbox') {
        return this.el.checked;
      } else if (this.el.tagName === 'SELECT' && this.el.multiple) {
        return this.getSelectValues();
      }

      return this.el.value;
    }
  }, {
    key: "setInputValueFromModel",
    value: function setInputValueFromModel(component) {
      var modelString = this.directives.get('model').value;
      var modelStringWithArraySyntaxForNumericKeys = modelString.replace(/\.([0-9]+)/, function (match, num) {
        return "[".concat(num, "]");
      });
      var modelValue = eval('component.data.' + modelStringWithArraySyntaxForNumericKeys);
      if (modelValue === undefined) return;
      this.setInputValue(modelValue);
    }
  }, {
    key: "setInputValue",
    value: function setInputValue(value) {
      if (this.rawNode().__vue__) {
        // If it's a vue component pass down the value prop.
        // Also, Vue will throw a warning because we are programmaticallly
        // setting a prop, we need to silence that.
        var originalSilent = window.Vue.config.silent;
        window.Vue.config.silent = true;
        this.rawNode().__vue__.$props.value = value;
        window.Vue.config.silent = originalSilent;
      } else if (this.el.type === 'radio') {
        this.el.checked = this.el.value == value;
      } else if (this.el.type === 'checkbox') {
        this.el.checked = !!value;
      } else if (this.el.tagName === 'SELECT') {
        this.updateSelect(value);
      } else {
        this.el.value = value;
      }
    }
  }, {
    key: "getSelectValues",
    value: function getSelectValues() {
      return Array.from(this.el.options).filter(function (option) {
        return option.selected;
      }).map(function (option) {
        return option.value || option.text;
      });
    }
  }, {
    key: "updateSelect",
    value: function updateSelect(value) {
      var arrayWrappedValue = [].concat(value);
      Array.from(this.el.options).forEach(function (option) {
        option.selected = arrayWrappedValue.includes(option.value);
      });
    }
  }, {
    key: "isSameNode",
    value: function isSameNode(el) {
      // We need to drop down to the raw node if we are comparing
      // to another "DOMElement" Instance.
      if (typeof el.rawNode === 'function') {
        return this.el.isSameNode(el.rawNode());
      }

      return this.el.isSameNode(el);
    }
  }, {
    key: "getAttributeNames",
    value: function getAttributeNames() {
      var _this$el;

      return (_this$el = this.el).getAttributeNames.apply(_this$el, arguments);
    }
  }, {
    key: "addEventListener",
    value: function addEventListener() {
      var _this$el2;

      return (_this$el2 = this.el).addEventListener.apply(_this$el2, arguments);
    }
  }, {
    key: "querySelector",
    value: function querySelector() {
      var _this$el3;

      return (_this$el3 = this.el).querySelector.apply(_this$el3, arguments);
    }
  }, {
    key: "querySelectorAll",
    value: function querySelectorAll() {
      var _this$el4;

      return (_this$el4 = this.el).querySelectorAll.apply(_this$el4, arguments);
    }
  }, {
    key: "ref",
    get: function get() {
      return this.directives.has('ref') ? this.directives.get('ref').value : null;
    }
  }, {
    key: "classList",
    get: function get() {
      return this.el.classList;
    }
  }]);

  return DOMElement;
}();



/***/ }),

/***/ "./src/js/dom/morphdom/index.js":
/*!**************************************!*\
  !*** ./src/js/dom/morphdom/index.js ***!
  \**************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _morphAttrs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./morphAttrs */ "./src/js/dom/morphdom/morphAttrs.js");
/* harmony import */ var _morphdom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./morphdom */ "./src/js/dom/morphdom/morphdom.js");


var morphdom = Object(_morphdom__WEBPACK_IMPORTED_MODULE_1__["default"])(_morphAttrs__WEBPACK_IMPORTED_MODULE_0__["default"]);
/* harmony default export */ __webpack_exports__["default"] = (morphdom);

/***/ }),

/***/ "./src/js/dom/morphdom/morphAttrs.js":
/*!*******************************************!*\
  !*** ./src/js/dom/morphdom/morphAttrs.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return morphAttrs; });
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util */ "./src/js/dom/morphdom/util.js");

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
  var fromValue;

  for (i = attrs.length - 1; i >= 0; --i) {
    attr = attrs[i];
    attrName = attr.name;
    attrNamespaceURI = attr.namespaceURI;
    attrValue = attr.value;

    if (attrNamespaceURI) {
      attrName = attr.localName || attrName;
      fromValue = fromNode.getAttributeNS(attrNamespaceURI, attrName);

      if (fromValue !== attrValue) {
        fromNode.setAttributeNS(attrNamespaceURI, attrName, attrValue);
      }
    } else {
      fromValue = fromNode.getAttribute(attrName);

      if (fromValue !== attrValue) {
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

        if (!Object(_util__WEBPACK_IMPORTED_MODULE_0__["hasAttributeNS"])(toNode, attrNamespaceURI, attrName)) {
          fromNode.removeAttributeNS(attrNamespaceURI, attrName);
        }
      } else {
        if (!Object(_util__WEBPACK_IMPORTED_MODULE_0__["hasAttributeNS"])(toNode, null, attrName)) {
          fromNode.removeAttribute(attrName);
        }
      }
    }
  }
}

/***/ }),

/***/ "./src/js/dom/morphdom/morphdom.js":
/*!*****************************************!*\
  !*** ./src/js/dom/morphdom/morphdom.js ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return morphdomFactory; });
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util */ "./src/js/dom/morphdom/util.js");
/* harmony import */ var _specialElHandlers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./specialElHandlers */ "./src/js/dom/morphdom/specialElHandlers.js");
// From Caleb: I had to change all the "isSameNode"s to "isEqualNode"s and now everything is working great!

/**
 * I pulled in my own version of morphdom, so I could tweak it as needed.
 * Here are the tweaks I've made so far:
 *
 * 1) Changed all the "isSameNode"s to "isEqualNode"s so that morhing doesn't check by reference, only by equality.
 * 2) Automatically filter out any non-"ElementNode"s from the lifecycle hooks.
 */




var ELEMENT_NODE = 1;
var TEXT_NODE = 3;
var COMMENT_NODE = 8;

function noop() {}

function defaultGetNodeKey(node) {
  return node.id;
}

function callHook(hook) {
  if (hook.name !== 'getNodeKey' && hook.name !== 'onBeforeElUpdated') {} // debugger
  // console.log(hook.name, ...params)
  // Don't call hook on non-"DOMElement" elements.


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
        toNode = _util__WEBPACK_IMPORTED_MODULE_0__["doc"].createElement('html');
        toNode.innerHTML = toNodeHtml;
      } else {
        toNode = Object(_util__WEBPACK_IMPORTED_MODULE_0__["toElement"])(toNode);
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

    var fromNodesLookup = {};
    var keyedRemovalList;

    function addKeyedRemoval(key) {
      if (keyedRemovalList) {
        keyedRemovalList.push(key);
      } else {
        keyedRemovalList = [key];
      }
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
    } // // TreeWalker implementation is no faster, but keeping this around in case this changes in the future
    // function indexTree(root) {
    //     var treeWalker = document.createTreeWalker(
    //         root,
    //         NodeFilter.SHOW_ELEMENT);
    //
    //     var el;
    //     while((el = treeWalker.nextNode())) {
    //         var key = callHook(getNodeKey, el);
    //         if (key) {
    //             fromNodesLookup[key] = el;
    //         }
    //     }
    // }
    // // NodeIterator implementation is no faster, but keeping this around in case this changes in the future
    //
    // function indexTree(node) {
    //     var nodeIterator = document.createNodeIterator(node, NodeFilter.SHOW_ELEMENT);
    //     var el;
    //     while((el = nodeIterator.nextNode())) {
    //         var key = callHook(getNodeKey, el);
    //         if (key) {
    //             fromNodesLookup[key] = el;
    //         }
    //     }
    // }


    function indexTree(node) {
      if (node.nodeType === ELEMENT_NODE) {
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
      var curChild = el.firstChild;

      while (curChild) {
        var nextSibling = curChild.nextSibling;
        var key = callHook(getNodeKey, curChild);

        if (key) {
          var unmatchedFromEl = fromNodesLookup[key];

          if (unmatchedFromEl && Object(_util__WEBPACK_IMPORTED_MODULE_0__["compareNodeNames"])(curChild, unmatchedFromEl)) {
            curChild.parentNode.replaceChild(unmatchedFromEl, curChild);
            morphEl(unmatchedFromEl, curChild);
          }
        }

        handleNodeAdded(curChild);
        curChild = nextSibling;
      }
    }

    function morphEl(fromEl, toEl, childrenOnly) {
      var toElKey = callHook(getNodeKey, toEl);
      var curFromNodeKey;

      if (toElKey) {
        // If an element with an ID is being morphed then it is will be in the final
        // DOM so clear it out of the saved elements collection
        delete fromNodesLookup[toElKey];
      }

      if (toEl.isEqualNode && toEl.isEqualNode(fromEl)) {
        return;
      }

      if (!childrenOnly) {
        if (callHook(onBeforeElUpdated, fromEl, toEl) === false) {
          return;
        }

        morphAttrs(fromEl, toEl);
        callHook(onElUpdated, fromEl);

        if (callHook(onBeforeElChildrenUpdated, fromEl, toEl) === false) {
          return;
        }
      }

      if (fromEl.nodeName !== 'TEXTAREA') {
        var curToNodeChild = toEl.firstChild;
        var curFromNodeChild = fromEl.firstChild;
        var curToNodeKey;
        var fromNextSibling;
        var toNextSibling;
        var matchingFromEl;

        outer: while (curToNodeChild) {
          toNextSibling = curToNodeChild.nextSibling;
          curToNodeKey = callHook(getNodeKey, curToNodeChild);

          while (curFromNodeChild) {
            fromNextSibling = curFromNodeChild.nextSibling;

            if (curToNodeChild.isEqualNode && curToNodeChild.isEqualNode(curFromNodeChild)) {
              curToNodeChild = toNextSibling;
              curFromNodeChild = fromNextSibling;
              continue outer;
            }

            curFromNodeKey = callHook(getNodeKey, curFromNodeChild);
            var curFromNodeType = curFromNodeChild.nodeType;
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
                      if (curFromNodeChild.nextSibling === matchingFromEl) {
                        // Special case for single element removals. To avoid removing the original
                        // DOM node out of the tree (since that can break CSS transitions, etc.),
                        // we will instead discard the current node and wait until the next
                        // iteration to properly match up the keyed target element with its matching
                        // element in the original tree
                        isCompatible = false;
                      } else {
                        // We found a matching keyed element somewhere in the original DOM tree.
                        // Let's moving the original DOM node into the current position and morph
                        // it.
                        // NOTE: We use insertBefore instead of replaceChild because we want to go through
                        // the `removeNode()` function for the node that is being discarded so that
                        // all lifecycle hooks are correctly invoked
                        fromEl.insertBefore(matchingFromEl, curFromNodeChild);
                        fromNextSibling = curFromNodeChild.nextSibling;

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

                isCompatible = isCompatible !== false && Object(_util__WEBPACK_IMPORTED_MODULE_0__["compareNodeNames"])(curFromNodeChild, curToNodeChild);

                if (isCompatible) {
                  // We found compatible DOM elements so transform
                  // the current "from" node to match the current
                  // target DOM node.
                  morphEl(curFromNodeChild, curToNodeChild);
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
              curToNodeChild = toNextSibling;
              curFromNodeChild = fromNextSibling;
              continue outer;
            } // No compatible match so remove the old node from the DOM and continue trying to find a
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

            curFromNodeChild = fromNextSibling;
          } // If we got this far then we did not find a candidate match for
          // our "to node" and we exhausted all of the children "from"
          // nodes. Therefore, we will just append the current "to" node
          // to the end


          if (curToNodeKey && (matchingFromEl = fromNodesLookup[curToNodeKey]) && Object(_util__WEBPACK_IMPORTED_MODULE_0__["compareNodeNames"])(matchingFromEl, curToNodeChild)) {
            fromEl.appendChild(matchingFromEl);
            morphEl(matchingFromEl, curToNodeChild);
          } else {
            var onBeforeNodeAddedResult = callHook(onBeforeNodeAdded, curToNodeChild);

            if (onBeforeNodeAddedResult !== false) {
              if (onBeforeNodeAddedResult) {
                curToNodeChild = onBeforeNodeAddedResult;
              }

              if (curToNodeChild.actualize) {
                curToNodeChild = curToNodeChild.actualize(fromEl.ownerDocument || _util__WEBPACK_IMPORTED_MODULE_0__["doc"]);
              }

              fromEl.appendChild(curToNodeChild);
              handleNodeAdded(curToNodeChild);
            }
          }

          curToNodeChild = toNextSibling;
          curFromNodeChild = fromNextSibling;
        } // We have processed all of the "to nodes". If curFromNodeChild is
        // non-null then we still have some from nodes left over that need
        // to be removed


        while (curFromNodeChild) {
          fromNextSibling = curFromNodeChild.nextSibling;

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

      var specialElHandler = _specialElHandlers__WEBPACK_IMPORTED_MODULE_1__["default"][fromEl.nodeName];

      if (specialElHandler && !fromEl.hasAttribute('wire:model')) {
        specialElHandler(fromEl, toEl);
      }
    } // END: morphEl(...)


    var morphedNode = fromNode;
    var morphedNodeType = morphedNode.nodeType;
    var toNodeType = toNode.nodeType;

    if (!childrenOnly) {
      // Handle the case where we are given two DOM nodes that are not
      // compatible (e.g. <div> --> <span> or <div> --> TEXT)
      if (morphedNodeType === ELEMENT_NODE) {
        if (toNodeType === ELEMENT_NODE) {
          if (!Object(_util__WEBPACK_IMPORTED_MODULE_0__["compareNodeNames"])(fromNode, toNode)) {
            callHook(onNodeDiscarded, fromNode);
            morphedNode = Object(_util__WEBPACK_IMPORTED_MODULE_0__["moveChildren"])(fromNode, Object(_util__WEBPACK_IMPORTED_MODULE_0__["createElementNS"])(toNode.nodeName, toNode.namespaceURI));
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
        morphedNode = morphedNode.actualize(fromNode.ownerDocument || _util__WEBPACK_IMPORTED_MODULE_0__["doc"]);
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

/***/ }),

/***/ "./src/js/dom/morphdom/specialElHandlers.js":
/*!**************************************************!*\
  !*** ./src/js/dom/morphdom/specialElHandlers.js ***!
  \**************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util */ "./src/js/dom/morphdom/util.js");


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

/* harmony default export */ __webpack_exports__["default"] = ({
  /**
   * Needed for IE. Apparently IE doesn't think that "selected" is an
   * attribute when reading over the attributes using selectEl.attributes
   */
  OPTION: function OPTION(fromEl, toEl) {
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

    if (!Object(_util__WEBPACK_IMPORTED_MODULE_0__["hasAttributeNS"])(toEl, null, 'value')) {
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
    if (!Object(_util__WEBPACK_IMPORTED_MODULE_0__["hasAttributeNS"])(toEl, null, 'multiple')) {
      var selectedIndex = -1;
      var i = 0;
      var curChild = toEl.firstChild;

      while (curChild) {
        var nodeName = curChild.nodeName;

        if (nodeName && nodeName.toUpperCase() === 'OPTION') {
          if (Object(_util__WEBPACK_IMPORTED_MODULE_0__["hasAttributeNS"])(curChild, null, 'selected')) {
            selectedIndex = i;
            break;
          }

          i++;
        }

        curChild = curChild.nextSibling;
      }

      fromEl.selectedIndex = i;
    }
  }
});

/***/ }),

/***/ "./src/js/dom/morphdom/util.js":
/*!*************************************!*\
  !*** ./src/js/dom/morphdom/util.js ***!
  \*************************************/
/*! exports provided: doc, hasAttributeNS, toElement, compareNodeNames, createElementNS, moveChildren */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "doc", function() { return doc; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "hasAttributeNS", function() { return hasAttributeNS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "toElement", function() { return toElement; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "compareNodeNames", function() { return compareNodeNames; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createElementNS", function() { return createElementNS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "moveChildren", function() { return moveChildren; });
var range; // Create a range object for efficently rendering strings to elements.

var NS_XHTML = 'http://www.w3.org/1999/xhtml';
var doc = typeof document === 'undefined' ? undefined : document;
var testEl = doc ? doc.body || doc.createElement('div') : {}; // Fixes <https://github.com/patrick-steele-idem/morphdom/issues/32>
// (IE7+ support) <=IE7 does not support el.hasAttribute(name)

var actualHasAttributeNS;

if (testEl.hasAttributeNS) {
  actualHasAttributeNS = function actualHasAttributeNS(el, namespaceURI, name) {
    return el.hasAttributeNS(namespaceURI, name);
  };
} else if (testEl.hasAttribute) {
  actualHasAttributeNS = function actualHasAttributeNS(el, namespaceURI, name) {
    return el.hasAttribute(name);
  };
} else {
  actualHasAttributeNS = function actualHasAttributeNS(el, namespaceURI, name) {
    return el.getAttributeNode(namespaceURI, name) != null;
  };
}

var hasAttributeNS = actualHasAttributeNS;
function toElement(str) {
  if (!range && doc.createRange) {
    range = doc.createRange();
    range.selectNode(doc.body);
  }

  var fragment;

  if (range && range.createContextualFragment) {
    fragment = range.createContextualFragment(str);
  } else {
    fragment = doc.createElement('body');
    fragment.innerHTML = str;
  }

  return fragment.childNodes[0];
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

/***/ }),

/***/ "./src/js/dom/prefix.js":
/*!******************************!*\
  !*** ./src/js/dom/prefix.js ***!
  \******************************/
/*! no static exports found */
/***/ (function(module, exports) {

var prefix = null;

module.exports = function () {
  if (prefix === null) {
    prefix = (document.querySelector('meta[name="livewire-prefix"]') || {
      content: 'wire'
    }).content;
  }

  return prefix;
};

/***/ }),

/***/ "./src/js/index.js":
/*!*************************!*\
  !*** ./src/js/index.js ***!
  \*************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./store */ "./src/js/store.js");
/* harmony import */ var _dom_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dom/dom */ "./src/js/dom/dom.js");
/* harmony import */ var _component__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./component */ "./src/js/component/index.js");
/* harmony import */ var _connection__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./connection */ "./src/js/connection/index.js");
/* harmony import */ var _connection_drivers__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./connection/drivers */ "./src/js/connection/drivers/index.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }







var Livewire =
/*#__PURE__*/
function () {
  function Livewire() {
    var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {
      driver: 'http'
    },
        driver = _ref.driver;

    _classCallCheck(this, Livewire);

    if (_typeof(driver) !== 'object') {
      driver = _connection_drivers__WEBPACK_IMPORTED_MODULE_4__["default"][driver];
    }

    this.connection = new _connection__WEBPACK_IMPORTED_MODULE_3__["default"](driver);
    this.components = _store__WEBPACK_IMPORTED_MODULE_0__["default"];
    this.start();
  }

  _createClass(Livewire, [{
    key: "emit",
    value: function emit(event) {
      var _this$components;

      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        params[_key - 1] = arguments[_key];
      }

      (_this$components = this.components).emit.apply(_this$components, [event].concat(params));
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
      this.components.wipeComponents();
    }
  }, {
    key: "start",
    value: function start() {
      var _this = this;

      _dom_dom__WEBPACK_IMPORTED_MODULE_1__["default"].rootComponentElementsWithNoParents().forEach(function (el) {
        _this.components.addComponent(new _component__WEBPACK_IMPORTED_MODULE_2__["default"](el, _this.connection));
      });
    }
  }]);

  return Livewire;
}();

if (!window.Livewire) {
  window.Livewire = Livewire;
}

/* harmony default export */ __webpack_exports__["default"] = (Livewire);

/***/ }),

/***/ "./src/js/message.js":
/*!***************************!*\
  !*** ./src/js/message.js ***!
  \***************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var _default =
/*#__PURE__*/
function () {
  function _default(component, actionQueue) {
    _classCallCheck(this, _default);

    this.component = component;
    this.actionQueue = actionQueue;
  }

  _createClass(_default, [{
    key: "prepareForSend",
    value: function prepareForSend() {
      this.loadingEls = this.component.setLoading(this.refs);
    }
  }, {
    key: "payload",
    value: function payload() {
      return {
        id: this.component.id,
        data: this.component.data,
        name: this.component.name,
        children: this.component.children,
        middleware: this.component.middleware,
        checksum: this.component.checksum,
        actionQueue: this.actionQueue.map(function (action) {
          // This ensures only the type & payload properties only get sent over.
          return {
            type: action.type,
            payload: action.payload
          };
        })
      };
    }
  }, {
    key: "storeResponse",
    value: function storeResponse(payload) {
      return this.response = {
        id: payload.id,
        dom: payload.dom,
        children: payload.children,
        dirtyInputs: payload.dirtyInputs,
        eventQueue: payload.eventQueue,
        listeningFor: payload.listeningFor,
        data: payload.data,
        redirectTo: payload.redirectTo
      };
    }
  }, {
    key: "refs",
    get: function get() {
      return this.actionQueue.map(function (action) {
        return action.ref;
      }).filter(function (ref) {
        return ref;
      });
    }
  }]);

  return _default;
}();



/***/ }),

/***/ "./src/js/node_initializer.js":
/*!************************************!*\
  !*** ./src/js/node_initializer.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util */ "./src/js/util/index.js");
/* harmony import */ var _action_model__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./action/model */ "./src/js/action/model.js");
/* harmony import */ var _action_method__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./action/method */ "./src/js/action/method.js");
/* harmony import */ var _dom_dom_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./dom/dom_element */ "./src/js/dom/dom_element.js");
/* harmony import */ var _store__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./store */ "./src/js/store.js");





/* harmony default export */ __webpack_exports__["default"] = ({
  initialize: function initialize(el, component) {
    var _this = this;

    // Parse out "direcives", "modifiers", and "value" from livewire attributes.
    el.directives.all().forEach(function (directive) {
      switch (directive.type) {
        case 'loading':
          _this.registerElementForLoading(el, directive, component);

          break;

        case 'poll':
          _this.fireActionOnInterval(el, directive, component);

          break;

        case 'model':
          el.setInputValueFromModel(component);

          _this.attachModelListener(el, directive, component);

          break;

        default:
          _this.attachDomListener(el, directive, component);

          break;
      }
    });
  },
  registerElementForLoading: function registerElementForLoading(el, directive, component) {
    var refName = el.directives.get('target') && el.directives.get('target').value;
    component.addLoadingEl(el, directive.value, refName, directive.modifiers.includes('remove'));
  },
  fireActionOnInterval: function fireActionOnInterval(el, directive, component) {
    var method = directive.method || '$refresh';
    setInterval(function () {
      component.addAction(new _action_method__WEBPACK_IMPORTED_MODULE_2__["default"](method, directive.params, el));
    }, directive.durationOr(500));
  },
  attachModelListener: function attachModelListener(el, directive, component) {
    var isLazy = directive.modifiers.includes('lazy');

    var debounceIf = function debounceIf(condition, callback, time) {
      return condition ? Object(_util__WEBPACK_IMPORTED_MODULE_0__["debounce"])(callback, time) : callback;
    };

    var hasDebounceModifier = directive.modifiers.includes('debounce'); // If it's a Vue component, listen for Vue input event emission.

    if (el.isVueComponent()) {
      el.asVueComponent().$on('input', debounceIf(hasDebounceModifier, function (e) {
        var model = directive.value;
        var value = e;
        component.addAction(new _action_model__WEBPACK_IMPORTED_MODULE_1__["default"](model, value, el));
      }, directive.durationOr(150)));
    } else {
      // If it's a text input and not .lazy, debounce, otherwise fire immediately.
      el.addEventListener(isLazy ? 'change' : 'input', debounceIf(hasDebounceModifier || el.isTextInput() && !isLazy, function (e) {
        var model = directive.value;
        var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_3__["default"](e.target);
        var value = el.valueFromInput();
        component.addAction(new _action_model__WEBPACK_IMPORTED_MODULE_1__["default"](model, value, el));
      }, directive.durationOr(150)));
    }
  },
  attachDomListener: function attachDomListener(el, directive, component) {
    switch (directive.type) {
      case 'keydown':
        this.attachListener(el, directive, component, function (e) {
          // Only handle listener if no, or matching key modifiers are passed.
          return !(directive.modifiers.length === 0 || directive.modifiers.includes(Object(_util__WEBPACK_IMPORTED_MODULE_0__["kebabCase"])(e.key)));
        });
        break;

      default:
        this.attachListener(el, directive, component);
        break;
    }
  },
  attachListener: function attachListener(el, directive, component, callback) {
    var _this2 = this;

    el.addEventListener(directive.type, function (e) {
      if (callback && callback(e) !== false) {
        return;
      }

      var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_3__["default"](e.target); // This is outside the conditional below so "wire:click.prevent" without
      // a value still prevents default.

      _this2.preventAndStop(e, directive.modifiers); // Check for global event emission.


      if (directive.value.match(/\$emit\(.*\)/)) {
        var tempStoreForEval = _store__WEBPACK_IMPORTED_MODULE_4__["default"];
        eval(directive.value.replace(/\$emit\((.*)\)/, function (match, group1) {
          return 'tempStoreForEval.emit(' + group1 + ')';
        }));
        return;
      }

      if (directive.value) {
        directive.setEventContext(e);
        component.addAction(new _action_method__WEBPACK_IMPORTED_MODULE_2__["default"](directive.method, directive.params, el));
      }
    });
  },
  preventAndStop: function preventAndStop(event, modifiers) {
    modifiers.includes('prevent') && event.preventDefault();
    modifiers.includes('stop') && event.stopPropagation();
  }
});

/***/ }),

/***/ "./src/js/store.js":
/*!*************************!*\
  !*** ./src/js/store.js ***!
  \*************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _action_event__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./action/event */ "./src/js/action/event.js");

var store = {
  componentsById: {},
  listeners: {},
  addComponent: function addComponent(component) {
    return this.componentsById[component.id] = component;
  },
  findComponent: function findComponent(id) {
    return this.componentsById[id];
  },
  wipeComponents: function wipeComponents() {
    this.componentsById = {};
  },
  on: function on(event, callback) {
    if (this.listeners[event] !== undefined) {
      this.listeners[event].push(callback);
    } else {
      this.listeners[event] = [callback];
    }
  },
  emit: function emit(event) {
    var _this = this;

    for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      params[_key - 1] = arguments[_key];
    }

    Object.keys(this.listeners).forEach(function (event) {
      _this.listeners[event].forEach(function (callback) {
        return callback.apply(void 0, params);
      });
    });
    this.componentsListeningForEvent(event).forEach(function (component) {
      return component.addAction(new _action_event__WEBPACK_IMPORTED_MODULE_0__["default"](event, params));
    });
  },
  componentsListeningForEvent: function componentsListeningForEvent(event) {
    var _this2 = this;

    return Object.keys(this.componentsById).map(function (key) {
      return _this2.componentsById[key];
    }).filter(function (component) {
      return component.events.includes(event);
    });
  }
};
/* harmony default export */ __webpack_exports__["default"] = (store);

/***/ }),

/***/ "./src/js/util/add_mixin.js":
/*!**********************************!*\
  !*** ./src/js/util/add_mixin.js ***!
  \**********************************/
/*! exports provided: addMixin */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "addMixin", function() { return addMixin; });
function addMixin(classTarget) {
  for (var _len = arguments.length, sources = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
    sources[_key - 1] = arguments[_key];
  }

  sources.forEach(function (source) {
    var descriptors = Object.keys(source).reduce(function (descriptors, key) {
      descriptors[key] = Object.getOwnPropertyDescriptor(source, key);
      return descriptors;
    }, {});
    Object.getOwnPropertySymbols(source).forEach(function (sym) {
      var descriptor = Object.getOwnPropertyDescriptor(source, sym);

      if (descriptor.enumerable) {
        descriptors[sym] = descriptor;
      }
    });
    Object.defineProperties(classTarget.prototype, descriptors);
  });
  return classTarget.prototype;
}

/***/ }),

/***/ "./src/js/util/debounce.js":
/*!*********************************!*\
  !*** ./src/js/util/debounce.js ***!
  \*********************************/
/*! exports provided: debounceWithFiringOnBothEnds, debounce */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "debounceWithFiringOnBothEnds", function() { return debounceWithFiringOnBothEnds; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "debounce", function() { return debounce; });
// This is kindof like a normal debouncer, except it behaves like both "immediate" and
// "non-immediate" strategies. I'll try to visually demonstrate the differences:
// [normal] =    .......|
// [immediate] = |.......
// [both] =      |......|
// The reason I want it to fire on both ends of the debounce is for the following scenario:
// - a user types a letter into an input
// - the debouncer is waiting 200ms to send the ajax request
// - in the meantime a user hits the enter key
// - the debouncer is not up yet, so the "enter" request will get fired before the "key" request
// Note: I also added a checker in here ("wasInterupted") for the the case of a user
// only typing one key, but two ajax requests getting sent.
function debounceWithFiringOnBothEnds(func, wait) {
  var timeout;
  var timesInterupted = 0;
  return function () {
    var context = this,
        args = arguments;
    var callNow = !timeout;

    if (timeout) {
      clearTimeout(timeout);
      timesInterupted++;
    }

    timeout = setTimeout(function () {
      timeout = null;

      if (timesInterupted > 0) {
        func.apply(context, args);
        timesInterupted = 0;
      }
    }, wait);

    if (callNow) {
      func.apply(context, args);
    }
  };
}
;
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

/***/ }),

/***/ "./src/js/util/dispatch.js":
/*!*********************************!*\
  !*** ./src/js/util/dispatch.js ***!
  \*********************************/
/*! exports provided: dispatch */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "dispatch", function() { return dispatch; });
// I grabbed this from Turbolink's codebase.
function dispatch(eventName) {
  var _ref = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {},
      target = _ref.target,
      cancelable = _ref.cancelable,
      data = _ref.data;

  var event = document.createEvent("Events");
  event.initEvent(eventName, true, cancelable == true);
  event.data = data || {}; // Fix setting `defaultPrevented` when `preventDefault()` is called
  // http://stackoverflow.com/questions/23349191/event-preventdefault-is-not-working-in-ie-11-for-custom-events

  if (event.cancelable && !preventDefaultSupported) {
    var preventDefault = event.preventDefault;

    event.preventDefault = function () {
      if (!this.defaultPrevented) {
        Object.defineProperty(this, "defaultPrevented", {
          get: function get() {
            return true;
          }
        });
      }

      preventDefault.call(this);
    };
  }

  (target || document).dispatchEvent(event);
  return event;
}

var preventDefaultSupported = function () {
  var event = document.createEvent("Events");
  event.initEvent("test", true, true);
  event.preventDefault();
  return event.defaultPrevented;
}();

/***/ }),

/***/ "./src/js/util/index.js":
/*!******************************!*\
  !*** ./src/js/util/index.js ***!
  \******************************/
/*! exports provided: debounceWithFiringOnBothEnds, debounce, walk, dispatch, addMixin, kebabCase, tap */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "kebabCase", function() { return kebabCase; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "tap", function() { return tap; });
/* harmony import */ var _debounce__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./debounce */ "./src/js/util/debounce.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "debounceWithFiringOnBothEnds", function() { return _debounce__WEBPACK_IMPORTED_MODULE_0__["debounceWithFiringOnBothEnds"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "debounce", function() { return _debounce__WEBPACK_IMPORTED_MODULE_0__["debounce"]; });

/* harmony import */ var _walk__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./walk */ "./src/js/util/walk.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "walk", function() { return _walk__WEBPACK_IMPORTED_MODULE_1__["walk"]; });

/* harmony import */ var _dispatch__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./dispatch */ "./src/js/util/dispatch.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "dispatch", function() { return _dispatch__WEBPACK_IMPORTED_MODULE_2__["dispatch"]; });

/* harmony import */ var _add_mixin__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./add_mixin */ "./src/js/util/add_mixin.js");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "addMixin", function() { return _add_mixin__WEBPACK_IMPORTED_MODULE_3__["addMixin"]; });





function kebabCase(subject) {
  return subject.split(/[_\s]/).join("-").toLowerCase();
}
function tap(output, callback) {
  callback(output);
  return output;
}

/***/ }),

/***/ "./src/js/util/walk.js":
/*!*****************************!*\
  !*** ./src/js/util/walk.js ***!
  \*****************************/
/*! exports provided: walk */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "walk", function() { return walk; });
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

/***/ }),

/***/ 0:
/*!*******************************!*\
  !*** multi ./src/js/index.js ***!
  \*******************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(/*! /Users/calebporzio/Documents/Code/sites/livewire/src/js/index.js */"./src/js/index.js");


/***/ })

/******/ });
});