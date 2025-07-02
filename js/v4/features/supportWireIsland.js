import { directive } from "@/directives"
import interceptor from '@/v4/interceptors/interceptors.js'
import messageBroker from '@/v4/requests/messageBroker.js'

let wireIslands = new WeakMap

interceptor.add(({el, directive, component}) => {
    let name = wireIslands.get(el)?.name ?? closestIslandName(el)

    if (! name) return

    messageBroker.addContext(component, 'islands', name)
})

directive('island', ({ el, directive }) => {
    let name = directive.expression ?? 'default'

    let mode = directive.modifiers.includes('append')
        ? 'append'
        : (directive.modifiers.includes('prepend')
            ? 'prepend'
            : 'replace')

    wireIslands.set(el, {
        name,
        mode,
    })
})

export function wireIslandHook(el) {
    if (! wireIslands.has(el)) return

    let { name, mode } = wireIslands.get(el)

    Alpine.evaluate(el, `$wire.call('__island', '${name}', '${mode}')`)
}

export function implicitIslandHook(el) {
    let name = closestIslandName(el)

    if (! name) return

    Alpine.evaluate(el, `$wire.call('__island', '${name}')`)
}

function closestIslandName(el) {
    let current = el;

    while (current) {
        // Check previous siblings
        let sibling = current.previousSibling;

        while (sibling) {
            if (isEndMarker(sibling)) {
                break; // Found end marker, need to go up to parent
            }

            if (isStartMarker(sibling)) {
                return extractIslandName(sibling);
            }

            sibling = sibling.previousSibling;
        }

        // No start marker found at this level or found end marker
        // Go up to parent unless we've hit the component root
        current = current.parentElement;

        if (current && current.hasAttribute('wire:id')) {
            break; // Stop at component root
        }
    }

    return null;
}

function isStartMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ISLAND')
}

function isEndMarker(el) {
    return el.nodeType === 8 && el.textContent.startsWith('[if ENDISLAND')
}

function extractIslandMode(el) {
    let mode = el.textContent.match(/\[if ISLAND:.*:(\w+)\]/)?.[1]

    return mode || 'replace'
}

function extractIslandName(el) {
    let name = el.textContent.match(/\[if ISLAND:(\w+):.*\]/)?.[1]

    return name || 'default'
}