import { describe, it, expect } from 'vitest'
import { findFragment } from './fragment'

let start = '<!--[if FRAGMENT:type=island|name=rows|token=abc]><![endif]-->'
let end = '<!--[if ENDFRAGMENT:type=island|name=rows|token=abc]><![endif]-->'
let block = (inner) => `<!--[if BLOCK]><![endif]-->${inner}<!--[if ENDBLOCK]><![endif]-->`

function island(content) {
    let el = document.createElement('ul')

    el.innerHTML = start + content + end

    return { el, fragment: findFragment(el, { isMatch: ({ name }) => name === 'rows' }) }
}

// Every node between the island's markers, comments by their text and elements by their text.
function shape(el) {
    return Array.from(el.childNodes)
        .filter(node => ! (node.nodeType === 3 && node.textContent.trim() === ''))
        .map(node => node.nodeType === 8 ? node.textContent.match(/\[if (\w+)/)[1] : node.textContent)
        .filter(text => ! text.includes('FRAGMENT'))
}

describe('Fragment append and prepend', () => {
    it('joins an appended loop block to the block before it', () => {
        let { el, fragment } = island(block('<li>1</li><li>2</li>'))

        fragment.append('ul', block('<li>3</li><li>4</li>'))

        expect(shape(el)).toEqual(['BLOCK', '1', '2', '3', '4', 'ENDBLOCK'])
    })

    it('joins a prepended loop block to the block after it', () => {
        let { el, fragment } = island(block('<li>2</li><li>1</li>'))

        fragment.prepend('ul', block('<li>4</li><li>3</li>'))

        expect(shape(el)).toEqual(['BLOCK', '4', '3', '2', '1', 'ENDBLOCK'])
    })

    it('keeps the blocks inside each row while joining the outer ones', () => {
        let { el, fragment } = island(block(block('<li>1</li>')))

        fragment.append('ul', `\n    ${block(block('<li>2</li>'))}\n`)

        expect(shape(el)).toEqual(['BLOCK', 'BLOCK', '1', 'ENDBLOCK', 'BLOCK', '2', 'ENDBLOCK', 'ENDBLOCK'])
    })

    it('leaves appended content that is not a single block as it landed', () => {
        let { el, fragment } = island('Hi,')

        fragment.append('ul', ' how are you?')

        expect(el.textContent).toBe('Hi, how are you?')
    })

    it('leaves an island holding several top-level blocks as it landed', () => {
        let { el, fragment } = island(block('<li>a</li>') + block('<li>b</li>'))

        fragment.append('ul', block('<li>c</li>'))

        expect(shape(el)).toEqual(['BLOCK', 'a', 'ENDBLOCK', 'BLOCK', 'b', 'ENDBLOCK', 'BLOCK', 'c', 'ENDBLOCK'])
    })

    it('leaves the seam the node shape a full render draws', () => {
        let rows = (...ns) => block(ns.map(n => `\n<li>${n}</li>`).join('') + '\n')
        let nodes = (el) => Array.from(el.childNodes).map(node => node.nodeType === 3 ? '#text' : node.nodeType === 8 ? node.textContent : node.textContent)

        let appended = island('\n' + rows(1, 2) + '\n')
        appended.fragment.append('ul', '\n' + rows(3, 4) + '\n')

        let prepended = island('\n' + rows(3, 4) + '\n')
        prepended.fragment.prepend('ul', '\n' + rows(1, 2) + '\n')

        let full = island('\n' + rows(1, 2, 3, 4) + '\n')

        expect(nodes(appended.el)).toEqual(nodes(full.el))
        expect(nodes(prepended.el)).toEqual(nodes(full.el))
    })
})
