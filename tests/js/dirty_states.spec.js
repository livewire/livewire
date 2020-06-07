import { wait, fireEvent, waitForDomChange } from 'dom-testing-library'
import harness from 'fixtures/test_harness'

test('input element with dirty directive and class modifier attaches class to input', async () => {
    harness.mount({
        dom: '<input wire:model="foo" wire:dirty.class="dirty" value="bar">',
        initialData: { foo: 'bar' },
    })

    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('dirty')).toBeTruthy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    expect(document.querySelector('input').value).toEqual('bar')
    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()
})

test('dirty classes are removed when livewire updates', async () => {
    harness.mount({
        dom: '<input wire:dirty.class="dirty" wire:model.lazy="foo" value="bar">',
        response: {
            dom: '<input wire:dirty.class="dirty" wire:model.lazy="foo" value="bar"><button>Im here to trigger dom change</button>',
        }
    })

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('dirty')).toBeTruthy()

    fireEvent.change(document.querySelector('input'), { target: { value: 'plop' }})

    await waitForDomChange(document.body, () => {
        expect(document.querySelector('input').value).toEqual('plop')
        expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()
    })

})

test('input element with dirty directive and class.remove modifier removes class from input', async () => {
    harness.mount({
        dom: '<input wire:model="foo" wire:dirty.class.remove="clean" value="bar" class="clean">',
        initialData: { foo: 'bar' },
    })

    expect(document.querySelector('input').classList.contains('clean')).toBeTruthy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('clean')).toBeFalsy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    expect(document.querySelector('input').value).toEqual('bar')
    expect(document.querySelector('input').classList.contains('clean')).toBeTruthy()
})

test('input element with dirty directive and class modifier attaches class by reference to data', async () => {
    harness.mount({
        dom: '<span wire:dirty.class="dirty" wire:target="foo"><input wire:model="foo" class="foo"></span>',
        initialData: { foo: 'bar' },
    })

    expect(document.querySelector('span').classList.contains('dirty')).toBeFalsy()
    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()
    expect(document.querySelector('span').classList.contains('dirty')).toBeTruthy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    expect(document.querySelector('input').value).toEqual('bar')
    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()
    expect(document.querySelector('span').classList.contains('dirty')).toBeFalsy()
})

test('input element with dirty directive and class.remove modifier attaches class by reference to data', async () => {
    harness.mount({
        dom: '<span class="clean" wire:dirty.class.remove="clean" wire:target="foo"><input wire:model="foo" class="foo"></span>',
        initialData: { foo: 'bar' },
    })

    expect(document.querySelector('input').classList.contains('clean')).toBeFalsy()
    expect(document.querySelector('span').classList.contains('clean')).toBeTruthy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('clean')).toBeFalsy()
    expect(document.querySelector('span').classList.contains('clean')).toBeFalsy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    expect(document.querySelector('input').value).toEqual('bar')
    expect(document.querySelector('input').classList.contains('clean')).toBeFalsy()
    expect(document.querySelector('span').classList.contains('clean')).toBeTruthy()
})

test('element with dirty directive and no modifier will be hidden by default and shown when dirty', async () => {
    harness.mount({
        dom: '<span wire:dirty wire:target="foo"><input wire:model="foo" class="foo"></span>',
        initialData: { foo: 'bar' },
    })

    expect(document.querySelector('span').style.display).toEqual('')

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')

    expect(document.querySelector('span').style.display).toEqual('inline-block')
})

test('remove element reference from components generic dirty array', async () => {
    harness.mount({
        dom: '<button wire:click="foo"></button><span wire:dirty wire:target="foo"></span>',
        response: {
            dom: '<button wire:click="foo"></button>',
        },
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span')).toEqual(null)
        expect(window.livewire.components.findComponent(123).genericDirtyEls).toEqual([])
    })
})

test('remove element reference from components targeted dirty array', async () => {
    harness.mount({
        dom: '<button wire:click="foo"></button><span wire:dirty wire:target="foo"></span>',
        response: {
            dom: '<button wire:click="foo"></button>',
        },
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span')).toEqual(null)
        expect(window.livewire.components.findComponent(123).targetedDirtyElsByProperty).toEqual({foo: []})
    })
})
