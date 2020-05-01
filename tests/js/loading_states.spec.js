import { wait } from 'dom-testing-library'
import { mount, mountAndReturn, mountAndError } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

test('show element while loading and hide after', async () => {
    mountAndReturn(
        `<button wire:click="onClick"></button><span style="display: none" wire:loading></span>`,
        `<button wire:click="onClick"></button><span style="display: none" wire:loading></span>`,
        [], async () => {
            // Make the loading last for 50ms.
            await timeout(50)
        }
    )

    expect(document.querySelector('span').style.display).toEqual('none')

    document.querySelector('button').click()

    await wait(async () => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')

        await wait(async () => {
            expect(document.querySelector('span').style.display).toEqual('none')
        })
    })
})

test('hide element while loading and show after', async () => {
    mountAndReturn(
        `<button wire:click="onClick"></button><span style="display: inline-block" wire:loading.remove></span>`,
        `<button wire:click="onClick"></button><span style="display: inline-block" wire:loading.remove></span>`,
        [], async () => {
            // Make the loading last for 50ms.
            await timeout(50)
        }
    )

    expect(document.querySelector('span').style.display).toEqual('inline-block')

    document.querySelector('button').click()

    await wait(async () => {
        expect(document.querySelector('span').style.display).toEqual('none')

        await wait(async () => {
            expect(document.querySelector('span').style.display).toEqual('inline-block')
        })
    })
})

test('loading is scoped to current element if it fires an action', async () => {
    mountAndReturn(
        `<button wire:click="foo" wire:loading.attr="disabled"><span wire:click="bar" wire:loading.class="baz"></span>`,
        `<button wire:click="foo" wire:loading.attr="disabled"><span wire:click="bar" wire:loading.class="baz"></span>`,
        [], async () => {
            // Make the loading last for 50ms.
            await timeout(50)
        }
    )

    expect(document.querySelector('button').disabled).toEqual(false)
    expect(document.querySelector('span').classList.contains('baz')).toEqual(false)

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('button').disabled).toEqual(true)
        expect(document.querySelector('span').classList.contains('baz')).toEqual(false)
    })

    await wait(() => {
        expect(document.querySelector('button').disabled).toEqual(false)
        expect(document.querySelector('span').classList.contains('baz')).toEqual(false)
    })
})

test('loading is scoped to current element if it binds data with wire:model', async () => {
    mountAndReturn(
        `<button wire:click="foo" wire:loading.attr="disabled"><input wire:model="bar" wire:loading.class="baz"></button>`,
        `<button wire:click="foo" wire:loading.attr="disabled"><input wire:model="bar" wire:loading.class="baz"></button>`,
        [], async () => {
            // Make the loading last for 50ms.
            await timeout(50)
        }
    )

    expect(document.querySelector('button').disabled).toEqual(false)
    expect(document.querySelector('input').classList.contains('baz')).toEqual(false)

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('button').disabled).toEqual(true)
        expect(document.querySelector('input').classList.contains('baz')).toEqual(false)
    })

    await wait(() => {
        expect(document.querySelector('button').disabled).toEqual(false)
        expect(document.querySelector('input').classList.contains('baz')).toEqual(false)
    })
})

test('loading is scoped to current element if it fires an action, even with parameters', async () => {
    mountAndReturn(
        `<button wire:click="foo('bar')" wire:loading.attr="disabled"><span wire:click="bar" wire:loading.class="baz"></span>`,
        `<button wire:click="foo('bar')" wire:loading.attr="disabled"><span wire:click="bar" wire:loading.class="baz"></span>`,
        [], async () => {
            // Make the loading last for 50ms.
            await timeout(50)
        }
    )

    expect(document.querySelector('button').disabled).toEqual(false)
    expect(document.querySelector('span').classList.contains('baz')).toEqual(false)

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('button').disabled).toEqual(true)
        expect(document.querySelector('span').classList.contains('baz')).toEqual(false)
    })

    await wait(() => {
        expect(document.querySelector('button').disabled).toEqual(false)
        expect(document.querySelector('span').classList.contains('baz')).toEqual(false)
    })
})

