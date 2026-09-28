import { describe, it, expect, beforeEach } from 'vitest'
import { findFragment } from './fragment'

let marker = (token, label) => `<!--[if FRAGMENT:type=island|name=${label}|token=${token}|mode=morph]><![endif]--><span>${label}</span><!--[if ENDFRAGMENT:type=island|name=${label}|token=${token}|mode=morph]><![endif]-->`

let labelOf = (fragment) => fragment?.startMarkerNode.nextSibling.textContent

let isMatch = (token) => (metadata) => metadata.type === 'island' && metadata.token === token

describe('findFragment', () => {
    beforeEach(() => {
        document.body.innerHTML = ''
    })

    it('returns the first match in document order, not the last', () => {
        document.body.innerHTML = `
            <div id="root">
                <section>${marker('abc-1', 'first')}</section>
                <section>${marker('abc-1', 'second')}</section>
            </div>
        `

        let fragment = findFragment(document.getElementById('root'), { isMatch: isMatch('abc-1') })

        expect(labelOf(fragment)).toBe('first')
    })

    it('returns the first match among markers that share a parent', () => {
        document.body.innerHTML = `<div id="root">${marker('abc-1', 'first')}${marker('abc-1', 'second')}</div>`

        let fragment = findFragment(document.getElementById('root'), { isMatch: isMatch('abc-1') })

        expect(labelOf(fragment)).toBe('first')
    })

    it('does not descend past a boundary', () => {
        document.body.innerHTML = `
            <div id="root" wire:id="parent">
                <aside><div wire:id="child"><section>${marker('abc-1', 'child')}</section></div></aside>
                <section>${marker('abc-1', 'parent')}</section>
            </div>
        `

        let fragment = findFragment(document.getElementById('root'), {
            isMatch: isMatch('abc-1'),
            hasReachedBoundary: ({ el }) => el.hasAttribute('wire:id'),
        })

        expect(labelOf(fragment)).toBe('parent')
    })

    it('finds nothing when the only match is behind a boundary', () => {
        document.body.innerHTML = `
            <div id="root" wire:id="parent">
                <div wire:id="child"><section>${marker('abc-1', 'child')}</section></div>
            </div>
        `

        let fragment = findFragment(document.getElementById('root'), {
            isMatch: isMatch('abc-1'),
            hasReachedBoundary: ({ el }) => el.hasAttribute('wire:id'),
        })

        expect(fragment).toBeNull()
    })
})
