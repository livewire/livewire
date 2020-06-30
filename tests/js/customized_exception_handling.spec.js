import { wait } from 'dom-testing-library'
import { mountAndError } from './utils'

test('without custom error handler, errors are displayed in modal', async () => {
    global.fetch = jest.fn(() =>
        Promise.resolve({
            ok: false,
            status: 400,
            statusText: 'Not Found',
            text: () => Promise.resolve('Not Found / 404'),
        })
    )

    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:initial-data="{}"><button wire:click="onClick"></button></div>'

    window.livewire = new Livewire()
    window.livewire_token = 'foo'
    window.livewire.start()

    expect(document.getElementById('burst-error')).toBeFalsy()

    document.querySelector('button').click()

    await wait(() => {
        expect(document.getElementById('burst-error')).toBeTruthy()
    })
})

test('without custom error handler, dumps are displayed in modal', async () => {
    global.fetch = jest.fn(() =>
        Promise.resolve({
            ok: true,
            status: 200,
            statusText: 'OK',
            text: () => Promise.resolve('<script>Sfdump = function(a) { return a; };</script><script>Sfdump("foo")</script>'),
        })
    )

    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:initial-data="{}"><button wire:click="onClick"></button></div>'

    window.livewire = new Livewire()
    window.livewire_token = 'foo'
    window.livewire.start()

    expect(document.getElementById('burst-error')).toBeFalsy()

    document.querySelector('button').click()

    await wait(() => {
        expect(document.getElementById('burst-error')).toBeTruthy()
    })
})

test('can customize error handling', async () => {
    mountAndError('<button wire:click="onClick"></button>')

    var customErrorTracker = false
    window.livewire.onError((status) => {
        customErrorTracker = status
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(customErrorTracker).toBeTruthy()
    })
})

test('custom error handler receives the response status info', async () => {
    mountAndError('<button wire:click="onClick"></button>')

    var customErrorTracker = false
    window.livewire.onError((status) => {
        customErrorTracker = status
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(customErrorTracker.ok).toBe(false)
        expect(customErrorTracker.status).toBeTruthy()
        expect(customErrorTracker.statusText).toBeTruthy()
    })
})

test('custom error handler can prevent displaying error in modal', async () => {
    global.fetch = jest.fn(() =>
        Promise.resolve({
            ok: false,
            status: 400,
            statusText: 'Not Found',
            text: () => Promise.resolve('Not Found / 404'),
        })
    )

    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:initial-data="{}"><button wire:click="onClick"></button></div>'

    window.livewire = new Livewire()
    window.livewire_token = 'foo'
    window.livewire.start()

    window.livewire.onError(() => {
        return false
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.getElementById('burst-error')).toBeFalsy()
    })
})

test('custom error handler can prevent displaying dump in modal', async () => {
    global.fetch = jest.fn(() =>
        Promise.resolve({
            ok: true,
            status: 200,
            statusText: 'OK',
            text: () => Promise.resolve('<script>Sfdump = function(a) { return a; };</script><script>Sfdump("foo")</script>'),
        })
    )

    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:initial-data="{}"><button wire:click="onClick"></button></div>'

    window.livewire = new Livewire()
    window.livewire_token = 'foo'
    window.livewire.start()

    window.livewire.onError(() => {
        return false
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.getElementById('burst-error')).toBeFalsy()
    })
})

test('custom error handler is called when fetch has error', async () => {
    global.fetch = jest.fn(() =>
        Promise.reject("API is down")
    )

    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:initial-data="{}"><button wire:click="onClick"></button></div>'

    window.livewire = new Livewire()
    window.livewire_token = 'foo'
    window.livewire.start()

    var customErrorTracker = false
    window.livewire.onError((status) => {
        customErrorTracker = status
        return false
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(customErrorTracker).toEqual('API is down')
    })
})

test('custom error handler can filter errors from default handling', async () => {
    global.fetch = jest.fn()
    global.fetch.mockReturnValueOnce(
        Promise.resolve({
            ok: true,
            status: 200,
            statusText: 'OK',
            text: () => Promise.resolve('<script>Sfdump = function(a) { return a; };</script><script>Sfdump("foo")</script>'),
        })
    ).mockReturnValue(
        Promise.resolve({
            ok: false,
            status: 404,
            statusText: 'Not Found',
            text: () => Promise.resolve('Not Found / 404'),
        })
    )

    document.body.innerHTML = '';
    document.body.innerHTML = '<div wire:id="123" wire:initial-data="{}"><button wire:click="onClick"></button></div>'

    window.livewire = new Livewire()
    window.livewire_token = 'foo'
    window.livewire.start()

    window.livewire.onError((status) => {
        if (status.ok) return false
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.getElementById('burst-error')).toBeFalsy()
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.getElementById('burst-error')).toBeTruthy()
    })
})
