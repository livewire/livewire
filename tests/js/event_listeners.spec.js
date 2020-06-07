import { wait } from 'dom-testing-library'
import MockEcho from 'mock-echo'
import testHarness from './fixtures/test_harness'

test('receive event from global fire', async () => {
    var payload
    testHarness.mount({
        dom: '<div></div>',
        initialData: { events: ['foo'] },
        requestInterceptor: i => payload = i
    })

    window.livewire.emit('foo', 'bar');

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})

test('receive event from action fire', async () => {
    var payload
    testHarness.mount({
        dom: '<button wire:click="$emit(\'foo\', \'bar\')"></button>',
        initialData: { events: ['foo'] },
        requestInterceptor: i => payload = i
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})

test('receive event from component fire, and make sure global listener receives event too', async () => {
    var returnedParamFromOuterListener
    var returnedParamFromInnerListener
    var returnedParamFromGlobalListener
    let dom = `
        <div>
            <button id="outer-button" wire:click="$refresh"></button>
            <div wire:id="456" wire:initial-data="{}" wire:events="[]"></div>
        </div>
    `
    testHarness.mount({
        dom,
        response: {
            dom,
            eventQueue: [{ event: 'foo', params: ['bar'] }],
        }
    })

    const outerComponent = window.livewire.components.findComponent(123)
    const innerComponent = window.livewire.components.findComponent(456)

    outerComponent.on('foo', (shouldBeBar) => {
        returnedParamFromOuterListener = shouldBeBar
    });

    innerComponent.on('foo', (shouldNotGetCalled) => {
        returnedParamFromInnerListener = shouldNotGetCalled
    });

    window.livewire.on('foo', (shouldBeBar) => {
        returnedParamFromGlobalListener = shouldBeBar
    });

    document.querySelector('#outer-button').click()

    await wait(() => {
        expect(returnedParamFromOuterListener).toEqual('bar')
        expect(returnedParamFromGlobalListener).toEqual('bar')
        expect(returnedParamFromInnerListener).toEqual(undefined)
    })
})

test('receive event from component fired only to ancestors, and make sure global listener doesnt receive it', async () => {
    let dom = `
        <div wire:id="123" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;] }">
            <div wire:id="456" wire:initial-data="{}" wire:events="[]">
                <button wire:click="$refresh"></button>
            </div>
        </div>
        <div wire:id="789" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;] }"></div>
    `
    let requestInterceptor = jest.fn()

    testHarness.mount({
        dom,
        asRoot: true,
        requestInterceptor,
        response: [
            {
                dom,
                eventQueue: [{
                    ancestorsOnly: true,
                    event: 'foo',
                    params: [],
                }],
            },
            {
                dom,
            },
        ]
    })

    let globalEventHandler = jest.fn()
    window.livewire.on('foo', globalEventHandler)

    document.querySelector('button').click()

    await wait(() => {
        expect(requestInterceptor).toHaveBeenCalledTimes(2)
        expect(requestInterceptor.mock.calls[0][0].id).toEqual('456')
        expect(requestInterceptor.mock.calls[1][0].id).toEqual('123')
        expect(globalEventHandler).not.toHaveBeenCalled()
    })
})

test('receive event from action fired only to ancestors, and make sure global listener doesnt receive it', async () => {
    let dom = `
        <div wire:id="123" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;] }">
            <div wire:id="456" wire:initial-data="{}" wire:events="[]">
                <button wire:click="$emitUp('foo')"></button>
            </div>
        </div>
        <div wire:id="789" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;] }"></div>
    `
    let requestInterceptor = jest.fn()

    testHarness.mount({
        dom,
        asRoot: true,
        requestInterceptor,
    })

    let globalEventHandler = jest.fn()
    window.livewire.on('foo', globalEventHandler)

    document.querySelector('button').click()

    await wait(() => {
        expect(requestInterceptor).toHaveBeenCalledTimes(1)
        expect(requestInterceptor.mock.calls[0][0].id).toEqual('123')
        expect(globalEventHandler).not.toHaveBeenCalled()
    })
})

test('receive event from action fired only to self, and make sure global listener doesnt receive it', async () => {
    let dom = `
        <div wire:id="123" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;] }">
            <button wire:click="$emitSelf('foo')"></button>
        </div>
        <div wire:id="456" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;] }"></div>
    `
    let requestInterceptor = jest.fn()

    testHarness.mount({
        dom,
        asRoot: true,
        requestInterceptor,
    })

    let globalEventHandler = jest.fn()
    window.livewire.on('foo', globalEventHandler)

    document.querySelector('button').click()

    await wait(() => {
        expect(requestInterceptor).toHaveBeenCalledTimes(1)
        expect(requestInterceptor.mock.calls[0][0].id).toEqual('123')
        expect(globalEventHandler).not.toHaveBeenCalled()
    })
})

test('receive event from action fired only to component name, and make sure global listener doesnt receive it', async () => {
    let dom = `
        <div wire:id="123" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;], &quot;name&quot;: &quot;the-wrong-name&quot; }">
            <button wire:click="$emitTo('the-right-name', 'foo')"></button>
        </div>
        <div wire:id="456" wire:initial-data="{&quot;events&quot;: [&quot;foo&quot;], &quot;name&quot;: &quot;the-right-name&quot; }"></div>
    `
    let requestInterceptor = jest.fn()

    testHarness.mount({
        dom,
        asRoot: true,
        requestInterceptor,
    })

    let globalEventHandler = jest.fn()
    window.livewire.on('foo', globalEventHandler)

    document.querySelector('button').click()

    await wait(() => {
        expect(requestInterceptor).toHaveBeenCalledTimes(1)
        expect(requestInterceptor.mock.calls[0][0].id).toEqual('456')
        expect(globalEventHandler).not.toHaveBeenCalled()
    })
})

describe('test Laravel Echo', () => {
    let mockEcho

    beforeEach(() => {
        mockEcho = new MockEcho()
        global.Echo = mockEcho
    })

    afterEach(() => {
        delete global.Echo
    })

    test('public echo channel is created and reacts', async () => {
        expect(mockEcho.channelExist('foo')).toBe(false)

        var payload
        testHarness.mount({
            dom: '<div></div>',
            initialData: { events: ['echo:foo,bar'] },
            requestInterceptor: i => payload = i,
        })

        expect(mockEcho.channelExist('foo')).toBe(true)
        expect(mockEcho.getChannel('foo').eventExist('bar')).toBe(true)

        mockEcho.getChannel('foo').broadcast('bar', 'baz')

        await wait(() => {
            expect(payload.actionQueue[0].type).toEqual('fireEvent')
            expect(payload.actionQueue[0].payload.event).toEqual('echo:foo,bar')
            expect(payload.actionQueue[0].payload.params).toEqual(['baz'])
        })
    })

    test('private echo channel is created and reacts', async () => {
        expect(mockEcho.privateChannelExist('foo')).toBe(false)

        var payload
        testHarness.mount({
            dom: '<div></div>',
            initialData: { events: ['echo-private:foo,bar'] },
            requestInterceptor: i => payload = i,
        })

        expect(mockEcho.privateChannelExist('foo')).toBe(true)
        expect(mockEcho.getPrivateChannel('foo').eventExist('bar')).toBe(true)

        mockEcho.getPrivateChannel('foo').broadcast('bar', 'baz')

        await wait(() => {
            expect(payload.actionQueue[0].type).toEqual('fireEvent')
            expect(payload.actionQueue[0].payload.event).toEqual('echo-private:foo,bar')
            expect(payload.actionQueue[0].payload.params).toEqual(['baz'])
        })
    })


    test('presence echo channel is created and reacts', async () => {
        expect(mockEcho.presenceChannelExist('foo')).toBe(false)

        var payload
        testHarness.mount({
            dom: '<div></div>',
            initialData: { events: ['echo-presence:foo,here'] },
            requestInterceptor: i => payload = i,
        })

        expect(mockEcho.presenceChannelExist('foo')).toBe(true)

        mockEcho.getPresenceChannel('foo').iJoin({id: 1, name: 'Caleb'})

        await wait(() => {
            expect(payload.actionQueue[0].type).toEqual('fireEvent')
            expect(payload.actionQueue[0].payload.event).toEqual('echo-presence:foo,here')
            expect(payload.actionQueue[0].payload.params).toEqual([[{id: 1, name: 'Caleb'}]])
        })
    })

    test('notification echo channel is created', async () => {
        expect(mockEcho.privateChannelExist('foo')).toBe(false)

        var payload
        testHarness.mount({
            dom: '<div></div>',
            initialData: { events: ['echo-notification:foo'] },
            requestInterceptor: i => payload = i,
        })

        expect(mockEcho.privateChannelExist('foo')).toBe(true)
    })
})