test('loading element is hidden after Livewire receives error from backend', async () => {
    mountAndError(
        `<button wire:click="onClick"></button><span style="display: none" wire:loading></span>`,
        async () => {
            // Make the loading last for 50ms.
            await timeout(50)
        }
    )

    expect(document.querySelector('span').style.display).toEqual('none')

    document.querySelector('button').click()

    await wait(async () => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')

        await wait(async () => {
            expect(document.querySelector('span').style.display).toEqual('none')
        })
    })
})

test('show element while targeted action is loading', async () => {
    mount(
`<button wire:click="foo"></button>
<span style="display: none" wire:loading wire:target="foo"></span>
<h1 style="display: none" wire:loading wire:target="bar"></h1>`
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
        expect(document.querySelector('h1').style.display).toEqual('none')
    })
})

test('hide element while targeted action is loading', async () => {
    mount(
        `<button wire:click="foo"></button>
<span style="display: inline-block" wire:loading.remove wire:target="foo"></span>
<h1 style="display: inline-block" wire:loading.remove wire:target="bar"></h1>`
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').style.display).toEqual('none')
        expect(document.querySelector('h1').style.display).toEqual('inline-block')
    })
})

test('loading element can have multiple targets', async () => {
    mount(
`<button wire:click="foo"></button>
<a wire:click="bar"></a>
<span style="display: none" wire:loading wire:target="foo, bar"></span>`
    )

    document.querySelector('a').click()

    await wait( () => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
    })
})

test('add element class while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class="foo-class"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('foo-class')).toBeTruthy()
    })
})

test('add element class with spaces while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class="foo bar"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('foo')).toBeTruthy()
        expect(document.querySelector('span').classList.contains('bar')).toBeTruthy()
    })
})

test('remove element class while loading', async () => {
    mount('<button wire:click="onClick"></button><span class="hidden" wire:loading.class.remove="hidden"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('hidden')).toBeFalsy()
    })
})

test('add element attribute while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr="disabled"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeTruthy()
    })
})

test('remove element attribute while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr.remove="disabled" disabled="true"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeFalsy()
    })
})

test('add element attribute AND class while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr="disabled" wire:loading.class="foo"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeTruthy()
        expect(document.querySelector('span').classList.contains('foo')).toBeTruthy()
    })
})

test('remove element reference from components generic loading array', async () => {
    let componentReference
    mountAndReturn(
        '<button wire:click="foo"></button><span wire:loading></span>',
        '<button wire:click="foo"></button>'
    )

    window.livewire.hook('messageSent', component => {
        componentReference = component
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span')).toEqual(null)
        expect(componentReference.genericLoadingEls).toEqual([])
    })
})

test('remove element reference from components targeted loading array', async () => {
    let componentReference
    mountAndReturn(
        '<button wire:click="foo"></button><span wire:loading wire:target="foo"></span>',
        '<button wire:click="foo"></button>'
    )

    window.livewire.hook('messageSent', component => {
        componentReference = component
    })

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span')).toEqual(null)
        expect(componentReference.targetedLoadingElsByAction).toEqual({foo: []})
    })
})

test('do nothing when the loading class is empty when adding classes', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class=""></span>')

    document.querySelector('button').click()

     await timeout(5)

     await wait(() => {
         expect(document.querySelector('span').classList.length).toEqual(0)
     })
})

test('do nothing when the loading class is empty when removing classes', async () => {
    mount('<button wire:click="onClick"></button><span class="foo" wire:loading.remove.class=""></span>')

    document.querySelector('button').click()

     await timeout(5)

     await wait(() => {
         expect(document.querySelector('span').classList.length).toEqual(1)
         expect(document.querySelector('span').classList.contains('foo')).toBeTruthy()
     })
 })
