import { isolateRequestsWhen } from "@/request"
import { directive, getDirectives } from "@/directives"
import Alpine from 'alpinejs'

directive('poll', ({ el, directive, component }) => {
    let interval = extractDurationFrom(directive.modifiers, 2000)

    let isolated = directive.modifiers.includes('isolate')

    let { start, pauseWhile, throttleWhile, stopWhen } = poll(() => {
        console.log('trying to poll')
        isolateRequestsWhen(isolated, () => {
            triggerComponentRequest(el, directive)
        })
    }, interval)

    // We have 3 components polling
    // one is isolated (A) the rest are not (B, C)
    // Component's A & (B/C) all send at first
    // If the (B/C) request takes too long, component A's polling is held up

    // Alternatively:
    // We have 3 components polling
    // one is isolated (C) the rest are not (A, B)
    // Component's (A/B) & C all send at first
    // If the (A/B) request takes too long, component C spawns an entirely new request for A/B while the other one is held up

    start()

    throttleWhile(() => theTabIsInTheBackground() && theDirectiveIsMissingKeepAlive(directive))
    pauseWhile(() => theDirectiveHasVisible(directive) && theElementIsNotInTheViewport(el))
    pauseWhile(() => theDirectiveIsOffTheElement(el))
    pauseWhile(() => livewireIsOffline())
    stopWhen(() => theElementIsDisconnected(el))
})

function triggerComponentRequest(el, directive) {
    Alpine.evaluate(el,
        directive.expression ? '$wire.' + directive.expression : '$wire.$commit()'
    )
}

function poll(callback, interval = 2000) {
    let pauseConditions = []
    let throttleConditions = []
    let stopConditions = []

    return {
        start() {
            let clear = syncronizedInterval(interval, () => {
                if (stopConditions.some(i => i())) return clear()
                if (pauseConditions.some(i => i())) return
                if (throttleConditions.some(i => i()) && Math.random() < .95) return

                callback()
            })
        },
        pauseWhile(condition) {
            pauseConditions.push(condition)
        },
        throttleWhile(condition) {
            throttleConditions.push(condition)
        },
        stopWhen(condition) {
            stopConditions.push(condition)
        }
    }
}

let clocks = []

function syncronizedInterval(ms, callback) {
    if (! clocks[ms]) {
        let clock = {
            timer: setInterval(() => clock.callbacks.forEach(i => i()), ms),
            callbacks: new Set,
        }

        clocks[ms] = clock
    }

    clocks[ms].callbacks.add(callback)

    return () => {
        clocks[ms].callbacks.delete(callback)

        if (clocks[ms].callbacks.size === 0) {
            clearInterval(clocks[ms].timer)
            delete clocks[ms]
        }
    }
}

let isOffline = false

window.addEventListener('offline', () => isOffline = true)
window.addEventListener('online', () => isOffline = false)

function livewireIsOffline() {
    return isOffline
}

let inBackground = false

document.addEventListener('visibilitychange', () => { inBackground = document.hidden }, false)

function theTabIsInTheBackground() {
    return inBackground
}

function theDirectiveIsOffTheElement(el) {
    return ! getDirectives(el).has('poll')
}

function theDirectiveIsMissingKeepAlive(directive) {
    return ! directive.modifiers.includes('keep-alive')
}

function theDirectiveHasVisible(directive) {
    return directive.modifiers.includes('visible')
}

function theElementIsNotInTheViewport(el) {
    let bounding = el.getBoundingClientRect()

    return ! (
        bounding.top < (window.innerHeight || document.documentElement.clientHeight) &&
        bounding.left < (window.innerWidth || document.documentElement.clientWidth) &&
        bounding.bottom > 0 &&
        bounding.right > 0
    )
}

function theElementIsDisconnected(el) {
    return el.isConnected === false
}

function extractDurationFrom(modifiers, defaultDuration) {
    let durationInMilliSeconds
    let durationInMilliSecondsString = modifiers.find(mod => mod.match(/([0-9]+)ms/))
    let durationInSecondsString = modifiers.find(mod => mod.match(/([0-9]+)s/))

    if (durationInMilliSecondsString) {
        durationInMilliSeconds = Number(durationInMilliSecondsString.replace('ms', ''))
    } else if (durationInSecondsString) {
        durationInMilliSeconds = Number(durationInSecondsString.replace('s', '')) * 1000
    }

    return durationInMilliSeconds || defaultDuration
}
