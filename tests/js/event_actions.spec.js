import { fireEvent, wait } from 'dom-testing-library'
import { mount, mountAsRoot, mountAsRootAndReturn, mountAndReturn, mountAndError } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

test('basic click', async () => {
    var payload
    mount('<button wire:click="someMethod"></button>', i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual([])
    })
})

test('basic click with self modifier', async () => {
    var payload
    mount('<button wire:click.self="outerMethod"><span wire:click="innerMethod"></span></button>', i => payload = i)

    document.querySelector('span').click()

    await wait(() => {
        expect(payload.actionQueue[0].payload.method).toEqual('innerMethod')
        expect(payload.actionQueue[1]).toBeUndefined()
    })
})

test('click with params', async () => {
    var payload
    mount(`<button wire:click="someMethod('foo', 'bar')"></button>`, i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('if a click and blur happen at the same time, the actions are queued and sent together', async () => {
    var payload
    mount('<input wire:blur="onBlur"><button wire:click="onClick"></button>', i => payload = i)

    document.querySelector('input').focus()
    document.querySelector('button').click()
    document.querySelector('input').blur()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('onClick')
        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('onBlur')
    })
})

test('two keydown events', async () => {
    var payload
    mount('<button wire:keydown="someMethod" wire:keydown.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyDown(document.querySelector('button'), { key: 'Enter' })

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual([])
        expect(payload.actionQueue[1].type).toEqual('callMethod')
        expect(payload.actionQueue[1].payload.method).toEqual('otherMethod')
        expect(payload.actionQueue[1].payload.params).toEqual([])
    })
})

test('keydown.enter doesnt fire when other keys are pressed', async () => {
    var payload
    mount('<button wire:keydown.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyDown(document.querySelector('button'), { key: 'Escape' })

    await timeout(10)

    expect(payload).toBeUndefined()
})

test('keyup.enter doesnt fire when other keys are pressed', async () => {
    var payload
    mount('<button wire:keyup.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyUp(document.querySelector('button'), { key: 'Escape' })

    await timeout(10)

    expect(payload).toBeUndefined()
})

test('keyup.cmd.enter', async () => {
    var payload
    mount('<button wire:keyup.cmd.enter="otherMethod"></button>', i => payload = i)

    fireEvent.keyUp(document.querySelector('button'), { metaKey: false, key: 'Enter' })

    await timeout(10)

    expect(payload).toBeUndefined()
})

test('init', async () => {
    var initHappened = false
    mountAsRoot('<div wire:id="123" wire:initial-data="{}" wire:init="someMethod"></div>', () => { initHappened = true })

    await timeout(10)

    expect(initHappened).toBeTruthy()
})

test('elements are marked as read-only or disabled during form submissions', async () => {
    var payload
    mount(`
        <form wire:submit.prevent="someMethod">
            <input type="text">
            <input type="checkbox">
            <input type="radio">
            <select></select>
            <textarea></textarea>
            <button type="submit"></button>
        </form>
    `, i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual([])
        expect(document.querySelector('button').disabled).toBeTruthy()
        expect(document.querySelector('select').disabled).toBeTruthy()
        expect(document.querySelector('input[type=checkbox]').disabled).toBeTruthy()
        expect(document.querySelector('input[type=radio]').disabled).toBeTruthy()
        expect(document.querySelector('input[type=text]').readOnly).toBeTruthy()
        expect(document.querySelector('textarea').readOnly).toBeTruthy()
    })
})

test('elements are unmarked as read-only or disabled after successful form submissions', async () => {
    mountAndReturn(`
        <form wire:submit.prevent="someMethod">
            <input type="text">
            <button type="submit"></button>
        </form>
    `, `
        <form wire:submit.prevent="someMethod">
            <input type="text">
            <button type="submit"></button>
        </form>
    `, [], async () => new Promise(resolve => setTimeout(resolve, 5)))

    expect(document.querySelector('input').readOnly).toEqual(false)
    expect(document.querySelector('button').disabled).toEqual(false)

    document.querySelector('button').click()

    expect(document.querySelector('input').readOnly).toEqual(true)
    expect(document.querySelector('button').disabled).toEqual(true)

    await wait(() => {
        expect(document.querySelector('input').readOnly).toEqual(false)
        expect(document.querySelector('button').disabled).toEqual(false)
    })
})

test('elements are unmarked as read-only or disabled after form submissions that throw an error', async () => {
    mountAndError(`
        <form wire:submit.prevent="someMethod">
            <input type="text">
            <button type="submit"></button>
        </form>
    `, async () => new Promise(resolve => setTimeout(resolve, 5)))

    expect(document.querySelector('input').readOnly).toEqual(false)
    expect(document.querySelector('button').disabled).toEqual(false)

    document.querySelector('button').click()

    expect(document.querySelector('input').readOnly).toEqual(true)
    expect(document.querySelector('button').disabled).toEqual(true)

    await wait(() => {
        expect(document.querySelector('input').readOnly).toEqual(false)
        expect(document.querySelector('button').disabled).toEqual(false)
    })
})

test('elements are not marked as read-only or disabled during form submissions if they are withing a wire:ignore', async () => {
    var payload
    mount(`
        <form wire:submit.prevent="someMethod">
            <input type="text">
            <div wire:ignore>
                <button type="submit"></button>
            </div>
        </form>
    `, i => payload = i)

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('button').disabled).toBeFalsy()
        expect(document.querySelector('input[type=text]').readOnly).toBeTruthy()
    })
})

