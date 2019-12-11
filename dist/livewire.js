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

/***/ "./node_modules/get-value/index.js":
/*!*****************************************!*\
  !*** ./node_modules/get-value/index.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/*!
 * get-value <https://github.com/jonschlinkert/get-value>
 *
 * Copyright (c) 2014-2018, Jon Schlinkert.
 * Released under the MIT License.
 */

const isObject = __webpack_require__(/*! isobject */ "./node_modules/isobject/index.js");

module.exports = function(target, path, options) {
  if (!isObject(options)) {
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
  return isObject(val) || Array.isArray(val) || typeof val === 'function';
}


/***/ }),

/***/ "./node_modules/isobject/index.js":
/*!****************************************!*\
  !*** ./node_modules/isobject/index.js ***!
  \****************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/*!
 * isobject <https://github.com/jonschlinkert/isobject>
 *
 * Copyright (c) 2014-2017, Jon Schlinkert.
 * Released under the MIT License.
 */



module.exports = function isObject(val) {
  return val != null && typeof val === 'object' && Array.isArray(val) === false;
};


/***/ }),

/***/ "./node_modules/process/browser.js":
/*!*****************************************!*\
  !*** ./node_modules/process/browser.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),

/***/ "./node_modules/promise-polyfill/src/finally.js":
/*!******************************************************!*\
  !*** ./node_modules/promise-polyfill/src/finally.js ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/**
 * @this {Promise}
 */
function finallyConstructor(callback) {
  var constructor = this.constructor;
  return this.then(
    function(value) {
      // @ts-ignore
      return constructor.resolve(callback()).then(function() {
        return value;
      });
    },
    function(reason) {
      // @ts-ignore
      return constructor.resolve(callback()).then(function() {
        // @ts-ignore
        return constructor.reject(reason);
      });
    }
  );
}

/* harmony default export */ __webpack_exports__["default"] = (finallyConstructor);


/***/ }),

/***/ "./node_modules/promise-polyfill/src/index.js":
/*!****************************************************!*\
  !*** ./node_modules/promise-polyfill/src/index.js ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(setImmediate) {/* harmony import */ var _finally__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./finally */ "./node_modules/promise-polyfill/src/finally.js");


// Store setTimeout reference so promise-polyfill will be unaffected by
// other code modifying setTimeout (like sinon.useFakeTimers())
var setTimeoutFunc = setTimeout;

function isArray(x) {
  return Boolean(x && typeof x.length !== 'undefined');
}

function noop() {}

// Polyfill for Function.prototype.bind
function bind(fn, thisArg) {
  return function() {
    fn.apply(thisArg, arguments);
  };
}

/**
 * @constructor
 * @param {Function} fn
 */
function Promise(fn) {
  if (!(this instanceof Promise))
    throw new TypeError('Promises must be constructed via new');
  if (typeof fn !== 'function') throw new TypeError('not a function');
  /** @type {!number} */
  this._state = 0;
  /** @type {!boolean} */
  this._handled = false;
  /** @type {Promise|undefined} */
  this._value = undefined;
  /** @type {!Array<!Function>} */
  this._deferreds = [];

  doResolve(fn, this);
}

function handle(self, deferred) {
  while (self._state === 3) {
    self = self._value;
  }
  if (self._state === 0) {
    self._deferreds.push(deferred);
    return;
  }
  self._handled = true;
  Promise._immediateFn(function() {
    var cb = self._state === 1 ? deferred.onFulfilled : deferred.onRejected;
    if (cb === null) {
      (self._state === 1 ? resolve : reject)(deferred.promise, self._value);
      return;
    }
    var ret;
    try {
      ret = cb(self._value);
    } catch (e) {
      reject(deferred.promise, e);
      return;
    }
    resolve(deferred.promise, ret);
  });
}

function resolve(self, newValue) {
  try {
    // Promise Resolution Procedure: https://github.com/promises-aplus/promises-spec#the-promise-resolution-procedure
    if (newValue === self)
      throw new TypeError('A promise cannot be resolved with itself.');
    if (
      newValue &&
      (typeof newValue === 'object' || typeof newValue === 'function')
    ) {
      var then = newValue.then;
      if (newValue instanceof Promise) {
        self._state = 3;
        self._value = newValue;
        finale(self);
        return;
      } else if (typeof then === 'function') {
        doResolve(bind(then, newValue), self);
        return;
      }
    }
    self._state = 1;
    self._value = newValue;
    finale(self);
  } catch (e) {
    reject(self, e);
  }
}

function reject(self, newValue) {
  self._state = 2;
  self._value = newValue;
  finale(self);
}

function finale(self) {
  if (self._state === 2 && self._deferreds.length === 0) {
    Promise._immediateFn(function() {
      if (!self._handled) {
        Promise._unhandledRejectionFn(self._value);
      }
    });
  }

  for (var i = 0, len = self._deferreds.length; i < len; i++) {
    handle(self, self._deferreds[i]);
  }
  self._deferreds = null;
}

/**
 * @constructor
 */
function Handler(onFulfilled, onRejected, promise) {
  this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
  this.onRejected = typeof onRejected === 'function' ? onRejected : null;
  this.promise = promise;
}

/**
 * Take a potentially misbehaving resolver function and make sure
 * onFulfilled and onRejected are only called once.
 *
 * Makes no guarantees about asynchrony.
 */
function doResolve(fn, self) {
  var done = false;
  try {
    fn(
      function(value) {
        if (done) return;
        done = true;
        resolve(self, value);
      },
      function(reason) {
        if (done) return;
        done = true;
        reject(self, reason);
      }
    );
  } catch (ex) {
    if (done) return;
    done = true;
    reject(self, ex);
  }
}

Promise.prototype['catch'] = function(onRejected) {
  return this.then(null, onRejected);
};

Promise.prototype.then = function(onFulfilled, onRejected) {
  // @ts-ignore
  var prom = new this.constructor(noop);

  handle(this, new Handler(onFulfilled, onRejected, prom));
  return prom;
};

Promise.prototype['finally'] = _finally__WEBPACK_IMPORTED_MODULE_0__["default"];

Promise.all = function(arr) {
  return new Promise(function(resolve, reject) {
    if (!isArray(arr)) {
      return reject(new TypeError('Promise.all accepts an array'));
    }

    var args = Array.prototype.slice.call(arr);
    if (args.length === 0) return resolve([]);
    var remaining = args.length;

    function res(i, val) {
      try {
        if (val && (typeof val === 'object' || typeof val === 'function')) {
          var then = val.then;
          if (typeof then === 'function') {
            then.call(
              val,
              function(val) {
                res(i, val);
              },
              reject
            );
            return;
          }
        }
        args[i] = val;
        if (--remaining === 0) {
          resolve(args);
        }
      } catch (ex) {
        reject(ex);
      }
    }

    for (var i = 0; i < args.length; i++) {
      res(i, args[i]);
    }
  });
};

Promise.resolve = function(value) {
  if (value && typeof value === 'object' && value.constructor === Promise) {
    return value;
  }

  return new Promise(function(resolve) {
    resolve(value);
  });
};

Promise.reject = function(value) {
  return new Promise(function(resolve, reject) {
    reject(value);
  });
};

Promise.race = function(arr) {
  return new Promise(function(resolve, reject) {
    if (!isArray(arr)) {
      return reject(new TypeError('Promise.race accepts an array'));
    }

    for (var i = 0, len = arr.length; i < len; i++) {
      Promise.resolve(arr[i]).then(resolve, reject);
    }
  });
};

// Use polyfill for setImmediate for performance gains
Promise._immediateFn =
  // @ts-ignore
  (typeof setImmediate === 'function' &&
    function(fn) {
      // @ts-ignore
      setImmediate(fn);
    }) ||
  function(fn) {
    setTimeoutFunc(fn, 0);
  };

Promise._unhandledRejectionFn = function _unhandledRejectionFn(err) {
  if (typeof console !== 'undefined' && console) {
    console.warn('Possible Unhandled Promise Rejection:', err); // eslint-disable-line no-console
  }
};

/* harmony default export */ __webpack_exports__["default"] = (Promise);

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../../timers-browserify/main.js */ "./node_modules/timers-browserify/main.js").setImmediate))

/***/ }),

/***/ "./node_modules/promise-polyfill/src/polyfill.js":
/*!*******************************************************!*\
  !*** ./node_modules/promise-polyfill/src/polyfill.js ***!
  \*******************************************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* WEBPACK VAR INJECTION */(function(global) {/* harmony import */ var _index__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./index */ "./node_modules/promise-polyfill/src/index.js");
/* harmony import */ var _finally__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./finally */ "./node_modules/promise-polyfill/src/finally.js");



/** @suppress {undefinedVars} */
var globalNS = (function() {
  // the only reliable means to get the global object is
  // `Function('return this')()`
  // However, this causes CSP violations in Chrome apps.
  if (typeof self !== 'undefined') {
    return self;
  }
  if (typeof window !== 'undefined') {
    return window;
  }
  if (typeof global !== 'undefined') {
    return global;
  }
  throw new Error('unable to locate global object');
})();

