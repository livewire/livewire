import { wait } from 'dom-testing-library';
import testHarness from './fixtures/test_harness'

test('get component data item value', async () => {
    testHarness.mount({
        dom: '<span></span>',
        initialData: { foo: 'bar' },
    })

    const component = window.livewire.find(123)

    expect(component.get('foo')).toEqual('bar')
})

test('set component data item value', async () => {
    var payload
    testHarness.mount({
        dom: '<span></span>',
        requestInterceptor: i => payload = i,
    })

    const component = window.livewire.find(123)

    component.set('foo', 'bar')

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('call component action', async () => {
    var payload
    testHarness.mount({
        dom: '<span></span>',
        requestInterceptor: i => payload = i,
    })

    const component = window.livewire.find(123)

    component.call('foo', 'bar')

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})
