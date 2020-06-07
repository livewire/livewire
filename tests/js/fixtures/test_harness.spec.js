import { wait } from 'dom-testing-library'
import harness from './test_harness'

describe('the test harness', () => {
    test('stops any previously running livewire session', () => {
        let stop = jest.fn()
        window.livewire = { stop }
        document.body.innerHTML = '<div>Some content</div>'

        harness.unmount()
        expect(stop).toBeCalled()
        expect(document.body.innerHTML).toEqual('')
    })

    test('initializes given dom content as livewire component', () => {
        harness.configure({
            dom: 'this is the dom',
        }).initializeDom()

        // given content appears wrapped in div with wire:id="123" attribute
        expect(document.body.innerHTML).toMatch(/^<div.*wire:id="123"/)
        expect(document.body.innerHTML).toMatch(/this is the dom/)
        expect(document.body.innerHTML).toMatch(/<\/div>$/)
    })

    test('can initialize the given dom content as the root document body', () => {
        harness.configure({
            dom: 'this is the dom',
            asRoot: true,
        }).initializeDom()

        expect(document.body.innerHTML).toEqual('this is the dom')
    })

    test('can initialize the dom with initial data', () => {
        let initialData = { foo: 'bar' }
        harness.configure({
            dom: 'this is the dom',
            initialData,
        }).initializeDom()

        // look for root div with wire:initial-data attribute
        // containing the initial data as JSON
        let re = new RegExp(
            '^<div .*wire:initial-data="' +
            JSON.stringify({ data: { ...initialData } }).replace(/\"/g, '&quot;') +
            '"'
        )
        expect(document.body.innerHTML).toMatch(re)
    })

    test('uses a test driver to simulate the connection', () => {
        harness.configure({})

        expect(harness.driver).not.toBe(undefined)
        expect(harness.driver).toHaveProperty('sendMessage')
        expect(harness.driver).toHaveProperty('onMessage')
        expect(harness.driver).toHaveProperty('onError')
        expect(harness.driver).toHaveProperty('config')

        let config = {
            delay: 'delay',
            error: 'error',
            requestInterceptor: 'request interceptor',
            response: {
                data: 'response data',
            }
        }

        harness.configure({
            ...config,
            ...{ extra: 'ignored', dom: 'dom' },
        }).initializeDriver()

        expect(harness.driver.config).toEqual(config)
    })

    test('returns the initialized livewire element when mounted', () => {
        let el = harness.mount({
            dom: 'some dom content',
        })

        expect(el.__livewire).toBeTruthy()
        expect(el.__livewire.id).toEqual('123')
    })

    test('can register livewire directives', () => {
        harness.mount({
            dom: 'some dom',
            directives: [{
                name: 'foo',
                callback: () => 'bar'
            }]
        })

        expect(window.livewire.components.directives.directives.listeners['foo']).toBeTruthy()
    })

    test('can intercept the connection request', async () => {
        let spy = jest.fn()
        harness.mount({
            dom: '<button wire:click="someMethod"></button>',
            requestInterceptor: spy,
        })

        document.querySelector('button').click()

        await wait(() => {
            expect(spy).toHaveBeenCalled()
        })
    })

    test('can specify data for the connection response message', async () => {
        harness.mount({
            dom: '<button wire:click="someMethod"></button>',
            response: { foo: 'bar' }
        })
        let spy = jest.fn()
        harness.driver.onMessage = spy

        harness.driver.sendMessage({})

        await wait(() => {
            expect(spy).toHaveBeenCalledTimes(1)
            expect(spy.mock.calls[spy.mock.calls.length-1][0]).toMatchObject({ foo: 'bar' })
        })
    })

    test('ensures any given response dom appears as a livewire test component', () => {
        harness.configure({
            response: { dom: 'some dom stuff' }
        }).initializeDriver()
        expect(harness.driver.config.response.dom).toEqual('<div wire:id=\"123\">some dom stuff</div>')

        harness.configure({
            response: { dom: '<div wire:id="456">stuff</div>' }
        }).initializeDriver()
        expect(harness.driver.config.response.dom).toEqual('<div wire:id=\"123\"><div wire:id=\"456\">stuff</div></div>')

        harness.configure({
            response: { dom: '<div wire:id=\"123\">some dom stuff</div>' }
        }).initializeDriver()
        expect(harness.driver.config.response.dom).toEqual('<div wire:id=\"123\">some dom stuff</div>')

        harness.configure({
            response: { dom: '<div wire:id=\"123\"><div wire:id="456">stuff</div></div>' }
        }).initializeDriver()
        expect(harness.driver.config.response.dom).toEqual('<div wire:id=\"123\"><div wire:id=\"456\">stuff</div></div>')
    })

    test('can simulate multiple responses', async () => {
        harness.configure({
            response: [
                { foo: 'response 1' },
                { foo: 'response 2' },
            ]
        }).initializeDriver()
        let spy = jest.fn()
        harness.driver.onMessage = spy

        harness.driver.sendMessage({})
        await wait(() => {
            expect(spy).toHaveBeenCalledTimes(1)
            expect(spy.mock.calls[spy.mock.calls.length-1][0]).toMatchObject({ foo: 'response 1' })
        })

        harness.driver.sendMessage({})
        await wait(() => {
            expect(spy).toHaveBeenCalledTimes(2)
            expect(spy.mock.calls[spy.mock.calls.length-1][0]).toMatchObject({ foo: 'response 2' })
        })

        harness.driver.sendMessage({})
        await wait(() => {
            expect(spy).toHaveBeenCalledTimes(3)
            expect(Object.keys(spy.mock.calls[spy.mock.calls.length-1][0])).toEqual(['id', 'fromPrefetch'])
        })
    })
})