if (!('Promise' in globalNS)) {
  globalNS['Promise'] = _index__WEBPACK_IMPORTED_MODULE_0__["default"];
} else if (!globalNS.Promise.prototype['finally']) {
  globalNS.Promise.prototype['finally'] = _finally__WEBPACK_IMPORTED_MODULE_1__["default"];
}

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/setimmediate/setImmediate.js":
/*!***************************************************!*\
  !*** ./node_modules/setimmediate/setImmediate.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global, process) {(function (global, undefined) {
    "use strict";

    if (global.setImmediate) {
        return;
    }

    var nextHandle = 1; // Spec says greater than zero
    var tasksByHandle = {};
    var currentlyRunningATask = false;
    var doc = global.document;
    var registerImmediate;

    function setImmediate(callback) {
      // Callback can either be a function or a string
      if (typeof callback !== "function") {
        callback = new Function("" + callback);
      }
      // Copy function arguments
      var args = new Array(arguments.length - 1);
      for (var i = 0; i < args.length; i++) {
          args[i] = arguments[i + 1];
      }
      // Store and register the task
      var task = { callback: callback, args: args };
      tasksByHandle[nextHandle] = task;
      registerImmediate(nextHandle);
      return nextHandle++;
    }

    function clearImmediate(handle) {
        delete tasksByHandle[handle];
    }

    function run(task) {
        var callback = task.callback;
        var args = task.args;
        switch (args.length) {
        case 0:
            callback();
            break;
        case 1:
            callback(args[0]);
            break;
        case 2:
            callback(args[0], args[1]);
            break;
        case 3:
            callback(args[0], args[1], args[2]);
            break;
        default:
            callback.apply(undefined, args);
            break;
        }
    }

    function runIfPresent(handle) {
        // From the spec: "Wait until any invocations of this algorithm started before this one have completed."
        // So if we're currently running a task, we'll need to delay this invocation.
        if (currentlyRunningATask) {
            // Delay by doing a setTimeout. setImmediate was tried instead, but in Firefox 7 it generated a
            // "too much recursion" error.
            setTimeout(runIfPresent, 0, handle);
        } else {
            var task = tasksByHandle[handle];
            if (task) {
                currentlyRunningATask = true;
                try {
                    run(task);
                } finally {
                    clearImmediate(handle);
                    currentlyRunningATask = false;
                }
            }
        }
    }

    function installNextTickImplementation() {
        registerImmediate = function(handle) {
            process.nextTick(function () { runIfPresent(handle); });
        };
    }

    function canUsePostMessage() {
        // The test against `importScripts` prevents this implementation from being installed inside a web worker,
        // where `global.postMessage` means something completely different and can't be used for this purpose.
        if (global.postMessage && !global.importScripts) {
            var postMessageIsAsynchronous = true;
            var oldOnMessage = global.onmessage;
            global.onmessage = function() {
                postMessageIsAsynchronous = false;
            };
            global.postMessage("", "*");
            global.onmessage = oldOnMessage;
            return postMessageIsAsynchronous;
        }
    }

    function installPostMessageImplementation() {
        // Installs an event handler on `global` for the `message` event: see
        // * https://developer.mozilla.org/en/DOM/window.postMessage
        // * http://www.whatwg.org/specs/web-apps/current-work/multipage/comms.html#crossDocumentMessages

        var messagePrefix = "setImmediate$" + Math.random() + "$";
        var onGlobalMessage = function(event) {
            if (event.source === global &&
                typeof event.data === "string" &&
                event.data.indexOf(messagePrefix) === 0) {
                runIfPresent(+event.data.slice(messagePrefix.length));
            }
        };

        if (global.addEventListener) {
            global.addEventListener("message", onGlobalMessage, false);
        } else {
            global.attachEvent("onmessage", onGlobalMessage);
        }

        registerImmediate = function(handle) {
            global.postMessage(messagePrefix + handle, "*");
        };
    }

    function installMessageChannelImplementation() {
        var channel = new MessageChannel();
        channel.port1.onmessage = function(event) {
            var handle = event.data;
            runIfPresent(handle);
        };

        registerImmediate = function(handle) {
            channel.port2.postMessage(handle);
        };
    }

    function installReadyStateChangeImplementation() {
        var html = doc.documentElement;
        registerImmediate = function(handle) {
            // Create a <script> element; its readystatechange event will be fired asynchronously once it is inserted
            // into the document. Do so, thus queuing up the task. Remember to clean up once it's been called.
            var script = doc.createElement("script");
            script.onreadystatechange = function () {
                runIfPresent(handle);
                script.onreadystatechange = null;
                html.removeChild(script);
                script = null;
            };
            html.appendChild(script);
        };
    }

    function installSetTimeoutImplementation() {
        registerImmediate = function(handle) {
            setTimeout(runIfPresent, 0, handle);
        };
    }

    // If supported, we should attach to the prototype of global, since that is where setTimeout et al. live.
    var attachTo = Object.getPrototypeOf && Object.getPrototypeOf(global);
    attachTo = attachTo && attachTo.setTimeout ? attachTo : global;

    // Don't get fooled by e.g. browserify environments.
    if ({}.toString.call(global.process) === "[object process]") {
        // For Node.js before 0.9
        installNextTickImplementation();

    } else if (canUsePostMessage()) {
        // For non-IE10 modern browsers
        installPostMessageImplementation();

    } else if (global.MessageChannel) {
        // For web workers, where supported
        installMessageChannelImplementation();

    } else if (doc && "onreadystatechange" in doc.createElement("script")) {
        // For IE 6–8
        installReadyStateChangeImplementation();

    } else {
        // For older browsers
        installSetTimeoutImplementation();
    }

    attachTo.setImmediate = setImmediate;
    attachTo.clearImmediate = clearImmediate;
}(typeof self === "undefined" ? typeof global === "undefined" ? this : global : self));

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js"), __webpack_require__(/*! ./../process/browser.js */ "./node_modules/process/browser.js")))

/***/ }),

/***/ "./node_modules/timers-browserify/main.js":
/*!************************************************!*\
  !*** ./node_modules/timers-browserify/main.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {var scope = (typeof global !== "undefined" && global) ||
            (typeof self !== "undefined" && self) ||
            window;
var apply = Function.prototype.apply;

// DOM APIs, for completeness

exports.setTimeout = function() {
  return new Timeout(apply.call(setTimeout, scope, arguments), clearTimeout);
};
exports.setInterval = function() {
  return new Timeout(apply.call(setInterval, scope, arguments), clearInterval);
};
exports.clearTimeout =
exports.clearInterval = function(timeout) {
  if (timeout) {
    timeout.close();
  }
};

function Timeout(id, clearFn) {
  this._id = id;
  this._clearFn = clearFn;
}
Timeout.prototype.unref = Timeout.prototype.ref = function() {};
Timeout.prototype.close = function() {
  this._clearFn.call(scope, this._id);
};

// Does not start the time, just sets up the members needed.
exports.enroll = function(item, msecs) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = msecs;
};

exports.unenroll = function(item) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = -1;
};

exports._unrefActive = exports.active = function(item) {
  clearTimeout(item._idleTimeoutId);

  var msecs = item._idleTimeout;
  if (msecs >= 0) {
    item._idleTimeoutId = setTimeout(function onTimeout() {
      if (item._onTimeout)
        item._onTimeout();
    }, msecs);
  }
};

// setimmediate attaches itself to the global object
__webpack_require__(/*! setimmediate */ "./node_modules/setimmediate/setImmediate.js");
// On some exotic environments, it's not clear which object `setimmediate` was
// able to install onto.  Search each possibility in the same order as the
// `setimmediate` library.
exports.setImmediate = (typeof self !== "undefined" && self.setImmediate) ||
                       (typeof global !== "undefined" && global.setImmediate) ||
                       (this && this.setImmediate);
exports.clearImmediate = (typeof self !== "undefined" && self.clearImmediate) ||
                         (typeof global !== "undefined" && global.clearImmediate) ||
                         (this && this.clearImmediate);

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),

/***/ "./node_modules/whatwg-fetch/fetch.js":
/*!********************************************!*\
  !*** ./node_modules/whatwg-fetch/fetch.js ***!
  \********************************************/
/*! exports provided: Headers, Request, Response, DOMException, fetch */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Headers", function() { return Headers; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Request", function() { return Request; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "Response", function() { return Response; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "DOMException", function() { return DOMException; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "fetch", function() { return fetch; });
var support = {
  searchParams: 'URLSearchParams' in self,
  iterable: 'Symbol' in self && 'iterator' in Symbol,
  blob:
    'FileReader' in self &&
    'Blob' in self &&
    (function() {
      try {
        new Blob()
        return true
      } catch (e) {
        return false
      }
    })(),
  formData: 'FormData' in self,
  arrayBuffer: 'ArrayBuffer' in self
}

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
  ]

  var isArrayBufferView =
    ArrayBuffer.isView ||
    function(obj) {
      return obj && viewClasses.indexOf(Object.prototype.toString.call(obj)) > -1
    }
}

function normalizeName(name) {
  if (typeof name !== 'string') {
    name = String(name)
  }
  if (/[^a-z0-9\-#$%&'*+.^_`|~]/i.test(name)) {
    throw new TypeError('Invalid character in header field name')
  }
  return name.toLowerCase()
}

function normalizeValue(value) {
  if (typeof value !== 'string') {
    value = String(value)
  }
  return value
}

// Build a destructive iterator for the value list
function iteratorFor(items) {
  var iterator = {
    next: function() {
      var value = items.shift()
      return {done: value === undefined, value: value}
    }
  }

  if (support.iterable) {
    iterator[Symbol.iterator] = function() {
      return iterator
    }
  }

  return iterator
}

function Headers(headers) {
  this.map = {}

  if (headers instanceof Headers) {
    headers.forEach(function(value, name) {
      this.append(name, value)
    }, this)
  } else if (Array.isArray(headers)) {
    headers.forEach(function(header) {
      this.append(header[0], header[1])
    }, this)
  } else if (headers) {
    Object.getOwnPropertyNames(headers).forEach(function(name) {
      this.append(name, headers[name])
    }, this)
  }
}

Headers.prototype.append = function(name, value) {
  name = normalizeName(name)
  value = normalizeValue(value)
  var oldValue = this.map[name]
  this.map[name] = oldValue ? oldValue + ', ' + value : value
}

Headers.prototype['delete'] = function(name) {
  delete this.map[normalizeName(name)]
}

Headers.prototype.get = function(name) {
  name = normalizeName(name)
  return this.has(name) ? this.map[name] : null
}

Headers.prototype.has = function(name) {
  return this.map.hasOwnProperty(normalizeName(name))
}

Headers.prototype.set = function(name, value) {
  this.map[normalizeName(name)] = normalizeValue(value)
}

Headers.prototype.forEach = function(callback, thisArg) {
  for (var name in this.map) {
    if (this.map.hasOwnProperty(name)) {
      callback.call(thisArg, this.map[name], name, this)
    }
  }
}

Headers.prototype.keys = function() {
  var items = []
  this.forEach(function(value, name) {
    items.push(name)
  })
  return iteratorFor(items)
}

Headers.prototype.values = function() {
  var items = []
  this.forEach(function(value) {
    items.push(value)
  })
  return iteratorFor(items)
}

Headers.prototype.entries = function() {
  var items = []
  this.forEach(function(value, name) {
    items.push([name, value])
  })
  return iteratorFor(items)
}

if (support.iterable) {
  Headers.prototype[Symbol.iterator] = Headers.prototype.entries
}

function consumed(body) {
  if (body.bodyUsed) {
    return Promise.reject(new TypeError('Already read'))
  }
  body.bodyUsed = true
}

function fileReaderReady(reader) {
  return new Promise(function(resolve, reject) {
    reader.onload = function() {
      resolve(reader.result)
    }
    reader.onerror = function() {
      reject(reader.error)
    }
  })
}

function readBlobAsArrayBuffer(blob) {
  var reader = new FileReader()
  var promise = fileReaderReady(reader)
  reader.readAsArrayBuffer(blob)
  return promise
}

function readBlobAsText(blob) {
  var reader = new FileReader()
  var promise = fileReaderReady(reader)
  reader.readAsText(blob)
  return promise
}

function readArrayBufferAsText(buf) {
  var view = new Uint8Array(buf)
  var chars = new Array(view.length)

  for (var i = 0; i < view.length; i++) {
    chars[i] = String.fromCharCode(view[i])
  }
  return chars.join('')
}

function bufferClone(buf) {
  if (buf.slice) {
    return buf.slice(0)
  } else {
    var view = new Uint8Array(buf.byteLength)
    view.set(new Uint8Array(buf))
    return view.buffer
  }
}

function Body() {
  this.bodyUsed = false

  this._initBody = function(body) {
    this._bodyInit = body
    if (!body) {
      this._bodyText = ''
    } else if (typeof body === 'string') {
      this._bodyText = body
    } else if (support.blob && Blob.prototype.isPrototypeOf(body)) {
      this._bodyBlob = body
    } else if (support.formData && FormData.prototype.isPrototypeOf(body)) {
      this._bodyFormData = body
    } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
      this._bodyText = body.toString()
    } else if (support.arrayBuffer && support.blob && isDataView(body)) {
      this._bodyArrayBuffer = bufferClone(body.buffer)
      // IE 10-11 can't handle a DataView body.
      this._bodyInit = new Blob([this._bodyArrayBuffer])
    } else if (support.arrayBuffer && (ArrayBuffer.prototype.isPrototypeOf(body) || isArrayBufferView(body))) {
      this._bodyArrayBuffer = bufferClone(body)
    } else {
      this._bodyText = body = Object.prototype.toString.call(body)
    }

    if (!this.headers.get('content-type')) {
      if (typeof body === 'string') {
        this.headers.set('content-type', 'text/plain;charset=UTF-8')
      } else if (this._bodyBlob && this._bodyBlob.type) {
        this.headers.set('content-type', this._bodyBlob.type)
      } else if (support.searchParams && URLSearchParams.prototype.isPrototypeOf(body)) {
        this.headers.set('content-type', 'application/x-www-form-urlencoded;charset=UTF-8')
      }
    }
  }

  if (support.blob) {
    this.blob = function() {
      var rejected = consumed(this)
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
    }

    this.arrayBuffer = function() {
      if (this._bodyArrayBuffer) {
        return consumed(this) || Promise.resolve(this._bodyArrayBuffer)
      } else {
        return this.blob().then(readBlobAsArrayBuffer)
      }
    }
  }

  this.text = function() {
    var rejected = consumed(this)
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
  }

  if (support.formData) {
    this.formData = function() {
      return this.text().then(decode)
    }
  }

  this.json = function() {
    return this.text().then(JSON.parse)
  }

  return this
}

