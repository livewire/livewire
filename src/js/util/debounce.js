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

export function debounceWithFiringOnBothEnds(func, wait) {
    var timeout;
    var timesInterupted = 0;

	return function() {
        var context = this, args = arguments;

        var callNow = ! timeout;

        if (timeout) {
            clearTimeout(timeout);
            timesInterupted++
        }

        timeout = setTimeout(function () {
            timeout = null;
            if (timesInterupted > 0) {
                func.apply(context, args);
                timesInterupted = 0
            }
        }, wait);

		if (callNow) {
            func.apply(context, args);
        }
	};
};

export function debounce(func, wait, immediate) {
    var timeout
    return function () {
        var context = this, args = arguments
        var later = function () {
            timeout = null
            if (!immediate) func.apply(context, args)
        }
        var callNow = immediate && !timeout
        clearTimeout(timeout)
        timeout = setTimeout(later, wait)
        if (callNow) func.apply(context, args)
    }
}
