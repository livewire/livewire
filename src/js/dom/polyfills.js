// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/from#Polyfill
export function ArrayFrom() {
    if (!Array.from) {
        Array.from = (function () {
            var toStr = Object.prototype.toString;
            var isCallable = function (fn) {
                return typeof fn === 'function' || toStr.call(fn) === '[object Function]';
            };
            var toInteger = function (value) {
                var number = Number(value);
                if (isNaN(number)) { return 0; }
                if (number === 0 || !isFinite(number)) { return number; }
                return (number > 0 ? 1 : -1) * Math.floor(Math.abs(number));
            };
            var maxSafeInteger = Math.pow(2, 53) - 1;
            var toLength = function (value) {
                var len = toInteger(value);
                return Math.min(Math.max(len, 0), maxSafeInteger);
            };

            // The length property of the from method is 1.
            return function from(arrayLike/*, mapFn, thisArg */) {
                // 1. Let C be the this value.
                var C = this;

                // 2. Let items be ToObject(arrayLike).
                var items = Object(arrayLike);

                // 3. ReturnIfAbrupt(items).
                if (arrayLike == null) {
                    throw new TypeError('Array.from requires an array-like object - not null or undefined');
                }

                // 4. If mapfn is undefined, then let mapping be false.
                var mapFn = arguments.length > 1 ? arguments[1] : void undefined;
                var T;
                if (typeof mapFn !== 'undefined') {
                    // 5. else
                    // 5. a If IsCallable(mapfn) is false, throw a TypeError exception.
                    if (!isCallable(mapFn)) {
                        throw new TypeError('Array.from: when provided, the second argument must be a function');
                    }

                    // 5. b. If thisArg was supplied, let T be thisArg; else let T be undefined.
                    if (arguments.length > 2) {
                        T = arguments[2];
                    }
                }

                // 10. Let lenValue be Get(items, "length").
                // 11. Let len be ToLength(lenValue).
                var len = toLength(items.length);

                // 13. If IsConstructor(C) is true, then
                // 13. a. Let A be the result of calling the [[Construct]] internal method
                // of C with an argument list containing the single item len.
                // 14. a. Else, Let A be ArrayCreate(len).
                var A = isCallable(C) ? Object(new C(len)) : new Array(len);

                // 16. Let k be 0.
                var k = 0;
                // 17. Repeat, while k < len… (also steps a - h)
                var kValue;
                while (k < len) {
                    kValue = items[k];
                    if (mapFn) {
                        A[k] = typeof T === 'undefined' ? mapFn(kValue, k) : mapFn.call(T, kValue, k);
                    } else {
                        A[k] = kValue;
                    }
                    k += 1;
                }
                // 18. Let putStatus be Put(A, "length", len, true).
                A.length = len;
                // 20. Return A.
                return A;
            };
        }());
    }
}


// https://stackoverflow.com/questions/53308396/how-to-polyfill-array-prototype-includes-for-ie8
export function ArrayIncludes() {
    if (!Array.prototype.includes) {
        //or use Object.defineProperty
        Array.prototype.includes = function (search) {
            return !!~this.indexOf(search);
        }
    }
}


// https://developer.mozilla.org/en-US/docs/Web/API/Element/getAttributeNames#Polyfill
export function ElementGetAttributeNames() {
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
}

// https://raw.githubusercontent.com/jonathantneal/array-flat-polyfill/master/src/polyfill-flat.js
export function ArrayFlat() {
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
}

// https://tc39.github.io/ecma262/#sec-array.prototype.find
if (!Array.prototype.find) {
  Object.defineProperty(Array.prototype, 'find', {
    value: function(predicate) {
     // 1. Let O be ? ToObject(this value).
      if (this == null) {
        throw new TypeError('"this" is null or not defined');
      }

      var o = Object(this);

      // 2. Let len be ? ToLength(? Get(O, "length")).
      var len = o.length >>> 0;

      // 3. If IsCallable(predicate) is false, throw a TypeError exception.
      if (typeof predicate !== 'function') {
        throw new TypeError('predicate must be a function');
      }

      // 4. If thisArg was supplied, let T be thisArg; else let T be undefined.
      var thisArg = arguments[1];

      // 5. Let k be 0.
      var k = 0;

      // 6. Repeat, while k < len
      while (k < len) {
        // a. Let Pk be ! ToString(k).
        // b. Let kValue be ? Get(O, Pk).
        // c. Let testResult be ToBoolean(? Call(predicate, T, « kValue, k, O »)).
        // d. If testResult is true, return kValue.
        var kValue = o[k];
        if (predicate.call(thisArg, kValue, k, o)) {
          return kValue;
        }
        // e. Increase k by 1.
        k++;
      }

      // 7. Return undefined.
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
(function(global){
var Element;
var ElementPrototype;
var matches;
if (Element = global.Element) {
    ElementPrototype = Element.prototype;
/**
     * @see https://dom.spec.whatwg.org/#dom-element-matches
     */
if (!(matches = ElementPrototype.matches)) {
if ((
        matches = ElementPrototype.matchesSelector ||
ElementPrototype.mozMatchesSelector ||
ElementPrototype.msMatchesSelector ||
ElementPrototype.oMatchesSelector ||
ElementPrototype.webkitMatchesSelector ||
          (ElementPrototype.querySelectorAll && function matches(selectors) {
var element = this;
var nodeList = (element.parentNode || element.document || element.ownerDocument).querySelectorAll(selectors);
var index = nodeList.length;
while (--index >= 0 && nodeList.item(index) !== element) {}
return index > -1;
          })
      )) {
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
}(Function('return this')()));