// HTTP methods whose capitalization should be normalized
var methods = ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'POST', 'PUT']

function normalizeMethod(method) {
  var upcased = method.toUpperCase()
  return methods.indexOf(upcased) > -1 ? upcased : method
}

function Request(input, options) {
  options = options || {}
  var body = options.body

  if (input instanceof Request) {
    if (input.bodyUsed) {
      throw new TypeError('Already read')
    }
    this.url = input.url
    this.credentials = input.credentials
    if (!options.headers) {
      this.headers = new Headers(input.headers)
    }
    this.method = input.method
    this.mode = input.mode
    this.signal = input.signal
    if (!body && input._bodyInit != null) {
      body = input._bodyInit
      input.bodyUsed = true
    }
  } else {
    this.url = String(input)
  }

  this.credentials = options.credentials || this.credentials || 'same-origin'
  if (options.headers || !this.headers) {
    this.headers = new Headers(options.headers)
  }
  this.method = normalizeMethod(options.method || this.method || 'GET')
  this.mode = options.mode || this.mode || null
  this.signal = options.signal || this.signal
  this.referrer = null

  if ((this.method === 'GET' || this.method === 'HEAD') && body) {
    throw new TypeError('Body not allowed for GET or HEAD requests')
  }
  this._initBody(body)
}

Request.prototype.clone = function() {
  return new Request(this, {body: this._bodyInit})
}

function decode(body) {
  var form = new FormData()
  body
    .trim()
    .split('&')
    .forEach(function(bytes) {
      if (bytes) {
        var split = bytes.split('=')
        var name = split.shift().replace(/\+/g, ' ')
        var value = split.join('=').replace(/\+/g, ' ')
        form.append(decodeURIComponent(name), decodeURIComponent(value))
      }
    })
  return form
}

function parseHeaders(rawHeaders) {
  var headers = new Headers()
  // Replace instances of \r\n and \n followed by at least one space or horizontal tab with a space
  // https://tools.ietf.org/html/rfc7230#section-3.2
  var preProcessedHeaders = rawHeaders.replace(/\r?\n[\t ]+/g, ' ')
  preProcessedHeaders.split(/\r?\n/).forEach(function(line) {
    var parts = line.split(':')
    var key = parts.shift().trim()
    if (key) {
      var value = parts.join(':').trim()
      headers.append(key, value)
    }
  })
  return headers
}

Body.call(Request.prototype)

function Response(bodyInit, options) {
  if (!options) {
    options = {}
  }

  this.type = 'default'
  this.status = options.status === undefined ? 200 : options.status
  this.ok = this.status >= 200 && this.status < 300
  this.statusText = 'statusText' in options ? options.statusText : 'OK'
  this.headers = new Headers(options.headers)
  this.url = options.url || ''
  this._initBody(bodyInit)
}

Body.call(Response.prototype)

Response.prototype.clone = function() {
  return new Response(this._bodyInit, {
    status: this.status,
    statusText: this.statusText,
    headers: new Headers(this.headers),
    url: this.url
  })
}

Response.error = function() {
  var response = new Response(null, {status: 0, statusText: ''})
  response.type = 'error'
  return response
}

var redirectStatuses = [301, 302, 303, 307, 308]

Response.redirect = function(url, status) {
  if (redirectStatuses.indexOf(status) === -1) {
    throw new RangeError('Invalid status code')
  }

  return new Response(null, {status: status, headers: {location: url}})
}

var DOMException = self.DOMException
try {
  new DOMException()
} catch (err) {
  DOMException = function(message, name) {
    this.message = message
    this.name = name
    var error = Error(message)
    this.stack = error.stack
  }
  DOMException.prototype = Object.create(Error.prototype)
  DOMException.prototype.constructor = DOMException
}

function fetch(input, init) {
  return new Promise(function(resolve, reject) {
    var request = new Request(input, init)

    if (request.signal && request.signal.aborted) {
      return reject(new DOMException('Aborted', 'AbortError'))
    }

    var xhr = new XMLHttpRequest()

    function abortXhr() {
      xhr.abort()
    }

    xhr.onload = function() {
      var options = {
        status: xhr.status,
        statusText: xhr.statusText,
        headers: parseHeaders(xhr.getAllResponseHeaders() || '')
      }
      options.url = 'responseURL' in xhr ? xhr.responseURL : options.headers.get('X-Request-URL')
      var body = 'response' in xhr ? xhr.response : xhr.responseText
      resolve(new Response(body, options))
    }

    xhr.onerror = function() {
      reject(new TypeError('Network request failed'))
    }

    xhr.ontimeout = function() {
      reject(new TypeError('Network request failed'))
    }

    xhr.onabort = function() {
      reject(new DOMException('Aborted', 'AbortError'))
    }

    xhr.open(request.method, request.url, true)

    if (request.credentials === 'include') {
      xhr.withCredentials = true
    } else if (request.credentials === 'omit') {
      xhr.withCredentials = false
    }

    if ('responseType' in xhr && support.blob) {
      xhr.responseType = 'blob'
    }

    request.headers.forEach(function(value, name) {
      xhr.setRequestHeader(name, value)
    })

    if (request.signal) {
      request.signal.addEventListener('abort', abortXhr)

      xhr.onreadystatechange = function() {
        // DONE (success or failure)
        if (xhr.readyState === 4) {
          request.signal.removeEventListener('abort', abortXhr)
        }
      }
    }

    xhr.send(typeof request._bodyInit === 'undefined' ? null : request._bodyInit)
  })
}

fetch.polyfill = true

if (!self.fetch) {
  self.fetch = fetch
  self.Headers = Headers
  self.Request = Request
  self.Response = Response
}


/***/ }),

/***/ "./src/js/Component/PrefetchManager.js":
/*!*********************************************!*\
  !*** ./src/js/Component/PrefetchManager.js ***!
  \*********************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var PrefetchManager =
/*#__PURE__*/
function () {
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
    key: "storeResponseInMessageForPayload",
    value: function storeResponseInMessageForPayload(payload) {
      var message = this.prefetchMessagesByActionId[payload.fromPrefetch];
      if (message) message.storeResponse(payload);
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

/* harmony default export */ __webpack_exports__["default"] = (PrefetchManager);

/***/ }),

/***/ "./src/js/Component/index.js":
/*!***********************************!*\
  !*** ./src/js/Component/index.js ***!
  \***********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return Component; });
/* harmony import */ var _Message__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Message */ "./src/js/Message.js");
/* harmony import */ var _PrefetchMessage__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/PrefetchMessage */ "./src/js/PrefetchMessage.js");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/util */ "./src/js/util/index.js");
/* harmony import */ var _dom_morphdom__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/dom/morphdom */ "./src/js/dom/morphdom/index.js");
/* harmony import */ var _dom_dom__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/dom/dom */ "./src/js/dom/dom.js");
/* harmony import */ var _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/dom/dom_element */ "./src/js/dom/dom_element.js");
/* harmony import */ var _node_initializer__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @/node_initializer */ "./src/js/node_initializer.js");
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
/* harmony import */ var _PrefetchManager__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./PrefetchManager */ "./src/js/Component/PrefetchManager.js");
/* harmony import */ var _action_method__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @/action/method */ "./src/js/action/method.js");
/* harmony import */ var _action_model__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @/action/model */ "./src/js/action/model.js");
/* harmony import */ var _MessageBus__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ../MessageBus */ "./src/js/MessageBus.js");
function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance"); }

