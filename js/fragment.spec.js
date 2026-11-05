import { describe, it, expect } from 'vitest'
import { findFragment } from './fragment'

let island = (label) => `<!--[if FRAGMENT:type=island|name=foo|token=abc-1|mode=morph]><![endif]--><span>${label}</span><!--[if ENDFRAGMENT:type=island|name=foo|token=abc-1|mode=morph]><![endif]-->`

describe('findFragment', () => {
    it('returns the first match in document order', () => {
        document.body.innerHTML = `
            <div id="root">
                <section>${island('first')}${island('second')}</section>
                <section>${island('third')}</section>
            </div>
        `

        let fragment = findFragment(document.getElementById('root'), {
            isMatch: ({ token }) => token === 'abc-1',
        })

        expect(fragment.startMarkerNode.nextSibling.textContent).toBe('first')
    })
})
