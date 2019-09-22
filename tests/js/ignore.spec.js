import { mountAndReturn } from './utils'
const timeout = ms => new Promise(resolve => setTimeout(resolve, ms))

test('wire:ignore doesnt modify element or children after update', async () => {
    mountAndReturn(
        '<button wire:click="$refresh" wire:ignore></button>',
        '<button wire:click="$refresh" some-new-attribute="true"></button>',
    )

    document.querySelector('button').click()

    await timeout(20)

    expect(document.querySelector('button').hasAttribute('some-new-attribute')).toBeFalsy()
})

test('wire:ignore ignores updates to children', async () => {
    mountAndReturn(
        '<button wire:click="$refresh" wire:ignore><span>foo</span></button>',
        '<button wire:click="$refresh" wire:ignore><span>bar</span></button>',
    )

    document.querySelector('button').click()

    await timeout(20)

    expect(document.querySelector('span').innerHTML).toEqual('foo')
})

test('wire:ignore.self ignores updates to self, but not children', async () => {
    mountAndReturn(
        '<button wire:click="$refresh" wire:ignore.self><span>foo</span></button>',
        '<button wire:click="$refresh" wire:ignore.self some-new-attribute="foo"><span>bar</span></button>',
    )

    document.querySelector('button').click()

    await timeout(20)

    expect(document.querySelector('button').hasAttribute('some-new-attribute')).toBeFalsy()
    expect(document.querySelector('span').innerHTML).toEqual('bar')
})