test('action parameters without space around comma', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo','bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('action parameters with space before comma', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo' ,'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('action parameters with space after comma', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo', 'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('action parameters with space around comma', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo' , 'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', 'bar'])
    })
})

test('action parameters with space and comma inside will be handled', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo, bar', true , 'baz',null,'x,y')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo, bar', true, 'baz', null, 'x,y'])
    })
})

test('action parameters must be separated by comma', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo'|'bar')"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).not.toEqual(['foo', 'bar'])
    })
})

test('action parameter can be empty', async () => {
    var payload
    mount(`<button wire:click="callSomething()"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual([])
    })
})

test('action parameter can use double-quotes', async () => {
    var payload
    mount(`<button wire:click='callSomething("double-quotes are ugly", true)'></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['double-quotes are ugly', true])
    })
})

test('action parameters can include expressions', async () => {
    var payload
    mount(`<button wire:click="callSomething('foo', new Array('1','2'))"></button>`, i => payload = i)

    fireEvent.click(document.querySelector('button'))

    await wait(() => {
        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('callSomething')
        expect(payload.actionQueue[0].payload.params).toEqual(['foo', ['1','2']])
    })
})

test('debounce keyup event', async () => {
    var payload
    mount('<input wire:keyup.debounce.50ms="someMethod"></button>', i => payload = i)

    fireEvent.keyUp(document.querySelector('input'), { key: 'x' })

    await timeout(1)

    expect(payload).toEqual(undefined)

    await timeout(60)

    expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
})

test('debounce keyup event with key specified', async () => {
    var payload
    mount('<input wire:keyup.x.debounce.50ms="someMethod"></button>', i => payload = i)

    fireEvent.keyUp(document.querySelector('input'), { key: 'k' })

    await timeout(5)

    expect(payload).toEqual(undefined)

    await timeout(60)

    expect(payload).toEqual(undefined)

    fireEvent.keyUp(document.querySelector('input'), { key: 'x' })

    await timeout(5)

    expect(payload).toEqual(undefined)

    await timeout(60)

    expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
})

test('keydown event', async () => {
    var payload
    mount('<input wire:keydown="someMethod"></button>', i => payload = i)

    fireEvent.keyDown(document.querySelector('input'), { key: 'x' })

    await wait(() => {

        expect(payload.actionQueue[0].type).toEqual('callMethod')
        expect(payload.actionQueue[0].payload.method).toEqual('someMethod')
        expect(payload.actionQueue[0].payload.params).toEqual([])
    })
})