function _iterableToArrayLimit(arr, i) { if (!(Symbol.iterator in Object(arr) || Object.prototype.toString.call(arr) === "[object Arguments]")) { return; } var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

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

    el.rawNode().__livewire = this;
    this.id = el.getAttribute('id');
    var initialData = JSON.parse(this.extractLivewireAttribute('initial-data'));
    this.data = initialData.data || {};
    this.events = initialData.events || [];
    this.children = initialData.children || {};
    this.checksum = initialData.checksum || '';
    this.name = initialData.name || '';
    this.errorBag = initialData.errorBag || {};
    this.redirectTo = initialData.redirectTo || false;
    this.scopedListeners = new _MessageBus__WEBPACK_IMPORTED_MODULE_11__["default"](), this.connection = connection;
    this.actionQueue = [];
    this.messageInTransit = null;
    this.modelTimeout = null;
    this.tearDownCallbacks = [];
    this.prefetchManager = new _PrefetchManager__WEBPACK_IMPORTED_MODULE_8__["default"](this);
    _Store__WEBPACK_IMPORTED_MODULE_7__["default"].callHook('componentInitialized', this);
    this.initialize();
    this.registerEchoListeners();

    if (this.redirectTo) {
      this.redirect(this.redirectTo);
      return;
    }
  }

  _createClass(Component, [{
    key: "extractLivewireAttribute",
    value: function extractLivewireAttribute(name) {
      var value = this.el.getAttribute(name);
      this.el.removeAttribute(name);
      return value;
    }
  }, {
    key: "initialize",
    value: function initialize() {
      var _this = this;

      this.walk(function (el) {
        // Will run for every node in the component tree (not child component nodes).
        _node_initializer__WEBPACK_IMPORTED_MODULE_6__["default"].initialize(el, _this);
      }, function (el) {
        // When new component is encountered in the tree, add it.
        _Store__WEBPACK_IMPORTED_MODULE_7__["default"].addComponent(new Component(el, _this.connection));
      });
    }
  }, {
    key: "get",
    value: function get(name) {
      return this.data[name];
    }
  }, {
    key: "set",
    value: function set(name, value) {
      this.addAction(new _action_model__WEBPACK_IMPORTED_MODULE_10__["default"](name, value, this.el));
    }
  }, {
    key: "call",
    value: function call(method) {
      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
        params[_key - 1] = arguments[_key];
      }

      this.addAction(new _action_method__WEBPACK_IMPORTED_MODULE_9__["default"](method, params, this.el));
    }
  }, {
    key: "on",
    value: function on(event, callback) {
      this.scopedListeners.register(event, callback);
    }
  }, {
    key: "addAction",
    value: function addAction(action) {
      if (this.prefetchManager.actionHasPrefetch(action) && this.prefetchManager.actionPrefetchResponseHasBeenReceived(action)) {
        var message = this.prefetchManager.getPrefetchMessageByAction(action);
        this.handleResponse(message.response);
        this.prefetchManager.clearPrefetches();
        return;
      }

      this.actionQueue.push(action); // This debounce is here in-case two events fire at the "same" time:
      // For example: if you are listening for a click on element A,
      // and a "blur" on element B. If element B has focus, and then,
      // you click on element A, the blur event will fire before the "click"
      // event. This debounce captures them both in the actionsQueue and sends
      // them off at the same time.
      // Note: currently, it's set to 5ms, that might not be the right amount, we'll see.

      Object(_util__WEBPACK_IMPORTED_MODULE_2__["debounce"])(this.fireMessage, 5).apply(this); // Clear prefetches.

      this.prefetchManager.clearPrefetches();
    }
  }, {
    key: "fireMessage",
    value: function fireMessage() {
      if (this.messageInTransit) return;
      this.messageInTransit = new _Message__WEBPACK_IMPORTED_MODULE_0__["default"](this, this.actionQueue);
      this.connection.sendMessage(this.messageInTransit);
      _Store__WEBPACK_IMPORTED_MODULE_7__["default"].callHook('messageSent', this, this.messageInTransit);
      this.actionQueue = [];
    }
  }, {
    key: "messageSendFailed",
    value: function messageSendFailed() {
      _Store__WEBPACK_IMPORTED_MODULE_7__["default"].callHook('messageFailed', this);
      this.messageInTransit = null;
    }
  }, {
    key: "receiveMessage",
    value: function receiveMessage(payload) {
      var response = this.messageInTransit.storeResponse(payload);
      this.handleResponse(response); // This bit of logic ensures that if actions were queued while a request was
      // out to the server, they are sent when the request comes back.

      if (this.actionQueue.length > 0) {
        this.fireMessage();
      }
    }
  }, {
    key: "handleResponse",
    value: function handleResponse(response) {
      var _this2 = this;

      this.data = response.data;
      this.checksum = response.checksum;
      this.children = response.children;
      this.errorBag = response.errorBag; // This means "$this->redirect()" was called in the component. let's just bail and redirect.

      if (response.redirectTo) {
        this.redirect(response.redirectTo);
        return;
      }

      _Store__WEBPACK_IMPORTED_MODULE_7__["default"].callHook('responseReceived', this, response);
      this.replaceDom(response.dom, response.dirtyInputs);
      this.forceRefreshDataBoundElementsMarkedAsDirty(response.dirtyInputs);
      this.messageInTransit = null;

      if (response.eventQueue && response.eventQueue.length > 0) {
        response.eventQueue.forEach(function (event) {
          var _this2$scopedListener;

          (_this2$scopedListener = _this2.scopedListeners).call.apply(_this2$scopedListener, [event.event].concat(_toConsumableArray(event.params)));

          _Store__WEBPACK_IMPORTED_MODULE_7__["default"].emit.apply(_Store__WEBPACK_IMPORTED_MODULE_7__["default"], [event.event].concat(_toConsumableArray(event.params)));
        });
      }
    }
  }, {
    key: "redirect",
    value: function redirect(url) {
      window.location.href = url;
    }
  }, {
    key: "forceRefreshDataBoundElementsMarkedAsDirty",
    value: function forceRefreshDataBoundElementsMarkedAsDirty(dirtyInputs) {
      var _this3 = this;

      this.walk(function (el) {
        if (el.directives.missing('model')) return;
        var modelValue = el.directives.get('model').value;
        if (el.isFocused() && !dirtyInputs.includes(modelValue)) return;
        el.setInputValueFromModel(_this3);
      });
    }
  }, {
    key: "replaceDom",
    value: function replaceDom(rawDom) {
      _Store__WEBPACK_IMPORTED_MODULE_7__["default"].beforeDomUpdateCallback();
      this.handleMorph(this.formatDomBeforeDiffToAvoidConflictsWithVue(rawDom.trim()));
      _Store__WEBPACK_IMPORTED_MODULE_7__["default"].afterDomUpdateCallback();
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
    key: "addPrefetchAction",
    value: function addPrefetchAction(action) {
      if (this.prefetchManager.actionHasPrefetch(action)) {
        return;
      }

      var message = new _PrefetchMessage__WEBPACK_IMPORTED_MODULE_1__["default"](this, action);
      this.prefetchManager.addMessage(message);
      this.connection.sendMessage(message);
    }
  }, {
    key: "receivePrefetchMessage",
    value: function receivePrefetchMessage(payload) {
      this.prefetchManager.storeResponseInMessageForPayload(payload);
    }
  }, {
    key: "handleMorph",
    value: function handleMorph(dom) {
      var _this4 = this;

      Object(_dom_morphdom__WEBPACK_IMPORTED_MODULE_3__["default"])(this.el.rawNode(), dom, {
        childrenOnly: false,
        getNodeKey: function getNodeKey(node) {
          // This allows the tracking of elements by the "key" attribute, like in VueJs.
          return node.hasAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_4__["default"].prefix, ":key")) ? node.getAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_4__["default"].prefix, ":key")) // If no "key", then first check for "wire:id", then "wire:model", then "id"
          : node.hasAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_4__["default"].prefix, ":id")) ? node.getAttribute("".concat(_dom_dom__WEBPACK_IMPORTED_MODULE_4__["default"].prefix, ":id")) : node.id;
        },
        onBeforeNodeAdded: function onBeforeNodeAdded(node) {
          return new _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__["default"](node).transitionElementIn();
        },
        onBeforeNodeDiscarded: function onBeforeNodeDiscarded(node) {
          var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__["default"](node);
          return el.transitionElementOut(function (nodeDiscarded) {
            _Store__WEBPACK_IMPORTED_MODULE_7__["default"].callHook('elementRemoved', el, _this4);
          });
        },
        onNodeDiscarded: function onNodeDiscarded(node) {
          var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__["default"](node);
          _Store__WEBPACK_IMPORTED_MODULE_7__["default"].callHook('elementRemoved', el, _this4);

          if (node.__livewire) {
            _Store__WEBPACK_IMPORTED_MODULE_7__["default"].removeComponent(node.__livewire);
          }
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

          var fromEl = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__["default"](from); // Honor the "wire:ignore" attribute.

          if (fromEl.directives.has('ignore')) {
            if (fromEl.directives.get('ignore').modifiers.includes('self')) {
              // Don't update children of "wire:ingore.self" attribute.
              from.skipElUpdatingButStillUpdateChildren = true;
            } else {
              return false;
            }
          } // Children will update themselves.


          if (fromEl.isComponentRootEl() && fromEl.getAttribute('id') !== _this4.id) return false; // Don't touch Vue components

          if (fromEl.isVueComponent()) return false;
        },
        onElUpdated: function onElUpdated(node) {//
        },
        onNodeAdded: function onNodeAdded(node) {
          var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__["default"](node);
          var closestComponentId = el.closestRoot().getAttribute('id');

          if (closestComponentId === _this4.id) {
            _node_initializer__WEBPACK_IMPORTED_MODULE_6__["default"].initialize(el, _this4);
          } else if (el.isComponentRootEl()) {
            _Store__WEBPACK_IMPORTED_MODULE_7__["default"].addComponent(new Component(el, _this4.connection));
          } // Skip.

        }
      });
    }
  }, {
    key: "walk",
    value: function walk(callback) {
      var _this5 = this;

      var callbackWhenNewComponentIsEncountered = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : function (el) {};

      Object(_util__WEBPACK_IMPORTED_MODULE_2__["walk"])(this.el.rawNode(), function (node) {
        var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_5__["default"](node); // Skip the root component element.

        if (el.isSameNode(_this5.el)) {
          callback(el);
          return;
        } // If we encounter a nested component, skip walking that tree.


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
                _Store__WEBPACK_IMPORTED_MODULE_7__["default"].emit(event, e);
              });
            } else if (channel_type == 'presence') {
              Echo.join(channel)[event_name](function (e) {
                _Store__WEBPACK_IMPORTED_MODULE_7__["default"].emit(event, e);
              });
            } else if (channel_type == 'notification') {
              Echo["private"](channel).notification(function (notification) {
                _Store__WEBPACK_IMPORTED_MODULE_7__["default"].emit(event, notification);
              });
            } else {
              console.warn('Echo channel type not yet supported');
            }
          }
        });
      }
    }
  }, {
    key: "modelSyncDebounce",
    value: function modelSyncDebounce(callback, time) {
      var _this6 = this;

      return function (e) {
        clearTimeout(_this6.modelTimeout);

        _this6.modelTimeoutCallback = function () {
          callback(e);
        };

        _this6.modelTimeout = setTimeout(function () {
          callback(e);
          _this6.modelTimeout = null;
          _this6.modelTimeoutCallback = null;
        }, time);
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
      if (this.modelTimeout) {
        clearTimeout(this.modelTimeout);
        this.modelTimeoutCallback();
        this.modelTimeout = null;
        this.modelTimeoutCallback = null;
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
    key: "el",
    get: function get() {
      return _dom_dom__WEBPACK_IMPORTED_MODULE_4__["default"].getByAttributeAndValue('id', this.id);
    }
  }, {
    key: "root",
    get: function get() {
      return this.el;
    }
  }]);

  return Component;
}();



