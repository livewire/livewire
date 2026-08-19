import { afterEach, describe, expect, it, vi } from 'vitest'
import { getActiveNavigation, isNavigating, onNavigationStart, startNavigation } from './navigation'

describe('Navigation lifecycle', () => {
    afterEach(() => {
        getActiveNavigation()?.cancel()
    })

    it('tracks the active navigation', () => {
        expect(isNavigating()).toBe(false)

        let navigation = startNavigation()

        expect(getActiveNavigation()).toBe(navigation)
        expect(isNavigating()).toBe(true)
    })

    it('notifies listeners when a navigation starts', () => {
        let callback = vi.fn()
        let removeListener = onNavigationStart(callback)

        let navigation = startNavigation()

        expect(callback).toHaveBeenCalledOnce()
        expect(callback).toHaveBeenCalledWith(navigation)

        removeListener()
    })

    it('stays active when the destination is ready', () => {
        let navigation = startNavigation()
        let callback = vi.fn()

        navigation.onReady(callback)
        navigation.ready()

        expect(callback).toHaveBeenCalledOnce()
        expect(getActiveNavigation()).toBe(navigation)
        expect(isNavigating()).toBe(true)
    })

    it('clears when the navigation is cancelled', () => {
        let navigation = startNavigation()
        let callback = vi.fn()

        navigation.onCancelled(callback)
        navigation.cancel()

        expect(callback).toHaveBeenCalledOnce()
        expect(getActiveNavigation()).toBeUndefined()
        expect(isNavigating()).toBe(false)
    })

    it('clears when the navigation is finished', () => {
        let navigation = startNavigation()
        let callback = vi.fn()

        navigation.onFinished(callback)
        navigation.finish()

        expect(callback).toHaveBeenCalledOnce()
        expect(getActiveNavigation()).toBeUndefined()
        expect(isNavigating()).toBe(false)
    })

    it('cancels the previous navigation when another starts', () => {
        let first = startNavigation()
        let callback = vi.fn()

        first.onCancelled(callback)

        let second = startNavigation()

        expect(callback).toHaveBeenCalledOnce()
        expect(getActiveNavigation()).toBe(second)
    })

    it('immediately invokes listeners for transitions that already happened', () => {
        let navigation = startNavigation()
        let ready = vi.fn()

        navigation.ready()
        navigation.onReady(ready)

        expect(ready).toHaveBeenCalledOnce()

        let cancelledNavigation = startNavigation()
        let cancelled = vi.fn()

        cancelledNavigation.cancel()
        cancelledNavigation.onCancelled(cancelled)

        expect(cancelled).toHaveBeenCalledOnce()
    })

    it('settles the destination when it becomes ready', () => {
        let navigation = startNavigation()
        let state
        let callback = vi.fn(() => state = navigation.state)

        navigation.onDestinationSettled(callback)
        navigation.ready()
        navigation.cancel()

        expect(callback).toHaveBeenCalledOnce()
        expect(callback).toHaveBeenCalledWith()
        expect(state).toBe('ready')
    })

    it('settles the destination when navigation is cancelled', () => {
        let navigation = startNavigation()
        let state
        let callback = vi.fn(() => state = navigation.state)

        navigation.onDestinationSettled(callback)
        navigation.cancel()
        navigation.ready()

        expect(callback).toHaveBeenCalledOnce()
        expect(callback).toHaveBeenCalledWith()
        expect(state).toBe('cancelled')
    })

    it('allows listeners to be removed', () => {
        let navigation = startNavigation()
        let callback = vi.fn()

        let removeListener = navigation.onReady(callback)

        removeListener()
        navigation.ready()

        expect(callback).not.toHaveBeenCalled()
    })

    it('allows navigation start listeners to be removed', () => {
        let callback = vi.fn()
        let removeListener = onNavigationStart(callback)

        removeListener()
        startNavigation()

        expect(callback).not.toHaveBeenCalled()
    })
})
