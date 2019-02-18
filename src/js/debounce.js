import renameme from './renameme.js'

// This is kindof like a normal debouncer, except it behaves like both "immediate" and
// "non-immediate". I'll try to graph the differences:
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

export default function debounce(func, wait) {

    var timeout;
    var timesInterupted = 0;
	return function() {
        var context = this, args = arguments;

        var callNow = !timeout;

        if (timeout) {
            clearTimeout(timeout);
            timesInterupted++
        }

        timeout = setTimeout(function () {
            timeout = null;
            renameme.timeout = 0
            if (timesInterupted > 0) {
                func.apply(context, args);
                timesInterupted = 0
            }
        }, wait);
        renameme.timeout = wait

		if (callNow) {
            func.apply(context, args);
        }
	};
};
