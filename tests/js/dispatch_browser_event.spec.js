import { wait } from 'dom-testing-library'
import testHarness from './fixtures/test_harness'

test('receive event from global fire', async () => {
    let dom = '<div><button wire:click="$refresh"></button></div>'
    testHarness.mount({
        dom,
        response: {
            dom,
            dispatchQueue: [ {event: 'foo', data: {bar: 'baz'}} ]
        }
    })

    var eventReceived;
    window.addEventListener('foo', e => {
        eventReceived = e
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(eventReceived.type).toEqual('foo')
        expect(eventReceived.detail.bar).toEqual('baz')
    })
})
