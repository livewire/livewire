import { beforeEach, afterEach, describe, expect, it, vi } from 'vitest'
import Alpine from 'alpinejs'
import morph from '@alpinejs/morph'
import { on } from '@/hooks'
import { renderIsland, renderIslandPart } from './supportIslands'

let interceptors = vi.hoisted(() => [])
vi.mock('@/request', () => ({
    interceptAction: () => {},
    interceptMessage: callback => interceptors.push(callback),
}))
vi.mock('@/store', () => ({
    findComponentByEl: el => {
        while (el && ! el.hasAttribute('wire:id')) el = el.parentElement
        return el?.__livewire
    },
}))

Alpine.plugin(morph)

function island(name, token, html) {
    let metadata = `type=island|name=${name}|token=${token}|mode=morph`
    return `<!--[if FRAGMENT:${metadata}]><![endif]-->${html}<!--[if ENDFRAGMENT:${metadata}]><![endif]-->`
}

function runtime(html = '<li data-island-part="row-1" wire:key="row-1">First</li><li data-island-part="row-2" wire:key="row-2">Second</li>', tag = 'ul') {
    document.body.innerHTML = `<div wire:id="parent"><${tag}>${island('users', 'users-token', html)}</${tag}></div>`
    let component = { el: document.body.firstElementChild, id: 'parent', effects: {} }
    component.el.__livewire = component
    return component
}

function operation(overrides = {}) {
    return { island: 'users', token: 'users-token', name: 'row', target: 'row-1', mode: 'morph', html: '<li data-island-part="row-1" wire:key="row-1">Updated</li>', ...overrides }
}

beforeEach(() => {
    window.Alpine = Alpine
    window.reportError = vi.fn()
    // jsdom does not provide CSS.escape; browser tests exercise native escaping.
    vi.stubGlobal('CSS', { escape: value => value.replace(/[^a-zA-Z0-9_-]/g, character => `\\${character.codePointAt(0).toString(16)} `) })
})
afterEach(() => { vi.unstubAllGlobals() })

