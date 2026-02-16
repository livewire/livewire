import Alpine from 'alpinejs'

function getAlpineScopeKeys(el) {
    let keys = []

    let currentEl = el

    while (currentEl) {
        if (currentEl._x_dataStack) {
            for (let scope of currentEl._x_dataStack) {
                for (let key of Object.keys(scope)) {
                    if (! keys.includes(key)) keys.push(key)
                }
            }
        }

        // Stop at the Livewire component root element...
        if (currentEl.hasAttribute && currentEl.hasAttribute('wire:id')) break

        currentEl = currentEl.parentElement
    }

    return keys
}

export function evaluateExpression(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let result = Alpine.evaluateRaw(el, expression, options)

    if (result instanceof Promise) {
        result.catch(() => {})
    }

    return result
}

export function evaluateActionExpression(el, expression, options = {}) {
    if (! expression || expression.trim() === '') return

    let contextualExpression = contextualizeExpression(expression, el)

    try {
        let result = Alpine.evaluateRaw(el, contextualExpression, options)

        // Silently catch Livewire request failures. These are handled by
        // Livewire at the request level...
        if (result instanceof Promise && result._livewireAction) {
            result.catch(() => {})
        }

        return result
    } catch (error) {
        console.warn(`Livewire Expression Error: ${error.message}\n\n${ expression ? 'Expression: \"' + expression + '\"\n\n' : '' }`, el)

        console.error(error)
    }
}

export function contextualizeExpression(expression, el) {
    let SKIP = ['JSON', 'true', 'false', 'null', 'undefined', 'this', '$wire', '$event']

    // If an element is provided, collect Alpine scope keys between
    // this element and the Livewire component root so they don't
    // get incorrectly prefixed with $wire.
    if (el) {
        SKIP.push(...getAlpineScopeKeys(el))
    }
    let strings = []

    // 1. Yank out string literals so we don't touch them
    let result = expression.replace(/(["'`])(?:(?!\1)[^\\]|\\.)*\1/g, (m) => {
        strings.push(m)
        return `___${strings.length - 1}___`
    })

    // 2. Prefix identifiers not after a dot (skip placeholders from step 1)
    //    Also skip object keys (identifiers immediately followed by colon)
    result = result.replace(/(^|[^.\w$])(\$?[a-zA-Z_]\w*)/g, (m, pre, ident, offset) => {
        if (SKIP.includes(ident) || /^___\d+___$/.test(ident)) return pre + ident
        if (result[offset + m.length] === ':') return pre + ident
        return pre + '$wire.' + ident
    })

    // 3. Restore strings
    return result.replace(/___(\d+)___/g, (m, i) => strings[i])
}