/***/ }),

/***/ "./src/js/HookManager.js":
/*!*******************************!*\
  !*** ./src/js/HookManager.js ***!
  \*******************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _MessageBus__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./MessageBus */ "./src/js/MessageBus.js");

/* harmony default export */ __webpack_exports__["default"] = ({
  availableHooks: ['componentInitialized', 'elementInitialized', 'elementRemoved', 'messageSent', 'messageFailed', 'responseReceived'],
  bus: new _MessageBus__WEBPACK_IMPORTED_MODULE_0__["default"](),
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
});

/***/ }),

/***/ "./src/js/Message.js":
/*!***************************!*\
  !*** ./src/js/Message.js ***!
  \***************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
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
    key: "payload",
    value: function payload() {
      var payload = {
        id: this.component.id,
        data: this.component.data,
        name: this.component.name,
        checksum: this.component.checksum,
        children: this.component.children,
        actionQueue: this.actionQueue.map(function (action) {
          // This ensures only the type & payload properties only get sent over.
          return {
            type: action.type,
            payload: action.payload
          };
        })
      };

      if (Object.keys(this.component.errorBag).length > 0) {
        payload.errorBag = this.component.errorBag;
      }

      return payload;
    }
  }, {
    key: "storeResponse",
    value: function storeResponse(payload) {
      return this.response = {
        id: payload.id,
        dom: payload.dom,
        checksum: payload.checksum,
        children: payload.children,
        dirtyInputs: payload.dirtyInputs,
        eventQueue: payload.eventQueue,
        events: payload.events,
        data: payload.data,
        redirectTo: payload.redirectTo,
        errorBag: payload.errorBag || {}
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

/***/ "./src/js/MessageBus.js":
/*!******************************!*\
  !*** ./src/js/MessageBus.js ***!
  \******************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return MessageBus; });
function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

var MessageBus =
/*#__PURE__*/
function () {
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
  }]);

  return MessageBus;
}();



/***/ }),

/***/ "./src/js/PrefetchMessage.js":
/*!***********************************!*\
  !*** ./src/js/PrefetchMessage.js ***!
  \***********************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return _default; });
/* harmony import */ var _Message__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Message */ "./src/js/Message.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }

function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _get(target, property, receiver) { if (typeof Reflect !== "undefined" && Reflect.get) { _get = Reflect.get; } else { _get = function _get(target, property, receiver) { var base = _superPropBase(target, property); if (!base) return; var desc = Object.getOwnPropertyDescriptor(base, property); if (desc.get) { return desc.get.call(receiver); } return desc.value; }; } return _get(target, property, receiver || target); }

function _superPropBase(object, property) { while (!Object.prototype.hasOwnProperty.call(object, property)) { object = _getPrototypeOf(object); if (object === null) break; } return object; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }



var _default =
/*#__PURE__*/
function (_Message) {
  _inherits(_default, _Message);

  function _default(component, action) {
    _classCallCheck(this, _default);

    return _possibleConstructorReturn(this, _getPrototypeOf(_default).call(this, component, [action]));
  }

  _createClass(_default, [{
    key: "payload",
    value: function payload() {
      return _objectSpread({
        fromPrefetch: this.prefetchId
      }, _get(_getPrototypeOf(_default.prototype), "payload", this).call(this));
    }
  }, {
    key: "storeResponse",
    value: function storeResponse(payload) {
      _get(_getPrototypeOf(_default.prototype), "storeResponse", this).call(this, payload);

      this.response.fromPrefetch = payload.fromPrefetch;
      return this.response;
    }
  }, {
    key: "prefetchId",
    get: function get() {
      return this.actionQueue[0].toId();
    }
  }]);

  return _default;
}(_Message__WEBPACK_IMPORTED_MODULE_0__["default"]);



/***/ }),

/***/ "./src/js/Store.js":
/*!*************************!*\
  !*** ./src/js/Store.js ***!
  \*************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _action_event__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/action/event */ "./src/js/action/event.js");
/* harmony import */ var _HookManager__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/HookManager */ "./src/js/HookManager.js");
/* harmony import */ var _MessageBus__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./MessageBus */ "./src/js/MessageBus.js");



var store = {
  componentsById: {},
  listeners: new _MessageBus__WEBPACK_IMPORTED_MODULE_2__["default"](),
  beforeDomUpdateCallback: function beforeDomUpdateCallback() {},
  afterDomUpdateCallback: function afterDomUpdateCallback() {},
  livewireIsInBackground: false,
  livewireIsOffline: false,
  hooks: _HookManager__WEBPACK_IMPORTED_MODULE_1__["default"],
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
      return component.addAction(new _action_event__WEBPACK_IMPORTED_MODULE_0__["default"](event, params));
    });
  },
  componentsListeningForEvent: function componentsListeningForEvent(event) {
    return this.components().filter(function (component) {
      return component.events.includes(event);
    });
  },
  registerHook: function registerHook(name, callback) {
    this.hooks.register(name, callback);
  },
  callHook: function callHook(name) {
    var _this$hooks;

    for (var _len2 = arguments.length, params = new Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
      params[_key2 - 1] = arguments[_key2];
    }

    (_this$hooks = this.hooks).call.apply(_this$hooks, [name].concat(params));
  },
  beforeDomUpdate: function beforeDomUpdate(callback) {
    this.beforeDomUpdateCallback = callback;
  },
  afterDomUpdate: function afterDomUpdate(callback) {
    this.afterDomUpdateCallback = callback;
  },
  removeComponent: function removeComponent(component) {
    // Remove event listeners attached to the DOM.
    component.tearDown(); // Remove the component from the store.

    delete this.componentsById[component.id];
  }
};
/* harmony default export */ __webpack_exports__["default"] = (store);

/***/ }),

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

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

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
  } // Overriding toId() becuase some EventActions don't have an "el"


  _createClass(_default, [{
    key: "toId",
    value: function toId() {
      return btoa(encodeURIComponent(this.type, this.payload.event, JSON.stringify(this.payload.params)));
    }
  }]);

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
    key: "toId",
    value: function toId() {
      return btoa(encodeURIComponent(this.el.el.outerHTML));
    }
  }, {
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

/***/ "./src/js/component/DirtyStates.js":
/*!*****************************************!*\
  !*** ./src/js/component/DirtyStates.js ***!
  \*****************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _dom_dom_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/dom/dom_element */ "./src/js/dom/dom_element.js");
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }



/* harmony default export */ __webpack_exports__["default"] = (function () {
  _Store__WEBPACK_IMPORTED_MODULE_1__["default"].registerHook('componentInitialized', function (component) {
    component.targetedDirtyElsByProperty = {};
    component.genericDirtyEls = [];
    registerListener(component);
  });
  _Store__WEBPACK_IMPORTED_MODULE_1__["default"].registerHook('elementInitialized', function (el, component) {
    if (el.directives.missing('dirty')) return;
    var propertyNames = el.directives.has('target') && el.directives.get('target').value.split(',').map(function (s) {
      return s.trim();
    });
    addDirtyEls(component, el, propertyNames);
  });
  _Store__WEBPACK_IMPORTED_MODULE_1__["default"].registerHook('elementRemoved', function (el, component) {
    // Look through the targeted elements to remove.
    Object.keys(component.targetedDirtyElsByProperty).forEach(function (key) {
      component.targetedDirtyElsByProperty[key] = component.targetedDirtyElsByProperty[key].filter(function (element) {
        return !element.isSameNode(el);
      });
    }); // Look through the global/generic elements for the element to remove.

    component.genericDirtyEls.forEach(function (element, index) {
      if (element.isSameNode(el)) {
        component.genericDirtyEls.splice(index, 1);
      }
    });
  });
});

function addDirtyEls(component, el, targetProperties) {
  if (targetProperties) {
    targetProperties.forEach(function (targetProperty) {
      if (component.targetedDirtyElsByProperty[targetProperty]) {
        component.targetedDirtyElsByProperty[targetProperty].push(el);
      } else {
        component.targetedDirtyElsByProperty[targetProperty] = [el];
      }
    });
  } else {
    component.genericDirtyEls.push(el);
  }
}

function registerListener(component) {
  component.el.addEventListener('input', function (e) {
    var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_0__["default"](e.target);
    var allEls = [];

    if (el.directives.has('model') && component.targetedDirtyElsByProperty[el.directives.get('model').value]) {
      allEls.push.apply(allEls, _toConsumableArray(component.targetedDirtyElsByProperty[el.directives.get('model').value]));
    }

    if (el.directives.has('dirty')) {
      allEls.push.apply(allEls, _toConsumableArray(component.genericDirtyEls.filter(function (dirtyEl) {
        return dirtyEl.directives.get('model').value === el.directives.get('model').value;
      })));
    }

    if (allEls.length < 1) return;

    if (el.directives.missing('model')) {
      console.warn('`wire:model` must be present on any element that uses `wire:dirty` or is a `wire:dirty` target.');
    }

    var isDirty = el.valueFromInput(component) != component.data[el.directives.get('model').value];
    allEls.forEach(function (el) {
      setDirtyState(el, isDirty);
    });
  });
}

function setDirtyState(el, isDirty) {
  var directive = el.directives.get('dirty');

  if (directive.modifiers.includes('class')) {
    var classes = directive.value.split(' ');

    if (directive.modifiers.includes('remove') !== isDirty) {
      var _el$classList;

      (_el$classList = el.classList).add.apply(_el$classList, _toConsumableArray(classes));
    } else {
      var _el$classList2;

      (_el$classList2 = el.classList).remove.apply(_el$classList2, _toConsumableArray(classes));
    }
  } else if (directive.modifiers.includes('attr')) {
    if (directive.modifiers.includes('remove') !== isDirty) {
      el.setAttribute(directive.value, true);
    } else {
      el.removeAttrsibute(directive.value);
    }
  } else if (!el.directives.get('model')) {
    el.el.style.display = isDirty ? 'inline-block' : 'none';
  }
}

/***/ }),