describe('island part DOM effects', () => {
    it('does not scan part identities during ordinary whole-island rendering', async () => {
        let component = runtime()
        let query = vi.spyOn(component.el, 'querySelectorAll')
        await renderIsland(component, island('users', 'users-token', '<li data-island-part="row-1">Updated</li>'))
        expect(query.mock.calls.filter(([selector]) => selector.startsWith('[data-island-part='))).toHaveLength(0)
    })

    it('morphs only the selected item and retains neighboring DOM identity', async () => {
        let component = runtime()
        let first = component.el.querySelector('[data-island-part="row-1"]')
        let second = component.el.querySelector('[data-island-part="row-2"]')
        let query = vi.spyOn(component.el, 'querySelectorAll')
        await renderIslandPart(component, operation())
        expect(first.textContent).toBe('Updated')
        expect(component.el.querySelector('[data-island-part="row-1"]')).toBe(first)
        expect(component.el.querySelector('[data-island-part="row-2"]')).toBe(second)
        expect(query.mock.calls.filter(([selector]) => selector.startsWith('[data-island-part='))).toHaveLength(1)
    })

    it.each(['append', 'prepend'])('inserts a new item at the %s boundary', async mode => {
        let component = runtime()
        let existing = component.el.querySelector('[data-island-part="row-1"]')
        await renderIslandPart(component, operation({ mode, target: 'row-3', html: '<li data-island-part="row-3">Third</li>' }))
        let targets = Array.from(component.el.querySelectorAll('[data-island-part]')).map(el => el.dataset.islandPart)
        expect(targets).toEqual(mode === 'append' ? ['row-1', 'row-2', 'row-3'] : ['row-3', 'row-1', 'row-2'])
        expect(component.el.querySelector('[data-island-part="row-1"]')).toBe(existing)
    })

    it('uses removing/removed hooks and respects a skipped removal', async () => {
        let component = runtime()
        let removed = vi.fn()
        let stop = on('morph.removing', ({ skip }) => skip())
        let off = on('morph.removed', removed)
        await renderIslandPart(component, operation({ mode: 'remove', html: '' }))
        expect(component.el.querySelector('[data-island-part="row-1"]')).not.toBeNull()
        expect(removed).not.toHaveBeenCalled()
        stop()
        await renderIslandPart(component, operation({ mode: 'remove', html: '' }))
        expect(component.el.querySelector('[data-island-part="row-1"]')).toBeNull()
        expect(removed).toHaveBeenCalledOnce()
        off()
    })

    it('runs adding/added hooks for insertion', async () => {
        let component = runtime()
        let adding = vi.fn()
        let added = vi.fn()
        let offAdding = on('morph.adding', adding)
        let offAdded = on('morph.added', added)
        await renderIslandPart(component, operation({ mode: 'append', target: 'row-3', html: '<li data-island-part="row-3">Third</li>' }))
        expect(adding).toHaveBeenCalledOnce()
        expect(added).toHaveBeenCalledOnce()
        offAdding()
        offAdded()
    })

    it.each([
        ['missing target', { target: 'missing' }],
        ['duplicate insert', { mode: 'append' }],
        ['multiple roots', { html: '<li data-island-part="row-1"></li><li></li>' }],
        ['non-whitespace text', { html: 'text<li data-island-part="row-1"></li>' }],
        ['wrong target', { html: '<li data-island-part="other"></li>' }],
        ['child root', { html: '<li wire:id="child" data-island-part="row-1"></li>' }],
        ['missing island', { token: 'missing' }],
        ['invalid mode', { mode: 'replace' }],
    ])('rejects %s before DOM mutation', async (_, overrides) => {
        let component = runtime()
        let before = component.el.innerHTML
        await expect(renderIslandPart(component, operation(overrides))).rejects.toThrow('Livewire island part')
        expect(component.el.innerHTML).toBe(before)
    })

    it('rejects duplicate existing targets', async () => {
        let component = runtime('<li data-island-part="row-1"></li><li data-island-part="row-1"></li>')
        await expect(renderIslandPart(component, operation())).rejects.toThrow('found 2')
    })

    it('does not target elements owned by nested islands or child components', async () => {
        let component = runtime(island('nested', 'nested-token', '<li data-island-part="row-1"></li>') + '<li wire:id="child"><span data-island-part="row-1"></span></li>')
        await expect(renderIslandPart(component, operation())).rejects.toThrow('found 0')
    })

    it('does not discover an island inside a child component', async () => {
        let component = runtime('')
        component.el.innerHTML = `<section wire:id="child">${island('users', 'users-token', '<li data-island-part="row-1"></li>')}</section>`
        await expect(renderIslandPart(component, operation())).rejects.toThrow('island was not found')
    })

    it('parses table rows in their contextual container', async () => {
        let component = runtime('<tr data-island-part="row-1"><td>Before</td></tr>', 'tbody')
        // Build an actual table, since the HTML parser discards standalone tbody.
        component.el.innerHTML = `<table><tbody>${island('users', 'users-token', '<tr data-island-part="row-1"><td>Before</td></tr>')}</tbody></table>`
        await renderIslandPart(component, operation({ html: ' <!-- ok --> <tr data-island-part="row-1"><td>After</td></tr> ' }))
        expect(component.el.querySelector('tbody > tr > td').textContent).toBe('After')
    })

    it('uses exact selectors for unusual target strings', async () => {
        let target = 'row-"[]\\ space é'
        let component = runtime('<li></li>')
        let el = component.el.querySelector('li')
        el.setAttribute('data-island-part', target)
        let incoming = el.cloneNode()
        incoming.textContent = 'Updated'
        await renderIslandPart(component, operation({ target, html: incoming.outerHTML }))
        expect(el.textContent).toBe('Updated')
    })

    it('reports bad effects while allowing later operations and request cleanup to finish', async () => {
        let component = runtime()
        let morphCallbacks = []
        interceptors.forEach(callback => callback({
            message: { component },
            onStream: () => {},
            onSuccess: callback => callback({
                payload: { effects: { islandParts: [operation({ target: 'missing' }), operation()] } },
                onMorph: callback => morphCallbacks.push(callback),
            }),
        }))
        for (let callback of morphCallbacks) await callback()
        expect(window.reportError).toHaveBeenCalledOnce()
        expect(component.el.querySelector('[data-island-part="row-1"]').textContent).toBe('Updated')
    })
})
