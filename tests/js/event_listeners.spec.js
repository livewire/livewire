import { wait } from 'dom-testing-library'
import { mountWithEvent } from './utils'
import MockEcho from 'mock-echo'

test('receive event from global fire', async () => {
    var payload
    mountWithEvent('<div></div>', ['foo'], i => payload = i)

    window.livewire.emit('foo', 'bar');

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})

test('receive event from action fire', async () => {
    var payload
    mountWithEvent('<button wire:click="$emit(\'foo\', \'bar\')"></button>', ['foo'], i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('fireEvent')
        expect(payload.actionQueue[0].payload.event).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
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
        mountWithEvent('<div></div>', ['echo:foo,bar'], i => payload = i)
    
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
        mountWithEvent('<div></div>', ['echo-private:foo,bar'], i => payload = i)
    
        expect(mockEcho.privateChannelExist('foo')).toBe(true)
        expect(mockEcho.getPrivateChannel('foo').eventExist('bar')).toBe(true)
    
        mockEcho.getPrivateChannel('foo').broadcast('bar', 'baz')
    
        await wait(() => {
            expect(payload.actionQueue[0].type).toEqual('fireEvent')
            expect(payload.actionQueue[0].payload.event).toEqual('echo-private:foo,bar')
            expect(payload.actionQueue[0].payload.params).toEqual(['baz'])
        })
    })
})
