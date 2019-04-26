import { fireEvent, wait, waitForDomChange } from 'dom-testing-library'
import { mount, mountWithData, mountAndReturn, mountAndReturnWithData } from './utils'

test('properties sync on input change', async () => {
    var payload
    mount('<input wire:model="foo">', i => payload = i)

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('syncInput')
        expect(payload.actionQueue[0].payload.name).toEqual('foo')
        expect(payload.actionQueue[0].payload.value).toEqual('bar')
    })
})

test('properties are lazy synced when action is fired', async () => {
    var payload
    mount('<input wire:model.lazy="foo"><button wire:click="onClick"></button>', i => payload = i)

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})
    document.querySelector('button').click()

    await wait(() => {
        expect(payload.syncQueue).toEqual({ foo: 'bar'})
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('onClick')
        expect(payload.actionQueue[0].payload.params).toEqual([])
    })
})

test('input element value doesnt change unless property is marked as dirty', async () => {
    mountAndReturn(
        '<input wire:model="foo" value="">',
        '<input wire:model="foo" value="bar"><button>Im here to trigger dom change</button>',
        []
    )

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('baz')
    })

    mountAndReturn(
        '<input wire:model="foo" value="">',
        '<input wire:model="foo" value="bar"><button>Im here to trigger dom change</button>',
        ['foo']
    )

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('bar')
    })
})

test('input element value doesnt change, but other attributes do when not marked as dirty', async () => {
    mountAndReturn(
        '<input wire:model="foo" class="foo" value="">',
        '<input wire:model="foo" class="foo bar" value="bar">',
        []
    )

    document.querySelector('input').focus()
    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await wait(() => {
        expect(document.querySelector('input').value).toEqual('baz')
        expect(document.querySelector('input').classList.contains('bar')).toBeTruthy()
    })
})

test('input element value changes, when not marked as dirty, only when element isnt focused', async () => {
    mountAndReturn(
        '<input wire:model="foo" value="">',
        '<input wire:model="foo" value="bar">',
        []
    )

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    await wait(() => {
        expect(document.querySelector('input').value).toEqual('bar')
    })
})

test('input element value attribute is automatically added if not present in returned dom', async () => {
    mountAndReturnWithData(
        '<input wire:model="foo"><button wire:click="onClick"></button>',
        '<input wire:model="foo"><button wire:click="onClick"></button>',
        { foo: 'bar' }
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('input').hasAttribute('value')).toBeTruthy()
        expect(document.querySelector('input').value).toBe('bar')
    })
})

test('input element value attribute is automatically added if not present in the initial dom', async () => {
    mountWithData(
        '<input wire:model="foo">',
        { foo: 'bar' }
    )

    await wait(() => {
        expect(document.querySelector('input').value).toBe('bar')
    })
})

test('textarea element value attribute is automatically added if not present in the initial dom', async () => {
    mountWithData(
        '<textarea wire:model="foo"></textarea>',
        { foo: 'bar' }
    )

    await wait(() => {
        expect(document.querySelector('div').innerHTML).toBe('<textarea wire:model=\"foo\">bar</textarea>')
    })
})
