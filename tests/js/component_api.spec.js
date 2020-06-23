import { mount, mountWithData } from './utils'
import { wait } from 'dom-testing-library';

test('get component data item value', async () => {
    mountWithData('<span></span>', { foo: 'bar' })

    const component = window.livewire.find(123)

    expect(component.get('foo')).toEqual('bar')
})

test('get nested component data item value', async () => {
    mountWithData('<span></span>', { foo: { bar: 'baz' } })

    const component = window.livewire.find(123)

    expect(component.get('foo.bar')).toEqual('baz')
})

test('set component data item value', async () => {
    var payload
    mount('<span></span>', i => payload = i)

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
    mount('<span></span>', i => payload = i)

    const component = window.livewire.find(123)

    component.call('foo', 'bar')

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('foo')
        expect(payload.actionQueue[0].payload.params).toEqual(['bar'])
    })
})