/***/ "./src/js/component/LoadingStates.js":
/*!*******************************************!*\
  !*** ./src/js/component/LoadingStates.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }


/* harmony default export */ __webpack_exports__["default"] = (function () {
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('componentInitialized', function (component) {
    component.targetedLoadingElsByAction = {};
    component.genericLoadingEls = [];
    component.currentlyActiveLoadingEls = [];
  });
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('elementInitialized', function (el, component) {
    if (el.directives.missing('loading')) return;
    var loadingDirectives = el.directives.directives.filter(function (i) {
      return i.type === 'loading';
    });
    loadingDirectives.forEach(function (directive) {
      processLoadingDirective(component, el, directive);
    });
  });
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('messageSent', function (component, message) {
    var actions = message.actionQueue.filter(function (action) {
      return action.type === 'callMethod';
    }).map(function (action) {
      return action.payload.method;
    });
    setLoading(component, actions);
  });
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('messageFailed', function (component) {
    unsetLoading(component);
  });
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('responseReceived', function (component) {
    unsetLoading(component);
  });
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('elementRemoved', function (el, component) {
    removeLoadingEl(component, el);
  });
});

function processLoadingDirective(component, el, directive) {
  var actionNames = false;

  if (el.directives.get('target')) {
    // wire:target overrides any automatic loading scoping we do.
    actionNames = el.directives.get('target').value.split(',').map(function (s) {
      return s.trim();
    });
  } else {
    // If there is no wire:target, let's check for the existance of a wire:click="foo" or something,
    // and automatically scope this loading directive to that action.
    var nonActionLivewireDirectives = ['init', 'model', 'dirty', 'offline', 'target', 'loading', 'poll', 'ignore'];
    actionNames = el.directives.all().filter(function (i) {
      return !nonActionLivewireDirectives.includes(i.type);
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
  allEls.forEach(function (_ref) {
    var el = _ref.el,
        directive = _ref.directive;
    el = el.el; // I'm so sorry @todo

    if (directive.modifiers.includes('class')) {
      // This is because wire:loading.class="border border-red"
      // wouldn't work with classList.add.
      var classes = directive.value.split(' ');

      if (directive.modifiers.includes('remove')) {
        var _el$classList;

        (_el$classList = el.classList).remove.apply(_el$classList, _toConsumableArray(classes));
      } else {
        var _el$classList2;

        (_el$classList2 = el.classList).add.apply(_el$classList2, _toConsumableArray(classes));
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
  component.currentlyActiveLoadingEls = allEls;
}

function unsetLoading(component) {
  component.currentlyActiveLoadingEls.forEach(function (_ref2) {
    var el = _ref2.el,
        directive = _ref2.directive;
    el = el.el; // I'm so sorry @todo

    if (directive.modifiers.includes('class')) {
      var classes = directive.value.split(' ');

      if (directive.modifiers.includes('remove')) {
        var _el$classList3;

        (_el$classList3 = el.classList).add.apply(_el$classList3, _toConsumableArray(classes));
      } else {
        var _el$classList4;

        (_el$classList4 = el.classList).remove.apply(_el$classList4, _toConsumableArray(classes));
      }
    } else if (directive.modifiers.includes('attr')) {
      if (directive.modifiers.includes('remove')) {
        el.setAttribute(directive.value, true);
      } else {
        el.removeAttribute(directive.value);
      }
    } else {
      el.style.display = 'none';
    }
  });
  component.currentlyActiveLoadingEls = [];
}

/***/ }),

/***/ "./src/js/component/OfflineStates.js":
/*!*******************************************!*\
  !*** ./src/js/component/OfflineStates.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }


var offlineEls = [];
/* harmony default export */ __webpack_exports__["default"] = (function () {
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('elementInitialized', function (el) {
    if (el.directives.missing('offline')) return;
    offlineEls.push(el);
  });
  window.addEventListener('offline', function () {
    _Store__WEBPACK_IMPORTED_MODULE_0__["default"].livewireIsOffline = true;
    offlineEls.forEach(function (el) {
      toggleOffline(el, true);
    });
  });
  window.addEventListener('online', function () {
    _Store__WEBPACK_IMPORTED_MODULE_0__["default"].livewireIsOffline = false;
    offlineEls.forEach(function (el) {
      toggleOffline(el, false);
    });
  });
  _Store__WEBPACK_IMPORTED_MODULE_0__["default"].registerHook('elementRemoved', function (el) {
    offlineEls = offlineEls.filter(function (el) {
      return !el.isSameNode(el);
    });
  });
});

function toggleOffline(el, isOffline) {
  var directive = el.directives.get('offline');

  if (directive.modifiers.includes('class')) {
    var classes = directive.value.split(' ');

    if (directive.modifiers.includes('remove') !== isOffline) {
      var _el$rawNode$classList;

      (_el$rawNode$classList = el.rawNode().classList).add.apply(_el$rawNode$classList, _toConsumableArray(classes));
    } else {
      var _el$rawNode$classList2;

      (_el$rawNode$classList2 = el.rawNode().classList).remove.apply(_el$rawNode$classList2, _toConsumableArray(classes));
    }
  } else if (directive.modifiers.includes('attr')) {
    if (directive.modifiers.includes('remove') !== isOffline) {
      el.rawNode().setAttribute(directive.value, true);
    } else {
      el.rawNode().removeAttribute(directive.value);
    }
  } else if (!el.directives.get('model')) {
    el.rawNode().style.display = isOffline ? 'inline-block' : 'none';
  }
}

/***/ }),

/***/ "./src/js/component/Polling.js":
/*!*************************************!*\
  !*** ./src/js/component/Polling.js ***!
  \*************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _action_method__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/action/method */ "./src/js/action/method.js");
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");


/* harmony default export */ __webpack_exports__["default"] = (function () {
  _Store__WEBPACK_IMPORTED_MODULE_1__["default"].registerHook('elementInitialized', function (el, component) {
    if (el.directives.missing('poll')) return;
    fireActionOnInterval(el, component);
  });
});

function fireActionOnInterval(el, component) {
  var directive = el.directives.get('poll');
  var method = directive.method || '$refresh';
  setInterval(function () {
    // Don't poll when the tab is in the background.
    // The "Math.random" business effectivlly prevents 95% of requests
    // from executing. We still want "some" requests to get through.
    if (_Store__WEBPACK_IMPORTED_MODULE_1__["default"].livewireIsInBackground && Math.random() < .95) return; // Don't poll if livewire is offline as well.

    if (_Store__WEBPACK_IMPORTED_MODULE_1__["default"].livewireIsOffline) return;
    component.addAction(new _action_method__WEBPACK_IMPORTED_MODULE_0__["default"](method, directive.params, el));
  }, directive.durationOr(2000));
}

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
  sendMessage: function sendMessage(payload) {
    var _this = this;

    // Forward the query string for the ajax requests.
    fetch("".concat(window.livewire_app_url, "/livewire/message/").concat(payload.name).concat(window.location.search), {
      method: 'POST',
      body: JSON.stringify(payload),
      // This enables "cookies".
      credentials: "same-origin",
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'text/html, application/xhtml+xml',
        'X-CSRF-TOKEN': this.getCSRFToken(),
        'X-Socket-ID': this.getSocketId(),
        'X-Livewire': true
      }
    }).then(function (response) {
      if (response.ok) {
        response.text().then(function (response) {
          if (_this.isOutputFromDump(response)) {
            _this.onError(payload);

            _this.showHtmlModal(response);
          } else {
            _this.onMessage.call(_this, JSON.parse(response));
          }
        });
      } else {
        response.text().then(function (response) {
          _this.onError(payload);

          _this.showHtmlModal(response);
        });
      }
    })["catch"](function () {
      _this.onError(payload);
    });
  },
  isOutputFromDump: function isOutputFromDump(output) {
    return !!output.match(/<script>Sfdump\(".+"\)<\/script>/);
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
  getSocketId: function getSocketId() {
    if (typeof Echo !== 'undefined') {
      return Echo.socketId();
    }
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
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Store */ "./src/js/Store.js");
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
    };

    this.driver.init();
  }

  _createClass(Connection, [{
    key: "onMessage",
    value: function onMessage(payload) {
      if (payload.fromPrefetch) {
        _Store__WEBPACK_IMPORTED_MODULE_1__["default"].findComponent(payload.id).receivePrefetchMessage(payload);
      } else {
        _Store__WEBPACK_IMPORTED_MODULE_1__["default"].findComponent(payload.id).receiveMessage(payload);
        Object(_util__WEBPACK_IMPORTED_MODULE_0__["dispatch"])('livewire:update');
      }
    }
  }, {
    key: "onError",
    value: function onError(payloadThatFailedSending) {
      _Store__WEBPACK_IMPORTED_MODULE_1__["default"].findComponent(payloadThatFailedSending.id).messageSendFailed();
    }
  }, {
    key: "sendMessage",
    value: function sendMessage(message) {
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
        return name.match(new RegExp(prefix + ':'));
      }) // Parse out the type, modifiers, and value from it.
      .map(function (name) {
        var _name$replace$split = name.replace(new RegExp(prefix + ':'), '').split('.'),
            _name$replace$split2 = _toArray(_name$replace$split),
            type = _name$replace$split2[0],
            modifiers = _name$replace$split2.slice(1);

        return new _directive__WEBPACK_IMPORTED_MODULE_0__["default"](type, modifiers, name, _this.el);
      }));
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
/* harmony import */ var get_value__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! get-value */ "./node_modules/get-value/index.js");
/* harmony import */ var get_value__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(get_value__WEBPACK_IMPORTED_MODULE_1__);
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
        this.fadeIn(directive);
        return;
      }

      if (directive.modifiers.includes('slide')) {
        this.slideIn(directive);
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
        this.fadeOut(directive, onDiscarded);
        return false;
      }

      if (directive.modifiers.includes('slide')) {
        this.slideOut(directive, onDiscarded);
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
    key: "fadeIn",
    value: function fadeIn(directive) {
      var _this4 = this;

      this.el.style.opacity = 0;
      this.el.style.transition = "opacity ".concat(directive.durationOr(300) / 1000, "s ease");
      this.nextFrame(function () {
        _this4.el.style.opacity = 1;
      });
    }
  }, {
    key: "slideIn",
    value: function slideIn(directive) {
      var _this5 = this;

      var directions = {
        up: 'translateY(10px)',
        down: 'translateY(-10px)',
        left: 'translateX(-10px)',
        right: 'translateX(10px)'
      };
      this.el.style.opacity = 0;
      this.el.style.transform = directions[directive.cardinalDirectionOr('right')];
      this.el.style.transition = "opacity ".concat(directive.durationOr(300) / 1000, "s ease, transform ").concat(directive.durationOr(300) / 1000, "s ease");
      this.nextFrame(function () {
        _this5.el.style.opacity = 1;
        _this5.el.style.transform = "";
      });
    }
  }, {
    key: "fadeOut",
    value: function fadeOut(directive, onDiscarded) {
      var _this6 = this;

      this.nextFrame(function () {
        _this6.el.style.opacity = 0;
        setTimeout(function () {
          onDiscarded(_this6.el);

          _this6.el.remove();
        }, directive.durationOr(300));
      });
    }
  }, {
    key: "slideOut",
    value: function slideOut(directive, onDiscarded) {
      var _this7 = this;

      var directions = {
        up: 'translateY(10px)',
        down: 'translateY(-10px)',
        left: 'translateX(-10px)',
        right: 'translateX(10px)'
      };
      this.nextFrame(function () {
        _this7.el.style.opacity = 0;
        _this7.el.style.transform = directions[directive.cardinalDirectionOr('right')];
        setTimeout(function () {
          onDiscarded(_this7.el);

          _this7.el.remove();
        }, directive.durationOr(300));
      });
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
    key: "removeAttribute",
    value: function removeAttribute(attribute) {
      return this.el.removeAttribute("".concat(prefix, ":").concat(attribute));
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
    value: function valueFromInput(component) {
      var _this8 = this;

      if (this.el.type === 'checkbox') {
        var modelName = this.directives.get('model').value;
        var modelValue = get_value__WEBPACK_IMPORTED_MODULE_1___default()(component.data, modelName);

        if (Array.isArray(modelValue)) {
          if (this.el.checked) {
            modelValue = modelValue.includes(this.el.value) ? modelValue : modelValue.concat(this.el.value);
          } else {
            modelValue = modelValue.filter(function (item) {
              return item !== _this8.el.value;
            });
          }

          return modelValue;
        }

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
      var modelValue = get_value__WEBPACK_IMPORTED_MODULE_1___default()(component.data, modelString);
      if (modelValue === undefined) return;
      this.setInputValue(modelValue);
    }
  }, {
    key: "setInputValue",
    value: function setInputValue(value) {
      var _this9 = this;

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
        if (Array.isArray(value)) {
          // I'm purposely not using Array.includes here because it's
          // strict, and because of Numeric/String mis-casting, I
          // want the "includes" to be "fuzzy".
          var valueFound = false;
          value.forEach(function (val) {
            if (val == _this9.el.value) {
              valueFound = true;
            }
          });
          this.el.checked = valueFound;
        } else {
          this.el.checked = !!value;
        }
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
      var arrayWrappedValue = [].concat(value).map(function (value) {
        return value + '';
      });
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
    key: "removeEventListener",
    value: function removeEventListener() {
      var _this$el3;

      return (_this$el3 = this.el).removeEventListener.apply(_this$el3, arguments);
    }
  }, {
    key: "querySelector",
    value: function querySelector() {
      var _this$el4;

      return (_this$el4 = this.el).querySelector.apply(_this$el4, arguments);
    }
  }, {
    key: "querySelectorAll",
    value: function querySelectorAll() {
      var _this$el5;

      return (_this$el5 = this.el).querySelectorAll.apply(_this$el5, arguments);
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
 * 3) Tagged other changes with "@livewireModification".
 */




var ELEMENT_NODE = 1;
var DOCUMENT_FRAGMENT_NODE = 11;
var TEXT_NODE = 3;
var COMMENT_NODE = 8;

function noop() {}

function defaultGetNodeKey(node) {
  return node.id;
}

function callHook(hook) {
  if (hook.name !== 'getNodeKey' && hook.name !== 'onBeforeElUpdated') {} // console.log(hook.name, ...params)
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
        _specialElHandlers__WEBPACK_IMPORTED_MODULE_1__["default"].TEXTAREA(fromEl, toEl);
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

              isCompatible = isCompatible !== false && Object(_util__WEBPACK_IMPORTED_MODULE_0__["compareNodeNames"])(curFromNodeChild, curToNodeChild);

              if (isCompatible) {
                // If the two nodes are different, but the next element is an exact match,
                // we can assume that the new node is meant to be inserted, instead of
                // used as a morph target.
                // @livewireUpdate
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
            // Before we just remove the original element, let's see if it's the very next
            // element in the "to" list. If it is, we can assume we can insert the new
            // element before the original one instead of removing it. This is kind of
            // a "look-ahead".
            // @livewireUpdate
            if (curToNodeChild.nextElementSibling && curToNodeChild.nextElementSibling.isEqualNode(curFromNodeChild)) {
              var nodeToBeAdded = curToNodeChild.cloneNode(true);
              fromEl.insertBefore(nodeToBeAdded, curFromNodeChild);
              handleNodeAdded(nodeToBeAdded);
              curToNodeChild = curToNodeChild.nextElementSibling.nextSibling;
              curFromNodeChild = fromNextSibling;
              continue outer;
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


        if (curToNodeKey && (matchingFromEl = fromNodesLookup[curToNodeKey]) && Object(_util__WEBPACK_IMPORTED_MODULE_0__["compareNodeNames"])(matchingFromEl, curToNodeChild)) {
          fromEl.appendChild(matchingFromEl); // MORPH

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
      }

      cleanupFromEl(fromEl, curFromNodeChild, curFromNodeKey);
      var specialElHandler = _specialElHandlers__WEBPACK_IMPORTED_MODULE_1__["default"][fromEl.nodeName];

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
});

/***/ }),

/***/ "./src/js/dom/morphdom/util.js":
/*!*************************************!*\
  !*** ./src/js/dom/morphdom/util.js ***!
  \*************************************/
/*! exports provided: doc, toElement, compareNodeNames, createElementNS, moveChildren */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "doc", function() { return doc; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "toElement", function() { return toElement; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "compareNodeNames", function() { return compareNodeNames; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "createElementNS", function() { return createElementNS; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "moveChildren", function() { return moveChildren; });
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

/***/ }),

/***/ "./src/js/dom/polyfills.js":
/*!*********************************!*\
  !*** ./src/js/dom/polyfills.js ***!
  \*********************************/
/*! exports provided: ArrayFrom, ArrayIncludes, ElementGetAttributeNames, ArrayFlat */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ArrayFrom", function() { return ArrayFrom; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ArrayIncludes", function() { return ArrayIncludes; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ElementGetAttributeNames", function() { return ElementGetAttributeNames; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "ArrayFlat", function() { return ArrayFlat; });
// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/from#Polyfill
function ArrayFrom() {
  if (!Array.from) {
    Array.from = function () {
      var toStr = Object.prototype.toString;

      var isCallable = function isCallable(fn) {
        return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
      };

      var toInteger = function toInteger(value) {
        var number = Number(value);

        if (isNaN(number)) {
          return 0;
        }

        if (number === 0 || !isFinite(number)) {
          return number;
        }

        return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
      };

      var maxSafeInteger = Math.pow(2, 53) - 1;

      var toLength = function toLength(value) {
        var len = toInteger(value);
        return Math.min(Math.max(len, 0), maxSafeInteger);
      }; // The length property of the from method is 1.


      return function from(arrayLike
      /*, mapFn, thisArg */
      ) {
        // 1. Let C be the this value.
        var C = this; // 2. Let items be ToObject(arrayLike).

        var items = Object(arrayLike); // 3. ReturnIfAbrupt(items).

        if (arrayLike == null) {
          throw new TypeError('Array.from requires an array-like object - not null or undefined');
        } // 4. If mapfn is undefined, then let mapping be false.


        var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
        var T;

        if (typeof mapFn !== 'undefined') {
          // 5. else
          // 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
          if (!isCallable(mapFn)) {
            throw new TypeError('Array.from: when provided, the second argument must be a function');
          } // 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.


          if (arguments.length > 2) {
            T = arguments[2];
          }
        } // 10. Let lenValue be Get(items, "length").
        // 11. Let len be ToLength(lenValue).


        var len = toLength(items.length); // 13. If IsConstructor(C) is true, then
        // 13. a. Let A be the result of calling the [[Construct]] internal method
        // of C with an argument list containing the single item len.
        // 14. a. Else, Let A be ArrayCreate(len).

        var A = isCallable(C) ? Object(new C(len)) : new Array(len); // 16. Let k be 0.

        var k = 0; // 17. Repeat, while k < len… (also steps a - h)

        var kValue;

        while (k < len) {
          kValue = items[k];

          if (mapFn) {
            A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
          } else {
            A[k] = kValue;
          }

          k += 1;
        } // 18. Let putStatus be Put(A, "length", len, true).


        A.length = len; // 20. Return A.

        return A;
      };
    }();
  }
} // https://stackoverflow.com/questions/53308396/how-to-polyfill-array-prototype-includes-for-ie8

function ArrayIncludes() {
  if (!Array.prototype.includes) {
    //or use Object.defineProperty
    Array.prototype.includes = function (search) {
      return !!~this.indexOf(search);
    };
  }
} // https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttributeNames#Polyfill

function ElementGetAttributeNames() {
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
} // https://raw.githubusercontent.com/jonathantneal/array-flat-polyfill/master/src/polyfill-flat.js

function ArrayFlat() {
  if (!Array.prototype.flat) {
    Object.defineProperty(Array.prototype, 'flat', {
      configurable: true,
      value: function flat() {
        var depth = isNaN(arguments[0]) ? 1 : Number(arguments[0]);
        return depth ? Array.prototype.reduce.call(this, function (acc, cur) {
          if (Array.isArray(cur)) {
            acc.push.apply(acc, flat.call(cur, depth - 1));
          } else {
            acc.push(cur);
          }

          return acc;
        }, []) : Array.prototype.slice.call(this);
      },
      writable: true
    });
  }
} // https://tc39.github.io/ecma262/#sec-array.prototype.find

if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, 'find', {
    value: function value(predicate) {
      // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this); // 2. Let len be ? ToLength(? Get(O, "length")).

      var len = o.length >>> 0; // 3. If IsCallable(predicate) is false, throw a TypeError exception.

      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
      } // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.


      var thisArg = arguments[1]; // 5. Let k be 0.

      var k = 0; // 6. Repeat, while k < len

      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return kValue.
        var kValue = o[k];

        if (predicate.call(thisArg, kValue, k, o)) {
          return kValue;
        } // e. Increase k by 1.


        k++;
      } // 7. Return undefined.


      return undefined;
    },
    configurable: true,
    writable: true
  });
}
/**
 * @see https://dom.spec.whatwg.org/#interface-element
 * @see https://developer.mozilla.org/docs/Web/API/Element/matches#Polyfill
 * @see https://gist.github.com/jonathantneal/3062955
 * @see https://github.com/jonathantneal/closest
 */


(function (global) {
  var Element;
  var ElementPrototype;
  var matches;

  if (Element = global.Element) {
    ElementPrototype = Element.prototype;
    /**
         * @see https://dom.spec.whatwg.org/#dom-element-matches
         */

    if (!(matches = ElementPrototype.matches)) {
      if (matches = ElementPrototype.matchesSelector || ElementPrototype.mozMatchesSelector || ElementPrototype.msMatchesSelector || ElementPrototype.oMatchesSelector || ElementPrototype.webkitMatchesSelector || ElementPrototype.querySelectorAll && function matches(selectors) {
        var element = this;
        var nodeList = (element.parentNode || element.document || element.ownerDocument).querySelectorAll(selectors);
        var index = nodeList.length;

        while (--index >= 0 && nodeList.item(index) !== element) {}

        return index > -1;
      }) {
        ElementPrototype.matches = matches;
      }
    }
    /**
         * @see https://dom.spec.whatwg.org/#dom-element-closest
         */


    if (!ElementPrototype.closest && matches) {
      ElementPrototype.closest = function closest(selectors) {
        var element = this;

        while (element) {
          if (element.nodeType === 1 && element.matches(selectors)) {
            return element;
          }

          element = element.parentNode;
        }

        return null;
      };
    }
  }
})(Function('return this')());

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
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
/* harmony import */ var _dom_dom__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/dom/dom */ "./src/js/dom/dom.js");
/* harmony import */ var _Component_index__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/Component/index */ "./src/js/Component/index.js");
/* harmony import */ var _connection__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/connection */ "./src/js/connection/index.js");
/* harmony import */ var _connection_drivers__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/connection/drivers */ "./src/js/connection/drivers/index.js");
/* harmony import */ var _dom_polyfills__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @/dom/polyfills */ "./src/js/dom/polyfills.js");
/* harmony import */ var whatwg_fetch__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! whatwg-fetch */ "./node_modules/whatwg-fetch/fetch.js");
/* harmony import */ var promise_polyfill_src_polyfill__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! promise-polyfill/src/polyfill */ "./node_modules/promise-polyfill/src/polyfill.js");
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./util */ "./src/js/util/index.js");
/* harmony import */ var _component_LoadingStates__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! @/component/LoadingStates */ "./src/js/component/LoadingStates.js");
/* harmony import */ var _component_DirtyStates__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! @/component/DirtyStates */ "./src/js/component/DirtyStates.js");
/* harmony import */ var _component_OfflineStates__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! @/component/OfflineStates */ "./src/js/component/OfflineStates.js");
/* harmony import */ var _component_Polling__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! @/component/Polling */ "./src/js/component/Polling.js");
function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }















