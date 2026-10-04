import { describe, it, expect, beforeEach } from 'vitest'
import { toggleBooleanStateDirective } from './shared'

function directive(modifiers = []) {
    return { modifiers, expression: '' }
}

beforeEach(() => {
    document.head.innerHTML = ''
    document.body.innerHTML = ''
})

describe('toggleBooleanStateDirective', () => {
    it('shows the element with its own display instead of inline-block', () => {
        let style = document.createElement('style')
        style.textContent = '.flex { display: flex; }'
        document.head.appendChild(style)

        document.body.innerHTML = '<div><span class="flex" style="display: none;" wire:loading></span></div>'

        let el = document.querySelector('span')

        expect(getComputedStyle(el).display).toBe('none')

        toggleBooleanStateDirective(el, directive(), true)

        expect(el.style.display).toBe('flex')

        toggleBooleanStateDirective(el, directive(), false)

        expect(el.style.display).toBe('none')

        toggleBooleanStateDirective(el, directive(), true)

        expect(el.style.display).toBe('flex')
    })

    it('still prefers an explicit display modifier', () => {
        document.body.innerHTML = '<div><span wire:loading></span></div>'

        let el = document.querySelector('span')

        toggleBooleanStateDirective(el, directive(['block']), true)

        expect(el.style.display).toBe('block')
    })

    it('falls back to inline-block when no display can be resolved', () => {
        let style = document.createElement('style')
        style.textContent = '.always-hidden { display: none; }'
        document.head.appendChild(style)

        document.body.innerHTML = '<div><span class="always-hidden" style="display: none;" wire:loading></span></div>'

        let el = document.querySelector('span')

        toggleBooleanStateDirective(el, directive(), true)

        expect(el.style.display).toBe('inline-block')
    })
})
