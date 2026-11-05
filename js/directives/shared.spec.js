import { describe, it, vi, expect, afterEach } from 'vitest'
import { Directive } from '../directives'
import { toggleBooleanStateDirective } from './shared'
import Alpine from '../../node_modules/alpinejs/dist/module.esm'

window.Alpine = Alpine

function directive(modifiers) {
    return new Directive('loading', modifiers, 'wire:loading', document.createElement('div'))
}

describe('Toggling display', () => {
    afterEach(() => vi.restoreAllMocks())

    it('shows the element while truthy', () => {
        let el = document.createElement('div')

        toggleBooleanStateDirective(el, directive(['flex']), true)
        expect(el.style.display).toBe('flex')

        toggleBooleanStateDirective(el, directive(['flex']), false)
        expect(el.style.display).toBe('none')
    })

    it('hides the element while truthy with the remove modifier', () => {
        let el = document.createElement('div')

        toggleBooleanStateDirective(el, directive(['remove']), true)
        expect(el.style.display).toBe('none')

        toggleBooleanStateDirective(el, directive(['remove']), false)
        expect(el.style.display).toBe('inline-block')
    })

    // Reading a computed style forces the browser to recalculate every style changed so far,
    // which on a large page is the most expensive part of ending a loading state...
    it('never reads the computed style', () => {
        let spy = vi.spyOn(window, 'getComputedStyle')
        let el = document.createElement('div')

        toggleBooleanStateDirective(el, directive([]), true)
        toggleBooleanStateDirective(el, directive([]), false)
        toggleBooleanStateDirective(el, directive(['remove']), true)
        toggleBooleanStateDirective(el, directive(['remove']), false)

        expect(spy).not.toHaveBeenCalled()
    })
})
