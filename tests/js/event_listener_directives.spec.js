import { fireEventAndGetPayloadBeingSentToServer } from './utils'

test('test click', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <button wire:click="doSomething"></button>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.event).toEqual('fireMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test click with stopPropogation', (done) => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <div wire:click="doSomething">
            <input wire:click.stop>
        </div>
    </div>
    `
    fireEventAndGetPayloadBeingSentToServer('input', 'click')
        .then(() => {
            done.fail(new Error('This shouldnt have gotten called'))
        })

    done()
})

test('test click with preventDefault', (done) => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <form wire:submit="doSomething">
            <button type="submit" wire:click.prevent>Submit</button>
        </form>
    </div>
    `
    fireEventAndGetPayloadBeingSentToServer('button', 'click')
        .then(() => {
            done.fail(new Error('This shouldnt have gotten called'))
        })

    done()
})

test('test click with string param', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <button wire:click="doSomething('hey')">Button</button>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.event).toEqual('fireMethod')
    expect(payload.data.method).toEqual('doSomething')
    expect(payload.data.params).toEqual(['hey'])
})

test('test click with model param', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <input value="this" wire:model="hey">
        <button wire:click="doSomething(hey)">Button</button>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.event).toEqual('fireMethod')
    expect(payload.data.method).toEqual('doSomething')
    expect(payload.data.params).toEqual(['this'])
})

test('test keydown', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <input wire:keydown="doSomething">
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'keydown', { keyCode: 13})

    expect(payload.event).toEqual('fireMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test keydown with keycode', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <input wire:keydown.enter="doSomething">
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'keydown', { keyCode: 13})

    expect(payload.event).toEqual('fireMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test submit', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <form wire:submit="doSomething">
            <input name="email" value="hey@example.com">
            <button>Button</button>
        </form>
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.event).toEqual('fireMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test sync', async () => {
    document.body.innerHTML = `
    <div wire:root-id="test-id" wire:root-serialized="test-serialized">
        <input name="email" value="test" wire:sync="email">
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'input')

    expect(payload.event).toEqual('syncInput')
    expect(payload.data.name).toEqual('email')
    expect(payload.data.value).toEqual('test')
})