var Livewire =
/*#__PURE__*/
function () {
  function Livewire() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

    _classCallCheck(this, Livewire);

    var defaults = {
      driver: 'http'
    };
    options = Object.assign({}, defaults, options);
    var driver = _typeof(options.driver) === 'object' ? options.driver : _connection_drivers__WEBPACK_IMPORTED_MODULE_4__["default"][options.driver];
    this.connection = new _connection__WEBPACK_IMPORTED_MODULE_3__["default"](driver);
    this.components = _Store__WEBPACK_IMPORTED_MODULE_0__["default"];

    this.onLoadCallback = function () {};

    this.activatePolyfills();
  }

  _createClass(Livewire, [{
    key: "find",
    value: function find(componentId) {
      return this.components.componentsById[componentId];
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
    key: "activatePolyfills",
    value: function activatePolyfills() {
      Object(_dom_polyfills__WEBPACK_IMPORTED_MODULE_5__["ArrayFlat"])();
      Object(_dom_polyfills__WEBPACK_IMPORTED_MODULE_5__["ArrayFrom"])();
      Object(_dom_polyfills__WEBPACK_IMPORTED_MODULE_5__["ArrayIncludes"])();
      Object(_dom_polyfills__WEBPACK_IMPORTED_MODULE_5__["ElementGetAttributeNames"])();
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

      _dom_dom__WEBPACK_IMPORTED_MODULE_1__["default"].rootComponentElementsWithNoParents().forEach(function (el) {
        _this.components.addComponent(new _Component_index__WEBPACK_IMPORTED_MODULE_2__["default"](el, _this.connection));
      });
      this.onLoadCallback();
      Object(_util__WEBPACK_IMPORTED_MODULE_8__["dispatch"])('livewire:load');
      window.addEventListener('beforeunload', function () {
        _this.components.tearDownComponents();
      });
      document.addEventListener('visibilitychange', function () {
        _this.components.livewireIsInBackground = document.hidden;
      }, false);
    }
  }, {
    key: "rescan",
    value: function rescan() {
      var _this2 = this;

      _dom_dom__WEBPACK_IMPORTED_MODULE_1__["default"].rootComponentElementsWithNoParents().forEach(function (el) {
        var componentId = el.getAttribute('id');
        if (_this2.components.hasComponent(componentId)) return;

        _this2.components.addComponent(new _Component_index__WEBPACK_IMPORTED_MODULE_2__["default"](el, _this2.connection));
      });
    }
  }, {
    key: "beforeDomUpdate",
    value: function beforeDomUpdate(callback) {
      this.components.beforeDomUpdate(callback);
    }
  }, {
    key: "afterDomUpdate",
    value: function afterDomUpdate(callback) {
      this.components.afterDomUpdate(callback);
    }
  }, {
    key: "plugin",
    value: function plugin(callable) {
      callable(this);
    }
  }]);

  return Livewire;
}();

