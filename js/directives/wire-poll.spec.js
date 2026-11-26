import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { poll as createPoll, backoffFor, extractIntervalFrom, extractBackoffLimitFrom } from './wire-poll'

// Polls with the same interval share one clock across the whole module, so every
// poll a test starts has to stop before the next test swaps the fake timers...
let polls = []

function poll(callback, interval) {
    let created = createPoll(callback, interval)

    polls.push(created)

    return created
}

function stopEveryPoll() {
    polls.forEach(poll => poll.stop())

    polls = []
}

describe('wire:poll.backoff', () => {
    beforeEach(() => vi.useFakeTimers({ now: 0 }))

    afterEach(() => {
        stopEveryPoll()

        vi.useRealTimers()
    })

    it('doubles the wait after each response that matches the previous one', () => {
        let backedOff = pollWithBackoff(5000, 40000, ({ succeed }) => succeed('nothing new'))

        vi.advanceTimersByTime(162000)

        expect(backedOff.sentAt).toEqual([5, 10, 20, 40, 80, 120, 160])
    })

    it('never waits longer than the limit', () => {
        let backedOff = pollWithBackoff(5000, 30000, ({ succeed }) => succeed('nothing new'))

        vi.advanceTimersByTime(162000)

        expect(backedOff.sentAt).toEqual([5, 10, 20, 40, 70, 100, 130, 160])
    })

    it('rounds the limit down to a whole number of intervals', () => {
        let backedOff = pollWithBackoff(5000, 32000, ({ succeed }) => succeed('nothing new'))

        vi.advanceTimersByTime(162000)

        expect(backedOff.sentAt).toEqual([5, 10, 20, 40, 70, 100, 130, 160])
    })

    it('never slows down when the limit is shorter than the interval', () => {
        let backedOff = pollWithBackoff(5000, 1000, ({ succeed }) => succeed('nothing new'))

        vi.advanceTimersByTime(32000)

        expect(backedOff.sentAt).toEqual([5, 10, 15, 20, 25, 30])
    })

    it('goes back to the interval when a response brings something new', () => {
        let backedOff = pollWithBackoff(5000, 30000, ({ now, succeed }) => succeed(now < 70000 ? 'old' : 'new'))

        vi.advanceTimersByTime(162000)

        expect(backedOff.sentAt).toEqual([5, 10, 20, 40, 70, 75, 85, 105, 135])
    })

    it('goes back to the interval when anything else updates the component', () => {
        let backedOff = pollWithBackoff(5000, 30000, ({ succeed }) => succeed('nothing new'))

        setTimeout(() => backedOff.backoff.componentWasUpdated('nothing new'), 62000)

        vi.advanceTimersByTime(162000)

        expect(backedOff.sentAt).toEqual([5, 10, 20, 40, 65, 75, 95, 125, 155])
    })

    it('doubles the wait after each failed poll', () => {
        let backedOff = pollWithBackoff(5000, 40000, ({ now, succeed, fail }) => {
            (now >= 15000 && now < 85000) ? fail() : succeed(now)
        })

        vi.advanceTimersByTime(102000)

        expect(backedOff.sentAt).toEqual([5, 10, 15, 25, 45, 85, 90, 95, 100])
    })

    it('keeps a slowed down poll on the clock it shares with other polls', () => {
        let ticks = []
        let fast = poll(() => ticks.push(Date.now()), 5000)
        let slow = pollWithBackoff(5000, 40000, ({ succeed }) => succeed('nothing new'))

        fast.start()

        vi.advanceTimersByTime(162000)

        slow.sentAt.forEach(second => expect(ticks).toContain(second * 1000))
    })
})

describe('wire:poll stopping', () => {
    beforeEach(() => vi.useFakeTimers({ now: 0 }))

    afterEach(() => {
        stopEveryPoll()

        vi.useRealTimers()
    })

    it('runs the stop callbacks once when stopped by hand', () => {
        let callback = vi.fn()
        let { start, stop, onStop } = poll(() => {}, 1000)

        onStop(callback)
        start()
        stop()
        stop()

        expect(callback).toHaveBeenCalledTimes(1)
    })

    it('runs the stop callbacks when a stop condition is met', () => {
        let callback = vi.fn()
        let isDisconnected = false
        let { start, onStop, stopWhen } = poll(() => {}, 1000)

        onStop(callback)
        stopWhen(() => isDisconnected)
        start()

        vi.advanceTimersByTime(1000)
        expect(callback).not.toHaveBeenCalled()

        isDisconnected = true
        vi.advanceTimersByTime(3000)
        expect(callback).toHaveBeenCalledTimes(1)
    })
})

describe('wire:poll durations', () => {
    it('reads the interval from any duration that does not follow backoff', () => {
        expect(extractIntervalFrom(['5s'], 2000)).toBe(5000)
        expect(extractIntervalFrom(['5s', 'backoff', '30s'], 2000)).toBe(5000)
        expect(extractIntervalFrom(['backoff', '30s', '5s'], 2000)).toBe(5000)
        expect(extractIntervalFrom(['5s', 'backoff', '500ms'], 2000)).toBe(5000)
        expect(extractIntervalFrom(['backoff', '30s'], 2000)).toBe(2000)
        expect(extractIntervalFrom(['backoff', 'visible'], 2000)).toBe(2000)
    })

    it('reads the limit from the duration right after backoff', () => {
        expect(extractBackoffLimitFrom(['5s', 'backoff', '30s'], 40000)).toBe(30000)
        expect(extractBackoffLimitFrom(['5s', 'backoff', '1m'], 40000)).toBe(60000)
        expect(extractBackoffLimitFrom(['5s', 'backoff', '500ms'], 40000)).toBe(500)
        expect(extractBackoffLimitFrom(['5s', 'backoff'], 40000)).toBe(40000)
        expect(extractBackoffLimitFrom(['5s', 'backoff', 'visible', '30s'], 40000)).toBe(40000)
        expect(extractBackoffLimitFrom(['5s', '30s'], 40000)).toBe(40000)
    })
})

// Wires a backoff into a poll the way the directive does, with each send answered
// on the spot by `respond`. Records the second at which each poll was sent...
function pollWithBackoff(interval, limit, respond) {
    let sentAt = []
    let backoff = backoffFor(interval, limit)

    let { start, pauseWhile } = poll(() => {
        backoff.pollWasSent()

        sentAt.push(Date.now() / 1000)

        respond({
            now: Date.now(),
            succeed: response => backoff.pollSucceeded(response),
            fail: () => backoff.pollFailed(),
        })
    }, interval)

    pauseWhile(() => backoff.isWaiting())

    start()

    return { sentAt, backoff }
}
