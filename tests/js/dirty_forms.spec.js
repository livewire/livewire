import { fireEvent, wait, waitForDomChange } from 'dom-testing-library'
import { mount, mountWithData, mountAndReturn, mountAndReturnWithData } from './utils'

test('input element with dirty directive and class modifier attaches class to input', async () => {
    mountWithData(
        '<input wire:model="foo" wire:dirty.class="dirty" value="bar">',
        { foo: 'bar' }
    )

    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('dirty')).toBeTruthy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    expect(document.querySelector('input').value).toEqual('bar')
    expect(document.querySelector('input').classList.contains('dirty')).toBeFalsy()
})

test('input element with dirty directive and class.remove modifier removes class from input', async () => {
    mountWithData(
        '<input wire:model="foo" wire:dirty.class.remove="clean" value="bar" class="clean">',
        { foo: 'bar' }
    )

    expect(document.querySelector('input').classList.contains('clean')).toBeTruthy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'baz' }})

    expect(document.querySelector('input').value).toEqual('baz')
    expect(document.querySelector('input').classList.contains('clean')).toBeFalsy()

    fireEvent.input(document.querySelector('input'), { target: { value: 'bar' }})

    expect(document.querySelector('input').value).toEqual('bar')
    expect(document.querySelector('input').classList.contains('clean')).toBeTruthy()
})

test('input element with dirty directive and class modifier attaches class by reference foobar', async () => {
    mountWithData(
        '<span wire:dirty.class="dirty" wire:target="bar"><input wire:model="foo" class="foo" wire:ref="bar"></span>',
        { foo: 'bar' }
    )

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

test('input element with dirty directive and class.remove modifier attaches class by reference', async () => {
    mountWithData(
        '<span class="clean" wire:dirty.class.remove="clean" wire:target="bar"><input wire:model="foo" class="foo" wire:ref="bar"></span>',
        { foo: 'bar' }
    )

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
    mountWithData(
        '<span wire:dirty.class="dirty" wire:target="bar"><input wire:model="foo" class="foo" wire:dirty wire:ref="bar"></span>',
        { foo: 'bar' }
    )

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
