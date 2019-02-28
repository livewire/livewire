import { callbackAndGetPayloadBeingSentToServer, fireEvent, fireEventAndGetPayloadBeingSentToServer } from './utils'

test('test click', async () => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
        <button wire:click="doSomething"></button>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.type).toEqual('callMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test click with stopPropogation', (done) => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
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
    <div wire:id="test-id" wire:serialized="test-serialized">
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
    <div wire:id="test-id" wire:serialized="test-serialized">
        <button wire:click="doSomething('hey')">Button</button>
    </div>
    `
    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.type).toEqual('callMethod')
    expect(payload.data.method).toEqual('doSomething')
    expect(payload.data.params).toEqual(['hey'])
})

test('click_with_model_param', async () => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
        <input value="this" wire:model.lazy="hey">
        <button wire:click="doSomething">Button</button>
    </div>
    `
    const payload = await callbackAndGetPayloadBeingSentToServer(() => {
        document.querySelector('input').value = 'that'
        fireEvent('input', 'input')

        fireEvent('button', 'click')
    })

    expect(payload.type).toEqual('callMethod')
    expect(payload.data.method).toEqual('doSomething')
    expect(payload.data.params).toEqual([])
    expect(payload.data.syncQueue).toEqual({hey: 'that'})
})

test('test keydown', async () => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
        <input wire:keydown="doSomething">
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'keydown', { key: "Enter"})

    expect(payload.type).toEqual('callMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test keydown with keycode', async () => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
        <input wire:keydown.enter="doSomething">
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'keydown', { key: "Enter" })

    expect(payload.type).toEqual('callMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test submit', async () => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
        <form wire:submit="doSomething">
            <input name="email" value="hey@example.com">
            <button>Button</button>
        </form>
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('button', 'click')

    expect(payload.type).toEqual('callMethod')
    expect(payload.data.method).toEqual('doSomething')
})

test('test sync', async () => {
    document.body.innerHTML = `
    <div wire:id="test-id" wire:serialized="test-serialized">
        <input name="email" value="test" wire:model="email">
    </div>
    `

    const payload = await fireEventAndGetPayloadBeingSentToServer('input', 'input')

    expect(payload.type).toEqual('syncInput')
    expect(payload.data.name).toEqual('email')
    expect(payload.data.value).toEqual('test')
})
