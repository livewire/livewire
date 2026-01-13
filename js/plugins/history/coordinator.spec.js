import { describe, it, vi, expect } from 'vitest'
import coordinator from './coordinator'

describe('History Coordinator', () => {
    it('should replace state', async () => {
        let replaceSpy = vi.spyOn(window.history, 'replaceState')

        coordinator.replaceState('/home', { foo: 'bar' })

        // Coordinator pushes replace state to a batch, which gets flushed in a microtask, so we need to wait for it...
        await new Promise(queueMicrotask)

        expect(replaceSpy).toHaveBeenCalledOnce()
        expect(replaceSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'bar' } },
          '',
          '/home'
        )

        expect(window.history.state).toEqual({ alpine: { foo: 'bar' } })
    })

    it('should only call replaceState once per batch', async () => {
        let replaceSpy = vi.spyOn(window.history, 'replaceState')

        coordinator.replaceState('/home', { foo: 'bar' })
        coordinator.replaceState('/home', { foo: 'baz' })
        coordinator.replaceState('/home', { foo: 'qux' })
        coordinator.replaceState('/home', { foo: 'quux' })

        await new Promise(queueMicrotask)

        expect(replaceSpy).toHaveBeenCalledOnce()
        expect(replaceSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'quux' } },
          '',
          '/home'
        )

        expect(window.history.state).toEqual({ alpine: { foo: 'quux' } })
    })

    it('should merge updates into the current state when calling replaceState', async () => {
        window.history.replaceState({ alpine: { foo: 'bar' }, other: 'bob' }, '', '/home')

        let replaceSpy = vi.spyOn(window.history, 'replaceState')

        coordinator.replaceState('/home', { foo: 'baz' })

        await new Promise(queueMicrotask)

        expect(replaceSpy).toHaveBeenCalledOnce()
        expect(replaceSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'baz' }, other: 'bob' },
          '',
          '/home'
        )

        expect(window.history.state).toEqual({ alpine: { foo: 'baz' }, other: 'bob' })
    })

    it('should push state', async () => {
        let pushSpy = vi.spyOn(window.history, 'pushState')

        coordinator.pushState('/home', { foo: 'bar' })

        await new Promise(queueMicrotask)

        expect(pushSpy).toHaveBeenCalledOnce()
        expect(pushSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'bar' } },
          '',
          '/home'
        )

        expect(window.history.state).toEqual({ alpine: { foo: 'bar' } })
    })

    it('should flush pending replaces before pushing state', async () => {
        let replaceSpy = vi.spyOn(window.history, 'replaceState')
        let pushSpy = vi.spyOn(window.history, 'pushState')

        coordinator.replaceState('/home', { foo: 'bar' })
        coordinator.replaceState('/home', { foo: 'baz' })
        coordinator.replaceState('/home', { foo: 'qux' })
        coordinator.pushState('/other', { foo: 'quux' })

        await new Promise(queueMicrotask)

        expect(replaceSpy).toHaveBeenCalledOnce()
        expect(replaceSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'qux' } },
          '',
          '/home'
        )

        expect(pushSpy).toHaveBeenCalledOnce()
        expect(pushSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'quux' } },
          '',
          '/other'
        )

        expect(window.history.state).toEqual({ alpine: { foo: 'quux' } })
    })

    it('should replace the entire state when calling pushState', async () => {
        window.history.pushState({ alpine: { foo: 'bar' }, other: 'bob' }, '', '/home')

        let pushSpy = vi.spyOn(window.history, 'pushState')

        coordinator.pushState('/home', { foo: 'baz' })

        await new Promise(queueMicrotask)

        expect(pushSpy).toHaveBeenCalledOnce()
        expect(pushSpy).toHaveBeenCalledWith(
          { alpine: { foo: 'baz' } },
          '',
          '/home'
        )

        expect(window.history.state).toEqual({ alpine: { foo: 'baz' } })
    })

    it('can have a custom error handler', async () => {
        let error = new Error('test')
        let url = '/home'
        vi.spyOn(window.history, 'replaceState').mockImplementation(() => {
          throw error
        })

        let errorHandler = vi.fn()

        coordinator.addErrorHandler('test', errorHandler)

        coordinator.replaceState(url, { foo: 'bar' })

        await new Promise(queueMicrotask)

        expect(errorHandler).toHaveBeenCalledOnce()
        expect(errorHandler).toHaveBeenCalledWith(error, url)

        expect(window.history.state).toEqual({ alpine: { foo: 'bar' } })
    })
})
