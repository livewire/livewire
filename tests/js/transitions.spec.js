import { wait, waitForElement, waitForDomChange } from 'dom-testing-library'
import { mountAndReturn } from './utils'

test('fade in transition is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button>',
        '<button wire:click="onClick"></button><span wire:transition.fade></span>'
    )

    document.querySelector('button').click()

    await waitForElement(() => document.querySelector('span'))

    expect(document.querySelector('span').style.opacity).toBe('0')

    await wait(() => {
        expect(document.querySelector('span').style.opacity).toBe('1')
    })
})

test('fade out transition is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button><span wire:transition.fade.50ms></span>',
        '<button wire:click="onClick"></button>'
    )

    document.querySelector('button').click()

    expect(document.querySelector('span').style.opacity).toBe('')

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span').style.opacity).toBe('0')
    })

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span')).toBeNull()
    })
})

test('fade out transition is not applied if "in" modifier is present', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button><span wire:transition.fade.in.500ms></span>',
        '<button wire:click="onClick"></button>'
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span')).toBeNull()
    }, { timeout: 100 })
})

test('fade in transition class is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button>',
        // Because Livewire uses "transitionDuration" to determine when to remove the transitions class
        // we have to set a transition duration inline on the <span> tag.
        '<button wire:click="onClick"></button><span style="transition-duration: .01s" wire:transition="fade"></span>'
    )

    document.querySelector('button').click()

    await waitForElement(() => document.querySelector('span'))

    expect(document.querySelector('span').classList.contains('fade-enter', 'fade-enter-active')).toBeTruthy()

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span').classList.contains('fade-enter')).toBeFalsy()
        expect(document.querySelector('span').classList.contains('fade-enter-active')).toBeTruthy()
    })

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span').classList.contains('fade-enter')).toBeFalsy()
        expect(document.querySelector('span').classList.contains('fade-enter-active')).toBeFalsy()
    })
})

test('fade out transition class is applied', async () => {
    mountAndReturn(
`<button wire:click="onClick"></button>
<span style="transition-duration: .01s" wire:transition="fade"></span>`,
`<button wire:click="onClick"></button>`
    )

    document.querySelector('button').click()

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span').classList.contains('fade-leave')).toBeFalsy()
        expect(document.querySelector('span').classList.contains('fade-leave-active')).toBeTruthy()
    })

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span').classList.contains('fade-leave')).toBeTruthy()
        expect(document.querySelector('span').classList.contains('fade-leave-active')).toBeTruthy()
    })

    await wait(() => {
        expect(document.querySelector('span')).toBeNull()
    })
})

test('slide in transition is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button>',
        '<button wire:click="onClick"></button><span wire:transition.slide></span>'
    )

    document.querySelector('button').click()

    await waitForElement(() => document.querySelector('span'))

    expect(document.querySelector('span').style.opacity).toBe('0')
    expect(document.querySelector('span').style.transform).toBe('translateX(10px)')

    await wait(() => {
        expect(document.querySelector('span').style.opacity).toBe('1')
        expect(document.querySelector('span').style.transform).toBe('')
    })
})

test('slide out transition is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button><span wire:transition.slide.50ms></span>',
        '<button wire:click="onClick"></button>'
    )

    document.querySelector('button').click()

    expect(document.querySelector('span').style.opacity).toBe('')
    expect(document.querySelector('span').style.transform).toBe('')

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span').style.opacity).toBe('0')
        expect(document.querySelector('span').style.transform).toBe('translateX(10px)')
    })

    await waitForDomChange(document.querySelector('span'), () => {
        expect(document.querySelector('span')).toBeNull()
    })
})

test('slide out transition is not applied if "in" modifier is present', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button><span wire:transition.slide.in.500ms></span>',
        '<button wire:click="onClick"></button>'
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span')).toBeNull()
    }, { timeout: 100 })
})

test('slide right transition is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button>',
        '<button wire:click="onClick"></button><span wire:transition.slide.right></span>'
    )

    document.querySelector('button').click()

    await waitForElement(() => document.querySelector('span'))

    expect(document.querySelector('span').style.opacity).toBe('0')
    expect(document.querySelector('span').style.transform).toBe('translateX(10px)')

    await wait(() => {
        expect(document.querySelector('span').style.opacity).toBe('1')
        expect(document.querySelector('span').style.transform).toBe('')
    })
})

test('slide left transition is applied', async () => {
    mountAndReturn(
        '<button wire:click="onClick"></button>',
        '<button wire:click="onClick"></button><span wire:transition.slide.left></span>'
    )

    document.querySelector('button').click()

    await waitForElement(() => document.querySelector('span'))

    expect(document.querySelector('span').style.opacity).toBe('0')
    expect(document.querySelector('span').style.transform).toBe('translateX(-10px)')

    await wait(() => {
        expect(document.querySelector('span').style.opacity).toBe('1')
        expect(document.querySelector('span').style.transform).toBe('')
    })
})