if (!window.Livewire) {
  window.Livewire = Livewire;
}

Object(_component_LoadingStates__WEBPACK_IMPORTED_MODULE_9__["default"])();
Object(_component_DirtyStates__WEBPACK_IMPORTED_MODULE_10__["default"])();
Object(_component_OfflineStates__WEBPACK_IMPORTED_MODULE_11__["default"])();
Object(_component_Polling__WEBPACK_IMPORTED_MODULE_12__["default"])();
Object(_util__WEBPACK_IMPORTED_MODULE_8__["dispatch"])('livewire:available');
/* harmony default export */ __webpack_exports__["default"] = (Livewire);

/***/ }),

/***/ "./src/js/node_initializer.js":
/*!************************************!*\
  !*** ./src/js/node_initializer.js ***!
  \************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _util__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @/util */ "./src/js/util/index.js");
/* harmony import */ var _action_model__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @/action/model */ "./src/js/action/model.js");
/* harmony import */ var _action_method__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @/action/method */ "./src/js/action/method.js");
/* harmony import */ var _dom_dom_element__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @/dom/dom_element */ "./src/js/dom/dom_element.js");
/* harmony import */ var _Store__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @/Store */ "./src/js/Store.js");
function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance"); }

function _iterableToArray(iter) { if (Symbol.iterator in Object(iter) || Object.prototype.toString.call(iter) === "[object Arguments]") return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = new Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } }






/* harmony default export */ __webpack_exports__["default"] = ({
  initialize: function initialize(el, component) {
    var _this = this;

    el.directives.all().forEach(function (directive) {
      switch (directive.type) {
        case 'init':
          _this.fireActionRightAway(el, directive, component);

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
    _Store__WEBPACK_IMPORTED_MODULE_4__["default"].callHook('elementInitialized', el, component);
  },
  fireActionRightAway: function fireActionRightAway(el, directive, component) {
    var method = directive.value ? directive.method : '$refresh';
    component.addAction(new _action_method__WEBPACK_IMPORTED_MODULE_2__["default"](method, directive.params, el));
  },
  attachModelListener: function attachModelListener(el, directive, component) {
    // This is used by morphdom: morphdom.js:391
    el.el.isLivewireModel = true;
    var isLazy = directive.modifiers.includes('lazy');

    var debounceIf = function debounceIf(condition, callback, time) {
      return condition ? component.modelSyncDebounce(callback, time) : callback;
    };

    var hasDebounceModifier = directive.modifiers.includes('debounce'); // If it's a Vue component, listen for Vue input event emission.

    if (el.isVueComponent()) {
      el.asVueComponent().$on('input', debounceIf(hasDebounceModifier, function (e) {
        var model = directive.value;
        var value = e;
        component.addAction(new _action_model__WEBPACK_IMPORTED_MODULE_1__["default"](model, value, el));
      }, directive.durationOr(150)));
    } else {
      var defaultEventType = el.isTextInput() ? 'input' : 'change'; // If it's a text input and not .lazy, debounce, otherwise fire immediately.

      var event = isLazy ? 'change' : defaultEventType;
      var handler = debounceIf(hasDebounceModifier || el.isTextInput() && !isLazy, function (e) {
        var model = directive.value;
        var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_3__["default"](e.target);
        var value = el.valueFromInput(component);
        component.addAction(new _action_model__WEBPACK_IMPORTED_MODULE_1__["default"](model, value, el));
      }, directive.durationOr(150));
      el.addEventListener(event, handler);
      component.addListenerForTeardown(function () {
        el.removeEventListener(event, handler);
      });
    }
  },
  attachDomListener: function attachDomListener(el, directive, component) {
    switch (directive.type) {
      case 'keydown':
      case 'keyup':
        this.attachListener(el, directive, component, function (e) {
          // Only handle listener if no, or matching key modifiers are passed.
          return directive.modifiers.length === 0 || directive.modifiers.includes(Object(_util__WEBPACK_IMPORTED_MODULE_0__["kebabCase"])(e.key));
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
        component.addPrefetchAction(new _action_method__WEBPACK_IMPORTED_MODULE_2__["default"](directive.method, directive.params, el));
      });
    }

    var event = directive.type;

    var handler = function handler(e) {
      if (callback && callback(e) === false) {
        return;
      }

      component.callAfterModelDebounce(function () {
        var el = new _dom_dom_element__WEBPACK_IMPORTED_MODULE_3__["default"](e.target);
        directive.setEventContext(e); // This is outside the conditional below so "wire:click.prevent" without
        // a value still prevents default.

        _this2.preventAndStop(e, directive.modifiers);

        var method = directive.method;
        var params = directive.params; // Check for global event emission.

        if (method === '$emit') {
          var _component$scopedList;

          (_component$scopedList = component.scopedListeners).call.apply(_component$scopedList, _toConsumableArray(params));

          _Store__WEBPACK_IMPORTED_MODULE_4__["default"].emit.apply(_Store__WEBPACK_IMPORTED_MODULE_4__["default"], _toConsumableArray(params));
          return;
        }

        if (directive.value) {
          component.addAction(new _action_method__WEBPACK_IMPORTED_MODULE_2__["default"](method, params, el));
        }
      });
    };

    el.addEventListener(event, handler);
    component.addListenerForTeardown(function () {
      el.removeEventListener(event, handler);
    });
  },
  preventAndStop: function preventAndStop(event, modifiers) {
    modifiers.includes('prevent') && event.preventDefault();
    modifiers.includes('stop') && event.stopPropagation();
  }
});

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
/*! exports provided: kebabCase, tap, debounceWithFiringOnBothEnds, debounce, walk, dispatch */
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




function kebabCase(subject) {
  return subject.replace(/([a-z])([A-Z])/g, '$1-$2').replace(/[_\s]/, '-').toLowerCase();
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